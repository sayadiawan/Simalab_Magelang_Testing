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
use \Smt\Masterweb\Models\SamplesMethod;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;



use Carbon\Carbon;


class LaboratoriumReleaseManagement extends Controller
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
        // $user = Auth()->user();
        // $samples = Sample::orderBy('created_at')->get();

        

        // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));

        return view('masterweb::module.admin.laboratorium.sample.pagination-release'); 

                
    }


    public function printLHU($id){
       // dd("ygygyu");

       $sample = Sample::findOrFail($id);

       $customer = Customer::findOrFail($sample->customer_samples);
       $category = Industry::findOrFail($customer->category_customer);
       return view('masterweb::module.admin.laboratorium.sample.printLHU',compact('sample','customer','category')); 

    
    }

    public function getSamplePagination(Request $request){


        $auth = Auth()->user();


        $draw = $request->get('draw');
        $start = $request->get("start");
        
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index

        if($columnIndex!=0){
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        }else{
            $columnName = 'created_at';
            $columnSortOrder ='desc';
        }
        
        $searchValue = $search_arr['value']; // Search value

        // Total records


        if($auth->level=="0e6da765-0f3a-4471-9e1d-6af257e60a70"){
            $totalRecords = Sample::select('count(*) as allcount')
            ->where('tb_samples.status', '=','0' )
            ->count();

            $totalRecordswithFilter = Sample::select('count(*) as allcount')
            ->where('tb_samples.status', '=','0' )
            ->where('codesample_samples', 'like', '%' .$searchValue . '%')
            ->count();

            // Fetch records
            $samples = Sample::orderBy($columnName,$columnSortOrder)
            ->where('tb_samples.codesample_samples', 'like', '%' .$searchValue . '%')
            ->where('tb_samples.status', '=','0' )
            ->select('tb_samples.*')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        }else{
            $totalRecords = Sample::select('count(*) as allcount')->count();
            $totalRecordswithFilter = Sample::select('count(*) as allcount')->where('codesample_samples', 'like', '%' .$searchValue . '%')->count();
    
            // Fetch records
            $samples = Sample::orderBy($columnName,$columnSortOrder)
            ->where('tb_samples.codesample_samples', 'like', '%' .$searchValue . '%')
            ->select('tb_samples.*')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        }
        

        $data_arr = array();

        $no=$start+1;
       
        foreach($samples as $samples){
            $id_samples = $samples->id_samples;
            $user_samples = $samples->user_samples;
            $customer_samples = $samples->customer_samples;
            $customer_samples =Customer::where("id_customer",$customer_samples)->first();
            $qr=QrCode::size(100)->generate($samples->codesample_samples);
            $codesample_samples = $samples->codesample_samples.'<br><br>'.$qr;
           
            $datelab_samples = $samples->datelab_samples;
            $datelab_samples=Carbon::createFromFormat('Y-m-d H:i:s', $datelab_samples)->format('d/m/Y');
            
            


            $status=
            '<a href="/elits-release/printLHU/'.$samples->id_samples.'">
                <button type="button" class="btn btn-outline-success">  
                        <i class="fas fa-file-alt"></i>   
                    Rilis Sample                                        
                </button> 
            </a>';
            $data_arr[] = array(
                "number"=>$no,
                "codesample_samples" => $codesample_samples,
                "id_samples" => $id_samples,
                "user_samples" => $user_samples,
                "customer_samples" => $customer_samples->name_customer,
                "status" => $status,
                "datelab_samples" => $datelab_samples,
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


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user




        $user = Auth()->user();
        $count = Sample::count();
        $users = User::all();

        $code ='KS.CLCP-'.date("Ymd", time()).'-0'.($count+1);
        

        return view('masterweb::module.admin.laboratorium.sample.add',compact('user','code','users'));
        //get all menu public
    }


    public function analys($id, $id_method = null){
        

        $auth = Auth()->user();
        
        $samples= Sample::where("id_samples",$id)->first();
        $customer = Customer::where("id_customer",$samples->customer_samples)->first();
  

        if($auth->level=="3382abf2-8518-42f9-91e1-096f25da8ae8"){
            $method = SamplesMethod::
            join('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
            ->join('tb_delegation', function($join)
            {
              $join->on('tb_delegation.id_method', '=', 'ms_samples_method.id_method');
              $join->on('tb_delegation.id_samples', '=', 'ms_samples_method.id_samples');
           
            } )
            ->where("ms_samples_method.id_samples",$id)
            ->where('tb_delegation.id_delegation', '=', $auth->id)
            ->where('ms_method.deleted_at', '=', null)
            ->where('tb_delegation.deleted_at', '=', null)
            ->select('ms_samples_method.*','ms_method.*','tb_delegation.*')
            ->get();

        }else{
            $method = SamplesMethod::where("id_samples",$id)->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')->get();

        }

    

        return view('masterweb::module.admin.laboratorium.sample.analys',compact('samples','method','customer','id_method'));
        
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
         
      
        $user = Auth()->user();
         $sample = new Sample;
         //uuid
         $uuid4 = Uuid::uuid4();

         $datesampling_samples = Carbon::createFromFormat('d/m/Y', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
         $datelab_samples = Carbon::createFromFormat('d/m/Y', $request->post('datelab_samples'))->format('Y-m-d H:i:s');

         $sample->id_samples = $uuid4->toString();
         $sample->codesample_samples = $request->post('code_samples');
         $sample->customer_samples = $request->post('customerAttributes');
        
         if(!$request->post('issend_samples')=="on"){
            $sample->sender_samples = $request->post('sender_samples');
            $sample->issend_samples = 0;
        }else{
            $sample->issend_samples = 1;
        }
        $sample->cost_samples = $request->post('cost_samples');
        $sample->typesample_samples = $request->post('typesample_samples');
        $sample->user_input_samples = $user->id;
        $sample->wadah_samples = $request->post('wadah_samples');
        $sample->amount_samples = $request->post('amount_samples');
        $sample->unit_samples = $request->post('unitAttributes');
        
        
        $sample->poinsample_samples = $request->post('poinsample_samples');
        $sample->datesampling_samples = $datesampling_samples;
        $sample->datelab_samples = $datelab_samples;

         $sample->save();

         
         if(isset ($data["methodAttributes"])){
            for($i = 0; $i < count($data["methodAttributes"]);$i++ ) {
                $method = new SamplesMethod;
                $method->id= Uuid::uuid4();
                $method->id_samples=$sample->id_samples;
                $method->id_method=$data["methodAttributes"][$i];
                $method->save();
            }
        }

 
        return response()->json(
            [
                'success'=>'Ajax request submitted successfully',
                'data'=>$data,
            ]);
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
