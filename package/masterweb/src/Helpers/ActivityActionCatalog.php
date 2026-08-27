<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Http\Request;

/**
 * Kamus aksi & objek log aktivitas — selaras fitur PPT SimaLab.
 */
class ActivityActionCatalog
{
    /** Label aksi untuk tampilan UI (Bahasa Indonesia). */
    public const ACTION_LABELS = [
        'create' => 'Tambah',
        'update' => 'Edit',
        'delete' => 'Hapus',
        'print' => 'Cetak',
        'export' => 'Ekspor',
        'view' => 'Lihat',
        'validate' => 'Validasi',
        'confirm' => 'Konfirmasi',
        'login' => 'Login',
        'login_failed' => 'Login Gagal',
        'logout' => 'Logout',
        'other' => 'Lainnya',
    ];

    /** Kategori akun PPT. */
    public const PPT_KATEGORI = [
        'pendaftaran' => 'Akun Pendaftaran',
        'pembayaran' => 'Akun Pembayaran (Bendahara)',
        'pengambil_sampel' => 'Akun Pengambil Sampel',
        'penjab_ruangan' => 'Akun Penjab Ruangan',
        'pencetak' => 'Akun Pencetak Hasil & Arsiparis',
        'kasie_klinis' => 'Akun Kasie Lab Klinis',
        'kasie_kesmas' => 'Akun Kasie Lab Kesmas',
        'ktu' => 'Akun KTU',
        'kepala_uptd' => 'Akun Kepala UPTD Labkes',
        'admin' => 'Akun Admin',
        'umum' => 'Umum',
    ];

    /**
     * Modul/path → label objek (Bahasa Indonesia).
     *
     * @var array<string, string>
     */
    private const MODULE_LABELS = [
        'elits-permohonan-uji-klinik-2' => 'Permohonan Uji Klinik',
        'elits-permohonan-uji-klinik' => 'Permohonan Uji Klinik',
        'elits-pasien' => 'Data Pasien/Klien',
        'elits-customers' => 'Data Klien/Pelanggan',
        'elits-release' => 'Rilis/Hasil Kesmas',
        'elits-samples' => 'Data Sampel Kesmas',
        'elits-sample-officer' => 'Petugas Pengambilan Sampel',
        'elits-packet' => 'Paket Layanan',
        'elits-rates' => 'Tarif Layanan',
        'elits-baku-mutu' => 'Baku Mutu Kesmas',
        'elits-baku-mutu-klinik' => 'Baku Mutu Klinik',
        'elits-units' => 'Satuan',
        'elits-methods' => 'Metode Pengujian',
        'elits-parameter-jenis-klinik' => 'Parameter Jenis Klinik',
        'elits-parameter-satuan-klinik' => 'Parameter Satuan Klinik',
        'elits-parameter-paket-klinik' => 'Parameter Paket Klinik',
        'elits-rekam-medis-klinik' => 'Rekam Medis Klinik',
        'elits-pendapatan-klinik' => 'Laporan Pendapatan Klinik',
        'elits-pendapatan-nonklinik' => 'Laporan Pendapatan Kesmas',
        'elits-containers' => 'Wadah Sampel',
        'elits-jenis-sampel-klinik' => 'Jenis Sampel Klinik',
        'adm-users' => 'Akun Pengguna',
        'privileges-elits' => 'Hak Akses/Privilege',
        'menuadm' => 'Menu Admin',
        'activity-log' => 'Log Aktivitas',
        'pengarsipan' => 'Pengarsipan Hasil',
        'pengarsipan-dokumen' => 'Dokumen Arsip Tambahan',
        'klinik-number-settings' => 'Setting Nomor Klinik',
        'kesmas-sample-number-settings' => 'Setting Nomor Kesmas',
        'report-jumlah-jenis-sampel' => 'Laporan Jumlah Jenis Sampel',
        'monitoring-sampling-penerima' => 'Monitoring Pengambilan Sampel',
        'klinik' => 'Analisis Hasil Klinik per Wilayah',
        'home' => 'Beranda/Dashboard',
        'mobile/sampling' => 'Pengambilan Sampel Kesmas (Mobile)',
        'mobile/sampling-klinik' => 'Pengambilan Sampel Klinik (Mobile)',
        'mobile/testing' => 'Pengujian Kesmas (Mobile)',
        'mobile/testing-klinik' => 'Pengujian Klinik (Mobile)',
        'mobile/dokter' => 'Dokter/Validasi Klinik (Mobile)',
        'mobile/signing' => 'Tanda Tangan Digital (Mobile)',
    ];

