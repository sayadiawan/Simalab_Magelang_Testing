<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiParameterKlinik2 extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_parameter_klinik_2";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_parameter_klinik';

  protected $guarded = ['id_permohonan_uji_parameter_klinik'];


  public function permohonanujiklinik()
  {
    return $this->belongsTo(PermohonanUjiKlinik::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik')->withDefault();
  }

  public function permohonanujipaketklinik()
  {
    return $this->belongsTo(PermohonanUjiPaketKlinik::class, 'permohonan_uji_paket_klinik', 'id_permohonan_uji_paket_klinik')->withDefault();
  }

  public function permohonanujijenispaketklinik()
  {
    return $this->belongsTo(ParameterPaketJenisKlinik::class, 'parameter_paket_jenis_klinik', 'id_parameter_paket_jenis_klinik')->withDefault();
  }

  public function jenisparameterklinik()
  {
    return $this->belongsTo(ParameterJenisKlinik::class, 'jenis_parameter_klinik_id', 'id_parameter_jenis_klinik')->withDefault();
  }


  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'satuan_permohonan_uji_parameter_klinik', 'id_unit')->withDefault();
  }

  public function bakumutu()
  {
    return $this->belongsTo(BakuMutu::class, 'baku_mutu_permohonan_uji_parameter_klinik', 'id_baku_mutu')->withDefault();
  }

  public function permohonanujisubparameterklinik()
  {
    return $this->hasMany(PermohonanUjiSubParameterKlinik::class, 'permohonan_uji_parameter_klinik_id', 'id_permohonan_uji_parameter_klinik');
  }
}
