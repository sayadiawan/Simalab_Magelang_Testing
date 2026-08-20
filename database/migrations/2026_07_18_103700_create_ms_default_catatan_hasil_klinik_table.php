<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsDefaultCatatanHasilKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_default_catatan_hasil_klinik', function (Blueprint $table) {
            $table->char('id_default_catatan_hasil_klinik', 36);
            $table->char('parameter_satuan_klinik', 36);
            $table->longText('catatan_default');
            $table->boolean('is_active')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_default_catatan_hasil_klinik', 'pk_default_catatan_hasil');
            $table->index('parameter_satuan_klinik', 'idx_default_catatan_param');
            $table->index(['is_active', 'deleted_at'], 'idx_default_catatan_active');
        });

        if (Schema::hasTable('tb_permohonan_uji_klinik_2')
            && !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'catatan_hasil_from_master')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->boolean('catatan_hasil_from_master')->default(0)->after('catatan_hasil');
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
        if (Schema::hasTable('tb_permohonan_uji_klinik_2')
            && Schema::hasColumn('tb_permohonan_uji_klinik_2', 'catatan_hasil_from_master')) {
            Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
                $table->dropColumn('catatan_hasil_from_master');
            });
        }

        Schema::dropIfExists('ms_default_catatan_hasil_klinik');
    }
}
