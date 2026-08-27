<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Yajra\DataTables\DataTables;

class BendaharaPemeriksaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless((auth()->user()->getlevel->level ?? null) === 'BNDR', 403);

        if ($request->ajax()) {
            $rows = $this->buildRows($request);

            return DataTables::of($rows)
                ->addColumn('jenis', function ($row) {
                    $class = $row['jenis'] === 'Klinik' ? 'badge-info' : 'badge-primary';
                    return '<span class="badge ' . $class . '">' . e($row['jenis']) . '</span>';
                })
                ->addColumn('tanggal', function ($row) {
                    return !empty($row['tanggal']) ? Carbon::parse($row['tanggal'])->isoFormat('D MMM Y HH:mm') : '-';
                })
                ->addColumn('nominal', function ($row) {
                    return rupiah($row['nominal'] ?? 0);
                })
                ->addColumn('status_html', function ($row) {
                    return $row['status_html'] ?? '-';
                })
                ->addColumn('dokumen_html', function ($row) {
                    return $row['dokumen_html'] ?? '-';
                })
                ->rawColumns(['jenis', 'status_html', 'dokumen_html'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('masterweb::module.admin.bendahara.pemeriksaan.index');
    }

    private function buildRows(Request $request): Collection
    {
        $search = trim((string) $request->input('search.value', ''));
        $payment = $request->input('payment_status');
        $source = $request->input('source_type');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $rows = collect()
            ->merge($this->buildKlinikRows())
            ->merge($this->buildKesmasRows())
            ->when($source && $source !== 'all', function ($collection) use ($source) {
                return $collection->where('jenis_key', $source);
            })
            ->when($payment !== null && $payment !== '' && $payment !== 'all', function ($collection) use ($payment) {
                return $collection->where('status_key', $payment);
            })
            ->when($dateStart, function ($collection) use ($dateStart) {
                return $collection->filter(function ($row) use ($dateStart) {
                    return !empty($row['tanggal']) && Carbon::parse($row['tanggal'])->toDateString() >= $dateStart;
                });
            })
            ->when($dateEnd, function ($collection) use ($dateEnd) {
                return $collection->filter(function ($row) use ($dateEnd) {
                    return !empty($row['tanggal']) && Carbon::parse($row['tanggal'])->toDateString() <= $dateEnd;
                });
            })
            ->when($search !== '', function ($collection) use ($search) {
                $needle = mb_strtolower($search);

                return $collection->filter(function ($row) use ($needle) {
                    return str_contains(mb_strtolower((string) ($row['nomor'] ?? '')), $needle)
                        || str_contains(mb_strtolower((string) ($row['nama'] ?? '')), $needle)
                        || str_contains(mb_strtolower((string) ($row['jenis'] ?? '')), $needle);
                });
            })
            ->sortByDesc('tanggal')
            ->values();

        return $rows;
    }

    private function buildKlinikRows(): Collection
    {
        $paymentMap = PermohonanUjiPaymentKlinik::query()
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('permohonan_uji_klinik_id');

        return PermohonanUjiKlinik2::query()
            ->leftJoin('ms_pasien', function ($join) {
                $join->on('ms_pasien.id_pasien', '=', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik')
                    ->whereNull('ms_pasien.deleted_at');
            })
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at')
            ->select(
                'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik',
                'tb_permohonan_uji_klinik_2.noregister_permohonan_uji_klinik',
                'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik',
                'tb_permohonan_uji_klinik_2.created_at',
                'tb_permohonan_uji_klinik_2.status_pembayaran',
                'tb_permohonan_uji_klinik_2.total_harga_permohonan_uji_klinik',
                'tb_permohonan_uji_klinik_2.biaya_pengambilan_sampel',
                'tb_permohonan_uji_klinik_2.metode_pembayaran',
                'ms_pasien.nama_pasien'
            )
            ->get()
            ->map(function ($row) use ($paymentMap) {
                $payment = $paymentMap->get($row->id_permohonan_uji_klinik);
                $total = (int) ($row->total_harga_permohonan_uji_klinik ?? 0) + (int) ($row->biaya_pengambilan_sampel ?? 0);
                $paid = (int) ($payment->terbayar_permohonan_uji_payment_klinik ?? 0);
                $isPaid = (int) $row->status_pembayaran === 1;
                $isPartial = !$isPaid && $paid > 0;

                return [
                    'id' => $row->id_permohonan_uji_klinik,
                    'jenis' => 'Klinik',
                    'jenis_key' => 'klinik',
                    'nomor' => $row->noregister_permohonan_uji_klinik ?: '-',
                    'nama' => $row->nama_pasien ?: '-',
                    'tanggal' => $row->tglregister_permohonan_uji_klinik ?: $row->created_at,
                    'nominal' => $total,
                    'status_key' => $isPaid ? 'lunas' : 'belum_lunas',
                    'status_html' => $isPaid
                        ? '<span class="badge badge-success pointer btn-payment-detail" data-id="' . e($row->id_permohonan_uji_klinik) . '">Lunas</span>'
                        : '<span class="badge ' . ($isPartial ? 'badge-warning' : 'badge-danger') . ' pointer btn-payment" data-id="' . e($row->id_permohonan_uji_klinik) . '">' . ($isPartial ? 'Belum Lunas' : 'Belum Bayar') . '</span>',
                    'dokumen_html' => $this->printButtons('klinik', $row->id_permohonan_uji_klinik, $isPaid),
                ];
            });
    }

    private function buildKesmasRows(): Collection
    {
        return PermohonanUji::query()
            ->leftJoin('ms_customer', function ($join) {
                $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
                    ->whereNull('ms_customer.deleted_at');
            })
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->select(
                'tb_permohonan_uji.id_permohonan_uji',
                'tb_permohonan_uji.code_permohonan_uji',
                'tb_permohonan_uji.created_at',
                'tb_permohonan_uji.total_harga',
                'tb_permohonan_uji.status_pembayaran',
                'tb_permohonan_uji.nota_diterima_dari',
                'tb_permohonan_uji.nota_address_from',
                'ms_customer.name_customer',
                'ms_customer.address_customer'
            )
            ->get()
            ->map(function ($row) {
                $isPaid = (int) $row->status_pembayaran === 1;
                $modalId = 'modal-kesmas-payment-' . $row->id_permohonan_uji;
                $statusButton = $isPaid
                    ? '<span class="badge badge-success">Lunas</span>'
                    : '<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#' . $modalId . '">Belum Bayar</button>';

                $modal = '';
                if (!$isPaid) {
                    $modal = '
                        <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Konfirmasi Pembayaran Kesmas</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="' . route('elits-permohonan-uji.payment', $row->id_permohonan_uji) . '" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                                            <div class="form-group">
                                                <label>Pelanggan</label>
                                                <input type="text" class="form-control" value="' . e($row->name_customer ?: '-') . '" name="recipient-name" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Alamat</label>
                                                <textarea class="form-control" name="address" rows="3" required>' . e($row->address_customer ?: '-') . '</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Bayar</label>
                                                <input type="date" class="form-control" name="tanggal_bayar" value="' . now()->format('Y-m-d') . '" required>
                                            </div>
                                            <div class="alert alert-info mb-0">Nominal tagihan: <strong>' . rupiah($row->total_harga ?? 0) . '</strong></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Lunaskan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                }

                return [
                    'id' => $row->id_permohonan_uji,
                    'jenis' => 'Kesmas',
                    'jenis_key' => 'kesmas',
                    'nomor' => $row->code_permohonan_uji ?: '-',
                    'nama' => $row->name_customer ?: '-',
                    'tanggal' => $row->created_at,
                    'nominal' => (int) ($row->total_harga ?? 0),
                    'status_key' => $isPaid ? 'lunas' : 'belum_lunas',
                    'status_html' => $statusButton . $modal,
                    'dokumen_html' => $this->printButtons(
                        'kesmas',
                        $row->id_permohonan_uji,
                        $isPaid,
                        $row->nota_diterima_dari ?? ($row->name_customer ?? ''),
                        $row->nota_address_from ?? ($row->address_customer ?? '')
                    ),
                ];
            });
    }

    private function printButtons(string $source, $id, bool $isPaid, string $notaDiterimaDari = '', string $notaAlamat = ''): string
    {
        if ($source === 'klinik') {
            $invoiceUrl = route('elits-persuratan.invoice.klinik', $id);
            $notaUrl = route('elits-persuratan.nota.klinik', $id);
        } else {
            $invoiceUrl = route('elits-persuratan.invoice.kesmas', $id);
            $notaUrl = route('elits-persuratan.nota.kesmas', $id);
        }

        $invoice = '<a class="btn btn-sm btn-info mb-1" target="_blank" href="' . $invoiceUrl . '" title="Cetak Invoice / Tagihan"><i class="fa fa-file-invoice"></i> Invoice</a>';

        $nota = $isPaid
            ? '<a class="btn btn-sm btn-primary mb-1" target="_blank" href="' . $notaUrl . '" title="Cetak Nota / Bukti Pembayaran"><i class="fa fa-file-alt"></i> Nota</a>'
            : '';

        $editNota = '';
        if ($source === 'kesmas') {
            $editNota = '<button type="button" class="btn btn-sm btn-warning mb-1 btn-edit-nota-kesmas"'
                . ' data-id="' . e($id) . '"'
                . ' data-diterima-dari="' . e($notaDiterimaDari) . '"'
                . ' data-alamat="' . e($notaAlamat) . '"'
                . ' title="Edit data penerima pada nota">'
                . '<i class="fa fa-edit"></i> Edit Nota</button>';
        }

        return '<div class="d-flex flex-column align-items-start">' . $invoice . $nota . $editNota . '</div>';
    }
}
