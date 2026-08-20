@php
    $permohonanId = $permohonanId ?? '';
    $stepKey = $stepKey ?? '';
@endphp
    (function applyVerificationStepMetaFromLocalStorage() {
        var storageKey = 'verification_step_meta_{{ $permohonanId }}_{{ $stepKey }}';
        var raw;
        try {
            raw = localStorage.getItem(storageKey);
        } catch (e) {
            return;
        }
        if (!raw) return;

        var data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            return;
        }
        if (!data) return;

        var jam = (data.jam || '').trim();
        var petugas = (data.nama_petugas || '').trim();

        function extractTimeOnly(value) {
            value = (value || '').trim();
            if (/^\d{1,2}:\d{2}$/.test(value)) return value;
            var m = value.match(/(\d{1,2}:\d{2})$/);
            return m ? m[1] : value;
        }

        function toDmyHi(value) {
            value = (value || '').trim();
            if (/^\d{2}\/\d{2}\/\d{4}/.test(value)) return value;
            var m = value.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})/);
            if (m) {
                return m[3] + '/' + m[2] + '/' + m[1] + ' ' +
                    String(m[4]).padStart(2, '0') + ':' + m[5];
            }
            return value;
        }

        function setSelectValue($sel, value) {
            if (!$sel.length || !value) return;
            if ($sel.find('option').filter(function() {
                return String($(this).val()).trim() === value;
            }).length) {
                $sel.val(value);
            } else {
                $sel.append($('<option>', { value: value, text: value, selected: true }));
            }
        }

        @if($stepKey === 'penerima')
        if (jam && $('#jam_penerima').length) {
            $('#jam_penerima').val(extractTimeOnly(jam));
        }
        setSelectValue($('#nama_petugas_penerima'), petugas);
        @elseif($stepKey === 'pemeriksa')
        if (jam && $('#tglpengujian_permohonan_uji_klinik').length) {
            $('#tglpengujian_permohonan_uji_klinik').val(toDmyHi(jam));
        }
        setSelectValue($('#analis_permohonan_uji_klinik'), petugas);
        if (petugas && $('#analis_permohonan_uji_klinik').is('input[type=hidden]')) {
            $('#analis_permohonan_uji_klinik').val(petugas);
        }
        @elseif($stepKey === 'verifikasi')
        setSelectValue($('#verifikator_permohonan_uji_klinik'), petugas);
        if (petugas && $('#verifikator_permohonan_uji_klinik_hidden').length) {
            $('#verifikator_permohonan_uji_klinik_hidden').val(petugas);
        }
        @elseif($stepKey === 'validasi')
        if (jam && $('#jam_validasi').length) {
            $('#jam_validasi').val(extractTimeOnly(jam));
        }
        setSelectValue($('#nama_petugas_validasi'), petugas);
        @endif
    })();
