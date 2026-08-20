<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class ParameterPaketKlinik extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_parameter_paket_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_parameter_paket_klinik';

  protected $fillable = [
    'id_parameter_paket_klinik',
    'name_parameter_paket_klinik',
    'singkatan_laporan',
    'is_agregat_laporan',
    'kategori_laporan',
    'tampil_di_laporan',
    'harga_parameter_paket_klinik',
    'sort',
  ];

  protected $casts = [
    'is_agregat_laporan' => 'boolean',
    'tampil_di_laporan' => 'boolean',
  ];

  // public function parameterjenisklinik()
  // {
  //     return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik', 'id_parameter_jenis_klinik');
  // }

  public function parameterpaketjenisklinik()
  {
    return $this->hasMany(ParameterPaketJenisKlinik::class, 'parameter_paket_klinik_id', 'id_parameter_paket_klinik')->orderBy('sort', 'asc');
  }

  public function parameterSubPaketExtra()
  {
      return $this->hasMany(ParameterSubPaketExtra::class, 'id_parameter_paket_klinik', 'id_parameter_paket_klinik');
  }

  // public function parametersatuanpaketklinik()
  // {
  //     return $this->hasMany(ParameterSatuanPaketKlinik::class, 'parameter_paket_klinik', 'id_parameter_paket_klinik');
  // }
}
