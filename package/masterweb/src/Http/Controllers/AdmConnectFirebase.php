<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Contact;
use Smt\Masterweb\Models\User;

class AdmConnectFirebase extends Controller
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

        $user = Auth()->user();

       $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();

        // $users = User::paginate(10);


       
       
       
        
        return view('masterweb::module.admin.firebase.dashboard',compact('user','users'));
        
    }

    public function device()
    {
        //get auth user
       // $data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
       $user = Auth()->user();
       $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();

        return view('masterweb::module.admin.firebase.device',compact('users','users'));
        
    }


    public function client()
    {
        //get auth user
        $user = Auth()->user();
       // $data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
       if(($user->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||($user->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||($user->level=='625b6020-1087-4898-b2ea-b598d90b6179')){
            $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get(); 
            return view('masterweb::module.admin.firebase.client',compact('users','users'));
       }else{

            $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();
            $is_user_client=true; 
            return view('masterweb::module.admin.firebase.admin-client.client',compact('users','users'));
        

       }
        
    }


    public function report()
    {
        //get auth user
       // $data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
       $user = Auth()->user();
       $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();

       return view('masterweb::module.admin.firebase.device',compact('users','users'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth()->user();
        
        return view('masterweb::module.admin.users.add',compact('user','privileges'));

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

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'email' => 'required',
            'phone' => 'required'
        ]);
        
        $data = Contact::find($id);
        $data->nama = $request->post('nama');
        $data->alamat = $request->post('alamat');
        $data->email = $request->post('email');
        $data->phone = $request->post('phone');
        $data->save();
        return redirect()->route('admcontact.index')->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subsector = Content::findOrFail($id);
        $subsector->delete();
        return redirect()->route('admcontent.index')->with('status', 'Data berhasil dihapus');
    }
}
