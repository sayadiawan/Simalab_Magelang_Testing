<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PengesahanHasil extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_pengesahan_hasil";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_pengesahan_hasil';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public function sample()
    {
        return $this->belongsTo(Sample::class, 'sample_id', 'id_samples');
    }
}
