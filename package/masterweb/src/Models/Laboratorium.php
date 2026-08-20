<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Laboratorium extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_laboratorium";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_laboratorium';
    
     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
