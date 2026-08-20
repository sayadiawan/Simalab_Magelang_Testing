<?php

namespace App\Services\Mqtt;

/**
 * Parser storage/logs/mqtt-tms-subscribe.log (output daemon mqtt:tms-subscribe).
 */
class TmsMqttSubscribeLogParser
{
    /**
     * @param  string  $path
     * @param  array  $options  since (Y-m-d), sample, limit, dedupe, include_errors
     * @return array<int, array{received_at:?string, topic:?string, payload:?array, raw:string, log_error:?string, message_id:?string, sample_id:?string}>
     */
    public function parseNotApplied($path, array $options = [])
    {
        $options['only_not_applied'] = true;

        return $this->parseBlocks($path, $options);
    }

    /**
     * Semua message di log, terlepas APPLIED atau tidak.
     *
     * @param  string  $path
     * @param  array  $options  since (Y-m-d), sample, limit, dedupe
     * @return array<int, array>
     */
    public function parseAll($path, array $options = [])
    {
        $options['only_not_applied'] = false;

        return $this->parseBlocks($path, $options);
    }

    /**
     * @param  string  $path
     * @param  array  $options
     * @return array<int, array>
     */
    protected function parseBlocks($path, array $options = [])
    {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return [];
        }

        $since = isset($options['since']) ? trim((string) $options['since']) : '';
        $sampleFilter = isset($options['sample']) ? trim((string) $options['sample']) : '';
        $limit = isset($options['limit']) ? max(1, (int) $options['limit']) : 50;
        $dedupe = !array_key_exists('dedupe', $options) || (bool) $options['dedupe'];
        $includeErrors = !empty($options['include_errors']);
        $onlyNotApplied = !array_key_exists('only_not_applied', $options)
            || (bool) $options['only_not_applied'];

        $blocks = preg_split('/^={10,}\s*$/m', $content) ?: [];
        $entries = [];
        $seenMessageIds = [];

        foreach ($blocks as $block) {
            $block = trim((string) $block);
            if ($block === '') {
                continue;
            }

            $isNotApplied = stripos($block, 'NOT APPLIED') !== false;
            $isApplyError = stripos($block, 'APPLY ERROR') !== false;
            $isApplied = !$isNotApplied && preg_match('/^APPLIED\s*:/m', $block) === 1;

            if ($onlyNotApplied && !$isNotApplied && !($includeErrors && $isApplyError)) {
                continue;
            }

            $receivedAt = null;
            if (preg_match('/^MQTT MESSAGE RECEIVED\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/m', $block, $m)) {
                $receivedAt = trim($m[1]);
            }

            if ($since !== '' && $receivedAt !== null && substr($receivedAt, 0, 10) < $since) {
                continue;
            }

            $topic = null;
            if (preg_match('/^TOPIC\s*:\s*(.+)$/m', $block, $m)) {
                $topic = trim($m[1]);
            }

            $raw = null;
            if (preg_match('/^MESSAGE\s*:\s*(.+)$/m', $block, $m)) {
                $raw = trim($m[1]);
            }
            if ($raw === null || $raw === '') {
                continue;
            }

            $payload = MqttService::decodeJsonMessage($raw);
            if (!is_array($payload)) {
                continue;
            }

            $messageId = trim((string) ($payload['message_id'] ?? ''));
            $sampleId = $this->extractSampleId($payload);

            if ($sampleFilter !== '' && $sampleId !== $sampleFilter) {
                continue;
            }

            if ($dedupe && $messageId !== '') {
                if (isset($seenMessageIds[$messageId])) {
                    continue;
                }
                $seenMessageIds[$messageId] = true;
            }

            $logError = null;
            if ($isNotApplied && preg_match('/^NOT APPLIED\s*:\s*(.+)$/m', $block, $m)) {
                $logError = trim($m[1]);
            } elseif ($isApplyError && preg_match('/^APPLY ERROR\s*:\s*(.+)$/m', $block, $m)) {
                $logError = trim($m[1]);
            }

            $status = 'unknown';
            if ($isApplied) {
                $status = 'applied';
            } elseif ($isNotApplied) {
                $status = 'not_applied';
            } elseif ($isApplyError) {
                $status = 'error';
            }

            $entries[] = [
                'received_at' => $receivedAt,
                'topic' => $topic,
                'payload' => $payload,
                'raw' => $raw,
                'log_error' => $logError,
                'log_status' => $status,
                'results' => $this->extractResults($payload),
                'tray' => $this->extractTrayPos($payload, 'tray'),
                'pos' => $this->extractTrayPos($payload, 'pos'),
                'message_id' => $messageId !== '' ? $messageId : null,
                'sample_id' => $sampleId !== '' ? $sampleId : null,
            ];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    /**
     * Baris hasil pada payload: parameter_id, nilai, nama.
     *
     * @param  array  $payload
     * @return array<int, array{parameter_id:int, parameter_name:?string, value:?string}>
     */
    protected function extractResults(array $payload)
    {
        $data = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;
        $rows = $data['results'] ?? $data['parameters'] ?? $payload['results'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $parameterId = (int) ($row['parameter_id'] ?? $row['id_parameter_tms'] ?? 0);
            if ($parameterId <= 0) {
                continue;
            }

            $value = null;
            foreach (['hasil', 'value', 'result_value'] as $key) {
                if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                    $value = trim((string) $row[$key]);
                    break;
                }
            }

            $out[] = [
                'parameter_id' => $parameterId,
                'parameter_name' => isset($row['parameter_name']) ? trim((string) $row['parameter_name']) : null,
                'value' => $value,
            ];
        }

        return $out;
    }

    /**
     * @param  array  $payload
     * @param  string  $field  tray|pos
     * @return string|null
     */
    protected function extractTrayPos(array $payload, $field)
    {
        $data = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

        $value = $field === 'pos'
            ? ($data['pos'] ?? $data['posisi'] ?? $payload['pos'] ?? $payload['posisi'] ?? null)
            : ($data['tray'] ?? $payload['tray'] ?? null);

        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    /**
     * @param  array  $payload
     * @return string
     */
    protected function extractSampleId(array $payload)
    {
        $data = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

        return trim((string) (
            $data['sample_id']
            ?? $data['kode_barcode']
            ?? $payload['sample_id']
            ?? $payload['kode_barcode']
            ?? ''
        ));
    }
}
