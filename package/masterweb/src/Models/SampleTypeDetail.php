<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleTypeDetail extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_sample_type_detail";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_type_detail';

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }
}
