<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class BakuMutuDetailParameterKlinik extends Model
{
  use Uuid;
  use SoftDeletes;

  public $incrementing = false;
  protected $table = "tb_baku_mutu_detail_parameter_klinik";
  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_baku_mutu_detail_parameter_klinik';

  public function parameterjenisklinik()
  {
    return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik_id', 'id_parameter_jenis_klinik')->withDefault();
  }

  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik_id', 'id_parameter_satuan_klinik')->withDefault();
  }

  public function bakumutu()
  {
    return $this->belongsTo(BakuMutu::class, 'baku_mutu_id', 'id_baku_mutu')->withDefault();
  }

  public function parametersubsatuanklinik()
  {
    return $this->belongsTo(ParameterSubSatuanKlinik::class, 'parameter_sub_satuan_baku_mutu_detail_parameter_klinik', 'id_parameter_sub_satuan_klinik')->withDefault();
  }

  public function unit()
  {
    return $this->hasOne(Unit::class, 'id_unit', 'unit_id_baku_mutu_detail_parameter_klinik');
  }
}