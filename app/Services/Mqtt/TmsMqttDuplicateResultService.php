<?php

namespace App\Services\Mqtt;

use App\Models\MqttTmsDuplicateResult;
use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderTms;

/**
 * Kumpulkan parameter TMS yang muncul lebih dari sekali untuk satu sample_id,
 * bandingkan dengan tb_orderdetail_tms, lalu simpan sebagai riwayat.
 */
class TmsMqttDuplicateResultService
{
    /** @var \App\Services\Mqtt\TmsMqttSubscribeLogParser */
    protected $parser;

    public function __construct(TmsMqttSubscribeLogParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Kelompokkan hasil log per sample_id + parameter_id, sisakan yang muncul > 1 kali.
     *
     * @param  string  $path
     * @param  array  $options  since, sample, parameter, limit
     * @return array<int, array{sample_id:string, parameter_id:int, parameter_name:?string, hits:array}>
     */
    public function findDuplicates($path, array $options = [])
    {
        $entries = $this->parser->parseAll($path, [
            'since' => $options['since'] ?? null,
            'sample' => $options['sample'] ?? null,
            'limit' => $options['limit'] ?? 2000,
            'dedupe' => true,
        ]);

        $parameterFilter = (int) ($options['parameter'] ?? 0);
        $groups = [];

        foreach ($entries as $entry) {
            $sampleId = trim((string) ($entry['sample_id'] ?? ''));
            if ($sampleId === '') {
                continue;
            }

            foreach ($entry['results'] as $row) {
                if ($parameterFilter > 0 && $row['parameter_id'] !== $parameterFilter) {
                    continue;
                }

                $key = $sampleId . '|' . $row['parameter_id'];
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'sample_id' => $sampleId,
                        'parameter_id' => $row['parameter_id'],
                        'parameter_name' => $row['parameter_name'],
                        'hits' => [],
                    ];
                }
                if ($groups[$key]['parameter_name'] === null && $row['parameter_name'] !== null) {
                    $groups[$key]['parameter_name'] = $row['parameter_name'];
                }

                $groups[$key]['hits'][] = [
                    'received_at' => $entry['received_at'],
                    'value' => $row['value'],
                    'tray' => $entry['tray'],
                    'pos' => $entry['pos'],
                    'status' => $entry['log_status'],
                ];
            }
        }

        $groups = array_values(array_filter($groups, function ($group) {
            return count($group['hits']) > 1;
        }));

        foreach ($groups as &$group) {
            usort($group['hits'], function ($a, $b) {
                return strcmp((string) $a['received_at'], (string) $b['received_at']);
            });
        }
        unset($group);

        usort($groups, function ($a, $b) {
            $cmp = strcmp($a['sample_id'], $b['sample_id']);

            return $cmp !== 0 ? $cmp : ($a['parameter_id'] <=> $b['parameter_id']);
        });

