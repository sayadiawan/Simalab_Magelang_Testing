<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\LaboratoriumProgress;



use Carbon\Carbon;


class LaboratoriumInputLaboratoriumManagement extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $user = Auth()->user();
    $level = $user->getlevel->level;



    if ($level == "elits-dev" || $level == "admin") {
      $all_laboratorium = Laboratorium::orderBy('ms_laboratorium.created_at')
        ->leftJoin('ms_users', function ($join) {
          $join->on('ms_users.id', '=', 'ms_laboratorium.koordinator_id')
            ->whereNull('ms_users.deleted_at')
            ->whereNull('ms_laboratorium.deleted_at');
        })
        ->get();



      return view('masterweb::module.admin.laboratorium.input-laboratorium.list', compact('all_laboratorium'));
    } else {
      return abort(404);
    }
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
    $level = $user->getlevel->level;
    $koordinators = User::where('level', '0e6da765-0f3a-4471-9e1d-6af257e60a70')->get();


    if ($level == "elits-dev" || $level == "admin") {
      return view('masterweb::module.admin.laboratorium.input-laboratorium.add', compact('koordinators'));
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
    // dd($request->all());


    $laboratorium = new Laboratorium;
    //uuid
    $uuid4 = Uuid::uuid4();

    $laboratorium->id_laboratorium = $uuid4->toString();
    $laboratorium->nama_laboratorium = $request->post('nama_laboratorium');
    $laboratorium->kode_laboratorium = $request->post('kode_laboratorium');
    $laboratorium->nama_ttd_kepala_laboratorium = $request->post('nama_ttd_kepala_laboratorium');
    $laboratorium->nip_kepala_laboratorium = $request->post('nip_kepala_laboratorium');
    $laboratorium->koordinator_id = $request->post('koordinator_id');

    $laboratorium->save();

    $progress_names = $request->post('progress_name');
    if (isset($progress_names)) {

      $link_names = $request->post('link_name');

      for ($i = 0; $i < count($link_names); $i++) {
        $laboratoriumprogress = new LaboratoriumProgress;
        $laboratoriumprogress->laboratorium_id = $laboratorium->id_laboratorium;
        $laboratoriumprogress->name_progress = $progress_names[$i];
        $laboratoriumprogress->link = $link_names[$i];
        $laboratoriumprogress->order_sort = $i + 1;
        $laboratoriumprogress->save();
      }
    }

    return redirect()->route('elits-input-laboratorium.index')->with(['status' => 'Container succesfully inserted']);



    //return redirect()->route('module.admin.users.index')->with('status', 'User succesfully inserted');
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
    $user = Auth()->user();
    $level = $user->getlevel->level;
    $laboratorium = Laboratorium::findOrFail($id);
    $laboratoriumprogress = LaboratoriumProgress::where('laboratorium_id', $id)->orderBy('order_sort', 'asc')->get();
    $koordinators = User::where('level', '0e6da765-0f3a-4471-9e1d-6af257e60a70')->get();
    return view('masterweb::module.admin.laboratorium.input-laboratorium.edit', compact('laboratorium', 'koordinators', 'id', 'laboratoriumprogress'));
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
    $laboratorium = Laboratorium::findOrFail($id);
    $laboratorium->nama_laboratorium = $request->post('nama_laboratorium');
    $laboratorium->kode_laboratorium = $request->post('kode_laboratorium');
    $laboratorium->koordinator_id = $request->post('koordinator_id');
    $laboratorium->nama_ttd_kepala_laboratorium = $request->post('nama_ttd_kepala_laboratorium');
    $laboratorium->nip_kepala_laboratorium = $request->post('nip_kepala_laboratorium');
    $laboratorium->save();


    $progress_names = $request->post('progress_name');
    $link_names = $request->post('link_name');

    // Hanya ganti progress jika form mengirim daftar progress.
    // Hindari hard-delete semua progress saat form kosong (penyebab progress hilang).
    if (is_array($progress_names) && is_array($link_names) && count($link_names) > 0) {
      LaboratoriumProgress::where('laboratorium_id', '=', $id)->get()->each(function ($row) {
        $row->delete();
      });

      for ($i = 0; $i < count($link_names); $i++) {
        if (!isset($progress_names[$i]) || trim((string) $progress_names[$i]) === '' || trim((string) $link_names[$i]) === '') {
          continue;
        }
        $laboratoriumprogress = new LaboratoriumProgress;
        $laboratoriumprogress->laboratorium_id = $laboratorium->id_laboratorium;
        $laboratoriumprogress->name_progress = $progress_names[$i];
        $laboratoriumprogress->link = $link_names[$i];
        $laboratoriumprogress->order_sort = $i + 1;
        $laboratoriumprogress->save();
      }
    }
    return redirect()->route('elits-input-laboratorium.index', [$id])->with('status', 'Laboratorium succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $laboratorium = Laboratorium::findOrFail($id);
    $laboratorium->delete();
    return redirect()->route('elits-input-laboratorium.index', [$id])->with('status', 'Laboratorium succesfully updated');
  }

  public function getLaboratorium(Request $request)
  {
    $search = $request->search;

    if (isset($request->param)) {
      $data = Laboratorium::orderby('nama_laboratorium', 'asc')
        ->select('id_laboratorium', 'nama_laboratorium', 'kode_laboratorium')
        ->where('nama_laboratorium', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    } else {
      if ($search == '') {
        $data = Laboratorium::orderby('nama_laboratorium', 'asc')
          ->select('id_laboratorium', 'nama_laboratorium', 'kode_laboratorium')
          ->limit(10)
          ->get();
      } else {
        $data = Laboratorium::orderby('nama_laboratorium', 'asc')
          ->select('id_laboratorium', 'nama_laboratorium', 'kode_laboratorium')
          ->where('nama_laboratorium', 'like', '%' . $search . '%')
          ->limit(10)
          ->get();
      }
    }

    
    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_laboratorium,
        "text" => $item->nama_laboratorium,
        "kode_laboratorium" => $item->kode_laboratorium,
      );
    }

    return response()->json($response);
  }

   public function getLaboratoriumNonKlinik(Request $request)
  {
    $search = $request->search;

    if (isset($request->param)) {
      $data = Laboratorium::orderby('nama_laboratorium', 'asc')
        ->select('id_laboratorium', 'nama_laboratorium')
        ->where('kode_laboratorium','!=','KLI')
        ->where('nama_laboratorium', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    } else {
      if ($search == '') {
        $data = Laboratorium::orderby('nama_laboratorium', 'asc')
          ->select('id_laboratorium', 'nama_laboratorium')
           ->where('kode_laboratorium','!=','KLI')
          ->limit(10)
          ->get();
      } else {
        $data = Laboratorium::orderby('nama_laboratorium', 'asc')
          ->select('id_laboratorium', 'nama_laboratorium')
           ->where('kode_laboratorium','!=','KLI')
          ->where('nama_laboratorium', 'like', '%' . $search . '%')
          ->limit(10)
          ->get();
      }
    }

    
    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_laboratorium,
        "text" => $item->nama_laboratorium
      );
    }

    return response()->json($response);
  }
}