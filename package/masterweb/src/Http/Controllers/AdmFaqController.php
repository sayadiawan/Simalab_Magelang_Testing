<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Faq;
use SmtHelp;

class AdmFaqController extends Controller
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
        $data = Faq::all();
        return view('masterweb::module.admin.faq.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        return view('masterweb::module.admin.faq.add');
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
            'question' => 'required',
            'answer' => 'required',
        ]);
        
        $data = new Faq;

        $data->question = $request->post('question');
        $data->answer = $request->post('answer');
        $data->ordered = $request->post('ordered');
        $data->publish = "1";
        $data->save();
        return redirect()->route('adm-faq.index')->with('status', 'Data berhasil ditambahkan');
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
        $data = Faq::find($id);
        return view('masterweb::module.admin.faq.edit',compact('data'));
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
            'question' => 'required',
            'answer' => 'required',
        ]);
        
        $data = Faq::find($id);
        $data->question = $request->post('question');
        $data->answer = $request->post('answer');
        $data->ordered = $request->post('ordered');
        $data->save();
        return redirect()->route('adm-faq.index', [$id])->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $slideshow = Faq::findOrFail($id);
        $slideshow->delete();
        return redirect()->route('adm-faq.index', [$id])->with('status', 'Data berhasil dihapus');
    }

    public function publish(Request $request,$id)
    {
        $slideshow = Faq::findOrFail($id);
        if($slideshow->publish == NULL OR $slideshow->publish == 0)
        {
            $slideshow->publish = "1";
        }else{
            $slideshow->publish = "0";
        }
        $slideshow->save();
        
        return redirect('/adm-faq');
    }
}
