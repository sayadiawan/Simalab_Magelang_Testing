<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Stock extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_stock";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_stock';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}
