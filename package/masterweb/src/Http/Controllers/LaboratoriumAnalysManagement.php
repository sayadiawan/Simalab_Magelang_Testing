<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Smt\Masterweb\Models\Customer;
use Smt\Masterweb\Models\Industry;
use Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\SamplesMethod;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;


class LaboratoriumAnalysManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    //get auth user
    //$methods = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
    //get auth user




    $user = Auth()->user();
    $userLevel = $user->getlevel->level ?? null;
    $lab = Auth()->user()->laboratorium()->first();

    $methods = Method::all();

    return view('masterweb::module.admin.laboratorium.analys.list', compact('methods', 'user', 'userLevel'));

    // return view('masterweb::module.admin.laboratorium.analys.pagination');


  }

  public function index_klinik()
  {
    $data = PermohonanUjiKlinik2::orderBy('created_at', 'desc')->get();
    $listVerifications = [];

    foreach ($data as $item) {
        $latestVerificationActivitySample = VerificationActivitySample::where('is_klinik', $item->id_permohonan_uji_klinik)
            ->where('is_done', true)
            ->join('ms_verification_activities', 'tb_verification_activity_samples.id_verification_activity', '=', 'ms_verification_activities.id')
            ->orderBy('tb_verification_activity_samples.created_at', 'desc')
            ->first();

        if ($latestVerificationActivitySample) {
            $listVerifications[$item->id_permohonan_uji_klinik] = $latestVerificationActivitySample;
        }
    }

    return view('masterweb::module.admin.laboratorium.analys.list_klinik', compact('data', 'listVerifications'));

  }



  public function getSamplePagination(Request $request)
  {

    $limit_val = $request->input('length');
    $start = $request["start"];
    $search = $request["search"];
    $request["start"] = 0;
    $lab = Auth()->user()->laboratorium()->first();

    $result = $this->loadDataTableSample($limit_val, $start, $search, $lab);




    $datas = $result["datas"];
    $data_table = Datatables::of($datas)
      ->filter(function ($instance) use ($request) {
        if (!empty($request->get('search'))) {
          $instance->collection = $instance->collection->filter(function ($row) use ($request) {
            if (Str::contains(Str::lower($row['codesample_samples']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['code_permohonan_uji']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['name_sample_type']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['last_status']), Str::lower($request->get('search')))) {
              return true;
            }

            return false;
          });
        }
      })

      ->addColumn('codesample_samples', function ($data) {
        return $data->codesample_samples;
      })
      ->addColumn('nama_laboratorium', function ($data) {
        return $data->nama_laboratorium;
      })
      ->addColumn('pelanggan', function ($data) {
        return $data instanceof Sample ? $data->namaPelangganDisplay() : (optional(optional($data->permohonanuji)->customer)->name_customer ?? '-');
      })
      ->addColumn('name_sample_type', function ($data) {
        return $data->name_sample_type;
      })
      ->addColumn("last_status", function ($data) {

        $verificationActivities = VerificationActivitySample::query()->where('id_sample', '=', $data->id_samples)->join('ms_verification_activities', 'tb_verification_activity_samples.id_verification_activity', '=', 'ms_verification_activities.id')->get();
        $label_status = '';
        $step = [];
        
        if ($verificationActivities->isNotEmpty()) {
          foreach ($verificationActivities as $verificationActivity) {
            $step[$verificationActivity->id_verification_activity] = $verificationActivity;
          }
            
          if (!empty($step)) {
            // Urutan step yang benar: 1 -> 6 -> 7 -> 2 -> 3 -> 4 -> 5
            $stepOrder = [1, 6, 7, 2, 3, 4, 5];
            
            // Cari step terakhir yang sudah selesai berdasarkan urutan yang benar
            $lastStep = null;
            foreach ($stepOrder as $stepId) {
              if (isset($step[$stepId])) {
                $lastStep = $stepId;
              }
            }
            
            // Jika ada step terakhir, tampilkan status berdasarkan step tersebut
            if ($lastStep !== null) {
              switch ($lastStep) {
                case 1:
                  $label_status = '<label class="badge badge-primary badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                case 6:
                  $label_status = '<label class="badge badge-secondary badge-pill w-75">Pengambilan Sampel</label>';
                  break;
                case 7:
                  $label_status = '<label class="badge badge-dark badge-pill w-75">Penerimaan Sampel</label>';
                  break;
                case 2:
                  $label_status = '<label class="badge badge-warning badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                case 3:
                  $label_status = '<label class="badge badge-danger badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                case 4:
                  $label_status = '<label class="badge badge-info badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                case 5:
                  $label_status = '<label class="badge badge-success badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                default:
                  break;
              }
            }
          }
        }
//        $penerimaan_sample = PenerimaanSample::where('sample_id', $data->id_samples)->where('laboratorium_id', $data->id_laboratorium)->first();
//        $penanganan_sample = PenangananSample::where('sample_id', $data->id_samples)->where('laboratorium_id', $data->id_laboratorium)->first();
//        $pelaporan_hasil = PelaporanHasil::where('sample_id', $data->id_samples)->where('laboratorium_id', $data->id_laboratorium)->first();
//        $verifikasi_hasil = VerifikasiHasil::where('sample_id', $data->id_samples)->where('laboratorium_id', $data->id_laboratorium)->first();
//        $pengesahan_hasil = PengesahanHasil::where('sample_id', $data->id_samples)->where('laboratorium_id', $data->id_laboratorium)->first();
//
//
//
//        $laboratoriumsampleanalitikprogress = SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id', $data->id_laboratorium)
//          ->where('tb_sample_analitik_progress.sample_id', $data->id_samples)
//          ->orderBy('tb_laboratorium_progress.order_sort', 'desc')
//          ->join('tb_laboratorium_progress', function ($join) {
//            $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
//              ->whereNull('tb_laboratorium_progress.deleted_at')
//              ->whereNull('tb_sample_analitik_progress.deleted_at');
//          })
//          ->first();
//
//        $laboratorium_fase_analitik = \Smt\Masterweb\Models\LaboratoriumProgress::where('laboratorium_id', $data->id_laboratorium)->orderBy('order_sort', 'asc')->get();
//
//        $label_status = '';
//
//        if (isset($laboratoriumsampleanalitikprogress)) {
//          if ($laboratoriumsampleanalitikprogress->link == 'baca-hasil' && $laboratoriumsampleanalitikprogress->date_done != null) {
//            if (isset($pengesahan_hasil->pengesahan_hasil_date)) {
//              $label_status = '<label class="badge badge-success badge-pill">Selesai</label>';
//            } else if (isset($verifikasi_hasil->verifikasi_hasil_date)) {
//              $label_status = '<label class="badge badge-success badge-pill">Pengesahan Hasil</label>';
//            } else if (isset($pelaporan_hasil->pelaporan_hasil_date)) {
//              $label_status = '<label class="badge badge-success badge-pill">Verifikasi Hasil</label>';
//            } else {
//              $label_status = '<label class="badge badge-success badge-pill">Pelaporan Hasil</label>';
//            }
//          } else {
//            if (isset($laboratoriumsampleanalitikprogress)) {
//              if ($laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort) - 1]->link == "baca-hasil" && !isset($laboratoriumsampleanalitikprogress->date_done)) {
//                $label_status = '<label class="badge badge-success badge-pill">' . $laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort) - 1]->name_progress . '</label>';
//              } else {
//                if ($laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->link == "baca-hasil") {
//                  $label_status = '<label class="badge badge-success badge-pill">' . $laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->name_progress . '</label>';
//                } else {
//                  $label_status = '<label class="badge badge-primary badge-pill">' . $laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->name_progress . '</label>';
//                }
//              }
//            } else {
//              if (isset($penanganan_sample->penanganan_sample_date)) {
//                $label_status = '<label class="badge badge-primary badge-pill">' . $laboratorium_fase_analitik[0]->name_progress . '</label>';
//              } else if (isset($penerimaan_sample->penerimaan_sample_date)) {
//                $label_status = '<label class="badge badge-warning badge-pill">Penanganan Sampel</label>';
//              } else if (isset($data->date_sending)) {
//                $label_status = '<label class="badge badge-warning badge-pill">Penanganan Sampel</label>';
//              }
//            }
//          }
//        } else {
//          if (isset($laboratoriumsampleanalitikprogress)) {
//            if ($laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->link == "baca-hasil") {
//              $label_status = '<label class="badge badge-success badge-pill">' . $laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->name_progress . '</label>';
//            } else {
//              $label_status = '<label class="badge badge-primary badge-pill">' . $laboratorium_fase_analitik[((int)$laboratoriumsampleanalitikprogress->order_sort)]->name_progress . '</label>';
//            }
//          } else {
//            if (isset($data->penanganan_sample_date)) {
//              $label_status = '<label class="badge badge-primary badge-pill">' . $laboratorium_fase_analitik[0]->name_progress . '</label>';
//            } else if (isset($data->penerimaan_sample_date)) {
//              $label_status = '<label class="badge badge-warning badge-pill">Penanganan Sampel</label>';
//            } else if (isset($data->date_sending)) {
//              $label_status = '<label class="badge badge-warning badge-pill">Penanganan Sampel</label>';
//            }
//          }
//        }

        return $label_status;
      })
      ->addColumn('date_sending', function ($data) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_sending)->isoFormat('D MMMM Y');
      })





      ->addColumn('action', function ($data) {
        $editButton = '';
        $draftButton = '';
        $verificationButton = '';

        if (!empty($data->permohonan_uji_id)) {
          $draftButton = '<a href="' . route('elits-sample-draft.index', [$data->permohonan_uji_id]) . '" title="Kelola Draft Sampel"> <button type="button" class="btn btn-outline-info btn-rounded btn-icon" style="margin-right: 4px;">
          <i class="fas fa-file-alt"></i>
      </button></a> ';
        }

        if (getAction('update')) {
          $editButton = '<a href="' . route('elits-samples.edit', [$data->id_samples]) . '" title="Edit Sampel"> <button type="button" class="btn btn-outline-warning btn-rounded btn-icon" style="margin-right: 4px;">
          <i class="fas fa-pencil-alt"></i>
      </button></a> ';
        }

        if (getSpesialAction('elits-samples', 'verification-sampel', '')) {
          $verificationButton = '<a href="' . route('elits-samples.verification-2', [$data->id_samples, $data->id_laboratorium]) . '" title="Verifikasi"> <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
          <i class="fa fa-check"></i>
      </button></a> ';
        }

        $button = $draftButton . $editButton . $verificationButton;

        return trim($button);
      })

      ->rawColumns(['codesample_samples', 'nama_laboratorium', 'name_sample_type', 'last_status', 'action', 'date_sending'])
      ->setFilteredRecords($result["totalFilteredRecord"])
      ->setTotalRecords($result["totalDataRecord"])
      ->make(true);
    return $data_table;
  }

  public function loadDataTableSearch($datas, $search)
  {
    $datas = $datas->where(function ($query) use ($search) {
      $query->where('tb_samples.codesample_samples', 'like', "%{$search}%")
        // ->Orwhere('ms_customer.name_customer', 'like', "%{$search}%")
        ->Orwhere('tb_permohonan_uji.code_permohonan_uji', 'like', "%{$search}%")

        ->Orwhere('ms_sample_type.name_sample_type', 'like', "%{$search}%");
    });
    return $datas;
  }

  public function loadDataTableSample($limit_val, $start, $search, $lab = null)
  {
    $totalFilteredRecord = $totalDataRecord = "";
    $totalFilteredRecord =
      Sample::
      // where('date_done', NULL)
      // ->
      orderBy('tb_samples.created_at', 'desc')
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
      ->select('ms_laboratorium.id_laboratorium', 'tb_permohonan_uji.code_permohonan_uji', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*')
      // ->select('ms_laboratorium.id_laboratorium', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*', 'tb_sample_penerimaan.*', 'tb_sample_penanganan.*', 'tb_pengesahan_hasil.*', 'tb_verifikasi_hasil.*', 'tb_pelaporan_hasil.*')
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      // ->leftjoin('tb_sample_penerimaan', function ($join) {
      //   $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penerimaan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_sample_penanganan', function ($join) {
      //   $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penanganan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pelaporan_hasil', function ($join) {
      //   $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
      //     // $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
      //     // ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
      //     ->whereNull('tb_pelaporan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      // ->leftjoin('tb_verifikasi_hasil', function ($join) {
      //   $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_verifikasi_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pengesahan_hasil', function ($join) {
      //   $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_pengesahan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      ->orderBy('tb_samples.created_at', 'DESC')
      ->distinct('tb_samples.id_samples')
      ->limit($limit_val);

    if (isset($lab)) {
      $totalFilteredRecord = $totalFilteredRecord->where('id_laboratorium', '=', $lab->id_laboratorium);
    }

    $totalFilteredRecord = $this->loadDataTableSearch($totalFilteredRecord, $search);
    $totalFilteredRecord = $totalFilteredRecord->count();

    $totalDataRecord =
      Sample::
      // where('date_done', NULL)
      // ->
      orderBy('tb_samples.created_at', 'desc')
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
      ->select('ms_laboratorium.id_laboratorium', 'tb_permohonan_uji.code_permohonan_uji', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*')
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      // ->leftjoin('tb_sample_penerimaan', function ($join) {
      //   $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penerimaan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_sample_penanganan', function ($join) {
      //   $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penanganan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pelaporan_hasil', function ($join) {
      //   $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
      //     // $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
      //     // ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
      //     ->whereNull('tb_pelaporan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_verifikasi_hasil', function ($join) {
      //   $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_verifikasi_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pengesahan_hasil', function ($join) {
      //   $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_pengesahan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      ->distinct('tb_samples.id_samples');



    if (isset($lab)) {
      $totalDataRecord = $totalDataRecord->where('id_laboratorium', '=', $lab->id_laboratorium);
    }


    $totalDataRecord = $this->loadDataTableSearch($totalDataRecord, $search);


    $totalDataRecord = $totalDataRecord->count();





    $totalFilteredRecord = $totalDataRecord;

    $datas =
      Sample::where('date_done', NULL)
      ->orderBy('tb_samples.created_at', 'desc')
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
      ->select('ms_laboratorium.id_laboratorium', 'tb_permohonan_uji.code_permohonan_uji', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*')
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      // ->leftjoin('tb_sample_penerimaan', function ($join) {
      //   $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penerimaan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_sample_penanganan', function ($join) {
      //   $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_sample_penanganan.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pelaporan_hasil', function ($join) {
      //   $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
      //     // $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
      //     // ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
      //     ->whereNull('tb_pelaporan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_verifikasi_hasil', function ($join) {
      //   $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_verifikasi_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      // ->leftjoin('tb_pengesahan_hasil', function ($join) {
      //   $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

      //     ->whereNull('tb_pengesahan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })
      ->offset($start)
      ->distinct('tb_samples.id_samples')
      ->limit($limit_val)
      ->orderBy('tb_samples.created_at', 'DESC');



    if (isset($lab)) {
      $datas = $datas->where('id_laboratorium', '=', $lab->id_laboratorium);
    }
    $datas = $this->loadDataTableSearch($datas, $search);
    $datas = $datas->get();

    $no = (int) $start + 1;
    $i = 0;
    foreach ($datas as $data) {
      $datas[$i]["nomer"] = $no;
      $no++;
      $i++;
    }
    $result["totalFilteredRecord"] = $totalFilteredRecord;
    $result["totalDataRecord"] = $totalDataRecord;
    $result["datas"] = $datas;

    return $result;
  }





  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //get auth user




    $user = Auth()->user();
    $count = Sample::count();
    $users = User::all();

    $code = 'KS.CLCP-' . date("Ymd", time()) . '-0' . ($count + 1);


    return view('masterweb::module.admin.laboratorium.sample.add', compact('user', 'code', 'users'));
    //get all menu public
  }


  public function analys($id, $id_method = null)
  {

    $auth = Auth()->user();

    $samples = Sample::where("id_samples", $id)->first();
    $customer = Customer::where("id_customer", $samples->customer_samples)->first();
    $method = SamplesMethod::join('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
      ->join('tb_delegation', function ($join) {
        $join->on('tb_delegation.id_method', '=', 'ms_samples_method.id_method');
        $join->on('tb_delegation.id_samples', '=', 'ms_samples_method.id_samples');
      })
      ->where("ms_samples_method.id_samples", $id)
      ->where('tb_delegation.id_delegation', '=', $auth->id)
      ->where('ms_method.deleted_at', '=', null)
      ->where('tb_delegation.deleted_at', '=', null)
      ->select('ms_samples_method.*', 'ms_method.*', 'tb_delegation.*')
      ->get();
    // $method = SamplesMethod::where("id_samples",$id)
    // ->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
    // ->where('ms_method.deleted_at', '=', null)->get();


    return view('masterweb::module.admin.laboratorium.sample.analys', compact('samples', 'method', 'customer', 'id_method'));
  }




  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $request->all();

    // print_r($data);


    $user = Auth()->user();
    $sample = new Sample;
    //uuid
    $uuid4 = Uuid::uuid4();

    $datesampling_samples = Carbon::createFromFormat('d/m/Y', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
    $datelab_samples = Carbon::createFromFormat('d/m/Y', $request->post('datelab_samples'))->format('Y-m-d H:i:s');

    $sample->id_samples = $uuid4->toString();
    $sample->codesample_samples = $request->post('code_samples');
    $sample->customer_samples = $request->post('customerAttributes');

    if (!$request->post('issend_samples') == "on") {
      $sample->sender_samples = $request->post('sender_samples');
      $sample->issend_samples = 0;
    } else {
      $sample->issend_samples = 1;
    }
    $sample->cost_samples = $request->post('cost_samples');
    $sample->typesample_samples = $request->post('typesample_samples');
    $sample->user_input_samples = $user->id;
    $sample->wadah_samples = $request->post('wadah_samples');
    $sample->amount_samples = $request->post('amount_samples');
    $sample->unit_samples = $request->post('unitAttributes');


    $sample->poinsample_samples = $request->post('poinsample_samples');
    $sample->datesampling_samples = $datesampling_samples;
    $sample->datelab_samples = $datelab_samples;

    $sample->save();


    if (isset($data["methodAttributes"])) {
      for ($i = 0; $i < count($data["methodAttributes"]); $i++) {
        $method = new SamplesMethod;
        $method->id = Uuid::uuid4();
        $method->id_samples = $sample->id_samples;
        $method->id_method = $data["methodAttributes"][$i];
        $method->save();
      }
    }


    return response()->json(
      [
        'success' => 'Ajax request submitted successfully',
        'data' => $data,
      ]
    );
    //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);

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
   * Get statistics for analys lists page
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function getStatisticsAnalys(Request $request)
  {
    $user = auth()->user();
    $lab = $user->laboratorium()->first();

    // Base query - semua sample
    $baseQuery = Sample::query();
    
    if ($lab) {
      $baseQuery->whereHas('samplemethod', function($q) use ($lab) {
        $q->where('laboratorium_id', $lab->id_laboratorium);
      });
    }

    $sampleIds = $baseQuery->pluck('id_samples')->toArray();

    // Hitung statistik per step berdasarkan id_verification_activity dengan urutan 1-6-7-2-3-4-5
    // Logika: Step sebelumnya sudah is_done = 1, step tersebut is_done = 0 atau belum ada
    $stats = [
      'belum_pemeriksaan' => 0, // Step 1 belum selesai atau belum ada
      'pengambilan_sample' => 0, // Step 1 selesai, Step 6 belum selesai atau belum ada (optional)
      'penerimaan_sample' => 0, // Step 1, 6 selesai, Step 7 belum selesai atau belum ada
      'pemeriksaan' => 0, // Step 1, 6, 7 selesai, Step 2 belum selesai atau belum ada
      'input_hasil' => 0, // Step 1, 6, 7, 2 selesai, Step 3 belum selesai atau belum ada
      'verifikasi' => 0, // Step 1, 6, 7, 2, 3 selesai, Step 4 belum selesai atau belum ada
      'validasi' => 0, // Step 1, 6, 7, 2, 3, 4 selesai, Step 5 belum selesai atau belum ada
      'selesai' => 0, // Step 1, 6, 7, 2, 3, 4, 5 selesai
    ];

    if (empty($sampleIds)) {
      return response()->json($stats);
    }

    // Get semua verification activities untuk sample ini
    $verificationActivities = DB::table('tb_verification_activity_samples')
      ->whereIn('id_sample', $sampleIds)
      ->select('id_sample', 'id_verification_activity', 'is_done')
      ->get()
      ->groupBy('id_sample')
      ->map(function ($group) {
        $result = [];
        foreach ($group as $item) {
          $result[$item->id_verification_activity] = $item->is_done;
        }
        return $result;
      });

    // Ambil info is_sampling dari tb_samples & tb_permohonan_uji
    $sampleInfo = DB::table('tb_samples')
      ->leftJoin('tb_permohonan_uji', 'tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
      ->whereIn('tb_samples.id_samples', $sampleIds)
      ->select('tb_samples.id_samples', 'tb_samples.is_sampling as sample_is_sampling', 'tb_permohonan_uji.is_sampling as permohonan_is_sampling')
      ->get()
      ->keyBy('id_samples');

    // Hitung statistik per step dengan urutan 1-6-7-2-3-4-5
    foreach ($sampleIds as $id) {
      $activities = $verificationActivities->get($id, []);
      $sInfo = $sampleInfo->get($id);
      if ($sInfo) {
        if ($sInfo->permohonan_is_sampling !== null && (int) $sInfo->permohonan_is_sampling === 0) {
          $isSampling = 0;
        } elseif ($sInfo->sample_is_sampling !== null && (int) $sInfo->sample_is_sampling === 0) {
          $isSampling = 0;
        } else {
          $isSampling = $sInfo->sample_is_sampling !== null ? (int) $sInfo->sample_is_sampling : (int) ($sInfo->permohonan_is_sampling ?? 1);
        }
      } else {
        $isSampling = 1;
      }
 
      // Cek status setiap step (1 = sudah done, 0 = belum done, null = belum ada)
      $step1Done = isset($activities[1]) && $activities[1] == 1;
      $step6Done = isset($activities[6]) && $activities[6] == 1;
      $step6NotDone = isset($activities[6]) && $activities[6] == 0;
      $step6Exists = isset($activities[6]);
      
      $step7Done = isset($activities[7]) && $activities[7] == 1;
      $step7NotDone = isset($activities[7]) && $activities[7] == 0;
      $step7Exists = isset($activities[7]);
      
      $step2Done = isset($activities[2]) && $activities[2] == 1;
      $step2NotDone = isset($activities[2]) && $activities[2] == 0;
      $step2Exists = isset($activities[2]);
      
      $step3Done = isset($activities[3]) && $activities[3] == 1;
      $step3NotDone = isset($activities[3]) && $activities[3] == 0;
      $step3Exists = isset($activities[3]);

      $step4Done = isset($activities[4]) && $activities[4] == 1;
      $step4NotDone = isset($activities[4]) && $activities[4] == 0;
      $step4Exists = isset($activities[4]);
      
      $step5Done = isset($activities[5]) && $activities[5] == 1;
      $step5NotDone = isset($activities[5]) && $activities[5] == 0;
      $step5Exists = isset($activities[5]);
      
      // Urutan pengecekan hierarki dari tahap akhir ke awal
      // Selesai: Step 5 sudah selesai
      if ($step5Done) {
        $stats['selesai']++;
      }
      // Validasi: Step 4 sudah selesai, Step 5 belum selesai
      elseif ($step4Done) {
        $stats['validasi']++;
      }
      // Verifikasi: Step 3 sudah selesai, Step 4 belum selesai
      elseif ($step3Done) {
        $stats['verifikasi']++;
      }
      // Input Hasil: Step 2 sudah selesai, Step 3 belum selesai
      elseif ($step2Done) {
        $stats['input_hasil']++;
      }
      // Pemeriksaan: Step 7 sudah selesai, Step 2 belum selesai
      elseif ($step7Done) {
        $stats['pemeriksaan']++;
      }
      // Penerimaan Sample:
      // Kasus A (is_sampling = 1 / diambil lab): Step 6 sudah selesai, Step 7 belum selesai
      // Kasus B (is_sampling = 0 / dibawa pelanggan): Step 1 sudah selesai, Step 7 belum selesai (langsung ke penerimaan)
      elseif (($step6Done || $isSampling === 0) && $step1Done) {
        $stats['penerimaan_sample']++;
      }
      // Pengambilan Sample: is_sampling = 1, Step 1 selesai, Step 6 belum selesai, dan belum ada step lanjutan
      elseif ($step1Done && $isSampling === 1) {
        $stats['pengambilan_sample']++;
      }
      // Belum Pemeriksaan: Step 1 belum selesai atau belum ada
      else {
        $stats['belum_pemeriksaan']++;
      }
    }

    return response()->json($stats);
  }

  /**
   * Get data for analys lists page with status filter
   *
   * @return \Illuminate\Http\Response
   */
  public function data_analys(Request $request)
  {
    $user = auth()->user();
    $lab = $user->laboratorium()->first();

    // Base query
    $query = Sample::orderBy('tb_samples.created_at', 'desc');
    
    if ($lab) {
      $query->whereHas('samplemethod', function($q) use ($lab) {
        $q->where('laboratorium_id', $lab->id_laboratorium);
      });
    }

    // Apply status filter
    $userLevel = $user && $user->getlevel ? $user->getlevel->level : null;
    
    // Tentukan filter yang diizinkan berdasarkan level
    $allowedFilters = [];
    if (in_array($userLevel, ['ANLS', 'ALAB'])) {
      $allowedFilters = ['penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi'];
    } elseif ($userLevel == 'RGSTR') {
      $allowedFilters = ['all', 'belum_pemeriksaan', 'selesai'];
    } elseif ($userLevel == 'DKTR') {
      $allowedFilters = ['all', 'belum_pemeriksaan', 'validasi', 'selesai'];
    } elseif (\Smt\Masterweb\Helpers\SampleCollectorAccess::isKesmas($userLevel)) {
      $allowedFilters = ['pengambilan_sample'];
    } else {
      $allowedFilters = ['all', 'belum_pemeriksaan', 'pengambilan_sample', 'penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi', 'selesai'];
    }
    
    if ($request->status_filter && $request->status_filter != '') {
      $statusFilter = $request->status_filter;
      
      // Validasi: jika filter tidak diizinkan untuk level user, set ke 'all'
      if (!in_array($statusFilter, $allowedFilters)) {
        $statusFilter = 'all';
      }
      
      if ($statusFilter == 'belum_pemeriksaan') {
        // Step 1 (id_verification_activity = 1) belum selesai atau belum ada
        $query->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 1);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 1)
              ->where('is_done', 0);
          });
        });
      } elseif ($statusFilter == 'pengambilan_sample') {
        // Step 1 selesai, sampel diambil lab (is_sampling = 1), Step 6 (id_verification_activity = 6) belum selesai, dan belum ada step 7, 2, 3, 4, 5
        $query->where(function ($q) {
          $q->where(function ($sub) {
            $sub->where('tb_samples.is_sampling', 1)
              ->whereHas('permohonanuji', function ($pq) {
                $pq->where('is_sampling', '!=', 0)->orWhereNull('is_sampling');
              });
          })->orWhere(function ($sub) {
            $sub->whereNull('tb_samples.is_sampling')
              ->whereHas('permohonanuji', function ($pq) {
                $pq->where('is_sampling', 1);
              });
          });
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereNotExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereNotExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->whereIn('id_verification_activity', [7, 2, 3, 4, 5])
            ->where('is_done', 1);
        });
      } elseif ($statusFilter == 'penerimaan_sample') {
        // Step 1 selesai, Step 7 belum selesai, belum ada step 2, 3, 4, 5
        // DAN: (is_sampling = 1 AND Step 6 selesai) ATAU (is_sampling = 0 / dibawa pelanggan)
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->where(function ($q) {
          // Kasus 1: diambil oleh lab (is_sampling = 1) -> harus sudah step 6
          $q->where(function ($q1) {
            $q1->where(function ($qSampling) {
              $qSampling->where(function ($sub) {
                $sub->where('tb_samples.is_sampling', 1)
                  ->whereHas('permohonanuji', function ($pq) {
                    $pq->where('is_sampling', '!=', 0)->orWhereNull('is_sampling');
                  });
              })->orWhere(function ($sub) {
                $sub->whereNull('tb_samples.is_sampling')
                  ->whereHas('permohonanuji', function ($pq) {
                    $pq->where('is_sampling', 1);
                  });
              });
            })->whereExists(function ($subquery) {
              $subquery->select(DB::raw(1))
                ->from('tb_verification_activity_samples')
                ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
                ->where('id_verification_activity', 6)
                ->where('is_done', 1);
            });
          })
          // Kasus 2: tidak diambil oleh lab (is_sampling = 0) -> langsung ke penerimaan tanpa syarat step 6
          ->orWhere(function ($q2) {
            $q2->where('tb_samples.is_sampling', 0)
              ->orWhereHas('permohonanuji', function ($pq) {
                $pq->where('is_sampling', 0);
              });
          });
        })->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 7);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 7)
              ->where('is_done', 0);
          });
        })->whereNotExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->whereIn('id_verification_activity', [2, 3, 4, 5])
            ->where('is_done', 1);
        });
      } elseif ($statusFilter == 'pemeriksaan') {
        // Step 1, 6, 7 selesai, Step 2 (id_verification_activity = 2) belum selesai atau belum ada
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 7)
            ->where('is_done', 1);
        })->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 2);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 2)
              ->where('is_done', 0);
          });
        });
      } elseif ($statusFilter == 'input_hasil') {
        // Step 1, 6, 7, 2 selesai, Step 3 (id_verification_activity = 3) belum selesai atau belum ada
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 7)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 2)
            ->where('is_done', 1);
        })->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 3);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 3)
              ->where('is_done', 0);
          });
        });
      } elseif ($statusFilter == 'verifikasi') {
        // Step 1, 6, 7, 2, 3 selesai, Step 4 (id_verification_activity = 4) belum selesai atau belum ada
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 7)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 2)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 3)
            ->where('is_done', 1);
        })->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 4);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 4)
              ->where('is_done', 0);
          });
        });
      } elseif ($statusFilter == 'validasi') {
        // Step 1, 6, 7, 2, 3, 4 selesai, Step 5 (id_verification_activity = 5) belum selesai atau belum ada
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 7)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 2)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 3)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 4)
            ->where('is_done', 1);
        })->where(function ($q) {
          $q->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 5);
          })->orWhereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('tb_verification_activity_samples')
              ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
              ->where('id_verification_activity', 5)
              ->where('is_done', 0);
          });
        });
      } elseif ($statusFilter == 'selesai') {
        // Step 1, 6, 7, 2, 3, 4, 5 semua selesai
        $query->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 1)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 6)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 7)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 2)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 3)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 4)
            ->where('is_done', 1);
        })->whereExists(function ($subquery) {
          $subquery->select(DB::raw(1))
            ->from('tb_verification_activity_samples')
            ->whereColumn('tb_verification_activity_samples.id_sample', 'tb_samples.id_samples')
            ->where('id_verification_activity', 5)
            ->where('is_done', 1);
        });
      }
    }

    // Return data untuk DataTables
    return $this->getSamplePaginationFromQuery($query, $request);
  }

  public function getSamplePaginationFromQuery($query, Request $request)
  {
    $limit_val = $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $search = $request->input('search');
    if (is_array($search)) {
      $search = $search['value'] ?? '';
    }

    $query = $query
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
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('ms_laboratorium.id_laboratorium', 'tb_permohonan_uji.code_permohonan_uji', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*')
      ->distinct('tb_samples.id_samples');

    if (!empty($search)) {
      $query = $this->loadDataTableSearch($query, $search);
    }

    $totalFilteredRecord = (clone $query)->count('tb_samples.id_samples');
    $totalDataRecord = $totalFilteredRecord;

    $datas = (clone $query)
      ->offset($start)
      ->limit($limit_val)
      ->get();

    $no = $start + 1;
    $i = 0;
    foreach ($datas as $data) {
      $datas[$i]["nomer"] = $no;
      $no++;
      $i++;
    }

    $data_table = Datatables::of($datas)
      ->addColumn('codesample_samples', function ($data) {
        return $data->codesample_samples;
      })
      ->addColumn('nama_laboratorium', function ($data) {
        return $data->nama_laboratorium;
      })
      ->addColumn('pelanggan', function ($data) {
        return $data instanceof Sample ? $data->namaPelangganDisplay() : (optional(optional($data->permohonanuji)->customer)->name_customer ?? '-');
      })
      ->addColumn('name_sample_type', function ($data) {
        return $data->name_sample_type;
      })
      ->addColumn("last_status", function ($data) {
        $verificationActivities = VerificationActivitySample::query()->where('id_sample', '=', $data->id_samples)->join('ms_verification_activities', 'tb_verification_activity_samples.id_verification_activity', '=', 'ms_verification_activities.id')->get();
        $label_status = '';
        $step = [];

        if ($verificationActivities->isNotEmpty()) {
          foreach ($verificationActivities as $verificationActivity) {
            $step[$verificationActivity->id_verification_activity] = $verificationActivity;
          }

          if (!empty($step)) {
            $stepOrder = [1, 6, 7, 2, 3, 4, 5];
            $lastStep = null;
            foreach ($stepOrder as $stepId) {
              if (isset($step[$stepId])) {
                $lastStep = $stepId;
              }
            }

            if ($lastStep !== null) {
              switch ($lastStep) {
                case 1:
                  $label_status = '<label class="badge badge-primary badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                  break;
                case 6:
                  $label_status = '<label class="badge badge-secondary badge-pill w-75">Pengambilan Sampel</label>';
                  break;
                case 7:
                  $label_status = '<label class="badge badge-dark badge-pill w-75">Penerimaan Sampel</label>';
                  break;
                case 2:
                  $label_status = '<label class="badge badge-info badge-pill w-75">Pemeriksaan Sampel</label>';
                  break;
                case 3:
                  $label_status = '<label class="badge badge-warning badge-pill w-75">Input Hasil</label>';
                  break;
                case 4:
                  $label_status = '<label class="badge badge-danger badge-pill w-75">Verifikasi</label>';
                  break;
                case 5:
                  $label_status = '<label class="badge badge-success badge-pill w-75">Validasi</label>';
                  break;
                default:
                  $label_status = '<label class="badge badge-success badge-pill w-75">Selesai</label>';
                  break;
              }
            } else {
              $label_status = '<label class="badge badge-primary badge-pill w-75">Pendaftaran / Registrasi</label>';
            }
          }
        } else {
          $label_status = '<label class="badge badge-primary badge-pill w-75">Pendaftaran / Registrasi</label>';
        }

        return $label_status;
      })
      ->addColumn('date_sending', function ($data) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_sending)->isoFormat('D MMMM Y');
      })
      ->addColumn('action', function ($data) {
        $editButton = '';
        $draftButton = '';
        $verificationButton = '';

        if (!empty($data->permohonan_uji_id)) {
          $draftButton = '<a href="' . route('elits-sample-draft.index', [$data->permohonan_uji_id]) . '" title="Kelola Draft Sampel"> <button type="button" class="btn btn-outline-info btn-rounded btn-icon" style="margin-right: 4px;">
          <i class="fas fa-file-alt"></i>
      </button></a> ';
        }

        if (getAction('update')) {
          $editButton = '<a href="' . route('elits-samples.edit', [$data->id_samples]) . '" title="Edit Sampel"> <button type="button" class="btn btn-outline-warning btn-rounded btn-icon" style="margin-right: 4px;">
          <i class="fas fa-pencil-alt"></i>
      </button></a> ';
        }

        if (getSpesialAction('elits-samples', 'verification-sampel', '')) {
          $verificationButton = '<a href="' . route('elits-samples.verification-2', [$data->id_samples, $data->id_laboratorium]) . '" title="Verifikasi"> <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
          <i class="fa fa-check"></i>
      </button></a> ';
        }

        $button = $draftButton . $editButton . $verificationButton;

        return trim($button);
      })
      ->rawColumns(['codesample_samples', 'nama_laboratorium', 'name_sample_type', 'last_status', 'action', 'date_sending'])
      ->setFilteredRecords($totalFilteredRecord)
      ->setTotalRecords($totalDataRecord)
      ->make(true);

    return $data_table;
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