<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillTipeNilaiBakuMutuFromEqualOnTbBakuMutu extends Migration
{
    public function up()
    {
        // Jika equal = Negatif/Positif => kualitatif
        DB::statement("
            UPDATE tb_baku_mutu
            SET tipe_nilai_baku_mutu = 'kualitatif'
            WHERE LOWER(TRIM(`equal`)) IN ('negatif', 'positif')
        ");

        // Selain itu => kuantitatif
        DB::statement("
            UPDATE tb_baku_mutu
            SET tipe_nilai_baku_mutu = 'kuantitatif'
            WHERE `equal` IS NULL
               OR LOWER(TRIM(`equal`)) NOT IN ('negatif', 'positif')
        ");
    }

    public function down()
    {
        // Rollback data backfill: kosongkan kembali nilainya
        DB::table('tb_baku_mutu')->update([
            'tipe_nilai_baku_mutu' => null,
        ]);
    }
}

