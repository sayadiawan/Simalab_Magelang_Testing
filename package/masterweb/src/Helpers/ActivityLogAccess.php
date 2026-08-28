<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Smt\Masterweb\Models\ActivityLog;
use Smt\Masterweb\Models\User;

/**
 * Hak akses log aktivitas disesuaikan tupoksi:
 * - Super Admin / Elits-Dev → semua log
 * - Admin Lab → semua log
 * - Koordinator / Kepala Lab Klinik (PLAB/KLAB/DKTR + lab KLI) → semua aktivitas klinik
 * - Koordinator / Kepala Lab Kesmas (PLAB/KLAB + lab KIM/MBI) → semua aktivitas kesmas
 * - Bendahara (BNDR) → hanya log yang dilakukan akun bendahara
 * - Role operasional lain → hanya log akun sendiri
 */
class ActivityLogAccess
{
    public const MODE_ALL = 'all';
    public const MODE_BIDANG = 'bidang';
    public const MODE_OWN = 'own';
    public const MODE_PRINT_EXPORT = 'print_export';

    /** Aksi log yang boleh dilihat pengarsip hasil. */
    public const PENGARSIP_ACTIONS = ['print', 'export'];

    /** Privilege level: bendahara — hanya log miliknya. */
    private const BENDAHARA_LEVELS = ['BNDR'];

    /** Privilege level: super administrator. */
    private const SUPER_ADMIN_LEVELS = ['00', 'elits-dev'];

    /** Privilege level: admin penuh (lihat semua log). */
    private const FULL_ADMIN_LEVELS = ['LAB', 'ADMD', 'MAN', 'admin'];

    /** Privilege level: Kepala UPTD Labkes — seluruh laporan aktivitas lintas bidang. */
    private const KEPALA_UPTD_LEVELS = ['KUPTD'];

    /** Privilege level: pengarsip / pencetak hasil. */
    private const PENGARSIP_LEVELS = ['ARSP'];

    /** Privilege level: koordinator / kepala lab (scope per laboratorium). */
    private const KOOR_LEVELS = ['PLAB', 'KLAB'];

    /** Privilege level: koordinator klinik (kasie / dokter lab klinik). */
    private const KOOR_KLINIK_LEVELS = ['PLAB', 'KLAB', 'DKTR', 'KSKL'];

    /** Privilege level: kasie / kepala lab kesmas. */
    private const KOOR_KESMAS_LEVELS = ['PLAB', 'KLAB', 'KSKM'];

    /** Kode lab klinik. */
    private const KLINIK_LAB_CODES = ['KLI'];

    /** Kode lab kesmas (kimia, mikro, dll.). */
    private const KESMAS_LAB_CODES = ['KIM', 'MBI', 'KMA', 'FKA', 'MIK'];

