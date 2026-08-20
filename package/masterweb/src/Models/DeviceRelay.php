<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class DeviceRelay extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_device_relay";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
    public function devices()
    {
        return $this->hasOne('Smt\Masterweb\Models\Device', 'kode_device', 'device_id');
    }
}
