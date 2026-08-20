<?php

namespace App\Http\Controllers;

use App\Services\Tms\TmsOrderFormatter;
use App\TmsResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderDetailTms;
use Smt\Masterweb\Models\OrderTms;

class TmsController extends Controller
{
    public function store(Request $request)
    {
        foreach ($request->results as $result) {
            TmsResult::create([
                'result_date'    => $request->tanggal,
                'sample_id'      => $request->sample_id,
                'parameter_id'   => $result['parameter_id'] ?? null,
                'parameter_name' => $result['parameter_name'] ?? null,
                'patient_name'   => $request->patient_name,
                'result_value'   => $result['hasil'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
        ], 201);
    }

    public function index(Request $request)
    {
        $query = TmsResult::orderBy('result_date', 'desc');

        if ($request->filled('sample_id')) {
            $query->where('sample_id', $request->sample_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->limit(100)->get(),
        ]);
    }

    /**
     * Daftar order TMS yang belum tereksekusi (untuk komputer alat).
     *
     * GET /api/tms/orders
     * Auth: Basic elits / labkeskabmagelang
     *
     * Query opsional:
     * - sample_id / kode_barcode
     * - tray
     * - pos / posisi
     * - limit (default 100, max 500)
     */
    public function pendingOrders(Request $request)
    {
        $limit = (int) $request->input('limit', 100);
        if ($limit < 1) {
            $limit = 100;
        }
        if ($limit > 500) {
            $limit = 500;
        }

        $query = OrderTms::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('is_executed', 0)->orWhereNull('is_executed');
            })
            ->with([
                'details' => function ($q) {
                    $q->whereNull('deleted_at')->with([
                        'parameterTms',
                        'permohonanUjiParameterKlinik.parametersatuanklinik',
                    ]);
                },
                'permohonanUjiKlinik.pasien',
            ])
            ->orderBy('created_at', 'asc');

        $sampleId = trim((string) $request->input('sample_id', $request->input('kode_barcode', '')));
        if ($sampleId !== '') {
            TmsKlinikHelper::applyBarcodeLookup($query, $sampleId);
        }

        if ($request->filled('tray')) {
            $query->where('tray', trim((string) $request->input('tray')));
        }

        $posFilter = trim((string) $request->input('pos', $request->input('posisi', '')));
        if ($posFilter !== '') {
            $query->where('pos', $posFilter);
        }

        $orders = $query->limit($limit)->get();

        $data = $orders->map(function ($order) {
            return $this->formatOrderPayload($order, false);
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Order belum tereksekusi',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Detail satu order (pending atau sudah executed).
     * GET /api/tms/orders/{id}
     */
    public function showOrder(Request $request, $id_order_tms)
    {
        $order = OrderTms::query()
            ->where('id_order_tms', $id_order_tms)
            ->whereNull('deleted_at')
            ->with([
                'details' => function ($q) {
                    $q->whereNull('deleted_at')->with([
                        'parameterTms',
                        'permohonanUjiParameterKlinik.parametersatuanklinik',
                    ]);
                },
                'permohonanUjiKlinik.pasien',
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOrderPayload($order, true),
        ]);
    }

    /**
     * Update value parameter order + tandai executed.
     *
     * POST /api/tms/orders/{id_order_tms}/execute
     * POST /api/tms/orders/execute  (cari via sample_id / id_order_tms di body)
     *
     * Body contoh:
     * {
     *   "sample_id": "20.07.2026/3836",
     *   "tanggal": "2026-08-08 10:00:00",
     *   "execute": true,
     *   "save_result": true,
     *   "results": [
     *     {"parameter_id": 2, "hasil": "98.5", "parameter_name": "Glukosa"},
     *     {"parameter_id": 3, "value": "28"}
     *   ]
     * }
     */
    public function executeOrder(Request $request, $id_order_tms = null)
    {
        try {
            $order = $this->resolveOrderForExecute($request, $id_order_tms);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan. Kirim id_order_tms atau sample_id/kode_barcode.',
                ], 404);
            }

            $results = $request->input('results', $request->input('parameters', []));
            if (!is_array($results) || empty($results)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field results/parameters wajib diisi (array).',
                ], 422);
            }

