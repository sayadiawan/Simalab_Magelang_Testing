<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class LaboratoriumMethod extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_laboratorium_method";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_laboratorium_method';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public function laboratorium()
    {
      return $this->belongsTo(LaboratoriumMethod::class, 'id_method', 'method_id');
    }
   
}