    /**
     * @return array{
     *     mode: string,
     *     bidang: string|null,
     *     user_id: string|null,
     *     label: string,
     *     role_label: string,
     *     can_filter_user: bool,
     *     can_filter_bidang: bool
     * }
     */
    public static function resolveScope(?User $user = null): array
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return self::ownScope(null, 'Tamu');
        }

        $level = (string) optional($user->getlevel)->level;
        $labCode = strtoupper((string) optional($user->laboratorium)->kode_laboratorium);
        $privilegeName = (string) optional($user->getlevel)->name;

        if (self::isBendahara($level)) {
            return [
                'mode' => self::MODE_OWN,
                'bidang' => null,
                'user_id' => (string) $user->id,
                'privilege_level' => 'BNDR',
                'label' => 'Hanya aktivitas yang dilakukan oleh akun Bendahara',
                'role_label' => $privilegeName ?: 'Bendahara Penerimaan',
                'can_filter_user' => false,
                'can_filter_bidang' => false,
            ];
        }

        if (self::isSuperAdmin($level)) {
            return [
                'mode' => self::MODE_ALL,
                'bidang' => null,
                'user_id' => null,
                'label' => 'Semua aktivitas seluruh pengguna dan bidang',
                'role_label' => 'Super Administrator',
                'can_filter_user' => true,
                'can_filter_bidang' => true,
            ];
        }

        if (self::isFullAdmin($level)) {
            return [
                'mode' => self::MODE_ALL,
                'bidang' => null,
                'user_id' => null,
                'label' => 'Semua aktivitas seluruh pengguna dan bidang',
                'role_label' => $privilegeName ?: 'Administrator',
                'can_filter_user' => true,
                'can_filter_bidang' => true,
            ];
        }

        if (self::isKepalaUptd($level)) {
            return [
                'mode' => self::MODE_ALL,
                'bidang' => null,
                'user_id' => null,
                'label' => 'Seluruh laporan aktivitas laboratorium (Kesmas & Klinik)',
                'role_label' => $privilegeName ?: 'Kepala UPTD Labkes',
                'can_filter_user' => true,
                'can_filter_bidang' => true,
            ];
        }

        if (self::isPengarsip($level)) {
            return [
                'mode' => self::MODE_PRINT_EXPORT,
                'bidang' => null,
                'user_id' => null,
                'label' => 'Riwayat cetak dan ekspor dokumen laboratorium (seluruh pengguna)',
                'role_label' => 'Pengarsip Hasil',
                'can_filter_user' => true,
                'can_filter_bidang' => true,
                'allowed_actions' => self::PENGARSIP_ACTIONS,
            ];
        }

        if (self::isKoorKlinik($level, $labCode)) {
            return [
                'mode' => self::MODE_BIDANG,
                'bidang' => 'klinik',
                'user_id' => null,
                'label' => 'Semua aktivitas bidang klinik (seluruh pengguna klinik)',
                'role_label' => 'Koordinator / Kepala Lab Klinik',
                'can_filter_user' => true,
                'can_filter_bidang' => false,
            ];
        }

        if (self::isKoorKesmas($level, $labCode)) {
            return [
                'mode' => self::MODE_BIDANG,
                'bidang' => 'kesmas',
                'user_id' => null,
                'label' => 'Semua aktivitas bidang kesmas (seluruh pengguna kesmas)',
                'role_label' => 'Koordinator / Kepala Lab Kesmas',
                'can_filter_user' => true,
                'can_filter_bidang' => false,
            ];
        }

        return self::ownScope(
            (string) $user->id,
            $privilegeName ?: $level,
            $user->username ?? $user->name
        );
    }

    public static function applyScope(Builder $query, array $scope): Builder
    {
        if ($scope['mode'] === self::MODE_PRINT_EXPORT) {
            $query->whereIn('action', self::PENGARSIP_ACTIONS);
        }

        if ($scope['mode'] === self::MODE_OWN && !empty($scope['user_id'])) {
            $query->where(function ($q) use ($scope) {
                $q->where('user_id', $scope['user_id']);
                if (!empty($scope['privilege_level'])) {
                    $q->orWhere('privilege_level', $scope['privilege_level']);
                }
            });
        }

        if ($scope['mode'] === self::MODE_BIDANG && !empty($scope['bidang'])) {
            $query->where('bidang', $scope['bidang']);
        }

        return $query;
    }

    public static function canViewLog(ActivityLog $log, ?User $user = null): bool
    {
        $scope = self::resolveScope($user);

        if ($scope['mode'] === self::MODE_PRINT_EXPORT) {
            return in_array((string) $log->action, self::PENGARSIP_ACTIONS, true);
        }

        if ($scope['mode'] === self::MODE_ALL) {
            return true;
        }

        if ($scope['mode'] === self::MODE_OWN) {
            if ((string) $log->user_id === (string) $scope['user_id']) {
                return true;
            }

            return !empty($scope['privilege_level'])
                && (string) $log->privilege_level === (string) $scope['privilege_level'];
        }

        if ($scope['mode'] === self::MODE_BIDANG) {
            return (string) $log->bidang === (string) $scope['bidang'];
        }

        return false;
    }

    private static function isBendahara(string $level): bool
    {
        return in_array($level, self::BENDAHARA_LEVELS, true);
    }

    private static function isSuperAdmin(string $level): bool
    {
        return in_array($level, self::SUPER_ADMIN_LEVELS, true);
    }

    private static function isFullAdmin(string $level): bool
    {
        return in_array($level, self::FULL_ADMIN_LEVELS, true);
    }

    private static function isKepalaUptd(string $level): bool
    {
        return in_array($level, self::KEPALA_UPTD_LEVELS, true);
    }

    private static function isPengarsip(string $level): bool
    {
        return in_array($level, self::PENGARSIP_LEVELS, true);
    }

    private static function isKlinikLab(string $labCode): bool
    {
        return in_array($labCode, self::KLINIK_LAB_CODES, true);
    }

    private static function isKesmasLab(string $labCode): bool
    {
        return in_array($labCode, self::KESMAS_LAB_CODES, true);
    }

    private static function isKoorKlinik(string $level, string $labCode): bool
    {
        if ($level === 'KSKL') {
            return true;
        }

        if (!in_array($level, self::KOOR_KLINIK_LEVELS, true)) {
            return false;
        }

        if ($level === 'DKTR') {
            return true;
        }

        return self::isKlinikLab($labCode);
    }

    private static function isKoorKesmas(string $level, string $labCode): bool
    {
        if (in_array($level, self::KOOR_KESMAS_LEVELS, true)) {
            if ($level === 'KSKM') {
                return true;
            }

            return self::isKesmasLab($labCode);
        }

        return false;
    }

    /**
     * @return array{
     *     mode: string,
     *     bidang: string|null,
     *     user_id: string|null,
     *     label: string,
     *     role_label: string,
     *     can_filter_user: bool,
     *     can_filter_bidang: bool
     * }
     */
    private static function ownScope(?string $userId, string $roleLabel, ?string $username = null): array
    {
        $suffix = $username ? ' (' . $username . ')' : '';

        return [
            'mode' => self::MODE_OWN,
            'bidang' => null,
            'user_id' => $userId,
            'label' => 'Hanya aktivitas akun Anda' . $suffix,
            'role_label' => $roleLabel,
            'can_filter_user' => false,
            'can_filter_bidang' => true,
        ];
    }
}
