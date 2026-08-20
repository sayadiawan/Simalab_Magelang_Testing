<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BakuMutu extends Model
{
  use Uuid;
  use SoftDeletes;

  const TIPE_NILAI_KUANTITATIF = 'kuantitatif';
  const TIPE_NILAI_KUALITATIF = 'kualitatif';
  const TIPE_NILAI_ALLOWED = [
    self::TIPE_NILAI_KUANTITATIF,
    self::TIPE_NILAI_KUALITATIF,
  ];

  public $incrementing = false;
  protected $table = "tb_baku_mutu";

  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_baku_mutu';
  protected $casts = [
    'tipe_nilai_baku_mutu' => 'string',
  ];

  public function library()
  {
    return $this->belongsTo(Library::class, 'library_id', 'id_library')->withDefault();
  }

  public function laboratorium()
  {
    return $this->belongsTo(Laboratorium::class, 'lab_id', 'id_laboratorium')->withDefault();
  }

  public function parameterjenisklinik()
  {
    return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik_id', 'id_parameter_jenis_klinik')->withDefault();
  }

  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik_id', 'id_parameter_satuan_klinik')->withDefault();
  }

  public function bakumutudetailparameterklinik()
  {
    return $this->hasMany(BakuMutuDetailParameterKlinik::class, 'baku_mutu_id', 'id_baku_mutu');
  }


  public function unit()
  {
    return $this->hasOne(Unit::class, 'id_unit', 'unit_id');
  }

  public function jenismakanan()
  {
    return $this->belongsTo(JenisMakanan::class, 'jenis_makanan_id', 'id_jenis_makanan')->withDefault();
  }

  public function method()
  {
    return $this->belongsTo(Method::class, 'method_id', 'id_method')->withDefault();
  }
}