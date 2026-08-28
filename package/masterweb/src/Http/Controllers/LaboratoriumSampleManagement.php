<?php

namespace Smt\Masterweb\Http\Controllers;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Illuminate\Http\Request;
use Smt\Masterweb\Helpers\BakuMutuSampletypeHelper;
use Smt\Masterweb\Helpers\Smt;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\VerificationActivity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mapper;
use PDF;
use Ramsey\Uuid\Uuid;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\BakuMutuDetailParameterNonKlinik;
use Smt\Masterweb\Models\Container;
use Smt\Masterweb\Models\Customer;
use Smt\Masterweb\Models\JenisMakanan;
use Smt\Masterweb\Models\LabNum;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\LaboratoriumMethod;
use Smt\Masterweb\Models\LaboratoriumProgress;
use Smt\Masterweb\Models\LHU;
use Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\MethodSampleTypePrice;
use Smt\Masterweb\Models\Packet;
use Smt\Masterweb\Models\PenerimaanSample;
use Smt\Masterweb\Models\PengesahanHasil;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\Program;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\SampleAnalitikProgress;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Models\SampleResult;
use Smt\Masterweb\Models\SampleResultDetail;
use Smt\Masterweb\Models\SampleType;
use Smt\Masterweb\Models\SampleTypeDetail;
use Smt\Masterweb\Models\StartNum;
use Smt\Masterweb\Models\Unit;
use Smt\Masterweb\Models\GlobalLabSequence;
use Smt\Masterweb\Models\GlobalLabSequenceDetail;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\VerifikasiHasil;
use Smt\Masterweb\Models\Library;
use Smt\Masterweb\Models\NomerLabKesmas;
use Smt\Masterweb\Models\KesmasSampleNumberSettings;
use Yajra\Datatables\Datatables;

// use \Smt\Masterweb\Models\PenerimaanSample;


class LaboratoriumSampleManagement extends Controller
{
  protected array $sample_types = [
    'AMB' => 'Air Minum Baktereologi',
    'ABB' => 'Air Higiene Bakteorologi',
    'AMF' => 'Air Minum Fisika',
    'AMK' => 'Air Minum Kimia',
    'AM' => 'Air Minum',
    'ALB' => 'Air Limbah Bakteorologi',
    'ALT' => 'Alat Makan dan Usap Alat Makan',
    'AKK' => 'Alat Makan dan Usap Alat Makan'
  ];

  public function __construct()
  {
    // Use regular auth for all methods except 'create' and 'store'
    // Print methods tetap menggunakan auth, tapi middleware Authenticate akan mengizinkan
    // jika ada session mobile (mobile_testing_auth, mobile_sampling_auth, atau sampling_auth)
    $this->middleware('auth')->except(['create', 'store']);
    // Use sampling auth for create and store methods (field workers can access)
    $this->middleware('sampling.auth')->only(['create', 'store']);
  }

  /**
   * Helper to return PDF output with headers that allow iframe embedding.
   */
  protected function streamPdfInline($pdf, ?string $filename = null)
  {
    $response = response($pdf->output(), 200)
      ->header('Content-Type', 'application/pdf')
      ->header('X-Frame-Options', 'SAMEORIGIN')
      ->header('Content-Security-Policy', "frame-ancestors 'self'");

    if ($filename) {
      $response->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    return $response;
  }

  /**
   * Helper untuk mengambil min dan max nomor lab dari NomerLabKesmas berdasarkan permohonan_uji_id
   * Digunakan untuk konsistensi di semua format print
   *
   * @param string|null $permohonanUjiId
   * @return array ['min' => int|null, 'max' => int|null]
   */
  protected function getMinMaxLabNumFromKesmas(?string $permohonanUjiId): array
  {
    $minLabNum = null;
    $maxLabNum = null;

    if ($permohonanUjiId) {
      // Ambil semua NomerLabKesmas untuk permohonan_uji_id yang sama
      $allNomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiId)
        ->whereNotNull('nomer_lab')
        ->pluck('nomer_lab')
        ->filter()
        ->toArray();

      if (!empty($allNomerLabKesmas)) {
        $minLabNum = min($allNomerLabKesmas);
        $maxLabNum = max($allNomerLabKesmas);
      }
    }

    return ['min' => $minLabNum, 'max' => $maxLabNum];
  }

  /**
   * Kode sampel penuh manual (Kimia / Mikro) saat setting Kesmas aktif.
   */
  protected function getKesmasManualCodeFromRequest(Request $request, string $labShortName): ?string
  {
    if (!KesmasSampleNumberSettings::getSettings()->is_nomor_sampel_manual) {
      return null;
    }
    // Primary: dedicated hidden field (legacy, rarely present in practice)
    $key = ($labShortName === 'kimia') ? 'manual_code_sample_kimia' : 'manual_code_sample_mikro';
    $val = $request->input($key);
    if ($val !== null && $val !== '') {
      return trim((string) $val);
    }
    // Fallback: the form always submits code_sample_kimia / code_sample_mikro (updated by
    // bindKesmasKlinikSpecimenInput in step-3 review); these arrive in short format
    // "01/NNNN/YYYY" — the caller is responsible for adding the type-code prefix.
    $legacyKey = ($labShortName === 'kimia') ? 'code_sample_kimia' : 'code_sample_mikro';
    $val2 = $request->input($legacyKey);
    if ($val2 !== null && $val2 !== '') {
      return trim((string) $val2);
    }

    return null;
  }

  /**
   * Normalisasi kode manual: tambah prefix jenis (jika belum ada) dan zero-pad urut 4 digit.
   */
  protected function normalizeKesmasManualSampleCode(string $manualCode, ?string $sampleTypeCode = null): string
  {
    $manualCode = trim($manualCode);
    if ($manualCode === '') {
      return '';
    }

    if (!preg_match('/^[A-Za-z]/', $manualCode) && $sampleTypeCode) {
      $manualCode = $sampleTypeCode . '.' . $manualCode;
    }

    $parts = explode('/', $manualCode);
    if (count($parts) >= 3) {
      $seq = (int) preg_replace('/\D/', '', (string) $parts[1]);
      if ($seq > 0) {
        $parts[1] = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
      }

      return implode('/', $parts);
    }

    return $manualCode;
  }

  /**
   * Simpan kode sampel manual + cadangan agar bisa dipulihkan setelah sinkronisasi otomatis.
   */
  protected function persistKesmasManualSampleCode(Sample $sample, string $manualCode, ?string $sampleTypeCode = null): void
  {
    $manualCode = $this->normalizeKesmasManualSampleCode($manualCode, $sampleTypeCode);
    $sample->codesample_samples = $manualCode;

    if (Schema::hasColumn('tb_samples', 'codesample_samples_manual')) {
      $sample->codesample_samples_manual = $manualCode;
    }

    $parts = explode('/', $manualCode);
    $seq = isset($parts[1]) ? (int) preg_replace('/\D/', '', (string) $parts[1]) : 0;
    if ($seq > 0) {
      $sample->count_id = $seq;
    }

    $sample->is_nomor_sampel_manual = 1;
  }

  /**
   * Apakah laboratorium termasuk unit Kimia (bukan Mikrobiologi).
   */
  protected function isKimiaLaboratorium(?Laboratorium $laboratorium): bool
  {
    if (!$laboratorium) {
      return false;
    }

    $kode = strtoupper((string) ($laboratorium->kode_laboratorium ?? ''));

    return in_array($kode, ['KIM', 'KMA', 'FKA'], true);
  }

  /**
   * Ambil kode sampel dari form edit: utamakan field yang benar-benar diubah user.
   */
  /**
   * Cari sampel pasangan Kimia/Mikro (codeKimiaMikro atau nomor urut + tahun sama).
   */
  protected function findPairedKesmasSample(Sample $sample): ?Sample
  {
    if (!empty($sample->codeKimiaMikro)) {
      return Sample::query()
        ->where('id_samples', '!=', $sample->id_samples)
        ->where('codeKimiaMikro', $sample->codeKimiaMikro)
        ->whereNull('deleted_at')
        ->first();
    }

    $code = trim((string) ($sample->codesample_samples ?? ''));
    if ($code === '') {
      return null;
    }

    $parts = explode('/', $code);
    if (count($parts) < 3) {
      return null;
    }

    $seq = (int) preg_replace('/\D/', '', (string) $parts[1]);
    $year = (int) $parts[2];
    if ($seq <= 0 || $year <= 0) {
      return null;
    }

    $firstSegments = explode('.', $parts[0], 2);
    $typeCode = $firstSegments[0] ?? '';
    if ($typeCode === '') {
      return null;
    }

    $currentLabSuffix = $firstSegments[1] ?? null;
    $altSuffix = ($currentLabSuffix === '02') ? '01' : '02';

    $candidates = Sample::query()
      ->where('permohonan_uji_id', $sample->permohonan_uji_id)
      ->where('id_samples', '!=', $sample->id_samples)
      ->whereNull('deleted_at')
      ->where('codesample_samples', 'like', $typeCode . '.' . $altSuffix . '/%')
      ->get();

    foreach ($candidates as $candidate) {
      $cParts = explode('/', $candidate->codesample_samples ?? '');
      if (count($cParts) < 3) {
        continue;
      }
      $cSeq = (int) preg_replace('/\D/', '', (string) $cParts[1]);
      $cYear = (int) $cParts[2];
      if ($cSeq === $seq && $cYear === $year) {
        return $candidate;
      }
    }

    return null;
  }

  /**
   * Nilai kode sampel yang ditampilkan di form edit per kolom Kimia / Mikro.
   *
   * @return array{kimia: string, mikro: string}
   */
  protected function expectedKesmasCodeDisplaysForEdit(Sample $sample, string $oldCode): array
  {
    $oldCode = trim($oldCode);
    $paired = $this->findPairedKesmasSample($sample);

    $labNum = LabNum::where('sample_id', $sample->id_samples)->first();
    $isKimiaLab = null;
    if ($labNum && $labNum->lab_id) {
      $isKimiaLab = $this->isKimiaLaboratorium(Laboratorium::find($labNum->lab_id));
    }

    $kimiaSource = $oldCode;
    $mikroSource = $oldCode;

    if ($isKimiaLab === true) {
      $kimiaSource = $oldCode;
      $mikroSource = ($paired && !empty($paired->codesample_samples))
        ? (string) $paired->codesample_samples
        : $oldCode;
    } elseif ($isKimiaLab === false) {
      $mikroSource = $oldCode;
      $kimiaSource = ($paired && !empty($paired->codesample_samples))
        ? (string) $paired->codesample_samples
        : $oldCode;
    } elseif ($paired && !empty($paired->codesample_samples)) {
      $pairedLabNum = LabNum::where('sample_id', $paired->id_samples)->first();
      $pairedIsKimia = $pairedLabNum && $pairedLabNum->lab_id
        ? $this->isKimiaLaboratorium(Laboratorium::find($pairedLabNum->lab_id))
        : null;
      if ($pairedIsKimia === true) {
        $kimiaSource = (string) $paired->codesample_samples;
      } elseif ($pairedIsKimia === false) {
        $mikroSource = (string) $paired->codesample_samples;
      }
    }

    return [
      'kimia' => $kimiaSource !== '' ? $this->formatKesmasCodeForLabDisplay($kimiaSource, '01') : '',
      'mikro' => $mikroSource !== '' ? $this->formatKesmasCodeForLabDisplay($mikroSource, '02') : '',
    ];
  }

  protected function resolveCodeSampleFromEditRequest(Request $request, Sample $sample, string $oldCode): ?string
  {
    $codeKimia = trim((string) $request->post('code_sample_kimia', ''));
    $codeMikro = trim((string) $request->post('code_sample_mikro', ''));
    $codeMaster = trim((string) $request->post('code_sample', ''));
    $oldCode = trim($oldCode);

    $expected = $this->expectedKesmasCodeDisplaysForEdit($sample, $oldCode);
    $displayKimia = $expected['kimia'];
    $displayMikro = $expected['mikro'];

    $kimiaChanged = $codeKimia !== '' && $codeKimia !== $displayKimia;
    $mikroChanged = $codeMikro !== '' && $codeMikro !== $displayMikro;

    if (!$kimiaChanged && !$mikroChanged) {
      return null;
    }

    if ($kimiaChanged && !$mikroChanged) {
      return $codeKimia;
    }
    if ($mikroChanged && !$kimiaChanged) {
      return $codeMikro;
    }

    $isKimiaLab = null;
    $labNum = LabNum::where('sample_id', $sample->id_samples)->first();
    if ($labNum && $labNum->lab_id) {
      $isKimiaLab = $this->isKimiaLaboratorium(Laboratorium::find($labNum->lab_id));
    } else {
      $methods = $request->input('method', []);
      if (is_array($methods) && count($methods) > 0) {
        $methodParts = explode('_', (string) $methods[0]);
        if (isset($methodParts[1])) {
          $isKimiaLab = $this->isKimiaLaboratorium(Laboratorium::find($methodParts[1]));
        }
      }
    }

    if ($isKimiaLab === true && $codeKimia !== '') {
      return $codeKimia;
    }
    if ($isKimiaLab === false && $codeMikro !== '') {
      return $codeMikro;
    }

    if ($codeMaster !== '') {
      return $codeMaster;
    }
    if ($codeKimia !== '') {
      return $codeKimia;
    }
    if ($codeMikro !== '') {
      return $codeMikro;
    }

    return null;
  }

  /**
   * Sesuaikan suffix lab (.01 / .02) pada kode sampel untuk tampilan per unit lab.
   */
  protected function formatKesmasCodeForLabDisplay(string $code, string $labCode): string
  {
    $code = trim($code);
    if ($code === '') {
      return '';
    }

    $parts = explode('/', $code);
    if (count($parts) < 3) {
      return $code;
    }

    $firstSegments = explode('.', $parts[0], 2);
    $typeCode = $firstSegments[0] !== '' ? $firstSegments[0] : '...';
    $parts[0] = $typeCode . '.' . $labCode;

    return implode('/', $parts);
  }

  /**
   * Cari sampel lain yang memakai kode/nomor sampel yang sama (normalisasi urut 4 digit).
   */
  protected function findConflictingSampleByCode(string $code, $excludeSampleIds = null): ?Sample
  {
    $code = trim($code);
    if ($code === '') {
      return null;
    }

    if ($excludeSampleIds === null) {
      $excludeSampleIds = [];
    } elseif (!is_array($excludeSampleIds)) {
      $excludeSampleIds = [$excludeSampleIds];
    }
    $excludeSampleIds = array_values(array_filter($excludeSampleIds));

    $normalized = $this->normalizeKesmasManualSampleCode($code);

    $query = Sample::whereNull('deleted_at');
    if (!empty($excludeSampleIds)) {
      $query->whereNotIn('id_samples', $excludeSampleIds);
    }

    $candidates = $query->where(function ($q) use ($code, $normalized) {
      $q->where('codesample_samples', $code);
      if ($normalized !== $code) {
        $q->orWhere('codesample_samples', $normalized);
      }
    })->limit(20)->get();

    foreach ($candidates as $candidate) {
      $candidateNorm = $this->normalizeKesmasManualSampleCode($candidate->codesample_samples ?? '');
      if ($candidateNorm === $normalized || trim((string) ($candidate->codesample_samples ?? '')) === $code) {
        return $candidate;
      }
    }

    return null;
  }

  /**
   * Info konflik nomor sampel untuk ditampilkan di frontend.
   */
  protected function formatSampleCodeConflictInfo(Sample $conflictSample, string $attemptedCode): array
  {
    $sampleType = SampleType::find($conflictSample->typesample_samples);
    $displayName = $conflictSample->name_customer_pdam
      ?: $conflictSample->name_pelanggan
      ?: $conflictSample->titik_pengambilan
      ?: '-';

    return [
      'sample_id'            => $conflictSample->id_samples,
      'codesample_samples'   => $conflictSample->codesample_samples,
      'attempted_code'       => $attemptedCode,
      'name_pelanggan'       => html_entity_decode(strip_tags((string) ($conflictSample->name_pelanggan ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
      'name_customer_pdam'   => html_entity_decode(strip_tags((string) ($conflictSample->name_customer_pdam ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
      'titik_pengambilan'    => html_entity_decode(strip_tags((string) ($conflictSample->titik_pengambilan ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
      'display_name'         => html_entity_decode(strip_tags((string) $displayName), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
      'name_sample_type'     => optional($sampleType)->name_sample_type,
      'permohonan_uji_id'    => $conflictSample->permohonan_uji_id,
      'date_sending'         => $conflictSample->date_sending,
    ];
  }

  /**
   * Terapkan kode sampel baru ke satu record (termasuk LabNum / sequence bila ada).
   */
  protected function applySampleCodeToSample(Sample $sample, string $newCode, ?string $sampleTypeCode = null): void
  {
    $newCode = $this->normalizeKesmasManualSampleCode($newCode, $sampleTypeCode);
    $oldCode = $sample->codesample_samples;

    $sample->codesample_samples = $newCode;
    if (Schema::hasColumn('tb_samples', 'codesample_samples_manual')) {
      $sample->codesample_samples_manual = $newCode;
    }

    $parts = explode('/', $newCode);
    if (count($parts) >= 2) {
      $seq = (int) preg_replace('/\D/', '', (string) $parts[1]);
      if ($seq > 0) {
        $sample->count_id = $seq;
      }
    }

    if (Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
      $sample->is_nomor_sampel_manual = 1;
    }

    $sample->save();

    $labNum = LabNum::where('sample_id', $sample->id_samples)->first();
    if ($labNum && $oldCode !== $newCode) {
      $this->updateGlobalLabSequenceDetailForCodeChange($sample, $oldCode, $labNum);
    }
  }

  /**
   * Deteksi / selesaikan konflik nomor sampel saat update.
   * Mengembalikan JsonResponse jika harus dihentikan, null jika boleh lanjut.
   */
  protected function handleSampleCodeConflictOnUpdate(Sample $sample, string $oldCode, string $newCode, Request $request)
  {
    $conflict = $this->findConflictingSampleByCode($newCode, [$sample->id_samples]);
    if (!$conflict) {
      return null;
    }

    if (!$request->boolean('resolve_sample_code_conflict')) {
      return response()->json([
        'status'    => false,
        'conflict'  => true,
        'pesan'     => 'Nomor sampel sudah digunakan oleh sampel lain.',
        'conflicts' => [
          $this->formatSampleCodeConflictInfo($conflict, $newCode),
        ],
      ], 200);
    }

    $resolutions = json_decode((string) $request->input('conflict_resolutions', '[]'), true);
    if (!is_array($resolutions) || empty($resolutions)) {
      return response()->json([
        'status' => false,
        'pesan'  => 'Konflik nomor sampel: tentukan nomor baru untuk sampel yang bentrok.',
      ], 200);
    }

    $resolutionMap = [];
    foreach ($resolutions as $res) {
      if (!empty($res['sample_id']) && isset($res['new_code']) && trim((string) $res['new_code']) !== '') {
        $resolutionMap[(string) $res['sample_id']] = trim((string) $res['new_code']);
      }
    }

    if (!isset($resolutionMap[(string) $conflict->id_samples])) {
      return response()->json([
        'status' => false,
        'pesan'  => 'Konflik nomor sampel: masukkan nomor baru untuk sampel ' . ($conflict->codesample_samples ?? ''),
      ], 200);
    }

    $conflictSampleType = SampleType::find($conflict->typesample_samples);
    $conflictTypeCode = optional($conflictSampleType)->code_sample_type;
    $newCodeForConflict = $this->normalizeKesmasManualSampleCode(
      $resolutionMap[(string) $conflict->id_samples],
      $conflictTypeCode
    );

    $secondConflict = $this->findConflictingSampleByCode($newCodeForConflict, [
      $sample->id_samples,
      $conflict->id_samples,
    ]);
    if ($secondConflict) {
      return response()->json([
        'status'    => false,
        'conflict'  => true,
        'pesan'     => 'Nomor pengganti masih bentrok dengan sampel lain.',
        'conflicts' => [
          $this->formatSampleCodeConflictInfo($secondConflict, $newCodeForConflict),
        ],
      ], 200);
    }

    $this->applySampleCodeToSample($conflict, $newCodeForConflict, $conflictTypeCode);

    return null;
  }

  /**
   * Terapkan perubahan nomor sampel Kesmas (konflik, persist manual, sync pasangan Kimia/Mikro).
   * Cari pasangan pakai kode LAMA agar urutan baru tidak kehilangan pasangan.
   *
   * @return \Illuminate\Http\JsonResponse|null
   */
  protected function applyKesmasSampleCodeChangeOnUpdate(
    Sample $sample,
    string $oldCode,
    string $newCode,
    Request $request
  ) {
    $sampleTypeRow = SampleType::find($sample->typesample_samples);
    $sampleTypeCode = optional($sampleTypeRow)->code_sample_type;
    $normalizedCode = $this->normalizeKesmasManualSampleCode($newCode, $sampleTypeCode);

    $conflictResponse = $this->handleSampleCodeConflictOnUpdate(
      $sample,
      $oldCode,
      $normalizedCode,
      $request
    );
    if ($conflictResponse !== null) {
      return $conflictResponse;
    }

    // Pair lookup harus pakai kode lama (sebelum urutan berubah).
    $sampleForPairLookup = clone $sample;
    $sampleForPairLookup->codesample_samples = $oldCode;
    $pairedSample = $this->findPairedKesmasSample($sampleForPairLookup);

    $this->persistKesmasManualSampleCode($sample, $normalizedCode, $sampleTypeCode);

    if ($pairedSample) {
      $pairedTypeRow = SampleType::find($pairedSample->typesample_samples);
      $pairedTypeCode = optional($pairedTypeRow)->code_sample_type;
      $pairedLabNum = LabNum::where('sample_id', $pairedSample->id_samples)->first();
      $pairedIsKimia = $pairedLabNum && $pairedLabNum->lab_id
        ? $this->isKimiaLaboratorium(Laboratorium::find($pairedLabNum->lab_id))
        : false;
      $pairedLabCode = $pairedIsKimia ? '01' : '02';
      $pairedNewCode = $this->formatKesmasCodeForLabDisplay($normalizedCode, $pairedLabCode);
      $pairedOldCode = (string) ($pairedSample->codesample_samples ?? '');

      if ($pairedNewCode !== '' && $pairedNewCode !== $pairedOldCode) {
        $this->persistKesmasManualSampleCode($pairedSample, $pairedNewCode, $pairedTypeCode);
        $pairedSample->save();

        $pairedLabNumRow = LabNum::where('sample_id', $pairedSample->id_samples)->first();
        if ($pairedLabNumRow) {
          $this->updateGlobalLabSequenceDetailForCodeChange($pairedSample, $pairedOldCode, $pairedLabNumRow);
        }
      }
    }

    return null;
  }

  /**
   * Nomor laboratorium manual (angka) saat setting Kesmas aktif.
   */
  protected function getKesmasManualLabNumFromRequest(Request $request, string $labShortName): ?int
  {
    if (!KesmasSampleNumberSettings::getSettings()->is_nomor_laboratorium_manual) {
      return null;
    }
    $key = ($labShortName === 'kimia') ? 'manual_nomer_lab_kimia' : 'manual_nomer_lab_mikro';
    $v = $request->input($key);
    if ($v === null || $v === '') {
      return null;
    }

    return (int) $v;
  }

  /**
   * Nomor laboratorium manual per baris konfigurasi sampel (multi-jenis / AJAX samples[]).
   */
  protected function getKesmasManualLabNumFromSampleConfig(array $sampleConfig, string $current_lab_name): ?int
  {
    if (!KesmasSampleNumberSettings::getSettings()->is_nomor_laboratorium_manual) {
      return null;
    }
    $key = ($current_lab_name === 'kimia') ? 'nomer_lab_kimia' : 'nomer_lab_mikro';
    $v = $sampleConfig[$key] ?? null;
    if ($v === null || $v === '') {
      return null;
    }

    return (int) $v;
  }

  /**
   * Lab Kimia/Mikro yang dipakai dari daftar method (format methodId_labId_price).
   *
   * @return array{kimia: bool, mikro: bool}
   */
  protected function labsUsedFromMethodStrings(array $methodStrings): array
  {
    $useKimia = false;
    $useMikro = false;

    foreach ($methodStrings as $methodString) {
      $parts = explode('_', (string) $methodString);
      if (count($parts) < 2) {
        continue;
      }
      $lab = Laboratorium::find($parts[1]);
      if (!$lab) {
        continue;
      }
      $kode = strtoupper((string) ($lab->kode_laboratorium ?? ''));
      $nama = strtolower((string) ($lab->nama_laboratorium ?? ''));
      if (in_array($kode, ['KIM', 'KMA', 'FKA'], true) || $nama === 'kimia') {
        $useKimia = true;
      }
      if ($kode === 'MBI' || strpos($nama, 'mikro') !== false) {
        $useMikro = true;
      }
    }

    return ['kimia' => $useKimia, 'mikro' => $useMikro];
  }

  /**
   * Validasi nomor lab manual wajib terisi untuk setiap lab yang dipakai parameter.
   *
   * @throws \InvalidArgumentException
   */
  protected function assertKesmasManualLabNumbersFilled(Request $request, array $usedLabs, ?array $sampleConfigRow = null): void
  {
    if (!KesmasSampleNumberSettings::getSettings()->is_nomor_laboratorium_manual) {
      return;
    }

    $missing = [];

    if (!empty($usedLabs['kimia'])) {
      $raw = $sampleConfigRow !== null
        ? ($sampleConfigRow['nomer_lab_kimia'] ?? null)
        : $request->input('manual_nomer_lab_kimia');
      $num = (int) preg_replace('/\D/', '', (string) $raw);
      if ($num < 1) {
        $missing[] = 'Kimia';
      }
    }

    if (!empty($usedLabs['mikro'])) {
      $raw = $sampleConfigRow !== null
        ? ($sampleConfigRow['nomer_lab_mikro'] ?? null)
        : $request->input('manual_nomer_lab_mikro');
      $num = (int) preg_replace('/\D/', '', (string) $raw);
      if ($num < 1) {
        $missing[] = 'Mikrobiologi';
      }
    }

    if ($missing !== []) {
      throw new \InvalidArgumentException(
        'Nomor laboratorium manual wajib diisi untuk lab: ' . implode(', ', $missing) . '. Lengkapi saat validasi / pengesahan hasil.'
      );
    }
  }

  /**
   * Apakah kode sampel manual sudah berisi angka urut (segmen tengah).
   */
  protected function kesmasManualSampleCodeHasUrut(?string $code): bool
  {
    if ($code === null || trim($code) === '') {
      return false;
    }
    $parts = explode('/', trim($code));
    if (count($parts) < 2) {
      return false;
    }
    $middle = $parts[count($parts) >= 3 ? 1 : 0];

    return (int) preg_replace('/\D/', '', (string) $middle) >= 1;
  }

  /**
   * Validasi kode/nomor sampel manual wajib terisi (hanya saat is_nomor_sampel_manual aktif).
   *
   * @throws \InvalidArgumentException
   */
  protected function assertKesmasManualSampleCodesFilled(Request $request, array $usedLabs, ?array $sampleConfigRow = null): void
  {
    if (!KesmasSampleNumberSettings::getSettings()->is_nomor_sampel_manual) {
      return;
    }

    $missing = [];

    if (!empty($usedLabs['kimia'])) {
      $raw = null;
      if ($sampleConfigRow !== null) {
        $raw = $sampleConfigRow['code_sample_kimia'] ?? $sampleConfigRow['code_sample'] ?? null;
      } else {
        $raw = $request->input('code_sample_kimia') ?: $request->input('manual_code_sample_kimia');
      }
      if (!$this->kesmasManualSampleCodeHasUrut($raw !== null ? (string) $raw : null)) {
        $missing[] = 'Kimia';
      }
    }

    if (!empty($usedLabs['mikro'])) {
      $raw = null;
      if ($sampleConfigRow !== null) {
        $raw = $sampleConfigRow['code_sample_mikro'] ?? $sampleConfigRow['code_sample'] ?? null;
      } else {
        $raw = $request->input('code_sample_mikro') ?: $request->input('manual_code_sample_mikro');
      }
      if (!$this->kesmasManualSampleCodeHasUrut($raw !== null ? (string) $raw : null)) {
        $missing[] = 'Mikrobiologi';
      }
    }

    if ($missing !== []) {
      throw new \InvalidArgumentException(
        'Nomor sampel manual wajib diisi untuk: ' . implode(', ', $missing) . '. Lengkapi di langkah Review & Simpan.'
      );
    }
  }

  /**
   * Satu baris nomor lab Kesmas per permohonan per laboratorium (isi awal dari input manual).
   */
  protected function upsertNomerLabKesmasIfManual(string $permohonanUjiId, string $labId, int $nomerLab): void
  {
    if ($nomerLab < 1) {
      return;
    }
    $exists = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiId)
      ->where('laboratorium_id', $labId)
      ->first();
    if ($exists) {
      return;
    }
    NomerLabKesmas::create([
      'id' => Uuid::uuid4()->toString(),
      'permohonan_uji_id' => $permohonanUjiId,
      'laboratorium_id' => $labId,
      'nomer_lab' => $nomerLab,
      'year' => (int) date('Y'),
    ]);
  }

  /**
   * Setelah LabNum disimpan: mode otomatis = link baris dari GlobalLabSequence::getNextNumber;
   * mode manual (is_nomor_laboratorium_manual) = GlobalLabSequenceDetail.sequence_number = nomor manual.
   */
  protected function linkGlobalLabSequenceDetailForLabNum(
    string $labId,
    int $labNumber,
    string $labNumId,
    bool $isManualKesmasLabNum,
    $year = null
  ): void {
    if ($labNumber < 1 || $labNumId === '') {
      return;
    }

    $year = GlobalLabSequence::resolveYear($year);

    if ($isManualKesmasLabNum) {
      $detail = GlobalLabSequenceDetail::where('year', $year)
        ->where('lab_type', 'lab')
        ->where('lab_id', $labId)
        ->where('sequence_number', $labNumber)
        ->where(function ($q) use ($labNumId) {
          $q->whereNull('reference_id')->orWhere('reference_id', $labNumId);
        })
        ->orderBy('created_at', 'desc')
        ->first();

      if (!$detail) {
        GlobalLabSequenceDetail::create([
          'year' => $year,
          'sequence_number' => $labNumber,
          'lab_id' => $labId,
          'lab_type' => 'lab',
          'reference_id' => $labNumId,
        ]);
      } else {
        $detail->update(['reference_id' => $labNumId]);
      }

      GlobalLabSequence::raiseLastNumberToAtLeast($labNumber, $year);

      return;
    }

    $sequence_detail = GlobalLabSequenceDetail::where('sequence_number', $labNumber)
      ->where('year', $year)
      ->where('lab_id', $labId)
      ->whereNull('reference_id')
      ->orderBy('created_at', 'desc')
      ->first();

    if ($sequence_detail) {
      $sequence_detail->update(['reference_id' => $labNumId]);
    }
  }

  /**
   * Nomor LHU: format baku 449.5 mengikuti setting Kesmas dan flag per sampel.
   */
  protected function applyKesmasNomorLabBakuToNoLhu(?Sample $sampleModel, string $no_LHU, $year, $labId): string
  {
    if (!$sampleModel) {
      return $no_LHU;
    }
    $baku = $sampleModel->getNomorLab('449.5', $year ?? date('Y'), $labId);
    if ($baku === null || $baku === '') {
      return $no_LHU;
    }
    $kesmas = KesmasSampleNumberSettings::getSettings();
    if (!$kesmas->is_nomor_laboratorium_manual) {
      return $baku;
    }

    return $sampleModel->is_nomor_laboratorium_manual ? $baku : $no_LHU;
  }

  /**
   * Progress ID override baku mutu harus selaras dengan baca-hasil
   * (sample_progress_id = laboratorium_progress_id langkah link "baca-hasil",
   * atau fallback id_sample_analitik_progress bila pernah tersimpan demikian).
   *
   * Selalu sertakan ID langkah baca-hasil lab agar override tetap terbaca di cetak LHU
   * meskipun baris SampleAnalitikProgress belum ada.
   */
  protected function bacaHasilProgressIdsForSample(string $sampleId, string $laboratoriumId)
  {
    $bacaHasilProgressIds = LaboratoriumProgress::query()
      ->where('laboratorium_id', $laboratoriumId)
      ->where('link', 'baca-hasil')
      ->whereNull('deleted_at')
      ->pluck('id_laboratorium_progress');

    $analitikQuery = SampleAnalitikProgress::query()
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
      // Legacy Magelang: URL lama "Pemeriksaan" sempat dipakai sebagai baca-hasil
      ->merge(collect(['bfecda4a-73f2-47d6-9fc3-01f65e0f02a1', 'bc2850f5-4ec4-450f-a727-2b1428c861d9']))
      ->filter()
      ->unique()
      ->values();
  }

  /**
   * Terapkan override baku mutu khusus sampel ke koleksi method cetak/preview.
   */
  protected function applyBakuMutuSampleOverridesToMethods($laboratoriummethods, string $sampleId, string $laboratoriumId)
  {
    $methods = $laboratoriummethods instanceof \Illuminate\Support\Collection
      ? $laboratoriummethods
      : collect($laboratoriummethods);

    if ($methods->isEmpty()) {
      return $laboratoriummethods;
    }

    $sampleProgressIds = $this->bacaHasilProgressIdsForSample($sampleId, $laboratoriumId);
    if ($sampleProgressIds->isEmpty()) {
      return $laboratoriummethods;
    }

    $bmOverrides = \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $sampleProgressIds)
      ->get()
      ->keyBy('method_id');

    if ($bmOverrides->isEmpty()) {
      return $laboratoriummethods;
    }

    $overrideUnitIds = $bmOverrides->pluck('unit_id')->filter()->unique()->values()->all();
    $overrideUnitsById = !empty($overrideUnitIds)
      ? Unit::whereIn('id_unit', $overrideUnitIds)->get()->keyBy('id_unit')
      : collect();
    $hasOverrideLibrary = Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id');

    $methods = $methods->map(function ($item) use ($bmOverrides, $overrideUnitsById, $hasOverrideLibrary) {
      $methodId = $item->method_id ?? $item->id_method ?? null;
      if ($methodId === null || !isset($bmOverrides[$methodId])) {
        return $item;
      }

      $ov = $bmOverrides[$methodId];
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
      if (empty($item->name_report)) {
        $item->name_report = $item->params_method ?? $item->name_method ?? '-';
      }
      $item->has_sample_override = true;

      return $item;
    });

    return $methods;
  }

  /**
   * Terapkan override baku mutu khusus sampel ke struktur $table cetak mikro
   * (tiap baris punya sample_type + result/hasil[].method_id).
   */
  protected function applyBakuMutuSampleOverridesToPrintTable(array $table, string $laboratoriumId): array
  {
    if (empty($table) || $laboratoriumId === '') {
      return $table;
    }

    $hasOverrideLibrary = Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id');
    $overrideCacheBySample = [];
    $unitCache = [];

    foreach ($table as $idx => $sampleOne) {
      $sampleObj = $sampleOne['sample_type'] ?? ($sampleOne['sample'] ?? null);
      $sampleId = null;
      if (is_object($sampleObj)) {
        $sampleId = $sampleObj->id_samples ?? null;
      } elseif (is_array($sampleObj)) {
        $sampleId = $sampleObj['id_samples'] ?? null;
      }
      if (!$sampleId) {
        continue;
      }

      if (!array_key_exists($sampleId, $overrideCacheBySample)) {
        $progressIds = $this->bacaHasilProgressIdsForSample((string) $sampleId, $laboratoriumId);
        $overrideCacheBySample[$sampleId] = $progressIds->isEmpty()
          ? collect()
          : \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $progressIds)
            ->get()
            ->keyBy('method_id');
      }

      $bmOverrides = $overrideCacheBySample[$sampleId];
      if ($bmOverrides->isEmpty()) {
        continue;
      }

      foreach (['result', 'hasil'] as $resultKey) {
        if (empty($sampleOne[$resultKey]) || !is_array($sampleOne[$resultKey])) {
          continue;
        }

        foreach ($sampleOne[$resultKey] as $rIdx => $resultRow) {
          $methodId = $resultRow['method_id'] ?? null;
          if (!$methodId || !isset($bmOverrides[$methodId])) {
            continue;
          }

          $ov = $bmOverrides[$methodId];
          if (!is_null($ov->nilai_baku_mutu)) {
            $sampleOne[$resultKey][$rIdx]['nilai_baku_mutu'] = $ov->nilai_baku_mutu;
          }
          if (!is_null($ov->min)) {
            $sampleOne[$resultKey][$rIdx]['min'] = $ov->min;
          }
          if (!is_null($ov->max)) {
            $sampleOne[$resultKey][$rIdx]['max'] = $ov->max;
          }
          if (!is_null($ov->equal)) {
            $sampleOne[$resultKey][$rIdx]['equal'] = $ov->equal;
          }
          if (!is_null($ov->unit_id) && $ov->unit_id !== '') {
            if (!array_key_exists($ov->unit_id, $unitCache)) {
              $unitCache[$ov->unit_id] = Unit::where('id_unit', $ov->unit_id)->first();
            }
            $unit = $unitCache[$ov->unit_id];
            if ($unit) {
              $sampleOne[$resultKey][$rIdx]['satuan_bakumutu'] = $unit->shortname_unit ?? $unit->name_unit;
            }
          }
          if ($hasOverrideLibrary && !is_null($ov->library_id) && $ov->library_id !== '') {
            $sampleOne[$resultKey][$rIdx]['library_id'] = $ov->library_id;
          }
          $sampleOne[$resultKey][$rIdx]['has_sample_override'] = true;
        }
      }

      $table[$idx] = $sampleOne;
    }

    return $table;
  }

  /**
   * Terapkan override baku mutu khusus sampel ke relasi nested
   * sample->sampleresult->method->bakumutu (format gabungan mikro).
   */
  protected function applyBakuMutuSampleOverridesToSampleCollection($samples, string $laboratoriumId)
  {
    if (!$samples || (is_countable($samples) && count($samples) === 0) || $laboratoriumId === '') {
      return $samples;
    }

    $hasOverrideLibrary = Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id');
    $unitCache = [];

    foreach ($samples as $sample) {
      $sampleId = (string) ($sample->id_samples ?? '');
      if ($sampleId === '') {
        continue;
      }

      $progressIds = $this->bacaHasilProgressIdsForSample($sampleId, $laboratoriumId);
      if ($progressIds->isEmpty()) {
        continue;
      }

      $bmOverrides = \Smt\Masterweb\Models\BakuMutuSampleOverride::whereIn('sample_progress_id', $progressIds)
        ->get()
        ->keyBy('method_id');
      if ($bmOverrides->isEmpty()) {
        continue;
      }

      foreach ($sample->sampleresult ?? [] as $sampleresult) {
        $methodId = $sampleresult->method_id ?? null;
        if (!$methodId || !isset($bmOverrides[$methodId])) {
          continue;
        }

        $ov = $bmOverrides[$methodId];
        foreach ($sampleresult->method ?? [] as $method) {
          $bm = $method->bakumutu;
          if (!$bm) {
            $bm = new \Smt\Masterweb\Models\BakuMutu();
            $bm->method_id = $method->id_method;
            $method->setRelation('bakumutu', $bm);
          }

          if (!is_null($ov->nilai_baku_mutu)) {
            $bm->nilai_baku_mutu = $ov->nilai_baku_mutu;
          }
          if (!is_null($ov->min)) {
            $bm->min = $ov->min;
          }
          if (!is_null($ov->max)) {
            $bm->max = $ov->max;
          }
          if (!is_null($ov->equal)) {
            $bm->equal = $ov->equal;
          }
          if (!is_null($ov->unit_id) && $ov->unit_id !== '') {
            $bm->unit_id = $ov->unit_id;
            if (!array_key_exists($ov->unit_id, $unitCache)) {
              $unitCache[$ov->unit_id] = Unit::where('id_unit', $ov->unit_id)->first();
            }
            if ($unitCache[$ov->unit_id]) {
              $bm->setRelation('unit', $unitCache[$ov->unit_id]);
            }
          }
          if ($hasOverrideLibrary && !is_null($ov->library_id) && $ov->library_id !== '') {
            $bm->library_id = $ov->library_id;
          }
        }
      }
    }

    return $samples;
  }


  /**
   * Retrieves the number of lab records for the given lab key in the current year.
   *
   * @param string $lab_key The lab key to filter records by.
   * @return int The number of lab records for the given lab key in the current year.
   */
  public function getLabNumByLabKey($lab_key,$id,$is_makanan=false): int
  {

    // dd($id);

    $start_num= StartNum::join('ms_laboratorium', function ($join) {
      $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereNull('ms_start_number.deleted_at');
    })->where('id_laboratorium',$lab_key)->first();



    $permohonan_uji = PermohonanUji::findOrfail($id);


    if ($is_makanan) {
      # code...
      $start_num= StartNum::where('code_lab_start_number','MAK-MIN')->first();

      // dd($start_num);




      $lab_num =
      LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
        // ->where('lab_id', $lab_key)
        ->where('is_makanan',1)
        ->where('lab_id', $lab_key)
        ->where('tb_lab_num.created_at','<=',$permohonan_uji->created_at)
        // ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
        //     $query->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
        //     ->where('is_makanan',1)
        //     ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
        // })
        ->where('year_lab_num', '=', date('Y'))
        // ->tb_samples.created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')
        ->count();


      if ($lab_num>0) {
        # code...
        // dd($lab_num);


        return LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
        ->where('created_at','<',$permohonan_uji->created_at)
        ->where('is_makanan',1)
        ->where('lab_id', $lab_key)
        ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
          $query->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
          ->where('is_makanan',1)
          ->where('lab_id', $lab_key)
          ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
      })
        ->max('lab_number');

      }else{
        return LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
        ->where('is_makanan',1)
        ->where('lab_id', $lab_key)
          ->count() +$start_num->count_start_number;

      }


    }else{

      $lab_num=LabNum::where('lab_id', $lab_key)
      // ->where(function($query) use ($permohonan_uji,$lab_key) {

      //   $query

      //   // ->whereDay('tb_lab_num.created_at', '<=',(int) date('d'));
      //   // ->where(DB::raw('HOUR(tb_lab_num.created_at)'), '<=', date('H'));
      // })

      ->where(DB::raw('YEAR(tb_lab_num.created_at)'), '=', (int)date('Y'))
      ->whereMonth('tb_lab_num.created_at', '>=', (int) date('M'))
      // ->where(DB::raw('MOUNT(tb_lab_num.created_at)'), '<=', date('M'))

    //   ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
    //     $query->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
    //     ->where('lab_id', $lab_key)
    //     ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
    // })

    // ->where('year_lab_num', '=', date('Y'))
    // ->where(DB::raw('YEAR(tb_lab_num.created_at)'), '=', date('Y'))
      ->count();




      // dd(  $lab_num);

    //   dd(LabNum::where('lab_id', $lab_key)
    //   ->where('created_at','<=',$permohonan_uji->created_at)
    //   ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
    //     $query
    //     ->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
    //     ->where('lab_id', $lab_key)
    //     ->where(DB::raw('YEAR(tb_lab_num.created_at)'), '=', date('Y'));
    // })
    // ->where(DB::raw('YEAR(tb_lab_num.created_at)'), '=', date('Y'))
    // ->first());

      // dd($lab_num);
      if ($lab_num>0  ) {
        # code...
        // dd($lab_num);

        $last_lab_num_permohonan_uji=LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))

          ->where('year_lab_num', '=', date('Y'))

          // ->where('created_at','<',$permohonan_uji->created_at)
          ->where('permohonan_uji_id',$permohonan_uji->id_permohonan_uji)
          ->where('lab_id', $lab_key)
        //   ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
        //     $query
        //     // ->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
        //     ->where('lab_id', $lab_key)
        //     ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
        // })
          ->max('lab_number');

        $last_lab_num = LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))

          ->where('year_lab_num', '=', date('Y'))

          // ->where('created_at','<',$permohonan_uji->created_at)
          ->where('lab_id', $lab_key)
          ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
            $query
            // ->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
            ->where('lab_id', $lab_key)
            ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
        })
          ->max('lab_number');


          // if ($last_lab_num_permohonan_uji) {
          //  re
          // }




      //   return LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))

      //   ->where('year_lab_num', '=', date('Y'))

      //   ->where('created_at','<',$permohonan_uji->created_at)
      //   ->where('lab_id', $lab_key)
      //   ->orWhere(function($query) use ($permohonan_uji,$lab_key) {
      //     $query->where('permohonan_uji_id','<=',$permohonan_uji->id_permohonan_uji)
      //     ->where('lab_id', $lab_key)
      //     ->where(DB::raw('YEAR(created_at)'), '=', date('Y'));
      // })
      //   ->max('lab_number');

        // if ($lab_key=="3416ca19-6c69-4e5f-a004-ae8275de7644") {
        //   # code...
        //   dd($last_lab_num);
        // }

        return $last_lab_num;

      }else{


        if (date('Y')==$start_num->year_start_number) {
          # code...
          return LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
          ->where('lab_id', $lab_key)
          ->where('year_lab_num', '=', date('Y'))
          ->count() +$start_num->count_start_number;
        }else{
          return 0;
        }


      }
    }


  }

  public function getCodeSample($count, $lab_code = '01', $sample_type_code = '...')
  {
    // Format: {kode_jenis_sample}.{kode_lab}/{nomer_urut}/{tahun}
    // Example: AM.01/0003/2025 (Kimia) or AM.02/0004/2025 (Mikro)
    // Note: $count is now the actual number (not count - 1), so we use it directly

    $code_number = str_pad((int)$count, 4, '0', STR_PAD_LEFT);
    $code_datetime = now();
    $code_year = $code_datetime->format('Y');

    // Format: SAMPLE_TYPE.LAB_CODE/NUMBER/YEAR
    $code = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;
    return $code;
  }

  private function getCodeSampleIndexes(array $parts)
  {
    // New format: 449.5/01/0001/2026 => seq at index 2, year at index 3
    if (count($parts) >= 4) {
      return [2, 3];
    }

    // Legacy format: AB.01/0001/2026 => seq at index 1, year at index 2
    if (count($parts) >= 3) {
      return [1, 2];
    }

    return [null, null];
  }

  private function extractSampleSequence($codeSample)
  {
    $parts = explode('/', (string) $codeSample);
    [$sequenceIndex, $yearIndex] = $this->getCodeSampleIndexes($parts);

    if ($sequenceIndex === null || !isset($parts[$sequenceIndex])) {
      return null;
    }

    return (int) $parts[$sequenceIndex];
  }

  private function extractSampleYear($codeSample)
  {
    $parts = explode('/', (string) $codeSample);
    [, $yearIndex] = $this->getCodeSampleIndexes($parts);

    if ($yearIndex === null || !isset($parts[$yearIndex])) {
      return null;
    }

    return (int) $parts[$yearIndex];
  }

  private function replaceSampleSequenceAndYear($codeSample, $sequenceNumber, $year = null)
  {
    $parts = explode('/', (string) $codeSample);
    [$sequenceIndex, $yearIndex] = $this->getCodeSampleIndexes($parts);

    if ($sequenceIndex === null || $yearIndex === null) {
      return $codeSample;
    }

    $parts[$sequenceIndex] = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
    $parts[$yearIndex] = (string) ($year ?: date('Y'));

    return implode('/', $parts);
  }

  /**
   * Optimized helper: attach baku mutu + unit metadata
   * to a list of methods with a single batched query.
   */
  private function attachBakuMutuToMethods($methodAllRaw, $sampleTypeId, array $jenisMakananIds = [], bool $hasNullJenisMakanan = false, bool $preferSimpleFirst = false)
  {
    $methodAll = collect();
    if (!$methodAllRaw || $methodAllRaw->count() === 0) {
      return $methodAll;
    }

    $methodIds = $methodAllRaw->pluck('id_method')->filter()->unique()->values()->all();
    if (empty($methodIds)) {
      return $methodAllRaw;
    }

    $allBakuMutu = BakuMutu::whereIn('method_id', $methodIds)
      ->where('sampletype_id', $sampleTypeId)
      ->whereNull('deleted_at')
      ->get()
      ->groupBy('method_id');

    $unitIds = $allBakuMutu->flatten()->pluck('unit_id')->filter()->unique()->values()->all();
    $unitsById = empty($unitIds)
      ? collect()
      : Unit::whereIn('id_unit', $unitIds)->whereNull('deleted_at')->get()->keyBy('id_unit');

    foreach ($methodAllRaw as $method) {
      $candidates = $allBakuMutu->get($method->id_method, collect());
      $selectedBakuMutu = null;

      if ($candidates->count() > 0) {
        if ($preferSimpleFirst) {
          $selectedBakuMutu = $candidates->first();
        } else {
          if (!empty($jenisMakananIds)) {
            $selectedBakuMutu = $candidates->first(function ($bm) use ($jenisMakananIds, $hasNullJenisMakanan) {
              return in_array($bm->jenis_makanan_id, $jenisMakananIds, true)
                || ($hasNullJenisMakanan && is_null($bm->jenis_makanan_id));
            });
          } elseif ($hasNullJenisMakanan) {
            $selectedBakuMutu = $candidates
              ->sortBy(function ($bm) {
                return is_null($bm->jenis_makanan_id) ? 0 : 1;
              })
              ->first();
          } else {
            $selectedBakuMutu = $candidates->first();
          }
        }
      }

      if ($selectedBakuMutu) {
        foreach ($selectedBakuMutu->getAttributes() as $key => $value) {
          $method->{$key} = $value;
        }
        $unitBakuMutu = $unitsById->get($selectedBakuMutu->unit_id);
        if ($unitBakuMutu) {
          foreach ($unitBakuMutu->getAttributes() as $key => $value) {
            $method->{'unit_' . $key} = $value;
          }
          // Template cetak (air_limbah, dll) memakai shortname_unit / name_unit tanpa prefix
          $method->shortname_unit = $unitBakuMutu->shortname_unit;
          $method->name_unit = $unitBakuMutu->name_unit;
        }
      }

      $methodAll->push($method);
    }

    return $methodAll;
  }

  /**
   * Ambil method list unik untuk print mikro (MBI)
   * dengan filter sample type + optional packet.
   */
  private function fetchMethodAllRawMbi(string $idPermohonanUji, $sampleTypeId = null, $packetId = null)
  {
    $query = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->where('ms_laboratorium.kode_laboratorium', 'MBI')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      });

    if (!is_null($sampleTypeId)) {
      $query->where('tb_samples.typesample_samples', $sampleTypeId);
    }
    if (!is_null($packetId)) {
      $query->where('tb_samples.packet_id', $packetId);
    }

    return $query
      ->select('ms_method.*')
      ->distinct('ms_method.id_method')
      ->get();
  }

  public function getNewNumberSequence(string $lab_key ,$id,$is_makanan = false)
  {
    return $this->getLabNumByLabKey($lab_key,$id,$is_makanan);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request, $id)
  {

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
      ->whereNull('tb_permohonan_uji.deleted_at')
      ->with(['customer' => function($query) {
        $query->whereNull('ms_customer.deleted_at');
      }])
      ->first();

    // Fallback: if customer relationship is not loaded, try to load it directly
    if ($permohonan_uji && !$permohonan_uji->customer && $permohonan_uji->customer_id) {
      $permohonan_uji->customer = Customer::where('id_customer', $permohonan_uji->customer_id)
        ->whereNull('deleted_at')
        ->first();
    }

    $check_mikro = Sample::where('permohonan_uji_id', '=', $id)
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
      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
          ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('id_laboratorium')
      ->distinct('id_laboratorium')
      ->where('id_laboratorium', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      ->get();


    $packet_prints = Sample::where('permohonan_uji_id', '=', $id)
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
      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
          ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('ms_jenis_makanan', function ($join) {
        $join->on('ms_jenis_makanan.id_jenis_makanan', '=', 'tb_samples.jenis_makanan_id')
          ->whereNull('tb_samples.deleted_at')
          ->whereNull('ms_jenis_makanan.deleted_at');
      })
      ->leftjoin('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')

          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('id_laboratorium', 'ms_jenis_makanan.*', 'typesample_samples', 'ms_sample_type.id_sample_type', 'ms_sample_type.name_sample_type', 'tb_samples.jenis_makanan_id')
      ->distinct('id_laboratorium', 'ms_sample_type.id_sample_type', 'tb_samples.jenis_makanan_id')
      ->where('id_laboratorium', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      ->get();


    if (request()->ajax()) {
      $datas = Sample::where('permohonan_uji_id', '=', $id)
        ->leftjoin('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at')
            ->leftjoin('ms_laboratorium', function ($join) {
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
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        ->leftjoin('ms_jenis_makanan', function ($join) {
          $join->on('ms_jenis_makanan.id_jenis_makanan', '=', 'tb_samples.jenis_makanan_id')
            ->whereNull('tb_samples.deleted_at')
            ->whereNull('ms_jenis_makanan.deleted_at');
        })

        ->leftjoin('tb_sample_penanganan', function ($join) use ($id) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pelaporan_hasil', function ($join) {
          $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_pelaporan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_verifikasi_hasil', function ($join) {
          $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_verifikasi_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select(array_merge(
          [
            'tb_sample_penanganan.id_sample_penanganan',
            'tb_sample_penanganan.penyimpanan_sample',
            'tb_sample_penanganan.date_checking',
            'tb_sample_penanganan.date_done_estimation_labs',
            'tb_sample_penanganan.destroyed_sample_date',
            'tb_sample_penanganan.penanganan_sample_date',
            'ms_jenis_makanan.id_jenis_makanan',
            'ms_jenis_makanan.name_jenis_makanan',
            'ms_jenis_makanan.olahan_jenis_makanan',
            'ms_jenis_makanan.price_jenis_makanan',
            'tb_pengesahan_hasil.id_pengesahan_hasil',
            'tb_pengesahan_hasil.pengesahan_hasil_date',
            'tb_pelaporan_hasil.id_pelaporan_hasil',
            'tb_pelaporan_hasil.pelaporan_hasil_date',
            'tb_verifikasi_hasil.id_verifikasi_hasil',
            'tb_verifikasi_hasil.verifikasi_hasil_date',
            'tb_sample_penerimaan.id_sample_penerimaan',
            'tb_sample_penerimaan.wadah_id',
            'tb_sample_penerimaan.wadah_sampel_other',
            'tb_sample_penerimaan.pengawet',
            'tb_sample_penerimaan.pengawet_other',
            'tb_sample_penerimaan.volume',
            'tb_sample_penerimaan.unit_id',
            'tb_sample_penerimaan.kondisi_sample',
            'tb_sample_penerimaan.validation_sample',
            'tb_sample_penerimaan.penerimaan_sample_date',
            'id_laboratorium',
            'nama_laboratorium',
            'date_sending',
            'date_analitik_sample',
            'name_sample_type',
            'codesample_samples',
            'id_samples',
            'tb_samples.group_id',
            'tb_samples.created_at',
            'tb_samples.titik_pengambilan',
            'tb_samples.jenis_sarana_names',
          ],
          Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')
            ? ['tb_samples.is_nomor_sampel_manual']
            : []
        ))
        ->distinct('id_laboratorium')
        ->orderBy(  'codesample_samples')
        ->latest()
        ->get();

      // Create mapping from group_id to group number (Grup 1, Grup 2, etc.)
      $groupMapping = [];
      $groupNumber = 1;
      $uniqueGroupIds = $datas->pluck('group_id')->filter()->unique()->values();

      foreach ($uniqueGroupIds as $group_id) {
        $groupSamplesCount = Sample::where('group_id', $group_id)->where('permohonan_uji_id', $id)->count();
        if ($groupSamplesCount > 1) {
          $groupMapping[$group_id] = $groupNumber;
          $groupNumber++;
        }
      }

      return Datatables::of($datas)
        ->filter(function ($instance) use ($request) {
          if (!empty($request->get('search'))) {
            $instance->collection = $instance->collection->filter(function ($row) use ($request) {
              if (Str::contains(Str::lower($row['nama_laboratorium']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['last_status']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['name_sample_type']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['titik_pengambilan'] ?? ''), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['jenis_sarana_names'] ?? ''), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['codesample_samples']), Str::lower($request->get('search')))) {
                return true;
              } else if (Str::contains(Str::lower($row['last_status']), Str::lower($request->get('search')))) {
                return true;
              }

              return false;
            });
          }
        })
        ->addColumn('jenis_makanan', function ($data) {
          return isset($data->name_jenis_makanan) ? $data->name_jenis_makanan : '';
        })
        ->editColumn('name_sample_type', function ($data) {
          $jenisSarana = trim((string) ($data->jenis_sarana_names ?? ''));
          if ($jenisSarana === '') {
            $jenisSarana = trim((string) ($data->name_sample_type ?? ''));
          }

          $titik = trim(strip_tags((string) ($data->titik_pengambilan ?? '')));
          if ($titik === '') {
            return e($jenisSarana);
          }

          return e($jenisSarana) . '<br><span class="text-muted">(' . e($titik) . ')</span>';
        })
        ->addColumn("codesample_samples", function ($data) use ($id, $groupMapping) {
          $sample_code = Sample::codesampleTableCellHtmlFrom($data, true);

          // Add group badge below sample code if it's part of a group
          $group_id = $data->group_id ?? null;
          $group_badge = '';
          if ($group_id && isset($groupMapping[$group_id])) {
            $groupNumber = $groupMapping[$group_id];
            $group_badge = '<br><span class="badge badge-info badge-pill mt-1" title="Group ID: ' . substr($group_id, 0, 8) . '...">
                        <i class="fas fa-layer-group mr-1"></i>Grup ' . $groupNumber . '
                      </span>';
          }

          return $sample_code . $group_badge;
        })
        ->addColumn("last_status", function ($data) {
          $verificationActivities = VerificationActivitySample::query()->where('id_sample', '=', $data->id_samples)->join('ms_verification_activities', 'tb_verification_activity_samples.id_verification_activity', '=', 'ms_verification_activities.id')->get();
          $label_status = '';
          $step = [];


          if ($verificationActivities->isNotEmpty()) {
            foreach ($verificationActivities as $verificationActivity) {
              $step[$verificationActivity->id_verification_activity] = $verificationActivity;
            }

            if (!empty($step)) {
              // Urutan step yang benar: 1 -> 6 -> 7 -> 2 -> 3 -> 4 -> 5
              $stepOrder = [1, 6, 7, 2, 3, 4, 5];

              // Cari step terakhir yang sudah selesai berdasarkan urutan yang benar
              $lastStep = null;
              foreach ($stepOrder as $stepId) {
                if (isset($step[$stepId])) {
                  $lastStep = $stepId;
                }
              }

              // Jika ada step terakhir, tampilkan status berdasarkan step tersebut
              if ($lastStep !== null) {
                switch ($lastStep) {
                  case 1:
                    $label_status = '<label class="badge badge-primary badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                    break;
                  case 6:
                    $label_status = '<label class="badge badge-secondary badge-pill w-75">Pengambilan Sampel</label>';
                    break;
                  case 7:
                    $label_status = '<label class="badge badge-dark badge-pill w-75">Penerimaan Sampel</label>';
                    break;
                  case 2:
                    $label_status = '<label class="badge badge-warning badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                    break;
                  case 3:
                    $label_status = '<label class="badge badge-danger badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                    break;
                  case 4:
                    $label_status = '<label class="badge badge-info badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                    break;
                  case 5:
                    $label_status = '<label class="badge badge-success badge-pill w-75">' . $step[$lastStep]->name . '</label>';
                    break;
                  default:
                    break;
                }
              }
            }
          }



          return $label_status;
        })
        ->addColumn("count_method", function ($data) {

          $prefixMethod = $sampleMethod = '';

          foreach ($data->samplemethod as $mytable) {

            // if($mytable)
            if ($data->id_laboratorium == $mytable['laboratorium_id']) {
              $sampleMethod .= $prefixMethod . $mytable['method']['params_method'];
              $prefixMethod = '<br><br>';
            }
          }

          return $sampleMethod;
        })
        ->addColumn('action', function ($data) use ($id) {
          $editButton = '';
          $deleteButton = '';
          $printButton = '';
          $informConcernButton = '';
          $verifikasiButton2 = '';
          $duplicate = '<a href="#" class="dropdown-item btn-duplicate-sample" data-sample-id="' . ($data->id_samples ?? 0) . '" data-lab-id="' . ($data->id_laboratorium ?? 0) . '" data-toggle="modal" data-target="#modalDuplicateSample" title="Duplicate">Duplicate</a> ';

          // Get group_id from sample
          $sample = Sample::find($data->id_samples);
          $group_id = $sample ? $sample->group_id : null;

          // Group actions (only show if group_id exists and has multiple samples)
          $duplicateGroup = '';
          $editGroup = '';
          $deleteGroup = '';

          if ($group_id) {
            $groupSamplesCount = Sample::where('group_id', $group_id)->where('permohonan_uji_id', $id)->count();
            if ($groupSamplesCount > 1) {
              if (getAction('update')) {
                $duplicateGroup = '<a href="#" class="dropdown-item btn-duplicate-group" data-group-id="' . $group_id . '" data-toggle="modal" data-target="#modalDuplicateGroup" title="Duplicate Group">Duplicate Group</a> ';
                $editGroup = '<a href="' . route('elits-samples.edit-group', [$group_id]) . '" class="dropdown-item" title="Edit Group">Edit Group</a> ';
              }
              if (getAction('delete')) {
                $deleteGroup = '<a class="dropdown-item btn-hapus-group" href="#hapus-group" data-group-id="' . $group_id . '" data-nama="Group (' . $groupSamplesCount . ' samples)" title="Hapus Group">Hapus Group</a> ';
              }
            }
          }

          if (getAction('update') || getAction('delete')) {
            if (getAction('update')) {
              $editButton = '<a href="' . route('elits-samples.edit', [$data->id_samples]) . '" class="dropdown-item" title="Edit">Edit</a> ';
            }

            if (getSpesialAction(request()->segments(1), 'verification-sampel', '')) {
              $verifikasiButton2 = '<a href="' . route('elits-samples.verification-2', [$data->id_samples, $data->id_laboratorium]) . '" class="dropdown-item" title="Verifikasi">Verifikasi</a> ';
            }

            if (isset($data->id_laboratorium)) {
              if ($data->id_laboratorium !== 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5') {

                $printButton = '<a data-href="' . route('elits-release.printLHU', [$data->id_samples, $data->id_laboratorium]) . '" class="dropdown-item pointer" title="Print" data-toggle="modal" data-target="#signOptionModal">Print</a> ';
              }
              $informConcernButton = '<a href="' . route('elits-release.print-inform-concern', [$data->id_samples, $data->id_laboratorium]) . '" class="dropdown-item" title="Inform Concern">Print Informed Consent</a> ';
            } else {
              $printButton = '<a data-href="' . route('elits-release.printLHU', [$data->id_samples, 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5']) . '" class="dropdown-item pointer" title="Print" data-toggle="modal" data-target="#signOptionModal">Print</a> ';
            }

            if (getAction('delete')) {
              $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_samples  . '" data-nama="' . $data->codesample_samples . '" title="Hapus">Hapus</a> ';
            }
          }

          if(Auth::user()->getlevel->level == "RGSTR"){
            $verifikasiButton2 = '';
          }

          $button = '<div class="dropdown show m-1">
                              <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Aksi
                              </a>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                  ' . $printButton . '

                                  ' . $informConcernButton . '

                                  ' . $verifikasiButton2 . '

                                  ' . $editButton . '

                                  ' . $duplicate . '

                                  ' . $deleteButton . '

                                  ' . ($duplicateGroup || $editGroup || $deleteGroup ? '<div class="dropdown-divider"></div>' : '') . '

                                  ' . $duplicateGroup . '

                                  ' . $editGroup . '

                                  ' . $deleteGroup . '
                              </div>
                          </div>';

          return $button;
        })
        ->rawColumns(['jenis_makanan', 'name_sample_type', 'last_status', 'action', 'count_method', 'codesample_samples'])
        ->addIndexColumn() //increment
        ->make(true);
    }


    $pudam = Sample::where('permohonan_uji_id', '=', $id)
      ->where('kode_laboratorium', 'KIM')
      ->distinct('typesample_samples')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM
            tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND
            tb_pengesahan_hasil.deleted_at is NULL AND tb_samples.deleted_at is NULL LIMIT 1)'))
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_baku_mutu', function ($join) {
        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
          ->on('tb_baku_mutu.sampletype_id', '=', 'tb_samples.typesample_samples')
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->join('ms_unit as unit_baku_mutu', function ($join) {
        $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
          ->whereNull('unit_baku_mutu.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->where('name_sample_type', 'PUDAM SISA CHLOR + FISIKA')
      ->select('ms_sample_type.name_sample_type')
      ->get();




    $makmin = Sample::where('permohonan_uji_id', '=', $id)
      ->where('kode_laboratorium', 'KIM')
      ->distinct('typesample_samples')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_method.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM
            tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND
            tb_pengesahan_hasil.deleted_at is NULL AND tb_samples.deleted_at is NULL LIMIT 1)'))
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_baku_mutu', function ($join) {
        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
          ->on('tb_baku_mutu.sampletype_id', '=', 'tb_samples.typesample_samples')
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('tb_samples.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->join('ms_unit as unit_baku_mutu', function ($join) {
        $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
          ->whereNull('unit_baku_mutu.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->where('name_sample_type', 'Makanan/Minuman/Lainnya')
      ->select('ms_sample_type.name_sample_type')
      ->get();

    // $pudam->add($makmin);
    // dd($pudam);
    // if()
    // dd(count($pudam[0]));


    // Check if permohonan_uji exists
    if (!$permohonan_uji) {
      abort(404, 'Permohonan Uji tidak ditemukan');
    }

    return view('masterweb::module.admin.laboratorium.sample.list', compact('makmin', 'permohonan_uji', 'check_mikro', 'packet_prints', 'pudam', 'id'));
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

  public function getSamplePagination(Request $request)
  {


    $auth = Auth()->user();


    $draw = $request->get('draw');
    $start = $request->get("start");
    $rowperpage = $request->get("length"); // Rows display per page

    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');

    $columnIndex = $columnIndex_arr[0]['column']; // Column index

    if ($columnIndex != 0) {
      $columnName = $columnName_arr[$columnIndex]['data']; // Column name
      $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    } else {
      $columnName = 'created_at';
      $columnSortOrder = 'desc';
    }


    $searchValue = $search_arr['value']; // Search value

    // Total records


    if ($auth->level == "0e6da765-0f3a-4471-9e1d-6af257e60a70") {
      $totalRecords = Sample::select('count(*) as allcount')
        ->where('tb_samples.status', '=', '0')
        ->count();

      $totalRecordswithFilter = Sample::select('count(*) as allcount')
        ->where('tb_samples.status', '=', '0')
        ->where('codesample_samples', 'like', '%' . $searchValue . '%')
        ->count();

      // Fetch records
      $samples = Sample::orderBy($columnName, $columnSortOrder)
        ->where('tb_samples.codesample_samples', 'like', '%' . $searchValue . '%')
        ->where('tb_samples.status', '=', '0')
        ->select('tb_samples.*')
        ->skip($start)
        ->take($rowperpage)
        ->get();
    } else {
      $totalRecords = Sample::select('count(*) as allcount')->count();
      $totalRecordswithFilter = Sample::select('count(*) as allcount')->where('codesample_samples', 'like', '%' . $searchValue . '%')->count();

      // Fetch records
      $samples = Sample::orderBy($columnName, $columnSortOrder)
        ->where('tb_samples.codesample_samples', 'like', '%' . $searchValue . '%')
        ->select('tb_samples.*')

        ->skip($start)
        ->take($rowperpage)
        ->get();
    }


    $data_arr = array();

    $no = $start + 1;

    foreach ($samples as $samples) {
      $id_samples = $samples->id_samples ?? '';
      $user_samples = $samples->user_samples ?? '';
      $customer_samples = $samples->customer_samples;
      $customer_samples = Customer::where("id_customer", $customer_samples)->first();

      // Generate QR Code with error handling
      $codesample_samples = $samples->codesample_samples ?? '-';
      try {
        if ($samples->codesample_samples) {
          $qr = QrCode::size(100)->generate($samples->codesample_samples);
          $codesample_samples = $samples->codesample_samples . '<br><br>' . $qr;
        }
      } catch (\Exception $e) {
        // If QR generation fails, just show the code
        $codesample_samples = $samples->codesample_samples ?? '-';
      }

      // Handle date with fallback
      $datelab_samples = '-';
      if ($samples->datelab_samples) {
        try {
          $datelab_samples = Carbon::createFromFormat('Y-m-d H:i:s', $samples->datelab_samples)->format('d/m/Y');
        } catch (\Exception $e) {
          $datelab_samples = $samples->datelab_samples;
        }
      }




      if ($samples->status == '0') {
        $status =
          '<a href="/elits-deligations/' . $samples->id_samples . '">
                        <button type="button" class="btn btn-outline-warning">
                            <i class="fa fa-users" aria-hidden="true"></i>
                            Proses Delegation
                        </button>
                    </a>';
      } else if ($samples->status == '1') {
        $status =
          '<a href="/elits-samples/analys/' . $samples->id_samples . '">
                    <button type="button" class="btn btn-outline-primary">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Proses Analisa
                    </button>
                </a>';
      } else if ($samples->status == '2') {
        $status =
          '<a href="/elits-samples/analys/' . $samples->id_samples . '">
                    <button type="button" class="btn btn-outline-light">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Proses Invoice
                    </button>
                </a>';
      } else {
        $status =
          '<a href="/elits-release/printLHU/' . $samples->id_samples . '">
                    <button type="button" class="btn btn-outline-success">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Rilis Sample
                    </button>
                </a>';
      }


      $data_arr[] = array(
        "DT_RowId" => 'row_' . $id_samples, // Add unique row ID for DataTables
        "number" => $no ?? '',
        "codesample_samples" => $codesample_samples ?? '-',
        "id_samples" => $id_samples ?? '',
        "user_samples" => $user_samples ?? '',
        "customer_samples" => $customer_samples ? ($customer_samples->name_customer ?? '-') : '-',
        "status" => $status ?? '',
        "datelab_samples" => $datelab_samples ?? '-',
      );
      $no++;
    }




    $response = array(
      "draw" => intval($draw),
      "recordsTotal" => $totalRecords,
      "recordsFiltered" => $totalRecordswithFilter,
      "data" => $data_arr
    );

    return response()->json($response);

    // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));


  }

  /**
   * Get the sample types code and their corresponding descriptions.
   *
   * @return array
   */
  public function getSampleTypes(): array
  {
    $sample_types = [
      'AMB' => 'Air Minum Baktereologi',
      'ABB' => 'Air Higiene Bakteorologi',
      'AMF' => 'Air Minum Fisika',
      'AMK' => 'Air Minum Kimia',
      'ALB' => 'Air Limbah Bakteorologi',
      'ALT' => 'Alat Makan dan Usap Alat Makan',
      'AKK' => 'Alat Makan dan Usap Alat Makan'
    ];

    return $sample_types;
  }


  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  function numberToRomanRepresentation($number)
  {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
      foreach ($map as $roman => $int) {
        if ($number >= $int) {
          $number -= $int;
          $returnValue .= $roman;
          break;
        }
      }
    }
    return $returnValue;
  }

  /**
   * Get current global lab sequence number for preview
   */
  public function getCurrentSequenceNumber()
  {
    try {
      $currentNumber = GlobalLabSequence::getCurrentNumber();
      return response()->json([
        'status' => true,
        'current_number' => $currentNumber
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Gagal mendapatkan current number: ' . $e->getMessage()
      ], 400);
    }
  }

  public function create($id, $id_lab = null)
  {
    if ($id_lab == null) {

      if (Sample::where(DB::raw('YEAR(date_sending)'), '=', date('Y'))->count() == 0) {
        $count = 0;
      } else {
        $count = Sample::where(DB::raw('YEAR(date_sending)'), '=', date('Y'))->orderBy('count_id', 'DESC', 'DESC')->first()->count_id;
      }

      $packets = Packet::where('id_packet', '!=', '0')->orderBy('created_at')->get();
      $all_jenis_makanan = JenisMakanan::all();

      // Get laboratoriums ordered by name to ensure Kimia comes first, then Mikrobiologi
      $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')
        ->orderByRaw("CASE WHEN LOWER(nama_laboratorium) = 'kimia' THEN 1 WHEN LOWER(nama_laboratorium) = 'mikrobiologi' THEN 2 ELSE 3 END")
        ->get();
      $programs = Program::orderBy('created_at')->get();

      $permohonan_uji = PermohonanUji::find($id);


      $data_methods = array();
      $code_samples = array();
      $lab_keys = array();

      // Get current global sequence number for preview (without incrementing)
      $current_global = GlobalLabSequence::getCurrentNumber();

      // Get start number for initialization check
      $start_num = StartNum::join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('ms_start_number.deleted_at');
      })->where('kode_laboratorium', '!=', 'KLI')->first();

      // Initialize start number if needed
      if ($current_global == 0 && $start_num && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
        $current_global = $start_num->count_start_number ?? 0;
      }

      // Track position for sequential assignment (Kimia = 0, Mikro = 1)
      $position = 0;
      foreach ($laboratoriums as $laboratorium) {
        // Determine lab code: 01 for Kimia, 02 for Mikrobiologi
        $lab_code = strtolower($laboratorium->nama_laboratorium) === 'kimia' ? '01' : '02';

        // Use global sequence: kimia gets next number, mikro gets next+1
        $preview_number = $current_global + $position + 1;
        $code_samples[strtolower($laboratorium->nama_laboratorium)] = $this->getCodeSample($preview_number, $lab_code, '...');
        $lab_keys[strtolower($laboratorium->nama_laboratorium)] = $laboratorium->id_laboratorium;
        $position++;

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
          })
          ->get();
        foreach ($laboratoriummethods as $laboratoriummethod) {
          //    print_r($laboratoriummethod->params_method);

          // Semua sampletype_id yang punya baris baku mutu untuk method+lab ini (termasuk BM generik MM tanpa jenis_makanan_id)
          $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
            $laboratoriummethod->id_method,
            $data_method->id_lab
          );

          array_push(
            $data_methods[$i]->method,
            (object) array(
              'name_method' => $laboratoriummethod->params_method,
              'id_method' => $laboratoriummethod->id_method,
              'price_method' => $laboratoriummethod->price_total_method,
              'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
            )
          );
        }

        $i++;
      }

      $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);

      // dd($data_methods);







      $sampletypes = SampleType::orderBy('created_at')->get();

      $code_karanganyar = 'NK/' . date("Ymd", time()) . '/' . str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);

      $code_number = str_pad((int)($count + ($i + 1)), 4, '0', STR_PAD_LEFT);
      // $sample_types = $this->getSampleTypes();

      $code_type = '...';

      $code_datetime = now();
      $code_year = $code_datetime->format('Y');
      $code_month = $this->numberToRomanRepresentation($code_datetime->format('m'));

      $code = implode('/', [$code_number, $code_type, $code_month, $code_year]);



      $units = Unit::all();
      $libraries = Library::all();
      $kesmasSampleSettings = KesmasSampleNumberSettings::getSettings();
      $kesmasNextSampleNumber = (int) $current_global + 1;
      $kesmasNextLabNumber = (int) (DB::table('tb_lab_num as ln')
        ->join('tb_samples as s', function ($join) {
          $join->on('s.id_samples', '=', 'ln.sample_id')->whereNull('s.deleted_at');
        })
        ->whereNull('ln.deleted_at')
        ->whereYear('ln.created_at', (int) date('Y'))
        ->where(function ($q) {
          $q->where('s.is_nomor_laboratorium_manual', 1)
            ->orWhereNotNull('ln.lab_number');
        })
        ->max('ln.lab_number') ?? 0);
      // Prefer max lab manual bila ada; fallback ke next sample global
      $maxLabManualOnly = (int) (DB::table('tb_lab_num as ln')
        ->join('tb_samples as s', function ($join) {
          $join->on('s.id_samples', '=', 'ln.sample_id')->whereNull('s.deleted_at');
        })
        ->whereNull('ln.deleted_at')
        ->whereYear('ln.created_at', (int) date('Y'))
        ->where('s.is_nomor_laboratorium_manual', 1)
        ->max('ln.lab_number') ?? 0);
      if ($maxLabManualOnly > 0) {
        $kesmasNextLabNumber = $maxLabManualOnly + 1;
      } else {
        $kesmasNextLabNumber = $kesmasNextSampleNumber;
      }

      return view('masterweb::module.admin.laboratorium.sample.add', compact(
        'permohonan_uji',
        'id',
        'programs',
        'all_jenis_makanan',
        'units',
        'libraries',
        'data_methods',
        'packets',
        'sampletypes',
        'code',
        'code_samples',
        'lab_keys',
        'kesmasSampleSettings',
        'kesmasNextSampleNumber',
        'kesmasNextLabNumber'
      ));
    } else {
      return abort(404);
    }
    //get all menu public
  }

  public function analys($id, $id_method = null)
  {


    $auth = Auth()->user();

    $samples = Sample::where("id_samples", $id)->first();


    $customer = Customer::where("id_customer", $samples->customer_samples)->first();


    if ($auth->level == "3382abf2-8518-42f9-91e1-096f25da8ae8") {
      $method = SampleMethod::join('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
        ->join('tb_delegation', function ($join) {
          $join->on('tb_delegation.id_method', '=', 'ms_samples_method.id_method');
          $join->on('tb_delegation.id_samples', '=', 'ms_samples_method.id_samples');
        })
        ->where("ms_samples_method.id_samples", $id)
        ->where('tb_delegation.id_delegation', '=', $auth->id)
        ->where('ms_method.deleted_at', '=', null)
        ->where('tb_delegation.deleted_at', '=', null)
        ->select('ms_samples_method.*', 'ms_method.*', 'tb_delegation.*')
        ->get();
    } else {
      $method = SampleMethod::where("id_samples", $id)
        ->leftJoin('ms_method', 'ms_method.id_method', '=', 'ms_samples_method.id_method')
        ->where('ms_method.deleted_at', '=', null)
        ->get();
    }



    return view('masterweb::module.admin.laboratorium.sample.analys', compact('samples', 'method', 'customer', 'id_method'));
  }

  public function verification($id, $idlab = null)
  {
    if ($idlab == null) {
      abort(404);
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
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples  AND tb_sample_penanganan.laboratorium_id ="' . $idlab . '" AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pelaporan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            // $join->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
            ->whereNull('tb_pelaporan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengetikan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pengetikan_hasil.id_pengetikan_hasil', '=', DB::raw('(SELECT id_pengetikan_hasil FROM tb_pengetikan_hasil WHERE tb_pengetikan_hasil.sample_id = tb_samples.id_samples AND tb_pengetikan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pengetikan_hasil.deleted_at  is NULL AND  tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->on('tb_samples.id_samples', '=', 'tb_pengetikan_hasil.sample_id')
            ->whereNull('tb_pengetikan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_verifikasi_hasil', function ($join) use ($idlab) {
          $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.laboratorium_id ="' . $idlab . '" AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_verifikasi_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengesahan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->first();


      $year_now = Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->format('Y');


      $first_year = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
        // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
        ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now)
        ->limit(1)
        ->orderBy('tb_samples.created_at', 'asc')
        ->first();

      if ((int)$sample->count_id == (int) $first_year->count_id) {
        $previewssample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now - 1)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'desc')
          ->first();
      } else {
        $previewssample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          ->where('tb_samples.count_id', '<', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', $year_now)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'desc')
          ->first();
      }


      $last_year = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
        // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
        ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now)
        ->limit(1)
        ->orderBy('tb_samples.created_at', 'desc')
        ->first();




      // dd($previewssample->date_sending);
      // dd(Carbon::createFromFormat('Y-m-d H:i:s', $previewssample->date_sending)->format('Y'));
      if ((int)$sample->count_id == (int)$last_year->count_id) {

        $nextsample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now + 1)
          // ->where('tb_samples.count_id', '>', (int)$sample->count_id)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'asc')
          ->first();
      } else {
        $nextsample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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

          ->where('tb_samples.count_id', '>', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', $year_now)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'asc')
          ->first();
      }



      // dd($previewssample);

      // dd($sample);

      $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
        ->where('sample_id', '=', $id)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->get();

      $laboratoriumprogress = LaboratoriumProgress::where('laboratorium_id', $idlab)->orderBy('order_sort', 'asc')->get();

      $laboratoriumsampleanalitikprogress = SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id', $idlab)
        ->where('tb_sample_analitik_progress.sample_id', $id)
        ->orderBy('tb_laboratorium_progress.order_sort', 'asc')
        ->join('tb_laboratorium_progress', function ($join) {
          $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
            ->on('tb_laboratorium_progress.laboratorium_id', '=', 'tb_sample_analitik_progress.laboratorium_id')
            ->whereNull('tb_laboratorium_progress.deleted_at')
            ->whereNull('tb_sample_analitik_progress.deleted_at');
        })
        ->get();

      // dd($laboratoriumsampleanalitikprogress);

      // dd($laboratoriumsampleanalitikprogress);
      $penerimaan_sample = PenerimaanSample::where('laboratorium_id', $idlab)->first();
      // dd($sample);

      $lab_num = LabNum::where('sample_id', $sample->id_samples)
        ->where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->where('sample_type_id', $sample->typesample_samples)
        ->first();


      return view('masterweb::module.admin.laboratorium.sample.verification', compact(
        'sample',
        'previewssample',
        'nextsample',
        'laboratoriummethods',
        'laboratoriumprogress',
        'penerimaan_sample',
        'laboratoriumsampleanalitikprogress',
        'lab_num'
      ));
    }
  }
  public function verification2($id, $idlab = null)
  {
    if ($idlab == null) {
      abort(404);
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
        ->leftjoin('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples  AND tb_sample_penanganan.laboratorium_id ="' . $idlab . '" AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pelaporan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_pelaporan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengetikan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pengetikan_hasil.id_pengetikan_hasil', '=', DB::raw('(SELECT id_pengetikan_hasil FROM tb_pengetikan_hasil WHERE tb_pengetikan_hasil.sample_id = tb_samples.id_samples AND tb_pengetikan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pengetikan_hasil.deleted_at  is NULL AND  tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_pengetikan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_verifikasi_hasil', function ($join) use ($idlab) {
          $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.laboratorium_id ="' . $idlab . '" AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_verifikasi_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_pengesahan_hasil', function ($join) use ($idlab) {
          $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.laboratorium_id ="' . $idlab . '" AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'tb_pengesahan_hasil.*', 'tb_pengesahan_hasil.*', 'tb_verifikasi_hasil.*', 'tb_pengetikan_hasil.*', 'tb_pelaporan_hasil.*', 'tb_sample_penanganan.*', 'tb_sample_penerimaan.*', 'ms_sample_type.name_sample_type', 'ms_sample_type.code_sample_type', 'ms_sample_type.typesample_type', 'ms_laboratorium.*')
        ->first();

      // dd($sample);


      $year_now = Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->format('Y');


      $first_year = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
        // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
        ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now)
        ->limit(1)
        ->orderBy('tb_samples.created_at', 'asc')
        ->first();

      if ((int)$sample->count_id == (int) $first_year->count_id) {
        $previewssample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now - 1)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'desc')
          ->first();
      } else {
        $previewssample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          ->where('tb_samples.count_id', '<', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', $year_now)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'desc')
          ->first();
      }


      $last_year = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
        // ->where('tb_samples.count_id', '<', (int)$sample->count_id)
        ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now)
        ->limit(1)
        ->orderBy('tb_samples.created_at', 'desc')
        ->first();




      // dd($previewssample->date_sending);
      // dd(Carbon::createFromFormat('Y-m-d H:i:s', $previewssample->date_sending)->format('Y'));
      if ((int)$sample->count_id == (int)$last_year->count_id) {

        $nextsample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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
          ->where(DB::raw('YEAR(date_sending)'), '=', (int)$year_now + 1)
          // ->where('tb_samples.count_id', '>', (int)$sample->count_id)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'asc')
          ->first();
      } else {
        $nextsample = Sample::where('ms_laboratorium.id_laboratorium', '=', $idlab)
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

          ->where('tb_samples.count_id', '>', (int)$sample->count_id)
          ->where(DB::raw('YEAR(date_sending)'), '=', $year_now)
          ->limit(1)
          ->orderBy('tb_samples.created_at', 'asc')
          ->first();
      }



      // dd($previewssample);

      // dd($sample);

      $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
        ->where('sample_id', '=', $id)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->get();

      $laboratoriumprogress = LaboratoriumProgress::where('laboratorium_id', $idlab)->orderBy('order_sort', 'asc')->get();

      $laboratoriumsampleanalitikprogress = SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id', $idlab)
        ->where('tb_sample_analitik_progress.sample_id', $id)
        ->orderBy('tb_laboratorium_progress.order_sort', 'asc')
        ->join('tb_laboratorium_progress', function ($join) {
          $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
            ->on('tb_laboratorium_progress.laboratorium_id', '=', 'tb_sample_analitik_progress.laboratorium_id')
            ->whereNull('tb_laboratorium_progress.deleted_at')
            ->whereNull('tb_sample_analitik_progress.deleted_at');
        })
        ->get();

      // dd($laboratoriumsampleanalitikprogress);

      // dd($laboratoriumsampleanalitikprogress);
      $penerimaan_sample = PenerimaanSample::where('laboratorium_id', $idlab)
        ->where('sample_id', $id)
        ->first();

      // Ambil data analis dan koordinator kesmas dari penerimaan sample untuk default value
      $default_analis = null;
      $default_koordinator_kesmas = null;
      if ($penerimaan_sample) {
        $default_analis = $penerimaan_sample->disposisi_analis;
        $default_koordinator_kesmas = $penerimaan_sample->disposisi_koordinator_kesmas;
      }

      // dd($sample);

      $lab_num = LabNum::where('sample_id', $sample->id_samples)
        ->where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->where('sample_type_id', $sample->typesample_samples)
        ->first();


      $permohonanUji = PermohonanUji::query()->where('id_permohonan_uji', '=', $sample->permohonan_uji_id)->first();

      $verificationActivitySamples = VerificationActivitySample::query()
                                      ->where('id_sample', '=', $sample->id_samples)
                                      ->get();
      $listVerifications = [];
      foreach ($verificationActivitySamples as  $verificationActivitySample) {
        $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
      }

      $verificationActivity = VerificationActivity::all();
      // dd(  $verificationActivity);


      $labAnalitikProgres = $laboratoriumprogress->firstWhere('link', 'baca-hasil');
      if (!$labAnalitikProgres) {
        $labAnalitikProgres = LaboratoriumProgress::where('laboratorium_id', $idlab)
          ->where('link', 'baca-hasil')
          ->whereNull('deleted_at')
          ->orderBy('order_sort', 'asc')
          ->first();
      }

      $verifikasiHasil = VerifikasiHasil::query()->where('sample_id', '=', $sample->id_samples)->first();
      $pengesahanHasil = PengesahanHasil::query()->where('sample_id', '=', $sample->id_samples)->first();

      return view('masterweb::module.admin.laboratorium.sample.verification-2', compact(
        'sample',
        'previewssample',
        'nextsample',
        'verificationActivity',
        'laboratoriummethods',
        'laboratoriumprogress',
        'penerimaan_sample',
        'laboratoriumsampleanalitikprogress',
        'lab_num',
        'permohonanUji',
        'listVerifications',
        'labAnalitikProgres',
        'verifikasiHasil',
        'pengesahanHasil',
        'default_analis',
        'default_koordinator_kesmas',
      ));
    }
  }

  /**
   * Fungsi untuk mengecek nik atau password petugas di verifikasi
   * return string true jika nik dan password tidak null atau string kosong
   *  **/
  public function checkNikAndPassword($namaPetugas): string
  {
    // Jika BSRE tidak digunakan, selalu lolos
    if (!config('app.bsre_use', false)) {
      return "true";
    }
    $petugas = Petugas::query()->where('nama', '=', $namaPetugas)->first();

    if (isset($petugas)){
      if ($petugas->nik != null and $petugas->nik != "" and $petugas->password != null and $petugas->password != ""){
        return "true";
      }
    }

    return "false";
  }

  /**
   * fungsi untuk menyimpan nik dan password petugas di tabel ms_petugas
   */
  public function saveNikAndPassword(Request $request, $namaPetugas)
  {
    // Mulai: Tidak menyimpan NIK dan password ke database
    $request->validate([
      'nik' => 'required',
      'password' => 'required'
    ]);
    // Simpan sementara di session saja (sekali pakai)
    $request->session()->put('bsre_nik', trim($request->input('nik')));
    $request->session()->put('bsre_password', $request->input('password'));
    $request->session()->save();
    return "true";
  }


  public function verificationAnalytic(Request $request, $id_sample)
  {
   $request->validate([
      'verification_step' => 'required',
      'start_date' => 'required',
      'stop_date' => 'required',
      'nama_petugas' => 'required'
    ]);




    // Helper function to parse date from multiple formats (tanggal+jam atau tanggal saja)
    // Tanggal tanpa jam → jam diset ke waktu sekarang
    $parseDate = function($dateString) {
      $parsed = \Smt\Masterweb\Helpers\DateHelper::parseStageDate($dateString);
      if (!$parsed) {
        throw new \InvalidArgumentException('Format tanggal tidak valid: ' . $dateString);
      }
      return $parsed;
    };

    // updated if exist
    $verificationActivitySampleUpdated = VerificationActivitySample::query()->where('id_sample', '=', $id_sample)->where('id_verification_activity', '=', $request->get('verification_step'))->first();

    // dd($verificationActivitySampleUpdated);
    if (isset($verificationActivitySampleUpdated)) {
      $start_date = $parseDate($request->get('start_date'))->format('Y-m-d H:i:s');
      $stop_date = $parseDate($request->get('stop_date'))->format('Y-m-d H:i:s');
      $verificationActivitySampleUpdated->start_date =  $start_date;
      $verificationActivitySampleUpdated->stop_date =  $stop_date;
      $verificationActivitySampleUpdated->nama_petugas = $request->get('nama_petugas');
      $verificationActivitySampleUpdated->is_done = 1;
      $verificationActivitySampleUpdated->save();

      // Jika verification_step = 2 (Pemeriksaan / Analitik), redirect ke halaman baca hasil
      if ($request->get('verification_step') == 2) {
        // Ambil id_laboratorium dari request (hidden input) atau dari sample method
        $id_laboratorium = $request->get('id_laboratorium');

        if (!$id_laboratorium) {
          // Fallback: ambil dari sample method
          $sampleMethod = SampleMethod::where('sample_id', '=', $id_sample)
            ->whereNull('deleted_at')
            ->first();

          if ($sampleMethod && $sampleMethod->laboratorium_id) {
            $id_laboratorium = $sampleMethod->laboratorium_id;
          }
        }

        if ($id_laboratorium) {
          $laboratorium_progress_id = $this->resolveBacaHasilProgressIdForRedirect($id_sample, $id_laboratorium);

          if ($laboratorium_progress_id) {
            return redirect()->route('elits-baca-hasil.index', [
              'id' => $id_sample,
              'idlabs' => $id_laboratorium,
              'idprogress' => $laboratorium_progress_id
            ])->with('status', 'Verifikasi Pemeriksaan / Analitik berhasil disimpan!');
          } else {
            // Jika laboratorium progress tidak ditemukan, tetap redirect back dengan error
            return redirect()->back()->with('error', 'Laboratorium progress tidak ditemukan untuk redirect ke halaman baca hasil. Pastikan data Laboratorium Progress sudah ada untuk laboratorium ini.');
          }
        } else {
          // Jika id_laboratorium tidak ditemukan, tetap redirect back dengan error
          return redirect()->back()->with('error', 'ID Laboratorium tidak ditemukan untuk redirect ke halaman baca hasil.');
        }
      }

      return redirect()->back();
    }

    $verificationActivitySample = new VerificationActivitySample();
    $verificationActivitySample->id = Uuid::uuid4()->toString();
    $verificationActivitySample->id_sample = $id_sample;
    $verificationActivitySample->id_verification_activity = $request->get('verification_step');


    $start_date = $parseDate($request->get('start_date'))->format('Y-m-d H:i:s');
    $stop_date = $parseDate($request->get('stop_date'))->format('Y-m-d H:i:s');

    // dd( $start_date);
    $verificationActivitySample->is_done = 1;
    $verificationActivitySample->start_date =  $start_date;
    $verificationActivitySample->stop_date = $stop_date;
    $verificationActivitySample->nama_petugas = $request->get('nama_petugas');

    $verificationActivitySample->save();

      // Jika verification_step = 3 (Input / Output Hasil Px), redirect kembali ke halaman baca hasil
      if ($request->get('verification_step') == 3) {
        // Ambil parameter dari request untuk redirect kembali
        $id_laboratorium = $request->get('id_laboratorium');
        $laboratorium_progress_id = $request->get('laboratorium_progress_id');

        if ($id_laboratorium && $laboratorium_progress_id) {
          return redirect()->route('elits-baca-hasil.index', [
            'id' => $id_sample,
            'idlabs' => $id_laboratorium,
            'idprogress' => $laboratorium_progress_id
          ])->with('status', 'Verifikasi Baca Hasil berhasil disimpan!');
        } else {
          // Fallback: redirect back dengan pesan sukses
          return redirect()->back()->with('status', 'Verifikasi Baca Hasil berhasil disimpan!');
        }
      }

      // Jika verification_step = 2 (Pemeriksaan / Analitik), redirect ke halaman baca hasil
      if ($request->get('verification_step') == 2) {
        // Ambil id_laboratorium dari request (hidden input) atau dari sample method
        $id_laboratorium = $request->get('id_laboratorium');

        if (!$id_laboratorium) {
          // Fallback: ambil dari sample method
          $sampleMethod = SampleMethod::where('sample_id', '=', $id_sample)
            ->whereNull('deleted_at')
            ->first();

          if ($sampleMethod && $sampleMethod->laboratorium_id) {
            $id_laboratorium = $sampleMethod->laboratorium_id;
          }
        }

        if ($id_laboratorium) {
          $laboratorium_progress_id = $this->resolveBacaHasilProgressIdForRedirect($id_sample, $id_laboratorium);

          if ($laboratorium_progress_id) {
            return redirect()->route('elits-baca-hasil.index', [
              'id' => $id_sample,
              'idlabs' => $id_laboratorium,
              'idprogress' => $laboratorium_progress_id
            ])->with('status', 'Verifikasi Pemeriksaan / Analitik berhasil disimpan!');
          } else {
            // Jika laboratorium progress tidak ditemukan, tetap redirect back dengan error
            return redirect()->back()->with('error', 'Laboratorium progress tidak ditemukan untuk redirect ke halaman baca hasil. Pastikan data Laboratorium Progress sudah ada untuk laboratorium ini.');
          }
        } else {
          // Jika id_laboratorium tidak ditemukan, tetap redirect back dengan error
          return redirect()->back()->with('error', 'ID Laboratorium tidak ditemukan untuk redirect ke halaman baca hasil.');
        }
      }

    return redirect()->back();
  }

  public function getIdSample($idSampleType)
  {
    $user = Auth()->user();
    $level = $user->getlevel;

    if ($level->level == "elits-dev" || $level->level == "LAB") {

      $samplecount = Sample::join('ms_sample_type', function ($join) use ($idSampleType) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')


          ->where('ms_sample_type.typesample_type', '=', $idSampleType);
      })->max('tb_samples.codenumber_samples');
      $samplecount = $samplecount + 1;
    } else {
      return response()->json([
        'success' => 'false',
        'errors'  => "Cannot Access",
      ], 400);
    }

    return response()->json(array('success' => true, 'samplecount' =>  $samplecount));
  }

  public function rules($request)
  {
    $rule = [
      'jenis_sampel' => 'required',
      // 'program_samples' => 'required',
      // 'wadah' => 'required',
      // 'pengawet' => 'required',
      // 'volume' => 'required',
      // 'unit' => 'required',
      // 'kondisi_sample' => 'required',
      // 'validation_sample' => 'required',
      // 'kelayakan_tempat_kemasan' => 'required',
      // 'kelayakan_berat_vol' => 'required',
    ];

    $pesan = [
      'jenis_sampel.required' => 'Jenis sample tidak boleh kosong!',
      // 'wadah.required' => 'Wadah tidak boleh kosong!',
      // 'pengawet.required' => 'Pengawet tidak boleh kosong!',
      // 'volume.required' => 'Volume tidak boleh kosong!',
      // 'unit.required' => 'Unit tidak boleh kosong!',
      // 'kondisi_sample.required' => 'Kondisi sample tidak boleh kosong!',
      // 'validation_sample.required' => 'Validasi sample tidak boleh kosong!',
      // 'kelayakan_tempat_kemasan.required' => 'Validasi tempat/kemasan tidak boleh kosong!',
      // 'kelayakan_berat_vol.required' => 'Kelayakan berat/volume sample tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }


  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $id)
  {
    // Check if this is a multiple samples request (with array 'samples')
    // Handle both JSON string and array format
    $samples_data = null;
    if ($request->has('samples')) {
      if (is_string($request->samples)) {
        $samples_data = json_decode($request->samples, true);
      } else if (is_array($request->samples)) {
        $samples_data = $request->samples;
      }
    }

    if (!empty($samples_data) && is_array($samples_data) && count($samples_data) > 0) {
      // Handle multiple samples with group_id (similar to sample-draft)
      return $this->storeMultipleSamples($request, $id);
    }

    // Original single sample logic (backward compatible)
    $validator = $this->rules($request->all());
    $is_success = true;


    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    }

    $methodStringsForLabCheck = isset($request->method) && is_array($request->method) ? $request->method : [];
    $usedLabsSingle = $this->labsUsedFromMethodStrings($methodStringsForLabCheck);
    try {
      $this->assertKesmasManualSampleCodesFilled($request, $usedLabsSingle);
    } catch (\InvalidArgumentException $e) {
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
    }

    DB::beginTransaction();

      // Generate group_id for single sample (will be same for itself)
      $group_id = Uuid::uuid4()->toString();



      $codeKimiaMikro = Uuid::uuid4();

      $paketAirHigiene = 'a0067d2a-6193-4225-9210-be6569d88a6c';
      $paketAirMinum = '04d4e517-73c0-4fab-809c-5ba0bac730d7';

      // try {
      $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

      # Code Samples
      $code_sample_kimia = $request->get('code_sample_kimia');
      $code_sample_mikro = $request->get('code_sample_mikro');

      $code_samples = [
        'kimia' => $code_sample_kimia,
        'mikro' => $code_sample_mikro
      ];

      # Parse Parameters.
      $data = $request->all();
      $array_method = [];
      $lab = [];



      if (isset($data["method"])) {
        for ($i = 0; $i < count($data["method"]); $i++) {
          $method = [];
          $methodlab = explode("_", $data["method"][$i]);
          $method["method"] = $methodlab[0];
          $method["lab"] = $methodlab[1];
          $method["price_method"] = $methodlab[2];

          array_push($array_method, $method);
          array_push($lab, $methodlab[1]);
        }
      }

      usort($array_method, function ($a, $b) {
        return strcmp($a["lab"], $b["lab"]);
      });
      $lab = array_unique($lab);
      sort($lab);

      // Check if lab array is empty
      if (empty($lab) || count($lab) === 0) {
        return response()->json([
          'status' => false,
          'pesan' => 'Tidak ada parameter pengujian yang dipilih!'
        ], 400);
      }

      $laboratoriumId= $lab[0];



      # Remove Unused Lab by Params Lab Id.
      if (count($lab) < 2) {
        $current_lab = head($lab);
        $current_laboratorium = $laboratoriums->where('id_laboratorium', $current_lab)->first();
        $current_lab_name = strtolower($current_laboratorium->nama_laboratorium);

        if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';

        // Example: ["kimia" => uuid4, "mikro" => uuid4]


        foreach ($code_samples as $loop_lab_name => $loop_lab_key) {
          // dd($code_samples[$loop_lab_name]);
          if ($loop_lab_name != $current_lab_name) {
            unset($code_samples[$loop_lab_name]);
          }
        }
      }


      $created_at = Carbon::now()->format('Y-m-d H:i:s');



      do {
        # Initialization
        $current_lab = head($lab);

        $current_laboratorium = $laboratoriums->where('id_laboratorium', $current_lab)->first();
        $current_lab_name = strtolower($current_laboratorium->nama_laboratorium);
        // dd($current_lab_name);
        if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';
        $current_sample_code = $code_samples[$current_lab_name];

        // Parse urutan from code sample format: AB.01/0003/2025
        $parts = explode("/", $current_sample_code);
        $current_sample_urutan = isset($parts[1]) ? (int) $parts[1] : 0;


        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();


        // DEBUG: Check parsing

        $sample = new Sample;
        $sample->permohonan_uji_id = $id;

        $sample->created_at = $created_at;

        if ($request->post('jenis_sample_uji_usap') !== null){
          $sample->jenis_sample_uji_usap = $request->post('jenis_sample_uji_usap');
        }


        $sample->codesample_samples = $current_sample_code;

        $sample->name_pelanggan = $request->post('name_pelanggan');

        $sample->is_pudam  = $request->post('is_pudam')?1:0;
        $sample->name_customer_pdam = $request->post('name_customer_pdam');
        $sample->address_location_pdam  = $request->post('address_location_pdam');

        $packet = $request->post('packet');


        if (isset($packet)) {
          # code...

          if (count($packet)>1 ) {
            $is_lab=false;
            # code...

            if( $current_laboratorium->kode_laboratorium=="KIM"){
              $id_packet="";
              $cost=0;
              foreach ($packet as $key => $item_packet) {

                if ($item_packet == $paketAirMinum or $item_packet == $paketAirHigiene){
                  $sample->codeKimiaMikro = $codeKimiaMikro;
                }
                # code...
                $packet_obj = Packet::find($item_packet);

                if (  str_contains($packet_obj->name_packet,'Fisika') || str_contains($packet_obj->name_packet,'Kimia')) {
                  # code...
                  $is_lab=true;
                  $id_packet=$item_packet;
                  $cost=$packet_obj->price_total_packet;
                }else{
                  foreach ($packet_obj->packet_detail as $packet_detail){
                    foreach ($packet_detail->method->laboratorium_method as $laboratorium_method){


                      if( $current_laboratorium->id_laboratorium==$laboratorium_method->laboratorium_id){
                        $is_lab=true;
                        $id_packet=$item_packet;
                        $cost=$packet_obj->price_total_packet;
                      }
                      // else{
                      //   dd($current_laboratorium);
                      // }
                    }
                  }
                }
              }


              if ( $is_lab) {
                # code...
                $sample->packet_id = $id_packet;
                $sample->cost_samples =  $cost;
              }else{
                $count=0;
                foreach ($array_method as $method) {


                  if ($method["lab"] == $current_laboratorium->id_laboratorium) {

                    $count= $count+$method["price_method"];
                  }
                }
                $sample->cost_samples =$count;
              }


            }else if( $current_laboratorium->kode_laboratorium=="MBI"){
              $id_packet="";
              $cost=0;
              foreach ($packet as $key => $item_packet) {
                # code...

                if ($item_packet == $paketAirMinum or $item_packet == $paketAirHigiene){
                  $sample->codeKimiaMikro = $codeKimiaMikro;
                }

                $packet_obj = Packet::find($item_packet);

                if (  str_contains($packet_obj->name_packet,'Bakteriologis')) {
                  # code...
                  $is_lab=true;
                  $id_packet=$item_packet;
                  $cost=$packet_obj->price_total_packet;
                }else{
                  foreach ($packet_obj->packet_detail as $packet_detail){
                    foreach ($packet_detail->method->laboratorium_method as $laboratorium_method){


                      if( $current_laboratorium->id_laboratorium==$laboratorium_method->laboratorium_id){
                        $is_lab=true;
                        $id_packet=$item_packet;
                        $cost=$packet_obj->price_total_packet;
                      }
                      // else{
                      //   dd($current_laboratorium);
                      // }
                    }
                  }
                }
              }


              if ( $is_lab) {
                # code...
                $sample->packet_id = $id_packet;
                $sample->cost_samples =  $cost;
              }else{
                $count=0;
                foreach ($array_method as $method) {


                  if ($method["lab"] == $current_laboratorium->id_laboratorium) {

                    $count= $count+$method["price_method"];
                  }
                }
                $sample->cost_samples =$count;
              }
            }


          }else if (count($packet)==1 ) {

              $sample->packet_id = $packet[0];
              if ($packet[0] == $paketAirMinum or $packet[0] == $paketAirHigiene){
                $sample->codeKimiaMikro = $codeKimiaMikro;
              }
              $sample->cost_samples = $request->post('cost_samples');

              // $packet = $request->post('packet');



              // foreach ($packet as $packet_id){
              $is_lab=false;
              $sample->packet_id = null;
                $packet_model = Packet::find($packet[0]);
                // dd($packet_model->packet_detail);
                foreach ($packet_model->packet_detail as $packet_detail){
                  foreach ($packet_detail->method->laboratorium_method as $laboratorium_method){


                    if( $current_laboratorium->id_laboratorium==$laboratorium_method->laboratorium_id){
                      $is_lab=true;
                    }
                    // else{
                    //   dd($current_laboratorium);
                    // }
                  }
                }


                if ( $is_lab) {
                  # code...
                  $sample->packet_id = $packet[0];
                  $sample->cost_samples =$packet_model->price_total_packet;
                }else{
                  $count=0;
                  foreach ($array_method as $method) {


                    if ($method["lab"] == $current_laboratorium->id_laboratorium) {

                      $count= $count+$method["price_method"];
                    }
                  }
                  $sample->cost_samples =$count;
                }


              // }


          }else{
            $sample->cost_samples = $request->post('cost_samples');
          }
        }else{
          $sample->cost_samples = $request->post('cost_samples');
        }





        # Jenis Sampel
        $sample->typesample_samples = $request->post('jenis_sampel');
        if (isset($request->gender_samples)) {
          $sample->gender_samples = $request->post('gender_samples');
        }

        # Umur
        if (isset($request->umur_samples)) {
          $sample->umur_samples = $request->post('umur_samples');
        }

        # Jenis Makanan
        $jenis_makanan_id = $request->post('jenis_makanan_id');
        if (isset($jenis_makanan_id)) {
          $sample->jenis_makanan_id = $request->post('jenis_makanan_id');
        }

        $jenis_makanan =$request->post('jenis_makanan_minuman');
        // if ($request->post('jenis_sampel') == "d34b4a50-4560-4fce-96c3-046c7080a986"){
          if (isset($jenis_makanan) ){
            $sample->nama_jenis_makanan = $request->post('jenis_makanan_minuman');
          }
        // }

        # Sample Urutan
        // if (Sample::where(DB::raw('YEAR(date_sending)'), '=', date('Y'))->count() == 0) {
        //   $sample_urutan = 0;
        // } else {
        //   $sample_urutan = Sample::where(DB::raw('YEAR(date_sending)'), '=', date('Y'))->orderBy('count_id', 'DESC')->first()->count_id;
        // }

        $sample->name_send_sample = $request->post('name_send_sample');
        $sample->code_sample_customer = $request->post('code_sample_customer');
        $sample->count_id =  $current_sample_urutan;
        $sample->program_samples = $request->post('program_samples');


        # Jenis Sarana
        if (isset($request->jenis_sarana)) {
          $sample->jenis_sarana_names = $request->jenis_sarana;
        }

        $datesampling_samples = Carbon::createFromFormat('d/m/Y H:i', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
        $date_sending = Carbon::createFromFormat('d/m/Y H:i', $request->post('date_sending'))->format('Y-m-d H:i:s');
        $sample->datesampling_samples = $datesampling_samples;
        $sample->date_sending = $date_sending;
        $sample->note_samples = $request->post('note');
        $sample->titik_pengambilan = $request->post('titik_pengambilan');
        $sample->group_id = $group_id; // Add group_id for single sample
        $permohonan_uji_parent = PermohonanUji::find($id);
        $sample->is_sampling = $request->has('is_sampling') && $request->is_sampling !== null
          ? (int) $request->is_sampling
          : (int) ($permohonan_uji_parent->is_sampling ?? 1);
        $simpan_sample = $sample->save();



        # Lab Num
        if (isset($data["method"])) {
          foreach ($lab as $lab_key) {
            if ($current_lab == $lab_key) {
              # code...


                $start_num= StartNum::join('ms_laboratorium', function ($join) {
                  $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                    ->whereNull('ms_laboratorium.deleted_at')
                    ->whereNull('ms_start_number.deleted_at');
                })->where('id_laboratorium',$lab_key)->first();

                $sample->typesample_samples = $request->post('jenis_sampel');

                $sample_type=SampleType::find($sample->typesample_samples);
                if (str_contains($sample_type->name_sample_type,"Makanan/Minuman/Lainnya")) {

                  $start_num= StartNum::where('code_lab_start_number','MAK-MIN')->first();

                  $_lab_row = $laboratoriums->where('id_laboratorium', $lab_key)->first();
                  $_lab_short = strtolower($_lab_row->nama_laboratorium ?? '');
                  if ($_lab_short == 'mikrobiologi') {
                    $_lab_short = 'mikro';
                  }

                  $ksSettings = KesmasSampleNumberSettings::getSettings();
                  $manualLabNum = $this->getKesmasManualLabNumFromRequest($request, $_lab_short);

                  if ($manualLabNum === null) {
                    # Get next number from global sequence
                    $current_global = GlobalLabSequence::getCurrentNumber();

                    // Handle start number for first year
                    if ($current_global == 0 && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
                      // Initialize with start number
                      $sequence = GlobalLabSequence::where('year', date('Y'))->first();
                      if (!$sequence) {
                        $sequence = GlobalLabSequence::create([
                          'year' => date('Y'),
                          'last_number' => ($start_num->count_start_number ?? 0),
                        ]);
                      } else {
                        $sequence->last_number = ($start_num->count_start_number ?? 0);
                        $sequence->save();
                      }
                    }

                    // Get next number and record lab_id
                    $lab_num_urutan = GlobalLabSequence::getNextNumber(null, $lab_key, 'lab', null);
                  } else {
                    $lab_num_urutan = $manualLabNum;
                  }

                  $lab_num = new LabNum;
                  $lab_num->sample_id = $sample->id_samples;
                  $lab_num->sample_type_id = $sample->typesample_samples;
                  $lab_num->lab_id = $lab_key;
                  $lab_num->is_makanan = 1;
                  $lab_num->mount_lab_num = Carbon::now()->format('m');
                  $lab_num->year_lab_num = Carbon::now()->format('Y');
                  $lab_num->permohonan_uji_id = $sample->permohonan_uji_id;
                  $lab_num->lab_number = $lab_num_urutan;
                  $lab_num->save();

                  $manualCode = $this->getKesmasManualCodeFromRequest($request, $_lab_short);
                  if ($manualCode !== null) {
                    $stCode = optional($sample_type)->code_sample_type ?? '...';
                    $this->persistKesmasManualSampleCode($sample, $manualCode, $stCode);
                    $sample->save();
                  } elseif (!$ksSettings->is_nomor_sampel_manual) {
                    // Ensure codesample uses global sequence number
                    // Build from existing preview code by replacing the number segment
                    $preview_code_parts = explode('/', $current_sample_code);
                    if (count($preview_code_parts) >= 3) {
                      $prefix_code = $preview_code_parts[0];
                      $year_suffix = $preview_code_parts[count($preview_code_parts) - 1];
                      $global_number_str = str_pad((int)$lab_num_urutan, 4, '0', STR_PAD_LEFT);
                      $new_code_sample = $prefix_code . '/' . $global_number_str . '/' . $year_suffix;
                      $sample->codesample_samples = $new_code_sample;
                      $sample->count_id = $global_number_str;
                      $sample->save();
                    }
                  }

                  if ($manualLabNum !== null) {
                    $this->upsertNomerLabKesmasIfManual($id, $lab_key, (int) $manualLabNum);
                  }

                  $this->linkGlobalLabSequenceDetailForLabNum(
                    $lab_key,
                    (int) $lab_num_urutan,
                    $lab_num->id_lab_num,
                    $manualLabNum !== null,
                    $lab_num->year_lab_num ?? date('Y')
                  );


                }else{

                  $_lab_row = $laboratoriums->where('id_laboratorium', $lab_key)->first();
                  $_lab_short = strtolower($_lab_row->nama_laboratorium ?? '');
                  if ($_lab_short == 'mikrobiologi') {
                    $_lab_short = 'mikro';
                  }

                  $ksSettings = KesmasSampleNumberSettings::getSettings();
                  $manualLabNum = $this->getKesmasManualLabNumFromRequest($request, $_lab_short);

                  if ($manualLabNum === null) {
                    # Get next number from global sequence
                    $current_global = GlobalLabSequence::getCurrentNumber();

                    // Handle start number for first year
                    if ($current_global == 0 && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
                      // Initialize with start number
                      $sequence = GlobalLabSequence::where('year', date('Y'))->first();
                      if (!$sequence) {
                        $sequence = GlobalLabSequence::create([
                          'year' => date('Y'),
                          'last_number' => ($start_num->count_start_number ?? 0),
                        ]);
                      } else {
                        $sequence->last_number = ($start_num->count_start_number ?? 0);
                        $sequence->save();
                      }
                    }

                    // Get next number and record lab_id
                    $lab_num_urutan = GlobalLabSequence::getNextNumber(null, $lab_key, 'lab', null);
                  } else {
                    $lab_num_urutan = $manualLabNum;
                  }

                  $lab_num = new LabNum;
                  $lab_num->sample_id = $sample->id_samples;
                  $lab_num->sample_type_id = $sample->typesample_samples;
                  $lab_num->lab_id = $lab_key;
                  $lab_num->mount_lab_num = Carbon::now()->format('m');
                  $lab_num->year_lab_num = Carbon::now()->format('Y');
                  $lab_num->permohonan_uji_id = $sample->permohonan_uji_id;
                  $lab_num->lab_number = $lab_num_urutan;
                  $lab_num->save();

                  $manualCode = $this->getKesmasManualCodeFromRequest($request, $_lab_short);
                  if ($manualCode !== null) {
                    $stCode = optional($sample_type)->code_sample_type ?? '...';
                    $this->persistKesmasManualSampleCode($sample, $manualCode, $stCode);
                    $sample->save();
                  } elseif (!$ksSettings->is_nomor_sampel_manual) {
                    $preview_code_parts = explode('/', $current_sample_code);
                    if (count($preview_code_parts) >= 3) {
                      $prefix_code = $preview_code_parts[0];
                      $year_suffix = $preview_code_parts[count($preview_code_parts) - 1];
                      $global_number_str = str_pad((int)$lab_num_urutan, 4, '0', STR_PAD_LEFT);
                      $new_code_sample = $prefix_code . '/' . $global_number_str . '/' . $year_suffix;
                      $sample->codesample_samples = $new_code_sample;
                      $sample->count_id = $global_number_str;
                      $sample->save();
                    }
                  }

                  if ($manualLabNum !== null) {
                    $this->upsertNomerLabKesmasIfManual($id, $lab_key, (int) $manualLabNum);
                  }

                  $this->linkGlobalLabSequenceDetailForLabNum(
                    $lab_key,
                    (int) $lab_num_urutan,
                    $lab_num->id_lab_num,
                    $manualLabNum !== null,
                    $lab_num->year_lab_num ?? date('Y')
                  );
                }

              $sample->is_nomor_sampel_manual = isset($manualCode) && $manualCode !== null ? 1 : 0;
              $sample->is_nomor_laboratorium_manual = $manualLabNum !== null ? 1 : 0;
              $sample->save();

              foreach ($array_method as $method) {
                if ($method["lab"] == $lab_key) {
                  $saplemmethod = new SampleMethod;
                  $saplemmethod->sample_id = $sample->id_samples;
                  $saplemmethod->method_id = $method["method"];
                  $saplemmethod->price_method = (int)$method["price_method"];
                  $saplemmethod->laboratorium_id = $method["lab"];

                  $baku_mutu = BakuMutu::where('method_id', '=', $method["method"])
                    ->where('lab_id', '=', $method["lab"])
                    ->where('sampletype_id', '=', $sample->typesample_samples)->first();

                  if ($baku_mutu && $baku_mutu->is_sub == "1") {
                    $saplemmethod->is_sub = 1;
                    $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=', $method["method"])
                      ->where('sampletype_id', '=', $sample->typesample_samples)
                      ->where('baku_mutu_id', '=',  $baku_mutu->id_baku_mutu)->get();

                    foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {
                      $sample_result_detail = new SampleResultDetail;
                      // $sample_result_detail->id_sample_result_detail = Uuid::uuid4();
                      $sample_result_detail->sample_id = $sample->id_samples;
                      $sample_result_detail->method_id = $bakuMutuDetailParameterNonKlinik->method_id;
                      $sample_result_detail->sampletype_id = $bakuMutuDetailParameterNonKlinik->sampletype_id;
                      $sample_result_detail->lab_id  = $bakuMutuDetailParameterNonKlinik->lab_id;
                      $sample_result_detail->name_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik;
                      $sample_result_detail->min_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik;
                      $sample_result_detail->max_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik;
                      $sample_result_detail->equal_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik;
                      $sample_result_detail->nilai_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik;
                      $sample_result_detail->save();
                    }
                  }

                  $saplemmethod->save();
                }
              }
            }
          }


        }


        # [Update] Permohonan Uji (including sampling costs)
        $total_cost = Sample::where('permohonan_uji_id', '=', $id)->sum('cost_samples');
        $total_sampling_cost = Sample::where('permohonan_uji_id', '=', $id)
            ->where('is_sampling', 1)
            ->sum('cost_sampling_samples');
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', '=', $id)->first();
        $permohonan_uji->total_harga = $total_cost + $total_sampling_cost;
        $permohonan_uji->save();

        #sortng number



        # Penerimaan Sample
        $penerimaan_sample = new PenerimaanSample;
        $penerimaan_sample->sample_id =  $sample->id_samples;
        $penerimaan_sample->penerimaan_sample_date = $date_sending;
        $penerimaan_sample->kelayakan_tempat_kemasan  = $request->post('kelayakan_tempat_kemasan');
        $penerimaan_sample->kelayakan_berat_vol  = $request->post('kelayakan_berat_vol');
        $simpan_penerimaan_sample = $penerimaan_sample->save();

        # Verivication
        if ($simpan_sample == true && $simpan_penerimaan_sample == true) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_verification_activity = 1;
          $verificationActivitySample->id_sample = $sample->id_samples;
          $verificationActivitySample->start_date = $date_sending;
          $verificationActivitySample->stop_date = Carbon::createFromFormat('d/m/Y H:i', $request->get('date_sending_stop'))->format('Y-m-d H:i:s');
          $verificationActivitySample->nama_petugas = $permohonan_uji->petugas_penerima;
          $verificationActivitySample->is_done = true;
          $verificationActivitySample->save();
        } else {
          $is_success = false;
        }





        array_shift($code_samples);
        array_shift($lab);

        // foreach ($lab as $lab_key) {
        //    $this->sortingNumber($lab_key,null);
        // }



        // if (isset($data["method"])) {

        // }


      } while (count($code_samples) > 0 && $simpan_penerimaan_sample == true && $simpan_sample == true);

      DB::commit();

        if ($is_success) {
          return response()->json(['status' => true, 'pesan' => "Data sample berhasil disimpan!", 'url_redirect' => route('elits-samples.index', $id)], 200);
        } else {
        return response()->json(['status' => false, 'pesan' => "Data sample tidak berhasil disimpan!"], 200);
      }
      // } catch (\Exception $e) {
      //   DB::rollback();

      //   return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
      // }
  }

  /**
   * Store multiple samples with group_id (for multiple sample types in one input)
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  string  $id  permohonan_uji_id
   * @return \Illuminate\Http\Response
   */
  /**
   * Gabungkan beberapa konfigurasi sampel yang identitasnya sama
   * (jenis sampel + titik pengambilan + nomor manual kimia/mikro)
   * menjadi satu entri: metode digabung, harga paket dijumlahkan.
   * Satu paket + parameter tambahan → satu id_samples per lab.
   *
   * Mode nomor otomatis (tanpa kode manual): tidak digabung (tetap sampel terpisah).
   */
  /**
   * Resolve id progress "baca-hasil" untuk redirect setelah verification step 2.
   * Prioritas: progress master link=baca-hasil, lalu SampleAnalitikProgress yang match.
   */
  private function resolveBacaHasilProgressIdForRedirect(string $id_sample, string $id_laboratorium): ?string
  {
    $laboratoriumProgress = LaboratoriumProgress::where('laboratorium_id', $id_laboratorium)
      ->where('link', 'baca-hasil')
      ->whereNull('deleted_at')
      ->orderBy('order_sort', 'asc')
      ->first();

    $laboratorium_progress_id = $laboratoriumProgress
      ? $laboratoriumProgress->id_laboratorium_progress
      : null;

    if (!$laboratorium_progress_id) {
      return null;
    }

    $sampleAnalitikProgress = \Smt\Masterweb\Models\SampleAnalitikProgress::where('sample_id', $id_sample)
      ->where('laboratorium_id', $id_laboratorium)
      ->where('laboratorium_progress_id', $laboratorium_progress_id)
      ->whereNull('deleted_at')
      ->first();

    if (!$sampleAnalitikProgress) {
      $sampleAnalitikProgress = new \Smt\Masterweb\Models\SampleAnalitikProgress();
      $sampleAnalitikProgress->laboratorium_progress_id = $laboratorium_progress_id;
      $sampleAnalitikProgress->laboratorium_id = $id_laboratorium;
      $sampleAnalitikProgress->sample_id = $id_sample;
      $sampleAnalitikProgress->save();
    }

    return $laboratorium_progress_id;
  }

  private function mergeSampleConfigsByIdentity(array $samplesData): array
  {
    $merged = [];

    foreach ($samplesData as $index => $cfg) {
      if (!is_array($cfg)) {
        continue;
      }

      $typeId = (string) ($cfg['sample_type_id'] ?? '');
      if ($typeId === '') {
        continue;
      }

      $titik = trim((string) ($cfg['titik_pengambilan'] ?? ''));
      $codeKimia = trim((string) ($cfg['code_sample_kimia'] ?? ''));
      $codeMikro = trim((string) ($cfg['code_sample_mikro'] ?? ''));
      $codeGeneric = trim((string) ($cfg['code_sample'] ?? ''));

      $hasManualCode = ($codeKimia !== '' || $codeMikro !== '' || $codeGeneric !== '');

      // Hanya gabung jika nomor manual sama; nomor otomatis tetap terpisah per config
      if ($hasManualCode) {
        $key = implode('|', [
          $typeId,
          $titik,
          mb_strtolower($codeKimia !== '' ? $codeKimia : $codeGeneric),
          mb_strtolower($codeMikro !== '' ? $codeMikro : $codeGeneric),
        ]);
      } else {
        $key = 'auto|' . $index;
      }

      if (!isset($merged[$key])) {
        $merged[$key] = $cfg;
        $merged[$key]['methods'] = array_values(array_unique($cfg['methods'] ?? []));
        $merged[$key]['packet_price'] = (float) ($cfg['packet_price'] ?? 0);
        continue;
      }

      $existingMethods = $merged[$key]['methods'] ?? [];
      $newMethods = $cfg['methods'] ?? [];
      $merged[$key]['methods'] = array_values(array_unique(array_merge($existingMethods, $newMethods)));

      $merged[$key]['packet_price'] = (float) ($merged[$key]['packet_price'] ?? 0)
        + (float) ($cfg['packet_price'] ?? 0);

      // Prefer packet_id yang ada (paket menimpa null)
      if (empty($merged[$key]['packet_id']) && !empty($cfg['packet_id'])) {
        $merged[$key]['packet_id'] = $cfg['packet_id'];
      }

      // Lengkapi nomor manual jika salah satu kosong
      if (empty($merged[$key]['code_sample_kimia']) && !empty($cfg['code_sample_kimia'])) {
        $merged[$key]['code_sample_kimia'] = $cfg['code_sample_kimia'];
      }
      if (empty($merged[$key]['code_sample_mikro']) && !empty($cfg['code_sample_mikro'])) {
        $merged[$key]['code_sample_mikro'] = $cfg['code_sample_mikro'];
      }
      if (empty($merged[$key]['code_sample']) && !empty($cfg['code_sample'])) {
        $merged[$key]['code_sample'] = $cfg['code_sample'];
      }
      if (empty($merged[$key]['lab_num_kimia']) && !empty($cfg['lab_num_kimia'])) {
        $merged[$key]['lab_num_kimia'] = $cfg['lab_num_kimia'];
      }
      if (empty($merged[$key]['lab_num_mikro']) && !empty($cfg['lab_num_mikro'])) {
        $merged[$key]['lab_num_mikro'] = $cfg['lab_num_mikro'];
      }
    }

    return array_values($merged);
  }

  private function storeMultipleSamples(Request $request, $id)
  {
    try {
      DB::beginTransaction();

      $user = Auth()->user();
      $permohonan_uji = PermohonanUji::findOrFail($id);

      // Parse samples array from JSON string
      $samples_data = [];
      if ($request->has('samples')) {
        if (is_string($request->samples)) {
          $samples_data = json_decode($request->samples, true);
        } else {
          $samples_data = $request->samples;
        }
      }

      if (empty($samples_data) || !is_array($samples_data)) {
        return response()->json([
          'status' => false,
          'pesan' => 'Data samples tidak valid!'
        ], 400);
      }

      // Paket + parameter tambahan dengan jenis sampel (dan nomor manual) sama
      // digabung jadi satu sampel / satu pengujian per lab.
      $samples_data = $this->mergeSampleConfigsByIdentity($samples_data);

      $validatedSampleTypesForManualLab = [];
      foreach ($samples_data as $sampleConfigRow) {
        $typeId = $sampleConfigRow['sample_type_id'] ?? '';
        if ($typeId === '' || isset($validatedSampleTypesForManualLab[$typeId])) {
          continue;
        }
        $validatedSampleTypesForManualLab[$typeId] = true;
        $methodsInRow = isset($sampleConfigRow['methods']) && is_array($sampleConfigRow['methods'])
          ? $sampleConfigRow['methods']
          : [];
        $usedLabsRow = $this->labsUsedFromMethodStrings($methodsInRow);
        try {
          $this->assertKesmasManualSampleCodesFilled($request, $usedLabsRow, $sampleConfigRow);
        } catch (\InvalidArgumentException $e) {
          return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
        }
      }

      // Get default program
      $program = Program::orderBy('created_at')->first();
      $default_program = $program ? $program->id_program : null;

      // Parse dates
      $datesampling_samples = $request->datesampling_samples ?
        Carbon::createFromFormat('d/m/Y H:i', $request->datesampling_samples)->format('Y-m-d H:i:s') :
        Carbon::now();
      $date_sending = $request->date_sending ?
        Carbon::createFromFormat('d/m/Y H:i', $request->date_sending)->format('Y-m-d H:i:s') :
        Carbon::now();


      $created_samples = [];

      $total_created = 0;

      // Get laboratoriums
      $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

      // Process each sample configuration
      // IMPORTANT: Urutan global untuk semua lab (satu urutan untuk semua)
      // Process samples in order, but create separate Sample records for each lab
      foreach ($samples_data as $sampleConfig) {
        // Generate a group_id for this specific sample type
        $group_id = Uuid::uuid4()->toString();

        // Get sample type for code generation
        $sample_type = SampleType::find($sampleConfig['sample_type_id']);
        $sample_type_code = $sample_type->code_sample_type ?? '...';

        // Extract unique lab IDs from methods
        $lab_ids = [];
        if (isset($sampleConfig['methods']) && is_array($sampleConfig['methods'])) {
          foreach ($sampleConfig['methods'] as $method_string) {
            $parts = explode('_', $method_string);
            if (count($parts) >= 2 && !in_array($parts[1], $lab_ids)) {
              $lab_ids[] = $parts[1];
            }
          }
        }

        // Skip if no lab IDs found (no methods selected)
        if (empty($lab_ids)) {
          continue;
        }

        // Process each lab for this sample config
        // Urutan global - semua lab menggunakan urutan yang sama
        foreach ($lab_ids as $lab_key) {
          // Create new Sample for this lab
          $sample = new Sample;
          $sample->id_samples = Uuid::uuid4()->toString();
          $sample->permohonan_uji_id = $id;
          $sample->group_id = $group_id; // Same group ID for all samples from same input
          $sample->typesample_samples = $sampleConfig['sample_type_id'];
          $sample->packet_id = !empty($sampleConfig['packet_id']) ? $sampleConfig['packet_id'] : null;

          // Calculate cost only for methods in this lab
          $lab_cost = 0;
          if (isset($sampleConfig['methods']) && is_array($sampleConfig['methods'])) {
            foreach ($sampleConfig['methods'] as $method_string) {
              $parts = explode('_', $method_string);
              if (count($parts) >= 3 && $parts[1] == $lab_key) {
                $lab_cost += (float)$parts[2];
              }
            }
          }
          // Add packet cost if packet is selected (divide by number of labs if multiple labs)
          if (!empty($sampleConfig['packet_id']) && isset($sampleConfig['packet_price'])) {
            $lab_cost += ($sampleConfig['packet_price'] / count($lab_ids));
          }

          $sample->cost_samples = $lab_cost;
          $sample->note_samples = $request->note ?? $request->note_samples ?? null;
          $sample->titik_pengambilan = $sampleConfig['titik_pengambilan'] ?? $request->titik_pengambilan ?? null;
          $sample->datesampling_samples = $datesampling_samples;
          $sample->date_sending = $date_sending;
        $sample->name_pelanggan = $request->name_pelanggan ?? $permohonan_uji->customer->name_customer ?? null;
        $sample->is_sampling = $request->has('is_sampling') && $request->is_sampling !== null
          ? (int) $request->is_sampling
          : (int) ($permohonan_uji->is_sampling ?? 1);
        $sample->cost_sampling_samples = $request->cost_sampling ?? 0;
          $sample->program_samples = $request->program_samples ?? $default_program;
          $sample->pengambil_sampel = $user->name ?? 'Petugas';
          $sample->created_at = Carbon::now();
          $current_laboratorium = $laboratoriums->where('id_laboratorium', $lab_key)->first();
          if (!$current_laboratorium) continue;

          $current_lab_name = strtolower($current_laboratorium->nama_laboratorium);
          if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';
          $lab_code = $current_lab_name === 'kimia' ? '01' : '02';

          // Get start number
          $start_num = StartNum::join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('ms_start_number.deleted_at');
          })->where('id_laboratorium', $lab_key)->first();

          // Check if makanan/minuman
          $is_makanan = false;
          if ($sample_type && str_contains($sample_type->name_sample_type, "Makanan/Minuman/Lainnya")) {
            $is_makanan = true;
            $start_num = StartNum::where('code_lab_start_number', 'MAK-MIN')->first();
          }

          // Generate lab number - urutan GLOBAL, atau nomor laboratorium manual (setting Kesmas)
          $current_year = date('Y');
          $ksSettings = KesmasSampleNumberSettings::getSettings();
          $manualLabNum = $this->getKesmasManualLabNumFromSampleConfig($sampleConfig, $current_lab_name)
            ?? $this->getKesmasManualLabNumFromRequest($request, $current_lab_name);

          $sequence_detail_new = null;

          if ($manualLabNum === null) {
            $ksSettingsPreview = KesmasSampleNumberSettings::getSettings();
            $willUseManualSampleCode = (bool) $ksSettingsPreview->is_nomor_sampel_manual;

            if ($willUseManualSampleCode) {
              // Nomor sampel manual: jangan ambil counter global terpisah.
              // lab_number mengikuti angka di kode sampel (dicek setelah overrideCode dibaca).
              $lab_num_urutan = 0;
            } else {
              // Seed start number jika tahun masih kosong (sama seperti jalur lama)
              $current_global = GlobalLabSequence::getCurrentNumber($current_year);
              if ($current_global == 0 && $start_num && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
                GlobalLabSequence::raiseLastNumberToAtLeast((int) ($start_num->count_start_number ?? 0), $current_year);
              }

              // Urutan GLOBAL bersama klinik+kesmas (max nomor hidup + 1)
              $lab_num_urutan = GlobalLabSequence::getNextNumber($current_year, $lab_key, 'lab', null);
              $sequence_detail_new = GlobalLabSequenceDetail::where('year', $current_year)
                ->where('sequence_number', $lab_num_urutan)
                ->where('lab_id', $lab_key)
                ->where('lab_type', 'lab')
                ->orderBy('created_at', 'desc')
                ->first();
            }
          } else {
            $lab_num_urutan = $manualLabNum;
          }

          $ksNomorSampelManual = (bool) $ksSettings->is_nomor_sampel_manual;
          $overrideCode = null;
          if ($ksNomorSampelManual) {
            if ($current_lab_name === 'kimia' && !empty($sampleConfig['code_sample_kimia'])) {
              $overrideCode = trim((string) $sampleConfig['code_sample_kimia']);
            } elseif ($current_lab_name === 'mikro' && !empty($sampleConfig['code_sample_mikro'])) {
              $overrideCode = trim((string) $sampleConfig['code_sample_mikro']);
            }
            if (($overrideCode === null || $overrideCode === '') && !empty($sampleConfig['code_sample'])) {
              $overrideCode = trim((string) $sampleConfig['code_sample']);
            }
          }

          if ($ksNomorSampelManual) {
            if ($overrideCode === null || $overrideCode === '') {
              throw new \InvalidArgumentException(
                'Nomor sampel manual wajib untuk jenis ' . $sample_type_code
                . ' (' . $current_lab_name . '). Lengkapi di langkah Review & Simpan.'
              );
            }
            $code_sample = $this->normalizeKesmasManualSampleCode($overrideCode, $sample_type_code);
            $partsMc = explode('/', $code_sample);
            $code_number = isset($partsMc[1]) ? (int) preg_replace('/\D/', '', (string) $partsMc[1]) : 0;
            if ($code_number < 1) {
              throw new \InvalidArgumentException(
                'Format nomor sampel manual tidak valid untuk jenis ' . $sample_type_code . '.'
              );
            }
            $this->persistKesmasManualSampleCode($sample, $code_sample, $sample_type_code);

            // Samakan lab_number dengan nomor di kode sampel (hindari counter global yang melenceng)
            if ($manualLabNum === null) {
              $lab_num_urutan = $code_number;
              GlobalLabSequence::raiseLastNumberToAtLeast($lab_num_urutan, $current_year);
            }
          } else {
            $code_number = str_pad((int) $lab_num_urutan, 4, '0', STR_PAD_LEFT);
            $code_year = Carbon::now()->format('Y');
            $code_sample = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;
            $sample->codesample_samples = $code_sample;
            $sample->count_id = $code_number;
            $sample->is_nomor_sampel_manual = 0;
          }
          $sample->is_nomor_laboratorium_manual = ($manualLabNum !== null) ? 1 : 0;

          // IMPORTANT: Save sample first before creating LabNum (for foreign key constraint)
          $sample->save();

          // Verify sample was saved
          if (!$sample->id_samples) {
            throw new \Exception('Failed to save Sample record before creating LabNum');
          }

          // Create LabNum using create() method (UUID will be auto-generated by Uuid trait)
          try {
            $lab_num = LabNum::create([
              'sample_id' => $sample->id_samples,
              'sample_type_id' => $sample->typesample_samples,
              'lab_id' => $lab_key,
              'is_makanan' => $is_makanan ? 1 : 0,
              'mount_lab_num' => Carbon::now()->format('m'),
              'year_lab_num' => Carbon::now()->format('Y'),
              'permohonan_uji_id' => $sample->permohonan_uji_id,
              'lab_number' => $lab_num_urutan,
            ]);

            // Verify LabNum was created
            if (!$lab_num || !$lab_num->id_lab_num) {
              throw new \Exception('Failed to create LabNum record - no ID returned');
            }

            // Refresh to ensure we have the latest data
            $lab_num->refresh();

          } catch (\Exception $e) {
            // Log detailed error information
            \Log::error('Failed to create LabNum', [
              'error' => $e->getMessage(),
              'sample_id' => $sample->id_samples ?? 'N/A',
              'sample_type_id' => $sample->typesample_samples ?? 'N/A',
              'lab_id' => $lab_key ?? 'N/A',
              'lab_number' => $lab_num_urutan ?? 'N/A',
              'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to create LabNum record: ' . $e->getMessage());
          }

          // Update reference_id in GlobalLabSequenceDetail (otomatis: baris dari getNextNumber; manual: nomor input)
          if ($sequence_detail_new) {
            $sequence_detail_new->reference_id = $lab_num->id_lab_num;
            $sequence_detail_new->save();

            if ($sequence_detail_new->reference_id !== $lab_num->id_lab_num) {
              throw new \Exception('Failed to update GlobalLabSequenceDetail reference_id');
            }
          } else {
            $this->linkGlobalLabSequenceDetailForLabNum(
              $lab_key,
              (int) $lab_num_urutan,
              $lab_num->id_lab_num,
              $manualLabNum !== null,
              $lab_num->year_lab_num ?? $current_year
            );
            if ($manualLabNum === null) {
              $linked = GlobalLabSequenceDetail::where('reference_id', $lab_num->id_lab_num)->exists();
              if (!$linked) {
                \Log::warning('GlobalLabSequenceDetail not found for sequence_number: ' . $lab_num_urutan . ', lab_id: ' . $lab_key);
              }
            }
          }

          if ($manualLabNum !== null) {
            $this->upsertNomerLabKesmasIfManual($id, $lab_key, (int) $manualLabNum);
          }

          // Sample already saved above before LabNum creation

          // Save methods for this sample (only methods for this lab)
          if (isset($sampleConfig['methods']) && is_array($sampleConfig['methods'])) {
            foreach ($sampleConfig['methods'] as $method_string) {
              $parts = explode('_', $method_string);
              // Only save methods that belong to this lab
              if (count($parts) >= 3 && $parts[1] == $lab_key) {
                $sample_method = new SampleMethod;
                $sample_method->id_sample_method = Uuid::uuid4()->toString();
                $sample_method->sample_id = $sample->id_samples;
                $sample_method->method_id = $parts[0];
                $sample_method->laboratorium_id = $parts[1];
                $sample_method->price_method = (float)$parts[2];
                $sample_method->is_sub = isset($parts[3]) ? (int)$parts[3] : 0;
                $sample_method->save();
              }
            }
          }

          // Create PenerimaanSample if kelayakan data exists (for this lab's sample)
          if ($request->has('kelayakan_tempat_kemasan') || $request->has('kelayakan_berat_vol')) {
            $penerimaan_sample = new PenerimaanSample;
            $penerimaan_sample->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan_sample->sample_id = $sample->id_samples;
            $penerimaan_sample->laboratorium_id = $lab_key;
            $penerimaan_sample->penerimaan_sample_date = Carbon::now();
            $penerimaan_sample->kelayakan_tempat_kemasan = $request->kelayakan_tempat_kemasan ?? 'layak';
            $penerimaan_sample->kelayakan_berat_vol = $request->kelayakan_berat_vol ?? 'layak';
            $penerimaan_sample->kondisi_sample = 'Baik';
            $penerimaan_sample->penerima_sampel = $user->name ?? 'Petugas';
            $penerimaan_sample->penerima_tanggal = Carbon::now();
            $penerimaan_sample->save();
          }

          // Create VerificationActivitySample step 1 (Pendaftaran/Registrasi) automatically
          // Using tb_samples.date_sending as start_date with 5 minutes duration
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_verification_activity = 1; // Step 1: Pendaftaran/Registrasi
          $verificationActivitySample->id_sample = $sample->id_samples;
          $verificationActivitySample->start_date = $date_sending; // Use date_sending (already parsed to Y-m-d H:i:s)
          $verificationActivitySample->stop_date = Carbon::parse($date_sending)->addMinutes(5)->format('Y-m-d H:i:s'); // Add 5 minutes
          $verificationActivitySample->nama_petugas = $permohonan_uji->petugas_penerima ?? $user->name ?? 'Petugas';
          $verificationActivitySample->is_done = true;
          $verificationActivitySample->save();

          $created_samples[] = $sample->id_samples;
          $total_created++;
        }

        // Note: PenerimaanSample is now created inside the lab loop above
      }

      // Update total_harga on PermohonanUji
      $total_cost = Sample::where('permohonan_uji_id', $id)->sum('cost_samples');
      $total_sampling_cost = Sample::where('permohonan_uji_id', $id)
        ->where('is_sampling', 1)
        ->distinct('group_id')
        ->get(['group_id', 'cost_sampling_samples'])
        ->sum('cost_sampling_samples');

      $permohonan_uji->total_harga = $total_cost + $total_sampling_cost;
      $permohonan_uji->save();

      DB::commit();

      $message = "Berhasil menyimpan {$total_created} sample dengan grup yang sama!";
      return response()->json([
        'status' => true,
        'pesan' => $message,
        'total_created' => $total_created,
        'group_id' => $group_id,
        'ids' => $created_samples,
        'url_redirect' => route('elits-samples.index', $id)
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => false,
        'pesan' => 'Gagal menyimpan multiple samples: ' . $e->getMessage()
      ], 400);
    }
  }

  public function storeSampleDuplicate(Request $request, $data, $id_lab)
  {
    $sample = Sample::query()->find($data);

    // Simpan name_pelanggan dari sample asli sebelum di-overwrite di loop
    $original_name_pelanggan = $sample->name_pelanggan;

    $codesSamples = Sample::query()->where('permohonan_uji_id', '=', $sample->permohonan_uji_id)->get();


    DB::beginTransaction();
    // try {

      // dd($sample->packet_id);


      $packet_id=$sample->packet_id;
      if (isset($packet_id)) {
        // dd($sample);
        $samples = Sample::where('permohonan_uji_id',$sample->permohonan_uji_id)->where('created_at',$sample->created_at)->get();
        // dd($samples );

        # code...
        $created_at = Carbon::now()->format('Y-m-d H:i:s');


        foreach ($samples as $index => $sample) {




          $labNum = LabNum::query()->where('sample_id', '=', $sample->id_samples)->get();


          $sampleMethod = SampleMethod::query()->where('sample_id', '=', $sample->id_samples)->get();

          // dd($labNum);


          // print($sampleMethod);
          $penerimaanSamples = PenerimaanSample::query()->where('sample_id', '=', $sample->id_samples)->get();

          $arrayCode = [];
          foreach ($codesSamples as $cds) {
            $codes = explode('/', $cds->codesample_samples);
            array_push($arrayCode, $codes[0]);
          }

          $code = explode('/', $sample->codesample_samples);
          // $number = max($arrayCode);
          // $number = (int)$number + 1;

          // $number_copy= $number;
          // dd($number_copy);

          $sample_type = SampleType::find($sample->typesample_samples);


          // Gunakan urutan global untuk nomor baru
          $globalNum = GlobalLabSequence::getNextNumber(null, $labNum[0]->lab_id, 'lab', null);
          $number_copy = (int)$globalNum;
          $number = str_pad($globalNum, 4, "0", STR_PAD_LEFT);
          $code[1] = $number;
          $code = implode('/', $code);


          // dd($code);


          $duplicateSample = new Sample();

          $duplicateSample->count_id = $number_copy;
          $duplicateSample->packet_id = $sample->packet_id;
          $duplicateSample->created_at = $created_at;
          $duplicateSample->is_pudam  = $request->post('is_pudam')?1:0;
          $duplicateSample->name_customer_pdam = $request->post('name_customer_pdam');
          $duplicateSample->address_location_pdam  = $request->post('address_location_pdam');

          // $created_at

          $duplicateSample->name_pelanggan = $original_name_pelanggan;
          $duplicateSample->jenis_makanan_id = $sample->jenis_makanan_id;
          $duplicateSample->permohonan_uji_id = $sample->permohonan_uji_id;
          $duplicateSample->codesample_samples = $code;
          $duplicateSample->is_nomor_sampel_manual = 0;
          $duplicateSample->is_nomor_laboratorium_manual = 0;
          $duplicateSample->typesample_samples = $sample->typesample_samples;
          $duplicateSample->jenis_sarana_names = $sample->jenis_sarana_names;
          $duplicateSample->gender_samples = $sample->gender_samples;
          $duplicateSample->umur_samples = $sample->umur_samples;
          $duplicateSample->program_samples = $sample->program_samples;
          $duplicateSample->titik_pengambilan = $sample->titik_pengambilan;
          $duplicateSample->wadah_samples = $sample->wadah_samples;
          $duplicateSample->wadah_samples_others = $sample->wadah_samples_others;
          $duplicateSample->unit_samples = $sample->unit_samples;
          $duplicateSample->location_samples = $sample->location_samples;
          $duplicateSample->cost_samples = $sample->cost_samples;
          $duplicateSample->biaya_tindakan_rectal_swab = $sample->biaya_tindakan_rectal_swab;
          $duplicateSample->note_samples = $sample->note_samples;
          $duplicateSample->status = $sample->status;
          $duplicateSample->name_send_sample = $sample->name_send_sample;
          $duplicateSample->code_sample_customer = $sample->code_sample_customer;
          $duplicateSample->date_sending = $sample->date_sending;
          $duplicateSample->datesampling_samples = $sample->datesampling_samples;
          $duplicateSample->date_done_estimation = $sample->date_done_estimation;
          $duplicateSample->date_penerimaan_sample = $sample->date_penerimaan_sample;
          $duplicateSample->date_penanganan_sample = $sample->date_penanganan_sample;
          $duplicateSample->date_analitik_sample = $sample->date_analitik_sample;
          $duplicateSample->pelaporan_hasil_sample = $sample->pelaporan_hasil_sample;
          $duplicateSample->pengetikan_hasil_sample = $sample->pengetikan_hasil_sample;
          $duplicateSample->is_lengkap = $sample->is_lengkap;
          $duplicateSample->date_delegation = $sample->date_delegation;
          $duplicateSample->date_analition = $sample->date_analition;
          $duplicateSample->date_invoice = $sample->date_invoice;
          $duplicateSample->sample_type_group = $sample->sample_type_group;

          $duplicateSample->save();




          foreach ($labNum as $lab) {



            if (!str_contains($sample_type->name_sample_type,"Makanan/Minuman/Lainnya")) {
              # code...
              // $this->sortingNumber($lab->lab_id,$number_copy);

              $newLab = new LabNum();

              $newLab->sample_id = $duplicateSample->id_samples;
              $newLab->sample_type_id = $lab->sample_type_id;
              $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
              $newLab->lab_id = $lab->lab_id;
              $newLab->lab_number = $number_copy;
              $newLab->lab_string = $lab->lab_string;
              $newLab->mount_lab_num = $lab->mount_lab_num;
              $newLab->year_lab_num = $lab->year_lab_num;
              $newLab->save();

              // Update reference_id pada detail urutan global
              $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                ->where('year', date('Y'))
                ->where('lab_id', $lab->lab_id)
                ->whereNull('reference_id')
                ->orderBy('created_at', 'desc')
                ->first();
              if ($seqDetail) {
                $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
              }
            }else{
              // $this->sortingNumber($lab->lab_id,null);

              $newLab = new LabNum();



              $newLab->sample_id = $duplicateSample->id_samples;
              $newLab->sample_type_id = $lab->sample_type_id;
              $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
              $newLab->lab_id = $lab->lab_id;
              $newLab->is_makanan = 1;
              $newLab->lab_number = $number_copy;
              $newLab->lab_string = $lab->lab_string;
              $newLab->mount_lab_num = $lab->mount_lab_num;
              $newLab->year_lab_num = $lab->year_lab_num;
              $newLab->save();

              // Update reference_id pada detail urutan global
              $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                ->where('year', date('Y'))
                ->where('lab_id', $lab->lab_id)
                ->whereNull('reference_id')
                ->orderBy('created_at', 'desc')
                ->first();
              if ($seqDetail) {
                $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
              }
            }


            // $start_num= StartNum::join('ms_laboratorium', function ($join) {
            //   $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
            //     ->whereNull('ms_laboratorium.deleted_at')
            //     ->whereNull('ms_start_number.deleted_at');
            // })->where('id_laboratorium',$lab->lab_id)->first();


            // $lab_num_urutan =
            // LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
            // ->where('lab_id', $lab->lab_id)
            // ->count();
            // if ($lab_num_urutan>0) {
            //   # code...
            //   $lab_num_urutan =
            //   LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
            //   ->where('lab_id', $lab->lab_id)
            //   ->max('lab_number');
            //   $lab_num_urutan=$lab_num_urutan +1;
            // }else{
            //   $lab_num_urutan=$lab_num_urutan +$start_num->count_start_number;

            // }


          }


          foreach ($sampleMethod as $smplMethod) {
            // if ($smplMethod->laboratorium_id == $id_lab) {
              $newSampleMethod = new SampleMethod();

              $newSampleMethod->sample_id = $duplicateSample->id_samples;
              $newSampleMethod->method_id = $smplMethod->method_id;
              $newSampleMethod->laboratorium_id = $smplMethod->laboratorium_id;
              $newSampleMethod->lab_num_id = $smplMethod->lab_num_id;
              $newSampleMethod->is_sub = $smplMethod->is_sub;
              $newSampleMethod->price_method = $smplMethod->price_method;

              $newSampleMethod->save();
            // }
          }

          foreach ($penerimaanSamples as $penerimaanSample) {
            if ($penerimaanSample->sample_id == $sample->id_samples) {
              $duplicatePenerimaanSample = new PenerimaanSample();

              $duplicatePenerimaanSample->sample_id = $duplicateSample->id_samples;
              $duplicatePenerimaanSample->laboratorium_id = $penerimaanSample->laboratorium_id;
              $duplicatePenerimaanSample->wadah_id = $penerimaanSample->wadah_id;
              $duplicatePenerimaanSample->wadah_sampel_other = $penerimaanSample->wadah_sampel_other;
              $duplicatePenerimaanSample->pengawet = $penerimaanSample->pengawet;
              $duplicatePenerimaanSample->pengawet_other = $penerimaanSample->pengawet_other;
              $duplicatePenerimaanSample->volume = $penerimaanSample->volume;
              $duplicatePenerimaanSample->unit_id = $penerimaanSample->unit_id;
              $duplicatePenerimaanSample->kondisi_sample = $penerimaanSample->kondisi_sample;
              $duplicatePenerimaanSample->validation_sample = $penerimaanSample->validation_sample;
              $duplicatePenerimaanSample->penerimaan_sample_date = $penerimaanSample->penerimaan_sample_date;
              $duplicatePenerimaanSample->kelayakan_tempat_kemasan = $penerimaanSample->kelayakan_tempat_kemasan;
              $duplicatePenerimaanSample->kelayakan_berat_vol = $penerimaanSample->kelayakan_berat_vol;

              $duplicatePenerimaanSample->save();
            }
          }

          $verifSample = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->first();
          $dateStartDate = Carbon::createFromFormat('Y-m-d H:i:s', $verifSample->stop_date)->format('Y-m-d H:i:s');

          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $duplicateSample->id_samples;
          $verificationActivitySample->id_verification_activity = 1;
          $verificationActivitySample->start_date = $dateStartDate;
          $verificationActivitySample->stop_date = Carbon::createFromFormat('Y-m-d H:i:s', $dateStartDate)->add(10, 'minutes')->format('Y-m-d H:i:s');
          $verificationActivitySample->nama_petugas = $verifSample->nama_petugas;
          $verificationActivitySample->is_done = true;

          $verificationActivitySample->save();
          # code...
        }
      }else{

        $samples = Sample::where('permohonan_uji_id',$sample->permohonan_uji_id)->where('created_at',$sample->created_at)->get();
        // dd($samples );

        # code...
        $created_at = Carbon::now()->format('Y-m-d H:i:s');


        foreach ($samples as $index => $sample) {


          $labNum = LabNum::query()->where('sample_id', '=', $sample->id_samples)->get();

          $sampleMethod = SampleMethod::query()->where('sample_id', '=', $sample->id_samples)->get();

          // dd($labNum);


          // print($sampleMethod);
          $penerimaanSamples = PenerimaanSample::query()->where('sample_id', '=', $sample->id_samples)->get();

          $arrayCode = [];
          foreach ($codesSamples as $cds) {
            $codes = explode('/', $cds->codesample_samples);
            array_push($arrayCode, $codes[0]);
          }

          $code = explode('/', $sample->codesample_samples);


          $sample_type = SampleType::find($sample->typesample_samples);



          // Gunakan urutan global untuk nomor baru
          $globalNum = GlobalLabSequence::getNextNumber(null, $labNum[0]->lab_id, 'lab', null);
          $number_copy = (int)$globalNum;
          $number = str_pad($globalNum, 4, "0", STR_PAD_LEFT);
          $code[0] = $number;
          $code = implode('/', $code);



          // try {
          $duplicateSample = new Sample();

          $duplicateSample->count_id = $number_copy;
          $duplicateSample->packet_id = $sample->packet_id;

          $duplicateSample->name_pelanggan = $original_name_pelanggan;
          $duplicateSample->jenis_makanan_id = $sample->jenis_makanan_id;
          $duplicateSample->permohonan_uji_id = $sample->permohonan_uji_id;
          $duplicateSample->codesample_samples = $code;
          $duplicateSample->is_nomor_sampel_manual = 0;
          $duplicateSample->is_nomor_laboratorium_manual = 0;
          $duplicateSample->typesample_samples = $sample->typesample_samples;
          $duplicateSample->jenis_sarana_names = $sample->jenis_sarana_names;
          $duplicateSample->gender_samples = $sample->gender_samples;
          $duplicateSample->umur_samples = $sample->umur_samples;
          $duplicateSample->program_samples = $sample->program_samples;
          $duplicateSample->titik_pengambilan = $sample->titik_pengambilan;
          $duplicateSample->wadah_samples = $sample->wadah_samples;
          $duplicateSample->wadah_samples_others = $sample->wadah_samples_others;
          $duplicateSample->unit_samples = $sample->unit_samples;
          $duplicateSample->location_samples = $sample->location_samples;
          $duplicateSample->cost_samples = $sample->cost_samples;
          $duplicateSample->biaya_tindakan_rectal_swab = $sample->biaya_tindakan_rectal_swab;
          $duplicateSample->note_samples = $sample->note_samples;
          $duplicateSample->status = $sample->status;
          $duplicateSample->name_send_sample = $sample->name_send_sample;
          $duplicateSample->code_sample_customer = $sample->code_sample_customer;
          $duplicateSample->date_sending = $sample->date_sending;
          $duplicateSample->datesampling_samples = $sample->datesampling_samples;
          $duplicateSample->date_done_estimation = $sample->date_done_estimation;
          $duplicateSample->date_penerimaan_sample = $sample->date_penerimaan_sample;
          $duplicateSample->date_penanganan_sample = $sample->date_penanganan_sample;
          $duplicateSample->date_analitik_sample = $sample->date_analitik_sample;
          $duplicateSample->pelaporan_hasil_sample = $sample->pelaporan_hasil_sample;
          $duplicateSample->pengetikan_hasil_sample = $sample->pengetikan_hasil_sample;
          $duplicateSample->is_lengkap = $sample->is_lengkap;
          $duplicateSample->date_delegation = $sample->date_delegation;
          $duplicateSample->date_analition = $sample->date_analition;
          $duplicateSample->date_invoice = $sample->date_invoice;
          $duplicateSample->sample_type_group = $sample->sample_type_group;


          $cek = $duplicateSample->save();
          // dd( $duplicateSample->id_samples);

          foreach ($labNum as $lab) {




            if (!str_contains($sample_type->name_sample_type,"Makanan/Minuman/Lainnya")) {
              # code...
              // $this->sortingNumber($lab->lab_id,$number_copy);

              $newLab = new LabNum();

              $newLab->sample_id = $duplicateSample->id_samples;
              $newLab->sample_type_id = $lab->sample_type_id;
              $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
              $newLab->lab_id = $lab->lab_id;
              $newLab->lab_number = $number_copy;
              $newLab->lab_string = $lab->lab_string;
              $newLab->mount_lab_num = $lab->mount_lab_num;
              $newLab->year_lab_num = $lab->year_lab_num;

              $newLab->save();
            }else{
              // $this->sortingNumber($lab->lab_id,null);

              $newLab = new LabNum();



              $newLab->sample_id = $duplicateSample->id_samples;
              $newLab->sample_type_id = $lab->sample_type_id;
              $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
              $newLab->lab_id = $lab->lab_id;
              $newLab->is_makanan = 1;
              $newLab->lab_number = $number_copy;
              $newLab->lab_string = $lab->lab_string;
              $newLab->mount_lab_num = $lab->mount_lab_num;
              $newLab->year_lab_num = $lab->year_lab_num;
              $newLab->save();

              // Update reference_id pada detail urutan global
              $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                ->where('year', date('Y'))
                ->where('lab_id', $lab->lab_id)
                ->whereNull('reference_id')
                ->orderBy('created_at', 'desc')
                ->first();
              if ($seqDetail) {
                $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
              }
            }


          }




          foreach ($sampleMethod as $smplMethod) {
            if ($smplMethod->laboratorium_id == $lab->lab_id) {
              $newSampleMethod = new SampleMethod();

              $newSampleMethod->sample_id = $duplicateSample->id_samples;
              $newSampleMethod->method_id = $smplMethod->method_id;
              $newSampleMethod->laboratorium_id = $smplMethod->laboratorium_id;
              $newSampleMethod->lab_num_id = $smplMethod->lab_num_id;
              $newSampleMethod->is_sub = $smplMethod->is_sub;
              $newSampleMethod->price_method = $smplMethod->price_method;

              $newSampleMethod->save();
            }
          }



          foreach ($penerimaanSamples as $penerimaanSample) {
            if ($penerimaanSample->sample_id == $sample->id_samples) {
              $duplicatePenerimaanSample = new PenerimaanSample();

              $duplicatePenerimaanSample->sample_id = $duplicateSample->id_samples;
              $duplicatePenerimaanSample->laboratorium_id = $penerimaanSample->laboratorium_id;
              $duplicatePenerimaanSample->wadah_id = $penerimaanSample->wadah_id;
              $duplicatePenerimaanSample->wadah_sampel_other = $penerimaanSample->wadah_sampel_other;
              $duplicatePenerimaanSample->pengawet = $penerimaanSample->pengawet;
              $duplicatePenerimaanSample->pengawet_other = $penerimaanSample->pengawet_other;
              $duplicatePenerimaanSample->volume = $penerimaanSample->volume;
              $duplicatePenerimaanSample->unit_id = $penerimaanSample->unit_id;
              $duplicatePenerimaanSample->kondisi_sample = $penerimaanSample->kondisi_sample;
              $duplicatePenerimaanSample->validation_sample = $penerimaanSample->validation_sample;
              $duplicatePenerimaanSample->penerimaan_sample_date = $penerimaanSample->penerimaan_sample_date;
              $duplicatePenerimaanSample->kelayakan_tempat_kemasan = $penerimaanSample->kelayakan_tempat_kemasan;
              $duplicatePenerimaanSample->kelayakan_berat_vol = $penerimaanSample->kelayakan_berat_vol;

              $duplicatePenerimaanSample->save();
            }
          }

          $verifSample = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->first();
          $dateStartDate = Carbon::createFromFormat('Y-m-d H:i:s', $verifSample->stop_date)->format('Y-m-d H:i:s');

          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $duplicateSample->id_samples;
          $verificationActivitySample->id_verification_activity = 1;
          $verificationActivitySample->start_date = $dateStartDate;
          $verificationActivitySample->stop_date = Carbon::createFromFormat('Y-m-d H:i:s', $dateStartDate)->add(10, 'minutes')->format('Y-m-d H:i:s');
          $verificationActivitySample->nama_petugas = $verifSample->nama_petugas;
          $verificationActivitySample->is_done = true;

          $verificationActivitySample->save();
        }

      }




      // dd();
      DB::commit();
    // } catch (\Exception $exception) {
    //   DB::rollBack();
    //   return redirect()->back()->with('message', 'Duplicate sample failed');
    // }

    return redirect()->back()->with('message', 'Duplicate sample successfully');
  }

  /**
   * Store multiple duplicate samples with custom titik pengambilan
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function storeSampleDuplicateMultiple(Request $request)
  {
    try {
      $sampleId = $request->input('sample_id');
      $labId = $request->input('lab_id');
      $duplicateCount = (int) $request->input('duplicate_count', 1);
      $titikPengambilanArray = $request->input('titik_pengambilan', []);

      // Validate input
      if ($duplicateCount < 1 || $duplicateCount > 20) {
        return response()->json([
          'status' => false,
          'message' => 'Jumlah duplikasi harus antara 1 dan 20'
        ], 400);
      }

      $sample = Sample::find($sampleId);
      if (!$sample) {
        return response()->json([
          'status' => false,
          'message' => 'Sample tidak ditemukan'
        ], 404);
      }

      $codesSamples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)->get();
      $duplicatedSamples = [];

      DB::beginTransaction();

      $packet_id = $sample->packet_id;

      // Loop for each duplicate count
      for ($iteration = 0; $iteration < $duplicateCount; $iteration++) {
        // Get titik pengambilan for this iteration (if provided)
        $currentTitikPengambilan = isset($titikPengambilanArray[$iteration]) && !empty($titikPengambilanArray[$iteration])
          ? $titikPengambilanArray[$iteration]
          : $sample->titik_pengambilan;

        if (isset($packet_id)) {
          // Duplicate with packet
          $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->where('created_at', $sample->created_at)
            ->get();

          $created_at = Carbon::now()->format('Y-m-d H:i:s');

          foreach ($samples as $index => $sampleItem) {
            $labNum = LabNum::where('sample_id', $sampleItem->id_samples)->get();
            $sampleMethod = SampleMethod::where('sample_id', $sampleItem->id_samples)->get();
            $penerimaanSamples = PenerimaanSample::where('sample_id', $sampleItem->id_samples)->get();

            $arrayCode = [];
            foreach ($codesSamples as $cds) {
              $codes = explode('/', $cds->codesample_samples);
              array_push($arrayCode, $codes[0]);
            }

            // Parse existing code: AB.01/0002/2025 -> ['AB.01', '0002', '2025']
            $code = explode('/', $sampleItem->codesample_samples);
            $sample_type = SampleType::find($sampleItem->typesample_samples);

            // Use global sequence for duplicate
            $globalNum = GlobalLabSequence::getNextNumber(null, $labNum[0]->lab_id, 'lab', null);
            $number_copy = (int)$globalNum;

            // Format: AB.01/0002/2025
            // code[0] = sample type + lab code (AB.01) - KEEP THIS
            // code[1] = sequence number (0002) - UPDATE THIS
            // code[2] = year (2025) - UPDATE THIS
            $number = str_pad($globalNum, 4, "0", STR_PAD_LEFT);
            $code[1] = $number; // Update sequence number only
            $code[2] = Carbon::now()->format('Y'); // Update year
            $code = implode('/', $code);

            // Create duplicate sample
            $duplicateSample = new Sample();
            $duplicateSample->count_id = $number_copy;
            $duplicateSample->packet_id = $sampleItem->packet_id;
            $duplicateSample->created_at = $created_at;
            $duplicateSample->is_pudam = $request->post('is_pudam') ? 1 : 0;
            $duplicateSample->name_customer_pdam = $request->post('name_customer_pdam');
            $duplicateSample->address_location_pdam = $request->post('address_location_pdam');
            $duplicateSample->jenis_makanan_id = $sampleItem->jenis_makanan_id;
            $duplicateSample->permohonan_uji_id = $sampleItem->permohonan_uji_id;
            $duplicateSample->codesample_samples = $code;
            $duplicateSample->is_nomor_sampel_manual = 0;
            $duplicateSample->is_nomor_laboratorium_manual = 0;
            $duplicateSample->typesample_samples = $sampleItem->typesample_samples;
            $duplicateSample->name_pelanggan = $sampleItem->name_pelanggan;
            $duplicateSample->jenis_sarana_names = $sampleItem->jenis_sarana_names;
            $duplicateSample->gender_samples = $sampleItem->gender_samples;
            $duplicateSample->umur_samples = $sampleItem->umur_samples;
            $duplicateSample->program_samples = $sampleItem->program_samples;

            // Use custom titik pengambilan if provided
            $duplicateSample->titik_pengambilan = $currentTitikPengambilan;

            $duplicateSample->wadah_samples = $sampleItem->wadah_samples;
            $duplicateSample->wadah_samples_others = $sampleItem->wadah_samples_others;
            $duplicateSample->unit_samples = $sampleItem->unit_samples;
            $duplicateSample->location_samples = $sampleItem->location_samples;
            $duplicateSample->cost_samples = $sampleItem->cost_samples;
            $duplicateSample->biaya_tindakan_rectal_swab = $sampleItem->biaya_tindakan_rectal_swab;
            $duplicateSample->note_samples = $sampleItem->note_samples;
            $duplicateSample->status = $sampleItem->status;
            $duplicateSample->name_send_sample = $sampleItem->name_send_sample;
            $duplicateSample->code_sample_customer = $sampleItem->code_sample_customer;
            $duplicateSample->date_sending = $sampleItem->date_sending;
            $duplicateSample->datesampling_samples = $sampleItem->datesampling_samples;
            $duplicateSample->date_done_estimation = $sampleItem->date_done_estimation;
            $duplicateSample->date_penerimaan_sample = $sampleItem->date_penerimaan_sample;
            $duplicateSample->date_penanganan_sample = $sampleItem->date_penanganan_sample;
            $duplicateSample->date_analitik_sample = $sampleItem->date_analitik_sample;
            $duplicateSample->pelaporan_hasil_sample = $sampleItem->pelaporan_hasil_sample;
            $duplicateSample->pengetikan_hasil_sample = $sampleItem->pengetikan_hasil_sample;
            $duplicateSample->is_lengkap = $sampleItem->is_lengkap;
            $duplicateSample->date_delegation = $sampleItem->date_delegation;
            $duplicateSample->date_analition = $sampleItem->date_analition;
            $duplicateSample->date_invoice = $sampleItem->date_invoice;
            $duplicateSample->sample_type_group = $sampleItem->sample_type_group;

            $duplicateSample->save();
            $duplicatedSamples[] = $duplicateSample->codesample_samples;

            // Duplicate lab numbers
            foreach ($labNum as $lab) {
              if (!str_contains($sample_type->name_sample_type, "Makanan/Minuman/Lainnya")) {
                $newLab = new LabNum();
                $newLab->sample_id = $duplicateSample->id_samples;
                $newLab->sample_type_id = $lab->sample_type_id;
                $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
                $newLab->lab_id = $lab->lab_id;
                $newLab->lab_number = $number_copy;
                $newLab->lab_string = $lab->lab_string;
                $newLab->mount_lab_num = $lab->mount_lab_num;
                $newLab->year_lab_num = $lab->year_lab_num;
                $newLab->save();

                // Update global sequence detail reference
                $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                  ->where('year', date('Y'))
                  ->where('lab_id', $lab->lab_id)
                  ->whereNull('reference_id')
                  ->orderBy('created_at', 'desc')
                  ->first();
                if ($seqDetail) {
                  $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
                }
              } else {
                $newLab = new LabNum();
                $newLab->sample_id = $duplicateSample->id_samples;
                $newLab->sample_type_id = $lab->sample_type_id;
                $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
                $newLab->lab_id = $lab->lab_id;
                $newLab->is_makanan = 1;
                $newLab->lab_number = $number_copy;
                $newLab->lab_string = $lab->lab_string;
                $newLab->mount_lab_num = $lab->mount_lab_num;
                $newLab->year_lab_num = $lab->year_lab_num;
                $newLab->save();

                $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                  ->where('year', date('Y'))
                  ->where('lab_id', $lab->lab_id)
                  ->whereNull('reference_id')
                  ->orderBy('created_at', 'desc')
                  ->first();
                if ($seqDetail) {
                  $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
                }
              }
            }

            // Duplicate sample methods
            foreach ($sampleMethod as $smplMethod) {
              $newSampleMethod = new SampleMethod();
              $newSampleMethod->sample_id = $duplicateSample->id_samples;
              $newSampleMethod->method_id = $smplMethod->method_id;
              $newSampleMethod->laboratorium_id = $smplMethod->laboratorium_id;
              $newSampleMethod->lab_num_id = $smplMethod->lab_num_id;
              $newSampleMethod->is_sub = $smplMethod->is_sub;
              $newSampleMethod->price_method = $smplMethod->price_method;
              $newSampleMethod->save();
            }

            // Duplicate penerimaan samples
            foreach ($penerimaanSamples as $penerimaanSample) {
              if ($penerimaanSample->sample_id == $sampleItem->id_samples) {
                $duplicatePenerimaanSample = new PenerimaanSample();
                $duplicatePenerimaanSample->sample_id = $duplicateSample->id_samples;
                $duplicatePenerimaanSample->laboratorium_id = $penerimaanSample->laboratorium_id;
                $duplicatePenerimaanSample->wadah_id = $penerimaanSample->wadah_id;
                $duplicatePenerimaanSample->wadah_sampel_other = $penerimaanSample->wadah_sampel_other;
                $duplicatePenerimaanSample->pengawet = $penerimaanSample->pengawet;
                $duplicatePenerimaanSample->pengawet_other = $penerimaanSample->pengawet_other;
                $duplicatePenerimaanSample->volume = $penerimaanSample->volume;
                $duplicatePenerimaanSample->unit_id = $penerimaanSample->unit_id;
                $duplicatePenerimaanSample->kondisi_sample = $penerimaanSample->kondisi_sample;
                $duplicatePenerimaanSample->validation_sample = $penerimaanSample->validation_sample;
                $duplicatePenerimaanSample->penerimaan_sample_date = $penerimaanSample->penerimaan_sample_date;
                $duplicatePenerimaanSample->kelayakan_tempat_kemasan = $penerimaanSample->kelayakan_tempat_kemasan;
                $duplicatePenerimaanSample->kelayakan_berat_vol = $penerimaanSample->kelayakan_berat_vol;
                $duplicatePenerimaanSample->save();
              }
            }

            // Duplicate verification activity
            $verifSample = VerificationActivitySample::where('id_sample', $sampleItem->id_samples)->first();
            if ($verifSample) {
              $dateStartDate = Carbon::createFromFormat('Y-m-d H:i:s', $verifSample->stop_date)->format('Y-m-d H:i:s');

              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_sample = $duplicateSample->id_samples;
              $verificationActivitySample->id_verification_activity = 1;
              $verificationActivitySample->start_date = $dateStartDate;
              $verificationActivitySample->stop_date = Carbon::createFromFormat('Y-m-d H:i:s', $dateStartDate)->add(10, 'minutes')->format('Y-m-d H:i:s');
              $verificationActivitySample->nama_petugas = $verifSample->nama_petugas;
              $verificationActivitySample->is_done = true;
              $verificationActivitySample->save();
            }
          }
        } else {
          // Duplicate without packet (similar logic for non-packet samples)
          $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->where('created_at', $sample->created_at)
            ->get();

          $created_at = Carbon::now()->format('Y-m-d H:i:s');

          foreach ($samples as $index => $sampleItem) {
            $labNum = LabNum::where('sample_id', $sampleItem->id_samples)->get();
            $sampleMethod = SampleMethod::where('sample_id', $sampleItem->id_samples)->get();
            $penerimaanSamples = PenerimaanSample::where('sample_id', $sampleItem->id_samples)->get();

            $arrayCode = [];
            foreach ($codesSamples as $cds) {
              $codes = explode('/', $cds->codesample_samples);
              array_push($arrayCode, $codes[1]);
            }

            // Parse existing code: AB.01/0002/2025 -> ['AB.01', '0002', '2025']
            $code = explode('/', $sampleItem->codesample_samples);
            $sample_type = SampleType::find($sampleItem->typesample_samples);

            // Use global sequence for duplicate
            $globalNum = GlobalLabSequence::getNextNumber(null, $labNum[0]->lab_id, 'lab', null);
            $number_copy = (int)$globalNum;

            // Format: AB.01/0002/2025
            // code[0] = sample type + lab code (AB.01) - KEEP THIS
            // code[1] = sequence number (0002) - UPDATE THIS
            // code[2] = year (2025) - UPDATE THIS
            $number = str_pad($globalNum, 4, "0", STR_PAD_LEFT);
            $code[1] = $number; // Update sequence number only
            $code[2] = Carbon::now()->format('Y'); // Update year
            $code = implode('/', $code);

            $duplicateSample = new Sample();
            $duplicateSample->count_id = $number_copy;
            $duplicateSample->name_pelanggan = $sampleItem->name_pelanggan;
            $duplicateSample->jenis_makanan_id = $sampleItem->jenis_makanan_id;
            $duplicateSample->permohonan_uji_id = $sampleItem->permohonan_uji_id;
            $duplicateSample->codesample_samples = $code;
            $duplicateSample->is_nomor_sampel_manual = 0;
            $duplicateSample->is_nomor_laboratorium_manual = 0;
            $duplicateSample->typesample_samples = $sampleItem->typesample_samples;
            $duplicateSample->jenis_sarana_names = $sampleItem->jenis_sarana_names;
            $duplicateSample->gender_samples = $sampleItem->gender_samples;
            $duplicateSample->umur_samples = $sampleItem->umur_samples;
            $duplicateSample->program_samples = $sampleItem->program_samples;

            // Use custom titik pengambilan if provided
            $duplicateSample->titik_pengambilan = $currentTitikPengambilan;

            $duplicateSample->wadah_samples = $sampleItem->wadah_samples;
            $duplicateSample->wadah_samples_others = $sampleItem->wadah_samples_others;
            $duplicateSample->unit_samples = $sampleItem->unit_samples;
            $duplicateSample->location_samples = $sampleItem->location_samples;
            $duplicateSample->cost_samples = $sampleItem->cost_samples;
            $duplicateSample->biaya_tindakan_rectal_swab = $sampleItem->biaya_tindakan_rectal_swab;
            $duplicateSample->note_samples = $sampleItem->note_samples;
            $duplicateSample->status = $sampleItem->status;
            $duplicateSample->name_send_sample = $sampleItem->name_send_sample;
            $duplicateSample->code_sample_customer = $sampleItem->code_sample_customer;
            $duplicateSample->date_sending = $sampleItem->date_sending;
            $duplicateSample->datesampling_samples = $sampleItem->datesampling_samples;
            $duplicateSample->date_done_estimation = $sampleItem->date_done_estimation;
            $duplicateSample->date_penerimaan_sample = $sampleItem->date_penerimaan_sample;
            $duplicateSample->date_penanganan_sample = $sampleItem->date_penanganan_sample;
            $duplicateSample->date_analitik_sample = $sampleItem->date_analitik_sample;
            $duplicateSample->pelaporan_hasil_sample = $sampleItem->pelaporan_hasil_sample;
            $duplicateSample->pengetikan_hasil_sample = $sampleItem->pengetikan_hasil_sample;
            $duplicateSample->is_lengkap = $sampleItem->is_lengkap;
            $duplicateSample->date_delegation = $sampleItem->date_delegation;
            $duplicateSample->date_analition = $sampleItem->date_analition;
            $duplicateSample->date_invoice = $sampleItem->date_invoice;
            $duplicateSample->sample_type_group = $sampleItem->sample_type_group;

            $duplicateSample->save();
            $duplicatedSamples[] = $duplicateSample->codesample_samples;

            // Duplicate lab numbers
            foreach ($labNum as $lab) {
              if (!str_contains($sample_type->name_sample_type, "Makanan/Minuman/Lainnya")) {
                $newLab = new LabNum();
                $newLab->sample_id = $duplicateSample->id_samples;
                $newLab->sample_type_id = $lab->sample_type_id;
                $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
                $newLab->lab_id = $lab->lab_id;
                $newLab->lab_number = $number_copy;
                $newLab->lab_string = $lab->lab_string;
                $newLab->mount_lab_num = $lab->mount_lab_num;
                $newLab->year_lab_num = $lab->year_lab_num;
                $newLab->save();

                $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                  ->where('year', date('Y'))
                  ->where('lab_id', $lab->lab_id)
                  ->whereNull('reference_id')
                  ->orderBy('created_at', 'desc')
                  ->first();
                if ($seqDetail) {
                  $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
                }
              } else {
                $newLab = new LabNum();
                $newLab->sample_id = $duplicateSample->id_samples;
                $newLab->sample_type_id = $lab->sample_type_id;
                $newLab->permohonan_uji_id = $lab->permohonan_uji_id;
                $newLab->lab_id = $lab->lab_id;
                $newLab->is_makanan = 1;
                $newLab->lab_number = $number_copy;
                $newLab->lab_string = $lab->lab_string;
                $newLab->mount_lab_num = $lab->mount_lab_num;
                $newLab->year_lab_num = $lab->year_lab_num;
                $newLab->save();

                $seqDetail = GlobalLabSequenceDetail::where('sequence_number', $number_copy)
                  ->where('year', date('Y'))
                  ->where('lab_id', $lab->lab_id)
                  ->whereNull('reference_id')
                  ->orderBy('created_at', 'desc')
                  ->first();
                if ($seqDetail) {
                  $seqDetail->update(['reference_id' => $newLab->id_lab_num]);
                }
              }
            }

            // Duplicate sample methods
            foreach ($sampleMethod as $smplMethod) {
              $newSampleMethod = new SampleMethod();
              $newSampleMethod->sample_id = $duplicateSample->id_samples;
              $newSampleMethod->method_id = $smplMethod->method_id;
              $newSampleMethod->laboratorium_id = $smplMethod->laboratorium_id;
              $newSampleMethod->lab_num_id = $smplMethod->lab_num_id;
              $newSampleMethod->is_sub = $smplMethod->is_sub;
              $newSampleMethod->price_method = $smplMethod->price_method;
              $newSampleMethod->save();
            }

            // Duplicate penerimaan samples
            foreach ($penerimaanSamples as $penerimaanSample) {
              if ($penerimaanSample->sample_id == $sampleItem->id_samples) {
                $duplicatePenerimaanSample = new PenerimaanSample();
                $duplicatePenerimaanSample->sample_id = $duplicateSample->id_samples;
                $duplicatePenerimaanSample->laboratorium_id = $penerimaanSample->laboratorium_id;
                $duplicatePenerimaanSample->wadah_id = $penerimaanSample->wadah_id;
                $duplicatePenerimaanSample->wadah_sampel_other = $penerimaanSample->wadah_sampel_other;
                $duplicatePenerimaanSample->pengawet = $penerimaanSample->pengawet;
                $duplicatePenerimaanSample->pengawet_other = $penerimaanSample->pengawet_other;
                $duplicatePenerimaanSample->volume = $penerimaanSample->volume;
                $duplicatePenerimaanSample->unit_id = $penerimaanSample->unit_id;
                $duplicatePenerimaanSample->kondisi_sample = $penerimaanSample->kondisi_sample;
                $duplicatePenerimaanSample->validation_sample = $penerimaanSample->validation_sample;
                $duplicatePenerimaanSample->penerimaan_sample_date = $penerimaanSample->penerimaan_sample_date;
                $duplicatePenerimaanSample->kelayakan_tempat_kemasan = $penerimaanSample->kelayakan_tempat_kemasan;
                $duplicatePenerimaanSample->kelayakan_berat_vol = $penerimaanSample->kelayakan_berat_vol;
                $duplicatePenerimaanSample->save();
              }
            }

            // Duplicate verification activity
            $verifSample = VerificationActivitySample::where('id_sample', $sampleItem->id_samples)->first();
            if ($verifSample) {
              $dateStartDate = Carbon::createFromFormat('Y-m-d H:i:s', $verifSample->stop_date)->format('Y-m-d H:i:s');

              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_sample = $duplicateSample->id_samples;
              $verificationActivitySample->id_verification_activity = 1;
              $verificationActivitySample->start_date = $dateStartDate;
              $verificationActivitySample->stop_date = Carbon::createFromFormat('Y-m-d H:i:s', $dateStartDate)->add(10, 'minutes')->format('Y-m-d H:i:s');
              $verificationActivitySample->nama_petugas = $verifSample->nama_petugas;
              $verificationActivitySample->is_done = true;
              $verificationActivitySample->save();
            }
          }
        }
      }

      DB::commit();

      $message = "Berhasil menduplikasi {$duplicateCount} sample";
      if (count($duplicatedSamples) > 0) {
        $message .= ": " . implode(', ', array_slice($duplicatedSamples, 0, 5));
        if (count($duplicatedSamples) > 5) {
          $message .= " dan " . (count($duplicatedSamples) - 5) . " lainnya";
        }
      }

      return response()->json([
        'status' => true,
        'message' => $message,
        'duplicated_samples' => $duplicatedSamples
      ]);

    } catch (\Exception $e) {
      DB::rollback();

      return response()->json([
        'status' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
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
  public function edit($id, $id_lab = null)
  {
    $auth = Auth()->user();

    $user = Auth()->user();
    if ($id_lab == null) {



      $sample = Sample::where('tb_samples.id_samples', '=', $id)->first();
      if (!$sample) {
        $sample = Sample::where('tb_samples.permohonan_uji_id', '=', $id)->first();
      }
      if (!$sample) {
        abort(404, 'Sampel tidak ditemukan.');
      }
      $id = $sample->id_samples;
      $sample2 = null;
      if (isset($sample->codeKimiaMikro)){
        $sample2 = Sample::query()->where('id_samples', '!=', $sample->id_samples)->where('codeKimiaMikro', '=', $sample->codeKimiaMikro)->first();
      }

      $permohonan_uji = $sample->permohonanuji ?: PermohonanUji::find($sample->permohonan_uji_id);
      if (!$permohonan_uji) {
        abort(404, 'Permohonan Uji tidak ditemukan.');
      }

        // dd(  $permohonan_uji);


      $penerimaan_sample = PenerimaanSample::where('sample_id', '=', $id)->first();

      // Check if sample has reached input hasil stage
      $has_input_hasil = DB::table('tb_verification_activity_samples')
        ->join('ms_verification_activities', 'ms_verification_activities.id', '=', 'tb_verification_activity_samples.id_verification_activity')
        ->where('tb_verification_activity_samples.id_sample', '=', $id)
        ->where('ms_verification_activities.name', 'LIKE', '%Input Hasil%')
        ->where('tb_verification_activity_samples.is_done', '=', 1)
        ->exists();

      // Get selected packet if exists (foreign key: tb_samples.pac -> ms_packet.id_packet)
      $selected_packet_id = null;
      if (isset($sample->packet_id) && !empty($sample->packet_id)) {
        $selected_packet_id = $sample->packet_id;
        \Log::info('Edit Sample - Selected Packet ID: ' . $selected_packet_id);
      } else {
        \Log::info('Edit Sample - No packet selected or pac is empty', [
          'sample_id' => $id,
          'pac_value' => $sample->packet_id ?? 'null'
        ]);
      }

      $methods = SampleMethod::where('sample_id', '=', $id)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->get();

      if (isset($sample2)){
        $methods = SampleMethod::where('sample_id', '=', $id)
          ->orWhere('sample_id', '=', $sample2->id_samples)
          ->orderBy('ms_method.created_at')
          ->join('ms_method', function ($join) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at');
          })
          ->get();
      }

      // "method_id" => "a39d7537-6b81-4a8b-800d-62aba1cb0dbb"
      // "laboratorium_id" => "3416ca19-6c69-4e5f-a004-ae8275de7644"


      $count = Sample::count();


      $users = User::all();

      $packets = Packet::where('id_packet', '!=', '0')->orderBy('created_at')->get();

      $all_jenis_makanan = JenisMakanan::all();

      $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();
      $programs = Program::orderBy('created_at')->get();

      $data_methods = array();
      $code_samples = array();
      $lab_keys = array();

      foreach ($laboratoriums as $laboratorium) {
        // Determine lab code: 01 for Kimia, 02 for Mikrobiologi
        $lab_code = strtolower($laboratorium->nama_laboratorium) === 'kimia' ? '01' : '02';

        // Get existing sample code for this lab if exists (paket: kimia & mikro bisa beda record)
        $existing_code = '';
        if (isset($sample)) {
          $pairedSample = $this->findPairedKesmasSample($sample);
          if (strtolower($laboratorium->nama_laboratorium) === 'kimia') {
            $existing_code = (string) ($sample->codesample_samples ?? '');
            if ($existing_code === '' && $pairedSample) {
              $existing_code = (string) ($pairedSample->codesample_samples ?? '');
            }
          } elseif (strtolower($laboratorium->nama_laboratorium) === 'mikrobiologi') {
            if ($pairedSample && !empty($pairedSample->codesample_samples)) {
              $existing_code = (string) $pairedSample->codesample_samples;
            } else {
              $existing_code = (string) ($sample->codesample_samples ?? '');
            }
          }
        }

        // Use existing code if available, otherwise generate new one
        if (!empty($existing_code)) {
          $code_samples[strtolower($laboratorium->nama_laboratorium)] = $this->formatKesmasCodeForLabDisplay(
            $existing_code,
            $lab_code
          );
        } else {
          $code_samples[strtolower($laboratorium->nama_laboratorium)] = $this->getCodeSample($this->getLabNumByLabKey($laboratorium->id_laboratorium, $sample->permohonanuji->id_permohonan_uji), $lab_code, '...');
        }

        $lab_keys[strtolower($laboratorium->nama_laboratorium)] = $laboratorium->id_laboratorium;

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

          // Semua sampletype_id yang punya baris baku mutu untuk method+lab ini (termasuk BM generik MM tanpa jenis_makanan_id)
          $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
            $laboratoriummethod->id_method,
            $data_method->id_lab
          );

          array_push(
            $data_methods[$i]->method,
            (object) array(
              'name_method' => $laboratoriummethod->params_method,
              'id_method' => $laboratoriummethod->id_method,
              'price_method' => $laboratoriummethod->price_total_method,
              'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
            )
          );
        }

        $i++;
      }

      $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);

      // dd($data_methods);







      // K/ tanggal/0001
      $containers = Container::where('id_container', '!=', '0')->get();
      $sampletypes = SampleType::orderBy('created_at')->get();

      $code = 'NK/' . date("Ymd", time()) . '/' . str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);

      $units = Unit::all();

      // Set $id as permohonan_uji ID for compatibility with view
      $id = $permohonan_uji->id_permohonan_uji;

      return view('masterweb::module.admin.laboratorium.sample.edit-2', compact('permohonan_uji','penerimaan_sample', 'methods', 'sample', 'programs', 'units', 'user', 'data_methods', 'containers', 'packets', 'sampletypes', 'code', 'users', 'all_jenis_makanan', 'code_samples', 'lab_keys', 'id', 'has_input_hasil', 'selected_packet_id'));
    } else {
    }


    // return view('masterweb::module.admin.laboratorium.customer.edit', compact('customer', 'auth', 'categories', 'id'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */

   public function updateTitik($id, Request $request)
  {


    $permohonan_uji = PermohonanUji::findOrFail($id);

    foreach ($permohonan_uji->samples as $one_sample){

      if ($one_sample->titik_pengambilan !="" || $one_sample->titik_pengambilan !=" " ) {
        # code...
        $samples = Sample::where('permohonan_uji_id',$one_sample->permohonan_uji_id)->where('created_at',$one_sample->created_at)->get();
        foreach ($samples as $index => $sample) {
          $sample->titik_pengambilan=$one_sample->titik_pengambilan;
          $sample->save();
        }
      }

    }


    return redirect()->route('elits-samples.index', [$id])->with(['status' => 'Sampel berhasil diupdate titik']);

  }
  public function update($id, Request $request)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      DB::beginTransaction();

      try {
        $data = $request->all();



        $sample = Sample::where('id_samples', $id)->first();

        // Store old code sample for comparison
        $old_code_sample = $sample->codesample_samples;

        $resolvedCodeForRouting = $this->resolveCodeSampleFromEditRequest(
          $request,
          $sample,
          (string) ($old_code_sample ?? '')
        );

        $methodsInput = $request->input('method', []);
        if (!is_array($methodsInput)) {
          $methodsInput = ($methodsInput !== null && $methodsInput !== '') ? [$methodsInput] : [];
        }
        $methodsNonEmptyCount = count(array_filter(
          $methodsInput,
          static function ($v) {
            return $v !== null && $v !== '';
          }
        ));
        $changeSampleType = in_array($request->input('changeSampleType'), ['1', 1, true, 'true'], true);

        // Sync parameter HANYA jika user mengubah jenis/paket/parameter (changeSampleType=1).
        // Form selalu mengirim method[] tercentang, jadi jangan pakai itu sebagai pemicu —
        // kalau dipaksa, edit nomor sampel saja ikut gagal di jalur paket kimia+mikro.
        if ($changeSampleType && $methodsNonEmptyCount > 0) {
          return $this->updateSampleAndParameter($id, $request);
        }

        // $samples = Sample::where('permohonan_uji_id',$sample->permohonan_uji_id)->where('created_at',$sample->created_at)->get();

        // $sample->id_samples = $uuid4->toString();
        // $sample->permohonan_uji_id = $id;

        // foreach ($samples as $index => $sample) {

          // $packet = $request->post('packet');

          $id_sample=$sample->id_samples;

          // if (isset($packet) || $packet != null) {
          //   $sample->packet_id = $packet;
          // } else {
          //   $sample->packet_id = null;
          // }



          // $karanganyar_code = 'NK/' .  Carbon::createFromFormat('d/m/Y H:i', $request->post('date_sending'))->format('Ymd') . '/' . str_pad((int)$sample->count_id, 4, '0', STR_PAD_LEFT);

          // $code_number = str_pad((int)$sample->count_id, 4, '0', STR_PAD_LEFT);

          // $code_type = isset($this->sample_types[$data['jenis_sampel']])
          //   ? $this->sample_types[$data['jenis_sampel']]
          //   : '...';

          // $code_datetime = Carbon::createFromFormat('d/m/Y H:i', $request->post('date_sending'));
          // $code_year = $code_datetime->format('Y');
          // $code_month = $code_datetime->format('m');

          // $boyolali_code = implode('/', [$code_number, $code_type, $code_year, $code_month]);

          // $code = $request->post('code_sample', $boyolali_code);



          // $sample->codesample_samples = $code;


          if (isset($request->name_pelanggan)) {
            # code...
            $sample->name_pelanggan =  $request->name_pelanggan;
          }

          $lab_num = LabNum::where('sample_id', $id_sample)->first();

          $resolvedCodeSample = $resolvedCodeForRouting ?? $this->resolveCodeSampleFromEditRequest($request, $sample, (string) ($old_code_sample ?? ''));
          if ($resolvedCodeSample !== null && $resolvedCodeSample !== '') {
            $sample->codesample_samples = $resolvedCodeSample;
          }


          if (isset($request->ispacket)) {
            $ispacket = $request->post('ispacket');
          }

          if (isset($packet)) {
            $sample->is_lengkap = 1;
          } else {
            $sample->is_lengkap = 0;
          }

          if (isset($request->jenis_sampel)) {
            $sample->typesample_samples = $request->post('jenis_sampel');
          }

          if (isset($request->gender_samples) || $request->gender_samples != null) {
            $sample->gender_samples = $request->post('gender_samples');
          } else {
            $sample->gender_samples = null;
          }


          if (isset($request->umur_samples) || $request->umur_samples != null) {
            $sample->umur_samples = $request->post('umur_samples');
          } else {
            $sample->umur_samples = null;
          }

          $sample->name_send_sample = $request->post('name_send_sample');
          $sample->code_sample_customer = $request->post('code_sample_customer');
          // $sample->count_id = $sample_urutan + 1;
          if (isset($request->cost_samples)) {
            $sample->cost_samples = $request->post('cost_samples');
          }

          $jenis_makanan_id = $request->post('jenis_makanan_id');

          if (isset($jenis_makanan_id) || $jenis_makanan_id != null) {
            $sample->jenis_makanan_id = $request->post('jenis_makanan_id');
          } else {
            $sample->jenis_makanan_id = null;
          }

          $sample->is_pudam  = $request->post('is_pudam')? 1:0;
          $sample->name_customer_pdam = $request->post('name_customer_pdam');
          $sample->address_location_pdam  = $request->post('address_location_pdam');

          if(isset($sample->codeKimiaMikro)){
            $sample2 = Sample::where('codeKimiaMikro', '=', $sample->codeKimiaMikro)->where('id_samples', '!=', $sample->id_samples)->first();
            if ($sample2) {
              $sample2->address_location_pdam = $request->post('address_location_pdam');
              $sample2->cost_sampling_samples = $request->post('cost_sampling') ?? 20000;
              $sample2->save();
            }
          }

          $datesampling_samples = Carbon::createFromFormat('d/m/Y H:i', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
          $date_sending = Carbon::createFromFormat('d/m/Y H:i', $request->post('date_sending'))->format('Y-m-d H:i:s');
          // // $date_done_estimation = Carbon::createFromFormat('d/m/Y', $request->post('date_done'))->format('Y-m-d H:i:s');
          $sample->datesampling_samples = $datesampling_samples;
          $sample->date_sending = $date_sending;
          // $sample->date_done_estimation=$date_done_estimation;
          $sample->note_samples = $request->post('note');
          $sample->titik_pengambilan = $request->post('titik_pengambilan');
          $sample->cost_sampling_samples = $request->post('cost_sampling') ?? 20000;

          if (!empty($sample->codesample_samples) && $sample->codesample_samples !== $old_code_sample) {
            $conflictResponse = $this->applyKesmasSampleCodeChangeOnUpdate(
              $sample,
              (string) ($old_code_sample ?? ''),
              (string) $sample->codesample_samples,
              $request
            );
            if ($conflictResponse !== null) {
              DB::rollBack();

              return $conflictResponse;
            }
          }

          $simpan_sample = $sample->save();

          // $methodsrequest = [];

          // if (isset($data["method"])) {
          //   for ($i = 0; $i < count($data["method"]); $i++) {
          //     $methodlab = explode("_", $data["method"][$i]);
          //     array_push($methodsrequest, $methodlab[0]);
          //   }

          //   $sample_methods = SampleMethod::whereIn('method_id', $methodsrequest)
          //     ->where('sample_id', $id_sample)
          //     ->select('method_id')
          //     ->get();

          //   $methodsnow = [];
          //   foreach ($sample_methods as $sample_method) {
          //     array_push($methodsnow, $sample_method->method_id);
          //   }

          //   $results = array_diff($methodsrequest, $methodsnow);

          //   foreach ($results as $key => $result) {
          //     // print_r($data["method"][$key]);
          //     $methodlab = explode("_", $data["method"][$key]);
          //     // array_push($methodsrequest,$methodlab[0]);
          //     $method = new SampleMethod;



          //     // $method->id_sample_method = Uuid::uuid4();
          //     $method->sample_id = $sample->id_samples;
          //     $method->method_id = $methodlab[0];
          //     $method->laboratorium_id = $methodlab[1];
          //     $method->price_method = $methodlab[2];

          //     $method->save();
          //     $baku_mutu = BakuMutu::where('method_id', '=',  $methodlab[0])
          //       ->where('lab_id', '=', $methodlab[1])
          //       ->where('sampletype_id', '=', $sample->typesample_samples)->first();




          //     if ($baku_mutu->is_sub == "1") {
          //       $method->is_sub = 1;
          //       $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=',  $methodlab[0])
          //         ->where('sampletype_id', '=', $sample->typesample_samples)
          //         ->where('baku_mutu_id', '=',  $baku_mutu->id_baku_mutu)->get();

          //       $sample_result_details = SampleResultDetail::where('method_id', '=',  $methodlab[0])
          //         ->where('sampletype_id', '=', $sample->typesample_samples)
          //         ->where('sample_id', '=', $sample->id_samples)
          //         ->where('lab_id', '=', $methodlab[1])
          //         ->get();

          //       if (count($sample_result_details) == 0) {
          //         foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {

          //           $sample_result_detail = new SampleResultDetail;
          //           // $sample_result_detail->id_sample_result_detail = Uuid::uuid4();
          //           $sample_result_detail->sample_id = $sample->id_samples;
          //           $sample_result_detail->method_id = $bakuMutuDetailParameterNonKlinik->method_id;
          //           $sample_result_detail->sampletype_id = $bakuMutuDetailParameterNonKlinik->sampletype_id;
          //           $sample_result_detail->lab_id  = $bakuMutuDetailParameterNonKlinik->lab_id;
          //           $sample_result_detail->name_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->min_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->max_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->equal_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->nilai_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->save();
          //         }
          //       } else {
          //         $sample_result_details = SampleResultDetail::where('method_id', '=',  $methodlab[0])
          //           ->where('sampletype_id', '=', $sample->typesample_samples)
          //           ->where('sample_id', '=', $sample->id_samples)
          //           ->where('lab_id', '=', $methodlab[1])->delete();
          //         foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {

          //           $sample_result_detail = new SampleResultDetail;
          //           // $sample_result_detail->id_sample_result_detail = Uuid::uuid4();
          //           $sample_result_detail->sample_id = $sample->id_samples;
          //           $sample_result_detail->method_id = $bakuMutuDetailParameterNonKlinik->method_id;
          //           $sample_result_detail->sampletype_id = $bakuMutuDetailParameterNonKlinik->sampletype_id;
          //           $sample_result_detail->lab_id  = $bakuMutuDetailParameterNonKlinik->lab_id;
          //           $sample_result_detail->name_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->min_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->max_sample_result_detail  = $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->equal_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->nilai_sample_result_detail   = $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik;
          //           $sample_result_detail->save();
          //         }
          //       }
          //     }
          //   }


          //   // dd( $results);



          //   $sample_method = SampleMethod::whereNotIn('method_id', $methodsrequest)
          //     ->where('sample_id', $id_sample)
          //     ->delete();
          // }

          $penerimaan_sample = PenerimaanSample::where('sample_id', $id_sample)->first();

          if (!$penerimaan_sample) {
            throw new \Exception('Data penerimaan sample tidak ditemukan. Hubungi administrator.');
          }

          // $penerimaan_sample->wadah_id = $request->post('wadah');
          // $wadah_samples = $request->post('wadah_samples');

          // if (isset($wadah_samples)) {
          //   $penerimaan_sample->wadah_sampel_other = $wadah_samples;
          // } else {
          //   $penerimaan_sample->wadah_sampel_other = null;
          // }

          // $penerimaan_sample->pengawet = $request->post('pengawet');

          // $pengawet_others_sample = $request->post('pengawet_others_sample');
          // if (isset($pengawet_others_sample)) {
          //   $penerimaan_sample->pengawet_other = $pengawet_others_sample;
          // } else {
          //   $penerimaan_sample->pengawet_other = null;
          // }
          // $penerimaan_sample->unit_id = $request->post('unit');
          // $penerimaan_sample->volume = $request->post('volume');
          // $penerimaan_sample->kondisi_sample = $request->post('kondisi_sample');
          // $penerimaan_sample->validation_sample  = $request->post('validation_sample');

          $penerimaan_sample->kelayakan_tempat_kemasan  = $request->post('kelayakan_tempat_kemasan');
          $penerimaan_sample->kelayakan_berat_vol  = $request->post('kelayakan_berat_vol');

          $simpan_penerimaan_sample = $penerimaan_sample->save();
        // }

        // Get lab_num if not already retrieved above
        if (!isset($lab_num)) {
          $lab_num = LabNum::where('sample_id', $id_sample)->first();
        }

        if ($lab_num && $sample->codesample_samples && !$sample->is_nomor_sampel_manual) {
          $labnum = explode("/", $sample->codesample_samples);

          // Only update if the exploded array has at least 2 elements
          if (isset($labnum[1])) {
            $lab_num->lab_number = (int)$labnum[1];

            // Also update mount_lab_num and year_lab_num from code sample
            if (count($labnum) >= 3) {
              $lab_num->mount_lab_num = (int)$labnum[1];
              $lab_num->year_lab_num = (int)$labnum[2];
            }

            // $this->sortingNumberKesmasByCode();
            $lab_num->save();
          }
        }

        // Check if codesample_samples has changed and update GlobalLabSequenceDetail
        $new_code_sample = $sample->codesample_samples;
        if ($old_code_sample !== $new_code_sample && !empty($new_code_sample) && $lab_num) {
          // Refresh sample to get latest codesample_samples value
          $sample->refresh();
          // This method will delete old LabNum and GlobalLabSequenceDetail, then create new ones
          $new_lab_num = $this->updateGlobalLabSequenceDetailForCodeChange($sample, $old_code_sample, $lab_num);
          // Update lab_num reference if new one was created
          if ($new_lab_num) {
            $lab_num = $new_lab_num;
          }
        }

        // Update total_harga on PermohonanUji
        $total_cost = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)->sum('cost_samples');
        $total_sampling_cost = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->where('is_sampling', 1)
            ->distinct('group_id')
            ->get(['group_id', 'cost_sampling_samples'])
            ->sum('cost_sampling_samples');
        
        $permohonan_uji = PermohonanUji::find($sample->permohonan_uji_id);
        if ($permohonan_uji) {
            $permohonan_uji->total_harga = $total_cost + $total_sampling_cost;
            $permohonan_uji->save();
        }

        DB::commit();

        if ($simpan_sample == true && $simpan_penerimaan_sample == true) {
          // $this->sortingNumberAll();
          return response()->json(['status' => true, 'pesan' => "Data sample berhasil disimpan!", 'url_redirect' => route('elits-samples.index', $sample->permohonan_uji_id), 'redirect' => route('elits-samples.index', $sample->permohonan_uji_id)], 200);
        } else {
          return response()->json(['status' => false, 'pesan' => "Data sample tidak berhasil disimpan!"], 200);
        }
      } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
      }
    }
  }
  private function updateSampleAndParameter($id, Request $request)
  {

    $sample = Sample::where('id_samples', $id)->first();
    $old_code_sample = (string) ($sample->codesample_samples ?? '');

    $paketAirHigiene = 'a0067d2a-6193-4225-9210-be6569d88a6c';
    $paketAirMinum = '04d4e517-73c0-4fab-809c-5ba0bac730d7';

    $packetRaw = $request->post('packet');
    $packet = null;
    if (is_array($packetRaw)) {
      foreach ($packetRaw as $pid) {
        if ($pid !== null && $pid !== '') {
          $packet = $pid;
          break;
        }
      }
    } else {
      $packet = $packetRaw;
    }

    $methods = $request->input('method', []);
    if (!is_array($methods)) {
      $methods = $methods !== null && $methods !== '' ? [$methods] : [];
    }

    $distinctMethod = [];

    foreach ($methods as $method){
      $parts = explode('_', $method);

      if (count($parts) > 1) {
        $groupKey = $parts[1];
        $distinctMethod[$groupKey][] = $method;
      }
    }

    $sample2 = null;

    $isPaket = $request->input('is_paket');
    $isPaketActive = in_array($isPaket, ['true', '1', 1, true], true);

    // Paket dari form; jika tidak dikirim (field disabled), pakai packet_id yang sudah tersimpan
    $effectivePacket = ($packet !== null && $packet !== '') ? $packet : $sample->packet_id;

    //  untuk menghandle jika bukan paket
    if (!$isPaketActive){
      $sample->packet_id = null;

      if (count($distinctMethod) > 1){
        // Gagal jika terdapat 2 lab (kimia dan mikro) hanya boleh salah satu
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }

      try {
        $method = array_first($distinctMethod);

        // Get lab_id from the first method to determine lab type
        $firstMethod = reset($distinctMethod);
        $firstMethodParts = explode('_', $firstMethod[0]);
        $labId = isset($firstMethodParts[1]) ? $firstMethodParts[1] : null;

        // Get lab information to determine if it's kimia or mikro
        $isKimiaLab = false;
        if ($labId) {
          $laboratorium = Laboratorium::find($labId);
          $isKimiaLab = $this->isKimiaLaboratorium($laboratorium);
        }

        $codeConflict = $this->resolveAndApplyKesmasCodeForParameterUpdate(
          $sample,
          $old_code_sample,
          $request
        );
        if ($codeConflict !== null) {
          DB::rollBack();
          return $codeConflict;
        }

        $this->storeUpdateSampleAndParameter($sample, $method, $request);
        DB::commit();
        return response()->json(['status' => true, 'pesan' => "Data sample berhasil disimpan!", 'url_redirect' => route('elits-samples.index', $sample->permohonan_uji_id), 'redirect' => route('elits-samples.index', $sample->permohonan_uji_id)], 200);
      }catch (\Exception $e){
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }
    }

    // cek paket apakah paket air higiene atau paket air minum

    if ($effectivePacket == $paketAirMinum or $effectivePacket == $paketAirHigiene){
      // jika sebelum edit paketnya paket air higiene atau paket air minum maka ambil sampel 2
      if ($sample->packet_id == $paketAirMinum or $sample->packet_id == $paketAirHigiene){
        $codeKimiaMikro = $sample->codeKimiaMikro;
        if (isset($codeKimiaMikro) and $codeKimiaMikro != ""){
          $sample2 = Sample::where('codeKimiaMikro', '=', $codeKimiaMikro)->where('id_samples','!=', $id)->first();
        } else {
          $sample2 = $this->findPairedKesmasSample($sample);
        }
        if (!$sample2) {
          throw new \Exception("Data sample tidak berhasil disimpan!");
        }
      }else{
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }
    }else{
      if ($sample->packet_id == $paketAirMinum or $sample->packet_id == $paketAirHigiene){
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }

      if (count($distinctMethod) > 1){
        // Gagal jika terdapat 2 lab (kimia dan mikro) hanya boleh salah satu
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }
    }

    if (isset($sample2)){
      $lab = SampleMethod::query()->where('sample_id', '=', $sample->id_samples)->first();

      if (!$lab) {
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }

      $labId = $lab->laboratorium_id;
      $method = $this->resolveMethodsForLabOnParameterUpdate($sample, $labId, $distinctMethod);
      if ($method === null || count($method) === 0) {
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }

      // Get lab information to determine if it's kimia or mikro
      $laboratorium = Laboratorium::find($labId);
      $isKimiaLab = $this->isKimiaLaboratorium($laboratorium);

      // Apply code once on primary sample; helper syncs paired kimia/mikro.
      $codeConflict = $this->resolveAndApplyKesmasCodeForParameterUpdate(
        $sample,
        $old_code_sample,
        $request
      );
      if ($codeConflict !== null) {
        DB::rollBack();
        return $codeConflict;
      }

      $this->storeUpdateSampleAndParameter($sample, $method, $request);

      $lab = SampleMethod::query()->where('sample_id', '=', $sample2->id_samples)->first();
      if (!$lab) {
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }
      $method = $this->resolveMethodsForLabOnParameterUpdate($sample2, $lab->laboratorium_id, $distinctMethod);
      if ($method === null || count($method) === 0) {
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }

      // Get lab information for sample2
      $laboratorium2 = Laboratorium::find($lab->laboratorium_id);
      $isKimiaLab2 = $this->isKimiaLaboratorium($laboratorium2);

      // Refresh pair after code sync (may already be updated by helper above)
      $sample2->refresh();

      $this->storeUpdateSampleAndParameter($sample2, $method, $request);

      DB::commit();
      return response()->json(['status' => true, 'pesan' => "Data sample berhasil disimpan!", 'url_redirect' => route('elits-samples.index', $sample->permohonan_uji_id), 'redirect' => route('elits-samples.index', $sample->permohonan_uji_id)], 200);

    }else{
      $lab = SampleMethod::query()->where('sample_id', '=', $sample->id_samples)->first();

      $labId = $lab ? $lab->laboratorium_id : null;
      try {
        $method = $this->resolveMethodsForLabOnParameterUpdate($sample, $labId, $distinctMethod);
        if ($method === null || count($method) === 0) {
          // Fallback: form mungkin hanya punya 1 group method
          $method = array_first($distinctMethod);
        }
        if ($method === null || count($method) === 0) {
          throw new \Exception("Data sample tidak berhasil disimpan!");
        }

        // Get lab information to determine if it's kimia or mikro
        $laboratorium = Laboratorium::find($labId);
        $isKimiaLab = $this->isKimiaLaboratorium($laboratorium);

        $codeConflict = $this->resolveAndApplyKesmasCodeForParameterUpdate(
          $sample,
          $old_code_sample,
          $request
        );
        if ($codeConflict !== null) {
          DB::rollBack();
          return $codeConflict;
        }

        $this->storeUpdateSampleAndParameter($sample, $method, $request);

        DB::commit();
        return response()->json(['status' => true, 'pesan' => "Data sample berhasil disimpan!", 'url_redirect' => route('elits-samples.index', $sample->permohonan_uji_id), 'redirect' => route('elits-samples.index', $sample->permohonan_uji_id)], 200);
      }catch (\Exception $exception){
        // Tidak bisa ganti dari kimia ke mikro atau sebaliknya karena nomor sample
        throw new \Exception("Data sample tidak berhasil disimpan!");
      }
    }

  }

  /**
   * Ambil daftar method (format methodId_labId_price) untuk lab tertentu.
   * Form edit sering hanya mengirim method lab sampel yang sedang dibuka;
   * pasangan kimia/mikro harus tetap bisa di-update tanpa Undefined index.
   *
   * @param  array<string, array<int, string>>  $distinctMethod
   * @return array<int, string>|null
   */
  private function resolveMethodsForLabOnParameterUpdate(Sample $sample, $labId, array $distinctMethod)
  {
    if ($labId !== null && $labId !== '' && isset($distinctMethod[$labId]) && is_array($distinctMethod[$labId])) {
      return $distinctMethod[$labId];
    }

    $existing = SampleMethod::query()
      ->where('sample_id', $sample->id_samples)
      ->when($labId, function ($q) use ($labId) {
        $q->where('laboratorium_id', $labId);
      })
      ->get();

    if ($existing->isEmpty()) {
      return null;
    }

    $methods = [];
    foreach ($existing as $row) {
      $methods[] = $row->method_id . '_' . $row->laboratorium_id . '_' . $row->price_method;
    }

    return $methods;
  }

  /**
   * Resolve kode dari form edit lalu apply (termasuk konflik + sync pasangan).
   *
   * @return \Illuminate\Http\JsonResponse|null
   */
  private function resolveAndApplyKesmasCodeForParameterUpdate(Sample $sample, string $oldCode, Request $request)
  {
    $resolvedCode = $this->resolveCodeSampleFromEditRequest($request, $sample, $oldCode);
    if ($resolvedCode === null || $resolvedCode === '') {
      return null;
    }

    if (trim($resolvedCode) === trim($oldCode)) {
      return null;
    }

    return $this->applyKesmasSampleCodeChangeOnUpdate($sample, $oldCode, $resolvedCode, $request);
  }

  private function storeUpdateSampleAndParameter(Sample $sample, $methods, Request $request)
  {

    $id_sample= $sample->id_samples;


    if (isset($request->name_pelanggan)) {
      $sample->name_pelanggan =  $request->name_pelanggan;
    }

    $packetRaw = $request->post('packet');
    $packet = null;
    if (is_array($packetRaw)) {
      foreach ($packetRaw as $pid) {
        if ($pid !== null && $pid !== '') {
          $packet = $pid;
          break;
        }
      }
    } else {
      $packet = $packetRaw;
    }

    if ($packet !== null && $packet !== '') {
      $sample->packet_id = $packet;
    }


    if (isset($request->jenis_sampel)) {
      $sample->typesample_samples = $request->post('jenis_sampel');
    }

    if (isset($request->gender_samples) || $request->gender_samples != null) {
      $sample->gender_samples = $request->post('gender_samples');
    } else {
      $sample->gender_samples = null;
    }


    if (isset($request->umur_samples) || $request->umur_samples != null) {
      $sample->umur_samples = $request->post('umur_samples');
    } else {
      $sample->umur_samples = null;
    }

    $sample->name_send_sample = $request->post('name_send_sample');
    $sample->code_sample_customer = $request->post('code_sample_customer');

    if (isset($request->cost_samples)) {
      $sample->cost_samples = $request->post('cost_samples');
    }

    $jenis_makanan_id = $request->post('jenis_makanan_id');

    if (isset($jenis_makanan_id) || $jenis_makanan_id != null) {
      $sample->jenis_makanan_id = $request->post('jenis_makanan_id');
    } else {
      $sample->jenis_makanan_id = null;
    }

    $sample->is_pudam  = $request->post('is_pudam')? 1:0;
    $sample->name_customer_pdam = $request->post('name_customer_pdam');
    $sample->address_location_pdam  = $request->post('address_location_pdam');

    $datesampling_samples = Carbon::createFromFormat('d/m/Y H:i', $request->post('datesampling_samples'))->format('Y-m-d H:i:s');
    $date_sending = Carbon::createFromFormat('d/m/Y H:i', $request->post('date_sending'))->format('Y-m-d H:i:s');
    // // $date_done_estimation = Carbon::createFromFormat('d/m/Y', $request->post('date_done'))->format('Y-m-d H:i:s');
    $sample->datesampling_samples = $datesampling_samples;
    $sample->date_sending = $date_sending;
    // $sample->date_done_estimation=$date_done_estimation;
    $sample->note_samples = $request->post('note');
    $sample->titik_pengambilan = $request->post('titik_pengambilan');
    $sample->cost_sampling_samples = $request->post('cost_sampling') ?? 20000;

    $simpan_sample = $sample->save();

    if(isset($sample->codeKimiaMikro)){
      $sample2 = Sample::where('codeKimiaMikro', '=', $sample->codeKimiaMikro)->where('id_samples', '!=', $sample->id_samples)->first();
      if ($sample2) {
        $sample2->cost_sampling_samples = $request->post('cost_sampling') ?? 20000;
        $sample2->save();
      }
    }


    //delete method sebelumnya

    SampleMethod::query()->where('sample_id', $id_sample)->delete();

    LabNum::query()->where('sample_id', $id_sample)->delete();
    // insert method baru


    if (isset($methods)) {
      foreach ($methods as $method) {
        $methodLab = explode("_", $method);


        $newMethod = new SampleMethod();
        $newMethod->sample_id = $sample->id_samples;
        $newMethod->method_id = $methodLab[0];
        $newMethod->laboratorium_id = $methodLab[1];
        $newMethod->price_method = $methodLab[2];

        $newMethod->save();

        $baku_mutu = BakuMutu::where('method_id', '=', $methodLab[0])
          ->where('lab_id', '=', $methodLab[1])
          ->where('sampletype_id', '=', $sample->typesample_samples)->first();

        if ($baku_mutu && $baku_mutu->is_sub == "1") {
          $newMethod->is_sub = 1;
          $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=', $methodLab[0])
            ->where('sampletype_id', '=', $sample->typesample_samples)
            ->where('baku_mutu_id', '=', $baku_mutu->id_baku_mutu)->get();

          $sample_result_details = SampleResultDetail::where('method_id', '=', $methodLab[0])
            ->where('sampletype_id', '=', $sample->typesample_samples)
            ->where('sample_id', '=', $sample->id_samples)
            ->where('lab_id', '=', $methodLab[1])
            ->get();

          if (count($sample_result_details) == 0) {
            foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {

              $sample_result_detail = new SampleResultDetail;
              // $sample_result_detail->id_sample_result_detail = Uuid::uuid4();
              $sample_result_detail->sample_id = $sample->id_samples;
              $sample_result_detail->method_id = $bakuMutuDetailParameterNonKlinik->method_id;
              $sample_result_detail->sampletype_id = $bakuMutuDetailParameterNonKlinik->sampletype_id;
              $sample_result_detail->lab_id = $bakuMutuDetailParameterNonKlinik->lab_id;
              $sample_result_detail->name_sample_result_detail = $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->min_sample_result_detail = $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->max_sample_result_detail = $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->equal_sample_result_detail = $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->nilai_sample_result_detail = $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->save();
            }
          } else {
            SampleResultDetail::where('method_id', '=', $methodLab[0])
              ->where('sampletype_id', '=', $sample->typesample_samples)
              ->where('sample_id', '=', $sample->id_samples)
              ->where('lab_id', '=', $methodLab[1])->delete();

            foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {

              $sample_result_detail = new SampleResultDetail;
              $sample_result_detail->sample_id = $sample->id_samples;
              $sample_result_detail->method_id = $bakuMutuDetailParameterNonKlinik->method_id;
              $sample_result_detail->sampletype_id = $bakuMutuDetailParameterNonKlinik->sampletype_id;
              $sample_result_detail->lab_id = $bakuMutuDetailParameterNonKlinik->lab_id;
              $sample_result_detail->name_sample_result_detail = $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->min_sample_result_detail = $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->max_sample_result_detail = $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->equal_sample_result_detail = $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->nilai_sample_result_detail = $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik;
              $sample_result_detail->save();
            }
          }


        }

        $lab_num = LabNum::where('sample_id', $sample->id_samples);
        $lab_num->delete();

        $newLab = new LabNum();

        $newLab->sample_id = $sample->id_samples;
        $newLab->sample_type_id = $sample->typesample_samples;
        $newLab->permohonan_uji_id = $sample->permohonan_uji_id;
        $newLab->lab_id = $methodLab[1];
        if (isset($sample->jenis_makanan_id)){
          $newLab->is_makanan = 1;
        }else{
          $newLab->is_makanan = 0;
        }
        $newLab->lab_number = $sample->count_id;

        if ($sample->is_nomor_sampel_manual) {
          $newLab->mount_lab_num = Carbon::parse($sample->date_sending)->format('m');
          $partsCs = explode('/', (string) $sample->codesample_samples);
          $newLab->year_lab_num = count($partsCs) >= 3 ? (int) end($partsCs) : (int) Carbon::parse($sample->date_sending)->format('Y');
        } else {
          $codeSample = explode('/', $sample->codesample_samples);
          $newLab->mount_lab_num = $codeSample[count($codeSample) - 2];
          $newLab->year_lab_num = end($codeSample);
        }

        $newLab->save();
      }
    }

    $penerimaan_sample = PenerimaanSample::where('sample_id', $id_sample)->first();

    if ($penerimaan_sample) {
      $penerimaan_sample->kelayakan_tempat_kemasan = $request->post('kelayakan_tempat_kemasan');
      $penerimaan_sample->kelayakan_berat_vol = $request->post('kelayakan_berat_vol');
      $penerimaan_sample->save();
    }
  }

  /**
   * Update GlobalLabSequenceDetail when codesample_samples changes
   *
   * @param Sample $sample The sample being updated
   * @param string $old_code_sample The old code sample value
   * @param LabNum $lab_num The LabNum record associated with the sample
   * @return LabNum|null The new LabNum record, or null if failed
   */
  private function updateGlobalLabSequenceDetailForCodeChange($sample, $old_code_sample, $lab_num)
  {
    try {
      $new_code_sample = $sample->codesample_samples;

      // Parse new code sample: Format is typically "CODE.LAB/NUMBER/YEAR" or "CODE/NUMBER/YEAR"
      // Example: "AM.01/0013/2025" or "AM/0013/2025"
      $code_parts = explode('/', $new_code_sample);

      if (count($code_parts) < 3) {
        \Log::warning('Invalid code sample format for GlobalLabSequenceDetail update: ' . $new_code_sample);
        return;
      }

      // Extract sequence number (second part) and year (third part)
      $sequence_number = (int) $code_parts[1];
      $year = (int) $code_parts[2];

      // Extract lab_id from first part if it contains lab code (e.g., "AM.01" -> lab_id from "01")
      $first_part = $code_parts[0];
      $lab_id = $lab_num->lab_id; // Use lab_id from LabNum as primary source

      // Parse old code sample to get old sequence details
      $old_sequence_number = null;
      $old_year = null;
      if (!empty($old_code_sample)) {
        $old_code_parts = explode('/', $old_code_sample);
        if (count($old_code_parts) >= 3) {
          $old_sequence_number = (int) $old_code_parts[1];
          $old_year = (int) $old_code_parts[2];
        }
      }

      // Store old LabNum ID before deletion
      $old_lab_num_id = $lab_num->id_lab_num;

      // DELETE old GlobalLabSequenceDetail that was linked to this LabNum
      $old_sequence_details = GlobalLabSequenceDetail::where('reference_id', $old_lab_num_id)
        ->whereNull('deleted_at')
        ->get();

      foreach ($old_sequence_details as $old_detail) {
        $old_detail->delete(); // Soft delete
      }

      // Also delete old GlobalLabSequenceDetail based on old code sample if different
      if ($old_sequence_number !== null && $old_year !== null) {
        $old_details_by_code = GlobalLabSequenceDetail::where('sequence_number', $old_sequence_number)
          ->where('year', $old_year)
          ->where('lab_id', $lab_id)
          ->where('reference_id', $old_lab_num_id)
          ->whereNull('deleted_at')
          ->get();

        foreach ($old_details_by_code as $old_detail) {
          $old_detail->delete(); // Soft delete
        }
      }

      // DELETE old LabNum and create new one
      $lab_num->delete(); // Soft delete old LabNum

      // Create new LabNum with updated values
      $new_lab_num = new LabNum();
      $new_lab_num->sample_id = $sample->id_samples;
      $new_lab_num->sample_type_id = $sample->typesample_samples;
      $new_lab_num->permohonan_uji_id = $sample->permohonan_uji_id;
      $new_lab_num->lab_id = $lab_id;
      $new_lab_num->is_makanan = isset($sample->jenis_makanan_id) ? 1 : 0;
      $new_lab_num->lab_number = $sequence_number;
      $new_lab_num->mount_lab_num = $sequence_number;
      $new_lab_num->year_lab_num = $year;
      $new_lab_num->save();

      // Check if GlobalLabSequenceDetail already exists for this sequence number, year, and lab_id
      $existing_sequence_detail = GlobalLabSequenceDetail::where('sequence_number', $sequence_number)
        ->where('year', $year)
        ->where('lab_id', $lab_id)
        ->whereNull('deleted_at')
        ->first();

      if ($existing_sequence_detail) {
        // Update existing record to point to new LabNum
        $existing_sequence_detail->reference_id = $new_lab_num->id_lab_num;
        $existing_sequence_detail->save();
      } else {
        // Create new GlobalLabSequenceDetail record
        $new_sequence_detail = GlobalLabSequenceDetail::create([
          'year' => $year,
          'sequence_number' => $sequence_number,
          'lab_id' => $lab_id,
          'lab_type' => 'lab', // Default to 'lab' type
          'reference_id' => $new_lab_num->id_lab_num,
        ]);

        if (!$new_sequence_detail || !$new_sequence_detail->id) {
          \Log::error('Failed to create GlobalLabSequenceDetail for code change: ' . $new_code_sample);
          throw new \Exception('Failed to create GlobalLabSequenceDetail record');
        }
      }

      // Update sample count_id to match sequence number
      $sample->count_id = $sequence_number;
      $sample->save();

      // Return new LabNum for reference update
      return $new_lab_num;

    } catch (\Exception $e) {
      \Log::error('Error updating GlobalLabSequenceDetail for code change: ' . $e->getMessage());
      // Don't throw exception to prevent transaction rollback, just log the error
      return null;
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
    $sample = Sample::where('id_samples', $id)->first();
    $permohonanUjiId = $sample ? $sample->permohonan_uji_id : null;
    $year = $sample && $sample->created_at
      ? (int) \Carbon\Carbon::parse($sample->created_at)->year
      : (int) date('Y');

    $labNums = LabNum::where('sample_id', $id)->get();
    foreach ($labNums as $ln) {
      if ($ln->id_lab_num) {
        \Smt\Masterweb\Models\GlobalLabSequence::deleteByLabNumId($ln->id_lab_num);
      }
    }

    Sample::where('id_samples', $id)->delete();
    LabNum::where('sample_id', $id)->delete();
    \Smt\Masterweb\Models\GlobalLabSequence::syncAfterSampleChange($year);

    if ($permohonanUjiId) {
      return redirect()->route('elits-samples.index', [$permohonanUjiId])->with(['status' => 'Sampel berhasil dihapus']);
    }

    return redirect()->route('elits-permohonan-uji.index')->with(['status' => 'Sampel berhasil dihapus']);
  }
  public function sortingNumberAll()
  {
      $result = \Smt\Masterweb\Models\GlobalLabSequence::resequenceAutoOnlyForYear((int) date('Y'));

      return redirect()
          ->route('elits-permohonan-uji.index')
          ->with([
            'status' => 'Nomor otomatis berhasil diurutkan ('.$result['auto'].' record). Nomor manual dilewati ('.$result['manual'].' record).',
          ]);
  }

  /**
   * Menjalankan normalisasi nomor/global sequence.
   * Nomor MANUAL (sampel/lab kesmas & lab/spesimen klinik) tidak diubah.
   */
  protected function applyGlobalLabSortingAndSyncScripts(): void
  {
      \Smt\Masterweb\Models\GlobalLabSequence::resequenceAutoOnlyForYear((int) date('Y'));
  }


  /**
   * Panel: Daftar semua sample (Nomor Sample, Nama Pelanggan, Detail Sample, Lab)
   */
  public function allSamples(\Illuminate\Http\Request $request)
  {
    $type = strtolower($request->get('type', 'all'));
    $search = trim((string)$request->get('search'));
    $perPage = 25;

    if ($type === 'klinik') {
      $klinikQuery = $this->buildKlinikSamplesQuery($search);

      $samples = \DB::query()
        ->fromSub($klinikQuery, 'samples_union')
        ->orderByRaw('YEAR(created_at) DESC')
        ->orderBy('global_seq', 'desc')
        ->paginate($perPage)
        ->appends($request->except('page'));
    } elseif ($type === 'all') {
      $labQuery = $this->buildLabSamplesQuery($search, $type);
      $klinikQuery = $this->buildKlinikSamplesQuery($search);

      $labQuery->unionAll($klinikQuery);

      $samples = \DB::query()
        ->fromSub($labQuery, 'samples_union')
        ->orderByRaw('YEAR(created_at) DESC')
        ->orderBy('global_seq', 'desc')
        ->paginate($perPage)
        ->appends($request->except('page'));
    } else {
      // Untuk filter lab tertentu (mis. mikrobiologi), bungkus juga sebagai subquery
      // supaya alias kolom `created_at` hasil SELECT tidak bentrok dengan kolom bawaan tabel.
      $labQuery = $this->buildLabSamplesQuery($search, $type);

      $samples = \DB::query()
        ->fromSub($labQuery, 'samples_union')
        ->orderByRaw('YEAR(created_at) DESC')
        ->orderBy('global_seq', 'desc')
        ->paginate($perPage)
        ->appends($request->except('page'));
    }

    return view('masterweb::module.admin.laboratorium.sample.all', compact('samples'));
  }

  private function buildLabSamplesQuery($search = null, $labFilter = null)
  {
    $query = \DB::table('global_lab_sequence_detail as gsd')
      ->join('tb_lab_num as ln', function($join){
        $join->on('ln.id_lab_num', '=', 'gsd.reference_id')
          ->whereNull('ln.deleted_at');
      })
      ->join('tb_samples as s', function($join){
        $join->on('s.id_samples', '=', 'ln.sample_id')
          ->whereNull('s.deleted_at');
      })
      ->leftJoin('ms_laboratorium as lab', function($join){
        $join->on('lab.id_laboratorium', '=', 'ln.lab_id')
          ->whereNull('lab.deleted_at');
      })
      ->leftJoin('tb_nomer_lab_kesmas as nlk', function($join){
        $join->on('nlk.permohonan_uji_id', '=', 'ln.permohonan_uji_id')
             ->on('nlk.laboratorium_id', '=', 'ln.lab_id');
      })
      ->select([
        's.id_samples as id_samples',
        's.codesample_samples as codesample_samples',
        's.name_pelanggan as name_pelanggan',
        's.titik_pengambilan as titik_pengambilan',
        \DB::raw("COALESCE(NULLIF(s.titik_pengambilan,''), '-') as detail_sample"),
        'lab.nama_laboratorium as lab_name',
        'gsd.sequence_number as global_seq',
        'gsd.lab_type as lab_type',
        \DB::raw("COALESCE(s.created_at, gsd.created_at) as created_at"),
        'nlk.nomer_lab as nomer_lab',  // per-lab nomer, bukan per-permohonan_uji
      ])
      ->where('gsd.lab_type', 'lab')
      ->whereNull('gsd.deleted_at');

    if (!empty($search)) {
      $query->where(function($q) use ($search){
        $q->where('s.codesample_samples','like','%'.$search.'%')
          ->orWhere('s.name_pelanggan','like','%'.$search.'%')
          ->orWhere('s.titik_pengambilan','like','%'.$search.'%')
          ->orWhere('s.jenis_sarana_names','like','%'.$search.'%')
          ->orWhere('s.note_samples','like','%'.$search.'%')
          ->orWhere('lab.nama_laboratorium','like','%'.$search.'%');
      });
    }

    if (in_array($labFilter, ['kimia','mikrobiologi','mikro'])) {
      $label = $labFilter === 'mikro' ? 'Mikrobiologi' : ucfirst($labFilter);
      $query->where('lab.nama_laboratorium', 'like', $label.'%');
    }

    return $query;
  }

  private function buildKlinikSamplesQuery($search = null)
  {
    $query = \DB::table('global_lab_sequence_detail as gsd')
      ->join('tb_permohonan_uji_klinik_2 as p', function($join){
        $join->on('p.id_permohonan_uji_klinik', '=', 'gsd.reference_id')
          ->whereNull('p.deleted_at');
      })
      ->leftJoin('tb_number_klinik as nk', function($join){
        $join->on('nk.id_permohonan_uji_klinik', '=', 'p.id_permohonan_uji_klinik')
          ->whereNull('nk.deleted_at');
      })
      ->leftJoin('ms_pasien as pasien', 'pasien.id_pasien', '=', 'p.pasien_permohonan_uji_klinik')
      ->select([
        'p.id_permohonan_uji_klinik as id_samples',
        \DB::raw("COALESCE(NULLIF(nk.new_number,''), p.noregister_permohonan_uji_klinik) as codesample_samples"),
        'pasien.nama_pasien as name_pelanggan',
        \DB::raw("NULL as titik_pengambilan"),
        \DB::raw("COALESCE(p.jenis_spesimen, '-') as detail_sample"),
        \DB::raw("'Klinik' as lab_name"),
        'gsd.sequence_number as global_seq',
        'gsd.lab_type as lab_type',
        \DB::raw("COALESCE(p.created_at, gsd.created_at) as created_at"),
        'p.nomer_lab as nomer_lab',
      ])
      ->where('gsd.lab_type', 'klinik')
      ->whereNull('gsd.deleted_at');

    if (!empty($search)) {
      $query->where(function($q) use ($search){
        $q->where('nk.new_number','like','%'.$search.'%')
          ->orWhere('p.noregister_permohonan_uji_klinik','like','%'.$search.'%')
          ->orWhere('pasien.nama_pasien','like','%'.$search.'%')
          ->orWhere('p.jenis_spesimen','like','%'.$search.'%');
      });
    }

    return $query;
  }

  public function sortingNumberBylabAndPlusCount($labId, $plusCount)
  {
    $this->sortingNumber($labId, $plusCount);
    dd("Success");
  }

  public function sortingNumberKesmasByCode(){
    // Tidak mengurut ulang kode manual — hanya sync lab_number dari codesample untuk non-manual
    DB::statement("
    UPDATE tb_lab_num
    JOIN tb_samples ON tb_lab_num.sample_id = tb_samples.id_samples
    SET tb_lab_num.lab_number = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 2), '/', -1) AS UNSIGNED),
    tb_samples.count_id = SUBSTRING_INDEX(SUBSTRING_INDEX(tb_samples.codesample_samples, '/', 2), '/', -1)
    WHERE YEAR(tb_lab_num.created_at) = YEAR(CURDATE())
      AND tb_lab_num.deleted_at IS NULL
      AND tb_samples.deleted_at IS NULL
      AND (tb_samples.is_nomor_sampel_manual = 0 OR tb_samples.is_nomor_sampel_manual IS NULL)
      AND (tb_samples.is_nomor_laboratorium_manual = 0 OR tb_samples.is_nomor_laboratorium_manual IS NULL);
    ");
  }

  public function sortingNumberKesmasByCodeAll(){
    $this->sortingNumberKesmasByCode();
    return redirect()
        ->route('elits-permohonan-uji.index')
        ->with(['status' => 'Sinkron kode sampel otomatis selesai (nomor manual dilewati).']);
  }
  public function sortingNumber($lab_id, $plus_number=0){
    // Gunakan sorter global yang melewati nomor manual
    \Smt\Masterweb\Models\GlobalLabSequence::resequenceAutoOnlyForYear((int) date('Y'));
  }

  public function sample_destroy($id)
  {
    $hapus = false;

    try {
      DB::beginTransaction();

      $sample = Sample::where('id_samples', $id)->first();
      if (!$sample) {
        DB::rollBack();
        return response()->json(['status' => false, 'pesan' => 'Data sample tidak ditemukan atau sudah dihapus.'], 200);
      }

      $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $sample->permohonan_uji_id)->first();

      // Subtract both cost_samples and cost_sampling_samples (if applicable)
      $totalToSubtract = (float) ($sample->cost_samples ?? 0);
      if ((int) $sample->is_sampling === 1) {
        $totalToSubtract += (float) ($sample->cost_sampling_samples ?? 0);
      }
      if ($permohonan_uji) {
        $permohonan_uji->total_harga = max(0, (float) $permohonan_uji->total_harga - $totalToSubtract);
        $permohonan_uji->save();
      }

      // Get all lab_num for this sample before deletion
      $labNums = LabNum::where('sample_id', $id)->get();
      $year = $sample->created_at
        ? (int) Carbon::parse($sample->created_at)->year
        : (int) date('Y');

      // Delete global_lab_sequence_detail for all lab_num of this sample
      // This must be done before deleting lab_num records
      foreach ($labNums as $ln) {
        if ($ln->id_lab_num) {
          \Smt\Masterweb\Models\GlobalLabSequence::deleteByLabNumId($ln->id_lab_num);
        }
      }

      LabNum::where('sample_id', $id)->delete();

      $hapus = (bool) $sample->delete();

      DB::commit();

      // Sync counter dari max nomor sampel/lab yang masih hidup
      \Smt\Masterweb\Models\GlobalLabSequence::syncAfterSampleChange($year);
    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('sample_destroy failed: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);

      return response()->json([
        'status' => false,
        'pesan' => 'Gagal menghapus sample: ' . $e->getMessage(),
      ], 200);
    }

    if ($hapus) {
      return response()->json(['status' => true, 'pesan' => 'Data sample berhasil dihapus!'], 200);
    }

    return response()->json(['status' => false, 'pesan' => 'Data sample tidak berhasil dihapus!'], 200);
  }
  public function printMikro(Request $request, $id_permohonan_uji, $sample_type_id = null, $packet_id = null)
  {

    $jenis_makanan_id = $request->jenis_makanan_id;
    // Hanya true jika user memfilter jenis makanan di URL/request (bukan dari loop sample)
    $jenisMakananFilterFromRequest = $request->filled('jenis_makanan_id');

    // Air Higiene / Air Bersih + Air Minum sering dicetak gabungan; URL lama bisa
    // memakai UUID AB padahal sampel yang ada hanya AM (atau sebaliknya).
    $mbiAbUuid = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';
    $mbiAmUuid = '65df8403-b29f-4645-a1ed-12d2aeff1fbd';
    $mbiAbAmTypeIds = [$mbiAbUuid, $mbiAmUuid];
    $isMbiAbAmType = $sample_type_id && in_array($sample_type_id, $mbiAbAmTypeIds, true);

    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id_permohonan_uji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->first();


    $sample_type = SampleType::findOrFail($sample_type_id);

    $is_landing = false;
    $lab_num_max = LabNum::where('tb_lab_num.permohonan_uji_id', $id_permohonan_uji)
      ->join('tb_samples', function ($join) {
        $join->on('tb_lab_num.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_samples.deleted_at')
          ->whereNull('tb_lab_num.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_lab_num.lab_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_lab_num.deleted_at');
      })

      ->where('kode_laboratorium', "MBI")
      ->where('sample_type_id', $sample_type_id)
      ->orderBy('lab_number', 'desc')
      ->first();

    $lab_num_min = LabNum::where('tb_lab_num.permohonan_uji_id', $id_permohonan_uji)
      ->join('tb_samples', function ($join) {
        $join->on('tb_lab_num.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_samples.deleted_at')
          ->whereNull('tb_lab_num.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_lab_num.lab_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_lab_num.deleted_at');
      })
      ->where('kode_laboratorium', "MBI")
      ->where('sample_type_id', $sample_type_id)
      ->orderBy('lab_number', 'asc')
      ->first();

    if (isset($lab_num_max->lab_number) && isset($lab_num_min->lab_number)) {
      if ((int)$lab_num_max->lab_number == (int)$lab_num_min->lab_number) {
        $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number);
      } else {
        $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number) . "-" . sprintf("%04d", (int)$lab_num_max->lab_number);
      }

      ///mendapatkan format belakang strip
      $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->where('tb_samples.typesample_samples', $sample_type_id)
        // ->where('tb_samples.packet_id', $packet_id)
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.created_at', 'asc')
        ->first();

      // dd(strstr($sample->codesample_samples, '/'));

      $lab_string = $lab_num . strstr($sample->codesample_samples, '/');
    } else {
      $lab_string = null;
      $lab_num = null;
    }

    $lab_string = $lab_string;




    // $lab_num=$lab_num_min."-".$lab_num_max;




    // $lab_num = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
    // ->where('sample_type_id', $sample_type_id)
    // ->get();

    // $sort_lab_num = array_column((array)$lab_num, 'lab_number');
    // $lab_num_min = min($sort_lab_num);
    // $lab_num_max = max($sort_lab_num);



    $sample_type = SampleType::where('id_sample_type', $sample_type_id)->first();
    $allSamplesQuery = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->where('ms_laboratorium.kode_laboratorium', 'MBI')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      });

    if (isset($sample_type_id)) {
      $allSamplesQuery->where('tb_samples.typesample_samples', $sample_type_id);
    }
    if (isset($packet_id)) {
      $allSamplesQuery->where('tb_samples.packet_id', $packet_id);
    }
    if (isset($jenis_makanan_id)) {
      $allSamplesQuery->where('tb_samples.jenis_makanan_id', $jenis_makanan_id);
    }

    $all_samples = $allSamplesQuery
      ->select('tb_samples.*', 'ms_sample_type.*')
      ->distinct('tb_samples.id_samples')
      ->orderBy('tb_samples.count_id', 'asc')
      ->get();



    // Loop $all_samples untuk mendapatkan semua jenis makanan yang berbeda
    // Termasuk yang tanpa jenis makanan (null)
    $all_jenis_makanan_ids = [];
    $has_null_jenis_makanan = false;

    if (isset($all_samples) && $all_samples->count() > 0) {
      foreach ($all_samples as $sample) {
        if (isset($sample->jenis_makanan_id) && $sample->jenis_makanan_id !== null) {
          if (!in_array($sample->jenis_makanan_id, $all_jenis_makanan_ids)) {
            $all_jenis_makanan_ids[] = $sample->jenis_makanan_id;
          }
        } else {
          $has_null_jenis_makanan = true;
        }
      }
    }

    // Untuk jenis sampel makanan, gunakan jenis_makanan_id dari request (bisa kosong)
    // Untuk jenis sampel lain, juga gunakan dari request untuk konsistensi
    $jenis_makanan_id = $request->jenis_makanan_id;

    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {


          // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
          // Tanpa filter jenis makanan di level sample agar semua method terambil
          $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id, $packet_id);

          // Batch attach baku mutu + unit metadata
          $method_all = $this->attachBakuMutuToMethods(
            $method_all_raw,
            $sample_type_id,
            $all_jenis_makanan_ids,
            $has_null_jenis_makanan
          );
        } else {
          // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
          // Tanpa filter jenis makanan di level sample agar semua method terambil
          $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id);

          // Batch attach baku mutu + unit metadata
          $method_all = $this->attachBakuMutuToMethods(
            $method_all_raw,
            $sample_type_id,
            $all_jenis_makanan_ids,
            $has_null_jenis_makanan
          );
        }
      } else {
        if ($sample_type_id == "d34b4a50-4560-4fce-96c3-046c7080a986") {

          if (isset($packet_id)) {
            // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
            $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id, $packet_id);

            // Batch attach baku mutu + unit metadata
            $method_all = $this->attachBakuMutuToMethods(
              $method_all_raw,
              $sample_type_id,
              $all_jenis_makanan_ids,
              $has_null_jenis_makanan
            );
          } else {
            // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
            $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id);

            // Batch attach baku mutu + unit metadata
            $method_all = $this->attachBakuMutuToMethods(
              $method_all_raw,
              $sample_type_id,
              $all_jenis_makanan_ids,
              $has_null_jenis_makanan
            );
          }

        } else {

          if (isset($packet_id)) {
            // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
            $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id, $packet_id);

            // Batch attach baku mutu + unit metadata (non-makanan)
            $method_all = $this->attachBakuMutuToMethods(
              $method_all_raw,
              $sample_type_id,
              [],
              false,
              true
            );
          } else {
            // Ambil semua method dari semua sample (dengan dan tanpa jenis makanan)
            $method_all_raw = $this->fetchMethodAllRawMbi($id_permohonan_uji, $sample_type_id);

            // Batch attach baku mutu + unit metadata (non-makanan)
            $method_all = $this->attachBakuMutuToMethods(
              $method_all_raw,
              $sample_type_id,
              [],
              false,
              true
            );
          }
        }
      }


      //pengurutan order list
      $sample_type_details = SampleTypeDetail::where('sample_type_id', $sample_type_id)->orderBy('orderlist_sample_type_detail')->get();
      if ($sample_type_details->count() > 0 && isset($method_all) && collect($method_all)->count() > 0) {
        $orderMap = [];
        foreach ($sample_type_details as $sample_type_detail) {
          $orderMap[$sample_type_detail->method_id] = $sample_type_detail->orderlist_sample_type_detail;
        }
        $method_all = collect($method_all)->sortBy(function ($method) use ($orderMap) {
          return $orderMap[$method->id_method] ?? 9999;
        })->values();
      }








    } else {
      $method_all = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)

        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at')
            ->whereNull('tb_sample_method.deleted_at')
            ->join('tb_baku_mutu', function ($join) {
              $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                // ->where('tb_baku_mutu.sampletype_id', '=',$sample_type_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->join('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            });
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        // ->join('tb_baku_mutu', function ($join) {
        //   $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
        //     ->where('tb_baku_mutu.sampletype_id', '=','tb_samples.typesample_samples')
        //     ->where('tb_baku_mutu.jenis_makanan_id', '=','tb_samples.jenis_makanan_id')
        //     ->whereNull('tb_baku_mutu.deleted_at')
        //     ->whereNull('ms_method.deleted_at');
        // })


        ->select('ms_method.*', 'unit_baku_mutu.*', 'tb_baku_mutu.*')
        ->distinct('ms_method.id_method')
        ->get();
    }


    $table = [];

    // Prefetch lokasi_selected pertama per sample agar tidak query per-loop.
    $sampleIdsForTable = collect($all_samples ?? [])->pluck('id_samples')->filter()->unique()->values()->all();
    $firstLokasiBySampleId = [];
    if (!empty($sampleIdsForTable)) {
      $firstLokasiRows = SampleResult::whereIn('sample_id', $sampleIdsForTable)
        ->whereNull('deleted_at')
        ->whereNotNull('lokasi_selected')
        ->orderBy('created_at', 'asc')
        ->get(['sample_id', 'lokasi_selected']);
      foreach ($firstLokasiRows as $row) {
        if (!isset($firstLokasiBySampleId[$row->sample_id])) {
          $firstLokasiBySampleId[$row->sample_id] = $row->lokasi_selected;
        }
      }
    }

    // Cache in-memory untuk pemilihan baku mutu dan unit per kombinasi berulang.
    $bakuMutuByMethodJenisCache = [];
    $unitByIdCache = [];



    if ($sample_type_id != "6cbc3684-6166-4723-b5ba-827fb8727097") {
      foreach ($all_samples as $sample) {
        $sample_one = [];
        $sample_one["sample_type"] = $sample;
        $sample_one["result"] = [];

        // Ambil lokasi_selected pertama dari hasil prefetch (untuk Kualitas Udara)
        $selectedRuangan = $firstLokasiBySampleId[$sample->id_samples] ?? null;
        if ($selectedRuangan) {
          $sample_one["sample_type"]->selected_ruangan = $selectedRuangan;
        }


        if ($sample->jenis_makanan_id !='' && $sample->jenis_makanan_id !=null) {
          # code...
          $jenis_makanan_id = $sample->jenis_makanan_id;
        }


        foreach ($method_all as $method) {

          // Makanan/Minuman/Lainnya: jangan pakai JOIN baku mutu + jenis eksak (sering gagal jika BM generik/null).
          // Selalu pakai cabang terpisah: SampleResult + query BakuMutu fleksibel di bawah.
          if (isset($jenis_makanan_id) && $sample_type_id !== 'd34b4a50-4560-4fce-96c3-046c7080a986') {
            $id_method = $method->id_method;
            $result_one = SampleResult::where('tb_sample_result.sample_id', '=', $sample->id_samples)
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->where('tb_sample_result.method_id', $method->id_method)
              ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_samples', function ($join) {
                $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
                  ->whereNull('tb_samples.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_sample_method', function ($join) use ($id_method) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                  ->where('tb_sample_method.method_id', $id_method)
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })
              ->leftJoin('ms_sample_type', function ($join) {
                $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                  ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                  ->whereNull('ms_sample_type.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })
              ->leftjoin('tb_sample_analitik_progress', function ($join) {
                $join->on('tb_sample_analitik_progress.sample_id', '=', 'tb_sample_result.sample_id')
                  ->whereNull('tb_sample_analitik_progress.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at')
                  ->where('tb_sample_analitik_progress.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                  ->where('tb_sample_analitik_progress.laboratorium_progress_id', 'bc2850f5-4ec4-450f-a727-2b1428c861d9');
              })
              ->join('ms_method', function ($join) use ($id_method, $sample_type_id, $jenis_makanan_id) {
                $join->where('ms_method.id_method', '=', $id_method)
                  ->whereNull('tb_sample_result.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->join('tb_baku_mutu', function ($join) use ($sample_type_id, $id_method, $jenis_makanan_id) {
                    $join
                      ->where('tb_baku_mutu.method_id', '=', $id_method)
                      ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                      ->where('tb_baku_mutu.sampletype_id', '=', $sample_type_id)
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at');
                  })
                  ->join('ms_unit as unit_baku_mutu', function ($join) {
                    $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                      ->whereNull('unit_baku_mutu.deleted_at')
                      ->whereNull('tb_baku_mutu.deleted_at');
                  });
              })

              ->orderBy('tb_sample_result.created_at', 'asc')
              ->first();

          } else {

            $id_method = $method->id_method;
            $jenis_makanan_id_sample = $sample->jenis_makanan_id;


             if ($sample_type_id == "d34b4a50-4560-4fce-96c3-046c7080a986") {
              // UNTUK JENIS SAMPEL MAKANAN: Pisahkan query hasil dan baku mutu

              // 1. Ambil hasil pemeriksaan dengan query yang sederhana
              $result_one = \Smt\Masterweb\Models\SampleResult::where('sample_id', $sample->id_samples)
                ->where('method_id', $method->id_method)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->first();

              // 2. Cari baku mutu secara terpisah dengan prioritas:
              //    a) jenis_makanan_id spesifik sample
              //    b) fallback baku mutu generic (jenis_makanan_id = null)
              $bmCacheKey = $id_method . '|' . $sample_type_id . '|' . ($jenis_makanan_id_sample ?: 'null');
              if (!array_key_exists($bmCacheKey, $bakuMutuByMethodJenisCache)) {
                $baku_mutu_query = \Smt\Masterweb\Models\BakuMutu::where('method_id', $id_method)
                  ->where('sampletype_id', $sample_type_id)
                  ->whereNull('deleted_at');

                if ($jenis_makanan_id_sample) {
                  $baku_mutu_query
                    ->where(function ($q) use ($jenis_makanan_id_sample) {
                      $q->where('jenis_makanan_id', $jenis_makanan_id_sample)
                        ->orWhereNull('jenis_makanan_id');
                    })
                    ->orderByRaw('CASE WHEN jenis_makanan_id = ? THEN 0 ELSE 1 END', [$jenis_makanan_id_sample]);
                } else {
                  // Sampel tanpa jenis_makanan (mis. parameter satuan / tambahan): BM di master bisa
                  // generik (NULL) atau terikat jenis — jangan batasi hanya NULL agar satuan_bakumutu tampil.
                  $baku_mutu_query->orderByRaw('CASE WHEN jenis_makanan_id IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('jenis_makanan_id');
                }

                $bakuMutuByMethodJenisCache[$bmCacheKey] = $baku_mutu_query->first();
              }
              $baku_mutu = $bakuMutuByMethodJenisCache[$bmCacheKey];

              // Ambil unit jika ada baku mutu
              $unit_baku_mutu = null;
              if ($baku_mutu && $baku_mutu->unit_id) {
                if (!array_key_exists($baku_mutu->unit_id, $unitByIdCache)) {
                  $unitByIdCache[$baku_mutu->unit_id] = \Smt\Masterweb\Models\Unit::where('id_unit', $baku_mutu->unit_id)
                    ->whereNull('deleted_at')
                    ->first();
                }
                $unit_baku_mutu = $unitByIdCache[$baku_mutu->unit_id];
              }

              // 3. Gabungkan data hasil dengan baku mutu (jika ada)
              if ($result_one) {
                if ($baku_mutu) {
                  // Jika ada baku mutu, gabungkan datanya
                  $result_one->min = $baku_mutu->min;
                  $result_one->max = $baku_mutu->max;
                  $result_one->offset_baku_mutu = $baku_mutu->offset_baku_mutu;
                  $result_one->equal = $baku_mutu->equal;
                  $result_one->nilai_baku_mutu = $baku_mutu->nilai_baku_mutu;
                  $result_one->unit_id = $baku_mutu->unit_id;
                  $result_one->name_unit = $unit_baku_mutu ? $unit_baku_mutu->name_unit : null;
                  $result_one->shortname_unit = $unit_baku_mutu ? $unit_baku_mutu->shortname_unit : null;
                  $result_one->name_report = $baku_mutu->name_report
                    ?: ($method->name_report ?? $method->params_method ?? null);
                } else {
                  // Jika tidak ada baku mutu, set nilai default null
                  $result_one->min = null;
                  $result_one->max = null;
                  $result_one->offset_baku_mutu = null;
                  $result_one->equal = null;
                  $result_one->nilai_baku_mutu = null;
                  $result_one->unit_id = null;
                  $result_one->name_unit = null;
                  $result_one->shortname_unit = null;
                  $result_one->name_report = $method->name_report ?? $method->params_method ?? null;
                }
              }
            } else {

              $result_one = SampleResult::where('tb_sample_result.sample_id', '=', $sample->id_samples)
                ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                ->where('tb_samples.typesample_samples', $sample_type_id)

                ->where('tb_sample_result.method_id', $method->id_method)
                ->join('ms_laboratorium', function ($join) {
                  $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
                    ->whereNull('ms_laboratorium.deleted_at')
                    ->whereNull('tb_sample_result.deleted_at');
                })
                ->join('tb_samples', function ($join) {
                  $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
                    ->whereNull('tb_samples.deleted_at')
                    ->whereNull('tb_sample_result.deleted_at');
                })
                ->join('tb_sample_method', function ($join) use ($id_method) {
                  $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                    ->where('tb_sample_method.method_id', $id_method)
                    ->whereNull('tb_sample_method.deleted_at')
                    ->whereNull('tb_samples.deleted_at');
                })
                ->leftJoin('ms_sample_type', function ($join) {
                  $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                    ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                    ->whereNull('ms_sample_type.deleted_at')
                    ->whereNull('tb_samples.deleted_at');
                })
                ->leftjoin('tb_sample_analitik_progress', function ($join) {
                  $join->on('tb_sample_analitik_progress.sample_id', '=', 'tb_sample_result.sample_id')
                    ->whereNull('tb_sample_analitik_progress.deleted_at')
                    ->whereNull('tb_sample_result.deleted_at')
                    ->where('tb_sample_analitik_progress.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                    ->where('tb_sample_analitik_progress.laboratorium_progress_id', 'bc2850f5-4ec4-450f-a727-2b1428c861d9');
                })
                ->join('ms_method', function ($join) use ($id_method, $sample_type_id) {
                  $join->where('ms_method.id_method', '=', $id_method)
                    ->whereNull('tb_sample_result.deleted_at')
                    ->whereNull('ms_method.deleted_at')
                    ->join('tb_baku_mutu', function ($join) use ($sample_type_id, $id_method) {
                      $join
                        ->where('tb_baku_mutu.method_id', '=', $id_method)
                        ->where('tb_baku_mutu.sampletype_id', '=', $sample_type_id)
                        ->whereNull('tb_baku_mutu.deleted_at');
                    })
                    ->join('ms_unit as unit_baku_mutu', function ($join) {
                      $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                        ->whereNull('unit_baku_mutu.deleted_at')
                        ->whereNull('tb_baku_mutu.deleted_at');
                    });
                })

                ->orderBy('tb_sample_result.created_at', 'asc')
                ->first();
            }
          }


          // print_r($result_one);






          $sample_result = [];
          $sample_result["hasil"] = $result_one ? ($result_one->hasil ?? '-') : '-';

          // Untuk sample type Makanan/Minuman/Lainnya (d34b4a50-4560-4fce-96c3-046c7080a986)
          // tanpa filter jenis makanan di request (mode print-all),
          // baku mutu harus selalu mengikuti jenis_makanan setiap sample.
          // Jadi JANGAN fallback ke nilai baku mutu default dari $method,
          // karena itu bisa berasal dari jenis_makanan lain dan menyebabkan baku mutu dobel.
          if ($sample_type_id === "d34b4a50-4560-4fce-96c3-046c7080a986" && !$jenisMakananFilterFromRequest) {
            $sample_result["min"] = $result_one ? ($result_one->min ?? null) : null;
            $sample_result["max"] = $result_one ? ($result_one->max ?? null) : null;
            $sample_result["offset_baku_mutu"] = $result_one ? ($result_one->offset_baku_mutu ?? null) : null;
            $sample_result["equal"] = $result_one ? ($result_one->equal ?? null) : null;
            $sample_result["nilai_baku_mutu"] = $result_one ? ($result_one->nilai_baku_mutu ?? null) : null;
            $sample_result["satuan_bakumutu"] = ($result_one && isset($result_one->unit_id))
              ? ($result_one->shortname_unit ?? $result_one->name_unit ?? null)
              : null;
          } else {
            $sample_result["min"] = $result_one && isset($result_one->min) ? $result_one->min : (isset($method->min) ? $method->min : null);
            $sample_result["max"] = $result_one && isset($result_one->max) ? $result_one->max : (isset($method->max) ? $method->max : null);
            $sample_result["offset_baku_mutu"] = $result_one && isset($result_one->offset_baku_mutu) ? $result_one->offset_baku_mutu : (isset($method->offset_baku_mutu) ? $method->offset_baku_mutu : null);
            $sample_result["equal"] = $result_one && isset($result_one->equal) ? $result_one->equal : (isset($method->equal) ? $method->equal : null);
            $sample_result["nilai_baku_mutu"] = $result_one && isset($result_one->nilai_baku_mutu) ? $result_one->nilai_baku_mutu : (isset($method->nilai_baku_mutu) ? $method->nilai_baku_mutu : null);
            $sample_result["satuan_bakumutu"] = ($result_one && isset($result_one->unit_id))
              ? ($result_one->shortname_unit ?? $result_one->name_unit ?? null)
              : (isset($method->shortname_unit) ? $method->shortname_unit : (isset($method->name_unit) ? $method->name_unit : null));
          }

          $sample_result["method_id"] = $method->id_method;
          $sample_result["name_sample_type"] = isset($sample->name_sample_type) ? $sample->name_sample_type : null;
          $sample_result["name_report"] = ($result_one ? ($result_one->name_report ?? null) : null)
            ?: ($method->name_report ?? $method->params_method ?? null);
          $sample_result["keterangan"] = $result_one ? ($result_one->keterangan ?? null) : null;
          $sample_result["lokasi_selected"] = $result_one && isset($result_one->lokasi_selected) ? $result_one->lokasi_selected : null;

          $sample_result["perlakuan_usap_tangan_sample_analitik_progress"] = $result_one && isset($result_one->perlakuan_usap_tangan_sample_analitik_progress) ? $result_one->perlakuan_usap_tangan_sample_analitik_progress : null;

          if ($sample_result["hasil"] != '-' && $sample->name_sample_type=='Air Kolam Renang') {

            array_push($sample_one["result"], $sample_result);
          }

          if ($sample->name_sample_type!='Air Kolam Renang') {

            array_push($sample_one["result"], $sample_result);
          }
        }



        array_push($table, $sample_one);
      }
    } else {
      // Prefetch hasil untuk kombinasi sample x method (hindari query per-iterasi).
      $prefetchedResultBySampleMethod = [];
      $sampleIdsForPrefetch = collect($all_samples ?? [])->pluck('id_samples')->filter()->unique()->values()->all();
      $methodIdsForPrefetch = collect($method_all ?? [])->pluck('id_method')->filter()->unique()->values()->all();
      if (!empty($sampleIdsForPrefetch) && !empty($methodIdsForPrefetch)) {
        $prefetchedRows = SampleResult::whereIn('tb_sample_result.sample_id', $sampleIdsForPrefetch)
          ->whereIn('tb_sample_result.method_id', $methodIdsForPrefetch)
          ->whereNull('tb_sample_result.deleted_at')
          ->join('tb_samples', function ($join) use ($sample_type_id) {
            $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->whereNull('tb_samples.deleted_at');
          })
          ->join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->whereNull('ms_laboratorium.deleted_at');
          })
          ->leftJoin('tb_sample_analitik_progress', function ($join) {
            $join->on('tb_sample_analitik_progress.sample_id', '=', 'tb_sample_result.sample_id')
              ->whereNull('tb_sample_analitik_progress.deleted_at')
              ->where('tb_sample_analitik_progress.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
              ->where('tb_sample_analitik_progress.laboratorium_progress_id', 'bc2850f5-4ec4-450f-a727-2b1428c861d9');
          })
          ->leftJoin('tb_baku_mutu', function ($join) use ($sample_type_id) {
            $join->on('tb_baku_mutu.method_id', '=', 'tb_sample_result.method_id')
              ->where('tb_baku_mutu.sampletype_id', '=', $sample_type_id)
              ->whereNull('tb_baku_mutu.deleted_at');
          })
          ->leftJoin('ms_unit as unit_baku_mutu', function ($join) {
            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
              ->whereNull('unit_baku_mutu.deleted_at');
          })
          ->select(
            'tb_sample_result.*',
            'tb_baku_mutu.min as bm_min',
            'tb_baku_mutu.max as bm_max',
            'tb_baku_mutu.offset_baku_mutu as bm_offset_baku_mutu',
            'tb_baku_mutu.equal as bm_equal',
            'tb_baku_mutu.nilai_baku_mutu as bm_nilai_baku_mutu',
            'tb_baku_mutu.unit_id as bm_unit_id',
            'tb_baku_mutu.name_report as bm_name_report',
            'unit_baku_mutu.name_unit as bm_name_unit',
            'unit_baku_mutu.shortname_unit as bm_shortname_unit',
            'tb_sample_analitik_progress.perlakuan_usap_tangan_sample_analitik_progress'
          )
          ->orderBy('tb_sample_result.created_at', 'asc')
          ->get();

        foreach ($prefetchedRows as $row) {
          $key = $row->sample_id . '|' . $row->method_id;
          if (!isset($prefetchedResultBySampleMethod[$key])) {
            $prefetchedResultBySampleMethod[$key] = $row;
          }
        }
      }

      foreach ($method_all as $method) {

        $sample_one = [];
        $sample_one["method"] = $method;
        $sample_one["result"] = [];
        foreach ($all_samples as $sample) {



          $key = $sample->id_samples . '|' . $method->id_method;
          $result_one = $prefetchedResultBySampleMethod[$key] ?? null;


          if (isset($result_one)) {
            $sample_result = [];
            $sample_result["hasil"] = isset($result_one->hasil) ? $result_one->hasil : "-";
            $sample_result["min"] = isset($result_one->bm_min) ? $result_one->bm_min : null;
            $sample_result["max"] = isset($result_one->bm_max) ? $result_one->bm_max : null;
            $sample_result["name_sample_type"] = isset($sample->name_sample_type) ? $sample->name_sample_type : null;
            $sample_result["method_id"] = $method->id_method;
            $sample_result["offset_baku_mutu"] = isset($result_one->bm_offset_baku_mutu) ? $result_one->bm_offset_baku_mutu : null;
            $sample_result["equal"] = isset($result_one->bm_equal) ? $result_one->bm_equal : null;
            $sample_result["name_report"] = isset($result_one->bm_name_report) ? $result_one->bm_name_report : null;
            $sample_result["nilai_baku_mutu"] = isset($result_one->bm_nilai_baku_mutu) ? $result_one->bm_nilai_baku_mutu : null;
            $sample_result["satuan_bakumutu"] = isset($result_one->bm_unit_id)
              ? ($result_one->bm_shortname_unit ?? $result_one->bm_name_unit ?? null)
              : null;
            $sample_result["perlakuan_usap_tangan_sample_analitik_progress"] = isset($result_one->perlakuan_usap_tangan_sample_analitik_progress) ? $result_one->perlakuan_usap_tangan_sample_analitik_progress : null;
            $sample_result["sample"] = $sample;
            $sample_result["keterangan"] = $result_one->keterangan ?? null;

            if ($sample_result["hasil"] != '-' && $sample->name_sample_type=='Air Kolam Renang') {

              array_push($sample_one["hasil"], $sample_result);
            }

            if ($sample->name_sample_type!='Air Kolam Renang') {

              array_push($sample_one["hasil"], $sample_result);
            }
          }
        }

        // print_r($result_one);

        array_push($table, $sample_one);
      }
    }

    // Override baku mutu khusus sampel (baca-hasil) untuk semua format cetak mikro
    $mbiLabId = (string) (Laboratorium::where('kode_laboratorium', 'MBI')->whereNull('deleted_at')->value('id_laboratorium')
      ?: 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5');
    $table = $this->applyBakuMutuSampleOverridesToPrintTable($table, $mbiLabId);










    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {

          $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
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
            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftJoin('ms_sample_type', function ($join) {
              $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                ->whereNull('ms_sample_type.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending','code_sample_type',  'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples', 'permohonan_uji_id', 'name_sample_type','datesampling_samples', 'tb_samples.jenis_makanan_id')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            ->take(1)
            ->first();
        } else {
          $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
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
            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftJoin('ms_sample_type', function ($join) {
              $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                ->whereNull('ms_sample_type.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending','code_sample_type',  'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples', 'permohonan_uji_id', 'name_sample_type','datesampling_samples', 'tb_samples.jenis_makanan_id')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            ->take(1)
            ->first();
        }
      } else {
        if (isset($packet_id)) {
          $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
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
            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftJoin('ms_sample_type', function ($join) {
              $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                ->whereNull('ms_sample_type.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'code_sample_type', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples', 'permohonan_uji_id', 'name_sample_type', 'datesampling_samples', 'tb_samples.jenis_makanan_id')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            ->take(1)
            ->first();
        } else {
          $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('tb_samples.typesample_samples', $sample_type_id)
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
            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftJoin('ms_sample_type', function ($join) {
              $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                ->whereNull('ms_sample_type.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->select('tb_pengesahan_hasil.*', 'id_laboratorium','code_sample_type',  'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples', 'permohonan_uji_id', 'name_sample_type', 'datesampling_samples', 'tb_samples.jenis_makanan_id')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            ->take(1)
            ->first();
        }
      }
    } else {
      $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
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
        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftJoin('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples', 'permohonan_uji_id', 'name_sample_type','code_sample_type', 'tb_lab_num.*', 'datesampling_samples', 'tb_samples.jenis_makanan_id')
        ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
        ->take(1)
        ->first();
    }

    // Jika query di atas tidak mengembalikan baris (filter jenis/packet/pengesahan),
    // ambil referensi sampel dari isi $table atau $all_samples agar preview/cetak tidak error.
    if (!$sample && !empty($table)) {
      $firstTableRow = $table[0] ?? null;
      if (is_array($firstTableRow) && isset($firstTableRow['sample_type'])) {
        $sample = $firstTableRow['sample_type'];
      }
    }

    if (!$sample && !empty($table)) {
      foreach ($table as $row) {
        if (!is_array($row)) {
          continue;
        }
        if (isset($row['sample_type']) && is_object($row['sample_type'])) {
          $sample = $row['sample_type'];
          break;
        }
        if (!empty($row['hasil']) && is_array($row['hasil'])) {
          foreach ($row['hasil'] as $hasilRow) {
            if (isset($hasilRow['sample']) && is_object($hasilRow['sample'])) {
              $sample = $hasilRow['sample'];
              break 2;
            }
          }
        }
        if (!empty($row['result']) && is_array($row['result'])) {
          foreach ($row['result'] as $resultRow) {
            if (isset($resultRow['sample']) && is_object($resultRow['sample'])) {
              $sample = $resultRow['sample'];
              break 2;
            }
          }
        }
      }
    }

    if (!$sample && !empty($all_samples)) {
      if ($all_samples instanceof \Illuminate\Support\Collection) {
        $sample = $all_samples->first();
      } elseif (is_array($all_samples)) {
        $sample = reset($all_samples) ?: null;
      }
    }

    // Tanpa pengesahan / filter jenis di query utama, $sample bisa masih null padahal ada sampel MBI.
    // Ambil satu baris referensi (preview/cetak) tanpa leftjoin pengesahan agar iframe tidak 404.
    if (!$sample && !empty($id_permohonan_uji)) {
      $fallbackMikro = Sample::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
        ->whereNull('tb_samples.deleted_at')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        });
      if (!empty($sample_type_id)) {
        if ($isMbiAbAmType) {
          $fallbackMikro->whereIn('tb_samples.typesample_samples', $mbiAbAmTypeIds);
        } else {
          $fallbackMikro->where('tb_samples.typesample_samples', $sample_type_id);
        }
      }
      $sample = $fallbackMikro
        ->select('tb_samples.*', 'ms_sample_type.*', 'ms_laboratorium.id_laboratorium')
        ->orderBy('tb_samples.count_id', 'asc')
        ->first();
    }

    if (!$sample) {
      abort(404, 'Data sampel untuk cetak hasil mikro tidak ditemukan.');
    }

    // Query utama memilih id_laboratorium dari join; objek dari $all_samples bisa belum punya field ini.
    if (!isset($sample->id_laboratorium) || $sample->id_laboratorium === null || $sample->id_laboratorium === '') {
      $sid = $sample->id_samples ?? null;
      if ($sid) {
        $labId = DB::table('tb_sample_method')
          ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->where('tb_sample_method.sample_id', $sid)
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_laboratorium.deleted_at')
          ->where('ms_laboratorium.kode_laboratorium', 'MBI')
          ->value('ms_laboratorium.id_laboratorium');
        if ($labId) {
          $sample->id_laboratorium = $labId;
        }
      }
    }

    $sample_ids = [];
   $lab_nums = [];
    foreach ($table as $mytable) {
     $idSample = data_get($mytable, 'sample_type.id_samples');
     if ($idSample) {
       $sample_ids[] = $idSample;
       // Struktur minimal yang dipakai di view hanya membutuhkan properti page_break.
       // Inisialisasi default page_break = 1 agar penomoran tetap berurutan.
       $lab_nums[$idSample] = (object)[
         'page_break' => 1,
       ];
     }
   }

   // Banyaknya nomor per halaman untuk kebutuhan penomoran saat ada page break di view mikro.
   // Saat ini view hanya menggunakan nilai ini untuk menggeser nomor jika page_break > 1.
   $lab_num_per_page = 25;


  // Ambil nomer lab KESMAS per (permohonan + jenis sampel + lab) untuk MBI
  $permohonanUjiIdForKesmas = $sample->permohonan_uji_id ?? $id_permohonan_uji ?? null;
  $nomerLabKesmas = null;
  if ($permohonanUjiIdForKesmas && $sample_type_id) {
    if (!empty($sample->id_laboratorium)) {
      $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiIdForKesmas)
        ->where('sample_type_id', $sample_type_id)
        ->where('laboratorium_id', $sample->id_laboratorium)
        ->first();
    }
    if (!$nomerLabKesmas) {
      $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiIdForKesmas)
        ->where('sample_type_id', $sample_type_id)
        ->whereHas('laboratorium', function ($q) {
          $q->where('kode_laboratorium', 'MBI');
        })
        ->first();
    }
  }
  // Fallback tanpa filter jenis sampel jika tidak ditemukan
  if (!$nomerLabKesmas && $permohonanUjiIdForKesmas) {
    $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $permohonanUjiIdForKesmas)
      ->whereHas('laboratorium', function ($q) {
        $q->where('kode_laboratorium', 'MBI');
      })
      ->first();
  }

  $kesmasLabNumber = $nomerLabKesmas ? $nomerLabKesmas->nomer_lab : null;

  // Bangun string Nomor Laboratorium 449.5/02 untuk ditampilkan di template
  if ($kesmasLabNumber) {
    $nomerLabDisplay = '449.5/02/' . str_pad($kesmasLabNumber, 4, '0', STR_PAD_LEFT) . '/' . ($nomerLabKesmas->year ?? date('Y'));
  } else {
    $nomerLabDisplay = '449.5/02/............/' . date('Y');
  }

    // dd($lab_nums, $sample_ids, $table);


    $pengesahan_hasil = PengesahanHasil::where('sample_id', '=', $sample->id_samples)->where('laboratorium_id', '=', $sample->laboratorium_id)->first();

    $agenda = $request->input('agenda');
    // dd($agenda);


    $no_LHU = LHU::where('sample_id', '=', $sample->id_samples)->where('lab_id', '=', $sample->laboratorium_id)->first();

    if (!isset($no_LHU)) {


      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::where(DB::raw('YEAR(created_at)'), '=', date('Y'))->max('nomer_urut_LHU');
      // $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // Hanya gunakan nomer lab KESMAS, tidak ada fallback
      $labNumber = $kesmasLabNumber;

      // Simpan nomor lengkap di database: 445.02/{nomer_lab}/{jenis_sampel}/05.31/{tahun}
      // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
      if ($labNumber) {
        $no_LHU->nomer_LHU = '445.02/' . $labNumber . '/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
      } else {
        $no_LHU->nomer_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
      }
      $no_LHU->sample_id = $sample->id_samples;
      $no_LHU->lab_id = $sample->id_laboratorium;
      $no_LHU->save();

      $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
      $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');

      // Deteksi jenis sampel unik dari $all_samples atau $table untuk format gabungan
      $jenisSampelUnik = [];

      // Coba dari $all_samples dulu (lebih akurat untuk format gabungan)
      if (isset($all_samples) && is_iterable($all_samples)) {
        foreach ($all_samples as $sampleItem) {
          if (isset($sampleItem->name_sample_type)) {
            $jenisSampel = $sampleItem->name_sample_type;
            if (!in_array($jenisSampel, $jenisSampelUnik)) {
              $jenisSampelUnik[] = $jenisSampel;
            }
          }
        }
      }

      // Jika belum dapat dari $all_samples, coba dari $table
      if (empty($jenisSampelUnik) && isset($table) && is_array($table)) {
        foreach ($table as $mytable) {
          if (isset($mytable['sample_type']) && isset($mytable['sample_type']->name_sample_type)) {
            $jenisSampel = $mytable['sample_type']->name_sample_type;
            if (!in_array($jenisSampel, $jenisSampelUnik)) {
              $jenisSampelUnik[] = $jenisSampel;
            }
          }
          // Juga cek dari result jika ada
          if (isset($mytable['result']) && is_array($mytable['result'])) {
            foreach ($mytable['result'] as $result) {
              if (isset($result['name_sample_type'])) {
                $jenisSampel = $result['name_sample_type'];
                if (!in_array($jenisSampel, $jenisSampelUnik)) {
                  $jenisSampelUnik[] = $jenisSampel;
                }
              }
            }
          }
        }
      }

      // Jika ada 2 jenis sampel berbeda, gunakan format dengan nomor lab minimum - maksimum
      $hasTwoJenisSampel = count($jenisSampelUnik) == 2;

      // Ambil nomor lab minimum dan maksimum dari NomerLabKesmas saja
      $minLabNum = null;
      $maxLabNum = null;

      // Gunakan helper function untuk konsistensi
      // Ambil permohonan_uji_id dari sample atau dari parameter $id_permohonan_uji
      $permohonanUjiId = $sample->permohonan_uji_id ?? $id_permohonan_uji ?? null;
      if ($permohonanUjiId) {
        $labNums = $this->getMinMaxLabNumFromKesmas($permohonanUjiId);
        $minLabNum = $labNums['min'];
        $maxLabNum = $labNums['max'];
      }

      // Tampilkan selalu dengan nomor lab:
      // - jika agenda diisi, pakai agenda
      // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
      $nomerLab = !empty($agenda)
        ? $agenda
        : $kesmasLabNumber;

      if ($hasTwoJenisSampel && $minLabNum !== null && $maxLabNum !== null) {
        // Format untuk 2 jenis sampel: 445.02/{min_lab_num} - {max_lab_num} /AB-AM/05.31/{year}
        $minLabNumFormatted = str_pad($minLabNum, 3, '0', STR_PAD_LEFT);
        $maxLabNumFormatted = str_pad($maxLabNum, 3, '0', STR_PAD_LEFT);

        // Jika min dan max sama, hanya tampilkan satu angka
        if ($minLabNum === $maxLabNum) {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . ' - ' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . ' - ' . $maxLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      } else {
        // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
        if ($nomerLab) {
          $nomerLab = str_pad($nomerLab, 3, '0', STR_PAD_LEFT);
          $no_LHU = '445.02/' . $nomerLab . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      }

    } else {
      if (isset($pengesahan_hasil)) {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('Y');
      } else {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');
      }

      // Deteksi jenis sampel unik dari $all_samples atau $table untuk format gabungan
      $jenisSampelUnik = [];

      // Coba dari $all_samples dulu (lebih akurat untuk format gabungan)
      if (isset($all_samples) && is_iterable($all_samples)) {
        foreach ($all_samples as $sampleItem) {
          if (isset($sampleItem->name_sample_type)) {
            $jenisSampel = $sampleItem->name_sample_type;
            if (!in_array($jenisSampel, $jenisSampelUnik)) {
              $jenisSampelUnik[] = $jenisSampel;
            }
          }
        }
      }

      // Jika belum dapat dari $all_samples, coba dari $table
      if (empty($jenisSampelUnik) && isset($table) && is_array($table)) {
        foreach ($table as $mytable) {
          if (isset($mytable['sample_type']) && isset($mytable['sample_type']->name_sample_type)) {
            $jenisSampel = $mytable['sample_type']->name_sample_type;
            if (!in_array($jenisSampel, $jenisSampelUnik)) {
              $jenisSampelUnik[] = $jenisSampel;
            }
          }
          // Juga cek dari result jika ada
          if (isset($mytable['result']) && is_array($mytable['result'])) {
            foreach ($mytable['result'] as $result) {
              if (isset($result['name_sample_type'])) {
                $jenisSampel = $result['name_sample_type'];
                if (!in_array($jenisSampel, $jenisSampelUnik)) {
                  $jenisSampelUnik[] = $jenisSampel;
                }
              }
            }
          }
        }
      }

      // Jika ada 2 jenis sampel berbeda, gunakan format dengan nomor lab minimum - maksimum
      $hasTwoJenisSampel = count($jenisSampelUnik) == 2;

      // Ambil nomor lab minimum dan maksimum dari NomerLabKesmas saja
      $minLabNum = null;
      $maxLabNum = null;

      // Gunakan helper function untuk konsistensi
      // Ambil permohonan_uji_id dari sample atau dari parameter $id_permohonan_uji
      $permohonanUjiId = $sample->permohonan_uji_id ?? $id_permohonan_uji ?? null;
      if ($permohonanUjiId) {
        $labNums = $this->getMinMaxLabNumFromKesmas($permohonanUjiId);
        $minLabNum = $labNums['min'];
        $maxLabNum = $labNums['max'];
      }

      // Tampilkan selalu dengan nomor lab:
      // - jika agenda diisi, pakai agenda
      // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
      $nomerLab = !empty($agenda)
        ? $agenda
        : $kesmasLabNumber;

      if ($hasTwoJenisSampel && $minLabNum !== null && $maxLabNum !== null) {
        // Format untuk 2 jenis sampel: 445.02/{min_lab_num} - {max_lab_num} /AB-AM/05.31/{year}
        $minLabNumFormatted = str_pad($minLabNum, 3, '0', STR_PAD_LEFT);
        $maxLabNumFormatted = str_pad($maxLabNum, 3, '0', STR_PAD_LEFT);

        // Jika min dan max sama, hanya tampilkan satu angka
        if ($minLabNum === $maxLabNum) {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . ' - ' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . ' - ' . $maxLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      } else {
        // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
        if ($nomerLab) {
          $nomerLab = str_pad($nomerLab, 3, '0', STR_PAD_LEFT);
          $no_LHU = '445.02/' . $nomerLab . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      }


    }

    $sampleModel = Sample::find($sample->id_samples);
    $no_LHU = $this->applyKesmasNomorLabBakuToNoLhu($sampleModel, $no_LHU, $year ?? date('Y'), $sample->id_laboratorium);

    $laboratorium = Laboratorium::findOrFail($sample->id_laboratorium);


    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {
          $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      } else {
        if (isset($packet_id)) {
          $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      }
    } else {
      $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
        // ->take(1)
        ->first();
    }

    // Query di atas memakai filter jenis_makanan/paket; jika tidak cocok dengan baris sampel (mis. Jenang + Kue beras), hasilnya null dan memicu 404.
    if (!$checking_min && isset($sample->id_samples)) {
      $checking_min = Sample::where('tb_samples.id_samples', $sample->id_samples)
        ->whereNull('tb_samples.deleted_at')
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'tb_sample_penanganan.date_checking', 'tb_sample_penanganan.date_done_estimation_labs')
        ->first();
    }
    if (!$checking_min) {
      abort(404, 'Data penanganan sampel untuk cetak tidak ditemukan.');
    }

    $checking_min = $checking_min->date_checking ?? $sample->date_sending ?? null;

    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {

          $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      } else {

        if (isset($packet_id)) {

          $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      }
    } else {

      $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
        // ->take(1)
        ->first();
    }

    // dd($checking_min);

    if (!$done_max && isset($sample->id_samples)) {
      $done_max = Sample::where('tb_samples.id_samples', $sample->id_samples)
        ->whereNull('tb_samples.deleted_at')
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'tb_sample_penanganan.date_done_estimation_labs')
        ->first();
    }
    $done_max = $done_max ? $done_max->date_done_estimation_labs : null;

    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {

          $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      } else {
        if (isset($packet_id)) {

          $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      }
    } else {
      $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
        // ->take(1)
        ->first();
    }
    if (!$diambil_min && isset($sample->id_samples)) {
      $diambil_min = Sample::where('tb_samples.id_samples', $sample->id_samples)
        ->whereNull('tb_samples.deleted_at')
        ->first();
    }
    $diambil_min = $diambil_min ? $diambil_min->datesampling_samples : ($sample->datesampling_samples ?? null);
    // dd($diambil_min);
    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {
          $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      } else {

        if (isset($packet_id)) {
          $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        } else {
          $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
            // ->take(1)
            ->first();
        }
      }
    } else {
      $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
        // ->take(1)
        ->first();
    }

    if (!$diambil_max && isset($sample->id_samples)) {
      $diambil_max = Sample::where('tb_samples.id_samples', $sample->id_samples)
        ->whereNull('tb_samples.deleted_at')
        ->first();
    }
    $diambil_max = $diambil_max ? $diambil_max->datesampling_samples : ($sample->datesampling_samples ?? null);

    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {
          $number_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'desc')
            // ->take(1)
            ->first();
        } else {
          $number_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'desc')
            // ->take(1)
            ->first();
        }
      } else {

        if (isset($packet_id)) {
          $number_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'desc')
            // ->take(1)
            ->first();
        } else {
          $number_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'desc')
            // ->take(1)
            ->first();
        }
      }
    } else {
      $number_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_samples.count_id', 'desc')
        // ->take(1)
        ->first();
    }


    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {
          $number_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'asc')
            // ->take(1)
            ->first();
        } else {
          $number_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'asc')
            // ->take(1)
            ->first();
        }
      } else {

        if (isset($packet_id)) {
          $number_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'asc')
            // ->take(1)
            ->first();
        } else {
          $number_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_penanganan', function ($join) {
              $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

                // ->limit(1)
                ->whereNull('tb_sample_penanganan.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_method.deleted_at');
            })

            ->leftjoin('tb_pengesahan_hasil', function ($join) {
              $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
                ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->whereNull('tb_pengesahan_hasil.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
            ->orderBy('tb_samples.count_id', 'asc')
            // ->take(1)
            ->first();
        }
      }
    } else {
      $number_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            // ->limit(1)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->leftjoin('tb_pengesahan_hasil', function ($join) {
          $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
            ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
            ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
            ->whereNull('tb_pengesahan_hasil.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
        ->orderBy('tb_samples.count_id', 'asc')
        // ->take(1)
        ->first();
    }
    if (isset($sample_type_id)) {
      if (isset($jenis_makanan_id)) {
        if (isset($packet_id)) {
          $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.packet_id', $packet_id)
            ->join('ms_method', function ($join)  use ($jenis_makanan_id, $sample_type_id) {
              $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                ->whereNull('tb_laboratorium_method.deleted_at')
                ->whereNull('ms_method.deleted_at')
                ->join('tb_baku_mutu', function ($join) use ($jenis_makanan_id, $sample_type_id) {
                  $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                    ->where('tb_baku_mutu.jenis_makanan_id', $jenis_makanan_id)
                    ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                    ->whereNull('tb_baku_mutu.deleted_at')
                    ->whereNull('ms_method.deleted_at');
                })
                ->join('ms_library', function ($join) {
                  $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                    ->whereNull('ms_library.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
                });
            })
            ->leftjoin('tb_sample_result', function ($join) {
              $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                ->whereNull('tb_laboratorium_method.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_samples', function ($join) {
              $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                ->whereNull('tb_samples.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_laboratorium_method.deleted_at');
            })
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            ->distinct('ms_library.id_library')
            ->select('ms_library.*')
            ->get();
        } else {
          $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->where('tb_samples.typesample_samples', $sample_type_id)
            ->join('ms_method', function ($join) use ($jenis_makanan_id, $sample_type_id) {
              $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                ->whereNull('tb_laboratorium_method.deleted_at')
                ->whereNull('ms_method.deleted_at')
                ->join('tb_baku_mutu', function ($join) use ($jenis_makanan_id, $sample_type_id) {
                  $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                    ->where('tb_baku_mutu.jenis_makanan_id', $jenis_makanan_id)
                    ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                    ->whereNull('tb_baku_mutu.deleted_at')
                    ->whereNull('ms_method.deleted_at');
                })
                ->join('ms_library', function ($join) {
                  $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                    ->whereNull('ms_library.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
                });
            })

            ->leftjoin('tb_sample_result', function ($join) {
              $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                ->whereNull('tb_laboratorium_method.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_samples', function ($join) {
              $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                ->whereNull('tb_samples.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_laboratorium_method.deleted_at');
            })
            ->join('tb_sample_method', function ($join) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })

            ->distinct('ms_library.id_library')
            ->select('ms_library.*')
            ->get();
        }
      } else {
        if ($sample_type_id == "d34b4a50-4560-4fce-96c3-046c7080a986") {
          if (isset($packet_id)) {
            $where_in = [];
            foreach ($all_samples as $sample_one) {
              array_push($where_in, $sample_one->jenis_makanan_id);
            }
            $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->where('tb_samples.packet_id', $packet_id)
              ->whereIn('tb_samples.jenis_makanan_id', $where_in)
              ->join('ms_method', function ($join) use ($sample_type_id, $where_in) {
                $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->join('tb_baku_mutu', function ($join) use ($sample_type_id, $where_in) {
                    $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                      ->whereIn('tb_baku_mutu.jenis_makanan_id', $where_in)
                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at');
                  })
                  ->join('ms_library', function ($join) {
                    $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                      ->whereNull('ms_library.deleted_at')
                      ->whereNull('tb_baku_mutu.deleted_at');
                  });
              })

              ->leftjoin('tb_sample_result', function ($join) {
                $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                  ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_samples', function ($join) {
                $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                  ->whereNull('tb_samples.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_laboratorium_method.deleted_at');
              })
              ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })

              ->distinct('ms_library.id_library')
              ->select('ms_library.*')
              ->get();
          } else {
            $where_in = [];
            foreach ($all_samples as $sample_one) {
              array_push($where_in, $sample_one->jenis_makanan_id);
            }


            $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->whereIn('tb_samples.jenis_makanan_id', $where_in)
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_laboratorium_method.deleted_at');
              })
              ->join('ms_method', function ($join) use ($sample_type_id, $where_in) {
                $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->join('tb_baku_mutu', function ($join) use ($sample_type_id, $where_in) {
                    $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                      ->whereIn('tb_baku_mutu.jenis_makanan_id', $where_in)
                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at');
                  })
                  ->join('ms_library', function ($join) {
                    $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                      ->whereNull('ms_library.deleted_at')
                      ->whereNull('tb_baku_mutu.deleted_at');
                  });
              })
              ->leftjoin('tb_sample_result', function ($join) {
                $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                  ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_samples', function ($join) {
                $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                  ->whereNull('tb_samples.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })

              ->distinct('ms_library.id_library')
              ->select('ms_library.*')
              ->get();
          }
        } else {
          if (isset($packet_id)) {

            $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->where('tb_samples.packet_id', $packet_id)
              ->join('ms_method', function ($join) use ($sample_type_id) {
                $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->join('tb_baku_mutu', function ($join) use ($sample_type_id) {
                    $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at');
                  })
                  ->join('ms_library', function ($join) {
                    $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                      ->whereNull('ms_library.deleted_at')
                      ->whereNull('tb_baku_mutu.deleted_at');
                  });
              })

              ->leftjoin('tb_sample_result', function ($join) {
                $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                  ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_samples', function ($join) {
                $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                  ->whereNull('tb_samples.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_laboratorium_method.deleted_at');
              })
              ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })

              ->distinct('ms_library.id_library')
              ->select('ms_library.*')
              ->get();
          } else {



            $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
              ->where('ms_laboratorium.kode_laboratorium', 'MBI')
              ->where('tb_samples.typesample_samples', $sample_type_id)
              ->join('ms_laboratorium', function ($join) {
                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                  ->whereNull('ms_laboratorium.deleted_at')
                  ->whereNull('tb_laboratorium_method.deleted_at');
              })
              ->join('ms_method', function ($join) use ($sample_type_id) {
                $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('ms_method.deleted_at')
                  ->join('tb_baku_mutu', function ($join) use ($sample_type_id) {
                    $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)

                      ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                      ->whereNull('tb_baku_mutu.deleted_at')
                      ->whereNull('ms_method.deleted_at');
                  })
                  ->join('ms_library', function ($join) {
                    $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                      ->whereNull('ms_library.deleted_at')
                      ->whereNull('tb_baku_mutu.deleted_at');
                  });
              })
              ->leftjoin('tb_sample_result', function ($join) {
                $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
                  ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
                  ->whereNull('tb_laboratorium_method.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_samples', function ($join) {
                $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

                  ->whereNull('tb_samples.deleted_at')
                  ->whereNull('tb_sample_result.deleted_at');
              })
              ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                  ->whereNull('tb_sample_method.deleted_at')
                  ->whereNull('tb_samples.deleted_at');
              })

              ->distinct('ms_library.id_library')
              ->select('ms_library.*')
              ->get();
          }
        }
      }
    } else {
      $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'MBI')
        ->where('tb_samples.typesample_samples', $sample_type_id)
        ->join('ms_method', function ($join) use ($sample_type_id) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->join('tb_baku_mutu', function ($join) use ($sample_type_id) {
              $join->where('tb_baku_mutu.sampletype_id', $sample_type_id)
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->join('ms_library', function ($join) {
              $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                ->whereNull('ms_library.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            });
        })

        ->leftjoin('tb_sample_result', function ($join) {
          $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
            ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('tb_sample_result.deleted_at');
        })
        ->join('tb_samples', function ($join) {
          $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

            ->whereNull('tb_samples.deleted_at')
            ->whereNull('tb_sample_result.deleted_at');
        })
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_laboratorium_method.deleted_at');
        })

        ->distinct('ms_library.id_library')
        ->select('ms_library.*')
        ->get();
    }

    // Cari petugas dominan (paling sering) untuk masing-masing peran:
    // 2 = Pemeriksa, 4 = Verifikator, 5 = Validator
    $sampleIdsForRole = collect($table)
      ->map(function ($row) {
        return data_get($row, 'sample_type.id_samples');
      })
      ->filter()
      ->unique()
      ->values()
      ->all();

    $pemeriksa = '-';
    $verifikator = '-';
    $validator = '-';

    if (!empty($sampleIdsForRole)) {
      $verificationActivitySamples = VerificationActivitySample::whereIn('id_sample', $sampleIdsForRole)
        ->whereIn('id_verification_activity', [2, 4, 5])
        ->get();

      $groupedByRole = $verificationActivitySamples->groupBy('id_verification_activity');

      // Pemeriksa (2)
      if ($groupedByRole->has(2)) {
        $name = $groupedByRole->get(2)
          ->pluck('nama_petugas')
          ->filter()
          ->countBy()
          ->sortByDesc(function ($count, $nama) {
            // Urutkan berdasarkan frekuensi (nilai), bukan nama (key)
            return $count;
          })
          ->keys()
          ->first();
        $pemeriksa = $name ?? '-';
      }

      // Verifikator (4)
      if ($groupedByRole->has(4)) {
        $name = $groupedByRole->get(4)
          ->pluck('nama_petugas')
          ->filter()
          ->countBy()
          ->sortByDesc(function ($count, $nama) {
            return $count;
          })
          ->keys()
          ->first();
        $verifikator = $name ?? '-';
      }

      // Validator (5)
      if ($groupedByRole->has(5)) {
        $name = $groupedByRole->get(5)
          ->pluck('nama_petugas')
          ->filter()
          ->countBy()
          ->sortByDesc(function ($count, $nama) {
            return $count;
          })
          ->keys()
          ->first();
        $validator = $name ?? '-';
      }
    }

    // Normalisasi nama petugas jika perlu
    $validator = $this->searchPetugas($validator);
    $verifikator = $this->searchPetugas($verifikator);
    $pemeriksa = $this->searchPetugas($pemeriksa);


    if (in_array($sample->name_sample_type, ['Air Higiene', 'Air Bersih', 'Air Minum'], true)) {
      $samplePrint = $request->printSamples;

      # code...
      if(isset($samplePrint)){

        if(count($samplePrint) > count($table)){
          $agenda = $request->input('agenda');

         $idPermohonanUji = $id_permohonan_uji;

          $permohonan_uji = PermohonanUji::query()->where('id_permohonan_uji', $idPermohonanUji)->first();
          $customer= $permohonan_uji->customer;

          $samples = Sample::query()->where('permohonan_uji_id', $idPermohonanUji)
                      ->whereHas('samplemethod', function ($query) {
                          $query->where('laboratorium_id', "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5");
                      })
                      ->where(function ($query) {
                        $query->whereIn('name_sample_type', ['Air Higiene', 'Air Bersih'])
                              ->orWhere('name_sample_type', 'Air Minum');
                      })
                      ->leftJoin('ms_sample_type', function ($join) {
                        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                          ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                          ->whereNull('ms_sample_type.deleted_at')
                          ->whereNull('tb_samples.deleted_at');
                      })
                      ->with(['sampleresult.method','sampletype', 'labnum', 'sampleresult.method.bakumutu', 'sampleresult.method.bakumutu.unit', 'permohonanuji'])
                      ->orderBy('count_id', 'asc')
                      ->get();



          $samplePrint = $request->printSamples;

          if(isset($samplePrint)){
            if(count($samples) > count($samplePrint)){
              $samples = Sample::query()->where('permohonan_uji_id', $idPermohonanUji)
                      ->whereIn('id_samples', $samplePrint)
                      ->where(function ($query) {
                        $query->whereIn('name_sample_type', ['Air Higiene', 'Air Bersih'])
                              ->orWhere('name_sample_type', 'Air Minum');
                      })
                      ->leftJoin('ms_sample_type', function ($join) {
                        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                          ->on('tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
                          ->whereNull('ms_sample_type.deleted_at')
                          ->whereNull('tb_samples.deleted_at');
                      })
                      ->whereHas('samplemethod', function ($query) {
                          $query->where('laboratorium_id', "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5");
                      })
                      ->with(['sampleresult.method', 'labnum', 'sampleresult.method.bakumutu', 'sampleresult.method.bakumutu.unit'])
                      ->orderBy('count_id', 'asc')
                      ->get();
            }
          }

          $listVerifications =
          $jenis_sarana = [];
          $tanggal_sampling = [];
          $labNums = [];
          $metode_pemeriksaan = [];
          $params = [];
          $methods = [];
          $results = [];
          $bakumutu = [];

          // Override baku mutu khusus sampel (baca-hasil) untuk format gabungan AB/AM
          $mbiLabIdGabungan = (string) (Laboratorium::where('kode_laboratorium', 'MBI')->whereNull('deleted_at')->value('id_laboratorium')
            ?: 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5');
          $samples = $this->applyBakuMutuSampleOverridesToSampleCollection($samples, $mbiLabIdGabungan);

          foreach($samples as $sample){
            $jenis_sarana[] = $sample->jenis_sarana_names;
            $tanggal_sampling[] = Carbon::parse($sample->datesampling_samples)->toDateString();
            $labNums[] = $sample->labnum[0]->lab_number;

            $result_item = [
              'labnum' => $sample->count_id,
              'lokasi' => $sample->location_samples ?? $sample->name_pelanggan,
              'codesample_samples' => $sample->codesample_samples,

              'location_samples' => $sample->location_samples,
              'name_sample_type' => $sample->name_sample_type ?? $sample->sampletype->name_sample_type,
              'is_pudam' => $sample->is_pudam,
              'jam_sampling' => date("H:i", strtotime($sample->datesampling_samples)),
              'hasil' => [],
            ];

            foreach($sample->sampleresult as $sampleresult){
              $metode_pemeriksaan[] = $sampleresult->metode;
              $result_item['hasil'][] = [
                $sampleresult->method_id => [
                  'hasil' => $sampleresult->hasil,
                  'metode' => $sampleresult->metode,
                  'keterangan' => $sampleresult->keterangan,
                  'offset_baku_mutu' => $sampleresult->offset_baku_mutu,
                  'bakumutu' => null
                ]
              ];

              foreach($sampleresult->method as $method)
              {
                $bakumutu[$method->id_method] = $method->bakumutu;
                if ($method->params_method == "E-Coli (Membran Filter)") {
                  $params[$method->id_method] = "Escherichia coli";
                } elseif ($method->params_method == "Total Coliform (Membrane Filter)") {
                    $params[$method->id_method] = "Total Coliform";
                } else {
                    $params[$method->id_method] = $method->params_method;
                }
              }
            }

            $result_item = array_merge($result_item, $sample->toArray());


            $results[] = $result_item;
          }

          $results = json_decode(json_encode($results));




          // Deteksi jenis sampel unik dari $samples atau $table
          $jenisSampelUnik = [];

          // Coba dari $samples dulu (lebih akurat untuk format gabungan)
          if (isset($samples) && is_iterable($samples)) {
            foreach ($samples as $sampleItem) {
              if (isset($sampleItem->name_sample_type)) {
                $jenisSampel = $sampleItem->name_sample_type;
                if (!in_array($jenisSampel, $jenisSampelUnik)) {
                  $jenisSampelUnik[] = $jenisSampel;
                }
              }
            }
          }

          // Jika belum dapat dari $samples, coba dari $table
          if (empty($jenisSampelUnik) && isset($table) && is_array($table)) {
            foreach ($table as $mytable) {
              if (isset($mytable['sample_type']) && isset($mytable['sample_type']->name_sample_type)) {
                $jenisSampel = $mytable['sample_type']->name_sample_type;
                if (!in_array($jenisSampel, $jenisSampelUnik)) {
                  $jenisSampelUnik[] = $jenisSampel;
                }
              }
            }
          }

          // Jika ada 2 jenis sampel berbeda, gunakan format dengan nomor lab minimum - maksimum
          $hasTwoJenisSampel = count($jenisSampelUnik) == 2;

          // Ambil nomor lab minimum dan maksimum dari NomerLabKesmas saja
          $minLabNum = null;
          $maxLabNum = null;

          // Ambil permohonan_uji_id dari sample pertama atau dari parameter
          $permohonanUjiId = null;
          if (isset($samples) && $samples->isNotEmpty()) {
            $firstSample = $samples->first();
            $permohonanUjiId = $firstSample->permohonan_uji_id ?? null;
          }

          // Fallback ke parameter $id_permohonan_uji jika tidak ada dari sample
          if (!$permohonanUjiId && isset($id_permohonan_uji)) {
            $permohonanUjiId = $id_permohonan_uji;
          }

          // Gunakan helper function untuk konsistensi
          if ($permohonanUjiId) {
            $labNums = $this->getMinMaxLabNumFromKesmas($permohonanUjiId);
            $minLabNum = $labNums['min'];
            $maxLabNum = $labNums['max'];
          }

          if(count($labNums) == 1){
            $no_register = "AB-AM.02"."/0".$labNums[0]."/2025";

            if ($hasTwoJenisSampel && $minLabNum !== null && $maxLabNum !== null) {
              $minLabNumFormatted = str_pad($minLabNum, 3, '0', STR_PAD_LEFT);
              $maxLabNumFormatted = str_pad($maxLabNum, 3, '0', STR_PAD_LEFT);

              // Jika min dan max sama, hanya tampilkan satu angka
              if ($minLabNum === $maxLabNum) {
                $no_agenda = !empty($agenda) ? '445.02/' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/' . $minLabNumFormatted . '/AB-AM/05.31/' . date("Y");
              } else {
                $no_agenda = !empty($agenda) ? '445.02/' . $agenda . ' - ' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/' . $minLabNumFormatted . ' - ' . $maxLabNumFormatted . '/AB-AM/05.31/' . date("Y");
              }
            } else {
              $no_agenda = !empty($agenda) ? '445.02/' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/AB-AM/05.31/' . date("Y");
            }

          }else{
            $no_register = "AB-AM.02"."/0".min($labNums)." - "."0".max($labNums)."/2025";

            if ($hasTwoJenisSampel && $minLabNum !== null && $maxLabNum !== null) {
              $minLabNumFormatted = str_pad($minLabNum, 3, '0', STR_PAD_LEFT);
              $maxLabNumFormatted = str_pad($maxLabNum, 3, '0', STR_PAD_LEFT);

              // Jika min dan max sama, hanya tampilkan satu angka
              if ($minLabNum === $maxLabNum) {
                $no_agenda = !empty($agenda) ? '445.02/' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/' . $minLabNumFormatted . '/AB-AM/05.31/' . date("Y");
              } else {
                $no_agenda = !empty($agenda) ? '445.02/' . $agenda . ' - ' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/' . $minLabNumFormatted . ' - ' . $maxLabNumFormatted . '/AB-AM/05.31/' . date("Y");
              }
            } else {
              $no_agenda = !empty($agenda) ? '445.02/' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/AB-AM/05.31/' . date("Y");
            }

          }

          $verificationActivitySamples = VerificationActivitySample::query()->where('id_sample', '=', $samples[0]->id_samples)->get();
          $listVerifications = [];
          foreach ($verificationActivitySamples as  $verificationActivitySample) {
            $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
          }

          $tembusan = Sample::where('id_samples', $samples[0]->id_samples)->get('tembusan')->first()->tembusan;

          $signOption = $request->signOption;


          arsort($params);
          if(isset($signOption) and $signOption == 0){
            $data = [
              "no_agenda" => $no_agenda,
              "no_register" => $no_register,
              "nama_pelanggan" => $customer->name_customer,
              "alamat_pelanggan" => $customer->address_customer,
              "permohonanuji" => json_decode(json_encode($permohonan_uji->toArray())),
              "sample" => json_decode(json_encode($sample->toArray())),
              "jenis_sampel" => "Air Higiene dan Air Minum",
              "jenis_sarana" => $jenis_sarana,
              "metode_pemeriksaan" => array_unique($metode_pemeriksaan),
              "petugas_sampling" => $permohonan_uji->name_sampling,
              "tanggal_sampling" => $tanggal_sampling,
              "parameter" => array_unique($params),
              "results" => $results,
              "pemeriksa" => $pemeriksa,
              "verifikator" => $verifikator,
              "validator" => $validator,
            ];





            $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih_air_minum', compact('data', 'listVerifications', 'tembusan', 'signOption', 'nomerLabDisplay'));
             return $this->streamPdfInline($pdf);
          }else{


            $url = config("app.url");
            if (config("app.env") == "local"){
              $url .= ":8000";
            }
            $url .= "/elits-signature/progress/".$sample->sample_id."/0";

            $result = Builder::create()
              ->data($url)
              ->encoding(new Encoding('UTF-8'))
              ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
              ->size(600)
              ->margin(2)
              ->build();

            $qrBase64 = base64_encode($result->getString());


            $data = [
              "no_agenda" => $no_agenda,
              "no_register" => $no_register,
              "nama_pelanggan" => $customer->name_customer,
              "alamat_pelanggan" => $customer->address_customer,
              "jenis_sampel" => "Air Higiene dan Air Minum",
              "jenis_sarana" => $jenis_sarana,
              "metode_pemeriksaan" => array_unique($metode_pemeriksaan),
              "petugas_sampling" => $permohonan_uji->name_sampling,
              "tanggal_sampling" => $tanggal_sampling,
              "parameter" => array_unique($params),
              "results" => $samples,
              "qr_code" => "",
              "pemeriksa" => $pemeriksa,
              "verifikator" => $verifikator,
              "validator" => $validator,
            ];

            $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih_air_minum', compact('data', 'listVerifications', 'tembusan', 'signOption', 'qrBase64', 'nomerLabDisplay'));

            // Jika BSRE tidak digunakan, langsung tampilkan PDF tanpa tanda tangan
            if (!config('app.bsre_use', false)) {
              return $pdf->stream();
            }

            // Ambil kredensial BSRE sekali pakai dari session (tidak disimpan DB)
            $sessionNik = session('bsre_nik');
            $sessionPass = session('bsre_password');
            // Hapus segera setelah dibaca
            session()->forget(['bsre_nik', 'bsre_password']);
            $dataKepalaLab = null;
            if ($sessionNik && $sessionPass) {
              $dataKepalaLab = (object)['nik' => $sessionNik, 'password' => $sessionPass];
            } else {
              // fallback lama (diupayakan tidak digunakan)
              $dataKepalaLab = Petugas::query()->where('nik', '=', '3309094611720002')->where('nama', '=', 'dr. Muharyati')->get(['nik', 'password'])->first();
            }

            if (!isset($agenda)){
              return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap input agenda!');
            }

            if (count($listVerifications) < 5){
              return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap selesaikan seluruh step verifikasi!');
            }

            if (isset($dataKepalaLab->nik) and isset($dataKepalaLab->password)){

              $dataPetugasBSRE[] = [
                'nik' => $dataKepalaLab->nik,
                'passPhrase' => $dataKepalaLab->password,
                'tampilan' => 'invisible',
                'reason' => "hasil12121",
                'location' => "boyolali",
                'text' => "hasil22323"
              ];

              $signBSRE = Smt::signBSRE($pdf, $dataPetugasBSRE);

              if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
                $data =  base64_encode($signBSRE["data"]["file"]);

                return view('masterweb::module.admin.laboratorium.sample.blob',
                  compact('data')
                );
              }elseif ($signBSRE['status'] == 500){
                return redirect()->back()->with('error-laporan', 'errors');
              }else{
                return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Silahkan coba lagi!');
              }

            }else{
              return redirect()->back()->with('error-bsre', 'Kredensial untuk tanda tangan elektronik belum lengkap. Harap lengkapi kredensial seluruh petugas!');
            }
          }
        }


        $labnumspartial = [];
        if(count($table) > count($samplePrint)){
          $filteredTable = array_filter($table, function($item) use ($samplePrint){
            return in_array($item['sample_type']->id_samples, $samplePrint);
          });

          $all_samples = $all_samples->filter(function ($sample) use ($samplePrint){
            return in_array($sample->id_samples, $samplePrint);
          });

          $table = $filteredTable;
          $lab_string = '/' . explode('/', $lab_string, 2)[1];

          foreach($table as $tbl){
            $labnumspartial[] = $tbl['sample_type']->count_id;
          }

          $labnumspartial = array_map(function($num) {
            return str_pad($num, 4, '0', STR_PAD_LEFT);
          }, $labnumspartial);

          if(count($labnumspartial) == 1){
            $labnumpartial = implode(',', $labnumspartial);
            $lab_string = $labnumpartial. $lab_string;
          }else{
            $maxlabnum = max($labnumspartial);
            $minlabnum = min($labnumspartial);

            $lab_string = $minlabnum.'-'.$maxlabnum. $lab_string;
          }
        }
      }
    }

    // Subset cetak via printSamples untuk Makanan/Mikro (jalur Air memakai blok khusus di atas).
    if (!in_array($sample->name_sample_type, ['Air Higiene', 'Air Bersih', 'Air Minum'], true)) {
      $printSamplesMikro = $request->input('printSamples');
      if ($printSamplesMikro !== null && $printSamplesMikro !== '') {
        if (!is_array($printSamplesMikro)) {
          $printSamplesMikro = [$printSamplesMikro];
        }
        $printSamplesMikro = array_values(array_filter(array_map('strval', $printSamplesMikro)));
        if (!empty($printSamplesMikro) && isset($table) && is_array($table)) {
          $tableRowCountBefore = count($table);
          $filteredForPrint = array_values(array_filter($table, function ($item) use ($printSamplesMikro) {
            $sid = data_get($item, 'sample_type.id_samples');

            return $sid && in_array((string) $sid, $printSamplesMikro, true);
          }));
          if (count($filteredForPrint) > 0 && count($filteredForPrint) < $tableRowCountBefore) {
            $table = $filteredForPrint;
            if (isset($all_samples) && $all_samples instanceof \Illuminate\Support\Collection) {
              $all_samples = $all_samples->filter(function ($sampleRow) use ($printSamplesMikro) {
                return isset($sampleRow->id_samples)
                  && in_array((string) $sampleRow->id_samples, $printSamplesMikro, true);
              })->values();
            }
            if (!empty($lab_string) && is_string($lab_string) && strpos($lab_string, '/') !== false) {
              $lab_string = '/' . explode('/', $lab_string, 2)[1];
              $labnumspartial = [];
              foreach ($table as $tbl) {
                if (isset($tbl['sample_type']->count_id)) {
                  $labnumspartial[] = $tbl['sample_type']->count_id;
                }
              }
              $labnumspartial = array_map(function ($num) {
                return str_pad((string) $num, 4, '0', STR_PAD_LEFT);
              }, $labnumspartial);
              if (count($labnumspartial) === 1) {
                $lab_string = $labnumspartial[0] . $lab_string;
              } elseif (count($labnumspartial) > 1) {
                $lab_string = min($labnumspartial) . '-' . max($labnumspartial) . $lab_string;
              }
            }
          }
        }
      }
    }

    $tembusan = Sample::where('id_samples', $sample->id_samples)->get('tembusan')->first()->tembusan;

    $sample_type = SampleType::findOrFail($sample_type_id);

    # [Baca Hasil] Ambil keterangan untuk metode pemeriksaan.
    $metode_pemeriksaan = [];

    foreach($table as $smp){

      if(isset($smp["sample_type"])){
        $metode = SampleResult::where('laboratorium_id', $sample->laboratorium_id)
          ->where('sample_id', $smp["sample_type"]->id_samples)
          ->whereNull('deleted_at')
          ->pluck('metode')->unique()->toArray();

        $metode_pemeriksaan = array_unique(array_merge($metode_pemeriksaan, $metode));

      }
    }


    // # Verifikasi 2
    $verificationActivitySamples = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->get();
    $listVerifications = [];
    foreach ($verificationActivitySamples as  $verificationActivitySample) {
      $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
    }
    // # Tanggal Pengambilan s.d.
    $tanggal_pengambilan = [];
    foreach ($table as $row) {
      $tanggal = data_get($row, 'sample_type.datesampling_samples');
      $tanggals = explode(' ', $tanggal);
      $tanggal_pengambilan[] = head($tanggals);
    }

    // dd($table);
    sort($tanggal_pengambilan);
    $tanggal_pengambilan = array_unique($tanggal_pengambilan);
    // dd($tanggal_pengambilan);
    // # Semi Hard Code total coliform.
    // foreach ($method_all as $index => $method) {
    //   if (\Illuminate\Support\Str::slug($method->name_report) === 'total-coliform') {
    //     $method_all->forget($index);
    //     $method_all->prepend($method);
    //     break;
    //   }
    // }
    // 1. Pendaftaran / Registrasi
    // 2. Pemeriksaan / Analitik
    // 3. Input / Output Hasil Px
    // 4. Verifikasi
    // 5. Validasi

    // dd($listVerifications);

    $url = config("app.url");
    if (config("app.env") == "local"){
      $url .= ":8000";
    }
    $url .= "/elits-signature/progress/".$sample->sample_id."/0";

    $result = Builder::create()
      ->data($url)
      ->encoding(new Encoding('UTF-8'))
      ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
      ->size(600)
      ->margin(2)
      ->build();

    $qrBase64 = base64_encode($result->getString());

    $fontsize = (float) (isset($request->fontsize) ? $request->fontsize : data_get($sample, 'fontsize_hasil_baca_hasil', 12.0));
    $lineHeight = (float) (isset($request->line_height) ? $request->line_height : data_get($sample, 'line_height_hasil_baca_hasil', 1.0));
    $padding = (float) (isset($request->padding) ? $request->padding : data_get($sample, 'padding_hasil_baca_hasil', 1.0));
    if (isset($request->show_kop)) {
      $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
    } else {
      $showKop = (int) data_get($sample, 'show_kop_hasil_baca_hasil', 1);
    }

    $colWidths = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::resolve(
      $sample,
      $request,
      null,
      $laboratorium ?? null
    );

    $signOption = $request->signOption;

    if (isset($signOption) and $signOption == 0){
      $isKuantitatif = true;

      // Initialize laboratoriummethodsArray if not already set
      if (!isset($laboratoriummethodsArray)) {
        $laboratoriummethodsArray = [];
      }

      $payloads = compact(
        'all_acuan_baku_mutu',
        'number_min',
        'number_max',
        'permohonan_uji',
        'table',
        'sample',
        'tembusan',
        'checking_min',
        'done_max',
        'diambil_min',
        'diambil_max',
        'laboratorium',
        'no_LHU',
        'all_samples',
        'lab_num',
        'lab_string',
        'method_all',
        'pengesahan_hasil',
        'lab_nums',
          'lab_num_per_page',
        'listVerifications',
        'metode_pemeriksaan',
        'tanggal_pengambilan',
        'agenda',
        'signOption',
        'pemeriksa',
        'verifikator',
        'validator',
        'isKuantitatif',
        'laboratoriummethodsArray',
        'fontsize',
        'lineHeight',
        'padding',
        'showKop',
        'nomerLabDisplay',
        'colWidths'
      );

      // Check for Air Limbah (contains check to support "Air Limbah Domestik" and "Air Limbah Industri")
      $sampleTypeLower = Str::lower(str_replace(' ', '', $sample_type->name_sample_type));
      if (strpos(Str::lower($sample_type->name_sample_type), 'air limbah') !== false) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_limbah', $payloads);
        return $this->streamPdfInline($pdf);
      }

      switch ($sampleTypeLower) {
        case Str::lower(str_replace(' ', '', "Air Minum")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_minum', $payloads);
          return $this->streamPdfInline($pdf);
        }

        case Str::lower(str_replace(' ', '', "Makanan/Minuman/Lainnya")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.alt_makan_minum', $payloads);
          return $this->streamPdfInline($pdf);
        }

        case Str::lower(str_replace(' ', '', "Uji Usap")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.uji_usap', $payloads);
          return $this->streamPdfInline($pdf);
        }

        case Str::lower(str_replace(' ', '', "Air Higiene")):
        case Str::lower(str_replace(' ', '', "Air Bersih")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih', $payloads);
          return $this->streamPdfInline($pdf);
        }

        case Str::lower(str_replace(' ', '', "Air Kolam Renang")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_kolam_renang', $payloads);
          return $this->streamPdfInline($pdf);
        }

        case Str::lower(str_replace(' ', '', "Kualitas Udara")): {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.kualitas_udara', $payloads);
          return $this->streamPdfInline($pdf);
        }

        default: {
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.mikro', $payloads);
          return $this->streamPdfInline($pdf);
        }
      }
    }

    $isKuantitatif = true;
    $allKualitatif = true;
    $hasMethods = false;

    foreach ($laboratoriummethodsArray as $methodGroup) {
      if (!isset($methodGroup['methods'])) {
        continue;
      }

      foreach ($methodGroup['methods'] as $method) {
        $hasMethods = true;
        $tipeNilaiBakuMutu = isset($method->tipe_nilai_baku_mutu)
          ? strtolower(trim((string) $method->tipe_nilai_baku_mutu))
          : '';

        if ($tipeNilaiBakuMutu !== 'kualitatif') {
          $allKualitatif = false;
          break 2;
        }
      }
    }

    if ($hasMethods && $allKualitatif) {
      $isKuantitatif = false;
    }

    // Initialize laboratoriummethodsArray if not already set
    if (!isset($laboratoriummethodsArray)) {
      $laboratoriummethodsArray = [];
    }

    $payloads = compact(
      'all_acuan_baku_mutu',
      'number_min',
      'number_max',
      'permohonan_uji',
      'table',
      'sample',
      'tembusan',
      'checking_min',
      'done_max',
      'diambil_min',
      'diambil_max',
      'laboratorium',
      'no_LHU',
      'all_samples',
      'lab_num',
      'lab_string',
      'method_all',
      'pengesahan_hasil',
      'lab_nums',
        'lab_num_per_page',
      'listVerifications',
      'metode_pemeriksaan',
      'tanggal_pengambilan',
      'agenda',
      'qrBase64',
      'pemeriksa',
      'verifikator',
      'validator',
      'isKuantitatif',
      'laboratoriummethodsArray',
      'fontsize',
      'lineHeight',
      'padding',
      'showKop',
      'nomerLabDisplay',
      'colWidths'
    );

    // Check for Air Limbah (contains check to support "Air Limbah Domestik" and "Air Limbah Industri")
    if (strpos(Str::lower($sample_type->name_sample_type), 'air limbah') !== false) {
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_limbah', $payloads);
    } else {
    switch (Str::lower(str_replace(' ', '', $sample_type->name_sample_type))) {
      case Str::lower(str_replace(' ', '', "Air Minum")):
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_minum', $payloads);
          break;

      case Str::lower(str_replace(' ', '', "Makanan/Minuman/Lainnya")):
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.alt_makan_minum', $payloads);
          break;

      case Str::lower(str_replace(' ', '', "Uji Usap")):
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.uji_usap', $payloads);
          break;

      case Str::lower(str_replace(' ', '', "Air Higiene")):
      case Str::lower(str_replace(' ', '', "Air Bersih")):
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih', $payloads);
          break;

      case Str::lower(str_replace(' ', '', "Kualitas Udara")):
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.kualitas_udara', $payloads);
          break;

      default:
          $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.mikro', $payloads);
          break;
      }
    }

    $dataKepalaLab = Petugas::query()->where('nik', '=', '3309094611720002')->where('nama', '=', 'dr. Muharyati')->get(['nik', 'password'])->first();

    if (!isset($agenda)){
      return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap input agenda!');
    }

    if (count($listVerifications) < 5){
      return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap selesaikan seluruh step verifikasi!');
    }

    if (isset($dataKepalaLab->nik) and isset($dataKepalaLab->password)){

      $dataPetugasBSRE[] = [
        'nik' => $dataKepalaLab->nik,
        'passPhrase' => $dataKepalaLab->password,
        'tampilan' => 'invisible',
        'reason' => "hasil12121",
        'location' => "boyolali",
        'text' => "hasil22323"
      ];

      $signBSRE = Smt::signBSRE($pdf, $dataPetugasBSRE);

      if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
        $data =  base64_encode($signBSRE["data"]["file"]);

        return view('masterweb::module.admin.laboratorium.sample.blob',
          compact('data')
        );
      }elseif ($signBSRE['status'] == 500){
        return redirect()->back()->with('error-laporan', 'errors');
      }else{
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Silahkan coba lagi!');
      }

    }else{
      return redirect()->back()->with('error-bsre', 'Kredensial untuk tanda tangan elektronik belum lengkap. Harap lengkapi kredensial seluruh petugas!');
    }

  }

  public function printKimia(Request $request,$id_permohonan_uji, $sample_type_id = null)
  {
    $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id_permohonan_uji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Initialize makmin variable
    $makmin = [];

    $sample_type = SampleType::where('id_sample_type', $sample_type_id)->first();

    // Fallback: jika $sample_type null (misal $sample_type_id salah), cari dari permohonan
    if (!$sample_type) {
      $sample_type = SampleType::whereIn('id_sample_type', function ($q) use ($id_permohonan_uji) {
        $q->select('typesample_samples')
          ->from('tb_samples')
          ->where('permohonan_uji_id', $id_permohonan_uji)
          ->whereNull('deleted_at');
      })->first();
    }

    // Check if there are samples with type "Makanan/Minuman/Lainnya" and lab "KIM"
    // First check if there are any samples with this type (without requiring method join)
    $makminTypeCheck = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->whereNull('tb_samples.deleted_at')
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->where('ms_sample_type.name_sample_type', '=', 'Makanan/Minuman/Lainnya')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->exists();

    // If Makanan/Minuman/Lainnya, use the makmin view with proper data structure
    if ($makminTypeCheck && $sample_type && $sample_type->name_sample_type == 'Makanan/Minuman/Lainnya') {
      // Get chemistry lab ID (KIM)
      $kimLab = Laboratorium::where('kode_laboratorium', 'KIM')->whereNull('deleted_at')->first();
      if (!$kimLab) {
        return abort(404, 'Laboratorium Kimia tidak ditemukan');
      }
      $idlab = $kimLab->id_laboratorium;

      // Get all samples for this permohonan uji with type Makanan/Minuman/Lainnya
      // First get all samples with this type, then filter those that have KIM lab methods
      $allMakminSamples = Sample::query()
        ->where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->whereNull('tb_samples.deleted_at')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->where('ms_sample_type.name_sample_type', '=', 'Makanan/Minuman/Lainnya')
            ->whereNull('ms_sample_type.deleted_at');
        })
        ->join('tb_permohonan_uji', function ($join) {
          $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
            ->whereNull('tb_permohonan_uji.deleted_at');
        })
        ->join('ms_customer', function ($join) {
          $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
            ->whereNull('ms_customer.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*', 'ms_customer.*', 'tb_permohonan_uji.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'ASC')
        ->get();

      // Filter samples that have KIM lab methods (batch, avoid exists() per sample)
      $allMakminSampleIds = $allMakminSamples->pluck('id_samples')->filter()->unique()->values()->all();
      $sampleIdsWithKimMethod = [];
      if (!empty($allMakminSampleIds)) {
        $sampleIdsWithKimMethod = SampleMethod::whereIn('sample_id', $allMakminSampleIds)
          ->where('laboratorium_id', $idlab)
          ->whereNull('deleted_at')
          ->pluck('sample_id')
          ->unique()
          ->values()
          ->all();
      }
      $samples = $allMakminSamples->whereIn('id_samples', $sampleIdsWithKimMethod)->values();

      // Filter by printSamples if provided
      $printSamples = $request->input('printSamples', []);
      if (!empty($printSamples)) {
        // Handle both array and single value
        if (!is_array($printSamples)) {
          $printSamples = [$printSamples];
        }
        // Convert to array of strings for comparison
        $printSamples = array_map('strval', $printSamples);
        $samples = $samples->filter(function ($sample) use ($printSamples) {
          return in_array((string)$sample->id_samples, $printSamples, true);
        })->values();
      }

      // Only process if we have samples with KIM lab methods
      if ($samples->isNotEmpty()) {
        $labnums = [];
        $laboratoriummethodsArray = [];
        $no_lhus = [];
        $param_methods = [];
        $validasi = [];
        $tembusans = [];

        // Cache is_tambahan per sample type + method to avoid repeated lookup
        $sampleTypeDetailCache = [];

        foreach ($samples as $sample) {
          $sampletype_id = $sample->id_sample_type;
          $id = $sample->id_samples;

          $lab_num = LabNum::where('sample_id', $sample->id_samples)
            ->where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->where('sample_type_id', $sample->typesample_samples)
            ->first();

          if (!$lab_num) {
            continue; // Skip if no lab_num
          }

          // Get laboratorium methods for this sample from tb_sample_method
          $laboratoriummethods = SampleMethod::where('tb_sample_method.sample_id', '=', $id)
            ->where('tb_sample_method.laboratorium_id', '=', $idlab)
            ->whereNull('tb_sample_method.deleted_at')
            ->join('ms_method', function ($join) {
              $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('ms_sample_type_detail', function ($join) use ($sampletype_id) {
              $join->on('ms_sample_type_detail.method_id', '=', 'tb_sample_method.method_id')
                ->where('ms_sample_type_detail.sample_type_id', '=', $sampletype_id)
                ->whereNull('ms_sample_type_detail.deleted_at');
            })
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $sample, $idlab) {
              $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->where('tb_baku_mutu.lab_id', '=', $idlab)
                ->whereNull('tb_baku_mutu.deleted_at');
              // Prioritas: baku mutu dengan jenis_makanan_id spesifik ATAU generic (null)
              if ($sample->jenis_makanan_id) {
                $join->whereRaw('(tb_baku_mutu.jenis_makanan_id = ? OR tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)', [$sample->jenis_makanan_id, '']);
              } else {
                $join->whereRaw('(tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = ?)', ['']);
              }
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at');
            })
            ->orderByRaw('COALESCE(ms_sample_type_detail.orderlist_sample_type_detail, 999) ASC')
            // Prioritaskan baku mutu spesifik (jenis_makanan_id tidak null) agar deduplication ambil yang benar
            ->orderByRaw('CASE WHEN tb_baku_mutu.jenis_makanan_id IS NULL OR tb_baku_mutu.jenis_makanan_id = \'\' THEN 1 ELSE 0 END ASC')
            ->select(
              'tb_baku_mutu.*',
              'ms_method.*',
              'tb_sample_method.*',
              'tb_sample_result.hasil',
              'tb_sample_result.keterangan',
              'tb_sample_result.metode',
              'tb_sample_result.offset_baku_mutu',
              'ms_sample_type_detail.is_tambahan',
              'ms_sample_type_detail.orderlist_sample_type_detail',
              'unit_baku_mutu.shortname_unit',
              'unit_baku_mutu.name_unit',
              'unit_baku_mutu.id_unit as unit_baku_mutu_id',
              DB::raw('tb_baku_mutu.nilai_baku_mutu as nilai_baku_mutu'),
              DB::raw('tb_baku_mutu.min as min'),
              DB::raw('tb_baku_mutu.max as max'),
              DB::raw('tb_baku_mutu.equal as equal'),
              DB::raw('tb_baku_mutu.unit_id as unit_id'),
              DB::raw('tb_baku_mutu.id_baku_mutu as id_baku_mutu'),
              DB::raw('tb_baku_mutu.tipe_nilai_baku_mutu as tipe_nilai_baku_mutu'),
              DB::raw('tb_baku_mutu.library_id as library_id'),
              DB::raw('tb_baku_mutu.jenis_makanan_id as jenis_makanan_id')
            )
            ->get();

          // Deduplicate by id_method — ambil satu row per metode (prioritas: yang punya baku mutu matching jenis_makanan_id spesifik)
          $jenisMakananIdSample = $sample->jenis_makanan_id;
          $laboratoriummethods = $laboratoriummethods
            ->sortBy(function ($m) use ($jenisMakananIdSample) {
              // Prioritas 0: exact match, 1: generic null, 2: tidak ada baku mutu
              if ($m->jenis_makanan_id === $jenisMakananIdSample && $jenisMakananIdSample !== null) return 0;
              if (is_null($m->jenis_makanan_id)) return 1;
              return 2;
            })
            ->groupBy('id_method')
            ->map(function ($group) {
              return $group->first();
            })
            ->values();

          $sample_type_details_for_sort = SampleTypeDetail::where('sample_type_id', $sampletype_id)
            ->orderBy('orderlist_sample_type_detail')
            ->get();
          $laboratoriummethods = kesmas_sort_laboratorium_methods($laboratoriummethods, $sample_type_details_for_sort);

          // Prefetch hasil/detail per sample agar tidak query per method
          $sampleResultDetailsByMethod = SampleResultDetail::where('sampletype_id', '=', $sampletype_id)
            ->where('sample_id', '=', $id)
            ->get()
            ->groupBy('method_id');
          $sampleResultsByMethod = SampleResult::where('laboratorium_id', '=', $idlab)
            ->where('sample_id', '=', $id)
            ->get()
            ->keyBy('method_id');

          foreach ($laboratoriummethods as $key => $laboratoriummethod) {
            array_push($param_methods, $laboratoriummethod->params_method);

            $laboratoriummethods[$key]->detail = array();

            $sampleTypeDetailKey = $sampletype_id . '|' . $laboratoriummethod->id_method;
            if (!array_key_exists($sampleTypeDetailKey, $sampleTypeDetailCache)) {
              $sampleTypeDetailCache[$sampleTypeDetailKey] = SampleTypeDetail::where('method_id', '=', $laboratoriummethod->id_method)
                ->where('ms_sample_type_detail.sample_type_id', $sampletype_id)
                ->first();
            }
            $sampleTypeDetail = $sampleTypeDetailCache[$sampleTypeDetailKey];

            if ($sampleTypeDetail) {
              $laboratoriummethods[$key]->is_tambahan = $sampleTypeDetail->is_tambahan;
              $laboratoriummethods[$key]->orderlist_sample_type_detail = (int) $sampleTypeDetail->orderlist_sample_type_detail;
            } else {
              $laboratoriummethods[$key]->is_tambahan = 0;
              $laboratoriummethods[$key]->orderlist_sample_type_detail = isset($laboratoriummethod->orderlist_sample_type_detail)
                ? (int) $laboratoriummethod->orderlist_sample_type_detail
                : null;
            }

            $resolvedMethodId = kesmas_lhu_resolve_method_id($laboratoriummethod);

            $laboratoriummethods[$key]->detail = $sampleResultDetailsByMethod->get($resolvedMethodId, collect())->values();

            $sample_result = $sampleResultsByMethod->get($resolvedMethodId);

            $laboratoriummethods[$key]->hasil = kesmas_lhu_resolve_hasil_for_print($laboratoriummethod, $sample_result);
            $laboratoriummethods[$key]->keterangan = isset($sample_result->keterangan) ? $sample_result->keterangan : "-";
            $laboratoriummethods[$key]->metode = kesmas_resolve_metode_for_print($laboratoriummethod, $sample_result);

            if (count($laboratoriummethods[$key]->detail) == 0) {
              $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=', $laboratoriummethod->id_method)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('baku_mutu_id', '=', $laboratoriummethod->id_baku_mutu ?? null)->get();

              if (count($bakuMutuDetailParameterNonKliniks) > 0) {
                $all_array = [];
                foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {
                  $array = [
                    "id_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->id_baku_mutu_detail_parameter_non_klinik,
                    "method_id" => $laboratoriummethod->id_method,
                    "sample_id" => $laboratoriummethod->sample_id ?? $id,
                    "is_tambahan" => $laboratoriummethods[$key]->is_tambahan,
                    "sampletype_id" => $sampletype_id,
                    "lab_id" => $idlab,
                    "name_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik,
                    "min_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik,
                    "max_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik,
                    "equal_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik,
                    "nilai_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik,
                    "hasil" => "-",
                    "offset_baku_mutu" => "false",
                    "created_at" => $bakuMutuDetailParameterNonKlinik->created_at,
                    "updated_at" => $bakuMutuDetailParameterNonKlinik->updated_at,
                    "deleted_at" => $bakuMutuDetailParameterNonKlinik->deleted_at
                  ];
                  array_push($all_array, $array);
                }
                $laboratoriummethods[$key]->detail = $all_array;
              }
            }
          }

          // Apply per-sample baku mutu overrides dari tb_baku_mutu_sample_override
          $laboratoriummethods = $this->applyBakuMutuSampleOverridesToMethods(
            $laboratoriummethods,
            (string) $id,
            (string) $idlab
          );

          // Get LHU number
          $no_LHU = LHU::where('sample_id', '=', $id)->where('lab_id', '=', $idlab)->first();

          // Ambil nomer lab KESMAS per-lab (bukan per-sampel)
          $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $sample->permohonan_uji_id ?? null)
            ->where('laboratorium_id', $idlab)
            ->first();
          $kesmasLabNumber = $nomerLabKesmas ? $nomerLabKesmas->nomer_lab : null;
          $agenda = $request->input('agenda');

          if (!isset($no_LHU)) {
            $no_LHU = new LHU;
            $uuid4 = Uuid::uuid4();
            $no_LHU_urutan = LHU::max('nomer_urut_LHU');
            $no_LHU->id_lhu = $uuid4->toString();
            $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
            $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

            // Hanya gunakan nomer lab KESMAS, tidak ada fallback
            $labNumber = $kesmasLabNumber;

              // Simpan nomor lengkap di database: 445.01/{nomer_lab}/{jenis_sampel}/05.31/{tahun}
              // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
              if ($labNumber) {
                $no_LHU->nomer_LHU = '445.01/' . $labNumber . '/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
              } else {
                $no_LHU->nomer_LHU = '445.01/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
              }
            $no_LHU->sample_id = $id;
            $no_LHU->lab_id = $idlab;
            $no_LHU->save();

            $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
            $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');

            // Tampilkan selalu dengan nomor lab:
            // - jika agenda diisi, pakai agenda
            // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
            $nomerLab = !empty($agenda)
              ? $agenda
              : $kesmasLabNumber;

             // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
              if ($nomerLab) {
                $no_LHU = '449.5/01/' . $nomerLab . '/' . $year;
              } else {
                $no_LHU = '449.5/01/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $year;
              }
          } else {
            $pengesahan_hasil = PengesahanHasil::where('sample_id', '=', $id)->where('laboratorium_id', '=', $idlab)->first();
            if (isset($pengesahan_hasil)) {
              $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m'));
              $year = Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('Y');
            } else {
              $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
              $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');
            }

            // Tampilkan selalu dengan nomor lab:
            // - jika agenda diisi, pakai agenda
            // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
            $nomerLab = !empty($agenda)
              ? $agenda
              : $kesmasLabNumber;

             // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
              if ($nomerLab) {
                $no_LHU = '449.5/01/' . $nomerLab . '/' . $year;
              } else {
                $no_LHU = '449.5/01/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $year;
              }
          }

          $analytic = VerificationActivitySample::query()
            ->where('id_sample', '=', $sample->id_samples)
            ->where('id_verification_activity', '=', 2)
            ->first();
          $valid = VerificationActivitySample::query()
            ->where('id_sample', '=', $sample->id_samples)
            ->where('id_verification_activity', '=', 5)
            ->first();

          $arr = [];

          // Get jenis sarana and jenis makanan names
          $jenisSarana = $sample->jenis_sarana_names ?? '';
          $namaJenisMakanan = '';
          if ($sample->jenis_makanan_id) {
            $jenisMakananObj = JenisMakanan::where('id_jenis_makanan', $sample->jenis_makanan_id)->first();
            $namaJenisMakanan = $jenisMakananObj ? $jenisMakananObj->name_jenis_makanan : '';
          }

          // Get address from sample or permohonanuji->customer
          $alamatSampel = '';
          if (isset($sample->address_customer) && $sample->address_customer !== '') {
            $alamatSampel = $sample->address_customer;
          } elseif (isset($sample->kecamatan_customer) && $sample->kecamatan_customer !== '') {
            $alamatSampel = $sample->kecamatan_customer;
          } elseif ($sample->permohonanuji && optional($sample->permohonanuji->customer)->address_customer) {
            $alamatSampel = $sample->permohonanuji->customer->address_customer;
          }

          // Get pengirim from sample or permohonanuji
          $pengirim = $sample->name_send_sample ?? '';
          if (empty($pengirim)) {
            $pengirim = $sample->namaPengambilDisplay('');
          }
          if (empty($pengirim)) {
            $pengirim = 'Petugas Disdagkop & UKM';
          }

          $labSamples = [
            "jenis_sarana" => $jenisSarana,
            "titik_pengambilan" => $sample->titik_pengambilan ?? '',
            "nama_jenis_makanan" => $namaJenisMakanan,
            "lab_num" => $lab_num->lab_number,
            "codesample_samples" => $sample->codesample_samples ?? '',
            "date_sending" => $sample->date_sending,
            "date_analytic" => isset($analytic) ? $analytic->stop_date : null,
            "alamat_sampel" => $alamatSampel,
            "pengirim" => $pengirim
          ];

          $arr = [
            "methods" => $laboratoriummethods,
            "sample_info" => $labSamples
          ];

          array_push($labnums, $lab_num);
          array_push($laboratoriummethodsArray, $arr);
          array_push($no_lhus, $no_LHU);
          array_push($tembusans, $sample->tembusan ?? '');
          if (isset($valid)) {
            array_push($validasi, $valid);
        }
        }

        $no_LHU = $no_lhus[0] ?? '';

        // Get unique param methods and sort by orderlist_sample_type_detail
        $param_methods = array_unique($param_methods);

        // Sort param_methods by orderlist_sample_type_detail from ms_sample_type_detail
        if (!empty($param_methods) && !empty($samples)) {
          $firstSample = $samples->first();
          $sampletype_id = $firstSample->id_sample_type;

          // Get all methods with their order from sample_type_detail
          $sampleTypeDetails = SampleTypeDetail::where('sample_type_id', $sampletype_id)
            ->orderBy('orderlist_sample_type_detail', 'asc')
            ->join('ms_method', function ($join) {
              $join->on('ms_method.id_method', '=', 'ms_sample_type_detail.method_id')
                ->whereNull('ms_method.deleted_at');
            })
            ->select('ms_method.params_method', 'ms_sample_type_detail.orderlist_sample_type_detail')
            ->get();

          $orderedParams = [];
          $remainingParams = $param_methods;

          // Add params in order from sample_type_detail
          foreach ($sampleTypeDetails as $detail) {
            if (in_array($detail->params_method, $param_methods)) {
              $orderedParams[] = $detail->params_method;
              $remainingParams = array_diff($remainingParams, [$detail->params_method]);
            }
          }

          // Add any remaining params that weren't in sample_type_detail
          $param_methods = array_merge($orderedParams, array_values($remainingParams));
        } else {
          $param_methods = array_values($param_methods);
        }

        if (count($validasi) == count($samples)) {
          $validasi = $validasi[count($samples) - 1];
        } else {
          $validasi = null;
        }

        $tembusans = $tembusans[count($samples) - 1] ?? '';

        // Get signatures
        $firstSample = $samples->first();
        $verificationActivitySample = VerificationActivitySample::where('id_sample', $firstSample->id_samples)
          ->whereIn('id_verification_activity', [2, 4, 5])
          ->get()
          ->keyBy('id_verification_activity');

        $validatorName = optional($verificationActivitySample->get(5))->nama_petugas ?? '-';
        $verifikatorName = optional($verificationActivitySample->get(4))->nama_petugas ?? '-';
        $pemeriksaName = optional($verificationActivitySample->get(2))->nama_petugas ?? '-';

        $validator = $this->searchPetugas($validatorName);
        $verifikator = $this->searchPetugas($verifikatorName);
        $pemeriksa = $this->searchPetugas($pemeriksaName);

        $signOption = $request->input('signOption', 0);
        $version = $request->input('version', 'default'); // 'default' or 'alt'

        // Get first sample for single sample references in view
        $sample = $samples->first();
        $fontsize = (float) (isset($request->fontsize) ? $request->fontsize : data_get($sample, 'fontsize_hasil_baca_hasil', 12.0));
        $lineHeight = (float) (isset($request->line_height) ? $request->line_height : data_get($sample, 'line_height_hasil_baca_hasil', 1.0));
        $padding = (float) (isset($request->padding) ? $request->padding : data_get($sample, 'padding_hasil_baca_hasil', 1.0));
        if (isset($request->show_kop)) {
          $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
        } else {
          $showKop = (int) data_get($sample, 'show_kop_hasil_baca_hasil', 1);
        }

        // Choose view based on version parameter
       $viewName = ($version == 'default'
          ? 'masterweb::module.admin.laboratorium.sample.formatPrint.kimia.alt_makmin'
          : 'masterweb::module.admin.laboratorium.sample.formatPrint.kimia.makmin');

        // Format kuantitatif dipakai jika ada parameter non-kualitatif.
        // Semua parameter tipe_nilai_baku_mutu = 'kualitatif' → tabel tanpa kolom Satuan.
        $isKuantitatif = true;
        $allKualitatif = true;
        $hasMethods = false;

        foreach ($laboratoriummethodsArray as $methodGroup) {
          if (!isset($methodGroup['methods'])) {
            continue;
          }

          foreach ($methodGroup['methods'] as $method) {
            $hasMethods = true;
            $tipeNilaiBakuMutu = isset($method->tipe_nilai_baku_mutu)
              ? strtolower(trim((string) $method->tipe_nilai_baku_mutu))
              : '';

            if ($tipeNilaiBakuMutu !== 'kualitatif') {
              $allKualitatif = false;
              break 2;
            }
          }
        }

        if ($hasMethods && $allKualitatif) {
          $isKuantitatif = false;
        }


        // Kumpulkan semua library_id dari metode yang sudah diproses untuk acuan baku mutu
        $allLibraryIds = collect();
        foreach ($laboratoriummethodsArray as $methodGroup) {
          foreach ($methodGroup['methods'] as $method) {
            if (isset($method->library_id) && $method->library_id) {
              $allLibraryIds->push($method->library_id);
            }
          }
        }
        $allLibraryIds = $allLibraryIds->unique()->filter()->values();
        $all_acuan_baku_mutu = Library::whereIn('id_library', $allLibraryIds->toArray())
          ->orderBy('title_library')
          ->get();

        $pdf = PDF::loadView($viewName, compact(
          'no_LHU',
          'samples',
          'sample',
          'labnums',
          'laboratoriummethodsArray',
          'param_methods',
          'tembusans',
          'validasi',
          'signOption',
          'validator',
          'verifikator',
          'pemeriksa',
          'fontsize',
          'lineHeight',
          'padding',
          'showKop',
          'permohonan_uji',
          'isKuantitatif',
          'all_acuan_baku_mutu'
        ));

        return $pdf->stream();
      } // End of if ($samples->isNotEmpty())
    } // End of if ($makminTypeCheck)






    // dd($method_all);

    $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
      // ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
      // ->orderBy('ms_method.created_at')

      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })

      ->leftjoin('tb_sample_result', function ($join) {
        $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
          ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('tb_samples', function ($join) {
        $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

          ->whereNull('tb_samples.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
          ->where('ms_laboratorium.kode_laboratorium', 'KIM')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_laboratorium_method.deleted_at');
      })
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_baku_mutu', function ($join) {
        $join->on('tb_samples.typesample_samples', 'tb_baku_mutu.sampletype_id')
          ->on('tb_baku_mutu.method_id', '=', 'tb_sample_method.method_id')

          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })

      ->join('ms_library', function ($join) {
        $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
          ->whereNull('ms_library.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->distinct('ms_library.id_library')
      ->select('ms_library.*')
      ->get();

    // dd($all_acuan_baku_mutu);


    if (count($makmin) > 0) {
      $all_samples = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->where('name_sample_type', 'Makanan/Minuman/Lainnya')
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*', 'tb_sample_penanganan.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'ASC')
        ->get();

      $sample_type = SampleType::where('name_sample_type', 'Makanan/Minuman/Lainnya')->first();

      $lab_num_max = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
        ->orderBy('lab_number', 'asc')
        ->first();

      $lab_num_min = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
        ->orderBy('lab_number', 'desc')
        ->first();
      if (isset($lab_num_max->lab_number) && isset($lab_num_min->lab_number)) {
        if ((int)$lab_num_max->lab_number == (int)$lab_num_min->lab_number) {
          $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number);
        } else {
          $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number) . "-" . sprintf("%04d", (int)$lab_num_max->lab_number);
        }
        if (isset($lab_num_max->lab_string) && $lab_num_max->lab_string != "") {
          $lab_string = $lab_num_max->lab_string;
        } else {
          $lab_string = $lab_num . '/Mak-KIM/' . getRomawi((int)$lab_num_max->mount_lab_num) . '/' . (int)$lab_num_max->year_lab_num;


          $lab_num_max = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
            ->orderBy('lab_number', 'asc')
            ->get();


          foreach ($lab_num_max as $item) {
            # code...
            $lab_num = LabNum::findOrFail($item->id_lab_num);
            $lab_num->lab_string = $lab_string;
            $lab_num->save();
          }
        }
      } else {
        $lab_string = null;
        $lab_num = null;
      }
      $lab_string = $lab_num;
    } else {
      $all_samples = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->orderBy('tb_samples.count_id', 'ASC')
        ->where('name_sample_type', 'PUDAM SISA CHLOR + FISIKA')
        ->leftjoin('tb_sample_penanganan', function ($join) {
          $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*', 'tb_sample_penanganan.*')
        ->distinct('tb_samples.id_samples')
        ->get();
      $sample_type = SampleType::where('name_sample_type', 'PUDAM SISA CHLOR + FISIKA')->first();

      $lab_num_max = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
        ->orderBy('lab_number', 'asc')
        ->first();

      $lab_num_min = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
        ->orderBy('lab_number', 'desc')
        ->first();
      if (isset($lab_num_max->lab_number) && isset($lab_num_min->lab_number)) {
        if ((int)$lab_num_max->lab_number == (int)$lab_num_min->lab_number) {
          $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number);
        } else {
          $lab_num = sprintf("%04d", (int)$lab_num_min->lab_number) . "-" . sprintf("%04d", (int)$lab_num_max->lab_number);
        }
        if (isset($lab_num_max->lab_string) && $lab_num_max->lab_string != "") {
          $lab_string = $lab_num_max->lab_string;
        } else {
          $lab_string = $lab_num . '/Mak-KIM/' . getRomawi((int)$lab_num_max->mount_lab_num) . '/' . (int)$lab_num_max->year_lab_num;


          $lab_num_max = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
            ->orderBy('lab_number', 'asc')
            ->get();


          foreach ($lab_num_max as $item) {
            # code...
            $lab_num = LabNum::findOrFail($item->id_lab_num);
            $lab_num->lab_string = $lab_string;
            $lab_num->save();
          }
          $lab_num = sprintf("%04d", (int)$lab_num->lab_number);
        }
      } else {
        $lab_string = null;
        $lab_num = null;
      }
    }

    if (count($makmin) > 0) {
      // $method_all = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      //   ->where('ms_laboratorium.kode_laboratorium', 'KIM')
      //   ->where('tb_samples.typesample_samples', 'd34b4a50-4560-4fce-96c3-046c7080a986')
      //   ->join('tb_sample_method', function ($join) {
      //     $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
      //       ->whereNull('tb_sample_method.deleted_at')
      //       ->whereNull('tb_samples.deleted_at');
      //   })
      //   ->join('ms_method', function ($join) {
      //     $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
      //       ->whereNull('ms_method.deleted_at')
      //       ->whereNull('tb_sample_method.deleted_at');
      //   })
      //   ->join('tb_baku_mutu', function ($join) {
      //     $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
      //       ->on('tb_baku_mutu.sampletype_id', '=', 'tb_samples.typesample_samples')
      //       ->whereNull('tb_baku_mutu.deleted_at')
      //       ->whereNull('ms_method.deleted_at');
      //   })
      //   ->join('ms_unit as unit_baku_mutu', function ($join) {
      //     $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
      //       ->whereNull('unit_baku_mutu.deleted_at')
      //       ->whereNull('tb_baku_mutu.deleted_at');
      //   })
      //   ->join('ms_laboratorium', function ($join) {
      //     $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
      //       ->whereNull('ms_laboratorium.deleted_at')
      //       ->whereNull('tb_sample_method.deleted_at');
      //   })
      //   ->select('ms_method.*')
      //   ->distinct('ms_method.id_method')
      //   ->get();
      $where_in = [];
      foreach ($all_samples as $sample_one) {
        # code...
        array_push($where_in, $sample_one->jenis_makanan_id);
      }
      $method_all = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', 'd34b4a50-4560-4fce-96c3-046c7080a986')
        ->whereIn('tb_baku_mutu.jenis_makanan_id', $where_in)
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_method', function ($join) use ($where_in) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at')
            ->whereNull('tb_sample_method.deleted_at')
            ->join('tb_baku_mutu', function ($join) use ($where_in) {
              $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.sampletype_id', '=', 'd34b4a50-4560-4fce-96c3-046c7080a986')
                ->where('tb_baku_mutu.jenis_makanan_id', '=', $where_in)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->join('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            });
        })


        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->select(
          'ms_method.*',
          'unit_baku_mutu.*',
          'tb_baku_mutu.*'
        )
        ->distinct('ms_method.id_method')
        ->get();
    } else {
      $method_all = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', '0e9a1091-b248-44e9-921b-5b4773109603')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })

        ->join('tb_baku_mutu', function ($join) {
          $join->on('tb_baku_mutu.method_id', '=', 'tb_sample_method.method_id')
            ->where('tb_baku_mutu.sampletype_id', '=', "0e9a1091-b248-44e9-921b-5b4773109603")
            ->whereNull('tb_baku_mutu.deleted_at')
            ->whereNull('tb_samples.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->join('ms_unit as unit_baku_mutu', function ($join) {
          $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
            ->whereNull('unit_baku_mutu.deleted_at')
            ->whereNull('tb_baku_mutu.deleted_at');
        })
        ->select('ms_method.*', 'unit_baku_mutu.*', 'tb_baku_mutu.*')
        ->distinct('ms_method.id_method')
        ->get();
    }
    $lab_string = $lab_num;


    if (count($makmin) > 0) {

      $all_samples_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', 'd34b4a50-4560-4fce-96c3-046c7080a986')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'DESC')
        ->first();

      $all_samples_max_is_manual = false;
      if (isset($all_samples_max)) {
        $all_samples_max_is_manual = (bool) $all_samples_max->is_nomor_sampel_manual;
        $all_samples_max = $all_samples_max->codesample_samples;
      }


      $all_samples_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', 'd34b4a50-4560-4fce-96c3-046c7080a986')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'ASC')
        ->first();

      $all_samples_min_is_manual = false;
      if (isset($all_samples_min)) {
        $all_samples_min_is_manual = (bool) $all_samples_min->is_nomor_sampel_manual;
        $all_samples_min = $all_samples_min->codesample_samples;
      }
    } else {


      $all_samples_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', '0e9a1091-b248-44e9-921b-5b4773109603')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'DESC')
        ->first();

      $all_samples_max_is_manual = false;
      if (isset($all_samples_max)) {
        $all_samples_max_is_manual = (bool) $all_samples_max->is_nomor_sampel_manual;
        $all_samples_max = $all_samples_max->codesample_samples;
      }

      $all_samples_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
        ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        ->where('tb_samples.typesample_samples', '0e9a1091-b248-44e9-921b-5b4773109603')
        ->join('tb_sample_method', function ($join) {
          $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })

        ->join('ms_laboratorium', function ($join) {
          $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
            ->whereNull('ms_laboratorium.deleted_at')
            ->whereNull('tb_sample_method.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->select('tb_samples.*', 'ms_sample_type.*')
        ->distinct('tb_samples.id_samples')
        ->orderBy('tb_samples.count_id', 'ASC')
        ->first();

      $all_samples_min_is_manual = false;
      if (isset($all_samples_min)) {
        $all_samples_min_is_manual = (bool) $all_samples_min->is_nomor_sampel_manual;
        $all_samples_min = $all_samples_min->codesample_samples;
      }
    }



    // dd($all_samples_max->codesample_samples." ".$all_samples_min->codesample_samples);

    $table = [];


    foreach ($all_samples as $sample) {
      $sample_one = [];
      $sample_one["sample_type"] = $sample;
      $sample_one["result"] = [];
      $jenis_makanan_id = $sample->jenis_makanan_id;
      $sample_one["jenis_makanan"] = JenisMakanan::where('id_jenis_makanan', $jenis_makanan_id)->first();

      foreach ($method_all as $method) {

        $sampletype = $sample->typesample_samples;

        $id_method = $method->id_method;
        if (isset($jenis_makanan_id)) {
          $result_one = SampleResult::where('tb_sample_result.sample_id', '=', $sample->id_samples)
            ->where('ms_laboratorium.kode_laboratorium', 'KIM')
            ->where('tb_samples.typesample_samples', $sampletype)
            ->where('tb_sample_result.method_id', $method->id_method)
            ->where('tb_samples.jenis_makanan_id', $jenis_makanan_id)
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_samples', function ($join) {
              $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
                ->whereNull('tb_samples.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_sample_method', function ($join) use ($id_method) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->where('tb_sample_method.method_id', $id_method)
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_analitik_progress', function ($join) {
              $join->on('tb_sample_analitik_progress.sample_id', '=', 'tb_sample_result.sample_id')
                ->whereNull('tb_sample_analitik_progress.deleted_at')
                ->whereNull('tb_sample_result.deleted_at')
                ->where('tb_sample_analitik_progress.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->where('tb_sample_analitik_progress.laboratorium_progress_id', 'bc2850f5-4ec4-450f-a727-2b1428c861d9');
            })
            ->join('ms_method', function ($join) use ($id_method, $sampletype, $jenis_makanan_id) {
              $join->where('ms_method.id_method', '=', $id_method)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at')
                ->join('tb_baku_mutu', function ($join) use ($sampletype, $id_method, $jenis_makanan_id) {
                  $join
                    ->where('tb_baku_mutu.method_id', '=', $id_method)
                    ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                    ->where('tb_baku_mutu.sampletype_id', '=', $sampletype)
                    ->whereNull('tb_baku_mutu.deleted_at')
                    ->whereNull('ms_method.deleted_at');
                })
                ->join('ms_unit as unit_baku_mutu', function ($join) {
                  $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                    ->whereNull('unit_baku_mutu.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
                });
            })

            ->orderBy('tb_sample_result.created_at', 'asc')
            ->first();
        } else {

          $result_one = SampleResult::where('tb_sample_result.sample_id', '=', $sample->id_samples)
            ->where('ms_laboratorium.kode_laboratorium', 'KIM')
            ->where('tb_samples.typesample_samples', $sampletype)
            ->where('tb_sample_result.method_id', $method->id_method)
            ->join('ms_laboratorium', function ($join) {
              $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_samples', function ($join) {
              $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
                ->whereNull('tb_samples.deleted_at')
                ->whereNull('tb_sample_result.deleted_at');
            })
            ->join('tb_sample_method', function ($join) use ($id_method) {
              $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                ->where('tb_sample_method.method_id', $id_method)
                ->whereNull('tb_sample_method.deleted_at')
                ->whereNull('tb_samples.deleted_at');
            })
            ->leftjoin('tb_sample_analitik_progress', function ($join) {
              $join->on('tb_sample_analitik_progress.sample_id', '=', 'tb_sample_result.sample_id')
                ->whereNull('tb_sample_analitik_progress.deleted_at')
                ->whereNull('tb_sample_result.deleted_at')
                ->where('tb_sample_analitik_progress.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                ->where('tb_sample_analitik_progress.laboratorium_progress_id', 'bc2850f5-4ec4-450f-a727-2b1428c861d9');
            })
            ->join('ms_method', function ($join) use ($id_method, $sampletype) {
              $join->where('ms_method.id_method', '=', $id_method)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at')
                ->join('tb_baku_mutu', function ($join) use ($sampletype, $id_method) {
                  $join
                    ->where('tb_baku_mutu.method_id', '=', $id_method)
                    ->where('tb_baku_mutu.sampletype_id', '=', $sampletype)
                    ->whereNull('tb_baku_mutu.deleted_at');
                })
                ->join('ms_unit as unit_baku_mutu', function ($join) {
                  $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                    ->whereNull('unit_baku_mutu.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
                });
            })

            ->orderBy('tb_sample_result.created_at', 'asc')
            ->first();
        }
        // $id_method = $method->id_method;

        // $result_one = SampleResult::where('tb_sample_result.sample_id', '=', $sample->id_samples)
        //   ->where('ms_laboratorium.kode_laboratorium', 'KIM')
        //   ->where('tb_sample_result.method_id', $method->id_method)
        //   ->join('ms_laboratorium', function ($join) {
        //     $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_result.laboratorium_id')
        //       ->whereNull('ms_laboratorium.deleted_at')
        //       ->whereNull('tb_sample_result.deleted_at');
        //   })
        //   ->join('ms_method', function ($join) {
        //     $join->on('ms_method.id_method', '=', 'tb_sample_result.method_id')
        //       ->whereNull('tb_sample_result.deleted_at')
        //       ->whereNull('ms_method.deleted_at');
        //   })
        //   ->join('tb_samples', function ($join) {
        //     $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')
        //       ->whereNull('tb_samples.deleted_at')
        //       ->whereNull('tb_sample_result.deleted_at');
        //   })
        //   ->join('tb_sample_method', function ($join) use ($id_method) {
        //     $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
        //       ->where('tb_sample_method.method_id', $id_method)
        //       ->whereNull('tb_sample_method.deleted_at')
        //       ->whereNull('tb_samples.deleted_at');
        //   })
        //   ->join('tb_baku_mutu', function ($join) use ($sampletype, $id_method) {
        //     $join->where('tb_baku_mutu.method_id', '=', $id_method)
        //       ->where('tb_baku_mutu.sampletype_id', '=', $sampletype)
        //       ->whereNull('tb_baku_mutu.deleted_at')
        //       ->whereNull('ms_method.deleted_at');
        //   })
        //   ->join('ms_unit as unit_baku_mutu', function ($join) {
        //     $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
        //       ->whereNull('unit_baku_mutu.deleted_at')
        //       ->whereNull('tb_baku_mutu.deleted_at');
        //   })

        //   ->first();

        $sample_result = [];
        $sample_result["hasil"] = isset($result_one->hasil) ? $result_one->hasil : "-";
        $sample_result["min"] = isset($result_one->min) ? $result_one->min : null;
        $sample_result["max"] = isset($result_one->max) ? $result_one->max : null;

        $sample_result["equal"] = isset($result_one->equal) ? $result_one->equal : null;
        array_push($sample_one["result"],  $sample_result);
        // array_push($sample_one["min"], isset($result_one->min) ? $result_one->min : "-");
        // array_push($sample_one["max"], isset($result_one->max) ? $result_one->max : "-");
      }
      array_push($table, $sample_one);
    }





    if (count($makmin) > 0) {
      $sample_type_id = 'd34b4a50-4560-4fce-96c3-046c7080a986';
    } else {
      $sample_type_id = '0e9a1091-b248-44e9-921b-5b4773109603';
    }
    $diambil_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->orderBy('datesampling_samples', 'ASC')
      ->where('kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      // ->whereNotNull('penanganan_sample_date')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))


          // ->limit(1)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })

      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
          ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
          ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })

      // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
      ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
      // ->take(1)
      ->firstOrFail();

    $diambil_min = $diambil_min->datesampling_samples;

    // dd($diambil_min);

    $diambil_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->orderBy('datesampling_samples', 'DESC')
      ->where('kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      // ->whereNotNull('penanganan_sample_date')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))


          // ->limit(1)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })

      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
          ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
          ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })

      // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
      ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
      // ->take(1)
      ->firstOrFail();

    $diambil_max = $diambil_max->datesampling_samples;

    $checking_min = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->orderBy('date_checking', 'ASC')
      ->where('kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      ->whereNotNull('penanganan_sample_date')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))


          // ->limit(1)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })

      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
          ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
          ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })

      // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
      ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
      // ->take(1)
      ->firstOrFail();

    $checking_min = $checking_min->date_checking;

    $done_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->orderBy('date_done_estimation_labs', 'DESC')
      ->where('kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      ->whereNotNull('penanganan_sample_date')
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))


          // ->limit(1)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })

      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })

      // ->leftjoin('tb_pengesahan_hasil', function ($join) {
      //   $join->on('tb_pengesahan_hasil.sample_id', '=', 'tb_samples.id_samples')
      //     ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
      //     ->where('tb_pengesahan_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      //     ->whereNull('tb_pengesahan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })

      // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
      ->orderBy('tb_pengesahan_hasil.created_at', 'desc')
      // ->take(1)
      ->first();


    $date_verif_max = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)

      ->where('kode_laboratorium', 'KIM')
      ->whereNotNull('verifikasi_hasil_date')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_verifikasi_hasil', function ($join) {
        $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))


          // ->limit(1)
          ->whereNull('tb_verifikasi_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')

          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })

      // ->leftjoin('tb_verifikasi_hasil', function ($join) {
      //   $join->on('tb_verifikasi_hasil.sample_id', '=', 'tb_samples.id_samples')
      //     ->on('tb_samples.id_samples', '=', 'tb_verifikasi_hasil.sample_id')
      //     ->where('tb_verifikasi_hasil.laboratorium_id', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
      //     ->whereNull('tb_pengesahan_hasil.deleted_at')
      //     ->whereNull('tb_samples.deleted_at');
      // })

      // ->select('tb_pengesahan_hasil.*', 'id_laboratorium', 'date_sending', 'date_analitik_sample', 'nama_laboratorium', 'codesample_samples', 'id_samples')
      ->orderBy('tb_verifikasi_hasil.verifikasi_hasil_date', 'desc')
      // ->take(1)
      ->first();

    // dd($date_verif_max);
    if (isset($date_verif_max->verifikasi_hasil_date)) {
      $date_verif_max = $date_verif_max->verifikasi_hasil_date;
    } else {
    }



    // dd($checking_min);

    $done_max = $done_max->date_done_estimation_labs;


    $sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->where('kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->orderBy('tb_pengesahan_hasil.pengesahan_hasil_date', 'desc')
      ->first();


    $pengesahan_hasil = PengesahanHasil::where('sample_id', '=', $sample->id_samples)->where('laboratorium_id', '=', $sample->laboratorium_id)->first();

    $no_LHU = LHU::where('sample_id', '=', $sample->id_samples)->where('lab_id', '=', $sample->laboratorium_id)->first();

    $agenda = $request->input('agenda');
    // dd($agenda);

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();



      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));
      $no_LHU->nomer_LHU = '445.9/A.' . $no_LHU->nomer_urut_LHU . '/4.2.26/' . $romawi_bulan . '/' . Carbon::now()->format('Y');
      $no_LHU->sample_id = $sample->id_samples;
      $no_LHU->lab_id = $sample->id_laboratorium;
      $no_LHU->save();




      $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
      $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');

      // $no_LHU = '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
      $no_LHU = !empty($agenda)
        ? '445.9/' . $agenda . '/4.2.26/' . $mount . '/' . $year
        : '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;

    } else {
      if (isset($pengesahan_hasil)) {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('Y');
      } else {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');
      }
      // $no_LHU = '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
      $no_LHU = !empty($agenda)
        ? '445.9/' . $agenda . '/4.2.26/' . $mount . '/' . $year
        : '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
    }

    $item_sample = Sample::where('permohonan_uji_id', '=', $id_permohonan_uji)
      ->where('ms_laboratorium.kode_laboratorium', 'KIM')
      ->where('tb_samples.typesample_samples',  $sample_type_id)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.*')
      ->distinct('tb_samples.id_samples')
      ->first();

    $lab_num_max = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
      ->orderBy('lab_number', 'asc')
      ->take(1);

    $lab_num_min = LabNum::where('permohonan_uji_id', $id_permohonan_uji)
      ->orderBy('lab_number', 'desc')
      ->take(1);
    // Fallback: gunakan item_sample jika $sample null (misal belum ada penanganan)
    if (!$sample) {
      $sample = $item_sample;
    }
    $laboratorium = $sample ? Laboratorium::where('id_laboratorium', $sample->id_laboratorium)->first() : null;
    //dd($year);


    if (count($makmin) > 0) {



      $pdf = PDF::loadView("masterweb::module.admin.laboratorium.sample.formatPrint.kimia.makmin", compact(
        'permohonan_uji',
        'all_samples_max',
        'all_samples_min',
        'all_samples_max_is_manual',
        'all_samples_min_is_manual',
        'table',
        'sample',
        'laboratorium',
        'no_LHU',
        'method_all',
        'diambil_min',
        'diambil_max',
        'checking_min',
        'done_max',
        'pengesahan_hasil',
        'all_acuan_baku_mutu',
        'date_verif_max',
        'lab_num',
        'lab_string',
      ));

      return $pdf->stream();
    } else {
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.pudam', compact(
        'permohonan_uji',
        'all_samples_max',
        'all_samples_min',
        'all_samples_max_is_manual',
        'all_samples_min_is_manual',
        'table',
        'sample',
        'laboratorium',
        'no_LHU',
        'method_all',
        'diambil_min',
        'diambil_max',
        'checking_min',
        'done_max',
        'pengesahan_hasil',
        'all_acuan_baku_mutu',
        'date_verif_max',
        'lab_num',
        'lab_string',
      ));

      return $pdf->stream();
    }

    // testing for print of view
    // return view('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.pudam', compact('permohonan_uji', 'all_samples_max', 'all_samples_min', 'table', 'sample', 'laboratorium', 'no_LHU', 'method_all', 'diambil_min', 'diambil_max', 'checking_min', 'done_max', 'pengesahan_hasil', 'all_acuan_baku_mutu', 'date_verif_max'));
  }

  public function printLHU(Request $request, $id, $idlab, $ischlor = null)
  {
    // Allow iframe embedding by removing X-Frame-Options and setting frame-ancestors
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: frame-ancestors 'self'");

    //ini_set('max_execution_time', 3600);
    $sample = Sample::where('id_samples', '=', $id)
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
        $join
          ->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
          ->where('tb_sample_penanganan.laboratorium_id', '=', $idlab)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->first();

    $fontsize = isset($request->fontsize)
      ? (float) $request->fontsize
      : (float) ($sample->fontsize_hasil_baca_hasil ?? 12.0);
    $lineHeight = isset($request->line_height)
      ? (float) $request->line_height
      : (float) ($sample->line_height_hasil_baca_hasil ?? 1.0);
    $padding = isset($request->padding)
      ? (float) $request->padding
      : (float) ($sample->padding_hasil_baca_hasil ?? 1.0);

    if (isset($request->show_kop)) {
      $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
    } else {
      $showKop = (int) ($sample->show_kop_hasil_baca_hasil ?? 1);
    }
    // dd($sample);mount

    $tembusans = $sample->tembusan;

    $pengesahan_hasil = PengesahanHasil::where('sample_id', '=', $id)->where('laboratorium_id', '=', $idlab)->first();

    $no_LHU = LHU::where('sample_id', '=', $id)->where('lab_id', '=', $idlab)->first();

    $agenda = $request->input('agenda');
    // dd($agenda);

    // Ambil nomer lab KESMAS per (lab + jenis sampel)
    $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $sample->permohonan_uji_id ?? null)
      ->where('laboratorium_id', $idlab)
      ->where('sample_type_id', $sample->typesample_samples ?? null)
      ->first();
    // Fallback tanpa filter jenis sampel
    if (!$nomerLabKesmas) {
      $nomerLabKesmas = NomerLabKesmas::where('permohonan_uji_id', $sample->permohonan_uji_id ?? null)
        ->where('laboratorium_id', $idlab)
        ->first();
    }
    $kesmasLabNumber = $nomerLabKesmas ? $nomerLabKesmas->nomer_lab : null;

    // Bangun string Nomor Laboratorium 449.5/01 untuk Kimia
    if ($kesmasLabNumber) {
      $nomerLabDisplay = '449.5/01/' . str_pad($kesmasLabNumber, 4, '0', STR_PAD_LEFT) . '/' . ($nomerLabKesmas->year ?? date('Y'));
    } else {
      $nomerLabDisplay = '449.5/01/............/' . date('Y');
    }

    if (!isset($no_LHU)) {
      $no_LHU = new LHU;
      //uuid
      $uuid4 = Uuid::uuid4();

      $no_LHU_urutan = LHU::max('nomer_urut_LHU');
      $no_LHU->id_lhu = $uuid4->toString();
      $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
      $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));

      // Hanya gunakan nomer lab KESMAS, tidak ada fallback
      $labNumber = $kesmasLabNumber;

      // Simpan nomor lengkap di database: 445.02/{nomer_lab}/{jenis_sampel}/05.31/{tahun}
      // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
      if ($labNumber) {
        $no_LHU->nomer_LHU = '445.02/' . $labNumber . '/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
      } else {
        $no_LHU->nomer_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . Carbon::now()->format('Y');
      }
      $no_LHU->sample_id = $id;
      $no_LHU->lab_id = $idlab;
      $no_LHU->save();


      $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
      $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');

      // Deteksi jenis sampel unik dari $table untuk format gabungan
      // $table tidak tersedia di printLHU (per-sampel), jadi inisialisasi sebagai array kosong
      if (!isset($table)) {
        $table = [];
      }
      $jenisSampelUnik = [];
      foreach ($table as $mytable) {
        if (isset($mytable['sample_type']) && isset($mytable['sample_type']->name_sample_type)) {
          $jenisSampel = $mytable['sample_type']->name_sample_type;
          if (!in_array($jenisSampel, $jenisSampelUnik)) {
            $jenisSampelUnik[] = $jenisSampel;
          }
        }
      }

      // Jika ada 2 jenis sampel berbeda, gunakan format dengan nomor lab minimum - maksimum
      $hasTwoJenisSampel = count($jenisSampelUnik) == 2;

      // Ambil nomor lab minimum dan maksimum dari NomerLabKesmas saja
      $minLabNum = null;
      $maxLabNum = null;

      // Gunakan helper function untuk konsistensi
      $permohonanUjiId = $sample->permohonan_uji_id ?? null;
      if ($permohonanUjiId) {
        $labNums = $this->getMinMaxLabNumFromKesmas($permohonanUjiId);
        $minLabNum = $labNums['min'];
        $maxLabNum = $labNums['max'];
      }

      // Tampilkan selalu dengan nomor lab:
      // - jika agenda diisi, pakai agenda
      // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
      $nomerLab = !empty($agenda)
        ? $agenda
        : $kesmasLabNumber;

      if ($hasTwoJenisSampel && $minLabNum !== null && $maxLabNum !== null) {
        // Format untuk 2 jenis sampel: 445.02/{min_lab_num} - {max_lab_num} /AB-AM/05.31/{year}
        $minLabNumFormatted = str_pad($minLabNum, 3, '0', STR_PAD_LEFT);
        $maxLabNumFormatted = str_pad($maxLabNum, 3, '0', STR_PAD_LEFT);

        // Jika min dan max sama, hanya tampilkan satu angka
        if ($minLabNum === $maxLabNum) {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = !empty($agenda)
            ? '445.02/' . $agenda . ' - ' . $agenda . '/' . $sample->code_sample_type . '/05.31/' . $year
            : '445.02/' . $minLabNumFormatted . ' - ' . $maxLabNumFormatted . '/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      } else {
        // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
        if ($nomerLab) {
          $nomerLab = str_pad($nomerLab, 3, '0', STR_PAD_LEFT);
          $no_LHU = '445.02/' . $nomerLab . '/' . $sample->code_sample_type . '/05.31/' . $year;
        } else {
          $no_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . $year;
        }
      }
    } else {
      if (isset($pengesahan_hasil)) {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('Y');
      } else {
        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');
      }

      // Tampilkan selalu dengan nomor lab:
      // - jika agenda diisi, pakai agenda
      // - jika tidak, pakai nomer lab KESMAS per-lab (hanya dari NomerLabKesmas)
      $nomerLab = !empty($agenda)
        ? $agenda
        : $kesmasLabNumber;

      // Jika tidak ada nomor lab dari NomerLabKesmas, gunakan spasi kosong
      if ($nomerLab) {
        $nomerLab = str_pad($nomerLab, 3, '0', STR_PAD_LEFT);
        $no_LHU = '445.02/' . $nomerLab . '/' . $sample->code_sample_type . '/05.31/' . $year;
      } else {
        $no_LHU = '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/' . $sample->code_sample_type . '/05.31/' . $year;
      }
    }

    $sampleModel = Sample::find($id);
    $no_LHU = $this->applyKesmasNomorLabBakuToNoLhu($sampleModel, $no_LHU, $year ?? date('Y'), $idlab);

    // dd($sample);

    $verifikasi = VerifikasiHasil::where('sample_id', '=', $id)->where('laboratorium_id', '=', $idlab)->first();
    $laboratorium = Laboratorium::findOrFail($idlab);

    $sampletype_id = $sample->id_sample_type;

    $sampletype_id = $sample->id_sample_type;
    $jenis_makanan_id = $sample->jenis_makanan_id;
    if (isset($jenis_makanan_id)) {



      $laboratoriummethods_sampletypes = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
        ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
        // ->orderBy('ms_method.created_at')
        ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
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
            // ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
            //   $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
            //     ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
            //     ->whereNull('tb_baku_mutu.deleted_at');
            // })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            })->leftjoin('tb_sample_result', function ($join) use ($idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')

                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })
        ->leftjoin('ms_sample_type_detail', function ($join) use ($id) {
          $join->on('ms_sample_type_detail.method_id', '=', 'tb_laboratorium_method.method_id')
            ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_sample_type_detail.deleted_at');
        })


        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'unit_baku_mutu.*',
          // 'tb_sample_result.hasil',
          // 'ms_sample_type_detail.is_tambahan',
          'tb_laboratorium_method.*',
        )

        // ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
        //   $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
        //     ->whereNull('tb_baku_mutu.deleted_at')
        //     ->whereNull('ms_method.deleted_at');
        // })
        // ->join('ms_unit as unit_baku_mutu', function ($join) {
        //   $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
        //     ->whereNull('unit_baku_mutu.deleted_at')
        //     ->whereNull('tb_baku_mutu.deleted_at');
        // })
        ->distinct('ms_method.id_method')
        // ->select('tb_baku_mutu.*', 'ms_method.*', 'tb_laboratorium_method.*', 'unit_baku_mutu.*')
        ->get();
    } else {
      $laboratoriummethods_sampletypes = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
        ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
        ->where('ms_sample_type_detail.sample_type_id', '=', $sampletype_id)

        // ->orderBy('ms_method.created_at')
        ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail')
        // ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
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
            })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            });
        })
        ->leftjoin('ms_sample_type_detail', function ($join) {
          $join->on('ms_sample_type_detail.method_id', '=', 'tb_laboratorium_method.method_id')

            ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_sample_type_detail.deleted_at');
        })


        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'unit_baku_mutu.*',
          // 'tb_sample_result.hasil',
          // 'ms_sample_type_detail.is_tambahan',
          'tb_laboratorium_method.*',
        )

        // ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
        //   $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
        //     ->whereNull('tb_baku_mutu.deleted_at')
        //     ->whereNull('ms_method.deleted_at');
        // })
        // ->join('ms_unit as unit_baku_mutu', function ($join) {
        //   $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
        //     ->whereNull('unit_baku_mutu.deleted_at')
        //     ->whereNull('tb_baku_mutu.deleted_at');
        // })
        ->distinct('ms_method.id_method')
        // ->distinct('tb_laboratorium_method.laboratorium_id')
        // ->select('tb_baku_mutu.*', 'ms_method.*', 'tb_laboratorium_method.*', 'unit_baku_mutu.*')
        ->get();
      // dd($laboratoriummethods_sampletypes);

    }

    if (isset($jenis_makanan_id)) {

      // dd($jenis_makanan_id);

      $laboratoriummethodsResult = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
        ->where('tb_sample_method.sample_id', '=', $id)
        // ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
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
          'tb_sample_result.*',
          'tb_sample_result.offset_baku_mutu'
        )
        ->distinct('ms_method.id_method')
        ->get();
    } else {
      $laboratoriummethodsResult = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
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
          'tb_sample_result.*',
          'tb_sample_result.offset_baku_mutu'
        )
        ->distinct('ms_method.id_method')
        ->get();
    }

    // Use $laboratoriummethodsResult (from SampleMethod) as primary source
    // This ensures we only show methods that are actually selected for this sample
    // $laboratoriummethods_sampletypes is used for ordering and additional data
    if (isset($laboratoriummethodsResult) && $laboratoriummethodsResult->count() > 0) {
      $laboratoriummethods = $laboratoriummethodsResult;
    } else {
      // Fallback to $laboratoriummethods_sampletypes if no results from SampleMethod
      $laboratoriummethods = $laboratoriummethods_sampletypes;
    }

    // dd($laboratoriummethods);

    // $laboratoriummethods = SampleTypeDetail::where('ms_sample_type_detail.sample_type_id', '=', $sampletype_id)
    //   ->where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
    //   ->join('ms_method', function ($join) {
    //     $join->on('ms_method.id_method', '=', 'ms_sample_type_detail.method_id')
    //       ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
    //       ->whereNull('ms_sample_type_detail.deleted_at')
    //       ->whereNull('ms_method.deleted_at');
    //   })
    //   ->join('tb_laboratorium_method', function ($join) {
    //     $join->on('tb_laboratorium_method.method_id', '=', 'ms_sample_type_detail.method_id')
    //       ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
    //       ->whereNull('ms_sample_type_detail.deleted_at')
    //       ->whereNull('tb_laboratorium_method.deleted_at');
    //   })
    //   ->leftjoin('tb_sample_result', function ($join) use ($id) {
    //     $join->on('tb_sample_result.method_id', '=', 'ms_sample_type_detail.method_id')
    //       ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
    //       ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
    //       ->where('tb_sample_result.sample_id', '=', $id)
    //       ->whereNull('ms_sample_type_detail.deleted_at')
    //       ->whereNull('tb_sample_result.deleted_at');
    //   })
    //   ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
    //     $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
    //       ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
    //       ->whereNull('tb_baku_mutu.deleted_at')
    //       ->whereNull('ms_method.deleted_at');
    //   })
    //   ->join('ms_unit as unit_baku_mutu', function ($join) {
    //     $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
    //       ->whereNull('unit_baku_mutu.deleted_at')
    //       ->whereNull('tb_baku_mutu.deleted_at');
    //   })
    //   ->get();
    // $sampletype_id = $sample->id_sample_type;
    // $jenis_makanan_id = $sample->jenis_makanan_id;
    // if (isset($jenis_makanan_id)) {

    //   $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
    //     ->where('tb_sample_method.sample_id', '=', $id)
    //     ->orderBy('ms_method.jenis_parameter_kimia')
    //     ->join('ms_method', function ($join)   use ($sampletype_id, $jenis_makanan_id, $idlab, $id) {
    //       $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
    //         ->whereNull('tb_sample_method.deleted_at')
    //         ->whereNull('ms_method.deleted_at')
    //         ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
    //           $join
    //             // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
    //             //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
    //             //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
    //             //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
    //             //     LIMIT 1)'))
    //             ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
    //             ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
    //             ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
    //             ->whereNull('tb_baku_mutu.deleted_at')
    //             ->whereNull('ms_method.deleted_at');
    //         })
    //         // ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
    //         //   $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
    //         //     ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
    //         //     ->whereNull('tb_baku_mutu.deleted_at');
    //         // })

    //         ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
    //           $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
    //             ->whereNull('unit_baku_mutu.deleted_at')
    //             ->whereNull('tb_baku_mutu.deleted_at');
    //         })->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
    //           $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
    //             ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
    //             ->where('tb_sample_result.sample_id', '=', $id)
    //             ->whereNull('tb_sample_result.deleted_at')
    //             ->whereNull('ms_method.deleted_at');
    //         });
    //     })



    //     ->select(
    //       'tb_baku_mutu.*',
    //       'ms_method.*',
    //       'tb_sample_method.*',
    //       'unit_baku_mutu.*',
    //       'tb_sample_result.hasil',
    //       'tb_sample_result.offset_baku_mutu'
    //     )
    //     ->get();
    // } else {
    //   $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $idlab)
    //     ->where('tb_sample_method.sample_id', '=', $id)
    //     ->orderBy('ms_method.jenis_parameter_kimia')
    //     ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
    //       $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
    //         ->whereNull('tb_sample_method.deleted_at')
    //         ->whereNull('ms_method.deleted_at')
    //         ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $idlab) {
    //           $join
    //             // ->on('tb_baku_mutu.method_id', '=', DB::raw('(SELECT id_method FROM
    //             //     ms_method WHERE tb_baku_mutu.method_id = ms_method.id_method AND
    //             //     tb_baku_mutu.deleted_at is NULL AND ms_method.deleted_at is NULL
    //             //     AND tb_baku_mutu.sampletype_id =  "'.$sampletype_id.'"
    //             //     LIMIT 1)'))
    //             ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
    //             ->where('tb_baku_mutu.lab_id', '=', $idlab)
    //             ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
    //             ->whereNull('tb_baku_mutu.deleted_at')
    //             ->whereNull('ms_method.deleted_at');
    //         })
    //         // ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
    //         //   $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
    //         //     ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
    //         //     ->whereNull('tb_baku_mutu.deleted_at');
    //         // })

    //         ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
    //           $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
    //             ->whereNull('unit_baku_mutu.deleted_at')
    //             ->whereNull('tb_baku_mutu.deleted_at');
    //         })
    //         ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
    //           $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
    //             ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
    //             ->where('tb_sample_result.sample_id', '=', $id)
    //             ->whereNull('tb_sample_result.deleted_at')
    //             ->whereNull('ms_method.deleted_at');
    //         });
    //     })




    //     ->select(
    //       'tb_baku_mutu.*',
    //       'ms_method.*',
    //       'tb_sample_method.*',
    //       'unit_baku_mutu.*',
    //       'tb_sample_result.hasil',
    //       'tb_sample_result.offset_baku_mutu'
    //     )
    //     ->distinct('ms_method.id_method')
    //     ->get();
    // }

    $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();

    // Sort $laboratoriummethods by orderlist_sample_type_detail if available
    // But don't filter - show all methods selected for this sample
    // Convert to collection if not already
    if (!($laboratoriummethods instanceof \Illuminate\Support\Collection)) {
      $laboratoriummethods = collect($laboratoriummethods);
    }

    if ($laboratoriummethods->count() > 0) {
      $laboratoriummethods = kesmas_sort_laboratorium_methods($laboratoriummethods, $sample_type_details);
    }

    foreach ($laboratoriummethods as $key => $laboratoriummethod) {
      # code...sample_type_details
      $laboratoriummethods[$key]->detail = array();

      $resolvedMethodId = kesmas_lhu_resolve_method_id($laboratoriummethod);

      // Use first() instead of firstOrFail() to avoid 404 if method not in sample_type_details
      $sampleTypeDetail = SampleTypeDetail::where('method_id', '=', $resolvedMethodId)
        ->where('ms_sample_type_detail.sample_type_id', $sampletype_id)
        ->first();

      $laboratoriummethods[$key]->is_tambahan = $sampleTypeDetail ? (int) $sampleTypeDetail->is_tambahan : 1;
      $laboratoriummethods[$key]->orderlist_sample_type_detail = $sampleTypeDetail
        ? (int) $sampleTypeDetail->orderlist_sample_type_detail
        : null;

      $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $resolvedMethodId)
        ->where('sampletype_id', '=', $sampletype_id)
        ->where('sample_id', '=',  $id)->get();

      $sample_result = SampleResult::where('method_id', '=', $resolvedMethodId)
        ->where('laboratorium_id', '=', $idlab)
        ->where('sample_id', '=',  $id)->first();


      $laboratoriummethods[$key]->hasil = kesmas_lhu_resolve_hasil_for_print($laboratoriummethod, $sample_result);
      $laboratoriummethods[$key]->keterangan = isset($sample_result->keterangan) ? $sample_result->keterangan : "-";
      $laboratoriummethods[$key]->metode = kesmas_resolve_metode_for_print($laboratoriummethod, $sample_result);

      // dd($sample_result);

      if (count($laboratoriummethods[$key]->detail) == 0) {

        $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=', $laboratoriummethod->id_method)
          ->where('sampletype_id', '=', $sampletype_id)
          ->where('baku_mutu_id', '=',  $laboratoriummethod->id_baku_mutu)->get();

        if (count($bakuMutuDetailParameterNonKliniks) > 0) {
          // dd($bakuMutuDetailParameterNonKliniks);
          $all_array = [];
          foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {
            # code...
            $array = [];
            $array = [
              "id_sample_result_detail" =>    $bakuMutuDetailParameterNonKlinik->id_baku_mutu_detail_parameter_non_klinik,
              "method_id" => $laboratoriummethod->id_method,
              "sample_id" => $laboratoriummethod->sample_id,
              "is_tambahan" => $laboratoriummethod->is_tambahan,
              "sampletype_id" => $sampletype_id,
              "lab_id" =>  $laboratoriummethod->lab_id,
              "name_sample_result_detail" =>    $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik,
              "min_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik,
              "max_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik,
              "equal_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik,
              "nilai_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik,
              "hasil" => "-",
              "offset_baku_mutu" => "false",
              "created_at" => $bakuMutuDetailParameterNonKlinik->created_at,
              "updated_at" => $bakuMutuDetailParameterNonKlinik->updated_at,
              "deleted_at" => $bakuMutuDetailParameterNonKlinik->deleted_at
            ];
            array_push($all_array, $array);
          }
          $laboratoriummethods[$key]->detail = $all_array;
        }
      }
      //else {
      //   if ($laboratoriummethods_sampletypes[$key]->name_report == "pH") {
      //     dd("");
      //     # code...
      //   }
      // }
    }

    // Override baku mutu khusus sampel (dari baca-hasil tab "Khusus Sampel Ini")
    $laboratoriummethods = $this->applyBakuMutuSampleOverridesToMethods(
      $laboratoriummethods,
      (string) $id,
      (string) $idlab
    );

    $all_acuan_baku_mutu = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
      ->where('tb_sample_result.sample_id', '=', $id)
      ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
      // ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) use ($sampletype_id) {
        $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('ms_method.deleted_at')
          ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
            $join->where('tb_baku_mutu.sampletype_id', $sampletype_id)
              ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')

              ->whereNull('tb_baku_mutu.deleted_at')
              ->whereNull('ms_method.deleted_at');
          })

          ->join('ms_library', function ($join) {
            $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
              ->whereNull('ms_library.deleted_at')
              ->whereNull('tb_baku_mutu.deleted_at');
          });
      })
      ->leftjoin('tb_sample_result', function ($join) use ($id) {
        $join->on('tb_sample_result.method_id', '=', 'tb_laboratorium_method.method_id')
          ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
          ->whereNull('tb_laboratorium_method.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('tb_samples', function ($join) {
        $join->on('tb_samples.id_samples', '=', 'tb_sample_result.sample_id')

          ->whereNull('tb_samples.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) {
        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_laboratorium_method.laboratorium_id')
          // ->where('ms_laboratorium.kode_laboratorium', 'KIM')
          ->whereNull('ms_laboratorium.deleted_at')
          ->whereNull('tb_laboratorium_method.deleted_at');
      })
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })

      ->distinct('tb_laboratorium_method.laboratorium_id')
      ->select('ms_library.*')
      ->get();


    /* $data_method = Method::select(DB::raw('DISTINCT jenis_parameter_kimia, berhubungan_kesehatan, id_method'))
      ->orderBy('params_method')
      ->whereNull('deleted_at')
      ->get(); */

    $data_method = Method::select('jenis_parameter_kimia', 'berhubungan_kesehatan', 'id_method')
      ->distinct('jenis_parameter_kimia')
      // ->orderBy('params_method')
      ->whereNull('deleted_at')
      ->get();

    $laboratoriummethods_plus_count = SampleTypeDetail::where('ms_sample_type_detail.sample_type_id', '=', $sampletype_id)
      ->where('ms_sample_type_detail.is_tambahan', '=', 1)
      ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
      ->where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'ms_sample_type_detail.method_id')
          ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
          ->whereNull('ms_sample_type_detail.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->join('tb_laboratorium_method', function ($join) {
        $join->on('tb_laboratorium_method.method_id', '=', 'ms_sample_type_detail.method_id')
          ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
          ->whereNull('ms_sample_type_detail.deleted_at')
          ->whereNull('tb_laboratorium_method.deleted_at');
      })
      ->leftjoin('tb_sample_result', function ($join) use ($id) {
        $join->on('tb_sample_result.method_id', '=', 'ms_sample_type_detail.method_id')
          ->on('tb_sample_result.laboratorium_id', '=', 'tb_laboratorium_method.laboratorium_id')
          ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
          ->where('tb_sample_result.sample_id', '=', $id)

          ->whereNull('ms_sample_type_detail.deleted_at')
          ->whereNull('tb_sample_result.deleted_at');
      })
      ->where(function ($query) {
        $query->whereNotNull('tb_sample_result.hasil')
          ->where('tb_sample_result.hasil', '!=', '-');
      })
      ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
          ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
          ->whereNull('tb_baku_mutu.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->join('ms_unit as unit_baku_mutu', function ($join) {
        $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
          ->whereNull('unit_baku_mutu.deleted_at')
          ->whereNull('tb_baku_mutu.deleted_at');
      })
      ->count();

    // dd($laboratoriummethods_plus_count);



    $lab_num = LabNum::where('sample_id', $sample->id_samples)
      ->where('permohonan_uji_id', $sample->permohonan_uji_id)
      ->where('sample_type_id', $sample->typesample_samples)
      ->first();

    $folder = '';

    $sampleModel = Sample::find($sample->id_samples);
    $no_LHU = $this->applyKesmasNomorLabBakuToNoLhu($sampleModel, $no_LHU, isset($year) ? $year : date('Y'), $idlab);

    //    $mount = (int)Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m');
    //    if (($mount > 4 && $year == 2023) || $year > 2023) {
    //      $folder = '52023';
    //    } else {
    //      $folder = '2021_42023';
    //    }
    $validation = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->where('id_verification_activity', '=', 5)->first();
    $analytic = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->where('id_verification_activity', '=', 2)->first();

    //    // Makanan / Minuman
    //    if ($sample->id_sample_type == "d34b4a50-4560-4fce-96c3-046c7080a986"){
    //      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.makmin', compact(
    //        'pengesahan_hasil',
    //        'no_LHU',
    //        'sample',
    //        'verifikasi',
    //        'lab_num',
    //        'laboratoriummethods',
    //        'tembusans',
    //        'validation',
    //        'analytic'
    //      ));
    //
    //      return $pdf->stream();
    //    }

    $laboratoriummethodsUnits = $laboratoriummethods;

//    $laboratoriummethods = collect($laboratoriummethods)
//      ->filter(function ($item) {
//        return $item->hasil !== null;
//      })
//      ->sortBy('name_report');
    // $laboratoriummethods = $laboratoriummethodsResult;

    $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
    ->whereIn('id_verification_activity', [2, 4, 5])
    ->get()
    ->keyBy('id_verification_activity');

    $validator = optional($verificationActivitySample->get(5))->nama_petugas ?? '-';
    $verifikator = optional($verificationActivitySample->get(4))->nama_petugas ?? '-';
    $pemeriksa = optional($verificationActivitySample->get(2))->nama_petugas ?? '-';

    $validator = $this->searchPetugas($validator);
    $verifikator = $this->searchPetugas($verifikator);
    $pemeriksa = $this->searchPetugas($pemeriksa);


    $kimiaOrganikCount = collect($laboratoriummethods)->filter(function ($item) {
      return $item->jenis_parameter_kimia === 'kimia organik';
    })->count();

    $kimiaCount = collect($laboratoriummethods)->filter(function ($item) {
      return $item->jenis_parameter_kimia === 'kimiawi';
    })->count();

    $fisikaCount = collect($laboratoriummethods)->filter(function ($item) {
      return $item->jenis_parameter_kimia === 'fisika';
    })->count();

    $url = config("app.url");
    if (config("app.env") == "local"){
      $url .= ":8000";
    }
    $url .= "/elits-signature/progress/".$id."/0";

    $result = Builder::create()
      ->data($url)
      ->encoding(new Encoding('UTF-8'))
      ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
      ->size(600)
      ->margin(2)
      ->build();

    $qrBase64 = base64_encode($result->getString());


    $fontsize = (float) (isset($request->fontsize) ? $request->fontsize : data_get($sample, 'fontsize_hasil_baca_hasil', 12.0));
    $lineHeight = (float) (isset($request->line_height) ? $request->line_height : data_get($sample, 'line_height_hasil_baca_hasil', 1.0));
    $padding = (float) (isset($request->padding) ? $request->padding : data_get($sample, 'padding_hasil_baca_hasil', 1.0));
    if (isset($request->show_kop)) {
      $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
    } else {
      $showKop = (int) data_get($sample, 'show_kop_hasil_baca_hasil', 1);
    }

    $signOption = $request->signOption ?? 0;

    if (isset($request->signoption) and $request->signoption == "1"){
      $signOption = 1;
    }

    if ($signOption == 0){
      if ($laboratorium->kode_laboratorium == "KIM" && $sample->name_sample_type == "Air Minum") {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.air_minum', compact(
          'laboratoriummethods_sampletypes',
          'pengesahan_hasil',
          'laboratoriummethods_plus_count',
          'no_LHU',
          'sample',
          'verifikasi',
          'lab_num',
          'laboratorium',
          'laboratoriummethods',
          'all_acuan_baku_mutu',
          'data_method',
          'tembusans',
          'validation',
          'kimiaCount',
          'fisikaCount',
          'kimiaOrganikCount',
          'laboratoriummethodsUnits',
          'signOption',
          'validator',
          'verifikator',
          'pemeriksa',
          'fontsize',
          'lineHeight',
          'padding',
          'showKop',
          'nomerLabDisplay'
        ));

      } elseif ($laboratorium->kode_laboratorium == "KIM" && in_array($sample->name_sample_type, ['Air Higiene', 'Air Bersih'], true)) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.air_bersih', compact(
          'laboratoriummethods_sampletypes',
          'pengesahan_hasil',
          'laboratoriummethods_plus_count',
          'no_LHU',
          'sample',
          'lab_num',
          'verifikasi',
          'laboratorium',
          'laboratoriummethods',
          'all_acuan_baku_mutu',
          'tembusans',
          'validation',
          'kimiaCount',
          'fisikaCount',
          'kimiaOrganikCount',
          'laboratoriummethodsUnits',
          'signOption',
          'validator',
          'verifikator',
          'pemeriksa',
          'fontsize',
          'lineHeight',
          'padding',
          'showKop',
          'nomerLabDisplay'
        ));

      } elseif ($laboratorium->kode_laboratorium == "KIM" && $ischlor != null) {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.printLHU', compact(
          'laboratoriummethods_sampletypes',
          'pengesahan_hasil',
          'no_LHU',
          'sample',
          'laboratoriummethods_plus_count',
          'verifikasi',
          'lab_num',
          'laboratorium',
          'laboratoriummethods',
          'all_acuan_baku_mutu',
          'tembusans',
          'validation',
          'kimiaCount',
          'fisikaCount',
          'kimiaOrganikCount',
          'laboratoriummethodsUnits',
          'signOption',
          'validator',
          'verifikator',
          'pemeriksa',
          'fontsize',
          'lineHeight',
          'padding',
          'showKop',
          'nomerLabDisplay'
        ));

      } else {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.printLHU', compact(
          'laboratoriummethods_sampletypes',
          'pengesahan_hasil',
          'no_LHU',
          'sample',
          'lab_num',
          'laboratoriummethods_plus_count',
          'verifikasi',
          'laboratorium',
          'laboratoriummethods',
          'all_acuan_baku_mutu',
          'tembusans',
          'validation',
          'kimiaCount',
          'fisikaCount',
          'kimiaOrganikCount',
          'laboratoriummethodsUnits',
          'signOption',
          'validator',
          'verifikator',
          'pemeriksa',
          'fontsize',
          'lineHeight',
          'padding',
          'showKop',
          'nomerLabDisplay'
        ));

      }

      // Set headers to allow iframe embedding
      $response = response($pdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('X-Frame-Options', 'SAMEORIGIN')
        ->header('Content-Security-Policy', "frame-ancestors 'self'");

      return $response;
    }

    $dataKepalaLab = Petugas::query()->where('nik', '=', '3309094611720002')->where('nama', '=', 'dr. Muharyati')->get(['nik', 'password'])->first();

    $verificationActivity = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->get();

    if ($laboratorium->kode_laboratorium == "KIM" && $sample->name_sample_type == "Air Minum") {
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.air_minum', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'laboratoriummethods_plus_count',
        'no_LHU',
        'sample',
        'verifikasi',
        'lab_num',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu',
        'data_method',
        'tembusans',
        'validation',
        'kimiaCount',
        'fisikaCount',
        'kimiaOrganikCount',
        'laboratoriummethodsUnits',
        'qrBase64',
        'validator',
        'verifikator',
        'pemeriksa',
        'nomerLabDisplay'
      ));

    } elseif ($laboratorium->kode_laboratorium == "KIM" && in_array($sample->name_sample_type, ['Air Higiene', 'Air Bersih'], true)) {
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.air_bersih', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'laboratoriummethods_plus_count',
        'no_LHU',
        'sample',
        'lab_num',
        'verifikasi',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu',
        'tembusans',
        'validation',
        'kimiaCount',
        'fisikaCount',
        'kimiaOrganikCount',
        'laboratoriummethodsUnits',
        'qrBase64',
        'validator',
        'verifikator',
        'pemeriksa',
        'nomerLabDisplay'
      ));

    } elseif ($laboratorium->kode_laboratorium == "KIM" && $ischlor != null) {
      /* dd('KIM && null');

      return view('masterweb::module.admin.laboratorium.sample.printLHU', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'no_LHU',
        'sample',
        'laboratoriummethods_plus_count',
        'verifikasi',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu'
      )); */

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.printLHU', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'no_LHU',
        'sample',
        'laboratoriummethods_plus_count',
        'verifikasi',
        'lab_num',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu',
        'tembusans',
        'validation',
        'kimiaCount',
        'fisikaCount',
        'kimiaOrganikCount',
        'laboratoriummethodsUnits',
        'qrBase64',
        'validator',
        'verifikator',
        'pemeriksa',
        'fontsize',
        'lineHeight',
        'padding',
        'showKop',
        'nomerLabDisplay'
      ));

    } else {
      /* dd('else');

      return view('masterweb::module.admin.laboratorium.sample.printLHU', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'no_LHU',
        'sample',
        'laboratoriummethods_plus_count',
        'verifikasi',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu'
      )); */

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.printLHU', compact(
        'laboratoriummethods_sampletypes',
        'pengesahan_hasil',
        'no_LHU',
        'sample',
        'lab_num',
        'laboratoriummethods_plus_count',
        'verifikasi',
        'laboratorium',
        'laboratoriummethods',
        'all_acuan_baku_mutu',
        'tembusans',
        'validation',
        'kimiaCount',
        'fisikaCount',
        'kimiaOrganikCount',
        'laboratoriummethodsUnits',
        'qrBase64',
        'validator',
        'verifikator',
        'pemeriksa',
        'fontsize',
        'lineHeight',
        'padding',
        'showKop',
        'nomerLabDisplay'
      ));

    }

    if (!isset($agenda)){
      return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap input agenda!');
    }

    if (count($verificationActivity) < 5){
      return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap selesaikan seluruh step verifikasi!');
    }

    if (isset($dataKepalaLab->nik) and isset($dataKepalaLab->password) and isset($verifikasi)){

      $dataPetugasBSRE[] = [
        'nik' => $dataKepalaLab->nik,
        'passPhrase' => $dataKepalaLab->password,
        'tampilan' => 'invisible',
        'reason' => "hasil12121",
        'location' => "boyolali",
        'text' => "hasil22323"
      ];

      $signBSRE = Smt::signBSRE($pdf, $dataPetugasBSRE);

      if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
        $data =  base64_encode($signBSRE["data"]["file"]);

        return response()
          ->view('masterweb::module.admin.laboratorium.sample.blob', compact('data'))
          ->header('X-Frame-Options', 'SAMEORIGIN')
          ->header('Content-Security-Policy', "frame-ancestors 'self'");
      }elseif ($signBSRE['status'] == 500){
        return redirect()->back()->with('error-laporan', 'errors');
      }else{
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Silahkan coba lagi!');
      }

    }else{
      return redirect()->back()->with('error-bsre', 'Kredensial untuk tanda tangan elektronik belum lengkap. Harap lengkapi kredensial seluruh petugas!');
    }

  }
  public function printAllMakanMinum(Request $request, $idPermohonanUji)
  {

    $samples = Sample::query()->where('permohonan_uji_id', '=', $idPermohonanUji)->where('name_sample_type', '=', 'Makanan/Minuman/Lainnya')
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->get();

    $labnums = [];
    $idlab = '3416ca19-6c69-4e5f-a004-ae8275de7644';
    $laboratoriummethodsArray = [];
    $no_lhus = [];
    $param_methods = [];
    $validasi = [];
    $tembusans = [];
    foreach ($samples as $sample) {
      $sampletype_id = $sample->id_sample_type;
      $id = $sample->id_samples;
      $lab_num = LabNum::where('sample_id', $sample->id_samples)
        ->where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->where('sample_type_id', $sample->typesample_samples)
        ->first();

      $laboratoriummethods = LaboratoriumMethod::where('tb_laboratorium_method.laboratorium_id', '=', $idlab)
        ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
        ->where('ms_sample_type_detail.sample_type_id', '=', $sampletype_id)

        // ->orderBy('ms_method.created_at')
        ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail')
        // ->orderBy('ms_method.jenis_parameter_kimia')
        ->join('ms_method', function ($join)   use ($sampletype_id, $idlab, $id) {
          $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_method.deleted_at')
            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id) {
              $join
                ->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                ->whereNull('tb_baku_mutu.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })
            ->leftjoin('tb_sample_result', function ($join) use ($id, $idlab) {
              $join->where('tb_sample_result.laboratorium_id', '=', $idlab)
                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                ->where('tb_sample_result.sample_id', '=', $id)
                ->whereNull('tb_sample_result.deleted_at')
                ->whereNull('ms_method.deleted_at');
            })

            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
              $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                ->whereNull('unit_baku_mutu.deleted_at')
                ->whereNull('tb_baku_mutu.deleted_at');
            });
        })
        ->leftjoin('ms_sample_type_detail', function ($join) {
          $join->on('ms_sample_type_detail.method_id', '=', 'tb_laboratorium_method.method_id')

            ->orderBy('ms_sample_type_detail.orderlist_sample_type_detail', 'asc')
            ->whereNull('tb_laboratorium_method.deleted_at')
            ->whereNull('ms_sample_type_detail.deleted_at');
        })


        ->select(
          'tb_baku_mutu.*',
          'ms_method.*',
          'unit_baku_mutu.*',
          'tb_laboratorium_method.*',
        )
        ->distinct('ms_method.id_method')
        ->get();

      foreach ($laboratoriummethods as $key => $laboratoriummethod) {
        array_push($param_methods, $laboratoriummethod->params_method);
        # code...
        $laboratoriummethods[$key]->detail = array();


        $laboratoriummethods[$key]->is_tambahan = SampleTypeDetail::where('method_id', '=', $laboratoriummethod->id_method)
          ->where('method_id', '=', $laboratoriummethod->id_method)
          ->where('ms_sample_type_detail.sample_type_id', $sampletype_id)
          ->firstOrFail()->is_tambahan;



        $resolvedMethodId = kesmas_lhu_resolve_method_id($laboratoriummethod);

        $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $resolvedMethodId)
          ->where('sampletype_id', '=', $sampletype_id)
          ->where('sample_id', '=',  $id)->get();

        $sample_result = SampleResult::where('method_id', '=', $resolvedMethodId)
          ->where('laboratorium_id', '=', $idlab)
          ->where('sample_id', '=',  $id)->first();


        $laboratoriummethods[$key]->hasil = kesmas_lhu_resolve_hasil_for_print($laboratoriummethod, $sample_result);
        $laboratoriummethods[$key]->keterangan = isset($sample_result->keterangan) ? $sample_result->keterangan : "-";
        $laboratoriummethods[$key]->metode = kesmas_resolve_metode_for_print($laboratoriummethod, $sample_result);
        // dd($sample_result);

        if (count($laboratoriummethods[$key]->detail) == 0) {

          $bakuMutuDetailParameterNonKliniks = BakuMutuDetailParameterNonKlinik::where('method_id', '=', $laboratoriummethod->id_method)
            ->where('sampletype_id', '=', $sampletype_id)
            ->where('baku_mutu_id', '=',  $laboratoriummethod->id_baku_mutu)->get();

          if (count($bakuMutuDetailParameterNonKliniks) > 0) {
            // dd($bakuMutuDetailParameterNonKliniks);
            $all_array = [];
            foreach ($bakuMutuDetailParameterNonKliniks as $bakuMutuDetailParameterNonKlinik) {
              # code...
              $array = [];
              $array = [
                "id_sample_result_detail" =>    $bakuMutuDetailParameterNonKlinik->id_baku_mutu_detail_parameter_non_klinik,
                "method_id" => $laboratoriummethod->id_method,
                "sample_id" => $laboratoriummethod->sample_id,
                "is_tambahan" => $laboratoriummethod->is_tambahan,
                "sampletype_id" => $sampletype_id,
                "lab_id" =>  $laboratoriummethod->lab_id,
                "name_sample_result_detail" =>    $bakuMutuDetailParameterNonKlinik->name_baku_mutu_detail_parameter_non_klinik,
                "min_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->min_baku_mutu_detail_parameter_non_klinik,
                "max_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->max_baku_mutu_detail_parameter_non_klinik,
                "equal_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->equal_baku_mutu_detail_parameter_non_klinik,
                "nilai_sample_result_detail" => $bakuMutuDetailParameterNonKlinik->nilai_baku_mutu_detail_parameter_non_klinik,
                "hasil" => "-",
                "offset_baku_mutu" => "false",
                "created_at" => $bakuMutuDetailParameterNonKlinik->created_at,
                "updated_at" => $bakuMutuDetailParameterNonKlinik->updated_at,
                "deleted_at" => $bakuMutuDetailParameterNonKlinik->deleted_at
              ];
              array_push($all_array, $array);
            }
            $laboratoriummethods[$key]->detail = $all_array;
          }
        }
      }

      // Override baku mutu khusus sampel (baca-hasil) untuk cetak makmin/all
      $laboratoriummethods = $this->applyBakuMutuSampleOverridesToMethods(
        $laboratoriummethods,
        (string) $id,
        (string) $idlab
      );


      $no_LHU = LHU::where('sample_id', '=', $id)->where('lab_id', '=', $idlab)->first();

      $agenda = $request->input('agenda');
      // dd($agenda);

      if (!isset($no_LHU)) {
        $no_LHU = new LHU;
        //uuid
        $uuid4 = Uuid::uuid4();

        $no_LHU_urutan = LHU::max('nomer_urut_LHU');
        $no_LHU->id_lhu = $uuid4->toString();
        $no_LHU->nomer_urut_LHU = $no_LHU_urutan + 1;
        $romawi_bulan = $this->convertToRoman(Carbon::now()->format('m'));
        $no_LHU->nomer_LHU = '445.9/A.' . $no_LHU->nomer_urut_LHU . '/4.2.26/' . $romawi_bulan . '/' . Carbon::now()->format('Y');
        $no_LHU->sample_id = $id;
        $no_LHU->lab_id = $idlab;
        $no_LHU->save();


        $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');

        // $no_LHU = '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
        $no_LHU = !empty($agenda)
          ? '445.9/' . $agenda . '/4.2.26/' . $mount . '/' . $year
          : '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
      } else {
        if (isset($pengesahan_hasil)) {
          $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('m'));
          $year = Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->format('Y');
        } else {
          $mount = $this->convertToRoman(Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('m'));
          $year = Carbon::createFromFormat('Y-m-d H:i:s', $no_LHU->created_at)->format('Y');
        }

        // $no_LHU = '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
        $no_LHU = !empty($agenda)
          ? '445.9/' . $agenda . '/4.2.26/' . $mount . '/' . $year
          : '<span style="padding-right:40px">445.9/</span>/4.2.26/' . $mount . '/' . $year;
      }

      $analytic = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->where('id_verification_activity', '=', 2)->first();
      $valid = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->where('id_verification_activity', '=', 5)->first();

      $arr = [];

      $labSamples = [
        "jenis_sarana" => $sample->jenis_sarana_names,
        "nama_jenis_makanan" => $sample->nama_jenis_makanan,
        "lab_num" => $lab_num->lab_number,
        "date_sending" => $sample->date_sending,
        "date_analytic" => isset($analytic) ? $analytic->stop_date : null
      ];

      array_push($arr, $laboratoriummethods);
      array_push($arr, $labSamples);

      array_push($labnums, $lab_num);
      array_push($laboratoriummethodsArray, $arr);
      array_push($no_lhus, $no_LHU);
      array_push($tembusans, $sample->tembusan);
      if (isset($valid)) {
        array_push($validasi, $valid);
      }
    }

    $no_LHU = $no_lhus[0];

    $firstSampleModel = Sample::find($samples->first()->id_samples ?? null);
    $no_LHU = $this->applyKesmasNomorLabBakuToNoLhu($firstSampleModel, $no_LHU, $year ?? date('Y'), $idlab);

    $param_methods = array_unique($param_methods);
    $reviewSample = $samples->first();
    $fontsize = (float) (isset($request->fontsize) ? $request->fontsize : data_get($reviewSample, 'fontsize_hasil_baca_hasil', 12.0));
    $lineHeight = (float) (isset($request->line_height) ? $request->line_height : data_get($reviewSample, 'line_height_hasil_baca_hasil', 1.0));
    $padding = (float) (isset($request->padding) ? $request->padding : data_get($reviewSample, 'padding_hasil_baca_hasil', 1.0));
    if (isset($request->show_kop)) {
      $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
    } else {
      $showKop = (int) data_get($reviewSample, 'show_kop_hasil_baca_hasil', 1);
    }

    if (count($validasi) == count($samples)) {
      $validasi = $validasi[count($samples) - 1];
    } else {
      $validasi = null;
    }

    $tembusans = $tembusans[count($samples) - 1];

    if (isset($request->signOption) and $request->signOption == 1){
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.makmin', compact(
        'no_LHU',
        'samples',
        'labnums',
        'laboratoriummethodsArray',
        'param_methods',
        'tembusans',
        'validasi',
        'fontsize',
        'lineHeight',
        'padding',
        'showKop'
      ));


      $datas = [
        [
          'nik' => 3309094611720002,
          'passPhrase' => "@Muharyati123",
          'tampilan' => 'invisible',
          'reason' => "hasil12121",
          'location' => "boyolali",
          'text' => "hasil22323"
        ]
      ];

      $signBSRE = Smt::signBSRE($pdf, $datas);

      if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
        $data =  base64_encode($signBSRE["data"]["file"]);

        return view('masterweb::module.admin.laboratorium.sample.blob',
          compact('data')
        );
      }elseif ($signBSRE['status'] == 500){
        return redirect()->back()->with('error-laporan', 'errors');
      }
    }

    $signOption = 0;

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.makmin', compact(
      'no_LHU',
      'samples',
      'labnums',
      'laboratoriummethodsArray',
      'param_methods',
      'tembusans',
      'validasi',
      'signOption',
      'fontsize',
      'lineHeight',
      'padding',
      'showKop'
    ));

    return $pdf->stream();
  }

  public function print_verifikasi($id, $idlab = null, Request $request)
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
      ->leftjoin('tb_sample_penerimaan', function ($join) {
        $join->on('tb_sample_penerimaan.id_sample_penerimaan', '=', DB::raw('(SELECT id_sample_penerimaan FROM tb_sample_penerimaan WHERE tb_sample_penerimaan.sample_id = tb_samples.id_samples AND tb_sample_penerimaan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

          ->whereNull('tb_sample_penerimaan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) {
        $join->on('tb_sample_penanganan.id_sample_penanganan', '=', DB::raw('(SELECT id_sample_penanganan FROM tb_sample_penanganan WHERE tb_sample_penanganan.sample_id = tb_samples.id_samples AND tb_sample_penanganan.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_pelaporan_hasil', function ($join) {
        $join->on('tb_pelaporan_hasil.id_pelaporan_hasil', '=', DB::raw('(SELECT id_pelaporan_hasil FROM tb_pelaporan_hasil WHERE tb_pelaporan_hasil.sample_id = tb_samples.id_samples AND tb_pelaporan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))
          // $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
          // ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
          ->whereNull('tb_pelaporan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_pengetikan_hasil', function ($join) {
        $join->on('tb_pengetikan_hasil.id_pengetikan_hasil', '=', DB::raw('(SELECT id_pengetikan_hasil FROM tb_pengetikan_hasil WHERE tb_pengetikan_hasil.sample_id = tb_samples.id_samples AND tb_pengetikan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

          // ->on('tb_samples.id_samples', '=', 'tb_pengetikan_hasil.sample_id')
          ->whereNull('tb_pengetikan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_verifikasi_hasil', function ($join) {
        $join->on('tb_verifikasi_hasil.id_verifikasi_hasil', '=', DB::raw('(SELECT id_verifikasi_hasil FROM tb_verifikasi_hasil WHERE tb_verifikasi_hasil.sample_id = tb_samples.id_samples AND tb_verifikasi_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

          ->whereNull('tb_verifikasi_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftjoin('tb_pengesahan_hasil', function ($join) {
        $join->on('tb_pengesahan_hasil.id_pengesahan_hasil', '=', DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND tb_pengesahan_hasil.deleted_at  is NULL AND tb_samples.deleted_at   is NULL  LIMIT 1)'))

          ->whereNull('tb_pengesahan_hasil.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->first();

    $fontsize = isset($request->fontsize)
      ? (float) $request->fontsize
      : (float) ($sample->fontsize_hasil_baca_hasil ?? 12.0);
    $lineHeight = isset($request->line_height)
      ? (float) $request->line_height
      : (float) ($sample->line_height_hasil_baca_hasil ?? 1.0);
    $padding = isset($request->padding)
      ? (float) $request->padding
      : (float) ($sample->padding_hasil_baca_hasil ?? 1.0);

    if (isset($request->show_kop)) {
      $showKop = ($request->show_kop === '1' || $request->show_kop === 1 || $request->show_kop === 'true' || $request->show_kop === 'on') ? 1 : 0;
    } else {
      $showKop = (int) ($sample->show_kop_hasil_baca_hasil ?? 1);
    }

    //    // dd($sample);
    //
    //
    //    $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $idlab)
    //      ->where('sample_id', '=', $id)
    //      ->orderBy('ms_method.created_at')
    //      ->join('ms_method', function ($join) {
    //        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
    //          ->whereNull('tb_sample_method.deleted_at')
    //          ->whereNull('ms_method.deleted_at');
    //      })
    //      ->get();
    //
    //    $laboratoriumprogress = LaboratoriumProgress::where('laboratorium_id', $idlab)->orderBy('order_sort', 'asc')->get();
    //    $laboratoriumsampleanalitikprogress = SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id', $idlab)
    //      ->where('tb_sample_analitik_progress.sample_id', $id)
    //      ->orderBy('tb_laboratorium_progress.order_sort', 'asc')
    //      ->join('tb_laboratorium_progress', function ($join) {
    //        $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
    //          ->on('tb_laboratorium_progress.laboratorium_id', '=', 'tb_sample_analitik_progress.laboratorium_id')
    //          ->whereNull('tb_laboratorium_progress.deleted_at')
    //          ->whereNull('tb_sample_analitik_progress.deleted_at');
    //      })
    //      ->get();
    //
    //    // dd($laboratoriumsampleanalitikprogress);

    //    // dd($laboratoriumsampleanalitikprogress);
    //    $penerimaan_sample = PenerimaanSample::where('sample_id', $id)
    //      ->join('ms_container', function ($join) {
    //        $join->on('ms_container.id_container', '=', 'tb_sample_penerimaan.wadah_id')
    //          ->whereNull('ms_container.deleted_at')
    //          ->whereNull('tb_sample_penerimaan.deleted_at');
    //      })
    //      ->join('ms_unit', function ($join) {
    //        $join->on('ms_unit.id_unit', '=', 'tb_sample_penerimaan.unit_id')
    //          ->whereNull('ms_unit.deleted_at')
    //          ->whereNull('tb_sample_penerimaan.deleted_at');
    //      })
    //      ->first();
    //
    //    $penanganan_sample = PenangananSample::where('sample_id', $id)->where('laboratorium_id', $idlab)->first();
    //
    //
    //    // dd($penanganan_sample);
    //
    //
    //    // dd($sample);

    $verificationActivitySamples = VerificationActivitySample::query()->where('id_sample', '=', $sample->id_samples)->get();
    $listVerifications = [];

    $url = config("app.url");
    if (config("app.env") == "local"){
      $url .= ":8000";
    }
    $url .= "/elits-signature/progress/".$id."/0";

    $result = Builder::create()
      ->data($url)
      ->encoding(new Encoding('UTF-8'))
      ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
      ->size(600)
      ->margin(2)
      ->build();

    $qrBase64 = base64_encode($result->getString());

    if (isset($request->tanggal_cetak_verifikasi)){
      $datePrintVerification = \DateTime::createFromFormat('d/m/Y' , $request->tanggal_cetak_verifikasi)->format('Y-m-d');
    }else{
      $datePrintVerification = Carbon::now()->toDateString();
    }

    $isPreviewMode = isset($request->mode) && $request->mode === 'preview';
    if ($isPreviewMode || (isset($request->signOption) && $request->signOption == 0)){
      foreach ($verificationActivitySamples as  $verificationActivitySample){
        $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
      }

      $signOption = 0;

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.print_verifikasi', compact('listVerifications', 'sample', 'datePrintVerification', 'signOption', 'fontsize', 'lineHeight', 'padding', 'showKop'));

      return $pdf->stream();
    }

    $dataPetugasBSRE = [];
    foreach ($verificationActivitySamples as  $verificationActivitySample) {
      $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;

      $petugas = Petugas::query()->where('nama', '=', $verificationActivitySample->nama_petugas)->get()->first();
      if (isset($petugas)){
        $dataPetugasBSRE[] = [
          'nik' => $petugas->nik,
          'passPhrase' => $petugas->password,
          'tampilan' => 'invisible',
          'reason' => "hasil12121",
          'location' => "boyolali",
          'text' => "hasil22323"
        ];
      }
    }


    if (count($listVerifications) == 5 and count($dataPetugasBSRE) == 5){

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.print_verifikasi', compact('listVerifications', 'sample', 'datePrintVerification', 'qrBase64', 'fontsize', 'lineHeight', 'padding', 'showKop'));

      $signBSRE = Smt::signBSRE($pdf, $dataPetugasBSRE);

      if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
        $data =  base64_encode($signBSRE["data"]["file"]);

        return view('masterweb::module.admin.laboratorium.sample.blob',
          compact('data')
        );
      }elseif ($signBSRE['status'] == 500){
        return redirect()->back()->with('error-verifikasi', 'errors');
      }else{
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Silahkan coba lagi!');
      }
    }else{
      if (count($listVerifications) < 5){
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap selesaikan seluruh step verifikasi!');
      }

      if (count($dataPetugasBSRE) < 5){
        return redirect()->back()->with('error-bsre', 'Kredensial untuk tanda tangan elektronik belum lengkap. Harap lengkapi kredensial seluruh petugas!');
      }
    }
  }

  public function printInformConcern($id, $idlab, $ischlor = null)
  {
    $sample = Sample::where('id_samples', '=', $id)
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
        $join
          ->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
          ->where('tb_sample_penanganan.laboratorium_id', '=', $idlab)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->first();

    $laboratorium = Laboratorium::findOrFail($idlab);

    $sampletype_id = $sample->id_sample_type;
    $jenis_makanan_id = $sample->jenis_makanan_id;
    $fontsize = (float) data_get($sample, 'fontsize_hasil_baca_hasil', 11.0);
    $lineHeight = (float) data_get($sample, 'line_height_hasil_baca_hasil', 1.0);
    $padding = (float) data_get($sample, 'padding_hasil_baca_hasil', 1.0);
    $showKop = (int) data_get($sample, 'show_kop_hasil_baca_hasil', 1);
    $kimia = [];
    $fisika = [];
    $kimiaMakanan = [];

    $penerimaan_sample = PenerimaanSample::where('sample_id', '=', $id)->firstOrFail();

    if (isset($jenis_makanan_id)) {
      $laboratoriummethods_sampletypes = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->where('sample_id', '=', $id)
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('tb_sample_method.deleted_at')
          ->whereNull('ms_method.deleted_at');
      })
      ->get();

      foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
        $kimiaMakanan[$laboratoriumMethod->params_method] = "checked";
      }
    } else {



        $laboratoriummethods_sampletypes = SampleMethod::where('laboratorium_id', '=', $idlab)
        ->where('sample_id', '=', $id)
        ->orderBy('ms_method.created_at')
        ->join('ms_method', function ($join) {
          $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('tb_sample_method.deleted_at')
            ->whereNull('ms_method.deleted_at');
        })
        ->get();



      foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
        if ($laboratoriumMethod->jenis_parameter_kimia == "kimiawi" || $laboratoriumMethod->jenis_parameter_kimia == "kimia organik") {
          $kimia[$laboratoriumMethod->params_method] = "checked";
        } else {
          $fisika[$laboratoriumMethod->params_method] = "checked";
        }
      }
    }

    // dd($laboratoriummethods_sampletypes);


    $list_kimia_wajib=[
      "Besi (Fe)" => "checked",
      "Fluorida"  => "checked",
      "Kesadahan (CaCO3)" => "checked",
      "Chlorida"  => "checked",
      "Mangan (Mn)"    => "checked",
      "Nitrat"    => "checked",
      "Nitrit"    => "checked",
      "Sianida"   => "checked",
      "pH"        => "checked",
      "Zat Organik"=> "checked",
      "Boraks"    => "checked",
      "Formalin"  => "checked",
      "Benzoat"   => "checked",
      "Salisilat" => "checked",
      "Pewarna"   => "checked",
      "Siklamat"  => "checked",
      "Sakarin"   => "checked",
      "Besi"      => "checked",
      "Chlorida"  => "checked",
      "pH"        => "checked",
      "Boraks"    => "checked",
      "Formalin"   => "checked"];


    $not_in_list_kimia = array_diff_key($kimia, $list_kimia_wajib);
    // dd($not_in_list_kimia);



    if ($laboratorium->kode_laboratorium == "MBI") {
      $airBersih = '';
      $airMinum  = '';
      $airLimbah = '';
      $bakteri   = '';
      $kuman     = '';

      // Reuse methods already fetched in this sample context
      $mikroMethods = collect($laboratoriummethods_sampletypes ?? [])
        ->pluck('params_method')
        ->filter()
        ->values()
        ->toArray();

      // Check methods to determine category
      $hasAirMinum = false;
      $hasAirBersih = false;
      $hasAirLimbah = false;
      $hasKuman = false;
      $hasBakteri = false;

      foreach ($mikroMethods as $methodName) {
        $methodNameLower = strtolower($methodName);

        // Check for Air Minum methods
        if (strpos($methodNameLower, 'air minum') !== false ||
            strpos($methodNameLower, 'escherichia coli') !== false ||
            strpos($methodNameLower, 'total coliform') !== false) {
          if (strpos(strtolower($sample->name_sample_type), 'air minum') !== false) {
            $hasAirMinum = true;
          }
        }

        // Check for Air Higiene methods (nama lama / parameter: air bersih)
        if (strpos($methodNameLower, 'air higiene') !== false || strpos($methodNameLower, 'air bersih') !== false) {
          if (strpos(strtolower($sample->name_sample_type), 'air higiene') !== false || strpos(strtolower($sample->name_sample_type), 'air bersih') !== false) {
            $hasAirBersih = true;
          }
        }

        // Check for Air Limbah methods
        if (strpos($methodNameLower, 'air limbah') !== false) {
          if (strpos(strtolower($sample->name_sample_type), 'air limbah') !== false) {
            $hasAirLimbah = true;
          }
        }

        // Check for Angka Kuman/ALT methods
        if (strpos($methodNameLower, 'angka kuman') !== false ||
            strpos($methodNameLower, 'alt') !== false ||
            strpos($methodNameLower, 'total plate count') !== false ||
            strpos($methodNameLower, 'tpc') !== false ||
            strpos($methodNameLower, 'kuman') !== false) {
          $hasKuman = true;
        }

        // Check for Bakteriologis Makanan methods
        if (strpos($methodNameLower, 'e. coli') !== false ||
            strpos($methodNameLower, 'escherichia coli') !== false ||
            strpos($methodNameLower, 'coliform') !== false ||
            strpos($methodNameLower, 'salmonella') !== false ||
            strpos($methodNameLower, 'staphylococcus') !== false ||
            strpos($methodNameLower, 'bakteri') !== false) {
          // Only set as bakteri if it's not for air (air methods are checked above)
          if (strpos(strtolower($sample->name_sample_type), 'air minum') === false &&
              (strpos(strtolower($sample->name_sample_type), 'air higiene') === false && strpos(strtolower($sample->name_sample_type), 'air bersih') === false) &&
              strpos(strtolower($sample->name_sample_type), 'air limbah') === false) {
            $hasBakteri = true;
          }
        }
      }

      // Determine category based on methods and sample type
      $nameSampleTypeLower = strtolower($sample->name_sample_type);

      if ($hasAirBersih || strpos($nameSampleTypeLower, 'air higiene') !== false || strpos($nameSampleTypeLower, 'air bersih') !== false) {
        $airBersih = 'checked';
      } elseif ($hasAirMinum || strpos($nameSampleTypeLower, 'air minum') !== false) {
        $airMinum = 'checked';
      } elseif ($hasAirLimbah || strpos($nameSampleTypeLower, 'air limbah') !== false) {
        $airLimbah = 'checked';
      } elseif ($hasKuman) {
        // Check for Angka Kuman/ALT based on method
        $kuman = 'checked';
      } elseif ($hasBakteri || strpos($nameSampleTypeLower, 'makanan/minuman/lainnya') !== false) {
        $bakteri = 'checked';
      } else {
        // Default fallback
        $kuman = 'checked';
      }
      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.inform_concern', compact('sample', 'airMinum', 'airBersih', 'airLimbah', 'bakteri', 'kuman', 'penerimaan_sample','not_in_list_kimia', 'fontsize', 'lineHeight', 'padding', 'showKop'));
      return $pdf->stream();
    }

    //dd($fisika, $kimia, $kimiaMakanan);

    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.inform_concern', compact('sample', 'kimia', 'fisika', 'kimiaMakanan', 'penerimaan_sample','not_in_list_kimia', 'fontsize', 'lineHeight', 'padding', 'showKop'));
    return $pdf->stream();
  }
  public function printInformConcernGabungan($idPermohonanUji)
  {
    $permohonan_uji = PermohonanUji::query()
      ->where('id_permohonan_uji', $idPermohonanUji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Get all samples for this permohonan uji
    $allSamples = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereNull('deleted_at')
      ->orderBy('count_id', 'asc')
      ->get();

    if ($allSamples->isEmpty()) {
      return abort(404, 'Tidak ada sample yang ditemukan');
    }

    // Group samples by laboratory type (Kimia/Fisika vs Mikro)
    $samplesByLabType = [
      'kimia' => [],
      'mikro' => []
    ];

    foreach ($allSamples as $sample) {
      $labCodes = SampleMethod::where('sample_id', $sample->id_samples)
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->pluck('kode_laboratorium')
        ->unique();


      foreach ($labCodes as $labCode) {
        if (in_array($labCode, ['KMA', 'FKA', 'KIM'])) {
          $samplesByLabType['kimia'][] = $sample->id_samples;
        } elseif ($labCode == 'MBI') {
          $samplesByLabType['mikro'][] = $sample->id_samples;
        }
      }
    }


    // Remove duplicates
    $samplesByLabType['kimia'] = array_unique($samplesByLabType['kimia']);
    $samplesByLabType['mikro'] = array_unique($samplesByLabType['mikro']);

    // Prepare data for all labs
    $allLabsData = [];

    // Process Kimia/Fisika samples
    if (!empty($samplesByLabType['kimia'])) {
      $idlabKimia = SampleMethod::where('sample_id', $samplesByLabType['kimia'][0])
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereIn('kode_laboratorium', ['KMA', 'FKA', 'KIM'])
        ->pluck('laboratorium_id')
        ->first();

      if ($idlabKimia) {
        $laboratoriumKimia = Laboratorium::findOrFail($idlabKimia);
        $samplesDataKimia = $this->processSamplesForLab($samplesByLabType['kimia'], $idlabKimia, false);

        if (!empty($samplesDataKimia)) {
          $allLabsData[] = [
            'type' => 'kimia',
            'laboratorium' => $laboratoriumKimia,
            'samplesData' => $samplesDataKimia
          ];
        }
      }
    }

    // Process Mikro samples
    if (!empty($samplesByLabType['mikro'])) {
      $idlabMikro = SampleMethod::where('sample_id', $samplesByLabType['mikro'][0])
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->where('kode_laboratorium', 'MBI')
        ->pluck('laboratorium_id')
        ->first();

      if ($idlabMikro) {
        $laboratoriumMikro = Laboratorium::findOrFail($idlabMikro);
        $samplesDataMikro = $this->processSamplesForLab($samplesByLabType['mikro'], $idlabMikro, true);

        if (!empty($samplesDataMikro)) {
          $allLabsData[] = [
            'type' => 'mikro',
            'laboratorium' => $laboratoriumMikro,
            'samplesData' => $samplesDataMikro
          ];
        }
      }
    }

    if (empty($allLabsData)) {
      return abort(404, 'Tidak ada data yang dapat diprint');
    }

    $reviewSample = null;
    if (!empty($allLabsData[0]['samplesData']) && !empty($allLabsData[0]['samplesData'][0]['sample'])) {
      $reviewSample = $allLabsData[0]['samplesData'][0]['sample'];
    }

    $fontsize = (float) data_get($reviewSample, 'fontsize_hasil_baca_hasil', 11.0);
    $lineHeight = (float) data_get($reviewSample, 'line_height_hasil_baca_hasil', 1.0);
    $padding = (float) data_get($reviewSample, 'padding_hasil_baca_hasil', 1.0);
    $showKop = (int) data_get($reviewSample, 'show_kop_hasil_baca_hasil', 1);

    // Generate combined PDF
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.inform_concern_combined', compact('allLabsData', 'permohonan_uji', 'fontsize', 'lineHeight', 'padding', 'showKop'));
    return $pdf->stream('Inform_Consent_Gabungan_' . $idPermohonanUji . '.pdf');
  }

  private function processSamplesForLab($sampleIds, $idlab, $isMikro = false)
  {
    $samplesData = [];

    foreach ($sampleIds as $sampleId) {
      $sample = Sample::where('id_samples', '=', $sampleId)
        ->join('tb_permohonan_uji', function ($join) {
          $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
            ->whereNull('tb_permohonan_uji.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_customer', function ($join) {
          $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
            ->whereNull('ms_customer.deleted_at')
            ->whereNull('tb_permohonan_uji.deleted_at');
        })
        ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
          $join->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
            ->where('tb_sample_penanganan.laboratorium_id', '=', $idlab)
            ->whereNull('tb_sample_penanganan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->leftjoin('tb_sample_penerimaan', function ($join) {
          $join->on('tb_samples.id_samples', '=', 'tb_sample_penerimaan.sample_id')
            ->whereNull('tb_sample_penerimaan.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->join('ms_sample_type', function ($join) {
          $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
            ->whereNull('ms_sample_type.deleted_at')
            ->whereNull('tb_samples.deleted_at');
        })
        ->first();

      if (!$sample) {
        continue;
      }

      $jenis_makanan_id = $sample->jenis_makanan_id;
      $kimia = [];
      $fisika = [];
      $kimiaMakanan = [];

      $penerimaan_sample = PenerimaanSample::where('sample_id', '=', $sampleId)->first();

      if (isset($jenis_makanan_id)) {
        $laboratoriummethods_sampletypes = SampleMethod::where('laboratorium_id', '=', $idlab)
          ->where('sample_id', '=', $sampleId)
          ->orderBy('ms_method.created_at')
          ->join('ms_method', function ($join) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at');
          })
          ->get();

        foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
          $kimiaMakanan[$laboratoriumMethod->params_method] = "checked";
        }
      } else {
        $laboratoriummethods_sampletypes = SampleMethod::where('laboratorium_id', '=', $idlab)
          ->where('sample_id', '=', $sampleId)
          ->orderBy('ms_method.created_at')
          ->join('ms_method', function ($join) {
            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
              ->whereNull('tb_sample_method.deleted_at')
              ->whereNull('ms_method.deleted_at');
          })
          ->get();

        foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
          if ($laboratoriumMethod->jenis_parameter_kimia == "kimiawi" || $laboratoriumMethod->jenis_parameter_kimia == "kimia organik") {
            $kimia[$laboratoriumMethod->params_method] = "checked";
          } else {
            $fisika[$laboratoriumMethod->params_method] = "checked";
          }
        }
      }

      $list_kimia_wajib = [
        "Besi (Fe)" => "checked",
        "Fluorida"  => "checked",
        "Kesadahan (CaCO3)" => "checked",
        "Chlorida"  => "checked",
        "Mangan (Mn)"    => "checked",
        "Nitrat"    => "checked",
        "Nitrit"    => "checked",
        "Sianida"   => "checked",
        "pH"        => "checked",
        "Zat Organik" => "checked",
        "Boraks"    => "checked",
        "Formalin"  => "checked",
        "Benzoat"   => "checked",
        "Salisilat" => "checked",
        "Pewarna"   => "checked",
        "Siklamat"  => "checked",
        "Sakarin"   => "checked",
        "Besi"      => "checked",
        "Chlorida"  => "checked",
        "pH"        => "checked",
        "Boraks"    => "checked",
        "Formalin"   => "checked"
      ];

      $not_in_list_kimia = array_diff_key($kimia, $list_kimia_wajib);

      $sampleData = [
        'sample' => $sample,
        'kimia' => $kimia,
        'fisika' => $fisika,
        'kimiaMakanan' => $kimiaMakanan,
        'penerimaan_sample' => $penerimaan_sample,
        'not_in_list_kimia' => $not_in_list_kimia
      ];

      if ($isMikro) {
        $airBersih = '';
        $airMinum  = '';
        $airLimbah = '';
        $bakteri   = '';
        $kuman     = '';

        // Reuse methods already fetched in this sample context
        $mikroMethods = collect($laboratoriummethods_sampletypes ?? [])
          ->pluck('params_method')
          ->filter()
          ->values()
          ->toArray();

        // Check methods to determine category
        $hasAirMinum = false;
        $hasAirBersih = false;
        $hasAirLimbah = false;
        $hasKuman = false;
        $hasBakteri = false;

        foreach ($mikroMethods as $methodName) {
          $methodNameLower = strtolower($methodName);

          // Check for Air Minum methods
          if (strpos($methodNameLower, 'air minum') !== false ||
              strpos($methodNameLower, 'escherichia coli') !== false ||
              strpos($methodNameLower, 'total coliform') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air minum') !== false) {
              $hasAirMinum = true;
            }
          }

          // Check for Air Higiene methods (nama lama / parameter: air bersih)
          if (strpos($methodNameLower, 'air higiene') !== false || strpos($methodNameLower, 'air bersih') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air higiene') !== false || strpos(strtolower($sample->name_sample_type), 'air bersih') !== false) {
              $hasAirBersih = true;
            }
          }

          // Check for Air Limbah methods
          if (strpos($methodNameLower, 'air limbah') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air limbah') !== false) {
              $hasAirLimbah = true;
            }
          }

          // Check for Angka Kuman/ALT methods
          if (strpos($methodNameLower, 'angka kuman') !== false ||
              strpos($methodNameLower, 'alt') !== false ||
              strpos($methodNameLower, 'total plate count') !== false ||
              strpos($methodNameLower, 'tpc') !== false ||
              strpos($methodNameLower, 'kuman') !== false) {
            $hasKuman = true;
          }

          // Check for Bakteriologis Makanan methods
          if (strpos($methodNameLower, 'e. coli') !== false ||
              strpos($methodNameLower, 'escherichia coli') !== false ||
              strpos($methodNameLower, 'coliform') !== false ||
              strpos($methodNameLower, 'salmonella') !== false ||
              strpos($methodNameLower, 'staphylococcus') !== false ||
              strpos($methodNameLower, 'bakteri') !== false) {
            // Only set as bakteri if it's not for air (air methods are checked above)
            if (strpos(strtolower($sample->name_sample_type), 'air minum') === false &&
                (strpos(strtolower($sample->name_sample_type), 'air higiene') === false && strpos(strtolower($sample->name_sample_type), 'air bersih') === false) &&
                strpos(strtolower($sample->name_sample_type), 'air limbah') === false) {
              $hasBakteri = true;
            }
          }
        }

        // Determine category based on methods and sample type
        $nameSampleTypeLower = strtolower($sample->name_sample_type);

        if ($hasAirBersih || strpos($nameSampleTypeLower, 'air higiene') !== false || strpos($nameSampleTypeLower, 'air bersih') !== false) {
          $airBersih = 'checked';
        } elseif ($hasAirMinum || strpos($nameSampleTypeLower, 'air minum') !== false) {
          $airMinum = 'checked';
        } elseif ($hasAirLimbah || strpos($nameSampleTypeLower, 'air limbah') !== false) {
          $airLimbah = 'checked';
        } elseif ($hasKuman) {
          // Check for Angka Kuman/ALT based on method
          $kuman = 'checked';
        } elseif ($hasBakteri || strpos($nameSampleTypeLower, 'makanan/minuman/lainnya') !== false) {
          $bakteri = 'checked';
        } else {
          // Default fallback
          $kuman = 'checked';
        }

        $sampleData['airMinum'] = $airMinum;
        $sampleData['airBersih'] = $airBersih;
        $sampleData['airLimbah'] = $airLimbah;
        $sampleData['bakteri'] = $bakteri;
        $sampleData['kuman'] = $kuman;
      }

      $samplesData[] = $sampleData;
    }

    return $samplesData;
  }

  public function printInformConcernGabunganByLab($idPermohonanUji, $labType)
  {
    $permohonan_uji = PermohonanUji::query()
      ->where('id_permohonan_uji', $idPermohonanUji)
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('ms_customer.deleted_at');
      })
      ->first();

    if (!$permohonan_uji) {
      return abort(404, 'Permohonan Uji tidak ditemukan');
    }

    // Get all samples for this permohonan uji
    $allSamples = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereNull('deleted_at')
      ->orderBy('count_id', 'asc')
      ->get();

    if ($allSamples->isEmpty()) {
      return abort(404, 'Tidak ada sample yang ditemukan');
    }

    // Tentukan lab ID berdasarkan tipe
    $targetLabCodes = [];
    if ($labType == 'kimia') {
      $targetLabCodes = ['KMA', 'FKA', 'KIM']; // Kimia dan Fisika
    } elseif ($labType == 'mikro') {
      $targetLabCodes = ['MBI']; // Mikrobiologi
    } else {
      return abort(404, 'Tipe lab tidak valid');
    }

    // Filter samples berdasarkan lab (batch, avoid query per-sample)
    $allSampleIds = $allSamples->pluck('id_samples')->filter()->unique()->values()->all();
    $sampleMethodsForTargetLab = collect();
    if (!empty($allSampleIds)) {
      $sampleMethodsForTargetLab = SampleMethod::whereIn('tb_sample_method.sample_id', $allSampleIds)
        ->whereNull('tb_sample_method.deleted_at')
        ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
        ->whereNull('ms_laboratorium.deleted_at')
        ->whereIn('kode_laboratorium', $targetLabCodes)
        ->select('tb_sample_method.sample_id', 'tb_sample_method.laboratorium_id', 'ms_laboratorium.kode_laboratorium')
        ->get();
    }
    $filteredSampleIds = $sampleMethodsForTargetLab->pluck('sample_id')->unique()->values()->all();

    if (empty($filteredSampleIds)) {
      return abort(404, 'Tidak ada sample untuk lab ' . $labType);
    }

    // Get lab ID (ambil yang pertama)
    $idlab = optional($sampleMethodsForTargetLab->first())->laboratorium_id;

    $laboratorium = Laboratorium::findOrFail($idlab);
    $samplesData = [];

    // Prefetch penerimaan sample untuk semua sample terfilter
    $penerimaanBySampleId = PenerimaanSample::whereIn('sample_id', $filteredSampleIds)
      ->get()
      ->keyBy('sample_id');

    // Prefetch method list per-sample untuk lab terpilih
    $methodsBySampleId = SampleMethod::where('laboratorium_id', '=', $idlab)
      ->whereIn('sample_id', $filteredSampleIds)
      ->whereNull('tb_sample_method.deleted_at')
      ->orderBy('ms_method.created_at')
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('tb_sample_method.sample_id', 'ms_method.params_method', 'ms_method.jenis_parameter_kimia')
      ->get()
      ->groupBy('sample_id');

    // Prefetch detail sample agar tidak query per-sample di loop
    $sampleDetailRows = Sample::whereIn('tb_samples.id_samples', $filteredSampleIds)
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_customer', function ($join) {
        $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
          ->whereNull('ms_customer.deleted_at')
          ->whereNull('tb_permohonan_uji.deleted_at');
      })
      ->leftjoin('tb_sample_penanganan', function ($join) use ($idlab) {
        $join->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
          ->where('tb_sample_penanganan.laboratorium_id', '=', $idlab)
          ->whereNull('tb_sample_penanganan.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.*', 'tb_sample_penanganan.*', 'ms_customer.*', 'tb_permohonan_uji.*')
      ->get()
      ->keyBy('id_samples');

    // Process each sample
    foreach ($filteredSampleIds as $sampleId) {
      $sample = $sampleDetailRows->get($sampleId);

      if (!$sample) {
        continue;
      }

      $sampletype_id = $sample->id_sample_type;
      $jenis_makanan_id = $sample->jenis_makanan_id;
      $kimia = [];
      $fisika = [];
      $kimiaMakanan = [];

      $penerimaan_sample = $penerimaanBySampleId->get($sampleId);
      $laboratoriummethods_sampletypes = $methodsBySampleId->get($sampleId, collect());

      if (isset($jenis_makanan_id)) {
        foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
          $kimiaMakanan[$laboratoriumMethod->params_method] = "checked";
        }
      } else {
        foreach ($laboratoriummethods_sampletypes as $laboratoriumMethod) {
          if ($laboratoriumMethod->jenis_parameter_kimia == "kimiawi" || $laboratoriumMethod->jenis_parameter_kimia == "kimia organik") {
            $kimia[$laboratoriumMethod->params_method] = "checked";
          } else {
            $fisika[$laboratoriumMethod->params_method] = "checked";
          }
        }
      }

      $list_kimia_wajib = [
        "Besi (Fe)" => "checked",
        "Fluorida"  => "checked",
        "Kesadahan (CaCO3)" => "checked",
        "Chlorida"  => "checked",
        "Mangan (Mn)"    => "checked",
        "Nitrat"    => "checked",
        "Nitrit"    => "checked",
        "Sianida"   => "checked",
        "pH"        => "checked",
        "Zat Organik" => "checked",
        "Boraks"    => "checked",
        "Formalin"  => "checked",
        "Benzoat"   => "checked",
        "Salisilat" => "checked",
        "Pewarna"   => "checked",
        "Siklamat"  => "checked",
        "Sakarin"   => "checked",
        "Besi"      => "checked",
        "Chlorida"  => "checked",
        "pH"        => "checked",
        "Boraks"    => "checked",
        "Formalin"   => "checked"
      ];

      $not_in_list_kimia = array_diff_key($kimia, $list_kimia_wajib);

      // Store sample data
      $sampleData = [
        'sample' => $sample,
        'kimia' => $kimia,
        'fisika' => $fisika,
        'kimiaMakanan' => $kimiaMakanan,
        'penerimaan_sample' => $penerimaan_sample,
        'not_in_list_kimia' => $not_in_list_kimia
      ];

      // Add additional data for mikro lab
      if ($laboratorium->kode_laboratorium == "MBI") {
        $airBersih = '';
        $airMinum  = '';
        $airLimbah = '';
        $bakteri   = '';
        $kuman     = '';

        // Reuse methods already fetched in this sample context
        $mikroMethods = collect($laboratoriummethods_sampletypes ?? [])
          ->pluck('params_method')
          ->filter()
          ->values()
          ->toArray();

        // Check methods to determine category
        $hasAirMinum = false;
        $hasAirBersih = false;
        $hasAirLimbah = false;
        $hasKuman = false;
        $hasBakteri = false;

        foreach ($mikroMethods as $methodName) {
          $methodNameLower = strtolower($methodName);

          // Check for Air Minum methods
          if (strpos($methodNameLower, 'air minum') !== false ||
              strpos($methodNameLower, 'escherichia coli') !== false ||
              strpos($methodNameLower, 'total coliform') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air minum') !== false) {
              $hasAirMinum = true;
            }
          }

          // Check for Air Higiene methods (nama lama / parameter: air bersih)
          if (strpos($methodNameLower, 'air higiene') !== false || strpos($methodNameLower, 'air bersih') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air higiene') !== false || strpos(strtolower($sample->name_sample_type), 'air bersih') !== false) {
              $hasAirBersih = true;
            }
          }

          // Check for Air Limbah methods
          if (strpos($methodNameLower, 'air limbah') !== false) {
            if (strpos(strtolower($sample->name_sample_type), 'air limbah') !== false) {
              $hasAirLimbah = true;
            }
          }

          // Check for Angka Kuman/ALT methods
          if (strpos($methodNameLower, 'angka kuman') !== false ||
              strpos($methodNameLower, 'alt') !== false ||
              strpos($methodNameLower, 'total plate count') !== false ||
              strpos($methodNameLower, 'tpc') !== false ||
              strpos($methodNameLower, 'kuman') !== false) {
            $hasKuman = true;
          }

          // Check for Bakteriologis Makanan methods
          if (strpos($methodNameLower, 'e. coli') !== false ||
              strpos($methodNameLower, 'escherichia coli') !== false ||
              strpos($methodNameLower, 'coliform') !== false ||
              strpos($methodNameLower, 'salmonella') !== false ||
              strpos($methodNameLower, 'staphylococcus') !== false ||
              strpos($methodNameLower, 'bakteri') !== false) {
            // Only set as bakteri if it's not for air (air methods are checked above)
            if (strpos(strtolower($sample->name_sample_type), 'air minum') === false &&
                (strpos(strtolower($sample->name_sample_type), 'air higiene') === false && strpos(strtolower($sample->name_sample_type), 'air bersih') === false) &&
                strpos(strtolower($sample->name_sample_type), 'air limbah') === false) {
              $hasBakteri = true;
            }
          }
        }

        // Determine category based on methods and sample type
        $nameSampleTypeLower = strtolower($sample->name_sample_type);

        if ($hasAirBersih || strpos($nameSampleTypeLower, 'air higiene') !== false || strpos($nameSampleTypeLower, 'air bersih') !== false) {
          $airBersih = 'checked';
        } elseif ($hasAirMinum || strpos($nameSampleTypeLower, 'air minum') !== false) {
          $airMinum = 'checked';
        } elseif ($hasAirLimbah || strpos($nameSampleTypeLower, 'air limbah') !== false) {
          $airLimbah = 'checked';
        } elseif ($hasKuman) {
          // Check for Angka Kuman/ALT based on method
          $kuman = 'checked';
        } elseif ($hasBakteri || strpos($nameSampleTypeLower, 'makanan/minuman/lainnya') !== false) {
          $bakteri = 'checked';
        } else {
          // Default fallback
          $kuman = 'checked';
        }

        $sampleData['airMinum'] = $airMinum;
        $sampleData['airBersih'] = $airBersih;
        $sampleData['airLimbah'] = $airLimbah;
        $sampleData['bakteri'] = $bakteri;
        $sampleData['kuman'] = $kuman;
      }

      $samplesData[] = $sampleData;
    }

    // Generate PDF
    if (!empty($samplesData)) {
      $reviewSample = $samplesData[0]['sample'] ?? null;
      $fontsize = (float) data_get($reviewSample, 'fontsize_hasil_baca_hasil', 11.0);
      $lineHeight = (float) data_get($reviewSample, 'line_height_hasil_baca_hasil', 1.0);
      $padding = (float) data_get($reviewSample, 'padding_hasil_baca_hasil', 1.0);
      $showKop = (int) data_get($reviewSample, 'show_kop_hasil_baca_hasil', 1);

      if ($laboratorium->kode_laboratorium == "MBI") {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.inform_concern_gabungan', compact('samplesData', 'permohonan_uji', 'laboratorium', 'fontsize', 'lineHeight', 'padding', 'showKop'));
        return $pdf->stream('Inform_Consent_Mikro_' . $idPermohonanUji . '.pdf');
      } else {
        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.inform_concern_gabungan', compact('samplesData', 'permohonan_uji', 'laboratorium', 'fontsize', 'lineHeight', 'padding', 'showKop'));
        return $pdf->stream('Inform_Consent_Kimia_' . $idPermohonanUji . '.pdf');
      }
    }

    return abort(404, 'Tidak ada data yang dapat diprint');
  }

  public function updateNamaPengambil(Request $request, $id)
  {
    //dd($id);
    $request->validate([
        'name_send_sample' => 'required',
    ]);

    $sample = Sample::findOrFail($id);
    $sample->name_send_sample = $request->name_send_sample;
    $sample->save();

    if (!empty($sample->permohonan_uji_id)) {
      $permohonan_uji = PermohonanUji::find($sample->permohonan_uji_id);
      if ($permohonan_uji) {
        $permohonan_uji->name_sampling = $request->name_send_sample;
        $permohonan_uji->save();
      }
    }

    return redirect()->back()->with('status', 'Nama pengambil berhasil diperbarui.');
  }

  public function getSamplesByPermohonanUjiAndSampleTypeMikro($idPermohonanUji, $idSampleType)
  {

    $samples = Sample::query();

    if($idSampleType != "air-bersih-air-minum"){
      $samples = $samples->where('permohonan_uji_id', '=', $idPermohonanUji)->where('typesample_samples', '=', $idSampleType);
    }else{


      $samples = $samples->where('permohonan_uji_id', '=', $idPermohonanUji)

      ->where(function ($query) {
        $query->whereIn('name_sample_type', ['Air Higiene', 'Air Bersih'])
              ->orWhere('name_sample_type', 'Air Minum');
      });

    }



    $samples = $samples
      ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
      ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
      ->where('tb_sample_method.laboratorium_id', '=', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5') // Lab Mikro
      ->select('id_samples', 'location_samples', 'name_pelanggan', 'count_id', 'name_sample_type', 'titik_pengambilan', 'nama_jenis_makanan')
      ->distinct('id_samples')
      ->orderBy('count_id', 'asc')
      ->get();


    return $samples;
  }

  public function getSamplesByPermohonanUjiAndSampleTypeKimia($idPermohonanUji, $idSampleType)
  {

    $samples = Sample::query();

    if($idSampleType != "air-bersih-air-minum"){
      $samples = $samples->where('permohonan_uji_id', '=', $idPermohonanUji)->where('typesample_samples', '=', $idSampleType);
    }else{


      $samples = $samples->where('permohonan_uji_id', '=', $idPermohonanUji)

      ->where(function ($query) {
        $query->whereIn('name_sample_type', ['Air Higiene', 'Air Bersih'])
              ->orWhere('name_sample_type', 'Air Minum');
      });

    }

    $samples = $samples
      ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
      ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
      ->where('tb_sample_method.laboratorium_id', '=', '3416ca19-6c69-4e5f-a004-ae8275de7644') // Lab Kimia
      ->select('id_samples', 'titik_pengambilan','tb_sample_method.laboratorium_id','location_samples', 'name_pelanggan', 'count_id', 'name_sample_type')
      // ->distinct('id_samples')
      ->orderBy('count_id', 'asc')
      ->get();





    return $samples;
  }


  public function getSamplesMikroBySampleId($idSample, $labId)
  {
    $sample = Sample::query()->where('id_samples', $idSample)->select('permohonan_uji_id', 'typesample_samples')->first();
    $sampleType = $sample->typesample_samples;
    $sampleAB = null;
    $sampleAM = null;
    if ($sampleType == "c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0"){
      $sampleAB = $sampleType;
      $sampleAM = "65df8403-b29f-4645-a1ed-12d2aeff1fbd";
    }
    if ($sampleType == "65df8403-b29f-4645-a1ed-12d2aeff1fbd"){
      $sampleAM = $sampleType;
      $sampleAB = "c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0";
    }

    if (isset($sampleAM) and $sampleAB){
      $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
        ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
        ->where('tb_sample_method.laboratorium_id', '=', $labId)
        ->where(function ($query) use ($sampleAB, $sampleAM) {
          $query->where('tb_samples.typesample_samples', $sampleAB)
            ->orWhere('tb_samples.typesample_samples', $sampleAM);
        })
        ->select('id_samples', 'location_samples', 'name_pelanggan', 'count_id', 'name_sample_type')
        ->distinct('id_samples')
        ->orderBy('count_id', 'asc')
        ->get();
    }elseif (isset($sampleAM)){
      $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
        ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
        ->where('tb_sample_method.laboratorium_id', '=', $labId)
        ->where('tb_samples.typesample_samples', '=', $sampleType)
        ->select('id_samples', 'location_samples', 'name_pelanggan', 'count_id', 'name_sample_type')
        ->distinct('id_samples')
        ->orderBy('count_id', 'asc')
        ->get();
    }elseif (isset($sampleAB)){
      $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
        ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
        ->where('tb_sample_method.laboratorium_id', '=', $labId)
        ->where('tb_samples.typesample_samples', '=', $sampleType)
        ->select('id_samples', 'location_samples', 'name_pelanggan', 'count_id', 'name_sample_type')
        ->distinct('id_samples')
        ->orderBy('count_id', 'asc')
        ->get();
    }else{
      $samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
        ->join('tb_sample_method', 'tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
        ->join('ms_sample_type', 'tb_samples.typesample_samples', '=', 'ms_sample_type.id_sample_type')
        ->where('tb_sample_method.laboratorium_id', '=', $labId)
        ->where('tb_samples.typesample_samples', '=', $sampleType)
        ->select('id_samples', 'location_samples', 'name_pelanggan', 'count_id', 'name_sample_type')
        ->distinct('id_samples')
        ->orderBy('count_id', 'asc')
        ->get();
    }

      return $samples;
  }
  public function printMikroGabungan(Request $request, $idPermohonanUji)
  {

    $agenda = $request->input('agenda');
    $no_agenda = !empty($agenda) ? '445.02/' . $agenda . '/AB-AM/05.31/' . date("Y") : '445.02/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/AB-AM/05.31/' . date("Y");


    $permohonan_uji = PermohonanUji::query()->where('id_permohonan_uji', $idPermohonanUji)->first();
    $customer= $permohonan_uji->customer;

    $samples = Sample::query()->where('permohonan_uji_id', $idPermohonanUji)
      ->whereHas('samplemethod', function ($query) {
        $query->where('laboratorium_id', "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5");
      })
      ->with(['sampleresult.method', 'labnum', 'sampleresult.method.bakumutu', 'sampleresult.method.bakumutu.unit'])
      ->orderBy('count_id', 'asc')
      ->get();


    $samplePrint = $request->printSamples;

    if (isset($samplePrint) && is_array($samplePrint) && count($samplePrint) > 0) {
      $samplePrint = array_map('strval', $samplePrint);
      $samples = $samples->filter(function ($sample) use ($samplePrint) {
        return in_array((string) $sample->id_samples, $samplePrint, true);
      })->values();
    }

    if ($samples->isEmpty()) {
      return abort(404, 'Tidak ada sample mikro yang dapat dicetak');
    }

    // Override baku mutu khusus sampel (baca-hasil) untuk print mikro gabungan
    $mbiLabId = (string) (Laboratorium::where('kode_laboratorium', 'MBI')->whereNull('deleted_at')->value('id_laboratorium')
      ?: 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5');
    $samples = $this->applyBakuMutuSampleOverridesToSampleCollection($samples, $mbiLabId);

    $listVerifications =
    $jenis_sarana = [];
    $tanggal_sampling = [];
    $labNums = [];
    $metode_pemeriksaan = [];
    $params = [];
    $methods = [];
    $results = [];
    $bakumutu = [];

    $firstSample = $samples->first();
    if (!$firstSample) {
      return abort(404, 'Tidak ada sample mikro yang dapat dicetak');
    }

    foreach($samples as $sample){
      $jenis_sarana[] = $sample->jenis_sarana_names;
      $tanggal_sampling[] = Carbon::parse($sample->datesampling_samples)->toDateString();
      $labNumValue = optional($sample->labnum->first())->lab_number;
      if (!is_null($labNumValue)) {
        $labNums[] = $labNumValue;
      }

      $result_item = [
        'labnum' => $sample->count_id,
        'lokasi' => $sample->location_samples ?? $sample->name_pelanggan,
        'jam_sampling' => date("H:i", strtotime($sample->datesampling_samples)),
        'hasil' => [],
      ];

      foreach($sample->sampleresult as $sampleresult){
        $metode_pemeriksaan[] = $sampleresult->metode;
        $result_item['hasil'][] = [
          $sampleresult->method_id => [
            'hasil' => $sampleresult->hasil,
            'metode' => $sampleresult->metode,
            'keterangan' => $sampleresult->keterangan,
            'offset_baku_mutu' => $sampleresult->offset_baku_mutu,
            'bakumutu' => null
          ]
        ];

        foreach($sampleresult->method as $method)
        {
          $bakumutu[$method->id_method] = $method->bakumutu;
          if ($method->params_method == "E-Coli (Membran Filter)") {
            $params[$method->id_method] = "Escherichia coli";
          } elseif ($method->params_method == "Total Coliform (Membrane Filter)") {
              $params[$method->id_method] = "Total Coliform";
          } else {
              $params[$method->id_method] = $method->params_method;
          }
        }
      }

      $results[] = $result_item;
    }

    $jenis_sarana = array_unique(array_filter(array_map('trim', $jenis_sarana)));

    // Bentuk nomor sampel gabungan:
    // - Jika hanya satu jenis (misal Air Minum): AM.02/0094 - 0096/2025
    // - Jika gabungan Air Higiene & Air Minum: AM-AB.02/0094 - 0096/2025
    $prefixes = [];
    foreach ($samples as $s) {
      if (!empty($s->codesample_samples) && preg_match('/^([A-Z]+)\./', $s->codesample_samples, $m)) {
        $prefixes[$m[1]] = true;
      }
    }
    $prefixStr = implode('-', array_keys($prefixes));

    // Ambil kode sampel pertama untuk referensi bulan/tahun
    $firstCode = $firstSample->codesample_samples ?? null; // contoh: AB.02/0094/2025
    $monthPart = '';
    $yearPart = date('Y');
    if ($firstCode && preg_match('/^[A-Z]+\.([0-9]{2})\/[0-9]+\/([0-9]{4})$/', $firstCode, $m)) {
      $monthPart = $m[1];
      $yearPart = $m[2];
    }

    $minLab = !empty($labNums) ? min($labNums) : 0;
    $maxLab = !empty($labNums) ? max($labNums) : 0;
    $minStr = sprintf('%03d', (int) $minLab);
    $maxStr = sprintf('%03d', (int) $maxLab);

    if ($minLab === $maxLab) {
      // Satu nomor saja
      $no_register = "{$prefixStr}.{$monthPart}/{$minStr}/{$yearPart}";
    } else {
      // Rentang nomor
      $no_register = "{$prefixStr}.{$monthPart}/{$minStr} - {$maxStr}/{$yearPart}";
    }

    $verificationActivitySamples = VerificationActivitySample::query()->where('id_sample', '=', $firstSample->id_samples)->get();
    $listVerifications = [];
    foreach ($verificationActivitySamples as  $verificationActivitySample) {
      $listVerifications[$verificationActivitySample->id_verification_activity] = $verificationActivitySample;
    }

    $tembusan = Sample::where('id_samples', $firstSample->id_samples)->value('tembusan');

    $signOption = $request->signOption;


    arsort($params);
    if(isset($signOption) and $signOption == 0){
      $data = [
        "no_agenda" => $no_agenda,
        "no_register" => $no_register,
        "nama_pelanggan" => $customer->name_customer,
        "alamat_pelanggan" => $customer->address_customer,
        "jenis_sampel" => "Air Higiene dan Air Minum",
        "jenis_sarana" => $jenis_sarana,
        "metode_pemeriksaan" => array_unique($metode_pemeriksaan),
        "petugas_sampling" => $permohonan_uji->name_sampling,
        "tanggal_sampling" => $tanggal_sampling,
        "parameter" => array_unique($params),
        "results" => $samples,
        "bakumutu" => $bakumutu,
      ];

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih_air_minum', compact('data', 'listVerifications', 'tembusan', 'signOption'));
      return $pdf->stream();
    }else{


      $url = config("app.url");
      if (config("app.env") == "local"){
        $url .= ":8000";
      }
      $url .= "/elits-signature/progress/".$firstSample->id_samples."/0";

      $result = Builder::create()
        ->data($url)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->size(600)
        ->margin(2)
        ->build();

      $qrBase64 = base64_encode($result->getString());


      $data = [
        "no_agenda" => $no_agenda,
        "no_register" => $no_register,
        "nama_pelanggan" => $customer->name_customer,
        "alamat_pelanggan" => $customer->address_customer,
        "jenis_sampel" => "Air Higiene dan Air Minum",
        "jenis_sarana" => $jenis_sarana,
        "metode_pemeriksaan" => array_unique($metode_pemeriksaan),
        "petugas_sampling" => $permohonan_uji->name_sampling,
        "tanggal_sampling" => $tanggal_sampling,
        "parameter" => array_unique($params),
        "results" => $samples,
        "qr_code" => ""
      ];

      $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.formatPrint.mikro.air_bersih_air_minum', compact('data', 'listVerifications', 'tembusan', 'signOption', 'qrBase64'));

      $dataKepalaLab = Petugas::query()->where('nik', '=', '3309094611720002')->where('nama', '=', 'dr. Muharyati')->get(['nik', 'password'])->first();

      if (!isset($agenda)){
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap input agenda!');
      }

      if (count($listVerifications) < 5){
        return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Harap selesaikan seluruh step verifikasi!');
      }

      if (isset($dataKepalaLab->nik) and isset($dataKepalaLab->password)){

        $dataPetugasBSRE[] = [
          'nik' => $dataKepalaLab->nik,
          'passPhrase' => $dataKepalaLab->password,
          'tampilan' => 'invisible',
          'reason' => "hasil12121",
          'location' => "boyolali",
          'text' => "hasil22323"
        ];

        $signBSRE = Smt::signBSRE($pdf, $dataPetugasBSRE);

        if (isset($signBSRE["status"]) and $signBSRE["status"] == "success" and isset($signBSRE["data"]) and $signBSRE["data"]["status"] == 200){
          $data =  base64_encode($signBSRE["data"]["file"]);

          return view('masterweb::module.admin.laboratorium.sample.blob',
            compact('data')
          );
        }elseif ($signBSRE['status'] == 500){
          return redirect()->back()->with('error-laporan', 'errors');
        }else{
          return redirect()->back()->with('error-bsre', 'Dokumen belum bisa ditanda tangani secara elektronik. Silahkan coba lagi!');
        }

      }else{
        return redirect()->back()->with('error-bsre', 'Kredensial untuk tanda tangan elektronik belum lengkap. Harap lengkapi kredensial seluruh petugas!');
      }
    }
  }

  /**
   * Menampilkan form input penerimaan sampel massal untuk semua sampel dalam satu permohonan uji
   */
  public function penerimaanSampelForm($id_samples, $id_permohonan_uji, $idlabs)
  {
    Carbon::setLocale('id');

    // Ambil semua sample dari permohonan uji yang sama untuk lab ini
    $samples = Sample::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_sample_method', function ($join) use ($idlabs) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) use ($idlabs) {
        $join->on('ms_laboratorium.id_laboratorium', '=', DB::raw("'".$idlabs."'"))
          ->whereNull('ms_laboratorium.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.name_sample_type', 'ms_laboratorium.kode_laboratorium')
      ->distinct()
      ->get();

    // Ambil permohonan uji
    $permohonan_uji = PermohonanUji::find($id_permohonan_uji);

    // Ambil data laboratorium
    $laboratorium = Laboratorium::find($idlabs);

    // Ambil existing data penerimaan sample jika ada
    $existing_penerimaan = [];
    foreach ($samples as $sample) {
      $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->first();
      if ($penerimaan) {
        $existing_penerimaan[$sample->id_samples] = $penerimaan;
      }
    }

    // Ambil parameter per sample
    foreach ($samples as $sample) {
      $methods = SampleMethod::where('sample_id', $sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->join('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
        ->select('ms_method.params_method')
        ->get();
      $sample->parameters = $methods;
    }

    // Ambil semua verification activity dan convert ke array berdasarkan ID
    $verificationActivity = VerificationActivity::all()->keyBy('id')->toArray();

    // Ambil list penerima sampel dari VerificationActivity id = 7
    // Jika is_sampling = 1: ambil dari field lab (mikro/kimia/klnik) - penerima sample di lab tersebut
    // Jika is_sampling = 0: ambil dari field register - semua pendaftaran
    $penerima_sampel_list = [];
    if (isset($verificationActivity[7])) {
      $activity7 = (object) $verificationActivity[7];
      $activity1 = (object) $verificationActivity[1];
      if ($permohonan_uji->is_sampling == 1) {
        // Sampling: ambil dari field lab (penerima sample di lab tersebut)
        if ($laboratorium['kode_laboratorium'] == 'MBI') {
          $penerima_sampel_list = array_filter(explode(', ', $activity7->mikro ?? ''));
        } elseif ($laboratorium['kode_laboratorium'] == 'KIM') {
          $penerima_sampel_list = array_filter(explode(', ', $activity7->kimia ?? ''));
        } else {
          $penerima_sampel_list = array_filter(explode(', ', $activity7->klnik ?? ''));
        }
      } else {
        // Tidak sampling: ambil dari field register (semua pendaftaran)
        $penerima_sampel_list = array_filter(explode(', ',  $activity1->register ?? ''));
        $penerima_sampel_list = array_merge($penerima_sampel_list, array_filter(explode(', ', $activity1->klinik ?? '')));
      }
    }

    // Ambil list analis dari VerificationActivity id = 2 (Input Hasil/Analitik)
    $analis_list = [];
    if (isset($verificationActivity[2])) {
      $activity2 = (object) $verificationActivity[2];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $analis_list = array_filter(explode(', ', $activity2->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $analis_list = array_filter(explode(', ', $activity2->kimia ?? ''));
      } else {
        $analis_list = array_filter(explode(', ', $activity2->klnik ?? ''));
      }
    }

    // Ambil list koordinator kesmas dari VerificationActivity id = 4
    $koordinator_kesmas_list = [];
    if (isset($verificationActivity[4])) {
      $activity4 = (object) $verificationActivity[4];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $koordinator_kesmas_list = array_filter(explode(', ', $activity4->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $koordinator_kesmas_list = array_filter(explode(', ', $activity4->kimia ?? ''));
      } else {
        $koordinator_kesmas_list = array_filter(explode(', ', $activity4->klnik ?? ''));
      }
    }

    // Deteksi step mana yang sedang aktif di view
    $step_penerima_done = false;
    $step_koordinator_done = false;
    $step_analis_done = false;

    // Hitung jumlah sample yang sudah memiliki data penerima DAN pengawetan
    $samples_with_penerima = 0;
    $samples_with_pengawetan = 0;
    $total_samples = count($samples);

    foreach ($samples as $sample) {
      $penerimaan = isset($existing_penerimaan[$sample->id_samples]) ? $existing_penerimaan[$sample->id_samples] : null;

      // Cek data penerima
      if ($penerimaan && !empty($penerimaan->penerima_sampel) && !empty($penerimaan->penerima_tanggal)) {
        $samples_with_penerima++;
      }

      // Cek data pengawetan
      if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
        $samples_with_pengawetan++;
      }
    }

    // Step 1 selesai hanya jika semua sample sudah memiliki data penerima DAN semua sample sudah memiliki pengawetan
    $step_penerima_done = ($samples_with_penerima == $total_samples &&
                          $samples_with_pengawetan == $total_samples &&
                          $total_samples > 0);

    $first_penerimaan = count($existing_penerimaan) > 0 ? reset($existing_penerimaan) : null;
    if ($step_penerima_done && $first_penerimaan && !empty($first_penerimaan->disposisi_koordinator_kesmas) && !empty($first_penerimaan->disposisi_tanggal)) {
      $step_koordinator_done = true;
    }
    if ($step_koordinator_done && $first_penerimaan && !empty($first_penerimaan->disposisi_analis) && !empty($first_penerimaan->disposisi_analis_tanggal)) {
      $step_analis_done = true;
    }

    // Cek BSRE_USE dari config
    $use_tte = config('app.bsre_use', false);

    return view('masterweb::module.admin.laboratorium.sample.penerimaan-sampel-massal', compact('samples', 'permohonan_uji', 'laboratorium', 'existing_penerimaan', 'idlabs', 'penerima_sampel_list', 'analis_list', 'koordinator_kesmas_list', 'use_tte', 'step_penerima_done', 'step_koordinator_done', 'step_analis_done'));
  }

  /**
   * Halaman verifikasi Pemeriksaan / Analitik
   */
  public function verifikasiPemeriksaanAnalitik($id_samples, $id_permohonan_uji, $idlabs)
  {
    Carbon::setLocale('id');

    // Ambil semua sample dari permohonan uji yang sama untuk lab ini
    $samples = Sample::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_sample_method', function ($join) use ($idlabs) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->join('ms_laboratorium', function ($join) use ($idlabs) {
        $join->on('ms_laboratorium.id_laboratorium', '=', DB::raw("'".$idlabs."'"))
          ->whereNull('ms_laboratorium.deleted_at');
      })
      ->select('tb_samples.*', 'ms_sample_type.name_sample_type', 'ms_laboratorium.kode_laboratorium')
      ->distinct()
      ->get();

    // Ambil permohonan uji
    $permohonan_uji = PermohonanUji::find($id_permohonan_uji);

    // Ambil data laboratorium
    $laboratorium = Laboratorium::find($idlabs);

    // Ambil data penerimaan sample untuk mendapatkan default disposisi_analis
    $first_penerimaan = null;
    $default_analis = null;
    if ($samples->count() > 0) {
      $first_sample = $samples->first();
      $penerimaan = PenerimaanSample::where('sample_id', $first_sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->first();
      if ($penerimaan) {
        $first_penerimaan = $penerimaan;
        $default_analis = $penerimaan->disposisi_analis;
      }
    }

    // Ambil data Pendaftaran (verification_step = 1) untuk default start date
    $pendaftaran_verif = null;
    $default_start_date = null;
    $default_stop_date = null;
    if ($samples->count() > 0) {
      $first_sample = $samples->first();
      $pendaftaran_verif = VerificationActivitySample::where('id_sample', $first_sample->id_samples)
        ->where('id_verification_activity', 1)
        ->first();

      if ($pendaftaran_verif && $pendaftaran_verif->stop_date) {
        // Default start date = Pendaftaran Stop
        $default_start_date = Carbon::parse($pendaftaran_verif->stop_date);
      } else {
        $default_start_date = Carbon::now();
      }

      // Adjust to work hours (8:00 - 15:00)
      if ($default_start_date->hour < 8) {
        $default_start_date->setTime(8, 0, 0);
      } elseif ($default_start_date->hour >= 15) {
        $default_start_date->addDay()->setTime(8, 0, 0);
      }

      // Default stop date = start date + 2 hari (untuk Kimia) atau +3 hari (untuk Mikro)
      $default_stop_date = $default_start_date->copy();
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $default_stop_date->addDays(3); // Mikro: +3 hari
      } else {
        $default_stop_date->addDays(2); // Kimia/Klinik: +2 hari
      }

      // Adjust stop date to work hours
      if ($default_stop_date->hour < 8) {
        $default_stop_date->setTime(8, 0, 0);
      } elseif ($default_stop_date->hour >= 15) {
        $default_stop_date->addDay()->setTime(8, 0, 0);
      }
    }

    // Ambil list analis dari VerificationActivity id = 2 (Pemeriksaan / Analitik)
    $analis_list = [];
    $verificationActivity = VerificationActivity::all()->keyBy('id')->toArray();
    if (isset($verificationActivity[2])) {
      $activity2 = (object) $verificationActivity[2];
      if ($laboratorium->kode_laboratorium == 'MBI') {
        $analis_list = array_filter(explode(', ', $activity2->mikro ?? ''));
      } elseif ($laboratorium->kode_laboratorium == 'KIM') {
        $analis_list = array_filter(explode(', ', $activity2->kimia ?? ''));
      } else {
        $analis_list = array_filter(explode(', ', $activity2->klnik ?? ''));
      }
    }

    // Cek BSRE_USE dari config
    $use_tte = config('app.bsre_use', false);

    return view('masterweb::module.admin.laboratorium.sample.verifikasi-pemeriksaan-analitik', compact(
      'samples',
      'permohonan_uji',
      'laboratorium',
      'idlabs',
      'default_analis',
      'default_start_date',
      'default_stop_date',
      'analis_list',
      'use_tte'
    ));
  }

  /**
   * Menyimpan data penerimaan sampel massal (bertingkat)
   */
  public function penerimaanSampelStore(Request $request, $id_samples, $id_permohonan_uji, $idlabs)
  {

    // Ambil semua sample dari permohonan uji yang sama untuk lab ini
    $samples = Sample::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_sample_method', function ($join) use ($idlabs) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->select('tb_samples.*')
      ->distinct()
      ->get();


    // Cek existing penerimaan untuk menentukan step mana yang sedang aktif
    $existing_penerimaan = [];
    foreach ($samples as $sample) {
      $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->first();
      if ($penerimaan) {
        $existing_penerimaan[$sample->id_samples] = $penerimaan;
      }
    }

    // Deteksi step mana yang sedang aktif
    $step_penerima_done = false;
    $step_koordinator_done = false;

    // Hitung jumlah sample yang sudah memiliki data penerima DAN pengawetan
    $samples_with_penerima = 0;
    $samples_with_pengawetan = 0;
    $total_samples = count($samples);

    foreach ($samples as $sample) {
      $penerimaan = isset($existing_penerimaan[$sample->id_samples]) ? $existing_penerimaan[$sample->id_samples] : null;

      // Cek data penerima
      if ($penerimaan && !empty($penerimaan->penerima_sampel) && !empty($penerimaan->penerima_tanggal)) {
        $samples_with_penerima++;
      }

      // Cek data pengawetan
      if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
        $samples_with_pengawetan++;
      }
    }

    // Step 1 selesai hanya jika semua sample sudah memiliki data penerima DAN semua sample sudah memiliki pengawetan
    $step_penerima_done = ($samples_with_penerima == $total_samples &&
                          $samples_with_pengawetan == $total_samples &&
                          $total_samples > 0);

    $first_penerimaan = count($existing_penerimaan) > 0 ? reset($existing_penerimaan) : null;
    if ($step_penerima_done && $first_penerimaan && !empty($first_penerimaan->disposisi_koordinator_kesmas) && !empty($first_penerimaan->disposisi_tanggal)) {
      $step_koordinator_done = true;
    }

    // Cek apakah step analis sudah selesai
    $step_analis_done = false;
    if ($step_koordinator_done && $first_penerimaan && !empty($first_penerimaan->disposisi_analis) && !empty($first_penerimaan->disposisi_analis_tanggal)) {
      $step_analis_done = true;
    }

     $last_sample = Sample::find($id_samples);

    // MODE SIMPAN SEMUA STEP: Jika save_all_steps = 1, simpan step 1, 2, dan 3 sekaligus
    if ($request->input('save_all_steps') == '1' || $request->input('current_step') == 'all') {
      // Validasi - field boleh kosong untuk memungkinkan penyimpanan sebagian
      $request->validate([
        'samples' => 'nullable|array',
        'samples.*.kelayakan' => 'nullable|in:1,0',
        'penerima_sampel' => 'nullable',
        'penerima_tanggal' => 'nullable',
        'disposisi_koordinator_kesmas' => 'nullable',
        'disposisi_tanggal' => 'nullable',
        'disposisi_analis' => 'nullable',
        'disposisi_analis_tanggal' => 'nullable',
      ]);

      $samples_data = $request->input('samples', []);
      $first_sample_id = null;

      // Ambil sample pertama yang sudah memiliki data penerimaan untuk di-copy ke sample baru
      $reference_penerimaan = null;
      foreach ($samples as $sample) {
        $existing_penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
        if ($existing_penerimaan && !empty($existing_penerimaan->penerima_sampel) && !empty($existing_penerimaan->penerima_tanggal)) {
          $reference_penerimaan = $existing_penerimaan;
          break;
        }
      }

      // Hanya proses jika ada data samples
      if (!empty($samples_data)) {
        foreach ($samples_data as $sample_id => $data) {
          if ($first_sample_id === null) {
            $first_sample_id = $sample_id;
          }

          $penerimaan = PenerimaanSample::where('sample_id', $sample_id)
            ->where('laboratorium_id', $idlabs)
            ->first();

          $is_new_sample = false;
          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample_id;
            $penerimaan->laboratorium_id = $idlabs;
            $is_new_sample = true;

            // Copy data dari sample referensi jika ada (untuk sample baru)
            if ($reference_penerimaan) {
              $penerimaan->penerima_sampel = $reference_penerimaan->penerima_sampel;
              $penerimaan->penerima_tanggal = $reference_penerimaan->penerima_tanggal;
              $penerimaan->penerima_signature = $reference_penerimaan->penerima_signature;
              $penerimaan->penerima_signature_type = $reference_penerimaan->penerima_signature_type;
              $penerimaan->disposisi_koordinator_kesmas = $reference_penerimaan->disposisi_koordinator_kesmas;
              $penerimaan->disposisi_tanggal = $reference_penerimaan->disposisi_tanggal;
              $penerimaan->disposisi_signature = $reference_penerimaan->disposisi_signature;
              $penerimaan->disposisi_signature_type = $reference_penerimaan->disposisi_signature_type;
              $penerimaan->disposisi_analis = $reference_penerimaan->disposisi_analis;
              $penerimaan->disposisi_analis_tanggal = $reference_penerimaan->disposisi_analis_tanggal;
              $penerimaan->disposisi_analis_signature = $reference_penerimaan->disposisi_analis_signature;
              $penerimaan->disposisi_analis_signature_type = $reference_penerimaan->disposisi_analis_signature_type;
            }
          }

          // Data kelayakan - selalu update jika ada data
          if (isset($data['kelayakan'])) {
            $penerimaan->kelayakan_tempat_kemasan = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';
            $penerimaan->kelayakan_berat_vol = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';

            // Data kondisi sample jika tidak layak
            if ($data['kelayakan'] == '0') {
              $kondisi_sample = [];
              if (isset($data['kondisi_tidak_diawetkan'])) {
                $kondisi_sample[] = 'tidak diawetkan di lapangan';
              }
              if (isset($data['kondisi_wadah_tidak_sesuai'])) {
                $kondisi_sample[] = 'wadah sampel tidak sesuai';
              }
              if (isset($data['kondisi_kadaluarsa'])) {
                $kondisi_sample[] = 'sampel kadaluarsa';
              }
              if (isset($data['kondisi_lainnya']) && !empty($data['kondisi_lainnya_text'])) {
                $kondisi_sample[] = 'lainnya: ' . $data['kondisi_lainnya_text'];
              }

              $penerimaan->kondisi_sample = !empty($kondisi_sample) ? implode('; ', $kondisi_sample) : null;
            } else {
              $penerimaan->kondisi_sample = null;
            }
          }

          // Data pengawetan - selalu update jika ada data
          if (isset($data['pengawetan_oleh'])) {
            $penerimaan->pengawetan_oleh = $data['pengawetan_oleh'];
          }

          $pengawetan_dengan = [];
          if (isset($data['pengawetan_pendinginan'])) {
            $pengawetan_dengan[] = 'Pendinginan';
          }
          if (isset($data['pengawetan_hno3'])) {
            $pengawetan_dengan[] = 'HNO3';
          }
          if (isset($data['pengawetan_h2so4'])) {
            $pengawetan_dengan[] = 'H2SO4';
          }
          if (isset($data['pengawetan_naoh'])) {
            $pengawetan_dengan[] = 'NaOH';
          }
          if (isset($data['pengawetan_lainnya']) && !empty($data['pengawetan_lainnya_text'])) {
            $pengawetan_dengan[] = 'lainnya: ' . $data['pengawetan_lainnya_text'];
          }
          $penerimaan->pengawetan_dengan = implode('; ', $pengawetan_dengan);

          $penerimaan->save();
        }
      }

      // Update SEMUA sample dengan data step 1 (penerima sampel)
      if ($request->filled('penerima_sampel') || $request->filled('penerima_tanggal')) {
        $penerima_sampel_new = $request->input('penerima_sampel');
        $penerima_tanggal_new = null;

        if ($request->filled('penerima_tanggal')) {
          try {
            $penerima_tanggal_new = Carbon::createFromFormat('d/m/Y H:i', $request->input('penerima_tanggal'))->format('Y-m-d H:i:s');
          } catch (\Exception $e) {
            // Jika format tanggal tidak valid, skip
          }
        }

        foreach ($samples as $sample) {
          $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
            ->where('laboratorium_id', $idlabs)
            ->first();

          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
          }

          if ($penerima_sampel_new) {
            $penerimaan->penerima_sampel = $penerima_sampel_new;
          }
          if ($penerima_tanggal_new) {
            $penerimaan->penerima_tanggal = $penerima_tanggal_new;
          }

          if (!empty($request->input('penerima_signature'))) {
            $penerimaan->penerima_signature = $request->input('penerima_signature');
            $penerimaan->penerima_signature_type = $request->input('penerima_signature_type', 'canvas');
          }

          $penerimaan->save();
        }
      }

      // Update SEMUA sample dengan data step 2 (disposisi koordinator)
      if ($request->filled('disposisi_koordinator_kesmas') || $request->filled('disposisi_tanggal')) {
        foreach ($samples as $sample) {
          $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
            ->where('laboratorium_id', $idlabs)
            ->first();

          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
          }

          if ($request->filled('disposisi_koordinator_kesmas')) {
            $penerimaan->disposisi_koordinator_kesmas = $request->input('disposisi_koordinator_kesmas');
          }
          if ($request->filled('disposisi_tanggal')) {
            try {
              $disposisi_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_tanggal'))->format('Y-m-d H:i:s');
              $penerimaan->disposisi_tanggal = $disposisi_tanggal;
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip
            }
          }

          if (!empty($request->input('disposisi_signature'))) {
            $penerimaan->disposisi_signature = $request->input('disposisi_signature');
            $penerimaan->disposisi_signature_type = $request->input('disposisi_signature_type', 'canvas');
          }

          $penerimaan->save();
        }
      }

      // Update SEMUA sample dengan data step 3 (disposisi analis)
      if ($request->filled('disposisi_analis') || $request->filled('disposisi_analis_tanggal')) {
        foreach ($samples as $sample) {
          $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
            ->where('laboratorium_id', $idlabs)
            ->first();

          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
          }

          if ($request->filled('disposisi_analis')) {
            $penerimaan->disposisi_analis = $request->input('disposisi_analis');
          }
          if ($request->filled('disposisi_analis_tanggal')) {
            try {
              $disposisi_analis_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_analis_tanggal'))->format('Y-m-d H:i:s');
              $penerimaan->disposisi_analis_tanggal = $disposisi_analis_tanggal;
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip
            }
          }

          if (!empty($request->input('disposisi_analis_signature'))) {
            $penerimaan->disposisi_analis_signature = $request->input('disposisi_analis_signature');
            $penerimaan->disposisi_analis_signature_type = $request->input('disposisi_analis_signature_type', 'canvas');
          }

          $penerimaan->save();
        }
      }

      // Update atau buat VerificationActivitySample untuk step Penerimaan Sampel (id_verification_activity = 7) dengan is_done = 1
      // Gunakan data dari form verifikasi jika ada, jika tidak gunakan data dari penerimaan
      if ($request->input('submit_verifikasi') == '1' &&
          $request->filled('verifikasi_start_date') &&
          $request->filled('verifikasi_stop_date') &&
          $request->filled('verifikasi_nama_petugas')) {
        // Gunakan data dari form verifikasi
        try {
          $verifikasi_start_date = Carbon::createFromFormat('d/m/Y H:i', $request->input('verifikasi_start_date'))->format('Y-m-d H:i:s');
          $verifikasi_stop_date = Carbon::createFromFormat('d/m/Y H:i', $request->input('verifikasi_stop_date'))->format('Y-m-d H:i:s');
          $verifikasi_nama_petugas = $request->input('verifikasi_nama_petugas');

          foreach ($samples as $sample) {
            $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
              ->where('id_verification_activity', 7)
              ->first();

            if (!$verificationActivitySample) {
              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_sample = $sample->id_samples;
              $verificationActivitySample->id_verification_activity = 7;
            }

            $verificationActivitySample->start_date = $verifikasi_start_date;
            $verificationActivitySample->stop_date = $verifikasi_stop_date;
            $verificationActivitySample->nama_petugas = $verifikasi_nama_petugas;
            $verificationActivitySample->is_done = 1;
            $verificationActivitySample->save();
          }
        } catch (\Exception $e) {
          // Jika format tanggal tidak valid, gunakan fallback ke data penerimaan
        }
      } else {
        // Fallback: gunakan data dari penerimaan (seperti sebelumnya)
        foreach ($samples as $sample) {
          $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
            ->where('laboratorium_id', $idlabs)
            ->first();

          if ($penerimaan && $penerimaan->penerima_tanggal && $penerimaan->disposisi_analis_tanggal) {
            $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
              ->where('id_verification_activity', 7)
              ->first();

            if (!$verificationActivitySample) {
              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_sample = $sample->id_samples;
              $verificationActivitySample->id_verification_activity = 7;
            }

            $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
            $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
            $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
            $verificationActivitySample->is_done = 1;
            $verificationActivitySample->save();
          }
        }
      }

      // Redirect ke halaman verifikasi Pemeriksaan / Analitik setelah semua step disimpan
      $statusMessage = 'Semua step (1-3) berhasil disimpan sekaligus!';
      if ($request->input('submit_verifikasi') == '1' &&
          $request->filled('verifikasi_start_date') &&
          $request->filled('verifikasi_stop_date') &&
          $request->filled('verifikasi_nama_petugas')) {
        $statusMessage = 'Semua step (1-3 + Verifikasi) berhasil disimpan sekaligus!';
      }

      return redirect()->route('elits-samples.verifikasi-pemeriksaan-analitik', [$last_sample->id_samples, $id_permohonan_uji, $idlabs])
        ->with('status', $statusMessage);
    }


    // STEP 1: Simpan data kelayakan dan penerima sampel
    if (!$step_penerima_done) {
      // Validasi - field boleh kosong untuk memungkinkan penyimpanan sebagian
      $request->validate([
        'samples' => 'nullable|array',
        'samples.*.kelayakan' => 'nullable|in:1,0',
        'penerima_sampel' => 'nullable',
        'penerima_tanggal' => 'nullable',
      ]);

      $samples_data = $request->input('samples', []);
      $first_sample_id = null;

      // Ambil sample pertama yang sudah memiliki data penerimaan untuk di-copy ke sample baru
      $reference_penerimaan = null;
      foreach ($samples as $sample) {
        $existing_penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
        if ($existing_penerimaan && !empty($existing_penerimaan->penerima_sampel) && !empty($existing_penerimaan->penerima_tanggal)) {
          $reference_penerimaan = $existing_penerimaan;
          break; // Gunakan sample pertama yang sudah lengkap sebagai referensi
        }
      }

      // Hanya proses jika ada data samples
      if (!empty($samples_data)) {
        foreach ($samples_data as $sample_id => $data) {
          if ($first_sample_id === null) {
            $first_sample_id = $sample_id;
          }

          $penerimaan = PenerimaanSample::where('sample_id', $sample_id)
            ->where('laboratorium_id', $idlabs)
            ->first();

          $is_new_sample = false;
          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample_id;
            $penerimaan->laboratorium_id = $idlabs;
            $is_new_sample = true;

            // Copy data dari sample referensi jika ada (untuk sample baru)
            if ($reference_penerimaan) {
              $penerimaan->penerima_sampel = $reference_penerimaan->penerima_sampel;
              $penerimaan->penerima_tanggal = $reference_penerimaan->penerima_tanggal;
              $penerimaan->penerima_signature = $reference_penerimaan->penerima_signature;
              $penerimaan->penerima_signature_type = $reference_penerimaan->penerima_signature_type;
              $penerimaan->disposisi_koordinator_kesmas = $reference_penerimaan->disposisi_koordinator_kesmas;
              $penerimaan->disposisi_tanggal = $reference_penerimaan->disposisi_tanggal;
              $penerimaan->disposisi_signature = $reference_penerimaan->disposisi_signature;
              $penerimaan->disposisi_signature_type = $reference_penerimaan->disposisi_signature_type;
              $penerimaan->disposisi_analis = $reference_penerimaan->disposisi_analis;
              $penerimaan->disposisi_analis_tanggal = $reference_penerimaan->disposisi_analis_tanggal;
              $penerimaan->disposisi_analis_signature = $reference_penerimaan->disposisi_analis_signature;
              $penerimaan->disposisi_analis_signature_type = $reference_penerimaan->disposisi_analis_signature_type;
            }
          }

          // Data kelayakan - selalu update jika ada data (untuk sample baru atau update)
          if (isset($data['kelayakan'])) {
            $penerimaan->kelayakan_tempat_kemasan = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';
            $penerimaan->kelayakan_berat_vol = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';

            // Data kondisi sample jika tidak layak
            if ($data['kelayakan'] == '0') {
              $kondisi_sample = [];
              if (isset($data['kondisi_tidak_diawetkan'])) {
                $kondisi_sample[] = 'tidak diawetkan di lapangan';
              }
              if (isset($data['kondisi_wadah_tidak_sesuai'])) {
                $kondisi_sample[] = 'wadah sampel tidak sesuai';
              }
              if (isset($data['kondisi_kadaluarsa'])) {
                $kondisi_sample[] = 'sampel kadaluarsa';
              }
              if (isset($data['kondisi_lainnya']) && !empty($data['kondisi_lainnya_text'])) {
                $kondisi_sample[] = 'lainnya: ' . $data['kondisi_lainnya_text'];
              }

              $penerimaan->kondisi_sample = !empty($kondisi_sample) ? implode('; ', $kondisi_sample) : null;
            } else {
              $penerimaan->kondisi_sample = null;
            }
          }

          // Data pengawetan - selalu update jika ada data
          if (isset($data['pengawetan_oleh'])) {
            $penerimaan->pengawetan_oleh = $data['pengawetan_oleh'];
          }

          $pengawetan_dengan = [];
          if (isset($data['pengawetan_pendinginan'])) {
            $pengawetan_dengan[] = 'Pendinginan';
          }
          if (isset($data['pengawetan_hno3'])) {
            $pengawetan_dengan[] = 'HNO3';
          }
          if (isset($data['pengawetan_h2so4'])) {
            $pengawetan_dengan[] = 'H2SO4';
          }
          if (isset($data['pengawetan_naoh'])) {
            $pengawetan_dengan[] = 'NaOH';
          }
          if (isset($data['pengawetan_lainnya']) && !empty($data['pengawetan_lainnya_text'])) {
            $pengawetan_dengan[] = 'lainnya: ' . $data['pengawetan_lainnya_text'];
          }
          $penerimaan->pengawetan_dengan = implode('; ', $pengawetan_dengan);

          $penerimaan->save();
        }
      }

      // Update SEMUA sample (termasuk yang sudah memiliki data) dengan nama penerima dan tanggal yang dipilih
      // Ini memastikan bahwa ketika user memilih nama penerima di Step 1, semua sample akan diupdate/diganti
      if ($request->filled('penerima_sampel') || $request->filled('penerima_tanggal')) {
        $penerima_sampel_new = $request->input('penerima_sampel');
        $penerima_tanggal_new = null;

        if ($request->filled('penerima_tanggal')) {
          try {
            $penerima_tanggal_new = Carbon::createFromFormat('d/m/Y H:i', $request->input('penerima_tanggal'))->format('Y-m-d H:i:s');
          } catch (\Exception $e) {
            // Jika format tanggal tidak valid, skip
          }
        }

        // Update SEMUA sample (termasuk yang sudah memiliki data penerima)
        foreach ($samples as $sample) {
          $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
            ->where('laboratorium_id', $idlabs)
            ->first();

          // Jika belum ada record penerimaan, buat baru
          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
          }

          // Update SEMUA sample dengan data baru (mengganti data yang sudah ada)
          if ($penerima_sampel_new) {
            $penerimaan->penerima_sampel = $penerima_sampel_new;
          }
          if ($penerima_tanggal_new) {
            $penerimaan->penerima_tanggal = $penerima_tanggal_new;
          }

          // Update signature jika ada
          if (!empty($request->input('penerima_signature'))) {
            $penerimaan->penerima_signature = $request->input('penerima_signature');
            $penerimaan->penerima_signature_type = $request->input('penerima_signature_type', 'canvas');
          }

          $penerimaan->save();
        }
      }

      // Redirect ke halaman verifikasi sample pertama setelah semua step selesai
      // Atau redirect ke form jika masih ada step yang belum selesai
      if ($step_koordinator_done && $step_analis_done) {
        return redirect()->route('elits-samples.verification-2', [$last_sample->id_samples, $idlabs])
          ->with('status', 'Step 1: Data Penerima Sampel berhasil disimpan!');
      } else {
        return redirect()->route('elits-samples.penerimaan-sampel-form', [$last_sample->id_samples,$id_permohonan_uji, $idlabs])
          ->with('status', 'Step 1: Data Penerima Sampel berhasil disimpan! Silakan lanjut ke Step 2.');
      }
    }
    // STEP 2: Simpan data disposisi ke koordinator kesmas
    elseif (!$step_koordinator_done) {
      // Validasi: Jika belum ada pengawetan, maka step 1 harus diisi dulu
      $has_pengawetan = false;
      $samples_data = $request->input('samples', []);

      // Cek pengawetan dari data yang sudah tersimpan
      foreach ($samples as $sample) {
        $penerimaan = isset($existing_penerimaan[$sample->id_samples]) ? $existing_penerimaan[$sample->id_samples] : null;
        if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
          $has_pengawetan = true;
          break;
        }
      }

      // Cek pengawetan dari data form yang baru diinput
      if (!$has_pengawetan && !empty($samples_data)) {
        foreach ($samples_data as $sample_id => $data) {
          if (isset($data['pengawetan_oleh']) && !empty($data['pengawetan_oleh'])) {
            $has_pengawetan = true;
            break;
          }
          if (isset($data['pengawetan_pendinginan']) ||
              isset($data['pengawetan_hno3']) ||
              isset($data['pengawetan_h2so4']) ||
              isset($data['pengawetan_naoh']) ||
              (isset($data['pengawetan_lainnya']) && !empty($data['pengawetan_lainnya_text']))) {
            $has_pengawetan = true;
            break;
          }
        }
      }

      // Jika belum ada pengawetan, pastikan step 1 sudah diisi
      if (!$has_pengawetan && !$step_penerima_done) {
        return redirect()->route('elits-samples.penerimaan-sampel-form', [$last_sample->id_samples,$id_permohonan_uji, $idlabs])
          ->with('error', 'Apabila belum ada pengawetan, maka Step 1 (Penerima Sampel) harus diisi terlebih dahulu!');
      }

      // Validasi - field boleh kosong untuk memungkinkan penyimpanan sebagian
      $request->validate([
        'disposisi_koordinator_kesmas' => 'nullable',
        'disposisi_tanggal' => 'nullable',
      ]);

      // Ambil sample pertama yang sudah memiliki disposisi koordinator untuk di-copy ke sample baru
      $reference_penerimaan = null;
      foreach ($samples as $sample) {
        $existing_penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
        if ($existing_penerimaan && !empty($existing_penerimaan->disposisi_koordinator_kesmas) && !empty($existing_penerimaan->disposisi_tanggal)) {
          $reference_penerimaan = $existing_penerimaan;
          break; // Gunakan sample pertama yang sudah lengkap sebagai referensi
        }
      }

      foreach ($samples as $sample) {
        $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();

        if (!$penerimaan) {
          // Jika sample baru, copy dari referensi
          if ($reference_penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
            // Copy data dari referensi
            $penerimaan->penerima_sampel = $reference_penerimaan->penerima_sampel;
            $penerimaan->penerima_tanggal = $reference_penerimaan->penerima_tanggal;
            $penerimaan->penerima_signature = $reference_penerimaan->penerima_signature;
            $penerimaan->penerima_signature_type = $reference_penerimaan->penerima_signature_type;
            $penerimaan->disposisi_koordinator_kesmas = $reference_penerimaan->disposisi_koordinator_kesmas;
            $penerimaan->disposisi_tanggal = $reference_penerimaan->disposisi_tanggal;
            $penerimaan->disposisi_signature = $reference_penerimaan->disposisi_signature;
            $penerimaan->disposisi_signature_type = $reference_penerimaan->disposisi_signature_type;
            $penerimaan->disposisi_analis = $reference_penerimaan->disposisi_analis;
            $penerimaan->disposisi_analis_tanggal = $reference_penerimaan->disposisi_analis_tanggal;
            $penerimaan->disposisi_analis_signature = $reference_penerimaan->disposisi_analis_signature;
            $penerimaan->disposisi_analis_signature_type = $reference_penerimaan->disposisi_analis_signature_type;
            // Copy data lainnya
            $penerimaan->kelayakan_tempat_kemasan = $reference_penerimaan->kelayakan_tempat_kemasan;
            $penerimaan->kelayakan_berat_vol = $reference_penerimaan->kelayakan_berat_vol;
            $penerimaan->kondisi_sample = $reference_penerimaan->kondisi_sample;
            $penerimaan->pengawetan_oleh = $reference_penerimaan->pengawetan_oleh;
            $penerimaan->pengawetan_dengan = $reference_penerimaan->pengawetan_dengan;
            $penerimaan->save();
            continue; // Skip update karena sudah di-copy
          }
        }

        if ($penerimaan) {
          // Hanya update sample yang belum memiliki disposisi koordinator (tidak mempengaruhi yang sudah selesai)
          $needs_update = empty($penerimaan->disposisi_koordinator_kesmas) || empty($penerimaan->disposisi_tanggal);

          if ($needs_update) {
            // Data disposisi koordinator - hanya update jika ada data
            if ($request->filled('disposisi_koordinator_kesmas')) {
              $penerimaan->disposisi_koordinator_kesmas = $request->input('disposisi_koordinator_kesmas');
            }
            if ($request->filled('disposisi_tanggal')) {
              try {
                $disposisi_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_tanggal'))->format('Y-m-d H:i:s');
                $penerimaan->disposisi_tanggal = $disposisi_tanggal;
              } catch (\Exception $e) {
                // Jika format tanggal tidak valid, skip
              }
            }

            if (!empty($request->input('disposisi_signature'))) {
              $penerimaan->disposisi_signature = $request->input('disposisi_signature');
              $penerimaan->disposisi_signature_type = $request->input('disposisi_signature_type', 'canvas');
            }

            $penerimaan->save();
          }
        }
      }





      // Redirect ke halaman verifikasi sample pertama setelah semua step selesai
      // Atau redirect ke form jika masih ada step yang belum selesai

      if ($step_analis_done) {
        return redirect()->route('elits-samples.verification-2', [ $last_sample->id_samples, $idlabs])
          ->with('status', 'Step 2: Data Disposisi ke Koordinator Kesmas berhasil disimpan!');
      } else {
        return redirect()->route('elits-samples.penerimaan-sampel-form', [$last_sample->id_samples,$id_permohonan_uji, $idlabs])
          ->with('status', 'Step 2: Data Disposisi ke Koordinator Kesmas berhasil disimpan! Silakan lanjut ke Step 3.');
      }
    }

    // STEP 3: Simpan data disposisi ke analis
    elseif (!$step_analis_done) {
      // Validasi: Jika belum ada pengawetan, maka step 1 harus diisi dulu
      $has_pengawetan = false;
      $samples_data = $request->input('samples', []);

      // Cek pengawetan dari data yang sudah tersimpan
      foreach ($samples as $sample) {
        $penerimaan = isset($existing_penerimaan[$sample->id_samples]) ? $existing_penerimaan[$sample->id_samples] : null;
        if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
          $has_pengawetan = true;
          break;
        }
      }

      // Cek pengawetan dari data form yang baru diinput
      if (!$has_pengawetan && !empty($samples_data)) {
        foreach ($samples_data as $sample_id => $data) {
          if (isset($data['pengawetan_oleh']) && !empty($data['pengawetan_oleh'])) {
            $has_pengawetan = true;
            break;
          }
          if (isset($data['pengawetan_pendinginan']) ||
              isset($data['pengawetan_hno3']) ||
              isset($data['pengawetan_h2so4']) ||
              isset($data['pengawetan_naoh']) ||
              (isset($data['pengawetan_lainnya']) && !empty($data['pengawetan_lainnya_text']))) {
            $has_pengawetan = true;
            break;
          }
        }
      }

      // Jika belum ada pengawetan, pastikan step 1 sudah diisi
      if (!$has_pengawetan && !$step_penerima_done) {
        return redirect()->route('elits-samples.penerimaan-sampel-form', [$sample->id_samples, $id_permohonan_uji, $idlabs])
          ->with('error', 'Apabila belum ada pengawetan, maka Step 1 (Penerima Sampel) harus diisi terlebih dahulu!');
      }

      // Validasi - field boleh kosong untuk memungkinkan penyimpanan sebagian
      $request->validate([
        'disposisi_analis' => 'nullable',
        'disposisi_analis_tanggal' => 'nullable',
      ]);

      // Ambil sample pertama yang sudah memiliki disposisi analis untuk di-copy ke sample baru
      $reference_penerimaan = null;
      foreach ($samples as $sample) {
        $existing_penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
        if ($existing_penerimaan && !empty($existing_penerimaan->disposisi_analis) && !empty($existing_penerimaan->disposisi_analis_tanggal)) {
          $reference_penerimaan = $existing_penerimaan;
          break; // Gunakan sample pertama yang sudah lengkap sebagai referensi
        }
      }

      $first_sample_id = null;

      foreach ($samples as $sample) {
        if ($first_sample_id === null) {
          $first_sample_id = $sample->id_samples;
        }

        $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();

        if (!$penerimaan) {
          // Jika sample baru, copy dari referensi
          if ($reference_penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample->id_samples;
            $penerimaan->laboratorium_id = $idlabs;
            // Copy semua data dari referensi
            $penerimaan->penerima_sampel = $reference_penerimaan->penerima_sampel;
            $penerimaan->penerima_tanggal = $reference_penerimaan->penerima_tanggal;
            $penerimaan->penerima_signature = $reference_penerimaan->penerima_signature;
            $penerimaan->penerima_signature_type = $reference_penerimaan->penerima_signature_type;
            $penerimaan->disposisi_koordinator_kesmas = $reference_penerimaan->disposisi_koordinator_kesmas;
            $penerimaan->disposisi_tanggal = $reference_penerimaan->disposisi_tanggal;
            $penerimaan->disposisi_signature = $reference_penerimaan->disposisi_signature;
            $penerimaan->disposisi_signature_type = $reference_penerimaan->disposisi_signature_type;
            $penerimaan->disposisi_analis = $reference_penerimaan->disposisi_analis;
            $penerimaan->disposisi_analis_tanggal = $reference_penerimaan->disposisi_analis_tanggal;
            $penerimaan->disposisi_analis_signature = $reference_penerimaan->disposisi_analis_signature;
            $penerimaan->disposisi_analis_signature_type = $reference_penerimaan->disposisi_analis_signature_type;
            // Copy data lainnya
            $penerimaan->kelayakan_tempat_kemasan = $reference_penerimaan->kelayakan_tempat_kemasan;
            $penerimaan->kelayakan_berat_vol = $reference_penerimaan->kelayakan_berat_vol;
            $penerimaan->kondisi_sample = $reference_penerimaan->kondisi_sample;
            $penerimaan->pengawetan_oleh = $reference_penerimaan->pengawetan_oleh;
            $penerimaan->pengawetan_dengan = $reference_penerimaan->pengawetan_dengan;
            $penerimaan->save();

            // Buat VerificationActivitySample untuk sample baru
            $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
              ->where('id_verification_activity', 7)
              ->first();

            if (!$verificationActivitySample) {
              $verificationActivitySample = new VerificationActivitySample();
              $verificationActivitySample->id = Uuid::uuid4()->toString();
              $verificationActivitySample->id_sample = $sample->id_samples;
              $verificationActivitySample->id_verification_activity = 7;
            }

            $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
            $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
            $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
            $verificationActivitySample->is_done = 1;
            $verificationActivitySample->save();

            continue; // Skip update karena sudah di-copy
          }
        }

        if ($penerimaan) {
          // Hanya update sample yang belum memiliki disposisi analis (tidak mempengaruhi yang sudah selesai)
          $needs_update = empty($penerimaan->disposisi_analis) || empty($penerimaan->disposisi_analis_tanggal);

          if ($needs_update) {
            // Data disposisi analis - hanya update jika ada data
            if ($request->filled('disposisi_analis')) {
              $penerimaan->disposisi_analis = $request->input('disposisi_analis');
            }
            if ($request->filled('disposisi_analis_tanggal')) {
              try {
                $disposisi_analis_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_analis_tanggal'))->format('Y-m-d H:i:s');
                $penerimaan->disposisi_analis_tanggal = $disposisi_analis_tanggal;
              } catch (\Exception $e) {
                // Jika format tanggal tidak valid, skip
              }
            }

            if (!empty($request->input('disposisi_analis_signature'))) {
              $penerimaan->disposisi_analis_signature = $request->input('disposisi_analis_signature');
              $penerimaan->disposisi_analis_signature_type = $request->input('disposisi_analis_signature_type', 'canvas');
            }

            $penerimaan->save();
          }

          // Update atau buat VerificationActivitySample untuk step Penerimaan Sampel (id = 7)
          $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
            ->where('id_verification_activity', 7)
            ->first();

          if (!$verificationActivitySample) {
            $verificationActivitySample = new VerificationActivitySample();
            $verificationActivitySample->id = Uuid::uuid4()->toString();
            $verificationActivitySample->id_sample = $sample->id_samples;
            $verificationActivitySample->id_verification_activity = 7;
          }

          // Set tanggal dan petugas dari data penerimaan sample
          // Start date = tanggal penerimaan sample
          // Stop date = tanggal disposisi ke analis
          // Nama petugas = nama penerima sample
          $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
          $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
          $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
          $verificationActivitySample->is_done = 1;
          $verificationActivitySample->save();
        }
      }

      $verificationActivitySample = VerificationActivitySample::where('id_sample',$last_sample->id_samples)
      ->where('id_verification_activity', 7)
      ->first();
      if ($verificationActivitySample && $penerimaan->penerima_tanggal && $penerimaan->disposisi_analis_tanggal) {
        $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
        $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
        $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
        $verificationActivitySample->is_done = 1;
        $verificationActivitySample->save();
      }else{
        if (!isset($verificationActivitySample)) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $last_sample->id_samples;
          $verificationActivitySample->id_verification_activity = 7;
          $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
          $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
          $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
          $verificationActivitySample->is_done = 1;
          $verificationActivitySample->save();
        }
      }

      // Redirect ke halaman verifikasi sample pertama setelah semua step selesai
      return redirect()->route('elits-samples.verification-2', [$last_sample->id_samples, $idlabs])
        ->with('status', 'Step 3: Data Disposisi ke Analis berhasil disimpan! Semua step Penerimaan Sampel selesai!');
    }

    // STEP 4: Update semua data jika semua step sudah selesai (untuk edit)
    else {
      // Validasi - field boleh kosong untuk memungkinkan penyimpanan sebagian
      $request->validate([
        'samples' => 'nullable|array',
        'samples.*.kelayakan' => 'nullable|in:1,0',
        'penerima_sampel' => 'nullable',
        'penerima_tanggal' => 'nullable',
        'disposisi_koordinator_kesmas' => 'nullable',
        'disposisi_tanggal' => 'nullable',
        'disposisi_analis' => 'nullable',
        'disposisi_analis_tanggal' => 'nullable',
      ]);

      $samples_data = $request->input('samples', []);
      $first_sample_id = null;
      $has_new_sample = false; // Flag untuk menandai apakah ada sample baru yang ditambahkan

      // Update data samples jika ada
      if (!empty($samples_data)) {
        foreach ($samples_data as $sample_id => $data) {
          if ($first_sample_id === null) {
            $first_sample_id = $sample_id;
          }

          $penerimaan = PenerimaanSample::where('sample_id', $sample_id)
            ->where('laboratorium_id', $idlabs)
            ->first();

          $is_new = false;
          if (!$penerimaan) {
            $penerimaan = new PenerimaanSample();
            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $penerimaan->sample_id = $sample_id;
            $penerimaan->laboratorium_id = $idlabs;
            $is_new = true;
            $has_new_sample = true;
          }

          // Data kelayakan - hanya update jika ada data
          if (isset($data['kelayakan'])) {
            $penerimaan->kelayakan_tempat_kemasan = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';
            $penerimaan->kelayakan_berat_vol = $data['kelayakan'] == '1' ? 'layak' : 'tidak layak';

            // Data kondisi sample jika tidak layak
            if ($data['kelayakan'] == '0') {
              $kondisi_sample = [];
              if (isset($data['kondisi_tidak_diawetkan'])) {
                $kondisi_sample[] = 'tidak diawetkan di lapangan';
              }
              if (isset($data['kondisi_wadah_tidak_sesuai'])) {
                $kondisi_sample[] = 'wadah sampel tidak sesuai';
              }
              if (isset($data['kondisi_kadaluarsa'])) {
                $kondisi_sample[] = 'sampel kadaluarsa';
              }
              if (isset($data['kondisi_lainnya']) && !empty($data['kondisi_lainnya_text'])) {
                $kondisi_sample[] = 'lainnya: ' . $data['kondisi_lainnya_text'];
              }

              $penerimaan->kondisi_sample = !empty($kondisi_sample) ? implode('; ', $kondisi_sample) : null;
            } else {
              $penerimaan->kondisi_sample = null;
            }
          }

          // Data pengawetan
          $penerimaan->pengawetan_oleh = $data['pengawetan_oleh'] ?? null;

          $pengawetan_dengan = [];
          if (isset($data['pengawetan_pendinginan'])) {
            $pengawetan_dengan[] = 'Pendinginan';
          }
          if (isset($data['pengawetan_hno3'])) {
            $pengawetan_dengan[] = 'HNO3';
          }
          if (isset($data['pengawetan_h2so4'])) {
            $pengawetan_dengan[] = 'H2SO4';
          }
          if (isset($data['pengawetan_naoh'])) {
            $pengawetan_dengan[] = 'NaOH';
          }
          if (isset($data['pengawetan_lainnya']) && !empty($data['pengawetan_lainnya_text'])) {
            $pengawetan_dengan[] = 'lainnya: ' . $data['pengawetan_lainnya_text'];
          }
          $penerimaan->pengawetan_dengan = implode('; ', $pengawetan_dengan);

          $penerimaan->save();
        }
      }

      // Update data penerima sampel untuk semua samples
      foreach ($samples as $sample) {
        if ($first_sample_id === null) {
          $first_sample_id = $sample->id_samples;
        }

        $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();

        if ($penerimaan) {
          // Data penerima sampel - hanya update jika ada data
          if ($request->filled('penerima_sampel')) {
            $penerimaan->penerima_sampel = $request->input('penerima_sampel');
          }
          if ($request->filled('penerima_tanggal')) {
            try {
              $penerima_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('penerima_tanggal'))->format('Y-m-d H:i:s');
              $penerimaan->penerima_tanggal = $penerima_tanggal;
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip
            }
          }

          if (!empty($request->input('penerima_signature'))) {
            $penerimaan->penerima_signature = $request->input('penerima_signature');
            $penerimaan->penerima_signature_type = $request->input('penerima_signature_type', 'canvas');
          }

          // Data disposisi koordinator - hanya update jika ada data
          if ($request->filled('disposisi_koordinator_kesmas')) {
            $penerimaan->disposisi_koordinator_kesmas = $request->input('disposisi_koordinator_kesmas');
          }
          if ($request->filled('disposisi_tanggal')) {
            try {
              $disposisi_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_tanggal'))->format('Y-m-d H:i:s');
              $penerimaan->disposisi_tanggal = $disposisi_tanggal;
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip
            }
          }

          if (!empty($request->input('disposisi_signature'))) {
            $penerimaan->disposisi_signature = $request->input('disposisi_signature');
            $penerimaan->disposisi_signature_type = $request->input('disposisi_signature_type', 'canvas');
          }

          // Data disposisi analis - hanya update jika ada data
          if ($request->filled('disposisi_analis')) {
            $penerimaan->disposisi_analis = $request->input('disposisi_analis');
          }
          if ($request->filled('disposisi_analis_tanggal')) {
            try {
              $disposisi_analis_tanggal = Carbon::createFromFormat('d/m/Y H:i', $request->input('disposisi_analis_tanggal'))->format('Y-m-d H:i:s');
              $penerimaan->disposisi_analis_tanggal = $disposisi_analis_tanggal;
            } catch (\Exception $e) {
              // Jika format tanggal tidak valid, skip
            }
          }

          if (!empty($request->input('disposisi_analis_signature'))) {
            $penerimaan->disposisi_analis_signature = $request->input('disposisi_analis_signature');
            $penerimaan->disposisi_analis_signature_type = $request->input('disposisi_analis_signature_type', 'canvas');
          }

          $penerimaan->save();

          // Update VerificationActivitySample jika ada
          $verificationActivitySample = VerificationActivitySample::where('id_sample', $sample->id_samples)
            ->where('id_verification_activity', 7)
            ->first();

          if ($verificationActivitySample && $penerimaan->penerima_tanggal && $penerimaan->disposisi_analis_tanggal) {
            $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
            $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
            $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
            $verificationActivitySample->is_done = 1;
            $verificationActivitySample->save();
          }
        }
      }




      $verificationActivitySample = VerificationActivitySample::where('id_sample', $last_sample->id_samples)
      ->where('id_verification_activity', 7)
      ->first();
      if ($verificationActivitySample && $penerimaan->penerima_tanggal && $penerimaan->disposisi_analis_tanggal) {
        $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
        $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
        $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
        $verificationActivitySample->is_done = 1;
        $verificationActivitySample->save();
      }else{
        if (!isset($verificationActivitySample)) {
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_sample = $last_sample->id_samples;
          $verificationActivitySample->id_verification_activity = 7;
          $verificationActivitySample->start_date = $penerimaan->penerima_tanggal;
          $verificationActivitySample->stop_date = $penerimaan->disposisi_analis_tanggal;
          $verificationActivitySample->nama_petugas = $penerimaan->penerima_sampel;
          $verificationActivitySample->is_done = 1;
          $verificationActivitySample->save();
        }
      }


      // Redirect berdasarkan apakah ada sample baru atau tidak
      // Jika ada sample baru, redirect ke form penerimaan sampel
      // Jika tidak ada sample baru (hanya update), redirect ke verification
      if ($has_new_sample && $first_sample_id) {
        return redirect()->route('elits-samples.penerimaan-sampel-form', [$last_sample->id_samples, $id_permohonan_uji, $idlabs])
          ->with('status', 'Data berhasil diupdate! Silakan lengkapi penerimaan sampel untuk sample baru.');
      } else {

        // Redirect ke verification sample pertama jika tidak ada sample baru
        $first_sample = $samples->first();
        if ($first_sample) {

          return redirect()->route('elits-samples.verification-2', [$last_sample->id_samples, $idlabs])
            ->with('status', 'Data berhasil diupdate!');
        } else {
          return redirect()->back()
            ->with('status', 'Data berhasil diupdate!');
        }
      }
    }
  }

  /**
   * Print formulir pengamanan sampel
   */
  public function printFormulirPengamanan($id_permohonan_uji, $idlabs)
  {
    Carbon::setLocale('id');

    // Ambil data permohonan uji
    $permohonan_uji = PermohonanUji::find($id_permohonan_uji);

    // Ambil data laboratorium
    $laboratorium = Laboratorium::find($idlabs);

    // Ambil semua sample dari permohonan uji yang sama untuk lab ini
    $samples = Sample::where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
      ->whereNull('tb_samples.deleted_at')
      ->join('tb_sample_method', function ($join) use ($idlabs) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->where('tb_sample_method.laboratorium_id', '=', $idlabs)
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->join('ms_sample_type', function ($join) {
        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
          ->whereNull('ms_sample_type.deleted_at');
      })
      ->select('tb_samples.id_samples', 'tb_samples.codesample_samples', 'tb_samples.permohonan_uji_id', 'tb_samples.typesample_samples', 'ms_sample_type.name_sample_type')
      ->distinct()
      ->get();

    // Ambil data penerimaan sample (ambil dari sample pertama yang memiliki lab sesuai)
    // Karena TTD disimpan per sample per lab, kita ambil dari sample pertama dengan lab yang sesuai
    $penerimaan_sample = null;
    if ($samples->count() > 0) {
      // Cari sample pertama yang memiliki penerimaan untuk lab ini
      foreach ($samples as $sample) {
        $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
        if ($penerimaan) {
          $penerimaan_sample = $penerimaan;
          break;
        }
      }
      // Jika tidak ditemukan, coba ambil dari sample pertama (untuk backward compatibility)
      if (!$penerimaan_sample && $samples->count() > 0) {
        $penerimaan_sample = PenerimaanSample::where('sample_id', $samples->first()->id_samples)
          ->where('laboratorium_id', $idlabs)
          ->first();
      }
    }

    // Ambil data verifikasi koordinator teknik (id = 4) - hanya jika is_done = 1
    $verifikasi_koordinator = null;
    if ($samples->count() > 0) {
      $verifikasi_koordinator = VerificationActivitySample::where('id_sample', $samples->first()->id_samples)
        ->where('id_verification_activity', 4)
        ->where('is_done', 1)
        ->first();
    }

    // Ambil data analis (id = 2) - ambil semua (tidak hanya is_done = 1)
    $verifikasi_analis = null;
    if ($samples->count() > 0) {
      $verifikasi_analis = VerificationActivitySample::where('id_sample', $samples->first()->id_samples)
        ->where('id_verification_activity', 2)
        ->first();
    }

    // Ambil data input output selesai (id = 3)
    $input_output_selesai = null;
    if ($samples->count() > 0) {
      $input_output_selesai = VerificationActivitySample::where('id_sample', $samples->first()->id_samples)
        ->where('id_verification_activity', 3)
        ->where('is_done', 1)
        ->first();
    }

    // Ambil data pengetikan laporan (id = 5) - ambil semua (tidak hanya is_done = 1)
    $pengetikan_laporan = null;
    if ($samples->count() > 0) {
      $pengetikan_laporan = VerificationActivitySample::where('id_sample', $samples->first()->id_samples)
        ->where('id_verification_activity', 5)
        ->get();
    }

    // Jika input output selesai (id = 3) is_done = true, maka pengetikan menggunakan data dari disposisi kesmas (id = 4) dan analis (id = 2)
    if ($input_output_selesai && $input_output_selesai->is_done == 1) {
      // Prioritas: gunakan data dari disposisi kesmas (id = 4), jika tidak ada gunakan analis (id = 2)
      $source_data = null;
      if ($verifikasi_koordinator) {
        $source_data = $verifikasi_koordinator;
      } elseif ($verifikasi_analis) {
        $source_data = $verifikasi_analis;
      }

      // Jika ada source data, pastikan pengetikan terisi
      if ($source_data) {
        // Jika pengetikan belum ada, buat objek baru
        if (!$pengetikan_laporan) {
          $pengetikan_laporan = new \stdClass();
        }

        // Copy data dari disposisi kesmas atau analis ke pengetikan
        $pengetikan_laporan->start_date = $source_data->start_date;
        $pengetikan_laporan->stop_date = $source_data->stop_date ?? $source_data->start_date;
        $pengetikan_laporan->nama_petugas = $source_data->nama_petugas;
        $pengetikan_laporan->is_done = $source_data->is_done ?? 1;
      }
    }

    // Ambil data penerimaan per sample untuk kelayakan dan kondisi
    $penerimaan_per_sample = [];
    foreach ($samples as $sample) {
      $penerimaan = PenerimaanSample::where('sample_id', $sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->first();
      if ($penerimaan) {
        $penerimaan_per_sample[$sample->id_samples] = $penerimaan;
      }

      // Ambil parameter per sample
      $methods = SampleMethod::where('sample_id', $sample->id_samples)
        ->where('laboratorium_id', $idlabs)
        ->join('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
        ->select('ms_method.params_method')
        ->get();
      $sample->parameters = $methods;
    }

    $reviewSample = $samples->first();
    $fontsize = (float) data_get($reviewSample, 'fontsize_hasil_baca_hasil', 11.0);
    $lineHeight = (float) data_get($reviewSample, 'line_height_hasil_baca_hasil', 1.0);
    $padding = (float) data_get($reviewSample, 'padding_hasil_baca_hasil', 1.0);
    $showKop = (int) data_get($reviewSample, 'show_kop_hasil_baca_hasil', 1);

    // Generate PDF
    $pdf = PDF::loadView('masterweb::module.admin.laboratorium.sample.print-formulir-pengamanan', compact(
      'permohonan_uji',
      'laboratorium',
      'samples',
      'penerimaan_sample',
      'penerimaan_per_sample',
      'verifikasi_koordinator',
      'pengetikan_laporan',
      'fontsize',
      'lineHeight',
      'padding',
      'showKop'
    ));

    $pdf->setPaper('A4', 'portrait');

    return $pdf->stream('Formulir_Pengamanan_Sampel_' . $permohonan_uji->noregister_permohonan_uji . '.pdf');
  }

  private function searchPetugas(string $namaCariPetugas){
    $namaCari = trim(str_replace(',', '', $namaCariPetugas));
    $namaCariTanpaGelar = preg_replace('/\b[A-Z][a-z]*\.[A-Z]*\b/', '', $namaCari);
    $namaCariTanpaGelar = trim($namaCariTanpaGelar);

    $petugas = Petugas::whereRaw("REPLACE(nama, ',', '') LIKE ?", ['%' . $namaCari . '%'])->first();

    if (!$petugas) {
        $petugas = Petugas::whereRaw("REPLACE(nama, ',', '') LIKE ?", ['%' . $namaCariTanpaGelar . '%'])->first();
    }

    return $petugas;
  }

  /**
   * Edit all samples in a group
   * Redirects to edit page of first sample in group with group_id parameter
   */
  public function editGroup($group_id)
  {
    $firstSample = Sample::where('group_id', $group_id)->first();

    if (!$firstSample) {
      return redirect()->back()->with('error', 'Group tidak ditemukan');
    }

    // Redirect to edit first sample, but we'll handle group editing in the view
    return redirect()->route('elits-samples.edit', [$firstSample->id_samples])->with('group_id', $group_id);
  }

  /**
   * Duplicate all samples in a group
   */
  public function duplicateGroup(Request $request, $group_id)
  {
    try {
      DB::beginTransaction();

      $groupSamples = Sample::where('group_id', $group_id)->get();

      if ($groupSamples->isEmpty()) {
        return response()->json([
          'status' => false,
          'message' => 'Group tidak ditemukan'
        ], 404);
      }

      // Get permohonan_uji_id from first sample
      $permohonan_uji_id = $groupSamples->first()->permohonan_uji_id;
      $permohonan_uji = PermohonanUji::find($permohonan_uji_id);
      $user = Auth()->user();

      // Get laboratoriums
      $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

      // Generate new group_id for duplicated samples
      // IMPORTANT: All duplicated samples will share the same new group_id
      // This ensures they are grouped together as a new group, separate from the original
      $new_group_id = Uuid::uuid4()->toString();

      $duplicatedSamples = [];

      // Process each sample in group
      // Each sample in the original group will be duplicated with the new group_id
      foreach ($groupSamples as $originalSample) {
        // Get sample type for code generation
        $sample_type = SampleType::find($originalSample->typesample_samples);
        $sample_type_code = $sample_type->code_sample_type ?? '...';

        // Get lab IDs from sample methods
        $lab_ids = [];
        $sampleMethods = SampleMethod::where('sample_id', $originalSample->id_samples)->get();
        foreach ($sampleMethods as $method) {
          if (!in_array($method->laboratorium_id, $lab_ids)) {
            $lab_ids[] = $method->laboratorium_id;
          }
        }

        // Skip if no lab IDs found
        if (empty($lab_ids)) {
          continue;
        }

        // Process each lab for this sample (similar to storeMultipleSamples)
        foreach ($lab_ids as $lab_key) {
          // Get laboratorium info
          $current_laboratorium = $laboratoriums->where('id_laboratorium', $lab_key)->first();
          if (!$current_laboratorium) continue;

          $current_lab_name = strtolower($current_laboratorium->nama_laboratorium);
          if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';
          $lab_code = $current_lab_name === 'kimia' ? '01' : '02';

          // Get start number
          $start_num = StartNum::join('ms_laboratorium', function ($join) {
            $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
              ->whereNull('ms_laboratorium.deleted_at')
              ->whereNull('ms_start_number.deleted_at');
          })->where('id_laboratorium', $lab_key)->first();

          // Check if makanan/minuman
          $is_makanan = false;
          if ($sample_type && str_contains($sample_type->name_sample_type, "Makanan/Minuman/Lainnya")) {
            $is_makanan = true;
            $start_num = StartNum::where('code_lab_start_number', 'MAK-MIN')->first();
          }

          // Generate lab number - urutan GLOBAL bersama klinik+kesmas (ikut nomor lab manual terakhir)
          $current_year = date('Y');

          $current_global = GlobalLabSequence::getCurrentNumber($current_year);
          if ($current_global == 0 && $start_num && date('Y') == ($start_num->year_start_number ?? date('Y'))) {
            GlobalLabSequence::raiseLastNumberToAtLeast((int) ($start_num->count_start_number ?? 0), $current_year);
          }

          $lab_num_urutan = GlobalLabSequence::getNextNumber($current_year, $lab_key, 'lab', null);
          $sequence_detail_new = GlobalLabSequenceDetail::where('year', $current_year)
            ->where('sequence_number', $lab_num_urutan)
            ->where('lab_id', $lab_key)
            ->where('lab_type', 'lab')
            ->orderBy('created_at', 'desc')
            ->first();

          // Generate sample code
          $code_number = str_pad((int)$lab_num_urutan, 4, '0', STR_PAD_LEFT);
          $code_year = Carbon::now()->format('Y');
          $code_sample = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;

          // Duplicate sample for this lab
          // IMPORTANT: All duplicated samples use the same new_group_id
          // This ensures all samples from the duplicated group are linked together
          $duplicateSample = $originalSample->replicate();
          $duplicateSample->id_samples = Uuid::uuid4()->toString();
          $duplicateSample->group_id = $new_group_id; // Same group_id for all duplicated samples
          $duplicateSample->codesample_samples = $code_sample;
          $duplicateSample->count_id = $code_number;
          $duplicateSample->is_nomor_sampel_manual = 0;
          $duplicateSample->is_nomor_laboratorium_manual = 0;
          // Ensure packet_id is copied from original sample
          $duplicateSample->packet_id = $originalSample->packet_id;
          $duplicateSample->save();

          // Create LabNum
          $lab_num = LabNum::create([
            'sample_id' => $duplicateSample->id_samples,
            'sample_type_id' => $duplicateSample->typesample_samples,
            'lab_id' => $lab_key,
            'is_makanan' => $is_makanan ? 1 : 0,
            'mount_lab_num' => Carbon::now()->format('m'),
            'year_lab_num' => Carbon::now()->format('Y'),
            'permohonan_uji_id' => $duplicateSample->permohonan_uji_id,
            'lab_number' => $lab_num_urutan,
          ]);

          // Update reference_id in GlobalLabSequenceDetail
          if ($sequence_detail_new) {
            $sequence_detail_new->reference_id = $lab_num->id_lab_num;
            $sequence_detail_new->save();
          }

          // Duplicate sample methods for this lab only
          foreach ($sampleMethods as $method) {
            if ($method->laboratorium_id == $lab_key) {
              $duplicateMethod = $method->replicate();
              $duplicateMethod->id_sample_method = Uuid::uuid4()->toString();
              $duplicateMethod->sample_id = $duplicateSample->id_samples;
              $duplicateMethod->save();
            }
          }

          // Duplicate penerimaan samples for this lab
          $penerimaanSamples = PenerimaanSample::where('sample_id', $originalSample->id_samples)
            ->where('laboratorium_id', $lab_key)
            ->get();
          foreach ($penerimaanSamples as $penerimaan) {
            $duplicatePenerimaan = $penerimaan->replicate();
            $duplicatePenerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
            $duplicatePenerimaan->sample_id = $duplicateSample->id_samples;
            $duplicatePenerimaan->save();
          }

          // Create VerificationActivitySample step 1 automatically
          $verificationActivitySample = new VerificationActivitySample();
          $verificationActivitySample->id = Uuid::uuid4()->toString();
          $verificationActivitySample->id_verification_activity = 1;
          $verificationActivitySample->id_sample = $duplicateSample->id_samples;
          $verificationActivitySample->start_date = $duplicateSample->date_sending;
          $verificationActivitySample->stop_date = Carbon::parse($duplicateSample->date_sending)->addMinutes(5)->format('Y-m-d H:i:s');
          $verificationActivitySample->nama_petugas = $permohonan_uji->petugas_penerima ?? $user->name ?? 'Petugas';
          $verificationActivitySample->is_done = true;
          $verificationActivitySample->save();

          $duplicatedSamples[] = $duplicateSample->id_samples;
        }
      }

      DB::commit();

      return response()->json([
        'status' => true,
        'message' => 'Berhasil menduplikasi group dengan ' . count($duplicatedSamples) . ' samples',
        'new_group_id' => $new_group_id,
        'samples' => $duplicatedSamples
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => false,
        'message' => 'Gagal menduplikasi group: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Destroy all samples in a group
   */
  public function destroyGroup($group_id)
  {
    try {
      DB::beginTransaction();

      $groupSamples = Sample::where('group_id', $group_id)->get();

      if ($groupSamples->isEmpty()) {
        return response()->json([
          'status' => false,
          'pesan' => 'Group tidak ditemukan'
        ], 404);
      }

      $permohonan_uji_id = $groupSamples->first()->permohonan_uji_id;
      $permohonan_uji = PermohonanUji::find($permohonan_uji_id);

      $totalToSubtract = 0;
      $yearsNeedSync = [];

      foreach ($groupSamples as $sample) {
        // Subtract costs
        $totalToSubtract += (float)$sample->cost_samples;
        if ($sample->is_sampling == 1) {
          $totalToSubtract += (float)$sample->cost_sampling_samples;
        }

        $yearsNeedSync[$sample->created_at
          ? (int) \Carbon\Carbon::parse($sample->created_at)->year
          : (int) date('Y')] = true;

        // Get all lab_num for this sample before deletion
        $labNums = LabNum::where('sample_id', $sample->id_samples)->get();

        // Delete global_lab_sequence_detail for all lab_num of this sample
        foreach ($labNums as $ln) {
          if ($ln->id_lab_num) {
            \Smt\Masterweb\Models\GlobalLabSequence::deleteByLabNumId($ln->id_lab_num);
          }
        }

        // Delete all lab_num records
        LabNum::where('sample_id', $sample->id_samples)->delete();

        // Delete sample
        $sample->delete();
      }

      // Update permohonan_uji total
      if ($permohonan_uji) {
        $permohonan_uji->total_harga = (float)$permohonan_uji->total_harga - $totalToSubtract;
        $permohonan_uji->save();
      }

      DB::commit();

      foreach (array_keys($yearsNeedSync) as $year) {
        \Smt\Masterweb\Models\GlobalLabSequence::syncAfterSampleChange((int) $year);
      }

      return response()->json([
        'status' => true,
        'pesan' => 'Berhasil menghapus group dengan ' . count($groupSamples) . ' samples'
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => false,
        'pesan' => 'Gagal menghapus group: ' . $e->getMessage()
      ], 500);
    }
  }
}