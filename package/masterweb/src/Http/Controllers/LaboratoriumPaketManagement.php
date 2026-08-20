<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\JenisMakanan;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\PacketDetail;
use \Smt\Masterweb\Models\SampleType;

use \Smt\Masterweb\Models\LaboratoriumPacket;





use Carbon\Carbon;


class LaboratoriumPaketManagement extends Controller
{
  /**
   * Samakan payload create/update: harga kosong dianggap 0, dukung alias price_base_packet,
   * dan pastikan methodAttributes berupa array (bukan string CSV dari hidden input).
   */
  private function normalizePacketWriteRequest(Request $request): void
  {
    $isEmpty = function ($v) {
      return $v === null || $v === '';
    };

    $bahan = $request->input('price_bahan_packet');
    if ($isEmpty($bahan)) {
      $bahan = $request->input('price_base_packet');
    }
    if ($isEmpty($bahan)) {
      $bahan = 0;
    }

    $jasa = $isEmpty($request->input('price_jasa_packet')) ? 0 : $request->input('price_jasa_packet');
    $sarana = $isEmpty($request->input('price_sarana_packet')) ? 0 : $request->input('price_sarana_packet');

    $total = $request->input('price_total_packet');
    if ($isEmpty($total)) {
      $total = (int) $bahan + (int) $jasa + (int) $sarana;
    }

    $methods = $request->input('methodAttributes');
    if (is_string($methods) && $methods !== '') {
      $methods = array_values(array_filter(array_map('trim', explode(',', $methods))));
      $request->merge(['methodAttributes' => $methods]);
    }

    $request->merge([
      'price_bahan_packet' => $bahan,
      'price_jasa_packet' => $jasa,
      'price_sarana_packet' => $sarana,
      'price_total_packet' => $total,
    ]);
  }

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

    $packets = Packet::with('sampletype')->get();

    return view('masterweb::module.admin.laboratorium.packet.list', compact('packets'));
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

    $sampletypes = SampleType::all();

    $all_jenis_makanan = JenisMakanan::all();


