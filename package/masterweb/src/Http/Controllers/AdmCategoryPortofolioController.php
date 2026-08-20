<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\CategoryPortofolio;
use SmtHelp;
use App\Http\Middleware\RoleCheck;

class AdmCategoryPortofolioController extends Controller
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
        $data = CategoryPortofolio::all();
        return view('masterweb::module.admin.portofolio.list_category',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.portofolio.add_category');
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
        
        $data = new CategoryPortofolio;
        $data->name_category_portofolio = $request->post('name_category_portofolio');
        $data->link_category_portofolio = str_replace(' ','-', $request->post('name_category_portofolio'));
        $data->save();
        return redirect()->route('adm-categoryportofolio.index')->with('status', 'Data berhasil ditambahkan');
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
        $categoryportofolio = CategoryPortofolio::find($id);
        return view('masterweb::module.admin.portofolio.edit_category',compact('categoryportofolio'));
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
        
        $data = CategoryPortofolio::find($id);
        $data->name_category_portofolio = $request->post('name_category_portofolio');
         $data->link_category_portofolio = str_replace(' ','-', $request->post('name_category_portofolio'));
        $data->save();
        return redirect()->route('adm-categoryportofolio.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Client = CategoryPortofolio::findOrFail($id);
        $Client->delete();
        return redirect()->route('adm-categoryportofolio.index', [$id])->with('status', 'Data berhasil dihapus');
    }
}
