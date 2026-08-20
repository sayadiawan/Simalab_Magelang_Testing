<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class LHU extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_lhu";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_lhu';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}