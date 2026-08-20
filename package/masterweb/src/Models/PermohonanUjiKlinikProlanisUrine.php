<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiKlinikProlanisUrine extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_klinik_prolanis_urine";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_klinik_prolanis_urine';

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
}
