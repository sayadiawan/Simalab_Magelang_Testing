<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSamplingToTbPermohonanUjiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji', 'is_sampling')) {
            $table->tinyInteger('is_sampling')->default(1)->after('petugas_penerima')->comment('1=Laboratorium, 0=Pelanggan');
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
            if (Schema::hasColumn('tb_permohonan_uji', 'is_sampling')) {
            $table->dropColumn('is_sampling');
            }
        });
    }
}
