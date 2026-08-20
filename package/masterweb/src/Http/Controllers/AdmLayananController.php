<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Layanan;
use \Smt\Masterweb\Models\CategoryLayanan;
use \Smt\Masterweb\Models\Menu;

class AdmLayananController extends Controller
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
        $data = Layanan::all();
        return view('masterweb::module.admin.layanan.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //get all menu public
        $menupublic = Menu::where(['publish'=>1])->Where('type','28')->get();
        $data_cat = CategoryLayanan::all();
        return view('masterweb::module.admin.layanan.add',compact('menupublic','data_cat'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'menu_id' => 'required',
            'title'   => 'required',
            'link_url' => 'required',
            // 'type' => 'required',
        ]);
        
        $data = new Layanan;

        $data->menu_id = $request->post('menu_id');
        // $data->type = $request->post('type');
        $data->title = $request->post('title');
        $data->kategori = $request->post('kategori');
        $fitur = implode($request->fitur, ',');
        $data->fitur = $fitur;
        $data->link_url = $request->post('link_url');
        $data->deskripsi = $request->post('deskripsi');
        $data->publish = "1";
        if($request->file('image')){
            $file = $request->file('image');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/layanan/');
            $file->move($destinationPath, $imgName);

            $data->image = $imgName;
        }
        $data->save();
        return redirect('admlayanan')->with('status', 'Data berhasil ditambahkan');
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
        $layanan = Layanan::find($id);
        $menupublic = Menu::where(['publish'=>1])->Where('type','28')->get();
        $data_cat = CategoryLayanan::all();
        return view('masterweb::module.admin.layanan.edit',compact('menupublic','layanan','data_cat'));
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
            'menu_id' => 'required',
            'title'   => 'required',
            'link_url' => 'required',
            // 'type' => 'required',
        ]);
        
        $data = Layanan::find($id);
        $data->menu_id = $request->post('menu_id');
        // $data->type = $request->post('type');
        $data->title = $request->post('title');
        $data->kategori = $request->post('kategori');
        $fitur = implode($request->post('fitur'), ',');
        $data->fitur = $fitur;
        $data->link_url = $request->post('link_url');
        $data->deskripsi = $request->post('deskripsi');

        if($request->file('image')){
            $file = $request->file('image');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/layanan/');
            $file->move($destinationPath, $imgName);

            $data->image = $imgName;
        }
        $data->save();
        
        return redirect()->route('admlayanan.index')->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subsector = Layanan::findOrFail($id);
        $subsector->delete();
        return redirect()->route('admlayanan.index')->with('status', 'Data berhasil dihapus');
    }
}
