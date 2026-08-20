<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Smt\Masterweb\Traits\Uuid;

class VerificationActivitySample extends Model
{
  use Uuid;

  /** Step klinik: Registrasi */
  public const ACTIVITY_REGISTRASI = 1;

  /** Step klinik: Pemeriksaan / olah sampel */
  public const ACTIVITY_PEMERIKSAAN = 2;

  /** Step klinik: Input hasil (analis) */
  public const ACTIVITY_INPUT_HASIL = 3;

  /** Step klinik: Verifikasi */
  public const ACTIVITY_VERIFIKASI = 4;

  /** Step klinik: Validasi */
  public const ACTIVITY_VALIDASI = 5;

  /** Step klinik: Pengambilan sampel */
  public const ACTIVITY_PENGAMBILAN_SAMPLE = 6;

  /** Step klinik: Penerimaan sampel */
  public const ACTIVITY_PENERIMAAN_SAMPLE = 7;

  protected $table = 'tb_verification_activity_samples';
  protected $primaryKey = 'id';
  public $timestamps = false;

  protected $fillable = [
    'id',
    'id_verification_activity',
    'id_sample',
    'start_date',
    'stop_date',
    'nama_petugas',
    'is_done',
    'is_klinik',
    'resampling',
  ];

  protected $casts = [
    'is_done' => 'integer',
    'resampling' => 'integer',
  ];

  public function activity(): BelongsTo
  {
    return $this->belongsTo(VerificationActivity::class, 'id_verification_activity', 'id');
  }

  public function permohonanKlinik(): BelongsTo
  {
    return $this->belongsTo(PermohonanUjiKlinik2::class, 'is_klinik', 'id_permohonan_uji_klinik');
  }

  public function scopeForKlinik($query, $permohonanId)
  {
    return $query->where('is_klinik', $permohonanId);
  }

  public function scopeActivity($query, $activityId)
  {
    return $query->where('id_verification_activity', $activityId);
  }

  public function scopeValidasi($query)
  {
    return $query->where('id_verification_activity', self::ACTIVITY_VALIDASI);
  }

  public function scopeDone($query)
  {
    return $query->where('is_done', 1);
  }
}
