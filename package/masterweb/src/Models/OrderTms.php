<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class OrderTms extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'tb_order_tms';
    protected $primaryKey = 'id_order_tms';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_order_tms',
        'id_permohonan_uji_klinik',
        'nama_pasien',
        'tanggal_lahir',
        'jenis_kelamin',
        'jenis_sampel',
        'kode_barcode',
        'tray',
        'pos',
        'is_executed',
        'executed_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_executed' => 'boolean',
        'executed_at' => 'datetime',
    ];

    public function permohonanUjiKlinik()
    {
        return $this->belongsTo(PermohonanUjiKlinik2::class, 'id_permohonan_uji_klinik', 'id_permohonan_uji_klinik');
    }

    public function details()
    {
        return $this->hasMany(OrderDetailTms::class, 'id_order_tms', 'id_order_tms');
    }

    public function markExecuted()
    {
        $this->is_executed = true;
        $this->executed_at = $this->executed_at ?: now();
        return $this->save();
    }

    public function markNotExecuted()
    {
        $this->is_executed = false;
        $this->executed_at = null;
        return $this->save();
    }
}
