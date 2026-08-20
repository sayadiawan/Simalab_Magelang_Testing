<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\Sample;
use Illuminate\Support\Facades\DB;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Container;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\PacketDetail;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\SamplesMethod;
use \Smt\Masterweb\Models\MethodSampling;
use \Smt\Masterweb\Models\LabNum;
use Smt\Masterweb\Models\NomerLabKesmas;
use Smt\Masterweb\Models\NomerLabSequence;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\Wilayah;
use Yajra\Datatables\Datatables;
use Smt\Masterweb\Traits\SampleCodeOrdering;


class LaboratoriumPermohonanUjiManagement extends Controller
{
  use SampleCodeOrdering;
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
    // if (request()->ajax()) {
    //   if (Auth::user()->level == "0e6da765-0f3a-4471-9e1d-6af257e60a70") {
    //     $datas = PermohonanUji::where('tb_permohonan_uji.status', '=', '0')
    //       ->select('tb_permohonan_uji.*')
    //       ->orderBy('created_at', 'DESC')
    //       ->get();
    //   } else {
    //     $datas = PermohonanUji::select('tb_permohonan_uji.*')
    //       ->orderBy('created_at', 'DESC')
    //       ->get();
    //   }

    //   return Datatables::of($datas)
    //     ->filter(function ($instance) use ($request) {
    //       if (!empty($request->get('search'))) {
    //         $instance->collection = $instance->collection->filter(function ($row) use ($request) {
    //           if (Str::contains(Str::lower($row['code_permohonan_uji']), Str::lower($request->get('search')))) {
    //             return true;
    //           } else if (Str::contains(Str::lower($row['customer_permohonan_uji']), Str::lower($request->get('search')))) {
    //             return true;
    //           } else if (Str::contains(Str::lower($row['date_permohonan_uji']), Str::lower($request->get('search')))) {
    //             return true;
    //           } else if (Str::contains(Str::lower($row['status_pembayaran']), Str::lower($request->get('search')))) {
    //             return true;
    //           } else if (Str::contains(Str::lower($row['count_sample_type']), Str::lower($request->get('search')))) {
    //             return true;
    //           }

    //           return false;
    //         });
    //       }
    //     })
    //     ->addColumn('code_permohonan_uji', function ($data) {
    //       $qr = QrCode::size(100)->generate(route('scan.verification', [$data->id_permohonan_uji]));
    //       $code_permohonan_uji = $data->code_permohonan_uji . '<br><br>' . $qr;

    //       return $code_permohonan_uji;
    //     })
    //     ->addColumn('customer_permohonan_uji', function ($data) {
    //       return $data->customer->name_customer;
    //     })
    //     ->addColumn('date_permohonan_uji', function ($data) {
    //       return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_permohonan_uji)->isoFormat('D MMMM Y');;
    //     })
    //     ->addColumn("status_pembayaran", function ($data) {
    //       if ($data->status_pembayaran == 0) {
    //         $get_sample_rectal = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)
    //           ->where('typesample_samples', 'ab516530-aed0-481b-ab9c-86c8ccbcabb3')
    //           ->join('ms_sample_type', function ($join) {
    //             $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
    //               ->whereNull('ms_sample_type.deleted_at')
    //               ->whereNull('tb_samples.deleted_at');
    //           })
    //           ->select(
    //             'name_sample_type',
    //             'id_samples',
    //             'tb_samples.created_at'
    //           )
    //           ->distinct('typesample_samples')
    //           ->latest()
    //           ->get();

    //         if (count($get_sample_rectal) > 0) {
    //           $text = '
    //               <div class="form-group">
    //                   <p>Total permohonan uji: <b>' . rupiah($data->total_harga) . '</b></p>
    //                   <p>Total tambahan biaya tindakan Rectal Swab: <b>' . rupiah(20000) . '</b></p>
    //                   <p>Total yang harus dibayar: <b>' . rupiah($data->total_harga + 20000) . '</b></p>
    //               </div>
    //           ';

    //           $input = '<input type="hidden" class="form-control" name="biaya_tindakan_rectal_swab" value="20000" id="biaya-tindakan-rectal-swab" readonly>';
    //         } else {
    //           $text = '
    //               <div class="form-group">
    //                   <p>Total yang harus dibayar: <b>' . rupiah($data->total_harga) . '</b></p>
    //               </div>
    //           ';

    //           $input = '<input type="hidden" class="form-control" name="biaya_tindakan_rectal_swab" value="0" id="biaya-tindakan-rectal-swab" readonly>';
    //         }

    //         $status_pembayaran = '
    //         <button type="button" class="btn btn-outline-danger" data-toggle="modal" style="padding:0px 10px !important; width:100%;" data-target="#pembayaran_' . $data->id_permohonan_uji . '" >
    //                     Belum Terbayar
    //                 </button>

    //                 <div class="modal fade" id="pembayaran_' . $data->id_permohonan_uji . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    //                     <div class="modal-dialog" role="document">
    //                         <div class="modal-content">
    //                             <div class="modal-header">
    //                                 <h5 class="modal-title" id="exampleModalLabel">Nota Pembayaran</h5>
    //                                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    //                                 <span aria-hidden="true">&times;</span>
    //                                 </button>
    //                             </div>
    //                             <form action="' . route('elits-permohonan-uji.payment', [$data->id_permohonan_uji]) . '" method="POST">

    //                                 <div class="modal-body">

    //                                     ' . $text . '

    //                                     <input type="hidden" name="_token" value="' . csrf_token() . '">

    //                                     <div class="form-group">
    //                                         <label for="recipient-name" class="col-form-label">TELAH DITERIMA DARI :</label>
    //                                         <input type="text" class="form-control" name="recipient-name" value="' . $data->customer->name_customer . '" id="recipient-name">

    //                                         ' . $input . '
    //                                     </div>

    //                                     <div class="form-group">
    //                                         <label for="message-text" class="col-form-label">ALAMAT :</label>
    //                                         <textarea class="form-control" name="address" id="message-text">' . $data->customer->address_customer . '</textarea>
    //                                     </div>

    //                                 </div>
    //                                 <div class="modal-footer">
    //                                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    //                                     <button type="submit" class="btn btn-success" >TERBAYAR</button>
    //                                 </div>
    //                             </form>
    //                         </div>
    //                     </div>
    //                 </div>
    //         ';
    //       } else {
    //         $get_sample_rectal = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)
    //           ->where('typesample_samples', 'ab516530-aed0-481b-ab9c-86c8ccbcabb3')
    //           ->join('ms_sample_type', function ($join) {
    //             $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
    //               ->whereNull('ms_sample_type.deleted_at')
    //               ->whereNull('tb_samples.deleted_at');
    //           })
    //           ->select(
    //             'name_sample_type',
    //             'id_samples',
    //             'tb_samples.created_at'
    //           )
    //           ->distinct('typesample_samples')
    //           ->latest()
    //           ->get();

    //         if (count($get_sample_rectal) > 0) {
    //           $text = '
    //               <div class="form-group">
    //                   <p>Total permohonan uji: <b>' . rupiah($data->total_harga) . '</b></p>
    //                   <p>Total tambahan biaya tindakan Rectal Swab: <b>' . rupiah(20000) . '</b></p>
    //                   <p>Total yang harus dibayar: <b>' . rupiah($data->total_harga + 20000) . '</b></p>
    //               </div>
    //           ';

    //           $input = '<input type="hidden" class="form-control" name="biaya_tindakan_rectal_swab" value="20000" id="biaya-tindakan-rectal-swab" readonly>';
    //         } else {
    //           $text = '
    //               <div class="form-group">
    //                   <p>Total yang harus dibayar: <b>' . rupiah($data->total_harga) . '</b></p>
    //               </div>
    //           ';

    //           $input = '<input type="hidden" class="form-control" name="biaya_tindakan_rectal_swab" value="0" id="biaya-tindakan-rectal-swab" readonly>';
    //         }
    //         $status_pembayaran = '<button type="button" class="btn btn-outline-success" style="padding:0px 10px !important; width:100%;" data-toggle="modal" data-target="#pembayaran_' . $data->id_permohonan_uji . '">Terbayar</button>

    //         <div class="modal fade" id="pembayaran_' . $data->id_permohonan_uji . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    //                     <div class="modal-dialog" role="document">
    //                         <div class="modal-content">
    //                             <div class="modal-header">
    //                                 <h5 class="modal-title" id="exampleModalLabel">Nota Pembayaran</h5>
    //                                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    //                                 <span aria-hidden="true">&times;</span>
    //                                 </button>
    //                             </div>
    //                             <form action="' . route('elits-permohonan-uji.edit_payment', [$data->id_permohonan_uji]) . '" method="POST">

    //                                 <div class="modal-body">

    //                                     ' . $text . '

    //                                     <input type="hidden" name="_token" value="' . csrf_token() . '">

    //                                     <div class="form-group">
    //                                         <label for="recipient-name" class="col-form-label">TELAH DITERIMA DARI :</label>
    //                                         <input type="text" class="form-control" name="recipient-name" value="' . $data->nota_diterima_dari . '" id="recipient-name">

    //                                         ' . $input . '
    //                                     </div>

    //                                     <div class="form-group">
    //                                         <label for="message-text" class="col-form-label">ALAMAT :</label>
    //                                         <textarea class="form-control" name="address" id="message-text">' . $data->nota_address_from . '</textarea>
    //                                     </div>

    //                                 </div>
    //                                 <div class="modal-footer">
    //                                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    //                                     <button type="submit" class="btn btn-success" >TERBAYAR</button>
    //                                 </div>
    //                             </form>
    //                         </div>
    //                     </div>
    //                 </div>
    //         ';
    //       }

    //       $done_sample = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)
    //         ->join('tb_pengesahan_hasil', function ($join) {
    //           $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
    //             ->whereNull('tb_pengesahan_hasil.deleted_at')
    //             ->whereNull('tb_samples.deleted_at');
    //         })->count();
    //       $all_sample = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)->count();

    //       if ($all_sample > 0) {
    //         if ($done_sample >= $all_sample) {
    //           $status_pembayaran = $status_pembayaran . '
    //           <br>
    //           <br>
    //           <button type="submit" class="btn btn-outline-success " style="padding:0px 10px !important;width:100%">Selesai</button>
    //           ';
    //         }
    //       }


    //       return $status_pembayaran;
    //     })
    //     ->addColumn('action', function ($data) {
    //       $readButton = '';
    //       $editButton = '';
    //       $deleteButton = '';

    //       if (getAction('read')) {
    //         $readButton = '<a href="' . route('elits-samples.index', [$data->id_permohonan_uji]) . '" class="dropdown-item" title="Lihat Daftar Sample">Lihat Daftar Sample</a> ';
    //       }

    //       if (getAction('update')) {
    //         $editButton = '<a href="' . route('elits-permohonan-uji.edit', [$data->id_permohonan_uji]) . '" class="dropdown-item" title="Edit">Edit</a> ';
    //       }

    //       if (getAction('delete')) {
    //         $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji  . '" data-nama="' . $data->customer->name_customer . '" title="Hapus">Hapus</a> ';
    //       }

    //       $button = '<div class="dropdown show m-1">
    //                           <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    //                           Aksi
    //                           </a>

    //                           <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
    //                               ' . $readButton . '

    //                               ' . $editButton . '

    //                               ' . $deleteButton . '
    //                           </div>
    //                       </div>';

    //       return $button;
    //     })
    //     ->addColumn('cetak', function ($data) {
    //       $get_sample = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)
    //         ->join('ms_sample_type', function ($join) {
    //           $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
    //             ->whereNull('ms_sample_type.deleted_at')
    //             ->whereNull('tb_samples.deleted_at');
    //         })
    //         ->select(
    //           'name_sample_type',
    //           'id_samples',
    //           'tb_samples.created_at'
    //         )
    //         ->distinct('typesample_samples')
    //         ->latest()
    //         ->get();

    //       // generate button

    //       if (count($get_sample) > 0) {
    //         $cetakButton = '
    //               <div class="dropdown show">
    //                   <a class="btn btn-success dropdown-toggle" href="#" role="button" id="dropdownPrintLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    //                       <i class="fa fa-print" aria-hidden="true"></i> Cetak
    //                   </a>

    //                   <div class="dropdown-menu" aria-labelledby="dropdownPrintLink">

    //                       <a class="dropdown-item" href="' . route('elits-release.permintaan-pemeriksaan', [$data->id_permohonan_uji]) . '" target="__blank">Cetak Permintaan Pemeriksaan</a>
    //                       <a class="dropdown-item" href="' . route('elits-release.nota', [$data->id_permohonan_uji]) . '" target="__blank">Cetak Nota</a>

    //                   </div>
    //               </div>
    //         ';
    //       } else {
    //         $cetakButton = '
    //           <button type="button" class="btn btn-success" disabled>
    //             <i class="fa fa-print" aria-hidden="true"></i> Cetak
    //           </button>
    //         ';
    //       }

    //       return  $cetakButton;
    //     })
    //     ->addColumn('count_sample_type', function ($data) {
    //       $get_sample = Sample::where('permohonan_uji_id', '=', $data->id_permohonan_uji)
    //         ->join('ms_sample_type', function ($join) {
    //           $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
    //             ->whereNull('ms_sample_type.deleted_at')
    //             ->whereNull('tb_samples.deleted_at');
    //         })
    //         ->select(
    //           'name_sample_type',
    //           'tb_samples.typesample_samples'
    //         )
    //         ->distinct('typesample_samples')
    //         ->get();

    //       $prefixSampleType = $sampleType = '';

    //       foreach ($get_sample as $mytable) {
    //         $sampleType .= $prefixSampleType . $mytable['name_sample_type'];
    //         $prefixSampleType = '<br><br>';
    //       }

    //       return $sampleType;
    //     })
    //     ->rawColumns(['code_permohonan_uji', 'customer_permohonan_uji', 'date_permohonan_uji', 'status_pembayaran', 'action', 'cetak', 'count_sample_type'])
    //     ->addIndexColumn() //increment
    //     ->make(true);
    // }

