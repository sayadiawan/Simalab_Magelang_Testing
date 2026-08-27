<?php

namespace Smt\Masterweb\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2;
use Smt\Masterweb\Models\LabNotification;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Helpers\PetugasStepAccess;

/**
 * Inbox notifikasi LIMS (event + unread/read).
 * Worklist tetap sumber kebenaran pekerjaan yang belum selesai.
 *
 * Tidak men-generate ribuan notifikasi historis: hanya sejak FEATURE_ACTIVATED_AT.
 */
class NotificationInboxService
{
    /**
     * Batas awal fitur — sampel lama tetap di worklist, tidak di-backfill ke notifikasi.
     * Sesuaikan saat deploy produksi jika perlu.
     */
    public const FEATURE_ACTIVATED_AT = '2026-08-25 00:00:00';

    private const DROPDOWN_LIMIT = 20;
    private const SYNC_CANDIDATE_LIMIT = 40;

    /**
     * @return array<string, mixed>
     */
    public function feed(User $user): array
    {
        if (!$this->tableReady()) {
            return $this->emptyFeed($user);
        }

        $this->syncForUser($user);
        $this->autoReadResolved($user);

        $unreadCount = LabNotification::forUser($user->id)->unread()->count();

        $items = LabNotification::forUser($user->id)
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->limit(self::DROPDOWN_LIMIT)
            ->get();

        return [
            'unread' => $unreadCount,
            'total' => $unreadCount, // badge = unread
            'items' => $items->map(function (LabNotification $n) {
                return $this->serialize($n);
            })->values()->all(),
            'groups' => $this->groupItems($items),
            'worklist' => $this->worklistSummary($user),
            'feature_since' => self::FEATURE_ACTIVATED_AT,
        ];
    }

