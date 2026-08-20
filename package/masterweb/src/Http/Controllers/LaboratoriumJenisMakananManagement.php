<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\JenisMakanan;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\PacketDetail;
use \Smt\Masterweb\Models\SampleType;

use \Smt\Masterweb\Models\LaboratoriumPacket;





use Carbon\Carbon;


class LaboratoriumJenisMakananManagement extends Controller
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
    $user = Auth()->user();
    $level = $user->getlevel->level;



    if ($level == "elits-dev" || $level == "admin" || $level == "LAB") {
      $all_jenis_makanan = JenisMakanan::orderBy('name_jenis_makanan')->get();
      



      return view('masterweb::module.admin.laboratorium.jenis_makanan.list', compact('all_jenis_makanan'));
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

    if ($level == "elits-dev" || $level == "admin" || $level == "LAB") {

      return view('masterweb::module.admin.laboratorium.jenis_makanan.add');
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

    $validated = $request->validate([
      'name_jenis_makanan' => ['required'],

    ]);

    $jenis_makanan = new JenisMakanan();
    //uuid
    $uuid4 = Uuid::uuid4();

    $jenis_makanan->id_jenis_makanan = $uuid4->toString();
    $jenis_makanan->name_jenis_makanan = $request->post('name_jenis_makanan');
    $jenis_makanan->olahan_jenis_makanan = $request->post('olahan_jenis_makanan') ?? '';
    
    
    $jenis_makanan->save();

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'status'  => true,
            'message' => 'Jenis makanan berhasil ditambahkan.',
            'id'      => $jenis_makanan->id_jenis_makanan,
            'name'    => $jenis_makanan->name_jenis_makanan,
        ]);
    }

    return response()->json(['message' => 'Successfully add']);







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

    $jenis_makanan = JenisMakanan::findOrFail($id);




    return view('masterweb::module.admin.laboratorium.jenis_makanan.edit', compact('jenis_makanan', 'id'));
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

    $validated = $request->validate([
      // 'name_packet' => ['required', 'max:255'],
      // // 'methodAttributes' => ['required'],
      // 'price_packet' => ['required'],
      'name_jenis_makanan' => ['required'],
    ]);

    $jenis_makanan = JenisMakanan::findOrFail($id);
    $jenis_makanan->name_jenis_makanan = $request->post('name_jenis_makanan');
    $jenis_makanan->olahan_jenis_makanan = $request->post('olahan_jenis_makanan');
    $jenis_makanan->save();

   


    // if(isset ($data["laboratoriumAttributes"])){
    //     $laboratorium_packets= LaboratoriumPacket::where('packet_id',$id);
    //     if(isset($laboratorium_packets)){
    //         $laboratorium_packets->delete();
    //     }
    //     for($i = 0; $i < count($data["laboratoriumAttributes"]);$i++ ) {
    //         $packetdetail = new LaboratoriumPacket;
    //         $packetdetail->id_laboratorium_packet= Uuid::uuid4();
    //         $packetdetail->packet_id= $packet->id_packet;
    //         $packetdetail->laboratorium_id=$data["laboratoriumAttributes"][$i];
    //         $packetdetail->save();
    //     }

    // }


    return response()->json(['message' => 'Successfully edit']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $jenis_makanan = JenisMakanan::findOrFail($id);
    $jenis_makanan->delete();
   
    return redirect()->route('elits-jenis-makanan.index', [$id])->with('status', 'Jenis Makanan succesfully updated');
  }
}