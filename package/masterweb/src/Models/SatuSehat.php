<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SatuSehat extends Model
{
  use Uuid;
  use SoftDeletes;

  public $incrementing = false;
  protected $table = "ms_satusehat_setting";

  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_satusehat';

}