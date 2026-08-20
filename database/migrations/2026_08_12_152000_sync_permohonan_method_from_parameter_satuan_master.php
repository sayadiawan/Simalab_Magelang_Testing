<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Helpers\Smt;

/**
 * Sinkronkan method_permohonan_uji_parameter_klinik ke opsi metode master
 * bila nilai tersimpan sudah tidak ada di master (mis. Kreatinin: Sarcosine Oxidase → Enzimatic colorimetric).
 */
class SyncPermohonanMethodFromParameterSatuanMaster extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_satuan_klinik')
            || !Schema::hasTable('tb_permohonan_uji_klinik_2')
        ) {
            return;
        }

        $rows = DB::table('tb_permohonan_uji_parameter_klinik as pp')
            ->join('ms_parameter_satuan_klinik as s', 's.id_parameter_satuan_klinik', '=', 'pp.parameter_satuan_klinik')
            ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'pp.permohonan_uji_klinik')
            ->whereNull('pp.deleted_at')
            ->whereNull('s.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'pp.id_permohonan_uji_parameter_klinik',
                'pp.method_permohonan_uji_parameter_klinik',
                's.metode_parameter_satuan_klinik',
                's.metode_parameter_satuan_klinik_haji',
                'p.is_haji',
            ])
            ->get();

        $now = now();

        DB::transaction(function () use ($rows, $now) {
            foreach ($rows as $row) {
                $metodeRaw = Smt::pickMetodeForContext($row, (int) ($row->is_haji ?? 0));
                $options = Smt::parseMetodeOptionsList(is_string($metodeRaw) ? $metodeRaw : null);
                if (empty($options)) {
                    continue;
                }

                $saved = trim((string) ($row->method_permohonan_uji_parameter_klinik ?? ''));
                $resolved = Smt::resolveMethodSelectedForDisplay($saved, (string) $metodeRaw);

                if ($resolved === '' || $resolved === $saved) {
                    continue;
                }

                DB::table('tb_permohonan_uji_parameter_klinik')
                    ->where('id_permohonan_uji_parameter_klinik', $row->id_permohonan_uji_parameter_klinik)
                    ->update([
                        'method_permohonan_uji_parameter_klinik' => $resolved,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down()
    {
        // Data operasional — tidak di-rollback otomatis.
    }
}
