<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCollationMismatchParameterPaketJenisKlinik extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix collation untuk tabel ms_parameter_paket_jenis_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_jenis_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Fix collation untuk kolom parameter_jenis_klinik_id di ms_parameter_paket_jenis_klinik
        // Pastikan menggunakan utf8mb4_unicode_ci untuk konsistensi dengan ms_parameter_jenis_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_jenis_klinik` 
            MODIFY COLUMN `parameter_jenis_klinik_id` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");

        // Fix collation untuk kolom parameter_paket_klinik_id di ms_parameter_paket_jenis_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_jenis_klinik` 
            MODIFY COLUMN `parameter_paket_klinik_id` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NULL");

        // Fix collation untuk kolom id_parameter_paket_jenis_klinik di ms_parameter_paket_jenis_klinik
        DB::statement("ALTER TABLE `ms_parameter_paket_jenis_klinik` 
            MODIFY COLUMN `id_parameter_paket_jenis_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");

        // Pastikan juga ms_parameter_jenis_klinik menggunakan collation yang sama
        DB::statement("ALTER TABLE `ms_parameter_jenis_klinik` 
            CONVERT TO CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci");

        // Fix collation untuk kolom id_parameter_jenis_klinik di ms_parameter_jenis_klinik
        DB::statement("ALTER TABLE `ms_parameter_jenis_klinik` 
            MODIFY COLUMN `id_parameter_jenis_klinik` CHAR(36) 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL");
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