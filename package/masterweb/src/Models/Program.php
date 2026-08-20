<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class Program extends Model
{
    use Uuid;
    public $incrementing = false;
    protected $table = "ms_program";

    protected $dates = ['deleted_at'];
    protected $primaryKey = 'id_program';

}
