<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Layout extends Model
{
    use Uuid;
    use SoftDeletes;
    
    protected $table = "ms_layout";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id', 'id_type', 'action', 'layout'
    ];
}