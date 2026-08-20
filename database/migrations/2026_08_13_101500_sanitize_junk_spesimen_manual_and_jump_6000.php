<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Rapikan No Register yang salah masuk:
 * - 25991559 (junk nomor_spesimen_manual)
 * - sisa 6216/6217 (loncatan counter)
 */
class SanitizeJunkSpesimenManualAndJump6000 extends Migration
{
    public function up()
    {
        if (!class_exists(\App\Console\Commands\FixSpesimenJump6000::class)) {
            return;
        }

        Artisan::call('spesimen:fix-jump-6000', [
            '--year' => 2026,
            '--threshold' => 5000,
        ]);
    }

    public function down()
    {
        // no-op
    }
}
