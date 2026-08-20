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
use \Smt\Masterweb\Models\PenerimaanSample;
use \Smt\Masterweb\Models\PengetikanHasil;
use \Smt\Masterweb\Models\Unit;



use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;
use DB;

use Mapper;


use Carbon\Carbon;


class LaboratoriumPengetikanHasilManagement extends Controller
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
    public function index($id,$idlab)
    {

        Carbon::setLocale('id');
        $sample=Sample::where('tb_samples.id_samples','=',$id)
        ->where('ms_laboratorium.id_laboratorium','=',$idlab)
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
        $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id','=',$idlab)
            ->orderBy('ms_method.created_at')
            ->join('ms_method', function ($join) {
                $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                ->whereNull('tb_laboratorium_method.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })->get();
        $units = Unit::all();

        $containers= Container::where('id_container','!=','0')->get();


        return view('masterweb::module.admin.laboratorium.penerimaan-sample.penerimaan',compact('sample','laboratoriummethods','containers','units'));

                
    }


 

    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id,$idlab)
    {
        //get auth user




        $user = Auth()->user();
        $sample=Sample::where('tb_samples.id_samples','=',$id)
        ->where('ms_laboratorium.id_laboratorium','=',$idlab)
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

        $laboratoriummethods = SampleMethod::where('laboratorium_id','=',$idlab)
                                    ->where('sample_id','=',$id)
                                ->orderBy('ms_method.created_at')
                                ->join('ms_method', function ($join) {
                                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                                    ->whereNull('tb_sample_method.deleted_at')
                                    ->whereNull('ms_method.deleted_at');
                                })->get();
       

        

        return view('masterweb::module.admin.laboratorium.pengetikan-hasil.pengetikan_hasil',compact('user','sample','laboratoriummethods'));
        //get all menu public
    }


    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id,$idlabs)
    {
        $data =$request->all();

        
        // print_r($data);
     
            $user = Auth()->user();
            $pengetikan_hasil = new PengetikanHasil;
            //uuid
            $uuid4 = Uuid::uuid4();
    
            $pengetikan_hasil->id_pengetikan_hasil = $uuid4->toString();
            $pengetikan_hasil->sample_id = $id;
            $pengetikan_hasil->laboratorium_id = $idlabs;
            $pengetikan_hasil->pengetikan_hasil_date =Carbon::createFromFormat('d/m/Y', $data["pengetikan_hasil_date"])->format('Y-m-d H:i:s');
        
            $pengetikan_hasil->save();
        

        // $sample = Sample::where('id_samples',$id)->first();


       
        return redirect()->route('elits-samples.verification',[$id,$idlabs])->with(['status'=>'Pelaporan Hasil berhasil di input']);
  
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

        $customer = Customer::where('id_customer',$id)->first();

        $categories= Industry::all();
       

        return view('masterweb::module.admin.laboratorium.customer.edit',compact('customer','auth','categories','id'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {

        
        // print_r($data);
         
      
        
         $data =$request->all();
         $customer = Customer::find($id);
         $customer->name_customer = $request->post('name_customer');
         $customer->address_customer = $request->post('address_customer');
         $customer->email_customer = $request->post('email_customer');
         $customer->category_customer =$request->post('category_customer');
         $customer->cp_customer =$request->post('cp_customer');
         $customer->save();
 
         return redirect()->route('elits-customers.index')->with(['status'=>'Customer succesfully updated']);


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