        return $groups;
    }

    /**
     * Simpan satu grup duplikat sebagai baris riwayat (satu baris per kemunculan).
     *
     * @param  array  $group
     * @return array{verdict:string, db_slots:int, db_filled:int, rows:int}
     */
    public function record(array $group)
    {
        $state = $this->inspectDatabase($group);
        $total = count($group['hits']);
        $now = now();

        foreach ($group['hits'] as $index => $hit) {
            $occurrence = $index + 1;

            MqttTmsDuplicateResult::query()->updateOrCreate(
                ['entry_key' => $this->entryKey($group, $hit)],
                [
                    'sample_id' => $group['sample_id'],
                    'parameter_id' => $group['parameter_id'],
                    'parameter_name' => $group['parameter_name'],
                    'occurrence' => $occurrence,
                    'total_occurrence' => $total,
                    'duplicate_type' => $state['duplicate_type'],
                    'distinct_count' => $state['distinct_count'],
                    'gap_minutes' => $this->gapMinutes($group['hits'], $index),
                    'label' => $this->occurrenceLabel(
                        $group['parameter_id'],
                        $occurrence,
                        $state['duplicate_type']
                    ),
                    'received_at' => $this->parseDate($hit['received_at']),
                    'value' => $hit['value'],
                    'tray' => $hit['tray'],
                    'pos' => $hit['pos'],
                    'log_status' => $hit['status'],
                    'db_slots' => $state['db_slots'],
                    'db_filled' => $state['db_filled'],
                    'verdict' => $state['verdict'],
                    'scanned_at' => $now,
                ]
            );
        }

        return $state + ['rows' => $total];
    }

    /**
     * Satu pemeriksaan dihitung unik dari kombinasi nilai + tray + pos.
     * Kiriman ulang alat menghasilkan kombinasi yang sama persis.
     *
     * @param  array  $hits
     * @return array{duplicate_type:string, distinct_count:int, distinct_values:array}
     */
    public function classifyHits(array $hits)
    {
        $signatures = [];
        $values = [];

        foreach ($hits as $hit) {
            if ($hit['value'] === null || $hit['value'] === '') {
                continue;
            }
            $signatures[implode('|', [
                (string) $hit['value'],
                (string) $hit['tray'],
                (string) $hit['pos'],
            ])] = true;
            $values[] = $hit['value'];
        }

        $distinct = count($signatures);

        return [
            'duplicate_type' => $distinct > 1
                ? MqttTmsDuplicateResult::TYPE_PEMERIKSAAN_BERBEDA
                : MqttTmsDuplicateResult::TYPE_KIRIMAN_ULANG,
            'distinct_count' => $distinct,
            'distinct_values' => array_values(array_unique($values)),
        ];
    }

    /**
     * Selisih menit terhadap kemunculan pertama.
     *
     * @param  array  $hits
     * @param  int  $index
     * @return int|null
     */
    protected function gapMinutes(array $hits, $index)
    {
        if ($index === 0) {
            return 0;
        }

        $first = $this->parseDate($hits[0]['received_at'] ?? null);
        $current = $this->parseDate($hits[$index]['received_at'] ?? null);
        if (!$first || !$current) {
            return null;
        }

        return (int) abs($current->diffInMinutes($first));
    }

    /**
     * Bandingkan hasil log dengan slot parameter di tb_orderdetail_tms.
     *
     * @param  array  $group
     * @return array{verdict:string, db_slots:int, db_filled:int, db_values:array, duplicate_type:string, distinct_count:int}
     */
    public function inspectDatabase(array $group)
    {
        $classification = $this->classifyHits($group['hits']);
        $aliasWasOn = TmsKlinikHelper::plasmaSampleDigitAlias();
        TmsKlinikHelper::setPlasmaSampleDigitAlias(true);

        try {
            $orders = TmsKlinikHelper::fetchOrdersMatchingSampleId(
                OrderTms::query()->whereNull('deleted_at'),
                $group['sample_id']
            );
        } finally {
            TmsKlinikHelper::setPlasmaSampleDigitAlias($aliasWasOn);
        }

        if ($orders->isEmpty()) {
            return [
                'verdict' => MqttTmsDuplicateResult::VERDICT_TANPA_ORDER,
                'db_slots' => 0,
                'db_filled' => 0,
                'db_values' => [],
                'duplicate_type' => $classification['duplicate_type'],
                'distinct_count' => $classification['distinct_count'],
            ];
        }

        $slots = 0;
        $filledValues = [];

        foreach ($orders as $order) {
            foreach ($order->details as $detail) {
                if ((int) $detail->id_parameter_tms !== (int) $group['parameter_id']) {
                    continue;
                }
                $slots++;
                $value = trim((string) ($detail->value ?? ''));
                if (!TmsKlinikHelper::detailValueIsEmpty($value)) {
                    $filledValues[] = $value;
                }
            }
        }

        return [
            'verdict' => $this->verdict($slots, $filledValues, $classification),
            'db_slots' => $slots,
            'db_filled' => count($filledValues),
            'db_values' => $filledValues,
            'duplicate_type' => $classification['duplicate_type'],
            'distinct_count' => $classification['distinct_count'],
        ];
    }

    /**
     * Penilaian memakai jumlah pemeriksaan unik, bukan jumlah pesan.
     * Kiriman ulang alat tidak butuh slot tambahan.
     *
     * @param  int  $slots
     * @param  array  $filledValues
     * @param  array  $classification
     * @return string
     */
    protected function verdict($slots, array $filledValues, array $classification)
    {
        if ($slots === 0) {
            return MqttTmsDuplicateResult::VERDICT_TIDAK_ADA_SLOT;
        }

        $needed = max(1, (int) $classification['distinct_count']);

        if ($needed > $slots) {
            return MqttTmsDuplicateResult::VERDICT_SLOT_KURANG;
        }

        if (count($filledValues) < $needed) {
            return MqttTmsDuplicateResult::VERDICT_BELUM_MASUK;
        }

        $distinctLogValues = $classification['distinct_values'];
        if (count($distinctLogValues) > 1 && count(array_unique($filledValues)) === 1) {
            return MqttTmsDuplicateResult::VERDICT_TERTUMPUK;
        }

        return MqttTmsDuplicateResult::VERDICT_OK;
    }

    /**
     * @param  int  $parameterId
     * @param  int  $occurrence
     * @param  string|null  $duplicateType
     * @return string
     */
    public function occurrenceLabel($parameterId, $occurrence, $duplicateType = null)
    {
        if ($duplicateType === MqttTmsDuplicateResult::TYPE_KIRIMAN_ULANG) {
            return $occurrence === 1 ? 'Hasil asli' : 'Kiriman ulang ke-' . ($occurrence - 1);
        }

        if ((int) $parameterId === 2) {
            if ($occurrence === 1) {
                return 'Gula darah puasa';
            }
            if ($occurrence === 2) {
                return 'Gula darah 2 jam PP';
            }
        }

        return 'Pengambilan ke-' . $occurrence;
    }

    /**
     * @param  array  $group
     * @param  array  $hit
     * @return string
     */
    protected function entryKey(array $group, array $hit)
    {
        return sha1(implode('|', [
            $group['sample_id'],
            $group['parameter_id'],
            (string) $hit['received_at'],
            (string) $hit['value'],
        ]));
    }

    /**
     * @param  string|null  $raw
     * @return \Carbon\Carbon|null
     */
    protected function parseDate($raw)
    {
        if (empty($raw)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param  array  $filters  sample, parameter, verdict, since, limit
     * @return \Illuminate\Support\Collection
     */
    public function history(array $filters = [])
    {
        $query = MqttTmsDuplicateResult::query()
            ->orderBy('sample_id')
            ->orderBy('parameter_id')
            ->orderBy('occurrence');

        if (!empty($filters['sample'])) {
            $query->where('sample_id', $filters['sample']);
        }
        if (!empty($filters['parameter'])) {
            $query->where('parameter_id', (int) $filters['parameter']);
        }
        if (!empty($filters['verdict'])) {
            $query->where('verdict', $filters['verdict']);
        }
        if (!empty($filters['since'])) {
            $query->whereDate('received_at', '>=', $filters['since']);
        }

        $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 500;

        return $query->limit($limit)->get();
    }
}
