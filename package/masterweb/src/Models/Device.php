<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Device extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_devices";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'kode_device';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   

    function getuser()
    {
        return $this->hasOne('Smt\Masterweb\Models\User','client_id','id');
    }

    
}
