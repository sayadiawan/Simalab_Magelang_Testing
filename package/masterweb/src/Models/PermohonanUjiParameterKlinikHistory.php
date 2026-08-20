<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiParameterKlinikHistory extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_permohonan_uji_parameter_klinik_history";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_permohonan_uji_parameter_klinik_history';

    protected $fillable = [
        'permohonan_uji_parameter_klinik_id',
        'hasil_permohonan_uji_parameter_klinik',
        'created_by'
    ];

    public function permohonanUjiParameterKlinik()
    {
        return $this->belongsTo(PermohonanUjiParameterKlinik::class, 'permohonan_uji_parameter_klinik_id', 'id_permohonan_uji_parameter_klinik');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withDefault();
    }
}

