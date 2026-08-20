<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    protected $table = "ms_menus";
    use SoftDeletes;
    
    public function typeMenu()
    {
        return $this->hasOne('Smt\Masterweb\Models\Type', 'id', 'type');
    }
}
