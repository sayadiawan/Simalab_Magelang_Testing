<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMsKesmasSampleNumberSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_kesmas_sample_number_settings')) {
            Schema::create('ms_kesmas_sample_number_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->boolean('is_nomor_sampel_manual')->default(false)
                    ->comment('true = kode/nomor sampel diisi manual di form tambah sampel kesmas');
                $table->boolean('is_nomor_laboratorium_manual')->default(false)
                    ->comment('true = nomor laboratorium diisi manual (Kimia/Mikro) + tb_nomer_lab_kesmas awal');
                $table->text('description')->nullable();
                $table->timestamps();
            });

            DB::table('ms_kesmas_sample_number_settings')->insert([
                'is_nomor_sampel_manual' => false,
                'is_nomor_laboratorium_manual' => false,
                'description' => 'Default: nomor sampel & laboratorium otomatis (urutan global)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_kesmas_sample_number_settings');
    }
}
