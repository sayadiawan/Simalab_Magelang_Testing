<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterSubSatuanKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_sub_satuan_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_sub_satuan_klinik';


  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
  }

  public function permohonanujisubparameterklinik()
  {
    return $this->belongsTo(PermohonanUjiSubParameterKlinik::class, 'id_parameter_sub_satuan_klinik', 'parameter_sub_satuan_klinik_id');
  }

  public function bakumutudetailparmeterklinik()
  {
    return $this->belongsTo(BakuMutuDetailParameterKlinik::class, 'id_parameter_sub_satuan_klinik', 'parameter_sub_satuan_baku_mutu_detail_parameter_klinik')->withDefault();
  }
}