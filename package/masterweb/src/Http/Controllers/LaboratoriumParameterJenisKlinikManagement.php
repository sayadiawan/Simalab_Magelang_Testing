<?php

namespace Smt\Masterweb\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;

use \Smt\Masterweb\Models\Method;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;

use Illuminate\Validation\Rule;
use Kreait\Firebase\Http\Requests;

class LaboratoriumParameterJenisKlinikManagement extends Controller
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
    // #1 attempt
    /* $user = Auth()->user();
    $level = $user->getlevel->level;

    if ($level == "elits-dev" || $level == "admin") {
      $data = ParameterJenisKlinik::orderBy('created_at', 'desc')->get();

      return view('masterweb::module.admin.laboratorium.parameter-jenis-klinik.list', compact('data'));
    } else {
      return abort(404);
    } */

    // #2 attempt - with parent relationship and hierarchy ordering
    $data = ParameterJenisKlinik::with('parent')
              ->orderBy('level', 'asc')
              ->orderBy('sort_parameter_jenis_klinik', 'asc')
              ->orderBy('created_at', 'desc')
              ->get();

    return view('masterweb::module.admin.laboratorium.parameter-jenis-klinik.list', compact('data'));
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

    // Get parent options (only parent-level items)
    $parentOptions = ParameterJenisKlinik::whereNull('id_parameter_jenis_klinik_parent')
                                        ->orderBy('name_parameter_jenis_klinik', 'asc')
                                        ->get();

    return view('masterweb::module.admin.laboratorium.parameter-jenis-klinik.add', compact('parentOptions'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validator = Validator::make(
      $request->all(),
      [
        'name_parameter_jenis_klinik' => 'required|max:256',
        'code_parameter_jenis_klinik' => 'required|max:6|unique:ms_parameter_jenis_klinik,code_parameter_jenis_klinik,NULL,id_parameter_jenis_klinik,deleted_at,NULL'
      ],
      [
        'name_parameter_jenis_klinik.required' => 'Nama parameter jenis klinik tidak boleh kosong!',
        'code_parameter_jenis_klinik.required' => 'Kode parameter jenis klinik tidak boleh kosong!',
        'code_parameter_jenis_klinik.max' => 'Kode parameter jenis klinik tidak boleh lebih dari 6 karakter!',
        'code_parameter_jenis_klinik.unique' => 'Kode parameter jenis klinik sudah tersedia, silahkan masukkan kode yang berbeda!',
      ]
    );

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = new ParameterJenisKlinik();
        $post->name_parameter_jenis_klinik = $request->post('name_parameter_jenis_klinik');
        $post->code_parameter_jenis_klinik = $request->post('code_parameter_jenis_klinik');
        
        // Jika urutan tidak dikirim dari form, tetapkan otomatis ke max+1
        $requestedSort = $request->post('sort_parameter_jenis_klinik');
        if ($requestedSort === null || $requestedSort === '') {
          $maxSort = ParameterJenisKlinik::max('sort_parameter_jenis_klinik');
          $post->sort_parameter_jenis_klinik = ($maxSort ?? 0) + 1;
        } else {
          $post->sort_parameter_jenis_klinik = (int)$requestedSort;
        }
        
        $post->id_parameter_jenis_klinik_parent = $request->post('id_parameter_jenis_klinik_parent');
        $post->level = $request->post('id_parameter_jenis_klinik_parent') ? 1 : 0; // 0 = parent, 1 = child
        $post->sort_order = $request->post('sort_order', 0);

        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json([
            'status' => true,
            'pesan' => 'Data parameter jenis berhasil disimpan!',
            'id_parameter_jenis_klinik' => $post->id_parameter_jenis_klinik,
            'name_parameter_jenis_klinik' => $post->name_parameter_jenis_klinik,
            'code_parameter_jenis_klinik' => $post->code_parameter_jenis_klinik,
          ], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter jenis tidak berhasil disimpan!"], 400);
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
    //get auth user

    $user = Auth()->user();
    $level = $user->getlevel->level;
    $item = ParameterJenisKlinik::find($id);

    $existing_sorts = ParameterJenisKlinik::orderBy('sort_parameter_jenis_klinik')
    ->pluck('name_parameter_jenis_klinik', 'sort_parameter_jenis_klinik');

    // Get parent options (only parent-level items, exclude current item)
    $parentOptions = ParameterJenisKlinik::whereNull('id_parameter_jenis_klinik_parent')
                                        ->where('id_parameter_jenis_klinik', '!=', $id)
                                        ->orderBy('name_parameter_jenis_klinik', 'asc')
                                        ->get();

    return view('masterweb::module.admin.laboratorium.parameter-jenis-klinik.edit', compact('item', 'existing_sorts', 'parentOptions'));
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
    $post = ParameterJenisKlinik::find($id);

    if ($request->code_parameter_jenis_klinik !== $post->code_parameter_jenis_klinik) {
      $validator = Validator::make(
        $request->all(),
        [
          'name_parameter_jenis_klinik' => 'required|max:256',
          'code_parameter_jenis_klinik' => 'required|max:6|unique:ms_parameter_jenis_klinik,code_parameter_jenis_klinik,NULL,id_parameter_jenis_klinik,deleted_at,NULL'
        ],
        [
          'name_parameter_jenis_klinik.required' => 'Nama parameter jenis klinik tidak boleh kosong!',
          'code_parameter_jenis_klinik.required' => 'Kode parameter jenis klinik tidak boleh kosong!',
          'code_parameter_jenis_klinik.max' => 'Kode parameter jenis klinik tidak boleh lebih dari 6 karakter!',
          'code_parameter_jenis_klinik.unique' => 'Kode parameter jenis klinik sudah tersedia, silahkan masukkan kode yang berbeda!',
        ]
      );
    } else {
      $validator = Validator::make(
        $request->all(),
        [
          'name_parameter_jenis_klinik' => 'required|max:256',
          'code_parameter_jenis_klinik' => 'required|max:6'
        ],
        [
          'name_parameter_jenis_klinik.required' => 'Nama parameter jenis klinik tidak boleh kosong!',
          'code_parameter_jenis_klinik.required' => 'Kode parameter jenis klinik tidak boleh kosong!',
          'code_parameter_jenis_klinik.max' => 'Kode parameter jenis klinik tidak boleh lebih dari 6 karakter!',
        ]
      );
    }

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post->name_parameter_jenis_klinik = $request->post('name_parameter_jenis_klinik');
        $post->code_parameter_jenis_klinik = $request->post('code_parameter_jenis_klinik');

        // Simpan nomor urut yang lama
        $old_sort = $post->sort_parameter_jenis_klinik;

        // DD($old_sort);

        // Ambil nomor urut setelah yang dipilih di dropdown
        $after_sort = $request->post('after_sort_parameter_jenis_klinik');

        // Tentukan nomor urut yang baru (after_sort + 1)
        if($after_sort != null){
          $new_sort = $after_sort ? ($after_sort + 1) : 1;
        }else{
          $new_sort = $old_sort;
        }

        // Cek jika item yang sedang diedit tidak memiliki nomor urut sebelumnya
        if (is_null($old_sort) || $old_sort == 0) {
            // Jika item tidak memiliki urutan sebelumnya, update semua item
            // dengan nomor urut lebih besar dari atau sama dengan new_sort
            ParameterJenisKlinik::where('sort_parameter_jenis_klinik', '>=', $new_sort)
                ->increment('sort_parameter_jenis_klinik');
        } else {
            // Jika nomor urut diubah
            if ($old_sort != $new_sort) {
                if ($new_sort > $old_sort) {
                    // Jika pindah ke bawah, kurangi urutan item antara old_sort dan new_sort
                    ParameterJenisKlinik::whereBetween('sort_parameter_jenis_klinik', [$old_sort + 1, $new_sort])
                        ->decrement('sort_parameter_jenis_klinik');
                } else {
                    // Jika pindah ke atas, tambahkan urutan item antara new_sort dan old_sort
                    ParameterJenisKlinik::whereBetween('sort_parameter_jenis_klinik', [$new_sort, $old_sort - 1])
                        ->increment('sort_parameter_jenis_klinik');
                }
            }
        }

        // Update urutan item yang sedang diedit
        $post->sort_parameter_jenis_klinik = $new_sort;
        
        // Update parent and hierarchy fields
        $post->id_parameter_jenis_klinik_parent = $request->post('id_parameter_jenis_klinik_parent');
        $post->level = $request->post('id_parameter_jenis_klinik_parent') ? 1 : 0; // 0 = parent, 1 = child
        $post->sort_order = $request->post('sort_order', 0);

        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter jenis berhasil diubah!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter jenis tidak berhasil diubah!"], 400);
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
    $hapus = ParameterJenisKlinik::where('id_parameter_jenis_klinik', $id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter jenis berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter jenis tidak berhasil dihapus!"], 400);
    }
  }

  public function getParameterJenisKlinik(Request $request)
  {
    $search = $request->search;

    if ($search == '') {
      $data = ParameterJenisKlinik::orderby('name_parameter_jenis_klinik', 'asc')->select('id_parameter_jenis_klinik', 'name_parameter_jenis_klinik', 'code_parameter_jenis_klinik')->limit(10)->get();
    } else {
      $data = ParameterJenisKlinik::orderby('name_parameter_jenis_klinik', 'asc')->select('id_parameter_jenis_klinik', 'name_parameter_jenis_klinik', 'code_parameter_jenis_klinik')->where('name_parameter_jenis_klinik', 'like', '%' . $search . '%')->limit(10)->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_jenis_klinik,
        "text" => $item->name_parameter_jenis_klinik . ' - ' . $item->code_parameter_jenis_klinik
      );
    }

    return response()->json($response);
  }

  // Reorder sort_parameter_jenis_klinik via drag & drop
  public function reorder(Request $request)
  {
    // Support two payload modes:
    // 1) ids: [id1, id2, ...] -> sequential ordering by current list
    // 2) orders: [{id, sort}, ...] -> explicit sort values editable by user
    $orders = $request->post('orders');
    $ids = $request->post('ids');

    if ((!is_array($orders) || count($orders) === 0) && (!is_array($ids) || count($ids) === 0)) {
      return response()->json(['status' => false, 'pesan' => 'Tidak ada data urutan'], 200);
    }

    DB::beginTransaction();
    try {
      if (is_array($orders) && count($orders) > 0) {
        // Simpan urutan berdasarkan posisi di list (drag & drop order)
        // Urutan array $orders sudah mencerminkan urutan drag & drop
        $sort = 1;
        foreach ($orders as $row) {
          if (!isset($row['id'])) { continue; }
          $id = $row['id'];
          // Gunakan urutan berdasarkan posisi di array (drag & drop order)
          // Jika user mengedit input manual, tetap gunakan posisi array untuk konsistensi
          ParameterJenisKlinik::where('id_parameter_jenis_klinik', $id)
            ->update(['sort_parameter_jenis_klinik' => $sort++]);
        }
      } else {
        // Fallback: sequential ordering by ids
        $sort = 1;
        foreach ($ids as $id) {
          ParameterJenisKlinik::where('id_parameter_jenis_klinik', $id)
            ->update(['sort_parameter_jenis_klinik' => $sort++]);
        }
      }

      DB::commit();
      return response()->json(['status' => true, 'pesan' => 'Urutan berhasil disimpan']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan urutan']);
    }
  }

  // Reorder page
  public function reorderPage(Request $request)
  {
    $data = ParameterJenisKlinik::with('parent')
      ->whereNull('deleted_at')
      ->orderByRaw('COALESCE(sort_parameter_jenis_klinik, 999999) ASC')
      ->orderBy('created_at', 'ASC')
      ->get();
    return view('masterweb::module.admin.laboratorium.parameter-jenis-klinik.reorder', compact('data'));
  }

  /**
   * Hapus parameter jenis klinik yang tidak digunakan di parameter satuan klinik dan parameter paket klinik
   */
  public function deleteUnused(Request $request)
  {
    DB::beginTransaction();
    try {
      // Ambil semua ID parameter jenis klinik yang digunakan di parameter satuan klinik
      $usedInSatuan = ParameterSatuanKlinik::whereNotNull('parameter_jenis_klinik')
        ->whereNull('deleted_at')
        ->distinct()
        ->pluck('parameter_jenis_klinik')
        ->toArray();

      // Ambil semua ID parameter jenis klinik yang digunakan di parameter paket jenis klinik
      $usedInPaket = ParameterPaketJenisKlinik::whereNotNull('parameter_jenis_klinik_id')
        ->whereNull('deleted_at')
        ->distinct()
        ->pluck('parameter_jenis_klinik_id')
        ->toArray();

      // Gabungkan semua ID yang digunakan
      $usedIds = array_unique(array_merge($usedInSatuan, $usedInPaket));

      // Cari parameter jenis klinik yang TIDAK digunakan
      $unused = ParameterJenisKlinik::whereNotIn('id_parameter_jenis_klinik', $usedIds)
        ->whereNull('deleted_at')
        ->get();

      $count = $unused->count();
      $deletedNames = [];

      // Hapus yang tidak terpakai
      foreach ($unused as $item) {
        $deletedNames[] = $item->name_parameter_jenis_klinik;
        $item->delete();
      }

      DB::commit();

      if ($count > 0) {
        $message = "Berhasil menghapus {$count} parameter jenis klinik yang tidak terpakai:\n" . implode("\n", array_slice($deletedNames, 0, 10));
        if ($count > 10) {
          $message .= "\n... dan " . ($count - 10) . " lainnya";
        }
        return response()->json(['status' => true, 'pesan' => $message], 200);
      } else {
        return response()->json(['status' => true, 'pesan' => 'Tidak ada parameter jenis klinik yang tidak terpakai'], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal menghapus: ' . $e->getMessage()], 400);
    }
  }
}