            $doExecute = $request->input('execute', true);
            $doExecute = filter_var($doExecute, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($doExecute === null) {
                $doExecute = true;
            }

            $saveResult = $request->input('save_result', true);
            $saveResult = filter_var($saveResult, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($saveResult === null) {
                $saveResult = true;
            }

            $resultDate = $request->input('tanggal', $request->input('result_date', now()->format('Y-m-d H:i:s')));
            $sampleId = trim((string) $request->input(
                'sample_id',
                $request->input('kode_barcode', $order->kode_barcode)
            ));
            if ($sampleId === '') {
                $sampleId = $order->kode_barcode;
            }

            $detailsByParam = $order->details->keyBy(function ($d) {
                return (int) $d->id_parameter_tms;
            });

            $updated = 0;
            $skipped = [];
            $updatedItems = [];

            DB::beginTransaction();

            foreach ($results as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $parameterId = (int) ($row['parameter_id'] ?? $row['id_parameter_tms'] ?? 0);
                if ($parameterId <= 0) {
                    $skipped[] = ['reason' => 'parameter_id kosong', 'row' => $row];
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
                    $skipped[] = [
                        'parameter_id' => $parameterId,
                        'reason' => 'value/hasil kosong',
                    ];
                    continue;
                }

                $value = TmsKlinikHelper::formatResultValue($rawValue);
                $parameterName = $row['parameter_name']
                    ?? $row['name_parameter_tms']
                    ?? optional(optional($detailsByParam->get($parameterId))->parameterTms)->name_parameter_tms
                    ?? null;

                /** @var OrderDetailTms|null $detail */
                $detail = $detailsByParam->get($parameterId);
                if (!$detail) {
                    $skipped[] = [
                        'parameter_id' => $parameterId,
                        'reason' => 'parameter tidak ada di order',
                    ];
                    continue;
                }

                $existingValue = trim((string) ($detail->value ?? ''));
                if ($existingValue !== '' && $existingValue !== '-') {
                    $skipped[] = [
                        'parameter_id' => $parameterId,
                        'reason' => 'parameter sudah terisi',
                    ];
                    continue;
                }

                $detail->value = $value;
                $detail->save();
                $updated++;

                $updatedItems[] = [
                    'id_orderdetail_tms' => $detail->id_orderdetail_tms,
                    'parameter_id' => $parameterId,
                    'parameter_name' => $parameterName,
                    'value' => $value,
                ];

                if ($saveResult) {
                    TmsResult::create([
                        'result_date' => $resultDate,
                        'sample_id' => $sampleId,
                        'parameter_id' => $parameterId,
                        'parameter_name' => $parameterName,
                        'patient_name' => $order->nama_pasien,
                        'result_value' => $value,
                    ]);
                }
            }

            if ($updated === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada value yang berhasil diupdate.',
                    'skipped' => $skipped,
                ], 422);
            }

            if ($doExecute) {
                $order->is_executed = 1;
                $order->executed_at = now();
                $order->save();
            }

            DB::commit();

            $order->load(['details' => function ($q) {
                $q->whereNull('deleted_at')->with('parameterTms');
            }]);

            return response()->json([
                'success' => true,
                'message' => $doExecute
                    ? ('Berhasil update ' . $updated . ' parameter dan order ditandai executed.')
                    : ('Berhasil update ' . $updated . ' parameter (belum di-execute).'),
                'updated_count' => $updated,
                'is_executed' => (bool) $order->is_executed,
                'updated' => $updatedItems,
                'skipped' => $skipped,
                'data' => $this->formatOrderPayload($order, true),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $id_order_tms
     * @return \Smt\Masterweb\Models\OrderTms|null
     */
    private function resolveOrderForExecute(Request $request, $id_order_tms = null)
    {
        $id = trim((string) ($id_order_tms ?: $request->input('id_order_tms', '')));
        $with = [
            'details' => function ($q) {
                $q->whereNull('deleted_at')->with([
                    'parameterTms',
                    'permohonanUjiParameterKlinik.parametersatuanklinik',
                ]);
            },
            'permohonanUjiKlinik.pasien',
        ];

        if ($id !== '') {
            return OrderTms::query()
                ->where('id_order_tms', $id)
                ->whereNull('deleted_at')
                ->with($with)
                ->first();
        }

        $sampleId = trim((string) $request->input('sample_id', $request->input('kode_barcode', '')));
        if ($sampleId === '') {
            return null;
        }

        $tray = TmsKlinikHelper::normalizeTrayPosValue($request->input('tray'));
        $pos = TmsKlinikHelper::normalizeTrayPosValue($request->input('pos', $request->input('posisi')));

        $resolved = TmsKlinikHelper::resolvePendingOrdersByBarcodeTrayPos(
            OrderTms::query()->whereNull('deleted_at'),
            $sampleId,
            $tray,
            $pos
        );

        $order = $resolved['orders']->first();
        if ($order) {
            return OrderTms::query()
                ->where('id_order_tms', $order->id_order_tms)
                ->whereNull('deleted_at')
                ->with($with)
                ->first();
        }

        return null;
    }

    /**
     * Payload HTTP TMS (GET/POST execute). Logika format ada di TmsOrderFormatter
     * agar MQTT memakai sumber yang sama.
     *
     * @param  \Smt\Masterweb\Models\OrderTms  $order
     * @param  bool  $includeValues
     * @return array
     */
    private function formatOrderPayload($order, $includeValues = true)
    {
        return TmsOrderFormatter::fromOrder($order, $includeValues);
    }
}