    public static function actionLabel(string $action): string
    {
        return self::ACTION_LABELS[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    public static function pptKategoriLabel(string $key): string
    {
        return self::PPT_KATEGORI[$key] ?? $key;
    }

    /**
     * Kelas CSS badge aksi — kontras tinggi, tidak bergantung tema Bootstrap.
     */
    public static function badgeClass(string $action): string
    {
        $map = [
            'create' => 'al-act-create',
            'update' => 'al-act-update',
            'delete' => 'al-act-delete',
            'print' => 'al-act-print',
            'export' => 'al-act-export',
            'view' => 'al-act-view',
            'validate' => 'al-act-validate',
            'confirm' => 'al-act-confirm',
            'login' => 'al-act-login',
            'login_failed' => 'al-act-login_failed',
            'logout' => 'al-act-logout',
        ];

        return $map[$action] ?? 'al-act-other';
    }

    /**
     * Lengkapi label tampilan — termasuk log lama tanpa metadata JSON.
     *
     * @param  \Smt\Masterweb\Models\ActivityLog  $log
     * @return array<string, string|null>
     */
    public static function enrichDisplay($log): array
    {
        $meta = is_array($log->metadata) ? $log->metadata : [];
        $action = (string) ($log->action ?? 'other');
        $bidang = (string) ($log->bidang ?? 'umum');
        $combined = strtolower(
            ($log->route_name ?? '') . ' '
            . ($log->module ?? '') . ' '
            . ($log->url ?? '') . ' '
            . ($log->description ?? '')
        );

        $subjectLabel = $meta['subject_label'] ?? null;
        if (!$subjectLabel) {
            $subjectLabel = self::resolveSubjectLabelFromStored(
                (string) ($log->module ?? ''),
                (string) ($log->route_name ?? ''),
                (string) ($log->url ?? '')
            );
        }

        $pptKategori = $meta['ppt_kategori'] ?? null;
        if (!$pptKategori) {
            $pptKategori = self::resolvePptKategori($action, $bidang, $combined);
        }

        $pptFitur = $meta['ppt_fitur'] ?? null;
        if (!$pptFitur) {
            $pptFitur = self::resolvePptFitur($action, $pptKategori, $combined);
        }

        return [
            'action_label' => self::actionLabel($action),
            'badge_class' => self::badgeClass($action),
            'subject_label' => $subjectLabel,
            'ppt_kategori' => $pptKategori,
            'ppt_kategori_label' => self::pptKategoriLabel($pptKategori),
            'ppt_fitur' => $pptFitur,
        ];
    }

    private static function resolveSubjectLabelFromStored(string $module, string $routeName, string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $path = is_string($path) ? trim($path, '/') : $module;

        if ($module !== '' && isset(self::MODULE_LABELS[$module])) {
            return self::refineSubjectLabel(self::MODULE_LABELS[$module], $path, $routeName);
        }

        foreach (self::MODULE_LABELS as $prefix => $label) {
            if ($path === $prefix || strpos($path, $prefix . '/') === 0 || strpos($path, $prefix) === 0) {
                return self::refineSubjectLabel($label, $path, $routeName);
            }
        }

        if ($routeName !== '') {
            $routePrefix = explode('.', $routeName)[0];

            return self::refineSubjectLabel(self::humanizeSlug($routePrefix), $path, $routeName);
        }

        return self::humanizeSlug($module !== '' ? $module : 'Aktivitas Sistem');
    }

    /**
     * Deteksi aksi dari request — lebih spesifik selaras PPT.
     */
    public static function detectAction(Request $request): string
    {
        $method = strtoupper($request->method());
        $path = strtolower($request->path());
        $routeName = strtolower((string) optional($request->route())->getName());
        $combined = $path . ' ' . $routeName;

        if (strpos($routeName, 'login') !== false && $method === 'POST') {
            return 'login';
        }
        if (strpos($routeName, 'logout') !== false || $path === 'logout') {
            return 'logout';
        }

        if (preg_match('#/(print|cetak|nota|lhu|preview-pdf|preview_pdf|qrcode|kartu-medis|label|hasil-klinik|hasil_klinik)#', $combined)) {
            return 'print';
        }

        if (preg_match('#/(export|excel|xlsx|download-report|users/export)#', $combined)) {
            return 'export';
        }

        if (preg_match('#(validasi|verifikasi|verification|pengesahan|store-validasi|store-verifikasi|store-pengesahan|verification-analytic|resend-hasil)#', $combined)) {
            return 'validate';
        }

        if (preg_match('#(pembayaran|payment|konfirmasi|confirm|nota-gabungan|save.*nota)#', $combined) && $method !== 'GET') {
            return 'confirm';
        }

        if ($method === 'DELETE' || preg_match('#(destroy|delete|hapus|remove)#', $combined)) {
            return 'delete';
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            return 'update';
        }

        if ($method === 'POST') {
            if (preg_match('#(update|edit|save-|verification|diagnosis|store-diagnosis|store-parameter|store-input-hasil|store-baca-hasil|store-pemeriksaan|store-penerimaan|store-pengolah|store-pemeriksa|mark-done|finish-drafts|sort|reorder|reset)#', $combined)) {
                if (preg_match('#(validasi|verifikasi|verification|pengesahan)#', $combined)) {
                    return 'validate';
                }

                return 'update';
            }

            if (preg_match('#(store|create|register|tambah|input-id|save-signature|saveSignature)#', $combined)) {
                return 'create';
            }

            return 'update';
        }

        if ($method === 'GET') {
            return 'view';
        }

        return 'other';
    }

    /**
     * @return array{subject_type: string|null, subject_id: string|null, subject_label: string|null}
     */
    public static function resolveSubject(Request $request): array
    {
        $path = $request->path();
        $routeName = (string) optional($request->route())->getName();
        $subjectLabel = self::resolveSubjectLabel($path, $routeName);
        $subjectType = self::resolveSubjectTypeKey($path, $routeName, $subjectLabel);
        $subjectId = self::resolveSubjectId($request);

        return [
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
        ];
    }

    /**
     * Deskripsi manusiawi: "[Tambah] Permohonan Uji Klinik — ID xxx"
     */
    public static function buildDescription(Request $request, string $action, ?string $subjectLabel = null, ?string $subjectId = null): string
    {
        $label = self::actionLabel($action);
        $object = $subjectLabel ?: self::resolveSubjectLabel($request->path(), (string) optional($request->route())->getName());

        if ($subjectId) {
            return sprintf('[%s] %s — ID %s', $label, $object, $subjectId);
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName !== '') {
            return sprintf('[%s] %s — %s', $label, $object, $routeName);
        }

        return sprintf('[%s] %s — %s', $label, $object, $request->path());
    }

    /**
     * Fitur PPT yang paling mendekati aktivitas ini.
     *
     * @return array{ppt_kategori: string, ppt_fitur: string}
     */
    public static function resolvePptMapping(string $action, string $bidang, ?string $subjectLabel, Request $request): array
    {
        $combined = strtolower(
            ($subjectLabel ?? '') . ' '
            . $request->path() . ' '
            . (string) optional($request->route())->getName()
        );

        $pptKategori = self::resolvePptKategori($action, $bidang, $combined);
        $pptFitur = self::resolvePptFitur($action, $pptKategori, $combined);

        return [
            'ppt_kategori' => $pptKategori,
            'ppt_fitur' => $pptFitur,
        ];
    }

    private static function resolveSubjectLabel(string $path, string $routeName): string
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);

        foreach (self::MODULE_LABELS as $prefix => $label) {
            if ($path === $prefix || strpos($path, $prefix . '/') === 0 || strpos($path, $prefix) === 0) {
                return self::refineSubjectLabel($label, $path, $routeName);
            }
        }

        if (isset($segments[0]) && $segments[0] === 'mobile' && isset($segments[1])) {
            $mobileKey = 'mobile/' . $segments[1];

            return self::MODULE_LABELS[$mobileKey] ?? ('Mobile ' . ucfirst($segments[1]));
        }

        if (isset($segments[0]) && strpos($segments[0], 'elits-') === 0) {
            return self::humanizeSlug($segments[0]);
        }

        if ($routeName !== '') {
            $parts = explode('.', $routeName);
            if (count($parts) > 1) {
                return self::humanizeSlug(str_replace('-', ' ', $parts[0]));
            }
        }

        return self::humanizeSlug($segments[0] ?? 'Aktivitas Sistem');
    }

