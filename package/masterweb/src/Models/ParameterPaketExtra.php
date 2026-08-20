<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterPaketExtra extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_paket_extra";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_paket_extra';

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */

  // public function parameterjenisklinik()
  // {
  //     return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik', 'id_parameter_jenis_klinik');
  // }

  public function parameterSubPaketExtra()
  {
      return $this->hasMany(ParameterSubPaketExtra::class, 'id_parameter_paket_extra', 'id_parameter_paket_extra')->orderBy('created_at', 'asc');
  }

  // public function parametersatuanpaketklinik()
  // {
  //     return $this->hasMany(ParameterSatuanPaketKlinik::class, 'parameter_paket_klinik', 'id_parameter_paket_klinik');
  // }
}
