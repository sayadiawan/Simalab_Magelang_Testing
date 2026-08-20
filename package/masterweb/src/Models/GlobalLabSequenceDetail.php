<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Models\Laboratorium;

class GlobalLabSequenceDetail extends Model
{
    use Uuid;
    use SoftDeletes;

    public $incrementing = false;
    protected $table = "global_lab_sequence_detail";
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'year',
        'sequence_number',
        'lab_id',
        'lab_type',
        'reference_id',
    ];

    // Relations
    public function lab()
    {
        return $this->belongsTo(Laboratorium::class, 'lab_id', 'id_laboratorium')->withDefault();
    }
}

