<?php

namespace App\Services\Mqtt;

use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderTms;

/**
 * Terapkan payload MQTT result (pola mqtt:tms-subscribe) ke tb_orderdetail_tms.
 * Tidak menulis ke biolis_results — tabel itu khusus feed REST dari alat.
 */
class TmsMqttResultHandler
{
    /**
     * @param  string  $idPermohonan
     * @param  array  $message  hasil MqttService::subscribeCollect
     * @return array
     */
    public function applyToPermohonan($idPermohonan, array $message)
    {
        return $this->applyMessage($message, $idPermohonan);
    }

    /**
     * Versi tanpa konteks permohonan (dipakai daemon mqtt:tms-subscribe).
     * Order dicari global dari id_order_tms / sample_id pada payload.
     *
     * @param  array  $message  hasil MqttService::subscribeCollect
     * @return array
     */
    public function apply(array $message)
    {
        return $this->applyMessage($message, null);
    }

    /**
     * @param  array  $message
     * @param  string|null  $idPermohonan  null = cari order global
     * @return array
     */
    protected function applyMessage(array $message, $idPermohonan = null)
    {
        $report = [
            'applied' => false,
            'matched' => false,
            'updated' => 0,
            'id_order_tms' => null,
            'sample_id' => null,
            'tray' => null,
            'pos' => null,
            'matched_by' => null,
            'skipped_parameters' => [],
            'error' => null,
        ];

        try {
            $payload = isset($message['payload']) ? $message['payload'] : null;
            if (!is_array($payload)) {
                $report['error'] = 'Payload MQTT bukan JSON object.';
                return $report;
            }

            $data = (isset($payload['data']) && is_array($payload['data']))
                ? $payload['data']
                : $payload;

            $idOrder = $this->firstNonEmptyString([
                $data['id_order_tms'] ?? null,
                $payload['id_order_tms'] ?? null,
            ]);
            $sampleId = $this->firstNonEmptyString([
                $data['sample_id'] ?? null,
                $data['kode_barcode'] ?? null,
                $payload['sample_id'] ?? null,
                $payload['kode_barcode'] ?? null,
            ]);
            $report['sample_id'] = $sampleId !== '' ? $sampleId : null;

            $tray = TmsKlinikHelper::normalizeTrayPosValue(
                $data['tray'] ?? $payload['tray'] ?? null
            );
            $pos = TmsKlinikHelper::normalizeTrayPosValue(
                $data['pos'] ?? $data['posisi'] ?? $payload['pos'] ?? $payload['posisi'] ?? null
            );
            $report['tray'] = $tray;
            $report['pos'] = $pos;

            $includeAllMatching = !empty($message['replayed_from_log']);

            $resolved = $this->resolveOrders($idPermohonan, $idOrder, $sampleId, $tray, $pos, $includeAllMatching);
            $orders = $resolved['orders'];
            $report['matched_by'] = $resolved['matched_by'];

            if ($orders->isEmpty()) {
                if (!empty($resolved['error'])) {
                    $report['error'] = $resolved['error'];
                } elseif (!empty($resolved['all_filled'])) {
                    $broaderForIdempotent = $this->resolveAllOrdersByBarcode($idPermohonan, $sampleId, $tray, $pos);
                    if ($broaderForIdempotent->isNotEmpty()) {
                        $orders = $broaderForIdempotent;
                        $report['matched_by'] = ($resolved['matched_by'] ?: 'sample_id') . '+all_filled';
                    } else {
                        $report['error'] = 'Semua order pending untuk sample_id '
                            . $sampleId
                            . ' sudah memiliki nilai parameter dari payload.';
                        return $report;
                    }
                } else {
                    if ($idPermohonan !== null) {
                        $report['error'] = 'Order tidak cocok dengan permohonan ini.';
                    } elseif ($sampleId !== '') {
                        $report['error'] = 'Order tidak ditemukan untuk '
                            . TmsKlinikHelper::formatOrderLocationHint($sampleId, $tray, $pos)
                            . '.';
                    } else {
                        $report['error'] = 'Order tidak ditemukan. Payload perlu sample_id (barcode).';
                    }
                }
                return $report;
            }

            $report['matched'] = true;
            $report['id_order_tms'] = $orders->pluck('id_order_tms')->filter()->implode(',');

            $rows = $data['results'] ?? $data['parameters'] ?? $payload['results'] ?? [];
            if (!is_array($rows) || empty($rows)) {
                $report['error'] = 'Tidak ada results/parameters pada payload.';
                return $report;
            }

            foreach ($orders as $order) {
                $order->load(['details' => function ($q) {
                    $q->whereNull('deleted_at');
                }]);
            }

            $updated = 0;
            $idempotent = 0;
            $skipped = [];
            $touched = [];
            $broaderOrders = null;

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $parameterId = (int) ($row['parameter_id'] ?? $row['id_parameter_tms'] ?? 0);
                if ($parameterId <= 0) {
                    continue;
                }

                $rawValue = null;
                if (array_key_exists('hasil', $row)) {
                    $rawValue = $row['hasil'];
                } elseif (array_key_exists('value', $row)) {
                    $rawValue = $row['value'];
                } elseif (array_key_exists('result_value', $row)) {
                    $rawValue = $row['result_value'];
                }
                if ($rawValue === null || $rawValue === '') {
                    continue;
                }

                $found = $this->findDetailForParameter($orders, $parameterId, $tray, $pos);
                if (!$found) {
                    if ($broaderOrders === null) {
                        $broaderOrders = $this->resolveAllOrdersByBarcode($idPermohonan, $sampleId, $tray, $pos);
                    }
                    $found = $this->findDetailForParameter($broaderOrders, $parameterId, $tray, $pos);
                }
                if (!$found) {
                    $searchPool = $broaderOrders !== null ? $broaderOrders : $orders;
                    $found = $this->findDetailForParameterSameValue($searchPool, $parameterId, $rawValue, $tray, $pos);
                    if ($found) {
                        $idempotent++;
                        $touched[$found['order']->id_order_tms] = $found['order'];
                        continue;
                    }
                    $skipped[] = $parameterId;
                    continue;
                }

                $found['detail']->value = TmsKlinikHelper::formatResultValue($rawValue);
                $found['detail']->save();
                $updated++;
                $touched[$found['order']->id_order_tms] = $found['order'];
            }

            $report['skipped_parameters'] = $skipped;
            $report['idempotent_parameters'] = $idempotent;

            if ($updated > 0 || ($idempotent > 0 && empty($skipped))) {
                if ($updated === 0 && $idempotent > 0) {
                    $report['already_applied'] = true;
                }
                foreach ($touched as $order) {
                    $resolvedBarcode = TmsKlinikHelper::resolveBarcodeForOrderFromSampleId($order, $sampleId);
                    if ($resolvedBarcode !== '' && trim((string) ($order->kode_barcode ?? '')) !== $resolvedBarcode) {
                        $order->kode_barcode = $resolvedBarcode;
                    }
                    if ($tray !== null && TmsKlinikHelper::normalizeTrayPosValue($order->tray) !== $tray) {
                        $order->tray = $tray;
                    }
                    if ($pos !== null && TmsKlinikHelper::normalizeTrayPosValue($order->pos) !== $pos) {
                        $order->pos = $pos;
                    }

                    $order->load(['details' => function ($q) {
                        $q->whereNull('deleted_at');
                    }]);
                    $allFilled = true;
                    foreach ($order->details as $detail) {
                        if ($this->detailValueIsEmpty($detail)) {
                            $allFilled = false;
                            break;
                        }
                    }
                    if ($allFilled) {
                        $order->is_executed = 1;
                        $order->executed_at = $order->executed_at ?: now();
                    } else {
                        // Hasil terlambat / parsial: tetap pending agar batch berikutnya
                        // mengisi order yang sama, bukan order duplikat berikutnya.
                        $order->is_executed = 0;
                        $order->executed_at = null;
                    }
                    $order->save();
                }
                $report['applied'] = true;
                $report['updated'] = $updated;
                $report['id_order_tms'] = implode(',', array_keys($touched));
            } else {
                $report['error'] = empty($skipped)
                    ? 'Tidak ada parameter order yang berhasil diisi dari payload MQTT (semua slot parameter sudah terisi).'
                    : 'Parameter pada payload (' . implode(', ', $skipped) . ') tidak ada di order ini atau sudah terisi nilai berbeda.';
            }
        } catch (\Throwable $e) {
            Log::error('MQTT TMS result apply failed', [
                'id_permohonan_uji_klinik' => $idPermohonan,
                'message' => $e->getMessage(),
            ]);
            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Urutan pencocokan:
     * 1. id_order_tms (opsional, jika ada di payload)
     * 2. barcode/sample_id + tray + pos → order slot kosong (antrian helper)
     * 3. per parameter: slot kosong pada order terpilih
     *
     * @param  string|null  $idPermohonan
     * @param  string  $idOrder
     * @param  string  $sampleId
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @param  bool  $includeAllMatching
     * @return array{orders:\Illuminate\Support\Collection, matched_by:string|null, error?:string|null, all_filled?:bool}
     */
    protected function resolveOrders($idPermohonan, $idOrder, $sampleId, $tray = null, $pos = null, $includeAllMatching = false)
    {
        $base = OrderTms::query()->whereNull('deleted_at');
        if ($idPermohonan !== null && $idPermohonan !== '') {
            $base->where('id_permohonan_uji_klinik', $idPermohonan);
        }

        if ($idOrder !== '') {
            $byId = (clone $base)->where('id_order_tms', $idOrder)->get();
            if ($byId->isNotEmpty()) {
                return ['orders' => $byId, 'matched_by' => 'id_order_tms'];
            }
        }

        $tray = TmsKlinikHelper::normalizeTrayPosValue($tray);
        $pos = TmsKlinikHelper::normalizeTrayPosValue($pos);

        return TmsKlinikHelper::resolvePendingOrdersByBarcodeTrayPos($base, $sampleId, $tray, $pos, $includeAllMatching);
    }

    /**
     * Semua order dengan barcode yang sama (tanpa filter slot kosong).
     *
     * @param  string|null  $idPermohonan
     * @param  string  $sampleId
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return \Illuminate\Support\Collection
     */
    protected function resolveAllOrdersByBarcode($idPermohonan, $sampleId, $tray = null, $pos = null)
    {
        $sampleId = trim($sampleId);
        if ($sampleId === '') {
            return collect();
        }

        $base = OrderTms::query()->whereNull('deleted_at');
        if ($idPermohonan !== null && $idPermohonan !== '') {
            $base->where('id_permohonan_uji_klinik', $idPermohonan);
        }

        $tray = TmsKlinikHelper::normalizeTrayPosValue($tray);
        $pos = TmsKlinikHelper::normalizeTrayPosValue($pos);

        $orders = TmsKlinikHelper::fetchOrdersMatchingSampleId(clone $base, $sampleId);

        return TmsKlinikHelper::sortOrdersForResultMatch($orders, $tray, $pos);
    }

    /**
     * @param  \Illuminate\Support\Collection  $orders
     * @param  int  $parameterId
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return array{detail:\Smt\Masterweb\Models\OrderDetailTms, order:\Smt\Masterweb\Models\OrderTms}|null
     */
    protected function findDetailForParameter($orders, $parameterId, $tray = null, $pos = null)
    {
        $candidates = [];

        foreach ($orders as $order) {
            $detail = $order->details->first(function ($d) use ($parameterId) {
                return (int) $d->id_parameter_tms === (int) $parameterId;
            });
            if ($detail && $this->detailValueIsEmpty($detail)) {
                $candidates[] = ['detail' => $detail, 'order' => $order];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) use ($tray, $pos) {
            return TmsKlinikHelper::compareOrdersForResultMatch($a['order'], $b['order'], $tray, $pos);
        });

        return $candidates[0];
    }

    /**
     * Slot sudah terisi dengan nilai sama persis (replay / hasil terlambat).
     *
     * @param  \Illuminate\Support\Collection  $orders
     * @param  int  $parameterId
     * @param  mixed  $rawValue
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return array{detail:\Smt\Masterweb\Models\OrderDetailTms, order:\Smt\Masterweb\Models\OrderTms}|null
     */
    protected function findDetailForParameterSameValue($orders, $parameterId, $rawValue, $tray = null, $pos = null)
    {
        $target = TmsKlinikHelper::formatResultValue($rawValue);
        if ($target === '' || $target === '-') {
            return null;
        }

        $candidates = [];

        foreach ($orders as $order) {
            $detail = $order->details->first(function ($d) use ($parameterId) {
                return (int) $d->id_parameter_tms === (int) $parameterId;
            });
            if (!$detail || $this->detailValueIsEmpty($detail)) {
                continue;
            }
            if (TmsKlinikHelper::formatResultValue($detail->value) !== $target) {
                continue;
            }
            $candidates[] = ['detail' => $detail, 'order' => $order];
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) use ($tray, $pos) {
            return TmsKlinikHelper::compareOrdersForResultMatch($a['order'], $b['order'], $tray, $pos);
        });

        return $candidates[0];
    }

    /**
     * @param  \Smt\Masterweb\Models\OrderDetailTms  $detail
     * @return bool
     */
    protected function detailValueIsEmpty($detail)
    {
        return TmsKlinikHelper::detailValueIsEmpty($detail->value ?? '');
    }

    /**
     * @param  array  $values
     * @return string
     */
    protected function firstNonEmptyString(array $values)
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }
            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }
}
