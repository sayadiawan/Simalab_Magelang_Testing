<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleResult extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_result";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_result';
    protected $fillable = ['lokasi_selected'];

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }

    public function method()
    {
        return $this->hasMany(Method::class, 'id_method', 'method_id');
    }
}
