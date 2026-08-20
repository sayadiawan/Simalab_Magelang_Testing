<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Container;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\PenerimaanSample;
use \Smt\Masterweb\Models\PengesahanHasil;
use \Smt\Masterweb\Models\Unit;
use \Smt\Masterweb\Models\SampleResultDetail;



use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;
use DB;

use Mapper;


use Carbon\Carbon;
use Smt\Masterweb\Models\SampleTypeDetail;
use \Smt\Masterweb\Models\VerificationActivitySample;
use \Smt\Masterweb\Models\NomerLabSequence;
use \Smt\Masterweb\Models\LabNum;
use \Smt\Masterweb\Models\NomerLabKesmas;

class LaboratoriumPengesahanHasilManagement extends Controller
{
  /**
   * Sama seperti LaboratoriumVerifikasiHasilManagement: override tb_baku_mutu_sample_override
   * disimpan dengan sample_progress_id = laboratorium_progress langkah "baca-hasil".
   * Sertakan ID langkah baca-hasil lab + id_sample_analitik_progress agar override ikut ke LHU.
   */
  protected function bacaHasilProgressIdsForSample(string $sampleId, string $laboratoriumId): \Illuminate\Support\Collection
  {
    $bacaHasilProgressIds = \Smt\Masterweb\Models\LaboratoriumProgress::query()
      ->where('laboratorium_id', $laboratoriumId)
      ->where('link', 'baca-hasil')
      ->whereNull('deleted_at')
      ->pluck('id_laboratorium_progress');

    $analitikQuery = \Smt\Masterweb\Models\SampleAnalitikProgress::query()
      ->where('sample_id', $sampleId)
      ->where('laboratorium_id', $laboratoriumId)
      ->whereNull('deleted_at');

    if ($bacaHasilProgressIds->isNotEmpty()) {
      $analitikQuery->whereIn('laboratorium_progress_id', $bacaHasilProgressIds->all());
    } else {
      $analitikQuery->whereIn('laboratorium_progress_id', function ($sub) use ($laboratoriumId) {
        $sub->select('id_laboratorium_progress')
          ->from('tb_laboratorium_progress')
          ->where('laboratorium_id', $laboratoriumId)
          ->where('link', 'baca-hasil')
          ->whereNull('deleted_at');
      });
    }

    $analitikRows = $analitikQuery->get(['id_sample_analitik_progress', 'laboratorium_progress_id']);

    return $bacaHasilProgressIds
      ->merge($analitikRows->pluck('laboratorium_progress_id'))
      ->merge($analitikRows->pluck('id_sample_analitik_progress'))
      ->merge(collect(['bfecda4a-73f2-47d6-9fc3-01f65e0f02a1', 'bc2850f5-4ec4-450f-a727-2b1428c861d9']))
      ->filter()
      ->unique()
      ->values();
  }

  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index($id, $idlab)
  {
    // This method is not used, but keeping for compatibility
    Carbon::setLocale('id');
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.id_sample_type', 'ms_sample_type.name_sample_type', 'ms_laboratorium.id_laboratorium', 'ms_laboratorium.kode_laboratorium', 'ms_laboratorium.nama_laboratorium')
      ->first();
    $sampletype_id = $sample->typesample_samples; // pakai FK langsung dari tb_samples
    $jenis_makanan_id = $sample->jenis_makanan_id;
    // Untuk sampel Makanan/Minuman/Lainnya yang punya jenis_makanan_id,
    // baku mutu wajib mengikuti jenis makanan pada lab aktif.
    $isSampleMakanan = (isset($sample->name_sample_type) && $sample->name_sample_type === 'Makanan/Minuman/Lainnya');

    if ($isSampleMakanan && isset($jenis_makanan_id)) {

      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at')
                // Spesifik jenis makanan ATAU generik (null/'') — sama seperti baca-hasil
                ->whereRaw(
                  '(tb_baku_mutu.jenis_makanan_id = ? OR tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)',
                  [$jenis_makanan_id, '']
                );
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })



