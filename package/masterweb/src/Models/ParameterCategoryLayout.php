<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterCategoryLayout extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_param_category_layout";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_param_category_layout';

  protected $fillable = [
    'category_code',
    'category_name',
    'column_width',
    'empty_column_position',
    'grid_rows',
    'grid_columns',
    'sort_order',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'integer',
    'sort_order' => 'integer',
    'column_width' => 'integer',
    'grid_rows' => 'integer',
    'grid_columns' => 'integer',
  ];

  public function categoryItems()
  {
    return $this->hasMany(ParameterCategoryItem::class, 'id_param_category_layout', 'id_param_category_layout')
      ->orderBy('row_position', 'asc')
      ->orderBy('column_position', 'asc')
      ->orderBy('sort_order', 'asc');
  }
  
  public function categoryItemsGrid()
  {
    // For grid display, return items with their positions
    return $this->hasMany(ParameterCategoryItem::class, 'id_param_category_layout', 'id_param_category_layout')
      ->whereNotNull('row_position')
      ->whereNotNull('column_position')
      ->orderBy('row_position', 'asc')
      ->orderBy('column_position', 'asc');
  }
}
