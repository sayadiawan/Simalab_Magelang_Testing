<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\ActivityLog;

/**
 * Pencatat log aktivitas pengguna — tidak mengganggu alur request utama.
 */
class ActivityLogger
{
    /** @var array<int, string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        '_token',
        'captcha',
        'hp_field',
        'remember_token',
    ];

    /** @var array<int, string> */
    private const SKIP_PATH_PREFIXES = [
        '/assets/',
        '/storage/',
        '/image/qrcode',
        '/captcha',
        '/activity-log/data',
    ];

    /**
     * @param  array<string, mixed>  $attrs
     */
    public static function record(array $attrs): void
    {
        try {
            if (!Schema::hasTable('tb_activity_log')) {
                return;
            }

            ActivityLog::create([
                'user_id' => $attrs['user_id'] ?? null,
                'user_name' => isset($attrs['user_name']) ? (string) $attrs['user_name'] : null,
                'username' => isset($attrs['username']) ? (string) $attrs['username'] : null,
                'privilege_level' => isset($attrs['privilege_level']) ? (string) $attrs['privilege_level'] : null,
                'action' => (string) ($attrs['action'] ?? 'other'),
                'bidang' => (string) ($attrs['bidang'] ?? 'umum'),
                'module' => isset($attrs['module']) ? (string) $attrs['module'] : null,
                'description' => isset($attrs['description']) ? mb_substr((string) $attrs['description'], 0, 500) : null,
                'subject_type' => isset($attrs['subject_type']) ? (string) $attrs['subject_type'] : null,
                'subject_id' => isset($attrs['subject_id']) ? (string) $attrs['subject_id'] : null,
                'route_name' => isset($attrs['route_name']) ? (string) $attrs['route_name'] : null,
                'url' => isset($attrs['url']) ? mb_substr((string) $attrs['url'], 0, 500) : null,
                'http_method' => isset($attrs['http_method']) ? (string) $attrs['http_method'] : null,
                'ip_address' => isset($attrs['ip_address']) ? (string) $attrs['ip_address'] : null,
                'user_agent' => isset($attrs['user_agent']) ? mb_substr((string) $attrs['user_agent'], 0, 1000) : null,
                'request_data' => $attrs['request_data'] ?? null,
                'metadata' => $attrs['metadata'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Jangan gagalkan request karena gagal menulis log.
        }
    }

    public static function fromRequest(Request $request, string $action, ?string $description = null, array $extra = []): void
    {
        $actor = self::resolveActor($request);
        if ($actor === null && !in_array($action, ['login', 'login_failed'], true)) {
            return;
        }

        $path = '/' . ltrim($request->path(), '/');
        $bidang = self::resolveBidang($path, $request);

        if (!isset($extra['action']) && ($action === 'other' || $action === 'view')) {
            $action = ActivityActionCatalog::detectAction($request);
        }

        $subject = ActivityActionCatalog::resolveSubject($request);
        $subjectLabel = $extra['subject_label'] ?? $subject['subject_label'];
        $subjectType = $extra['subject_type'] ?? $subject['subject_type'];
        $subjectId = $extra['subject_id'] ?? $subject['subject_id'];

        $ppt = ActivityActionCatalog::resolvePptMapping($action, $bidang, $subjectLabel, $request);
        $metadata = array_merge($ppt, $extra['metadata'] ?? []);
        if ($subjectLabel) {
            $metadata['subject_label'] = $subjectLabel;
        }

        self::record(array_merge([
            'user_id' => $actor['user_id'] ?? null,
            'user_name' => $actor['user_name'] ?? null,
            'username' => $actor['username'] ?? ($extra['username'] ?? null),
            'privilege_level' => $actor['privilege_level'] ?? null,
            'action' => $action,
            'bidang' => $bidang,
            'module' => self::resolveModule($path, $request),
            'description' => $description ?? ActivityActionCatalog::buildDescription($request, $action, $subjectLabel, $subjectId),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'route_name' => optional($request->route())->getName(),
            'url' => $request->fullUrl(),
            'http_method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => self::sanitizeRequestData($request),
            'metadata' => $metadata,
        ], $extra));
    }

    public static function shouldLogRequest(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        foreach (self::SKIP_PATH_PREFIXES as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return false;
            }
        }

        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|map)$/i', $path)) {
            return false;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === 'logout') {
            return false;
        }
        if ($request->isMethod('POST') && strpos($routeName, 'login') !== false) {
            return false;
        }

        $method = strtoupper($request->method());

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return self::resolveActor($request) !== null || self::isAuthRoute($request);
        }

