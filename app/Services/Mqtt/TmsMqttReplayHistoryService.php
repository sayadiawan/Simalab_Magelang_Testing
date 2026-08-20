<?php

namespace App\Services\Mqtt;

use App\Models\MqttTmsReplayHistory;
use Illuminate\Support\Collection;
use Smt\Masterweb\Helpers\TmsKlinikHelper;

class TmsMqttReplayHistoryService
{
    /**
     * @param  array  $entry
     * @return string
     */
    public function entryKey(array $entry)
    {
        $messageId = trim((string) ($entry['message_id'] ?? ''));
        if ($messageId !== '') {
            return 'msg:' . $messageId;
        }

        $receivedAt = trim((string) ($entry['received_at'] ?? ''));
        $raw = trim((string) ($entry['raw'] ?? ''));

        return 'hash:' . sha1($receivedAt . "\n" . $raw);
    }

    /**
     * @param  string  $entryKey
     * @return bool
     */
    public function alreadyProcessed($entryKey)
    {
        return MqttTmsReplayHistory::query()
            ->where('entry_key', $entryKey)
            ->exists();
    }

    /**
     * Kembalikan map entry_key => status|null.
     * null = belum pernah diproses.
     *
     * @param  string[]  $entryKeys
     * @return array<string, string|null>
     */
    public function processedMap(array $entryKeys)
    {
        if (empty($entryKeys)) {
            return [];
        }

        $existing = MqttTmsReplayHistory::query()
            ->whereIn('entry_key', $entryKeys)
            ->pluck('status', 'entry_key')
            ->all();

        $map = [];
        foreach ($entryKeys as $key) {
            $map[$key] = $existing[$key] ?? null;
        }

        return $map;
    }

    /**
     * Apakah entri boleh dilewati (sudah berhasil sebelumnya).
     * not_applied tetap dicoba ulang.
     *
     * @param  string|null  $status
     * @return bool
     */
    public function shouldSkip($status)
    {
        return in_array($status, [
            MqttTmsReplayHistory::STATUS_APPLIED,
            MqttTmsReplayHistory::STATUS_ALREADY_FILLED,
        ], true);
    }

    /**
     * @param  array  $entry
     * @param  array  $report
     * @return \App\Models\MqttTmsReplayHistory
     */
    public function record(array $entry, array $report)
    {
        $data = isset($entry['payload']['data']) && is_array($entry['payload']['data'])
            ? $entry['payload']['data']
            : ($entry['payload'] ?? []);

        $tray = TmsKlinikHelper::normalizeTrayPosValue($data['tray'] ?? null);
        $pos = TmsKlinikHelper::normalizeTrayPosValue($data['pos'] ?? $data['posisi'] ?? null);

        if (!empty($report['applied'])) {
            $status = !empty($report['already_applied'])
                ? MqttTmsReplayHistory::STATUS_ALREADY_FILLED
                : MqttTmsReplayHistory::STATUS_APPLIED;
        } else {
            $status = MqttTmsReplayHistory::STATUS_NOT_APPLIED;
        }

        $logReceivedAt = null;
        if (!empty($entry['received_at'])) {
            try {
                $logReceivedAt = \Carbon\Carbon::parse($entry['received_at']);
            } catch (\Throwable $e) {
                $logReceivedAt = null;
            }
        }

        return MqttTmsReplayHistory::query()->updateOrCreate(
            ['entry_key' => $this->entryKey($entry)],
            [
                'message_id' => $entry['message_id'] ?? null,
                'sample_id' => $entry['sample_id'] ?? null,
                'tray' => $tray,
                'pos' => $pos,
                'log_received_at' => $logReceivedAt,
                'status' => $status,
                'log_error' => $entry['log_error'] ?? null,
                'replay_error' => !empty($report['applied']) ? null : ($report['error'] ?? null),
                'id_order_tms' => $report['id_order_tms'] ?? null,
                'updated_count' => (int) ($report['updated'] ?? 0),
                'matched_by' => $report['matched_by'] ?? null,
                'replayed_at' => now(),
            ]
        );
    }

    /**
     * @param  array  $filters
     * @return \Illuminate\Support\Collection
     */
    public function listRecent(array $filters = [])
    {
        $query = MqttTmsReplayHistory::query()->orderBy('replayed_at', 'desc');

        if (!empty($filters['since'])) {
            $query->whereDate('log_received_at', '>=', $filters['since']);
        }
        if (!empty($filters['sample'])) {
            $query->where('sample_id', $filters['sample']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 100;

        return $query->limit($limit)->get();
    }

    /**
     * @param  \Illuminate\Support\Collection  $rows
     * @return array{applied:int, already_filled:int, not_applied:int}
     */
    public function summarize(Collection $rows)
    {
        return [
            'applied' => $rows->where('status', MqttTmsReplayHistory::STATUS_APPLIED)->count(),
            'already_filled' => $rows->where('status', MqttTmsReplayHistory::STATUS_ALREADY_FILLED)->count(),
            'not_applied' => $rows->where('status', MqttTmsReplayHistory::STATUS_NOT_APPLIED)->count(),
        ];
    }
}
