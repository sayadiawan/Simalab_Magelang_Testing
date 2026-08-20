<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateMsRegisterHasilKlinisKolom extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ms_register_hasil_klinis_kolom')) {
            Schema::create('ms_register_hasil_klinis_kolom', function (Blueprint $table) {
                $table->char('id_register_hasil_klinis_kolom', 36)->primary();
                $table->string('kode', 40);
                $table->string('label', 80);
                $table->string('grup', 40);
                $table->unsignedInteger('sort')->default(0);
                $table->boolean('tampil')->default(1);
                $table->text('match_keys')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('kode');
                $table->index(['grup', 'sort']);
            });
        }

        $this->seedDefaults();
    }

    public function down()
    {
        Schema::dropIfExists('ms_register_hasil_klinis_kolom');
    }

    private function seedDefaults(): void
    {
        $exists = DB::table('ms_register_hasil_klinis_kolom')->count();
        if ($exists > 0) {
            return;
        }

        $now = now();
        $rows = [];
        $sort = 0;

        foreach ($this->defaultColumns() as $col) {
            $sort++;
            $rows[] = [
                'id_register_hasil_klinis_kolom' => Uuid::uuid4()->toString(),
                'kode' => $col['kode'],
                'label' => $col['label'],
                'grup' => $col['grup'],
                'sort' => $sort,
                'tampil' => 1,
                'match_keys' => json_encode($col['match'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('ms_register_hasil_klinis_kolom')->insert($chunk);
        }
    }

    private function defaultColumns(): array
    {
        return [
            // Kimia Darah
            ['kode' => 'HbA1c', 'label' => 'HbA1c', 'grup' => 'kimia_darah', 'match' => ['hba1c']],
            ['kode' => 'GDN', 'label' => 'GDN', 'grup' => 'kimia_darah', 'match' => ['gula darah puasa', 'gdn']],
            ['kode' => 'PP', 'label' => 'PP', 'grup' => 'kimia_darah', 'match' => ['gula darah 2 jam pp', 'gd 2 jam pp', 'pp']],
            ['kode' => 'GDS', 'label' => 'GDS', 'grup' => 'kimia_darah', 'match' => ['gula darah sewaktu', 'gds']],
            ['kode' => 'Chol', 'label' => 'Chol', 'grup' => 'kimia_darah', 'match' => ['kolesterol', 'cholesterol', 'chol']],
            ['kode' => 'HDL', 'label' => 'HDL', 'grup' => 'kimia_darah', 'match' => ['hdl']],
            ['kode' => 'LDL', 'label' => 'LDL', 'grup' => 'kimia_darah', 'match' => ['ldl']],
            ['kode' => 'Tg', 'label' => 'Tg', 'grup' => 'kimia_darah', 'match' => ['trigliserida', 'trigliserid', 'tg']],
            ['kode' => 'AU', 'label' => 'AU', 'grup' => 'kimia_darah', 'match' => ['asam urat', 'au']],
            ['kode' => 'OT', 'label' => 'OT', 'grup' => 'kimia_darah', 'match' => ['sgot', 'ast', 'ot']],
            ['kode' => 'PT', 'label' => 'PT', 'grup' => 'kimia_darah', 'match' => ['sgpt', 'alt', 'pt']],
            ['kode' => 'Ur', 'label' => 'Ur', 'grup' => 'kimia_darah', 'match' => ['ureum', 'urea', 'ur']],
            ['kode' => 'Cre', 'label' => 'Cre', 'grup' => 'kimia_darah', 'match' => ['creatinine', 'creatinin', 'cre']],
            ['kode' => 'Alb', 'label' => 'Alb', 'grup' => 'kimia_darah', 'match' => ['albumin', 'alb']],
            ['kode' => 'Glob', 'label' => 'Glob', 'grup' => 'kimia_darah', 'match' => ['globulin', 'glob']],
            ['kode' => 'TP', 'label' => 'TP', 'grup' => 'kimia_darah', 'match' => ['total protein', 'tp']],
            ['kode' => 'Bil.T', 'label' => 'Bil.T', 'grup' => 'kimia_darah', 'match' => ['bilirubin total', 'bil t']],
            ['kode' => 'Bil.D', 'label' => 'Bil.D', 'grup' => 'kimia_darah', 'match' => ['bilirubin direct', 'bil d']],
            ['kode' => 'Bil.I', 'label' => 'Bil.I', 'grup' => 'kimia_darah', 'match' => ['bilirubin indirect', 'bil i']],
            ['kode' => 'ALP', 'label' => 'ALP', 'grup' => 'kimia_darah', 'match' => ['alkaline phosphatase', 'alp']],
            ['kode' => 'GGT', 'label' => 'GGT', 'grup' => 'kimia_darah', 'match' => ['gamma-gt', 'ggt']],
            ['kode' => 'LDH', 'label' => 'LDH', 'grup' => 'kimia_darah', 'match' => ['lactate dehydrogenase', 'ldh']],
            ['kode' => 'CK', 'label' => 'CK', 'grup' => 'kimia_darah', 'match' => ['creatine kinase', 'ck']],
            ['kode' => 'CKMB', 'label' => 'CKMB', 'grup' => 'kimia_darah', 'match' => ['ckmb']],
            ['kode' => 'Amylase', 'label' => 'Amylase', 'grup' => 'kimia_darah', 'match' => ['amylase']],
            ['kode' => 'Lipase', 'label' => 'Lipase', 'grup' => 'kimia_darah', 'match' => ['lipase']],
            ['kode' => 'Na', 'label' => 'Na', 'grup' => 'kimia_darah', 'match' => ['natrium', 'sodium', 'na']],
            ['kode' => 'K', 'label' => 'K', 'grup' => 'kimia_darah', 'match' => ['kalium', 'potassium', 'k']],
            ['kode' => 'Cl', 'label' => 'Cl', 'grup' => 'kimia_darah', 'match' => ['klorida', 'chloride', 'cl']],
            ['kode' => 'Ca', 'label' => 'Ca', 'grup' => 'kimia_darah', 'match' => ['kalsium', 'calcium', 'ca']],
            ['kode' => 'Mg', 'label' => 'Mg', 'grup' => 'kimia_darah', 'match' => ['magnesium', 'mg']],
            ['kode' => 'Phos', 'label' => 'Phos', 'grup' => 'kimia_darah', 'match' => ['phosphate', 'fosfat', 'phos']],

            // Darah Rutin
            ['kode' => 'Hb', 'label' => 'Hb', 'grup' => 'darah_rutin', 'match' => ['hemoglobin', 'hb']],
            ['kode' => 'Ht', 'label' => 'Ht', 'grup' => 'darah_rutin', 'match' => ['hematokrit', 'ht']],
            ['kode' => 'MCV', 'label' => 'MCV', 'grup' => 'darah_rutin', 'match' => ['mcv']],
            ['kode' => 'MCH', 'label' => 'MCH', 'grup' => 'darah_rutin', 'match' => ['mch']],
            ['kode' => 'MCHC', 'label' => 'MCHC', 'grup' => 'darah_rutin', 'match' => ['mchc']],
            ['kode' => 'PLT', 'label' => 'PLT', 'grup' => 'darah_rutin', 'match' => ['trombosit', 'platelet', 'plt']],
            ['kode' => 'Neu', 'label' => 'Neu', 'grup' => 'darah_rutin', 'match' => ['neutrofil', 'neu']],
            ['kode' => 'Limfo', 'label' => 'Limfo', 'grup' => 'darah_rutin', 'match' => ['limfosit', 'lymphocyte', 'limfo']],
            ['kode' => 'Mono', 'label' => 'Mono', 'grup' => 'darah_rutin', 'match' => ['monosit', 'monocyte', 'mono']],
            ['kode' => 'Eos', 'label' => 'Eos', 'grup' => 'darah_rutin', 'match' => ['eosinofil', 'eosinophil', 'eos']],
            ['kode' => 'Baso', 'label' => 'Baso', 'grup' => 'darah_rutin', 'match' => ['basofil', 'basophil', 'baso']],
            ['kode' => 'LED', 'label' => 'LED', 'grup' => 'darah_rutin', 'match' => ['led', 'esr']],
            ['kode' => 'GolDar', 'label' => 'GolDar', 'grup' => 'darah_rutin', 'match' => ['golongan darah', 'golongan', 'gol dar']],

            // Widal
            ['kode' => 'Typhi O', 'label' => 'Typhi O', 'grup' => 'widal', 'match' => ['widal typhi o', 'typhi o']],
            ['kode' => 'Typhi H', 'label' => 'Typhi H', 'grup' => 'widal', 'match' => ['widal typhi h', 'typhi h']],

            // HbSAg
            ['kode' => 'HbSAg', 'label' => 'HbSAg', 'grup' => 'hbsag', 'match' => ['hbs ag', 'hbsag']],

            // Urin Rutin
            ['kode' => 'Warna', 'label' => 'Warna', 'grup' => 'urin_rutin', 'match' => ['warna']],
            ['kode' => 'Kekeruhan', 'label' => 'Kekeruhan', 'grup' => 'urin_rutin', 'match' => ['kekeruhan', 'turbidity']],
            ['kode' => 'Blood', 'label' => 'Blood', 'grup' => 'urin_rutin', 'match' => ['blood']],
            ['kode' => 'Uro', 'label' => 'Uro', 'grup' => 'urin_rutin', 'match' => ['urobilinogen', 'uro']],
            ['kode' => 'Bilirubin', 'label' => 'Bilirubin', 'grup' => 'urin_rutin', 'match' => ['bilirubin']],
            ['kode' => 'Protein', 'label' => 'Protein', 'grup' => 'urin_rutin', 'match' => ['protein urin', 'protein']],
            ['kode' => 'Nitrit', 'label' => 'Nitrit', 'grup' => 'urin_rutin', 'match' => ['nitrite', 'nitrit']],
            ['kode' => 'Keton', 'label' => 'Keton', 'grup' => 'urin_rutin', 'match' => ['ketones', 'keton']],
            ['kode' => 'Glukosa', 'label' => 'Glukosa', 'grup' => 'urin_rutin', 'match' => ['glukosa', 'glucose']],
            ['kode' => 'pH', 'label' => 'pH', 'grup' => 'urin_rutin', 'match' => ['ph']],
            ['kode' => 'BJ', 'label' => 'BJ', 'grup' => 'urin_rutin', 'match' => ['specific gravity', 'berat jenis', 'bj']],
            ['kode' => 'Leukosit', 'label' => 'Leukosit', 'grup' => 'urin_rutin', 'match' => ['leukocyte', 'leukosit']],
            ['kode' => 'Eritrosit', 'label' => 'Eritrosit', 'grup' => 'urin_rutin', 'match' => ['erythrocyte', 'eritrosit']],
            ['kode' => 'Bakteri', 'label' => 'Bakteri', 'grup' => 'urin_rutin', 'match' => ['bacteria', 'bakteri']],
            ['kode' => 'Epitel', 'label' => 'Epitel', 'grup' => 'urin_rutin', 'match' => ['epithelial', 'epitel']],
            ['kode' => 'Silinder', 'label' => 'Silinder', 'grup' => 'urin_rutin', 'match' => ['silinder', 'cast']],
            ['kode' => 'Kristal', 'label' => 'Kristal', 'grup' => 'urin_rutin', 'match' => ['kristal', 'crystal']],
            ['kode' => 'Lain"', 'label' => 'Lain"', 'grup' => 'urin_rutin', 'match' => ['lain-lain', 'lain lain']],

            // Lain-lain
            ['kode' => 'PPTes', 'label' => 'PPTes', 'grup' => 'other', 'match' => ['pregnancy test', 'tes kehamilan', 'ppt']],
            ['kode' => 'Narkoba', 'label' => 'Narkoba', 'grup' => 'other', 'match' => ['tes narkoba', 'drug test', 'narkoba']],
            ['kode' => 'Lain-lain', 'label' => 'Lain-lain', 'grup' => 'other', 'match' => ['lain-lain', 'lain lain']],
        ];
    }
}
