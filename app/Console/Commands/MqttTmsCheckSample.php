<?php

namespace App\Console\Commands;

use App\Services\Mqtt\TmsMqttSubscribeLogParser;
use Illuminate\Console\Command;
use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderTms;

/**
 * Bandingkan hasil di log MQTT dengan isi tb_orderdetail_tms.
 * Tujuan: deteksi hasil yang hilang atau saling menimpa (tertumpuk).
 */
class MqttTmsCheckSample extends Command
{
    protected $signature = 'mqtt:tms-check-sample
        {sample?* : Satu atau beberapa sample_id / barcode}
        {--file= : Path log subscriber (default: storage/logs/mqtt-tms-subscribe.log)}
        {--since= : Hanya entri log sejak tanggal YYYY-MM-DD}
        {--parameter= : Fokus pada parameter_id tertentu}
        {--limit=2000 : Maksimum message log yang dibaca}
        {--duplicates-only : Periksa semua sample yang punya parameter dobel di log}';

    protected $description = 'Cek apakah hasil MQTT sudah masuk benar ke order TMS atau saling tertumpuk';

    public function handle(TmsMqttSubscribeLogParser $parser)
    {
        $path = (string) ($this->option('file') ?: storage_path('logs/mqtt-tms-subscribe.log'));

        if (!is_file($path)) {
            $this->error('Log tidak ditemukan: ' . $path);

            return 1;
        }

        $entries = $parser->parseAll($path, [
            'since' => $this->option('since'),
            'limit' => (int) $this->option('limit'),
            'dedupe' => true,
        ]);

        if (empty($entries)) {
            $this->warn('Tidak ada message pada log: ' . $path);

            return 0;
        }

        $parameterFilter = (int) $this->option('parameter');
        $logBySample = $this->groupLogBySample($entries, $parameterFilter);

        $samples = $this->argument('sample');
        if (empty($samples)) {
            if (!$this->option('duplicates-only')) {
                $this->error('Sebutkan sample_id, atau pakai --duplicates-only.');

                return 1;
            }
            $samples = $this->samplesWithDuplicates($logBySample);
        }

        if (empty($samples)) {
            $this->info('Tidak ada sample dengan parameter dobel pada log ini.');

            return 0;
        }

        TmsKlinikHelper::setPlasmaSampleDigitAlias(true);
        $problems = 0;

        try {
            foreach ($samples as $sampleId) {
                $problems += $this->checkSample((string) $sampleId, $logBySample[$sampleId] ?? []);
            }
        } finally {
            TmsKlinikHelper::setPlasmaSampleDigitAlias(false);
        }

        $this->line('');
        if ($problems > 0) {
            $this->warn($problems . ' parameter perlu ditinjau (hasil hilang atau kemungkinan tertumpuk).');
        } else {
            $this->info('Semua hasil di log sudah tercermin di database.');
        }

        return $problems > 0 ? 1 : 0;
    }

    /**
     * @param  array  $entries
     * @param  int  $parameterFilter
     * @return array<string, array<int, array>>
     */
    protected function groupLogBySample(array $entries, $parameterFilter = 0)
    {
        $bySample = [];

        foreach ($entries as $entry) {
            $sampleId = (string) ($entry['sample_id'] ?? '');
            if ($sampleId === '') {
                continue;
            }

            foreach ($entry['results'] as $row) {
                if ($parameterFilter > 0 && $row['parameter_id'] !== $parameterFilter) {
                    continue;
                }

                $bySample[$sampleId][] = [
                    'parameter_id' => $row['parameter_id'],
                    'parameter_name' => $row['parameter_name'],
                    'value' => $row['value'],
                    'received_at' => $entry['received_at'],
                    'tray' => $entry['tray'],
                    'pos' => $entry['pos'],
                    'status' => $entry['log_status'],
                ];
            }
        }

        foreach ($bySample as &$rows) {
            usort($rows, function ($a, $b) {
                return strcmp((string) $a['received_at'], (string) $b['received_at']);
            });
        }
        unset($rows);

        return $bySample;
    }

