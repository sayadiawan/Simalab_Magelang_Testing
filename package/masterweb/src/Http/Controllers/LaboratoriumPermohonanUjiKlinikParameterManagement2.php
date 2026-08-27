<?php

namespace Smt\Masterweb\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Smt\Masterweb\Helpers\HajiPaketHelper;
use Smt\Masterweb\Helpers\SatuSehatHelper;
use Smt\Masterweb\Models\Pasien;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\BakuMutu;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Models\ParameterPaketExtra;
use Smt\Masterweb\Models\PermohonanUjiKlinik;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterSubPaketExtra;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik2;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\ParameterCategoryLayout;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik2;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Smt\Masterweb\Models\SatuSehatLocation;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\Petugas;

class LaboratoriumPermohonanUjiKlinikParameterManagement2 extends Controller
{
  protected $satuSehatHelper;
  public function __construct(SatuSehatHelper $satuSehatHelper)
  {
    $this->middleware('auth');
    $this->satuSehatHelper = $satuSehatHelper;
  }

  /**
   * Kelola parameter klinik: register (registrasi) + analis + admin.
   */
  private function canManageKlinikParameter(): bool
  {
    $level = auth()->user()->getlevel->level ?? null;

    return in_array($level, ['RGSTR', 'ANLS', 'ALAB', 'PLAB', 'ADMN'], true);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request, $id_permohonan_uji_klinik)
  {
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');

    if (request()->ajax()) {

      $datas = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereHas('parameterPaketKlinik', function ($query) {
          $query->where('name_parameter_paket_klinik', '!=', 'Sedimen');
        })
        ->orderBy('parameter_paket_extra', 'desc')
        ->get();

      // dd($datas);

      $temp_datas = [];

      $temp_extra_paket = null;
      $paket_haji_added = false;
      $hasIndividualPakets = $this->permohonanHasIndividualPaketKlinik($datas);

      foreach($datas as $val){
        if(isset($val->parameter_paket_extra) && $temp_extra_paket != $val->parameter_paket_extra && $val->parameter_paket_extra != ''){
          $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
          $value_extra_paket = [
            "id_permohonan_uji_paket_klinik" => $val->id_permohonan_uji_paket_klinik,
            "permohonan_uji_klinik" => $val->permohonan_uji_klinik,
            "parameter_jenis_klinik" =>  $val->parameter_jenis_klinik,
            "type_permohonan_uji_paket_klinik" =>  'EP',
            "nama_parameter_paket_extra" => $extra_paket->nama_parameter_paket_extra,
            "parameter_paket_klinik" =>  $val->parameter_paket_klinik,
            "parameter_paket_extra" =>  $val->parameter_paket_extra,
            "harga_permohonan_uji_paket_klinik" =>  $extra_paket->harga_parameter_paket_extra,
            "is_prolanis_gula" =>  $val->is_prolanis_gula,
            "is_prolanis_urine" =>  $val->is_prolanis_urine,
            "is_haji" => $val->is_haji,
            "id_service_request" =>  $val->id_service_request,
            "response_service_request" =>  $val->response_service_request,
          ];

          array_push($temp_datas, $value_extra_paket);
          $temp_extra_paket = $val->parameter_paket_extra;

        }elseif($val->parameter_paket_extra == ''){
          if ($val->parameter_paket_klinik) {
            $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();
            if ($paket && HajiPaketHelper::isCompositePaketHajiName($paket->name_parameter_paket_klinik)) {
              if ($hasIndividualPakets) {
                continue;
              }

              if (!$paket_haji_added) {
                $permohonan = PermohonanUjiKlinik2::with('permohonanujiparameterklinik')->find($id_permohonan_uji_klinik);
                $expandedPakets = $permohonan
                  ? HajiPaketHelper::resolveLayoutPaketsFromPermohonan($permohonan)
                  : collect();
                foreach ($expandedPakets as $individualPaket) {
                  $expanded = clone $val;
                  $expanded->parameter_paket_klinik = $individualPaket->id_parameter_paket_klinik;
                  $expanded->harga_permohonan_uji_paket_klinik = $individualPaket->harga_parameter_paket_klinik;
                  array_push($temp_datas, $expanded);
                }
                $paket_haji_added = true;
              }
            } else {
              array_push($temp_datas, $val);
            }
          } else {
            array_push($temp_datas, $val);
          }
        }
      }

      // dd($temp_datas);

      $datas = $this->sortPermohonanPaketBySatuanReorder($temp_datas);

      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['parameter_jenis_klinik']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['parameter_paket_klinik']), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        // ->addColumn('parameter_jenis_klinik', function ($data) {
        //   $jenis_parameter = ParameterJenisKlinik::find($data->parameter_jenis_klinik);
        //   return $jenis_parameter->name_parameter_jenis_klinik;

        // })
        ->addColumn('paket', function ($data) {

          // Gabungkan nama paket menjadi string yang dipisahkan koma
          if($data['type_permohonan_uji_paket_klinik'] == 'EP'){
            return $data['nama_parameter_paket_extra'];
          }else{
            $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $data->parameter_paket_klinik)->first();
            return $paket->name_parameter_paket_klinik;
          }

        })
        ->addColumn('harga_permohonan_uji_paket_klinik', function ($data) {
          if($data['type_permohonan_uji_paket_klinik'] == 'EP'){
            return rupiah($data['harga_permohonan_uji_paket_klinik']);
          }else{
            return $data->harga_permohonan_uji_paket_klinik != null ? rupiah($data->harga_permohonan_uji_paket_klinik) : 'Rp. 0';
          }
        })
        ->addColumn('action', function ($data) use ($id_permohonan_uji_klinik) {
          // $data_permohonan_uji_klinik = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);

          // $editButton = '';
          // $deleteButton = '';

          // $detailButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik-2.show-permohonan-uji-klinik-parameter', [$id_permohonan_uji_klinik, $data->id_permohonan_uji_paket_klinik]) . '" title="Detail">Detail</a> ';

          // if ($data_permohonan_uji_klinik->status_permohonan_uji_klinik != "SELESAI") {
          //   $editButton = '<a href="' . route('elits-permohonan-uji-klinik.edit-permohonan-uji-klinik-parameter', [$id_permohonan_uji_klinik, $data->id_permohonan_uji_paket_klinik]) . '" class="dropdown-item" title="Edit">Edit</a> ';

          //   $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji_paket_klinik . '" data-nama="' . $data->parameterjenisklinik->name_parameter_jenis_klinik . '" title="Hapus">Hapus</a> ';
          // }

          $button = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">


                            </div>
                        </div>';

          return $button;
        })
        ->rawColumns(['parameter_jenis_klinik', 'total_harga', 'paket', 'action'])
        ->addIndexColumn() //increment
        ->make(true);
    }

    $item = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);
    // dd($item);
    $tgl_register = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    //cek permohonan uji klinik payment
    $payment = PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)->first();
