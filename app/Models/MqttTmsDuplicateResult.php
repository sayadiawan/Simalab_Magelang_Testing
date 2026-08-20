<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat parameter TMS yang muncul lebih dari sekali untuk satu sample_id.
 * Contoh: Glukosa (id 2) dua kali = gula darah puasa lalu 2 jam PP.
 */
class MqttTmsDuplicateResult extends Model
{
    protected $table = 'tb_mqtt_tms_duplicate_results';

    protected $fillable = [
        'entry_key',
        'sample_id',
        'parameter_id',
        'parameter_name',
        'occurrence',
        'total_occurrence',
        'duplicate_type',
        'distinct_count',
        'gap_minutes',
        'label',
        'received_at',
        'value',
        'tray',
        'pos',
        'log_status',
        'db_slots',
        'db_filled',
        'verdict',
        'scanned_at',
    ];

    protected $casts = [
        'parameter_id' => 'integer',
        'occurrence' => 'integer',
        'total_occurrence' => 'integer',
        'distinct_count' => 'integer',
        'gap_minutes' => 'integer',
        'db_slots' => 'integer',
        'db_filled' => 'integer',
        'received_at' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    /** Nilai, tray, dan pos sama persis: alat mengirim ulang hasil yang sama. */
    public const TYPE_KIRIMAN_ULANG = 'kiriman_ulang';

    /** Nilai atau posisi tabung berbeda: benar-benar dua pemeriksaan. */
    public const TYPE_PEMERIKSAAN_BERBEDA = 'pemeriksaan_berbeda';

    public const VERDICT_OK = 'OK';
    public const VERDICT_SLOT_KURANG = 'SLOT KURANG';
    public const VERDICT_BELUM_MASUK = 'BELUM MASUK';
    public const VERDICT_TERTUMPUK = 'KEMUNGKINAN TERTUMPUK';
    public const VERDICT_TIDAK_ADA_SLOT = 'PARAMETER TIDAK ADA DI ORDER';
    public const VERDICT_TANPA_ORDER = 'ORDER TIDAK ADA';
}
