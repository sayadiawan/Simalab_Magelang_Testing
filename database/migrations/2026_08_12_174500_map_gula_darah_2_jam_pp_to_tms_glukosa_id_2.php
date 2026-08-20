<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hubungkan parameter Gula Darah 2 Jam PP ke TMS Glukosa (id_parameter_tms = 2).
 */
class MapGulaDarah2JamPpToTmsGlukosaId2 extends Migration
{
    private const TMS_GLUKOSA_ID = 2;

    private const SATUAN_NAMES = [
        'Gula Darah 2 Jam PP',
        'Gula Darah 2jpp',
        '2 Jam PP',
        'GD 2 Jam PP',
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_parameter_satuan_klinik')
            || !Schema::hasColumn('ms_parameter_satuan_klinik', 'id_parameter_tms')
        ) {
            return;
        }

        $ids = DB::table('ms_parameter_satuan_klinik')
            ->whereNull('deleted_at')
            ->whereIn('name_parameter_satuan_klinik', self::SATUAN_NAMES)
            ->pluck('id_parameter_satuan_klinik');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('ms_parameter_satuan_klinik')
            ->whereIn('id_parameter_satuan_klinik', $ids->all())
            ->update(['id_parameter_tms' => self::TMS_GLUKOSA_ID]);

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'id_parameter_tms')
        ) {
            DB::table('tb_permohonan_uji_parameter_klinik')
                ->whereIn('parameter_satuan_klinik', $ids->all())
                ->whereNull('id_parameter_tms')
                ->update(['id_parameter_tms' => self::TMS_GLUKOSA_ID]);
        }
    }

    public function down()
    {
        // Data operasional — tidak di-rollback otomatis.
    }
}
