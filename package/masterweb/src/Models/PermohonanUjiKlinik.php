<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_klinik';


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
}