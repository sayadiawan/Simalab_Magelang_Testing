<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Rates extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_rate";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_rate';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}
