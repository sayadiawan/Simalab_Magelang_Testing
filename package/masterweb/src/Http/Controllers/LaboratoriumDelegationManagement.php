<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Delegation;
use \Smt\Masterweb\Models\SamplesMethod;
use \Smt\Masterweb\Models\Sample;




use Carbon\Carbon;


class LaboratoriumDelegationManagement extends Controller
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
    public function index($id)
    {
        //get auth user
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();
        $delegation = Delegation::where("id_samples",$id)->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_delegation.id_method')->leftJoin('ms_users', 'ms_users.id', '=', 'tb_delegation.id_delegation') ->select('ms_users.id AS id_user','ms_users.name AS user_name','ms_method.id_method AS method_id', 'tb_delegation.*')->get();
       
        $method = SamplesMethod::where("id_samples",$id)->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')->get();
        $samples = Sample::where("id_samples",$id)->leftJoin('ms_customer', 'ms_customer.id_customer', '=', 'tb_samples.customer_samples')->first();
        

        return view('masterweb::module.admin.laboratorium.delegation.list',compact('user','delegation','method','samples'));

                
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request,$id)
    {
        $data =$request->all();

        $methods= SamplesMethod::where("id_samples",$id)->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')->get();
        $delegations=Delegation::where('id_samples',$id)->get();
        

        foreach ($delegations as $delegation) {
            
            $delegationdeleted = Delegation::findOrFail($delegation->id);
            $delegationdeleted->delete();
        }
        
        // print_r($data);
         
      
        $user = Auth()->user();
        foreach ($methods as $method) {

            if(isset($data["office_".$method->id_method])){
             
                $delegation = new Delegation;
                //uuid
                $uuid4 = Uuid::uuid4();
                $delegation->id_samples=$id;
                $delegation->id_method=$method->id_method;
                $delegation->id_delegation=$data["office_".$method->id_method];
                $delegation->save();
            }
            
        }

        return response()->json(
            [
                'success'=>'Delegasi Berhasil Tersimpan',
                'data'=>$data,
            ]);

        // return redirect()->route('elits-deligations',["id"=>$id])->with(['status_delegation'=>'Delegasi Berhasil Tersimpan']);
  
    }

    public function start(Request $request,$id)
    {
        $data =$request->all();

        $methods= SamplesMethod::where("id_samples",$id)->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')->get();
        $delegations=Delegation::where('id_samples',$id)->get();
        

        foreach ($delegations as $delegation) {
            
            $delegationdeleted = Delegation::findOrFail($delegation->id);
            $delegationdeleted->delete();
        }
        
        // print_r($data);
         
      
        $user = Auth()->user();
        foreach ($methods as $method) {

            if(isset($data["office_".$method->id_method])){
             
                $delegation = new Delegation;
                //uuid
                $uuid4 = Uuid::uuid4();
                $delegation->id_samples=$id;
                $delegation->id_method=$method->id_method;
                $delegation->id_delegation=$data["office_".$method->id_method];
                $delegation->save();
            }
            
        }


        $sample = Sample::find($id);
        $sample->status = 2;
        $timeNow = Carbon::now()->toDateTimeString();
        $sample->date_delegation=$timeNow;
        $sample->save();


        return response()->json(
            [
                'success'=>'Sample Berhasil Masuk ke Tahap Analisa',
                'data'=>$data,
            ]);
        // return redirect()->route('elits-samples.index')->with(['status'=>'Sample '.$sample->id_samples.' Masuk Ke Tahap Analisa']);
  

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
