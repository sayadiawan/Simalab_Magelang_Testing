<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class NomorChangeHistory extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'tb_nomor_change_history';
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_nomor_change_history';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'field_name',
        'old_value',
        'new_value',
        'event',
        'source',
        'note',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withDefault();
    }
}
