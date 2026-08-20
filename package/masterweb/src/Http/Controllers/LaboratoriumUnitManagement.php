<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Unit;

class LaboratoriumUnitManagement extends Controller
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
        $units = Unit::all();

        return view('masterweb::module.admin.laboratorium.unit.list', compact('user', 'units'));
    }


    public function device()
    {
        //get auth user
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();




        //$data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
        // if(($user->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||
        //($user->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||
        //($user->level=='e6684739-b90b-4149-a093-9457474a277b')||
        // ($user->level=='625b6020-1087-4898-b2ea-b598d90b6179')){
        //     $users = User::where('level','7d6bc1b7-5115-4724-820d-f04744f61828')->where('client_id', $id) ->get();
        //     return view('masterweb::module.admin.firebase.user-client.list',compact('user','users','id'));
        // }else{

        //     $id=$user->id;
        //     $users = User::where('level','7d6bc1b7-5115-4724-820d-f04744f61828')->where('client_id', $user->id) ->get();
        //     return view('masterweb::module.admin.firebase.user-client.list',compact('user','users','id'));

        // }
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


        return view('masterweb::module.admin.laboratorium.unit.add', compact('user'));
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



        $unit = new Unit;
        //uuid
        $uuid4 = Uuid::uuid4();

        $unit->id_unit = $uuid4->toString();
        $unit->name_unit = $request->post('name_unit');
        $unit->shortname_unit = $request->post('shortname_unit');
        $unit->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'         => true,
                'pesan'          => 'Satuan berhasil disimpan!',
                'id_unit'        => $unit->id_unit,
                'shortname_unit' => $unit->shortname_unit,
            ]);
        }

        return redirect()->route('elits-units.index')->with(['status' => 'Unit succesfully inserted']);

        //  return response()->json(
        //      [
        //          'success'=>'Ajax request submitted successfully',
        //          'data'=>$data,
        //      ]);

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

        $unit = Unit::where('id_unit', $id)->first();



        return view('masterweb::module.admin.laboratorium.unit.edit', compact('unit', 'auth', 'id'));
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

        $data = $request->all();
        $unit = Unit::find($id);
        $unit->name_unit = $request->post('name_unit');
        $unit->shortname_unit = $request->post('shortname_unit');
        $unit->save();


        return redirect()->route('elits-units.index')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $units = Unit::findOrFail($id);
        $units->delete();
        return redirect()->route('elits-units.index')->with('status', 'Data berhasil dihapus');
    }

    public function getDataUnitBySelect(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $data = Unit::orderby('name_unit', 'asc')->select('id_unit', 'name_unit')->limit(10)->get();
        } else {
            $data = Unit::orderby('name_unit', 'asc')->select('id_unit', 'name_unit')->where('name_unit', 'like', '%' . $search . '%')->limit(10)->get();
        }

        $response = array();
        foreach ($data as $item) {
            $response[] = array(
                "id" => $item->id_unit,
                "text" => $item->name_unit
            );
        }

        return response()->json($response);
    }
}
