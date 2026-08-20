<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Industry extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_industry";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_industry';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}
