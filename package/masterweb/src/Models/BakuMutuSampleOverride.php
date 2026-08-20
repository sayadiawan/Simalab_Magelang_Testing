<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class BakuMutuSampleOverride extends Model
{
    use Uuid;

    public $incrementing  = false;
    protected $table      = 'tb_baku_mutu_sample_override';
    protected $primaryKey = 'id';
    protected $keyType    = 'string';

    protected $fillable = [
        'id',
        'sample_progress_id',
        'method_id',
        'nilai_baku_mutu',
        'min',
        'max',
        'equal',
        'unit_id',
        'library_id',
    ];
}
