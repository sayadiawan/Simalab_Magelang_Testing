<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class MatriksJenisSarana extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_sample_type_sarana";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_sample_type_sarana';

  public function sampletype()
  {
    return $this->hasMany(SampleType::class, 'id_sample_type_sarana', 'sample_type_sarana_id');
  }
}