<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PengarsipanDokumen extends Model
{
    use Uuid;
    use SoftDeletes;

    protected $table = 'tb_pengarsipan_dokumen';
    public $incrementing = false;
    protected $primaryKey = 'id_pengarsipan_dokumen';

    protected $fillable = [
        'nomor_arsip',
        'bidang',
        'judul',
        'keterangan',
        'ref_bidang',
        'ref_id',
        'ref_label',
        'file_path',
        'file_name_original',
        'file_mime',
        'file_size',
        'tahun',
        'uploaded_by',
        'uploaded_by_name',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id')->withDefault();
    }

    public function getBidangLabelAttribute()
    {
        $map = [
            'klinik' => 'Klinik',
            'kesmas' => 'Kesmas',
            'umum' => 'Umum',
        ];

        return $map[$this->bidang] ?? strtoupper((string) $this->bidang);
    }

    public function getFileSizeHumanAttribute()
    {
        $bytes = (int) $this->file_size;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
