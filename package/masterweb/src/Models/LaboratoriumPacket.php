<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class LaboratoriumPacket extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_laboratorium_packet";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_laboratorium_packet';
    
     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}
