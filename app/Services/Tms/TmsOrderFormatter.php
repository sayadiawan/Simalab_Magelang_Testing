<?php

namespace App\Services\Tms;

use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderTms;

/**
 * Format order TMS yang dipakai GET /api/tms/orders (PowerCell HTTP).
 * MQTT memakai output yang sama agar tidak ada business logic kedua.
 */
class TmsOrderFormatter
{
    /**
     * Relasi yang sama dengan endpoint GET TMS existing.
     *
     * @return array
     */
    public static function eagerLoad()
    {
        return [
            'details' => function ($q) {
                $q->whereNull('deleted_at')->with([
                    'parameterTms',
                    'permohonanUjiParameterKlinik.parametersatuanklinik',
                ]);
            },
            'permohonanUjiKlinik.pasien',
        ];
    }

    /**
     * @param  \Smt\Masterweb\Models\OrderTms  $order
     * @param  bool  $includeValues
     * @return array
     */
    public static function fromOrder($order, $includeValues = true)
    {
        $dob = $order->tanggal_lahir;
        if ($dob && method_exists($dob, 'format')) {
            $dob = $dob->format('Y-m-d');
        }

        $permohonan = $order->relationLoaded('permohonanUjiKlinik')
            ? $order->permohonanUjiKlinik
            : $order->permohonanUjiKlinik()->with('pasien')->first();

        $parameters = $order->details->map(function ($detail) use ($includeValues, $permohonan) {
            $tmsJenis = optional($detail->parameterTms)->jenis_sampel;
            $satuan = optional($detail->permohonanUjiParameterKlinik)->parametersatuanklinik;
            $jenisParam = TmsKlinikHelper::resolveJenisSpesimenForTmsOrder(
                (object) ['parametersatuanklinik' => $satuan],
                $tmsJenis,
                $permohonan
            );
            if ($jenisParam === 'Lainnya') {
                $fromTmsName = TmsKlinikHelper::jenisSampelFromLabel((string) optional($detail->parameterTms)->name_parameter_tms);
                if ($fromTmsName !== '') {
                    $jenisParam = $fromTmsName;
                }
            }

            $row = [
                'parameter_id' => (int) $detail->id_parameter_tms,
                'parameter_name' => optional($detail->parameterTms)->name_parameter_tms,
                'id_orderdetail_tms' => $detail->id_orderdetail_tms,
                'jenis_spesimen' => $jenisParam,
            ];
            if ($includeValues) {
                $row['value'] = $detail->value;
            }
            return $row;
        })->values()->all();

        $specimen = TmsKlinikHelper::specimenNumberParts($permohonan);
        $jenisSpesimen = trim((string) ($order->jenis_sampel ?? ''));
        if ($jenisSpesimen === '') {
            $jenisSpesimen = TmsKlinikHelper::inferJenisSpesimenFromOrder($order, $permohonan);
        }

        return [
            'id_order_tms' => $order->id_order_tms,
            'sample_id' => $order->kode_barcode,
            'kode_barcode' => $order->kode_barcode,
            'jenis_sampel' => $jenisSpesimen,
            'jenis_spesimen' => $jenisSpesimen,
            // Identitas untuk alat: nomer_sampel.nomer_pasien (bukan UUID permohonan/pasien)
            'nomer_sampel' => $specimen['nomer_sampel'],
            'nomer_pasien' => $specimen['nomer_pasien'],
            'nomer_spesimen' => $specimen['nomer_spesimen'],
            'patient_name' => $order->nama_pasien,
            'birth_date' => $dob,
            'gender' => $order->jenis_kelamin,
            'tray' => $order->tray !== null && $order->tray !== '' ? (string) $order->tray : null,
            'pos' => $order->pos !== null && $order->pos !== '' ? (string) $order->pos : null,
            'posisi' => $order->pos !== null && $order->pos !== '' ? (string) $order->pos : null,
            'is_executed' => (bool) $order->is_executed,
            'executed_at' => optional($order->executed_at)->format('Y-m-d H:i:s'),
            'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
            'parameters' => $parameters,
        ];
    }

    /**
     * @param  string  $idOrderTms
     * @return \Smt\Masterweb\Models\OrderTms|null
     */
    public static function findWithRelations($idOrderTms)
    {
        return OrderTms::query()
            ->where('id_order_tms', $idOrderTms)
            ->whereNull('deleted_at')
            ->with(self::eagerLoad())
            ->first();
    }
}
