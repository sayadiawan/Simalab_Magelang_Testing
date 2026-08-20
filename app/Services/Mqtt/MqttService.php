<?php

namespace App\Services\Mqtt;

use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use RuntimeException;

class MqttService
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $password;

    /** @var bool */
    protected $tls;

    public function __construct()
    {
        $this->host = (string) config('mqtt.host');
        $this->port = (int) config('mqtt.port');
        $this->username = config('mqtt.username');
        $this->password = config('mqtt.password');
        $this->tls = (bool) config('mqtt.tls', true);
    }

    /**
     * Publish JSON ke broker. Exception dibiarkan ke pemanggil
     * (publisher aman akan menangkapnya).
     *
     * @param  string  $topic
     * @param  array  $payload
     * @param  int  $qos
     * @param  bool  $retain
     * @return bool
     */
    public function publish($topic, array $payload, $qos = 1, $retain = false)
    {
        $client = null;

        try {
            if ($this->host === '') {
                throw new RuntimeException('MQTT_HOST kosong.');
            }

            $clientId = (string) config('mqtt.client_id_prefix', 'simalab-server') . '-' . uniqid('', true);

            $client = new MqttClient(
                $this->host,
                $this->port,
                $clientId
            );

            $settings = (new ConnectionSettings())
                ->setUsername($this->username !== null && $this->username !== '' ? $this->username : null)
                ->setPassword($this->password !== null && $this->password !== '' ? $this->password : null)
                ->setUseTls($this->tls)
                ->setTlsSelfSignedAllowed(false)
                ->setConnectTimeout((int) config('mqtt.connect_timeout', 10))
                ->setKeepAliveInterval((int) config('mqtt.keep_alive', 60));

            $client->connect($settings, true);

            $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new RuntimeException('Payload MQTT gagal di-encode.');
            }

            $client->publish($topic, $json, (int) $qos, (bool) $retain);

            // QoS 1: tunggu PUBACK broker.
            if ((int) $qos > 0) {
                $client->loop(true, true);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('MQTT publish error', [
                'host' => $this->host,
                'port' => $this->port,
                'topic' => $topic,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $this->safeDisconnect($client);
        }
    }

    /**
     * Subscribe singkat (pola mqtt:tms-subscribe), kumpulkan message, lalu disconnect.
     * Untuk UI Riwayat MQTT — bukan pengganti daemon subscribe.
     *
     * @param  string[]  $topics
     * @param  int  $timeoutSeconds
     * @param  int  $qos
     * @return array
     */
    public function subscribeCollect(array $topics, $timeoutSeconds = 5, $qos = 1)
    {
        $client = null;
        $messages = [];
        $timeoutSeconds = (int) $timeoutSeconds;
        if ($timeoutSeconds < 2) {
            $timeoutSeconds = 2;
        }
        if ($timeoutSeconds > 15) {
            $timeoutSeconds = 15;
        }

        $topics = array_values(array_filter(array_map('strval', $topics)));
        if (empty($topics)) {
            return $messages;
        }

        try {
            if ($this->host === '') {
                throw new RuntimeException('MQTT_HOST kosong.');
            }

            $clientId = (string) config('mqtt.client_id_prefix', 'simalab-server') . '-listen-' . uniqid('', true);
            $client = new MqttClient($this->host, $this->port, $clientId);

            $settings = (new ConnectionSettings())
                ->setUsername($this->username !== null && $this->username !== '' ? $this->username : null)
                ->setPassword($this->password !== null && $this->password !== '' ? $this->password : null)
                ->setUseTls($this->tls)
                ->setTlsSelfSignedAllowed(false)
                ->setConnectTimeout((int) config('mqtt.connect_timeout', 10))
                ->setKeepAliveInterval((int) config('mqtt.keep_alive', 60));

            $client->connect($settings, true);

            $callback = function ($topic, $message) use (&$messages) {
                $decoded = self::decodeJsonMessage($message);
                $messages[] = [
                    'topic' => $topic,
                    'raw' => $message,
                    'payload' => is_array($decoded) ? $decoded : null,
                    'received_at' => now()->toIso8601String(),
                ];
            };

            foreach ($topics as $topic) {
                $client->subscribe($topic, $callback, (int) $qos);
            }

            $client->registerLoopEventHandler(function ($mqtt, $elapsedTime) use ($timeoutSeconds) {
                if ($elapsedTime >= $timeoutSeconds) {
                    $mqtt->interrupt();
                }
            });

            $client->loop(true);
        } catch (\Throwable $e) {
            Log::error('MQTT subscribe error', [
                'host' => $this->host,
                'port' => $this->port,
                'topics' => $topics,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $this->safeDisconnect($client);
        }

        return $messages;
    }

    /**
     * Bersihkan karakter invisible (UTF-8 BOM dll.) dari payload MQTT alat.
     *
     * @param  string  $message
     * @return string
     */
    public static function normalizeMessage($message)
    {
        $message = (string) $message;

        // UTF-8 BOM (EF BB BF) — sering muncul dari alat Windows/.NET
        if (strncmp($message, "\xEF\xBB\xBF", 3) === 0) {
            $message = substr($message, 3);
        }

        // Unicode BOM (U+FEFF)
        $message = preg_replace('/^\x{FEFF}/u', '', $message);

        return trim($message);
    }

    /**
     * Decode JSON payload MQTT; toleran BOM / whitespace di awal-akhir.
     *
     * @param  string  $message
     * @return array|null
     */
    public static function decodeJsonMessage($message)
    {
        $normalized = self::normalizeMessage($message);
        if ($normalized === '') {
            return null;
        }

        $decoded = json_decode($normalized, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  \PhpMqtt\Client\MqttClient|null  $client
     * @return void
     */
    protected function safeDisconnect($client)
    {
        if ($client === null) {
            return;
        }
        try {
            $client->disconnect();
        } catch (\Throwable $e) {
            // disconnect tidak boleh menggagalkan proses utama
        }
    }
}
