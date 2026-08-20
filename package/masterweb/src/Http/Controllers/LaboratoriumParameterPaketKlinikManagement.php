<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use \Smt\Masterweb\Models\User;

class LaboratoriumParameterPaketKlinikManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function rules($request)
  {
    $rule = [
      'parameter_jenis_klinik.*' => 'required',
      'parameter_satuan_klinik.*' => 'required',
      'name_parameter_paket_klinik' => 'required|max:256',
      'harga_parameter_paket_klinik' => 'required|numeric',
    ];

    $pesan = [
      'parameter_jenis_klinik.*.required' => 'Parameter jenis klinik tidak boleh kosong!',
      'parameter_satuan_klinik.*.required' => 'Parameter satuan klinik tidak boleh kosong!',
      'name_parameter_paket_klinik.required' => 'Nama parameter paket klinik tidak boleh kosong!',
      'harga_parameter_paket_klinik.required' => 'Harga parameter paket klinik tidak boleh kosong!',
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
    $data = ParameterPaketKlinik::orderBy('sort', 'asc')
      ->orderBy('created_at', 'asc')
      ->whereHas('parameterpaketjenisklinik', function ($query) {
        return $query->whereNull('deleted_at');
      })
      ->get();

    $data_paket_jenis_klinik = ParameterPaketJenisKlinik::orderBy('created_at', 'desc')->get();

    return view('masterweb::module.admin.laboratorium.parameter-paket-klinik.list', compact('data', 'data_paket_jenis_klinik'));
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

    return view('masterweb::module.admin.laboratorium.parameter-paket-klinik.add');
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

    // dd($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = new ParameterPaketKlinik();
        $post->name_parameter_paket_klinik = $request->post('name_parameter_paket_klinik');
        $post->singkatan_laporan = $request->post('singkatan_laporan') ?: null;
        $post->kategori_laporan = $request->post('kategori_laporan') ?: null;
        $post->is_agregat_laporan = $request->post('is_agregat_laporan') ? 1 : 0;
        $post->tampil_di_laporan = $request->has('tampil_di_laporan') ? 1 : 0;
        $post->harga_parameter_paket_klinik = $request->post('harga_parameter_paket_klinik');
        $simpan = $post->save();

        $sorting_parameter_jenis_klinik = 1;
        foreach ($request->post('parameter_jenis_klinik') as $key_jenis_klinik => $value) {
          $post_detail = new ParameterPaketJenisKlinik();
          $post_detail->parameter_paket_klinik_id = $post->id_parameter_paket_klinik;
          $post_detail->parameter_jenis_klinik_id = $request->post('parameter_jenis_klinik')[$key_jenis_klinik];
          $post_detail->sort = $sorting_parameter_jenis_klinik;
          $simpan_detail = $post_detail->save();

          $sorting_parameter_satuan_klinik = 1;

          foreach ($request->post('parameter_satuan_klinik')[$key_jenis_klinik] as $key => $value) {
            $post_detail_paket_satuan = new ParameterSatuanPaketKlinik();
            $post_detail_paket_satuan->parameter_paket_jenis_klinik = $post_detail->id_parameter_paket_jenis_klinik;
            $post_detail_paket_satuan->parameter_satuan_klinik = $request->post('parameter_satuan_klinik')[$key_jenis_klinik][$key];
            $post_detail_paket_satuan->sorting = $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] != null || $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] != 0 ? $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] : $sorting_parameter_satuan_klinik;
            $simpan_detail_post_detail_paket_satuan = $post_detail_paket_satuan->save();
            $sorting_parameter_satuan_klinik++;
          }

          $sorting_parameter_jenis_klinik++;
        }

        DB::commit();

        if ($simpan == true && $simpan_detail == true && $simpan_detail_post_detail_paket_satuan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter paket berhasil disimpan!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter paket tidak berhasil disimpan!"], 400);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
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
    $item = ParameterPaketKlinik::find($id);
    // $data_satuan_paket = ParameterSatuanKlinik::whereNull('deleted_at')->get();
    // $item_satuan_paket = ParameterSatuanPaketKlinik::where('parameter_paket_klinik', $id)->get();

    // dd($item_satuan_paket);

    return view('masterweb::module.admin.laboratorium.parameter-paket-klinik.edit', compact('item'));
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
        $post = ParameterPaketKlinik::find($id);
        $post->name_parameter_paket_klinik = $request->post('name_parameter_paket_klinik');
        $post->singkatan_laporan = $request->post('singkatan_laporan') ?: null;
        $post->kategori_laporan = $request->post('kategori_laporan') ?: null;
        $post->is_agregat_laporan = $request->post('is_agregat_laporan') ? 1 : 0;
        $post->tampil_di_laporan = $request->has('tampil_di_laporan') ? 1 : 0;
        $post->harga_parameter_paket_klinik = $request->post('harga_parameter_paket_klinik');
        $simpan = $post->save();

        $parameterpaketjenisklinik = ParameterPaketJenisKlinik::where('parameter_paket_klinik_id', $id);

        $get_parameterpaketjenisklinik = $parameterpaketjenisklinik->get();

        if (count($get_parameterpaketjenisklinik) > 0) {
          foreach ($get_parameterpaketjenisklinik as $key => $value_parameterpaketjenisklinik) {
            ParameterSatuanPaketKlinik::where('parameter_paket_jenis_klinik', $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik)->delete();
          }
        }

        $parameterpaketjenisklinik->delete();

        $sorting_parameter_jenis_klinik = 1;
        foreach ($request->post('parameter_jenis_klinik') as $key_jenis_klinik => $value) {
          $post_detail = new ParameterPaketJenisKlinik();
          $post_detail->parameter_paket_klinik_id = $post->id_parameter_paket_klinik;
          $post_detail->parameter_jenis_klinik_id = $request->post('parameter_jenis_klinik')[$key_jenis_klinik];
          $post_detail->sort = $sorting_parameter_jenis_klinik;
          $simpan_detail = $post_detail->save();

          $sorting_parameter_satuan_klinik = 1;

          foreach ($request->post('parameter_satuan_klinik')[$key_jenis_klinik] as $key => $value) {
            $post_detail_paket_satuan = new ParameterSatuanPaketKlinik();
            $post_detail_paket_satuan->parameter_paket_jenis_klinik = $post_detail->id_parameter_paket_jenis_klinik;
            $post_detail_paket_satuan->parameter_satuan_klinik = $request->post('parameter_satuan_klinik')[$key_jenis_klinik][$key];
            $post_detail_paket_satuan->sorting = $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] != null || $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] != 0 ? $request->post('sorting_parameter_satuan_paket_klinik')[$key_jenis_klinik][$key] : $sorting_parameter_satuan_klinik;
            $simpan_detail_post_detail_paket_satuan = $post_detail_paket_satuan->save();
            $sorting_parameter_satuan_klinik++;
          }

          $sorting_parameter_jenis_klinik++;
        }

        DB::commit();

        if ($simpan == true && $simpan_detail == true && $simpan_detail_post_detail_paket_satuan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter paket berhasil diubah!"], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter paket tidak berhasil diubah!"], 400);
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
    $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $id);
    $parameterpaketjenisklinik = ParameterPaketJenisKlinik::where('parameter_paket_klinik_id', $id);

    $get_parameterpaketjenisklinik = $parameterpaketjenisklinik->get();

    if (count($get_parameterpaketjenisklinik) > 0) {
      foreach ($get_parameterpaketjenisklinik as $key => $value_parameterpaketjenisklinik) {
        ParameterSatuanPaketKlinik::where('parameter_paket_jenis_klinik', $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik)->delete();
      }
    }

    $hapus_detail = $parameterpaketjenisklinik->delete();
    $hapus = $parameterpaketklinik->delete();

    if ($hapus == true && $hapus_detail == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter paket berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter paket tidak berhasil dihapus!"], 400);
    }
  }

  // return by ajax
  public function getParameterPaketKlinik(Request $request)
  {
    $search = $request->search;

    if ($search == '') {
      $data = ParameterPaketKlinik::orderby('name_parameter_paket_klinik', 'asc')->select('id_parameter_paket_klinik', 'name_parameter_paket_klinik')->limit(10)->get();
    } else {
      $data = ParameterPaketKlinik::orderby('name_parameter_paket_klinik', 'asc')->select('id_parameter_paket_klinik', 'name_parameter_paket_klinik')->where('name_parameter_paket_klinik', 'like', '%' . $search . '%')->limit(10)->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_paket_klinik,
        "text" => $item->name_parameter_paket_klinik,
      );
    }

    return response()->json($response);
  }

  // Update sort order
  public function updateSort(Request $request)
  {
    try {
      $items = $request->items;

      foreach ($items as $item) {
        ParameterPaketKlinik::where('id_parameter_paket_klinik', $item['id'])
          ->update(['sort' => $item['sort']]);
      }

      return response()->json(['status' => true, 'pesan' => 'Urutan berhasil diupdate!'], 200);
    } catch (\Exception $e) {
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }

  // Show parameter arrangement page
  // Show category layout page
  public function categoryLayout()
  {
    $categories = \Smt\Masterweb\Models\ParameterCategoryLayout::orderBy('sort_order', 'asc')
      ->with('categoryItems.parameterPaketKlinik')
      ->get();

    // Kumpulkan semua ID paket yang sudah terpetakan di grid (semua kategori)
    $mappedPaketIds = \Smt\Masterweb\Models\ParameterCategoryItem::whereNull('deleted_at')
      ->whereNotNull('id_parameter_paket_klinik')
      ->pluck('id_parameter_paket_klinik')
      ->unique()
      ->values()
      ->all();

    // Hanya tampilkan paket yang BELUM terpetakan di mana pun
    $allPakets = ParameterPaketKlinik::orderBy('name_parameter_paket_klinik', 'asc')
      ->whereNull('deleted_at')
      ->whereNotIn('id_parameter_paket_klinik', $mappedPaketIds)
      ->get();

    return view('masterweb::module.admin.laboratorium.parameter-paket-klinik.category-layout', compact('categories', 'allPakets'));
  }

  // Update category layout
  public function updateCategoryLayout(Request $request)
  {
    try {
      DB::beginTransaction();

      // Update category order and properties
      if ($request->has('categories')) {
        foreach ($request->categories as $category) {
          $updateData = [
            'category_code' => $category['code'] ?? '',
            'category_name' => $category['name'] ?? '',
            'column_width' => $category['width'] ?? 4,
            'empty_column_position' => $category['empty_position'] ?? 'none',
            'grid_rows' => $category['grid_rows'] ?? 0,
            'grid_columns' => $category['grid_columns'] ?? 3,
            'sort_order' => $category['sort'] ?? 0,
          ];
          
          // Only update is_active if it's explicitly sent and not empty
          if (isset($category['is_active']) && $category['is_active'] !== '' && $category['is_active'] !== null) {
            $updateData['is_active'] = (int)$category['is_active'];
          }
          
          \Smt\Masterweb\Models\ParameterCategoryLayout::where('id_param_category_layout', $category['id'])
            ->update($updateData);
        }
      }

      DB::commit();
      return response()->json(['status' => true, 'pesan' => 'Layout kategori berhasil diupdate!'], 200);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }

  // Add new category
  public function addCategory(Request $request)
  {
    try {
      $lastSort = \Smt\Masterweb\Models\ParameterCategoryLayout::max('sort_order') ?? 0;

      $category = new \Smt\Masterweb\Models\ParameterCategoryLayout();
      $category->category_code = $request->category_code ?? 'NEW';
      $category->category_name = $request->category_name ?? 'KATEGORI BARU';
      $category->column_width = $request->column_width ?? 4;
      $category->sort_order = $lastSort + 1;
      $category->is_active = true;
      $category->save();

      return response()->json(['status' => true, 'pesan' => 'Kategori berhasil ditambahkan!', 'data' => $category], 200);
    } catch (\Exception $e) {
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }

  // Update category items
  public function updateCategoryItems(Request $request)
  {
    try {
      DB::beginTransaction();

      $categoryId = $request->category_id;
      $items = $request->items;

      // Delete existing items for this category
      \Smt\Masterweb\Models\ParameterCategoryItem::where('id_param_category_layout', $categoryId)->delete();

      // Insert new items
      foreach ($items as $index => $item) {
        $categoryItem = new \Smt\Masterweb\Models\ParameterCategoryItem();
        $categoryItem->id_param_category_layout = $categoryId;
        $categoryItem->id_parameter_paket_klinik = $item['paket_id'];
        $categoryItem->sort_order = $index + 1;
        $categoryItem->save();
      }

      DB::commit();
      return response()->json(['status' => true, 'pesan' => 'Item kategori berhasil diupdate!'], 200);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }

  // Update grid configuration (rows x columns)
  public function updateGridConfig(Request $request)
  {
    try {
      $categoryId = $request->category_id;
      $gridRows = $request->grid_rows ?? 0;
      $gridColumns = $request->grid_columns ?? 3;

      \Smt\Masterweb\Models\ParameterCategoryLayout::where('id_param_category_layout', $categoryId)
        ->update([
          'grid_rows' => $gridRows,
          'grid_columns' => $gridColumns,
        ]);

      return response()->json(['status' => true, 'pesan' => 'Grid berhasil diupdate!'], 200);
    } catch (\Exception $e) {
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }

  // Update item grid position
  public function updateItemGridPosition(Request $request)
  {
    try {
      DB::beginTransaction();

      $categoryId = $request->category_id;
      $items = $request->items; // [{ paket_id, row, column }]

      // Delete existing items for this category
      \Smt\Masterweb\Models\ParameterCategoryItem::where('id_param_category_layout', $categoryId)->delete();

      // Insert items with grid positions
      $sortOrder = 1;
      foreach ($items as $item) {
        if (isset($item['paket_id']) && $item['paket_id']) {
          $categoryItem = new \Smt\Masterweb\Models\ParameterCategoryItem();
          $categoryItem->id_param_category_layout = $categoryId;
          $categoryItem->id_parameter_paket_klinik = $item['paket_id'];
          $categoryItem->row_position = $item['row'] ?? null;
          $categoryItem->column_position = $item['column'] ?? null;
          $categoryItem->sort_order = $sortOrder++;
          $categoryItem->save();
        }
      }

      DB::commit();
      return response()->json(['status' => true, 'pesan' => 'Posisi parameter berhasil diupdate!'], 200);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 400);
    }
  }
}
