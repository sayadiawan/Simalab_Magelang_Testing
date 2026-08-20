<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class UserTele extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_user_tele";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    function getUser()
    {
        return $this->hasOne('Smt\Masterweb\Models\User','id','user_id')->select('id','root_firebase','client_id','username');
    }
   

}