    private static function refineSubjectLabel(string $baseLabel, string $path, string $routeName): string
    {
        $combined = strtolower($path . ' ' . $routeName);

        if (preg_match('#(print.*hasil|hasil-klinik|print-permohonan-uji-klinik-hasil)#', $combined)) {
            return 'Hasil Pemeriksaan Klinik';
        }
        if (preg_match('#(label|qrcode|print.*label)#', $combined)) {
            return 'Label Sampel';
        }
        if (preg_match('#(kartu-medis|rekam-medis|print-kartu)#', $combined)) {
            return 'Kartu Rekam Medis';
        }
        if (preg_match('#(nota|invoice|kwitansi|persuratan\.nota)#', $combined)) {
            return 'Nota/Invoice Pembayaran';
        }
        if (preg_match('#(formulir-pengambilan|pengambilan-sampel|sampling)#', $combined)) {
            return 'Formulir/Pengambilan Sampel';
        }
        if (preg_match('#(printLHU|print-lhu|lhu|inform-concern)#', $combined)) {
            return 'LHU / Laporan Hasil Uji Kesmas';
        }
        if (preg_match('#(verifikasi|validasi|verification|pengesahan)#', $combined)) {
            return 'Verifikasi/Validasi Hasil';
        }
        if (preg_match('#(diagnosis|store-diagnosis)#', $combined)) {
            return 'Diagnosis Klinik';
        }
        if (preg_match('#(pendapatan|laporan|report)#', $combined)) {
            return 'Laporan ' . preg_replace('#^Laporan\s+#', '', $baseLabel);
        }
        if (preg_match('#(baku-mutu|baku_mutu)#', $combined)) {
            return 'Baku Mutu';
        }
        if (preg_match('#(rates|tarif|packet|paket)#', $combined)) {
            return 'Tarif & Paket Layanan';
        }

        return $baseLabel;
    }

