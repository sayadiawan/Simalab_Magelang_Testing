<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\PermohonanUjiKlinikHaji;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\Customer;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterCategoryLayout;
use Smt\Masterweb\Models\ParameterPaketExtra;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Smt\Masterweb\Exports\FormatHajiExport;
use Smt\Masterweb\Exports\RekapHajiExport;
use Smt\Masterweb\Exports\RekapHajiUrinRutinExport;
use Smt\Masterweb\Exports\PasienHajiExport;
use Smt\Masterweb\Imports\HajiImport;
use Smt\Masterweb\Helpers\Smt;
use Smt\Masterweb\Helpers\DateHelper;
use Smt\Masterweb\Helpers\BakuMutuPermohonanKlinikHelper;
use Smt\Masterweb\Helpers\HajiPaketHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\NomorChangeHistory;
use Smt\Masterweb\Models\NumberKlinik;
use Smt\Masterweb\Models\KlinikNumberSettings;
use Smt\Masterweb\Models\NomerLabSequence;
use Smt\Masterweb\Models\PermohonanUjiAnalisKlinik;
use Smt\Masterweb\Models\GlobalLabSequence;
use Smt\Masterweb\Models\GlobalLabSequenceDetail;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\StartNum;
use Smt\Masterweb\Models\OrderTms;
use Smt\Masterweb\Models\OrderDetailTms;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use PDF;
use Exception;


