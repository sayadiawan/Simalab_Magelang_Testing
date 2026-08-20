<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddJenisSampelToMsParameterTmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_parameter_tms')) {
            return;
        }

        if (!Schema::hasColumn('ms_parameter_tms', 'jenis_sampel')) {
            Schema::table('ms_parameter_tms', function (Blueprint $table) {
                $table->string('jenis_sampel', 30)->nullable()->after('name_parameter_tms');
                $table->index('jenis_sampel', 'idx_parameter_tms_jenis_sampel');
            });
        }

        $this->seedJenisSampel();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('ms_parameter_tms')
            || !Schema::hasColumn('ms_parameter_tms', 'jenis_sampel')
        ) {
            return;
        }

        Schema::table('ms_parameter_tms', function (Blueprint $table) {
            $table->dropIndex('idx_parameter_tms_jenis_sampel');
            $table->dropColumn('jenis_sampel');
        });
    }

    /**
     * Isi jenis sampel alat untuk baris yang masih kosong.
     * Nama yang memuat "urin" diutamakan Urine (mis. glukosa urin).
     *
     * @return void
     */
    private function seedJenisSampel()
    {
        $now = now()->format('Y-m-d H:i:s');
        $rows = DB::table('ms_parameter_tms')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('jenis_sampel')->orWhere('jenis_sampel', '');
            })
            ->get(['id_parameter_tms', 'name_parameter_tms']);

        foreach ($rows as $row) {
            $jenis = $this->inferJenisSampel((int) $row->id_parameter_tms, (string) $row->name_parameter_tms);
            if ($jenis === null) {
                continue;
            }

            DB::table('ms_parameter_tms')
                ->where('id_parameter_tms', $row->id_parameter_tms)
                ->update([
                    'jenis_sampel' => $jenis,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * @param  int  $id
     * @param  string  $name
     * @return string|null
     */
    private function inferJenisSampel($id, $name)
    {
        $n = strtolower(trim($name));

        if ($n !== '') {
            if (strpos($n, 'urin') !== false) {
                return 'Urine';
            }
            if (strpos($n, 'blood cell') !== false || $n === 'a1c' || $n === 'hba1c') {
                return 'Blood Cell';
            }
            if (strpos($n, 'feses') !== false || strpos($n, 'feces') !== false) {
                return 'Feses';
            }
            if (strpos($n, 'swab') !== false) {
                return 'Swab';
            }
            if (strpos($n, 'plasma') !== false) {
                return 'Plasma';
            }
            if (strpos($n, 'serum') !== false) {
                return 'Serum';
            }
            if (strpos($n, 'darah') !== false || $n === 'hb') {
                return 'Darah';
            }
        }

        // Channel kimia darah/serum pada instrument (bukan urin).
        $serumIds = [2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 78, 79, 80];
        if (in_array($id, $serumIds, true)) {
            return 'Serum';
        }

        $darahIds = [101, 107];
        if (in_array($id, $darahIds, true)) {
            return 'Darah';
        }

        $bloodCellIds = [102, 106, 108];
        if (in_array($id, $bloodCellIds, true)) {
            return 'Blood Cell';
        }

        return null;
    }
}
