<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Smt\Masterweb\Helpers\ActivityActionCatalog;
use Smt\Masterweb\Helpers\ActivityLogAccess;
use Smt\Masterweb\Models\ActivityLog;
use Smt\Masterweb\Models\User;

class ActivityLogController extends Controller
{
    private const ALLOWED_PER_PAGE = [10, 25, 50, 100];

    private const DEFAULT_PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Log Aktivitas Sistem.');
        }

        $dateFrom = $request->get('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $bidang = $request->get('bidang', '');
        $action = $request->get('action', '');
        $pptKategori = $request->get('ppt_kategori', '');
        $userId = $request->get('user_id', '');
        $q = trim((string) $request->get('q', ''));
        $perPage = $this->resolvePerPage($request);
        $authUser = auth()->user();
        $scope = ActivityLogAccess::resolveScope($authUser);
        $allowedActions = $scope['allowed_actions'] ?? null;

        if (is_array($allowedActions) && $action !== '' && !in_array($action, $allowedActions, true)) {
            $action = '';
        }

        if (!$scope['can_filter_user']) {
            $userId = (string) $authUser->id;
        }

        if (!$scope['can_filter_bidang'] && !empty($scope['bidang'])) {
            $bidang = $scope['bidang'];
        }

        $users = User::query()
            ->whereNull('deleted_at')
            ->when($scope['mode'] === ActivityLogAccess::MODE_PRINT_EXPORT, function ($q) {
                $ids = ActivityLog::query()
                    ->whereIn('action', ActivityLogAccess::PENGARSIP_ACTIONS)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id');
                $q->whereIn('id', $ids);
            })
            ->when($scope['mode'] === ActivityLogAccess::MODE_BIDANG, function ($q) use ($scope) {
                // Daftar pengguna yang pernah tercatat di bidang ini
                $ids = ActivityLog::query()
                    ->where('bidang', $scope['bidang'])
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id');
                $q->whereIn('id', $ids);
            })
            ->when($scope['mode'] === ActivityLogAccess::MODE_OWN, function ($q) use ($authUser) {
                $q->where('id', $authUser->id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        $baseQuery = $this->buildQuery($dateFrom, $dateTo, $bidang, $action, $pptKategori, $userId, $q);
        ActivityLogAccess::applyScope($baseQuery, $scope);

        $actions = (clone $baseQuery)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $bidangs = (clone $baseQuery)
            ->select('bidang')
            ->distinct()
            ->orderBy('bidang')
            ->pluck('bidang');

        $logs = (clone $baseQuery)
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('masterweb::module.admin.activity-log.list', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'bidangs' => $bidangs,
            'scope' => $scope,
            'actionLabels' => $this->resolveActionLabels($scope),
            'pptKategoris' => ActivityActionCatalog::PPT_KATEGORI,
            'allowedPerPage' => self::ALLOWED_PER_PAGE,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'bidang' => $bidang,
                'action' => $action,
                'ppt_kategori' => $pptKategori,
                'user_id' => $userId,
                'q' => $q,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show($id)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Log Aktivitas Sistem.');
        }

        $log = ActivityLog::query()->findOrFail($id);

        if (!ActivityLogAccess::canViewLog($log)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat log ini.');
        }

        return view('masterweb::module.admin.activity-log.show', [
            'log' => $log,
            'scope' => ActivityLogAccess::resolveScope(),
            'actionLabels' => ActivityActionCatalog::ACTION_LABELS,
            'pptKategoris' => ActivityActionCatalog::PPT_KATEGORI,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery($dateFrom, $dateTo, $bidang, $action, $pptKategori, $userId, $q)
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        try {
            $start = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        } catch (\Throwable $e) {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        if ($bidang !== '') {
            $query->where('bidang', $bidang);
        }

        if ($action !== '') {
            $query->where('action', $action);
        }

        if ($pptKategori !== '') {
            $query->where('metadata->ppt_kategori', $pptKategori);
        }

        if ($userId !== '') {
            $query->where('user_id', $userId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', '%' . $q . '%')
                    ->orWhere('user_name', 'like', '%' . $q . '%')
                    ->orWhere('username', 'like', '%' . $q . '%')
                    ->orWhere('module', 'like', '%' . $q . '%')
                    ->orWhere('subject_type', 'like', '%' . $q . '%')
                    ->orWhere('subject_id', 'like', '%' . $q . '%')
                    ->orWhere('url', 'like', '%' . $q . '%')
                    ->orWhere('route_name', 'like', '%' . $q . '%')
                    ->orWhere('metadata', 'like', '%' . $q . '%');
            });
        }

        return $query;
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', self::DEFAULT_PER_PAGE);

        return in_array($perPage, self::ALLOWED_PER_PAGE, true)
            ? $perPage
            : self::DEFAULT_PER_PAGE;
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, string>
     */
    private function resolveActionLabels(array $scope): array
    {
        $labels = ActivityActionCatalog::ACTION_LABELS;
        $allowed = $scope['allowed_actions'] ?? null;

        if (!is_array($allowed) || empty($allowed)) {
            return $labels;
        }

        return array_intersect_key($labels, array_flip($allowed));
    }
}
