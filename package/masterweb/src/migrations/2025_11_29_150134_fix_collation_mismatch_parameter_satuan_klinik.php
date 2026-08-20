<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCollationMismatchParameterSatuanKlinik extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix collation untuk kolom parameter_satuan_klinik di tb_permohonan_uji_parameter_klinik
        // Pastikan menggunakan utf8mb4_unicode_ci untuk konsistensi
        DB::statement("ALTER TABLE `tb_permohonan_uji_parameter_klinik` 
            MODIFY COLUMN `parameter_satuan_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");

        // Fix collation untuk kolom id_parameter_satuan_klinik di ms_parameter_satuan_klinik
        // Pastikan menggunakan utf8mb4_unicode_ci untuk konsistensi
        DB::statement("ALTER TABLE `ms_parameter_satuan_klinik` 
            MODIFY COLUMN `id_parameter_satuan_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");

        // Fix collation untuk seluruh tabel ms_parameter_satuan_klinik
        DB::statement("ALTER TABLE `ms_parameter_satuan_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Fix collation untuk seluruh tabel tb_permohonan_uji_parameter_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_parameter_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Fix collation untuk tabel terkait yang juga terlibat dalam join
        // tb_permohonan_uji_paket_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_paket_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // tb_permohonan_uji_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Pastikan kolom foreign key juga menggunakan collation yang sama
        // Fix kolom permohonan_uji_klinik di tb_permohonan_uji_paket_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_paket_klinik` 
            MODIFY COLUMN `permohonan_uji_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");

        // Fix kolom id_permohonan_uji_klinik di tb_permohonan_uji_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_klinik` 
            MODIFY COLUMN `id_permohonan_uji_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");

        // Fix kolom id_permohonan_uji_paket_klinik di tb_permohonan_uji_paket_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_paket_klinik` 
            MODIFY COLUMN `id_permohonan_uji_paket_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");

        // Fix kolom permohonan_uji_paket_klinik di tb_permohonan_uji_parameter_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_parameter_klinik` 
            MODIFY COLUMN `permohonan_uji_paket_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");

        // Fix ms_parameter_paket_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Fix kolom id_parameter_paket_klinik di ms_parameter_paket_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_klinik` 
            MODIFY COLUMN `id_parameter_paket_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");

        // Fix kolom parameter_paket_klinik di tb_permohonan_uji_paket_klinik
        DB::statement("ALTER TABLE `tb_permohonan_uji_paket_klinik` 
            MODIFY COLUMN `parameter_paket_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: Reverting collation changes can be risky if data contains special characters
        // This down method is intentionally left minimal to prevent data loss
        // If you need to revert, manually check and adjust collations as needed
    }
}