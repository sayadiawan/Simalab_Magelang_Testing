<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganMetodeToTbPermohonanUjiKlinik2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'keterangan_metode')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->text('keterangan_metode')->nullable()->after('kesimpulan_hasil');
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
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'keterangan_metode')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->dropColumn('keterangan_metode');
            });
        }
    }
}
