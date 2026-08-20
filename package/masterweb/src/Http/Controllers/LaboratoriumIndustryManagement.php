<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Industry;

class LaboratoriumIndustryManagement extends Controller
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
        $industries = Industry::all();

        return view('masterweb::module.admin.laboratorium.industry.list',compact('user','industries'));

                
    }


    public function device()
    {
        //get auth user
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();

        

        
        //$data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
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
        

        return view('masterweb::module.admin.laboratorium.industry.add',compact('user'));
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
         
      
        
         $industry = new Industry;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $industry->id_industry = $uuid4->toString();
         $industry->name_industry = $request->post('name_industry');
         $industry->save();
 
         return redirect()->route('elits-industries.index')->with(['status'=>'Industry succesfully inserted']);
   
        //  return response()->json(
        //      [
        //          'success'=>'Ajax request submitted successfully',
        //          'data'=>$data,
        //      ]);
  
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

        $industry = Industry::where('id_industry',$id)->first();


        return view('masterweb::module.admin.laboratorium.industry.edit',compact('industry','auth','id'));

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

        $data =$request->all();
        $industry = Industry::find($id);
        $industry->name_industry = $request->post('name_industry');
        $industry->save();
      

        return redirect()->route('elits-industries.index')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $majors = Industry::findOrFail($id);
        $majors->delete();
        return redirect()->route('elits-industries.index')->with('status', 'Data berhasil dihapus');
    }
}
