<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Koneksi terpisah untuk alokasi nomor (spesimen/lab).
 * Commit independen dari transaksi bisnis luar — cegah race dobel nomor.
 */
class SequenceDb
{
    public static function connectionName(): string
    {
        $default = (string) config('database.default', 'mysql');
        $name = $default . '_seq';

        if (!config("database.connections.{$name}")) {
            $base = config("database.connections.{$default}");
            if (is_array($base)) {
                // Hindari reuse PDO parent (yang bisa sedang dalam transaksi luar)
                unset($base['pdo']);
                config(["database.connections.{$name}" => $base]);
            }
        }

        return $name;
    }

    public static function connection()
    {
        return DB::connection(self::connectionName());
    }

    public static function isDuplicateKeyException(\Throwable $e): bool
    {
        if (!$e instanceof \Illuminate\Database\QueryException) {
            return false;
        }

        $code = (string) ($e->errorInfo[1] ?? '');
        // MySQL 1062 duplicate, SQLite 19/1555
        return $code === '1062' || $code === '19' || $code === '1555'
            || stripos($e->getMessage(), 'Duplicate') !== false;
    }

    public static function isLockWaitTimeoutException(\Throwable $e): bool
    {
        if (!$e instanceof \Illuminate\Database\QueryException) {
            return false;
        }

        $code = (string) ($e->errorInfo[1] ?? '');

        // MySQL 1205 lock wait, 1213 deadlock
        return $code === '1205' || $code === '1213'
            || stripos($e->getMessage(), 'Lock wait timeout') !== false
            || stripos($e->getMessage(), 'Deadlock') !== false;
    }

    public static function isRetryableSequenceException(\Throwable $e): bool
    {
        return self::isDuplicateKeyException($e) || self::isLockWaitTimeoutException($e);
    }
}
