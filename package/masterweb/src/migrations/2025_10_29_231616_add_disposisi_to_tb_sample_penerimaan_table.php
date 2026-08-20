<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisposisiToTbSamplePenerimaanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_sample_penerimaan', function (Blueprint $table) {
            // Tambahkan kolom penerima sampel
            if (!Schema::hasColumn('tb_sample_penerimaan', 'penerima_sampel')) {
                $table->string('penerima_sampel')->nullable()->after('pengawetan_dengan');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'penerima_tanggal')) {
                $table->datetime('penerima_tanggal')->nullable()->after('penerima_sampel');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'penerima_signature')) {
                $table->longText('penerima_signature')->nullable()->after('penerima_tanggal');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'penerima_signature_type')) {
                $table->enum('penerima_signature_type', ['canvas', 'tte'])->nullable()->after('penerima_signature');
            }
            
            // Tambahkan kolom disposisi ke analis
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis')) {
                $table->string('disposisi_analis')->nullable()->after('penerima_signature_type');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_tanggal')) {
                $table->datetime('disposisi_analis_tanggal')->nullable()->after('disposisi_analis');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_signature')) {
                $table->longText('disposisi_analis_signature')->nullable()->after('disposisi_analis_tanggal');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_signature_type')) {
                $table->enum('disposisi_analis_signature_type', ['canvas', 'tte'])->nullable()->after('disposisi_analis_signature');
            }
            
            // Tambahkan kolom disposisi ke koordinator kesmas
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_koordinator_kesmas')) {
                $table->string('disposisi_koordinator_kesmas')->nullable()->after('disposisi_analis_signature_type');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_tanggal')) {
                $table->datetime('disposisi_tanggal')->nullable()->after('disposisi_koordinator_kesmas');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_signature')) {
                $table->longText('disposisi_signature')->nullable()->after('disposisi_tanggal');
            }
            if (!Schema::hasColumn('tb_sample_penerimaan', 'disposisi_signature_type')) {
                $table->enum('disposisi_signature_type', ['canvas', 'tte'])->nullable()->after('disposisi_signature');
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
            // Hapus kolom disposisi
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_koordinator_kesmas')) {
                $table->dropColumn('disposisi_koordinator_kesmas');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_tanggal')) {
                $table->dropColumn('disposisi_tanggal');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_signature')) {
                $table->dropColumn('disposisi_signature');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_signature_type')) {
                $table->dropColumn('disposisi_signature_type');
            }
            
            // Hapus kolom disposisi analis
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis')) {
                $table->dropColumn('disposisi_analis');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_tanggal')) {
                $table->dropColumn('disposisi_analis_tanggal');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_signature')) {
                $table->dropColumn('disposisi_analis_signature');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'disposisi_analis_signature_type')) {
                $table->dropColumn('disposisi_analis_signature_type');
            }
            
            // Hapus kolom penerima sampel
            if (Schema::hasColumn('tb_sample_penerimaan', 'penerima_sampel')) {
                $table->dropColumn('penerima_sampel');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'penerima_tanggal')) {
                $table->dropColumn('penerima_tanggal');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'penerima_signature')) {
                $table->dropColumn('penerima_signature');
            }
            if (Schema::hasColumn('tb_sample_penerimaan', 'penerima_signature_type')) {
                $table->dropColumn('penerima_signature_type');
            }
        });
    }
}