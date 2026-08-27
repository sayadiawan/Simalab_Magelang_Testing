<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PermohonanUjiKlinik2 extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_klinik_2";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_klinik';

  protected $guarded = ['id_permohonan_uji_klinik'];


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  /**
   * Get the permohonanuji associated with the PermohonanUji
   *
   * @return \Illuminate\Database\Eloquent\Relations\hasMany
   */
  public function permohonanujipaketklinik()
  {
    return $this->hasMany(PermohonanUjiPaketKlinik::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik');
  }

  public function permohonanujiparameterklinik()
  {
    return $this->hasMany(PermohonanUjiParameterKlinik::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik');
  }

  public function orderTms()
  {
    return $this->hasMany(OrderTms::class, 'id_permohonan_uji_klinik', 'id_permohonan_uji_klinik');
  }

  public function pasien()
  {
    return $this->belongsTo(Pasien::class, 'pasien_permohonan_uji_klinik', 'id_pasien')->withDefault();
  }

  public function permohonanujipaymentklinik()
  {
    return $this->hasOne(PermohonanUjiPaymentKlinik::class, 'id_permohonan_uji_klinik', 'permohonan_uji_klinik_id');
  }

  public function permohonanujianalisklinik()
  {
    return $this->hasOne(PermohonanUjiAnalisKlinik::class, 'permohonan_uji_klinik_id', 'id_permohonan_uji_klinik');
  }

  // public function pengirim()
  // {
  //   return $this->belongsTo(User::class, 'namapengirim_permohonan_uji_klinik', 'id')->withDefault();
  // }

  public function analis()
  {
    return $this->belongsTo(User::class, 'id', 'analis_permohonan_uji_klinik')->withDefault();
  }

  public function dokter()
  {
    return $this->belongsTo(User::class, 'id', 'dokter_rekomendasi_permohonan_uji_klinik')->withDefault();
  }

  public function pengambilanSampleKlinik()
  {
    return $this->hasMany(PengambilanSampleKlinik::class, 'permohonan_uji_klinik_id', 'id_permohonan_uji_klinik');
  }

  /**
   * Generate noregister_permohonan_uji_klinik with format: {nourut_permohonan_uji_klinik}/{jumlah_pendaftaran_klinik}
   * Both are 4 digits padded with zeros, starting from 1
   */
  public function generateNoregister()
  {
    $nourut = $this->nourut_permohonan_uji_klinik ?? 1;
    $year = (int)date('Y', strtotime($this->created_at ?? 'now'));
    // Order/index of this registration among all registrations in the year
    $registration_index = self::where(DB::raw('YEAR(created_at)'), '=', $year)
      ->where('created_at', '<=', $this->created_at ?? now())
      ->count();
    $registration_index = max(1, $registration_index);
    
    // Format both as 4 digits
    return str_pad($nourut, 4, '0', STR_PAD_LEFT) . '/' . str_pad($registration_index, 4, '0', STR_PAD_LEFT);
  }

  /**
   * Angka urut spesimen/sampel untuk tampilan (03/{urut}/tahun).
   */
  public function resolveSpesimenUrut(): string
  {
    // Manual hanya dipakai jika flag aktif DAN angka wajar (1–6 digit, bukan loncatan 5xxx+).
    // Tolak junk seperti NIK/HP (mis. 25991559) dan nomor loncatan counter (mis. 6216).
    $manual = preg_replace('/\D+/', '', trim((string) ($this->nomor_spesimen_manual ?? '')));
    if ((int) ($this->is_nomor_spesimen_manual ?? 0) === 1
      && $manual !== ''
      && strlen($manual) <= 6
      && (int) $manual > 0
      && (int) $manual < 5000
    ) {
      return (string) ((int) $manual);
    }

    $noreg = trim((string) ($this->noregister_permohonan_uji_klinik ?? ''));
    if ($noreg !== '') {
      // "3480" atau "3480 / 2139"
      if (preg_match('/^\d{1,6}$/', $noreg)) {
        return (string) ((int) $noreg);
      }
      if (preg_match('/^(\d{1,6})\s*\/\s*\d+/', $noreg, $m)) {
        return (string) ((int) $m[1]);
      }
      // "03/3480/2026"
      if (preg_match('#(?:^|/)\s*(\d{1,6})\s*/\s*\d{4}\s*$#', $noreg, $m)) {
        return (string) ((int) $m[1]);
      }
      // "3480/0001" (format lama)
      if (preg_match('/^(\d{1,6})\s*\//', $noreg, $m)) {
        return (string) ((int) $m[1]);
      }
    }

    $nourut = preg_replace('/\D+/', '', trim((string) ($this->nourut_permohonan_uji_klinik ?? '')));
    if ($nourut !== '' && strlen($nourut) <= 6 && (int) $nourut > 0) {
      return (string) ((int) $nourut);
    }

    return '';
  }

  /**
   * Angka urut nomor lab untuk tampilan (449.5/03/{urut}/tahun).
   */
  public function resolveLabUrut(): string
  {
    $manual = preg_replace('/\D+/', '', trim((string) ($this->nomor_lab_manual ?? '')));
    // Abaikan 0 / kosong — itu data rusak, jangan ditampilkan sebagai nomor lab
    if ($manual !== '' && (int) $manual > 0) {
      return (string) ((int) $manual);
    }

    $nomerLab = preg_replace('/\D+/', '', trim((string) ($this->nomer_lab ?? '')));
    if ($nomerLab !== '' && (int) $nomerLab > 0) {
      return (string) ((int) $nomerLab);
    }

    // Jangan pakai bagian kedua noregister: formatnya spesimen/jumlah_pendaftaran, bukan nomor lab.
    return '';
  }

  /**
   * Get nomor laboratorium (manual atau otomatis berdasarkan flag permohonan).
   * @return string|null
   */
  public function getNomorLab()
  {
    $urut = $this->resolveLabUrut();
    return $urut !== '' ? $urut : null;
  }

  /**
   * Get nomor spesimen (manual atau otomatis berdasarkan flag permohonan).
   * @return string|null
   */
  public function getNomorSpesimen()
  {
    $urut = $this->resolveSpesimenUrut();
    return $urut !== '' ? $urut : null;
  }

  /**
   * Get display nomor register untuk klinik berdasarkan flag permohonan.
   * Format: {number sampel} / {number lab}
   * @return string
   */
  public function getDisplayNoregister($settings = null)
  {
    $spesimen = $this->resolveSpesimenUrut();
    $lab = $this->resolveLabUrut();

    if ($spesimen !== '' && $lab !== '') {
      return $spesimen . ' / ' . $lab;
    }

    if ($spesimen !== '') {
      return $spesimen;
    }

    if ($lab !== '') {
      return $lab;
    }

    return $this->noregister_permohonan_uji_klinik ?? '-';
  }

  /**
   * Urutkan query berdasarkan nomor spesimen (sesuai halaman registrasi).
   */
  public function scopeOrderByNomerSpesimen($query, $direction = 'asc', $settings = null)
  {
    $table = $query->getModel()->getTable();
    $dir = strtolower((string) $direction) === 'desc' ? 'DESC' : 'ASC';

    // Spesimen: manual → nomor_spesimen_manual; else nourut / bagian kiri noregister
    $spesimenExpr = "CASE "
      . "WHEN {$table}.is_nomor_spesimen_manual = 1 "
      . "AND NULLIF(TRIM({$table}.nomor_spesimen_manual), '') IS NOT NULL "
      . "THEN CAST(NULLIF(TRIM({$table}.nomor_spesimen_manual), '') AS UNSIGNED) "
      . "WHEN NULLIF(TRIM({$table}.nourut_permohonan_uji_klinik), '') IS NOT NULL "
      . "THEN CAST(NULLIF(TRIM({$table}.nourut_permohonan_uji_klinik), '') AS UNSIGNED) "
      . "ELSE CAST(NULLIF(TRIM(SUBSTRING_INDEX({$table}.noregister_permohonan_uji_klinik, '/', 1)), '') AS UNSIGNED) END";
    // Lab: manual / nomer_lab; jangan pakai bagian kanan noregister (itu jumlah pendaftaran)
    $labExpr = "CASE "
      . "WHEN {$table}.is_nomor_lab_manual = 1 "
      . "AND NULLIF(TRIM({$table}.nomor_lab_manual), '') IS NOT NULL "
      . "AND CAST(NULLIF(TRIM({$table}.nomor_lab_manual), '') AS UNSIGNED) > 0 "
      . "THEN CAST(NULLIF(TRIM({$table}.nomor_lab_manual), '') AS UNSIGNED) "
      . "WHEN NULLIF(TRIM({$table}.nomer_lab), '') IS NOT NULL "
      . "AND CAST(NULLIF(TRIM({$table}.nomer_lab), '') AS UNSIGNED) > 0 "
      . "THEN CAST(NULLIF(TRIM({$table}.nomer_lab), '') AS UNSIGNED) "
      . "ELSE 0 END";

    return $query
      ->orderByRaw("{$spesimenExpr} {$dir}")
      ->orderByRaw("{$labExpr} {$dir}");
  }

  public function getLabNumber()
  {
    $urut = $this->resolveLabUrut();
    $year = Carbon::parse($this->tglregister_permohonan_uji_klinik ?? $this->created_at ?? now())->year;
    if ($urut !== '') {
      return '449.5/03/' . $urut . '/' . $year;
    }

    $nourut = (int) ($this->nourut_permohonan_uji_klinik ?? 0);
    if ($nourut > 0) {
      return '449.5/03/' . $nourut . '/' . $year;
    }

    return '—';
  }

  public function getSpesimenNumber()
  {
    if ((int) ($this->is_nomor_spesimen_manual ?? 0) === 1 && !empty($this->nomor_spesimen_manual)) {
      return "03/" . $this->nomor_spesimen_manual . '/' . Carbon::parse($this->created_at ?? now())->year;
    }
    return "03/" . $this->nourut_permohonan_uji_klinik . '/' . Carbon::parse($this->created_at ?? now())->year;
  }

  public function getNoRekamMedis()
  {
    return "449.5." . str_pad($this->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT);
  }

  /**
   * Nama pengirim untuk tampilan (rujukan/haji = dokter pengirim, lab = dokter penanggung jawab lab).
   */
  public function getNamaPengirim($defaultLabDokter = 'dr. Dummy Pengirim')
  {
    if ($this->doctor_type === 'rujukan' || $this->is_haji == 1) {
      return $this->nama_dokter_pengirim_permohonan_uji_klinik ?: '-';
    }

    if (!empty($this->nama_dokter_pengirim_permohonan_uji_klinik)) {
      return $this->nama_dokter_pengirim_permohonan_uji_klinik;
    }

    return $defaultLabDokter;
  }

  protected static function booted()
  {
    static::updating(function (self $model) {
      $model->logNomorFields();
    });
  }

  /**
   * @return string[]
   */
  public static function nomorTrackedFields(): array
  {
    return [
      'nourut_permohonan_uji_klinik',
      'noregister_permohonan_uji_klinik',
      'nomer_lab',
      'nomor_lab_manual',
      'nomor_spesimen_manual',
    ];
  }

  private function logNomorFields(): void
  {
    foreach (self::nomorTrackedFields() as $field) {
      if (!$this->isDirty($field)) {
        continue;
      }

      \Smt\Masterweb\Helpers\NomorChangeLogger::record([
        'subject_type' => 'klinik',
        'subject_id' => (string) $this->getKey(),
        'field_name' => $field,
        'old_value' => $this->getOriginal($field),
        'new_value' => $this->getAttribute($field),
        'event' => 'penggantian',
        'source' => self::resolveNomorChangeSource(),
      ]);
    }
  }

  private static function resolveNomorChangeSource(): string
  {
    try {
      $path = request()->path();
      if (strpos($path, 'import-haji') !== false) {
        return 'import-excel';
      }
      if (strpos($path, 'store-pasien') !== false || strpos($path, 'haji/store') !== false) {
        return 'haji';
      }
      if (strpos($path, 'elits-permohonan-uji-klinik') !== false) {
        return 'klinik';
      }
    } catch (\Throwable $e) {
      // ignore
    }

    return 'sistem';
  }
}
