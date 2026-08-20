<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PenangananSample extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_penanganan";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_penanganan';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
