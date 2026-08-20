<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\SampleType;
use Smt\Masterweb\Models\VerificationActivitySample;

class LaboratoriumReportDateVerificationMonthly implements FromView, ShouldAutoSize
{

  private $month;
  private $year;
  private $sampletype;
  private $date_verification;
  private $laboratorium;

  public function __construct(int $month, int $year, $sampletype, $date_verification)
  {
    $this->month = $month;
    $this->year  = $year;
    $this->sampletype  = $sampletype;
    $this->date_verification = $date_verification;
    $this->laboratorium = null;
  }

  public function view(): View
  {
    $bulan  = $this->month;
    $tahun = $this->year;
    $sampletype = $this->sampletype;
    $date_verification = $this->date_verification;
    $laboratorium = $this->laboratorium;

    $item_sampletype = SampleType::find($sampletype);

    if (isset($laboratorium)) {
      $data_sample = Sample::whereHas('pengesahanhasil', function ($query) use ($laboratorium)  {
        return $query->where('laboratorium_id',$laboratorium)->whereNull('deleted_at');
      })

        ->whereHas('samplemethod', function ($query) use ($laboratorium){
          return $query->where('laboratorium_id',$laboratorium)->whereNull('deleted_at');
        });

      $data_sample =  $data_sample->whereHas('permohonanuji', function ($query) use ($tahun, $bulan) {
        return $query->whereMonth('tb_permohonan_uji.date_permohonan_uji', $bulan)
          ->whereYear('tb_permohonan_uji.date_permohonan_uji', $tahun)
          ->whereNull('deleted_at');
      });
      $data_sample =  $data_sample->leftjoin('tb_sample_penanganan', function ($join) use ($laboratorium)  {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL AND  tb_sample_penanganan.laboratorium_id ="'.$laboratorium.'"    LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });
    }else{
      $data_sample = Sample::whereHas('pengesahanhasil', function ($query) use ($laboratorium) {
        return $query->where('laboratorium_id',$laboratorium)->whereNull('deleted_at');
      })

        ->whereHas('samplemethod', function ($query) use ($laboratorium) {
          return $query->where('laboratorium_id',$laboratorium)->whereNull('deleted_at');
        })
        ->orderBy('tb_sample_penanganan.penanganan_sample_date', 'asc');

      $data_sample =  $data_sample->whereHas('permohonanuji', function ($query) use ($tahun, $bulan) {
        return $query->whereMonth('tb_permohonan_uji.date_permohonan_uji', $bulan)
          ->whereYear('tb_permohonan_uji.date_permohonan_uji', $tahun)
          ->whereNull('deleted_at');
      });
      $data_sample =  $data_sample->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });
    }

    if ($sampletype != null) {

      $data_sample = $data_sample->where('typesample_samples', $sampletype);
    }
    $data_sample = $data_sample->get();

    if (isset($laboratorium)) {

      $method_all = Sample::leftjoin('tb_sample_penanganan', function ($join) use ($laboratorium){
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL AND  tb_sample_penanganan.laboratorium_id ="'.$laboratorium.'"    LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
        ->join('tb_sample_method', function ($join) use ($laboratorium) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->where('tb_sample_method.laboratorium_id',$laboratorium)
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_method', function ($join){
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')

            ->whereNull('ms_method.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('tb_baku_mutu', function ($join) use ($laboratorium) {
          $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
            ->where('tb_baku_mutu.lab_id',$laboratorium)
            ->on('tb_baku_mutu.sampletype_id', '=', 'tb_samples.typesample_samples')
            ->whereNull('tb_baku_mutu.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->join('ms_unit as unit_baku_mutu', function ($join) {
          $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
            ->whereNull('unit_baku_mutu.deleted_at')
            ->whereNull('tb_baku_mutu.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) use ($laboratorium) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->where('ms_laboratorium.id_laboratorium',$laboratorium)
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*', 'tb_baku_mutu.*', 'unit_baku_mutu.*')
        ->distinct('ms_method.id_method');
    }else{
      $method_all = Sample::leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('tb_baku_mutu', function ($join) {
          $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
            ->on('tb_baku_mutu.sampletype_id', '=', 'tb_samples.typesample_samples')
            ->whereNull('tb_baku_mutu.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->join('ms_unit as unit_baku_mutu', function ($join) {
          $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
            ->whereNull('unit_baku_mutu.deleted_at')
            ->whereNull('tb_baku_mutu.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*', 'tb_baku_mutu.*', 'unit_baku_mutu.*')
        ->distinct('ms_method.id_method');
    }

    $method_all =  $method_all->whereHas('permohonanuji', function ($query) use ($tahun, $bulan) {
      return $query->whereMonth('tb_permohonan_uji.date_permohonan_uji', $bulan)
        ->whereYear('tb_permohonan_uji.date_permohonan_uji', $tahun)
        ->whereNull('deleted_at');
    });
    if ($sampletype != null) {

      $method_all = $method_all->where('typesample_samples', $sampletype);
    }
    $method_all = $method_all->get();


    if (isset($laboratorium)) {

      $data_sample_methods = Sample::orderBy('penanganan_sample_date', 'asc')
        ->leftjoin('tb_sample_penanganan', function ($join) use ($laboratorium){
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL AND  tb_sample_penanganan.laboratorium_id ="'.$laboratorium.'"   LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        });
    }else{
      $data_sample_methods = Sample::orderBy('penanganan_sample_date', 'asc')
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL   LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        });
    }

    $data_sample_methods =  $data_sample_methods->whereHas('permohonanuji', function ($query) use ($tahun, $bulan) {
      return $query->whereMonth('tb_permohonan_uji.date_permohonan_uji', $bulan)
        ->whereYear('tb_permohonan_uji.date_permohonan_uji', $tahun)
        ->whereNull('deleted_at');
    });
    if ($sampletype != null) {

      $data_sample_methods = $data_sample_methods->where('typesample_samples', $sampletype);
    }

    if (isset($laboratorium)) {
      $data_sample_methods = $data_sample_methods->whereHas('samplemethod', function ($query) use ($laboratorium){
        return $query->where('laboratorium_id',$laboratorium)->whereNull('deleted_at');
      });
    }
    $data_sample_methods = $data_sample_methods->get();

    $samples = [];

    $lab_in_to = [];



    foreach ($data_sample_methods as $data_sample_method) {
      $sample = [];
      $sample["sample"] = $data_sample_method;

      if (isset($laboratorium)) {
        $labs = Laboratorium::where('id_laboratorium',$laboratorium)->get();
      }else{
        $labs = Sample::where('tb_samples.id_samples', '=', $data_sample_method->id_samples)
          ->join('tb_sample_method', function ($join) {
            $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('tb_samples.deleted_at')
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_sample_method.deleted_at');
              });
          })
          ->select('ms_laboratorium.*')
          ->distinct('ms_laboratorium.id_laboratorium')
          ->get();
      }

      $sample["lab_id"] = [];
      foreach ($labs as $lab) {

        if ($lab->kode_laboratorium == "KIM" && !in_array("KIM", $lab_in_to)) {
          array_push($lab_in_to, $lab->kode_laboratorium);
        }
        if ($lab->kode_laboratorium == "MBI" && !in_array("MBI", $lab_in_to)) {
          array_push($lab_in_to, $lab->kode_laboratorium);
        }
        $dataVerifQuery = VerificationActivitySample::query()->where('id_sample', '=', $sample['sample']->id_samples);
        $sample["lab_id"][$lab->kode_laboratorium] = [];

        $sample["lab_id"][$lab->kode_laboratorium]['pendaftaran_registrasi'] = $dataVerifQuery->where('id_verification_activity', '=', 1)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
        $sample["lab_id"][$lab->kode_laboratorium]['pengambilan_sample'] = $dataVerifQuery->where('id_verification_activity', '=', 6)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
        $sample["lab_id"][$lab->kode_laboratorium]['pemeriksaan_analitik'] = $dataVerifQuery->where('id_verification_activity', '=', 2)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
        $sample["lab_id"][$lab->kode_laboratorium]['input_output_hasil_px'] = $dataVerifQuery->where('id_verification_activity', '=', 3)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
        $sample["lab_id"][$lab->kode_laboratorium]['verifikasi'] = $dataVerifQuery->where('id_verification_activity', '=', 4)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
        $sample["lab_id"][$lab->kode_laboratorium]['validasi'] = $dataVerifQuery->where('id_verification_activity', '=', 5)->get(['start_date', 'stop_date', 'nama_petugas'])->first();
      }
      array_push($samples, $sample);

    }

    $size = count($date_verification);

    return view('masterweb::module.admin.laboratorium.report.report-date-verification-monthly.formatPrint.report-date-verification-monthly-maatweb', [
      'data_sample' => $data_sample,
      'bulan' => $bulan,
      'tahun' => $tahun,
      'size' => $size,
      'item_sampletype' => $item_sampletype,
      'date_verification' => $date_verification,
      'samples' => $samples
    ]);
  }
}
