<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Testimoni;
use SmtHelp;
use App\Http\Middleware\RoleCheck;

class AdmTestimoniController extends Controller
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
        $data = Testimoni::all();
        return view('masterweb::module.admin.testimoni.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.testimoni.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     // 'file' => 'required|mimes:jpeg,bmp,png,jpg',
        // ]);
        
        $data = new Testimoni;
        $data->name_testimoni = $request->post('name_testimoni');
        $data->description_testimoni = $request->post('description_testimoni');
        $data->publish = "1";
        if($request->file('file_testimoni')){
            $file = $request->file('file_testimoni');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/testimoni/');
            $file->move($destinationPath, $imgName);
            $data->file_testimoni = $imgName;
        }
        $data->save();
        return redirect()->route('adm-testimoni.index')->with('status', 'Data berhasil ditambahkan');
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
        $testimoni = Testimoni::find($id);
        return view('masterweb::module.admin.testimoni.edit',compact('testimoni'));
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
        // $validatedData = $request->validate([
        //     // 'file_testimoni' => 'mimes:jpeg,bmp,png,jpg',
        // ]);
        $data = Testimoni::find($id);
        $data->name_testimoni = $request->post('name_testimoni');
        $data->description_testimoni = $request->post('description_testimoni');
        
        $data->publish = "1";
        if($request->file('file_testimoni')){
            $file = $request->file('file_testimoni');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/testimoni/');
            $file->move($destinationPath, $imgName);
            $data->file_testimoni = $imgName;
        }
        $data->save();
        return redirect()->route('adm-testimoni.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $testimoni = Testimoni::findOrFail($id);
        $testimoni->delete();
        return redirect()->route('adm-testimoni.index', [$id])->with('status', 'Data berhasil dihapus');
    }

    public function publish($id)
    {
        $testimoni = Testimoni::findOrFail($id);
        if($testimoni->publish == NULL OR $testimoni->publish == 0)
        {
            $testimoni->publish = "1";
        }else{
            $testimoni->publish = "0";
        }
        $testimoni->save();
        
        return redirect('/adm-testimoni');
    }
}
