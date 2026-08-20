<?php



namespace Smt\Masterweb\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Smt\Masterweb\Traits\Uuid;



class CategoryPortofolio extends Model

{

    use SoftDeletes;

    use Uuid;



    protected $table = "tb_category_portofolio";

    protected $dates = ['deleted_at'];

    public $incrementing = false;

    protected $primaryKey = 'id_category_portofolio';

    public function portofolio()
    {
        return $this->hasOne('\Smt\Masterweb\Models\Portofolio', 'catport_portofolio', 'id_category_portofolio');
    }

}

