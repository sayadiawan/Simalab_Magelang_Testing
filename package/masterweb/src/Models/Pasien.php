<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Pasien extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_pasien";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_pasien';

  protected $fillable = [
    'nik_pasien',
    'nourut_pasien',
    'no_rekammedis_pasien',
    'id_pasien_satu_sehat',
    'nama_pasien',
    'wilayah_id',
    'gender_pasien',
    'tgllahir_pasien',
    'umurtahun_pasien',
    'umurbulan_pasien',
    'umurhari_pasien',
    'alamat_pasien',
    'phone_pasien',
    'tmpt_lahir',
    'pekerjaan',
  ];


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */

  public function permohonanujiklinik()
  {
    return $this->hasMany(PermohonanUjiKlinik::class, 'pasien_permohonan_uji_klinik', 'id_pasien');
  }

  /**
   * Get the wilayah that owns the pasien.
   */
  public function wilayah()
  {
    return $this->belongsTo(Wilayah::class, 'wilayah_id', 'id_wilayah');
  }

  /**
   * Nomor rekam medis berikutnya: MAX(no_rekammedis_pasien) sebagai bilangan bulat + 1 (minimum 1).
   * Hanya baris dengan no_rekammedis_pasien berisi angka murni yang dihitung (bukan nourut_pasien,
   * agar tidak loncat jika nourut lama masih besar setelah RM di-reset/dinomori ulang).
   * Baris soft-delete (deleted_at IS NOT NULL) tidak ikut dihitung.
   */
  public static function nextNoRekamMedis(): int
  {
    $maxRm = (int) static::query()
      ->whereNull('deleted_at')
      ->whereNotNull('no_rekammedis_pasien')
      ->whereRaw("TRIM(no_rekammedis_pasien) != ''")
      ->whereRaw('TRIM(no_rekammedis_pasien) REGEXP "^[0-9]+$"')
      ->selectRaw('COALESCE(MAX(CAST(TRIM(no_rekammedis_pasien) AS UNSIGNED)), 0) as m')
      ->value('m');

    return max(1, $maxRm + 1);
  }
}