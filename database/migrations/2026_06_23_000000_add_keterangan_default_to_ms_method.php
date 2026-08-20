<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganDefaultToMsMethod extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ms_method')) {
            return;
        }

        Schema::table('ms_method', function (Blueprint $table) {
            if (!Schema::hasColumn('ms_method', 'keterangan_default')) {
                $table->text('keterangan_default')->nullable()->after('name_method');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('ms_method')) {
            return;
        }

        Schema::table('ms_method', function (Blueprint $table) {
            if (Schema::hasColumn('ms_method', 'keterangan_default')) {
                $table->dropColumn('keterangan_default');
            }
        });
    }
}
