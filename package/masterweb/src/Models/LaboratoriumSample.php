<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class LaboratoriumSample extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_laboratorium";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_laboratorium';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
