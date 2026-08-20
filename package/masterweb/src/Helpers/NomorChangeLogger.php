<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\NomorChangeHistory;

/**
 * Catat jejak penggantian nomor tanpa mengganggu alokasi nomor.
 */
class NomorChangeLogger
{
    /**
     * @param  array<string, mixed>  $attrs
     */
    public static function record(array $attrs): void
    {
        try {
            if (!Schema::hasTable('tb_nomor_change_history')) {
                return;
            }

            $old = self::stringify($attrs['old_value'] ?? null);
            $new = self::stringify($attrs['new_value'] ?? null);
            if ($old === $new) {
                return;
            }

            $userId = null;
            try {
                $userId = Auth::id();
            } catch (\Throwable $e) {
                $userId = null;
            }

            NomorChangeHistory::create([
                'subject_type' => (string) ($attrs['subject_type'] ?? 'klinik'),
                'subject_id' => (string) ($attrs['subject_id'] ?? ''),
                'field_name' => (string) ($attrs['field_name'] ?? ''),
                'old_value' => $old === '' ? null : $old,
                'new_value' => $new === '' ? null : $new,
                'event' => (string) ($attrs['event'] ?? 'penggantian'),
                'source' => (string) ($attrs['source'] ?? 'sistem'),
                'note' => isset($attrs['note']) ? (string) $attrs['note'] : null,
                'created_by' => $userId,
            ]);
        } catch (\Throwable $e) {
            // Jangan gagalkan simpan nomor karena gagal menulis history.
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function recordMany(array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            self::record($row);
        }
    }

    private static function stringify($value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }
}
