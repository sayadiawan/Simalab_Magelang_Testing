<?php

namespace Smt\Masterweb\Helpers;

use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\VerificationActivity;

/**
 * Step verifikasi klinik berdasarkan peran petugas (ms_petugas.role = id ms_verification_activities).
 */
class PetugasStepAccess
{
    /** id activity → status_filter di verifikasi/lists */
    public const ACTIVITY_TO_FILTER = [
        1 => 'belum_pemeriksaan',
        2 => 'pemeriksaan',
        3 => 'input_hasil',
        4 => 'verifikasi',
        5 => 'validasi',
        6 => 'pengambilan_sample',
        7 => 'penerimaan_sample',
        // 8 = Disposisi Sampel — belum ada tab khusus di lists
    ];

    public const FILTER_META = [
        'belum_pemeriksaan' => [
            'label' => 'Belum Pemeriksaan',
            'icon' => 'fa fa-clock',
            'order' => 10,
        ],
        'pengambilan_sample' => [
            'label' => 'Pengambilan Sampel',
            'icon' => 'ti-layers',
            'order' => 14,
        ],
        'penerimaan_sample' => [
            'label' => 'Penerimaan Sampel',
            'icon' => 'ti-import',
            'order' => 15,
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan',
            'icon' => 'ti-save-alt',
            'order' => 16,
        ],
        'input_hasil' => [
            'label' => 'Input Hasil',
            'icon' => 'ti-pencil-alt',
            'order' => 17,
        ],
        'verifikasi' => [
            'label' => 'Verifikasi',
            'icon' => 'fas fa-clinic-medical',
            'order' => 18,
        ],
        'validasi' => [
            'label' => 'Validasi Hasil',
            'icon' => 'ti-save-alt',
            'order' => 19,
        ],
    ];

    /**
     * Level privilege yang memakai step dari relasi petugas.
     */
    public static function usesPetugasSteps(?string $userLevel): bool
    {
        return in_array($userLevel, ['ANLS', 'ALAB'], true);
    }

    public static function getPetugasForUser(?User $user): ?Petugas
    {
        if (!$user || empty($user->id_petugas)) {
            return null;
        }

        return Petugas::find($user->id_petugas);
    }

    /**
     * ID activity dari ms_petugas.role (string/int dinormalisasi).
     *
     * @return array<int, int>
     */
    public static function getActivityIds(?User $user): array
    {
        $petugas = self::getPetugasForUser($user);
        if (!$petugas) {
            return [];
        }

        $roles = $petugas->role;
        if (is_string($roles)) {
            $roles = json_decode($roles, true);
        }
        if (!is_array($roles)) {
            return [];
        }

        $ids = [];
        foreach ($roles as $role) {
            if ($role === null || $role === '') {
                continue;
            }
            $ids[] = (int) $role;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * status_filter yang boleh diakses petugas (urut workflow).
     *
     * @return array<int, string>
     */
    public static function getAllowedFilters(?User $user, bool $includeAll = true, bool $includeSelesai = true): array
    {
        $userLevel = $user && $user->getlevel ? $user->getlevel->level : null;
        if (!self::usesPetugasSteps($userLevel)) {
            return [];
        }

        $filters = [];
        foreach (self::getActivityIds($user) as $activityId) {
            if (isset(self::ACTIVITY_TO_FILTER[$activityId])) {
                $filters[] = self::ACTIVITY_TO_FILTER[$activityId];
            }
        }

        // Fallback bila petugas belum punya role: tetap bisa buka antrian analisa
        if ($filters === []) {
            $filters = ['pemeriksaan', 'input_hasil', 'verifikasi'];
        }

        $order = array_keys(self::FILTER_META);
        usort($filters, function ($a, $b) use ($order) {
            return array_search($a, $order, true) <=> array_search($b, $order, true);
        });

        $result = [];
        if ($includeAll) {
            $result[] = 'all';
        }
        foreach ($filters as $f) {
            $result[] = $f;
        }
        if ($includeSelesai) {
            $result[] = 'selesai';
        }

        return array_values(array_unique($result));
    }

    /**
     * Item menu sidebar (mirip kasie): satu link per step.
     *
     * @return array<int, array{label: string, icon: string, filter: string, url: string}>
     */
    public static function getSidebarMenus(?User $user): array
    {
        $filters = self::getAllowedFilters($user, false, false);
        if ($filters === []) {
            return [];
        }

        $base = url('elits-permohonan-uji-klinik/verifikasi/lists');
        $menus = [];

        foreach ($filters as $filter) {
            $meta = self::FILTER_META[$filter] ?? null;
            if (!$meta) {
                continue;
            }
            $menus[] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'filter' => $filter,
                'url' => $base . '?status_filter=' . $filter,
                'order' => $meta['order'],
            ];
        }

        usort($menus, function ($a, $b) {
            return ($a['order'] ?? 99) <=> ($b['order'] ?? 99);
        });

        return $menus;
    }

    /**
     * Nama activity untuk debug/UI (opsional).
     *
     * @return array<int, string>
     */
    public static function getActivityNames(?User $user): array
    {
        $ids = self::getActivityIds($user);
        if ($ids === []) {
            return [];
        }

        return VerificationActivity::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id')
            ->all();
    }
}
