<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiPaketKlinik2 extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_paket_klinik2";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_paket_klinik';

  protected $fillable = [
    'id_permohonan_uji_klinik',
    'id_jenis_parameter_klinik',
    'total_harga'
  ];
}
