@extends('masterweb::template.admin.layout')

@section('title')
    Baca Hasil
@endsection

@section('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <style>
        /* Sembunyikan spin-button bawaan browser pada input pengaturan hasil
           agar angka tidak tertutup panah naik/turun (sudah ada tombol +/- custom) */
        #fontsize-input::-webkit-inner-spin-button,
        #fontsize-input::-webkit-outer-spin-button,
        #lineheight-input::-webkit-inner-spin-button,
        #lineheight-input::-webkit-outer-spin-button,
        #padding-input::-webkit-inner-spin-button,
        #padding-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        #fontsize-input,
        #lineheight-input,
        #padding-input {
            -moz-appearance: textfield;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <style>
        [data-tip] {
            position: relative;

        }

        [data-tip]:before {
            content: '';
            /* hides the tooltip when not hovered */
            display: none;
            content: '';
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #1a1a1a;
            position: absolute;
            top: 30px;
            left: 35px;
            z-index: 8;
            font-size: 0;
            line-height: 0;
            width: 0;
            height: 0;
            white-space: pre-line;
        }

        [data-tip]:after {
            display: none;
            content: attr(data-tip);
            position: absolute;
            top: 35px;
            left: 0px;
            padding: 5px 8px;
            background: #1a1a1a;
            color: #fff;
            z-index: 9;
            font-size: 0.75em;
            height: 180px;
            line-height: 18px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            white-space: nowrap;
            word-wrap: normal;
            white-space: pre-line;
        }

        [data-tip]:hover:before,
        [data-tip]:hover:after {
            display: block;
        }

        @media only screen and (max-width: 600px) {
            [data-tip]:after {
                height: 280px;
            }
        }

        @media only screen and (min-width: 601px) {
            [data-tip]:after {
                height: 180px;
            }
        }

        /* Custom Checkbox Styling for Status Relay */
        .custom-control.custom-checkbox {
            padding-left: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #28a745;
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .custom-control.custom-checkbox .custom-control-input:not(:checked)~.custom-control-label::before {
            background-color: #dc3545;
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
        }

        .custom-control.custom-checkbox .custom-control-label {
            cursor: pointer;
            user-select: none;
        }

        .custom-control.custom-checkbox .custom-control-label::before,
        .custom-control.custom-checkbox .custom-control-label::after {
            width: 1.5rem;
            height: 1.5rem;
            top: 0;
            left: 0;
        }

        .custom-control.custom-checkbox .custom-control-label::before {
            border-width: 2px;
            transition: all 0.3s ease;
        }

        .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
            font-weight: bold;
        }

        /* Hover effect */
        .custom-control.custom-checkbox:hover .custom-control-label::before {
            transform: scale(1.1);
        }

        /* Status indicator when checked */
        .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::after {
            animation: checkboxPop 0.3s ease;
        }

        @keyframes checkboxPop {
            0% {
                transform: scale(0.8);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Math Toolbar Styling */
        .math-toolbar {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .math-symbol-btn {
            font-size: 14px;
            font-weight: bold;
            min-width: 35px;
            height: 32px;
            padding: 4px 8px;
            transition: all 0.2s ease;
        }

        .math-symbol-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .math-symbol-btn:active {
            transform: translateY(0);
        }

        .math-symbol-btn .fa-superscript {
            font-size: 10px;
        }

        /* Textarea focus with toolbar */
        .result_method:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        /* Inline editing styles - same as analis page */
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
            width: 100%;
            box-sizing: border-box;
            position: relative;
        }

        .inline-keterangan-editor:hover {
            border-color: #b8c1ec;
        }

        .inline-keterangan-editor:focus,
        .inline-keterangan-editor.mce-edit-focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }

        /* Placeholder hanya saat kosong DAN tidak fokus (hindari menimpa teks ketikan) */
        .inline-keterangan-editor.empty:not(:focus):not(.mce-edit-focus):before {
            content: attr(data-placeholder);
            color: #999;
            pointer-events: none;
            display: block;
        }

        .inline-metode-editor {
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

        #nama_jenis_makanan,
        textarea#nama_jenis_makanan {
            width: 100%;
        }

        textarea#nama_jenis_makanan + .tox-tinymce {
            width: 100% !important;
        }

        .result-badge-inline {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0; /* Allow badge to shrink if needed */
        }

        .result-badge-inline .badge {
            font-size: 13px;
            padding: 6px 12px;
            display: inline-block;
        }

        /* Highlight row on focus */
        tr:has(.inline-hasil-input:focus),
        tr:has(.inline-hasil-editor:focus),
        tr:has(.inline-keterangan-editor:focus),
        tr:has(.inline-metode-editor:focus),
        tr:has(.inline-metode-editor.mce-edit-focus) {
            background-color: #f8f9ff;
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

        /* Hide edit buttons when inline editing is active */
        .open-editor-modal {
            display: none !important;
        }

        /* Baku Mutu Modal Styles */
        .offset-baku-mutu-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Superscript/Subscript styles for badges */
        .badge sup,
        .badge-success sup,
        .badge-danger sup {
            vertical-align: super;
            font-size: 0.75em;
            line-height: 0;
            position: relative;
            top: -0.4em;
        }

        .badge sub,
        .badge-success sub,
        .badge-danger sub {
            vertical-align: sub;
            font-size: 0.75em;
            line-height: 0;
            position: relative;
            bottom: -0.25em;
        }

        .offset-option {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .offset-option:hover {
            border-color: #0b3a5c;
            background-color: #f8f9ff;
        }

        .offset-option input[type="radio"] {
            margin-right: 10px;
        }

        .offset-option input[type="radio"]:checked + label {
            font-weight: bold;
        }

        .offset-option label {
            cursor: pointer;
            margin: 0;
        }

        /* Perlebar editor TinyMCE untuk lokasi pengambilan */
        #lokasi_pengambilan, #lokasi_pengambilan_kimia {
            width: 100% !important;
            max-width: 100% !important;
        }

        #lokasi_pengambilan + .tox-tinymce,
        #lokasi_pengambilan_kimia + .tox-tinymce,
        .tox-tinymce[aria-label*="lokasi_pengambilan"] {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Perlebar container form-group untuk lokasi pengambilan */
        textarea#lokasi_pengambilan,
        textarea#lokasi_pengambilan_kimia {
            width: 100% !important;
        }

        textarea#lokasi_pengambilan + .tox,
        textarea#lokasi_pengambilan_kimia + .tox {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Result display styling */
        .result-display {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            min-height: 30px;
            word-wrap: break-word;
        }

        /* Keterangan display styling */
        .keterangan-display {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            min-height: 40px;
            word-wrap: break-word;
            white-space: normal;
        }

        .keterangan-display.empty {
            color: #999;
            font-style: italic;
        }

        #table-parameter tbody td .keterangan-display {
            white-space: normal;
            word-break: break-word;
        }

        .result-display.empty {
            color: #999;
            font-style: italic;
        }

        /* Action buttons container */
        .hasil-action-buttons {
            display: flex;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        /* Badge container in horizontal layout */
        .result-badge-inline {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0; /* Allow badge to shrink if needed */
        }

        /* Container for badge and buttons row */
        .badge-buttons-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            width: 100%;
            flex-wrap: wrap;
        }

        /* Select2 dalam modal styling */
        /* Prevent modal backdrop from interfering with Select2 */
        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }

        /* Modal create Satuan / Dokumen Acuan / Jenis Sampel di atas Edit Baku Mutu */
        #modalCreateLibrary.modal,
        #modalCreateUnit.modal,
        #modalCreateSampleType.modal {
            z-index: 1065 !important;
        }

        .modal-backdrop.modal-stack {
            z-index: 1060 !important;
        }

        /* Fix Select2 z-index in modal - must be higher than modal */
        #modalTambahBakuMutu .select2-container,
        #modalEditBakuMutu .select2-container {
            z-index: 10060 !important;
        }

        #modalTambahBakuMutu .select2-container--open,
        #modalEditBakuMutu .select2-container--open {
            z-index: 10061 !important;
        }

        #modalTambahBakuMutu .select2-dropdown,
        #modalEditBakuMutu .select2-dropdown {
            z-index: 10062 !important;
            border: 1px solid #ced4da !important;
        }

        /*
         * Modal Tambah Baku Mutu — scroll body yang benar.
         * Form membungkus body+footer, jadi form harus jadi flex column agar body bisa di-scroll.
         * Jangan pakai overflow:visible di .modal-content (itu memutus scroll).
         */
        #modalTambahBakuMutu .modal-dialog {
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
            display: flex;
            overflow: hidden;
        }

        #modalTambahBakuMutu .modal-content {
            max-height: calc(100vh - 2rem);
            width: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden !important;
        }

        #modalTambahBakuMutu form#formTambahBakuMutu {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        #modalTambahBakuMutu .modal-header,
        #modalTambahBakuMutu .modal-footer {
            flex-shrink: 0;
        }

        #modalTambahBakuMutu .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto !important;
            overflow-x: hidden;
            max-height: none;
            position: relative;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        #modalTambahBakuMutu .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #modalTambahBakuMutu .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #modalTambahBakuMutu .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        #modalTambahBakuMutu .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        #modalTambahBakuMutu .select2-container,
        #modalEditBakuMutu .select2-container {
            width: 100% !important;
        }

        /* Select2 dropdown di body (bukan di dalam modal) agar tidak terpotong overflow:hidden */
        body > .select2-container--open {
            z-index: 10070 !important;
        }

        body > .select2-container--open .select2-dropdown {
            z-index: 10071 !important;
        }

        #modalEditBakuMutu .modal-dialog.modal-body-scrollable .modal-content {
            overflow: visible !important;
        }

        #modalEditBakuMutu .tab-content {
            overflow: visible !important;
        }

        /* ===== Sticky Sample Info Section ===== */
        .sample-data-sticky-wrapper {
            position: relative;
            z-index: 10;
            margin-bottom: 16px;
        }

        .sample-data-sticky-wrapper.sticky {
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

        .sample-data-sticky-wrapper.sticky.compact {
            padding: 0;
        }

        .sample-data-compact {
            display: none;
            padding: 8px 15px;
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sample-data-sticky-wrapper.sticky.compact .sample-data-compact {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sample-data-sticky-wrapper.sticky.compact .sample-data-full {
            display: none;
        }

        .sample-data-compact-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            flex: 1;
        }

        .sample-data-compact-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .sample-data-compact-item i {
            font-size: 13px;
            opacity: 0.9;
        }

        .sample-data-compact-item strong {
            font-weight: 600;
            margin-right: 3px;
        }

        .sample-data-compact-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sample-data-compact-actions .btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s;
        }

        .sample-data-compact-actions .btn i {
            color: white !important;
        }

        .sample-data-compact-actions .btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .sample-data-sticky-wrapper.sticky.expanded .sample-data-compact {
            display: none;
        }

        .sample-data-sticky-wrapper.sticky.expanded .sample-data-full {
            display: block;
            padding: 12px 20px;
            max-height: 300px;
            overflow-y: auto;
        }

        /* Spacer to prevent content jump when sticky */
        .sample-data-spacer {
            display: none;
            height: 0;
            transition: height 0.3s ease;
        }

        .sample-data-sticky-wrapper.sticky ~ .sample-data-spacer {
            display: block;
        }

        .sample-data-sticky-wrapper.sticky.compact ~ .sample-data-spacer {
            height: 48px;
        }

        .sample-data-sticky-wrapper.sticky.expanded ~ .sample-data-spacer {
            height: 300px;
        }

        @media (max-width: 768px) {
            .sample-data-compact-content {
                gap: 10px;
                font-size: 12px;
            }
            .sample-data-compact-item {
                font-size: 11px;
            }
            .sample-data-compact-item strong {
                display: none;
            }
            .sample-data-compact-actions .btn {
                padding: 3px 8px;
                font-size: 11px;
            }
        }
        /* ===== End Sticky Sample Info ===== */
        .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            line-height: calc(1.5em + 0.75rem);
        }

        /* Fix for Select2 search input focus */
        .select2-search--dropdown {
            position: sticky;
            top: 0;
            z-index: 1;
            background: white;
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


    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                                        Beranda </a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-permohonan-uji') }}">
                                        Permohonan Uji</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">
                                        Daftar Pengujian</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ url('/elits-samples/verification-2', [Request::segment(2), Request::segment(3)]) }}">
                                        Analys</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page"><span>Baca Hasil</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @php
