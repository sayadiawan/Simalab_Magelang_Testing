<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class DefaultCatatanHasilKlinik extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'ms_default_catatan_hasil_klinik';
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_default_catatan_hasil_klinik';

    protected $fillable = [
        'parameter_satuan_klinik',
        'catatan_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parameterSatuanKlinik()
    {
        return $this->belongsTo(
            ParameterSatuanKlinik::class,
            'parameter_satuan_klinik',
            'id_parameter_satuan_klinik'
        )->withDefault();
    }
}
