<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Role extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_role";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
