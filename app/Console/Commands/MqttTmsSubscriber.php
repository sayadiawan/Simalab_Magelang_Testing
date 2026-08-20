<?php

namespace App\Console\Commands;

use App\Services\Mqtt\TmsMqttResultHandler;
use App\Services\Mqtt\MqttService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttTmsSubscriber extends Command
{
    protected $signature = 'mqtt:tms-subscribe
        {--dry-run : Tampilkan payload saja, jangan tulis hasil ke database}';

    protected $description = 'Subscribe TMS result via MQTT dan isi hasil ke tb_orderdetail_tms';

    public function handle()
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $qos = (int) config('mqtt.qos', 1);
        $dryRun = (bool) $this->option('dry-run');

        $resultTopic = (string) config('mqtt.topics.tms.result');

        if ($host === '') {
            $this->error('MQTT_HOST kosong.');
            return 1;
        }
        if ($resultTopic === '') {
            $this->error('MQTT_TOPIC_TMS_RESULT kosong.');
            return 1;
        }

        $mqtt = null;

        try {
            $mqtt = new MqttClient(
                $host,
                $port,
                (string) config('mqtt.client_id_prefix', 'simalab-server') . '-tms-' . uniqid('', true)
            );

            $username = config('mqtt.username');
            $password = config('mqtt.password');

            $settings = (new ConnectionSettings())
                ->setUsername($username !== null && $username !== '' ? $username : null)
                ->setPassword($password !== null && $password !== '' ? $password : null)
                ->setUseTls((bool) config('mqtt.tls', true))
                ->setTlsSelfSignedAllowed(false)
                ->setConnectTimeout((int) config('mqtt.connect_timeout', 10))
                ->setKeepAliveInterval((int) config('mqtt.keep_alive', 60));

            $handler = app(TmsMqttResultHandler::class);

            $this->info("Connecting MQTT {$host}:{$port}");
            $mqtt->connect($settings, true);
            $this->info('Connected.');

            $mqtt->subscribe(
                $resultTopic,
                function ($topic, $message) use ($handler, $dryRun) {
                    $this->handleMessage($handler, $topic, $message, $dryRun);
                },
                $qos
            );
            $this->info("Subscribe: {$resultTopic}");

            if ($dryRun) {
                $this->warn('Mode --dry-run: hasil TIDAK ditulis ke database.');
            }

            $this->info('Waiting message... (Ctrl+C untuk berhenti)');

            /*
             * WAJIB.
             *
             * Subscriber tidak akan menerima message
             * tanpa event loop ini.
             */
            $mqtt->loop(true);
        } catch (\Throwable $e) {
            $this->error('MQTT ERROR: ' . get_class($e) . ': ' . $e->getMessage());
            $this->line('DI      : ' . $e->getFile() . ':' . $e->getLine());
            Log::error('MQTT TMS subscriber error', [
                'host' => $host,
                'port' => $port,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            return 1;
        } finally {
            if ($mqtt !== null) {
                try {
                    $mqtt->disconnect();
                } catch (\Throwable $e) {
                }
            }
        }

        return 0;
    }

    /**
     * Satu pesan gagal tidak boleh mematikan daemon.
     *
     * @param  \App\Services\Mqtt\TmsMqttResultHandler  $handler
     * @param  string  $topic
     * @param  string  $message
     * @param  bool  $dryRun
     * @return void
     */
    protected function handleMessage($handler, $topic, $message, $dryRun)
    {
        $this->line('');
        $this->line('==============================');
        $this->line('MQTT MESSAGE RECEIVED ' . now()->format('Y-m-d H:i:s'));
        $this->line('TOPIC   : ' . $topic);
        $this->line('MESSAGE : ' . MqttService::normalizeMessage($message));

        $decoded = MqttService::decodeJsonMessage($message);
        if (!is_array($decoded)) {
            $this->error('SKIP    : payload bukan JSON object.');
            $this->line('==============================');
            return;
        }

        if ($dryRun) {
            $this->line('DRY RUN : payload valid, tidak ditulis ke database.');
            $this->line('==============================');
            return;
        }

        try {
            // Koneksi DB bisa timeout pada daemon yang idle lama.
            $this->ensureDatabaseConnection();

            $report = $handler->apply([
                'topic' => $topic,
                'raw' => $message,
                'payload' => $decoded,
                'received_at' => now()->toIso8601String(),
            ]);

            if (!empty($report['applied'])) {
                $this->info(sprintf(
                    'APPLIED : %d value tb_orderdetail_tms terisi (order %s, sample %s, tray %s, pos %s, via %s).',
                    (int) $report['updated'],
                    (string) $report['id_order_tms'],
                    (string) $report['sample_id'],
                    (string) ($report['tray'] ?? '-'),
                    (string) ($report['pos'] ?? '-'),
                    (string) ($report['matched_by'] ?: '-')
                ));
            } else {
                $this->warn('NOT APPLIED : ' . ($report['error'] ?: 'tidak ada perubahan.'));
                if (!empty($report['tray']) || !empty($report['pos'])) {
                    $this->line('TRAY/POS : tray=' . ($report['tray'] ?? '-') . ' pos=' . ($report['pos'] ?? '-'));
                }
            }

            Log::info('MQTT TMS result handled by subscriber', $report);
        } catch (\Throwable $e) {
            $this->error('APPLY ERROR : ' . $e->getMessage());
            Log::error('MQTT TMS subscriber apply failed', [
                'topic' => $topic,
                'message' => $e->getMessage(),
            ]);
        }

        $this->line('==============================');
    }

    /**
     * @return void
     */
    protected function ensureDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            DB::reconnect();
        }
    }
}
