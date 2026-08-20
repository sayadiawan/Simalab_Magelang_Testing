<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\Sample;
use Yajra\DataTables\DataTables;
use PDF;

/**
 * Laporan pendapatan non-klinik.
 * - 1 baris = 1 sampel per lab (tidak dobel karena banyak method)
 * - Tarif paket dikoreksi lewat Sample::resolveReportTarifFromValues
 * - Biaya rectal swab dijumlahkan konsisten di list, total, dan print
 */
class LaboratoriumPendapatanNonklinikController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    if (request()->ajax()) {
      $filters = $this->parseFilters($request, 'm/d/Y', true);
      $rows = $this->fetchPendapatanRows($filters);

      return Datatables::of($rows)
        ->addColumn('name_sample_type', function ($data) {
          $name = $data->name_sample_type ?? '-';
          if ((float) ($data->rectal_swab_price ?? 0) > 0) {
            $name .= ' + Biaya Rectal Swab';
          }

          return $name;
        })
        ->addColumn('tgl_transaksi', function ($data) {
          return !empty($data->date_sending)
            ? Carbon::parse($data->date_sending)->isoFormat('D MMMM Y HH:mm')
            : '-';
        })
        ->addColumn('cost_samples', function ($data) {
          return rupiah($this->resolveRowAmount($data));
        })
        ->addColumn('name_customer', function ($data) {
          return $data->name_customer ?? '-';
        })
        ->addColumn('nama_laboratorium', function ($data) {
          return $data->nama_laboratorium ?? '-';
        })
        ->rawColumns(['codesample_samples', 'name_sample_type', 'tgl_transaksi', 'cost_samples', 'name_customer', 'nama_laboratorium'])
        ->addIndexColumn()
        ->make(true);
    }

    $data_lab = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->latest()->get();

    return view('masterweb::module.admin.laboratorium.report.report-pendapatan-nonklinik.list', compact('data_lab'));
  }

  public function getCountTotalPendapatan(Request $request)
  {
    $filters = $this->parseFilters($request, 'm/d/Y', true);
    $rows = $this->fetchPendapatanRows($filters);

    $count_pendapatan = 0.0;
    foreach ($rows as $row) {
      $count_pendapatan += $this->resolveRowAmount($row);
    }

    return response()->json([
      'count_pendapatan' => $count_pendapatan,
      'count_sample' => $rows->count(),
      'count_pendapatan_rupiah' => rupiah($count_pendapatan),
    ]);
  }

  public function setPrintDataPeriodikNonklinik(Request $request)
  {
    $filters = $this->parseFilters($request, 'Y-m-d', false);

    $start_date_format = !empty($filters['start_date'])
      ? Carbon::parse($filters['start_date'])->isoFormat('D MMMM Y')
      : Carbon::now()->isoFormat('D MMMM Y');
    $end_date_format = !empty($filters['end_date'])
      ? Carbon::parse($filters['end_date'])->isoFormat('D MMMM Y')
      : Carbon::now()->isoFormat('D MMMM Y');

    $rows = $this->fetchPendapatanRows($filters);
    foreach ($rows as $row) {
      $row->resolved_cost = $this->resolveRowAmount($row);
      $row->has_rectal_swab = (float) ($row->rectal_swab_price ?? 0) > 0;
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.report.report-pendapatan-nonklinik.print-format.print-periodik', [
      'permohonan_uji_nonklinik' => $rows,
      'start_date_format' => $start_date_format,
      'end_date_format' => $end_date_format,
    ]);

    return $pdf->stream();
  }

  /**
   * @return array{lab_id:?string,start_date:?string,end_date:?string,payment:?string,search:?string}
   */
  private function parseFilters(Request $request, string $inputFormat, bool $defaultTodayIfEmpty): array
  {
    $labId = $request->input('name_lab');
    if ($labId === null || $labId === '' || $labId === 'null') {
      $labId = null;
    }

    $startRaw = $request->input('start_date');
    $endRaw = $request->input('end_date');
    if ($startRaw === 'Invalid date') {
      $startRaw = null;
    }
    if ($endRaw === 'Invalid date') {
      $endRaw = null;
    }

    $startDate = null;
    $endDate = null;
    try {
      if (!empty($startRaw)) {
        $startDate = Carbon::createFromFormat($inputFormat, $startRaw)->format('Y-m-d');
      }
      if (!empty($endRaw)) {
        $endDate = Carbon::createFromFormat($inputFormat, $endRaw)->format('Y-m-d');
      }
    } catch (\Throwable $e) {
      try {
        $startDate = !empty($startRaw) ? Carbon::parse($startRaw)->format('Y-m-d') : null;
        $endDate = !empty($endRaw) ? Carbon::parse($endRaw)->format('Y-m-d') : null;
      } catch (\Throwable $e2) {
        $startDate = null;
        $endDate = null;
      }
    }

    if ($defaultTodayIfEmpty && (empty($startDate) || empty($endDate))) {
      $today = Carbon::now()->format('Y-m-d');
      $startDate = $startDate ?: $today;
      $endDate = $endDate ?: $today;
    }

    $payment = $request->input('payment');
    if ($payment === null || $payment === '' || $payment === 'null') {
      $payment = null;
    }

    $search = trim((string) $request->input('search', ''));
    if ($search === '') {
      $search = null;
    }

    return [
      'lab_id' => $labId,
      'start_date' => $startDate,
      'end_date' => $endDate,
      'payment' => $payment,
      'search' => $search,
    ];
  }

  private function fetchPendapatanRows(array $filters)
  {
    $labId = $filters['lab_id'];
    $startDate = $filters['start_date'];
    $endDate = $filters['end_date'];
    $payment = $filters['payment'];
    $search = $filters['search'];

    // Satu baris per (sample, lab) — hindari dobel karena banyak method / penerimaan
    $sampleLabSub = DB::table('tb_sample_method')
      ->select('sample_id', 'laboratorium_id')
      ->whereNull('deleted_at')
      ->when($labId, function ($q) use ($labId) {
        $q->where('laboratorium_id', $labId);
      })
      ->groupBy('sample_id', 'laboratorium_id');

    $query = Sample::query()
      ->from('tb_samples')
      ->joinSub($sampleLabSub, 'sm_lab', function ($join) {
        $join->on('sm_lab.sample_id', '=', 'tb_samples.id_samples');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'sm_lab.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at');
      })
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->leftJoin('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->leftJoin('ms_packet', function ($join) {
        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
          ->whereNull('ms_packet.deleted_at');
      })
      ->whereNull('tb_samples.deleted_at')
      ->where('ms_laboratorium.kode_laboratorium', '!=', 'KLI')
      ->select([
        'tb_samples.id_samples',
        'tb_samples.codesample_samples',
        'tb_samples.cost_samples',
        'tb_samples.packet_id',
        'tb_samples.date_sending',
        'tb_samples.count_id',
        DB::raw('COALESCE(tb_samples.biaya_tindakan_rectal_swab, 0) as rectal_swab_price'),
        'ms_sample_type.name_sample_type',
        'ms_laboratorium.id_laboratorium',
        'ms_laboratorium.nama_laboratorium',
        'ms_customer.name_customer',
        'tb_permohonan_uji.code_permohonan_uji',
        'tb_permohonan_uji.status_pembayaran',
        DB::raw('COALESCE(ms_packet.price_total_packet, 0) as price_total_packet'),
      ]);

    if ($startDate) {
      $query->whereDate('tb_samples.date_sending', '>=', $startDate);
    }
    if ($endDate) {
      $query->whereDate('tb_samples.date_sending', '<=', $endDate);
    }
    if ($payment !== null) {
      $query->where('tb_permohonan_uji.status_pembayaran', $payment);
    }
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('tb_permohonan_uji.code_permohonan_uji', 'LIKE', '%' . $search . '%')
          ->orWhere('ms_customer.name_customer', 'LIKE', '%' . $search . '%')
          ->orWhere('tb_samples.codesample_samples', 'LIKE', '%' . $search . '%');
      });
    }

    return $query
      ->orderBy('tb_samples.count_id', 'asc')
      ->orderBy('tb_samples.date_sending', 'asc')
      ->get();
  }

  private function resolveRowAmount($row): float
  {
    $tarif = Sample::resolveReportTarifFromValues(
      (float) ($row->cost_samples ?? 0),
      (float) ($row->price_total_packet ?? 0)
    );
    $rectal = (float) ($row->rectal_swab_price ?? 0);

    return $tarif + max(0, $rectal);
  }

  // Unused CRUD stubs kept for route compatibility
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
