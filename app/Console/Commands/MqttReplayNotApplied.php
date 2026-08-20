<?php

namespace App\Console\Commands;

use App\Models\MqttTmsReplayHistory;
use App\Services\Mqtt\TmsMqttReplayHistoryService;
use App\Services\Mqtt\TmsMqttResultHandler;
use App\Services\Mqtt\TmsMqttSubscribeLogParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Helpers\TmsKlinikHelper;

class MqttReplayNotApplied extends Command
{
    protected $signature = 'mqtt:replay-not-applied
        {--file= : Path log subscriber (default: storage/logs/mqtt-tms-subscribe.log)}
        {--dry-run : Tampilkan entri tanpa menulis ke database}
        {--since= : Hanya entri sejak tanggal YYYY-MM-DD}
        {--sample= : Filter sample_id / barcode}
        {--limit=500 : Maksimum entri yang diproses (default 500)}
        {--no-dedupe : Proses ulang pesan duplikat message_id}
        {--include-errors : Sertakan blok APPLY ERROR selain NOT APPLIED}
        {--history : Tampilkan riwayat replay (tidak eksekusi)}
        {--status= : Filter riwayat: applied, already_filled, not_applied}
        {--force : Eksekusi ulang meski sudah ada di riwayat}';

    protected $description = 'Eksekusi ulang payload MQTT yang NOT APPLIED dari log subscriber';

