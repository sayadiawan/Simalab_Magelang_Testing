<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Customer extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_customer";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_customer';

  // function user(){
  //     return $this->hasMany("App\User",'level','id');
  // }

  // public function category()
  // {
  //   return $this->hasOne(Industry::class, 'id_industry', 'category_customer');
  // }

  public function permohonanuji()
  {
    return $this->hasMany(PermohonanUji::class, 'customer_id', 'id_customer');
  }
}