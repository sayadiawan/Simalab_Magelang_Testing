<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class OrderDetailTms extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'tb_orderdetail_tms';
    protected $primaryKey = 'id_orderdetail_tms';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_orderdetail_tms',
        'id_order_tms',
        'id_parameter_tms',
        'id_permohonan_uji_parameter_klinik',
        'value',
    ];

    public function order()
    {
        return $this->belongsTo(OrderTms::class, 'id_order_tms', 'id_order_tms');
    }

    public function parameterTms()
    {
        return $this->belongsTo(ParameterTms::class, 'id_parameter_tms', 'id_parameter_tms');
    }

    public function permohonanUjiParameterKlinik()
    {
        return $this->belongsTo(
            PermohonanUjiParameterKlinik::class,
            'id_permohonan_uji_parameter_klinik',
            'id_permohonan_uji_parameter_klinik'
        );
    }
}
