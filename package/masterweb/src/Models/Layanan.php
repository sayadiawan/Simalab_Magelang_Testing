<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Layanan extends Model
{
    use SoftDeletes;
    use Uuid;
    public $incrementing = false;
    protected $table = "tb_layanan";
    protected $primaryKey = 'id_layanan';
    protected $dates = ['deleted_at'];

    public function menu()
    {
        return $this->hasOne('Smt\Masterweb\Models\Menu', 'id', 'menu_id');
    }

    public function category_layanan()
    {
        return $this->hasOne('\Smt\Masterweb\Models\CategoryLayanan', 'id_category_layanan', 'kategori');
    }
}
