<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermohonanUjiParameterKlinikHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop table if exists (from previous failed migration)
        if (!Schema::hasTable('tb_permohonan_uji_parameter_klinik_history')) {
            Schema::create('tb_permohonan_uji_parameter_klinik_history', function (Blueprint $table) {
                $table->uuid('id_permohonan_uji_parameter_klinik_history')->primary('pk_puk_history_id');
                $table->uuid('permohonan_uji_parameter_klinik_id');
                $table->text('hasil_permohonan_uji_parameter_klinik')->nullable();
                $table->text('keterangan_permohonan_uji_parameter_klinik')->nullable();
                $table->string('method_permohonan_uji_parameter_klinik')->nullable();
                $table->string('offset_baku_mutu', 20)->default('default')->nullable();
                $table->uuid('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
    
                // Add index on foreign key column
                $table->index('permohonan_uji_parameter_klinik_id', 'idx_history_parameter_klinik_id');
            });
            // Add foreign key constraint using DB::statement for better compatibility
            try {
                DB::statement('ALTER TABLE `tb_permohonan_uji_parameter_klinik_history` 
                    ADD CONSTRAINT `fk_history_parameter_klinik` 
                    FOREIGN KEY (`permohonan_uji_parameter_klinik_id`) 
                    REFERENCES `tb_permohonan_uji_parameter_klinik` (`id_permohonan_uji_parameter_klinik`) 
                    ON DELETE CASCADE');
            } catch (\Exception $e) {
                // If foreign key fails, continue without it (data integrity maintained at application level)
                \Log::warning('Failed to add foreign key constraint for history table: ' . $e->getMessage());
            }
        }

   


        // Add field to mark selected result
        if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'selected_history_id')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->uuid('selected_history_id')->nullable()->after('offset_baku_mutu');
            });

            // Add foreign key constraint using DB::statement
            try {
                DB::statement('ALTER TABLE `tb_permohonan_uji_parameter_klinik` 
                    ADD CONSTRAINT `fk_selected_history` 
                    FOREIGN KEY (`selected_history_id`) 
                    REFERENCES `tb_permohonan_uji_parameter_klinik_history` (`id_permohonan_uji_parameter_klinik_history`) 
                    ON DELETE SET NULL');
            } catch (\Exception $e) {
                \Log::warning('Failed to add foreign key constraint for selected_history_id: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'selected_history_id')) {
                $table->dropForeign('fk_selected_history');
                $table->dropColumn('selected_history_id');
            }
        });

        Schema::dropIfExists('tb_permohonan_uji_parameter_klinik_history');
    }
}

