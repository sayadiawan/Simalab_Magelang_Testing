<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Slideshow;
use SmtHelp;
use App\Http\Middleware\RoleCheck;

class AdmSlideshowController extends Controller
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
        $data = Slideshow::all();
        return view('masterweb::module.admin.slideshow.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.slideshow.add');
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
            'images' => 'required|mimes:jpeg,bmp,png,jpg',
        ]);
        
        $data = new Slideshow;

        $data->name = $request->post('name');
        $data->deskripsi = $request->post('deskripsi');
        $data->link = SmtHelp::create_link($request->post('name'));
        $data->url = $request->post('url');
        $data->order = $request->post('order');
        $data->publish = "1";
        if($request->file('images')){
            $file = $request->file('images');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/slideshow/');
            $file->move($destinationPath, $imgName);
            $data->images = $imgName;
        }
        $data->save();
        return redirect()->route('admslideshow.index')->with('status', 'Data berhasil ditambahkan');
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
        $slideshow = Slideshow::find($id);
        return view('masterweb::module.admin.slideshow.edit',compact('slideshow'));
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
            'images' => 'mimes:jpeg,bmp,png,jpg',
        ]);
        
        $data = Slideshow::find($id);
        $data->name = $request->post('name');
        $data->deskripsi = $request->post('deskripsi');
        $data->link = SmtHelp::create_link($request->post('name'));
        $data->url = $request->post('url');
        $data->order = $request->post('order');
        $data->publish = "1";
        if($request->file('images')){
            $file = $request->file('images');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/slideshow/');
            $file->move($destinationPath, $imgName);
            $data->images = $imgName;
        }
        $data->save();
        return redirect()->route('admslideshow.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $slideshow = Slideshow::findOrFail($id);
        $slideshow->delete();
        return redirect()->route('admslideshow.index', [$id])->with('status', 'Data berhasil dihapus');
    }

    public function publish(Request $request,$id)
    {
        $slideshow = Slideshow::findOrFail($id);
        if($slideshow->publish == NULL OR $slideshow->publish == 0)
        {
            $slideshow->publish = "1";
        }else{
            $slideshow->publish = "0";
        }
        $slideshow->save();
        
        return redirect('/admslideshow');
    }
}
