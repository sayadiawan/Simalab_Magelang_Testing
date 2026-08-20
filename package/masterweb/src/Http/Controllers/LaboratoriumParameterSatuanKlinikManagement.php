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

use Illuminate\Validation\Rule;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\BakuMutu;

class LaboratoriumParameterSatuanKlinikManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function rules($request)
  {
    $rule = [
      'parameter_jenis_klinik' => 'required',
      'name_parameter_satuan_klinik' => 'required|max:256',
      // 'metode_parameter_satuan_klinik' => 'required|max:256',
      'jenis_pemeriksaan_parameter_satuan_klinik' => 'required',
      'harga_satuan_parameter_satuan_klinik' => 'required|numeric'
    ];

    $pesan = [
      'parameter_jenis_klinik.required' => 'Parameter jenis klinik tidak boleh kosong!',
      'name_parameter_satuan_klinik.required' => 'Nama parameter satuan klinik tidak boleh kosong!',
      // 'metode_parameter_satuan_klinik.required' => 'Metode parameter satuan klinik tidak boleh kosong!',
      'jenis_pemeriksaan_parameter_satuan_klinik.required' => 'Jenis parameter pemeriksaan klinik tidak boleh kosong!',
      'harga_satuan_parameter_satuan_klinik.required' => 'Harga parameter satuan klinik tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Simpan jenis sampel / metode / LOINC non-haji + (jika parameter haji) varian haji.
   */
  private function applyJenisSampelFields(ParameterSatuanKlinik $post, Request $request): void
  {
    $post->jenis_sampel = $request->jenis_sampel;
    $post->is_haji = $request->has('is_haji') ? 1 : 0;

    if ((int) $post->is_haji === 1) {
      $haji = $request->jenis_sampel_haji;
      $post->jenis_sampel_haji = !empty($haji) ? $haji : null;

      $metodeHaji = $this->resolveMetodeParameterSatuanKlinik(
        $request,
        'metode_parameter_satuan_klinik_haji_list',
        'metode_parameter_satuan_klinik_haji'
      );
      $post->metode_parameter_satuan_klinik_haji = ($metodeHaji === '' || $metodeHaji === '-') ? null : $metodeHaji;

      $loincHaji = trim((string) $request->input('loinc_parameter_satuan_klinik_haji', ''));
      $post->loinc_parameter_satuan_klinik_haji = $loincHaji !== '' ? $loincHaji : null;
    }
  }

  /**
   * Gabungkan list metode dari form menjadi string CSV.
   */
  private function resolveMetodeParameterSatuanKlinik(
    Request $request,
    string $listKey = 'metode_parameter_satuan_klinik_list',
    string $singleKey = 'metode_parameter_satuan_klinik'
  ): string
  {
    if ($request->has($listKey)) {
      $list = $request->input($listKey, []);
      if (!is_array($list)) {
        $list = [$list];
      }

      $items = array_values(array_filter(array_map('trim', $list), function ($value) {
        return $value !== '' && $value !== '-';
      }));

      return count($items) > 0 ? implode(',', $items) : '-';
    }

    $value = trim((string) $request->input($singleKey, '-'));

    return $value !== '' ? $value : '-';
  }

  /**
   * Set requires_nama_jenis; pastikan kolom ada dulu agar checklist tidak hilang setelah simpan.
   */
  private function applyRequiresNamaJenis(ParameterSatuanKlinik $post, Request $request): void
  {
    ParameterSatuanKlinik::ensureRequiresNamaJenisColumn();

    if (!ParameterSatuanKlinik::hasRequiresNamaJenisColumn()) {
      return;
    }

    $post->requires_nama_jenis = ($post->is_option && $request->has('requires_nama_jenis')) ? 1 : 0;
  }

  /**
   * Samakan flag requires_nama_jenis pada record permohonan yang memakai parameter ini.
   */
  private function syncRequiresNamaJenisToPermohonan(ParameterSatuanKlinik $post): void
  {
    if (!PermohonanUjiParameterKlinik::ensureRequiresNamaJenisColumn()) {
      return;
    }

    PermohonanUjiParameterKlinik::where('parameter_satuan_klinik', $post->id_parameter_satuan_klinik)
      ->whereNull('deleted_at')
      ->update([
        'requires_nama_jenis' => (int) ($post->requires_nama_jenis ? 1 : 0),
        'updated_at' => now(),
      ]);
  }

  /**
   * Remove simple-translate extension HTML from content
   *
   * @param  string  $content
   * @return string
   */
  private function cleanSimpleTranslate($content)
  {
    if (empty($content)) {
      return $content;
    }

    // Remove simple-translate divs (handles nested divs properly)
    // Pattern 1: Match div with id="simple-translate" and all nested content
    $content = preg_replace('/<div[^>]*id=["\']simple-translate["\'][^>]*>[\s\S]*?<\/div>/is', '', $content);
    
    // Pattern 2: Match div with class containing "simple-translate" and all nested content
    $content = preg_replace('/<div[^>]*class=["\'][^"\']*simple-translate[^"\']*["\'][^>]*>[\s\S]*?<\/div>/is', '', $content);
    
    // Pattern 3: Match any div containing "simple-translate" in attributes (fallback)
    $content = preg_replace('/<div[^>]*simple-translate[^>]*>[\s\S]*?<\/div>/is', '', $content);

    // Clean up any remaining empty lines or whitespace
    $content = preg_replace('/\n\s*\n/', "\n", $content);

    return trim($content);
  }

  /**
   * Update keterangan_permohonan_uji_parameter_klinik for records created this year
   *
   * @param  string  $parameterSatuanKlinikId
   * @param  string  $ketDefault
   * @return void
   */
  private function updatePermohonanUjiParameterKlinik($parameterSatuanKlinikId, $ketDefault)
  {
    // Get current year
    $currentYear = Carbon::now()->year;
    
    // Update only records created in current year
    PermohonanUjiParameterKlinik::where('parameter_satuan_klinik', $parameterSatuanKlinikId)
      ->whereYear('created_at', $currentYear)
      ->update([
        'keterangan_permohonan_uji_parameter_klinik' => $ketDefault
      ]);
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
            $data = ParameterSatuanKlinik::orderBy('created_at', 'desc')
                ->whereHas('parameterjenisklinik', function ($query) {
                    return $query->whereNull('deleted_at');
                })
                ->get();

            return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.list', compact('data'));
        } else {
            return abort(404);
        } */

    // #2
    $data = ParameterSatuanKlinik::orderBy('created_at', 'desc')
      ->whereHas('parameterjenisklinik', function ($query) {
        return $query->whereNull('deleted_at');
      })
      ->get();

    return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.list', compact('data'));
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

    return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.add');
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
      // dd($request->name_parameter_satuan_klinik);
      // check apakah ada kesamaan data parameter jenis dan satuan yang akan diinput lagi
      $check = ParameterSatuanKlinik::where('name_parameter_satuan_klinik', $request->post('name_parameter_satuan_klinik'))
        ->where('parameter_jenis_klinik', $request->post('parameter_jenis_klinik'))
        ->first();

      if (isset($check)) {
        if ($check->name_parameter_satuan_klinik == $request->post('name_parameter_satuan_klinik')) {
          return response()->json(['status' => false, 'pesan' => "Nama parameter satuan pada jenis tersebut sudah pernah dibuat, silahkan coba dengan nama yang berbeda atau jenisnya!"], 200);
        } else {
          DB::beginTransaction();

          try {
          $post = new ParameterSatuanKlinik();
          $post->parameter_jenis_klinik = $request->post('parameter_jenis_klinik');
          $post->name_parameter_satuan_klinik = $request->name_parameter_satuan_klinik;
          $post->metode_parameter_satuan_klinik = $this->resolveMetodeParameterSatuanKlinik($request);
          $post->loinc_parameter_satuan_klinik = $request->loinc_parameter_satuan_klinik;
          $post->ket_default_parameter_satuan_klinik = rubahNilaikeHtml($this->cleanSimpleTranslate($request->ket_default_parameter_satuan_klinik));
          $post->jenis_pemeriksaan_parameter_satuan_klinik = $request->post('jenis_pemeriksaan_parameter_satuan_klinik');
          $this->applyJenisSampelFields($post, $request);
          $post->is_sub_parameter_satuan_klinik = $request->post('is_sub_parameter_satuan_klinik');
          $post->harga_satuan_parameter_satuan_klinik = $request->post('harga_satuan_parameter_satuan_klinik');
          // Jika urutan tidak dikirim dari form, tetapkan otomatis ke max+1 per jenis
          $requestedSort = $request->post('sort_parameter_satuan_klinik');
          if ($requestedSort === null || $requestedSort === '') {
            $maxSort = ParameterSatuanKlinik::where('parameter_jenis_klinik', $request->post('parameter_jenis_klinik'))
              ->max('sort_parameter_satuan_klinik');
            $post->sort_parameter_satuan_klinik = ($maxSort ?? 0) + 1;
          } else {
            $post->sort_parameter_satuan_klinik = (int)$requestedSort;
          }
          $post->is_option = $request->has('is_option') ? 1 : 0;
          $this->applyRequiresNamaJenis($post, $request);
          $post->option = $request->post('option') ?? null;
          $post->number_format = $request->post('number_format', 'en');
          $post->is_haji = $request->has('is_haji') ? 1 : 0;

          $simpan = $post->save();

            if (($request->is_sub_parameter_satuan_klinik == 1) && isset($request->name_parameter_sub_satuan_klinik)) {
              $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

              for ($i = 1; $i <= $count_sub_parameter; $i++) {
                $post_sub = new ParameterSubSatuanKlinik();
                $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
                $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

                $simpan_sub = $post_sub->save();
              }
            }

            DB::commit();

            if ($simpan == true) {
              return response()->json([
                'status' => true,
                'pesan' => 'Data parameter satuan berhasil disimpan!',
                'id_parameter_satuan_klinik' => $post->id_parameter_satuan_klinik,
                'name_parameter_satuan_klinik' => $post->name_parameter_satuan_klinik,
              ], 200);
            } else {
              return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil disimpan!"], 200);
            }
          } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['status' => false, 'pesan' => $e], 200);
          }
        }
      } else {
        DB::beginTransaction();

        try {
          $post = new ParameterSatuanKlinik();
          $post->parameter_jenis_klinik = $request->post('parameter_jenis_klinik');
          $post->name_parameter_satuan_klinik = $request->name_parameter_satuan_klinik;
          $post->metode_parameter_satuan_klinik = $this->resolveMetodeParameterSatuanKlinik($request);
          $post->loinc_parameter_satuan_klinik = $request->loinc_parameter_satuan_klinik;
          $post->ket_default_parameter_satuan_klinik = rubahNilaikeHtml($this->cleanSimpleTranslate($request->ket_default_parameter_satuan_klinik));
          $post->jenis_pemeriksaan_parameter_satuan_klinik = $request->post('jenis_pemeriksaan_parameter_satuan_klinik');
          $this->applyJenisSampelFields($post, $request);
          $post->is_sub_parameter_satuan_klinik = $request->post('is_sub_parameter_satuan_klinik');
          $post->harga_satuan_parameter_satuan_klinik = $request->post('harga_satuan_parameter_satuan_klinik');
          // Jika urutan tidak dikirim dari form, tetapkan otomatis ke max+1 per jenis
          $requestedSort2 = $request->post('sort_parameter_satuan_klinik');
          if ($requestedSort2 === null || $requestedSort2 === '') {
            $maxSort2 = ParameterSatuanKlinik::where('parameter_jenis_klinik', $request->post('parameter_jenis_klinik'))
              ->max('sort_parameter_satuan_klinik');
            $post->sort_parameter_satuan_klinik = ($maxSort2 ?? 0) + 1;
          } else {
            $post->sort_parameter_satuan_klinik = (int)$requestedSort2;
          }
          $post->is_option = $request->has('is_option') ? 1 : 0;
          $this->applyRequiresNamaJenis($post, $request);
          $post->option = $request->post('option') ?? null;
          $post->number_format = $request->post('number_format', 'en');
          $post->is_haji = $request->has('is_haji') ? 1 : 0;

          $simpan = $post->save();

          if (($request->is_sub_parameter_satuan_klinik == 1) && isset($request->name_parameter_sub_satuan_klinik)) {
            $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

            for ($i = 1; $i <= $count_sub_parameter; $i++) {
              $post_sub = new ParameterSubSatuanKlinik();
              $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
              $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

              $simpan_sub = $post_sub->save();
            }
          }

          DB::commit();

          if ($simpan == true) {
            return response()->json(['status' => true, 'pesan' => "Data parameter satuan berhasil disimpan!"], 200);
          } else {
            return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil disimpan!"], 200);
          }
        } catch (\Exception $e) {
          DB::rollback();

          return response()->json(['status' => false, 'pesan' => $e], 200);
        }
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
    $user = Auth()->user();
    $level = $user->getlevel->level;
    $item = ParameterSatuanKlinik::find($id);

    $data_subitem = [];

    if ($item->is_sub_parameter_satuan_klinik == '1') {
      $data_subitem = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)
        ->whereNull('deleted_at')
        ->get();
    }

    return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.show', compact('item', 'data_subitem'));
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
    $item = ParameterSatuanKlinik::find($id);

    $data_subitem = [];

    if ($item->is_sub_parameter_satuan_klinik == '1') {
      $data_subitem = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)
        ->whereNull('deleted_at')
        ->get();
    }

    $existing_sorts = ParameterSatuanKlinik::with('parameterjenisklinik')->orderBy('sort_parameter_satuan_klinik')
    // ->pluck('name_parameter_satuan_klinik', 'sort_parameter_satuan_klinik');
    ->get();
    // dd($existing_sorts);

    $usageSamples = $this->getSamplesUsingParameterSatuanKlinik($id);

    return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.edit', compact(
      'item',
      'data_subitem',
      'existing_sorts',
      'usageSamples'
    ));
  }

  /**
   * Daftar permohonan/sampel klinik aktif yang memakai parameter satuan ini.
   *
   * @return \Illuminate\Support\Collection<int, object{id:string,nomor_sampel:string,nomor_lab:?string,tanggal:?string,is_haji:int}>
   */
  private function getSamplesUsingParameterSatuanKlinik(string $parameterSatuanKlinikId)
  {
    $rows = DB::table('tb_permohonan_uji_parameter_klinik as pup')
      ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'pup.permohonan_uji_klinik')
      ->where('pup.parameter_satuan_klinik', $parameterSatuanKlinikId)
      ->whereNull('pup.deleted_at')
      ->whereNull('p.deleted_at')
      ->select([
        'p.id_permohonan_uji_klinik',
        'p.noregister_permohonan_uji_klinik',
        'p.nourut_permohonan_uji_klinik',
        'p.nomor_spesimen_manual',
        'p.is_nomor_spesimen_manual',
        'p.nomer_lab',
        'p.nomor_lab_manual',
        'p.is_nomor_lab_manual',
        'p.tglregister_permohonan_uji_klinik',
        'p.created_at',
        'p.is_haji',
      ])
      ->orderByDesc('p.created_at')
      ->get()
      ->unique('id_permohonan_uji_klinik')
      ->values();

    return $rows->map(function ($row) {
      $permohonan = new \Smt\Masterweb\Models\PermohonanUjiKlinik2();
      $permohonan->forceFill((array) $row);

      $tahun = Carbon::parse($row->tglregister_permohonan_uji_klinik ?: ($row->created_at ?: now()))->year;
      $urutSpesimen = $permohonan->resolveSpesimenUrut();
      $nomorSampel = $urutSpesimen !== ''
        ? ('03/' . $urutSpesimen . '/' . $tahun)
        : (trim((string) ($row->noregister_permohonan_uji_klinik ?? '')) ?: '-');

      $tanggal = $row->tglregister_permohonan_uji_klinik ?: $row->created_at;

      return (object) [
        'id' => $row->id_permohonan_uji_klinik,
        'nomor_sampel' => $nomorSampel,
        'nomor_lab' => $permohonan->getNomorLab(),
        'tanggal' => $tanggal ? Carbon::parse($tanggal)->format('d/m/Y') : null,
        'is_haji' => (int) ($row->is_haji ?? 0),
      ];
    });
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
      $post = ParameterSatuanKlinik::find($id);

      // pastikan antara data yang sudah disimpan sama dengan data yang diupdate
      // jika sama maka tidak ada validasi
      // jika berbeda maka akan ada validasi lanjutan untuk mengecek apakah data jenis dan satuan berbeda atau sama
      if ($post->parameter_jenis_klinik == $request->post('parameter_jenis_klinik') && $post->name_parameter_satuan_klinik == $request->post('name_parameter_satuan_klinik')) {
        DB::beginTransaction();

        try {
          $post->parameter_jenis_klinik = $request->post('parameter_jenis_klinik');
          $post->name_parameter_satuan_klinik = $request->post('name_parameter_satuan_klinik');
          $post->metode_parameter_satuan_klinik = $this->resolveMetodeParameterSatuanKlinik($request);
          $post->ket_default_parameter_satuan_klinik = rubahNilaikeHtml($this->cleanSimpleTranslate($request->ket_default_parameter_satuan_klinik));
          $post->loinc_parameter_satuan_klinik = $request->loinc_parameter_satuan_klinik;
          $post->jenis_pemeriksaan_parameter_satuan_klinik = $request->post('jenis_pemeriksaan_parameter_satuan_klinik');
          $this->applyJenisSampelFields($post, $request);
          $post->is_sub_parameter_satuan_klinik = $request->post('is_sub_parameter_satuan_klinik');
          $post->harga_satuan_parameter_satuan_klinik = $request->post('harga_satuan_parameter_satuan_klinik');

          // Edit tidak mengubah urutan. Biarkan $post->sort_parameter_satuan_klinik apa adanya
          $post->is_option = $request->has('is_option') ? 1 : 0;
          $this->applyRequiresNamaJenis($post, $request);
          $post->option = $request->post('option') ?? null;
          $post->number_format = $request->post('number_format', 'en');
          $post->is_haji = $request->has('is_haji') ? 1 : 0;

          $simpan = $post->save();

          // Update keterangan_permohonan_uji_parameter_klinik untuk record tahun ini
          if ($simpan) {
            $this->updatePermohonanUjiParameterKlinik($post->id_parameter_satuan_klinik, $post->ket_default_parameter_satuan_klinik);
            $this->syncRequiresNamaJenisToPermohonan($post);
          }

          if ($request->is_sub_parameter_satuan_klinik == 1  && isset($request->name_parameter_sub_satuan_klinik)) {
            $check_subitem = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->get();

            if (count($check_subitem) > 0) {
              ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->delete();

              $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

              for ($i = 1; $i <= $count_sub_parameter; $i++) {
                $post_sub = new ParameterSubSatuanKlinik();
                $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
                $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

                $post_sub->save();
              }
            } else {
              $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

              for ($i = 1; $i <= $count_sub_parameter; $i++) {
                $post_sub = new ParameterSubSatuanKlinik();
                $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
                $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

                $post_sub->save();
              }
            }
          } else {
            $check_subitem = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->get();

            if (count($check_subitem) > 0) {
              ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->delete();
            }
          }

          DB::commit();

          if ($simpan == true) {
            return response()->json(['status' => true, 'pesan' => "Data parameter satuan berhasil diubah!"], 200);
          } else {
            return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil diubah!"], 200);
          }
        } catch (\Exception $e) {
          DB::rollback();

          return response()->json(['status' => false, 'pesan' => $e], 200);
        }
      } else {
        $check = ParameterSatuanKlinik::where('name_parameter_satuan_klinik', '=', $request->post('name_parameter_satuan_klinik'))
          ->where('parameter_jenis_klinik', $request->post('parameter_jenis_klinik'))
          ->first();

        if (isset($check)) {
          if ($check->name_parameter_satuan_klinik == $request->post('name_parameter_satuan_klinik')) {
            return response()->json(['status' => false, 'pesan' => "Nama parameter satuan pada jenis tersebut sudah pernah dibuat, silahkan coba dengan nama yang berbeda atau jenisnya!"], 200);
          } else {
            DB::beginTransaction();

            try {
              $post->parameter_jenis_klinik = $request->post('parameter_jenis_klinik');
              $post->name_parameter_satuan_klinik = $request->post('name_parameter_satuan_klinik');
              $post->metode_parameter_satuan_klinik = $this->resolveMetodeParameterSatuanKlinik($request);
              $post->ket_default_parameter_satuan_klinik = rubahNilaikeHtml($this->cleanSimpleTranslate($request->ket_default_parameter_satuan_klinik));
              $post->loinc_parameter_satuan_klinik = $request->loinc_parameter_satuan_klinik;
              $post->jenis_pemeriksaan_parameter_satuan_klinik = $request->post('jenis_pemeriksaan_parameter_satuan_klinik');
              $this->applyJenisSampelFields($post, $request);
              $post->is_sub_parameter_satuan_klinik = $request->post('is_sub_parameter_satuan_klinik');
              $post->harga_satuan_parameter_satuan_klinik = $request->post('harga_satuan_parameter_satuan_klinik');
              $post->is_option = $request->has('is_option') ? 1 : 0;
              $this->applyRequiresNamaJenis($post, $request);
              $post->option = $request->post('option') ?? null;
              $post->number_format = $request->post('number_format', 'en');
              $post->is_haji = $request->has('is_haji') ? 1 : 0;

              // Simpan nomor urut yang lama
              $old_sort = $post->sort_parameter_satuan_klinik;

              // Ambil nomor urut setelah yang dipilih di dropdown atau input angka langsung
              $after_sort = $request->post('after_sort_parameter_satuan_klinik');
              $requested_sort = $request->post('sort_parameter_satuan_klinik');

              // Tentukan nomor urut yang baru: prioritas ke input angka langsung, lalu after_sort, lalu tetap
              if ($requested_sort !== null && $requested_sort !== '') {
                $new_sort = max(1, (int)$requested_sort);
              } else if ($after_sort != null){
                $new_sort = $after_sort ? ($after_sort + 1) : 1;
              } else {
                $new_sort = $old_sort;
              }

              // Cek jika item yang sedang diedit tidak memiliki nomor urut sebelumnya
              if (is_null($old_sort) || $old_sort == 0) {
                  // Jika item tidak memiliki urutan sebelumnya, update semua item
                  // dengan nomor urut lebih besar dari atau sama dengan new_sort
                  ParameterSatuanKlinik::where('sort_parameter_satuan_klinik', '>=', $new_sort)
                      ->increment('sort_parameter_satuan_klinik');
              } else {
                  // Jika nomor urut diubah
                  if ($old_sort != $new_sort) {
                      if ($new_sort > $old_sort) {
                          // Jika pindah ke bawah, kurangi urutan item antara old_sort dan new_sort
                          ParameterSatuanKlinik::whereBetween('sort_parameter_satuan_klinik', [$old_sort + 1, $new_sort])
                              ->decrement('sort_parameter_satuan_klinik');
                      } else {
                          // Jika pindah ke atas, tambahkan urutan item antara new_sort dan old_sort
                          ParameterSatuanKlinik::whereBetween('sort_parameter_satuan_klinik', [$new_sort, $old_sort - 1])
                              ->increment('sort_parameter_satuan_klinik');
                      }
                  }
              }

              // Update urutan item yang sedang diedit
              $post->sort_parameter_satuan_klinik = $new_sort;

              $simpan = $post->save();

              // Update keterangan_permohonan_uji_parameter_klinik untuk record tahun ini
              if ($simpan) {
                $this->updatePermohonanUjiParameterKlinik($post->id_parameter_satuan_klinik, $post->ket_default_parameter_satuan_klinik);
                $this->syncRequiresNamaJenisToPermohonan($post);
              }

              if (($request->is_sub_parameter_satuan_klinik == 1) && isset($request->name_parameter_sub_satuan_klinik)) {
                $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

                for ($i = 1; $i <= $count_sub_parameter; $i++) {
                  $post_sub = new ParameterSubSatuanKlinik();
                  $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
                  $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

                  $simpan_sub = $post_sub->save();
                }
              }

              DB::commit();

              if ($simpan == true) {
                return response()->json(['status' => true, 'pesan' => "Data parameter satuan berhasil disimpan!"], 200);
              } else {
                return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil disimpan!"], 200);
              }
            } catch (\Exception $e) {
              DB::rollback();

              return response()->json(['status' => false, 'pesan' => $e], 200);
            }
          }
        } else {
          DB::beginTransaction();

          try {
            $post->parameter_jenis_klinik = $request->post('parameter_jenis_klinik');
            $post->name_parameter_satuan_klinik = $request->post('name_parameter_satuan_klinik');
            $post->metode_parameter_satuan_klinik = $this->resolveMetodeParameterSatuanKlinik($request);
            $post->ket_default_parameter_satuan_klinik = rubahNilaikeHtml($this->cleanSimpleTranslate($request->ket_default_parameter_satuan_klinik));
            $post->loinc_parameter_satuan_klinik = $request->loinc_parameter_satuan_klinik;
            $post->jenis_pemeriksaan_parameter_satuan_klinik = $request->post('jenis_pemeriksaan_parameter_satuan_klinik');
            $this->applyJenisSampelFields($post, $request);
            $post->is_sub_parameter_satuan_klinik = $request->post('is_sub_parameter_satuan_klinik');
            $post->harga_satuan_parameter_satuan_klinik = $request->post('harga_satuan_parameter_satuan_klinik');
            $post->is_option = $request->has('is_option') ? 1 : 0;
            $this->applyRequiresNamaJenis($post, $request);
            $post->option = $request->post('option') ?? null;
            $post->number_format = $request->post('number_format', 'en');
            $post->is_haji = $request->has('is_haji') ? 1 : 0;

            // Edit tidak mengubah urutan. Biarkan nilai sort tetap.

            $simpan = $post->save();

            // Update keterangan_permohonan_uji_parameter_klinik untuk record tahun ini
            if ($simpan) {
              $this->updatePermohonanUjiParameterKlinik($post->id_parameter_satuan_klinik, $post->ket_default_parameter_satuan_klinik);
              $this->syncRequiresNamaJenisToPermohonan($post);
            }

            if (($request->is_sub_parameter_satuan_klinik == 1) && isset($request->name_parameter_sub_satuan_klinik)) {
              $count_sub_parameter = count($request->name_parameter_sub_satuan_klinik);

              for ($i = 1; $i <= $count_sub_parameter; $i++) {
                $post_sub = new ParameterSubSatuanKlinik();
                $post_sub->parameter_satuan_klinik = $post->id_parameter_satuan_klinik;
                $post_sub->name_parameter_sub_satuan_klinik = $request->name_parameter_sub_satuan_klinik[$i];

                $simpan_sub = $post_sub->save();
              }
            }

            DB::commit();

            if ($simpan == true) {
              return response()->json([
                'status' => true,
                'pesan' => 'Data parameter satuan berhasil disimpan!',
                'id_parameter_satuan_klinik' => $post->id_parameter_satuan_klinik,
                'name_parameter_satuan_klinik' => $post->name_parameter_satuan_klinik,
              ], 200);
            } else {
              return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil disimpan!"], 200);
            }
          } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['status' => false, 'pesan' => $e], 200);
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
    $hapus = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $id)->delete();

    $check_subitem = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->get();

    if (count($check_subitem) > 0) {
      ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $id)->delete();
    }

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter satuan berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil dihapus!"], 200);
    }
  }

  public function getParameterSatuanKlinik(Request $request)
  {
    $search = $request->search;
    $is_haji = $request->get('is_haji', null); // Filter berdasarkan is_haji jika dikirim
    $exclude_existing_haji = $request->get('exclude_existing_haji', false); // Exclude parameter yang sudah ada baku mutu haji

    if (isset($request->param)) {
      /* $data = ParameterSatuanKlinik::orderby('name_parameter_satuan_klinik', 'asc')->select('id_parameter_satuan_klinik', 'name_parameter_satuan_klinik')->where('parameter_jenis_klinik', $request->param)->limit(20)->get(); */

      $query = ParameterSatuanKlinik::orderby('name_parameter_satuan_klinik', 'asc')
        ->select('id_parameter_satuan_klinik', 'name_parameter_satuan_klinik')
        ->where('parameter_jenis_klinik', $request->param);
      
      // Filter berdasarkan is_haji jika dikirim
      if ($is_haji !== null) {
        $query->where('is_haji', (int)$is_haji);
      }
      
      // Exclude parameter yang sudah memiliki baku mutu haji
      if ($exclude_existing_haji && $is_haji == 1) {
        $labId = $request->get('lab_id', null);
        $existingHajiQuery = BakuMutu::where('is_haji', 1)
          ->where('is_sub_parameter_satuan_baku_mutu', 0);
        
        // Filter berdasarkan lab_id jika dikirim
        if ($labId) {
          $existingHajiQuery->where('lab_id', $labId);
        }
        
        $existingHajiParams = $existingHajiQuery->distinct()
          ->pluck('parameter_satuan_klinik_id')
          ->toArray();
        
        if (!empty($existingHajiParams)) {
          $query->whereNotIn('id_parameter_satuan_klinik', $existingHajiParams);
        }
      }

      if ($search == '' || $search == null) {
        $data = $query->limit(20)->get();
      } else {
        $data = $query->where('name_parameter_satuan_klinik', 'like', '%' . $search . '%')
          ->limit(20)
          ->get();
      }
    } else {
      $query = ParameterSatuanKlinik::orderby('name_parameter_satuan_klinik', 'asc')
        ->select('id_parameter_satuan_klinik', 'name_parameter_satuan_klinik');
      
      // Filter berdasarkan is_haji jika dikirim
      if ($is_haji !== null) {
        $query->where('is_haji', (int)$is_haji);
      }
      
      // Exclude parameter yang sudah memiliki baku mutu haji
      if ($exclude_existing_haji && $is_haji == 1) {
        $labId = $request->get('lab_id', null);
        $existingHajiQuery = BakuMutu::where('is_haji', 1)
          ->where('is_sub_parameter_satuan_baku_mutu', 0);
        
        // Filter berdasarkan lab_id jika dikirim
        if ($labId) {
          $existingHajiQuery->where('lab_id', $labId);
        }
        
        $existingHajiParams = $existingHajiQuery->distinct()
          ->pluck('parameter_satuan_klinik_id')
          ->toArray();
        
        if (!empty($existingHajiParams)) {
          $query->whereNotIn('id_parameter_satuan_klinik', $existingHajiParams);
        }
      }
      
      if ($search == '' || $search == null) {
        $data = $query->limit(50)->get(); // Increase limit untuk haji parameters
      } else {
        $data = $query->where('name_parameter_satuan_klinik', 'like', '%' . $search . '%')
          ->limit(50)
          ->get();
      }
    }


    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_parameter_satuan_klinik,
        "text" => $item->name_parameter_satuan_klinik
      );
    }

    return response()->json($response);
  }

  // Reorder sort_parameter_satuan_klinik via drag & drop
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
        // Normalize and update explicit sorts
        foreach ($orders as $row) {
          if (!isset($row['id'])) { continue; }
          $id = $row['id'];
          $sort = isset($row['sort']) ? (int)$row['sort'] : null;
          if ($sort === null || $sort < 1) { continue; }
          ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $id)
            ->update(['sort_parameter_satuan_klinik' => $sort]);
        }
        // Optionally re-normalize to 1..N with current ascending sort to avoid duplicates
        $all = ParameterSatuanKlinik::orderBy('sort_parameter_satuan_klinik')->get(['id_parameter_satuan_klinik']);
        $i = 1;
        foreach ($all as $item) {
          ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $item->id_parameter_satuan_klinik)
            ->update(['sort_parameter_satuan_klinik' => $i++]);
        }
      } else {
        // Fallback: sequential ordering by ids
        $sort = 1;
        foreach ($ids as $id) {
          ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $id)
            ->update(['sort_parameter_satuan_klinik' => $sort++]);
        }
      }

      DB::commit();
      \Illuminate\Support\Facades\Cache::forget('puk2.data_parameter_satuan_klinik');
      $this->syncPermohonanSortingFromCanonicalMasterSort();
      return response()->json(['status' => true, 'pesan' => 'Urutan berhasil disimpan']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan urutan']);
    }
  }

  // Reorder page
  public function reorderPage(Request $request)
  {
    $data = ParameterSatuanKlinik::with('parameterjenisklinik')
      ->orderBy('sort_parameter_satuan_klinik')
      ->get();
    return view('masterweb::module.admin.laboratorium.parameter-satuan-klinik.reorder', compact('data'));
  }

  /**
   * Samakan kolom sorting permohonan dengan urutan kanonik master (min sort per nama).
   */
  private function syncPermohonanSortingFromCanonicalMasterSort(): void
  {
    if (!\Illuminate\Support\Facades\Schema::hasTable('tb_permohonan_uji_parameter_klinik')
      || !\Illuminate\Support\Facades\Schema::hasTable('ms_parameter_satuan_klinik')) {
      return;
    }

    $permohonanDeletedFilter = \Illuminate\Support\Facades\Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'deleted_at')
      ? 'AND p.deleted_at IS NULL'
      : '';
    $masterDeletedFilter = \Illuminate\Support\Facades\Schema::hasColumn('ms_parameter_satuan_klinik', 'deleted_at')
      ? 'AND m.deleted_at IS NULL'
      : '';

    $canonicalSortSql = "
      SELECT MIN(m2.sort_parameter_satuan_klinik) AS canon_sort
      FROM ms_parameter_satuan_klinik AS m2
      WHERE m2.sort_parameter_satuan_klinik IS NOT NULL
        " . (\Illuminate\Support\Facades\Schema::hasColumn('ms_parameter_satuan_klinik', 'deleted_at') ? 'AND m2.deleted_at IS NULL' : '') . "
        AND (
              LOWER(TRIM(m2.name_parameter_satuan_klinik)) = LOWER(TRIM(m.name_parameter_satuan_klinik))
              OR (
                  LOWER(TRIM(m.name_parameter_satuan_klinik)) IN ('kreatinin', 'creatinine')
                  AND LOWER(TRIM(m2.name_parameter_satuan_klinik)) IN ('kreatinin', 'creatinine')
              )
        )
    ";

    if (\Illuminate\Support\Facades\Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_permohonan_uji_parameter_klinik')) {
      DB::statement("
        UPDATE tb_permohonan_uji_parameter_klinik AS p
        INNER JOIN ms_parameter_satuan_klinik AS m
          ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
        SET p.sorting_permohonan_uji_parameter_klinik = ({$canonicalSortSql})
        WHERE p.parameter_satuan_klinik IS NOT NULL
          AND p.parameter_satuan_klinik <> ''
          AND m.sort_parameter_satuan_klinik IS NOT NULL
          {$permohonanDeletedFilter}
          {$masterDeletedFilter}
      ");
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_parameter_satuan')) {
      DB::statement("
        UPDATE tb_permohonan_uji_parameter_klinik AS p
        INNER JOIN ms_parameter_satuan_klinik AS m
          ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
        SET p.sorting_parameter_satuan = ({$canonicalSortSql})
        WHERE p.parameter_satuan_klinik IS NOT NULL
          AND p.parameter_satuan_klinik <> ''
          AND m.sort_parameter_satuan_klinik IS NOT NULL
          {$permohonanDeletedFilter}
          {$masterDeletedFilter}
      ");
    }
  }

  // Get ParameterSatuanKlinik detail untuk baku mutu klinik
  public function getParameterSatuanKlinikDetail(Request $request)
  {
    $id = $request->post('id');
    
    if (!$id) {
      return response()->json(['status' => false, 'pesan' => 'ID tidak boleh kosong'], 200);
    }

    $item = ParameterSatuanKlinik::find($id);
    
    if (!$item) {
      return response()->json(['status' => false, 'pesan' => 'Parameter satuan klinik tidak ditemukan'], 200);
    }

    $options = [];
    if ($item->is_option == 1 && !empty($item->option)) {
      $options = array_map('trim', explode(',', $item->option));
      $options = array_filter($options);
    }

    return response()->json([
      'status' => true,
      'data' => [
        'is_option' => $item->is_option ?? 0,
        'requires_nama_jenis' => $item->requires_nama_jenis ?? 0,
        'option' => $item->option ?? '',
        'options' => $options,
        'is_haji' => $item->is_haji ?? 0,
        'parameter_jenis_klinik' => $item->parameter_jenis_klinik ?? null // id_parameter_jenis_klinik dari tabel
      ]
    ], 200);
  }
}