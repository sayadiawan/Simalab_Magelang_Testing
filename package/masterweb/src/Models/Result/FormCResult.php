<?php

namespace Smt\Masterweb\Models\Result;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class FormCResult extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_form_C_result";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
