<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\PermohonanUjiKlinikProlanisGula;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Smt\Masterweb\Exports\FormatProlanisGulaExport;
use Smt\Masterweb\Imports\ProlanisGulaImport;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\NumberKlinik;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class LaboratoriumPermohonanUjiKlinikProlanisGulaManagement extends Controller
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
    $data = PermohonanUjiKlinikProlanisGula::orderBy('created_at', 'desc')->get();
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanisGula.list', [
      'data' => $data,
    ]);
  }

  public function rules($request)
  {
    $rule = [
      'nama_prolanis_gula' => 'required|string|max:255',
      'tgl_prolanis_gula' => 'required',
      'kuota_prolanis_gula' => 'required',
    ];

    $pesan = [
      'nama_prolanis_gula.required' => 'Nama Prolanis Gula wajib diisi.',
      'tgl_prolanis_gula.required' => 'Tanggal Prolanis Gula wajib diisi.',
      'kuota_prolanis_gula.required' => 'Kuota Prolanis Gula wajib diisi.',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function create()
  {
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanisGula.add');
  }

  public function store(Request $request)
  {
      $validator = $this->rules($request->all());

      if ($validator->fails()) {
        return response()->json(['status' => false, 'pesan' => $validator->errors()]);
      }

      // Simpan data ke dalam database
      $prolanis = new PermohonanUjiKlinikProlanisGula();
      $prolanis->nama_prolanis_gula = $request->input('nama_prolanis_gula');
      $prolanis->tgl_prolanis_gula = $request->input('tgl_prolanis_gula');
      $prolanis->kuota_prolanis_gula = $request->input('kuota_prolanis_gula');
      $prolanis->save();

      return response()->json(['status' => true, 'pesan' => "Data prolanis gula berhasil disimpan!"], 200);

  }

  public function downloadFormatProlanisGula($id)
  {
      DB::beginTransaction();

      try {
          $data = PermohonanUjiKlinikProlanisGula::where('id_permohonan_uji_klinik_prolanis_gula', $id)->first();
          $data_number = NumberKlinik::where('id_prolanis_gula', $id)->where(DB::raw( "YEAR(tb_number_klinik.created_at)"), date('Y'))->first();

          if($data_number){
            //ambil data last_number sebelumyanya / booking
            $count = $data_number->last_number - $data->kuota_prolanis_gula;

          }else{
            $count = NumberKlinik::max('last_number');

            // simpan last_number_klinik
            $number_klinik = new NumberKlinik();
            $number_klinik->new_number =  $count + 1;
            $number_klinik->last_number =  $count + $data->kuota_prolanis_gula;
            $number_klinik->id_prolanis_gula =  $id;
            $number_klinik->save();
          }

          DB::commit();

          return Excel::download(new FormatProlanisGulaExport($count, $data->nama_prolanis_gula, $data->tgl_prolanis_gula, $data->kuota_prolanis_gula), 'Format-Prolanis-Gula-' . $data->nama_prolanis_gula . '-' . $data->tgl_prolanis_gula . '.xlsx');
      } catch (\Exception $e) {
          DB::rollBack();
          return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
      }
  }

  public function importProlanisGula(Request $request, $id)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx'
    ]);

    $import = new ProlanisGulaImport();
    $data = Excel::toArray($import, $request->file('file'))[0];

    // dd($data);

    //cek momor lab apakah sudah pernah diinput
    foreach ($data as $row) {
        $data_permohonan_uji_klinik = PermohonanUjiKlinik2::where('noregister_permohonan_uji_klinik', $row[6])->first();
        if($data_permohonan_uji_klinik){
          return back()->with('error', 'Data dengan nomor lab format sudah pernah di unggah!');
        }
    }

    DB::beginTransaction();

    try {
        foreach ($data as $row) {
          // row[1] = nama,
          // row[2] = tgl_lahir,
          // row[3] = alamat,
          // row[4] = kelamin,
          // row[5] = no_lab,
          // row[6] = HbA1C,
          // row[7] = Gula Darah Puasa,
          // row[8] = Gula Darah 2 Jam PP,
          // row[9] = Rerata Gula Darah,
          // row[10] = CHOL,
          // row[11] = LDL,
          // row[12] = HDL,
          // row[13] = TG,
          // row[14] = UREUM,
          // row[15] = CREAT,
          // row[16] = MIKROALB,
          // row[18] = tgl_register,
          // row[18] = tgl_validasi

            $bulanIndonesia = [
              'Januari' => 'January',
              'Februari' => 'February',
              'Maret' => 'March',
              'April' => 'April',
              'Mei' => 'May',
              'Juni' => 'June',
              'Juli' => 'July',
              'Agustus' => 'August',
              'September' => 'September',
              'Oktober' => 'October',
              'November' => 'November',
              'Desember' => 'December',
            ];



            $tgl_lahir = $row[3];

            
            $tgl_registrasi = $row[18];

            // dd($tgl_registrasi);
            $tgl_validasi = $row[19];

            // Mengonversi tanggal
            if (is_numeric($tgl_lahir) && is_numeric($tgl_registrasi) && is_numeric($tgl_validasi)) {
                // Konversi dari format serial Excel ke DateTime
                $tgl_lahir = Date::excelToDateTimeObject($tgl_lahir)->format('Y-m-d');
                $tgl_registrasi = Date::excelToDateTimeObject($tgl_registrasi)->format('Y-m-d');
                $tgl_validasi = Date::excelToDateTimeObject($tgl_validasi)->format('Y-m-d');

              
            } else {
                try {
                    // Ubah bulan dari bahasa Indonesia ke bahasa Inggris
                    $tgl_lahir = $this->ubahBulanKeInggris($tgl_lahir, $bulanIndonesia);
                    $tgl_registrasi = $this->ubahBulanKeInggris($tgl_registrasi, $bulanIndonesia);
                    $tgl_validasi = $this->ubahBulanKeInggris($tgl_validasi, $bulanIndonesia);

                    // Konversi dari format teks DD MMMM YYYY ke Carbon instance
                    $tgl_lahir = Carbon::createFromFormat('d F Y', $tgl_lahir)->format('Y-m-d');

                  
                    // dd($tgl_registrasi);
                    $tgl_registrasi = Carbon::createFromFormat('d F Y', $tgl_registrasi)->format('Y-m-d');
                    $tgl_validasi = Carbon::createFromFormat('d F Y', $tgl_validasi)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Jika format tanggal tidak valid, set ke null atau tangani sesuai kebutuhan
                    $tgl_lahir = null;
                    $tgl_registrasi = null;
                    $tgl_validasi = null;
                }
            }

            // Jika $tgl_validasi tidak memiliki waktu, tambahkan waktu saat ini
            if ($tgl_registrasi || $tgl_validasi) {
                $tgl_registrasi = Carbon::parse($tgl_registrasi)->format('Y-m-d') . ' ' . Carbon::now()->format('H:i:s');
                $tgl_validasi = Carbon::parse($tgl_validasi)->format('Y-m-d') . ' ' . Carbon::now()->format('H:i:s');
            }

            // Menghitung umur dalam tahun, bulan, dan hari
            if ($tgl_lahir) {
                $birthdate = Carbon::parse($tgl_lahir);
                $now = Carbon::now();

                $years = $birthdate->diffInYears($now);
                $months = $birthdate->copy()->addYears($years)->diffInMonths($now);
                $days = $birthdate->copy()->addYears($years)->addMonths($months)->diffInDays($now);

                $umur = "{$years} tahun, {$months} bulan, {$days} hari";
            } else {
                $years = null;
                $months = null;
                $days = null;
            }



            // Pengecekan data pasien dengan LIKE
            $exists = Pasien::where('nama_pasien', $row[1])
                ->where('tgllahir_pasien', $tgl_lahir)
                ->where('gender_pasien', $row[5])
                ->exists();

            //menentukan nomor urut dan nomor rekamedis
              if (!$exists) {
                $set_count = Pasien::nextNoRekamMedis();

                $new_pasien = new Pasien();
                $new_pasien->nourut_pasien = $set_count;
                $new_pasien->no_rekammedis_pasien = $set_count;
                $new_pasien->nama_pasien = $row[1];
                $new_pasien->tgllahir_pasien = $tgl_lahir;
                $new_pasien->alamat_pasien = $row[4];
                $new_pasien->gender_pasien = $row[5];
                $new_pasien->save();

                $id_pasien = $new_pasien->id_pasien; // Mengambil ID pasien yang baru disimpan
              } else {
                $pasien = Pasien::where('nama_pasien', $row[1])
                    ->where('tgllahir_pasien', $tgl_lahir)
                    ->where('gender_pasien', $row[5])
                    ->first();

                $id_pasien = $pasien->id_pasien; // Mengambil ID pasien yang ditemukan
              }

              //cari parameter paket klinik
              $data_paket = ParameterPaketKlinik::where('name_parameter_paket_klinik', '=', 'Paket Prolanis Gula')->first();
              $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();
              // dd($data_paket);

              // simpan permohonan uji klinik
              $permohonan_uji_klinik = new PermohonanUjiKlinik2();
              $permohonan_uji_klinik->nourut_permohonan_uji_klinik = Str::before($row[5], '/');
              $permohonan_uji_klinik->noregister_permohonan_uji_klinik= $row[6];
              $permohonan_uji_klinik->tglregister_permohonan_uji_klinik= $tgl_registrasi;
              $permohonan_uji_klinik->pasien_permohonan_uji_klinik= $id_pasien;
              $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik= $years;
              $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik= $months;
              $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik= $days;
              $permohonan_uji_klinik->total_harga_permohonan_uji_klinik= $data_paket->harga_parameter_paket_klinik;
              $permohonan_uji_klinik->metode_pembayaran = 0;
              $permohonan_uji_klinik->status_pembayaran= 0;
              $permohonan_uji_klinik->status_permohonan_uji_klinik= 'SELESAI';
              $permohonan_uji_klinik->id_permohonan_uji_klinik_prolanis_gula= $id;
              $permohonan_uji_klinik->is_prolanis_gula= 1;
              $permohonan_uji_klinik->save();

              // dd($permohonan_uji_klinik);
              //simpan permohonan uji paket klinik

              if ($data_paket && $data_paket->parameterpaketjenisklinik->isNotEmpty()) {
                  // Mengambil item pertama dari koleksi

                  // Membuat objek baru PermohonanUjiPaketKlinik
                  $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
                  $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                  $permohonan_paket_klinik->parameter_paket_klinik = $data_paket->id_parameter_paket_klinik;
                  $permohonan_paket_klinik->parameter_jenis_klinik = $first_parameter_paket_jenis_klinik->parameter_jenis_klinik_id;
                  $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = "P";
                  $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $data_paket->harga_parameter_paket_klinik;
                  $permohonan_paket_klinik->is_prolanis_gula = 1;
                  $permohonan_paket_klinik->save();

                  // Debugging untuk memastikan data tersimpan dengan benar
                  // dd($permohonan_paket_klinik);
              } else {
                  // Tangani kasus ketika $data_paket tidak ditemukan atau relasi kosong
                  dd("Parameter Paket Klinik atau Jenis Klinik tidak ditemukan.");
              }


              $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($permohonan_uji_klinik->id_permohonan_uji_klinik);

              //cek parameter paket jenis klinik
              foreach ($data_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
                # code...
                #looping satuan parameter dari paket
                foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

                  // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                  $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                  $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                  $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                    ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                    ->where('is_khusus_baku_mutu', '0')
                    ->leftJoin('ms_library', function ($join) {
                      $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                        ->whereNull('ms_library.deleted_at')
                        ->whereNull('tb_baku_mutu.deleted_at');
                    })
                    ->first();
                  // cek dulu apakah ada data dengan data khusus sperti general atau specific
                  if ($check_parameter_by_baku_mutu) {
                    $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                  } else {
                    $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                      ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                      ->where('is_khusus_baku_mutu', '1')
                      ->where('gender_baku_mutu', $pasien_gender=="male"?"L":"P")
                      ->where(function ($query) use ($pasien_umur) {
                        $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                          ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                      })
                      ->leftJoin('ms_library', function ($join) {
                        $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                          ->whereNull('ms_library.deleted_at')
                          ->whereNull('tb_baku_mutu.deleted_at');
                      })
                      ->first();
                    if (!isset($item_parameter_by_baku_mutu)) {
                      $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                        ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                        ->where('is_khusus_baku_mutu', '1')
                        ->leftJoin('ms_library', function ($join) {
                          $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                            ->whereNull('ms_library.deleted_at')
                            ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->where('gender_baku_mutu', $pasien_gender=="male"?"L":"P")
                        ->first();
                    }

                  }

                  //simpan parameter klinik
                  $post_parameter = new PermohonanUjiParameterKlinik();
                  $post_parameter->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                  $post_parameter->permohonan_uji_paket_klinik = $permohonan_paket_klinik->id_permohonan_uji_paket_klinik;
                  $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
                  $post_parameter->parameter_paket_klinik = $permohonan_paket_klinik->parameter_paket_klinik;
                  $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
                  $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;
                  // $post_parameter->hasil_permohonan_uji_parameter_klinik = $row[$i];

                  $post_parameter->method_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->title_library;

                  $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
                  $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;
                  $post_parameter->keterangan_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->ket_default_parameter_satuan_klinik;

                  $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;
                  $post_parameter->is_prolanis_gula = 1;

                  if (isset($item_parameter_by_baku_mutu->unit_id)) {
                    $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                    $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                  } else {
                    $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                    $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                  }

                  $columns = [
                    1 => $row[7],
                    2 => $row[8],
                    3 => $row[9],
                    4 => $row[10],
                    5 => $row[11],
                    6 => $row[12],
                    7 => $row[13],
                    8 => $row[14],
                    9 => $row[15],
                    10 => $row[16],
                    11 => $row[17]
                  ];

                  if (isset($columns[$value_parametersatuanpaketklinik->sorting])) {
                      $post_parameter->hasil_permohonan_uji_parameter_klinik = $columns[$value_parametersatuanpaketklinik->sorting];
                  }

                  // dd($post_parameter);

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
                }
              }

              //simpadn data permohonan uji klinik analis dan update table permohonan uji klinik
              // $permohonan_uji_klinik->tglpengujian_permohonan_uji_klinik = '-';
              // $permohonan_uji_klinik->spesimen_darah_permohonan_uji_klinik = '-';
              // $permohonan_uji_klinik->spesimen_urine_permohonan_uji_klinik = '-';
              // $permohonan_uji_klinik->analis_permohonan_uji_klinik = '-';
              // $permohonan_uji_klinik->name_analis_permohonan_uji_klinik = '-';
              // $permohonan_uji_klinik->nip_analis_permohonan_uji_klinik = '-';
              $permohonan_uji_klinik->status_permohonan_uji_klinik = 'SELESAI';
              $permohonan_uji_klinik->save();


              //simpan verifikkasi
              // Data untuk setiap aktivitas verifikasi
              $activities = [
                ['id_verification_activity' => 1, 'date' => $tgl_registrasi, 'nama_petugas' => 'PETUGAS DUMMY 10'], // pendaftaran/registrasi
                ['id_verification_activity' => 6, 'date' => $tgl_registrasi, 'nama_petugas' => 'Tatin Prastyo Meiningsih'], // pengambilan sample
                ['id_verification_activity' => 2, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'], // Pemeriksaan / Analitik
                ['id_verification_activity' => 3, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'], // pengambilan sample
                ['id_verification_activity' => 4, 'date' => $tgl_validasi, 'nama_petugas' => 'Hewi Yuliati'],   // verifikasi
                ['id_verification_activity' => 5, 'date' => $tgl_validasi, 'nama_petugas' => 'dr. Muharyati'],   // validasi
              ];

              foreach ($activities as $key => $activity) {
                $verificationActivitySample = new VerificationActivitySample();
                $verificationActivitySample->id = Uuid::uuid4()->toString();
                $verificationActivitySample->id_verification_activity = $activity['id_verification_activity'];
                $verificationActivitySample->start_date = $activity['date'];
                $verificationActivitySample->stop_date = $activity['date'];
                $verificationActivitySample->nama_petugas = $activity['nama_petugas'];
                $verificationActivitySample->is_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                $verificationActivitySample->is_prolanis_gula = 1;
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->created_at = Carbon::now()->addSeconds($key); // Tambah detik berdasarkan urutan;

                // Simpan ke database
                $verificationActivitySample->save();
              }
              // dd($verificationActivitySample);
        }

        DB::commit();
        return back()->with('success', 'Data Prolanis Gula berhasil diimport!');
    } catch (Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan saat mengimpor data.');
    }
  }

   // Fungsi untuk mengganti nama bulan dalam bahasa Indonesia ke bahasa Inggris
  public function ubahBulanKeInggris($tanggal, $bulanIndonesia) {
      foreach ($bulanIndonesia as $namaBulanIndo => $namaBulanEng) {
          if (strpos($tanggal, $namaBulanIndo) !== false) {
              // Ganti nama bulan Indonesia dengan nama bulan dalam bahasa Inggris
              return str_replace($namaBulanIndo, $namaBulanEng, $tanggal);
          }
      }
      return $tanggal; // Jika tidak ditemukan, kembalikan tanggal tanpa perubahan
  }

  public function getProlanisGula(Request $request)
  {
      // Ambil data gula dari database
      $data = DB::table('tb_permohonan_uji_klinik_prolanis_gula')
                      ->select('id_permohonan_uji_klinik_prolanis_gula', 'nama_prolanis_gula')
                      ->when($request->is_filter, function($query) {
                          // Jika filter ada, tambahkan kondisi filter
                          $query->where('is_prolanis_gula', 1);
                      })
                      ->get();

      // Return data sebagai JSON
      return response()->json($data);
  }
}