    public function handle(
        TmsMqttSubscribeLogParser $parser,
        TmsMqttResultHandler $handler,
        TmsMqttReplayHistoryService $history
    ) {
        if ($this->option('history')) {
            return $this->showHistory($history);
        }

        if (!Schema::hasTable('tb_mqtt_tms_replay_history')) {
            $this->warn('Tabel tb_mqtt_tms_replay_history belum ada. Jalankan: php artisan migrate');
        }

        $path = (string) ($this->option('file') ?: storage_path('logs/mqtt-tms-subscribe.log'));
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (!is_file($path)) {
            $this->error('Log tidak ditemukan: ' . $path);
            $this->line('Pastikan daemon mqtt:tms-subscribe menulis ke file ini (nohup redirect).');

            return 1;
        }

        $entries = $parser->parseNotApplied($path, [
            'since' => $this->option('since'),
            'sample' => $this->option('sample'),
            'limit' => (int) $this->option('limit'),
            'dedupe' => !$this->option('no-dedupe'),
            'include_errors' => (bool) $this->option('include-errors'),
        ]);

        if (empty($entries)) {
            $this->warn('Tidak ada entri NOT APPLIED di log: ' . $path);

            return 0;
        }

        $entryKeys = array_map(function ($entry) use ($history) {
            return $history->entryKey($entry);
        }, $entries);
        $processedMap = Schema::hasTable('tb_mqtt_tms_replay_history')
            ? $history->processedMap($entryKeys)
            : [];

        $alreadySucceeded = array_filter($processedMap ?? [], fn($s) => $history->shouldSkip($s));
        $willRetry = array_filter($processedMap ?? [], fn($s) => !$history->shouldSkip($s) && $s !== null);

        $this->info('Ditemukan ' . count($entries) . ' entri NOT APPLIED di log.');
        $this->line('Mode alias: Plasma & Plasma NaF → digit barcode 3 (sementara).');
        if (!empty($alreadySucceeded)) {
            $this->line(count($alreadySucceeded) . ' entri akan dilewati (sudah applied sebelumnya).');
        }
        if (!empty($willRetry)) {
            $this->line(count($willRetry) . ' entri akan dicoba ulang (sebelumnya not_applied).');
        }
        if ($dryRun) {
            $this->warn('Mode --dry-run: tidak menulis ke database / riwayat.');
        }
        if ($force) {
            $this->warn('--force aktif: semua entri dieksekusi ulang termasuk yang sudah applied.');
        }

        TmsKlinikHelper::setPlasmaSampleDigitAlias(true);

        $applied = 0;
        $failed = 0;
        $skipped = 0;

        try {
            foreach ($entries as $index => $entry) {
                $no = $index + 1;
                $sampleId = (string) ($entry['sample_id'] ?? '-');
                $receivedAt = (string) ($entry['received_at'] ?? '-');
                $logError = (string) ($entry['log_error'] ?? '-');
                $entryKey = $history->entryKey($entry);

                $this->line('');
                $this->line("--- [{$no}/" . count($entries) . "] {$receivedAt} sample={$sampleId} ---");
                $this->line('Log error : ' . $logError);

                $prevStatus = $processedMap[$entryKey] ?? null;
                if (!$force && $history->shouldSkip($prevStatus)) {
                    $skipped++;
                    $this->line('SKIP      : sudah applied sebelumnya (status=' . $prevStatus . ').');
                    continue;
                }

                if ($dryRun) {
                    $data = isset($entry['payload']['data']) && is_array($entry['payload']['data'])
                        ? $entry['payload']['data']
                        : ($entry['payload'] ?? []);
                    $tray = TmsKlinikHelper::normalizeTrayPosValue($data['tray'] ?? null);
                    $pos = TmsKlinikHelper::normalizeTrayPosValue($data['pos'] ?? $data['posisi'] ?? null);
                    $this->line('Tray/pos   : tray=' . ($tray ?? '-') . ' pos=' . ($pos ?? '-'));
                    $this->line('Message ID : ' . ($entry['message_id'] ?? '-'));
                    continue;
                }

                try {
                    $report = $handler->apply([
                        'topic' => $entry['topic'] ?: (string) config('mqtt.topics.tms.result'),
                        'raw' => $entry['raw'],
                        'payload' => $entry['payload'],
                        'received_at' => $entry['received_at'],
                        'replayed_from_log' => true,
                    ]);

                    if (Schema::hasTable('tb_mqtt_tms_replay_history')) {
                        $history->record($entry, $report);
                    }

                    if (!empty($report['applied'])) {
                        $applied++;
                        if (!empty($report['already_applied'])) {
                            $this->info(sprintf(
                                'APPLIED (sudah terisi) : sample %s param sudah cocok di order %s.',
                                $sampleId,
                                (string) ($report['id_order_tms'] ?? '-')
                            ));
                        } else {
                            $this->info(sprintf(
                                'APPLIED : %d value (order %s, via %s).',
                                (int) $report['updated'],
                                (string) ($report['id_order_tms'] ?? '-'),
                                (string) ($report['matched_by'] ?? '-')
                            ));
                        }
                    } else {
                        $failed++;
                        $this->warn('STILL NOT APPLIED : ' . ($report['error'] ?: 'tidak ada perubahan.'));
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error('REPLAY ERROR : ' . $e->getMessage());
                    if (Schema::hasTable('tb_mqtt_tms_replay_history')) {
                        $history->record($entry, [
                            'applied' => false,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } finally {
            TmsKlinikHelper::setPlasmaSampleDigitAlias(false);
        }

        $this->line('');
        if ($dryRun) {
            $this->info('Dry-run selesai. Jalankan tanpa --dry-run untuk menerapkan ke database.');
        } else {
            $this->info("Selesai: {$applied} applied, {$failed} gagal, {$skipped} dilewati (sudah pernah replay).");
            $this->line('Riwayat lengkap: php artisan mqtt:replay-not-applied --history');
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * @param  \App\Services\Mqtt\TmsMqttReplayHistoryService  $history
     * @return int
     */
    protected function showHistory(TmsMqttReplayHistoryService $history)
    {
        if (!Schema::hasTable('tb_mqtt_tms_replay_history')) {
            $this->error('Tabel tb_mqtt_tms_replay_history belum ada. Jalankan: php artisan migrate');

            return 1;
        }

        $rows = $history->listRecent([
            'since' => $this->option('since'),
            'sample' => $this->option('sample'),
            'status' => $this->option('status'),
            'limit' => (int) $this->option('limit'),
        ]);

        if ($rows->isEmpty()) {
            $this->warn('Belum ada riwayat replay.');

            return 0;
        }

        $summary = $history->summarize($rows);
        $this->info(sprintf(
            'Riwayat replay (applied=%d, sudah terisi=%d, gagal=%d):',
            $summary['applied'],
            $summary['already_filled'],
            $summary['not_applied']
        ));

        $tableRows = $rows->map(function (MqttTmsReplayHistory $row) {
            $statusLabel = $row->status;
            if ($statusLabel === MqttTmsReplayHistory::STATUS_APPLIED) {
                $statusLabel = 'applied';
            } elseif ($statusLabel === MqttTmsReplayHistory::STATUS_ALREADY_FILLED) {
                $statusLabel = 'sudah terisi';
            } elseif ($statusLabel === MqttTmsReplayHistory::STATUS_NOT_APPLIED) {
                $statusLabel = 'belum applied';
            }

            $error = $row->replay_error ?: $row->log_error ?: '-';
            if (strlen($error) > 80) {
                $error = substr($error, 0, 77) . '...';
            }

            return [
                optional($row->log_received_at)->format('Y-m-d H:i:s') ?: '-',
                $row->sample_id ?: '-',
                'T' . ($row->tray ?: '-') . '/P' . ($row->pos ?: '-'),
                $statusLabel,
                (int) $row->updated_count,
                $error,
            ];
        })->all();

        $this->table(
            ['Log waktu', 'Sample', 'Tray/Pos', 'Status', 'Updated', 'Keterangan'],
            $tableRows
        );

        $this->line('');
        $this->line('Entri di riwayat tidak akan dieksekusi lagi saat replay (kecuali --force).');

        return 0;
    }
}
