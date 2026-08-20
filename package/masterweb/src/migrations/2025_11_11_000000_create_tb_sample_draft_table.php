<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbSampleDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
        Schema::create('tb_sample_draft', function (Blueprint $table) {
            $table->uuid('id_sample_draft')->primary();
                $table->char('permohonan_uji_id', 36); // Changed from uuid() to char(36) for compatibility
            $table->uuid('draft_group_id')->nullable()->comment('Group ID for drafts from same input session');
            $table->uuid('typesample_samples')->nullable();
            $table->string('codesample_samples')->nullable();
            $table->string('name_pelanggan')->nullable();
            $table->datetime('datesampling_samples')->nullable();
            $table->datetime('date_sending')->nullable();
            $table->text('titik_pengambilan')->nullable();
            $table->decimal('cost_samples', 15, 2)->default(0);
            $table->text('note_samples')->nullable();
            $table->string('pengambil_sampel')->nullable();
            $table->integer('count_id')->nullable();
            $table->uuid('program_samples')->nullable();
            $table->uuid('packet_id')->nullable();
            $table->string('name_send_sample')->nullable();
            $table->string('code_sample_customer')->nullable();
            $table->tinyInteger('is_sampling')->default(1);
            $table->decimal('cost_sampling_samples', 15, 2)->default(0);
            
            // JSON field untuk menyimpan method yang dipilih
            $table->json('method_data')->nullable()->comment('Array of method_id, laboratorium_id, price_method');
            
            // Status draft
            $table->enum('status', ['draft', 'confirmed', 'deleted'])->default('draft');
            $table->uuid('confirmed_by')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->uuid('created_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
                // Indexes only - no foreign key constraint due to potential data type incompatibility
                // Using index provides query performance without strict referential integrity
            $table->index('permohonan_uji_id');
            $table->index('draft_group_id');
            $table->index('status');
        });
        }
        
        // NOTE: Foreign key constraint is intentionally skipped
        // Reason: Data type incompatibility between permohonan_uji_id (CHAR) and id_permohonan_uji (potential BINARY/UUID)
        // The index above provides adequate query performance
        // Referential integrity should be maintained at application level
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No foreign key to drop (intentionally not created)
        // Just drop the table if it exists
        if (Schema::hasTable('tb_sample_draft')) {
            Schema::dropIfExists('tb_sample_draft');
        }
    }
}