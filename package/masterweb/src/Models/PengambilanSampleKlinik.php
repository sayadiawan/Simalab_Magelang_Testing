<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PengambilanSampleKlinik extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'tb_pengambilan_sample_klinik';
    protected $primaryKey = 'id_pengambilan_sample_klinik';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_pengambilan_sample_klinik',
        'permohonan_uji_klinik_id',
        'jenis_sample',
        'volume_sample',
        'time_sampling',
        'status_sampling',
        'signature_pengambil_sample_petugas',
        'signature_pengambil_sample_pasien',
        'tindakan_medis_khusus',
        'id_spesimen_satu_sehat',
        'id_service_request_satu_sehat',
        'pasien_permohonan_uji_klinik',
        'kondisi_pasien',
        'resampling',
        'resample_reason',
        'petugas_name',
        'petugas_id',
        'number_sampling_success',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Calculate number_sampling_success for a given permohonan_uji_klinik_id
     * Returns the count of successful samplings
     */
    public static function calculateNumberSamplingSuccess($permohonan_uji_klinik_id)
    {
        return self::where('permohonan_uji_klinik_id', $permohonan_uji_klinik_id)
            ->where('status_sampling', 'berhasil')
            ->whereNull('deleted_at')
            ->count();
    }

    // Relations
    public function permohonanUjiKlinik()
    {
        return $this->belongsTo(PermohonanUjiKlinik2::class, 'permohonan_uji_klinik_id', 'id_permohonan_uji_klinik');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_permohonan_uji_klinik', 'id_pasien')->withDefault();
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas')->withDefault();
    }
}