<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk menyimpan Nomer Lab Kesmas per (permohonan_uji, laboratorium).
 *
 * Berbeda dengan klinik yang satu nomor per PermohonanUjiKlinik2, di kesmas
 * setiap lab (Kimia, Mikro) mendapat nomor sendiri ketika SEMUA sampel
 * di lab tersebut dalam satu PermohonanUji sudah selesai (PengesahanHasil).
 */
class CreateNomerLabKesmasTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_nomer_lab_kesmas')) {
            Schema::create('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->string('id', 36)->primary();
                $table->string('permohonan_uji_id', 36)->index();
                $table->string('laboratorium_id', 36)->index();
                $table->unsignedBigInteger('nomer_lab');
                $table->unsignedSmallInteger('year');
                $table->timestamps();

                $table->unique(['permohonan_uji_id', 'laboratorium_id'], 'unique_nomer_lab_kesmas');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tb_nomer_lab_kesmas');
    }
}
