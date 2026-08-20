<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Content;
use \Smt\Masterweb\Models\Menu;

class AdmContentController extends Controller
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
        $data = Content::all();
        return view('masterweb::module.admin.content.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //get all menu public
        $menupublic = Menu::where(['publish'=>1])->Where('type','15')->orWhere('type','18')->orWhere('type','28')->get();
        
        return view('masterweb::module.admin.content.add',compact('menupublic'));
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
            'menu_id' => 'required',
            'type' => 'required',
            'title' => 'required',
            'link_url' => 'required',
            'content' => 'required',
            'author' => 'required'
        ]);
        
        $data = new Content;

        $data->menu_id = $request->post('menu_id');
        $data->type = $request->post('type');
        $data->title = $request->post('title');
        $data->link_url = $request->post('link_url');
        $data->content = $request->post('content');
        $data->views = "0";
        $data->author = $request->post('author');
        $data->keyword = $request->post('keyword');
        $data->deskripsi = $request->post('deskripsi');
        $data->publish = "1";
        if($request->file('img_thumbnail')){
            $file = $request->file('img_thumbnail');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/');
            $file->move($destinationPath, $imgName);

            $data->img_thumbnail = $imgName;
        }
        $data->save();
        return redirect()->route('admcontent.index')->with('status', 'Data berhasil ditambahkan');
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
        $content = Content::find($id);
        $menupublic = Menu::all()->where('publish',1);
        return view('masterweb::module.admin.content.edit',compact('menupublic','content'));
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
            'type' => 'required',
            'title' => 'required',
            'link_url' => 'required',
            'content' => 'required',
            'author' => 'required'
        ]);
        
        $data = Content::find($id);
        $data->menu_id = $request->post('menu_id');
        $data->type = $request->post('type');
        $data->title = $request->post('title');
        $data->link_url = $request->post('link_url');
        $data->content = $request->post('content');
        $data->author = $request->post('author');
        $data->keyword = $request->post('keyword');
        $data->deskripsi = $request->post('deskripsi');

        if($request->file('img_thumbnail')){
            $file = $request->file('img_thumbnail');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/');
            $file->move($destinationPath, $imgName);

            $data->img_thumbnail = $imgName;
        }
        $data->save();
        
        return redirect()->route('admcontent.index')->with('status', 'Data berhasil diperbarui');
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
