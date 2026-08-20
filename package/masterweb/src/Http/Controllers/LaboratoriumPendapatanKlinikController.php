<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use PDF;

class LaboratoriumPendapatanKlinikController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $count = PermohonanUjiKlinik2::whereNull('deleted_at')->sum('total_harga_permohonan_uji_klinik');

    if (request()->ajax()) {
      $query = PermohonanUjiKlinik2::with('pasien')->whereNull('deleted_at');

      if (!empty($request->get('start_date')) && !empty($request->get('end_date'))) {
        $start_date = $this->parseDateInput($request->get('start_date'));
        $end_date = $this->parseDateInput($request->get('end_date'));
        if ($start_date && $end_date) {
          $query->whereDate('tglregister_permohonan_uji_klinik', '>=', $start_date)
            ->whereDate('tglregister_permohonan_uji_klinik', '<=', $end_date);
        }
      }

      $datas = $query->latest()->get();

      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['noregister_permohonan_uji_klinik'] ?? ''), Str::lower($request->get('search')))) {
                return true;
              }
              if (Str::contains(Str::lower($row['nama_pasien'] ?? ''), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        ->addColumn('tgl_register', function ($data) {
          return $data->tglregister_permohonan_uji_klinik != null
            ? Carbon::parse($data->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y')
            : '-';
        })
        ->addColumn('tgl_transaksi', function ($data) {
          return $data->created_at != null
            ? Carbon::parse($data->created_at)->isoFormat('D MMMM Y HH:mm')
            : '-';
        })
        ->addColumn('total_harga', function ($data) {
          return $data->total_harga_permohonan_uji_klinik != null
            ? rupiah($data->total_harga_permohonan_uji_klinik)
            : 'Rp. 0';
        })
        ->addColumn('nama_pasien', function ($data) {
          return optional($data->pasien)->nama_pasien ?? '-';
        })
        ->rawColumns(['tgl_register', 'tgl_transaksi', 'total_harga', 'nama_pasien'])
        ->addIndexColumn()
        ->make(true);
    }

    return view('masterweb::module.admin.laboratorium.report.report-pendapatan-klinik.list', compact('count'));
  }

  public function getCountTotalPendapatan(Request $request)
  {
    $search = $request->search;
    $start_date = $this->parseDateInput($request->start_date);
    $end_date = $this->parseDateInput($request->end_date);

    $query = PermohonanUjiKlinik2::whereNull('deleted_at');

    if ($start_date && $end_date) {
      $query->whereDate('tglregister_permohonan_uji_klinik', '>=', $start_date)
        ->whereDate('tglregister_permohonan_uji_klinik', '<=', $end_date);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('noregister_permohonan_uji_klinik', 'LIKE', '%' . $search . '%')
          ->orWhereHas('pasien', function ($query) use ($search) {
            $query->where('nama_pasien', 'LIKE', '%' . $search . '%')
              ->whereNull('deleted_at');
          });
      });
    }

    $count = (float) $query->sum('total_harga_permohonan_uji_klinik');

    return response()->json($count);
  }

  public function setPrintDataPeriodikKlinik(Request $request)
  {
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $search = $request->search;

    if ($start_date === 'Invalid date') {
      $start_date = null;
    }
    if ($end_date === 'Invalid date') {
      $end_date = null;
    }

    $start_date_format = !empty($start_date)
      ? Carbon::parse($start_date)->isoFormat('D MMMM Y')
      : '';
    $end_date_format = !empty($end_date)
      ? Carbon::parse($end_date)->isoFormat('D MMMM Y')
      : '';

    $query = PermohonanUjiKlinik2::query()
      ->leftJoin('ms_pasien', function ($join) {
        $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
          ->whereNull('ms_pasien.deleted_at');
      })
      ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
      ->select(
        'ms_pasien.nik_pasien as nik_pasien',
        'ms_pasien.nama_pasien as nama_pasien',
        'ms_pasien.no_rekammedis_pasien as nopasien_permohonan_uji_klinik',
        'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik as id_permohonan_uji_klinik',
        'tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik as noregister_permohonan_uji_klinik',
        'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik as tglregister_permohonan_uji_klinik',
        'tb_permohonan_uji_klinik_2.created_at as created_at',
        'tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik as total_harga_permohonan_uji_klinik'
      );

    if (!empty($start_date) && !empty($end_date)) {
      $query->whereDate('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', '>=', $start_date)
        ->whereDate('tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik', '<=', $end_date);
    }

    if (!empty($search)) {
      $query->where(function ($q) use ($search) {
        $q->where('tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik', 'LIKE', '%' . $search . '%')
          ->orWhere('ms_pasien.nama_pasien', 'LIKE', '%' . $search . '%');
      });
    }

    $data = $query->orderBy('tb_permohonan_uji_klinik_2.created_at', 'asc')->get();

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-pendapatan-klinik.print-format.print-periodik', [
      'permohonan_uji_klinik' => $data,
      'start_date_format' => $start_date_format,
      'end_date_format' => $end_date_format,
    ]);

    return $pdf->stream();
  }

  private function parseDateInput($value): ?string
  {
    if ($value === null || $value === '' || $value === 'Invalid date') {
      return null;
    }

    try {
      return Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
    } catch (\Throwable $e) {
      try {
        return Carbon::parse($value)->format('Y-m-d');
      } catch (\Throwable $e2) {
        return null;
      }
    }
  }

  public function create()
  {
  }

  public function store(Request $request)
  {
  }

  public function show($id)
  {
  }

  public function edit($id)
  {
  }

  public function update(Request $request, $id)
  {
  }

  public function destroy($id)
  {
  }
}
