<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Traits\Uuid;

class PermohonanUjiParameterKlinik extends Model
{
  use SoftDeletes;
  use Uuid {
    boot as private bootUuidTrait;
  }

  protected $table = "tb_permohonan_uji_parameter_klinik";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_permohonan_uji_parameter_klinik';

  protected $casts = [
    'requires_nama_jenis' => 'boolean',
  ];

  /** @var bool|null Cache Schema::hasColumn per proses (hindari error jika migrasi belum dijalankan) */
  protected static $schemaHasRequiresNamaJenisColumn;

  public static function hasRequiresNamaJenisColumn(): bool
  {
    if (self::$schemaHasRequiresNamaJenisColumn === null) {
      try {
        self::$schemaHasRequiresNamaJenisColumn = Schema::hasColumn(
          (new self())->getTable(),
          'requires_nama_jenis'
        );
      } catch (\Throwable $e) {
        self::$schemaHasRequiresNamaJenisColumn = false;
      }
    }

    return (bool) self::$schemaHasRequiresNamaJenisColumn;
  }

  /**
   * Pastikan kolom requires_nama_jenis ada (fallback jika migrasi belum dijalankan).
   */
  public static function ensureRequiresNamaJenisColumn(): bool
  {
    if (self::hasRequiresNamaJenisColumn()) {
      return true;
    }

    try {
      $table = (new self())->getTable();
      if (!Schema::hasTable($table)) {
        return false;
      }

      if (!Schema::hasColumn($table, 'requires_nama_jenis')) {
        Schema::table($table, function ($blueprint) {
          $blueprint->boolean('requires_nama_jenis')->default(0)->after('parameter_satuan_klinik');
        });
      }

      self::$schemaHasRequiresNamaJenisColumn = true;
      return true;
    } catch (\Throwable $e) {
      self::$schemaHasRequiresNamaJenisColumn = false;
      return false;
    }
  }

  protected static function boot()
  {
    // Laravel 6: tidak ada booted(); jaga Uuid tetap terpanggil.
    static::bootUuidTrait();

    static::creating(function (self $model) {
      if (!self::ensureRequiresNamaJenisColumn()) {
        $model->offsetUnset('requires_nama_jenis');
        return;
      }
      if ($model->requires_nama_jenis !== null) {
        return;
      }
      if (empty($model->parameter_satuan_klinik)) {
        $model->requires_nama_jenis = 0;
        return;
      }

      $master = ParameterSatuanKlinik::query()
        ->where('id_parameter_satuan_klinik', $model->parameter_satuan_klinik)
        ->first();

      $model->requires_nama_jenis = $master ? (int) ($master->requires_nama_jenis ?? 0) : 0;

      // Salin mapping TMS dari master jika belum diisi
      if ($master && empty($model->id_parameter_tms) && !empty($master->id_parameter_tms)) {
        $model->id_parameter_tms = $master->id_parameter_tms;
      }
    });

    static::saving(function (self $model) {
      if (!array_key_exists('requires_nama_jenis', $model->getAttributes())) {
        return;
      }

      if (!self::ensureRequiresNamaJenisColumn()) {
        $model->offsetUnset('requires_nama_jenis');
      }
    });
  }

  public function permohonanujiklinik()
  {
    return $this->belongsTo(PermohonanUjiKlinik2::class, 'permohonan_uji_klinik', 'id_permohonan_uji_klinik')->withDefault();
  }

  public function permohonanujipaketklinik()
  {
    return $this->belongsTo(PermohonanUjiPaketKlinik::class, 'permohonan_uji_paket_klinik', 'id_permohonan_uji_paket_klinik')->withDefault();
  }

  public function permohonanujijenispaketklinik()
  {
    return $this->belongsTo(ParameterPaketJenisKlinik::class, 'parameter_paket_jenis_klinik', 'id_parameter_paket_jenis_klinik')->withDefault();
  }

  public function jenisparameterklinik()
  {
    return $this->belongsTo(ParameterJenisKlinik::class, 'jenis_parameter_klinik_id', 'id_parameter_jenis_klinik')->withDefault();
  }

  public function parameterpaketklinik()
  {
    return $this->belongsTo(ParameterPaketKlinik::class, 'parameter_paket_klinik', 'id_parameter_paket_klinik');
  }


  public function parametersatuanklinik()
  {
    return $this->belongsTo(ParameterSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
  }

  public function parameterTms()
  {
    return $this->belongsTo(ParameterTms::class, 'id_parameter_tms', 'id_parameter_tms')->withDefault();
  }

  public function orderDetailsTms()
  {
    return $this->hasMany(
      OrderDetailTms::class,
      'id_permohonan_uji_parameter_klinik',
      'id_permohonan_uji_parameter_klinik'
    );
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'satuan_permohonan_uji_parameter_klinik', 'id_unit')->withDefault();
  }

  public function bakumutu()
  {
    return $this->belongsTo(BakuMutu::class, 'baku_mutu_permohonan_uji_parameter_klinik', 'id_baku_mutu')->withDefault();
  }

  public function permohonanujisubparameterklinik()
  {
    return $this->hasMany(PermohonanUjiSubParameterKlinik::class, 'permohonan_uji_parameter_klinik_id', 'id_permohonan_uji_parameter_klinik');
  }

  public function history()
  {
    return $this->hasMany(PermohonanUjiParameterKlinikHistory::class, 'permohonan_uji_parameter_klinik_id', 'id_permohonan_uji_parameter_klinik')->orderBy('created_at', 'desc');
  }

  public function selectedHistory()
  {
    return $this->belongsTo(PermohonanUjiParameterKlinikHistory::class, 'selected_history_id', 'id_permohonan_uji_parameter_klinik_history')->withDefault();
  }
}