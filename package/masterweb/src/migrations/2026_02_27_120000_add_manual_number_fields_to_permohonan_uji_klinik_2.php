<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualNumberFieldsToPermohonanUjiKlinik2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                // Kolom untuk input manual nomor laboratorium
                if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomor_lab_manual')) {
                    $table->string('nomor_lab_manual', 50)->nullable()->after('nomer_lab')->comment('Nomor laboratorium input manual');
                }
                
                // Kolom untuk input manual nomor spesimen
                if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomor_spesimen_manual')) {
                    $table->string('nomor_spesimen_manual', 50)->nullable()->after('noregister_permohonan_uji_klinik')->comment('Nomor spesimen input manual');
                }
                
                // Flag untuk menentukan apakah nomor lab manual atau otomatis
                if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'is_nomor_lab_manual')) {
                    $table->boolean('is_nomor_lab_manual')->default(false)->after('nomor_lab_manual')->comment('Flag: true = manual, false = otomatis');
                }
                
                // Flag untuk menentukan apakah nomor spesimen manual atau otomatis
                if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'is_nomor_spesimen_manual')) {
                    $table->boolean('is_nomor_spesimen_manual')->default(false)->after('nomor_spesimen_manual')->comment('Flag: true = manual, false = otomatis');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'is_nomor_spesimen_manual')) {
                    $table->dropColumn('is_nomor_spesimen_manual');
                }
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'is_nomor_lab_manual')) {
                    $table->dropColumn('is_nomor_lab_manual');
                }
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomor_spesimen_manual')) {
                    $table->dropColumn('nomor_spesimen_manual');
                }
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomor_lab_manual')) {
                    $table->dropColumn('nomor_lab_manual');
                }
            });
        }
    }
}
