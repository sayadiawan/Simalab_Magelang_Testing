<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateMsJenisSampelKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_jenis_sampel_klinik')) {
            Schema::create('ms_jenis_sampel_klinik', function (Blueprint $table) {
                $table->char('id_jenis_sampel_klinik', 36);
                $table->string('name_jenis_sampel_klinik', 100);
                $table->string('code_jenis_sampel_klinik', 50)->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_jenis_sampel_klinik', 'pk_jenis_sampel_klinik');
                $table->index(['is_active', 'deleted_at'], 'idx_jenis_sampel_klinik_active');
                $table->index('sort_order', 'idx_jenis_sampel_klinik_sort');
            });
        }

        $this->seedDefaults();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_jenis_sampel_klinik');
    }

    /**
     * Seed opsi default yang sebelumnya di-hardcode di form klinik.
     *
     * @return void
     */
    private function seedDefaults()
    {
        if (!Schema::hasTable('ms_jenis_sampel_klinik')) {
            return;
        }

        $defaults = [
            ['name' => 'Darah', 'code' => 'DARAH', 'sort' => 1],
            ['name' => 'Serum', 'code' => 'SERUM', 'sort' => 2],
            ['name' => 'Plasma', 'code' => 'PLASMA', 'sort' => 3],
            ['name' => 'Urine', 'code' => 'URINE', 'sort' => 4],
            ['name' => 'Feses', 'code' => 'FESES', 'sort' => 5],
        ];

        $now = now()->format('Y-m-d H:i:s');

        foreach ($defaults as $row) {
            $exists = DB::table('ms_jenis_sampel_klinik')
                ->where('name_jenis_sampel_klinik', $row['name'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('ms_jenis_sampel_klinik')->insert([
                'id_jenis_sampel_klinik' => Uuid::uuid4()->toString(),
                'name_jenis_sampel_klinik' => $row['name'],
                'code_jenis_sampel_klinik' => $row['code'],
                'is_active' => 1,
                'sort_order' => $row['sort'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }
}