    /**
     * @param  array<string, array>  $logBySample
     * @return string[]
     */
    protected function samplesWithDuplicates(array $logBySample)
    {
        $out = [];

        foreach ($logBySample as $sampleId => $rows) {
            $counts = [];
            foreach ($rows as $row) {
                $pid = $row['parameter_id'];
                $counts[$pid] = ($counts[$pid] ?? 0) + 1;
            }
            foreach ($counts as $count) {
                if ($count > 1) {
                    $out[] = (string) $sampleId;
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * @param  string  $sampleId
     * @param  array  $logRows
     * @return int  jumlah parameter bermasalah
     */
    protected function checkSample($sampleId, array $logRows)
    {
        $this->line('');
        $this->line('==============================');
        $this->info('Sample ' . $sampleId);

        $orders = TmsKlinikHelper::fetchOrdersMatchingSampleId(
            OrderTms::query()->whereNull('deleted_at'),
            $sampleId
        );

        if ($orders->isEmpty()) {
            $this->error('Tidak ada order TMS di database untuk barcode ini.');

            return count($logRows) > 0 ? 1 : 0;
        }

        $orderRows = [];
        $dbValues = [];

        foreach ($orders as $order) {
            foreach ($order->details as $detail) {
                $pid = (int) $detail->id_parameter_tms;
                $value = trim((string) ($detail->value ?? ''));
                $filled = !TmsKlinikHelper::detailValueIsEmpty($value);

                $orderRows[] = [
                    substr((string) $order->id_order_tms, 0, 8),
                    (string) ($order->jenis_sampel ?: '-'),
                    'T' . ($order->tray ?: '-') . '/P' . ($order->pos ?: '-'),
                    $pid,
                    $filled ? $value : '-',
                    optional($order->created_at)->format('H:i:s') ?: '-',
                ];

                if (!isset($dbValues[$pid])) {
                    $dbValues[$pid] = ['slots' => 0, 'filled' => []];
                }
                $dbValues[$pid]['slots']++;
                if ($filled) {
                    $dbValues[$pid]['filled'][] = $value;
                }
            }
        }

        $this->table(
            ['Order', 'Jenis', 'Tray/Pos', 'Param', 'Nilai DB', 'Dibuat'],
            $orderRows
        );

        if (empty($logRows)) {
            $this->line('Tidak ada hasil untuk sample ini pada rentang log yang dibaca.');

            return 0;
        }

        $logByParam = [];
        foreach ($logRows as $row) {
            $logByParam[$row['parameter_id']][] = $row;
        }

        $problems = 0;
        $compare = [];

        foreach ($logByParam as $pid => $rows) {
            $logValues = array_values(array_filter(array_map(function ($r) {
                return $r['value'];
            }, $rows), function ($v) {
                return $v !== null && $v !== '';
            }));

            $slots = $dbValues[$pid]['slots'] ?? 0;
            $filled = $dbValues[$pid]['filled'] ?? [];

            $verdict = 'OK';
            if ($slots === 0) {
                $verdict = 'PARAMETER TIDAK ADA DI ORDER';
                $problems++;
            } elseif (count($filled) < count($logValues) && count($logValues) > $slots) {
                $verdict = 'SLOT KURANG (' . count($logValues) . ' hasil, ' . $slots . ' slot)';
                $problems++;
            } elseif (count($filled) < count($logValues)) {
                $verdict = 'ADA HASIL BELUM MASUK';
                $problems++;
            } elseif (count(array_unique($logValues)) > 1 && count(array_unique($filled)) === 1) {
                $verdict = 'KEMUNGKINAN TERTUMPUK';
                $problems++;
            }

            $compare[] = [
                $pid,
                count($rows),
                implode(' , ', $logValues) ?: '-',
                $slots,
                implode(' , ', $filled) ?: '-',
                $verdict,
            ];
        }

        $this->table(
            ['Param', 'Hasil di log', 'Nilai log', 'Slot DB', 'Nilai DB terisi', 'Kesimpulan'],
            $compare
        );

        return $problems;
    }
}
