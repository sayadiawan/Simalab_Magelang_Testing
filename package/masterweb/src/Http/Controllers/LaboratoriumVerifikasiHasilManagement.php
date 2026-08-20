<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\SampleResult;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Container;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\LaboratoriumMethod;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\PenerimaanSample;
use \Smt\Masterweb\Models\VerifikasiHasil;
use \Smt\Masterweb\Models\Unit;
use \Smt\Masterweb\Models\LHU;
use \Smt\Masterweb\Models\SampleResultDetail;
use \Smt\Masterweb\Models\JenisMakanan;


use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;
use DB;

use Mapper;


use Carbon\Carbon;
use Smt\Masterweb\Models\SampleTypeDetail;
use \Smt\Masterweb\Models\VerificationActivitySample;

class LaboratoriumVerifikasiHasilManagement extends Controller
{
  /**
   * Samakan dengan LaboratoriumAnalitikSampleManagement: jangan terapkan override sampel
   * bila user membuka halaman dengan jenis_makanan_id berbeda dari yang tersimpan (preview).
   */
  protected function shouldApplyBakuMutuSampleOverrides(Request $request, Sample $sample, $jenis_makanan_id, bool $isKimiaMakanan, bool $isMbiMakanan): bool
  {
    if (!$isKimiaMakanan && !$isMbiMakanan) {
      return true;
    }
    if (!$request->has('jenis_makanan_id')) {
      return true;
    }
    $normalize = static function ($v) {
      if ($v === null || $v === '') {
        return '';
      }

      return (string) $v;
    };

    $sampleJenis = $normalize($sample->jenis_makanan_id);
    if ($sampleJenis === '') {
      return true;
    }

    return $sampleJenis === $normalize($jenis_makanan_id);
  }

