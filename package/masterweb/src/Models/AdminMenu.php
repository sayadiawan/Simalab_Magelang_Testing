<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;
class AdminMenu extends Model
{
    protected $table = "ms_menuadm";
    use SoftDeletes;   

    
}
