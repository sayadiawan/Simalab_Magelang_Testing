<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertNilaiBakuMutuToUtf8mb4 extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('tb_baku_mutu', 'nilai_baku_mutu')) {
            DB::statement('ALTER TABLE tb_baku_mutu MODIFY nilai_baku_mutu TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }
        if (Schema::hasColumn('tb_baku_mutu', 'equal')) {
            DB::statement('ALTER TABLE tb_baku_mutu MODIFY equal VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }
        if (Schema::hasColumn('tb_baku_mutu', 'kesimpulan_baku_mutu')) {
            DB::statement('ALTER TABLE tb_baku_mutu MODIFY kesimpulan_baku_mutu TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'baku_mutu_permohonan_uji_parameter_klinik')) {
            DB::statement('ALTER TABLE tb_permohonan_uji_parameter_klinik MODIFY baku_mutu_permohonan_uji_parameter_klinik TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }
    }

    public function down()
    {
        // Tidak di-revert: latin1 tidak mendukung karakter unicode penuh
    }
}
