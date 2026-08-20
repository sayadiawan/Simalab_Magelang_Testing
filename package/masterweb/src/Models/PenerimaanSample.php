<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PenerimaanSample extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_sample_penerimaan";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_sample_penerimaan';
    
    protected $fillable = [
        'id_sample_penerimaan',
        'sample_id',
        'laboratorium_id',
        'penerimaan_sample_date',
        'kelayakan_tempat_kemasan',
        'kelayakan_berat_vol',
        'kondisi_sample',
        'pengawetan_oleh',
        'pengawetan_dengan',
        'penerima_sampel',
        'penerima_tanggal',
        'penerima_signature',
        'penerima_signature_type',
        'disposisi_analis',
        'disposisi_analis_tanggal',
        'disposisi_analis_signature',
        'disposisi_analis_signature_type',
        'disposisi_koordinator_kesmas',
        'disposisi_tanggal',
        'disposisi_signature',
        'disposisi_signature_type',
    ];
     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
   
}