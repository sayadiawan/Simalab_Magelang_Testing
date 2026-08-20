<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingIndexesToRelationalTables extends Migration
{
    public function up()
    {
        // global_lab_sequence_detail
        Schema::table('global_lab_sequence_detail', function (Blueprint $table) {
            $table->index('reference_id', 'idx_gls_detail_reference_id');
        });

        // ms_laboratorium
        Schema::table('ms_laboratorium', function (Blueprint $table) {
            $table->index('koordinator_id', 'idx_ms_lab_koordinator_id');
        });

        // ms_parameter_sub_paket_extra
        Schema::table('ms_parameter_sub_paket_extra', function (Blueprint $table) {
            $table->index('id_parameter_paket_extra', 'idx_sub_paket_extra_paket_extra_id');
            $table->index('id_parameter_paket_klinik', 'idx_sub_paket_extra_paket_klinik_id');
        });

        // ms_pasien
        Schema::table('ms_pasien', function (Blueprint $table) {
            $table->index('wilayah_id', 'idx_ms_pasien_wilayah_id');
        });

        // ms_user_devices
        Schema::table('ms_user_devices', function (Blueprint $table) {
            $table->index('user_id', 'idx_ms_user_devices_user_id');
            $table->index('device_id', 'idx_ms_user_devices_device_id');
        });

        // ms_user_tele
        Schema::table('ms_user_tele', function (Blueprint $table) {
            $table->index('user_id', 'idx_ms_user_tele_user_id');
        });

        // ms_users
        Schema::table('ms_users', function (Blueprint $table) {
            $table->index('id_petugas', 'idx_ms_users_id_petugas');
            $table->index('petugas_id', 'idx_ms_users_petugas_id');
        });

        // sessions
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id', 'idx_sessions_user_id');
        });

        // tb_baku_mutu_sample_override (method_id is 2nd in composite unique, add single index for queries)
        Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
            $table->index('method_id', 'idx_baku_mutu_override_method_id');
        });

        // tb_delegation
        Schema::table('tb_delegation', function (Blueprint $table) {
            $table->index('id_delegation', 'idx_delegation_id_delegation');
            $table->index('id_method', 'idx_delegation_id_method');
            $table->index('id_samples', 'idx_delegation_id_samples');
        });

        // tb_delegation_sampling
        Schema::table('tb_delegation_sampling', function (Blueprint $table) {
            $table->index('delegation_sampling_id', 'idx_deleg_sampling_delegation_id');
            $table->index('method_id', 'idx_deleg_sampling_method_id');
            $table->index('permohonan_uji_id', 'idx_deleg_sampling_permohonan_id');
        });

        // tb_kebisingan
        Schema::table('tb_kebisingan', function (Blueprint $table) {
            $table->index('permohonan_uji_id', 'idx_kebisingan_permohonan_id');
        });

        // tb_kebisingan_detail
        Schema::table('tb_kebisingan_detail', function (Blueprint $table) {
            $table->index('kebisingan_id', 'idx_kebisingan_detail_kebisingan_id');
        });

        // tb_layanan
        Schema::table('tb_layanan', function (Blueprint $table) {
            $table->index('menu_id', 'idx_tb_layanan_menu_id');
        });

        // tb_lhu
        Schema::table('tb_lhu', function (Blueprint $table) {
            $table->index('permohonan_uji_klinik_id', 'idx_lhu_permohonan_klinik_id');
        });

        // tb_method_sampling
        Schema::table('tb_method_sampling', function (Blueprint $table) {
            $table->index('method_id', 'idx_method_sampling_method_id');
            $table->index('permohonan_uji_id', 'idx_method_sampling_permohonan_id');
        });

        // tb_number_klinik
        Schema::table('tb_number_klinik', function (Blueprint $table) {
            $table->index('id_permohonan_uji_klinik', 'idx_num_klinik_permohonan_id');
            $table->index('id_haji', 'idx_num_klinik_id_haji');
            $table->index('id_prolanis', 'idx_num_klinik_id_prolanis');
            $table->index('id_prolanis_gula', 'idx_num_klinik_id_prolanis_gula');
            $table->index('id_prolanis_urine', 'idx_num_klinik_id_prolanis_urine');
        });

        // tb_number_klinik_ (duplicate/legacy table)
        Schema::table('tb_number_klinik_', function (Blueprint $table) {
            $table->index('id_permohonan_uji_klinik', 'idx_num_klinik2_permohonan_id');
            $table->index('id_haji', 'idx_num_klinik2_id_haji');
            $table->index('id_prolanis', 'idx_num_klinik2_id_prolanis');
            $table->index('id_prolanis_gula', 'idx_num_klinik2_id_prolanis_gula');
            $table->index('id_prolanis_urine', 'idx_num_klinik2_id_prolanis_urine');
        });

        // tb_pelaporan_hasil
        Schema::table('tb_pelaporan_hasil', function (Blueprint $table) {
            $table->index('laboratorium_id', 'idx_pelaporan_hasil_lab_id');
        });

        // tb_pencahayaan
        Schema::table('tb_pencahayaan', function (Blueprint $table) {
            $table->index('permohonan_uji_id', 'idx_pencahayaan_permohonan_id');
        });

        // tb_pencahayaan_detail
        Schema::table('tb_pencahayaan_detail', function (Blueprint $table) {
            $table->index('pencahayaan_id', 'idx_pencahayaan_detail_pencahayaan_id');
        });

        // tb_pengambilan_sample
        Schema::table('tb_pengambilan_sample', function (Blueprint $table) {
            $table->index('permohonan_uji_id', 'idx_pengambilan_sample_permohonan_id');
        });

        // tb_pengambilan_sample_klinik
        Schema::table('tb_pengambilan_sample_klinik', function (Blueprint $table) {
            $table->index('petugas_id', 'idx_pengambilan_klinik_petugas_id');
        });

        // tb_pengesahan_hasil
        Schema::table('tb_pengesahan_hasil', function (Blueprint $table) {
            $table->index('laboratorium_id', 'idx_pengesahan_hasil_lab_id');
        });

        // tb_pengetikan_hasil
        Schema::table('tb_pengetikan_hasil', function (Blueprint $table) {
            $table->index('laboratorium_id', 'idx_pengetikan_hasil_lab_id');
        });

        // tb_permohonan_uji_analis_klinik
        Schema::table('tb_permohonan_uji_analis_klinik', function (Blueprint $table) {
            $table->index('permohonan_uji_klinik_id', 'idx_analis_klinik_permohonan_id');
        });

        // tb_permohonan_uji_klinik_2
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->index('id_permohonan_uji_klinik_haji', 'idx_puk2_id_haji');
            $table->index('id_permohonan_uji_klinik_prolanis', 'idx_puk2_id_prolanis');
        });

        // tb_permohonan_uji_paket_klinik2
        Schema::table('tb_permohonan_uji_paket_klinik2', function (Blueprint $table) {
            $table->index('id_permohonan_uji_klinik', 'idx_paket_klinik2_permohonan_id');
            $table->index('id_jenis_parameter_klinik', 'idx_paket_klinik2_jenis_param_id');
        });

        // tb_permohonan_uji_parameter_klinik
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            $table->index('jenis_parameter_klinik_id', 'idx_param_klinik_jenis_param_id');
        });

        // tb_permohonan_uji_parameter_klinik_2
        Schema::table('tb_permohonan_uji_parameter_klinik_2', function (Blueprint $table) {
            $table->index('id_permohonan_uji_klinik', 'idx_param_klinik2_permohonan_id');
            $table->index('id_permohonan_uji_paket_klinik', 'idx_param_klinik2_paket_id');
            $table->index('id_jenis_parameter_klinik', 'idx_param_klinik2_jenis_param_id');
            $table->index('id_parameter_paket_klinik', 'idx_param_klinik2_paket_klinik_id');
        });

        // tb_permohonan_uji_payment_klinik
        Schema::table('tb_permohonan_uji_payment_klinik', function (Blueprint $table) {
            $table->index('permohonan_uji_klinik_id', 'idx_payment_klinik_permohonan_id');
        });

        // tb_permohonan_uji_sub_parameter_klinik
        Schema::table('tb_permohonan_uji_sub_parameter_klinik', function (Blueprint $table) {
            $table->index('permohonan_uji_parameter_klinik_id', 'idx_sub_param_klinik_param_id');
            $table->index('parameter_sub_satuan_klinik_id', 'idx_sub_param_klinik_satuan_id');
            $table->index('selected_history_id', 'idx_sub_param_klinik_history_id');
        });

        // tb_privilege_menu
        Schema::table('tb_privilege_menu', function (Blueprint $table) {
            $table->index('menu_id', 'idx_privilege_menu_menu_id');
        });

        // tb_privilege_menu_role
        Schema::table('tb_privilege_menu_role', function (Blueprint $table) {
            $table->index('id_privilege_menu', 'idx_priv_menu_role_privilege_menu');
            $table->index('privilege_id', 'idx_priv_menu_role_privilege_id');
        });

        // tb_productcategory
        Schema::table('tb_productcategory', function (Blueprint $table) {
            $table->index('service_id', 'idx_productcategory_service_id');
        });

        // tb_role
        Schema::table('tb_role', function (Blueprint $table) {
            $table->index('menu_id', 'idx_tb_role_menu_id');
            $table->index('privilege_id', 'idx_tb_role_privilege_id');
        });

        // tb_sample_analitik_progress (separate indexes for single-column queries)
        Schema::table('tb_sample_analitik_progress', function (Blueprint $table) {
            $table->index('laboratorium_id', 'idx_analitik_progress_lab_id');
            $table->index('sample_id', 'idx_analitik_progress_sample_id');
        });

        // tb_sample_draft
        Schema::table('tb_sample_draft', function (Blueprint $table) {
            $table->index('packet_id', 'idx_sample_draft_packet_id');
        });

        // tb_sample_method_draft
        Schema::table('tb_sample_method_draft', function (Blueprint $table) {
            $table->index('sample_draft_id', 'idx_sample_method_draft_draft_id');
            $table->index('laboratorium_id', 'idx_sample_method_draft_lab_id');
            $table->index('method_id', 'idx_sample_method_draft_method_id');
        });

        // tb_sample_result
        Schema::table('tb_sample_result', function (Blueprint $table) {
            $table->index('sample_id', 'idx_sample_result_sample_id');
            $table->index('laboratorium_id', 'idx_sample_result_lab_id');
            $table->index('method_id', 'idx_sample_result_method_id');
        });

        // tb_sample_result_detail
        Schema::table('tb_sample_result_detail', function (Blueprint $table) {
            $table->index('sample_id', 'idx_sample_result_detail_sample_id');
            $table->index('lab_id', 'idx_sample_result_detail_lab_id');
            $table->index('method_id', 'idx_sample_result_detail_method_id');
            $table->index('sampletype_id', 'idx_sample_result_detail_type_id');
        });

        // tb_services
        Schema::table('tb_services', function (Blueprint $table) {
            $table->index('menu_id', 'idx_tb_services_menu_id');
        });

        // tb_verifikasi_hasil
        Schema::table('tb_verifikasi_hasil', function (Blueprint $table) {
            $table->index('laboratorium_id', 'idx_verifikasi_hasil_lab_id');
        });
    }

    public function down()
    {
        Schema::table('global_lab_sequence_detail', function (Blueprint $table) {
            $table->dropIndex('idx_gls_detail_reference_id');
        });

        Schema::table('ms_laboratorium', function (Blueprint $table) {
            $table->dropIndex('idx_ms_lab_koordinator_id');
        });

        Schema::table('ms_parameter_sub_paket_extra', function (Blueprint $table) {
            $table->dropIndex('idx_sub_paket_extra_paket_extra_id');
            $table->dropIndex('idx_sub_paket_extra_paket_klinik_id');
        });

        Schema::table('ms_pasien', function (Blueprint $table) {
            $table->dropIndex('idx_ms_pasien_wilayah_id');
        });

        Schema::table('ms_user_devices', function (Blueprint $table) {
            $table->dropIndex('idx_ms_user_devices_user_id');
            $table->dropIndex('idx_ms_user_devices_device_id');
        });

        Schema::table('ms_user_tele', function (Blueprint $table) {
            $table->dropIndex('idx_ms_user_tele_user_id');
        });

        Schema::table('ms_users', function (Blueprint $table) {
            $table->dropIndex('idx_ms_users_id_petugas');
            $table->dropIndex('idx_ms_users_petugas_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_user_id');
        });

        Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
            $table->dropIndex('idx_baku_mutu_override_method_id');
        });

        Schema::table('tb_delegation', function (Blueprint $table) {
            $table->dropIndex('idx_delegation_id_delegation');
            $table->dropIndex('idx_delegation_id_method');
            $table->dropIndex('idx_delegation_id_samples');
        });

        Schema::table('tb_delegation_sampling', function (Blueprint $table) {
            $table->dropIndex('idx_deleg_sampling_delegation_id');
            $table->dropIndex('idx_deleg_sampling_method_id');
            $table->dropIndex('idx_deleg_sampling_permohonan_id');
        });

        Schema::table('tb_kebisingan', function (Blueprint $table) {
            $table->dropIndex('idx_kebisingan_permohonan_id');
        });

        Schema::table('tb_kebisingan_detail', function (Blueprint $table) {
            $table->dropIndex('idx_kebisingan_detail_kebisingan_id');
        });

        Schema::table('tb_layanan', function (Blueprint $table) {
            $table->dropIndex('idx_tb_layanan_menu_id');
        });

        Schema::table('tb_lhu', function (Blueprint $table) {
            $table->dropIndex('idx_lhu_permohonan_klinik_id');
        });

        Schema::table('tb_method_sampling', function (Blueprint $table) {
            $table->dropIndex('idx_method_sampling_method_id');
            $table->dropIndex('idx_method_sampling_permohonan_id');
        });

        Schema::table('tb_number_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_num_klinik_permohonan_id');
            $table->dropIndex('idx_num_klinik_id_haji');
            $table->dropIndex('idx_num_klinik_id_prolanis');
            $table->dropIndex('idx_num_klinik_id_prolanis_gula');
            $table->dropIndex('idx_num_klinik_id_prolanis_urine');
        });

        Schema::table('tb_number_klinik_', function (Blueprint $table) {
            $table->dropIndex('idx_num_klinik2_permohonan_id');
            $table->dropIndex('idx_num_klinik2_id_haji');
            $table->dropIndex('idx_num_klinik2_id_prolanis');
            $table->dropIndex('idx_num_klinik2_id_prolanis_gula');
            $table->dropIndex('idx_num_klinik2_id_prolanis_urine');
        });

        Schema::table('tb_pelaporan_hasil', function (Blueprint $table) {
            $table->dropIndex('idx_pelaporan_hasil_lab_id');
        });

        Schema::table('tb_pencahayaan', function (Blueprint $table) {
            $table->dropIndex('idx_pencahayaan_permohonan_id');
        });

        Schema::table('tb_pencahayaan_detail', function (Blueprint $table) {
            $table->dropIndex('idx_pencahayaan_detail_pencahayaan_id');
        });

        Schema::table('tb_pengambilan_sample', function (Blueprint $table) {
            $table->dropIndex('idx_pengambilan_sample_permohonan_id');
        });

        Schema::table('tb_pengambilan_sample_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_pengambilan_klinik_petugas_id');
        });

        Schema::table('tb_pengesahan_hasil', function (Blueprint $table) {
            $table->dropIndex('idx_pengesahan_hasil_lab_id');
        });

        Schema::table('tb_pengetikan_hasil', function (Blueprint $table) {
            $table->dropIndex('idx_pengetikan_hasil_lab_id');
        });

        Schema::table('tb_permohonan_uji_analis_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_analis_klinik_permohonan_id');
        });

        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropIndex('idx_puk2_id_haji');
            $table->dropIndex('idx_puk2_id_prolanis');
        });

        Schema::table('tb_permohonan_uji_paket_klinik2', function (Blueprint $table) {
            $table->dropIndex('idx_paket_klinik2_permohonan_id');
            $table->dropIndex('idx_paket_klinik2_jenis_param_id');
        });

        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_param_klinik_jenis_param_id');
        });

        Schema::table('tb_permohonan_uji_parameter_klinik_2', function (Blueprint $table) {
            $table->dropIndex('idx_param_klinik2_permohonan_id');
            $table->dropIndex('idx_param_klinik2_paket_id');
            $table->dropIndex('idx_param_klinik2_jenis_param_id');
            $table->dropIndex('idx_param_klinik2_paket_klinik_id');
        });

        Schema::table('tb_permohonan_uji_payment_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_payment_klinik_permohonan_id');
        });

        Schema::table('tb_permohonan_uji_sub_parameter_klinik', function (Blueprint $table) {
            $table->dropIndex('idx_sub_param_klinik_param_id');
            $table->dropIndex('idx_sub_param_klinik_satuan_id');
            $table->dropIndex('idx_sub_param_klinik_history_id');
        });

        Schema::table('tb_privilege_menu', function (Blueprint $table) {
            $table->dropIndex('idx_privilege_menu_menu_id');
        });

        Schema::table('tb_privilege_menu_role', function (Blueprint $table) {
            $table->dropIndex('idx_priv_menu_role_privilege_menu');
            $table->dropIndex('idx_priv_menu_role_privilege_id');
        });

        Schema::table('tb_productcategory', function (Blueprint $table) {
            $table->dropIndex('idx_productcategory_service_id');
        });

        Schema::table('tb_role', function (Blueprint $table) {
            $table->dropIndex('idx_tb_role_menu_id');
            $table->dropIndex('idx_tb_role_privilege_id');
        });

        Schema::table('tb_sample_analitik_progress', function (Blueprint $table) {
            $table->dropIndex('idx_analitik_progress_lab_id');
            $table->dropIndex('idx_analitik_progress_sample_id');
        });

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            $table->dropIndex('idx_sample_draft_packet_id');
        });

        Schema::table('tb_sample_method_draft', function (Blueprint $table) {
            $table->dropIndex('idx_sample_method_draft_draft_id');
            $table->dropIndex('idx_sample_method_draft_lab_id');
            $table->dropIndex('idx_sample_method_draft_method_id');
        });

        Schema::table('tb_sample_result', function (Blueprint $table) {
            $table->dropIndex('idx_sample_result_sample_id');
            $table->dropIndex('idx_sample_result_lab_id');
            $table->dropIndex('idx_sample_result_method_id');
        });

        Schema::table('tb_sample_result_detail', function (Blueprint $table) {
            $table->dropIndex('idx_sample_result_detail_sample_id');
            $table->dropIndex('idx_sample_result_detail_lab_id');
            $table->dropIndex('idx_sample_result_detail_method_id');
            $table->dropIndex('idx_sample_result_detail_type_id');
        });

        Schema::table('tb_services', function (Blueprint $table) {
            $table->dropIndex('idx_tb_services_menu_id');
        });

        Schema::table('tb_verifikasi_hasil', function (Blueprint $table) {
            $table->dropIndex('idx_verifikasi_hasil_lab_id');
        });
    }
}
