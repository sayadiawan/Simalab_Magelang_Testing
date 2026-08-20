<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Traits\Uuid;

class SampleDraft extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_draft";
    protected $dates = ['deleted_at', 'confirmed_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_draft';

    protected $fillable = [
        'id_sample_draft',
        'permohonan_uji_id',
        'draft_group_id',
        'typesample_samples',
        'codesample_samples',
        'nomor_spesimen_manual',
        'nomor_spesimen_mikro_manual',
        'nomor_lab_kimia_manual',
        'nomor_lab_mikro_manual',
        'is_nomor_sampel_manual',
        'name_pelanggan',
        'datesampling_samples',
        'date_sending',
        'titik_pengambilan',
        'cost_samples',
        'note_samples',
        'pengambil_sampel',
        'count_id',
        'program_samples',
        'packet_id',
        'name_send_sample',
        'code_sample_customer',
        'is_sampling',
        'cost_sampling_samples',
        'method_data',
        'status',
        'confirmed_by',
        'confirmed_at',
        'created_by',
    ];

    /** @var array<string, bool>|null */
    protected static $schemaHasKesmasNomorColumns;

    protected static function booted()
    {
        static::creating(function (SampleDraft $model) {
            $table = $model->getTable();
            if (self::$schemaHasKesmasNomorColumns === null) {
                self::$schemaHasKesmasNomorColumns = [
                    'nomor_spesimen_manual' => Schema::hasColumn($table, 'nomor_spesimen_manual'),
                    'nomor_spesimen_mikro_manual' => Schema::hasColumn($table, 'nomor_spesimen_mikro_manual'),
                    'nomor_lab_kimia_manual' => Schema::hasColumn($table, 'nomor_lab_kimia_manual'),
                    'nomor_lab_mikro_manual' => Schema::hasColumn($table, 'nomor_lab_mikro_manual'),
                    'is_nomor_sampel_manual' => Schema::hasColumn($table, 'is_nomor_sampel_manual'),
                ];
            }
            foreach (self::$schemaHasKesmasNomorColumns as $column => $exists) {
                if (!$exists && array_key_exists($column, $model->getAttributes())) {
                    $model->offsetUnset($column);
                }
            }
        });
    }

    protected $casts = [
        'is_nomor_sampel_manual' => 'boolean',
        'method_data' => 'array',
        'datesampling_samples' => 'datetime',
        'date_sending' => 'datetime',
        'confirmed_at' => 'datetime',
        'cost_samples' => 'decimal:2',
        'cost_sampling_samples' => 'decimal:2',
    ];

    public function permohonanuji()
    {
        return $this->belongsTo(PermohonanUji::class, 'permohonan_uji_id', 'id_permohonan_uji');
    }

    public function sampletype()
    {
        return $this->belongsTo(SampleType::class, 'typesample_samples', 'id_sample_type')->where('deleted_at', NULL);
    }

    public function packet()
    {
        return $this->belongsTo(Packet::class, 'packet_id', 'id_packet')->where('deleted_at', NULL);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_samples', 'id_program')->where('deleted_at', NULL);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }

    public function samplemethoddraft()
    {
        return $this->hasMany(SampleMethodDraft::class, 'sample_draft_id', 'id_sample_draft');
    }

    /**
     * Get sibling drafts with the same draft_group_id
     * These are drafts created in the same input session
     */
    public function siblingDrafts()
    {
        return $this->hasMany(SampleDraft::class, 'draft_group_id', 'draft_group_id')
                    ->where('id_sample_draft', '!=', $this->id_sample_draft);
    }

    /**
     * Get all drafts in the same group (including self)
     */
    public function groupDrafts()
    {
        return SampleDraft::where('draft_group_id', $this->draft_group_id)->get();
    }
}

