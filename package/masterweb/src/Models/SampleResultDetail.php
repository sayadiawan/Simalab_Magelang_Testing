<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleResultDetail extends Model
{
  use Uuid;
  use SoftDeletes;

  public $incrementing = false;
  protected $table = "tb_sample_result_detail";
  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_sample_result_detail';

  
}

