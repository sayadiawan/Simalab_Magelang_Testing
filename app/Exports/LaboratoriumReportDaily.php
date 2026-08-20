<?php

namespace App\Exports;

use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\Program;

use Illuminate\Support\Facades\DB;

use Illuminate\Contracts\View\View;
use Smt\Masterweb\Models\SampleType;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Models\SampleResult;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Smt\Masterweb\Models\Laboratorium;

class LaboratoriumReportDaily  implements FromView, ShouldAutoSize
{
  private $tanggal;
  private $program;
  private $sampletype;
  private $laboratorium;

  public function __construct($tanggal, $program, $sampletype, $laboratorium)
  {
    $this->tanggal = $tanggal;
    $this->program  = $program;
    $this->sampletype  = $sampletype;
    $this->laboratorium  = $laboratorium;
  }

  public function view(): View
  {
    $tanggal  = $this->tanggal;
    $program = $this->program;
    $sampletype = $this->sampletype;
    $laboratorium = $this->laboratorium;


    $item_program = Program::find($program);
    $item_sampletype = SampleType::find($sampletype);
    $item_laboratorium = Laboratorium::find($laboratorium);

    $data_sample = Sample::whereHas('permohonanuji', function ($query) use ($tanggal) {
      return $query->whereNull('deleted_at')->whereDate('date_permohonan_uji', $tanggal);
    })
      ->whereHas('samplemethod', function ($query) {
        return $query->whereNull('tb_sample_method.deleted_at')
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          });
      })
      ->orderBy('tb_sample_penanganan.penanganan_sample_date', 'asc')
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });

    if ($program != null) {
      $data_sample = $data_sample->where('program_samples', $program);
    }

    if ($sampletype != null) {
      $data_sample = $data_sample->where('typesample_samples', $sampletype);
    }

    if ($laboratorium != null) {
      $data_sample = $data_sample->where('laboratorium_id', $laboratorium);
    }

    $data_sample = $data_sample->get();

    $method_all = Sample::whereHas('permohonanuji', function ($query) use ($tanggal) {
      return $query->whereNull('deleted_at')->whereDate('date_permohonan_uji', $tanggal);
    })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });

   

    if ($laboratorium != null) {
      $method_all = $method_all ->join('tb_sample_method', function ($join) use ($laboratorium) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at')
            ->where('tb_sample_method.laboratorium_id', $laboratorium);
        });
    }else{
      $method_all ->join('tb_sample_method', function ($join)  {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        });
    }

    $method_all=$method_all->join('ms_method', function ($join) {
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
      ->select('ms_method.*', 'tb_baku_mutu.*', 'unit_baku_mutu.*')
      ->distinct('ms_method.id_method');

     if ($program != null) {
      $method_all = $method_all->where('program_samples', $program);
    }

    if ($sampletype != null) {
      $method_all = $method_all->where('typesample_samples', $sampletype);
    }

    $method_all = $method_all->get();

    $data_sample_methods = Sample::whereHas('permohonanuji', function ($query) use ($tanggal) {
      return $query->whereNull('deleted_at')->whereDate('date_permohonan_uji', $tanggal);
    })
     
      ->orderBy('tb_sample_penanganan.penanganan_sample_date', 'asc')
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });

    if ($program != null) {
      $data_sample_methods = $data_sample_methods->where('program_samples', $program);
    }

    if ($sampletype != null) {
      $data_sample_methods = $data_sample_methods->where('typesample_samples', $sampletype);
    }

    if ($laboratorium != null) {
      $data_sample_methods = $data_sample_methods ->whereHas('samplemethod', function ($query) use($laboratorium) {
        return $query->whereNull('tb_sample_method.deleted_at')
          ->join('ms_laboratorium', function ($join) use($laboratorium) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at')
              ->where('tb_sample_method.laboratorium_id', $laboratorium)
              ->whereNull('tb_sample_method.deleted_at');
          });
      });
    }else{
         $data_sample_methods = $data_sample_methods ->whereHas('samplemethod', function ($query)  {
        return $query->whereNull('tb_sample_method.deleted_at')
          ->join('ms_laboratorium', function ($join)  {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          });
      });
    }

    $data_sample_methods = $data_sample_methods->get();

    // dd($data_sample_methods);

    $sampels = [];
    foreach ($data_sample_methods as $data_sample_method) {
      $sample = [];
      $sample["sample"] = $data_sample_method;
      $sample["method"] = [];
      foreach ($method_all as $method) {

        $result = SampleResult::where('method_id',  $method->id_method)
          ->where('sample_id', $data_sample_method->id_samples)
          ->first();

        if (isset($result)) {
          if ($method->sampletype_id == $data_sample_method->typesample_samples) {
            array_push($sample["method"], $result->hasil);
          } else {
            array_push($sample["method"], '');
          }
        } else {
          array_push($sample["method"], '');
        }
      }
      array_push($sampels, $sample);
    }

    return view('masterweb::module.admin.laboratorium.report.report-daily.formatPrint.report-daily-maatweb', [
      'data_sample' => $data_sample,
      'tanggal' => $tanggal,
      'item_program' => $item_program,
      'item_sampletype' => $item_sampletype,
      'method_all' => $method_all,
      'data_sample_method' => $sampels
    ]);
  }
}