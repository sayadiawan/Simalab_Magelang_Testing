<?php

namespace Smt\Masterweb\Helpers;

/**
 * Pengambil sampel: terpisah Klinik (SOLK) dan Kesmas (SOLM).
 */
class SampleCollectorAccess
{
    public const LEVEL_KLINIK = 'SOLK';

    public const LEVEL_KESMAS = 'SOLM';

    /** @return array<int, string> */
    public static function klinikLevels(): array
    {
        return [self::LEVEL_KLINIK];
    }

    /** @return array<int, string> */
    public static function kesmasLevels(): array
    {
        return [self::LEVEL_KESMAS];
    }

    /** @return array<int, string> */
    public static function allLevels(): array
    {
        return array_merge(self::klinikLevels(), self::kesmasLevels());
    }

    public static function isKlinik(?string $level): bool
    {
        return in_array($level, self::klinikLevels(), true);
    }

    public static function isKesmas(?string $level): bool
    {
        return in_array($level, self::kesmasLevels(), true);
    }

    public static function isAny(?string $level): bool
    {
        return self::isKlinik($level) || self::isKesmas($level);
    }

    /**
     * Validasi lab untuk pengambil sampel Kesmas (Kimia / Mikrobiologi).
     */
    public static function kesmasLabAllowed(?string $labName): bool
    {
        return in_array(strtolower(trim((string) $labName)), ['kimia', 'mikrobiologi'], true);
    }
}
