<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomerLabToKlinikAndKesmas extends Migration
{
    public function up()
    {
        // Nomer Lab untuk klinik (tb_permohonan_uji_klinik_2)
        if (Schema::hasTable('tb_permohonan_uji_klinik_2') && !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->unsignedBigInteger('nomer_lab')->nullable()->after('nourut_permohonan_uji_klinik');
            });
        }

        // Nomer Lab untuk kesmas (tb_permohonan_uji)
        if (Schema::hasTable('tb_permohonan_uji') && !Schema::hasColumn('tb_permohonan_uji', 'nomer_lab')) {
            Schema::table('tb_permohonan_uji', function (Blueprint $table) {
                $table->unsignedBigInteger('nomer_lab')->nullable()->after('urutan_permohonan_uji');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tb_permohonan_uji_klinik_2') && Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->dropColumn('nomer_lab');
            });
        }

        if (Schema::hasTable('tb_permohonan_uji') && Schema::hasColumn('tb_permohonan_uji', 'nomer_lab')) {
            Schema::table('tb_permohonan_uji', function (Blueprint $table) {
                $table->dropColumn('nomer_lab');
            });
        }
    }
}
