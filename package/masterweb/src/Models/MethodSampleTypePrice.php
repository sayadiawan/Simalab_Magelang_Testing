<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class MethodSampleTypePrice extends Model
{
  use Uuid;

  public $incrementing = false;

  protected $table = 'ms_method_sample_type_price';

  protected $primaryKey = 'id';

  protected $keyType = 'string';

  protected $fillable = [
    'id',
    'method_id',
    'sample_type_id',
    'price',
  ];

  protected $casts = [
    'price' => 'decimal:2',
  ];

  /**
   * @param  array<int, object>  $data_methods  elemen punya ->method array of objects dengan id_method
   */
  public static function attachPricesToDataMethods(array $data_methods): array
  {
    $ids = [];
    foreach ($data_methods as $dm) {
      foreach ($dm->method as $m) {
        $ids[] = $m->id_method;
      }
    }
    $ids = array_unique($ids);
    if (empty($ids)) {
      return $data_methods;
    }

    $rows = self::query()->whereIn('method_id', $ids)->get();
    $map = [];
    foreach ($rows as $r) {
      $map[$r->method_id][(string) $r->sample_type_id] = (float) $r->price;
    }

    foreach ($data_methods as $i => $dm) {
      foreach ($dm->method as $j => $m) {
        $mid = $m->id_method;
        $data_methods[$i]->method[$j]->prices_by_sample_type = $map[$mid] ?? [];
      }
    }

    return $data_methods;
  }
}
