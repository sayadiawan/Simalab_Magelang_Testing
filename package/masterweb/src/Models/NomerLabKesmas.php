<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model untuk Nomer Lab Kesmas per (PermohonanUji, Laboratorium).
 *
 * Setiap lab (Kimia, Mikro) mendapat nomor sendiri ketika semua sampel
 * di lab tersebut dalam satu PermohonanUji sudah selesai.
 *
 * Tabel: tb_nomer_lab_kesmas
 */
class NomerLabKesmas extends Model
{
    use SoftDeletes;

    protected $table      = 'tb_nomer_lab_kesmas';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $dates      = ['deleted_at'];

    protected $fillable = [
        'id',
        'permohonan_uji_id',
        'laboratorium_id',
        'sample_type_id',
        'nomer_lab',
        'year',
    ];

    public function permohonanUji()
    {
        return $this->belongsTo(PermohonanUji::class, 'permohonan_uji_id', 'id_permohonan_uji');
    }

    public function laboratorium()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium_id', 'id_laboratorium');
    }
}
