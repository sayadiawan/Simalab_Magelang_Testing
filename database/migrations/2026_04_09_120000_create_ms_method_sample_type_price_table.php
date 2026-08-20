<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsMethodSampleTypePriceTable extends Migration
{
  /**
   * Harga parameter per jenis sampel (Kesmas / non-klinik). Laboratorium tidak membedakan harga.
   */
  public function up()
  {
    Schema::create('ms_method_sample_type_price', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('method_id');
      $table->uuid('sample_type_id');
      $table->decimal('price', 15, 2)->default(0);
      $table->timestamps();

      $table->unique(['method_id', 'sample_type_id'], 'method_sample_type_unique');
      $table->index('sample_type_id');
    });
  }

  public function down()
  {
    Schema::dropIfExists('ms_method_sample_type_price');
  }
}