    /**
     * Halaman histori dengan pagination.
     *
     * @return array{items: array, unread: int, pagination: array}
     */
    public function history(User $user, int $page = 1, int $perPage = 30, ?string $filter = null): array
    {
        if (!$this->tableReady()) {
            return [
                'items' => [],
                'unread' => 0,
                'pagination' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
            ];
        }

        $this->syncForUser($user);
        $this->autoReadResolved($user);

        $query = LabNotification::forUser($user->id)->orderByDesc('created_at');

        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', max(1, $page));

        return [
            'items' => collect($paginator->items())->map(function (LabNotification $n) {
                return $this->serialize($n);
            })->values()->all(),
            'unread' => LabNotification::forUser($user->id)->unread()->count(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function markRead(User $user, string $notificationId): bool
    {
        if (!$this->tableReady()) {
            return false;
        }

        $n = LabNotification::forUser($user->id)->where('id', $notificationId)->first();
        if (!$n) {
            return false;
        }

        $n->markRead();

        return true;
    }

    public function markAllRead(User $user): int
    {
        if (!$this->tableReady()) {
            return 0;
        }

        return LabNotification::forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Generate event notifikasi baru untuk user ini (idempotent per type+reference).
     */
    public function syncForUser(User $user): void
    {
        if (!$this->tableReady()) {
            return;
        }

        $level = $user->getlevel->level ?? null;
        $since = Carbon::parse(self::FEATURE_ACTIVATED_AT);
        $userLab = $user->laboratorium()->first();
        $userKodeLab = $userLab ? $userLab->kode_laboratorium : null;

        // 1. Pengambilan Sampel Klinik (SOLK, KSKL, ADMN, ALAB klinik)
        if (in_array($level, ['SOLK', 'KSKL', 'ADMN', 'ALAB'], true)) {
            $this->syncKlinikSamplePickup($user, $level, $since);
        }

        // 1b. Pengambilan Sampel Kesmas (SOLM, SOLAB, KSKM, ADMN, admin, elits-dev, ALAB kesmas)
        if (in_array($level, ['SOLM', 'SOLAB', 'KSKM', 'ADMN', 'admin', 'elits-dev'], true)
            || (in_array($level, ['ALAB'], true) && (!$userKodeLab || in_array($userKodeLab, ['KIM', 'KMA', 'FKA', 'MBI'], true)))) {
            $this->syncKesmasSamplePickup($user, $level, $since, $userKodeLab);
        }

        // 2. Penerimaan Sampel Klinik (ANLS, ALAB klinik/umum, PLAB, KSKL, ADMN, LAB, admin, elits-dev)
        if (in_array($level, ['ANLS', 'ALAB', 'PLAB', 'KSKL', 'ADMN', 'admin', 'elits-dev', 'LAB'], true)) {
            if (!$userKodeLab || $userKodeLab === 'KLI' || in_array($level, ['KSKL', 'ADMN', 'admin', 'elits-dev', 'PLAB', 'LAB'], true)) {
                $this->syncKlinikSampleReceipt($user, $level, $since);
            }
        }

        // 3. Penerimaan Sampel Kesmas (ALAB kimia/mikro/umum, ANLS, PLAB, KSKM, ADMN, LAB, admin, elits-dev)
        if (in_array($level, ['ALAB', 'ANLS', 'PLAB', 'KSKM', 'ADMN', 'admin', 'elits-dev', 'LAB'], true)) {
            if (!$userKodeLab || in_array($userKodeLab, ['KIM', 'KMA', 'FKA', 'MBI'], true) || in_array($level, ['KSKM', 'ADMN', 'admin', 'elits-dev', 'PLAB', 'LAB'], true)) {
                $this->syncKesmasSampleReceipt($user, $level, $since, $userKodeLab);
            }
        }

        // 4. Pemeriksaan / Analis Klinik (ANLS, ALAB klinik/umum, PLAB, KSKL, ADMN, LAB, admin, elits-dev)
        if (in_array($level, ['ANLS', 'ALAB', 'PLAB', 'ADMN', 'admin', 'elits-dev', 'LAB'], true)) {
            if (!$userKodeLab || $userKodeLab === 'KLI' || in_array($level, ['ADMN', 'admin', 'elits-dev', 'PLAB', 'LAB'], true)) {
                $this->syncKlinikSampleReady($user, $level, $since);
            }
        }

        // 5. Belum Lunas Klinik & Kesmas (BNDR, RGSTR, ADMN, admin, elits-dev)
        if (in_array($level, ['BNDR', 'RGSTR', 'ADMN', 'ALAB', 'admin', 'elits-dev'], true)) {
            $this->syncUnpaidKlinik($user, $level, $since);
        }

        if (in_array($level, ['BNDR', 'RGSTR', 'ADMN', 'admin', 'elits-dev'], true)) {
            $this->syncUnpaidKesmas($user, $level, $since);
        }
    }

    private function syncKlinikSampleReady(User $user, string $level, Carbon $since): void
    {
        // Permohonan baru sejak fitur aktif, sudah punya parameter, belum selesai validasi
        $rows = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien', 'permohonanujiparameterklinik.parametersatuanklinik'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->whereHas('permohonanujiparameterklinik', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT)
            ->get();

        foreach ($rows as $row) {
            if ($this->klinikAlreadyValidated($row->id_permohonan_uji_klinik)) {
                continue;
            }

            // Hanya yang masih butuh tindakan analis (belum lewat tahap pemeriksaan selesai total)
            if (!$this->klinikNeedsAnalystAttention($row->id_permohonan_uji_klinik)) {
                continue;
            }

            $pasien = optional($row->pasien)->nama_pasien ?: '-';
            $noreg = $row->noregister_permohonan_uji_klinik ?: substr((string) $row->id_permohonan_uji_klinik, 0, 8);
            $params = $row->permohonanujiparameterklinik
                ->map(function ($p) {
                    return optional($p->parametersatuanklinik)->name_parameter_satuan_klinik
                        ?? optional($p->parametersatuanklinik)->nama_parameter_satuan_klinik
                        ?? null;
                })
                ->filter()
                ->unique()
                ->take(5)
                ->implode(', ');

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'klinik_sample_new',
                'reference_type' => 'permohonan_uji_klinik',
                'reference_id' => (string) $row->id_permohonan_uji_klinik,
                'title' => 'Sampel baru siap diperiksa',
                'message' => trim('No. ' . $noreg . ' · ' . $pasien . ($params ? "\n" . $params : '')),
                'url' => url('elits-permohonan-uji-klinik/verifikasi/lists') . '?status_filter=pemeriksaan',
                'icon' => 'fa-flask',
                'color' => 'primary',
                'meta' => [
                    'noregister' => $noreg,
                    'pasien' => $pasien,
                    'parameters' => $params,
                ],
                'created_at' => $row->created_at ?: now(),
            ]);
        }
    }

    private function syncKlinikSamplePickup(User $user, string $level, Carbon $since): void
    {
        $rows = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->whereHas('permohonanujiparameterklinik', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT)
            ->get();

        foreach ($rows as $row) {
            if (!$this->klinikNeedsSamplePickup($row->id_permohonan_uji_klinik)) {
                continue;
            }

            $pasien = optional($row->pasien)->nama_pasien ?: '-';
            $noreg = $row->noregister_permohonan_uji_klinik ?: '-';

            $targetUrl = url('elits-permohonan-uji-klinik-2/create-permohonan-uji-sample/' . $row->id_permohonan_uji_klinik . '/1');

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'klinik_sample_pickup',
                'reference_type' => 'permohonan_uji_klinik',
                'reference_id' => (string) $row->id_permohonan_uji_klinik,
                'title' => 'Sampel baru menunggu diambil',
                'message' => 'No. ' . $noreg . ' · ' . $pasien,
                'url' => $targetUrl,
                'icon' => 'fa-vial',
                'color' => 'info',
                'meta' => ['noregister' => $noreg, 'pasien' => $pasien],
                'created_at' => $row->created_at ?: now(),
            ]);
        }
    }

