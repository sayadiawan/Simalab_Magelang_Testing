<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PrivilegeMenuRole extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_privilege_menu_role";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_privilege_menu_role';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   

}
