<?php

namespace Smt\Masterweb\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PDF;
use Mapper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Unit;
use \Smt\Masterweb\Models\User;
use Yajra\DataTables\DataTables;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\Library;
use \Smt\Masterweb\Models\BakuMutu;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Container;
use App\Http\Controllers\Controller;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\JenisMakanan;
use \Smt\Masterweb\Models\Laboratorium;


use \Smt\Masterweb\Models\SampleMethod;

use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\PenerimaanSample;

use SimpleSoftwareIO\QrCode\Facades\QrCode;


use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\BakuMutuDetailParameterNonKlinik;


class LaboratoriumBakuMutuKimiaManagement extends Controller
{
  private function resolveLibraryId($libraryInput)
  {
    if (empty($libraryInput)) {
      return null;
    }

    if (is_string($libraryInput) && str_starts_with($libraryInput, 'new::')) {
      $libraryTitle = trim(substr($libraryInput, strlen('new::')));
      if ($libraryTitle === '') {
        return null;
      }

      $library = Library::withTrashed()->where('title_library', $libraryTitle)->first();
      if (!$library) {
        $library = new Library();
        $library->title_library = $libraryTitle;
        $library->save();
      } elseif ($library->deleted_at) {
        $library->restore();
      }

      return $library->id_library;
    }

    return $libraryInput;
  }

  private function resolveUnitId($unitInput)
  {
    if (empty($unitInput)) {
      return null;
    }

    if ($unitInput === '-') {
      return $unitInput;
    }

    if (is_string($unitInput) && str_starts_with($unitInput, 'new::')) {
      $unitShortname = trim(substr($unitInput, strlen('new::')));
      if ($unitShortname === '') {
        return null;
      }

      $unit = Unit::withTrashed()->where('shortname_unit', $unitShortname)->first();
      if (!$unit) {
        $unit = new Unit();
        $unit->shortname_unit = $unitShortname;
        $unit->save();
      } elseif ($unit->deleted_at) {
        $unit->restore();
      }

      return $unit->id_unit;
    }

    return $unitInput;
  }

