<?php

namespace App\Console\Commands;

use App\Services\Mqtt\MqttService;
use App\Services\Mqtt\TmsOrderPayload;
use App\Services\Mqtt\TmsOrderPublisher;
use App\Services\Tms\TmsOrderFormatter;
use Illuminate\Console\Command;
use Smt\Masterweb\Models\OrderTms;

class MqttTestTmsOrder extends Command
{
    protected $signature = 'mqtt:test-tms-order';

    protected $description = 'Test publish TMS order via MQTT menggunakan payload builder production';

    public function handle(MqttService $mqtt, TmsOrderPublisher $publisher)
    {
        $topic = config('mqtt.topics.tms.order');
        $qos = (int) config('mqtt.qos', 1);
        $retain = (bool) config('mqtt.retain', false);

        $order = OrderTms::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('is_executed', 0)->orWhereNull('is_executed');
            })
            ->with(TmsOrderFormatter::eagerLoad())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($order) {
            $this->info('Sumber: order existing pending (aman untuk uji payload GET TMS).');
            $this->line('id_order_tms: ' . $order->id_order_tms);
            $payload = $publisher->buildPayload($order);
        } else {
            $this->warn('Tidak ada order pending existing. Memakai dummy payload.');
            $payload = TmsOrderPayload::dummy();
        }

        $this->info('MQTT TMS ORDER TEST');
        $this->line('Topic: ' . $topic);
        $this->line('QoS: ' . $qos);
        $this->line('Retain: ' . ($retain ? 'true' : 'false'));
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        try {
            $mqtt->publish($topic, $payload, $qos, $retain);
            $this->info('PUBLISH SUCCESS');
            return 0;
        } catch (\Throwable $e) {
            $this->error('PUBLISH FAILED');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
