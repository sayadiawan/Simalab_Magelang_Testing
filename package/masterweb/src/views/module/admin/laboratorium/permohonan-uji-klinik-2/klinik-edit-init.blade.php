<script>
    window.KLINIK_IS_EDIT = true;
    window.KLINIK_EDIT_DATA = @json($klinikEditData);

    $(document).ready(function() {
        if (!window.KLINIK_EDIT_DATA) {
            return;
        }

        var d = window.KLINIK_EDIT_DATA;

        // Haji: dokter rujukan otomatis, langsung ke informasi permohonan
        if (d.is_haji) {
            d.doctor_type = 'rujukan';
            $('.doctor-type-card[data-type="lab"]').hide();
        }
        if (d.doctor_type) {
            $('.doctor-type-card').removeClass('selected');
            $('.doctor-type-card[data-type="' + d.doctor_type + '"]').addClass('selected');
            $('#doctor_type').val(d.doctor_type).trigger('change');
            selectedDoctorType = d.doctor_type;
            $('#btn-next-step-1').prop('disabled', false);
        }
        if (d.is_haji && typeof goToStep === 'function') {
            goToStep(3);
        }

        setTimeout(function() {
            d = window.KLINIK_EDIT_DATA;

            // Step 2: pasien
            if (d.pasien && d.pasien.id_pasien) {
                var tgllahirUi = d.pasien.tgllahir_pasien || '';
                if (/^\d{4}-\d{2}-\d{2}/.test(String(tgllahirUi))) {
                    var parts = String(tgllahirUi).substring(0, 10).split('-');
                    tgllahirUi = parts[2] + '/' + parts[1] + '/' + parts[0];
                }

                if (typeof selectedPatientFullData !== 'undefined') {
                    selectedPatientFullData = {
                        id_pasien: d.pasien.id_pasien,
                        nik_pasien: d.pasien.nik_pasien,
                        nama_pasien: d.pasien.nama_pasien,
                        gender_pasien: d.pasien.gender_pasien,
                        tgllahir_pasien: tgllahirUi,
                        tmpt_lahir: d.pasien.tmpt_lahir || '',
                        pekerjaan: d.pasien.pekerjaan || '',
                        phone_pasien: d.pasien.phone_pasien,
                        alamat_pasien: d.pasien.alamat_pasien,
                        alamat_lengkap: d.pasien.alamat_lengkap || d.pasien.alamat_pasien,
                        wilayah_id: d.pasien.wilayah_id || null,
                        no_rekammedis_pasien: d.pasien.no_rekammedis_pasien,
                        id_satu_sehat: d.pasien.id_satu_sehat,
                        id_pasien_satu_sehat: d.pasien.id_satu_sehat
                    };
                }
                if (typeof displayPatientDetail === 'function') {
                    displayPatientDetail(typeof buildPatientDetailViewData === 'function'
                        ? buildPatientDetailViewData(selectedPatientFullData)
                        : {
                            id_satu_sehat: d.pasien.id_satu_sehat,
                            nik_pasien: d.pasien.nik_pasien,
                            no_rekammedis_pasien: d.pasien.no_rekammedis_pasien,
                            nama_pasien: d.pasien.nama_pasien,
                            gender_pasien: d.pasien.gender_pasien,
                            tgllahir_pasien: tgllahirUi,
                            tmpt_lahir: d.pasien.tmpt_lahir || '-',
                            pekerjaan: d.pasien.pekerjaan || '-',
                            phone_pasien: d.pasien.phone_pasien,
                            alamat_pasien: d.pasien.alamat_lengkap || d.pasien.alamat_pasien
                        });
                }
                $('#seccond_pasien_permohonan_uji_klinik').val(d.pasien.id_pasien);
                $('#nopasien_permohonan_uji_klinik').val(d.pasien.nik_pasien || '');
                $('#tgllahir_pasien_permohonan_uji_klinik').val(tgllahirUi || '');
                if (tgllahirUi && typeof calculateAge === 'function') {
                    calculateAge(tgllahirUi);
                }
                $('#btn-next-step-2').prop('disabled', false);
                if (typeof currentSearchType !== 'undefined') {
                    currentSearchType = 'existing';
                }
            }

            // Step 3: informasi permohonan (nomor yang sudah tersimpan)
            if (d.nourut) {
                $('#nourut_permohonan_uji_klinik').val(d.nourut);
            }
            if (d.spesimen_urut) {
                $('#nomor_spesimen_auto').val(d.spesimen_urut);
                $('#nomor_spesimen_manual').val(d.spesimen_urut);
            } else if (d.nomor_spesimen_manual) {
                $('#nomor_spesimen_auto').val(d.nomor_spesimen_manual);
                $('#nomor_spesimen_manual').val(d.nomor_spesimen_manual);
            }
            if (d.lab_urut) {
                $('#nomor_lab_auto').val(d.lab_urut);
                $('#nomor_lab_manual').val(d.lab_urut);
            } else if (d.nomor_lab_manual) {
                $('#nomor_lab_auto').val(d.nomor_lab_manual);
                $('#nomor_lab_manual').val(d.nomor_lab_manual);
            }
            if (typeof updateKlinikNumberDisplays === 'function') {
                updateKlinikNumberDisplays();
            } else {
                if (d.nomor_spesimen_preview) {
                    $('#nomor_spesimen_display').val(d.nomor_spesimen_preview);
                }
                if (d.nomor_lab_preview) {
                    $('#nomor_lab_display').val(d.nomor_lab_preview);
                }
                if (d.noregister) {
                    $('#noregister_permohonan_uji_klinik').val(d.noregister);
                }
            }
            if (d.tglregister_display) {
                $('#tglregister_permohonan_uji_klinik_display').val(d.tglregister_display);
            }
            if (d.tglregister) {
                $('#tglregister_permohonan_uji_klinik').val(d.tglregister);
            }
            if (d.petugas_penerima) {
                $('#petugas_penerima').val(d.petugas_penerima);
            }
            if (d.petugas_pengambil_sampel) {
                if (typeof setPetugasPengambilValue === 'function') {
                    setPetugasPengambilValue(d.petugas_pengambil_sampel);
                } else {
                    $('#petugas_pengambil_sampel').val(d.petugas_pengambil_sampel);
                }
            }
            if (d.mode_pengambilan_sampel) {
                $('#mode_pengambilan_sampel').val(d.mode_pengambilan_sampel).trigger('change');
            }
            if (d.biaya_pengambilan_sampel) {
                $('#biaya_pengambilan_sampel').val(d.biaya_pengambilan_sampel);
            }
            if (d.umurtahun !== null && d.umurtahun !== undefined) {
                $('#umurtahun_pasien_permohonan_uji_klinik').val(d.umurtahun);
            }
            if (d.umurbulan !== null && d.umurbulan !== undefined) {
                $('#umurbulan_pasien_permohonan_uji_klinik').val(d.umurbulan);
            }
            if (d.umurhari !== null && d.umurhari !== undefined) {
                $('#umurhari_pasien_permohonan_uji_klinik').val(d.umurhari);
            }
            if (d.metode_pembayaran !== null && d.metode_pembayaran !== undefined) {
                $('select[name="metode_pembayaran"]').val(String(d.metode_pembayaran));
            }
            if (d.request_pasien) {
                $('#request_pasien_permohonan_uji_klinik').val(d.request_pasien);
                if (typeof tinymce !== 'undefined') {
                    var editor = tinymce.get('request_pasien_permohonan_uji_klinik');
                    if (editor) {
                        editor.setContent(d.request_pasien);
                    }
                }
            }
            if (d.kirim_hasil_whatsapp === '1') {
                $('#kirim_hasil_whatsapp_ya').prop('checked', true);
            } else {
                $('#kirim_hasil_whatsapp_tidak').prop('checked', true);
            }

            // Dokter rujukan
            if (d.doctor_type === 'rujukan') {
                $('#nama_dokter_pengirim_permohonan_uji_klinik').val(d.nama_dokter_pengirim || '');
                $('#hp_dokter_pengirim_permohonan_uji_klinik').val(d.hp_dokter_pengirim || '');
                $('#diagnosa_permohonan_uji_klinik').val(d.diagnosa || '');
                if (d.tipe_pemeriksaan_prolanis && $('#tipe_pemeriksaan_prolanis').length) {
                    $('#tipe_pemeriksaan_prolanis').val(d.tipe_pemeriksaan_prolanis);
                }
            }

            // Perwakilan dokter (dokter lab)
            if (d.is_perwakilan_dokter) {
                $('#flexSwitchCheckDefaultDokter').prop('checked', true);
                $('#form_perwakilan_dokter').show();
                $('#diagnosa_permohonan_uji_klinik_perwakilan').val(d.diagnosa || '');
            }

            // Wali
            if (d.is_perwakilan && d.perwakilan) {
                $('#flexSwitchCheckDefault').prop('checked', true);
                $('#form_perwakilan').show().find('input, textarea, select').prop('disabled', false);
                $('#nama_perwakian_permohonan_uji_klinik').val(d.perwakilan.nama || '');
                $('#gender_perwakilan_permohonan_uji_klinik').val(d.perwakilan.gender || 'L');
                if (d.perwakilan.tanggal_lahir) {
                    $('#basic2').val(d.perwakilan.tanggal_lahir);
                }
                $('#alamat_perwakilan').val(d.perwakilan.alamat || '');
                $('#status_hubungan_perwakilan_permohonan_uji_klinik').val(d.perwakilan.status_hubungan || '');
                if (d.perwakilan.status_hubungan === 'Lainnya') {
                    $('#status_hubungan_lainnya_group').show();
                    $('#status_hubungan_lainnya_permohonan_uji_klinik').val(d.perwakilan.status_hubungan_lainnya || '');
                }
            }

            // Edit: langsung ke informasi permohonan yang sudah terisi
            if (typeof goToStep === 'function') {
                goToStep(3);
            }
        }, 600);
    });
</script>
