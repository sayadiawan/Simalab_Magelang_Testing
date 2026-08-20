<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SampleMethodDraft extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_method_draft";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_method_draft';

    protected $fillable = [
        'id_sample_method_draft',
        'sample_draft_id',
        'method_id',
        'laboratorium_id',
        'price_method',
        'is_sub',
    ];

    protected $casts = [
        'price_method' => 'decimal:2',
        'is_sub' => 'integer',
    ];

    public function sampledraft()
    {
        return $this->belongsTo(SampleDraft::class, 'sample_draft_id', 'id_sample_draft');
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'method_id', 'id_method')->where('deleted_at', NULL);
    }

    public function laboratorium()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium_id', 'id_laboratorium')->where('deleted_at', NULL);
    }
}


