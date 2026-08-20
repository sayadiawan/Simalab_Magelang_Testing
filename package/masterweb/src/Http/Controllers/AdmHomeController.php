<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Smt\Masterweb\Rules\Captcha;
use \Smt\Masterweb\Models\Sample;
use Illuminate\Support\Facades\DB;


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
  public function index()
  {
    $auth = Auth()->user();
    $userLevel = $auth->getlevel->level ?? null;
    $isDKTR = ($userLevel == 'DKTR');
    
    // Cek laboratorium user (jika ada)
    $kodeLaboratorium = $auth->laboratorium->kode_laboratorium ?? null;
    $isKLI = ($kodeLaboratorium == 'KLI');
    $isKIM = ($kodeLaboratorium == 'KIM');
    $isMBI = ($kodeLaboratorium == 'MBI'); // Mikrobiologi
    
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
        ->whereBetween('created_at', [now()->subMonths(12), now()->addMonths(4)])
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

      // Statistik Kesmas per bulan: jumlah permohonan per bulan
      $pendapatan = PermohonanUji::query()
        ->select(
          DB::raw('COUNT(*) as total_permohonan'),
          DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
        )
        ->whereBetween('created_at', [now()->subMonths(12), now()->addMonths(4)])
        ->where('status_pembayaran', 1)
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('bulan')
        ->get();

      $pendapatanKlinik = PermohonanUjiKlinik2::query()
        ->select(
          DB::raw('COUNT(*) as total_permohonan'),
          DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan')
        )
        ->whereBetween('created_at', [now()->subMonths(12), now()->addMonths(4)])
        ->where('status_pembayaran', 1)
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('bulan')
        ->get();

      $bulans = [];
      $pendapatans = []; // sekarang: jumlah permohonan per bulan (Kesmas+Klinik)

      foreach ($pendapatan as $pd) {
        $found = false;

        foreach ($pendapatanKlinik as $pdk) {
          if ($pd->bulan == $pdk->bulan) {
            $bulans[] = $pd->bulan;
            $pendapatans[] = $pd->total_permohonan + $pdk->total_permohonan;
            $found = true;
            break;
          }
        }

        if (!$found) {
          $bulans[] = $pd->bulan;
          $pendapatans[] = $pd->total_permohonan;
        }
      }

      foreach ($pendapatanKlinik as $pdk) {
        if (!in_array($pdk->bulan, $bulans)) {
          $bulans[] = $pdk->bulan;
          $pendapatans[] = $pdk->total_permohonan;
        }
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
          ->select(DB::raw('COALESCE(COUNT(DISTINCT tb_samples.id_samples), 0) as total_sample'), DB::raw('ms_sample_type.name_sample_type as type'))
          ->groupBy('ms_sample_type.name_sample_type')
          ->get();
      } else {
        // Semua Kesmas (existing logic)
        $samples = Sample::query()
          ->join('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'typesample_samples')
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


    // Set default values untuk non-DKTR dan non-KLI users
    if (!$isDKTR && !$isKLI) {
      $menunggu_diagnosis = 0;
      $sudah_terdiagnosis = 0;
      $dari_rujukan_dokter = 0;
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
      )
    );
  }
}
