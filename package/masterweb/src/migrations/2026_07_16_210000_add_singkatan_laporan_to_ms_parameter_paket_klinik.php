<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSingkatanLaporanToMsParameterPaketKlinik extends Migration
{
    /**
     * Singkatan untuk laporan klinik harian/tahunan.
     * Paket agregat (Kimia Klinik panel, Urin Rutin, Narkoba, Darah Rutin) ditandai is_agregat_laporan=1.
     */
    public function up()
    {
        Schema::table('ms_parameter_paket_klinik', function (Blueprint $table) {
            if (!Schema::hasColumn('ms_parameter_paket_klinik', 'singkatan_laporan')) {
                $table->string('singkatan_laporan', 80)->nullable()->after('name_parameter_paket_klinik');
            }
            if (!Schema::hasColumn('ms_parameter_paket_klinik', 'is_agregat_laporan')) {
                $table->boolean('is_agregat_laporan')->default(0)->after('singkatan_laporan');
            }
            if (!Schema::hasColumn('ms_parameter_paket_klinik', 'kategori_laporan')) {
                // kimia = baris di bawah "Kimia klinik"; lain = baris sendiri; null = auto
                $table->string('kategori_laporan', 20)->nullable()->after('is_agregat_laporan');
            }
            if (!Schema::hasColumn('ms_parameter_paket_klinik', 'tampil_di_laporan')) {
                $table->boolean('tampil_di_laporan')->default(1)->after('kategori_laporan');
            }
        });

        $this->seedKnownMappings();
    }

    private function seedKnownMappings(): void
    {
        $maps = [
            // Kimia (satuan/paket tunggal)
            ['like' => 'Gula Darah Puasa%', 'singkatan' => 'GDN', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Gula Darah 2 Jam PP%', 'singkatan' => 'GD 2 Jam PP', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Gula Darah Sewaktu%', 'singkatan' => 'GDS', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'HbA1c%', 'singkatan' => 'HbA1c', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Cholesterol Total%', 'singkatan' => 'Cholesterol', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Cholesterol LDL%', 'singkatan' => 'LDL', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Cholesterol HDL%', 'singkatan' => 'HDL', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Trigliserid%', 'singkatan' => 'Trigliserid', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Asam Urat%', 'singkatan' => 'Asam Urat', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Ureum%', 'singkatan' => 'Ureum', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Kreatinin%', 'singkatan' => 'Creatinin', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'Creatinin%', 'singkatan' => 'Creatinin', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'SGOT%', 'singkatan' => 'SGOT', 'kategori' => 'kimia', 'agregat' => 0],
            ['like' => 'SGPT%', 'singkatan' => 'SGPT', 'kategori' => 'kimia', 'agregat' => 0],

            // Lain (bukan agregat gabungan)
            ['like' => 'HB (Hemoglobin)%', 'singkatan' => 'Hemoglobin', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Hemoglobin%', 'singkatan' => 'Hemoglobin', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'LED%', 'singkatan' => 'LED', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Widal%', 'singkatan' => 'Widal', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Golongan Darah%', 'singkatan' => 'Golongan darah', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'HBsAg%', 'singkatan' => 'HBsAg', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'PP Tes%', 'singkatan' => 'Tes Kehamilan', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Antigen NS1%', 'singkatan' => 'NS1', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Dengue IgG/IgM%', 'singkatan' => 'Dengue IgG/IgM', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Salmonella Typhi IgG/IgM%', 'singkatan' => 'Typhi IgG/IgM', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Feses%', 'singkatan' => 'Feses', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Pemeriksaan Feses%', 'singkatan' => 'Feses', 'kategori' => 'lain', 'agregat' => 0],
            ['like' => 'Croschek%', 'singkatan' => 'Croschek TB', 'kategori' => 'lain', 'agregat' => 0],

            // Agregat (gabungan beberapa parameter) — tidak dipecah per satuan
            ['like' => 'Darah Rutin%', 'singkatan' => 'Darah rutin', 'kategori' => 'lain', 'agregat' => 1],
            ['like' => 'Urin Rutin%', 'singkatan' => 'Urin rutin', 'kategori' => 'lain', 'agregat' => 1],
            ['like' => 'Tes Narkoba%', 'singkatan' => 'Tes Narkoba', 'kategori' => 'lain', 'agregat' => 1],
            ['like' => 'Kimia Klinik%', 'singkatan' => 'Kimia klinik', 'kategori' => 'kimia', 'agregat' => 1],
        ];

        foreach ($maps as $map) {
            DB::table('ms_parameter_paket_klinik')
                ->whereNull('deleted_at')
                ->where('name_parameter_paket_klinik', 'like', $map['like'])
                ->where(function ($q) {
                    $q->whereNull('singkatan_laporan')->orWhere('singkatan_laporan', '=', '');
                })
                ->update([
                    'singkatan_laporan' => $map['singkatan'],
                    'kategori_laporan' => $map['kategori'],
                    'is_agregat_laporan' => $map['agregat'],
                ]);
        }
    }

    public function down()
    {
        Schema::table('ms_parameter_paket_klinik', function (Blueprint $table) {
            if (Schema::hasColumn('ms_parameter_paket_klinik', 'tampil_di_laporan')) {
                $table->dropColumn('tampil_di_laporan');
            }
            if (Schema::hasColumn('ms_parameter_paket_klinik', 'kategori_laporan')) {
                $table->dropColumn('kategori_laporan');
            }
            if (Schema::hasColumn('ms_parameter_paket_klinik', 'is_agregat_laporan')) {
                $table->dropColumn('is_agregat_laporan');
            }
            if (Schema::hasColumn('ms_parameter_paket_klinik', 'singkatan_laporan')) {
                $table->dropColumn('singkatan_laporan');
            }
        });
    }
}
