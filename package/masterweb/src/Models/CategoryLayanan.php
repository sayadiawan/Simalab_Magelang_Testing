<?php



namespace Smt\Masterweb\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Smt\Masterweb\Traits\Uuid;



class CategoryLayanan extends Model

{

    use SoftDeletes;

    use Uuid;



    protected $table = "tb_category_layanan";

    protected $dates = ['deleted_at'];

    public $incrementing = false;

    protected $primaryKey = 'id_category_layanan';

    public function layanan()
    {
        return $this->hasOne('\Smt\Masterweb\Models\Layanan', 'kategori', 'id_category_layanan');
    }

}

