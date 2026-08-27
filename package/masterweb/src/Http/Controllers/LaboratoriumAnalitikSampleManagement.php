<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Mapper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Unit;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\Sample;
use Illuminate\Support\Facades\DB;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Container;
use App\Http\Controllers\Controller;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Laboratorium;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\SampleResult;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\JenisMakanan;
use \Smt\Masterweb\Models\Library;
use \Smt\Masterweb\Models\BakuMutuSampleOverride;

use Illuminate\Support\Facades\Validator;
use LDAP\Result;
use \Smt\Masterweb\Models\PenerimaanSample;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

use \Smt\Masterweb\Models\LaboratoriumMethod;


use \Smt\Masterweb\Models\SampleResultDetail;
use \Smt\Masterweb\Models\LaboratoriumProgress;
use \Smt\Masterweb\Models\SampleAnalitikProgress;
use Smt\Masterweb\Models\SampleTypeDetail;

class LaboratoriumAnalitikSampleManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  protected function getMissingBakuMutuMethodNames($laboratoriummethods, $sampletypeId = null, $labId = null, $jenisMakananId = null, $sampleProgressId = null): array
  {
    $missing = [];
    foreach ($laboratoriummethods as $method) {
      $methodId = $method->method_id ?? $method->id_method ?? null;
      if (!$methodId) {
        continue;
      }

      if ($this->methodHasUsableBakuMutu($method, $methodId, $sampletypeId, $labId, $jenisMakananId, $sampleProgressId)) {
        continue;
      }

      $missing[] = $method->params_method ?? $method->name_report ?? $methodId;
    }

    return array_values(array_unique($missing));
  }

  /**
   * Cek baku mutu usable (sama logika tampilan baca-hasil):
   * - id_baku_mutu dari join, ATAU
   * - ada di master (jenis spesifik / generik null), ATAU
   * - ada override per sampel
   */
  protected function methodHasUsableBakuMutu($method, $methodId, $sampletypeId = null, $labId = null, $jenisMakananId = null, $sampleProgressId = null): bool
  {
    if (!empty($method->id_baku_mutu)) {
      return true;
    }

    // Ada nilai baku mutu yang sudah ter-resolve di objek (termasuk dari override)
    $hasNilai = (isset($method->nilai_baku_mutu) && trim((string) $method->nilai_baku_mutu) !== '')
      || (isset($method->min) && $method->min !== null && $method->min !== '')
      || (isset($method->max) && $method->max !== null && $method->max !== '')
      || (isset($method->equal) && trim((string) $method->equal) !== '')
      || (isset($method->baku_mutu_min) && $method->baku_mutu_min !== null && $method->baku_mutu_min !== '')
      || (isset($method->baku_mutu_max) && $method->baku_mutu_max !== null && $method->baku_mutu_max !== '')
      || (isset($method->baku_mutu_equal) && trim((string) $method->baku_mutu_equal) !== '');
    if ($hasNilai) {
      return true;
    }

    if ($sampleProgressId) {
      $hasOverride = BakuMutuSampleOverride::where('sample_progress_id', $sampleProgressId)
        ->where('method_id', $methodId)
        ->where(function ($q) {
          $q->whereNotNull('nilai_baku_mutu')
            ->orWhereNotNull('min')
            ->orWhereNotNull('max')
            ->orWhereNotNull('equal');
        })
        ->exists();
      if ($hasOverride) {
        return true;
      }
    }

    if (!$sampletypeId || !$labId) {
      return false;
    }

    $query = \Smt\Masterweb\Models\BakuMutu::where('method_id', $methodId)
      ->where('sampletype_id', $sampletypeId)
      ->where('lab_id', $labId);

    if ($jenisMakananId !== null && $jenisMakananId !== '' && $jenisMakananId !== '__new__') {
      $query->where(function ($q) use ($jenisMakananId) {
        $q->where('jenis_makanan_id', $jenisMakananId)
          ->orWhereNull('jenis_makanan_id')
          ->orWhere('jenis_makanan_id', '');
      });
    }

    return $query->exists();
  }

  /**
   * Override baku mutu per-sampel mengikat nilai ke konteks jenis makanan yang tersimpan di sampel.
   * Saat user memilih jenis lain lewat URL (?jenis_makanan_id=), jangan terapkan override lama.
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

  /** Hapus override sampel jika jenis makanan berubah (MBI/KIM + MM), agar nilai lama tidak menimpa baku mutu master. */
  protected function clearBakuMutuOverridesOnJenisMakananChange(Sample $sample, string $progress, Request $request, $idlab): void
  {
    if (!$request->has('jenis_makanan_id') || $request->get('jenis_makanan_id') === '') {
      return;
    }
    $lab = Laboratorium::find($idlab);
    if (!$lab || !in_array($lab->kode_laboratorium, ['MBI', 'KIM'], true)) {
      return;
    }
    if (!$sample->relationLoaded('sampletype')) {
      $sample->load('sampletype');
    }
    $stName = optional($sample->sampletype)->name_sample_type ?? '';
    $isMML = str_contains($stName, 'Makanan')
      || str_contains($stName, 'Minuman')
      || str_contains($stName, 'Lainnya');
    if (!$isMML) {
      return;
    }
    $incoming = (string) $request->get('jenis_makanan_id');
    if ($incoming === '__none__' || $incoming === '__new__') {
      $incoming = '';
    }
    $prev = (string) ($sample->jenis_makanan_id ?? '');
    if ($prev === $incoming) {
      return;
    }
    BakuMutuSampleOverride::where('sample_progress_id', $progress)->delete();
  }

  /**
   * UUID id progress di URL kadang terpotong (SMS/chat). Cocokkan prefix ke langkah baca-hasil di lab ini.
   */
  private function resolveBacaHasilLaboratoriumProgressId($idlab, $progress)
  {
    $bacaHasilId = LaboratoriumProgress::query()
      ->where('laboratorium_id', $idlab)
      ->where('link', 'baca-hasil')
      ->whereNull('deleted_at')
      ->orderBy('order_sort', 'asc')
      ->value('id_laboratorium_progress');

    // URL lama Magelang memakai ID "Pemeriksaan" (bfecda4a...) sebagai baca-hasil.
    if (is_string($progress) && $progress === 'bfecda4a-73f2-47d6-9fc3-01f65e0f02a1' && $bacaHasilId) {
      return $bacaHasilId;
    }

    if (!is_string($progress) || strlen($progress) >= 36) {
      // Jika progress bukan langkah baca-hasil lab ini, pakai yang benar.
      if ($bacaHasilId && is_string($progress) && $progress !== $bacaHasilId) {
        $isBacaHasil = LaboratoriumProgress::query()
          ->where('id_laboratorium_progress', $progress)
          ->where('laboratorium_id', $idlab)
          ->where('link', 'baca-hasil')
          ->whereNull('deleted_at')
          ->exists();
        if (!$isBacaHasil) {
          return $bacaHasilId;
        }
      }
      return $progress;
    }

    $full = LaboratoriumProgress::query()
      ->where('laboratorium_id', $idlab)
      ->where('link', 'baca-hasil')
      ->where('id_laboratorium_progress', 'like', $progress . '%')
      ->value('id_laboratorium_progress');

    return $full ?: ($bacaHasilId ?: $progress);
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function persiapan_reagen($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.persiapan-reagen.persiapan-reagen', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function persiapan_reagen_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();
    if (!isset($sampleanalitikprogress)) {

      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["persiapan_reagen_date"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["persiapan_reagen_date"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }

    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);
  }

  public function inkubasi($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.inkubasi.inkubasi', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function inkubasi_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();
    if (!isset($sampleanalitikprogress)) {

      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["inkubasi_date"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["inkubasi_date"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }
    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);
    // }else{
    //     return redirect()->route('elits-inkubasi.verification',[$id,$idlab]);

    // }


  }

  public function preparasi($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.preparasi.preparasi', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function preparasi_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();
    if (!isset($sampleanalitikprogress)) {
      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["preparasi"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["preparasi"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }
    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);
  }

  public function pemeriksaan_alat($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.pemeriksaan-alat.pemeriksaan-alat', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function pemeriksaan_alat_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();


    // $validated = $request->validate([
    //     'pemeriksaan_alat' => ['required']
    // ]);

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();
    if (!isset($sampleanalitikprogress)) {
      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pemeriksaan_alat"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pemeriksaan_alat"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }
    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);


    // dd("");

    // if($data["pemeriksaan_alat"]=="ya"){
    //     $sampleanalitikprogress =new SampleAnalitikProgress;
    //     $uuid4 = Uuid::uuid4();
    //     $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
    //     $sampleanalitikprogress->laboratorium_progress_id  = $progress;
    //     $sampleanalitikprogress->laboratorium_id = $idlab;
    //     $sampleanalitikprogress->sample_id = $id;
    //     $sampleanalitikprogress->date_done =Carbon::now()->format('Y-m-d H:i:s');
    //     $sampleanalitikprogress->save();
    //     return redirect()->route('elits-samples.verification',[$id,$idlab])->with(['status'=>'Pemeriksaan Alat berhasil di input']);

    // }else{
    //     return redirect()->route('elits-pemeriksaan-alat.verification',[$id,$idlab]);

    // }


  }

  public function pemeriksaan($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.pemeriksaan.pemeriksaan', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function pemeriksaan_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();

    if (!isset($sampleanalitikprogress)) {
      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pemeriksaan"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pemeriksaan"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }
    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);
  }

  public function pipetase($id, $idlab)
  {

    Carbon::setLocale('id');
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
      ->first();

    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })->get();
    $units = Unit::all();

    $containers = Container::where('id_container', '!=', '0')->get();




    return view('masterweb::module.admin.laboratorium.analitik.pipetase.pipetase', compact('sample', 'laboratoriummethods', 'containers', 'units'));
  }

  public function pipetase_store(Request $request, $id, $idlab, $progress)
  {
    $data = $request->all();

    $sampleanalitikprogress = SampleAnalitikProgress::where('laboratorium_progress_id', '=', $progress)
      ->where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)->first();

    if (!isset($sampleanalitikprogress)) {
      $sampleanalitikprogress = new SampleAnalitikProgress;
      $uuid4 = Uuid::uuid4();
      $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
      $sampleanalitikprogress->laboratorium_progress_id  = $progress;
      $sampleanalitikprogress->laboratorium_id = $idlab;
      $sampleanalitikprogress->sample_id = $id;
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pipetase"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    } else {
      $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pipetase"])->format('Y-m-d H:i:s');
      $sampleanalitikprogress->save();
    }
    return redirect()->route('elits-samples.verification', [$id, $idlab])->with(['status' => 'Sampel berhasil di input']);
  }

  public function baca_hasil(Request $request, $id, $idlab, $progress)
  {
    Carbon::setLocale('id');
    $progress = $this->resolveBacaHasilLaboratoriumProgressId($idlab, $progress);
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
    // Gunakan typesample_samples dari tb_samples langsung untuk menghindari ambiguitas kolom join
    $sampletype_id = $sample->typesample_samples;

    $laboratorium = Laboratorium::where('id_laboratorium', '=', $idlab)->first();

    // Default "Asal Sampel" untuk mikro:
    // 1) pakai detail_alamat_sampling (jika ada),
    // 2) jika kosong, gabungkan name_customer + address_customer dari relasi permohonan uji.
    $defaultAsalSampel = null;
    if ($sample->permohonanuji) {
      $perm = $sample->permohonanuji;
      $rawDetail = $perm->detail_alamat_sampling ?? $perm->detail_adress_sampling ?? null;
      if (!empty($rawDetail)) {
        $defaultAsalSampel = $rawDetail;
      } elseif ($perm->customer) {
        $cust = $perm->customer;
        $nama = $cust->name_customer ?? '';
        $alamat = $cust->address_customer ?? '';
        $defaultAsalSampel = trim($nama . ($alamat ? '<br>' . $alamat : ''));
      }
    }

    // Tentukan jenis makanan awal: dari query string (jika ada), jika tidak dari sample
    // Tandai apakah user eksplisit memilih jenis_makanan_id dari query string
    $userExplicitJenisMakanan = $request->has('jenis_makanan_id');
    $jenis_makanan_id = $request->query('jenis_makanan_id', $sample->jenis_makanan_id);
    if ($jenis_makanan_id === '__none__') {
      $jenis_makanan_id = null;
    } elseif ($jenis_makanan_id === '__new__') {
      $jenis_makanan_id = $sample->jenis_makanan_id;
    }

    // Semua jenis makanan (untuk popup tambah jenis makanan lain)
    $allJenisMakanan = JenisMakanan::orderBy('name_jenis_makanan')->get();

    // Siapkan daftar jenis makanan yang punya baku mutu untuk parameter yang dipilih
    $jenisMakananAll = collect();
    // Nilai default jenis sarana (akan bisa di-override otomatis untuk makanan)
    $autoJenisSarana = $sample->jenis_sarana_names;
    // Flag untuk KIM: apakah ada baku mutu tanpa jenis makanan
    $hasBakuMutuWithoutJenisMakanan = false;

    // Helper: deteksi sample type Makanan/Minuman/Lainnya secara fleksibel
    $stName = $sample->name_sample_type ?? '';
    $isSampleTypeMML = str_contains($stName, 'Makanan')
        || str_contains($stName, 'Minuman')
        || str_contains($stName, 'Lainnya');

    // Helper: ambil method IDs untuk sample ini di lab ini (reusable)
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
        // Hanya auto-reset jika user TIDAK eksplisit memilih dari query string
        if (!$userExplicitJenisMakanan &&
          (!$jenis_makanan_id || !$jenisMakananAll->pluck('id_jenis_makanan')->contains($jenis_makanan_id))
        ) {
          $jenis_makanan_id = $jenisMakananAll->first()->id_jenis_makanan;
        }

        // Set jenis sarana otomatis mengikuti nama jenis makanan yang dipilih (jika ada di list)
        $selectedJenis = $jenisMakananAll->firstWhere('id_jenis_makanan', $jenis_makanan_id);
        if ($selectedJenis) {
          $autoJenisSarana = $selectedJenis->name_jenis_makanan;
        } elseif ($userExplicitJenisMakanan && $jenis_makanan_id) {
          // User memilih jenis makanan yang tidak ada di list baku mutu — ambil nama langsung dari DB
          $selectedJenisAny = \Smt\Masterweb\Models\JenisMakanan::find($jenis_makanan_id);
          if ($selectedJenisAny) {
            $autoJenisSarana = $selectedJenisAny->name_jenis_makanan;
          }
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
          ->filter()
          ->toArray();

        // Jika ada jenis makanan yang punya baku mutu, tampilkan dropdown
        if (!empty($jenisIds)) {
          $jenisMakananAll = \Smt\Masterweb\Models\JenisMakanan::whereIn('id_jenis_makanan', $jenisIds)
            ->orderBy('name_jenis_makanan')
            ->get();
        }

        // Cek apakah ada baku mutu generik (tanpa jenis makanan spesifik): NULL atau string kosong di DB
        $hasBakuMutuWithoutJenisMakanan = \Smt\Masterweb\Models\BakuMutu::query()
          ->where('lab_id', $idlab)
          ->where('sampletype_id', $sampletype_id)
          ->whereIn('method_id', $methodIdsForSample)
          ->whereNull('deleted_at')
          ->where(function ($q) {
            $q->whereNull('jenis_makanan_id')->orWhere('jenis_makanan_id', '=', '');
          })
          ->exists();

        // KIM: jenis makanan opsional — default selalu kosong
        // kecuali user eksplisit memilih lewat query ?jenis_makanan_id=...
        if (!$userExplicitJenisMakanan) {
          $jenis_makanan_id = null;
        }
      }
    }

    // Jika user eksplisit memilih jenis makanan dari query string namun tidak ada di $jenisMakananAll,
    // tambahkan ke koleksi agar tetap muncul sebagai opsi terpilih di picker dropdown
    if ($userExplicitJenisMakanan && $jenis_makanan_id) {
      $alreadyInList = $jenisMakananAll->pluck('id_jenis_makanan')->contains($jenis_makanan_id);
      if (!$alreadyInList) {
        $extraJm = \Smt\Masterweb\Models\JenisMakanan::find($jenis_makanan_id);
        if ($extraJm) {
          $jenisMakananAll = $jenisMakananAll->push($extraJm)->sortBy('name_jenis_makanan')->values();
        }
      }
    }

    // Untuk KIM dengan Makanan/Minuman/Lainnya, gunakan jenis_makanan_id jika ada
    // Untuk MBI dengan Makanan/Minuman/Lainnya, selalu gunakan jenis_makanan_id
    // Untuk lainnya, tidak gunakan jenis_makanan_id
    $isKimiaMakanan = $laboratorium && $laboratorium->kode_laboratorium === 'KIM' && $isSampleTypeMML;
    $isMbiMakanan   = $laboratorium && $laboratorium->kode_laboratorium === 'MBI' && $isSampleTypeMML;

    if ($isKimiaMakanan || ($isMbiMakanan && isset($jenis_makanan_id))) {

      // dd($jenis_makanan_id);

      $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        // ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id, $isKimiaMakanan) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $isKimiaMakanan, $idlab) {
              if ($isKimiaMakanan && $jenis_makanan_id !== null && $jenis_makanan_id !== '') {
                // Jenis makanan spesifik: HANYA baku mutu untuk jenis tersebut (tanpa fallback generik)
                $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->where('tb_baku_mutu.lab_id', '=', $idlab)
                  ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at');
              } else {
                // Untuk KIM tanpa jenis_makanan_id atau MBI: gunakan join biasa
                $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                     ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                     ->where('tb_baku_mutu.lab_id', '=', $idlab)
                     ->whereNull('tb_baku_mutu.deleted_at')
                     ->whereNull('ms_method.deleted_at');

                if ($isKimiaMakanan) {
                  // JoinClause tidak mendukung where(closure) seperti Query\Builder; pakai whereRaw
                  $join->whereRaw('(tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)', ['']);
                } else {
                  // Untuk MBI, selalu gunakan jenis_makanan_id
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
            })
            ->leftjoin('ms_parameter_satuan_klinik', function ($join) {
              $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_baku_mutu.parameter_satuan_klinik_id')
                ->whereNull('ms_parameter_satuan_klinik.deleted_at')
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
          'ms_parameter_satuan_klinik.is_option as parameter_satuan_klinik_is_option',
          'ms_parameter_satuan_klinik.option as parameter_satuan_klinik_option',
          'ms_method.is_option as method_is_option',
          'ms_method.option as method_option',
          'ms_method.orderlist_method as orderlist_method',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.metode',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          // Select min, max, equal from tb_baku_mutu with aliases AFTER all * selects to prevent overwriting
          DB::raw('tb_baku_mutu.min as baku_mutu_min'),
          DB::raw('tb_baku_mutu.max as baku_mutu_max'),
          DB::raw('tb_baku_mutu.equal as baku_mutu_equal'),
          DB::raw('tb_baku_mutu.library_id as baku_mutu_library_id')
        )
        ->orderBy('ms_method.id_method');

      // Untuk KIM dengan jenis_makanan_id: urutkan berdasarkan prioritas (yang dengan jenis_makanan_id lebih dulu)
      if ($isKimiaMakanan && $jenis_makanan_id) {
        $laboratoriummethods = $laboratoriummethods->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id]);
      }

      $laboratoriummethods = $laboratoriummethods->get();

      // Untuk KIM dengan jenis_makanan_id: group by method dan ambil yang pertama (dengan prioritas tertinggi)
      if ($isKimiaMakanan && $jenis_makanan_id) {
        $laboratoriummethods = $laboratoriummethods->groupBy('id_method')
          ->map(function ($group) {
            // Ambil yang pertama (dengan prioritas tertinggi berdasarkan order by)
            return $group->first();
          })
          ->values();
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
            ->leftjoin('ms_parameter_satuan_klinik', function ($join) {
              $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_baku_mutu.parameter_satuan_klinik_id')
                ->whereNull('ms_parameter_satuan_klinik.deleted_at')
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
          'ms_parameter_satuan_klinik.is_option as parameter_satuan_klinik_is_option',
          'ms_parameter_satuan_klinik.option as parameter_satuan_klinik_option',
          'ms_method.is_option as method_is_option',
          'ms_method.option as method_option',
          'ms_method.orderlist_method as orderlist_method',
          'tb_sample_result.hasil',
          'tb_sample_result.keterangan',
          'tb_sample_result.metode',
          'tb_sample_result.offset_baku_mutu',
          'tb_sample_result.lokasi_selected',
          // Select min, max, equal from tb_baku_mutu with aliases AFTER all * selects to prevent overwriting
          DB::raw('tb_baku_mutu.min as baku_mutu_min'),
          DB::raw('tb_baku_mutu.max as baku_mutu_max'),
          DB::raw('tb_baku_mutu.equal as baku_mutu_equal'),
          DB::raw('tb_baku_mutu.library_id as baku_mutu_library_id')
        )
        ->distinct('ms_method.id_method')
        ->get();
    }


    //pengurutan order list
    $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();

    // Debug: Check first method's baku mutu values
    // Uncomment to debug:
    // if ($laboratoriummethods->isNotEmpty()) {
    //     $firstMethod = $laboratoriummethods->first();
    //     \Log::info('DEBUG FIRST METHOD', [
    //         'method_id' => $firstMethod->method_id ?? 'NO ID',
    //         'name_report' => $firstMethod->name_report ?? 'NO NAME',
    //         'baku_mutu_min' => $firstMethod->baku_mutu_min ?? 'NOT SET',
    //         'baku_mutu_max' => $firstMethod->baku_mutu_max ?? 'NOT SET',
    //         'baku_mutu_equal' => $firstMethod->baku_mutu_equal ?? 'NOT SET',
    //         'min' => $firstMethod->min ?? 'NOT SET',
    //         'max' => $firstMethod->max ?? 'NOT SET',
    //         'equal' => $firstMethod->equal ?? 'NOT SET',
    //         'all_keys' => array_keys($firstMethod->toArray()),
    //     ]);
    // }

    // Pastikan setiap parameter (method) hanya muncul sekali
    $laboratoriummethods = collect($laboratoriummethods)
      ->unique('id_method')
      ->values();

    // Urutkan sesuai master jenis sampel; parameter di luar urutan wajib → paling bawah
    $laboratoriummethods = kesmas_sort_laboratorium_methods($laboratoriummethods, $sample_type_details);

    // Query memilih tb_baku_mutu.* lalu ms_method.* — kolom bernama sama (min, max, equal)
    // tertimpa nilai dari ms_method sehingga badge baca-hasil bisa salah (mis. rentang min/max).
    // Pakai alias baku_mutu_* untuk mengembalikan min/max/equal sesuai tb_baku_mutu.
    $laboratoriummethods = $laboratoriummethods->map(function ($item) {
      if (!empty($item->id_baku_mutu)) {
        $item->min = $item->baku_mutu_min;
        $item->max = $item->baku_mutu_max;
        $item->equal = $item->baku_mutu_equal;
      }
      return $item;
    });

    $lab = Laboratorium::where('id_laboratorium', '=', $idlab)->first();

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...
      $laboratoriummethods[$key]->detail = array();

      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();
    }

    // dd($laboratoriummethods);
    $jenis_sarana_options = $this->get_jenis_sarana_options($sample);







    $units = Unit::all();
    $libraries = Library::all();
    $sample_types = SampleType::orderBy('name_sample_type')->get();

    $containers = Container::where('id_container', '!=', '0')->get();

    // $containers= Container::where('id_container','!=','0')->get();

    $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $progress)
      ->where('laboratorium_id', $idlab)
      ->where('sample_id', $id)->first();

    // dd($sampleanalitikprogress);

    // Ambil default nama petugas dari Step 3: Disposisi ke Analis
    $default_nama_petugas = null;
    $penerimaan_sample = PenerimaanSample::where('sample_id', $id)
      ->where('laboratorium_id', $idlab)
      ->first();
    if ($penerimaan_sample && $penerimaan_sample->disposisi_analis) {
      $default_nama_petugas = $penerimaan_sample->disposisi_analis;
    }

    // Ambil list petugas untuk verifikasi (dari VerificationActivity id = 3 - Input / Output Hasil Px)
    $petugas_verifikasi_list = [];
    $verificationActivity = \Smt\Masterweb\Models\VerificationActivity::all()->keyBy('id')->toArray();
    if (isset($verificationActivity[3])) {
      $activity3 = (object) $verificationActivity[3];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $petugas_verifikasi_list = array_filter(explode(', ', $activity3->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $petugas_verifikasi_list = array_filter(explode(', ', $activity3->kimia ?? ''));
      } else {
        $petugas_verifikasi_list = array_filter(explode(', ', $activity3->klnik ?? ''));
      }
    }

    // Ambil data verifikasi step 3 jika sudah ada
    $verifikasi_baca_hasil = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $id)
      ->where('id_verification_activity', 3)
      ->first();

    // Default start date dan stop date untuk verifikasi baca hasil
    $default_start_date_verifikasi = null;
    $default_stop_date_verifikasi = null;

    if ($verifikasi_baca_hasil) {
      // Jika sudah ada, gunakan data yang sudah tersimpan
      $default_start_date_verifikasi = $verifikasi_baca_hasil->start_date ? Carbon::parse($verifikasi_baca_hasil->start_date) : null;
      $default_stop_date_verifikasi = $verifikasi_baca_hasil->stop_date ? Carbon::parse($verifikasi_baca_hasil->stop_date) : null;
    } else {
      // Jika belum ada, gunakan tanggal sekarang
      $default_start_date_verifikasi = Carbon::now();
      if ($default_start_date_verifikasi->hour < 8) {
        $default_start_date_verifikasi->setTime(8, 0, 0);
      } elseif ($default_start_date_verifikasi->hour >= 15) {
        $default_start_date_verifikasi->addDay()->setTime(8, 0, 0);
      }

      $default_stop_date_verifikasi = $default_start_date_verifikasi->copy()->addHour();
      if ($default_stop_date_verifikasi->hour >= 15) {
        $default_stop_date_verifikasi->addDay()->setTime(8, 0, 0);
      }
    }



    // Load per-sample baku mutu overrides dan apply ke laboratoriummethods
    $bmOverrides = BakuMutuSampleOverride::where('sample_progress_id', $progress)
      ->get()
      ->keyBy('method_id');

    $applyBmOverrides = $this->shouldApplyBakuMutuSampleOverrides(
      $request,
      $sample,
      $jenis_makanan_id,
      $isKimiaMakanan,
      $isMbiMakanan
    );

    if ($applyBmOverrides && $bmOverrides->isNotEmpty()) {
      $overrideUnitIds = $bmOverrides->pluck('unit_id')->filter()->unique()->values()->all();
      $overrideUnitsById = !empty($overrideUnitIds)
        ? Unit::whereIn('id_unit', $overrideUnitIds)->get()->keyBy('id_unit')
        : collect();

      $hasOverrideLibrary = \Illuminate\Support\Facades\Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id');
      $laboratoriummethods = $laboratoriummethods->map(function ($item) use ($bmOverrides, $overrideUnitsById, $hasOverrideLibrary) {
        if (isset($bmOverrides[$item->method_id])) {
          $ov = $bmOverrides[$item->method_id];
          if (!is_null($ov->nilai_baku_mutu)) {
            $item->nilai_baku_mutu = $ov->nilai_baku_mutu;
          }
          if (!is_null($ov->min)) {
            $item->min = $ov->min;
          }
          if (!is_null($ov->max)) {
            $item->max = $ov->max;
          }
          if (!is_null($ov->equal)) {
            $item->equal = $ov->equal;
          }
          if (!is_null($ov->unit_id) && $ov->unit_id !== '') {
            $item->unit_id = $ov->unit_id;
            $unit = $overrideUnitsById->get($ov->unit_id);
            if ($unit) {
              $item->shortname_unit = $unit->shortname_unit;
              $item->name_unit = $unit->name_unit;
            }
          }
          if ($hasOverrideLibrary && !is_null($ov->library_id) && $ov->library_id !== '') {
            $item->library_id = $ov->library_id;
            $item->baku_mutu_library_id = $ov->library_id;
          }
          $item->has_sample_override = true;
        }

        return $item;
      });
    }

    // Judul acuan (perpustakaan) per parameter untuk mengisi keterangan otomatis di baca-hasil MM
    $libraryIdsForKeterangan = $laboratoriummethods
      ->map(function ($item) {
        return $item->baku_mutu_library_id ?? $item->library_id ?? null;
      })
      ->filter()
      ->unique()
      ->values();
    $titlesByLibraryId = $libraryIdsForKeterangan->isNotEmpty()
      ? Library::whereIn('id_library', $libraryIdsForKeterangan->all())->pluck('title_library', 'id_library')
      : collect();
    $laboratoriummethods = $laboratoriummethods->map(function ($item) use ($titlesByLibraryId) {
      $libId = $item->baku_mutu_library_id ?? $item->library_id ?? null;
      $item->acuan_title_library = ($libId && $titlesByLibraryId->has($libId))
        ? (string) $titlesByLibraryId[$libId]
        : '';
      return $item;
    });

    $bacaHasilAutoKeteranganNamaJenis = null;
    if ($isSampleTypeMML && in_array($laboratorium->kode_laboratorium, ['MBI', 'KIM'], true)) {
      if (!empty($jenis_makanan_id)) {
        $bacaHasilAutoKeteranganNamaJenis = optional(JenisMakanan::find($jenis_makanan_id))->name_jenis_makanan;
      }
    }

    return view(
      'masterweb::module.admin.laboratorium.analitik.baca-hasil.baca-hasil',
      compact(
        'sampleanalitikprogress',
        'sample',
        'laboratoriummethods',
        'containers',
        'units',
        'libraries',
        'sample_types',
        'lab',
        'jenis_sarana_options',
        'jenis_makanan_id',
        'jenisMakananAll',
        'allJenisMakanan',
        'hasBakuMutuWithoutJenisMakanan',
      'autoJenisSarana',
      'defaultAsalSampel',
      'default_nama_petugas',
      'petugas_verifikasi_list',
      'verifikasi_baca_hasil',
      'default_start_date_verifikasi',
      'default_stop_date_verifikasi',
      'progress',
      'sampletype_id',
      'bacaHasilAutoKeteranganNamaJenis'
      )
    );
  }

  public function baca_hasil_save(Request $request, $id, $idlab, $progress)
  {
    $progress = $this->resolveBacaHasilLaboratoriumProgressId($idlab, $progress);
    DB::beginTransaction();

    try {
      $simpan_baca_hasil = false;
      $data = $request->all();

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

      $sampletype_id = $sample->typesample_samples;

      // Ambil jenis_makanan dari request jika ada, jika tidak pakai dari sample
      $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);
      if ($jenis_makanan_id === '__none__') {
        $jenis_makanan_id = null;
      } elseif ($jenis_makanan_id === '__new__') {
        $jenis_makanan_id = $sample->jenis_makanan_id;
      }
      $hasJenisMakanan = $jenis_makanan_id !== null && $jenis_makanan_id !== '';

      if ($hasJenisMakanan) {

        $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
          ->where('tb_sample_method.sample_id', '=', $id)
          ->orderBy('ms_method.jenis_parameter_kimia')
          ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at')
              ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $idlab) {
                $join
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->where('tb_baku_mutu.lab_id', '=', $idlab)
                  ->whereNull('tb_baku_mutu.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  // Jenis spesifik saja — tanpa fallback generik
                  ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id);
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
            'tb_sample_result.lokasi_selected',
            DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu')
          )
          ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id])
          ->get();

        // Satu baris per method (prioritas jenis makanan spesifik)
        $laboratoriummethods = collect($laboratoriummethods)
          ->groupBy(function ($m) {
            return $m->id_method ?? $m->method_id;
          })
          ->map(function ($group) {
            return $group->first();
          })
          ->values();
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
                  ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                  ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                  ->where('tb_baku_mutu.lab_id', '=', $idlab)
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
            'tb_sample_result.lokasi_selected',
            DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu')
          )
          ->get();

        $laboratoriummethods = collect($laboratoriummethods)
          ->unique(function ($m) {
            return $m->id_method ?? $m->method_id;
          })
          ->values();
      }

      $missingBakuMutuMethods = $this->getMissingBakuMutuMethodNames(
        $laboratoriummethods,
        $sampletype_id,
        $idlab,
        $hasJenisMakanan ? $jenis_makanan_id : null,
        $progress
      );
      if (!empty($missingBakuMutuMethods)) {
        DB::rollBack();
        return response()->json([
          'status' => false,
          'pesan' => 'Masih ada parameter tanpa baku mutu. Tambahkan baku mutu terlebih dahulu di Baca Hasil.',
          'missing_methods' => $missingBakuMutuMethods,
        ], 200);
      }

      SampleResult::where("sample_id", $id)
        ->where("laboratorium_id", $idlab)->delete();


      // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
      $selectedRuangan = $request->input('selected_ruangan');

      foreach ($laboratoriummethods as $laboratoriummethod) {
        $sampleresult                   = new SampleResult;
        $uuid4                          = Uuid::uuid4();
        // $sampleresult->id_sample_result = $uuid4->toString();
        $sampleresult->method_id        = $laboratoriummethod->method_id;
        $sampleresult->sample_id        = $id;
        $sampleresult->laboratorium_id  = $idlab;
        if (isset($data["status_" . $laboratoriummethod->method_id])) {
          $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];

          $sampleresult->hasil            = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));
          $sampleresult->metode           = $data["metode_" . $laboratoriummethod->method_id];
          $sampleresult->keterangan       = $data["keterangan_" . $laboratoriummethod->method_id];
        } else {
          $sampleresult->keterangan       = $data["keterangan_" . $laboratoriummethod->method_id];
          $sampleresult->hasil            = "-";
        }

        // Simpan lokasi_selected untuk Kualitas Udara
        if ($selectedRuangan) {
          $sampleresult->lokasi_selected = $selectedRuangan;
        }

        $simpan_baca_hasil = $sampleresult->save();

        $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
          ->where('sampletype_id', '=', $sampletype_id)
          ->where('sample_id', '=',  $id)->get();

        foreach ($sampleresultdetails as $key => $sampleresultdetail) {
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

      // simpan ke SampleAnalitikProgress
      $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $progress)
        ->where('laboratorium_id', $idlab)
        ->where('sample_id', $id)->first();

      if (isset($sampleanalitikprogress)) {
        $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $progress)
          ->where('laboratorium_id', $idlab)
          ->where('sample_id', $id)->first();


        // $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pengujian"])->format('Y-m-d H:i:s');
        if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
          $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
        }

        $sampleanalitikprogress->save();
      } else {
        $sampleanalitikprogress = new SampleAnalitikProgress;
        $sampleanalitikprogress->laboratorium_progress_id  = $progress;
        $sampleanalitikprogress->laboratorium_id = $idlab;
        $sampleanalitikprogress->sample_id = $id;
        // $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pengujian"])->format('Y-m-d H:i:s');

        if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
          $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
        }

        $sampleanalitikprogress->save();
      }

      // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya
      $namaJenisInput = $request->input('nama_jenis_makanan');

      $this->clearBakuMutuOverridesOnJenisMakananChange($sample, $progress, $request, $idlab);

      if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
        $jmRequestId = $request->get('jenis_makanan_id');
        if ($jmRequestId === '__none__') {
          $sample->jenis_makanan_id = null;
        } elseif ($jmRequestId !== '__new__') {
          $sample->jenis_makanan_id = $jmRequestId;
          // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
          if ($namaJenisInput === null || $namaJenisInput === '') {
            $jm = JenisMakanan::find($sample->jenis_makanan_id);
            if ($jm) {
              $namaJenisInput = $jm->name_jenis_makanan;
            }
          }
        }
      }
      if ($namaJenisInput !== null && $namaJenisInput !== '') {
        $sample->nama_jenis_makanan = $namaJenisInput;
      }

      // Simpan Asal & Titik Sampel tergantung jenis laboratorium / jenis sampel
      $lab = Laboratorium::find($idlab);
      $titik = $request->get('titik_pengambilan');

      if ($lab && $lab->kode_laboratorium === 'MBI') {
        // Asal Sampel selalu disimpan di permohonan_uji.detail_alamat_sampling
        if ($request->filled('lokasi_pengambilan')) {
          if (!$sample->relationLoaded('permohonanuji')) {
            $sample->load(['permohonanuji']);
          }
          if ($sample->permohonanuji) {
            $sample->permohonanuji->detail_alamat_sampling = $request->get('lokasi_pengambilan');
            $sample->permohonanuji->save();
          }
        }

        // Titik Sampel (Air Minum, Air Higiene — nama lama: Air Bersih, Uji Usap, Air Kolam Renang) → location_samples
        $tipe = $sample->name_sample_type ?? null;
        $jenisTitikSpesifik = ['Air Minum', 'Air Higiene', 'Air Bersih', 'Uji Usap', 'Air Kolam Renang'];
        if ($tipe && in_array($tipe, $jenisTitikSpesifik)) {
          $sample->location_samples = $titik;
        } else {
          // Untuk jenis lain, pertahankan perilaku lama (lokasi_pengambilan ke location_samples)
          $sample->location_samples = $request->get('lokasi_pengambilan');
        }
        $sample->titik_pengambilan = $titik;
      } else {
        // Non-mikro: tetap seperti sebelumnya
        $sample->location_samples = $request->get('lokasi_pengambilan');
        $sample->titik_pengambilan = $titik;
      }

      $sample->jenis_sarana_names = $request->get('jenis_sarana');

      if ($request->has('fontsize_hasil')) {
        $sample->fontsize_hasil_baca_hasil = (float) $request->input('fontsize_hasil', 12);
      }
      if ($request->has('line_height_hasil')) {
        $sample->line_height_hasil_baca_hasil = (float) $request->input('line_height_hasil', 1.0);
      }
      if ($request->has('padding_hasil')) {
        $sample->padding_hasil_baca_hasil = (float) $request->input('padding_hasil', 1.0);
      }
      if ($request->has('show_kop_hasil')) {
        $sample->show_kop_hasil_baca_hasil = (int) $request->input('show_kop_hasil', 1);
      }
      if ($request->has('column_widths_hasil') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'column_widths_hasil_baca_hasil')) {
        $incoming = $request->input('column_widths_hasil');
        if (is_string($incoming)) {
          $decoded = json_decode($incoming, true);
          $incoming = is_array($decoded) ? $decoded : [];
        }
        if (is_array($incoming)) {
          $profile = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::resolveProfile($sample);
          $merged = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::mergeIncoming(
            $sample->column_widths_hasil_baca_hasil,
            $incoming,
            $profile
          );
          $sample->column_widths_hasil_baca_hasil = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::encodeStored($merged);
        }
      }

      if ($request->has('keterangan_metode') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'keterangan_metode')) {
        $sample->keterangan_metode = $request->input('keterangan_metode');
      }
      if ($request->has('catatan_hasil') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'catatan_hasil')) {
        $sample->catatan_hasil = $request->input('catatan_hasil');
      }

      // Update nama pengambil di permohonan uji
      if ($request->has('nama_pengambil')) {
        $sample->syncNamaPengambil($request->get('nama_pengambil'));
      }

      $sample->save();

      DB::commit();

      // Jika commit berhasil, data pasti tersimpan (baik ada metode maupun belum ada hasil sebelumnya)
      return response()->json(['status' => true, 'pesan' => "Data baca hasil berhasil disimpan!", 'url_redirect' => route('elits-baca-hasil.index', [$id, $idlab, $progress])], 200);

    } catch (\Exception $e) {
      DB::rollback();

      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
    }
  }

  public function saveReviewHasilSetting(Request $request, $id, $idlab, $progress)
  {
    $progress = $this->resolveBacaHasilLaboratoriumProgressId($idlab, $progress);
    $validated = $request->validate([
      'fontsize' => 'required|numeric|min:6|max:20',
      'line_height' => 'required|numeric|min:0.5|max:3',
      'padding' => 'required|numeric|min:0|max:16',
      'show_kop' => 'nullable|in:0,1,true,false,on,off',
      'column_widths' => 'nullable',
    ]);

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
      ->select('tb_samples.*')
      ->first();

    if (!$sample) {
      return response()->json([
        'status' => false,
        'pesan' => 'Data sampel tidak ditemukan.'
      ], 404);
    }

    $showKopRaw = $request->input('show_kop', 1);
    $showKop = ($showKopRaw === '0' || $showKopRaw === 0 || $showKopRaw === 'false' || $showKopRaw === 'off') ? 0 : 1;

    $sample->fontsize_hasil_baca_hasil = (float) $validated['fontsize'];
    $sample->line_height_hasil_baca_hasil = (float) $validated['line_height'];
    $sample->padding_hasil_baca_hasil = (float) $validated['padding'];
    $sample->show_kop_hasil_baca_hasil = $showKop;

    if ($request->has('column_widths') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'column_widths_hasil_baca_hasil')) {
      $incoming = $request->input('column_widths');
      if (is_string($incoming)) {
        $decoded = json_decode($incoming, true);
        $incoming = is_array($decoded) ? $decoded : [];
      }
      if (is_array($incoming)) {
        $profile = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::resolveProfile($sample);
        $merged = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::mergeIncoming(
          $sample->column_widths_hasil_baca_hasil,
          $incoming,
          $profile
        );
        $sample->column_widths_hasil_baca_hasil = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::encodeStored($merged);
      }
    }

    if ($request->has('keterangan_metode') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'keterangan_metode')) {
      $sample->keterangan_metode = $request->input('keterangan_metode');
    }
    if ($request->has('catatan_hasil') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'catatan_hasil')) {
      $sample->catatan_hasil = $request->input('catatan_hasil');
    }

    $sample->save();

    return response()->json([
      'status' => true,
      'pesan' => 'Pengaturan review hasil berhasil disimpan.',
      'data' => [
        'fontsize' => $sample->fontsize_hasil_baca_hasil,
        'line_height' => $sample->line_height_hasil_baca_hasil,
        'padding' => $sample->padding_hasil_baca_hasil,
        'show_kop' => $sample->show_kop_hasil_baca_hasil,
        'column_widths' => \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::decodeStored($sample->column_widths_hasil_baca_hasil ?? null),
        'keterangan_metode' => $sample->keterangan_metode ?? '',
        'catatan_hasil' => $sample->catatan_hasil ?? '',
      ]
    ]);
  }

  public function rules_hasil_store($request)
  {
    $rule = [
      'baca_hasil' => 'required'
    ];

    $pesan = [
      'baca_hasil.required' => 'Baca hasil wajib dichecklist!'
    ];

    return Validator::make($request, $rule, $pesan);
  }

  public function baca_hasil_store(Request $request, $id, $idlab, $progress)
  {
    $progress = $this->resolveBacaHasilLaboratoriumProgressId($idlab, $progress);
    $validator = $this->rules_hasil_store($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $simpan_baca_hasil = false;
        $data = $request->all();



        $sampleresults = SampleResult::where('laboratorium_id', '=', $idlab)
          ->where('sample_id', '=', $id)->count();
        // dd($sampleresults);

        $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $progress)
          ->where('laboratorium_id', $idlab)
          ->where('sample_id', $id)->first();

        // dd($sampleanalitikprogress);



        if (isset($sampleanalitikprogress)) {
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
            ->first();

          $sampletype_id = $sample->id_sample_type;

          // Ambil jenis_makanan_id dari request jika ada (mis. dari dropdown pada baca-hasil), jika tidak pakai yang tersimpan di sample
          $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);
          if ($jenis_makanan_id === '__none__') {
            $jenis_makanan_id = null;
          } elseif ($jenis_makanan_id === '__new__') {
            $jenis_makanan_id = $sample->jenis_makanan_id;
          }
          $hasJenisMakanan = $jenis_makanan_id !== null && $jenis_makanan_id !== '';

          if ($hasJenisMakanan) {

            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
              ->where('tb_sample_method.sample_id', '=', $id)
              ->orderBy('ms_method.jenis_parameter_kimia')
              ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
                $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id, $idlab) {
                    $join
                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                      ->where('tb_baku_mutu.lab_id', '=', $idlab)
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at')
                      // Jenis spesifik saja — tanpa fallback generik
                      ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id);
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
                DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu')
              )
              ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id])
              ->get();

            $laboratoriummethods = collect($laboratoriummethods)
              ->groupBy(function ($m) {
                return $m->id_method ?? $m->method_id;
              })
              ->map(function ($group) {
                return $group->first();
              })
              ->values();
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
                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                      ->where('tb_baku_mutu.lab_id', '=', $idlab)
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
                DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu')
              )
              ->get();

            $laboratoriummethods = collect($laboratoriummethods)
              ->unique(function ($m) {
                return $m->id_method ?? $m->method_id;
              })
              ->values();
          }

          $missingBakuMutuMethods = $this->getMissingBakuMutuMethodNames(
            $laboratoriummethods,
            $sampletype_id,
            $idlab,
            $hasJenisMakanan ? $jenis_makanan_id : null,
            $progress
          );
          if (!empty($missingBakuMutuMethods)) {
            DB::rollBack();
            return response()->json([
              'status' => false,
              'pesan' => 'Masih ada parameter tanpa baku mutu. Tambahkan baku mutu terlebih dahulu di Baca Hasil.',
              'missing_methods' => $missingBakuMutuMethods,
            ], 200);
          }


          // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
          $selectedRuangan = $request->input('selected_ruangan');

          SampleResult::where("sample_id", $id)
            ->where("laboratorium_id", $idlab)->delete();

          foreach ($laboratoriummethods as $laboratoriummethod) {
              $sampleresult                   = new SampleResult;
              $uuid4                          = Uuid::uuid4();
              // $sampleresult->id_sample_result = $uuid4->toString();
              $sampleresult->method_id        = $laboratoriummethod->method_id;
              $sampleresult->sample_id        = $id;
              $sampleresult->laboratorium_id  = $idlab;

              // Always set offset_baku_mutu if provided, regardless of status
              // Normalize the value to ensure it's 'true', 'false', or 'default'
              $offsetValue = 'default';
              if (isset($data["offset_baku_mutu_" . $laboratoriummethod->method_id])) {
                $offsetValue = strtolower(trim($data["offset_baku_mutu_" . $laboratoriummethod->method_id]));
                if ($offsetValue !== 'true' && $offsetValue !== 'false') {
                  $offsetValue = 'default';
                }
              }
              $sampleresult->offset_baku_mutu = $offsetValue;

              if (isset($data["status_" . $laboratoriummethod->method_id])) {
                $sampleresult->hasil            = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));

                $sampleresult->metode           = $data["metode_" . $laboratoriummethod->method_id];
                $sampleresult->keterangan       = $data["keterangan_" . $laboratoriummethod->method_id];
              } else {
                $sampleresult->keterangan       = $data["keterangan_" . $laboratoriummethod->method_id];
                $sampleresult->hasil            = "-";
              }

              // Simpan lokasi_selected untuk Kualitas Udara
              if ($selectedRuangan) {
                $sampleresult->lokasi_selected = $selectedRuangan;
              }

              $simpan_baca_hasil = $sampleresult->save();

            $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('sample_id', '=',  $id)->get();

              foreach ($sampleresultdetails as $key => $sampleresultdetail) {
                $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);

                // Always set offset_baku_mutu if provided, regardless of status
                // Normalize the value to ensure it's 'true', 'false', or 'default'
                $offsetValue = 'default';
                if (isset($data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail])) {
                  $offsetValue = strtolower(trim($data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail]));
                  if ($offsetValue !== 'true' && $offsetValue !== 'false') {
                    $offsetValue = 'default';
                  }
                }
                $sampleresultdetail_edit->offset_baku_mutu = $offsetValue;

                if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {
                  $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
                } else {
                  $sampleresultdetail_edit->hasil = "-";
                }

                $sampleresultdetail_edit->save();
              }
          }

          // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya
          $namaJenisInputStore = $request->input('nama_jenis_makanan');

          $this->clearBakuMutuOverridesOnJenisMakananChange($sample, $progress, $request, $idlab);

          if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
            $jmRequestId = $request->get('jenis_makanan_id');
            if ($jmRequestId === '__none__') {
              $sample->jenis_makanan_id = null;
            } elseif ($jmRequestId !== '__new__') {
              $sample->jenis_makanan_id = $jmRequestId;
              // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
              if ($namaJenisInputStore === null || $namaJenisInputStore === '') {
                $jmStore = JenisMakanan::find($sample->jenis_makanan_id);
                if ($jmStore) {
                  $namaJenisInputStore = $jmStore->name_jenis_makanan;
                }
              }
            }
          }
          if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
            $sample->nama_jenis_makanan = $namaJenisInputStore;
          }
          $sample->save();


          $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $progress)
            ->where('laboratorium_id', $idlab)
            ->where('sample_id', $id)->first();


          //          $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pengujian"])->format('Y-m-d H:i:s');
          if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
            $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
          }

          $sampleanalitikprogress->save();

          $analys = Sample::where('tb_samples.id_samples', '=', $id)
            ->where('ms_laboratorium.id_laboratorium', '!=', $idlab)
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
            ->leftjoin('tb_sample_analitik_progress', function ($join) {
              $join->on('tb_sample_analitik_progress.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                ->on('tb_sample_analitik_progress.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_analitik_progress.deleted_at')
                ->whereNull('tb_samples.deleted_at')
                ->join('tb_laboratorium_progress', function ($join) {
                  $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
                    ->whereNull('tb_laboratorium_progress.deleted_at')
                    ->whereNull('tb_sample_analitik_progress.deleted_at')
                    ->where('tb_laboratorium_progress.link', '=', 'baca-hasil');
                });
            })
            ->get();

          $is_all_done = true;

          foreach ($analys as $analy) {
            if (!isset($analy->date_done) && ($analy->date_done != NULL)) {
              $is_all_done = false;
            }
          }

          if ($is_all_done) {
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
              ->first();

            $sample->date_analitik_sample = Carbon::now()->format('Y-m-d H:i:s');
            $sample->location_samples = $request->post('lokasi_pengambilan');
            $sample->tembusan = $request->post('tembusan');
            $sample->only_fisika = $request->post('only_fisika');
            $sample->save();
          }
        } else {
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
            ->first();

           // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya
          $namaJenisInputStore = $request->input('nama_jenis_makanan');

          $this->clearBakuMutuOverridesOnJenisMakananChange($sample, $progress, $request, $idlab);

          if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
            $jmRequestId = $request->get('jenis_makanan_id');
            if ($jmRequestId === '__none__') {
              $sample->jenis_makanan_id = null;
            } elseif ($jmRequestId !== '__new__') {
              $sample->jenis_makanan_id = $jmRequestId;
              // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
              if ($namaJenisInputStore === null || $namaJenisInputStore === '') {
                $jmStore = JenisMakanan::find($sample->jenis_makanan_id);
                if ($jmStore) {
                  $namaJenisInputStore = $jmStore->name_jenis_makanan;
                }
              }
            }
          }
          if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
            $sample->nama_jenis_makanan = $namaJenisInputStore;
          }
          $sample->save();

          $sampletype_id = $sample->id_sample_type;

          $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
            ->where('sample_id', '=', $id)
            ->orderBy('ms_method.created_at')
            ->join('ms_method', function ($join) {
              $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
              $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })->join('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->select('tb_baku_mutu.*', 'ms_method.*', 'tb_sample_method.*', 'unit_baku_mutu.*')
            ->get();

          // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
          $selectedRuangan = $request->input('selected_ruangan');

          SampleResult::where("sample_id", $id)
            ->where("laboratorium_id", $idlab)->delete();



          foreach ($laboratoriummethods as $laboratoriummethod) {
            $sampleresult                   = new SampleResult;
            $uuid4                          = Uuid::uuid4();
            // $sampleresult->id_sample_result = $uuid4->toString();
            $sampleresult->method_id        = $laboratoriummethod->method_id;
            $sampleresult->sample_id        = $id;
            $sampleresult->laboratorium_id  = $idlab;
            if (isset($data["status_" . $laboratoriummethod->method_id])) {
              $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];

              $sampleresult->hasil            = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));

              $sampleresult->metode           = $data["metode_" . $laboratoriummethod->method_id];
              $sampleresult->keterangan       = $data["keterangan_" . $laboratoriummethod->method_id];
            } else {
              $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id];
              $sampleresult->hasil            = "-";
            }

            // Simpan lokasi_selected untuk Kualitas Udara
            if ($selectedRuangan) {
              $sampleresult->lokasi_selected = $selectedRuangan;
            }

            $simpan_baca_hasil = $sampleresult->save();

            $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
              ->where('sampletype_id', '=', $sampletype_id)
              ->where('sample_id', '=',  $id)->get();

            foreach ($sampleresultdetails as $key => $sampleresultdetail) {
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





          $sampleanalitikprogress = new SampleAnalitikProgress;
          $uuid4 = Uuid::uuid4();
          // $sampleanalitikprogress->id_sample_analitik_progress = $uuid4->toString();
          $sampleanalitikprogress->laboratorium_progress_id  = $progress;
          $sampleanalitikprogress->laboratorium_id = $idlab;
          $sampleanalitikprogress->sample_id = $id;
          //          $sampleanalitikprogress->date_done = Carbon::createFromFormat('d/m/Y', $data["pengujian"])->format('Y-m-d H:i:s');

          if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
            $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
          }

          $sampleanalitikprogress->save();

          $analys = Sample::where('tb_samples.id_samples', '=', $id)
            ->where('ms_laboratorium.id_laboratorium', '!=', $idlab)
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
            ->leftjoin('tb_sample_analitik_progress', function ($join) {
              $join->on('tb_sample_analitik_progress.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                ->on('tb_sample_analitik_progress.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_analitik_progress.deleted_at')
                ->whereNull('tb_samples.deleted_at')
                ->join('tb_laboratorium_progress', function ($join) {
                  $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
                    ->whereNull('tb_laboratorium_progress.deleted_at')
                    ->whereNull('tb_sample_analitik_progress.deleted_at')
                    ->where('tb_laboratorium_progress.link', '=', 'baca-hasil');
                });
            })
            ->get();

          $is_all_done = true;

          foreach ($analys as $analy) {
            if (!isset($analy->date_done) && ($analy->date_done != NULL)) {
              $is_all_done = false;
            }
          }

          if ($is_all_done) {
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
              ->first();

            $sample->date_analitik_sample = Carbon::now()->format('Y-m-d H:i:s');
            $sample->location_samples = $request->post('lokasi_pengambilan');
            $sample->jenis_sarana_names = $request->post('jenis_sarana');
            $sample->tembusan = $request->post('tembusan');
            $sample->only_fisika = $request->post('only_fisika');
            $sample->titik_pengambilan = $request->post('titik_pengambilan');

            $sample->pengambil_sampel = $request->post('nama_pengambil');

            // Update nama pengambil di permohonan uji
            if ($request->has('nama_pengambil')) {
              $sample->syncNamaPengambil($request->post('nama_pengambil'));
            }

            $sample->save();
          }
        }

        $sample->refresh();

        // Simpan Asal & Titik Sampel tergantung jenis laboratorium / jenis sampel
        $labStore = Laboratorium::find($idlab);
        $titikStore = $request->get('titik_pengambilan');

        if ($labStore && $labStore->kode_laboratorium === 'MBI') {
          // Asal Sampel selalu disimpan di permohonan_uji.detail_alamat_sampling
          if ($request->filled('lokasi_pengambilan')) {
            if (!$sample->relationLoaded('permohonanuji')) {
              $sample->load('permohonanuji');
            }
            if ($sample->permohonanuji) {
              $sample->permohonanuji->detail_alamat_sampling = $request->get('lokasi_pengambilan');
              $sample->permohonanuji->save();
            }
          }

          // Titik Sampel (Air Minum, Air Higiene — nama lama: Air Bersih, Uji Usap, Air Kolam Renang) → location_samples
          $tipeStore = $sample->name_sample_type ?? null;
          $jenisTitikSpesifikStore = ['Air Minum', 'Air Higiene', 'Air Bersih', 'Uji Usap', 'Air Kolam Renang'];
          if ($tipeStore && in_array($tipeStore, $jenisTitikSpesifikStore)) {
            $sample->location_samples = $titikStore;
          } else {
            // Untuk jenis lain, pertahankan perilaku lama (lokasi_pengambilan ke location_samples)
            $sample->location_samples = $request->get('lokasi_pengambilan');
          }
          $sample->titik_pengambilan = $titikStore;
        } else {
          // Non-mikro: tetap seperti sebelumnya
          $sample->location_samples = $request->get('lokasi_pengambilan');
          $sample->titik_pengambilan = $titikStore;
        }

          $sample->jenis_sarana_names = $request->get('jenis_sarana');

          if ($request->has('fontsize_hasil')) {
            $sample->fontsize_hasil_baca_hasil = (float) $request->input('fontsize_hasil', 12);
          }
          if ($request->has('line_height_hasil')) {
            $sample->line_height_hasil_baca_hasil = (float) $request->input('line_height_hasil', 1.0);
          }
          if ($request->has('padding_hasil')) {
            $sample->padding_hasil_baca_hasil = (float) $request->input('padding_hasil', 1.0);
          }
          if ($request->has('show_kop_hasil')) {
            $sample->show_kop_hasil_baca_hasil = (int) $request->input('show_kop_hasil', 1);
          }
          if ($request->has('column_widths_hasil') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'column_widths_hasil_baca_hasil')) {
            $incoming = $request->input('column_widths_hasil');
            if (is_string($incoming)) {
              $decoded = json_decode($incoming, true);
              $incoming = is_array($decoded) ? $decoded : [];
            }
            if (is_array($incoming)) {
              $profile = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::resolveProfile($sample);
              $merged = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::mergeIncoming(
                $sample->column_widths_hasil_baca_hasil,
                $incoming,
                $profile
              );
              $sample->column_widths_hasil_baca_hasil = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::encodeStored($merged);
            }
          }

          if ($request->has('keterangan_metode') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'keterangan_metode')) {
            $sample->keterangan_metode = $request->input('keterangan_metode');
          }
          if ($request->has('catatan_hasil') && \Illuminate\Support\Facades\Schema::hasColumn('tb_samples', 'catatan_hasil')) {
            $sample->catatan_hasil = $request->input('catatan_hasil');
          }

        // Update nama pengambil di permohonan uji
        if ($request->has('nama_pengambil')) {
          $sample->syncNamaPengambil($request->get('nama_pengambil'));
        }

        $sample->save();

        // Simpan verifikasi baca hasil jika ada data verifikasi di request
        if ($request->has('verification_step') && $request->get('verification_step') == 3) {
          $verification_start_date = $request->get('verification_start_date');
          $verification_stop_date = $request->get('verification_stop_date');
          $verification_nama_petugas = $request->get('verification_nama_petugas');

          if ($verification_start_date && $verification_stop_date && $verification_nama_petugas) {
            // Helper function untuk parse date dari format d/m/Y H:i atau d/m/Y
            // Tanggal tanpa jam → jam diset ke waktu sekarang
            $parseDate = function($dateStr) {
              return \Smt\Masterweb\Helpers\DateHelper::parseStageDate($dateStr);
            };

            $start_date = $parseDate($verification_start_date);
            $stop_date = $parseDate($verification_stop_date);

            if ($start_date && $stop_date) {
              // Cek apakah sudah ada verifikasi untuk step 3
              $verificationActivitySample = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $id)
                ->where('id_verification_activity', 3)
                ->first();

              if (!$verificationActivitySample) {
                // Buat baru
                $verificationActivitySample = new \Smt\Masterweb\Models\VerificationActivitySample();
                $verificationActivitySample->id = Uuid::uuid4()->toString();
                $verificationActivitySample->id_sample = $id;
                $verificationActivitySample->id_verification_activity = 3;
              }

              $verificationActivitySample->is_done = 1;
              $verificationActivitySample->start_date = $start_date->format('Y-m-d H:i:s');
              $verificationActivitySample->stop_date = $stop_date->format('Y-m-d H:i:s');
              $verificationActivitySample->nama_petugas = $verification_nama_petugas;
              $verificationActivitySample->save();
            }
          }
        }

        DB::commit();

        if ($simpan_baca_hasil == true) {
          // Redirect to verifikasi hasil page
            return response()->json(['status' => true, 'pesan' => "Data baca hasil berhasil disimpan!", 'url_redirect' => route('elits-verifikasi-hasil.index', [$id, $idlab])], 200);

        } else {
          return response()->json(['status' => false, 'pesan' => "Data baca hasil tidak berhasil disimpan!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
      }
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create($id)
  {
    //get auth user




    $user = Auth()->user();
    $count = Sample::count();
    $users = User::all();

    $packets = Packet::where('id_packet', '!=', '0')->orderBy('created_at')->get();

    $laboratoriums = Laboratorium::all();

    $data_methods = array();

    foreach ($laboratoriums as $laboratorium) {
      array_push(
        $data_methods,
        (object) array(
          'name' => $laboratorium->nama_laboratorium,
          'id_lab' => $laboratorium->id_laboratorium,
          'method' => array()
        )
      );
    }


    $i = 0;
    foreach ($data_methods as $data_method) {
      $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $data_method->id_lab)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })->get();
      foreach ($laboratoriummethods as $laboratoriummethod) {
        //    print_r($laboratoriummethod->params_method);
        array_push(
          $data_methods[$i]->method,
          (object) array(
            'name_method' => $laboratoriummethod->params_method,
            'id_method' => $laboratoriummethod->id_method,
            'price_method' => $laboratoriummethod->price_method
          )
        );
      }

      $i++;
    }







    $containers = Container::where('id_container', '!=', '0')->get();
    $sampletypes = SampleType::orderBy('created_at')
      ->get();

    $code = 'S.KRNGY-' . date("Ymd", time()) . '-0' . ($count + 1);





    return view('masterweb::module.admin.laboratorium.sample.add', compact('user', 'data_methods', 'containers', 'packets', 'sampletypes', 'code', 'users'));
    //get all menu public
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


    // print_r($data);
    $validated = $request->validate([
      'wadah' => ['required', 'max:255'],
      'pengawet' => ['required'],
      'volume' => ['required'],
      'unit' => ['required'],
      'kondisi_sample' => ['required'],
      'validation_sample' => ['required']
    ]);

    // dd($data);


    $user = Auth()->user();
    $penerimaan_sample = new PenerimaanSample;
    //uuid
    $uuid4 = Uuid::uuid4();

    $penerimaan_sample->id_sample_penerimaan = $uuid4->toString();
    $penerimaan_sample->sample_id = $id;
    $penerimaan_sample->laboratorium_id = $idlabs;
    $penerimaan_sample->wadah_id = $request->post('wadah');
    $wadah_samples = $request->post('wadah_samples');
    if (isset($wadah_samples)) {
      $penerimaan_sample->wadah_sampel_other = $wadah_samples;
    }
    $penerimaan_sample->pengawet = $request->post('pengawet');
    $penerimaan_sample->unit_id = $request->post('unit');
    $penerimaan_sample->volume = $request->post('volume');
    $penerimaan_sample->kondisi_sample = $request->post('kondisi_sample');
    $penerimaan_sample->validation_sample  = $request->post('validation_sample');
    $penerimaan_sample->save();

    $sample = Sample::where('id_samples', $id)->first();



    return redirect()->route('elits-samples.verification-2', [$id, $idlabs])->with(['status' => 'Penerimaan berhasil di input']);
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

  /**
   * Retrieves the options for the jenis sarana.
   *
   * This function is responsible for retrieving the options for the jenis sarana.
   * It does not take any parameters and does not return any value.
   *
   * @return array|null
   */
  protected function get_jenis_sarana_options(Sample $sample)
  {
    $jenis_sample = $sample->name_sample_type;
    $options = [];
    $processed_options = [];

    // Check for Air Limbah (contains check to support "Air Limbah Domestik" and "Air Limbah Industri")
    if (stripos($jenis_sample, 'Air Limbah') !== false) {
      $options = [
        'IPAL'
      ];
    } else {
    switch ($jenis_sample) {
      case 'Air Minum':
        $options = [
          'PDAM',
          'DAM / DAMIU',
          'AMDK',
          'Sumur',
          'PAMSIMAS',
          'Lainnya',
        ];
        break;
      case 'Air Higiene':
      case 'Air Bersih':
        $options = [
          'Sumur Gali',
          'Sumur Dalam',
          'Sumur Bor',
          'Perpipaan',
          'Sumur Artesis',
          'Lainnya',
        ];
        break;
      case 'Makanan/Minuman/Lainnya':
        $options = [
          'Nasi',
          'Sayur',
          'Lauk',
          'Lainnya',
        ];
        break;
      default:
        return null;
      }
    }

    foreach ($options as $index => $value) {
      $key = \Illuminate\Support\Str::slug($value);
      $processed_options[$index] = [
        'key' => $key,
        'value' => $value
      ];
    }

    return $processed_options;
  }

  // Fetch baku mutu mikro berdasarkan jenis makanan untuk auto-set di baca hasil
  public function getBakuMutuMikroByJenis(Request $request)
  {
    $labId = $request->post('lab_id');
    $sampleTypeId = $request->post('sampletype_id');
    $jenisMakananId = $request->post('jenis_makanan_id');
    $methodIds = $request->post('method_ids', []);

    if (!$labId || !$sampleTypeId || !$jenisMakananId || !is_array($methodIds)) {
      return response()->json(['status' => false, 'pesan' => 'Parameter tidak lengkap'], 200);
    }

    $rows = \Smt\Masterweb\Models\BakuMutu::query()
      ->where('lab_id', $labId)
      ->where('sampletype_id', $sampleTypeId)
      ->where('jenis_makanan_id', $jenisMakananId)
      ->whereIn('method_id', $methodIds)
      ->get(['method_id','min','max','equal','nilai_baku_mutu','unit_id']);

    $result = [];
    foreach ($rows as $r) {
      $result[$r->method_id] = [
        'min' => $r->min,
        'max' => $r->max,
        'equal' => $r->equal,
        'nilai_baku_mutu' => $r->nilai_baku_mutu,
        'unit_id' => $r->unit_id,
      ];
    }

    return response()->json(['status' => true, 'data' => $result]);
  }

  /**
   * Daftar baku mutu parameter yang sama di jenis makanan lain
   * (untuk dijadikan referensi saat menambah baku mutu baru).
   */
  public function getBakuMutuReferensiJenisMakanan(Request $request)
  {
    $methodId = $request->get('method_id');
    $sampleTypeId = $request->get('sampletype_id');
    $labId = $request->get('lab_id');
    $excludeJenisMakananId = $request->get('exclude_jenis_makanan_id');

    if (!$methodId || !$sampleTypeId) {
      return response()->json(['status' => false, 'pesan' => 'Parameter tidak lengkap'], 200);
    }

    $selectCols = [
      'tb_baku_mutu.id_baku_mutu',
      'tb_baku_mutu.method_id',
      'tb_baku_mutu.jenis_makanan_id',
      'tb_baku_mutu.min',
      'tb_baku_mutu.max',
      'tb_baku_mutu.equal',
      'tb_baku_mutu.nilai_baku_mutu',
      'tb_baku_mutu.unit_id',
      'tb_baku_mutu.library_id',
      'tb_baku_mutu.name_report',
      'tb_baku_mutu.tipe_nilai_baku_mutu',
      'ms_jenis_makanan.name_jenis_makanan',
      'ms_method.params_method',
    ];

    $previewFn = static function ($row) {
      $raw = (string) ($row->nilai_baku_mutu
        ?: $row->equal
        ?: (($row->min !== null || $row->max !== null) ? ($row->min . ' - ' . $row->max) : ''));
      // Decode entity (&nbsp;, &#60;, dll) lalu buang tag HTML agar label dropdown bersih
      $plain = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $plain = strip_tags($plain);
      $plain = preg_replace('/\s+/u', ' ', $plain);

      return trim((string) $plain);
    };

    $mapRow = static function ($row, $source, $label) use ($previewFn) {
      $preview = $previewFn($row);
      return [
        'id_baku_mutu' => $row->id_baku_mutu,
        'method_id' => $row->method_id,
        'source' => $source,
        'jenis_makanan_id' => $row->jenis_makanan_id,
        'name_jenis_makanan' => $row->name_jenis_makanan,
        'params_method' => $row->params_method,
        'label' => $preview !== '' ? ($label . ' — ' . $preview) : $label,
        'min' => $row->min,
        'max' => $row->max,
        'equal' => $row->equal,
        'nilai_baku_mutu' => $row->nilai_baku_mutu,
        'unit_id' => $row->unit_id,
        'library_id' => $row->library_id,
        'name_report' => $row->name_report,
        'tipe_nilai_baku_mutu' => $row->tipe_nilai_baku_mutu,
      ];
    };

    // 1) Parameter yang sama, jenis makanan lain (khusus Makanan/Minuman/Lainnya)
    $sameMethodQuery = \Smt\Masterweb\Models\BakuMutu::query()
      ->leftJoin('ms_jenis_makanan', 'ms_jenis_makanan.id_jenis_makanan', '=', 'tb_baku_mutu.jenis_makanan_id')
      ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_baku_mutu.method_id')
      ->where('tb_baku_mutu.method_id', $methodId)
      ->where('tb_baku_mutu.sampletype_id', $sampleTypeId)
      ->whereNull('tb_baku_mutu.deleted_at');

    if ($labId) {
      $sameMethodQuery->where('tb_baku_mutu.lab_id', $labId);
    }

    if ($excludeJenisMakananId) {
      $sameMethodQuery->where(function ($q) use ($excludeJenisMakananId) {
        $q->whereNull('tb_baku_mutu.jenis_makanan_id')
          ->orWhere('tb_baku_mutu.jenis_makanan_id', '!=', $excludeJenisMakananId);
      });
    }

    $sameMethodRows = $sameMethodQuery
      ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id IS NULL THEN 1 ELSE 0 END')
      ->orderBy('ms_jenis_makanan.name_jenis_makanan')
      ->get($selectCols);

    $dataJenisMakanan = [];
    foreach ($sameMethodRows as $row) {
      $labelJenis = $row->name_jenis_makanan
        ? $row->name_jenis_makanan
        : 'Tanpa jenis makanan (generik)';
      $dataJenisMakanan[] = $mapRow($row, 'jenis_makanan', $labelJenis);
    }

    // 2) Parameter lain di lab yang sama (jenis sampel sama)
    $otherMethodQuery = \Smt\Masterweb\Models\BakuMutu::query()
      ->leftJoin('ms_jenis_makanan', 'ms_jenis_makanan.id_jenis_makanan', '=', 'tb_baku_mutu.jenis_makanan_id')
      ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_baku_mutu.method_id')
      ->where('tb_baku_mutu.sampletype_id', $sampleTypeId)
      ->where('tb_baku_mutu.method_id', '!=', $methodId)
      ->whereNull('tb_baku_mutu.deleted_at')
      ->whereNull('ms_method.deleted_at');

    if ($labId) {
      $otherMethodQuery->where('tb_baku_mutu.lab_id', $labId);
    }

    // Preferensi: jika konteks jenis makanan dipilih, utamakan BM dengan jenis yang sama / generik
    if ($excludeJenisMakananId) {
      $otherMethodQuery->where(function ($q) use ($excludeJenisMakananId) {
        $q->where('tb_baku_mutu.jenis_makanan_id', $excludeJenisMakananId)
          ->orWhereNull('tb_baku_mutu.jenis_makanan_id')
          ->orWhere('tb_baku_mutu.jenis_makanan_id', '');
      });
    }

    $otherMethodRows = $otherMethodQuery
      ->orderBy('ms_method.params_method')
      ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = \'\' THEN 1 ELSE 0 END')
      ->orderBy('ms_jenis_makanan.name_jenis_makanan')
      ->get($selectCols);

    $dataParameterLain = [];
    $seenMethods = [];
    foreach ($otherMethodRows as $row) {
      // Satu opsi per parameter (prioritas sudah di-order)
      if (isset($seenMethods[$row->method_id])) {
        continue;
      }
      $seenMethods[$row->method_id] = true;

      $paramName = $row->params_method ?: ($row->name_report ?: 'Parameter');
      $jmSuffix = $row->name_jenis_makanan ? (' [' . $row->name_jenis_makanan . ']') : '';
      $dataParameterLain[] = $mapRow($row, 'parameter_lain', $paramName . $jmSuffix);
    }

    $data = array_merge($dataJenisMakanan, $dataParameterLain);

    return response()->json([
      'status' => true,
      'data' => $data,
      'groups' => [
        'jenis_makanan' => $dataJenisMakanan,
        'parameter_lain' => $dataParameterLain,
      ],
    ]);
  }

  // ─── Edit Baku Mutu dari halaman Baca Hasil ────────────────────────────────

  /** GET: ambil data baku mutu untuk form edit */
  public function getBakuMutuDataForEdit(Request $request, $id_baku_mutu)
  {
    $bm = \Smt\Masterweb\Models\BakuMutu::find($id_baku_mutu);
    if (!$bm) {
      return response()->json(['status' => false, 'pesan' => 'Baku mutu tidak ditemukan'], 200);
    }

    $unit = null;
    if (!empty($bm->unit_id)) {
      $unit = Unit::where('id_unit', $bm->unit_id)->first();
    }

    $data = [
      'id_baku_mutu'       => $bm->id_baku_mutu,
      'nilai_baku_mutu'    => $bm->nilai_baku_mutu,
      'min'                => $bm->min,
      'max'                => $bm->max,
      'equal'              => $bm->equal,
      'unit_id'            => $bm->unit_id,
      'library_id'         => $bm->library_id,
      'sampletype_id'      => $bm->sampletype_id,
      'jenis_makanan_id'   => $bm->jenis_makanan_id,
      'tipe_nilai_baku_mutu' => $bm->tipe_nilai_baku_mutu,
      'shortname_unit'     => $unit ? $unit->shortname_unit : null,
      'method_id'          => $bm->method_id,
      'has_sample_override'=> false,
    ];

    // Prefill tab override dari override sampel jika ada
    $progressId = trim((string) $request->query('sample_progress_id', ''));
    $methodId = trim((string) $request->query('method_id', $bm->method_id ?: ''));
    $sampleId = trim((string) $request->query('sample_id', ''));
    if ($methodId === '') {
      $methodId = (string) ($bm->method_id ?? '');
    }

    $override = null;
    if ($methodId !== '') {
      if ($progressId !== '') {
        $override = BakuMutuSampleOverride::where('sample_progress_id', $progressId)
          ->where('method_id', $methodId)
          ->first();
      }

      // Fallback: cari lewat progress baca-hasil / id analitik milik sampel
      if (!$override && $sampleId !== '') {
        $analitikRows = SampleAnalitikProgress::query()
          ->where('sample_id', $sampleId)
          ->whereNull('deleted_at')
          ->get(['id_sample_analitik_progress', 'laboratorium_progress_id']);

        $candidateProgressIds = $analitikRows
          ->pluck('laboratorium_progress_id')
          ->merge($analitikRows->pluck('id_sample_analitik_progress'))
          ->filter()
          ->unique()
          ->values();

        if ($candidateProgressIds->isNotEmpty()) {
          $override = BakuMutuSampleOverride::whereIn('sample_progress_id', $candidateProgressIds->all())
            ->where('method_id', $methodId)
            ->orderByDesc('updated_at')
            ->first();
        }
      }
    }

    if ($override) {
      $data['has_sample_override'] = true;
      // Selalu kirim kunci override_* agar frontend tidak fallback ke master
      $data['override_nilai_baku_mutu'] = !is_null($override->nilai_baku_mutu)
        ? $override->nilai_baku_mutu
        : $bm->nilai_baku_mutu;
      $data['override_min'] = !is_null($override->min) ? $override->min : $bm->min;
      $data['override_max'] = !is_null($override->max) ? $override->max : $bm->max;
      $data['override_equal'] = !is_null($override->equal) ? $override->equal : $bm->equal;
      $data['override_unit_id'] = (!is_null($override->unit_id) && $override->unit_id !== '')
        ? $override->unit_id
        : $bm->unit_id;
      if (!empty($data['override_unit_id'])) {
        $ovUnit = Unit::where('id_unit', $data['override_unit_id'])->first();
        $data['override_shortname_unit'] = $ovUnit ? $ovUnit->shortname_unit : null;
      }
      if (\Illuminate\Support\Facades\Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id')) {
        $data['override_library_id'] = (!is_null($override->library_id) && $override->library_id !== '')
          ? $override->library_id
          : $bm->library_id;
      }
    }

    return response()->json([
      'status' => true,
      'data'   => $data,
    ]);
  }

  /** POST: update baku mutu secara umum (global, semua sampel) */
  public function updateBakuMutuUmum($id_baku_mutu, Request $request)
  {
    $bm = \Smt\Masterweb\Models\BakuMutu::find($id_baku_mutu);
    if (!$bm) {
      return response()->json(['status' => false, 'pesan' => 'Baku mutu tidak ditemukan'], 200);
    }

    \DB::beginTransaction();
    try {
      if ($request->has('nilai_baku_mutu')) {
        $bm->nilai_baku_mutu = rubahNilaikeHtml(str_replace(',', '.', $request->nilai_baku_mutu));
      }
      if ($request->has('min')) {
        $bm->min = $request->min !== '' ? $request->min : null;
      }
      if ($request->has('max')) {
        $bm->max = $request->max !== '' ? $request->max : null;
      }
      if ($request->has('equal')) {
        $bm->equal = $request->equal !== '' ? rubahNilaikeHtml(str_replace(',', '.', $request->equal)) : null;
      }
      if ($request->has('unit_id')) {
        $unitId = $request->unit_id;
        $bm->unit_id = ($unitId !== '' && $unitId !== '-') ? $unitId : null;
      }
      if ($request->has('library_id')) {
        $libraryId = $request->library_id;
        $bm->library_id = ($libraryId !== '' && $libraryId !== '-') ? $libraryId : null;
      }
      if ($request->has('sampletype_id')) {
        $sampletypeId = $request->sampletype_id;
        if ($sampletypeId !== '' && $sampletypeId !== '-') {
          $bm->sampletype_id = $sampletypeId;
        }
      }
      if ($request->has('jenis_makanan_id')) {
        $jenisMakananId = $request->jenis_makanan_id;
        $bm->jenis_makanan_id = ($jenisMakananId !== '' && $jenisMakananId !== '-' && $jenisMakananId !== '__none__')
          ? $jenisMakananId
          : null;
      }
      if ($request->has('tipe_nilai_baku_mutu')) {
        $tipeNilai = $request->tipe_nilai_baku_mutu;
        $bm->tipe_nilai_baku_mutu = in_array($tipeNilai, ['kuantitatif', 'kualitatif'], true)
          ? $tipeNilai
          : null;
      }
      $bm->save();
      \DB::commit();

      $unit = !empty($bm->unit_id) ? Unit::where('id_unit', $bm->unit_id)->first() : null;

      return response()->json([
        'status' => true,
        'pesan'  => 'Baku mutu berhasil diperbarui.',
        'data'   => [
          'nilai_baku_mutu'      => $bm->nilai_baku_mutu,
          'min'                  => $bm->min,
          'max'                  => $bm->max,
          'equal'                => $bm->equal,
          'unit_id'              => $bm->unit_id,
          'library_id'           => $bm->library_id,
          'sampletype_id'        => $bm->sampletype_id,
          'jenis_makanan_id'     => $bm->jenis_makanan_id,
          'tipe_nilai_baku_mutu' => $bm->tipe_nilai_baku_mutu,
          'shortname_unit'       => $unit ? $unit->shortname_unit : null,
        ],
      ]);
    } catch (\Exception $e) {
      \DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal: ' . $e->getMessage()], 200);
    }
  }

  /** POST: simpan / update baku mutu override khusus satu sampel */
  public function upsertBakuMutuSampleOverride(Request $request)
  {
    $progressId = $request->post('sample_progress_id');
    $methodId   = $request->post('method_id');

    if (!$progressId || !$methodId) {
      return response()->json(['status' => false, 'pesan' => 'Parameter tidak lengkap'], 200);
    }

    \DB::beginTransaction();
    try {
      $override = \Smt\Masterweb\Models\BakuMutuSampleOverride::where('sample_progress_id', $progressId)
        ->where('method_id', $methodId)
        ->first();

      if (!$override) {
        $override = new \Smt\Masterweb\Models\BakuMutuSampleOverride();
        $override->id                = (string) \Illuminate\Support\Str::uuid();
        $override->sample_progress_id = $progressId;
        $override->method_id          = $methodId;
      }

      $override->nilai_baku_mutu = $request->has('nilai_baku_mutu')
        ? ($request->nilai_baku_mutu !== '' ? rubahNilaikeHtml(str_replace(',', '.', $request->nilai_baku_mutu)) : null)
        : $override->nilai_baku_mutu;
      $override->min   = $request->has('min')   ? ($request->min   !== '' ? $request->min   : null) : $override->min;
      $override->max   = $request->has('max')   ? ($request->max   !== '' ? $request->max   : null) : $override->max;
      $override->equal = $request->has('equal') ? ($request->equal !== '' ? rubahNilaikeHtml(str_replace(',', '.', $request->equal)) : null) : $override->equal;
      if ($request->has('unit_id')) {
        $unitId = $request->unit_id;
        $override->unit_id = ($unitId !== '' && $unitId !== '-') ? $unitId : null;
      }
      if ($request->has('library_id') && \Illuminate\Support\Facades\Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id')) {
        $libraryId = $request->library_id;
        $override->library_id = ($libraryId !== '' && $libraryId !== '-') ? $libraryId : null;
      }
      $override->save();

      $unit = !empty($override->unit_id) ? Unit::where('id_unit', $override->unit_id)->first() : null;

      \DB::commit();
      return response()->json([
        'status' => true,
        'pesan'  => 'Override baku mutu sampel berhasil disimpan.',
        'data'   => [
          'nilai_baku_mutu' => $override->nilai_baku_mutu,
          'min'             => $override->min,
          'max'             => $override->max,
          'equal'           => $override->equal,
          'unit_id'         => $override->unit_id,
          'library_id'      => $override->library_id ?? null,
          'shortname_unit'  => $unit ? $unit->shortname_unit : null,
        ],
      ]);
    } catch (\Exception $e) {
      \DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal: ' . $e->getMessage()], 200);
    }
  }

  /** POST: update metode master (permanen) dari editor hasil baca-hasil / verifikasi */
  public function updateMetodeParameter(Request $request, $method_id)
  {
    $method = Method::where('id_method', $method_id)->whereNull('deleted_at')->first();
    if (!$method) {
      return response()->json(['status' => false, 'pesan' => 'Metode tidak ditemukan'], 200);
    }

    $nameMethod = trim((string) $request->input('name_method', ''));
    if ($nameMethod === '') {
      return response()->json(['status' => false, 'pesan' => 'Metode tidak boleh kosong'], 200);
    }

    if (!$request->boolean('permanent')) {
      return response()->json(['status' => true, 'pesan' => 'Metode sampel diperbarui.']);
    }

    DB::beginTransaction();
    try {
      $method->name_method = rubahNilaikeHtml($nameMethod);
      $method->save();
      DB::commit();

      return response()->json([
        'status' => true,
        'pesan'  => 'Metode master berhasil diperbarui permanen.',
        'data'   => ['name_method' => $method->name_method],
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'pesan' => 'Gagal: ' . $e->getMessage()], 200);
    }
  }
}