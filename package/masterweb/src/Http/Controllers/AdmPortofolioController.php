<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\CategoryPortofolio;
use \Smt\Masterweb\Models\Portofolio;
use SmtHelp;
use App\Http\Middleware\RoleCheck;

class AdmPortofolioController extends Controller
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
        $data = Portofolio::all();
        return view('masterweb::module.admin.portofolio.list_portofolio',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        $data_cat = CategoryPortofolio::all();
        return view('masterweb::module.admin.portofolio.add_portofolio',compact('data_cat'));
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
            // 'file_client' => 'required|mimes:jpeg,bmp,png,jpg',
        ]);
        $data = new Portofolio;
    
        $data->catport_portofolio = $request->post('catport_portofolio');
        $data->name_portofolio = $request->post('name_portofolio');
        $data->client_portofolio = $request->post('client_portofolio');
        $data->tech_portofolio = $request->post('tech_portofolio');
        $data->link_portofolio = $request->post('link_portofolio');
        $data->desc_portofolio = $request->post('desc_portofolio');
        $data->publish = "1";
        if($request->file('file_portofolio')){
            $file = $request->file('file_portofolio');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/portofolio/');
            $file->move($destinationPath, $imgName);
            $data->file_portofolio = $imgName;
        }
        $data->save();
        return redirect()->route('adm-portofolio.index')->with('status', 'Data berhasil ditambahkan');
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
        $get_data = Portofolio::find($id);
        $data_cat = CategoryPortofolio::all();
        return view('masterweb::module.admin.portofolio.edit_portofolio',compact('get_data','data_cat'));
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
            // 'file_client' => 'mimes:jpeg,bmp,png,jpg',
        ]);
        
        $data = Portofolio::find($id);
        $data->catport_portofolio = $request->post('catport_portofolio');
        $data->name_portofolio = $request->post('name_portofolio');
        $data->client_portofolio = $request->post('client_portofolio');
        $data->tech_portofolio = $request->post('tech_portofolio');
        $data->link_portofolio = $request->post('link_portofolio');
        $data->desc_portofolio = $request->post('desc_portofolio');
        $data->publish = "1";
        if($request->file('file_portofolio')){
            $file = $request->file('file_portofolio');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/portofolio/');
            $file->move($destinationPath, $imgName);
            $data->file_portofolio = $imgName;
        }
        $data->save();
        return redirect()->route('adm-portofolio.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Client = Portofolio::findOrFail($id);
        $Client->delete();
        return redirect()->route('adm-portofolio.index', [$id])->with('status', 'Data berhasil dihapus');
    }

    public function publish($id)
    {
        $Client = Portofolio::findOrFail($id);
        if($Client->publish == NULL OR $Client->publish == 0)
        {
            $Client->publish = "1";
        }else{
            $Client->publish = "0";
        }
        $Client->save();
        
        return redirect('/adm-portofolio');
    }
}
