<?php

namespace App\Console\Commands;

use App\Services\Mqtt\TmsMqttSubscribeLogParser;
use Illuminate\Console\Command;
use Smt\Masterweb\Helpers\TmsKlinikHelper;
use Smt\Masterweb\Models\OrderTms;

/**
 * Cek apakah pada satu hari ada dua hasil Glukosa untuk pasien yang sama,
 * termasuk bila dikirim dengan sample_id / barcode yang berbeda
 * (mis. tabung Plasma dan Plasma NaF).
 */
class TmsCekGlukosaGanda extends Command
{
    protected $signature = 'tms:cek-glukosa-ganda
        {--date= : Tanggal YYYY-MM-DD (default hari ini)}
        {--all-dates : Abaikan filter tanggal, pindai seluruh log}
        {--parameter=2 : parameter_id yang diperiksa (2 = Glukosa)}
        {--rekap : Tampilkan semua hasil parameter ini, bukan hanya yang dobel}
        {--file= : Path log subscriber}
        {--limit=5000 : Maksimum message log yang dibaca}';

    protected $description = 'Cek dua hasil Glukosa dalam satu hari untuk pasien yang sama, termasuk beda sample_id';

    public function handle(TmsMqttSubscribeLogParser $parser)
    {
        $path = (string) ($this->option('file') ?: storage_path('logs/mqtt-tms-subscribe.log'));

        if (!is_file($path)) {
            $this->error('Log tidak ditemukan: ' . $path);

            return 1;
        }

        $date = $this->option('all-dates')
            ? null
            : trim((string) ($this->option('date') ?: now()->format('Y-m-d')));
        $parameterId = (int) $this->option('parameter') ?: 2;

        $entries = $parser->parseAll($path, [
            'limit' => (int) $this->option('limit'),
            'dedupe' => true,
        ]);

        if ($this->option('rekap')) {
            return $this->showRekap($entries, $parameterId, $date);
        }

        $groups = $this->groupByPatient($entries, $parameterId, $date);

        if (empty($groups)) {
            $this->info($date
                ? 'Tidak ada pasien dengan dua hasil parameter ' . $parameterId . ' pada ' . $date . '.'
                : 'Tidak ada pasien dengan dua hasil parameter ' . $parameterId . ' pada log ini.');

            return 0;
        }

        $this->info(count($groups) . ' pasien dengan lebih dari satu hasil parameter ' . $parameterId
            . ($date ? ' pada ' . $date : '') . ':');

        $bedaSample = 0;

        foreach ($groups as $group) {
            $this->line('');
            $this->line('------------------------------');

            $sampleIds = array_values(array_unique(array_map(function ($hit) {
                return $hit['sample_id'];
            }, $group['hits'])));

            $header = 'Pasien ' . $group['base'] . ' — ' . count($group['hits']) . ' hasil';
            if (count($sampleIds) > 1) {
                $bedaSample++;
                $header .= ' | BEDA SAMPLE_ID (' . implode(', ', $sampleIds) . ')';
            } else {
                $header .= ' | sample_id sama (' . $sampleIds[0] . ')';
            }
            $this->line($header);

            $rows = [];
            foreach ($group['hits'] as $index => $hit) {
                $rows[] = [
                    $index + 1,
                    $hit['sample_id'],
                    $hit['received_at'] ?: '-',
                    $hit['value'] ?: '-',
                    'T' . ($hit['tray'] ?: '-') . '/P' . ($hit['pos'] ?: '-'),
                    $hit['jenis_barcode'],
                    $hit['jenis_db'] ?: '-',
                    $hit['status'],
                    $this->label($parameterId, $index + 1),
                ];
            }

            $this->table(
                ['Ke-', 'Sample', 'Waktu', 'Nilai', 'Tray/Pos', 'Jenis (barcode)', 'Jenis (DB)', 'Status log', 'Interpretasi'],
                $rows
            );

            foreach ($this->notes($group, $sampleIds) as $note) {
                $this->line($note);
            }
        }

        $this->line('');
        if ($bedaSample > 0) {
            $this->warn($bedaSample . ' pasien menerima hasil dari sample_id berbeda. '
                . 'Pastikan tiap tabung memang punya order sendiri.');
        }
        $this->line('Urutan waktu menentukan makna: hasil pertama = puasa, kedua = 2 jam PP.');

        return 0;
    }

    /**
     * Kelompokkan hasil per pasien (9 digit awal barcode), bukan per sample_id,
     * agar tabung dengan digit jenis berbeda tetap masuk satu kelompok.
     *
     * @param  array  $entries
     * @param  int  $parameterId
     * @param  string|null  $date
     * @return array<int, array{base:string, hits:array}>
     */
    protected function groupByPatient(array $entries, $parameterId, $date)
    {
        $groups = [];

        foreach ($this->collectHits($entries, $parameterId, $date) as $hit) {
            $base = $hit['base'];
            $groups[$base]['base'] = $base;
            $groups[$base]['hits'][] = $hit;
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
            return strcmp($a['base'], $b['base']);
        });

