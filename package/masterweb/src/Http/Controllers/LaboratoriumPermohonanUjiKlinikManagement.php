<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use Ramsey\Uuid\Uuid;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\LHU;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\PermohonanUjiAnalisKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;

use Smt\Masterweb\Models\StartNum;


use Yajra\Datatables\Datatables;



use GuzzleHttp\Client;
use \Smt\Masterweb\Models\User;

class LaboratoriumPermohonanUjiKlinikManagement extends Controller
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
  public function index()
  {
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.pagination');
  }

  public function rules($request)
  {
    $rule = [
      'noregister_permohonan_uji_klinik' => 'required',
      // 'nopasien_permohonan_uji_klinik' => 'required',
      'tglregister_permohonan_uji_klinik' => 'required',
      'tglpengambilan_permohonan_uji_klinik' => 'required',
      'namapengirim_permohonan_uji_klinik' => 'required',
    ];

    $pesan = [
      'noregister_permohonan_uji_klinik.required' => 'No register permohonan uji klinik tidak boleh kosong!',
      // 'nopasien_permohonan_uji_klinik.required' => 'No pasien permohonan uji klinik tidak boleh kosong!',
      'tglregister_permohonan_uji_klinik.required' => 'Tanggal register permohonan uji klinik tidak boleh kosong!',
      'tglpengambilan_permohonan_uji_klinik.required' => 'Tanggal pengambilan tidak boleh kosong!',
      'namapengirim_permohonan_uji_klinik.required' => 'Nama pengirim tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function rules_pasien($request)
  {
    $rule = [
      'nama_pasien' => 'required|string|min:3|max:255',
      'tgllahir_pasien' => 'required',
      'phone_pasien' => 'required',
    ];

    $pesan = [
      'nama_pasien.required' => 'Nama pasien tidak boleh kosong!',
      'tgllahir_pasien.required' => 'Tanggal lahir pasien tidak boleh kosong!',
      'phone_pasien.required' => 'Nomor telepon tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function data_permohonan_uji_klinik(Request $request)
  {
    if (Auth::user()->getlevel->level == 'DKTR') {
      $datas = PermohonanUjiKlinik::orderBy('created_at', 'desc')->wherein('status_permohonan_uji_klinik', ['SELESAI'])->get();
    } else {
      $datas = PermohonanUjiKlinik::orderBy('created_at', 'desc')->get();
    }

    return Datatables::of($datas)
      ->filter(function ($instance) use ($request) {
        if (!empty($request->get('search'))) {
          $instance->collection = $instance->collection->filter(function ($row) use ($request) {
            if (Str::contains(Str::lower($row['noregister_permohonan_uji_klinik']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['no_rekammedis']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['nama_pasien']), Str::lower($request->get('search')))) {
              return true;
            } else if (Str::contains(Str::lower($row['status_permohonan']), Str::lower($request->get('search')))) {
              return true;
            }

            return false;
          });
        }
      })
      ->addColumn('action', function ($data) use ($request) {
        $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
          ->whereNull('deleted_at')
          ->get();

        $analisButton = '';
        $dokterButton = '';
        $parameterButton = '';
        $detailButton = '';
        $editButton = '';
        $deleteButton = '';

        if (getSpesialAction($request->segment(1), 'analis-klinik', '')) {
          if (count($data_permohonan_uji_paket_klinik) > 0) {
            $analisButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.create-permohonan-uji-analis', $data->id_permohonan_uji_klinik) . '" title="Analisa">Analis</a> ';
          }
        }

        if (getSpesialAction($request->segment(1), 'rekomendasi-dokter-klinik', '')) {
          if ($data->status_permohonan_uji_klinik == "SELESAI") {
            $dokterButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.create-permohonan-uji-rekomendasi-dokter', $data->id_permohonan_uji_klinik) . '" title="Dokter">Dokter</a> ';
          }
        }

        if (getSpesialAction($request->segment(1), 'parameter-klinik', '')) {
          $parameterButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', $data->id_permohonan_uji_klinik) . '" title="Masukkan Parameter">Parameter</a> ';
        }

        if (getAction('read')) {
          $detailButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.show', $data->id_permohonan_uji_klinik) . '" title="Detail">Detail</a> ';
        }

        if (getAction('read')) {
          $editButton = '<a href="' . route('elits-permohonan-uji-klinik.edit', $data->id_permohonan_uji_klinik) . '" class="dropdown-item" title="Edit">Edit</a> ';
        }

        if (getAction('delete')) {
          $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji_klinik . '" data-nama="' . $data->namapasien_permohonan_uji_klinik . '" title="Hapus">Hapus</a> ';
        }

        $button = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $analisButton . '

                                ' . $dokterButton . '

                                ' . $parameterButton . '

                                ' . $detailButton . '

                                ' . $editButton . '

                                ' . $deleteButton . '
                            </div>
                        </div>';

        // BUTTON PRINT
        $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->orderBy('name_parameter_satuan_klinik', 'asc')
              ->whereNull('deleted_at');
          })
          ->get();

        $printFormulirKlinik = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-formulir', $data->id_permohonan_uji_klinik) . '" target="__blank" title="Print Formulir Uji Klinik">Print Formulir Uji Klinik</a> ';

        $printkartuMedis = '';

        if (count($data_permohonan_uji_parameter_klinik) > 0) {
          $printNota = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-nota', $data->id_permohonan_uji_klinik) . '" title="Print Nota" target="__blank">Print Nota</a> ';
        } else {
          $printNota = '';
        }

        $printHasilKlinik = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil', $data->id_permohonan_uji_klinik) . '" target="__blank" title="Print Hasil Klinik">Print Hasil Klinik</a> ';

        $data_permohonan_uji_parameter_klinik_rapid_antibody = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->orderBy('name_parameter_satuan_klinik', 'asc')
              ->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
              ->whereNull('deleted_at');
          })
          ->get();

        if (count($data_permohonan_uji_parameter_klinik_rapid_antibody) > 0) {
          $printHasilRapidAntibody = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antibody', $data->id_permohonan_uji_klinik) . '" title="Print Hasil Rapid Antibody" target="__blank">Print Hasil Rapid Antibody</a> ';
        } else {
          $printHasilRapidAntibody = '';
        }

        $data_permohonan_uji_parameter_klinik_rapid_antigen = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->orderBy('name_parameter_satuan_klinik', 'asc')
              ->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
              ->whereNull('deleted_at');
          })
          ->get();

        if (count($data_permohonan_uji_parameter_klinik_rapid_antigen) > 0) {
          $printHasilRapidAntigen = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antigen', $data->id_permohonan_uji_klinik) . '" title="Print Hasil Rapid Antigen" target="__blank">Print Hasil Rapid Antigen</a> ';
        } else {
          $printHasilRapidAntigen = '';
        }

        $data_permohonan_uji_parameter_klinik_pcr = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->orderBy('name_parameter_satuan_klinik', 'asc')
              ->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
              ->whereNull('deleted_at');
          })
          ->get();

        if (count($data_permohonan_uji_parameter_klinik_pcr) > 0) {
          $printHasilPcr = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-pcr', $data->id_permohonan_uji_klinik) . '" title="Print Hasil PCR" target="__blank">Print Hasil PCR</a> ';
        } else {
          $printHasilPcr = '';
        }

        $printHasilQrcode = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-qrcode', $data->id_permohonan_uji_klinik) . '" title="Print QR Code" target="__blank">Print QR Code</a> ';

        $buttonPrint = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLinkPrint" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Print
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $printFormulirKlinik . '
                                ' . $printkartuMedis . '
                                ' . $printNota . '
                                ' . $printHasilKlinik . '
                                ' . $printHasilRapidAntibody . '
                                ' . $printHasilRapidAntigen . '
                                ' . $printHasilPcr . '
                                ' . $printHasilQrcode . '
                            </div>
                        </div>';

        return $button . ' ' . $buttonPrint;
      })
      ->addColumn('nama_pasien', function ($data) {
        return $data->pasien->nama_pasien;
      })
      ->addColumn('no_rekammedis', function ($data) {
        return Carbon::createFromFormat('Y-m-d', $data->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $data->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT);
      })
      ->addColumn('tgl_pengambilan', function ($data) {
        // return Carbon::createFromFormat('Y-m-d H:i:S', $data->tglpengambilan_permohonan_uji_klinik, 'Asia/Jakarta')->isoFormat('D MMMM Y H:i');

        return Carbon::createFromFormat('Y-m-d H:i:s', $data->tglpengambilan_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
      })
      /* ->addColumn('status_permohonan', function ($data) {
    $status_parameter = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)->whereNull('deleted_at')->get();

    if (count($status_parameter) > 0) {
    $status = '<label class="badge badge-primary badge-pill">Proses Analisa</label>';
    } else {
    $status = '<label class="badge badge-warning badge-pill">Proses Parameter</label>';
    }

    if ($data->tglpengujian_permohonan_uji_klinik || $data->spesimen_darah_permohonan_uji_klinik || $data->spesimen_urine_permohonan_uji_klinik) {
    $status = '<label class="badge badge-success badge-pill">Proses Selesai</label>';
    }

    return $status;
    }) */
      ->addColumn('status_permohonan', function ($data) {
        if ($data->status_permohonan_uji_klinik == "PARAMETER") {
          $status = '<label class="badge badge-danger badge-pill">Proses Parameter</label>';
        }

        if ($data->status_permohonan_uji_klinik == "ANALIS") {
          $status = '<label class="badge badge-warning badge-pill">Proses Analisa</label>';
        }

        if ($data->status_permohonan_uji_klinik == "SELESAI") {
          $status = '<label class="badge badge-success badge-pill">Proses Selesai</label>';
        }

        return $status;
      })
      ->addColumn('status_pembayaran', function ($data) {
        if ($data->is_paid_permohonan_uji_klinik == '0') {
          $status = '<a href="javascript:void(0)" class="btn btn-outline-danger btn-payment" data-id="' . $data->id_permohonan_uji_klinik . '">Lunasi Sekarang</a>';
        } else {
          $status = '<button type="button" class="btn btn-success">Lunas</button>';
        }

        return $status;
      })
      ->rawColumns(['action', 'tgl_pengambilan', 'status_permohonan', 'nama_pasien', 'status_pembayaran', 'no_rekammedis'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $user = Auth()->user();
    $count = PermohonanUjiKlinik::where(DB::raw('YEAR(tglregister_permohonan_uji_klinik)'), '=', date('Y'))->max('nourut_permohonan_uji_klinik');


    $permohonan_count=PermohonanUjiKlinik::count();

    $start_num=StartNum::where('code_lab_start_number','KLI')->first();
    if ($permohonan_count==0) {
      # code...
      
      $count_pasien = Pasien::nextNoRekamMedis() + ($start_num->count_start_number ?? 0);
    }else{
      $count_pasien = Pasien::nextNoRekamMedis() + ($start_num->count_start_number ?? 0);
    }


    if ($count < 1) {
      $set_count = 1 + ($start_num->count_start_number);
    } else {
      $set_count = $count + 1 + ($start_num->count_start_number);
    }

    // $code ='PU.NK/'.date("Ymd", time()).'/'.($count+1);
    $code = 'PU.K/' . date("Ymd", time()) . '/' . str_pad((int) ($set_count), 4, '0', STR_PAD_LEFT);

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.add', compact('code', 'set_count', 'count_pasien'));
    //get all menu public
  }

  public function store(Request $request)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = new PermohonanUjiKlinik();
        $post->nourut_permohonan_uji_klinik = $request->post('nourut_permohonan_uji_klinik');
        $post->noregister_permohonan_uji_klinik = $request->post('noregister_permohonan_uji_klinik');
        $post->tglregister_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y', $request->post('tglregister_permohonan_uji_klinik'))->format('Y-m-d');

        $post->umurtahun_pasien_permohonan_uji_klinik = $request->post('umurtahun_pasien_permohonan_uji_klinik');
        $post->umurbulan_pasien_permohonan_uji_klinik = $request->post('umurbulan_pasien_permohonan_uji_klinik');
        $post->umurhari_pasien_permohonan_uji_klinik = $request->post('umurhari_pasien_permohonan_uji_klinik');

        $post->tglpengambilan_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $request->post('tglpengambilan_permohonan_uji_klinik'))->format('Y-m-d H:i');
        $post->namapengirim_permohonan_uji_klinik = $request->post('namapengirim_permohonan_uji_klinik');
        $post->diagnosa_permohonan_uji_klinik = $request->post('diagnosa_permohonan_uji_klinik');
        $post->status_permohonan_uji_klinik = 'PARAMETER';
        $post->dokter_permohonan_uji_klinik = $request->dokter_permohonan_uji_klinik ?? null;

        if ($request->post('is_lapangan_permohonan_uji_klinik') != null) {
          $post->is_lapangan_permohonan_uji_klinik = '1';
        } else {
          $post->is_lapangan_permohonan_uji_klinik = '0';
        }

        $store_pasien = false;

        // check dulu apakah user mengisi pasien baru atau pasien yang sudah ada
        if ($request->pasien_permohonan_uji_klinik == null) {
          $validator = $this->rules_pasien($request->all());

          if ($validator->fails()) {
            return response()->json(['status' => false, 'pesan' => $validator->errors()]);
          } else {
            if ($request->post('nik_pasien') != '-') {
              $check = Pasien::where('nik_pasien', $request->post('nik_pasien'))
                ->first();

              if ($check) {
                return response()->json(['status' => false, 'pesan' => "NIK pasien sudah pernah diinput, silahkan input NIK yang berbeda!"], 200);
              } else {
                $set_count = Pasien::nextNoRekamMedis();

                $post_newpasien = new Pasien();
                $post_newpasien->nik_pasien = $request->post('nik_pasien');
                $post_newpasien->nourut_pasien = $set_count;
                $post_newpasien->no_rekammedis_pasien = $set_count;
                // $post_newpasien->no_rekammedis_pasien = $request->no_rekammedis_pasien;
                $post_newpasien->nama_pasien = $request->post('nama_pasien');
                $post_newpasien->gender_pasien = $request->post('gender_pasien');
                $post_newpasien->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');

                $post_newpasien->alamat_pasien = $request->post('alamat_pasien');
                $post_newpasien->phone_pasien = $request->post('phone_pasien');
                $post_newpasien->divisi_instansi_pasien = $request->post('divisi_instansi_pasien');

                $store_pasien = $post_newpasien->save();

                $post->pasien_permohonan_uji_klinik = $post_newpasien->id_pasien;
              }
            } else {
              $set_count = Pasien::nextNoRekamMedis();

              $post_newpasien = new Pasien();
              $post_newpasien->nik_pasien = $request->post('nik_pasien');
              $post_newpasien->nourut_pasien = $set_count;
              $post_newpasien->no_rekammedis_pasien = $set_count;
              // $post_newpasien->no_rekammedis_pasien = $request->no_rekammedis_pasien;
              $post_newpasien->nama_pasien = $request->post('nama_pasien');
              $post_newpasien->gender_pasien = $request->post('gender_pasien');
              $post_newpasien->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');

              $post_newpasien->alamat_pasien = $request->post('alamat_pasien');
              $post_newpasien->phone_pasien = $request->post('phone_pasien');
              $post_newpasien->divisi_instansi_pasien = $request->post('divisi_instansi_pasien');

              $store_pasien = $post_newpasien->save();

              $post->pasien_permohonan_uji_klinik = $post_newpasien->id_pasien;
            }
          }
        } else {
          $store_pasien = true;
          $post->pasien_permohonan_uji_klinik = $request->post('pasien_permohonan_uji_klinik');
        }

        $simpan = $post->save();

        DB::commit();

        $urlNextStep = route('elits-permohonan-uji-klinik.create-permohonan-uji-parameter', [$post->id_permohonan_uji_klinik, 'complete-step' => true]);

        if ($simpan == true && $store_pasien == true) {
          return response()->json(['status' => true, 'pesan' => "Data permohonan uji klinik berhasil disimpan!", 'urlNextStep' => $urlNextStep], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data permohonan uji klinik tidak berhasil disimpan!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!'], 200);
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
    $auth = Auth()->user();
    $item = PermohonanUjiKlinik::find($id);

    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');
    $tgl_pengambilan = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglpengambilan_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');

    // data parameter
    $data_parameter_jenis = ParameterJenisKlinik::whereNull('deleted_at')->orderBy('name_parameter_jenis_klinik', 'asc')->get();
    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)
      ->whereNull('deleted_at')
      ->get();
    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    // data analis
    if ($item->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    if ($item->spesimen_darah_permohonan_uji_klinik !== null) {
      $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item->spesimen_darah_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_darah = '-';
    }

    if ($item->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item->spesimen_urine_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_urine = '-';
    }

    // non sedimen
    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)
      ->whereNull('deleted_at')
      ->get();

    // array untuk mapping data parameter satuan ke baku mutu
    $arr_permohonan_parameter = [];

    $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
      ->orderBy('jenis_parameter_klinik_id', 'ASC')
      ->orderBy('sorting_parameter_satuan', 'ASC')
      ->get();

    $data_jenis_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
      ->groupBy('jenis_parameter_klinik_id')
      ->orderBy('sort_jenis_klinik', 'ASC')
      ->get();

    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id);

    if (count($data_permohonan_uji_satuan_klinik) > 0) {
      foreach ($data_jenis_parameter_klinik as $key_jenis_parameter_klinik => $value_jenis_parameter_klinik) {
        # code...
        $parameter_jenis_klinik = [];
        $parameter_jenis_klinik['name_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->name_parameter_jenis_klinik;
        $parameter_jenis_klinik['id_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik;
        $parameter_jenis_klinik['item_permohonan_parameter_satuan'] = [];

        foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {

          if ($value_satuan->jenis_parameter_klinik_id == $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik) {

            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $id_parameter_jenis_klinik = $value_satuan->jenis_parameter_klinik_id;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            $item_parameter_by_baku_mutu = null;
            if (count($data_parameter_by_baku_mutu) > 0) {

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific

              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                // cek dulu punya satuan di subparameter atau tidak
                // jika tidak ada maka ambil ke baku mutu
                // else null
                /* if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                } else {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik; */
                $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = $item_permohonan_parameter_subsatuan;
                // array_push($arr_permohonan_parameter['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($parameter_jenis_klinik['item_permohonan_parameter_satuan'], $item_permohonan_parameter_satuan);
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
          }
        }
        array_push($arr_permohonan_parameter, $parameter_jenis_klinik);
      }
    }

    // if (count($data_permohonan_uji_paket_klinik) > 0) {
    //   foreach ($data_permohonan_uji_paket_klinik as $key_paket => $value_paket) {
    //     $item_permohonan_parameter_paket = [];
    //     $item_permohonan_parameter_paket['id_permohonan_uji_paket_klinik'] = $value_paket->id_permohonan_uji_paket_klinik;
    //     $item_permohonan_parameter_paket['permohonan_uji_klinik'] = $value_paket->permohonan_uji_klinik;
    //     $item_permohonan_parameter_paket['parameter_jenis_klinik'] = $value_paket->parameter_jenis_klinik;
    //     $item_permohonan_parameter_paket['type_permohonan_uji_paket_klinik'] = $value_paket->type_permohonan_uji_paket_klinik;
    //     $item_permohonan_parameter_paket['parameter_paket_klinik'] = $value_paket->parameter_paket_klinik;
    //     $item_permohonan_parameter_paket['nama_parameter_paket_klinik'] = $value_paket->parameterpaketklinik->name_parameter_paket_klinik ?? null;
    //     $item_permohonan_parameter_paket['harga_permohonan_uji_paket_klinik'] = $value_paket->harga_permohonan_uji_paket_klinik;

    //     // get data perameter satuan yang ada dalam paket
    //     $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
    //       ->where('permohonan_uji_paket_klinik', $value_paket->id_permohonan_uji_paket_klinik)
    //       ->get();

    //     $item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'] = [];

    //     if (count($data_permohonan_uji_satuan_klinik) > 0) {
    //       foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {
    //         // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
    //         // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
    //         $pasien_umur = $item->umurtahun_pasien_permohonan_uji_klinik;
    //         $pasien_gender = $item->pasien->gender_pasien;

    //         $item_permohonan_parameter_satuan = [];
    //         $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
    //         $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
    //         $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
    //         $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
    //         $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
    //         $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

    //         $id_parameter_jenis_klinik = null;

    //         if (isset($value_paket->parameter_jenis_klinik)) {
    //           $id_parameter_jenis_klinik = $value_paket->parameter_jenis_klinik;
    //         } else {
    //           $id_parameter_jenis_klinik = $value_satuan->permohonanujijenispaketklinik->parameter_jenis_klinik_id;
    //         }
    //         $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //           ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //           ->get();

    //         $item_parameter_by_baku_mutu = null;
    //         if (count($data_parameter_by_baku_mutu) > 0) {

    //           $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //             ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //             ->where('is_khusus_baku_mutu', '0')
    //             ->first();

    //           // cek dulu apakah ada data dengan data khusus sperti general atau specific

    //           if ($check_parameter_by_baku_mutu) {
    //             $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
    //           } else {
    //             $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //               ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //               ->where('is_khusus_baku_mutu', '1')
    //               ->where('gender_baku_mutu', $pasien_gender)
    //               ->where(function ($query) use ($pasien_umur) {
    //                 $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
    //                   ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
    //               })
    //               ->first();
    //           }
    //         }

    //         // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
    //         // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
    //         if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
    //         } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
    //         } else {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
    //         }

    //          $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
    $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
    $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
    //         $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

    //         // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
    //         $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

    //         $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
    //           ->get();

    //         if (count($data_permohonan_uji_subsatuan_klinik)) {
    //           foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
    //             $item_permohonan_parameter_subsatuan = [];
    //             $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
    //             $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
    //             $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
    //             $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
    //             // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

    //             // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
    //             if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
    //               $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
    //                 ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
    //                 ->first();

    //               if ($item_parameter_subsatuan_by_baku_mutu) {
    //                 $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
    //               } else {
    //                 $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
    //               }
    //             } else {
    //               $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
    //             }

    //             if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
    //             } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
    //             } else {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
    //             }

    //             array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
    //           }
    //         }

    //         array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
    //       }
    //     }

    //     array_push($arr_permohonan_parameter, $item_permohonan_parameter_paket);
    //   }
    // }

    $parameterCustom = DB::table('tb_permohonan_uji_paket_klinik')->where('tb_permohonan_uji_paket_klinik.permohonan_uji_klinik', '=', $id)
      ->join('tb_permohonan_uji_klinik', function ($join) {
        $join->on('tb_permohonan_uji_klinik.id_permohonan_uji_klinik', '=', 'tb_permohonan_uji_paket_klinik.permohonan_uji_klinik')
          ->whereNull('tb_permohonan_uji_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->join('tb_permohonan_uji_parameter_klinik', function ($join) {
        $join->on('tb_permohonan_uji_paket_klinik.id_permohonan_uji_paket_klinik', '=', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_paket_klinik')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at');
      })
      ->join('ms_parameter_satuan_klinik', function ($join) {
        $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik')
          ->whereNull('ms_parameter_satuan_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at');
      })
      ->where('tb_permohonan_uji_paket_klinik.type_permohonan_uji_paket_klinik', "C")
      ->select(
        'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
        'ms_parameter_satuan_klinik.id_parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.harga_permohonan_uji_parameter_klinik',
        DB::raw('count(*) as count_method')
      )
      ->groupBy(
        'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
        'ms_parameter_satuan_klinik.id_parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.harga_permohonan_uji_parameter_klinik'
      )
      ->get();

    $value_items = array();

    foreach ($parameterCustom as $valueCustom) {
      $nilaiPermohonanUjiKlinik["name_item"] = $valueCustom->name_parameter_satuan_klinik;
      $nilaiPermohonanUjiKlinik["count_item"] = $valueCustom->count_method;
      $nilaiPermohonanUjiKlinik["price_item"] = $valueCustom->harga_permohonan_uji_parameter_klinik;
      $nilaiPermohonanUjiKlinik["total"] = $valueCustom->harga_permohonan_uji_parameter_klinik * $valueCustom->count_method;

      array_push($value_items, $nilaiPermohonanUjiKlinik);
    }

    // dd($value_items);

    $parameterPaket = DB::table('tb_permohonan_uji_paket_klinik')->where('tb_permohonan_uji_paket_klinik.permohonan_uji_klinik', '=', $id)
      ->join('tb_permohonan_uji_klinik', function ($join) {
        $join->on('tb_permohonan_uji_klinik.id_permohonan_uji_klinik', '=', 'tb_permohonan_uji_paket_klinik.permohonan_uji_klinik')
          ->whereNull('tb_permohonan_uji_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->join('ms_parameter_paket_klinik', function ($join) {
        $join->on('ms_parameter_paket_klinik.id_parameter_paket_klinik', '=', 'tb_permohonan_uji_paket_klinik.parameter_paket_klinik')
          ->whereNull('ms_parameter_paket_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->where('tb_permohonan_uji_paket_klinik.type_permohonan_uji_paket_klinik', "P")
      ->select(
        'ms_parameter_paket_klinik.name_parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik',
        DB::raw('count(tb_permohonan_uji_paket_klinik.parameter_paket_klinik) as count_method')
      )
      ->groupBy(
        'ms_parameter_paket_klinik.name_parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik'
      )
      ->get();

    foreach ($parameterPaket as $valuePaket) {
      $nilaiPermohonanUjiKlinik["name_item"] = $valuePaket->name_parameter_paket_klinik;
      $nilaiPermohonanUjiKlinik["count_item"] = $valuePaket->count_method;
      $nilaiPermohonanUjiKlinik["price_item"] = $valuePaket->harga_permohonan_uji_paket_klinik;
      $nilaiPermohonanUjiKlinik["total"] = $valuePaket->harga_permohonan_uji_paket_klinik * $valuePaket->count_method;

      array_push($value_items, $nilaiPermohonanUjiKlinik);
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.read', [
      'item' => $item,
      'tgl_register' => $tgl_register,
      'tgl_pengambilan' => $tgl_pengambilan,
      'data_parameter_jenis' => $data_parameter_jenis,
      'data_parameter_satuan' => $data_parameter_satuan,
      'data_permohonan_uji_paket_klinik' => $data_permohonan_uji_paket_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_detail_uji_paket' => $data_permohonan_uji_paket_klinik,
      'value_items' => $value_items,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
    ]);
  }

  function print($id) {}

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $auth = Auth()->user();
    $item = PermohonanUjiKlinik::find($id);

    $tgl_pengambilan = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglpengambilan_permohonan_uji_klinik)->format('d/m/Y H:m:s');

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.edit', [
      'item' => $item,
      'tgl_pengambilan' => $tgl_pengambilan,
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      // dd($request);
      DB::beginTransaction();

      try {
        $post = PermohonanUjiKlinik::findOrFail($id);
        $post->noregister_permohonan_uji_klinik = $request->post('noregister_permohonan_uji_klinik');
        // $post->nopasien_permohonan_uji_klinik = $request->post('nopasien_permohonan_uji_klinik');
        $post->tglregister_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y', $request->post('tglregister_permohonan_uji_klinik'))->format('Y-m-d');
        $post->tglpengambilan_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i:s', $request->post('tglpengambilan_permohonan_uji_klinik'))->format('Y-m-d H:i');
        $post->namapengirim_permohonan_uji_klinik = $request->post('namapengirim_permohonan_uji_klinik');
        $post->diagnosa_permohonan_uji_klinik = $request->post('diagnosa_permohonan_uji_klinik');
        $post->dokter_permohonan_uji_klinik = $request->dokter_permohonan_uji_klinik ?? null;

        if ($request->post('is_lapangan_permohonan_uji_klinik') != null) {
          $post->is_lapangan_permohonan_uji_klinik = '1';
        } else {
          $post->is_lapangan_permohonan_uji_klinik = '0';
        }

        $simpan = $post->save();

        // dd($simpan);

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data permohonan uji klinik berhasil diubah!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data permohonan uji klinik tidak berhasil diubah!"], 200);
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
    // $data = PermohonanUjiKlinik::find($id);
    $hapus = PermohonanUjiKlinik::where('id_permohonan_uji_klinik', $id)->delete();

    $detail_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)->get();

    if ($detail_paket_klinik) {
      PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)->delete();
    }

    $detail_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)->get();

    if ($detail_parameter_klinik) {
      PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)->delete();
    }

    $detail_analis_klinik = PermohonanUjiAnalisKlinik::where('permohonan_uji_klinik_id', $id)->first();

    if ($detail_analis_klinik) {
      PermohonanUjiAnalisKlinik::where('permohonan_uji_klinik_id', $id)->delete();
    }

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data permohonan uji klinik berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data permohonan uji klinik tidak berhasil dihapus!"], 200);
    }
  }

  public function buktiDaftarPermohonanUjiParameter(Request $request, $id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::findOrFail($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik !== null) {
      if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
        $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
      } else {
        $tgl_pengujian = '';
      }

      if ($item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik !== null) {
        $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
      } else {
        $tgl_spesimen_darah = '';
      }

      if ($item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik !== null) {
        $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
      } else {
        $tgl_spesimen_urine = '';
      }

      $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

      if (!isset($no_LHU)) {
        $no_LHU = new LHU;
        //uuid
        $uuid4 = Uuid::uuid4();

        $no_LHU_urutan = LHU::max('nomer_urut_LHU');
        $no_LHU->id_lhu = $uuid4->toString();
        $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
        $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

        // permintaan pertama
        // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

        // permintaan kedua
        $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

        $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
        $no_LHU->save();

        $no_LHU = $no_LHU->nomer_LHU;
      } else {
        $get_nomor_lhu = $no_LHU->nomer_LHU;
        $explode_nomor_lhu = explode("/", $get_nomor_lhu);
        $get_pisah0_lhu = $explode_nomor_lhu[0];
        $get_pisah2_lhu = $explode_nomor_lhu[2];
        $get_pisah3_lhu = $explode_nomor_lhu[3];

        $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
      }

      $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereHas('parametersatuanklinik', function ($query) {
          return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'asc');
        })
        ->whereNull('deleted_at')
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->get();

      $data_permohonan_uji_parameter_klinik_pcr = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereHas('parametersatuanklinik', function ($query) {
          return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'asc');
        })
        ->whereNull('deleted_at')
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->get();

      $data_permohonan_uji_parameter_klinik_antigen = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereHas('parametersatuanklinik', function ($query) {
          return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'asc');
        })
        ->whereNull('deleted_at')
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->get();

      $data_permohonan_uji_parameter_klinik_antibody = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereHas('parametersatuanklinik', function ($query) {
          return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'asc');
        })
        ->whereNull('deleted_at')
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->get();

      $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

      $text_redirect_barcode = route('scan.permohonan-uji-klinik', $item_permohonan_uji_klinik->id_permohonan_uji_klinik);

      // dd($text_redirect_barcode);

      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.bukti-daftar-permohonan-uji-klinik', [
        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
        'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
        'data_permohonan_uji_parameter_klinik_pcr' => $data_permohonan_uji_parameter_klinik_pcr,
        'data_permohonan_uji_parameter_klinik_antigen' => $data_permohonan_uji_parameter_klinik_antigen,
        'data_permohonan_uji_parameter_klinik_antibody' => $data_permohonan_uji_parameter_klinik_antibody,
        'tgl_pengujian' => $tgl_pengujian,
        'tgl_spesimen_darah' => $tgl_spesimen_darah,
        'tgl_spesimen_urine' => $tgl_spesimen_urine,
        'data_parameter_satuan' => $data_parameter_satuan,
        'no_LHU' => $no_LHU,
        'text_redirect_barcode' => $text_redirect_barcode,
      ]);
    } else {
      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.pagination');
    }
  }

  public function rules_permohonan_uji_parameter($request)
  {
    $rule = [
      'type_parameter.*' => 'required',
    ];

    $pesan = [
      'type_parameter.*.required' => 'Tipe Parameter tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function createPermohonanUjiParameter($id)
  {
    $item = PermohonanUjiKlinik::find($id);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    $data_detail_uji_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)
      ->whereNull('deleted_at')
      ->get();

    // dd($data_detail_uji_paket);

    if ($item->status_permohonan_uji_klinik == 'SELESAI') {
      $data_detail_uji_parameter = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
        ->whereNull('deleted_at')
        ->get();

      $data_parameter_paket = ParameterPaketKlinik::whereNull('deleted_at')->orderBy('name_parameter_paket_klinik', 'asc')->get();

      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.show-permohonan-uji-parameter-klinik', [
        'item' => $item,
        'tgl_register' => $tgl_register,
        'data_detail_uji_paket' => $data_detail_uji_paket,
        'data_detail_uji_parameter' => $data_detail_uji_parameter,
        'data_parameter_paket' => $data_parameter_paket,
      ]);
    } else {
      if (count($data_detail_uji_paket) > 0) {
        $data_detail_uji_parameter = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
          ->whereNull('deleted_at')
          ->get();

        $data_parameter_paket = ParameterPaketKlinik::whereNull('deleted_at')->orderBy('name_parameter_paket_klinik', 'asc')->get();

        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.edit-permohonan-uji-paramater-klinik', [
          'item' => $item,
          'tgl_register' => $tgl_register,
          'data_detail_uji_paket' => $data_detail_uji_paket,
          'data_detail_uji_parameter' => $data_detail_uji_parameter,
          'data_parameter_paket' => $data_parameter_paket,
        ]);
      } else {
        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.create-permohonan-uji-paramater-klinik', [
          'item' => $item,
          'tgl_register' => $tgl_register,
          'id' => $id,
        ]);
      }
    }
  }

  public function getParameterDanHarga(Request $request)
  {
    $search = $request->search;
    $jenis_parameter = $request->jenis_parameter;
    $type_parameter = $request->type_parameter;
    $response = array();
    $set_data = [];

    if ($type_parameter == "P") {
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

      $set_data = [
        'data' => $data,
      ];
    }

    if ($type_parameter == "C") {
      if ($search) {
        $data = ParameterSatuanKlinik::where('parameter_jenis_klinik', $jenis_parameter)
          ->orderby('name_parameter_satuan_klinik', 'asc')
          ->limit(10)
          ->get();
      } else {
        $data = ParameterSatuanKlinik::where('parameter_jenis_klinik', $jenis_parameter)
          ->where('name_parameter_satuan_klinik', 'like', '%' . $search . '%')
          ->orderby('name_parameter_satuan_klinik', 'asc')
          ->limit(10)
          ->get();
      }

      foreach ($data as $item) {
        $response[] = array(
          "id" => $item->id_parameter_satuan_klinik,
          "text" => $item->name_parameter_satuan_klinik,
          "harga" => $item->harga_satuan_parameter_satuan_klinik,
        );
      }

      $set_data = [
        'data' => $data,
      ];
    }

    return response()->json($response);
  }

  public function countParameterDanHarga(Request $request)
  {
    $jenis_parameter = $request->jenis_parameter;
    $type_parameter = $request->type_parameter;
    $satuan_parameter = $request->satuan_parameter;
    $harga_satuan = 0;
    $response = array();

    if ($type_parameter == "P") {
      $data = ParameterPaketKlinik::where('id_parameter_paket_klinik', $satuan_parameter)
        ->whereNull('deleted_at')
        ->first();

      if (isset($data)) {
        $response[] = array(
          "harga" => $data->harga_parameter_paket_klinik,
        );
      } else {
        $response[] = array(
          "harga" => 0,
        );
      }
    }

    if (isset($jenis_parameter) || isset($type_parameter) || isset($satuan_parameter)) {
      if ($type_parameter == "C") {
        if (isset($request->satuan_parameter)) {
          $count_parameter = count($request->satuan_parameter);

          for ($i = 0; $i < $count_parameter; $i++) {
            // print_r($jenis_parameter . ', ');

            $data = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->satuan_parameter[$i])
              ->where('parameter_jenis_klinik', $jenis_parameter)
              ->whereNull('deleted_at')
              ->first();

            $harga_satuan = $harga_satuan + $data->harga_satuan_parameter_satuan_klinik;
          }

          // dd();
        } else {
          $harga_satuan = 0;
        }

        if (isset($data)) {
          if ($harga_satuan > 0 || $harga_satuan !== null) {
            $get_harga_satuan = $harga_satuan;
          } else {
            $get_harga_satuan = 0;
          }

          $response[] = array(
            "harga" => $get_harga_satuan,
          );
        } else {
          $response[] = array(
            "harga" => 0,
          );
        }
      }
    }

    return response()->json($response);
  }

  private function _storePermohonanUjiParameter(Request $request)
  {
    $validator = $this->rules_permohonan_uji_parameter($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {

      DB::beginTransaction();

      try {
        if (isset($request->jenis_parameter) || $request->jenis_parameter !== null || count($request->jenis_parameter) > 0) {
          // update ke permohonan uji klinik
          $post = PermohonanUjiKlinik::find($request->permohonan_uji_klinik);
          $post->total_harga_permohonan_uji_klinik = $request->post('subamount_harga_parameter');
          $post->status_permohonan_uji_klinik = 'ANALIS';
          $simpan_post = $post->save();

          // store ke permohonan uji paket klinik
          $no_ajaib = array();

          // store ke permohonan uji paket klinik
          foreach ($request->jenis_parameter as $key => $value) {
            $post_paket = new PermohonanUjiPaketKlinik();
            $post_paket->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
            $post_paket->parameter_jenis_klinik = $request->post('jenis_parameter')[$key];
            $post_paket->type_permohonan_uji_paket_klinik = $request->post('type_parameter')[$key];

            if ($request->post('type_parameter')[$key] == "P") {
              $post_paket->parameter_paket_klinik = $request->post('satuan_parameter')[$key][0];
            } else {
              $post_paket->parameter_paket_klinik = null;
            }

            $post_paket->harga_permohonan_uji_paket_klinik = $request->post('harga_parameter')[$key];

            $simpan_post_paket = $post_paket->save();

            array_push($no_ajaib, $post_paket->id_permohonan_uji_paket_klinik);
          }

          // dd($no_ajaib);

          $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($request->permohonan_uji_klinik);

          foreach ($no_ajaib as $key => $value) {
            if ($request->post('type_parameter')[$key + 1] == "P") {
              // jika memilih paket name field satuan akan menjadi parameter_paket_klinik
              $data_parameter_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $request->post('satuan_parameter')[$key + 1])
                ->whereNull('deleted_at')
                ->get();

              // mapping data parmeter satuan untuk dimasukkan ke permohonanujiparameterklinik
              foreach ($data_parameter_paket as $key3 => $value3) {
                // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key])
                  ->where('parameter_satuan_klinik_id', $value3->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '0')
                  ->first();

                // cek dulu apakah ada data dengan data khusus sperti general atau specific
                if ($check_parameter_by_baku_mutu) {
                  $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                } else {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key])
                    ->where('parameter_satuan_klinik_id', $value3->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->where(function ($query) use ($pasien_umur) {
                      $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                        ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                    })
                    ->first();
                  if (!isset($item_parameter_by_baku_mutu)) {
                    $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key])
                      ->where('parameter_satuan_klinik_id', $value3->parameter_satuan_klinik)
                      ->where('is_khusus_baku_mutu', '1')
                      ->where('gender_baku_mutu', $pasien_gender)
                      ->first();
                  }
                }

                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                $post_parameter->permohonan_uji_paket_klinik = $value;
                $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key + 1][0];
                $post_parameter->parameter_satuan_klinik = $value3->parameter_satuan_klinik;
                $post_parameter->harga_permohonan_uji_parameter_klinik = $value3->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

                if (isset($item_parameter_by_baku_mutu->unit_id)) {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                } else {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                }

                $simpan_post_parameter = $post_parameter->save();

                // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
                $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value3->parameter_satuan_klinik)
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

            if ($request->post('type_parameter')[$key + 1] == "C") {
              foreach ($request->post('satuan_parameter')[$key + 1] as $key_c => $value_c) {
                // #attempt 1
                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                $post_parameter->permohonan_uji_paket_klinik = $value;
                $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key + 1][$key_c];

                // get harga parameter
                $item_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key + 1][$key_c])->first();

                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter->harga_satuan_parameter_satuan_klinik;

                $simpan_post_parameter = $post_parameter->save();

                // #attempt 2
              }
            }
          }

          // store ke permohonan uji parameter klinik
          /* foreach ($request->jenis_parameter as $key => $value) {
          $data_permohonan_paket_p = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))->where('parameter_jenis_klinik', $request->post('jenis_parameter')[$key])->where('type_permohonan_uji_paket_klinik', 'P')->whereNotNull('parameter_paket_klinik')->first();

          $data_permohonan_paket_c = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))
          ->where('parameter_jenis_klinik', $request->post('jenis_parameter')[$key])
          ->where('type_permohonan_uji_paket_klinik', 'C')
          ->whereNull('parameter_paket_klinik')
          ->get();

          foreach ($request->post('satuan_parameter')[$key] as $key2 => $value2) {
          if ($request->post('type_parameter')[$key] == "P") {
          $data_parameter_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $request->post('satuan_parameter')[$key][$key2])
          ->whereNull('deleted_at')
          ->get();

          foreach ($data_parameter_paket as $key3 => $value3) {
          $post_parameter = new PermohonanUjiParameterKlinik();
          $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          $post_parameter->permohonan_uji_paket_klinik = $data_permohonan_paket_p->id_permohonan_uji_paket_klinik;
          $post_parameter->parameter_satuan_klinik = $value3->parameter_satuan_klinik;
          $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key][0];

          $simpan_post_parameter = $post_parameter->save();
          }
          }

          if ($request->post('type_parameter')[$key] == "C") {
          foreach ($data_permohonan_paket_c as $key_ppc => $value_ppc) {
          $post_parameter = new PermohonanUjiParameterKlinik();
          $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          $post_parameter->permohonan_uji_paket_klinik = $value_ppc->id_permohonan_uji_paket_klinik;
          $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key][$key2];

          // get harga parameter
          $item_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key][$key2])->first();

          $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter->harga_satuan_parameter_satuan_klinik;

          $simpan_post_parameter = $post_parameter->save();
          }
          }
          }
          } */

          if (($request->complete_step != null && $request->complete_step == 1)) {
            $urlNextStep = route('elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', $post->id_permohonan_uji_klinik);
          } else {
            $urlNextStep = route('elits-permohonan-uji-klinik.index');
          }

          DB::commit();

          if ($simpan_post == true && $simpan_post_paket == true && $simpan_post_parameter == true) {
            return response()->json(['status' => true, 'pesan' => "Data permohonan uji paket klinik berhasil disimpan!", 'urlNextStep' => $urlNextStep], 200);
          } else {
            return response()->json(['status' => false, 'pesan' => "Data permohonan uji paket klinik tidak berhasil disimpan!"], 200);
          }
        }
      } catch (Exception $e) {
        DB::rollback();
        return response()->json(['status' => false, 'pesan' => "System gagal menyimpan data permohonan uji paket klinik!"], 200);
      }
    }
  }

  public function storePermohonanUjiParameter(Request $request)
  {
    $validator = $this->rules_permohonan_uji_parameter($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {

      // DB::beginTransaction();

      // try {
      if (isset($request->jenis_parameter) || $request->jenis_parameter !== null || count($request->jenis_parameter) > 0) {
        // update ke permohonan uji klinik

        $post = PermohonanUjiKlinik::find($request->permohonan_uji_klinik);
        $post->total_harga_permohonan_uji_klinik = $request->post('subamount_harga_parameter');
        $post->status_permohonan_uji_klinik = 'ANALIS';
        $simpan_post = $post->save();

        // store ke permohonan uji paket klinik
        $no_ajaib = array();


        // dd($request->all());

        // store ke permohonan uji paket klinik
        foreach ($request->type_parameter as $key => $value) {
          $post_paket = new PermohonanUjiPaketKlinik();
          $post_paket->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          $post_paket->parameter_jenis_klinik = $request->post('jenis_parameter')[$key];
          $post_paket->type_permohonan_uji_paket_klinik = $request->post('type_parameter')[$key];

          if ($request->post('type_parameter')[$key] == "P") {
            if (is_array($request->post('satuan_parameter')[$key])) {
              $post_paket->parameter_paket_klinik = $request->post('satuan_parameter')[$key][0];
            } else {
              $post_paket->parameter_paket_klinik = $request->post('satuan_parameter')[$key];
            }
          } else {
            $post_paket->parameter_paket_klinik = null;
          }

          $post_paket->harga_permohonan_uji_paket_klinik = $request->post('harga_parameter')[$key];

          $simpan_post_paket = $post_paket->save();

          array_push($no_ajaib, $post_paket->id_permohonan_uji_paket_klinik);
        }



        $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($request->permohonan_uji_klinik);

        foreach ($no_ajaib as $key => $value) {
          // if ($request->post('type_parameter')[$key + 1] == "P") {
          //   // jika memilih paket name field satuan akan menjadi parameter_paket_klinik
          //   $data_parameter_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $request->post('satuan_parameter')[$key + 1])
          //     ->whereNull('deleted_at')
          //     ->get();

          //   // mapping data parmeter satuan untuk dimasukkan ke permohonanujiparameterklinik
          //   foreach ($data_parameter_paket as $key3 => $value3) {
          //     // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
          //     $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
          //     $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

          //     $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key + 1])
          //       ->where('parameter_satuan_klinik_id', $value3->parameter_satuan_klinik)
          //       ->where('is_khusus_baku_mutu', '0')
          //       ->first();

          //     // cek dulu apakah ada data dengan data khusus sperti general atau specific
          //     if ($check_parameter_by_baku_mutu) {
          //       $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
          //     } else {
          //       $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key + 1])
          //         ->where('parameter_satuan_klinik_id', $value3->parameter_satuan_klinik)
          //         ->where('is_khusus_baku_mutu', '1')
          //         ->where('gender_baku_mutu', $pasien_gender)
          //         ->where(function ($query) use ($pasien_umur) {
          //           $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
          //             ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
          //         })
          //         ->first();
          //     }

          //     $post_parameter = new PermohonanUjiParameterKlinik();
          //     $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          //     $post_parameter->permohonan_uji_paket_klinik = $value;
          //     $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key + 1][0];
          //     $post_parameter->parameter_satuan_klinik = $value3->parameter_satuan_klinik;
          //     $post_parameter->harga_permohonan_uji_parameter_klinik = $value3->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

          //     if (isset($item_parameter_by_baku_mutu->unit_id)) {
          //       $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
          //       $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
          //     } else {
          //       $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
          //       $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
          //     }

          //     $simpan_post_parameter = $post_parameter->save();

          //     // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
          //     $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value3->parameter_satuan_klinik)
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

          if ($request->post('type_parameter')[$key + 1] == "P") {
            $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $request->post('satuan_parameter')[$key + 1])
              ->whereNull('deleted_at')
              ->first();

            #looping jenis parameter paket
            foreach ($parameterpaketklinik->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
              # code...
              #looping satuan parameter dari paket
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
                $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                $post_parameter->permohonan_uji_paket_klinik = $value;
                $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
                $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key + 1];
                $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
                $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

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
              }
            }
          }

          if ($request->post('type_parameter')[$key + 1] == "C") {
            $no_param = 0;
            foreach ($request->post('satuan_parameter')[$key + 1] as $key_c => $value_c) {
              // #attempt 1
              /* $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
              $post_parameter->permohonan_uji_paket_klinik = $value;
              $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key + 1][$key_c];

              // get harga parameter
              $item_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key + 1][$key_c])->first();

              $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter->harga_satuan_parameter_satuan_klinik;

              $simpan_post_parameter = $post_parameter->save(); */

              // #attempt 2
              $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
              $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key + 1])
                ->where('parameter_satuan_klinik_id', $request->post('satuan_parameter')[$key + 1][$key_c])
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key + 1])
                  ->where('parameter_satuan_klinik_id', $request->post('satuan_parameter')[$key + 1][$key_c])
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->post('jenis_parameter')[$key + 1])
                    ->where('parameter_satuan_klinik_id', $request->post('satuan_parameter')[$key + 1][$key_c])
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }

              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
              $post_parameter->permohonan_uji_paket_klinik = $value;

              $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key + 1][$key_c];
              $post_parameter->jenis_parameter_klinik_id  = $request->post('jenis_parameter')[$key + 1];

              $post_parameter->sort_jenis_klinik = $key + 1;
              $post_parameter->sorting_parameter_satuan = $no_param;


              // get harga parameter
              $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key + 1][$key_c])->first();

              $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              $simpan_post_parameter = $post_parameter->save();

              // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $request->post('satuan_parameter')[$key + 1][$key_c])
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
                    }
                  }

                  $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                }
              }
            }
          }
        }

        if (($request->complete_step != null && $request->complete_step == 1)) {
          $urlNextStep = route('elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', $post->id_permohonan_uji_klinik);
        } else {
          $urlNextStep = route('elits-permohonan-uji-klinik.index');
        }

        DB::commit();

        if ($simpan_post == true && $simpan_post_paket == true && $simpan_post_parameter == true) {
          return response()->json(['status' => true, 'pesan' => "Data permohonan uji paket klinik berhasil disimpan!", 'urlNextStep' => $urlNextStep], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data permohonan uji paket klinik tidak berhasil disimpan!"], 200);
        }
      }
      //   } catch (Exception $e) {
      //     DB::rollback();
      //     return response()->json(['status' => false, 'pesan' => "System gagal menyimpan data permohonan uji paket klinik!"], 200);
      //   }
    }
  }

  public function updatePermohonanUjiParameter(Request $request, $id_permohonan_uji_klinik)
  {
    $validator = $this->rules_permohonan_uji_parameter($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      // dd($request->all());

      DB::beginTransaction();

      try {
        if (isset($request->jenis_parameter) || $request->jenis_parameter !== null || count($request->jenis_parameter) > 0) {
          // update ke permohonan uji klinik
          $post = PermohonanUjiKlinik::find($request->permohonan_uji_klinik);
          $post->total_harga_permohonan_uji_klinik = $request->post('subamount_harga_parameter');
          $simpan_post = $post->save();

          // delete
          PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)->delete();
          PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)->delete();

          $no_ajaib = array();

          // store ke permohonan uji paket klinik
          foreach ($request->jenis_parameter as $key => $value) {
            $post_paket = new PermohonanUjiPaketKlinik();
            $post_paket->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
            $post_paket->parameter_jenis_klinik = $request->post('jenis_parameter')[$key];
            $post_paket->type_permohonan_uji_paket_klinik = $request->post('type_parameter')[$key];

            if ($request->post('type_parameter')[$key] == "P") {
              $post_paket->parameter_paket_klinik = $request->post('satuan_parameter')[$key][0];
            }

            $post_paket->harga_permohonan_uji_paket_klinik = $request->post('harga_parameter')[$key];

            $simpan_post_paket = $post_paket->save();

            array_push($no_ajaib, $post_paket->id_permohonan_uji_paket_klinik);
          }

          // dd($no_ajaib);

          foreach ($no_ajaib as $key => $value) {
            if ($request->post('type_parameter')[$key + 1] == "P") {
              $data_parameter_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $request->post('satuan_parameter')[$key + 1])
                ->whereNull('deleted_at')
                ->get();

              foreach ($data_parameter_paket as $key3 => $value3) {
                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                $post_parameter->permohonan_uji_paket_klinik = $value;
                $post_parameter->parameter_satuan_klinik = $value3->parameter_satuan_klinik;
                $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key + 1][0];

                $simpan_post_parameter = $post_parameter->save();
              }
            }

            if ($request->post('type_parameter')[$key + 1] == "C") {
              foreach ($request->post('satuan_parameter')[$key + 1] as $key_c => $value_c) {
                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                $post_parameter->permohonan_uji_paket_klinik = $value;
                $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key + 1][$key_c];

                // get harga parameter
                $item_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key + 1][$key_c])->first();

                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter->harga_satuan_parameter_satuan_klinik;

                $simpan_post_parameter = $post_parameter->save();
              }
            }
          }

          // store ke permohonan uji parameter klinik
          /* foreach ($request->jenis_parameter as $key => $value) {
          $data_permohonan_paket_p = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))
          ->where('parameter_jenis_klinik', $request->post('jenis_parameter')[$key])
          ->where('type_permohonan_uji_paket_klinik', 'P')
          ->whereNotNull('parameter_paket_klinik')
          ->first();

          $data_permohonan_paket_c = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))
          ->where('parameter_jenis_klinik', $request->post('jenis_parameter')[$key])
          ->where('type_permohonan_uji_paket_klinik', 'C')
          ->whereNull('parameter_paket_klinik')
          ->get();

          foreach ($request->post('satuan_parameter')[$key] as $key2 => $value2) {
          if ($request->post('type_parameter')[$key] == "P") {
          $data_parameter_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $request->post('satuan_parameter')[$key][$key2])
          ->whereNull('deleted_at')
          ->get();

          foreach ($data_parameter_paket as $key3 => $value3) {
          $post_parameter = new PermohonanUjiParameterKlinik();
          $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          $post_parameter->permohonan_uji_paket_klinik = $data_permohonan_paket_p->id_permohonan_uji_paket_klinik;
          $post_parameter->parameter_satuan_klinik = $value3->parameter_satuan_klinik;
          $post_parameter->parameter_paket_klinik = $request->post('satuan_parameter')[$key][0];

          $simpan_post_parameter = $post_parameter->save();
          }
          }

          if ($request->post('type_parameter')[$key] == "C") {
          foreach ($data_permohonan_paket_c as $key_ppc => $value_ppc) {
          $post_parameter = new PermohonanUjiParameterKlinik();
          $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
          $post_parameter->permohonan_uji_paket_klinik = $value_ppc->id_permohonan_uji_paket_klinik;
          $post_parameter->parameter_satuan_klinik = $request->post('satuan_parameter')[$key][$key2];

          // get harga parameter
          $item_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->post('satuan_parameter')[$key][$key2])->first();

          $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter->harga_satuan_parameter_satuan_klinik;

          $simpan_post_parameter = $post_parameter->save();
          }
          }
          }
          } */

          DB::commit();

          if ($simpan_post == true && $simpan_post_paket == true && $simpan_post_parameter == true) {
            return response()->json(['status' => true, 'pesan' => "Data permohonan uji paket klinik berhasil disimpan!"], 200);
          } else {
            return response()->json(['status' => false, 'pesan' => "Data permohonan uji paket klinik tidak berhasil disimpan!"], 200);
          }
        }
      } catch (Exception $e) {
        DB::rollback();
        return response()->json(['status' => false, 'pesan' => "System gagal menyimpan data permohonan uji paket klinik!"], 200);
      }
    }
  }

  public function createPermohonanUjiAnalis($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->toISOString();
    } else {
      $tgl_pengujian = Carbon::now()->toISOString();
    }

    if ($item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik !== null) {
      $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik)->toISOString();
    } else {
      $tgl_spesimen_darah = null;
    }

    if ($item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik)->toISOString();
    } else {
      $tgl_spesimen_urine = null;
    }

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    // array untuk mapping data parameter satuan ke baku mutu
    $arr_permohonan_parameter = [];

    // if (count($data_permohonan_uji_paket_klinik) > 0) {
    //   foreach ($data_permohonan_uji_paket_klinik as $key_paket => $value_paket) {
    //     $item_permohonan_parameter_paket = [];
    //     $item_permohonan_parameter_paket['id_permohonan_uji_paket_klinik'] = $value_paket->id_permohonan_uji_paket_klinik;
    //     $item_permohonan_parameter_paket['permohonan_uji_klinik'] = $value_paket->permohonan_uji_klinik;
    //     $item_permohonan_parameter_paket['parameter_jenis_klinik'] = $value_paket->parameter_jenis_klinik;
    //     $item_permohonan_parameter_paket['type_permohonan_uji_paket_klinik'] = $value_paket->type_permohonan_uji_paket_klinik;
    //     $item_permohonan_parameter_paket['parameter_paket_klinik'] = $value_paket->parameter_paket_klinik;
    //     $item_permohonan_parameter_paket['nama_parameter_paket_klinik'] = $value_paket->parameterpaketklinik->name_parameter_paket_klinik ?? null;
    //     $item_permohonan_parameter_paket['harga_permohonan_uji_paket_klinik'] = $value_paket->harga_permohonan_uji_paket_klinik;

    //     // get data perameter satuan yang ada dalam paket
    //     $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
    //       ->where('permohonan_uji_paket_klinik', $value_paket->id_permohonan_uji_paket_klinik)
    //       // ->groupBy('jenis_parameter_klinik_id')
    //       ->get();

    //     $item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'] = [];

    //     if (count($data_permohonan_uji_satuan_klinik) > 0) {
    //       foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {
    //         // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
    //         // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
    //         $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
    //         $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

    //         $item_permohonan_parameter_satuan = [];
    //         $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
    //         $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
    //         $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
    //         $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
    //         $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
    //         $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
    //         $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

    //         $id_parameter_jenis_klinik = null;
    //         if (isset($value_paket->parameter_jenis_klinik)) {
    //           $id_parameter_jenis_klinik = $value_paket->parameter_jenis_klinik;
    //         } else {
    //           $id_parameter_jenis_klinik = $value_satuan->permohonanujijenispaketklinik->parameter_jenis_klinik_id;
    //         }

    //         $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //           ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //           ->get();

    //         $item_parameter_by_baku_mutu = null;
    //         if (count($data_parameter_by_baku_mutu) > 0) {

    //           $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //             ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //             ->where('is_khusus_baku_mutu', '0')
    //             ->first();

    //           // cek dulu apakah ada data dengan data khusus sperti general atau specific

    //           if ($check_parameter_by_baku_mutu) {
    //             $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
    //           } else {
    //             $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
    //               ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
    //               ->where('is_khusus_baku_mutu', '1')
    //               ->where('gender_baku_mutu', $pasien_gender)
    //               ->where(function ($query) use ($pasien_umur) {
    //                 $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
    //                   ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
    //               })
    //               ->first();
    //           }
    //         }

    //         // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
    //         // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
    //         if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
    //         } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
    //         } else {
    //           $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
    //           $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
    //         }

    //          $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
    // $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
    // $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
    //         $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

    //         // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
    //         $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

    //         $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
    //           ->get();

    //         if (count($data_permohonan_uji_subsatuan_klinik)) {
    //           foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
    //             $item_permohonan_parameter_subsatuan = [];
    //             $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
    //             $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
    //             $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
    //             $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
    //             // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

    //             // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
    //             if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
    //               $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
    //                 ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
    //                 ->first();

    //               if ($item_parameter_subsatuan_by_baku_mutu) {
    //                 $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
    //                 $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
    //               } else {
    //                 $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
    //                 $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
    //               }
    //             } else {
    //               $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
    //               $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
    //             }

    //             if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
    //             } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
    //             } else {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

    //               $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
    //             }

    //             // cek dulu punya satuan di subparameter atau tidak
    //             // jika tidak ada maka ambil ke baku mutu
    //             // else null
    //             /* if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;
    //             } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
    //             } else {
    //               $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;
    //             }

    //             $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
    //             $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik; */

    //             array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
    //           }
    //         }

    //         array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
    //       }
    //     }

    //     array_push($arr_permohonan_parameter, $item_permohonan_parameter_paket);
    //   }
    // }

    $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->orderBy('jenis_parameter_klinik_id', 'ASC')
      ->orderBy('sorting_parameter_satuan', 'ASC')
      ->get();

    $data_jenis_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->groupBy('jenis_parameter_klinik_id')
      ->orderBy('sort_jenis_klinik', 'ASC')
      ->get();

    if (count($data_permohonan_uji_satuan_klinik) > 0) {
      foreach ($data_jenis_parameter_klinik as $key_jenis_parameter_klinik => $value_jenis_parameter_klinik) {
        # code...
        $parameter_jenis_klinik = [];

        $parameter_jenis_klinik['name_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->name_parameter_jenis_klinik;
        $parameter_jenis_klinik['id_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik;
        $parameter_jenis_klinik['item_permohonan_parameter_satuan'] = [];

        foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {

          if ($value_satuan->jenis_parameter_klinik_id == $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik) {

            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $id_parameter_jenis_klinik = $value_satuan->jenis_parameter_klinik_id;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            $item_parameter_by_baku_mutu = null;
            if (count($data_parameter_by_baku_mutu) > 0) {

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific

              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();

                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                // cek dulu punya satuan di subparameter atau tidak
                // jika tidak ada maka ambil ke baku mutu
                // else null
                /* if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                } else {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik; */
                $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = $item_permohonan_parameter_subsatuan;
                // array_push($arr_permohonan_parameter['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($parameter_jenis_klinik['item_permohonan_parameter_satuan'], $item_permohonan_parameter_satuan);
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
          }
        }
        array_push($arr_permohonan_parameter, $parameter_jenis_klinik);
      }
    }









    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.analis-permohonan-uji-paramater-klinik', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_register_permohonan_uji_klinik' => $tgl_register,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_detail_uji_paket' => $data_permohonan_uji_paket_klinik,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
    ]);
  }

  public function storePermohonanUjiAnalis(Request $request, $id_permohonan_uji_klinik)
  {
    try {
      DB::beginTransaction();

      $post = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

      if ($request->post('tglpengujian_permohonan_uji_klinik') !== null) {
        $tglpengujian_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $request->post('tglpengujian_permohonan_uji_klinik'))->format('Y-m-d H:i');
      } else {
        $tglpengujian_permohonan_uji_klinik = null;
      }

      $post->tglpengujian_permohonan_uji_klinik = $tglpengujian_permohonan_uji_klinik;
      $is_not_darah = $request->post('is_not_darah');
      if (!isset($is_not_darah) && $request->post('spesimen_darah_permohonan_uji_klinik') !== null) {
        $spesimen_darah_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $request->post('spesimen_darah_permohonan_uji_klinik'))->format('Y-m-d H:i');
      } else {
        $spesimen_darah_permohonan_uji_klinik = null;
      }

      $post->spesimen_darah_permohonan_uji_klinik = $spesimen_darah_permohonan_uji_klinik;
      $is_not_urine = $request->post('is_not_urine');

      if (!isset($is_not_urine) && $request->post('spesimen_urine_permohonan_uji_klinik') !== null) {
        $spesimen_urine_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $request->post('spesimen_urine_permohonan_uji_klinik'))->format('Y-m-d H:i');
      } else {
        $spesimen_urine_permohonan_uji_klinik = null;
      }

      $post->spesimen_urine_permohonan_uji_klinik = $spesimen_urine_permohonan_uji_klinik;
      $post->analis_permohonan_uji_klinik = $request->analis_permohonan_uji_klinik;
      $post->name_analis_permohonan_uji_klinik = $request->name_analis_permohonan_uji_klinik;
      $post->nip_analis_permohonan_uji_klinik = $request->nip_analis_permohonan_uji_klinik;
      // $post->analisnip_permohonan_uji_klinik = $request->analisnip_permohonan_uji_klinik;
      $post->status_permohonan_uji_klinik = 'SELESAI';
      $simpan = $post->save();

      // mapping data untuk disimpan ke tb_permohonan_uji_paket_klinik, tb_permohonan_uji_parameter_klinik, tb_permohonan_uji_sub_parameter_klinik
      // cek dulu ada foreach untuk field name permohonan_uji_parameter_klinik
      if (count($request->permohonan_uji_parameter_klinik) > 0) {
        // mapping data untuk parameter satuan dulu
        foreach ($request->permohonan_uji_parameter_klinik as $key_permohonan_uji_parameter_klinik => $value_permohonan_uji_parameter_klinik) {
          // cek dulu setiap forloop ada data parameter subsatuan atau tidak
          if (isset($request->parameter_sub_satuan_klinik_id[$value_permohonan_uji_parameter_klinik])) {
            foreach ($request->parameter_sub_satuan_klinik_id[$value_permohonan_uji_parameter_klinik] as $key_parameter_sub_satuan_klinik => $value_parameter_sub_satuan_klinik) {
              // cek dulu di permohonan uji sub parameter klinik sudah ada datanya atau belum
              $check_pu_sub_parameter = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $request->permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik])
                ->where('id_permohonan_uji_sub_parameter_klinik', $request->parameter_sub_satuan_klinik_id[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik])
                ->first();

              /* if (isset($check_pu_sub_parameter)) {
              $post_detail_sub_pupark =  $check_pu_sub_parameter;
              } else {
              $post_detail_sub_pupark = new PermohonanUjiSubParameterKlinik();
              } */

              if (isset($check_pu_sub_parameter)) {
                $post_detail_sub_pupark = $check_pu_sub_parameter;
                /* $post_detail_sub_pupark->permohonan_uji_parameter_klinik_id = $request->permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
                $post_detail_sub_pupark->parameter_sub_satuan_klinik_id = $request->parameter_sub_satuan_klinik_id[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik]; */
                $post_detail_sub_pupark->hasil_permohonan_uji_sub_parameter_klinik = $request->hasil_permohonan_uji_sub_parameter_klinik[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik];
                $post_detail_sub_pupark->flag_permohonan_uji_sub_parameter_klinik = $request->flag_permohonan_uji_sub_parameter_klinik[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik] ?? null;
                $post_detail_sub_pupark->satuan_permohonan_uji_sub_parameter_klinik = $request->satuan_permohonan_uji_sub_parameter_klinik[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik] ?? null;
                $post_detail_sub_pupark->baku_mutu_permohonan_uji_sub_parameter_klinik = $request->baku_mutu_permohonan_uji_sub_parameter_klinik[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik] ?? null;
                $post_detail_sub_pupark->keterangan_permohonan_uji_sub_parameter_klinik = $request->keterangan_permohonan_uji_sub_parameter_klinik[$value_permohonan_uji_parameter_klinik][$value_parameter_sub_satuan_klinik] ?? null;

                $post_detail_sub_pupark->save();
              }
            }
          } else {
            $check_detail_pupark = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
              ->where('id_permohonan_uji_parameter_klinik', $request->permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik])
              ->first();

            $post_detail_pupark = $check_detail_pupark;
            $post_detail_pupark->hasil_permohonan_uji_parameter_klinik = $request->hasil_permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
            $post_detail_pupark->flag_permohonan_uji_parameter_klinik = $request->flag_permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
            $post_detail_pupark->satuan_permohonan_uji_parameter_klinik = $request->satuan_permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
            $post_detail_pupark->baku_mutu_permohonan_uji_parameter_klinik = $request->baku_mutu_permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
            $post_detail_pupark->keterangan_permohonan_uji_parameter_klinik = $request->keterangan_permohonan_uji_parameter_klinik[$value_permohonan_uji_parameter_klinik];
            $post_detail_pupark->save();
          }
        }
      }

      DB::commit();

      if ($simpan == true) {
        return response()->json(['status' => true, 'pesan' => "Data permohonan uji klinik untuk analis berhasil diubah!"], 200);
      } else {
        return response()->json(['status' => false, 'pesan' => "Data permohonan uji klinik untuk analis tidak berhasil diubah!"], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => 'System gagal melakukan perubahan!'], 200);
    }
  }

  public function convertToRoman($integer)
  {
    // Convert the integer into an integer (just to make sure)
    $integer = intval($integer);
    $result = '';

    // Create a lookup array that contains all of the Roman numerals.
    $lookup = array(
      'M' => 1000,
      'CM' => 900,
      'D' => 500,
      'CD' => 400,
      'C' => 100,
      'XC' => 90,
      'L' => 50,
      'XL' => 40,
      'X' => 10,
      'IX' => 9,
      'V' => 5,
      'IV' => 4,
      'I' => 1,
    );

    foreach ($lookup as $roman => $value) {
      // Determine the number of matches
      $matches = intval($integer / $value);

      // Add the same number of characters to the string
      $result .= str_repeat($roman, $matches);

      // Set the integer to be the remainder of the integer and the value
      $integer = $integer % $value;
    }

    // The Roman numeral should be built, return it
    return $result;
  }

  public function getDataPermohonanUjiKlinikPayment(Request $request)
  {
    $id_permohonan_uji_klinik = $request->permohonan_uji_klinik_id;
    $itemPermohonanUjiKlinik = PermohonanUjiKlinik::findOrFail($id_permohonan_uji_klinik);

    $data = [
      'id_permohonan_uji_klinik' => $itemPermohonanUjiKlinik->id_permohonan_uji_klinik ?? null,
      'nota_petugas' => Auth::user()->id,
      'nota_namapetugas' => Auth::user()->name,
      'nama_pasien' => $itemPermohonanUjiKlinik->pasien->nama_pasien ?? null,
      'alamat_pasien' => $itemPermohonanUjiKlinik->pasien
        ? \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($itemPermohonanUjiKlinik->pasien)
        : null,
      'total_harga' => $itemPermohonanUjiKlinik->total_harga_permohonan_uji_klinik,
      'total_harga_custom' => $itemPermohonanUjiKlinik->total_harga_permohonan_uji_klinik != null ? rupiah($itemPermohonanUjiKlinik->total_harga_permohonan_uji_klinik) : 'Rp. 0',
    ];

    return response()->json($data);
  }

  public function storeDataPermohonanUjiKlinikPayment(Request $request)
  {
    $max_nota = (int) PermohonanUjiPaymentKlinik::max('no_nota_permohonan_uji_payment_klinik');

    // store ke payment
    $post = new PermohonanUjiPaymentKlinik();
    $post->permohonan_uji_klinik_id = $request->id_permohonan_uji_klinik ?? null;
    $post->no_nota_permohonan_uji_payment_klinik = $max_nota + 1;
    $post->nota_petugas_permohonan_uji_payment_klinik = $request->nota_petugas_permohonan_uji_payment_klinik ?? null;
    $post->total_harga_permohonan_uji_payment_klinik = $request->total_harga ?? null;
    /* $post->uang_muka_permohonan_uji_payment_klinik = $request->uang_muka_permohonan_uji_payment_klinik ?? null;
    $post->sisa_permohonan_uji_payment_klinik = $request->sisa_permohonan_uji_payment_klinik ?? null;
    $post->catatan_permohonan_uji_payment_klinik = $request->catatan_permohonan_uji_payment_klinik ?? null; */
    $post->date_done_estimation_permohonan_uji_payment_klinik = date('Y-m-d H:i:s', time());
    $simpan = $post->save();

    // update is_paid
    $permohonan_uji_klinik = PermohonanUjiKlinik::findOrFail($request->id_permohonan_uji_klinik);
    $permohonan_uji_klinik->is_paid_permohonan_uji_klinik = '1';
    $simpan_permohonan_uji_klinik = $permohonan_uji_klinik->save();

    DB::commit();

    if ($simpan == true) {
      return response()->json(['status' => true, 'pesan' => "Proses pembayaran berhasil disimpan!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Proses pembayaran tidak berhasil disimpan!"], 200);
    }

    /* DB::beginTransaction();

  try {

  } catch (\Exception $e) {
  DB::rollback();

  return response()->json(['status' => false, 'pesan' => 'System gagal melakukan pembayaran!'], 200);
  } */
  }

  public function createPermohonanUjiRekomendasiDokter($id_permohonan_uji_klinik)
  {
    $item = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    if ($item->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglpengujian_permohonan_uji_klinik)->isoFormat('D/M/Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    if ($item->spesimen_darah_permohonan_uji_klinik !== null) {
      $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item->spesimen_darah_permohonan_uji_klinik)->isoFormat('D/M/Y HH:mm');
    } else {
      $tgl_spesimen_darah = '-';
    }

    if ($item->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item->spesimen_urine_permohonan_uji_klinik)->isoFormat('D/M/Y HH:mm');
    } else {
      $tgl_spesimen_urine = '-';
    }

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    $arr_permohonan_parameter = [];

    $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->orderBy('jenis_parameter_klinik_id', 'ASC')
      ->orderBy('sorting_parameter_satuan', 'ASC')
      ->get();

    $data_jenis_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->groupBy('jenis_parameter_klinik_id')
      ->orderBy('sort_jenis_klinik', 'ASC')
      ->get();




    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if (count($data_permohonan_uji_satuan_klinik) > 0) {
      foreach ($data_jenis_parameter_klinik as $key_jenis_parameter_klinik => $value_jenis_parameter_klinik) {
        # code...
        $parameter_jenis_klinik = [];
        $parameter_jenis_klinik['name_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->name_parameter_jenis_klinik;
        $parameter_jenis_klinik['id_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik;
        $parameter_jenis_klinik['item_permohonan_parameter_satuan'] = [];

        foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {

          if ($value_satuan->jenis_parameter_klinik_id == $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik) {

            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $id_parameter_jenis_klinik = $value_satuan->jenis_parameter_klinik_id;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            $item_parameter_by_baku_mutu = null;
            if (count($data_parameter_by_baku_mutu) > 0) {

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific

              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                // cek dulu punya satuan di subparameter atau tidak
                // jika tidak ada maka ambil ke baku mutu
                // else null
                /* if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                } else {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik; */
                $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = $item_permohonan_parameter_subsatuan;
                // array_push($arr_permohonan_parameter['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($parameter_jenis_klinik['item_permohonan_parameter_satuan'], $item_permohonan_parameter_satuan);
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
          }
        }
        array_push($arr_permohonan_parameter, $parameter_jenis_klinik);
      }
    }

    // dd($data_detail_uji_paket);

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.dokter-permohonan-uji-klinik', [
      'item' => $item,
      'tgl_register_permohonan_uji_klinik' => $tgl_register,
      'tgl_pengujian' => $tgl_pengujian,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_detail_uji_paket' => $data_permohonan_uji_paket_klinik,
    ]);
  }

  public function storePermohonanUjiRekomendasiDokter(Request $request, $id_permohonan_uji_klinik)
  {
    DB::beginTransaction();

    try {
      $post = PermohonanUjiKlinik::findOrFail($id_permohonan_uji_klinik);
      $post->dokter_rekomendasi_permohonan_uji_klinik = Auth::user()->id;
      $post->save();

      $check_detail_klinik = PermohonanUjiAnalisKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)->first();

      if ($check_detail_klinik) {
        $post_detail_klinik = PermohonanUjiAnalisKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)->first();
        $post_detail_klinik->kesimpulan_permohonan_uji_analis_klinik = $request->kesimpulan_permohonan_uji_analis_klinik;
        $post_detail_klinik->kategori_permohonan_uji_analis_klinik = $request->kategori_permohonan_uji_analis_klinik;
        $post_detail_klinik->saran_permohonan_uji_analis_klinik = $request->saran_permohonan_uji_analis_klinik;
        $simpan = $post_detail_klinik->save();
      } else {
        $post_detail_klinik = new PermohonanUjiAnalisKlinik();
        $post_detail_klinik->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
        $post_detail_klinik->kesimpulan_permohonan_uji_analis_klinik = $request->kesimpulan_permohonan_uji_analis_klinik;
        $post_detail_klinik->kategori_permohonan_uji_analis_klinik = $request->kategori_permohonan_uji_analis_klinik;
        $post_detail_klinik->saran_permohonan_uji_analis_klinik = $request->saran_permohonan_uji_analis_klinik;
        $simpan = $post_detail_klinik->save();
      }

      DB::commit();

      if ($simpan == true) {
        return response()->json(['status' => true, 'pesan' => "Data rekomendasi berhasil disimpan!"], 200);
      } else {
        return response()->json(['status' => false, 'pesan' => "Data rekomendasi tidak berhasil disimpan!"], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => 'System gagal melakukan perubahan!'], 200);
    }
  }

  public function _printPermohonanUjiKlinikNota($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $itemPermohonanUjiPaymentKlinik = PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)->first();

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->with(['parameterjenisklinik', 'parameterpaketklinik', 'permohonanujiklinik', 'permohonanujiparameterklinik'])
      ->orderBy('created_at', 'desc')
      ->whereNull('deleted_at')
      ->get();

    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      // ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    // forloop into view
    $detail_permohonan_uji_klinik = [];

    // looping paket dulu
    foreach ($data_permohonan_uji_paket_klinik as $key => $value) {
      $item_data_permohonan_uji_paket_klinik = [];
      $item_data_permohonan_uji_paket_klinik["id_parameter_jenis_klinik"] = $value->parameter_jenis_klinik;
      $item_data_permohonan_uji_paket_klinik["name_parameter_jenis_klinik"] = $value->parameterjenisklinik->name_parameter_jenis_klinik;
      $item_data_permohonan_uji_paket_klinik["type_permohonan_uji_paket_klinik"] = $value->type_permohonan_uji_paket_klinik;
      $item_data_permohonan_uji_paket_klinik["id_parameter_paket_klinik"] = $value->parameter_paket_klinik;
      $item_data_permohonan_uji_paket_klinik["name_parameter_paket_klinik"] = $value->parameterpaketklinik->name_parameter_paket_klinik ?? null;
      $item_data_permohonan_uji_paket_klinik["harga_permohonan_uji_paket_klinik"] = $value->harga_permohonan_uji_paket_klinik;

      // panggil parameter
      $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->where('permohonan_uji_paket_klinik', $value->id_permohonan_uji_paket_klinik)
        ->whereNull('deleted_at')
        ->get();

      $item_data_permohonan_uji_paket_klinik["permohonan_uji_parameter_klinik"] = [];

      foreach ($data_permohonan_uji_parameter_klinik as $key_pupk => $value_pupk) {
        $bakumutu = BakuMutu::where('parameter_jenis_klinik_id', $value->parameter_jenis_klinik)
          ->where('parameter_satuan_klinik_id', $value_pupk->parameter_satuan_klinik)
          ->first();

        $item_permohonan_uji_parameter_klinik = [];
        $item_permohonan_uji_parameter_klinik['permohonan_uji_klinik'] = $value_pupk->permohonan_uji_klinik;
        $item_permohonan_uji_parameter_klinik['permohonan_uji_paket_klinik'] = $value_pupk->permohonan_uji_paket_klinik;
        $item_permohonan_uji_parameter_klinik['id_parameter_satuan_klinik'] = $value_pupk->parameter_satuan_klinik;
        $item_permohonan_uji_parameter_klinik['name_parameter_satuan_klinik'] = $value_pupk->parametersatuanklinik->name_parameter_satuan_klinik;
        $item_permohonan_uji_parameter_klinik['harga_permohonan_uji_parameter_klinik'] = $value_pupk->harga_permohonan_uji_parameter_klinik;
        $item_permohonan_uji_parameter_klinik['hasil_permohonan_uji_parameter_klinik'] = $value_pupk->hasil_permohonan_uji_parameter_klinik;
        $item_permohonan_uji_parameter_klinik['flag_permohonan_uji_parameter_klinik'] = $value_pupk->flag_permohonan_uji_parameter_klinik;
        $item_permohonan_uji_parameter_klinik['id_satuan_permohonan_uji_parameter_klinik'] = $value_pupk->satuan_permohonan_uji_parameter_klinik;
        $item_permohonan_uji_parameter_klinik['name_satuan_permohonan_uji_parameter_klinik'] = $value_pupk->unit->name_unit;
        $item_permohonan_uji_parameter_klinik['id_baku_mutu_permohonan_uji_parameter_klinik'] = $value_pupk->baku_mutu_permohonan_uji_parameter_klinik;
        $item_permohonan_uji_parameter_klinik['keterangan_permohonan_uji_parameter_klinik'] = $value_pupk->keterangan_permohonan_uji_parameter_klinik;

        $item_permohonan_uji_parameter_klinik['permohonan_uji_sub_parameter_klinik'] = [];

        // get data parameter sub satuan

        foreach ($value_pupk->permohonanujisubparameterklinik as $key_puspk => $value_puspk) {
          $item_permohonan_uji_sub_parameter_klinik = [];
          $item_permohonan_uji_sub_parameter_klinik['permohonan_uji_parameter_klinik_id'] = $value_puspk->permohonan_uji_parameter_klinik_id;
          $item_permohonan_uji_sub_parameter_klinik['parameter_sub_satuan_klinik_id'] = $value_puspk->parameter_sub_satuan_klinik_id;
          $item_permohonan_uji_sub_parameter_klinik['name_parameter_sub_satuan_klinik_id'] = $value_puspk->parametersubsatuanklinik->name_parameter_sub_satuan_klinik;
          $item_permohonan_uji_sub_parameter_klinik['hasil_permohonan_uji_sub_parameter_klinik'] = $value_puspk->hasil_permohonan_uji_sub_parameter_klinik;
          $item_permohonan_uji_sub_parameter_klinik['flag_permohonan_uji_sub_parameter_klinik'] = $value_puspk->flag_permohonan_uji_sub_parameter_klinik;
          $item_permohonan_uji_sub_parameter_klinik['satuan_permohonan_uji_sub_parameter_klinik'] = $value_puspk->satuan_permohonan_uji_sub_parameter_klinik;
          $item_permohonan_uji_sub_parameter_klinik['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_puspk->baku_mutu_permohonan_uji_sub_parameter_klinik;
          $item_permohonan_uji_sub_parameter_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_puspk->keterangan_permohonan_uji_sub_parameter_klinik;

          array_push($item_permohonan_uji_parameter_klinik['permohonan_uji_sub_parameter_klinik'], $item_permohonan_uji_sub_parameter_klinik);
        }

        array_push($item_data_permohonan_uji_paket_klinik["permohonan_uji_parameter_klinik"], $item_permohonan_uji_parameter_klinik);
      }

      array_push($detail_permohonan_uji_klinik, $item_data_permohonan_uji_paket_klinik);
    }

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $pdf = PDF::loadView(
      'masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.nota',
      [
        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
        'detail_payment' => $itemPermohonanUjiPaymentKlinik,
        'detail_permohonan_uji_klinik' => $detail_permohonan_uji_klinik,
      ]
    )
      ->setPaper('a5', 'landscape');
    return $pdf->stream();

    // dd($detail_permohonan_uji_klinik);

    /* return view(
  'masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.nota',
  [
  'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
  'detail_payment' => $itemPermohonanUjiPaymentKlinik,
  'tgl_register' => $tgl_register,
  'tgl_pengambilan' => $tgl_pengambilan,
  'tgl_pengujian' => $tgl_pengujian,
  'tgl_spesimen_darah' => $tgl_spesimen_darah,
  'tgl_spesimen_urine' => $tgl_spesimen_urine,
  'detail_permohonan_uji_klinik' => $detail_permohonan_uji_klinik
  ]
  ); */
  }

  public function printPermohonanUjiKlinikNota($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $itemPermohonanUjiPaymentKlinik = PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)->first();

    $parameterCustom = DB::table('tb_permohonan_uji_paket_klinik')->where('tb_permohonan_uji_paket_klinik.permohonan_uji_klinik', '=', $id_permohonan_uji_klinik)
      ->join('tb_permohonan_uji_klinik', function ($join) {
        $join->on('tb_permohonan_uji_klinik.id_permohonan_uji_klinik', '=', 'tb_permohonan_uji_paket_klinik.permohonan_uji_klinik')
          ->whereNull('tb_permohonan_uji_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->join('tb_permohonan_uji_parameter_klinik', function ($join) {
        $join->on('tb_permohonan_uji_paket_klinik.id_permohonan_uji_paket_klinik', '=', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_paket_klinik')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at');
      })
      ->join('ms_parameter_satuan_klinik', function ($join) {
        $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik')
          ->whereNull('ms_parameter_satuan_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at');
      })
      ->where('tb_permohonan_uji_paket_klinik.type_permohonan_uji_paket_klinik', "C")
      ->select(
        'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
        'ms_parameter_satuan_klinik.id_parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.harga_permohonan_uji_parameter_klinik',
        DB::raw('count(*) as count_method')
      )
      ->groupBy(
        'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
        'ms_parameter_satuan_klinik.id_parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
        'tb_permohonan_uji_parameter_klinik.harga_permohonan_uji_parameter_klinik'
      )
      ->get();

    $value_items = array();

    foreach ($parameterCustom as $valueCustom) {
      $nilaiPermohonanUjiKlinik["name_item"] = $valueCustom->name_parameter_satuan_klinik;
      $nilaiPermohonanUjiKlinik["count_item"] = $valueCustom->count_method;
      $nilaiPermohonanUjiKlinik["price_item"] = $valueCustom->harga_permohonan_uji_parameter_klinik;
      $nilaiPermohonanUjiKlinik["total"] = $valueCustom->harga_permohonan_uji_parameter_klinik * $valueCustom->count_method;

      array_push($value_items, $nilaiPermohonanUjiKlinik);
    }

    // dd($value_items);

    $parameterPaket = DB::table('tb_permohonan_uji_paket_klinik')->where('tb_permohonan_uji_paket_klinik.permohonan_uji_klinik', '=', $id_permohonan_uji_klinik)
      ->join('tb_permohonan_uji_klinik', function ($join) {
        $join->on('tb_permohonan_uji_klinik.id_permohonan_uji_klinik', '=', 'tb_permohonan_uji_paket_klinik.permohonan_uji_klinik')
          ->whereNull('tb_permohonan_uji_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->join('ms_parameter_paket_klinik', function ($join) {
        $join->on('ms_parameter_paket_klinik.id_parameter_paket_klinik', '=', 'tb_permohonan_uji_paket_klinik.parameter_paket_klinik')
          ->whereNull('ms_parameter_paket_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
      ->where('tb_permohonan_uji_paket_klinik.type_permohonan_uji_paket_klinik', "P")
      ->select(
        'ms_parameter_paket_klinik.name_parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik',
        DB::raw('count(tb_permohonan_uji_paket_klinik.parameter_paket_klinik) as count_method')
      )
      ->groupBy(
        'ms_parameter_paket_klinik.name_parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.parameter_paket_klinik',
        'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik'
      )
      ->get();

    foreach ($parameterPaket as $valuePaket) {
      $nilaiPermohonanUjiKlinik["name_item"] = $valuePaket->name_parameter_paket_klinik;
      $nilaiPermohonanUjiKlinik["count_item"] = $valuePaket->count_method;
      $nilaiPermohonanUjiKlinik["price_item"] = $valuePaket->harga_permohonan_uji_paket_klinik;
      $nilaiPermohonanUjiKlinik["total"] = $valuePaket->harga_permohonan_uji_paket_klinik * $valuePaket->count_method;

      array_push($value_items, $nilaiPermohonanUjiKlinik);
    }

    $pdf = PDF::loadView(
      'masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.nota',
      [
        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
        'detail_payment' => $itemPermohonanUjiPaymentKlinik,
        'value_items' => $value_items,
      ]
    )
      ->setPaper('a5', 'landscape');
    return $pdf->stream();

    /* return view(
  'masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.nota',
  [
  'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
  'detail_payment' => $itemPermohonanUjiPaymentKlinik,
  'tgl_register' => $tgl_register,
  'tgl_pengambilan' => $tgl_pengambilan,
  'tgl_pengujian' => $tgl_pengujian,
  'tgl_spesimen_darah' => $tgl_spesimen_darah,
  'tgl_spesimen_urine' => $tgl_spesimen_urine,
  'detail_permohonan_uji_klinik' => $detail_permohonan_uji_klinik
  ]
  ); */
  }

  public function printPermohonanUjiKlinikFormulir($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.formulir', [
      "item_permohonan_uji_klinik" => $item_permohonan_uji_klinik
    ]);

    return $pdf->stream();
  }

  public function printPermohonanUjiKlinikKartuMedis($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.kartu-medis', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
    ]);
    return $pdf->stream();

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.kartu-medis', [
  'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik
  ]); */
  }

  public function printPermohonanUjiKlinikHasil($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    if ($item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik !== null) {
      $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_darah = '-';
    }

    if ($item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_urine = '-';
    }

    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $arr_permohonan_parameter = [];
    $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->orderBy('jenis_parameter_klinik_id', 'ASC')
      ->orderBy('sorting_parameter_satuan', 'ASC')
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->orderBy('name_parameter_satuan_klinik', 'asc')
          ->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
          ->whereNull('deleted_at');
      })
      ->get();

    $data_jenis_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->groupBy('jenis_parameter_klinik_id')
      ->orderBy('sort_jenis_klinik', 'ASC')
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->orderBy('name_parameter_satuan_klinik', 'asc')
          ->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
          ->whereNull('deleted_at');
      })
      ->get();

    if (count($data_permohonan_uji_satuan_klinik) > 0) {
      foreach ($data_jenis_parameter_klinik as $key_jenis_parameter_klinik => $value_jenis_parameter_klinik) {
        $parameter_jenis_klinik = [];
        $parameter_jenis_klinik['name_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->name_parameter_jenis_klinik;
        $parameter_jenis_klinik['id_parameter_jenis_klinik'] = $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik;
        $parameter_jenis_klinik['item_permohonan_parameter_satuan'] = [];

        foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {

          if ($value_satuan->jenis_parameter_klinik_id == $value_jenis_parameter_klinik->jenisparameterklinik->id_parameter_jenis_klinik) {

            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $id_parameter_jenis_klinik = $value_satuan->jenis_parameter_klinik_id;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            $item_parameter_by_baku_mutu = null;
            if (count($data_parameter_by_baku_mutu) > 0) {

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();

                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                // cek dulu punya satuan di subparameter atau tidak
                // jika tidak ada maka ambil ke baku mutu
                // else null
                /* if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                } else {
                $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik; */
                $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = $item_permohonan_parameter_subsatuan;
                // array_push($arr_permohonan_parameter['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($parameter_jenis_klinik['item_permohonan_parameter_satuan'], $item_permohonan_parameter_satuan);
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
          }
        }

        array_push($arr_permohonan_parameter, $parameter_jenis_klinik);
      }
    }

    $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // permintaan pertama
      // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      // permintaan kedua
      $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $get_nomor_lhu = $no_LHU->nomer_LHU;
      $explode_nomor_lhu = explode("/", $get_nomor_lhu);
      $get_pisah0_lhu = $explode_nomor_lhu[0];
      $get_pisah2_lhu = $explode_nomor_lhu[2];
      $get_pisah3_lhu = $explode_nomor_lhu[3];

      $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-klinik', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_parameter_satuan' => $data_parameter_satuan,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
      'no_LHU' => $no_LHU
    ]);

    return $pdf->stream($no_LHU);

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-klinik', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_parameter_satuan' => $data_parameter_satuan,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
      'no_LHU' => $no_LHU
    ]); */
  }

  public function printPermohonanUjiKlinikHasilRapidAntibody($id_permohonan_uji_klinik)
  {
    // dd(PermohonanUjiParameterKlinik::first());
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    // non sedimen
    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // permintaan pertama
      // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      // permintaan kedua
      $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $get_nomor_lhu = $no_LHU->nomer_LHU;
      $explode_nomor_lhu = explode("/", $get_nomor_lhu);
      $get_pisah0_lhu = $explode_nomor_lhu[0];
      $get_pisah2_lhu = $explode_nomor_lhu[2];
      $get_pisah3_lhu = $explode_nomor_lhu[3];

      $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
    }

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    // array untuk mapping data parameter satuan ke baku mutu
    $arr_permohonan_parameter = [];

    if (count($data_permohonan_uji_paket_klinik) > 0) {
      foreach ($data_permohonan_uji_paket_klinik as $key_paket => $value_paket) {
        $item_permohonan_parameter_paket = [];
        $item_permohonan_parameter_paket['id_permohonan_uji_paket_klinik'] = $value_paket->id_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['permohonan_uji_klinik'] = $value_paket->permohonan_uji_klinik;
        $item_permohonan_parameter_paket['parameter_jenis_klinik'] = $value_paket->parameter_jenis_klinik;
        $item_permohonan_parameter_paket['type_permohonan_uji_paket_klinik'] = $value_paket->type_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['parameter_paket_klinik'] = $value_paket->parameter_paket_klinik;
        $item_permohonan_parameter_paket['nama_parameter_paket_klinik'] = $value_paket->parameterpaketklinik->name_parameter_paket_klinik ?? null;
        $item_permohonan_parameter_paket['harga_permohonan_uji_paket_klinik'] = $value_paket->harga_permohonan_uji_paket_klinik;

        // get data perameter satuan yang ada dalam paket
        $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
          ->where('permohonan_uji_paket_klinik', $value_paket->id_permohonan_uji_paket_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
              ->whereNull('deleted_at')
              ->orderBy('name_parameter_satuan_klinik', 'asc');
          })
          ->get();

        $item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'] = [];

        if (count($data_permohonan_uji_satuan_klinik) > 0) {
          foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            if (count($data_parameter_by_baku_mutu) > 0) {
              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter, $item_permohonan_parameter_paket);
      }
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-rapid-antibody', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
    ]);
    return $pdf->stream($no_LHU);

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-rapid-antibody', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter
    ]); */
  }

  public function printPermohonanUjiKlinikHasilRapidAntigen($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    // non sedimen
    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // permintaan pertama
      // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      // permintaan kedua
      $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $get_nomor_lhu = $no_LHU->nomer_LHU;
      $explode_nomor_lhu = explode("/", $get_nomor_lhu);
      $get_pisah0_lhu = $explode_nomor_lhu[0];
      $get_pisah2_lhu = $explode_nomor_lhu[2];
      $get_pisah3_lhu = $explode_nomor_lhu[3];

      $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
    }

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    // array untuk mapping data parameter satuan ke baku mutu
    $arr_permohonan_parameter = [];

    if (count($data_permohonan_uji_paket_klinik) > 0) {
      foreach ($data_permohonan_uji_paket_klinik as $key_paket => $value_paket) {
        $item_permohonan_parameter_paket = [];
        $item_permohonan_parameter_paket['id_permohonan_uji_paket_klinik'] = $value_paket->id_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['permohonan_uji_klinik'] = $value_paket->permohonan_uji_klinik;
        $item_permohonan_parameter_paket['parameter_jenis_klinik'] = $value_paket->parameter_jenis_klinik;
        $item_permohonan_parameter_paket['type_permohonan_uji_paket_klinik'] = $value_paket->type_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['parameter_paket_klinik'] = $value_paket->parameter_paket_klinik;
        $item_permohonan_parameter_paket['nama_parameter_paket_klinik'] = $value_paket->parameterpaketklinik->name_parameter_paket_klinik ?? null;
        $item_permohonan_parameter_paket['harga_permohonan_uji_paket_klinik'] = $value_paket->harga_permohonan_uji_paket_klinik;

        // get data perameter satuan yang ada dalam paket
        $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
          ->where('permohonan_uji_paket_klinik', $value_paket->id_permohonan_uji_paket_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
              ->whereNull('deleted_at')
              ->orderBy('name_parameter_satuan_klinik', 'asc');
          })
          ->get();

        $item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'] = [];

        if (count($data_permohonan_uji_satuan_klinik) > 0) {
          foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            if (count($data_parameter_by_baku_mutu) > 0) {
              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter, $item_permohonan_parameter_paket);
      }
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-rapid-antigen', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
    ]);
    return $pdf->stream($no_LHU);

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-rapid-antigen', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter
    ]); */
  }

  public function printPermohonanUjiKlinikHasilPcr($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    // non sedimen
    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // permintaan pertama
      // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      // permintaan kedua
      $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $get_nomor_lhu = $no_LHU->nomer_LHU;
      $explode_nomor_lhu = explode("/", $get_nomor_lhu);
      $get_pisah0_lhu = $explode_nomor_lhu[0];
      $get_pisah2_lhu = $explode_nomor_lhu[2];
      $get_pisah3_lhu = $explode_nomor_lhu[3];

      $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
    }

    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    // array untuk mapping data parameter satuan ke baku mutu
    $arr_permohonan_parameter = [];

    if (count($data_permohonan_uji_paket_klinik) > 0) {
      foreach ($data_permohonan_uji_paket_klinik as $key_paket => $value_paket) {
        $item_permohonan_parameter_paket = [];
        $item_permohonan_parameter_paket['id_permohonan_uji_paket_klinik'] = $value_paket->id_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['permohonan_uji_klinik'] = $value_paket->permohonan_uji_klinik;
        $item_permohonan_parameter_paket['parameter_jenis_klinik'] = $value_paket->parameter_jenis_klinik;
        $item_permohonan_parameter_paket['type_permohonan_uji_paket_klinik'] = $value_paket->type_permohonan_uji_paket_klinik;
        $item_permohonan_parameter_paket['parameter_paket_klinik'] = $value_paket->parameter_paket_klinik;
        $item_permohonan_parameter_paket['nama_parameter_paket_klinik'] = $value_paket->parameterpaketklinik->name_parameter_paket_klinik ?? null;
        $item_permohonan_parameter_paket['harga_permohonan_uji_paket_klinik'] = $value_paket->harga_permohonan_uji_paket_klinik;

        // get data perameter satuan yang ada dalam paket
        $data_permohonan_uji_satuan_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
          ->where('permohonan_uji_paket_klinik', $value_paket->id_permohonan_uji_paket_klinik)
          ->whereHas('parametersatuanklinik', function ($query) {
            return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
              ->whereNull('deleted_at')
              ->orderBy('name_parameter_satuan_klinik', 'asc');
          })
          ->get();

        $item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'] = [];

        if (count($data_permohonan_uji_satuan_klinik) > 0) {
          foreach ($data_permohonan_uji_satuan_klinik as $key_satuan => $value_satuan) {
            // get data baku mutu untuk menyiapkan data nilai rujukan jika ada dimasukkan jika tidak maka akan dilewati
            // melewati data kondisi pasien berupa umur dan gender dahulu ya kawaaaaan
            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

            $item_permohonan_parameter_satuan = [];
            $item_permohonan_parameter_satuan['id_permohonan_uji_parameter_klinik'] = $value_satuan->id_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_klinik'] = $value_satuan->permohonan_uji_klinik;
            $item_permohonan_parameter_satuan['permohonan_uji_paket_klinik'] = $value_satuan->permohonan_uji_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_paket_klinik'] = $value_satuan->parameter_paket_klinik;
            $item_permohonan_parameter_satuan['parameter_satuan_klinik'] = $value_satuan->parameter_satuan_klinik;
            $item_permohonan_parameter_satuan['nama_parameter_satuan_klinik'] = $value_satuan->parametersatuanklinik->name_parameter_satuan_klinik ?? null;
            $item_permohonan_parameter_satuan['harga_permohonan_uji_parameter_klinik'] = $value_satuan->harga_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['hasil_permohonan_uji_parameter_klinik'] = $value_satuan->hasil_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['flag_permohonan_uji_parameter_klinik'] = $value_satuan->flag_permohonan_uji_parameter_klinik;
            $item_permohonan_parameter_satuan['keterangan_permohonan_uji_parameter_klinik'] = $value_satuan->keterangan_permohonan_uji_parameter_klinik;

            $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
              ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
              ->get();

            if (count($data_parameter_by_baku_mutu) > 0) {
              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();
                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_paket->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $value_satuan->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }
            }

            // jika tidak ada satuan di permohonan uji satuan maka ambil value dari baku mutu
            // jika ada nilai satuan di permohonan uji satuan maka ambil nilai dari tabel itu sendiri
            if ($value_satuan->satuan_permohonan_uji_parameter_klinik != null) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $value_satuan->satuan_permohonan_uji_parameter_klinik;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $value_satuan->unit->name_unit ?? null;
            } else if (isset($item_parameter_by_baku_mutu->unit_id)) {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit_id;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = $item_parameter_by_baku_mutu->unit->name_unit ?? null;
            } else {
              $item_permohonan_parameter_satuan['satuan_permohonan_uji_parameter_klinik'] = null;
              $item_permohonan_parameter_satuan['nama_satuan_permohonan_uji_parameter_klinik'] = null;
            }

            $item_permohonan_parameter_satuan['min'] = $item_parameter_by_baku_mutu->min ?? null;
            $item_permohonan_parameter_satuan['max'] = $item_parameter_by_baku_mutu->max ?? null;
            $item_permohonan_parameter_satuan['equal'] = $item_parameter_by_baku_mutu->equal ?? null;
            $item_permohonan_parameter_satuan['nilai_baku_mutu'] = $item_parameter_by_baku_mutu->nilai_baku_mutu ?? null;

            // menyiapkan data array untuk mapping jika data parameter memiliki subsatuan
            $item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'] = [];

            $data_permohonan_uji_subsatuan_klinik = PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value_satuan->id_permohonan_uji_parameter_klinik)
              ->get();

            if (count($data_permohonan_uji_subsatuan_klinik)) {
              foreach ($data_permohonan_uji_subsatuan_klinik as $key_subsatuan => $value_subsatuan) {
                $item_permohonan_parameter_subsatuan = [];
                $item_permohonan_parameter_subsatuan['id_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->id_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['permohonan_uji_parameter_klinik_id'] = $value_subsatuan->permohonan_uji_parameter_klinik_id;
                $item_permohonan_parameter_subsatuan['parameter_sub_satuan_klinik_id'] = $value_subsatuan->parameter_sub_satuan_klinik_id;
                $item_permohonan_parameter_subsatuan['nama_parameter_sub_satuan_klinik_id'] = $value_subsatuan->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? null;
                $item_permohonan_parameter_subsatuan['hasil_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->hasil_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['flag_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->flag_permohonan_uji_sub_parameter_klinik;
                // $item_permohonan_parameter_subsatuan['baku_mutu_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik;
                $item_permohonan_parameter_subsatuan['keterangan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->keterangan_permohonan_uji_sub_parameter_klinik;

                // cek data baku mutu jika memiliki data subsatuan dengan nilai rujukan
                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                  $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                    ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_subsatuan->parameter_sub_satuan_klinik_id)
                    ->first();

                  if ($item_parameter_subsatuan_by_baku_mutu) {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->min_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->max_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->equal_baku_mutu_detail_parameter_klinik;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->nilai_baku_mutu_detail_parameter_klinik;
                  } else {
                    $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                    $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                  }
                } else {
                  $item_permohonan_parameter_subsatuan['min_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['max_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['equal_baku_mutu_detail_parameter_klinik'] = null;
                  $item_permohonan_parameter_subsatuan['nilai_baku_mutu_detail_parameter_klinik'] = null;
                }

                if ($value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik != null) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->satuan_permohonan_uji_sub_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $value_subsatuan->unit->name_unit ?? null;
                } else if (isset($item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik)) {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = $item_parameter_subsatuan_by_baku_mutu->unit->name_unit ?? null;
                } else {
                  $item_permohonan_parameter_subsatuan['satuan_permohonan_uji_sub_parameter_klinik'] = null;

                  $item_permohonan_parameter_subsatuan['nama_satuan_permohonan_uji_sub_parameter_klinik'] = null;
                }

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter, $item_permohonan_parameter_paket);
      }
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-pcr', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter,
    ]);
    return $pdf->stream($no_LHU);

    /*  return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-pcr', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'tgl_pengujian' => $tgl_pengujian,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'arr_permohonan_parameter' => $arr_permohonan_parameter
    ]); */
  }

  public function printPermohonanUjiKlinikQrcode($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

    if ($item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik !== null) {
      $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengujian = '';
    }

    if ($item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik !== null) {
      $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_darah = '';
    }

    if ($item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_spesimen_urine = '';
    }

    $no_LHU = LHU::where('permohonan_uji_klinik_id', '=', $id_permohonan_uji_klinik)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // permintaan pertama
      // $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      // permintaan kedua
      $no_LHU->nomer_LHU = '449.5/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');

      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $get_nomor_lhu = $no_LHU->nomer_LHU;
      $explode_nomor_lhu = explode("/", $get_nomor_lhu);
      $get_pisah0_lhu = $explode_nomor_lhu[0];
      $get_pisah2_lhu = $explode_nomor_lhu[2];
      $get_pisah3_lhu = $explode_nomor_lhu[3];

      $no_LHU = $get_pisah0_lhu . '/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.22/' . $get_pisah2_lhu . '/' . $get_pisah3_lhu;
    }

    $data_permohonan_uji_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_permohonan_uji_parameter_klinik_pcr = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_permohonan_uji_parameter_klinik_antigen = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_permohonan_uji_parameter_klinik_antibody = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereHas('parametersatuanklinik', function ($query) {
        return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
          ->whereNull('deleted_at')
          ->orderBy('name_parameter_satuan_klinik', 'asc');
      })
      ->whereNull('deleted_at')
      ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();

    $text_redirect_barcode = route('scan.permohonan-uji-klinik', $item_permohonan_uji_klinik->id_permohonan_uji_klinik);

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-qrcode', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
      'data_permohonan_uji_parameter_klinik_pcr' => $data_permohonan_uji_parameter_klinik_pcr,
      'data_permohonan_uji_parameter_klinik_antigen' => $data_permohonan_uji_parameter_klinik_antigen,
      'data_permohonan_uji_parameter_klinik_antibody' => $data_permohonan_uji_parameter_klinik_antibody,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'text_redirect_barcode' => $text_redirect_barcode,
    ]);
    return $pdf->stream();

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.hasil-qrcode', [
  'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
  'data_permohonan_uji_parameter_klinik' => $data_permohonan_uji_parameter_klinik,
  'data_permohonan_uji_parameter_klinik_pcr' => $data_permohonan_uji_parameter_klinik_pcr,
  'data_permohonan_uji_parameter_klinik_antigen' => $data_permohonan_uji_parameter_klinik_antigen,
  'data_permohonan_uji_parameter_klinik_antibody' => $data_permohonan_uji_parameter_klinik_antibody,
  'tgl_pengujian' => $tgl_pengujian,
  'tgl_spesimen_darah' => $tgl_spesimen_darah,
  'tgl_spesimen_urine' => $tgl_spesimen_urine,
  'data_parameter_satuan' => $data_parameter_satuan,
  'no_LHU' => $no_LHU,
  'text_redirect_barcode' => $text_redirect_barcode
  ]); */
  }

  public function label(Request $request)
  {
    if (request()->ajax()) {
      $datas = PermohonanUjiPaketKlinik::latest()->get();

      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['nama_pasien']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['no_rekammedis_pasien']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['tgllahir_pasien']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['set_jenis_pemeriksaan']), Str::lower($request->get('search')))) {
                return true;
              }
              return false;
            });
          }
        })
        ->addColumn('set_checkbox', function ($data) {
          $set_checkbox = '<input type="checkbox" class="form-control checkbox-label" name="checkbox_label[' . $data->id_permohonan_uji_paket_klinik . ']" value="' . $data->id_permohonan_uji_paket_klinik . '">';
          return $set_checkbox;
        })
        ->addColumn('nama_pasien', function ($data) {
          $nama_pasien = '<div class="text-center">' . $data->permohonanujiklinik->pasien->nama_pasien . '</div>';
          return $nama_pasien;
        })
        ->addColumn('no_rekammedis_pasien', function ($data) {
          $no_rekammedis_pasien = '<div class="text-center">' . $data->permohonanujiklinik->pasien->no_rekammedis_pasien . '</div>';
          return $no_rekammedis_pasien;
        })
        ->addColumn('tgllahir_pasien', function ($data) {
          $tgllahir_pasien = Carbon::createFromFormat("Y-m-d", $data->permohonanujiklinik->pasien->tgllahir_pasien)->isoFormat("D MMMM YYYY");
          $tgllahir_pasien = '<div class="text-center">' . $tgllahir_pasien . '</div>';
          return $tgllahir_pasien;
        })
        ->addColumn('set_jenis_pemeriksaan', function ($data) {
          if ($data->type_permohonan_uji_paket_klinik == 'P') {
            $set_jenis_pemeriksaan = $data->parameterpaketklinik->name_parameter_paket_klinik;
          } else if ($data->type_permohonan_uji_paket_klinik == 'C') {
            $set_jenis_pemeriksaan = $data->parameterjenisklinik->name_parameter_jenis_klinik;
          }
          return $set_jenis_pemeriksaan;
        })
        ->addColumn('tgl_pemeriksaan', function ($data) {
          $tgl_pemeriksaan = Carbon::createFromFormat("Y-m-d H:i:s", $data->created_at)->format("d-m-Y, H:i:s");
          $tgl_pemeriksaan = '<div class="text-center">' . $tgl_pemeriksaan . '</div>';
          return $tgl_pemeriksaan;
        })
        ->rawColumns(['set_checkbox', 'nama_pasien', 'no_rekammedis_pasien', 'tgllahir_pasien', 'set_jenis_pemeriksaan', 'tgl_pemeriksaan'])
        ->addIndexColumn() //increment
        ->make(true);
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.index');
  }

  public function printLabel(Request $request)
  {
    $id_pengujian_uji_klinik_string = $request->pengujian_uji_klinik;
    $id_pengujian_uji_klinik_arr = explode(",", $id_pengujian_uji_klinik_string);

    $i = 0;
    foreach ($id_pengujian_uji_klinik_arr as $id_pengujian_uji_klinik) {
      $get_data[$i] = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id_pengujian_uji_klinik)->first();
      $i++;
    }

    if (count($get_data) > 1) {
      for ($i = 0; $i < count($get_data); $i++) {
        $get_data[$i]->permohonanujiklinik->pasien->tgllahir_pasien = Carbon::createFromFormat("Y-m-d", $get_data[$i]->permohonanujiklinik->pasien->tgllahir_pasien)->format("d-m-Y");
        $full_name = explode(" ", $get_data[$i]->permohonanujiklinik->pasien->nama_pasien);
        if (implode(" ", array_splice($full_name, 3)) != null) {
          $get_data[$i]->permohonanujiklinik->pasien->nama_pasien = implode(" ", array_splice($full_name, 0, 3)) . ' ...';
        } else {
          $get_data[$i]->permohonanujiklinik->pasien->nama_pasien = implode(" ", array_splice($full_name, 0, 3));
        }
        if ($get_data[$i]->type_permohonan_uji_paket_klinik == 'P') {
          $get_data[$i]->jenis_pemeriksaan = $get_data[$i]->parameterpaketklinik->name_parameter_paket_klinik;
        } else if ($get_data[$i]->type_permohonan_uji_paket_klinik == 'C') {
          $get_data[$i]->jenis_pemeriksaan = $get_data[$i]->parameterjenisklinik->name_parameter_jenis_klinik;
        }
      }
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label', compact('get_data'));

    // $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label', compact('get_data'));
    // return $pdf->stream();
  }
}