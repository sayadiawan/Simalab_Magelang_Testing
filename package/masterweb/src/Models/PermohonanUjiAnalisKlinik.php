<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiAnalisKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_analis_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_analis_klinik';

  protected $fillable = [
    'permohonan_uji_klinik_id',
    'kesimpulan_permohonan_uji_analis_klinik',
    'kategori_permohonan_uji_analis_klinik',
    'saran_permohonan_uji_analis_klinik'
  ];

  public function permohonanujiklinik()
  {
    return $this->belongsTo(PermohonanUjiKlinik::class, 'permohonan_uji_klinik_id', 'id_permohonan_uji_klinik');
  }
}