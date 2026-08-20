<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsKlinikNumberSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_klinik_number_settings')) {
            Schema::create('ms_klinik_number_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->boolean('is_nomor_lab_manual')->default(false)->comment('Setting global: true = manual input, false = otomatis');
                $table->boolean('is_nomor_spesimen_manual')->default(false)->comment('Setting global: true = manual input, false = otomatis');
                $table->text('description')->nullable()->comment('Keterangan setting');
                $table->timestamps();
            });

            // Insert default setting (otomatis)
            DB::table('ms_klinik_number_settings')->insert([
                'is_nomor_lab_manual' => false,
                'is_nomor_spesimen_manual' => false,
                'description' => 'Setting default: Nomor lab dan spesimen otomatis',
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
        Schema::dropIfExists('ms_klinik_number_settings');
    }
}