// dd($payment);
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.parameter.pagination', [
      'id' => $id_permohonan_uji_klinik,
      'item' => $item,
      'tgl_register' => $tgl_register,
      'payment' => $payment,
    ]);
  }

  public function rules($request)
  {
    $rule = [
      'type_permohonan_uji_paket_klinik' => 'required',
    ];

    $pesan = [
      'type_permohonan_uji_paket_klinik.required' => 'Tipe paket tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create(Request $request, $id_permohonan_uji_klinik)
  {
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');

    // dd($request->all());
    $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id_permohonan_uji_klinik)->first();
    // dd($item->jenis_spesimen);
    $id = $item->id_permohonan_uji_klinik;
    $tgl_register = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');
    $code = $item->noregister_permohonan_uji_klinik;
    // $tgl_sampling = Carbon::createFromFormat('d/m/Y', $request->tgl_sampling)->isoFormat('D MMMM Y');
    $pasien = Pasien::where('id_pasien', $item->pasien_permohonan_uji_klinik)->first();

    // Cek apakah data pasien dan tanggal lahir pasien ada
    if ($pasien && $pasien->tgllahir_pasien) {
    // Buat instance Carbon dari tanggal lahir pasien
    $tanggal_lahir = Carbon::parse($pasien->tgllahir_pasien);

    // Dapatkan tanggal saat ini dengan Carbon
    $tanggal_sekarang = Carbon::now();

    // Hitung selisih umur menggunakan Carbon
    $umur_tahun = $tanggal_sekarang->diffInYears($tanggal_lahir);
    $umur_bulan = $tanggal_sekarang->diffInMonths($tanggal_lahir) % 12;
    $umur_hari = $tanggal_sekarang->diffInDays($tanggal_lahir->copy()->addYears($umur_tahun)->addMonths($umur_bulan));

    // Buat string umur yang lebih mudah dibaca
    $umur_string = "$umur_tahun tahun $umur_bulan bulan $umur_hari hari";
    } else {
      // Jika data tanggal lahir tidak ditemukan
      $umur_string = "Tanggal lahir tidak tersedia";
    }

    $parameter_jenis_klinik = ParameterJenisKlinik::with('pakets')->orderBy('created_at', 'asc')->get();

    $parameter_paket_extra = ParameterPaketExtra::with('parameterSubPaketExtra')->orderBy('created_at', 'asc')->get();

    // Load category layout from database
    $categoryLayouts = \Smt\Masterweb\Models\ParameterCategoryLayout::where('is_active', 1)
      ->orderBy('sort_order', 'asc')
      ->with(['categoryItems' => function($query) {
        $query->with('parameterPaketKlinik')
          ->orderBy('row_position', 'asc')
          ->orderBy('column_position', 'asc')
          ->orderBy('sort_order', 'asc');
      }])
      ->get();

    // Edit parameter pasien haji: jangan tampilkan BPJS/Klaim & Paket Haji komposit.
    if ((int) ($item->is_haji ?? 0) === 1) {
      $categoryLayouts->each(function ($category) {
        if (!$category->relationLoaded('categoryItems')) {
          return;
        }
        $category->setRelation(
          'categoryItems',
          $category->categoryItems->filter(function ($itemRow) {
            $paket = $itemRow->parameterPaketKlinik;
            if (!$paket) {
              return false;
            }
            $name = (string) $paket->name_parameter_paket_klinik;
            return !HajiPaketHelper::isCompositePaketHajiName($name)
              && !HajiPaketHelper::isBillingVariantPaketName($name);
          })->values()
        );
      });
    }
    // dd($categoryLayouts->toArray());

    //Ambil semua data paket klinik berdasarkan ID permohonan
    $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
    ->orderBy('parameter_paket_extra', 'desc')
      ->get();

    // dd($datas);

    $temp_datas = [];

    $temp_extra_paket = null;
    foreach($data_paket as $val){
      // dd($val->parameter_paket_extra);
      if(isset($val->parameter_paket_extra) && $temp_extra_paket != $val->parameter_paket_extra && $val->parameter_paket_extra != ''){
        $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
        // dd($extra_paket);
        $value_extra_paket = [
          "id_permohonan_uji_paket_klinik" => $val->id_permohonan_uji_paket_klinik,
          "permohonan_uji_klinik" => $val->permohonan_uji_klinik,
          "parameter_jenis_klinik" =>  $val->parameter_jenis_klinik,
          "type_permohonan_uji_paket_klinik" =>  'EP',
          "nama_parameter_paket_extra" => $extra_paket->nama_parameter_paket_extra,
          "parameter_paket_klinik" =>  $val->parameter_paket_klinik,
          "parameter_paket_extra" =>  $val->parameter_paket_extra,
          "harga_permohonan_uji_paket_klinik" =>  $extra_paket->harga_parameter_paket_extra,
          "is_prolanis_gula" =>  $val->is_prolanis_gula,
          "is_prolanis_urine" =>  $val->is_prolanis_urine,
          "is_haji" => $val->is_haji,
          "id_service_request" =>  $val->id_service_request,
          "response_service_request" =>  $val->response_service_request,
        ];

        array_push($temp_datas, $value_extra_paket);
        $temp_extra_paket = $val->parameter_paket_extra;

      }elseif($val->parameter_paket_extra == ''){
        array_push($temp_datas, $val);
      }
    }

    // dd($temp_datas);

    $data_paket_extra = $temp_datas;

    $selectedPaketExtra = collect($data_paket_extra)->pluck('parameter_paket_extra')->toArray();

  // dd($data_paket);

    $paket_array = [];
    $hasIndividualPakets = $this->permohonanHasIndividualPaketKlinik($data_paket);
    foreach ($data_paket as $val) {
      if (empty($val->parameter_paket_extra)) {
          $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();

          if ($paket) {
              if (HajiPaketHelper::isCompositePaketHajiName($paket->name_parameter_paket_klinik)) {
                if ($hasIndividualPakets) {
                  continue;
                }
                $permohonan = PermohonanUjiKlinik2::with('permohonanujiparameterklinik')->find($id_permohonan_uji_klinik);
                $expandedPakets = $permohonan
                  ? HajiPaketHelper::resolveLayoutPaketsFromPermohonan($permohonan)
                  : collect();
                foreach ($expandedPakets as $individualPaket) {
                  $paket_array[] = [
                    'name_parameter_paket_klinik' => $individualPaket->name_parameter_paket_klinik,
                  ];
                }
              } else {
                $paket_array[] = [
                  'name_parameter_paket_klinik' => $paket->name_parameter_paket_klinik,
                ];
              }
          }
      }
    }

      // dd($paket_array);

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.parameter.add-paramater', [
      'item' => $item,
      'tgl_register' => $tgl_register,
      'id' => $id,
      'code' => $code,
      'pasien' => $pasien,
      'umur_string' => $umur_string,
      'parameter_paket_extra' => $parameter_paket_extra,
      'parameter_jenis_klinik' => $parameter_jenis_klinik,
      'paket' => $paket_array,
      'paket_extra' => $selectedPaketExtra,
      'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik,
      'categoryLayouts' => $categoryLayouts
    ]);
  }

  private function resolveParameterJenisKlinikIdForPaket($packetId, $formKey = null)
  {
    if (!empty($formKey) && ParameterJenisKlinik::where('id_parameter_jenis_klinik', $formKey)->exists()) {
      return $formKey;
    }

    $bridge = ParameterPaketJenisKlinik::where('parameter_paket_klinik_id', $packetId)
      ->orderBy('sort')
      ->first();

    return $bridge ? $bridge->parameter_jenis_klinik_id : null;
  }

  public function store(Request $request, $id_permohonan_uji_klinik)
  {
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');


     // Inisialisasi total harga
     $total_harga = 0;
    //  dd($request->all());

     // Mulai DB transaction
     DB::beginTransaction();

     // Dipakai untuk mendeteksi parameter yang baru ditambahkan setelah pemeriksaan selesai.
     $id_parameter_sebelum_simpan = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
       ->pluck('id_permohonan_uji_parameter_klinik')
       ->all();

    //  try {

      // Kumpulkan semua packet_id yang ada di request (yang dicentang)
      $requested_packet_ids = [];
      if (isset($request->jenis_parameters)) {
        foreach ($request->jenis_parameters as $key => $value) {
          if (isset($value["pakets"]) && is_array($value["pakets"])) {
            foreach ($value["pakets"] as $packectParameter) {
              $array_packet = explode("_", $packectParameter);
              $packet_id = $array_packet[0];
              // Pastikan tipe data konsisten (string)
              $requested_packet_ids[] = (string)$packet_id;
            }
          }
        }
      }

      // Ambil semua paket yang ada di database untuk permohonan ini
      $existing_pakets_db = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->get();

      // Tentukan paket mana yang harus dihapus (ada di DB tapi tidak ada di request)
      $paket_ids_to_delete = [];
      foreach ($existing_pakets_db as $paket) {
        $db_packet_id = (string)$paket->parameter_paket_klinik;
        // Jika paket ada di database tapi tidak ada di request, hapus
        if (!in_array($db_packet_id, $requested_packet_ids)) {
          $paket_ids_to_delete[] = $paket->id_permohonan_uji_paket_klinik;
        }
      }

      // Paket yang hasilnya sudah diisi analis tidak boleh ikut terhapus saat edit,
      // supaya centang yang tidak sengaja lepas tidak membuang hasil yang sudah ada.
      $paket_bertahan_karena_ada_hasil = $this->paketYangSudahAdaHasil($paket_ids_to_delete);
      if (!empty($paket_bertahan_karena_ada_hasil)) {
        $paket_ids_to_delete = array_values(array_diff($paket_ids_to_delete, $paket_bertahan_karena_ada_hasil));
      }

      // Hapus parameter klinik yang terkait dengan paket yang akan dihapus
      if (!empty($paket_ids_to_delete)) {
        PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
          ->whereIn('permohonan_uji_paket_klinik', $paket_ids_to_delete)
          ->delete();
      }

      // Hapus paket klinik yang tidak ada di request
      if (!empty($paket_ids_to_delete)) {
        PermohonanUjiPaketKlinik::whereIn('id_permohonan_uji_paket_klinik', $paket_ids_to_delete)
          ->delete();
      }

      // $permohonanUjiSubParameterKlinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      // ->where('is_prolanis', 0)
      // ->delete();

      $paketExtras = $request->input('paket_extra');

     //  foreach ($paketExtras as $id => $value) {
     //      // Pisahkan value yang digabungkan dengan underscore
     //      [$id_parameter_paket_extra, $harga_parameter_paket_extra] = explode('_', $value);


     //      // Simpan data paket ekstra
     //      $paketExtra = new PermohonanUjiPaketExtraKlinik();  // Pastikan mengganti `PaketExtra` dengan model yang sesuai

     //      $paketExtra->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
     //      $paketExtra->id_parameter_paket_extra = $id_parameter_paket_extra;
     //      $paketExtra->harga_permohonan_uji_paket_extra = $harga_parameter_paket_extra;
     //      $paketExtra->save();

     //      // Simpan sub paketnya jika ada

     //     $dataSubPaketExtra = ParameterSubPaketExtra::where('id_parameter_paket_extra', $id_parameter_paket_extra)->get();
     //     foreach ($dataSubPaketExtra as $subPaket) {
     //         $subPaketExtra = new PermohonanUjiSubPaketExtraKlinik();
     //         $subPaketExtra->id_permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
     //         $subPaketExtra->id_permohonan_paket_extra = $paketExtra->id_permohonan_uji_paket_extra_klinik;
     //         // $subPaketExtra->id_parameter_sub_paket_klinik = $subPaket->id_parameter_sub_paket_klinik;
     //         $subPaketExtra->id_parameter_paket_klinik = $subPaket->id_parameter_paket_klinik;
     //         $subPaketExtra->save();

     //         // dd($subPaketExtra);
     //     }
     //  }

     $total_harga_paket_extra=0;


     $no_ajaib = array();


     $data_jenis_parameter = $request->input('jenis_parameters');


     if(!empty($paketExtras)){
       foreach ($paketExtras as $id => $value) {

           // Pisahkan value yang digabungkan dengan underscore
           [$id_parameter_paket_extra, $harga_parameter_paket_extra] = explode('_', $value);
           //total harga paket extra
           $total_harga_paket_extra += $harga_parameter_paket_extra;

           $dataSubPaketExtra = ParameterSubPaketExtra::where('id_parameter_paket_extra', $id_parameter_paket_extra)->get();
          //  dd($dataSubPaketExtra);
           foreach ($dataSubPaketExtra as $subPaket) {
               $paketExtra = new PermohonanUjiPaketKlinik();
               $paketExtra->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
               $paketExtra->parameter_paket_extra = $id_parameter_paket_extra;
               $paketExtra->type_permohonan_uji_paket_klinik = "P";
               $paketExtra->harga_permohonan_uji_paket_klinik = $subPaket->parameterPaketKlinik->harga_parameter_paket_klinik;
               $paketExtra->parameter_paket_klinik = $subPaket->id_parameter_paket_klinik;
               $paketExtra->save();

               // Tambahkan packet_id ke array $paket_extra
               $packet_id = $subPaket->id_parameter_paket_klinik;
               array_push($no_ajaib, $packet_id);
           }
       }

     }

      //  dd($packet_id);
       // dd($request->all());
       // Validasi input




       // store ke permohonan uji paket klinik

       // Hitung total dari SEMUA parameter yang dipilih di request (termasuk yang sudah ada di DB)
       $price_total = 0;
       $name_permohonan = "";

       if (isset($request->jenis_parameters)) {
         foreach ($request->jenis_parameters as $key => $value) {
           if (!isset($value["pakets"]) || !is_array($value["pakets"])) {
             continue;
           }

           foreach ($value["pakets"] as $packectParameter) {
             $array_packet = explode("_", $packectParameter);
             if (count($array_packet) < 2) {
               continue;
             }

             $packet_id = $array_packet[0];
             $price = $array_packet[1];
             $price_total += (int) $price;

             $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $packet_id)
               ->whereNull('deleted_at')
               ->first();
             if ($parameterpaketklinik) {
               $name_permohonan .= $parameterpaketklinik->name_parameter_paket_klinik . " ";
             }
           }
         }
       }

      // Paket yang dipertahankan karena sudah ada hasil tetap ikut dihitung, kalau tidak
      // total biaya jadi lebih kecil dari isi permohonan yang sebenarnya.
      if (!empty($paket_bertahan_karena_ada_hasil)) {
        $paket_bertahan = PermohonanUjiPaketKlinik::whereIn('id_permohonan_uji_paket_klinik', $paket_bertahan_karena_ada_hasil)
          ->get();

        foreach ($paket_bertahan as $paket) {
          $price_total += (int) $paket->harga_permohonan_uji_paket_klinik;

          if ($paket->parameterpaketklinik) {
            $name_permohonan .= $paket->parameterpaketklinik->name_parameter_paket_klinik . " ";
          }
        }
      }

      // Ambil semua paket yang sudah ada di database untuk permohonan ini
      // Ini untuk mencegah duplikasi saat menyimpan
       $existing_pakets = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))
         ->pluck('parameter_paket_klinik')
         ->map(function ($id) {
           return (string) $id;
         })
         ->toArray();

       if (isset($request->jenis_parameters)) {
         foreach ($request->jenis_parameters as $key => $value) {
           if (!isset($value["pakets"]) || !is_array($value["pakets"])) {
             continue;
           }

           foreach ($value["pakets"] as $packectParameter) {
             $array_packet = explode("_", $packectParameter);
             if (count($array_packet) < 2) {
               continue;
             }

             $packet_id = (string) $array_packet[0];
             $price = $array_packet[1];

             $paketMaster = ParameterPaketKlinik::find($packet_id);
             if ($paketMaster && (
               HajiPaketHelper::isCompositePaketHajiName($paketMaster->name_parameter_paket_klinik)
               || (
                 (int) (optional(PermohonanUjiKlinik2::find($id_permohonan_uji_klinik))->is_haji ?? 0) === 1
                 && HajiPaketHelper::isBillingVariantPaketName((string) $paketMaster->name_parameter_paket_klinik)
               )
             )) {
               continue;
             }

             // Cek apakah paket ini sudah ada di database (untuk Paket Haji, Prolanis, dll)
             if (in_array($packet_id, $existing_pakets, true)) {
               continue;
             }

             $post_paket = new PermohonanUjiPaketKlinik();
             $post_paket->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
             $post_paket->parameter_jenis_klinik = $this->resolveParameterJenisKlinikIdForPaket($packet_id, $key);
             $post_paket->type_permohonan_uji_paket_klinik = "P";
             $post_paket->parameter_paket_klinik = $packet_id;
             $post_paket->harga_permohonan_uji_paket_klinik = $price;

             $post_paket->save();

             // Tambahkan ke list existing pakets untuk mencegah duplikasi dalam request yang sama
             $existing_pakets[] = $packet_id;
             array_push($no_ajaib, $packet_id);
           }
         }
       }



      $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($request->permohonan_uji_klinik);
      $item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik= $price_total+$total_harga_paket_extra;

      // dd(Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String());
      $encounterId = null;
      $encounterDisplay = "Permohonanan Pengujian ".$name_permohonan;
      $practitionerId = null;
      $practitionerName = null;
      if (isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat)) {
        # code...
        if ( config('services.satu_sehat.version')=="prd") {

          # code...

          $pendaftaran=VerificationActivitySample::where('is_klinik',($request->permohonan_uji_klinik)) ->where('id_verification_activity', 1)->first();

          // $satusehat = SatuSehat::first();
          $location_satusehat= SatuSehatLocation::where('name_satusehat_location',"LIKE",'%Administrasi%')->where('version_satusehat_location','prd')->first();

          // Normalize nama petugas untuk matching (hilangkan koma dari kedua sisi)
          $namaPetugas_normalized = str_replace(',', '', $pendaftaran->nama_petugas);
          $practitioner_satusehat = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();

          // Jika tidak ditemukan dengan like, coba cari dari ms_petugas
          // Normalisasi nama di database juga (hilangkan koma) untuk perbandingan
          if (!$practitioner_satusehat) {
            $petugas = Petugas::where('nama', 'like', '%' . $namaPetugas_normalized . '%')->first();
            if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
              $practitioner_satusehat = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
            }
          }

          // Pastikan $practitioner_satusehat tidak null sebelum mengakses properti
          if ($practitioner_satusehat && isset($practitioner_satusehat->code_satu_sehat_practitioner)) {
            $practitionerId = $practitioner_satusehat->code_satu_sehat_practitioner;
            $practitionerName = $practitioner_satusehat->name_satu_sehat_practitioner ?? $pendaftaran->name_satu_sehat_practitioner;
          } else {
            // Fallback jika practitioner tidak ditemukan
            Log::warning('Practitioner SatuSehat not found for petugas: ' . $pendaftaran->name_satu_sehat_practitioner);
            $practitionerId = null;
            $practitionerName = $pendaftaran->name_satu_sehat_practitioner;
          }

          //
          // dd($satusehat);
          $data = [
              "resourceType" => "Encounter",
              "status" => "arrived", // Status Encounter (mulai, selesai, atau dibatalkan)
              "subject" => [
                  "reference" => "Patient/".$item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat, // Tambahkan ID Pasien yang sesuai
                  "display" => $item_permohonan_uji_klinik->pasien->nama_pasien
              ],
              "class" => [
                  "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                  "code" => "PRENC",
                  "display" => "Permohonanan Pengujian ".$name_permohonan
              ],
              "participant" => [
                  [
                      "type" => [
                          [
                              "coding" => [
                                  [
                                      "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                                      "code" => "ADM", // Tipe partisipasi: pendaftaran, pengambilan sample, pemeriksaan, verifikasi
                                      "display" => "pendaftaran"
                                  ]
                              ]
                          ]
                      ],
                      "individual" => [
                          "reference" => "Practitioner/".($practitioner_satusehat && isset($practitioner_satusehat->code_satu_sehat_practitioner) ? $practitioner_satusehat->code_satu_sehat_practitioner : ''), // Tambahkan UUID Praktisi yang sesuai
                          "display" => ($practitioner_satusehat && isset($practitioner_satusehat->name_satu_sehat_practitioner) ? $practitioner_satusehat->name_satu_sehat_practitioner : $pendaftaran->name_satu_sehat_practitioner)
                      ]
                  ]
              ],
              "period" => [
                  "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
              ],
              "location" => [
                  [
                      "location" => [
                          "reference" => "Location/".$location_satusehat->kode_satusehat_location, // Lokasi yang sesuai
                          "display" => $location_satusehat->name_satusehat_location
                      ]
                  ]
              ],
              "statusHistory" => [
                  [
                      "status" => "arrived",
                      "period" => [
                          "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String(),
                          "end" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
                      ]
                  ]
              ],
              "serviceProvider" => [
                  "reference" => "Organization/".config('services.satu_sehat.org_id') // Tambahkan ID Organisasi yang sesuai
              ],
              "identifier" => [
                  [
                      "system" => "http://sys-ids.kemkes.go.id/encounter/".config('services.satu_sehat.org_id'), // Sistem Identifier
                      "value" =>  $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik
                  ]
              ]
          ];
          // dd($data);

          $response = $this->satuSehatHelper->post('Encounter',$data);
          // dd(json_encode($response));
          // if (!isset($item_permohonan_uji_klinik->id_satu_sehat_encounter )) {
          //   # code...

          // // }else{
          // dd($response);
          if ($response['status_code'] == '201') {
            # code...

            $encounterId = $response['body']["id"];
            $item_permohonan_uji_klinik->id_satu_sehat_encounter=$response['body']["id"];
            $item_permohonan_uji_klinik->encounter_json_satu_sehat=json_encode($response['body']);
            $response=$item_permohonan_uji_klinik->save();
            $satu_sehat=true;
          }else{
            // Meskipun encounter gagal, tetap set $satu_sehat=true jika ada id_pasien_satu_sehat
            // karena ServiceRequest tetap bisa dibuat tanpa encounter (field optional)
            $satu_sehat=true;
            // Log warning untuk tracking
            Log::warning('Encounter creation failed but continuing with ServiceRequest. Response: ' . json_encode($response));
          }
          // }

          // dd($response);

        } else {
          // Jika bukan production, tetap set $satu_sehat jika ada id_pasien_satu_sehat
          $satu_sehat = isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat);
        }
      } else {
        // Jika tidak ada id_pasien_satu_sehat, set $satu_sehat = null
        $satu_sehat = null;
      }

      //      // dd($item_permohonan_uji_klinik);

      //      // dd($satusehat);
      //      $data = [
      //          "resourceType" => "Encounter",
      //          "status" => "arrived", // Status Encounter (mulai, selesai, atau dibatalkan)
      //          "subject" => [
      //              "reference" => "Patient/".$item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat, // Tambahkan ID Pasien yang sesuai
      //              "display" => $item_permohonan_uji_klinik->pasien->nama_pasien
      //          ],
      //          "class" => [
      //              "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
      //              "code" => "PRENC",
      //              "display" => "Permohonanan Pengujian ".$name_permohonan
      //          ],
      //          "participant" => [
      //              [
      //                  "type" => [
      //                      [
      //                          "coding" => [
      //                              [
      //                                  "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
      //                                  "code" => "ADM", // Tipe partisipasi: pendaftaran, pengambilan sample, pemeriksaan, verifikasi
      //                                  "display" => "pendaftaran"
      //                              ]
      //                          ]
      //                      ]
      //                  ],
      //                  "individual" => [
      //                      "reference" => "Practitioner/".$practitioner_satusehat->code_satu_sehat_practitioner, // Tambahkan UUID Praktisi yang sesuai
      //                      "display" => $practitioner_satusehat->name_satu_sehat_practitioner
      //                  ]
      //              ]
      //          ],
      //          "period" => [
      //              "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
      //          ],
      //          "location" => [
      //              [
      //                  "location" => [
      //                      "reference" => "Location/".$location_satusehat->kode_satusehat_location, // Lokasi yang sesuai
      //                      "display" => "Ruang Administrasi Laboratorium Kesehatan Boyolali"
      //                  ]
      //              ]
      //          ],
      //          "statusHistory" => [
      //              [
      //                  "status" => "arrived",
      //                  "period" => [
      //                      "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
      //                  ]
      //              ]
      //          ],
      //          "serviceProvider" => [
      //              "reference" => "Organization/".config('services.satu_sehat.org_id') // Tambahkan ID Organisasi yang sesuai
      //          ],
      //          "identifier" => [
      //              [
      //                  "system" => "http://sys-ids.kemkes.go.id/encounter/".config('services.satu_sehat.org_id'), // Sistem Identifier
      //                  "value" =>  $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik
      //              ]
      //          ]
      //      ];
      //      // dd($data);

      //      $response = $this->satuSehatHelper->post('Encounter',$data);
      //      // dd(json_encode($response));
      //      // if (!isset($item_permohonan_uji_klinik->id_satu_sehat_encounter )) {
      //      //   # code...

      //      // }else{
      //        $item_permohonan_uji_klinik->id_satu_sehat_encounter=$response["id"];
      //        $item_permohonan_uji_klinik->encounter_json_satu_sehat=json_encode($response);
      //        $item_permohonan_uji_klinik->save();
      //      // }

      //      // dd($response);

      //    }
      //  }
       $item_permohonan_uji_klinik->save();

       foreach ($no_ajaib as $key => $value) {
         $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $value)
           ->whereNull('deleted_at')
           ->first();


       // if (isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat)) {


       // else{
       //     $satusehat= SatuSehat::first();

       //     $location_satusehat= SatuSehatLocation::where('name_satusehat_location','Administrasi')->where('version_satusehat_location','sttg')->first();


       //     $data = [
       //           "resourceType" => "Encounter",
       //           "status" => "arrived", // Status Encounter (mulai, selesai, atau dibatalkan)
       //           "subject" => [
       //               "reference" => "Patient/".$item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat, // Tambahkan ID Pasien yang sesuai
       //               "display" => $item_permohonan_uji_klinik->pasien->nama_pasien
       //           ],
       //           "class" => [
       //               "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
       //               "code" => "PRENC",
       //               "display" => "Permohonanan Pengujian ".$name_permohonan
       //           ],
       //           "participant" => [
       //               [
       //                   "type" => [
       //                       [
       //                           "coding" => [
       //                               [
       //                                   "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
       //                                   "code" => "ADM", // Tipe partisipasi: pendaftaran, pengambilan sample, pemeriksaan, verifikasi
       //                                   "display" => "pendaftaran"
       //                               ]
       //                           ]
       //                       ]
       //                   ],
       //                   "individual" => [
       //                       "reference" => "Practitioner/".$satusehat->Practition_uuid, // Tambahkan UUID Praktisi yang sesuai
       //                       "display" => "Administrator"
       //                   ]
       //               ]
       //           ],
       //           "period" => [
       //               "start" => "2024-08-20T12:00:00+07:00"
       //           ],
       //           "location" => [
       //               [
       //                   "location" => [
       //                       "reference" => "Location/".$location_satusehat->kode_satusehat_location, // Lokasi yang sesuai
       //                       "display" => "Ruang Administrasi Laboratorium Kesehatan Boyolali"
       //                   ]
       //               ]
       //           ],
       //           "statusHistory" => [
       //               [
       //                   "status" => "arrived",
       //                   "period" => [
       //                       "start" => "2022-06-15T07:00:00+07:00"
       //                   ]
       //               ]
       //           ],
       //           "serviceProvider" => [
       //               "reference" => "Organization/".$satusehat->Org_lab // Tambahkan ID Organisasi yang sesuai
       //           ],
       //           "identifier" => [
       //               [
       //                   "system" => "http://sys-ids.kemkes.go.id/encounter/".$satusehat->Org_lab, // Sistem Identifier
       //                   "value" =>  $item_permohonan_uji_klinik->code_permohonan_uji
       //               ]
       //           ]
       //       ];

       //       if (isset($satusehat)) {

       //         $client = new Client([
       //           'base_uri' => env('URL_SATUSEHAT'),// Set base URI dari environment variable
       //         ]);

       //         # code...
       //         try {

       //             // Melakukan POST request dengan JSON body
       //             $response = $client->post( env('BASE_URL_SATUSEHAT').'/Encounter', [
       //                 'headers' => [
       //                     'Authorization' => 'Bearer ' . $satusehat->token, // Gunakan token jika dibutuhkan
       //                     'Content-Type' => 'application/json',
       //                     'Accept' => 'application/json',
       //                 ],
       //                 'json' => $data, // Mengirim data dalam format JSON
       //             ]);

       //             // Mendapatkan body response
       //             $responseBody = json_decode($response->getBody(), true);
       //             dd(  $responseBody);

       //             // $post_newpasien->id_pasien_satu_sehat=$responseBody["data"]["patient_id"];


       //             // $store_pasien = $post_newpasien->save();
       //             // $post->pasien_permohonan_uji_klinik = $post_newpasien->id_pasien;
       //         } catch (RequestException $e) {
       //             // Menangani error
       //             // dd($e->getResponse()->getBody()->getContents());


       //             if ($e->hasResponse()) {
       //                 return response()->json(['status' => false, 'pesan' => $e->getResponse()->getBody()->getContents(), 'error' => $e->getMessage()], 200);
       //             } else {
       //                 return response()->json(['status' => false, 'pesan' => $e->getMessage(), 'error' => $e->getMessage()], 200);
       //             }

       //             // $store_pasien = $post_newpasien->save();

       //         }
       //       }

       //   }
       // }



         $detailPacket = [];
         $packetName = $parameterpaketklinik->name_parameter_paket_klinik;
         foreach ($parameterpaketklinik->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
           # code...
           #looping satuan parameter dari paket
           foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

             $code = \Smt\Masterweb\Helpers\Smt::pickLoincForContext(
               $value_parametersatuanpaketklinik->parametersatuanklinik ?? null,
               $item_permohonan_uji_klinik
             );
             $detailPacket[] = [
               "system" => "http://loinc.org",
               "code" => $code,
               "display" =>
                 $value_parametersatuanpaketklinik->parametersatuanklinik->name_parameter_satuan_klinik,
             ];




             // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
             $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
             $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

             $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
               ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
               ->where('is_khusus_baku_mutu', '0')
               ->leftJoin('ms_library', function ($join) {
                 $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                   ->whereNull('ms_library.deleted_at')
                   ->whereNull('tb_baku_mutu.deleted_at');
               })
               ->first();




             // cek dulu apakah ada data dengan data khusus sperti general atau specific
             if ($check_parameter_by_baku_mutu) {
               $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
             } else {
               $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                 ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                 ->where('is_khusus_baku_mutu', '1')
                 ->where('gender_baku_mutu', $pasien_gender=="male"?"L":"P")
                 ->where(function ($query) use ($pasien_umur) {
                   $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                     ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                 })
                 ->leftJoin('ms_library', function ($join) {
                   $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                     ->whereNull('ms_library.deleted_at')
                     ->whereNull('tb_baku_mutu.deleted_at');
                 })
                 ->first();
               if (!isset($item_parameter_by_baku_mutu)) {
                 $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                   ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                   ->where('is_khusus_baku_mutu', '1')
                   ->leftJoin('ms_library', function ($join) {
                     $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                       ->whereNull('ms_library.deleted_at')
                       ->whereNull('tb_baku_mutu.deleted_at');
                   })
                   ->where('gender_baku_mutu', $pasien_gender=="male"?"L":"P")
                   ->first();
               }

             }


             $permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('parameter_paket_klinik', $value)
             ->where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))->first();


             $post_parameter = new PermohonanUjiParameterKlinik();
             $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
             $post_parameter->permohonan_uji_paket_klinik = $permohonan_uji_paket_klinik->id_permohonan_uji_paket_klinik;
             $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
             $post_parameter->parameter_paket_klinik = $value;
             $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
             $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;




             $post_parameter->method_permohonan_uji_parameter_klinik = \Smt\Masterweb\Helpers\Smt::resolveInitialMethodForParameter(
               $value_parametersatuanpaketklinik->parametersatuanklinik,
               PermohonanUjiKlinik2::find($request->post('permohonan_uji_klinik'))
             );

             $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
             $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;

             $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

             if (isset($item_parameter_by_baku_mutu->unit_id)) {
               $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
               $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
             } else {
               $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
               $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
             }

             $post_parameter->keterangan_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->ket_default_parameter_satuan_klinik;

             // dd($post_parameter);

             $simpan_post_parameter = $post_parameter->save();

             // // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
             $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
               ->get();

             if (count($data_parameter_subsatuan) > 0) {
               foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                 $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                 $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                 $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                 // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                 if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                   $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                     ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                     ->first();

                   $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                   $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                 }

                 $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
               }
             }
           }
         }

         // #looping jenis parameter paket
         // foreach ($parameterpaketklinik->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
         //   # code...
         //   #looping satuan parameter dari paket
         //   foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

         //     // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
         //     $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
         //     $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

         //     $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
         //       ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
         //       ->where('is_khusus_baku_mutu', '0')
         //       ->first();
         //     // cek dulu apakah ada data dengan data khusus sperti general atau specific
         //     if ($check_parameter_by_baku_mutu) {
         //       $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
         //     } else {
         //       $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
         //         ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
         //         ->where('is_khusus_baku_mutu', '1')
         //         ->where('gender_baku_mutu', $pasien_gender)
         //         ->where(function ($query) use ($pasien_umur) {
         //           $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
         //             ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
         //         })
         //         ->first();
         //       if (!isset($item_parameter_by_baku_mutu)) {
         //         $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
         //           ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
         //           ->where('is_khusus_baku_mutu', '1')
         //           ->where('gender_baku_mutu', $pasien_gender)
         //           ->first();
         //       }
         //     }

         //     $post_parameter = new PermohonanUjiParameterKlinik();
         //     $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
         //     $post_parameter->permohonan_uji_paket_klinik = $value;
         //     $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
         //     $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key + 1];
         //     $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
         //     $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

         //     $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
         //     $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;

         //     $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

         //     if (isset($item_parameter_by_baku_mutu->unit_id)) {
         //       $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
         //       $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
         //     } else {
         //       $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
         //       $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
         //     }

         //     $simpan_post_parameter = $post_parameter->save();

         //     // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
         //     $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
         //       ->get();

         //     if (count($data_parameter_subsatuan) > 0) {
         //       foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
         //         $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
         //         $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
         //         $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

         //         // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
         //         if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
         //           $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
         //             ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
         //             ->first();

         //           $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
         //           $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
         //         }

         //         $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
         //       }
         //     }
         //   }
         // }

        // ServiceRequest
        // dd($satu_sehat);

        if (isset($item_permohonan_uji_klinik)){
          $encounterId = $item_permohonan_uji_klinik->id_satu_sehat_encounter;
          $data = json_decode($item_permohonan_uji_klinik->encounter_json_satu_sehat, true);
          $encounterDisplay = "Permohonanan Pengujian ".$packetName;
          $practitionerId = null;
          $practitionerName = null;
         if (isset($data['participant'][0]['individual']['reference']) && isset($data['participant'][0]['individual']['display'])) {
           $practitionerId = str_replace('Practitioner/', '', $data['participant'][0]['individual']['reference']);
           $practitionerName = $data['participant'][0]['individual']['display'];
         }

        //  dd($parameterpaketklinik);
        $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::query()->where('permohonan_uji_klinik', '=',  $request->post('permohonan_uji_klinik'))->where('parameter_paket_klinik', '=', $parameterpaketklinik->id_parameter_paket_klinik)->first();

        // if (isset($permohonanUjiPaketKlinik)){
        // dd($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat);
        // if ($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat=="" && !isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat)) {
        //   # code...
        //   dd();
        // }

         // Skip createServiceRequest if practitionerId is NULL (untuk doctor_type='lab' yang tidak punya practitioner data)
         // Tambahkan check: practitionerId TIDAK NULL dan practitionerName TIDAK NULL
         // Hanya kirim ke SatuSehat jika VERSION_SATUSEHAT == 'prd'
         if (
           config('services.satu_sehat.version') == 'prd' &&
           isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat) &&
           $item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat != "" &&
           isset($encounterId) &&
           isset($practitionerId) && $practitionerId !== null &&
           isset($practitionerName) && $practitionerName !== null
         ) {
           try {
             $this->createServiceRequest(
               $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik,
               $item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat,
               $encounterId,
               $encounterDisplay,
               $practitionerId,
               $practitionerName,
               Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String(),
               $detailPacket,
               $packetName,
               $request->post('permohonan_uji_klinik'),
               $parameterpaketklinik->id_parameter_paket_klinik
             );
           } catch (\Exception $e) {
             // Log error but don't stop the process - ServiceRequest is optional for local data
             \Log::error('Error creating ServiceRequest (continuing without it): ' . $e->getMessage(), [
               'permohonan_uji_klinik_id' => $request->post('permohonan_uji_klinik'),
               'parameter_paket_klinik_id' => $parameterpaketklinik->id_parameter_paket_klinik,
               'exception' => $e->getTraceAsString()
             ]);
             // Continue without ServiceRequest - local data is more important
             // Only throw if in production and SatuSehat is critical
             if (config('services.satu_sehat.version') == 'prd') {
               // In production, log warning but continue - don't break the user flow
               \Log::warning('ServiceRequest creation failed in production but continuing', [
                 'permohonan_uji_klinik_id' => $request->post('permohonan_uji_klinik'),
                 'error' => $e->getMessage()
               ]);
             }
           }
          }
        }

       }

       // foreach ($data_jenis_parameter as $jenis_parameter_id => $pakets) {
       //     $total_harga_jenis = 0; // Inisialisasi total harga untuk jenis parameter

       //     // Simpan data ke model PermohonanUjiPaketKlinik2 dengan total harga jenis
       //     $data_jenis = [
       //         'id_permohonan_uji_klinik' => $request->permohonan_uji_klinik,
       //         'id_jenis_parameter_klinik' => $jenis_parameter_id,
       //         'total_harga' => $total_harga_jenis, // Akan di-update nanti setelah menghitung total harga
       //     ];
       //     $permohonan_uji_paket = PermohonanUjiPaketKlinik2::create($data_jenis);
       //     $new_paket_id = $permohonan_uji_paket->id_permohonan_uji_paket_klinik;

       //     foreach ($pakets['pakets'] as $parameter_paket_id) {
       //         // Ambil harga dari ParameterPaketKlinik
       //         $harga_parameter_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $parameter_paket_id)
       //             ->value('harga_parameter_paket_klinik'); // Ambil nilai langsung, bukan objek

       //         if ($harga_parameter_paket === null) {
       //             // Tangani jika harga tidak ditemukan
       //             \Log::warning("Harga tidak ditemukan untuk paket parameter ID: $parameter_paket_id");
       //             continue;
       //         }

       //         // Tambahkan harga ke total harga dan total harga jenis
       //         $total_harga += $harga_parameter_paket;
       //         $total_harga_jenis += $harga_parameter_paket;

       //         // Simpan data ke model PermohonanUjiParameterKlinik2
       //         $data_paket = [
       //             'id_permohonan_uji_klinik' => $request->permohonan_uji_klinik,
       //             'id_jenis_parameter_klinik' => $jenis_parameter_id,
       //             'id_permohonan_uji_paket_klinik' => $new_paket_id,
       //             'id_parameter_paket_klinik' => $parameter_paket_id,
       //             'harga' => $harga_parameter_paket,
       //         ];

       //         PermohonanUjiParameterKlinik2::create($data_paket);
       //     }

       //     // Update total harga jenis di PermohonanUjiPaketKlinik2
       //     $permohonan_uji_paket->total_harga = $total_harga_jenis;
       //     $permohonan_uji_paket->save();
       // }

       // // Update total harga di PermohonanUjiKlinik2
       // $update_permohonan_uji_klinik = PermohonanUjiKlinik2::findOrFail($request->permohonan_uji_klinik);
       // $update_permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $total_harga;
       // $update_permohonan_uji_klinik->status_permohonan_uji_klinik = 'ANALIS';
       // $update_permohonan_uji_klinik->save();

      // Update status registrasi menjadi "Sudah Terdiagnosis" jika ada pemeriksaan yang dipilih
      // Cek apakah ada pemeriksaan yang dipilih (baik dari jenis_parameters maupun paket_extra)
      $hasSelectedExamination = false;

      // Cek dari jenis_parameters
      if (isset($request->jenis_parameters) && !empty($request->jenis_parameters)) {
        foreach ($request->jenis_parameters as $key => $value) {
          if (isset($value["pakets"]) && is_array($value["pakets"]) && count($value["pakets"]) > 0) {
            $hasSelectedExamination = true;
            break;
          }
        }
      }

      // Cek dari paket_extra jika belum ada pemeriksaan yang dipilih
      if (!$hasSelectedExamination && !empty($paketExtras)) {
        $hasSelectedExamination = true;
      }

      // Cek juga apakah ada paket yang sudah tersimpan di database (untuk kasus update)
      if (!$hasSelectedExamination) {
        $existing_pakets_count = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->permohonan_uji_klinik)->count();
        if ($existing_pakets_count > 0) {
          $hasSelectedExamination = true;
        }
      }

      // Jika ada pemeriksaan yang dipilih dan doctor_type adalah 'lab', update done_register menjadi true
      if ($hasSelectedExamination && $item_permohonan_uji_klinik->doctor_type == 'lab') {
        $item_permohonan_uji_klinik->done_register = true;
        $item_permohonan_uji_klinik->save();
      }

      $dibuka_lagi_untuk_analis = $this->bukaLagiAnalisJikaAdaParameterBaru(
        $id_permohonan_uji_klinik,
        $id_parameter_sebelum_simpan
      );

      // Commit transaction
      DB::commit();

      // Get permohonan data for payment modal
      $permohonan = PermohonanUjiKlinik2::with('pasien')->find($request->permohonan_uji_klinik);

      // Get biaya_pengambilan_sampel directly from database
      $biaya_pengambilan_sampel_raw = DB::table('tb_permohonan_uji_klinik_2')
        ->where('id_permohonan_uji_klinik', $request->permohonan_uji_klinik)
        ->value('biaya_pengambilan_sampel');

      $biaya_pengambilan_sampel = (int) ($biaya_pengambilan_sampel_raw ?? 0);

      // Calculate total akhir: harga parameter + biaya pengambilan sampel
      $total_harga_parameter = $permohonan->total_harga_permohonan_uji_klinik;
      $total_akhir = $total_harga_parameter + $biaya_pengambilan_sampel;

      $selectedItems = PermohonanUjiPaketKlinik::with([
        'parameterpaketklinik',
        'parameterpaketextra',
        'permohonanujiparameterklinik.jenisparameterklinik',
        'permohonanujiparameterklinik.parametersatuanklinik',
      ])
        ->where('permohonan_uji_klinik', $request->permohonan_uji_klinik)
        ->orderBy('created_at', 'asc')
        ->get();

      $items = [];
      foreach ($selectedItems as $selectedItem) {
        $isExtra = !empty($selectedItem->parameter_paket_extra);

        if ($isExtra) {
          $items[] = [
            'type' => 'Paket Extra',
            'name' => $selectedItem->parameterpaketextra->nama_parameter_paket_extra ?? '-',
            'harga' => (int) ($selectedItem->harga_permohonan_uji_paket_klinik ?? 0),
          ];
          continue;
        }

        if ($selectedItem->parameterpaketklinik) {
          $items[] = [
            'type' => 'Paket',
            'name' => $selectedItem->parameterpaketklinik->name_parameter_paket_klinik ?? '-',
            'harga' => (int) ($selectedItem->harga_permohonan_uji_paket_klinik ?? 0),
          ];
        }

        foreach ($selectedItem->permohonanujiparameterklinik as $selectedParameter) {
          $parameterName = $selectedParameter->parametersatuanklinik->name_parameter_satuan_klinik
            ?? $selectedParameter->jenisparameterklinik->name_parameter_jenis_klinik
            ?? '-';

          $items[] = [
            'type' => 'Parameter',
            'name' => $parameterName,
            'harga' => (int) ($selectedParameter->harga_permohonan_uji_parameter_klinik ?? 0),
          ];
        }
      }

      $payment_data = [
        'id_permohonan_uji_klinik' => $permohonan->id_permohonan_uji_klinik,
        'nama_pasien' => $permohonan->pasien->nama_pasien ?? '-',
        'alamat_pasien' => $permohonan->pasien ? \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($permohonan->pasien) : '-',
        'total_harga_parameter' => $total_harga_parameter,
        'biaya_pengambilan_sampel' => $biaya_pengambilan_sampel,
        'total_harga' => $total_akhir,
        'total_harga_custom' => rupiah($total_akhir),
        'nota_petugas' => auth()->id(),
        'nota_namapetugas' => auth()->user()->name,
        'items' => $items,
      ];

      $pesan = "Parameter permohonan uji klinik berhasil disimpan!";
      if ($dibuka_lagi_untuk_analis) {
        $pesan .= " Karena ada parameter baru, status permohonan dikembalikan ke ANALIS untuk pengisian hasil.";
      }
      if (!empty($paket_bertahan_karena_ada_hasil)) {
        $pesan .= " " . count($paket_bertahan_karena_ada_hasil) . " paket tetap dipertahankan karena hasilnya sudah terisi.";
      }

      return response()->json([
        'status' => true,
        'pesan' => $pesan,
        'show_payment' => false,
        'payment_data' => $payment_data,
        'dibuka_lagi_untuk_analis' => $dibuka_lagi_untuk_analis,
        'paket_dipertahankan' => count($paket_bertahan_karena_ada_hasil)
      ], 200);
    //  } catch (\Exception $e) {
    //    // Jika ada kesalahan, rollback transaction
    //    DB::rollBack();

    //    // Log error dan kirim response dengan status 500
    //    \Log::error('Error menyimpan parameter permohonan uji klinik: ' . $e->getMessage(), [
    //      'permohonan_uji_klinik' => $request->permohonan_uji_klinik,
    //      'jenis_parameters' => $request->jenis_parameters,
    //    ]);

    //    return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!', 'error' => $e->getMessage()], 500);
    //  }
  }

  /**
   * Ambil paket yang parameternya sudah terisi hasil, sehingga tidak boleh ikut dihapus.
   *
   * @param  array<int, string>  $paketIds
   * @return array<int, string>
   */
  private function paketYangSudahAdaHasil(array $paketIds): array
  {
    if (empty($paketIds)) {
      return [];
    }

    return PermohonanUjiParameterKlinik::whereIn('permohonan_uji_paket_klinik', $paketIds)
      ->whereNotNull('hasil_permohonan_uji_parameter_klinik')
      ->whereNotIn('hasil_permohonan_uji_parameter_klinik', ['', '-'])
      ->distinct()
      ->pluck('permohonan_uji_paket_klinik')
      ->map(function ($id) {
        return (string) $id;
      })
      ->all();
  }

  /**
   * Parameter yang ditambahkan setelah pemeriksaan ditutup harus kembali ke antrean analis,
   * kalau tidak hasil barunya tidak pernah diisi tapi permohonan sudah berstatus SELESAI.
   * Hasil yang sudah tersimpan tidak disentuh.
   *
   * @param  array<int, string>  $idParameterSebelumSimpan
   */
  private function bukaLagiAnalisJikaAdaParameterBaru($idPermohonanUjiKlinik, array $idParameterSebelumSimpan): bool
  {
    $permohonan = PermohonanUjiKlinik2::find($idPermohonanUjiKlinik);

    if (!$permohonan || $permohonan->status_permohonan_uji_klinik !== 'SELESAI') {
      return false;
    }

    $adaParameterBaruBelumTerisi = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $idPermohonanUjiKlinik)
      ->when(!empty($idParameterSebelumSimpan), function ($query) use ($idParameterSebelumSimpan) {
        return $query->whereNotIn('id_permohonan_uji_parameter_klinik', $idParameterSebelumSimpan);
      })
      ->where(function ($query) {
        $query->whereNull('hasil_permohonan_uji_parameter_klinik')
          ->orWhere('hasil_permohonan_uji_parameter_klinik', '')
          ->orWhere('hasil_permohonan_uji_parameter_klinik', '-');
      })
      ->exists();

    if (!$adaParameterBaruBelumTerisi) {
      return false;
    }

    $permohonan->status_permohonan_uji_klinik = 'ANALIS';
    $permohonan->save();

    // Step 3 (Pemeriksa Sampel) ditutup saat analis menekan Selesai — buka lagi
    // supaya permohonan keluar dari tab selesai dan bisa dikerjakan ulang.
    VerificationActivitySample::where('is_klinik', $idPermohonanUjiKlinik)
      ->where('id_verification_activity', 3)
      ->where(function ($query) {
        $query->where('resampling', 0)->orWhereNull('resampling');
      })
      ->update(['is_done' => 0]);

    Log::info('Permohonan dibuka lagi ke ANALIS karena ada parameter baru', [
      'permohonan_uji_klinik' => $idPermohonanUjiKlinik,
      'nomor_register' => $permohonan->noregister_permohonan_uji_klinik,
    ]);

    return true;
  }

  private function createServiceRequest($identifier, $patientId, $encounterId, $encounterDisplay, $practitionerId, $practitionerName, $date, $coding, $text, $permohonanUjiKlinikId, $parameterPaketKlinikId)
  {
    $data = [
      "resourceType" => "ServiceRequest",
      "identifier" => [
        [
          "system" => "http://sys-ids.kemkes.go.id/servicerequest/".config('services.satu_sehat.org_id'),
          "value" => $identifier,
        ],
      ],
      "status" => "active",
      "intent" => "original-order",
      "priority" => "routine",
      "category" => [
        [
          "coding" => [
            [
              "system" => "http://snomed.info/sct",
              "code" => "108252007",
              "display" => "Laboratory procedure",
            ],
          ],
        ],
      ],
      "code" => [
        "coding" => $coding,
        "text" => $text
      ],
      "subject" => ["reference" => "Patient/".$patientId],
      "encounter" => [
        "reference" => "Encounter/".$encounterId,
        "display" => $encounterDisplay,
      ],
      "occurrenceDateTime" => $date,
      "authoredOn" => $date,
      "requester" => [
        "reference" => "Practitioner/".$practitionerId,
        "display" => $practitionerName,
      ],
      "performer" => [
        ["reference" => "Practitioner/".$practitionerId, "display" => $practitionerName],
      ]
    ];


    $response = $this->satuSehatHelper->post('ServiceRequest', $data);

    if ($response['status_code'] == '201'){
      $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::query()->where('permohonan_uji_klinik', '=', $permohonanUjiKlinikId)->where('parameter_paket_klinik', '=', $parameterPaketKlinikId)->first();
      if (isset($permohonanUjiPaketKlinik)){
        $permohonanUjiPaketKlinik->id_service_request = $response['body']["id"];
        $permohonanUjiPaketKlinik->response_service_request = json_encode($response['body']);
        $permohonanUjiPaketKlinik->save();
      }
    }else{
      // Log detailed error information
      $errorMessage = "Gagal membuat service request!";
      $errorDetails = [];

      if (isset($response['status_code'])) {
        $errorDetails['status_code'] = $response['status_code'];
      }

      if (isset($response['body'])) {
        if (is_array($response['body'])) {
          // Extract error message from SatuSehat response
          if (isset($response['body']['issue']) && is_array($response['body']['issue'])) {
            $issues = [];
            foreach ($response['body']['issue'] as $issue) {
              if (isset($issue['details']['text'])) {
                $issues[] = $issue['details']['text'];
              } elseif (isset($issue['diagnostics'])) {
                $issues[] = $issue['diagnostics'];
              }
            }
            if (!empty($issues)) {
              $errorMessage .= " Detail: " . implode('; ', $issues);
            }
          }
          $errorDetails['body'] = $response['body'];
        } else {
          $errorDetails['body'] = $response['body'];
        }
      }

      // Log error with context
      \Log::error('Error creating ServiceRequest', [
        'permohonan_uji_klinik_id' => $permohonanUjiKlinikId,
        'parameter_paket_klinik_id' => $parameterPaketKlinikId,
        'identifier' => $identifier,
        'patient_id' => $patientId,
        'encounter_id' => $encounterId,
        'practitioner_id' => $practitionerId,
        'response' => $errorDetails,
        'request_data' => $data
      ]);

      throw new Exception($errorMessage . " Status Code: " . ($response['status_code'] ?? 'unknown') . ". Response: " . json_encode($errorDetails));
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    $item = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    // mapping data parameter paket dulu
    $item_parameter_paket = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)->first();

    $data_parameter_satuan = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)
      ->groupBy('parameter_paket_jenis_klinik')
      ->get();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.read', [
      'item' => $item,
      'tgl_register' => $tgl_register,
      'item_parameter_paket' => $item_parameter_paket,
      'data_parameter_satuan' => $data_parameter_satuan,
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');

    $item_paket = PermohonanUjiPaketKlinik::find($id_permohonan_uji_paket_klinik);
    $data_parameter_paket = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)
      ->get();
    $data_parameter_satuan = ParameterSatuanKlinik::where('parameter_jenis_klinik', $item_paket->parameter_jenis_klinik)->get();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.edit', [
      'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik,
      'id_permohonan_uji_paket_klinik' => $id_permohonan_uji_paket_klinik,
      'item_paket' => $item_paket,
      'data_parameter_paket' => $data_parameter_paket,
      'data_parameter_satuan' => $data_parameter_satuan,
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');

    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

        // Untuk paket atau custom dimasukkanke permohonanujipaketklinik
        $post = PermohonanUjiPaketKlinik::find($id_permohonan_uji_paket_klinik);
        $post->permohonan_uji_klinik = $id_permohonan_uji_klinik;
        $post->type_permohonan_uji_paket_klinik = $request->type_permohonan_uji_paket_klinik;

        if ($request->type_permohonan_uji_paket_klinik == "P") {
          $post->parameter_paket_klinik = $request->parameter_paket_klinik;
        } else {
          $post->parameter_jenis_klinik = $request->parameter_jenis_klinik;
          $post->parameter_paket_klinik = null;
        }

        $post->harga_permohonan_uji_paket_klinik = $request->harga_permohonan_uji_paket_klinik;
        $simpan = $post->save();

        // delete data yang di paket parameter
        $data_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik);
        $get_data_parameter_klinik = $data_parameter_klinik->get();

        foreach ($get_data_parameter_klinik as $key => $value) {
          // delete data yang di paket sub parameter
          PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value->id_permohonan_uji_parameter_klinik)->delete();
        }

        $data_parameter_klinik->delete();

        // Akan untuk paket atau custom akan dimasukkan di permohonan uji parameter klinik
        // Cek dulu paket atau custom
        if ($request->type_permohonan_uji_paket_klinik == "P") {
          // get data paket untuk mendapatkan jenis parameter untuk di mapping dengan parameter satuan
          $item_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $request->parameter_paket_klinik)
            ->first();

          #looping jenis parameter paket
          foreach ($item_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
            #looping satuan parameter dari paket
            $no_sorting_parameter_satuan = 1;
            foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

              // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
              $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
              $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                  ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();

                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                    ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }

              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
              $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
              $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
              $post_parameter->parameter_paket_klinik = $request->parameter_paket_klinik;
              $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
              $post_parameter->sorting_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->sorting ?? $no_sorting_parameter_satuan;
              $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

              $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              $simpan_post_parameter = $post_parameter->save();

              // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->get();

              if (count($data_parameter_subsatuan) > 0) {
                foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                  $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                  $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                  $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                  // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                  if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                      ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                      ->first();

                    $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                    $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                  }

                  $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                }
              }

              $no_sorting_parameter_satuan++;
            }
          }
        }

        if ($request->type_permohonan_uji_paket_klinik == "C") {
          //  check apakah ada element itu
          if ($request->parameter_custom_klinik) {
            // check dengan count dulu
            if (count($request->parameter_custom_klinik) > 0) {
              // for loop karena multiple
              $item_paket = PermohonanUjiPaketKlinik::find($post->id_permohonan_uji_paket_klinik);
              foreach ($request->parameter_custom_klinik as $key_parameter_satauan => $value_parameter_satauan) {
                #1 attempt
                /* $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];

                // get harga parameter satuan
                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $simpan_parameter = $post_parameter->save(); */

                #2 attempt
                // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->where('is_khusus_baku_mutu', '0')
                  ->first();

                // cek dulu apakah ada data dengan data khusus sperti general atau specific
                if ($check_parameter_by_baku_mutu) {
                  $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                } else {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->where(function ($query) use ($pasien_umur) {
                      $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                        ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                    })
                    ->first();
                  if (!isset($item_parameter_by_baku_mutu)) {
                    $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                      ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                      ->where('is_khusus_baku_mutu', '1')
                      ->where('gender_baku_mutu', $pasien_gender)
                      ->first();
                  }
                }

                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];
                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $post_parameter->jenis_parameter_klinik_id = $request->parameter_jenis_klinik;

                if (isset($item_parameter_by_baku_mutu->unit_id)) {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                } else {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                }

                $simpan_parameter = $post_parameter->save();

                // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
                $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->get();

                if (count($data_parameter_subsatuan) > 0) {
                  foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                    $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                    $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                    $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                    // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                    if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                      $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                        ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                        ->first();

                      if ($item_parameter_subsatuan_by_baku_mutu) {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                      } else {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = null;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = null;
                      }
                    }

                    $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                  }
                }
              }
            }
          }
        }

        // cari semua paket dan tambahkan semua harga untuk diupdate ke permohonan_uji_klinik
        $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)->get();

        $harga_total = 0;

        foreach ($data_paket as $key => $value) {
          $harga_total += $value->harga_permohonan_uji_paket_klinik;
        }

        $post_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
        $post_klinik->total_harga_permohonan_uji_klinik = $harga_total;
        $post_klinik->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter berhasil diubah!", 'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter tidak berhasil diubah!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => 'System gagal melakukan perubahan!'], 200);
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
    abort_unless($this->canManageKlinikParameter(), 403, 'Anda tidak memiliki akses kelola parameter.');

    $detail_paket_klinik = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id)->first();

    $data_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $id);
    $get_data_parameter_klinik = $data_parameter_klinik->get();

    foreach ($get_data_parameter_klinik as $key => $value) {
      // delete data yang di paket sub parameter
      PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value->id_permohonan_uji_parameter_klinik)->delete();
    }

    $data_parameter_klinik->delete();

    if ($detail_paket_klinik) {
      $hapus = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id)->delete();
    }

    $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $detail_paket_klinik->permohonan_uji_klinik)->get();

    $harga_total = 0;

    foreach ($data_paket as $key => $value) {
      $harga_total += $value->harga_permohonan_uji_paket_klinik;
    }

    $post_klinik = PermohonanUjiKlinik2::find($detail_paket_klinik->permohonan_uji_klinik);

    $post_klinik->total_harga_permohonan_uji_klinik = $harga_total;
    if (count($data_paket) == 0) {
      $post_klinik->status_permohonan_uji_klinik = 'PARAMETER';
    }

    $post_klinik->save();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter tidak berhasil dihapus!"], 200);
    }
  }

  public function getDataParameterCustom(Request $request)
  {
    $id_parameter_jenis_klinik = $request->id_parameter_jenis_klinik;
    $response = array();

    // $data = ParameterPaketKlinik::where('parameter_jenis_klinik', $id_parameter_jenis_klinik)->get();
    $parameter_jenis_klinik = ParameterJenisKlinik::with('pakets')->orderBy('created_at', 'asc')
    ->where('id_parameter_jenis_klinik', $id_parameter_jenis_klinik)->get();
    // dd($parameter_jenis_klinik);

    foreach($parameter_jenis_klinik as $jenis){
      foreach($jenis->pakets as $paket){
        $response[] = array(
          "id" => $paket->id_parameter_paket_klinik,
          "text" => $paket->name_parameter_paket_klinik,
          "harga" => $paket->harga_parameter_paket_klinik,
        );
      }
    }

    // foreach ($data as $item) {
    //   $response[] = array(
    //     "id" => $item->id_parameter_paket_klinik,
    //     "text" => $item->name_parameter_paket_klinik,
    //     "harga" => $item->harga_parameter_paket_klinik,
    //   );
    // }

    return response()->json($response);
  }

  public function getDataParameterPaket(Request $request)
  {
    $search = $request->search;
    $parameter_type = $request->parameter_type;
    $response = array();

    if (!isset($search)) {
      $data = ParameterPaketKlinik::orderby('name_parameter_paket_klinik', 'asc')
        ->limit(10)
        ->get();
    } else {
      $data = ParameterPaketKlinik::where('name_parameter_paket_klinik', 'like', '%' . $search . '%')
        ->orderby('name_parameter_paket_klinik', 'asc')
        ->limit(10)
        ->get();
    }

    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_paket_klinik,
        "text" => $item->name_parameter_paket_klinik,
        "harga" => $item->harga_parameter_paket_klinik,
      );
    }

    return response()->json($response);
  }

  public function getCountHargaTotal(Request $request)
  {
    $search = $request->search;
    $id_permohonan_uji_klinik = $request->id_permohonan_uji_klinik;

    $datas = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->orderBy('parameter_paket_extra', 'desc')
      ->get();

      $temp_datas = [];
      $hasIndividualPakets = $this->permohonanHasIndividualPaketKlinik($datas);
      $paket_haji_added = false;

      $temp_extra_paket = null;
      foreach($datas as $val){
        // dd($val->parameter_paket_extra);
        if(isset($val->parameter_paket_extra) && $temp_extra_paket != $val->parameter_paket_extra && $val->parameter_paket_extra != ''){
          $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
          // dd('test');
          $value_extra_paket = [
            "id_permohonan_uji_paket_klinik" => $val->id_permohonan_uji_paket_klinik,
            "permohonan_uji_klinik" => $val->permohonan_uji_klinik,
            "parameter_jenis_klinik" =>  $val->parameter_jenis_klinik,
            "type_permohonan_uji_paket_klinik" =>  'EP',
            "nama_parameter_paket_extra" => $extra_paket->nama_parameter_paket_extra,
            "parameter_paket_klinik" =>  $val->parameter_paket_klinik,
            "parameter_paket_extra" =>  $val->parameter_paket_extra,
            "harga_permohonan_uji_paket_klinik" =>  $extra_paket->harga_parameter_paket_extra,
            "is_prolanis_gula" =>  $val->is_prolanis_gula,
            "is_prolanis_urine" =>  $val->is_prolanis_urine,
            "is_haji" => $val->is_haji,
            "id_service_request" =>  $val->id_service_request,
            "response_service_request" =>  $val->response_service_request,
          ];

          array_push($temp_datas, $value_extra_paket);
          $temp_extra_paket = $val->parameter_paket_extra;

        }elseif($val->parameter_paket_extra == ''){
          if ($val->parameter_paket_klinik) {
            $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();
            if ($paket && HajiPaketHelper::isCompositePaketHajiName($paket->name_parameter_paket_klinik)) {
              // Legacy: expand ke paket individu berdasarkan satuan yang tersimpan di permohonan
              if ($hasIndividualPakets) {
                continue;
              }
              if (!$paket_haji_added) {
                $permohonan = PermohonanUjiKlinik2::with('permohonanujiparameterklinik')->find($id_permohonan_uji_klinik);
                $expandedPakets = $permohonan
                  ? HajiPaketHelper::resolveLayoutPaketsFromPermohonan($permohonan)
                  : collect();
                foreach ($expandedPakets as $individualPaket) {
                  $expanded = clone $val;
                  $expanded->parameter_paket_klinik = $individualPaket->id_parameter_paket_klinik;
                  $expanded->harga_permohonan_uji_paket_klinik = (int) ($individualPaket->harga_parameter_paket_klinik ?? 0);
                  array_push($temp_datas, $expanded);
                }
                $paket_haji_added = true;
              }
            } else {
              array_push($temp_datas, $val);
            }
          } else {
            array_push($temp_datas, $val);
          }
        }
      }

      // dd($temp_datas);

    $count = 0;
    foreach($temp_datas as $data){
      if (is_array($data) && ($data['type_permohonan_uji_paket_klinik'] ?? '') == 'EP') {
        $count = $count + (int) ($data['harga_permohonan_uji_paket_klinik'] ?? 0);
      } else {
        $count = $count + (int) (is_array($data)
          ? ($data['harga_permohonan_uji_paket_klinik'] ?? 0)
          : ($data->harga_permohonan_uji_paket_klinik ?? 0));
      }
    }

    // if ($search) {
    // $count = PermohonanUjiPaketKlinik::leftjoin('ms_parameter_jenis_klinik', function ($join) {
    //   $join->on('ms_parameter_jenis_klinik.id_parameter_jenis_klinik', '=', 'tb_permohonan_uji_paket_klinik.parameter_jenis_klinik')
    //     ->whereNull('ms_parameter_jenis_klinik.deleted_at')
    //     ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
    // })
    //   ->select(
    //     'ms_parameter_jenis_klinik.id_parameter_jenis_klinik as id_parameter_jenis_klinik',
    //     'ms_parameter_jenis_klinik.name_parameter_jenis_klinik as name_parameter_jenis_klinik',
    //     'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik as harga_permohonan_uji_paket_klinik',
    //   )
    //   ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
    //   ->where(function ($query) use ($search) {
    //     $query->Where('ms_parameter_jenis_klinik.name_parameter_jenis_klinik', 'LIKE', '%' . $search . '%');
    //   })
    //   ->sum('tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik');
    // } else {
    //   $count = PermohonanUjiPaketKlinik::select(
    //     'harga_permohonan_uji_paket_klinik',
    //     // DB::raw('SUM(tb_permohonan_uji_klinik.harga_permohonan_uji_paket_klinik)')
    //   )
    //     ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
    //     ->orderBy('created_at', 'asc')
    //     ->sum('harga_permohonan_uji_paket_klinik');
    // }

    return response()->json($count);
  }

  /**
   * Cek apakah permohonan punya paket individu (bukan komposit Paket Haji).
   */
  private function permohonanHasIndividualPaketKlinik($paketRows)
  {
    foreach ($paketRows as $row) {
      if (!empty($row->parameter_paket_extra) || empty($row->parameter_paket_klinik)) {
        continue;
      }

      $paket = ParameterPaketKlinik::find($row->parameter_paket_klinik);
      if ($paket && !HajiPaketHelper::isCompositePaketHajiName($paket->name_parameter_paket_klinik)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @deprecated Tidak lagi dipakai; expand legacy Paket Haji via satuan permohonan.
   */
  private function getIndividualPaketsMatchingPaketHaji()
  {
    return HajiPaketHelper::getLayoutIndividualPakets();
  }

  /**
   * Urutkan daftar paket permohonan mengikuti sort_parameter_satuan_klinik
   * (halaman /elits-parameter-satuan-klinik/reorder).
   */
  private function sortPermohonanPaketBySatuanReorder(array $items): array
  {
    if (empty($items)) {
      return $items;
    }

    $sortMapByPaketId = $this->buildPaketSortMapBySatuanReorder();
    $sortMapByName = $this->buildSatuanSortMapByNameForPaket();

    usort($items, function ($a, $b) use ($sortMapByPaketId, $sortMapByName) {
      $sortA = $this->resolvePaketReorderSort($a, $sortMapByPaketId, $sortMapByName);
      $sortB = $this->resolvePaketReorderSort($b, $sortMapByPaketId, $sortMapByName);

      if ($sortA !== $sortB) {
        return $sortA <=> $sortB;
      }

      $nameA = $this->resolvePaketDisplayName($a);
      $nameB = $this->resolvePaketDisplayName($b);

      return strcasecmp($nameA, $nameB);
    });

    return array_values($items);
  }

  private function resolvePaketReorderSort($item, array $sortMapByPaketId, array $sortMapByName): int
  {
    $isArray = is_array($item);
    $type = $isArray
      ? ($item['type_permohonan_uji_paket_klinik'] ?? null)
      : ($item->type_permohonan_uji_paket_klinik ?? null);

    // Paket extra tetap di atas / terpisah
    if ($type === 'EP') {
      return -1000;
    }

    $paketId = (string) ($isArray
      ? ($item['parameter_paket_klinik'] ?? '')
      : ($item->parameter_paket_klinik ?? ''));

    if ($paketId !== '' && isset($sortMapByPaketId[$paketId])) {
      return $sortMapByPaketId[$paketId];
    }

    $name = $this->resolvePaketDisplayName($item);
    $canonical = $this->canonicalSatuanNameForSort($name);
    if ($canonical !== '' && isset($sortMapByName[$canonical])) {
      return $sortMapByName[$canonical];
    }

    return 9999;
  }

  private function resolvePaketDisplayName($item): string
  {
    if (is_array($item)) {
      if (($item['type_permohonan_uji_paket_klinik'] ?? null) === 'EP') {
        return (string) ($item['nama_parameter_paket_extra'] ?? '');
      }
      $paketId = $item['parameter_paket_klinik'] ?? null;
    } else {
      if (($item->type_permohonan_uji_paket_klinik ?? null) === 'EP') {
        return (string) ($item->nama_parameter_paket_extra ?? '');
      }
      $paketId = $item->parameter_paket_klinik ?? null;
    }

    if (!$paketId) {
      return '';
    }

    static $nameCache = [];
    if (!isset($nameCache[$paketId])) {
      $paket = ParameterPaketKlinik::find($paketId);
      $nameCache[$paketId] = $paket->name_parameter_paket_klinik ?? '';
    }

    return (string) $nameCache[$paketId];
  }

  /**
   * Min sort satuan per paket (via bridge paket-jenis-satuan).
   */
  private function buildPaketSortMapBySatuanReorder(): array
  {
    $map = [];

    $bridges = ParameterPaketJenisKlinik::whereNull('deleted_at')
      ->with([
        'parametersatuanpaketklinik' => function ($q) {
          $q->whereNull('deleted_at')->with('parametersatuanklinik');
        },
      ])
      ->get();

    foreach ($bridges as $bridge) {
      $paketId = (string) ($bridge->parameter_paket_klinik_id ?? '');
      if ($paketId === '') {
        continue;
      }

      foreach ($bridge->parametersatuanpaketklinik ?? [] as $link) {
        $satuan = $link->parametersatuanklinik ?? null;
        if (!$satuan || !empty($satuan->deleted_at)) {
          continue;
        }

        $sort = (int) ($satuan->sort_parameter_satuan_klinik ?? 9999);
        if (!isset($map[$paketId]) || $sort < $map[$paketId]) {
          $map[$paketId] = $sort;
        }
      }
    }

    return $map;
  }

  /**
   * Fallback: sort terkecil per nama satuan (untuk paket 1:1 dengan nama mirip).
   */
  private function buildSatuanSortMapByNameForPaket(): array
  {
    $map = [];
    $records = ParameterSatuanKlinik::whereNull('deleted_at')
      ->orderBy('sort_parameter_satuan_klinik', 'asc')
      ->get(['name_parameter_satuan_klinik', 'sort_parameter_satuan_klinik']);

    foreach ($records as $record) {
      $canonical = $this->canonicalSatuanNameForSort($record->name_parameter_satuan_klinik ?? '');
      if ($canonical === '') {
        continue;
      }

      $sort = (int) ($record->sort_parameter_satuan_klinik ?? 9999);
      if (!isset($map[$canonical]) || $sort < $map[$canonical]) {
        $map[$canonical] = $sort;
      }
    }

    return $map;
  }

  private function canonicalSatuanNameForSort(?string $name): string
  {
    $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name ?? '')), 'UTF-8');
    if ($normalized === '') {
      return '';
    }

    // Samakan varian ejaan / label paket vs satuan
    $normalized = preg_replace('/\s*\(.*?\)\s*/u', ' ', $normalized);
    $normalized = trim(preg_replace('/\s+/u', ' ', $normalized));

    $aliases = [
      'creatinine' => ['creatinine', 'kreatinin'],
      'triglyceride' => ['triglyceride', 'trigliserid', 'trigliseride'],
      'cholesterol total' => ['cholesterol total', 'cholesterol', 'kolesterol total', 'kolesterol'],
      'hdl cholesterol' => ['hdl cholesterol', 'cholesterol hdl', 'hdl'],
      'ldl cholesterol' => ['ldl cholesterol', 'cholesterol ldl', 'ldl'],
      'gula darah puasa' => ['gula darah puasa', 'gdn', 'gdp'],
      'sgot' => ['sgot', 'sgot (37°c)', 'sgot (37c)'],
      'sgpt' => ['sgpt', 'sgpt (37°c)', 'sgpt (37c)'],
      'led' => ['led', 'led (laju endap darah)', 'laju endap darah'],
    ];

    foreach ($aliases as $canonical => $list) {
      if (in_array($normalized, $list, true)) {
        return $canonical;
      }
    }

    return $normalized;
  }
}