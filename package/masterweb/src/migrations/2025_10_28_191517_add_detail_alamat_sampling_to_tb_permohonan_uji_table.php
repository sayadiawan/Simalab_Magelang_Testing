<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailAlamatSamplingToTbPermohonanUjiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji', 'detail_alamat_sampling')) {
            $table->text('detail_alamat_sampling')->nullable()->after('alamat_lengkap_sampling')->comment('Detail alamat sampling (jalan, nomor, RT/RW, patokan)');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            if (Schema::hasColumn('tb_permohonan_uji', 'detail_alamat_sampling')) {
            $table->dropColumn('detail_alamat_sampling');
            }
        });
    }
}
