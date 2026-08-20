<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Unit extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_unit";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_unit';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}
