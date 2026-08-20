<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Container;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\PenangananSample;
use \Smt\Masterweb\Models\Unit;



use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;
use DB;

use Mapper;


use Carbon\Carbon;


class LaboratoriumPenangananSampleManagement extends Controller
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
  public function index($id, $idlab)
  {

    // dd("");
    Carbon::setLocale('id');
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->first();


    // $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id','=',$idlab)
    //             ->where('sample_id','=',$id)
    //             ->orderBy('ms_method.created_at')
    //             ->join('ms_method', function ($join) {
    //                 $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
    //                 ->whereNull('tb_laboratorium_method.deleted_at')
    //                 ->whereNull('ms_method.deleted_at');
    //             })->get();


    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();


    return view('masterweb::module.admin.laboratorium.penanganan-sample.penanganan', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }






  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create($id)
  {
    //get auth user




    $user = Auth()->user();
    $count = Sample::count();
    $users = User::all();

    $packets = Packet::where('id_packet', '!=', '0')->orderBy('created_at')->get();

    $laboratoriums = Laboratorium::all();

    $data_methods = array();

    foreach ($laboratoriums as $laboratorium) {
      array_push(
        $data_methods,
        (object) array(
          'name' => $laboratorium->nama_laboratorium,
          'id_lab' => $laboratorium->id_laboratorium,
          'method' => array()
        )
      );
    }


    $i = 0;
    foreach ($data_methods as $data_method) {
      $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $data_method->id_lab)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })->get();
      foreach ($laboratoriummethods as $laboratoriummethod) {
        //    print_r($laboratoriummethod->params_method);
        array_push(
          $data_methods[$i]->method,
          (object) array(
            'name_method' => $laboratoriummethod->params_method,
            'id_method' => $laboratoriummethod->id_method,
            'price_method' => $laboratoriummethod->price_method
          )
        );
      }

      $i++;
    }





    $containers = Container::where('id_container', '!=', '0')->get();
    $sampletypes = SampleType::orderBy('created_at')
      ->get();

    $code = 'S.KRNGY-' . date("Ymd", time()) . '-0' . ($count + 1);





    return view('masterweb::module.admin.laboratorium.sample.add', compact('user', 'data_methods', 'containers', 'packets', 'sampletypes', 'code', 'users'));
    //get all menu public
  }




  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $id, $idlabs)
  {
    $data = $request->all();


    // print_r($data);

    $penanganan_sample = PenangananSample::where('sample_id', $id)->where('laboratorium_id', $idlabs)->first();

    if (isset($penanganan_sample)) {
      PenangananSample::where('sample_id', $id)->where('laboratorium_id', $idlabs)->delete();
    }

    $validated = $request->validate([
      'penyimpanan_sample' => ['required'],
      'date_checking' => ['required'],
      'date_done_estimation_labs' => ['required'],
      'destroyed_sample_date' => ['required']
    ]);

    // dd($data);


    $user = Auth()->user();
    $penanganan_sample = new PenangananSample;
    //uuid
    $uuid4 = Uuid::uuid4();

    $penanganan_sample->id_sample_penanganan = $uuid4->toString();
    $penanganan_sample->sample_id = $id;
    $penanganan_sample->laboratorium_id = $idlabs;
    $penanganan_sample->penyimpanan_sample = $request->post('penyimpanan_sample');
    $penanganan_sample->date_checking = Carbon::createFromFormat('d/m/Y', $request->post('date_checking'))->format('Y-m-d H:i:s');
    $penanganan_sample->date_done_estimation_labs = Carbon::createFromFormat('d/m/Y', $request->post('date_done_estimation_labs'))->format('Y-m-d H:i:s');
    $penanganan_sample->destroyed_sample_date = Carbon::createFromFormat('d/m/Y', $request->post('destroyed_sample_date'))->format('Y-m-d H:i:s');
    $penanganan_sample->penanganan_sample_date = Carbon::createFromFormat('d/m/Y', $request->post('date_checking'))->format('Y-m-d H:i:s');
    $penanganan_sample->save();

    $sample = Sample::where('id_samples', $id)->first();



    return redirect()->route('elits-samples.verification', [$id, $idlabs])->with(['status' => 'Penanganan Sampel berhasil di input']);
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
  public function edit($id, $idlab)
  {

    $penanganan_sample = PenangananSample::where('sample_id', '=', $id)
      ->where('laboratorium_id', '=', $idlab)
      ->first();

    // dd($penanganan_sample);

    Carbon::setLocale('id');
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->first();
    // $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id','=',$idlab)
    //             ->where('sample_id','=',$id)
    //             ->orderBy('ms_method.created_at')
    //             ->join('ms_method', function ($join) {
    //                 $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
    //                 ->whereNull('tb_laboratorium_method.deleted_at')
    //                 ->whereNull('ms_method.deleted_at');
    //             })->get();


    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();



    return view('masterweb::module.admin.laboratorium.penanganan-sample.penanganan-edit', compact('penanganan_sample', 'sample', 'laboratoriummethods', 'containers', 'units'));
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
    $customer = Customer::find($id);
    $customer->name_customer = $request->post('name_customer');
    $customer->address_customer = $request->post('address_customer');
    $customer->email_customer = $request->post('email_customer');
    $customer->category_customer = $request->post('category_customer');
    $customer->cp_customer = $request->post('cp_customer');
    $customer->save();

    return redirect()->route('elits-customers.index')->with(['status' => 'Customer succesfully updated']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $customer = Customer::findOrFail($id);
    $customer->delete();
    return redirect()->route('elits-customers.index')->with('status', 'Data berhasil dihapus');
  }
}
