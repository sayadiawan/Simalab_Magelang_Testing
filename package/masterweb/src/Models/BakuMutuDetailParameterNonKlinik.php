<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class BakuMutuDetailParameterNonKlinik extends Model
{
  use Uuid;
  use SoftDeletes;

  public $incrementing = false;
  protected $table = "tb_baku_mutu_detail_parameter_non_klinik";
  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_baku_mutu_detail_parameter_non_klinik';

  
}

