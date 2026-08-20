<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleType extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_sample_type";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_sample_type';

  public function matriksjenissarana()
  {
    return $this->belongsTo(MatriksJenisSarana::class, 'sample_type_sarana_id', 'id_sample_type_sarana')->withDefault();
  }
}