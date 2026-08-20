<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSatuanLinkToRegisterHasilKlinisKolom extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ms_register_hasil_klinis_kolom_satuan')) {
            Schema::create('ms_register_hasil_klinis_kolom_satuan', function (Blueprint $table) {
                $table->char('id_register_hasil_klinis_kolom', 36);
                $table->char('id_parameter_satuan_klinik', 36);
                $table->primary(
                    ['id_register_hasil_klinis_kolom', 'id_parameter_satuan_klinik'],
                    'pk_register_kolom_satuan'
                );
                $table->index('id_parameter_satuan_klinik', 'idx_register_kolom_satuan_satuan');
            });
        }

        $this->seedLinksFromMatchKeys();
    }

    public function down()
    {
        Schema::dropIfExists('ms_register_hasil_klinis_kolom_satuan');
    }

    /**
     * Seed relasi awal: cocokkan match_keys kolom ke nama parameter satuan.
     */
    private function seedLinksFromMatchKeys(): void
    {
        $exists = DB::table('ms_register_hasil_klinis_kolom_satuan')->count();
        if ($exists > 0) {
            return;
        }

        $satuans = DB::table('ms_parameter_satuan_klinik')
            ->whereNull('deleted_at')
            ->get(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik']);

        $columns = DB::table('ms_register_hasil_klinis_kolom')
            ->whereNull('deleted_at')
            ->get(['id_register_hasil_klinis_kolom', 'kode', 'match_keys']);

        $inserts = [];
        $seen = [];

        foreach ($columns as $col) {
            $keys = json_decode($col->match_keys ?: '[]', true);
            if (!is_array($keys)) {
                $keys = [];
            }
            $keys[] = strtolower(trim((string) $col->kode));
            $keys = array_values(array_unique(array_filter(array_map(function ($k) {
                return strtolower(trim((string) $k));
            }, $keys))));

            // Urutkan key panjang dulu agar lebih spesifik
            usort($keys, function ($a, $b) {
                return strlen($b) <=> strlen($a);
            });

            foreach ($satuans as $satuan) {
                $name = strtolower(trim((string) $satuan->name_parameter_satuan_klinik));
                if ($name === '') {
                    continue;
                }

                $matched = false;
                foreach ($keys as $key) {
                    if ($key === '') {
                        continue;
                    }
                    $exactOnly = strlen($key) <= 2;
                    if ($exactOnly) {
                        if ($name === $key) {
                            $matched = true;
                            break;
                        }
                        continue;
                    }
                    if ($name === $key || strpos($name, $key) !== false) {
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    continue;
                }

                $pair = $col->id_register_hasil_klinis_kolom . '|' . $satuan->id_parameter_satuan_klinik;
                if (isset($seen[$pair])) {
                    continue;
                }
                $seen[$pair] = true;
                $inserts[] = [
                    'id_register_hasil_klinis_kolom' => $col->id_register_hasil_klinis_kolom,
                    'id_parameter_satuan_klinik' => $satuan->id_parameter_satuan_klinik,
                ];
            }
        }

        foreach (array_chunk($inserts, 100) as $chunk) {
            DB::table('ms_register_hasil_klinis_kolom_satuan')->insert($chunk);
        }
    }
}