class LaboratoriumPermohonanUjiKlinikHajiManagement extends Controller
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
    $dateStart = null;
    $dateEnd = null;

    try {
      if ($request->filled('date_start')) {
        $dateStart = Carbon::parse($request->get('date_start'))->format('Y-m-d');
      }
      if ($request->filled('date_end')) {
        $dateEnd = Carbon::parse($request->get('date_end'))->format('Y-m-d');
      }
    } catch (\Throwable $e) {
      $dateStart = null;
      $dateEnd = null;
    }

    $query = PermohonanUjiKlinikHaji::query()->orderBy('tgl_haji', 'desc')->orderBy('created_at', 'desc');

    if ($dateStart && $dateEnd) {
      if ($dateStart > $dateEnd) {
        // Tukar agar filter tetap jalan jika user salah input urutan
        [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
      }
      $query->whereDate('tgl_haji', '>=', $dateStart)
        ->whereDate('tgl_haji', '<=', $dateEnd);
    } elseif ($dateStart) {
      $query->whereDate('tgl_haji', '>=', $dateStart);
    } elseif ($dateEnd) {
      $query->whereDate('tgl_haji', '<=', $dateEnd);
    }

    $data = $query->get();

    // Hitung jumlah pasien dan status pembayaran untuk setiap haji
    foreach ($data as $item) {
      $permohonanList = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $item->id_permohonan_uji_klinik_haji)
        ->whereNull('deleted_at')
        ->get();

      $item->jumlah_pasien = $permohonanList->count();

      // Hitung total harga dan total terbayar
      $total_harga = $permohonanList->sum('total_harga_permohonan_uji_klinik');
      $permohonanIds = $permohonanList->pluck('id_permohonan_uji_klinik')->toArray();
      $total_terbayar = PermohonanUjiPaymentKlinik::whereIn('permohonan_uji_klinik_id', $permohonanIds)
        ->whereNull('deleted_at')
        ->sum('terbayar_permohonan_uji_payment_klinik');

      // Tentukan status pembayaran
      $item->status_pembayaran = ($total_terbayar >= $total_harga && $total_harga > 0) ? 'Lunas' : 'Belum Lunas';
      $item->total_harga = $total_harga;
      $item->total_terbayar = $total_terbayar;
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.list', [
      'data' => $data,
      'date_start' => $dateStart,
      'date_end' => $dateEnd,
    ]);
  }

  public function rules($request)
  {
    $rule = [
      'nama_haji' => 'required|string|max:255',
      'tgl_haji' => 'required',
      'kuota_haji' => 'required',
];

    $pesan = [
      'nama_haji.required' => 'Nama Haji wajib diisi.',
      'tgl_haji.required' => 'Tanggal Haji wajib diisi.',
      'kuota_haji.required' => 'Kuota Haji wajib diisi.',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function create()
  {
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.add');
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
          // Simpan data haji ke dalam database
          $haji = new PermohonanUjiKlinikHaji();
          $haji->nama_haji = $request->input('nama_haji');
          $haji->tgl_haji = $request->input('tgl_haji');
          $haji->kuota_haji = $request->input('kuota_haji');
          $haji->save();

          // Dapatkan last_number terbesar dari tabel NumberKlinik

          $count = NumberKlinik::where( DB::raw( "YEAR(tb_number_klinik.created_at)"), date('Y'))->max('last_number');
          // $count = NumberKlinik::max('last_number');

          // Booking haji dan Simpan data last_number_klinik
          $number_klinik = new NumberKlinik();
          $number_klinik->new_number = $count + 1;
          $number_klinik->last_number = $count + $haji->kuota_haji;
          $number_klinik->id_haji = $haji->id_permohonan_uji_klinik_haji; // Ambil ID setelah penyimpanan
          $number_klinik->save();

          DB::commit(); // Commit transaksi jika semua berhasil

          return response()->json(['status' => true, 'pesan' => "Data haji berhasil disimpan!"], 200);

      } catch (\Exception $e) {
          DB::rollBack(); // Rollback transaksi jika ada error

          // Kirim response error
          return response()->json(['status' => false, 'pesan' => "Terjadi kesalahan: " . $e->getMessage()], 500);
      }
  }

  public function downloadFormatHaji($id, Request $request)
  {
      DB::beginTransaction();

      try {
          $data = PermohonanUjiKlinikHaji::where('id_permohonan_uji_klinik_haji', $id)->first();

          // Preview nomor sampel berikutnya dari GlobalLabSequence (bukan booking NumberKlinik lama)
          $seqYear = GlobalLabSequence::resolveYear(date('Y'));
          $count = (int) GlobalLabSequence::getCurrentNumber($seqYear);

          // Ambil jumlah baris dari request, default 10 jika tidak ada
          $jumlahBaris = $request->input('rows', 10);
          $jumlahBaris = max(1, min(1000, (int)$jumlahBaris)); // Batasi antara 1-1000

          DB::commit();

          $nextLabNumber = NomerLabSequence::peekNextNumber($seqYear);

          return Excel::download(
            new FormatHajiExport($count, $data->nama_haji, $data->tgl_haji, $jumlahBaris, $nextLabNumber),
            'Format-Data-Haji-' . $data->nama_haji . '-' . $data->tgl_haji . '.xlsx'
          );
      } catch (\Exception $e) {
          DB::rollBack();
          return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
      }
  }

  public function importHaji(Request $request, $id)
  {
    try {
      $request->validate([
        'file' => 'required|mimes:xlsx,xls',
        'customer_id' => 'nullable'
      ]);

      $import = new HajiImport();
      $data = Excel::toArray($import, $request->file('file'))[0];

      // Cek jika data kosong
      if (empty($data)) {
        return back()->with('error', 'File Excel kosong atau tidak valid!')
          ->with('haji_import_status', 'error');
      }

      DB::beginTransaction();

      $importedPasienIds = [];
      $importedSuccessCount = 0;
      $importedSkipCount = 0;
      $importedNewCount = 0;
      $importedExistingSystemNames = [];
      $importedSkippedHajiNames = [];
      // Import dari wizard step 3: hanya buat/update pasien, permohonan dibuat saat Simpan.
      $wizardImport = $request->filled('customer_id');
      $excelCols = $this->getHajiExcelColumnMap();
      $labAutoCursor = null;

      // Non-wizard: wajib parameter individu (session / pasien existing), tanpa master Paket Haji.
      $importJenisParameters = [];
      if (!$wizardImport) {
        $importJenisParameters = session('haji_parameters', []);
        if (session('haji_id') !== $id) {
          $importJenisParameters = [];
        }
        $importJenisParameters = $this->stripCompositePaketHajiFromParameters($importJenisParameters);
        if ($this->isHajiParametersEmpty($importJenisParameters)) {
          $importJenisParameters = $this->buildHajiParametersFromMajority($id);
        }
        if ($this->isHajiParametersEmpty($importJenisParameters)) {
          DB::rollBack();
          return back()
            ->with('error', 'Parameter pemeriksaan belum dipilih. Silakan pakai wizard: pilih parameter individu dulu (seperti pemeriksaan umum), lalu import pasien.')
            ->with('haji_import_status', 'error');
        }
      }

        foreach ($data as $row) {
            // Skip baris kosong agar import tidak gagal karena kolom tidak lengkap.
            $namaPasien = isset($row[$excelCols['nama']]) ? trim((string)$row[$excelCols['nama']]) : '';
            $nikPasien = isset($row[$excelCols['nik']]) ? trim((string)$row[$excelCols['nik']]) : '';
            $jenisKelamin = isset($row[$excelCols['kelamin']]) ? trim((string)$row[$excelCols['kelamin']]) : '';
            if ($namaPasien === '' && $jenisKelamin === '') {
              continue;
            }

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

            // Mapping kolom: No, Nama, NIK, Kelamin, Tempat Lahir, Tgl Lahir, Tgl Lahir (String), Alamat, Pekerjaan
            // + Nomor Lab / Nomor Spesimen jika setting manual klinik aktif.

            $tgl_lahir = isset($row[$excelCols['tgl_lahir']]) ? $row[$excelCols['tgl_lahir']] : null;
            $nomorLabManual = isset($excelCols['nomor_lab']) && isset($row[$excelCols['nomor_lab']])
              ? trim((string)$row[$excelCols['nomor_lab']]) : '';
            $nomorSpesimenManual = isset($excelCols['nomor_spesimen']) && isset($row[$excelCols['nomor_spesimen']])
              ? trim((string)$row[$excelCols['nomor_spesimen']]) : '';

            // Mengonversi tanggal lahir
            if (is_numeric($tgl_lahir)) {
                // Konversi dari format serial Excel ke DateTime
                $tgl_lahir = Date::excelToDateTimeObject($tgl_lahir)->format('Y-m-d');
            } else {
                try {
                    // Ubah bulan dari bahasa Indonesia ke bahasa Inggris
                    $tgl_lahir = $this->ubahBulanKeInggris($tgl_lahir, $bulanIndonesia);
                    // Konversi dari format teks DD MMMM YYYY ke Carbon instance
                    $tgl_lahir = Carbon::createFromFormat('d F Y', $tgl_lahir)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Jika format tanggal tidak valid, set ke null
                    $tgl_lahir = null;
                }
            }

            // Ambil jam dan nama petugas dari Excel untuk setiap step
            // Format: jam (HH:mm) dan nama petugas
            $jam_pendaftaran = !empty($row[16]) ? trim($row[16]) : null;
            $nama_petugas_pendaftaran = !empty($row[17]) ? trim($row[17]) : null;
            $jam_pengambil_sample = !empty($row[18]) ? trim($row[18]) : null;
            $nama_petugas_pengambil_sample = !empty($row[19]) ? trim($row[19]) : null;
            $jam_penerima_sampel = !empty($row[20]) ? trim($row[20]) : null;
            $nama_petugas_penerima_sampel = !empty($row[21]) ? trim($row[21]) : null;
            $jam_pengolah_sampel = !empty($row[22]) ? trim($row[22]) : null;
            $nama_petugas_pengolah_sampel = !empty($row[23]) ? trim($row[23]) : null;
            $jam_pemeriksa_sampel = !empty($row[24]) ? trim($row[24]) : null;
            $nama_petugas_pemeriksa_sampel = !empty($row[25]) ? trim($row[25]) : null;
            $jam_verifikasi = !empty($row[26]) ? trim($row[26]) : null;
            $nama_petugas_verifikasi = !empty($row[27]) ? trim($row[27]) : null;
            $jam_validasi = !empty($row[28]) ? trim($row[28]) : null;
            $nama_petugas_validasi = !empty($row[29]) ? trim($row[29]) : null;

            // Gunakan tanggal hari ini untuk semua step, jam dari Excel
            $tanggal_hari_ini = Carbon::now()->format('Y-m-d');

            // Fungsi helper untuk membuat datetime dari jam
            $createDateTime = function($jam, $defaultDate = null) use ($tanggal_hari_ini) {
                if (empty($jam)) {
                    return $defaultDate ?: Carbon::now()->format('Y-m-d H:i:s');
                }

                // Parse jam (format bisa HH:mm, H:mm, atau format Excel time)
                $jam = trim($jam);
                try {
                    // Coba parse sebagai format time Excel (numeric)
                    if (is_numeric($jam)) {
                        $timeValue = floatval($jam);
                        $hours = floor($timeValue * 24);
                        $minutes = floor(($timeValue * 24 - $hours) * 60);
                        $date = $defaultDate ? Carbon::parse($defaultDate) : Carbon::now();
                        return $date->setTime($hours, $minutes, 0)->format('Y-m-d H:i:s');
                    }

                    // Coba parse sebagai format string HH:mm atau H:mm
                    $parsedTime = Carbon::createFromFormat('H:i', $jam);
                    $date = $defaultDate ? Carbon::parse($defaultDate) : Carbon::now();
                    return $date->setTime($parsedTime->hour, $parsedTime->minute, 0)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // Jika parsing gagal, coba parse dengan format lain atau gunakan waktu sekarang
                    try {
                        $parsedTime = Carbon::parse($jam);
                        $date = $defaultDate ? Carbon::parse($defaultDate) : Carbon::now();
                        return $date->setTime($parsedTime->hour, $parsedTime->minute, $parsedTime->second)->format('Y-m-d H:i:s');
                    } catch (\Exception $e2) {
                        // Jika semua parsing gagal, gunakan waktu sekarang
                        return Carbon::now()->format('Y-m-d H:i:s');
                    }
                }
            };

            // Buat datetime untuk setiap step
            $tgl_registrasi = $createDateTime($jam_pendaftaran, $tanggal_hari_ini);

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

            // dd($tgl_lahir);


            // Prioritas cek pasien berdasarkan NIK jika NIK tersedia.
            // Fallback ke nama + tgl lahir + gender untuk data lama tanpa NIK.
            $pasien = null;
            if ($nikPasien !== '') {
              $pasien = Pasien::where('nik_pasien', $nikPasien)
                ->whereNull('deleted_at')
                ->first();
            }

            if (!$pasien) {
              $pasien = Pasien::where('nama_pasien', $namaPasien)
                ->where('tgllahir_pasien', $tgl_lahir)
                ->where('gender_pasien', $jenisKelamin)
                ->whereNull('deleted_at')
                ->first();
            }

            if (!$pasien) {
                $set_count = Pasien::nextNoRekamMedis();

                $new_pasien = new Pasien();
                $new_pasien->nourut_pasien = $set_count;
                $new_pasien->no_rekammedis_pasien = $set_count;
                $new_pasien->nama_pasien = $namaPasien; // Nama
                $new_pasien->nik_pasien = $nikPasien !== '' ? $nikPasien : null; // NIK (row[2])
                $new_pasien->tgllahir_pasien = $tgl_lahir;
                $new_pasien->alamat_pasien = $this->resolveAlamatPasienFromHajiExcelRow($row, $excelCols, $tgl_lahir);
                $new_pasien->gender_pasien = $jenisKelamin;
                $new_pasien->tmpt_lahir = isset($row[$excelCols['tempat_lahir']]) ? trim($row[$excelCols['tempat_lahir']]) : null;
                $new_pasien->pekerjaan = isset($row[$excelCols['pekerjaan']]) ? trim($row[$excelCols['pekerjaan']]) : null;
                $new_pasien->save();

                $id_pasien = $new_pasien->id_pasien; // Mengambil ID pasien yang baru disimpan
                $importedNewCount++;
                $isExistingSystemPasien = false;
              } else {
                // Update NIK jika ada dan belum terisi
                if ($pasien && $nikPasien !== '' && empty($pasien->nik_pasien)) {
                    $pasien->nik_pasien = $nikPasien;
                    $pasien->save();
                }

                $alamatBaru = $this->resolveAlamatPasienFromHajiExcelRow($row, $excelCols, $tgl_lahir);
                if ($pasien) {
                  $alamatLamaRaw = $pasien->alamat_pasien ?? '';
                  $alamatLamaValid = \Smt\Masterweb\Helpers\Smt::sanitizeAlamatPasien(
                    $alamatLamaRaw,
                    $pasien->tgllahir_pasien ?? $tgl_lahir
                  );
                  $alamatLamaIsDate = \Smt\Masterweb\Helpers\Smt::isAlamatPasienTanggalLahir(
                    $alamatLamaRaw,
                    $pasien->tgllahir_pasien ?? $tgl_lahir
                  );

                  // Hanya isi/ganti alamat dari field alamat pasien yang valid (bukan tanggal lahir).
                  if ($alamatBaru !== null && ($alamatLamaValid === null || $alamatLamaIsDate)) {
                    $pasien->alamat_pasien = $alamatBaru;
                    $pasien->save();
                  } elseif ($alamatLamaIsDate) {
                    // Bersihkan nilai alamat yang salah terisi tanggal lahir.
                    $pasien->alamat_pasien = null;
                    $pasien->save();
                  }
                }

                $id_pasien = $pasien->id_pasien; // Mengambil ID pasien yang ditemukan
                $importedExistingSystemNames[] = $namaPasien !== '' ? $namaPasien : ($pasien->nama_pasien ?? 'Pasien');
                $isExistingSystemPasien = true;
              }

              // Hindari duplikasi pada haji yang sama untuk pasien yang sama.
              $existingPermohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
                ->where('pasien_permohonan_uji_klinik', $id_pasien)
                ->whereNull('deleted_at')
                ->first();

              if ($existingPermohonan) {
                $importedSkipCount++;
                $importedSkippedHajiNames[] = $namaPasien !== '' ? $namaPasien : ($pasien->nama_pasien ?? 'Pasien');
                continue;
              }

              // Wizard step 3: import pasien saja, permohonan dibuat di storePasien (parameter step 2).
              if ($wizardImport) {
                $importedPasienIds[] = $id_pasien;
                $settingsImport = $excelCols['settings'];
                // Lab selalu unik di meta (mode manual: Excel duplikat/kosong → bump ke slot kosong)
                $nomorLabManual = $this->resolveUniqueHajiLabNumber(
                  (int) date('Y'),
                  $nomorLabManual,
                  $labAutoCursor
                );
                $this->storeHajiImportPasienMeta(
                  $id_pasien,
                  $nomorLabManual,
                  $nomorSpesimenManual,
                  $settingsImport,
                  !empty($isExistingSystemPasien)
                );
                $importedSuccessCount++;
                continue;
              }

              $importedPasienIds[] = $id_pasien;

              // Generate nomor sampel dari GlobalLabSequence (selaras klinik / storePasien)
              $seqAlloc = $this->allocateHajiKlinikSequenceNumber($tgl_registrasi);
              $set_count = (int) $seqAlloc['set_count'];
              $seqYear = (int) $seqAlloc['seq_year'];

              $totalHargaImport = 0;
              foreach ($importJenisParameters as $paramData) {
                if (!isset($paramData['pakets']) || !is_array($paramData['pakets'])) {
                  continue;
                }
                foreach ($paramData['pakets'] as $paketValue) {
                  $parts = explode('_', (string) $paketValue);
                  $totalHargaImport += isset($parts[1]) ? (int) $parts[1] : 0;
                }
              }

              // simpan permohonan uji klinik
              $permohonan_uji_klinik = new PermohonanUjiKlinik2();
              $permohonan_uji_klinik->tglregister_permohonan_uji_klinik= $tgl_registrasi;
              $permohonan_uji_klinik->pasien_permohonan_uji_klinik= $id_pasien;
              $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik= $years;
              $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik= $months;
              $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik= $days;
              $permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $totalHargaImport;
              $permohonan_uji_klinik->metode_pembayaran = 0;
              $permohonan_uji_klinik->status_pembayaran= 0;
              $permohonan_uji_klinik->status_permohonan_uji_klinik= 'ANALIS';
              $permohonan_uji_klinik->id_permohonan_uji_klinik_haji= $id;
              $permohonan_uji_klinik->is_haji= 1;
              $permohonan_uji_klinik->doctor_type = 'rujukan';
              $permohonan_uji_klinik->done_register = true;

              // Update mapping nomor amplop: dari 5 kolom menjadi 4 kolom
              $permohonan_uji_klinik->no_amplop_1= !empty($row[30]) ? $row[30] : null;
              $permohonan_uji_klinik->no_amplop_2= !empty($row[31]) ? $row[31] : null;
              $permohonan_uji_klinik->no_amplop_3= !empty($row[32]) ? $row[32] : null;
              $permohonan_uji_klinik->no_amplop_4= !empty($row[33]) ? $row[33] : null;
              $permohonan_uji_klinik->no_amplop_5= null; // Kolom ke-5 tidak ada lagi
              $this->applyHajiManualNumbersToPermohonan(
                $permohonan_uji_klinik,
                $set_count,
                $nomorLabManual,
                $nomorSpesimenManual,
                $excelCols['settings'],
                $seqYear,
                $labAutoCursor
              );
              $permohonan_uji_klinik->save();
              $assignedLab = (int) $this->normalizeHajiManualNumber($permohonan_uji_klinik->nomor_lab_manual);
              if ($assignedLab > 0) {
                NomerLabSequence::raiseLastNumberToAtLeast($assignedLab, (int) $seqYear);
                $labAutoCursor = $assignedLab;
              }
              $this->linkHajiKlinikSequenceDetail(
                $set_count,
                $seqYear,
                $permohonan_uji_klinik->id_permohonan_uji_klinik
              );

              $number_klinik = new NumberKlinik();
              $number_klinik->new_number = $set_count;
              $number_klinik->last_number = $set_count;
              $number_klinik->id_permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
              $number_klinik->id_haji = $id;
              $number_klinik->save();

              $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($permohonan_uji_klinik->id_permohonan_uji_klinik);

              // Simpan paket individu (sama seperti storePasien / pemeriksaan umum)
              foreach ($importJenisParameters as $paramData) {
                if (!isset($paramData['pakets']) || !is_array($paramData['pakets'])) {
                  continue;
                }
                foreach ($paramData['pakets'] as $paketValue) {
                  $parts = explode('_', (string) $paketValue);
                  $paketKlinikId = $parts[0] ?? '';
                  $harga = isset($parts[1]) ? (int) $parts[1] : 0;
                  if ($paketKlinikId === '') {
                    continue;
                  }

                  $data_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $paketKlinikId)->first();
                  if (!$data_paket
                    || HajiPaketHelper::isCompositePaketHajiName($data_paket->name_parameter_paket_klinik)
                    || HajiPaketHelper::isBillingVariantPaketName((string) $data_paket->name_parameter_paket_klinik)
                  ) {
                    continue;
                  }
                  if ($data_paket->parameterpaketjenisklinik->isEmpty()) {
                    continue;
                  }

                  $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();
                  $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
                  $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                  $permohonan_paket_klinik->parameter_paket_klinik = $data_paket->id_parameter_paket_klinik;
                  $permohonan_paket_klinik->parameter_jenis_klinik = $first_parameter_paket_jenis_klinik->parameter_jenis_klinik_id;
                  $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = 'P';
                  $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $harga;
                  $permohonan_paket_klinik->is_haji = 1;
                  $permohonan_paket_klinik->save();

                  foreach ($data_paket->parameterpaketjenisklinik as $value_parameterpaketjenisklinik) {
                    foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $value_parametersatuanpaketklinik) {
                      $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                      $pasien_gender = optional($item_permohonan_uji_klinik->pasien)->gender_pasien;

                      $jenis_bm_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;
                      $satuan_bm_id = BakuMutuPermohonanKlinikHelper::resolveHajiLookupSatuanId(
                        (string) $jenis_bm_id,
                        (string) $value_parametersatuanpaketklinik->parameter_satuan_klinik
                      );
                      $pasien_gender_lp = ($pasien_gender == 'male' || $pasien_gender == 'L') ? 'L' : 'P';
                      $item_parameter_by_baku_mutu = BakuMutuPermohonanKlinikHelper::resolveMatchedBakuMutu(
                        (string) $jenis_bm_id,
                        (string) $satuan_bm_id,
                        $pasien_gender_lp,
                        $pasien_umur,
                        1
                      );
                      if (!$item_parameter_by_baku_mutu) {
                        $item_parameter_by_baku_mutu = BakuMutuPermohonanKlinikHelper::resolveMatchedBakuMutu(
                          (string) $jenis_bm_id,
                          (string) $value_parametersatuanpaketklinik->parameter_satuan_klinik,
                          $pasien_gender_lp,
                          $pasien_umur,
                          0
                        );
                      }
                      if ($item_parameter_by_baku_mutu && !empty($item_parameter_by_baku_mutu->library_id)) {
                        $lib = \Smt\Masterweb\Models\Library::find($item_parameter_by_baku_mutu->library_id);
                        if ($lib) {
                          $item_parameter_by_baku_mutu->title_library = $lib->title_library ?? null;
                        }
                      }

                      $post_parameter = new PermohonanUjiParameterKlinik();
                      $post_parameter->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                      $post_parameter->permohonan_uji_paket_klinik = $permohonan_paket_klinik->id_permohonan_uji_paket_klinik;
                      $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
                      $post_parameter->parameter_paket_klinik = $permohonan_paket_klinik->parameter_paket_klinik;
                      $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
                      $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;
                      $post_parameter->method_permohonan_uji_parameter_klinik = \Smt\Masterweb\Helpers\Smt::resolveInitialMethodForParameter(
                        $value_parametersatuanpaketklinik->parametersatuanklinik,
                        1
                      );
                      $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
                      $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;
                      $post_parameter->keterangan_permohonan_uji_parameter_klinik = optional($value_parametersatuanpaketklinik->parametersatuanklinik)->ket_default_parameter_satuan_klinik;
                      $post_parameter->harga_permohonan_uji_parameter_klinik = optional($value_parametersatuanpaketklinik->parametersatuanklinik)->harga_satuan_parameter_satuan_klinik;
                      $post_parameter->is_haji = 1;

                      if ($item_parameter_by_baku_mutu && isset($item_parameter_by_baku_mutu->unit_id)) {
                        $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                        $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                        BakuMutuPermohonanKlinikHelper::applySnapshotToParameter(
                          $post_parameter,
                          $item_parameter_by_baku_mutu,
                          null,
                          BakuMutuPermohonanKlinikHelper::loadBakuMutuForParameter(
                            (string) $jenis_bm_id,
                            (string) $satuan_bm_id,
                            1
                          )
                        );
                      } else {
                        $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                        $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                      }

                      $post_parameter->save();

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
                }
              }

              $permohonan_uji_klinik->status_permohonan_uji_klinik = 'ANALIS';
              $permohonan_uji_klinik->save();


              // Simpan aktivitas awal saja (registrasi),
              // agar alur tidak langsung dianggap selesai/terverifikasi semua step.
              $registrationDate = $createDateTime($jam_pendaftaran, $tanggal_hari_ini);

              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_verification_activity = 1; // Pendaftaran / Registrasi
              $verificationActivitySample->start_date = $registrationDate;
              $verificationActivitySample->stop_date = Carbon::parse($registrationDate)->addMinutes(5)->format('Y-m-d H:i:s');
              $verificationActivitySample->nama_petugas = $nama_petugas_pendaftaran ?: 'Petugas';
              $verificationActivitySample->is_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
              $verificationActivitySample->is_haji = 1;
              $verificationActivitySample->is_done = 1;
              $verificationActivitySample->save();

              $data_haji = PermohonanUjiKlinikHaji::findOrFail($id);
              $data_haji->status_haji = 1; //sudah import

              $data_haji->save();
              // dd($data_haji);

                $importedSuccessCount++;
        }

        DB::commit();

        $importedPasienIds = array_values(array_unique(array_filter($importedPasienIds)));
        $importedExistingSystemNames = array_values(array_unique(array_filter($importedExistingSystemNames)));
        $importedSkippedHajiNames = array_values(array_unique(array_filter($importedSkippedHajiNames)));
        $customerId = $request->input('customer_id');

        $importMessageParts = [];
        if ($importedSuccessCount > 0) {
          $importMessageParts[] = $importedSuccessCount . ' data pasien berhasil diimport';
          if ($importedNewCount > 0 && count($importedExistingSystemNames) > 0) {
            $importMessageParts[] = $importedNewCount . ' pasien baru, ' . count($importedExistingSystemNames) . ' pasien sudah tersimpan di sistem';
          } elseif (count($importedExistingSystemNames) > 0 && $importedNewCount === 0) {
            $importMessageParts[] = count($importedExistingSystemNames) . ' pasien sudah tersimpan di sistem';
          }
        }
        if ($importedSkipCount > 0) {
          $skippedPreview = implode(', ', array_slice($importedSkippedHajiNames, 0, 5));
          if (count($importedSkippedHajiNames) > 5) {
            $skippedPreview .= ', ...';
          }
          $importMessageParts[] = $importedSkipCount . ' pasien sudah tersimpan pada rombongan ini'
            . ($skippedPreview !== '' ? ' (' . $skippedPreview . ')' : '');
        }
        if (empty($importMessageParts)) {
          $importMessageParts[] = 'Tidak ada data pasien baru yang dapat diimport';
        }

        $importMessage = implode('. ', $importMessageParts);
        if ($wizardImport) {
          $importMessage .= '. Klik Simpan untuk mendaftarkan permohonan dengan parameter yang dipilih.';
        } else {
          $importMessage .= '!';
        }

        if ($customerId) {
          session([
            'haji_customer_id' => $customerId,
            'haji_id' => $id,
          ]);

          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
            'step' => 3,
            'customer_id' => $customerId,
            'haji_id' => $id,
          ])->with('success', $importMessage)
            ->with('haji_import_status', $importedSuccessCount > 0 ? 'success' : ($importedSkipCount > 0 ? 'warning' : 'error'))
            ->with('haji_imported_pasien_ids', $importedPasienIds)
            ->with('haji_import_existing_names', $importedExistingSystemNames)
            ->with('haji_import_skipped_names', $importedSkippedHajiNames);
        }

        return back()->with('success', $importMessage)
          ->with('haji_import_status', $importedSuccessCount > 0 ? 'success' : ($importedSkipCount > 0 ? 'warning' : 'error'))
          ->with('haji_import_existing_names', $importedExistingSystemNames)
          ->with('haji_import_skipped_names', $importedSkippedHajiNames);
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->validator)->withInput()
          ->with('error', 'Validasi upload gagal. Pastikan file berformat .xlsx atau .xls')
          ->with('haji_import_status', 'error');
    } catch (Exception $e) {
        DB::rollBack();
        \Log::error('Error importing haji data: ' . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage())
          ->with('haji_import_status', 'error');
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

  public function getHaji(Request $request)
  {
      // Rombongan haji aktif (SoftDeletes) untuk dropdown filter
      $data = PermohonanUjiKlinikHaji::query()
        ->select('id_permohonan_uji_klinik_haji', 'nama_haji', 'tgl_haji')
        ->orderByDesc('tgl_haji')
        ->orderBy('nama_haji')
        ->get();

      return response()->json($data);
  }

  public function printAmplop($id){
    $data = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->first();
    $pasien = Pasien::where('id_pasien', $data->pasien_permohonan_uji_klinik)->first();
    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint.print_amplop_haji', [
      'data' => $data,
      'pasien' => $pasien,
    ]);
  }

  public function sortingNumberKlinik()
  {
    GlobalLabSequence::resequenceAutoOnlyForYear((int) date('Y'));
  }

  public function destroy($id)
  {
      DB::beginTransaction();

      try {
          $haji = PermohonanUjiKlinikHaji::where('id_permohonan_uji_klinik_haji', $id)->first();
          if (!$haji) {
              DB::rollBack();
              return response()->json([
                  'status' => false,
                  'pesan' => 'Data Haji tidak ditemukan atau sudah dihapus.',
              ], 404);
          }

          // Ambil semua pemeriksaan terkait (aktif)
          $permohonans = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
              ->whereNull('deleted_at')
              ->get([
                  'id_permohonan_uji_klinik',
                  'created_at',
                  'is_nomor_lab_manual',
                  'is_nomor_spesimen_manual',
                  'nomor_lab_manual',
                  'nomor_spesimen_manual',
              ]);

          $yearsNeedSync = [];
          $deletedAutoCount = 0;
          $deletedManualCount = 0;

          foreach ($permohonans as $puk) {
              $pukId = $puk->id_permohonan_uji_klinik;
              $isManual = GlobalLabSequence::isManualLinkedDetail('klinik', $pukId);
              $year = $puk->created_at ? (int) Carbon::parse($puk->created_at)->year : (int) date('Y');
              $yearsNeedSync[$year] = true;

              if ($isManual) {
                  $deletedManualCount++;
              } else {
                  $deletedAutoCount++;
              }

              $this->cascadeDeletePermohonanUjiKlinikById($pukId);
          }

          // NumberKlinik yang tertaut langsung ke master haji (bila ada)
          NumberKlinik::where('id_haji', $id)->delete();

          $hapusHaji = PermohonanUjiKlinikHaji::where('id_permohonan_uji_klinik_haji', $id)->delete();
          if (!$hapusHaji) {
              DB::rollBack();
              return response()->json([
                  'status' => false,
                  'pesan' => 'Data Haji gagal dihapus.',
              ], 400);
          }

          DB::commit();

          // Jangan resequence sisa nomor. Hanya sync counter (jangan turunkan last_number).
          if (empty($yearsNeedSync)) {
              $yearsNeedSync[(int) date('Y')] = true;
          }
          foreach (array_keys($yearsNeedSync) as $year) {
              try {
                  GlobalLabSequence::ensureSyncedWithManualSources((int) $year);
              } catch (\Throwable $e) {
                  // hapus tetap sukses
              }
          }

          $pesan = 'Data Haji berhasil dihapus';
          $pesan .= ' (' . $permohonans->count() . ' pemeriksaan';
          if ($deletedManualCount > 0) {
              $pesan .= ', ' . $deletedManualCount . ' nomor manual dihapus';
          }
          if ($deletedAutoCount > 0) {
              $pesan .= ', ' . $deletedAutoCount . ' nomor otomatis dihapus';
          }
          $pesan .= ').';

          return response()->json([
              'status' => true,
              'pesan' => $pesan,
              'deleted_permohonan' => $permohonans->count(),
              'deleted_manual' => $deletedManualCount,
              'deleted_auto' => $deletedAutoCount,
              'synced_years' => array_map('intval', array_keys($yearsNeedSync)),
          ], 200);
      } catch (\Throwable $e) {
          DB::rollBack();
          return response()->json([
              'status' => false,
              'pesan' => $e->getMessage(),
          ], 500);
      }
  }

  /**
   * Hapus cascade satu permohonan uji klinik + relasi:
   * parameter, sub-parameter, paket, payment, analis, pengambilan sample,
   * order TMS, verification, LHU, number klinik, sequence.
   * Sync counter spesimen/lab dilakukan SETELAH soft-delete data hidup.
   */
  protected function cascadeDeletePermohonanUjiKlinikById(string $pukId): void
  {
      $puk = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $pukId)
          ->whereNull('deleted_at')
          ->first(['id_permohonan_uji_klinik', 'created_at', 'tglregister_permohonan_uji_klinik']);

      $year = (int) date('Y');
      if ($puk) {
          $year = GlobalLabSequence::resolveYear(
              $puk->tglregister_permohonan_uji_klinik ?? $puk->created_at ?? date('Y')
          );
      }

      GlobalLabSequence::deleteByKlinikId($pukId, false);

      if (\Illuminate\Support\Facades\Schema::hasTable('tb_order_tms')) {
          $orderIds = OrderTms::where('id_permohonan_uji_klinik', $pukId)->pluck('id_order_tms');
          if ($orderIds->isNotEmpty()) {
              if (\Illuminate\Support\Facades\Schema::hasTable('tb_orderdetail_tms')) {
                  OrderDetailTms::whereIn('id_order_tms', $orderIds)->delete();
              }
              OrderTms::whereIn('id_order_tms', $orderIds)->delete();
          }
      }

      NumberKlinik::where('id_permohonan_uji_klinik', $pukId)->delete();
      PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $pukId)->delete();

      if (\Illuminate\Support\Facades\Schema::hasTable('tb_permohonan_uji_paket_klinik2')) {
          DB::table('tb_permohonan_uji_paket_klinik2')
              ->where('id_permohonan_uji_klinik', $pukId)
              ->whereNull('deleted_at')
              ->update(['deleted_at' => now()]);
      }

      $paramIds = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $pukId)
          ->pluck('id_permohonan_uji_parameter_klinik');

      if ($paramIds->isNotEmpty()) {
          $subParamIds = PermohonanUjiSubParameterKlinik::whereIn('permohonan_uji_parameter_klinik_id', $paramIds)
              ->pluck('id_permohonan_uji_sub_parameter_klinik');

          // History sub-parameter: FK = permohonan_uji_sub_parameter_klinik_id
          if ($subParamIds->isNotEmpty()
              && \Illuminate\Support\Facades\Schema::hasTable('tb_permohonan_uji_sub_parameter_klinik_history')
              && \Illuminate\Support\Facades\Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik_history', 'permohonan_uji_sub_parameter_klinik_id')
          ) {
              DB::table('tb_permohonan_uji_sub_parameter_klinik_history')
                  ->whereIn('permohonan_uji_sub_parameter_klinik_id', $subParamIds->all())
                  ->whereNull('deleted_at')
                  ->update(['deleted_at' => now()]);
          }

          PermohonanUjiSubParameterKlinik::whereIn('permohonan_uji_parameter_klinik_id', $paramIds)->delete();

          // History parameter: FK = permohonan_uji_parameter_klinik_id
          if (\Illuminate\Support\Facades\Schema::hasTable('tb_permohonan_uji_parameter_klinik_history')
              && \Illuminate\Support\Facades\Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'permohonan_uji_parameter_klinik_id')
          ) {
              DB::table('tb_permohonan_uji_parameter_klinik_history')
                  ->whereIn('permohonan_uji_parameter_klinik_id', $paramIds->all())
                  ->whereNull('deleted_at')
                  ->update(['deleted_at' => now()]);
          }
      }

      PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $pukId)->delete();

      if (\Illuminate\Support\Facades\Schema::hasTable('tb_permohonan_uji_parameter_klinik_2')) {
          DB::table('tb_permohonan_uji_parameter_klinik_2')
              ->where('id_permohonan_uji_klinik', $pukId)
              ->whereNull('deleted_at')
              ->update(['deleted_at' => now()]);
      }

      PermohonanUjiAnalisKlinik::where('permohonan_uji_klinik_id', $pukId)->delete();
      PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $pukId)->delete();
      PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $pukId)->delete();

      // Hard delete (tabel tanpa SoftDeletes)
      VerificationActivitySample::where('is_klinik', $pukId)->delete();

      if (\Illuminate\Support\Facades\Schema::hasTable('tb_lhu')) {
          \Smt\Masterweb\Models\LHU::where('permohonan_uji_klinik_id', $pukId)->delete();
      }

      PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $pukId)->delete();

      GlobalLabSequence::ensureSyncedWithManualSources($year);
      NomerLabSequence::syncLastNumberFromLiveData($year);
  }


  /**
   * Ambil list ID permohonan uji klinik (tb_permohonan_uji_klinik_2)
   * berdasarkan ID Haji untuk kebutuhan print massal hasil.
   *
   * @param  int  $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function getHasilIdsByHaji($id)
  {
      $ids = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
          ->orderBy('nourut_permohonan_uji_klinik', 'asc')
          ->pluck('id_permohonan_uji_klinik');

      return response()->json($ids);
  }

  /**
   * Print massal hasil permohonan uji klinik haji dalam 1 file PDF
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function printMassalHasil($id)
  {
      $haji = PermohonanUjiKlinikHaji::find($id);

      if (!$haji) {
          abort(404, 'Data Haji tidak ditemukan');
      }

      // Ambil semua permohonan uji klinik berdasarkan ID haji
      $permohonanIds = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
          ->where('status_permohonan_uji_klinik', 'SELESAI')
          ->orderBy('nourut_permohonan_uji_klinik', 'asc')
          ->pluck('id_permohonan_uji_klinik');

      if ($permohonanIds->isEmpty()) {
          abort(404, 'Tidak ada data hasil yang selesai untuk dicetak');
      }

      // Generate PDF dengan semua hasil - view akan memanggil method yang sama untuk setiap permohonan
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint.hasil-klinik-massal', [
          'permohonanIds' => $permohonanIds,
          'haji' => $haji,
      ]);

      $filename = 'Hasil_Massal_Haji_' . $haji->nama_haji . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.pdf';

      // TTE/BSrE overlay sementara dihilangkan dari kop hasil
      // return Smt::streamPdfWithBsreLastPageFooter($pdf, $filename);
      return $pdf->stream($filename);
  }

  /**
   * Create new haji flow - Step 1: Customer
   */
  public function createNew(Request $request)
  {
    $step = $request->get('step', 1);
    $customer_id = $request->get('customer_id');
    $haji_id = $request->get('haji_id');

    if ($step == 1) {
      // Step 1: Show customer selection
      $customers = Customer::orderBy('name_customer', 'asc')->get();
      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.create-new', [
        'step' => 1,
        'customers' => $customers
      ]);
    } elseif ($step == 2) {
      // Step 2: Show parameter selection
      if (!$customer_id || !$haji_id) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
          ->with('error', 'Data customer atau haji tidak ditemukan');
      }

      $customer = Customer::find($customer_id);
      if (!$customer) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
          ->with('error', 'Customer tidak ditemukan');
      }

      $parameter_jenis_klinik = ParameterJenisKlinik::with('pakets')->orderBy('created_at', 'asc')->get();
      $parameter_paket_extra = ParameterPaketExtra::with('parameterSubPaketExtra')->orderBy('created_at', 'asc')->get();

      $categoryLayouts = ParameterCategoryLayout::where('is_active', 1)
        ->orderBy('sort_order', 'asc')
        ->with(['categoryItems' => function ($query) {
          $query->with('parameterPaketKlinik')
            ->orderBy('row_position', 'asc')
            ->orderBy('column_position', 'asc')
            ->orderBy('sort_order', 'asc');
        }])
        ->get();

      // Form haji: sembunyikan varian BPJS/Klaim & komposit Paket Haji (sama seperti pemeriksaan umum yang dipilih user).
      $categoryLayouts->each(function ($category) {
        if (!$category->relationLoaded('categoryItems')) {
          return;
        }
        $category->setRelation(
          'categoryItems',
          $category->categoryItems->filter(function ($item) {
            $paket = $item->parameterPaketKlinik;
            if (!$paket) {
              return false;
            }
            $name = (string) $paket->name_parameter_paket_klinik;
            if (HajiPaketHelper::isCompositePaketHajiName($name)) {
              return false;
            }
            if (HajiPaketHelper::isBillingVariantPaketName($name)) {
              return false;
            }
            return true;
          })->values()
        );
      });

      $paket = [];
      $paketExtra = [];
      // Prefill dari session (pilihan user ATAU default mayoritas dari tambah pasien).
      if (session('haji_id') === $haji_id) {
        $sessionParams = session('haji_parameters', []);
        if (!$this->isHajiParametersEmpty($sessionParams)) {
          $paket = $this->buildHajiPaketArrayForView($sessionParams);
        }
        $paketExtraSession = session('haji_paket_extra', []);
        if (is_array($paketExtraSession)) {
          $paketExtra = array_keys($paketExtraSession);
        }
      }

      // Default checked: paket mayoritas dari batch ini / customer haji lain yang sudah ada.
      if (empty($paket)) {
        $majorityParams = $this->stripCompositePaketHajiFromParameters(
          $this->buildHajiParametersFromMajority($haji_id)
        );
        if (!$this->isHajiParametersEmpty($majorityParams)) {
          $paket = $this->buildHajiPaketArrayForView($majorityParams);
          session([
            'haji_parameters' => $majorityParams,
            'haji_id' => $haji_id,
            'haji_customer_id' => $customer_id,
            'haji_parameters_user_selected' => false,
          ]);
        }
      }

      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.create-new', [
        'step' => 2,
        'customer_id' => $customer_id,
        'customer_name' => $customer->name_customer,
        'haji_id' => $haji_id,
        'parameter_jenis_klinik' => $parameter_jenis_klinik,
        'parameter_paket_extra' => $parameter_paket_extra,
        'categoryLayouts' => $categoryLayouts,
        'paket' => $paket,
        'paket_extra' => $paketExtra,
        'hideBillingVariants' => true,
      ]);
    } elseif ($step == 3) {
      // Step 3: Show pasien form
      if (!$customer_id || !$haji_id) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
          ->with('error', 'Data customer atau haji tidak ditemukan');
      }

      $prefilledPasienIds = session('haji_imported_pasien_ids', []);
      if (!is_array($prefilledPasienIds)) {
        $prefilledPasienIds = [];
      }

      // Jangan prefill pasien yang sudah punya permohonan di haji ini (mis. setelah import Excel).
      $prefilledPasienIds = array_values(array_filter($prefilledPasienIds, function ($pasienId) use ($haji_id) {
        if (empty($pasienId)) {
          return false;
        }

        return !PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $haji_id)
          ->where('pasien_permohonan_uji_klinik', $pasienId)
          ->whereNull('deleted_at')
          ->exists();
      }));
      session()->forget('haji_imported_pasien_ids');

      $importPasienMeta = session('haji_import_pasien_meta', []);
      if (!is_array($importPasienMeta)) {
        $importPasienMeta = [];
      }

      $prefilledPasien = [];
      if (!empty($prefilledPasienIds)) {
        $pasienRows = Pasien::whereIn('id_pasien', $prefilledPasienIds)
          ->whereNull('deleted_at')
          ->get()
          ->keyBy('id_pasien');

        foreach ($prefilledPasienIds as $pasienId) {
          $pasien = $pasienRows->get($pasienId);
          if (!$pasien) {
            continue;
          }
          $meta = is_array($importPasienMeta[$pasienId] ?? null) ? $importPasienMeta[$pasienId] : [];
          $prefilledPasien[] = [
            'id_pasien' => $pasien->id_pasien,
            'nama_pasien' => $pasien->nama_pasien,
            'nik_pasien' => $pasien->nik_pasien,
            'tgllahir_pasien' => $pasien->tgllahir_pasien,
            'gender_pasien' => $pasien->gender_pasien,
            'alamat_pasien' => Smt::alamatLengkapPasien($pasien),
            'no_rekammedis_pasien' => $pasien->no_rekammedis_pasien,
            'nomor_lab_manual' => $meta['nomor_lab_manual'] ?? '',
            'nomor_spesimen_manual' => $meta['nomor_spesimen_manual'] ?? '',
            'is_existing_system' => !empty($meta['is_existing_system']),
          ];
        }
      }

      return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.create-new', [
        'step' => 3,
        'customer_id' => $customer_id,
        'haji_id' => $haji_id,
        'prefilled_pasien_ids' => $prefilledPasienIds,
        'prefilled_pasien' => $prefilledPasien,
        'count_pasien' => Pasien::nextNoRekamMedis(),
        'petugasPenerima' => $this->getPetugasAdministrasi(),
        'klinikNumberSettings' => KlinikNumberSettings::getSettings(),
        'nextLabNumber' => NomerLabSequence::peekNextNumber((int) date('Y')),
      ]);
    }
  }

  /**
   * Store customer and proceed to step 2
   */
  public function storeCustomer(Request $request)
  {
    DB::beginTransaction();
    try {
      $customer_id = $request->input('customer_id');
      $name_customer = $request->input('name_customer');

      // If customer_id is provided, use existing customer
      if ($customer_id) {
        $customer = Customer::find($customer_id);
        if (!$customer) {
          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
            ->with('error', 'Customer tidak ditemukan');
        }
      } else {
        // Create new customer
        if (!$name_customer || !$request->input('address_customer')) {
          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
            ->with('error', 'Nama dan alamat customer wajib diisi');
        }

        $customer = new Customer();
        $customer->id_customer = Uuid::uuid4()->toString();
        $customer->name_customer = $name_customer;
        $customer->address_customer = $request->input('address_customer');
        $customer->email_customer = $request->input('email_customer');
        $customer->cp_customer = $request->input('cp_customer');

        $kecamatan = $request->input('kecamatan');
        if ($kecamatan != '0') {
          $customer->kecamatan_customer = $kecamatan;
        } else {
          $customer->kecamatan_customer = $request->input('kecamatan_other');
        }

        $customer->save();
        $customer_id = $customer->id_customer;
      }

      // Create haji record if not exists
      $haji = new PermohonanUjiKlinikHaji();
      $haji->nama_haji = $customer->name_customer; // Ambil nama dari customer
      $haji->tgl_haji = Carbon::now()->format('Y-m-d');
      $haji->kuota_haji = 1; // Will be updated when pasien is added
      $haji->status_haji = 0;
      $haji->save();

      // Reset pilihan parameter agar step 2 dimulai kosong
      session([
        'haji_dokter_pengirim' => $request->input('nama_dokter_pengirim_permohonan_uji_klinik'),
        'haji_customer_id' => $customer_id,
        'haji_id' => $haji->id_permohonan_uji_klinik_haji,
        'haji_parameters' => [],
        'haji_paket_extra' => [],
        'haji_parameters_user_selected' => false,
      ]);
      session()->forget(['haji_imported_pasien_ids', 'haji_import_pasien_meta']);

      DB::commit();

      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
        'step' => 2,
        'customer_id' => $customer_id,
        'haji_id' => $haji->id_permohonan_uji_klinik_haji
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
        ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
  }

  /**
   * Store parameter and proceed to step 3
   */
  public function storeParameter(Request $request)
  {
    $customer_id = $request->input('customer_id');
    $haji_id = $request->input('haji_id');

    if (!$customer_id || !$haji_id) {
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
        ->with('error', 'Data customer atau haji tidak ditemukan');
    }

    $userHasSelection = !$this->isHajiParametersEmpty($request->input('jenis_parameters', []));
    $parameters = $request->input('jenis_parameters', []);
    $paketExtra = $request->input('paket_extra', []);

    // Sama seperti pemeriksaan umum: wajib pilih minimal satu parameter individu.
    if (!$userHasSelection) {
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
        'step' => 2,
        'customer_id' => $customer_id,
        'haji_id' => $haji_id,
      ])->with('error', 'Silakan pilih minimal satu parameter pemeriksaan.');
    }

    // Abaikan master komposit "Paket Haji" jika masih ikut terkirim.
    $parameters = $this->stripCompositePaketHajiFromParameters($parameters);
    if ($this->isHajiParametersEmpty($parameters)) {
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
        'step' => 2,
        'customer_id' => $customer_id,
        'haji_id' => $haji_id,
      ])->with('error', 'Silakan pilih parameter pemeriksaan individu (bukan Paket Haji).');
    }

    // Store parameter selection in session for later use
    session([
      'haji_customer_id' => $customer_id,
      'haji_id' => $haji_id,
      'haji_parameters' => $parameters,
      'haji_paket_extra' => $paketExtra,
      'haji_parameters_user_selected' => true,
    ]);

    return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
      'step' => 3,
      'customer_id' => $customer_id,
      'haji_id' => $haji_id
    ]);
  }

  /**
   * Store pasien and complete the flow
   */
  public function storePasien(Request $request)
  {
    DB::beginTransaction();
    try {
      $customer_id = $request->input('customer_id');
      $haji_id = $request->input('haji_id');
      $parameters = $this->resolveStoredHajiParameters($request);
      $parameters = $this->stripCompositePaketHajiFromParameters($parameters);

      if ($this->isHajiParametersEmpty($parameters)) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
          'step' => 2,
          'customer_id' => $customer_id,
          'haji_id' => $haji_id,
        ])->with('error', 'Parameter pemeriksaan belum dipilih. Silakan pilih parameter seperti pemeriksaan umum.');
      }

      $petugasRegistrasi = trim((string) $request->input('petugas_penerima', ''));
      if ($petugasRegistrasi === '') {
        $petugasRegistrasi = auth()->user()->name ?? 'Petugas';
      }

      $modePengambilan = trim((string) $request->input('mode_pengambilan_sampel', ''));
      $allowedModes = ['diambil_lab', 'dibawa_pelanggan', 'diambil_lokasi_rumah'];
      if ($modePengambilan === '' || !in_array($modePengambilan, $allowedModes, true)) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
          'step' => 3,
          'customer_id' => $customer_id,
          'haji_id' => $haji_id
        ])->withInput()->with('error', 'Mode pengambilan sampel wajib dipilih sebelum simpan.');
      }

      $biayaPengambilan = null;
      if ($modePengambilan === 'diambil_lokasi_rumah') {
        $biayaRaw = $request->input('biaya_pengambilan_sampel');
        if ($biayaRaw === null || $biayaRaw === '') {
          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
            'step' => 3,
            'customer_id' => $customer_id,
            'haji_id' => $haji_id
          ])->withInput()->with('error', 'Biaya pengambilan sampel wajib diisi.');
        }
        $biayaPengambilan = (int) $biayaRaw;
        if ($biayaPengambilan < 0) {
          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
            'step' => 3,
            'customer_id' => $customer_id,
            'haji_id' => $haji_id
          ])->withInput()->with('error', 'Biaya pengambilan sampel tidak valid.');
        }
      }
      
      $paket_extra = $this->resolveStoredHajiPaketExtra($request);
      if (!$customer_id || !$haji_id) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new')
          ->with('error', 'Data customer atau haji tidak ditemukan');
      }



      // Prepare list of pasien to process
      $pasien_list = [];
      $numberSettings = KlinikNumberSettings::getSettings();
      $requireNomorLab = (bool) ($numberSettings->is_nomor_lab_manual ?? false);
      $requireNomorSample = (bool) ($numberSettings->is_nomor_spesimen_manual ?? false);
      $pasienManualNumbers = $request->input('pasien_manual_numbers', []);
      if (!is_array($pasienManualNumbers)) {
        $pasienManualNumbers = [];
      }
      $importMetaSession = session('haji_import_pasien_meta', []);
      if (!is_array($importMetaSession)) {
        $importMetaSession = [];
      }

      // Check if pasien_search is provided (from select2 - multiple pasien from search)
      $pasien_search = $request->input('pasien_search', []);

      if (!empty($pasien_search) && is_array($pasien_search)) {
        // Mode: Cari dari Sistem (multiple pasien)
        foreach ($pasien_search as $pasien_id) {
          $pasien = Pasien::where('id_pasien', $pasien_id)
            ->whereNull('deleted_at')
            ->first();

          if (!$pasien) {
            return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
              'step' => 3,
              'customer_id' => $customer_id,
              'haji_id' => $haji_id
            ])->with('error', 'Salah satu pasien tidak ditemukan');
          }

          $manualFromRequest = is_array($pasienManualNumbers[$pasien_id] ?? null)
            ? $pasienManualNumbers[$pasien_id]
            : [];
          $manualFromImport = is_array($importMetaSession[$pasien_id] ?? null)
            ? $importMetaSession[$pasien_id]
            : [];
          $nomorLab = $this->normalizeHajiManualNumber(
            $manualFromRequest['nomor_lab_manual'] ?? ($manualFromImport['nomor_lab_manual'] ?? null)
          );
          $nomorSample = $this->normalizeHajiManualNumber(
            $manualFromRequest['nomor_spesimen_manual'] ?? ($manualFromImport['nomor_spesimen_manual'] ?? null)
          );

          if ($requireNomorLab && $nomorLab === '') {
            return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
              'step' => 3,
              'customer_id' => $customer_id,
              'haji_id' => $haji_id
            ])->withInput()->with('error', 'Nomor lab wajib diisi untuk pasien ' . ($pasien->nama_pasien ?? ''));
          }
          if ($requireNomorSample && $nomorSample === '') {
            return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
              'step' => 3,
              'customer_id' => $customer_id,
              'haji_id' => $haji_id
            ])->withInput()->with('error', 'Nomor sample wajib diisi untuk pasien ' . ($pasien->nama_pasien ?? ''));
          }

          $pasien_list[] = [
            'id_pasien' => $pasien->id_pasien,
            'tgllahir_pasien' => $pasien->tgllahir_pasien,
            'gender_pasien' => $pasien->gender_pasien,
            'nomor_lab_manual' => $nomorLab,
            'nomor_spesimen_manual' => $nomorSample,
          ];
        }
      } else {
        // Mode: Buat Baru (multiple pasien)
        $pasien_data = $request->input('pasien', []);

        if (empty($pasien_data)) {
          return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
            'step' => 3,
            'customer_id' => $customer_id,
            'haji_id' => $haji_id
          ])->with('error', 'Silakan tambah minimal satu pasien');
        }

        // Validate each pasien
        foreach ($pasien_data as $index => $pasien_item) {
          $tgllahir = $this->normalizePasienTglLahir($pasien_item['tgllahir_pasien'] ?? null);
          $nomorLab = $this->normalizeHajiManualNumber($pasien_item['nomor_lab_manual'] ?? null);
          $nomorSample = $this->normalizeHajiManualNumber($pasien_item['nomor_spesimen_manual'] ?? null);

          $rules = [
            'nama_pasien' => 'required',
            'tgllahir_pasien' => 'required|date',
            'gender_pasien' => 'required|in:L,P',
          ];
          if ($requireNomorLab) {
            $rules['nomor_lab_manual'] = 'required';
          }
          if ($requireNomorSample) {
            $rules['nomor_spesimen_manual'] = 'required';
          }

          $validator = Validator::make(array_merge($pasien_item, [
            'tgllahir_pasien' => $tgllahir,
            'nomor_lab_manual' => $nomorLab,
            'nomor_spesimen_manual' => $nomorSample,
          ]), $rules, [
            'nomor_lab_manual.required' => 'Nomor lab wajib diisi',
            'nomor_spesimen_manual.required' => 'Nomor sample wajib diisi',
          ]);

          if ($validator->fails()) {
            return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
              'step' => 3,
              'customer_id' => $customer_id,
              'haji_id' => $haji_id
            ])->with('error', 'Data pasien #' . ($index + 1) . ' tidak valid: ' . $validator->errors()->first());
          }

          $wilayah_id = null;
          if (!empty($pasien_item['desa_pasien'])) {
            $wilayah_id = $pasien_item['desa_pasien'];
          } elseif (!empty($pasien_item['kecamatan_pasien'])) {
            $wilayah_id = $pasien_item['kecamatan_pasien'];
          } elseif (!empty($pasien_item['kabupaten_pasien'])) {
            $wilayah_id = $pasien_item['kabupaten_pasien'];
          } elseif (!empty($pasien_item['provinsi_pasien'])) {
            $wilayah_id = $pasien_item['provinsi_pasien'];
          }

          // Cari atau buat pasien
          $pasien = Pasien::where('nama_pasien', $pasien_item['nama_pasien'])
            ->where('tgllahir_pasien', $tgllahir)
            ->where('gender_pasien', $pasien_item['gender_pasien'])
            ->whereNull('deleted_at')
            ->first();

          if (!$pasien) {
            $set_count = Pasien::nextNoRekamMedis();

            $pasien = new Pasien();
            $pasien->nourut_pasien = $set_count;
            $pasien->no_rekammedis_pasien = $set_count;
            $pasien->nik_pasien = $pasien_item['nik_pasien'] ?? null;
            $pasien->nama_pasien = $pasien_item['nama_pasien'];
            $pasien->tgllahir_pasien = $tgllahir;
            $pasien->tmpt_lahir = $pasien_item['tmpt_lahir'] ?? null;
            $pasien->pekerjaan = $pasien_item['pekerjaan'] ?? null;
            $pasien->phone_pasien = $pasien_item['phone_pasien'] ?? null;
            $pasien->wilayah_id = $wilayah_id;
            $pasien->alamat_pasien = \Smt\Masterweb\Helpers\Smt::sanitizeAlamatPasien(
              $pasien_item['alamat_pasien'] ?? '',
              $tgllahir
            );
            $pasien->gender_pasien = $pasien_item['gender_pasien'];
            $pasien->save();
          }

          $pasien_list[] = [
            'id_pasien' => $pasien->id_pasien,
            'tgllahir_pasien' => $pasien->tgllahir_pasien,
            'gender_pasien' => $pasien->gender_pasien,
            'nomor_lab_manual' => $nomorLab,
            'nomor_spesimen_manual' => $nomorSample,
          ];
        }
      }

    

      // Validate that at least one pasien is provided
      if (empty($pasien_list)) {
        return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
          'step' => 3,
          'customer_id' => $customer_id,
          'haji_id' => $haji_id
        ])->with('error', 'Silakan pilih atau tambah minimal satu pasien');
      }

      // Process each pasien
      $success_count = 0;
      $skip_count = 0;
      $labAutoCursor = null;

     
      foreach ($pasien_list as $pasien_item) {
        $id_pasien = $pasien_item['id_pasien'];
        $tgllahir_pasien = $pasien_item['tgllahir_pasien'];
        $gender_pasien = $pasien_item['gender_pasien'];

        // Hindari duplikasi pasien di haji yang sama.
        $existingPermohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $haji_id)
          ->where('pasien_permohonan_uji_klinik', $id_pasien)
          ->whereNull('deleted_at')
          ->first();

        if ($existingPermohonan) {
          $skip_count++;
          continue;
        }

        // Calculate age
        $birthdate = Carbon::parse($tgllahir_pasien);
        $now = Carbon::now();
        $years = $birthdate->diffInYears($now);
        $months = $birthdate->copy()->addYears($years)->diffInMonths($now);
        $days = $birthdate->copy()->addYears($years)->addMonths($months)->diffInDays($now);

        // Nomor sampel/urut dari GlobalLabSequence (bukan NumberKlinik / count lama)
        $registerNow = Carbon::now();
        $seqAlloc = $this->allocateHajiKlinikSequenceNumber($registerNow);
        $set_count = (int) $seqAlloc['set_count'];
        $seqYear = (int) $seqAlloc['seq_year'];

        $importMeta = session('haji_import_pasien_meta', []);
        $pasienMeta = is_array($importMeta) ? ($importMeta[$id_pasien] ?? []) : [];
        $nomorLabManual = $this->normalizeHajiManualNumber(
          $pasien_item['nomor_lab_manual'] ?? ($pasienMeta['nomor_lab_manual'] ?? null)
        );
        $nomorSpesimenManual = $this->normalizeHajiManualNumber(
          $pasien_item['nomor_spesimen_manual'] ?? ($pasienMeta['nomor_spesimen_manual'] ?? null)
        );

        // Spesimen otomatis: abaikan Excel/form. Lab otomatis: tetap dari urutan gabungan kesmas+klinik.
        if (!(bool) ($numberSettings->is_nomor_spesimen_manual ?? false)) {
          $nomorSpesimenManual = '';
        }
        if (!(bool) ($numberSettings->is_nomor_lab_manual ?? false) && $nomorLabManual === '') {
          $nomorLabManual = (string) $this->resolveNextHajiLabNumber((int) $seqYear, $labAutoCursor);
        }

        // Create permohonan uji klinik
        $permohonan_uji_klinik = new PermohonanUjiKlinik2();
        $this->applyHajiManualNumbersToPermohonan(
          $permohonan_uji_klinik,
          $set_count,
          $nomorLabManual !== '' ? $nomorLabManual : null,
          $nomorSpesimenManual !== '' ? $nomorSpesimenManual : null,
          $numberSettings,
          $seqYear,
          $labAutoCursor
        );
        $permohonan_uji_klinik->tglregister_permohonan_uji_klinik = $registerNow;
        $permohonan_uji_klinik->pasien_permohonan_uji_klinik = $id_pasien;
        $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik = $years;
        $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik = $months;
        $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik = $days;
        $permohonan_uji_klinik->total_harga_permohonan_uji_klinik = 0; // Will be calculated
        $permohonan_uji_klinik->metode_pembayaran = 0;
        $permohonan_uji_klinik->status_pembayaran = 0;
        $permohonan_uji_klinik->status_permohonan_uji_klinik = 'ANALIS';
        $permohonan_uji_klinik->id_permohonan_uji_klinik_haji = $haji_id;
        $permohonan_uji_klinik->is_haji = 1;
        $permohonan_uji_klinik->doctor_type = 'rujukan';
        $permohonan_uji_klinik->done_register = true; // Set done_register = true karena sudah melalui semua step
        // Set Dokter Pengirim from session
        $permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik = session('haji_dokter_pengirim');
        $permohonan_uji_klinik->mode_pengambilan_sampel = $modePengambilan;
        $permohonan_uji_klinik->biaya_pengambilan_sampel = $biayaPengambilan;
        $permohonan_uji_klinik->save();

        $assignedLab = (int) $this->normalizeHajiManualNumber($permohonan_uji_klinik->nomor_lab_manual);
        if ($assignedLab > 0) {
          NomerLabSequence::raiseLastNumberToAtLeast($assignedLab, (int) $seqYear);
          $labAutoCursor = $assignedLab;
        }

        // Calculate total harga
        $total_harga = 0;


        // Process parameters
        if ($parameters && count($parameters) > 0) {
          foreach ($parameters as $paket_id => $data) {
            if (isset($data['pakets']) && is_array($data['pakets'])) {
              foreach ($data['pakets'] as $paket_value) {
                $parts = explode('_', $paket_value);
                $paket_klinik_id = $parts[0];
                $harga = isset($parts[1]) ? (int) $parts[1] : 0;

                $data_paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $paket_klinik_id)->first();
                if ($data_paket && (
                  HajiPaketHelper::isCompositePaketHajiName($data_paket->name_parameter_paket_klinik)
                  || HajiPaketHelper::isBillingVariantPaketName((string) $data_paket->name_parameter_paket_klinik)
                )) {
                  // Alur baru: jangan simpan master komposit / varian BPJS-Klaim otomatis.
                  continue;
                }
                if ($data_paket && $data_paket->parameterpaketjenisklinik->isNotEmpty()) {
                  $first_parameter_paket_jenis_klinik = $data_paket->parameterpaketjenisklinik->first();

                  $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
                  $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                  $permohonan_paket_klinik->parameter_paket_klinik = $paket_klinik_id;
                  $permohonan_paket_klinik->parameter_jenis_klinik = $first_parameter_paket_jenis_klinik->parameter_jenis_klinik_id;
                  $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = "P";
                  $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $harga;
                  $permohonan_paket_klinik->is_haji = 1;
                  $permohonan_paket_klinik->save();

                  $total_harga += $harga;

                  // Save parameters
                  foreach ($data_paket->parameterpaketjenisklinik as $value_parameterpaketjenisklinik) {
                    foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $value_parametersatuanpaketklinik) {
                      $pasien_umur = $years;
                      $pasien_gender = $gender_pasien;

                      $jenis_bm_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;
                      $satuan_bm_id = BakuMutuPermohonanKlinikHelper::resolveHajiLookupSatuanId(
                        (string) $jenis_bm_id,
                        (string) $value_parametersatuanpaketklinik->parameter_satuan_klinik
                      );
                      $pasien_gender_lp = ($pasien_gender == "male" || $pasien_gender == "L") ? "L" : "P";
                      $check_parameter_by_baku_mutu = BakuMutuPermohonanKlinikHelper::resolveMatchedBakuMutu(
                        (string) $jenis_bm_id,
                        (string) $satuan_bm_id,
                        $pasien_gender_lp,
                        $pasien_umur,
                        1
                      );
                      if (!$check_parameter_by_baku_mutu) {
                        $check_parameter_by_baku_mutu = BakuMutuPermohonanKlinikHelper::resolveMatchedBakuMutu(
                          (string) $jenis_bm_id,
                          (string) $value_parametersatuanpaketklinik->parameter_satuan_klinik,
                          $pasien_gender_lp,
                          $pasien_umur,
                          0
                        );
                      }
                      if ($check_parameter_by_baku_mutu && !empty($check_parameter_by_baku_mutu->library_id)) {
                        $lib = \Smt\Masterweb\Models\Library::find($check_parameter_by_baku_mutu->library_id);
                        if ($lib) {
                          $check_parameter_by_baku_mutu->title_library = $lib->title_library ?? null;
                        }
                      }

                      $post_parameter = new PermohonanUjiParameterKlinik();
                      $post_parameter->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
                      $post_parameter->permohonan_uji_paket_klinik = $permohonan_paket_klinik->id_permohonan_uji_paket_klinik;
                      $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
                      $post_parameter->parameter_paket_klinik = $permohonan_paket_klinik->parameter_paket_klinik;
                      $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
                      $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;
                      $parameter_satuan_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik;
                      $post_parameter->method_permohonan_uji_parameter_klinik = \Smt\Masterweb\Helpers\Smt::resolveInitialMethodForParameter(
                        $parameter_satuan_klinik,
                        $permohonan_uji_klinik
                      );
                      $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort ?? 0;
                      $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting ?? 0;
                      $post_parameter->keterangan_permohonan_uji_parameter_klinik = ($parameter_satuan_klinik && isset($parameter_satuan_klinik->ket_default_parameter_satuan_klinik)) ? $parameter_satuan_klinik->ket_default_parameter_satuan_klinik : '';
                      $post_parameter->harga_permohonan_uji_parameter_klinik = ($parameter_satuan_klinik && isset($parameter_satuan_klinik->harga_satuan_parameter_satuan_klinik)) ? $parameter_satuan_klinik->harga_satuan_parameter_satuan_klinik : 0;
                      $post_parameter->is_haji = 1;

                      if ($check_parameter_by_baku_mutu && isset($check_parameter_by_baku_mutu->unit_id)) {
                        $post_parameter->satuan_permohonan_uji_parameter_klinik = $check_parameter_by_baku_mutu->unit_id;
                        $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $check_parameter_by_baku_mutu->id_baku_mutu;
                        BakuMutuPermohonanKlinikHelper::applySnapshotToParameter(
                          $post_parameter,
                          $check_parameter_by_baku_mutu,
                          null,
                          BakuMutuPermohonanKlinikHelper::loadBakuMutuForParameter(
                            (string) $jenis_bm_id,
                            (string) $satuan_bm_id,
                            1
                          )
                        );
                      }

                      $post_parameter->save();

                      // Handle sub parameters
                      $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)->get();
                      if (count($data_parameter_subsatuan) > 0) {
                        foreach ($data_parameter_subsatuan as $value_parameter_subsatuan) {
                          $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                          $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                          $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                          if ($check_parameter_by_baku_mutu && isset($check_parameter_by_baku_mutu->id_baku_mutu)) {
                            $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $check_parameter_by_baku_mutu->id_baku_mutu)
                              ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                              ->first();

                            if ($item_parameter_subsatuan_by_baku_mutu) {
                              $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik ?? null;
                              $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik ?? null;
                            }
                          }

                          $post_parameter_subsatuan->save();
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }

        // Process paket extra
        if (!empty($paket_extra)) {
          foreach ($paket_extra as $extra_id => $extra_value) {
            $parts = explode('_', $extra_value);
            $id_parameter_paket_extra = $parts[0];
            $harga_extra = isset($parts[1]) ? $parts[1] : 0;

            $data_extra = ParameterPaketExtra::find($id_parameter_paket_extra);
            if ($data_extra) {
              $permohonan_paket_klinik = new PermohonanUjiPaketKlinik();
              $permohonan_paket_klinik->permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
              $permohonan_paket_klinik->parameter_paket_extra = $id_parameter_paket_extra;
              $permohonan_paket_klinik->type_permohonan_uji_paket_klinik = "EP";
              $permohonan_paket_klinik->harga_permohonan_uji_paket_klinik = $harga_extra;
              $permohonan_paket_klinik->is_haji = 1;
              $permohonan_paket_klinik->save();

              $total_harga += $harga_extra;
            }
          }
        }

        // Update total harga
        $permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $total_harga;
        $permohonan_uji_klinik->save();

        // Update number klinik + tautkan global sequence detail
        $number_klinik = new NumberKlinik();
        $number_klinik->new_number = $set_count;
        $number_klinik->last_number = $set_count;
        $number_klinik->id_permohonan_uji_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
        $number_klinik->id_haji = $haji_id;
        $number_klinik->save();
        $this->linkHajiKlinikSequenceDetail(
          $set_count,
          $seqYear,
          $permohonan_uji_klinik->id_permohonan_uji_klinik
        );

        // Pada saat pendaftaran klinik baru, naikkan number_sampling_success +1
        // (walaupun belum ada pengambilan sampel atau status 'berhasil')
        try {
          // Jumlah pendaftaran klinik pada tahun berjalan (setelah tersimpan termasuk current)
          $newCount = PermohonanUjiKlinik2::where(DB::raw('YEAR(created_at)'), '=', date('Y'))->count();

          // Cari record resampling=0 (pengambilan awal). Jika belum ada, buat placeholder.
          $firstSampling = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $permohonan_uji_klinik->id_permohonan_uji_klinik)
            ->where('resampling', 0)
            ->orderBy('created_at', 'asc')
            ->first();

          if (!$firstSampling) {
            $firstSampling = new PengambilanSampleKlinik();
            $firstSampling->id_pengambilan_sample_klinik = Uuid::uuid4()->toString();
            $firstSampling->permohonan_uji_klinik_id = $permohonan_uji_klinik->id_permohonan_uji_klinik;
            $firstSampling->resampling = 0;
            $firstSampling->pasien_permohonan_uji_klinik = $permohonan_uji_klinik->pasien_permohonan_uji_klinik ?? null;
            $firstSampling->petugas_name = $petugasRegistrasi;
            $firstSampling->status_sampling = $firstSampling->status_sampling ?? null; // belum ada status
          }
          // Set nilai akumulasi pada placeholder
          $firstSampling->number_sampling_success = $newCount;
          $firstSampling->save();

          // Sinkronkan ke seluruh record terkait permohonan yang sama
          PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $permohonan_uji_klinik->id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->update(['number_sampling_success' => $newCount]);
        } catch (\Exception $e) {
          // Abaikan jika terjadi error agar pendaftaran tetap lanjut
        }

        // Simpan ke verifikasi (VerificationActivitySample untuk step 1: Pendaftaran/Registrasi)
        $startDate = Carbon::parse($permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('Y-m-d H:i:s');
        if ($permohonan_uji_klinik->tglregister_permohonan_uji_klinik) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_verification_activity = 1; // Pendaftaran / Registrasi
          $verificationActivitySample->start_date = $startDate;
          $verificationActivitySample->stop_date = Carbon::parse($startDate)->addMinutes(5)->format('Y-m-d H:i:s');
          $verificationActivitySample->nama_petugas = $petugasRegistrasi;
          $verificationActivitySample->is_klinik = $permohonan_uji_klinik->id_permohonan_uji_klinik;
          $verificationActivitySample->is_done = 1;
          // Simpan ke database
          $verificationActivitySample->save();

          if ($modePengambilan === 'dibawa_pelanggan') {
            app(LaboratoriumPermohonanUjiKlinikManagement2::class)
              ->ensureDibawaPelangganPengambilSampleSkipped($permohonan_uji_klinik->id_permohonan_uji_klinik);
          }
        }

        $success_count++;
      }

      // Clear session
      session()->forget([
        'haji_customer_id',
        'haji_id',
        'haji_parameters',
        'haji_paket_extra',
        'haji_imported_pasien_ids',
        'haji_import_pasien_meta',
        'haji_parameters_user_selected',
      ]);

      DB::commit();

      if ($success_count > 1) {
        $message = $success_count . ' pasien berhasil ditambahkan!';
      } elseif ($success_count === 1) {
        $message = 'Pasien berhasil ditambahkan!';
      } else {
        $message = 'Tidak ada pasien baru yang ditambahkan.';
      }

      if ($skip_count > 0) {
        $message .= ' ' . $skip_count . ' pasien dilewati karena sudah terdaftar pada haji ini.';
      }

      return redirect()->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji_id)
        ->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
        'step' => 3,
        'customer_id' => $request->input('customer_id'),
        'haji_id' => $request->input('haji_id')
      ])->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
  }

  /**
   * Get customer detail by ID
   */
  public function getCustomerDetail(Request $request)
  {
    try {
      $customer_id = $request->input('customer_id');

      if (!$customer_id) {
        return response()->json([
          'status' => false,
          'pesan' => 'Customer ID tidak ditemukan'
        ], 400);
      }

      $customer = Customer::where('id_customer', $customer_id)
        ->whereNull('deleted_at')
        ->first();

      if (!$customer) {
        return response()->json([
          'status' => false,
          'pesan' => 'Customer tidak ditemukan'
        ], 404);
      }

      return response()->json([
        'status' => true,
        'data' => [
          'id_customer' => $customer->id_customer,
          'name_customer' => $customer->name_customer,
          'address_customer' => $customer->address_customer,
          'email_customer' => $customer->email_customer,
          'cp_customer' => $customer->cp_customer,
          'kecamatan_customer' => $customer->kecamatan_customer,
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'pesan' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get pasien detail by ID
   */
  public function getPasienDetail(Request $request)
  {
    try {
      $pasien_id = $request->input('pasien_id');

      if (!$pasien_id) {
        return response()->json([
          'status' => false,
          'pesan' => 'Pasien ID tidak ditemukan'
        ], 400);
      }

      $pasien = Pasien::where('id_pasien', $pasien_id)
        ->whereNull('deleted_at')
        ->first();

      if (!$pasien) {
        return response()->json([
          'status' => false,
          'pesan' => 'Pasien tidak ditemukan'
        ], 404);
      }

      return response()->json([
        'status' => true,
        'data' => [
          'id_pasien' => $pasien->id_pasien,
          'nama_pasien' => $pasien->nama_pasien,
          'nik_pasien' => $pasien->nik_pasien,
          'tgllahir_pasien' => $pasien->tgllahir_pasien,
          'gender_pasien' => $pasien->gender_pasien,
          'alamat_pasien' => Smt::alamatLengkapPasien($pasien),
          'no_rekammedis_pasien' => $pasien->no_rekammedis_pasien,
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'pesan' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Daftar Pasien berdasarkan haji_id
   */
  public function daftarPasien($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $klinikNumberSettings = KlinikNumberSettings::getSettings();

    $data = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->orderByNomerSpesimen('asc')
      ->get();

    $statusPengujianMap = $this->buildHajiStatusPengujianMap($data->pluck('id_permohonan_uji_klinik')->all());

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.daftar-pasien', [
      'haji' => $haji,
      'data' => $data,
      'klinikNumberSettings' => $klinikNumberSettings,
      'statusPengujianMap' => $statusPengujianMap,
    ]);
  }

  /**
   * Riwayat penggantian nomor spesimen/lab pasien dalam satu rombongan haji.
   */
  public function riwayatNomor($id, Request $request)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $ids = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->withTrashed()
      ->pluck('id_permohonan_uji_klinik')
      ->all();

    $history = collect();
    if (!empty($ids) && Schema::hasTable('tb_nomor_change_history')) {
      $query = NomorChangeHistory::query()
        ->with('creator')
        ->where('subject_type', 'klinik')
        ->whereIn('subject_id', $ids)
        ->orderByDesc('created_at');

      $search = trim((string) $request->get('q', ''));
      if ($search !== '') {
        $query->where(function ($q) use ($search) {
          $q->where('old_value', 'like', '%' . $search . '%')
            ->orWhere('new_value', 'like', '%' . $search . '%')
            ->orWhere('field_name', 'like', '%' . $search . '%');
        });
      }

      $history = $query->paginate(50)->appends($request->query());
    }

    $pasienById = collect();
    if (!empty($ids)) {
      $pasienById = PermohonanUjiKlinik2::withTrashed()
        ->whereIn('id_permohonan_uji_klinik', $ids)
        ->with('pasien')
        ->get()
        ->keyBy('id_permohonan_uji_klinik');
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.riwayat-nomor', [
      'haji' => $haji,
      'history' => $history,
      'pasienById' => $pasienById,
    ]);
  }

  /**
   * Form penerimaan sampel massal untuk pasien terpilih dalam satu rombongan haji.
   * Data penerimaan (catatan, volume, kualitas, jam, petugas) diisi sekali lalu diterapkan ke semua yang dipilih.
   */
  public function createPenerimaSampelMassal(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $selectedIds = array_values(array_filter((array) $request->input('selected_ids', [])));

    if (empty($selectedIds)) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Pilih minimal satu pasien untuk penerimaan massal.');
    }

    $pasienList = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereIn('id_permohonan_uji_klinik', $selectedIds)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($pasienList->isEmpty()) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Pasien terpilih tidak ditemukan pada rombongan ini.');
    }

    $statusPengujianMap = $this->buildHajiStatusPengujianMap($pasienList->pluck('id_permohonan_uji_klinik')->all());

    // Hanya pasien yang sedang di tahap penerimaan sampel (step 1+6 selesai, step 7 belum)
    $eligible = $pasienList->filter(function ($item) use ($statusPengujianMap) {
      $label = $statusPengujianMap[$item->id_permohonan_uji_klinik]['label'] ?? '';
      return $label === 'Penerimaan Sample';
    });

    if ($eligible->isEmpty()) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Tidak ada pasien terpilih yang siap penerimaan sampel. Status harus "Penerimaan Sample".');
    }

    $jenisSampelArray = $this->resolveJenisSampelUnionForMassal($eligible);
    $petugasPenerima = $this->getPetugasPenerimaKlinikForHaji();
    $klinikNumberSettings = KlinikNumberSettings::getSettings();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.penerima-sampel-massal', [
      'haji' => $haji,
      'pasienList' => $eligible->values(),
      'selectedIds' => $eligible->pluck('id_permohonan_uji_klinik')->values()->all(),
      'jenis_sampel_array' => $jenisSampelArray,
      'petugas_penerima_sampel' => $petugasPenerima,
      'klinikNumberSettings' => $klinikNumberSettings,
      'statusPengujianMap' => $statusPengujianMap,
    ]);
  }

  /**
   * Simpan penerimaan sampel massal: payload sama diterapkan ke setiap pasien terpilih.
   */
  public function storePenerimaSampelMassal(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $selectedIds = array_values(array_filter((array) $request->input('selected_ids', [])));

    if (empty($selectedIds)) {
      return response()->json(['status' => false, 'pesan' => 'Tidak ada pasien yang dipilih.'], 200);
    }

    if (empty($request->penerimaan_sampel)) {
      return response()->json(['status' => false, 'pesan' => 'Penerimaan sampel wajib diisi!'], 200);
    }

    if (empty($request->volume_sampel)) {
      return response()->json(['status' => false, 'pesan' => 'Volume sampel wajib diisi!'], 200);
    }

    if (empty($request->jam_penerima) || empty($request->nama_petugas_penerima)) {
      return response()->json(['status' => false, 'pesan' => 'Jam dan petugas penerima wajib diisi!'], 200);
    }

    $validIds = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereIn('id_permohonan_uji_klinik', $selectedIds)
      ->whereNull('deleted_at')
      ->pluck('id_permohonan_uji_klinik')
      ->all();

    if (empty($validIds)) {
      return response()->json(['status' => false, 'pesan' => 'Pasien terpilih tidak valid untuk rombongan ini.'], 200);
    }

    $statusMap = $this->buildHajiStatusPengujianMap($validIds);
    $eligibleIds = array_values(array_filter($validIds, function ($pid) use ($statusMap) {
      return ($statusMap[$pid]['label'] ?? '') === 'Penerimaan Sample';
    }));

    if (empty($eligibleIds)) {
      return response()->json([
        'status' => false,
        'pesan' => 'Tidak ada pasien yang siap penerimaan sampel (status harus "Penerimaan Sample").',
      ], 200);
    }

    $payload = $request->except(['selected_ids', '_method', '_token']);
    $klinikController = app(LaboratoriumPermohonanUjiKlinikManagement2::class);

    $sukses = 0;
    $gagal = [];

    foreach ($eligibleIds as $permohonanId) {
      try {
        // Harus POST agar $request->post('jam_penerima') / petugas terisi di storePenerimaSampel
        $subRequest = Request::create('/', 'POST', $payload);
        $subRequest->headers->set('Accept', 'application/json');
        $response = $klinikController->storePenerimaSampel($subRequest, $permohonanId);
        $body = json_decode($response->getContent(), true);

        if (!empty($body['status'])) {
          $sukses++;
        } else {
          $gagal[] = [
            'id' => $permohonanId,
            'pesan' => $body['pesan'] ?? 'Gagal menyimpan',
          ];
        }
      } catch (\Exception $e) {
        \Log::error('Error penerimaan massal haji: ' . $e->getMessage(), [
          'haji_id' => $haji->id_permohonan_uji_klinik_haji,
          'permohonan_id' => $permohonanId,
        ]);
        $gagal[] = [
          'id' => $permohonanId,
          'pesan' => $e->getMessage(),
        ];
      }
    }

    $skipped = count($validIds) - count($eligibleIds);
    $pesan = "Penerimaan massal selesai: {$sukses} berhasil";
    if (!empty($gagal)) {
      $pesan .= ', ' . count($gagal) . ' gagal';
    }
    if ($skipped > 0) {
      $pesan .= ", {$skipped} dilewati (bukan tahap penerimaan)";
    }
    $pesan .= '.';

    return response()->json([
      'status' => $sukses > 0,
      'pesan' => $pesan,
      'sukses' => $sukses,
      'gagal' => $gagal,
      'redirect' => route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id),
    ], 200);
  }

  /**
   * Gabungkan jenis sampel dari semua pasien terpilih (union, urutan tetap).
   *
   * @param  \Illuminate\Support\Collection  $pasienList
   * @return array<int, string>
   */
  private function resolveJenisSampelUnionForMassal($pasienList): array
  {
    $union = [];

    foreach ($pasienList as $item) {
      $jenis = $this->resolveJenisSampelForPermohonan($item);
      foreach ($jenis as $type) {
        $type = trim((string) $type);
        if ($type === '') {
          continue;
        }
        if (!in_array($type, $union, true)) {
          $union[] = $type;
        }
      }
    }

    return $union;
  }

  /**
   * Ambil daftar jenis sampel untuk satu permohonan (sama logika form penerima tunggal).
   *
   * @return array<int, string>
   */
  private function resolveJenisSampelForPermohonan(PermohonanUjiKlinik2 $item): array
  {
    $id = $item->id_permohonan_uji_klinik;
    $jenisSampel = '';

    $latestSampling = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id)
      ->where('status_sampling', 'Berhasil')
      ->whereNull('deleted_at')
      ->orderBy('created_at', 'desc')
      ->first();

    if ($latestSampling && !empty($latestSampling->jenis_sample)) {
      if (is_string($latestSampling->jenis_sample)) {
        $decoded = json_decode($latestSampling->jenis_sample, true);
        if (is_array($decoded) && !empty($decoded)) {
          $jenisSampel = implode(', ', $decoded);
        } else {
          $jenisSampel = $latestSampling->jenis_sample;
        }
      } elseif (is_array($latestSampling->jenis_sample)) {
        $jenisSampel = implode(', ', $latestSampling->jenis_sample);
      } else {
        $jenisSampel = (string) $latestSampling->jenis_sample;
      }
    } elseif ($item->jenis_sampel !== null) {
      $jenisSampel = $item->jenis_sampel;
    } else {
      $jenisSampelArray = Smt::getJenisSampelFromParameter($id, null);
      if (!empty($jenisSampelArray) && is_array($jenisSampelArray)) {
        $jenisSampel = implode(', ', $jenisSampelArray);
      }
    }

    if ($jenisSampel === '' || $jenisSampel === null) {
      return [];
    }

    if (is_array($jenisSampel)) {
      return array_values(array_filter(array_map('trim', $jenisSampel)));
    }

    if (strpos((string) $jenisSampel, ',') !== false) {
      return array_values(array_filter(array_map('trim', explode(',', (string) $jenisSampel))));
    }

    return [trim((string) $jenisSampel)];
  }

  /**
   * Daftar petugas penerima sampel klinik (step 7), untuk form massal haji.
   *
   * @return array<int, string>
   */
  private function getPetugasPenerimaKlinikForHaji(): array
  {
    $petugasPenerima = [];

    $verificationActivity = VerificationActivity::find(7);
    if ($verificationActivity && !empty($verificationActivity->klinik) && $verificationActivity->klinik !== '-' && $verificationActivity->klinik !== 'NULL') {
      foreach (preg_split('/,\s*/', $verificationActivity->klinik) as $name) {
        $name = trim($name);
        if ($name !== '' && !in_array($name, $petugasPenerima, true)) {
          $petugasPenerima[] = $name;
        }
      }
    }

    if (empty($petugasPenerima)) {
      $petugasList = Petugas::whereNotNull('role')->get();
      foreach ($petugasList as $petugas) {
        $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
        if (!is_array($roles)) {
          continue;
        }
        // Role analis / penerima umum — fallback nama petugas
        if (in_array('5', $roles) || in_array(5, $roles) || in_array('7', $roles) || in_array(7, $roles)) {
          $nama = trim($petugas->nama ?? '');
          if ($nama !== '' && !in_array($nama, $petugasPenerima, true)) {
            $petugasPenerima[] = $nama;
          }
        }
      }
    }

    sort($petugasPenerima);

    return LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($petugasPenerima);
  }

  /**
   * Form pengolah sampel massal (verification step 2) untuk pasien status "Pemeriksaan".
   */
  public function createPengolahSampelMassal(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $selectedIds = array_values(array_filter((array) $request->input('selected_ids', [])));

    if (empty($selectedIds)) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Pilih minimal satu pasien untuk pengolah massal.');
    }

    $pasienList = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereIn('id_permohonan_uji_klinik', $selectedIds)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($pasienList->isEmpty()) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Pasien terpilih tidak ditemukan pada rombongan ini.');
    }

    $statusPengujianMap = $this->buildHajiStatusPengujianMap($pasienList->pluck('id_permohonan_uji_klinik')->all());

    $eligible = $pasienList->filter(function ($item) use ($statusPengujianMap) {
      $label = $statusPengujianMap[$item->id_permohonan_uji_klinik]['label'] ?? '';
      return $label === 'Pemeriksaan';
    });

    if ($eligible->isEmpty()) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Tidak ada pasien terpilih yang siap pengolah sampel. Status harus "Pemeriksaan".');
    }

    $klinikNumberSettings = KlinikNumberSettings::getSettings();

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.pengolah-sampel-massal', [
      'haji' => $haji,
      'pasienList' => $eligible->values(),
      'selectedIds' => $eligible->pluck('id_permohonan_uji_klinik')->values()->all(),
      'petugas_pengolah_sampel' => $this->getPetugasPengolahKlinikForHaji(),
      'klinikNumberSettings' => $klinikNumberSettings,
      'statusPengujianMap' => $statusPengujianMap,
    ]);
  }

  /**
   * Simpan pengolah sampel massal (step 2) ke semua pasien terpilih.
   */
  public function storePengolahSampelMassal(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);
    $selectedIds = array_values(array_filter((array) $request->input('selected_ids', [])));
    $jam = trim((string) $request->input('jam_pengolah', ''));
    $petugas = trim((string) $request->input('nama_petugas_pengolah', ''));

    if (empty($selectedIds)) {
      return response()->json(['status' => false, 'pesan' => 'Tidak ada pasien yang dipilih.'], 200);
    }

    if ($jam === '' || $petugas === '') {
      return response()->json(['status' => false, 'pesan' => 'Jam dan petugas pengolah wajib diisi!'], 200);
    }

    if (!preg_match('/^\d{1,2}:\d{2}$/', $jam)) {
      return response()->json(['status' => false, 'pesan' => 'Format jam tidak valid. Gunakan HH:mm.'], 200);
    }

    $validIds = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereIn('id_permohonan_uji_klinik', $selectedIds)
      ->whereNull('deleted_at')
      ->pluck('id_permohonan_uji_klinik')
      ->all();

    if (empty($validIds)) {
      return response()->json(['status' => false, 'pesan' => 'Pasien terpilih tidak valid untuk rombongan ini.'], 200);
    }

    $statusMap = $this->buildHajiStatusPengujianMap($validIds);
    $eligibleIds = array_values(array_filter($validIds, function ($pid) use ($statusMap) {
      return ($statusMap[$pid]['label'] ?? '') === 'Pemeriksaan';
    }));

    if (empty($eligibleIds)) {
      return response()->json([
        'status' => false,
        'pesan' => 'Tidak ada pasien yang siap pengolah sampel (status harus "Pemeriksaan").',
      ], 200);
    }

    $petugasFormatted = LaboratoriumPermohonanUjiKlinikManagement2::resolvePetugasCanonicalName($petugas);
    $sukses = 0;
    $gagal = [];

    foreach ($eligibleIds as $permohonanId) {
      try {
        $this->applyPengolahSampelMassalItem($permohonanId, $jam, $petugasFormatted);
        $sukses++;
      } catch (\Exception $e) {
        \Log::error('Error pengolah massal haji: ' . $e->getMessage(), [
          'haji_id' => $haji->id_permohonan_uji_klinik_haji,
          'permohonan_id' => $permohonanId,
        ]);
        $gagal[] = [
          'id' => $permohonanId,
          'pesan' => $e->getMessage(),
        ];
      }
    }

    $skipped = count($validIds) - count($eligibleIds);
    $pesan = "Pengolah massal selesai: {$sukses} berhasil";
    if (!empty($gagal)) {
      $pesan .= ', ' . count($gagal) . ' gagal';
    }
    if ($skipped > 0) {
      $pesan .= ", {$skipped} dilewati (bukan tahap pemeriksaan)";
    }
    $pesan .= '.';

    return response()->json([
      'status' => $sukses > 0,
      'pesan' => $pesan,
      'sukses' => $sukses,
      'gagal' => $gagal,
      'redirect' => route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id),
    ], 200);
  }

  /**
   * Upsert VerificationActivitySample step 2 (Pengolah Sampel) untuk satu permohonan.
   */
  private function applyPengolahSampelMassalItem(string $permohonanId, string $jam, string $petugas): void
  {
    $permohonan = PermohonanUjiKlinik2::find($permohonanId);
    $start = DateHelper::clockOnRegisterDate(
      $permohonan ? DateHelper::permohonanAnchorAt($permohonan) : null,
      $jam
    );
    $stop = $start->copy();

    $verifikasi = VerificationActivitySample::where('is_klinik', $permohonanId)
      ->where('id_verification_activity', 2)
      ->where(function ($q) {
        $q->where('resampling', 0)->orWhereNull('resampling');
      })
      ->first();

    if ($verifikasi) {
      $verifikasi->start_date = $start->format('Y-m-d H:i:s');
      $verifikasi->stop_date = $stop->format('Y-m-d H:i:s');
      $verifikasi->nama_petugas = $petugas;
      $verifikasi->is_done = 1;
      $verifikasi->save();
      return;
    }

    $verifikasiBaru = new VerificationActivitySample();
    $verifikasiBaru->id = Uuid::uuid4()->toString();
    $verifikasiBaru->id_verification_activity = 2;
    $verifikasiBaru->start_date = $start->format('Y-m-d H:i:s');
    $verifikasiBaru->stop_date = $stop->format('Y-m-d H:i:s');
    $verifikasiBaru->nama_petugas = $petugas;
    $verifikasiBaru->is_klinik = $permohonanId;
    $verifikasiBaru->is_done = 1;
    $verifikasiBaru->resampling = 0;
    $verifikasiBaru->save();
  }

  /**
   * Daftar petugas pengolah sampel klinik (step 2).
   *
   * @return array<int, string>
   */
  private function getPetugasPengolahKlinikForHaji(): array
  {
    $petugasList = [];

    $verificationActivity = VerificationActivity::find(2);
    if ($verificationActivity && !empty($verificationActivity->klinik) && $verificationActivity->klinik !== '-' && $verificationActivity->klinik !== 'NULL') {
      foreach (preg_split('/,\s*/', $verificationActivity->klinik) as $name) {
        $name = trim($name);
        if ($name !== '' && !in_array($name, $petugasList, true)) {
          $petugasList[] = $name;
        }
      }
    }

    if (empty($petugasList)) {
      // Fallback: pakai daftar petugas penerima bila step 2 belum dikonfigurasi
      $petugasList = $this->getPetugasPenerimaKlinikForHaji();
    }

    sort($petugasList);

    return LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($petugasList);
  }

  /**
   * Status pengujian berdasarkan tahapan verifikasi (1-6-7-2-3-4-5),
   * bukan hanya status_permohonan_uji_klinik (sering langsung "ANALIS" saat daftar).
   *
   * @param  array<int, string>  $permohonanIds
   * @return array<string, array{label: string, class: string}>
   */
  private function buildHajiStatusPengujianMap(array $permohonanIds): array
  {
    $map = [];
    foreach ($permohonanIds as $pid) {
      $map[$pid] = [
        'label' => 'Belum Dimulai',
        'class' => 'badge-secondary',
      ];
    }

    if (empty($permohonanIds)) {
      return $map;
    }

    $activities = VerificationActivitySample::query()
      ->whereIn('is_klinik', $permohonanIds)
      ->select('is_klinik', 'id_verification_activity', 'is_done')
      ->get()
      ->groupBy('is_klinik');

    $permohonanMeta = PermohonanUjiKlinik2::query()
      ->whereIn('id_permohonan_uji_klinik', $permohonanIds)
      ->get(['id_permohonan_uji_klinik', 'status_permohonan_uji_klinik', 'mode_pengambilan_sampel'])
      ->keyBy('id_permohonan_uji_klinik');

    foreach ($permohonanIds as $pid) {
      $meta = $permohonanMeta[$pid] ?? null;
      if (trim((string) (optional($meta)->status_permohonan_uji_klinik ?? '')) === 'SELESAI') {
        $map[$pid] = ['label' => 'Selesai', 'class' => 'badge-success'];
        continue;
      }

      $steps = [];
      foreach ($activities->get($pid, collect()) as $row) {
        $steps[(int) $row->id_verification_activity] = (int) $row->is_done;
      }

      $step1 = ($steps[1] ?? 0) === 1;
      $skipPengambilan = (optional($meta)->mode_pengambilan_sampel ?? '') === 'dibawa_pelanggan';
      $step6 = $skipPengambilan || ($steps[6] ?? 0) === 1;
      $step7 = ($steps[7] ?? 0) === 1;
      $step2 = ($steps[2] ?? 0) === 1;
      $step3 = ($steps[3] ?? 0) === 1;
      $step4 = ($steps[4] ?? 0) === 1;
      $step5 = ($steps[5] ?? 0) === 1;

      // Posisi proses saat ini (urutan klinik: 1-6-7-2-3-4-5)
      if ($step1 && $step6 && $step7 && $step2 && $step3 && $step4 && $step5) {
        $map[$pid] = ['label' => 'Selesai', 'class' => 'badge-success'];
      } elseif ($step1 && $step6 && $step7 && $step2 && $step3 && $step4) {
        $map[$pid] = ['label' => 'Validasi', 'class' => 'badge-info'];
      } elseif ($step1 && $step6 && $step7 && $step2 && $step3) {
        $map[$pid] = ['label' => 'Verifikasi', 'class' => 'badge-info'];
      } elseif ($step1 && $step6 && $step7 && $step2) {
        $map[$pid] = ['label' => 'Input Hasil', 'class' => 'badge-warning'];
      } elseif ($step1 && $step6 && $step7) {
        $map[$pid] = ['label' => 'Pemeriksaan', 'class' => 'badge-warning'];
      } elseif ($step1 && $step6) {
        $map[$pid] = ['label' => 'Penerimaan Sample', 'class' => 'badge-dark'];
      } elseif ($step1) {
        $map[$pid] = ['label' => 'Pengambilan Sample', 'class' => 'badge-secondary'];
      } else {
        $map[$pid] = ['label' => 'Registrasi', 'class' => 'badge-primary'];
      }
    }

    return $map;
  }

  /**
   * Form edit data pasien pada daftar pasien haji.
   */
  public function editPasien($hajiId, $permohonanId)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($hajiId);
    $permohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $permohonanId)
      ->where('id_permohonan_uji_klinik_haji', $hajiId)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->firstOrFail();

    if (!$permohonan->pasien) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $hajiId)
        ->with('error', 'Data pasien tidak ditemukan.');
    }

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.edit-pasien', [
      'haji' => $haji,
      'permohonan' => $permohonan,
      'pasien' => $permohonan->pasien,
    ]);
  }

  /**
   * Simpan perubahan data pasien dari daftar pasien haji.
   */
  public function updatePasien(Request $request, $hajiId, $permohonanId)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($hajiId);
    $permohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $permohonanId)
      ->where('id_permohonan_uji_klinik_haji', $hajiId)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->firstOrFail();

    $pasien = $permohonan->pasien;
    if (!$pasien) {
      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $hajiId)
        ->with('error', 'Data pasien tidak ditemukan.');
    }

    $validator = Validator::make($request->all(), [
      'nama_pasien' => 'required|string|max:255',
      'gender_pasien' => 'required|in:L,P',
      'tgllahir_pasien' => 'required|date_format:Y-m-d',
      'nik_pasien' => 'nullable|string|max:16',
      'tmpt_lahir' => 'nullable|string|max:255',
      'pekerjaan' => 'nullable|string|max:255',
      'phone_pasien' => 'nullable|string|max:50',
      'alamat_pasien' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
      return redirect()
        ->back()
        ->withErrors($validator)
        ->withInput()
        ->with('error', $validator->errors()->first());
    }

    $tgllahir = $request->input('tgllahir_pasien');
    $nik = trim((string) $request->input('nik_pasien', ''));

    if ($nik !== '') {
      $nikExists = Pasien::where('nik_pasien', $nik)
        ->where('id_pasien', '!=', $pasien->id_pasien)
        ->whereNull('deleted_at')
        ->exists();
      if ($nikExists) {
        return redirect()
          ->back()
          ->withInput()
          ->with('error', 'NIK sudah digunakan pasien lain.');
      }
    }

    $pasien->nama_pasien = mb_strtoupper(trim((string) $request->input('nama_pasien')), 'UTF-8');
    $pasien->gender_pasien = $request->input('gender_pasien');
    $pasien->tgllahir_pasien = $tgllahir;
    $pasien->nik_pasien = $nik !== '' ? $nik : null;
    $pasien->tmpt_lahir = trim((string) $request->input('tmpt_lahir', '')) ?: null;
    $pasien->pekerjaan = trim((string) $request->input('pekerjaan', '')) ?: null;
    $pasien->phone_pasien = trim((string) $request->input('phone_pasien', '')) ?: null;
    $pasien->alamat_pasien = Smt::sanitizeAlamatPasien(
      $request->input('alamat_pasien', ''),
      $tgllahir
    );
    $pasien->save();

    // Sinkron umur di permohonan bila tanggal lahir berubah
    try {
      $birthdate = Carbon::parse($tgllahir);
      $now = Carbon::now();
      $years = $birthdate->diffInYears($now);
      $months = $birthdate->copy()->addYears($years)->diffInMonths($now);
      $days = $birthdate->copy()->addYears($years)->addMonths($months)->diffInDays($now);

      $permohonan->umurtahun_pasien_permohonan_uji_klinik = $years;
      $permohonan->umurbulan_pasien_permohonan_uji_klinik = $months;
      $permohonan->umurhari_pasien_permohonan_uji_klinik = $days;
      $permohonan->save();
    } catch (\Throwable $e) {
      // Umur gagal dihitung tidak memblokir update pasien.
    }

    return redirect()
      ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji)
      ->with('success', 'Data pasien berhasil diperbarui.');
  }

  /**
   * Tambah pasien ke haji yang sudah ada
   * Selalu lewat Step 2 (pilih parameter) dulu — paket mayoritas sudah tercentang.
   */
  public function tambahPasien($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    // Ambil customer berdasarkan nama haji (puskesmas) — wajib untuk wizard
    $customer = Customer::where('name_customer', $haji->nama_haji)->first();
    if (!$customer) {
      return redirect()->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', 'Customer tidak ditemukan. Silakan hubungi administrator.');
    }

    // Prefill session dengan paket mayoritas agar Step 2 langsung menampilkan centangan.
    $parameters = $this->stripCompositePaketHajiFromParameters(
      $this->buildHajiParametersFromMajority($id)
    );

    session([
      'haji_parameters' => $parameters,
      'haji_id' => $id,
      'haji_customer_id' => $customer->id_customer,
      // false = default sistem (mayoritas), user masih bisa ubah di Step 2
      'haji_parameters_user_selected' => false,
    ]);

    $redirect = redirect()->route('elits-permohonan-uji-klinik-2.haji.create-new', [
      'step' => 2,
      'customer_id' => $customer->id_customer,
      'haji_id' => $id,
    ]);

    if ($this->isHajiParametersEmpty($parameters)) {
      return $redirect->with('error', 'Belum ada parameter pemeriksaan. Silakan pilih parameter seperti pemeriksaan umum.');
    }

    return $redirect->with(
      'info',
      'Parameter sudah diisi dari paket mayoritas pasien haji yang sudah ada. Cek/ubah jika perlu, lalu lanjut ke tambah pasien.'
    );
  }

  /**
   * Cetak Nota konsolidasi untuk semua pasien dalam haji (atas nama customer/puskesmas)
   */
  public function cetakNota($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    // Ambil customer berdasarkan nama_haji
    $customer = Customer::where('name_customer', $haji->nama_haji)->first();
    if (!$customer) {
      abort(404, 'Customer tidak ditemukan');
    }

    // Ambil semua permohonan dalam haji
    $permohonanList = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($permohonanList->isEmpty()) {
      abort(404, 'Tidak ada data untuk dicetak');
    }

    // Grouping paket berdasarkan seluruh pasien
    // Key: parameter_paket_klinik_id atau parameter_paket_extra_id
    // Value: [name_item, price_item, jumlah_sampel, jenis_sampel]
    $grouped_items = [];

    foreach ($permohonanList as $permohonan) {
      // Get parameter paket untuk permohonan ini
      $parameterPaket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $permohonan->id_permohonan_uji_klinik)
        ->whereNull('deleted_at')
        ->get();

      foreach ($parameterPaket as $val) {
        if (!empty($val->parameter_paket_extra)) {
          // Extra Paket
          $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
          if ($extra_paket) {
            $key = 'EP_' . $val->parameter_paket_extra;
            if (!isset($grouped_items[$key])) {
              $grouped_items[$key] = [
                'name_item' => $extra_paket->nama_parameter_paket_extra,
                'price_item' => $val->harga_permohonan_uji_paket_klinik,
                'jumlah_sampel' => 0,
                'jenis_sampel' => $permohonan->jenis_sampel ?? '',
              ];
            }
            $grouped_items[$key]['jumlah_sampel']++;
          }
        } else {
          // Paket Normal
          $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();
          if ($paket) {
            $key = 'P_' . $val->parameter_paket_klinik;
            if (!isset($grouped_items[$key])) {
              $grouped_items[$key] = [
                'name_item' => $paket->name_parameter_paket_klinik,
                'price_item' => $val->harga_permohonan_uji_paket_klinik,
                'jumlah_sampel' => 0,
                'jenis_sampel' => $permohonan->jenis_sampel ?? '',
              ];
            }
            $grouped_items[$key]['jumlah_sampel']++;
          }
        }
      }
    }

    // Convert grouped_items ke format value_items dengan subtotal
    $value_items = [];
    foreach ($grouped_items as $item) {
      $value_items[] = [
        'name_item' => $item['name_item'],
        'price_item' => $item['price_item'],
        'jumlah_sampel' => $item['jumlah_sampel'],
        'subtotal' => $item['price_item'] * $item['jumlah_sampel'],
        'jenis_sampel' => $item['jenis_sampel'],
      ];
    }

    // Total parameter (pemeriksaan saja, sebelum sampling)
    $total_harga = array_sum(array_column($value_items, 'subtotal'));

    // Biaya pengambilan sampel: tampil sebagai baris Pemeriksaan (jenis sampel = sampling)
    // Hitung dari tarif mayoritas × jumlah pasien berbayar agar tidak terpengaruh outlier (mis. 19998)
    $samplingPatients = $permohonanList->filter(function ($permohonan) {
      $mode = (string) ($permohonan->mode_pengambilan_sampel ?? '');
      $biaya = (int) ($permohonan->biaya_pengambilan_sampel ?? 0);
      return $mode === 'diambil_lokasi_rumah' || $biaya > 0;
    });
    $jumlah_sampling = $samplingPatients->count();
    $biaya_pengambilan = 0;
    if ($jumlah_sampling > 0) {
      $feeCounts = [];
      foreach ($samplingPatients as $permohonan) {
        $fee = (int) ($permohonan->biaya_pengambilan_sampel ?? 0);
        if ($fee <= 0) {
          continue;
        }
        if (!isset($feeCounts[$fee])) {
          $feeCounts[$fee] = 0;
        }
        $feeCounts[$fee]++;
      }
      arsort($feeCounts);
      $unit_sampling = 20000;
      if (!empty($feeCounts)) {
        reset($feeCounts);
        $unit_sampling = (int) key($feeCounts);
      }
      $biaya_pengambilan = $unit_sampling * $jumlah_sampling;

      $value_items[] = [
        'name_item' => 'Pengambilan Sampel',
        'price_item' => $unit_sampling,
        'jumlah_sampel' => $jumlah_sampling,
        'subtotal' => $biaya_pengambilan,
        'jenis_sampel' => 'sampling',
      ];
    }

    // Get verification activity step 1 dari semua permohonan untuk nama petugas
    // Ambil dari permohonan pertama yang memiliki verification activity step 1
    $nama_petugas_registrasi = "...................";
    $tanggalPemeriksaan = null;

    foreach ($permohonanList as $permohonan) {
      $verificationActivity = VerificationActivitySample::query()
        ->where('is_klinik', '=', $permohonan->id_permohonan_uji_klinik)
        ->where('id_verification_activity', '=', 1)
        ->first();

      if ($verificationActivity && !empty($verificationActivity->nama_petugas)) {
        $nama_petugas_registrasi = $verificationActivity->nama_petugas;
        $tanggalPemeriksaan = $verificationActivity->stop_date ?? null;
        break; // Ambil dari yang pertama ditemukan
      }
    }

    // Generate PDF dengan nota konsolidasi
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.cetak-nota-konsolidasi', [
      'haji' => $haji,
      'customer' => $customer,
      'value_items' => $value_items,
      'total_harga' => $total_harga,
      'biaya_pengambilan' => $biaya_pengambilan,
      'tanggal_transaksi_lunas' => $tanggalPemeriksaan,
      'nama_petugas_registrasi' => $nama_petugas_registrasi,
      'jumlah_pasien' => $permohonanList->count(),
    ])->setPaper('a4', 'portrait');

    $filename = 'Nota_Haji_' . $haji->nama_haji . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.pdf';

    return $pdf->stream($filename);
  }

  /**
   * Cetak Nota satuan (satu halaman nota untuk tiap pasien dalam satu rombongan haji)
   */
  public function cetakNotaPerPasien($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    $permohonanList = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($permohonanList->isEmpty()) {
      abort(404, 'Tidak ada data untuk dicetak');
    }

    $notaController = new LaboratoriumNotaController();
    $notas = [];

    foreach ($permohonanList as $permohonan) {
      $nota = $notaController->buildNotaKlinikData($permohonan->id_permohonan_uji_klinik);

      if (!empty($nota)) {
        $notas[] = $nota;
      }
    }

    if (empty($notas)) {
      abort(404, 'Tidak ada data untuk dicetak');
    }

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.persuratan.nota.nota-massal', [
      'notas' => $notas,
      'lab_name' => 'KLINIK',
    ])->setPaper('a4', 'portrait');

    $filename = 'Nota_Satuan_Haji_' . $haji->nama_haji . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.pdf';

    return $pdf->stream($filename);
  }

  /**
   * Cetak Rekap untuk semua pasien dalam haji
   */
  public function cetakRekap($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    // Ambil semua permohonan dalam haji
    $data = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($data->isEmpty()) {
      abort(404, 'Tidak ada data untuk dicetak');
    }

    // Ambil semua payment untuk permohonan ini
    $permohonanIds = $data->pluck('id_permohonan_uji_klinik')->toArray();
    $payments = PermohonanUjiPaymentKlinik::whereIn('permohonan_uji_klinik_id', $permohonanIds)
      ->whereNull('deleted_at')
      ->get()
      ->keyBy('permohonan_uji_klinik_id');

    // Attach payment ke setiap permohonan
    foreach ($data as $item) {
      $item->payment = $payments->get($item->id_permohonan_uji_klinik);
    }

    // Hitung total
    $total_harga = $data->sum('total_harga_permohonan_uji_klinik');
    $total_terbayar = $payments->sum('terbayar_permohonan_uji_payment_klinik');

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.cetak-rekap', [
      'haji' => $haji,
      'data' => $data,
      'total_harga' => $total_harga,
      'total_terbayar' => $total_terbayar,
      'jumlah_pasien' => $data->count()
    ]);

    $filename = 'Rekap_Haji_' . $haji->nama_haji . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.pdf';

    return $pdf->stream($filename);
  }

  /**
   * Export Excel rekap haji
   */
  public function exportRekapHaji($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    // Ambil semua permohonan dalam haji dengan relasi lengkap
    $permohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->with([
        'pasien',
        'permohonanujiparameterklinik.parametersatuanklinik.parameterjenisklinik',
        'permohonanujiparameterklinik.jenisparameterklinik',
        'permohonanujiparameterklinik.permohonanujisubparameterklinik.parametersubsatuanklinik',
        'permohonanujiparameterklinik.bakumutu.unit'
      ])
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($permohonan->isEmpty()) {
      abort(404, 'Tidak ada data untuk diexport');
    }

    // Mapping parameter name ke kolom yang diharapkan view
    // Key lebih spesifik harus dicek dulu (diurutkan panjang menurun saat match)
    $parameterMapping = [
      // KIMIA DARAH
      'hemoglobin a1c' => 'hba1c',
      'hba1c' => 'hba1c',
      'gula darah puasa' => 'gdp',
      'gula darah 2 jam pp' => 'gpp',
      'gd 2 jam pp' => 'gpp',
      'gdn' => 'gdp',
      'gdp' => 'gdp',
      'gpp' => 'gpp',
      'cholesterol total' => 'chol',
      'cholesterol' => 'chol',
      'kolesterol' => 'chol',
      'chol' => 'chol',
      'trigliserida' => 'tg',
      'trigliserid' => 'tg',
      'triglyceride' => 'tg',
      'tg' => 'tg',
      'creatinine' => 'kreatinin',
      'creatinin' => 'kreatinin',
      'kreatinin' => 'kreatinin',
      'cre' => 'kreatinin',
      'e-gfr' => 'egfr',
      'egfr' => 'egfr',
      'ureum' => 'ureum',
      'urea' => 'ureum',
      'sgot' => 'sgot',
      'sgpt' => 'sgpt',
      'delta leukosit' => 'delta_leu',
      'delta leu' => 'delta_leu',
      'leukosit' => 'delta_leu',
      // DARAH RUTIN
      'delta eritrosit' => 'delta_eri',
      'delta eri' => 'delta_eri',
      'eritrosit' => 'delta_eri',
      'hemoglobin' => 'hb',
      'hb' => 'hb',
      'hematokrit' => 'hematokrit',
      'ht' => 'hematokrit',
      'trombosit' => 'trombosit',
      'platelet' => 'trombosit',
      'plt' => 'trombosit',
      'neutrophil' => 'neu',
      'neutrofil' => 'neu',
      'neu' => 'neu',
      'lymphocyte' => 'lym',
      'lymphosit' => 'lym',
      'limfosit' => 'lym',
      'limfo' => 'lym',
      'lym' => 'lym',
      'monocyte' => 'mono',
      'monosit' => 'mono',
      'mono' => 'mono',
      'eosinophil' => 'eos',
      'eosinofil' => 'eos',
      'eos' => 'eos',
      'basophil' => 'baso',
      'basofil' => 'baso',
      'baso' => 'baso',
      // LED: nama di master sering "Laju Endap Darah" (tanpa kata LED)
      'laju endap darah 1 jam' => 'led',
      'laju endap darah 2 jam' => 'led',
      'laju endap darah' => 'led',
      'laju endap' => 'led',
      'sedimentasi' => 'led',
      'led' => 'led',
      'esr' => 'led',
      // Golongan darah
      'golongan darah' => 'gol_darah',
      'gol darah' => 'gol_darah',
      'gol dar' => 'gol_darah',
      'golongan' => 'gol_darah',
      'blood type' => 'gol_darah',
      'blood group' => 'gol_darah',
    ];

    // Urutkan key panjang → pendek agar match spesifik menang
    uksort($parameterMapping, function ($a, $b) {
      return strlen($b) <=> strlen($a);
    });

    // Kolom rekap darah hanya boleh diisi dari jenis parameter yang sesuai.
    // Nama seperti Leukosit/Eritrosit juga ada di Urin Rutin & Sedimen — jangan ikut terambil.
    $mappedKeyAllowedJenis = [
      'hba1c' => ['kimia klinik'],
      'gdp' => ['kimia klinik'],
      'gpp' => ['kimia klinik'],
      'chol' => ['kimia klinik', 'lemak darah'],
      'tg' => ['kimia klinik', 'lemak darah'],
      'kreatinin' => ['kimia klinik'],
      'egfr' => ['kimia klinik'],
      'ureum' => ['kimia klinik'],
      'sgot' => ['kimia klinik'],
      'sgpt' => ['kimia klinik'],
      'delta_leu' => ['darah rutin'],
      'delta_eri' => ['darah rutin'],
      'hb' => ['darah rutin', 'hematologi indeks eritrosit'],
      'hematokrit' => ['darah rutin', 'hematologi indeks eritrosit'],
      'trombosit' => ['darah rutin', 'hematologi indeks eritrosit'],
      'neu' => ['hitung jenis', 'darah rutin'],
      'lym' => ['hitung jenis', 'darah rutin'],
      'mono' => ['hitung jenis', 'darah rutin'],
      'eos' => ['hitung jenis', 'darah rutin'],
      'baso' => ['hitung jenis', 'darah rutin'],
      'led' => ['darah rutin'],
      'gol_darah' => null, // boleh dari jenis mana pun
    ];

    $resolveMappedKey = function (string $paramName) use ($parameterMapping) {
      $paramName = strtolower(trim($paramName));
      if ($paramName === '') {
        return null;
      }
      foreach ($parameterMapping as $key => $value) {
        if (stripos($paramName, $key) !== false) {
          return $value;
        }
      }

      return null;
    };

    $isJenisAllowedForKey = function (string $mappedKey, string $jenisName) use ($mappedKeyAllowedJenis) {
      if (!array_key_exists($mappedKey, $mappedKeyAllowedJenis)) {
        return true;
      }
      $allowed = $mappedKeyAllowedJenis[$mappedKey];
      if ($allowed === null) {
        return true;
      }
      $jenisName = strtolower(trim($jenisName));
      if ($jenisName === '') {
        return false;
      }
      foreach ($allowed as $allowedJenis) {
        if ($jenisName === $allowedJenis || strpos($jenisName, $allowedJenis) === 0) {
          return true;
        }
      }

      return false;
    };

    $resolveJenisName = function ($param) {
      $fromParam = strtolower(trim((string) optional($param->jenisparameterklinik)->name_parameter_jenis_klinik));
      if ($fromParam !== '') {
        return $fromParam;
      }

      return strtolower(trim((string) optional(optional($param->parametersatuanklinik)->parameterjenisklinik)->name_parameter_jenis_klinik));
    };

    $normalizeHasil = function ($hasil) {
      if ($hasil === null) {
        return '';
      }
      $plain = html_entity_decode(strip_tags((string) $hasil), ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $plain = preg_replace('/\s+/u', ' ', $plain);
      $plain = trim((string) $plain);
      // Flag abnormal di UI sering ditandai asterisk di akhir nilai
      $plain = preg_replace('/\s*\*+\s*$/u', '', $plain);

      return trim((string) $plain);
    };

    // Prepare data untuk export
    $data = [];
    foreach ($permohonan as $item) {
      $pasien = $item->pasien;

      // Hitung umur
      $umur = '-';
      if ($pasien && $pasien->tgllahir_pasien) {
        $birthdate = Carbon::parse($pasien->tgllahir_pasien);
        $now = Carbon::now();
        $umur = $birthdate->diffInYears($now);
      }

      // Inisialisasi semua kolom parameter dengan '-'
      $rowData = [
        // Jangan pakai noregister mentah: sebagian tersimpan polos, sebagian bersuffix
        // penghitung pendaftaran yang bukan nomor lab.
        'no_specimen' => $item->getDisplayNoregister(),
        'nama' => $pasien ? $pasien->nama_pasien : '-',
        'umur' => $umur,
        'jk' => $pasien ? ($pasien->gender_pasien == 'L' ? 'L' : 'P') : '-',
        'alamat' => $pasien ? Smt::alamatLengkapPasien($pasien) : '-',
        'hba1c' => '-',
        'gdp' => '-',
        'gpp' => '-',
        'chol' => '-',
        'tg' => '-',
        'kreatinin' => '-',
        'egfr' => '-',
        'ureum' => '-',
        'sgot' => '-',
        'sgpt' => '-',
        'delta_leu' => '-',
        'delta_eri' => '-',
        'hb' => '-',
        'hematokrit' => '-',
        'trombosit' => '-',
        'neu' => '-',
        'lym' => '-',
        'mono' => '-',
        'eos' => '-',
        'baso' => '-',
        'led' => '-',
        'gol_darah' => '-',
      ];

      // Ambil parameter pemeriksaan darah
      $parameters = $item->permohonanujiparameterklinik ?? collect();

      foreach ($parameters as $param) {
        if (!$param->parametersatuanklinik) {
          continue;
        }

        $paramName = strtolower(trim($param->parametersatuanklinik->name_parameter_satuan_klinik ?? ''));
        $jenisName = $resolveJenisName($param);
        $hasil = $normalizeHasil($param->hasil_permohonan_uji_parameter_klinik ?? '');

        $mappedKey = $resolveMappedKey($paramName);

        if (
          $mappedKey
          && $hasil !== ''
          && $hasil !== '-'
          && $isJenisAllowedForKey($mappedKey, $jenisName)
        ) {
          // Jangan timpa nilai yang sudah terisi oleh match yang lebih spesifik
          if ($rowData[$mappedKey] === '-' || $rowData[$mappedKey] === '') {
            $rowData[$mappedKey] = $hasil;
          }
        }

        // Cek sub parameter (untuk hitung jenis leukosit / Rh dll.)
        $subParams = $param->permohonanujisubparameterklinik ?? collect();
        foreach ($subParams as $subParam) {
          if (!$subParam->parametersubsatuanklinik) {
            continue;
          }

          $subParamName = strtolower(trim($subParam->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? ''));
          $subHasil = $normalizeHasil($subParam->hasil_permohonan_uji_sub_parameter_klinik ?? '');
          if ($subHasil === '' || $subHasil === '-') {
            continue;
          }

          $subMapped = $resolveMappedKey($subParamName);
          if (
            $subMapped
            && in_array($subMapped, ['neu', 'lym', 'mono', 'eos', 'baso', 'led', 'gol_darah'], true)
            && $isJenisAllowedForKey($subMapped, $jenisName)
          ) {
            if ($rowData[$subMapped] === '-' || $rowData[$subMapped] === '') {
              $rowData[$subMapped] = $subHasil;
            }
            continue;
          }

          // Rh / rhesus sering jadi sub dari Golongan Darah
          if ($mappedKey === 'gol_darah' || stripos($subParamName, 'rh') !== false || stripos($subParamName, 'rhesus') !== false) {
            $currentGol = $rowData['gol_darah'];
            if ($currentGol === '-' || $currentGol === '') {
              $rowData['gol_darah'] = $subHasil;
            } elseif (stripos($currentGol, $subHasil) === false) {
              $rowData['gol_darah'] = trim($currentGol . ' ' . $subHasil);
            }
          }
        }
      }

      $data[] = $rowData;
    }

    // Ambil tahun haji dari tanggal haji
    $tahun_haji = $haji->tgl_haji ? Carbon::parse($haji->tgl_haji)->format('Y') : Carbon::now()->format('Y');

    // Ambil nama puskesmas dari nama_haji atau cari customer
    $nama_pusklesmas = $haji->nama_haji;
    $customer = Customer::where('name_customer', $haji->nama_haji)->first();
    if ($customer) {
      $nama_pusklesmas = $customer->name_customer;
    }

    // Tanggal diambil dan diperiksa (ambil dari tanggal register pertama atau sekarang)
    $tgl_diambil = $permohonan->first()->tglregister_permohonan_uji_klinik
      ? Carbon::parse($permohonan->first()->tglregister_permohonan_uji_klinik)->format('d F Y')
      : Carbon::now()->format('d F Y');
    $tgl_diperiksa = $tgl_diambil;

    // No Lab (bisa diambil dari nomor register pertama atau default)
    $no_lab = $permohonan->first()->noregister_permohonan_uji_klinik ?? '445.03/160/05.31/' . $tahun_haji;

    $export = new RekapHajiExport($data, $tahun_haji, $nama_pusklesmas, $no_lab, $tgl_diambil, $tgl_diperiksa);

    $filename = 'Rekap_Haji_' . str_replace(' ', '_', $haji->nama_haji) . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.xlsx';

    return Excel::download($export, $filename);
  }

  /**
   * Export Excel rekap identitas pasien jamaah (nama, usia, alamat).
   * Opsional filter ?tanggal=YYYY-MM-DD untuk jamaah yang didaftarkan pada tanggal tertentu.
   */
  public function exportPasienHaji(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    $query = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->with('pasien')
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->orderBy('created_at', 'asc');

    $filterTanggal = null;
    if ($request->filled('tanggal')) {
      $filterTanggal = Carbon::parse($request->get('tanggal'))->format('Y-m-d');
      $query->where(function ($q) use ($filterTanggal) {
        $q->whereDate('tglregister_permohonan_uji_klinik', $filterTanggal)
          ->orWhere(function ($q2) use ($filterTanggal) {
            $q2->whereNull('tglregister_permohonan_uji_klinik')
              ->whereDate('created_at', $filterTanggal);
          });
      });
    }

    $permohonanList = $query->get();
    if ($permohonanList->isEmpty()) {
      $pesan = $filterTanggal
        ? 'Tidak ada pasien jamaah yang terdaftar pada tanggal ' . Carbon::parse($filterTanggal)->isoFormat('DD MMMM YYYY') . '.'
        : 'Tidak ada data pasien jamaah untuk diexport.';

      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('error', $pesan);
    }

    $rows = $this->buildPasienHajiExportRows($permohonanList);
    $export = new PasienHajiExport($rows, $haji, $filterTanggal);

    $slugNama = Str::slug($haji->nama_haji, '_');
    $tglLabel = $filterTanggal
      ?: ($haji->tgl_haji ? Carbon::parse($haji->tgl_haji)->format('Y-m-d') : date('Y-m-d'));
    $filename = 'Daftar_Pasien_Haji_' . $slugNama . '_' . $tglLabel . '.xlsx';

    return Excel::download($export, $filename);
  }

  /**
   * @param \Illuminate\Support\Collection|\Smt\Masterweb\Models\PermohonanUjiKlinik2[] $permohonanList
   */
  private function buildPasienHajiExportRows($permohonanList): array
  {
    $rows = [];
    $no = 1;
    $settings = KlinikNumberSettings::getSettings();

    foreach ($permohonanList as $permohonan) {
      $pasien = $permohonan->pasien;
      $rows[] = [
        'no_spesimen' => $this->resolveNoSpesimenPasienHajiExport($permohonan, $settings),
        'no' => $no++,
        'nama' => $pasien && $pasien->nama_pasien ? mb_strtoupper($pasien->nama_pasien, 'UTF-8') : '-',
        'jk' => $this->resolveJkPasienHajiExport($pasien),
        'tanggal_lahir' => $this->resolveTanggalLahirPasienHajiExport($pasien),
        'usia' => $this->resolveUsiaPasienHaji($permohonan, $pasien),
        'alamat' => $pasien ? Smt::alamatLengkapPasien($pasien) : '-',
        'keterangan' => '',
      ];
    }

    return $rows;
  }

  /**
   * Kolom NO SPESIMEN di daftar hadir: selalu {spesimen} / {lab} dari field lab asli.
   * Jangan pakai suffix noregister (itu jumlah pendaftaran, bukan nomor lab).
   */
  private function resolveNoSpesimenPasienHajiExport(PermohonanUjiKlinik2 $permohonan, $settings): string
  {
    $spesimen = '';
    if (!empty($settings->is_nomor_spesimen_manual) && !empty($permohonan->nomor_spesimen_manual)) {
      $spesimen = (string) preg_replace('/\D+/', '', (string) $permohonan->nomor_spesimen_manual);
    }
    if ($spesimen === '' || (int) $spesimen < 1) {
      $spesimen = (string) ($permohonan->getNomorSpesimen() ?? '');
    }
    if ($spesimen === '' || (int) $spesimen < 1) {
      $spesimen = (string) preg_replace('/\D+/', '', (string) ($permohonan->nourut_permohonan_uji_klinik ?? ''));
    }

    $lab = (string) ($permohonan->getNomorLab() ?? '');
    if ($lab === '' || (int) $lab < 1) {
      $lab = (string) preg_replace('/\D+/', '', trim((string) ($permohonan->nomor_lab_manual ?? '')));
    }
    if (($lab === '' || (int) $lab < 1) && !empty($permohonan->nomer_lab)) {
      $lab = (string) preg_replace('/\D+/', '', (string) $permohonan->nomer_lab);
    }

    if ($spesimen !== '' && (int) $spesimen > 0 && $lab !== '' && (int) $lab > 0) {
      return ((int) $spesimen) . ' / ' . ((int) $lab);
    }
    if ($spesimen !== '' && (int) $spesimen > 0) {
      return (string) ((int) $spesimen);
    }
    if ($lab !== '' && (int) $lab > 0) {
      return (string) ((int) $lab);
    }

    return '-';
  }

  private function resolveJkPasienHajiExport($pasien): string
  {
    if (!$pasien) {
      return '-';
    }

    $gender = strtoupper(trim((string) ($pasien->gender_pasien ?? '')));
    if ($gender === 'L' || $gender === 'LAKI-LAKI' || $gender === 'LAKI') {
      return 'L';
    }
    if ($gender === 'P' || $gender === 'PEREMPUAN' || $gender === 'WANITA') {
      return 'P';
    }

    $jk = strtoupper(trim((string) ($pasien->jeniskelamin_pasien ?? '')));
    if ($jk === 'L' || strpos($jk, 'LAKI') !== false) {
      return 'L';
    }
    if ($jk === 'P' || strpos($jk, 'PEREMPUAN') !== false || strpos($jk, 'WANITA') !== false) {
      return 'P';
    }

    return '-';
  }

  private function resolveTanggalLahirPasienHajiExport($pasien): string
  {
    if (!$pasien || empty($pasien->tgllahir_pasien)) {
      return '-';
    }

    try {
      return Carbon::parse($pasien->tgllahir_pasien)->format('d/m/Y');
    } catch (\Throwable $e) {
      return '-';
    }
  }

  private function resolveUsiaPasienHaji($permohonan, $pasien): string
  {
    $tahun = $permohonan->umurtahun_pasien_permohonan_uji_klinik;
    if ($tahun !== null && $tahun !== '') {
      return (string) $tahun;
    }

    if ($pasien && !empty($pasien->tgllahir_pasien)) {
      $refDate = $permohonan->tglregister_permohonan_uji_klinik
        ?? $permohonan->created_at
        ?? now();
      try {
        return (string) Carbon::parse($pasien->tgllahir_pasien)->diffInYears(Carbon::parse($refDate));
      } catch (\Throwable $e) {
        return '-';
      }
    }

    return '-';
  }

  /**
   * Export Excel rekap haji untuk Urin Rutin
   */
  public function exportRekapHajiUrinRutin($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    // Ambil semua permohonan dalam haji dengan relasi lengkap
    $permohonan = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at')
      ->with([
        'pasien',
        'permohonanujiparameterklinik.parametersatuanklinik.parameterjenisklinik',
        'permohonanujiparameterklinik.jenisparameterklinik',
        'permohonanujiparameterklinik.permohonanujisubparameterklinik.parametersubsatuanklinik',
        'permohonanujiparameterklinik.bakumutu.unit'
      ])
      ->orderBy('nourut_permohonan_uji_klinik', 'asc')
      ->get();

    if ($permohonan->isEmpty()) {
      abort(404, 'Tidak ada data untuk diexport');
    }

    // Prepare data untuk export
    $data = [];
    foreach ($permohonan as $item) {
      $pasien = $item->pasien;

      // Hitung umur
      $umur = '-';
      if ($pasien && $pasien->tgllahir_pasien) {
        $birthdate = Carbon::parse($pasien->tgllahir_pasien);
        $now = Carbon::now();
        $umur = $birthdate->diffInYears($now);
      }

      // Inisialisasi semua kolom parameter dengan '-'
      $rowData = [
        // Jangan pakai noregister mentah: sebagian tersimpan polos, sebagian bersuffix
        // penghitung pendaftaran yang bukan nomor lab.
        'no_specimen' => $item->getDisplayNoregister(),
        'nama' => $pasien ? $pasien->nama_pasien : '-',
        'umur' => $umur,
        'jk' => $pasien ? ($pasien->gender_pasien == 'L' ? 'L' : 'P') : '-',
        'alamat' => $pasien ? Smt::alamatLengkapPasien($pasien) : '-',
        'warna' => '-',
        'bau' => '-',
        'kejernihan' => '-',
        'eritrosit' => '-',
        'urobilinogen' => '-',
        'bilirubin' => '-',
        'protein' => '-',
        'nitrat' => '-',
        'keton' => '-',
        'glukosa' => '-',
        'ph' => '-',
        'berat_jenis' => '-',
        'leu' => '-',
        'epitel' => '-',
        'leu_sedimen' => '-',
        'ery' => '-',
        'cyli' => '-',
        'kristal' => '-',
        'lain2' => '-',
        'pp_test' => '-',
      ];

      // Ambil parameter pemeriksaan urin
      $parameters = $item->permohonanujiparameterklinik ?? collect();
      $bobotKolom = [];

      foreach ($parameters as $param) {
        if (!$param->parametersatuanklinik) continue;
        if (!$this->isParameterSampelUrinHaji($param)) continue;

        $paramName = (string) ($param->parametersatuanklinik->name_parameter_satuan_klinik ?? '');
        $isSedimen = $this->isParameterSedimenUrinHaji($param);

        $this->assignHasilUrinRutinHaji(
          $rowData,
          $bobotKolom,
          $paramName,
          $param->hasil_permohonan_uji_parameter_klinik ?? '',
          $isSedimen
        );

        $subParams = $param->permohonanujisubparameterklinik ?? collect();
        foreach ($subParams as $subParam) {
          if (!$subParam->parametersubsatuanklinik) continue;

          $subParamName = (string) ($subParam->parametersubsatuanklinik->name_parameter_sub_satuan_klinik ?? '');

          $this->assignHasilUrinRutinHaji(
            $rowData,
            $bobotKolom,
            $subParamName,
            $subParam->hasil_permohonan_uji_sub_parameter_klinik ?? '',
            $isSedimen || stripos($subParamName, 'sedimen') !== false
          );
        }
      }

      $data[] = $rowData;
    }

    // Ambil tahun haji dari tanggal haji
    $tahun_haji = $haji->tgl_haji ? Carbon::parse($haji->tgl_haji)->format('Y') : Carbon::now()->format('Y');

    // Ambil nama puskesmas dari nama_haji atau cari customer
    $nama_pusklesmas = $haji->nama_haji;
    $customer = Customer::where('name_customer', $haji->nama_haji)->first();
    if ($customer) {
      $nama_pusklesmas = $customer->name_customer;
    }

    // Tanggal diambil dan diperiksa (ambil dari tanggal register pertama atau sekarang)
    $tgl_diambil = $permohonan->first()->tglregister_permohonan_uji_klinik
      ? Carbon::parse($permohonan->first()->tglregister_permohonan_uji_klinik)->format('d F Y')
      : Carbon::now()->format('d F Y');
    $tgl_diperiksa = $tgl_diambil;

    // No Lab (bisa diambil dari nomor register pertama atau default)
    $no_lab = $permohonan->first()->noregister_permohonan_uji_klinik ?? '445.03/160/05.31/' . $tahun_haji;

    $export = new RekapHajiUrinRutinExport($data, $tahun_haji, $nama_pusklesmas, $no_lab, $tgl_diambil, $tgl_diperiksa);

    $filename = 'Rekap_Haji_Urin_Rutin_' . str_replace(' ', '_', $haji->nama_haji) . '_' . Carbon::parse($haji->tgl_haji)->format('Y-m-d') . '.xlsx';

    return Excel::download($export, $filename);
  }

  /**
   * Nama parameter carik/kimia urin -> kolom rekap haji urin rutin.
   * Kolom ERITROSIT pada rekap adalah pembacaan darah samar (Blood) dari carik celup.
   */
  const KOLOM_URIN_RUTIN_HAJI = [
    'berat jenis' => 'berat_jenis',
    'specific gravity' => 'berat_jenis',
    'protein urin' => 'protein',
    'darah samar' => 'eritrosit',
    'sel darah merah' => 'eritrosit',
    'tes kehamilan' => 'pp_test',
    'test kehamilan' => 'pp_test',
    'pregnancy test' => 'pp_test',
    'plano test' => 'pp_test',
    'pp test' => 'pp_test',
    'warna' => 'warna',
    'color' => 'warna',
    'bau' => 'bau',
    'odor' => 'bau',
    'kekeruhan' => 'kejernihan',
    'kejernihan' => 'kejernihan',
    'jernih' => 'kejernihan',
    'turbidity' => 'kejernihan',
    'clarity' => 'kejernihan',
    'blood' => 'eritrosit',
    'darah' => 'eritrosit',
    'eritrosit' => 'eritrosit',
    'erythrocyte' => 'eritrosit',
    'urobilinogen' => 'urobilinogen',
    'bilirubin' => 'bilirubin',
    'protein' => 'protein',
    'nitrit' => 'nitrat',
    'nitrat' => 'nitrat',
    'nitrite' => 'nitrat',
    'keton' => 'keton',
    'ketone' => 'keton',
    'ketones' => 'keton',
    'glukosa' => 'glukosa',
    'glukose' => 'glukosa',
    'glucose' => 'glukosa',
    'reduksi' => 'glukosa',
    'ph' => 'ph',
    'bj' => 'berat_jenis',
    'leukosit' => 'leu',
    'lekosit' => 'leu',
    'leukocyte' => 'leu',
    'leu' => 'leu',
    'ppt' => 'pp_test',
    'hcg' => 'pp_test',
  ];

  /**
   * Nama parameter sedimen -> kolom blok SEDIMEN URINE pada rekap haji.
   */
  const KOLOM_SEDIMEN_URIN_HAJI = [
    'sel epitel' => 'epitel',
    'lain lain' => 'lain2',
    'epitel' => 'epitel',
    'epithelial' => 'epitel',
    'leukosit' => 'leu_sedimen',
    'lekosit' => 'leu_sedimen',
    'leukocyte' => 'leu_sedimen',
    'leu' => 'leu_sedimen',
    'eritrosit' => 'ery',
    'erythrocyte' => 'ery',
    'ery' => 'ery',
    'silinder' => 'cyli',
    'cylinder' => 'cyli',
    'cyli' => 'cyli',
    'cast' => 'cyli',
    'kristal' => 'kristal',
    'crystal' => 'kristal',
    'bakteri' => 'lain2',
    'bacteria' => 'lain2',
    'lain2' => 'lain2',
    'lainnya' => 'lain2',
    'other' => 'lain2',
  ];

  /**
   * Jenis pemeriksaan yang tidak pernah masuk rekap urin walaupun nama parameternya sama
   * (mis. Eritrosit/Leukosit darah rutin, Protein & Glukosa kimia klinik).
   */
  const JENIS_BUKAN_URIN_HAJI = [
    'darah rutin',
    'hematologi',
    'hitung jenis',
    'kimia klinik',
    'widal',
    'feses',
    'bakteriologi',
    'molekuler',
    'fisik diagnostik',
  ];

  /**
   * Nama jenis pemeriksaan (Urin Rutin, Kimia Urin, Sedimen, ...) dari baris parameter permohonan.
   */
  private function jenisPemeriksaanNameHaji($param): string
  {
    $name = $param->jenisparameterklinik->name_parameter_jenis_klinik ?? null;

    if (empty($name)) {
      $name = $param->parametersatuanklinik->parameterjenisklinik->name_parameter_jenis_klinik ?? null;
    }

    return strtolower(trim((string) $name));
  }

  /**
   * Hanya parameter bersampel urin yang boleh mengisi rekap urin rutin.
   */
  private function isParameterSampelUrinHaji($param): bool
  {
    $satuan = $param->parametersatuanklinik ?? null;
    if (!$satuan) {
      return false;
    }

    $jenis = $this->jenisPemeriksaanNameHaji($param);

    if ($jenis !== '') {
      if (strpos($jenis, 'urin') !== false || strpos($jenis, 'sedimen') !== false) {
        return true;
      }

      foreach (self::JENIS_BUKAN_URIN_HAJI as $blokir) {
        if (strpos($jenis, $blokir) !== false) {
          return false;
        }
      }
    }

    $raw = Smt::pickJenisSampelRawForContext($satuan, 1);
    $raw = is_array($raw) ? $raw : [$raw];

    foreach ($raw as $sampel) {
      if (strpos(strtolower(trim((string) $sampel)), 'urin') !== false) {
        return true;
      }
    }

    return false;
  }

  private function isParameterSedimenUrinHaji($param): bool
  {
    if (strpos($this->jenisPemeriksaanNameHaji($param), 'sedimen') !== false) {
      return true;
    }

    $name = strtolower((string) ($param->parametersatuanklinik->name_parameter_satuan_klinik ?? ''));

    return strpos($name, 'sedimen') !== false;
  }

  /**
   * Cocokkan nama parameter ke kolom rekap: exact dulu, lalu pencocokan per kata
   * supaya "Lymphosit" tidak pernah jatuh ke kolom pH.
   */
  private function resolveKolomUrinRutinHaji(string $rawName, bool $isSedimen)
  {
    $name = strtolower(trim($rawName));
    $name = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', $name)));

    if ($name === '') {
      return null;
    }

    $map = $isSedimen ? self::KOLOM_SEDIMEN_URIN_HAJI : self::KOLOM_URIN_RUTIN_HAJI;

    if (isset($map[$name])) {
      return $map[$name];
    }

    foreach ($map as $key => $kolom) {
      if (preg_match('/\b' . preg_quote($key, '/') . '\b/', $name)) {
        return $kolom;
      }
    }

    return null;
  }

  /**
   * Hasil bisa tersimpan ber-HTML entity (mis. "&gt;100"); tanpa decode, Excel menampilkan
   * "&gt;100" karena Blade meng-escape sekali lagi.
   */
  private function normalizeHasilUrinRutinHaji($hasil): string
  {
    if ($hasil === null) {
      return '';
    }

    $plain = strip_tags((string) $hasil);

    for ($i = 0; $i < 3 && strpos($plain, '&') !== false; $i++) {
      $decoded = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      if ($decoded === $plain) {
        break;
      }
      $plain = $decoded;
    }

    $plain = trim((string) preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $plain)));

    // Tanda abnormal ditambahkan lagi saat render, jangan sampai dobel.
    return trim((string) preg_replace('/\s*\*+\s*$/u', '', $plain));
  }

  /**
   * Berat jenis urin selalu 3 desimal (1.010, bukan 1.01) dan ditulis sebagai teks
   * supaya Excel tidak memangkas nol di belakang.
   */
  private function formatBeratJenisUrinHaji(string $value): string
  {
    $numeric = str_replace(',', '.', $value);

    if (!is_numeric($numeric)) {
      return $value;
    }

    return number_format((float) $numeric, 3, '.', '');
  }

  private function assignHasilUrinRutinHaji(array &$rowData, array &$bobotKolom, string $rawName, $hasil, bool $isSedimen): void
  {
    $value = $this->normalizeHasilUrinRutinHaji($hasil);
    if ($value === '' || $value === '-') {
      return;
    }

    $kolom = $this->resolveKolomUrinRutinHaji($rawName, $isSedimen);
    if ($kolom === null) {
      return;
    }

    if ($kolom === 'berat_jenis') {
      $value = $this->formatBeratJenisUrinHaji($value);
    }

    // Kolom Lain2 memakai keterangan bakteri sebagai rujukan, jadi Bakteri menang atas Lain-lain.
    $bobot = ($kolom === 'lain2' && stripos($rawName, 'bakteri') !== false) ? 2 : 1;

    if (isset($bobotKolom[$kolom]) && $bobotKolom[$kolom] >= $bobot) {
      return;
    }

    $rowData[$kolom] = $value;
    $bobotKolom[$kolom] = $bobot;
  }

  /**
   * Normalisasi tanggal lahir pasien (dd/mm/yyyy atau Y-m-d) ke format Y-m-d.
   */
  private function normalizePasienTglLahir($value)
  {
    if (empty($value)) {
      return null;
    }

    $value = trim((string) $value);

    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
      return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    }

    try {
      return Carbon::parse($value)->format('Y-m-d');
    } catch (\Exception $e) {
      return $value;
    }
  }

  /**
   * Cek apakah session/form parameter haji kosong.
   */
  private function isHajiParametersEmpty($parameters)
  {
    if (empty($parameters) || !is_array($parameters)) {
      return true;
    }

    foreach ($parameters as $data) {
      if (!is_array($data) || empty($data['pakets'])) {
        continue;
      }

      $pakets = is_array($data['pakets']) ? $data['pakets'] : [$data['pakets']];
      if (count(array_filter($pakets)) > 0) {
        return false;
      }
    }

    return true;
  }

  /**
   * Ambil parameter dari session step 2 (sumber utama pilihan user).
   */
  private function resolveStoredHajiParameters(Request $request)
  {
    $hajiId = $request->input('haji_id');
    if ($hajiId && session('haji_id') === $hajiId) {
      $sessionParams = session('haji_parameters', []);
      if (is_array($sessionParams)) {
        return $sessionParams;
      }
    }

    return [];
  }

  /**
   * Ambil paket extra dari session step 2.
   */
  private function resolveStoredHajiPaketExtra(Request $request)
  {
    $hajiId = $request->input('haji_id');
    if ($hajiId && session('haji_id') === $hajiId) {
      $paketExtra = session('haji_paket_extra', []);
      if (is_array($paketExtra)) {
        return $paketExtra;
      }
    }

    return [];
  }

  /**
   * Ambil pilihan parameter dari permohonan yang sudah ada (paket individu).
   * Legacy "Paket Haji" di-expand ke paket layout berdasarkan satuan tersimpan.
   */
  private function buildHajiParametersFromPermohonan(PermohonanUjiKlinik2 $permohonan)
  {
    $pakets = HajiPaketHelper::resolveLayoutPaketsFromPermohonan($permohonan);

    return HajiPaketHelper::buildJenisParametersSession($pakets);
  }

  /**
   * Parameter default: paket mayoritas di batch haji ini,
   * fallback ke mayoritas customer haji lain yang sudah ada.
   */
  private function buildHajiParametersFromMajority(?string $hajiId = null): array
  {
    $pakets = collect();

    if (!empty($hajiId)) {
      $pakets = HajiPaketHelper::resolveMajorityLayoutPaketsForHaji($hajiId);
    }

    if ($pakets->isEmpty()) {
      $pakets = HajiPaketHelper::resolveMajorityLayoutPaketsFromExistingHaji($hajiId);
    }

    if ($pakets->isEmpty()) {
      return [];
    }

    return HajiPaketHelper::buildJenisParametersSession($pakets);
  }

  /**
   * Buang master komposit "Paket Haji" dan varian BPJS/Klaim dari payload form/session.
   */
  private function stripCompositePaketHajiFromParameters($parameters)
  {
    if (!is_array($parameters) || empty($parameters)) {
      return [];
    }

    $cleaned = [];
    foreach ($parameters as $paketId => $data) {
      $paket = ParameterPaketKlinik::find($paketId);
      if ($paket && (
        HajiPaketHelper::isCompositePaketHajiName($paket->name_parameter_paket_klinik)
        || HajiPaketHelper::isBillingVariantPaketName((string) $paket->name_parameter_paket_klinik)
      )) {
        continue;
      }

      if (!isset($data['pakets']) || !is_array($data['pakets'])) {
        continue;
      }

      $pakets = [];
      foreach ($data['pakets'] as $paketValue) {
        $parts = explode('_', (string) $paketValue);
        $id = $parts[0] ?? '';
        if ($id === '') {
          continue;
        }
        $row = ParameterPaketKlinik::find($id);
        if ($row && (
          HajiPaketHelper::isCompositePaketHajiName($row->name_parameter_paket_klinik)
          || HajiPaketHelper::isBillingVariantPaketName((string) $row->name_parameter_paket_klinik)
        )) {
          continue;
        }
        $pakets[] = $paketValue;
      }

      if (!empty($pakets)) {
        $cleaned[$paketId] = ['pakets' => $pakets];
      }
    }

    return $cleaned;
  }

  /**
   * Array paket untuk checkbox checked di view step 2.
   */
  private function buildHajiPaketArrayForView(array $parametersSession)
  {
    $paketArray = [];

    foreach ($parametersSession as $paketId => $data) {
      $paket = ParameterPaketKlinik::find($paketId);
      if ($paket) {
        $paketArray[] = [
          'name_parameter_paket_klinik' => $paket->name_parameter_paket_klinik,
        ];
      }
    }

    return $paketArray;
  }

  /**
   * Format nomor register untuk tampilan daftar haji.
   * Format: {number sampel} / {number lab}
   */
  private function formatHajiDisplayNoregister(PermohonanUjiKlinik2 $row, $settings = null)
  {
    return $row->getDisplayNoregister($settings);
  }

  /**
   * Ambil alamat pasien dari kolom Alamat di Excel (bukan Tgl Lahir / formula TEXT).
   * Template resmi: kolom H. Template lama tanpa "Tgl Lahir (String)": kolom G, hanya jika bukan tanggal.
   */
  private function resolveAlamatPasienFromHajiExcelRow(array $row, array $excelCols, $tglLahir = null): ?string
  {
    // Utama: kolom Alamat template resmi
    if (isset($excelCols['alamat'])) {
      $alamatIdx = (int) $excelCols['alamat'];
      if (array_key_exists($alamatIdx, $row)) {
        $sanitized = \Smt\Masterweb\Helpers\Smt::sanitizeAlamatPasien($row[$alamatIdx], $tglLahir);
        if ($sanitized !== null && $sanitized !== '') {
          return $sanitized;
        }
      }
    }

    // Template lama: tanpa kolom "Tgl Lahir (String)", Alamat bergeser ke index G.
    // Jangan pernah pakai isi kolom G jika terlihat tanggal lahir / formula TEXT.
    if (isset($excelCols['tgl_lahir_string'])) {
      $legacyIdx = (int) $excelCols['tgl_lahir_string'];
      if (array_key_exists($legacyIdx, $row)) {
        $raw = $row[$legacyIdx];
        $rawTrim = trim((string) $raw);
        if (
          $rawTrim !== ''
          && strpos($rawTrim, '=') !== 0
          && !\Smt\Masterweb\Helpers\Smt::isAlamatPasienTanggalLahir($raw, $tglLahir)
        ) {
          $sanitized = \Smt\Masterweb\Helpers\Smt::sanitizeAlamatPasien($raw, $tglLahir);
          if ($sanitized !== null && $sanitized !== '') {
            return $sanitized;
          }
        }
      }
    }

    return null;
  }

  /**
   * Peta indeks kolom format Excel haji (0-based).
   */
  private function getHajiExcelColumnMap()
  {
    $settings = KlinikNumberSettings::getSettings();
    $columns = [
      'no' => 0,
      'nama' => 1,
      'nik' => 2,
      'kelamin' => 3,
      'tempat_lahir' => 4,
      'tgl_lahir' => 5,
      'tgl_lahir_string' => 6,
      'alamat' => 7,
      'pekerjaan' => 8,
      'settings' => $settings,
    ];

    $nextIndex = 9;
    if ($settings->is_nomor_lab_manual) {
      $columns['nomor_lab'] = $nextIndex++;
    }
    if ($settings->is_nomor_spesimen_manual) {
      $columns['nomor_spesimen'] = $nextIndex++;
    }

    return $columns;
  }

  /**
   * Daftar petugas registrasi (role 1 / kolom register verification activity).
   * Sama sumbernya dengan form /elits-permohonan-uji-klinik-2/create.
   */
  private function getPetugasAdministrasi()
  {
    $petugasPenerima = [];

    $verificationActivities = VerificationActivity::all();
    foreach ($verificationActivities as $activity) {
      if (!empty($activity->register) && $activity->register !== '-' && $activity->register !== 'NULL') {
        $names = explode(', ', $activity->register);
        foreach ($names as $name) {
          $name = trim($name);
          if (
            !empty($name)
            && LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($name, $petugasPenerima) === null
          ) {
            $petugasPenerima[] = $name;
          }
        }
      }
    }

    $petugasWithRole1 = Petugas::whereNotNull('role')->get();
    foreach ($petugasWithRole1 as $petugas) {
      $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
      if (is_array($roles) && in_array('1', $roles)) {
        $nama = trim($petugas->nama);
        if (
          !empty($nama)
          && LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($nama, $petugasPenerima) === null
        ) {
          $petugasPenerima[] = $nama;
        }
      }
    }

    sort($petugasPenerima);

    return $petugasPenerima;
  }

  /**
   * Simpan nomor lab/spesimen manual dari import Excel ke session (wizard step 3).
   */
  private function storeHajiImportPasienMeta($pasienId, $nomorLabManual, $nomorSpesimenManual, $settings, $isExistingSystem = false)
  {
    $meta = [];
    $nomorLabManual = $this->normalizeHajiManualNumber($nomorLabManual);
    $nomorSpesimenManual = $this->normalizeHajiManualNumber($nomorSpesimenManual);

    if ($nomorLabManual !== '') {
      $meta['nomor_lab_manual'] = $nomorLabManual;
    }
    if ($settings->is_nomor_spesimen_manual && $nomorSpesimenManual !== '') {
      $meta['nomor_spesimen_manual'] = $nomorSpesimenManual;
    }
    if ($isExistingSystem) {
      $meta['is_existing_system'] = 1;
    }

    if (empty($meta)) {
      return;
    }

    $importMeta = session('haji_import_pasien_meta', []);
    if (!is_array($importMeta)) {
      $importMeta = [];
    }

    $importMeta[$pasienId] = array_merge($importMeta[$pasienId] ?? [], $meta);
    session(['haji_import_pasien_meta' => $importMeta]);
  }

  /**
   * Bersihkan nomor lab/sample manual (hanya digit).
   */
  private function normalizeHajiManualNumber($value): string
  {
    return preg_replace('/\D+/', '', trim((string) $value));
  }

  /**
   * Alokasi nomor urut dari GlobalLabSequence (selaras create klinik).
   *
   * @return array{set_count:int,seq_year:int,klinik_lab_id:?string}
   */
  private function allocateHajiKlinikSequenceNumber($registerDate = null): array
  {
    $seqYear = GlobalLabSequence::resolveYear($registerDate ?? date('Y'));
    $startNum = StartNum::where('code_lab_start_number', 'KLI')->first();
    $currentGlobal = GlobalLabSequence::getCurrentNumber($seqYear);

    if ($currentGlobal == 0 && $startNum && $seqYear == ($startNum->year_start_number ?? $seqYear)) {
      GlobalLabSequence::raiseLastNumberToAtLeast((int) ($startNum->count_start_number ?? 0), $seqYear);
    }

    $labKlinik = Laboratorium::where('kode_laboratorium', 'KLI')->first();
    $klinikLabId = $labKlinik ? $labKlinik->id_laboratorium : null;
    $setCount = (int) GlobalLabSequence::getNextNumber($seqYear, $klinikLabId, 'klinik', null);

    return [
      'set_count' => $setCount,
      'seq_year' => (int) $seqYear,
      'klinik_lab_id' => $klinikLabId,
    ];
  }

  /**
   * Link detail sequence ke id permohonan klinik (setelah save).
   */
  private function linkHajiKlinikSequenceDetail(int $setCount, int $seqYear, string $permohonanId): void
  {
    $sequenceDetail = GlobalLabSequenceDetail::where('sequence_number', $setCount)
      ->where('year', $seqYear)
      ->where('lab_type', 'klinik')
      ->where(function ($q) {
        $q->whereNull('reference_id')->orWhere('reference_id', '');
      })
      ->orderBy('created_at', 'desc')
      ->first();

    if ($sequenceDetail) {
      $sequenceDetail->update(['reference_id' => $permohonanId]);
    }
  }

  /**
   * Nomor lab berikutnya: gabungan kesmas + klinik (bukan GlobalLabSequence spesimen).
   */
  private function resolveNextHajiLabNumber(int $year, ?int $minExclusive = null): int
  {
    $next = (int) NomerLabSequence::peekNextNumber($year);
    if ($minExclusive !== null) {
      $next = max($next, ((int) $minExclusive) + 1);
    }

    return max(1, $next);
  }

  /**
   * Ambil nomor lab unik untuk batch haji/import.
   * Jika preferred sudah dipakai (Excel duplikat / bentrok DB), naikkan ke slot kosong.
   *
   * @param  int|null  $labAutoCursor  di-update by-ref ke nomor yang dipakai
   */
  private function resolveUniqueHajiLabNumber(int $year, $preferred, ?int &$labAutoCursor, ?string $excludePermohonanId = null): string
  {
    $unique = NomerLabSequence::resolveUniqueLabNumber(
      $preferred,
      $year,
      $excludePermohonanId,
      $labAutoCursor
    );
    $labAutoCursor = $unique;

    return (string) $unique;
  }

  /**
   * Terapkan nomor lab/spesimen ke permohonan klinik haji (selaras klinik-number-settings).
   * - Spesimen otomatis → noregister = set_count (global), abaikan input Excel/form
   * - Spesimen manual → input Excel/form (unik; duplikat di-bump)
   * - Lab → selalu unik per batch/DB (duplikat Excel di-bump)
   *
   * @param  int|null  $labAutoCursor  cursor batch untuk lab (di-update by-ref)
   */
  private function applyHajiManualNumbersToPermohonan(
    PermohonanUjiKlinik2 $permohonan,
    $setCountOrLegacyCode,
    $nomorLabManual,
    $nomorSpesimenManual,
    $settings,
    ?int $seqYear = null,
    ?int &$labAutoCursor = null
  ) {
    $isNomorLabManual = (bool) ($settings->is_nomor_lab_manual ?? false);
    $isNomorSpesimenManual = (bool) ($settings->is_nomor_spesimen_manual ?? false);

    $setCount = is_numeric($setCountOrLegacyCode)
      ? (int) $setCountOrLegacyCode
      : (int) preg_replace('/\D+/', '', (string) explode('/', (string) $setCountOrLegacyCode)[0]);

    if ($setCount < 1) {
      $setCount = 1;
    }

    $year = $seqYear ?? GlobalLabSequence::resolveYear(
      $permohonan->tglregister_permohonan_uji_klinik ?? $permohonan->created_at ?? date('Y')
    );

    $nomorLabManual = $this->normalizeHajiManualNumber($nomorLabManual);
    $nomorSpesimenManual = $this->normalizeHajiManualNumber($nomorSpesimenManual);

    // Spesimen: otomatis dari global set_count; manual unik terhadap DB
    if ($isNomorSpesimenManual && $nomorSpesimenManual !== '') {
      $finalNomorSpesimen = (string) GlobalLabSequence::resolveUniqueSpesimenNumber(
        $nomorSpesimenManual,
        (int) $year,
        $permohonan->id_permohonan_uji_klinik ?? null
      );
    } else {
      $finalNomorSpesimen = (string) $setCount;
    }

    // Lab: selalu unik dalam batch + terhadap DB (cegah duplikat dari Excel manual)
    $finalLab = $this->resolveUniqueHajiLabNumber(
      (int) $year,
      $nomorLabManual,
      $labAutoCursor,
      $permohonan->id_permohonan_uji_klinik ?? null
    );

    $permohonan->nourut_permohonan_uji_klinik = $setCount;
    $permohonan->noregister_permohonan_uji_klinik = $finalNomorSpesimen;
    $permohonan->nomor_lab_manual = $finalLab;
    if (\Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab') && (int) $finalLab > 0) {
      $permohonan->nomer_lab = (int) $finalLab;
    }
    $permohonan->nomor_spesimen_manual = ($isNomorSpesimenManual && $finalNomorSpesimen !== '')
      ? $finalNomorSpesimen
      : null;
    $permohonan->is_nomor_lab_manual = $isNomorLabManual ? 1 : 0;
    $permohonan->is_nomor_spesimen_manual = $isNomorSpesimenManual ? 1 : 0;
  }

  /**
   * Form edit massal nama customer (puskesmas) + dokter pengirim untuk satu rombongan haji.
   */
  public function editCustomerDokterMassal($id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    $pasienQuery = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
      ->whereNull('deleted_at');

    $jumlahPasien = (clone $pasienQuery)->count();

    $dokterGroups = (clone $pasienQuery)
      ->select(
        DB::raw("TRIM(COALESCE(nama_dokter_pengirim_permohonan_uji_klinik, '')) as nama_dokter_pengirim_permohonan_uji_klinik"),
        DB::raw('COUNT(*) as total')
      )
      ->groupBy(DB::raw("TRIM(COALESCE(nama_dokter_pengirim_permohonan_uji_klinik, ''))"))
      ->orderByDesc('total')
      ->get();

    // Mayoritas = nama dengan jumlah pasien terbanyak (boleh kosong).
    $dokterUtama = trim((string) (optional($dokterGroups->first())->nama_dokter_pengirim_permohonan_uji_klinik ?? ''));
    $dokterUtamaTotal = (int) (optional($dokterGroups->first())->total ?? 0);

    // Jika mayoritas kosong tapi ada dokter terisi, tampilkan juga kandidat non-kosong terbanyak.
    $dokterNonKosong = $dokterGroups->first(function ($g) {
      return trim((string) ($g->nama_dokter_pengirim_permohonan_uji_klinik ?? '')) !== '';
    });
    $dokterNonKosongNama = $dokterNonKosong
      ? trim((string) $dokterNonKosong->nama_dokter_pengirim_permohonan_uji_klinik)
      : '';
    $dokterNonKosongTotal = $dokterNonKosong ? (int) $dokterNonKosong->total : 0;

    $customer = Customer::where('name_customer', $haji->nama_haji)
      ->whereNull('deleted_at')
      ->first();

    $customers = Customer::whereNull('deleted_at')
      ->orderBy('name_customer', 'asc')
      ->get(['id_customer', 'name_customer', 'address_customer']);

    return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.edit-customer-dokter-massal', [
      'haji' => $haji,
      'jumlahPasien' => $jumlahPasien,
      'dokterUtama' => $dokterUtama,
      'dokterUtamaTotal' => $dokterUtamaTotal,
      'dokterNonKosongNama' => $dokterNonKosongNama,
      'dokterNonKosongTotal' => $dokterNonKosongTotal,
      'dokterGroups' => $dokterGroups,
      'customer' => $customer,
      'customers' => $customers,
    ]);
  }

  /**
   * Simpan edit massal: update nama_haji (+ customer terkait) dan dokter pengirim semua pasien.
   */
  public function updateCustomerDokterMassal(Request $request, $id)
  {
    $haji = PermohonanUjiKlinikHaji::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'nama_customer' => 'required|string|max:255',
      'nama_dokter_pengirim' => 'nullable|string|max:255',
      'customer_id' => 'nullable|string',
      'update_customer_master' => 'nullable|in:0,1',
    ], [
      'nama_customer.required' => 'Nama customer / puskesmas wajib diisi.',
    ]);

    if ($validator->fails()) {
      return redirect()
        ->back()
        ->withErrors($validator)
        ->withInput();
    }

    $namaCustomerBaru = trim((string) $request->input('nama_customer'));
    $namaDokterBaru = trim((string) $request->input('nama_dokter_pengirim', ''));
    $customerId = $request->input('customer_id');
    $updateCustomerMaster = (string) $request->input('update_customer_master', '1') === '1';
    $namaLama = trim((string) $haji->nama_haji);

    DB::beginTransaction();
    try {
      $customer = null;
      if ($customerId) {
        $customer = Customer::where('id_customer', $customerId)->whereNull('deleted_at')->first();
      }
      if (!$customer) {
        $customer = Customer::where('name_customer', $namaLama)->whereNull('deleted_at')->first();
      }

      $haji->nama_haji = $namaCustomerBaru;
      $haji->save();

      if ($customer && $updateCustomerMaster) {
        $customer->name_customer = $namaCustomerBaru;
        $customer->save();
      }

      $pasienUpdated = 0;
      if ($namaDokterBaru !== '') {
        $pasienUpdated = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik_haji', $id)
          ->whereNull('deleted_at')
          ->update([
            'nama_dokter_pengirim_permohonan_uji_klinik' => $namaDokterBaru,
            'updated_at' => now(),
          ]);
      }

      DB::commit();

      $pesan = 'Data rombongan haji berhasil diperbarui.';
      if ($namaLama !== $namaCustomerBaru) {
        $pesan .= ' Nama customer: "' . $namaLama . '" → "' . $namaCustomerBaru . '".';
      }
      if ($namaDokterBaru !== '') {
        $pesan .= ' Dokter pengirim diterapkan ke ' . $pasienUpdated . ' pasien.';
      }

      return redirect()
        ->route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $id)
        ->with('success', $pesan);
    } catch (\Throwable $e) {
      DB::rollBack();

      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
  }

  /**
   * Get Roman month
   */
  private function getRomanMonth()
  {
    $month = date('n');
    $romans = [
      1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
      7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
    ];
    return $romans[$month] ?? 'I';
  }

}