<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteAndKesmasIndexes extends Migration
{
    /**
     * Index deleted_at pada semua tabel yang menggunakan soft delete,
     * plus index kolom relasi kesmas yang belum tercakup migrasi sebelumnya.
     */
    public function up()
    {
        // ---------------------------------------------------------------
        // STATUS & FILTER COLUMNS — tb_permohonan_uji (kesmas)
        // ---------------------------------------------------------------
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            $table->index('status',            'idx_perm_uji_status');
            $table->index('status_pembayaran', 'idx_perm_uji_status_bayar');
            $table->index('is_sampling',       'idx_perm_uji_is_sampling');
            $table->index('date_permohonan_uji','idx_perm_uji_date');
            // Referensi wilayah sampling
            $table->index('provinsi_sampling',  'idx_perm_uji_provinsi');
            $table->index('kabupaten_sampling', 'idx_perm_uji_kabupaten');
            $table->index('kecamatan_sampling', 'idx_perm_uji_kecamatan');
            $table->index('desa_sampling',      'idx_perm_uji_desa');
        });

        // tb_samples (kesmas)
        Schema::table('tb_samples', function (Blueprint $table) {
            $table->index('status',      'idx_samples_status');
            $table->index('is_sampling', 'idx_samples_is_sampling');
            $table->index('date_done',   'idx_samples_date_done');
        });

        // tb_sample_draft (kesmas)
        Schema::table('tb_sample_draft', function (Blueprint $table) {
            $table->index('typesample_samples', 'idx_sample_draft_typesample');
            $table->index('program_samples',    'idx_sample_draft_program');
            $table->index('confirmed_by',       'idx_sample_draft_confirmed_by');
            $table->index('created_by',         'idx_sample_draft_created_by');
        });

        // tb_permohonan_uji_klinik_2 — status pembayaran
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->index('status_pembayaran', 'idx_puk2_status_bayar');
        });

        // ---------------------------------------------------------------
        // DELETED_AT — soft delete index untuk semua tabel relevan
        // ---------------------------------------------------------------
        $softDeleteTables = [
            'global_lab_sequence'                              => 'idx_gls_deleted_at',
            'global_lab_sequence_detail'                       => 'idx_gls_detail_deleted_at',
            'ms_code_lhu'                                      => 'idx_ms_code_lhu_deleted_at',
            'ms_container'                                     => 'idx_ms_container_deleted_at',
            'ms_customer'                                      => 'idx_ms_customer_deleted_at',
            'ms_industry'                                      => 'idx_ms_industry_deleted_at',
            'ms_jenis_makanan'                                 => 'idx_ms_jenis_makanan_deleted_at',
            'ms_laboratorium'                                  => 'idx_ms_lab_deleted_at',
            'ms_layout'                                        => 'idx_ms_layout_deleted_at',
            'ms_library'                                       => 'idx_ms_library_deleted_at',
            'ms_major'                                         => 'idx_ms_major_deleted_at',
            'ms_menuadm'                                       => 'idx_ms_menuadm_deleted_at',
            'ms_menus'                                         => 'idx_ms_menus_deleted_at',
            'ms_method'                                        => 'idx_ms_method_deleted_at',
            'ms_module'                                        => 'idx_ms_module_deleted_at',
            'ms_packet'                                        => 'idx_ms_packet_deleted_at',
            'ms_packet_detail'                                 => 'idx_ms_packet_detail_deleted_at',
            'ms_param_category_items'                          => 'idx_param_cat_items_deleted_at',
            'ms_param_category_layout'                         => 'idx_param_cat_layout_deleted_at',
            'ms_parameter_jenis_klinik'                        => 'idx_param_jenis_klinik_deleted_at',
            'ms_parameter_paket_extra'                         => 'idx_param_paket_extra_deleted_at',
            'ms_parameter_paket_jenis_klinik'                  => 'idx_param_paket_jenis_deleted_at',
            'ms_parameter_paket_klinik'                        => 'idx_param_paket_klinik_deleted_at',
            'ms_parameter_satuan_klinik'                       => 'idx_param_satuan_klinik_deleted_at',
            'ms_parameter_satuan_paket_klinik'                 => 'idx_param_satuan_paket_deleted_at',
            'ms_parameter_sub_paket_extra'                     => 'idx_param_sub_paket_deleted_at',
            'ms_parameter_sub_satuan_klinik'                   => 'idx_param_sub_satuan_deleted_at',
            'ms_pasien'                                        => 'idx_ms_pasien_deleted_at',
            'ms_privilege'                                     => 'idx_ms_privilege_deleted_at',
            'ms_product'                                       => 'idx_ms_product_deleted_at',
            'ms_program'                                       => 'idx_ms_program_deleted_at',
            'ms_rate'                                          => 'idx_ms_rate_deleted_at',
            'ms_sample_officer'                                => 'idx_ms_sample_officer_deleted_at',
            'ms_sample_type'                                   => 'idx_ms_sample_type_deleted_at',
            'ms_sample_type_detail'                            => 'idx_ms_sample_type_detail_deleted_at',
            'ms_sample_type_sarana'                            => 'idx_ms_sample_sarana_deleted_at',
            'ms_satusehat_location'                            => 'idx_ms_ss_location_deleted_at',
            'ms_satusehat_practitioner'                        => 'idx_ms_ss_pract_deleted_at',
            'ms_satusehat_setting'                             => 'idx_ms_ss_setting_deleted_at',
            'ms_slideshows'                                    => 'idx_ms_slideshows_deleted_at',
            'ms_socmeds'                                       => 'idx_ms_socmeds_deleted_at',
            'ms_start_number'                                  => 'idx_ms_start_num_deleted_at',
            'ms_type'                                          => 'idx_ms_type_deleted_at',
            'ms_unit'                                          => 'idx_ms_unit_deleted_at',
            'ms_user_devices'                                  => 'idx_ms_user_dev_deleted_at',
            'ms_user_tele'                                     => 'idx_ms_user_tele_deleted_at',
            'ms_users'                                         => 'idx_ms_users_deleted_at',
            'tb_baku_mutu'                                     => 'idx_baku_mutu_deleted_at',
            'tb_baku_mutu_detail_parameter_klinik'             => 'idx_bm_detail_klinik_deleted_at',
            'tb_baku_mutu_detail_parameter_non_klinik'         => 'idx_bm_detail_nonklinik_del',
            'tb_category_layanan'                              => 'idx_cat_layanan_deleted_at',
            'tb_category_portofolio'                           => 'idx_cat_porto_deleted_at',
            'tb_client'                                        => 'idx_tb_client_deleted_at',
            'tb_delegation'                                    => 'idx_delegation_deleted_at',
            'tb_delegation_sampling'                           => 'idx_deleg_sampling_deleted_at',
            'tb_invoice'                                       => 'idx_invoice_deleted_at',
            'tb_kebisingan'                                    => 'idx_kebisingan_deleted_at',
            'tb_kebisingan_detail'                             => 'idx_kebisingan_det_deleted_at',
            'tb_lab_num'                                       => 'idx_lab_num_deleted_at',
            'tb_laboratorium_method'                           => 'idx_lab_method_deleted_at',
            'tb_laboratorium_packet'                           => 'idx_lab_packet_deleted_at',
            'tb_laboratorium_progress'                         => 'idx_lab_progress_deleted_at',
            'tb_layanan'                                       => 'idx_layanan_deleted_at',
            'tb_lhu'                                           => 'idx_lhu_deleted_at',
            'tb_method_sampling'                               => 'idx_method_samp_deleted_at',
            'tb_number_klinik'                                 => 'idx_num_klinik_deleted_at',
            'tb_number_klinik_'                                => 'idx_num_klinik2_deleted_at',
            'tb_pelaporan_hasil'                               => 'idx_pelaporan_hasil_del',
            'tb_pencahayaan'                                   => 'idx_pencahayaan_deleted_at',
            'tb_pencahayaan_detail'                            => 'idx_pencahayaan_det_del',
            'tb_pengambilan_sample_klinik'                     => 'idx_pengambilan_klinik_del',
            'tb_pengesahan_hasil'                              => 'idx_pengesahan_hasil_del',
            'tb_pengetikan_hasil'                              => 'idx_pengetikan_hasil_del',
            'tb_permohonan_uji'                                => 'idx_perm_uji_deleted_at',
            'tb_permohonan_uji_analis_klinik'                  => 'idx_analis_klinik_deleted_at',
            'tb_permohonan_uji_klinik'                         => 'idx_perm_klinik_deleted_at',
            'tb_permohonan_uji_klinik_2'                       => 'idx_perm_klinik2_deleted_at',
            'tb_permohonan_uji_klinik_haji'                    => 'idx_perm_haji_deleted_at',
            'tb_permohonan_uji_klinik_prolanis'                => 'idx_perm_prolanis_deleted_at',
            'tb_permohonan_uji_klinik_prolanis_gula'           => 'idx_perm_prolan_gula_del',
            'tb_permohonan_uji_klinik_prolanis_urine'          => 'idx_perm_prolan_urine_del',
            'tb_permohonan_uji_paket_klinik'                   => 'idx_perm_paket_klinik_del',
            'tb_permohonan_uji_paket_klinik2'                  => 'idx_perm_paket_klinik2_del',
            'tb_permohonan_uji_parameter_klinik'               => 'idx_param_klinik_deleted_at',
            'tb_permohonan_uji_parameter_klinik_2'             => 'idx_param_klinik2_deleted_at',
            'tb_permohonan_uji_parameter_klinik_history'       => 'idx_param_klinik_hist_del',
            'tb_permohonan_uji_payment_klinik'                 => 'idx_payment_klinik_del',
            'tb_permohonan_uji_sub_parameter_klinik'           => 'idx_sub_param_klinik_del',
            'tb_permohonan_uji_sub_parameter_klinik_history'   => 'idx_sub_param_hist_del',
            'tb_privilege_menu'                                => 'idx_priv_menu_deleted_at',
            'tb_privilege_menu_role'                           => 'idx_priv_menu_role_del',
            'tb_productcategory'                               => 'idx_productcat_deleted_at',
            'tb_release'                                       => 'idx_release_deleted_at',
            'tb_report'                                        => 'idx_report_deleted_at',
            'tb_reportdetail'                                  => 'idx_reportdetail_deleted_at',
            'tb_request'                                       => 'idx_request_deleted_at',
            'tb_role'                                          => 'idx_role_deleted_at',
            'tb_sample_analitik_progress'                      => 'idx_analitik_prog_deleted_at',
            'tb_sample_draft'                                  => 'idx_sample_draft_deleted_at',
            'tb_sample_method'                                 => 'idx_sample_method_deleted_at',
            'tb_sample_method_draft'                           => 'idx_sample_method_draft_del',
            'tb_sample_penanganan'                             => 'idx_sample_penanganan_del',
            'tb_sample_penerimaan'                             => 'idx_sample_penerimaan_del',
            'tb_sample_result'                                 => 'idx_sample_result_deleted_at',
            'tb_sample_result_detail'                          => 'idx_sample_result_det_del',
            'tb_samples'                                       => 'idx_samples_deleted_at',
            'tb_services'                                      => 'idx_services_deleted_at',
            'tb_stock'                                         => 'idx_stock_deleted_at',
            'tb_stockopname'                                   => 'idx_stockopname_deleted_at',
            'tb_testimoni'                                     => 'idx_testimoni_deleted_at',
            'tb_tools'                                         => 'idx_tools_deleted_at',
            'tb_verifikasi_hasil'                              => 'idx_verifikasi_hasil_del',
        ];

        foreach ($softDeleteTables as $tableName => $indexName) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->index('deleted_at', $indexName);
            });
        }
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            $table->dropIndex('idx_perm_uji_status');
            $table->dropIndex('idx_perm_uji_status_bayar');
            $table->dropIndex('idx_perm_uji_is_sampling');
            $table->dropIndex('idx_perm_uji_date');
            $table->dropIndex('idx_perm_uji_provinsi');
            $table->dropIndex('idx_perm_uji_kabupaten');
            $table->dropIndex('idx_perm_uji_kecamatan');
            $table->dropIndex('idx_perm_uji_desa');
        });

        Schema::table('tb_samples', function (Blueprint $table) {
            $table->dropIndex('idx_samples_status');
            $table->dropIndex('idx_samples_is_sampling');
            $table->dropIndex('idx_samples_date_done');
        });

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            $table->dropIndex('idx_sample_draft_typesample');
            $table->dropIndex('idx_sample_draft_program');
            $table->dropIndex('idx_sample_draft_confirmed_by');
            $table->dropIndex('idx_sample_draft_created_by');
        });

        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropIndex('idx_puk2_status_bayar');
        });

        $softDeleteTables = [
            'global_lab_sequence'                              => 'idx_gls_deleted_at',
            'global_lab_sequence_detail'                       => 'idx_gls_detail_deleted_at',
            'ms_code_lhu'                                      => 'idx_ms_code_lhu_deleted_at',
            'ms_container'                                     => 'idx_ms_container_deleted_at',
            'ms_customer'                                      => 'idx_ms_customer_deleted_at',
            'ms_industry'                                      => 'idx_ms_industry_deleted_at',
            'ms_jenis_makanan'                                 => 'idx_ms_jenis_makanan_deleted_at',
            'ms_laboratorium'                                  => 'idx_ms_lab_deleted_at',
            'ms_layout'                                        => 'idx_ms_layout_deleted_at',
            'ms_library'                                       => 'idx_ms_library_deleted_at',
            'ms_major'                                         => 'idx_ms_major_deleted_at',
            'ms_menuadm'                                       => 'idx_ms_menuadm_deleted_at',
            'ms_menus'                                         => 'idx_ms_menus_deleted_at',
            'ms_method'                                        => 'idx_ms_method_deleted_at',
            'ms_module'                                        => 'idx_ms_module_deleted_at',
            'ms_packet'                                        => 'idx_ms_packet_deleted_at',
            'ms_packet_detail'                                 => 'idx_ms_packet_detail_deleted_at',
            'ms_param_category_items'                          => 'idx_param_cat_items_deleted_at',
            'ms_param_category_layout'                         => 'idx_param_cat_layout_deleted_at',
            'ms_parameter_jenis_klinik'                        => 'idx_param_jenis_klinik_deleted_at',
            'ms_parameter_paket_extra'                         => 'idx_param_paket_extra_deleted_at',
            'ms_parameter_paket_jenis_klinik'                  => 'idx_param_paket_jenis_deleted_at',
            'ms_parameter_paket_klinik'                        => 'idx_param_paket_klinik_deleted_at',
            'ms_parameter_satuan_klinik'                       => 'idx_param_satuan_klinik_deleted_at',
            'ms_parameter_satuan_paket_klinik'                 => 'idx_param_satuan_paket_deleted_at',
            'ms_parameter_sub_paket_extra'                     => 'idx_param_sub_paket_deleted_at',
            'ms_parameter_sub_satuan_klinik'                   => 'idx_param_sub_satuan_deleted_at',
            'ms_pasien'                                        => 'idx_ms_pasien_deleted_at',
            'ms_privilege'                                     => 'idx_ms_privilege_deleted_at',
            'ms_product'                                       => 'idx_ms_product_deleted_at',
            'ms_program'                                       => 'idx_ms_program_deleted_at',
            'ms_rate'                                          => 'idx_ms_rate_deleted_at',
            'ms_sample_officer'                                => 'idx_ms_sample_officer_deleted_at',
            'ms_sample_type'                                   => 'idx_ms_sample_type_deleted_at',
            'ms_sample_type_detail'                            => 'idx_ms_sample_type_detail_deleted_at',
            'ms_sample_type_sarana'                            => 'idx_ms_sample_sarana_deleted_at',
            'ms_satusehat_location'                            => 'idx_ms_ss_location_deleted_at',
            'ms_satusehat_practitioner'                        => 'idx_ms_ss_pract_deleted_at',
            'ms_satusehat_setting'                             => 'idx_ms_ss_setting_deleted_at',
            'ms_slideshows'                                    => 'idx_ms_slideshows_deleted_at',
            'ms_socmeds'                                       => 'idx_ms_socmeds_deleted_at',
            'ms_start_number'                                  => 'idx_ms_start_num_deleted_at',
            'ms_type'                                          => 'idx_ms_type_deleted_at',
            'ms_unit'                                          => 'idx_ms_unit_deleted_at',
            'ms_user_devices'                                  => 'idx_ms_user_dev_deleted_at',
            'ms_user_tele'                                     => 'idx_ms_user_tele_deleted_at',
            'ms_users'                                         => 'idx_ms_users_deleted_at',
            'tb_baku_mutu'                                     => 'idx_baku_mutu_deleted_at',
            'tb_baku_mutu_detail_parameter_klinik'             => 'idx_bm_detail_klinik_deleted_at',
            'tb_baku_mutu_detail_parameter_non_klinik'         => 'idx_bm_detail_nonklinik_del',
            'tb_category_layanan'                              => 'idx_cat_layanan_deleted_at',
            'tb_category_portofolio'                           => 'idx_cat_porto_deleted_at',
            'tb_client'                                        => 'idx_tb_client_deleted_at',
            'tb_delegation'                                    => 'idx_delegation_deleted_at',
            'tb_delegation_sampling'                           => 'idx_deleg_sampling_deleted_at',
            'tb_invoice'                                       => 'idx_invoice_deleted_at',
            'tb_kebisingan'                                    => 'idx_kebisingan_deleted_at',
            'tb_kebisingan_detail'                              => 'idx_kebisingan_det_deleted_at',
            'tb_lab_num'                                       => 'idx_lab_num_deleted_at',
            'tb_laboratorium_method'                           => 'idx_lab_method_deleted_at',
            'tb_laboratorium_packet'                           => 'idx_lab_packet_deleted_at',
            'tb_laboratorium_progress'                         => 'idx_lab_progress_deleted_at',
            'tb_layanan'                                       => 'idx_layanan_deleted_at',
            'tb_lhu'                                           => 'idx_lhu_deleted_at',
            'tb_method_sampling'                               => 'idx_method_samp_deleted_at',
            'tb_number_klinik'                                 => 'idx_num_klinik_deleted_at',
            'tb_number_klinik_'                                => 'idx_num_klinik2_deleted_at',
            'tb_pelaporan_hasil'                               => 'idx_pelaporan_hasil_del',
            'tb_pencahayaan'                                   => 'idx_pencahayaan_deleted_at',
            'tb_pencahayaan_detail'                            => 'idx_pencahayaan_det_del',
            'tb_pengambilan_sample_klinik'                     => 'idx_pengambilan_klinik_del',
            'tb_pengesahan_hasil'                              => 'idx_pengesahan_hasil_del',
            'tb_pengetikan_hasil'                              => 'idx_pengetikan_hasil_del',
            'tb_permohonan_uji'                                => 'idx_perm_uji_deleted_at',
            'tb_permohonan_uji_analis_klinik'                  => 'idx_analis_klinik_deleted_at',
            'tb_permohonan_uji_klinik'                         => 'idx_perm_klinik_deleted_at',
            'tb_permohonan_uji_klinik_2'                       => 'idx_perm_klinik2_deleted_at',
            'tb_permohonan_uji_klinik_haji'                    => 'idx_perm_haji_deleted_at',
            'tb_permohonan_uji_klinik_prolanis'                => 'idx_perm_prolanis_deleted_at',
            'tb_permohonan_uji_klinik_prolanis_gula'           => 'idx_perm_prolan_gula_del',
            'tb_permohonan_uji_klinik_prolanis_urine'          => 'idx_perm_prolan_urine_del',
            'tb_permohonan_uji_paket_klinik'                   => 'idx_perm_paket_klinik_del',
            'tb_permohonan_uji_paket_klinik2'                  => 'idx_perm_paket_klinik2_del',
            'tb_permohonan_uji_parameter_klinik'               => 'idx_param_klinik_deleted_at',
            'tb_permohonan_uji_parameter_klinik_2'             => 'idx_param_klinik2_deleted_at',
            'tb_permohonan_uji_parameter_klinik_history'       => 'idx_param_klinik_hist_del',
            'tb_permohonan_uji_payment_klinik'                 => 'idx_payment_klinik_del',
            'tb_permohonan_uji_sub_parameter_klinik'           => 'idx_sub_param_klinik_del',
            'tb_permohonan_uji_sub_parameter_klinik_history'   => 'idx_sub_param_hist_del',
            'tb_privilege_menu'                                => 'idx_priv_menu_deleted_at',
            'tb_privilege_menu_role'                           => 'idx_priv_menu_role_del',
            'tb_productcategory'                               => 'idx_productcat_deleted_at',
            'tb_release'                                       => 'idx_release_deleted_at',
            'tb_report'                                        => 'idx_report_deleted_at',
            'tb_reportdetail'                                  => 'idx_reportdetail_deleted_at',
            'tb_request'                                       => 'idx_request_deleted_at',
            'tb_role'                                          => 'idx_role_deleted_at',
            'tb_sample_analitik_progress'                      => 'idx_analitik_prog_deleted_at',
            'tb_sample_draft'                                  => 'idx_sample_draft_deleted_at',
            'tb_sample_method'                                 => 'idx_sample_method_deleted_at',
            'tb_sample_method_draft'                           => 'idx_sample_method_draft_del',
            'tb_sample_penanganan'                             => 'idx_sample_penanganan_del',
            'tb_sample_penerimaan'                             => 'idx_sample_penerimaan_del',
            'tb_sample_result'                                 => 'idx_sample_result_deleted_at',
            'tb_sample_result_detail'                          => 'idx_sample_result_det_del',
            'tb_samples'                                       => 'idx_samples_deleted_at',
            'tb_services'                                      => 'idx_services_deleted_at',
            'tb_stock'                                         => 'idx_stock_deleted_at',
            'tb_stockopname'                                   => 'idx_stockopname_deleted_at',
            'tb_testimoni'                                     => 'idx_testimoni_deleted_at',
            'tb_tools'                                         => 'idx_tools_deleted_at',
            'tb_verifikasi_hasil'                              => 'idx_verifikasi_hasil_del',
        ];

        foreach ($softDeleteTables as $tableName => $indexName) {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
}
