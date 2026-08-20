<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterCategoryItem extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_param_category_items";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_param_category_item';

  protected $fillable = [
    'id_param_category_layout',
    'id_parameter_paket_klinik',
    'sort_order',
    'row_position',
    'column_position',
  ];
  
  protected $casts = [
    'sort_order' => 'integer',
    'row_position' => 'integer',
    'column_position' => 'integer',
  ];

  public function categoryLayout()
  {
    return $this->belongsTo(ParameterCategoryLayout::class, 'id_param_category_layout', 'id_param_category_layout');
  }

  public function parameterPaketKlinik()
  {
    return $this->belongsTo(ParameterPaketKlinik::class, 'id_parameter_paket_klinik', 'id_parameter_paket_klinik');
  }
}
