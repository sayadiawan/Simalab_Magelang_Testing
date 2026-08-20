<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Majors;

class LaboratoriumMajorManagement extends Controller
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
        $majors = Majors::all();

        return view('masterweb::module.admin.laboratorium.major.list',compact('user','majors'));

                
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
        

        return view('masterweb::module.admin.laboratorium.major.add',compact('user'));
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
         
      
        
         $major = new Majors;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $major->id_major = $uuid4->toString();
         $major->name_major = $request->post('name_major');
          $major->save();
 
         return redirect()->route('elits-majors.index')->with(['status'=>'Major succesfully inserted']);
   
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

        $major = Majors::where('id_major',$id)->first();



        return view('masterweb::module.admin.laboratorium.major.edit',compact('major','auth','id'));

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
        $major = Majors::find($id);
        $major->name_major = $request->post('name_major');
        $major->save();
      

        return redirect()->route('elits-majors.index')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $majors = Majors::findOrFail($id);
        $majors->delete();
        return redirect()->route('elits-majors.index')->with('status', 'Data berhasil dihapus');
    }
}
