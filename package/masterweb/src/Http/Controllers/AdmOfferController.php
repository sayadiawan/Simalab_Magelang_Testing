<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Offer;
use \Smt\Masterweb\Models\Menu;

class AdmOfferController extends Controller
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
        $datas = Offer::all();
        return view('masterweb::module.admin.offer.list',compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menupublic = Menu::where(['publish'=>1])->Where('type','28')->get();
        return view('masterweb::module.admin.offer.add', compact('menupublic'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $validasi = $request->validate([
           'menu_id' => 'required',
           'judul'  =>  'required',
           'foto'   =>  'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
       ]);

       $offer = new Offer;

       $offer->menu_id = $request->menu_id;
       $offer->judul = $request->judul;
       $offer->publish = 1;
       if($request->file('foto')){
            $file = $request->file('foto');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/layanan/');
            $file->move($destinationPath, $imgName);

            $offer->foto = $imgName;
        }
       $offer->save();
       return redirect('/admoffer')->with('status', 'Data berhasil ditambahkan');
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
        $data = Offer::findOrFail($id);
        return view('masterweb::module.admin.offer.show',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Offer::findOrFail($id);
        $menupublic = Menu::where(['publish'=>1])->Where('type','28')->get();
        return view('masterweb::module.admin.offer.edit',compact('data', 'menupublic'));
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
        $validasi = $request->validate([
            'menu_id' => 'required',
            'judul'  =>  'required',
            'foto'   =>  'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
 
        $offer = Offer::find($id);
 
        $offer->menu_id = $request->post('menu_id');
        $offer->judul = $request->post('judul');
        if($request->file('foto')){
             $file = $request->file('foto');
             $imgName = $file->getClientOriginalName();
             $destinationPath = public_path('/assets/public/images/layanan/');
             $file->move($destinationPath, $imgName);
 
             $offer->foto = $imgName;
         }
        $offer->save();
        return redirect('/admoffer')->with('status', 'Data berhasil ditambahkan');   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();
        return redirect()->route('admoffer.index')->with('status', 'Data berhasil dihapus');
    }

    public function publish($id)
    {
        $data = Offer::findOrFail($id);
        if($data->publish == NULL OR $data->publish == 0)
        {
            $data->publish = "1";
        }else{
            $data->publish = "0";
        }
        $data->save();
        
        return redirect('/admoffer');
    }
}
