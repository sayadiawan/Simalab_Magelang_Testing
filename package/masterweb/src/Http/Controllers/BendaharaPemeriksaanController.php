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
                'tb_permohonan_uji.terbayar',
                'tb_permohonan_uji.status_pembayaran',
                'tb_permohonan_uji.nota_diterima_dari',
                'tb_permohonan_uji.nota_address_from',
                'tb_permohonan_uji.biaya_tindakan_rectal_swab',
                'tb_permohonan_uji.tanggal_bayar',
                'ms_customer.name_customer',
                'ms_customer.address_customer'
            )
            ->get()
            ->map(function ($row) {
                $isPaid = (int) $row->status_pembayaran === 1;
                $total = (int) ($row->total_harga ?? 0) + (int) ($row->biaya_tindakan_rectal_swab ?? 0);
                $paid = (int) ($row->terbayar ?? 0);
                $sisa = max(0, $total - $paid);
                $isPartial = !$isPaid && $paid > 0;
                $modalId = 'modal-kesmas-payment-' . $row->id_permohonan_uji;

                $statusButton = $isPaid
                    ? '<span class="badge badge-success">Lunas</span>'
                    : '<button type="button" class="btn btn-sm ' . ($isPartial ? 'btn-warning' : 'btn-danger') . '" data-toggle="modal" data-target="#' . $modalId . '">' . ($isPartial ? 'Belum Lunas' : 'Belum Bayar') . '</button>';

                $modal = '';
                if (!$isPaid) {
                    $recipientVal = e($row->nota_diterima_dari ?: ($row->name_customer ?: '-'));
                    $addressVal = e($row->nota_address_from ?: ($row->address_customer ?: '-'));
                    $tglBayarVal = $row->tanggal_bayar ? Carbon::parse($row->tanggal_bayar)->format('Y-m-d') : now()->format('Y-m-d');

                    $partialSection = '';
                    if ($isPartial) {
                        $partialSection = '
                            <div class="row mt-2 pt-2 border-top">
                                <div class="col-6 text-left">
                                    <small class="text-muted d-block"><i class="fa fa-check mr-1 text-success"></i>Sudah Dibayar:</small>
                                    <span class="font-weight-bold text-success">' . rupiah($paid) . '</span>
                                </div>
                                <div class="col-6 text-right">
                                    <small class="text-muted d-block"><i class="fa fa-exclamation-circle mr-1 text-danger"></i>Sisa Tagihan:</small>
                                    <span class="font-weight-bold text-danger">' . rupiah($sisa) . '</span>
                                </div>
                            </div>';
                    }

                    $modal = '
                        <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content text-left" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                    <div class="modal-header" style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; padding: 15px 20px;">
                                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 1.1rem;">
                                            <i class="fa fa-cash-register mr-2"></i> Konfirmasi Pembayaran Kesmas
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="' . route('elits-permohonan-uji.payment', $row->id_permohonan_uji) . '" method="POST">
                                        <div class="modal-body" style="padding: 20px; background-color: #f8f9fa;">
                                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                                            
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-muted small mb-1"><i class="fa fa-user mr-1 text-primary"></i> Pelanggan</label>
                                                <input type="text" class="form-control form-control-sm" value="' . $recipientVal . '" name="recipient-name" required>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-muted small mb-1"><i class="fa fa-map-marker-alt mr-1 text-primary"></i> Alamat</label>
                                                <textarea class="form-control form-control-sm" name="address" rows="2" required>' . $addressVal . '</textarea>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-muted small mb-1"><i class="fa fa-calendar-alt mr-1 text-primary"></i> Tanggal Bayar</label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_bayar" value="' . $tglBayarVal . '" required>
                                            </div>

                                            <div class="card p-3 mb-3 border bg-white" style="border-radius: 8px;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small font-weight-bold">Total Tagihan:</span>
                                                    <span class="font-weight-bold text-dark" style="font-size: 1.05rem;">' . rupiah($total) . '</span>
                                                </div>
                                                ' . $partialSection . '
                                            </div>

                                            <div class="form-group mb-1">
                                                <label class="font-weight-bold text-muted small mb-1"><i class="fa fa-wallet mr-1 text-primary"></i> Nominal Pembayaran yang Disetorkan (Rp)</label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold bg-light">Rp</span>
                                                    </div>
                                                    <input type="number" class="form-control" name="amount" min="1" max="' . $sisa . '" value="' . $sisa . '" placeholder="' . $sisa . '">
                                                </div>
                                                <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;">
                                                    * Klik <strong>Bayar Sebagian</strong> untuk mencatat cicilan/uang muka, atau <strong>Lunaskan</strong> untuk langsung melunasi seluruh tagihan.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-between bg-white" style="padding: 12px 20px;">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                                                <i class="fa fa-times mr-1"></i> Batal
                                            </button>
                                            <div>
                                                <button type="submit" name="payment_submit" value="partial" class="btn btn-warning btn-sm mr-1">
                                                    <i class="fa fa-hourglass-half mr-1"></i> Bayar Sebagian
                                                </button>
                                                <button type="submit" name="payment_submit" value="lunas" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check-circle mr-1"></i> Lunaskan
                                                </button>
                                            </div>
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
                    'nominal' => $total,
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
