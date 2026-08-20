<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\MethodSampleTypePrice;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Unit;





class LaboratoriumMethodManagement extends Controller
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
    $user = Auth()->user();
    $methods = Method::orderByRaw('COALESCE(orderlist_method, 999999) ASC')
      ->orderBy('params_method', 'ASC')
      ->get();

    return view('masterweb::module.admin.laboratorium.method.list', compact('user', 'methods'));
  }

  /** Default sumber urutan: Air Higiene */
  const DEFAULT_ORDER_SAMPLE_TYPE_ID = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';

  /**
   * Halaman pengaturan urutan method.
   */
  public function reorderPage(Request $request)
  {
    $user = Auth()->user();
    $defaultSampleTypeId = self::DEFAULT_ORDER_SAMPLE_TYPE_ID;

    $sampleType = SampleType::where('id_sample_type', $defaultSampleTypeId)->first();
    $methods = Method::orderByRaw('COALESCE(orderlist_method, 999999) ASC')
      ->orderBy('params_method', 'ASC')
      ->get();

    // Map urutan dari detail jenis sarana (untuk badge/info)
    $sampleTypeOrderMap = [];
    if ($sampleType) {
      $details = \Smt\Masterweb\Models\SampleTypeDetail::where('sample_type_id', $defaultSampleTypeId)
        ->whereNull('deleted_at')
        ->orderBy('is_tambahan', 'asc')
        ->orderByRaw('COALESCE(orderlist_sample_type_detail, 999999) ASC')
        ->get(['method_id', 'is_tambahan', 'orderlist_sample_type_detail']);

      $pos = 1;
      foreach ($details as $detail) {
        if (!$detail->method_id || isset($sampleTypeOrderMap[$detail->method_id])) {
          continue;
        }
        $sampleTypeOrderMap[$detail->method_id] = [
          'order' => $pos++,
          'is_tambahan' => (int) $detail->is_tambahan,
          'detail_order' => $detail->orderlist_sample_type_detail,
        ];
      }
    }

    return view('masterweb::module.admin.laboratorium.method.reorder', compact(
      'user',
      'methods',
      'sampleType',
      'sampleTypeOrderMap',
      'defaultSampleTypeId'
    ));
  }

  /**
   * Simpan urutan method ke ms_method.orderlist_method.
   */
  public function reorder(Request $request)
  {
    $orders = $request->post('orders');
    $ids = $request->post('ids');

    if ((!is_array($orders) || count($orders) === 0) && (!is_array($ids) || count($ids) === 0)) {
      return response()->json(['status' => false, 'pesan' => 'Tidak ada data urutan'], 200);
    }

    \DB::beginTransaction();
    try {
      if (is_array($orders) && count($orders) > 0) {
        $sort = 1;
        foreach ($orders as $row) {
          if (!isset($row['id'])) {
            continue;
          }
          Method::where('id_method', $row['id'])
            ->update(['orderlist_method' => $sort++]);
        }
      } else {
        $sort = 1;
        foreach ($ids as $id) {
          Method::where('id_method', $id)
            ->update(['orderlist_method' => $sort++]);
        }
      }

      \DB::commit();
      return response()->json(['status' => true, 'pesan' => 'Urutan berhasil disimpan']);
    } catch (\Exception $e) {
      \DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan urutan: ' . $e->getMessage()], 200);
    }
  }

  /**
   * Salin urutan dari detail jenis sarana ke ms_method.
   * - fill_only_empty=1: hanya isi yang orderlist_method masih kosong
   * - fill_only_empty=0: tulis ulang semua dari jenis sarana, sisanya di belakang
   */
  public function syncOrderFromSampleType(Request $request)
  {
    $sampleTypeId = $request->post('sample_type_id') ?: self::DEFAULT_ORDER_SAMPLE_TYPE_ID;
    $fillOnlyEmpty = (string) $request->post('fill_only_empty', '1') === '1';

    $sampleType = SampleType::where('id_sample_type', $sampleTypeId)->first();
    if (!$sampleType) {
      return response()->json(['status' => false, 'pesan' => 'Jenis sarana tidak ditemukan'], 200);
    }

    \DB::beginTransaction();
    try {
      $details = \Smt\Masterweb\Models\SampleTypeDetail::where('sample_type_id', $sampleTypeId)
        ->whereNull('deleted_at')
        ->whereNotNull('method_id')
        ->orderBy('is_tambahan', 'asc')
        ->orderByRaw('COALESCE(orderlist_sample_type_detail, 999999) ASC')
        ->get(['method_id']);

      $orderedMethodIds = [];
      foreach ($details as $detail) {
        if (!$detail->method_id || isset($orderedMethodIds[$detail->method_id])) {
          continue;
        }
        $orderedMethodIds[$detail->method_id] = true;
      }
      $orderedMethodIds = array_keys($orderedMethodIds);

      $updatedFromSampleType = 0;
      $appended = 0;

      if ($fillOnlyEmpty) {
        // Sisipkan yang kosong di belakang urutan yang sudah ada, tetap ikuti urutan jenis sarana
        $next = ((int) Method::max('orderlist_method')) + 1;
        if ($next < 1) {
          $next = 1;
        }

        foreach ($orderedMethodIds as $methodId) {
          $affected = Method::where('id_method', $methodId)
            ->whereNull('orderlist_method')
            ->update(['orderlist_method' => $next]);
          if ($affected) {
            $updatedFromSampleType++;
            $next++;
          }
        }

        foreach (
          Method::whereNull('orderlist_method')
            ->orderBy('params_method', 'asc')
            ->get(['id_method']) as $method
        ) {
          Method::where('id_method', $method->id_method)
            ->update(['orderlist_method' => $next++]);
          $appended++;
        }
      } else {
        // Reset: jenis sarana dulu (1..N), sisanya di belakang
        $order = 1;
        foreach ($orderedMethodIds as $methodId) {
          Method::where('id_method', $methodId)
            ->update(['orderlist_method' => $order++]);
          $updatedFromSampleType++;
        }

        foreach (
          Method::whereNotIn('id_method', $orderedMethodIds)
            ->orderByRaw('COALESCE(orderlist_method, 999999) ASC')
            ->orderBy('params_method', 'ASC')
            ->get(['id_method']) as $method
        ) {
          Method::where('id_method', $method->id_method)
            ->update(['orderlist_method' => $order++]);
          $appended++;
        }
      }

      \DB::commit();

      $modeLabel = $fillOnlyEmpty ? 'hanya yang kosong' : 'semua diganti dari jenis sarana';
      return response()->json([
        'status' => true,
        'pesan' => 'Urutan disalin dari "' . $sampleType->name_sample_type . '" (' . $modeLabel . '). '
          . $updatedFromSampleType . ' dari jenis sarana, ' . $appended . ' disesuaikan di belakang.',
      ]);
    } catch (\Exception $e) {
      \DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal menyalin urutan: ' . $e->getMessage()], 200);
    }
  }

  public function load($id, $id_samples)
  {
    //get auth user
    //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
    //get auth user
    $user = Auth()->user();
    $method = Method::where('id_method', $id)->first();
    $model_name = substr('Smt\Masterweb\Models\Module\ ', 0, 28) . $method->model_method;
    $model_return_name = substr('Smt\Masterweb\Models\Result\ ', 0, 28) . $method->model_method . "Result";



    $form = $model_name::where('id_samples', $id_samples)->where('id_method', $id)->first();

    $form_result = null;

    if ($form != null) {
      $form_result = $model_return_name::where('id_' . $method->model_method, $form->id)->orderBy('order', 'asc')->get();
    }




    return view('masterweb::module.admin.laboratorium.method.module.' . $method->module_method, compact('form', 'form_result', 'id', 'method'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //get auth user

    $user = Auth()->user();

    $all_laboratorium = Laboratorium::all();

    $units = Unit::all();

    $sampletypes = SampleType::whereNull('deleted_at')->orderBy('name_sample_type')->get();
    $sample_type_prices = [];

    return view('masterweb::module.admin.laboratorium.method.add', compact('user', 'all_laboratorium', 'units', 'sampletypes', 'sample_type_prices'));
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
    $data = $request->all();

    $method = new Method;
    $uuid4 = Uuid::uuid4();

    $method->id_method = $uuid4->toString();
    $method->params_method = $request->post('params_method');
    $method->name_method = $request->post('name_method');
    $method->keterangan_default = $request->post('keterangan_default') ?? null;
    $method->price_bahan = $request->post('price_bahan');
    $method->price_sarana = $request->post('price_sarana');
    $method->berhubungan_kesehatan = $request->post('berhubungan_kesehatan');
    $method->jenis_parameter_kimia = $request->post('jenis_parameter_kimia');
    $method->price_jasa = $request->post('price_jasa');
    $method->price_total_method = $request->post('price_total_method');
    $method->unit_method = $request->post('unit');
    $method->name_report_method = $request->post('name_report_method');
    $method->id_pdam_method = $request->post('id_pdam_method');
    $method->is_ready = $request->post('is_ready');
    $method->is_option = $request->has('is_option') ? 1 : 0;
    $method->option = $request->post('option') ?? null;
    $method->save();

    $this->syncMethodSampleTypePrices($method->id_method, $request);

    if (isset($data["laboratoriumAttributes"])) {
      for ($i = 0; $i < count($data["laboratoriumAttributes"]); $i++) {
        $methoddetail = new LaboratoriumMethod;
        $methoddetail->id_laboratorium_method = Uuid::uuid4();
        $methoddetail->method_id = $method->id_method;
        $methoddetail->laboratorium_id = $data["laboratoriumAttributes"][$i];
        $methoddetail->save();
      }
    }

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json([
        'status' => true,
        'pesan' => 'Parameter berhasil disimpan!',
        'method_id' => $method->id_method,
        'params_method' => $method->params_method,
      ]);
    }

    return redirect()->route('elits-methods.index')->with(['status' => 'Method succesfully inserted']);

    //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);

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
    $method = Method::where('id_method', $id)->first();

    // dd($method);
    $all_laboratorium = Laboratorium::all();
    $units = Unit::all();


    $method_laboratorium_details = LaboratoriumMethod::where('method_id', $id)->get();

    $sampletypes = SampleType::whereNull('deleted_at')->orderBy('name_sample_type')->get();
    $sample_type_prices = MethodSampleTypePrice::where('method_id', $id)->pluck('price', 'sample_type_id')->toArray();

    $returnAfterPath = $this->normalizeReturnPathAfterMethodUpdate(request()->query('return_path'));

    return view('masterweb::module.admin.laboratorium.method.edit', compact('auth', 'method', 'id', 'units', 'all_laboratorium', 'method_laboratorium_details', 'sampletypes', 'sample_type_prices', 'returnAfterPath'));
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


    // print_r($data);



    $data = $request->all();
    $method = Method::find($id);
    $method->params_method = $request->post('params_method');
    $method->name_method = $request->post('name_method');
    $method->keterangan_default = $request->post('keterangan_default') ?? null;
    $method->price_bahan = $request->post('price_bahan');
    $method->price_sarana = $request->post('price_sarana');
    $method->berhubungan_kesehatan = $request->post('berhubungan_kesehatan');
    $method->jenis_parameter_kimia = $request->post('jenis_parameter_kimia');
    $method->price_jasa = $request->post('price_jasa');
    $method->price_total_method = $request->post('price_total_method');
    $method->unit_method = $request->post('unit');
    $method->id_pdam_method = $request->post('id_pdam_method');
    $method->is_ready = $request->post('is_ready');
    $method->is_option = $request->has('is_option') ? 1 : 0;
    $method->option = $request->post('option') ?? null;
    //  $method->kadar_diperbolehkan_method = $request->post('kadar_diperbolehkan_method');
    //  $method->module_method = $request->post('module_method');
    //  $method->model_method = $request->post('model_method');
    $method->save();

    $this->syncMethodSampleTypePrices($method->id_method, $request);

    if (isset($data["laboratoriumAttributes"])) {
      $laboratorium_methods = LaboratoriumMethod::where('method_id', $id);
      if (isset($laboratorium_methods)) {
        $laboratorium_methods->delete();
      }
      for ($i = 0; $i < count($data["laboratoriumAttributes"]); $i++) {
        $packetdetail = new LaboratoriumMethod;
        $packetdetail->id_laboratorium_method = Uuid::uuid4();
        $packetdetail->method_id = $method->id_method;
        $packetdetail->laboratorium_id = $data["laboratoriumAttributes"][$i];
        $packetdetail->save();
      }
    }

    $returnPath = $this->normalizeReturnPathAfterMethodUpdate($request->input('return_after_path'));
    if ($returnPath !== null) {
      return redirect()->to($returnPath)->with(['status' => 'Method succesfully updated']);
    }

    return redirect()->route('elits-methods.index')->with(['status' => 'Method succesfully updated']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $method = Method::findOrFail($id);
    MethodSampleTypePrice::where('method_id', $id)->delete();
    $method->delete();
    return redirect()->route('elits-methods.index')->with('status', 'Data berhasil dihapus');
  }

  /**
   * Harga per jenis sampel (Kesmas): tidak membedakan laboratorium.
   */
  private function syncMethodSampleTypePrices(string $methodId, Request $request): void
  {
    MethodSampleTypePrice::where('method_id', $methodId)->delete();
    $prices = $request->input('sample_type_price', []);
    if (!is_array($prices)) {
      return;
    }
    foreach ($prices as $sampleTypeId => $raw) {
      if ($raw === null || $raw === '') {
        continue;
      }
      $price = (float) preg_replace('/[^\d.-]/', '', (string) $raw);
      if ($price < 0) {
        continue;
      }
      MethodSampleTypePrice::create([
        'method_id' => $methodId,
        'sample_type_id' => $sampleTypeId,
        'price' => $price,
      ]);
    }
  }

  /**
   * JSON: kembalikan data method beserta laboratoriums, sampletypes, harga per jenis sampel.
   * Dipakai popup edit di halaman input sampel.
   */
  public function getMethodData($id)
  {
    $method = Method::where('id_method', $id)->first();
    if (!$method) {
      return response()->json(['status' => false, 'pesan' => 'Method tidak ditemukan'], 404);
    }

    $all_laboratorium       = Laboratorium::orderBy('nama_laboratorium')->get(['id_laboratorium', 'nama_laboratorium']);
    $method_laboratorium_ids = LaboratoriumMethod::where('method_id', $id)->pluck('laboratorium_id')->toArray();
    $sampletypes            = SampleType::whereNull('deleted_at')->orderBy('name_sample_type')
                                        ->get(['id_sample_type', 'name_sample_type']);
    $sample_type_prices     = MethodSampleTypePrice::where('method_id', $id)
                                        ->pluck('price', 'sample_type_id')
                                        ->toArray();

    return response()->json([
      'status'                  => true,
      'method'                  => $method,
      'all_laboratorium'        => $all_laboratorium,
      'method_laboratorium_ids' => $method_laboratorium_ids,
      'sampletypes'             => $sampletypes,
      'sample_type_prices'      => $sample_type_prices,
    ]);
  }

  /**
   * JSON: simpan perubahan method dari popup edit halaman input sampel.
   * Logika identik dengan update(), mengembalikan JSON.
   */
  public function updateAjax($id, Request $request)
  {
    $method = Method::find($id);
    if (!$method) {
      return response()->json(['status' => false, 'pesan' => 'Method tidak ditemukan'], 404);
    }

    $method->params_method         = $request->post('params_method');
    $method->name_method           = $request->post('name_method');
    $method->keterangan_default    = $request->post('keterangan_default') ?? null;
    $method->price_bahan           = $request->post('price_bahan');
    $method->price_sarana          = $request->post('price_sarana');
    $method->berhubungan_kesehatan = $request->post('berhubungan_kesehatan');
    $method->jenis_parameter_kimia = $request->post('jenis_parameter_kimia');
    $method->price_jasa            = $request->post('price_jasa');
    $method->price_total_method    = $request->post('price_total_method');
    $method->unit_method           = $request->post('unit');
    $method->id_pdam_method        = $request->post('id_pdam_method');
    $method->is_ready              = $request->post('is_ready');
    $method->is_option             = $request->has('is_option') ? 1 : 0;
    $method->option                = $request->post('option') ?? null;
    $method->save();

    $this->syncMethodSampleTypePrices($method->id_method, $request);

    $data = $request->all();
    if (isset($data['laboratoriumAttributes'])) {
      LaboratoriumMethod::where('method_id', $id)->delete();
      foreach ((array) $data['laboratoriumAttributes'] as $labId) {
        $lm                          = new LaboratoriumMethod;
        $lm->id_laboratorium_method  = Uuid::uuid4();
        $lm->method_id               = $method->id_method;
        $lm->laboratorium_id         = $labId;
        $lm->save();
      }
    }

    $updatedSampleTypePrices = MethodSampleTypePrice::where('method_id', $id)
      ->pluck('price', 'sample_type_id')
      ->toArray();

    return response()->json([
      'status'              => true,
      'pesan'               => 'Parameter berhasil diperbarui.',
      'price_total_method'  => (int) $method->price_total_method,
      'params_method'       => $method->params_method,
      'name_method'         => $method->name_method,
      'sample_type_prices'  => $updatedSampleTypePrices,
    ]);
  }

  /**
   * Redirect setelah update method: hanya path internal di bawah /elits-samples/.
   *
   * @param  mixed  $path
   * @return string|null
   */
  private function normalizeReturnPathAfterMethodUpdate($path)
  {
    if (!is_string($path) || $path === '') {
      return null;
    }
    $path = '/' . ltrim($path, '/');
    if (strpos($path, "\n") !== false || strpos($path, "\r") !== false) {
      return null;
    }
    if (strpos($path, '//') !== false) {
      return null;
    }
    if (strpos($path, '/elits-samples/') !== 0) {
      return null;
    }

    return $path;
  }
}
