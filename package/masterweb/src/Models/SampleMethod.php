<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleMethod extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_sample_method";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_sample_method';
  
  protected $fillable = [
    'id_sample_method',
    'sample_id',
    'method_id',
    'laboratorium_id',
    'price_method',
    'is_sub',
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */

  public function sample()
  {
    return $this->belongsTo(Sample::class, 'sample_id', 'id_samples')->withDefault();
  }

  public function method()
  {
    return $this->belongsTo(Method::class, 'method_id', 'id_method')->withDefault();
  }

  public function laboratorium()
  {
    return $this->belongsTo(Laboratorium::class, 'laboratorium_id', 'id_laboratorium')->withDefault();
  }
}