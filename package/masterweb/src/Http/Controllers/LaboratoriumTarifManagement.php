<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Rates;
use \Smt\Masterweb\Models\Majors;

class LaboratoriumTarifManagement extends Controller
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
        $rates = Rates::all();

        return view('masterweb::module.admin.laboratorium.rates.list',compact('user','rates'));

                
    }


    public function device()
    {
        //get auth user
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();

        

        
        // // $data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
        // if(($user->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||
        //($user->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||
        //($user->level=='e6684739-b90b-4149-a093-9457474a277b')||
        // ($user->level=='625b6020-1087-4898-b2ea-b598d90b6179')){
        //     $users = User::where('level','7d6bc1b7-5115-4724-820d-f04744f61828')->where('client_id', $id) ->get(); 
        //     return view('masterweb::module.admin.firebase.user-client.list',compact('user','users','id'));
        // }else{

        //     $id=$user->id;
        //     $users = User::where('level','7d6bc1b7-5115-4724-820d-f04744f61828')->where('client_id', $user->id) ->get(); 
        //     return view('masterweb::module.admin.firebase.user-client.list',compact('user','users','id'));
        
        // }        
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
        $majors= Majors::all();
        

        return view('masterweb::module.admin.laboratorium.rates.add',compact('user','majors'));
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
         
      
        
         $rate = new Rates;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $rate->id_rate = $uuid4->toString();
         $rate->params_rate = $request->post('params_rate');
         $rate->major_rate = $request->post('major_rate');
         $rate->rate_rate = $request->post('rate_rate');
         $rate->purpose_rate =$request->post('purpose_rate');
         $rate->save();
 
         return redirect()->route('elits-rates.index')->with(['status'=>'Rate succesfully inserted']);
   
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

        $rate = Rates::where('id_rate',$id)->first();

        $majors= Majors::all();
       

        return view('masterweb::module.admin.laboratorium.rates.edit',compact('rate','auth','majors','id'));

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
         $rate = Rates::find($id);
         $rate->params_rate = $request->post('params_rate');
         $rate->major_rate = $request->post('major_rate');
         $rate->rate_rate = $request->post('rate_rate');
         $rate->purpose_rate =$request->post('purpose_rate');
         $rate->save();
 
         return redirect()->route('elits-rates.index')->with(['status'=>'Rate succesfully updated']);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $rates = Rates::findOrFail($id);
        $rates->delete();
        return redirect()->route('elits-rates.index')->with('status', 'Data berhasil dihapus');
    }
}