  /**
   * Progress ID untuk tb_baku_mutu_sample_override harus selaras dengan baca-hasil:
   * LaboratoriumAnalitikSampleManagement memuat override dengan where('sample_progress_id', $progress)
   * ($progress dari URL = langkah link "baca-hasil"). Sertakan juga ID langkah baca-hasil lab
   * dan id_sample_analitik_progress agar cetak/verifikasi tetap membaca override sampel.
   */
  protected function bacaHasilProgressIdsForSample(string $sampleId, string $laboratoriumId): \Illuminate\Support\Collection
  {
    $bacaHasilProgressIds = LaboratoriumProgress::query()
      ->where('laboratorium_id', $laboratoriumId)
      ->where('link', 'baca-hasil')
      ->whereNull('deleted_at')
      ->pluck('id_laboratorium_progress');

    $analitikQuery = \Smt\Masterweb\Models\SampleAnalitikProgress::query()
      ->where('sample_id', $sampleId)
      ->where('laboratorium_id', $laboratoriumId)
      ->whereNull('deleted_at');

    if ($bacaHasilProgressIds->isNotEmpty()) {
      $analitikQuery->whereIn('laboratorium_progress_id', $bacaHasilProgressIds->all());
    } else {
      $analitikQuery->whereIn('laboratorium_progress_id', function ($sub) use ($laboratoriumId) {
        $sub->select('id_laboratorium_progress')
          ->from('tb_laboratorium_progress')
          ->where('laboratorium_id', $laboratoriumId)
          ->where('link', 'baca-hasil')
          ->whereNull('deleted_at');
      });
    }

    $analitikRows = $analitikQuery->get(['id_sample_analitik_progress', 'laboratorium_progress_id']);

    return $bacaHasilProgressIds
      ->merge($analitikRows->pluck('laboratorium_progress_id'))
      ->merge($analitikRows->pluck('id_sample_analitik_progress'))
      ->merge(collect(['bfecda4a-73f2-47d6-9fc3-01f65e0f02a1', 'bc2850f5-4ec4-450f-a727-2b1428c861d9']))
      ->filter()
      ->unique()
      ->values();
  }

  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index($id, $idlab)
  {

    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          });
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.id_sample_type', 'ms_sample_type.name_sample_type', 'ms_laboratorium.id_laboratorium', 'ms_laboratorium.kode_laboratorium', 'ms_laboratorium.nama_laboratorium')
      ->first();
    // Gunakan typesample_samples dari tb_samples langsung untuk menghindari ambiguitas kolom join
    $sampletype_id = $sample->typesample_samples;
    $jenis_makanan_id = $sample->jenis_makanan_id;
    if (isset($jenis_makanan_id)) {

      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id, $isKimiaMakanan) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $isKimiaMakanan) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
              
              // Untuk KIM: jika jenis_makanan_id null, cari baku mutu dengan jenis_makanan_id null
              // Jika jenis_makanan_id ada, cari baku mutu dengan jenis_makanan_id tersebut
              if ($isKimiaMakanan) {
                if ($jenis_makanan_id === null || $jenis_makanan_id === '') {
                  $join->whereNull('tb_baku_mutu.jenis_makanan_id');
                } else {
                  $join->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id);
                }
              } else {
                // Untuk MBI, selalu gunakan jenis_makanan_id
                $join->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id);
              }
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })



        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
      $laboratoriummethods = collect($laboratoriummethods)->unique('id_method')->values();
    } else {
      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })




        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->distinct('ms_method.id_method')
        ->get();
    }





    // Apply per-sample baku mutu overrides dari tb_baku_mutu_sample_override
    $sampleProgressIds = $this->bacaHasilProgressIdsForSample($id, $idlab);

    if ($sampleProgressIds->isNotEmpty()) {
      $bmOverrides = \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $sampleProgressIds)
        ->get()
        ->keyBy('method_id');

      if ($bmOverrides->isNotEmpty()) {
        $laboratoriummethods = $laboratoriummethods->map(function ($item) use ($bmOverrides) {
          if (isset($bmOverrides[$item->method_id])) {
            $ov = $bmOverrides[$item->method_id];
            if (!is_null($ov->nilai_baku_mutu)) $item->nilai_baku_mutu = $ov->nilai_baku_mutu;
            if (!is_null($ov->min))             $item->min             = $ov->min;
            if (!is_null($ov->max))             $item->max             = $ov->max;
            if (!is_null($ov->equal))           $item->equal           = $ov->equal;
            // Set name_report agar baris merah tidak muncul (parameter dianggap sudah ada baku mutu)
            if (empty($item->name_report)) {
              $item->name_report = $item->params_method ?? $item->name_method ?? '-';
            }
            $item->has_sample_override = true;
          }
          return $item;
        });
      }
    }

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...
      $laboratoriummethods[$key]->detail = array();
      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();
    }





    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();


    return view('masterweb::module.admin.laboratorium.penerimaan-sample.penerimaan', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }






  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create(Request $request, $id, $idlab)
  {
    //get auth user
    Carbon::setLocale('id');

    $user = Auth()->user();
    $sample = Sample::where('tb_samples.id_samples', '=', $id)
      ->where('ms_laboratorium.id_laboratorium', '=', $idlab)
      ->with(['permohonanuji', 'permohonanuji.customer'])
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          });
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.id_sample_type', 'ms_sample_type.name_sample_type', 'ms_laboratorium.id_laboratorium', 'ms_laboratorium.kode_laboratorium', 'ms_laboratorium.nama_laboratorium')
      ->first();
    $sampletype_id = $sample->typesample_samples; // pakai FK langsung dari tb_samples
    
    // Tentukan jenis makanan awal: dari query string (jika ada), jika tidak dari sample
    $userExplicitJenisMakanan = $request->has('jenis_makanan_id');
    $jenis_makanan_id = $request->query('jenis_makanan_id', $sample->jenis_makanan_id);

    // Siapkan daftar jenis makanan yang punya baku mutu untuk parameter yang dipilih
    $jenisMakananAll = collect();
    // Flag untuk KIM: apakah ada baku mutu tanpa jenis makanan
    $hasBakuMutuWithoutJenisMakanan = false;
    
    // Ambil data laboratorium
    $laboratorium = Laboratorium::find($idlab);

    $stName = $sample->name_sample_type ?? '';
    $isSampleTypeMML = str_contains($stName, 'Makanan')
      || str_contains($stName, 'Minuman')
      || str_contains($stName, 'Lainnya');

    $methodIdsForSample = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
      ->where('tb_sample_method.sample_id', '=', $id)
      ->whereNull('tb_sample_method.deleted_at')
      ->pluck('method_id')
      ->unique()
      ->toArray();
    
    // Logika untuk MBI
    if ($laboratorium && $laboratorium->kode_laboratorium === 'MBI' && $isSampleTypeMML) {
      if (!empty($methodIdsForSample)) {
        // Cari jenis_makanan yang punya baku_mutu untuk kombinasi lab, sample type, dan method2 tersebut
        $jenisIds = \Smt\Masterweb\Models\BakuMutu::query()
          ->where('lab_id', $idlab)
          ->where('sampletype_id', $sampletype_id)
          ->whereIn('method_id', $methodIdsForSample)
          ->whereNull('deleted_at')
          ->pluck('jenis_makanan_id')
          ->unique()
          ->filter()
          ->toArray();

        if (!empty($jenisIds)) {
          $jenisMakananAll = \Smt\Masterweb\Models\JenisMakanan::whereIn('id_jenis_makanan', $jenisIds)
            ->orderBy('name_jenis_makanan')
            ->get();
        }
      }

      if ($jenisMakananAll->isNotEmpty()) {
        if (!$userExplicitJenisMakanan &&
          (!$jenis_makanan_id || !$jenisMakananAll->pluck('id_jenis_makanan')->contains($jenis_makanan_id))
        ) {
          $jenis_makanan_id = $jenisMakananAll->first()->id_jenis_makanan;
        }
      }
    }
    
    // Logika untuk KIM (Kimia) - Makanan/Minuman/Lainnya
    if ($laboratorium && $laboratorium->kode_laboratorium === 'KIM' && $isSampleTypeMML) {
      if (!empty($methodIdsForSample)) {
        // Cari jenis_makanan yang punya baku_mutu untuk kombinasi lab, sample type, dan method2 tersebut
        // Untuk KIM, jenis_makanan_id bisa null atau ada nilai
        $jenisIds = \Smt\Masterweb\Models\BakuMutu::query()
          ->where('lab_id', $idlab)
          ->where('sampletype_id', $sampletype_id)
          ->whereIn('method_id', $methodIdsForSample)
          ->whereNull('deleted_at')
          ->pluck('jenis_makanan_id')
          ->unique()
          ->filter() // Filter null values
          ->toArray();

        // Jika ada jenis makanan yang punya baku mutu, tampilkan dropdown
        if (!empty($jenisIds)) {
          $jenisMakananAll = \Smt\Masterweb\Models\JenisMakanan::whereIn('id_jenis_makanan', $jenisIds)
            ->orderBy('name_jenis_makanan')
            ->get();
        }
        
        // Baku mutu generik: jenis_makanan_id NULL atau string kosong
        $hasBakuMutuWithoutJenisMakanan = \Smt\Masterweb\Models\BakuMutu::query()
          ->where('lab_id', $idlab)
          ->where('sampletype_id', $sampletype_id)
          ->whereIn('method_id', $methodIdsForSample)
          ->whereNull('deleted_at')
          ->where(function ($q) {
            $q->whereNull('jenis_makanan_id')->orWhere('jenis_makanan_id', '=', '');
          })
          ->exists();
        
        // Jika ada baku mutu tanpa jenis makanan dan ada juga dengan jenis makanan,
        // atau jika hanya ada baku mutu dengan jenis makanan (lebih dari satu),
        // maka tampilkan dropdown jenis makanan
        if ($jenisMakananAll->isNotEmpty() && ($hasBakuMutuWithoutJenisMakanan || $jenisMakananAll->count() > 1)) {
          if (!$userExplicitJenisMakanan &&
            (!$jenis_makanan_id || !$jenisMakananAll->pluck('id_jenis_makanan')->contains($jenis_makanan_id))
          ) {
            $jenis_makanan_id = $hasBakuMutuWithoutJenisMakanan ? null : $jenisMakananAll->first()->id_jenis_makanan;
          }
        } else if (!$userExplicitJenisMakanan && $jenisMakananAll->count() == 1 && !$hasBakuMutuWithoutJenisMakanan) {
          $jenis_makanan_id = $jenisMakananAll->first()->id_jenis_makanan;
        } else if (!$userExplicitJenisMakanan && !$hasBakuMutuWithoutJenisMakanan && $jenisMakananAll->isEmpty()) {
          $jenis_makanan_id = null;
        }
        // Jika ada baku mutu tanpa jenis makanan dan tidak ada dengan jenis makanan,
        // maka jenis_makanan_id tetap null (tidak perlu dropdown)
      }
    }

    if ($userExplicitJenisMakanan && $jenis_makanan_id) {
      $alreadyInList = $jenisMakananAll->pluck('id_jenis_makanan')->contains($jenis_makanan_id);
      if (!$alreadyInList) {
        $extraJm = JenisMakanan::find($jenis_makanan_id);
        if ($extraJm) {
          $jenisMakananAll = $jenisMakananAll->push($extraJm)->sortBy('name_jenis_makanan')->values();
        }
      }
    }

    // Untuk KIM dengan Makanan/Minuman/Lainnya, gunakan jenis_makanan_id jika ada
    // Untuk MBI dengan Makanan/Minuman/Lainnya, selalu gunakan jenis_makanan_id
    // Untuk lainnya, tidak gunakan jenis_makanan_id
    $isKimiaMakanan = $laboratorium && $laboratorium->kode_laboratorium === 'KIM' && $isSampleTypeMML;
    $isMbiMakanan = $laboratorium && $laboratorium->kode_laboratorium === 'MBI' && $isSampleTypeMML;
    
    if ($isKimiaMakanan || ($isMbiMakanan && $jenis_makanan_id !== null && $jenis_makanan_id !== '')) {

      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id, $isKimiaMakanan) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $isKimiaMakanan, $idlab) {
              if ($isKimiaMakanan && $jenis_makanan_id !== null && $jenis_makanan_id !== '') {
                $join->on('tb_baku_mutu.id_baku_mutu', '=', DB::raw('(
                  SELECT id_baku_mutu
                  FROM tb_baku_mutu bm
                  WHERE bm.method_id = ms_method.id_method
                    AND bm.sampletype_id = ' . DB::getPdo()->quote($sampletype_id) . '
                    AND bm.lab_id = ' . DB::getPdo()->quote($idlab) . '
                    AND bm.deleted_at IS NULL
                    AND (
                      bm.jenis_makanan_id = ' . DB::getPdo()->quote($jenis_makanan_id) . '
                      OR bm.jenis_makanan_id IS NULL
                      OR bm.jenis_makanan_id = \'\'
                    )
                  ORDER BY CASE WHEN bm.jenis_makanan_id = ' . DB::getPdo()->quote($jenis_makanan_id) . ' THEN 0 ELSE 1 END
                  LIMIT 1
                )'));
              } else {
                $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->where('tb_baku_mutu.lab_id', '=', $idlab)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');

                if ($isKimiaMakanan) {
                  $join->whereRaw('(tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)', ['']);
                } else {
                  $join->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id);
                }
              }
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })



        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'ms_method.is_option as method_is_option',
          'ms_method.option as method_option',
          'tb_sample_result.hasil',
          'tb_sample_result.metode',
          'tb_sample_result.keterangan',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          DB::raw('tb_baku_mutu.min as baku_mutu_min'),
          DB::raw('tb_baku_mutu.max as baku_mutu_max'),
          DB::raw('tb_baku_mutu.equal as baku_mutu_equal'),
          DB::raw('tb_baku_mutu.nilai_baku_mutu as baku_mutu_nilai_display'),
          DB::raw('tb_baku_mutu.library_id as baku_mutu_library_id')
        )
        ->orderBy('ms_method.id_method');
      
      // Untuk KIM dengan jenis_makanan_id: urutkan berdasarkan prioritas (yang dengan jenis_makanan_id lebih dulu)
      if ($isKimiaMakanan && $jenis_makanan_id) {
        $laboratoriummethods = $laboratoriummethods->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id]);
      }
      
      $laboratoriummethods = $laboratoriummethods->get();

      // Join ke tb_baku_mutu_detail_parameter_non_klinik menggandakan baris per method; satu baris per parameter.
      if ($isKimiaMakanan && $jenis_makanan_id) {
        $laboratoriummethods = $laboratoriummethods->groupBy('id_method')
          ->map(function ($group) {
            return $group->first();
          })
          ->values();
      } else {
        $laboratoriummethods = $laboratoriummethods->unique('id_method')->values();
      }
    } else {
      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $idlab) {
              $join
                // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                //     LIMIT 1)'))
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
              $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })




        ->select(
          'tb_baku_mutu.*', // Ensure all baku_mutu fields, including lokasi_data, are selected
          'ms_method.*',
          'tb_sample_method.*',
          'unit_baku_mutu.*',
          'ms_method.is_option as method_is_option',
          'ms_method.option as method_option',
          'tb_sample_result.keterangan',
          'tb_sample_result.metode',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          DB::raw('tb_baku_mutu.min as baku_mutu_min'),
          DB::raw('tb_baku_mutu.max as baku_mutu_max'),
          DB::raw('tb_baku_mutu.equal as baku_mutu_equal'),
          DB::raw('tb_baku_mutu.nilai_baku_mutu as baku_mutu_nilai_display'),
          DB::raw('tb_baku_mutu.library_id as baku_mutu_library_id')
        )
        ->distinct('ms_method.id_method')
        ->get();
    }

     //pengurutan order ist
     $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail','ASC')->get();




    $laboratoriummethods = collect($laboratoriummethods)
      ->unique('id_method')
      ->values();

    $laboratoriummethods = kesmas_sort_laboratorium_methods($laboratoriummethods, $sample_type_details);

    // tb_baku_mutu.* lalu ms_method.* menimpa min/max/equal — pakai alias baku_mutu_* lalu remap.
    $laboratoriummethods = $laboratoriummethods->map(function ($item) {
      if (!empty($item->id_baku_mutu)) {
        $item->min = $item->baku_mutu_min;
        $item->max = $item->baku_mutu_max;
        $item->equal = $item->baku_mutu_equal;
        $item->nilai_baku_mutu = $item->baku_mutu_nilai_display;
      }

      return $item;
    });

    // Apply per-sample baku mutu overrides dari tb_baku_mutu_sample_override
    $sampleProgressIds = $this->bacaHasilProgressIdsForSample($id, $idlab);

    $applyBmOverrides = $this->shouldApplyBakuMutuSampleOverrides(
      $request,
      $sample,
      $jenis_makanan_id,
      $isKimiaMakanan,
      $isMbiMakanan
    );

    if ($sampleProgressIds->isNotEmpty()) {
      $bmOverrides = \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $sampleProgressIds)
        ->get()
        ->keyBy('method_id');

      if ($applyBmOverrides && $bmOverrides->isNotEmpty()) {
        $laboratoriummethods = collect($laboratoriummethods)->map(function ($item) use ($bmOverrides) {
          if (isset($bmOverrides[$item->method_id])) {
            $ov = $bmOverrides[$item->method_id];
            if (!is_null($ov->nilai_baku_mutu)) $item->nilai_baku_mutu = $ov->nilai_baku_mutu;
            if (!is_null($ov->min))             $item->min             = $ov->min;
            if (!is_null($ov->max))             $item->max             = $ov->max;
            if (!is_null($ov->equal))           $item->equal           = $ov->equal;
            if (empty($item->name_report)) {
              $item->name_report = $item->params_method ?? $item->name_method ?? '-';
            }
            $item->has_sample_override = true;
          }
          return $item;
        });
      }
    }

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...
      $laboratoriummethods[$key]->detail = array();
      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();
    }



    $verifikasi_hasil = VerifikasiHasil::where('laboratorium_id', $idlab)
      ->where('sample_id', $id)->first();

    // Ambil data laboratorium (jika belum diambil sebelumnya)
    if (!isset($laboratorium)) {
      $laboratorium = Laboratorium::find($idlab);
    }

    // Ambil default nama petugas dari Step 2: Disposisi ke Koordinator Kesmas (dari PenerimaanSample)
    $penerimaan_sample = PenerimaanSample::where('laboratorium_id', $idlab)
      ->where('sample_id', $id)
      ->first();
    $default_analis = null;
    if ($penerimaan_sample && $penerimaan_sample->disposisi_koordinator_kesmas) {
      $default_analis = $penerimaan_sample->disposisi_koordinator_kesmas;
    }

    // Ambil list analis dari VerificationActivity (step 4 = Verifikasi Hasil)
    $analis_list = [];
    $verificationActivity = \Smt\Masterweb\Models\VerificationActivity::all()->keyBy('id')->toArray();
    if (isset($verificationActivity[4])) {
      $activity4 = (object) $verificationActivity[4];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $analis_list = array_filter(explode(', ', $activity4->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $analis_list = array_filter(explode(', ', $activity4->kimia ?? ''));
      } else {
        $analis_list = array_filter(explode(', ', $activity4->klnik ?? ''));
      }
    }

    // Ambil data verifikasi step 4 jika sudah ada untuk default start date dan stop date
    $verifikasi_hasil_verif = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $id)
      ->where('id_verification_activity', 4)
      ->first();

    $default_start_date_verifikasi = null;
    $default_stop_date_verifikasi = null;
    
    if ($verifikasi_hasil_verif) {
      // Jika sudah ada, gunakan data yang sudah tersimpan
      $default_start_date_verifikasi = $verifikasi_hasil_verif->start_date ? Carbon::parse($verifikasi_hasil_verif->start_date) : null;
      $default_stop_date_verifikasi = $verifikasi_hasil_verif->stop_date ? Carbon::parse($verifikasi_hasil_verif->stop_date) : null;
    } else {
      // Jika belum ada, gunakan tanggal sekarang
      $default_start_date_verifikasi = Carbon::now();
      $default_stop_date_verifikasi = Carbon::now();
    }

    return view(
      'masterweb::module.admin.laboratorium.verifikasi-hasil.verifikasi_hasil',
      compact('verifikasi_hasil', 'user', 'sample', 'laboratoriummethods', 'jenis_makanan_id', 'jenisMakananAll', 'hasBakuMutuWithoutJenisMakanan', 'default_analis', 'analis_list', 'default_start_date_verifikasi', 'default_stop_date_verifikasi', 'idlab', 'laboratorium')
    );
    //get all menu public
  }


  function convertToRoman($integer)
  {
    // Convert the integer into an integer (just to make sure)
    $integer = intval($integer);
    $result = '';

    // Create a lookup array that contains all of the Roman numerals.
    $lookup = array(
      'M' => 1000,
      'CM' => 900,
      'D' => 500,
      'CD' => 400,
      'C' => 100,
      'XC' => 90,
      'L' => 50,
      'XL' => 40,
      'X' => 10,
      'IX' => 9,
      'V' => 5,
      'IV' => 4,
      'I' => 1
    );

    foreach ($lookup as $roman => $value) {
      // Determine the number of matches
      $matches = intval($integer / $value);

      // Add the same number of characters to the string
      $result .= str_repeat($roman, $matches);

      // Set the integer to be the remainder of the integer and the value
      $integer = $integer % $value;
    }

    // The Roman numeral should be built, return it
    return $result;
  }




  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $id, $idlabs)
  {
    $data = $request->all();
    $user = Auth()->user();

    $verifikasi_hasil = VerifikasiHasil::where('laboratorium_id', $idlabs)
      ->where('sample_id', $id)->first();

    if (isset($verifikasi_hasil)) {
      $verifikasi_hasil->verifikasi_hasil_date = Carbon::createFromFormat('d/m/Y', $data["verifikasi_hasil"])->format('Y-m-d H:i:s');

      $verifikasi_hasil->save();

      $sample = Sample::where('tb_samples.id_samples', '=', $id)
        ->where('ms_laboratorium.id_laboratorium', '=', $idlabs)
        ->with(['permohonanuji', 'permohonanuji.customer'])
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at')
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            });
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->first();

      // Simpan jenis_makanan_id dan nama_jenis_makanan (untuk sampel makanan/minuman/lainnya)
      $namaJenisInput = $request->input('nama_jenis_makanan');
      if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
        $sample->jenis_makanan_id = $request->get('jenis_makanan_id');
        if ($namaJenisInput === null || $namaJenisInput === '') {
          $jm = JenisMakanan::find($sample->jenis_makanan_id);
          if ($jm) {
            $namaJenisInput = $jm->name_jenis_makanan;
          }
        }
      }
      if ($namaJenisInput !== null && $namaJenisInput !== '') {
        $sample->nama_jenis_makanan = $namaJenisInput;
      }
      $namaJenisInputStore = $request->input('jenis_sarana');

     
      if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
        $sample->jenis_sarana_names = $namaJenisInputStore;
      }

      $sample->save();
      $sampletype_id = $sample->id_sample_type;

      $jenis_makanan_id = $sample->jenis_makanan_id;
      if (isset($jenis_makanan_id)) {

        $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->where('tb_sample_method.sample_id', '=', $id)
          ->orderBy('ms_method.jenis_parameter_kimia')
          ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlabs, $id) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at')
              ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                $join
                  // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                  //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                  //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                  //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                  //     LIMIT 1)'))
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              })
              ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                  ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })

              ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                  ->whereNull('unit_baku_mutu.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })->leftjoin('tb_sample_result', function ($join) use ($id, $idlabs) {
                $join->where('tb_sample_result.laboratorium_id', '=', $idlabs)
                  ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                  ->where('tb_sample_result.sample_id', '=', $id)
                  ->whereNull('tb_sample_result.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              });
          })


          ->select(
            'tb_baku_mutu.*',
            'ms_method.*',
            'tb_sample_method.*',
            'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
      } else {
        $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->where('tb_sample_method.sample_id', '=', $id)
          ->orderBy('ms_method.jenis_parameter_kimia')
          ->join('ms_method', function ($join)   use ($sampletype_id, $idlabs, $id) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at')
              ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id) {
                $join
                  // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                  //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                  //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                  //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                  //     LIMIT 1)'))
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              })
              ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                  ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })

              ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                  ->whereNull('unit_baku_mutu.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })
              ->leftjoin('tb_sample_result', function ($join) use ($id, $idlabs) {
                $join->where('tb_sample_result.laboratorium_id', '=', $idlabs)
                  ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                  ->where('tb_sample_result.sample_id', '=', $id)
                  ->whereNull('tb_sample_result.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              });
          })




          ->select(
            'tb_baku_mutu.*',
            'ms_method.*',
            'tb_sample_method.*',
            'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
      }
      // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
      $selectedRuangan = $request->input('selected_ruangan');

      SampleResult::where("sample_id", $id)
        ->where("laboratorium_id", $idlabs)->delete();
      foreach ($laboratoriummethods as $laboratoriummethod) {


        $sampleresult                   = new SampleResult;
        $uuid4                          = Uuid::uuid4();
        $sampleresult->id_sample_result = $uuid4->toString();
        $sampleresult->method_id        = $laboratoriummethod->method_id;
        $sampleresult->sample_id        = $id;
        $sampleresult->laboratorium_id  = $idlabs;
        if (isset($data["status_" . $laboratoriummethod->method_id])) {
          $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];

          $sampleresult->hasil            = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));
          $sampleresult->metode = $data["metode_" . $laboratoriummethod->method_id];
          $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id];
        } else {
          $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id];
          $sampleresult->hasil            = "-";
        }
        
        // Simpan lokasi_selected untuk Kualitas Udara
        if ($selectedRuangan) {
          $sampleresult->lokasi_selected = $selectedRuangan;
        }
        
        $sampleresult->save();


        $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
          ->where('sampletype_id', '=', $sampletype_id)
          ->where('sample_id', '=',  $id)->get();

        foreach ($sampleresultdetails as $key => $sampleresultdetail) {
          # code...
          $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);
          if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {

            $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
          } else {
            $sampleresultdetail_edit->hasil            = "-";
          }
          $sampleresultdetail_edit->offset_baku_mutu = $data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail];
          $sampleresultdetail_edit->save();
        }
      }
    } else {

      $verifikasi_hasil = new VerifikasiHasil;
      //uuid
      $uuid4 = Uuid::uuid4();

      $verifikasi_hasil->id_verifikasi_hasil = $uuid4->toString();
      $verifikasi_hasil->sample_id = $id;
      $verifikasi_hasil->laboratorium_id = $idlabs;
      $verifikasi_hasil->verifikasi_hasil_date = Carbon::createFromFormat('d/m/Y', $data["verifikasi_hasil"])->format('Y-m-d H:i:s');

      $verifikasi_hasil->save();

      $sample = Sample::where('tb_samples.id_samples', '=', $id)
        ->where('ms_laboratorium.id_laboratorium', '=', $idlabs)
        ->with(['permohonanuji', 'permohonanuji.customer'])
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at')
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            });
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->first();
      $sampletype_id = $sample->id_sample_type;
      $jenis_makanan_id = $sample->jenis_makanan_id;
      $namaJenisInputStore = $request->input('jenis_sarana');
     
      if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
        $sample->nama_jenis_makanan = $namaJenisInputStore;
      }


      $sample->save();
      if (isset($jenis_makanan_id)) {

        $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->where('tb_sample_method.sample_id', '=', $id)
          ->orderBy('ms_method.jenis_parameter_kimia')
          ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at')
              ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                $join
                  // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                  //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                  //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                  //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                  //     LIMIT 1)'))
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              })
              ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                  ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })

              ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                  ->whereNull('unit_baku_mutu.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              });
          })

          ->leftjoin('tb_sample_result', function ($join) use ($id, $idlabs) {
            $join->where('tb_sample_result.laboratorium_id', '=', $idlabs)
              ->where('tb_sample_result.method_id', '=', 'ms_method.id_method')
              ->where('tb_sample_result.sample_id', '=', $id)
              ->whereNull('tb_sample_result.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          })



          ->select(
            'tb_baku_mutu.*',
            'ms_method.*',
            'tb_sample_method.*',
            'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
      } else {
        $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->where('tb_sample_method.sample_id', '=', $id)
          ->orderBy('ms_method.jenis_parameter_kimia')
          ->join('ms_method', function ($join)   use ($sampletype_id) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at')
              ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id) {
                $join
                  // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
                  //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
                  //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
                  //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
                  //     LIMIT 1)'))
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              })
              ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                  ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              })

              ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                  ->whereNull('unit_baku_mutu.deleted_at')
                  ->whereNull('tb_baku_mutu.deleted_at');
              });
          })

          ->leftjoin('tb_sample_result', function ($join) use ($id, $idlabs) {
            $join->where('tb_sample_result.laboratorium_id', '=', $idlabs)
              ->where('tb_sample_result.method_id', '=', 'ms_method.id_method')
              ->where('tb_sample_result.sample_id', '=', $id)
              ->whereNull('tb_sample_result.deleted_at')
              ->whereNull('tb_sample_method.deleted_at');
          })



          ->select(
            'tb_baku_mutu.*',
            'ms_method.*',
            'tb_sample_method.*',
            'unit_baku_mutu.*',
          'tb_sample_result.hasil',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected'
        )
        ->get();
      }

      $namaJenisInputStore = $request->input('jenis_sarana');
    
      if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
        $sample->nama_jenis_makanan = $namaJenisInputStore;
      }

      // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
      $selectedRuangan = $request->input('selected_ruangan');

      $sample->save();
      SampleResult::where("sample_id", $id)
        ->where("laboratorium_id", $idlabs)->delete();
      foreach ($laboratoriummethods as $laboratoriummethod) {


        $sampleresult                   = new SampleResult;
        $uuid4                          = Uuid::uuid4();
        $sampleresult->id_sample_result = $uuid4->toString();
        $sampleresult->method_id        = $laboratoriummethod->method_id;
        $sampleresult->sample_id        = $id;
        $sampleresult->laboratorium_id  = $idlabs;
        if (isset($data["status_" . $laboratoriummethod->method_id])) {
          $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];

          $sampleresult->hasil            = rubahNilaikeHtml(str_replace(',', '.', $data["result_method_" . $laboratoriummethod->method_id]));
          $sampleresult->metode = $data["metode_" . $laboratoriummethod->method_id];
          $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id];
        } else {
          $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id];
          $sampleresult->hasil            = "-";
        }
        
        // Simpan lokasi_selected untuk Kualitas Udara
        if ($selectedRuangan) {
          $sampleresult->lokasi_selected = $selectedRuangan;
        }
        
        $sampleresult->save();


        $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
          ->where('sampletype_id', '=', $sampletype_id)
          ->where('sample_id', '=',  $id)->get();

        foreach ($sampleresultdetails as $key => $sampleresultdetail) {
          # code...
          $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);
          if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {

            $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
          } else {
            $sampleresultdetail_edit->hasil = "-";
          }
          $sampleresultdetail_edit->offset_baku_mutu = $data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail];

          $sampleresultdetail_edit->save();
        }
      }

      $no_LHU = LHU::where('sample_id', '=', $id)->where('lab_id', '=', $idlabs)->first();

      if (!isset($no_LHU)) {
        $no_LHU = new LHU;
        //uuid
        $uuid4 = Uuid::uuid4();

        $no_LHU_urutan = LHU::max('nomer_urut_LHU');


        // $no_LHU->id_lhu = $uuid4->toString();
        $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
        $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));
        $no_LHU->nomer_LHU = '449.5/A.' . $no_LHU->nomer_urut_LHU . '.5.22/' . $romawi_bulan . '/' . Carbon::now()->format('Y');
        $no_LHU->sample_id = $id;
        $no_LHU->lab_id = $idlabs;
        $no_LHU->save();
      }
    }
    // dd($data);
    if (isset($sample->location_samples)){
      $sample->location_samples = $data['lokasi_pengambilan'];
    }else{
      $sample->address_location_pdam = $data['lokasi_pengambilan'];
    }
    
    // Simpan titik pengambilan
    if (isset($data['titik_pengambilan'])) {
      $sample->titik_pengambilan = $data['titik_pengambilan'];
    }

    if (isset($data['nama_pengambil'])) {
      $sample->syncNamaPengambil($data['nama_pengambil']);
    }

    $sample->save();

    // Simpan verifikasi hasil (verification_step = 4) jika ada data verifikasi di request
    if ($request->has('verification_step_verifikasi_hasil') && $request->input('verification_step_verifikasi_hasil') == 4) {
      $verifikasi_start_date = $request->input('start_date_verifikasi_hasil_hidden');
      $verifikasi_stop_date = $request->input('stop_date_verifikasi_hasil_hidden');
      $verifikasi_nama_petugas = $request->input('nama_petugas_verifikasi_hasil_hidden');
      
      if ($verifikasi_start_date && $verifikasi_stop_date && $verifikasi_nama_petugas) {
        // Helper function untuk parse date dari format d/m/Y H:i
        $parseDate = function($dateStr) {
          if (empty($dateStr)) return null;
          
          // Coba format d/m/Y H:i
          if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $dateStr, $matches)) {
            return Carbon::createFromFormat('d/m/Y H:i', $dateStr);
          }
          
          // Coba format lain jika perlu
          try {
            return Carbon::parse($dateStr);
          } catch (\Exception $e) {
            return null;
          }
        };

        $verificationActivitySample = VerificationActivitySample::where('id_sample', $id)
          ->where('id_verification_activity', 4)
          ->first();

        if (!$verificationActivitySample) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $id;
          $verificationActivitySample->id_verification_activity = 4;
        }
        
        $parsedStartDate = $parseDate($verifikasi_start_date);
        $parsedStopDate = $parseDate($verifikasi_stop_date);
        
        if ($parsedStartDate) {
          $verificationActivitySample->start_date = $parsedStartDate->format('Y-m-d H:i:s');
        }
        if ($parsedStopDate) {
          $verificationActivitySample->stop_date = $parsedStopDate->format('Y-m-d H:i:s');
        }
        $verificationActivitySample->nama_petugas = $verifikasi_nama_petugas;
        $verificationActivitySample->is_done = 1;
        $verificationActivitySample->save();
      }
    }

    // $sample = Sample::where('id_samples',$id)->first();



    return redirect()->route('elits-pengesahan-hasil.index', [$id, $idlabs])->with(['status' => 'Verifikasi Hasil berhasil disimpan!']);
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
    $auth = Auth()->user();

    $customer = Customer::where('id_customer', $id)->first();

    $categories = Industry::all();


    return view('masterweb::module.admin.laboratorium.customer.edit', compact('customer', 'auth', 'categories', 'id'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update($id, Request $request)
  {


    // print_r($data);



    $data = $request->all();
    $customer = Customer::find($id);
    $customer->name_customer = $request->post('name_customer');
    $customer->address_customer = $request->post('address_customer');
    $customer->email_customer = $request->post('email_customer');
    $customer->category_customer = $request->post('category_customer');
    $customer->cp_customer = $request->post('cp_customer');
    $customer->save();

    return redirect()->route('elits-customers.index')->with(['status' => 'Customer succesfully updated']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $customer = Customer::findOrFail($id);
    $customer->delete();
    return redirect()->route('elits-customers.index')->with('status', 'Data berhasil dihapus');
  }
}