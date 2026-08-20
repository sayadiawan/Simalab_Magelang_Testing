<?php



namespace Smt\Masterweb\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Smt\Masterweb\Traits\Uuid;



class Testimoni extends Model

{

    use SoftDeletes;

    use Uuid;



    protected $table = "tb_testimoni";

    protected $dates = ['deleted_at'];

    public $incrementing = false;

    protected $primaryKey = 'id_testimoni';

}

