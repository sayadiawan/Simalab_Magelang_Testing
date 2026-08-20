<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterTms extends Model
{
    use SoftDeletes;

    protected $table = 'ms_parameter_tms';
    protected $primaryKey = 'id_parameter_tms';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_parameter_tms',
        'name_parameter_tms',
        'jenis_sampel',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetailTms::class, 'id_parameter_tms', 'id_parameter_tms');
    }

    public function permohonanUjiParameterKlinik()
    {
        return $this->hasMany(PermohonanUjiParameterKlinik::class, 'id_parameter_tms', 'id_parameter_tms');
    }

    public function parameterSatuanKlinik()
    {
        return $this->hasMany(ParameterSatuanKlinik::class, 'id_parameter_tms', 'id_parameter_tms');
    }
}
