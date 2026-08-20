<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Method extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_method";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_method';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_option' => 'boolean',
    ];

    // function user(){
    //     return $this->hasMany("App\User",'level','id');
    // }

    public function laboratorium_method()
    {
      return $this->hasMany(LaboratoriumMethod::class, 'method_id', 'id_method');
    }

    public function bakumutu()
    {
      return $this->hasOne(BakuMutu::class, 'method_id', 'id_method');
    }
    
}