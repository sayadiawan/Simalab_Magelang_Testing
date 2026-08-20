<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class MethodSampling extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_method_sampling";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_method_sampling';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   

}
