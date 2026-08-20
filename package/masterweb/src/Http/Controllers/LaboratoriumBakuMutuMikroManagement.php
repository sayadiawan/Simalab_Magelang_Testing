<?php

namespace Smt\Masterweb\Http\Controllers;


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
use Illuminate\Support\Facades\DB;
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
use Illuminate\Support\Facades\Validator;

use \Smt\Masterweb\Models\PenerimaanSample;


use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\BakuMutuDetailParameterNonKlinik;


class LaboratoriumBakuMutuMikroManagement extends Controller
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
    $lab_link = "mikro";
    $lab = "Mikro";
    $labIdMikro = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';

    if ($request->ajax()) {
      $query = BakuMutu::query()
        ->where('tb_baku_mutu.lab_id', '=', $labIdMikro)
        ->leftJoin('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_baku_mutu.method_id')
            ->whereNull('ms_method.deleted_at');
        })
        ->leftJoin('ms_library', function ($join) {
          $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
            ->whereNull('ms_library.deleted_at');
        })
        ->leftJoin('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_baku_mutu.sampletype_id')
            ->whereNull('ms_sample_type.deleted_at');
        })
        ->leftJoin('ms_jenis_makanan', function ($join) {
          $join->on('ms_jenis_makanan.id_jenis_makanan', '=', 'tb_baku_mutu.jenis_makanan_id')
            ->whereNull('ms_jenis_makanan.deleted_at');
        })
        ->select(
          'tb_baku_mutu.id_baku_mutu',
          'tb_baku_mutu.nilai_baku_mutu',
          'tb_baku_mutu.created_at',
          'ms_method.params_method',
          'ms_library.title_library',
          'ms_sample_type.name_sample_type',
          'ms_jenis_makanan.name_jenis_makanan'
        )
        ->orderBy('tb_baku_mutu.created_at');

      $searchInput = $request->get('search');
      $globalSearch = is_array($searchInput)
        ? trim((string) ($searchInput['value'] ?? ''))
        : trim((string) $searchInput);
      $filterJenisSample = trim((string) $request->get('jenis_sample'));
      $filterParameter = trim((string) $request->get('parameter'));

      if ($globalSearch !== '') {
        $query->where(function ($q) use ($globalSearch) {
          $q->where('ms_sample_type.name_sample_type', 'like', "%{$globalSearch}%")
            ->orWhere('ms_method.params_method', 'like', "%{$globalSearch}%")
            ->orWhere('ms_library.title_library', 'like', "%{$globalSearch}%")
            ->orWhere('ms_jenis_makanan.name_jenis_makanan', 'like', "%{$globalSearch}%");
        });
      }

      if ($filterJenisSample !== '') {
        $query->where('ms_sample_type.name_sample_type', $filterJenisSample);
      }

      if ($filterParameter !== '') {
        $query->where('ms_method.params_method', $filterParameter);
      }

      return DataTables::of($query)
        ->filter(function () {
          // Filters already applied on the query builder above.
        }, false)
        ->addColumn('action', function ($data) {
          if (getAction('update') || getAction('delete')) {
            if (getAction('update')) {
              $editButton = '<a href="' . route('elits-baku-mutu-mikro.edit', [$data->id_baku_mutu]) . '" class="dropdown-item"
              data-toggle="tooltip" data-custom-class="tooltip-info" data-placement="top"
              title="Edit Data">Edit</a>';
            } else {
              $editButton = '';
            }

            $deleteButton = '';
            if (getAction('delete')) {
              $deleteButton = '<a class="dropdown-item btn-hapus" href="javascript:void(0)" data-id="' . $data->id_baku_mutu . '" data-nama="' . $data->name_sample_type . ' - ' . $data->params_method . '" title="Hapus">Hapus</a> ';
            }

            $buttonAksi = '<div class="dropdown show m-1">
                              <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLinkAksi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Aksi
                              </a>

                              <div class="dropdown-menu dropdown-scroll-menu" aria-labelledby="dropdownMenuLink">
                                  ' . $editButton . '
                                  ' . $deleteButton . '
                              </div>
                          </div>';
          } else {
            $buttonAksi = '';
          }

          return $buttonAksi;
        })
        ->addColumn('jenis_sample', function ($data) {
          $jenisSample = $data->name_sample_type ?? '-';
          if (str_contains($jenisSample, 'Makanan/Minuman/Lainnya') && !empty($data->name_jenis_makanan)) {
            $jenisSample .= ' <span class="badge badge-info">' . $data->name_jenis_makanan . '</span>';
          }
          return $jenisSample;
        })
        ->addColumn('parameter', function ($data) {
          return $data->params_method ?? '-';
        })
        ->addColumn('acuan_bakumutu', function ($data) {
          return $data->title_library;
        })
        ->addColumn('nilai_bakumutu', function ($data) {
          $nilai = $data->nilai_baku_mutu;
          if (empty($nilai)) {
            return '-';
          }
          $nilai = html_entity_decode($nilai, ENT_QUOTES | ENT_HTML5, 'UTF-8');
          if (strpos($nilai, '&lt;') !== false || strpos($nilai, '&gt;') !== false) {
            $nilai = html_entity_decode($nilai, ENT_QUOTES | ENT_HTML5, 'UTF-8');
          }
          return $nilai;
        })
        ->orderColumn('jenis_sample', 'ms_sample_type.name_sample_type $1')
        ->orderColumn('parameter', 'ms_method.params_method $1')
        ->rawColumns([
          'action',
          'jenis_sample',
          'parameter',
          'acuan_bakumutu',
          'nilai_bakumutu'
        ])
        ->addIndexColumn()
        ->make(true);
    }

    $bakuSampleTypeIds = DB::table('tb_baku_mutu')
      ->where('lab_id', $labIdMikro)
      ->whereNull('deleted_at')
      ->whereNotNull('sampletype_id')
      ->distinct()
      ->pluck('sampletype_id');

    $sample_types = $bakuSampleTypeIds->isEmpty()
      ? collect()
      : SampleType::query()
        ->whereIn('id_sample_type', $bakuSampleTypeIds->all())
        ->whereNull('deleted_at')
        ->orderBy('name_sample_type')
        ->get(['name_sample_type']);

    $paramsByJenisSample = [];
    $paramsAllMap = [];
    $pairsMikro = DB::table('tb_baku_mutu as bm')
      ->join('ms_sample_type as st', function ($join) {
        $join->on('st.id_sample_type', '=', 'bm.sampletype_id')
          ->whereNull('st.deleted_at');
      })
      ->join('ms_method as m', function ($join) {
        $join->on('m.id_method', '=', 'bm.method_id')
          ->whereNull('m.deleted_at');
      })
      ->where('bm.lab_id', $labIdMikro)
      ->whereNull('bm.deleted_at')
      ->whereNotNull('bm.sampletype_id')
      ->whereNotNull('bm.method_id')
      ->select('st.name_sample_type', 'm.params_method')
      ->distinct()
      ->orderBy('m.params_method')
      ->get();

    foreach ($pairsMikro as $row) {
      $jn = $row->name_sample_type;
      $pm = $row->params_method;
      if ($jn === null || $pm === null || $pm === '') {
        continue;
      }
      if (!isset($paramsByJenisSample[$jn])) {
        $paramsByJenisSample[$jn] = [];
      }
      if (!isset($paramsByJenisSample[$jn][$pm])) {
        $paramsByJenisSample[$jn][$pm] = true;
      }
      $paramsAllMap[$pm] = true;
    }
    foreach ($paramsByJenisSample as $k => $arr) {
      $paramsByJenisSample[$k] = array_keys($arr);
      sort($paramsByJenisSample[$k], SORT_NATURAL | SORT_FLAG_CASE);
    }
    $paramsAll = array_keys($paramsAllMap);
    sort($paramsAll, SORT_NATURAL | SORT_FLAG_CASE);

    $sample_methods = collect($paramsAll)->map(function ($p) {
      return (object) ['params_method' => $p];
    });

    $params_by_jenis_sample = $paramsByJenisSample;
    $params_all = $paramsAll;

    return view('masterweb::module.admin.laboratorium.baku-mutu.list_mikro', compact(
      'lab',
      'lab_link',
      'sample_types',
      'sample_methods',
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
    $methods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
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
    $lab_link = "mikro";
    $lab = "Mikro";

    return view('masterweb::module.admin.laboratorium.baku-mutu.add', compact('all_jenis_makanan', 'units', 'libraries', 'sample_types', 'id_lab', 'methods', 'lab_link', 'lab', 'all_laboratorium'));
  }

  public function rules($request)
  {
    $rule = [
      'sampletype_id' => 'required',
      'method_id' => 'required',
      'tipe_nilai_baku_mutu' => 'nullable|in:kuantitatif,kualitatif'
    ];

    $pesan = [
      'sampletype_id.required' => 'Jenis sampel tidak boleh kosong!',
      'method_id.required' => 'Parameter tidak boleh kosong!',
      'tipe_nilai_baku_mutu.in' => 'Tipe nilai baku mutu harus kuantitatif atau kualitatif.'
    ];

    return Validator::make($request, $rule, $pesan);
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

    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $isMakananMinumanLainnya = $this->isMakananMinumanLainnyaSampleType($data['sampletype_id'] ?? null);
        if ($isMakananMinumanLainnya && empty($data['tipe_nilai_baku_mutu'])) {
          return response()->json(['status' => false, 'pesan' => 'Tipe nilai baku mutu wajib dipilih untuk jenis sampel Makanan, Minuman, atau Lainnya.'], 200);
        }

        // validasi biar tidak ada data yang double (untuk data tanpa lokasi spesifik)
        // Data dengan lokasi spesifik (JSON) tidak perlu check uniqueness karena bisa multiple
        $useLokasi = isset($data['use_lokasi']) && $data['use_lokasi'] == '1';

        if (!$useLokasi) {
          // Check uniqueness hanya untuk data tanpa lokasi spesifik
          if ($isMakananMinumanLainnya) {
            // Untuk jenis sample Makanan/Minuman/Lainnya, wajib ada jenis_makanan_id
            if (!isset($request->jenis_makanan_id) || $request->jenis_makanan_id == null) {
              return response()->json(['status' => false, 'pesan' => "Jenis Makanan wajib diisi untuk jenis sample Makanan/Minuman/Lainnya!"], 200);
            }
            // Validasi duplikat berdasarkan kombinasi sampletype_id + method_id + jenis_makanan_id
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->where('jenis_makanan_id', $data['jenis_makanan_id'])
              ->whereNull('lokasi_data') // Hanya check data tanpa lokasi_data (backward compatible)
              ->first();
          } else {
            // Untuk jenis sample lainnya, validasi duplikat berdasarkan sampletype_id + method_id (tanpa jenis_makanan_id)
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->whereNull('jenis_makanan_id')
              ->whereNull('lokasi_data') // Hanya check data tanpa lokasi_data (backward compatible)
              ->first();
          }

          if ($check != null) {
            return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian! Jika ingin menambahkan baku mutu untuk ruangan berbeda, silakan centang 'Gunakan Lokasi / Ruangan' dan tambahkan lokasi."], 200);
          }
        }

        if ($data["is_sub"] == "false") {
          $user = Auth()->user();
          $baku_mutu = new BakuMutu;
          //uuid
          $uuid4 = Uuid::uuid4();

          // $baku_mutu->id_baku_mutu = $uuid4->toString();
          $baku_mutu->sampletype_id = $data['sampletype_id'];
          $baku_mutu->method_id = $data['method_id'];
          $baku_mutu->unit_id = $resolvedUnitId;
          $baku_mutu->min = $data['min_no_sub'];
          $baku_mutu->max = $data['max_no_sub'];
          if (isset($data["equal_no_sub"])) {
            $baku_mutu->equal = rubahNilaikeHtml(str_replace(",", ".", $data["equal_no_sub"]));
          }
          if (isset($data["name_report"])) {
            $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
          }
          $baku_mutu->library_id = $resolvedLibraryId;
          // $baku_mutu->nilai_baku_mutu = $data['nilai_baku_mutu_no_sub'];
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

          $baku_mutu->lab_id = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
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

          $simpan = $baku_mutu->save();
        } else {
          $user = Auth()->user();
          $baku_mutu = new BakuMutu;
          //uuid
          $uuid4 = Uuid::uuid4();

          // $baku_mutu->id_baku_mutu = $uuid4->toString();
          if (isset($data["name_report"])) {
            $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
          }
          $baku_mutu->sampletype_id = $data['sampletype_id'];
          $baku_mutu->method_id = $data['method_id'];
          $baku_mutu->unit_id = $resolvedUnitId;
          $baku_mutu->is_sub = 1;
          $baku_mutu->library_id = $resolvedLibraryId;

          // Handle lokasi data dalam JSON (untuk is_sub = true, lokasi tidak digunakan karena pakai sub baku mutu)
          $baku_mutu->lokasi_data = null;

          $baku_mutu->lab_id = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
          if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
            $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
          } else {
            $baku_mutu->jenis_makanan_id = NULL;
          }
          $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;

          $simpan = $baku_mutu->save();

          foreach ($data['name_subbakumutu'] as $key => $name_subbakumutu) {
            if (isset($name_subbakumutu)) {
              $bakuMutudetailparameternonklinik = new BakuMutuDetailParameterNonKlinik;
              //uuid
              $uuid4 = Uuid::uuid4();

              // $bakuMutudetailparameternonklinik->id_baku_mutu_detail_parameter_non_klinik = $uuid4->toString();
              $bakuMutudetailparameternonklinik->baku_mutu_id = $baku_mutu->id_baku_mutu;
              $bakuMutudetailparameternonklinik->sampletype_id = $baku_mutu->sampletype_id;
              $bakuMutudetailparameternonklinik->method_id = $baku_mutu->method_id;
              $bakuMutudetailparameternonklinik->lab_id = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
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

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil disimpan!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil disimpan!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
      }
    }
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
    $all_jenis_makanan = JenisMakanan::all();


    $bakuMutudetailparameternonkliniks = BakuMutuDetailParameterNonKlinik::where('baku_mutu_id', $id)
      ->where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      ->where('sampletype_id', $baku_mutu->sampletype_id)
      ->where('method_id', $baku_mutu->method_id)
      ->orWhere('baku_mutu_id', $baku_mutu->id_baku_mutu)
      ->orderBy('created_at')
      ->get();

    $methods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('tb_laboratorium_method.*', 'ms_method.*')
      ->get();
    $id_lab = "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5";
    $sample_types = SampleType::orderBy('created_at')->get();

    $libraries = Library::all();
    $units = Unit::all();
    $all_laboratorium = Laboratorium::orderBy('nama_laboratorium')->get();
    $lab_link = "mikro";
    $lab = "Mikro";


    return view('masterweb::module.admin.laboratorium.baku-mutu.edit', compact('all_jenis_makanan', 'id', 'baku_mutu', 'units', 'libraries', 'sample_types', 'id_lab', 'methods', 'lab_link', 'lab', 'bakuMutudetailparameternonkliniks', 'all_laboratorium'));
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

    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $isMakananMinumanLainnya = $this->isMakananMinumanLainnyaSampleType($data['sampletype_id'] ?? null);
        if ($isMakananMinumanLainnya && empty($data['tipe_nilai_baku_mutu'])) {
          return response()->json(['status' => false, 'pesan' => 'Tipe nilai baku mutu wajib dipilih untuk jenis sampel Makanan, Minuman, atau Lainnya.'], 200);
        }

        // validasi untuk mengetahui apakah adatanya beda atau sama
        $baku_mutu = BakuMutu::find($id);

        if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
          if (
            $baku_mutu->sampletype_id != $request->sampletype_id &&
            $baku_mutu->method_id != $request->method_id &&
            $baku_mutu->jenis_makanan_id != $request->jenis_makanan_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->where('jenis_makanan_id', $data['jenis_makanan_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
              $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;
            }
          } else if (
            $baku_mutu->sampletype_id == $request->sampletype_id &&
            $baku_mutu->method_id != $request->method_id &&
            $baku_mutu->jenis_makanan_id != $request->jenis_makanan_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->where('jenis_makanan_id', $data['jenis_makanan_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu parameter dan jenis makanan sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          } else if (
            $baku_mutu->sampletype_id != $request->sampletype_id &&
            $baku_mutu->method_id == $request->method_id &&
            $baku_mutu->jenis_makanan_id != $request->jenis_makanan_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->where('jenis_makanan_id', $data['jenis_makanan_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu jenis sample dan jenis makanan sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          } else if (
            $baku_mutu->sampletype_id != $request->sampletype_id &&
            $baku_mutu->method_id != $request->method_id &&
            $baku_mutu->jenis_makanan_id == $request->jenis_makanan_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->where('jenis_makanan_id', $data['jenis_makanan_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu jenis sample dan parameter sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          }
        } else {
          if (
            $baku_mutu->sampletype_id != $request->sampletype_id &&
            $baku_mutu->method_id != $request->method_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          } else if (
            $baku_mutu->sampletype_id == $request->sampletype_id &&
            $baku_mutu->method_id != $request->method_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu parameter sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          } else if (
            $baku_mutu->sampletype_id != $request->sampletype_id &&
            $baku_mutu->method_id == $request->method_id
          ) {
            $check = BakuMutu::where('lab_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('sampletype_id', $data['sampletype_id'])
              ->where('method_id', $data['method_id'])
              ->first();

            if ($check != null) {
              return response()->json(['status' => false, 'pesan' => "Data baku mutu jenis sample sudah pernah diinputkan. Untuk detailnya bisa dicari pada kolom pencarian!"], 200);
            } else {
              $baku_mutu->sampletype_id = $data['sampletype_id'];
              $baku_mutu->method_id = $data['method_id'];

              if (isset($request->jenis_makanan_id) && $request->jenis_makanan_id != null) {
                $baku_mutu->jenis_makanan_id = $request->jenis_makanan_id;
              } else {
                $baku_mutu->jenis_makanan_id = NULL;
              }
            }
          }
        }

        if ($data["is_sub"] == "false") {
          $user = Auth()->user();
          // $baku_mutu = BakuMutu::find($id);

          if (isset($data["name_report"])) {
            $baku_mutu->name_report = rubahNilaikeHtml($data["name_report"]);
          }
          $baku_mutu->unit_id = $resolvedUnitId;
          $baku_mutu->min = $data['min_no_sub'];
          $baku_mutu->max = $data['max_no_sub'];
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

          // Handle is_option dan option
          $baku_mutu->is_option = isset($request['is_option']) && $request['is_option'] ? 1 : 0;
          if ($baku_mutu->is_option == 1) {
            $baku_mutu->option = $request['option'] ?? null;
          } else {
            $baku_mutu->option = null;
          }

          $simpan = $baku_mutu->save();
        } else {
          $user = Auth()->user();
          // $baku_mutu = BakuMutu::find($id);
          //uuid
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
          $baku_mutu->tipe_nilai_baku_mutu = $isMakananMinumanLainnya ? ($data['tipe_nilai_baku_mutu'] ?? null) : null;

          $simpan = $baku_mutu->save();


          BakuMutuDetailParameterNonKlinik::where('baku_mutu_id', $baku_mutu->id_baku_mutu)->delete();
          foreach ($data['name_subbakumutu'] as $key => $name_subbakumutu) {
            # code...
            if (isset($name_subbakumutu)) {
              $bakuMutudetailparameternonklinik = new BakuMutuDetailParameterNonKlinik;
              //uuid
              $uuid4 = Uuid::uuid4();

              // $bakuMutudetailparameternonklinik->id_baku_mutu_detail_parameter_non_klinik = $uuid4->toString();
              $bakuMutudetailparameternonklinik->lab_id = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
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

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil diubah!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil diubah!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
      }
    }
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
      return response()->json(['status' => true, 'pesan' => "Data baku mutu mikro berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data baku mutu mikro tidak berhasil dihapus!"], 200);
    }
  }

  public function resyncFormatBakuMutu(Request $request)
  {
    DB::beginTransaction();

    try {
      // terkadang ada data min max dan nilai bakumutunya yang berkoma
      $bakumutus = BakuMutu::orderBy('created_at', 'desc')
        ->get();

      if (count($bakumutus) > 0) {
        foreach ($bakumutus as $key => $bakumutu) {
          $post = BakuMutu::findOrFail($bakumutu->id_baku_mutu);

          if ($post->min != null) {
            if (strpos($post->min, ',') !== false) {
              $post->min = str_replace(",", ".", $post->min);
            }
          }

          if ($post->max != null) {
            if (strpos($post->max, ',') !== false) {
              $post->max = str_replace(",", ".", $post->max);
            }
          }

          if ($post->nilai_baku_mutu != null && ($post->min != null || $post->max != null)) {
            if (strpos($post->nilai_baku_mutu, ',') !== false) {
              $post->nilai_baku_mutu = str_replace(",", ".", $post->nilai_baku_mutu);
            }
          }

          $post->save();

          $data_bakumutu_detail_nonklinik = BakuMutuDetailParameterNonKlinik::where('baku_mutu_id', $bakumutu->id_baku_mutu)->get();

          if (count($data_bakumutu_detail_nonklinik) > 0) {
            foreach ($data_bakumutu_detail_nonklinik as $key => $value) {
              $post_detail = BakuMutuDetailParameterNonKlinik::findOrFail($value->id_baku_mutu_detail_parameter_non_klinik);

              if ($post_detail->min_baku_mutu_detail_parameter_non_klinik != null) {
                if (strpos($post_detail->min_baku_mutu_detail_parameter_non_klinik, ',') !== false) {
                  $post_detail->min_baku_mutu_detail_parameter_non_klinik = str_replace(",", ".", $post_detail->min_baku_mutu_detail_parameter_non_klinik);
                }
              }

              if ($post_detail->max_baku_mutu_detail_parameter_non_klinik != null) {
                if (strpos($post_detail->max_baku_mutu_detail_parameter_non_klinik, ',') !== false) {
                  $post_detail->max_baku_mutu_detail_parameter_non_klinik = str_replace(",", ".", $post_detail->max_baku_mutu_detail_parameter_non_klinik);
                }
              }

              if ($post_detail->nilai_baku_mutu_detail_parameter_non_klinik != null && ($post_detail->min_baku_mutu_detail_parameter_non_klinik != null || $post_detail->max_baku_mutu_detail_parameter_non_klinik != null)) {
                if (strpos($post_detail->nilai_baku_mutu_detail_parameter_non_klinik, ',') !== false) {
                  $post_detail->nilai_baku_mutu_detail_parameter_non_klinik = str_replace(",", ".", $post_detail->nilai_baku_mutu_detail_parameter_non_klinik);
                }
              }

              $post_detail->save();
            }
          }

          $data_bakumutu_detail_klinik = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $bakumutu->id_baku_mutu)->get();

          if (count($data_bakumutu_detail_klinik) > 0) {
            foreach ($data_bakumutu_detail_klinik as $key => $value) {
              $post_detail = BakuMutuDetailParameterKlinik::findOrFail($value->id_baku_mutu_detail_parameter_klinik);

              if ($post_detail->min_baku_mutu_detail_parameter_klinik != null) {
                if (strpos($post_detail->min_baku_mutu_detail_parameter_klinik, ',') !== false) {
                  $post_detail->min_baku_mutu_detail_parameter_klinik = str_replace(",", ".", $post_detail->min_baku_mutu_detail_parameter_klinik);
                }
              }

              if ($post_detail->max_baku_mutu_detail_parameter_klinik != null) {
                if (strpos($post_detail->max_baku_mutu_detail_parameter_klinik, ',') !== false) {
                  $post_detail->max_baku_mutu_detail_parameter_klinik = str_replace(",", ".", $post_detail->max_baku_mutu_detail_parameter_klinik);
                }
              }

              if ($post_detail->nilai_baku_mutu_detail_parameter_klinik != null && ($post_detail->min_baku_mutu_detail_parameter_klinik != null || $post_detail->max_baku_mutu_detail_parameter_klinik != null)) {
                if (strpos($post_detail->nilai_baku_mutu_detail_parameter_klinik, ',') !== false) {
                  $post_detail->nilai_baku_mutu_detail_parameter_klinik = str_replace(",", ".", $post_detail->nilai_baku_mutu_detail_parameter_klinik);
                }
              }

              $post_detail->save();
            }
          }
        }
      }

      DB::commit();

      return view('masterweb::response', [
        'code' => 200,
        'status' => true,
        'pesan' => 'Resynchronize tanda baca pada baku mutu terhadap format yang diinginkan berhasil diubah!',
      ]);
    } catch (\Exception $e) {
      DB::rollback();

      return view('masterweb::response', [
        'code' => 400,
        'status' => false,
        'pesan' => $e->getMessage(),
      ]);
    }
  }
}
