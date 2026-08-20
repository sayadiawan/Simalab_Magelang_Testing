<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketExtra;
use Smt\Masterweb\Models\ParameterSubPaketExtra;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use \Smt\Masterweb\Models\User;

class LaboratoriumParameterPaketExtraKlinikManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function rules($request)
  {
    $rule = [
      'parameter_paket_klinik.*' => 'required',
      'nama_parameter_paket_extra' => 'required|max:256',
      'harga_parameter_paket_extra' => 'required|numeric',
    ];

    $pesan = [
      'parameter_paket_klinik.*.required' => 'Parameter paket klinik tidak boleh kosong!',
      'nama_parameter_paket_extra.*.required' => 'Nama parameter paket extra tidak boleh kosong!',
      'harga_parameter_paket_extra.required' => 'Harga parameter paket extra tidak boleh kosong!',
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
    // #1
    /* $user = Auth()->user();
    $level = $user->getlevel->level;

    if ($level == "elits-dev" || $level == "admin") {
    $data = ParameterPaketKlinik::orderBy('created_at')
    ->whereHas('parameterjenisklinik', function ($query) {
    return $query->whereNull('deleted_at');
    })
    ->whereHas('parametersatuanpaketklinik', function ($query) {
    return $query->whereNull('deleted_at');
    })
    ->get();

    $data_parameter_satuan = ParameterSatuanKlinik::orderBy('created_at', 'desc')->get();

    return view('masterweb::module.admin.laboratorium.parameter-paket-klinik.list', compact('data', 'data_parameter_satuan'));
    } else {
    return abort(404);
    } */

    // #2
    // $data = ParameterPaketExtra::join('ms_parameter_sub_paket_extra', 'ms_parameter_sub_paket_extra.id_parameter_paket_extra', '=', 'ms_parameter_paket_extra.id_parameter_paket_extra')
    // ->join('ms_parameter_paket_klinik', 'ms_parameter_sub_paket_extra.id_parameter_paket_klinik', '=', 'ms_parameter_paket_klinik.id_parameter_paket_klinik')
    // ->orderBy('ms_parameter_paket_extra.created_at')
    // ->get();
    $data = ParameterPaketExtra::orderBy('created_at')->get();
  //   foreach ($data as $item) {
  //     // Mengakses relasi 'parameterSubPaketExtras' yang harus didefinisikan di model ParameterPaketExtra
  //     foreach ($item->parameterSubPaketExtra as $val) {
  //         // Pastikan 'parameterPaketKlinik' adalah relasi 'belongsTo' dan bukan koleksi
  //         $parameterPaketKlinik = $val->parameterPaketKlinik;
          
  //         // Periksa apakah objek $parameterPaketKlinik ada
  //         if ($parameterPaketKlinik) {
  //             dd($parameterPaketKlinik->name_parameter_paket_klinik);
  //         }
  //     }
  // }
    return view('masterweb::module.admin.laboratorium.parameter-paket-extra-klinik.list', compact('data'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $parameter_paket = ParameterPaketKlinik::all();
    return view('masterweb::module.admin.laboratorium.parameter-paket-extra-klinik.add', compact('parameter_paket'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
      // Validasi request
      $validator = $this->rules($request->all());
  
      if ($validator->fails()) {
          return response()->json(['status' => false, 'pesan' => $validator->errors()]);
      }
  
      DB::beginTransaction();
  
      try {
          // Simpan data ParameterPaketExtra
          $post = new ParameterPaketExtra();
          $post->nama_parameter_paket_extra = $request->input('nama_parameter_paket_extra');
          $post->harga_parameter_paket_extra = $request->input('harga_parameter_paket_extra');
          $simpan = $post->save();
  
          // Simpan data ParameterSubPaketExtra
          $simpan_post_paket_extra = true;
          foreach ($request->input('parameter_paket_klinik', []) as $value) {
              $post_paket_extra = new ParameterSubPaketExtra();
              $post_paket_extra->id_parameter_paket_extra = $post->id_parameter_paket_extra;
              $post_paket_extra->id_parameter_paket_klinik = $value;
              if (!$post_paket_extra->save()) {
                  $simpan_post_paket_extra = false;
                  break; // Stop the loop if saving fails
              }
          }
  
          if ($simpan && $simpan_post_paket_extra) {
              DB::commit();
              return response()->json(['status' => true, 'pesan' => "Data parameter paket berhasil disimpan!"], 200);
          } else {
              DB::rollback();
              return response()->json(['status' => false, 'pesan' => "Data parameter paket tidak berhasil disimpan!"], 400);
          }
      } catch (\Exception $e) {
          DB::rollback();
          return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
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
    //get auth user

    $user = Auth()->user();
    $level = $user->getlevel->level;
    $item = ParameterPaketExtra::find($id);
    $parameter_paket = ParameterPaketKlinik::all();
    $selected_paket = $item->parameterSubPaketExtra->pluck('id_parameter_paket_klinik')->toArray();
   
    return view('masterweb::module.admin.laboratorium.parameter-paket-extra-klinik.edit', compact('item', 'parameter_paket', 'selected_paket'));
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
              // Update ParameterPaketExtra
              $post = ParameterPaketExtra::find($id);
              $post->nama_parameter_paket_extra = $request->input('nama_parameter_paket_extra');
              $post->harga_parameter_paket_extra = $request->input('harga_parameter_paket_extra');
              $simpan = $post->save();

              // Hapus semua ParameterSubPaketExtra yang terkait
              $parameterpaketextra = ParameterSubPaketExtra::where('id_parameter_paket_extra', $id);
              $parameterpaketextra->delete();

              // Simpan data ParameterSubPaketExtra yang baru
              $simpan_post_paket_extra = true;
              foreach ($request->input('parameter_paket_klinik', []) as $value) {
                  $post_paket_extra = new ParameterSubPaketExtra();
                  $post_paket_extra->id_parameter_paket_extra = $id; // Memastikan ID terisi
                  $post_paket_extra->id_parameter_paket_klinik = $value;
                  
                  if (!$post_paket_extra->save()) {
                      $simpan_post_paket_extra = false;
                      break; // Hentikan loop jika penyimpanan gagal
                  }
              }

              if ($simpan && $simpan_post_paket_extra) {
                  DB::commit();
                  return response()->json([
                      'status' => true,
                      'pesan' => "Data parameter paket extra berhasil disimpan!",
                      'redirect_url' => route('elits-parameter-paket-extra.index')
                  ], 200);
              } else {
                  DB::rollback();
                  return response()->json(['status' => false, 'pesan' => "Data parameter paket extra tidak berhasil disimpan!"], 400);
              }
          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
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
      DB::beginTransaction(); // Mulai transaksi

      try {
          // Hapus ParameterSubPaketExtra yang terkait
          $parametersubpaketextra = ParameterSubPaketExtra::where('id_parameter_paket_extra', $id);

          $hapus_sub = $parametersubpaketextra->delete();

          // Hapus ParameterPaketExtra
          $parameterpaketextra = ParameterPaketExtra::where('id_parameter_paket_extra', $id);
          $hapus = $parameterpaketextra->delete();

          if ($hapus == true && $hapus_sub == true) {
              DB::commit(); // Jika semua berhasil, commit transaksi
              return response()->json(['status' => true, 'pesan' => "Data parameter paket berhasil dihapus!"], 200);
          } else {
              DB::rollback(); // Jika ada kegagalan, rollback transaksi
              return response()->json(['status' => false, 'pesan' => "Data parameter paket tidak berhasil dihapus!"], 400);
          }
      } catch (\Exception $e) {
          DB::rollback(); // Rollback transaksi jika terjadi exception
          return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
      }
  }
}
