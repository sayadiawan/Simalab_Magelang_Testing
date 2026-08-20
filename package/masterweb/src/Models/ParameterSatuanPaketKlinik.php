<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterSatuanPaketKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_satuan_paket_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_satuan_paket_klinik';


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
  }

  public function parameterpaketjenisklinik()
  {
    return $this->belongsTo(ParameterPaketJenisKlinik::class, 'parameter_paket_jenis_klinik', 'id_parameter_paket_jenis_klinik');
  }
}