<?php

namespace Smt\Masterweb\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\BakuMutu;
use App\Http\Controllers\Controller;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\PacketDetail;
use Illuminate\Support\Facades\Validator;
use \Smt\Masterweb\Models\SampleTypeDetail;
use Illuminate\Support\Facades\DB;

class LaboratoriumSampleTypeManagement extends Controller
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
  public function index(Request $request)
  {
    if (request()->ajax()) {

      $datas = SampleType::orderBy('created_at', 'desc')
        ->latest()
        ->get();

      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['name_sample_type']), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        ->addColumn('action', function ($data) {
          $urlShow = '';
          $urlEdit = '';
          $urlHapus = '';

          /* if (getAction('read')) {
            $urlShow = '<a class="dropdown-item" href="' . route('elits-sampletypes.show', $data->id_sample_type) . '">Detail</a>';
          } */

          if (getAction('update')) {
            $urlEdit = '<a class="btn btn-outline-success btn-rounded btn-icon" href="' . route('elits-sampletypes.edit', $data->id_sample_type) . '" title="Edit"><i class="fas fa-pencil-alt"></i></a> ';
          }

          if (getAction('delete')) {
            $urlHapus = '<a class="btn btn-outline-danger btn-rounded btn-icon btn-hapus" href="javascript:void(0)" data-id="' . $data->id_sample_type . '" data-nama="' . $data->name_sample_type . '" title="Hapus"><i class="fas fa-trash"></i></a> ';
          }

          $buttonAksi = $urlEdit . $urlHapus;

          return $buttonAksi;
        })
        ->rawColumns(['action'])
        ->addIndexColumn() //increment
        ->make(true);
    }

    $user = Auth()->user();

    return view('masterweb::module.admin.laboratorium.sample-type.list', compact('user'));
  }

  public function rules($request)
  {
    // Quick create dari modal (tanpa parameter wajib)
    if (!empty($request['quick_create'])) {
      return Validator::make($request, [
        'name_sampletype' => 'required',
      ], [
        'name_sampletype.required' => 'Nama jenis sampel tidak boleh kosong!',
      ]);
    }

    $rule = [
      'name_sampletype' => 'required',
      'methodAttributes' => 'required|array|min:1'
    ];

    $pesan = [
      'name_sampletype.required' => 'Nama jenis sarana tidak boleh kosong!',
      'methodAttributes.required' => 'Parameter wajib tidak boleh kosong!',
      'methodAttributes.min' => 'Parameter wajib tidak boleh kosong!'
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $user = Auth()->user();

    return view('masterweb::module.admin.laboratorium.sample-type.add', compact('user'));
    //get all menu public
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $data = $request->all();

        $sampletype = new SampleType;
        $uuid4 = Uuid::uuid4();

        $sampletype->id_sample_type = $uuid4->toString();
        $sampletype->name_sample_type = $request->post('name_sampletype');

        $sampletype->code_sample_type = $request->post('code_sample_type');
        $simpan = $sampletype->save();

        $simpan_sampletype_detail = false;

        // Get order information from drag and drop
        $methodOrder = [];
        if (isset($data['methodOrder'])) {
          $orderData = json_decode($data['methodOrder'], true);
          if (is_array($orderData)) {
            foreach ($orderData as $item) {
              $methodOrder[$item['id']] = $item['order'];
            }
          }
        }

        $methodPlusOrder = [];
        if (isset($data['methodPlusOrder'])) {
          $orderData = json_decode($data['methodPlusOrder'], true);
          if (is_array($orderData)) {
            foreach ($orderData as $item) {
              $methodPlusOrder[$item['id']] = $item['order'];
            }
          }
        }

        if (isset($data['methodAttributes'])) {
          foreach ($data['methodAttributes'] as $key => $method) {

            $sampletype_detail = new SampleTypeDetail;
            $uuid4 = Uuid::uuid4();
            $sampletype_detail->id_sample_type_detail = $uuid4;
            $sampletype_detail->sample_type_id = $sampletype->id_sample_type;
            $sampletype_detail->method_id = $method;
            $sampletype_detail->is_tambahan = 0;
            // Use order from drag and drop if available, otherwise use array key
            $sampletype_detail->orderlist_sample_type_detail = isset($methodOrder[$method]) ? $methodOrder[$method] : ($key + 1);
            $simpan_sampletype_detail = $sampletype_detail->save();
          }
        } else {
          $simpan_sampletype_detail = true;
        }

        if (isset($data['methodPlusAttributes'])) {
          foreach ($data['methodPlusAttributes'] as $key => $method) {

            $sampletype_detail = new SampleTypeDetail;
            $uuid4 = Uuid::uuid4();
            $sampletype_detail->id_sample_type_detail = $uuid4;
            $sampletype_detail->sample_type_id = $sampletype->id_sample_type;
            $sampletype_detail->method_id = $method;
            $sampletype_detail->is_tambahan = 1;
            // Use order from drag and drop if available, otherwise use array key
            $sampletype_detail->orderlist_sample_type_detail = isset($methodPlusOrder[$method]) ? $methodPlusOrder[$method] : ($key + 1);
            $sampletype_detail->save();
          }
        }

        DB::commit();

        if ($simpan == true && $simpan_sampletype_detail == true) {
          return response()->json([
            'status' => true,
            'pesan' => !empty($data['quick_create'])
              ? 'Jenis sampel berhasil disimpan!'
              : 'Data jenis sarana berhasil disimpan!',
            'id_sample_type' => $sampletype->id_sample_type,
            'name_sample_type' => $sampletype->name_sample_type,
            'code_sample_type' => $sampletype->code_sample_type,
          ], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data jenis sarana tidak berhasil disimpan!"], 200);
        }
      } catch (\Throwable $th) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $th], 200);
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

    $sampletype = SampleType::where('id_sample_type', $id)->first();
    $packets = Packet::orderBy('name_packet')->get();

    $sampletype_details = SampleTypeDetail::where('sample_type_id', $id)
      ->where('is_tambahan', 0)
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'ms_sample_type_detail.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('ms_sample_type_detail.deleted_at');
      })
      ->orderBy('orderlist_sample_type_detail', 'asc')
      ->get();

    $sampletype_plus_details = SampleTypeDetail::where('sample_type_id', $id)
      ->where('is_tambahan', 1)
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'ms_sample_type_detail.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('ms_sample_type_detail.deleted_at');
      })
      ->orderBy('orderlist_sample_type_detail', 'asc')
      ->get();

    // sampletype_details

    return view('masterweb::module.admin.laboratorium.sample-type.edit', compact('sampletype_details', 'packets', 'sampletype', 'auth', 'id', 'sampletype_plus_details'));
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
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $data = $request->all();
        $sampletype = SampleType::find($id);
        $sampletype->name_sample_type = $request->post('name_sampletype');
        $sampletype->code_sample_type = $request->post('code_sample_type');

        $simpan = $sampletype->save();

        $sampletype_detail = SampleTypeDetail::where('sample_type_id', $id);

        if (isset($sampletype_detail)) {
          $sampletype_detail->delete();
        }

        $simpan_sampletype_detail = false;

        // Get order information from drag and drop
        $methodOrder = [];
        if (isset($data['methodOrder'])) {
          $orderData = json_decode($data['methodOrder'], true);
          if (is_array($orderData)) {
            foreach ($orderData as $item) {
              $methodOrder[$item['id']] = $item['order'];
            }
          }
        }

        $methodPlusOrder = [];
        if (isset($data['methodPlusOrder'])) {
          $orderData = json_decode($data['methodPlusOrder'], true);
          if (is_array($orderData)) {
            foreach ($orderData as $item) {
              $methodPlusOrder[$item['id']] = $item['order'];
            }
          }
        }

        if (isset($data['methodAttributes'])) {
          foreach ($data['methodAttributes'] as $key => $method) {

            $sampletype_detail = new SampleTypeDetail;
            $uuid4 = Uuid::uuid4();
            $sampletype_detail->id_sample_type_detail = $uuid4;
            $sampletype_detail->sample_type_id = $sampletype->id_sample_type;
            $sampletype_detail->method_id = $method;
            $sampletype_detail->is_tambahan = 0;
            // Use order from drag and drop if available, otherwise use array key
            $sampletype_detail->orderlist_sample_type_detail = isset($methodOrder[$method]) ? $methodOrder[$method] : ($key + 1);
            $simpan_sampletype_detail = $sampletype_detail->save();
          }
        } else {
          $simpan_sampletype_detail = true;
        }

        if (isset($data['methodPlusAttributes'])) {
          foreach ($data['methodPlusAttributes'] as $key => $method) {

            $sampletype_detail = new SampleTypeDetail;
            $uuid4 = Uuid::uuid4();
            $sampletype_detail->id_sample_type_detail = $uuid4;
            $sampletype_detail->sample_type_id = $sampletype->id_sample_type;
            $sampletype_detail->method_id = $method;
            $sampletype_detail->is_tambahan = 1;
            // Use order from drag and drop if available, otherwise use array key
            $sampletype_detail->orderlist_sample_type_detail = isset($methodPlusOrder[$method]) ? $methodPlusOrder[$method] : ($key + 1);
            $sampletype_detail->save();
          }
        }

        DB::commit();

        if ($simpan == true && $simpan_sampletype_detail == true) {
          return response()->json(['status' => true, 'pesan' => "Data jenis sarana berhasil disimpan!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data jenis sarana tidak berhasil disimpan!"], 200);
        }
      } catch (\Throwable $th) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $th], 200);
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function _destroy($id)
  {
    $sampletype = SampleType::findOrFail($id);
    $sampletype->delete();
    return redirect()->route('elits-sampletypes.index')->with('status', 'Data berhasil dihapus');
  }

  public function destroy($id)
  {
    $hapus = SampleType::where('id_sample_type', $id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data jenis sarana berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data jenis sarana tidak berhasil dihapus!"], 200);
    }
  }

  public function getSampleType(Request $request)
  {
    $search = $request->search;

    if (isset($request->param)) {
      $data = SampleType::orderby('name_sample_type', 'asc')
        ->select('id_sample_type', 'name_sample_type')
        ->where('name_sample_type', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    } else {
      if ($search == '') {
        $data = SampleType::orderby('name_sample_type', 'asc')
          ->select('id_sample_type', 'name_sample_type')
          ->limit(10)
          ->get();
      } else {
        $data = SampleType::orderby('name_sample_type', 'asc')
          ->select('id_sample_type', 'name_sample_type')
          ->where('name_sample_type', 'like', '%' . $search . '%')
          ->limit(10)
          ->get();
      }
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_sample_type,
        "text" => $item->name_sample_type
      );
    }

    return response()->json($response);
  }

  public function getbaku_mutu($id)
  {
    $user = Auth()->user();

    // if (isset($id_jenis_makanan)) {
    //   $sampletype_bakumutu_details = BakuMutu::where('sampletype_id', $id)
    //     ->where('jenis_makanan_id', $id_jenis_makanan)
    //     ->join('ms_method', function ($join) {
    //       $join->on('ms_method.id_method', '=', 'tb_baku_mutu.method_id')
    //         ->whereNull('ms_method.deleted_at')
    //         ->whereNull('tb_baku_mutu.deleted_at');
    //     })
    //     ->get();
    // } else {

    $sampletype_bakumutu_details = BakuMutu::where('sampletype_id', $id)->join('ms_method', function ($join) {
      $join->on('ms_method.id_method', '=', 'tb_baku_mutu.method_id')
        ->whereNull('ms_method.deleted_at')
        ->whereNull('tb_baku_mutu.deleted_at');
    })
      ->get();

    // dd($sampletype_bakumutu_details);
    // }

    return response()->json(
      [
        'success' => 'Ajax request submitted successfully',
        'data' => $sampletype_bakumutu_details
      ]
    );
  }


  public function getdetail_sample_type($id)
  {
    $user = Auth()->user();



    $id_pakets = explode(",", $id);

    $sampletype_details = PacketDetail::whereIn('packet_id', $id_pakets)
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'ms_packet_detail.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->get();


    $sampletype = Packet::whereIn('id_packet', $id_pakets)->get();

    $methods = array();

    foreach ($sampletype_details as $sampletype_detail) {
      array_push($methods, $sampletype_detail->id_method);
    }

    return response()->json(
      [
        'success' => 'Ajax request submitted successfully',
        'data' => $sampletype_details,
        'price' => $sampletype->sum('price_total_packet'),
        'methods' => $methods,
      ]
    );
  }


  public function setBakuMutu($id) {}
}
