<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTbSamplePenerimaanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_sample_penerimaan', function (Blueprint $table) {
            // Tambahkan kolom baru untuk form penerimaan sampel massal
            if (!Schema::hasColumn('tb_sample_penerimaan', 'kondisi_sample')) {
                $table->text('kondisi_sample')->nullable()->after('kelayakan_berat_vol');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'pengawetan_oleh')) {
                $table->string('pengawetan_oleh')->nullable()->after('kondisi_sample');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'pengawetan_dengan')) {
                $table->text('pengawetan_dengan')->nullable()->after('pengawetan_oleh');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'laboratorium_id')) {
                $table->string('laboratorium_id')->nullable()->after('sample_id');
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
        Schema::table('tb_sample_penerimaan', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            if (Schema::hasColumn('tb_sample_penerimaan', 'kondisi_sample')) {
                $table->dropColumn('kondisi_sample');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'pengawetan_oleh')) {
                $table->dropColumn('pengawetan_oleh');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'pengawetan_dengan')) {
                $table->dropColumn('pengawetan_dengan');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'laboratorium_id')) {
                $table->dropColumn('laboratorium_id');
            }
        });
    }
}
