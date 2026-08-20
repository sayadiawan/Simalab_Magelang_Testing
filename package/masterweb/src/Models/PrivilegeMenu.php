<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class PrivilegeMenu extends Model
{
    use Uuid;
    public $incrementing = false;
    protected $table = "tb_privilege_menu";

    protected $dates = ['deleted_at'];
    protected $primaryKey = 'id_privilege_features';

}
