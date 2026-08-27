<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Smt\Masterweb\Rules\Captcha;
use \Smt\Masterweb\Models\Sample;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Helpers\SampleCollectorAccess;




class AdmHomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index(Request $request)
  {
    $auth = Auth()->user();
    $userLevel = $auth->getlevel->level ?? null;

    if ($userLevel === 'ARSP') {
      return redirect()->route('pengarsipan.index');
    }

    $isDKTR = ($userLevel == 'DKTR');
    $isBendahara = ($userLevel == 'BNDR');
    
    $isSolK = SampleCollectorAccess::isKlinik($userLevel);
    $isSolM = SampleCollectorAccess::isKesmas($userLevel);
    
    // Cek laboratorium user (jika ada)
    $kodeLaboratorium = $auth->laboratorium->kode_laboratorium ?? null;
    $namaLaboratorium = $auth->laboratorium->nama_laboratorium ?? null;
    $isKLI = ($kodeLaboratorium == 'KLI') || ($userLevel == 'KSKL') || $isSolK;
    $isKIM = ($kodeLaboratorium == 'KIM');
    $isMBI = ($kodeLaboratorium == 'MBI'); // Mikrobiologi
    // Lab Kesmas (kimia + mikro) — grafik/statistik di-scope ke lab user
    $isKesmasLabUser = in_array($kodeLaboratorium, ['KIM', 'KMA', 'FKA', 'MBI'], true);
    $kesmasLabCodes = $isKesmasLabUser ? [$kodeLaboratorium] : [];
    // Fallback: lab dari relasi petugas jika user belum punya laboratory_users
    if ($kesmasLabCodes === [] && !$isKLI && !empty($auth->id_petugas)) {
      $petugasLab = \Smt\Masterweb\Models\Petugas::find($auth->id_petugas);
      $petugasLabIds = $petugasLab
        ? (is_array($petugasLab->lab_id) ? $petugasLab->lab_id : (json_decode($petugasLab->lab_id ?? '[]', true) ?: []))
        : [];
      if (!empty($petugasLabIds)) {
        $kesmasLabCodes = Laboratorium::query()
          ->whereIn('id_laboratorium', $petugasLabIds)
          ->whereIn('kode_laboratorium', ['KIM', 'KMA', 'FKA', 'MBI'])
          ->whereNull('deleted_at')
          ->pluck('kode_laboratorium')
          ->unique()
          ->values()
          ->all();
        $isKesmasLabUser = $kesmasLabCodes !== [];
        if ($isKesmasLabUser && !$kodeLaboratorium) {
          $kodeLaboratorium = $kesmasLabCodes[0];
          $namaLaboratorium = Laboratorium::where('kode_laboratorium', $kodeLaboratorium)->value('nama_laboratorium');
        }
      }
    }
    $chartLabScopeLabel = $namaLaboratorium
      ?: ($kodeLaboratorium ? (string) $kodeLaboratorium : null);

    // Rentang tanggal grafik (default: 12 bulan terakhir)
    $chartFromInput = $request->query('chart_from');
    $chartToInput = $request->query('chart_to');
    try {
      $chartStart = $chartFromInput
        ? Carbon::createFromFormat('Y-m-d', $chartFromInput)->startOfDay()
        : now()->subMonths(11)->startOfMonth();
    } catch (\Exception $e) {
      $chartStart = now()->subMonths(11)->startOfMonth();
    }
    try {
      $chartEnd = $chartToInput
        ? Carbon::createFromFormat('Y-m-d', $chartToInput)->endOfDay()
        : now()->endOfDay();
    } catch (\Exception $e) {
      $chartEnd = now()->endOfDay();
    }
    if ($chartStart->gt($chartEnd)) {
      [$chartStart, $chartEnd] = [$chartEnd->copy()->startOfDay(), $chartStart->copy()->endOfDay()];
    }
    // Batasi maksimal 36 bulan agar query tetap ringan
    if ($chartStart->diffInMonths($chartEnd) > 36) {
      $chartStart = $chartEnd->copy()->subMonths(36)->startOfDay();
    }
    $chartFrom = $chartStart->format('Y-m-d');
    $chartTo = $chartEnd->format('Y-m-d');
    
    // Jika user level DKTR atau memiliki laboratorium KLI, hanya ambil data klinik
    if ($isDKTR || $isKLI) {
      $total_permohonan_uji = 0;
      $total_sample = 0;
      $total_berjalan = 0;
      $total_selesai = 0;
      
      // Untuk DKTR, hanya hitung permohonan dengan doctor_type = 'lab'
      // Untuk KLI, hitung semua permohonan uji klinik
      if ($isDKTR) {
        $dataPermohonanUjiKlinik = PermohonanUjiKlinik2::query()
          ->selectRaw('
            (SELECT COUNT(*) FROM tb_permohonan_uji_klinik_2 WHERE deleted_at IS NULL AND doctor_type = "lab") AS total_permohonan,
            COUNT(DISTINCT pasien_permohonan_uji_klinik) AS total_pasien
        ')
          ->whereNull('deleted_at')
          ->where('doctor_type', 'lab')
          ->first();
      } else {
        // Untuk KLI, hitung semua permohonan uji klinik
        $dataPermohonanUjiKlinik = PermohonanUjiKlinik2::query()
          ->selectRaw('
            (SELECT COUNT(*) FROM tb_permohonan_uji_klinik_2 WHERE deleted_at IS NULL) AS total_permohonan,
            COUNT(DISTINCT pasien_permohonan_uji_klinik) AS total_pasien
        ')
          ->whereNull('deleted_at')
          ->first();
      }

      // Data khusus untuk DKTR - Total Sampel dari permohonan uji klinik
      $dataSampelKlinik = PermohonanUjiKlinik2::query()
        ->selectRaw('COUNT(*) AS total_sampel_klinik')
        ->whereNull('deleted_at')
        ->first();

      // Data khusus untuk DKTR - Analisa Berjalan dan Selesai dari permohonan uji klinik
      $dataAnalisaKlinik = PermohonanUjiKlinik2::query()
        ->selectRaw('
          SUM(CASE WHEN status_permohonan_uji_klinik = "pending" OR status_permohonan_uji_klinik = "processing" THEN 1 ELSE 0 END) AS analisa_berjalan,
          SUM(CASE WHEN status_permohonan_uji_klinik = "completed" OR status_permohonan_uji_klinik = "done" THEN 1 ELSE 0 END) AS analisa_selesai
        ')
        ->whereNull('deleted_at')
        ->first();

      // Data khusus untuk DKTR - Status Diagnosis Dokter
      $dataDiagnosisDokter = PermohonanUjiKlinik2::query()
        ->selectRaw('
          SUM(CASE WHEN doctor_type = "lab" AND (done_register = 0 OR done_register IS NULL) THEN 1 ELSE 0 END) AS menunggu_diagnosis,
          SUM(CASE WHEN doctor_type = "lab" AND done_register = 1 THEN 1 ELSE 0 END) AS sudah_terdiagnosis,
          SUM(CASE WHEN doctor_type != "lab" OR doctor_type IS NULL THEN 1 ELSE 0 END) AS dari_rujukan_dokter
        ')
        ->whereNull('deleted_at')
        ->first();

      $total_permohonan_uji_klinik = $dataPermohonanUjiKlinik->total_permohonan;
      $pasien_klinik = $dataPermohonanUjiKlinik->total_pasien;
      $total_sampel_klinik = $dataSampelKlinik->total_sampel_klinik;
      $analisa_berjalan_klinik = $dataAnalisaKlinik->analisa_berjalan ?? 0;
      $analisa_selesai_klinik = $dataAnalisaKlinik->analisa_selesai ?? 0;
      $menunggu_diagnosis = $dataDiagnosisDokter->menunggu_diagnosis ?? 0;
      $sudah_terdiagnosis = $dataDiagnosisDokter->sudah_terdiagnosis ?? 0;
      $dari_rujukan_dokter = $dataDiagnosisDokter->dari_rujukan_dokter ?? 0;

      // Statistik klinik per bulan untuk DKTR/KLI: jumlah permohonan per bulan
      $pendapatanKlinik = PermohonanUjiKlinik2::query()
        ->select(
          DB::raw('COUNT(*) as total_permohonan'),
          DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
        )
        ->whereBetween('created_at', [$chartStart, $chartEnd])
        ->whereNull('deleted_at')
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('bulan')
        ->get();

      $bulans = [];
      $pendapatans = []; // sekarang berisi jumlah permohonan per bulan

      foreach ($pendapatanKlinik as $pdk) {
        $bulans[] = $pdk->bulan;
        $pendapatans[] = $pdk->total_permohonan;
      }

      // Tidak ada data sample untuk DKTR
      $countSample = [];
      $sampleTypes = [];
    } else {
      // Logic untuk user selain DKTR/KLI (Kesmas)
      if ($isKesmasLabUser && $kesmasLabCodes !== []) {
        // Dashboard khusus lab Kesmas: hanya data berdasarkan lab terkait (tb_sample_method + ms_laboratorium)
        // Gunakan COUNT DISTINCT untuk memastikan total_berjalan / total_selesai tidak melebihi total_sample
        $dataTotal = Sample::query()
          ->join('tb_sample_method', function ($join) {
            $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
              ->whereNull('tb_sample_method.deleted_at');
          })
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at');
          })
          ->whereIn('ms_laboratorium.kode_laboratorium', $kesmasLabCodes)
          ->whereNull('tb_samples.deleted_at')
          ->selectRaw('
              COUNT(DISTINCT tb_samples.permohonan_uji_id) AS total_permohonan_uji,
              COUNT(DISTINCT tb_samples.id_samples) AS total_sample,
              COUNT(DISTINCT CASE WHEN tb_samples.date_analitik_sample IS NULL THEN tb_samples.id_samples ELSE NULL END) AS total_berjalan,
              COUNT(DISTINCT CASE WHEN tb_samples.date_analitik_sample IS NOT NULL THEN tb_samples.id_samples ELSE NULL END) AS total_selesai
          ')
          ->first();
      } else {
        // Default: semua Kesmas (existing logic)
        $dataTotal = Sample::query()->selectRaw('
            (SELECT COUNT(*) FROM tb_permohonan_uji WHERE deleted_at IS NULL) AS total_permohonan_uji,
            COUNT(*) AS total_sample,
            SUM(CASE WHEN date_analitik_sample IS NULL AND deleted_at IS NULL THEN 1 ELSE 0 END) AS total_berjalan,
            SUM(CASE WHEN date_analitik_sample IS NOT NULL AND deleted_at IS NULL THEN 1 ELSE 0 END) AS total_selesai
        ')
          ->whereNull('deleted_at')
          ->first();
      }

      $total_permohonan_uji = $dataTotal->total_permohonan_uji ?? 0;
      $total_sample = $dataTotal->total_sample ?? 0;
      $total_berjalan = $dataTotal->total_berjalan ?? 0;
      $total_selesai = $dataTotal->total_selesai ?? 0;

      $dataPermohonanUjiKlinik = PermohonanUjiKlinik2::query()
        ->selectRaw('
          (SELECT COUNT(*) FROM tb_permohonan_uji_klinik_2 WHERE deleted_at IS NULL) AS total_permohonan,
          COUNT(DISTINCT pasien_permohonan_uji_klinik) AS total_pasien
      ')
        ->whereNull('deleted_at')
        ->first();

      // Data khusus untuk SOLAB - Total Sampel dari permohonan uji klinik
      $dataSampelKlinik = PermohonanUjiKlinik2::query()
        ->selectRaw('COUNT(*) AS total_sampel_klinik')
        ->whereNull('deleted_at')
        ->first();

      // Data khusus untuk SOLAB - Analisa Berjalan dan Selesai dari permohonan uji klinik
      $dataAnalisaKlinik = PermohonanUjiKlinik2::query()
        ->selectRaw('
          SUM(CASE WHEN status_permohonan_uji_klinik = "pending" OR status_permohonan_uji_klinik = "processing" THEN 1 ELSE 0 END) AS analisa_berjalan,
          SUM(CASE WHEN status_permohonan_uji_klinik = "completed" OR status_permohonan_uji_klinik = "done" THEN 1 ELSE 0 END) AS analisa_selesai
        ')
        ->whereNull('deleted_at')
        ->first();

      $total_permohonan_uji_klinik = $dataPermohonanUjiKlinik->total_permohonan;
      $pasien_klinik = $dataPermohonanUjiKlinik->total_pasien;
      $total_sampel_klinik = $dataSampelKlinik->total_sampel_klinik;
      $analisa_berjalan_klinik = $dataAnalisaKlinik->analisa_berjalan ?? 0;
      $analisa_selesai_klinik = $dataAnalisaKlinik->analisa_selesai ?? 0;

      // Statistik Kesmas per bulan — scope ke lab user jika ada
      if ($isKesmasLabUser && $kesmasLabCodes !== []) {
        $pendapatan = Sample::query()
          ->join('tb_sample_method', function ($join) {
            $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
              ->whereNull('tb_sample_method.deleted_at');
          })
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at');
          })
          ->whereIn('ms_laboratorium.kode_laboratorium', $kesmasLabCodes)
          ->whereNull('tb_samples.deleted_at')
          ->whereBetween('tb_samples.created_at', [$chartStart, $chartEnd])
          ->select(
            DB::raw('COUNT(DISTINCT tb_samples.permohonan_uji_id) as total_permohonan'),
            DB::raw('DATE_FORMAT(tb_samples.created_at, "%Y-%m") as bulan')
          )
          ->groupBy(DB::raw('DATE_FORMAT(tb_samples.created_at, "%Y-%m")'))
          ->orderBy('bulan')
          ->get();
      } else {
        $pendapatan = PermohonanUji::query()
          ->select(
            DB::raw('COUNT(*) as total_permohonan'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
          )
          ->whereBetween('created_at', [$chartStart, $chartEnd])
          ->where('status_pembayaran', 1)
          ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
          ->orderBy('bulan')
          ->get();
      }

      $bulans = [];
      $pendapatans = [];

      foreach ($pendapatan as $pd) {
        $bulans[] = $pd->bulan;
        $pendapatans[] = (int) $pd->total_permohonan;
      }

      // Data chart jenis sampel
      if ($isKesmasLabUser && $kesmasLabCodes !== []) {
        // Hanya sampel yang dianalisa di lab user
        $samples = Sample::query()
          ->join('tb_sample_method', function ($join) {
            $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
              ->whereNull('tb_sample_method.deleted_at');
          })
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at');
          })
          ->join('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'typesample_samples')
          ->whereIn('ms_laboratorium.kode_laboratorium', $kesmasLabCodes)
          ->whereNull('tb_samples.deleted_at')
          ->whereBetween('tb_samples.created_at', [$chartStart, $chartEnd])
          ->select(DB::raw('COALESCE(COUNT(DISTINCT tb_samples.id_samples), 0) as total_sample'), DB::raw('ms_sample_type.name_sample_type as type'))
          ->groupBy('ms_sample_type.name_sample_type')
          ->get();
      } else {
        // Semua Kesmas (existing logic)
        $samples = Sample::query()
          ->join('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'typesample_samples')
          ->whereBetween('tb_samples.created_at', [$chartStart, $chartEnd])
          ->select(DB::raw('COALESCE(COUNT(id_samples), 0) as total_sample'), DB::raw('ms_sample_type.name_sample_type as type'))
          ->groupBy('ms_sample_type.name_sample_type')
          ->get();
      }

      $countSample = [];
      $sampleTypes = [];

      foreach ($samples as  $sample){
        $countSample[] = $sample->total_sample;
        $sampleTypes[] = $sample->type;
      }
    }

    // —— Dataset grafik terpisah: Kesmas vs Klinik ——
    $chartKesmasMonths = $isDKTR || $isKLI ? [] : ($bulans ?? []);
    $chartKesmasSeries = $isDKTR || $isKLI ? [] : ($pendapatans ?? []);
    $chartKesmasLabels = $isDKTR || $isKLI ? [] : ($sampleTypes ?? []);
    $chartKesmasValues = $isDKTR || $isKLI ? [] : ($countSample ?? []);

    // Klinik: jumlah pemeriksaan per bulan (Haji vs Non-Haji), acuan tgl register
    $klinikTrendQuery = PermohonanUjiKlinik2::query()
      ->select(
        DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m") as bulan'),
        DB::raw('SUM(CASE WHEN COALESCE(is_haji, 0) = 1 THEN 1 ELSE 0 END) as total_haji'),
        DB::raw('SUM(CASE WHEN COALESCE(is_haji, 0) = 0 THEN 1 ELSE 0 END) as total_non_haji'),
        DB::raw('COUNT(*) as total_pemeriksaan')
      )
      ->whereNull('deleted_at')
      ->whereRaw('COALESCE(tglregister_permohonan_uji_klinik, created_at) BETWEEN ? AND ?', [
        $chartStart,
        $chartEnd,
      ]);

    if ($isDKTR) {
      $klinikTrendQuery->where('doctor_type', 'lab');
    }

    $pendapatanKlinikOnly = $klinikTrendQuery
      ->groupBy(DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m")'))
      ->orderBy('bulan')
      ->get();

    $chartKlinikMonths = [];
    $chartKlinikSeries = [];
    $chartKlinikSeriesHaji = [];
    $chartKlinikSeriesNonHaji = [];
    foreach ($pendapatanKlinikOnly as $row) {
      $chartKlinikMonths[] = $row->bulan;
      $chartKlinikSeries[] = (int) $row->total_pemeriksaan;
      $chartKlinikSeriesHaji[] = (int) $row->total_haji;
      $chartKlinikSeriesNonHaji[] = (int) $row->total_non_haji;
    }

    // Komposisi klinik: Haji vs Non-Haji
    $klinikHajiRows = PermohonanUjiKlinik2::query()
      ->select(
        DB::raw('CASE WHEN COALESCE(is_haji, 0) = 1 THEN "Haji" ELSE "Non-Haji" END as status_label'),
        DB::raw('COUNT(*) as total')
      )
      ->whereNull('deleted_at')
      ->whereRaw('COALESCE(tglregister_permohonan_uji_klinik, created_at) BETWEEN ? AND ?', [
        $chartStart,
        $chartEnd,
      ])
      ->when($isDKTR, function ($q) {
        $q->where('doctor_type', 'lab');
      })
      ->groupBy(DB::raw('CASE WHEN COALESCE(is_haji, 0) = 1 THEN "Haji" ELSE "Non-Haji" END'))
      ->orderByDesc('total')
      ->get();

    $chartKlinikLabels = [];
    $chartKlinikValues = [];
    foreach ($klinikHajiRows as $row) {
      $chartKlinikLabels[] = (string) $row->status_label;
      $chartKlinikValues[] = (int) $row->total;
    }

    // Paket klinik yang paling sering dipilih (top 10)
    $paketTopQuery = DB::table('tb_permohonan_uji_paket_klinik as p')
      ->leftJoin('ms_parameter_paket_klinik as m', 'm.id_parameter_paket_klinik', '=', 'p.parameter_paket_klinik')
      ->leftJoin('tb_permohonan_uji_klinik_2 as k', 'k.id_permohonan_uji_klinik', '=', 'p.permohonan_uji_klinik')
      ->whereNull('p.deleted_at')
      ->where(function ($q) {
        $q->whereNull('k.deleted_at')->orWhereNull('k.id_permohonan_uji_klinik');
      })
      ->whereNotNull('p.parameter_paket_klinik')
      ->whereRaw('COALESCE(k.tglregister_permohonan_uji_klinik, k.created_at, p.created_at) BETWEEN ? AND ?', [
        $chartStart,
        $chartEnd,
      ]);

    if ($isDKTR) {
      $paketTopQuery->where('k.doctor_type', 'lab');
    }

    $paketTopRows = $paketTopQuery
      ->select(
        DB::raw("COALESCE(NULLIF(TRIM(m.name_parameter_paket_klinik), ''), 'Paket tidak diketahui') as nama_paket"),
        DB::raw('COUNT(*) as total')
      )
      ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(m.name_parameter_paket_klinik), ''), 'Paket tidak diketahui')"))
      ->orderByDesc('total')
      ->limit(10)
      ->get();

    $chartKlinikPaketLabels = [];
    $chartKlinikPaketValues = [];
    foreach ($paketTopRows as $row) {
      $chartKlinikPaketLabels[] = (string) $row->nama_paket;
      $chartKlinikPaketValues[] = (int) $row->total;
    }

    // Backward-compatible aliases (default tab: Kesmas jika ada, else Klinik)
    if ($isDKTR || $isKLI) {
      $bulans = $chartKlinikMonths;
      $pendapatans = $chartKlinikSeries;
      $sampleTypes = $chartKlinikLabels;
      $countSample = $chartKlinikValues;
    }

    // Set default values untuk non-DKTR dan non-KLI users
    if (!$isDKTR && !$isKLI) {
      $menunggu_diagnosis = 0;
      $sudah_terdiagnosis = 0;
      $dari_rujukan_dokter = 0;
    }

    $showChartKesmas = !($isDKTR || $isKLI);
    $showChartKlinik = !$isKesmasLabUser; // lab kimia/mikro: fokus grafik Kesmas lab-nya
    $showChartKeuangan = true;

    // Analis / Tim Teknis: hanya grafik bidang lab, tanpa tab Keuangan
    if (in_array($userLevel, ['ANLS', 'ALAB', 'SOLK', 'SOLM', 'KSKM'], true)) {
      $showChartKeuangan = false;
      if ($isKLI || $userLevel === 'KSKL' || $isSolK) {
        $showChartKesmas = false;
        $showChartKlinik = true;
      } elseif ($isKesmasLabUser || $isSolM) {
        $showChartKesmas = true;
        $showChartKlinik = false;
      }
    }

    if ($isBendahara) {
      // Dashboard bendahara fokus penuh ke keuangan / status nota.
      $showChartKesmas = false;
      $showChartKlinik = false;
      $showChartKeuangan = true;
    }

    // —— Tab Keuangan: pendapatan sesuai total nota ——
    $keuanganMonths = [];
    $cursorMonth = $chartStart->copy()->startOfMonth();
    $endMonth = $chartEnd->copy()->startOfMonth();
    while ($cursorMonth->lte($endMonth)) {
      $keuanganMonths[] = $cursorMonth->format('Y-m');
      $cursorMonth->addMonth();
    }

    $kesmasMap = [];
    if ($showChartKesmas) {
      $kesmasQuery = PermohonanUji::query()
        ->select(
          DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan'),
          DB::raw('COALESCE(SUM(total_harga), 0) as total_nota'),
          DB::raw('COUNT(*) as jumlah_nota')
        )
        ->whereNull('deleted_at')
        ->whereBetween('created_at', [$chartStart, $chartEnd]);

      if ($isKesmasLabUser && $kesmasLabCodes !== []) {
        $kesmasQuery->whereExists(function ($q) use ($kesmasLabCodes) {
          $q->select(DB::raw(1))
            ->from('tb_samples')
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at');
            })
            ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
            ->whereNull('tb_samples.deleted_at')
            ->whereIn('ms_laboratorium.kode_laboratorium', $kesmasLabCodes);
        });
      }

      $kesmasRows = $kesmasQuery
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('bulan')
        ->get();

      foreach ($kesmasRows as $row) {
        $kesmasMap[$row->bulan] = [
          'total' => (float) $row->total_nota,
          'count' => (int) $row->jumlah_nota,
        ];
      }
    }

    $klinikMap = [];
    $klinikKeuanganQuery = PermohonanUjiKlinik2::query()
      ->select(
        DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m") as bulan'),
        DB::raw('COALESCE(SUM(total_harga_permohonan_uji_klinik), 0) as total_nota'),
        DB::raw('COUNT(*) as jumlah_nota')
      )
      ->whereNull('deleted_at')
      ->whereRaw('COALESCE(tglregister_permohonan_uji_klinik, created_at) BETWEEN ? AND ?', [
        $chartStart,
        $chartEnd,
      ]);
    if ($isDKTR) {
      $klinikKeuanganQuery->where('doctor_type', 'lab');
    }
    $klinikKeuanganRows = $klinikKeuanganQuery
      ->groupBy(DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m")'))
      ->orderBy('bulan')
      ->get();
    foreach ($klinikKeuanganRows as $row) {
      $klinikMap[$row->bulan] = [
        'total' => (float) $row->total_nota,
        'count' => (int) $row->jumlah_nota,
      ];
    }

    $chartKeuanganMonths = $keuanganMonths;
    $chartKeuanganSeriesKesmas = [];
    $chartKeuanganSeriesKlinik = [];
    $chartKeuanganSeriesTotal = [];
    $chartKeuanganSeriesPrimaryLabel = 'Kesmas';
    $chartKeuanganSeriesSecondaryLabel = 'Klinik';
    $chartKeuanganShowPrimary = $showChartKesmas;
    $chartKeuanganTrendHeading = 'Tren pendapatan (total nota)';
    $chartKeuanganTrendSub = 'Pendapatan sesuai total nota per bulan (' . $chartFrom . ' – ' . $chartTo . ')';
    $chartKeuanganDonutTitle = 'Komposisi pendapatan';
    $chartKeuanganDonutSub = 'Total nota Kesmas vs Klinik';
    $chartKeuanganIsMoney = true;
    $totalPendapatanKesmas = 0.0;
    $totalPendapatanKlinik = 0.0;
    $jumlahNotaKesmas = 0;
    $jumlahNotaKlinik = 0;
    foreach ($keuanganMonths as $bulan) {
      $k = (float) ($kesmasMap[$bulan]['total'] ?? 0);
      $c = (float) ($klinikMap[$bulan]['total'] ?? 0);
      $chartKeuanganSeriesKesmas[] = $k;
      $chartKeuanganSeriesKlinik[] = $c;
      $chartKeuanganSeriesTotal[] = $k + $c;
      $totalPendapatanKesmas += $k;
      $totalPendapatanKlinik += $c;
      $jumlahNotaKesmas += (int) ($kesmasMap[$bulan]['count'] ?? 0);
      $jumlahNotaKlinik += (int) ($klinikMap[$bulan]['count'] ?? 0);
    }

    $chartKeuanganLabels = [];
    $chartKeuanganValues = [];
    if ($showChartKesmas) {
      $chartKeuanganLabels[] = 'Kesmas';
      $chartKeuanganValues[] = $totalPendapatanKesmas;
    }
    $chartKeuanganLabels[] = 'Klinik';
    $chartKeuanganValues[] = $totalPendapatanKlinik;

    if ($isBendahara) {
      $paidMap = [];
      $unpaidMap = [];

      $kesmasPaymentRows = PermohonanUji::query()
        ->select(
          DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan'),
          DB::raw('SUM(CASE WHEN COALESCE(status_pembayaran, 0) = 1 THEN 1 ELSE 0 END) as lunas'),
          DB::raw('SUM(CASE WHEN COALESCE(status_pembayaran, 0) != 1 THEN 1 ELSE 0 END) as belum_lunas')
        )
        ->whereNull('deleted_at')
        ->whereBetween('created_at', [$chartStart, $chartEnd])
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->get();

      foreach ($kesmasPaymentRows as $row) {
        $paidMap[$row->bulan] = (int) ($paidMap[$row->bulan] ?? 0) + (int) $row->lunas;
        $unpaidMap[$row->bulan] = (int) ($unpaidMap[$row->bulan] ?? 0) + (int) $row->belum_lunas;
      }

      $klinikPaymentRows = PermohonanUjiKlinik2::query()
        ->select(
          DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m") as bulan'),
          DB::raw('SUM(CASE WHEN COALESCE(status_pembayaran, 0) = 1 THEN 1 ELSE 0 END) as lunas'),
          DB::raw('SUM(CASE WHEN COALESCE(status_pembayaran, 0) != 1 THEN 1 ELSE 0 END) as belum_lunas')
        )
        ->whereNull('deleted_at')
        ->whereRaw('COALESCE(tglregister_permohonan_uji_klinik, created_at) BETWEEN ? AND ?', [
          $chartStart,
          $chartEnd,
        ])
        ->groupBy(DB::raw('DATE_FORMAT(COALESCE(tglregister_permohonan_uji_klinik, created_at), "%Y-%m")'))
        ->get();

      foreach ($klinikPaymentRows as $row) {
        $paidMap[$row->bulan] = (int) ($paidMap[$row->bulan] ?? 0) + (int) $row->lunas;
        $unpaidMap[$row->bulan] = (int) ($unpaidMap[$row->bulan] ?? 0) + (int) $row->belum_lunas;
      }

      $chartKeuanganSeriesKesmas = [];
      $chartKeuanganSeriesKlinik = [];
      $chartKeuanganSeriesTotal = [];
      $totalNotaLunas = 0;
      $totalNotaBelumLunas = 0;

      foreach ($keuanganMonths as $bulan) {
        $paidCount = (int) ($paidMap[$bulan] ?? 0);
        $unpaidCount = (int) ($unpaidMap[$bulan] ?? 0);
        $chartKeuanganSeriesKesmas[] = $paidCount;
        $chartKeuanganSeriesKlinik[] = $unpaidCount;
        $chartKeuanganSeriesTotal[] = $paidCount + $unpaidCount;
        $totalNotaLunas += $paidCount;
        $totalNotaBelumLunas += $unpaidCount;
      }

      $chartKeuanganLabels = ['Lunas', 'Belum Lunas'];
      $chartKeuanganValues = [$totalNotaLunas, $totalNotaBelumLunas];
      $chartKeuanganSeriesPrimaryLabel = 'Lunas';
      $chartKeuanganSeriesSecondaryLabel = 'Belum Lunas';
      $chartKeuanganShowPrimary = true;
      $chartKeuanganTrendHeading = 'Tren nota pembayaran';
      $chartKeuanganTrendSub = 'Jumlah nota lunas vs belum lunas per bulan (' . $chartFrom . ' – ' . $chartTo . ')';
      $chartKeuanganDonutTitle = 'Komposisi status nota';
      $chartKeuanganDonutSub = 'Total nota lunas ' . number_format($totalNotaLunas) . ' · belum lunas ' . number_format($totalNotaBelumLunas);
      $chartKeuanganIsMoney = false;
    }

    $requestedTab = $request->query('chart_tab');
    if ($isBendahara) {
      $defaultChartTab = 'keuangan';
    } elseif ($requestedTab === 'keuangan' && $showChartKeuangan) {
      $defaultChartTab = 'keuangan';
    } elseif ($requestedTab === 'klinik' && $showChartKlinik) {
      $defaultChartTab = 'klinik';
    } elseif ($requestedTab === 'kesmas' && $showChartKesmas) {
      $defaultChartTab = 'kesmas';
    } else {
      $defaultChartTab = $showChartKesmas ? 'kesmas' : 'klinik';
    }

    return view(
      'masterweb::module.admin.beranda',
      compact(
        "auth",
        'total_permohonan_uji',
        'total_sample',
        'total_berjalan',
        'total_selesai',
        'bulans',
        'pendapatans',
        'sampleTypes',
        'countSample',
        'total_permohonan_uji_klinik',
        'pasien_klinik',
        'total_sampel_klinik',
        'analisa_berjalan_klinik',
        'analisa_selesai_klinik',
        'menunggu_diagnosis',
        'sudah_terdiagnosis',
        'dari_rujukan_dokter',
        'chartKesmasMonths',
        'chartKesmasSeries',
        'chartKesmasLabels',
        'chartKesmasValues',
        'chartKlinikMonths',
        'chartKlinikSeries',
        'chartKlinikSeriesHaji',
        'chartKlinikSeriesNonHaji',
        'chartKlinikLabels',
        'chartKlinikValues',
        'chartKlinikPaketLabels',
        'chartKlinikPaketValues',
        'chartKeuanganMonths',
        'chartKeuanganSeriesKesmas',
        'chartKeuanganSeriesKlinik',
        'chartKeuanganSeriesTotal',
        'chartKeuanganSeriesPrimaryLabel',
        'chartKeuanganSeriesSecondaryLabel',
        'chartKeuanganShowPrimary',
        'chartKeuanganLabels',
        'chartKeuanganValues',
        'chartKeuanganTrendHeading',
        'chartKeuanganTrendSub',
        'chartKeuanganDonutTitle',
        'chartKeuanganDonutSub',
        'chartKeuanganIsMoney',
        'totalPendapatanKesmas',
        'totalPendapatanKlinik',
        'jumlahNotaKesmas',
        'jumlahNotaKlinik',
        'showChartKesmas',
        'showChartKlinik',
        'showChartKeuangan',
        'defaultChartTab',
        'chartFrom',
        'chartTo',
        'chartLabScopeLabel',
        'kodeLaboratorium'
      )
    );
  }
}
