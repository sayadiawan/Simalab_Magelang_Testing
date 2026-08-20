<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class NumberKlinik extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_number_klinik";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_number_klinik';

    protected $fillable = [
        'id_number_klinik',
        'new_number',
        'last_number',
        'id_permohonan_uji_klinik',
        'id_prolanis',
        'id_haji',
        'id_prolanis_gula',
        'id_prolanis_urine',
    ];
}
