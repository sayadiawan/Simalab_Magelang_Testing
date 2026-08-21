@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script> --}}
    {{-- CKEditor dari CDN dipindah ke file lokal --}}
    {{-- <script src="//cdn.ckeditor.com/4.22.1/basic/ckeditor.js"></script> --}}
    <script src="{{ asset('assets/admin/cdn-local/js/ckeditor-4.22.1-basic.js') }}"></script>
    {{-- Number Format Helper --}}
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
    
    {{-- Flatpickr for Date Time Picker (Replacing Gijgo) - gunakan file lokal --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" onload="console.log('✅ Flatpickr loaded successfully');"></script> --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>

    <style>
        .urinalisa-dual-input {
            min-width: 220px;
        }

        .urinalisa-dual-input .urinalisa-dual-help {
            font-size: 11px;
            line-height: 1.4;
            margin-top: 6px;
            margin-bottom: 0;
            white-space: normal;
            word-break: break-word;
        }

        .urinalisa-dual-input .urinalisa-name-row + .urinalisa-name-row {
            margin-top: 6px;
        }

        .urinalisa-dual-input .urinalisa-grade-col {
            flex: 0 0 38%;
            max-width: 38%;
        }

        .urinalisa-dual-input .urinalisa-name-col {
            min-width: 0;
        }

        .urinalisa-dual-input .urinalisa-finding-row {
            width: 100%;
        }

        .urinalisa-dual-input .urinalisa-dual-actions {
            margin-top: 8px;
        }

        .urinalisa-dual-input .badge-buttons-row {
            margin-top: 0;
            align-items: flex-start;
        }

        .urinalisa-dual-input .badge,
        .result-display .badge {
            white-space: normal;
            text-align: left;
            display: inline-block;
            line-height: 1.35;
        }

        #table-parameter tbody td:has(.urinalisa-dual-input) {
            min-width: 240px;
            white-space: normal;
            vertical-align: top;
        }

        /* Flatpickr Custom Styles */
        .flatpickr-calendar {
            z-index: 9999 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
        }
        
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: #0b3a5c !important;
            border-color: #0b3a5c !important;
        }
        
        #tglpengujian_permohonan_uji_klinik {
            cursor: pointer;
            background-color: white !important;
        }
        
        #tglpengujian_permohonan_uji_klinik:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }
        
        .info-card {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(11, 58, 92, 0.3);
        }

        .info-card h4 {
            color: white;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h4 i {
            font-size: 24px;
        }

        .data-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        /* Sticky Data Pasien Section */
        .patient-data-sticky-wrapper {
            position: relative;
            z-index: 10;
            margin-bottom: 20px;
        }

        .patient-data-sticky-wrapper.sticky {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            width: 100%;
        }

        .patient-data-sticky-wrapper.sticky.compact {
            padding: 0;
        }

        .patient-data-compact {
            display: none;
            padding: 8px 15px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .patient-data-sticky-wrapper.sticky.compact .patient-data-compact {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .patient-data-sticky-wrapper.sticky.compact .patient-data-full {
            display: none;
        }

        .patient-data-compact-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            flex: 1;
        }

        .patient-data-compact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .patient-data-compact-item i {
            font-size: 14px;
            opacity: 0.9;
        }

        .patient-data-compact-item strong {
            font-weight: 600;
            margin-right: 5px;
        }

        .patient-data-compact-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patient-data-compact-actions .btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s;
        }

        .patient-data-compact-actions .btn i {
            color: white !important;
        }

        .patient-data-compact-actions .btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .patient-data-compact-actions .btn:hover i {
            color: white !important;
        }

        .patient-data-sticky-wrapper.sticky.expanded .patient-data-compact {
            display: none;
        }

        .patient-data-sticky-wrapper.sticky.expanded .patient-data-full {
            display: block;
            padding: 15px 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Spacer untuk mencegah content jump saat sticky */
        .patient-data-spacer {
            display: none;
            height: 0;
            transition: height 0.3s ease;
        }

        .patient-data-sticky-wrapper.sticky ~ .patient-data-spacer {
            display: block;
        }

        .patient-data-sticky-wrapper.sticky.compact ~ .patient-data-spacer {
            height: 50px;
        }

        .patient-data-sticky-wrapper.sticky.expanded ~ .patient-data-spacer {
            height: 400px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .patient-data-compact-content {
                gap: 10px;
                font-size: 12px;
            }

            .patient-data-compact-item {
                font-size: 11px;
            }

            .patient-data-compact-item strong {
                display: none;
            }

            .patient-data-compact-actions .btn {
                padding: 3px 8px;
                font-size: 11px;
            }
        }

        .data-card h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0b3a5c;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-card h5 i {
            color: #0b3a5c;
        }

        .info-table th {
            color: #6c757d;
            font-weight: 600;
            padding: 12px 15px;
            background: #f8f9fa;
            border: none;
            width: 200px;
        }

        .info-table td {
            padding: 12px 15px;
            border: none;
            color: #212529;
            font-weight: 500;
        }

        .info-table tr {
            border-bottom: 1px solid #e9ecef;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .form-section {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .form-section h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0b3a5c;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Style untuk tabel HTML di keterangan default dan current */
        .keterangan-default-display table,
        .keterangan-current-display table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .keterangan-default-display table td,
        .keterangan-current-display table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }


        .keterangan-default-display,
        .keterangan-current-display {
            max-width: 100%;
            word-wrap: break-word;
        }

        .form-section h5 i {
            color: #0b3a5c;
            font-size: 22px;
        }

        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }

        .btn-action {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary.btn-action {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        .btn-primary.btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 58, 92, 0.4);
        }

        .btn-light.btn-action {
            border: 1px solid #dee2e6;
        }

        .btn-light.btn-action:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        /* Responsive wrapper untuk table dengan sticky header */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            display: block;
            position: relative;
            max-height: calc(100vh - 180px);
        }

        /* Table parameter wrapper - area tbody lebih tinggi agar lebih banyak baris terlihat */
        #tableParameterResponsive {
            max-height: calc(100vh - 200px);
            min-height: 520px;
            overflow-x: auto;
            overflow-y: auto;
            order: 2;
            flex: 1 1 auto;
        }

        @media (min-height: 800px) {
            #tableParameterResponsive {
                max-height: calc(100vh - 150px);
                min-height: 560px;
            }
        }

        @media (min-height: 1000px) {
            #tableParameterResponsive {
                max-height: calc(100vh - 120px);
                min-height: 620px;
            }
        }

        /* Table wrapper dengan indikator scroll */
        .table-parameter-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Indikator scroll di bagian bawah — di luar area scroll agar tidak menutupi baris */
        .table-scroll-indicator {
            position: relative;
            bottom: auto;
            left: auto;
            right: auto;
            height: auto;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0;
            flex-shrink: 0;
            background: none;
            pointer-events: none;
            z-index: 1;
            order: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: max-height 0.25s ease, opacity 0.25s ease, padding 0.25s ease;
        }

        .table-parameter-wrapper.has-more-content .table-scroll-indicator {
            max-height: 44px;
            opacity: 1;
            padding: 6px 0 4px;
        }

        /* Indikator scroll di bagian atas — di luar area scroll */
        .table-scroll-indicator-top {
            position: relative;
            top: auto;
            left: auto;
            right: auto;
            height: auto;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0;
            flex-shrink: 0;
            background: none;
            pointer-events: none;
            z-index: 1;
            order: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: max-height 0.25s ease, opacity 0.25s ease, padding 0.25s ease;
        }

        .table-parameter-wrapper.has-content-above .table-scroll-indicator-top {
            max-height: 44px;
            opacity: 1;
            padding: 4px 0 6px;
        }

        .table-scroll-indicator-content {
            background: rgba(11, 58, 92, 0.9);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            animation: bounce-indicator 2s infinite;
        }

        .table-scroll-indicator-content i {
            font-size: 14px;
        }

        @keyframes bounce-indicator {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2px);
            }
        }

        #table-parameter tbody td .result-display,
        #table-parameter tbody td .hasil-input-container,
        #table-parameter tbody td .verifikasi-col,
        #table-parameter tbody td .catatan-verifikasi-col {
            white-space: normal;
            word-break: break-word;
        }

        /* Shadow gradient di bagian bawah untuk menunjukkan ada konten */
        #tableParameterResponsive::after {
            content: '';
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to top, rgba(11, 58, 92, 0.08) 0%, transparent 100%);
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .table-parameter-wrapper.has-more-content #tableParameterResponsive::after {
            opacity: 1;
        }

        /* Shadow gradient di bagian atas untuk menunjukkan ada konten */
        #tableParameterResponsive::before {
            content: '';
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to bottom, rgba(11, 58, 92, 0.08) 0%, transparent 100%);
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .table-parameter-wrapper.has-content-above #tableParameterResponsive::before {
            opacity: 1;
        }

        /* Counter badge untuk menunjukkan jumlah parameter */
        .parameter-counter-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            bottom: auto;
            background: rgba(11, 58, 92, 0.9);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            z-index: 15;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            display: none;
        }

        .table-parameter-wrapper.has-more-content .parameter-counter-badge {
            display: block;
        }

        #table-parameter {
            background: #fff;
            border-radius: 8px;
            overflow: visible;
            margin-bottom: 0;
        }

        #table-parameter thead {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #table-parameter thead th {
            padding: 15px;
            font-weight: 600;
            border: none;
            color: white;
            white-space: nowrap;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            position: sticky;
            top: 0;
            z-index: 101;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        #table-parameter thead th.sticky-below-patient {
            top: 50px;
        }

        #table-parameter thead th.sticky-below-patient-expanded {
            top: 400px;
        }

        #table-parameter tbody tr {
            border-bottom: 1px solid #e9ecef;
        }

        #table-parameter tbody tr:last-child {
            border-bottom: none;
        }

        #table-parameter tbody th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 15px;
            border: none;
        }

        #table-parameter tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        #table-parameter tbody td.nilai-normal-cell {
            font-size: 14px !important;
            line-height: 1.5 !important;
        }

        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-left,
        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-left * {
            text-align: left !important;
        }

        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-center,
        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-center * {
            text-align: center !important;
        }

        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-left table,
        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-left .bmu-nilai-table-wrap,
        #table-parameter tbody td.nilai-normal-cell.nilai-normal-align-left .nilai-normal-content {
            width: 100%;
            margin: 0;
        }

        /* Sembunyikan tabel verifikasi saat inisialisasi JS belum selesai */
        .verification-table-loading {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease-in-out;
        }

        .verification-table-loaded {
            opacity: 1;
            pointer-events: auto;
        }

        /* Badge kecil di pojok kanan atas tombol pengulangan/history */
        .btn-repeat-parameter {
            position: relative;
        }

        .btn-repeat-parameter .repeat-count-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 16px;
            height: 16px;
            padding: 0 3px;
            border-radius: 8px;
            background-color: #dc3545; /* merah kecil seperti badge error */
            color: #fff;
            font-size: 10px;
            line-height: 16px;
            text-align: center;
            font-weight: 600;
            pointer-events: none;
        }

        #table-parameter tbody td.metode-col {
            min-width: 120px;
            font-size: 12px;
        }

        #table-parameter tbody td.metode-col .metode-parameter-select {
            font-size: 12px;
            padding: 4px 8px;
        }

        .inline-metode-editor {
            width: 100%;
            min-height: 36px;
            padding: 6px 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 12px;
            background: white;
            cursor: text;
            transition: all 0.3s;
        }

        .inline-metode-editor:hover {
            border-color: #b8c1ec;
        }

        .inline-metode-editor:focus,
        .inline-metode-editor.mce-edit-focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }

        .inline-metode-editor sup {
            color: #0b3a5c;
            font-weight: 600;
        }

        .inline-metode-editor sub {
            color: #28a745;
            font-weight: 600;
        }

        #table-parameter tbody input[type="text"],
        #table-parameter tbody textarea,
        #table-parameter tbody select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 8px 12px;
            transition: all 0.3s;
        }

        #table-parameter tbody input[type="text"]:focus,
        #table-parameter tbody textarea:focus,
        #table-parameter tbody select:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }

        .badge-custom {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-block;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 12px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-danger.hasil-melewati-baku-mutu,
        .hasil-melewati-baku-mutu {
            font-weight: 700 !important;
        }

        .badge-danger .bm-kesimpulan-hasil,
        .hasil-melewati-baku-mutu .bm-kesimpulan-hasil {
            color: #fff;
            display: block;
            margin-top: 2px;
        }

        .badge-danger .bintang-baku-mutu,
        .hasil-melewati-baku-mutu .bintang-baku-mutu {
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge .hasil-multi-line,
        .result-display .badge .hasil-multi-line {
            display: inline-block;
            text-align: left;
            white-space: normal;
            line-height: 1.35;
        }

        .badge .hasil-multi-line br,
        .badge br,
        .hasil-melewati-baku-mutu br {
            display: block;
            content: '';
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .d-flex {
            display: flex;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-2 {
            gap: 8px;
        }

        /* Styling untuk data inputan analis */
        .input-analis {
            background-color: #e7f3ff;
            border-left: 3px solid #17a2b8;
            padding: 8px;
            border-radius: 4px;
        }

        /* Styling untuk status verifikasi */
        .status-verifikasi {
            font-weight: 600;
        }

        #table-parameter {
            min-width: 920px;
        }

        #table-parameter td.verifikasi-col {
            position: relative;
            overflow: visible;
            min-width: 150px;
            vertical-align: top;
            z-index: 1;
        }

        #table-parameter tbody tr:focus-within td.verifikasi-col {
            z-index: 20;
        }

        #table-parameter td.verifikasi-col select.status-verifikasi-inline {
            width: 100%;
            min-width: 120px;
            max-width: 100%;
            /* Beri ruang untuk panah dropdown & teks tidak terpotong */
            height: auto;
            min-height: 34px;
            line-height: 1.3;
            padding: 6px 26px 6px 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3E%3Cpath d='M4.5 6l3.5 4 3.5-4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px 12px;
        }

        /* Panah gelap saat status pending (background kuning + teks gelap) */
        #table-parameter td.verifikasi-col select.status-verifikasi-inline.status-pending {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3E%3Cpath d='M4.5 6l3.5 4 3.5-4z'/%3E%3C/svg%3E");
        }

        #table-parameter td.catatan-col {
            min-width: 150px;
            vertical-align: top;
        }

        #table-parameter td.catatan-col textarea {
            resize: vertical;
            min-height: 52px;
            width: 100%;
        }

        @media (max-width: 991.98px) {
            #table-parameter {
                min-width: 880px;
            }

            #table-parameter td.verifikasi-col {
                min-width: 135px;
            }
        }

        /* Tabel dari keterangan_dilaporan (bmu-nilai-table) */
        .bmu-nilai-table {
            border-collapse: collapse;
            width: 100%;
        }
        .bmu-nilai-table td,
        .bmu-nilai-table th {
            border: none !important;
            padding: 2px 6px;
            text-align: left !important;
        }
        .bmu-nilai-table tr {
            border: none !important;
        }

        /* Responsive: mobile & tablet */
        @media (max-width: 767.98px) {
            .data-card {
                padding: 15px;
                border-radius: 8px;
            }

            .form-section {
                padding: 15px;
                border-radius: 8px;
            }

            .info-table th {
                width: auto !important;
                min-width: 100px;
                font-size: 12px;
                padding: 8px 10px;
            }

            .info-table td {
                font-size: 12px;
                padding: 8px 10px;
            }

            .info-card {
                padding: 15px;
                border-radius: 10px;
            }

            .info-card h4 {
                font-size: 16px;
            }

            .patient-data-compact-content {
                flex-wrap: wrap;
                gap: 6px;
            }

            /* Tombol aksi di halaman verifikasi */
            .btn-verif-action {
                width: 100%;
                margin-bottom: 5px;
            }

            /* Stack dua kolom data pasien */
            .col-md-6 {
                margin-bottom: 15px;
            }

            /* Form verifikasi lebih kompak */
            #editModal .modal-body {
                padding: 15px;
            }

            #editModal .form-group-modal {
                margin-bottom: 15px;
                padding-bottom: 15px;
            }

            #editModal .offset-baku-mutu-group {
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (max-width: 575.98px) {
            .info-table {
                font-size: 11px;
            }

            .info-table th {
                min-width: 80px;
                padding: 6px 8px;
            }

            .info-table td {
                padding: 6px 8px;
            }

            .data-card h5 {
                font-size: 14px;
            }

            /* Table parameter pada layar sangat kecil */
            #table-parameter {
                min-width: 700px;
            }
        }

        /* Styling untuk modal edit lengkap */
        #editModal .modal-body {
            padding: 25px 30px;
        }

        #editModal .form-group-modal {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        #editModal .form-group-modal:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        #editModal .form-group-modal label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            display: block;
            font-size: 14px;
        }

        #editModal .form-group-modal label i {
            margin-right: 8px;
            color: #0b3a5c;
        }

        #editModal .form-control {
            margin-top: 8px;
        }

        #editModal #modal-hasil-editor-container,
        #editModal #modal-keterangan-editor-container {
            margin-top: 8px;
        }

        #editModal .simulasi-output-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 8px;
        }

        #editModal .simulasi-output-box .title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }

        #editModal .offset-baku-mutu-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        #editModal .offset-option {
            flex: 1;
            min-width: 150px;
        }

        #editModal .offset-option input[type="radio"] {
            margin-right: 8px;
        }

        #editModal .offset-option label {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            cursor: pointer;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            transition: all 0.3s;
        }

        #editModal .offset-option input[type="radio"]:checked + label {
            background-color: #e7f4f2;
            border-color: #0b3a5c;
        }

        .status-verifikasi option[value="pending"] {
            background-color: #ffc107;
            color: #212529;
        }

        .status-verifikasi option[value="approved"] {
            background-color: #28a745;
            color: white;
        }

        .status-verifikasi option[value="rejected"] {
            background-color: #dc3545;
            color: white;
        }

        .status-verifikasi option[value="corrected"] {
            background-color: #17a2b8;
            color: white;
        }

        /* Styling untuk dropdown status verifikasi di modal */
        #modal-status-verifikasi {
            font-weight: 600;
        }

        #modal-status-verifikasi option[value="pending"] {
            background-color: #ffc107;
            color: #212529;
        }

        #modal-status-verifikasi option[value="approved"] {
            background-color: #28a745;
            color: white;
        }

        #modal-status-verifikasi option[value="rejected"] {
            background-color: #dc3545;
            color: white;
        }

        #modal-status-verifikasi option[value="corrected"] {
            background-color: #17a2b8;
            color: white;
        }

        /* Styling untuk badge status verifikasi */
        .status-verifikasi-badge {
            margin-top: 8px;
        }

        .status-verifikasi-badge .badge {
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        /* Styling untuk komentar verifikasi */
        .komentar-verifikasi {
            font-size: 12px;
        }

        /* Badge komentar yang bisa diklik */
        .badge-comment-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }

        .badge-comment-clickable:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
        }

        .badge-comment-clickable:active {
            transform: scale(0.98);
        }

        /* Modal komentar */
        .comment-modal .modal-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .comment-modal .modal-body {
            padding: 25px;
            max-height: 500px;
            overflow-y: auto;
        }

        .comment-content {
            background: #f8f9fa;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.6;
            color: #495057;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .comment-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
        }

        .comment-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .comment-meta-item i {
            color: #2196F3;
        }

        /* Styling untuk baris yang perlu dikoreksi */
        tr.needs-correction {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }

        tr.needs-correction:hover {
            background-color: #ffeaa7 !important;
        }

        tr.needs-correction td {
            position: relative;
        }

        /* Highlight untuk data yang sudah dikoreksi */
        .data-dikoreksi {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }
        
        /* Inline editing styles */
        .inline-hasil-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .inline-hasil-input:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }
        
        .inline-hasil-editor {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: text;
            transition: all 0.3s;
        }
        
        .inline-hasil-editor:hover {
            border-color: #b8c1ec;
        }
        
        .inline-hasil-editor:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }
        
        .inline-hasil-editor[data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #999;
        }
        
        .inline-hasil-editor sup {
            color: #0b3a5c;
            font-weight: 600;
        }
        
        .inline-hasil-editor sub {
            color: #28a745;
            font-weight: 600;
        }
        
        .inline-keterangan-editor {
            min-height: 60px;
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            cursor: text;
            transition: all 0.3s;
        }
        
        .inline-keterangan-editor:hover {
            border-color: #b8c1ec;
        }
        
        .inline-keterangan-editor:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }
        
        .inline-keterangan-editor.empty {
            color: #999;
        }
        
        .inline-keterangan-editor.empty:before {
            content: 'Klik untuk mengisi keterangan...';
        }
        
        .result-badge-inline {
            margin-top: 8px;
            display: inline-block;
        }
        
        .result-badge-inline .badge {
            font-size: 13px;
            padding: 6px 12px;
        }
        
        /* Highlight row on focus */
        tr:has(.inline-hasil-input:focus), 
        tr:has(.inline-hasil-editor:focus),
        tr:has(.inline-keterangan-editor:focus),
        tr:has(.inline-metode-editor:focus),
        tr:has(.inline-metode-editor.mce-edit-focus) {
            background-color: #f8f9ff;
        }
        
        .parameter-cell {
            vertical-align: middle;
        }
        
        /* TinyMCE toolbar customization for inline */
        .tox.tox-tinymce-inline .tox-toolbar__primary {
            background: #0b3a5c !important;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .tox.tox-tinymce-inline .tox-tbtn {
            color: white !important;
        }
        
        .tox.tox-tinymce-inline .tox-tbtn:hover {
            background: rgba(255,255,255,0.2) !important;
        }
        
        .tox.tox-tinymce-inline .tox-tbtn svg {
            fill: white !important;
        }
        
        .hasil-input-container {
            position: relative;
        }
        
        /* Action buttons container */
        .action-buttons-container {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
        }
        
        .action-buttons-container .btn {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            white-space: nowrap;
        }
        
        /* Indicator untuk field hasil yang perlu diisi ulang setelah repeat */
        .inline-hasil-input.needs-refill,
        .inline-hasil-editor.needs-refill {
            border: 2px solid #ff6b6b !important;
            background-color: #fff5f5 !important;
            animation: pulse-border 2s infinite;
        }
        .inline-hasil-input.needs-refill::placeholder {
            color: #ff6b6b;
        }
        .inline-hasil-editor.needs-refill:empty:before {
            content: "⚠ Harap isi ulang hasil pemeriksaan";
            color: #ff6b6b;
            font-weight: 600;
        }
        .needs-refill-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 4px 8px;
            background-color: #ff6b6b;
            color: white;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-border {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.4); }
            50% { box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1); }
        }
        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Offset baku mutu group styles */
        .offset-baku-mutu-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .offset-option {
            flex: 1;
            min-width: 150px;
        }

        .offset-option input[type="radio"] {
            margin-right: 8px;
        }

        .offset-option label {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            cursor: pointer;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .offset-option input[type="radio"]:checked + label {
            background-color: #e7f4f2;
            border-color: #0b3a5c;
        }

        /* Modal — body scrollable saat konten tinggi */
        .modal-dialog.modal-body-scrollable {
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
        }

        .modal-dialog.modal-body-scrollable.modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 2rem);
        }

        .modal-dialog.modal-body-scrollable .modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            width: 100%;
        }

        .modal-dialog.modal-body-scrollable .modal-header,
        .modal-dialog.modal-body-scrollable .modal-footer {
            flex-shrink: 0;
        }

        .modal-dialog.modal-body-scrollable .modal-body {
            overflow-y: auto !important;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan
                                        Uji Klinik
                                        Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>verifikasi permohonan uji paket
                                        klinik</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form
        action="{{ route('elits-permohonan-uji-klinik-2.store-verification-permohonan-uji-paramater-klinik', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
        method="POST" enctype="multipart/form-data" id="form">
        @csrf
        @method('PUT')

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

        <!-- Header Card -->
        <div class="info-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <h4>
                <i class="fa fa-check-circle"></i>
                Verifikasi Permohonan Uji Klinik
            </h4>
            <p style="margin: 0; opacity: 0.9;">Formulir untuk verifikator mengkoreksi dan menilai inputan analis, serta
                memverifikasi apakah hasil pemeriksaan sudah sesuai</p>
        </div>

        <!-- Informasi Analis -->
        @if ($nama_analis)
            <div class="alert alert-info" style="border-left: 4px solid #17a2b8;">
                <div class="d-flex align-items-center">
                    <i class="fa fa-user-md mr-3" style="font-size: 24px;"></i>
                    <div>
                        <strong>Data Inputan Analis:</strong>
                        <span class="badge badge-primary ml-2">{{ $nama_analis }}</span>
                        @if ($data_verifikasi_analitik && $data_verifikasi_analitik->stop_date)
                            <span class="text-muted ml-2">
                                <i class="fa fa-clock mr-1"></i>
                                Tanggal Input:
                                {{ \Carbon\Carbon::parse($data_verifikasi_analitik->stop_date)->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif


        <!-- Form Tanggal Pengujian -->
        <div class="form-section" style="margin-top: 0;">
            <h5>
                <i class="fa fa-calendar-alt"></i>
                Tanggal Pengujian
            </h5>
            <div class="row">
                <div class="col-md-6">
                    @if ($data_verifikasi_analitik)
                        <div class="form-group">
                            <label for="tglpengujian_permohonan_uji_klinik">
                                <i class="fa fa-clock mr-2"></i>
                                Tanggal Pengujian
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="tglpengujian_permohonan_uji_klinik"
                                   id="tglpengujian_permohonan_uji_klinik" 
                                   placeholder="DD/MM/YYYY HH:mm"
                                value="{{ \Carbon\Carbon::parse($tgl_pengujian)->format('d/m/Y H:i') ?? old('tglpengujian_permohonan_uji_klinik') }}"
                                   autocomplete="off">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tanggal pengujian sudah diinput oleh Analis
                            </small>
                        </div>
                        <script>
                            // Flatpickr for verified data (editable)
                            (function() {
                                var maxRetries = 20;
                                var retryCount = 0;
                                var retryInterval = 100;
                                
                                function initFlatpickr() {
                                    console.log('🕐 [VERIFIED] Attempt #' + (retryCount + 1) + ': Checking Flatpickr availability');
                                    
                                    if (typeof flatpickr !== 'undefined') {
                                        var elem = document.getElementById('tglpengujian_permohonan_uji_klinik');
                                        
                                        if (elem) {
                                            console.log('✅ [VERIFIED] Both Flatpickr and element are ready!');
                                            try {
                                                var fp = flatpickr(elem, {
                                                    enableTime: true,
                                                    dateFormat: "d/m/Y H:i",
                                                    time_24hr: true,
                                                    allowInput: true,
                                                    locale: {
                                                        firstDayOfWeek: 1,
                                                        weekdays: {
                                                            shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                                                            longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                                                        },
                                                        months: {
                                                            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                                                            longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                                                        }
                                                    },
                                                    defaultDate: "{{ \Carbon\Carbon::parse($tgl_pengujian)->format('d/m/Y H:i') }}",
                                                    onReady: function() {
                                                        console.log('📅 [VERIFIED] Flatpickr calendar is ready!');
                                                    },
                                                    onOpen: function() {
                                                        console.log('📅 [VERIFIED] Flatpickr calendar opened');
                                                    }
                                                });
                                                console.log('✅ [VERIFIED] SUCCESS: Flatpickr initialized:', fp);
                                            } catch(e) {
                                                console.error('❌ [VERIFIED] ERROR initializing Flatpickr:', e);
                                            }
                                        } else {
                                            console.error('❌ [VERIFIED] Element not found');
                                        }
                                    } else {
                                        retryCount++;
                                        if (retryCount < maxRetries) {
                                            console.log('⏳ [VERIFIED] Flatpickr not loaded yet, retrying...');
                                            setTimeout(initFlatpickr, retryInterval);
                                        } else {
                                            console.error('❌ [VERIFIED] TIMEOUT: Flatpickr failed to load');
                                        }
                                    }
                                }
                                
                                initFlatpickr();
                            })();
                        </script>
                    @else

                        <div class="form-group">
                            <label for="tglpengujian_permohonan_uji_klinik">
                                <i class="fa fa-clock mr-2"></i>
                                Tanggal Pengujian <span style="color: red">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="tglpengujian_permohonan_uji_klinik"
                                   id="tglpengujian_permohonan_uji_klinik" 
                                   placeholder="DD/MM/YYYY HH:mm"
                                   value="{{ $tgl_pengujian ?? old('tglpengujian_permohonan_uji_klinik') }}" 
                                   autocomplete="off"
                                   required>
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Klik pada field untuk memilih tanggal dan waktu
                            </small>
                        </div>
                            <script>
                            // Inline Flatpickr initialization with retry mechanism
                            (function() {
                                var maxRetries = 20; // Max 20 attempts (2 seconds)
                                var retryCount = 0;
                                var retryInterval = 100; // Check every 100ms
                                
                                function initFlatpickr() {
                                    console.log('🕐 Attempt #' + (retryCount + 1) + ': Checking Flatpickr availability');
                                    
                                    if (typeof flatpickr !== 'undefined') {
                                        var elem = document.getElementById('tglpengujian_permohonan_uji_klinik');
                                        
                                        if (elem) {
                                            console.log('✅ Both Flatpickr and element are ready!');
                                            try {
                                                var fp = flatpickr(elem, {
                                                    enableTime: true,
                                                    dateFormat: "d/m/Y H:i",
                                                    time_24hr: true,
                                                    allowInput: true,
                                                    locale: {
                                                        firstDayOfWeek: 1,
                                                        weekdays: {
                                                            shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                                                            longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                                                        },
                                                        months: {
                                                            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                                                            longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                                                        }
                                                    },
                                                    @if(isset($tgl_pengujian) && $tgl_pengujian)
                                                    defaultDate: "{{ $tgl_pengujian }}",
                                                    @endif
                                                    onReady: function() {
                                                        console.log('📅 Flatpickr calendar is ready!');
                                                    },
                                                    onOpen: function() {
                                                        console.log('📅 Flatpickr calendar opened');
                                                    }
                                                });
                                                console.log('✅ SUCCESS: Flatpickr initialized:', fp);
                                            } catch(e) {
                                                console.error('❌ ERROR initializing Flatpickr:', e);
                                            }
                                        } else {
                                            console.error('❌ Element not found');
                                        }
                                    } else {
                                        retryCount++;
                                        if (retryCount < maxRetries) {
                                            console.log('⏳ Flatpickr not loaded yet, retrying...');
                                            setTimeout(initFlatpickr, retryInterval);
                                        } else {
                                            console.error('❌ TIMEOUT: Flatpickr failed to load after ' + maxRetries + ' attempts');
                                        }
                                    }
                                }
                                
                                // Start initialization
                                initFlatpickr();
                            })();
                            </script>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        @php
                            $userHasPetugas = !empty($user_petugas_nama ?? null);
                            $defaultVerifikator = old('verifikator_permohonan_uji_klinik',
                                $userHasPetugas ? ($user_petugas_nama) : ($nama_verifikator ?? ''));
                            $verifikatorLocked = isset($verifikator_locked) ? $verifikator_locked : false;
                            $verifikatorList = collect($petugas_analis);
                            $verifikatorInList = $verifikatorList->contains(function ($item) use ($defaultVerifikator) {
                                return isset($item['nama']) && trim($item['nama']) === trim($defaultVerifikator);
                            });
                        @endphp
                        <label for="verifikator_permohonan_uji_klinik">
                            <i class="fa fa-user-check mr-2"></i>
                            Pilih Verifikator <span style="color: red">*</span>
                        </label>
                        @if ($verifikatorLocked)
                            <div class="form-control bg-light text-muted" style="height: auto;">
                                {{ $defaultVerifikator ?: '-' }}
                            </div>
                            <small class="text-muted">
                                @if ($userHasPetugas)
                                    Verifikator ditetapkan sesuai akun yang digunakan.
                                @else
                                    Verifikator telah ditetapkan dan tidak dapat diubah.
                                @endif
                            </small>
                            <input type="hidden" name="verifikator_permohonan_uji_klinik"
                                id="verifikator_permohonan_uji_klinik_hidden" value="{{ $defaultVerifikator }}">
                        @else
                            <select class="form-control" name="verifikator_permohonan_uji_klinik"
                                id="verifikator_permohonan_uji_klinik" required>
                                <option value="">-- Pilih Verifikator --</option>
                                @foreach ($petugas_analis as $petugas)
                                    <option value="{{ $petugas['nama'] }}" data-nip="{{ $petugas['nip'] }}"
                                        data-id="{{ $petugas['id_petugas'] }}"
                                        {{ $defaultVerifikator == $petugas['nama'] ? 'selected' : '' }}>
                                        {{ $petugas['nama'] }}{{ !empty($petugas['nip']) ? ' - ' . $petugas['nip'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih petugas yang akan melakukan verifikasi</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="patient-data-sticky-wrapper" id="patientDataStickyWrapper">
            <!-- Compact View (akan muncul saat sticky) -->
            <div class="patient-data-compact">
                <div class="patient-data-compact-content">
                    <div class="patient-data-compact-item">
                        <i class="fa fa-user"></i>
                        <strong>Nama:</strong>
                        <span>{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}</span>
                    </div>
                    <div class="patient-data-compact-item">
                        <i class="fa fa-id-card"></i>
                        <strong>No. Sampel:</strong>
                        <span>{{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</span>
                    </div>
                    <div class="patient-data-compact-item">
                        <i class="fa fa-birthday-cake"></i>
                        <strong>Usia:</strong>
                        <span>{{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik }} tahun</span>
                    </div>
                    <div class="patient-data-compact-item">
                        <i class="fa fa-{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'mars' : 'venus' }}"></i>
                        <strong>JK:</strong>
                        <span>{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                        'info_haji' => $info_haji ?? null,
                        'mode' => 'compact',
                    ])
                </div>
                <div class="patient-data-compact-actions">
                    <button type="button" class="btn btn-sm" id="expandPatientData" title="Perlebar">
                        <i class="fa fa-expand"></i>
                    </button>
                    <button type="button" class="btn btn-sm" id="minimizePatientData" title="Minimize" style="display: none;">
                        <i class="fa fa-compress"></i>
                    </button>
                </div>
            </div>

            <!-- Full View -->
            <div class="patient-data-full">
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
            'info_haji' => $info_haji ?? null,
            'mode' => 'alert',
        ])
        <div class="row">
            <!-- Data Pasien - Kiri -->
            <div class="col-md-6">
                <div class="data-card">
                    <h5>
                        <i class="fa fa-user"></i>
                        Data Pasien
                    </h5>
                    <div class="table-responsive">
                        <table class="table info-table">
                            <tr>
                                <th width="250px">No. Sampel</th>
                                <td>{{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</td>
                            </tr>
                            <tr>
                                <th width="250px">No. Lab</th>
                                <td>{{ $item_permohonan_uji_klinik->getLabNumber() }}</td>
                            </tr>

                            <tr>
                                <th width="250px">No. Rekam Medis</th>
                                <td>
                                    {{ $item_permohonan_uji_klinik->getNoRekamMedis() }}
                                </td>
                            </tr>

                            <tr>
                                <th width="250px">Tgl. Register</th>
                                <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Nama Pasien</th>
                                        <td style="text-transform: uppercase;">{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}</td>
                            </tr>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                'info_haji' => $info_haji ?? null,
                                'mode' => 'table-rows',
                            ])

                            <tr>
                                <th width="250px">Usia</th>
                                <td>
                                    {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik .
                                        ' tahun ' .
                                        $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik .
                                        ' bulan ' .
                                        $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik .
                                        ' hari' }}
                                </td>
                            </tr>

                            <tr>
                                <th width="250px">Jenis Kelamin</th>
                                <td>
                                    {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                            </tr>

                            <tr>
                                <th width="250px">Alamat Pasien</th>
                                <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item_permohonan_uji_klinik->pasien) }}</td>
                            </tr>

                            <tr>
                                <th width="250px">No. Telepon</th>
                                <td>{{ $item_permohonan_uji_klinik->pasien->phone_pasien }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Data Pasien - Kanan -->
            <div class="col-md-6">
                <div class="data-card">
                    <h5>
                        <i class="fa fa-info-circle"></i>
                        Informasi Tambahan
                    </h5>
                    <div class="table-responsive">
                        <table class="table info-table">
                            <tr>
                                <th>No. Pasien</th>
                                <td>{{ $item_permohonan_uji_klinik->pasien->nourut_pasien }}</td>
                            </tr>
                            <tr>
                                <th>No. KTP</th>
                                <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td>
                                    {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                                        ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                                            'D MMMM Y',
                                        )
                                        : '' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Pengirim</th>
                                <td>{{ $item_permohonan_uji_klinik->getNamaPengirim() }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Request Pasien / Keluhan</th>
                                <td>{!! $item_permohonan_uji_klinik->request_pasien_permohonan_uji_klinik ?? '-' !!}</td>
                            </tr>

                            <tr>
                                <th width="250px">Diagnosis Dokter</th>
                                <td>{{ $item_permohonan_uji_klinik->diagnosa_permohonan_uji_klinik ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Kondisi Pasien</th>
                                <td>{{ $kondisi_pasien ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
        <div class="patient-data-spacer"></div>

        <!-- Informasi Pengambilan Sample -->
        @if ($latest_sampling)
            <div class="row">
                <div class="col-md-12">
                    <div class="data-card">
                        <h5>
                            <i class="fa fa-vial"></i>
                            Informasi Pengambilan Sample
                        </h5>
                        <div class="table-responsive">
                            <table class="table info-table">
                                <tr>
                                    <th width="200px">Tanggal & Waktu Sampling</th>
                                    <td>
                                        {{ $tgl_waktu_sampling ?? \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::formatTanggalWaktuSamplingKlinikDisplay($item_permohonan_uji_klinik->id_permohonan_uji_klinik, $latest_sampling, $item_permohonan_uji_klinik) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Sampling</th>
                                    <td>
                                        @if ($latest_sampling->status_sampling == 'Berhasil')
                                            <span
                                                class="badge badge-success">{{ $latest_sampling->status_sampling }}</span>
                                        @else
                                            <span
                                                class="badge badge-danger">{{ $latest_sampling->status_sampling }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jenis Sample</th>
                                    <td>
                                        @php
                                            $jenis_sample_display = '';
                                            if (!empty($latest_sampling->jenis_sample)) {
                                                if (is_string($latest_sampling->jenis_sample)) {
                                                    $decoded = json_decode($latest_sampling->jenis_sample, true);
                                                    $jenis_sample_display = is_array($decoded)
                                                        ? implode(', ', $decoded)
                                                        : $latest_sampling->jenis_sample;
                                                } elseif (is_array($latest_sampling->jenis_sample)) {
                                                    $jenis_sample_display = implode(
                                                        ', ',
                                                        $latest_sampling->jenis_sample,
                                                    );
                                                } else {
                                                    $jenis_sample_display = $latest_sampling->jenis_sample;
                                                }
                                            }
                                        @endphp
                                        <span class="badge-custom">{{ $jenis_sample_display ?: '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tindakan Medis Khusus</th>
                                    <td>{{ \Smt\Masterweb\Helpers\Smt::formatTindakanMedisKhususDisplay($latest_sampling->tindakan_medis_khusus ?? null) }}</td>
                                </tr>
                                <tr>
                                    <th>Kondisi Pasien</th>
                                    <td>{{ $latest_sampling->kondisi_pasien ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Petugas Pengambil</th>
                                    <td>{{ $latest_sampling->petugas_name ?? '-' }}</td>
                                </tr>
                                @if ($latest_sampling->resampling > 0)
                                    <tr>
                                        <th>Resampling</th>
                                        <td>
                                            <span class="badge badge-warning">Resampling
                                                ke-{{ $latest_sampling->resampling + 1 }}</span>
                                            @if ($latest_sampling->resample_reason)
                                                <br><small class="text-muted">Alasan:
                                                    {{ $latest_sampling->resample_reason }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Informasi Penerimaan Sampel -->
        @if (
            $penerimaan_sampel ||
                $volume_sampel ||
                $kualitas_lisis ||
                $kualitas_ikterik ||
                $kualitas_lipemik ||
                $kualitas_cukup ||
                $kualitas_beku)
            <div class="row">
                <div class="col-md-12">
                    <div class="data-card">
                        <h5>
                            <i class="fa fa-clipboard-check"></i>
                            Informasi Penerimaan Sampel
                        </h5>
                        <div class="table-responsive">
                            <table class="table info-table">
                                @if ($penerimaan_sampel)
                                    <tr>
                                        <th width="200px">Penerimaan Sampel</th>
                                        <td>
                                            @foreach (json_decode($penerimaan_sampel, true) as $jenis => $nilai)
                                                <div><strong>{{ $jenis }}</strong>: {{ $nilai }}</div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                                @if ($volume_sampel)
                                    <tr>
                                        <th>Volume Sampel</th>
                                        <td>
                                            @php
                                                $volume_data = \Smt\Masterweb\Helpers\Smt::decodeJsonMap($volume_sampel);
                                            @endphp
                                            @if (count($volume_data) > 0)
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($volume_data as $jenis => $nilai)
                                                        <span
                                                            style="display: inline-block; background: #e7f4f2; border: 1px solid #0b3a5c; color: #0b3a5c; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-right: 8px; margin-bottom: 5px;">
                                                            <i class="fa fa-vial" style="margin-right: 5px;"></i>{{ $jenis }}:
                                                            <strong>{{ is_array($nilai) ? implode(', ', $nilai) : $nilai }}</strong>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ is_string($volume_sampel) && substr(ltrim($volume_sampel), 0, 1) !== '{' ? $volume_sampel : '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Kualitas Sampel</th>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @if ($kualitas_lisis)
                                                <span class="badge badge-danger">Lisis</span>
                                            @endif
                                            @if ($kualitas_ikterik)
                                                <span class="badge badge-warning">Ikterik</span>
                                            @endif
                                            @if ($kualitas_lipemik)
                                                <span class="badge badge-info">Lipemik</span>
                                            @endif
                                            @if ($kualitas_cukup)
                                                <span class="badge badge-success">Cukup</span>
                                            @endif
                                            @if ($kualitas_beku)
                                                <span class="badge badge-secondary">Beku</span>
                                            @endif
                                            @if (!$kualitas_lisis && !$kualitas_ikterik && !$kualitas_lipemik && !$kualitas_cukup && !$kualitas_beku)
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Hasil Pemeriksaan -->
        <div class="form-section">
            <h5>
                <i class="fa fa-clipboard-check"></i>
                Verifikasi Hasil Pemeriksaan
            </h5>
            <div class="alert alert-warning">
                <i class="fa fa-info-circle mr-2"></i>
                <strong>Catatan:</strong> Data yang ditampilkan adalah inputan dari analis. Verifikator dapat mengkoreksi,
                memperbaiki, atau menyetujui hasil tersebut.
            </div>
            <!-- Alert untuk scroll horizontal di layar kecil -->
            <div class="alert alert-info d-md-none mb-3" role="alert" style="display: flex; align-items: center; padding: 10px 15px; border-radius: 6px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <i class="fa fa-arrows-alt-h mr-2" style="font-size: 16px; color: #2196F3;"></i>
                <span style="font-size: 13px; color: #1976D2;">
                    <strong>Tips:</strong> Geser tabel ke kanan dan kiri untuk melihat semua kolom termasuk tombol "Edit" di layar kecil.
                </span>
            </div>
            <div class="table-parameter-wrapper verification-table-loading" id="tableParameterWrapper">
                <!-- Indikator scroll di bagian atas -->
                <div class="table-scroll-indicator-top">
                    <div class="table-scroll-indicator-content">
                        <i class="fa fa-chevron-up"></i>
                        <span>Masih ada parameter di atas</span>
                    </div>
                </div>
                <div class="table-responsive" id="tableParameterResponsive">
                <table id="table-parameter" class="table">
                    <thead>
                        <tr>
                            <th style="width: 20%">Nama Test</th>
                            <th style="width: 14%">Hasil</th>
                            <th style="width: 12%" class="text-center">Verifikasi</th>
                            <th style="width: 14%" class="text-center">Catatan</th>
                            <th style="width: 7%" class="text-center">Satuan</th>
                            <th style="width: 12%" class="text-center">Metode</th>
                            <th style="width: 15%; display: none;">Keterangan</th>
                            <th style="width: 16%" class="text-center">Nilai Normal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 0;
                        @endphp

                        @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                            <tr>
                                <th colspan="8">
                                    <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                                </th>
                            </tr>
                            @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                                @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] ?? []) > 0)
                                    <tr>
                                        <td>
                                            -{{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                            <input type="hidden"
                                                name="permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                id="permohonan_uji_parameter_klinik_{{ $no }}"
                                                value="{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}"
                                                readonly>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._metode-parameter-column', [
                                            'item_satuan_klinik' => $item_satuan_klinik,
                                            'method_input_id' => 'method_permohonan_uji_parameter_klinik_' . $no,
                                            'method_index' => $no,
                                        ])
                                        <td style="display: none;"></td>
                                        <td></td>
                                    </tr>

                                    @php
                                        $no_sub = 0;
                                    @endphp

                                    {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parametersubsatuan --}}
                                    @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                        @php
                                            $status_verifikasi_sub = $item_subsatuan_klinik['status_verifikasi'] ?? '';
                                            // Badge muncul hanya jika statusnya eksplisit rejected atau need_correction
                                            $needs_correction = ($status_verifikasi_sub == 'rejected' || $status_verifikasi_sub == 'need_correction');
                                            $has_comment = !empty($item_subsatuan_klinik['komentar_verifikasi'] ?? '');
                                        @endphp
                                        <tr class="{{ $needs_correction ? 'needs-correction' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <p style="padding-left: 30px; margin: 0; flex: 1;">
                                                        {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                                        ~
                                                    </p>
                                                    @if ($needs_correction)
                                                        <span class="badge badge-warning ml-2" title="Perlu dikoreksi">
                                                            <i class="fa fa-exclamation-triangle"></i> Perlu Koreksi
                                                        </span>
                                                    @endif
                                                    @if ($has_comment)
                                                        <span class="badge badge-info ml-2 badge-comment-clickable" 
                                                            title="Klik untuk melihat komentar"
                                                            data-comment="{{ htmlspecialchars($item_subsatuan_klinik['komentar_verifikasi'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                            data-parameter-name="{{ htmlspecialchars($item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                            onclick="showCommentModal(this)">
                                                            <i class="fa fa-comment"></i> Ada Komentar
                                                        </span>
                                                    @endif
                                                </div>
                                                <input type="hidden"
                                                    name="parameter_sub_satuan_klinik_id[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    id="parameter_sub_satuan_klinik_id_{{ $no_sub }}"
                                                    value="{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}"
                                                    readonly>
                                            </td>
                                            <td>
                                                    <!-- Hidden textarea for form submission -->
                                                    <textarea class="form-control result_method_klinik"
                                                        id="hasil_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}"
                                                        name="hasil_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        data-min="{{ $item_subsatuan_klinik['min_baku_mutu_detail_parameter_klinik'] ?? '' }}"
                                                        data-max="{{ $item_subsatuan_klinik['max_baku_mutu_detail_parameter_klinik'] ?? '' }}"
                                                        data-equal="{{ $item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik'] ?? '' }}"
                                                        data-is-option="0"
                                                        data-option-values="[]"
                                                        data-number-format="{{ $item_satuan_klinik['number_format'] ?? 'en' }}"
                                                        style="display: none;">{{ isset($item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik']) ? rubahNilaikeForm($item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik']) : (isset($item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik']) ? rubahNilaikeForm($item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik']) : '') }}</textarea>

                                                {{-- Hidden input untuk kesimpulan baku mutu sub parameter --}}
                                                    <input type="hidden"
                                                        value="{{ $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '' }}"
                                                        id="kesimpulan_baku_mutu_sub_{{ $no_sub }}">


                                                {{-- Hidden input untuk offset baku mutu --}}
                                                <input type="hidden"
                                                    name="offset_baku_mutu_sub[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    id="offset_baku_mutu_sub_{{ $no_sub }}"
                                                    value="{{ isset($item_subsatuan_klinik['offset_baku_mutu']) ? $item_subsatuan_klinik['offset_baku_mutu'] : 'default' }}">

                                                {{-- Hidden input untuk status verifikasi --}}
                                                <input type="hidden"
                                                    name="status_verifikasi_sub[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    id="status_verifikasi_sub_{{ $no_sub }}"
                                                    value="{{ $item_subsatuan_klinik['status_verifikasi'] ?? 'approved' }}">

                                                {{-- Hidden input untuk komentar verifikasi --}}
                                                <input type="hidden"
                                                    name="komentar_verifikasi_sub[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    id="komentar_verifikasi_sub_{{ $no_sub }}"
                                                    value="{{ $item_subsatuan_klinik['komentar_verifikasi'] ?? '' }}">

                                                {{-- Display hasil --}}
                                                <div class="result-display {{ empty($item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik']) ? 'empty' : '' }}"
                                                    id="result_display_sub_{{ $no_sub }}">
                                                    @if (!empty($item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik']))
                                                        @php
                                                            $hasil_value = $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'];
                                                            $min = $item_subsatuan_klinik['min_baku_mutu_detail_parameter_klinik'] ?? null;
                                                            $max = $item_subsatuan_klinik['max_baku_mutu_detail_parameter_klinik'] ?? null;
                                                            $equal = $item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik'] ?? null;
                                                            $offset = isset($item_subsatuan_klinik['offset_baku_mutu']) ? $item_subsatuan_klinik['offset_baku_mutu'] : 'default';
                                                            $multipleBakuMutu = $item_subsatuan_klinik['multiple_normal_baku_mutu'] ?? null;
                                                            $kesimpulan = $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '';
                                                            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                                                            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien ?? null;
                                                            $result_badge = \Smt\Masterweb\Helpers\Smt::checkBakuMutu($hasil_value, $min, $max, $equal, $offset, $multipleBakuMutu, $kesimpulan, $pasien_umur, $pasien_gender, $item_subsatuan_klinik['nama_sub_parameter_satuan_klinik'] ?? null);
                                                        @endphp
                                                        {!! $result_badge ?: rubahNilaikeForm($hasil_value) !!}
                                                        @if (isset($item_subsatuan_klinik['has_selected_history']) && $item_subsatuan_klinik['has_selected_history'])
                                                            <span class="badge badge-info badge-sm ml-1" title="Hasil ini dipilih dari history">
                                                                <i class="fa fa-history"></i> Dari History
                                                            </span>
                                                        @endif
                                                            @else
                                                        <span class="text-muted">-</span>
                                                            @endif
                                                        </div>
                                                {{-- Hidden div untuk simulasi output --}}
                                                <div id="result_output_sub_{{ $no_sub }}" style="display: none;"
                                                    data-multiple-baku-mutu="{{ json_encode($item_subsatuan_klinik['multiple_normal_baku_mutu'] ?? []) }}"
                                                    data-history-count="{{ $item_subsatuan_klinik['history_count'] ?? 0 }}"></div>

                                                {{-- Display Status Verifikasi - akan dipindahkan ke samping tombol Baku Mutu oleh JavaScript --}}
                                                @php
                                                    $status_verifikasi_display = $item_subsatuan_klinik['status_verifikasi'] ?? 'approved';
                                                    $status_labels = [
                                                        'pending' => ['label' => 'Belum Diverifikasi', 'class' => 'warning', 'icon' => 'clock'],
                                                        'approved' => ['label' => 'Diterima', 'class' => 'success', 'icon' => 'check-circle'],
                                                        'rejected' => ['label' => 'Ditolak', 'class' => 'danger', 'icon' => 'times-circle'],
                                                        'corrected' => ['label' => 'Diperbaiki', 'class' => 'info', 'icon' => 'edit']
                                                    ];
                                                    $status_info = $status_labels[$status_verifikasi_display] ?? $status_labels['approved'];
                                                @endphp
                                                <div class="status-verifikasi-badge" id="status_verifikasi_badge_sub_{{ $no_sub }}" style="display: none;">
                                                    <span class="badge badge-{{ $status_info['class'] }}" style="font-size: 12px; padding: 6px 10px;">
                                                        <i class="fa fa-{{ $status_info['icon'] }} mr-1"></i>
                                                        {{ $status_info['label'] }}
                                                    </span>
                                                </div>
                                            </td>
                                            {{-- Kolom Verifikasi (dropdown status) --}}
                                            <td class="align-middle verifikasi-col" style="width: 14%;">
                                                {{-- Simpan keterangan sebagai hidden --}}
                                                <input type="hidden"
                                                    id="keterangan_sub_{{ $no_sub }}"
                                                    name="keterangan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    value="{{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' }}">

                                                <select class="form-control form-control-sm status-verifikasi status-verifikasi-inline"
                                                    data-type="sub"
                                                    data-index="{{ $no_sub }}"
                                                    data-hidden-id="status_verifikasi_sub_{{ $no_sub }}">
                                                    <option value="pending" {{ $status_verifikasi_display == 'pending' ? 'selected' : '' }}>Belum Diverifikasi</option>
                                                    <option value="approved" {{ $status_verifikasi_display == 'approved' ? 'selected' : '' }}>Diterima</option>
                                                    <option value="rejected" {{ $status_verifikasi_display == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                    <option value="corrected" {{ $status_verifikasi_display == 'corrected' ? 'selected' : '' }}>Diperbaiki</option>
                                                </select>
                                            </td>
                                            {{-- Kolom Catatan: selalu bisa diisi, termasuk saat Diterima --}}
                                            <td class="align-middle catatan-col" style="width: 16%;">
                                                <textarea class="form-control form-control-sm komentar-verifikasi-inline"
                                                    rows="2"
                                                    placeholder="Catatan verifikasi (opsional)"
                                                    data-type="sub"
                                                    data-index="{{ $no_sub }}"
                                                    data-hidden-id="komentar_verifikasi_sub_{{ $no_sub }}">{{ $item_subsatuan_klinik['komentar_verifikasi'] ?? '' }}</textarea>
                                            </td>
                                            <td class="text-center align-middle">
                                                {!! isset($item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'])
                                                    ? $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik']
                                                    : '-' !!}
                                                <input type="hidden"
                                                    name="satuan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                    value="{{ $item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] ?? '' }}">
                                            </td>
                                            <td class="text-center align-middle metode-col"></td>
                                            <td style="display: none;"></td>
                                            <td class="align-middle nilai-normal-cell {{ nilaiNormalAlignClassFromHtml(rubahNilaikeForm($item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] ?? '-')) }}">
                                                {!! rubahNilaikeForm($item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] ?? '-') !!}
                                            </td>
                                        </tr>

                                        @php
                                            $no_sub++;
                                        @endphp
                                    @endforeach
                                @else
                                    @php
                                        $status_verifikasi_param = $item_satuan_klinik['status_verifikasi'] ?? '';
                                        // Badge muncul hanya jika statusnya eksplisit rejected atau need_correction
                                        $needs_correction = ($status_verifikasi_param == 'rejected' || $status_verifikasi_param == 'need_correction');
                                        $has_comment = !empty($item_satuan_klinik['komentar_verifikasi'] ?? '');
                                    @endphp
                                    <tr class="{{ $needs_correction ? 'needs-correction' : '' }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span style="flex: 1;">-
                                                    {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}</span>
                                                @if ($needs_correction)
                                                    <span class="badge badge-warning ml-2" title="Perlu dikoreksi">
                                                        <i class="fa fa-exclamation-triangle"></i> Perlu Koreksi
                                                    </span>
                                                @endif
                                                @if ($has_comment)
                                                    <span class="badge badge-info ml-2 badge-comment-clickable" 
                                                        title="Klik untuk melihat komentar"
                                                        data-comment="{{ htmlspecialchars($item_satuan_klinik['komentar_verifikasi'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                        data-parameter-name="{{ htmlspecialchars($item_satuan_klinik['nama_parameter_satuan_klinik'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                        onclick="showCommentModal(this)">
                                                        <i class="fa fa-comment"></i> Ada Komentar
                                                    </span>
                                                @endif
                                            </div>
                                            <input type="hidden"
                                                name="permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                id="permohonan_uji_parameter_klinik_{{ $no }}"
                                                value="{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}"
                                                readonly>
                                        </td>
                                        <td>
                                                @php
                                                    // Cek apakah parameter memiliki is_option = 1 untuk kolom Hasil
                                                    $is_option_hasil =
                                                        isset(
                                                            $item_satuan_klinik['parameter_satuan_klinik_is_option'],
                                                        ) &&
                                                        $item_satuan_klinik['parameter_satuan_klinik_is_option'] == 1;
                                                    $option_values_hasil = [];
                                                    if (
                                                        $is_option_hasil &&
                                                        !empty($item_satuan_klinik['parameter_satuan_klinik_option'])
                                                    ) {
                                                        $option_values_hasil = array_map(function ($opt) {
                                                            return rubahNilaikeForm(trim($opt));
                                                        }, explode(
                                                            ',',
                                                            $item_satuan_klinik['parameter_satuan_klinik_option'],
                                                        ));
                                                    }

                                                    $urinalisaRequiresNamaJenis = (int) (
                                                        ($item_satuan_klinik['requires_nama_jenis'] ?? 0)
                                                        || ($item_satuan_klinik['parameter_satuan_klinik_requires_nama_jenis'] ?? 0)
                                                    );
                                                    $urinalisaDualType = \Smt\Masterweb\Helpers\Smt::urinalisaDualColumnType(
                                                        $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? '',
                                                        $item_satuan_klinik['parameter_satuan_klinik_option']
                                                            ?? $item_satuan_klinik['option']
                                                            ?? null,
                                                        $urinalisaRequiresNamaJenis
                                                    );
                                                    if ($urinalisaDualType) {
                                                        $is_option_hasil = false;
                                                        $option_values_hasil = [];
                                                    }
                                                @endphp

                                                <!-- Hidden textarea for form submission -->
                                                <textarea class="form-control result_method_klinik" id="hasil_permohonan_uji_parameter_klinik_{{ $no }}"
                                                    name="hasil_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    data-name="{{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}"
                                                    data-min="{{ $item_satuan_klinik['min'] ?? '' }}" 
                                                    data-max="{{ $item_satuan_klinik['max'] ?? '' }}"
                                                    data-equal="{{ $item_satuan_klinik['equal'] ?? '' }}"
                                                    data-is-option="{{ $is_option_hasil ? '1' : '0' }}"
                                                    data-option-values="{{ $is_option_hasil ? json_encode($option_values_hasil) : '[]' }}"
                                                    data-number-format="{{ $item_satuan_klinik['number_format'] ?? 'en' }}"
                                                    data-urinalisa-dual="{{ $urinalisaDualType ? '1' : '0' }}"
                                                    style="display: none;">{{ isset($item_satuan_klinik['hasil_permohonan_uji_parameter_klinik']) ? rubahNilaikeForm($item_satuan_klinik['hasil_permohonan_uji_parameter_klinik']) : (isset($item_satuan_klinik['equal']) ? rubahNilaikeForm($item_satuan_klinik['equal']) : '') }}</textarea>

                                                {{-- Hidden input untuk kesimpulan baku mutu --}}
                                                <input type="hidden"
                                                    value="{{ $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '' }}"
                                                    id="kesimpulan_baku_mutu_param_{{ $no }}">


                                            {{-- Hidden input untuk offset baku mutu --}}
                                                <input type="hidden"
                                                    name="offset_baku_mutu_param[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="offset_baku_mutu_param_{{ $no }}"
                                                    value="{{ isset($item_satuan_klinik['offset_baku_mutu']) ? $item_satuan_klinik['offset_baku_mutu'] : 'default' }}">

                                                {{-- Hidden input untuk status verifikasi --}}
                                                <input type="hidden"
                                                    name="status_verifikasi_param[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="status_verifikasi_param_{{ $no }}"
                                                value="{{ $item_satuan_klinik['status_verifikasi'] ?? 'pending' }}">

                                                {{-- Hidden input untuk komentar verifikasi --}}
                                                <input type="hidden"
                                                    name="komentar_verifikasi_param[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="komentar_verifikasi_param_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['komentar_verifikasi'] ?? '' }}">

                                                @if ($urinalisaDualType)
                                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.urinalisa-dual-result-input', [
                                                        'no' => $no,
                                                        'item_satuan_klinik' => $item_satuan_klinik,
                                                    ])
                                                @endif

                                                {{-- Display hasil --}}
                                                <div class="result-display {{ empty($item_satuan_klinik['hasil_permohonan_uji_parameter_klinik']) ? 'empty' : '' }}"
                                                    id="result_display_param_{{ $no }}">
                                                    @if (!empty($item_satuan_klinik['hasil_permohonan_uji_parameter_klinik']))
                                                        @php
                                                            $hasil_value = $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'];
                                                            $min = $item_satuan_klinik['min'] ?? null;
                                                            $max = $item_satuan_klinik['max'] ?? null;
                                                            $equal = $item_satuan_klinik['equal'] ?? null;
                                                            $offset = isset($item_satuan_klinik['offset_baku_mutu']) ? $item_satuan_klinik['offset_baku_mutu'] : 'default';
                                                            $multipleBakuMutu = isset($item_satuan_klinik['multiple_baku_mutu']) && count($item_satuan_klinik['multiple_baku_mutu']) > 1 ? $item_satuan_klinik['multiple_baku_mutu'] : null;
                                                            $kesimpulan = $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '';
                                                            $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                                                            $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien ?? null;
                                                            $result_badge = \Smt\Masterweb\Helpers\Smt::checkBakuMutu($hasil_value, $min, $max, $equal, $offset, $multipleBakuMutu, $kesimpulan, $pasien_umur, $pasien_gender, $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? null);
                                                            @endphp
                                                        {!! $result_badge ?: rubahNilaikeForm($hasil_value) !!}
                                                    @if (isset($item_satuan_klinik['has_selected_history']) && $item_satuan_klinik['has_selected_history'])
                                                        <span class="badge badge-info badge-sm ml-1" title="Hasil ini dipilih dari history">
                                                            <i class="fa fa-history"></i> Dari History
                                                        </span>
                                                    @endif
                                                @else
                                                        <span class="text-muted">-</span>
                                                @endif
                                                    </div>
                                                {{-- Hidden div untuk simulasi output --}}
                                                <div id="result_output_param_{{ $no }}" style="display: none;"
                                                @if (isset($item_satuan_klinik['multiple_baku_mutu']) && count($item_satuan_klinik['multiple_baku_mutu']) > 1) data-multiple-baku-mutu="{{ json_encode($item_satuan_klinik['multiple_baku_mutu']) }}" @endif
                                                data-history-count="{{ $item_satuan_klinik['history_count'] ?? 0 }}"></div>

                                            {{-- Display Status Verifikasi - akan dipindahkan ke samping tombol Baku Mutu oleh JavaScript --}}
                                            @php
                                                $status_verifikasi_display_param = $item_satuan_klinik['status_verifikasi'] ?? 'pending';
                                                $status_labels = [
                                                    'pending' => ['label' => 'Belum Diverifikasi', 'class' => 'warning', 'icon' => 'clock'],
                                                    'approved' => ['label' => 'Diterima', 'class' => 'success', 'icon' => 'check-circle'],
                                                    'rejected' => ['label' => 'Ditolak', 'class' => 'danger', 'icon' => 'times-circle'],
                                                    'corrected' => ['label' => 'Diperbaiki', 'class' => 'info', 'icon' => 'edit']
                                                ];
                                                $status_info_param = $status_labels[$status_verifikasi_display_param] ?? $status_labels['pending'];
                                            
                                            @endphp
                                            <div class="status-verifikasi-badge" id="status_verifikasi_badge_param_{{ $no }}" style="display: none;">
                                                <span class="badge badge-{{ $status_info_param['class'] }}" style="font-size: 12px; padding: 6px 10px;">
                                                    <i class="fa fa-{{ $status_info_param['icon'] }} mr-1"></i>
                                                    {{ $status_info_param['label'] }}
                                                </span>
                                                        </div>
                                        </td>
                                        {{-- Kolom Verifikasi (dropdown status) --}}
                                        <td class="align-middle verifikasi-col" style="width: 14%;">

                                            <input type="hidden" id="keterangan_param_{{ $no }}"
                                                name="keterangan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                value="{{ $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '' }}">

                                            <select class="form-control form-control-sm status-verifikasi status-verifikasi-inline"
                                                data-type="param"
                                                data-index="{{ $no }}"
                                                data-hidden-id="status_verifikasi_param_{{ $no }}">
                                                <option value="pending" {{ $status_verifikasi_display_param == 'pending' ? 'selected' : '' }}>Belum Diverifikasi</option>
                                                <option value="approved" {{ $status_verifikasi_display_param == 'approved' ? 'selected' : '' }}>Diterima</option>
                                                <option value="rejected" {{ $status_verifikasi_display_param == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                <option value="corrected" {{ $status_verifikasi_display_param == 'corrected' ? 'selected' : '' }}>Diperbaiki</option>
                                            </select>
                                        </td>
                                        {{-- Kolom Catatan: selalu bisa diisi, termasuk saat Diterima --}}
                                        <td class="align-middle catatan-col" style="width: 16%;">
                                            <textarea class="form-control form-control-sm komentar-verifikasi-inline"
                                                rows="2"
                                                placeholder="Catatan verifikasi (opsional)"
                                                data-type="param"
                                                data-index="{{ $no }}"
                                                data-hidden-id="komentar_verifikasi_param_{{ $no }}">{{ $item_satuan_klinik['komentar_verifikasi'] ?? '' }}</textarea>
                                        </td>
                                        <td class="text-center align-middle">
                                            {!! isset($item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'])
                                                ? $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik']
                                                : '-' !!}
                                            <input type="hidden"
                                                name="satuan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                value="{{ $item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] ?? '' }}">
                                            <input type="hidden"
                                                name="baku_mutu_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                value="{{ $item_satuan_klinik['id_baku_mutu'] ?? '' }}">
                                        </td>
                                        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._metode-parameter-column', [
                                            'item_satuan_klinik' => $item_satuan_klinik,
                                            'method_input_id' => 'method_permohonan_uji_parameter_klinik_' . $no,
                                            'method_index' => $no,
                                        ])
                                        <td style="display: none;"></td>
                                        <td class="align-middle nilai-normal-cell {{ nilaiNormalAlignClass($item_satuan_klinik) }}">
                                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._nilai-normal-parameter', ['item_satuan_klinik' => $item_satuan_klinik])
                                        </td>
                                    </tr>
                                @endif

                                @php
                                    $no++;
                                @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                </div>
                <!-- Indikator scroll di bagian bawah -->
                <div class="table-scroll-indicator">
                    <div class="table-scroll-indicator-content">
                        <i class="fa fa-chevron-down"></i>
                        <span>Masih ada parameter di bawah</span>
                    </div>
                </div>
                <!-- Counter badge -->
                <div class="parameter-counter-badge" id="parameterCounterBadge">
                    <i class="fa fa-list"></i>
                    <span id="parameterCounterText">0 parameter tersisa</span>
                </div>
            </div>
        </div>

        <!-- Catatan Hasil -->
        <div class="form-section" style="margin-top: 30px;">
            <h5>
                <i class="fa fa-file-alt"></i>
                Catatan Hasil
            </h5>
            <div class="form-group">
                <textarea 
                    name="catatan_hasil" 
                    id="catatan_hasil" 
                    class="form-control" 
                    rows="5" 
                    placeholder="Masukkan catatan hasil pemeriksaan...">{{ old('catatan_hasil', $item_permohonan_uji_klinik->catatan_hasil ?? '') }}</textarea>
            </div>
        </div>

        <!-- Kesimpulan Hasil -->
        <div class="form-section" style="margin-top: 30px;">
            <h5>
                <i class="fa fa-file-alt"></i>
                Kesimpulan Hasil
            </h5>
            <div class="form-group">
                @php
                    $kesimpulanHasilValue = \Smt\Masterweb\Helpers\Smt::resolveKesimpulanHasilFormValue(
                        $item_permohonan_uji_klinik,
                        $arr_permohonan_parameter ?? []
                    );
                @endphp
                <textarea 
                    name="kesimpulan_hasil" 
                    id="kesimpulan_hasil" 
                    class="form-control" 
                    rows="5" 
                    placeholder="Masukkan kesimpulan hasil pemeriksaan...">{{ old('kesimpulan_hasil', $kesimpulanHasilValue) }}</textarea>
            </div>
        </div>
    <input type="hidden" name="is_selesai" id="is_selesai_verif" value="0">
    </form>

    <!-- Modal Komentar Verifikator -->
    <div class="modal fade comment-modal" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentModalLabel">
                        <i class="fa fa-comment-dots mr-2"></i>
                        Komentar Verifikator
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 12px; text-transform: uppercase;">
                            <i class="fa fa-flask mr-1"></i>Parameter:
                        </label>
                        <p class="mb-0" id="commentParameterName" style="font-size: 14px; color: #495057; font-weight: 500;"></p>
                    </div>
                    <div class="comment-content" id="commentContent"></div>
                    <div class="comment-meta">
                        <div class="comment-meta-item">
                            <i class="fa fa-info-circle"></i>
                            <span>Komentar dari Verifikator</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4 mb-4">
        <div class="col-12 text-right">
            <button type="button" class="btn btn-light btn-action mr-2"
                onclick="document.location='{{ url('/elits-permohonan-uji-klinik-2/verification/' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}'">
                <i class="fa fa-arrow-left mr-2"></i>Kembali
            </button>
            <button type="button" class="btn btn-info btn-action mr-2 btn-review-hasil-verif">
                <i class="fa fa-eye mr-2"></i>Review Hasil
            </button>
            <button type="button" class="btn btn-info btn-action mr-2 btn-approve-all">
                <i class="fa fa-check-double mr-2"></i>Approve All
            </button>
            <button type="button" class="btn btn-primary btn-action btn-simpan mr-2" id="btn-simpan-verifikasi">
                <i class="fa fa-save mr-2"></i>Simpan Verifikasi
            </button>
            <button type="button" class="btn btn-success btn-action btn-selesai-verif" id="btn-selesai-verifikasi">
                <i class="fa fa-check-circle mr-2"></i>Selesai
            </button>
        </div>
    </div>

    {{-- ============================================================
         MODAL REVIEW HASIL (Verification)
         ============================================================ --}}
    <div class="modal fade" id="modalReviewHasilVerif" tabindex="-1" role="dialog" aria-labelledby="modalReviewHasilVerifLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalReviewHasilVerifLabel">
                        <i class="fa fa-eye mr-2"></i>Review Hasil Pemeriksaan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Sesuaikan pengaturan tampilan sebelum membuka preview hasil pemeriksaan.
                    </p>

                    {{-- Ukuran Font --}}
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-text-height mr-1"></i>Ukuran Font Hasil
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum, bukan narkoba)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">6</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="verif-fontsize-slider"
                                min="6" max="20" step="0.5"
                                value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                            <span class="text-muted small ml-2">20</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-fontsize-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 90px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="verif-fontsize-input"
                                    min="6" max="20" step="0.5"
                                    value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">pt</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-fontsize-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white text-center">
                            <span id="verif-fontsize-preview-sample" style="font-size: 12pt;">
                                Contoh: Hemoglobin = <strong>14.5</strong> g/dL
                            </span>
                        </div>
                    </div>

                    {{-- Jarak Baris --}}
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-align-justify mr-1"></i>Jarak Baris (Line Spacing)
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0.5</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="verif-lineheight-slider"
                                min="0.5" max="3.0" step="0.1"
                                value="{{ $item_permohonan_uji_klinik->line_height_hasil_permohonan_uji_klinik ?? 1 }}">
                            <span class="text-muted small ml-2">3.0</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-lineheight-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="verif-lineheight-input"
                                    min="0.5" max="3.0" step="0.1"
                                    value="{{ $item_permohonan_uji_klinik->line_height_hasil_permohonan_uji_klinik ?? 1 }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">×</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-lineheight-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white">
                            <span id="verif-lineheight-preview-sample" style="line-height: 1; display: block;">
                                Contoh baris pertama: Hemoglobin = <strong>14.5</strong> g/dL<br>
                                Contoh baris kedua: Leukosit = <strong>8.200</strong> /µL
                            </span>
                        </div>
                    </div>

                    {{-- Margin Atas/Bawah Baris & Margin Kiri/Kanan Halaman --}}
                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings', [
                        'idPrefix' => 'verif-',
                        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
                    ])

                    {{-- Kop Surat --}}
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fa fa-file-alt mr-1"></i>Kop Surat
                        </label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-sm text-muted" id="verif-kop-label-text">
                                    {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                                </div>
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input" id="verif-toggle-kop"
                                    {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="verif-toggle-kop"></label>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Jika dimatikan, area kop tetap ada namun kosong (tanpa gambar).
                        </small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle mr-1"></i>
                        Pengaturan akan disimpan dan diterapkan pada hasil cetak PDF.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info" id="verif-btn-buka-review" disabled>
                        <i class="fa fa-spinner fa-spin mr-1 d-none" id="verif-review-loading-icon"></i>
                        <i class="fa fa-save mr-1" id="verif-review-save-icon"></i>
                        <span class="verif-btn-label-text">Simpan & Buka Review</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Preview Fullscreen (Verification) --}}
    <div class="modal fade" id="modalPreviewHasilVerif" tabindex="-1" role="dialog" aria-hidden="true"
         data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document"
             style="max-width: 98vw; width: 98vw; margin: 10px auto;">
            <div class="modal-content" style="height: 95vh; display: flex; flex-direction: column;">
                <div class="modal-header py-2 bg-info text-white" style="flex-shrink: 0;">
                    <h5 class="modal-title">
                        <i class="fa fa-file-alt mr-2"></i>Preview Hasil Pemeriksaan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="flex: 1; overflow: hidden;">
                    <iframe id="verif-preview-hasil-iframe"
                        src="about:blank"
                        style="width: 100%; height: 100%; border: none;"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="modal-footer py-2" style="flex-shrink: 0;">
                    <small class="text-muted mr-auto">
                        <i class="fa fa-info-circle mr-1"></i>
                        Ini adalah preview HTML. Tampilan final PDF dapat sedikit berbeda.
                    </small>
                    <button type="button" class="btn btn-outline-warning btn-sm" id="verif-btn-pengaturan-preview">
                        <i class="fa fa-cog mr-1"></i>Pengaturan Hasil
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-success btn-sm d-none" id="verif-btn-preview-lanjut-selesai">
                        <i class="fa fa-check-circle mr-1"></i>Lanjutkan & Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>



    <script>
        // Function to show comment modal
        window.showCommentModal = function(element) {
            var $badge = $(element);
            var comment = $badge.data('comment') || '';
            var parameterName = $badge.data('parameter-name') || 'Parameter';
            
            // Decode HTML entities if needed
            if (comment) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = comment;
                comment = tempDiv.textContent || tempDiv.innerText || comment;
            }
            
            // Set modal content
            $('#commentParameterName').text(parameterName);
            $('#commentContent').html(comment ? comment.replace(/\n/g, '<br>') : '<em class="text-muted">Tidak ada komentar</em>');
            
            // Show modal
            $('#commentModal').modal('show');
        };
        // Global AJAX error handler untuk handle 419 (CSRF Token Expired)
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            // Handle 419 CSRF Token Expired
            if (xhr.status === 419) {
                // Tampilkan notifikasi sebelum refresh
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Session Expired",
                        text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                        icon: "warning",
                        timer: 2000,
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false
                    }).then(function() {
                        // Refresh halaman setelah notifikasi
                        window.location.reload();
                    });
                } else {
                    // Jika SweetAlert tidak tersedia, langsung refresh
                    alert('Session Anda telah berakhir. Halaman akan di-refresh.');
                    window.location.reload();
                }
                return false; // Prevent other error handlers
            }
        });

        // Setup AJAX untuk handle 419 secara global
        $.ajaxSetup({
            statusCode: {
                419: function() {
                    // Handle 419 CSRF Token Expired
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: "Session Expired",
                            text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                            icon: "warning",
                            timer: 2000,
                            buttons: false,
                            closeOnClickOutside: false,
                            closeOnEsc: false
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        alert('Session Anda telah berakhir. Halaman akan di-refresh.');
                        window.location.reload();
                    }
                }
            }
        });

        function GetHasilParameter(row) {
            // mengkalkulasi hitungan nilai rujukan dan nilai hasil sehingga mendapatkan hasil flagnya dari baku mutu
            var val_baku_mutu = $('#baku_mutu_permohonan_uji_parameter_klinik_' + row).val();
            var val_min = $('#min_' + row).val();
            var val_max = $('#max_' + row).val();
            var val_equal = $('#equal_' + row).val();
            var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
            var cetak_hasil = '';



            $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

            if (val_hasil != null && val_hasil != '') {
                if (val_equal != null && val_equal != '') {
                    console.log(val_equal);
                    var cetak_hasil = "";

                    if (val_equal != val_hasil) {
                        cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                        $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                        $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                    } else {

                        $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                        $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');
                    }

                } else {

                    var cetak_hasil = "";

                    if ((val_min != null && val_min != '') && (val_max != null && val_max != '')) {
                        var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
                        if (parseFloat(val_hasil) >= parseFloat(val_min) && parseFloat(val_hasil) <= parseFloat(val_max)) {
                            cetak_hasil = val_hasil;
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                        } else {
                            cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);

                        }


                    } else {

                        if (val_min != null && val_min != '') {

                            if (parseFloat(val_hasil) < parseFloat(val_min)) {
                                cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                            } else {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                            }
                        }

                        if (val_max != null && val_max != '') {
                            if (parseFloat(val_hasil) > parseFloat(val_max)) {

                                cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);

                            } else {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                            }
                        }
                    }
                }


            } else {

                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');
            }

            // if (val_baku_mutu !== null && val_baku_mutu !== '-') {
            //   if (val_min != 0 && val_max != 0) {
            //     var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
            //     var cetak_hasil = "";

            //     console.log(val_hasil);

            //     // kondisi mendapatkan nilai between

            //     if (val_hasil == null || val_hasil == '') {
            //       console.log('kosong');
            //       $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            //       $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('-');
            //     } else {
            //       if (val_hasil >= val_min && val_hasil <= val_max) {
            //         cetak_hasil = val_hasil;

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       } else {
            //         cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       }
            //     }
            //   }

            //   // mendeteksi jika yang dimasukkan positif atau negatif
            //   if (val_equal != null && val_equal != 0) {
            //     var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();

            //     if (val_hasil == null || val_hasil == '') {
            //       $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            //       $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('-');
            //     } else {
            //       if (val_hasil == val_equal) {
            //         cetak_hasil = val_hasil;

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       } else {
            //         cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       }
            //     }
            //   }
            // }
        }

        // Fungsi untuk menentukan stadium CKD berdasarkan nilai e-GFR
        function getCKDStage(gfrValue) {
            var gfr = parseFloat(gfrValue);
            if (isNaN(gfr)) {
                return null;
            }

            if (gfr >= 90) {
                return 'CKD 1 : ≥ 90';
            } else if (gfr >= 60) {
                return 'CKD 2 : 60-89';
            } else if (gfr >= 45) {
                return 'CKD 3 a : 45-59';
            } else if (gfr >= 30) {
                return 'CKD 3 b : 30-44';
            } else if (gfr >= 15) {
                return 'CKD 4 : 15-29';
            } else {
                return 'CKD 5 : < 15';
            }
        }

        function calculateEfgr(gender, age, kreatinin) {
            let scr = parseFloat(kreatinin);
            let gfr = 0;

            if (gender === 'L') {
                gfr = 142 *
                    Math.pow(Math.min(scr / 0.9, 1), -0.302) *
                    Math.pow(Math.max(scr / 0.9, 1), -1.2) *
                    Math.pow(0.9938, age);
            } else {
                gfr = 142 *
                    Math.pow(Math.min(scr / 0.7, 1), -0.241) *
                    Math.pow(Math.max(scr / 0.7, 1), -1.2) *
                    Math.pow(0.9938, age) *
                    1.012;
            }

            var gfrValue = gfr.toFixed(0);

            let input = document.querySelector('[data-name="e-GFR (CKD-EPI)"]');
            if (input) {
                input.value = gfrValue;

                // Otomatis tambahkan catatan stadium CKD ke catatan_hasil
                // Skip jika sudah ada Stadium GFR/CKD dari master agar tidak dobel.
                var ckdStage = getCKDStage(gfrValue);
                if (ckdStage) {
                    var $catatanHasil = $('#catatan_hasil');
                    
                    if ($catatanHasil.length > 0) {
                        var currentCatatan = $catatanHasil.val() || '';
                        var catatanPlain = currentCatatan.replace(/<[^>]+>/g, ' ');

                        if (/Stadium\s*(GFR|CKD)/i.test(catatanPlain)) {
                            // Sudah ada dari master / input sebelumnya
                        } else {
                            var catatanText = 'Stadium CKD : ' + ckdStage;
                        
                            // Hapus catatan CKD lama jika ada (untuk menghindari duplikasi)
                            var ckdPattern = /Stadium CKD\s*:.*?(?=\n|$)/gi;
                            currentCatatan = currentCatatan.replace(ckdPattern, '').trim();
                        
                            if (currentCatatan) {
                                currentCatatan = currentCatatan + '\n' + catatanText;
                            } else {
                                currentCatatan = catatanText;
                            }
                        
                            $catatanHasil.val(currentCatatan);
                        
                            if (typeof tinymce !== 'undefined') {
                                var catatanEditor = tinymce.get('catatan_hasil');
                                if (catatanEditor && typeof catatanEditor.setContent === 'function' && !catatanEditor.removed) {
                                    catatanEditor.setContent(currentCatatan);
                                }
                            }
                        }
                    }
                }
            }
        }

        function GetHasilSubParameter(row) {
            // mengkalkulasi hitungan nilai rujukan dan nilai hasil sehingga mendapatkan hasil flagnya dari baku mutu sub parameter
            var val_baku_mutu = $('#baku_mutu_permohonan_uji_sub_parameter_klinik_' + row).val();
            var val_min = $('#min_baku_mutu_detail_parameter_klinik_' + row).val();
            var val_max = $('#max_baku_mutu_detail_parameter_klinik_' + row).val();
            var val_equal = $('#equal_baku_mutu_detail_parameter_klinik_' + row).val();
            var cetak_hasil = '';


            // $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
            // $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(0);

            if (val_baku_mutu !== null && val_baku_mutu !== '-') {
                // mendeteksi jika yang dimasukkan nilai antara

                if (val_hasil != null && val_hasil != '') {

                    if (val_equal != null && val_equal != '') {
                        var cetak_hasil = "";
                        if (val_equal != val_hasil) {
                            cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                        } else {
                            cetak_hasil = val_hasil;
                            $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                            $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                        }

                    } else {
                        var cetak_hasil = "";

                        if ((val_min != null && val_min != '') && (val_max != null && val_max != '')) {
                            var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
                            if (parseFloat(val_hasil) >= parseFloat(val_min) && parseFloat(val_hasil) <= parseFloat(
                                    val_max)) {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');

                            } else {
                                cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                            }

                        } else {

                            if (val_min != null && val_min != '') {

                                if (parseFloat(val_hasil) < parseFloat(val_min)) {
                                    cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                                } else {
                                    cetak_hasil = val_hasil;
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                                }
                            }

                            if (val_max != null && val_max != '') {
                                if (parseFloat(val_hasil) > parseFloat(val_max)) {
                                    cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');

                                } else {
                                    cetak_hasil = val_hasil;
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                                }
                            }
                        }
                    }


                } else {

                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                }



            }
        }

        $('#is_urine').on('change', function() {
            if ($(this).is(":checked")) {
                $("#spesimen_urine_permohonan_uji_klinik").css('display', 'none')
            } else {
                $("#spesimen_urine_permohonan_uji_klinik").css('display', 'block')
            }
        });

        $('#is_darah').on('change', function() {
            if ($(this).is(":checked")) {
                $("#spesimen_darah_permohonan_uji_klinik").css('display', 'none')
            } else {
                $("#spesimen_darah_permohonan_uji_klinik").css('display', 'block')
            }
        });

        $('#by_account').prop('checked', false);
        $('#by_account').on('change', function() {
            if ($(this).is(":checked")) {
                $("#name_analis_permohonan_uji_klinik").val($(this).data('name'))
                $("#nip_analis_permohonan_uji_klinik").val($(this).data('nip'))
                $("#analis_permohonan_uji_klinik").val($(this).data('id'))
                console.log("checked");

                // it is checked
            } else {
                $("#name_analis_permohonan_uji_klinik").val("Estu Lentera")
                $("#nip_analis_permohonan_uji_klinik").val("111111111111111111")
            }
        })
        var CSRF_TOKEN = $('#csrf-token').val();

        // Initialize Select2 untuk dropdown analis
        $("#analis_permohonan_uji_klinik").select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '-- Pilih Analis --',
            allowClear: true
        });

        $("#dokter_permohonan_uji_klinik").select2({
            ajax: {
                url: "{{ route('get-dokter-by-select') }}",
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        _token: CSRF_TOKEN,
                        search: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: $.map(response, function(obj) {
                            return {
                                id: obj.id,
                                text: obj.text
                            };
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Pilih dokter',
            allowClear: true
        });

        $(".satuan_permohonan_uji_parameter_klinik").select2({
            ajax: {
                url: "{{ route('getDataUnitBySelect') }}",
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        _token: CSRF_TOKEN,
                        search: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true,
            },
            placeholder: 'Pilih unit',
            allowClear: true
        });

        $(document).ready(function() {
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-apply-localstorage', [
                'permohonanId' => $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                'stepKey' => 'verifikasi',
            ])

            // Initialize TinyMCE for Catatan Hasil
            function initCatatanHasilTinyMCE() {
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                    setTimeout(initCatatanHasilTinyMCE, 300);
                    return;
                }

                if (tinymce.get('catatan_hasil')) {
                    return;
                }

                var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
                if (tinymce.baseURL === undefined ||
                    tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
                    tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                }

                if ($('#catatan_hasil').length > 0) {
                    try {
                        tinymce.init({
                            selector: '#catatan_hasil',
                            height: 300,
                            menubar: false,
                            theme: 'modern',
                            content_css: false,
                            document_base_url: window.location.origin,
                            plugins: ['lists charmap', 'searchreplace', 'paste'],
                            toolbar: 'bold italic underline | superscript subscript | charmap | bullist numlist | removeformat',
                            paste_as_text: true,
                            content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; }',
                            charmap_append: [
                                [0x00B1, 'plus-minus sign'], [0x00B2, 'superscript two'], [0x00B3, 'superscript three'],
                                [0x00B5, 'micro sign'], [0x2264, 'less-than or equal to'], [0x2265, 'greater-than or equal to'],
                                [0x2248, 'almost equal to'], [0x2260, 'not equal to'], [0x00B0, 'degree sign'],
                                [0x2103, 'degree celsius'], [0x00D7, 'multiplication sign'], [0x00F7, 'division sign'],
                                [0x03B1, 'greek small letter alpha'], [0x03B2, 'greek small letter beta'],
                                [0x03B3, 'greek small letter gamma'], [0x03BC, 'greek small letter mu']
                            ],
                            setup: function(editor) {
                                editor.on('blur', function() {
                                    $('#catatan_hasil').val(editor.getContent());
                                });
                            }
                        });
                    } catch (e) {
                        console.error('Error initializing TinyMCE for catatan_hasil:', e);
                        setTimeout(initCatatanHasilTinyMCE, 500);
                    }
                }
            }

            setTimeout(initCatatanHasilTinyMCE, 500);

                        // Initialize TinyMCE for Kesimpulan Hasil
            function initKesimpulanHasilTinyMCE() {
                // Check if TinyMCE is fully ready
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function' ||
                    typeof tinymce.util === 'undefined' || typeof tinymce.EditorManager === 'undefined') {
                    console.log('TinyMCE not ready yet, retrying...');
                    setTimeout(initKesimpulanHasilTinyMCE, 300);
                    return;
                }

                // Check if editor already exists
                if (tinymce.get('kesimpulan_hasil')) {
                    console.log('TinyMCE editor for kesimpulan_hasil already exists');
                    return;
                }

                var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
                if (tinymce.baseURL === undefined || 
                    tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                    tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                }

                if ($('#kesimpulan_hasil').length > 0) {
                    try {
                        tinymce.init({
                            selector: '#kesimpulan_hasil',
                            height: 300,
                            menubar: false,
                            theme: 'modern',
                            content_css: false,
                            document_base_url: window.location.origin,
                            plugins: [
                                'lists charmap',
                                'searchreplace',
                                'paste'
                            ],
                            toolbar: 'bold italic underline | superscript subscript | charmap | ' +
                                'bullist numlist | removeformat',
                            paste_as_text: true,
                            content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; }',
                            charmap_append: [
                                [0x00B1, 'plus-minus sign'],
                                [0x00B2, 'superscript two'],
                                [0x00B3, 'superscript three'],
                                [0x00B5, 'micro sign'],
                                [0x2264, 'less-than or equal to'],
                                [0x2265, 'greater-than or equal to'],
                                [0x2248, 'almost equal to'],
                                [0x2260, 'not equal to'],
                                [0x00B0, 'degree sign'],
                                [0x2103, 'degree celsius'],
                                [0x00D7, 'multiplication sign'],
                                [0x00F7, 'division sign'],
                                [0x03B1, 'greek small letter alpha'],
                                [0x03B2, 'greek small letter beta'],
                                [0x03B3, 'greek small letter gamma'],
                                [0x03BC, 'greek small letter mu']
                            ],
                            setup: function(editor) {
                                editor.on('init', function() {
                                    console.log('TinyMCE editor for kesimpulan_hasil initialized');
                                });
                                
                                editor.on('blur', function() {
                                    // Sync content to textarea for form submission
                                    var content = editor.getContent();
                                    $('#kesimpulan_hasil').val(content);
                                });
                            }
                        });
                    } catch(e) {
                        console.error('Error initializing TinyMCE for kesimpulan_hasil:', e);
                        setTimeout(initKesimpulanHasilTinyMCE, 500);
                    }
                }
            }
            
            // Initialize after a short delay to ensure TinyMCE is loaded
            // setTimeout(initCatatanHasilTinyMCE, 500);
            setTimeout(initKesimpulanHasilTinyMCE, 500);

            // Sticky Data Pasien Handler
            (function() {
                var $wrapper = $('#patientDataStickyWrapper');
                var $spacer = $('.patient-data-spacer');
                var stickyOffset = 0;
                var isSticky = false;
                var isExpanded = false;

                function updateTableHeaderPosition() {
                    var $tableHeaders = $('#table-parameter thead th');
                    if ($wrapper.hasClass('sticky')) {
                        if ($wrapper.hasClass('expanded')) {
                            $tableHeaders.removeClass('sticky-below-patient').addClass('sticky-below-patient-expanded');
                        } else {
                            $tableHeaders.removeClass('sticky-below-patient-expanded').addClass('sticky-below-patient');
                        }
                    } else {
                        $tableHeaders.removeClass('sticky-below-patient sticky-below-patient-expanded');
                    }
                }

                // Calculate initial offset
                function calculateOffset() {
                    if ($wrapper.length && $wrapper.offset()) {
                        stickyOffset = $wrapper.offset().top;
                    }
                }

                function updateSticky() {
                    var scrollTop = $(window).scrollTop();
                    
                    if (scrollTop > stickyOffset && !isSticky) {
                        isSticky = true;
                        $wrapper.addClass('sticky compact');
                        $spacer.show();
                    } else if (scrollTop <= stickyOffset && isSticky) {
                        isSticky = false;
                        isExpanded = false;
                        $wrapper.removeClass('sticky compact expanded');
                        $spacer.hide();
                        $('#expandPatientData').show();
                        $('#minimizePatientData').hide();
                    }

                    updateTableHeaderPosition();
                }

                // Handle expand/minimize buttons
                $('#expandPatientData').on('click', function() {
                    if (isSticky) {
                        isExpanded = true;
                        $wrapper.removeClass('compact').addClass('expanded');
                        $(this).hide();
                        $('#minimizePatientData').show();
                        updateTableHeaderPosition();
                    }
                });

                $('#minimizePatientData').on('click', function() {
                    if (isSticky) {
                        isExpanded = false;
                        $wrapper.removeClass('expanded').addClass('compact');
                        $(this).hide();
                        $('#expandPatientData').show();
                        updateTableHeaderPosition();
                    }
                });

                // Update on scroll
                $(window).on('scroll', function() {
                    updateSticky();
                });

                // Update on resize (offset might change)
                $(window).on('resize', function() {
                    if (!isSticky) {
                        calculateOffset();
                    }
                    updateSticky();
                });

                // Initial calculation and check
                calculateOffset();
                updateSticky();
            })();

            // Handle table parameter scroll indicator (untuk menunjukkan masih ada konten di bawah dan di atas)
            function updateTableParameterIndicator() {
                var $tableWrapper = $('#tableParameterWrapper');
                var $tableResponsive = $('#tableParameterResponsive');
                
                if ($tableWrapper.length && $tableResponsive.length) {
                    var scrollTop = $tableResponsive.scrollTop();
                    var scrollHeight = $tableResponsive[0].scrollHeight;
                    var clientHeight = $tableResponsive[0].clientHeight;
                    var maxScroll = scrollHeight - clientHeight;
                    
                    // Hitung jumlah parameter yang terlihat dan tersisa
                    var $allRows = $('#table-parameter tbody tr');
                    var totalRows = $allRows.length;
                    
                    // Hitung baris yang terlihat di viewport
                    var visibleCount = 0;
                    var firstVisibleIndex = -1;
                    var lastVisibleIndex = -1;
                    
                    $allRows.each(function(index) {
                        var $row = $(this);
                        var rowOffset = $row.position().top + $tableResponsive.scrollTop();
                        var rowHeight = $row.outerHeight();
                        var tableScrollTop = $tableResponsive.scrollTop();
                        var viewportTop = tableScrollTop;
                        var viewportBottom = tableScrollTop + clientHeight;
                        
                        // Cek apakah baris terlihat di viewport
                        if (rowOffset + rowHeight >= viewportTop && rowOffset <= viewportBottom) {
                            if (firstVisibleIndex === -1) {
                                firstVisibleIndex = index;
                            }
                            lastVisibleIndex = index;
                            visibleCount++;
                        }
                    });
                    
                    // Hitung parameter tersisa di bawah (yang belum terlihat di bagian bawah)
                    var remainingRowsBelow = totalRows - (lastVisibleIndex + 1);
                    remainingRowsBelow = Math.max(0, remainingRowsBelow);
                    
                    // Hitung parameter tersisa di atas (yang belum terlihat di bagian atas)
                    var remainingRowsAbove = firstVisibleIndex;
                    remainingRowsAbove = Math.max(0, remainingRowsAbove);
                    
                    // Update counter badge
                    var $counterBadge = $('#parameterCounterBadge');
                    var $counterText = $('#parameterCounterText');
                    if (remainingRowsBelow > 0 || remainingRowsAbove > 0) {
                        var textParts = [];
                        if (remainingRowsAbove > 0) {
                            textParts.push(remainingRowsAbove + ' di atas');
                        }
                        if (remainingRowsBelow > 0) {
                            textParts.push(remainingRowsBelow + ' di bawah');
                        }
                        $counterText.text(textParts.join(', ') + ' tersisa');
                    } else {
                        $counterText.text('Semua parameter terlihat');
                    }
                    
                    // Tampilkan/sembunyikan indikator berdasarkan apakah masih bisa scroll
                    // Indikator bawah (masih ada konten di bawah)
                    if (scrollTop < maxScroll - 10 && maxScroll > 0) {
                        // Masih ada konten di bawah
                        $tableWrapper.addClass('has-more-content');
                    } else {
                        // Sudah sampai di bawah
                        $tableWrapper.removeClass('has-more-content');
                    }
                    
                    // Indikator atas (masih ada konten di atas)
                    if (scrollTop > 10 && maxScroll > 0) {
                        // Masih ada konten di atas
                        $tableWrapper.addClass('has-content-above');
                    } else {
                        // Sudah sampai di atas
                        $tableWrapper.removeClass('has-content-above');
                    }
                }
            }

            // Attach scroll event listener untuk table parameter
            $('#tableParameterResponsive').on('scroll', function() {
                updateTableParameterIndicator();
            });

            // Initialize indicator on page load
            setTimeout(function() {
                updateTableParameterIndicator();
            }, 500); // Delay sedikit untuk memastikan DOM sudah selesai render

            // Re-check on window resize
            $(window).on('resize', function() {
                setTimeout(function() {
                    updateTableParameterIndicator();
                }, 100);
            });

            // === PASIENT DATA FOR BAKU MUTU SELECTION ===
            var pasienGender = '{{ $item_permohonan_uji_klinik->pasien->gender_pasien ?? "" }}';
            var pasienUmur = {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? 0 }};
            
            // === UPDATE STATUS VERIFIKASI COLOR ===
            function updateStatusVerifikasiColor() {
                // Update badge dan class baris berdasarkan status verifikasi
                $('.status-verifikasi').each(function() {
                    var $select = $(this);
                    var $row = $select.closest('tr');
                    var status = $select.val();
                    var needsCorrection = status !== 'approved';

                    // Update class baris
                    if (needsCorrection) {
                        $row.addClass('needs-correction');
                    } else {
                        $row.removeClass('needs-correction');
                    }

                    // Update badge "Perlu Koreksi"
                    var $badgeKoreksi = $row.find('.badge-warning:contains("Perlu Koreksi")');
                    if (needsCorrection && $badgeKoreksi.length === 0) {
                        // Tambahkan badge jika belum ada
                        var $nameCell = $row.find('td:first');
                        var $badgeContainer = $nameCell.find('.d-flex');
                        if ($badgeContainer.length === 0) {
                            // Jika belum ada container, buat dulu
                            var $content = $nameCell.find('p, span').first();
                            $nameCell.html('<div class="d-flex align-items-center">' +
                                '<div style="flex: 1;">' + $content.html() + '</div>' +
                                '<span class="badge badge-warning ml-2" title="Perlu dikoreksi">' +
                                '<i class="fa fa-exclamation-triangle"></i> Perlu Koreksi</span>' +
                                '</div>');
                        } else {
                            // Tambahkan badge ke container yang sudah ada
                            if ($badgeContainer.find('.badge-warning:contains("Perlu Koreksi")').length ===
                                0) {
                                $badgeContainer.append(
                                    '<span class="badge badge-warning ml-2" title="Perlu dikoreksi">' +
                                    '<i class="fa fa-exclamation-triangle"></i> Perlu Koreksi</span>');
                            }
                        }
                    } else if (!needsCorrection && $badgeKoreksi.length > 0) {
                        // Hapus badge jika status sudah approved
                        $badgeKoreksi.remove();
                    }

                    // Update warna dropdown status verifikasi
                    var value = $select.val();
                    $select.removeClass('status-pending status-approved status-rejected status-corrected');

                    if (value === 'pending') {
                        $select.addClass('status-pending').css({
                            'background-color': '#ffc107',
                            'color': '#212529',
                            'border-color': '#ffc107'
                        });
                    } else if (value === 'approved') {
                        $select.addClass('status-approved').css({
                            'background-color': '#28a745',
                            'color': 'white',
                            'border-color': '#28a745'
                        });
                    } else if (value === 'rejected') {
                        $select.addClass('status-rejected').css({
                            'background-color': '#dc3545',
                            'color': 'white',
                            'border-color': '#dc3545'
                        });
                    } else if (value === 'corrected') {
                        $select.addClass('status-corrected').css({
                            'background-color': '#17a2b8',
                            'color': 'white',
                            'border-color': '#17a2b8'
                        });
                    }
                });

                // Update badge berdasarkan komentar
                $('.komentar-verifikasi').each(function() {
                    var $textarea = $(this);
                    var $row = $textarea.closest('tr');
                    var hasComment = $textarea.val().trim() !== '';

                    // Update badge "Ada Komentar"
                    var $badgeComment = $row.find('.badge-info:contains("Ada Komentar")');
                    if (hasComment && $badgeComment.length === 0) {
                        var $nameCell = $row.find('td:first');
                        var $badgeContainer = $nameCell.find('.d-flex');
                        if ($badgeContainer.length > 0) {
                            if ($badgeContainer.find('.badge-info:contains("Ada Komentar")').length === 0) {
                                $badgeContainer.append(
                                    '<span class="badge badge-info ml-2" title="Memiliki komentar">' +
                                    '<i class="fa fa-comment"></i> Ada Komentar</span>');
                            }
                        }
                    } else if (!hasComment && $badgeComment.length > 0) {
                        $badgeComment.remove();
                    }
                });
            }

            // Update warna & hidden field saat status verifikasi berubah
            $(document).on('change', '.status-verifikasi', function() {
                var $select = $(this);
                var value = $select.val();

                // Sinkronkan ke hidden input kalau ada
                var hiddenId = $select.data('hidden-id');
                if (hiddenId) {
                    $('#' + hiddenId).val(value);
                }

                // Update badge status verifikasi di kolom hasil
                var type = $select.data('type');
                var index = $select.data('index');
                if (type && typeof updateStatusVerifikasiBadge === 'function') {
                    updateStatusVerifikasiBadge(type, index, value);
                }

                updateStatusVerifikasiColor();

                // Tandai row sebagai dikoreksi jika status = corrected
                var $row = $select.closest('tr');
                if (value === 'corrected') {
                    $row.addClass('data-dikoreksi');
                } else {
                    $row.removeClass('data-dikoreksi');
                }
            });

            // Update badge saat komentar verifikasi berubah
            $(document).on('input', '.komentar-verifikasi', function() {
                var $textarea = $(this);
                var $row = $textarea.closest('tr');
                var hasComment = $textarea.val().trim() !== '';

                // Update badge "Ada Komentar"
                var $badgeComment = $row.find('.badge-info:contains("Ada Komentar")');
                if (hasComment && $badgeComment.length === 0) {
                    var $nameCell = $row.find('td:first');
                    var $badgeContainer = $nameCell.find('.d-flex');
                    if ($badgeContainer.length > 0) {
                        if ($badgeContainer.find('.badge-info:contains("Ada Komentar")').length === 0) {
                            $badgeContainer.append(
                                '<span class="badge badge-info ml-2" title="Memiliki komentar">' +
                                '<i class="fa fa-comment"></i> Ada Komentar</span>');
                        }
                    }
                } else if (!hasComment && $badgeComment.length > 0) {
                    $badgeComment.remove();
                }
            });

            // Update warna saat halaman dimuat
            updateStatusVerifikasiColor();

            // Handler untuk komentar verifikasi inline: sinkron ke hidden & tampilan
            $(document).on('input', '.komentar-verifikasi-inline', function() {
                var $textarea = $(this);
                var komentar = $textarea.val();
                var hiddenId = $textarea.data('hidden-id');
                if (hiddenId) {
                    $('#' + hiddenId).val(komentar);
                }

                var type = $textarea.data('type');
                var index = $textarea.data('index');
                if (type && typeof updateKomentarVerifikasiDisplay === 'function') {
                    updateKomentarVerifikasiDisplay(type, index, komentar);
                }
            });

            window.convertToTinyMCE = function(value) {
                if (!value) return '';
                value = String(value);
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                return value;
            };

            window.convertFromTinyMCE = function(value) {
                if (!value) return '';
                value = String(value);
                value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
                value = value.replace(/<[^>]*>/g, '');
                value = value.replace(/&le;/gi, '≤');
                value = value.replace(/&ge;/gi, '≥');
                value = value.replace(/&lt;/g, '<');
                value = value.replace(/&gt;/g, '>');
                value = value.replace(/&plusmn;/g, '±');
                value = value.replace(/&nbsp;/g, ' ');
                return value;
            };

            // Initialize all previews on page load
            setTimeout(function() {
                $('.result_method_klinik').each(function() {
                    var $textarea = $(this);
                    var targetId = $textarea.attr('id');
                    var methodId = null;
                    var m = targetId && targetId.match(/hasil_permohonan_uji_sub_parameter_klinik_(\d+)/);
                    if (m) {
                        methodId = 'sub_' + m[1];
                    } else {
                        m = targetId && targetId.match(/hasil_permohonan_uji_parameter_klinik_(\d+)/);
                        if (m) {
                            methodId = 'param_' + m[1];
                        }
                    }
                    if (methodId) {
                        updateResultPreview(targetId, methodId);
                    }
                });
            }, 300);

            // Function to update status verifikasi badge
            function updateStatusVerifikasiBadge(type, index, status) {
                var statusLabels = {
                    'pending': {label: 'Belum Diverifikasi', class: 'warning', icon: 'clock'},
                    'approved': {label: 'Diterima', class: 'success', icon: 'check-circle'},
                    'rejected': {label: 'Ditolak', class: 'danger', icon: 'times-circle'},
                    'corrected': {label: 'Diperbaiki', class: 'info', icon: 'edit'}
                };
                
                var statusInfo = statusLabels[status] || statusLabels['approved'];
                var badgeId = type == 'sub' ? 'status_verifikasi_badge_sub_' + index : 'status_verifikasi_badge_param_' + index;
                var $badgeContainer = $('#' + badgeId);
                
                var badgeHtml = '<span class="badge badge-' + statusInfo.class + '" style="font-size: 12px; padding: 6px 10px;">' +
                        '<i class="fa fa-' + statusInfo.icon + ' mr-1"></i>' +
                        statusInfo.label +
                    '</span>';
                
                // Update original badge (hidden)
                if ($badgeContainer.length) {
                    $badgeContainer.html(badgeHtml);
                }
                
                // Also update badge in action buttons container if it exists
                var hasilSelector = type == 'sub' ? '#result_display_sub_' + index : '#result_display_param_' + index;
                var $hasilTd = $(hasilSelector).closest('td');
                var $actionButtons = $hasilTd.find('.hasil-action-buttons');
                if ($actionButtons.length > 0) {
                    // Find badge next to Baku Mutu button
                    var $badgeInContainer = $actionButtons.find('.status-verifikasi-badge');
                    if ($badgeInContainer.length > 0) {
                        $badgeInContainer.html(badgeHtml);
                    } else {
                        // If badge doesn't exist in container yet, add it after Baku Mutu button
                        var $bakuMutuBtn = $actionButtons.find('.btn-baku-mutu-override[data-index="' + index + '"]');
                        if ($bakuMutuBtn.length > 0) {
                            var $newBadge = $('<div class="status-verifikasi-badge" style="display: inline-block; margin-left: 5px; vertical-align: middle;"></div>');
                            $newBadge.html(badgeHtml);
                            $newBadge.insertAfter($bakuMutuBtn);
                        }
                    }
                } else {
                    // If action buttons not yet created, try to move badge later
                    setTimeout(function() {
                        if (typeof window.moveStatusVerifikasiBadgeToActionButtons === 'function') {
                            window.moveStatusVerifikasiBadgeToActionButtons();
                        }
                    }, 500);
                }
            }

            // Function to update komentar verifikasi display
            function updateKomentarVerifikasiDisplay(type, index, komentar) {
                var commentId = type == 'sub' ? 'komentar_verifikasi_display_sub_' + index : 'komentar_verifikasi_display_param_' + index;
                var $commentContainer = $('#' + commentId);
                
                if ($commentContainer.length) {
                    if (komentar && komentar.trim() !== '') {
                        // Escape HTML and convert newlines to <br>
                        var escapedKomentar = $('<div>').text(komentar).html();
                        var commentHtml = escapedKomentar.replace(/\n/g, '<br>');
                        
                        // Update content and show
                        $commentContainer.find('.text-muted').html(commentHtml);
                        $commentContainer.show();
                    } else {
                        // Hide if no comment
                        $commentContainer.hide();
                    }
                } else {
                    // If container doesn't exist, create it in the keterangan column (which is hidden but still exists)
                    // Find the keterangan column td (it has display: none but still exists in DOM)
                    var keteranganTd = type == 'sub' 
                        ? $('#keterangan_sub_' + index).closest('td')
                        : $('#keterangan_param_' + index).closest('td');
                    
                    if (keteranganTd.length && komentar && komentar.trim() !== '') {
                        var escapedKomentar = $('<div>').text(komentar).html();
                        var commentContent = escapedKomentar.replace(/\n/g, '<br>');
                        
                        var commentHtml = '<div class="verification-comment mt-2" id="' + commentId + '">' +
                            '<div class="verification-comment-title" style="font-size: 11px; font-weight: 600; color: #6c757d; margin-bottom: 4px;">' +
                            '<i class="fa fa-comment-dots mr-1"></i> Komentar Verifikator:' +
                            '</div>' +
                            '<div class="text-muted" style="font-size: 12px; padding: 6px 10px; background-color: #f8f9fa; border-radius: 4px; border-left: 3px solid #17a2b8;">' +
                            commentContent +
                            '</div>' +
                            '</div>';
                        
                        keteranganTd.append(commentHtml);
                    }
                }
            }


            // Handle override baku mutu radio button changes
            $(document).on('change', '.offset_baku_mutu_klinik', function(e) {
                e.stopPropagation();
                var $radio = $(this);
                var name = $radio.attr('name');
                var selectedValue = $radio.val();

                // Find the corresponding textarea and methodId
                var $row = $radio.closest('tr');
                var $textarea = $row.find('.result_method_klinik');

                // Check if this is for sub parameter or main parameter
                var isSubParam = name.indexOf('offset_baku_mutu_sub') !== -1;
                var $editorBtn = isSubParam ?
                    $row.find('.open-editor-modal[data-method-id^="sub_"]') :
                    $row.find('.open-editor-modal[data-method-id^="param_"]');

                if ($textarea.length && $editorBtn.length) {
                    var targetId = $textarea.attr('id');
                    var methodId = $editorBtn.data('method-id');

                    // Update preview immediately
                    updateResultPreview(targetId, methodId);
                }
            });

            // Also handle click on label to ensure radio is selected
            $(document).on('click', '.form-check-label', function(e) {
                var $label = $(this);
                var $radio = $label.find('input[type="radio"]').length ? $label.find(
                    'input[type="radio"]') : $('#' + $label.attr('for'));

                if ($radio.length && $radio.hasClass('offset_baku_mutu_klinik')) {
                    $radio.prop('checked', true).trigger('change');
                }
            });

            // Handle input changes on textarea
            $(document).on('input', '.result_method_klinik', function() {
                var targetId = $(this).attr('id');
                var methodId = null;
                var $editorBtn = $(this).closest('td').find('.open-editor-modal');
                if ($editorBtn.length) {
                    methodId = $editorBtn.data('method-id');
                } else if (targetId) {
                    var m = targetId.match(/hasil_permohonan_uji_sub_parameter_klinik_(\d+)/);
                    if (m) {
                        methodId = 'sub_' + m[1];
                    } else {
                        m = targetId.match(/hasil_permohonan_uji_parameter_klinik_(\d+)/);
                        if (m) {
                            methodId = 'param_' + m[1];
                        }
                    }
                }
                if (methodId) {
                    updateResultPreview(targetId, methodId);
                }
            });

            // Initialize Select2 untuk dropdown hasil dengan is_option = 1
            // Handler ini harus di dalam $(document).ready() agar updateResultPreview tersedia
            $(".result-dropdown-klinik").each(function() {
                var $select = $(this);
                if (!$select.hasClass('select2-hidden-accessible')) {
                    var textareaId = $select.data('textarea-id');
                    var methodId = $select.data('method-id');

                    $select.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: '-- Pilih Hasil --',
                        allowClear: true
                    });

                    // Sync select value ke textarea saat change dan update preview baku mutu
                    $select.on('change', function() {
                        var selectedValue = $(this).val();
                        if (textareaId) {
                            var $textarea = $('#' + textareaId);
                            $textarea.val(selectedValue);

                            // Trigger input event untuk memastikan semua handler terpanggil
                            $textarea.trigger('input');

                            // Update preview baku mutu
                            if (methodId) {
                                // Gunakan setTimeout untuk memastikan value sudah terset
                                setTimeout(function() {
                                    updateResultPreview(textareaId, methodId);
                                }, 100);
                            }
                        }
                    });
                }
            });

            // === MULTIPLE BAKU MUTU FUNCTIONS ===
            // Normalisasi HTML TinyMCE (Shift+Enter → <br>)
            function normalizeTinyMceHasilHtml(value) {
                value = String(value || '');
                if (!value) return '';

                value = value.replace(/<p[^>]*>(?:\s|&nbsp;|<br[^>]*>)*<\/p>/gi, '<br>');
                value = value.replace(/<div[^>]*>(?:\s|&nbsp;|<br[^>]*>)*<\/div>/gi, '<br>');
                value = value.replace(/<\/p>\s*<p[^>]*>/gi, '<br>');
                value = value.replace(/<\/div>\s*<div[^>]*>/gi, '<br>');
                value = value.replace(/<p[^>]*>/gi, '');
                value = value.replace(/<\/p>/gi, '');
                value = value.replace(/<div[^>]*>/gi, '');
                value = value.replace(/<\/div>/gi, '');
                value = value.replace(/\r\n|\r|\n/g, '<br>');
                value = value.replace(/^(?:<br\s*\/?>\s*)+/gi, '');
                value = value.replace(/(?:<br\s*\/?>\s*)+$/gi, '');

                return value.trim();
            }

            function wrapMultilineHasilBadge(html) {
                html = String(html || '').trim();
                if (!html || !/<br/i.test(html)) {
                    return html;
                }
                if (/class="[^"]*\bhasil-multi-line\b/i.test(html)) {
                    return html;
                }

                return '<span class="hasil-multi-line" style="display:inline-block;text-align:left;white-space:normal;line-height:1.35;">'
                    + html
                    + '</span>';
            }

            // Format value for display (convert ^() to HTML)
            function toFormatHtml(value) {
                if (!value) return '';
                // Ensure value is a string
                value = String(value);
                
                // Convert Unicode superscript characters to <sup> tags FIRST
                // This handles characters like ³, ², ¹, etc.
                value = value.replace(/¹/g, '<sup>1</sup>');
                value = value.replace(/²/g, '<sup>2</sup>');
                value = value.replace(/³/g, '<sup>3</sup>');
                value = value.replace(/⁴/g, '<sup>4</sup>');
                value = value.replace(/⁵/g, '<sup>5</sup>');
                value = value.replace(/⁶/g, '<sup>6</sup>');
                value = value.replace(/⁷/g, '<sup>7</sup>');
                value = value.replace(/⁸/g, '<sup>8</sup>');
                value = value.replace(/⁹/g, '<sup>9</sup>');
                value = value.replace(/⁰/g, '<sup>0</sup>');
                
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                value = normalizeTinyMceHasilHtml(value);
                return wrapMultilineHasilBadge(value);
            }

            function normalizeComparisonOperatorDisplay(str) {
                if (!str) return str;
                return String(str).replace(/(^|[\s,(;])\?\s*(?=\d)/g, '$1≥ ');
            }

            function normalizeForComparison(str) {
                if (!str) return '';
                str = normalizeComparisonOperatorDisplay(str.toString());
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = str;
                var decoded = tempDiv.textContent || tempDiv.innerText || '';
                decoded = decoded.replace(/\s+/g, '');
                return decoded;
            }

            // Bandingkan equal baku mutu; "(+) NamaJenis" cocok dengan "(+)" / "Pos 1 (+)"
            function bakuMutuEqualMatches(value, equal) {
                var normalizedValue = normalizeForComparison(value || '');
                var normalizedEqual = normalizeForComparison(equal || '');
                if (normalizedValue !== '' && normalizedValue.toUpperCase() === normalizedEqual.toUpperCase()) {
                    return true;
                }

                function gradeToken(text) {
                    text = String(text || '').trim();
                    if (!text) return null;
                    var m = text.match(/\((\+{1,4})\)/);
                    if (m) return '(' + m[1] + ')';
                    m = text.match(/^(\+{1,4})$/);
                    if (m) return '(' + m[1] + ')';
                    return null;
                }

                var valueGrade = gradeToken(value);
                var equalGrade = gradeToken(equal);
                return !!(valueGrade && equalGrade && valueGrade === equalGrade);
            }

            // Create result badge based on status
            function createResultBadge(value, status, kesimpulanBakuMutu) {
                kesimpulanBakuMutu = kesimpulanBakuMutu || '';
                if (status === 'success') {
                    var kesimpulanHtml = kesimpulanBakuMutu
                        ? ' <small style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                        : '';
                    return '<span class="badge badge-success font-weight-bold" style="font-size: 14px; padding: 8px 12px; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;"><i class="fa fa-check-circle mr-1"></i>' + value + kesimpulanHtml + '</span>';
                }

                var kesimpulanHtml = kesimpulanBakuMutu && String(kesimpulanBakuMutu).trim()
                    ? '<br><small class="bm-kesimpulan-hasil" style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                    : '';
                var markedValue = String(value || '');
                var star = '<span class="bintang-baku-mutu">&nbsp;*</span>';
                markedValue = appendAbnormalAsteriskToFirstLine(markedValue, star);
                return '<span class="badge badge-danger hasil-melewati-baku-mutu" style="font-size: 14px; padding: 8px 12px; font-weight: 700; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;"><strong>' + markedValue + '</strong>' + kesimpulanHtml + '</span>';
            }

            // Hasil multi-baris: baris tambahan biasanya catatan, jadi baku mutu dinilai
            // dari baris yang mengandung nilainya (tampilan tetap memakai nilai penuh).
            function klinikHasilEvaluationValue(value, numberFormat) {
                var raw = String(value == null ? '' : value);
                if (!raw || !/<br|<\/?p\b|<\/?div\b|\r|\n/i.test(raw)) {
                    return value;
                }

                var normalized = raw
                    .replace(/<\/(p|div)>\s*<(p|div)[^>]*>/gi, '<br>')
                    .replace(/<\/?(p|div)[^>]*>/gi, '<br>')
                    .replace(/\r\n|\r|\n/g, '<br>');

                var lines = normalized.split(/<br\s*\/?>/i);
                var firstLine = null;
                var fmt = numberFormat || 'en';

                for (var i = 0; i < lines.length; i++) {
                    var line = String(lines[i] || '').trim();
                    if (!line || line.replace(/<[^>]*>/g, '').trim() === '') {
                        continue;
                    }
                    if (firstLine === null) {
                        firstLine = line;
                    }

                    var num = parseNumberInput(line, fmt);
                    if (num !== null && !isNaN(num)) {
                        return line;
                    }
                    if (typeof parseResultRange === 'function' && parseResultRange(line, fmt)) {
                        return line;
                    }
                }

                return firstLine !== null ? firstLine : value;
            }
            window.klinikHasilEvaluationValue = klinikHasilEvaluationValue;

            // Check if result exceeds baku mutu - make it accessible globally
            window.checkBakuMutu = function(value, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutuParam, numberFormat, parameterName) {
                var valueStr = (value === 0 || value === '0') ? '0' : String(value == null ? '' : value);
                if (valueStr.trim() === '' || valueStr.trim() === '-') return '';

                // Default format to 'en' if not specified (backward compatibility)
                numberFormat = numberFormat || 'en';

                offset_baku_mutu = String(offset_baku_mutu === true ? 'true' : (offset_baku_mutu === false ? 'false' : (offset_baku_mutu || 'default'))).trim().toLowerCase();
                if (offset_baku_mutu === '1' || offset_baku_mutu === 'yes') offset_baku_mutu = 'true';
                if (offset_baku_mutu === 'no') offset_baku_mutu = 'false';

                if (offset_baku_mutu === 'false') {
                    return createResultBadge(formatUrinalisaFindingsHtml(value), 'success');
                } else if (offset_baku_mutu === 'true') {
                    return createResultBadge(formatUrinalisaFindingsHtml(value), 'danger');
                }

                var urinalisaFindings = splitUrinalisaDualFindings(valueStr);
                if (urinalisaFindings.length > 1) {
                    var anyDanger = false;
                    urinalisaFindings.forEach(function(finding) {
                        var subBadge = window.checkBakuMutu(finding, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutuParam, numberFormat, parameterName);
                        if (subBadge && (subBadge.indexOf('badge-danger') !== -1 || subBadge.indexOf('hasil-melewati-baku-mutu') !== -1)) {
                            anyDanger = true;
                        }
                    });
                    return createResultBadge(formatUrinalisaFindingsHtml(value || ''), anyDanger ? 'danger' : 'success', kesimpulanBakuMutuParam || '');
                }

                // Urinalisa Lain-lain: kosong/negatif = normal, selain itu abnormal
                if (parameterName) {
                    var paramNameLower = String(parameterName).toLowerCase();
                    if (paramNameLower.indexOf('lain-lain') !== -1 || paramNameLower.indexOf('lain lain') !== -1) {
                        var valTrim = String(value).trim();
                        var formattedValue = formatUrinalisaFindingsHtml(value || '');
                        if (valTrim === '' || valTrim.toLowerCase() === 'negatif' || valTrim === '-') {
                            return createResultBadge(formattedValue, 'success', kesimpulanBakuMutuParam || '');
                        }
                        return createResultBadge(formattedValue, 'danger', kesimpulanBakuMutuParam || '');
                    }
                }

                var evalValue = klinikHasilEvaluationValue(valueStr, numberFormat);

                var melewati = false;
                var hasMultipleBakuMutu = multipleBakuMutu && multipleBakuMutu.length > 1;
                var isOutsideNormalRange = false;
                var kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';

                if (true) {
                    // Default: Check automatically based on min/max/equal
                    var numValue = null;

                    // Prioritas: cocokkan ke SEMUA baris baku mutu bila >1 (Negatif + Pos 1 (+), dll)
                    if (hasMultipleBakuMutu) {
                        var specificBakuMutu = multipleBakuMutu.filter(function(bm) {
                            var hasGenderFilter = bm.gender_baku_mutu && bm.gender_baku_mutu !== null && bm.gender_baku_mutu !== '';
                            var hasUmurFilter = bm.minimal_umur_baku_mutu !== null && bm.minimal_umur_baku_mutu !== undefined
                                && bm.maksimal_umur_baku_mutu !== null && bm.maksimal_umur_baku_mutu !== undefined;

                            if (!hasGenderFilter && !hasUmurFilter) return false;

                            var genderOk = !hasGenderFilter || (bm.gender_baku_mutu === pasienGender);
                            var umurOk = !hasUmurFilter || (pasienUmur >= parseFloat(bm.minimal_umur_baku_mutu) && pasienUmur <= parseFloat(bm.maksimal_umur_baku_mutu));
                            return genderOk && umurOk;
                        });

                        // Baris terpilih via filter gender/umur = rentang normal spesifik pasien
                        // (mis. baku mutu haji L: 13-16, P: 12-14); dianggap normal meski is_normal=0.
                        // Hanya jika filter gender/umur mempersempit kandidat (mis. haji L vs P),
                        // bukan ketika semua baris punya rentang umur yang sama (mis. HbA1c 18–99).
                        var usedDemographicSpecific = specificBakuMutu.length > 0
                            && specificBakuMutu.length < multipleBakuMutu.length;
                        var candidateBakuMutu = usedDemographicSpecific ? specificBakuMutu.slice() : multipleBakuMutu.slice();
                        candidateBakuMutu.sort(function(a, b) {
                            return (parseInt(b.is_normal, 10) || 0) - (parseInt(a.is_normal, 10) || 0);
                        });

                        var dbFormatMulti = numberFormat || 'en';
                        var hasilRangeMulti = (typeof parseResultRange === 'function') ? parseResultRange(evalValue, dbFormatMulti) : null;
                        var isHasilRangeMulti = hasilRangeMulti && hasilRangeMulti.isRange;
                        numValue = hasilRangeMulti ? hasilRangeMulti.high : parseNumberInput(evalValue, numberFormat);
                        var matchedBakuMutu = null;

                        for (var i = 0; i < candidateBakuMutu.length; i++) {
                            var bm = candidateBakuMutu[i];
                            var isWithinThisRange = false;
                            var dbFormat = numberFormat || 'en';

                            if (bm.equal) {
                                isWithinThisRange = bakuMutuEqualMatches(evalValue, bm.equal);
                            }

                            if (!isWithinThisRange && isHasilRangeMulti && typeof evaluateBakuMutuRange === 'function') {
                                var evalBm = evaluateBakuMutuRange(evalValue, bm.min, bm.max, dbFormat);
                                if (evalBm !== null) {
                                    isWithinThisRange = !evalBm;
                                }
                            } else if (!isWithinThisRange && numValue !== null && !isNaN(numValue)) {
                                if (bm.min && bm.max) {
                                    var bmMin = parseNumberInput(bm.min, dbFormat);
                                    var bmMax = parseNumberInput(bm.max, dbFormat);
                                    if (bmMin === bmMax) {
                                        isWithinThisRange = (numValue >= bmMin);
                                    } else {
                                        isWithinThisRange = (numValue >= bmMin && numValue <= bmMax);
                                    }
                                } else if (bm.min) {
                                    var bmMin = parseNumberInput(bm.min, dbFormat);
                                    isWithinThisRange = (numValue >= bmMin);
                                } else if (bm.max) {
                                    var bmMax = parseNumberInput(bm.max, dbFormat);
                                    isWithinThisRange = (numValue <= bmMax);
                                }
                            }

                            if (isWithinThisRange) {
                                matchedBakuMutu = bm;
                                break;
                            }
                        }

                        if (matchedBakuMutu) {
                            var matchedHasEqual = !!matchedBakuMutu.equal;
                            var matchedIsRange = !matchedHasEqual && (
                                (matchedBakuMutu.min !== null && matchedBakuMutu.min !== undefined && matchedBakuMutu.min !== '')
                                || (matchedBakuMutu.max !== null && matchedBakuMutu.max !== undefined && matchedBakuMutu.max !== '')
                            );

                            if (usedDemographicSpecific && matchedIsRange) {
                                // Rentang normal per gender/umur pasien: di dalam rentang = normal.
                                melewati = false;
                            } else {
                                melewati = (matchedBakuMutu.is_normal != 1);
                            }

                            if (matchedBakuMutu.kesimpulan_baku_mutu) {
                                kesimpulanBakuMutu = matchedBakuMutu.kesimpulan_baku_mutu;
                            }
                        } else {
                            melewati = true;
                        }

                        isOutsideNormalRange = melewati;
                    } else if (equal && equal !== '') {
                        melewati = !bakuMutuEqualMatches(evalValue, equal);
                    } else {
                        var dbFormat = numberFormat || 'en';
                        var hasilRange = (typeof parseResultRange === 'function') ? parseResultRange(evalValue, dbFormat) : null;
                        var isHasilRange = hasilRange && hasilRange.isRange;

                        if (isHasilRange && typeof evaluateBakuMutuRange === 'function') {
                            // Hasil tipe range (mis. "0-1"): abnormal hanya jika batas atas > max
                            var evalRange = evaluateBakuMutuRange(evalValue, min, max, dbFormat);
                            if (evalRange !== null) {
                                melewati = evalRange;
                            }
                        } else if (min && min !== '' && max && max !== '') {
                            // Parse value with number format support
                            numValue = parseNumberInput(evalValue, numberFormat);
                            if (numValue !== null && !isNaN(numValue)) {
                                var minNum = parseNumberInput(min, dbFormat);
                                var maxNum = parseNumberInput(max, dbFormat);
                                melewati = (numValue < minNum || numValue > maxNum);
                            }
                        } else if (min && min !== '') {
                            numValue = parseNumberInput(evalValue, numberFormat);
                            if (numValue !== null && !isNaN(numValue)) {
                                var minNum = parseNumberInput(min, dbFormat);
                                melewati = (numValue < minNum);
                            }
                        } else if (max && max !== '') {
                            numValue = parseNumberInput(evalValue, numberFormat);
                            if (numValue !== null && !isNaN(numValue)) {
                                var maxNum = parseNumberInput(max, dbFormat);
                                melewati = (numValue > maxNum);
                            }
                        }
                    }

                                        // Jika belum di-set kesimpulan dan tidak ada multiple baku mutu, gunakan dari parameter
                    if (!hasMultipleBakuMutu && !kesimpulanBakuMutu) {
                        kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';
                    }

                    var status = melewati ? 'danger' : 'success';
                    var formattedValue = toFormatHtml(value || '');
                    var badge = createResultBadge(formattedValue, status, kesimpulanBakuMutu);
                    
                    // Clean up any "undefined" strings in badge HTML
                    if (badge) {
                        badge = String(badge).replace(/undefined/g, '');
                    }



                    // Hapus notifikasi "Di luar semua range normal" - tidak perlu ditampilkan

                    // Final cleanup: remove any "undefined" strings before returning
                    if (badge) {
                        badge = String(badge).replace(/undefined/g, '');
                    }

                    return badge;
                }
            }

            console.log('checkBakuMutu function defined and available globally');

            
            // Set a flag to indicate checkBakuMutu is ready
            window.checkBakuMutuReady = true;

            // Run initial validation for all result_display elements immediately after checkBakuMutu is defined
            // This ensures badges with pengulangan appear on page load
            // Don't wait for document ready - run as soon as checkBakuMutu is defined
            function runInitialValidationForAllResultDisplays() {
                if (typeof window.checkBakuMutu !== 'function') {
                    setTimeout(runInitialValidationForAllResultDisplays, 100);
                    return;
                }
                
                // Wait for DOM to be ready
                if (typeof $ === 'undefined' || !$('.result-display').length) {
                    setTimeout(runInitialValidationForAllResultDisplays, 100);
                    return;
                }
                
                console.log('Running initial validation for all result_display elements (immediate)...');
                
                // Process all result_display elements
                $('.result-display').each(function() {
                        var $display = $(this);
                        var displayId = $display.attr('id');
                        if (!displayId) return;
                        
                        // Extract index from ID (result_display_sub_X or result_display_param_X)
                        var indexMatch = displayId.match(/(?:sub_|param_)(\d+)/);
                        if (!indexMatch || !indexMatch[1]) return;
                        var index = indexMatch[1];
                        var isSub = displayId.includes('sub_');
                        
                        // Get history count from result_output div
                        var resultOutputId;
                        if (isSub) {
                            resultOutputId = 'result_output_sub_' + index;
                        } else {
                            resultOutputId = 'result_output_param_' + index;
                        }
                        var $resultOutput = $('#' + resultOutputId);
                        var historyCount = 0;
                        if ($resultOutput.length > 0) {
                            historyCount = parseInt($resultOutput.data('history-count') || 0);
                        }
                        
                        // Find corresponding textarea to get value
                        var textareaId;
                        if (isSub) {
                            textareaId = 'hasil_permohonan_uji_sub_parameter_klinik_' + index;
                        } else {
                            textareaId = 'hasil_permohonan_uji_parameter_klinik_' + index;
                        }
                        var $textarea = $('#' + textareaId);
                        if ($textarea.length === 0) {
                            // Tidak perlu menambah teks \"Pengulangan\" di result_display jika tidak ada textarea.
                            return;
                        }
                        
                        var currentValue = $textarea.val();
                        // Process if there's a value OR if badge already exists from PHP
                        var existingHtml = $display.html();
                        var hasExistingBadge = existingHtml && (existingHtml.includes('badge') || existingHtml.includes('badge-success') || existingHtml.includes('badge-danger'));
                        
                        if (currentValue && currentValue.trim() !== '') {
                            // Get baku mutu data
                            var min = $textarea.data('min') || '';
                            var max = $textarea.data('max') || '';
                            var equal = $textarea.data('equal') || '';
                            var numberFormat = $textarea.data('number-format') || 'en';
                            
                            // Get offset
                            var offsetInputId;
                            if (isSub) {
                                offsetInputId = 'offset_baku_mutu_sub_' + index;
                            } else {
                                offsetInputId = 'offset_baku_mutu_param_' + index;
                            }
                            var $offsetInput = $('#' + offsetInputId);
                            var offsetBakuMutu = 'default';
                            if ($offsetInput.length > 0) {
                                offsetBakuMutu = String($offsetInput.val() || 'default').trim();
                            }
                            
                            // Get multiple baku mutu
                            var multipleBakuMutu = null;
                            if ($resultOutput.length > 0) {
                                var multipleBakuMutuData = $resultOutput.attr('data-multiple-baku-mutu');
                                if (multipleBakuMutuData) {
                                    try {
                                        multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                                    } catch(e) {
                                        multipleBakuMutu = null;
                                    }
                                }
                            }
                            
                            // Get kesimpulan
                            var kesimpulanInputId;
                            if (isSub) {
                                kesimpulanInputId = 'kesimpulan_baku_mutu_sub_' + index;
                            } else {
                                kesimpulanInputId = 'kesimpulan_baku_mutu_param_' + index;
                            }
                            var $kesimpulanInput = $('#' + kesimpulanInputId);
                            var kesimpulanBakuMutu = '';
                            if ($kesimpulanInput.length > 0 && $kesimpulanInput.val()) {
                                kesimpulanBakuMutu = $kesimpulanInput.val();
                            }
                            
                            // Generate badge with checkBakuMutu
                            var badgeHtml = window.checkBakuMutu(currentValue, min, max, equal, offsetBakuMutu, multipleBakuMutu, kesimpulanBakuMutu, numberFormat);
                            if (badgeHtml && badgeHtml !== 'undefined' && badgeHtml !== '') {
                                // Clean up any "undefined" strings
                                badgeHtml = badgeHtml.replace(/undefined/g, '');
                                
                                // Update result_display tanpa teks Pengulangan
                                $display.html(badgeHtml).removeClass('empty');
                            }
                        } else if (hasExistingBadge && historyCount > 0) {
                            // Jika badge sudah ada dari PHP, biarkan saja tanpa menambah teks Pengulangan
                        }
                    });
                    
                console.log('Initial validation for result_display completed');
            }
            
            // Run immediately after checkBakuMutu is defined
            runInitialValidationForAllResultDisplays();
            
            // Also run when document is ready (as fallback)
            if (typeof $ !== 'undefined') {
                $(document).ready(function() {
                    setTimeout(runInitialValidationForAllResultDisplays, 200);
                });
            } else {
                // Wait for jQuery to be available
                var jqueryCheck = setInterval(function() {
                    if (typeof $ !== 'undefined') {
                        clearInterval(jqueryCheck);
                        $(document).ready(function() {
                            setTimeout(runInitialValidationForAllResultDisplays, 200);
                        });
                    }
                }, 100);
                setTimeout(function() {
                    clearInterval(jqueryCheck);
                }, 5000);
            }
        

            // Update input analis display (menggunakan nilai asli dari database, bukan nilai yang sedang diedit)
            function updateInputAnalisDisplay(methodId) {
                var $inputAnalisDiv = $('#input_analis_' + methodId);

                if ($inputAnalisDiv.length === 0) {
                    return;
                }

                // Ambil data dari atribut data-* di input_analis div
                var originalValue = String($inputAnalisDiv.data('value') || '');
                var min = String($inputAnalisDiv.data('min') || '');
                var max = String($inputAnalisDiv.data('max') || '');
                var equal = String($inputAnalisDiv.data('equal') || '');
                var kesimpulanBakuMutu = String($inputAnalisDiv.data('kesimpulan-baku-mutu') || '');

                // Get multiple baku mutu data if available
                var multipleBakuMutu = null;
                var multipleBakuMutuData = $inputAnalisDiv.attr('data-multiple-baku-mutu');
                if (multipleBakuMutuData) {
                    try {
                        multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                    } catch (e) {
                        console.error('Error parsing multiple baku mutu data:', e);
                        multipleBakuMutu = null;
                    }
                }

                // Gunakan nilai asli untuk Input Analis
                var output = checkBakuMutu(originalValue, min, max, equal, 'default', multipleBakuMutu,
                    kesimpulanBakuMutu);
                $inputAnalisDiv.html(output || '<span class="text-muted">-</span>');
            }

            // Update result preview
            function updateResultPreview(targetId, methodId) {
                var $textarea = $('#' + targetId);
                var value = String($textarea.val() || '');
                var min = String($textarea.attr('data-min') || '');
                var max = String($textarea.attr('data-max') || '');
                var equal = String($textarea.attr('data-equal') || '');
                var numberFormat = $textarea.attr('data-number-format') || 'en';
                var parameterName = $textarea.attr('data-name') || '';

                if (methodId.indexOf('param_') === 0) {
                    var previewParamNo = methodId.replace('param_', '');
                    var $previewDual = $('.urinalisa-dual-input[data-param-no="' + previewParamNo + '"]');
                    if ($previewDual.length && isUrinalisaDualWrapIncomplete($previewDual)) {
                        clearUrinalisaDualPreview(previewParamNo);
                        return;
                    }
                }

                // Get offset_baku_mutu
                var offset_baku_mutu = 'default';
                var $row = $textarea.closest('tr');
                if (methodId.indexOf('sub_') !== -1) {
                    var $offsetInput = $row.find('input[id^="offset_baku_mutu_sub_"]');
                    if ($offsetInput.length) {
                        offset_baku_mutu = String($offsetInput.val() || 'default').trim();
                    }
                } else {
                    var $offsetInput = $row.find('input[id^="offset_baku_mutu_param_"]');
                    if ($offsetInput.length) {
                        offset_baku_mutu = String($offsetInput.val() || 'default').trim();
                    }
                }

                // Get multiple baku mutu data if available
                var multipleBakuMutu = null;
                var multipleBakuMutuData = $row.find('[data-multiple-baku-mutu]').attr('data-multiple-baku-mutu');
                if (multipleBakuMutuData) {
                    try {
                        multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                    } catch (e) {
                        console.error('Error parsing multiple baku mutu data:', e);
                        multipleBakuMutu = null;
                    }
                }

                // Get kesimpulan baku mutu
                var kesimpulanBakuMutu = '';
                if (methodId.includes('param_')) {
                    var paramNo = methodId.replace('param_', '');
                    kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_param_' + paramNo).val() || '';
                } else if (methodId.includes('sub_')) {
                    var subNo = methodId.replace('sub_', '');
                    kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_sub_' + subNo).val() || '';
                }

                var output = checkBakuMutu(value, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutu, numberFormat, parameterName);
                $('#result_output_' + methodId).html(output || '-');

                var badgeIndex = null;
                if (methodId.indexOf('param_') === 0) {
                    badgeIndex = methodId.replace('param_', '');
                } else if (methodId.indexOf('sub_') === 0) {
                    badgeIndex = methodId.replace('sub_', '');
                }
                if (badgeIndex !== null) {
                    var $badge = $('#badge_' + badgeIndex);
                    if ($badge.length) {
                        $badge.html(output || '');
                    }
                }

                if (methodId.includes('sub_')) {
                    var subNo = methodId.replace('sub_', '');
                    if (output && output !== '' && output !== '-') {
                        $('#result_display_sub_' + subNo).html(output).removeClass('empty');
                    } else {
                        $('#result_display_sub_' + subNo).html('<span class="text-muted">-</span>').addClass('empty');
                    }
                } else {
                    var paramNo = methodId.replace('param_', '');
                    if (output && output !== '' && output !== '-') {
                        $('#result_display_param_' + paramNo).html(output).removeClass('empty');
                    } else {
                        $('#result_display_param_' + paramNo).html('<span class="text-muted">-</span>').addClass('empty');
                    }
                }
            }

            // Urinalisa dual: grade per jenis, nama bisa lebih dari satu.
            function splitUrinalisaByGradeTokens(text) {
                text = String(text || '').trim();
                if (!text) return [];
                var re = /\((\+{1,4})\)/g;
                var matches = [];
                var m;
                while ((m = re.exec(text)) !== null) {
                    matches.push({ index: m.index, length: m[0].length });
                }
                if (matches.length <= 1) return [text];

                var segments = [];
                var i;

                // Format baru "Ca Oxalate (++) Asam Urat (+++)": tiap temuan ditutup grade.
                if (text.slice(0, matches[0].index).trim() !== '') {
                    var cursor = 0;
                    for (i = 0; i < matches.length; i++) {
                        var stop = matches[i].index + matches[i].length;
                        var trailing = text.slice(cursor, stop).trim();
                        if (!trailing) return [text];
                        segments.push(trailing);
                        cursor = stop;
                    }
                    if (text.slice(cursor).trim() !== '') return [text];
                    return segments;
                }

                for (i = 0; i < matches.length; i++) {
                    var start = matches[i].index;
                    var end = (i + 1 < matches.length) ? matches[i + 1].index : text.length;
                    var seg = text.slice(start, end).trim();
                    if (seg) segments.push(seg);
                }
                return segments.length ? segments : [text];
            }

            // Data lama "(++) Asam Urat" ditampilkan sebagai "Asam Urat (++)".
            function reorderUrinalisaGradeAfterName(line) {
                line = String(line || '').trim();
                if (!line) return '';
                var m = line.match(/^\((\+{1,4})\)\s+(.+)$/);
                if (!m) return line;
                var name = m[2].trim();
                if (!name || extractUrinalisaGradeToken(name)) return line;
                return name + ' (' + m[1] + ')';
            }

            function splitUrinalisaDualFindings(hasil) {
                hasil = String(hasil || '').trim();
                if (!hasil) return [];
                var parts = [];
                String(hasil).split(/\r\n|\r|\n/).forEach(function(chunk) {
                    chunk = chunk.trim();
                    if (!chunk) return;
                    chunk.split(/\s+\|\s+/).forEach(function(piece) {
                        piece = piece.trim();
                        if (!piece) return;
                        splitUrinalisaByGradeTokens(piece).forEach(function(finding) {
                            finding = String(finding || '').trim();
                            if (finding) parts.push(finding);
                        });
                    });
                });
                return parts;
            }

            function appendAbnormalAsteriskToFirstLine(html, mark) {
                html = String(html || '');
                mark = mark !== undefined ? mark : '&nbsp;*';
                if (!html) return mark;

                var brMatch = html.match(/<br\s*\/?>/i);
                if (!brMatch) {
                    return html + mark;
                }

                var brIdx = html.indexOf(brMatch[0]);
                var before = html.slice(0, brIdx);
                var brTag = brMatch[0];
                var after = html.slice(brIdx + brTag.length);

                var spanMatch = before.match(/^(<span\b[^>]*>)([\s\S]*)$/i);
                if (spanMatch) {
                    return spanMatch[1]
                        + '<span class="urinalisa-first-jenis" style="white-space:nowrap;">'
                        + spanMatch[2] + mark
                        + '</span>'
                        + brTag + after;
                }

                return '<span class="urinalisa-first-jenis" style="white-space:nowrap;">'
                    + before + mark
                    + '</span>'
                    + brTag + after;
            }

            function formatUrinalisaFindingsHtml(value) {
                var findings = splitUrinalisaDualFindings(value);
                if (!findings.length) return toFormatHtml(value || '');
                if (findings.length === 1) return toFormatHtml(reorderUrinalisaGradeAfterName(findings[0]));

                var normalized = [];
                var lastGrade = '';
                findings.forEach(function(finding) {
                    finding = String(finding || '').trim();
                    var grade = extractUrinalisaGradeToken(finding);
                    var name = finding
                        .replace(/^\((\+{1,4})\)\s*/, '')
                        .replace(/\s*\((\+{1,4})\)$/, '')
                        .trim();
                    if (!grade && name && lastGrade) {
                        grade = lastGrade;
                    }
                    if (grade) {
                        lastGrade = grade;
                    }
                    if (name) {
                        normalized.push(grade ? (name + ' ' + grade) : name);
                    } else if (grade) {
                        normalized.push(grade);
                    } else if (finding) {
                        normalized.push(finding);
                    }
                });

                return '<span class="urinalisa-multi-hasil" style="display:inline-block;text-align:left;white-space:nowrap;line-height:1.3;">'
                    + normalized.map(function(line) { return toFormatHtml(line); }).join('<br>')
                    + '</span>';
            }

            function isUrinalisaDualIncomplete(positivity, detail) {
                positivity = (positivity || '').trim();
                detail = (detail || '').trim();

                if (!positivity || positivity.toLowerCase() === 'negatif') {
                    return false;
                }

                return detail === '';
            }

            function extractUrinalisaGradeToken(text) {
                text = (text || '').toString().trim();
                if (!text) return null;
                var m = text.match(/\((\+{1,4})\)/);
                if (m) return '(' + m[1] + ')';
                m = text.match(/^(\+{1,4})$/);
                if (m) return '(' + m[1] + ')';
                return null;
            }

            function composeUrinalisaDualResult(positivity, detail, name) {
                positivity = (positivity || '').trim();
                detail = (detail || '').trim();
                name = (name || '').trim();

                if (!positivity || positivity.toLowerCase() === 'negatif') {
                    return 'Negatif';
                }

                // Nama diisi tanpa grade → default (+)
                if (!detail && name) {
                    detail = '(+)';
                }

                if (isUrinalisaDualIncomplete(positivity, detail)) {
                    return '';
                }

                var grade = extractUrinalisaGradeToken(detail);
                if (grade) {
                    return name ? (name + ' ' + grade) : detail;
                }

                return name ? (name + ' ' + detail) : detail;
            }

            function clearUrinalisaDualPreview(paramNo) {
                $('#badge_' + paramNo).html('');
                $('#result_output_param_' + paramNo).html('-');
                if ($('#result_display_param_' + paramNo).length) {
                    $('#result_display_param_' + paramNo).html('<span class="text-muted">-</span>').addClass('empty');
                }
            }

            function toggleUrinalisaFindingVisibility($wrap) {
                var positivity = ($wrap.find('.urinalisa-positivity-select').val() || 'Negatif').trim();
                var isNegatif = positivity.toLowerCase() === 'negatif';
                var requiresNama = String($wrap.data('requires-nama-jenis') || '0') === '1';
                var allowMultiple = String($wrap.data('allow-multiple') || '0') === '1';

                if (!requiresNama) {
                    $wrap.find('.urinalisa-detail-wrap').first().toggle(!isNegatif);
                }

                var $names = $wrap.find('.urinalisa-names');
                if ($names.length) {
                    $names.toggle(!isNegatif && requiresNama);
                }

                $wrap.find('.urinalisa-add-finding').toggle(!isNegatif && allowMultiple && requiresNama);

                if (isNegatif) {
                    $wrap.find('.urinalisa-detail-input').val('');
                }
            }

            function ensureUrinalisaDefaultGrade($detailSelect, name) {
                if ($detailSelect && $detailSelect.length) {
                    var detail = ($detailSelect.val() || '').trim();
                    if (detail || !name) {
                        return detail;
                    }
                    var $optPlus = $detailSelect.find('option').filter(function() {
                        return extractUrinalisaGradeToken($(this).val()) === '(+)';
                    }).first();
                    if ($optPlus.length) {
                        $detailSelect.val($optPlus.val());
                        return $optPlus.val();
                    }
                    return '(+)';
                }

                return name ? '(+)' : '';
            }

            function collectUrinalisaFindingRows($wrap) {
                var rows = [];
                $wrap.find('.urinalisa-names .urinalisa-name-row').each(function() {
                    var $row = $(this);
                    rows.push({
                        detail: ($row.find('.urinalisa-detail-input').val() || '').trim(),
                        name: ($row.find('.urinalisa-name-input').val() || '').trim()
                    });
                });
                return rows;
            }

            function collectUrinalisaJenisNames($wrap) {
                return collectUrinalisaFindingRows($wrap).map(function(row) {
                    return row.name;
                });
            }

            function isUrinalisaDualWrapIncomplete($wrap) {
                var positivity = ($wrap.find('.urinalisa-positivity-select').val() || 'Negatif').trim();
                if (positivity.toLowerCase() === 'negatif') {
                    return false;
                }

                var requiresNama = String($wrap.data('requires-nama-jenis') || '0') === '1';
                if (requiresNama) {
                    var rows = collectUrinalisaFindingRows($wrap);
                    return !rows.some(function(row) {
                        return !!row.name || !!row.detail;
                    });
                }

                var detail = ($wrap.find('.urinalisa-detail-wrap .urinalisa-detail-input').first().val() || '').trim();
                var hasName = collectUrinalisaJenisNames($wrap).some(function(n) { return !!n; });
                return !detail && !hasName;
            }

            function syncUrinalisaDualInput(paramNo) {
                var $wrap = $('.urinalisa-dual-input[data-param-no="' + paramNo + '"]');
                var textareaId = 'hasil_permohonan_uji_parameter_klinik_' + paramNo;
                var $textarea = $('#' + textareaId);
                var $detailHint = $('#urinalisa_detail_hint_' + paramNo);

                if (!$wrap.length || !$textarea.length) {
                    return;
                }

                toggleUrinalisaFindingVisibility($wrap);

                var positivity = ($wrap.find('.urinalisa-positivity-select').val() || 'Negatif').trim();
                var requiresNama = String($wrap.data('requires-nama-jenis') || '0') === '1';
                var composedParts = [];

                if (positivity.toLowerCase() === 'negatif') {
                    $wrap.find('.urinalisa-detail-input').removeClass('is-invalid');
                    if ($detailHint.length) {
                        $detailHint.addClass('d-none');
                    }
                    $textarea.val('Negatif');
                    updateResultPreview(textareaId, 'param_' + paramNo);
                    return;
                }

                if (requiresNama) {
                    var hasFilledRow = false;

                    $wrap.find('.urinalisa-names .urinalisa-name-row').each(function() {
                        var $row = $(this);
                        var name = ($row.find('.urinalisa-name-input').val() || '').trim();
                        var $rowDetailSelect = $row.find('.urinalisa-detail-input');
                        var detail = ensureUrinalisaDefaultGrade($rowDetailSelect, name);

                        if (!name && !detail) {
                            return;
                        }

                        hasFilledRow = true;
                        var part = composeUrinalisaDualResult(positivity, detail, name);
                        if (part) {
                            composedParts.push(part);
                        }
                    });

                    if (!hasFilledRow) {
                        $textarea.val('');
                        $wrap.find('.urinalisa-detail-input').addClass('is-invalid');
                        if ($detailHint.length) {
                            $detailHint.removeClass('d-none');
                        }
                        clearUrinalisaDualPreview(paramNo);
                        return;
                    }
                } else {
                    var $detailSelect = $wrap.find('.urinalisa-detail-wrap .urinalisa-detail-input').first();
                    var names = collectUrinalisaJenisNames($wrap);
                    var filledNames = names.filter(function(n) { return !!n; });
                    var detail = ensureUrinalisaDefaultGrade($detailSelect, filledNames.join(' '));

                    if (isUrinalisaDualIncomplete(positivity, detail) && !filledNames.length) {
                        $textarea.val('');
                        $detailSelect.addClass('is-invalid');
                        if ($detailHint.length) {
                            $detailHint.removeClass('d-none');
                        }
                        clearUrinalisaDualPreview(paramNo);
                        return;
                    }

                    if (!filledNames.length) {
                        var justGrade = composeUrinalisaDualResult(positivity, detail, '');
                        composedParts = justGrade ? [justGrade] : [];
                    } else {
                        composedParts = filledNames.map(function(name) {
                            return composeUrinalisaDualResult(positivity, detail, name);
                        }).filter(Boolean);
                    }
                }

                $wrap.find('.urinalisa-detail-input').removeClass('is-invalid');
                if ($detailHint.length) {
                    $detailHint.addClass('d-none');
                }

                $textarea.val(composedParts.join('\n'));
                updateResultPreview(textareaId, 'param_' + paramNo);
            }

            function collectIncompleteUrinalisaParams() {
                var incomplete = [];

                $('.urinalisa-dual-input').each(function() {
                    var $wrap = $(this);
                    if (!isUrinalisaDualWrapIncomplete($wrap)) {
                        return;
                    }

                    var paramNo = $wrap.data('param-no');
                    var $textarea = $('#hasil_permohonan_uji_parameter_klinik_' + paramNo);
                    var paramName = ($textarea.data('name') || '').toString().trim();

                    if (!paramName) {
                        paramName = $textarea.closest('tr').find('td').first().text().trim();
                        paramName = paramName.replace(/^[-~]\s*/, '').trim();
                    }

                    if (paramName) {
                        incomplete.push(paramName + ' (pilih grade jika Positif)');
                    }
                });

                return incomplete;
            }

            function addUrinalisaFindingRow($wrap) {
                var maxFindings = 8;
                var $list = $wrap.find('.urinalisa-names');
                if (!$list.length) {
                    return;
                }
                if ($list.find('.urinalisa-name-row').length >= maxFindings) {
                    return;
                }
                var $templateRow = $wrap.find('.urinalisa-finding-row-template .urinalisa-name-row').first();
                if (!$templateRow.length) {
                    return;
                }
                var $clone = $templateRow.clone();
                $list.append($clone);
                $clone.find('.urinalisa-detail-input').first().focus();
                syncUrinalisaDualInput($wrap.data('param-no'));
            }

            $(document).on('change', '.urinalisa-positivity-select', function() {
                var paramNo = $(this).closest('.urinalisa-dual-input').data('param-no');
                syncUrinalisaDualInput(paramNo);
            });

            $(document).on('input change', '.urinalisa-detail-input, .urinalisa-name-input', function() {
                var paramNo = $(this).closest('.urinalisa-dual-input').data('param-no');
                syncUrinalisaDualInput(paramNo);
            });

            $(document).on('click', '.urinalisa-add-finding', function(e) {
                e.preventDefault();
                e.stopPropagation();
                addUrinalisaFindingRow($(this).closest('.urinalisa-dual-input'));
            });

            $(document).on('click', '.urinalisa-remove-finding', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $wrap = $(this).closest('.urinalisa-dual-input');
                $(this).closest('.urinalisa-name-row').remove();
                syncUrinalisaDualInput($wrap.data('param-no'));
            });

            $('.urinalisa-dual-input').each(function() {
                syncUrinalisaDualInput($(this).data('param-no'));
            });

            // Auto-update all result outputs and input analis displays on page load
            // Update result_output elements
            $('[id^="result_output_"]').each(function() {
                var targetId = $(this).attr('id');
                var inputId = targetId.replace('result_output_', ''); // e.g., 'param_1' or 'sub_1'
                var $inputElement = $('#hasil_permohonan_uji_parameter_klinik_' + inputId);
                if (!$inputElement.length) {
                    $inputElement = $('#hasil_permohonan_uji_sub_parameter_klinik_' + inputId);
                }

                if ($inputElement.length) {
                    updateResultPreview($inputElement.attr('id'), inputId);
                }
            });

            // Update input_analis elements
            setTimeout(function() {
                $('[id^="input_analis_"]').each(function() {
                    var targetId = $(this).attr('id');
                    var inputId = targetId.replace('input_analis_',
                        ''); // e.g., 'param_1' or 'sub_1'

                    updateInputAnalisDisplay(inputId);
                });
            }, 100);

            // Fungsi untuk mengecek apakah ada yang masih pending
            function checkPendingVerification() {
                var hasPending = false;
                $('.status-verifikasi-inline').each(function() {
                    if ($(this).val() === 'pending') {
                        hasPending = true;
                        return false; // break loop
                    }
                });
                return hasPending;
            }

            // Fungsi untuk update status tombol submit
            function updateSubmitButtonStatus() {
                var $btnSimpan = $('#btn-simpan-verifikasi');
                var hasPending = checkPendingVerification();
                
                if (hasPending) {
                    $btnSimpan.prop('disabled', true)
                        .removeClass('btn-success')
                        .addClass('btn-secondary')
                        .attr('title', 'Terdapat parameter yang belum diverifikasi. Harap verifikasi semua parameter terlebih dahulu.');
                } else {
                    $btnSimpan.prop('disabled', false)
                        .removeClass('btn-secondary')
                        .addClass('btn-success')
                        .attr('title', '');
                }
            }

            // Update status tombol submit saat dropdown berubah
            $(document).on('change', '.status-verifikasi-inline', function() {
                updateSubmitButtonStatus();
                
                // Update badge dan hidden input
                var $select = $(this);
                var value = $select.val();
                var hiddenId = $select.data('hidden-id');
                if (hiddenId) {
                    $('#' + hiddenId).val(value);
                }

                // Update badge jika ada fungsi updateStatusVerifikasiBadge
                if (typeof updateStatusVerifikasiBadge === 'function') {
                    var index = $select.data('index');
                    var type = $select.data('type');
                    updateStatusVerifikasiBadge(type, index, value);
                }
            });

            // Inisialisasi status tombol submit saat halaman dimuat
            // Dipanggil beberapa kali dengan delay berbeda untuk memastikan semua elemen sudah ter-render
            setTimeout(function() {
                updateSubmitButtonStatus();
            }, 500);
            
            setTimeout(function() {
                updateSubmitButtonStatus();
            }, 2000);
            
            setTimeout(function() {
                updateSubmitButtonStatus();
            }, 4000);

            // Handler tombol Approve All
            $('.btn-approve-all').on('click', function() {
                swal({
                    title: "Approve All?",
                    text: "Apakah Anda yakin ingin menyetujui semua parameter sekaligus?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: false,
                            visible: true,
                            className: "btn-light"
                        },
                        confirm: {
                            text: "Ya, Approve All",
                            value: true,
                            visible: true,
                            className: "btn-info"
                        }
                    },
                    dangerMode: false
                }).then(function(confirmed) {
                    if (confirmed) {
                        // Set semua dropdown menjadi "approved"
                        $('.status-verifikasi-inline').each(function() {
                            var $select = $(this);
                            $select.val('approved');
                            
                            // Update hidden input
                            var hiddenId = $select.data('hidden-id');
                            if (hiddenId) {
                                $('#' + hiddenId).val('approved');
                            }
                            
                            // Trigger change event untuk update badge
                            $select.trigger('change');
                        });
                        
                        // Update status tombol submit
                        updateSubmitButtonStatus();
                        
                        swal({
                            title: "Berhasil!",
                            text: "Semua parameter telah disetujui.",
                            icon: "success",
                            button: "OK"
                        });
                    }
                });
            });

            $('.btn-simpan').on('click', function() {
                // Sync TinyMCE content to textarea before submit
                if (typeof tinymce !== 'undefined' && tinymce.get('catatan_hasil')) {
                    var editor = tinymce.get('catatan_hasil');
                    if (editor) {
                        editor.save();
                        $('#catatan_hasil').val(editor.getContent());
                    }
                }
                
                if (typeof tinymce !== 'undefined' && tinymce.get('kesimpulan_hasil')) {
                    var editor = tinymce.get('kesimpulan_hasil');
                    if (editor) {
                        editor.save();
                        var content = editor.getContent();
                        $('#kesimpulan_hasil').val(content);
                    }
                }

                if (typeof window.syncMetodeInlineEditorsToTextareas === 'function') {
                    window.syncMetodeInlineEditorsToTextareas();
                }

                // Sinkronkan urinalisa dual (Positif + jenis + nama) ke textarea
                $('.urinalisa-dual-input').each(function() {
                    if (typeof syncUrinalisaDualInput === 'function') {
                        syncUrinalisaDualInput($(this).data('param-no'));
                    }
                });

                if (typeof collectIncompleteUrinalisaParams === 'function') {
                    var urinalisaIncomplete = collectIncompleteUrinalisaParams();
                    if (urinalisaIncomplete.length > 0) {
                        swal({
                            title: "Peringatan!",
                            text: "Hasil Positif belum lengkap (pilih jenis terlebih dahulu):\n\n- " +
                                urinalisaIncomplete.join("\n- "),
                            icon: "warning",
                            button: "OK"
                        });
                        return false;
                    }
                }

                // Validasi: Cek apakah ada yang masih pending
                if (checkPendingVerification()) {
                    swal({
                        title: "Peringatan!",
                        text: "Terdapat parameter yang belum diverifikasi. Harap verifikasi semua parameter terlebih dahulu sebelum menyimpan.",
                        icon: "warning",
                        button: "OK"
                    });
                    return false;
                }

                // Validasi: Verifikator harus diisi terlebih dahulu
                var $verifikatorSelect = $('#verifikator_permohonan_uji_klinik');
                var verifikatorValue = $verifikatorSelect.length ? $verifikatorSelect.val() : null;
                if ($verifikatorSelect.length === 0) {
                    verifikatorValue = $('#verifikator_permohonan_uji_klinik_hidden').val();
                }
                if (!verifikatorValue || verifikatorValue === '') {
                    swal({
                        title: "Peringatan!",
                        text: "Verifikator harus diisi terlebih dahulu sebelum menyimpan verifikasi.",
                        icon: "warning",
                        button: "OK"
                    });
                    // Focus ke field verifikator
                    if ($verifikatorSelect.length) {
                        $verifikatorSelect.focus();
                    }
                    return false;
                }

                // Sinkronkan status verifikasi & komentar inline ke hidden input sebelum submit
                $('.status-verifikasi-inline').each(function() {
                    var $select = $(this);
                    var value = $select.val();
                    var hiddenId = $select.data('hidden-id');
                    if (hiddenId) {
                        $('#' + hiddenId).val(value);
                    }
                });

                $('.komentar-verifikasi-inline').each(function() {
                    var $textarea = $(this);
                    var value = $textarea.val();
                    var hiddenId = $textarea.data('hidden-id');
                    if (hiddenId) {
                        $('#' + hiddenId).val(value);
                    }
                });

                swal({
                    title: "Menyimpan Verifikasi...",
                    text: "Harap tunggu beberapa saat.",
                    icon: "info",
                    buttons: false,
                    closeOnClickOutside: false,
                });

                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Tersimpan!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    location.reload();
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '. <br>';
                                    wrapper.innerHTML = pesan;
                                });

                                swal({
                                    title: "Error!",
                                    content: wrapper,
                                    icon: "warning"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle 419 CSRF Token Expired
                        if (xhr.status === 419) {
                            swal({
                                title: "Session Expired",
                                text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                                icon: "warning",
                                timer: 2000,
                                buttons: false,
                                closeOnClickOutside: false,
                                closeOnEsc: false
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                        swal("Error!", "System gagal menyimpan!", "error");
                        }
                    }
                });
            });

            $('.btn-selesai-verif').on('click', function() {
                var $btn = $(this);
                if ($btn.data('submitting')) {
                    return false;
                }
                if (typeof tinymce !== 'undefined' && tinymce.get('catatan_hasil')) {
                    var catatanEditor = tinymce.get('catatan_hasil');
                    if (catatanEditor) { catatanEditor.save(); $('#catatan_hasil').val(catatanEditor.getContent()); }
                }
                if (typeof tinymce !== 'undefined' && tinymce.get('kesimpulan_hasil')) {
                    var editor = tinymce.get('kesimpulan_hasil');
                    if (editor) { editor.save(); $('#kesimpulan_hasil').val(editor.getContent()); }
                }
                if (checkPendingVerification()) {
                    swal({ title: "Peringatan!", text: "Terdapat parameter yang belum diverifikasi.", icon: "warning", button: "OK" });
                    return false;
                }
                var $verifikatorSelect = $('#verifikator_permohonan_uji_klinik');
                var verifikatorValue = $verifikatorSelect.length ? $verifikatorSelect.val() : $('#verifikator_permohonan_uji_klinik_hidden').val();
                if (!verifikatorValue || verifikatorValue === '') {
                    swal({ title: "Peringatan!", text: "Verifikator harus diisi terlebih dahulu.", icon: "warning", button: "OK" });
                    return false;
                }
                $('.status-verifikasi-inline').each(function() {
                    var hiddenId = $(this).data('hidden-id');
                    if (hiddenId) $('#' + hiddenId).val($(this).val());
                });
                $('.komentar-verifikasi-inline').each(function() {
                    var hiddenId = $(this).data('hidden-id');
                    if (hiddenId) $('#' + hiddenId).val($(this).val());
                });
                $('#is_selesai_verif').val('1');
                $btn.data('submitting', true).prop('disabled', true);
                swal({ title: "Menyimpan Verifikasi...", text: "Harap tunggu beberapa saat.", icon: "info", buttons: false, closeOnClickOutside: false });
                $('#form').ajaxSubmit({
                    success: function(response) {
                        $('#is_selesai_verif').val('0');
                        $btn.data('submitting', false).prop('disabled', false);
                        if (response.status == true) {
                            swal({ title: "Success!", text: response.pesan, icon: "success" })
                                .then(function() {
                                    document.location = response.redirect_url || '{{ url('/elits-permohonan-uji-klinik-2/verification/' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}';
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');
                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) { pesan += value + '. <br>'; wrapper.innerHTML = pesan; });
                                swal({ title: "Error!", content: wrapper, icon: "warning" });
                            } else {
                                swal({ title: "Error!", text: response.pesan, icon: "warning" });
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#is_selesai_verif').val('0');
                        $btn.data('submitting', false).prop('disabled', false);
                        if (xhr.status === 419) {
                            swal({ title: "Session Expired", text: "Session Anda telah berakhir.", icon: "warning", timer: 2000, buttons: false }).then(function() { window.location.reload(); });
                        } else {
                            swal("Error!", "System gagal menyimpan!", "error");
                        }
                    }
                });
            });
        });
    </script>

    <!-- TinyMCE is already loaded in template admin scripts.blade.php from local assets -->
    <!-- Wait for TinyMCE to be ready before using it -->
    <script>
        // Verify TinyMCE is loaded (from template admin scripts.blade.php) and wait for it to be ready
        (function checkTinyMCELoaded() {
            if (typeof tinymce === 'undefined') {
                console.warn('TinyMCE not yet loaded, retrying...');
                setTimeout(checkTinyMCELoaded, 100);
            } else {
                console.log('TinyMCE loaded successfully from template admin');
                // Force TinyMCE to use local assets - prevent CDN loading
                var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                if (tinymce.baseURL === undefined || tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                    console.log('TinyMCE baseURL set to:', tinymce.baseURL);
                }
                // Ensure TinyMCE is fully initialized
                if (typeof tinymce.init === 'function') {
                    console.log('TinyMCE ready to use');
                }
            }
        })();
        
        // Set flag to prevent auto-initialization
        window.skipAnalisInlineEditorAutoInit = true;
    </script>

    <!-- Modal Baku Mutu Override -->
    <div class="modal fade" id="bakuMutuModal" tabindex="-1" role="dialog" aria-labelledby="bakuMutuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bakuMutuModalLabel">
                        <i class="fa fa-exclamation-triangle mr-2"></i>
                        Pilih Status Baku Mutu
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <strong id="bakuMutuParamName"></strong>
                    </p>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Status:</label>
                        <div class="offset-baku-mutu-group mt-2">
                            <div class="offset-option mb-3">
                                <input type="radio" id="baku-mutu-default" name="baku-mutu-offset" value="default" checked>
                                <label for="baku-mutu-default" class="d-block">
                                    <span class="badge badge-info"><i class="fa fa-cog"></i> Default by Sistem</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Sistem otomatis menentukan berdasarkan perbandingan hasil dengan baku mutu
                                    </small>
                                </label>
                            </div>
                            <div class="offset-option mb-3">
                                <input type="radio" id="baku-mutu-true" name="baku-mutu-offset" value="true">
                                <label for="baku-mutu-true" class="d-block">
                                    <span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> Dianggap Melewati Baku Mutu</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Paksa parameter ini dianggap tidak memenuhi syarat (melewati baku mutu), berapapun nilainya
                                    </small>
                                </label>
                            </div>
                            <div class="offset-option mb-3">
                                <input type="radio" id="baku-mutu-false" name="baku-mutu-offset" value="false">
                                <label for="baku-mutu-false" class="d-block">
                                    <span class="badge badge-success"><i class="fa fa-check-circle"></i> Tidak Dianggap Melewati Baku Mutu</span>
                                    <small class="d-block text-muted mt-1" style="font-size: 11px;">
                                        <i class="fa fa-info-circle"></i> Paksa parameter ini dianggap memenuhi syarat (tidak melewati baku mutu), berapapun nilainya
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="baku-mutu-save-btn">
                        <i class="fa fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="fa fa-history mr-2"></i>
                        History Pemeriksaan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="historyModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    
    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._metode-inline-editor-script')

    <!-- Analis Inline Editing Script -->
    <script>
        var scriptLoading = false;
        function loadAnalisInlineEditor() {
            // Prevent double loading
            if (scriptLoading) {
                console.log('Script is already being loaded, skipping...');
                return;
            }
            
            // Check if script is already loaded
            if (typeof AnalisInlineEditor !== 'undefined') {
                console.log('AnalisInlineEditor already loaded');
                initializeEditor();
                return;
            }
            
            // Check if script tag already exists
            var existingScript = document.querySelector('script[src*="analis-inline-editing.js"]');
            if (existingScript) {
                console.log('analis-inline-editing.js script tag already exists, waiting for it to load...');
                scriptLoading = true;
                var checkInterval = setInterval(function() {
                    if (typeof AnalisInlineEditor !== 'undefined') {
                        clearInterval(checkInterval);
                        scriptLoading = false;
                        console.log('AnalisInlineEditor is now available');
                        initializeEditor();
                    }
                }, 100);
                // Timeout after 5 seconds
                setTimeout(function() {
                    clearInterval(checkInterval);
                    scriptLoading = false;
                    if (typeof AnalisInlineEditor === 'undefined') {
                        console.error('AnalisInlineEditor failed to load after 5 seconds!');
                    }
                }, 5000);
                return;
            }
            
            // Mark as loading
            scriptLoading = true;
            
            // Load script dynamically
            var script = document.createElement('script');
            script.src = '{{ asset("assets/js/analis-inline-editing.js") }}?v={{ time() }}';
            script.onload = function() {
                console.log('analis-inline-editing.js loaded successfully');
                // Wait a bit for the script to initialize
                setTimeout(function() {
                    if (typeof AnalisInlineEditor === 'undefined') {
                        console.error('AnalisInlineEditor failed to initialize after loading script!');
                    } else {
                        console.log('AnalisInlineEditor is now available');
                        initializeEditor();
                    }
                }, 100);
            };
            script.onerror = function() {
                console.error('Failed to load analis-inline-editing.js! Check if the file exists.');
            };
            document.head.appendChild(script);
        }
        
        $(document).ready(function() {
            // Wait for TinyMCE to be available before loading analis-inline-editing.js
            function waitForTinyMCE() {
                if (typeof tinymce === 'undefined') {
                    console.warn('TinyMCE not yet available, retrying in 200ms...');
                    setTimeout(waitForTinyMCE, 200);
                    return;
                }
                console.log('TinyMCE is available, loading analis-inline-editing.js...');
                setTimeout(loadAnalisInlineEditor, 100);
            }
            
            // Start waiting for TinyMCE
            waitForTinyMCE();
        });
        
        // Function to initialize and run validation
        var editorInitialized = false;
        function initializeEditor() {
            // Prevent double initialization
            if (editorInitialized) {
                console.log('initializeEditor already called, skipping...');
                return;
            }
            
            // Verify dependencies
            if (typeof $ === 'undefined') {
                console.error('jQuery is not loaded!');
                setTimeout(initializeEditor, 200);
                return;
            }
            
            if (typeof swal === 'undefined') {
                console.warn('SweetAlert is not loaded yet, but will be available when needed.');
            }
            
            // Wait for AnalisInlineEditor to be available
            if (typeof AnalisInlineEditor === 'undefined') {
                console.warn('AnalisInlineEditor not yet available, retrying...');
                setTimeout(initializeEditor, 200);
                return;
            }
            
            // Wait for checkBakuMutu to be available
            if (typeof window.checkBakuMutu === 'undefined') {
                console.warn('checkBakuMutu not yet available, retrying...');
                setTimeout(initializeEditor, 200);
                return;
            }
            
            // Mark as initialized to prevent double calls
            editorInitialized = true;
            
            // Initialize Analis Inline Editor (only if not already initialized)
            try {
                if (AnalisInlineEditor.initialized) {
                    console.log('AnalisInlineEditor already initialized, skipping...');
                } else {
                    AnalisInlineEditor.init();
                    console.log('AnalisInlineEditor initialized successfully');
                }
                
                // Run initial validation after a short delay to ensure all editors are initialized
                // Use shorter delay to ensure badges appear quickly
                setTimeout(function() {
                    if (typeof AnalisInlineEditor.runInitialValidation === 'function') {
                        console.log('Running initial validation for all fields...');
                        AnalisInlineEditor.runInitialValidation();
                    } else {
                        console.warn('runInitialValidation function not found');
                    }
                    
                    // Move status verifikasi badge to action buttons container (next to Baku Mutu button)
                    if (typeof window.moveStatusVerifikasiBadgeToActionButtons === 'function') {
                        window.moveStatusVerifikasiBadgeToActionButtons();
                    }

                    // Setelah semua badge hasil ter-render, sinkronkan badge kecil
                    // pada tombol repeat berdasarkan data-history-count di result_output_*.
                    if (typeof window.syncRepeatButtonBadgesFromHistory === 'function') {
                        console.log('Syncing repeat button badges from history (verification page)...');
                        // Beri sedikit delay tambahan agar DOM tabel sudah stabil
                        setTimeout(function () {
                            window.syncRepeatButtonBadgesFromHistory();
                        }, 300);
                    } else {
                        console.warn('syncRepeatButtonBadgesFromHistory function not found');
                    }
                }, 1000);
                
                // Function to move status verifikasi badge to action buttons container (defined globally)
                if (typeof window.moveStatusVerifikasiBadgeToActionButtons === 'undefined') {
                    window.moveStatusVerifikasiBadgeToActionButtons = function() {
                    // Process sub parameters
                    $('[id^="status_verifikasi_badge_sub_"]').each(function() {
                            var $badge = $(this);
                            var badgeId = $badge.attr('id');
                            var indexMatch = badgeId.match(/status_verifikasi_badge_sub_(\d+)/);
                            if (!indexMatch || !indexMatch[1]) return;
                            var index = indexMatch[1];
                            
                        // Find the action buttons container di kolom hasil
                        var $hasilTd = $('#result_display_sub_' + index).closest('td');
                        var $actionButtons = $hasilTd.find('.hasil-action-buttons');
                            if ($actionButtons.length > 0) {
                                // Clone badge and append after Baku Mutu button
                                var $badgeClone = $badge.clone();
                                $badgeClone.css({
                                    'display': 'inline-block',
                                    'margin-left': '5px',
                                    'vertical-align': 'middle'
                                }).removeClass('mt-2');
                                
                                // Find Baku Mutu button and append badge after it
                                var $bakuMutuBtn = $actionButtons.find('.btn-baku-mutu-override[data-index="' + index + '"]');
                                if ($bakuMutuBtn.length > 0) {
                                    $badgeClone.insertAfter($bakuMutuBtn);
                                    // Hide original badge
                                    $badge.hide();
                                }
                            }
                        });
                        
                    // Process main parameters
                    $('[id^="status_verifikasi_badge_param_"]').each(function() {
                            var $badge = $(this);
                            var badgeId = $badge.attr('id');
                            var indexMatch = badgeId.match(/status_verifikasi_badge_param_(\d+)/);
                            if (!indexMatch || !indexMatch[1]) return;
                            var index = indexMatch[1];
                            
                        // Find the action buttons container di kolom hasil
                        var $hasilTd = $('#result_display_param_' + index).closest('td');
                        var $actionButtons = $hasilTd.find('.hasil-action-buttons');
                            if ($actionButtons.length > 0) {
                                // Clone badge and append after Baku Mutu button
                                var $badgeClone = $badge.clone();
                                $badgeClone.css({
                                    'display': 'inline-block',
                                    'margin-left': '5px',
                                    'vertical-align': 'middle'
                                }).removeClass('mt-2');
                                
                                // Find Baku Mutu button and append badge after it
                                var $bakuMutuBtn = $actionButtons.find('.btn-baku-mutu-override[data-index="' + index + '"]');
                                if ($bakuMutuBtn.length > 0) {
                                    $badgeClone.insertAfter($bakuMutuBtn);
                                    // Hide original badge
                                    $badge.hide();
                                }
                            }
                        });
                    };
                }
                
                // Try to move badges after action buttons are created (with multiple retries)
                setTimeout(function() {
                    if (typeof window.moveStatusVerifikasiBadgeToActionButtons === 'function') {
                        window.moveStatusVerifikasiBadgeToActionButtons();
                    }
                }, 1500);
                
                setTimeout(function() {
                    window.moveStatusVerifikasiBadgeToActionButtons();
                }, 2500);
                
                setTimeout(function() {
                    window.moveStatusVerifikasiBadgeToActionButtons();
                    // Update status tombol submit setelah semua elemen ter-render
                    if (typeof updateSubmitButtonStatus === 'function') {
                        updateSubmitButtonStatus();
                    }
                }, 3500);
                
                // Also run initial validation for result_display that might not have inline editors
                // This ensures all badges are displayed with pengulangan on page load
                // Run immediately after checkBakuMutu is available, don't wait for editors
                function runInitialValidationForResultDisplay() {
                    if (typeof window.checkBakuMutu === 'function') {
                        console.log('Running initial validation for result_display elements...');
                        // Process all result_display elements that have values OR already have badge content
                        $('.result-display').each(function() {
                            var $display = $(this);
                            var displayId = $display.attr('id');
                            if (!displayId) return;
                            
                            // Skip if empty
                            if ($display.hasClass('empty') && $display.text().trim() === '-') return;
                            
                            // Extract index from ID (result_display_sub_X or result_display_param_X)
                            var indexMatch = displayId.match(/(?:sub_|param_)(\d+)/);
                            if (!indexMatch || !indexMatch[1]) return;
                            var index = indexMatch[1];
                            var isSub = displayId.includes('sub_');
                            
                            // Find corresponding textarea
                            var textareaId;
                            if (isSub) {
                                textareaId = 'hasil_permohonan_uji_sub_parameter_klinik_' + index;
                            } else {
                                textareaId = 'hasil_permohonan_uji_parameter_klinik_' + index;
                            }
                            var $textarea = $('#' + textareaId);
                            if ($textarea.length === 0) return;
                            
                            var currentValue = $textarea.val();
                            // If no value in textarea, try to extract from existing badge in result_display
                            if (!currentValue || currentValue.trim() === '') {
                                // Try to extract value from existing badge HTML
                                var existingHtml = $display.html();
                                if (existingHtml && existingHtml.includes('badge')) {
                                    // Badge already exists from PHP, just add pengulangan if needed
                                    // Get history count and add pengulangan if not already present
                                    var resultOutputId;
                                    if (isSub) {
                                        resultOutputId = 'result_output_sub_' + index;
                                    } else {
                                        resultOutputId = 'result_output_param_' + index;
                                    }
                                    var $resultOutput = $('#' + resultOutputId);
                                    var historyCount = 0;
                                    if ($resultOutput.length > 0) {
                                        historyCount = parseInt($resultOutput.data('history-count') || 0);
                                    }
                                    
                                    // Tidak perlu menambah teks Pengulangan di sini; badge kecil di tombol repeat sudah cukup
                                }
                                return;
                            }
                            
                            // Get baku mutu data
                            var min = $textarea.data('min') || '';
                            var max = $textarea.data('max') || '';
                            var equal = $textarea.data('equal') || '';
                            var numberFormat = $textarea.data('number-format') || 'en';
                            
                            // Get offset
                            var offsetInputId;
                            if (isSub) {
                                offsetInputId = 'offset_baku_mutu_sub_' + index;
                            } else {
                                offsetInputId = 'offset_baku_mutu_param_' + index;
                            }
                            var $offsetInput = $('#' + offsetInputId);
                            var offsetBakuMutu = 'default';
                            if ($offsetInput.length > 0) {
                                offsetBakuMutu = String($offsetInput.val() || 'default').trim();
                            }
                            
                            // Get multiple baku mutu and history count
                            var resultOutputId;
                            if (isSub) {
                                resultOutputId = 'result_output_sub_' + index;
                            } else {
                                resultOutputId = 'result_output_param_' + index;
                            }
                            var $resultOutput = $('#' + resultOutputId);
                            var multipleBakuMutu = null;
                            var kesimpulanBakuMutu = '';
                            var historyCount = 0;
                            
                            if ($resultOutput.length > 0) {
                                var multipleBakuMutuData = $resultOutput.attr('data-multiple-baku-mutu');
                                if (multipleBakuMutuData) {
                                    try {
                                        multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                                    } catch(e) {
                                        multipleBakuMutu = null;
                                    }
                                }
                                historyCount = parseInt($resultOutput.data('history-count') || 0);
                            }
                            
                            // Tambahkan badge pengulangan pada tombol repeat/history jika ada
                            if (historyCount > 0) {
                                var $row = $display.closest('tr');
                                var $repeatBtn = $row.find('.btn-repeat-parameter').first();
                                if ($repeatBtn.length) {
                                    $repeatBtn.attr('data-repeat-count', historyCount);
                                }
                            }

                            // Get kesimpulan from hidden input
                            var kesimpulanInputId;
                            if (isSub) {
                                kesimpulanInputId = 'kesimpulan_baku_mutu_sub_' + index;
                            } else {
                                kesimpulanInputId = 'kesimpulan_baku_mutu_param_' + index;
                            }
                            var $kesimpulanInput = $('#' + kesimpulanInputId);
                            if ($kesimpulanInput.length > 0 && $kesimpulanInput.val()) {
                                kesimpulanBakuMutu = $kesimpulanInput.val();
                            }
                            
                            // Generate badge with checkBakuMutu
                            var badgeHtml = window.checkBakuMutu(currentValue, min, max, equal, offsetBakuMutu, multipleBakuMutu, kesimpulanBakuMutu, numberFormat);
                            if (badgeHtml && badgeHtml !== 'undefined' && badgeHtml !== '') {
                                // Clean up any "undefined" strings
                                badgeHtml = badgeHtml.replace(/undefined/g, '');
                                
                                // Update result_display tanpa teks Pengulangan
                                $display.html(badgeHtml).removeClass('empty');
                            }
                        });
                        console.log('Initial validation for result_display completed');

                        // Setelah semua badge & status siap, tampilkan tabel verifikasi (hilangkan state loading)
                        $('#tableParameterWrapper')
                            .removeClass('verification-table-loading')
                            .addClass('verification-table-loaded');
                    } else {
                        // Retry if checkBakuMutu not yet available
                        setTimeout(runInitialValidationForResultDisplay, 200);
                    }
                }
                
                // Run immediately when checkBakuMutu is available
                if (typeof window.checkBakuMutu === 'function') {
                    runInitialValidationForResultDisplay();
                } else {
                    // Wait for checkBakuMutu to be available
                    var checkInterval = setInterval(function() {
                        if (typeof window.checkBakuMutu === 'function') {
                            clearInterval(checkInterval);
                            runInitialValidationForResultDisplay();
                        }
                    }, 100);
                    // Timeout after 5 seconds
                    setTimeout(function() {
                        clearInterval(checkInterval);
                    }, 5000);
                }
                
                // Also run after a delay to catch any elements that might be loaded later
                setTimeout(runInitialValidationForResultDisplay, 800);
            } catch(e) {
                console.error('Error initializing AnalisInlineEditor:', e);
                editorInitialized = false; // Reset on error to allow retry
            }
        }
        
        // Function to update baku mutu status
        window.updateBakuMutuStatus = function(selectedOffset, index, isSub) {
            // Find the offset input field
            var offsetInputId;
            if (isSub) {
                offsetInputId = 'offset_baku_mutu_sub_' + index;
            } else {
                offsetInputId = 'offset_baku_mutu_param_' + index;
            }
            
            var $offsetInput = $('#' + offsetInputId);
            if ($offsetInput.length > 0) {
                    // Update hidden input value
                    $offsetInput.val(selectedOffset);
                    
                    // Update button appearance
                    var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
                    $btn.attr('data-current-offset', selectedOffset);
                    
                    if (selectedOffset === 'true') {
                        $btn.html('<i class="fa fa-exclamation-triangle"></i> Melewati');
                        $btn.removeClass('btn-warning btn-success').addClass('btn-danger');
                    } else if (selectedOffset === 'false') {
                        $btn.html('<i class="fa fa-check-circle"></i> Normal');
                        $btn.removeClass('btn-warning btn-danger').addClass('btn-success');
                    } else {
                        $btn.html('<i class="fa fa-cog"></i> Baku Mutu');
                        $btn.removeClass('btn-danger btn-success').addClass('btn-warning');
                    }
                    
                    // Update badge by triggering validation
                    var $row = $btn.closest('tr');
                    var $textarea = $row.find('textarea.result_method_klinik');
                    if ($textarea.length > 0) {
                        var textareaId = $textarea.attr('id');
                        var textareaIndex = textareaId ? textareaId.match(/\d+/)[0] : '';
                        var currentValue = $textarea.val() || '';
                        
                        // Get value from inline editor if exists
                        if (typeof tinymce !== 'undefined') {
                            try {
                                var $editor = $row.find('.inline-hasil-editor[data-index="' + textareaIndex + '"]');
                                if ($editor.length > 0) {
                                    var editorId = $editor.attr('id');
                                    if (editorId) {
                                        var editor = tinymce.get(editorId);
                                        if (editor && editor.getContent) {
                                            currentValue = editor.getContent();
                                        } else {
                                            currentValue = $editor.html() || '';
                                        }
                                    } else {
                                        currentValue = $editor.html() || '';
                                    }
                                } else {
                                    // Check for dropdown
                                    var $dropdown = $row.find('select.inline-hasil-input[data-index="' + textareaIndex + '"]');
                                    if ($dropdown.length > 0) {
                                        currentValue = $dropdown.val() || '';
                                    }
                                }
                            } catch(e) {
                                console.warn('Error getting value from editor:', e);
                            }
                        }
                        
                        var min = $textarea.data('min') || '';
                        var max = $textarea.data('max') || '';
                        var equal = $textarea.data('equal') || '';
                        var numberFormat = $textarea.data('number-format') || 'en';
                        
                        // Get multiple baku mutu data
                        var multipleBakuMutu = null;
                        var $resultOutputDiv;
                        if (isSub) {
                            $resultOutputDiv = $row.find('#result_output_sub_' + textareaIndex);
                        } else {
                            $resultOutputDiv = $row.find('#result_output_param_' + textareaIndex);
                        }
                        
                        if ($resultOutputDiv.length > 0) {
                            var multipleBakuMutuData = $resultOutputDiv.attr('data-multiple-baku-mutu');
                            if (multipleBakuMutuData) {
                                try {
                                    multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                                } catch(e) {
                                    console.warn('Error parsing multiple baku mutu:', e);
                                }
                            }
                        }
                        
                        // Get kesimpulan baku mutu
                        var kesimpulanBakuMutu = '';
                        if (isSub) {
                            kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_sub_' + textareaIndex).val() || '';
                        } else {
                            kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_param_' + textareaIndex).val() || '';
                        }
                        
                        // Trigger badge update
                        var latestOffset = String($offsetInput.val() || 'default').trim();
                        
                        setTimeout(function() {
                            if (typeof window.checkBakuMutu === 'function') {
                                var badgeHtml = window.checkBakuMutu(currentValue, min, max, equal, latestOffset, multipleBakuMutu, kesimpulanBakuMutu, numberFormat);
                                if (badgeHtml) {
                                    // Tambahkan notifikasi pengulangan jika ada
                                    var historyCount = 0;
                                    if ($resultOutputDiv.length > 0) {
                                        historyCount = parseInt($resultOutputDiv.data('history-count') || 0);
                                    }
                                    
                                    // Tidak menambah teks Pengulangan di halaman verifikasi
                                    var badgeId = 'badge_' + textareaIndex;
                                    var $badgeContainer = $('#' + badgeId);
                                    if ($badgeContainer.length > 0) {
                                        $badgeContainer.html(badgeHtml);
                                    } else {
                                        // Update result display if badge container doesn't exist
                                        var displayId = 'result_display_' + (isSub ? 'sub_' : 'param_') + textareaIndex;
                                        var $display = $('#' + displayId);
                                        if ($display.length > 0) {
                                            $display.html(badgeHtml).removeClass('empty');
                                        }
                                    }
                                }
                            }
                        }, 50);
                    }
                }
            
        };
        
        // Handler for Baku Mutu Override button
        $(document).on('click', '.btn-baku-mutu-override', function() {
            var $btn = $(this);
            var index = $btn.data('index');
            var isSub = $btn.data('is-sub') == '1';
            
            // Find the row and get parameter name
            var $row = $btn.closest('tr');
            var parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
            
            // Get current offset directly from hidden input, not from button data attribute
            var offsetInputId;
            if (isSub) {
                offsetInputId = 'offset_baku_mutu_sub_' + index;
            } else {
                offsetInputId = 'offset_baku_mutu_param_' + index;
            }
            
            var $offsetInput = $('#' + offsetInputId);
            var currentOffset = 'default';
            if ($offsetInput.length > 0) {
                currentOffset = String($offsetInput.val() || 'default').trim();
            }
            
            // Also update button's data attribute to keep it in sync
            $btn.attr('data-current-offset', currentOffset);
            
            // Set parameter name in modal
            $('#bakuMutuParamName').text(parameterName);
            
            // Set current selection
            currentOffset = String(currentOffset || 'default').trim();
            $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]').prop('checked', true);
            
            // Store data in modal for later use
            $('#bakuMutuModal').data('index', index);
            $('#bakuMutuModal').data('is-sub', isSub);
            
            // Show modal
            $('#bakuMutuModal').modal('show');
        });
        
        // Handler for modal shown event - refresh current offset when modal is opened
        $('#bakuMutuModal').on('shown.bs.modal', function() {
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';
            
            if (index !== undefined && index !== null) {
                // Get current offset directly from hidden input
                var offsetInputId;
                if (isSub) {
                    offsetInputId = 'offset_baku_mutu_sub_' + index;
                } else {
                    offsetInputId = 'offset_baku_mutu_param_' + index;
                }
                
                var $offsetInput = $('#' + offsetInputId);
                var currentOffset = 'default';
                if ($offsetInput.length > 0) {
                    currentOffset = String($offsetInput.val() || 'default').trim();
                }
                
                // Update radio button selection to match the actual value
                $('input[name="baku-mutu-offset"]').prop('checked', false);
                $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]').prop('checked', true);
            }
        });
        
        // Handler for radio button change - update immediately
        $(document).on('change', 'input[name="baku-mutu-offset"]', function() {
            var selectedOffset = $(this).val();
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';
            
            if (index !== undefined && index !== null) {
                // Update immediately when radio button changes
                setTimeout(function() {
                    if (typeof window.updateBakuMutuStatus === 'function') {
                        window.updateBakuMutuStatus(selectedOffset, index, isSub);
                    }
                }, 10);
            }
        });
        
        // Handler for saving baku mutu override (close modal)
        $('#baku-mutu-save-btn').on('click', function() {
            // Status already updated by radio button change event
            // Just close the modal
            $('#bakuMutuModal').modal('hide');
        });
        
        // Handler for View History button
        $(document).on('click', '.btn-view-history', function() {
                var $btn = $(this);
                var parameterId = $btn.data('parameter-id');
                var parameterName = $btn.data('parameter-name') || 'Parameter';
                var isSub = $btn.data('is-sub') == 1 || $btn.data('is-sub') == true;
                
                if (!parameterId) {
                    swal('Error!', 'Parameter ID tidak ditemukan', 'error');
                    return;
                }

                // Show loading
                $('#historyModalBody').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat history...</div>');
                $('#historyModalLabel').text('History: ' + parameterName);
                $('#historyModal').modal('show');

                $.ajax({
                    url: '{{ url("/elits-permohonan-uji-klinik-2/get-parameter-history") }}/' + parameterId,
                    type: 'GET',
                    data: {
                        is_sub: isSub ? 1 : 0
                    },
                    success: function(response) {
                        if (response.status) {
                            var html = '';
                            if (response.data && response.data.length > 0) {
                                html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                                html += '<thead><tr><th>No</th><th>Hasil</th><th>Waktu</th><th>Oleh</th><th>Aksi</th></tr></thead><tbody>';
                                
                                response.data.forEach(function(history, index) {
                                    var badgeClass = history.is_selected ? 'badge-success' : '';
                                    var selectedBadge = history.is_selected ? '<span class="badge ' + badgeClass + '">Dipilih</span>' : '';
                                    
                                    html += '<tr' + (history.is_selected ? ' class="table-success"' : '') + '>';
                                    html += '<td>' + (index + 1) + '</td>';
                                    var hasilDisplay = history.hasil || '-';
                                    if (history.hasil && (history.hasil.includes('<') || history.hasil.includes('&'))) {
                                        hasilDisplay = history.hasil;
                                    }
                                    html += '<td>' + hasilDisplay + '</td>';
                                    html += '<td>' + history.created_at + '</td>';
                                    html += '<td>' + history.created_by + '</td>';
                                    html += '<td>';
                                    if (!history.is_selected) {
                                        var hasilEncoded = '';
                                        if (history.hasil) {
                                            hasilEncoded = btoa(unescape(encodeURIComponent(history.hasil)));
                                        }
                                        html += '<button class="btn btn-xs btn-primary btn-select-history" data-history-id="' + history.id + '" data-parameter-id="' + parameterId + '" data-is-sub="' + (isSub ? 1 : 0) + '" data-hasil-encoded="' + hasilEncoded + '">Pilih</button>';
                                    } else {
                                        html += selectedBadge;
                                    }
                                    html += '</td>';
                                    html += '</tr>';
                                });
                                
                                html += '</tbody></table></div>';
                                
                                if (response.current_result) {
                                    html += '<div class="alert alert-info mt-3"><strong>Hasil Saat Ini:</strong> ' + response.current_result + '</div>';
                                }
                            } else {
                                html = '<div class="alert alert-warning">Belum ada history untuk parameter ini.</div>';
                            }
                            
                            $('#historyModalBody').html(html);
                        } else {
                            $('#historyModalBody').html('<div class="alert alert-danger">' + response.pesan + '</div>');
                        }
                    },
                    error: function(xhr) {
                        // Handle 419 CSRF Token Expired
                        if (xhr.status === 419) {
                            if (typeof swal !== 'undefined') {
                                swal({
                                    title: "Session Expired",
                                    text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                                    icon: "warning",
                                    timer: 2000,
                                    buttons: false,
                                    closeOnClickOutside: false,
                                    closeOnEsc: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                alert('Session Anda telah berakhir. Halaman akan di-refresh.');
                                window.location.reload();
                            }
                        } else {
                            var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat memuat history';
                            $('#historyModalBody').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                        }
                    }
                });
        });
        
        // Handler for Select History button
        $(document).on('click', '.btn-select-history', function() {
                var $btn = $(this);
                var historyId = $btn.data('history-id');
                var parameterId = $btn.data('parameter-id');
                var isSub = $btn.data('is-sub') == 1 || $btn.data('is-sub') == true;
                var historyHasil = '';
                var hasilEncoded = $btn.attr('data-hasil-encoded') || '';
                if (hasilEncoded) {
                    try {
                        historyHasil = decodeURIComponent(escape(atob(hasilEncoded)));
                    } catch(e) {
                        console.error('Error decoding history hasil:', e);
                        historyHasil = $btn.data('hasil') || '';
                    }
                } else {
                    historyHasil = $btn.data('hasil') || '';
                }
                
                // Function to select history
                var selectHistory = function() {
                    $.ajax({
                        url: '{{ url("/elits-permohonan-uji-klinik-2/select-parameter-history") }}/' + historyId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            parameter_id: parameterId,
                            is_sub: isSub ? 1 : 0
                        },
                        success: function(response) {
                            if (response.status) {
                                // Convert history hasil to TinyMCE format if needed
                                var convertedHasil = historyHasil;
                                if (typeof window.convertToTinyMCE === 'function') {
                                    convertedHasil = window.convertToTinyMCE(historyHasil);
                                }
                                
                                // Find textarea and update
                                var $row = $btn.closest('tr');
                                var $hasilTextarea = $row.find('textarea.result_method_klinik');
                                if ($hasilTextarea.length > 0) {
                                    var textareaId = $hasilTextarea.attr('id');
                                    if (textareaId) {
                                        // Update textarea value (use original format, not converted)
                                        $hasilTextarea.val(historyHasil);
                                        
                                        // Update dropdown if exists
                                        var $dropdown = $('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                                        if ($dropdown.length > 0) {
                                            $dropdown.val(historyHasil).trigger('change');
                                        }
                                        
                                        // Update TinyMCE editor if exists
                                        var $editor = $('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                                        if ($editor.length > 0) {
                                            var editorId = $editor.attr('id');
                                            if (editorId && typeof tinymce !== 'undefined') {
                                                try {
                                                    var editor = tinymce.get(editorId);
                                                    if (editor && editor.setContent) {
                                                        editor.setContent(convertedHasil);
                                                    } else {
                                                        $editor.html(convertedHasil);
                                                    }
                                                } catch(e) {
                                                    console.warn('Error setting TinyMCE content:', e);
                                                    $editor.html(convertedHasil);
                                                }
                                            } else {
                                                $editor.html(convertedHasil);
                                            }
                                        }
                                        
                                        // Update result display
                                        var index = textareaId.match(/[0-9]+/);
                                        if (index && index[0]) {
                                            var displayId = 'result_display_' + (isSub ? 'sub_' : 'param_') + index[0];
                                            var $display = $('#' + displayId);
                                            if ($display.length > 0) {
                                                $display.html(convertedHasil).removeClass('empty');
                                            }
                                        }
                                    }
                                }
                                
                                swal('Berhasil!', response.pesan, 'success').then(() => {
                                    $('#historyModal').modal('hide');
                                    location.reload();
                                });
                            } else {
                                swal('Error!', response.pesan, 'error');
                            }
                        },
                        error: function(xhr) {
                            // Handle 419 CSRF Token Expired
                            if (xhr.status === 419) {
                                swal({
                                    title: "Session Expired",
                                    text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                                    icon: "warning",
                                    timer: 2000,
                                    buttons: false,
                                    closeOnClickOutside: false,
                                    closeOnEsc: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat memilih history';
                                swal('Error!', errorMsg, 'error');
                            }
                        }
                    });
                };
                
                // If current hasil exists, save it to history first
                var $row = $btn.closest('tr');
                var $hasilTextarea = $row.find('textarea.result_method_klinik');
                var currentHasil = $hasilTextarea.length > 0 ? $hasilTextarea.val() : '';
                
                if (currentHasil && currentHasil.trim() !== '') {
                    $.ajax({
                        url: '{{ url("/elits-permohonan-uji-klinik-2/save-parameter-history") }}/' + parameterId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            is_sub: isSub ? 1 : 0,
                            hasil: currentHasil
                        },
                        success: function(response) {
                            if (response.status) {
                                selectHistory();
                            } else {
                                swal('Error!', response.pesan || 'Gagal menyimpan hasil saat ini ke history', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat menyimpan hasil saat ini ke history';
                            swal('Error!', errorMsg, 'error');
                        }
                    });
                } else {
                    selectHistory();
                }
        });
        
        // Handler for Repeat Parameter button
        $(document).on('click', '.btn-repeat-parameter', function() {
                var $btn = $(this);
                var parameterId = $btn.data('parameter-id');
                var isSub = $btn.data('is-sub') == 1 || $btn.data('is-sub') == true;
                
                if (!parameterId) {
                    swal('Error!', 'Parameter ID tidak ditemukan', 'error');
                    return;
                }

                swal({
                    title: "Konfirmasi",
                    text: "Apakah Anda yakin ingin mengulangi pemeriksaan parameter ini? Hasil saat ini akan disimpan ke history.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Batal",
                            value: false,
                            visible: true,
                            className: "btn-secondary"
                        },
                        confirm: {
                            text: "Ya, Ulangi",
                            value: true,
                            visible: true,
                            className: "btn-info"
                        }
                    },
                    dangerMode: false
                }).then((willRepeat) => {
                    if (willRepeat) {
                        // Sync all inline editor values to hidden textareas before saving
                        $('select.inline-hasil-input').each(function() {
                            var $select = $(this);
                            var selectedValue = $select.val() || '';
                            var textareaId = $select.data('textarea-id');
                            if (textareaId) {
                                $('#' + textareaId).val(selectedValue);
                            }
                        });
                        
                        // Sync TinyMCE inline editor values
                        if (typeof tinymce !== 'undefined') {
                            $('.inline-hasil-editor').each(function() {
                                var $editor = $(this);
                                var textareaId = $editor.data('textarea-id');
                                if (textareaId) {
                                    var editorId = $editor.attr('id');
                                    try {
                                        if (editorId) {
                                            var editor = tinymce.get(editorId);
                                            if (editor && editor.getContent) {
                                                var content = editor.getContent();
                                                $('#' + textareaId).val(content);
                                            } else {
                                                var content = $editor.html() || '';
                                                $('#' + textareaId).val(content);
                                            }
                                        } else {
                                            var content = $editor.html() || '';
                                            $('#' + textareaId).val(content);
                                        }
                                    } catch(e) {
                                        var content = $editor.html() || '';
                                        $('#' + textareaId).val(content);
                                    }
                                }
                            });
                        }
                        
                        // Get current hasil value from textarea after sync
                        var $row = $btn.closest('tr');
                        var $hasilTextarea = $row.find('textarea.result_method_klinik');
                        var currentHasil = $hasilTextarea.length > 0 ? $hasilTextarea.val() : '';
                        
                        setTimeout(function() {
                            $.ajax({
                                url: '{{ url("/elits-permohonan-uji-klinik-2/save-parameter-history") }}/' + parameterId,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    is_sub: isSub ? 1 : 0,
                                    hasil: currentHasil
                                },
                                success: function(response) {
                                    if (response.status) {
                                        var textareaId = $hasilTextarea.attr('id');
                                        
                                        // Kosongkan textarea
                                        $hasilTextarea.val('');
                                        
                                        // Kosongkan dan beri indikator pada dropdown jika ada
                                        var $dropdown = $row.find('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                                        if ($dropdown.length > 0) {
                                            $dropdown.val('').addClass('needs-refill');
                                            if ($dropdown.next('.needs-refill-badge').length === 0) {
                                                $dropdown.after('<span class="needs-refill-badge" style="display: block; margin-top: 5px; padding: 4px 8px; background-color: #ff6b6b; color: white; border-radius: 4px; font-size: 11px; font-weight: 600;">⚠ Harap isi ulang hasil pemeriksaan</span>');
                                            }
                                            setTimeout(function() {
                                                $dropdown.focus();
                                            }, 300);
                                        }
                                        
                                        // Kosongkan dan beri indikator pada TinyMCE editor jika ada
                                        var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                                        if ($editor.length > 0) {
                                            var editorId = $editor.attr('id');
                                            if (editorId && typeof tinymce !== 'undefined') {
                                                try {
                                                    var editor = tinymce.get(editorId);
                                                    if (editor && editor.setContent) {
                                                        editor.setContent('');
                                                    } else {
                                                        $editor.html('').addClass('needs-refill');
                                                    }
                                                } catch(e) {
                                                    $editor.html('').addClass('needs-refill');
                                                }
                                            } else {
                                                $editor.html('').addClass('needs-refill');
                                            }
                                            $editor.addClass('needs-refill')
                                                .attr('data-placeholder', '⚠ Harap isi ulang hasil pemeriksaan');
                                            if ($editor.next('.needs-refill-badge').length === 0) {
                                                $editor.after('<span class="needs-refill-badge" style="display: block; margin-top: 5px; padding: 4px 8px; background-color: #ff6b6b; color: white; border-radius: 4px; font-size: 11px; font-weight: 600;">⚠ Harap isi ulang hasil pemeriksaan</span>');
                                            }
                                            setTimeout(function() {
                                                $editor.focus();
                                            }, 300);
                                        }
                                        
                                        // Update result display menjadi kosong
                                        var index = textareaId.match(/[0-9]+/);
                                        if (index && index[0]) {
                                            var displayId = 'result_display_' + (isSub ? 'sub_' : 'param_') + index[0];
                                            var $display = $('#' + displayId);
                                            if ($display.length > 0) {
                                                $display.html('<span class="text-muted">-</span>').addClass('empty');
                                            }
                                            
                                            var badgeId = 'badge_' + index[0];
                                            $('#' + badgeId).html('');
                                        }
                                        
                                        // Tampilkan pesan sukses tanpa reload
                                        swal('Berhasil!', response.pesan, 'success');
                                    } else {
                                        swal('Error!', response.pesan, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat menyimpan history';
                                    swal('Error!', errorMsg, 'error');
                                }
                            });
                        }, 100);
                    }
                });
            
        });

        // ============================================================
        // MODAL REVIEW HASIL - Verification (Font Size, Line Height, Kop)
        // ============================================================
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings-script')
        (function() {
            var saveFontsizeUrl = '{{ route('elits-permohonan-uji-klinik-2.save-fontsize-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}';
            var previewUrl     = '{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}?mode=preview';
            var csrfToken      = '{{ csrf_token() }}';

            var $slider        = $('#verif-fontsize-slider');
            var $input         = $('#verif-fontsize-input');
            var $preview       = $('#verif-fontsize-preview-sample');
            var $lhSlider      = $('#verif-lineheight-slider');
            var $lhInput       = $('#verif-lineheight-input');
            var $lhPreview     = $('#verif-lineheight-preview-sample');
            var $btnBuka       = $('#verif-btn-buka-review');
            var $loadingIcon   = $('#verif-review-loading-icon');
            var $saveIcon      = $('#verif-review-save-icon');
            var $toggleKop     = $('#verif-toggle-kop');
            var $kopLabel      = $('#verif-kop-label-text');

            var originalFontsize   = parseFloat($slider.val()) || 12;
            var currentFontsize    = originalFontsize;
            var originalLineHeight = parseFloat($lhSlider.val()) || 1;
            var currentLineHeight  = originalLineHeight;
            var originalShowKop    = $toggleKop.is(':checked') ? 1 : 0;
            var currentShowKop     = originalShowKop;

            var marginSettings = initReviewHasilMarginSettings('verif-', function() {
                $btnBuka.prop('disabled', false);
            });

            function updateFontsizeUI(val) {
                val = Math.min(20, Math.max(6, parseFloat(val) || 12));
                val = Math.round(val * 2) / 2;
                $slider.val(val); $input.val(val);
                $preview.css('font-size', val + 'pt');
                $btnBuka.prop('disabled', false);
                currentFontsize = val;
            }

            function updateLineHeightUI(val) {
                val = Math.min(3.0, Math.max(0.5, parseFloat(val) || 1));
                val = Math.round(val * 10) / 10;
                $lhSlider.val(val); $lhInput.val(val);
                $lhPreview.css('line-height', val);
                $btnBuka.prop('disabled', false);
                currentLineHeight = val;
            }

            function updateKopUI(checked) {
                currentShowKop = checked ? 1 : 0;
                $kopLabel.text(checked ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)');
                $btnBuka.prop('disabled', false);
            }

            // Init
            updateFontsizeUI(originalFontsize);
            updateLineHeightUI(originalLineHeight);
            updateKopUI($toggleKop.is(':checked'));

            $slider.on('input change', function() { updateFontsizeUI($(this).val()); });
            $input.on('input change',  function() { updateFontsizeUI($(this).val()); });
            $('#verif-fontsize-minus').on('click', function() { updateFontsizeUI(currentFontsize - 0.5); });
            $('#verif-fontsize-plus').on('click',  function() { updateFontsizeUI(currentFontsize + 0.5); });

            $lhSlider.on('input change', function() { updateLineHeightUI($(this).val()); });
            $lhInput.on('input change',  function() { updateLineHeightUI($(this).val()); });
            $('#verif-lineheight-minus').on('click', function() { updateLineHeightUI(currentLineHeight - 0.1); });
            $('#verif-lineheight-plus').on('click',  function() { updateLineHeightUI(currentLineHeight + 0.1); });

            $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });

            function syncTinyMceField(fieldId) {
                if (typeof tinymce === 'undefined') return;
                try {
                    var ed = tinymce.get(fieldId);
                    if (ed && !ed.removed) {
                        ed.save();
                        $('#' + fieldId).val(ed.getContent());
                    }
                } catch (e) { /* ignore */ }
            }

            function syncEditorsBeforePreviewSave() {
                $('select.inline-hasil-input').each(function() {
                    var textareaId = $(this).data('textarea-id');
                    var selectedValue = $(this).val() || '';
                    if (textareaId && selectedValue) {
                        $('#' + textareaId).val(selectedValue);
                    }
                });

                syncTinyMceField('catatan_hasil');
                syncTinyMceField('kesimpulan_hasil');

                if (typeof tinymce !== 'undefined') {
                    $('.inline-hasil-editor, .inline-keterangan-editor').each(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        var textareaId = $editor.data('textarea-id');
                        if (!editorId) return;
                        try {
                            var ed = tinymce.get(editorId);
                            if (ed && !ed.removed) {
                                ed.save();
                                if (textareaId) {
                                    var $ta = $('#' + textareaId);
                                    if ($ta.length) $ta.val(ed.getContent());
                                }
                            }
                        } catch (e) { /* ignore */ }
                    });
                }

                if (typeof window.syncMetodeInlineEditorsToTextareas === 'function') {
                    window.syncMetodeInlineEditorsToTextareas();
                }
            }

            function collectTempHasil() {
                var tempHasil = {};
                var tempSubHasil = {};

                $('textarea[name^="hasil_permohonan_uji_parameter_klinik"]').each(function() {
                    var $el = $(this);
                    var nameAttr = $el.attr('name') || '';
                    var match = nameAttr.match(/\[([^\]]+)\]$/);
                    if (!match) return;
                    var paramId = match[1];
                    var value = $el.val() || '';
                    if (!value || value.trim() === '' || value === '-') {
                        var textareaId = $el.attr('id') || '';
                        var $row = $el.closest('tr');
                        var $dropdown = $row.find('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                        if ($dropdown.length > 0) value = $dropdown.val() || '';
                    }
                    if (value && value.trim() !== '') tempHasil[paramId] = value;
                });

                $('textarea[name^="hasil_permohonan_uji_sub_parameter_klinik"]').each(function() {
                    var $el = $(this);
                    var nameAttr = $el.attr('name') || '';
                    var match = nameAttr.match(/\[([^\]]+)\]\[([^\]]+)\]$/);
                    if (!match) return;
                    var subId = match[2];
                    var value = $el.val() || '';
                    if (value && value.trim() !== '') tempSubHasil[subId] = value;
                });

                return { tempHasil: tempHasil, tempSubHasil: tempSubHasil };
            }

            function openPreviewVerif(modeSelesai) {
                var url = previewUrl + '&t=' + Date.now();
                $('#verif-preview-hasil-iframe').attr('src', url);
                $('#modalPreviewHasilVerif').data('mode-selesai', modeSelesai);
                if (modeSelesai) {
                    $('#verif-btn-preview-lanjut-selesai').removeClass('d-none');
                } else {
                    $('#verif-btn-preview-lanjut-selesai').addClass('d-none');
                }
                $('#modalPreviewHasilVerif').modal('show');
            }

            function saveSettingsThen(callback) {
                syncEditorsBeforePreviewSave();
                var collected = collectTempHasil();
                var tempMethod = (typeof window.collectTempMethod === 'function') ? window.collectTempMethod() : {};
                var marginValues = marginSettings.getValues();

                return $.ajax({
                    url: saveFontsizeUrl,
                    method: 'POST',
                    data: {
                        _token          : csrfToken,
                        fontsize        : currentFontsize,
                        line_height     : currentLineHeight,
                        padding         : marginValues.padding,
                        padding_top     : marginValues.padding_top,
                        padding_bottom  : marginValues.padding_bottom,
                        margin_left     : marginValues.margin_left,
                        margin_right    : marginValues.margin_right,
                        lebar_kolom_pemeriksaan: marginValues.lebar_kolom_pemeriksaan,
                        lebar_kolom_hasil: marginValues.lebar_kolom_hasil,
                        lebar_kolom_satuan: marginValues.lebar_kolom_satuan,
                        lebar_kolom_metode: marginValues.lebar_kolom_metode,
                        lebar_kolom_nilai_normal: marginValues.lebar_kolom_nilai_normal,
                        show_kop        : currentShowKop,
                        temp_hasil      : JSON.stringify(collected.tempHasil),
                        temp_sub_hasil  : JSON.stringify(collected.tempSubHasil),
                        temp_method     : JSON.stringify(tempMethod),
                        catatan_hasil   : $('#catatan_hasil').val() || '',
                        kesimpulan_hasil: $('#kesimpulan_hasil').val() || ''
                    },
                    success: function(response) {
                        if (response.status) {
                            originalFontsize   = currentFontsize;
                            originalLineHeight = currentLineHeight;
                            marginSettings.commitOriginal();
                            originalShowKop    = currentShowKop;
                            if (typeof callback === 'function') callback();
                        } else {
                            swal('Gagal', response.pesan || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function() {
                        swal('Gagal', 'Terjadi kesalahan saat menyimpan pengaturan.', 'error');
                    }
                });
            }

            function triggerDirectPreviewVerif(modeSelesai) {
                var $btnReview = $('.btn-review-hasil-verif');
                var $btnSelesai = $('.btn-selesai-verif');
                $btnReview.prop('disabled', true);
                $btnSelesai.prop('disabled', true);

                saveSettingsThen(function() {
                    openPreviewVerif(modeSelesai);
                }).always(function() {
                    $btnReview.prop('disabled', false);
                    $btnSelesai.prop('disabled', false);
                });
            }

            $('#modalReviewHasilVerif').on('show.bs.modal', function() {
                var modeSelesai = $(this).data('mode-selesai') || false;
                $(this).find('.modal-title').html(
                    modeSelesai
                        ? '<i class="fa fa-check-circle mr-2"></i>Pengaturan Hasil — Selesai'
                        : '<i class="fa fa-eye mr-2"></i>Review Hasil Pemeriksaan'
                );
                $btnBuka.find('span.verif-btn-label-text').text('Terapkan');
                updateFontsizeUI(originalFontsize);
                updateLineHeightUI(originalLineHeight);
                marginSettings.resetToOriginal();
                $toggleKop.prop('checked', originalShowKop === 1);
                updateKopUI(originalShowKop === 1);
                $btnBuka.prop('disabled', false);
            });

            $('#modalReviewHasilVerif').on('hidden.bs.modal', function() {
                var reopen = $(this).data('reopen-preview') || false;
                var modeSelesai = $(this).data('mode-selesai') || false;
                $(this).data('mode-selesai', false);
                $(this).data('reopen-preview', false);
                if (reopen) {
                    openPreviewVerif(modeSelesai);
                }
            });

            $btnBuka.on('click', function() {
                $btnBuka.prop('disabled', true);
                $loadingIcon.removeClass('d-none');
                $saveIcon.addClass('d-none');

                saveSettingsThen(function() {
                    $('#modalReviewHasilVerif').modal('hide');
                    openPreviewVerif($('#modalReviewHasilVerif').data('mode-selesai') || false);
                }).always(function() {
                    $btnBuka.prop('disabled', false);
                    $loadingIcon.addClass('d-none');
                    $saveIcon.removeClass('d-none');
                });
            });

            $('.btn-review-hasil-verif').on('click', function() {
                triggerDirectPreviewVerif(false);
            });

            $('#verif-btn-pengaturan-preview').on('click', function() {
                var modeSelesai = $('#modalPreviewHasilVerif').data('mode-selesai') || false;
                $('#modalReviewHasilVerif').data('reopen-preview', true);
                $('#modalReviewHasilVerif').data('mode-selesai', modeSelesai);
                $('#modalPreviewHasilVerif').one('hidden.bs.modal', function() {
                    $('#modalReviewHasilVerif').modal('show');
                });
                $('#modalPreviewHasilVerif').modal('hide');
            });
        })();

    </script>
@endsection
