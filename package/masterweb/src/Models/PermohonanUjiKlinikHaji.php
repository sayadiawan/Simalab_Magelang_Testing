<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;


class PermohonanUjiKlinikHaji extends Model
{
  use SoftDeletes;
  use Uuid;

  //prolanis gula
  protected $table = "tb_permohonan_uji_klinik_haji";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_klinik_haji';

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
}