echo Request::segment(3);
@endphp --}}

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <H4 class="mb-0"><i class="fa fa-flask mr-2"></i>Baca Hasil Pengujian</H4>
        </div>
        <div class="card-body">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- utama -->

                        <div class="col-md-12">
                            <!-- Sticky Sample Info Wrapper -->
                            <div class="sample-data-sticky-wrapper" id="sampleDataStickyWrapper">

                                <!-- Compact View (shown when sticky) -->
                                <div class="sample-data-compact">
                                    <div class="sample-data-compact-content">
                                        <div class="sample-data-compact-item">
                                            <i class="fa fa-user"></i>
                                            <strong>Pelanggan:</strong>
                                            <span>{{ $sample->name_pelanggan ?? ($sample->namaPelangganDisplay() ?? '-') }}</span>
                                        </div>
                                        <div class="sample-data-compact-item">
                                            <i class="fa fa-barcode"></i>
                                            <strong>No. Sampel:</strong>
                                            <span>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
                                        </div>
                                        <div class="sample-data-compact-item">
                                            <i class="fa fa-flask"></i>
                                            <strong>Jenis:</strong>
                                            <span>{{ $sample->name_sample_type }}</span>
                                        </div>
                                        <div class="sample-data-compact-item">
                                            <i class="fa fa-building"></i>
                                            <strong>Lab:</strong>
                                            <span>{{ $sample->nama_laboratorium }}</span>
                                        </div>
                                    </div>
                                    <div class="sample-data-compact-actions">
                                        <button type="button" class="btn btn-sm" id="expandSampleData" title="Perlebar">
                                            <i class="fa fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm" id="minimizeSampleData" title="Minimize" style="display: none;">
                                            <i class="fa fa-compress"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Full View -->
                                <div class="sample-data-full">
                            <!-- Info Sampel Section -->
                            <div class="card border-0 mb-3" style="background-color: #f8f9fa;">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3"><i class="fa fa-info-circle mr-2"></i>Informasi
                                        Sampel</h5>
                                    <table class="table table-borderless table-sm">
                                        <tbody>
                                            <tr>
                                                <td width="20%" class="font-weight-bold text-muted"><i
                                                        class="fa fa-user mr-2"></i>Nama Pelanggan</td>
                                                <td width="5%" class="text-center">:</td>
                                                <td width="25%">
                                                    @php
                                                        $customer = str_replace(
                                                            // Hanya mencari simbol 'Π'
                                                            'π',
                                                            '<span style="font-family: \'DejaVu Sans\', sans-serif;">π</span>', // Ganti dengan <span> yang sesuai
                                                            $sample->name_pelanggan ??
                                                                $sample->namaPelangganDisplay(),
                                                        );
                                                    @endphp
                                                    <strong>{!! $customer !!}</strong>
                                                </td>
                                                <td width="20%" class="font-weight-bold text-muted"><i
                                                        class="fa fa-calendar mr-2"></i>Tanggal Pengambilan</td>
                                                <td width="5%" class="text-center">:</td>
                                                <td width="25%">
                                                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold text-muted"><i
                                                        class="fa fa-barcode mr-2"></i>Nomor Sampel</td>
                                                <td class="text-center">:</td>
                                                <td><strong class="text-primary">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</strong>
                                                </td>
                                                <td class="font-weight-bold text-muted"><i
                                                        class="fa fa-flask mr-2"></i>Jenis Sampel</td>
                                                <td class="text-center">:</td>
                                                <td><strong>{{ $sample->jenisSampelDisplay() }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold text-muted"><i
                                                        class="fa fa-building mr-2"></i>Laboratorium</td>
                                                <td class="text-center">:</td>
                                                <td><strong class="badge badge-info"
                                                        style="font-size: 14px;">{{ $sample->nama_laboratorium }}</strong>
                                                </td>
                                                <td class="font-weight-bold text-muted"><i
                                                        class="fa fa-calendar-check mr-2"></i>Tanggal Pengiriman</td>
                                                <td class="text-center">:</td>
                                                <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                </div><!-- end .sample-data-full -->

                            </div><!-- end .sample-data-sticky-wrapper -->
                            <div class="sample-data-spacer"></div>
                            {{-- Note sample dengan kondisi --}}
                            @if ($sample->note_samples !== null)
                                <div class="alert alert-warning border-left-warning shadow-sm"
                                    style="border-left: 5px solid #ffc107;">
                                    <i class="fa fa-exclamation-triangle mr-2"></i><strong>Catatan:</strong>
                                    {{ $sample->note_samples }}
                                </div>
                            @endif


                            @if ($sample->is_pudam == 1)
                                <div class="card border-0 mb-3" style="background-color: #e3f2fd;">
                                    <div class="card-body">
                                        <h5 class="card-title text-info mb-3"><i class="fa fa-building mr-2"></i>Informasi
                                            PDAM</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><span class="font-weight-bold text-muted">Nama
                                                        Pengirim:</span></p>
                                                <p class="ml-3"><strong>{{ $sample->name_customer_pdam }}</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><span class="font-weight-bold text-muted">Alamat
                                                        Pengirim:</span></p>
                                                <p class="ml-3"><strong>{!! $sample->address_location_pdam !!}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Parameter Section -->
                            <div class="card border-0 mb-3" style="background-color: #fff3e0;">
                                <div class="card-body">
                                    <h5 class="card-title text-warning mb-3"><i class="fa fa-list-ul mr-2"></i>Parameter
                                        {{ $sample->nama_laboratorium }}</h5>
                                    <div class="row">
                                        @foreach ($laboratoriummethods as $index => $laboratoriummethod)
                                            <div class="col-md-3 mb-2">
                                                <span class="badge badge-light border"
                                                    style="font-size: 13px; padding: 8px 12px;">
                                                    <i
                                                        class="fa fa-check-circle text-success mr-1"></i>{{ $laboratoriummethod->params_method }}
                                                </span>
                                            </div>

                                            @if (($index + 1) % 4 == 0)
                                    </div>
                                    <div class="row">
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">


                                <div class="row">
                                    <div class="col-md-12">

                                        <form class="form"
                                            action="{{ route('elits-baca-hasil.store', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
                                            method="POST" id="form-baca-hasil">

                                            @csrf
                                            <input type="hidden" name="_token-select" id="csrf-token"
                                                value="{{ Session::token() }}" />
                                            {{-- Hidden input untuk menyimpan pilihan ruangan (khusus Kualitas Udara) --}}
                                            <input type="hidden" name="selected_ruangan" id="selected_ruangan_hidden" value="{{ old('pilih_ruangan') }}">

                                            {{-- Hidden inputs untuk verifikasi baca hasil --}}
                                            @php
                                                $defaultLineHeightHasil = old('line_height_hasil');
                                                if ($defaultLineHeightHasil === null) {
                                                    $lineHeightDb = $sample->line_height_hasil_baca_hasil ?? null;
                                                    $defaultLineHeightHasil = $lineHeightDb;
                                                }
                                                $defaultLineHeightHasil = ($defaultLineHeightHasil === null || (float) $defaultLineHeightHasil === 1.5) ? 1 : $defaultLineHeightHasil;

                                                $defaultPaddingHasil = old('padding_hasil');
                                                if ($defaultPaddingHasil === null) {
                                                    $paddingDb = $sample->padding_hasil_baca_hasil ?? null;
                                                    $defaultPaddingHasil = $paddingDb;
                                                }
                                                $defaultPaddingHasil = ($defaultPaddingHasil === null || (float) $defaultPaddingHasil === 4.0) ? 1 : $defaultPaddingHasil;
                                            @endphp
                                            <input type="hidden" name="verification_step" id="verification_step_hidden" value="3">
                                            <input type="hidden" name="verification_start_date" id="verification_start_date_hidden">
                                            <input type="hidden" name="verification_stop_date" id="verification_stop_date_hidden">
                                            <input type="hidden" name="verification_nama_petugas" id="verification_nama_petugas_hidden">
                                            <input type="hidden" name="verification_id_laboratorium" id="verification_id_laboratorium_hidden" value="{{ Request::segment(3) }}">
                                            <input type="hidden" name="verification_laboratorium_progress_id" id="verification_laboratorium_progress_id_hidden" value="{{ Request::segment(4) }}">
                                            <input type="hidden" name="fontsize_hasil" id="fontsize_hasil_hidden" value="{{ old('fontsize_hasil', $sample->fontsize_hasil_baca_hasil ?? 12) }}">
                                            <input type="hidden" name="line_height_hasil" id="line_height_hasil_hidden" value="{{ $defaultLineHeightHasil }}">
                                            <input type="hidden" name="padding_hasil" id="padding_hasil_hidden" value="{{ $defaultPaddingHasil }}">
                                            <input type="hidden" name="show_kop_hasil" id="show_kop_hasil_hidden" value="{{ old('show_kop_hasil', $sample->show_kop_hasil_baca_hasil ?? 1) }}">

                                            <!-- Form Verifikasi Baca Hasil - Dipindahkan ke atas -->
                                            <div class="card border-0 mb-4" style="background-color: #e3f2fd;">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0"><i class="fa fa-check-circle mr-2"></i>Verifikasi Baca Hasil</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="start_date_verifikasi_baca_hasil">
                                                                    <strong>Start Date <span class="text-danger">*</span></strong>
                                                                </label>
                                                                <input type="text"
                                                                       class="form-control"
                                                                       id="start_date_verifikasi_baca_hasil"
                                                                       placeholder="dd/mm/yyyy"
                                                                       required>
                                                                <small class="form-text text-muted">
                                                                    Format: dd/mm/yyyy (contoh: 08/01/2026)
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="stop_date_verifikasi_baca_hasil">
                                                                    <strong>Stop Date <span class="text-danger">*</span></strong>
                                                                </label>
                                                                <input type="text"
                                                                       class="form-control"
                                                                       id="stop_date_verifikasi_baca_hasil"
                                                                       placeholder="dd/mm/yyyy"
                                                                       required>
                                                                <small class="form-text text-muted">
                                                                    Format: dd/mm/yyyy (contoh: 08/01/2026)
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="nama_petugas_verifikasi_baca_hasil">
                                                                    <strong>Nama Petugas <span class="text-danger">*</span></strong>
                                                                </label>
                                                                <select id="nama_petugas_verifikasi_baca_hasil"
                                                                        class="form-control"
                                                                        required>
                                                                    <option value="">-- Pilih Nama Petugas --</option>
                                                                    @foreach ($petugas_verifikasi_list as $petugas)
                                                                        <option value="{{ $petugas }}"
                                                                                {{ $default_nama_petugas == $petugas ? 'selected' : '' }}>
                                                                            {{ $petugas }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <small class="form-text text-muted">
                                                                    Default: {{ $default_nama_petugas ?? 'Belum dipilih di Step 3' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Form Input Section -->
                                            <div class="card border-0 mb-4" style="background-color: #f1f8e9;">
                                                <div class="card-body">
                                                    <h5 class="card-title text-success mb-4"><i
                                                            class="fa fa-edit mr-2"></i>Informasi Lokasi dan Pengujian</h5>

                                                    {{-- ### Nama Pengambil ### --}}
                                                    <div class="form-group">
                                                        <label for="nama_pengambil" class="font-weight-bold">
                                                            <i class="fa fa-user-tie mr-2 text-primary"></i>Nama Pengambil:
                                                        </label>
                                                        @php
                                                            // Tentukan default value berdasarkan is_sampling
                                                            $defaultNamaPengambil = '';

                                                            // Jika sudah ada nilai yang tersimpan, gunakan itu
                                                            if (!empty($sample->namaPengambilDisplay())) {
                                                                $defaultNamaPengambil =
                                                                    $sample->namaPengambilDisplay();
                                                            } else {
                                                                // Jika belum ada, tentukan berdasarkan is_sampling
                                                                if ($sample->is_sampling == 1) {
                                                                    // Jika is_sampling = 1, petugas lab
                                                                    $defaultNamaPengambil =
                                                                        'Petugas Laboratorium Kesehatan';
                                                                } else {
                                                                    // Jika is_sampling = 0, petugas + nama pelanggan
                                                                    $customerName = $sample->namaPelangganDisplay('');
                                                                    $defaultNamaPengambil = 'Petugas ' . $customerName;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control shadow-sm"
                                                            id="nama_pengambil" name="nama_pengambil"
                                                            value="{{ old('nama_pengambil', $defaultNamaPengambil) }}"
                                                            placeholder="Masukkan nama pengambil..." required>
                                                    </div>

                                                    <div class="form-group">
                                                        {{-- ### Asal Sampel (TinyMCE) ### --}}
                                                        @if ($lab->kode_laboratorium === 'MBI')
                                                            <label for="lokasi_pengambilan" class="font-weight-bold">
                                                                <i class="fa fa-map-marker-alt mr-2 text-danger"></i>Asal
                                                                Sampel:
                                                            </label>
                                                        @else
                                                            <label for="lokasi_pengambilan" class="font-weight-bold">
                                                                <i class="fa fa-map-marker-alt mr-2 text-danger"></i>Asal
                                                                Contoh Air/ Lokasi Sampel:
                                                            </label>
                                                        @endif

                                                        @php
                                                            // Untuk mikro: gunakan defaultAsalSampel (permohonan uji)
                                                            $asal_sampel_value = old('lokasi_pengambilan');
                                                            if ($lab->kode_laboratorium === 'MBI') {
                                                                if (empty($asal_sampel_value)) {
                                                                    $asal_sampel_value = $defaultAsalSampel ?? '';
                                                                }
                                                            } else {
                                                                // Non-MBI: pertahankan perilaku lama (pakai location_samples)
                                                                if (empty($asal_sampel_value)) {
                                                                    $asal_sampel_value = $defaultAsalSampel ?? '';
                                                                }
                                                            }

                                                        @endphp

                                                        @if ($lab->kode_laboratorium === 'MBI')
                                                            <textarea class="form-control shadow-sm" id="lokasi_pengambilan" name="lokasi_pengambilan" rows="3"
                                                                placeholder="Masukkan asal sampel...">{!! $asal_sampel_value !!}</textarea>
                                                        @else
                                                            <div class="input-group date">
                                                                <textarea class="form-control shadow-sm" id="lokasi_pengambilan_kimia" name="lokasi_pengambilan" rows="3"
                                                                    placeholder="Masukkan lokasi sampel...">{!! $asal_sampel_value !!}</textarea>
                                                            </div>
                                                        @endif
                                                    </div>


                                                    {{-- ### Titik Sampel ###
                                                         Disembunyikan untuk Makanan/Minuman: kolom Titik Sampel LHU
                                                         memakai nilai field Jenis Sampel (nama_jenis_makanan) --}}
                                                    @if (
                                                        !(
                                                            $lab->kode_laboratorium === 'MBI' &&
                                                            isset($sample->name_sample_type) &&
                                                            $sample->name_sample_type === 'Makanan/Minuman/Lainnya'
                                                        )
                                                    )
                                                        <div class="form-group">
                                                            <label for="titik_pengambilan" class="font-weight-bold">
                                                                <i class="fa fa-map-pin mr-2 text-info"></i>Titik Sampel:
                                                            </label>
                                                            @php
                                                                $titik_pengambilan_value =
                                                                    $sample->titik_pengambilan ??
                                                                    old('titik_pengambilan');
                                                                $titik_pengambilan_value =
                                                                    $titik_pengambilan_value ?? '';
                                                            @endphp
                                                            <textarea class="form-control shadow-sm" id="titik_pengambilan" name="titik_pengambilan" rows="2"
                                                                placeholder="Masukkan titik/lokasi pengambilan sampel...">{!! $titik_pengambilan_value !!}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- ### Pilih Ruangan/Lokasi (Khusus untuk Kualitas Udara) ### --}}
                                                    @php
                                                        $isKualitasUdara = isset($sample->name_sample_type) &&
                                                            stripos($sample->name_sample_type, 'udara') !== false;

                                                        // Kumpulkan semua lokasi dari baku mutu yang memiliki lokasi_data
                                                        $allLokasiData = [];
                                                        $selectedRuanganFromResult = null;
                                                        if ($isKualitasUdara && isset($laboratoriummethods)) {
                                                            foreach ($laboratoriummethods as $lm) {
                                                                // Ambil lokasi_selected dari SampleResult jika ada
                                                                if (isset($lm->lokasi_selected) && !empty($lm->lokasi_selected) && !$selectedRuanganFromResult) {
                                                                    $selectedRuanganFromResult = $lm->lokasi_selected;
                                                                }
                                                                if (isset($lm->lokasi_data) && !empty($lm->lokasi_data)) {
                                                                    $lokasiData = json_decode($lm->lokasi_data, true);
                                                                    if (is_array($lokasiData)) {
                                                                        foreach ($lokasiData as $lokasi) {
                                                                            if (!empty($lokasi['nama'])) {
                                                                                $allLokasiData[$lokasi['nama']] = $lokasi['nama'];
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $uniqueLokasi = array_unique($allLokasiData);
                                                    @endphp
                                                    @if ($isKualitasUdara && !empty($uniqueLokasi))
                                                        <div class="form-group">
                                                            <label for="pilih_ruangan" class="font-weight-bold">
                                                                <i class="fa fa-building mr-2 text-warning"></i>Pilih Ruangan / Lokasi:
                                                                <small class="text-muted">(Menentukan baku mutu yang digunakan)</small>
                                                            </label>
                                                            <select id="pilih_ruangan" name="pilih_ruangan" class="form-control shadow-sm">
                                                                <option value="">-- Pilih Ruangan / Lokasi --</option>
                                                                @foreach ($uniqueLokasi as $lokasiNama)
                                                                    <option value="{{ $lokasiNama }}" {{ old('pilih_ruangan', $selectedRuanganFromResult) == $lokasiNama ? 'selected' : '' }}>
                                                                        {{ $lokasiNama }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="form-text text-muted">
                                                                <i class="fa fa-info-circle mr-1"></i>
                                                                Pilih ruangan/lokasi untuk menentukan baku mutu yang akan digunakan untuk setiap parameter.
                                                            </small>
                                                        </div>
                                                    @endif


                                                    {{-- ### Jenis Sarana ### --}}
                                                    @php
                                                        $isKualitasUdaraForJenisSarana = isset($sample->name_sample_type) &&
                                                            stripos($sample->name_sample_type, 'udara') !== false;
                                                    @endphp
                                                    @if (!$isKualitasUdaraForJenisSarana)
                                                        @if (
                                                            $lab->kode_laboratorium == 'MBI' &&
                                                                isset($sample->name_sample_type) &&
                                                                $sample->name_sample_type === 'Makanan/Minuman/Lainnya')
                                                            {{-- Disembunyikan di UI, tetapi tetap dikirim sebagai hidden input.
                                                                 Nilainya mengikuti pilihan Jenis Makanan (autoJenisSarana dari controller). --}}
                                                            <input type="hidden" name="jenis_sarana" id="jenis_sarana"
                                                                value="{{ old('jenis_sarana', $autoJenisSarana ?? ($sample->jenis_sarana_names ?? '')) }}">
                                                        @elseif ($lab->kode_laboratorium == 'MBI')
                                                            <div class="form-group">
                                                                <label for="input_jenis_sarana" class="font-weight-bold">
                                                                    <i class="fa fa-building mr-2 text-primary"></i>Jenis
                                                                    Sarana:
                                                                </label>
                                                                @isset($jenis_sarana_options)
                                                                    <select id="input_jenis_sarana" name="jenis_sarana"
                                                                        class="js-customer-basic-multiple js-states form-control shadow-sm"
                                                                        style="width: 100%">
                                                                        <option value="" @selected(empty(old('jenis_sarana')))> Pilih
                                                                            Jenis
                                                                            Sarana </option>

                                                                        @foreach ($jenis_sarana_options as $jenis_sarana)
                                                                            <option value="{{ $jenis_sarana['value'] }}"
                                                                                {{ old('jenis_sarana') ?? $sample->jenis_sarana_names === $jenis_sarana['value'] ? 'selected' : '' }}>
                                                                                {{ $jenis_sarana['value'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="text" name="jenis_sarana"
                                                                        id="input_jenis_sarana_lainnya"
                                                                        class="form-control shadow-sm"
                                                                        value="{{ old('jenis_sarana', $sample->jenis_sarana_names) }}"
                                                                        style="width: 100%; margin-top: 1em"
                                                                        placeholder="Masukkan jenis sarana..." disabled hidden>
                                                                @else


                                                                    <input type="text" class="form-control shadow-sm"
                                                                        name="jenis_sarana" id="jenis_sarana"
                                                                        placeholder="Jenis Sarana"
                                                                        value="{{ old('jenis_sarana', $sample->jenis_sarana_names!='' && $sample->jenis_sarana_names!=null ? $sample->jenis_sarana_names : ($sample->name_sample_type ?? '')) }}">
                                                                @endisset
                                                            </div>
                                                        @endif
                                                    @endif

                                                    {{-- ### Pilih Jenis Makanan (MBI & KIM) - ditempatkan setelah Jenis Sarana ### --}}
                                                    @php
                                                        $showJenisMakananPicker = false;
                                                        $labCode = $lab->kode_laboratorium ?? '';
                                                        $stNameCheck = $sample->name_sample_type ?? '';
                                                        $isMMLType = str_contains($stNameCheck, 'Makanan')
                                                            || str_contains($stNameCheck, 'Minuman')
                                                            || str_contains($stNameCheck, 'Lainnya');

                                                        // Untuk MBI: tampilkan jika ada jenis makanan yang punya baku mutu
                                                        // untuk parameter yang dipilih
                                                        if ($labCode === 'MBI' && $isMMLType &&
                                                            isset($jenisMakananAll) &&
                                                            $jenisMakananAll->count() > 0) {
                                                            $showJenisMakananPicker = true;
                                                        }

                                                        // Untuk KIM: tampilkan jika ada minimal satu jenis makanan yang punya baku mutu
                                                        // untuk parameter yang dipilih, atau ada baku mutu tanpa jenis makanan
                                                        if ($labCode === 'KIM' && $isMMLType && isset($jenisMakananAll)) {
                                                            $hasWithoutJenisMakanan = isset($hasBakuMutuWithoutJenisMakanan) && $hasBakuMutuWithoutJenisMakanan;

                                                            if ($jenisMakananAll->count() > 0 || $hasWithoutJenisMakanan) {
                                                                $showJenisMakananPicker = true;
                                                            }
                                                        }

                                                        $selectedJenisMakananId = $jenis_makanan_id;
                                                        $kimGenericJenisOption = $labCode === 'KIM' && !empty($hasBakuMutuWithoutJenisMakanan) && $hasBakuMutuWithoutJenisMakanan;
                                                    @endphp

                                                    @if ($showJenisMakananPicker)
                                                        <div class="form-group">
                                                            <label class="font-weight-bold" for="jenis_makanan_picker">
                                                                <i class="fa fa-utensils mr-2"></i>Pilih Jenis Makanan
                                                                @if ($labCode === 'KIM')
                                                                    (KIM - Opsional)
                                                                @else
                                                                    (MBI)
                                                                @endif
                                                            </label>
                                                            <select id="jenis_makanan_picker" name="jenis_makanan_id"
                                                                class="form-control" style="width:100%">
                                                                @if ($kimGenericJenisOption)
                                                                    <option value="__none__" {{ $selectedJenisMakananId === null || $selectedJenisMakananId === '' || $selectedJenisMakananId === '__none__' ? 'selected' : '' }}>
                                                                        Tidak berdasarkan jenis makanan
                                                                    </option>
                                                                @else
                                                                    <option value="" {{ $selectedJenisMakananId === null || $selectedJenisMakananId === '' ? 'selected' : '' }}>
                                                                        — Pilih jenis makanan —
                                                                    </option>
                                                                @endif
                                                                @foreach ($jenisMakananAll as $jm)
                                                                    <option value="{{ $jm->id_jenis_makanan }}"
                                                                        {{ $selectedJenisMakananId == $jm->id_jenis_makanan ? 'selected' : '' }}>
                                                                        {{ $jm->name_jenis_makanan }}</option>
                                                                @endforeach
                                                                <option value="__new__" style="color:#28a745;font-style:italic;">+ Jenis Makanan Lain...</option>
                                                            </select>
                                                            <small class="text-muted">Memilih jenis makanan akan otomatis
                                                                mengisi baku mutu sesuai database.
                                                                Jenis makanan baru membutuhkan baku mutu tersendiri per parameter.</small>
                                                            @if ($kimGenericJenisOption)
                                                                <small class="text-muted d-block mt-1">Pilih <em>Tidak berdasarkan jenis makanan</em> jika baku mutu generik (tanpa jenis makanan tertentu).</small>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    {{-- Modal Tambah Jenis Makanan Baru --}}
                                                    <div class="modal fade" id="modal-tambah-jenis-makanan" tabindex="-1" role="dialog" aria-labelledby="modalTambahJenisMakananLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-body-scrollable" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-success text-white">
                                                                    <h5 class="modal-title" id="modalTambahJenisMakananLabel">
                                                                        <i class="fa fa-utensils mr-2"></i>Pilih / Tambah Jenis Makanan
                                                                    </h5>
                                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    {{-- Searchable dropdown jenis makanan yang belum ada di dropdown utama --}}
                                                                    <div class="form-group" id="existing-jm-group">
                                                                        <label class="font-weight-bold">
                                                                            <i class="fa fa-list mr-1"></i>Pilih dari Daftar Lainnya
                                                                        </label>
                                                                        <div class="sdd-wrap" id="sdd-existing-jm" style="position:relative;">
                                                                            <div class="sdd-display form-control d-flex align-items-center justify-content-between"
                                                                                style="cursor:pointer; user-select:none; background:#fff;"
                                                                                id="sdd-existing-jm-display">
                                                                                <span class="sdd-label text-muted" id="sdd-existing-jm-label">— Pilih jenis makanan —</span>
                                                                                <i class="fa fa-chevron-down text-muted" style="font-size:12px;"></i>
                                                                            </div>
                                                                            <input type="hidden" id="existing-jenis-makanan-select" value="">
                                                                            <div class="sdd-panel shadow border rounded bg-white" id="sdd-existing-jm-panel"
                                                                                style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999; max-height:220px; overflow:hidden; flex-direction:column;">
                                                                                <div class="p-2 border-bottom">
                                                                                    <input type="text" class="form-control form-control-sm sdd-search"
                                                                                        id="sdd-existing-jm-search"
                                                                                        placeholder="Cari jenis makanan..."
                                                                                        autocomplete="off">
                                                                                </div>
                                                                                <div class="sdd-list" id="sdd-existing-jm-list"
                                                                                    style="overflow-y:auto; max-height:160px;">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-muted">Jenis makanan yang sudah ada di database tapi belum muncul di daftar utama.</small>
                                                                    </div>

                                                                    {{-- Tombol untuk buka input nama baru --}}
                                                                    <div class="mt-2 mb-1">
                                                                        <button type="button" id="btn-show-new-jm-input" class="btn btn-sm btn-outline-success">
                                                                            <i class="fa fa-plus mr-1"></i>Nama Jenis Makanan Baru
                                                                        </button>
                                                                    </div>

                                                                    {{-- Input nama baru (tersembunyi dulu) --}}
                                                                    <div class="form-group mb-0 mt-2" id="new-jm-input-group" style="display:none;">
                                                                        <label class="font-weight-bold" for="new-jenis-makanan-name">
                                                                            <i class="fa fa-pencil-alt mr-1"></i>Nama Jenis Makanan Baru
                                                                        </label>
                                                                        <input type="text" id="new-jenis-makanan-name" class="form-control"
                                                                            placeholder="Contoh: Ayam Goreng, Nasi Putih, dll"
                                                                            maxlength="100">
                                                                        <small class="text-muted">Jenis makanan baru akan disimpan ke database.</small>
                                                                    </div>
                                                                    <div id="new-jenis-makanan-error" class="text-danger small mt-1" style="display:none;"></div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                        <i class="fa fa-times mr-1"></i>Batal
                                                                    </button>
                                                                    <button type="button" id="btn-save-jenis-makanan" class="btn btn-success">
                                                                        <i class="fa fa-check mr-1"></i>Pilih / Simpan
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                        {{-- Jenis Sampel (editable text) mengikuti jenis makanan, disimpan ke Sample.nama_jenis_makanan --}}
                                                        <div class="form-group">
                                                            <label class="font-weight-bold" for="nama_jenis_makanan">
                                                                <i class="fa fa-tag mr-2"></i>Jenis Sampel
                                                            </label>
                                                            @php
                                                                // Default Jenis Sampel:
                                                                // 1. Jika sudah pernah disimpan (non-kosong), gunakan nama_jenis_makanan
                                                                // 2. Jika belum, gunakan titik_pengambilan
                                                                $defaultNamaJenis = (isset($sample->nama_jenis_makanan) && $sample->nama_jenis_makanan != '')
                                                                    ? $sample->namaJenisMakananPlain()
                                                                    : ($sample->titik_pengambilan ?? '');
                                                            @endphp
                                                            <textarea id="nama_jenis_makanan"
                                                                name="nama_jenis_makanan" class="form-control" rows="3"
                                                                placeholder="Contoh: Lemper, Nasi Uduk, dll">{{ old('nama_jenis_makanan', $defaultNamaJenis) }}</textarea>
                                                            <small class="text-muted">Nilai ini disimpan ke data sampel dan
                                                                tampil sebagai <strong>Titik Sampel</strong> di laporan.
                                                                Kolom <strong>Jenis Sampel</strong> di laporan memakai pilihan Jenis Makanan di atas.</small>
                                                        </div>
                                                </div>
                                            </div>
                                            <!-- Hasil Pengujian Section -->
                                            <div class="card border-0 mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="mb-0"><i class="fa fa-table mr-2"></i>Data Hasil
                                                        Pengujian</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table id="table-parameter" class="table table-hover table-striped mb-0">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th width="5%" class="text-center">No</th>
                                                                    <th width="20%">Parameter</th>
                                                                    <th width="15%" class="text-center">Kadar Maksimum Yang diperbolehkan</th>
                                                                    <th width="10%" class="text-center">Satuan</th>
                                                                    <th width="20%">Hasil</th>
                                                                    <th width="15%">Metode</th>
                                                                    <th width="15%">Keterangan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $no = 1;
                                                                    $tidak_simpan = false;
                                                                    $missing_baku_mutu_count = 0;
                                                                    // Jenis makanan yang sedang dipilih di halaman (query/picker), bukan hanya yang tersimpan di sample
                                                                    $currentJenisMakananId = $jenis_makanan_id ?? null;
                                                                    $currentJenisMakananName = '';
                                                                    if (!empty($currentJenisMakananId)) {
                                                                        $jmCurrent = collect($jenisMakananAll ?? [])->firstWhere('id_jenis_makanan', $currentJenisMakananId)
                                                                            ?? collect($allJenisMakanan ?? [])->firstWhere('id_jenis_makanan', $currentJenisMakananId);
                                                                        $currentJenisMakananName = $jmCurrent->name_jenis_makanan ?? '';
                                                                    }
                                                                @endphp
                                                                @php
                                                                    $paramIndex = 0; // Counter untuk data-index yang urut
                                                                @endphp
                                                                @foreach ($laboratoriummethods as $laboratoriummethod)
                                                                    @if (count($laboratoriummethod['detail']) == 0)
                                                                        @php
                                                                            $paramIndex++; // Increment untuk parameter utama
                                                                        @endphp
                                                                        @if (!is_null($laboratoriummethod->id_baku_mutu))
                                                                            <tr>
                                                                                <td class="text-center align-middle">
                                                                                    {{ $no }}</td>
                                                                                <td class="align-middle">
                                                                                    <div>
                                                                                        <b>{!! $laboratoriummethod->name_report ?? $laboratoriummethod->params_method !!}</b>
                                                                                        <div class="mt-2" style="display: flex; align-items: center; gap: 8px;">
                                                                                            <div class="custom-control custom-checkbox"
                                                                                                style="display: inline-block;">
                                                                                                <input type="checkbox"
                                                                                                    id="status_{{ $laboratoriummethod->method_id }}"
                                                                                                    value="true"
                                                                                                    name="status_{{ $laboratoriummethod->method_id }}"
                                                                                                    class="custom-control-input status-relay"
                                                                                                    onchange="updateStatusLabel(this, 'label_{{ $laboratoriummethod->method_id }}')"
                                                                                                    checked>
                                                                                                <label
                                                                                                    class="custom-control-label"
                                                                                                    for="status_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-toggle="tooltip"
                                                                                                    data-placement="top"
                                                                                                    title="Klik untuk mengubah status pengisian">
                                                                                                </label>
                                                                                            </div>
                                                                                            <small
                                                                                                id="label_{{ $laboratoriummethod->method_id }}"
                                                                                                class="badge badge-success"
                                                                                                style="font-size: 10px; padding: 4px 8px;">
                                                                                                <i
                                                                                                    class="fa fa-check-circle mr-1"></i>Wajib
                                                                                                Diisi
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-center align-middle">
                                                                                    <span id="nilai_baku_mutu_display_{{ $laboratoriummethod->method_id }}">
                                                                                    {!! rubahNilaikeHtml($laboratoriummethod->nilai_baku_mutu) !!}
                                                                                    </span>
                                                                                    @if (!is_null($laboratoriummethod->id_baku_mutu))
                                                                                    <div class="mt-1">
                                                                                        @if (!empty($laboratoriummethod->has_sample_override))
                                                                                        <span class="badge badge-info mb-1" style="font-size:9px;" title="Baku mutu ini telah di-override khusus untuk sampel ini">
                                                                                            <i class="fa fa-star mr-1"></i>Override Sampel
                                                                                        </span><br>
                                                                                        @endif
                                                                                        <button type="button"
                                                                                            class="btn btn-xs btn-outline-warning btn-edit-baku-mutu"
                                                                                            data-id-baku-mutu="{{ $laboratoriummethod->id_baku_mutu }}"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-method-name="{{ html_entity_decode(strip_tags($laboratoriummethod->name_report ?? $laboratoriummethod->params_method ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}"
                                                                                            data-sample-progress-id="{{ $progress }}"
                                                                                            data-sample-id="{{ $sample->id_samples ?? '' }}"
                                                                                            data-has-override="{{ !empty($laboratoriummethod->has_sample_override) ? '1' : '0' }}"
                                                                                            data-current-nilai="{{ e($laboratoriummethod->nilai_baku_mutu ?? '') }}"
                                                                                            data-current-min="{{ e(isset($laboratoriummethod->min) && $laboratoriummethod->min !== null ? (string) $laboratoriummethod->min : '') }}"
                                                                                            data-current-max="{{ e(isset($laboratoriummethod->max) && $laboratoriummethod->max !== null ? (string) $laboratoriummethod->max : '') }}"
                                                                                            data-current-equal="{{ e($laboratoriummethod->equal ?? '') }}"
                                                                                            data-current-unit-id="{{ e($laboratoriummethod->unit_id ?? '') }}"
                                                                                            data-current-library-id="{{ e($laboratoriummethod->library_id ?? $laboratoriummethod->baku_mutu_library_id ?? '') }}"
                                                                                            title="Edit nilai baku mutu"
                                                                                            style="font-size:10px;padding:2px 6px;">
                                                                                            <i class="fa fa-pencil-alt"></i> Edit
                                                                                        </button>
                                                                                    </div>
                                                                                    @endif
                                                                                </td>
                                                                                <td class="text-center align-middle">
                                                                                    <span id="satuan_baku_mutu_display_{{ $laboratoriummethod->method_id }}">
                                                                                    {!! isset($laboratoriummethod->shortname_unit) ? rubahNilaikeHtml($laboratoriummethod->shortname_unit) : '-' !!}
                                                                                    </span>
                                                                                </td>


                                                                                    <td>

                                                                                    <span
                                                                                        class="not_show_{{ $laboratoriummethod->method_id }}"
                                                                                        style="display: none;">-</span>

                                                                                    <div
                                                                                        class="show_{{ $laboratoriummethod->method_id }}">

                                                                                        <div>

                                                                                            <!-- Math Symbol Toolbar - HIDDEN, use Editor button instead -->
                                                                                            <div class="math-toolbar mb-2"
                                                                                                style="display: none;">
                                                                                                <small
                                                                                                    class="text-muted mr-2"
                                                                                                    style="font-weight: 600;">
                                                                                                    <i
                                                                                                        class="fa fa-calculator mr-1 text-info"></i>Simbol
                                                                                                    Math:
                                                                                                </small>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol="<">
                                                                                                    &lt;
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol=">">
                                                                                                    &gt;
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol="<=">
                                                                                                    &le;
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol=">=">
                                                                                                    &ge;
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-info math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol="^(">
                                                                                                    <i
                                                                                                        class="fa fa-superscript"></i>
                                                                                                    x<sup>n</sup>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-outline-warning math-symbol-btn"
                                                                                                    data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-symbol="±">
                                                                                                    ±
                                                                                                </button>
                                                                                            </div>


                                                                                            <div
                                                                                                style="position: relative;">
                                                                                                <!-- Hidden textarea for form submission -->
                                                                                                @php
                                                                                                    // Parse lokasi_data untuk parameter ini
                                                                                                    $lokasiDataForMethod = [];
                                                                                                    if (isset($laboratoriummethod->lokasi_data) && !empty($laboratoriummethod->lokasi_data)) {
                                                                                                        $lokasiDataForMethod = json_decode($laboratoriummethod->lokasi_data, true);
                                                                                                        if (!is_array($lokasiDataForMethod)) {
                                                                                                            $lokasiDataForMethod = [];
                                                                                                        }
                                                                                                    }
                                                                                                    // Simpan lokasi_data sebagai JSON di data attribute
                                                                                                    $lokasiDataJson = !empty($lokasiDataForMethod) ? json_encode($lokasiDataForMethod) : '';
                                                                                                @endphp
                                                                                                @php
                                                                                                    // Check if this is an option-based parameter
                                                                                                    $isOption = isset($laboratoriummethod->method_is_option) && $laboratoriummethod->method_is_option == 1;
                                                                                                    $optionValues = [];
                                                                                                    if ($isOption && !empty($laboratoriummethod->method_option)) {
                                                                                                        $optionValues = array_map('trim', explode(',', $laboratoriummethod->method_option));
                                                                                                    }
                                                                                                @endphp
                                                                                                <!-- Hidden input untuk offset baku mutu -->
                                                                                                <input type="hidden"
                                                                                                    name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                                                                                    id="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                                                                                    value="{{ isset($laboratoriummethod->offset_baku_mutu) ? $laboratoriummethod->offset_baku_mutu : 'default' }}">



                                                                                                @php
                                                                                                    $__nilaiBmPlain = '';
                                                                                                    if (!empty($laboratoriummethod->nilai_baku_mutu)) {
                                                                                                        $__nilaiBmPlain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->nilai_baku_mutu), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                    }
                                                                                                    if ($__nilaiBmPlain === '' && !empty($laboratoriummethod->equal) && preg_match('/[<>≤≥]/u', (string) $laboratoriummethod->equal)) {
                                                                                                        $__nilaiBmPlain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->equal), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                    }
                                                                                                @endphp
                                                                                                <textarea class="form-control result_method result_method_klinik result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    id="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    name="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                    data-index="{{ $paramIndex }}"
                                                                                                    data-min="{{ (isset($laboratoriummethod->min) ? (float)$laboratoriummethod->min : '') }}"
                                                                                                    data-max="{{ (isset($laboratoriummethod->max) ? (float)$laboratoriummethod->max : '')  }}"
                                                                                                    data-equal="{{ $laboratoriummethod->equal ?? '' }}"
                                                                                                    data-nilai-baku-mutu="{{ e($__nilaiBmPlain) }}"
                                                                                                    data-lokasi-data="{{ $lokasiDataJson }}"
                                                                                                    data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                                    data-number-format="en"
                                                                                                    data-is-option="{{ $isOption ? '1' : '0' }}"
                                                                                                    data-option-values="{{ $isOption ? json_encode($optionValues) : '[]' }}"
                                                                                                    placeholder="Hasil"
                                                                                                    required {{ $laboratoriummethod->is_ready == 1 ? '' : 'readonly' }} style="display: none;">
                                                                                      @if ($laboratoriummethod->is_ready == 1)
{!! isset($laboratoriummethod->hasil) ? rubahNilaikeForm($laboratoriummethod->hasil) : '' !!}
@else
Alat Dan Reagen tidak tersedia
@endif
                                                                                  </textarea>
                                                                                                @php
                                                                                                    // Referensi: Method (untuk semua jenis parameter)
                                                                                                    $isOption = false;
                                                                                                    $optionValue = '';

                                                                                                    if (
                                                                                                        isset(
                                                                                                            $laboratoriummethod->method_is_option,
                                                                                                        ) &&
                                                                                                        $laboratoriummethod->method_is_option ==
                                                                                                            1
                                                                                                    ) {
                                                                                                        $isOption = true;
                                                                                                        $optionValue =
                                                                                                            $laboratoriummethod->method_option ??
                                                                                                            '';
                                                                                                    }

                                                                                                    $options = [];
                                                                                                    if (
                                                                                                        $isOption &&
                                                                                                        !empty(
                                                                                                            $optionValue
                                                                                                        )
                                                                                                    ) {
                                                                                                        $options = array_map(
                                                                                                            'trim',
                                                                                                            explode(
                                                                                                                ',',
                                                                                                                $optionValue,
                                                                                                            ),
                                                                                                        );
                                                                                                    }
                                                                                                    $currentResult = isset(
                                                                                                        $laboratoriummethod->hasil,
                                                                                                    )
                                                                                                        ? rubahNilaikeForm(
                                                                                                            $laboratoriummethod->hasil,
                                                                                                        )
                                                                                                        : '';
                                                                                                    // Jika belum ada hasil dan ada equal, gunakan equal sebagai default
                                                                                                    if (
                                                                                                        empty(
                                                                                                            $currentResult
                                                                                                        ) &&
                                                                                                        isset(
                                                                                                            $laboratoriummethod->equal,
                                                                                                        ) &&
                                                                                                        !empty(
                                                                                                            $laboratoriummethod->equal
                                                                                                        )
                                                                                                    ) {
                                                                                                        $currentResult = rubahNilaikeForm(
                                                                                                            $laboratoriummethod->equal,
                                                                                                        );
                                                                                                    }
                                                                                                @endphp
                                                                                                @if ($laboratoriummethod->is_ready == 1)
                                                                                                    @if ($isOption && count($options) > 0)
                                                                                                        <!-- Hanya gunakan popup editor, opsi dikirim lewat data-attribute -->
                                                                                                        <button
                                                                                                            type="button"
                                                                                                            class="btn btn-sm btn-primary open-editor-modal"
                                                                                                            data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                                            data-method-name="{{ $laboratoriummethod->name_report }}"
                                                                                                            data-is-option="1"
                                                                                                            data-options='@json($options)'
                                                                                                            data-current-value="{{ $currentResult }}">
                                                                                                            <i
                                                                                                                class="fa fa-edit mr-1"></i>
                                                                                                            Pilih / Edit
                                                                                                            Hasil
                                                                                                        </button>
                                                                                                    @else
                                                                                                        <!-- TinyMCE Editor untuk is_option = 0 -->
                                                                                                        <button
                                                                                                            type="button"
                                                                                                            class="btn btn-sm btn-primary open-editor-modal"
                                                                                                            data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                                            data-method-name="{{ $laboratoriummethod->name_report }}">
                                                                                                            <i
                                                                                                                class="fa fa-edit mr-1"></i>
                                                                                                            Edit dengan
                                                                                                            Editor
                                                                                                        </button>
                                                                                                    @endif
                                                                                                @endif
                                                                                            </div>

                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    @php
                                                                                        $metodeOptions = \Smt\Masterweb\Helpers\Smt::parseMetodeOptionsListFromNameMethod(
                                                                                            $laboratoriummethod->name_method ?? '',
                                                                                        );
                                                                                        $metodeSelected = \Smt\Masterweb\Helpers\Smt::resolveMetodeSelectedFromNameMethod(
                                                                                            $laboratoriummethod->metode ?? '',
                                                                                            $laboratoriummethod->name_method ?? '',
                                                                                        );
                                                                                    @endphp
                                                                                    @if (count($metodeOptions) > 1)
                                                                                        <select class="form-control"
                                                                                            id="metode_{{ $laboratoriummethod->method_id }}"
                                                                                            name="metode_{{ $laboratoriummethod->method_id }}">
                                                                                            @foreach ($metodeOptions as $optMetode)
                                                                                                <option
                                                                                                    value="{{ $optMetode }}"
                                                                                                    {{ $metodeSelected === $optMetode ? 'selected' : '' }}>
                                                                                                    {{ $optMetode }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    @else
                                                                                        <textarea class="form-control metode-editor" id="metode_{{ $laboratoriummethod->method_id }}" name="metode_{{ $laboratoriummethod->method_id }}">{{ $metodeSelected !== '' ? $metodeSelected : ($laboratoriummethod->name_method ?? '') }}</textarea>
                                                                                    @endif
                                                                                </td>
                                                                                <td class="align-middle">
                                                                                    @php
                                                                                        $__ktStored = $laboratoriummethod->keterangan ?? '';
                                                                                        $__ktDefault = trim($laboratoriummethod->keterangan_default ?? '');
                                                                                        $__keteranganTampil = $__ktStored !== '' ? $__ktStored : $__ktDefault;
                                                                                        $__ktPlain = trim(html_entity_decode(strip_tags((string) $__keteranganTampil), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                                                                                        $__ktIsEmpty = ($__ktPlain === '' || $__ktPlain === '-');
                                                                                    @endphp
                                                                                    <div style="position: relative; min-width: 160px;">
                                                                                        <!-- Hidden textarea for form submission -->
                                                                                        <textarea class="form-control" id="keterangan_param_{{ $laboratoriummethod->method_id }}"
                                                                                            name="keterangan_{{ $laboratoriummethod->method_id }}" placeholder="Masukkan keterangan..."
                                                                                            style="display: none;">{{ $__keteranganTampil }}</textarea>

                                                                                        {{-- Editor keterangan selalu di-render agar kolom tidak kosong jika JS gagal --}}
                                                                                        <div class="inline-keterangan-editor {{ $__ktIsEmpty ? 'empty' : '' }}"
                                                                                            id="keterangan_editor_{{ $laboratoriummethod->method_id }}"
                                                                                            data-index="{{ $laboratoriummethod->method_id }}"
                                                                                            data-textarea-id="keterangan_param_{{ $laboratoriummethod->method_id }}"
                                                                                            data-placeholder="Klik untuk mengisi keterangan..."
                                                                                            contenteditable="true">
                                                                                            @if (!$__ktIsEmpty)
                                                                                                {!! rubahNilaikeForm($__keteranganTampil) !!}
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @else
                                                                            @php
                                                                                $tidak_simpan = true;
                                                                                $missing_baku_mutu_count++;
                                                                            @endphp
                                                                            <tr
                                                                                class="missing-baku-mutu-row"
                                                                                data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                                <td>{{ $no }}</td>
                                                                                @php
                                                                                    $jenis_makanan = $currentJenisMakananName ?: null;
                                                                                    if (!$jenis_makanan) {
                                                                                        $jenis_makanan_rel = $sample->jenis_makanan;
                                                                                        if (isset($jenis_makanan_rel)) {
                                                                                            $jenis_makanan = $jenis_makanan_rel->name_jenis_makanan;
                                                                                        }
                                                                                    }
                                                                                @endphp
                                                                                <td colspan="7">
                                                                                    <div class="alert alert-warning mb-2"
                                                                                        style="color: #856404; background-color: #fff3cd; border-color: #ffeaa7; margin-bottom: 10px;">
                                                                                        <i
                                                                                            class="fa fa-exclamation-triangle mr-2"></i>
                                                                                        Baku mutu untuk parameter
                                                                                        <b>{{ $laboratoriummethod->params_method }}</b>,
                                                                                        untuk
                                                                                        jenis sarana
                                                                                        <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) || $jenis_makanan === '' ? '' : ' - ' . $jenis_makanan }}</b></u>
                                                                                        belum tersedia.
                                                                                    </div>
                                                                                    @php
                                                                                        $_stNameMissing = $sample->name_sample_type ?? '';
                                                                                        $_isMmlMissing = str_contains($_stNameMissing, 'Makanan')
                                                                                            || str_contains($_stNameMissing, 'Minuman')
                                                                                            || str_contains($_stNameMissing, 'Lainnya');
                                                                                    @endphp
                                                                                    <div class="d-flex flex-wrap justify-content-center" style="gap: 8px;">
                                                                                        <button type="button"
                                                                                            class="btn btn-primary btn-sm btn-tambah-baku-mutu"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-method-name="{{ $laboratoriummethod->params_method }}"
                                                                                            data-sample-type-id="{{ $sample->id_sample_type }}"
                                                                                            data-sample-type-name="{{ $sample->name_sample_type }}"
                                                                                            data-jenis-makanan-id="{{ $currentJenisMakananId ?? '' }}"
                                                                                            data-jenis-makanan-name="{{ $jenis_makanan ?? '' }}"
                                                                                            data-lab-code="{{ $lab->kode_laboratorium }}"
                                                                                            data-lab-id="{{ $lab->id_laboratorium }}"
                                                                                            data-prefer-referensi="0"
                                                                                            style="position: relative; z-index: 1000; cursor: pointer;">
                                                                                            <i class="fa fa-plus mr-1"></i>
                                                                                            Tambah Baru
                                                                                        </button>
                                                                                        <button type="button"
                                                                                            class="btn btn-info btn-sm btn-tambah-baku-mutu"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-method-name="{{ $laboratoriummethod->params_method }}"
                                                                                            data-sample-type-id="{{ $sample->id_sample_type }}"
                                                                                            data-sample-type-name="{{ $sample->name_sample_type }}"
                                                                                            data-jenis-makanan-id="{{ $currentJenisMakananId ?? '' }}"
                                                                                            data-jenis-makanan-name="{{ $jenis_makanan ?? '' }}"
                                                                                            data-lab-code="{{ $lab->kode_laboratorium }}"
                                                                                            data-lab-id="{{ $lab->id_laboratorium }}"
                                                                                            data-prefer-referensi="1"
                                                                                            style="position: relative; z-index: 1000; cursor: pointer;">
                                                                                            <i class="fa fa-copy mr-1"></i>
                                                                                            @if ($_isMmlMissing)
                                                                                                Dari Referensi
                                                                                            @else
                                                                                                Dari Referensi Parameter Lain
                                                                                            @endif
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    @else
                                                                        @php
                                                                            $paramIndex++; // Increment untuk parameter parent yang punya detail
                                                                        @endphp
                                                                        @if (!is_null($laboratoriummethod->id_baku_mutu))
                                                                            <tr>
                                                                                <td style="vertical-align:top"
                                                                                    rowspan="{{ count($laboratoriummethod['detail']) + 1 }}">
                                                                                    {{ $no }}</td>
                                                                                <td colspan="7">
                                                                                    <b>{!! $laboratoriummethod->name_report ?? $laboratoriummethod->params_method !!}</b>
                                                                                </td>
                                                                            </tr>
                                                                            @foreach ($laboratoriummethod['detail'] as $detail)
                                                                                @php
                                                                                    $paramIndex++; // Increment untuk setiap detail parameter
                                                                                @endphp
                                                                                <tr>
                                                                                    <td>
                                                                                        <div>
                                                                                            <b>{!! $detail->name_sample_result_detail !!}</b>
                                                                                            <div class="mt-2" style="display: flex; align-items: center; gap: 8px;">
                                                                                                <div class="custom-control custom-checkbox"
                                                                                                    style="display: inline-block;">
                                                                                                    <input type="checkbox"
                                                                                                        id="status_{{ $detail->id_sample_result_detail }}"
                                                                                                        value="true"
                                                                                                        name="status_{{ $detail->id_sample_result_detail }}"
                                                                                                        class="custom-control-input status-relay"
                                                                                                        onchange="updateStatusLabel(this, 'label_{{ $detail->id_sample_result_detail }}')"
                                                                                                        checked>
                                                                                                    <label
                                                                                                        class="custom-control-label"
                                                                                                        for="status_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-toggle="tooltip"
                                                                                                        data-placement="top"
                                                                                                        title="Klik untuk mengubah status pengisian">
                                                                                                    </label>
                                                                                                </div>
                                                                                                <small
                                                                                                    id="label_{{ $detail->id_sample_result_detail }}"
                                                                                                    class="badge badge-success"
                                                                                                    style="font-size: 10px; padding: 4px 8px;">
                                                                                                    <i
                                                                                                        class="fa fa-check-circle mr-1"></i>Wajib
                                                                                                    Diisi
                                                                                                </small>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        {!! $detail->nilai_sample_result_detail !!}
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        {!! isset($laboratoriummethod->shortname_unit) ? rubahNilaikeHtml($laboratoriummethod->shortname_unit) : '-' !!}
                                                                                    </td>
                                                                                    <td>
                                                                                        <span
                                                                                            class="not_show_{{ $detail->id_sample_result_detail }}"
                                                                                            style="display: none;">-</span>
                                                                                        <div
                                                                                            class="show_{{ $detail->id_sample_result_detail }}">
                                                                                            <div>

                                                                                                <!-- Math Symbol Toolbar for Detail - HIDDEN, use Editor button instead -->
                                                                                                <div class="math-toolbar mb-2"
                                                                                                    style="display: none;">
                                                                                                    <small
                                                                                                        class="text-muted mr-2"
                                                                                                        style="font-weight: 600;">
                                                                                                        <i
                                                                                                            class="fa fa-calculator mr-1 text-info"></i>Simbol
                                                                                                        Math:
                                                                                                    </small>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol="<">
                                                                                                        &lt;
                                                                                                    </button>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol=">">
                                                                                                        &gt;
                                                                                                    </button>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol="<=">
                                                                                                        &le;
                                                                                                    </button>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-secondary math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol=">=">
                                                                                                        &ge;
                                                                                                    </button>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-info math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol="^(">
                                                                                                        <i
                                                                                                            class="fa fa-superscript"></i>
                                                                                                        x<sup>n</sup>
                                                                                                    </button>
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-warning math-symbol-btn"
                                                                                                        data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-symbol="±">
                                                                                                        ±
                                                                                                    </button>
                                                                                                </div>
                                                                                                @php
                                                                                                    // Referensi: Method (untuk semua jenis parameter detail)
                                                                                                    $isOptionDetail = false;
                                                                                                    $optionValueDetail =
                                                                                                        '';

                                                                                                    if (
                                                                                                        isset(
                                                                                                            $laboratoriummethod->method_is_option,
                                                                                                        ) &&
                                                                                                        $laboratoriummethod->method_is_option ==
                                                                                                            1
                                                                                                    ) {
                                                                                                        $isOptionDetail = true;
                                                                                                        $optionValueDetail =
                                                                                                            $laboratoriummethod->method_option ??
                                                                                                            '';
                                                                                                    }

                                                                                                    $optionsDetail = [];
                                                                                                    if (
                                                                                                        $isOptionDetail &&
                                                                                                        !empty(
                                                                                                            $optionValueDetail
                                                                                                        )
                                                                                                    ) {
                                                                                                        $optionsDetail = array_map(
                                                                                                            'trim',
                                                                                                            explode(
                                                                                                                ',',
                                                                                                                $optionValueDetail,
                                                                                                            ),
                                                                                                        );
                                                                                                    }
                                                                                                    $currentResultDetail = isset(
                                                                                                        $detail->hasil,
                                                                                                    )
                                                                                                        ? rubahNilaikeForm(
                                                                                                            $detail->hasil,
                                                                                                        )
                                                                                                        : '';
                                                                                                    // Jika belum ada hasil dan ada equal, gunakan equal sebagai default
                                                                                                    if (
                                                                                                        empty(
                                                                                                            $currentResultDetail
                                                                                                        ) &&
                                                                                                        isset(
                                                                                                            $detail->equal_sample_result_detail,
                                                                                                        ) &&
                                                                                                        !empty(
                                                                                                            $detail->equal_sample_result_detail
                                                                                                        )
                                                                                                    ) {
                                                                                                        $currentResultDetail = rubahNilaikeForm(
                                                                                                            $detail->equal_sample_result_detail,
                                                                                                        );
                                                                                                    }
                                                                                                @endphp
                                                                                                <div
                                                                                                    style="position: relative;">
                                                                                                    <!-- Hidden input untuk offset baku mutu -->
                                                                                                    <input type="hidden"
                                                                                                        name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                                                                        id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                                                                        value="{{ isset($detail->offset_baku_mutu) ? $detail->offset_baku_mutu : 'default' }}">

                                                                                                    <!-- Hidden textarea for form submission -->
                                                                                                    @php
                                                                                                        // Check if this is an option-based parameter for detail
                                                                                                        $isOptionDetailForTextarea = isset($laboratoriummethod->method_is_option) && $laboratoriummethod->method_is_option == 1;
                                                                                                        $optionValuesDetail = [];
                                                                                                        if ($isOptionDetailForTextarea && !empty($laboratoriummethod->method_option)) {
                                                                                                            $optionValuesDetail = array_map('trim', explode(',', $laboratoriummethod->method_option));
                                                                                                        }
                                                                                                    @endphp
                                                                                                    @php
                                                                                                        $__nilaiBmPlainDetail = '';
                                                                                                        if (!empty($laboratoriummethod->nilai_baku_mutu)) {
                                                                                                            $__nilaiBmPlainDetail = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->nilai_baku_mutu), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                        }
                                                                                                        if ($__nilaiBmPlainDetail === '' && !empty($detail->equal_sample_result_detail) && preg_match('/[<>≤≥]/u', (string) $detail->equal_sample_result_detail)) {
                                                                                                            $__nilaiBmPlainDetail = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($detail->equal_sample_result_detail), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                        }
                                                                                                        if ($__nilaiBmPlainDetail === '' && !empty($laboratoriummethod->equal) && preg_match('/[<>≤≥]/u', (string) $laboratoriummethod->equal)) {
                                                                                                            $__nilaiBmPlainDetail = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->equal), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                        }
                                                                                                    @endphp
                                                                                                    <textarea class="form-control result_method result_method_klinik result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        id="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        name="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                        data-index="{{ $paramIndex }}"
                                                                                                        data-min="{{ $detail->min_sample_result_detail }}"
                                                                                                        data-max="{{ $detail->max_sample_result_detail }}"
                                                                                                        data-equal="{{ $detail->equal_sample_result_detail }}"
                                                                                                        data-nilai-baku-mutu="{{ e($__nilaiBmPlainDetail) }}"
                                                                                                        data-number-format="en"
                                                                                                        data-is-option="{{ $isOptionDetailForTextarea ? '1' : '0' }}"
                                                                                                        data-option-values="{{ $isOptionDetailForTextarea ? json_encode($optionValuesDetail) : '[]' }}"
                                                                                                        placeholder="Hasil" required style="display: none;">{!! isset($detail->hasil)
                                                                                                            ? rubahNilaikeForm($detail->hasil)
                                                                                                            : '' !!}</textarea>
                                                                                                    @if ($isOptionDetail && count($optionsDetail) > 0)
                                                                                                        <!-- Hanya gunakan popup editor, opsi dikirim lewat data-attribute (Detail) -->
                                                                                                        <button
                                                                                                            type="button"
                                                                                                            class="btn btn-sm btn-primary open-editor-modal"
                                                                                                            data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                            data-method-id="{{ $detail->id_sample_result_detail }}"
                                                                                                            data-method-name="{{ $detail->nama_sample_result_detail }}"
                                                                                                            data-is-option="1"
                                                                                                            data-options='@json($optionsDetail)'
                                                                                                            data-current-value="{{ $currentResultDetail }}">
                                                                                                            <i
                                                                                                                class="fa fa-edit mr-1"></i>
                                                                                                            Pilih / Edit
                                                                                                            Hasil
                                                                                                        </button>
                                                                                                    @else
                                                                                                        <!-- TinyMCE Editor untuk is_option = 0 (Detail) -->
                                                                                                        <button
                                                                                                            type="button"
                                                                                                            class="btn btn-sm btn-primary open-editor-modal"
                                                                                                            data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                            data-method-id="{{ $detail->id_sample_result_detail }}"
                                                                                                            data-method-name="{{ $detail->nama_sample_result_detail }}">
                                                                                                            <i
                                                                                                                class="fa fa-edit mr-1"></i>
                                                                                                            Edit dengan
                                                                                                            Editor
                                                                                                        </button>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        @php
                                                                                            $metodeOptions = \Smt\Masterweb\Helpers\Smt::parseMetodeOptionsListFromNameMethod(
                                                                                                $laboratoriummethod->name_method ?? '',
                                                                                            );
                                                                                            $metodeSelected = \Smt\Masterweb\Helpers\Smt::resolveMetodeSelectedFromNameMethod(
                                                                                                $laboratoriummethod->metode ?? '',
                                                                                                $laboratoriummethod->name_method ?? '',
                                                                                            );
                                                                                        @endphp
                                                                                        @if (count($metodeOptions) > 1)
                                                                                            <select class="form-control"
                                                                                                id="metode_{{ $laboratoriummethod->method_id }}"
                                                                                                name="metode_{{ $laboratoriummethod->method_id }}">
                                                                                                @foreach ($metodeOptions as $optMetode)
                                                                                                    <option
                                                                                                        value="{{ $optMetode }}"
                                                                                                        {{ $metodeSelected === $optMetode ? 'selected' : '' }}>
                                                                                                        {{ $optMetode }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        @else
                                                                                            <textarea class="form-control metode-editor" id="metode_{{ $laboratoriummethod->method_id }}" name="metode_{{ $laboratoriummethod->method_id }}">{{ $metodeSelected !== '' ? $metodeSelected : ($laboratoriummethod->name_method ?? '') }}</textarea>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td>
                                                                                        @php
                                                                                            $__ktDetailStored = $detail->keterangan ?? '';
                                                                                            $__ktDetailDefault = trim($laboratoriummethod->keterangan_default ?? '');
                                                                                            $__keteranganDetailTampil = $__ktDetailStored !== '' ? $__ktDetailStored : $__ktDetailDefault;
                                                                                            $__ktDetailPlain = trim(html_entity_decode(strip_tags((string) $__keteranganDetailTampil), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                                                                                            $__ktDetailIsEmpty = ($__ktDetailPlain === '' || $__ktDetailPlain === '-');
                                                                                        @endphp
                                                                                        <div style="position: relative; min-width: 160px;">
                                                                                            <!-- Hidden textarea for form submission -->
                                                                                            <textarea class="form-control" id="keterangan_param_{{ $detail->id_sample_result_detail }}"
                                                                                                name="keterangan_{{ $laboratoriummethod->method_id }}" style="display: none;">{{ $__keteranganDetailTampil }}</textarea>

                                                                                            <div class="inline-keterangan-editor {{ $__ktDetailIsEmpty ? 'empty' : '' }}"
                                                                                                id="keterangan_editor_{{ $detail->id_sample_result_detail }}"
                                                                                                data-index="{{ $detail->id_sample_result_detail }}"
                                                                                                data-textarea-id="keterangan_param_{{ $detail->id_sample_result_detail }}"
                                                                                                data-placeholder="Klik untuk mengisi keterangan..."
                                                                                                contenteditable="true">
                                                                                                @if (!$__ktDetailIsEmpty)
                                                                                                    {!! rubahNilaikeForm($__keteranganDetailTampil) !!}
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @else
                                                                            @php
                                                                                $tidak_simpan = true;
                                                                                $missing_baku_mutu_count++;
                                                                            @endphp
                                                                            <tr
                                                                                class="missing-baku-mutu-row"
                                                                                data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                                <td>{{ $no }}</td>
                                                                                @php
                                                                                    $jenis_makanan = $currentJenisMakananName ?: null;
                                                                                    if (!$jenis_makanan) {
                                                                                        $jenis_makanan_rel = $sample->jenis_makanan;
                                                                                        if (isset($jenis_makanan_rel)) {
                                                                                            $jenis_makanan = $jenis_makanan_rel->name_jenis_makanan;
                                                                                        }
                                                                                    }
                                                                                @endphp
                                                                                <td colspan="7">
                                                                                    <div class="alert alert-warning mb-2"
                                                                                        style="color: #856404; background-color: #fff3cd; border-color: #ffeaa7; margin-bottom: 10px;">
                                                                                        <i
                                                                                            class="fa fa-exclamation-triangle mr-2"></i>
                                                                                        Baku mutu untuk parameter
                                                                                        <b>{{ $laboratoriummethod->params_method }}</b>,
                                                                                        untuk
                                                                                        jenis sarana
                                                                                        <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) || $jenis_makanan === '' ? '' : ' - ' . $jenis_makanan }}</b></u>
                                                                                        belum tersedia.
                                                                                    </div>
                                                                                    @php
                                                                                        $_stNameMissingDetail = $sample->name_sample_type ?? '';
                                                                                        $_isMmlMissingDetail = str_contains($_stNameMissingDetail, 'Makanan')
                                                                                            || str_contains($_stNameMissingDetail, 'Minuman')
                                                                                            || str_contains($_stNameMissingDetail, 'Lainnya');
                                                                                    @endphp
                                                                                    <div class="d-flex flex-wrap justify-content-center" style="gap: 8px;">
                                                                                        <button type="button"
                                                                                            class="btn btn-primary btn-sm btn-tambah-baku-mutu"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-method-name="{{ $laboratoriummethod->params_method }}"
                                                                                            data-sample-type-id="{{ $sample->id_sample_type }}"
                                                                                            data-sample-type-name="{{ $sample->name_sample_type }}"
                                                                                            data-jenis-makanan-id="{{ $currentJenisMakananId ?? '' }}"
                                                                                            data-jenis-makanan-name="{{ $jenis_makanan ?? '' }}"
                                                                                            data-lab-code="{{ $lab->kode_laboratorium }}"
                                                                                            data-lab-id="{{ $lab->id_laboratorium }}"
                                                                                            data-prefer-referensi="0"
                                                                                            style="position: relative; z-index: 1000; cursor: pointer;">
                                                                                            <i class="fa fa-plus mr-1"></i>
                                                                                            Tambah Baru
                                                                                        </button>
                                                                                        <button type="button"
                                                                                            class="btn btn-info btn-sm btn-tambah-baku-mutu"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-method-name="{{ $laboratoriummethod->params_method }}"
                                                                                            data-sample-type-id="{{ $sample->id_sample_type }}"
                                                                                            data-sample-type-name="{{ $sample->name_sample_type }}"
                                                                                            data-jenis-makanan-id="{{ $currentJenisMakananId ?? '' }}"
                                                                                            data-jenis-makanan-name="{{ $jenis_makanan ?? '' }}"
                                                                                            data-lab-code="{{ $lab->kode_laboratorium }}"
                                                                                            data-lab-id="{{ $lab->id_laboratorium }}"
                                                                                            data-prefer-referensi="1"
                                                                                            style="position: relative; z-index: 1000; cursor: pointer;">
                                                                                            <i class="fa fa-copy mr-1"></i>
                                                                                            @if ($_isMmlMissingDetail)
                                                                                                Dari Referensi
                                                                                            @else
                                                                                                Dari Referensi Parameter Lain
                                                                                            @endif
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    @endif


                                                                    @php
                                                                        $no++;
                                                                    @endphp
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.keterangan-metode-section', [
                                                'sample' => $sample,
                                                'laboratoriummethods' => $laboratoriummethods ?? collect(),
                                            ])

                                            @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.catatan-hasil-section', [
                                                'sample' => $sample,
                                                'lab' => $lab ?? null,
                                            ])

                                            <!-- Additional Information Section -->
                                            <div class="card border-0 mb-4" style="background-color: #fce4ec;">
                                                <div class="card-body">
                                                    <div class="form-group" style="display: none;">
                                                        <label for="tembusan" class="font-weight-bold"><i
                                                                class="fa fa-copy mr-2"></i>Tembusan</label>
                                                        <textarea class="form-control shadow-sm" id="tembusan" name="tembusan" rows="10"
                                                            placeholder="Masukkan tembusan laporan..."></textarea>
                                                    </div>

                                                    <div class="form-group" style="display: none;">
                                                        <label for="TampilanLaporan" class="font-weight-bold"><i
                                                                class="fa fa-file-alt mr-2"></i>Isi Tabel Laporan</label>
                                                        <select class="form-control shadow-sm" id="TampilanLaporan"
                                                            name="only_fisika">
                                                            <option value="0">FISIKA + KIMIA</option>
                                                            <option value="1">FISIKA</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-check p-3 border rounded"
                                                        style="background-color: #fff8e1;">
                                                        <input class="form-check-input" name="baca_hasil" type="checkbox"
                                                            value="ya" id="input_checkbox_confirm_submit_baca_hasil"
                                                            required>

                                                        <label class="form-check-label font-weight-bold ml-2"
                                                            for="input_checkbox_confirm_submit_baca_hasil">
                                                            <i class="fa fa-check-circle text-success mr-2"></i>Pengisian
                                                            Hasil sudah sesuai dengan hasil uji lapangan.
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($tidak_simpan)
                                                <div id="missing-baku-mutu-banner" class="alert alert-warning border-left-warning shadow-sm">
                                                    <i class="fa fa-exclamation-triangle mr-2"></i>
                                                    Masih ada <b><span id="missing-baku-mutu-count">{{ $missing_baku_mutu_count }}</span></b>
                                                    parameter tanpa baku mutu. Silakan isi baku mutu terlebih dahulu sebelum Simpan / Selesai.
                                                </div>
                                            @else
                                                <div id="missing-baku-mutu-banner" class="alert alert-warning border-left-warning shadow-sm" style="display:none;">
                                                    <i class="fa fa-exclamation-triangle mr-2"></i>
                                                    Masih ada <b><span id="missing-baku-mutu-count">0</span></b>
                                                    parameter tanpa baku mutu. Silakan isi baku mutu terlebih dahulu sebelum Simpan / Selesai.
                                                </div>
                                            @endif

                                            <!-- Action Buttons -->
                                            <div class="card border-0" id="baca-hasil-action-buttons" style="{{ $tidak_simpan ? 'display:none;' : '' }}">
                                                <div class="card-body text-center">
                                                    <button type="button" id="submitAll"
                                                        class="btn btn-success btn-lg shadow-sm mr-2">
                                                        <i class="fa fa-check mr-2"></i>Selesai
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-lg shadow-sm mr-2" id="btn-open-review-hasil">
                                                        <i class="fa fa-eye mr-2"></i>Review Hasil
                                                    </button>
                                                    <a href="javascript:void(0)" id="saveAll"
                                                        class="btn btn-primary btn-lg shadow-sm mr-2">
                                                        <i class="fa fa-save mr-2"></i>Simpan
                                                    </a>
                                                    <button type="button" class="btn btn-secondary btn-lg shadow-sm"
                                                        onclick="window.history.back()">
                                                        <i class="fa fa-arrow-left mr-2"></i>Kembali
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- utama -->
                </div>
                <!-- /.row -->
            </div>
        </div>
    </div>

    <!-- Modal Pilih Status Baku Mutu -->
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

    {{-- ============================================================
         MODAL EDIT BAKU MUTU
         ============================================================ --}}
    <div class="modal fade" id="modalEditBakuMutu" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fa fa-pencil-alt mr-2"></i>
                        Edit Baku Mutu &mdash; <span id="mepm-param-name" style="font-style:italic;"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs px-3 pt-3" id="editBakuMutuTabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-sample-override-link" data-toggle="tab" href="#tab-sample-override">
                                <i class="fa fa-star mr-1 text-info"></i> Khusus Sampel Ini
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-umum-link" data-toggle="tab" href="#tab-umum">
                                <i class="fa fa-globe mr-1 text-warning"></i> Secara Umum
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content px-3 py-3">

                        {{-- Tab 1: Khusus Sampel Ini --}}
                        <div class="tab-pane fade show active" id="tab-sample-override">
                            <div class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                                <i class="fa fa-info-circle mr-1"></i>
                                Perubahan hanya berlaku untuk <b>sampel ini saja</b>. Baku mutu umum tidak berubah.
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-flask mr-1"></i>Jenis Sampel
                                    </label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-override-sampletype-id" class="form-control form-control-sm">
                                                <option value="">Pilih Jenis Sampel</option>
                                                @if (isset($sample_types))
                                                    @foreach ($sample_types as $sample_type)
                                                        <option value="{{ $sample_type->id_sample_type }}">{{ $sample_type->name_sample_type }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateSampleType"
                                            title="Buat jenis sampel baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" style="font-size:11px;">
                                        Perubahan jenis sampel disimpan lewat tab <b>Secara Umum</b>.
                                    </small>
                                </div>
                            </div>
                            <div class="form-row mepm-jenis-makanan-row" style="display:none;">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-utensils mr-1"></i>Jenis Makanan
                                    </label>
                                    <select id="mepm-override-jenis-makanan-id" class="form-control form-control-sm">
                                        <option value="__none__">Tidak berdasarkan jenis makanan</option>
                                        @if (isset($allJenisMakanan))
                                            @foreach ($allJenisMakanan as $jm)
                                                <option value="{{ $jm->id_jenis_makanan }}">{{ $jm->name_jenis_makanan }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted" style="font-size:11px;">
                                        Perubahan jenis makanan disimpan lewat tab <b>Secara Umum</b>.
                                    </small>
                                </div>
                            </div>
                            <div class="form-row mepm-tipe-nilai-row" style="display:none;">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-balance-scale mr-1"></i>Tipe Nilai Baku Mutu
                                    </label>
                                    <select id="mepm-override-tipe-nilai" class="form-control form-control-sm">
                                        <option value="">Pilih Tipe Nilai</option>
                                        <option value="kuantitatif">Kuantitatif</option>
                                        <option value="kualitatif">Kualitatif</option>
                                    </select>
                                    <small class="text-muted" style="font-size:11px;">
                                        Perubahan tipe nilai disimpan lewat tab <b>Secara Umum</b>.
                                    </small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">Nilai Baku Mutu (Tampilan)</label>
                                    <textarea id="mepm-override-nilai-baku-mutu" class="form-control" rows="3" placeholder="Contoh: &lt;300, Tidak Berbau, dll."></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Min</label>
                                    <input type="text" class="form-control form-control-sm" id="mepm-override-min" placeholder="Minimal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Max</label>
                                    <input type="text" class="form-control form-control-sm" id="mepm-override-max" placeholder="Maksimal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Equal</label>
                                    <textarea id="mepm-override-equal" class="form-control form-control-sm" rows="2" placeholder="Sama dengan"></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">Satuan</label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-override-unit-id" class="form-control form-control-sm">
                                                <option value="">Pilih Satuan</option>
                                                @if (isset($units))
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id_unit }}">{!! rubahNilaikeHtml($unit->shortname_unit) !!}</option>
                                                    @endforeach
                                                @endif
                                                <option value="-">-</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-warning btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateUnit"
                                            title="Buat satuan baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-book mr-1"></i>Dokumen Acuan
                                    </label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-override-library-id" class="form-control form-control-sm">
                                                <option value="">Pilih Dokumen Acuan</option>
                                                @if (isset($libraries))
                                                    @foreach ($libraries as $library)
                                                        <option value="{{ $library->id_library }}">{{ $library->title_library }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateLibrary"
                                            title="Buat dokumen acuan baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-info btn-sm" id="btn-save-override-sample">
                                    <i class="fa fa-save mr-1"></i> Simpan untuk Sampel Ini
                                </button>
                            </div>
                        </div>

                        {{-- Tab 2: Secara Umum --}}
                        <div class="tab-pane fade" id="tab-umum">
                            <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:12px;">
                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                Perubahan akan berlaku <b>secara umum</b> untuk semua sampel yang menggunakan baku mutu ini.
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-flask mr-1"></i>Jenis Sampel
                                    </label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-umum-sampletype-id" class="form-control form-control-sm">
                                                <option value="">Pilih Jenis Sampel</option>
                                                @if (isset($sample_types))
                                                    @foreach ($sample_types as $sample_type)
                                                        <option value="{{ $sample_type->id_sample_type }}">{{ $sample_type->name_sample_type }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateSampleType"
                                            title="Buat jenis sampel baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row mepm-jenis-makanan-row" style="display:none;">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-utensils mr-1"></i>Jenis Makanan
                                    </label>
                                    <select id="mepm-umum-jenis-makanan-id" class="form-control form-control-sm">
                                        <option value="__none__">Tidak berdasarkan jenis makanan</option>
                                        @if (isset($allJenisMakanan))
                                            @foreach ($allJenisMakanan as $jm)
                                                <option value="{{ $jm->id_jenis_makanan }}">{{ $jm->name_jenis_makanan }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mepm-tipe-nilai-row" style="display:none;">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-balance-scale mr-1"></i>Tipe Nilai Baku Mutu
                                    </label>
                                    <select id="mepm-umum-tipe-nilai" class="form-control form-control-sm">
                                        <option value="">Pilih Tipe Nilai</option>
                                        <option value="kuantitatif">Kuantitatif</option>
                                        <option value="kualitatif">Kualitatif</option>
                                    </select>
                                    <small class="text-muted" style="font-size:11px;">
                                        Kualitatif: nilai setara/deskriptif (Equal). Kuantitatif: rentang Min/Max.
                                    </small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">Nilai Baku Mutu (Tampilan)</label>
                                    <textarea id="mepm-umum-nilai-baku-mutu" class="form-control" rows="3" placeholder="Contoh: &lt;300, Tidak Berbau, dll."></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Min</label>
                                    <input type="text" class="form-control form-control-sm" id="mepm-umum-min" placeholder="Minimal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Max</label>
                                    <input type="text" class="form-control form-control-sm" id="mepm-umum-max" placeholder="Maksimal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label style="font-size:12px;font-weight:600;">Equal</label>
                                    <textarea id="mepm-umum-equal" class="form-control form-control-sm" rows="2" placeholder="Sama dengan"></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">Satuan</label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-umum-unit-id" class="form-control form-control-sm">
                                                <option value="">Pilih Satuan</option>
                                                @if (isset($units))
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id_unit }}">{!! rubahNilaikeHtml($unit->shortname_unit) !!}</option>
                                                    @endforeach
                                                @endif
                                                <option value="-">-</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-warning btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateUnit"
                                            title="Buat satuan baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label style="font-size:12px;font-weight:600;">
                                        <i class="fa fa-book mr-1"></i>Dokumen Acuan
                                    </label>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1 mr-2">
                                            <select id="mepm-umum-library-id" class="form-control form-control-sm">
                                                <option value="">Pilih Dokumen Acuan</option>
                                                @if (isset($libraries))
                                                    @foreach ($libraries as $library)
                                                        <option value="{{ $library->id_library }}">{{ $library->title_library }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm flex-shrink-0"
                                            data-toggle="modal" data-target="#modalCreateLibrary"
                                            title="Buat dokumen acuan baru">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-warning btn-sm text-dark" id="btn-save-umum">
                                    <i class="fa fa-save mr-1"></i> Simpan Secara Umum
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL REVIEW HASIL
         ============================================================ --}}
    <div class="modal fade" id="modalReviewHasil" tabindex="-1" role="dialog" aria-labelledby="modalReviewHasilLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-body-scrollable" role="document" style="max-width: 520px;">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalReviewHasilLabel">
                        <i class="fa fa-eye mr-2"></i>Review Hasil Pemeriksaan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Sesuaikan pengaturan tampilan, lalu klik <strong>Terapkan</strong> untuk memperbarui preview.
                    </p>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-text-height mr-1"></i>Ukuran Font Hasil
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum, bukan narkoba)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">6</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="fontsize-slider" min="6" max="20" step="0.5" value="{{ old('fontsize_hasil', $sample->fontsize_hasil_baca_hasil ?? 12) }}">
                            <span class="text-muted small ml-2">20</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 90px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="fontsize-input" value="{{ old('fontsize_hasil', $sample->fontsize_hasil_baca_hasil ?? 12) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">pt</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white text-center">
                            <span id="fontsize-preview-sample" style="font-size: {{ old('fontsize_hasil', $sample->fontsize_hasil_baca_hasil ?? 12) }}pt;">
                                Contoh: Hemoglobin = <strong>14.5</strong> g/dL
                            </span>
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-align-justify mr-1"></i>Jarak Baris (Line Spacing)
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0.5</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="lineheight-slider" min="0.5" max="3.0" step="0.1" value="{{ $defaultLineHeightHasil }}">
                            <span class="text-muted small ml-2">3.0</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="lineheight-input" value="{{ $defaultLineHeightHasil }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white" id="lineheight-preview-text">
                            <span id="lineheight-preview-sample" style="line-height: {{ $defaultLineHeightHasil }}; display: block;">
                                Contoh baris pertama: Hemoglobin = <strong>14.5</strong> g/dL<br>
                                Contoh baris kedua: Leukosit = <strong>8.200</strong> /uL
                            </span>
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-arrows-v mr-1"></i>Margin Atas/Bawah Baris
                            <small class="text-muted font-weight-normal">(jarak antar baris & kolom tabel)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="padding-slider" min="0" max="16" step="0.5" value="{{ $defaultPaddingHasil }}">
                            <span class="text-muted small ml-2">16</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="padding-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="padding-input" value="{{ $defaultPaddingHasil }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">pt</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="padding-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Mengatur padding atas/bawah setiap sel di tabel hasil pemeriksaan. Nilai lebih besar = baris lebih renggang.
                        </small>
                    </div>

                    @php
                        $kesmasColWidthUi = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::uiPayload(
                            $sample ?? null,
                            null,
                            $lab ?? ($laboratorium ?? null)
                        );
                    @endphp
                    @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.pengaturan-column-widths', [
                        'sample' => $sample ?? null,
                        'kesmasColWidthUi' => $kesmasColWidthUi,
                    ])

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fa fa-file-alt mr-1"></i>Kop Surat
                        </label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-sm text-muted" id="kop-label-text">
                                    {{ (old('show_kop_hasil', $sample->show_kop_hasil_baca_hasil ?? 1)) ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                                </div>
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input" id="toggle-kop" {{ (old('show_kop_hasil', $sample->show_kop_hasil_baca_hasil ?? 1)) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="toggle-kop"></label>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Jika dimatikan, area kop tetap ada namun kosong (tanpa gambar).
                        </small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle mr-1"></i>
                        Pengaturan diterapkan saat mengklik <strong>Terapkan</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info" id="btn-buka-review">
                        <i class="fa fa-spinner fa-spin mr-1 d-none" id="review-loading-icon"></i>
                        <i class="fa fa-check mr-1" id="review-save-icon"></i>
                        <span class="btn-label-text">Terapkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL PREVIEW HASIL FULLSCREEN (iframe)
         ============================================================ --}}
    <div class="modal fade" id="modalPreviewHasil" tabindex="-1" role="dialog" aria-hidden="true"
         data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document" style="max-width: 98vw; width: 98vw; margin: 10px auto;">
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
                    <iframe id="preview-hasil-iframe" src="about:blank" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
                </div>
                <div class="modal-footer py-2" style="flex-shrink: 0;">
                    <small class="text-muted mr-auto">
                        <i class="fa fa-info-circle mr-1"></i>
                        Ini adalah preview hasil cetak saat ini.
                    </small>
                    <button type="button" class="btn btn-outline-warning btn-sm" id="btn-pengaturan-preview">
                        <i class="fa fa-cog mr-1"></i>Pengaturan Hasil
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-success btn-sm d-none" id="btn-preview-lanjut-selesai">
                        <i class="fa fa-check-circle mr-1"></i>Lanjutkan & Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_library')
    @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_unit')
    @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_sample_type')

@endsection

@section('scripts')
    @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.pengaturan-column-widths-script')
    <script>
        $(function () {
            // Pindahkan ke body agar nested modal tidak terpotong parent
            $('#modalCreateLibrary, #modalCreateUnit, #modalCreateSampleType').appendTo('body');

            $(document).on('show.bs.modal', '#modalCreateLibrary, #modalCreateUnit, #modalCreateSampleType', function () {
                var zIndex = 1065;
                $(this).css('z-index', zIndex);
                setTimeout(function () {
                    $('.modal-backdrop').not('.modal-stack').last()
                        .addClass('modal-stack')
                        .css('z-index', zIndex - 5);
                }, 0);
            });

            // Jaga body.modal-open agar parent Edit Baku Mutu tetap bisa di-scroll
            $(document).on('hidden.bs.modal', '#modalCreateLibrary, #modalCreateUnit, #modalCreateSampleType', function () {
                if ($('.modal.show').length) {
                    $('body').addClass('modal-open');
                }
            });

            // Sinkronkan pilihan jenis sampel antar tab Edit Baku Mutu + tampilkan jenis makanan bila relevan
            function _mepmIsMakananMinumanLainnya($select) {
                var text = ($select.find('option:selected').text() || '').trim();
                return text.indexOf('Makanan') !== -1 ||
                    text.indexOf('Minuman') !== -1 ||
                    text.indexOf('Lainnya') !== -1;
            }

            function _mepmToggleJenisMakanan() {
                var show = _mepmIsMakananMinumanLainnya($('#mepm-umum-sampletype-id')) ||
                    _mepmIsMakananMinumanLainnya($('#mepm-override-sampletype-id'));
                $('.mepm-jenis-makanan-row').toggle(show);
                $('.mepm-tipe-nilai-row').toggle(show);
                if (!show) {
                    ['#mepm-override-jenis-makanan-id', '#mepm-umum-jenis-makanan-id'].forEach(function (sel) {
                        var $el = $(sel);
                        if ($el.val() && $el.val() !== '__none__') {
                            $el.val('__none__').trigger('change.select2');
                        }
                    });
                    ['#mepm-override-tipe-nilai', '#mepm-umum-tipe-nilai'].forEach(function (sel) {
                        var $el = $(sel);
                        if ($el.val()) {
                            $el.val('');
                        }
                    });
                }
            }

            window._mepmToggleJenisMakanan = _mepmToggleJenisMakanan;

            $(document).on('change', '#mepm-override-sampletype-id, #mepm-umum-sampletype-id', function () {
                var val = $(this).val();
                var other = this.id === 'mepm-override-sampletype-id'
                    ? '#mepm-umum-sampletype-id'
                    : '#mepm-override-sampletype-id';
                if ($(other).val() !== val) {
                    $(other).val(val).trigger('change.select2');
                }
                _mepmToggleJenisMakanan();
            });

            // Sinkronkan pilihan jenis makanan antar tab Edit Baku Mutu
            $(document).on('change', '#mepm-override-jenis-makanan-id, #mepm-umum-jenis-makanan-id', function () {
                var val = $(this).val();
                var other = this.id === 'mepm-override-jenis-makanan-id'
                    ? '#mepm-umum-jenis-makanan-id'
                    : '#mepm-override-jenis-makanan-id';
                if ($(other).val() !== val) {
                    $(other).val(val).trigger('change.select2');
                }
            });

            // Sinkronkan tipe nilai (kualitatif/kuantitatif) antar tab
            $(document).on('change', '#mepm-override-tipe-nilai, #mepm-umum-tipe-nilai', function () {
                var val = $(this).val();
                var other = this.id === 'mepm-override-tipe-nilai'
                    ? '#mepm-umum-tipe-nilai'
                    : '#mepm-override-tipe-nilai';
                if ($(other).val() !== val) {
                    $(other).val(val);
                }
            });
        });
    </script>
    <!-- TinyMCE Script - Load from local assets (tidak pakai CDN) -->
    <script src="{{ asset('assets/admin/vendors/tinymce/tinymce.min.js') }}?v={{ time() }}"></script>
    <script>
        // Set TinyMCE base URL to local assets immediately after loading
        if (typeof tinymce !== 'undefined') {
            var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
            if (tinymce.baseURL === undefined ||
                tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
                tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                tinymce.baseURL = tinymceBasePath;
                console.log('TinyMCE baseURL set to local assets:', tinymce.baseURL);
            }

            // Override tinymce.init to ensure base_url is always set to local
            var originalInit = tinymce.init;
            tinymce.init = function(config) {
                if (!config.base_url) {
                    config.base_url = tinymceBasePath;
                }
                if (!config.suffix) {
                    config.suffix = '.min';
                }
                if (!config.theme_url) {
                    config.theme_url = tinymceBasePath + '/themes/modern/theme.min.js';
                }
                if (!config.skin_url) {
                    config.skin_url = tinymceBasePath + '/skins/lightgray';
                }
                return originalInit.call(this, config);
            };
        }

        // Nonaktifkan beforeunload handler untuk mencegah dialog konfirmasi saat user meninggalkan halaman
        // Set ke null untuk menonaktifkan semua handler
        window.onbeforeunload = null;

        // Override TinyMCE autosave beforeunload handler jika ada
        if (typeof tinymce !== 'undefined' && typeof tinymce.EditorManager !== 'undefined') {
            // Override _beforeUnloadHandler untuk mencegah dialog
            tinymce.EditorManager._beforeUnloadHandler = function() {
                // Do nothing - prevent dialog
                return undefined;
            };
            // Juga set window.onbeforeunload setelah TinyMCE load
            setTimeout(function() {
                window.onbeforeunload = null;
            }, 100);
        }
    </script>

    <script>
        // Function to update status label when checkbox changes
        function updateStatusLabel(checkbox, labelId) {
            var label = document.getElementById(labelId);
            var checkboxId = checkbox.id;
            var methodId = checkboxId.replace('status_', '');

            if (checkbox.checked) {
                // Checked: Wajib diisi
                label.className = 'badge badge-success';
                label.innerHTML = '<i class="fa fa-check-circle mr-1"></i>Wajib Diisi';

                // Tampilkan form dan button
                showHasilForm(methodId);
            } else {
                // Unchecked: Boleh dikosongkan
                label.className = 'badge badge-warning';
                label.innerHTML = '<i class="fa fa-times-circle mr-1"></i>Boleh Kosong';

                // Sembunyikan form dan button
                hideHasilForm(methodId);
            }
        }

        // Function to hide hasil form and button for a given method/detail ID
        function hideHasilForm(methodId) {
            // Find the textarea
            var $textarea = $('#result_method_' + methodId);
            if ($textarea.length > 0) {
                // Get data-index from textarea
                var index = $textarea.data('index') || methodId;

                // Find the hasil input container (contains input field, badge, and buttons)
                var $hasilContainer = $textarea.closest('td').find('.hasil-input-container');
                if ($hasilContainer.length > 0) {
                    $hasilContainer.hide();
                }

                // Also hide badge and buttons row if exists separately
                var $badgeButtonsRow = $textarea.closest('td').find('.badge-buttons-row');
                if ($badgeButtonsRow.length > 0) {
                    $badgeButtonsRow.hide();
                }
            }
        }

        // Function to show hasil form and button for a given method/detail ID
        function showHasilForm(methodId) {
            // Find the textarea
            var $textarea = $('#result_method_' + methodId);
            if ($textarea.length > 0) {
                // Get data-index from textarea
                var index = $textarea.data('index') || methodId;

                // Find the hasil input container (contains input field, badge, and buttons)
                var $hasilContainer = $textarea.closest('td').find('.hasil-input-container');
                if ($hasilContainer.length > 0) {
                    $hasilContainer.show();
                }

                // Also show badge and buttons row if exists separately
                var $badgeButtonsRow = $textarea.closest('td').find('.badge-buttons-row');
                if ($badgeButtonsRow.length > 0) {
                    $badgeButtonsRow.show();
                }
            }
        }

        $(document).ready(function() {
            function getMissingBakuMutuCount() {
                return $('.missing-baku-mutu-row:visible').length;
            }

            function updateMissingBakuMutuUI() {
                var missingCount = getMissingBakuMutuCount();
                $('#missing-baku-mutu-count').text(missingCount);
                if (missingCount > 0) {
                    $('#missing-baku-mutu-banner').show();
                    $('#baca-hasil-action-buttons').hide();
                } else {
                    $('#missing-baku-mutu-banner').hide();
                    $('#baca-hasil-action-buttons').show();
                }
            }

            function guardMissingBakuMutuBeforeSubmit() {
                var missingCount = getMissingBakuMutuCount();
                if (missingCount > 0) {
                    swal('Perhatian', 'Masih ada parameter tanpa baku mutu. Tambahkan baku mutu terlebih dahulu.', 'warning');
                    updateMissingBakuMutuUI();
                    return false;
                }
                return true;
            }

            updateMissingBakuMutuUI();

            // === INITIALIZE TOOLTIPS ===
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                boundary: 'window'
            });

            // === MATH SYMBOL TOOLBAR ===
            // Function to insert symbol at cursor position in textarea
            function insertSymbolAtCursor(textareaId, symbol) {
                var textarea = document.getElementById(textareaId);
                if (!textarea) return;

                var startPos = textarea.selectionStart;
                var endPos = textarea.selectionEnd;
                var textBefore = textarea.value.substring(0, startPos);
                var textAfter = textarea.value.substring(endPos, textarea.value.length);

                // Special handling untuk pangkat: insert ^() dan posisikan cursor di tengah
                if (symbol === '^(') {
                    textarea.value = textBefore + '^()' + textAfter;
                    // Set cursor di tengah kurung (setelah ^()
                    var newCursorPos = startPos + 2; // Position setelah ^(
                    textarea.setSelectionRange(newCursorPos, newCursorPos);
                } else {
                    // Insert symbol biasa
                    textarea.value = textBefore + symbol + textAfter;
                    // Set cursor position after inserted symbol
                    var newCursorPos = startPos + symbol.length;
                    textarea.setSelectionRange(newCursorPos, newCursorPos);
                }

                // Focus back to textarea
                textarea.focus();

                // Trigger input event untuk update simulasi output
                $(textarea).trigger('input');
            }

            // Handle math symbol button click
            $('.math-symbol-btn').on('click', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                var symbol = $(this).data('symbol');

                // Add button animation
                $(this).addClass('btn-primary').removeClass(
                    'btn-outline-secondary btn-outline-info btn-outline-warning');
                setTimeout(() => {
                    $(this).removeClass('btn-primary').addClass(
                        $(this).data('symbol') === '^(' ? 'btn-outline-info' :
                        $(this).data('symbol') === '±' ? 'btn-outline-warning' :
                        'btn-outline-secondary'
                    );
                }, 200);

                insertSymbolAtCursor(targetId, symbol);
            });

            // === JENIS SARANA ===
            $('#input_jenis_sarana').select2()
            let input_jenis_sarana_options = $('#input_jenis_sarana option')

            if ($('#input_jenis_sarana option:selected').text().trim() == 'Lainnya') {
                $('#input_jenis_sarana_lainnya').prop('disabled', false).prop('hidden', false)
            } else if (!!$('#input_jenis_sarana_lainnya').val() && $('#input_jenis_sarana_lainnya').val() != $(
                    '#input_jenis_sarana option:selected').text().trim()) {
                $('#input_jenis_sarana option[value="Lainnya"]').prop('selected', true)
                $('#input_jenis_sarana_lainnya').prop('disabled', false).prop('hidden', false)
            }

            $('#input_jenis_sarana').change(function() {
                let value = $(this).children(':selected').text().trim()
                let is_lainnya_on = $('#input_jenis_sarana_lainnya').prop('disabled') == false

                // # Jenis Sarana Lainnya
                if (value == "Lainnya" && !is_lainnya_on) {
                    $('#input_jenis_sarana_lainnya').prop('disabled', false).prop('hidden', false)
                } else if (value != 'Lainnya' && !!is_lainnya_on) {
                    $('#input_jenis_sarana_lainnya').prop('disabled', true).prop('hidden', true)
                }
            })
            // === END JENIS SARANA ===

            $('.tags').tagsInput({
                'width': '100%',
                'height': '75%',
                'interactive': true,
                'defaultText': 'Add More',
                'removeWithBackspace': true,
                'minChars': 0,
                'maxChars': 20, // if not provided there is no limit
                'placeholderColor': '#666666'
            });
            // Function to create badge HTML (shared function)
            function createResultBadge(value, type) {
                var badgeClass, icon, additionalIcon = '';

                if (type === 'danger') {
                    badgeClass = 'badge badge-danger font-weight-bold';
                    icon = '<i class="fa fa-times-circle mr-1"></i>';
                    additionalIcon = ' <i class="fa fa-exclamation-triangle ml-1"></i>';
                } else if (type === 'success') {
                    badgeClass = 'badge badge-success';
                    icon = '<i class="fa fa-check-circle mr-1"></i>';
                } else {
                    badgeClass = 'badge badge-secondary';
                    icon = '';
                }

                return '<span class="' + badgeClass + '" style="font-size: 14px; padding: 8px 12px;">' +
                    icon + value + additionalIcon + '</span>';
            }

            // Function to normalize string for comparison (like PHP version)
            function normalizeString(str) {
                if (!str) return '';

                // Remove HTML entities and spaces - EXACTLY like PHP (no toUpperCase)
                str = str.toString();
                str = str.replace(/&nbsp;/g, ' ');
                str = str.replace(/\s/g, ''); // Remove all whitespace
                // Do NOT convert to uppercase - PHP doesn't do it either

                return str;
            }

            // saveAll - Handle radio button click for offset_baku_mutu
            $(".offset_baku_mutu").click(function() {
                var value_offset_baku_mutu = $(this).attr('value') || $(this).val();

                var id = $(this).attr('id')
                // Extract ID from different possible formats
                if (id.indexOf('offset_baku_mutu_ya_') !== -1) {
                    id = id.substring(20, id.length);
                } else if (id.indexOf('offset_baku_mutu_tidak_') !== -1) {
                    id = id.substring(23, id.length);
                } else if (id.indexOf('offset_baku_mutu_default_') !== -1) {
                    id = id.substring(25, id.length);
                } else {
                    id = id.substring(17, id.length);
                }

                // Update hidden input for offset_baku_mutu
                var $hiddenInput = $('#offset_baku_mutu_' + id);
                if ($hiddenInput.length > 0) {
                    $hiddenInput.val(value_offset_baku_mutu);
                }

                var $ta = $("#result_method_" + id);
                var rawValue = $ta.val();
                var nilaiBm = $ta.attr('data-nilai-baku-mutu') || '';
                var numberFormat = $ta.attr('data-number-format') || 'en';

                if (typeof window.checkBakuMutu === 'function') {
                    var badge = window.checkBakuMutu(
                        rawValue,
                        $ta.attr('data-min') || '',
                        $ta.attr('data-max') || '',
                        $ta.attr('data-equal') || '',
                        value_offset_baku_mutu,
                        null,
                        '',
                        numberFormat,
                        nilaiBm
                    );
                    $("#result_output_method_" + id).html(badge || createResultBadge('-', 'secondary'));
                }
            })

            // Handle input change for result_method textarea
            $(".result_method").bind('input propertychange', function() {
                var id = $(this).attr('id')
                id = id.substring(14, id.length);

                var offset_baku_mutu = $('#offset_baku_mutu_' + id).val();
                if (!offset_baku_mutu || offset_baku_mutu === '') {
                    offset_baku_mutu = $('[name="offset_baku_mutu_' + id + '"]:checked').val();
                }

                var rawValue = this.value;
                var nilaiBm = $(this).attr('data-nilai-baku-mutu') || '';
                var numberFormat = $(this).attr('data-number-format') || 'en';

                if (typeof window.checkBakuMutu === 'function') {
                    var badge = window.checkBakuMutu(
                        rawValue,
                        $(this).attr('data-min') || '',
                        $(this).attr('data-max') || '',
                        $(this).attr('data-equal') || '',
                        offset_baku_mutu,
                        null,
                        '',
                        numberFormat,
                        nilaiBm
                    );
                    $("#result_output_method_" + id).html(badge || createResultBadge('-', 'secondary'));
                }
            })

            // Handle dropdown untuk is_option = 1
            $(document).on('change', '.result-dropdown', function() {
                var methodId = $(this).data('method-id');
                var selectedValue = $(this).val();
                var $textarea = $('#result_method_' + methodId);

                // Sync dropdown value ke textarea
                $textarea.val(selectedValue);

                var offset_baku_mutu = $('#offset_baku_mutu_' + methodId).val();
                if (!offset_baku_mutu || offset_baku_mutu === '') {
                    offset_baku_mutu = $('input[name="offset_baku_mutu_' + methodId + '"]:checked').val();
                }

                var nilaiBm = $textarea.attr('data-nilai-baku-mutu') || '';
                var numberFormat = $textarea.attr('data-number-format') || 'en';

                if (typeof window.checkBakuMutu === 'function') {
                    var badge = window.checkBakuMutu(
                        selectedValue,
                        $textarea.data('min'),
                        $textarea.data('max'),
                        $textarea.data('equal'),
                        offset_baku_mutu,
                        null,
                        '',
                        numberFormat,
                        nilaiBm
                    );
                    $("#result_output_method_" + methodId).html(badge || createResultBadge('-', 'secondary'));
                }
            });

            var CSRF_TOKEN = $('#csrf-token').val();

            // === PENGATURAN BAKU MUTU BERDASARKAN RUANGAN (KUALITAS UDARA) ===
            @php
                $isKualitasUdara = isset($sample->name_sample_type) &&
                    stripos($sample->name_sample_type, 'udara') !== false;
            @endphp
            @if ($isKualitasUdara)
                // Function untuk format nilai baku mutu ke HTML
                function formatNilaiBakuMutu(value) {
                    if (!value) return '-';
                    // Convert format sistem ke HTML untuk display
                    var formatted = value.toString();
                    formatted = formatted.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                    formatted = formatted.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                    formatted = formatted.replace(/≤/g, '&le;');
                    formatted = formatted.replace(/≥/g, '&ge;');
                    formatted = formatted.replace(/</g, '&lt;');
                    formatted = formatted.replace(/>/g, '&gt;');
                    formatted = formatted.replace(/±/g, '&plusmn;');
                    return formatted;
                }

                // Function untuk update baku mutu berdasarkan ruangan yang dipilih
                function updateBakuMutuByRuangan(ruanganNama) {
                    console.log('Updating baku mutu for ruangan:', ruanganNama);

                    if (!ruanganNama) {
                        // Jika ruangan tidak dipilih, gunakan baku mutu default
                        $('.result_method').each(function() {
                            var $textarea = $(this);
                            var methodId = $textarea.data('method-id');

                            // Reset ke baku mutu default (ambil dari data attribute original)
                            var originalMin = $textarea.attr('data-original-min');
                            var originalMax = $textarea.attr('data-original-max');
                            var originalEqual = $textarea.attr('data-original-equal');

                            if (originalMin !== undefined && originalMin !== null) {
                                $textarea.attr('data-min', originalMin);
                            }
                            if (originalMax !== undefined && originalMax !== null) {
                                $textarea.attr('data-max', originalMax);
                            }
                            if (originalEqual !== undefined && originalEqual !== null) {
                                $textarea.attr('data-equal', originalEqual);
                            }

                            // Update display nilai baku mutu
                            var originalNilaiBakuMutu = $textarea.attr('data-original-nilai-baku-mutu');
                            if (originalNilaiBakuMutu !== undefined && originalNilaiBakuMutu !== null) {
                                $('#nilai_baku_mutu_display_' + methodId).html(originalNilaiBakuMutu);
                                var plainBmOrig = $('<div>').html(originalNilaiBakuMutu || '').text().replace(/\s+/g, ' ').trim();
                                $textarea.attr('data-nilai-baku-mutu', plainBmOrig);
                            }

                            // Trigger input event untuk update preview hasil
                            $textarea.trigger('input');
                        });
                        return;
                    }

                    // Update baku mutu untuk setiap parameter berdasarkan ruangan
                    $('.result_method').each(function() {
                        var $textarea = $(this);
                        var methodId = $textarea.data('method-id');
                        var lokasiData = $textarea.data('lokasi-data');

                        if (!lokasiData) {
                            return; // Skip jika tidak ada lokasi data
                        }

                        try {
                            var lokasiArray = typeof lokasiData === 'string' ? JSON.parse(lokasiData) : lokasiData;

                            if (!Array.isArray(lokasiArray) || lokasiArray.length === 0) {
                                return; // Skip jika tidak ada lokasi data
                            }

                            // Simpan nilai original jika belum disimpan (cek dengan data attribute khusus)
                            if (!$textarea.attr('data-original-saved')) {
                                var currentMin = $textarea.data('min') || '';
                                var currentMax = $textarea.data('max') || '';
                                var currentEqual = $textarea.data('equal') || '';
                                var currentNilaiBakuMutu = $('#nilai_baku_mutu_display_' + methodId).html() || '-';

                                $textarea.attr('data-original-min', currentMin);
                                $textarea.attr('data-original-max', currentMax);
                                $textarea.attr('data-original-equal', currentEqual);
                                $textarea.attr('data-original-nilai-baku-mutu', currentNilaiBakuMutu);
                                $textarea.attr('data-original-saved', 'true');
                            }

                            // Cari lokasi yang sesuai
                            var selectedLokasi = null;
                            for (var i = 0; i < lokasiArray.length; i++) {
                                if (lokasiArray[i].nama && lokasiArray[i].nama.toLowerCase() === ruanganNama.toLowerCase()) {
                                    selectedLokasi = lokasiArray[i];
                                    break;
                                }
                            }

                            if (selectedLokasi) {
                                console.log('Found lokasi for method ' + methodId + ':', selectedLokasi);

                                // Update data attributes dengan nilai dari lokasi
                                $textarea.attr('data-min', selectedLokasi.min || '');
                                $textarea.attr('data-max', selectedLokasi.max || '');
                                $textarea.attr('data-equal', selectedLokasi.equal || '');

                                // Update display nilai baku mutu di kolom "Kadar Maksimum Yang diperbolehkan"
                                var nilaiBakuMutu = selectedLokasi.nilai_baku_mutu || '';
                                var formattedNilai = formatNilaiBakuMutu(nilaiBakuMutu);
                                $('#nilai_baku_mutu_display_' + methodId).html(formattedNilai);
                                var plainBm = $('<div>').html(nilaiBakuMutu || '').text().replace(/\s+/g, ' ').trim();
                                $textarea.attr('data-nilai-baku-mutu', plainBm);

                                console.log('Updated nilai baku mutu for method ' + methodId + ':', formattedNilai);

                                // Trigger input event untuk update preview hasil
                                $textarea.trigger('input');
                            } else {
                                console.log('No matching lokasi found for method ' + methodId + ' with ruangan:', ruanganNama);
                            }
                        } catch (e) {
                            console.error('Error updating baku mutu for method ' + methodId + ':', e);
                        }
                    });
                }

                // Handle perubahan dropdown ruangan
                $('#pilih_ruangan').on('change', function() {
                    var selectedRuangan = $(this).val();
                    console.log('Ruangan changed to:', selectedRuangan);
                    // Simpan ke hidden input untuk submit form
                    $('#selected_ruangan_hidden').val(selectedRuangan);
                    updateBakuMutuByRuangan(selectedRuangan);
                });

                // Initialize: Simpan nilai original saat halaman pertama kali dimuat
                $(document).ready(function() {
                    // Simpan nilai original untuk semua parameter yang punya lokasi_data
                    $('.result_method').each(function() {
                        var $textarea = $(this);
                        var methodId = $textarea.data('method-id');
                        var lokasiData = $textarea.data('lokasi-data');

                        if (lokasiData) {
                            // Simpan nilai original hanya jika belum disimpan
                            if (!$textarea.attr('data-original-saved')) {
                                var currentMin = $textarea.data('min') || '';
                                var currentMax = $textarea.data('max') || '';
                                var currentEqual = $textarea.data('equal') || '';
                                var currentNilaiBakuMutu = $('#nilai_baku_mutu_display_' + methodId).html() || '-';

                                $textarea.attr('data-original-min', currentMin);
                                $textarea.attr('data-original-max', currentMax);
                                $textarea.attr('data-original-equal', currentEqual);
                                $textarea.attr('data-original-nilai-baku-mutu', currentNilaiBakuMutu);
                                $textarea.attr('data-original-saved', 'true');
                            }
                        }
                    });

                    // Jika ada ruangan yang sudah dipilih, update baku mutu
                    var selectedRuangan = $('#pilih_ruangan').val();
                    if (selectedRuangan) {
                        updateBakuMutuByRuangan(selectedRuangan);
                    }
                });
            @endif

            // Auto-set baku mutu untuk MBI dan KIM ketika jenis makanan dipilih / berubah
            @if (
                ($lab->kode_laboratorium === 'MBI' || $lab->kode_laboratorium === 'KIM') &&
                    $isMMLType)
                $('#jenis_makanan_picker').select2({
                    placeholder: @json(($kimGenericJenisOption ?? false)
                        ? 'Tidak berdasarkan jenis makanan'
                        : 'Pilih jenis makanan'),
                    allowClear: false
                });
                // Saat user memilih jenis makanan, reload halaman dengan query ?jenis_makanan_id=...
                // Untuk KIM, jenisId bisa null/empty/__none__ (tanpa jenis makanan) hanya jika opsi generik tersedia
                $('#jenis_makanan_picker').on('change', function() {
                    var jenisId = $(this).val();
                    if (jenisId === '__new__') {
                        // Reset pilihan ke sebelumnya agar tidak muncul __new__ terpilih
                        var prevJm = @json(
                            ($selectedJenisMakananId === null || $selectedJenisMakananId === '' || $selectedJenisMakananId === '__none__')
                                ? (($kimGenericJenisOption ?? false) ? '__none__' : '')
                                : $selectedJenisMakananId
                        );
                        $(this).val(prevJm).trigger('change.select2');
                        // Reset modal fields
                        $('#new-jenis-makanan-name').val('');
                        $('#new-jenis-makanan-error').hide().text('');
                        $('#new-jm-input-group').hide();
                        $('#btn-show-new-jm-input').show();

                        // Kumpulkan ID yang sudah ada di dropdown utama
                        var existingIds = [];
                        $('#jenis_makanan_picker option').each(function() {
                            var v = $(this).val();
                            if (v && v !== '' && v !== '__new__' && v !== '__none__') existingIds.push(v);
                        });

                        // Rebuild SDD existing: hanya tampilkan yang belum ada di dropdown utama
                        var allJenisMakananData = @json(($allJenisMakanan ?? collect())->map(fn($j) => ['id' => $j->id_jenis_makanan, 'name' => $j->name_jenis_makanan]));
                        window._sddExistingJmData = [];
                        $.each(allJenisMakananData, function(i, jm) {
                            if (existingIds.indexOf(jm.id) === -1) {
                                window._sddExistingJmData.push(jm);
                            }
                        });
                        // Reset SDD state
                        $('#existing-jenis-makanan-select').val('');
                        $('#sdd-existing-jm-label').text('— Pilih jenis makanan —').addClass('text-muted');
                        $('#sdd-existing-jm-search').val('');
                        sddExistingJmRenderList('');

                        // Tampilkan/sembunyikan grup berdasarkan ada tidaknya opsi
                        var addedCount = window._sddExistingJmData.length;
                        if (addedCount > 0) {
                            $('#existing-jm-group').show();
                            $('#modal-tambah-jenis-makanan .text-center.my-2').show();
                        } else {
                            $('#existing-jm-group').hide();
                            $('#modal-tambah-jenis-makanan .text-center.my-2').hide();
                        }

                        $('#modal-tambah-jenis-makanan').modal('show');
                        return;
                    }
                    var url = new URL(window.location.href);
                    if (jenisId && jenisId !== '' && jenisId !== '__none__') {
                        url.searchParams.set('jenis_makanan_id', jenisId);
                    } else if (jenisId === '__none__') {
                        url.searchParams.set('jenis_makanan_id', '__none__');
                    } else {
                        url.searchParams.delete('jenis_makanan_id');
                    }
                    if (typeof window.persistBacaHasilFormDraftBeforeJenisNav === 'function') {
                        window.persistBacaHasilFormDraftBeforeJenisNav();
                    }
                    window.location.href = url.toString();
                });

                // Tampilkan input nama baru saat tombol diklik
                $(document).on('click', '#btn-show-new-jm-input', function() {
                    $(this).hide();
                    $('#new-jm-input-group').slideDown(200);
                    $('#new-jenis-makanan-name').focus();
                });

                // SDD existing jenis makanan: render list
                function sddExistingJmRenderList(query) {
                    var $list = $('#sdd-existing-jm-list');
                    $list.empty();
                    var q = (query || '').toLowerCase().trim();
                    var data = window._sddExistingJmData || [];
                    var filtered = q ? data.filter(function(jm){ return jm.name.toLowerCase().indexOf(q) !== -1; }) : data;
                    if (filtered.length === 0) {
                        $list.append('<div class="px-3 py-2 text-muted small">Tidak ada hasil</div>');
                        return;
                    }
                    $.each(filtered, function(i, jm) {
                        $list.append(
                            $('<div>').addClass('sdd-item px-3 py-2')
                                .css({ cursor:'pointer', fontSize:'14px', borderBottom:'1px solid #f0f0f0' })
                                .attr('data-id', jm.id).attr('data-name', jm.name)
                                .text(jm.name)
                                .hover(function(){ $(this).css('background','#f0f7ff'); }, function(){ $(this).css('background',''); })
                        );
                    });
                }

                // SDD toggle panel
                $(document).on('click', '#sdd-existing-jm-display', function(e) {
                    e.stopPropagation();
                    var $panel = $('#sdd-existing-jm-panel');
                    if ($panel.is(':visible')) {
                        $panel.hide();
                    } else {
                        $panel.css('display', 'flex').show();
                        $('#sdd-existing-jm-search').val('').focus();
                        sddExistingJmRenderList('');
                    }
                });

                // SDD search
                $(document).on('input', '#sdd-existing-jm-search', function() {
                    sddExistingJmRenderList($(this).val());
                });

                // SDD select item
                $(document).on('click', '.sdd-item', function(e) {
                    var $item = $(this);
                    if (!$item.closest('#sdd-existing-jm-list').length) return;
                    var id   = $item.data('id');
                    var name = $item.data('name');
                    $('#existing-jenis-makanan-select').val(id);
                    $('#sdd-existing-jm-label').text(name).removeClass('text-muted');
                    $('#sdd-existing-jm-panel').hide();
                });

                // Tutup SDD ketika klik di luar
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#sdd-existing-jm').length) {
                        $('#sdd-existing-jm-panel').hide();
                    }
                });

                // Helper: navigate ke URL dengan jenis_makanan_id baru
                function navigateToJenisMakanan(jenisId) {
                    var url = new URL(window.location.href);
                    if (jenisId && jenisId !== '' && jenisId !== '__none__') {
                        url.searchParams.set('jenis_makanan_id', jenisId);
                    } else if (jenisId === '__none__') {
                        url.searchParams.set('jenis_makanan_id', '__none__');
                    } else {
                        url.searchParams.delete('jenis_makanan_id');
                    }
                    if (typeof window.persistBacaHasilFormDraftBeforeJenisNav === 'function') {
                        window.persistBacaHasilFormDraftBeforeJenisNav();
                    }
                    window.location.href = url.toString();
                }

                // Simpan / pilih jenis makanan
                $(document).on('click', '#btn-save-jenis-makanan', function() {
                    var existingId   = $('#existing-jenis-makanan-select').val();
                    var existingName = $('#sdd-existing-jm-label').text();
                    if (existingName === '— Pilih jenis makanan —') existingName = '';
                    var newName      = $('#new-jenis-makanan-name').val().trim();

                    // Prioritas: text input (buat baru) > dropdown (pilih existing)
                    if (newName) {
                        // Buat jenis makanan baru lalu reload
                        var csrfToken = $('#csrf-token').val() || $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
                        var $btn = $(this);
                        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');
                        $.ajax({
                            url: '{{ route("elits-jenis-makanan.store") }}',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            data: { _token: csrfToken, name_jenis_makanan: newName },
                            success: function(res) {
                                if (res && res.status && res.id) {
                                    $('#modal-tambah-jenis-makanan').modal('hide');
                                    navigateToJenisMakanan(res.id);
                                } else {
                                    $('#new-jenis-makanan-error').text('Gagal menyimpan, silakan coba lagi.').show();
                                    $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Pilih / Simpan');
                                }
                            },
                            error: function(xhr) {
                                var msg = 'Terjadi kesalahan saat menyimpan.';
                                if (xhr.status === 419) {
                                    msg = 'Sesi telah berakhir. Silakan refresh halaman.';
                                } else if (xhr.responseJSON) {
                                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.name_jenis_makanan) {
                                        msg = xhr.responseJSON.errors.name_jenis_makanan[0];
                                    } else if (xhr.responseJSON.message) {
                                        msg = xhr.responseJSON.message;
                                    }
                                }
                                $('#new-jenis-makanan-error').text(msg).show();
                                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Pilih / Simpan');
                            }
                        });
                    } else if (existingId) {
                        // Pilih jenis makanan existing → langsung reload dengan ID tersebut
                        $('#modal-tambah-jenis-makanan').modal('hide');
                        navigateToJenisMakanan(existingId);
                    } else {
                        $('#new-jenis-makanan-error').text('Pilih dari daftar atau isi nama baru.').show();
                    }
                });
            @endif

            // TinyMCE untuk Titik Sampel (titik_pengambilan)
            if (tinymce.get('titik_pengambilan')) {
                tinymce.get('titik_pengambilan').remove();
            }
            // Ensure base URL is set before init (gunakan lokal)
            if (typeof tinymce !== 'undefined') {
                var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                if (tinymce.baseURL === undefined ||
                    tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
                    tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                }
            }
            tinymce.init({
                selector: 'textarea#titik_pengambilan',
                height: 150,
                menubar: false,
                theme: 'modern',
                plugins: [
                    'lists',
                    'help',
                    'wordcount'
                ],
                toolbar: 'undo redo | bold italic | bullist numlist | removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                setup: function(editor) {
                    editor.on('change blur', function() {
                        tinymce.triggerSave();
                    });
                }
            });

            /** Sinkronkan dropdown hasil + editor TinyMCE hasil/keterangan ke textarea (submit & draft ganti jenis makanan). */
            function syncBacaHasilResultAndKeteranganEditorsToTextareas() {
                $('.result-dropdown').each(function() {
                    var methodId = $(this).data('method-id');
                    var selectedValue = $(this).val();
                    var $textarea = $('#result_method_' + methodId);
                    if ($textarea.length) {
                        $textarea.val(selectedValue);
                    }
                });

                if (typeof tinymce !== 'undefined') {
                    $('.inline-hasil-editor').each(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        var textareaId = $editor.data('textarea-id');
                        var index = $editor.data('index');

                        if (editorId) {
                            try {
                                var editor = tinymce.get(editorId);
                                var content = '';

                                if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                                    content = editor.getContent();
                                    editor.save();
                                } else {
                                    content = $editor.html();
                                }

                                if (content && (content.includes('^(') || content.includes('_('))) {
                                    if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                                        content = AnalisInlineEditor.convertSuperscriptToHtml(content);
                                    } else if (typeof toFormatHtml === 'function') {
                                        content = toFormatHtml(content);
                                    }
                                }

                                var $textarea = null;
                                if (textareaId) {
                                    $textarea = $('#' + textareaId);
                                }
                                if ((!$textarea || $textarea.length === 0) && index) {
                                    $textarea = $('#result_method_' + index);
                                }

                                if ($textarea && $textarea.length > 0) {
                                    $textarea.val(content);
                                }
                            } catch (e) {
                                console.warn('Error syncing hasil editor:', editorId, e);
                            }
                        }
                    });
                }

                if (typeof tinymce !== 'undefined') {
                    $('.inline-keterangan-editor').each(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        var textareaId = $editor.data('textarea-id');

                        if (editorId && textareaId) {
                            try {
                                var editor = tinymce.get(editorId);
                                var content = '';

                                if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                                    content = editor.getContent();
                                    editor.save();
                                } else {
                                    content = $editor.html();
                                }

                                if (content && (content.includes('^(') || content.includes('_('))) {
                                    if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                                        content = AnalisInlineEditor.convertSuperscriptToHtml(content);
                                    } else if (typeof toFormatHtml === 'function') {
                                        content = toFormatHtml(content);
                                    }
                                }

                                var $textarea = $('#' + textareaId);
                                if ($textarea.length === 0) {
                                    var editorIndex = editorId.replace('keterangan_editor_', '');
                                    $textarea = $('textarea[name="keterangan_' + editorIndex + '"]');
                                    if ($textarea.length === 0) {
                                        $textarea = $('textarea[name="keterangan_param_' + editorIndex + '"]');
                                    }
                                    if ($textarea.length === 0) {
                                        $textarea = $('textarea[name="keterangan_sub_' + editorIndex + '"]');
                                    }
                                }

                                if ($textarea.length > 0) {
                                    $textarea.val(content);
                                }
                            } catch (e) {
                                console.warn('Error syncing keterangan editor:', editorId, e);
                                var content = $editor.html();
                                if (content && (content.includes('^(') || content.includes('_('))) {
                                    if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                                        content = AnalisInlineEditor.convertSuperscriptToHtml(content);
                                    } else if (typeof toFormatHtml === 'function') {
                                        content = toFormatHtml(content);
                                    }
                                }
                                var $textarea = $('#' + textareaId);
                                if ($textarea.length > 0) {
                                    $textarea.val(content);
                                }
                            }
                        }
                    });
                }

                if (typeof tinymce !== 'undefined') {
                    $('.inline-metode-editor').each(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        var originalId = $editor.data('original-id');

                        if (!editorId || !originalId) {
                            return;
                        }

                        try {
                            var editor = tinymce.get(editorId);
                            var content = '';

                            if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                                content = editor.getContent();
                            } else {
                                content = $editor.html();
                            }

                            var $textarea = $('#' + originalId);
                            if ($textarea.length > 0) {
                                $textarea.val(content);
                            }
                        } catch (e) {
                            console.warn('Error syncing metode editor:', editorId, e);
                        }
                    });
                }
            }

            window.bacaHasilFormDraftStorageKey = function() {
                return 'baca_hasil_draft_v1:' + window.location.pathname;
            };

            window.persistBacaHasilFormDraftBeforeJenisNav = function() {
                try {
                    syncBacaHasilResultAndKeteranganEditorsToTextareas();
                    if (typeof tinymce !== 'undefined' && typeof tinymce.triggerSave === 'function') {
                        tinymce.triggerSave();
                    }
                    var draft = { v: 1, ts: Date.now(), textareas: {}, inputs: {}, selects: {}, checkboxes: {} };
                    var skipSelectIds = { jenis_makanan_picker: true };
                    $('#form-baca-hasil textarea').each(function() {
                        if (this.id) {
                            draft.textareas[this.id] = $(this).val() || '';
                        }
                    });
                    $('#form-baca-hasil input[type="checkbox"]').each(function() {
                        if (this.id) {
                            draft.checkboxes[this.id] = this.checked;
                        }
                    });
                    $('#form-baca-hasil input').each(function() {
                        var t = this.type;
                        if (t === 'submit' || t === 'button' || t === 'file' || t === 'checkbox' || t === 'radio') {
                            return;
                        }
                        var id = this.id;
                        if (!id || id === 'csrf-token') {
                            return;
                        }
                        draft.inputs[id] = $(this).val() || '';
                    });
                    $('#form-baca-hasil select').each(function() {
                        var id = this.id;
                        if (id && skipSelectIds[id]) {
                            return;
                        }
                        if (id) {
                            draft.selects[id] = $(this).val() || '';
                        }
                    });
                    sessionStorage.setItem(window.bacaHasilFormDraftStorageKey(), JSON.stringify(draft));
                } catch (e) {
                    console.warn('persistBacaHasilFormDraftBeforeJenisNav:', e);
                }
            };

            window.persistBacaHasilFormDraft = window.persistBacaHasilFormDraftBeforeJenisNav;

            window.restoreBacaHasilFormDraftIfAny = function() {
                try {
                    var raw = sessionStorage.getItem(window.bacaHasilFormDraftStorageKey());
                    if (!raw) {
                        return;
                    }
                    var draft = JSON.parse(raw);
                    if (!draft || draft.v !== 1) {
                        sessionStorage.removeItem(window.bacaHasilFormDraftStorageKey());
                        return;
                    }
                    sessionStorage.removeItem(window.bacaHasilFormDraftStorageKey());

                    if (draft.inputs && typeof draft.inputs === 'object') {
                        Object.keys(draft.inputs).forEach(function(id) {
                            var $el = $('#' + id);
                            if ($el.length && $el.closest('#form-baca-hasil').length) {
                                $el.val(draft.inputs[id]);
                            }
                        });
                    }
                    if (draft.textareas && typeof draft.textareas === 'object') {
                        Object.keys(draft.textareas).forEach(function(id) {
                            var $el = $('#' + id);
                            if ($el.length && $el.is('textarea') && $el.closest('#form-baca-hasil').length) {
                                $el.val(draft.textareas[id]);
                            }
                        });
                    }
                    var skipRestoreSelectIds = { jenis_makanan_picker: true };
                    if (draft.selects && typeof draft.selects === 'object') {
                        Object.keys(draft.selects).forEach(function(id) {
                            if (skipRestoreSelectIds[id]) {
                                return;
                            }
                            var $el = $('#' + id);
                            if ($el.length && $el.is('select') && $el.closest('#form-baca-hasil').length) {
                                $el.val(draft.selects[id]).trigger('change.select2');
                            }
                        });
                    }
                    if (draft.checkboxes && typeof draft.checkboxes === 'object') {
                        Object.keys(draft.checkboxes).forEach(function(id) {
                            var $el = $('#' + id);
                            if ($el.length && $el.is(':checkbox') && $el.closest('#form-baca-hasil').length) {
                                $el.prop('checked', !!draft.checkboxes[id]);
                            }
                        });
                    }

                    var startEl = document.getElementById('start_date_verifikasi_baca_hasil');
                    var stopEl = document.getElementById('stop_date_verifikasi_baca_hasil');
                    if (startEl && startEl._flatpickr && draft.inputs && draft.inputs.start_date_verifikasi_baca_hasil) {
                        startEl._flatpickr.setDate(draft.inputs.start_date_verifikasi_baca_hasil, false);
                    }
                    if (stopEl && stopEl._flatpickr && draft.inputs && draft.inputs.stop_date_verifikasi_baca_hasil) {
                        stopEl._flatpickr.setDate(draft.inputs.stop_date_verifikasi_baca_hasil, false);
                    }

                    function applyTinymceContent(id, html) {
                        if (html === undefined || html === null) {
                            return;
                        }
                        if (typeof tinymce === 'undefined') {
                            return;
                        }
                        var ed = tinymce.get(id);
                        if (ed && !ed.removed) {
                            ed.setContent(html);
                        }
                    }
                    if (draft.textareas) {
                        applyTinymceContent('lokasi_pengambilan', draft.textareas.lokasi_pengambilan);
                        applyTinymceContent('lokasi_pengambilan_kimia', draft.textareas.lokasi_pengambilan_kimia);
                        applyTinymceContent('titik_pengambilan', draft.textareas.titik_pengambilan);
                        applyTinymceContent('tembusan', draft.textareas.tembusan);
                        applyTinymceContent('nama_jenis_makanan', draft.textareas.nama_jenis_makanan);
                    }

                    if (typeof tinymce !== 'undefined') {
                        $('.inline-hasil-editor').each(function() {
                            var $ed = $(this);
                            var tid = $ed.data('textarea-id');
                            var eid = $ed.attr('id');
                            if (!tid || !eid) {
                                return;
                            }
                            var v = $('#' + tid).val() || '';
                            var inst = tinymce.get(eid);
                            if (inst && !inst.removed) {
                                inst.setContent(v);
                            }
                        });
                        $('.inline-keterangan-editor').each(function() {
                            var $ed = $(this);
                            var tid = $ed.data('textarea-id');
                            var eid = $ed.attr('id');
                            if (!tid || !eid) {
                                return;
                            }
                            var $ta = $('#' + tid);
                            if (!$ta.length) {
                                var editorIndex = eid.replace('keterangan_editor_', '');
                                $ta = $('textarea[name="keterangan_' + editorIndex + '"]');
                                if (!$ta.length) {
                                    $ta = $('textarea[name="keterangan_param_' + editorIndex + '"]');
                                }
                            }
                            var v = ($ta.length ? $ta.val() : '') || '';
                            var inst = tinymce.get(eid);
                            if (inst && !inst.removed) {
                                inst.setContent(v);
                            }
                        });
                    }

                    if (typeof AnalisInlineEditor !== 'undefined' && AnalisInlineEditor.updateResultBadge) {
                        $('textarea.result_method_klinik').each(function() {
                            var $ta = $(this);
                            var idx = $ta.attr('data-index');
                            if (!idx) {
                                return;
                            }
                            var val = $ta.val() || '';
                            if (!val || val === '-') {
                                return;
                            }
                            AnalisInlineEditor.updateResultBadge(
                                idx,
                                val,
                                $ta.data('min'),
                                $ta.data('max'),
                                $ta.data('equal'),
                                $ta.data('number-format') || 'en'
                            );
                        });
                    }
                } catch (e) {
                    console.warn('restoreBacaHasilFormDraftIfAny:', e);
                    try {
                        sessionStorage.removeItem(window.bacaHasilFormDraftStorageKey());
                    } catch (e2) { /* ignore */ }
                }
            };

            function submitBacaHasilFinal() {

                // Validasi dan sync data verifikasi baca hasil ke hidden inputs
                var startDate = $('#start_date_verifikasi_baca_hasil').val();
                var stopDate = $('#stop_date_verifikasi_baca_hasil').val();
                var namaPetugas = $('#nama_petugas_verifikasi_baca_hasil').val();

                if (!startDate || !stopDate || !namaPetugas) {
                    swal({
                        title: "Perhatian!",
                        text: "Mohon lengkapi data Verifikasi Baca Hasil (Start Date, Stop Date, dan Nama Petugas) sebelum menyelesaikan.",
                        icon: "warning"
                    });
                    return false;
                }

                // Konversi format tanggal dari Flatpickr (d/m/Y H:i) ke format yang diharapkan backend
                var startDateInput = document.getElementById('start_date_verifikasi_baca_hasil');
                var stopDateInput = document.getElementById('stop_date_verifikasi_baca_hasil');

                if (startDateInput && startDateInput._flatpickr) {
                    var startDateFormatted = startDateInput._flatpickr.formatDate(startDateInput._flatpickr.selectedDates[0], 'd/m/Y');
                    $('#verification_start_date_hidden').val(startDateFormatted);
                } else {
                    $('#verification_start_date_hidden').val(startDate);
                }

                if (stopDateInput && stopDateInput._flatpickr) {
                    var stopDateFormatted = stopDateInput._flatpickr.formatDate(stopDateInput._flatpickr.selectedDates[0], 'd/m/Y');
                    $('#verification_stop_date_hidden').val(stopDateFormatted);
                } else {
                    $('#verification_stop_date_hidden').val(stopDate);
                }

                $('#verification_nama_petugas_hidden').val(namaPetugas);

                syncBacaHasilResultAndKeteranganEditorsToTextareas();

                // Ensure all offset_baku_mutu hidden inputs are included in form submission
                // Sync offset values from all hidden inputs to ensure they're submitted
                var offsetInputsFound = 0;
                $('input[id^="offset_baku_mutu_"], input[name^="offset_baku_mutu_"]').each(function() {
                    var $input = $(this);
                    var id = $input.attr('id');
                    var name = $input.attr('name');
                    var value = $input.val();
                    var isInForm = $input.closest('#form-baca-hasil').length > 0;

                    console.log('Offset input before submit:', {
                        id: id,
                        name: name,
                        value: value,
                        isInForm: isInForm
                    });

                    offsetInputsFound++;

                    // Ensure the input is inside the form
                    if (!isInForm) {
                        // If not in form, clone and add to form
                        var $clone = $input.clone();
                        $('#form-baca-hasil').append($clone);
                        console.log('Cloned offset input to form:', id);
                    }
                });

                console.log('Total offset inputs found:', offsetInputsFound);

                // Convert all textarea values that contain ^( notation before submit
                $('#form-baca-hasil textarea').each(function() {
                    var $textarea = $(this);
                    var value = $textarea.val() || '';

                    // Check if value contains ^( or _( notation that needs conversion
                    if (value && (value.includes('^(') || value.includes('_('))) {
                        // Convert using toFormatHtml or convertSuperscriptToHtml
                        var convertedValue = value;
                        if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                            convertedValue = AnalisInlineEditor.convertSuperscriptToHtml(value);
                        } else if (typeof toFormatHtml === 'function') {
                            convertedValue = toFormatHtml(value);
                        }

                        // Only update if conversion changed the value
                        if (convertedValue !== value) {
                            $textarea.val(convertedValue);
                            console.log('Converted textarea value before submit:', $textarea.attr('id') || $textarea.attr('name'), 'from:', value.substring(0, 30), 'to:', convertedValue.substring(0, 30));
                        }
                    }
                });

                // Also log all form data that will be submitted
                var formData = $('#form-baca-hasil').serializeArray();
                var offsetData = formData.filter(function(item) {
                    return item.name.indexOf('offset_baku_mutu_') !== -1;
                });
                console.log('Offset data in form submission:', offsetData);

                $('#submitAll').text('Proses....'); //change button text
                $('#submitAll').addClass('disabled'); //set button disable

                // Submit form baca hasil (yang sudah include data verifikasi di hidden inputs)
                $('#form-baca-hasil').ajaxSubmit({
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = response.url_redirect;
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    console.log(value);
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

                        $('#submitAll').text('Selesai'); //change button text
                        $('#submitAll').removeClass('disabled'); //set button disable
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");

                        swal({
                            type: 'error',
                            title: 'Gagal!',
                            text: err.Message
                        });

                        $('#submitAll').text('Selesai'); //change button text
                        $('#submitAll').removeClass('disabled'); //set button disable
                    }
                })
            }

            $('#submitAll').on('click', function(e) {
                e.preventDefault();
                if (!guardMissingBakuMutuBeforeSubmit()) return;
                triggerDirectPreview(true);
            });

            $('#btn-preview-lanjut-selesai').on('click', function() {
                $('#modalPreviewHasil').modal('hide');
                submitBacaHasilFinal();
            });

            // Tombol "Pengaturan Hasil" di dalam preview: sembunyikan preview lalu buka modal pengaturan
            $('#btn-pengaturan-preview').on('click', function() {
                var modeSelesai = $('#modalPreviewHasil').data('mode-selesai') || false;
                // Tandai bahwa preview perlu dibuka kembali setelah settings ditutup
                $('#modalReviewHasil').data('reopen-preview', true);
                $('#modalReviewHasil').data('mode-selesai', modeSelesai);
                // Sembunyikan preview dulu, baru buka settings setelah preview benar-benar tersembunyi
                $('#modalPreviewHasil').one('hidden.bs.modal', function() {
                    $('#modalReviewHasil').modal('show');
                });
                $('#modalPreviewHasil').modal('hide');
            });

            $("#saveAll").on('click', function() {
                if (!guardMissingBakuMutuBeforeSubmit()) return;
                $('#saveAll').text('Proses menyimpan....'); //change button text
                $('#saveAll').addClass('disabled'); //set button disable

                // Sync semua TinyMCE editor keterangan ke textarea sebelum submit
                if (typeof tinymce !== 'undefined') {
                    $('.inline-keterangan-editor').each(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        var textareaId = $editor.data('textarea-id');

                        if (editorId && textareaId) {
                            try {
                                var editor = tinymce.get(editorId);
                                var content = '';

                                if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                                    content = editor.getContent();
                                    // Save editor content to textarea
                                    editor.save();
                                } else {
                                    // Fallback: use HTML content from div
                                    content = $editor.html();
                                }

                                // Convert any remaining ^( notation to HTML (remove parentheses)
                                if (content && (content.includes('^(') || content.includes('_('))) {
                                    // Use convertSuperscriptToHtml if available, otherwise use toFormatHtml
                                    if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                                        content = AnalisInlineEditor.convertSuperscriptToHtml(content);
                                    } else if (typeof toFormatHtml === 'function') {
                                        content = toFormatHtml(content);
                                    }
                                }

                                // Ensure textarea exists and update value
                                var $textarea = $('#' + textareaId);
                                if ($textarea.length === 0) {
                                    // Try to find by name attribute (fallback)
                                    var editorIndex = editorId.replace('keterangan_editor_', '');
                                    // Try multiple name patterns
                                    $textarea = $('textarea[name="keterangan_' + editorIndex + '"]');
                                    if ($textarea.length === 0) {
                                        $textarea = $('textarea[name="keterangan_param_' + editorIndex + '"]');
                                    }
                                    if ($textarea.length === 0) {
                                        $textarea = $('textarea[name="keterangan_sub_' + editorIndex + '"]');
                                    }
                                }

                                if ($textarea.length > 0) {
                                    $textarea.val(content);
                                    console.log('Synced keterangan editor:', editorId, 'to textarea:', $textarea.attr('id') || $textarea.attr('name'), 'content length:', content.length);
                                } else {
                                    console.error('Textarea not found for keterangan editor:', editorId, 'textareaId:', textareaId);
                                }
                            } catch(e) {
                                console.warn('Error syncing keterangan editor:', editorId, e);
                                // Fallback: use HTML content from div
                                var content = $editor.html();
                                // Convert any remaining ^( notation
                                if (content && (content.includes('^(') || content.includes('_('))) {
                                    if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                                        content = AnalisInlineEditor.convertSuperscriptToHtml(content);
                                    } else if (typeof toFormatHtml === 'function') {
                                        content = toFormatHtml(content);
                                    }
                                }
                                var $textarea = $('#' + textareaId);
                                if ($textarea.length > 0) {
                                    $textarea.val(content);
                                }
                            }
                        } else {
                            console.warn('Keterangan editor missing ID or textarea-id:', {editorId: editorId, textareaId: textareaId});
                        }
                    });
                }

                var data = $('.form').serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});

                var url =
                    "{{ route('elits-baca-hasil.save', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = response.url_redirect;
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    console.log(value);
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

                        $('#saveAll').text('Simpan'); //change button text
                        $('#saveAll').removeClass('disabled'); //set button disable
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");

                        swal({
                            type: 'error',
                            title: 'Gagal!',
                            text: err.Message
                        });

                        $('#saveAll').text('Simpan'); //change button text
                        $('#saveAll').removeClass('disabled'); //set button disable
                    }
                });
            });

            (function() {
                @php
                    $kode = $lab->kode_laboratorium ?? '';
                    $stName = $sample->name_sample_type ?? '';
                    $_isMMLType = str_contains($stName, 'Makanan')
                        || str_contains($stName, 'Minuman')
                        || str_contains($stName, 'Lainnya');

                    // UUID tetap untuk Air Bersih / Air Higiene (AB) dan Air Minum (AM)
                    $_AB_UUID = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';
                    $_AM_UUID = '65df8403-b29f-4645-a1ed-12d2aeff1fbd';
                    $_isAbAm  = in_array($sampletype_id, [$_AB_UUID, $_AM_UUID]);

                    if ($kode === 'MBI') {
                        if ($_isAbAm) {
                            // Air Bersih / Air Minum: gabungkan semua sampel AB+AM dalam permohonan ini
                            $_labIdMbi = $lab->id_laboratorium;
                            $_combinedIds = \DB::table('tb_samples')
                                ->select('id_samples')
                                ->where('permohonan_uji_id', $sample->permohonan_uji_id)
                                ->whereIn('typesample_samples', [$_AB_UUID, $_AM_UUID])
                                ->whereNull('deleted_at')
                                ->whereIn('id_samples', function ($sub) use ($_labIdMbi) {
                                    $sub->select('sample_id')
                                        ->from('tb_sample_method')
                                        ->where('laboratorium_id', $_labIdMbi)
                                        ->whereNull('deleted_at');
                                })
                                ->orderBy('count_id', 'asc')
                                ->pluck('id_samples')
                                ->toArray();

                            // Pakai jenis sampel aktual (bukan selalu AB).
                            // URL dengan AB UUID saja 404 jika permohonan hanya punya Air Minum.
                            // printSamples[] tetap mengirim AB+AM; controller naik ke format gabungan
                            // bila jumlah printSamples > jumlah baris jenis aktual.
                            $_previewPrintUrl = route('elits-release.print-mikro', [
                                $sample->permohonan_uji_id,
                                $sampletype_id,
                            ]);
                            if (!empty($_combinedIds)) {
                                $_qs = implode('&', array_map(function ($sid) {
                                    return 'printSamples[]=' . rawurlencode($sid);
                                }, $_combinedIds));
                                $_previewPrintUrl .= '?' . $_qs;
                            }
                        } else {
                            // MBI non-AB/AM (Makanan/Minuman/Lainnya, dll): preview per permohonan+sampletype
                            $_previewPrintUrl = route('elits-release.print-mikro', [
                                $sample->permohonan_uji_id,
                                $sampletype_id,
                            ]);
                        }
                    } elseif ($kode === 'KIM' && $_isMMLType) {
                        // Kimia Makanan/Minuman/Lainnya: preview per permohonan+sampletype
                        $_previewPrintUrl = route('elits-release.print-kimia', [
                            $sample->permohonan_uji_id,
                            $sampletype_id,
                        ]);
                    } else {
                        // KIM non-makmin (Air Minum, Air Higiene / Air Bersih, dll) & lab lain:
                        // Gunakan printLHU per-sampel sesuai tampilan di validasi
                        $_previewPrintUrl = route('elits-release.printLHU', [
                            $sample->id_samples,
                            $lab->id_laboratorium,
                        ]);
                    }
                    // Sertakan jenis_makanan_id jika ada (hanya relevan untuk MBI non-AB/AM atau KIM makmin)
                    if (!empty($jenis_makanan_id) && !$_isAbAm && ($kode === 'MBI' || ($kode === 'KIM' && $_isMMLType))) {
                        $_sepJm = (strpos($_previewPrintUrl, '?') !== false) ? '&' : '?';
                        $_previewPrintUrl .= $_sepJm . 'jenis_makanan_id=' . urlencode($jenis_makanan_id);
                    }
                @endphp
                var previewUrl = '{!! $_previewPrintUrl !!}';
                var saveSettingUrl = '{{ route('elits-baca-hasil.save-fontsize-hasil', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}';
                var saveDataUrl   = '{{ route('elits-baca-hasil.save', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}';
                var csrfToken = '{{ csrf_token() }}';
                var $slider = $('#fontsize-slider');
                var $input = $('#fontsize-input');
                var $preview = $('#fontsize-preview-sample');
                var $lhSlider = $('#lineheight-slider');
                var $lhInput = $('#lineheight-input');
                var $lhPreview = $('#lineheight-preview-sample');
                var $pdSlider = $('#padding-slider');
                var $pdInput = $('#padding-input');
                var $toggleKop = $('#toggle-kop');
                var $kopLabel = $('#kop-label-text');
                var $btnBuka = $('#btn-buka-review');
                var $loadingIcon = $('#review-loading-icon');
                var $saveIcon = $('#review-save-icon');

                var currentFontsize = parseFloat($('#fontsize_hasil_hidden').val()) || 12;
                var currentLineHeight = parseFloat($('#line_height_hasil_hidden').val()) || 1;
                var currentPadding = parseFloat($('#padding_hasil_hidden').val()) || 1;
                var currentShowKop = ($('#show_kop_hasil_hidden').val() === '1') ? 1 : 0;

                function getColumnWidthsPayload() {
                    if (window.KesmasColWidths && typeof window.KesmasColWidths.collect === 'function') {
                        return window.KesmasColWidths.collect();
                    }
                    try {
                        return JSON.parse($('#column_widths_hasil_hidden').val() || '{}');
                    } catch (e) {
                        return {};
                    }
                }

                function updateFontsizeUI(val) {
                    val = Math.min(20, Math.max(6, parseFloat(val) || 12));
                    val = Math.round(val * 2) / 2;
                    currentFontsize = val;
                    $slider.val(val);
                    $input.val(val);
                    $preview.css('font-size', val + 'pt');
                    $('#fontsize_hasil_hidden').val(val);
                }

                function updateLineHeightUI(val) {
                    val = Math.min(3.0, Math.max(0.5, parseFloat(val) || 1.0));
                    val = Math.round(val * 10) / 10;
                    currentLineHeight = val;
                    $lhSlider.val(val);
                    $lhInput.val(val);
                    $lhPreview.css('line-height', val);
                    $('#line_height_hasil_hidden').val(val);
                }

                function updatePaddingUI(val) {
                    val = Math.min(16, Math.max(0, parseFloat(val)));
                    val = isNaN(val) ? 1 : val;
                    val = Math.round(val * 2) / 2;
                    currentPadding = val;
                    $pdSlider.val(val);
                    $pdInput.val(val);
                    $('#padding_hasil_hidden').val(val);
                }

                function updateKopUI(checked) {
                    currentShowKop = checked ? 1 : 0;
                    $kopLabel.text(checked ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)');
                    $('#show_kop_hasil_hidden').val(currentShowKop);
                }

                // Langsung simpan data + setting lalu tampilkan preview (tanpa membuka modal pengaturan dulu)
                function triggerDirectPreview(modeSelesai) {
                    var $btnSelesai = $('#submitAll');
                    var $btnReview  = $('#btn-open-review-hasil');
                    $btnSelesai.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Memproses...');
                    $btnReview.prop('disabled', true);

                    // Sync semua TinyMCE editor sebelum simpan
                    if (typeof tinymce !== 'undefined') {
                        if (typeof syncBacaHasilResultAndKeteranganEditorsToTextareas === 'function') {
                            syncBacaHasilResultAndKeteranganEditorsToTextareas();
                        }
                        tinymce.triggerSave();
                        $('.inline-hasil-editor, .inline-keterangan-editor').each(function() {
                            var $ed = $(this);
                            var editorId = $ed.attr('id');
                            var textareaId = $ed.data('textarea-id');
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
                            } catch(e) { /* ignore */ }
                        });
                    }

                    function resetButtons() {
                        $btnSelesai.prop('disabled', false).html('<i class="fa fa-check mr-2"></i>Selesai');
                        $btnReview.prop('disabled', false);
                    }

                    var formData = $('.form').serializeArray().reduce(function(obj, item) {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});

                    // Langkah 1: simpan data hasil
                    $.ajax({
                        url: saveDataUrl,
                        method: 'POST',
                        data: $.extend(formData, { _token: csrfToken }),
                        success: function(resp) {
                            if (resp && resp.status == true) {
                                // Langkah 2: simpan pengaturan
                                $.ajax({
                                    url: saveSettingUrl,
                                    method: 'POST',
                                    data: {
                                        _token: csrfToken,
                                        fontsize: currentFontsize,
                                        line_height: currentLineHeight,
                                        padding: currentPadding,
                                        show_kop: currentShowKop,
                                        column_widths: JSON.stringify(getColumnWidthsPayload()),
                                        keterangan_metode: ($('#keterangan_metode').length ? ($('#keterangan_metode').val() || '') : ''),
                                        catatan_hasil: ($('#catatan_hasil').length ? ($('#catatan_hasil').val() || '') : '')
                                    },
                                    success: function(r2) {
                                        if (r2 && r2.status) {
                                            openPreview(modeSelesai);
                                        } else {
                                            swal('Gagal', (r2 && r2.pesan) ? r2.pesan : 'Gagal menyimpan pengaturan.', 'error');
                                        }
                                        resetButtons();
                                    },
                                    error: function() {
                                        swal('Gagal', 'Terjadi kesalahan saat menyimpan pengaturan.', 'error');
                                        resetButtons();
                                    }
                                });
                            } else {
                                var msg = (resp && resp.pesan)
                                    ? (typeof resp.pesan === 'object' ? Object.values(resp.pesan).join('. ') : resp.pesan)
                                    : 'Gagal menyimpan data hasil.';
                                swal('Gagal Simpan', msg, 'error');
                                resetButtons();
                            }
                        },
                        error: function() {
                            swal('Gagal', 'Terjadi kesalahan saat menyimpan data hasil.', 'error');
                            resetButtons();
                        }
                    });
                }
                // Expose agar bisa dipanggil dari luar IIFE
                window.triggerDirectPreview = triggerDirectPreview;

                function openPreview(modeSelesai) {
                    // previewUrl mungkin sudah punya query string (jenis_makanan_id); pakai & bukan ? kedua
                    var querySep = (previewUrl.indexOf('?') !== -1) ? '&' : '?';
                    var keteranganMetode = ($('#keterangan_metode').length ? ($('#keterangan_metode').val() || '') : '');
                    var catatanHasil = ($('#catatan_hasil').length ? ($('#catatan_hasil').val() || '') : '');
                    var query =
                        'mode=preview' +
                        '&signOption=0' +
                        '&fontsize=' + encodeURIComponent(currentFontsize) +
                        '&line_height=' + encodeURIComponent(currentLineHeight) +
                        '&padding=' + encodeURIComponent(currentPadding) +
                        '&show_kop=' + encodeURIComponent(currentShowKop) +
                        '&column_widths=' + encodeURIComponent(JSON.stringify(getColumnWidthsPayload())) +
                        '&keterangan_metode=' + encodeURIComponent(keteranganMetode) +
                        '&catatan_hasil=' + encodeURIComponent(catatanHasil) +
                        '&t=' + Date.now();

                    $('#preview-hasil-iframe').attr('src', previewUrl + querySep + query);
                    // Simpan modeSelesai agar tombol Pengaturan di preview bisa meneruskannya
                    $('#modalPreviewHasil').data('mode-selesai', modeSelesai);
                    if (modeSelesai) {
                        $('#btn-preview-lanjut-selesai').removeClass('d-none');
                    } else {
                        $('#btn-preview-lanjut-selesai').addClass('d-none');
                    }
                    $('#modalPreviewHasil').modal('show');
                }

                updateFontsizeUI(currentFontsize);
                updateLineHeightUI(currentLineHeight);
                updatePaddingUI(currentPadding);
                $toggleKop.prop('checked', currentShowKop === 1);
                updateKopUI(currentShowKop === 1);

                $slider.on('input change', function() { updateFontsizeUI($(this).val()); });
                $input.on('input change', function() { updateFontsizeUI($(this).val()); });
                $('#fontsize-minus').on('click', function() { updateFontsizeUI(currentFontsize - 0.5); });
                $('#fontsize-plus').on('click', function() { updateFontsizeUI(currentFontsize + 0.5); });

                $lhSlider.on('input change', function() { updateLineHeightUI($(this).val()); });
                $lhInput.on('input change', function() { updateLineHeightUI($(this).val()); });
                $('#lineheight-minus').on('click', function() { updateLineHeightUI(currentLineHeight - 0.1); });
                $('#lineheight-plus').on('click', function() { updateLineHeightUI(currentLineHeight + 0.1); });

                $pdSlider.on('input change', function() { updatePaddingUI($(this).val()); });
                $pdInput.on('input change', function() { updatePaddingUI($(this).val()); });
                $('#padding-minus').on('click', function() { updatePaddingUI(currentPadding - 0.5); });
                $('#padding-plus').on('click', function() { updatePaddingUI(currentPadding + 0.5); });

                $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });

                $('#btn-open-review-hasil').on('click', function() {
                    triggerDirectPreview(false);
                });

                $('#modalReviewHasil').on('show.bs.modal', function() {
                    var modeSelesai = $(this).data('mode-selesai') || false;
                    $(this).find('.modal-title').html(
                        modeSelesai
                            ? '<i class="fa fa-cog mr-2"></i>Pengaturan Hasil - Selesai'
                            : '<i class="fa fa-cog mr-2"></i>Pengaturan Hasil'
                    );
                    $btnBuka.find('span.btn-label-text').text('Terapkan');
                });

                // Saat settings modal ditutup (Batal / X / Terapkan): buka kembali preview jika perlu
                $('#modalReviewHasil').on('hidden.bs.modal', function() {
                    var reopen    = $(this).data('reopen-preview') || false;
                    var modeSelesai = $(this).data('mode-selesai') || false;
                    $(this).data('mode-selesai', false);
                    $(this).data('reopen-preview', false);
                    if (reopen) {
                        // Buka ulang preview — openPreview menggunakan nilai slider terkini
                        openPreview(modeSelesai);
                    }
                });

                $btnBuka.on('click', function() {
                    var modeSelesai = $('#modalReviewHasil').data('mode-selesai') || false;
                    $btnBuka.prop('disabled', true);
                    $loadingIcon.removeClass('d-none');
                    $saveIcon.addClass('d-none');

                    // Langkah 1: sync TinyMCE lalu simpan data hasil, kemudian simpan setting, lalu buka preview
                    function doSaveThenPreview() {
                        // Sync semua TinyMCE hasil
                        if (typeof tinymce !== 'undefined') {
                            if (typeof syncBacaHasilResultAndKeteranganEditorsToTextareas === 'function') {
                                syncBacaHasilResultAndKeteranganEditorsToTextareas();
                            }
                            tinymce.triggerSave();
                            $('.inline-hasil-editor, .inline-keterangan-editor').each(function() {
                                var $ed = $(this);
                                var editorId = $ed.attr('id');
                                var textareaId = $ed.data('textarea-id');
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
                                } catch(e) { /* ignore */ }
                            });
                        }

                        var formData = $('.form').serializeArray().reduce(function(obj, item) {
                            obj[item.name] = item.value;
                            return obj;
                        }, {});

                        $.ajax({
                            url: saveDataUrl,
                            method: 'POST',
                            data: $.extend(formData, { _token: csrfToken }),
                            success: function(resp) {
                                if (resp && resp.status == true) {
                                    doSaveSetting();
                                } else {
                                    var msg = (resp && resp.pesan) ? (typeof resp.pesan === 'object' ? Object.values(resp.pesan).join('. ') : resp.pesan) : 'Gagal menyimpan data hasil.';
                                    swal('Gagal Simpan', msg, 'error');
                                    $btnBuka.prop('disabled', false);
                                    $loadingIcon.addClass('d-none');
                                    $saveIcon.removeClass('d-none');
                                }
                            },
                            error: function() {
                                swal('Gagal', 'Terjadi kesalahan saat menyimpan data hasil.', 'error');
                                $btnBuka.prop('disabled', false);
                                $loadingIcon.addClass('d-none');
                                $saveIcon.removeClass('d-none');
                            }
                        });
                    }

                    // Langkah 2: simpan pengaturan font, lalu tutup settings
                    // (hidden.bs.modal akan membuka kembali preview dengan pengaturan terbaru)
                    function doSaveSetting() {
                        $.ajax({
                            url: saveSettingUrl,
                            method: 'POST',
                            data: {
                                _token: csrfToken,
                                fontsize: currentFontsize,
                                line_height: currentLineHeight,
                                padding: currentPadding,
                                show_kop: currentShowKop,
                                column_widths: JSON.stringify(getColumnWidthsPayload()),
                                keterangan_metode: ($('#keterangan_metode').length ? ($('#keterangan_metode').val() || '') : ''),
                                catatan_hasil: ($('#catatan_hasil').length ? ($('#catatan_hasil').val() || '') : '')
                            },
                            success: function(response) {
                                if (response && response.status) {
                                    $('#modalReviewHasil').modal('hide');
                                } else {
                                    swal('Gagal', (response && response.pesan) ? response.pesan : 'Gagal menyimpan pengaturan.', 'error');
                                }
                            },
                            error: function() {
                                swal('Gagal', 'Terjadi kesalahan saat menyimpan pengaturan.', 'error');
                            },
                            complete: function() {
                                $btnBuka.prop('disabled', false);
                                $loadingIcon.addClass('d-none');
                                $saveIcon.removeClass('d-none');
                            }
                        });
                    }

                    doSaveThenPreview();
                });
            })();




            var laboratoriummethods = @json($laboratoriummethods);

            laboratoriummethods.forEach(laboratoriummethod => {
                $('#status_' + laboratoriummethod.method_id).change(function() {
                    // console.log($(this).val())
                    if ($(this).is(':checked')) {
                        $(".not_show_" + laboratoriummethod.method_id).hide();
                        $(".show_" + laboratoriummethod.method_id).show();
                        // Tampilkan form dan button
                        showHasilForm(laboratoriummethod.method_id);

                    } else {
                        // Sembunyikan form dan button ketika unchecked
                        hideHasilForm(laboratoriummethod.method_id);
                        $(".show_" + laboratoriummethod.method_id).hide();
                        $(".not_show_" + laboratoriummethod.method_id).show();


                    }
                })
                laboratoriummethod.detail.forEach(detail => {
                    $('#status_' + detail.id_sample_result_detail).change(function() {
                        // console.log($(this).val())
                        if ($(this).is(':checked')) {
                            $(".not_show_" + detail.id_sample_result_detail).hide();
                            $(".show_" + detail.id_sample_result_detail).show();
                            // Tampilkan form dan button
                            showHasilForm(detail.id_sample_result_detail);

                        } else {
                            // Sembunyikan form dan button ketika unchecked
                            hideHasilForm(detail.id_sample_result_detail);
                            $(".show_" + detail.id_sample_result_detail).hide();
                            $(".not_show_" + detail.id_sample_result_detail).show();


                        }
                    })
                })

            })

            function toFormatHtml(value) {
                // Auto-close kurung yang tidak tertutup untuk pangkat dan subscript
                var openSupCount = (value.match(/\^\(/g) || []).length;
                var openSubCount = (value.match(/\_\(/g) || []).length;
                var closeCount = (value.match(/\)/g) || []).length;

                // Jika ada ^( atau _( yang tidak tertutup, tambahkan ) di akhir
                var totalOpen = openSupCount + openSubCount;
                if (totalOpen > closeCount) {
                    for (var i = 0; i < (totalOpen - closeCount); i++) {
                        value += ')';
                    }
                }

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

                // Simple direct replacement with regex
                // Step 1: Replace comparison operators first
                value = value.replaceAll("<=", '&#8804;');
                value = value.replaceAll(">=", '&#8805;');
                value = value.replaceAll("<", '&#60;');
                value = value.replaceAll(">", '&#62;');

                // Step 2: Convert ^() to <sup> and _() to <sub>
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');

                return value;
            }

            tinymce.init({
                selector: 'textarea#tembusan',
                height: 200,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount',
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                setup: function(editor) {
                    editor.on('change blur', function() {
                        tinymce.triggerSave();
                    });
                }
            });

            @if ($lab->kode_laboratorium === 'MBI')
                tinymce.init({
                    selector: 'textarea#lokasi_pengambilan',
                    height: 250,
                    width: '100%',
                    menubar: false,
                    theme: 'modern',
                    plugins: [
                        'help',
                        'wordcount'
                    ],
                    toolbar: 'undo redo | bold italic | removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            // Perlebar editor setelah diinisialisasi
                            var $editorContainer = $(editor.getContainer());
                            $editorContainer.css({
                                'width': '100%',
                                'max-width': '100%'
                            });

                            // Perlebar container parent
                            $('#lokasi_pengambilan').closest('.form-group, .col-md-6, .col-md-12, .col-md-8, .col-md-4').css({
                                'width': '100%',
                                'max-width': '100%'
                            });
                        });
                        editor.on('change blur', function() {
                            tinymce.triggerSave();
                        });
                    }
                });

                if ($('#nama_jenis_makanan').length) {
                    tinymce.init({
                        selector: 'textarea#nama_jenis_makanan',
                        height: 150,
                        width: '100%',
                        menubar: false,
                        theme: 'modern',
                        plugins: ['help', 'wordcount'],
                        toolbar: 'undo redo | bold italic | removeformat | help',
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                        setup: function(editor) {
                            editor.on('change blur', function() {
                                tinymce.triggerSave();
                            });
                        }
                    });
                }
            @else
                tinymce.init({
                    selector: 'textarea#lokasi_pengambilan_kimia',
                    height: 250,
                    width: '100%',
                    menubar: false,
                    theme: 'modern',
                    plugins: [
                        'help',
                        'wordcount'
                    ],
                    toolbar: 'undo redo | bold italic | removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            // Perlebar editor setelah diinisialisasi
                            var $editorContainer = $(editor.getContainer());
                            $editorContainer.css({
                                'width': '100%',
                                'max-width': '100%'
                            });

                            // Perlebar container parent
                            $('#lokasi_pengambilan_kimia').closest('.form-group, .col-md-6, .col-md-12, .col-md-8, .col-md-4, .input-group').css({
                                'width': '100%',
                                'max-width': '100%'
                            });
                        });
                        editor.on('change blur', function() {
                            tinymce.triggerSave();
                        });
                    }
                });

                if ($('#nama_jenis_makanan').length) {
                    tinymce.init({
                        selector: 'textarea#nama_jenis_makanan',
                        height: 150,
                        width: '100%',
                        menubar: false,
                        theme: 'modern',
                        plugins: ['help', 'wordcount'],
                        toolbar: 'undo redo | bold italic | removeformat | help',
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                        setup: function(editor) {
                            editor.on('change blur', function() {
                                tinymce.triggerSave();
                            });
                        }
                    });
                }
            @endif

            function initMetodeInlineEditors() {
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                    return;
                }

                var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                if (tinymce.baseURL === undefined ||
                    tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
                    tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                }

                var pendingSelectors = [];

                $('.metode-editor').each(function() {
                    var $textarea = $(this);
                    var editorId = $textarea.attr('id');
                    if (!editorId) {
                        return;
                    }

                    var inlineEditorId = editorId + '_editor';
                    if (tinymce.get(inlineEditorId)) {
                        return;
                    }

                    if (!$('#' + inlineEditorId).length) {
                        var content = $textarea.val() || '';
                        var $editorDiv = $('<div>')
                            .addClass('inline-metode-editor')
                            .attr('id', inlineEditorId)
                            .attr('data-original-id', editorId)
                            .attr('contenteditable', 'true')
                            .html(content);
                        $textarea.after($editorDiv).hide();
                    }

                    pendingSelectors.push('#' + inlineEditorId);
                });

                if (pendingSelectors.length === 0) {
                    return;
                }

                tinymce.init({
                    selector: pendingSelectors.join(','),
                    inline: true,
                    menubar: false,
                    theme: 'modern',
                    content_css: false,
                    document_base_url: window.location.origin,
                    plugins: [
                        'lists charmap',
                        'searchreplace',
                        'paste'
                    ],
                    toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                    toolbar_mode: 'floating',
                    toolbar_location: 'auto',
                    paste_as_text: true,
                    content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; } sup { vertical-align: super; font-size: 0.8em; } sub { vertical-align: sub; font-size: 0.8em; }',
                    valid_elements: '*[*]',
                    extended_valid_elements: 'sup[*],sub[*]',
                    formats: {
                        superscript: {inline: 'sup', styles: {verticalAlign: 'super'}},
                        subscript: {inline: 'sub', styles: {verticalAlign: 'sub'}}
                    },
                    forced_root_block: false,
                    force_br_newlines: true,
                    force_p_newlines: false,
                    charmap_append: [
                        [0x00B1, 'plus-minus sign'],
                        [0x00B2, 'superscript two'],
                        [0x00B3, 'superscript three'],
                        [0x00B5, 'micro sign'],
                        [0x00BC, 'vulgar fraction one quarter'],
                        [0x00BD, 'vulgar fraction one half'],
                        [0x00BE, 'vulgar fraction three quarters'],
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
                        editor.on('change blur keyup', function() {
                            var originalId = $(editor.getElement()).data('original-id');
                            if (originalId) {
                                $('#' + originalId).val(editor.getContent());
                            }
                        });
                    }
                });
            }

            setTimeout(initMetodeInlineEditors, 300);
            $(document).on('analisEditorReady', function() {
                setTimeout(initMetodeInlineEditors, 200);
            });

            // === TINYMCE EDITOR MODAL ===
            var currentEditorTarget = null;
            var editorInstance = null;
            var currentMethodId = null;
            var updateMetodeParameterUrl = '{{ url('elits-laboratorium/metode-parameter/__METHOD_ID__') }}';

            function getMetodeFieldValue(methodId) {
                if (!methodId) return '';
                var editor = tinymce.get('metode_' + methodId + '_editor');
                if (editor) return editor.getContent();
                var $ta = $('#metode_' + methodId);
                return ($ta.length && !$ta.is('select')) ? ($ta.val() || '') : '';
            }

            function setMetodeFieldValue(methodId, htmlValue) {
                if (!methodId) return;
                var $ta = $('#metode_' + methodId);
                if (!$ta.length || $ta.is('select')) return;
                $ta.val(htmlValue);
                var editor = tinymce.get('metode_' + methodId + '_editor');
                if (editor) editor.setContent(htmlValue);
            }

            function toggleEditorMetodeSection(targetId, methodId) {
                var show = !!(targetId && targetId.indexOf('result_method_') === 0 && methodId && $('#metode_' + methodId).length && !$('#metode_' + methodId).is('select'));
                if (show) {
                    $('#editor_metode_container').show();
                    $('#editor_metode_content').val(getMetodeFieldValue(methodId));
                    $('#editor_metode_permanent').prop('checked', false);
                } else {
                    $('#editor_metode_container').hide();
                    $('#editor_metode_content').val('');
                    $('#editor_metode_permanent').prop('checked', false);
                }
            }

            function persistMetodeFromModal(methodId, done) {
                if (!methodId || !$('#editor_metode_container').is(':visible')) {
                    if (done) done(true);
                    return;
                }
                var metodeVal = $('#editor_metode_content').val() || '';
                setMetodeFieldValue(methodId, metodeVal);
                if (!$('#editor_metode_permanent').is(':checked')) {
                    if (done) done(true);
                    return;
                }
                $.ajax({
                    url: updateMetodeParameterUrl.replace('__METHOD_ID__', methodId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name_method: metodeVal,
                        permanent: 1
                    },
                    success: function(res) {
                        if (res && res.status) {
                            if (done) done(true);
                        } else {
                            alert((res && res.pesan) ? res.pesan : 'Gagal menyimpan metode permanen');
                            if (done) done(false);
                        }
                    },
                    error: function() {
                        alert('Gagal menyimpan metode permanen');
                        if (done) done(false);
                    }
                });
            }

            function finishEditorSave(goToNext) {
                goToNext = goToNext || false;
                var proceed = function() {
                    if (goToNext) {
                        var nextTargetId = getNextTargetId();
                        if (nextTargetId) {
                            $('#editorModal').modal('hide');
                            $('#editorModal').on('hidden.bs.modal', function() {
                                $('#editorModal').off('hidden.bs.modal');
                                setTimeout(function() {
                                    openEditorForTarget(nextTargetId);
                                }, 300);
                            });
                        } else {
                            $('#editorModal').modal('hide');
                        }
                    } else {
                        $('#editorModal').modal('hide');
                    }
                };
                persistMetodeFromModal(currentMethodId, function(ok) {
                    if (ok) proceed();
                });
            }
            var currentIsOption = false;
            var currentOptions = [];
            var allEditorButtons = [];

            // Convert HTML to plain text (remove HTML tags)
            function stripHtmlTags(html) {
                if (!html) return '';
                var tmp = document.createElement('DIV');
                tmp.innerHTML = html;
                return tmp.textContent || tmp.innerText || '';
            }

            // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
            function convertToTinyMCE(value) {
                if (!value) return '';

                console.log('convertToTinyMCE INPUT:', value);

                // Simple direct replacement - no complex placeholder system
                // Step 1: Handle comparison symbols first
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');

                // Step 2: Convert ^() to <sup> and _() to <sub>
                // Use regex with capturing group for content between markers
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');

                console.log('convertToTinyMCE OUTPUT:', value);

                return value;
            }

            // Convert from HTML <sup> and <sub> to ^() and _() format for our system
            function convertFromTinyMCE(value) {
                if (!value) return '';

                console.log('convertFromTinyMCE INPUT:', value);

                // Simple direct replacement
                // Step 1: Convert HTML tags to ^() and _() format
                // IMPORTANT: Handle tags with attributes (e.g., <sup style="...">)
                // Use [^>]* to match any attributes before the closing >
                value = value.replace(/<sup[^>]*>([^<]*)<\/sup>/gi, '^($1)');
                value = value.replace(/<sub[^>]*>([^<]*)<\/sub>/gi, '_($1)');

                // Step 2: Strip any remaining HTML tags
                value = value.replace(/<[^>]*>/g, '');

                // Step 3: Decode HTML entities
                value = value.replace(/&le;/gi, '≤');
                value = value.replace(/&ge;/gi, '≥');
                value = value.replace(/&lt;/g, '<');
                value = value.replace(/&gt;/g, '>');
                value = value.replace(/&plusmn;/g, '±');
                value = value.replace(/&nbsp;/g, ' ');

                console.log('convertFromTinyMCE OUTPUT:', value);

                return value;
            }

            // Collect all editor buttons on page load (in DOM order)
            function collectEditorButtons() {
                allEditorButtons = [];
                $('.open-editor-modal').each(function(index) {
                    allEditorButtons.push({
                        button: $(this),
                        methodId: $(this).data('method-id'),
                        targetId: $(this).data('target'),
                        methodName: $(this).data('method-name'),
                        index: index,
                        isOption: $(this).data('is-option') ? true : false,
                        options: $(this).data('options') || null,
                        currentValue: $(this).data('current-value') || ''
                    });
                });
            }

            // Initialize on page load
            collectEditorButtons();

            // Function to open editor for a specific target (by targetId)
            function openEditorForTarget(targetId) {
                var buttonData = allEditorButtons.find(function(item) {
                    return item.targetId == targetId;
                });

                if (buttonData) {
                    var methodName = buttonData.methodName;
                    var methodId = buttonData.methodId;

                    // Set current target BEFORE getting value (important for next navigation)
                    currentEditorTarget = targetId;
                    currentMethodId = methodId;
                    currentIsOption = buttonData.isOption || false;

                    // Parse options (if any) menjadi array
                    currentOptions = [];
                    if (buttonData.options) {
                        try {
                            if (Array.isArray(buttonData.options)) {
                                currentOptions = buttonData.options;
                            } else if (typeof buttonData.options === 'string') {
                                currentOptions = JSON.parse(buttonData.options);
                            }
                        } catch (e) {
                            console.warn('Failed to parse options for editor:', e);
                            currentOptions = [];
                        }
                    }

                    // Strip HTML tags from methodName for modal title
                    var methodNamePlain = stripHtmlTags(methodName);

                    // Set modal title
                    $('#editorModalLabel').text('Editor - ' + methodNamePlain);

                    // Clear editor content first (will be set when modal is shown)
                    $('#editor_content').val('');

                    // Show modal (value will be loaded from target textarea in shown.bs.modal event)
                    $('#editorModal').modal('show');
                }
            }

            // Function to open editor for a specific method (for backward compatibility and next navigation)
            function openEditorForMethod(methodId) {
                // Find first button with this methodId (usually the result button)
                var buttonData = allEditorButtons.find(function(item) {
                    return item.methodId == methodId && item.targetId.startsWith('result_method_');
                });

                if (buttonData) {
                    openEditorForTarget(buttonData.targetId);
                }
            }

            // Open editor modal - use targetId directly from clicked button
            $('.open-editor-modal').on('click', function() {
                var targetId = $(this).data('target');
                openEditorForTarget(targetId);
            });

            // Function to get next target ID (based on DOM order, same type only)
            function getNextTargetId() {
                if (!currentEditorTarget || allEditorButtons.length === 0) {
                    return null;
                }

                // Determine current input type (hasil or keterangan)
                var currentType = '';
                if (currentEditorTarget.startsWith('result_method_')) {
                    currentType = 'hasil';
                } else if (currentEditorTarget.startsWith('keterangan') || currentEditorTarget.startsWith(
                        'keterangan_detail')) {
                    currentType = 'keterangan';
                }

                if (!currentType) {
                    return null;
                }

                var currentIndex = -1;
                for (var i = 0; i < allEditorButtons.length; i++) {
                    if (allEditorButtons[i].targetId == currentEditorTarget) {
                        currentIndex = i;
                        break;
                    }
                }

                // Find next button of the same type in DOM order
                if (currentIndex >= 0) {
                    for (var i = currentIndex + 1; i < allEditorButtons.length; i++) {
                        var nextTargetId = allEditorButtons[i].targetId;
                        var nextType = '';

                        if (nextTargetId.startsWith('result_method_')) {
                            nextType = 'hasil';
                        } else if (nextTargetId.startsWith('keterangan') || nextTargetId.startsWith(
                                'keterangan_detail')) {
                            nextType = 'keterangan';
                        }

                        // Return if same type
                        if (nextType == currentType) {
                            return nextTargetId;
                        }
                    }
                }

                return null;
            }

            // Initialize editor modal when shown
            $('#editorModal').on('shown.bs.modal', function() {
                // Reset mode
                $('#editor_option_container').hide();
                $('#editor_text_container').hide();
                toggleEditorMetodeSection(currentEditorTarget, currentMethodId);

                // Jika currentIsOption = true, gunakan dropdown di dalam modal
                if (currentIsOption && currentOptions && currentOptions.length > 0 && currentEditorTarget) {
                    var $select = $('#editor_option_select');
                    $select.empty();

                    // Ambil nilai saat ini dari textarea
                    var currentVal = $('#' + currentEditorTarget).val() || '';

                    // Tambahkan opsi
                    $select.append($('<option>', {
                        value: '',
                        text: 'Pilih hasil'
                    }));
                    currentOptions.forEach(function(opt) {
                        $select.append($('<option>', {
                            value: opt,
                            text: opt,
                            selected: currentVal && currentVal.toLowerCase() === opt.toLowerCase()
                        }));
                    });

                    $('#editor_option_container').show();

                    // Tidak perlu inisialisasi TinyMCE dalam mode dropdown
                    if (editorInstance) {
                        try {
                            tinymce.remove('#editor_content');
                        } catch (e) {}
                        editorInstance = null;
                    }
                    return;
                }

                // MODE TINYMCE (default, non-option)
                $('#editor_text_container').show();

                // Remove existing TinyMCE instance if any
                if (editorInstance) {
                    try {
                        tinymce.remove('#editor_content');
                    } catch (e) {}
                    editorInstance = null;
                }

                // Get fresh value from target textarea (not from editor content)
                var targetValue = '';
                if (currentEditorTarget) {
                    targetValue = $('#' + currentEditorTarget).val() || '';
                }
                var tinymceValue = convertToTinyMCE(targetValue);

                // Set value to textarea before initializing TinyMCE
                $('#editor_content').val(tinymceValue);

                // Initialize TinyMCE
                tinymce.init({
                    selector: '#editor_content',
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists charmap',
                        'searchreplace code',
                        'insertdatetime paste help wordcount'
                    ],
                    toolbar: 'undo redo | bold italic underline | ' +
                        'superscript subscript | ' +
                        'charmap | ' +
                        'removeformat | code | help',
                    charmap_append: [
                        [60, 'less than'],
                        [62, 'greater than'],
                        [8804, 'less than or equal to'],
                        [8805, 'greater than or equal to'],
                        [177, 'plus-minus sign']
                    ],
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; padding: 10px; }',
                    setup: function(editor) {
                        editorInstance = editor;
                    },
                    init_instance_callback: function(editor) {
                        // Set content after editor is fully initialized
                        if (tinymceValue) {
                            editor.setContent(tinymceValue);
                        }
                    }
                });
            });

            // Save from editor to textarea
            function saveEditorContent(goToNext) {
                goToNext = goToNext || false;

                // MODE DROPDOWN (is_option = 1)
                if (currentIsOption && currentEditorTarget) {
                    var selectedValue = $('#editor_option_select').val() || '';

                    // Set ke textarea target
                    $('#' + currentEditorTarget).val(selectedValue);

                    // Trigger input event untuk update preview
                    $('#' + currentEditorTarget).trigger('input');

                    finishEditorSave(goToNext);
                    return;
                }

                // MODE TINYMCE (default)
                if (editorInstance && currentEditorTarget) {
                    // Get content from TinyMCE (HTML format)
                    var htmlContent = editorInstance.getContent();

                    // Convert from TinyMCE HTML format to our ^() format
                    var convertedContent = convertFromTinyMCE(htmlContent);

                    // Set to original textarea
                    $('#' + currentEditorTarget).val(convertedContent);

                    // Sync to dropdown if exists (for is_option = 1)
                    if (currentEditorTarget.startsWith('result_method_')) {
                        var methodId = currentEditorTarget.replace('result_method_', '');
                        var $dropdown = $('#result_dropdown_' + methodId);
                        if ($dropdown.length) {
                            // Check if convertedContent matches any dropdown option
                            var matched = false;
                            $dropdown.find('option').each(function() {
                                if ($(this).val() === convertedContent) {
                                    $dropdown.val(convertedContent);
                                    matched = true;
                                    return false; // break loop
                                }
                            });
                            // If no match, dropdown will remain as is (user can still select from dropdown)
                        }
                    }

                    // Trigger input event to update preview
                    $('#' + currentEditorTarget).trigger('input');

                    finishEditorSave(goToNext);
                }
            }

            $('#saveEditorContent').on('click', function() {
                saveEditorContent(false);
            });

            // Save and Next button
            $('#saveAndNextEditorContent').on('click', function() {
                saveEditorContent(true);
            });

            // Clean up on modal close
            $('#editorModal').on('hidden.bs.modal', function() {
                // Remove TinyMCE instance
                if (editorInstance) {
                    try {
                        tinymce.remove('#editor_content');
                    } catch (e) {}
                    editorInstance = null;
                }
                // Don't reset currentEditorTarget and currentMethodId if we're going to next
                // They will be reset when opening next editor
            });

            // ============================================
            // JEMBATAN OTOMATIS BAKU MUTU
            // ============================================

            console.log('Script baku mutu loaded!'); // Debug

            // Function untuk handle tambah baku mutu
            var _bakuMutuReferensiCache = [];
            var _bakuMutuReferensiUrl = '{{ route('elits-baca-hasil.baku-mutu.referensi') }}';
            var _preferReferensiOnOpen = false;

            function setModalTinyContent(editorId, htmlValue) {
                var content = htmlValue || '';
                var converted = (typeof convertFromSystemToTinyMCE === 'function')
                    ? convertFromSystemToTinyMCE(content)
                    : content;
                $('#' + editorId).val(content);
                if (tinymce.get(editorId)) {
                    tinymce.get(editorId).setContent(converted || '');
                }
            }

            function decodeHtmlEntities(text) {
                if (text == null || text === '') {
                    return '';
                }
                var decoded = $('<textarea/>').html(String(text)).text();
                // Cadangan untuk entity yang masih tersisa sebagai teks mentah
                decoded = decoded
                    .replace(/&nbsp;/gi, ' ')
                    .replace(/&#160;/gi, ' ')
                    .replace(/&#60;/gi, '<')
                    .replace(/&#62;/gi, '>')
                    .replace(/&lt;/gi, '<')
                    .replace(/&gt;/gi, '>')
                    .replace(/&amp;/gi, '&');
                return decoded.replace(/\s+/g, ' ').trim();
            }

            function destroyReferensiSelect2() {
                var $select = $('#modal-referensi-baku-mutu');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.off('select2:open select2:close select2:select');
                    $select.select2('destroy');
                }
            }

            function initReferensiSelect2() {
                var $select = $('#modal-referensi-baku-mutu');
                var $modal = $('#modalTambahBakuMutu');
                if (!$modal.hasClass('show') && !$modal.is(':visible')) {
                    return;
                }

                destroyReferensiSelect2();
                $select.select2({
                    placeholder: '— Buat baru tanpa referensi —',
                    allowClear: true,
                    // Parent body agar dropdown tidak terpotong overflow modal
                    dropdownParent: $(document.body),
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownAutoWidth: false
                });
            }

            function applyBakuMutuReferensi(ref) {
                if (!ref) {
                    return;
                }

                // name_report hanya disalin dari jenis makanan lain (parameter sama).
                // Dari parameter lain: tetap pakai nama parameter tujuan.
                if (ref.source !== 'parameter_lain' && ref.name_report) {
                    $('#modal-name-report').val($('<textarea/>').html(ref.name_report).text());
                }
                if (ref.tipe_nilai_baku_mutu) {
                    $('#modal-tipe-nilai').val(ref.tipe_nilai_baku_mutu);
                }
                if (ref.library_id) {
                    $('#modal-library-id').val(ref.library_id).trigger('change');
                }
                if (ref.unit_id) {
                    $('#modal-unit-id').val(ref.unit_id).trigger('change');
                } else if (ref.unit_id === '-' || ref.unit_id === null) {
                    $('#modal-unit-id').val(ref.unit_id === '-' ? '-' : '').trigger('change');
                }

                $('#modal-min').val(ref.min != null ? ref.min : '');
                $('#modal-max').val(ref.max != null ? ref.max : '');
                setModalTinyContent('modal-equal', ref.equal || '');
                setModalTinyContent('modal-nilai-baku-mutu', ref.nilai_baku_mutu || '');

                // Setelah isi dari referensi, scroll ke bagian konfigurasi di dalam modal-body
                var $modalBody = $('#modalTambahBakuMutu .modal-body');
                var $target = $('#modal-konfigurasi-card');
                if ($target.length && $modalBody.length) {
                    setTimeout(function() {
                        var top = $target.position().top + $modalBody.scrollTop() - 8;
                        $modalBody.stop(true).animate({ scrollTop: Math.max(0, top) }, 250);
                    }, 120);
                }
            }

            function loadBakuMutuReferensiOptions(methodId, sampleTypeId, labId, excludeJenisMakananId, isMml) {
                var $select = $('#modal-referensi-baku-mutu');
                _bakuMutuReferensiCache = [];
                destroyReferensiSelect2();
                $select.empty().append('<option value="">— Buat baru tanpa referensi —</option>');
                $('#modal-referensi-empty').hide().html(
                    '<i class="fa fa-info-circle text-muted mr-1"></i>Belum ada baku mutu referensi untuk jenis sampel ini di lab.'
                );
                $('#modal-referensi-loading').show();

                if (isMml) {
                    $('#modal-referensi-title').html('<i class="fa fa-copy mr-2"></i>Salin dari Referensi (Opsional)');
                    $('#modal-referensi-hint').text(
                        'Bisa menyalin dari jenis makanan lain (parameter yang sama) atau dari parameter lain di lab. Parameter & jenis makanan tujuan tetap yang sedang dipilih.'
                    );
                } else {
                    $('#modal-referensi-title').html('<i class="fa fa-copy mr-2"></i>Salin dari Parameter Lain di Lab (Opsional)');
                    $('#modal-referensi-hint').text(
                        'Memilih referensi akan mengisi form di bawah. Parameter tujuan tetap parameter yang sedang dipilih.'
                    );
                }

                return $.ajax({
                    url: _bakuMutuReferensiUrl,
                    type: 'GET',
                    data: {
                        method_id: methodId,
                        sampletype_id: sampleTypeId,
                        lab_id: labId || '',
                        exclude_jenis_makanan_id: excludeJenisMakananId || ''
                    }
                }).done(function(response) {
                    if (!response || !response.status) {
                        $('#modal-referensi-empty').show();
                        return;
                    }

                    var groups = response.groups || {};
                    var jenisMakananList = Array.isArray(groups.jenis_makanan) ? groups.jenis_makanan : [];
                    var parameterLainList = Array.isArray(groups.parameter_lain) ? groups.parameter_lain : [];

                    // Fallback jika response lama tanpa groups
                    if (!jenisMakananList.length && !parameterLainList.length && Array.isArray(response.data)) {
                        parameterLainList = response.data;
                    }

                    _bakuMutuReferensiCache = jenisMakananList.concat(parameterLainList);
                    if (!_bakuMutuReferensiCache.length) {
                        $('#modal-referensi-empty').show();
                        return;
                    }

                    if (jenisMakananList.length) {
                        var $ogJm = $('<optgroup></optgroup>').attr('label', 'Jenis Makanan Lain (parameter sama)');
                        jenisMakananList.forEach(function(item) {
                            $ogJm.append(
                                $('<option></option>')
                                    .attr('value', item.id_baku_mutu)
                                    .text(decodeHtmlEntities(item.label || item.name_jenis_makanan))
                            );
                        });
                        $select.append($ogJm);
                    }

                    if (parameterLainList.length) {
                        var $ogParam = $('<optgroup></optgroup>').attr('label', 'Parameter Lain di Lab (jenis sampel sama)');
                        parameterLainList.forEach(function(item) {
                            $ogParam.append(
                                $('<option></option>')
                                    .attr('value', item.id_baku_mutu)
                                    .text(decodeHtmlEntities(item.label || item.params_method || item.name_jenis_makanan))
                            );
                        });
                        $select.append($ogParam);
                    }
                }).fail(function() {
                    $('#modal-referensi-empty').show().html(
                        '<i class="fa fa-exclamation-triangle text-warning mr-1"></i>Gagal memuat daftar referensi.'
                    );
                }).always(function() {
                    $('#modal-referensi-loading').hide();
                    initReferensiSelect2();
                    if (_preferReferensiOnOpen) {
                        var $modalBody = $('#modalTambahBakuMutu .modal-body');
                        $modalBody.scrollTop(0);
                        setTimeout(function() {
                            try {
                                $select.select2('open');
                            } catch (e) { /* ignore */ }
                        }, 250);
                        _preferReferensiOnOpen = false;
                    }
                });
            }

            function handleTambahBakuMutu(button) {
                console.log('handleTambahBakuMutu called!', button); // Debug

                var $button = $(button);
                var methodId = $button.data('method-id');
                var methodName = $button.data('method-name');
                var sampleTypeId = $button.data('sample-type-id');
                var sampleTypeName = $button.data('sample-type-name');
                var jenisMakananId = $button.data('jenis-makanan-id');
                var jenisMakananName = $button.data('jenis-makanan-name');
                var labCode = $button.data('lab-code');
                var labId = $button.data('lab-id');
                _preferReferensiOnOpen = String($button.data('prefer-referensi')) === '1';

                console.log('Data:', {
                    methodId,
                    methodName,
                    sampleTypeId,
                    sampleTypeName,
                    jenisMakananId,
                    jenisMakananName,
                    labCode,
                    labId
                }); // Debug

                // Reset form
                $('#formTambahBakuMutu')[0].reset();
                $('#modal-referensi-baku-mutu').val('');

                // Set data ke form modal
                $('#modal-method-display').val(methodName);
                $('#modal-method-id').val(methodId);
                $('#modal-sample-type-display').val(sampleTypeName);
                $('#modal-sampletype-id').val(sampleTypeId);

                var sampleTypeText = (sampleTypeName || '').toString();
                var isMakananMinumanLainnya = sampleTypeText.includes('Makanan') ||
                    sampleTypeText.includes('Minuman') ||
                    sampleTypeText.includes('Lainnya');

                // Auto-fill nama parameter di laporan
                $('#modal-name-report').val(methodName);

                // Handle jenis makanan (khusus untuk sampel makanan/minuman/lainnya)
                // Ambil jenis makanan dari dropdown di halaman baca hasil jika ada
                var currentJenisMakananId = $('#jenis_makanan_picker').val();
                var currentJenisMakananName = $('#jenis_makanan_picker option:selected').text();

                if (currentJenisMakananId && currentJenisMakananId !== '__new__') {
                    // Gunakan yang dipilih di dropdown baca hasil
                    $('#modal-jenis-makanan-display').val(currentJenisMakananName);
                    $('#modal-jenis-makanan-id').val(currentJenisMakananId);
                    $('#modal-jenis-makanan-group').show();
                } else if (jenisMakananId && jenisMakananName) {
                    // Fallback ke data dari tombol
                    $('#modal-jenis-makanan-display').val(jenisMakananName);
                    $('#modal-jenis-makanan-id').val(jenisMakananId);
                    $('#modal-jenis-makanan-group').show();
                } else {
                    $('#modal-jenis-makanan-group').hide();
                }

                // Handle tipe nilai baku mutu (khusus sampel makanan/minuman/lainnya)
                if (isMakananMinumanLainnya) {
                    $('#modal-tipe-nilai-group').show();
                    $('#modal-tipe-nilai').prop('required', true);
                } else {
                    $('#modal-tipe-nilai-group').hide();
                    $('#modal-tipe-nilai').prop('required', false);
                    $('#modal-tipe-nilai').val('');
                }

                // Simpan lab code untuk submit
                $('#formTambahBakuMutu').data('lab-code', labCode);
                $('#formTambahBakuMutu').data('lab-id', labId);
                $('#formTambahBakuMutu').data('method-id', methodId);
                $('#formTambahBakuMutu').data('sample-type-id', sampleTypeId);

                var excludeJm = $('#modal-jenis-makanan-id').val() || jenisMakananId || '';
                loadBakuMutuReferensiOptions(methodId, sampleTypeId, labId, excludeJm, isMakananMinumanLainnya);

                // Tampilkan modal
                $('#modalTambahBakuMutu').modal('show');
            }

            $(document).on('change', '#modal-referensi-baku-mutu', function() {
                var selectedId = $(this).val();
                if (!selectedId) {
                    return;
                }
                var ref = null;
                for (var i = 0; i < _bakuMutuReferensiCache.length; i++) {
                    if (String(_bakuMutuReferensiCache[i].id_baku_mutu) === String(selectedId)) {
                        ref = _bakuMutuReferensiCache[i];
                        break;
                    }
                }
                if (ref) {
                    applyBakuMutuReferensi(ref);
                }
            });

            // Handle click tombol tambah baku mutu dengan event delegation
            $(document).on('click', '.btn-tambah-baku-mutu', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Tombol Tambah Baku Mutu diklik via event delegation!'); // Debug
                handleTambahBakuMutu(this);
            });

            // Initialize Select2 dan TinyMCE ketika modal baku mutu ditampilkan
            $('#modalTambahBakuMutu').on('shown.bs.modal', function() {
                var $modal = $(this);
                var $modalBody = $modal.find('.modal-body');

                // Destroy existing Select2 instances if any
                if ($('#modal-library-id').hasClass('select2-hidden-accessible')) {
                    $('#modal-library-id').select2('destroy');
                }
                if ($('#modal-unit-id').hasClass('select2-hidden-accessible')) {
                    $('#modal-unit-id').select2('destroy');
                }
                destroyReferensiSelect2();

                // Delay initialization untuk memastikan modal sudah fully rendered
                setTimeout(function() {
                    // Initialize Select2 untuk Acuan Baku Mutu
                    var $librarySelect = $('#modal-library-id');
                    $librarySelect.select2({
                        placeholder: 'Pilih Acuan Baku Mutu',
                        allowClear: true,
                        dropdownParent: $(document.body),
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownAutoWidth: false,
                        escapeMarkup: function(markup) {
                            return markup;
                        }
                    });

                    // Initialize Select2 untuk Satuan
                    var $unitSelect = $('#modal-unit-id');
                    $unitSelect.select2({
                        placeholder: 'Pilih Satuan',
                        allowClear: true,
                        dropdownParent: $(document.body),
                        width: '100%',
                        theme: 'bootstrap4',
                        dropdownAutoWidth: false,
                        escapeMarkup: function(markup) {
                            return markup;
                        }
                    });

                    // Select2 searchable untuk referensi baku mutu
                    initReferensiSelect2();

                    // Pastikan body modal bisa di-scroll setelah init
                    $modalBody.css({
                        'overflow-y': 'auto',
                        'overflow-x': 'hidden'
                    });

                    // Prevent modal from closing when clicking on Select2 dropdown
                    $(document).on('click.select2-modal', '.select2-dropdown', function(e) {
                        e.stopPropagation();
                    });

                    // Store original scroll position when Select2 opens to prevent jump
                    var originalScrollTop = 0;

                    $librarySelect.on('select2:open', function() {
                        originalScrollTop = $modalBody.scrollTop();
                    });

                    $librarySelect.on('select2:close', function() {
                        $modalBody.scrollTop(originalScrollTop);
                    });

                    $unitSelect.on('select2:open', function() {
                        originalScrollTop = $modalBody.scrollTop();
                    });

                    $unitSelect.on('select2:close', function() {
                        $modalBody.scrollTop(originalScrollTop);
                    });
                }, 200);

                // Initialize TinyMCE untuk Nilai Sama Dengan
                if (tinymce.get('modal-equal')) {
                    tinymce.get('modal-equal').remove();
                }
                tinymce.init({
                    selector: '#modal-equal',
                    height: 100,
                    menubar: false,
                    plugins: ['charmap'],
                    toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                    charmap_append: [
                        [60, 'less than'],
                        [62, 'greater than'],
                        [8804, 'less than or equal to'],
                        [8805, 'greater than or equal to'],
                        [177, 'plus-minus sign']
                    ],
                    valid_elements: '*[*]',
                    extended_valid_elements: 'sup[*],sub[*],br,p,span[*],strong/b,em/i,u',
                    entity_encoding: 'raw',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; } sup { vertical-align: super; font-size: smaller; } sub { vertical-align: sub; font-size: smaller; }',
                    setup: function(editor) {
                        editor.on('change', function() {
                            tinymce.triggerSave();
                        });
                        editor.on('init', function() {
                            // Convert existing content dari format sistem ke TinyMCE saat init
                            var existingContent = $('#modal-equal').val();
                            if (existingContent) {
                                var convertedContent = convertFromSystemToTinyMCE(
                                    existingContent);
                                editor.setContent(convertedContent);
                            }
                        });
                    }
                });

                // Initialize TinyMCE untuk Nilai Baku Mutu di Laporan
                if (tinymce.get('modal-nilai-baku-mutu')) {
                    tinymce.get('modal-nilai-baku-mutu').remove();
                }
                tinymce.init({
                    selector: '#modal-nilai-baku-mutu',
                    height: 150,
                    menubar: false,
                    plugins: ['charmap'],
                    toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                    charmap_append: [
                        [60, 'less than'],
                        [62, 'greater than'],
                        [8804, 'less than or equal to'],
                        [8805, 'greater than or equal to'],
                        [177, 'plus-minus sign']
                    ],
                    valid_elements: '*[*]',
                    extended_valid_elements: 'sup[*],sub[*],br,p,span[*],strong/b,em/i,u',
                    entity_encoding: 'raw',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; } sup { vertical-align: super; font-size: smaller; } sub { vertical-align: sub; font-size: smaller; }',
                    setup: function(editor) {
                        editor.on('change', function() {
                            tinymce.triggerSave();
                        });
                        editor.on('init', function() {
                            // Convert existing content dari format sistem ke TinyMCE saat init
                            var existingContent = $('#modal-nilai-baku-mutu').val();
                            if (existingContent) {
                                var convertedContent = convertFromSystemToTinyMCE(
                                    existingContent);
                                editor.setContent(convertedContent);
                            }
                        });
                    }
                });
            });

            // Cleanup TinyMCE ketika modal ditutup
            $('#modalTambahBakuMutu').on('hidden.bs.modal', function() {
                var $modal = $(this);
                var $modalBody = $modal.find('.modal-body');

                // Restore modal body overflow
                $modalBody.css({
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden'
                });

                // Destroy Select2
                if ($('#modal-library-id').hasClass('select2-hidden-accessible')) {
                    $('#modal-library-id').off('select2:open select2:close');
                    $('#modal-library-id').select2('destroy');
                }
                if ($('#modal-unit-id').hasClass('select2-hidden-accessible')) {
                    $('#modal-unit-id').off('select2:open select2:close');
                    $('#modal-unit-id').select2('destroy');
                }
                destroyReferensiSelect2();

                // Remove Select2 event handlers
                $(document).off('click.select2-modal', '.select2-dropdown');

                // Destroy TinyMCE
                if (tinymce.get('modal-equal')) {
                    tinymce.get('modal-equal').remove();
                }
                if (tinymce.get('modal-nilai-baku-mutu')) {
                    tinymce.get('modal-nilai-baku-mutu').remove();
                }
            });
        });

        // Function untuk konversi dari TinyMCE HTML ke format sistem
        function convertFromTinyMCEToSystem(htmlContent) {
            if (!htmlContent) return '';

            // Remove paragraph tags dan line breaks dari TinyMCE
            var content = htmlContent.replace(/<p>/gi, '').replace(/<\/p>/gi, '');
            content = content.replace(/<br\s*\/?>/gi, '');

            // Convert HTML entities ke format sistem
            // IMPORTANT: Handle tags with attributes (e.g., <sup style="...">)
            // Use [^>]* to match any attributes before the closing >
            content = content.replace(/<sup[^>]*>([^<]*)<\/sup>/gi, '^($1)');
            content = content.replace(/<sub[^>]*>([^<]*)<\/sub>/gi, '_($1)');
            content = content.replace(/&le;/gi, '≤');
            content = content.replace(/&ge;/gi, '≥');
            content = content.replace(/&lt;/gi, '<');
            content = content.replace(/&gt;/gi, '>');
            content = content.replace(/&plusmn;/gi, '±');
            content = content.replace(/&nbsp;/gi, ' ');

            // Remove any remaining HTML tags
            content = content.replace(/<[^>]*>/gi, '');

            // Trim whitespace
            content = content.trim();

            console.log('Converted from TinyMCE:', htmlContent, 'to:', content);
            return content;
        }

        // Function untuk konversi dari format sistem ke TinyMCE HTML
        function convertFromSystemToTinyMCE(systemContent) {
            if (!systemContent) return '';

            var content = String(systemContent);

            // Decode entity yang sudah lolos sebagai teks (&lt;sup&gt; → <sup>)
            content = content
                .replace(/&lt;(\/?sup(?:\s[^&]*)?)&gt;/gi, '<$1>')
                .replace(/&lt;(\/?sub(?:\s[^&]*)?)&gt;/gi, '<$1>')
                .replace(/&lt;sup&gt;/gi, '<sup>')
                .replace(/&lt;\/sup&gt;/gi, '</sup>')
                .replace(/&lt;sub&gt;/gi, '<sub>')
                .replace(/&lt;\/sub&gt;/gi, '</sub>');

            // Sudah HTML (sup/sub) → jangan escape ulang
            if (/<\/?(?:sup|sub)\b/i.test(content)) {
                return content;
            }

            // Simpan ^( ) / _( ) ke placeholder dulu agar tidak ikut di-escape
            var placeholders = [];
            content = content.replace(/\^\(([^\)]*)\)/g, function (_m, inner) {
                var idx = placeholders.length;
                placeholders.push('<sup>' + inner + '</sup>');
                return '%%PH' + idx + '%%';
            });
            content = content.replace(/\_\(([^\)]*)\)/g, function (_m, inner) {
                var idx = placeholders.length;
                placeholders.push('<sub>' + inner + '</sub>');
                return '%%PH' + idx + '%%';
            });

            content = content.replace(/≤/g, '&le;');
            content = content.replace(/≥/g, '&ge;');
            content = content.replace(/±/g, '&plusmn;');
            // Escape sisa < > (nilai perbandingan), tanpa merusak placeholder
            content = content.replace(/</g, '&lt;');
            content = content.replace(/>/g, '&gt;');

            content = content.replace(/%%PH(\d+)%%/g, function (_m, i) {
                return placeholders[parseInt(i, 10)] || '';
            });

            return content;
        }

        // Handle submit form baku mutu
        $(document).ready(function() {
            $(document).on('click', '#btnSimpanBakuMutu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#formTambahBakuMutu').trigger('submit');
            });

            $('#formTambahBakuMutu').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if ($('#modal-tipe-nilai-group').is(':visible') && !$('#modal-tipe-nilai').val()) {
                    swal({
                        title: "Perhatian!",
                        text: "Tipe nilai baku mutu wajib dipilih untuk jenis sampel Makanan, Minuman, atau Lainnya.",
                        icon: "warning"
                    });
                    return false;
                }

                // Trigger TinyMCE save untuk memastikan content tersimpan ke textarea
                tinymce.triggerSave();

                // Konversi TinyMCE HTML ke format sistem sebelum submit
                var equalContent = '';
                var nilaiContent = '';

                if (tinymce.get('modal-equal')) {
                    equalContent = convertFromTinyMCEToSystem(tinymce.get('modal-equal').getContent());
                    $('#modal-equal').val(equalContent);
                }

                if (tinymce.get('modal-nilai-baku-mutu')) {
                    nilaiContent = convertFromTinyMCEToSystem(tinymce.get('modal-nilai-baku-mutu')
                        .getContent());
                    $('#modal-nilai-baku-mutu').val(nilaiContent);
                }

                var labCode = $(this).data('lab-code');
                var formData = $(this).serialize();

                // Tentukan route berdasarkan lab
                var route = '';
                if (labCode === 'MBI') {
                    route = '{{ route('elits-baku-mutu-mikro.store') }}';
                } else if (labCode === 'KIM') {
                    route = '{{ route('elits-baku-mutu-kimia.store') }}';
                } else {
                    route = '{{ route('elits-baku-mutu-mikro.store') }}'; // Default ke mikro
                }

                console.log('Submitting to:', route, 'Data:', formData);

                // Disable tombol submit
                $('#btnSimpanBakuMutu').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

                $.ajax({
                    url: route,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $(
                            '#csrf-token').val()
                    },
                    success: function(response) {
                        console.log('Response:', response);

                        if (response.status == true) {
                            $('#modalTambahBakuMutu').modal('hide');
                            if (typeof window.persistBacaHasilFormDraftBeforeJenisNav === 'function') {
                                window.persistBacaHasilFormDraftBeforeJenisNav();
                            }
                            swal({
                                title: "Berhasil!",
                                text: "Baku mutu berhasil ditambahkan. Memuat ulang halaman…",
                                icon: "success"
                            });
                            setTimeout(function() {
                                window.location.reload();
                            }, 900);
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '. <br>';
                                });
                            } else {
                                pesan = response.pesan;
                            }

                            swal({
                                title: "Error!",
                                html: pesan,
                                icon: "warning"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText);

                        var errorMsg = 'Terjadi kesalahan saat menyimpan baku mutu.';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            // Use default error message
                        }

                        swal({
                            title: "Error!",
                            text: errorMsg,
                            icon: "error"
                        });
                    },
                    complete: function() {
                        // Re-enable tombol submit
                        $('#btnSimpanBakuMutu').prop('disabled', false).html(
                            '<i class="fa fa-save mr-1"></i>Simpan Baku Mutu');
                    }
                });

                return false;
            });
        });

        // BSRE Configuration
        const BSRE_USE = {{ config('app.bsre_use', false) ? 'true' : 'false' }};

        // Global variables untuk menyimpan nama petugas dan form class name
        var namaPetugasValue = null;
        var formClassNameValue = null;

        // Function to convert datetime-local format (YYYY-MM-DDTHH:mm) to d/m/Y H:i
        function convertDateTimeFormat(dateTimeValue) {
            if (!dateTimeValue) return '';

            // If already in d/m/Y H:i format, return as is
            if (dateTimeValue.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                return dateTimeValue;
            }

            // Convert from YYYY-MM-DDTHH:mm to d/m/Y H:i
            if (dateTimeValue.includes('T')) {
                const [datePart, timePart] = dateTimeValue.split('T');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            // If format is YYYY-MM-DD HH:mm:ss, convert it
            if (dateTimeValue.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/)) {
                const [datePart, timePart] = dateTimeValue.split(' ');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            return dateTimeValue;
        }

        // Function to convert date inputs in form before submission
        function convertFormDates(form) {
            if (!form) return;

            // For text inputs with flatpickr, get the formatted value directly
            const startDateInput = form.querySelector('#start_date_verifikasi_baca_hasil');
            const stopDateInput = form.querySelector('#stop_date_verifikasi_baca_hasil');

            if (startDateInput) {
                const flatpickrInstance = startDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        startDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting start date:', e);
                    }
                }
            }

            if (stopDateInput) {
                const flatpickrInstance = stopDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        stopDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting stop date:', e);
                    }
                }
            }
        }

        function checkNikAndPassword(namaPetugas, className) {
            namaPetugasValue = namaPetugas;
            formClassNameValue = className;
            event.preventDefault();

            const form = document.querySelector(`.${className}`);
            if (!form) {
                console.error('Form not found:', className);
                return;
            }

            // Convert date formats before submission
            convertFormDates(form);

            if (BSRE_USE === true || BSRE_USE === 'true') {
                // Wajib input popup
                $('#inputNikAndPasword').modal('show');
            } else {
                // Tidak pakai BSRE, langsung submit
                form.submit();
            }
        }

        function submitNikAndPassword() {
            event.preventDefault();

            if (namaPetugasValue != null) {
                // Jangan simpan DB, kirim ke server via endpoint session sekali-pakai
                const formData = {
                    nik: document.getElementById("nikPetugas").value,
                    password: document.getElementById("passwordPetugas").value,
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: "{{ url('elits-samples/update-petugas') }}/" + encodeURIComponent(namaPetugasValue),
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        if (response === "true") {
                            $('#inputNikAndPasword').modal('hide');
                            // submit form yang diminta sebelumnya
                            if (formClassNameValue) {
                                const form = document.querySelector(`.${formClassNameValue}`);
                                if (form) {
                                    // Convert dates again before submitting
                                    convertFormDates(form);
                                    form.submit();
                                }
                            } else {
                                // Fallback: submit form aktif terakhir di halaman
                                const forms = document.querySelectorAll('form');
                                if (forms && forms.length) {
                                    const lastForm = forms[forms.length - 1];
                                    convertFormDates(lastForm);
                                    lastForm.submit();
                                }
                            }
                        } else {
                            alert('Gagal mengirim kredensial BSRE.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan jaringan.');
                    }
                })
            }
        }

        // Initialize Flatpickr untuk form verifikasi baca hasil
        $(document).ready(function() {
            @php
                // Convert default dates to JavaScript Date objects for flatpickr
                $js_start_date = $default_start_date_verifikasi ? $default_start_date_verifikasi->format('Y-m-d') : '';
                $js_stop_date = $default_stop_date_verifikasi ? $default_stop_date_verifikasi->format('Y-m-d') : '';
            @endphp

            // Initialize Flatpickr for start date
            var startDatePicker = flatpickr("#start_date_verifikasi_baca_hasil", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_start_date_verifikasi)
                defaultDate: new Date("{{ $js_start_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

            // Initialize Flatpickr for stop date
            var stopDatePicker = flatpickr("#stop_date_verifikasi_baca_hasil", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_stop_date_verifikasi)
                defaultDate: new Date("{{ $js_stop_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

        });
    </script>

    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>

    <script>
        // BSRE Configuration
        const BSRE_USE = {{ config('app.bsre_use', false) ? 'true' : 'false' }};

        // Global variables untuk menyimpan nama petugas dan form class name
        var namaPetugasValue = null;
        var formClassNameValue = null;

        // Function to convert datetime-local format (YYYY-MM-DDTHH:mm) to d/m/Y H:i
        function convertDateTimeFormat(dateTimeValue) {
            if (!dateTimeValue) return '';

            // If already in d/m/Y H:i format, return as is
            if (dateTimeValue.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                return dateTimeValue;
            }

            // Convert from YYYY-MM-DDTHH:mm to d/m/Y H:i
            if (dateTimeValue.includes('T')) {
                const [datePart, timePart] = dateTimeValue.split('T');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            // If format is YYYY-MM-DD HH:mm:ss, convert it
            if (dateTimeValue.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/)) {
                const [datePart, timePart] = dateTimeValue.split(' ');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            return dateTimeValue;
        }

        // Function to convert date inputs in form before submission
        function convertFormDates(form) {
            if (!form) return;

            // For text inputs with flatpickr, get the formatted value directly
            const startDateInput = form.querySelector('#start_date_verifikasi_baca_hasil');
            const stopDateInput = form.querySelector('#stop_date_verifikasi_baca_hasil');

            if (startDateInput) {
                const flatpickrInstance = startDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        startDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting start date:', e);
                    }
                }
            }

            if (stopDateInput) {
                const flatpickrInstance = stopDateInput._flatpickr;
                if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                    try {
                        const formattedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y');
                        // Update the input value directly instead of creating hidden input
                        stopDateInput.value = formattedValue;
                    } catch (e) {
                        console.error('Error formatting stop date:', e);
                    }
                }
            }
        }

        function checkNikAndPassword(namaPetugas, className) {
            namaPetugasValue = namaPetugas;
            formClassNameValue = className;
            event.preventDefault();

            const form = document.querySelector(`.${className}`);
            if (!form) {
                console.error('Form not found:', className);
                return;
            }

            // Convert date formats before submission
            convertFormDates(form);

            if (BSRE_USE === true || BSRE_USE === 'true') {
                // Wajib input popup
                $('#inputNikAndPasword').modal('show');
            } else {
                // Tidak pakai BSRE, langsung submit
                form.submit();
            }
        }

        function submitNikAndPassword() {
            event.preventDefault();

            if (namaPetugasValue != null) {
                // Jangan simpan DB, kirim ke server via endpoint session sekali-pakai
                const formData = {
                    nik: document.getElementById("nikPetugas").value,
                    password: document.getElementById("passwordPetugas").value,
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: "{{ url('elits-samples/update-petugas') }}/" + encodeURIComponent(namaPetugasValue),
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        if (response === "true") {
                            $('#inputNikAndPasword').modal('hide');
                            // submit form yang diminta sebelumnya
                            if (formClassNameValue) {
                                const form = document.querySelector(`.${formClassNameValue}`);
                                if (form) {
                                    // Convert dates again before submitting
                                    convertFormDates(form);
                                    form.submit();
                                }
                            } else {
                                // Fallback: submit form aktif terakhir di halaman
                                const forms = document.querySelectorAll('form');
                                if (forms && forms.length) {
                                    const lastForm = forms[forms.length - 1];
                                    convertFormDates(lastForm);
                                    lastForm.submit();
                                }
                            }
                        } else {
                            alert('Gagal mengirim kredensial BSRE.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan jaringan.');
                    }
                })
            }
        }

        // Initialize Flatpickr untuk form verifikasi baca hasil
        $(document).ready(function() {
            @php
                // Convert default dates to JavaScript Date objects for flatpickr
                $js_start_date = $default_start_date_verifikasi ? $default_start_date_verifikasi->format('Y-m-d') : '';
                $js_stop_date = $default_stop_date_verifikasi ? $default_stop_date_verifikasi->format('Y-m-d') : '';
            @endphp

            // Initialize Flatpickr for start date
            var startDatePicker = flatpickr("#start_date_verifikasi_baca_hasil", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_start_date_verifikasi)
                defaultDate: new Date("{{ $js_start_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

            // Initialize Flatpickr for stop date
            var stopDatePicker = flatpickr("#stop_date_verifikasi_baca_hasil", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                },
                @if ($default_stop_date_verifikasi)
                defaultDate: new Date("{{ $js_stop_date }}"),
                @endif
                onChange: function(selectedDates, dateStr, instance) {
                    // Auto-adjust to work hours if needed
                    if (selectedDates.length > 0) {
                        var date = selectedDates[0];
                        var hour = date.getHours();
                        if (hour < 8) {
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        } else if (hour >= 15) {
                            date.setDate(date.getDate() + 1);
                            date.setHours(8, 0, 0, 0);
                            instance.setDate(date);
                        }
                    }
                }
            });

        });
    </script>

    <!-- TinyMCE Editor Modal -->
    <div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editorModalLabel">
                        <i class="fa fa-edit mr-2"></i>Editor Hasil
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle mr-2"></i>
                        <strong>Tips Penggunaan Editor:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Ketik angka atau teks hasil pengujian</li>
                            <li>Untuk <strong>pangkat (superscript)</strong>: pilih angka → klik tombol
                                <strong>x<sup>2</sup></strong> di toolbar. Contoh: H<sup>+</sup>, 10<sup>3</sup>
                            </li>
                            <li>Untuk <strong>subscript</strong>: pilih angka → klik tombol <strong>x<sub>2</sub></strong>
                                di toolbar. Contoh: H<sub>2</sub>O, CO<sub>2</sub></li>
                            <li>Untuk <strong>simbol matematika</strong> (≤, ≥, ±, <,>): klik tombol <strong>Ω
                                        (Charmap)</strong> di toolbar</li>
                            <li>Hasil akan otomatis dikonversi ke format sistem saat disimpan</li>
                        </ul>
                    </div>

                    {{-- Container untuk editor berbasis pilihan (dropdown) --}}
                    <div id="editor_option_container" style="display:none; margin-bottom: 1rem;">
                        <label class="font-weight-bold" for="editor_option_select">
                            <i class="fa fa-list mr-1"></i>Pilih Hasil
                        </label>
                        <select id="editor_option_select" class="form-control">
                        </select>
                    </div>

                    {{-- Container untuk TinyMCE (hasil free-text) --}}
                    <div id="editor_text_container">
                        <textarea id="editor_content" name="editor_content"></textarea>
                    </div>

                    <div id="editor_metode_container" style="display:none; margin-top: 1rem; border-top: 1px solid #dee2e6; padding-top: 1rem;">
                        <label class="font-weight-bold" for="editor_metode_content">
                            <i class="fa fa-flask mr-1"></i>Metode Pengujian
                        </label>
                        <textarea id="editor_metode_content" class="form-control" rows="3"
                            placeholder="Metode / acuan pengujian (SNI, modifikasi, dll.)"></textarea>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="editor_metode_permanent">
                            <label class="custom-control-label" for="editor_metode_permanent">
                                Simpan permanen ke master metode (berlaku untuk semua sampel berikutnya)
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Jika tidak dicentang, perubahan metode hanya disimpan untuk sampel ini saat data disimpan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" id="saveEditorContent">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAndNextEditorContent">
                        <i class="fa fa-save mr-1"></i>Simpan & Lanjut
                        <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Baku Mutu -->
    <div class="modal fade" id="modalTambahBakuMutu" tabindex="-1" role="dialog"
        aria-labelledby="modalTambahBakuMutuLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahBakuMutuLabel">
                        <i class="fa fa-plus mr-2"></i>Tambah Baku Mutu
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formTambahBakuMutu" action="javascript:void(0);" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="is_sub" value="false">
                    <div class="modal-body" style="position: relative;">
                        <!-- Referensi dari jenis makanan / parameter lain di lab -->
                        <div class="card mb-4 border-info" id="modal-referensi-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0" id="modal-referensi-title">
                                    <i class="fa fa-copy mr-2"></i>Salin dari Referensi (Opsional)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-2">
                                    <label for="modal-referensi-baku-mutu">
                                        <i class="fa fa-copy mr-1"></i>Pilih baku mutu referensi
                                    </label>
                                    <select id="modal-referensi-baku-mutu" class="form-control">
                                        <option value="">— Buat baru tanpa referensi —</option>
                                    </select>
                                    <small class="form-text text-muted" id="modal-referensi-hint">
                                        Memilih referensi akan mengisi form di bawah. Parameter tujuan tetap parameter yang sedang dipilih.
                                    </small>
                                    <div id="modal-referensi-empty" class="alert alert-light border mt-2 mb-0 py-2"
                                        style="display:none;">
                                        <i class="fa fa-info-circle text-muted mr-1"></i>
                                        Belum ada baku mutu referensi untuk jenis sampel ini di lab.
                                    </div>
                                    <div id="modal-referensi-loading" class="text-muted small mt-2" style="display:none;">
                                        <i class="fa fa-spinner fa-spin mr-1"></i>Memuat daftar referensi...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Dasar Parameter -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Dasar Parameter</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-flask mr-1"></i>Jenis Sampel</label>
                                            <input type="text" id="modal-sample-type-display" class="form-control"
                                                readonly>
                                            <input type="hidden" id="modal-sampletype-id" name="sampletype_id">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-list mr-1"></i>Parameter</label>
                                            <input type="text" id="modal-method-display" class="form-control"
                                                readonly>
                                            <input type="hidden" id="modal-method-id" name="method_id">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" id="modal-jenis-makanan-group" style="display: none;">
                                    <label><i class="fa fa-utensils mr-1"></i>Jenis Makanan <span
                                            class="badge badge-danger ml-1">Wajib</span></label>
                                    <input type="text" id="modal-jenis-makanan-display" class="form-control" readonly>
                                    <input type="hidden" id="modal-jenis-makanan-id" name="jenis_makanan_id">
                                </div>

                                <div class="form-group" id="modal-tipe-nilai-group" style="display: none;">
                                    <label><i class="fa fa-balance-scale mr-1"></i>Tipe Nilai Baku Mutu <span
                                            class="badge badge-danger ml-1">Wajib</span></label>
                                    <select id="modal-tipe-nilai" name="tipe_nilai_baku_mutu" class="form-control">
                                        <option value="" selected disabled>Pilih Tipe Nilai Baku Mutu</option>
                                        <option value="kuantitatif">Kuantitatif</option>
                                        <option value="kualitatif">Kualitatif</option>
                                    </select>
                                    <small class="form-text text-muted">Wajib untuk jenis sampel Makanan, Minuman, atau Lainnya.</small>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-file-alt mr-1"></i>Nama Parameter di Laporan</label>
                                    <input type="text" id="modal-name-report" name="name_report" class="form-control"
                                        placeholder="Nama Parameter di Laporan">
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-book mr-1"></i>Acuan Baku Mutu</label>
                                    <select name="library_id" id="modal-library-id" class="form-control">
                                        <option value="">Pilih Acuan Baku Mutu</option>
                                        @if (isset($libraries))
                                            @foreach ($libraries as $library)
                                                <option value="{{ $library->id_library }}">
                                                    {{ $library->title_library }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-ruler mr-1"></i>Satuan</label>
                                    <select id="modal-unit-id" name="unit_id" class="form-control">
                                        <option value="">Pilih Satuan</option>
                                        @if (isset($units))
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id_unit }}">{!! rubahNilaikeHtml($unit->shortname_unit) !!}</option>
                                            @endforeach
                                        @endif
                                        <option value="-">-</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Konfigurasi Baku Mutu -->
                        <div class="card mb-4" id="modal-konfigurasi-card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fa fa-sliders mr-2"></i>Konfigurasi Baku Mutu</h5>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                                    <h6 class="mb-3"><i class="fa fa-chart-line mr-2"></i>Nilai Baku Mutu</h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fa fa-arrow-down mr-1"></i>Min (Minimum)</label>
                                                <input type="text" class="form-control" id="modal-min"
                                                    name="min_no_sub" placeholder="Contoh: 4.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)</label>
                                                <input type="text" class="form-control" id="modal-max"
                                                    name="max_no_sub" placeholder="Contoh: 6.5">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fa fa-equals mr-1"></i>Nilai Sama Dengan</label>
                                        <textarea class="form-control" id="modal-equal" name="equal_no_sub" rows="3" placeholder="Contoh: Negatif"></textarea>
                                        <small class="form-text text-muted">Untuk nilai non-range seperti
                                            Positif/Negatif</small>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan</label>
                                        <textarea class="form-control" id="modal-nilai-baku-mutu" name="nilai_baku_mutu_no_sub" rows="3"
                                            placeholder="Nilai Baku Mutu"></textarea>
                                        <small class="form-text text-muted">Teks yang akan muncul di laporan hasil</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i>Batal
                        </button>
                        <button type="button" class="btn btn-success" id="btnSimpanBakuMutu">
                            <i class="fa fa-save mr-1"></i>Simpan Baku Mutu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Input NIK dan Password -->
    <div class="modal fade" id="inputNikAndPasword" tabindex="-1" aria-labelledby="inputNikAndPassword"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-body-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inputNikAndPassword">Input NIK dan Password BSRE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nikPetugas">NIK</label>
                            <input type="text" class="form-control" name="nik" id="nikPetugas"
                                placeholder="Nomor Induk Kependudukan" required>
                        </div>
                        <div class="form-group mt-2">
                            <label for="passwordPetugas">Password</label>
                            <input type="text" class="form-control" name="password" id="passwordPetugas"
                                placeholder="Password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitNikAndPassword()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Number Format Helper - Required for parseNumberInput function -->
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>

    <!-- Analis Inline Editing Script -->
    <script src="{{ asset('assets/js/analis-inline-editing.js') }}?v={{ filemtime(public_path('assets/js/analis-inline-editing.js')) }}"></script>

    <script>
        // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
        window.convertToTinyMCE = function(value) {
            if (!value) return '';
            // Simple direct replacement
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            // Convert ^() to <sup> and _() to <sub>
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            return value;
        };

        // Convert from HTML <sup> and <sub> to ^() and _() format for our system
        window.convertFromTinyMCE = function(value) {
            if (!value) return '';
            // Convert HTML tags to ^() and _() format
            // IMPORTANT: Handle tags with attributes (e.g., <sup style="...">)
            // Use [^>]* to match any attributes before the closing >
            value = value.replace(/<sup[^>]*>([^<]*)<\/sup>/gi, '^($1)');
            value = value.replace(/<sub[^>]*>([^<]*)<\/sub>/gi, '_($1)');
            // Strip any remaining HTML tags
            value = value.replace(/<[^>]*>/g, '');
            // Decode HTML entities
            value = value.replace(/&le;/gi, '≤');
            value = value.replace(/&ge;/gi, '≥');
            value = value.replace(/&lt;/g, '<');
            value = value.replace(/&gt;/g, '>');
            value = value.replace(/&plusmn;/g, '±');
            value = value.replace(/&nbsp;/g, ' ');
            return value;
        };

        // Parser numerik lab: A x 10^C, Unicode superscript, <sup>, dll. (selaras PHP kesmas_parse_print_numeric)
        window.parseLabNumeric = function(raw) {
            if (raw === null || raw === undefined || raw === '' || raw === '-') {
                return null;
            }
            if (typeof raw === 'number' && !isNaN(raw)) {
                return raw;
            }

            var s = String(raw);
            s = s.replace(/10\s*<sup>\s*([+\-]?\d+)\s*<\/sup>/gi, '10^$1');
            s = s.replace(/<[^>]*>/g, '');
            s = s.replace(/&nbsp;/gi, ' ').replace(/&lt;/gi, '<').replace(/&gt;/gi, '>');
            s = s.replace(/&times;/gi, 'x').replace(/&#215;/g, 'x').replace(/&#x00d7;/gi, 'x');

            var superMap = {
                '⁰': '0', '¹': '1', '²': '2', '³': '3', '⁴': '4',
                '⁵': '5', '⁶': '6', '⁷': '7', '⁸': '8', '⁹': '9',
                '⁺': '+', '⁻': '-'
            };
            s = s.replace(/10\s*([⁰¹²³⁴⁵⁶⁷⁸⁹⁺⁻]+)/g, function(_m, digits) {
                var d = '';
                for (var i = 0; i < digits.length; i++) {
                    d += (superMap[digits.charAt(i)] !== undefined) ? superMap[digits.charAt(i)] : digits.charAt(i);
                }
                return '10^' + d;
            });

            s = s.replace(/[×⋅·]/g, 'x');
            s = s.replace(/\s+/g, ' ').trim();

            var m = s.match(/([\d.]+)\s*[xX*]\s*10\s*\^?\s*\{?([+\-]?\d+)\}?/);
            if (m) {
                return parseFloat(m[1]) * Math.pow(10, parseInt(m[2], 10));
            }

            m = s.match(/^\s*([\d.]+)\s*$/);
            if (m) {
                return parseFloat(m[1]);
            }

            if (typeof parseNumberInput === 'function') {
                var p = parseNumberInput(s, 'en');
                if (p !== null && !isNaN(p)) {
                    return p;
                }
            }

            var n = parseFloat(s.replace(/,/g, '.'));
            return isNaN(n) ? null : n;
        };

        // Function to check baku mutu (compatible with baca-hasil format)
        // nilaiBakuMutuRaw: teks tampilan baku mutu (mis. "< 3" / "1.0 x 10⁵") — dipakai jika min/max DB kosong
        window.checkBakuMutu = function(value, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutuParam, numberFormat, nilaiBakuMutuRaw) {
            if (!value || value === '' || value === '-') return '';

            nilaiBakuMutuRaw = (nilaiBakuMutuRaw === undefined || nilaiBakuMutuRaw === null) ? '' : String(nilaiBakuMutuRaw);
            numberFormat = numberFormat || 'en';
            // Normalize offset value to ensure consistent comparison (case-insensitive)
            offset_baku_mutu = String(offset_baku_mutu || 'default').trim().toLowerCase();
            if (offset_baku_mutu !== 'true' && offset_baku_mutu !== 'false') {
                offset_baku_mutu = 'default';
            }

            var melewati = false;
            var kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';

            // Check manual override FIRST - these take precedence over automatic checking
            // offset_baku_mutu = 'false' means "Tidak Dianggap Melewati" -> badge hijau (success)
            // offset_baku_mutu = 'true' means "Dianggap Melewati" -> badge merah (danger)
            // offset_baku_mutu = 'default' means let system decide based on comparison
            if (offset_baku_mutu === 'false') {
                // Manual override: Tidak melewati baku mutu (hijau), berapapun nilainya
                var formattedValue = toFormatHtml(value || '');
                return createResultBadge(formattedValue, 'success');
            } else if (offset_baku_mutu === 'true') {
                // Manual override: Melewati baku mutu (merah), berapapun nilainya
                var formattedValue = toFormatHtml(value || '');
                return createResultBadge(formattedValue, 'danger');
            } else {
                // Default: Check automatically
                    var valueForComparison = value;
                    if (typeof value === 'string' && (value.includes('<sup') || value.includes('<sub'))) {
                        valueForComparison = value.replace(/<[^>]*>/g, '');
                    }

                    var normalizeBmDisplay = function(s) {
                        if (!s) return '';
                        return String(s).replace(/&lt;/gi, '<').replace(/&gt;/gi, '>').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
                    };

                    var parseThr = function(thrStr) {
                        var lab = window.parseLabNumeric(thrStr);
                        if (lab !== null) {
                            return lab;
                        }
                        if (typeof parseNumberInput === 'function') {
                            var p = parseNumberInput(String(thrStr), numberFormat || 'en');
                            if (p !== null && !isNaN(p)) {
                                return p;
                            }
                        }
                        return parseFloat(String(thrStr).replace(/,/g, '.'));
                    };

                    var numValue = window.parseLabNumeric(valueForComparison);
                    if (numValue === null && typeof parseNumberInput === 'function') {
                        numValue = parseNumberInput(String(valueForComparison).trim(), numberFormat);
                    }

                    var numMin = window.parseLabNumeric(min);
                    var numMax = window.parseLabNumeric(max);

                    var equalStr = (equal !== undefined && equal !== null) ? String(equal) : '';
                    // equal kadang berisi "< 3" — jangan bandingkan string dengan hasil "1"
                    var equalLooksInequality = equalStr !== '' && /[<>≤≥]/.test(equalStr);
                    var equalAsMax = (!equalLooksInequality) ? window.parseLabNumeric(equalStr) : null;

                    var nbm = normalizeBmDisplay(nilaiBakuMutuRaw);
                    if (!nbm && equalLooksInequality) {
                        nbm = normalizeBmDisplay(equalStr);
                    }
                    // Batas atas sering hanya di teks baku mutu bentuk A x 10^C
                    if (numMax === null && nbm !== '') {
                        var nbmNum = window.parseLabNumeric(nbm);
                        if (nbmNum !== null && !/[<>≤≥]/.test(nbm)) {
                            numMax = nbmNum;
                        }
                    }
                    if (numMax === null && equalAsMax !== null) {
                        numMax = equalAsMax;
                    }

                    var isSimpleNumericResult = (numValue !== null && !isNaN(numValue));
                    var hasMin = (numMin !== null && !isNaN(numMin));
                    var hasMax = (numMax !== null && !isNaN(numMax));

                    // Teks "< n" / "≤ n" / "> n" dulu — jangan pakai kesamaan string "3" vs hasil "1" jika ada teks baku mutu
                    var handledFromNilaiTeks = false;
                    if (nbm !== '' && isSimpleNumericResult) {
                        var mLe = nbm.match(/(?:<=|≤)\s*([\d.,]+(?:\s*[xX×]\s*10(?:\^|[⁰¹²³⁴⁵⁶⁷⁸⁹])[+\-]?\d*)?)/);
                        if (!mLe) {
                            mLe = nbm.match(/(?:<=|≤)\s*([\d.,]+)/);
                        }
                        if (mLe) {
                            var thrLe = parseThr(mLe[1]);
                            if (!isNaN(thrLe)) {
                                melewati = (numValue > thrLe);
                                handledFromNilaiTeks = true;
                            }
                        } else if (/<\s*[\d.,]+/.test(nbm) && !/(?:<=|≤)/.test(nbm)) {
                            var mLt = nbm.match(/<\s*([\d.,]+(?:\s*[xX×]\s*10(?:\^|[⁰¹²³⁴⁵⁶⁷⁸⁹])[+\-]?\d*)?)/);
                            if (!mLt) {
                                mLt = nbm.match(/<\s*([\d.,]+)/);
                            }
                            if (mLt) {
                                var thrLt = parseThr(mLt[1]);
                                if (!isNaN(thrLt)) {
                                    melewati = (numValue >= thrLt);
                                    handledFromNilaiTeks = true;
                                }
                            }
                        } else {
                            var mGe = nbm.match(/(?:>=|≥)\s*([\d.,]+)/);
                            if (mGe) {
                                var thrGe = parseThr(mGe[1]);
                                if (!isNaN(thrGe)) {
                                    melewati = (numValue < thrGe);
                                    handledFromNilaiTeks = true;
                                }
                            } else if (/>\s*[\d.,]+/.test(nbm) && !/(?:>=|≥)/.test(nbm)) {
                                var mGt = nbm.match(/>\s*([\d.,]+)/);
                                if (mGt) {
                                    var thrGt = parseThr(mGt[1]);
                                    if (!isNaN(thrGt)) {
                                        melewati = (numValue <= thrGt);
                                        handledFromNilaiTeks = true;
                                    }
                                }
                            }
                        }
                    }

                    if (!handledFromNilaiTeks && equalStr !== '' && !equalLooksInequality && equalAsMax === null) {
                        // Equal kualitatif (teks) — bandingkan string
                        var normalizedValue = String(valueForComparison).replace(/\s+/g, '').trim();
                        var normalizedEqual = equalStr.replace(/\s+/g, '').trim();
                        melewati = (normalizedValue.toLowerCase() !== normalizedEqual.toLowerCase());
                    } else if (!handledFromNilaiTeks) {
                        if (hasMin && hasMax) {
                            if (isSimpleNumericResult) {
                                melewati = (numValue < numMin || numValue > numMax);
                            }
                        } else if (hasMin) {
                            if (isSimpleNumericResult) {
                                // Baku mutu "kadar maks < n" kadang tersimpan di min=n; jika teks menunjukkan < n sama dengan min, anggap batas atas
                                var nbmForUpper = nbm;
                                var useUpperBoundFromLt = false;
                                if (nbmForUpper !== '') {
                                    var mLtUb = nbmForUpper.match(/<\s*([\d.,]+)/);
                                    if (mLtUb && !/(?:<=|≤)/.test(nbmForUpper)) {
                                        var thrUb = parseThr(mLtUb[1]);
                                        if (!isNaN(thrUb) && Math.abs(thrUb - numMin) < 1e-9) {
                                            useUpperBoundFromLt = true;
                                        }
                                    }
                                }
                                if (useUpperBoundFromLt) {
                                    melewati = (numValue >= numMin);
                                } else {
                                    melewati = (numValue < numMin);
                                }
                            }
                        } else if (hasMax) {
                            if (isSimpleNumericResult) {
                                melewati = (numValue > numMax);
                            }
                        }
                    }

                var status = melewati ? 'danger' : 'success';
                // Use original value (with HTML) for display
                var formattedValue = toFormatHtml(value || '');
                var badge = createResultBadge(formattedValue, status);

                if (kesimpulanBakuMutu && kesimpulanBakuMutu.trim() !== '') {
                    var kesimpulanFormatted = toFormatHtml(kesimpulanBakuMutu || '');
                    badge += '<br><small class="text-info mt-1"><i class="fa fa-info-circle"></i> ' +
                        kesimpulanFormatted + '</small>';
                }

                return badge;
            }
        };

        // Format value for display (convert ^() to HTML)
        function toFormatHtml(value) {
            if (!value) return '';
            var str = String(value);

            // Convert Unicode superscript characters to <sup> tags FIRST
            // This handles characters like ³, ², ¹, etc.
            str = str.replace(/¹/g, '<sup>1</sup>');
            str = str.replace(/²/g, '<sup>2</sup>');
            str = str.replace(/³/g, '<sup>3</sup>');
            str = str.replace(/⁴/g, '<sup>4</sup>');
            str = str.replace(/⁵/g, '<sup>5</sup>');
            str = str.replace(/⁶/g, '<sup>6</sup>');
            str = str.replace(/⁷/g, '<sup>7</sup>');
            str = str.replace(/⁸/g, '<sup>8</sup>');
            str = str.replace(/⁹/g, '<sup>9</sup>');
            str = str.replace(/⁰/g, '<sup>0</sup>');

            // If value already contains HTML tags (like <sup> or <sub>), preserve them
            // But still check for any remaining ^( or _( notation that might be mixed in
            if (str.includes('<sup') || str.includes('<sub')) {
                // Still convert any remaining ^( or _( notation that might be in the text
                str = str.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                str = str.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                // Also handle format without parentheses
                str = str.replace(/\^(\d+)/g, '<sup>$1</sup>');
                str = str.replace(/\_(\d+)/g, '<sub>$1</sub>');
                // Ensure special characters are encoded
                str = str.replace(/≤/g, '&le;');
                str = str.replace(/≥/g, '&ge;');
                str = str.replace(/±/g, '&plusmn;');
                return str;
            }
            // Convert text format to HTML
            // IMPORTANT: $1 captures only the content inside parentheses, not the parentheses themselves
            str = str.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            str = str.replace(/\^(\d+)/g, '<sup>$1</sup>'); // Handle ^2 format
            str = str.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            str = str.replace(/\_(\d+)/g, '<sub>$1</sub>'); // Handle _2 format
            str = str.replace(/≤/g, '&le;');
            str = str.replace(/≥/g, '&ge;');
            str = str.replace(/±/g, '&plusmn;');
            return str;
        }

        // Create result badge based on status
        function createResultBadge(value, status) {
            if (value === undefined || value === null) {
                value = '';
            }
            value = String(value || '');

            var badgeClass = status === 'success' ? 'badge-success' : 'badge-danger';
            var icon = status === 'success' ? 'fa-check-circle' : 'fa-times-circle';
            var warningIcon = status === 'danger' ? ' <i class="fa fa-exclamation-triangle ml-1"></i>' : '';

            // Ensure sup/sub tags have proper styling
            value = value.replace(/<sup>/g, '<sup style="vertical-align: super; font-size: 0.75em; line-height: 0; position: relative; top: -0.4em;">');
            value = value.replace(/<sub>/g, '<sub style="vertical-align: sub; font-size: 0.75em; line-height: 0; position: relative; bottom: -0.25em;">');

            return '<span class="badge ' + badgeClass +
                ' font-weight-bold" style="font-size: 14px; padding: 8px 12px; line-height: 1.4;"><i class="fa ' + icon +
                ' mr-1"></i>' + value + warningIcon +
                '</span>';
        }

        // Global error handler for TinyMCE to prevent "Node cannot be null" errors
        if (typeof window.addEventListener !== 'undefined') {
            window.addEventListener('error', function(e) {
                if (e.message && e.message.indexOf('Node cannot be null') !== -1) {
                    console.warn('TinyMCE node error caught and suppressed:', e.message);
                    e.preventDefault();
                    return true;
                }
            }, true);
        }

        // Override tinymce.get to add safety checks
        if (typeof tinymce !== 'undefined' && typeof tinymce.get === 'function') {
            var originalTinymceGet = tinymce.get;
            tinymce.get = function(id) {
                try {
                    var editor = originalTinymceGet.call(tinymce, id);
                    if (editor) {
                        // Verify editor element exists in DOM
                        var $editorEl = $('#' + id);
                        if ($editorEl.length === 0) {
                            console.warn('TinyMCE editor element not found in DOM:', id);
                            return null;
                        }
                    }
                    return editor;
                } catch(e) {
                    console.error('Error getting TinyMCE editor:', id, e);
                    return null;
                }
            };
        }

        $(document).one('analisEditorReady', function() {
            setTimeout(function() {
                if (typeof window.restoreBacaHasilFormDraftIfAny === 'function') {
                    window.restoreBacaHasilFormDraftIfAny();
                }
            }, 300);
        });

        // Wait for TinyMCE and then initialize AnalisInlineEditor
        var tinyMCERetryCount = 0;
        var maxTinyMCERetries = 50; // 5 seconds max wait

        function waitForTinyMCE() {
            if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                console.log('TinyMCE loaded successfully, waiting for DOM elements...');
                // Wait longer to ensure DOM elements are created
                setTimeout(function() {
                    if (typeof AnalisInlineEditor !== 'undefined') {
                        console.log('Initializing AnalisInlineEditor...');
                        AnalisInlineEditor.init();
                    } else {
                        console.error('AnalisInlineEditor not found');
                    }
                }, 800);
            } else {
                tinyMCERetryCount++;
                if (tinyMCERetryCount < maxTinyMCERetries) {
                    console.log('Waiting for TinyMCE... (attempt ' + tinyMCERetryCount + '/' + maxTinyMCERetries + ')');
                    setTimeout(waitForTinyMCE, 100);
                } else {
                    console.error('TinyMCE failed to load after ' + maxTinyMCERetries + ' attempts. Please check if TinyMCE script is loaded correctly.');
                    // Try to initialize anyway if AnalisInlineEditor is available
                    if (typeof AnalisInlineEditor !== 'undefined') {
                        console.log('Attempting to initialize AnalisInlineEditor without TinyMCE...');
                        AnalisInlineEditor.init();
                    }
                }
            }
        }

        // Check if TinyMCE is already loaded
        if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
            console.log('TinyMCE already loaded');
            $(document).ready(function() {
                waitForTinyMCE();
            });
        } else {
            // Wait for TinyMCE to load
            $(document).ready(function() {
                waitForTinyMCE();
            });
        }

        // Also try on window load as fallback
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                    console.log('TinyMCE confirmed loaded on window load');
                } else {
                    console.warn('TinyMCE still not loaded on window load');
                }

                if (typeof AnalisInlineEditor !== 'undefined' && !AnalisInlineEditor.initialized) {
                    console.log('Fallback: Initializing AnalisInlineEditor on window load...');
                    AnalisInlineEditor.init();
                }
                setTimeout(function() {
                    try {
                        var k = typeof window.bacaHasilFormDraftStorageKey === 'function'
                            ? window.bacaHasilFormDraftStorageKey()
                            : '';
                        if (k && sessionStorage.getItem(k) && typeof window.restoreBacaHasilFormDraftIfAny === 'function') {
                            window.restoreBacaHasilFormDraftIfAny();
                        }
                    } catch (e) { /* ignore */ }
                }, 3200);
            }, 1500);
        });

        // === BAKU MUTU MODAL HANDLERS ===
        // Handler for Baku Mutu Override button
        $(document).on('click', '.btn-baku-mutu-override', function() {
            var $btn = $(this);
            var index = $btn.data('index');
            var isSub = $btn.data('is-sub') == '1';

            // Find the row and get parameter name
            var $row = $btn.closest('tr');
            // For baca-hasil, parameter name is in the second column (Jenis Parameter)
            var $paramTd = $row.find('td').eq(1); // Second column (index 1)
            var parameterName = '';

            if ($paramTd.length > 0) {
                // Get text from the td, remove HTML tags and trim
                parameterName = $paramTd.clone().children().remove().end().text().trim();
                // If empty, try to get from b tag or strong tag
                if (!parameterName) {
                    parameterName = $paramTd.find('b, strong').first().text().trim();
                }
                // If still empty, get all text
                if (!parameterName) {
                    parameterName = $paramTd.text().trim();
                }
            }

            // Fallback: if still empty, use first td
            if (!parameterName) {
                parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
            }

            // Get current offset - for baca-hasil, ID format is offset_baku_mutu_{method_id} or offset_baku_mutu_{detail_id}
            // The index from button is actually the method_id or detail_id
            var offsetInputId = 'offset_baku_mutu_' + index;

            // Try multiple ways to find the offset input
            var $offsetInput = $('#' + offsetInputId);
            var currentOffset = 'default';

            if ($offsetInput.length === 0) {
                // Try by name attribute
                $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
            }
            if ($offsetInput.length === 0 && $row.length > 0) {
                // Try to find in row
                $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"], input[name="offset_baku_mutu_' + index + '"]');
            }
            if ($offsetInput.length === 0 && $row.length > 0) {
                // Try to find any offset input in row
                if (isSub) {
                    $offsetInput = $row.find('input[name*="offset_baku_mutu"][name*="detail"]');
                } else {
                    $offsetInput = $row.find('input[name*="offset_baku_mutu"]').not('[name*="detail"]');
                }
            }

            if ($offsetInput.length > 0) {
                currentOffset = String($offsetInput.val() || 'default').trim().toLowerCase();
                console.log('Found offset input on button click:', offsetInputId, 'Value:', currentOffset);
            } else {
                console.warn('Offset input not found on button click for index:', index, 'Using default');
            }

            // Normalize currentOffset to ensure it's 'true', 'false', or 'default'
            if (currentOffset !== 'true' && currentOffset !== 'false') {
                currentOffset = 'default';
            }

            // Also update button's data attribute to keep it in sync
            $btn.attr('data-current-offset', currentOffset);

            // Set parameter name in modal
            $('#bakuMutuParamName').text(parameterName);

            console.log('Setting modal radio button to:', currentOffset);

            // Store data in modal for later use BEFORE showing modal
            $('#bakuMutuModal').data('index', index);
            $('#bakuMutuModal').data('is-sub', isSub);
            $('#bakuMutuModal').data('offset-input-id', offsetInputId);
            $('#bakuMutuModal').data('current-offset', currentOffset); // Store current offset

            console.log('Modal data stored:', {
                index: index,
                isSub: isSub,
                offsetInputId: offsetInputId,
                currentOffset: currentOffset
            });

            // Set current selection - ensure we normalize the value
            $('input[name="baku-mutu-offset"]').prop('checked', false);
            var $targetRadio = $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]');
            if ($targetRadio.length > 0) {
                $targetRadio.prop('checked', true);
                console.log('Radio button set successfully to:', currentOffset);
            } else {
                console.error('Radio button not found for value:', currentOffset);
                // Fallback: set to default
                $('input[name="baku-mutu-offset"][value="default"]').prop('checked', true);
            }

            // Show modal
            $('#bakuMutuModal').modal('show');
        });

        // Handler for modal shown event - refresh current offset when modal is opened
        $('#bakuMutuModal').on('shown.bs.modal', function() {
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';
            var offsetInputId = $('#bakuMutuModal').data('offset-input-id');
            var storedOffset = $('#bakuMutuModal').data('current-offset'); // Get stored offset

            console.log('Modal shown, refreshing offset for index:', index, 'Stored offset:', storedOffset);

            if (index !== undefined && index !== null) {
                // Get current offset directly from hidden input (most reliable)
                if (!offsetInputId) {
                    offsetInputId = 'offset_baku_mutu_' + index;
                }

                // Try multiple ways to find the offset input
                var $offsetInput = $('#' + offsetInputId);
                if ($offsetInput.length === 0) {
                    $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
                }
                if ($offsetInput.length === 0) {
                    // Try to find in row
                    var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
                    if ($btn.length > 0) {
                        var $row = $btn.closest('tr');
                        if ($row.length > 0) {
                            $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"], input[name="offset_baku_mutu_' + index + '"]');
                        }
                    }
                }

                // Also try to get from button's data attribute as fallback
                var buttonOffset = null;
                var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
                if ($btn.length > 0) {
                    buttonOffset = $btn.attr('data-current-offset');
                    console.log('Button offset:', buttonOffset);
                }

                var currentOffset = 'default';
                if ($offsetInput.length > 0) {
                    currentOffset = String($offsetInput.val() || 'default').trim().toLowerCase();
                    console.log('Found offset input in modal shown:', offsetInputId, 'Value:', currentOffset);
                } else if (buttonOffset) {
                    currentOffset = String(buttonOffset).trim().toLowerCase();
                    console.log('Using button offset:', currentOffset);
                } else if (storedOffset) {
                    currentOffset = String(storedOffset).trim().toLowerCase();
                    console.log('Using stored offset:', currentOffset);
                } else {
                    console.warn('Offset input not found in modal shown for index:', index);
                }

                // Normalize currentOffset to ensure it's 'true', 'false', or 'default'
                if (currentOffset !== 'true' && currentOffset !== 'false') {
                    currentOffset = 'default';
                }

                console.log('Updating radio button in modal to:', currentOffset);

                // Update radio button selection to match the actual value
                $('input[name="baku-mutu-offset"]').prop('checked', false);
                var $targetRadio = $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]');
                if ($targetRadio.length > 0) {
                    $targetRadio.prop('checked', true);
                    console.log('Radio button updated successfully to:', currentOffset);
                } else {
                    console.error('Radio button not found for value:', currentOffset);
                    // Fallback: set to default
                    $('input[name="baku-mutu-offset"][value="default"]').prop('checked', true);
                }
            }
        });

        // Function to update baku mutu status
        function updateBakuMutuStatus(selectedOffset, index, isSub) {
            console.log('updateBakuMutuStatus called:', {selectedOffset: selectedOffset, index: index, isSub: isSub});

            // Normalize selectedOffset
            selectedOffset = String(selectedOffset || 'default').trim().toLowerCase();
            if (selectedOffset !== 'true' && selectedOffset !== 'false') {
                selectedOffset = 'default';
            }

            // Find the offset input field - for baca-hasil, ID format is offset_baku_mutu_{method_id} or offset_baku_mutu_{detail_id}
            var offsetInputId = 'offset_baku_mutu_' + index;

            // First, try to find the row to search within it
            var $row = null;
            var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
            if ($btn.length > 0) {
                $row = $btn.closest('tr');
            }
            if ($row.length === 0) {
                // Try to find row by textarea ID
                $row = $('textarea#result_method_' + index).closest('tr');
            }
            if ($row.length === 0) {
                // Try to find row by any element with the index
                $row = $('[id*="' + index + '"]').first().closest('tr');
            }

            // Search within row first, then globally
            var $offsetInput = null;
            if ($row.length > 0) {
                $offsetInput = $row.find('#' + offsetInputId);
                if ($offsetInput.length === 0) {
                    $offsetInput = $row.find('input[name="offset_baku_mutu_' + index + '"]');
                }
                if ($offsetInput.length === 0) {
                    $offsetInput = $row.find('input[id^="offset_baku_mutu_"]');
                }
            }

            // If not found in row, try globally
            if ($offsetInput.length === 0) {
                $offsetInput = $('#' + offsetInputId);
            }
            if ($offsetInput.length === 0) {
                $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
            }

            console.log('Looking for offset input:', offsetInputId, 'Found:', $offsetInput ? $offsetInput.length : 0, 'Row found:', $row ? $row.length : 0);

            // If still not found, try to create it
            if (!$offsetInput || $offsetInput.length === 0) {
                console.warn('Offset input not found, attempting to create...');

                // Try to find row by textarea if not already found
                if (!$row || $row.length === 0) {
                    $row = $('textarea#result_method_' + index).closest('tr');
                }

                var $newInput = $('<input>').attr({
                    'type': 'hidden',
                    'name': 'offset_baku_mutu_' + index,
                    'id': 'offset_baku_mutu_' + index,
                    'value': selectedOffset
                });

                if ($row && $row.length > 0) {
                    // Try to find textarea in row to append near it
                    var $textarea = $row.find('textarea.result_method_klinik');
                    if ($textarea.length > 0) {
                        $newInput.insertAfter($textarea);
                        console.log('Created offset input after textarea in row');
                    } else {
                        // Append to form
                        var $form = $('#form-baca-hasil');
                        if ($form.length > 0) {
                            $form.append($newInput);
                            console.log('Created offset input in form');
                        }
                    }
                } else {
                    // If row not found, just append to form
                    var $form = $('#form-baca-hasil');
                    if ($form.length > 0) {
                        $form.append($newInput);
                        console.log('Created offset input in form (no row found)');
                    }
                }

                $offsetInput = $newInput;
            }

            if ($offsetInput && $offsetInput.length > 0) {
                // Update hidden input value
                var oldValue = $offsetInput.val();
                $offsetInput.val(selectedOffset);
                console.log('Updated hidden input value from', oldValue, 'to:', selectedOffset);
                console.log('Input name:', $offsetInput.attr('name'), 'Input id:', $offsetInput.attr('id'));

                // Ensure the input is inside the form
                var $form = $('#form-baca-hasil');
                if ($form.length > 0 && !$offsetInput.closest('#form-baca-hasil').length) {
                    console.warn('Offset input is not inside form, moving it...');
                    $offsetInput.appendTo($form);
                    console.log('Moved offset input to form');
                }

                // Update button appearance - try multiple selectors
                var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
                if ($btn.length === 0) {
                    // Try to find by is-sub attribute
                    $btn = $('.btn-baku-mutu-override[data-index="' + index + '"][data-is-sub="' + (isSub ? '1' : '0') + '"]');
                }
                console.log('Found button:', $btn.length, 'buttons');

                if ($btn.length > 0) {
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
                } else {
                    console.warn('Button not found for index:', index);
                }

                // Update badge by triggering validation
                // Try to find row from button first, if not found, try to find from offset input
                var $row = null;
                if ($btn.length > 0) {
                    $row = $btn.closest('tr');
                }
                if ($row.length === 0) {
                    $row = $offsetInput.closest('tr');
                }
                if ($row.length === 0) {
                    // Try to find row by textarea ID
                    $row = $('textarea#result_method_' + index).closest('tr');
                }

                console.log('Found row:', $row.length, 'rows');

                if ($row.length > 0) {
                    var $textarea = $row.find('textarea.result_method_klinik');
                    if ($textarea.length > 0) {
                        var textareaId = $textarea.attr('id');
                        var textareaIndex = $textarea.attr('data-index');
                        if (textareaIndex === undefined || textareaIndex === '') {
                            textareaIndex = index;
                        }
                        var currentValue = $textarea.val() || '';

                        console.log('Found textarea:', textareaId, 'Index:', textareaIndex, 'Current value:', currentValue);

                    // Get value from inline editor if exists
                    if (typeof tinymce !== 'undefined') {
                        var $editor = $row.find('.inline-hasil-editor[data-index="' + textareaIndex + '"]');
                        if ($editor.length > 0) {
                            var editorId = $editor.attr('id');
                            if (editorId && tinymce.get(editorId)) {
                                currentValue = tinymce.get(editorId).getContent();
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
                    }

                    // Get min, max, equal, numberFormat from editor or textarea
                    var min = $textarea.data('min') || '';
                    var max = $textarea.data('max') || '';
                    var equal = $textarea.data('equal') || '';
                    var numberFormat = $textarea.data('number-format') || 'en';
                    var nilaiBmAttr = $textarea.attr('data-nilai-baku-mutu') || '';
                    if (textareaId && textareaId.indexOf('result_method_') === 0) {
                        var methodIdForDisp = textareaId.replace('result_method_', '');
                        var $nilaiDisp = $('#nilai_baku_mutu_display_' + methodIdForDisp);
                        if ($nilaiDisp.length) {
                            var dispPlain = $nilaiDisp.text().replace(/\s+/g, ' ').trim();
                            if (dispPlain && /[<>≤≥]/.test(nilaiBmAttr) === false && /[<>≤≥]/.test(dispPlain)) {
                                nilaiBmAttr = dispPlain;
                            } else if (!nilaiBmAttr && dispPlain) {
                                nilaiBmAttr = dispPlain;
                            }
                        }
                    }

                    // Update badge using checkBakuMutu directly with the new offset
                    // Ensure selectedOffset is properly normalized
                    var normalizedOffset = String(selectedOffset || 'default').trim().toLowerCase();
                    if (normalizedOffset !== 'true' && normalizedOffset !== 'false') {
                        normalizedOffset = 'default';
                    }

                    console.log('updateBakuMutuStatus - Updating badge with:', {
                        index: textareaIndex,
                        value: currentValue,
                        min: min,
                        max: max,
                        equal: equal,
                        offset: normalizedOffset,
                        numberFormat: numberFormat
                    });

                        if (typeof window.checkBakuMutu === 'function') {
                            var badgeHtml = window.checkBakuMutu(currentValue, min, max, equal, normalizedOffset, null, '', numberFormat, nilaiBmAttr);
                            console.log('checkBakuMutu returned:', badgeHtml);
                            if (badgeHtml) {
                                // Try multiple ways to find badge container
                                var $badgeContainer = $('#badge_' + textareaIndex);
                                if ($badgeContainer.length === 0) {
                                    // Try to find in row
                                    $badgeContainer = $row.find('#badge_' + textareaIndex);
                                }
                                if ($badgeContainer.length === 0) {
                                    // Try to find by class
                                    $badgeContainer = $row.find('.result-badge-inline, [id^="badge_"]');
                                }

                                console.log('Found badge container:', $badgeContainer.length, 'containers');

                                if ($badgeContainer.length > 0) {
                                // Get history count if exists
                                var historyCount = 0;
                                var $resultOutputDiv;
                                if (isSub) {
                                    $resultOutputDiv = $row.find('#result_output_sub_' + textareaIndex);
                                } else {
                                    $resultOutputDiv = $row.find('#result_output_param_' + textareaIndex);
                                }

                                if ($resultOutputDiv.length > 0) {
                                    historyCount = parseInt($resultOutputDiv.data('history-count') || 0);
                                }

                                if (historyCount > 0) {
                                    badgeHtml += '<br><small class="badge badge-info mt-1"><i class="fa fa-redo"></i> Pengulangan: ' + historyCount + 'x</small>';
                                }

                                    $badgeContainer.html(badgeHtml);
                                    console.log('Badge updated successfully');
                                } else {
                                    console.warn('Badge container not found for index:', textareaIndex);
                                }
                            } else {
                                // Clear badge if no result
                                var $badgeContainer = $('#badge_' + textareaIndex);
                                if ($badgeContainer.length === 0) {
                                    $badgeContainer = $row.find('#badge_' + textareaIndex);
                                }
                                if ($badgeContainer.length > 0) {
                                    $badgeContainer.html('');
                                }
                            }
                        } else if (typeof AnalisInlineEditor !== 'undefined' && AnalisInlineEditor.updateResultBadge) {
                            // Fallback to AnalisInlineEditor method
                            console.log('Using AnalisInlineEditor.updateResultBadge as fallback');
                            AnalisInlineEditor.updateResultBadge(textareaIndex, currentValue, min, max, equal, numberFormat);
                        } else {
                            console.error('checkBakuMutu function not found');
                        }
                    } else {
                        console.warn('Textarea not found in row for index:', index);
                    }
                } else {
                    console.warn('Row not found for index:', index);
                }
            } else {
                console.error('Offset input not found:', offsetInputId);
            }
        }

        // Handler for radio button change - update immediately
        $(document).on('change', 'input[name="baku-mutu-offset"]', function() {
            var selectedOffset = $(this).val();
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';

            console.log('Radio button changed:', {selectedOffset: selectedOffset, index: index, isSub: isSub});

            if (index !== undefined && index !== null) {
                // Update immediately when radio button changes
                setTimeout(function() {
                    updateBakuMutuStatus(selectedOffset, index, isSub);
                }, 10);
            } else {
                console.error('Index not found in modal data');
            }
        });

        // Handler for saving baku mutu override (close modal)
        $('#baku-mutu-save-btn').on('click', function() {
            // Get current selected value from radio button
            var selectedOffset = $('input[name="baku-mutu-offset"]:checked').val();
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';

            console.log('Saving baku mutu override:', {selectedOffset: selectedOffset, index: index, isSub: isSub});

            if (index !== undefined && index !== null) {
                // Ensure value is updated before closing modal
                if (typeof updateBakuMutuStatus === 'function') {
                    updateBakuMutuStatus(selectedOffset, index, isSub);

                    // Double-check that the hidden input was updated
                    setTimeout(function() {
                        var offsetInputId = 'offset_baku_mutu_' + index;
                        var $offsetInput = $('#' + offsetInputId);
                        if ($offsetInput.length === 0) {
                            $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
                        }

                        if ($offsetInput.length > 0) {
                            var currentValue = $offsetInput.val();
                            console.log('Verified offset input value after save:', {
                                id: offsetInputId,
                                value: currentValue,
                                expected: selectedOffset,
                                match: currentValue === selectedOffset
                            });

                            // If value doesn't match, force update
                            if (currentValue !== selectedOffset) {
                                $offsetInput.val(selectedOffset);
                                console.log('Force updated offset input value to:', selectedOffset);
                            }

                            // Ensure input is inside form
                            var $form = $('#form-baca-hasil');
                            if ($form.length > 0 && !$offsetInput.closest('#form-baca-hasil').length) {
                                $offsetInput.appendTo($form);
                                console.log('Moved offset input to form');
                            }
                        } else {
                            console.error('Offset input not found after save:', offsetInputId);
                        }
                    }, 100);
                } else {
                    console.error('updateBakuMutuStatus function not found');
                }
            } else {
                console.error('Index not found in modal data');
            }

            // Close the modal
            $('#bakuMutuModal').modal('hide');
        });

        // ===== Sticky Sample Info Handler =====
        (function() {
            var $wrapper = $('#sampleDataStickyWrapper');
            var $spacer = $('.sample-data-spacer');
            var stickyOffset = 0;
            var isSticky = false;
            var isExpanded = false;

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
                    $('#expandSampleData').show();
                    $('#minimizeSampleData').hide();
                }
            }

            $('#expandSampleData').on('click', function() {
                if (isSticky) {
                    isExpanded = true;
                    $wrapper.removeClass('compact').addClass('expanded');
                    $(this).hide();
                    $('#minimizeSampleData').show();
                }
            });

            $('#minimizeSampleData').on('click', function() {
                if (isSticky) {
                    isExpanded = false;
                    $wrapper.removeClass('expanded').addClass('compact');
                    $(this).hide();
                    $('#expandSampleData').show();
                }
            });

            $(window).on('scroll', function() {
                updateSticky();
            });

            $(window).on('resize', function() {
                if (!isSticky) {
                    calculateOffset();
                }
                updateSticky();
            });

            calculateOffset();
            updateSticky();
        })();
        // ===== End Sticky Sample Info Handler =====
    </script>

    {{-- ============================================================
         EDIT BAKU MUTU — handlers
         ============================================================ --}}
    <script>
    (function () {
        var _getBakuMutuDataUrl  = '{{ route("elits-baca-hasil.baku-mutu.get-data", ["id_baku_mutu" => "__ID__"]) }}';
        var _updateUmumUrl       = '{{ route("elits-baca-hasil.baku-mutu.update-umum", ["id_baku_mutu" => "__ID__"]) }}';
        var _overrideSampleUrl   = '{{ route("elits-baca-hasil.baku-mutu.override-sample") }}';
        var _csrfEbm             = '{{ csrf_token() }}';
        var _tinymceBase         = '{{ asset("assets/admin/vendors/tinymce") }}';

        // Data baku mutu yang sedang di-edit
        var _ebmIdBakuMutu = null;
        var _ebmMethodId   = null;
        var _ebmProgressId = null;
        var _ebmSampleId   = null;

        function _ebmReadBtnAttr($btn, name) {
            var v = $btn.attr(name);
            return (v === undefined || v === null) ? '' : String(v);
        }

        function _ebmBuildLocalPrefill($btn) {
            var hasOverride = _ebmReadBtnAttr($btn, 'data-has-override') === '1';
            if (!hasOverride) {
                return null;
            }
            return {
                has_sample_override: true,
                override_nilai_baku_mutu: _ebmReadBtnAttr($btn, 'data-current-nilai'),
                override_min: _ebmReadBtnAttr($btn, 'data-current-min'),
                override_max: _ebmReadBtnAttr($btn, 'data-current-max'),
                override_equal: _ebmReadBtnAttr($btn, 'data-current-equal'),
                override_unit_id: _ebmReadBtnAttr($btn, 'data-current-unit-id'),
                override_library_id: _ebmReadBtnAttr($btn, 'data-current-library-id'),
            };
        }

        function _ebmMergePrefill(base, incoming) {
            var out = $.extend({}, base || {}, incoming || {});
            // Pertahankan override_* dari base jika incoming tidak membawa has_sample_override
            if (base && base.has_sample_override && incoming && incoming.has_sample_override === false) {
                out.has_sample_override = true;
                ['override_nilai_baku_mutu', 'override_min', 'override_max', 'override_equal', 'override_unit_id', 'override_library_id'].forEach(function (k) {
                    if (incoming[k] === undefined && base[k] !== undefined) {
                        out[k] = base[k];
                    }
                });
            }
            return out;
        }

        // ID TinyMCE editors untuk kedua tab
        var _tinyIds = [
            'mepm-override-nilai-baku-mutu',
            'mepm-umum-nilai-baku-mutu',
            'mepm-override-equal',
            'mepm-umum-equal'
        ];

        var _ebmSelect2Ids = [
            'mepm-override-jenis-makanan-id',
            'mepm-umum-jenis-makanan-id',
            'mepm-override-unit-id',
            'mepm-umum-unit-id',
            'mepm-override-library-id',
            'mepm-umum-library-id'
        ];

        function _ebmSelect2Placeholder(id) {
            if (id.indexOf('jenis-makanan') !== -1) {
                return 'Tidak berdasarkan jenis makanan';
            }
            if (id.indexOf('unit') !== -1) {
                return 'Pilih Satuan';
            }
            return 'Pilih Dokumen Acuan';
        }

        function _ebmDestroySelect2() {
            _ebmSelect2Ids.forEach(function (id) {
                var $el = $('#' + id);
                if ($el.length && $el.hasClass('select2-hidden-accessible')) {
                    $el.off('select2:open.select2-ebm select2:close.select2-ebm');
                    $el.select2('destroy');
                }
            });
            $(document).off('click.select2-ebm', '.select2-dropdown');
        }

        function _ebmInitSelect2() {
            var $modal = $('#modalEditBakuMutu');
            var $modalBody = $modal.find('.modal-body');

            _ebmDestroySelect2();

            _ebmSelect2Ids.forEach(function (id) {
                var $el = $('#' + id);
                if (!$el.length) {
                    return;
                }
                $el.select2({
                    placeholder: _ebmSelect2Placeholder(id),
                    allowClear: true,
                    dropdownParent: $modal,
                    width: '100%',
                    theme: 'bootstrap4',
                    dropdownAutoWidth: false,
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });

                $el.on('select2:open.select2-ebm', function () {
                    $modalBody.css('overflow-y', 'auto');
                });
                $el.on('select2:close.select2-ebm', function () {
                    $modalBody.css('overflow-y', 'auto');
                });
            });

            $(document).on('click.select2-ebm', '.select2-dropdown', function (e) {
                e.stopPropagation();
            });
        }

        function _ebmSetSelectVal(id, val) {
            var $el = $('#' + id);
            if (!$el.length) {
                return;
            }
            $el.val(val || '');
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.trigger('change.select2');
            }
        }

        function _decodeHtmlForDisplay(str) {
            if (!str) {
                return '';
            }
            var el = document.createElement('textarea');
            el.innerHTML = str;
            return el.value;
        }

        // Config TinyMCE ringkas yang dipakai di kedua field
        var _tinyConfig = {
            menubar  : false,
            height   : 120,
            theme    : 'modern',
            base_url : _tinymceBase,
            suffix   : '.min',
            plugins  : ['help', 'wordcount', 'paste', 'charmap'],
            toolbar  : 'undo redo | bold italic superscript subscript | charmap | removeformat',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:13px; margin:4px; }',
            paste_as_text: false,
        };

        /** Inisialisasi TinyMCE pada kedua textarea di modal */
        function _initTiny(callback) {
            var remaining = _tinyIds.length;
            _tinyIds.forEach(function (tid) {
                // Hapus instance lama jika ada
                if (tinymce.get(tid)) {
                    tinymce.get(tid).remove();
                }
                var cfg = $.extend({}, _tinyConfig, {
                    selector: '#' + tid,
                    height: tid.indexOf('-equal') !== -1 ? 90 : 120,
                    setup: function (editor) {
                        editor.on('init', function () {
                            remaining--;
                            if (remaining <= 0 && typeof callback === 'function') callback();
                        });
                    }
                });
                tinymce.init(cfg);
            });
        }

        /** Ambil konten TinyMCE dengan aman (fallback ke textarea value) */
        function _getTinyVal(id) {
            var ed = tinymce.get(id);
            return ed ? ed.getContent() : $('#' + id).val();
        }

        function _prepareTinyContent(val) {
            if (!val) {
                return '';
            }
            var content = _decodeHtmlForDisplay(val);
            if (content.indexOf('^(') !== -1 || content.indexOf('_(') !== -1) {
                content = content.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                content = content.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            }
            return content;
        }

        /** Set konten TinyMCE dengan aman (fallback ke textarea value) */
        function _setTinyVal(id, val) {
            var content = _prepareTinyContent(val);
            var ed = tinymce.get(id);
            if (ed) {
                ed.setContent(content);
            } else {
                $('#' + id).val(content);
            }
        }

        // Buka modal edit baku mutu
        $(document).on('click', '.btn-edit-baku-mutu', function () {
            var $btn = $(this);
            // Pakai .attr() (bukan .data()) agar UUID/nilai tidak ter-cache/ter-coercion jQuery
            _ebmIdBakuMutu  = _ebmReadBtnAttr($btn, 'data-id-baku-mutu');
            _ebmMethodId    = _ebmReadBtnAttr($btn, 'data-method-id');
            _ebmProgressId  = _ebmReadBtnAttr($btn, 'data-sample-progress-id');
            _ebmSampleId    = _ebmReadBtnAttr($btn, 'data-sample-id');
            var paramName   = _ebmReadBtnAttr($btn, 'data-method-name') || '';

            $('#mepm-param-name').text(_decodeHtmlForDisplay(paramName));

            // Reset numeric/select fields
            ['override', 'umum'].forEach(function (scope) {
                $('#mepm-' + scope + '-min').val('');
                $('#mepm-' + scope + '-max').val('');
                _ebmSetSelectVal('mepm-' + scope + '-unit-id', '');
                _ebmSetSelectVal('mepm-' + scope + '-library-id', '');
                $('#mepm-' + scope + '-sampletype-id').val('');
                _ebmSetSelectVal('mepm-' + scope + '-jenis-makanan-id', '__none__');
                $('#mepm-' + scope + '-tipe-nilai').val('');
            });
            $('.mepm-jenis-makanan-row').hide();
            $('.mepm-tipe-nilai-row').hide();

            // Reset textarea values sebelum TinyMCE diinit
            _tinyIds.forEach(function (tid) { $('#' + tid).val(''); });

            // Prefill lokal dari nilai yang sudah tampil (override) agar tidak sempat fallback ke master
            var localPrefill = _ebmBuildLocalPrefill($btn);
            if (localPrefill) {
                $('#modalEditBakuMutu').data('prefill', localPrefill);
                _ebmApplyPrefill(localPrefill);
            } else {
                $('#modalEditBakuMutu').removeData('prefill');
            }

            // Selalu buka tab "Khusus Sampel Ini" terlebih dahulu
            $('#tab-sample-override-link').tab('show');
            $('#modalEditBakuMutu').modal('show');

            // Fetch data baku mutu — dilakukan di sini, sebelum TinyMCE ready
            var url = _getBakuMutuDataUrl.replace('__ID__', _ebmIdBakuMutu);
            url += (url.indexOf('?') === -1 ? '?' : '&') +
                'sample_progress_id=' + encodeURIComponent(_ebmProgressId || '') +
                '&method_id=' + encodeURIComponent(_ebmMethodId || '') +
                '&sample_id=' + encodeURIComponent(_ebmSampleId || '');
            $.get(url, function (res) {
                if (res.status && res.data) {
                    var merged = _ebmMergePrefill($('#modalEditBakuMutu').data('prefill'), res.data);
                    $('#modalEditBakuMutu').data('prefill', merged);
                    _ebmApplyPrefill(merged);
                }
            });
        });

        function _ebmApplyPrefill(d) {
            if (!d) return;

            // Tab umum: selalu dari master
            _setTinyVal('mepm-umum-nilai-baku-mutu', d.nilai_baku_mutu || '');
            _setTinyVal('mepm-umum-equal', d.equal || '');
            $('#mepm-umum-min').val(d.min || '');
            $('#mepm-umum-max').val(d.max || '');
            _ebmSetSelectVal('mepm-umum-unit-id', d.unit_id || '');
            _ebmSetSelectVal('mepm-umum-library-id', d.library_id || '');
            $('#mepm-umum-sampletype-id').val(d.sampletype_id || '');
            _ebmSetSelectVal('mepm-umum-jenis-makanan-id', d.jenis_makanan_id || '__none__');
            $('#mepm-umum-tipe-nilai').val(d.tipe_nilai_baku_mutu || '');

            // Tab override: pakai nilai override jika kunci ada, else fallback master
            var ovNilai = (d.override_nilai_baku_mutu !== undefined) ? d.override_nilai_baku_mutu : d.nilai_baku_mutu;
            var ovEqual = (d.override_equal !== undefined) ? d.override_equal : d.equal;
            var ovMin = (d.override_min !== undefined) ? d.override_min : d.min;
            var ovMax = (d.override_max !== undefined) ? d.override_max : d.max;
            var ovUnit = (d.override_unit_id !== undefined) ? d.override_unit_id : d.unit_id;
            var ovLibrary = (d.override_library_id !== undefined) ? d.override_library_id : d.library_id;

            _setTinyVal('mepm-override-nilai-baku-mutu', ovNilai || '');
            _setTinyVal('mepm-override-equal', ovEqual || '');
            $('#mepm-override-min').val(ovMin !== undefined && ovMin !== null ? ovMin : '');
            $('#mepm-override-max').val(ovMax !== undefined && ovMax !== null ? ovMax : '');
            _ebmSetSelectVal('mepm-override-unit-id', ovUnit || '');
            _ebmSetSelectVal('mepm-override-library-id', ovLibrary || '');
            $('#mepm-override-sampletype-id').val(d.sampletype_id || '');
            _ebmSetSelectVal('mepm-override-jenis-makanan-id', d.jenis_makanan_id || '__none__');
            $('#mepm-override-tipe-nilai').val(d.tipe_nilai_baku_mutu || '');

            if (typeof window._mepmToggleJenisMakanan === 'function') {
                window._mepmToggleJenisMakanan();
            }
        }

        // Inisialisasi TinyMCE + Select2 ketika modal sudah tampil sepenuhnya
        $('#modalEditBakuMutu').on('shown.bs.modal', function () {
            setTimeout(function () {
                _ebmInitSelect2();
                var d = $('#modalEditBakuMutu').data('prefill');
                if (d) {
                    _ebmApplyPrefill(d);
                }
            }, 150);

            _initTiny(function () {
                // Setelah TinyMCE init, apply prefill jika sudah ada
                var d = $('#modalEditBakuMutu').data('prefill');
                if (d) {
                    _ebmApplyPrefill(d);
                }
            });
        });

        // Destroy TinyMCE + Select2 saat modal ditutup
        $('#modalEditBakuMutu').on('hidden.bs.modal', function () {
            _tinyIds.forEach(function (tid) {
                if (tinymce.get(tid)) tinymce.get(tid).remove();
            });
            _ebmDestroySelect2();
            $('#modalEditBakuMutu').removeData('prefill');
        });

        // Simpan untuk SAMPEL INI (override)
        $('#btn-save-override-sample').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url  : _overrideSampleUrl,
                type : 'POST',
                data : {
                    _token              : _csrfEbm,
                    sample_progress_id  : _ebmProgressId,
                    method_id           : _ebmMethodId,
                    nilai_baku_mutu     : _getTinyVal('mepm-override-nilai-baku-mutu'),
                    min                 : $('#mepm-override-min').val(),
                    max                 : $('#mepm-override-max').val(),
                    equal               : _getTinyVal('mepm-override-equal'),
                    unit_id             : $('#mepm-override-unit-id').val(),
                    library_id          : $('#mepm-override-library-id').val(),
                },
                success: function (res) {
                    if (res.status) {
                        _ebmApplyToPage(res.data, true);
                        $('#modalEditBakuMutu').modal('hide');
                        _showToast('success', 'Override baku mutu berhasil disimpan untuk sampel ini.');
                    } else {
                        _showToast('danger', res.pesan || 'Gagal menyimpan.');
                    }
                },
                error: function () {
                    _showToast('danger', 'Terjadi kesalahan pada server.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan untuk Sampel Ini');
                }
            });
        });

        // Simpan SECARA UMUM
        $('#btn-save-umum').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            var url = _updateUmumUrl.replace('__ID__', _ebmIdBakuMutu);
            $.ajax({
                url  : url,
                type : 'POST',
                data : {
                    _token          : _csrfEbm,
                    nilai_baku_mutu : _getTinyVal('mepm-umum-nilai-baku-mutu'),
                    min             : $('#mepm-umum-min').val(),
                    max             : $('#mepm-umum-max').val(),
                    equal           : _getTinyVal('mepm-umum-equal'),
                    unit_id         : $('#mepm-umum-unit-id').val(),
                    library_id      : $('#mepm-umum-library-id').val(),
                    sampletype_id   : $('#mepm-umum-sampletype-id').val(),
                    jenis_makanan_id: $('#mepm-umum-jenis-makanan-id').val(),
                    tipe_nilai_baku_mutu: $('#mepm-umum-tipe-nilai').val(),
                },
                success: function (res) {
                    if (res.status) {
                        _ebmApplyToPage(res.data, false);
                        $('#modalEditBakuMutu').modal('hide');
                        _showToast('success', 'Baku mutu berhasil diperbarui secara umum.');
                    } else {
                        _showToast('danger', res.pesan || 'Gagal menyimpan.');
                    }
                },
                error: function () {
                    _showToast('danger', 'Terjadi kesalahan pada server.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan Secara Umum');
                }
            });
        });

        /** Apply hasil update ke DOM halaman tanpa reload */
        function _ebmApplyToPage(data, isOverride) {
            var mId = _ebmMethodId;
            if (!mId) return;

            // Update tampilan "Kadar Maksimum"
            var $display = $('#nilai_baku_mutu_display_' + mId);
            if ($display.length && data.nilai_baku_mutu !== undefined) {
                $display.html(data.nilai_baku_mutu || '-');
            }

            // Update tampilan satuan
            var $satuan = $('#satuan_baku_mutu_display_' + mId);
            if ($satuan.length && data.shortname_unit !== undefined) {
                $satuan.html(data.shortname_unit || '-');
            }

            // Update data-min / data-max / data-equal pada textarea hasil
            // Pakai .attr + .data agar cache jQuery ikut terbarui (cek hasil / badge)
            var $textarea = $('#result_method_' + mId);
            if ($textarea.length) {
                if (data.min !== undefined) {
                    var minVal = data.min !== null ? data.min : '';
                    $textarea.attr('data-min', minVal).data('min', minVal);
                }
                if (data.max !== undefined) {
                    var maxVal = data.max !== null ? data.max : '';
                    $textarea.attr('data-max', maxVal).data('max', maxVal);
                }
                if (data.equal !== undefined) {
                    var equalVal = data.equal !== null ? data.equal : '';
                    $textarea.attr('data-equal', equalVal).data('equal', equalVal);
                }
                if (data.nilai_baku_mutu !== undefined) {
                    var _plainBm = $('<div>').html(data.nilai_baku_mutu || '').text().replace(/\s+/g, ' ').trim();
                    $textarea.attr('data-nilai-baku-mutu', _plainBm).data('nilai-baku-mutu', _plainBm);
                }
            }

            // Update badge "Override Sampel" + atribut prefill tombol Edit
            var $editBtn = $('.btn-edit-baku-mutu[data-method-id="' + mId + '"]');
            if (isOverride) {
                $editBtn.attr('data-has-override', '1');
                if (data.nilai_baku_mutu !== undefined) {
                    $editBtn.attr('data-current-nilai', data.nilai_baku_mutu || '');
                }
                if (data.min !== undefined) {
                    $editBtn.attr('data-current-min', data.min !== null ? data.min : '');
                }
                if (data.max !== undefined) {
                    $editBtn.attr('data-current-max', data.max !== null ? data.max : '');
                }
                if (data.equal !== undefined) {
                    $editBtn.attr('data-current-equal', data.equal !== null ? data.equal : '');
                }
                if (data.unit_id !== undefined) {
                    $editBtn.attr('data-current-unit-id', data.unit_id !== null ? data.unit_id : '');
                }
                if (data.library_id !== undefined) {
                    $editBtn.attr('data-current-library-id', data.library_id !== null ? data.library_id : '');
                }
                if ($editBtn.closest('div').find('.badge-info').length === 0) {
                    $editBtn.before('<span class="badge badge-info mb-1" style="font-size:9px;" title="Baku mutu ini telah di-override khusus untuk sampel ini"><i class="fa fa-star mr-1"></i>Override Sampel</span><br>');
                }
            }

            // Re-hitung badge hasil setelah baku mutu diubah dari modal
            if (typeof AnalisInlineEditor !== 'undefined' && AnalisInlineEditor.updateResultBadge && $textarea.length) {
                var _idx = $textarea.data('index');
                var _val = $textarea.val();
                if (_idx !== undefined && _val) {
                    AnalisInlineEditor.updateResultBadge(
                        _idx,
                        _val,
                        $textarea.attr('data-min') || '',
                        $textarea.attr('data-max') || '',
                        $textarea.attr('data-equal') || '',
                        $textarea.attr('data-number-format') || 'en'
                    );
                }
            }
        }

        /** Toast sederhana */
        function _showToast(type, msg) {
            var bg = type === 'success' ? '#28a745' : '#dc3545';
            var $toast = $('<div>')
                .css({
                    position:'fixed', bottom:'20px', right:'20px', zIndex: 9999,
                    background: bg, color:'#fff', padding:'10px 20px', borderRadius:'6px',
                    fontSize:'13px', boxShadow:'0 2px 8px rgba(0,0,0,.2)', maxWidth:'320px'
                })
                .html('<i class="fa fa-' + (type==='success'?'check':'times') + '-circle mr-1"></i>' + msg);
            $('body').append($toast);
            setTimeout(function () { $toast.fadeOut(400, function () { $toast.remove(); }); }, 3000);
        }
    })();
    </script>
@endsection
