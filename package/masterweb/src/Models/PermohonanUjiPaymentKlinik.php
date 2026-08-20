<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiPaymentKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_permohonan_uji_payment_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_payment_klinik';

  public function permohonanujiklinik()
  {
    return $this->belongsTo(PermohonanUjiKlinik::class, 'permohonan_uji_klinik_id', 'id_permohonan_uji_klinik');
  }

  public function petugas()
  {
    return $this->belongsTo(User::class, 'nota_petugas_permohonan_uji_payment_klinik', 'id');
  }
}