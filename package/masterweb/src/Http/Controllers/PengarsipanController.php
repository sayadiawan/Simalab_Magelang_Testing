<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\ActivityLog;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\VerificationActivitySample;

class PengarsipanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (!getAction('read')) {
            abort(403, 'Anda tidak memiliki akses ke menu Pengarsipan Hasil.');
        }

        $q = trim((string) $request->get('q', ''));
        $stats = $this->buildStats();
        $recentValidated = $this->recentValidatedKlinik(8);
        $recentValidatedKesmas = $this->recentValidatedKesmas(8);
        $recentPrints = $this->recentPrintLogs(10);
        $pendingNomerLab = $this->pendingKesmasNomerLab(12);
        $searchResults = $q !== '' ? $this->searchHasil($q) : collect();

        return view('masterweb::module.admin.pengarsipan.index', [
            'stats' => $stats,
            'recentValidated' => $recentValidated,
            'recentValidatedKesmas' => $recentValidatedKesmas,
            'recentPrints' => $recentPrints,
            'pendingNomerLab' => $pendingNomerLab,
            'searchResults' => $searchResults,
            'q' => $q,
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function buildStats(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $klinikSiapCetak = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereNotNull('is_klinik')
            ->where('stop_date', '>=', now()->subDays(30))
            ->distinct()
            ->count('is_klinik');

        $kesmasSiapCetak = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereNotNull('id_sample')
            ->whereNull('is_klinik')
            ->where('stop_date', '>=', now()->subDays(30))
            ->distinct()
            ->count('id_sample');

        $printToday = 0;
        $printBulanIni = 0;

        if (Schema::hasTable('tb_activity_log')) {
            $printToday = ActivityLog::query()
                ->whereIn('action', ['print', 'export'])
                ->whereDate('created_at', $today)
                ->count();

            $printBulanIni = ActivityLog::query()
                ->whereIn('action', ['print', 'export'])
                ->where('created_at', '>=', $monthStart)
                ->count();
        }

        return [
            'klinik_siap_cetak' => $klinikSiapCetak,
            'kesmas_siap_cetak' => $kesmasSiapCetak,
            'cetak_hari_ini' => $printToday,
            'cetak_bulan_ini' => $printBulanIni,
        ];
    }

    /**
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function recentValidatedKlinik($limit = 8)
    {
        $rows = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereNotNull('is_klinik')
            ->orderByDesc('stop_date')
            ->limit($limit)
            ->get(['is_klinik', 'stop_date', 'nama_petugas']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $ids = $rows->pluck('is_klinik')->filter()->unique()->values();
        $permohonan = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien,no_rekammedis_pasien'])
            ->whereIn('id_permohonan_uji_klinik', $ids)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id_permohonan_uji_klinik');

        return $rows->map(function ($row) use ($permohonan) {
            $p = $permohonan->get($row->is_klinik);
            $id = (string) $row->is_klinik;

            return [
                'id' => $id,
                'pasien' => optional(optional($p)->pasien)->nama_pasien ?: '-',
                'noregister' => optional($p)->noregister_permohonan_uji_klinik
                    ?: optional(optional($p)->pasien)->no_rekammedis_pasien
                    ?: '-',
                'nomor_lab' => $p ? ($p->getLabNumber() ?: ($p->getNomorLab() ?: '-')) : '-',
                'validated_at' => $row->stop_date
                    ? Carbon::parse($row->stop_date)->format('d/m/Y H:i')
                    : '-',
                'validator' => $row->nama_petugas ?: '-',
                'print_url' => url('print-permohonan-uji-klinik-hasil-2/' . $id),
            ];
        });
    }

    /**
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function recentValidatedKesmas($limit = 8)
    {
        $rows = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereNotNull('id_sample')
            ->whereNull('is_klinik')
            ->orderByDesc('stop_date')
            ->limit($limit)
            ->get(['id_sample', 'stop_date', 'nama_petugas']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $ids = $rows->pluck('id_sample')->filter()->unique()->values();
        $samples = Sample::query()
            ->whereIn('id_samples', $ids)
            ->whereNull('deleted_at')
            ->get(['id_samples', 'codesample_samples', 'name_pelanggan', 'permohonan_uji_id'])
            ->keyBy('id_samples');

        $labIds = $this->resolveKesmasLabIdsForSamples($ids);

        return $rows->map(function ($row) use ($samples, $labIds) {
            $sampleId = (string) $row->id_sample;
            $sample = $samples->get($row->id_sample);
            $labId = $labIds[$sampleId] ?? null;

            return [
                'id' => $sampleId,
                'pelanggan' => optional($sample)->name_pelanggan ?: '-',
                'kode' => optional($sample)->codesample_samples ?: '-',
                'validated_at' => $row->stop_date
                    ? Carbon::parse($row->stop_date)->format('d/m/Y H:i')
                    : '-',
                'validator' => $row->nama_petugas ?: '-',
                'print_url' => $labId
                    ? url('elits-release/printLHU/' . $sampleId . '/' . $labId)
                    : null,
                'nota_url' => optional($sample)->permohonan_uji_id
                    ? url('elits-release/nota/' . $sample->permohonan_uji_id)
                    : null,
            ];
        });
    }

    /**
     * @param \Illuminate\Support\Collection|array $sampleIds
     * @return array<string, string>
     */
    private function resolveKesmasLabIdsForSamples($sampleIds)
    {
        $sampleIds = collect($sampleIds)->filter()->unique()->values();
        if ($sampleIds->isEmpty()) {
            return [];
        }

        $map = [];

        if (Schema::hasTable('tb_pengesahan_hasil')) {
            DB::table('tb_pengesahan_hasil')
                ->whereIn('sample_id', $sampleIds)
                ->whereNull('deleted_at')
                ->whereNotNull('laboratorium_id')
                ->orderByDesc('updated_at')
                ->get(['sample_id', 'laboratorium_id'])
                ->each(function ($row) use (&$map) {
                    $sid = (string) $row->sample_id;
                    if (!isset($map[$sid])) {
                        $map[$sid] = (string) $row->laboratorium_id;
                    }
                });
        }

        $missing = $sampleIds->filter(function ($id) use ($map) {
            return !isset($map[(string) $id]);
        });

        if ($missing->isNotEmpty() && Schema::hasTable('tb_sample_method')) {
            DB::table('tb_sample_method')
                ->whereIn('sample_id', $missing->all())
                ->whereNull('deleted_at')
                ->whereNotNull('laboratorium_id')
                ->orderByDesc('updated_at')
                ->get(['sample_id', 'laboratorium_id'])
                ->each(function ($row) use (&$map) {
                    $sid = (string) $row->sample_id;
                    if (!isset($map[$sid])) {
                        $map[$sid] = (string) $row->laboratorium_id;
                    }
                });
        }

        return $map;
    }

    /**
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function recentPrintLogs($limit = 10)
    {
        if (!Schema::hasTable('tb_activity_log')) {
            return collect();
        }

        return ActivityLog::query()
            ->whereIn('action', ['print', 'export'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['created_at', 'user_name', 'username', 'bidang', 'description', 'metadata'])
            ->map(function (ActivityLog $log) {
                $meta = is_array($log->metadata) ? $log->metadata : [];

                return [
                    'waktu' => $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-',
                    'pengguna' => $log->user_name ?: ($log->username ?: '-'),
                    'bidang' => strtoupper((string) $log->bidang),
                    'deskripsi' => $log->description ?: '-',
                    'fitur' => $meta['ppt_fitur'] ?? '-',
                ];
            });
    }

    /**
     * @param string $q
     * @return \Illuminate\Support\Collection
     */
    private function searchHasil($q)
    {
        $like = '%' . $q . '%';

        $klinik = PermohonanUjiKlinik2::query()
            ->with(['pasien:id_pasien,nama_pasien,no_rekammedis_pasien'])
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
            ->leftJoin('ms_pasien as ps', 'ps.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
            ->where(function ($sub) use ($like) {
                $sub->where('tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik', 'like', $like)
                    ->orWhere('tb_permohonan_uji_klinik_2.nomor_lab_manual', 'like', $like)
                    ->orWhere('tb_permohonan_uji_klinik_2.nomor_spesimen_manual', 'like', $like)
                    ->orWhere('ps.nama_pasien', 'like', $like)
                    ->orWhere('ps.no_rekammedis_pasien', 'like', $like)
                    ->orWhere('ps.nik_pasien', 'like', $like);
            })
            ->select('tb_permohonan_uji_klinik_2.*')
            ->orderByDesc('tb_permohonan_uji_klinik_2.created_at')
            ->limit(15)
            ->get();

        $validatedIds = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereIn('is_klinik', $klinik->pluck('id_permohonan_uji_klinik'))
            ->pluck('is_klinik')
            ->flip();

        $klinikResults = $klinik->map(function (PermohonanUjiKlinik2 $p) use ($validatedIds) {
            $id = (string) $p->id_permohonan_uji_klinik;
            $isValidated = $validatedIds->has($id);

            return [
                'bidang' => 'KLINIK',
                'label' => optional($p->pasien)->nama_pasien ?: 'Permohonan Klinik',
                'sub' => 'No. Lab: ' . ($p->getLabNumber() ?: ($p->getNomorLab() ?: '-')) . ' · Reg: ' . ($p->noregister_permohonan_uji_klinik ?: '-'),
                'status' => $isValidated ? 'Sudah divalidasi' : 'Belum divalidasi',
                'status_ok' => $isValidated,
                'print_url' => $isValidated ? url('print-permohonan-uji-klinik-hasil-2/' . $id) : null,
                'nota_url' => url('print-permohonan-uji-klinik-nota-2/' . $id),
            ];
        });

        $kesmas = Sample::query()
            ->whereNull('deleted_at')
            ->where(function ($sub) use ($like) {
                $sub->where('codesample_samples', 'like', $like)
                    ->orWhere('name_pelanggan', 'like', $like);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $validatedKesmasIds = VerificationActivitySample::query()
            ->where('id_verification_activity', VerificationActivitySample::ACTIVITY_VALIDASI)
            ->where('is_done', 1)
            ->whereNull('is_klinik')
            ->whereIn('id_sample', $kesmas->pluck('id_samples'))
            ->pluck('id_sample')
            ->flip();

        $kesmasLabIds = $this->resolveKesmasLabIdsForSamples($kesmas->pluck('id_samples'));

        $kesmasResults = $kesmas->map(function (Sample $s) use ($validatedKesmasIds, $kesmasLabIds) {
            $id = (string) $s->id_samples;
            $isValidated = $validatedKesmasIds->has($id);
            $labId = $kesmasLabIds[$id] ?? null;

            return [
                'bidang' => 'KESMAS',
                'label' => $s->name_pelanggan ?: 'Sampel Kesmas',
                'sub' => 'Kode: ' . ($s->codesample_samples ?: '-'),
                'status' => $isValidated ? 'Sudah divalidasi' : 'Belum divalidasi',
                'status_ok' => $isValidated,
                'print_url' => ($isValidated && $labId)
                    ? url('elits-release/printLHU/' . $id . '/' . $labId)
                    : null,
                'nota_url' => $s->permohonan_uji_id
                    ? url('elits-release/nota/' . $s->permohonan_uji_id)
                    : null,
                'nomer_lab_url' => $s->permohonan_uji_id
                    ? route('elits-permohonan-uji.nomer-lab', [$s->permohonan_uji_id])
                    : null,
            ];
        });

        return $klinikResults->concat($kesmasResults)->values();
    }

    /**
     * Daftar permohonan Kesmas yang masih kurang nomor lab (untuk input pengarsipan).
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function pendingKesmasNomerLab($limit = 12)
    {
        if (!Schema::hasTable('tb_samples') || !Schema::hasTable('tb_sample_method')) {
            return collect();
        }

        $recentIds = Sample::query()
            ->whereNull('deleted_at')
            ->whereNotNull('permohonan_uji_id')
            ->orderByDesc('created_at')
            ->limit(300)
            ->pluck('permohonan_uji_id')
            ->unique()
            ->filter()
            ->values();

        if ($recentIds->isEmpty()) {
            return collect();
        }

        $expectedComboRows = DB::table('tb_samples as s')
            ->join('tb_sample_method as sm', function ($j) {
                $j->on('sm.sample_id', '=', 's.id_samples')->whereNull('sm.deleted_at');
            })
            ->whereIn('s.permohonan_uji_id', $recentIds->all())
            ->whereNull('s.deleted_at')
            ->whereNotNull('s.typesample_samples')
            ->whereNotNull('sm.laboratorium_id')
            ->select(
                's.permohonan_uji_id',
                's.typesample_samples as sample_type_id',
                'sm.laboratorium_id'
            )
            ->distinct()
            ->get();

        $assignedKeys = [];
        if (Schema::hasTable('tb_nomer_lab_kesmas')) {
            $assignedQuery = DB::table('tb_nomer_lab_kesmas')
                ->whereIn('permohonan_uji_id', $recentIds->all())
                ->whereNotNull('nomer_lab')
                ->where('nomer_lab', '>', 0);

            if (Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
                $assignedQuery->whereNull('deleted_at');
            }

            foreach ($assignedQuery->get(['permohonan_uji_id', 'sample_type_id', 'laboratorium_id']) as $ar) {
                $assignedKeys[$ar->permohonan_uji_id][$ar->sample_type_id . '|' . $ar->laboratorium_id] = true;
            }
        }

        $expectedByPermohonan = [];
        foreach ($expectedComboRows as $er) {
            $expectedByPermohonan[$er->permohonan_uji_id][] = $er->sample_type_id . '|' . $er->laboratorium_id;
        }

        $pendingIds = [];
        foreach ($recentIds as $pid) {
            $keys = array_values(array_unique($expectedByPermohonan[$pid] ?? []));
            if (count($keys) === 0) {
                continue;
            }
            $assigned = 0;
            foreach ($keys as $k) {
                if (!empty($assignedKeys[$pid][$k])) {
                    $assigned++;
                }
            }
            if ($assigned < count($keys)) {
                $pendingIds[] = [
                    'id' => $pid,
                    'expected' => count($keys),
                    'assigned' => $assigned,
                    'missing' => count($keys) - $assigned,
                ];
            }
            if (count($pendingIds) >= $limit) {
                break;
            }
        }

        if (empty($pendingIds)) {
            return collect();
        }

        $permohonan = PermohonanUji::query()
            ->leftJoin('ms_customer', function ($join) {
                $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
                    ->whereNull('ms_customer.deleted_at');
            })
            ->whereIn('tb_permohonan_uji.id_permohonan_uji', array_column($pendingIds, 'id'))
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->select(
                'tb_permohonan_uji.id_permohonan_uji',
                'tb_permohonan_uji.code_permohonan_uji',
                'ms_customer.name_customer'
            )
            ->get()
            ->keyBy('id_permohonan_uji');

        return collect($pendingIds)->map(function ($row) use ($permohonan) {
            $p = $permohonan->get($row['id']);

            return [
                'id' => $row['id'],
                'pelanggan' => optional($p)->name_customer ?: '-',
                'kode' => optional($p)->code_permohonan_uji ?: '-',
                'status' => $row['assigned'] === 0
                    ? 'Belum diisi'
                    : ('Kurang ' . $row['missing'] . '/' . $row['expected']),
                'nomer_lab_url' => route('elits-permohonan-uji.nomer-lab', [$row['id']]),
            ];
        })->values();
    }
}
