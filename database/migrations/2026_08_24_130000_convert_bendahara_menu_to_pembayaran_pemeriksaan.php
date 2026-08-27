<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah menu BNDR dari "Semua Pemeriksaan" menjadi halaman pembayaran campuran.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_130000_convert_bendahara_menu_to_pembayaran_pemeriksaan.php
 */
class ConvertBendaharaMenuToPembayaranPemeriksaan extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('id', 232)
            ->whereNull('deleted_at')
            ->update([
                'name' => 'Pembayaran Pemeriksaan',
                'icon' => 'ti-wallet',
                'link' => '/bendahara/pembayaran-pemeriksaan',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('id', 232)
            ->whereNull('deleted_at')
            ->update([
                'name' => 'Semua Pemeriksaan',
                'icon' => 'ti-view-list',
                'link' => '/elits-permohonan-uji-klinik/verifikasi/lists',
                'updated_at' => now(),
            ]);
    }
}
