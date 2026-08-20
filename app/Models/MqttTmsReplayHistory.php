<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttTmsReplayHistory extends Model
{
    protected $table = 'tb_mqtt_tms_replay_history';

    protected $fillable = [
        'entry_key',
        'message_id',
        'sample_id',
        'tray',
        'pos',
        'log_received_at',
        'status',
        'log_error',
        'replay_error',
        'id_order_tms',
        'updated_count',
        'matched_by',
        'replayed_at',
    ];

    protected $casts = [
        'log_received_at' => 'datetime',
        'replayed_at' => 'datetime',
        'updated_count' => 'integer',
    ];

    public const STATUS_APPLIED = 'applied';
    public const STATUS_ALREADY_FILLED = 'already_filled';
    public const STATUS_NOT_APPLIED = 'not_applied';
}
