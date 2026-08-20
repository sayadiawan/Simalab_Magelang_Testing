<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleOfficer extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_sample_officer";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_officer';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
}