    return view('masterweb::module.admin.laboratorium.permohonan-uji.pagination');
  }

  public function loadDataTableSearch($datas, $search)
  {
    $search = trim((string) $search);
    if ($search === '') {
      return $datas;
    }

    $like = '%' . $search . '%';

    return $datas->where(function ($query) use ($like) {
      $query->where('tb_permohonan_uji.code_permohonan_uji', 'like', $like)
        ->orWhere('ms_customer.name_customer', 'like', $like)
        ->orWhereExists(function ($sub) use ($like) {
          $sub->select(DB::raw(1))
            ->from('tb_samples')
            ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
            ->whereNull('tb_samples.deleted_at')
            ->where('tb_samples.codesample_samples', 'like', $like);
        })
        ->orWhereExists(function ($sub) use ($like) {
          $sub->select(DB::raw(1))
            ->from('tb_samples')
            ->join('ms_sample_type', function ($join) {
              $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                ->whereNull('ms_sample_type.deleted_at');
            })
            ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
            ->whereNull('tb_samples.deleted_at')
            ->where('ms_sample_type.name_sample_type', 'like', $like);
        })
        ->orWhereExists(function ($sub) use ($like) {
          $sub->select(DB::raw(1))
            ->from('tb_samples')
            ->join('ms_packet', function ($join) {
              $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
                ->whereNull('ms_packet.deleted_at');
            })
            ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
            ->whereNull('tb_samples.deleted_at')
            ->whereNotNull('tb_samples.packet_id')
            ->where('ms_packet.name_packet', 'like', $like);
        })
        ->orWhereExists(function ($sub) use ($like) {
          $sub->select(DB::raw(1))
            ->from('tb_samples')
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at');
            })
            ->join('ms_method', function ($join) {
              $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                ->whereNull('ms_method.deleted_at');
            })
            ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
            ->whereNull('tb_samples.deleted_at')
            ->whereNull('tb_samples.packet_id')
            ->where('ms_method.params_method', 'like', $like);
        });
    });
  }

  private function formatPermohonanListCellHtml(array $lines, int $maxVisible = 2): string
  {
    $lines = array_values(array_filter($lines, function ($line) {
      return $line !== null && $line !== '';
    }));

    if (empty($lines)) {
      return '-';
    }

    $fullText = implode(', ', $lines);
    $visibleLines = count($lines) > $maxVisible
      ? array_slice($lines, 0, $maxVisible)
      : $lines;
    $shortText = implode(', ', $visibleLines);
    if (count($lines) > $maxVisible) {
      $shortText .= '…';
    }

    return '<span class="cell-truncate" title="' . e($fullText) . '">' . e($shortText) . '</span>';
  }

  public function loadDataTablePermohonanUji($limit_val, $start, $search, $dateStart = null, $dateEnd = null)
  {
    $totalDataRecord =  PermohonanUji::where('tb_permohonan_uji.status', '=', '0')
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      });
    $totalDataRecord = $this->applyDateSendingFilterOnPermohonan($totalDataRecord, $dateStart, $dateEnd);
    $totalDataRecord = $this->applyPermohonanUjiListOrdering($totalDataRecord);
    $totalDataRecord = $totalDataRecord->distinct('tb_permohonan_uji.id_permohonan_uji');

    $totalDataRecord = $this->loadDataTableSearch($totalDataRecord, $search);
    $totalDataRecord = $totalDataRecord->count();

    $totalFilteredRecord = $totalDataRecord;

    $datas = PermohonanUji::where('tb_permohonan_uji.status', '=', '0')
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select(
        'tb_permohonan_uji.*',
        'ms_customer.*'
      );
    $datas = $this->applyDateSendingFilterOnPermohonan($datas, $dateStart, $dateEnd);
    $datas = $this->applyPermohonanUjiListOrdering($datas);
    $datas = $this->loadDataTableSearch($datas, $search);
    $datas = $datas->distinct('tb_permohonan_uji.id_permohonan_uji');
    if ((int) $limit_val > 0) {
      $datas = $datas->offset($start)->limit((int) $limit_val);
    }
    $datas = $datas->get();

    $no = (int) $start + 1;
    $i = 0;
    foreach ($datas as $data) {
      $datas[$i]["nomer"] = $no;
      $no++;
      $i++;
    }
    $result["totalFilteredRecord"] = $totalFilteredRecord;
    $result["totalDataRecord"] = $totalDataRecord;
    $result["datas"] = $datas;

    return $result;
  }

  public function pagination(Request $request)
  {

    $limit_val = (int) $request->input('length', 10);
    if ($limit_val < 1) {
      $limit_val = 10;
    }
    if ($limit_val > 50) {
      $limit_val = 50;
    }
    $start = (int) $request->input('start', 0);
    $search = $request->input('search', '');
    if (is_array($search)) {
      $search = $search['value'] ?? '';
    }
    $dateStart = $request->input('date_start');
    $dateEnd = $request->input('date_end');

    $result = $this->loadDataTablePermohonanUji($limit_val, $start, $search, $dateStart, $dateEnd);

    // $data_table = Datatables::of($datas)
    // ->addColumn('nomer', function ($data) {
    //   return $data['nomer'];
    // })
    // ->addColumn('name_device_production', function ($data) {
    //   return $data['name_device_production'];
    // })
    // ->addColumn('kode_device', function ($data) {
    //   return $data['kode_device'];
    // })
    $datas = $result["datas"];
    $permohonanIds = collect($datas)->pluck('id_permohonan_uji')->filter()->unique()->values()->all();

    $samplesByPermohonan = collect();
    $sampleTypeRows = collect();
    $nonPacketMethodRows = collect();
    $packetRows = collect();
    $validateRows = collect();

    if (!empty($permohonanIds)) {
      $sampleRows = Sample::whereIn('permohonan_uji_id', $permohonanIds)
        ->whereNull('tb_samples.deleted_at')
        ->get(['id_samples', 'permohonan_uji_id', 'codesample_samples', 'typesample_samples', 'packet_id']);
      $samplesByPermohonan = $sampleRows->groupBy('permohonan_uji_id');

      $sampleTypeRows = Sample::whereIn('tb_samples.permohonan_uji_id', $permohonanIds)
        ->whereNull('tb_samples.deleted_at')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at');
        })
        ->select('tb_samples.permohonan_uji_id', 'tb_samples.typesample_samples', 'ms_sample_type.name_sample_type')
        ->distinct()
        ->get();

      $nonPacketMethodRows = Sample::whereIn('tb_samples.permohonan_uji_id', $permohonanIds)
        ->whereNull('tb_samples.deleted_at')
        ->whereNull('tb_samples.packet_id')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at');
        })
        ->select('tb_samples.permohonan_uji_id', 'ms_method.params_method')
        ->distinct()
        ->get();

      $packetRows = Sample::whereIn('tb_samples.permohonan_uji_id', $permohonanIds)
        ->whereNull('tb_samples.deleted_at')
        ->whereNotNull('tb_samples.packet_id')
        ->join('ms_packet', function ($join) {
          $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
            ->whereNull('ms_packet.deleted_at');
        })
        ->select('tb_samples.permohonan_uji_id', 'ms_packet.name_packet')
        ->distinct()
        ->get();

      $validateRows = Sample::query()
        ->whereIn('tb_samples.permohonan_uji_id', $permohonanIds)
        ->join('tb_verification_activity_samples', function ($join) {
          $join->on('tb_verification_activity_samples.id_sample', '=', 'tb_samples.id_samples')
            ->where('tb_verification_activity_samples.id_verification_activity', '=', 5);
        })
        ->select('tb_samples.permohonan_uji_id', DB::raw('count(*) as total'))
        ->groupBy('tb_samples.permohonan_uji_id')
        ->get();
    }

    $allSampleCountMap = [];
    $hasRectalMap = [];
    foreach ($samplesByPermohonan as $permohonanId => $items) {
      $allSampleCountMap[$permohonanId] = $items->count();
      $hasRectalMap[$permohonanId] = $items->contains(function ($s) {
        return $s->typesample_samples === 'ab516530-aed0-481b-ab9c-86c8ccbcabb3';
      });
    }

    $numSamplesHtmlMap = [];
    foreach ($samplesByPermohonan as $permohonanId => $items) {
      $orderedCodes = $items->pluck('codesample_samples')->filter()->sortByDesc(function ($code) {
        return (string) $code;
      })->values()->all();
      $numSamplesHtmlMap[$permohonanId] = $this->formatPermohonanListCellHtml($orderedCodes, 2);
    }

    $sampleTypeHtmlMap = [];
    $sampleTypeRows->groupBy('permohonan_uji_id')->each(function ($rows, $permohonanId) use (&$sampleTypeHtmlMap) {
      $sampleTypeHtmlMap[$permohonanId] = $this->formatPermohonanListCellHtml(
        $rows->pluck('name_sample_type')->filter()->unique()->values()->all(),
        2
      );
    });

    $pemeriksaanHtmlMap = [];
    foreach ($permohonanIds as $idPermohonan) {
      $nonPacket = $nonPacketMethodRows->where('permohonan_uji_id', $idPermohonan)->pluck('params_method')->filter()->unique()->values();
      $packets = $packetRows->where('permohonan_uji_id', $idPermohonan)->pluck('name_packet')->filter()->unique()->values();
      $pemeriksaanHtmlMap[$idPermohonan] = $this->formatPermohonanListCellHtml(
        $nonPacket->merge($packets)->values()->all(),
        2
      );
    }

    $validateCountMap = [];
    foreach ($validateRows as $vr) {
      $validateCountMap[$vr->permohonan_uji_id] = (int) $vr->total;
    }

    // Status nomor lab: expected (jenis sampel × lab) vs yang sudah di tb_nomer_lab_kesmas
    $nomerLabStatusMap = [];
    if (!empty($permohonanIds)) {
      $expectedComboRows = DB::table('tb_samples as s')
        ->join('tb_sample_method as sm', function ($j) {
          $j->on('sm.sample_id', '=', 's.id_samples')->whereNull('sm.deleted_at');
        })
        ->whereIn('s.permohonan_uji_id', $permohonanIds)
        ->whereNull('s.deleted_at')
        ->whereNotNull('s.typesample_samples')
        ->whereNotNull('sm.laboratorium_id')
        ->select(
          's.permohonan_uji_id',
          's.typesample_samples as sample_type_id',
          'sm.laboratorium_id'
        )
        ->distinct()
        ->get();

      $assignedRows = NomerLabKesmas::whereIn('permohonan_uji_id', $permohonanIds)
        ->whereNotNull('nomer_lab')
        ->where('nomer_lab', '>', 0)
        ->get(['permohonan_uji_id', 'sample_type_id', 'laboratorium_id', 'nomer_lab']);

      $assignedKeys = [];
      foreach ($assignedRows as $ar) {
        $assignedKeys[$ar->permohonan_uji_id][$ar->sample_type_id . '|' . $ar->laboratorium_id] = true;
      }

      $expectedByPermohonan = [];
      foreach ($expectedComboRows as $er) {
        $expectedByPermohonan[$er->permohonan_uji_id][] = $er->sample_type_id . '|' . $er->laboratorium_id;
      }

      foreach ($permohonanIds as $pid) {
        $keys = array_values(array_unique($expectedByPermohonan[$pid] ?? []));
        $expected = count($keys);
        $assigned = 0;
        foreach ($keys as $k) {
          if (!empty($assignedKeys[$pid][$k])) {
            $assigned++;
          }
        }
        $nomerLabStatusMap[$pid] = [
          'expected' => $expected,
          'assigned' => $assigned,
          'missing' => max(0, $expected - $assigned),
        ];
      }
    }

    try {
      $data_table = Datatables::of($datas)
      ->addColumn('pemeriksaan', function ($data) use ($pemeriksaanHtmlMap) {
        return $pemeriksaanHtmlMap[$data->id_permohonan_uji] ?? '';
      })
      ->addColumn('code_permohonan_uji', function ($data) {
        return $data->code_permohonan_uji;
      })
      ->addColumn('customer_permohonan_uji', function ($data) {
        $name = $data->name_customer ?? '-';

        return '<span class="cell-truncate" title="' . e($name) . '">' . e($name) . '</span>';
      })
      ->addColumn('num_samples', function ($data) use ($numSamplesHtmlMap) {
        return $numSamplesHtmlMap[$data->id_permohonan_uji] ?? '-';
        // return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_permohonan_uji)->isoFormat('D MMMM Y');;
      })
      ->addColumn('nomer_lab_status', function ($data) use ($nomerLabStatusMap) {
        $st = $nomerLabStatusMap[$data->id_permohonan_uji] ?? ['expected' => 0, 'assigned' => 0, 'missing' => 0];
        $url = route('elits-permohonan-uji.nomer-lab', [$data->id_permohonan_uji]);
        $expected = (int) $st['expected'];
        $assigned = (int) $st['assigned'];
        $missing = (int) $st['missing'];

        if ($expected === 0) {
          return '<span class="text-muted" title="Belum ada sampel / lab">—</span>';
        }

        if ($missing === 0) {
          return '<a href="' . e($url) . '" class="badge badge-success d-inline-block" title="Semua kombinasi jenis sampel × lab sudah punya nomor lab">'
            . '<i class="fa fa-check"></i> Lengkap (' . $assigned . '/' . $expected . ')</a>';
        }

        if ($assigned === 0) {
          return '<a href="' . e($url) . '" class="badge badge-danger d-inline-block" title="Belum ada nomor lab yang diisi">'
            . '<i class="fa fa-exclamation-circle"></i> Belum diisi</a>';
        }

        return '<a href="' . e($url) . '" class="badge badge-warning d-inline-block text-dark" title="' . $missing . ' kombinasi belum punya nomor lab">'
          . '<i class="fa fa-exclamation-triangle"></i> Kurang ' . $missing . '/' . $expected . '</a>';
      })
      ->addColumn('status_pembayaran', function ($data) use ($hasRectalMap, $validateCountMap, $allSampleCountMap) {
        $parts = [];

        if ((int) $data->is_sampling === 1) {
          $tooltip = '';
          if (!empty($data->alamat_lengkap_sampling)) {
            $fullAddress = $data->alamat_lengkap_sampling;
            if (!empty($data->detail_alamat_sampling)) {
              $fullAddress = $data->detail_alamat_sampling . ', ' . $fullAddress;
            }
            $tooltip = ' title="' . e($fullAddress) . '"';
          }

          $parts[] = '<span class="badge badge-warning d-block mb-1"' . $tooltip . '><i class="fa fa-flask"></i> Sampling Lab</span>';
        }

        $hasRectal = (bool) ($hasRectalMap[$data->id_permohonan_uji] ?? false);
        $biayaRectal = $hasRectal ? 20000 : 0;
        $totalHarusDibayar = (float) $data->total_harga + $biayaRectal;
        $paymentMethod = (int) $data->metode_pembayaran === 0 ? 'Cash' : 'Transfer';
        $tanggalBayar = ($data->tanggal_bayar ?? null)
          ? Carbon::parse($data->tanggal_bayar)->format('Y-m-d')
          : date('Y-m-d');

        if ((int) $data->status_pembayaran === 0) {
          $parts[] = '<button type="button" class="btn btn-outline-danger btn-sm btn-block btn-open-payment"'
            . ' data-id="' . e($data->id_permohonan_uji) . '"'
            . ' data-biaya-rectal="' . $biayaRectal . '"'
            . ' data-total-label="' . e(rupiah($totalHarusDibayar)) . '"'
            . ' data-customer-name="' . e($data->name_customer ?? '') . '"'
            . ' data-customer-address="' . e($data->address_customer ?? '') . '"'
            . ' data-tanggal-bayar="' . e($tanggalBayar) . '"'
            . ' data-metode="' . e($paymentMethod) . '">'
            . '<i class="fa fa-exclamation-circle"></i> ' . e($paymentMethod)
            . '</button>';
        } else {
          $parts[] = '<button type="button" class="btn btn-outline-success btn-sm btn-block" disabled>'
            . '<i class="fa fa-check-circle"></i> Terbayar</button>';
        }

        $validate_sample = (int) ($validateCountMap[$data->id_permohonan_uji] ?? 0);
        $all_sample = (int) ($allSampleCountMap[$data->id_permohonan_uji] ?? 0);
        if ($all_sample > 0 && $validate_sample >= $all_sample) {
          $parts[] = '<span class="badge badge-success d-block mt-1">Selesai</span>';
        }

        return implode('', $parts);
      })
      ->addColumn('action', function ($data) use ($allSampleCountMap) {
        $readButton = '';
        $editButton = '';
        $deleteButton = '';

        if (getAction('read')) {
          $readButton = '<a href="' . route('elits-samples.index', [$data->id_permohonan_uji]) . '" class="dropdown-item" title="Lihat Daftar Sample">Lihat Daftar Sample</a> ';
        }

        $nomerLabButton = '';
        if (getAction('update') || getAction('read')) {
          $nomerLabButton = '<a href="' . route('elits-permohonan-uji.nomer-lab', [$data->id_permohonan_uji]) . '" class="dropdown-item" title="Input Nomor Lab"><i class="fa fa-hashtag text-info"></i> Input Nomor Lab</a> ';
        }

        if (getAction('update')) {
          $editButton = '<a href="' . route('elits-permohonan-uji.edit', [$data->id_permohonan_uji]) . '" class="dropdown-item" title="Edit">Edit</a> ';
        }

        if (getAction('delete')) {
          $customerName = 'N/A';
          if ($data->customer) {
            $customerName = $data->customer->name_customer;
          } elseif (isset($data->name_customer)) {
            $customerName = $data->name_customer;
          }
          $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji  . '" data-nama="' . $customerName . '" title="Hapus">Hapus</a> ';
        }

        // Data untuk tombol cetak
        $sampleCountForAction = (int) ($allSampleCountMap[$data->id_permohonan_uji] ?? 0);

        // Cek apakah sampling oleh lab
        $suratPerintahLink = '';
        if ($data->is_sampling == 1) {
          $suratPerintahLink = '<a class="dropdown-item" href="' . route('elits-permohonan-uji.print-surat-perintah-sampling', [$data->id_permohonan_uji]) . '" target="__blank"><i class="fa fa-file-alt text-warning"></i> Surat Perintah Sampling</a>';
        }

        // Tombol Edit Nota (untuk mengubah "Diterima Dari" dan "Alamat" pada nota)
        $editNotaButton = '<a class="dropdown-item btn-edit-nota" href="#edit-nota"'
          . ' data-id="' . e($data->id_permohonan_uji) . '"'
          . ' data-diterima-dari="' . e($data->nota_diterima_dari ?? ($data->name_customer ?? '')) . '"'
          . ' data-alamat="' . e($data->nota_address_from ?? ($data->address_customer ?? '')) . '"'
          . '><i class="fa fa-edit"></i> Edit Nota</a>';

        if ($sampleCountForAction > 0 || $data->is_sampling == 1) {
          $cetakMenu = '<div class="dropdown-divider"></div>'
            . $suratPerintahLink
            . $editNotaButton
            . '<a class="dropdown-item" href="' . route('elits-persuratan.nota.kesmas', $data->id_permohonan_uji) . '" target="__blank"><i class="fa fa-file-invoice"></i> Cetak Nota</a>'
            . '<a class="dropdown-item" href="' . route('elits-release.formulir-pengambilan-sampel', [$data->id_permohonan_uji]) . '" target="__blank"><i class="fa fa-file-alt"></i> Formulir Pengambilan Sampel</a>'
            . '<a class="dropdown-item" href="' . route('elits-release.print-inform-concern-gabungan', [$data->id_permohonan_uji]) . '" target="__blank"><i class="fa fa-file-signature"></i> Informed Consent</a>'
            . '<a class="dropdown-item" href="' . route('elits-label-permohonan-uji.select-samples', [$data->id_permohonan_uji]) . '" target="__blank"><i class="fa fa-tags"></i> Cetak Label</a>';
        } else {
          $cetakMenu = '<div class="dropdown-divider"></div><span class="dropdown-item disabled text-muted"><i class="fa fa-info-circle"></i> Belum ada sampel</span>';
        }

        return '<div class="dropdown">'
          . '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-cog"></i></button>'
          . '<div class="dropdown-menu dropdown-menu-right">'
          . $readButton
          . $nomerLabButton
          . $editButton
          . $deleteButton
          . $cetakMenu
          . '</div></div>';
      })
      ->addColumn('count_sample_type', function ($data) use ($sampleTypeHtmlMap) {
        return $sampleTypeHtmlMap[$data->id_permohonan_uji] ?? '';
      })
      ->rawColumns(['code_permohonan_uji', 'customer_permohonan_uji','pemeriksaan', 'num_samples', 'nomer_lab_status', 'status_pembayaran', 'action', 'cetak', 'count_sample_type'])
      ->setFilteredRecords($result["totalFilteredRecord"])
      ->setTotalRecords($result["totalDataRecord"])
      ->skipPaging()
      ->make(true);
      return $data_table;
    } catch (\Throwable $e) {
      \Log::error('Failed to build permohonan-uji pagination datatable', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
      ]);

      // Fallback JSON so DataTables keeps rendering instead of total failure.
      $fallbackRows = collect($datas)->map(function ($data) {
        return [
          'nomer' => $data->nomer ?? '-',
          'code_permohonan_uji' => $data->code_permohonan_uji ?? '-',
          'customer_permohonan_uji' => $data->name_customer ?? '-',
          'pemeriksaan' => '-',
          'num_samples' => '-',
          'count_sample_type' => '-',
          'nomer_lab_status' => '-',
          'status_pembayaran' => '-',
          'action' => '-',
        ];
      })->values();

      return response()->json([
        'draw' => (int) $request->input('draw'),
        'recordsTotal' => (int) ($result['totalDataRecord'] ?? 0),
        'recordsFiltered' => (int) ($result['totalFilteredRecord'] ?? 0),
        'data' => $fallbackRows,
      ]);
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $count = PermohonanUji::where(DB::raw('YEAR(date_permohonan_uji)'), '=', date('Y'))->max('urutan_permohonan_uji');
    $code = 'PU.NK/' . date("Ymd", time()) . '/' . str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);
    $petugasPenerima = $this->getPetugasAdministrasi();

    return view('masterweb::module.admin.laboratorium.permohonan-uji.add', compact('code', 'petugasPenerima'));
  }

  /**
   * Get petugas administrasi/pendaftaran from:
   * 1. ms_verification_activities register column
   * 2. ms_petugas yang memiliki role dengan id "1" (Pendaftaran/Registrasi)
   */
  private function getPetugasAdministrasi()
  {
    $petugasPenerima = [];

    // 1. Get from ms_verification_activities register column
    $verificationActivities = VerificationActivity::all();
    foreach ($verificationActivities as $activity) {
      if (!empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
        // Parse names from register column (comma-separated)
        // Names are stored as "Name1, Name2, Name3" format
        $names = explode(', ', $activity->register);
        foreach ($names as $name) {
          $name = trim($name);
          if (!empty($name) && !in_array($name, $petugasPenerima)) {
            $petugasPenerima[] = $name;
          }
        }
      }
    }

    // 2. Get from ms_petugas yang memiliki role dengan id "1" (Pendaftaran/Registrasi)
    // Meskipun petugas juga memiliki role lain (misalnya role "6" untuk pengambil sample),
    // tetap dianggap sebagai petugas pendaftaran jika memiliki role "1"
    $petugasWithRole1 = \Smt\Masterweb\Models\Petugas::whereNotNull('role')->get();
    foreach ($petugasWithRole1 as $petugas) {
      $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
      if (is_array($roles) && in_array('1', $roles)) {
        // Petugas memiliki role "1" (Pendaftaran/Registrasi)
        $nama = trim($petugas->nama);
        if (!empty($nama) && !in_array($nama, $petugasPenerima)) {
          $petugasPenerima[] = $nama;
        }
      }
    }

    // Sort alphabetically
    sort($petugasPenerima);

    return $petugasPenerima;
  }




  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store2(Request $request)
  {
    $data = $request->all();

    // print_r($data);
    $validated = $request->validate([
      'code_permohonan_uji' => ['required'],
      'issend_samples' => ['required'],
      'customerAttributes' => ['required'],
      'name_personil' => ['required'],
      // 'date_done_estimation' => ['required'],
      'count_sample' => ['required'],
      'total_price_sample' => ['required'],
      'underpayment_sample' => ['required'],
      'down_payment_sample' => ['required'],
      'payment_samples' => ['required'],

    ]);

    $user = Auth()->user();
    $permohonan_uji = new PermohonanUji;

    $count = PermohonanUji::where(DB::raw('YEAR(date_permohonan_uji)'), '=', date('Y'))->max('urutan_permohonan_uji');

    $code = 'PU.NK/' . Carbon::createFromFormat('d/m/Y', $data["date"])->format('Ymd') . '/' . str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);

    //uuid
    $id_permohonan_uji = Uuid::uuid4()->toString();

    $permohonan_uji->id_permohonan_uji  = $id_permohonan_uji;
    $permohonan_uji->code_permohonan_uji = $code;
    $permohonan_uji->customer_id = $data["customerAttributes"];
    $permohonan_uji->name_personil = $data["name_personil"];

    if ($data["issend_samples"] != "on") {
      $permohonan_uji->pengambil_sample = 0;
      $permohonan_uji->date_get_sample = Carbon::createFromFormat('d/m/Y', $data["date_get_sample"])->format('Y-m-d H:i:s');
      $permohonan_uji->sample_condition = $data["condition_samples"];
      if ($data["condition_samples"] == '0') {
        $permohonan_uji->sample_condition_other = $data["condition_samples_others"];
      }
    } else {
      $permohonan_uji->pengambil_sample = 1;
      $permohonan_uji->date_sampling =  Carbon::createFromFormat('d/m/Y', $data["datesampling_samples"])->format('Y-m-d H:i:s');
      $permohonan_uji->packet_sampling = $data["packet_samples_sampling"];
      $permohonan_uji->status = 4;
    }
    $permohonan_uji->total_harga = $data["total_price_sample"];
    $permohonan_uji->uang_muka = $data["down_payment_sample"];
    $permohonan_uji->uang_muka = $data["down_payment_sample"];
    $permohonan_uji->sisa = $data["underpayment_sample"];
    // // $permohonan_uji->date_done_estimation = Carbon::createFromFormat('d/m/Y', $data["date_done_estimation"])->format('Y-m-d H:i:s');
    $permohonan_uji->pembayaran = $data["payment_samples"];
    if ($data["payment_samples"] == '0') {
      $permohonan_uji->pembayaran_lain = $data["payment_samples_others"];
    }

    $permohonan_uji->catatan = $data["note_sample"];

    if ($data["subcontract_samples"] == "true") {
      $permohonan_uji->subkontrak = 1;
      $permohonan_uji->name_subkontrak = $data["subcontract_name_samples"];
    } else {
      $permohonan_uji->subkontrak = 0;
    }



    // $permohonan_uji->date_done_estimation = $data["date_done_estimation"];

    $permohonan_uji->save();


    if ($data["issend_samples"] != "on") {


      foreach ($data["Allsamples"] as $sample_value) {
        $sample = new Sample;
        $uuid4 = Uuid::uuid4();


        // $datesampling_samples = Carbon::createFromFormat('d/m/Y', $sample_value['datesampling_samples'])->format('Y-m-d H:i:s');
        // $datelab_samples = Carbon::createFromFormat('d/m/Y', $sample_value['datesampling_samples'])->format('Y-m-d H:i:s');

        $sample->id_samples = $uuid4->toString();
        $sample->codesample_samples = $sample_value['code_samples'];
        $sample->code_sample_user = $sample_value['code_samples_user'];
        $sample->customer_samples =  $permohonan_uji->customer_id;
        $sample->permohonan_uji_id =  $permohonan_uji->id_permohonan_uji;
        $sample->issend_samples =   $permohonan_uji->pengambil_sample;
        $sample->packet_id = $sample_value['packet_samples'];
        $sample->cost_samples = $sample_value['cost_samples'];
        $sample->typesample_samples = $sample_value['typesample_samples'];
        $sample->wadah_samples = $sample_value['wadah_samples'];
        if ($sample->wadah_samples == '0') {
          $sample->wadah_samples_others = $sample_value['wadah_samples_others'];
        }
        $sample->weight_samples = $sample_value['weight_samples'];
        $sample->unit_samples = $sample_value['unitAttributes'];
        $sample->codenumber_samples = $sample_value['codenumber_samples'];
        $sample->typesample_samples = $sample_value['typesample_samples'];
        $sample->datelab_samples = $permohonan_uji->date_get_sample;
        $sample->save();


        if (isset($sample_value["packet_samples_others"]) && count($sample_value["packet_samples_others"]) > 0) {
          for ($i = 0; $i < count($sample_value["packet_samples_others"]); $i++) {
            $method = new SamplesMethod;
            $method->id = Uuid::uuid4();
            $method->id_samples = $sample->id_samples;
            $method->id_method = $sample_value["packet_samples_others"][$i];
            $method->save();
          }
        } else {
          // $packets = Packet::join('ms_packet_detail', function ($join) {
          //     $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
          //     ->whereNull('ms_packet_detail.deleted_at')
          //     ->whereNull('ms_packet.deleted_at');
          // })
          // ->where('ms_packet.id_packet','=', $sample->packet_id )
          // ->get();

          $packet_samples_selected = $sample_value["packet_samples_selected"];
          foreach ($packet_samples_selected as $method_id) {
            $method = new SamplesMethod;
            $method->id = Uuid::uuid4();
            $method->id_samples = $sample->id_samples;
            $method->id_method = $method_id;
            $method->save();
          }
        }
      }
    } else {


      if ($data["packet_samples_sampling"] == '0') {
        foreach ($data["methodAttributes_sampling"] as $methodsampling_value) {
          $methodSampling = new MethodSampling;
          $uuid4 = Uuid::uuid4();
          $methodSampling->id_method_sampling = $uuid4;
          $methodSampling->permohonan_uji_id = $permohonan_uji->id_permohonan_uji;
          $methodSampling->method_id = $methodsampling_value;
          $methodSampling->save();
        }
      } else {
        foreach ($data["methodAttributes_sampling"] as $methodsampling_value) {
          $methodSampling = new MethodSampling;
          $uuid4 = Uuid::uuid4();
          $methodSampling->id_method_sampling = $uuid4;
          $methodSampling->permohonan_uji_id = $permohonan_uji->id_permohonan_uji;
          $methodSampling->method_id = $methodsampling_value;
          $methodSampling->save();
        }
      }
    }





    // $user = Auth()->user();
    // $sample = new Sample;
    // //uuid
    // $uuid4 = Uuid::uuid4();

    // $datesampling_samples = Carbon::createFromFormat('d/m/Y', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
    // $datelab_samples = Carbon::createFromFormat('d/m/Y', $request->post('datelab_samples'))->format('Y-m-d H:i:s');

    // $sample->id_samples = $uuid4->toString();
    // $sample->codesample_samples = $request->post('code_samples');
    // $sample->customer_samples = $request->post('customerAttributes');
    // if($request->post('packet_samples')!="0"){
    //     $sample->packet_id = $request->post('packet_samples');
    // }

    // if(!$request->post('issend_samples')=="on"){
    //     $sample->sender_samples = $request->post('sender_samples');
    //     $sample->issend_samples = 0;
    // }else{
    //     $sample->issend_samples = 1;
    // }
    // $sample->cost_samples = $request->post('cost_samples');
    // $sample->typesample_samples = $request->post('typesample_samples');
    // $sample->user_input_samples = $user->id;
    // $sample->wadah_samples = $request->post('wadah_samples');
    // if($sample->wadah_samples=='0'){
    //     $sample->wadah_samples_others = $request->post('wadah_samples_others');
    // }

    // $sample->weight_samples = $request->post('weight_samples');
    // $sample->unit_samples = $request->post('unitAttributes');
    // $sample->codenumber_samples = $request->post('codenumber_samples');
    // $sample->typesample_samples = $request->post('typesample_samples');
    // $sample->poinsample_lat_samples = $request->post('poinsample_lat_samples');
    // $sample->poinsample_long_samples = $request->post('poinsample_long_samples');
    // $sample->poinsample_samples = $request->post('poinsample_samples');
    // $sample->datesampling_samples = $datesampling_samples;
    // $sample->datelab_samples = $datelab_samples;

    //  $sample->save();


    //  if(isset ($data["packet_samples_others"]) && count($data["packet_samples_others"])>0){
    //     for($i = 0; $i < count($data["packet_samples_others"]);$i++ ) {
    //         $method = new SamplesMethod;
    //         $method->id= Uuid::uuid4();
    //         $method->id_samples=$sample->id_samples;
    //         $method->id_method=$data["packet_samples_others"][$i];
    //         $method->save();
    //     }
    // }else{
    //     $packets = Packet::join('ms_packet_detail', function ($join) {
    //         $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
    //         ->whereNull('ms_packet_detail.deleted_at')
    //         ->whereNull('ms_packet.deleted_at');
    //     })
    //     ->where('ms_packet.id_packet','=', $sample->packet_id )
    //     ->get();
    //     foreach ($packets as $packet) {
    //         $method = new SamplesMethod;
    //         $method->id= Uuid::uuid4();
    //         $method->id_samples=$sample->id_samples;
    //         $method->id_method=$packet->method_id;
    //         $method->save();
    //     }
    // }


    return response()->json(
      [
        'success' => 'Ajax request submitted successfully',
        'data' => $data,
      ]
    );
    //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);

  }

  public function store(Request $request)
  {
    $data = $request->all();



    $validated = $request->validate([
      'code_permohonan_uji' => ['required'],
    ]);

    $user = Auth()->user();
    $count = PermohonanUji::where(DB::raw('YEAR(date_permohonan_uji)'), '=', date('Y'))->max('urutan_permohonan_uji');

    $code = 'PU.NK/' . Carbon::createFromFormat('d/m/Y', $data["date"])->format('Ymd') . '/' . str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);

    if (isset($data["new_customer"])) {
      $customer = new Customer;
      //uuid
      $uuid4 = Uuid::uuid4();


      $customer->id_customer = $uuid4->toString();
      $customer->name_customer = $request->post('new_customer');
      $customer->address_customer = $request->post('new_address_customer');
      $customer->email_customer = $request->post('new_email_customer');
      // $customer->category_customer = $request->post('new_category_customer');
      $customer->cp_customer = $request->post('new_cp_customer');
      $kecamatan = $request->post('new_kecamatan');
      if (!empty($kecamatan) && $kecamatan != '0') {
        $customer->kecamatan_customer = $kecamatan;
      } elseif ($request->filled('new_kecamatan_other')) {
        $customer->kecamatan_customer = $request->post('new_kecamatan_other');
      } else {
        $customer->kecamatan_customer = null;
      }
      $customer->save();
    }

    $permohonan_uji = new PermohonanUji;
    //uuid
    $id_permohonan_uji = Uuid::uuid4()->toString();

    $permohonan_uji_urutan = PermohonanUji::where(DB::raw('YEAR(date_permohonan_uji)'), '=', date('Y'))->max('urutan_permohonan_uji');

    $permohonan_uji->id_permohonan_uji  = $id_permohonan_uji;
    $permohonan_uji->code_permohonan_uji = $code;
    $permohonan_uji->pengirim_sample = $data["pengirim_sample"] ?? null;
    $permohonan_uji->pdam_pengirim_sample = '';
    $permohonan_uji->petugas_penerima = $data["petugas_penerima"];
    $permohonan_uji->is_sampling = $data["is_sampling"] ?? 1;

    // Save wilayah sampling if laboratory sampling is selected
    if (isset($data["is_sampling"]) && $data["is_sampling"] == 1) {
      $permohonan_uji->provinsi_sampling = $data["provinsi_sampling"] ?? null;
      $permohonan_uji->kabupaten_sampling = $data["kabupaten_sampling"] ?? null;
      $permohonan_uji->kecamatan_sampling = $data["kecamatan_sampling"] ?? null;
      $permohonan_uji->desa_sampling = $data["desa_sampling"] ?? null;

      // Save text versions of wilayah names
      $alamat_parts = [];

      if (isset($data["provinsi_sampling"]) && $data["provinsi_sampling"]) {
        $provinsi = Wilayah::find($data["provinsi_sampling"]);
        if ($provinsi) {
          $permohonan_uji->provinsi_sampling_text = $provinsi->wilayah;
          $alamat_parts[] = $provinsi->wilayah;
        }
      }

      if (isset($data["kabupaten_sampling"]) && $data["kabupaten_sampling"]) {
        $kabupaten = Wilayah::find($data["kabupaten_sampling"]);
        if ($kabupaten) {
          $permohonan_uji->kabupaten_sampling_text = $kabupaten->wilayah;
          array_unshift($alamat_parts, $kabupaten->wilayah);
        }
      }

      if (isset($data["kecamatan_sampling"]) && $data["kecamatan_sampling"]) {
        $kecamatan = Wilayah::find($data["kecamatan_sampling"]);
        if ($kecamatan) {
          $permohonan_uji->kecamatan_sampling_text = $kecamatan->wilayah;
          array_unshift($alamat_parts, $kecamatan->wilayah);
        }
      }

      if (isset($data["desa_sampling"]) && $data["desa_sampling"]) {
        $desa = Wilayah::find($data["desa_sampling"]);
        if ($desa) {
          $permohonan_uji->desa_sampling_text = $desa->wilayah;
          array_unshift($alamat_parts, $desa->wilayah);
        }
      }

      // Save complete address
      if (!empty($alamat_parts)) {
        $permohonan_uji->alamat_lengkap_sampling = implode(', ', $alamat_parts);
      }

      // Save detail address (jalan, RT/RW, etc)
      if (isset($data["detail_alamat_sampling"]) && !empty($data["detail_alamat_sampling"])) {
        $permohonan_uji->detail_alamat_sampling = $data["detail_alamat_sampling"];
      }
    }

    $permohonan_uji->location_permohonan_uji = '';
    $permohonan_uji->urutan_permohonan_uji = $permohonan_uji_urutan + 1;
