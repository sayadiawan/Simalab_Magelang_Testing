<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class SatuSehatPractitioner extends Model
{
    protected $table = "ms_satusehat_practitioner";
    use SoftDeletes;
    use Uuid;

    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_satu_sehat_practitioner';

    protected $fillable = [
        'name_petugas',
        'name_satu_sehat_practitioner',
        'code_satu_sehat_practitioner',
        'type'
    ];

    /**
     * Relasi ke Petugas berdasarkan code_satu_sehat_practitioner
     */
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'code_satu_sehat_practitioner', 'code_satu_sehat_practitioner');
    }

    /**
     * Mencari Petugas berdasarkan nama dengan metode pencarian yang sama
     * Metode: normalize nama (hilangkan koma) dan cari dengan LIKE
     * 
     * @return Petugas|null
     */
    public function findPetugasByName()
    {
        if (empty($this->name_petugas) && empty($this->name_satu_sehat_practitioner)) {
            return null;
        }

        // Gunakan name_satu_sehat_practitioner jika ada, fallback ke name_petugas
        $nama = $this->name_satu_sehat_practitioner ?? $this->name_petugas;
        
        // Normalize nama: hilangkan koma dan multiple spaces
        $nama_normalized = str_replace(',', '', $nama);
        $nama_normalized = preg_replace('/\s+/', ' ', $nama_normalized);
        $nama_normalized = trim($nama_normalized);
        
        // Cari di Petugas dengan metode pencarian yang sama
        $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $nama_normalized . '%'])->first();

        // Jika tidak ditemukan dengan nama, coba dengan code_satu_sehat_practitioner jika ada
        if (!$petugas && !empty($this->code_satu_sehat_practitioner)) {
            $petugas = Petugas::where('code_satu_sehat_practitioner', $this->code_satu_sehat_practitioner)->first();
        }

        return $petugas;
    }
}