<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConvertTbPermohonanUjiKlinik2ToUtf8mb4 extends Migration
{
    /**
     * Kolom-kolom yang menerima konten bebas dari user (TinyMCE, input form, JSON, dll.)
     * dan perlu mendukung karakter Unicode (≥, ≤, °, dll.).
     *
     * Catatan: kolom FK (pasien_permohonan_uji_klinik) TIDAK diubah agar FK tetap valid.
     */
    protected $textColumns = [
        ['name' => 'catatan_hasil',                           'type' => 'TEXT'],
        ['name' => 'kesimpulan_hasil',                        'type' => 'TEXT'],
        ['name' => 'diagnosa_permohonan_uji_klinik',          'type' => 'LONGTEXT'],
        ['name' => 'kondisi_pasien',                          'type' => 'TEXT'],
        ['name' => 'kualitas_sampel',                         'type' => 'TEXT'],
        ['name' => 'request_pasien_permohonan_uji_klinik',    'type' => 'TEXT'],
        ['name' => 'alamat_perwakilan_permohonan_uji_klinik', 'type' => 'TEXT'],
        ['name' => 'encounter_json_satu_sehat',               'type' => 'TEXT'],
        ['name' => 'id_spesimen',                             'type' => 'TEXT'],
        ['name' => 'blob_document',                           'type' => 'LONGTEXT'],
        ['name' => 'penerimaan_sampel',                       'type' => 'TEXT'],
    ];

    public function up()
    {
        foreach ($this->textColumns as $col) {
            DB::statement(sprintf(
                'ALTER TABLE `tb_permohonan_uji_klinik_2` MODIFY COLUMN `%s` %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL',
                $col['name'],
                $col['type']
            ));
        }
    }

    public function down()
    {
        foreach ($this->textColumns as $col) {
            DB::statement(sprintf(
                'ALTER TABLE `tb_permohonan_uji_klinik_2` MODIFY COLUMN `%s` %s CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL',
                $col['name'],
                $col['type']
            ));
        }
    }
}