//    $permohonan_uji->address_customer_edited = $data["alamat_customer_edited"];
    // $permohonan_uji->date_done_estimation = Carbon::createFromFormat('d/m/Y', $data["date_done"])->format('Y-m-d H:i:s');

    //metode pembayaran
    $permohonan_uji->metode_pembayaran = $data["metode_pembayaran"];

    if (isset($data["customer"])) {
      $permohonan_uji->customer_id = $data["customer"];
    } else {
      $permohonan_uji->customer_id = $customer->id_customer;
    }
    $permohonan_uji->catatan = $data["catatan"];
    $permohonan_uji->name_sampling = $data["name_sampling"] ?? null;
    $permohonan_uji->date_permohonan_uji = Carbon::createFromFormat('d/m/Y', $data["date"])->format('Y-m-d H:i:s');

    $save = $permohonan_uji->save();



    if ($save == true) {
      return response()->json([
        'status' => true,
        'pesan' => "Data permohonan uji berhasil ditambahkan!",
        'id' => $permohonan_uji->id_permohonan_uji,
        'is_sampling' => $permohonan_uji->is_sampling
      ], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data permohonan uji tidak berhasil ditambahkan!"], 400);
    }
    // return redirect()->route('elits-samples.create', [$permohonan_uji->id_permohonan_uji])->with(['status' => 'Permohonan Uji Berhasil di input']);
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
    $user = Auth()->user();
    $item_permohonan_uji = PermohonanUji::findOrFail($id);
    $users = User::all();

    $packets = Packet::where('id_packet', '!=', '0')->orderBy('created_at')->get();

    $containers = Container::where('id_container', '!=', '0')->get();
    $sampletypes = SampleType::orderBy('created_at')->get();


    return view('masterweb::module.admin.laboratorium.permohonan-uji.edit', [
      'user' => $user,
      'item_permohonan_uji' => $item_permohonan_uji,
      'users' => $users,
      'packets' => $packets,
      'containers' => $containers,
      'sampletypes' => $sampletypes
    ]);
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
    // dd($request);

    DB::beginTransaction();

    $post = PermohonanUji::findOrFail($id);
    // dd($request->post('date'));
    $code = 'PU.NK/' . Carbon::createFromFormat('d/m/Y', $request->post('date'))->format('Ymd') . '/' . str_pad((int)($post->urutan_permohonan_uji), 4, '0', STR_PAD_LEFT);

    $post->customer_id = $request->customer;

    $post->date_permohonan_uji = Carbon::createFromFormat('d/m/Y', $request->post('date'))->format('Y-m-d H:i:s');
    // $post->date_done_estimation = Carbon::createFromFormat('d/m/Y', $request->post('date_done'))->format('Y-m-d H:i:s');
    $post->code_permohonan_uji = $code;
    $post->pdam_pengirim_sample = $request->post("pdam_pengirim_sample");
    // $post->pengirim_sample = $request->post('pengirim_sample');
    $post->petugas_penerima = $request->post('petugas_penerima');
    $post->name_sampling = $request->post('name_sampling');
//    $post->address_customer_edited = $request->post('alamat_customer_edited');

    // $post->location_permohonan_uji = $request->post('location_permohonan_uji');
    $post->catatan = $request->post('catatan');
    $post->pengirim_sample = $request->post('pengirim_sample', null);
    $post->nota_diterima_dari = $request->post('pengirim_sample', null);

    try {
      $simpan = $post->save();

      DB::commit();

      if ($simpan == true) {
        return response()->json(['status' => true, 'pesan' => "Data permohonan uji berhasil diubah!"], 200);
      } else {
        return response()->json(['status' => false, 'pesan' => "Data permohonan uji tidak berhasil diubah!"], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => 'System gagal melakukan perubahan!'], 200);
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
    $data = PermohonanUji::findOrFail($id);
    $data->delete();
    $sample = Sample::where('permohonan_uji_id', $id);
    $sample->delete();



    // $all_sample = Sample::orderBy('count_id','ASC')->get();

    // dd($all_sample);

    // $i=1;
    // foreach ($all_sample as $sample) {
    //   # code...
    //   $sample->count_id=$i;
    //   $sample->count_id=$i;
    //   $sample->save();
    //   $i++;
    // }

    // $all_permohonan_uji = PermohonanUji::orderBy('urutan_permohonan_uji','ASC')->get();
    // $i=1;
    // foreach ($all_permohonan_uji as $permohonan_uji) {
    //   # code...
    //   $permohonan_uji->urutan_permohonan_uji=$i;
    //   $permohonan_uji->save();
    //   $i++;
    // }

    $lab_num = LabNum::where('permohonan_uji_id', $id);


    $lab_num_temp = LabNum::where('permohonan_uji_id', $id)->select('lab_id')->distinct('lab_id')->get();
    $lab_num->delete();

    foreach ($lab_num_temp as $lab){
      // sortingNumber($lab->lab_id, $lab->lab_number);
    }


    if ($data == true) {
      return response()->json(['status' => true, 'pesan' => "Data permohonan uji berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data permohonan uji tidak berhasil dihapus!"], 200);
    }
  }

  public function print($id)
  {
  }

  public function printSuratPerintahSampling($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)
      ->with('customer')
      ->first();

    if (!$permohonan_uji) {
      return redirect()->back()->with('error', 'Data permohonan uji tidak ditemukan');
    }

    // Get wilayah data if exists
    $wilayah_data = [];
    if ($permohonan_uji->provinsi_sampling) {
      $wilayah_data['provinsi'] = $permohonan_uji->provinsi_sampling_text ?? '';
      $wilayah_data['kabupaten'] = $permohonan_uji->kabupaten_sampling_text ?? '';
      $wilayah_data['kecamatan'] = $permohonan_uji->kecamatan_sampling_text ?? '';
      $wilayah_data['desa'] = $permohonan_uji->desa_sampling_text ?? '';
      $wilayah_data['detail_alamat'] = $permohonan_uji->detail_alamat_sampling ?? '';
      $wilayah_data['alamat_lengkap'] = $permohonan_uji->alamat_lengkap_sampling ?? '';
    }

    // Generate QR Code URL for mobile sampling
    // $samplingUrl = route('mobile.sampling.index', ['id' => $permohonan_uji->id_permohonan_uji]);

    // Generate QR Code using Endroid QR Code library (using GD)
    try {
      $qrCodeWriter = new \Endroid\QrCode\Writer\PngWriter();
      $qrCodeObject = \Endroid\QrCode\QrCode::create($permohonan_uji->id_permohonan_uji)
        ->setSize(150)
        ->setMargin(10);

      $result = $qrCodeWriter->write($qrCodeObject);
      $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());
    } catch (\Exception $e) {
      // Fallback: create simple text if QR generation fails
      $qrCodeBase64 = null;
    }

    $pdf = \PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.print-surat-perintah-sampling', compact('permohonan_uji', 'wilayah_data', 'qrCodeBase64'));
    $pdf->setPaper('A4', 'portrait');

    return $pdf->stream('Surat-Perintah-Sampling-' . $permohonan_uji->code_permohonan_uji . '.pdf');
  }

  public function edit_payment($id, Request $request)
  {
    $data = $request->all();
    $user = Auth()->user();

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();
    $permohonan_uji->nota_diterima_dari = $data['recipient-name'];
    $permohonan_uji->nota_address_from = $data['address'];
    if ($request->filled('tanggal_bayar')) {
      $permohonan_uji->tanggal_bayar = Carbon::parse($request->tanggal_bayar)->format('Y-m-d');
    }

    $status = '';
    if (isset($request->amount)){
      $permohonan_uji->terbayar += $request->amount;
      $permohonan_uji->status_pembayaran = '0';
      $status = 'Pembayaran berhasil disimpan';
    }else{
      $permohonan_uji->status_pembayaran = '1';
      $status = 'Pembayaran Selesai';
    }

    $permohonan_uji->save();
    return redirect()->route('elits-permohonan-uji.index')->with(['status' => $status]);
  }

  public function editNota($id, Request $request)
  {
    $request->validate([
      'nota_diterima_dari' => 'nullable|string|max:255',
      'nota_address_from' => 'nullable|string',
    ]);

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();

    if (!$permohonan_uji) {
      return redirect()->route('elits-permohonan-uji.index')->with(['status' => 'Data permohonan uji tidak ditemukan']);
    }

    $permohonan_uji->nota_diterima_dari = $request->input('nota_diterima_dari');
    $permohonan_uji->nota_address_from = $request->input('nota_address_from');
    $permohonan_uji->save();

    return redirect()->route('elits-permohonan-uji.index')->with(['status' => 'Nota berhasil diperbarui']);
  }

  public function payment($id, Request $request)
  {
    $data = $request->all();
    $user = Auth()->user();
    $max_nota = (int)PermohonanUji::where(DB::raw('YEAR(date_permohonan_uji)'), '=', date('Y'))->max('nomor_nota');

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();
    $permohonan_uji->nomor_nota = $max_nota + 1;
    $permohonan_uji->nota_diterima_dari = $data['recipient-name'];
    $permohonan_uji->nota_petugas_penerima = $user->id;
    $permohonan_uji->nota_address_from = $data['address'];
    if ($request->filled('tanggal_bayar')) {
      $permohonan_uji->tanggal_bayar = Carbon::parse($request->tanggal_bayar)->format('Y-m-d');
    } else if (empty($permohonan_uji->tanggal_bayar)) {
      $permohonan_uji->tanggal_bayar = Carbon::now()->format('Y-m-d');
    }

    if ($request->biaya_tindakan_rectal_swab != 0) {
      $permohonan_uji->biaya_tindakan_rectal_swab = $request->biaya_tindakan_rectal_swab;
    } else {
      $permohonan_uji->biaya_tindakan_rectal_swab = null;
    }

    $rectalFee = (float) ($request->input('biaya_tindakan_rectal_swab', 0) ?: 0);
    $totalWajib = (float) $permohonan_uji->total_harga + $rectalFee;

    $status = '';
    $submit = $request->input('payment_submit');

    if ($submit === 'partial') {
      if ($request->filled('amount') && is_numeric($request->input('amount')) && (float) $request->input('amount') > 0) {
        $permohonan_uji->terbayar += (float) $request->input('amount');
      }
      $permohonan_uji->status_pembayaran = '0';
      $status = 'Pembayaran berhasil disimpan';
    } elseif ($submit === 'lunas') {
      $permohonan_uji->terbayar = $totalWajib;
      $permohonan_uji->status_pembayaran = '1';
      $status = 'Pembayaran Selesai';
    } else {
      // Form lama (satu tombol "Konfirmasi Pembayaran" tanpa payment_submit / tanpa field amount)
      if ($request->filled('amount') && is_numeric($request->input('amount')) && (float) $request->input('amount') > 0) {
        $permohonan_uji->terbayar += (float) $request->input('amount');
        $permohonan_uji->status_pembayaran = '0';
        $status = 'Pembayaran berhasil disimpan';
      } else {
        $permohonan_uji->terbayar = $totalWajib > 0 ? $totalWajib : $permohonan_uji->terbayar;
        $permohonan_uji->status_pembayaran = '1';
        $status = 'Pembayaran Selesai';
      }
    }
    $permohonan_uji->save();

    return redirect()->route('elits-permohonan-uji.index')->with(['status' => $status]);
    //get auth user

    //get all menu public
  }

  public function analys($id, $id_method = null)
  {


    $auth = Auth()->user();

    $samples = Sample::where("id_samples", $id)->first();
    $customer = Customer::where("id_customer", $samples->customer_samples)->first();


    if ($auth->level == "3382abf2-8518-42f9-91e1-096f25da8ae8") {
      $method = SamplesMethod::join('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
        ->join('tb_delegation', function ($join) {
          $join->on('tb_delegation.id_method', '=', 'ms_samples_method.id_method');
          $join->on('tb_delegation.id_samples', '=', 'ms_samples_method.id_samples');
        })
        ->where("ms_samples_method.id_samples", $id)
        ->where('tb_delegation.id_delegation', '=', $auth->id)
        ->where('ms_method.deleted_at', '=', null)
        ->where('tb_delegation.deleted_at', '=', null)
        ->select('ms_samples_method.*', 'ms_method.*', 'tb_delegation.*')
        ->get();
    } else {
      $method = SamplesMethod::where("id_samples", $id)
        ->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
        ->where('ms_method.deleted_at', '=', null)
        ->get();
    }



    return view('masterweb::module.admin.laboratorium.permohonan-uji.analys', compact('samples', 'method', 'customer', 'id_method'));
  }

  public function setPersiapanSample($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();
    $permohonan_uji->status = 1;
    $permohonan_uji->date_sampling_prepare = Carbon::now()->format('Y-m-d H:i:s');
    $permohonan_uji->save();

    return redirect()->route('elits-permohonan-uji.index')->with(['status' => 'Proses Persiapan Sampel selesai']);
  }

  public function setSampling($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();
    $permohonan_uji->status = 2;
    $permohonan_uji->date_sampling_done = Carbon::now()->format('Y-m-d H:i:s');
    $permohonan_uji->save();

    return redirect()->route('elits-permohonan-uji.index')->with(['status' => 'Proses Persiapan Sampel selesai']);
  }

  public function getPacketDetail($id)
  {
    if (isset($id)) {
      $packet_sample_detail = PacketDetail::where('packet_id', $id)
        ->join('ms_method', function ($join) {
          $join->on('ms_packet_detail.method_id', '=', 'ms_method.id_method')
            ->whereNull('ms_packet_detail.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })->get();
    } else {
      $packet_sample_detail = null;
    }

    return response()->json(array('success' => true, 'packet_sample_detail' => $packet_sample_detail));

    # code...
  }

  public function getIdSample($id)
  {
    $user = Auth()->user();
    $level = $user->getlevel;


    $packet_sample = SampleType::where('id_sample_type', $id)->join('ms_packet', function ($join) {
      $join->on('ms_sample_type.packet_id', '=', 'ms_packet.id_packet')
        ->whereNull('ms_sample_type.deleted_at')
        ->whereNull('ms_packet.deleted_at');
    })
      ->first();
    if (isset($packet_sample->id_packet)) {
      $packet_sample_detail = PacketDetail::where('packet_id', $packet_sample->id_packet)
        ->join('ms_method', function ($join) {
          $join->on('ms_packet_detail.method_id', '=', 'ms_method.id_method')
            ->whereNull('ms_packet_detail.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })->get();
    } else {
      $packet_sample_detail = null;
    }
    // dd($packet_sample_detail);



    if ($level->level == "elits-dev" || $level->level == "LAB") {

      $samplecount = Sample::max('tb_samples.codenumber_samples');
      $samplecount = $samplecount + 1;
    } else {
      return response()->json([
        'success' => 'false',
        'errors'  => "Cannot Access",
      ], 400);
    }

    return response()->json(array('success' => true, 'samplecount' =>  $samplecount, 'packet_sample' => $packet_sample, 'packet_sample_detail' => $packet_sample_detail));
  }

  public function printLHU($id)
  {
    // dd("ygygyu");
    $sample = Sample::findOrFail($id);

    $customer = Customer::findOrFail($sample->customer_samples);
    $category = Industry::findOrFail($customer->category_customer);
    return view('masterweb::module.admin.laboratorium.permohonan-uji.printLHU', compact('sample', 'customer', 'category'));
  }

  public function printPermohonanUjiBlangko()
  {
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik.formatPrint.blangko', []);

    return $pdf->stream();
  }

  public function nota($id)
  {
    //    dd("ygygyu");
    $user = Auth()->user();
    $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $id)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
      ->first();




//    $methodNonPackets = Sample::where('permohonan_uji_id', '=', $id)
//      ->leftjoin('ms_packet', function ($join) {
//        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
//          ->whereNull('ms_packet.deleted_at')
//          ->whereNull('tb_samples.deleted_at');
//      })
//      ->join('tb_sample_method', function ($join) {
//        $join->on('tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
//          ->whereNull('tb_samples.deleted_at')
//          ->whereNull('tb_sample_method.deleted_at');
//      })
//      ->join('ms_method', function ($join) {
//        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
//          ->whereNull('ms_method.deleted_at')
//          ->whereNull('tb_sample_method.deleted_at');
//      })
//      ->whereNull('ms_packet.id_packet')
//      // ->orderBy('ms_packet.id_packet')
//      ->select('ms_method.id_method', 'tb_sample_method.price_method', 'ms_method.params_method', 'ms_method.price_total_method', DB::raw('count(*) as count_method'))
//      ->groupBy('ms_method.id_method', 'ms_method.params_method', 'ms_method.price_total_method')
//      // ->lists('total','id_method')
//      ->get();





    $value_items = array();
    $total = 0;
//    foreach ($methodNonPackets as $methodNonPacket) {
//      $methodNonPacket["name_item"] = $methodNonPacket["params_method"];
//      $methodNonPacket["count_item"] = $methodNonPacket["count_method"];
//      $methodNonPacket["price_item"] = $methodNonPacket["price_total_method"];
//      $methodNonPacket["total"] = $methodNonPacket["price_total_method"] * $methodNonPacket["count_method"];
//      array_push($value_items, $methodNonPacket);
//      $total = $total + $methodNonPacket["total"];
//    }


//    $methodPackets = Sample::where('permohonan_uji_id', '=', $id)
//    ->leftjoin('ms_packet', function ($join) {
//        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
//            ->whereNull('ms_packet.deleted_at')
//            ->whereNull('tb_samples.deleted_at');
//    })
//    ->leftJoin('tb_sample_method', 'tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
//    ->whereNotNull('ms_packet.id_packet')
//    ->select(
//        'tb_samples.sample_type_group',
//        'tb_samples.created_at',
//        'ms_packet.id_packet',
//        'tb_samples.cost_samples',
//        'ms_packet.name_packet',
//        DB::raw('SUM(tb_sample_method.price_method) as price_total_packet'),
//        DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')  // Count unique combination of created_at and id_packet
//    )
//    ->groupBy(
//        'tb_samples.sample_type_group',
//        'tb_samples.created_at',
//        'ms_packet.id_packet',
//        'ms_packet.name_packet',
//    )
//    ->orderBy('ms_packet.id_packet', 'DESC')
//    ->get();

    $additionalMethods = DB::table('tb_samples')
      ->whereNull('tb_samples.deleted_at')
      ->where('permohonan_uji_id', '=', $id)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'tb_samples.packet_id')
          ->on('ms_packet_detail.method_id', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('ms_packet_detail.id_packet_detail')
      ->select('tb_samples.*', 'tb_sample_method.*', 'ms_sample_type.name_sample_type')
      ->get();

    $mappedMethods = $additionalMethods->map(function($item) use ($id) {
      if ($item->method_id === null) {
        $methodInfo = DB::table('tb_sample_method')
          ->where('id_sample_method', $item->id_sample_method)
          ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->select('tb_sample_method.method_id', 'ms_method.params_method as method_name')
          ->first();

        if ($methodInfo) {
          $item->method_id = $methodInfo->method_id;
          $item->method_name = $methodInfo->method_name;
        }
      } else {
        $methodName = DB::table('ms_method')
          ->where('id_method', $item->method_id)
          ->value('params_method');

        if ($methodName) {
          $item->method_name = $methodName;
        }
      }

      // Add location/titik pengambilan info with HTML formatting
      $lokasi_raw = $item->titik_pengambilan ??
                    ($item->name_customer_pdam && $item->address_location_pdam
                      ? $item->name_customer_pdam . ' ' . $item->address_location_pdam
                      : '-');
      $item->lokasi_titik = $lokasi_raw;
      $item->jenis_sampel_display = $item->name_sample_type ?? '-';

      return $item;
    });

    $groupedMethods = $mappedMethods->groupBy('method_id');

    foreach ($groupedMethods as $methodId => $methods) {
      $firstMethod = $methods->first();
      $count = $methods->count();

      $price = $firstMethod->price_method ?? 0;
      $totalPrice = $price * $count;

      $additionalMethod = [
        "method_id" => $methodId,
        "method_name" => $firstMethod->method_name ?? null,
        "name_item" => $firstMethod->method_name ?? null,
        "count_item" => $count,
        "price_item" => $price,
        "total" => $totalPrice,
        "jenis_sampel" => $firstMethod->jenis_sampel_display ?? '-',
        "lokasi" => $firstMethod->lokasi_titik ?? '-'
      ];

      array_push($value_items, $additionalMethod);
      $total = $total + $totalPrice;
    }


// Data awal dari query Anda
//    $methodPackets = Sample::where('permohonan_uji_id', '=', $id)
//      ->leftjoin('ms_packet', function ($join) {
//        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
//          ->whereNull('ms_packet.deleted_at')
//          ->whereNull('tb_samples.deleted_at');
//      })
//      ->leftJoin('tb_sample_method', 'tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
//      ->whereNotNull('ms_packet.id_packet')
//      ->select(
//        'tb_samples.sample_type_group',
//        'tb_samples.created_at',
//        'ms_packet.id_packet',
//        'tb_samples.cost_samples',
//        'ms_packet.name_packet',
//        DB::raw('SUM(tb_sample_method.price_method) as price_total_packet'),
//        DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')
//      )
//      ->groupBy(
//        'tb_samples.sample_type_group',
//        'tb_samples.created_at',
//        'ms_packet.id_packet',
//        'ms_packet.name_packet',
//      )
//      ->orderBy('ms_packet.id_packet', 'DESC')
//      ->get();

    $additionalMethodIds = $additionalMethods->pluck('id_sample_method')->toArray();
    $excludeCondition = empty($additionalMethodIds) ? '1=1' : 'tb_sample_method.id_sample_method NOT IN (' . implode(',', array_map('intval', $additionalMethodIds)) . ')';


    $methodPackets = Sample::where('permohonan_uji_id', '=', $id)
      ->leftjoin('ms_packet', function ($join) {
        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
          ->whereNull('ms_packet.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftJoin('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNotNull('ms_packet.id_packet')
      ->select(
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'tb_samples.cost_samples',
        'ms_packet.name_packet',
        'ms_sample_type.name_sample_type',
        DB::raw('COUNT(DISTINCT ms_packet_detail.id_packet_detail) as count_packet_detail'),
        DB::raw('COUNT(DISTINCT CASE
          WHEN ' . $excludeCondition . '
          THEN tb_sample_method.id_sample_method
          ELSE NULL
        END) as count_sample_method'),
        DB::raw('CASE
  WHEN COUNT(DISTINCT ms_packet_detail.id_packet_detail) >= COUNT(DISTINCT CASE
    WHEN ' . $excludeCondition . '
    THEN tb_sample_method.id_sample_method
    ELSE NULL
  END)
  THEN ms_packet.price_total_packet
  ELSE (
    SELECT SUM(sm.price_method)
    FROM tb_sample_method sm
    INNER JOIN tb_samples s ON sm.sample_id = s.id_samples
    WHERE s.created_at = tb_samples.created_at
    AND s.packet_id = ms_packet.id_packet
    AND s.deleted_at IS NULL
    AND sm.deleted_at IS NULL
    AND (' . $excludeCondition . ')
  )
END as price_total_packet'),
        DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')
      )
      ->groupBy(
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'ms_packet.name_packet',
        'ms_packet.price_total_packet',
        'ms_sample_type.name_sample_type',
        'tb_samples.cost_samples'
      )
      ->orderBy('ms_packet.id_packet', 'DESC')
      ->get();

    $groupedPackets = [];

    foreach ($methodPackets as $packet) {
      $key = $packet->name_packet . '|' . $packet->price_total_packet;

      // Determine lokasi/titik pengambilan with HTML formatting
      $lokasi_raw = $packet->titik_pengambilan ??
                    ($packet->name_customer_pdam && $packet->address_location_pdam
                      ? $packet->name_customer_pdam . ' ' . $packet->address_location_pdam
                      : '-');
      $lokasi_titik = $lokasi_raw;

      if (isset($groupedPackets[$key])) {
        $groupedPackets[$key]['count_method'] += $packet->count_method;
      } else {
        $groupedPackets[$key] = [
          'sample_type_group' => $packet->sample_type_group,
          'created_at' => $packet->created_at,
          'id_packet' => $packet->id_packet,
          'cost_samples' => $packet->cost_samples,
          'name_packet' => $packet->name_packet,
          'count_packet_detail' => $packet->count_packet_detail,
          'count_sample_method' => $packet->count_sample_method,
          'price_total_packet' => $packet->price_total_packet,
          'count_method' => $packet->count_method,
          'jenis_sampel' => $packet->name_sample_type ?? '-',
          'lokasi' => $lokasi_titik
        ];
      }
    }

    $methodPackets = $groupedPackets;


//    $count = 1;
//    $sample_type_group = null;
//    $i = 0;
//
//    $date_create=null;
//    $packet_id=null;
//
//    foreach ($methodPackets as $methodPacket) {
//
//      if ($date_create!=$methodPacket-> created_at &&  $packet_id!=$methodPacket-> id_packet) {
//        # code...
//        if ($i == 0) {
//          $id_packet = $methodPacket['id_packet'];
//        }
//        if ($i == 0 && isset($methodPacket['sample_type_group'])) {
//          $id_packet = $methodPacket['id_packet'];
//          $methodPackets[$i]['count_method'] = 1;
//        }
//        if ($count = 1 && isset($methodPacket['sample_type_group'])) {
//          $id_packet = $methodPacket['id_packet'];
//          $methodPackets[$i]['count_method'] = 1;
//        }
//
//        if ($id_packet == $methodPacket['id_packet'] && isset($methodPacket['sample_type_group']) && $i != 0) {
//          // unset($methodPackets[$i]);
//          // $count++;
//          // $methodPackets[$i - 1]['count_method'] = $count;
//
//        } else {
//          $count = 1;
//        }
//        print_r( $count );
//        $i++;
//        $date_create=$methodPacket-> created_at;
//        $packet_id=$methodPacket-> id_packet;
//      }
//
//    }




    foreach ($methodPackets as $methodPacket) {

      $methodPacket["name_item"] = $methodPacket["name_packet"];
      $methodPacket["count_item"] = $methodPacket["count_method"];
      $methodPacket["price_item"] = $methodPacket["price_total_packet"];

//      if ($methodPacket['cost_samples'] == 0) {
//
//        $methodPacket["price_item"] = $methodPacket["price_total_packet"];
//        if ($methodPacket["price_total_packet"] == 0) {
//          $packet_details = PacketDetail::where('packet_id', $methodPacket["id_packet"])
//            ->join('ms_method', function ($join) {
//              $join->on('ms_method.id_method', '=', 'ms_packet_detail.method_id')
//                ->whereNull('ms_method.deleted_at')
//                ->whereNull('ms_packet_detail.deleted_at');
//            })->get();
//          $total = 0;
//          foreach ($packet_details as $packet_detail) {
//            # code...
//            $total = $total + $packet_detail->price_total_method;
//          }
//          $methodPacket["price_item"] = $total;
//        }
//      } else {
//        $methodPacket["price_item"] = $methodPacket['cost_samples'];
//      }

      $methodPacket["total"] = $methodPacket["price_item"] * $methodPacket["count_method"];
      array_push($value_items, $methodPacket);

      $total = $total +  $methodPacket["total"];

      $id_packet = $methodPacket['id_packet'];
    }

    // Add sampling costs for each sample
    $samplesWithSampling = Sample::where('permohonan_uji_id', '=', $id)
      ->where('is_sampling', '=', 1)
      ->where('cost_sampling_samples', '>', 0)
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('tb_samples.deleted_at')
      ->select(
        'tb_samples.id_samples',
        'tb_samples.codesample_samples',
        'tb_samples.titik_pengambilan',
        'tb_samples.cost_sampling_samples',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_sample_type.name_sample_type'
      )
      ->get();

    foreach ($samplesWithSampling as $sample) {
      // Determine lokasi
      $lokasi = $sample->titik_pengambilan ??
                ($sample->name_customer_pdam && $sample->address_location_pdam
                  ? $sample->name_customer_pdam . ' ' . $sample->address_location_pdam
                  : '-');

      $jenis_sampel = $sample->name_sample_type ?? '-';
      $kode_sampel = $sample->codesample_samples ?? '-';

      $samplingItem = [
        "name_item" => "Sampling - {$kode_sampel}",
        "count_item" => 1,
        "price_item" => $sample->cost_sampling_samples,
        "total" => $sample->cost_sampling_samples,
        "jenis_sampel" => $jenis_sampel,
        "lokasi" => $lokasi
      ];

      array_push($value_items, $samplingItem);
      $total = $total + $sample->cost_sampling_samples;
    }

    $permohonan_uji->total_harga = $total;
    $permohonan_uji->save();
    // dd($total);

    // Tanggal Pemeriksaan
    $sample = Sample::query()->where('permohonan_uji_id', $id)->first();

    try {
      $tanggalPemeriksaan = VerificationActivitySample::query()
        ->where('id_sample', '=', $sample->id_samples)
        ->where('id_verification_activity', '=', 1)
        ->firstOrFail()
        ->stop_date;
    }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      $tanggalPemeriksaan = null;
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.nota', compact('value_items', 'user', 'permohonan_uji', 'tanggalPemeriksaan'));

    return $pdf->stream();

    /* return view('masterweb::module.admin.laboratorium.permohonan-uji.nota', compact('value_items', 'user', 'permohonan_uji')); */
  }

  public function formulirPengambilanSampel($id)
  {
    $user = Auth()->user();
    $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $id)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
      ->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Get all samples with details
    $samples = Sample::where('permohonan_uji_id', '=', $id)
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('tb_samples.deleted_at')
      ->orderBy('tb_samples.count_id', 'asc')
      ->select(
        'tb_samples.*',
        'ms_sample_type.name_sample_type',
        'ms_sample_type.code_sample_type'
      )
      ->get();

    if ($samples->isEmpty()) {
      return abort(404, 'Tidak ada sample yang ditemukan');
    }

    // Get methods for each sample grouped by lab
    foreach ($samples as $sample) {
      $methods = SampleMethod::where('sample_id', $sample->id_samples)
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->join('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
        ->whereNull('tb_sample_method.deleted_at')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereNull('ms_method.deleted_at')
        ->select(
          'ms_laboratorium.nama_laboratorium',
          'ms_laboratorium.kode_laboratorium',
          'ms_method.params_method'
        )
        ->get();

      $sample->has_kimia = $methods->whereIn('kode_laboratorium', ['KMA', 'FKA', 'KIM'])->count() > 0;
      $sample->has_mikro = $methods->where('kode_laboratorium', 'MBI')->count() > 0;

      // Store methods list for display
      $sample->methods_list = $methods;

      // Check if using packet
      $sample->packet_name = null;
      if ($sample->packet_id) {
        $packet = \Smt\Masterweb\Models\Packet::find($sample->packet_id);
        if ($packet) {
          $sample->packet_name = $packet->name_packet;
        }
      }
    }

    // Wilayah data if sampling by lab
    $wilayah_data = null;
    if ($permohonan_uji->is_sampling == 1) {
      $wilayah_data = [
        'provinsi' => $permohonan_uji->provinsi_sampling_text,
        'kabupaten' => $permohonan_uji->kabupaten_sampling_text,
        'kecamatan' => $permohonan_uji->kecamatan_sampling_text,
        'desa' => $permohonan_uji->desa_sampling_text,
        'detail_alamat' => $permohonan_uji->detail_alamat_sampling,
      ];
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.formulir-pengambilan-sampel', compact('permohonan_uji', 'samples', 'user', 'wilayah_data'));

    return $pdf->stream('Formulir_Pengambilan_Sampel_' . $permohonan_uji->code_permohonan_uji . '.pdf');
  }

  public function notaGabungan($idPermohonanUji)
  {
    $user = Auth()->user();
    $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $idPermohonanUji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
      ->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Get all samples
    $allSamples = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereNull('deleted_at')
      ->orderBy('count_id', 'asc')
      ->get();

    if ($allSamples->isEmpty()) {
      return abort(404, 'Tidak ada sample yang ditemukan');
    }

    // Group samples by lab type
    $samplesByLabType = [
      'kimia' => [],
      'mikro' => []
    ];

    foreach ($allSamples as $sample) {
      $labCodes = SampleMethod::where('sample_id', $sample->id_samples)
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->pluck('kode_laboratorium')
        ->unique();

      foreach ($labCodes as $labCode) {
        if (in_array($labCode, ['KMA', 'FKA', 'KIM'])) {
          $samplesByLabType['kimia'][] = $sample->id_samples;
        } elseif ($labCode == 'MBI') {
          $samplesByLabType['mikro'][] = $sample->id_samples;
        }
      }
    }

    // Remove duplicates
    $samplesByLabType['kimia'] = array_unique($samplesByLabType['kimia']);
    $samplesByLabType['mikro'] = array_unique($samplesByLabType['mikro']);

    // Prepare data for each lab
    $allLabsData = [];

    foreach (['kimia', 'mikro'] as $labType) {
      if (empty($samplesByLabType[$labType])) {
        continue;
      }

      $labData = $this->prepareNotaDataForLab($idPermohonanUji, $samplesByLabType[$labType], $labType);
      if ($labData) {
        $allLabsData[] = [
          'type' => $labType,
          'labTypeName' => $labType === 'kimia' ? 'Kimia/Fisika' : 'Mikrobiologi',
          'value_items' => $labData['value_items'],
          'total' => $labData['total']
        ];
      }
    }

    if (empty($allLabsData)) {
      return abort(404, 'Tidak ada data yang dapat diprint');
    }

    // Tanggal Pemeriksaan
    $sample = Sample::query()->where('permohonan_uji_id', $idPermohonanUji)->first();

    try {
      $tanggalPemeriksaan = VerificationActivitySample::query()
        ->where('id_sample', '=', $sample->id_samples)
        ->where('id_verification_activity', '=', 1)
        ->firstOrFail()
        ->stop_date;
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      $tanggalPemeriksaan = null;
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.nota-gabungan', compact('allLabsData', 'user', 'permohonan_uji', 'tanggalPemeriksaan'));

    return $pdf->stream('Nota_Gabungan_' . $idPermohonanUji . '.pdf');
  }

  private function prepareNotaDataForLab($idPermohonanUji, $sampleIdsByLab, $labType)
  {
    $labCodes = $labType === 'kimia' ? ['KMA', 'FKA', 'KIM'] : ['MBI'];

    // Get additional methods (not in packet)
    $additionalMethods = DB::table('tb_samples')
      ->whereNull('tb_samples.deleted_at')
      ->where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->join('tb_sample_method', function ($join) use ($labCodes) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_laboratorium', function($join) use ($labCodes) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereIn('ms_laboratorium.kode_laboratorium', $labCodes);
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'tb_samples.packet_id')
          ->on('ms_packet_detail.method_id', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('ms_packet_detail.id_packet_detail')
      ->select('tb_samples.*', 'tb_sample_method.*', 'ms_sample_type.name_sample_type')
      ->get();


    $mappedMethods = $additionalMethods->map(function($item) {
      if ($item->method_id === null) {
        $methodInfo = DB::table('tb_sample_method')
          ->where('id_sample_method', $item->id_sample_method)
          ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->select('tb_sample_method.method_id', 'ms_method.params_method as method_name')
          ->first();

        if ($methodInfo) {
          $item->method_id = $methodInfo->method_id;
          $item->method_name = $methodInfo->method_name;
        }
      } else {
        $methodName = DB::table('ms_method')
          ->where('id_method', $item->method_id)
          ->value('params_method');

        if ($methodName) {
          $item->method_name = $methodName;
        }
      }

      $lokasi_raw = $item->titik_pengambilan ??
                    ($item->name_customer_pdam && $item->address_location_pdam
                      ? $item->name_customer_pdam . ' ' . $item->address_location_pdam
                      : '-');
      $item->lokasi_titik = $lokasi_raw;
      $item->jenis_sampel_display = $item->name_sample_type ?? '-';

      return $item;
    });

    $groupedMethods = $mappedMethods->groupBy('method_id');
    $value_items = array();
    $total = 0;

    foreach ($groupedMethods as $methodId => $methods) {
      $firstMethod = $methods->first();
      $count = $methods->count();

      $price = $firstMethod->price_method ?? 0;
      $totalPrice = $price * $count;

      $additionalMethod = [
        "method_id" => $methodId,
        "method_name" => $firstMethod->method_name ?? null,
        "name_item" => $firstMethod->method_name ?? null,
        "count_item" => $count,
        "price_item" => $price,
        "total" => $totalPrice,
        "jenis_sampel" => $firstMethod->jenis_sampel_display ?? '-',
        "lokasi" => $firstMethod->lokasi_titik ?? '-'
      ];

      array_push($value_items, $additionalMethod);
      $total = $total + $totalPrice;
    }

    // Get packet methods - only include packets that have at least one method from the specified lab
    $additionalMethodIds = $additionalMethods->pluck('id_sample_method')->toArray();
    $excludeCondition = empty($additionalMethodIds) ? '1=1' : 'tb_sample_method.id_sample_method NOT IN (' . implode(',', array_map('intval', $additionalMethodIds)) . ')';

    // First, get packets that actually have methods for this lab
    $validPacketIds = DB::table('tb_samples')
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->whereNotNull('tb_samples.packet_id')
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_laboratorium', function($join) use ($labCodes) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereIn('ms_laboratorium.kode_laboratorium', $labCodes);
      })
      ->distinct()
      ->pluck('tb_samples.packet_id');

    if ($validPacketIds->isEmpty()) {
      $methodPackets = collect([]);
    } else {
      $methodPackets = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
        ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
        ->whereIn('tb_samples.packet_id', $validPacketIds->toArray())
        ->leftjoin('ms_packet', function ($join) {
          $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
            ->whereNull('ms_packet.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('tb_sample_method', function ($join) use ($labCodes) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->leftJoin('ms_laboratorium', function($join) use ($labCodes) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereIn('ms_laboratorium.kode_laboratorium', $labCodes);
        })
        ->leftJoin('ms_packet_detail', function ($join) {
          $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
            ->whereNull('ms_packet_detail.deleted_at');
        })
        ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
        ->whereNotNull('ms_packet.id_packet')
        ->whereNotNull('ms_laboratorium.id_laboratorium')
        ->select(
          'tb_samples.sample_type_group',
          'tb_samples.created_at',
          'tb_samples.titik_pengambilan',
          'tb_samples.name_customer_pdam',
          'tb_samples.address_location_pdam',
          'ms_packet.id_packet',
          'tb_samples.cost_samples',
          'ms_packet.name_packet',
          'ms_sample_type.name_sample_type',
          DB::raw('COUNT(DISTINCT ms_packet_detail.id_packet_detail) as count_packet_detail'),
          DB::raw('COUNT(DISTINCT CASE
            WHEN ' . $excludeCondition . '
            THEN tb_sample_method.id_sample_method
            ELSE NULL
          END) as count_sample_method'),
          DB::raw('CASE
            WHEN COUNT(DISTINCT ms_packet_detail.id_packet_detail) >= COUNT(DISTINCT CASE
              WHEN ' . $excludeCondition . '
              THEN tb_sample_method.id_sample_method
              ELSE NULL
            END)
            THEN ms_packet.price_total_packet
            ELSE (
              SELECT SUM(sm.price_method)
              FROM tb_sample_method sm
              INNER JOIN tb_samples s ON sm.sample_id = s.id_samples
              INNER JOIN ms_laboratorium ml ON ml.id_laboratorium = sm.laboratorium_id
              WHERE s.created_at = tb_samples.created_at
              AND s.packet_id = ms_packet.id_packet
              AND s.deleted_at IS NULL
              AND sm.deleted_at IS NULL
              AND ml.deleted_at IS NULL
              AND ml.kode_laboratorium IN (\'' . implode("','", $labCodes) . '\')
              AND (' . $excludeCondition . ')
            )
          END as price_total_packet'),
          DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')
        )
        ->groupBy(
          'tb_samples.sample_type_group',
          'tb_samples.created_at',
          'tb_samples.titik_pengambilan',
          'tb_samples.name_customer_pdam',
          'tb_samples.address_location_pdam',
          'ms_packet.id_packet',
          'ms_packet.name_packet',
          'ms_packet.price_total_packet',
          'ms_sample_type.name_sample_type',
          'tb_samples.cost_samples'
        )
        ->orderBy('ms_packet.id_packet', 'DESC')
        ->get();
    }

    $groupedPackets = [];

    foreach ($methodPackets as $packet) {
      $key = $packet->name_packet . '|' . $packet->price_total_packet;

      $lokasi_raw = $packet->titik_pengambilan ??
                    ($packet->name_customer_pdam && $packet->address_location_pdam
                      ? $packet->name_customer_pdam . ' ' . $packet->address_location_pdam
                      : '-');
      $lokasi_titik = $lokasi_raw;

      if (isset($groupedPackets[$key])) {
        $groupedPackets[$key]['count_method'] += $packet->count_method;
      } else {
        $groupedPackets[$key] = [
          'sample_type_group' => $packet->sample_type_group,
          'created_at' => $packet->created_at,
          'id_packet' => $packet->id_packet,
          'cost_samples' => $packet->cost_samples,
          'name_packet' => $packet->name_packet,
          'count_packet_detail' => $packet->count_packet_detail,
          'count_sample_method' => $packet->count_sample_method,
          'price_total_packet' => $packet->price_total_packet,
          'count_method' => $packet->count_method,
          'jenis_sampel' => $packet->name_sample_type ?? '-',
          'lokasi' => $lokasi_titik
        ];
      }
    }

    $methodPackets = $groupedPackets;

    foreach ($methodPackets as $methodPacket) {
      $methodPacket["name_item"] = $methodPacket["name_packet"];
      $methodPacket["count_item"] = $methodPacket["count_method"];
      $methodPacket["price_item"] = $methodPacket["price_total_packet"];
      $methodPacket["total"] = $methodPacket["price_item"] * $methodPacket["count_method"];

      array_push($value_items, $methodPacket);
      $total = $total +  $methodPacket["total"];
    }

    // Add sampling costs for each sample in this lab
    $samplesWithSampling = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->where('is_sampling', '=', 1)
      ->where('cost_sampling_samples', '>', 0)
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('tb_samples.deleted_at')
      ->select(
        'tb_samples.id_samples',
        'tb_samples.codesample_samples',
        'tb_samples.titik_pengambilan',
        'tb_samples.cost_sampling_samples',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_sample_type.name_sample_type'
      )
      ->get();

    foreach ($samplesWithSampling as $sample) {
      // Determine lokasi
      $lokasi = $sample->titik_pengambilan ??
                ($sample->name_customer_pdam && $sample->address_location_pdam
                  ? $sample->name_customer_pdam . ' ' . $sample->address_location_pdam
                  : '-');

      $jenis_sampel = $sample->name_sample_type ?? '-';
      $kode_sampel = $sample->codesample_samples ?? '-';

      $samplingItem = [
        "name_item" => "Sampling - {$kode_sampel}",
        "count_item" => 1,
        "price_item" => $sample->cost_sampling_samples,
        "total" => $sample->cost_sampling_samples,
        "jenis_sampel" => $jenis_sampel,
        "lokasi" => $lokasi
      ];

      array_push($value_items, $samplingItem);
      $total = $total + $sample->cost_sampling_samples;
    }

    return [
      'value_items' => $value_items,
      'total' => $total
    ];
  }

  public function notaGabunganByLab($idPermohonanUji, $labType)
  {
    $user = Auth()->user();
    $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $idPermohonanUji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
      ->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Determine lab codes based on type
    $labCodes = $labType === 'kimia' ? ['KMA', 'FKA', 'KIM'] : ['MBI'];

    // Get all samples for this permohonan uji
    $allSamples = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereNull('deleted_at')
      ->orderBy('count_id', 'asc')
      ->get();

    if ($allSamples->isEmpty()) {
      return abort(404, 'Tidak ada sample yang ditemukan');
    }

    // Filter samples by lab type
    $sampleIdsByLab = [];
    foreach ($allSamples as $sample) {
      $hasLabType = SampleMethod::where('sample_id', $sample->id_samples)
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereIn('kode_laboratorium', $labCodes)
        ->exists();

      if ($hasLabType) {
        $sampleIdsByLab[] = $sample->id_samples;
      }
    }

    if (empty($sampleIdsByLab)) {
      return abort(404, 'Tidak ada sample untuk lab ' . $labType);
    }

    // Get additional methods (not in packet)
    $additionalMethods = DB::table('tb_samples')
      ->whereNull('tb_samples.deleted_at')
      ->where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->join('tb_sample_method', function ($join) use ($labCodes) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_laboratorium', function($join) use ($labCodes) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereIn('ms_laboratorium.kode_laboratorium', $labCodes);
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'tb_samples.packet_id')
          ->on('ms_packet_detail.method_id', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('ms_packet_detail.id_packet_detail')
      ->select('tb_samples.*', 'tb_sample_method.*', 'ms_sample_type.name_sample_type')
      ->get();

    $mappedMethods = $additionalMethods->map(function($item) use ($idPermohonanUji) {
      if ($item->method_id === null) {
        $methodInfo = DB::table('tb_sample_method')
          ->where('id_sample_method', $item->id_sample_method)
          ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->select('tb_sample_method.method_id', 'ms_method.params_method as method_name')
          ->first();

        if ($methodInfo) {
          $item->method_id = $methodInfo->method_id;
          $item->method_name = $methodInfo->method_name;
        }
      } else {
        $methodName = DB::table('ms_method')
          ->where('id_method', $item->method_id)
          ->value('params_method');

        if ($methodName) {
          $item->method_name = $methodName;
        }
      }

      $lokasi_raw = $item->titik_pengambilan ??
                    ($item->name_customer_pdam && $item->address_location_pdam
                      ? $item->name_customer_pdam . ' ' . $item->address_location_pdam
                      : '-');
      $item->lokasi_titik = $lokasi_raw;
      $item->jenis_sampel_display = $item->name_sample_type ?? '-';

      return $item;
    });

    $groupedMethods = $mappedMethods->groupBy('method_id');
    $value_items = array();
    $total = 0;

    foreach ($groupedMethods as $methodId => $methods) {
      $firstMethod = $methods->first();
      $count = $methods->count();

      $price = $firstMethod->price_method ?? 0;
      $totalPrice = $price * $count;

      $additionalMethod = [
        "method_id" => $methodId,
        "method_name" => $firstMethod->method_name ?? null,
        "name_item" => $firstMethod->method_name ?? null,
        "count_item" => $count,
        "price_item" => $price,
        "total" => $totalPrice,
        "jenis_sampel" => $firstMethod->jenis_sampel_display ?? '-',
        "lokasi" => $firstMethod->lokasi_titik ?? '-'
      ];

      array_push($value_items, $additionalMethod);
      $total = $total + $totalPrice;
    }

    // Get packet methods
    $additionalMethodIds = $additionalMethods->pluck('id_sample_method')->toArray();
    $excludeCondition = empty($additionalMethodIds) ? '1=1' : 'tb_sample_method.id_sample_method NOT IN (' . implode(',', array_map('intval', $additionalMethodIds)) . ')';

    $methodPackets = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->leftjoin('ms_packet', function ($join) {
        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
          ->whereNull('ms_packet.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftJoin('tb_sample_method', function ($join) use ($labCodes) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftJoin('ms_laboratorium', function($join) use ($labCodes) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereIn('ms_laboratorium.kode_laboratorium', $labCodes);
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNotNull('ms_packet.id_packet')
      ->whereNotNull('ms_laboratorium.id_laboratorium')
      ->select(
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'tb_samples.cost_samples',
        'ms_packet.name_packet',
        'ms_sample_type.name_sample_type',
        DB::raw('COUNT(DISTINCT ms_packet_detail.id_packet_detail) as count_packet_detail'),
        DB::raw('COUNT(DISTINCT CASE
          WHEN ' . $excludeCondition . '
          THEN tb_sample_method.id_sample_method
          ELSE NULL
        END) as count_sample_method'),
        DB::raw('CASE
          WHEN COUNT(DISTINCT ms_packet_detail.id_packet_detail) >= COUNT(DISTINCT CASE
            WHEN ' . $excludeCondition . '
            THEN tb_sample_method.id_sample_method
            ELSE NULL
          END)
          THEN ms_packet.price_total_packet
          ELSE (
            SELECT SUM(sm.price_method)
            FROM tb_sample_method sm
            INNER JOIN tb_samples s ON sm.sample_id = s.id_samples
            WHERE s.created_at = tb_samples.created_at
            AND s.packet_id = ms_packet.id_packet
            AND s.deleted_at IS NULL
            AND sm.deleted_at IS NULL
            AND (' . $excludeCondition . ')
          )
        END as price_total_packet'),
        DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')
      )
      ->groupBy(
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'ms_packet.name_packet',
        'ms_packet.price_total_packet',
        'ms_sample_type.name_sample_type',
        'tb_samples.cost_samples'
      )
      ->orderBy('ms_packet.id_packet', 'DESC')
      ->get();

    $groupedPackets = [];

    foreach ($methodPackets as $packet) {
      $key = $packet->name_packet . '|' . $packet->price_total_packet;

      $lokasi_raw = $packet->titik_pengambilan ??
                    ($packet->name_customer_pdam && $packet->address_location_pdam
                      ? $packet->name_customer_pdam . ' ' . $packet->address_location_pdam
                      : '-');
      $lokasi_titik = $lokasi_raw;

      if (isset($groupedPackets[$key])) {
        $groupedPackets[$key]['count_method'] += $packet->count_method;
      } else {
        $groupedPackets[$key] = [
          'sample_type_group' => $packet->sample_type_group,
          'created_at' => $packet->created_at,
          'id_packet' => $packet->id_packet,
          'cost_samples' => $packet->cost_samples,
          'name_packet' => $packet->name_packet,
          'count_packet_detail' => $packet->count_packet_detail,
          'count_sample_method' => $packet->count_sample_method,
          'price_total_packet' => $packet->price_total_packet,
          'count_method' => $packet->count_method,
          'jenis_sampel' => $packet->name_sample_type ?? '-',
          'lokasi' => $lokasi_titik
        ];
      }
    }

    $methodPackets = $groupedPackets;

    foreach ($methodPackets as $methodPacket) {
      $methodPacket["name_item"] = $methodPacket["name_packet"];
      $methodPacket["count_item"] = $methodPacket["count_method"];
      $methodPacket["price_item"] = $methodPacket["price_total_packet"];
      $methodPacket["total"] = $methodPacket["price_item"] * $methodPacket["count_method"];

      array_push($value_items, $methodPacket);
      $total = $total +  $methodPacket["total"];
    }

    // Add sampling costs for each sample in this lab
    $samplesWithSampling = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereIn('tb_samples.id_samples', $sampleIdsByLab)
      ->where('is_sampling', '=', 1)
      ->where('cost_sampling_samples', '>', 0)
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNull('tb_samples.deleted_at')
      ->select(
        'tb_samples.id_samples',
        'tb_samples.codesample_samples',
        'tb_samples.titik_pengambilan',
        'tb_samples.cost_sampling_samples',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_sample_type.name_sample_type'
      )
      ->get();

    foreach ($samplesWithSampling as $sample) {
      // Determine lokasi
      $lokasi = $sample->titik_pengambilan ??
                ($sample->name_customer_pdam && $sample->address_location_pdam
                  ? $sample->name_customer_pdam . ' ' . $sample->address_location_pdam
                  : '-');

      $jenis_sampel = $sample->name_sample_type ?? '-';
      $kode_sampel = $sample->codesample_samples ?? '-';

      $samplingItem = [
        "name_item" => "Sampling - {$kode_sampel}",
        "count_item" => 1,
        "price_item" => $sample->cost_sampling_samples,
        "total" => $sample->cost_sampling_samples,
        "jenis_sampel" => $jenis_sampel,
        "lokasi" => $lokasi
      ];

      array_push($value_items, $samplingItem);
      $total = $total + $sample->cost_sampling_samples;
    }

    // $permohonan_uji->total_harga = $total;
    // $permohonan_uji->save();

    // Tanggal Pemeriksaan
    $sample = Sample::query()->where('permohonan_uji_id', $idPermohonanUji)->first();

    try {
      $tanggalPemeriksaan = VerificationActivitySample::query()
        ->where('id_sample', '=', $sample->id_samples)
        ->where('id_verification_activity', '=', 1)
        ->firstOrFail()
        ->stop_date;
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      $tanggalPemeriksaan = null;
    }

    $labTypeName = $labType === 'kimia' ? 'Kimia/Fisika' : 'Mikrobiologi';
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.nota', compact('value_items', 'user', 'permohonan_uji', 'tanggalPemeriksaan', 'labTypeName'));

    return $pdf->stream('Nota_' . ucfirst($labType) . '_' . $idPermohonanUji . '.pdf');
  }

  public function permintaan_pemeriksaan($id)
  {

    //    dd("ygygyu");


    $user = Auth()->user();
    $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $id)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
      ->first();

    $samples = Sample::where('permohonan_uji_id', '=', $id)
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('ms_sample_type.*', 'tb_samples.*', 'tb_samples.created_at as dibuat')
      ->get();
    $samples_first = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('count_id', 'ASC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();
    $samples_last = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('count_id', 'DESC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();

    $sample_types = Sample::where('permohonan_uji_id', '=', $id)
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('ms_sample_type.*')
      ->distinct('ms_sample_type.id_sample_type')
      ->get();

    $sample_sampling_first = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('datesampling_samples', 'ASC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();
    $sample_sampling_last = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('datesampling_samples', 'DESC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();

    $sample_sending_first = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('date_sending', 'ASC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();
    $sample_sending_last = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('date_sending', 'DESC')

      ->select('tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();

    $sample_penerimaan_first = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('penerimaan_sample_date', 'ASC')
      ->join('tb_sample_penerimaan', function ($join) {
        $join->on('tb_sample_penerimaan.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_penerimaan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_sample_penerimaan.*', 'tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();

    $sample_penerimaan_last = Sample::where('permohonan_uji_id', '=', $id)
      ->orderBy('penerimaan_sample_date', 'DESC')
      ->join('tb_sample_penerimaan', function ($join) {
        $join->on('tb_sample_penerimaan.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_penerimaan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_sample_penerimaan.*', 'tb_samples.*', 'tb_samples.created_at as dibuat')
      ->first();



    // $result=$result->getString();

    // dd($samples);

    $laboratoriums = Sample::where('permohonan_uji_id', '=', $id)
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
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('ms_laboratorium.id_laboratorium', 'ms_laboratorium.nama_laboratorium')
      ->distinct('id_laboratorium')
      ->get();


    // view on pdf
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji.permintaan', compact('sample_penerimaan_first', 'sample_penerimaan_last', 'sample_sending_first', 'sample_sending_last', 'sample_sampling_first', 'sample_sampling_last', 'sample_types', 'samples_last', 'samples_first', 'id', 'user',  'permohonan_uji', 'samples', 'laboratoriums'));

    return $pdf->stream();

    // view on raw html
    /* return view('masterweb::module.admin.laboratorium.permohonan-uji.permintaan', compact('sample_penerimaan_first', 'sample_penerimaan_last', 'sample_sending_first', 'sample_sending_last', 'sample_sampling_first', 'sample_sampling_last', 'sample_types', 'samples_last', 'samples_first', 'id', 'user',  'permohonan_uji', 'samples', 'laboratoriums')); */
  }

  public function daily($date_from = null, $date_to = null)
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
    // dd($laboratoriummethods);
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


  public function getSamplePagination(Request $request)
  {
    $auth = Auth()->user();


    $draw = $request->get('draw');
    $start = $request->get("start");

    $rowperpage = $request->get("length"); // Rows display per page

    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');

    $columnIndex = $columnIndex_arr[0]['column']; // Column index

    if ($columnIndex != 0) {
      $columnName = $columnName_arr[$columnIndex]['data']; // Column name
      $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    } else {
      $columnName = 'created_at';
      $columnSortOrder = 'desc';
    }


    $searchValue = $search_arr['value']; // Search value

    // Total records


    if ($auth->level == "0e6da765-0f3a-4471-9e1d-6af257e60a70") {
      $totalRecords = PermohonanUji::select('count(*) as allcount')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->count();

      $totalRecordswithFilter = PermohonanUji::select('count(*) as allcount')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->where('code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->count();

      // Fetch records
      $permohonan_uji_all = PermohonanUji::orderBy($columnName, $columnSortOrder)
        ->where('tb_permohonan_uji.code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->select('tb_permohonan_uji.*')
        ->skip($start)
        ->take($rowperpage)
        ->get();
    } else {
      $totalRecords = PermohonanUji::select('count(*) as allcount')->count();
      $totalRecordswithFilter = PermohonanUji::select('count(*) as allcount')->where('code_permohonan_uji', 'like', '%' . $searchValue . '%')->count();

      // Fetch records
      $permohonan_uji_all = PermohonanUji::orderBy($columnName, $columnSortOrder)
        ->where('tb_permohonan_uji.code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->select('tb_permohonan_uji.*')

        ->skip($start)
        ->take($rowperpage)
        ->get();
    }


    $data_arr = array();

    $no = $start + 1;

    foreach ($permohonan_uji_all as $permohonan_uji) {
      $id_permohonan_uji = $permohonan_uji->id_permohonan_uji;
      $customer_permohonan_uji = $permohonan_uji->customer_id;
      $customer_permohonan_uji = Customer::where("id_customer", $customer_permohonan_uji)->first();
      $qr = QrCode::size(100)->generate(route('scan.verification', [$permohonan_uji->id_permohonan_uji]));
      $code_permohonan_uji = $permohonan_uji->code_permohonan_uji . '<br><br>' . $qr;

      $date_permohonan_uji = Carbon::createFromFormat('Y-m-d H:i:s', $permohonan_uji->date_permohonan_uji)->format('d/m/Y');


      if ($permohonan_uji->status_pembayaran == 0) {
        # code...\
        $status_pembayaran =
          '
                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#pembayaran_' . $permohonan_uji->id_permohonan_uji . '" style="padding:5px 10px !important; width:100%;">
                        <i class="fa fa-exclamation-circle"></i> Belum Lunas
                    </button>

                    <style>
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-dialog {
                            max-width: 550px;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-content {
                            border: none;
                            border-radius: 15px;
                            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                            overflow: hidden;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-header {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border: none;
                            padding: 25px 30px;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-header .modal-title {
                            font-size: 22px;
                            font-weight: 600;
                            display: flex;
                            align-items: center;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-header .modal-title i {
                            margin-right: 10px;
                            font-size: 24px;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-header .close {
                            color: white;
                            opacity: 1;
                            text-shadow: none;
                            font-size: 28px;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-body {
                            padding: 30px;
                            background-color: #f8f9fa;
                        }
                        .payment-summary-box {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 12px;
                            padding: 20px;
                            margin-bottom: 25px;
                            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                        }
                        .payment-summary-box .label {
                            font-size: 14px;
                            opacity: 0.9;
                            margin-bottom: 5px;
                        }
                        .payment-summary-box .amount {
                            font-size: 32px;
                            font-weight: 700;
                            letter-spacing: 1px;
                        }
                        .payment-info-card {
                            background: white;
                            border-radius: 10px;
                            padding: 20px;
                            margin-bottom: 15px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        }
                        .payment-info-card label {
                            font-size: 12px;
                            font-weight: 600;
                            color: #6c757d;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            margin-bottom: 8px;
                        }
                        .payment-info-card input,
                        .payment-info-card textarea {
                            border: 2px solid #e9ecef;
                            border-radius: 8px;
                            padding: 12px 15px;
                            font-size: 15px;
                            transition: all 0.3s;
                        }
                        .payment-info-card input:focus,
                        .payment-info-card textarea:focus {
                            border-color: #667eea;
                            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .modal-footer {
                            background: white;
                            border-top: 2px solid #e9ecef;
                            padding: 20px 30px;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .btn-payment-confirm {
                            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                            border: none;
                            padding: 12px 30px;
                            font-size: 16px;
                            font-weight: 600;
                            border-radius: 8px;
                            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
                            transition: all 0.3s;
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .btn-payment-confirm:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
                        }
                        #pembayaran_' . $permohonan_uji->id_permohonan_uji . ' .btn-secondary {
                            border-radius: 8px;
                            padding: 12px 25px;
                            font-weight: 500;
                        }
                    </style>

                    <div class="modal fade" id="pembayaran_' . $permohonan_uji->id_permohonan_uji . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        <i class="fa fa-cash-register"></i> Nota Pembayaran
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="' . route('elits-permohonan-uji.payment', [$permohonan_uji->id_permohonan_uji]) . '" method="POST">
                                    <div class="modal-body">
                                        <div class="payment-summary-box text-center">
                                            <div class="label">Total yang harus dibayar</div>
                                            <div class="amount">' . rupiah($permohonan_uji->total_harga) . '</div>
                                        </div>

                                        <input type="hidden" name="_token" value="' . csrf_token() . '">

                                        <div class="payment-info-card">
                                            <label for="recipient-name"><i class="fa fa-user"></i> Telah Diterima Dari</label>
                                            <input type="text" class="form-control" name="recipient-name" value="' . $customer_permohonan_uji->name_customer . '" id="recipient-name" required>
                                        </div>

                                        <div class="payment-info-card">
                                            <label for="message-text"><i class="fa fa-map-marker-alt"></i> Alamat</label>
                                            <textarea class="form-control" name="address" id="message-text" rows="3" required>' . $customer_permohonan_uji->address_customer . '</textarea>
                                        </div>

                                        <div class="payment-info-card">
                                            <label for="tanggal-bayar"><i class="fa fa-calendar-alt"></i> Tanggal Bayar</label>
                                            <input type="date" class="form-control" name="tanggal_bayar" id="tanggal-bayar" value="' . (($permohonan_uji->tanggal_bayar ?? null) ? \Carbon\Carbon::parse($permohonan_uji->tanggal_bayar)->format('Y-m-d') : date('Y-m-d')) . '" required>
                                        </div>

                                        <div class="alert alert-info" style="border-radius: 8px; border-left: 4px solid #17a2b8;">
                                            <i class="fa fa-info-circle"></i> <small>Pastikan data sudah benar sebelum konfirmasi pembayaran</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            <i class="fa fa-times"></i> Batal
                                        </button>
                                        <button type="submit" class="btn btn-success btn-payment-confirm">
                                            <i class="fa fa-check-circle"></i> Konfirmasi Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                ';

        $btn_detail_sample = '';
        $btn_edit = '';
        $btn_delete = '';

        if (getAction('read')) {
          $btn_detail_sample = '<a class="dropdown-item" href="' . route('elits-samples.index', [$permohonan_uji->id_permohonan_uji]) . '">Lihat Daftar Sampel</a>';
        }

        if (getAction('update')) {
          $btn_edit = '<a class="dropdown-item" href="' . route('elits-permohonan-uji.edit', [$permohonan_uji->id_permohonan_uji]) . '">Edit</a>';
        }

        if (getAction('delete')) {
          $btn_delete = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $permohonan_uji->id_permohonan_uji  . '" data-nama="' . $permohonan_uji->customer->name_customer . '" title="Hapus">Hapus</a>';
        }

        $action = '

                <div class="dropdown show">
                    <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Aksi
                    </a>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        ' . $btn_detail_sample . '
                        ' . $btn_edit . '
                        ' . $btn_delete . '
                    </div>
                </div>';


        // $print='

        // <div class="dropdown show">
        //     <a class="btn btn-success dropdown-toggle" href="#" role="button" id="dropdownPrintLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        //         <i class="fa fa-print" aria-hidden="true"></i> Cetak
        //     </a>

        //     <div class="dropdown-menu" aria-labelledby="dropdownPrintLink">
        //        <a class="dropdown-item" href="'.route('elits-release.permintaan-pemeriksaan',[$permohonan_uji->id_permohonan_uji]).'">Cetak Permintaan Pemeriksaan</a>
        //     </div>
        // </div>
        // ';

      } else {
        $status_pembayaran =
          '
                    <button type="button" class="btn btn-outline-success" >
                        Terbayar
                    </button>
                ';
      }

      $btn_detail_sample = '';
      $btn_edit = '';
      $btn_delete = '';

      if (getAction('read')) {
        $btn_detail_sample = '<a class="dropdown-item" href="' . route('elits-samples.index', [$permohonan_uji->id_permohonan_uji]) . '">Lihat Daftar Sampel</a>';
      }

      if (getAction('update')) {
        $btn_edit = '<a class="dropdown-item" href="' . route('elits-permohonan-uji.edit', [$permohonan_uji->id_permohonan_uji]) . '">Edit</a>';
      }

      if (getAction('delete')) {
        $btn_delete = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $permohonan_uji->id_permohonan_uji  . '" data-nama="' . $permohonan_uji->customer->name_customer . '" title="Hapus">Hapus</a>';
      }

      $action = '

            <div class="dropdown show">
                <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Aksi
                </a>

                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                ' . $btn_detail_sample . '
                ' . $btn_edit . '
                ' . $btn_delete . '
                </div>
            </div>';


      $print = '

            <div class="dropdown show">
                <a class="btn btn-success dropdown-toggle" href="#" role="button" id="dropdownPrintLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-print" aria-hidden="true"></i> Cetak
                </a>

                <div class="dropdown-menu" aria-labelledby="dropdownPrintLink">

                    <a class="dropdown-item" href="' . route('elits-release.permintaan-pemeriksaan', [$permohonan_uji->id_permohonan_uji]) . '">Cetak Permintaan Pemeriksaan</a>
                    <a class="dropdown-item" href="' . route('elits-persuratan.nota.kesmas', $permohonan_uji->id_permohonan_uji) . '">Cetak Nota</a>

                </div>
            </div>
            ';






      $data_arr[] = array(
        "number" => $no,
        "code_permohonan_uji" => $code_permohonan_uji,
        "id_permohonan_uji" => $id_permohonan_uji,
        "action" => $action,
        "print" => $print,
        'status_pembayaran' => $status_pembayaran,
        // "user_samples" => $user_samples,
        "customer_permohonan_uji" => $customer_permohonan_uji->name_customer,
        "date_permohonan_uji" => $date_permohonan_uji,
      );
      $no++;
    }




    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordswithFilter,
      "aaData" => $data_arr
    );

    echo json_encode($response);
    exit;

    // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));


  }

  public function getSamplePagination2(Request $request)
  {


    $auth = Auth()->user();

    $draw = $request->get('draw');
    $start = $request->get("start");

    $rowperpage = $request->get("length"); // Rows display per page

    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');

    $columnIndex = $columnIndex_arr[0]['column']; // Column index

    if ($columnIndex != 0) {
      $columnName = $columnName_arr[$columnIndex]['data']; // Column name
      $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    } else {
      $columnName = 'created_at';
      $columnSortOrder = 'desc';
    }


    $searchValue = $search_arr['value']; // Search value

    // Total records


    if ($auth->level == "0e6da765-0f3a-4471-9e1d-6af257e60a70") {
      $totalRecords = PermohonanUji::select('count(*) as allcount')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->count();

      $totalRecordswithFilter = PermohonanUji::select('count(*) as allcount')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->where('code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->count();

      // Fetch records
      $permohonan_uji_all = PermohonanUji::orderBy($columnName, $columnSortOrder)
        ->where('tb_permohonan_uji.code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->where('tb_permohonan_uji.status', '=', '0')
        ->select('tb_permohonan_uji.*')
        ->skip($start)
        ->take($rowperpage)
        ->get();
    } else {
      $totalRecords = PermohonanUji::select('count(*) as allcount')->count();
      $totalRecordswithFilter = PermohonanUji::select('count(*) as allcount')->where('code_permohonan_uji', 'like', '%' . $searchValue . '%')->count();

      // Fetch records
      $permohonan_uji_all = PermohonanUji::orderBy($columnName, $columnSortOrder)
        ->where('tb_permohonan_uji.code_permohonan_uji', 'like', '%' . $searchValue . '%')
        ->select('tb_permohonan_uji.*')

        ->skip($start)
        ->take($rowperpage)
        ->get();
    }


    $data_arr = array();

    $no = $start + 1;

    foreach ($permohonan_uji_all as $permohonan_uji) {
      $id_permohonan_uji = $permohonan_uji->id_permohonan_uji;
      $customer_permohonan_uji = $permohonan_uji->customer_id;
      $customer_permohonan_uji = Customer::where("id_customer", $customer_permohonan_uji)->first();
      $qr = QrCode::size(100)->generate($permohonan_uji->code_permohonan_uji);
      $code_permohonan_uji = $permohonan_uji->code_permohonan_uji . '<br><br>' . $qr;

      $date_create = Carbon::createFromFormat('Y-m-d H:i:s', $permohonan_uji->created_at)->format('d/m/Y');



      if ($permohonan_uji->status == '0') {


        $status =
          '
                        <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#exampleModalCenter_' . $permohonan_uji->id_permohonan_uji . '">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                            Proses Persiapan Sampling
                        </button>

                        <div class="modal fade" id="exampleModalCenter_' . $permohonan_uji->id_permohonan_uji . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                        <form action="' . route('elits-permohonan-uji.setPersiapanSample', [$permohonan_uji->id_permohonan_uji]) . '" method="POST">
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle"> Proses Persiapan Sampling</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">

                                        <h5>
                                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                                        </h5>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" name="defaultCheck1" type="checkbox" value="" required id="defaultCheck1">
                                                <label class="form-check-label" for="defaultCheck1">
                                                Penyiapan Alat dan bahan 1
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" name="defaultCheck2" type="checkbox" value="" required id="defaultCheck2">
                                                <label class="form-check-label" for="defaultCheck2">
                                                Penyiapan Alat dan bahan 2
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" name="defaultCheck3" type="checkbox" value="" required id="defaultCheck3">
                                                <label class="form-check-label" for="defaultCheck3">
                                                Penyiapan Alat dan bahan 2
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" name="defaultCheck4" type="checkbox" value="" required id="defaultCheck4">
                                                <label class="form-check-label" for="defaultCheck4">
                                                Penyiapan Alat dan bahan 4
                                                </label>
                                            </div>
                                        </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <input type="submit" class="btn btn-primary" value="Submit"/>
                                </div>
                            </div>
                        </div>
                         </form>
                        </div>


                    ';
      } else if ($permohonan_uji->status == '1') {

        if (isset($permohonan_uji->packet_sampling)) {
          $packet = Packet::where('id_packet', '=', $permohonan_uji->packet_sampling)
            ->first();


          if ($packet->name_packet == "Pencahayaan") {
            $status =
              '<a href="/elits-pencahayaan/' . $permohonan_uji->id_permohonan_uji . '">
                            <button type="button" class="btn btn-outline-primary">
                                <i class="fa fa-shower" aria-hidden="true"></i>
                                Proses Pembacaan Data
                            </button>
                        </a>';
          } else if ($packet->name_packet == "Kebisingan") {
            $status =
              '<a href="/elits-kebisingan/' . $permohonan_uji->id_permohonan_uji . '">
                            <button type="button" class="btn btn-outline-primary">
                                <i class="fa fa-assistive-listening-systems" aria-hidden="true"></i>
                                Proses Pembacaan Data
                            </button>
                        </a>';
          } else {
            // $status=
            // '<a href="/elits-sampling/'.$permohonan_uji->id_permohonan_uji.'">
            //     <button type="button" class="btn btn-outline-primary">
            //         <i class="fa fa-filter" aria-hidden="true"></i>
            //         Proses Sampling
            //     </button>
            // </a>';

            $status =
              '




                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#exampleModalCenter_' . $permohonan_uji->id_permohonan_uji . '">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                                Proses Sampling
                            </button>

                            <div class="modal fade" id="exampleModalCenter_' . $permohonan_uji->id_permohonan_uji . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                            <form action="' . route('elits-permohonan-uji.setPersiapanSample', [$permohonan_uji->id_permohonan_uji]) . '" method="POST">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle"> Proses Sampling</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">

                                            <h5>
                                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                                            </h5>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="defaultCheck1" type="checkbox" value="" required id="defaultCheck1">
                                                    <label class="form-check-label" for="defaultCheck1">
                                                    Penyiapan Alat dan bahan 1
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="defaultCheck2" type="checkbox" value="" required id="defaultCheck2">
                                                    <label class="form-check-label" for="defaultCheck2">
                                                    Penyiapan Alat dan bahan 2
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="defaultCheck3" type="checkbox" value="" required id="defaultCheck3">
                                                    <label class="form-check-label" for="defaultCheck3">
                                                    Penyiapan Alat dan bahan 2
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="defaultCheck4" type="checkbox" value="" required id="defaultCheck4">
                                                    <label class="form-check-label" for="defaultCheck4">
                                                    Penyiapan Alat dan bahan 4
                                                    </label>
                                                </div>
                                            </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <input type="submit" class="btn btn-primary" value="Submit"/>
                                    </div>
                                </div>
                            </div>
                            </form>
                            </div>



                        ';
          }
        }
      } else if ($permohonan_uji->status == '2') {


        $all_sample = Sample::where('permohonan_uji_id', $permohonan_uji->id_permohonan_uji)->get();
        $sample_text = '';
        foreach ($all_sample as $sample) {
          $sample_text = $sample_text . '<a class="dropdown-item" href="/elits-deligations/' . $sample->id_samples . '">' . $sample->codesample_samples . '</a>';
        }

        $status =
          '
                <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Proses Delegation
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $sample_text . '
                </div>';
      }
      //     else if($samples->status=='2'){
      //         $status=
      //         '<a href="/elits-samples/analys/'.$samples->id_samples.'">
      //             <button type="button" class="btn btn-outline-light">
      //                 <i class="fa fa-users" aria-hidden="true"></i>
      //                 Proses Invoice
      //             </button>
      //         </a>';


      //     }else{
      //         $status=
      //         '<a href="/elits-release/printLHU/'.$samples->id_samples.'">
      //             <button type="button" class="btn btn-outline-success">
      //                 <i class="fa fa-users" aria-hidden="true"></i>
      //                 Rilis Sample
      //             </button>
      //         </a>';


      //     }


      $data_arr[] = array(
        "number" => $no,
        "code_permohonan_uji" => $code_permohonan_uji,
        "id_permohonan_uji" => $id_permohonan_uji,
        // "user_samples" => $user_samples,
        "customer_permohonan_uji" => $customer_permohonan_uji->name_customer,
        "status" => $status,
        "date_create" => $date_create,
      );
      $no++;
    }




    $response = array(
      "draw" => intval($draw),
      "iTotalRecords" => $totalRecords,
      "iTotalDisplayRecords" => $totalRecordswithFilter,
      "aaData" => $data_arr
    );

    echo json_encode($response);
    exit;

    // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));


  }

  public function label(Request $request)
  {
    if (request()->ajax()) {
      $datas = PermohonanUji::orderBy('date_permohonan_uji', 'desc')
        ->whereNull('deleted_at')
        ->get();

      return Datatables::of($datas)
        ->addColumn('set_checkbox', function ($data) {
          return '<input type="checkbox" name="permohonan_uji[]" value="' . $data->id_permohonan_uji . '" class="permohonan-uji-checkbox">';
        })
        ->addColumn('code_permohonan_uji', function ($data) {
          return '<div class="text-center">' . ($data->code_permohonan_uji ?? '-') . '</div>';
        })
        ->addColumn('customer_name', function ($data) {
          $customer = $data->customer;
          return $customer ? $customer->name_customer : '-';
        })
        ->addColumn('date_permohonan_uji', function ($data) {
          $date = Carbon::createFromFormat("Y-m-d H:i:s", $data->date_permohonan_uji)->format("d-m-Y, H:i:s");
          return '<div class="text-center">' . $date . '</div>';
        })
        ->rawColumns(['set_checkbox', 'code_permohonan_uji', 'date_permohonan_uji'])
        ->addIndexColumn()
        ->make(true);
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji.label.index');
  }

  public function selectSamplesForLabel($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
      ->with('customer')
      ->first();

    if (!$permohonan_uji) {
      return redirect()->back()->with('error', 'Permohonan uji tidak ditemukan');
    }

    // Get all samples for this permohonan uji
    $samples = Sample::where('permohonan_uji_id', $id)
      ->whereNull('deleted_at')
      ->with('sampleType')
      ->get();

    return view('masterweb::module.admin.laboratorium.permohonan-uji.label.select-samples', compact('permohonan_uji', 'samples'));
  }

  public function printLabel(Request $request)
  {
    $samples_param = $request->samples;

    // Handle both array and string formats
    if (is_array($samples_param)) {
      $id_samples_arr = $samples_param;
    } else if (is_string($samples_param)) {
      $id_samples_arr = explode(",", $samples_param);
    } else {
      return redirect()->back()->with('error', 'Pilih minimal 1 sampel untuk dicetak');
    }

    // Filter out empty values
    $id_samples_arr = array_filter($id_samples_arr, function($value) {
      return !empty($value);
    });

    if (empty($id_samples_arr)) {
      return redirect()->back()->with('error', 'Pilih minimal 1 sampel untuk dicetak');
    }

    $samples_data = [];
    $i = 0;

    foreach ($id_samples_arr as $id_sample) {
      $sample = Sample::where('id_samples', $id_sample)
        ->whereNull('deleted_at')
        ->first();

      if ($sample) {
        // Generate QR code for each sample (using id_samples)
        try {
          $qrCode = QrCode::size(100)->generate($sample->id_samples);
          $samples_data[$i] = [
            'id_samples' => $sample->id_samples,
            'codesample_samples' => $sample->codesample_samples ?? '-',
            'qr_code' => $qrCode
          ];
          $i++;
        } catch (\Exception $e) {
          // If QR generation fails, still add the sample without QR
          $samples_data[$i] = [
            'id_samples' => $sample->id_samples,
            'codesample_samples' => $sample->codesample_samples ?? '-',
            'qr_code' => null
          ];
          $i++;
        }
      }
    }

    if (count($samples_data) == 0) {
      return redirect()->back()->with('error', 'Tidak ada sampel yang valid untuk dicetak');
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji.label.print-label', compact('samples_data'));
  }

  /**
   * Form input nomor lab Kesmas: satu nomor per (jenis sampel × laboratorium).
   */
  public function nomerLabForm($id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
      ->whereNull('deleted_at')
      ->with(['customer'])
      ->firstOrFail();

    $year = (int) date('Y');
    $lastLabNumber = NomerLabSequence::resolveMaxIssuedLabNumber($year);
    $nextPreview = $lastLabNumber + 1;

    $rows = $this->buildKesmasNomerLabRows($id);
    $cursor = $lastLabNumber;
    $rows = $rows->map(function ($row) use (&$cursor) {
      if (!empty($row->nomer_lab)) {
        $row->nomer_lab_default = (int) $row->nomer_lab;
        return $row;
      }
      $cursor++;
      $row->nomer_lab_default = $cursor;
      return $row;
    });

    return view('masterweb::module.admin.laboratorium.permohonan-uji.nomer-lab', compact(
      'permohonan_uji',
      'rows',
      'lastLabNumber',
      'nextPreview',
      'year',
      'id'
    ));
  }

  /**
   * Simpan nomor lab Kesmas per kombinasi jenis sampel × laboratorium, lalu propagate ke tb_lab_num.
   */
  public function nomerLabStore(Request $request, $id)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
      ->whereNull('deleted_at')
      ->firstOrFail();

    $items = $request->input('items', []);
    if (!is_array($items) || count($items) === 0) {
      return redirect()
        ->route('elits-permohonan-uji.nomer-lab', [$id])
        ->with('error', 'Tidak ada data nomor lab untuk disimpan.');
    }

    $year = (int) ($request->input('year') ?: date('Y'));
    $saved = 0;
    $cleared = 0;
    $maxSavedLab = 0;

    DB::beginTransaction();
    try {
      foreach ($items as $item) {
        $sampleTypeId = $item['sample_type_id'] ?? null;
        $labId = $item['laboratorium_id'] ?? null;
        if (!$sampleTypeId || !$labId) {
          continue;
        }

        $raw = preg_replace('/\D/', '', (string) ($item['nomer_lab'] ?? ''));
        $kesmasKey = [
          'permohonan_uji_id' => $id,
          'laboratorium_id' => $labId,
          'sample_type_id' => $sampleTypeId,
        ];

        if ($raw === '' || (int) $raw < 1) {
          $existing = NomerLabKesmas::withTrashed()->where($kesmasKey)->first();
          if ($existing && !$existing->trashed()) {
            $existing->delete();
            $cleared++;
          }
          continue;
        }

        $nomerLab = (int) $raw;
        $existing = NomerLabKesmas::withTrashed()->where($kesmasKey)->first();
        if ($existing) {
          if ($existing->trashed()) {
            $existing->restore();
          }
          $existing->nomer_lab = $nomerLab;
          $existing->year = $year;
          $existing->save();
        } else {
          NomerLabKesmas::create(array_merge($kesmasKey, [
            'id' => Uuid::uuid4()->toString(),
            'nomer_lab' => $nomerLab,
            'year' => $year,
          ]));
        }

        $this->propagateKesmasNomerLabToLabNum($id, $labId, $sampleTypeId, $nomerLab, $year);
        $saved++;
        $maxSavedLab = max($maxSavedLab, $nomerLab);
      }

      if ($maxSavedLab > 0) {
        NomerLabSequence::raiseLastNumberToAtLeast($maxSavedLab, $year);
      }

      DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('Gagal simpan nomer lab kesmas dari panel permohonan: ' . $e->getMessage(), [
        'permohonan_uji_id' => $id,
      ]);

      return redirect()
        ->route('elits-permohonan-uji.nomer-lab', [$id])
        ->with('error', 'Gagal menyimpan nomor lab: ' . $e->getMessage());
    }

    $msg = "Nomor lab tersimpan: {$saved}";
    if ($cleared > 0) {
      $msg .= ", dihapus: {$cleared}";
    }

    return redirect()
      ->route('elits-permohonan-uji.nomer-lab', [$id])
      ->with('status', $msg . '.');
  }

  /**
   * Distinct kombinasi (jenis sampel × lab) dari sampel permohonan + nomor yang sudah ada.
   *
   * @return \Illuminate\Support\Collection
   */
  protected function buildKesmasNomerLabRows(string $permohonanUjiId)
  {
    $combos = DB::table('tb_samples as s')
      ->join('tb_sample_method as sm', function ($j) {
        $j->on('sm.sample_id', '=', 's.id_samples')->whereNull('sm.deleted_at');
      })
      ->join('ms_laboratorium as l', function ($j) {
        $j->on('l.id_laboratorium', '=', 'sm.laboratorium_id')->whereNull('l.deleted_at');
      })
      ->join('ms_sample_type as st', function ($j) {
        $j->on('st.id_sample_type', '=', 's.typesample_samples')->whereNull('st.deleted_at');
      })
      ->where('s.permohonan_uji_id', $permohonanUjiId)
      ->whereNull('s.deleted_at')
      ->select(
        's.typesample_samples as sample_type_id',
        'st.name_sample_type',
        'st.code_sample_type',
        'sm.laboratorium_id',
        'l.nama_laboratorium',
        'l.kode_laboratorium',
        DB::raw('COUNT(DISTINCT s.id_samples) as sample_count')
      )
      ->groupBy(
        's.typesample_samples',
        'st.name_sample_type',
        'st.code_sample_type',
        'sm.laboratorium_id',
        'l.nama_laboratorium',
        'l.kode_laboratorium'
      )
      ->orderBy('st.name_sample_type')
      ->orderBy('l.nama_laboratorium')
      ->get();

    $existing = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiId)
      ->get()
      ->keyBy(function ($row) {
        return $row->sample_type_id . '|' . $row->laboratorium_id;
      });

    return $combos->map(function ($row) use ($existing) {
      $kode = strtoupper((string) ($row->kode_laboratorium ?? ''));
      $seg = '00';
      if (in_array($kode, ['KIM', 'KMA', 'FKA'], true)) {
        $seg = '01';
      } elseif ($kode === 'MBI' || stripos((string) $row->nama_laboratorium, 'mikro') !== false) {
        $seg = '02';
      }

      $key = $row->sample_type_id . '|' . $row->laboratorium_id;
      $assigned = $existing->get($key);

      return (object) [
        'sample_type_id' => $row->sample_type_id,
        'name_sample_type' => $row->name_sample_type,
        'code_sample_type' => $row->code_sample_type,
        'laboratorium_id' => $row->laboratorium_id,
        'nama_laboratorium' => $row->nama_laboratorium,
        'kode_laboratorium' => $row->kode_laboratorium,
        'lab_seg' => $seg,
        'sample_count' => (int) $row->sample_count,
        'nomer_lab' => $assigned ? (int) $assigned->nomer_lab : null,
        'year' => $assigned ? (int) $assigned->year : null,
      ];
    });
  }

  /**
   * Propagasi nomor lab ke semua tb_lab_num sampel dalam kelompok yang sama.
   */
  protected function propagateKesmasNomerLabToLabNum(
    string $permohonanUjiId,
    string $labId,
    string $sampleTypeId,
    int $nomerLab,
    int $year
  ): void {
    $groupSampleIds = DB::table('tb_samples as s')
      ->join('tb_sample_method as sm', function ($j) use ($labId) {
        $j->on('sm.sample_id', '=', 's.id_samples')
          ->where('sm.laboratorium_id', $labId)
          ->whereNull('sm.deleted_at');
      })
      ->where('s.permohonan_uji_id', $permohonanUjiId)
      ->where('s.typesample_samples', $sampleTypeId)
      ->whereNull('s.deleted_at')
      ->pluck('s.id_samples')
      ->unique()
      ->values();

    foreach ($groupSampleIds as $sampleId) {
      $existingLabNums = LabNum::where('sample_id', $sampleId)
        ->where('lab_id', $labId)
        ->get();

      if ($existingLabNums->isEmpty()) {
        LabNum::create([
          'sample_id' => $sampleId,
          'sample_type_id' => $sampleTypeId,
          'lab_id' => $labId,
          'permohonan_uji_id' => $permohonanUjiId,
          'lab_number' => $nomerLab,
          'year_lab_num' => $year,
          'mount_lab_num' => (int) date('m'),
        ]);
      } else {
        foreach ($existingLabNums as $existingLabNum) {
          $existingLabNum->lab_number = $nomerLab;
          $existingLabNum->year_lab_num = $year;
          $existingLabNum->save();
        }
      }
    }
  }
}