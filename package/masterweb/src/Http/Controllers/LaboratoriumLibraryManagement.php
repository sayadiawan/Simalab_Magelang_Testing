<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Library;

class LaboratoriumLibraryManagement extends Controller
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
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();
        $libraries = Library::all();

        return view('masterweb::module.admin.laboratorium.library.list', compact('user', 'libraries'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user

        $user = Auth()->user();


        return view('masterweb::module.admin.laboratorium.library.add', compact('user'));
        //get all menu public
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // print_r($data);



        $library = new Library;
        //uuid
        $uuid4 = Uuid::uuid4();

        $library->id_library = $uuid4->toString();
        $library->title_library = $request->post('title_library');
        $library->link_library = $request->post('link_library') ?? '';
        $library->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'        => true,
                'pesan'         => 'Acuan baku mutu berhasil disimpan!',
                'id_library'    => $library->id_library,
                'title_library' => $library->title_library,
            ]);
        }

        return redirect()->route('elits-libraries.index')->with(['status' => 'Library succesfully inserted']);

        //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);

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
        $auth = Auth()->user();

        $library = Library::where('id_library', $id)->first();


        return view('masterweb::module.admin.laboratorium.library.edit', compact('auth', 'library', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {


        // print_r($data);



        $data = $request->all();
        $library = Library::find($id);
        $library->title_library = $request->post('title_library');
        $library->link_library = $request->post('link_library') ?? '';
        $library->save();

        return redirect()->route('elits-libraries.index')->with(['status' => 'Library succesfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $library = Library::findOrFail($id);
        $library->delete();
        return redirect()->route('elits-libraries.index')->with('status', 'Data berhasil dihapus');
    }

    public function getLibrary(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $data = Library::orderby('title_library', 'asc')->select('id_library', 'title_library')->limit(10)->get();
        } else {
            $data = Library::orderby('title_library', 'asc')->select('id_library', 'title_library')->where('title_library', 'like', '%' . $search . '%')->limit(10)->get();
        }

        $response = array();
        foreach ($data as $item) {
            $response[] = array(
                "id" => $item->id_library,
                "text" => $item->title_library
            );
        }

        return response()->json($response);
    }
}
