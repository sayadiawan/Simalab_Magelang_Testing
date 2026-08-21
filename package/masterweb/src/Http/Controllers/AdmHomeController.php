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
    $isDKTR = ($userLevel == 'DKTR');
    
    // Cek laboratorium user (jika ada)
    $kodeLaboratorium = $auth->laboratorium->kode_laboratorium ?? null;
    $isKLI = ($kodeLaboratorium == 'KLI');
    $isKIM = ($kodeLaboratorium == 'KIM');
    $isMBI = ($kodeLaboratorium == 'MBI'); // Mikrobiologi

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
      if ($isKIM || $isMBI) {
        // Dashboard khusus lab KIMIA / Mikrobiologi: hanya data berdasarkan lab terkait (tb_sample_method + ms_laboratorium)
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
          ->where('ms_laboratorium.kode_laboratorium', $kodeLaboratorium)
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

      // Statistik Kesmas per bulan (terpisah dari klinik)
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

      $bulans = [];
      $pendapatans = [];

      foreach ($pendapatan as $pd) {
        $bulans[] = $pd->bulan;
        $pendapatans[] = (int) $pd->total_permohonan;
      }

      // Data chart jenis sampel
      if ($isKIM || $isMBI) {
        // Hanya sampel yang dianalisa di lab KIM / MBI (sesuai kode laboratorium user)
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
          ->where('ms_laboratorium.kode_laboratorium', $kodeLaboratorium)
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
    $showChartKlinik = true;
    $requestedTab = $request->query('chart_tab');
    if ($requestedTab === 'klinik' && $showChartKlinik) {
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
        'showChartKesmas',
        'showChartKlinik',
        'defaultChartTab',
        'chartFrom',
        'chartTo'
      )
    );
  }
}