    private function syncKlinikSampleReceipt(User $user, string $level, Carbon $since): void
    {
        $rows = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->whereHas('permohonanujiparameterklinik', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT)
            ->get();

        foreach ($rows as $row) {
            if (!$this->klinikNeedsSampleReceipt($row->id_permohonan_uji_klinik)) {
                continue;
            }

            $pasien = optional($row->pasien)->nama_pasien ?: '-';
            $noreg = $row->noregister_permohonan_uji_klinik ?: '-';
            $targetUrl = url('elits-permohonan-uji-klinik-2/create-penerima-sampel/' . $row->id_permohonan_uji_klinik);

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'klinik_sample_receipt',
                'reference_type' => 'permohonan_uji_klinik',
                'reference_id' => (string) $row->id_permohonan_uji_klinik,
                'title' => 'Sampel klinik siap diterima',
                'message' => 'No. ' . $noreg . ' · ' . $pasien,
                'url' => $targetUrl,
                'icon' => 'fa-inbox',
                'color' => 'warning',
                'meta' => ['noregister' => $noreg, 'pasien' => $pasien],
                'created_at' => $row->created_at ?: now(),
            ]);
        }
    }

    private function syncKesmasSamplePickup(User $user, string $level, Carbon $since, ?string $userKodeLab): void
    {
        $query = Sample::query()
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->where('tb_samples.is_sampling', 1)
                    ->orWhere(function ($sub) {
                        $sub->whereNull('tb_samples.is_sampling')
                            ->whereHas('permohonanuji', function ($pq) {
                                $pq->where('is_sampling', 1)->orWhereNull('is_sampling');
                            });
                    });
            })
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT);

        if ($userKodeLab && in_array($userKodeLab, ['KIM', 'KMA', 'FKA', 'MBI'], true)) {
            $query->whereHas('samplemethod.laboratorium', function ($q) use ($userKodeLab) {
                $q->where('kode_laboratorium', $userKodeLab);
            });
        }

        $samples = $query->get();

        foreach ($samples as $sample) {
            if (!$this->kesmasNeedsSamplePickup($sample->id_samples)) {
                continue;
            }

            $customer = optional(optional($sample->permohonanuji)->customer)->name_customer ?: '-';
            $code = $sample->codesample_samples ?: '-';

            $firstLab = optional($sample->samplemethod->first())->laboratorium;
            $labName = $firstLab ? $firstLab->nama_laboratorium : 'Kesmas';
            $labId = $firstLab ? $firstLab->id_laboratorium : '';

            $targetUrl = $labId
                ? url('elits-samples/verification-2/' . $sample->id_samples . '/' . $labId)
                : url('elits-analys?status_filter=pengambilan_sample');

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'kesmas_sample_pickup',
                'reference_type' => 'sample',
                'reference_id' => (string) $sample->id_samples,
                'title' => 'Sampel Kesmas baru menunggu diambil (' . $labName . ')',
                'message' => 'Kode ' . $code . ' · ' . $customer,
                'url' => $targetUrl,
                'icon' => 'fa-vial',
                'color' => 'info',
                'meta' => [
                    'codesample' => $code,
                    'customer' => $customer,
                    'laboratorium' => $labName,
                ],
                'created_at' => $sample->created_at ?: now(),
            ]);
        }
    }

    private function syncKesmasSampleReceipt(User $user, string $level, Carbon $since, ?string $userKodeLab): void
    {
        $query = Sample::query()
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT);

        if ($userKodeLab && in_array($userKodeLab, ['KIM', 'KMA', 'FKA', 'MBI'], true)) {
            $query->whereHas('samplemethod.laboratorium', function ($q) use ($userKodeLab) {
                $q->where('kode_laboratorium', $userKodeLab);
            });
        }

        $samples = $query->get();

        foreach ($samples as $sample) {
            if (!$this->kesmasNeedsSampleReceipt($sample->id_samples)) {
                continue;
            }

            $customer = optional(optional($sample->permohonanuji)->customer)->name_customer ?: '-';
            $code = $sample->codesample_samples ?: '-';

            $firstLab = optional($sample->samplemethod->first())->laboratorium;
            $labName = $firstLab ? $firstLab->nama_laboratorium : 'Kesmas';
            $labId = $firstLab ? $firstLab->id_laboratorium : '';

            $targetUrl = $labId
                ? url('elits-samples/verification-2/' . $sample->id_samples . '/' . $labId)
                : url('elits-analys?status_filter=penerimaan_sample');

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'kesmas_sample_receipt',
                'reference_type' => 'sample',
                'reference_id' => (string) $sample->id_samples,
                'title' => 'Sampel Kesmas siap diterima (' . $labName . ')',
                'message' => 'Kode ' . $code . ' · ' . $customer,
                'url' => $targetUrl,
                'icon' => 'fa-inbox',
                'color' => 'warning',
                'meta' => [
                    'codesample' => $code,
                    'customer' => $customer,
                    'laboratorium' => $labName,
                ],
                'created_at' => $sample->created_at ?: now(),
            ]);
        }
    }

    private function syncUnpaidKlinik(User $user, string $level, Carbon $since): void
    {
        $rows = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien'])
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNull('status_pembayaran')
                    ->orWhere('status_pembayaran', '!=', 1);
            })
            ->orderByDesc('created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT)
            ->get();

        foreach ($rows as $row) {
            $pasien = optional($row->pasien)->nama_pasien ?: '-';
            $noreg = $row->noregister_permohonan_uji_klinik ?: '-';
            $url = in_array($level, ['BNDR', 'ADMN'], true)
                ? url('bendahara/pembayaran-pemeriksaan')
                : url('elits-permohonan-uji-klinik/registrasi');

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'klinik_unpaid',
                'reference_type' => 'permohonan_uji_klinik',
                'reference_id' => (string) $row->id_permohonan_uji_klinik,
                'title' => 'Pendaftaran baru belum lunas',
                'message' => 'No. ' . $noreg . ' · ' . $pasien,
                'url' => $url,
                'icon' => 'fa-money-bill-wave',
                'color' => 'danger',
                'meta' => ['noregister' => $noreg, 'pasien' => $pasien],
                'created_at' => $row->created_at ?: now(),
            ]);
        }
    }

    private function syncUnpaidKesmas(User $user, string $level, Carbon $since): void
    {
        $rows = PermohonanUji::query()
            ->leftJoin('ms_customer', function ($join) {
                $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
                    ->whereNull('ms_customer.deleted_at');
            })
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->where('tb_permohonan_uji.created_at', '>=', $since)
            ->where(function ($q) {
                $q->whereNull('tb_permohonan_uji.status_pembayaran')
                    ->orWhere('tb_permohonan_uji.status_pembayaran', '!=', 1);
            })
            ->orderByDesc('tb_permohonan_uji.created_at')
            ->limit(self::SYNC_CANDIDATE_LIMIT)
            ->get([
                'tb_permohonan_uji.id_permohonan_uji',
                'tb_permohonan_uji.code_permohonan_uji',
                'tb_permohonan_uji.created_at',
                'ms_customer.name_customer',
            ]);

        foreach ($rows as $row) {
            $kode = $row->code_permohonan_uji ?: '-';
            $nama = $row->name_customer ?: '-';
            $url = in_array($level, ['BNDR', 'ADMN'], true)
                ? url('bendahara/pembayaran-pemeriksaan')
                : url('elits-permohonan-uji');

            $title = in_array($level, ['BNDR', 'ADMN'], true)
                ? 'Pendaftaran Kesmas: Terbitkan Nota'
                : 'Pendaftaran Kesmas belum lunas';

            $this->ensureNotification($user, [
                'role_level' => $level,
                'type' => 'kesmas_unpaid',
                'reference_type' => 'permohonan_uji',
                'reference_id' => (string) $row->id_permohonan_uji,
                'title' => $title,
                'message' => $kode . ' · ' . $nama,
                'url' => $url,
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'danger',
                'meta' => ['kode' => $kode, 'pelanggan' => $nama],
                'created_at' => $row->created_at ?: now(),
            ]);
        }
    }

    /**
     * Tandai terbaca otomatis jika pekerjaan sudah selesai / sudah lunas.
     */
    private function autoReadResolved(User $user): void
    {
        $unread = LabNotification::forUser($user->id)->unread()->limit(100)->get();

        foreach ($unread as $n) {
            $resolved = false;

            if ($n->type === 'klinik_unpaid' && $n->reference_id) {
                $paid = PermohonanUjiKlinik2::query()
                    ->where('id_permohonan_uji_klinik', $n->reference_id)
                    ->where('status_pembayaran', 1)
                    ->exists();
                $resolved = $paid;
            } elseif ($n->type === 'kesmas_unpaid' && $n->reference_id) {
                $paid = PermohonanUji::query()
                    ->where('id_permohonan_uji', $n->reference_id)
                    ->where('status_pembayaran', 1)
                    ->exists();
                $resolved = $paid;
            } elseif ($n->type === 'klinik_sample_new' && $n->reference_id) {
                $resolved = $this->klinikAlreadyValidated($n->reference_id)
                    || !$this->klinikNeedsAnalystAttention($n->reference_id);
            } elseif ($n->type === 'klinik_sample_pickup' && $n->reference_id) {
                $resolved = !$this->klinikNeedsSamplePickup($n->reference_id);
            } elseif ($n->type === 'kesmas_sample_pickup' && $n->reference_id) {
                $resolved = !$this->kesmasNeedsSamplePickup($n->reference_id);
            } elseif ($n->type === 'klinik_sample_receipt' && $n->reference_id) {
                $resolved = !$this->klinikNeedsSampleReceipt($n->reference_id);
            } elseif ($n->type === 'kesmas_sample_receipt' && $n->reference_id) {
                $resolved = !$this->kesmasNeedsSampleReceipt($n->reference_id);
            }

            if ($resolved) {
                $n->markRead();
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ensureNotification(User $user, array $data): void
    {
        $existing = LabNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $data['type'])
            ->where('reference_id', $data['reference_id'])
            ->first();

        if ($existing) {
            if (isset($data['url']) && $existing->url !== $data['url']) {
                $existing->url = $data['url'];
                $existing->save();
            }
            return;
        }

        $createdAt = $data['created_at'] ?? now();
        unset($data['created_at']);

        $n = new LabNotification(array_merge($data, [
            'user_id' => $user->id,
        ]));
        $n->created_at = $createdAt;
        $n->updated_at = now();
        $n->save();
    }

    private function klinikAlreadyValidated(string $id): bool
    {
        return \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 5)
            ->where('is_done', 1)
            ->exists();
    }

    private function klinikNeedsAnalystAttention(string $id): bool
    {
        // Step 2 belum selesai, ATAU step 3/4 belum selesai (masih di pipeline analis)
        $step2Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 2)
            ->where('is_done', 1)
            ->exists();
        $step4Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 4)
            ->where('is_done', 1)
            ->exists();

        return !$step4Done;
    }

    private function klinikNeedsSamplePickup(string $id): bool
    {
        $step1Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 1)
            ->where('is_done', 1)
            ->exists();
        $step6Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->exists();
        $subsequentDone = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->whereIn('id_verification_activity', [7, 2, 3, 4, 5])
            ->where('is_done', 1)
            ->exists();

        $isSelesai = \DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik', $id)
            ->where('status_permohonan_uji_klinik', 'SELESAI')
            ->exists();

        return $step1Done && !$step6Done && !$subsequentDone && !$isSelesai;
    }

    private function kesmasNeedsSamplePickup(string $idSample): bool
    {
        $sample = \DB::table('tb_samples')
            ->leftJoin('tb_permohonan_uji', 'tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
            ->where('tb_samples.id_samples', $idSample)
            ->whereNull('tb_samples.deleted_at')
            ->select('tb_samples.is_sampling as sample_is_sampling', 'tb_permohonan_uji.is_sampling as permohonan_is_sampling')
            ->first();

        if (!$sample) {
            return false;
        }

        $isSampling = $sample->sample_is_sampling !== null ? (int) $sample->sample_is_sampling : (int) ($sample->permohonan_is_sampling ?? 1);
        if ($isSampling !== 1) {
            return false;
        }

        $step6Done = \DB::table('tb_verification_activity_samples')
            ->where('id_sample', $idSample)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->exists();

        $subsequentDone = \DB::table('tb_verification_activity_samples')
            ->where('id_sample', $idSample)
            ->whereIn('id_verification_activity', [7, 2, 3, 4, 5])
            ->where('is_done', 1)
            ->exists();

        return !$step6Done && !$subsequentDone;
    }

    private function klinikNeedsSampleReceipt(string $id): bool
    {
        $step6Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->exists();
        $step7Done = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->where('id_verification_activity', 7)
            ->where('is_done', 1)
            ->exists();
        $subsequentDone = \DB::table('tb_verification_activity_samples')
            ->where('is_klinik', $id)
            ->whereIn('id_verification_activity', [2, 3, 4, 5])
            ->where('is_done', 1)
            ->exists();

        $isSelesai = \DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik', $id)
            ->where('status_permohonan_uji_klinik', 'SELESAI')
            ->exists();

        return $step6Done && !$step7Done && !$subsequentDone && !$isSelesai;
    }

    private function kesmasNeedsSampleReceipt(string $idSample): bool
    {
        $sample = \DB::table('tb_samples')
            ->leftJoin('tb_permohonan_uji', 'tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
            ->where('tb_samples.id_samples', $idSample)
            ->whereNull('tb_samples.deleted_at')
            ->select('tb_samples.is_sampling as sample_is_sampling', 'tb_permohonan_uji.is_sampling as permohonan_is_sampling')
            ->first();

        if (!$sample) {
            return false;
        }

        $isSampling = $sample->sample_is_sampling !== null ? (int) $sample->sample_is_sampling : (int) ($sample->permohonan_is_sampling ?? 1);

        $step6Done = \DB::table('tb_verification_activity_samples')
            ->where('id_sample', $idSample)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->exists();

        $step7Done = \DB::table('tb_verification_activity_samples')
            ->where('id_sample', $idSample)
            ->where('id_verification_activity', 7)
            ->where('is_done', 1)
            ->exists();

        $subsequentDone = \DB::table('tb_verification_activity_samples')
            ->where('id_sample', $idSample)
            ->whereIn('id_verification_activity', [2, 3, 4, 5])
            ->where('is_done', 1)
            ->exists();

        if ($subsequentDone || $step7Done) {
            return false;
        }

        if ($isSampling === 1) {
            return $step6Done;
        }

        return true;
    }

    /**
     * Ringkasan worklist (sumber kebenaran antrian) — terpisah dari notifikasi event.
     *
     * @return array<int, array<string, mixed>>
     */
    private function worklistSummary(User $user): array
    {
        $level = $user->getlevel->level ?? null;
        $steps = $this->worklistStepsForRole($level);
        if ($steps === []) {
            return [];
        }

        $userLab = $user->laboratorium()->first();
        $isKesmasUser = ($userLab && in_array($userLab->kode_laboratorium, ['KIM', 'KMA', 'FKA', 'MBI'], true)) || ($level === 'KSKM');

        try {
            if ($isKesmasUser) {
                $controller = app(\Smt\Masterweb\Http\Controllers\LaboratoriumAnalysManagement::class);
                $response = $controller->getStatisticsAnalys(new Request());
            } else {
                $controller = app(LaboratoriumPermohonanUjiKlinikManagement2::class);
                $response = $controller->getStatisticsVerifikasi(new Request(['is_filter' => 'all']));
            }
            $stats = json_decode($response->getContent(), true);
            if (!is_array($stats)) {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }

        $labels = [
            'penerimaan_sample' => 'Penerimaan sample',
            'pemeriksaan' => 'Menunggu pemeriksaan',
            'input_hasil' => 'Input hasil',
            'verifikasi' => 'Menunggu verifikasi',
            'validasi' => 'Menunggu validasi',
            'pengambilan_sample' => 'Pengambilan sample',
            'belum_pemeriksaan' => 'Belum lengkap',
        ];

        $baseUrl = $isKesmasUser
            ? url('elits-analys')
            : url('elits-permohonan-uji-klinik/verifikasi/lists');

        $items = [];
        foreach ($steps as $step) {
            $count = (int) ($stats[$step] ?? 0);
            if ($count <= 0) {
                continue;
            }
            $items[] = [
                'key' => $step,
                'label' => $labels[$step] ?? $step,
                'count' => $count,
                'url' => $baseUrl . '?status_filter=' . $step,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, string>
     */
    private function worklistStepsForRole(?string $level): array
    {
        if (in_array($level, ['ANLS', 'ALAB'], true)) {
            $user = auth()->user();
            $filters = PetugasStepAccess::getAllowedFilters($user, false, false);
            return $filters !== [] ? $filters : ['penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi'];
        }
        if ($level === 'SOLK') {
            return ['pengambilan_sample'];
        }
        if ($level === 'SOLM') {
            return ['pengambilan_sample'];
        }
        if ($level === 'DKTR') {
            return ['belum_pemeriksaan', 'validasi'];
        }
        if (in_array($level, ['PLAB', 'KSKL', 'KSKM', 'ADMN', 'admin', 'elits-dev', 'LAB'], true)) {
            return ['penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi'];
        }
        if ($level === 'RGSTR') {
            return ['belum_pemeriksaan'];
        }

        return [];
    }

    /**
     * @param \Illuminate\Support\Collection|array $items
     * @return array<string, array>
     */
    private function groupItems($items): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $groups = [
            'Hari ini' => [],
            'Kemarin' => [],
            'Sebelumnya' => [],
        ];

        foreach ($items as $n) {
            $serialized = $n instanceof LabNotification ? $this->serialize($n) : $n;
            $at = Carbon::parse($serialized['created_at']);
            if ($at->isSameDay($today)) {
                $groups['Hari ini'][] = $serialized;
            } elseif ($at->isSameDay($yesterday)) {
                $groups['Kemarin'][] = $serialized;
            } else {
                $groups['Sebelumnya'][] = $serialized;
            }
        }

        return array_filter($groups, function ($rows) {
            return count($rows) > 0;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(LabNotification $n): array
    {
        $url = $n->url;
        if ($n->type === 'klinik_sample_pickup' && $n->reference_id) {
            $url = url('elits-permohonan-uji-klinik-2/create-permohonan-uji-sample/' . $n->reference_id . '/1');
        } elseif ($n->type === 'klinik_sample_receipt' && $n->reference_id) {
            $url = url('elits-permohonan-uji-klinik-2/create-penerima-sampel/' . $n->reference_id);
        }

        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'url' => $url,
            'icon' => $n->icon ?: 'fa-bell',
            'color' => $n->color ?: 'secondary',
            'unread' => $n->isUnread(),
            'created_at' => optional($n->created_at)->toDateTimeString(),
            'created_human' => optional($n->created_at)->diffForHumans(),
            'read_at' => optional($n->read_at)->toDateTimeString(),
            'meta' => $n->meta,
        ];
    }

    private function tableReady(): bool
    {
        try {
            return Schema::hasTable('tb_notifications');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyFeed(User $user): array
    {
        return [
            'unread' => 0,
            'total' => 0,
            'items' => [],
            'groups' => [],
            'worklist' => $this->worklistSummary($user),
            'feature_since' => self::FEATURE_ACTIVATED_AT,
            'warning' => 'Tabel notifikasi belum tersedia. Jalankan migrasi.',
        ];
    }
}