    return view('masterweb::module.admin.laboratorium.packet.add', compact('all_jenis_makanan', 'sampletypes'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->normalizePacketWriteRequest($request);

    $validated = $request->validate([
      'name_packet' => ['required', 'max:255'],
      'methodAttributes' => ['required', 'array', 'min:1'],
      'sample_type_id' => ['required'],
      'price_bahan_packet' => ['required', 'numeric', 'min:0'],
      'price_jasa_packet' => ['required', 'numeric', 'min:0'],
      'price_sarana_packet' => ['required', 'numeric', 'min:0'],
      'price_total_packet' => ['required', 'numeric', 'min:0'],

    ]);

    $packet = new Packet;
    //uuid
    $uuid4 = Uuid::uuid4();

    $packet->id_packet = $uuid4->toString();
    $packet->name_packet = $request->post('name_packet');
    $packet->sample_type_id = $request->post('sample_type_id');
    $jenis_makanan_id = $request->post('jenis_makanan_id');
    if (isset($jenis_makanan_id)) {
      $packet->jenis_makanan_id = $request->post('jenis_makanan_id');
    } else {
      $packet->jenis_makanan_id = NULL;
    }
    $packet->price_bahan_packet = $request->post('price_bahan_packet');
    $packet->price_jasa_packet = $request->post('price_jasa_packet');
    $packet->price_total_packet = $request->post('price_total_packet');
    $packet->price_sarana_packet = $request->post('price_sarana_packet');

    $packet->save();
    $data = $request->all();





    if (isset($data["methodAttributes"])) {
      for ($i = 0; $i < count($data["methodAttributes"]); $i++) {
        $packetdetail = new PacketDetail;
        $packetdetail->id_packet_detail = Uuid::uuid4();
        $packetdetail->packet_id = $packet->id_packet;
        $packetdetail->method_id = $data["methodAttributes"][$i];
        $packetdetail->save();
      }
    }

    // if(isset ($data["laboratoriumAttributes"])){
    //     for($i = 0; $i < count($data["laboratoriumAttributes"]);$i++ ) {
    //         $packetdetail = new LaboratoriumPacket;
    //         $packetdetail->id_laboratorium_packet= Uuid::uuid4();
    //         $packetdetail->packet_id= $packet->id_packet;
    //         $packetdetail->laboratorium_id=$data["laboratoriumAttributes"][$i];
    //         $packetdetail->save();
    //     }

    // }



    // return redirect()->route('elits-containers.index')->with(['status'=>'Container succesfully inserted']);


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

    $packet = Packet::findOrFail($id);
    $packet_details = PacketDetail::where('packet_id', $id)->join('ms_method', function ($join) {
      $join->on('ms_method.id_method', '=', 'ms_packet_detail.method_id')
        ->whereNull('ms_method.deleted_at')
        ->whereNull('ms_packet_detail.deleted_at');
    })
      ->get();

    $sampletypes = SampleType::all();
    $all_jenis_makanan = JenisMakanan::all();



    return view('masterweb::module.admin.laboratorium.packet.edit', compact('all_jenis_makanan', 'sampletypes', 'packet', 'packet_details', 'id'));
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
    $this->normalizePacketWriteRequest($request);

    $validated = $request->validate([
      // 'name_packet' => ['required', 'max:255'],
      // // 'methodAttributes' => ['required'],
      // 'price_packet' => ['required'],
      'name_packet' => ['required', 'max:255'],
      'methodAttributes' => ['required', 'array', 'min:1'],
      'sample_type_id' => ['required'],
      'price_bahan_packet' => ['required', 'numeric', 'min:0'],
      'price_jasa_packet' => ['required', 'numeric', 'min:0'],
      'price_sarana_packet' => ['required', 'numeric', 'min:0'],
      'price_total_packet' => ['required', 'numeric', 'min:0'],

    ]);

    $packet = Packet::findOrFail($id);
    $packet->name_packet = $request->post('name_packet');
    $packet->sample_type_id = $request->post('sample_type_id');
    $packet->price_bahan_packet = $request->post('price_bahan_packet');
    $packet->price_jasa_packet = $request->post('price_jasa_packet');
    $packet->price_total_packet = $request->post('price_total_packet');
    $packet->price_sarana_packet = $request->post('price_sarana_packet');

    $jenis_makanan_id = $request->post('jenis_makanan_id');
    // dd($jenis_makanan_id);
    if (isset($jenis_makanan_id)) {
      $packet->jenis_makanan_id = $request->post('jenis_makanan_id');
    } else {
      $packet->jenis_makanan_id = NULL;
    }
    $packet->save();

    PacketDetail::where('packet_id', $id)->delete();

    $data = $request->all();

    if (isset($data["methodAttributes"])) {
      for ($i = 0; $i < count($data["methodAttributes"]); $i++) {
        $packetdetail = new PacketDetail;
        $packetdetail->id_packet_detail = Uuid::uuid4();
        $packetdetail->packet_id = $packet->id_packet;
        $packetdetail->method_id = $data["methodAttributes"][$i];
        $packetdetail->save();
      }
    }



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

  /** Ambil data paket beserta method_ids untuk modal edit di halaman sample/add */
  public function getPacketData($id)
  {
    $packet     = Packet::findOrFail($id);
    $method_ids = PacketDetail::where('packet_id', $id)
      ->whereNull('deleted_at')
      ->pluck('method_id')
      ->toArray();

    return response()->json([
      'status'              => true,
      'id_packet'           => $packet->id_packet,
      'name_packet'         => $packet->name_packet,
      'sample_type_id'      => $packet->sample_type_id,
      'price_bahan_packet'  => (int) ($packet->price_bahan_packet  ?? 0),
      'price_sarana_packet' => (int) ($packet->price_sarana_packet ?? 0),
      'price_jasa_packet'   => (int) ($packet->price_jasa_packet   ?? 0),
      'price_total_packet'  => (int) ($packet->price_total_packet  ?? 0),
      'method_ids'          => $method_ids,
    ]);
  }

  /** Tambah paket baru via AJAX dari halaman sample/add */
  public function storeAjax(Request $request)
  {
    $this->normalizePacketWriteRequest($request);

    $request->validate([
      'name_packet'         => ['required', 'max:255'],
      'methodAttributes'    => ['required', 'array', 'min:1'],
      'sample_type_id'      => ['required'],
      'price_bahan_packet'  => ['required', 'numeric', 'min:0'],
      'price_jasa_packet'   => ['required', 'numeric', 'min:0'],
      'price_sarana_packet' => ['required', 'numeric', 'min:0'],
      'price_total_packet'  => ['required', 'numeric', 'min:0'],
    ]);

    $packet                    = new Packet;
    $packet->id_packet         = Uuid::uuid4()->toString();
    $packet->name_packet       = $request->post('name_packet');
    $packet->sample_type_id    = $request->post('sample_type_id');
    $packet->price_bahan_packet  = $request->post('price_bahan_packet');
    $packet->price_jasa_packet   = $request->post('price_jasa_packet');
    $packet->price_total_packet  = $request->post('price_total_packet');
    $packet->price_sarana_packet = $request->post('price_sarana_packet');
    $jenis = $request->post('jenis_makanan_id');
    $packet->jenis_makanan_id = $jenis ?: null;
    $packet->save();

    foreach ($request->input('methodAttributes', []) as $methodId) {
      $detail                   = new PacketDetail;
      $detail->id_packet_detail = Uuid::uuid4()->toString();
      $detail->packet_id        = $packet->id_packet;
      $detail->method_id        = $methodId;
      $detail->save();
    }

    return response()->json([
      'status'             => true,
      'pesan'              => 'Paket berhasil ditambahkan.',
      'id_packet'          => $packet->id_packet,
      'name_packet'        => $packet->name_packet,
      'price_total_packet' => (int) $packet->price_total_packet,
      'sample_type_id'     => $packet->sample_type_id,
    ]);
  }

  /** Update paket via AJAX dari halaman sample/add */
  public function updateAjax(Request $request, $id)
  {
    $this->normalizePacketWriteRequest($request);

    $request->validate([
      'name_packet'         => ['required', 'max:255'],
      'methodAttributes'    => ['required', 'array', 'min:1'],
      'price_bahan_packet'  => ['required', 'numeric', 'min:0'],
      'price_jasa_packet'   => ['required', 'numeric', 'min:0'],
      'price_sarana_packet' => ['required', 'numeric', 'min:0'],
      'price_total_packet'  => ['required', 'numeric', 'min:0'],
    ]);

    $packet                    = Packet::findOrFail($id);
    $packet->name_packet       = $request->post('name_packet');
    $packet->price_bahan_packet  = $request->post('price_bahan_packet');
    $packet->price_jasa_packet   = $request->post('price_jasa_packet');
    $packet->price_total_packet  = $request->post('price_total_packet');
    $packet->price_sarana_packet = $request->post('price_sarana_packet');
    $packet->save();

    PacketDetail::where('packet_id', $id)->delete();

    foreach ($request->input('methodAttributes', []) as $methodId) {
      $detail                   = new PacketDetail;
      $detail->id_packet_detail = Uuid::uuid4()->toString();
      $detail->packet_id        = $packet->id_packet;
      $detail->method_id        = $methodId;
      $detail->save();
    }

    return response()->json([
      'status'             => true,
      'pesan'              => 'Paket berhasil diperbarui.',
      'id_packet'          => $packet->id_packet,
      'name_packet'        => $packet->name_packet,
      'price_total_packet' => (int) $packet->price_total_packet,
    ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $packet = Packet::findOrFail($id);
    $packet->delete();
    PacketDetail::where('packet_id', $id)->delete();

    return redirect()->route('elits-packet.index', [$id])->with('status', 'Paket succesfully updated');
  }
}