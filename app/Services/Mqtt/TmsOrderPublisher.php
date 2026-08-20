<?php

namespace App\Services\Mqtt;

use App\Services\Tms\TmsOrderFormatter;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\OrderTms;

/**
 * Channel tambahan: publish order TMS ke MQTT setelah DB commit.
 * Kegagalan MQTT tidak boleh menggagalkan transaksi/HTTP existing.
 */
class TmsOrderPublisher
{
    /** @var \App\Services\Mqtt\MqttService */
    protected $mqtt;

    public function __construct(MqttService $mqtt)
    {
        $this->mqtt = $mqtt;
    }

    /**
     * @param  string  $idOrderTms
     * @return array
     */
    public function publishById($idOrderTms)
    {
        $report = $this->emptyReport($idOrderTms);
        try {
            $order = TmsOrderFormatter::findWithRelations($idOrderTms);
            if (!$order) {
                Log::warning('MQTT TMS order skip: order tidak ditemukan setelah commit', [
                    'id_order_tms' => $idOrderTms,
                ]);
                $report['error'] = 'Order tidak ditemukan.';
                return $report;
            }

            return $this->publishSavedOrder($order);
        } catch (\Throwable $e) {
            Log::error('MQTT TMS order publish failed', [
                'id_order_tms' => $idOrderTms,
                'message' => $e->getMessage(),
            ]);
            $report['error'] = $e->getMessage();
            return $report;
        }
    }

    /**
     * @param  \Smt\Masterweb\Models\OrderTms  $order
     * @return array
     */
    public function publishSavedOrder(OrderTms $order)
    {
        $report = $this->emptyReport($order->id_order_tms);
        try {
            $payload = $this->buildPayload($order);
            $report['message_id'] = $payload['message_id'] ?? null;
            $report['payload'] = $payload;

            $this->mqtt->publish(
                $report['topic'],
                $payload,
                $report['qos'],
                $report['retain']
            );

            $report['published'] = true;

            Log::info('MQTT TMS order published', [
                'id_order_tms' => $order->id_order_tms,
                'topic' => $report['topic'],
                'message_id' => $report['message_id'],
            ]);

            return $report;
        } catch (\Throwable $e) {
            Log::error('MQTT TMS order publish failed', [
                'id_order_tms' => $order->id_order_tms ?? null,
                'message' => $e->getMessage(),
            ]);
            $report['error'] = $e->getMessage();
            return $report;
        }
    }

    /**
     * @param  string|null  $idOrderTms
     * @return array
     */
    protected function emptyReport($idOrderTms)
    {
        return [
            'id_order_tms' => $idOrderTms,
            'published' => false,
            'topic' => config('mqtt.topics.tms.order'),
            'qos' => (int) config('mqtt.qos', 1),
            'retain' => (bool) config('mqtt.retain', false),
            'message_id' => null,
            'payload' => null,
            'error' => null,
        ];
    }

    /**
     * Payload MQTT dari object order yang sama dengan GET TMS.
     *
     * @param  \Smt\Masterweb\Models\OrderTms  $order
     * @return array
     */
    public function buildPayload(OrderTms $order)
    {
        $fresh = $order;
        if (!$order->relationLoaded('details') || !$order->relationLoaded('permohonanUjiKlinik')) {
            $reloaded = TmsOrderFormatter::findWithRelations($order->id_order_tms);
            if ($reloaded) {
                $fresh = $reloaded;
            } else {
                $order->load(TmsOrderFormatter::eagerLoad());
                $fresh = $order;
            }
        }

        // False = sama dengan GET pending orders (tanpa value hasil).
        $formatted = TmsOrderFormatter::fromOrder($fresh, false);

        return TmsOrderPayload::fromFormatted($formatted);
    }
}
