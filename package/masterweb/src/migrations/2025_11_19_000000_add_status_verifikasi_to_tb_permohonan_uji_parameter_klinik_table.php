<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusVerifikasiToTbPermohonanUjiParameterKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cek apakah kolom sudah ada
        $hasStatusVerifikasi = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'status_verifikasi');
        $hasKomentarVerifikasi = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'komentar_verifikasi');
        
        // Jika kolom sudah ada, skip
        if ($hasStatusVerifikasi && $hasKomentarVerifikasi) {
            return;
        }
        
        // Cek kolom referensi untuk posisi
        $hasOffsetBakuMutu = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'offset_baku_mutu');
        $hasKeterangan = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'keterangan_permohonan_uji_parameter_klinik');
        
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) use ($hasOffsetBakuMutu, $hasKeterangan, $hasStatusVerifikasi, $hasKomentarVerifikasi) {
            if (!$hasStatusVerifikasi) {
            if ($hasOffsetBakuMutu) {
                $table->string('status_verifikasi', 50)->nullable()->after('offset_baku_mutu')->default('approved');
            } elseif ($hasKeterangan) {
                $table->string('status_verifikasi', 50)->nullable()->after('keterangan_permohonan_uji_parameter_klinik')->default('approved');
                } else {
                    $table->string('status_verifikasi', 50)->nullable()->default('approved');
                }
            }
            
            if (!$hasKomentarVerifikasi) {
                if ($hasStatusVerifikasi || Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'status_verifikasi')) {
                $table->text('komentar_verifikasi')->nullable()->after('status_verifikasi');
            } else {
                $table->text('komentar_verifikasi')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'komentar_verifikasi')) {
                $table->dropColumn('komentar_verifikasi');
            }
            if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'status_verifikasi')) {
                $table->dropColumn('status_verifikasi');
            }
        });
    }
}