<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterSubPaketExtra extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_sub_paket_extra";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_sub_paket_extra';

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */

  public function parameterPaketExtra()
  {
    return $this->hasMany(ParameterPaketExtra::class, 'id_parameter_paket_extra', 'id_parameter_paket_extra');
  }

  public function parameterPaketKlinik()
  {
    return $this->belongsTo(ParameterPaketKlinik::class, 'id_parameter_paket_klinik', 'id_parameter_paket_klinik');
  }

}
