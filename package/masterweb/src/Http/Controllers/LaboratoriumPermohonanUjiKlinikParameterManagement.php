<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\PermohonanUjiKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Yajra\Datatables\Datatables;

class LaboratoriumPermohonanUjiKlinikParameterManagement extends Controller
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
  public function index(Request $request, $id_permohonan_uji_klinik)
  {

    if (request()->ajax()) {
      $datas = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->orderBy('parameter_paket_extra', 'desc')
        ->get();


      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['parameter_jenis_klinik']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['type_permohonan_uji_paket_klinik']), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        ->addColumn('parameter_jenis_klinik', function ($data) {

          if ($data->type_permohonan_uji_paket_klinik == "P") {
            return $data->parameterpaketklinik->name_parameter_paket_klinik;
          } else {

            return $data->parameterjenisklinik->name_parameter_jenis_klinik;
          }
        })
        ->addColumn('type_permohonan_uji_paket_klinik', function ($data) {
          if ($data->type_permohonan_uji_paket_klinik == "P") {
            $status = "Paket";
          } else {
            $status = "Custom";
          }

          return $status;
        })
        ->addColumn('total_harga', function ($data) {
          return $data->harga_permohonan_uji_paket_klinik != null ? rupiah($data->harga_permohonan_uji_paket_klinik) : 'Rp. 0';
        })
        ->addColumn('action', function ($data) use ($id_permohonan_uji_klinik) {
          $data_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

          $editButton = '';
          $deleteButton = '';

          $detailButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.show-permohonan-uji-klinik-parameter', [$id_permohonan_uji_klinik, $data->id_permohonan_uji_paket_klinik]) . '" title="Detail">Detail</a> ';


          if ($data_permohonan_uji_klinik->status_permohonan_uji_klinik != "SELESAI") {
            $editButton = '<a href="' . route('elits-permohonan-uji-klinik.edit-permohonan-uji-klinik-parameter', [$id_permohonan_uji_klinik, $data->id_permohonan_uji_paket_klinik]) . '" class="dropdown-item" title="Edit">Edit</a> ';

            $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji_paket_klinik . '" data-nama="' . $data->parameterjenisklinik->name_parameter_jenis_klinik . '" title="Hapus">Hapus</a> ';
          }

          $button = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $detailButton . '

                                ' . $editButton . '

                                ' . $deleteButton . '
                            </div>
                        </div>';

          return $button;
        })
        ->rawColumns(['parameter_jenis_klinik', 'type_permohonan_uji_paket_klinik', 'total_harga', 'action'])
        ->addIndexColumn() //increment
        ->make(true);
    }

    $item = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.pagination', [
      'id' => $id_permohonan_uji_klinik,
      'item' => $item,
      'tgl_register' => $tgl_register,
    ]);
  }

  public function rules($request)
  {
    $rule = [
      'type_permohonan_uji_paket_klinik' => 'required',
    ];

    $pesan = [
      'type_permohonan_uji_paket_klinik.required' => 'Tipe paket tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create($id_permohonan_uji_klinik)
  {
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.add', [
      'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik,
    ]);
  }

  public function store(Request $request, $id_permohonan_uji_klinik)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

        // Untuk paket atau custom dimasukkanke permohonanujipaketklinik
        $post = new PermohonanUjiPaketKlinik();
        $post->permohonan_uji_klinik = $id_permohonan_uji_klinik;
        $post->type_permohonan_uji_paket_klinik = $request->type_permohonan_uji_paket_klinik;

        if ($request->type_permohonan_uji_paket_klinik == "P") {
          $post->parameter_paket_klinik = $request->parameter_paket_klinik;
        } else {
          $post->parameter_jenis_klinik = $request->parameter_jenis_klinik;
          $post->parameter_paket_klinik = null;
        }

        $post->harga_permohonan_uji_paket_klinik = $request->harga_permohonan_uji_paket_klinik;
        $simpan = $post->save();

        // Akan untuk paket atau custom akan dimasukkan di permohonan uji parameter klinik
        // Cek dulu paket atau custom
        // Akan untuk paket atau custom akan dimasukkan di permohonan uji parameter klinik
        // Cek dulu paket atau custom
        if ($request->type_permohonan_uji_paket_klinik == "P") {
          // get data paket untuk mendapatkan jenis parameter untuk di mapping dengan parameter satuan
          $item_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $request->parameter_paket_klinik)
            ->first();

          #looping jenis parameter paket
          foreach ($item_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
            #looping satuan parameter dari paket
            $no_sorting_parameter_satuan = 1;
            foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

              // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
              $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
              $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                  ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();

                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                    ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }

              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
              $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
              $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
              $post_parameter->parameter_paket_klinik = $request->parameter_paket_klinik;
              $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
              $post_parameter->sorting_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->sorting ?? $no_sorting_parameter_satuan;
              $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

              $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
              $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;

              $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              $simpan_post_parameter = $post_parameter->save();

              // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->get();

              if (count($data_parameter_subsatuan) > 0) {
                foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                  $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                  $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                  $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                  // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                  if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                      ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                      ->first();

                    $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                    $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                  }

                  $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                }
              }

              $no_sorting_parameter_satuan++;
            }
          }
        }

        if ($request->type_permohonan_uji_paket_klinik == "C") {
          //  check apakah ada element itu
          if ($request->parameter_custom_klinik) {
            // check dengan count dulu
            if (count($request->parameter_custom_klinik) > 0) {
              // for loop karena multiple
              $item_paket = PermohonanUjiPaketKlinik::find($post->id_permohonan_uji_paket_klinik);
              $no_param = 0;
              foreach ($request->parameter_custom_klinik as $key_parameter_satauan => $value_parameter_satauan) {
                #1 attempt
                /* $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];

                // get harga parameter satuan
                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $simpan_parameter = $post_parameter->save(); */

                #2 attempt
                // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->where('is_khusus_baku_mutu', '0')
                  ->first();

                // cek dulu apakah ada data dengan data khusus sperti general atau specific
                if ($check_parameter_by_baku_mutu) {
                  $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                } else {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->where(function ($query) use ($pasien_umur) {
                      $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                        ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                    })
                    ->first();
                  if (!isset($item_parameter_by_baku_mutu)) {
                    $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                      ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                      ->where('is_khusus_baku_mutu', '1')
                      ->where('gender_baku_mutu', $pasien_gender)
                      ->first();
                  }
                }

                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];
                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $post_parameter->jenis_parameter_klinik_id = $request->parameter_jenis_klinik;


                $post_parameter->sort_jenis_klinik = count($item_permohonan_uji_klinik->permohonanujipaketklinik) + 1;
                $post_parameter->sorting_parameter_satuan = $no_param;


                if (isset($item_parameter_by_baku_mutu->unit_id)) {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                } else {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                }

                $simpan_parameter = $post_parameter->save();

                // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
                $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->get();

                if (count($data_parameter_subsatuan) > 0) {
                  foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                    $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                    $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                    $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                    // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                    if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                      $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                        ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                        ->first();

                      if ($item_parameter_subsatuan_by_baku_mutu) {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                      } else {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = null;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = null;
                      }
                    }

                    $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                  }
                }
              }
            }
          }
        }

        // cari semua paket dan tambahkan semua harga untuk diupdate ke permohonan_uji_klinik
        $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)->get();

        $harga_total = 0;

        foreach ($data_paket as $key => $value) {
          $harga_total += $value->harga_permohonan_uji_paket_klinik;
        }

        $post_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
        $post_klinik->total_harga_permohonan_uji_klinik = $harga_total;
        $post_klinik->status_permohonan_uji_klinik = 'ANALIS';

        $post_klinik->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter berhasil disimpan!", 'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter tidak berhasil disimpan!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!'], 200);
      }
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    $item = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
    $tgl_register = Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');

    // mapping data parameter paket dulu
    $item_parameter_paket = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)->first();

    $data_parameter_satuan = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)
      ->groupBy('parameter_paket_jenis_klinik')
      ->get();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.read', [
      'item' => $item,
      'tgl_register' => $tgl_register,
      'item_parameter_paket' => $item_parameter_paket,
      'data_parameter_satuan' => $data_parameter_satuan,
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    $item_paket = PermohonanUjiPaketKlinik::find($id_permohonan_uji_paket_klinik);
    $data_parameter_paket = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
      ->where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik)
      ->get();
    $data_parameter_satuan = ParameterSatuanKlinik::where('parameter_jenis_klinik', $item_paket->parameter_jenis_klinik)->get();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik.parameter.edit', [
      'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik,
      'id_permohonan_uji_paket_klinik' => $id_permohonan_uji_paket_klinik,
      'item_paket' => $item_paket,
      'data_parameter_paket' => $data_parameter_paket,
      'data_parameter_satuan' => $data_parameter_satuan,
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $item_permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);

        // Untuk paket atau custom dimasukkanke permohonanujipaketklinik
        $post = PermohonanUjiPaketKlinik::find($id_permohonan_uji_paket_klinik);
        $post->permohonan_uji_klinik = $id_permohonan_uji_klinik;
        $post->type_permohonan_uji_paket_klinik = $request->type_permohonan_uji_paket_klinik;

        if ($request->type_permohonan_uji_paket_klinik == "P") {
          $post->parameter_paket_klinik = $request->parameter_paket_klinik;
        } else {
          $post->parameter_jenis_klinik = $request->parameter_jenis_klinik;
          $post->parameter_paket_klinik = null;
        }

        $post->harga_permohonan_uji_paket_klinik = $request->harga_permohonan_uji_paket_klinik;
        $simpan = $post->save();

        // delete data yang di paket parameter
        $data_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $id_permohonan_uji_paket_klinik);
        $get_data_parameter_klinik = $data_parameter_klinik->get();

        foreach ($get_data_parameter_klinik as $key => $value) {
          // delete data yang di paket sub parameter
          PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value->id_permohonan_uji_parameter_klinik)->delete();
        }

        $data_parameter_klinik->delete();

        // Akan untuk paket atau custom akan dimasukkan di permohonan uji parameter klinik
        // Cek dulu paket atau custom
        if ($request->type_permohonan_uji_paket_klinik == "P") {
          // get data paket untuk mendapatkan jenis parameter untuk di mapping dengan parameter satuan
          $item_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $request->parameter_paket_klinik)
            ->first();

          #looping jenis parameter paket
          foreach ($item_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
            #looping satuan parameter dari paket
            $no_sorting_parameter_satuan = 1;
            foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

              // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
              $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
              $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->first();

              // cek dulu apakah ada data dengan data khusus sperti general atau specific
              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                  ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender)
                  ->where(function ($query) use ($pasien_umur) {
                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                      ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                  })
                  ->first();

                if (!isset($item_parameter_by_baku_mutu)) {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                    ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->first();
                }
              }

              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
              $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
              $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
              $post_parameter->parameter_paket_klinik = $request->parameter_paket_klinik;
              $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
              $post_parameter->sorting_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->sorting ?? $no_sorting_parameter_satuan;
              $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

              $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              $simpan_post_parameter = $post_parameter->save();

              // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->get();

              if (count($data_parameter_subsatuan) > 0) {
                foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                  $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                  $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                  $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                  // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                  if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                      ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                      ->first();

                    $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                    $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                  }

                  $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                }
              }

              $no_sorting_parameter_satuan++;
            }
          }
        }

        if ($request->type_permohonan_uji_paket_klinik == "C") {
          //  check apakah ada element itu
          if ($request->parameter_custom_klinik) {
            // check dengan count dulu
            if (count($request->parameter_custom_klinik) > 0) {
              // for loop karena multiple
              $item_paket = PermohonanUjiPaketKlinik::find($post->id_permohonan_uji_paket_klinik);
              foreach ($request->parameter_custom_klinik as $key_parameter_satauan => $value_parameter_satauan) {
                #1 attempt
                /* $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];

                // get harga parameter satuan
                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $simpan_parameter = $post_parameter->save(); */

                #2 attempt
                // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                  ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->where('is_khusus_baku_mutu', '0')
                  ->first();

                // cek dulu apakah ada data dengan data khusus sperti general atau specific
                if ($check_parameter_by_baku_mutu) {
                  $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                } else {
                  $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                    ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                    ->where('is_khusus_baku_mutu', '1')
                    ->where('gender_baku_mutu', $pasien_gender)
                    ->where(function ($query) use ($pasien_umur) {
                      $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                        ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                    })
                    ->first();
                  if (!isset($item_parameter_by_baku_mutu)) {
                    $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik)
                      ->where('parameter_satuan_klinik_id', $request->parameter_custom_klinik[$key_parameter_satauan])
                      ->where('is_khusus_baku_mutu', '1')
                      ->where('gender_baku_mutu', $pasien_gender)
                      ->first();
                  }
                }

                $item_parameter_satuan = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])->first();

                $post_parameter = new PermohonanUjiParameterKlinik();
                $post_parameter->permohonan_uji_klinik = $id_permohonan_uji_klinik;
                $post_parameter->permohonan_uji_paket_klinik = $post->id_permohonan_uji_paket_klinik;
                $post_parameter->parameter_satuan_klinik = $request->parameter_custom_klinik[$key_parameter_satauan];
                $post_parameter->harga_permohonan_uji_parameter_klinik = $item_parameter_satuan->harga_satuan_parameter_satuan_klinik;
                $post_parameter->jenis_parameter_klinik_id = $request->parameter_jenis_klinik;

                if (isset($item_parameter_by_baku_mutu->unit_id)) {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                } else {
                  $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                  $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                }

                $simpan_parameter = $post_parameter->save();

                // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
                $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $request->parameter_custom_klinik[$key_parameter_satauan])
                  ->get();

                if (count($data_parameter_subsatuan) > 0) {
                  foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                    $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                    $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                    $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                    // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                    if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                      $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                        ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                        ->first();

                      if ($item_parameter_subsatuan_by_baku_mutu) {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                      } else {
                        $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = null;
                        $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = null;
                      }
                    }

                    $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                  }
                }
              }
            }
          }
        }

        // cari semua paket dan tambahkan semua harga untuk diupdate ke permohonan_uji_klinik
        $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)->get();

        $harga_total = 0;

        foreach ($data_paket as $key => $value) {
          $harga_total += $value->harga_permohonan_uji_paket_klinik;
        }

        $post_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
        $post_klinik->total_harga_permohonan_uji_klinik = $harga_total;
        $post_klinik->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data parameter berhasil diubah!", 'id_permohonan_uji_klinik' => $id_permohonan_uji_klinik], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data parameter tidak berhasil diubah!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => 'System gagal melakukan perubahan!'], 200);
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
    $detail_paket_klinik = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id)->first();

    $data_parameter_klinik = PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $id);
    $get_data_parameter_klinik = $data_parameter_klinik->get();

    foreach ($get_data_parameter_klinik as $key => $value) {
      // delete data yang di paket sub parameter
      PermohonanUjiSubParameterKlinik::where('permohonan_uji_parameter_klinik_id', $value->id_permohonan_uji_parameter_klinik)->delete();
    }

    $data_parameter_klinik->delete();

    if ($detail_paket_klinik) {
      $hapus = PermohonanUjiPaketKlinik::where('id_permohonan_uji_paket_klinik', $id)->delete();
    }

    $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $detail_paket_klinik->permohonan_uji_klinik)->get();

    $harga_total = 0;

    foreach ($data_paket as $key => $value) {
      $harga_total += $value->harga_permohonan_uji_paket_klinik;
    }

    $post_klinik = PermohonanUjiKlinik::find($detail_paket_klinik->permohonan_uji_klinik);

    $post_klinik->total_harga_permohonan_uji_klinik = $harga_total;
    if (count($data_paket) == 0) {
      $post_klinik->status_permohonan_uji_klinik = 'PARAMETER';
    }

    $post_klinik->save();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter tidak berhasil dihapus!"], 200);
    }
  }

  public function getDataParameterCustom(Request $request)
  {
    $parameter_type = $request->parameter_type;
    $type_paket = $request->type_paket;
    $response = array();

    $data = ParameterSatuanKlinik::where('parameter_jenis_klinik', $parameter_type)->get();

    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_satuan_klinik,
        "text" => $item->name_parameter_satuan_klinik,
        "harga" => $item->harga_satuan_parameter_satuan_klinik,
      );
    }

    return response()->json($response);
  }

  public function getDataParameterPaket(Request $request)
  {
    $search = $request->search;
    $parameter_type = $request->parameter_type;
    $response = array();

    if (!isset($search)) {
      $data = ParameterPaketKlinik::orderby('name_parameter_paket_klinik', 'asc')
        ->limit(10)
        ->get();
    } else {
      $data = ParameterPaketKlinik::where('name_parameter_paket_klinik', 'like', '%' . $search . '%')
        ->orderby('name_parameter_paket_klinik', 'asc')
        ->limit(10)
        ->get();
    }

    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_paket_klinik,
        "text" => $item->name_parameter_paket_klinik,
        "harga" => $item->harga_parameter_paket_klinik,
      );
    }

    return response()->json($response);
  }

  public function getCountHargaTotal(Request $request)
  {
    $search = $request->search;
    $id_permohonan_uji_klinik = $request->id_permohonan_uji_klinik;

    if ($search) {
      $count = PermohonanUjiPaketKlinik::leftjoin('ms_parameter_jenis_klinik', function ($join) {
        $join->on('ms_parameter_jenis_klinik.id_parameter_jenis_klinik', '=', 'tb_permohonan_uji_paket_klinik.parameter_jenis_klinik')
          ->whereNull('ms_parameter_jenis_klinik.deleted_at')
          ->whereNull('tb_permohonan_uji_paket_klinik.deleted_at');
      })
        ->select(
          'ms_parameter_jenis_klinik.id_parameter_jenis_klinik as id_parameter_jenis_klinik',
          'ms_parameter_jenis_klinik.name_parameter_jenis_klinik as name_parameter_jenis_klinik',
          'tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik as harga_permohonan_uji_paket_klinik',
        )
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->where(function ($query) use ($search) {
          $query->Where('ms_parameter_jenis_klinik.name_parameter_jenis_klinik', 'LIKE', '%' . $search . '%');
        })
        ->sum('tb_permohonan_uji_paket_klinik.harga_permohonan_uji_paket_klinik');
    } else {
      $count = PermohonanUjiPaketKlinik::select(
        'harga_permohonan_uji_paket_klinik',
        // DB::raw('SUM(tb_permohonan_uji_klinik.harga_permohonan_uji_paket_klinik)')
      )
        ->where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->orderBy('created_at', 'asc')
        ->sum('harga_permohonan_uji_paket_klinik');
    }

    return response()->json($count);
  }
}