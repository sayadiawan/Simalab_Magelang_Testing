<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiSubParameterKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_sub_parameter_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_sub_parameter_klinik';

  public function permohonanujiparameterklinik()
  {
    return $this->belongsTo(PermohonanUjiParameterKlinik::class, 'id_permohonan_uji_parameter_klinik', 'permohonan_uji_parameter_klinik_id');
  }

  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'satuan_permohonan_uji_sub_parameter_klinik', 'id_unit')->withDefault();
  }

  public function bakumutudetailparameterklinik()
  {
    return $this->belongsTo(BakuMutuDetailParameterKlinik::class, 'parameter_sub_satuan_klinik_id', 'parameter_sub_satuan_baku_mutu_detail_parameter_klinik')->withDefault();
  }

  public function parametersubsatuanklinik()
  {
    return $this->belongsTo(ParameterSubSatuanKlinik::class, 'parameter_sub_satuan_klinik_id', 'id_parameter_sub_satuan_klinik');
  }

  public function history()
  {
    return $this->hasMany(PermohonanUjiSubParameterKlinikHistory::class, 'permohonan_uji_sub_parameter_klinik_id', 'id_permohonan_uji_sub_parameter_klinik')->orderBy('created_at', 'desc');
  }

  public function selectedHistory()
  {
    return $this->belongsTo(PermohonanUjiSubParameterKlinikHistory::class, 'selected_history_id', 'id_permohonan_uji_sub_parameter_klinik_history')->withDefault();
  }
}