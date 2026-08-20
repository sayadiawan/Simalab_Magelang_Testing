<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PelaporanHasil extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_pelaporan_hasil";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_pelaporan_hasil';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
}
