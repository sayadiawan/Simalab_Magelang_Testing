<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Socmed;
use SmtHelp;

class AdmSosmedController extends Controller
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
        $datas = Socmed::all();
        return view('masterweb::module.admin.sosmed.list',compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.sosmed.add');
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
            'name' => 'required',
            'icon' => 'required',
            'link' => 'required',
            'url' => 'required',
        ]);
        
        $data = new Socmed;

        $data->name = $request->post('name');
        $data->link = $request->post('name');
        $data->url = $request->post('url');
        $data->icon = $request->post('icon');
        $data->publish = "1";
        $data->save();
        return redirect()->route('admsosmed.index')->with('status', 'Data berhasil ditambahkan');
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
        $data = Socmed::findOrFail($id);
        return view('masterweb::module.admin.sosmed.detail', compact('data'));
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
        $data = Socmed::find($id);
        return view('masterweb::module.admin.sosmed.edit',compact('data'));
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
            'name' => 'required',
            'icon' => 'required',
            'link' => 'required',
            'url' => 'required',
        ]);
        
        $data = Socmed::find($id);
        $data->name = $request->post('name');
        $data->link = $request->post('link');
        $data->url = $request->post('url');
        $data->icon = $request->post('icon');
        $data->save();
        return redirect()->route('admsosmed.index')->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Socmed::findOrFail($id);
        $data->delete();
        return redirect()->route('admsosmed.index')->with('status', 'Data berhasil dihapus');
    }

    public function publish(Request $request,$id)
    {
        $sosmed = Socmed::findOrFail($id);
        if($sosmed->publish == NULL OR $sosmed->publish == 0)
        {
            $sosmed->publish = "1";
        }else{
            $sosmed->publish = "0";
        }
        $sosmed->save();
        
        return redirect('/admsosmed');
    }
}
