<?php

namespace App\Console\Commands;

use App\Models\MqttTmsDuplicateResult;
use App\Services\Mqtt\TmsMqttDuplicateResultService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Cari parameter yang dikirim alat lebih dari sekali untuk satu sample_id,
 * lalu simpan sebagai riwayat. Contoh: Glukosa (id 2) dua kali =
 * gula darah puasa lalu 2 jam PP.
 */
class MqttTmsLogDuplicates extends Command
{
    protected $signature = 'mqtt:tms-log-duplicates
        {--file= : Path log subscriber (default: storage/logs/mqtt-tms-subscribe.log)}
        {--since= : Hanya entri sejak tanggal YYYY-MM-DD}
        {--sample= : Filter sample_id / barcode}
        {--parameter= : Filter parameter_id (mis. 2 untuk Glukosa)}
        {--limit=2000 : Maksimum message log yang dibaca}
        {--no-save : Jangan simpan hasil pindai ke tabel riwayat}
        {--history : Tampilkan riwayat tersimpan, tanpa memindai log}
        {--verdict= : Filter riwayat berdasarkan kesimpulan}';

    protected $description = 'Deteksi dan catat parameter TMS yang masuk lebih dari sekali per sample_id';

    public function handle(TmsMqttDuplicateResultService $service)
    {
        $hasTable = Schema::hasTable('tb_mqtt_tms_duplicate_results');

        if ($this->option('history')) {
            return $this->showHistory($service, $hasTable);
        }

        $path = (string) ($this->option('file') ?: storage_path('logs/mqtt-tms-subscribe.log'));

        if (!is_file($path)) {
            $this->error('Log tidak ditemukan: ' . $path);

            return 1;
        }

        $groups = $service->findDuplicates($path, [
            'since' => $this->option('since'),
            'sample' => $this->option('sample'),
            'parameter' => $this->option('parameter'),
            'limit' => (int) $this->option('limit'),
        ]);

        if (empty($groups)) {
            $this->info('Tidak ada parameter yang masuk lebih dari sekali pada log ini.');

            return 0;
        }

        $save = !$this->option('no-save');
        if ($save && !$hasTable) {
            $this->warn('Tabel tb_mqtt_tms_duplicate_results belum ada. Jalankan: php artisan migrate');
            $save = false;
        }

        $this->info(count($groups) . ' kombinasi sample + parameter dengan lebih dari satu hasil:');

        $rows = [];
        $flagged = 0;

        foreach ($groups as $group) {
            $state = $save
                ? $service->record($group)
                : $service->inspectDatabase($group);

            if ($state['verdict'] !== MqttTmsDuplicateResult::VERDICT_OK) {
                $flagged++;
            }

            foreach ($group['hits'] as $index => $hit) {
                $occurrence = $index + 1;

                $rows[] = [
                    $group['sample_id'],
                    $group['parameter_id'] . ' ' . ($group['parameter_name'] ?: ''),
                    $occurrence,
                    $hit['received_at'] ?: '-',
                    $hit['value'] ?: '-',
                    'T' . ($hit['tray'] ?: '-') . '/P' . ($hit['pos'] ?: '-'),
                    $hit['status'],
                    $this->typeLabel($state['duplicate_type']),
                    $service->occurrenceLabel(
                        $group['parameter_id'],
                        $occurrence,
                        $state['duplicate_type']
                    ),
                    $state['db_filled'] . '/' . $state['db_slots'],
                    $state['verdict'],
                ];
            }
        }

        $this->table($this->tableHeaders(), $rows);

        $this->line('');
        $this->line('Kolom DB berisi jumlah slot terisi / total slot parameter itu di tb_orderdetail_tms.');
        if ($flagged > 0) {
            $this->warn($flagged . ' kombinasi perlu ditinjau (hasil hilang atau kemungkinan tertumpuk).');
        } else {
            $this->info('Semua hasil dobel sudah tercermin benar di database.');
        }
        if ($save) {
            $this->line('Riwayat tersimpan. Lihat lagi: php artisan mqtt:tms-log-duplicates --history');
        }

        return $flagged > 0 ? 1 : 0;
    }

    /**
     * @param  \App\Services\Mqtt\TmsMqttDuplicateResultService  $service
     * @param  bool  $hasTable
     * @return int
     */
    protected function showHistory(TmsMqttDuplicateResultService $service, $hasTable)
    {
        if (!$hasTable) {
            $this->error('Tabel tb_mqtt_tms_duplicate_results belum ada. Jalankan: php artisan migrate');

            return 1;
        }

        $records = $service->history([
            'sample' => $this->option('sample'),
            'parameter' => $this->option('parameter'),
            'verdict' => $this->option('verdict'),
            'since' => $this->option('since'),
            'limit' => (int) $this->option('limit'),
        ]);

        if ($records->isEmpty()) {
            $this->warn('Belum ada riwayat parameter dobel.');

            return 0;
        }

        $this->info('Riwayat parameter dobel (' . $records->count() . ' baris):');

        $rows = $records->map(function (MqttTmsDuplicateResult $row) {
            return [
                $row->sample_id,
                $row->parameter_id . ' ' . ($row->parameter_name ?: ''),
                $row->occurrence . '/' . $row->total_occurrence,
                optional($row->received_at)->format('Y-m-d H:i:s') ?: '-',
                $row->value ?: '-',
                'T' . ($row->tray ?: '-') . '/P' . ($row->pos ?: '-'),
                $row->log_status ?: '-',
                $this->typeLabel($row->duplicate_type),
                $row->label ?: '-',
                $row->db_filled . '/' . $row->db_slots,
                $row->verdict ?: '-',
            ];
        })->all();

        $this->table($this->tableHeaders(), $rows);

        $summary = $records->groupBy('verdict')->map->count();
        $this->line('');
        foreach ($summary as $verdict => $count) {
            $this->line(($verdict ?: '-') . ' : ' . $count . ' baris');
        }

        return 0;
    }

    /**
     * @return string[]
     */
    protected function tableHeaders()
    {
        return [
            'Sample',
            'Parameter',
            'Ke-',
            'Waktu',
            'Nilai',
            'Tray/Pos',
            'Status log',
            'Jenis',
            'Interpretasi',
            'DB',
            'Kesimpulan',
        ];
    }

    /**
     * @param  string|null  $type
     * @return string
     */
    protected function typeLabel($type)
    {
        if ($type === MqttTmsDuplicateResult::TYPE_KIRIMAN_ULANG) {
            return 'Kiriman ulang';
        }
        if ($type === MqttTmsDuplicateResult::TYPE_PEMERIKSAAN_BERBEDA) {
            return 'Pemeriksaan beda';
        }

        return '-';
    }
}
