<?php



namespace Smt\Masterweb\Http\Controllers;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

use Smt\Masterweb\Exports\RegisterPendaftaranKlinikExport;

use Smt\Masterweb\Exports\RegisterPendaftaranNonKlinikExport;

use Illuminate\Support\Str;

use Smt\Masterweb\Models\Laboratorium;



class LaboratoriumRegisterPendaftaranExportController extends Controller

{

  public function __construct()

  {

    $this->middleware('auth');

  }



  /**

   * REGISTER PENDAFTARAN: 12 sheet / tahun. Jenis ekspor dipilih dari {@see Laboratorium::kode_laboratorium}

   * (KLI → klinik / PermohonanUjiKlinik2, selain itu → non-klinik).

   *

   * Query: year (wajib), laboratorium_id (wajib), month (opsional, 1–12 = satu sheet bulan),
   * date (opsional, Y-m-d = satu sheet hari tersebut, untuk laporan harian)

   */

  public function exportRegister(Request $request)

  {

    $request->validate([

      'year' => ['required', 'integer', 'min:2000', 'max:2100'],

      'laboratorium_id' => ['required', 'string'],

      'month' => ['nullable', 'integer', 'min:1', 'max:12'],

      'date' => ['nullable', 'date_format:Y-m-d'],

    ]);



    $labId = $request->input('laboratorium_id');

    $date = $request->filled('date') ? $request->input('date') : null;

    $month = $request->filled('month') ? (int) $request->input('month') : null;

    $year = (int) $request->input('year');

    if ($date) {

      $year = (int) substr($date, 0, 4);

      $month = (int) substr($date, 5, 2);

    }



    $lab = Laboratorium::find($labId);

    if (!$lab) {

      abort(404, 'Laboratorium tidak ditemukan.');

    }



    $slug = Str::slug($lab->nama_laboratorium ?? 'laboratorium');

    $isKlinik = ($lab->kode_laboratorium ?? '') === 'KLI';

    if ($date) {

      $periodSlug = '_' . str_replace('-', '', $date);

    } elseif ($month) {

      $periodSlug = '_' . Str::slug(fbulan($month));

    } else {

      $periodSlug = '';

    }



    if ($isKlinik) {

      $unitLabel = 'Klinis';

      $filename = 'register_pendaftaran_' . $slug . '_' . $year . $periodSlug . '_klinik.xlsx';



      return Excel::download(

        new RegisterPendaftaranKlinikExport($year, $labId, $unitLabel, $month, $date),

        $filename

      );

    }



    $unitLabel = $lab->nama_laboratorium ?? 'Laboratorium';

    $filename = 'register_pendaftaran_' . $slug . '_' . $year . $periodSlug . '.xlsx';



    return Excel::download(

      new RegisterPendaftaranNonKlinikExport($year, $labId, $unitLabel, $month, $date),

      $filename

    );

  }



  /**

   * Alias URL lama → {@see exportRegister}.

   */

  public function exportExcel(Request $request)

  {

    return $this->exportRegister($request);

  }



  /**

   * Alias URL lama → {@see exportRegister}.

   */

  public function exportExcelKlinik(Request $request)

  {

    return $this->exportRegister($request);

  }

}

