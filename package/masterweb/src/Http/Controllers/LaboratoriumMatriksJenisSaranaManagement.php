<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Models\MatriksJenisSarana;
use Smt\Masterweb\Models\SampleType;
use Yajra\DataTables\DataTables;

class LaboratoriumMatriksJenisSaranaManagement extends Controller
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
  public function index(Request $request)
  {
    if (request()->ajax()) {
      $datas = MatriksJenisSarana::orderBy('created_at', 'DESC')
        ->get();

      return DataTables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['name_sample_type_sarana']), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        ->addColumn('action', function ($data) {
          $editButton = '';
          $deleteButton = '';

          if (getAction('update')) {
            $editButton = '<a href="javascript:void(0)" class="dropdown-item btn-edit" data-id="' . $data->id_sample_type_sarana  . '" data-nama="' . $data->name_sample_type_sarana . '" title="Edit">Edit</a> ';
          }

          if (getAction('delete')) {
            $deleteButton = '<a class="dropdown-item btn-hapus" href="javascript:void(0)" data-id="' . $data->id_sample_type_sarana  . '" data-nama="' . $data->name_sample_type_sarana . '" title="Hapus">Hapus</a> ';
          }

          $button = '<div class="dropdown show m-1">
                              <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Aksi
                              </a>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                  ' . $editButton . '

                                  ' . $deleteButton . '
                              </div>
                          </div>';

          return $button;
        })
        ->addColumn('jumlah_jenis_sarana', function ($data) {
          return count($data->sampletype);
        })
        ->rawColumns(['action', 'jumlah_jenis_sarana'])
        ->addIndexColumn() //increment
        ->make(true);
    }

    return view('masterweb::module.admin.laboratorium.matriks-sample.list');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
  }

  public function rules($request)
  {
    $rule = [
      'name_sample_type_sarana' => 'required|max:100'
    ];

    $pesan = [
      'name_sample_type_sarana.required' => 'Nama matriks jenis sarana tidak boleh kosong!'
    ];

    return Validator::make($request, $rule, $pesan);
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
        $check = MatriksJenisSarana::where('name_sample_type_sarana', $request->name_sample_type_sarana)
          ->first();

        if ($check != null) {
          return response()->json(['status' => false, 'pesan' => "Nama matriks jenis sarana pernah diinputkan sebelumnya!"], 200);
        } else {
          $post = new MatriksJenisSarana();
          $post->name_sample_type_sarana = $request->name_sample_type_sarana;

          $simpan = $post->save();

          DB::commit();

          if ($simpan == true) {
            return response()->json([
              'status' => true,
              'pesan' => "Data matriks sample berhasil disimpan!"
            ], 200);
          } else {
            return response()->json([
              'status' => false,
              'pesan' => "Data matriks sample tidak berhasil disimpan!"
            ], 200);
          }
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
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
    $item = MatriksJenisSarana::findOrFail($id);

    $data = [
      'id_sample_type_sarana' => $item->id_sample_type_sarana,
      'name_sample_type_sarana' => $item->name_sample_type_sarana,
    ];

    return response()->json($data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $post = MatriksJenisSarana::find($request->id_sample_type_sarana);

        if ($post->name_sample_type_sarana != $request->name_sample_type_sarana) {
          $check = MatriksJenisSarana::where('name_sample_type_sarana', $request->name_sample_type_sarana)
            ->first();

          if ($check == null) {
            $post->name_sample_type_sarana = $request->name_sample_type_sarana;
          } else {
            return response()->json(['status' => false, 'pesan' => 'Nama matriks jenis sarana ' . $request->name_sample_type_sarana . ' telah tersedia. Silahkan gunakan nama lainnya.']);
          }
        }


        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json([
            'status' => true,
            'pesan' => "Data matriks sample berhasil disimpan!"
          ], 200);
        } else {
          return response()->json([
            'status' => false,
            'pesan' => "Data matriks sample tidak berhasil disimpan!"
          ], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
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
    $hapus_sampletype = SampleType::where('sample_type_sarana_id', $id)->delete();
    $hapus = MatriksJenisSarana::where('id_sample_type_sarana', $id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data matriks sample berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data matriks sample tidak berhasil dihapus!"], 400);
    }
  }

  public function getMatriksJenisSaranaSelect2(Request $request)
  {
    $search = $request->search;

    if ($search == '') {
      $data = MatriksJenisSarana::orderby('name_sample_type_sarana', 'asc')
        ->select('id_sample_type_sarana', 'name_sample_type_sarana')
        ->limit(10)
        ->get();
    } else {
      $data = MatriksJenisSarana::orderby('name_sample_type_sarana', 'asc')
        ->select('id_sample_type_sarana', 'name_sample_type_sarana')
        ->where('name_sample_type_sarana', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_sample_type_sarana,
        "text" => $item->name_sample_type_sarana
      );
    }

    return response()->json($response);
  }
}