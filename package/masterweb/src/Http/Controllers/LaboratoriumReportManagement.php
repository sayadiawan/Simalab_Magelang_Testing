<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\Program;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\SampleMethod;
use App\Exports\LaboratoriumReportDaily;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\LaboratoriumReportAnually;
use App\Exports\LaboratoriumReportMonthly;
use App\Exports\LaboratoriumReportDateVerificationMonthly;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Helpers\KesmasNotaHelper;

class LaboratoriumReportManagement extends Controller
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
    //get auth user
    //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
    //get auth user
    // $user = Auth()->user();
    // $samples = Sample::orderBy('created_at')->get();



    // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));

    return view('masterweb::module.admin.laboratorium.permohonan-uji.pagination');
  }

  public function report_daily()
  {
    return view('masterweb::module.admin.laboratorium.report.report-daily.list');
  }

  public function data_report_daily(Request $request)
  {
    $laboratoriumId = $request->laboratorium;
    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';

    $showNonKlinik = !$laboratoriumId || !$isKlinikLabFilter;
    $showKlinik = !$laboratoriumId || $isKlinikLabFilter;

    $nonKlinikRows = collect();
    if ($showNonKlinik) {
      $query = Sample::join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->leftJoin('ms_customer', function ($join) {
            $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
              ->whereNull('tb_permohonan_uji.deleted_at')
              ->whereNull('ms_customer.deleted_at');
          });
      })
        ->join('tb_sample_method', function ($join) use ($request) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');

          if ($request->laboratorium) {
            $join->where('tb_sample_method.laboratorium_id', $request->laboratorium);
          }
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('ms_packet', function ($join) {
          $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
            ->whereNull('ms_packet.deleted_at');
        });

      $query->whereDate('date_permohonan_uji', $request->tanggal);

      if ($request->sampletype) {
        $query->where('tb_samples.typesample_samples', $request->sampletype);
      }

      $query->select(
        'tb_sample_penanganan.id_sample_penanganan as id_sample_penanganan',
        'tb_sample_penanganan.sample_id as penanganan_sample_id',
        'tb_sample_penanganan.penanganan_sample_date as penanganan_sample_date',
        'tb_sample_penerimaan.id_sample_penerimaan as id_sample_penerimaan',
        'tb_sample_penerimaan.sample_id as penerimaan_sample_id',
        'date_sending',
        'date_analitik_sample',
        'name_sample_type',
        'tb_samples.id_samples as id_samples',
        'tb_samples.codesample_samples as codesample_samples',
        'tb_samples.permohonan_uji_id as permohonan_uji_id',
        'tb_samples.cost_samples as cost_samples',
        'tb_samples.packet_id as packet_id',
        'ms_packet.price_total_packet as price_total_packet',
        'ms_customer.id_customer as id_customer',
        'ms_customer.name_customer as name_customer',
        'tb_permohonan_uji.id_permohonan_uji as id_permohonan_uji',
        'tb_permohonan_uji.code_permohonan_uji as code_permohonan_uji',
        'tb_permohonan_uji.date_permohonan_uji as date_permohonan_uji',
        'tb_permohonan_uji.customer_id as customer_id',
        'tb_permohonan_uji.total_harga as total_harga',
        'tb_permohonan_uji.created_at as created_at'
      )->distinct('id_laboratorium');

      $nonKlinikRows = $query->get();
    }

    $klinikRows = collect();
    if ($showKlinik && !$request->sampletype) {
      $klinikRows = PermohonanUjiKlinik2::query()
        ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
        ->join('ms_pasien', function ($join) {
          $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
            ->whereNull('ms_pasien.deleted_at');
        })
        ->whereDate('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $request->tanggal)
        ->select(
          'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tgl_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jam_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jenis_spesimen',
          'tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik',
          'ms_pasien.nama_pasien as name_customer',
        )
        ->get()
        ->map(function ($row) {
          $obj = new \stdClass;
          $tanggalUji = $row->tglpengujian_permohonan_uji_klinik;
          if (!$tanggalUji && $row->tgl_sampling_permohonan_uji_klinik) {
            $tanggalUji = trim($row->tgl_sampling_permohonan_uji_klinik . ' ' . ($row->jam_sampling_permohonan_uji_klinik ?? ''));
          }
          $obj->penanganan_sample_date = $tanggalUji ?: $row->tglregister_permohonan_uji_klinik;
          $obj->name_customer = $row->name_customer;
          $code = $row->noregister_permohonan_uji_klinik ?? '-';
          $jenis = $this->formatJenisSpesimenLabel($row->jenis_spesimen);
          $obj->codesample_samples = $jenis !== ''
            ? ($code . ' — ' . $jenis)
            : $code;
          $obj->cost_samples = $row->total_harga_permohonan_uji_klinik;
          return $obj;
        });
    }

    $datas = $nonKlinikRows->values()->concat($klinikRows->values());

    return DataTables::of($datas)
      ->filter(function ($instance) use ($request) {
        $needle = $request->input('search.value', $request->get('search'));
        if (is_array($needle)) {
          $needle = $needle['value'] ?? '';
        }
        if ($needle === null || $needle === '') {
          return;
        }
        $needle = Str::lower((string) $needle);
        $instance->collection = $instance->collection->filter(function ($row) use ($needle) {
          $candidates = [
            Str::lower((string) data_get($row, 'name_customer')),
            Str::lower((string) data_get($row, 'codesample_samples')),
            Str::lower((string) data_get($row, 'penanganan_sample_date')),
          ];
          foreach ($candidates as $haystack) {
            if ($haystack !== '' && Str::contains($haystack, $needle)) {
              return true;
            }
          }
          return false;
        });
      })
      ->addColumn('tgl_pemeriksaan', function ($data) {
        $raw = data_get($data, 'penanganan_sample_date');
        if (empty($raw)) {
          return '';
        }
        try {
          return Carbon::parse($raw)->isoFormat('D MMMM Y HH:mm');
        } catch (\Throwable $e) {
          return '';
        }
      })
      ->addColumn('customer', function ($data) {
        return $data->name_customer ?? '-';
      })
      ->addColumn('harga_per_sample', function ($data) {
        $sampleId = data_get($data, 'id_samples');
        $permohonanId = data_get($data, 'permohonan_uji_id');
        if ($sampleId && $permohonanId) {
          return rupiah(KesmasNotaHelper::amountForSample($sampleId, $permohonanId));
        }

        return rupiah((float) ($data->cost_samples ?? 0));
      })
      ->rawColumns(['tgl_pemeriksaan', 'customer', 'harga_per_sample'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  /**
   * Total harga permohonan klinik pada tanggal register; sama syaratnya dengan baris klinik di laporan harian.
   */
  private function sumKlinikTotalHargaPermohonanHarian(Request $request): float
  {
    $sampletype = $request->sampletype;
    if ($sampletype === null || $sampletype === '' || (string) $sampletype === 'null') {
      $sampletype = null;
    }
    if ($sampletype !== null) {
      return 0.0;
    }

    $laboratoriumId = $request->laboratorium;
    if ($laboratoriumId === null || $laboratoriumId === '' || (string) $laboratoriumId === 'null') {
      $laboratoriumId = null;
    }

    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';
    if ($laboratoriumId && !$isKlinikLabFilter) {
      return 0.0;
    }

    $tanggal = $request->tanggal;
    if ($tanggal === null || $tanggal === '') {
      return 0.0;
    }

    $sum = PermohonanUjiKlinik2::query()
      ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
      ->join('ms_pasien', function ($join) {
        $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
          ->whereNull('ms_pasien.deleted_at');
      })
      ->whereDate('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $tanggal)
      ->sum('tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik');

    return (float) $sum;
  }

  public function getTotalHargaSampleDaily(Request $request)
  {
    $samples = 0.0;

    if ($request->tanggal) {
      $samples = $this->sumNonKlinikCostSamplesOnce($request, function ($query) use ($request) {
        $query->whereDate('tb_permohonan_uji.date_permohonan_uji', $request->tanggal);
      });
    }

    $samples = $samples + $this->sumKlinikTotalHargaPermohonanHarian($request);

    return response()->json(rupiah($samples));
  }

  public function printReportDaily(Request $request)
  {
    $tanggal  = request()->query('tanggal');
    $program = request()->query('program');


    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereDate('date_penanganan_sample', $tanggal)
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')->orderBy('name_method', 'asc');
      })
        ->get();

      if (count($data_sample_method->where('sample')) > 0) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-daily.formatPrint.report-daily-maatweb', [
          'data_sample' => $data_sample,
          'tanggal' => $tanggal,
          'item_program' => $item_program,
          'data_sample_method' => $data_sample_method
        ]);

        return $pdf->stream();
      } else {
        return redirect('/report-daily')->with(['warning' => 'Tidak ada laporan pada tanggal yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }


  public function printReportDaily_to_maatweb(Request $request)
  {
    $tanggal  = request()->query('tanggal');
    $program = request()->query('program');
    $sampletype = request()->query('sampletype');
    $laboratorium = request()->query('laboratorium');
    $judul = "laporan_harian_" . $tanggal . ".xlsx";

    if ($program != "null" && $program != null && isset($program)) {
      $item_program = Program::find($program);
      $clean_judul = preg_replace("/\//", "", $item_program->name_program);
      $judul = 'laporan_harian_' . Str::slug($clean_judul) . '_' . $tanggal . '.xlsx';
    } else {
      $program = null;
    }

    if ($sampletype != "null" && $sampletype != null && isset($sampletype)) {
      $item_sampletype = SampleType::find($sampletype);

      $clean_judul = preg_replace("/\//", "", $item_sampletype->name_sample_type);
      $judul = 'laporan_harian_' . Str::slug($clean_judul) . '_' . $tanggal . '.xlsx';
    } else {
      $sampletype = null;
    }

    if ($laboratorium != "null" && $laboratorium != null && isset($laboratorium)) {
      $item_laboratorium = Laboratorium::find($laboratorium);

      $clean_judul = preg_replace("/\//", "", $item_laboratorium->name_sample_type);
      $judul = 'laporan_harian_' . Str::slug($clean_judul) . '_' . $tanggal . '.xlsx';
    } else {
      $laboratorium = null;
    }

    return Excel::download(new LaboratoriumReportDaily($tanggal, $program, $sampletype, $laboratorium), $judul);
  }

  public function daily($date_from = null, $date_to = null)
  {

    $laboratoriummethods = SampleMethod::orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })

      ->select('ms_method.id_method', 'ms_method.params_method', DB::raw("count(ms_method.id_method) as count"), DB::raw('DATE_FORMAT(tb_sample_method.created_at, "%b-%Y") as date'))
      ->groupBy('ms_method.id_method', 'ms_method.params_method', DB::raw('DATE_FORMAT(tb_sample_method.created_at, "%b-%Y")'))
      ->whereMonth('tb_sample_method.created_at', '=', 9)
      ->whereYear('tb_sample_method.created_at', '=', 2021)
      // ->distinct('date')
      ->get();

    // ->join('assigned_tags', 'website_tags.id', '=', 'assigned_tags.tag_id')
    // ->select('website_tags.id as id', 'website_tags.title as title', DB::raw("count(assigned_tags.tag_id) as count"))
    // ->groupBy('website_tags.id')
    // ->get();
    //    dd("ygygyu");

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
      ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
      ->distinct('id_laboratorium')
      ->get();

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
    return $pdf->stream();
  }

  public function report_weekly()
  {
    return view('masterweb::module.admin.laboratorium.report.report-weekly.list');
  }

  public function data_report_weekly(Request $request)
  {
    $datas = Sample::whereHas('pengesahanhasil', function ($query) {
      return $query->whereNull('sample_id');
    })->join('tb_permohonan_uji', function ($join) {
      $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
        ->whereNull('tb_permohonan_uji.deleted_at')
        ->whereNull('tb_samples.deleted_at')
        ->leftjoin('ms_customer', function ($join) {
          $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->whereNull('ms_customer.deleted_at');
        });
    })
      ->orderBy('created_at');

    return Datatables::of($datas)
      ->addColumn('tgl_pemeriksaan', function ($data) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_penanganan_sample)->isoFormat('D MMMM Y HH:mm');
      })
      ->addColumn('customer', function ($data) {
        return $data instanceof Sample ? $data->namaPelangganDisplay() : (optional(optional($data->permohonanuji)->customer)->name_customer ?? '-');
      })
      ->filter(function ($query) {
        if (request()->has('from_tanggal') && request()->has('to_tanggal')) {
          $from_tanggal  = request()->has('from_tanggal') . "00:00:00";
          $to_tanggal  = request()->has('to_tanggal') . "23:59:59";

          $query->whereBetween('date_permohonan_uji', [$from_tanggal, $to_tanggal]);
        }

        if (request()->has('program')) {
          $query->where('program_samples', request()->has('program'));
        }
      })
      ->rawColumns(['tgl_pemeriksaan'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  public function printReportWeekly(Request $request)
  {
    $from_tanggal  = request()->query('from_tanggal') . "00:00:00";
    $to_tanggal  = request()->query('to_tanggal') . "23:59:59";
    $program = request()->query('program');

    if (isset($from_tanggal) && (isset($program) || $program !== null)) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereBetween('date_penanganan_sample', [$from_tanggal, $to_tanggal])
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')->orderBy('name_method', 'asc');
      })
        ->get();

      if (count($data_sample_method->where('sample')) > 0) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-weekly.formatPrint.report-weekly', [
          'data_sample' => $data_sample,
          'from_tanggal' => $from_tanggal,
          'to_tanggal' => $to_tanggal,
          'item_program' => $item_program,
          'data_sample_method' => $data_sample_method
        ]);

        return $pdf->stream();
      } else {
        return redirect('/report-weekly')->with(['warning' => 'Tidak ada laporan pada minggu yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }

  public function weekly($date_from = null, $date_to = null)
  {

    $laboratoriummethods = SampleMethod::orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('ms_method.params_method as id', DB::raw("count(ms_method.id_method) as count"))
      ->groupBy('ms_method.id_method')
      ->get();

    // ->join('assigned_tags', 'website_tags.id', '=', 'assigned_tags.tag_id')
    // ->select('website_tags.id as id', 'website_tags.title as title', DB::raw("count(assigned_tags.tag_id) as count"))
    // ->groupBy('website_tags.id')
    // ->get();
    //    dd("ygygyu");

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
      ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
      ->distinct('id_laboratorium')
      ->get();

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
    return $pdf->stream();
  }

  public function report_monthly()
  {
    return view('masterweb::module.admin.laboratorium.report.report-monthly.list');
  }

  public function data_report_monthly(Request $request)
  {
    $month = (int) $request->month;
    $year = (int) $request->year;
    $laboratoriumId = $request->laboratorium;
    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';

    $showNonKlinik = !$laboratoriumId || !$isKlinikLabFilter;
    $showKlinik = !$laboratoriumId || $isKlinikLabFilter;

    $nonKlinikRows = collect();
    if ($showNonKlinik && $month && $year) {
      $query = Sample::join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->leftJoin('ms_customer', function ($join) {
            $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
              ->whereNull('tb_permohonan_uji.deleted_at')
              ->whereNull('ms_customer.deleted_at');
          });
      })
        ->join('tb_sample_method', function ($join) use ($request) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');

          if ($request->laboratorium) {
            $join->where('tb_sample_method.laboratorium_id', $request->laboratorium);
          }
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('ms_packet', function ($join) {
          $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
            ->whereNull('ms_packet.deleted_at');
        })
        ->whereMonth('date_permohonan_uji', $month)
        ->whereYear('date_permohonan_uji', $year);

      if ($request->sampletype) {
        $query->where('tb_samples.typesample_samples', $request->sampletype);
      }

      $query->select(
        'tb_sample_penanganan.id_sample_penanganan as id_sample_penanganan',
        'tb_sample_penanganan.sample_id as penanganan_sample_id',
        'tb_sample_penanganan.penanganan_sample_date as penanganan_sample_date',
        'tb_sample_penerimaan.id_sample_penerimaan as id_sample_penerimaan',
        'tb_sample_penerimaan.sample_id as penerimaan_sample_id',
        'date_sending',
        'date_analitik_sample',
        'name_sample_type',
        'tb_samples.id_samples as id_samples',
        'tb_samples.codesample_samples as codesample_samples',
        'tb_samples.permohonan_uji_id as permohonan_uji_id',
        'tb_samples.cost_samples as cost_samples',
        'tb_samples.packet_id as packet_id',
        'ms_packet.price_total_packet as price_total_packet',
        'ms_customer.id_customer as id_customer',
        'ms_customer.name_customer as name_customer',
        'tb_permohonan_uji.id_permohonan_uji as id_permohonan_uji',
        'tb_permohonan_uji.code_permohonan_uji as code_permohonan_uji',
        'tb_permohonan_uji.date_permohonan_uji as date_permohonan_uji',
        'tb_permohonan_uji.customer_id as customer_id',
        'tb_permohonan_uji.total_harga as total_harga',
        'tb_permohonan_uji.created_at as created_at'
      )->distinct('id_laboratorium');

      $nonKlinikRows = $query->get();
    }

    $klinikRows = collect();
    if ($showKlinik && !$request->sampletype && $month && $year) {
      $klinikRows = PermohonanUjiKlinik2::query()
        ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
        ->join('ms_pasien', function ($join) {
          $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
            ->whereNull('ms_pasien.deleted_at');
        })
        ->whereMonth('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $month)
        ->whereYear('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $year)
        ->select(
          'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tgl_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jam_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jenis_spesimen',
          'tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik',
          'ms_pasien.nama_pasien as name_customer',
        )
        ->get()
        ->map(function ($row) {
          $obj = new \stdClass;
          $tanggalUji = $row->tglpengujian_permohonan_uji_klinik;
          if (!$tanggalUji && $row->tgl_sampling_permohonan_uji_klinik) {
            $tanggalUji = trim($row->tgl_sampling_permohonan_uji_klinik . ' ' . ($row->jam_sampling_permohonan_uji_klinik ?? ''));
          }
          $obj->penanganan_sample_date = $tanggalUji ?: $row->tglregister_permohonan_uji_klinik;
          $obj->name_customer = $row->name_customer;
          $code = $row->noregister_permohonan_uji_klinik ?? '-';
          $jenis = $this->formatJenisSpesimenLabel($row->jenis_spesimen);
          $obj->codesample_samples = $jenis !== ''
            ? ($code . ' — ' . $jenis)
            : $code;
          $obj->cost_samples = $row->total_harga_permohonan_uji_klinik;
          return $obj;
        });
    }

    $datas = $nonKlinikRows->values()->concat($klinikRows->values());

    return DataTables::of($datas)
      ->filter(function ($instance) use ($request) {
        $needle = $request->input('search.value', $request->get('search'));
        if (is_array($needle)) {
          $needle = $needle['value'] ?? '';
        }
        if ($needle === null || $needle === '') {
          return;
        }
        $needle = Str::lower((string) $needle);
        $instance->collection = $instance->collection->filter(function ($row) use ($needle) {
          $candidates = [
            Str::lower((string) data_get($row, 'name_customer')),
            Str::lower((string) data_get($row, 'codesample_samples')),
            Str::lower((string) data_get($row, 'penanganan_sample_date')),
          ];
          foreach ($candidates as $haystack) {
            if ($haystack !== '' && Str::contains($haystack, $needle)) {
              return true;
            }
          }
          return false;
        });
      })
      ->addColumn('tgl_pemeriksaan', function ($data) {
        $raw = data_get($data, 'penanganan_sample_date');
        if (empty($raw)) {
          return '';
        }
        try {
          return Carbon::parse($raw)->isoFormat('D MMMM Y HH:mm');
        } catch (\Throwable $e) {
          return '';
        }
      })
      ->addColumn('customer', function ($data) {
        return $data->name_customer ?? '-';
      })
      ->addColumn('harga_per_sample', function ($data) {
        $sampleId = data_get($data, 'id_samples');
        $permohonanId = data_get($data, 'permohonan_uji_id');
        if ($sampleId && $permohonanId) {
          return rupiah(KesmasNotaHelper::amountForSample($sampleId, $permohonanId));
        }

        return rupiah((float) ($data->cost_samples ?? 0));
      })
      ->rawColumns(['tgl_pemeriksaan', 'customer', 'harga_per_sample'])
      ->addIndexColumn()
      ->make(true);
  }

  /**
   * Total harga permohonan klinik pada bulan register; sama syaratnya dengan baris klinik di laporan bulanan.
   */
  private function sumKlinikTotalHargaPermohonanBulanan(Request $request): float
  {
    $sampletype = $request->sampletype;
    if ($sampletype !== null && $sampletype !== '' && (string) $sampletype !== 'null') {
      return 0.0;
    }

    $laboratoriumId = $request->laboratorium;
    if ($laboratoriumId === null || $laboratoriumId === '' || (string) $laboratoriumId === 'null') {
      $laboratoriumId = null;
    }

    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';
    if ($laboratoriumId && !$isKlinikLabFilter) {
      return 0.0;
    }

    $month = (int) $request->month;
    $year = (int) $request->year;
    if (!$month || !$year) {
      return 0.0;
    }

    return (float) PermohonanUjiKlinik2::query()
      ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
      ->whereMonth('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $month)
      ->whereYear('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $year)
      ->sum('tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik');
  }

  public function getTotalHargaSampleMonthly(Request $request)
  {
    $month = (int) $request->month;
    $year = (int) $request->year;
    $samples = 0.0;

    if ($month && $year) {
      $samples = $this->sumNonKlinikCostSamplesOnce($request, function ($query) use ($month, $year) {
        $query->whereMonth('tb_permohonan_uji.date_permohonan_uji', $month)
          ->whereYear('tb_permohonan_uji.date_permohonan_uji', $year);
      });
    }

    $samples = $samples + $this->sumKlinikTotalHargaPermohonanBulanan($request);

    return response()->json(rupiah($samples));
  }

  public function printReportMonthly(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');

    // dd($bulan . ' ' . $tahun . ' ' . $program);

    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          // return $query->whereNull('deleted_at')->whereNull('sample_id');
          return $query->whereNull('deleted_at')->whereNull('sample_id');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->orderBy('date_penanganan_sample', 'asc')
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
        ->get();

      // dd($data_sample);

      // PRINT ALL METHOD TO HORIZONTAL
      $method_all = Sample::orderBy('date_penanganan_sample', 'asc')
        ->where('program_samples', $program)
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
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
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*')
        ->distinct('ms_method.id_method')
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')
          ->orderBy('name_method', 'asc');
      })
        ->whereHas('sample', function ($query) use ($program, $bulan, $tahun) {
          return $query
            ->where('program_samples', $program)
            ->whereMonth('date_penanganan_sample', $bulan)
            ->whereYear('date_penanganan_sample', $tahun)
            ->orderBy('date_penanganan_sample', 'asc');
        })
        // ->whereHas('sample.pengesahanhasil', function ($query) {
        //     return $query->whereNull('sample_id');
        // })
        ->get();

      // dd($method_all);

      // dd(is_object($data_sample));

      if (!empty($data_sample) && $data_sample !== null) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-monthly.formatPrint.report-monthly', [
          'data_sample' => $data_sample,
          'bulan' => $bulan,
          'tahun' => $tahun,
          'item_program' => $item_program,
          'method_all' => $method_all,
          'data_sample_method' => $data_sample_method
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
      } else {
        return redirect('/report-monthly')->with(['warning' => 'Tidak ada laporan pada bulan yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }



  public function printReportMonthly_to_excel(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');

    // dd($bulan . ' ' . $tahun . ' ' . $program);

    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          // return $query->whereNull('deleted_at')->whereNull('sample_id');
          return $query->whereNull('deleted_at')->whereNull('sample_id');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->orderBy('date_penanganan_sample', 'asc')
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
        ->get();

      // dd($data_sample);

      // PRINT ALL METHOD TO HORIZONTAL
      $method_all = Sample::orderBy('date_penanganan_sample', 'asc')
        ->where('program_samples', $program)
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
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
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*')
        ->distinct('ms_method.id_method')
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')
          ->orderBy('name_method', 'asc');
      })
        ->whereHas('sample', function ($query) use ($program, $bulan, $tahun) {
          return $query
            ->where('program_samples', $program)
            ->whereMonth('date_penanganan_sample', $bulan)
            ->whereYear('date_penanganan_sample', $tahun)
            ->orderBy('date_penanganan_sample', 'asc');
        })
        // ->whereHas('sample.pengesahanhasil', function ($query) {
        //     return $query->whereNull('sample_id');
        // })
        ->get();

      // dd($method_all);

      // dd(is_object($data_sample));

      if (!empty($data_sample) && $data_sample !== null) {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A1", 'PEMERIKSAAN ' . strtoupper($item_program->name_program));
        $sheet->mergeCells("A2:F2");
        $sheet->setCellValue("A2", 'BULAN ' . fbulan($bulan) . ' ' . $tahun);

        $sheet->mergeCells("A4:A5");
        $sheet->setCellValue('A4', 'NO');
        $sheet->mergeCells("B4:B5");
        $sheet->setCellValue('B4', 'TGL. PEMERIKSAAN');
        $sheet->mergeCells("C4:C5");
        $sheet->setCellValue('C4', 'NAMA SARANA');
        $sheet->mergeCells("D4:D5");
        $sheet->setCellValue('D4', 'TITIK/LOKASI');
        $sheet->mergeCells("E4:E5");
        $sheet->setCellValue('E4', 'JENIS SAMPEL');
        $sheet->mergeCells($this->getColRange('F', 4, count($method_all)));
        $sheet->setCellValue('F4', 'HASIL PEMERIKSAAN');

        foreach ($method_all as $key => $value) {
          $sheet->setCellValue('F5', $value->name_method);
        }

        $no = 1;

        foreach ($data_sample as $key_ds => $item_ds) {
          $no++;

          $sheet->setCellValue('A' . $no, $no);
          $sheet->setCellValue('B' . $no, Carbon::createFromFormat('Y-m-d H:i:s', $item_ds->date_penanganan_sample)->isoFormat('D MMMM Y HH:mm'));
          $sheet->setCellValue('C' . $no, $item_ds->codesample_samples ?? '-');
          $sheet->setCellValue('D' . $no, $item_ds->location_samples ?? '-');
          $sheet->setCellValue('E' . $no, $item_ds->sampletype->name_sample_type);


          $sheet->setCellValue('F' . $no, $no);
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Laporan_Bulanan_' . $item_program->name_program . '_' . date('Y-m-d') . '.xlsx"');

        $writer->save('php://output');
      } else {
        return redirect('/report-monthly')->with(['warning' => 'Tidak ada laporan pada bulan yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }



  public function printReportMonthly_to_maatweb(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');
    $sampletype = request()->query('sampletype');
    $laboratorium = request()->query('laboratorium');

    if ($laboratorium && $laboratorium !== 'null') {
      $lab = Laboratorium::find($laboratorium);
      if ($lab && $lab->kode_laboratorium === 'KLI' && $tahun) {
        $params = [
          'year' => (int) $tahun,
          'laboratorium_id' => $laboratorium,
        ];
        if ($bulan && $bulan !== 'null') {
          $params['month'] = (int) $bulan;
        }
        return redirect()->route('report-register-pendaftaran.export', $params);
      }
    }

    $judul = 'laporan_bulanan_' . $bulan . $tahun . '.xlsx';

    if ($program != "null" && $program != null && isset($program)) {
      $item_program = Program::find($program);
      $clean_judul = preg_replace("/\//", "", $item_program->name_program);
      $judul = 'laporan_bulanan_' . Str::slug($clean_judul) . '_' . $bulan . $tahun . '.xlsx';
    } else {
      $program = null;
    }
    if ($sampletype != "null" && $sampletype != null && isset($sampletype)) {
      $item_sampletype = SampleType::find($sampletype);
      $clean_judul = preg_replace("/\//", "", $item_sampletype->name_sample_type);
      $judul = 'laporan_bulanan_' . Str::slug($clean_judul) . '_' . $bulan . $tahun . '.xlsx';
    } else {
      $sampletype = null;
    }
    if ($laboratorium != "null" && $laboratorium != null && isset($laboratorium)) {
      $item_laboratorium = Laboratorium::find($laboratorium);
      if ($item_laboratorium) {
        $clean_judul = preg_replace("/\//", "", $item_laboratorium->nama_laboratorium);
        $judul = 'laporan_bulanan_' . Str::slug($clean_judul) . '_' . $bulan . $tahun . '.xlsx';
      }
    } else {
      $laboratorium = null;
    }

    return Excel::download(new LaboratoriumReportMonthly($bulan, $tahun, $program, $sampletype, $laboratorium), $judul);
  }



  public function report_date_verification_monthly()
  {
    return view('masterweb::module.admin.laboratorium.report.report-date-verification-monthly.list');
  }

  public function data_report_date_verification_monthly(Request $request)
  {

    $datas = Sample::leftjoin('tb_sample_penanganan', function ($join) {
      $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
        ->whereNull('tb_sample_penanganan.deleted_at')
        ->whereNull('tb_samples.deleted_at');
    })->join('tb_permohonan_uji', function ($join) {
      $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
        ->whereNull('tb_permohonan_uji.deleted_at')
        ->whereNull('tb_samples.deleted_at')
        ->leftjoin('ms_customer', function ($join) {
          $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->whereNull('ms_customer.deleted_at');
        });
    })
      // ->orderBy('penanganan_sample_date', 'asc')
      ->select('tb_samples.*', 'penanganan_sample_date');
    // ->whereMonth('penanganan_sample_date', request()->query('month'))
    // ->whereYear('penanganan_sample_date', request()->query('year'))
    // ->distinct('tb_sample_penanganan.id_sample_penanganan')
    // ->get();

    // dd($datas);

    /* $datas = Sample::whereHas('pengesahanhasil', function ($query) {
            return $query->whereNull('sample_id');
        })
            ->orderBy('created_at'); */

    /* $datas = Sample::whereHas('pengesahanhasil', function ($query) {
            return $query->whereNull('deleted_at')->whereNull('sample_id');
        })
            ->whereHas('permohonanuji', function ($query) {
                return $query->whereNull('deleted_at');
            })
            ->whereHas('samplemethod', function ($query) {
                return $query->whereNull('deleted_at');
            })
            ->orderBy('date_penanganan_sample', 'asc'); */

    // $datas = Sample::orderBy('date_penanganan_sample', 'asc');

    // dd($datas);

    return Datatables::of($datas)
      ->addColumn('tgl_pemeriksaan', function ($data) {
        return isset($data->penanganan_sample_date) ? Carbon::createFromFormat('Y-m-d H:i:s', $data->penanganan_sample_date)->isoFormat('D MMMM Y HH:mm') : "";
      })
      ->addColumn('customer', function ($data) {
        return $data instanceof Sample ? $data->namaPelangganDisplay() : (optional(optional($data->permohonanuji)->customer)->name_customer ?? '-');
      })
      ->filter(function ($query) {
        if (request()->query('month')) {
          $query->whereMonth('date_permohonan_uji', request()->query('month'));
        }

        if (request()->query('year')) {
          $query->whereYear('date_permohonan_uji', request()->query('year'));
        }

        if (request()->has('program') && request()->query('program') != null) {
          $query->where('program_samples', request()->query('program'));
        }

        if (request()->has('sampletype') && request()->query('sampletype') != null) {
          $query->where('typesample_samples', request()->query('sampletype'));
        }
      })
      ->rawColumns(['tgl_pemeriksaan', 'customer'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  public function printReportDateVerificationMonthly(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');

    // dd($bulan . ' ' . $tahun . ' ' . $program);

    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          // return $query->whereNull('deleted_at')->whereNull('sample_id');
          return $query->whereNull('deleted_at')->whereNull('sample_id');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->orderBy('date_penanganan_sample', 'asc')
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
        ->get();

      // dd($data_sample);

      // PRINT ALL METHOD TO HORIZONTAL
      $method_all = Sample::orderBy('date_penanganan_sample', 'asc')
        ->where('program_samples', $program)
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
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
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*')
        ->distinct('ms_method.id_method')
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')
          ->orderBy('name_method', 'asc');
      })
        ->whereHas('sample', function ($query) use ($program, $bulan, $tahun) {
          return $query
            ->where('program_samples', $program)
            ->whereMonth('date_penanganan_sample', $bulan)
            ->whereYear('date_penanganan_sample', $tahun)
            ->orderBy('date_penanganan_sample', 'asc');
        })
        // ->whereHas('sample.pengesahanhasil', function ($query) {
        //     return $query->whereNull('sample_id');
        // })
        ->get();

      // dd($method_all);

      // dd(is_object($data_sample));

      if (!empty($data_sample) && $data_sample !== null) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-monthly.formatPrint.report-monthly', [
          'data_sample' => $data_sample,
          'bulan' => $bulan,
          'tahun' => $tahun,
          'item_program' => $item_program,
          'method_all' => $method_all,
          'data_sample_method' => $data_sample_method
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
      } else {
        return redirect('/report-monthly')->with(['warning' => 'Tidak ada laporan pada bulan yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }



  public function printReportDateVerificationMonthly_to_excel(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');

    // dd($bulan . ' ' . $tahun . ' ' . $program);

    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          // return $query->whereNull('deleted_at')->whereNull('sample_id');
          return $query->whereNull('deleted_at')->whereNull('sample_id');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->orderBy('date_penanganan_sample', 'asc')
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
        ->get();

      // dd($data_sample);

      // PRINT ALL METHOD TO HORIZONTAL
      $method_all = Sample::orderBy('date_penanganan_sample', 'asc')
        ->where('program_samples', $program)
        ->whereMonth('date_penanganan_sample', $bulan)
        ->whereYear('date_penanganan_sample', $tahun)
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
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->select('ms_method.*')
        ->distinct('ms_method.id_method')
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')
          ->orderBy('name_method', 'asc');
      })
        ->whereHas('sample', function ($query) use ($program, $bulan, $tahun) {
          return $query
            ->where('program_samples', $program)
            ->whereMonth('date_penanganan_sample', $bulan)
            ->whereYear('date_penanganan_sample', $tahun)
            ->orderBy('date_penanganan_sample', 'asc');
        })
        // ->whereHas('sample.pengesahanhasil', function ($query) {
        //     return $query->whereNull('sample_id');
        // })
        ->get();

      // dd($method_all);

      // dd(is_object($data_sample));

      if (!empty($data_sample) && $data_sample !== null) {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A1", 'PEMERIKSAAN ' . strtoupper($item_program->name_program));
        $sheet->mergeCells("A2:F2");
        $sheet->setCellValue("A2", 'BULAN ' . fbulan($bulan) . ' ' . $tahun);

        $sheet->mergeCells("A4:A5");
        $sheet->setCellValue('A4', 'NO');
        $sheet->mergeCells("B4:B5");
        $sheet->setCellValue('B4', 'TGL. PEMERIKSAAN');
        $sheet->mergeCells("C4:C5");
        $sheet->setCellValue('C4', 'NAMA SARANA');
        $sheet->mergeCells("D4:D5");
        $sheet->setCellValue('D4', 'TITIK/LOKASI');
        $sheet->mergeCells("E4:E5");
        $sheet->setCellValue('E4', 'JENIS SAMPEL');
        $sheet->mergeCells($this->getColRange('F', 4, count($method_all)));
        $sheet->setCellValue('F4', 'HASIL PEMERIKSAAN');

        foreach ($method_all as $key => $value) {
          $sheet->setCellValue('F5', $value->name_method);
        }

        $no = 1;

        foreach ($data_sample as $key_ds => $item_ds) {
          $no++;

          $sheet->setCellValue('A' . $no, $no);
          $sheet->setCellValue('B' . $no, Carbon::createFromFormat('Y-m-d H:i:s', $item_ds->date_penanganan_sample)->isoFormat('D MMMM Y HH:mm'));
          $sheet->setCellValue('C' . $no, $item_ds->codesample_samples ?? '-');
          $sheet->setCellValue('D' . $no, $item_ds->location_samples ?? '-');
          $sheet->setCellValue('E' . $no, $item_ds->sampletype->name_sample_type);


          $sheet->setCellValue('F' . $no, $no);
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Laporan_Bulanan_' . $item_program->name_program . '_' . date('Y-m-d') . '.xlsx"');

        $writer->save('php://output');
      } else {
        return redirect('/report-monthly')->with(['warning' => 'Tidak ada laporan pada bulan yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }



  public function printReportDateVerificationMonthly_to_maatweb(Request $request)
  {
    $bulan  = request()->query('month');
    $tahun = request()->query('year');
    $program = request()->query('program');
    $sampletype = request()->query('sampletype');


    $date_verification = request()->query('date_verification');
    $date_verification = explode(",", $date_verification);
    $date_verification = array_filter($date_verification);


    $judul = 'laporan_bulanan_' . $bulan . $tahun . '.xlsx';

    if ($program != "null" && $program != null && isset($program)) {
      $item_program = Program::find($program);
      $clean_judul = preg_replace("/\//", "", $item_program->name_program);
      $judul = 'laporan_bulanan_' . Str::slug($clean_judul) . '_' . $bulan . $tahun . '.xlsx';

    } else {
      $program = null;
    }
    if ($sampletype != "null" && $sampletype != null && isset($sampletype)) {
      $item_sampletype = SampleType::find($sampletype);
      $clean_judul = preg_replace("/\//", "", $item_sampletype->name_sample_type);
      $judul = 'laporan_bulanan_' . Str::slug($clean_judul) . '_' . $bulan . $tahun . '.xlsx';

    } else {
      $sampletype = null;
    }

    return Excel::download(new LaboratoriumReportDateVerificationMonthly($bulan, $tahun, $sampletype, $date_verification), $judul);
  }


  function getColRange($start_letter, $row_number, $count)
  {

    $alphabets = range('A', 'Z');
    $start_idx = array_search(
      $start_letter,
      $alphabets
    );

    return sprintf(
      "%s%s:%s%s",
      $start_letter,
      $row_number,
      $alphabets[$start_idx + $count],
      $row_number
    );
  }


  public function printReportAnually_to_maatweb(Request $request)
  {
    $tahun = request()->query('year');
    $program = request()->query('program');
    $sampletype = request()->query('sampletype');
    $laboratorium = request()->query('laboratorium');

    if ($laboratorium && $laboratorium !== 'null' && $tahun) {
      $lab = Laboratorium::find($laboratorium);
      if ($lab && $lab->kode_laboratorium === 'KLI') {
        return redirect()->route('report-register-pendaftaran.export', [
          'year' => (int) $tahun,
          'laboratorium_id' => $laboratorium,
        ]);
      }
    }

    $judul = 'laporan_tahunan_' . $tahun . '.xlsx';

    if ($program != "null" && $program != null && isset($program)) {
      $item_program = Program::find($program);
      $clean_judul = preg_replace("/\//", "", $item_program->name_program);
      $judul = 'laporan_tahunan_' . Str::slug($clean_judul) . '_' . $tahun . '.xlsx';
    } else {
      $program = null;
    }
    if ($sampletype != "null" && $sampletype != null && isset($sampletype)) {
      $item_sampletype = SampleType::find($sampletype);
      $clean_judul = preg_replace("/\//", "", $item_sampletype->name_sample_type);
      $judul = 'laporan_tahunan_' . Str::slug($clean_judul) . '_' . $tahun . '.xlsx';
    } else {
      $sampletype = null;
    }
    if ($laboratorium != "null" && $laboratorium != null && isset($laboratorium)) {
      $item_laboratorium = Laboratorium::find($laboratorium);
      if ($item_laboratorium) {
        $clean_judul = preg_replace("/\//", "", $item_laboratorium->nama_laboratorium);
        $judul = 'laporan_tahunan_' . Str::slug($clean_judul) . '_' . $tahun . '.xlsx';
      }
    } else {
      $laboratorium = null;
    }

    return Excel::download(new LaboratoriumReportAnually($tahun, $program, $sampletype, $laboratorium), $judul);
  }


  public function mountly(Request $request, $date_from = null, $date_to = null)
  {
    $laboratoriummethods = SampleMethod::orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('ms_method.params_method as id', DB::raw("count(ms_method.id_method) as count"))
      ->groupBy('ms_method.id_method')
      ->get();

    // ->join('assigned_tags', 'website_tags.id', '=', 'assigned_tags.tag_id')
    // ->select('website_tags.id as id', 'website_tags.title as title', DB::raw("count(assigned_tags.tag_id) as count"))
    // ->groupBy('website_tags.id')
    // ->get();
    dd($laboratoriummethods);
    //    dd("ygygyu");

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
      ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
      ->distinct('id_laboratorium')
      ->get();

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
    return $pdf->stream();
  }

  public function report_annual()
  {
    return view('masterweb::module.admin.laboratorium.report.report-annual.list');
  }

  public function data_report_annual(Request $request)
  {
    $year = (int) $request->year;
    $laboratoriumId = $request->laboratorium;
    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';

    $showNonKlinik = !$laboratoriumId || !$isKlinikLabFilter;
    $showKlinik = !$laboratoriumId || $isKlinikLabFilter;

    $nonKlinikRows = collect();
    if ($showNonKlinik && $year) {
      $query = Sample::join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->leftJoin('ms_customer', function ($join) {
            $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
              ->whereNull('tb_permohonan_uji.deleted_at')
              ->whereNull('ms_customer.deleted_at');
          });
      })
        ->whereExists(function ($q) use ($request) {
          $q->select(DB::raw(1))
            ->from('tb_sample_method')
            ->whereColumn('tb_sample_method.sample_id', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at');

          if ($request->laboratorium) {
            $q->where('tb_sample_method.laboratorium_id', $request->laboratorium);
          }
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at IS NULL AND tb_samples.deleted_at IS NULL LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('ms_packet', function ($join) {
          $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
            ->whereNull('ms_packet.deleted_at');
        })
        ->whereYear('date_permohonan_uji', $year);

      if ($request->sampletype) {
        $query->where('tb_samples.typesample_samples', $request->sampletype);
      }

      $query->select(
        'tb_sample_penanganan.id_sample_penanganan as id_sample_penanganan',
        'tb_sample_penanganan.sample_id as penanganan_sample_id',
        'tb_sample_penanganan.penanganan_sample_date as penanganan_sample_date',
        'tb_sample_penerimaan.id_sample_penerimaan as id_sample_penerimaan',
        'tb_sample_penerimaan.sample_id as penerimaan_sample_id',
        'date_sending',
        'date_analitik_sample',
        'name_sample_type',
        'tb_samples.id_samples as id_samples',
        'tb_samples.codesample_samples as codesample_samples',
        'tb_samples.permohonan_uji_id as permohonan_uji_id',
        'tb_samples.cost_samples as cost_samples',
        'tb_samples.packet_id as packet_id',
        'ms_packet.price_total_packet as price_total_packet',
        'ms_customer.id_customer as id_customer',
        'ms_customer.name_customer as name_customer',
        'tb_permohonan_uji.id_permohonan_uji as id_permohonan_uji',
        'tb_permohonan_uji.code_permohonan_uji as code_permohonan_uji',
        'tb_permohonan_uji.date_permohonan_uji as date_permohonan_uji',
        'tb_permohonan_uji.customer_id as customer_id',
        'tb_permohonan_uji.total_harga as total_harga',
        'tb_permohonan_uji.created_at as created_at'
      );

      $nonKlinikRows = $query->get()->unique('id_samples')->values();
    }

    $klinikRows = collect();
    if ($showKlinik && !$request->sampletype && $year) {
      $klinikRows = PermohonanUjiKlinik2::query()
        ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
        ->join('ms_pasien', function ($join) {
          $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
            ->whereNull('ms_pasien.deleted_at');
        })
        ->whereYear('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $year)
        ->select(
          'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.tgl_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jam_sampling_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik',
          'tb_permohonan_uji_klinik_2.jenis_spesimen',
          'tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik',
          'ms_pasien.nama_pasien as name_customer',
        )
        ->get()
        ->map(function ($row) {
          $obj = new \stdClass;
          $tanggalUji = $row->tglpengujian_permohonan_uji_klinik;
          if (!$tanggalUji && $row->tgl_sampling_permohonan_uji_klinik) {
            $tanggalUji = trim($row->tgl_sampling_permohonan_uji_klinik . ' ' . ($row->jam_sampling_permohonan_uji_klinik ?? ''));
          }
          $obj->penanganan_sample_date = $tanggalUji ?: $row->tglregister_permohonan_uji_klinik;
          $obj->date_permohonan_uji = $row->tglregister_permohonan_uji_klinik;
          $obj->name_customer = $row->name_customer;
          $code = $row->noregister_permohonan_uji_klinik ?? '-';
          $jenis = $this->formatJenisSpesimenLabel($row->jenis_spesimen);
          $obj->codesample_samples = $jenis !== ''
            ? ($code . ' — ' . $jenis)
            : $code;
          $obj->cost_samples = $row->total_harga_permohonan_uji_klinik;
          return $obj;
        });
    }

    $datas = $nonKlinikRows->values()->concat($klinikRows->values());

    return DataTables::of($datas)
      ->filter(function ($instance) use ($request) {
        $needle = $request->input('search.value', $request->get('search'));
        if (is_array($needle)) {
          $needle = $needle['value'] ?? '';
        }
        if ($needle === null || $needle === '') {
          return;
        }
        $needle = Str::lower((string) $needle);
        $instance->collection = $instance->collection->filter(function ($row) use ($needle) {
          $candidates = [
            Str::lower((string) data_get($row, 'name_customer')),
            Str::lower((string) data_get($row, 'codesample_samples')),
            Str::lower((string) data_get($row, 'penanganan_sample_date')),
          ];
          foreach ($candidates as $haystack) {
            if ($haystack !== '' && Str::contains($haystack, $needle)) {
              return true;
            }
          }
          return false;
        });
      })
      ->addColumn('tgl_pemeriksaan', function ($data) {
        $raw = data_get($data, 'penanganan_sample_date')
          ?: data_get($data, 'date_analitik_sample')
          ?: data_get($data, 'date_sending')
          ?: data_get($data, 'date_permohonan_uji');
        if (empty($raw)) {
          return '';
        }
        try {
          return Carbon::parse($raw)->isoFormat('D MMMM Y HH:mm');
        } catch (\Throwable $e) {
          return '';
        }
      })
      ->addColumn('customer', function ($data) {
        return $data->name_customer ?? '-';
      })
      ->addColumn('harga_per_sample', function ($data) {
        $sampleId = data_get($data, 'id_samples');
        $permohonanId = data_get($data, 'permohonan_uji_id');
        if ($sampleId && $permohonanId) {
          return rupiah(KesmasNotaHelper::amountForSample($sampleId, $permohonanId));
        }

        return rupiah((float) ($data->cost_samples ?? 0));
      })
      ->rawColumns(['tgl_pemeriksaan', 'customer', 'harga_per_sample'])
      ->addIndexColumn()
      ->make(true);
  }

  /**
   * Total harga permohonan klinik pada tahun register; sama syaratnya dengan baris klinik di laporan tahunan.
   */
  private function sumKlinikTotalHargaPermohonanTahunan(Request $request): float
  {
    $sampletype = $request->sampletype;
    if ($sampletype !== null && $sampletype !== '' && (string) $sampletype !== 'null') {
      return 0.0;
    }

    $laboratoriumId = $request->laboratorium;
    if ($laboratoriumId === null || $laboratoriumId === '' || (string) $laboratoriumId === 'null') {
      $laboratoriumId = null;
    }

    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    $isKlinikLabFilter = $selectedLab && $selectedLab->kode_laboratorium === 'KLI';
    if ($laboratoriumId && !$isKlinikLabFilter) {
      return 0.0;
    }

    $year = (int) $request->year;
    if (!$year) {
      return 0.0;
    }

    return (float) PermohonanUjiKlinik2::query()
      ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
      ->whereYear('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', $year)
      ->sum('tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik');
  }

  /**
   * Jumlahkan tarif sample sekali per sample, memakai rumus yang sama dengan nota Kesmas.
   */
  private function sumNonKlinikCostSamplesOnce(Request $request, callable $applyDateFilter): float
  {
    $laboratoriumId = $request->laboratorium;
    if ($laboratoriumId === null || $laboratoriumId === '' || (string) $laboratoriumId === 'null') {
      $laboratoriumId = null;
    }

    $selectedLab = $laboratoriumId ? Laboratorium::find($laboratoriumId) : null;
    if ($selectedLab && $selectedLab->kode_laboratorium === 'KLI') {
      return 0.0;
    }

    $query = Sample::query()
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->whereExists(function ($q) use ($laboratoriumId) {
        $q->select(DB::raw(1))
          ->from('tb_sample_method')
          ->whereColumn('tb_sample_method.sample_id', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');

        if ($laboratoriumId) {
          $q->where('tb_sample_method.laboratorium_id', $laboratoriumId);
        }
      });

    $applyDateFilter($query);

    $sampletype = $request->sampletype;
    if ($sampletype !== null && $sampletype !== '' && (string) $sampletype !== 'null') {
      $query->where('tb_samples.typesample_samples', $sampletype);
    }

    $rows = $query
      ->select('tb_samples.id_samples', 'tb_samples.permohonan_uji_id')
      ->distinct()
      ->get();

    $total = 0.0;
    foreach ($rows as $row) {
      $total += KesmasNotaHelper::amountForSample($row->id_samples, $row->permohonan_uji_id);
    }

    return $total;
  }

  public function getTotalHargaSampleAnnual(Request $request)
  {
    $year = (int) $request->year;
    $samples = 0.0;

    if ($year) {
      $samples = $this->sumNonKlinikCostSamplesOnce($request, function ($query) use ($year) {
        $query->whereYear('tb_permohonan_uji.date_permohonan_uji', $year);
      });
    }

    $samples = $samples + $this->sumKlinikTotalHargaPermohonanTahunan($request);

    return response()->json(rupiah($samples));
  }

  public function printReportAnnual(Request $request)
  {
    $tahun = request()->query('year');
    $program = request()->query('program');

    // dd($bulan . ' ' . $tahun . ' ' . $program);

    if (isset($program) || $program !== null) {
      $item_program = Program::find($program);

      $data_sample = Sample::where('program_samples', $program)
        ->whereHas('pengesahanhasil', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('permohonanuji', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereHas('samplemethod', function ($query) {
          return $query->whereNull('deleted_at');
        })
        ->whereYear('date_penanganan_sample', $tahun)
        ->get();

      $data_sample_method = SampleMethod::whereHas('method', function ($query) {
        return $query->whereNull('deleted_at')->orderBy('name_method', 'asc');
      })
        ->get();

      // dd(is_object($data_sample));

      if (!empty($data_sample) && $data_sample !== null) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-annual.formatPrint.report-annual', [
          'data_sample' => $data_sample,
          'tahun' => $tahun,
          'item_program' => $item_program,
          'data_sample_method' => $data_sample_method
        ]);

        return $pdf->stream();
      } else {
        return redirect('/report-annual')->with(['warning' => 'Tidak ada laporan pada tahun yang Anda pilih!']);
      }
    } else {
      return abort(404);
    }
  }

  public function yearly($date_from = null, $date_to = null)
  {

    $laboratoriummethods = SampleMethod::orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('ms_method.params_method as id', DB::raw("count(ms_method.id_method) as count"))
      ->groupBy('ms_method.id_method')
      ->get();

    // ->join('assigned_tags', 'website_tags.id', '=', 'assigned_tags.tag_id')
    // ->select('website_tags.id as id', 'website_tags.title as title', DB::raw("count(assigned_tags.tag_id) as count"))
    // ->groupBy('website_tags.id')
    // ->get();
    dd($laboratoriummethods);
    //    dd("ygygyu");

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
      ->select('tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'name_sample_type', 'codesample_samples', 'id_samples')
      ->distinct('id_laboratorium')
      ->get();

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.printLHU_all', compact('samples'));
    return $pdf->stream();
  }

  /** @param mixed $raw */
  private function formatJenisSpesimenLabel($raw): string
  {
    if ($raw === null || $raw === '') {
      return '';
    }

    if (is_array($raw)) {
      $parts = array_filter(array_map('trim', array_map('strval', $raw)));

      return $parts ? implode(', ', $parts) : '';
    }

    $decoded = json_decode((string) $raw, true);
    if (JSON_ERROR_NONE === json_last_error() && is_array($decoded)) {
      $parts = array_filter(array_map('trim', array_map('strval', $decoded)));

      return $parts ? implode(', ', $parts) : '';
    }

    $s = trim((string) $raw);

    return $s !== '' ? $s : '';
  }
}
