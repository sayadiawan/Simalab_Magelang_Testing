<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\LHU;
use Endroid\QrCode\Color\Color;
use Smt\Masterweb\Rules\Captcha;
use \Smt\Masterweb\Models\Sample;

use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\BakuMutu;
use App\Http\Controllers\Controller;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\VerifikasiHasil;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use Smt\Masterweb\Models\PermohonanUjiKlinik;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;


class ScanController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->join('ms_customer', function ($join) {
      $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
        ->whereNull('ms_customer.deleted_at')
        ->whereNull('tb_permohonan_uji.deleted_at');
    })->first();

    if ($permohonan_uji != null) {
      $samples = Sample::where('permohonan_uji_id', '=', $id)
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
        ->select('ms_laboratorium.id_laboratorium', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*', 'tb_sample_penerimaan.*', 'tb_sample_penanganan.*')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_sample_penerimaan.sample_id')
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pelaporan_hasil', function ($join) {
          $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
            ->whereNull('tb_pelaporan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengetikan_hasil', function ($join) {
          $join->on('tb_pengetikan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_pengetikan_hasil.sample_id')
            ->whereNull('tb_pengetikan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'tb_pengetikan_hasil.*', 'tb_pelaporan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
        ->distinct('id_laboratorium')
        ->get();
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
      return $pdf->stream();
    } else {
      return abort(404);
    }
  }


  public function printLHU_all($id)
  {
    //    dd("ygygyu");

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->join('ms_customer', function ($join) {
      $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
        ->whereNull('ms_customer.deleted_at')
        ->whereNull('tb_permohonan_uji.deleted_at');
    })->first();

    if ($permohonan_uji != null) {
      $samples = Sample::where('permohonan_uji_id', '=', $id)
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
        ->select('ms_laboratorium.id_laboratorium', 'ms_laboratorium.nama_laboratorium', 'tb_samples.*', 'ms_sample_type.*', 'tb_sample_penerimaan.*', 'tb_sample_penanganan.*')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_sample_penerimaan.sample_id')
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pelaporan_hasil', function ($join) {
          $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
            ->whereNull('tb_pelaporan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengetikan_hasil', function ($join) {
          $join->on('tb_pengetikan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_pengetikan_hasil.sample_id')
            ->whereNull('tb_pengetikan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_verifikasi_hasil', function ($join) {
          $join->on('tb_verifikasi_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
            ->on('tb_samples.id_samples', '=', 'tb_verifikasi_hasil.sample_id')
            ->whereNull('tb_verifikasi_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'tb_verifikasi_hasil.*', 'tb_pengetikan_hasil.*', 'tb_sample_penerimaan.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
        ->distinct('id_laboratorium')
        ->get();

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
      return $pdf->stream();
    } else {
      return abort(404);
    }
  }



  public function verification($id)
  {

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->join('ms_customer', function ($join) {
      $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
        ->whereNull('ms_customer.deleted_at')
        ->whereNull('tb_permohonan_uji.deleted_at');
    })->first();

    if ($permohonan_uji != null) {
      $samples = Sample::where('permohonan_uji_id', '=', $id)
        ->get();

      // dd($samples);

      return view('masterweb::module.admin.laboratorium.scan.verification', compact('permohonan_uji', 'samples'));
    } else {
      return abort(404);
    }
  }


  public function printLHU($id, $idlab)
  {
    $sample = Sample::where('id_samples', '=', $id)->join('tb_permohonan_uji', function ($join) {
      $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
        ->whereNull('tb_permohonan_uji.deleted_at')
        ->whereNull('tb_samples.deleted_at');
    })->join('ms_customer', function ($join) {
      $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
        ->whereNull('ms_customer.deleted_at')
        ->whereNull('tb_permohonan_uji.deleted_at');
    })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })


      ->first();

    $no_LHU = LHU::where('sample_id', '=', $id)->where('lab_id', '=', $idlab)->first();

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');


      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));
      $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');
      $no_LHU->sample_id = $id;
      $no_LHU->lab_id = $idlab;
      $no_LHU->save();
    } else {
      $no_LHU = $no_LHU->nomer_LHU;
    }

    // dd($sample);

    $verifikasi = VerifikasiHasil::where('sample_id', '=', $id)->where('laboratorium_id', '=', $idlab)->first();
    $laboratorium = Laboratorium::findOrFail($idlab);



    $sampletype_id = $sample->id_sample_type;

    $laboratoriummethods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
      ->where('tb_sample_result.sample_id', '=', $id)
      ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->leftjoin('tb_sample_result', function ($join) use ($id) {
        $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
          ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->join('ms_unit as unit_baku_mutu', function ($join) {
        $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
          ->whereNull('unit_baku_mutu.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->distinct('tb_laboratorium_method.laboratorium_id')
      ->select('tb_baku_mutu.*', 'ms_method.*', 'tb_laboratorium_method.*', 'unit_baku_mutu.shortname_unit as shortname_unit', 'tb_sample_result.hasil')
      ->get();

    $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
      ->where('tb_sample_result.sample_id', '=', $id)
      ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->leftjoin('tb_sample_result', function ($join) use ($id) {
        $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
          ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })

      ->join('ms_library', function ($join) use ($sampletype_id) {
        $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
          ->whereNull('ms_library.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->distinct('tb_laboratorium_method.laboratorium_id')
      ->select('ms_library.*')
      ->get();





    // if($sample==null||$laboratorium==null ||$laboratoriummethods==null){
    //     return abort(404);
    // }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.scan.printLHU', compact('no_LHU', 'sample', 'verifikasi', 'laboratorium', 'laboratoriummethods', 'all_acuan_baku_mutu'));

    // $pdf = PDF::loadView('masterweb::module.admin.laboratorium.scan.printLHU',compact('sample','verifikasi','laboratorium','laboratoriummethods'));
    return $pdf->stream();
  }

  public function makeQrCodePermohonanUjiKlinik($text, $size = 100, $margin = 0)
  {
    $writer = new PngWriter();

    // Create QR code
    $qrCode = QrCode::create(route('scan.permohonan-uji-klinik', [$text]))
      ->setEncoding(new Encoding('UTF-8'))
      ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
      ->setSize($size)
      ->setMargin($margin)
      ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
      ->setForegroundColor(new Color(0, 0, 0))
      ->setBackgroundColor(new Color(255, 255, 255));

    $result = $writer->write($qrCode);
    header('Content-Type: ' . $result->getMimeType());
    return $result->getString();
  }

  public function scanPermohonanUjiKlinik($id_permohonan_uji_klinik)
  {
    $item_permohonan_uji_klinik = PermohonanUjiKlinik::findOrFail($id_permohonan_uji_klinik);

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

    if ($item_permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik !== null) {
      $tgl_pengambilan = Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglpengambilan_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
    } else {
      $tgl_pengambilan = '';
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
      $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');
      $no_LHU->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
      $no_LHU->save();

      $no_LHU = $no_LHU->nomer_LHU;
    } else {
      $no_LHU = $no_LHU->nomer_LHU;
    }

    $data_parameter_satuan = ParameterSatuanKlinik::whereNull('deleted_at')->orderBy('name_parameter_satuan_klinik', 'asc')->get();
    $data_parameter_jenis = ParameterJenisKlinik::whereNull('deleted_at')->orderBy('name_parameter_jenis_klinik', 'asc')->get();

    // keluarin paket yang dibeli
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

    // keluarin semua data yang di analis
    $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->whereNull('deleted_at')
      ->get();

    $arr_permohonan_parameter_klinik = [];

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
            return $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
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

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter_klinik, $item_permohonan_parameter_paket);
      }
    }

    $arr_permohonan_parameter_klinik_pcr = [];

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

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter_klinik_pcr, $item_permohonan_parameter_paket);
      }
    }

    $arr_permohonan_parameter_klinik_antibody = [];

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

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter_klinik_antibody, $item_permohonan_parameter_paket);
      }
    }

    $arr_permohonan_parameter_klinik_antigen = [];

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

                array_push($item_permohonan_parameter_satuan['data_permohonan_uji_subsatuan_klinik'], $item_permohonan_parameter_subsatuan);
              }
            }

            array_push($item_permohonan_parameter_paket['data_permohonan_uji_satuan_klinik'], $item_permohonan_parameter_satuan);
          }
        }

        array_push($arr_permohonan_parameter_klinik_antigen, $item_permohonan_parameter_paket);
      }
    }

    return view('masterweb::module.admin.laboratorium.scan.permohonan-uji-klinik', [
      'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
      'arr_permohonan_parameter_klinik' => $arr_permohonan_parameter_klinik,
      'arr_permohonan_parameter_klinik_pcr' => $arr_permohonan_parameter_klinik_pcr,
      'arr_permohonan_parameter_klinik_antigen' => $arr_permohonan_parameter_klinik_antigen,
      'arr_permohonan_parameter_klinik_antibody' => $arr_permohonan_parameter_klinik_antibody,
      'tgl_pengujian' => $tgl_pengujian,
      'tgl_spesimen_darah' => $tgl_spesimen_darah,
      'tgl_spesimen_urine' => $tgl_spesimen_urine,
      'data_parameter_jenis' => $data_parameter_jenis,
      'data_parameter_satuan' => $data_parameter_satuan,
      'no_LHU' => $no_LHU,
      'value_items' => $value_items,
      'tgl_pengambilan' => $tgl_pengambilan
    ]);
  }
}