<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SamplesMethod;



use \Smt\Masterweb\Models\Module\FormA;


use \Smt\Masterweb\Models\Result\FormAResult;

use \Smt\Masterweb\Imports\FormImport;
use Excel;

use Response;


use Carbon\Carbon;

class LaboratoriumExcelManagement extends Controller
{

    protected $success= [],$failure= [];

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
        $methods = Method::all();

        return view('masterweb::module.admin.laboratorium.excel.add',compact('user','methods'));

                
    }


    public function formImports(Request $request){

        $auth = Auth()->user();

        $id_method = $request->method;

        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        // menangkap file excel
        $file = $request->file('file');

        // membuat nama file unik
        $nama_file = rand().$file->getClientOriginalName();

        // upload ke folder file_siswa di dalam folder public
        $file->store('excelForm/', 'public');

        // import data
        // Excel::load(storage_path('app/public/excelFormA/'.$file->hashName()), function($reader) {

        //     // Getting all results
        //     $results = $reader->get();

        //     // ->all() is a wrapper for ->get() and will work the same
        //     $results = $reader->all();


        // });
        $data =Excel::toArray(new FormImport, storage_path('app/public/excelForm/'.$file->hashName()));


        unlink(storage_path('app/public/excelForm/'.$file->hashName()));

        $this->success = [];
        $this->failure = [];



        //Cek success
        
        $this->cekSuccess(1,$data,$id_method);
        
        $this->inputForm($data,$id_method);











        
       


        return redirect()->route('elits-analys.index')->with(['status'=>'Import Excel succesfully inserted']);



    }

    public function cekSuccess($index,$data, $id_method){
        

        $auth = Auth()->user();

        if($index==1){

            for($i=1;$i<count($data[0]);$i++){
           
                if($i%2==1){
                    if($auth->level=="3382abf2-8518-42f9-91e1-096f25da8ae8"){

                        $method = SamplesMethod::
                        join('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
                        ->join('tb_delegation', function($join)
                        {
                            $join->on('tb_delegation.id_method', '=', 'ms_samples_method.id_method');
                            $join->on('tb_delegation.id_samples', '=', 'ms_samples_method.id_samples');
                        })
                        ->join('tb_samples', 'tb_samples.id_samples', '=', 'ms_samples_method.id_samples')
                        ->where('tb_samples.deleted_at', '=', null)
                        ->where('tb_delegation.id_delegation', '=', $auth->id)
                        ->where('ms_method.deleted_at', '=', null)
                        ->where('codesample_samples', '=', $data[0][$i][0])
                        ->where('ms_method.id_method', '=', $id_method)
                        ->where('tb_delegation.deleted_at', '=', null)
                        ->select('ms_method.params_method','ms_method.id_method','tb_samples.codesample_samples','tb_samples.id_samples')
                        ->distinct('codesample_samples')
                        ->first();
        
        
                       
        
                    }else{
                        $method = SamplesMethod::where("id_samples",$id)
                        ->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
                        ->where('ms_method.deleted_at', '=', null)
                        ->where('codesample_samples', '=', $data[0][$i][0])
                        ->where('ms_method.id_method', '=', $id_method)
                        ->join('tb_samples', 'tb_samples.id_samples', '=', 'ms_samples_method.id_samples')
                        ->where('tb_samples.deleted_at', '=', null)
                        ->select('ms_method.params_method','ms_method.id_method','tb_samples.codesample_samples')
                        ->distinct('codesample_samples')
                        ->first();
                    }
                    if($method!=null){
                        $data[0][$i][]=$method->id_samples;
                        $this->success[]= $data[0][$i];
                        $this->success[]= $data[0][$i+1];
                    }else{
                        if( $data[0][$i][0]!=null){
                            $this->failure[]=  $data[0][$i];
                        }
                       
                    }
                }
              
            }
    

        }
        

    }

    public function inputForm($data, $id_method){

        $method = Method::findOrFail($id_method);

        if($method->model_method == "FormA"){

            $success=$this->success;

            for($i=0;$i<count($success);$i++){
              
                if($i%2==0){
                    $formA= FormA::where('id_samples', $success[$i][8])->where('id_method', $id_method)->first();

                
                    if($formA==null){
                        $formA = new FormA;
                        $uuid4 = Uuid::uuid4();
                        
                        $formA->id=$uuid4;
                        $formA->id_samples = $success[$i][8];
                        $formA->id_method=$id_method;
                    
                        $formA->rpd = $success[$i][7];
                        $formA->hasil = $success[$i][6];
                        $formA->save();
                        

                    }else{
                        $formA->id_samples = $success[$i][8];
                        $formA->id_method=$id_method;
                        $formA->rpd = $success[$i][7];
                        $formA->hasil = $success[$i][6];
                        $formA->save();

                        $formAResult = FormAResult::where('id_FormA', $formA->id)->delete();
                    }


                    for($j=$i;$j<$i+2;$j++){
                    
                        if($j==$i){
                            $order=0;
                        }else{
                            $order=1;
                        }
                        $formAResult =new FormAResult;
                        $uuid4 = Uuid::uuid4();
                        $formAResult->id=$uuid4;
                        $formAResult->id_FormA=$formA->id;
                        $formAResult->date_test =  Carbon::createFromFormat('d-m-Y', $success[$j][2])->format('Y-m-d H:i:s');
                        $formAResult->crm_iot = $success[$i][1];
                        $formAResult->konsentrasi_1 = $success[$j][3];
                        $formAResult->konsentrasi_2 = $success[$j][4];
                        $formAResult->order = $order;
                        $formAResult->save();
                    }

                   
                }
            }

                    
           
                
              
          

        }else if($method->model_method == "FormB"){

        }

        //....





    }



    public function downloadFormImports($id_method){
        // validasi
        // $this->validate($request, [
        //     'file' => 'required|mimes:csv,xls,xlsx'
        // ]);

        // // menangkap file excel
        // $file = $request->file('file');

        // // membuat nama file unik
        // $nama_file = rand().$file->getClientOriginalName();

        // // upload ke folder file_siswa di dalam folder public
        // $file->store('excelForm/', 'public');

        // import data
        // Excel::load(storage_path('app/public/excelFormA/'.$file->hashName()), function($reader) {

        //     // Getting all results
        //     $results = $reader->get();

        //     // ->all() is a wrapper for ->get() and will work the same
        //     $results = $reader->all();


        // });

        $method = Method::where('id_method',$id_method)->first();

        $path = storage_path('app/public/excelFormExample/'.$method->model_method.'.xlsx');
        if (file_exists($path)) {
            return response()->download($path);
        }

       


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
        $categories= Industry::all();
        

        return view('masterweb::module.admin.laboratorium.customer.add',compact('user','categories'));
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
        $data =$request->all();
        
        // print_r($data);
         
      
        
         $customer = new Customer;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $customer->id_customer = $uuid4->toString();
         $customer->name_customer = $request->post('name_customer');
         $customer->address_customer = $request->post('address_customer');
         $customer->email_customer = $request->post('email_customer');
         $customer->category_customer =$request->post('category_customer');
         $customer->cp_customer =$request->post('cp_customer');
         $customer->save();
 
         return redirect()->route('elits-customers.index')->with(['status'=>'Customer succesfully inserted']);
   
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
