<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use Illuminate\Validation\Rule;

use \Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\Program;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class LaboratoriumProgramManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function rules($request)
  {
    $rule = [
      'name_program' => 'required',
    ];

    $pesan = [
      'name_program.required' => 'Nama program tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $user = Auth()->user();
    $level = $user->getlevel->level;

    /* if ($level == "elits-dev" || $level == "admin") {
            return view('masterweb::module.admin.laboratorium.program.list');
        } else {
            return abort(404);
        } */

    return view('masterweb::module.admin.laboratorium.program.list');
  }

  public function data_program()
  {
    $datas = Program::orderBy('created_at')->get();

    return Datatables::of($datas)
      ->addColumn('action', function ($data) {
        $buttonEdit = '<a href="' . route('elits-program.edit', [$data->id_program]) . '" class="btn btn-outline-success btn-rounded btn-icon"><i class="fas fa-pencil-alt"></i></a> ';

        $buttonDelete = '<a href="#hapus" class="btn btn-outline-danger btn-rounded btn-icon btn-hapus"
                data-id="' . $data->id_program . '"
                data-name="' . $data->name_program . '"><i class="fas fa-trash"></i></a> ';

        return $buttonEdit . $buttonDelete;
      })
      ->rawColumns(['action'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $user = Auth()->user();
    $level = $user->getlevel->level;

    if ($level == "elits-dev" || $level == "admin") {
      return view('masterweb::module.admin.laboratorium.program.add');
    } else {
      return abort(404);
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = new Program();
        $post->name_program = $request->post('name_program');

        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data program berhasil disimpan!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data program tidak berhasil disimpan!"], 400);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e], 400);
      }
    }
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
    $user = Auth()->user();
    $level = $user->getlevel->level;
    $item = Program::find($id);

    return view('masterweb::module.admin.laboratorium.program.edit', compact('item'));
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
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = Program::find($id);
        $post->name_program = $request->post('name_program');

        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data program berhasil diubah!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data program tidak berhasil diubah!"], 400);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e], 400);
      }
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $hapus = Program::where('id_program', $id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data program berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data program tidak berhasil dihapus!"], 400);
    }
  }

  public function getProgram(Request $request)
  {
    $search = $request->search;

    if (isset($request->param)) {
      $data = Program::orderby('name_program', 'asc')
        ->select('id_program', 'name_program')
        ->where('parameter_jenis_klinik', $request->param)
        ->limit(10)
        ->get();
    } else {
      if ($search == '') {
        $data = Program::orderby('name_program', 'asc')
          ->select('id_program', 'name_program')
          ->limit(10)
          ->get();
      } else {
        $data = Program::orderby('name_program', 'asc')
          ->select('id_program', 'name_program')
          ->where('name_program', 'like', '%' . $search . '%')
          ->limit(10)
          ->get();
      }
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_program,
        "text" => $item->name_program
      );
    }

    return response()->json($response);
  }
}