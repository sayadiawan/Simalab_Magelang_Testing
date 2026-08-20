<?php

namespace Smt\Masterweb\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\Wilayah;
use Yajra\DataTables\DataTables;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Helpers\SatuSehatHelper;

class AdmPasienController extends Controller
{
  protected $satuSehatHelper;
  
  public function __construct(SatuSehatHelper $satuSehatHelper)
  {
    $this->middleware('auth');
    $this->satuSehatHelper = $satuSehatHelper;
  }

  private function formatNamaPasien(?string $nama): string
  {
    return mb_strtoupper(trim((string) $nama), 'UTF-8');
  }
  
  /**
   * Get patient data from Satu Sehat
   */
  private function getDataPasienSatuSehat($nik, $name, $dob)
  {
    if (empty($nik) || empty($name) || empty($dob)) {
      return null;
    }
    
    try {
      $params = [
        "identifier" => 'https://fhir.kemkes.go.id/id/nik|'.$nik,
        "name" => $name,
        "birthdate" => $dob
      ];

      $response = $this->satuSehatHelper->get('Patient', $params)['body'];

      $entries = $response['entry'] ?? [];
      $result = [];

      foreach ($entries as $entry) {
        $resource = $entry['resource'] ?? [];
        $result[] = [
          'id' => $resource['id'] ?? null,
        ];
      }
      return $result[0]["id"] ?? null;
    } catch (\Exception $e) {
      Log::error('Error fetching Satu Sehat patient data: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $perPage = (int) $request->get('per_page', 15);
    $allowedPerPage = [10, 15, 25, 50, 100];

    if (!in_array($perPage, $allowedPerPage, true)) {
      $perPage = 15;
    }

    return view('masterweb::module.admin.pasien.list', compact('perPage', 'allowedPerPage'));
  }

  /**
   * Muat data pasien per halaman untuk DataTables (server-side, tanpa get semua).
   */
  protected function loadDataTablePasien(int $limitVal, int $start, string $search): array
  {
    $totalDataRecord = Pasien::query()->count();

    $filteredQuery = Pasien::query();
    if ($search !== '') {
      $like = '%' . $search . '%';
      $filteredQuery->where(function ($q) use ($like) {
        $q->where('nik_pasien', 'like', $like)
          ->orWhere('nama_pasien', 'like', $like)
          ->orWhere('no_rekammedis_pasien', 'like', $like)
          ->orWhere('phone_pasien', 'like', $like);
      });
    }

    $totalFilteredRecord = (clone $filteredQuery)->count();

    $datas = $filteredQuery->latest();
    if ($limitVal > 0) {
      $datas = $datas->offset($start)->limit($limitVal);
    }
    $datas = $datas->get();

    $no = $start + 1;
    foreach ($datas as $i => $data) {
      $datas[$i]->nomer = $no;
      $no++;
    }

    return [
      'totalDataRecord' => $totalDataRecord,
      'totalFilteredRecord' => $totalFilteredRecord,
      'datas' => $datas,
    ];
  }

  public function dataPasienDatatables(Request $request)
  {
    $limitVal = (int) $request->input('length', 15);
    if ($limitVal < 1) {
      $limitVal = 15;
    }
    if ($limitVal > 100) {
      $limitVal = 100;
    }

    $start = (int) $request->input('start', 0);
    $search = $request->input('search', '');
    if (is_array($search)) {
      $search = $search['value'] ?? '';
    }
    $search = trim((string) $search);

    $result = $this->loadDataTablePasien($limitVal, $start, $search);
    $datas = $result['datas'];

    return Datatables::of($datas)
      ->addColumn('action', function ($data) {
        $urlShow = '';
        $urlSunting = '';
        $urlHapus = '';

        if (getAction('read')) {
          $urlShow = '<a class="dropdown-item" href="' . route('elits-pasien.show', $data->id_pasien) . '">Detail</a>';
        }

        if (getAction('update')) {
          $urlSunting = '<a class="dropdown-item" href="' . route('elits-pasien.edit', $data->id_pasien) . '">Sunting</a>';
        }

        if (getAction('delete')) {
          $urlHapus = '<a class="dropdown-item btn-hapus" href="javascript:void(0)" data-id="' . $data->id_pasien . '" data-nama="' . $this->formatNamaPasien($data->nama_pasien) . '" title="Hapus">Hapus</a> ';
        }

        $buttonAksi = '
          <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              Aksi
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
              ' . $urlShow . '
              ' . $urlSunting . '
              ' . $urlHapus . '
            </div>
          </div>';

        return $buttonAksi;
      })
      ->editColumn('nama_pasien', function ($data) {
        return $this->formatNamaPasien($data->nama_pasien);
      })
      ->addColumn('nomer', function ($data) {
        return $data->nomer ?? '-';
      })
      ->rawColumns(['action'])
      ->setFilteredRecords($result['totalFilteredRecord'])
      ->setTotalRecords($result['totalDataRecord'])
      ->skipPaging()
      ->make(true);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('masterweb::module.admin.pasien.add');
  }

  public function rules($request)
  {
    $rule = [
      'nama_pasien' => 'required|string|min:3|max:255',
      'tgllahir_pasien' => 'required',
      'phone_pasien' => 'required',
    ];

    $pesan = [
      'nama_pasien.required' => 'Nama pasien tidak boleh kosong!',
      'tgllahir_pasien.required' => 'Tanggal lahir pasien tidak boleh kosong!',
      'phone_pasien.required' => 'Nomor telepon tidak boleh kosong!',
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
      // check jika sudah ada parameter jenis dan stuan di baku mutu
      // Skip validation if NIK is empty or "-"
      if ($request->post('nik_pasien') && $request->post('nik_pasien') != '-') {
        $check = Pasien::where('nik_pasien', $request->post('nik_pasien'))
          ->first();

        if ($check) {
          return response()->json(['status' => false, 'pesan' => "NIK pasien sudah pernah diinput, silahkan input NIK yang berbeda!"], 200);
        }
      }
      
      DB::beginTransaction();

      try {
        $rmInput = $request->post('no_rekammedis_pasien');
        if ($rmInput !== null && trim((string) $rmInput) !== '') {
          $set_count = (int) preg_replace('/\D/', '', (string) $rmInput);
          if ($set_count < 1) {
            $set_count = Pasien::nextNoRekamMedis();
          }
        } else {
          $set_count = Pasien::nextNoRekamMedis();
        }

        $post = new Pasien();
        $post->nik_pasien = $request->post('nik_pasien');
        $post->nourut_pasien = $set_count;
        $post->no_rekammedis_pasien = $set_count;
        // $post->no_rekammedis_pasien = $request->no_rekammedis_pasien;
        $post->nama_pasien = $this->formatNamaPasien($request->post('nama_pasien'));
        $post->gender_pasien = $request->post('gender_pasien');
        $post->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
        $post->tmpt_lahir = trim((string) $request->post('tmpt_lahir', '')) ?: null;
        $post->pekerjaan = trim((string) $request->post('pekerjaan', '')) ?: null;

        $post->alamat_pasien = $request->post('alamat_pasien');
        $post->phone_pasien = $request->post('phone_pasien');
        $post->divisi_instansi_pasien = $request->post('divisi_instansi_pasien');

        // Save wilayah_id - prioritas: Desa > Kecamatan > Kabupaten > Provinsi
        if ($request->has('desa_pasien') && !empty($request->post('desa_pasien'))) {
          $post->wilayah_id = $request->post('desa_pasien');
        } elseif ($request->has('kecamatan_pasien') && !empty($request->post('kecamatan_pasien'))) {
          $post->wilayah_id = $request->post('kecamatan_pasien');
        } elseif ($request->has('kabupaten_pasien') && !empty($request->post('kabupaten_pasien'))) {
          $post->wilayah_id = $request->post('kabupaten_pasien');
        } elseif ($request->has('provinsi_pasien') && !empty($request->post('provinsi_pasien'))) {
          $post->wilayah_id = $request->post('provinsi_pasien');
        }

        // Get Satu Sehat ID if NIK is provided
        if ($request->post('nik_pasien') && $request->post('nik_pasien') != '-') {
          $date = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
          $idSatuSehat = $this->getDataPasienSatuSehat($request->post('nik_pasien'), $post->nama_pasien, $date);
          if ($idSatuSehat) {
            $post->id_pasien_satu_sehat = $idSatuSehat;
          }
        }

        $simpan = $post->save();

        DB::commit();

        if ($simpan == true) {
          return response()->json(['status' => true, 'pesan' => "Data pasien berhasil disimpan!", 'id_pasien' => $post->id_pasien], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil disimpan!"], 200);
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
  public function show($id)
  {
    $item = Pasien::findOrFail($id);

    return view('masterweb::module.admin.pasien.show', compact('item'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $item = Pasien::with('wilayah')->findOrFail($id);
    
    // Get wilayah parents if exists
    $wilayahParents = null;
    if ($item->wilayah_id && $item->wilayah) {
      $wilayahParents = Wilayah::getParentIds($item->wilayah->wilayah_kode, $item->wilayah->tipe);
      
      // Include the selected item's ID itself based on tipe
      if ($item->wilayah->tipe == 'DESA') {
        $wilayahParents['desa_id'] = $item->wilayah_id;
      } elseif ($item->wilayah->tipe == 'KEC') {
        $wilayahParents['kecamatan_id'] = $item->wilayah_id;
      } elseif ($item->wilayah->tipe == 'KAB') {
        $wilayahParents['kabupaten_id'] = $item->wilayah_id;
      } elseif ($item->wilayah->tipe == 'PROV') {
        $wilayahParents['provinsi_id'] = $item->wilayah_id;
      }
    }

    return view('masterweb::module.admin.pasien.edit', compact('item', 'wilayahParents'));
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
      // dd($request);

      // check pasien
      $post = Pasien::find($id);

      // memastikan jika data yang ada di table sama dengan yang ada diinputan maka tidak ada validasi
      // validasi berjalan jika data yang ada di table tidak sama dengan yang ada diinputan
      if ($post->nik_pasien == $request->nik_pasien) {

        DB::beginTransaction();

        try {
          $post->nik_pasien = $request->post('nik_pasien');
          $post->nama_pasien = $this->formatNamaPasien($request->post('nama_pasien'));
          $post->no_rekammedis_pasien = $request->no_rekammedis_pasien;
          $post->gender_pasien = $request->post('gender_pasien');
          $post->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
          $post->tmpt_lahir = trim((string) $request->post('tmpt_lahir', '')) ?: null;
          $post->pekerjaan = trim((string) $request->post('pekerjaan', '')) ?: null;

          // dipindahkan ke table permohonan uji klinik
          /* $post->umurtahun_pasien = $request->post('umurtahun_pasien');
          $post->umurbulan_pasien = $request->post('umurbulan_pasien');
          $post->umurhari_pasien = $request->post('umurhari_pasien'); */

          $post->alamat_pasien = $request->post('alamat_pasien');
          $post->phone_pasien = $request->post('phone_pasien');
          $post->divisi_instansi_pasien = $request->post('divisi_instansi_pasien');

          // Update wilayah_id - prioritas: Desa > Kecamatan > Kabupaten > Provinsi
          if ($request->has('desa_pasien') && !empty($request->post('desa_pasien'))) {
            $post->wilayah_id = $request->post('desa_pasien');
          } elseif ($request->has('kecamatan_pasien') && !empty($request->post('kecamatan_pasien'))) {
            $post->wilayah_id = $request->post('kecamatan_pasien');
          } elseif ($request->has('kabupaten_pasien') && !empty($request->post('kabupaten_pasien'))) {
            $post->wilayah_id = $request->post('kabupaten_pasien');
          } elseif ($request->has('provinsi_pasien') && !empty($request->post('provinsi_pasien'))) {
            $post->wilayah_id = $request->post('provinsi_pasien');
          }

          // Update Satu Sehat ID if NIK is provided
          if ($request->post('nik_pasien') && $request->post('nik_pasien') != '-') {
            $date = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
            $idSatuSehat = $this->getDataPasienSatuSehat($request->post('nik_pasien'), $post->nama_pasien, $date);
            if ($idSatuSehat) {
              $post->id_pasien_satu_sehat = $idSatuSehat;
            }
          }

          $simpan = $post->save();

          DB::commit();

          if ($request->permohonanujiklinik_id != null) {
            $urlBack = route('elits-permohonan-uji-klinik.edit', $request->permohonanujiklinik_id);

            // get age
            $birthDate = Carbon::parse($post->tgllahir_pasien)->diff(Carbon::now())->format('%y-%m-%d');
            $birthDateExplode = explode('-', $birthDate);

            // update permohonan uji klinik
            $post_puk = PermohonanUjiKlinik2::findOrFail($request->permohonanujiklinik_id);
            $post_puk->umurtahun_pasien_permohonan_uji_klinik = $birthDateExplode[0];
            $post_puk->umurbulan_pasien_permohonan_uji_klinik = $birthDateExplode[1];
            $post_puk->umurhari_pasien_permohonan_uji_klinik = $birthDateExplode[2];

            $post_puk->save();
          } else {
            $urlBack = '/elits-pasien';
          }

          if ($simpan == true) {
            return response()->json(['status' => true, 'pesan' => "Data pasien berhasil diubah!", 'url_back' => $urlBack], 200);
          } else {
            return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil diubah!"], 200);
          }
        } catch (\Exception $e) {
          DB::rollback();

          return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!'], 200);
        }
      } else {
        if($request->nik_pasien!="-"){
          $check = Pasien::where('nik_pasien', $request->nik_pasien)
          ->first();
          if ($check) {
            return response()->json(['status' => false, 'pesan' => "NIK sudah pernah dibuat silahkan NIK yang berbeda!"], 200);
          } else {
            DB::beginTransaction();

            try {
              $post->nik_pasien = $request->post('nik_pasien');
              $post->nama_pasien = $this->formatNamaPasien($request->post('nama_pasien'));
              $post->gender_pasien = $request->post('gender_pasien');
              $post->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
              $post->tmpt_lahir = trim((string) $request->post('tmpt_lahir', '')) ?: null;
              $post->pekerjaan = trim((string) $request->post('pekerjaan', '')) ?: null;

              // dipindahkan ke table permohonan uji klinik
              /* $post->umurtahun_pasien = $request->post('umurtahun_pasien');
              $post->umurbulan_pasien = $request->post('umurbulan_pasien');
              $post->umurhari_pasien = $request->post('umurhari_pasien'); */

              $post->alamat_pasien = $request->post('alamat_pasien');
              $post->phone_pasien = $request->post('phone_pasien');

              // Update wilayah_id - prioritas: Desa > Kecamatan > Kabupaten > Provinsi
              if ($request->has('desa_pasien') && !empty($request->post('desa_pasien'))) {
                $post->wilayah_id = $request->post('desa_pasien');
              } elseif ($request->has('kecamatan_pasien') && !empty($request->post('kecamatan_pasien'))) {
                $post->wilayah_id = $request->post('kecamatan_pasien');
              } elseif ($request->has('kabupaten_pasien') && !empty($request->post('kabupaten_pasien'))) {
                $post->wilayah_id = $request->post('kabupaten_pasien');
              } elseif ($request->has('provinsi_pasien') && !empty($request->post('provinsi_pasien'))) {
                $post->wilayah_id = $request->post('provinsi_pasien');
              }

              $simpan = $post->save();

              DB::commit();

              if ($request->permohonanujiklinik_id != null) {
                $urlBack = route('elits-permohonan-uji-klinik.edit', $request->permohonanujiklinik_id);

                // get age
                $birthDate = Carbon::parse($post->tgllahir_pasien)->diff(Carbon::now())->format('%y-%m-%d');
                $birthDateExplode = explode('-', $birthDate);

                // update permohonan uji klinik
                $post_puk = PermohonanUjiKlinik2::findOrFail($request->permohonanujiklinik_id);
                $post_puk->umurtahun_pasien_permohonan_uji_klinik = $birthDateExplode[0];
                $post_puk->umurbulan_pasien_permohonan_uji_klinik = $birthDateExplode[1];
                $post_puk->umurhari_pasien_permohonan_uji_klinik = $birthDateExplode[2];

                $post_puk->save();
              } else {
                $urlBack = '/elits-pasien';
              }

              if ($simpan = true) {
                return response()->json(['status' => true, 'pesan' => "Data pasien berhasil diubah!", 'url_back' => $urlBack], 200);
              } else {
                return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil diubah!"], 200);
              }
            } catch (\Exception $e) {
              DB::rollback();

              return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!'], 200);
            }
          }
        }else{
          DB::beginTransaction();

            try {
              $post->nik_pasien = $request->post('nik_pasien');
              $post->nama_pasien = $this->formatNamaPasien($request->post('nama_pasien'));
              $post->gender_pasien = $request->post('gender_pasien');
              $post->tgllahir_pasien = Carbon::createFromFormat('d/m/Y', $request->tgllahir_pasien)->format('Y-m-d');
              $post->tmpt_lahir = trim((string) $request->post('tmpt_lahir', '')) ?: null;
              $post->pekerjaan = trim((string) $request->post('pekerjaan', '')) ?: null;

              // dipindahkan ke table permohonan uji klinik
              /* $post->umurtahun_pasien = $request->post('umurtahun_pasien');
              $post->umurbulan_pasien = $request->post('umurbulan_pasien');
              $post->umurhari_pasien = $request->post('umurhari_pasien'); */

              $post->alamat_pasien = $request->post('alamat_pasien');
              $post->phone_pasien = $request->post('phone_pasien');

              $simpan = $post->save();

              DB::commit();

              if ($request->permohonanujiklinik_id != null) {
                $urlBack = route('elits-permohonan-uji-klinik.edit', $request->permohonanujiklinik_id);

                // get age
                $birthDate = Carbon::parse($post->tgllahir_pasien)->diff(Carbon::now())->format('%y-%m-%d');
                $birthDateExplode = explode('-', $birthDate);

                // update permohonan uji klinik
                $post_puk = PermohonanUjiKlinik2::findOrFail($request->permohonanujiklinik_id);
                $post_puk->umurtahun_pasien_permohonan_uji_klinik = $birthDateExplode[0];
                $post_puk->umurbulan_pasien_permohonan_uji_klinik = $birthDateExplode[1];
                $post_puk->umurhari_pasien_permohonan_uji_klinik = $birthDateExplode[2];

                $post_puk->save();
              } else {
                $urlBack = '/elits-pasien';
              }

              if ($simpan = true) {
                return response()->json(['status' => true, 'pesan' => "Data pasien berhasil diubah!", 'url_back' => $urlBack], 200);
              } else {
                return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil diubah!"], 200);
              }
            } catch (\Exception $e) {
              DB::rollback();

              return response()->json(['status' => false, 'pesan' => 'System gagal melakukan penyimpanan!'], 200);
            }
        }
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
    try {
      DB::beginTransaction();
      // Hapus permohonan klinik-2 dulu (anak), lalu pasien — hindari FK / constraint.
      PermohonanUjiKlinik2::where('pasien_permohonan_uji_klinik', $id)->delete();
      $hapus = Pasien::where('id_pasien', $id)->delete();
      DB::commit();

      // delete() mengembalikan jumlah baris; 0 permohonan = sukses, bukan gagal.
      if ($hapus > 0) {
        return response()->json(['status' => true, 'pesan' => "Data pasien berhasil dihapus!"], 200);
      }

      return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil dihapus!"], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('AdmPasienController@destroy: ' . $e->getMessage(), ['id' => $id]);

      return response()->json(['status' => false, 'pesan' => "Data pasien tidak berhasil dihapus!"], 200);
    }
  }

  public function getPasienBySelect(Request $request)
  {
    try {
      $search = trim((string) $request->input('search', ''));

      $query = Pasien::query()
        ->select('id_pasien', 'nik_pasien', 'nama_pasien', 'tgllahir_pasien', 'no_rekammedis_pasien')
        ->orderBy('nama_pasien', 'asc');

      if ($search !== '') {
        $like = '%' . $search . '%';
        $query->where(function ($q) use ($like) {
          $q->where('nama_pasien', 'like', $like)
            ->orWhere('nik_pasien', 'like', $like)
            ->orWhere('tgllahir_pasien', 'like', $like)
            ->orWhere('no_rekammedis_pasien', 'like', $like);
        });
      }

      $data = $query->limit(10)->get();

      $response = [];
      foreach ($data as $item) {
        $tgllahir = $item->tgllahir_pasien ?? '-';
        $response[] = [
          'id' => $item->id_pasien,
          'text' => trim(($item->nama_pasien ?? '-') . ' - ' . ($item->nik_pasien ?? '-') . ' - ' . ($item->no_rekammedis_pasien ?? '-') . ' - (' . $tgllahir . ')'),
          'nama' => $item->nama_pasien ?? '-',
          'nik' => $item->nik_pasien ?? '-',
          'no_rekam' => $item->no_rekammedis_pasien ?? '-',
          'tgllahir' => $tgllahir,
        ];
      }

      return response()->json($response);
    } catch (\Exception $e) {
      Log::error('Error getPasienBySelect: ' . $e->getMessage(), [
        'search' => $request->input('search'),
      ]);

      return response()->json([], 200);
    }
  }

  public function getPasienByID(Request $request)
  {
    $item = Pasien::findOrFail($request->pasien_id);

    $response = [
      'id_pasien' => $item->id_pasien,
      'id_pasien_satu_sehat' => $item->id_pasien_satu_sehat,
      'nik_pasien' => $item->nik_pasien,
      'no_rekammedis_pasien' => str_pad((int)($item->no_rekammedis_pasien), 4, '0', STR_PAD_LEFT),
      'nama_pasien' => $item->nama_pasien,
      'gender_pasien' => $item->gender_pasien == "L" ? 'Laki-laki' : 'Perempuan',
      'tgllahir_pasien' => $item->tgllahir_pasien != null ? Carbon::createFromFormat('Y-m-d', $item->tgllahir_pasien)->isoFormat('dddd, D MMMM Y') : '',
      'tgllahir_pasien_normal' => $item->tgllahir_pasien != null ? Carbon::createFromFormat('Y-m-d', $item->tgllahir_pasien)->format('d/m/Y') : '',
      'alamat_pasien' => $item->alamat_pasien,
      'alamat_lengkap' => \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item),
      'wilayah_id' => $item->wilayah_id,
      'phone_pasien' => $item->phone_pasien,
      'tmpt_lahir' => $item->tmpt_lahir,
      'pekerjaan' => $item->pekerjaan,
      'divisi_instansi_pasien' => $item->divisi_instansi_pasien
    ];

    return response()->json($response);
  }

  /**
   * Get all provinces
   */
  public function getProvinsi()
  {
    $data = Wilayah::getProvinsi();
    return response()->json($data);
  }

  /**
   * Get kabupaten by province code
   */
  public function getKabupaten(Request $request)
  {
    $provinsiKode = $request->provinsi_kode;
    $data = Wilayah::getKabupatenByProvinsi($provinsiKode);
    return response()->json($data);
  }

  /**
   * Get kecamatan by kabupaten code
   */
  public function getKecamatan(Request $request)
  {
    $kabupatenKode = $request->kabupaten_kode;
    $data = Wilayah::getKecamatanByKabupaten($kabupatenKode);
    return response()->json($data);
  }

  /**
   * Get desa by kecamatan code
   */
  public function getDesa(Request $request)
  {
    $kecamatanKode = $request->kecamatan_kode;
    $data = Wilayah::getDesaByKecamatan($kecamatanKode);
    return response()->json($data);
  }

  /**
   * Search wilayah (autocomplete)
   */
  public function searchWilayah(Request $request)
  {
    $keyword = $request->keyword;
    $limit = $request->limit ?? 10;
    $types = ['DESA', 'KEC', 'KAB'];
    if ($request->filled('types')) {
      $types = array_values(array_filter(array_map('trim', explode(',', (string) $request->types))));
    }

    $results = Wilayah::searchWilayah($keyword, $limit, $types);
    return response()->json($results);
  }

  /**
   * Get wilayah detail with parent IDs
   */
  public function getWilayahDetail(Request $request)
  {
    $wilayahId = $request->wilayah_id;
    $wilayah = Wilayah::find($wilayahId);
    
    if (!$wilayah) {
      return response()->json(['error' => 'Wilayah not found'], 404);
    }
    
    $parents = Wilayah::getParentIds($wilayah->wilayah_kode, $wilayah->tipe);
    
    // IMPORTANT: Include the selected item's ID itself based on tipe
    // getParentIds only returns parents, not the item itself
    if ($wilayah->tipe == 'DESA') {
      $parents['desa_id'] = $wilayah->id_wilayah;
    } elseif ($wilayah->tipe == 'KEC') {
      $parents['kecamatan_id'] = $wilayah->id_wilayah;
    } elseif ($wilayah->tipe == 'KAB') {
      $parents['kabupaten_id'] = $wilayah->id_wilayah;
    } elseif ($wilayah->tipe == 'PROV') {
      $parents['provinsi_id'] = $wilayah->id_wilayah;
    }
    
    return response()->json([
      'wilayah' => $wilayah,
      'parents' => $parents
    ]);
  }
}