<?php

namespace Smt\Masterweb\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\Pasien;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\BakuMutu;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Smt\Masterweb\Models\NumberKlinik;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Smt\Masterweb\Imports\ProlanisGulaImport;
use Smt\Masterweb\Imports\ProlanisUrineImport;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Exports\FormatProlanisGulaExport;
use Smt\Masterweb\Exports\FormatProlanisUrineExport;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\PermohonanUjiKlinikProlanis;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\StartNum;

use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;


class LaboratoriumPermohonanUjiKlinikProlanisManagement extends Controller
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
    $data = PermohonanUjiKlinikProlanis::orderBy('created_at', 'desc')->get();
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanis.list', [
      'data' => $data,
    ]);
  }

  public function rules($request)
  {
    $rule = [
      'nama_prolanis' => 'required',
      'tgl_prolanis' => 'required',
      'kuota_prolanis' => 'required',
    ];

    $pesan = [
      'nama_prolanis.required' => 'Nama prolanis wajib diisi.',
      'tgl_prolanis.required' => 'Tanggal prolanis wajib diisi.',
      'kuota_prolanis.required' => 'Kuota prolanis wajib diisi.',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function create()
  {
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanis.add');
  }

  public function store(Request $request)
  {
      // Validasi data
      $validator = $this->rules($request->all());

      if ($validator->fails()) {
          return response()->json(['status' => false, 'pesan' => $validator->errors()]);
      }

      DB::beginTransaction(); // Mulai transaksi

      try {
          // Simpan data ke dalam database
          $prolanis = new PermohonanUjiKlinikProlanis();
          $prolanis->nama_prolanis = $request->input('nama_prolanis');
          $prolanis->tgl_prolanis = $request->input('tgl_prolanis');
          $prolanis->kuota_prolanis = $request->input('kuota_prolanis');
          $prolanis->save();

          // Dapatkan last_number terbesar dari tabel NumberKlinik
          $count = NumberKlinik::where( DB::raw( "YEAR(tb_number_klinik.created_at)"), date('Y'))->max('last_number');

          // Booking prolanis dan Simpan data last_number_klinik
          $number_klinik = new NumberKlinik();
          $number_klinik->new_number = $count + 1;
          $number_klinik->last_number = $count + $prolanis->kuota_prolanis;
          $number_klinik->id_prolanis = $prolanis->id_permohonan_uji_klinik_prolanis;
          $number_klinik->save();

          DB::commit(); // Commit transaksi jika semua berhasil

          return response()->json(['status' => true, 'pesan' => "Data prolanis berhasil disimpan!"], 200);

      } catch (\Exception $e) {
          DB::rollBack(); // Rollback transaksi jika ada error

          // Kirim response error
          return response()->json(['status' => false, 'pesan' => "Terjadi kesalahan: " . $e->getMessage()], 500);
      }
  }

  public function downloadFormatProlanisGula($id)
  {

      DB::beginTransaction();

      try {
          $data = PermohonanUjiKlinikProlanis::where('id_permohonan_uji_klinik_prolanis', $id)->first();
          $data_number = NumberKlinik::where('id_prolanis', $id)->first();

          if($data_number){
            //ambil data last_number sebelumyanya / booking
            $count = $data_number->last_number - $data->kuota_prolanis;

          }
          // else{
          //   $count = NumberKlinik::max('last_number');

          //   // simpan last_number_klinik
          //   $number_klinik = new NumberKlinik();
          //   $number_klinik->new_number =  $count + 1;
          //   $number_klinik->last_number =  $count + $data->kuota_prolanis;
          //   $number_klinik->id_prolanis =  $id;
          //   $number_klinik->save();
          // }

          DB::commit();

          return Excel::download(new FormatProlanisGulaExport($count, $data->nama_prolanis, $data->tgl_prolanis, $data->kuota_prolanis), 'Format-Prolanis-Gula-' . $data->nama_prolanis . '-' . $data->tgl_prolanis . '.xlsx');
      } catch (\Exception $e) {
          DB::rollBack();
          return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
      }



  }

  public function importProlanisGulaOld(Request $request, $id)
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

          $row[1] = str_replace("Tn. ", "", $row[1]);
          $row[1] = str_replace("Tn.", "", $row[1]);

          $row[1] = str_replace("Ny. ", "", $row[1]);
          $row[1] = str_replace("Ny.", "", $row[1]);

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

              if($row[7] == null || $row[7] == ''){
                $tipe_pemeriksaan_prolanis = 'PROLANIS DM'; //jika HbA1C kosong
              }else{
                $tipe_pemeriksaan_prolanis = 'PROLANIS HT'; //jika HbA1C ada nilainya
              }

              // simpan permohonan uji klinik
              $permohonan_uji_klinik = new PermohonanUjiKlinik2();
              $permohonan_uji_klinik->nourut_permohonan_uji_klinik = Str::before($row[6], '/');
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
              $permohonan_uji_klinik->id_permohonan_uji_klinik_prolanis = $id;
              $permohonan_uji_klinik->is_prolanis_gula= 1; //1 = prolanis gula
              $permohonan_uji_klinik->tipe_pemeriksaan_prolanis= $tipe_pemeriksaan_prolanis;
              $permohonan_uji_klinik->no_prolanis_1= $row[20];
              $permohonan_uji_klinik->no_prolanis_2= $row[21];
              $permohonan_uji_klinik->no_prolanis_3= $row[22];
              $permohonan_uji_klinik->no_prolanis_4= $row[23];
              $permohonan_uji_klinik->no_prolanis_5= $row[24];
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
                ['id_verification_activity' => 1, 'date' => $tgl_registrasi, 'nama_petugas' => 'Muhammad Fauzan'], // pendaftaran/registrasi
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

              $data_prolanis = PermohonanUjiKlinikProlanis::findOrFail($id);
              $data_prolanis->is_prolanis_gula = 1;
              $data_prolanis->status_prolanis = 1; //sudah import

              $data_prolanis->save();
              // dd($data_prolanis);
        }

        DB::commit();
        return back()->with('success', 'Data Prolanis Gula berhasil diimport!');
    } catch (Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan saat mengimpor data.');
    }
  }

  public function importProlanisGula(Request $request, $id)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx'
    ]);

    $import = new ProlanisGulaImport();
    $data = Excel::toArray($import, $request->file('file'))[0];

    // Variabel untuk tracking hasil import
    $totalRows = count($data);
    $successRows = 0;
    $skippedRows = 0;
    $errorRows = [];

    //cek nomor lab apakah sudah pernah diinput
    foreach ($data as $rowIndex => $row) {
      // Skip jika row kosong atau data penting tidak ada
      if (empty($row[6])) {
        continue;
      }

      $data_permohonan_uji_klinik = PermohonanUjiKlinik2::where('noregister_permohonan_uji_klinik', $row[6])->first();
      if($data_permohonan_uji_klinik){
        return back()->with('error', 'Data dengan nomor lab ' . $row[6] . ' sudah pernah di unggah!');
      }
    }

    DB::beginTransaction();

    try {
      foreach ($data as $rowIndex => $row) {
        // Validasi data wajib - skip jika data penting kosong
        if (empty($row[1]) || empty($row[6])) {
          $skippedRows++;
          continue;
        }

        try {
          // Bersihkan nama dari prefix
          $row[1] = str_replace(["Tn. ", "Tn.", "Ny. ", "Ny."], "", $row[1]);

          // Mapping kolom dengan nilai default untuk data yang kosong
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

          $tgl_lahir = $row[3] ?? null;
          $tgl_registrasi = $row[18] ?? null;
          $tgl_validasi = $row[19] ?? null;

          // Mengonversi tanggal dengan error handling
          if ($tgl_lahir && is_numeric($tgl_lahir) && $tgl_registrasi && is_numeric($tgl_registrasi) && $tgl_validasi && is_numeric($tgl_validasi)) {
            // Konversi dari format serial Excel ke DateTime
            $tgl_lahir = Date::excelToDateTimeObject($tgl_lahir)->format('Y-m-d');
            $tgl_registrasi = Date::excelToDateTimeObject($tgl_registrasi)->format('Y-m-d');
            $tgl_validasi = Date::excelToDateTimeObject($tgl_validasi)->format('Y-m-d');
          } else {
            try {
              if ($tgl_lahir) {
                $tgl_lahir = $this->ubahBulanKeInggris($tgl_lahir, $bulanIndonesia);
                $tgl_lahir = Carbon::createFromFormat('d F Y', $tgl_lahir)->format('Y-m-d');
              }

              if ($tgl_registrasi) {
                $tgl_registrasi = $this->ubahBulanKeInggris($tgl_registrasi, $bulanIndonesia);
                $tgl_registrasi = Carbon::createFromFormat('d F Y', $tgl_registrasi)->format('Y-m-d');
              }

              if ($tgl_validasi) {
                $tgl_validasi = $this->ubahBulanKeInggris($tgl_validasi, $bulanIndonesia);
                $tgl_validasi = Carbon::createFromFormat('d F Y', $tgl_validasi)->format('Y-m-d');
              }
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, gunakan tanggal default atau null
              Log::warning("Invalid date format in row " . ($rowIndex + 1) . ": " . $e->getMessage());
              $tgl_lahir = $tgl_lahir ?: null;
              $tgl_registrasi = $tgl_registrasi ?: Carbon::now()->format('Y-m-d');
              $tgl_validasi = $tgl_validasi ?: Carbon::now()->format('Y-m-d');
            }
          }

          // Jika masih ada tanggal yang kosong, gunakan default
          $tgl_registrasi = $tgl_registrasi ?: Carbon::now()->format('Y-m-d');
          $tgl_validasi = $tgl_validasi ?: Carbon::now()->format('Y-m-d');

          // Tambahkan waktu ke tanggal
          if ($tgl_registrasi || $tgl_validasi) {
            $tgl_registrasi = Carbon::parse($tgl_registrasi)->format('Y-m-d') . ' ' . Carbon::now()->format('H:i:s');
            $tgl_validasi = Carbon::parse($tgl_validasi)->format('Y-m-d') . ' ' . Carbon::now()->format('H:i:s');
          }

          // Menghitung umur dengan error handling
          $years = null;
          $months = null;
          $days = null;

          if ($tgl_lahir) {
            try {
              $birthdate = Carbon::parse($tgl_lahir);
              $now = Carbon::now();

              $years = $birthdate->diffInYears($now);
              $months = $birthdate->copy()->addYears($years)->diffInMonths($now);
              $days = $birthdate->copy()->addYears($years)->addMonths($months)->diffInDays($now);
            } catch (\Exception $e) {
              Log::warning("Error calculating age for row " . ($rowIndex + 1) . ": " . $e->getMessage());
            }
          }

          // Validasi gender dengan default
          $gender = $row[5] ?? 'male';

          // Pengecekan data pasien dengan LIKE
          $exists = Pasien::where('nama_pasien', $row[1])
            ->where('tgllahir_pasien', $tgl_lahir)
            ->where('gender_pasien', $gender)
            ->exists();

          //menentukan nomor urut dan nomor rekam medis
          if (!$exists) {
            $set_count = Pasien::nextNoRekamMedis();

            $new_pasien = new Pasien();
            $new_pasien->nourut_pasien = $set_count;
            $new_pasien->no_rekammedis_pasien = $set_count;
            $new_pasien->nama_pasien = $row[1];
            $new_pasien->tgllahir_pasien = $tgl_lahir;
            $new_pasien->alamat_pasien = $row[4] ?? '';
            $new_pasien->gender_pasien = $gender;
            $new_pasien->save();

            $id_pasien = $new_pasien->id_pasien;
          } else {
            $pasien = Pasien::where('nama_pasien', $row[1])
              ->where('tgllahir_pasien', $tgl_lahir)
              ->where('gender_pasien', $gender)
              ->first();

            $id_pasien = $pasien->id_pasien;
          }

          //cari parameter paket klinik
          $data_paket = ParameterPaketKlinik::where('name_parameter_paket_klinik', '=', 'Paket Prolanis Gula')->first();

          if (!$data_paket) {
            throw new \Exception("Parameter Paket Klinik 'Paket Prolanis Gula' tidak ditemukan");
          }

          $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();

          // Tentukan tipe pemeriksaan dengan validasi
          $tipe_pemeriksaan_prolanis = (empty($row[7]) || $row[7] == null) ? 'PROLANIS DM' : 'PROLANIS HT';

          // simpan permohonan uji klinik
          $permohonan_uji_klinik = new PermohonanUjiKlinik2();
          $permohonan_uji_klinik->nourut_permohonan_uji_klinik = Str::before($row[6], '/');
          $permohonan_uji_klinik->noregister_permohonan_uji_klinik = $row[6];
          $permohonan_uji_klinik->tglregister_permohonan_uji_klinik = $tgl_registrasi;
          $permohonan_uji_klinik->pasien_permohonan_uji_klinik = $id_pasien;
          $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik = $years;
          $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik = $months;
          $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik = $days;
          $permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $data_paket->harga_parameter_paket_klinik;
          $permohonan_uji_klinik->metode_pembayaran = 0;
          $permohonan_uji_klinik->status_pembayaran = 0;
          $permohonan_uji_klinik->status_permohonan_uji_klinik = 'SELESAI';
          $permohonan_uji_klinik->id_permohonan_uji_klinik_prolanis = $id;
          $permohonan_uji_klinik->is_prolanis_gula = 1;
          $permohonan_uji_klinik->tipe_pemeriksaan_prolanis = $tipe_pemeriksaan_prolanis;
          $permohonan_uji_klinik->no_prolanis_1 = $row[20] ?? null;
          $permohonan_uji_klinik->no_prolanis_2 = $row[21] ?? null;
          $permohonan_uji_klinik->no_prolanis_3 = $row[22] ?? null;
          $permohonan_uji_klinik->no_prolanis_4 = $row[23] ?? null;
          $permohonan_uji_klinik->no_prolanis_5 = $row[24] ?? null;
          $permohonan_uji_klinik->save();

          // Lanjutkan dengan proses yang sama seperti sebelumnya...
          // (kode untuk permohonan paket klinik, parameter, dll)

          if ($data_paket && $data_paket->parameterpaketjenisklinik->isNotEmpty()) {
            $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
            $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
            $permohonan_paket_klinik->parameter_paket_klinik = $data_paket->id_parameter_paket_klinik;
            $permohonan_paket_klinik->parameter_jenis_klinik = $first_parameter_paket_jenis_klinik->parameter_jenis_klinik_id;
            $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = "P";
            $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $data_paket->harga_parameter_paket_klinik;
            $permohonan_paket_klinik->is_prolanis_gula = 1;
            $permohonan_paket_klinik->save();
          } else {
            throw new \Exception("Parameter Paket Klinik atau Jenis Klinik tidak ditemukan untuk row " . ($rowIndex + 1));
          }

          $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($permohonan_uji_klinik->id_permohonan_uji_klinik);

          // Proses parameter dengan error handling
          foreach ($data_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
            foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

              $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
              $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

              // Mapping baku mutu (kode sama seperti sebelumnya)
              $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                ->where('is_khusus_baku_mutu', '0')
                ->leftJoin('ms_library', function ($join) {
                  $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                    ->whereNull('ms_library.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
                })
                ->first();

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

              // Simpan parameter klinik
              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
              $post_parameter->permohonan_uji_paket_klinik = $permohonan_paket_klinik->id_permohonan_uji_paket_klinik;
              $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
              $post_parameter->parameter_paket_klinik = $permohonan_paket_klinik->parameter_paket_klinik;
              $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
              $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

              $post_parameter->method_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->title_library ?? null;
              $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
              $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;
              $post_parameter->keterangan_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->ket_default_parameter_satuan_klinik ?? null;
              $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik ?? 0;
              $post_parameter->is_prolanis_gula = 1;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              // Mapping hasil dengan validasi
              $columns = [
                1 => $row[7] ?? null,
                2 => $row[8] ?? null,
                3 => $row[9] ?? null,
                4 => $row[10] ?? null,
                5 => $row[11] ?? null,
                6 => $row[12] ?? null,
                7 => $row[13] ?? null,
                8 => $row[14] ?? null,
                9 => $row[15] ?? null,
                10 => $row[16] ?? null,
                11 => $row[17] ?? null
              ];

              if (isset($columns[$value_parametersatuanpaketklinik->sorting])) {
                $post_parameter->hasil_permohonan_uji_parameter_klinik = $columns[$value_parametersatuanpaketklinik->sorting];
              }

              $post_parameter->save();

              // Proses sub parameter
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)->get();

              if (count($data_parameter_subsatuan) > 0) {
                foreach ($data_parameter_subsatuan as $value_parameter_subsatuan) {
                  $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                  $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                  $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                  if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                      ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                      ->first();

                    if ($item_parameter_subsatuan_by_baku_mutu) {
                      $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                      $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                    }
                  }

                  $post_parameter_subsatuan->save();
                }
              }
            }
          }

          // Update status
          $permohonan_uji_klinik->status_permohonan_uji_klinik = 'SELESAI';
          $permohonan_uji_klinik->save();

          // Simpan verifikasi
          $activities = [
            ['id_verification_activity' => 1, 'date' => $tgl_registrasi, 'nama_petugas' => 'Muhammad Fauzan'],
            ['id_verification_activity' => 6, 'date' => $tgl_registrasi, 'nama_petugas' => 'Tatin Prastyo Meiningsih'],
            ['id_verification_activity' => 2, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 3, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 4, 'date' => $tgl_validasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 5, 'date' => $tgl_validasi, 'nama_petugas' => 'dr. Muharyati'],
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
            $verificationActivitySample->created_at = Carbon::now()->addSeconds($key);
            $verificationActivitySample->save();
          }

          $successRows++;

        } catch (InvalidArgumentException $e) {
          DB::rollBack();
          Log::error('InvalidArgumentException: ' . $e->getMessage());
          return back()->with('error', 'Format file tidak valid: ' . $e->getMessage());
        } catch (\Exception $e) {
          // Log error untuk row ini tapi lanjutkan ke row berikutnya
          $errorRows[] = [
            'row' => $rowIndex + 1,
            'error' => $e->getMessage(),
            'data' => $row[1] ?? 'Unknown'
          ];
          $skippedRows++;
          Log::error("Error processing row " . ($rowIndex + 1) . ": " . $e->getMessage(), ['row_data' => $row]);
          continue;
        }
      }

      // Update status prolanis setelah semua berhasil
      $data_prolanis = PermohonanUjiKlinikProlanis::findOrFail($id);
      $data_prolanis->is_prolanis_gula = 1;
      $data_prolanis->status_prolanis = 1;
      $data_prolanis->save();

      DB::commit();

      return back()->with('success', 'Data Prolanis Gula berhasil diimport!');

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Import failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
      return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
    }
  }

  public function downloadFormatProlanisUrine($id)
  {
      DB::beginTransaction();

      try {
          $data = PermohonanUjiKlinikProlanis::where('id_permohonan_uji_klinik_prolanis', $id)->first();
          $data_number = NumberKlinik::where('id_prolanis', $id)->first();

          if($data_number){
            //ambil data last_number sebelumyanya / booking
            $count = $data_number->last_number - $data->kuota_prolanis;

          }else{
            $count = NumberKlinik::where( DB::raw( "YEAR(tb_number_klinik.created_at)"), date('Y'))->max('last_number');

            // simpan last_number_klinik
            $number_klinik = new NumberKlinik();
            $number_klinik->new_number =  $count + 1;
            $number_klinik->last_number =  $count + $data->kuota_prolanis;
            $number_klinik->id_prolanis =  $id;
            $number_klinik->save();
          }

          DB::commit();

          return Excel::download(new FormatProlanisUrineExport($count, $data->nama_prolanis, $data->tgl_prolanis, $data->kuota_prolanis), 'Format-Prolanis-Urine-' . $data->nama_prolanis . '-' . $data->tgl_prolanis . '.xlsx');
      } catch (\Exception $e) {
          DB::rollBack();
          return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
      }
  }

  public function importProlanisUrineOld(Request $request, $id)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx'
    ]);

    $import = new ProlanisUrineImport();
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

          $row[1] = str_replace("Tn. ", "", $row[1]);
          $row[1] = str_replace("Tn.", "", $row[1]);

          $row[1] = str_replace("Ny. ", "", $row[1]);
          $row[1] = str_replace("Ny.", "", $row[1]);
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
          // row[19] = tgl_validasi

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
            $tgl_registrasi = $row[27];
            $tgl_validasi = $row[28];

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
              $data_paket = ParameterPaketKlinik::where('name_parameter_paket_klinik', '=', 'Paket Prolanis Urine')->first();
              $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();
              // dd($data_paket);

              // simpan permohonan uji klinik
              $permohonan_uji_klinik = new PermohonanUjiKlinik2();
              $permohonan_uji_klinik->nourut_permohonan_uji_klinik = Str::before($row[6], '/');
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
              $permohonan_uji_klinik->id_permohonan_uji_klinik_prolanis = $id;
              $permohonan_uji_klinik->is_prolanis_urine= 1;
              $permohonan_uji_klinik->no_prolanis_1= $row[29];
              $permohonan_uji_klinik->no_prolanis_2= $row[30];
              $permohonan_uji_klinik->no_prolanis_3= $row[31];
              $permohonan_uji_klinik->no_prolanis_4= $row[32];
              $permohonan_uji_klinik->no_prolanis_5= $row[33];
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
                  $permohonan_paket_klinik->is_prolanis_urine = 1;
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
                  $post_parameter->is_prolanis_urine = 1;

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
                    11 => $row[17],
                    12 => $row[18],
                    13 => $row[19],
                    14 => $row[20],
                    15 => $row[21],
                    16 => $row[22],
                    17 => $row[23],
                    18 => $row[24],
                    19 => $row[25],
                    20 => $row[26],
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
                ['id_verification_activity' => 1, 'date' => $tgl_registrasi, 'nama_petugas' => 'Muhammad Fauzan'], // pendaftaran/registrasi
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
                $verificationActivitySample->is_prolanis_urine = 1;
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->created_at = Carbon::now()->addSeconds($key); // Tambah detik berdasarkan urutan;

                // Simpan ke database
                $verificationActivitySample->save();
              }

              $data_prolanis = PermohonanUjiKlinikProlanis::findOrFail($id);
              $data_prolanis->is_prolanis_urine = 1;
              $data_prolanis->status_prolanis = 1; //sudah import

              $data_prolanis->save();
              // dd($data_prolanis);

        }

        DB::commit();
        return back()->with('success', 'Data Prolanis Urine berhasil diimport!');
    } catch (Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan saat mengimpor data.');
    }
  }

  public function importProlanisUrine(Request $request, $id)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx'
    ]);

    $import = new ProlanisUrineImport();
    $data = Excel::toArray($import, $request->file('file'))[0];

    // Cek nomor lab apakah sudah pernah diinput
    foreach ($data as $row) {
      // Skip jika nomor lab kosong
      if (empty($row[6])) {
        continue;
      }

      $data_permohonan_uji_klinik = PermohonanUjiKlinik2::where('noregister_permohonan_uji_klinik', $row[6])->first();
      if($data_permohonan_uji_klinik){
        return back()->with('error', 'Data dengan nomor lab format sudah pernah di unggah!');
      }
    }

    DB::beginTransaction();

    try {
      $skipped_rows = 0;
      $processed_rows = 0;

      foreach ($data as $index => $row) {
        // Validasi data yang wajib ada
        if (empty($row[1]) || empty($row[6])) { // nama dan nomor lab
          Log::warning("Row " . ($index + 1) . " skipped: Missing required data (nama or nomor lab)");
          $skipped_rows++;
          continue;
        }

        // Validasi tambahan untuk data penting lainnya
        if (empty($row[3]) || empty($row[5])) { // tanggal lahir dan gender
          Log::warning("Row " . ($index + 1) . " skipped: Missing birth date or gender");
          $skipped_rows++;
          continue;
        }

        try {
          $row[1] = str_replace("Tn. ", "", $row[1]);
          $row[1] = str_replace("Tn.", "", $row[1]);
          $row[1] = str_replace("Ny. ", "", $row[1]);
          $row[1] = str_replace("Ny.", "", $row[1]);

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
          $tgl_registrasi = isset($row[27]) ? $row[27] : null;
          $tgl_validasi = isset($row[28]) ? $row[28] : null;

          // Skip jika tanggal registrasi atau validasi kosong
          if (empty($tgl_registrasi) || empty($tgl_validasi)) {
            Log::warning("Row " . ($index + 1) . " skipped: Missing registration or validation date");
            $skipped_rows++;
            continue;
          }

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
              $tgl_registrasi = Carbon::createFromFormat('d F Y', $tgl_registrasi)->format('Y-m-d');
              $tgl_validasi = Carbon::createFromFormat('d F Y', $tgl_validasi)->format('Y-m-d');
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip row ini
              Log::warning("Row " . ($index + 1) . " skipped: Invalid date format - " . $e->getMessage());
              $skipped_rows++;
              continue;
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

          // Menentukan nomor urut dan nomor rekamedis
          if (!$exists) {
            $set_count = Pasien::nextNoRekamMedis();

            $new_pasien = new Pasien();
            $new_pasien->nourut_pasien = $set_count;
            $new_pasien->no_rekammedis_pasien = $set_count;
            $new_pasien->nama_pasien = $row[1];
            $new_pasien->tgllahir_pasien = $tgl_lahir;
            $new_pasien->alamat_pasien = isset($row[4]) ? $row[4] : '';
            $new_pasien->gender_pasien = $row[5];
            $new_pasien->save();

            $id_pasien = $new_pasien->id_pasien;
          } else {
            $pasien = Pasien::where('nama_pasien', $row[1])
              ->where('tgllahir_pasien', $tgl_lahir)
              ->where('gender_pasien', $row[5])
              ->first();

            $id_pasien = $pasien->id_pasien;
          }

          // Cari parameter paket klinik
          $data_paket = ParameterPaketKlinik::where('name_parameter_paket_klinik', '=', 'Paket Prolanis Urine')->first();

          if (!$data_paket) {
            Log::warning("Row " . ($index + 1) . " skipped: Parameter paket klinik not found");
            $skipped_rows++;
            continue;
          }

          $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();

          // Simpan permohonan uji klinik
          $permohonan_uji_klinik = new PermohonanUjiKlinik2();
          $permohonan_uji_klinik->nourut_permohonan_uji_klinik = Str::before($row[6], '/');
          $permohonan_uji_klinik->noregister_permohonan_uji_klinik = $row[6];
          $permohonan_uji_klinik->tglregister_permohonan_uji_klinik = $tgl_registrasi;
          $permohonan_uji_klinik->pasien_permohonan_uji_klinik = $id_pasien;
          $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik = $years;
          $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik = $months;
          $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik = $days;
          $permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $data_paket->harga_parameter_paket_klinik;
          $permohonan_uji_klinik->metode_pembayaran = 0;
          $permohonan_uji_klinik->status_pembayaran = 0;
          $permohonan_uji_klinik->status_permohonan_uji_klinik = 'SELESAI';
          $permohonan_uji_klinik->id_permohonan_uji_klinik_prolanis = $id;
          $permohonan_uji_klinik->is_prolanis_urine = 1;
          $permohonan_uji_klinik->no_prolanis_1 = isset($row[29]) ? $row[29] : null;
          $permohonan_uji_klinik->no_prolanis_2 = isset($row[30]) ? $row[30] : null;
          $permohonan_uji_klinik->no_prolanis_3 = isset($row[31]) ? $row[31] : null;
          $permohonan_uji_klinik->no_prolanis_4 = isset($row[32]) ? $row[32] : null;
          $permohonan_uji_klinik->no_prolanis_5 = isset($row[33]) ? $row[33] : null;
          $permohonan_uji_klinik->save();

          // Simpan permohonan uji paket klinik
          if ($data_paket && $data_paket->parameterpaketjenisklinik->isNotEmpty()) {
            $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
            $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
            $permohonan_paket_klinik->parameter_paket_klinik = $data_paket->id_parameter_paket_klinik;
            $permohonan_paket_klinik->parameter_jenis_klinik = $first_parameter_paket_jenis_klinik->parameter_jenis_klinik_id;
            $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = "P";
            $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $data_paket->harga_parameter_paket_klinik;
            $permohonan_paket_klinik->is_prolanis_urine = 1;
            $permohonan_paket_klinik->save();
          } else {
            Log::warning("Row " . ($index + 1) . " skipped: Parameter Paket Klinik atau Jenis Klinik tidak ditemukan");
            $skipped_rows++;
            continue;
          }

          $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($permohonan_uji_klinik->id_permohonan_uji_klinik);

          // Cek parameter paket jenis klinik
          foreach ($data_paket->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
            foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

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

              if ($check_parameter_by_baku_mutu) {
                $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
              } else {
                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                  ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                  ->where('is_khusus_baku_mutu', '1')
                  ->where('gender_baku_mutu', $pasien_gender == "male" ? "L" : "P")
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
                    ->where('gender_baku_mutu', $pasien_gender == "male" ? "L" : "P")
                    ->first();
                }
              }

              // Simpan parameter klinik
              $post_parameter = new PermohonanUjiParameterKlinik();
              $post_parameter->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
              $post_parameter->permohonan_uji_paket_klinik = $permohonan_paket_klinik->id_permohonan_uji_paket_klinik;
              $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
              $post_parameter->parameter_paket_klinik = $permohonan_paket_klinik->parameter_paket_klinik;
              $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
              $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

              $post_parameter->method_permohonan_uji_parameter_klinik = isset($item_parameter_by_baku_mutu->title_library) ? $item_parameter_by_baku_mutu->title_library : null;
              $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
              $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;
              $post_parameter->keterangan_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->ket_default_parameter_satuan_klinik;
              $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;
              $post_parameter->is_prolanis_urine = 1;

              if (isset($item_parameter_by_baku_mutu->unit_id)) {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
              } else {
                $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
              }

              $columns = [
                1 => isset($row[7]) ? $row[7] : null,
                2 => isset($row[8]) ? $row[8] : null,
                3 => isset($row[9]) ? $row[9] : null,
                4 => isset($row[10]) ? $row[10] : null,
                5 => isset($row[11]) ? $row[11] : null,
                6 => isset($row[12]) ? $row[12] : null,
                7 => isset($row[13]) ? $row[13] : null,
                8 => isset($row[14]) ? $row[14] : null,
                9 => isset($row[15]) ? $row[15] : null,
                10 => isset($row[16]) ? $row[16] : null,
                11 => isset($row[17]) ? $row[17] : null,
                12 => isset($row[18]) ? $row[18] : null,
                13 => isset($row[19]) ? $row[19] : null,
                14 => isset($row[20]) ? $row[20] : null,
                15 => isset($row[21]) ? $row[21] : null,
                16 => isset($row[22]) ? $row[22] : null,
                17 => isset($row[23]) ? $row[23] : null,
                18 => isset($row[24]) ? $row[24] : null,
                19 => isset($row[25]) ? $row[25] : null,
                20 => isset($row[26]) ? $row[26] : null,
              ];

              if (isset($columns[$value_parametersatuanpaketklinik->sorting])) {
                $post_parameter->hasil_permohonan_uji_parameter_klinik = $columns[$value_parametersatuanpaketklinik->sorting];
              }

              $simpan_post_parameter = $post_parameter->save();

              // Parameter subsatuan handling
              $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)->get();

              if (count($data_parameter_subsatuan) > 0) {
                foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                  $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                  $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                  $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                  if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                      ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                      ->first();

                    if ($item_parameter_subsatuan_by_baku_mutu) {
                      $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                      $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                    }
                  }

                  $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                }
              }
            }
          }

          $permohonan_uji_klinik->status_permohonan_uji_klinik = 'SELESAI';
          $permohonan_uji_klinik->save();

          // Simpan verifikasi
          $activities = [
            ['id_verification_activity' => 1, 'date' => $tgl_registrasi, 'nama_petugas' => 'Muhammad Fauzan'],
            ['id_verification_activity' => 6, 'date' => $tgl_registrasi, 'nama_petugas' => 'Tatin Prastyo Meiningsih'],
            ['id_verification_activity' => 2, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 3, 'date' => $tgl_registrasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 4, 'date' => $tgl_validasi, 'nama_petugas' => 'Hewi Yuliati'],
            ['id_verification_activity' => 5, 'date' => $tgl_validasi, 'nama_petugas' => 'dr. Muharyati'],
          ];

          foreach ($activities as $key => $activity) {
            $verificationActivitySample = new VerificationActivitySample();
            $verificationActivitySample->id = Uuid::uuid4()->toString();
            $verificationActivitySample->id_verification_activity = $activity['id_verification_activity'];
            $verificationActivitySample->start_date = $activity['date'];
            $verificationActivitySample->stop_date = $activity['date'];
            $verificationActivitySample->nama_petugas = $activity['nama_petugas'];
            $verificationActivitySample->is_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
            $verificationActivitySample->is_prolanis_urine = 1;
            $verificationActivitySample->is_done = 1;
            $verificationActivitySample->created_at = Carbon::now()->addSeconds($key);
            $verificationActivitySample->save();
          }

          $data_prolanis = PermohonanUjiKlinikProlanis::findOrFail($id);
          $data_prolanis->is_prolanis_urine = 1;
          $data_prolanis->status_prolanis = 1;
          $data_prolanis->save();

          $processed_rows++;

        } catch (\Exception $rowException) {
          // Log error untuk row ini dan lanjut ke row berikutnya
          Log::error("Error processing row " . ($index + 1) . ": " . $rowException->getMessage());
          $skipped_rows++;
          continue;
        }
      }

      DB::commit();

      $message = "Data Prolanis Urine berhasil diimport! ";

      return back()->with('success', $message);

    } catch (InvalidArgumentException $e) {
      DB::rollBack();
      Log::error('InvalidArgumentException: ' . $e->getMessage());
      return back()->with('error', 'Format file tidak valid: ' . $e->getMessage());
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Import Error: ' . $e->getMessage());
      return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
    }
  }

   // Fungsi untuk mengganti nama bulan dalam bahasa Indonesia ke bahasa Inggris
  public function ubahBulanKeInggris($tanggal, $bulanIndonesia)
  {
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
      $data = DB::table('tb_permohonan_uji_klinik_prolanis')
                      ->select('id_permohonan_uji_klinik_prolanis', 'nama_prolanis')
                      ->when($request->is_filter, function($query) {
                          // Jika filter ada, tambahkan kondisi filter
                          $query->where('is_prolanis_gula', 1);
                      })
                      ->where('is_prolanis_gula', 1)
                      ->get();

      // Return data sebagai JSON
      return response()->json($data);
  }

  public function getProlanisUrine(Request $request)
  {
      // Ambil data urine dari database
      $data = DB::table('tb_permohonan_uji_klinik_prolanis')
                      ->select('id_permohonan_uji_klinik_prolanis', 'nama_prolanis')
                      ->when($request->is_filter, function($query) {
                          // Jika filter ada, tambahkan kondisi filter
                          $query->where('is_prolanis_urine', 1);
                      })
                      ->where('is_prolanis_urine', 1)
                      ->get();

      // Return data sebagai JSON
      return response()->json($data);
  }

  public function getAllProlanis(Request $request)
  {
      // Ambil data all prolanis dari database
      $data = DB::table('tb_permohonan_uji_klinik_prolanis')
                      ->select('id_permohonan_uji_klinik_prolanis', 'nama_prolanis')
                      ->when($request->is_filter, function($query) {
                          // Jika filter ada, tambahkan kondisi filter
                          $query->where([
                            ['is_prolanis_gula', 1],
                            ['is_prolanis_urine', 1],
                          ]);
                      })
                      ->get();

      // Return data sebagai JSON
      return response()->json($data);
  }

  public function printAmplopProlanis($id){
    $data = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->first();
    $pasien = Pasien::where('id_pasien', $data->pasien_permohonan_uji_klinik)->first();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanis.print_amplop_prolanis', [
      'data' => $data,
      'pasien' => $pasien,
    ]);
  }

  public function sortingNumberKlinik()
  {
    \Smt\Masterweb\Models\GlobalLabSequence::resequenceAutoOnlyForYear((int) date('Y'));
  }

  public function destroy($id)
  {
      DB::beginTransaction();

      try {
          // Hapus data prolanis
          $hapusProlanis = PermohonanUjiKlinikProlanis::where('id_permohonan_uji_klinik_prolanis', $id)->delete();

          // Hapus nomor klinik
          $hapusNumberKlinik = NumberKlinik::where('id_prolanis', $id)->delete();

          // $this->sortingNumberKlinik(); // sementara dinonaktifkan

          // dd("cek");


          if ($hapusProlanis && $hapusNumberKlinik) {
              DB::commit(); // Commit transaksi jika semua berhasil

              return response()->json(['status' => true, 'pesan' => "Data Prolanis berhasil dihapus!"], 200);
          }

          // Rollback jika salah satu operasi gagal
          DB::rollback();
          return response()->json(['status' => false, 'pesan' => "Data Prolanis gagal dihapus!"], 400);

      } catch (\Exception $e) {
          DB::rollback(); // Rollback transaksi jika terjadi exception
          return response()->json(['status' => false, 'pesan' => $e->getMessage()], 500);
      }
  }
}
