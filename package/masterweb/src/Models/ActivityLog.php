<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class ActivityLog extends Model
{
    use Uuid;

    protected $table = 'tb_activity_log';
    public $incrementing = false;
    protected $primaryKey = 'id_activity_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'username',
        'privilege_level',
        'action',
        'bidang',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'route_name',
        'url',
        'http_method',
        'ip_address',
        'user_agent',
        'request_data',
        'metadata',
    ];

    protected $casts = [
        'request_data' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }
}
