<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Smt\Masterweb\Traits\Uuid;

class Petugas extends Model
{
    use Uuid;

    protected $table = 'ms_petugas';
    protected $primaryKey = 'id_petugas';
    public $timestamps = false;
    
    protected $fillable = [
        'nama',
        'nik',
        'nip',
        'gelar',
        'gelar',
        'lab_id',
        'code_satu_sehat_practitioner',
        'role',
        'is_kepala_lab',
        'pran_per_lab'
    ];

    protected $casts = [
        'role' => 'array',
        'lab_id' => 'array',
        'pran_per_lab' => 'array',
    ];

    /**
     * Relasi ke SatuSehatPractitioner berdasarkan code_satu_sehat_practitioner
     */
    public function satuSehatPractitioner()
    {
        return $this->hasOne(SatuSehatPractitioner::class, 'code_satu_sehat_practitioner', 'code_satu_sehat_practitioner');
    }

    /**
     * Mencari SatuSehatPractitioner berdasarkan nama petugas dengan metode pencarian yang sama
     * Metode: normalize nama (hilangkan koma) dan cari dengan LIKE
     * 
     * @return SatuSehatPractitioner|null
     */
    public function findSatuSehatPractitionerByName()
    {
        if (empty($this->nama)) {
            return null;
        }

        // Normalize nama: hilangkan koma
        $namaPetugas_normalized = str_replace(',', '', $this->nama);
        
        // Cari di SatuSehatPractitioner dengan metode pencarian yang sama
        $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
            ->orWhereRaw("REPLACE(name_satu_sehat_practitioner, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
            ->first();

        // Jika tidak ditemukan dengan nama, coba dengan code_satu_sehat_practitioner jika ada
        if (!$practitioner && !empty($this->code_satu_sehat_practitioner)) {
            $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $this->code_satu_sehat_practitioner)->first();
        }

        return $practitioner;
    }

    /**
     * Get SatuSehatPractitioner dengan fallback: cari berdasarkan code, lalu nama
     * 
     * @return SatuSehatPractitioner|null
     */
    public function getSatuSehatPractitioner()
    {
        // Prioritas 1: Cari berdasarkan code_satu_sehat_practitioner jika ada
        if (!empty($this->code_satu_sehat_practitioner)) {
            $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $this->code_satu_sehat_practitioner)->first();
            if ($practitioner) {
                return $practitioner;
            }
        }

        // Prioritas 2: Cari berdasarkan nama dengan metode pencarian yang sama
        return $this->findSatuSehatPractitionerByName();
    }
}