        if ($method !== 'GET') {
            return false;
        }

        if (self::isPrintOrExportPath($path)) {
            return self::resolveActor($request) !== null;
        }

        if (strpos($path, '/mobile/') === 0) {
            return self::resolveActor($request) !== null;
        }

        if (preg_match('#^/(elits-|adm-|home|report-|klinik-|kesmas-|biodata|panel|sm-master)#', $path)) {
            return self::resolveActor($request) !== null;
        }

        return false;
    }

    public static function detectAction(Request $request): string
    {
        return ActivityActionCatalog::detectAction($request);
    }

    /**
     * @return array<string, string|null>|null
     */
    public static function resolveActor(?Request $request = null): ?array
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name ?? null,
                    'username' => $user->username ?? null,
                    'privilege_level' => optional($user->getlevel)->level ?? null,
                ];
            }
        } catch (\Throwable $e) {
            // lanjut ke session mobile
        }

        if ($request === null) {
            return null;
        }

        $sessionMap = [
            ['mobile_testing_user_id', 'mobile_testing_user_name', null],
            ['mobile_sampling_user_id', 'mobile_sampling_user_name', null],
            ['sampling_user_id', 'sampling_user_name', 'sampling_user_username'],
            ['mobile_dokter_user_id', 'mobile_dokter_user_name', null],
            ['mobile_testing_klinik_user_id', 'mobile_testing_klinik_user_name', null],
            ['mobile_sampling_klinik_user_id', 'mobile_sampling_klinik_user_name', null],
        ];

        foreach ($sessionMap as [$idKey, $nameKey, $usernameKey]) {
            $userId = $request->session()->get($idKey);
            if (!$userId) {
                continue;
            }

            return [
                'user_id' => (string) $userId,
                'user_name' => $request->session()->get($nameKey),
                'username' => $usernameKey ? $request->session()->get($usernameKey) : null,
                'privilege_level' => 'mobile',
            ];
        }

        return null;
    }

    private static function isAuthRoute(Request $request): bool
    {
        $routeName = (string) optional($request->route())->getName();

        return strpos($routeName, 'login') !== false;
    }

    private static function isPrintOrExportPath(string $path): bool
    {
        $path = strtolower($path);

        return (bool) preg_match('#/(print|export|preview|download|cetak|nota|lhu|pdf|excel|xlsx)#', $path);
    }

    private static function resolveBidang(string $path, Request $request): string
    {
        $path = strtolower($path);
        $routeName = strtolower((string) optional($request->route())->getName());

        if (strpos($path, '/mobile/') === 0) {
            if (strpos($path, 'klinik') !== false || strpos($routeName, 'klinik') !== false) {
                return 'klinik';
            }
            if (strpos($path, 'dokter') !== false) {
                return 'klinik';
            }

            return 'kesmas';
        }

        if (preg_match('#(klinik|pasien|dokter|rekam-medis|permohonan-uji-klinik|analisis-hasil-wilayah)#', $path)) {
            return 'klinik';
        }

        if (preg_match('#(kesmas|sample|permohonan-uji[^/]*$|release|lhu|kimia|mikro)#', $path)) {
            return 'kesmas';
        }

        if (preg_match('#^(adm-|menuadm|privileges|biodata|adm-users)#', ltrim($path, '/'))) {
            return 'admin';
        }

        return 'umum';
    }

    private static function resolveModule(string $path, Request $request): ?string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        if (empty($segments)) {
            return 'beranda';
        }

        $first = $segments[0];
        if ($first === 'mobile' && isset($segments[1])) {
            return 'mobile/' . $segments[1];
        }

        if (strpos($first, 'elits-') === 0) {
            return $first;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName !== '') {
            return explode('.', $routeName)[0];
        }

        return $first;
    }

    public static function actionLabel(string $action): string
    {
        return ActivityActionCatalog::actionLabel($action);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function sanitizeRequestData(Request $request): ?array
    {
        if (!in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        $data = $request->except(self::SENSITIVE_KEYS);
        if (empty($data)) {
            return null;
        }

        $json = json_encode($data);
        if ($json === false) {
            return null;
        }

        if (strlen($json) > 8000) {
            return ['_truncated' => true, 'keys' => array_keys($data)];
        }

        return $data;
    }
}