  private function isMakananMinumanLainnyaSampleType($sampletypeId)
  {
    if (empty($sampletypeId)) {
      return false;
    }

    $sampleType = SampleType::find($sampletypeId);
    if (!$sampleType || empty($sampleType->name_sample_type)) {
      return false;
    }

    $sampleTypeName = $sampleType->name_sample_type;
    return str_contains($sampleTypeName, 'Makanan')
      || str_contains($sampleTypeName, 'Minuman')
      || str_contains($sampleTypeName, 'Lainnya');
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
  public function index(Request $request)
  {
    Carbon::setLocale('id');
    $labIdKimia = '3416ca19-6c69-4e5f-a004-ae8275de7644';

    $bakuSampleTypeIds = DB::table('tb_baku_mutu')
      ->where('lab_id', $labIdKimia)
      ->whereNull('deleted_at')
      ->whereNotNull('sampletype_id')
      ->distinct()
      ->pluck('sampletype_id');

    $sample_types_for_filter = $bakuSampleTypeIds->isEmpty()
      ? collect()
      : SampleType::query()
        ->whereIn('id_sample_type', $bakuSampleTypeIds->all())
        ->whereNull('deleted_at')
        ->orderBy('name_sample_type')
        ->get();

    $paramsByJenisSample = [];
    $paramsAll = [];
    $pairsKimia = DB::table('tb_baku_mutu as bm')
      ->join('ms_sample_type as st', function ($join) {
        $join->on('st.id_sample_type', '=', 'bm.sampletype_id')
          ->whereNull('st.deleted_at');
      })
      ->join('ms_method as m', function ($join) {
        $join->on('m.id_method', '=', 'bm.method_id')
          ->whereNull('m.deleted_at');
      })
      ->where('bm.lab_id', $labIdKimia)
      ->whereNull('bm.deleted_at')
      ->whereNotNull('bm.sampletype_id')
      ->whereNotNull('bm.method_id')
      ->select('st.name_sample_type', 'm.params_method')
      ->orderBy('m.params_method')
      ->get();

    foreach ($pairsKimia as $row) {
      $jn = $row->name_sample_type;
      $pm = $row->params_method;
      if ($jn === null || $pm === null || $pm === '') {
        continue;
      }
      if (!isset($paramsByJenisSample[$jn])) {
        $paramsByJenisSample[$jn] = [];
      }
      if (!in_array($pm, $paramsByJenisSample[$jn], true)) {
        $paramsByJenisSample[$jn][] = $pm;
      }
      if (!in_array($pm, $paramsAll, true)) {
        $paramsAll[] = $pm;
      }
    }
    foreach ($paramsByJenisSample as $k => $arr) {
      sort($paramsByJenisSample[$k], SORT_NATURAL | SORT_FLAG_CASE);
    }
    sort($paramsAll, SORT_NATURAL | SORT_FLAG_CASE);

    $sample_methods = collect($paramsAll)->map(function ($p) {
      return (object) ['params_method' => $p];
    });

    $query = BakuMutu::where('tb_baku_mutu.lab_id', '=', '3416ca19-6c69-4e5f-a004-ae8275de7644')
      ->orderBy('tb_baku_mutu.created_at')
      ->leftjoin('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_baku_mutu.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->leftjoin('ms_library', function ($join) {
        $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
          ->whereNull('ms_library.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->leftjoin('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_baku_mutu.sampletype_id')
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->leftjoin('ms_jenis_makanan', function ($join) {
        $join->on('ms_jenis_makanan.id_jenis_makanan', '=', 'tb_baku_mutu.jenis_makanan_id')
          ->whereNull('ms_jenis_makanan.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->select(
        'tb_baku_mutu.*',
        'ms_method.params_method',
        'ms_method.id_method',
        'ms_library.title_library',
        'ms_sample_type.name_sample_type',
        'ms_jenis_makanan.name_jenis_makanan'
      );

    if ($request->has('search') && !empty($request->get('search'))) {
      $query->where(function ($query) use ($request) {
        $search = $request->get('search');
        $query->where('ms_sample_type.name_sample_type', 'like', "%$search%")
          ->orWhere('ms_method.params_method', 'like', "%$search%")
          ->orWhere('ms_jenis_makanan.name_jenis_makanan', 'like', "%$search%");
      });
    }

    if ($request->has('jenis_sample') && !empty($request->get('jenis_sample'))) {
      $query->where('ms_sample_type.name_sample_type', $request->get('jenis_sample'));
    }
    if ($request->has('parameter') && !empty($request->get('parameter'))) {
      $query->where('ms_method.params_method', $request->get('parameter'));
    }

    $datas = $query->get();

    if ($request->ajax()) {
      return DataTables::of($datas)
        ->addColumn('action', function ($data) {
          $editButton = getAction('update') ? '<a href="' . route('elits-baku-mutu-kimia.edit', [$data->id_baku_mutu]) . '" class="dropdown-item" data-toggle="tooltip" data-custom-class="tooltip-info" data-placement="top" title="Edit Data">Edit</a>' : '';
          $deleteButton = getAction('delete') ? '<a class="dropdown-item btn-hapus" href="javascript:void(0)" data-id="' . $data->id_baku_mutu . '" data-nama="' . $data->name_sample_type . ' - ' . $data->params_method . '" title="Hapus">Hapus</a>' : '';
          $buttonAksi = $editButton . $deleteButton;
          return '<div class="dropdown show m-1"><a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLinkAksi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</a><div class="dropdown-menu dropdown-scroll-menu" aria-labelledby="dropdownMenuLink">' . $buttonAksi . '</div></div>';
        })
        ->addColumn('jenis_sample', function ($data) {
          return $data->name_sample_type ?? '-';
        })
        ->addColumn('jenis_makanan', function ($data) {
          return $data->name_jenis_makanan ?? '-';
        })
        ->addColumn('parameter', function ($data) {
          return $data->params_method ?? '-';
        })
        ->addColumn('acuan_bakumutu', function ($data) {
          return $data->title_library;
        })
        ->addColumn('nilai_bakumutu', function ($data) {
          // Decode HTML entities and format nilai baku mutu for display
          $nilai = $data->nilai_baku_mutu;
          if (empty($nilai)) {
            return '-';
          }
          // Decode HTML entities first (e.g., &lt;sup&gt; to <sup>)
          $nilai = html_entity_decode($nilai, ENT_QUOTES | ENT_HTML5, 'UTF-8');
          // If value contains HTML entities that were double-encoded, decode again
          if (strpos($nilai, '&lt;') !== false || strpos($nilai, '&gt;') !== false) {
            $nilai = html_entity_decode($nilai, ENT_QUOTES | ENT_HTML5, 'UTF-8');
          }
          return $nilai;
        })
        ->rawColumns(['action', 'jenis_sample', 'jenis_makanan', 'parameter', 'acuan_bakumutu', 'nilai_bakumutu'])
        ->addIndexColumn()
        ->make(true);
    }

    $lab_link = "kimia";
    $lab = "Kimia";

    $params_by_jenis_sample = $paramsByJenisSample;
    $params_all = $paramsAll;

    return view('masterweb::module.admin.laboratorium.baku-mutu.list', compact(
      'lab_link',
      'lab',
      'sample_methods',
      'sample_types_for_filter',
      'params_by_jenis_sample',
      'params_all'
    ));
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
    $methods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', '3416ca19-6c69-4e5f-a004-ae8275de7644')

      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->get();
    $id_lab = "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5";
    $sample_types = SampleType::orderBy('created_at')->get();

    $all_jenis_makanan = JenisMakanan::all();

    $libraries = Library::all();

    $units = Unit::all();
    $all_laboratorium = Laboratorium::orderBy('nama_laboratorium')->get();
    $lab_link = "kimia";
    $lab = "Kimia";

    return view('masterweb::module.admin.laboratorium.baku-mutu.add', compact('units', 'all_jenis_makanan', 'libraries', 'sample_types', 'id_lab', 'methods', 'lab_link', 'lab', 'all_laboratorium'));
    //get all menu public
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
    $resolvedLibraryId = $this->resolveLibraryId($data['library_id'] ?? null);
    $resolvedUnitId = $this->resolveUnitId($data['unit_id'] ?? null);

    // $validator = $this->rules($request->all());

    // if ($validator->fails()) {
    //     return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    // } else {
    DB::beginTransaction();

    try {
      $validated = $request->validate([
        'sampletype_id' => ['required'],
        'method_id' => ['required'],
        'tipe_nilai_baku_mutu' => ['nullable', 'in:kuantitatif,kualitatif'],
      ]);

      $isMakananMinumanLainnya = $this->isMakananMinumanLainnyaSampleType($data['sampletype_id'] ?? null);
      if ($isMakananMinumanLainnya && empty($data['tipe_nilai_baku_mutu'])) {
        return response()->json(['status' => false, 'pesan' => 'Tipe nilai baku mutu wajib dipilih untuk jenis sampel Makanan, Minuman, atau Lainnya.'], 200);
      }

      // validasi biar tidak ada data yang double (untuk data tanpa lokasi spesifik)
      // Data dengan lokasi spesifik (JSON) tidak perlu check uniqueness karena bisa multiple
      $useLokasi = isset($data['use_lokasi']) && $data['use_lokasi'] == '1';

      if (!$useLokasi) {
        // Untuk lab kimia, validasi duplikat berdasarkan sampletype_id + method_id + jenis_makanan_id
        // Jika jenis_makanan_id berbeda atau null vs ada nilai, maka dianggap berbeda
        $checkQuery = BakuMutu::where('lab_id', '3416ca19-6c69-4e5f-a004-ae8275de7644')
          ->where('sampletype_id', $data['sampletype_id'])
          ->where('method_id', $data['method_id'])
          ->whereNull('lokasi_data'); // Hanya check data tanpa lokasi_data (backward compatible)

        // Tambahkan kondisi untuk jenis_makanan_id
        if (isset($data['jenis_makanan_id']) && !empty($data['jenis_makanan_id'])) {
          $checkQuery->where('jenis_makanan_id', $data['jenis_makanan_id']);
        } else {
          $checkQuery->whereNull('jenis_makanan_id');
        }

        $check = $checkQuery->first();

        if ($check != null) {
          return response()->json(['status' => false, 'pesan' => "Data baku mutu kimia sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian! Jika ingin menambahkan baku mutu untuk ruangan berbeda, silakan centang 'Gunakan Lokasi / Ruangan' dan tambahkan lokasi."], 200);
        }
      }

      if ($data["is_sub"] == "false") {

        $user = Auth()->user();
        $baku_mutu = new BakuMutu;
        //uuid
        $uuid4 = Uuid::uuid4();

        $baku_mutu->id_baku_mutu = $uuid4->toString();
        if (isset($data["name_report"])) {
          $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
        }
        $baku_mutu->sampletype_id = $data['sampletype_id'];
        $baku_mutu->method_id = $data['method_id'];
        $baku_mutu->unit_id = $resolvedUnitId;
        $baku_mutu->min = $data['min_no_sub'];
        $baku_mutu->max = $data['max_no_sub'];
        $baku_mutu->is_sub = 0;
        if (isset($data["equal_no_sub"])) {
          $baku_mutu->equal = rubahNilaikeHtml(str_replace(",", ".", $data["equal_no_sub"]));
        }
        // $baku_mutu->equal = $data['equal_no_sub'];
        $baku_mutu->library_id = $resolvedLibraryId;
        if (isset($data["nilai_baku_mutu_no_sub"])) {
          $baku_mutu->nilai_baku_mutu = rubahNilaikeHtml(str_replace(",", ".", $data["nilai_baku_mutu_no_sub"]));
        }

        // Handle lokasi data dalam JSON
        if (isset($data['use_lokasi']) && $data['use_lokasi'] == '1' && isset($data['lokasi']) && is_array($data['lokasi'])) {
          $lokasiData = [];
          foreach ($data['lokasi'] as $lokasi) {
            if (!empty($lokasi['nama'])) {
              $lokasiData[] = [
                'nama' => $lokasi['nama'],
                'min' => $lokasi['min'] ?? null,
                'max' => $lokasi['max'] ?? null,
                'equal' => isset($lokasi['equal']) ? rubahNilaikeHtml(str_replace(",", ".", $lokasi['equal'])) : null,
                'nilai_baku_mutu' => isset($lokasi['nilai_baku_mutu']) ? rubahNilaikeHtml(str_replace(",", ".", $lokasi['nilai_baku_mutu'])) : null,
              ];
            }
          }
          if (!empty($lokasiData)) {
            $baku_mutu->lokasi_data = json_encode($lokasiData);
            // Set nilai default untuk backward compatibility
            $baku_mutu->min = null;
            $baku_mutu->max = null;
            $baku_mutu->equal = null;
            $baku_mutu->nilai_baku_mutu = null;
          } else {
            $baku_mutu->lokasi_data = null;
          }
        } else {
          $baku_mutu->lokasi_data = null;
        }

        if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
          $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
        } else {
          $baku_mutu->jenis_makanan_id = NULL;
        }
        $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;
        // Handle is_option dan option
        $baku_mutu->is_option = isset($request['is_option']) && $request['is_option'] ? 1 : 0;
        if ($baku_mutu->is_option == 1) {
          $baku_mutu->option = $request['option'] ?? null;
        } else {
          $baku_mutu->option = null;
        }
        // $baku_mutu->nilai_baku_mutu = $data['nilai_baku_mutu_no_sub'];
        $baku_mutu->lab_id = '3416ca19-6c69-4e5f-a004-ae8275de7644';
        $baku_mutu->save();
      } else {
        $user = Auth()->user();
        $baku_mutu = new BakuMutu;
        //uuid
        $uuid4 = Uuid::uuid4();

        $baku_mutu->id_baku_mutu = $uuid4->toString();
        if (isset($data["name_report"])) {
          $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
        }
        $baku_mutu->sampletype_id = $data['sampletype_id'];
        $baku_mutu->method_id = $data['method_id'];
        $baku_mutu->unit_id = $resolvedUnitId;
        $baku_mutu->library_id = $resolvedLibraryId;
        $baku_mutu->is_sub = 1;
        $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;

        // Handle lokasi data dalam JSON (untuk is_sub = true, lokasi tidak digunakan karena pakai sub baku mutu)
        $baku_mutu->lokasi_data = null;

        $baku_mutu->lab_id = '3416ca19-6c69-4e5f-a004-ae8275de7644';
        $baku_mutu->save();

        foreach ($data['name_subbakumutu'] as $key => $name_subbakumutu) {
          # code...
          if (isset($name_subbakumutu)) {
            $bakuMutudetailparameternonklinik = new BakuMutuDetailParameterNonKlinik;
            //uuid
            $uuid4 = Uuid::uuid4();

            $bakuMutudetailparameternonklinik->id_baku_mutu_detail_parameter_non_klinik = $uuid4->toString();
            $bakuMutudetailparameternonklinik->lab_id = '3416ca19-6c69-4e5f-a004-ae8275de7644';
            $bakuMutudetailparameternonklinik->baku_mutu_id = $baku_mutu->id_baku_mutu;
            $bakuMutudetailparameternonklinik->sampletype_id = $baku_mutu->sampletype_id;
            $bakuMutudetailparameternonklinik->method_id = $baku_mutu->method_id;

            $bakuMutudetailparameternonklinik->name_baku_mutu_detail_parameter_non_klinik   = $data['name_subbakumutu'][$key];
            if (isset($data['min'][$key]) && $data['min'][$key] != "") {
              $bakuMutudetailparameternonklinik->min_baku_mutu_detail_parameter_non_klinik   = $data['min'][$key];
            }
            if (isset($data['max'][$key]) && $data['max'][$key] != "") {
              $bakuMutudetailparameternonklinik->max_baku_mutu_detail_parameter_non_klinik  = $data['max'][$key];
            }
            if (isset($data['equal'][$key]) && $data['equal'][$key] != "") {
              if (isset($data["equal"])) {
                $bakuMutudetailparameternonklinik->equal_baku_mutu_detail_parameter_non_klinik = rubahNilaikeHtml(str_replace(",", ".", $data['equal'][$key]));
              }
              // $bakuMutudetailparameternonklinik->equal_baku_mutu_detail_parameter_non_klinik  = $data['equal'][$key];
            }
            if (isset($data['nilai_baku_mutu'][$key]) && $data['nilai_baku_mutu'][$key] != "") {
              if (isset($data["nilai_baku_mutu"])) {
                $bakuMutudetailparameternonklinik->nilai_baku_mutu_detail_parameter_non_klinik = rubahNilaikeHtml(str_replace(",", ".", $data['nilai_baku_mutu'][$key]));
              }
              // $bakuMutudetailparameternonklinik->nilai_baku_mutu_detail_parameter_non_klinik  = $data['nilai_baku_mutu'][$key];
            }
            $bakuMutudetailparameternonklinik->save();
          }
        }
      }
      DB::commit();

      if ($baku_mutu == true) {
        return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil disimpan!"], 200);
      } else {
        return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil disimpan!"], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
    }
    // }

    // $sample = Sample::where('id_samples',$id)->first();


    if ($baku_mutu == true) {
      return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil diubah!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil diubah!"], 200);
    }
    // return redirect()->route('elits-baku-mutu-kimia.index')->with(['status'=>'Baku Mutu berhasil diinput']);

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

    $baku_mutu = BakuMutu::with('method')->where('id_baku_mutu', $id)->first();

    $bakuMutudetailparameternonkliniks = BakuMutuDetailParameterNonKlinik::where('baku_mutu_id', $id)
      ->where('lab_id', '3416ca19-6c69-4e5f-a004-ae8275de7644')
      ->where('sampletype_id', $baku_mutu->sampletype_id)
      ->where('method_id', $baku_mutu->method_id)
      ->orWhere('baku_mutu_id', $baku_mutu->id_baku_mutu)
      ->orderBy('created_at')
      ->get();
    $all_jenis_makanan = JenisMakanan::all();

    $methods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', '3416ca19-6c69-4e5f-a004-ae8275de7644')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('tb_laboratorium_method.*', 'ms_method.*')
      ->get();
    $id_lab = "3416ca19-6c69-4e5f-a004-ae8275de7644";
    $sample_types = SampleType::orderBy('created_at')->get();



    $libraries = Library::all();
    $units = Unit::all();
    $lab_link = "kimia";
    $lab = "Kimia";


    $all_laboratorium = Laboratorium::orderBy('nama_laboratorium')->get();
    return view('masterweb::module.admin.laboratorium.baku-mutu.edit', compact('all_jenis_makanan', 'id', 'bakuMutudetailparameternonkliniks', 'baku_mutu', 'units', 'libraries', 'sample_types', 'id_lab', 'methods', 'lab_link', 'lab', 'all_laboratorium'));
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
    $data = $request->all();
    $resolvedLibraryId = $this->resolveLibraryId($data['library_id'] ?? null);
    $resolvedUnitId = $this->resolveUnitId($data['unit_id'] ?? null);

    DB::beginTransaction();

    try {
      $validated = $request->validate([
        'sampletype_id' => ['required'],
        'method_id' => ['required'],
        'tipe_nilai_baku_mutu' => ['nullable', 'in:kuantitatif,kualitatif'],
      ]);

      $isMakananMinumanLainnya = $this->isMakananMinumanLainnyaSampleType($data['sampletype_id'] ?? null);
      if ($isMakananMinumanLainnya && empty($data['tipe_nilai_baku_mutu'])) {
        return response()->json(['status' => false, 'pesan' => 'Tipe nilai baku mutu wajib dipilih untuk jenis sampel Makanan, Minuman, atau Lainnya.'], 200);
      }


      $baku_mutu = BakuMutu::find($id);

      // Untuk lab kimia, validasi duplikat berdasarkan sampletype_id + method_id + jenis_makanan_id
      // Jika ada perubahan pada sampletype_id, method_id, atau jenis_makanan_id, cek duplikat
      $jenisMakananId = isset($request->jenis_makanan_id) && !empty($request->jenis_makanan_id) ? $request->jenis_makanan_id : null;
      $currentJenisMakananId = $baku_mutu->jenis_makanan_id;

      if (
        $baku_mutu->sampletype_id != $request->sampletype_id ||
        $baku_mutu->method_id != $request->method_id ||
        $currentJenisMakananId != $jenisMakananId
      ) {
        $checkQuery = BakuMutu::where('lab_id', '3416ca19-6c69-4e5f-a004-ae8275de7644')
          ->where('sampletype_id', $data['sampletype_id'])
          ->where('method_id', $data['method_id'])
          ->where('id_baku_mutu', '!=', $id); // Exclude current record

        // Tambahkan kondisi untuk jenis_makanan_id
        if ($jenisMakananId) {
          $checkQuery->where('jenis_makanan_id', $jenisMakananId);
        } else {
          $checkQuery->whereNull('jenis_makanan_id');
        }

        $check = $checkQuery->first();

        if ($check != null) {
          return response()->json(['status' => false, 'pesan' => "Data baku mutu kimia sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
        }
      }

      // Update data
      $baku_mutu->sampletype_id = $data['sampletype_id'];
      $baku_mutu->method_id = $data['method_id'];

      // Untuk lab kimia, jenis_makanan_id tidak wajib, bisa null
      if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
        $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
      } else {
        $baku_mutu->jenis_makanan_id = NULL;
      }
      $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;


      if ($data["is_sub"] == "false") {
        $user = Auth()->user();
        if (isset($data["name_report"])) {
          $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
        }
        $baku_mutu->unit_id = $resolvedUnitId;
        $baku_mutu->min = $data['min_no_sub'];
        $baku_mutu->max = $data['max_no_sub'];
        $baku_mutu->is_sub = 0;
        if (isset($data["equal_no_sub"])) {
          $baku_mutu->equal = rubahNilaikeHtml(str_replace(",", ".", $data["equal_no_sub"]));
        }
        $baku_mutu->library_id = $resolvedLibraryId;
        if (isset($data["nilai_baku_mutu_no_sub"])) {
          $baku_mutu->nilai_baku_mutu = rubahNilaikeHtml(str_replace(",", ".", $data["nilai_baku_mutu_no_sub"]));
        }

        // Handle lokasi data dalam JSON
        if (isset($data['use_lokasi']) && $data['use_lokasi'] == '1' && isset($data['lokasi']) && is_array($data['lokasi'])) {
          $lokasiData = [];
          foreach ($data['lokasi'] as $lokasi) {
            if (!empty($lokasi['nama'])) {
              $lokasiData[] = [
                'nama' => $lokasi['nama'],
                'min' => $lokasi['min'] ?? null,
                'max' => $lokasi['max'] ?? null,
                'equal' => isset($lokasi['equal']) ? rubahNilaikeHtml(str_replace(",", ".", $lokasi['equal'])) : null,
                'nilai_baku_mutu' => isset($lokasi['nilai_baku_mutu']) ? rubahNilaikeHtml(str_replace(",", ".", $lokasi['nilai_baku_mutu'])) : null,
              ];
            }
          }
          if (!empty($lokasiData)) {
            $baku_mutu->lokasi_data = json_encode($lokasiData);
            // Set nilai default untuk backward compatibility
            $baku_mutu->min = null;
            $baku_mutu->max = null;
            $baku_mutu->equal = null;
            $baku_mutu->nilai_baku_mutu = null;
          } else {
            $baku_mutu->lokasi_data = null;
          }
        } else {
          $baku_mutu->lokasi_data = null;
        }

        // Handle is_option dan option
        $baku_mutu->is_option = isset($request['is_option']) && $request['is_option'] ? 1 : 0;
        if ($baku_mutu->is_option == 1) {
          $baku_mutu->option = $request['option'] ?? null;
        } else {
          $baku_mutu->option = null;
        }

        $baku_mutu->save();

        // $sample = Sample::where('id_samples',$id)->first();
      } else {
        $user = Auth()->user();
        if (isset($data["name_report"])) {
          $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
        }
        $baku_mutu->unit_id = $resolvedUnitId;
        $baku_mutu->min = NULL;
        $baku_mutu->max = NULL;
        $baku_mutu->is_sub = 1;
        $baku_mutu->equal = NULL;
        $baku_mutu->library_id = $resolvedLibraryId;
        $baku_mutu->nilai_baku_mutu = NULL;

        // Handle lokasi data dalam JSON (untuk is_sub = true, lokasi tidak digunakan karena pakai sub baku mutu)
        $baku_mutu->lokasi_data = null;

        $baku_mutu->save();


        BakuMutuDetailParameterNonKlinik::where('baku_mutu_id', $baku_mutu->id_baku_mutu)->delete();
        foreach ($data['name_subbakumutu'] as $key => $name_subbakumutu) {
          # code...
          if (isset($name_subbakumutu)) {
            $bakuMutudetailparameternonklinik = new BakuMutuDetailParameterNonKlinik;
            //uuid
            $uuid4 = Uuid::uuid4();

            $bakuMutudetailparameternonklinik->id_baku_mutu_detail_parameter_non_klinik = $uuid4->toString();
            $bakuMutudetailparameternonklinik->lab_id = '3416ca19-6c69-4e5f-a004-ae8275de7644';
            $bakuMutudetailparameternonklinik->baku_mutu_id = $baku_mutu->id_baku_mutu;
            $bakuMutudetailparameternonklinik->sampletype_id = $baku_mutu->sampletype_id;
            $bakuMutudetailparameternonklinik->method_id = $baku_mutu->method_id;

            $bakuMutudetailparameternonklinik->name_baku_mutu_detail_parameter_non_klinik   = $data['name_subbakumutu'][$key];
            if (isset($data['min'][$key]) && $data['min'][$key] != "") {
              $bakuMutudetailparameternonklinik->min_baku_mutu_detail_parameter_non_klinik   = $data['min'][$key];
            }
            if (isset($data['max'][$key]) && $data['max'][$key] != "") {
              $bakuMutudetailparameternonklinik->max_baku_mutu_detail_parameter_non_klinik  = $data['max'][$key];
            }
            if (isset($data['equal'][$key]) && $data['equal'][$key] != "") {
              if (isset($data["equal"])) {
                $bakuMutudetailparameternonklinik->equal_baku_mutu_detail_parameter_non_klinik = rubahNilaikeHtml(str_replace(",", ".", $data['equal'][$key]));
              }
              // $bakuMutudetailparameternonklinik->equal_baku_mutu_detail_parameter_non_klinik  = $data['equal'][$key];
            }
            if (isset($data['nilai_baku_mutu'][$key]) && $data['nilai_baku_mutu'][$key] != "") {
              if (isset($data["nilai_baku_mutu"])) {
                $bakuMutudetailparameternonklinik->nilai_baku_mutu_detail_parameter_non_klinik = rubahNilaikeHtml(str_replace(",", ".", $data['nilai_baku_mutu'][$key]));
              }
              // $bakuMutudetailparameternonklinik->nilai_baku_mutu_detail_parameter_non_klinik  = $data['nilai_baku_mutu'][$key];
            }
            $bakuMutudetailparameternonklinik->save();
          }
        }
      }
      DB::commit();

      if ($baku_mutu == true) {
        return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil disimpan!"], 200);
      } else {
        return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil disimpan!"], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
    }

    // return redirect()->route('elits-baku-mutu-kimia.index')->with(['status'=>'Baku Mutu berhasil diupdate']);


  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $hapus = BakuMutu::where('id_baku_mutu', $id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data baku mutu kimia berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data baku mutu kimia tidak berhasil dihapus!"], 200);
    }
  }
}
