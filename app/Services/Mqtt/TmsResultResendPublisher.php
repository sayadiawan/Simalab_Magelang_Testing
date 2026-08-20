<?php

namespace App\Services\Mqtt;

use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\OrderTms;

/**
 * Minta alat TMS mengirim ulang hasil untuk sample_id tertentu.
 */
class TmsResultResendPublisher
{
    /** @var \App\Services\Mqtt\MqttService */
    protected $mqtt;

    public function __construct(MqttService $mqtt)
    {
        $this->mqtt = $mqtt;
    }

    /**
     * @param  string  $sampleId
     * @param  string|null  $idOrderTms
     * @return array
     */
    public function publishBySampleId($sampleId, $idOrderTms = null)
    {
        $sampleId = trim((string) $sampleId);
        $idOrderTms = trim((string) $idOrderTms);
        if ($idOrderTms === '') {
            $idOrderTms = null;
        }
        $report = [
            'id_order_tms' => $idOrderTms,
            'sample_id' => $sampleId,
            'published' => false,
            'topic' => (string) config('mqtt.topics.tms.order'),
            'qos' => (int) config('mqtt.qos', 1),
            'retain' => (bool) config('mqtt.retain', false),
            'payload' => null,
            'error' => null,
        ];

        if ($sampleId === '') {
            $report['error'] = 'Sample ID / barcode kosong.';
            return $report;
        }

        try {
            $payload = [
                'message_type' => 'tms.result.resend',
                'data' => [
                    'id_order_tms' => $idOrderTms,
                    'sample_id' => $sampleId,
                ],
            ];
            $report['payload'] = $payload;

            $this->mqtt->publish(
                $report['topic'],
                $payload,
                $report['qos'],
                $report['retain']
            );

            $report['published'] = true;

            Log::info('MQTT TMS result resend published', [
                'id_order_tms' => $idOrderTms,
                'sample_id' => $sampleId,
                'topic' => $report['topic'],
            ]);

            return $report;
        } catch (\Throwable $e) {
            Log::error('MQTT TMS result resend publish failed', [
                'id_order_tms' => $idOrderTms,
                'sample_id' => $sampleId,
                'message' => $e->getMessage(),
            ]);
            $report['error'] = $e->getMessage();
            return $report;
        }
    }

    /**
     * @param  string  $idOrderTms
     * @return array
     */
    public function publishByOrderId($idOrderTms)
    {
        $order = OrderTms::query()
            ->where('id_order_tms', $idOrderTms)
            ->whereNull('deleted_at')
            ->first();

        if (!$order) {
            return [
                'id_order_tms' => $idOrderTms,
                'sample_id' => null,
                'published' => false,
                'topic' => (string) config('mqtt.topics.tms.order'),
                'qos' => (int) config('mqtt.qos', 1),
                'retain' => (bool) config('mqtt.retain', false),
                'payload' => null,
                'error' => 'Order tidak ditemukan.',
            ];
        }

        $sampleId = trim((string) ($order->kode_barcode ?? ''));
        if ($sampleId === '') {
            return [
                'id_order_tms' => $idOrderTms,
                'sample_id' => null,
                'published' => false,
                'topic' => (string) config('mqtt.topics.tms.order'),
                'qos' => (int) config('mqtt.qos', 1),
                'retain' => (bool) config('mqtt.retain', false),
                'payload' => null,
                'error' => 'Barcode / sample_id order kosong.',
            ];
        }

        return $this->publishBySampleId($sampleId, $idOrderTms);
    }
}
