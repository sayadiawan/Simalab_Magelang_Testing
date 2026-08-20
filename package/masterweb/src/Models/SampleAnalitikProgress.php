<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleAnalitikProgress extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_sample_analitik_progress";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_sample_analitik_progress';


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
}