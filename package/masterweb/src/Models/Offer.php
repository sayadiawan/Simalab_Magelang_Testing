<?php

namespace Smt\Masterweb\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Offer extends Model
{

    use SoftDeletes;
    use Uuid;

    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $table = "tb_offer";
    protected $guarded = ['id'];

    public function menu()
    {
        return $this->hasOne('Smt\Masterweb\Models\Menu', 'id', 'menu_id');
    }
}
