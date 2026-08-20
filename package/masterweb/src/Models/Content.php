<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class Content extends Model
{
    use Uuid;
    public $incrementing = false;
    protected $table = "tb_content";
    protected $primaryKey = 'id_content';

    public function menu()
    {
        return $this->hasOne('Smt\Masterweb\Models\Menu', 'id', 'menu_id');
    }
}
