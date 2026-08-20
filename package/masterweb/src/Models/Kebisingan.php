<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Kebisingan extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_kebisingan";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_kebisingan';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
