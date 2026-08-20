<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTmptLahirPekerjaanToMsPasienTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_pasien', function (Blueprint $table) {
            if (!Schema::hasColumn('ms_pasien', 'tmpt_lahir')) {
                $table->string('tmpt_lahir', 191)->nullable()->after('tgllahir_pasien');
            }
            if (!Schema::hasColumn('ms_pasien', 'pekerjaan')) {
                $table->string('pekerjaan', 191)->nullable()->after('tmpt_lahir');
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
        Schema::table('ms_pasien', function (Blueprint $table) {
            if (Schema::hasColumn('ms_pasien', 'pekerjaan')) {
                $table->dropColumn('pekerjaan');
            }
            if (Schema::hasColumn('ms_pasien', 'tmpt_lahir')) {
                $table->dropColumn('tmpt_lahir');
            }
        });
    }
}