        return $groups;
    }

    /**
     * Ambil semua hasil parameter tertentu dari log, terurut waktu.
     *
     * @param  array  $entries
     * @param  int  $parameterId
     * @param  string|null  $date
     * @return array<int, array>
     */
    protected function collectHits(array $entries, $parameterId, $date)
    {
        $hits = [];

        foreach ($entries as $entry) {
            $sampleId = trim((string) ($entry['sample_id'] ?? ''));
            if ($sampleId === '') {
                continue;
            }

            $receivedAt = (string) ($entry['received_at'] ?? '');
            if ($date !== null && substr($receivedAt, 0, 10) !== $date) {
                continue;
            }

            foreach ($entry['results'] as $row) {
                if ($row['parameter_id'] !== $parameterId) {
                    continue;
                }

                $parsed = TmsKlinikHelper::parseSampleIdBarcode($sampleId);
                $typeDigit = $parsed['type1'] ?? null;

                $hits[] = [
                    'base' => $parsed['base9'] ?? $sampleId,
                    'sample_id' => $sampleId,
                    'received_at' => $receivedAt ?: null,
                    'value' => $row['value'],
                    'tray' => $entry['tray'],
                    'pos' => $entry['pos'],
                    'status' => $entry['log_status'],
                    'jenis_barcode' => $typeDigit !== null
                        ? TmsKlinikHelper::specimenTypeFromDigit($typeDigit)
                        : '-',
                    'jenis_db' => $this->jenisFromDatabase($sampleId),
                ];
            }
        }

        usort($hits, function ($a, $b) {
            return strcmp((string) $a['received_at'], (string) $b['received_at']);
        });

        return $hits;
    }

    /**
     * Rekap seluruh hasil parameter, bukan hanya yang dobel.
     *
     * @param  array  $entries
     * @param  int  $parameterId
     * @param  string|null  $date
     * @return int
     */
    protected function showRekap(array $entries, $parameterId, $date)
    {
        $hits = $this->collectHits($entries, $parameterId, $date);

        if (empty($hits)) {
            $this->warn('Tidak ada hasil parameter ' . $parameterId
                . ($date ? ' pada ' . $date : '') . ' di log.');

            return 0;
        }

        $perPatient = [];
        foreach ($hits as $hit) {
            $perPatient[$hit['base']] = ($perPatient[$hit['base']] ?? 0) + 1;
        }

        $this->info('Rekap hasil parameter ' . $parameterId
            . ($date ? ' pada ' . $date : ' (seluruh log)') . ':');

        $rows = [];
        foreach ($hits as $index => $hit) {
            $rows[] = [
                $index + 1,
                $hit['sample_id'],
                $hit['received_at'] ?: '-',
                $hit['value'] ?: '-',
                'T' . ($hit['tray'] ?: '-') . '/P' . ($hit['pos'] ?: '-'),
                $hit['jenis_barcode'],
                $hit['jenis_db'] ?: '-',
                $hit['status'],
                $perPatient[$hit['base']] > 1 ? 'ya (' . $perPatient[$hit['base']] . ')' : '-',
            ];
        }

        $this->table(
            ['No', 'Sample', 'Waktu', 'Nilai', 'Tray/Pos', 'Jenis (barcode)', 'Jenis (DB)', 'Status log', 'Dobel'],
            $rows
        );

        $statusCount = [];
        foreach ($hits as $hit) {
            $statusCount[$hit['status']] = ($statusCount[$hit['status']] ?? 0) + 1;
        }

        $patientsWithMultiple = count(array_filter($perPatient, function ($count) {
            return $count > 1;
        }));

        $this->line('');
        $this->line('Total hasil      : ' . count($hits));
        $this->line('Jumlah pasien    : ' . count($perPatient));
        $this->line('Pasien dobel     : ' . $patientsWithMultiple);
        foreach ($statusCount as $status => $count) {
            $this->line('Status ' . str_pad($status, 12) . ': ' . $count);
        }

        return 0;
    }

    /**
     * @param  string  $sampleId
     * @return string|null
     */
    protected function jenisFromDatabase($sampleId)
    {
        static $cache = [];

        if (array_key_exists($sampleId, $cache)) {
            return $cache[$sampleId];
        }

        $order = OrderTms::query()
            ->whereNull('deleted_at')
            ->where('kode_barcode', $sampleId)
            ->orderBy('created_at')
            ->first();

        $cache[$sampleId] = $order ? trim((string) ($order->jenis_sampel ?? '')) : null;

        return $cache[$sampleId];
    }

    /**
     * @param  int  $parameterId
     * @param  int  $occurrence
     * @return string
     */
    protected function label($parameterId, $occurrence)
    {
        if ($parameterId === 2) {
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
     * @param  array  $sampleIds
     * @return string[]
     */
    protected function notes(array $group, array $sampleIds)
    {
        $notes = [];

        $signatures = [];
        foreach ($group['hits'] as $hit) {
            $signatures[implode('|', [
                (string) $hit['value'],
                (string) $hit['tray'],
                (string) $hit['pos'],
                (string) $hit['sample_id'],
            ])] = true;
        }

        if (count($signatures) === 1) {
            $notes[] = 'Catatan: nilai, tabung, dan posisi identik — kemungkinan alat mengirim ulang hasil yang sama.';

            return $notes;
        }

        if (count($sampleIds) > 1) {
            $notes[] = 'Catatan: dua tabung berbeda, tiap sample_id harus punya order Glukosa sendiri.';
        } else {
            $notes[] = 'Catatan: sample_id sama, jadi order tersebut harus punya dua slot Glukosa '
                . 'atau ada order kedua dengan barcode yang sama.';
        }

        $trayPos = [];
        foreach ($group['hits'] as $hit) {
            $trayPos[($hit['tray'] ?: '-') . '/' . ($hit['pos'] ?: '-')] = true;
        }
        if (count($trayPos) === 1) {
            $notes[] = 'Peringatan: tray/pos kedua hasil sama, sistem tidak bisa memisahkan puasa dan 2 jam PP dari posisi.';
        }

        return $notes;
    }
}
