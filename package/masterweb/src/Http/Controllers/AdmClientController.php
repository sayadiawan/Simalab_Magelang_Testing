<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Client;
use SmtHelp;
use App\Http\Middleware\RoleCheck;

class AdmClientController extends Controller
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
        $data = Client::orderBy('urutan', 'asc')->get();
        return view('masterweb::module.admin.client.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.client.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'urutan'      => 'required',
            'file_client' => 'required|mimes:jpeg,bmp,png,jpg',
        ]);
        
        $data = new Client;
        $data->publish = "1";
        $data->urutan  = $request->urutan;
        if($request->file('file_client')){
            $file = $request->file('file_client');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/client/');
            $file->move($destinationPath, $imgName);
            $data->file_client = $imgName;
        }
        $data->save();
        return redirect()->route('adm-client.index')->with('status', 'Data berhasil ditambahkan');
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
        //get auth user
        $client = Client::find($id);
        return view('masterweb::module.admin.client.edit',compact('client'));
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
            'urutan'      => 'required',
            'file_client' => 'mimes:jpeg,bmp,png,jpg',
        ]);
        
        $data = Client::find($id);
        $data->urutan  = $request->post('urutan');
        $data->publish = "1";
        if($request->file('file_client')){
            $file = $request->file('file_client');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/client/');
            $file->move($destinationPath, $imgName);
            $data->file_client = $imgName;
        }
        $data->save();
        return redirect()->route('adm-client.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Client = Client::findOrFail($id);
        $Client->delete();
        return redirect()->route('adm-client.index', [$id])->with('status', 'Data berhasil dihapus');
    }

    public function publish($id)
    {
        $Client = Client::findOrFail($id);
        if($Client->publish == NULL OR $Client->publish == 0)
        {
            $Client->publish = "1";
        }else{
            $Client->publish = "0";
        }
        $Client->save();
        
        return redirect('/adm-client');
    }
}
