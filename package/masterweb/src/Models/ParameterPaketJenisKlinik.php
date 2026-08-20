<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterPaketJenisKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_paket_jenis_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_paket_jenis_klinik';

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */

  public function parametersatuanpaketklinik()
  {
    return $this->hasMany(ParameterSatuanPaketKlinik::class, 'parameter_paket_jenis_klinik', 'id_parameter_paket_jenis_klinik')->orderBy('sorting', 'asc');
  }

  public function parameterjenisklinik()
  {
    return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik_id', 'id_parameter_jenis_klinik');
  }
}
