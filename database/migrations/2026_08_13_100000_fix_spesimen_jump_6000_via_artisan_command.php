<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Pastikan sisa nomor spesimen 6216/6217 (dll) ter-rapikan di production.
 * Idempotent — aman dijalankan ulang via migrate maupun artisan command.
 */
class FixSpesimenJump6000ViaArtisanCommand extends Migration
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
