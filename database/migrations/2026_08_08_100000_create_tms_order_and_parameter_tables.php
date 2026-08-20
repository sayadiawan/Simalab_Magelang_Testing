<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTmsOrderAndParameterTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1) Master parameter TMS (ID mengikuti instrument)
        if (!Schema::hasTable('ms_parameter_tms')) {
            Schema::create('ms_parameter_tms', function (Blueprint $table) {
                $table->unsignedInteger('id_parameter_tms');
                $table->string('name_parameter_tms', 100)->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_parameter_tms', 'pk_parameter_tms');
                $table->index(['is_active', 'deleted_at'], 'idx_parameter_tms_active');
            });
        }

        $this->seedParameterTms();

        // 2) Order TMS (header) — relasi ke permohonan klinik
        if (!Schema::hasTable('tb_order_tms')) {
            Schema::create('tb_order_tms', function (Blueprint $table) {
                $table->char('id_order_tms', 36);
                $table->char('id_permohonan_uji_klinik', 36);
                $table->string('nama_pasien', 255)->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->string('jenis_kelamin', 20)->nullable();
                $table->string('kode_barcode', 100)->nullable();
                $table->string('tray', 50)->nullable();
                $table->string('pos', 50)->nullable();
                $table->boolean('is_executed')->default(0);
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_order_tms', 'pk_order_tms');
                $table->index('id_permohonan_uji_klinik', 'idx_order_tms_permohonan');
                $table->index('kode_barcode', 'idx_order_tms_barcode');
                $table->index('tray', 'idx_order_tms_tray');
                $table->index('pos', 'idx_order_tms_pos');
                $table->index('is_executed', 'idx_order_tms_executed');
            });
        }

        // 3) Order detail TMS — parameter + value
        if (!Schema::hasTable('tb_orderdetail_tms')) {
            Schema::create('tb_orderdetail_tms', function (Blueprint $table) {
                $table->char('id_orderdetail_tms', 36);
                $table->char('id_order_tms', 36);
                $table->unsignedInteger('id_parameter_tms')->nullable();
                $table->char('id_permohonan_uji_parameter_klinik', 36)->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_orderdetail_tms', 'pk_orderdetail_tms');
                $table->index('id_order_tms', 'idx_orderdetail_tms_order');
                $table->index('id_parameter_tms', 'idx_orderdetail_tms_param');
                $table->index('id_permohonan_uji_parameter_klinik', 'idx_orderdetail_tms_puk_param');
            });
        }

        // 4) Relasi parameter permohonan klinik → master TMS (opsional)
        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && !Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'id_parameter_tms')
        ) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->unsignedInteger('id_parameter_tms')->nullable();
                $table->index('id_parameter_tms', 'idx_puk_param_tms');
            });
        }

        // 5) Mapping opsional di master parameter satuan klinik → TMS
        if (Schema::hasTable('ms_parameter_satuan_klinik')
            && !Schema::hasColumn('ms_parameter_satuan_klinik', 'id_parameter_tms')
        ) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->unsignedInteger('id_parameter_tms')->nullable();
                $table->index('id_parameter_tms', 'idx_param_satuan_klinik_tms');
            });
        }

        $this->mapExistingParametersByName();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('ms_parameter_satuan_klinik')
            && Schema::hasColumn('ms_parameter_satuan_klinik', 'id_parameter_tms')
        ) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->dropIndex('idx_param_satuan_klinik_tms');
                $table->dropColumn('id_parameter_tms');
            });
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'id_parameter_tms')
        ) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->dropIndex('idx_puk_param_tms');
                $table->dropColumn('id_parameter_tms');
            });
        }

        Schema::dropIfExists('tb_orderdetail_tms');
        Schema::dropIfExists('tb_order_tms');
        Schema::dropIfExists('ms_parameter_tms');
    }

    /**
     * Seed master parameter TMS sesuai daftar instrument.
     *
     * @return void
     */
    private function seedParameterTms()
    {
        if (!Schema::hasTable('ms_parameter_tms')) {
            return;
        }

        $parameters = [
            1 => 'Test',
            2 => 'Glukosa',
            3 => 'Ureum',
            4 => 'CreatPAP',
            5 => '',
            6 => 'SGPT',
            7 => 'SGOT',
            8 => 'UA',
            9 => 'Bil T',
            10 => 'Bil D',
            11 => 'Alb',
            12 => 'T Prot',
            13 => 'ALP',
            14 => 'GGT',
            15 => 'CholPro',
            16 => 'TrigPro',
            17 => 'CHE',
            18 => 'HDL Sek',
            19 => 'LDL Sek',
            20 => 'Chol Sek',
            21 => 'Trig Sek',
            22 => '',
            23 => '',
            24 => '',
            25 => '',
            26 => '',
            27 => '',
            28 => '',
            29 => '',
            30 => 'T340-380',
            31 => 'T405-450',
            32 => 'T480-505',
            33 => 'T546-570',
            34 => 'T600-660',
            35 => 'T700-750',
            36 => 'T800',
            37 => '',
            38 => '',
            39 => '',
            40 => '',
            41 => '',
            42 => '',
            43 => '',
            44 => '',
            45 => '',
            46 => '',
            47 => '',
            48 => '',
            49 => '',
            50 => '',
            51 => '',
            52 => '',
            53 => '',
            54 => '',
            55 => '',
            56 => '',
            57 => '',
            58 => '',
            59 => '',
            60 => '',
            61 => '',
            62 => '',
            63 => '',
            64 => '',
            65 => '',
            66 => '',
            67 => '',
            68 => '',
            69 => '',
            70 => '',
            71 => '',
            72 => '',
            73 => '',
            74 => '',
            75 => '',
            76 => '',
            77 => '',
            78 => 'Na',
            79 => 'K',
            80 => 'Cl',
            81 => '',
            82 => '',
            83 => '',
            84 => '',
            85 => '',
            86 => '',
            87 => '',
            88 => '',
            89 => '',
            90 => '',
            91 => '',
            92 => '',
            93 => '',
            94 => '',
            95 => '',
            96 => 'L',
            97 => 'H',
            98 => 'I',
            99 => 'Lysing',
            101 => 'Hb',
            102 => 'A1c',
            106 => 'HbA1c',
            107 => 'Hb',
            108 => 'A1c',
            111 => 'DIL1',
            112 => 'DIL2',
            113 => 'Wash1',
            114 => 'Wash2',
            115 => 'Wash3',
            117 => 'Water',
        ];

        $now = now()->format('Y-m-d H:i:s');

        foreach ($parameters as $id => $name) {
            $exists = DB::table('ms_parameter_tms')
                ->where('id_parameter_tms', $id)
                ->exists();

            if ($exists) {
                DB::table('ms_parameter_tms')
                    ->where('id_parameter_tms', $id)
                    ->update([
                        'name_parameter_tms' => $name !== '' ? $name : null,
                        'is_active' => 1,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('ms_parameter_tms')->insert([
                'id_parameter_tms' => $id,
                'name_parameter_tms' => $name !== '' ? $name : null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Map parameter klinik yang namanya cocok dengan master TMS.
     * Prefer ID TMS yang ber-nama (bukan slot kosong).
     *
     * @return void
     */
    private function mapExistingParametersByName()
    {
        if (!Schema::hasTable('ms_parameter_tms')
            || !Schema::hasTable('ms_parameter_satuan_klinik')
            || !Schema::hasColumn('ms_parameter_satuan_klinik', 'id_parameter_tms')
        ) {
            return;
        }

        $aliases = [
            'glucose' => 'glukosa',
            'glukose' => 'glukosa',
            'creatinine' => 'creatpap',
            'creatinin' => 'creatpap',
            'kreatinin' => 'creatpap',
            'alt' => 'sgpt',
            'ast' => 'sgot',
            'uric acid' => 'ua',
            'asam urat' => 'ua',
            'bilirubin total' => 'bil t',
            'bilirubin direk' => 'bil d',
            'bilirubin direct' => 'bil d',
            'albumin' => 'alb',
            'total protein' => 't prot',
            'protein total' => 't prot',
            'cholesterol' => 'cholpro',
            'kolesterol' => 'cholpro',
            'trigliserida' => 'trigpro',
            'triglyceride' => 'trigpro',
            'sodium' => 'na',
            'natrium' => 'na',
            'potassium' => 'k',
            'kalium' => 'k',
            'chloride' => 'cl',
            'klorida' => 'cl',
            'hemoglobin' => 'hb',
            'hba1c' => 'hba1c',
        ];

        $tmsRows = DB::table('ms_parameter_tms')
            ->whereNull('deleted_at')
            ->whereNotNull('name_parameter_tms')
            ->where('name_parameter_tms', '!=', '')
            ->orderBy('id_parameter_tms')
            ->get(['id_parameter_tms', 'name_parameter_tms']);

        $tmsByNormalized = [];
        foreach ($tmsRows as $row) {
            $key = $this->normalizeParameterName($row->name_parameter_tms);
            if ($key === '') {
                continue;
            }
            // Simpan ID pertama (lebih kecil) jika nama duplikat (mis. Hb 101 & 107)
            if (!isset($tmsByNormalized[$key])) {
                $tmsByNormalized[$key] = (int) $row->id_parameter_tms;
            }
        }

        $masters = DB::table('ms_parameter_satuan_klinik')
            ->whereNull('deleted_at')
            ->whereNull('id_parameter_tms')
            ->get(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik']);

        foreach ($masters as $master) {
            $normalized = $this->normalizeParameterName($master->name_parameter_satuan_klinik);
            if ($normalized === '') {
                continue;
            }

            $lookup = $aliases[$normalized] ?? $normalized;
            if (!isset($tmsByNormalized[$lookup])) {
                continue;
            }

            $tmsId = $tmsByNormalized[$lookup];

            DB::table('ms_parameter_satuan_klinik')
                ->where('id_parameter_satuan_klinik', $master->id_parameter_satuan_klinik)
                ->update(['id_parameter_tms' => $tmsId]);

            if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
                && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'id_parameter_tms')
            ) {
                DB::table('tb_permohonan_uji_parameter_klinik')
                    ->where('parameter_satuan_klinik', $master->id_parameter_satuan_klinik)
                    ->whereNull('id_parameter_tms')
                    ->update(['id_parameter_tms' => $tmsId]);
            }
        }
    }

    /**
     * @param mixed $name
     * @return string
     */
    private function normalizeParameterName($name)
    {
        $name = strtolower(trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name);
        return $name === null ? '' : $name;
    }
}
