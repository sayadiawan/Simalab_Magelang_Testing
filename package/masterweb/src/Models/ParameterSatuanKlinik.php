<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Traits\Uuid;

class ParameterSatuanKlinik extends Model
{
    use SoftDeletes;
    use Uuid {
        boot as private bootUuidTrait;
    }

    protected $table = "ms_parameter_satuan_klinik";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_parameter_satuan_klinik';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parameter_jenis_klinik',
        'name_parameter_satuan_klinik',
        'metode_parameter_satuan_klinik',
        'metode_parameter_satuan_klinik_haji',
        'ket_default_parameter_satuan_klinik',
        'loinc_parameter_satuan_klinik',
        'loinc_parameter_satuan_klinik_haji',
        'id_parameter_tms',
        'jenis_pemeriksaan_parameter_satuan_klinik',
        'jenis_sampel',
        'jenis_sampel_haji',
        'is_sub_parameter_satuan_klinik',
        'harga_satuan_parameter_satuan_klinik',
        'sort_parameter_satuan_klinik',
        'is_option',
        'requires_nama_jenis',
        'option',
        'number_format',
        'is_haji',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'jenis_sampel' => 'array',
        'jenis_sampel_haji' => 'array',
        'is_option' => 'boolean',
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
                    $blueprint->boolean('requires_nama_jenis')->default(0)->after('is_option');
                });
            }

            self::$schemaHasRequiresNamaJenisColumn = Schema::hasColumn($table, 'requires_nama_jenis');
            return self::$schemaHasRequiresNamaJenisColumn;
        } catch (\Throwable $e) {
            self::$schemaHasRequiresNamaJenisColumn = false;
            return false;
        }
    }

    protected static function boot()
    {
        // Laravel 6: tidak ada booted(); jaga Uuid tetap terpanggil.
        static::bootUuidTrait();

        static::saving(function (self $model) {
            if (!array_key_exists('requires_nama_jenis', $model->getAttributes())) {
                return;
            }

            if (!self::ensureRequiresNamaJenisColumn()) {
                $model->offsetUnset('requires_nama_jenis');
            }
        });
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public function parameterjenisklinik()
    {
        return $this->belongsTo(ParameterJenisKlinik::class, 'parameter_jenis_klinik', 'id_parameter_jenis_klinik')->withDefault();
    }

    public function parametersatuanpaketklinik()
    {
        return $this->hasMany(ParameterSatuanPaketKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
    }

    public function parametersubsatuanklinik()
    {
        return $this->hasMany(ParameterSubSatuanKlinik::class, 'parameter_satuan_klinik', 'id_parameter_satuan_klinik');
    }

    public function BakuMutu()
    {
        return $this->belongsTo(BakuMutu::class, 'parameter_satuan_klinik_id', 'id_parameter_satuan_klinik')->withDefault();
    }

    public function parameterTms()
    {
        return $this->belongsTo(ParameterTms::class, 'id_parameter_tms', 'id_parameter_tms')->withDefault();
    }
}