        ->select(
          'tb_baku_mutu.*',
          'tb_baku_mutu.lokasi_data as baku_mutu_lokasi_data',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
    } else {
      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })




        ->select(
          'tb_baku_mutu.*',
          'tb_baku_mutu.lokasi_data as baku_mutu_lokasi_data',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->distinct('ms_method.id_method')
        ->get();
    }

    // Pastikan benar-benar unik per method (join detail/non-klinik bisa menggandakan row walaupun sudah DISTINCT)
    $laboratoriummethods = collect($laboratoriummethods)
      ->unique(function ($m) {
        return $m->id_method ?? $m->method_id ?? null;
      })
      ->values();

    //pengurutan order ist
    $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



    $laboratoriummethods = kesmas_sort_laboratorium_methods($laboratoriummethods, $sample_type_details);

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...
      $laboratoriummethods[$key]->detail = array();
      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();
    }

    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();


    return view('masterweb::module.admin.laboratorium.penerimaan-sample.penerimaan', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }






  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create($id, $idlab)
  {
    //get auth user




    $user = Auth()->user();
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.id_sample_type', 'ms_sample_type.name_sample_type', 'ms_laboratorium.id_laboratorium', 'ms_laboratorium.kode_laboratorium', 'ms_laboratorium.nama_laboratorium')
      ->first();
    $sampletype_id = $sample->typesample_samples; // pakai FK langsung dari tb_samples
    $jenis_makanan_id = $sample->jenis_makanan_id;
    // Untuk sampel Makanan/Minuman/Lainnya yang punya jenis_makanan_id,
    // baku mutu wajib mengikuti jenis makanan pada lab aktif.
    $isSampleMakanan = (isset($sample->name_sample_type) && $sample->name_sample_type === 'Makanan/Minuman/Lainnya');

    if ($isSampleMakanan && isset($jenis_makanan_id)) {

      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at')
                // Spesifik jenis makanan ATAU generik (null/'') — sama seperti baca-hasil
                ->whereRaw(
                  '(tb_baku_mutu.jenis_makanan_id = ? OR tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)',
                  [$jenis_makanan_id, '']
                );
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })



        ->select(
          'tb_baku_mutu.*',
          'tb_baku_mutu.lokasi_data as baku_mutu_lokasi_data',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu'),
          DB::raw('tb_baku_mutu.name_report as name_report'),
          DB::raw('tb_baku_mutu.nilai_baku_mutu as nilai_baku_mutu'),
          DB::raw('tb_baku_mutu.min as min'),
          DB::raw('tb_baku_mutu.max as max'),
          DB::raw('tb_baku_mutu.equal as equal')
        )
        ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id])
        ->get();
    } else {
      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })




        ->select(
          'tb_baku_mutu.*',
          'tb_baku_mutu.lokasi_data as baku_mutu_lokasi_data',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu'),
          DB::raw('tb_baku_mutu.name_report as name_report'),
          DB::raw('tb_baku_mutu.nilai_baku_mutu as nilai_baku_mutu'),
          DB::raw('tb_baku_mutu.min as min'),
          DB::raw('tb_baku_mutu.max as max'),
          DB::raw('tb_baku_mutu.equal as equal')
        )
        ->distinct('ms_method.id_method')
        ->get();
    }

    // Pastikan benar-benar unik per method (join detail/non-klinik bisa menggandakan row walaupun sudah DISTINCT)
    // Ambil baris pertama per method (prioritas jenis spesifik dari orderByRaw)
    $laboratoriummethods = collect($laboratoriummethods)
      ->groupBy(function ($m) {
        return $m->id_method ?? $m->method_id ?? null;
      })
      ->map(function ($group) {
        $item = $group->first();
        if (empty($item->name_report) && (
          !empty($item->id_baku_mutu)
          || !empty($item->nilai_baku_mutu)
          || (isset($item->min) && $item->min !== null && $item->min !== '')
          || (isset($item->max) && $item->max !== null && $item->max !== '')
        )) {
          $item->name_report = $item->params_method ?? $item->name_method ?? '-';
        }
        return $item;
      })
      ->values();
    //pengurutan order ist
    $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



    $method_all_temp = [];


    foreach ($sample_type_details as $sample_type_detail) {
      # code...
      foreach ($laboratoriummethods as $method) {
        # code...


        // print("& ".$method->id_method." ".$sample_type_detail->method_id);
        if ($method->id_method == $sample_type_detail->method_id) {
          $method_all_temp[] = $method;
        }
      }
    }

    if ($method_all_temp != [] and count($laboratoriummethods) == count($method_all_temp)) {
      # code...
      $laboratoriummethods = $method_all_temp;
    }

    // dd(  $sample_type_details);




    // Apply per-sample baku mutu overrides dari tb_baku_mutu_sample_override
    $sampleProgressIds = $this->bacaHasilProgressIdsForSample($id, $idlab);

    if ($sampleProgressIds->isNotEmpty()) {
      $bmOverrides = \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $sampleProgressIds)
        ->get()
        ->keyBy('method_id');

      if ($bmOverrides->isNotEmpty()) {
        $laboratoriummethods = collect($laboratoriummethods)->map(function ($item) use ($bmOverrides) {
          if (isset($bmOverrides[$item->method_id])) {
            $ov = $bmOverrides[$item->method_id];
            if (!is_null($ov->nilai_baku_mutu)) $item->nilai_baku_mutu = $ov->nilai_baku_mutu;
            if (!is_null($ov->min))             $item->min             = $ov->min;
            if (!is_null($ov->max))             $item->max             = $ov->max;
            if (!is_null($ov->equal))           $item->equal           = $ov->equal;
            if (empty($item->name_report)) {
              $item->name_report = $item->params_method ?? $item->name_method ?? '-';
            }
            $item->has_sample_override = true;
          }
          return $item;
        });
      }
    }

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...
      $laboratoriummethods[$key]->detail = array();
      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();
    }

    // dd($laboratoriummethods);

    $pengesahan_hasil = PengesahanHasil::where('laboratorium_id', $idlab)
      ->where('sample_id', $id)->first();

    // Ambil data laboratorium
    $laboratorium = Laboratorium::find($idlab);

    // Ambil default nama petugas dari Step 2: Disposisi ke Koordinator Kesmas (dari PenerimaanSample)
    $penerimaan_sample = PenerimaanSample::where('laboratorium_id', $idlab)
      ->where('sample_id', $id)
      ->first();
    $default_analis = null;
    if ($penerimaan_sample && $penerimaan_sample->disposisi_koordinator_kesmas) {
      $default_analis = $penerimaan_sample->disposisi_koordinator_kesmas;
    }

    // Ambil list analis dari VerificationActivity (step 5 = Pengesahan Hasil)
    $analis_list = [];
    $verificationActivity = \Smt\Masterweb\Models\VerificationActivity::all()->keyBy('id')->toArray();
    if (isset($verificationActivity[5])) {
      $activity5 = (object) $verificationActivity[5];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $analis_list = array_filter(explode(', ', $activity5->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $analis_list = array_filter(explode(', ', $activity5->kimia ?? ''));
      } else {
        $analis_list = array_filter(explode(', ', $activity5->klnik ?? ''));
      }
    }

    // Ambil data verifikasi step 5 jika sudah ada untuk default start date dan stop date
    $pengesahan_hasil_verif = VerificationActivitySample::where('id_sample', $id)
      ->where('id_verification_activity', 5)
      ->first();

    $default_start_date_verifikasi = null;
    $default_stop_date_verifikasi = null;
    
    if ($pengesahan_hasil_verif) {
      // Jika sudah ada, gunakan data yang sudah tersimpan
      $default_start_date_verifikasi = $pengesahan_hasil_verif->start_date ? Carbon::parse($pengesahan_hasil_verif->start_date) : null;
      $default_stop_date_verifikasi = $pengesahan_hasil_verif->stop_date ? Carbon::parse($pengesahan_hasil_verif->stop_date) : null;
    } else {
      // Jika belum ada, gunakan tanggal sekarang
      $default_start_date_verifikasi = Carbon::now();
      $default_stop_date_verifikasi = Carbon::now();
    }

    // Cek apakah sampel ini adalah sampel terakhir dalam kelompok (permohonan + lab + jenis sampel)
    $isLastSampleForNomerLab = false;
    $assignedNomerLab        = null;
    $assignedNomerLabYear    = null;
    $nomerLabGroupTotal      = 0;
    $nomerLabGroupDone       = 0;
    $nextNomerLabPreview     = null;

    $currentPermohonan = $sample->permohonan_uji_id ?? null;
    $currentSampleType = $sample->typesample_samples ?? null;

    if ($currentPermohonan && $idlab) {
      // Kumpulkan semua ID sampel dalam kelompok yang sama
      $groupSampleIds = DB::table('tb_samples as s')
        ->join('tb_sample_method as sm', function ($j) use ($idlab) {
          $j->on('sm.sample_id', '=', 's.id_samples')
            ->where('sm.laboratorium_id', $idlab)
            ->whereNull('sm.deleted_at');
        })
        ->where('s.permohonan_uji_id', $currentPermohonan)
        ->where('s.typesample_samples', $currentSampleType)
        ->whereNull('s.deleted_at')
        ->pluck('s.id_samples')
        ->unique()
        ->values();

      $nomerLabGroupTotal = $groupSampleIds->count();

      // Berapa yang sudah selesai pengesahan (termasuk sampel ini jika sudah)
      $nomerLabGroupDone = $groupSampleIds->isEmpty() ? 0 : DB::table('tb_pengesahan_hasil')
        ->whereIn('sample_id', $groupSampleIds->toArray())
        ->where('laboratorium_id', $idlab)
        ->whereNull('deleted_at')
        ->count();

      // Apakah sampel ini sendiri sudah selesai pengesahan?
      $thisSampleDone = DB::table('tb_pengesahan_hasil')
        ->where('sample_id', $id)
        ->where('laboratorium_id', $idlab)
        ->whereNull('deleted_at')
        ->exists();

      $otherIds   = $groupSampleIds->diff([$id])->values();
      $othersDone = $otherIds->isEmpty() ? 0 : DB::table('tb_pengesahan_hasil')
        ->whereIn('sample_id', $otherIds->toArray())
        ->where('laboratorium_id', $idlab)
        ->whereNull('deleted_at')
        ->count();

      // Sampel terakhir: semua yang lain sudah selesai, yang ini belum
      $isLastSampleForNomerLab = ($othersDone == $otherIds->count()) && !$thisSampleDone && $nomerLabGroupTotal > 0;

      if ($isLastSampleForNomerLab) {
        $nextNomerLabPreview = NomerLabSequence::peekNextNumber((int) date('Y'));
      }

      // Cek nomer lab yang sudah ditetapkan untuk kelompok ini
      $existingNomerLab = \Smt\Masterweb\Models\NomerLabKesmas::where('permohonan_uji_id', $currentPermohonan)
        ->where('laboratorium_id', $idlab)
        ->where('sample_type_id', $currentSampleType)
        ->first();

      if ($existingNomerLab) {
        $assignedNomerLab     = $existingNomerLab->nomer_lab;
        $assignedNomerLabYear = $existingNomerLab->year;
      }
    }

    return view('masterweb::module.admin.laboratorium.pengesahan-hasil.pengesahan_hasil', compact('pengesahan_hasil', 'user', 'sample', 'laboratoriummethods', 'default_analis', 'analis_list', 'default_start_date_verifikasi', 'default_stop_date_verifikasi', 'idlab', 'isLastSampleForNomerLab', 'assignedNomerLab', 'assignedNomerLabYear', 'nomerLabGroupTotal', 'nomerLabGroupDone', 'nextNomerLabPreview'));
    //get all menu public
  }




  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $id, $idlabs)
  {
    $data = $request->all();

    $parsePengesahanDate = function ($dateStr) {
      if (empty($dateStr)) {
        return null;
      }

      $dateStr = trim((string) $dateStr);
      $acceptedFormats = [
        'd/m/Y',
        'd-m-Y',
        'Y-m-d',
        'd/m/Y H:i',
        'Y-m-d H:i:s',
      ];

      foreach ($acceptedFormats as $format) {
        try {
          return Carbon::createFromFormat($format, $dateStr);
        } catch (\Throwable $e) {
          // Try next format.
        }
      }

      try {
        return Carbon::parse($dateStr);
      } catch (\Throwable $e) {
        return null;
      }
    };

    $parsedPengesahanDate = $parsePengesahanDate($data["pengesahan_hasil"] ?? null);

    // print_r($data);
    $pengesahan_hasil = PengesahanHasil::where('laboratorium_id', $idlabs)
      ->where('sample_id', $id)->first();

    if (isset($pengesahan_hasil)) {
      if ($parsedPengesahanDate) {
        $pengesahan_hasil->pengesahan_hasil_date = $parsedPengesahanDate->format('Y-m-d H:i:s');
      }

      $pengesahan_hasil->save();
    } else {
      $user = Auth()->user();
      $pengesahan_hasil = new PengesahanHasil;
      //uuid
      $uuid4 = Uuid::uuid4();

      $pengesahan_hasil->id_pengesahan_hasil = $uuid4->toString();
      $pengesahan_hasil->sample_id = $id;
      $pengesahan_hasil->laboratorium_id = $idlabs;
      $pengesahan_hasil->pengesahan_hasil_date = $parsedPengesahanDate
        ? $parsedPengesahanDate->format('Y-m-d H:i:s')
        : Carbon::now()->format('Y-m-d H:i:s');

      $pengesahan_hasil->save();
    }

    // Simpan verifikasi pengesahan hasil (verification_step = 5) jika ada data verifikasi di request
    if ($request->has('verification_step_verifikasi_pengesahan') && $request->input('verification_step_verifikasi_pengesahan') == 5) {
      $verifikasi_start_date = $request->input('start_date_verifikasi_pengesahan_hidden');
      $verifikasi_stop_date = $request->input('stop_date_verifikasi_pengesahan_hidden');
      $verifikasi_nama_petugas = $request->input('nama_petugas_verifikasi_pengesahan_hidden');
      
      if ($verifikasi_start_date && $verifikasi_stop_date && $verifikasi_nama_petugas) {
        // Helper function untuk parse date dari format d/m/Y H:i
        $parseDate = function($dateStr) {
          if (empty($dateStr)) return null;
          
          // Coba format d/m/Y H:i
          if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $dateStr, $matches)) {
            return Carbon::createFromFormat('d/m/Y H:i', $dateStr);
          }
          
          // Coba format lain jika perlu
          try {
            return Carbon::parse($dateStr);
          } catch (\Exception $e) {
            return null;
          }
        };

        $verificationActivitySample = VerificationActivitySample::where('id_sample', $id)
          ->where('id_verification_activity', 5)
          ->first();

        if (!$verificationActivitySample) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $id;
          $verificationActivitySample->id_verification_activity = 5;
        }
        
        $parsedStartDate = $parseDate($verifikasi_start_date);
        $parsedStopDate = $parseDate($verifikasi_stop_date);
        
        if ($parsedStartDate) {
          $verificationActivitySample->start_date = $parsedStartDate->format('Y-m-d H:i:s');
        }
        if ($parsedStopDate) {
          $verificationActivitySample->stop_date = $parsedStopDate->format('Y-m-d H:i:s');
        }
        $verificationActivitySample->nama_petugas = $verifikasi_nama_petugas;
        $verificationActivitySample->is_done = 1;
        $verificationActivitySample->save();

        // Assign Nomer Lab kesmas per (lab + jenis sampel) lalu propagate ke tb_lab_num tiap sampel
        try {
          $sample = Sample::find($id);
          if ($sample && $sample->permohonan_uji_id && $idlabs) {
            $nomerLabManual = $request->input('nomer_lab_manual');
            if (!empty($nomerLabManual) && is_numeric($nomerLabManual)) {
              // Manual: selalu upsert — sebelumnya hanya create sehingga baris yang sudah ada tidak pernah di-update (PDF tetap titik-titik).
              $manualInt = (int) $nomerLabManual;
              $yearInt   = (int) date('Y');
              $kesmasKey = [
                'permohonan_uji_id' => $sample->permohonan_uji_id,
                'laboratorium_id'   => $idlabs,
                'sample_type_id'    => $sample->typesample_samples,
              ];
              $existingKesmas = NomerLabKesmas::where($kesmasKey)->first();
              if ($existingKesmas) {
                $existingKesmas->nomer_lab = $manualInt;
                $existingKesmas->year     = $yearInt;
                $existingKesmas->save();
              } else {
                NomerLabKesmas::create(array_merge($kesmasKey, [
                  'id'         => Uuid::uuid4()->toString(),
                  'nomer_lab'  => $manualInt,
                  'year'       => $yearInt,
                ]));
              }
            } else {
              // Auto-assign: hanya jika semua sampel dalam kelompok sudah selesai
              NomerLabSequence::assignKesmasPerLabIfAllDone(
                $sample->permohonan_uji_id,
                $idlabs,
                $sample->typesample_samples ?? null
              );
            }

            // Propagasi nomer lab ke tb_lab_num untuk SEMUA sampel dalam kelompok yang sama
            $assignedRecord = NomerLabKesmas::where('permohonan_uji_id', $sample->permohonan_uji_id)
              ->where('laboratorium_id', $idlabs)
              ->where('sample_type_id', $sample->typesample_samples)
              ->first();

            if ($assignedRecord) {
              $groupSampleIds = DB::table('tb_samples as s')
                ->join('tb_sample_method as sm', function ($j) use ($idlabs) {
                  $j->on('sm.sample_id', '=', 's.id_samples')
                    ->where('sm.laboratorium_id', $idlabs)
                    ->whereNull('sm.deleted_at');
                })
                ->where('s.permohonan_uji_id', $sample->permohonan_uji_id)
                ->where('s.typesample_samples', $sample->typesample_samples)
                ->whereNull('s.deleted_at')
                ->pluck('s.id_samples')
                ->unique()
                ->values();

              foreach ($groupSampleIds as $sampleId) {
                $existingLabNums = LabNum::where('sample_id', $sampleId)
                  ->where('lab_id', $idlabs)
                  ->get();

                if ($existingLabNums->isEmpty()) {
                  LabNum::create([
                    'sample_id'         => $sampleId,
                    'sample_type_id'    => $sample->typesample_samples,
                    'lab_id'            => $idlabs,
                    'permohonan_uji_id' => $sample->permohonan_uji_id,
                    'lab_number'        => $assignedRecord->nomer_lab,
                    'year_lab_num'      => $assignedRecord->year ?? (int) date('Y'),
                    'mount_lab_num'     => (int) date('m'),
                  ]);
                } else {
                  foreach ($existingLabNums as $existingLabNum) {
                    $existingLabNum->lab_number   = $assignedRecord->nomer_lab;
                    $existingLabNum->year_lab_num = $assignedRecord->year ?? (int) date('Y');
                    $existingLabNum->save();
                  }
                }
              }
            }
          }
        } catch (\Throwable $e) {
          \Log::error('Gagal assign nomer_lab kesmas per-lab: ' . $e->getMessage(), [
            'sample_id'      => $id,
            'lab_id'         => $idlabs,
            'sample_type_id' => $sample->typesample_samples ?? null,
          ]);
        }
      }
    }

    // Redirect ke halaman tampil laporan
    return redirect()->route('elits-laporan-hasil.tampil', [$id, $idlabs])->with(['status' => 'Pengesahan Hasil berhasil disimpan!']);
  }

  /**
   * Tampilkan laporan hasil PDF
   *
   * @param  string  $id
   * @param  string  $idlab
   * @return \Illuminate\Http\Response
   */
  public function tampilLaporan($id, $idlab)
  {
    Carbon::setLocale('id');

    $user = Auth()->user();
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_laboratorium.*', 'ms_sample_type.*')
      ->first();

    if (!$sample) {
      abort(404, 'Sample tidak ditemukan');
    }

    return view('masterweb::module.admin.laboratorium.laporan-hasil.tampil_laporan', compact('sample', 'user'));
  }

  /**
   * Simpan pengaturan tampilan hasil (font, line height, padding, kop) dari halaman Laporan Hasil.
   * Menyimpan ke field yang sama dengan baca-hasil agar cetak LHU konsisten.
   */
  public function saveLaporanHasilSetting(Request $request, $id, $idlab)
  {
    $validated = $request->validate([
      'fontsize' => 'required|numeric|min:6|max:20',
      'line_height' => 'required|numeric|min:0.5|max:3',
      'padding' => 'required|numeric|min:0|max:16',
      'show_kop' => 'nullable|in:0,1,true,false,on,off'
    ]);

    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->select('tb_samples.*')
      ->first();

    if (!$sample) {
      return response()->json([
        'status' => false,
        'pesan' => 'Data sampel tidak ditemukan.'
      ], 404);
    }

    $showKopRaw = $request->input('show_kop', 1);
    $showKop = ($showKopRaw === '0' || $showKopRaw === 0 || $showKopRaw === 'false' || $showKopRaw === 'off') ? 0 : 1;

    $sample->fontsize_hasil_baca_hasil = (float) $validated['fontsize'];
    $sample->line_height_hasil_baca_hasil = (float) $validated['line_height'];
    $sample->padding_hasil_baca_hasil = (float) $validated['padding'];
    $sample->show_kop_hasil_baca_hasil = $showKop;
    $sample->save();

    return response()->json([
      'status' => true,
      'pesan' => 'Pengaturan hasil berhasil disimpan.',
      'data' => [
        'fontsize' => $sample->fontsize_hasil_baca_hasil,
        'line_height' => $sample->line_height_hasil_baca_hasil,
        'padding' => $sample->padding_hasil_baca_hasil,
        'show_kop' => $sample->show_kop_hasil_baca_hasil
      ]
    ]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //


  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $auth = Auth()->user();

    $customer = Customer::where('id_customer', $id)->first();

    $categories = Industry::all();


    return view('masterweb::module.admin.laboratorium.customer.edit', compact('customer', 'auth', 'categories', 'id'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update($id, Request $request)
  {


    // print_r($data);



    $data = $request->all();
    $customer = Customer::find($id);
    $customer->name_customer = $request->post('name_customer');
    $customer->address_customer = $request->post('address_customer');
    $customer->email_customer = $request->post('email_customer');
    $customer->category_customer = $request->post('category_customer');
    $customer->cp_customer = $request->post('cp_customer');
    $customer->save();

    return redirect()->route('elits-customers.index')->with(['status' => 'Customer succesfully updated']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $customer = Customer::findOrFail($id);
    $customer->delete();
    return redirect()->route('elits-customers.index')->with('status', 'Data berhasil dihapus');
  }
}