<?php

namespace Smt\Masterweb\Models\Module;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class FormA extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_form_A";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
