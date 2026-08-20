<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Reportdetail extends Model
{
    protected $table = "tb_reportdetail";
    use SoftDeletes;
    use Uuid;

    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_repdetail';
}
