<?php



namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Smt\Masterweb\Traits\Uuid;



class ProductCategory extends Model

{

    use SoftDeletes;

    use Uuid;



    protected $table = "tb_productcategory";

    protected $dates = ['deleted_at'];

    public $incrementing = false;

    protected $primaryKey = 'id_productcategory';

}

