<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MqttTmsSubscribeLogCleanup extends Command
{
    protected $signature = 'mqtt:cleanup-subscribe-log';

    protected $description = 'Kosongkan storage/logs/mqtt-tms-subscribe.log tanpa memutus proses nohup subscriber';

    public function handle()
    {
        $path = storage_path('logs/mqtt-tms-subscribe.log');

        if (!is_file($path)) {
            $this->info('Log MQTT subscriber belum ada: ' . $path);
            return 0;
        }

        $size = filesize($path);
        $handle = fopen($path, 'c');
        if ($handle === false) {
            $this->error('Gagal membuka log: ' . $path);
            return 1;
        }

        // Truncate in-place (inode sama) agar nohup tetap menulis ke file ini.
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            $this->error('Gagal mengunci log: ' . $path);
            return 1;
        }
        ftruncate($handle, 0);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        $this->info('Log MQTT subscriber dikosongkan (' . $size . ' byte).');

        return 0;
    }
}