    private static function resolveSubjectTypeKey(string $path, string $routeName, ?string $subjectLabel): ?string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $subjectLabel ?? ''));

        return $slug !== '' ? trim($slug, '_') : null;
    }

    private static function resolveSubjectId(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $priorityKeys = [
            'id_permohonan_uji_klinik',
            'idPermohonanUji',
            'id_permohonan_uji',
            'permohonan_uji_klinik_id',
            'sample_id',
            'id_sample',
            'lab_id',
            'idlab',
            'pasien_id',
            'id_pasien',
            'customer_id',
            'draft_id',
            'group_id',
            'method_id',
            'id',
        ];

        foreach ($priorityKeys as $key) {
            $value = $route->parameter($key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        foreach ($route->parameters() as $value) {
            if (is_string($value) && preg_match('/^[0-9a-f-]{8,}$/i', $value)) {
                return $value;
            }
        }

        return null;
    }

    private static function resolvePptKategori(string $action, string $bidang, string $combined): string
    {
        if (preg_match('#(adm-users|privileges|menuadm|backup|adm-)#', $combined)) {
            return 'admin';
        }

        if (preg_match('#(pendapatan|nota|payment|pembayaran|rates|tarif|packet|paket)#', $combined)) {
            return 'pembayaran';
        }

        if (preg_match('#(sampling|pengambil|sample-officer|formulir-pengambilan|mobile/sampling)#', $combined)) {
            return 'pengambil_sampel';
        }

        if (preg_match('#(validasi|verifikasi|verification|input-hasil|baca-hasil|baku-mutu|methods|units|penjab)#', $combined)) {
            return 'penjab_ruangan';
        }

        if ($action === 'print' || preg_match('#(print|lhu|hasil|label|kartu-medis)#', $combined)) {
            return 'pencetak';
        }

        if ($bidang === 'klinik' && preg_match('#(report|laporan|dashboard|analisis|home|beranda|activity-log)#', $combined)) {
            return 'kasie_klinis';
        }

        if ($bidang === 'kesmas' && preg_match('#(report|laporan|dashboard|home|beranda|activity-log)#', $combined)) {
            return 'kasie_kesmas';
        }

        if (preg_match('#(activity-log|home|beranda|panel|report-|monitoring|pendapatan|analisis)#', $combined)) {
            return 'ktu';
        }

        if ($action === 'validate' && preg_match('#(hasil|laboratorium|release|permohonan)#', $combined)) {
            return 'kepala_uptd';
        }

        if (preg_match('#(permohonan|pasien|customer|pendaftaran|register|registrasi|elits-pasien|elits-customers|permohonan-uji-klinik)#', $combined)) {
            return 'pendaftaran';
        }

        if ($bidang === 'admin') {
            return 'admin';
        }

        return 'umum';
    }

    private static function resolvePptFitur(string $action, string $pptKategori, string $combined): string
    {
        $map = [
            'pendaftaran' => [
                'create' => 'Mendaftarkan klien/pasien baru',
                'update' => 'Mengelola data klien/pasien',
                'delete' => 'Menghapus data klien/pasien',
                'view' => 'Melihat status pemeriksaan sampel',
                'print' => self::printFiturPendaftaran($combined),
                'export' => 'Mengelola laporan pendaftaran',
            ],
            'pembayaran' => [
                'confirm' => 'Mengkonfirmasi pembayaran',
                'create' => 'Menambah data tarif/paket layanan',
                'update' => 'Mengelola data tarif dan paket layanan',
                'delete' => 'Menghapus data tarif/paket layanan',
                'print' => 'Mencetak kwitansi/nota pembayaran',
                'export' => 'Mengelola laporan keuangan',
                'view' => 'Melihat data pembayaran/keuangan',
            ],
            'pengambil_sampel' => [
                'view' => 'Melihat daftar sampel',
                'update' => 'Memperbarui status sampel / input lokasi & waktu pengambilan',
                'create' => 'Menambahkan data pengambilan sampel',
                'print' => 'Menambahkan/mencetak form petugas pengambilan sampel',
                'export' => 'Mengelola laporan pengambilan sampel',
            ],
            'penjab_ruangan' => [
                'view' => 'Melihat sampel sesuai ruangan',
                'update' => 'Menginput hasil pemeriksaan',
                'validate' => 'Memvalidasi hasil pemeriksaan sesuai ruangan',
                'create' => 'Menambah data baku mutu/satuan/metode',
                'delete' => 'Menghapus data master pengujian',
                'export' => 'Mengelola laporan sesuai ruangan',
            ],
            'pencetak' => [
                'view' => 'Mengakses hasil pemeriksaan yang sudah divalidasi',
                'print' => 'Mencetak hasil sesuai format',
                'export' => 'Mengelola seluruh data laporan',
            ],
            'kasie_klinis' => [
                'view' => 'Melihat laporan hasil / dashboard bidang klinis',
                'export' => 'Melihat laporan pendapatan bidang klinis',
            ],
            'kasie_kesmas' => [
                'view' => 'Melihat laporan hasil / dashboard bidang kesmas',
                'export' => 'Melihat laporan pendapatan bidang kesmas',
            ],
            'ktu' => [
                'view' => 'Monitoring hasil pemeriksaan / dashboard laboratorium',
                'export' => 'Monitoring keuangan / laporan bulanan per bidang',
            ],
            'kepala_uptd' => [
                'view' => 'Melihat seluruh laporan aktivitas laboratorium',
                'validate' => 'Memvalidasi hasil pemeriksaan laboratorium',
                'export' => 'Dashboard rangkuman aktivitas laboratorium',
            ],
            'admin' => [
                'create' => 'Menambah akun pengguna / konfigurasi sistem',
                'update' => 'Manajemen akun pengguna / sistem informasi',
                'delete' => 'Menghapus akun/konfigurasi sistem',
                'view' => 'Manajemen sistem informasi',
            ],
        ];

        if (isset($map[$pptKategori][$action])) {
            return $map[$pptKategori][$action];
        }

        if ($action === 'login') {
            return 'Login ke sistem SimaLab';
        }
        if ($action === 'login_failed') {
            return 'Percobaan login gagal';
        }
        if ($action === 'logout') {
            return 'Logout dari sistem SimaLab';
        }

        return self::actionLabel($action) . ' — ' . self::pptKategoriLabel($pptKategori);
    }

    private static function printFiturPendaftaran(string $combined): string
    {
        if (preg_match('#(label|qrcode)#', $combined)) {
            return 'Mencetak label sampel';
        }
        if (preg_match('#(kartu-medis|rekam-medis)#', $combined)) {
            return 'Mencetak kartu rekam medis (klinis)';
        }
        if (preg_match('#(nota|invoice)#', $combined)) {
            return 'Mencetak invoice/nota';
        }

        return 'Mencetak dokumen pendaftaran';
    }

    private static function humanizeSlug(string $slug): string
    {
        $slug = str_replace(['elits-', '-', '_'], ' ', $slug);

        return ucwords(trim($slug));
    }
}
