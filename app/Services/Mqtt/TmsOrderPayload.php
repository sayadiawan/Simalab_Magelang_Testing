<?php

namespace App\Services\Mqtt;

use Illuminate\Support\Str;

class TmsOrderPayload
{
    /**
     * Envelope MQTT dari array data order.
     *
     * @param  array  $data
     * @return array
     */
    public static function make(array $data)
    {
        return [
            'message_id' => (string) Str::uuid(),
            'message_type' => 'tms.order',
            'version' => 1,
            'sent_at' => now()->toIso8601String(),
            'data' => [
                'id_order_tms' => $data['id_order_tms'] ?? null,
                'sample_id' => $data['sample_id'] ?? null,
                'nomer_spesimen' => $data['nomer_spesimen'] ?? null,
                'patient_name' => $data['patient_name'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'jenis_spesimen' => $data['jenis_spesimen'] ?? null,
                'tray' => array_key_exists('tray', $data) ? $data['tray'] : null,
                'pos' => array_key_exists('pos', $data) ? $data['pos'] : null,
                'parameters' => $data['parameters'] ?? [],
            ],
        ];
    }

    /**
     * Subset MQTT dari payload GET /api/tms/orders yang sudah diformat.
     *
     * @param  array  $formatted
     * @return array
     */
    public static function fromFormatted(array $formatted)
    {
        $parameters = [];
        foreach ($formatted['parameters'] ?? [] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $parameters[] = [
                'parameter_id' => (int) ($row['parameter_id'] ?? 0),
                'parameter_name' => $row['parameter_name'] ?? null,
            ];
        }

        return self::make([
            'id_order_tms' => $formatted['id_order_tms'] ?? null,
            'sample_id' => $formatted['sample_id'] ?? null,
            'nomer_spesimen' => $formatted['nomer_spesimen'] ?? null,
            'patient_name' => $formatted['patient_name'] ?? null,
            'birth_date' => $formatted['birth_date'] ?? null,
            'gender' => $formatted['gender'] ?? null,
            'jenis_spesimen' => $formatted['jenis_spesimen'] ?? null,
            'tray' => $formatted['tray'] ?? null,
            'pos' => $formatted['pos'] ?? null,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Dummy aman untuk command test jika belum ada order existing.
     *
     * @return array
     */
    public static function dummy()
    {
        return self::make([
            'id_order_tms' => (string) Str::uuid(),
            'sample_id' => 'TEST-MQTT-001',
            'nomer_spesimen' => 'TEST001',
            'patient_name' => 'TEST MQTT',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'jenis_spesimen' => 'Serum',
            'tray' => null,
            'pos' => null,
            'parameters' => [
                [
                    'parameter_id' => 3,
                    'parameter_name' => 'Ureum',
                ],
                [
                    'parameter_id' => 7,
                    'parameter_name' => 'SGOT',
                ],
            ],
        ]);
    }
}
