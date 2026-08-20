<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiSubPaketExtraKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_sub_paket_extra_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_sub_paket_extra_klinik';

  protected $guarded = ['id_permohonan_uji_sub_paket_extra_klinik'];


  // public function parameterjenisklinik()
  // {
  //   return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik', 'id_parameter_jenis_klinik')->withDefault();
  // }

  // public function parameterpaketklinik()
  // {
  //   return $this->belongsTo(ParameterPaketKlinik::class, 'parameter_paket_klinik', 'id_parameter_paket_klinik')->withDefault();
  // }

  // public function permohonanujiklinik()
  // {
  //   return $this->belongsTo(PermohonanUjiKlinik2::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik')->withDefault();
  // }

  // public function permohonanujiklinik2()
  // {
  //   return $this->belongsTo(PermohonanUjiKlinik::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik')->withDefault();
  // }

  // /**
  //  * Get all of the comments for the PermohonanUjiPaketKlinik
  //  *
  //  * @return \Illuminate\Database\Eloquent\Relations\HasMany
  //  */
  // public function permohonanujiparameterklinik()
  // {
  //   return $this->hasMany(PermohonanUjiParameterKlinik::class, 'permohonan_uji_paket_klinik', 'id_permohonan_uji_paket_klinik');
  // }
}
