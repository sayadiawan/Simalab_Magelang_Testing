@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script> --}}
    <link href="{{ asset('assets/admin/cdn-local/css/gijgo.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('assets/admin/cdn-local/js/gijgo.min.js') }}" type="text/javascript"></script>
    <script src="//cdn.ckeditor.com/4.25.1-lts/basic/ckeditor.js"></script>
    {{-- Number Format Helper --}}
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
    
    {{-- Flatpickr for date time picker --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">

    <style>
        /* Fix TinyMCE z-index in Bootstrap modal */
        .tox-tinymce-aux {
            z-index: 10060 !important;
        }
        .moxman-window {
            z-index: 10060 !important;
        }
        .tam-assetmanager-root {
            z-index: 10060 !important;
        }
        
        /* Ensure Flatpickr calendar is visible */
        .flatpickr-calendar {
            z-index: 9999 !important;
        }
        
        /* Style for flatpickr input */
        .flatpickr-input {
            cursor: pointer;
        }
        
        .flatpickr-input:read-only {
            background-color: #f8f9fa;
        }
        /* Ensure TinyMCE editor container is visible */
        #modal-hasil-editor, #modal-keterangan-editor {
            min-height: 200px;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .inline-hasil-editor[data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #999;
        }
        
        .inline-hasil-editor sup {
            color: #667eea;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
        
        /* Highlight row on focus — gunakan kelas (bukan :has) agar ringan saat banyak baris */
        tr.param-row-focused {
            background-color: #f8f9ff;
        }
        
        .parameter-cell {
            vertical-align: middle;
        }
        
        /* TinyMCE toolbar customization for inline */
        .tox.tox-tinymce-inline .tox-toolbar__primary {
            background: #667eea !important;
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
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-card h5 i {
            color: #667eea;
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
        }

        /* Styling untuk baris yang perlu diperbaiki */
        tr.needs-correction {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }

        tr.needs-correction:hover {
            background-color: #ffeaa7 !important;
        }

        /* Highlight animasi untuk parameter yang belum diisi */
        tr.missing-param-highlight {
            position: relative;
            background-color: #fff5f5 !important;
            transition: background-color 0.3s ease;
        }

        tr.missing-param-highlight::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: #dc3545;
            animation: pulse-border 1s ease-in-out infinite;
        }

        tr.missing-param-highlight:hover {
            background-color: #ffe3e3 !important;
        }

        @keyframes pulse-border {
            0% {
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.3;
            }
        }

        .verification-comment {
            background-color: #e7f3ff;
            border-left: 3px solid #2196F3;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .verification-comment-title {
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
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

        .form-section h5 {
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
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

        .keterangan-default-display table tr:first-child td,
        .keterangan-current-display table tr:first-child td {}

        .keterangan-default-display,
        .keterangan-current-display {
            max-width: 100%;
            word-wrap: break-word;
        }

        .form-section h5 i {
            color: #667eea;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-action {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary.btn-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary.btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-light.btn-action {
            border: 1px solid #dee2e6;
        }

        .btn-light.btn-action:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        #table-parameter {
            background: #fff;
            border-radius: 8px;
            overflow: visible;
            width: 100%;
            min-width: 100%;
        }

        #table-parameter thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        #table-parameter thead th {
            padding: 15px;
            font-weight: 600;
            border: none;
            color: white;
            white-space: nowrap;
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

        /* Sembunyikan kolom keterangan di seluruh body tabel */
        #table-parameter tbody td:nth-child(5) {
            display: none !important;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .inline-metode-editor sup {
            color: #667eea;
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
            width: 100%;
            max-width: 100%;
        }

        #table-parameter tbody input[type="text"]:focus,
        #table-parameter tbody textarea:focus,
        #table-parameter tbody select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Responsive wrapper untuk table */
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
            order: 2;
            flex: 1 1 auto;
            /* GPU-composited layer: scroll tidak memicu repaint seluruh halaman */
            will-change: transform;
            -webkit-overflow-scrolling: touch;
            /* Stacking context tersendiri agar repaint di dalam tabel tidak menjalar keluar */
            isolation: isolate;
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
            background: rgba(102, 126, 234, 0.9);
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

        /* Shadow gradient di bagian bawah untuk menunjukkan ada konten */
        #tableParameterResponsive::after {
            content: '';
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to top, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
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
            background: linear-gradient(to bottom, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
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
            background: rgba(102, 126, 234, 0.9);
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

        /* Ensure table maintains minimum width for readability */
        .table-responsive > #table-parameter {
            margin-bottom: 0;
        }

        /* Sticky header untuk table */
        #table-parameter thead {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        #table-parameter thead th {
            background-color: #667eea;
            color: #fff;
            font-weight: 600;
            border-bottom: 2px solid #5568d3;
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

        /* Scroll indicator untuk layar kecil */
        .scroll-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(33, 150, 243, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            z-index: 10;
            display: none;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        /* Tampilkan scroll indicator di layar kecil */
        @media (max-width: 768px) {
            .table-responsive {
                position: relative;
            }

            .scroll-indicator {
                display: block;
            }

            /* Tambahkan shadow gradient di sisi kanan untuk menunjukkan ada konten yang bisa di-scroll */
            .table-responsive::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 40px;
                height: 100%;
                background: linear-gradient(to left, rgba(33, 150, 243, 0.15), transparent);
                pointer-events: none;
                z-index: 5;
                transition: opacity 0.3s;
            }

            /* Tambahkan shadow gradient di sisi kiri saat scroll ke kanan */
            .table-responsive::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 40px;
                height: 100%;
                background: linear-gradient(to right, rgba(33, 150, 243, 0.15), transparent);
                pointer-events: none;
                z-index: 5;
                opacity: 0;
                transition: opacity 0.3s;
            }

            /* Tampilkan shadow kiri saat scroll */
            .table-responsive.scrolled-left::before {
                opacity: 1;
            }

            /* Hilangkan shadow kanan saat sudah di akhir scroll */
            .table-responsive.scrolled-right::after {
                opacity: 0;
            }
        }

        /* Tablet (768px - 1024px) */
        @media (max-width: 1024px) {
            #table-parameter thead th {
                padding: 12px 10px;
                font-size: 13px;
            }

            #table-parameter tbody td,
            #table-parameter tbody th {
                padding: 10px 8px;
                font-size: 13px;
            }

            #table-parameter tbody input[type="text"],
            #table-parameter tbody textarea,
            #table-parameter tbody select {
                padding: 6px 10px;
                font-size: 13px;
            }
        }

        /* Mobile Landscape (576px - 768px) */
        @media (max-width: 768px) {
            #table-parameter {
                font-size: 12px;
            }

            #table-parameter thead th {
                padding: 10px 8px;
                font-size: 12px;
            }

            #table-parameter tbody td,
            #table-parameter tbody th {
                padding: 8px 6px;
                font-size: 12px;
            }

            #table-parameter tbody input[type="text"],
            #table-parameter tbody textarea,
            #table-parameter tbody select {
                padding: 6px 8px;
                font-size: 12px;
            }

            /* Make action buttons smaller */
            #table-parameter .btn {
                padding: 4px 8px;
                font-size: 11px;
            }

            /* Adjust badge sizes */
            #table-parameter .badge {
                font-size: 10px;
                padding: 3px 6px;
            }
        }

        /* Mobile Portrait (max-width: 576px) */
        @media (max-width: 576px) {
            #table-parameter {
                font-size: 11px;
            }

            #table-parameter thead th {
                padding: 8px 6px;
                font-size: 11px;
            }

            #table-parameter tbody td,
            #table-parameter tbody th {
                padding: 6px 4px;
                font-size: 11px;
            }

            #table-parameter tbody input[type="text"],
            #table-parameter tbody textarea,
            #table-parameter tbody select {
                padding: 5px 6px;
                font-size: 11px;
            }

            /* Stack badges vertically on very small screens */
            #table-parameter .badge {
                display: inline-block;
                margin: 2px 0;
                font-size: 9px;
                padding: 2px 5px;
            }

            /* Make buttons more compact */
            #table-parameter .btn {
                padding: 3px 6px;
                font-size: 10px;
            }

            /* Ensure table scrolls horizontally */
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Prevent text wrapping in table cells on mobile */
            #table-parameter tbody td,
            #table-parameter tbody th {
                white-space: nowrap;
            }

            /* Allow wrapping for long text content */
            #table-parameter tbody td .result-display,
            #table-parameter tbody td .keterangan-display {
                white-space: normal;
                word-break: break-word;
            }
        }

        /* Extra small devices (max-width: 400px) */
        @media (max-width: 400px) {
            #table-parameter {
                font-size: 10px;
            }

            #table-parameter thead th {
                padding: 6px 4px;
                font-size: 10px;
            }

            #table-parameter tbody td,
            #table-parameter tbody th {
                padding: 5px 3px;
                font-size: 10px;
            }

            #table-parameter tbody input[type="text"],
            #table-parameter tbody textarea,
            #table-parameter tbody select {
                padding: 4px 5px;
                font-size: 10px;
            }

            #table-parameter .btn {
                padding: 2px 4px;
                font-size: 9px;
            }

            #table-parameter .btn i {
                font-size: 9px;
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

        /* Responsive: mobile */
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
                min-width: 90px;
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

            .col-md-6 {
                margin-bottom: 15px;
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
        }

        .keterangan-current-display tbody td {
            background-color: none !important;
        }

        .badge-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .badge-wa-kirim {
            background-color: #25d366;
            color: #fff;
            font-weight: 600;
        }

        .badge-wa-tidak {
            background-color: #6c757d;
            color: #fff;
            font-weight: 600;
        }

        .badge-wa-peringatan {
            background-color: #ffc107;
            color: #212529;
            font-weight: 600;
        }

        .wa-kirim-control {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .wa-kirim-control .form-check {
            margin: 0;
            display: flex;
            align-items: center;
        }

        .wa-kirim-control .form-check-label {
            margin-left: 6px;
            font-weight: 500;
            cursor: pointer;
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

        /* Styles for simplified table */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm-popup {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 4px;
        }

        .result-display {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            min-height: 30px;
            word-wrap: break-word;
        }

        .result-display.empty {
            color: #999;
            font-style: italic;
        }

        .keterangan-display {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            min-height: 30px;
            max-height: 100px;
            overflow-y: auto;
            word-wrap: break-word;
            font-size: 12px;
        }

        .keterangan-display.empty {
            color: #999;
            font-style: italic;
        }

        /* Modal styles */
        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
        }

        .modal-header .close {
            color: white;
            opacity: 0.9;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 25px;
        }

        .form-group-modal {
            margin-bottom: 20px;
        }

        .form-group-modal label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .simulasi-output-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }

        .simulasi-output-box .title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .offset-baku-mutu-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .offset-option {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .offset-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .offset-option input[type="radio"]:checked + label {
            color: #667eea;
        }

        .offset-option input[type="radio"] {
            margin-right: 10px;
        }

        .offset-option label {
            cursor: pointer;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* ===== Loading Overlay ===== */
        #page-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(3px);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            transition: opacity 0.4s ease;
        }

        #page-loading-overlay.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loading-spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #e0e0e0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: loading-spin 0.8s linear infinite;
        }

        @keyframes loading-spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 15px;
            color: #555;
            font-weight: 500;
            text-align: center;
        }

        .loading-subtext {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .loading-progress-dots span {
            display: inline-block;
            width: 7px;
            height: 7px;
            background: #667eea;
            border-radius: 50%;
            margin: 0 3px;
            animation: loading-dot-bounce 1.2s ease-in-out infinite;
        }

        .loading-progress-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-progress-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes loading-dot-bounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
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

    {{-- Loading overlay: mencegah interaksi sebelum semua asset (TinyMCE, inline editor) siap --}}
    <div id="page-loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">
            Memuat halaman, mohon tunggu…
            <div class="loading-subtext">Sedang menyiapkan editor hasil pemeriksaan</div>
        </div>
        <div class="loading-progress-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

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
                                <li class="breadcrumb-item active" aria-current="page"><span>analis permohonan uji paket
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
        action="{{ route('elits-permohonan-uji-klinik-2.store-permohonan-uji-analis2', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
        method="POST" enctype="multipart/form-data" id="form">
        @csrf
        @method('PUT')

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

        <!-- Header Card -->
        <div class="info-card">
            <h4>
                <i class="fa fa-flask"></i>
                Analis Permohonan Uji Klinik
            </h4>
            <p style="margin: 0; opacity: 0.9;">Formulir untuk menginput hasil pemeriksaan analitik dari parameter yang
                diminta</p>
        </div>

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
                            <input type="text" class="form-control" name="tglpengujian_permohonan_uji_klinik"
                                id="tglpengujian_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                                value="{{ \Carbon\Carbon::parse($tgl_pengujian)->format('d/m/Y H:i') ?? old('tglpengujian_permohonan_uji_klinik') }}"
                                readonly>
                        </div>
                    @else
                        <div class="form-group">
                            <label for="tglpengujian_permohonan_uji_klinik">
                                <i class="fa fa-clock mr-2"></i>
                                Tanggal Pengujian <span style="color: red">*</span>
                            </label>
                            <input type="text" class="form-control flatpickr-input" name="tglpengujian_permohonan_uji_klinik"
                                id="tglpengujian_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                                value="{{ $tgl_pengujian ? \Carbon\Carbon::parse($tgl_pengujian)->format('d/m/Y H:i') : old('tglpengujian_permohonan_uji_klinik') }}" required autocomplete="off">
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="analis_permohonan_uji_klinik">
                            <i class="fa fa-user-md mr-2"></i>
                            Pilih Analis (Input/Output Hasil Klinik) <span style="color: red">*</span>
                        </label>
                        @php
                            // Jika user terelasi dengan petugas (id_petugas terisi), namanya otomatis terpilih & terkunci
                            $userHasPetugas = !empty($user_petugas_nama);
                            // Prioritas nilai: user_petugas_nama (jika ada) → nama_analis dari DB → old value
                            $defaultAnalis = old('analis_permohonan_uji_klinik',
                                $userHasPetugas ? $user_petugas_nama : ($nama_analis ?? ''));
                            // Lock jika: sudah ada di database ATAU user punya petugas sendiri
                            $analisLocked = !empty($nama_analis) || $userHasPetugas;
                        @endphp
                        @if ($analisLocked)
                            <div class="form-control bg-light text-muted" style="height: auto;">
                                {{ $defaultAnalis ?: '-' }}
                            </div>
                            <small class="text-muted">
                                @if ($userHasPetugas)
                                    Analis ditetapkan sesuai akun yang digunakan.
                                @else
                                    Analis telah ditetapkan dan tidak dapat diubah.
                                @endif
                            </small>
                            <input type="hidden" name="analis_permohonan_uji_klinik" id="analis_permohonan_uji_klinik"
                                value="{{ $defaultAnalis }}">
                        @else
                            <select class="form-control" name="analis_permohonan_uji_klinik"
                                id="analis_permohonan_uji_klinik" required>
                                <option value="">-- Pilih Analis --</option>
                                @foreach ($petugas_analis as $petugas)
                                    <option value="{{ $petugas['nama'] }}" data-nip="{{ $petugas['nip'] }}"
                                        data-id="{{ $petugas['id_petugas'] }}"
                                        {{ $defaultAnalis == $petugas['nama'] ? 'selected' : '' }}>
                                        {{ $petugas['nama'] }}{{ !empty($petugas['nip']) ? ' - ' . $petugas['nip'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih petugas yang memiliki role Input/Output Hasil Klinik</small>
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
                    <div class="patient-data-compact-item">
                        <i class="fab fa-whatsapp"></i>
                        <strong>WA:</strong>
                        @if (!empty($bisa_kirim_hasil_whatsapp))
                            <span class="badge badge-wa-kirim">Akan dikirim</span>
                        @elseif (!empty($kirim_hasil_whatsapp))
                            <span class="badge badge-wa-peringatan">Aktif, HP kosong</span>
                        @else
                            <span class="badge badge-wa-tidak">Tidak dikirim</span>
                        @endif
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
                                        <td>{{ $item_permohonan_uji_klinik->pasien->phone_pasien ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th width="250px">Kirim Hasil WhatsApp</th>
                                        <td>
                                            @if (!empty($bisa_kirim_hasil_whatsapp))
                                                <span class="badge badge-wa-kirim">
                                                    <i class="fab fa-whatsapp"></i> Akan dikirim setelah validasi
                                                </span>
                                            @elseif (!empty($kirim_hasil_whatsapp))
                                                <span class="badge badge-wa-peringatan">
                                                    <i class="fa fa-exclamation-triangle"></i> Aktif, tetapi nomor HP kosong
                                                </span>
                                            @else
                                                <span class="badge badge-wa-tidak">
                                                    <i class="fab fa-whatsapp"></i> Tidak dikirim via WhatsApp
                                                </span>
                                            @endif
                                        </td>
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
                                    <tr>
                                        <th width="250px">Kirim Hasil WhatsApp</th>
                                        <td>
                                            <div class="wa-kirim-control">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="kirim_hasil_whatsapp"
                                                        id="kirim_hasil_whatsapp_ya" value="1"
                                                        {{ !empty($kirim_hasil_whatsapp) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="kirim_hasil_whatsapp_ya">Ya</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="kirim_hasil_whatsapp"
                                                        id="kirim_hasil_whatsapp_tidak" value="0"
                                                        {{ empty($kirim_hasil_whatsapp) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="kirim_hasil_whatsapp_tidak">Tidak</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted mt-1 mb-0">
                                                @if (empty($phone_pasien_wa))
                                                    Nomor HP pasien kosong — hasil tidak bisa dikirim meski dipilih Ya.
                                                @else
                                                    Setelah validasi selesai, PDF hasil dikirim ke {{ $phone_pasien_wa }}.
                                                @endif
                                            </small>
                                        </td>
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
                                            @php
                                                $penerimaan_data = \Smt\Masterweb\Helpers\Smt::decodeJsonMap($penerimaan_sampel);
                                            @endphp
                                            @if (count($penerimaan_data) > 0)
                                                @foreach ($penerimaan_data as $jenis => $catatan)
                                                    <div style="margin-bottom: 8px;">
                                                        <span
                                                            style="display: inline-block; background: #667eea; color: white; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-right: 8px;">{{ $jenis }}</span>
                                                        <span style="color: #495057;">{{ is_array($catatan) ? implode(', ', $catatan) : $catatan }}</span>
                                                    </div>
                                                @endforeach
                                            @else
                                                {{ is_string($penerimaan_sampel) ? $penerimaan_sampel : '-' }}
                                            @endif
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
                                                    @foreach ($volume_data as $jenis => $volume)
                                                        <span
                                                            style="display: inline-block; background: #f0f4ff; border: 1px solid #667eea; color: #667eea; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-right: 8px; margin-bottom: 5px;">
                                                            <i class="fa fa-vial"
                                                                style="margin-right: 5px;"></i>{{ $jenis }}:
                                                            <strong>{{ is_array($volume) ? implode(', ', $volume) : $volume }}</strong>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif (is_string($volume_sampel) && trim($volume_sampel) !== '' && trim($volume_sampel) !== '{}' && trim($volume_sampel) !== '[]')
                                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($volume_sampel, 120) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Kualitas Sampel</th>
                                    <td>
                                        @php
                                            // Parse JSON kualitas sampel per jenis
                                            $kualitas_data = [];
                                            if (!empty($kualitas_sampel)) {
                                                if (is_string($kualitas_sampel)) {
                                                    $decoded = json_decode($kualitas_sampel, true);
                                                    $kualitas_data = is_array($decoded) ? $decoded : [];
                                                } elseif (is_array($kualitas_sampel)) {
                                                    $kualitas_data = $kualitas_sampel;
                                                }
                                            }
                                        @endphp

                                        @if (count($kualitas_data) > 0)
                                            {{-- Tampilan per jenis sampel --}}
                                            @foreach ($kualitas_data as $jenis => $kualitas_arr)
                                                <div style="margin-bottom: 12px;">
                                                    <span
                                                        style="display: inline-block; background: #667eea; color: white; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-right: 8px; margin-bottom: 5px;">{{ $jenis }}</span>
                                                    <div style="display: inline-block;">
                                                        @if (is_array($kualitas_arr))
                                                            @foreach ($kualitas_arr as $kualitas)
                                                                @if ($kualitas == 'Lisis')
                                                                    <span class="badge badge-danger"
                                                                        style="margin-right: 5px;">Lisis</span>
                                                                @elseif ($kualitas == 'Ikterik')
                                                                    <span class="badge badge-warning"
                                                                        style="margin-right: 5px;">Ikterik</span>
                                                                @elseif ($kualitas == 'Lipemik')
                                                                    <span class="badge badge-info"
                                                                        style="margin-right: 5px;">Lipemik</span>
                                                                @elseif ($kualitas == 'Cukup')
                                                                    <span class="badge badge-success"
                                                                        style="margin-right: 5px;">Cukup</span>
                                                                @elseif ($kualitas == 'Beku')
                                                                    <span class="badge badge-secondary"
                                                                        style="margin-right: 5px;">Beku</span>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            {{-- Fallback ke tampilan lama --}}
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
                                        @endif
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
            <h5 class="d-flex align-items-center justify-content-between flex-wrap">
                <span>
                    <i class="fa fa-clipboard-list"></i>
                    Hasil Pemeriksaan
                </span>
                <span class="d-inline-flex flex-wrap" style="gap:6px;">
                    {{-- Sementara disembunyikan: jalur HTTP/biolis --}}
                    <button type="button" class="btn btn-sm btn-outline-primary d-none" id="btn-ambil-tms" title="Ambil hasil dari alat TMS (HTTP)">
                        <i class="fa fa-flask mr-1"></i> Ambil dari TMS
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-ambil-tms-mqtt" title="Ambil hasil dan buat order ke alat TMS">
                        <i class="fa fa-flask mr-1"></i> Ambil dari TMS
                    </button>
                </span>
            </h5>
            <!-- Alert untuk scroll horizontal di layar kecil -->
            <div class="alert alert-info d-md-none mb-3" role="alert" style="display: flex; align-items: center; padding: 10px 15px; border-radius: 6px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <i class="fa fa-arrows-alt-h mr-2" style="font-size: 16px; color: #2196F3;"></i>
                <span style="font-size: 13px; color: #1976D2;">
                    <strong>Tips:</strong> Geser tabel ke kanan dan kiri untuk melihat semua kolom termasuk tombol "Edit" di layar kecil.
                </span>
            </div>
            <div class="table-parameter-wrapper" id="tableParameterWrapper">
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
                                <th style="width: 22%">Nama Test</th>
                                <th style="width: 18%">Hasil</th>
                                <th style="width: 8%" class="text-center">Satuan</th>
                                <th style="width: 14%" class="text-center">Metode</th>
                                <th style="width: 15%; display: none;">Keterangan</th>
                                <th style="width: 20%" class="text-center">Nilai Normal</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $no = 0;
                        @endphp

                        @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                            <tr>
                                <th colspan="6">
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
                                                        <span class="badge badge-warning ml-2" title="Perlu diperbaiki">
                                                            <i class="fa fa-exclamation-triangle"></i> Perlu Diperbaiki
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
                                                        data-name="{{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}"
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
                                                            $result_badge = \Smt\Masterweb\Helpers\Smt::checkBakuMutu($hasil_value, $min, $max, $equal, $offset, $multipleBakuMutu, $kesimpulan, $pasien_umur, $pasien_gender, $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? null);
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
                                            <td style="display: none;">
                                                <!-- Hidden textarea for keterangan -->
                                                    <textarea class="form-control" id="keterangan_sub_{{ $no_sub }}"
                                                        name="keterangan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        style="display: none;">{{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' }}</textarea>

                                                {{-- Display keterangan --}}
                                                <div class="keterangan-display {{ empty($item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik']) ? 'empty' : '' }}"
                                                    id="keterangan_display_sub_{{ $no_sub }}">
                                                    @if (!empty($item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik']))
                                                        {!! rubahNilaikeForm($item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik']) !!}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </div>

                                                    @if ($has_comment)
                                                    <div class="verification-comment mt-2">
                                                            <div class="verification-comment-title">
                                                                <i class="fa fa-comment-dots"></i>
                                                            Komentar Verifikator:
                                                            </div>
                                                        <div class="text-muted" style="font-size: 12px;">
                                                                {!! nl2br(e($item_subsatuan_klinik['komentar_verifikasi'])) !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                            </td>
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
                                                    <span class="badge badge-warning ml-2" title="Perlu diperbaiki">
                                                        <i class="fa fa-exclamation-triangle"></i> Perlu Diperbaiki
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
                                                @endphp

                                                @php
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
                                                    data-parameter-tms-id="{{ $item_satuan_klinik['id_parameter_tms'] ?? '' }}"
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

                                            {{-- Display hasil --}}
                                            @if ($urinalisaDualType)
                                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.urinalisa-dual-result-input', [
                                                    'no' => $no,
                                                    'item_satuan_klinik' => $item_satuan_klinik,
                                                ])
                                            @endif
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
                                        <td style="display: none;">
                                            @php
                                                // Keterangan selalu menggunakan nilai dari database
                                                    $current_keterangan =
                                                        $item_satuan_klinik[
                                                            'keterangan_permohonan_uji_parameter_klinik'
                                                        ] ?? '';
                                                    $current_keterangan = rubahNilaikeForm($current_keterangan);
                                                @endphp

                                            <!-- Hidden textarea for keterangan -->
                                                <textarea class="form-control" id="keterangan_param_{{ $no }}"
                                                    name="keterangan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    style="display: none;">{{ $current_keterangan }}</textarea>

                                            {{-- Display keterangan --}}
                                            <div class="keterangan-display {{ empty($item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik']) ? 'empty' : '' }}"
                                                id="keterangan_display_param_{{ $no }}">


                                                @if (!empty($item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik']))
                                                    {!! rubahNilaikeForm($item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik']) !!}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                                        </div>

                                                @if ($has_comment)
                                                <div class="verification-comment mt-2">
                                                        <div class="verification-comment-title">
                                                            <i class="fa fa-comment-dots"></i>
                                                        Komentar Verifikator:
                                                        </div>
                                                    <div class="text-muted" style="font-size: 12px;">
                                                            {!! nl2br(e($item_satuan_klinik['komentar_verifikasi'])) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                        </td>
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
                @php
                    $catatanHasilValue = old(
                        'catatan_hasil',
                        \Smt\Masterweb\Helpers\Smt::resolveCatatanHasilFormValue(
                            $item_permohonan_uji_klinik,
                            $arr_permohonan_parameter ?? []
                        )
                    );
                @endphp
                <textarea 
                    name="catatan_hasil" 
                    id="catatan_hasil" 
                    class="form-control" 
                    rows="5" 
                    placeholder="Masukkan catatan hasil pemeriksaan...">{{ $catatanHasilValue }}</textarea>
                <small class="form-text text-muted">
                    Default diisi otomatis dari master berdasarkan parameter satuan pada permohonan ini. Perubahan manual tetap tersimpan.
                </small>
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
            <button type="button" class="btn btn-primary btn-action btn-simpan">
                <i class="fa fa-save mr-2"></i>Simpan Hasil
            </button>
            <button type="button" class="btn btn-info btn-action btn-review-hasil mr-2">
                <i class="fa fa-eye mr-2"></i>Review Hasil
            </button>
            <button type="button" class="btn btn-success btn-action btn-selesai">
                <i class="fa fa-check-circle mr-2"></i>Selesai
            </button>
            {{-- Tombol tersembunyi untuk trigger selesai setelah review --}}
            <button type="button" class="btn btn-success btn-action d-none" id="btn-lanjut-selesai">
                <i class="fa fa-check-circle mr-2"></i>Lanjutkan & Selesai
            </button>
        </div>
    </div>

    {{-- Modal Ambil dari TMS / Make Order --}}
    <div class="modal fade" id="modalAmbilTms" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-flask mr-2"></i>TMS — Ambil Hasil &amp; Make Order</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="tmsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tms-tab-hasil" data-toggle="tab" href="#tmsPaneHasil" role="tab">
                                <i class="fa fa-download mr-1"></i> Ambil Hasil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tms-tab-order" data-toggle="tab" href="#tmsPaneOrder" role="tab">
                                <i class="fa fa-plus-circle mr-1"></i> Make Order
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tms-tab-riwayat" data-toggle="tab" href="#tmsPaneRiwayat" role="tab">
                                <i class="fa fa-list mr-1"></i> Riwayat Order
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="tmsTabContent">
                        {{-- Tab: Ambil Hasil --}}
                        <div class="tab-pane fade show active" id="tmsPaneHasil" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Data diambil dari hasil alat TMS berdasarkan Sample ID,
                                lalu dipetakan ke parameter satuan. Jika tabel sudah terisi, klik
                                <strong>Isi ke Form</strong> untuk mengisi kolom hasil.
                            </p>
                            <div class="form-group">
                                <label for="tms-sample-id">Sample ID TMS</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tms-sample-id" placeholder="Contoh: 20260511001008">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="btn-cari-tms">
                                            <i class="fa fa-search mr-1"></i> Cari
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Kosongkan lalu klik Cari untuk mencoba nomor lab/register otomatis.</small>
                            </div>
                            <div id="tms-result-info" class="mb-2" style="display:none;"></div>
                            <div class="table-responsive" style="max-height: 40vh; overflow: auto;">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                    <thead class="thead-light" style="position: sticky; top: 0;">
                                        <tr>
                                            <th>Parameter Klinik</th>
                                            <th>TMS</th>
                                            <th>Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tms-result-body">
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Make Order --}}
                        <div class="tab-pane fade" id="tmsPaneOrder" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Hanya menampilkan parameter yang ada di permohonan ini dan sudah terhubung ke
                                <code>ms_parameter_tms</code>. Awalnya kosong — klik badge <strong>jenis sampel</strong>
                                untuk mencentang semua parameter di grup itu, atau centang manual per parameter.
                                Setiap jenis sampel membuat order terpisah — isi <strong>Tray</strong> dan <strong>Posisi</strong> per jenis sampel.
                            </p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Nama Pasien</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-order-nama" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Tgl Lahir</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-order-dob" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Jenis Kelamin</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-order-jk" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Kode Barcode / Sample ID</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-order-barcode" placeholder="260203902" maxlength="10" inputmode="numeric" autocomplete="off">
                                        <small class="text-muted">Maks. 10 digit: DDMM tanggal lahir + 5 digit nomor spesimen + 1 digit jenis (1 Darah, 2 Serum, 3 Plasma, 4 Urine, 5 Feses, 6 Swab, 7 Blood Cell, 9 Lainnya). Digit jenis diisi otomatis per order.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size: 13px;">Parameter TMS (dari permohonan)</strong>
                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-tms-param-all">Semua</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-tms-param-none">Kosongkan</button>
                                </div>
                            </div>
                            <div id="tms-order-existing-info" class="small text-muted mb-2" style="display:none;"></div>
                            <div id="tms-order-param-list" class="border rounded p-2" style="max-height: 32vh; overflow: auto; font-size: 12px;">
                                <div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat parameter...</div>
                            </div>
                            <div id="tms-order-info" class="mt-2" style="display:none;"></div>
                        </div>

                        {{-- Tab: Riwayat --}}
                        <div class="tab-pane fade" id="tmsPaneRiwayat" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Order TMS untuk permohonan ini. Parameter &amp; value di riwayat bersumber dari <code>tb_orderdetail_tms</code>.
                                Order yang sudah ada valuenya bisa diisi ke kolom hasil lewat tombol <strong>Isi ke Form</strong>.
                            </p>
                            <div id="tms-riwayat-body">
                                <div class="text-muted text-center py-3">Belum ada data</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-2 border-top bg-light" id="tms-round-options">
                    <div class="form-row align-items-end">
                        <div class="col-sm-5 col-md-5 mb-1 mb-sm-0">
                            <label for="tms-round-mode" class="small mb-0 text-muted">Pembulatan saat Isi ke Form</label>
                            <select id="tms-round-mode" class="form-control form-control-sm">
                                <option value="none">Apa adanya (tanpa bulatkan)</option>
                                <option value="round">Bulatkan biasa</option>
                                <option value="up">Ke atas</option>
                                <option value="down">Ke bawah</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-4 mb-1 mb-sm-0">
                            <label for="tms-round-decimals" class="small mb-0 text-muted">Angka di belakang koma</label>
                            <select id="tms-round-decimals" class="form-control form-control-sm">
                                <option value="0">0 (bilangan bulat)</option>
                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-sm-3 col-md-3">
                            <small class="text-muted d-block" style="line-height:1.25;">Berlaku untuk tombol <strong>Isi ke Form</strong>. Nilai non-angka tidak diubah.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary d-none" id="btn-buat-order-tms">
                        <i class="fa fa-paper-plane mr-1"></i> Buat Order
                    </button>
                    <button type="button" class="btn btn-success" id="btn-isi-tms" disabled>
                        <i class="fa fa-download mr-1"></i> Isi ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal TMS (jalur alat): Ambil Hasil, Buat Order, Riwayat --}}
    <div class="modal fade" id="modalAmbilTmsMqtt" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:#0e7490;">
                    <h5 class="modal-title"><i class="fa fa-flask mr-2"></i>TMS — Ambil Hasil &amp; Buat Order</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="tmsMqttTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tms-mqtt-tab-hasil" data-toggle="tab" href="#tmsMqttPaneHasil" role="tab">
                                <i class="fa fa-download mr-1"></i> Ambil Hasil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tms-mqtt-tab-order" data-toggle="tab" href="#tmsMqttPaneOrder" role="tab">
                                <i class="fa fa-plus-circle mr-1"></i> Buat Order
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tms-mqtt-tab-riwayat" data-toggle="tab" href="#tmsMqttPaneRiwayat" role="tab">
                                <i class="fa fa-list mr-1"></i> Riwayat Order
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tms-mqtt-tab-massal" data-toggle="tab" href="#tmsMqttPaneMassal" role="tab">
                                <i class="fa fa-users mr-1"></i> Order Massal
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tmsMqttPaneHasil" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Hasil dari alat dipetakan ke parameter permohonan ini.
                                Jika tabel sudah terisi, klik <strong>Isi ke Form</strong>.
                            </p>
                            <div class="form-group">
                                <label for="tms-mqtt-sample-id">Sample ID / Barcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tms-mqtt-sample-id" placeholder="Contoh: 1101037842">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" id="btn-cari-tms-mqtt">
                                            <i class="fa fa-search mr-1"></i> Cari
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="btn-tms-mqtt-listen" title="Tarik hasil terbaru dari alat">
                                            <i class="fa fa-refresh mr-1"></i> Tarik hasil
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Kosongkan lalu klik Cari untuk menampilkan semua hasil order pada permohonan ini.</small>
                            </div>
                            <div id="tms-mqtt-inbox" class="mb-2" style="display:none;"></div>
                            <div id="tms-mqtt-result-info" class="mb-2" style="display:none;"></div>
                            <div class="table-responsive" style="max-height: 40vh; overflow: auto;">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                    <thead class="thead-light" style="position: sticky; top: 0;">
                                        <tr>
                                            <th>Parameter Klinik</th>
                                            <th>TMS</th>
                                            <th>Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tms-mqtt-result-body">
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tmsMqttPaneOrder" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Pilih parameter permohonan yang akan dikirim ke alat.
                                Klik badge jenis sampel untuk mencentang semua, atau centang per parameter.
                                Setiap jenis sampel menjadi order terpisah. Tray dan posisi opsional.
                            </p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Nama Pasien</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-mqtt-order-nama" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Tgl Lahir</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-mqtt-order-dob" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="mb-1">Jenis Kelamin</label>
                                        <input type="text" class="form-control form-control-sm" id="tms-mqtt-order-jk" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-1">Kode Barcode / Sample ID</label>
                                <input type="text" class="form-control form-control-sm" id="tms-mqtt-order-barcode" placeholder="260203902" maxlength="10" inputmode="numeric" autocomplete="off">
                                <small class="text-muted">Maks. 10 digit: DDMM tanggal lahir + 5 digit nomor spesimen + 1 digit jenis (1 Darah, 2 Serum, 3 Plasma, 4 Urine, 5 Feses, 6 Swab, 7 Blood Cell, 9 Lainnya). Digit jenis diisi otomatis per order.</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size: 13px;">Parameter TMS (dari permohonan)</strong>
                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-tms-mqtt-param-all">Semua</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-tms-mqtt-param-none">Kosongkan</button>
                                </div>
                            </div>
                            <div id="tms-mqtt-order-existing-info" class="small text-muted mb-2" style="display:none;"></div>
                            <div id="tms-mqtt-order-param-list" class="border rounded p-2" style="max-height: 32vh; overflow: auto; font-size: 12px;">
                                <div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat parameter...</div>
                            </div>
                            <div id="tms-mqtt-order-info" class="mt-2" style="display:none;"></div>
                        </div>
                        <div class="tab-pane fade" id="tmsMqttPaneRiwayat" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Order TMS untuk permohonan ini. Order yang sudah ada hasilnya bisa diisi ke kolom hasil lewat <strong>Isi ke Form</strong>.
                            </p>
                            <div id="tms-mqtt-riwayat-body">
                                <div class="text-muted text-center py-3">Belum ada data</div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tmsMqttPaneMassal" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Urutan: <strong>1. Jenis sampel</strong> (bisa lebih dari satu) → <strong>2. Pasien</strong> → <strong>3. Kelompok parameter</strong>.
                                Satu kirim massal membuat order terpisah per jenis sampel.
                            </p>
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <div id="tms-mass-scope-info" class="small text-muted"></div>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-tms-mass-reload">
                                    <i class="fa fa-refresh mr-1"></i> Muat ulang
                                </button>
                            </div>

                            <div class="mb-3">
                                <strong class="d-block mb-1" style="font-size:13px;">1. Pilih jenis sampel</strong>
                                <small class="text-muted d-block mb-1">Klik untuk centang. Bisa pilih Serum, Darah, Blood Cell, dan jenis lain sekaligus.</small>
                                <div id="tms-mass-jenis-wrap" class="d-flex flex-wrap"></div>
                            </div>

                            <div id="tms-mass-step-pasien" style="display:none;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                                    <strong style="font-size:13px;">2. Pilih pasien <span id="tms-mass-jenis-label"></span></strong>
                                    <button type="button" class="btn btn-xs btn-outline-info" id="btn-tms-mass-fill-pos" title="Isi posisi 1, 2, 3, ... per jenis sampel">
                                        <i class="fa fa-sort-numeric-asc mr-1"></i> Isi pos berurutan
                                    </button>
                                </div>
                                <div id="tms-mass-patients-wrap" class="border rounded mb-3" style="max-height: 32vh; overflow: auto;"></div>
                            </div>

                            <div id="tms-mass-step-kelompok" style="display:none;">
                                <strong class="d-block mb-1" style="font-size:13px;">3. Kelompok parameter</strong>
                                <p class="text-muted small mb-2">Kelompok dibuat per jenis sampel. Pasien yang tidak memakai parameter tertentu bisa memakai kelompok berbeda.</p>
                                <div id="tms-mass-templates-wrap" class="mb-2"></div>
                            </div>
                            <div id="tms-mass-info" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-2 border-top bg-light" id="tms-mqtt-round-options">
                    <div class="form-row align-items-end">
                        <div class="col-sm-5 col-md-5 mb-1 mb-sm-0">
                            <label for="tms-mqtt-round-mode" class="small mb-0 text-muted">Pembulatan saat Isi ke Form</label>
                            <select id="tms-mqtt-round-mode" class="form-control form-control-sm">
                                <option value="none">Apa adanya (tanpa bulatkan)</option>
                                <option value="round">Bulatkan biasa</option>
                                <option value="up">Ke atas</option>
                                <option value="down">Ke bawah</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-4 mb-1 mb-sm-0">
                            <label for="tms-mqtt-round-decimals" class="small mb-0 text-muted">Angka di belakang koma</label>
                            <select id="tms-mqtt-round-decimals" class="form-control form-control-sm">
                                <option value="0">0 (bilangan bulat)</option>
                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-sm-3 col-md-3">
                            <small class="text-muted d-block" style="line-height:1.25;">Berlaku untuk tombol <strong>Isi ke Form</strong>. Nilai non-angka tidak diubah.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-info d-none" id="btn-buat-order-tms-mqtt">
                        <i class="fa fa-paper-plane mr-1"></i> Buat Order
                    </button>
                    <button type="button" class="btn btn-info d-none" id="btn-buat-order-tms-massal">
                        <i class="fa fa-paper-plane mr-1"></i> Kirim Order Massal
                    </button>
                    <button type="button" class="btn btn-success" id="btn-isi-tms-mqtt" disabled>
                        <i class="fa fa-download mr-1"></i> Isi ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL REVIEW HASIL
         ============================================================ --}}
    <div class="modal fade" id="modalReviewHasil" tabindex="-1" role="dialog" aria-labelledby="modalReviewHasilLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalReviewHasilLabel">
                        <i class="fa fa-cog mr-2"></i>Pengaturan Hasil
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

                    {{-- ---- Ukuran Font (hanya untuk hasil non-narkoba) ---- --}}
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-text-height mr-1"></i>Ukuran Font Hasil
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum, bukan narkoba)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">6</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="fontsize-slider"
                                min="6" max="20" step="0.5"
                                value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                            <span class="text-muted small ml-2">20</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 90px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="fontsize-input"
                                    min="6" max="20" step="0.5"
                                    value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">pt</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white text-center">
                            <span id="fontsize-preview-sample" style="font-size: 12pt;">
                                Contoh: Hemoglobin = <strong>14.5</strong> g/dL
                            </span>
                        </div>
                    </div>

                    {{-- ---- Jarak Baris (Line Spacing) ---- --}}
                    @php
                        $lineHeightRaw = $item_permohonan_uji_klinik->line_height_hasil_permohonan_uji_klinik;
                        $lineHeightFloat = (float) ($lineHeightRaw ?? 0);
                        if ($lineHeightRaw === null || $lineHeightFloat === 1.5 || $lineHeightFloat < 0.5) {
                            $lineHeightValue = 1;
                        } else {
                            $lineHeightValue = $lineHeightFloat;
                        }
                    @endphp
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-align-justify mr-1"></i>Jarak Baris (Line Spacing)
                            <small class="text-muted font-weight-normal">(khusus hasil pemeriksaan umum)</small>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0.5</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="lineheight-slider"
                                min="0.5" max="3.0" step="0.1"
                                value="{{ $lineHeightValue }}">
                            <span class="text-muted small ml-2">3.0</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="lineheight-input"
                                    min="0.5" max="3.0" step="0.1"
                                    value="{{ $lineHeightValue }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">×</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white" id="lineheight-preview-text">
                            <span id="lineheight-preview-sample" style="line-height: 1; display: block;">
                                Contoh baris pertama: Hemoglobin = <strong>14.5</strong> g/dL<br>
                                Contoh baris kedua: Leukosit = <strong>8.200</strong> /µL
                            </span>
                        </div>
                    </div>

                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings', [
                        'idPrefix' => '',
                        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
                    ])

                    {{-- ---- Pengaturan Kop Surat ---- --}}
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fa fa-file-alt mr-1"></i>Kop Surat
                        </label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-sm text-muted" id="kop-label-text">
                                    {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                                </div>
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input" id="toggle-kop"
                                    {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="toggle-kop"></label>
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
                    <button type="button" class="btn btn-info" id="btn-buka-review" disabled>
                        <i class="fa fa-spinner fa-spin mr-1 d-none" id="review-loading-icon"></i>
                        <i class="fa fa-save mr-1" id="review-save-icon"></i>
                        <span class="btn-label-text">Simpan & Buka Review</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW HASIL FULLSCREEN (iframe) — harus sebelum script agar handler terikat --}}
    <div class="modal fade" id="modalPreviewHasil" tabindex="-1" role="dialog" aria-hidden="true"
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
                    <iframe id="preview-hasil-iframe"
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
                    <button type="button" class="btn btn-outline-warning btn-sm" id="btn-pengaturan-preview">
                        <i class="fa fa-cog mr-1"></i>Pengaturan Hasil
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-success btn-sm d-none" id="btn-preview-lanjut-selesai">
                        <i class="fa fa-check-circle mr-1"></i>Lanjutkan & Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Lengkap dihapus — metode & hasil diedit inline di tabel -->

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
@endsection

@section('scripts')
    {{-- Flatpickr for date time picker - Load first --}}
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>
    
    <script>
        // Verify flatpickr is loaded
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr failed to load!');
        } else {
            console.log('Flatpickr loaded successfully');
        }
        
        // Patient data for eGFR calculation
        var pasienGender = '{{ $item_permohonan_uji_klinik->pasien->gender_pasien ?? "L" }}';
        var pasienAge = {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? 0 }};
    </script>
    
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <!-- TinyMCE is already loaded in template admin scripts.blade.php from local assets -->
    <!-- Wait for TinyMCE to be ready before loading scripts that use it -->
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
                    // Set flag to indicate TinyMCE is ready
                    window.tinymceReady = true;
                }
            }
        })();
    </script>

    <!-- Define checkBakuMutu function BEFORE loading analis-inline-editing.js -->
    <!-- This is a placeholder - the full function will be defined in the main script section -->
    <script>
        // Set flag to indicate checkBakuMutu will be available
        window.checkBakuMutuReady = false;
        window.checkBakuMutuDefined = false;
        
        // Placeholder function to prevent errors until the real function is defined
        window.checkBakuMutu = function() {
            console.warn('checkBakuMutu placeholder called - waiting for full definition...');
            return '';
        };
    </script>

    <!-- Analis Inline Editing Script - Load after TinyMCE check -->
    <!-- Note: This script will wait for checkBakuMutu to be available -->
    <script src="{{ asset('assets/js/analis-inline-editing.js') }}?v={{ time() }}"></script>
    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._metode-inline-editor-script')

    <script>
        // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
        // Make these functions globally available before document ready
        window.convertToTinyMCE = function(value) {
            if (!value) return '';
            // Simple direct replacement - no complex placeholder system
            // Step 1: Handle comparison symbols first
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            // Step 2: Convert ^() to <sup> and _() to <sub>
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            return value;
        };

        // Convert from HTML <sup> and <sub> to ^() and _() format for our system
        window.convertFromTinyMCE = function(value) {
            if (!value) return '';
            // Simple direct replacement
            // Step 1: Convert HTML tags to ^() and _() format
            value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
            value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
            // Step 2: Strip any remaining HTML tags
            value = value.replace(/<[^>]*>/g, '');
            // Step 3: Decode HTML entities
            value = value.replace(/&le;/gi, '≤');
            value = value.replace(/&ge;/gi, '≥');
            value = value.replace(/&lt;/g, '<');
            value = value.replace(/&gt;/g, '>');
            value = value.replace(/&plusmn;/g, '±');
            value = value.replace(/&nbsp;/g, ' ');
            return value;
        };
    </script>

    <script>
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

        // Fungsi untuk menampilkan seluruh tabel Stadium CKD sebagai HTML (setiap baris pakai <br>)
        // Tidak lagi tergantung pada nilai e-GFR, hanya mengembalikan blok teks tetap.
        function getCKDStage(gfrValue) {
            return 'Stadium CKD :<br>' +
                'CKD 1 : \u2265 90<br>' +
                'CKD 2 : 60-89<br>' +
                'CKD 3 a : 45-59<br>' +
                'CKD 3 b : 30-44<br>' +
                'CKD 4 : 15-29<br>' +
                'CKD 5 : < 15';
        }

        function isKreatininParamName(name) {
            var lower = String(name || '').toLowerCase();
            if (!lower || lower.indexOf('gfr') !== -1) {
                return false;
            }
            return lower.indexOf('kreatinin') !== -1
                || lower.indexOf('creatinine') !== -1
                || lower.indexOf('creatinin') !== -1
                || lower.indexOf('creatpap') !== -1;
        }

        function extractNumericHasil(val) {
            if (val == null || val === '') {
                return '';
            }
            var s = String(val)
                .replace(/<[^>]*>/g, ' ')
                .replace(/&nbsp;/gi, ' ')
                .replace(/,/g, '.')
                .trim();
            var m = s.match(/-?\d+(?:\.\d+)?/);
            return m ? m[0] : '';
        }

        function recalculateEgfrFromForm() {
            if (typeof calculateEfgr !== 'function') {
                return false;
            }
            var age = typeof pasienAge !== 'undefined' ? parseInt(pasienAge, 10) : 0;
            if (!(age > 0)) {
                return false;
            }
            var gender = typeof pasienGender !== 'undefined' ? pasienGender : 'L';
            var $kreat = null;
            $('textarea.result_method_klinik[data-name]').each(function() {
                if ($kreat) {
                    return;
                }
                if (isKreatininParamName($(this).attr('data-name') || $(this).data('name'))) {
                    $kreat = $(this);
                }
            });
            if (!$kreat || !$kreat.length) {
                return false;
            }
            var num = extractNumericHasil($kreat.val());
            if (!num) {
                return false;
            }
            calculateEfgr(gender, age, num);
            return true;
        }
        window.recalculateEgfrFromForm = recalculateEgfrFromForm;
        window.isKreatininParamName = isKreatininParamName;

        function calculateEfgr(gender, age, kreatinin) {
            var num = extractNumericHasil(kreatinin);
            if (!num || isNaN(parseFloat(num))) {
                return;
            }

            let scr = parseFloat(num);
            if (scr <= 0) {
                return;
            }

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

            // Cari textarea hasil yang merupakan parameter eGFR berdasarkan data-name (lebih fleksibel)
            var targetTextarea = null;
            var candidates = document.querySelectorAll('textarea.result_method_klinik[data-name]');

            candidates.forEach(function(el) {
                var nameAttr = (el.getAttribute('data-name') || '').toLowerCase();
                // Match ke nama yang mengandung 'gfr'
                if (!targetTextarea && nameAttr.includes('gfr')) {
                    targetTextarea = el;
                }
            });

            if (targetTextarea) {
                // Set nilai ke hidden textarea
                $(targetTextarea).val(gfrValue);

                // Jika ada inline editor yang terhubung (TinyMCE / contenteditable), update juga tampilannya
                var textareaId = targetTextarea.id;
                if (textareaId) {
                    var $row = $(targetTextarea).closest('tr');
                    if ($row.length > 0) {
                        // Inline editor di tabel utama
                        var $inlineEditor = $row.find('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                        if ($inlineEditor.length > 0) {
                            // Update konten editor biasa
                            $inlineEditor.html(gfrValue).removeClass('empty');

                            // Jika sudah di-init TinyMCE inline, sinkronkan juga instance-nya
                            if (typeof tinymce !== 'undefined') {
                                var editorId = $inlineEditor.attr('id');
                                if (editorId) {
                                    var editorInstance = tinymce.get(editorId);
                                    if (editorInstance && typeof editorInstance.setContent === 'function' && !editorInstance.removed) {
                                        editorInstance.setContent(gfrValue);
                                    }
                                }
                            }
                        }

                        // Badge / display hasil di kolom yang sama
                        var mEgfr = String(textareaId).match(/hasil_permohonan_uji_parameter_klinik_(\d+)/);
                        if (mEgfr) {
                            var egfrNo = mEgfr[1];
                            var $badge = $('#badge_' + egfrNo);
                            if ($badge.length) {
                                $badge.html(gfrValue);
                            }
                            var $display = $('#result_display_param_' + egfrNo);
                            if ($display.length) {
                                $display.html(gfrValue).removeClass('empty');
                            }
                            if (typeof updateResultPreview === 'function') {
                                try {
                                    updateResultPreview(textareaId, 'param_' + egfrNo);
                                } catch (e) {}
                            }
                        }
                    }
                }

                // Trigger input event supaya semua handler lain (preview, baku mutu, dll) ikut jalan
                $(targetTextarea).trigger('input').trigger('change');

                // Otomatis tambahkan catatan stadium CKD ke catatan_hasil
                // Hanya jika belum ada stadium dari master (Stadium GFR / Stadium CKD)
                // agar tidak dobel dengan default catatan hasil klinik.
                var ckdStage = getCKDStage(gfrValue);
                if (ckdStage) {
                    var $catatanHasil = $('#catatan_hasil');

                    if ($catatanHasil.length > 0) {
                        var currentCatatan = $catatanHasil.val() || '';
                        var catatanPlain = currentCatatan.replace(/<[^>]+>/g, ' ');

                        if (/Stadium\s*(GFR|CKD)/i.test(catatanPlain)) {
                            // Sudah ada dari master / input sebelumnya — jangan append lagi
                        } else {
                            var catatanText = ckdStage;

                            // Hapus SEMUA blok catatan CKD lama jika ada
                            var ckdPattern = /Stadium CKD[\s\S]*?(?=(?:Stadium CKD|$))/gi;
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
                                    catatanEditor.save();
                                    catatanEditor.fire('change');
                                }
                            }
                        }
                    }
                }
            } else {
                console.warn('eGFR target textarea not found. Pastikan nama parameter mengandung \"GFR\" dan data-name sudah di-set.');
            }
        }
        window.calculateEfgr = calculateEfgr;

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

        // Initialize Select2 untuk dropdown analis (hanya jika belum terpilih)
        @if (!$analisLocked)
            $("#analis_permohonan_uji_klinik").select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Pilih Analis --',
                allowClear: true
            });
        @endif

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

        // Initialize Select2 untuk dropdown hasil dengan is_option = 1
        // Handler ini akan dipindahkan ke dalam $(document).ready() agar updateResultPreview tersedia

        // Initialize Flatpickr for Tanggal Pengujian
        function initFlatpickrForTanggalPengujian() {
            console.log('[Flatpickr] Attempting to initialize...');
            console.log('[Flatpickr] flatpickr available:', typeof flatpickr !== 'undefined');
            
            var tglPengujianInput = document.getElementById('tglpengujian_permohonan_uji_klinik');
            console.log('[Flatpickr] Input element found:', tglPengujianInput !== null);
            
            if (!tglPengujianInput) {
                console.error('[Flatpickr] Tanggal Pengujian input element not found!');
                return false;
            }
            
            console.log('[Flatpickr] Input readonly:', tglPengujianInput.readOnly);
            console.log('[Flatpickr] Input value:', tglPengujianInput.value);
            
            // Check if already initialized
            if (tglPengujianInput._flatpickr) {
                console.log('[Flatpickr] Already initialized for this input');
                return true;
            }
            
            if (typeof flatpickr === 'undefined') {
                console.error('[Flatpickr] flatpickr is not loaded!');
                return false;
            }
            
            // Check if input is readonly - remove it completely for flatpickr to work
            var wasReadonly = tglPengujianInput.readOnly || tglPengujianInput.hasAttribute('readonly');
            if (wasReadonly) {
                console.log('[Flatpickr] Input is readonly, removing readonly attribute to allow flatpickr initialization');
                // Remove readonly attribute completely - don't restore it
                tglPengujianInput.removeAttribute('readonly');
                tglPengujianInput.readOnly = false;
                // Store the fact that it was readonly (for styling purposes)
                tglPengujianInput.setAttribute('data-was-readonly', 'true');
                console.log('[Flatpickr] Readonly removed, input.readOnly:', tglPengujianInput.readOnly);
                console.log('[Flatpickr] Input hasAttribute readonly:', tglPengujianInput.hasAttribute('readonly'));
            }
            
            var currentValue = tglPengujianInput.value;
            var defaultDate = null;
            
            // Parse current value if exists
            if (currentValue && currentValue.trim() !== '') {
                // Try to parse d/m/Y H:i format
                var parts = currentValue.split(' ');
                if (parts.length === 2) {
                    var dateParts = parts[0].split('/');
                    var timeParts = parts[1].split(':');
                    if (dateParts.length === 3 && timeParts.length === 2) {
                        defaultDate = new Date(
                            parseInt(dateParts[2]), // year
                            parseInt(dateParts[1]) - 1, // month (0-indexed)
                            parseInt(dateParts[0]), // day
                            parseInt(timeParts[0]), // hour
                            parseInt(timeParts[1]) // minute
                        );
                        console.log('[Flatpickr] Parsed date from value:', defaultDate);
                    }
                }
            }
            
            // If parsing failed, use tanggal klik (hari ini) + jam sekarang
            if (!defaultDate || isNaN(defaultDate.getTime())) {
                var regIso = @json(\Carbon\Carbon::parse($item_permohonan_uji_klinik->created_at ?? $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('Y-m-d'));
                var regParts = (regIso || '').split('-');
                defaultDate = regParts.length === 3
                    ? new Date(parseInt(regParts[0], 10), parseInt(regParts[1], 10) - 1, parseInt(regParts[2], 10), new Date().getHours(), new Date().getMinutes())
                    : new Date();
                console.log('[Flatpickr] Using permohonan created_at as default:', defaultDate);
            }
            
            try {
                var wasReadonly = tglPengujianInput.getAttribute('data-was-readonly') === 'true';
                
                var fpConfig = {
                    enableTime: true,
                    dateFormat: "d/m/Y H:i",
                    time_24hr: true,
                    defaultDate: defaultDate,
                    locale: 'id',
                    allowInput: !wasReadonly, // Allow manual input if not readonly
                    clickOpens: true, // Always allow click to open (even if readonly, user can still view calendar)
                    static: false, // Calendar is not static (will appear on click)
                    appendTo: document.body, // Append calendar to body to avoid z-index issues
                    onChange: function(selectedDates, dateStr, instance) {
                        // Ensure the value is set correctly
                        if (selectedDates.length > 0) {
                            tglPengujianInput.value = dateStr;
                            console.log('[Flatpickr] Date changed to:', dateStr);
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        console.log('[Flatpickr] Calendar is ready!');
                        console.log('[Flatpickr] Calendar element:', instance.calendarContainer);
                    },
                    onOpen: function(selectedDates, dateStr, instance) {
                        console.log('[Flatpickr] Calendar opened!');
                    },
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('[Flatpickr] Calendar closed!');
                    }
                };
                
                console.log('[Flatpickr] Creating flatpickr instance with config:', fpConfig);
                var fp = flatpickr(tglPengujianInput, fpConfig);
                
                // Verify flatpickr was created
                if (!fp) {
                    console.error('[Flatpickr] Failed to create flatpickr instance!');
                    return false;
                }
                
                console.log('[Flatpickr] Flatpickr instance created:', fp);
                console.log('[Flatpickr] Input element after init:', tglPengujianInput);
                console.log('[Flatpickr] Input._flatpickr:', tglPengujianInput._flatpickr);
                
                // Check if flatpickr calendar element exists
                setTimeout(function() {
                    var calendar = document.querySelector('.flatpickr-calendar');
                    console.log('[Flatpickr] Calendar element found:', calendar !== null);
                    if (calendar) {
                        console.log('[Flatpickr] Calendar is visible:', calendar.offsetParent !== null);
                    }
                }, 200);
                
                // If it was readonly, don't set it back - keep it editable for flatpickr
                // But prevent manual typing if needed
                if (wasReadonly) {
                    console.log('[Flatpickr] Input was readonly, but keeping editable for flatpickr');
                    // Don't set readonly back - let user interact with flatpickr
                    // But prevent manual typing
                    if (fp) {
                        fp.set('allowInput', false); // Prevent manual typing
                        fp.set('clickOpens', true); // Allow calendar to open
                        // Make input look readonly but still functional
                        tglPengujianInput.style.backgroundColor = '#f8f9fa';
                        tglPengujianInput.style.cursor = 'pointer';
                    }
                } else {
                    // If not readonly, make sure it's fully functional
                    if (fp) {
                        fp.set('allowInput', true);
                        fp.set('clickOpens', true);
                    }
                }
                
                console.log('[Flatpickr] Initialized successfully!', fp);
                return true;
            } catch (e) {
                console.error('[Flatpickr] Error initializing:', e);
                // If error and was readonly, restore readonly
                if (tglPengujianInput.getAttribute('data-was-readonly') === 'true') {
                    tglPengujianInput.setAttribute('readonly', 'readonly');
                }
                return false;
            }
        }
        
        $(document).ready(function() {
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-apply-localstorage', [
                'permohonanId' => $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                'stepKey' => 'pemeriksa',
            ])

            // CRITICAL: Prevent native form submission completely.
            // This form only submits via ajaxSubmit() (which does NOT fire the submit event),
            // so it's safe to block ALL native submit events.
            // Main cause: HTML5 spec auto-submits a form with Enter key when there is only
            // ONE <input type="text"> in the form (which is the date input here).
            $('#form').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            // Also prevent Enter key on the date input (flatpickr) from submitting
            $(document).on('keydown', '#tglpengujian_permohonan_uji_klinik, .flatpickr-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            
            // Scroll saat fokus: hanya lewat HTMLElement.prototype.focus di bawah (hindari scrollIntoView ganda)

            // Sorot baris aktif tanpa CSS :has (lebih ringan untuk tabel besar)
            $(document).on('focusin', '#tableParameterResponsive', function(ev) {
                var tr = ev.target && ev.target.closest ? ev.target.closest('tr') : null;
                if (!tr || !this.contains(tr)) return;
                $(tr).addClass('param-row-focused').siblings('.param-row-focused').removeClass('param-row-focused');
            });
            $(document).on('focusout', '#tableParameterResponsive', function(ev) {
                var related = ev.relatedTarget;
                if (related && this.contains(related)) return;
                $(this).find('tr.param-row-focused').removeClass('param-row-focused');
            });
            
            // Juga prevent scroll saat focus dipanggil secara programmatic (satu jalur ringan, tanpa banyak timer)
            var originalFocus = HTMLElement.prototype.focus;
            HTMLElement.prototype.focus = function(options) {
                var el = this;
                var $element = $(el);
                var $tableResponsive = $('#tableParameterResponsive');
                
                if ($tableResponsive.length && $element.closest('#tableParameterResponsive').length) {
                    // Jika preventScroll: true, skip scrollIntoView karena scroll sudah diatur oleh focusHasilInput
                    if (options && options.preventScroll) {
                        originalFocus.call(el, options);
                        return;
                    }
                    
                    var savedScrollY = window.scrollY;
                    var savedScrollX = window.scrollX;
                    if (el.scrollIntoView) {
                        // Gunakan smooth behavior agar transisi lebih halus saat user klik elemen secara manual
                        el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                    }
                    originalFocus.call(el, { preventScroll: true });
                    // Kembalikan posisi scroll window sekali saja via rAF (cukup satu kali untuk mencegah window jump)
                    requestAnimationFrame(function() {
                        if (window.scrollY !== savedScrollY || window.scrollX !== savedScrollX) {
                            window.scrollTo(savedScrollX, savedScrollY);
                        }
                    });
                } else {
                    originalFocus.call(el, options);
                }
            };

            // Initialize TinyMCE for Catatan Hasil
            function initCatatanHasilTinyMCE() {
                // Check if TinyMCE is fully ready (cukup pastikan objek & init tersedia)
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                    console.log('TinyMCE not ready yet, retrying...');
                    setTimeout(initCatatanHasilTinyMCE, 300);
                    return;
                }

                // Check if editor already exists
                if (tinymce.get('catatan_hasil')) {
                    console.log('TinyMCE editor for catatan_hasil already exists');
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
                                    console.log('TinyMCE editor for catatan_hasil initialized');
                                });
                                
                                editor.on('blur', function() {
                                    // Sync content to textarea for form submission
                                    var content = editor.getContent();
                                    $('#catatan_hasil').val(content);
                                });
                            }
                        });
                    } catch(e) {
                        console.error('Error initializing TinyMCE for catatan_hasil:', e);
                        setTimeout(initCatatanHasilTinyMCE, 500);
                    }
                }
            }

            // Initialize after a short delay to ensure TinyMCE is loaded
            setTimeout(initCatatanHasilTinyMCE, 500);


            // Initialize TinyMCE for Kesimpulan Hasil
            function initKesimpulanHasilTinyMCE() {
                // Check if TinyMCE is fully ready (cukup pastikan objek & init tersedia)
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
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

                // Update on scroll — rAF-throttled agar tidak memblokir setiap pixel scroll
                var _stickyRafPending = false;
                $(window).on('scroll', function() {
                    if (_stickyRafPending) return;
                    _stickyRafPending = true;
                    requestAnimationFrame(function() {
                        _stickyRafPending = false;
                        updateSticky();
                    });
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

            // Initialize Flatpickr with retry mechanism
            var initFlatpickrWithRetry = function() {
                var retries = 0;
                var maxRetries = 50;
                
                function tryInit() {
                    if (typeof flatpickr !== 'undefined') {
                        var success = initFlatpickrForTanggalPengujian();
                        if (success) {
                            console.log('[Flatpickr] Initialization completed successfully');
                            return;
                        }
                    }
                    
                    retries++;
                    if (retries < maxRetries) {
                        setTimeout(tryInit, 100);
                    } else {
                        console.error('[Flatpickr] Failed to initialize after ' + maxRetries + ' retries!');
                    }
                }
                
                tryInit();
            };
            
            // Start initialization
            initFlatpickrWithRetry();
            
            // Also try on window load as fallback
            window.addEventListener('load', function() {
                setTimeout(function() {
                    console.log('[Flatpickr] Window load event - attempting initialization');
                    initFlatpickrForTanggalPengujian();
                }, 1500);
            });
            
            // Auto-select analis if user has petugas
            @if (!empty($user_petugas_nama) && empty($nama_analis))
                var userPetugasNama = "{{ $user_petugas_nama }}";
                var $analisSelect = $('#analis_permohonan_uji_klinik');
                if ($analisSelect.length > 0 && $analisSelect.is('select')) {
                    // Check if user's petugas exists in the options
                    var $option = $analisSelect.find('option').filter(function() {
                        return $(this).text().includes(userPetugasNama) || $(this).val() === userPetugasNama;
                    });
                    if ($option.length > 0) {
                        $analisSelect.val($option.first().val()).trigger('change');
                    }
                }
            @endif
            
            // === PASIENT DATA FOR BAKU MUTU SELECTION ===
            var pasienGender = '{{ $item_permohonan_uji_klinik->pasien->gender_pasien ?? "" }}';
            var pasienUmur = {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? 0 }};
            
            // === TINYMCE EDITOR MODAL ===
            var currentEditorTarget = null;
            var editorInstance = null;
            var currentMethodId = null;
            var allEditorButtons = [];

            // Handle scroll indicator untuk tabel responsive
            function updateScrollIndicator() {
                var $tableWrapper = $('.table-responsive');
                if ($tableWrapper.length) {
                    $tableWrapper.each(function() {
                        var $wrapper = $(this);
                        var scrollLeft = $wrapper.scrollLeft();
                        var scrollWidth = $wrapper[0].scrollWidth;
                        var clientWidth = $wrapper[0].clientWidth;
                        var maxScroll = scrollWidth - clientWidth;

                        // Reset classes
                        $wrapper.removeClass('scrolled-left scrolled-right');

                        // Tampilkan shadow kiri jika sudah scroll ke kanan
                        if (scrollLeft > 10) {
                            $wrapper.addClass('scrolled-left');
                        }

                        // Hilangkan shadow kanan jika sudah di akhir scroll
                        if (scrollLeft >= maxScroll - 10) {
                            $wrapper.addClass('scrolled-right');
                        }
                    });
                }
            }

            // Handle table parameter scroll indicator
            // FAST: hanya cek scrollTop vs maxScroll — tanpa iterasi baris (O(1), tidak ada forced reflow)
            function updateTableParameterIndicatorFast() {
                var $tableWrapper = $('#tableParameterWrapper');
                var $tableResponsive = $('#tableParameterResponsive');
                if (!$tableWrapper.length || !$tableResponsive.length) return;

                var scrollTop = $tableResponsive.scrollTop();
                var maxScroll = $tableResponsive[0].scrollHeight - $tableResponsive[0].clientHeight;

                if (scrollTop < maxScroll - 10 && maxScroll > 0) {
                    $tableWrapper.addClass('has-more-content');
                } else {
                    $tableWrapper.removeClass('has-more-content');
                }
                if (scrollTop > 10 && maxScroll > 0) {
                    $tableWrapper.addClass('has-content-above');
                } else {
                    $tableWrapper.removeClass('has-content-above');
                }
            }

            // SLOW: hitung baris visible — hanya dijalankan sekali per 500ms agar tidak memblokir scroll
            var _rowCounterTimer = null;
            function scheduleRowCounterUpdate() {
                if (_rowCounterTimer) return; // sudah terjadwal, tidak perlu lagi
                _rowCounterTimer = setTimeout(function() {
                    _rowCounterTimer = null;
                    var $tableResponsive = $('#tableParameterResponsive');
                    if (!$tableResponsive.length) return;

                    var $allRows = $('#table-parameter tbody tr');
                    var totalRows = $allRows.length;
                    var clientHeight = $tableResponsive[0].clientHeight;
                    var scrollTop = $tableResponsive.scrollTop();
                    var viewportTop = scrollTop;
                    var viewportBottom = scrollTop + clientHeight;

                    var firstVisible = -1, lastVisible = -1;
                    // Gunakan getBoundingClientRect relatif ke container — lebih cepat dari .position()
                    var containerRect = $tableResponsive[0].getBoundingClientRect();
                    $allRows.each(function(index) {
                        var rect = this.getBoundingClientRect();
                        var top = rect.top - containerRect.top + scrollTop;
                        var bottom = top + rect.height;
                        if (bottom >= viewportTop && top <= viewportBottom) {
                            if (firstVisible === -1) firstVisible = index;
                            lastVisible = index;
                        }
                    });

                    var $counterText = $('#parameterCounterText');
                    if ($counterText.length) {
                        var below = Math.max(0, totalRows - (lastVisible + 1));
                        var above = Math.max(0, firstVisible);
                        if (below > 0 || above > 0) {
                            var parts = [];
                            if (above > 0) parts.push(above + ' di atas');
                            if (below > 0) parts.push(below + ' di bawah');
                            $counterText.text(parts.join(', ') + ' tersisa');
                        } else {
                            $counterText.text('Semua parameter terlihat');
                        }
                    }
                }, 500);
            }

            // rAF-throttled scroll handler: handler scroll hanya berjalan sekali per frame animasi
            var _scrollRafPending = false;
            function onTableScroll() {
                if (_scrollRafPending) return;
                _scrollRafPending = true;
                requestAnimationFrame(function() {
                    _scrollRafPending = false;
                    updateTableParameterIndicatorFast();
                    scheduleRowCounterUpdate();
                });
            }

            var _scrollIndicatorRafPending = false;
            function onAnyScroll() {
                if (_scrollIndicatorRafPending) return;
                _scrollIndicatorRafPending = true;
                requestAnimationFrame(function() {
                    _scrollIndicatorRafPending = false;
                    updateScrollIndicator();
                });
            }

            // Attach scroll event listener
            $('.table-responsive').on('scroll', function() {
                onAnyScroll();
                if ($(this).attr('id') === 'tableParameterResponsive') {
                    onTableScroll();
                }
            });

            // Initialize scroll indicator on page load
            updateScrollIndicator();
            setTimeout(function() {
                updateTableParameterIndicatorFast();
                scheduleRowCounterUpdate();
            }, 500);

            // Re-check on window resize
            $(window).on('resize', function() {
                setTimeout(function() {
                    updateScrollIndicator();
                    updateTableParameterIndicatorFast();
                }, 150);
            });

            // Initialize all previews on page load - execute for all textarea and dropdown
            // This ensures all results are displayed with correct format before Edit button is clicked
            setTimeout(function() {
                // Initialize all textarea results
                $('.result_method_klinik').each(function() {
                    var $textarea = $(this);
                    var targetId = $textarea.attr('id');
                    var currentValue = $textarea.val();
                    
                    // Update regardless of value to ensure format is correct
                    var $row = $textarea.closest('tr');
                    var $editorBtn = $row.find('.open-editor-modal');

                    if ($editorBtn.length) {
                        var methodId = $editorBtn.data('method-id');
                        if (methodId) {
                            // Always update to ensure correct format display
                        updateResultPreview(targetId, methodId);
                        }
                    }
                });
                
                // Initialize all dropdown results
                $(".result-dropdown-klinik").each(function() {
                    var $select = $(this);
                    var textareaId = $select.data('textarea-id');
                    var methodId = $select.data('method-id');
                    
                    if (textareaId && methodId) {
                        var $textarea = $('#' + textareaId);
                        // Always update to ensure correct format display
                        updateResultPreview(textareaId, methodId);
                    }
                });
            }, 500);

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
                // Simple direct replacement - no complex placeholder system
                // Step 1: Handle comparison symbols first
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                // Step 2: Convert ^() to <sup> and _() to <sub>
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                return value;
            }

            // Convert from HTML <sup> and <sub> to ^() and _() format for our system
            function convertFromTinyMCE(value) {
                if (!value) return '';
                // Simple direct replacement
                // Step 1: Convert HTML tags to ^() and _() format
                value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
                // Step 2: Strip any remaining HTML tags
                value = value.replace(/<[^>]*>/g, '');
                // Step 3: Decode HTML entities
                value = value.replace(/&le;/gi, '≤');
                value = value.replace(/&ge;/gi, '≥');
                value = value.replace(/&lt;/g, '<');
                value = value.replace(/&gt;/g, '>');
                value = value.replace(/&plusmn;/g, '±');
                value = value.replace(/&nbsp;/g, ' ');
                return value;
            }

            // Make functions globally accessible for modal handlers
            window.convertToTinyMCE = convertToTinyMCE;
            window.convertFromTinyMCE = convertFromTinyMCE;

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

            // Create result badge based on status
            function createResultBadge(value, status, kesimpulanBakuMutu) {
                if (value === undefined || value === null) {
                    value = '';
                }
                value = String(value || '');

                if (kesimpulanBakuMutu === undefined || kesimpulanBakuMutu === null) {
                    kesimpulanBakuMutu = '';
                }
                kesimpulanBakuMutu = String(kesimpulanBakuMutu || '');

                if (status === 'success') {
                    var kesimpulanHtml = kesimpulanBakuMutu
                        ? ' <small style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                        : '';
                    return '<span class="badge badge-success font-weight-bold" style="font-size: 14px; padding: 8px 12px; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;"><i class="fa fa-check-circle mr-1"></i>' + value + kesimpulanHtml + '</span>';
                }

                // Melewati baku mutu: hanya tampilkan kesimpulan jika ada (tanpa default "Tidak sesuai")
                var kesimpulanHtml = kesimpulanBakuMutu && String(kesimpulanBakuMutu).trim()
                    ? '<br><small class="bm-kesimpulan-hasil" style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                    : '';
                var markedValue = String(value || '');
                var star = '<span class="bintang-baku-mutu">&nbsp;*</span>';
                markedValue = appendAbnormalAsteriskToFirstLine(markedValue, star);
                return '<span class="badge badge-danger hasil-melewati-baku-mutu" style="font-size: 14px; padding: 8px 12px; font-weight: 700; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;"><strong>' + markedValue + '</strong>' + kesimpulanHtml + '</span>';
            }

            // Helper function to decode HTML entities and normalize whitespace
            function decodeHtmlEntities(str) {
                if (!str) return '';
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = str.toString();
                var decoded = tempDiv.textContent || tempDiv.innerText || '';
                // Normalize all whitespace (multiple spaces, tabs, newlines) to single space
                decoded = decoded.replace(/\s+/g, ' ').trim();
                return decoded;
            }
            
            function normalizeComparisonOperatorDisplay(str) {
                if (!str) return str;
                return String(str).replace(/(^|[\s,(;])\?\s*(?=\d)/g, '$1≥ ');
            }

            // Helper function to normalize string for comparison (decode HTML, remove all whitespace)
            function normalizeForComparison(str) {
                if (!str) return '';
                str = normalizeComparisonOperatorDisplay(str.toString());
                // Decode HTML entities first
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = str;
                var decoded = tempDiv.textContent || tempDiv.innerText || '';
                // Remove ALL whitespace (spaces, tabs, newlines, etc.) for comparison
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

                // Manual override harus menang sebelum urinalisa / min-max
                if (offset_baku_mutu === 'false') {
                    return createResultBadge(formatUrinalisaFindingsHtml(value || ''), 'success');
                } else if (offset_baku_mutu === 'true') {
                    return createResultBadge(formatUrinalisaFindingsHtml(value || ''), 'danger');
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

                // Store original equal for display (before normalization)
                var equalOriginal = equal;

                // Normalize equal - remove all whitespace (spaces, newlines, tabs, etc.) and decode HTML entities
                if (equal && equal !== '') {
                    equal = normalizeForComparison(equal);
                }

                console.log("numberFormat= ", numberFormat);

                var melewati = false;
                var hasMultipleBakuMutu = multipleBakuMutu && multipleBakuMutu.length > 1;
                var isOutsideNormalRange = false;
                var kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';

                if (true) {
                    // Default: Check automatically based on min/max/equal
                    var numValue = null;
                    var melewati = false;

                    // Prioritas: cocokkan ke SEMUA baris baku mutu bila >1
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
                        // Utamakan is_normal=1
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
                        // Cocokkan equal termasuk "(+) Nama" vs "(+)" / "Pos 1 (+)"
                        melewati = !bakuMutuEqualMatches(evalValue, equalOriginal || equal);
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

                    // Ensure kesimpulanBakuMutu is not undefined
                    kesimpulanBakuMutu = kesimpulanBakuMutu || '';
                    if (kesimpulanBakuMutu === undefined || kesimpulanBakuMutu === null) {
                        kesimpulanBakuMutu = '';
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

            // Mark checkBakuMutu as ready
            window.checkBakuMutuReady = true;
            window.checkBakuMutuDefined = true;
            console.log('checkBakuMutu function defined and available globally');

            // Update result preview
            function updateResultPreview(targetId, methodId) {
                var $textarea = $('#' + targetId);
                var value = $textarea.val();
                var min = $textarea.attr('data-min') || '';
                var max = $textarea.attr('data-max') || '';
                var equal = $textarea.attr('data-equal') || '';

                if (methodId.indexOf('param_') === 0) {
                    var previewParamNo = methodId.replace('param_', '');
                    var $previewDual = $('.urinalisa-dual-input[data-param-no="' + previewParamNo + '"]');
                    if ($previewDual.length && isUrinalisaDualWrapIncomplete($previewDual)) {
                        clearUrinalisaDualPreview(previewParamNo);
                        return;
                    }
                }

                // Get offset_baku_mutu from hidden input (bukan radio :checked di modal)
                var offset_baku_mutu = 'default';
                var $row = $textarea.closest('tr');
                if (targetId.indexOf('sub_') !== -1 || methodId.indexOf('sub_') !== -1) {
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
                var $row = $textarea.closest('tr');
                var multipleBakuMutuData = $row.find('[data-multiple-baku-mutu]').attr('data-multiple-baku-mutu');
                if (multipleBakuMutuData) {
                    try {
                        multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                    } catch (e) {
                        console.error('Error parsing multiple baku mutu data:', e);
                        multipleBakuMutu = null;
                    }
                }

                // Get kesimpulan baku mutu from data attributes or form data
                var kesimpulanBakuMutu = '';
                if (methodId.includes('param_')) {
                    var paramNo = methodId.replace('param_', '');
                    kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_param_' + paramNo).val() || '';
                } else if (methodId.includes('sub_')) {
                    var subNo = methodId.replace('sub_', '');
                    kesimpulanBakuMutu = $('#kesimpulan_baku_mutu_sub_' + subNo).val() || '';
                }

                var parameterName = $textarea.attr('data-name') || '';
                var numberFormat = $textarea.attr('data-number-format') || 'en';

                var output = checkBakuMutu(value, min, max, equal, offset_baku_mutu, multipleBakuMutu,
                    kesimpulanBakuMutu, numberFormat, parameterName);
                
                // Update result_output (simulasi output)
                $('#result_output_' + methodId).html(output || '-');

                // Inline editor (termasuk urinalisa dual) memakai #badge_{n}; result-display sering sudah dihapus
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
                
                // Update result_display dengan format yang sama seperti simulasi output
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
            window.updateResultPreview = updateResultPreview;

            // Debounce preview saat mengetik — hindari memanggil checkBakuMutu + repaint DOM tiap keypress (banyak parameter = macet)
            var __resultPreviewDebounce = {};
            function updateResultPreviewDebounced(targetId, methodId, ms) {
                if (!targetId || !methodId) return;
                ms = ms === undefined ? 120 : ms;
                var k = targetId + '\0' + methodId;
                clearTimeout(__resultPreviewDebounce[k]);
                __resultPreviewDebounce[k] = setTimeout(function() {
                    delete __resultPreviewDebounce[k];
                    updateResultPreview(targetId, methodId);
                }, ms);
            }

            // Urinalisa Kristal/Silinder/Lain-lain: grade per jenis, nama bisa lebih dari satu.
            // Contoh tersimpan:
            // Ca Oxalate (++)
            // Asam Urat (+++)
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

                if (positivity.toLowerCase() === 'negatif') {
                    $wrap.find('.urinalisa-detail-input').removeClass('is-invalid');
                    if ($detailHint.length) {
                        $detailHint.addClass('d-none');
                    }
                    $textarea.val('Negatif');
                    updateResultPreview(textareaId, 'param_' + paramNo);
                    return;
                }

                var composedParts = [];

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

            // Collect all editor buttons on page load (in DOM order)
            function collectEditorButtons() {
                allEditorButtons = [];
                $('.open-editor-modal').each(function(index) {
                    allEditorButtons.push({
                        button: $(this),
                        methodId: $(this).data('method-id'),
                        targetId: $(this).data('target'),
                        methodName: $(this).data('method-name'),
                        index: index
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

                    // Set current target BEFORE getting value
                    currentEditorTarget = targetId;
                    currentMethodId = methodId;

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

            // Function to get next target ID (based on DOM order, same type only)
            function getNextTargetId() {
                if (!currentEditorTarget || allEditorButtons.length === 0) {
                    return null;
                }

                // Determine current input type (hasil or keterangan)
                var currentType = '';
                if (currentEditorTarget.startsWith('result_') || currentEditorTarget.startsWith('hasil_')) {
                    currentType = 'hasil';
                } else if (currentEditorTarget.startsWith('keterangan')) {
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

                        if (nextTargetId.startsWith('result_') || nextTargetId.startsWith('hasil_')) {
                            nextType = 'hasil';
                        } else if (nextTargetId.startsWith('keterangan')) {
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

            // Open editor modal - use targetId directly from clicked button
            $('.open-editor-modal').on('click', function() {
                var targetId = $(this).data('target');
                openEditorForTarget(targetId);
            });

            // Helper function to ensure TinyMCE is ready before init
            function ensureTinyMCEReady(callback, retries) {
                retries = retries || 0;
                if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                    callback();
                } else if (retries < 50) {
                    setTimeout(function() {
                        ensureTinyMCEReady(callback, retries + 1);
                    }, 100);
                } else {
                    console.error('TinyMCE failed to load after 5 seconds');
                }
            }

            // Initialize TinyMCE when modal is shown
            $('#editorModal').on('shown.bs.modal', function() {
                // Remove existing editor instance if any
                if (editorInstance && typeof tinymce !== 'undefined') {
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

                // Initialize TinyMCE - ensure it's ready first
                ensureTinyMCEReady(function() {
                    tinymce.init({
                    selector: '#editor_content',
                    height: 300,
                    theme: 'modern', // Use theme available in local assets
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
            });

            // Save from editor to textarea
            function saveEditorContent(goToNext) {
                goToNext = goToNext || false;

                if (editorInstance && currentEditorTarget) {
                    // Get content from TinyMCE (HTML format)
                    var htmlContent = editorInstance.getContent();

                    // Convert from TinyMCE HTML format to our ^() format
                    var convertedContent = convertFromTinyMCE(htmlContent);

                    // Set to original textarea
                    $('#' + currentEditorTarget).val(convertedContent);

                    // Trigger input event to update preview
                    $('#' + currentEditorTarget).trigger('input');

                    if (goToNext) {
                        // Get next target ID
                        var nextTargetId = getNextTargetId();
                        if (nextTargetId) {
                            // Close modal first, then open next
                            $('#editorModal').modal('hide');

                            // Wait for modal to close, then open next
                            $('#editorModal').on('hidden.bs.modal', function() {
                                $('#editorModal').off('hidden.bs.modal');
                                setTimeout(function() {
                                    openEditorForTarget(nextTargetId);
                                }, 300);
                            });
                        } else {
                            // No next target, just close modal
                            $('#editorModal').modal('hide');
                        }
                    } else {
                        // Close modal
                        $('#editorModal').modal('hide');
                    }
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

            // Handle input on textarea (debounced preview — Enter/multiline tidak memblokir UI)
            $(document).on('input', '.result_method_klinik', function() {
                var targetId = $(this).attr('id');
                var $editorBtn = $(this).closest('td').find('.open-editor-modal');
                if ($editorBtn.length) {
                    var methodId = $editorBtn.data('method-id');
                    updateResultPreviewDebounced(targetId, methodId, 120);
                }
            });

            // Saat pindah field, langsung flush preview (tanpa tunggu debounce)
            $(document).on('blur', '.result_method_klinik', function() {
                var targetId = $(this).attr('id');
                var $editorBtn = $(this).closest('td').find('.open-editor-modal');
                if ($editorBtn.length) {
                    var methodId = $editorBtn.data('method-id');
                    var k = targetId + '\0' + methodId;
                    if (__resultPreviewDebounce[k]) {
                        clearTimeout(__resultPreviewDebounce[k]);
                        delete __resultPreviewDebounce[k];
                    }
                    updateResultPreview(targetId, methodId);
                }
            });

            // Handler terpisah: perubahan nilai kreatinin (setelah sinkron ke textarea) untuk hitung eGFR
            $(document).on('change input', '.result_method_klinik', function() {
                var paramName = $(this).data('name') || $(this).attr('data-name');
                if (!isKreatininParamName(paramName)) {
                    return;
                }

                var kreatininValue = extractNumericHasil($(this).val());
                if (!kreatininValue) {
                    return;
                }

                var gender = typeof pasienGender !== 'undefined' ? pasienGender : 'L';
                var age = typeof pasienAge !== 'undefined' ? pasienAge : 0;
                if (age > 0 && typeof calculateEfgr === 'function') {
                    calculateEfgr(gender, age, kreatininValue);
                }
            });

            // Jika Kreatinin sudah terisi saat buka halaman, hitung eGFR otomatis
            setTimeout(function() {
                if (typeof recalculateEgfrFromForm === 'function') {
                    recalculateEgfrFromForm();
                }
            }, 800);

            // Update simulasi output untuk parameter dengan multiple baku mutu saat halaman dimuat
            $('[data-multiple-baku-mutu]').each(function() {
                var $resultDiv = $(this);
                var targetId = $resultDiv.attr('id');
                var multipleBakuMutuData = $resultDiv.attr('data-multiple-baku-mutu');

                if (multipleBakuMutuData && multipleBakuMutuData !== '[]') {
                    try {
                        var multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                        // Check if this has multiple normal baku mutu
                        var normalCount = multipleBakuMutu.filter(function(bm) {
                            return bm.is_normal == 1;
                        }).length;

                        if (normalCount > 0) {
                            // Find corresponding input field
                            var inputId = '';
                            // targetId sudah didefinisikan di line sebelumnya dari $resultDiv.attr('id')
                            if (targetId && typeof targetId === 'string' && targetId.includes('result_output_param_')) {
                                var paramNo = targetId.replace('result_output_param_', '');
                                inputId = 'hasil_permohonan_uji_parameter_klinik_' + paramNo;
                            } else if (targetId && typeof targetId === 'string' && targetId.includes('result_output_sub_')) {
                                var subNo = targetId.replace('result_output_sub_', '');
                                inputId = 'hasil_permohonan_uji_sub_parameter_klinik_' + subNo;
                            }

                            if (inputId) {
                                var $input = $('#' + inputId);
                                var currentValue = $input.val();

                                if (currentValue && currentValue !== '' && currentValue !== '-') {
                                    // Find editor button to get methodId
                                    var $row = $input.closest('tr');
                                    var $editorBtn = $row.find('.open-editor-modal');
                                    if ($editorBtn.length) {
                                        var methodId = $editorBtn.data('method-id');
                                        if (methodId) {
                                    // Update preview dengan nilai yang ada
                                            updateResultPreview(inputId, methodId);
                                        }
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing multiple baku mutu data:', e);
                    }
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
                    
                    // Note: Preview initialization saat halaman dimuat sudah ditangani di bagian "Initialize all previews on page load"
                }
            });


            // Function to validate all parameters are filled
            function validateAllParametersFilled() {
                var missingParams = [];

                $('.urinalisa-dual-input').each(function() {
                    syncUrinalisaDualInput($(this).data('param-no'));
                });

                collectIncompleteUrinalisaParams().forEach(function(paramName) {
                    if (missingParams.indexOf(paramName) === -1) {
                        missingParams.push(paramName);
                    }
                });
                
                // First, sync all inline input/dropdown values to textarea
                // Sync dropdown values
                $('select.inline-hasil-input').each(function() {
                    var $select = $(this);
                    var selectedValue = $select.val() || '';
                    var textareaId = $select.data('textarea-id');
                    if (textareaId && selectedValue) {
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
                            if (editorId) {
                                var editorInstance = tinymce.get(editorId);
                                if (editorInstance && editorInstance.serializer) {
                                    try {
                                        var content = editorInstance.getContent();
                                        $('#' + textareaId).val(content);
                                    } catch (e) {
                                        console.error('TinyMCE getContent error (hasil editor):', editorId, e);
                                    }
                                }
                            }
                        }
                    });
                    
                    // Sync TinyMCE keterangan editor values
                    $('.inline-keterangan-editor').each(function() {
                        var $editor = $(this);
                        var textareaId = $editor.data('textarea-id');
                        if (textareaId) {
                            var editorId = $editor.attr('id');
                            if (editorId) {
                                var editorInstance = tinymce.get(editorId);
                                if (editorInstance && editorInstance.serializer) {
                                    try {
                                        var content = editorInstance.getContent();
                                        $('#' + textareaId).val(content);
                                    } catch (e) {
                                        console.error('TinyMCE getContent error (keterangan editor):', editorId, e);
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Check all parameter results (main parameters) - check both textarea and inline input
                $('.result_method_klinik').each(function() {
                    var $textarea = $(this);
                    var id = $textarea.attr('id');
                    var value = $textarea.val() || '';
                    
                    // If textarea is empty, check if there's an inline input/dropdown with value
                    if (!value || value.trim() === '' || value === '-') {
                        var $row = $textarea.closest('tr');
                        // Check for inline dropdown
                        var $dropdown = $row.find('select.inline-hasil-input[data-textarea-id="' + id + '"]');
                        if ($dropdown.length > 0) {
                            value = $dropdown.val() || '';
                        }
                        
                        // Check for TinyMCE inline editor
                        if ((!value || value.trim() === '' || value === '-') && typeof tinymce !== 'undefined') {
                            var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + id + '"]');
                            if ($editor.length > 0) {
                                var editorId = $editor.attr('id');
                                if (editorId) {
                                    var editorInstance = tinymce.get(editorId);
                                    if (editorInstance && editorInstance.serializer) {
                                        try {
                                            value = editorInstance.getContent() || '';
                                        } catch (e) {
                                            console.error('TinyMCE getContent error (validation hasil editor):', editorId, e);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Skip if empty (not filled)
                    if (!value || value.trim() === '' || value === '-') {
                        // Coba ambil nama parameter dari atribut data-name (lebih bersih, tanpa badge "Perlu Diperbaiki")
                        var paramName = ($textarea.data('name') || '').toString().trim();

                        // Jika data-name tidak tersedia, fallback ke teks pada kolom pertama (seperti sebelumnya)
                        if (!paramName) {
                            var $row = $textarea.closest('tr');
                            // Skip jika ini baris header (punya colspan)
                            if ($row.find('th[colspan]').length > 0) {
                                return true; // skip header rows
                            }

                            paramName = $row.find('td').first().text().trim();
                            // Remove leading "-" and "~" jika ada
                            paramName = paramName.replace(/^[-~]\s*/, '').trim();
                        }

                        if (paramName && paramName !== '' && !paramName.match(/^Urin Rutin|^Hematologi|^Kimia Klinik|^Serologi/i)) {
                            missingParams.push(paramName);
                        }
                    }
                });
                
                // Check all sub-parameter results - check both textarea and inline input
                $('textarea[id^="hasil_permohonan_uji_sub_parameter_klinik_"]').each(function() {
                    var $textarea = $(this);
                    var id = $textarea.attr('id');
                    var value = $textarea.val() || '';
                    
                    // If textarea is empty, check if there's an inline input/dropdown with value
                    if (!value || value.trim() === '' || value === '-') {
                        var $row = $textarea.closest('tr');
                        // Check for inline dropdown
                        var $dropdown = $row.find('select.inline-hasil-input[data-textarea-id="' + id + '"]');
                        if ($dropdown.length > 0) {
                            value = $dropdown.val() || '';
                        }
                        
                        // Check for TinyMCE inline editor
                        if ((!value || value.trim() === '' || value === '-') && typeof tinymce !== 'undefined') {
                            var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + id + '"]');
                            if ($editor.length > 0) {
                                var editorId = $editor.attr('id');
                                if (editorId && tinymce.get(editorId)) {
                                    value = tinymce.get(editorId).getContent() || '';
                                }
                            }
                        }
                    }
                    
                    // Skip if empty (not filled)
                    if (!value || value.trim() === '' || value === '-') {
                        // Coba ambil nama parameter dari atribut data-name terlebih dahulu
                        var paramName = ($textarea.data('name') || '').toString().trim();

                        // Jika tidak ada data-name, fallback ke teks kolom pertama
                        if (!paramName) {
                            var $row = $textarea.closest('tr');
                            paramName = $row.find('td').first().text().trim();
                            // Remove leading "-" and "~" jika ada
                            paramName = paramName.replace(/^[-~]\s*/, '').trim();
                        }

                        if (paramName && paramName !== '') {
                            missingParams.push(paramName);
                        }
                    }
                });
                
                // Check dropdown results (legacy - for backward compatibility)
                $('.result-dropdown-klinik').each(function() {
                    var $select = $(this);
                    var selectedValue = $select.val() || '';
                    var textareaId = $select.data('textarea-id');
                    
                    if (!selectedValue || selectedValue === '') {
                        // Get parameter name from the row
                        var $row = $select.closest('tr');
                        // Skip if this is a header row
                        if ($row.find('th[colspan]').length > 0) {
                            return true; // skip header rows
                        }
                        
                        var paramName = $row.find('td').first().text().trim();
                        // Remove leading "-" and "~" if present
                        paramName = paramName.replace(/^[-~]\s*/, '').trim();
                        
                        if (paramName && paramName !== '' && !paramName.match(/^Urin Rutin|^Hematologi|^Kimia Klinik|^Serologi/i)) {
                            missingParams.push(paramName);
                        }
                    }
                });
                
                return {
                    isValid: missingParams.length === 0,
                    missingParams: missingParams
                };
            }

            // Highlight semua baris parameter yang belum diisi (dengan animasi halus)
            function highlightMissingParameters(missingParams) {
                // Bersihkan highlight lama terlebih dahulu
                $('tr.missing-param-highlight').removeClass('missing-param-highlight');

                if (!missingParams || !missingParams.length) {
                    return;
                }

                // Kita hanya pakai satu parameter teratas saja
                var firstName = (missingParams[0] || '').toString().trim();
                if (!firstName) {
                    return;
                }
                var targetLower = firstName.toLowerCase();

                var $targetRow = null;

                $('.result_method_klinik[data-name]').each(function() {
                    var $textarea = $(this);
                    var name = ($textarea.data('name') || '').toString().trim().toLowerCase();
                    if (!name) {
                        return;
                    }

                    // Cari kecocokan dengan nama pertama yang belum diisi (full/partial)
                    var isMatch =
                        name === targetLower ||
                        name.indexOf(targetLower) !== -1 ||
                        targetLower.indexOf(name) !== -1;

                    if (isMatch) {
                        var $row = $textarea.closest('tr');
                        if ($row.length) {
                            $targetRow = $row;
                            return false; // break each
                        }
                    }
                });

                if ($targetRow && $targetRow.length) {
                    $targetRow.addClass('missing-param-highlight');
                }

                // Hapus highlight setelah beberapa detik supaya tidak mengganggu terus
                setTimeout(function() {
                    $('tr.missing-param-highlight').removeClass('missing-param-highlight');
                }, 6000);
            }

            // Fokus & scroll ke parameter pertama yang belum diisi
            function focusFirstMissingParameter(missingParams) {
                if (!missingParams || !missingParams.length) {
                    return;
                }

                var targetName = (missingParams[0] || '').toString().trim();
                if (!targetName) {
                    return;
                }

                var $targetTextarea = null;
                var targetLower = targetName.toLowerCase();

                // Cari berdasarkan data-name yang paling tepat
                $('.result_method_klinik[data-name]').each(function() {
                    var name = ($(this).data('name') || '').toString().trim();
                    if (!name) {
                        return;
                    }

                    var nameLower = name.toLowerCase();

                    // Prioritaskan kecocokan penuh
                    if (nameLower === targetLower) {
                        $targetTextarea = $(this);
                        return false; // break
                    }

                    // Jika belum ketemu, izinkan kecocokan partial (nama mengandung target atau sebaliknya)
                    if (!$targetTextarea &&
                        (nameLower.indexOf(targetLower) !== -1 || targetLower.indexOf(nameLower) !== -1)) {
                        $targetTextarea = $(this);
                    }
                });

                if (!$targetTextarea || !$targetTextarea.length) {
                    return;
                }

                var $row = $targetTextarea.closest('tr');
                if (!$row.length) {
                    return;
                }

                // Scroll halus ke baris parameter di dalam tabel #tableParameterResponsive
                var $tableResponsive = $('#tableParameterResponsive');
                if ($tableResponsive.length) {
                    // Simpan posisi scroll window saat ini
                    var savedScrollY = window.scrollY;
                    var savedScrollX = window.scrollX;
                    
                    // Hitung posisi row relatif terhadap tabel di dalam #tableParameterResponsive
                    setTimeout(function() {
                        var $table = $tableResponsive.find('table'); // Ambil tabel di dalam #tableParameterResponsive
                        if ($table.length && $row.closest('#tableParameterResponsive').length) {
                            var rowPosition = $row.position().top; // Posisi relatif terhadap parent table
                            var currentScroll = $tableResponsive.scrollTop();
                            
                            // Hitung target scroll: posisi row dikurangi sedikit offset
                            var targetScroll = rowPosition + currentScroll - 100;
                            
                            // Pastikan tidak scroll negatif dan tidak melebihi max scroll
                            var maxScroll = $tableResponsive[0].scrollHeight - $tableResponsive[0].clientHeight;
                            targetScroll = Math.max(0, Math.min(targetScroll, maxScroll));
                            
                            // Scroll di dalam tabel
                            $tableResponsive.animate({ scrollTop: targetScroll }, 500, function() {
                                // Setelah scroll selesai, focus ke textarea dengan mencegah scroll window
                                // Gunakan scrollIntoView dengan block: 'nearest' untuk mencegah scroll window
                                if ($targetTextarea[0] && $targetTextarea[0].scrollIntoView) {
                                    $targetTextarea[0].scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'nearest' });
                                }
                                
                                // Focus tanpa trigger scroll window
                                if ($targetTextarea[0]) {
                                    $targetTextarea[0].focus();
                                }
                                
                                // Kembalikan posisi scroll window setelah focus (beberapa kali untuk memastikan)
                                var restoreScroll = function() {
                                    if (window.scrollY !== savedScrollY || window.scrollX !== savedScrollX) {
                                        window.scrollTo(savedScrollX, savedScrollY);
                                    }
                                };
                                
                                setTimeout(restoreScroll, 0);
                                setTimeout(restoreScroll, 10);
                                setTimeout(restoreScroll, 50);
                                setTimeout(restoreScroll, 100);
                                setTimeout(restoreScroll, 200);
                            });
                        } else {
                            // Focus tanpa scroll window
                            if ($targetTextarea[0] && $targetTextarea[0].scrollIntoView) {
                                $targetTextarea[0].scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'nearest' });
                            }
                            if ($targetTextarea[0]) {
                                $targetTextarea[0].focus();
                            }
                            
                            var restoreScroll = function() {
                                if (window.scrollY !== savedScrollY || window.scrollX !== savedScrollX) {
                                    window.scrollTo(savedScrollX, savedScrollY);
                                }
                            };
                            
                            setTimeout(restoreScroll, 0);
                            setTimeout(restoreScroll, 10);
                            setTimeout(restoreScroll, 50);
                            setTimeout(restoreScroll, 100);
                        }
                    }, 150);
                } else {
                    // Jika tabel tidak ditemukan, focus saja tanpa scroll
                    setTimeout(function() {
                        $targetTextarea.focus();
                    }, 100);
                }
            }

            // Function to submit form (reusable for both save and selesai)
            function submitForm(requireAllFilled, isSelesai) {
                requireAllFilled = requireAllFilled || false;
                isSelesai = isSelesai || false;
                
                // Sync TinyMCE catatan & kesimpulan ke textarea sebelum submit
                ['catatan_hasil', 'kesimpulan_hasil'].forEach(function(fieldId) {
                    if (typeof tinymce !== 'undefined' && tinymce.get(fieldId)) {
                        var editor = tinymce.get(fieldId);
                        if (editor && !editor.removed) {
                            editor.save();
                            $('#' + fieldId).val(editor.getContent());
                        }
                    }
                });
                
                // Add hidden input to indicate if this is "selesai" or "simpan sementara"
                var $existingInput = $('#is_selesai');
                if ($existingInput.length > 0) {
                    $existingInput.remove();
                }
                $('<input>').attr({
                    type: 'hidden',
                    id: 'is_selesai',
                    name: 'is_selesai',
                    value: isSelesai ? '1' : '0'
                }).appendTo('#form');
                
                // Validasi: Analis harus diisi terlebih dahulu
                // Cek apakah analis sudah locked (hidden input) - jika iya, skip validasi
                var $analisField = $('#analis_permohonan_uji_klinik');
                var isAnalisLocked = $analisField.is('input[type="hidden"]');
                
                // Jika analis sudah locked, skip validasi
                if (isAnalisLocked) {
                    console.log('Analis sudah locked, skip validasi analis');
                } else {
                    // Jika analis belum locked (masih select), cek apakah sudah dipilih
                    var analisValue = $analisField.val();
                    if (!analisValue || analisValue === '' || analisValue === null) {
                        swal({
                            title: "Peringatan!",
                            text: "Analis harus diisi terlebih dahulu sebelum menyimpan hasil.",
                            icon: "warning",
                            button: "OK"
                        });
                        // Focus ke field analis
                        $analisField.focus();
                        return false;
                    }
                }

                // Urinalisa Kristal/Silinder: Positif wajib pilih jenis sebelum simpan/selesai
                $('.urinalisa-dual-input').each(function() {
                    syncUrinalisaDualInput($(this).data('param-no'));
                });

                var urinalisaIncomplete = collectIncompleteUrinalisaParams();
                if (urinalisaIncomplete.length > 0) {
                    var urinalisaMessage = urinalisaIncomplete.length === 1
                        ? 'Hasil Positif belum lengkap untuk parameter berikut (pilih jenis terlebih dahulu):'
                        : 'Hasil Positif belum lengkap untuk ' + urinalisaIncomplete.length + ' parameter berikut (pilih jenis terlebih dahulu):';

                    var urinalisaContentDiv = document.createElement('div');
                    urinalisaContentDiv.style.textAlign = 'center';
                    urinalisaContentDiv.style.marginBottom = '15px';
                    urinalisaContentDiv.style.fontWeight = '500';
                    urinalisaContentDiv.style.color = '#333';
                    urinalisaContentDiv.style.fontSize = '15px';
                    urinalisaContentDiv.textContent = urinalisaMessage;

                    var urinalisaListDiv = document.createElement('div');
                    urinalisaListDiv.style.textAlign = 'left';
                    urinalisaListDiv.style.margin = '15px 0';
                    urinalisaListDiv.style.maxHeight = '300px';
                    urinalisaListDiv.style.overflowY = 'auto';
                    urinalisaListDiv.style.backgroundColor = '#f8f9fa';
                    urinalisaListDiv.style.padding = '15px';
                    urinalisaListDiv.style.borderRadius = '5px';
                    urinalisaListDiv.style.border = '1px solid #dee2e6';

                    var urinalisaUl = document.createElement('ul');
                    urinalisaUl.style.margin = '0';
                    urinalisaUl.style.paddingLeft = '20px';

                    urinalisaIncomplete.forEach(function(param) {
                        if (param && param.trim() !== '') {
                            var li = document.createElement('li');
                            li.style.margin = '8px 0';
                            li.style.fontSize = '14px';
                            li.textContent = param;
                            urinalisaUl.appendChild(li);
                        }
                    });

                    urinalisaListDiv.appendChild(urinalisaUl);

                    var urinalisaWrapper = document.createElement('div');
                    urinalisaWrapper.appendChild(urinalisaContentDiv);
                    urinalisaWrapper.appendChild(urinalisaListDiv);

                    swal({
                        title: "Peringatan!",
                        content: urinalisaWrapper,
                        icon: "warning",
                        button: "OK"
                    }).then(function() {
                        try {
                            highlightMissingParameters(urinalisaIncomplete);
                            focusFirstMissingParameter(urinalisaIncomplete);
                        } catch (e) {
                            console.error('Error highlighting incomplete urinalisa params:', e);
                        }
                    });

                    return false;
                }

                // If requireAllFilled, validate all parameters are filled
                if (requireAllFilled) {
                    var validation = validateAllParametersFilled();
                    
                    if (!validation.isValid) {
                        // Pastikan ada parameter yang belum diisi
                        if (!validation.missingParams || validation.missingParams.length === 0) {
                            swal({
                                title: "Peringatan!",
                                text: "Hasil Pemeriksaan belum diisi. Silakan lengkapi semua parameter terlebih dahulu.",
                                icon: "warning",
                                button: "OK"
                            });
                            return false;
                        }
                        
                        // Format semua parameter yang belum diisi sebagai list
                        var totalMissing = validation.missingParams.length;
                        var messageText = totalMissing === 1 
                            ? "Hasil Pemeriksaan belum diisi untuk parameter berikut:" 
                            : "Hasil Pemeriksaan belum diisi untuk " + totalMissing + " parameter berikut:";
                        
                        // Buat DOM element untuk content
                        var contentDiv = document.createElement('div');
                        contentDiv.style.textAlign = 'center';
                        contentDiv.style.marginBottom = '15px';
                        contentDiv.style.fontWeight = '500';
                        contentDiv.style.color = '#333';
                        contentDiv.style.fontSize = '15px';
                        contentDiv.textContent = messageText;
                        
                        var listDiv = document.createElement('div');
                        listDiv.style.textAlign = 'left';
                        listDiv.style.margin = '15px 0';
                        listDiv.style.maxHeight = '300px';
                        listDiv.style.overflowY = 'auto';
                        listDiv.style.backgroundColor = '#f8f9fa';
                        listDiv.style.padding = '15px';
                        listDiv.style.borderRadius = '5px';
                        listDiv.style.border = '1px solid #dee2e6';
                        
                        var ul = document.createElement('ul');
                        ul.style.margin = '0';
                        ul.style.paddingLeft = '20px';
                        
                        validation.missingParams.forEach(function(param) {
                            if (param && param.trim() !== '') {
                                var li = document.createElement('li');
                                li.style.margin = '8px 0';
                                li.style.fontSize = '14px';
                                li.textContent = param;
                                ul.appendChild(li);
                            }
                        });
                        
                        listDiv.appendChild(ul);
                        
                        var wrapper = document.createElement('div');
                        wrapper.appendChild(contentDiv);
                        wrapper.appendChild(listDiv);
                        
                        swal({
                            title: "Peringatan!",
                            content: wrapper,
                            icon: "warning",
                            button: "OK"
                        }).then(function() {
                            // Setelah popup ditutup:
                            // 1. Highlight semua baris yang belum diisi (dengan animasi hover)
                            // 2. Arahkan ke parameter pertama yang belum diisi
                            try {
                                highlightMissingParameters(validation.missingParams);
                                focusFirstMissingParameter(validation.missingParams);
                            } catch (e) {
                                console.error('Error focusFirstMissingParameter:', e);
                            }
                        });
                        return false;
                    }
                }

                // Sync semua inline input/dropdown dan TinyMCE editor ke textarea sebelum submit
                // Sync dropdown values (inline-hasil-input)
                $('select.inline-hasil-input').each(function() {
                    var $select = $(this);
                    var selectedValue = $select.val() || '';
                    var textareaId = $select.data('textarea-id');
                    if (textareaId && selectedValue) {
                        $('#' + textareaId).val(selectedValue);
                    }
                });
                
                // Sync TinyMCE inline editor values (hasil)
                if (typeof tinymce !== 'undefined') {
                    $('.inline-hasil-editor').each(function() {
                        var $editor = $(this);
                        var textareaId = $editor.data('textarea-id');
                        if (textareaId) {
                            var editorId = $editor.attr('id');
                            if (editorId) {
                                var editorInstance = tinymce.get(editorId);
                                if (editorInstance && editorInstance.serializer) {
                                    try {
                                        var content = editorInstance.getContent();
                                        $('#' + textareaId).val(content);
                                    } catch (e) {
                                        console.error('TinyMCE getContent error (submit hasil editor):', editorId, e);
                                    }
                                }
                            }
                        }
                    });
                    
                    // Sync TinyMCE keterangan editor values
                    $('.inline-keterangan-editor').each(function() {
                        var $editor = $(this);
                        var textareaId = $editor.data('textarea-id');
                        if (textareaId) {
                            var editorId = $editor.attr('id');
                            if (editorId) {
                                var editorInstance = tinymce.get(editorId);
                                if (editorInstance && editorInstance.serializer) {
                                    try {
                                        var content = editorInstance.getContent();
                                        $('#' + textareaId).val(content);
                                    } catch (e) {
                                        console.error('TinyMCE getContent error (submit keterangan editor):', editorId, e);
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Sync contenteditable keterangan editor values (non-TinyMCE)
                $('.inline-keterangan-editor[contenteditable="true"]').each(function() {
                    var $editor = $(this);
                    var textareaId = $editor.data('textarea-id');
                    if (textareaId) {
                        var content = $editor.html() || '';
                        $('#' + textareaId).val(content);
                    }
                });

                if (typeof window.syncMetodeInlineEditorsToTextareas === 'function') {
                    window.syncMetodeInlineEditorsToTextareas();
                }
                
                // Sync legacy dropdown results (backward compatibility)
                $('.result-dropdown-klinik').each(function() {
                    var textareaId = $(this).data('textarea-id');
                    var selectedValue = $(this).val();
                    if (textareaId && selectedValue) {
                        $('#' + textareaId).val(selectedValue);
                    }
                });

                swal({
                    title: "Menyimpan...",
                    text: "Harap tunggu beberapa saat.",
                    icon: "info",
                    buttons: false,
                    closeOnClickOutside: false,
                });

                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status) {
                            swal('Berhasil!', response.pesan || 'Data berhasil disimpan', 'success').then(() => {
                                // Jika ada redirect_url (ketika selesai), redirect ke halaman verifikasi
                                // Jika tidak ada (simpan sementara), reload halaman
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            swal('Error!', response.pesan || 'Terjadi kesalahan saat menyimpan', 'error');
                        }
                    },
                    error: function() {
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            }

            // Handler untuk tombol Simpan (bisa menyimpan meskipun belum selesai)
            // ===== Ambil hasil / Make Order TMS =====
            var tmsMatchedCache = [];
            var tmsOrderParamsCache = [];
            var tmsOrdersCache = [];
            var tmsEditingOrderId = null;
            var tmsEditingOrder = null;
            var tmsFetchUrl = "{{ route('elits-permohonan-uji-klinik-2.fetch-tms-results', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsOrderFormUrl = "{{ route('elits-permohonan-uji-klinik-2.tms-order-form', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsStoreOrderUrl = "{{ route('elits-permohonan-uji-klinik-2.store-tms-order', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsListOrdersUrl = "{{ route('elits-permohonan-uji-klinik-2.list-tms-orders', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsMassCandidatesUrl = "{{ route('elits-permohonan-uji-klinik-2.tms-mass-order-candidates', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsMassStoreUrl = "{{ route('elits-permohonan-uji-klinik-2.store-tms-mass-order', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsMassJamQuery = @json(request()->query('jam'));
            var tmsSyncOrderUrlTpl = "{{ url('elits-permohonan-uji-klinik-2/sync-tms-order') }}/";
            var tmsUpdateOrderUrlTpl = "{{ url('elits-permohonan-uji-klinik-2/update-tms-order') }}/";
            var tmsDeleteOrderUrlTpl = "{{ url('elits-permohonan-uji-klinik-2/delete-tms-order') }}/";
            var tmsCsrf = $('#csrf-token').val() || '{{ csrf_token() }}';

            $(document).on('input', '#tms-order-barcode, #tms-mqtt-order-barcode', function() {
                var digits = String($(this).val() || '').replace(/\D+/g, '').slice(0, 10);
                if ($(this).val() !== digits) {
                    $(this).val(digits);
                }
            });

            function cleanTmsResultValue(val) {
                // Buang padding '^' dari alat TMS (mis. 82^^^^^^^^^ → 82)
                return String(val == null ? '' : val).replace(/[\^\uFF3E\u02C6]+$/g, '').trim();
            }

            function tmsOrderGroupKey(o) {
                return [
                    String(o.kode_barcode || '').trim(),
                    String(o.tray == null ? '' : o.tray).trim(),
                    String((o.pos == null ? o.posisi : o.pos) == null ? '' : (o.pos == null ? o.posisi : o.pos)).trim(),
                    String(o.jenis_spesimen || o.jenis_sampel || '').trim()
                ].join('\u0001');
            }

            function mergeTmsOrdersForDisplay(orders) {
                orders = orders || [];
                var groupMap = {};
                var groupOrder = [];
                orders.forEach(function(o) {
                    var key = tmsOrderGroupKey(o);
                    if (!groupMap[key]) {
                        groupMap[key] = [];
                        groupOrder.push(key);
                    }
                    groupMap[key].push(o);
                });

                var merged = [];
                groupOrder.forEach(function(key) {
                    var list = groupMap[key];
                    list.sort(function(a, b) {
                        return String(a.created_at || '').localeCompare(String(b.created_at || ''));
                    });
                    if (list.length === 1) {
                        merged.push(list[0]);
                        return;
                    }

                    var primary = list[0];
                    var execSeq = {};
                    var execList = list.filter(function(o) { return o.executed_at; }).sort(function(a, b) {
                        return String(a.executed_at).localeCompare(String(b.executed_at));
                    });
                    execList.forEach(function(o, idx) {
                        execSeq[o.id_order_tms] = idx + 1;
                    });

                    var valueByKey = {};
                    list.forEach(function(o) {
                        var execNo = execSeq[o.id_order_tms] || null;
                        (o.details || []).forEach(function(d) {
                            var val = cleanTmsResultValue(d.value);
                            if (val === '') {
                                return;
                            }
                            var dkey = d.id_permohonan_uji_parameter_klinik
                                ? ('puk:' + d.id_permohonan_uji_parameter_klinik)
                                : ('tms:' + d.id_parameter_tms);
                            if (!valueByKey[dkey]) {
                                valueByKey[dkey] = { value: val, exec_no: execNo };
                            }
                        });
                    });

                    var detailKeys = {};
                    var mergedDetails = [];
                    function pushDetail(d) {
                        var dkey = d.id_permohonan_uji_parameter_klinik
                            ? ('puk:' + d.id_permohonan_uji_parameter_klinik)
                            : ('tms:' + d.id_parameter_tms);
                        if (detailKeys[dkey]) {
                            return;
                        }
                        detailKeys[dkey] = true;
                        var hit = valueByKey[dkey];
                        var val = hit ? hit.value : cleanTmsResultValue(d.value);
                        mergedDetails.push(Object.assign({}, d, {
                            value: val,
                            exec_no: hit ? hit.exec_no : null
                        }));
                    }
                    (primary.details || []).forEach(pushDetail);
                    list.forEach(function(o) {
                        (o.details || []).forEach(pushDetail);
                    });

                    var allFilled = mergedDetails.length > 0 && mergedDetails.every(function(d) {
                        return cleanTmsResultValue(d.value) !== '';
                    });

                    merged.push(Object.assign({}, primary, {
                        details: mergedDetails,
                        is_executed: allFilled,
                        executed_at: execList.length ? execList[execList.length - 1].executed_at : (primary.executed_at || null),
                        created_at: primary.created_at,
                        _merged_from: list.map(function(o) { return o.id_order_tms; }),
                        _merge_count: list.length
                    }));
                });

                merged.sort(function(a, b) {
                    return String(b.created_at || '').localeCompare(String(a.created_at || ''));
                });
                return merged;
            }

            function tmsOrderFillState(o) {
                var details = (o && o.details) ? o.details : [];
                if (!details.length) {
                    return { hasAnyValue: false, allFilled: false };
                }
                var hasAnyValue = false;
                var allFilled = true;
                details.forEach(function(d) {
                    var val = cleanTmsResultValue(d.value);
                    if (val !== '') {
                        hasAnyValue = true;
                    } else {
                        allFilled = false;
                    }
                });
                return { hasAnyValue: hasAnyValue, allFilled: allFilled };
            }

            function tmsOrderStatusBadge(o) {
                var state = tmsOrderFillState(o);
                if (state.allFilled) {
                    return '<span class="badge badge-success">Selesai</span>';
                }
                if (state.hasAnyValue) {
                    return '<span class="badge badge-info">Hampir selesai</span>';
                }
                return '<span class="badge badge-warning text-dark">Belum tereksekusi</span>';
            }

            function formatTmsDetailValueCell(d) {
                var val = cleanTmsResultValue(d.value);
                if (val === '') {
                    return '<span class="text-muted">-</span>';
                }
                var html = escapeHtmlTms(val);
                if (d.exec_no) {
                    html += ' <small class="text-muted">(Ek. ' + d.exec_no + ')</small>';
                }
                return html;
            }

            function tmsOrderFooterMeta(o) {
                var html = 'Dibuat: ' + escapeHtmlTms(o.created_at || '-');
                if (o._merge_count > 1) {
                    html += ' | ' + o._merge_count + ' order digabung (hasil terlambat)';
                }
                if (o.executed_at) {
                    html += ' | Dieksekusi: ' + escapeHtmlTms(o.executed_at);
                }
                return html;
            }

            function getTmsRoundSelects() {
                if ($('#modalAmbilTmsMqtt').hasClass('show') || $('#modalAmbilTmsMqtt').is(':visible')) {
                    return {
                        mode: $('#tms-mqtt-round-mode'),
                        decimals: $('#tms-mqtt-round-decimals')
                    };
                }
                return {
                    mode: $('#tms-round-mode'),
                    decimals: $('#tms-round-decimals')
                };
            }

            function getTmsRoundOptions() {
                var $el = getTmsRoundSelects();
                var mode = String($el.mode.val() || 'none');
                var decimals = parseInt($el.decimals.val(), 10);
                if (isNaN(decimals) || decimals < 0) {
                    decimals = 2;
                }
                if (decimals > 6) {
                    decimals = 6;
                }
                return { mode: mode, decimals: decimals };
            }

            function syncTmsRoundOptionsUi() {
                var modeHttp = String($('#tms-round-mode').val() || 'none');
                $('#tms-round-decimals').prop('disabled', modeHttp === 'none');
                var modeMqtt = String($('#tms-mqtt-round-mode').val() || 'none');
                $('#tms-mqtt-round-decimals').prop('disabled', modeMqtt === 'none');
            }

            function loadTmsRoundOptionsFromStorage() {
                try {
                    var mode = localStorage.getItem('tms_round_mode');
                    var decimals = localStorage.getItem('tms_round_decimals');
                    ['#tms-round-mode', '#tms-mqtt-round-mode'].forEach(function(sel) {
                        if (mode && $(sel + ' option[value="' + mode + '"]').length) {
                            $(sel).val(mode);
                        }
                    });
                    ['#tms-round-decimals', '#tms-mqtt-round-decimals'].forEach(function(sel) {
                        if (decimals != null && $(sel + ' option[value="' + decimals + '"]').length) {
                            $(sel).val(String(decimals));
                        }
                    });
                } catch (e) {}
                syncTmsRoundOptionsUi();
            }

            function saveTmsRoundOptionsToStorage() {
                try {
                    var $el = getTmsRoundSelects();
                    localStorage.setItem('tms_round_mode', String($el.mode.val() || 'none'));
                    localStorage.setItem('tms_round_decimals', String($el.decimals.val() || '2'));
                    $('#tms-round-mode, #tms-mqtt-round-mode').val($el.mode.val() || 'none');
                    $('#tms-round-decimals, #tms-mqtt-round-decimals').val($el.decimals.val() || '2');
                } catch (e) {}
            }

            /**
             * Terapkan pembulatan ke nilai numerik TMS sebelum diisi ke form.
             * mode: none | round | up | down
             */
            function formatTmsValueForForm(val) {
                var cleaned = cleanTmsResultValue(val);
                if (cleaned === '') {
                    return '';
                }

                var opts = getTmsRoundOptions();
                if (opts.mode === 'none') {
                    return cleaned;
                }

                // Ambil angka pertama; biarkan teks non-numerik apa adanya
                var normalized = String(cleaned).replace(',', '.');
                var match = normalized.match(/-?\d+(?:\.\d+)?/);
                if (!match) {
                    return cleaned;
                }

                var num = parseFloat(match[0]);
                if (isNaN(num)) {
                    return cleaned;
                }

                var decimals = opts.decimals;
                var factor = Math.pow(10, decimals);
                var scaled = num * factor;
                var roundedScaled;
                // epsilon kecil agar kasus 1.10 * 100 tidak jadi 109.999...
                if (opts.mode === 'up') {
                    roundedScaled = Math.ceil(scaled - 1e-9);
                } else if (opts.mode === 'down') {
                    roundedScaled = Math.floor(scaled + 1e-9);
                } else {
                    // round biasa
                    roundedScaled = Math.round(scaled);
                }

                var rounded = roundedScaled / factor;
                return rounded.toFixed(decimals);
            }

            function escapeHtmlTms(text) {
                return $('<div>').text(text == null ? '' : text).html();
            }

            function riwayatHasFillableValues() {
                return $('#tms-riwayat-body tr[data-order-detail="1"]').filter(function() {
                    return (($(this).attr('data-value') || '') !== '') &&
                        (($(this).attr('data-puk') || $(this).attr('data-tms-id') || '') !== '');
                }).length > 0;
            }

            function clearTmsEditMode() {
                tmsEditingOrderId = null;
                tmsEditingOrder = null;
                $('#tms-order-existing-info').hide().empty();
                refreshTmsOrderSubmitButton();
            }

            function refreshTmsOrderSubmitButton() {
                if (tmsEditingOrderId) {
                    $('#btn-buat-order-tms').html('<i class="fa fa-save mr-1"></i> Simpan Perubahan');
                } else {
                    $('#btn-buat-order-tms').html('<i class="fa fa-paper-plane mr-1"></i> Buat Order');
                }
            }

            function updateTmsFooterButtons() {
                var tab = $('#tmsTabs .nav-link.active').attr('href') || '#tmsPaneHasil';
                if (tab === '#tmsPaneOrder') {
                    $('#btn-buat-order-tms').removeClass('d-none');
                    $('#btn-isi-tms').addClass('d-none');
                    $('#tms-round-options').addClass('d-none');
                    refreshTmsOrderSubmitButton();
                } else if (tab === '#tmsPaneRiwayat') {
                    var canFill = (tmsMatchedCache && tmsMatchedCache.length) || riwayatHasFillableValues();
                    $('#btn-buat-order-tms').addClass('d-none');
                    $('#btn-isi-tms').toggleClass('d-none', !canFill).prop('disabled', !canFill);
                    $('#tms-round-options').removeClass('d-none');
                } else {
                    $('#btn-buat-order-tms').addClass('d-none');
                    $('#btn-isi-tms').removeClass('d-none');
                    $('#tms-round-options').removeClass('d-none');
                }
            }

            function renderSuggestedSampleIds(ids) {
                if (!ids || !ids.length) {
                    return;
                }
                var html = '<div class="alert alert-warning mb-0 py-2">Sample ID terbaru di alat: ';
                html += ids.slice(0, 8).map(function(id) {
                    return '<button type="button" class="btn btn-link btn-sm p-0 align-baseline tms-suggested-sample" data-sample="' +
                        escapeHtmlTms(id) + '"><code>' + escapeHtmlTms(id) + '</code></button>';
                }).join(', ');
                html += '<br><small>Klik Sample ID, lalu <strong>Isi ke Form</strong> untuk mengisi kolom hasil.</small></div>';
                $('#tms-result-info').html(html).show();
            }

            function renderTmsMatched(rows) {
                if (!rows || !rows.length) {
                    $('#tms-result-body').html('<tr><td colspan="3" class="text-center text-muted">Tidak ada parameter yang cocok</td></tr>');
                    $('#btn-isi-tms').prop('disabled', true);
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    row.result_value = cleanTmsResultValue(row.result_value);
                    html += '<tr>';
                    html += '<td>' + escapeHtmlTms(row.nama_parameter_satuan_klinik) + '</td>';
                    html += '<td>' + escapeHtmlTms(row.tms_parameter_name) + ' <small class="text-muted">(ID ' + row.tms_parameter_id + ')</small></td>';
                    html += '<td class="font-weight-bold">' + escapeHtmlTms(row.result_value) + '</td>';
                    html += '</tr>';
                });
                $('#tms-result-body').html(html);
                $('#btn-isi-tms').prop('disabled', false);
            }

            function fetchTmsResults() {
                var sampleId = $.trim($('#tms-sample-id').val() || '');
                $('#tms-result-info').hide().empty();
                $('#tms-result-body').html('<tr><td colspan="3" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Mencari data...</td></tr>');
                $('#btn-isi-tms').prop('disabled', true);
                tmsMatchedCache = [];

                $.getJSON(tmsFetchUrl, { sample_id: sampleId })
                    .done(function(res) {
                        if (!res || !res.status) {
                            var msg = (res && res.pesan) ? res.pesan : 'Data tidak ditemukan';
                            $('#tms-result-body').html('<tr><td colspan="3" class="text-center text-danger">' + escapeHtmlTms(msg) + '</td></tr>');
                            renderSuggestedSampleIds(res && res.suggested_sample_ids);
                            return;
                        }

                        if (res.sample_id) {
                            $('#tms-sample-id').val(res.sample_id);
                        }
                        var info = '<div class="alert alert-success mb-0 py-2">';
                        info += '<strong>' + escapeHtmlTms(res.pesan) + '</strong>';
                        if (res.patient_name) info += ' — Pasien: ' + escapeHtmlTms(res.patient_name);
                        if (res.result_date) info += ' — Tgl: ' + escapeHtmlTms(res.result_date);
                        info += '</div>';
                        $('#tms-result-info').html(info).show();

                        tmsMatchedCache = res.matched || [];
                        renderTmsMatched(tmsMatchedCache);

                        if ((!tmsMatchedCache.length) && res.unmatched && res.unmatched.length) {
                            var extra = '<div class="alert alert-info mt-2 mb-0 py-2 small">Ada hasil TMS yang belum terhubung ke parameter permohonan: ';
                            extra += res.unmatched.map(function(u) {
                                return escapeHtmlTms(u.tms_parameter_name) + '=' + escapeHtmlTms(u.result_value);
                            }).join(', ');
                            extra += '</div>';
                            $('#tms-result-info').append(extra);
                        }
                        if ((!tmsMatchedCache.length) && res.suggested_sample_ids && res.suggested_sample_ids.length) {
                            renderSuggestedSampleIds(res.suggested_sample_ids);
                        }
                        updateTmsFooterButtons();
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal mengambil data TMS';
                        $('#tms-result-body').html('<tr><td colspan="3" class="text-center text-danger">' + escapeHtmlTms(msg) + '</td></tr>');
                        renderSuggestedSampleIds(xhr.responseJSON && xhr.responseJSON.suggested_sample_ids);
                    });
            }

            function renderTmsOrderParams(params) {
                tmsOrderParamsCache = params || [];
                if (!tmsOrderParamsCache.length) {
                    $('#tms-order-param-list').html('<div class="text-muted text-center py-3">Tidak ada parameter permohonan yang terhubung ke TMS</div>');
                    return;
                }

                var groups = {};
                var orderKeys = ['Darah', 'Blood Cell', 'Serum', 'Plasma', 'Plasma NaF', 'Urine', 'Feses', 'Swab', 'Lainnya'];
                tmsOrderParamsCache.forEach(function(p) {
                    var jenis = (p.jenis_spesimen || 'Lainnya').trim() || 'Lainnya';
                    if (!groups[jenis]) groups[jenis] = [];
                    groups[jenis].push(p);
                });

                function slugJenis(jenis) {
                    return String(jenis).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'lainnya';
                }

                function renderGroup(jenis, list) {
                    var slug = slugJenis(jenis);
                    var hasChecked = list.some(function(p) { return p.selected_default === true; });
                    var html = '';
                    html += '<div class="mb-3 tms-jenis-group border rounded p-2" data-jenis="' + escapeHtmlTms(jenis) + '">';
                    html += '<div class="d-flex flex-wrap align-items-center justify-content-between mb-2">';
                    html += '<div class="mb-1 mb-md-0">';
                    html += '<button type="button" class="btn btn-sm badge badge-info mr-2 tms-jenis-toggle" data-jenis="' + escapeHtmlTms(jenis) + '" title="Klik untuk centang/kosongkan semua parameter jenis ini" style="border:0; cursor:pointer;">' + escapeHtmlTms(jenis) + '</button>';
                    html += '<small class="text-muted">' + list.length + ' parameter — 1 order · klik jenis untuk pilih semua</small>';
                    html += '</div>';
                    html += '<div class="d-flex align-items-center tms-tray-pos-wrap" style="' + (hasChecked ? '' : 'opacity:0.45;') + '">';
                    html += '<div class="mr-2" style="min-width:72px;">';
                    html += '<label class="mb-0 small">Tray <span class="text-danger">*</span></label>';
                    html += '<input type="text" class="form-control form-control-sm tms-order-tray-jenis" data-jenis="' + escapeHtmlTms(jenis) + '" id="tms-tray-' + slug + '" placeholder="1" autocomplete="off"' + (hasChecked ? '' : ' disabled') + '>';
                    html += '</div>';
                    html += '<div style="min-width:72px;">';
                    html += '<label class="mb-0 small">Posisi <span class="text-danger">*</span></label>';
                    html += '<input type="text" class="form-control form-control-sm tms-order-pos-jenis" data-jenis="' + escapeHtmlTms(jenis) + '" id="tms-pos-' + slug + '" placeholder="1" autocomplete="off"' + (hasChecked ? '' : ' disabled') + '>';
                    html += '</div>';
                    html += '</div></div>';
                    html += '<div class="row no-gutters">';
                    list.forEach(function(p) {
                        var checked = (p.selected_default === true) ? ' checked' : '';
                        var klinikName = p.nama_parameter_klinik || '';
                        var pukId = p.id_permohonan_uji_parameter_klinik || '';
                        var checkId = 'tms_param_' + p.id_parameter_tms + '_' + String(pukId).replace(/[^a-zA-Z0-9_-]/g, '_');
                        html += '<div class="col-md-6 mb-1">';
                        html += '<div class="custom-control custom-checkbox">';
                        html += '<input type="checkbox" class="custom-control-input tms-param-check" id="' + checkId + '" value="' + escapeHtmlTms(pukId) + '" data-tms-id="' + p.id_parameter_tms + '" data-jenis="' + escapeHtmlTms(jenis) + '"' + checked + '>';
                        html += '<label class="custom-control-label" for="' + checkId + '">';
                        html += '<strong>' + escapeHtmlTms(p.name_parameter_tms) + '</strong>';
                        html += ' <small class="text-muted">(ID ' + p.id_parameter_tms + ')</small>';
                        if (klinikName && klinikName !== p.name_parameter_tms) {
                            html += '<br><small class="text-muted">' + escapeHtmlTms(klinikName) + '</small>';
                        }
                        html += '</label></div></div>';
                    });
                    html += '</div></div>';
                    return html;
                }

                var html = '';
                var seen = {};
                orderKeys.forEach(function(jenis) {
                    if (!groups[jenis] || !groups[jenis].length) return;
                    seen[jenis] = true;
                    html += renderGroup(jenis, groups[jenis]);
                });
                Object.keys(groups).forEach(function(jenis) {
                    if (seen[jenis]) return;
                    html += renderGroup(jenis, groups[jenis]);
                });
                $('#tms-order-param-list').html(html);
                syncTmsTrayPosBySelection();
            }

            function syncTmsTrayPosBySelection() {
                $('.tms-jenis-group').each(function() {
                    var $group = $(this);
                    var jenis = $group.data('jenis');
                    var hasChecked = $group.find('.tms-param-check:checked').length > 0;
                    var $wrap = $group.find('.tms-tray-pos-wrap');
                    var $inputs = $group.find('.tms-order-tray-jenis, .tms-order-pos-jenis');
                    $inputs.prop('disabled', !hasChecked);
                    $wrap.css('opacity', hasChecked ? '1' : '0.45');
                    if (!hasChecked) {
                        // biarkan nilai tetap jika user uncheck sementara, tapi jangan wajibkan
                    }
                });
            }

            function getSelectedTmsJenisList() {
                var jenisMap = {};
                $('.tms-param-check:checked').each(function() {
                    var jenis = (($(this).attr('data-jenis') || $(this).data('jenis') || 'Lainnya') + '').trim() || 'Lainnya';
                    jenisMap[jenis] = true;
                });
                return Object.keys(jenisMap);
            }

            function getTrayPosByJenis() {
                var trays = {};
                var positions = {};
                $('.tms-order-tray-jenis').each(function() {
                    var jenis = (($(this).attr('data-jenis') || $(this).data('jenis') || '') + '').trim();
                    if (!jenis) return;
                    trays[jenis] = $.trim($(this).val() || '');
                });
                $('.tms-order-pos-jenis').each(function() {
                    var jenis = (($(this).attr('data-jenis') || $(this).data('jenis') || '') + '').trim();
                    if (!jenis) return;
                    positions[jenis] = $.trim($(this).val() || '');
                });
                return { trays: trays, positions: positions };
            }

            function applyTmsEditPrefill(order) {
                if (!order) return;
                var jenisOrder = ((order.jenis_spesimen || order.jenis_sampel || 'Lainnya') + '').trim() || 'Lainnya';
                var selectedPuk = {};
                var selectedTms = {};
                (order.details || []).forEach(function(d) {
                    if (d.id_permohonan_uji_parameter_klinik) {
                        selectedPuk[String(d.id_permohonan_uji_parameter_klinik)] = true;
                    }
                    if (d.id_parameter_tms) {
                        selectedTms[String(d.id_parameter_tms)] = true;
                    }
                });

                $('.tms-param-check').prop('checked', false);
                $('.tms-jenis-group').each(function() {
                    var $group = $(this);
                    var jenis = (($group.attr('data-jenis') || $group.data('jenis') || '') + '').trim();
                    var sameJenis = jenis === jenisOrder;
                    $group.toggle(sameJenis);
                    if (!sameJenis) return;
                    $group.find('.tms-param-check').each(function() {
                        var puk = String($(this).val() || '');
                        var tmsId = String($(this).attr('data-tms-id') || $(this).data('tmsId') || '');
                        var on = (puk && selectedPuk[puk]) || (tmsId && selectedTms[tmsId]);
                        $(this).prop('checked', !!on);
                    });
                    $group.find('.tms-order-tray-jenis').val(order.tray || '');
                    $group.find('.tms-order-pos-jenis').val(order.pos || '');
                });

                if (order.kode_barcode) {
                    $('#tms-order-barcode').val(String(order.kode_barcode));
                }

                var info = 'Mengedit order <strong>' + escapeHtmlTms(jenisOrder) + '</strong>';
                info += ' (Tray ' + escapeHtmlTms(order.tray || '-') + ' / Pos ' + escapeHtmlTms(order.pos || '-') + ').';
                info += ' Parameter harus tetap satu jenis sampel.';
                info += ' <button type="button" class="btn btn-link btn-sm p-0 align-baseline" id="btn-batal-edit-tms-order">Batal edit</button>';
                $('#tms-order-existing-info').html(info).show();
                syncTmsTrayPosBySelection();
                refreshTmsOrderSubmitButton();
            }

            function loadTmsOrderForm(editOrder) {
                $('#tms-order-info').hide().empty();
                if (!editOrder) {
                    clearTmsEditMode();
                }
                $('#tms-order-param-list').html('<div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat parameter...</div>');
                $.getJSON(tmsOrderFormUrl)
                    .done(function(res) {
                        if (!res || !res.status) {
                            $('#tms-order-param-list').html('<div class="text-danger text-center py-3">' + escapeHtmlTms((res && res.pesan) || 'Gagal memuat') + '</div>');
                            return;
                        }
                        $('#tms-order-nama').val((res.pasien && res.pasien.nama_pasien) || '');
                        $('#tms-order-dob').val((res.pasien && res.pasien.tanggal_lahir) || '');
                        $('#tms-order-jk').val((res.pasien && res.pasien.jenis_kelamin) || '');
                        $('#tms-order-barcode').val(res.kode_barcode || '');
                        if (editOrder) {
                            tmsEditingOrderId = editOrder.id_order_tms;
                            tmsEditingOrder = editOrder;
                            // selected_default false supaya prefill yang menentukan checklist
                            var params = (res.parameters || []).map(function(p) {
                                var copy = $.extend({}, p);
                                copy.selected_default = false;
                                return copy;
                            });
                            renderTmsOrderParams(params);
                            applyTmsEditPrefill(editOrder);
                        } else {
                            if (res.existing_orders_count > 0) {
                                $('#tms-order-existing-info').html(
                                    'Sudah ada <strong>' + res.existing_orders_count + '</strong> order TMS untuk pengujian ini. Anda bisa membuat order tambahan.'
                                ).show();
                            }
                            renderTmsOrderParams(res.parameters || []);
                            refreshTmsOrderSubmitButton();
                        }
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal memuat form order';
                        $('#tms-order-param-list').html('<div class="text-danger text-center py-3">' + escapeHtmlTms(msg) + '</div>');
                    });
            }

            function getSelectedTmsParamIds() {
                var ids = [];
                var pukIds = [];
                $('.tms-param-check:checked').each(function() {
                    var tmsId = parseInt($(this).attr('data-tms-id') || $(this).data('tmsId') || 0, 10);
                    var pukId = $.trim($(this).val() || '');
                    if (tmsId > 0) {
                        ids.push(tmsId);
                    }
                    if (pukId !== '') {
                        pukIds.push(pukId);
                    }
                });
                return {
                    tmsIds: ids.filter(function(v, i, a) { return a.indexOf(v) === i; }),
                    pukIds: pukIds
                };
            }

            function submitTmsOrder() {
                var selected = getSelectedTmsParamIds();
                var ids = selected.tmsIds;
                var pukIds = selected.pukIds;
                if (!ids.length && !pukIds.length) {
                    alert('Pilih minimal satu parameter TMS.');
                    return;
                }

                var selectedJenis = getSelectedTmsJenisList();
                if (tmsEditingOrderId && selectedJenis.length !== 1) {
                    alert('Saat mengedit order, pilih parameter dari satu jenis sampel saja.');
                    return;
                }

                var trayPos = getTrayPosByJenis();
                var missingJenis = null;
                var missingField = null;
                for (var i = 0; i < selectedJenis.length; i++) {
                    var jenis = selectedJenis[i];
                    if (!trayPos.trays[jenis]) {
                        missingJenis = jenis;
                        missingField = 'tray';
                        break;
                    }
                    if (!trayPos.positions[jenis]) {
                        missingJenis = jenis;
                        missingField = 'pos';
                        break;
                    }
                }
                if (missingJenis) {
                    alert('Isi Tray dan Posisi untuk jenis sampel "' + missingJenis + '" (satu jenis sampel = satu order).');
                    var $focus = missingField === 'tray'
                        ? $('.tms-order-tray-jenis').filter(function() { return (($(this).attr('data-jenis') || '') === missingJenis); }).first()
                        : $('.tms-order-pos-jenis').filter(function() { return (($(this).attr('data-jenis') || '') === missingJenis); }).first();
                    $focus.prop('disabled', false).focus();
                    return;
                }

                var payload = {
                    _token: tmsCsrf,
                    nama_pasien: $('#tms-order-nama').val(),
                    tanggal_lahir: $('#tms-order-dob').val(),
                    jenis_kelamin: $('#tms-order-jk').val(),
                    kode_barcode: $.trim($('#tms-order-barcode').val() || ''),
                    trays: trayPos.trays,
                    positions: trayPos.positions,
                    // fallback kompatibilitas (order tunggal)
                    tray: selectedJenis.length === 1 ? trayPos.trays[selectedJenis[0]] : '',
                    pos: selectedJenis.length === 1 ? trayPos.positions[selectedJenis[0]] : '',
                    posisi: selectedJenis.length === 1 ? trayPos.positions[selectedJenis[0]] : '',
                    parameter_ids: ids,
                    parameter_puk_ids: pukIds
                };
                var isEdit = !!tmsEditingOrderId;
                var url = isEdit ? (tmsUpdateOrderUrlTpl + tmsEditingOrderId) : tmsStoreOrderUrl;
                var busyLabel = isEdit
                    ? '<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...'
                    : '<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...';
                $('#btn-buat-order-tms').prop('disabled', true).html(busyLabel);
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': tmsCsrf
                    }
                }).done(function(res) {
                    if (res && res.status) {
                        $('#tms-order-info').html('<div class="alert alert-success mb-0 py-2">' + escapeHtmlTms(res.pesan) + '</div>').show();
                        if (typeof swal === 'function') {
                            swal({ icon: 'success', title: 'Berhasil', text: res.pesan });
                        }
                        clearTmsEditMode();
                        $('#tms-tab-riwayat').tab('show');
                        loadTmsOrders();
                    } else {
                        $('#tms-order-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms((res && res.pesan) || 'Gagal menyimpan order') + '</div>').show();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal menyimpan order TMS';
                    $('#tms-order-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms(msg) + '</div>').show();
                }).always(function() {
                    $('#btn-buat-order-tms').prop('disabled', false);
                    refreshTmsOrderSubmitButton();
                });
            }

            function renderTmsOrders(orders) {
                tmsOrdersCache = orders || [];
                orders = mergeTmsOrdersForDisplay(orders);
                if (!orders || !orders.length) {
                    $('#tms-riwayat-body').html('<div class="text-muted text-center py-3">Belum ada order TMS untuk permohonan ini</div>');
                    return;
                }
                var html = '';
                orders.forEach(function(o) {
                    var statusBadge = tmsOrderStatusBadge(o);
                    html += '<div class="card mb-2 shadow-sm">';
                    html += '<div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size:12px;">';
                    html += '<div>';
                    html += statusBadge + ' ';
                    if (o.jenis_spesimen) {
                        html += '<span class="badge badge-info mr-1">' + escapeHtmlTms(o.jenis_spesimen) + '</span> ';
                    }
                    html += '<strong>Barcode:</strong> ' + escapeHtmlTms(o.kode_barcode || '-') + ' ';
                    html += '| <strong>Tray:</strong> ' + escapeHtmlTms(o.tray || '-') + ' ';
                    html += '| <strong>Pos:</strong> ' + escapeHtmlTms(o.pos || '-');
                    html += '</div>';
                    html += '<div class="mt-1 mt-md-0">';
                    html += '<button type="button" class="btn btn-xs btn-outline-secondary btn-edit-tms-order mr-1" data-id="' + o.id_order_tms + '"><i class="fa fa-pencil"></i> Edit</button>';
                    html += '<button type="button" class="btn btn-xs btn-outline-danger btn-delete-tms-order mr-1" data-id="' + o.id_order_tms + '"><i class="fa fa-trash"></i> Hapus</button>';
                    html += '<button type="button" class="btn btn-xs btn-outline-primary btn-sync-tms-order mr-1" data-id="' + o.id_order_tms + '" data-barcode="' + escapeHtmlTms(o.kode_barcode || '') + '"><i class="fa fa-refresh"></i> Sync Hasil</button>';
                    html += '<button type="button" class="btn btn-xs btn-success btn-isi-order-tms" data-id="' + o.id_order_tms + '"><i class="fa fa-download"></i> Isi ke Form</button>';
                    html += '</div></div>';
                    html += '<div class="card-body py-2 px-2">';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size:12px;">';
                    html += '<thead class="thead-light"><tr><th>Parameter</th><th>ID</th><th>Value</th></tr></thead><tbody>';
                    (o.details || []).forEach(function(d) {
                        d.value = cleanTmsResultValue(d.value);
                        html += '<tr data-order-detail="1"';
                        html += ' data-puk="' + escapeHtmlTms(d.id_permohonan_uji_parameter_klinik || '') + '"';
                        html += ' data-value="' + escapeHtmlTms(d.value || '') + '"';
                        html += ' data-tms-id="' + d.id_parameter_tms + '"';
                        html += ' data-tms-name="' + escapeHtmlTms(d.name_parameter_tms || '') + '">';
                        html += '<td>' + escapeHtmlTms(d.name_parameter_tms || '-') + '</td>';
                        html += '<td>' + d.id_parameter_tms + '</td>';
                        html += '<td class="font-weight-bold">' + formatTmsDetailValueCell(d) + '</td>';
                        html += '</tr>';
                    });
                    if (!(o.details || []).length) {
                        html += '<tr><td colspan="3" class="text-muted text-center">Tidak ada detail</td></tr>';
                    }
                    html += '</tbody></table></div>';
                    html += '<small class="text-muted">' + tmsOrderFooterMeta(o) + '</small>';
                    html += '</div></div>';
                });
                $('#tms-riwayat-body').html(html);
                updateTmsFooterButtons();
            }

            function loadTmsOrders() {
                $('#tms-riwayat-body').html('<div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat riwayat...</div>');
                $.getJSON(tmsListOrdersUrl)
                    .done(function(res) {
                        if (!res || !res.status) {
                            $('#tms-riwayat-body').html('<div class="text-danger text-center py-3">' + escapeHtmlTms((res && res.pesan) || 'Gagal memuat') + '</div>');
                            return;
                        }
                        renderTmsOrders(res.orders || []);
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal memuat riwayat order';
                        $('#tms-riwayat-body').html('<div class="text-danger text-center py-3">' + escapeHtmlTms(msg) + '</div>');
                    });
            }

            function findHasilTextareaForTmsRow(row) {
                var paramId = row.id_permohonan_uji_parameter_klinik;
                var $textarea = $();
                if (paramId) {
                    $textarea = $('textarea[name="hasil_permohonan_uji_parameter_klinik[' + paramId + ']"]');
                    if (!$textarea.length) {
                        $textarea = $('textarea[name*="hasil_permohonan_uji_parameter_klinik"]').filter(function() {
                            return (($(this).attr('name') || '').indexOf('[' + paramId + ']') !== -1);
                        });
                    }
                }
                if (!$textarea.length && row.tms_parameter_id) {
                    $textarea = $('textarea[data-parameter-tms-id="' + row.tms_parameter_id + '"]').first();
                }
                if (!$textarea.length && (row.nama_parameter_satuan_klinik || row.tms_parameter_name)) {
                    var want = String(row.nama_parameter_satuan_klinik || row.tms_parameter_name).toLowerCase().trim();
                    $textarea = $('textarea[name*="hasil_permohonan_uji_parameter_klinik"]').filter(function() {
                        var n = String($(this).attr('data-name') || '').toLowerCase().trim();
                        return n && (n === want || n.indexOf(want) !== -1 || want.indexOf(n) !== -1);
                    }).first();
                }
                return $textarea;
            }

            function applyTmsToForm(rows) {
                var filled = 0;
                (rows || []).forEach(function(row) {
                    var value = formatTmsValueForForm(row.result_value);
                    if (value === '') return;

                    var $textarea = findHasilTextareaForTmsRow(row);
                    if (!$textarea || !$textarea.length) return;

                    $textarea.val(value);

                    var textareaId = $textarea.attr('id') || '';
                    var $editor = $('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                    if ($editor.length) {
                        $editor.html(escapeHtmlTms(value)).removeClass('empty');
                        var edId = $editor.attr('id');
                        if (typeof tinymce !== 'undefined' && edId && tinymce.get(edId)) {
                            try { tinymce.get(edId).setContent(value); } catch (e) {}
                        }
                    }

                    var $select = $('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                    if ($select.length) {
                        $select.val(value).trigger('change');
                    }

                    var m = textareaId.match(/hasil_permohonan_uji_parameter_klinik_(\d+)/);
                    if (m && typeof updateResultPreview === 'function') {
                        updateResultPreview(textareaId, 'param_' + m[1]);
                    } else if (m) {
                        var $display = $('#result_display_param_' + m[1]);
                        if ($display.length) {
                            $display.html(escapeHtmlTms(value)).removeClass('empty');
                        }
                        var $badge = $('#badge_' + m[1]);
                        if ($badge.length) {
                            $badge.html(escapeHtmlTms(value));
                        }
                    }

                    // Pastikan handler eGFR / preview lain ikut jalan
                    $textarea.trigger('input').trigger('change');

                    filled++;
                });

                // Cadangan: hitung eGFR dari Kreatinin yang sudah terisi di form
                if (typeof recalculateEgfrFromForm === 'function') {
                    recalculateEgfrFromForm();
                }

                return filled;
            }

            function rowsFromOrderCard($card) {
                var rows = [];
                $card.find('tr[data-order-detail="1"]').each(function() {
                    var puk = $(this).attr('data-puk');
                    var val = $(this).attr('data-value');
                    if (!puk || val === '' || val == null) return;
                    rows.push({
                        id_permohonan_uji_parameter_klinik: puk,
                        result_value: val,
                        tms_parameter_id: $(this).attr('data-tms-id'),
                        tms_parameter_name: $(this).attr('data-tms-name'),
                        nama_parameter_satuan_klinik: $(this).attr('data-tms-name')
                    });
                });
                return rows;
            }

            $('#btn-ambil-tms').on('click', function() {
                loadTmsRoundOptionsFromStorage();
                $('#modalAmbilTms').modal('show');
                $('#tms-tab-hasil').tab('show');
                updateTmsFooterButtons();
                fetchTmsResults();
            });

            $('#tms-round-mode, #tms-round-decimals, #tms-mqtt-round-mode, #tms-mqtt-round-decimals').on('change', function() {
                syncTmsRoundOptionsUi();
                saveTmsRoundOptionsToStorage();
            });
            loadTmsRoundOptionsFromStorage();

            $('#tmsTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                updateTmsFooterButtons();
                var href = $(e.target).attr('href');
                if (href === '#tmsPaneOrder') {
                    // Jangan reset jika sedang masuk mode edit (handler Edit sudah set tmsEditingOrder)
                    loadTmsOrderForm(tmsEditingOrder || null);
                } else if (href === '#tmsPaneRiwayat') {
                    loadTmsOrders();
                }
            });

            $('#btn-cari-tms').on('click', function() {
                fetchTmsResults();
            });

            $('#tms-sample-id').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    fetchTmsResults();
                }
            });

            $('#btn-tms-param-all').on('click', function() {
                $('.tms-param-check:visible').prop('checked', true);
                syncTmsTrayPosBySelection();
            });
            $('#btn-tms-param-none').on('click', function() {
                $('.tms-param-check:visible').prop('checked', false);
                syncTmsTrayPosBySelection();
            });
            $(document).on('change', '.tms-param-check', function() {
                syncTmsTrayPosBySelection();
            });
            $(document).on('click', '.tms-jenis-toggle', function(e) {
                e.preventDefault();
                var $group = $(this).closest('.tms-jenis-group');
                var $checks = $group.find('.tms-param-check');
                if (!$checks.length) return;
                var allChecked = $checks.length === $checks.filter(':checked').length;
                $checks.prop('checked', !allChecked);
                syncTmsTrayPosBySelection();
                if (!allChecked) {
                    $group.find('.tms-order-tray-jenis').prop('disabled', false).focus();
                }
            });

            $('#btn-buat-order-tms').on('click', function() {
                submitTmsOrder();
            });

            $(document).on('click', '#btn-batal-edit-tms-order', function(e) {
                e.preventDefault();
                clearTmsEditMode();
                loadTmsOrderForm();
            });

            $(document).on('click', '.btn-edit-tms-order', function() {
                var id = $(this).data('id');
                var order = null;
                for (var i = 0; i < tmsOrdersCache.length; i++) {
                    if (tmsOrdersCache[i].id_order_tms === id) {
                        order = tmsOrdersCache[i];
                        break;
                    }
                }
                if (!order) {
                    alert('Order tidak ditemukan di riwayat. Muat ulang dulu.');
                    return;
                }
                tmsEditingOrderId = order.id_order_tms;
                tmsEditingOrder = order;
                $('#tms-tab-order').tab('show');
            });

            $(document).on('click', '.btn-delete-tms-order', function() {
                var id = $(this).data('id');
                var $btn = $(this);
                var order = null;
                for (var i = 0; i < tmsOrdersCache.length; i++) {
                    if (tmsOrdersCache[i].id_order_tms === id) {
                        order = tmsOrdersCache[i];
                        break;
                    }
                }
                var label = order
                    ? ((order.jenis_spesimen || order.jenis_sampel || 'Order') + ' / ' + (order.kode_barcode || id))
                    : id;
                var doDelete = function() {
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: tmsDeleteOrderUrlTpl + id,
                        method: 'POST',
                        data: { _token: tmsCsrf },
                        dataType: 'json',
                        headers: { 'X-CSRF-TOKEN': tmsCsrf }
                    }).done(function(res) {
                        if (res && res.status) {
                            if (tmsEditingOrderId === id) {
                                clearTmsEditMode();
                            }
                            if (typeof swal === 'function') {
                                swal({ icon: 'success', title: 'Dihapus', text: res.pesan });
                            } else {
                                alert(res.pesan);
                            }
                            loadTmsOrders();
                        } else {
                            alert((res && res.pesan) || 'Gagal menghapus order');
                        }
                    }).fail(function(xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal menghapus order');
                    }).always(function() {
                        $btn.prop('disabled', false);
                    });
                };

                if (typeof swal === 'function') {
                    swal({
                        title: 'Hapus order?',
                        text: 'Order "' + label + '" akan dihapus dari riwayat TMS.',
                        icon: 'warning',
                        buttons: ['Batal', 'Hapus'],
                        dangerMode: true
                    }).then(function(ok) {
                        if (ok) doDelete();
                    });
                } else if (confirm('Hapus order "' + label + '"?')) {
                    doDelete();
                }
            });

            $(document).on('click', '.btn-sync-tms-order', function() {
                var id = $(this).data('id');
                var barcode = $(this).data('barcode') || '';
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: tmsSyncOrderUrlTpl + id,
                    method: 'POST',
                    data: { _token: tmsCsrf, sample_id: barcode },
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.status) {
                        if (res.matched && res.matched.length) {
                            tmsMatchedCache = res.matched;
                            $('#btn-isi-tms').prop('disabled', false);
                        }
                        if (typeof swal === 'function') {
                            swal({ icon: 'success', title: 'Sync', text: res.pesan });
                        } else {
                            alert(res.pesan);
                        }
                        loadTmsOrders();
                        updateTmsFooterButtons();
                    } else {
                        alert((res && res.pesan) || 'Sync gagal');
                    }
                }).fail(function(xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Sync gagal');
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });

            $(document).on('click', '.btn-isi-order-tms', function() {
                var $card = $(this).closest('.card');
                var rows = rowsFromOrderCard($card);
                if (!rows.length) {
                    alert('Belum ada value pada order ini. Klik Sync Hasil dulu.');
                    return;
                }
                tmsMatchedCache = rows;
                var n = applyTmsToForm(rows);
                if (n > 0) {
                    $('#modalAmbilTms').modal('hide');
                    if (typeof swal === 'function') {
                        swal({ icon: 'success', title: 'Berhasil', text: n + ' parameter diisi dari order TMS. Jangan lupa Simpan Hasil.' });
                    } else {
                        alert(n + ' parameter diisi dari order TMS.');
                    }
                } else {
                    alert('Tidak ada nilai yang berhasil diisi (parameter permohonan belum terhubung).');
                }
            });

            $('#btn-isi-tms').on('click', function() {
                var tab = $('#tmsTabs .nav-link.active').attr('href') || '#tmsPaneHasil';
                var rows = tmsMatchedCache || [];
                if (tab === '#tmsPaneRiwayat') {
                    var fromOrders = [];
                    $('#tms-riwayat-body .card').each(function() {
                        fromOrders = fromOrders.concat(rowsFromOrderCard($(this)));
                    });
                    if (fromOrders.length) {
                        rows = fromOrders;
                    }
                }
                var n = applyTmsToForm(rows);
                if (n > 0) {
                    $('#modalAmbilTms').modal('hide');
                    if (typeof swal === 'function') {
                        swal({ icon: 'success', title: 'Berhasil', text: n + ' parameter diisi dari TMS. Jangan lupa Simpan Hasil.' });
                    } else {
                        alert(n + ' parameter diisi dari TMS. Jangan lupa Simpan Hasil.');
                    }
                } else {
                    alert('Tidak ada nilai yang berhasil diisi ke form. Cari Sample ID dulu atau Sync Hasil pada order.');
                }
            });

            $(document).on('click', '.tms-suggested-sample', function() {
                var sample = $(this).attr('data-sample') || $(this).data('sample') || '';
                if (!sample) return;
                $('#tms-sample-id').val(sample);
                $('#tms-tab-hasil').tab('show');
                fetchTmsResults();
            });

            // ===== TMS MQTT: Make Order (pub) + Riwayat (sub) =====
            var tmsMqttListenUrl = "{{ route('elits-permohonan-uji-klinik-2.tms-mqtt-listen', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}";
            var tmsMqttRepublishUrlTpl = "{{ url('elits-permohonan-uji-klinik-2/republish-tms-mqtt') }}/";
            var tmsMqttResendResultUrlTpl = "{{ url('elits-permohonan-uji-klinik-2/resend-tms-mqtt-result') }}/";
            var tmsMqttOrdersCache = [];
            var tmsMqttMatchedCache = [];
            var tmsMqttEditingOrderId = null;
            var tmsMqttEditingOrder = null;

            function mqttRiwayatHasFillableValues() {
                return $('#tms-mqtt-riwayat-body tr[data-order-detail="1"]').filter(function() {
                    return (($(this).attr('data-value') || '') !== '') &&
                        (($(this).attr('data-puk') || $(this).attr('data-tms-id') || '') !== '');
                }).length > 0;
            }

            function clearTmsMqttEditMode() {
                tmsMqttEditingOrderId = null;
                tmsMqttEditingOrder = null;
                $('#tms-mqtt-order-existing-info').hide().empty();
                refreshTmsMqttSubmitButton();
            }

            function refreshTmsMqttSubmitButton() {
                if (tmsMqttEditingOrderId) {
                    $('#btn-buat-order-tms-mqtt').html('<i class="fa fa-save mr-1"></i> Simpan &amp; Kirim ke Alat');
                } else {
                    $('#btn-buat-order-tms-mqtt').html('<i class="fa fa-paper-plane mr-1"></i> Buat Order');
                }
            }

            function updateTmsMqttFooterButtons() {
                var tab = $('#tmsMqttTabs .nav-link.active').attr('href') || '#tmsMqttPaneHasil';
                if (tab === '#tmsMqttPaneOrder') {
                    $('#btn-buat-order-tms-mqtt').removeClass('d-none');
                    $('#btn-buat-order-tms-massal').addClass('d-none');
                    $('#btn-isi-tms-mqtt').addClass('d-none');
                    $('#tms-mqtt-round-options').addClass('d-none');
                    refreshTmsMqttSubmitButton();
                } else if (tab === '#tmsMqttPaneMassal') {
                    $('#btn-buat-order-tms-mqtt').addClass('d-none');
                    $('#btn-buat-order-tms-massal').removeClass('d-none');
                    $('#btn-isi-tms-mqtt').addClass('d-none');
                    $('#tms-mqtt-round-options').addClass('d-none');
                } else if (tab === '#tmsMqttPaneRiwayat') {
                    var canFill = mqttRiwayatHasFillableValues();
                    $('#btn-buat-order-tms-mqtt').addClass('d-none');
                    $('#btn-buat-order-tms-massal').addClass('d-none');
                    $('#btn-isi-tms-mqtt').toggleClass('d-none', !canFill).prop('disabled', !canFill);
                    $('#tms-mqtt-round-options').removeClass('d-none');
                } else {
                    var canFillHasil = tmsMqttMatchedCache && tmsMqttMatchedCache.length;
                    $('#btn-buat-order-tms-mqtt').addClass('d-none');
                    $('#btn-buat-order-tms-massal').addClass('d-none');
                    $('#btn-isi-tms-mqtt').removeClass('d-none').prop('disabled', !canFillHasil);
                    $('#tms-mqtt-round-options').removeClass('d-none');
                }
            }

            function renderMqttPublishInfo(mqttList) {
                mqttList = mqttList || [];
                if (!mqttList.length) {
                    return '<div class="alert alert-warning mb-0 py-2 mt-2">Order tersimpan, tetapi belum terkirim ke alat.</div>';
                }
                var html = '';
                mqttList.forEach(function(m) {
                    if (m && m.published) {
                        html += '<div class="alert alert-info mb-2 py-2 small">Order terkirim ke alat.</div>';
                    } else {
                        html += '<div class="alert alert-danger mb-2 py-2 small">Gagal mengirim ke alat. Order tetap tersimpan.';
                        html += '<br>' + escapeHtmlTms((m && m.error) || 'unknown');
                        html += '</div>';
                    }
                });
                return html;
            }

            function syncTmsMqttTrayPosBySelection() {
                $('#tms-mqtt-order-param-list .tms-mqtt-jenis-group').each(function() {
                    var $group = $(this);
                    var hasChecked = $group.find('.tms-mqtt-param-check:checked').length > 0;
                    var $wrap = $group.find('.tms-mqtt-tray-pos-wrap');
                    $group.find('.tms-mqtt-order-tray-jenis, .tms-mqtt-order-pos-jenis').prop('disabled', !hasChecked);
                    $wrap.css('opacity', hasChecked ? '1' : '0.45');
                });
            }

            function renderTmsMqttOrderParams(params) {
                if (!params || !params.length) {
                    $('#tms-mqtt-order-param-list').html('<div class="text-muted text-center py-3">Tidak ada parameter permohonan yang terhubung ke TMS</div>');
                    return;
                }
                var groups = {};
                var orderKeys = ['Darah', 'Blood Cell', 'Serum', 'Plasma', 'Plasma NaF', 'Urine', 'Feses', 'Swab', 'Lainnya'];
                params.forEach(function(p) {
                    var jenis = (p.jenis_spesimen || 'Lainnya').trim() || 'Lainnya';
                    if (!groups[jenis]) groups[jenis] = [];
                    groups[jenis].push(p);
                });
                function slugJenis(jenis) {
                    return String(jenis).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'lainnya';
                }
                function renderGroup(jenis, list) {
                    var slug = slugJenis(jenis);
                    var html = '';
                    html += '<div class="mb-3 tms-mqtt-jenis-group border rounded p-2" data-jenis="' + escapeHtmlTms(jenis) + '">';
                    html += '<div class="d-flex flex-wrap align-items-center justify-content-between mb-2">';
                    html += '<div class="mb-1 mb-md-0">';
                    html += '<button type="button" class="btn btn-sm badge badge-info mr-2 tms-mqtt-jenis-toggle" data-jenis="' + escapeHtmlTms(jenis) + '" style="border:0; cursor:pointer;">' + escapeHtmlTms(jenis) + '</button>';
                    html += '<small class="text-muted">' + list.length + ' parameter — 1 order</small>';
                    html += '</div>';
                    html += '<div class="d-flex align-items-center tms-mqtt-tray-pos-wrap" style="opacity:0.45;">';
                    html += '<div class="mr-2" style="min-width:72px;"><label class="mb-0 small">Tray</label>';
                    html += '<input type="text" class="form-control form-control-sm tms-mqtt-order-tray-jenis" data-jenis="' + escapeHtmlTms(jenis) + '" id="tms-mqtt-tray-' + slug + '" placeholder="opsional" disabled></div>';
                    html += '<div style="min-width:72px;"><label class="mb-0 small">Posisi</label>';
                    html += '<input type="text" class="form-control form-control-sm tms-mqtt-order-pos-jenis" data-jenis="' + escapeHtmlTms(jenis) + '" id="tms-mqtt-pos-' + slug + '" placeholder="opsional" disabled></div>';
                    html += '</div></div><div class="row no-gutters">';
                    list.forEach(function(p) {
                        var klinikName = p.nama_parameter_klinik || '';
                        var pukId = p.id_permohonan_uji_parameter_klinik || '';
                        var checkId = 'tms_mqtt_param_' + p.id_parameter_tms + '_' + String(pukId).replace(/[^a-zA-Z0-9_-]/g, '_');
                        html += '<div class="col-md-6 mb-1"><div class="custom-control custom-checkbox">';
                        html += '<input type="checkbox" class="custom-control-input tms-mqtt-param-check" id="' + checkId + '" value="' + escapeHtmlTms(pukId) + '" data-tms-id="' + p.id_parameter_tms + '" data-jenis="' + escapeHtmlTms(jenis) + '">';
                        html += '<label class="custom-control-label" for="' + checkId + '"><strong>' + escapeHtmlTms(p.name_parameter_tms) + '</strong>';
                        html += ' <small class="text-muted">(ID ' + p.id_parameter_tms + ')</small>';
                        if (klinikName && klinikName !== p.name_parameter_tms) {
                            html += '<br><small class="text-muted">' + escapeHtmlTms(klinikName) + '</small>';
                        }
                        html += '</label></div></div>';
                    });
                    html += '</div></div>';
                    return html;
                }
                var html = '';
                var seen = {};
                orderKeys.forEach(function(jenis) {
                    if (!groups[jenis] || !groups[jenis].length) return;
                    seen[jenis] = true;
                    html += renderGroup(jenis, groups[jenis]);
                });
                Object.keys(groups).forEach(function(jenis) {
                    if (seen[jenis]) return;
                    html += renderGroup(jenis, groups[jenis]);
                });
                $('#tms-mqtt-order-param-list').html(html);
                syncTmsMqttTrayPosBySelection();
            }

            function applyTmsMqttEditPrefill(order) {
                if (!order) return;
                var jenisOrder = ((order.jenis_spesimen || order.jenis_sampel || 'Lainnya') + '').trim() || 'Lainnya';
                var selectedPuk = {};
                var selectedTms = {};
                (order.details || []).forEach(function(d) {
                    if (d.id_permohonan_uji_parameter_klinik) selectedPuk[String(d.id_permohonan_uji_parameter_klinik)] = true;
                    if (d.id_parameter_tms) selectedTms[String(d.id_parameter_tms)] = true;
                });
                $('#tms-mqtt-order-param-list .tms-mqtt-param-check').prop('checked', false);
                $('#tms-mqtt-order-param-list .tms-mqtt-jenis-group').each(function() {
                    var $group = $(this);
                    var jenis = (($group.attr('data-jenis') || '') + '').trim();
                    var sameJenis = jenis === jenisOrder;
                    $group.toggle(sameJenis);
                    if (!sameJenis) return;
                    $group.find('.tms-mqtt-param-check').each(function() {
                        var puk = String($(this).val() || '');
                        var tmsId = String($(this).attr('data-tms-id') || '');
                        $(this).prop('checked', !!(puk && selectedPuk[puk]) || !!(tmsId && selectedTms[tmsId]));
                    });
                    $group.find('.tms-mqtt-order-tray-jenis').val(order.tray || '');
                    $group.find('.tms-mqtt-order-pos-jenis').val(order.pos || '');
                });
                if (order.kode_barcode) {
                    $('#tms-mqtt-order-barcode').val(String(order.kode_barcode));
                }
                $('#tms-mqtt-order-existing-info').html(
                    'Mengedit order <strong>' + escapeHtmlTms(jenisOrder) + '</strong>, lalu kirim ulang ke alat. ' +
                    '<button type="button" class="btn btn-link btn-sm p-0 align-baseline" id="btn-batal-edit-tms-mqtt-order">Batal edit</button>'
                ).show();
                syncTmsMqttTrayPosBySelection();
                refreshTmsMqttSubmitButton();
            }

            function loadTmsMqttOrderForm(editOrder) {
                $('#tms-mqtt-order-info').hide().empty();
                if (!editOrder) clearTmsMqttEditMode();
                $('#tms-mqtt-order-param-list').html('<div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat parameter...</div>');
                $.getJSON(tmsOrderFormUrl).done(function(res) {
                    if (!res || !res.status) {
                        $('#tms-mqtt-order-param-list').html('<div class="text-danger text-center py-3">' + escapeHtmlTms((res && res.pesan) || 'Gagal memuat') + '</div>');
                        return;
                    }
                    $('#tms-mqtt-order-nama').val((res.pasien && res.pasien.nama_pasien) || '');
                    $('#tms-mqtt-order-dob').val((res.pasien && res.pasien.tanggal_lahir) || '');
                    $('#tms-mqtt-order-jk').val((res.pasien && res.pasien.jenis_kelamin) || '');
                    $('#tms-mqtt-order-barcode').val(res.kode_barcode || '');
                    if (editOrder) {
                        tmsMqttEditingOrderId = editOrder.id_order_tms;
                        tmsMqttEditingOrder = editOrder;
                        var params = (res.parameters || []).map(function(p) {
                            var copy = $.extend({}, p);
                            copy.selected_default = false;
                            return copy;
                        });
                        renderTmsMqttOrderParams(params);
                        applyTmsMqttEditPrefill(editOrder);
                    } else {
                        if (res.existing_orders_count > 0) {
                            $('#tms-mqtt-order-existing-info').html('Sudah ada <strong>' + res.existing_orders_count + '</strong> order TMS. Order baru akan dikirim ke alat.').show();
                        }
                        renderTmsMqttOrderParams(res.parameters || []);
                        refreshTmsMqttSubmitButton();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal memuat form order';
                    $('#tms-mqtt-order-param-list').html('<div class="text-danger text-center py-3">' + escapeHtmlTms(msg) + '</div>');
                });
            }

            function getSelectedTmsMqttParams() {
                var ids = [];
                var pukIds = [];
                var jenisMap = {};
                var trays = {};
                var positions = {};
                $('#tms-mqtt-order-param-list .tms-mqtt-param-check:checked').each(function() {
                    var tmsId = parseInt($(this).attr('data-tms-id') || 0, 10);
                    var pukId = $.trim($(this).val() || '');
                    var jenis = (($(this).attr('data-jenis') || 'Lainnya') + '').trim() || 'Lainnya';
                    if (tmsId > 0) ids.push(tmsId);
                    if (pukId !== '') pukIds.push(pukId);
                    jenisMap[jenis] = true;
                });
                $('#tms-mqtt-order-param-list .tms-mqtt-order-tray-jenis').each(function() {
                    var jenis = (($(this).attr('data-jenis') || '') + '').trim();
                    if (jenis) trays[jenis] = $.trim($(this).val() || '');
                });
                $('#tms-mqtt-order-param-list .tms-mqtt-order-pos-jenis').each(function() {
                    var jenis = (($(this).attr('data-jenis') || '') + '').trim();
                    if (jenis) positions[jenis] = $.trim($(this).val() || '');
                });
                return {
                    tmsIds: ids.filter(function(v, i, a) { return a.indexOf(v) === i; }),
                    pukIds: pukIds,
                    jenisList: Object.keys(jenisMap),
                    trays: trays,
                    positions: positions
                };
            }

            function submitTmsMqttOrder() {
                var selected = getSelectedTmsMqttParams();
                if (!selected.tmsIds.length && !selected.pukIds.length) {
                    alert('Pilih minimal satu parameter TMS.');
                    return;
                }
                if (tmsMqttEditingOrderId && selected.jenisList.length !== 1) {
                    alert('Saat mengedit order, pilih parameter dari satu jenis sampel saja.');
                    return;
                }
                var payload = {
                    _token: tmsCsrf,
                    via_mqtt: 1,
                    nama_pasien: $('#tms-mqtt-order-nama').val(),
                    tanggal_lahir: $('#tms-mqtt-order-dob').val(),
                    jenis_kelamin: $('#tms-mqtt-order-jk').val(),
                    kode_barcode: $.trim($('#tms-mqtt-order-barcode').val() || ''),
                    trays: selected.trays,
                    positions: selected.positions,
                    tray: selected.jenisList.length === 1 ? selected.trays[selected.jenisList[0]] : '',
                    pos: selected.jenisList.length === 1 ? selected.positions[selected.jenisList[0]] : '',
                    posisi: selected.jenisList.length === 1 ? selected.positions[selected.jenisList[0]] : '',
                    parameter_ids: selected.tmsIds,
                    parameter_puk_ids: selected.pukIds
                };
                var isEdit = !!tmsMqttEditingOrderId;
                var url = isEdit ? (tmsUpdateOrderUrlTpl + tmsMqttEditingOrderId) : tmsStoreOrderUrl;
                $('#btn-buat-order-tms-mqtt').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengirim ke alat...');
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': tmsCsrf }
                }).done(function(res) {
                    if (res && res.status) {
                        var html = '<div class="alert alert-success mb-0 py-2">' + escapeHtmlTms(res.pesan) + '</div>';
                        html += renderMqttPublishInfo(res.mqtt || []);
                        $('#tms-mqtt-order-info').html(html).show();
                        clearTmsMqttEditMode();
                        $('#tms-mqtt-tab-riwayat').tab('show');
                        loadTmsMqttOrders();
                    } else {
                        $('#tms-mqtt-order-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms((res && res.pesan) || 'Gagal menyimpan order') + '</div>').show();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal menyimpan/mengirim order';
                    $('#tms-mqtt-order-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms(msg) + '</div>').show();
                }).always(function() {
                    $('#btn-buat-order-tms-mqtt').prop('disabled', false);
                    refreshTmsMqttSubmitButton();
                });
            }

            function renderTmsMqttOrders(orders) {
                tmsMqttOrdersCache = orders || [];
                orders = mergeTmsOrdersForDisplay(orders);
                if (!orders || !orders.length) {
                    $('#tms-mqtt-riwayat-body').html('<div class="text-muted text-center py-3">Belum ada order TMS untuk permohonan ini</div>');
                    updateTmsMqttFooterButtons();
                    return;
                }
                var html = '';
                orders.forEach(function(o) {
                    var statusBadge = tmsOrderStatusBadge(o);
                    html += '<div class="card mb-2 shadow-sm">';
                    html += '<div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size:12px;">';
                    html += '<div>' + statusBadge + ' ';
                    if (o.jenis_spesimen) html += '<span class="badge badge-info mr-1">' + escapeHtmlTms(o.jenis_spesimen) + '</span> ';
                    html += '<strong>Barcode:</strong> ' + escapeHtmlTms(o.kode_barcode || '-') + ' ';
                    html += '| <strong>Tray:</strong> ' + escapeHtmlTms(o.tray || '-') + ' ';
                    html += '| <strong>Pos:</strong> ' + escapeHtmlTms(o.pos || '-');
                    html += '</div><div class="mt-1 mt-md-0">';
                    html += '<button type="button" class="btn btn-xs btn-outline-secondary btn-edit-tms-mqtt-order mr-1" data-id="' + o.id_order_tms + '"><i class="fa fa-pencil"></i> Edit</button>';
                    html += '<button type="button" class="btn btn-xs btn-outline-info btn-republish-tms-mqtt-order mr-1" data-id="' + o.id_order_tms + '"><i class="fa fa-paper-plane"></i> Kirim ulang</button>';
                    html += '<button type="button" class="btn btn-xs btn-outline-warning btn-resend-tms-mqtt-result mr-1" data-id="' + o.id_order_tms + '" data-sample="' + escapeHtmlTms(o.kode_barcode || '') + '" title="Minta alat kirim ulang hasil via MQTT"><i class="fa fa-repeat"></i> Resend Hasil</button>';
                    html += '<button type="button" class="btn btn-xs btn-outline-danger btn-delete-tms-mqtt-order mr-1" data-id="' + o.id_order_tms + '"><i class="fa fa-trash"></i> Hapus</button>';
                    html += '<button type="button" class="btn btn-xs btn-success btn-isi-order-tms-mqtt" data-id="' + o.id_order_tms + '"><i class="fa fa-download"></i> Isi ke Form</button>';
                    html += '</div></div><div class="card-body py-2 px-2">';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size:12px;">';
                    html += '<thead class="thead-light"><tr><th>Parameter</th><th>ID</th><th>Value</th></tr></thead><tbody>';
                    (o.details || []).forEach(function(d) {
                        d.value = cleanTmsResultValue(d.value);
                        html += '<tr data-order-detail="1" data-puk="' + escapeHtmlTms(d.id_permohonan_uji_parameter_klinik || '') + '" data-value="' + escapeHtmlTms(d.value || '') + '" data-tms-id="' + d.id_parameter_tms + '" data-tms-name="' + escapeHtmlTms(d.name_parameter_tms || '') + '">';
                        html += '<td>' + escapeHtmlTms(d.name_parameter_tms || '-') + '</td>';
                        html += '<td>' + d.id_parameter_tms + '</td>';
                        html += '<td class="font-weight-bold">' + formatTmsDetailValueCell(d) + '</td></tr>';
                    });
                    if (!(o.details || []).length) {
                        html += '<tr><td colspan="3" class="text-muted text-center">Tidak ada detail</td></tr>';
                    }
                    html += '</tbody></table></div>';
                    html += '<small class="text-muted">' + tmsOrderFooterMeta(o) + '</small>';
                    html += '</div></div>';
                });
                $('#tms-mqtt-riwayat-body').html(html);
                updateTmsMqttFooterButtons();
            }

            function loadTmsMqttOrders() {
                $('#tms-mqtt-riwayat-body').html('<div class="text-muted text-center py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat riwayat...</div>');
                $.getJSON(tmsListOrdersUrl).done(function(res) {
                    if (!res || !res.status) {
                        $('#tms-mqtt-riwayat-body').html('<div class="text-danger text-center py-3">' + escapeHtmlTms((res && res.pesan) || 'Gagal memuat') + '</div>');
                        return;
                    }
                    renderTmsMqttOrders(res.orders || []);
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal memuat riwayat order';
                    $('#tms-mqtt-riwayat-body').html('<div class="text-danger text-center py-3">' + escapeHtmlTms(msg) + '</div>');
                });
            }

            function tmsMqttOrdersToMatchedRows(orders) {
                var rows = [];
                var seen = {};
                mergeTmsOrdersForDisplay(orders || []).forEach(function(o) {
                    (o.details || []).forEach(function(d) {
                        var val = cleanTmsResultValue(d.value);
                        if (val === '') {
                            return;
                        }
                        var puk = d.id_permohonan_uji_parameter_klinik || '';
                        var key = puk ? ('puk:' + puk) : ('tms:' + d.id_parameter_tms);
                        if (seen[key]) {
                            return;
                        }
                        seen[key] = true;
                        rows.push({
                            id_permohonan_uji_parameter_klinik: puk,
                            result_value: val,
                            tms_parameter_id: d.id_parameter_tms,
                            tms_parameter_name: d.name_parameter_tms,
                            nama_parameter_satuan_klinik: d.nama_parameter_satuan_klinik || d.name_parameter_tms
                        });
                    });
                });
                return rows;
            }

            function renderTmsMqttMatched(rows) {
                if (!rows || !rows.length) {
                    $('#tms-mqtt-result-body').html('<tr><td colspan="3" class="text-center text-muted">Tidak ada hasil yang cocok</td></tr>');
                    tmsMqttMatchedCache = [];
                    updateTmsMqttFooterButtons();
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    row.result_value = cleanTmsResultValue(row.result_value);
                    html += '<tr>';
                    html += '<td>' + escapeHtmlTms(row.nama_parameter_satuan_klinik || '-') + '</td>';
                    html += '<td>' + escapeHtmlTms(row.tms_parameter_name || '-') + ' <small class="text-muted">(ID ' + escapeHtmlTms(row.tms_parameter_id) + ')</small></td>';
                    html += '<td class="font-weight-bold">' + escapeHtmlTms(row.result_value) + '</td>';
                    html += '</tr>';
                });
                $('#tms-mqtt-result-body').html(html);
                tmsMqttMatchedCache = rows;
                updateTmsMqttFooterButtons();
            }

            function renderTmsMqttSuggestedBarcodes(orders) {
                var ids = [];
                var seen = {};
                (orders || []).forEach(function(o) {
                    var id = String(o.kode_barcode || '').trim();
                    if (!id || seen[id]) {
                        return;
                    }
                    seen[id] = true;
                    ids.push(id);
                });
                if (!ids.length) {
                    return;
                }
                var html = '<div class="alert alert-warning mb-0 py-2">Barcode pada order: ';
                html += ids.slice(0, 8).map(function(id) {
                    return '<button type="button" class="btn btn-link btn-sm p-0 align-baseline tms-mqtt-suggested-sample" data-sample="' +
                        escapeHtmlTms(id) + '"><code>' + escapeHtmlTms(id) + '</code></button>';
                }).join(', ');
                html += '<br><small>Klik barcode, lalu <strong>Isi ke Form</strong>.</small></div>';
                $('#tms-mqtt-result-info').html(html).show();
            }

            function fetchTmsMqttResults() {
                var sampleId = $.trim($('#tms-mqtt-sample-id').val() || '');
                $('#tms-mqtt-result-info').hide().empty();
                $('#tms-mqtt-result-body').html('<tr><td colspan="3" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Mencari data...</td></tr>');
                tmsMqttMatchedCache = [];
                updateTmsMqttFooterButtons();

                $.getJSON(tmsListOrdersUrl)
                    .done(function(res) {
                        if (!res || !res.status) {
                            $('#tms-mqtt-result-body').html('<tr><td colspan="3" class="text-center text-danger">' + escapeHtmlTms((res && res.pesan) || 'Data tidak ditemukan') + '</td></tr>');
                            return;
                        }
                        var orders = res.orders || [];
                        tmsMqttOrdersCache = orders;
                        var filtered = orders;
                        if (sampleId) {
                            filtered = orders.filter(function(o) {
                                var barcode = String(o.kode_barcode || '');
                                return barcode === sampleId || barcode.indexOf(sampleId) === 0;
                            });
                        }
                        var matched = tmsMqttOrdersToMatchedRows(filtered);
                        if (!matched.length) {
                            $('#tms-mqtt-result-body').html('<tr><td colspan="3" class="text-center text-muted">Belum ada hasil untuk Sample ID ini</td></tr>');
                            if (!sampleId || !filtered.length) {
                                renderTmsMqttSuggestedBarcodes(orders);
                            }
                            tmsMqttMatchedCache = [];
                            updateTmsMqttFooterButtons();
                            return;
                        }
                        renderTmsMqttMatched(matched);
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal mencari hasil';
                        $('#tms-mqtt-result-body').html('<tr><td colspan="3" class="text-center text-danger">' + escapeHtmlTms(msg) + '</td></tr>');
                    });
            }

            function renderTmsMqttInbox(res) {
                var applied = (res && res.applied_count) ? res.applied_count : 0;
                var count = (res && res.count) ? res.count : 0;
                var html = '<div class="alert ' + (applied > 0 ? 'alert-success' : 'alert-info') + ' py-2 small mb-0">';
                if (applied > 0) {
                    html += applied + ' hasil dari alat berhasil masuk ke order. Silakan Cari lagi, lalu Isi ke Form.';
                } else if (count > 0) {
                    html += 'Ada pesan dari alat, tetapi belum cocok dengan order permohonan ini.';
                } else {
                    html += (res && res.pesan) ? escapeHtmlTms(res.pesan) : 'Tidak ada hasil baru dari alat.';
                }
                html += '</div>';
                $('#tms-mqtt-inbox').html(html).show();
            }

            function listenTmsMqtt() {
                $('#btn-tms-mqtt-listen').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Menarik...');
                $('#tms-mqtt-inbox').html('<div class="alert alert-info py-2 small mb-0">Menunggu hasil dari alat...</div>').show();
                $.getJSON(tmsMqttListenUrl, { timeout: {{ (int) config('mqtt.listen_timeout', 6) }} })
                    .done(function(res) {
                        renderTmsMqttInbox(res || {});
                        fetchTmsMqttResults();
                        if (res && res.applied_count > 0) {
                            loadTmsMqttOrders();
                        }
                    })
                    .fail(function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal menarik hasil dari alat';
                        $('#tms-mqtt-inbox').html('<div class="alert alert-danger py-2 small mb-0">' + escapeHtmlTms(msg) + '</div>').show();
                    })
                    .always(function() {
                        $('#btn-tms-mqtt-listen').prop('disabled', false).html('<i class="fa fa-refresh mr-1"></i> Tarik hasil');
                    });
            }

            $('#btn-ambil-tms-mqtt').on('click', function() {
                loadTmsRoundOptionsFromStorage();
                $('#modalAmbilTmsMqtt').modal('show');
                $('#tms-mqtt-tab-hasil').tab('show');
                updateTmsMqttFooterButtons();
                fetchTmsMqttResults();
            });

            $('#btn-cari-tms-mqtt').on('click', function() {
                fetchTmsMqttResults();
            });
            $(document).on('keydown', '#tms-mqtt-sample-id', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    fetchTmsMqttResults();
                }
            });
            $(document).on('click', '.tms-mqtt-suggested-sample', function() {
                var sample = $(this).attr('data-sample') || $(this).data('sample') || '';
                if (!sample) return;
                $('#tms-mqtt-sample-id').val(sample);
                $('#tms-mqtt-tab-hasil').tab('show');
                fetchTmsMqttResults();
            });

            var tmsMassPatientsCache = [];
            var tmsMassCatalogCache = [];
            var tmsMassTemplates = [];
            var tmsMassTemplateSeq = 0;
            var tmsMassSelectedJenisList = [];
            var tmsMassRowState = {};
            var tmsMassTrayByJenis = {};

            function tmsMassJenisOrder() {
                return ['Darah', 'Blood Cell', 'Serum', 'Plasma', 'Plasma NaF', 'Urine', 'Feses', 'Swab', 'Lainnya'];
            }

            function catalogForJenis(jenis) {
                return (tmsMassCatalogCache || []).filter(function(c) {
                    return (c.jenis_spesimen || 'Lainnya') === jenis;
                });
            }

            function buildPatientParamLookup(patient) {
                var map = {};
                (patient.parameters || []).forEach(function(p) {
                    var key = String(p.id_parameter_tms) + '|' + (p.jenis_spesimen || 'Lainnya');
                    map[key] = p.id_permohonan_uji_parameter_klinik;
                });
                return map;
            }

            function tmsMassRowKey(patientId, jenis) {
                return String(patientId) + '|' + String(jenis);
            }

            function snapshotTmsMassRowState() {
                $('#tms-mass-patients-wrap tr[data-patient-id]').each(function() {
                    var $row = $(this);
                    var key = tmsMassRowKey($row.attr('data-patient-id'), $row.attr('data-jenis'));
                    tmsMassRowState[key] = {
                        checked: $row.find('.tms-mass-patient-check').is(':checked'),
                        template: $row.find('.tms-mass-patient-template').val() || '',
                        tray: $row.find('.tms-mass-patient-tray').val() || '',
                        pos: $row.find('.tms-mass-patient-pos').val() || ''
                    };
                });
                $('.tms-mass-jenis-block').each(function() {
                    var jenis = ($(this).attr('data-jenis') || '').trim();
                    if (jenis) {
                        tmsMassTrayByJenis[jenis] = $.trim($(this).find('.tms-mass-tray-massal').val() || '');
                    }
                });
            }

            function selectedJenisOrdered() {
                var groups = groupTmsMassPatientsByJenis();
                var selected = {};
                tmsMassSelectedJenisList.forEach(function(j) { selected[j] = true; });
                return groups.filter(function(g) { return selected[g.jenis]; });
            }

            function groupTmsMassPatientsByJenis() {
                var groups = {};
                tmsMassPatientsCache.forEach(function(p) {
                    (p.parameters || []).forEach(function(pr) {
                        var jenis = (pr.jenis_spesimen || 'Lainnya').trim() || 'Lainnya';
                        if (!groups[jenis]) groups[jenis] = {};
                        if (!groups[jenis][p.id_permohonan_uji_klinik]) {
                            groups[jenis][p.id_permohonan_uji_klinik] = {
                                patient: p,
                                parameters: []
                            };
                        }
                        groups[jenis][p.id_permohonan_uji_klinik].parameters.push(pr);
                    });
                });

                var ordered = [];
                var seen = {};
                tmsMassJenisOrder().forEach(function(jenis) {
                    if (!groups[jenis]) return;
                    seen[jenis] = true;
                    ordered.push({
                        jenis: jenis,
                        rows: Object.keys(groups[jenis]).map(function(id) { return groups[jenis][id]; })
                    });
                });
                Object.keys(groups).forEach(function(jenis) {
                    if (seen[jenis]) return;
                    ordered.push({
                        jenis: jenis,
                        rows: Object.keys(groups[jenis]).map(function(id) { return groups[jenis][id]; })
                    });
                });
                return ordered;
            }

            function templatesForJenis(jenis) {
                return tmsMassTemplates.filter(function(tpl) {
                    return (tpl.jenis || '') === jenis;
                });
            }

            function renderTmsMassJenisPicker() {
                var groups = groupTmsMassPatientsByJenis();
                if (!groups.length) {
                    $('#tms-mass-jenis-wrap').html('<span class="text-muted small">Tidak ada jenis sampel TMS.</span>');
                    return;
                }
                var selected = {};
                tmsMassSelectedJenisList.forEach(function(j) { selected[j] = true; });
                var html = '';
                groups.forEach(function(group) {
                    var active = selected[group.jenis] ? 'btn-info' : 'btn-outline-info';
                    html += '<button type="button" class="btn btn-sm ' + active + ' mr-1 mb-1 btn-tms-mass-jenis" data-jenis="' + escapeHtmlTms(group.jenis) + '">';
                    html += escapeHtmlTms(group.jenis) + ' <span class="badge badge-light">' + group.rows.length + '</span>';
                    html += '</button>';
                });
                $('#tms-mass-jenis-wrap').html(html);
            }

            function ensureDefaultTemplateForJenis(jenis) {
                if (templatesForJenis(jenis).length) return;
                var keys = catalogForJenis(jenis).map(function(c) { return c.catalog_key; });
                tmsMassTemplateSeq += 1;
                tmsMassTemplates.push({
                    id: 'tpl_' + tmsMassTemplateSeq,
                    name: 'Semua ' + jenis,
                    jenis: jenis,
                    catalog_keys: keys
                });
            }

            function toggleTmsMassJenis(jenis) {
                jenis = (jenis || '').trim();
                if (!jenis) return;
                snapshotTmsMassRowState();
                var idx = tmsMassSelectedJenisList.indexOf(jenis);
                if (idx >= 0) {
                    tmsMassSelectedJenisList.splice(idx, 1);
                } else {
                    tmsMassSelectedJenisList.push(jenis);
                }
                refreshTmsMassSteps();
            }

            function refreshTmsMassSteps() {
                renderTmsMassJenisPicker();
                if (!tmsMassSelectedJenisList.length) {
                    $('#tms-mass-step-pasien, #tms-mass-step-kelompok').hide();
                    $('#tms-mass-jenis-label').empty();
                    return;
                }
                tmsMassSelectedJenisList.forEach(function(jenis) {
                    ensureDefaultTemplateForJenis(jenis);
                });
                var labels = tmsMassSelectedJenisList.map(function(j) {
                    return '<span class="badge badge-info mr-1">' + escapeHtmlTms(j) + '</span>';
                }).join('');
                $('#tms-mass-jenis-label').html(labels);
                $('#tms-mass-step-pasien').show();
                renderTmsMassPatientsTable();
                syncTmsMassKelompokStep();
            }

            function syncTmsMassKelompokStep() {
                var hasChecked = $('#tms-mass-patients-wrap .tms-mass-patient-check:checked').length > 0;
                if (tmsMassSelectedJenisList.length && hasChecked) {
                    $('#tms-mass-step-kelompok').show();
                    renderTmsMassTemplates();
                } else {
                    $('#tms-mass-step-kelompok').hide();
                }
            }

            function renderTmsMassPatientRows(jenis, rows) {
                var html = '';
                rows.forEach(function(row) {
                    var p = row.patient;
                    var paramLabels = (row.parameters || []).map(function(pr) {
                        return pr.name_parameter_tms;
                    }).join(', ');
                    var state = tmsMassRowState[tmsMassRowKey(p.id_permohonan_uji_klinik, jenis)] || { checked: true, template: '', tray: '', pos: '' };
                    var rowClass = p.is_current ? 'table-info' : '';
                    var pid = escapeHtmlTms(p.id_permohonan_uji_klinik);
                    var jenisEsc = escapeHtmlTms(jenis);
                    html += '<tr class="' + rowClass + '" data-patient-id="' + pid + '" data-jenis="' + jenisEsc + '">';
                    html += '<td><input type="checkbox" class="tms-mass-patient-check" value="' + pid + '" data-jenis="' + jenisEsc + '"' + (state.checked ? ' checked' : '') + '></td>';
                    html += '<td>' + escapeHtmlTms(p.noregister || '-') + '</td>';
                    html += '<td>' + escapeHtmlTms(p.nama_pasien || '-') + (p.is_current ? ' <span class="badge badge-primary">pasien sekarang</span>' : '') + '</td>';
                    html += '<td><code>' + escapeHtmlTms(p.kode_barcode || '-') + '</code></td>';
                    html += '<td><small>' + escapeHtmlTms(paramLabels) + '</small></td>';
                    html += '<td><select class="form-control form-control-sm tms-mass-patient-template" data-patient-id="' + pid + '" data-jenis="' + jenisEsc + '" data-prev="' + escapeHtmlTms(state.template) + '" style="min-width:140px;"></select></td>';
                    html += '<td><input type="text" class="form-control form-control-sm tms-mass-patient-tray" data-patient-id="' + pid + '" data-jenis="' + jenisEsc + '" placeholder="opsional" autocomplete="off" inputmode="numeric" value="' + escapeHtmlTms(state.tray) + '"></td>';
                    html += '<td><input type="text" class="form-control form-control-sm tms-mass-patient-pos" data-patient-id="' + pid + '" data-jenis="' + jenisEsc + '" placeholder="opsional" autocomplete="off" inputmode="numeric" value="' + escapeHtmlTms(state.pos) + '"></td>';
                    html += '</tr>';
                });
                return html;
            }

            function renderTmsMassPatientsTable() {
                var selectedGroups = selectedJenisOrdered();
                if (!selectedGroups.length) {
                    $('#tms-mass-patients-wrap').html('<div class="text-muted text-center py-3">Pilih jenis sampel terlebih dahulu.</div>');
                    return;
                }

                var html = '';
                selectedGroups.forEach(function(group) {
                    var traySaved = tmsMassTrayByJenis[group.jenis] || '';
                    html += '<div class="tms-mass-jenis-block" data-jenis="' + escapeHtmlTms(group.jenis) + '">';
                    html += '<div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1 bg-light border-bottom">';
                    html += '<div class="d-flex align-items-center mb-1 mb-sm-0">';
                    html += '<input type="checkbox" class="tms-mass-check-all mr-1" data-jenis="' + escapeHtmlTms(group.jenis) + '" checked>';
                    html += '<span class="badge badge-info">' + escapeHtmlTms(group.jenis) + '</span>';
                    html += ' <small class="text-muted ml-1">' + group.rows.length + ' pasien</small></div>';
                    html += '<div class="d-flex align-items-center">';
                    html += '<input type="text" class="form-control form-control-sm tms-mass-tray-massal mr-1" data-jenis="' + escapeHtmlTms(group.jenis) + '" placeholder="Tray" style="width:64px;" inputmode="numeric" autocomplete="off" value="' + escapeHtmlTms(traySaved) + '">';
                    html += '<button type="button" class="btn btn-xs btn-outline-info btn-tms-mass-fill-tray" data-jenis="' + escapeHtmlTms(group.jenis) + '" title="Isi tray pasien tercetang pada jenis ini"><i class="fa fa-th mr-1"></i> Isi tray massal</button>';
                    html += '</div></div>';
                    html += '<table class="table table-sm table-bordered mb-0" style="font-size:12px;">';
                    html += '<thead class="thead-light"><tr>';
                    html += '<th style="width:36px;"></th>';
                    html += '<th>No. Reg</th><th>Nama Pasien</th><th>Barcode</th><th>Parameter</th><th>Kelompok</th>';
                    html += '<th style="width:72px;">Tray</th><th style="width:72px;">Pos</th>';
                    html += '</tr></thead><tbody>';
                    html += renderTmsMassPatientRows(group.jenis, group.rows);
                    html += '</tbody></table></div>';
                });
                $('#tms-mass-patients-wrap').html(html);
                refreshTmsMassPatientTemplateSelects();
            }

            function refreshTmsMassPatientTemplateSelects() {
                $('.tms-mass-patient-template').each(function() {
                    var $sel = $(this);
                    var jenis = (($sel.attr('data-jenis') || '') + '').trim();
                    var prev = $sel.attr('data-prev') || $sel.val() || '';
                    var matched = templatesForJenis(jenis);
                    $sel.empty();
                    $sel.append('<option value="">— pilih kelompok —</option>');
                    matched.forEach(function(tpl) {
                        $sel.append('<option value="' + tpl.id + '">' + escapeHtmlTms(tpl.name) + '</option>');
                    });
                    if (prev && matched.some(function(t) { return String(t.id) === String(prev); })) {
                        $sel.val(prev);
                    } else if (matched.length === 1) {
                        $sel.val(String(matched[0].id));
                    }
                    $sel.removeAttr('data-prev');
                });
            }

            function renderTmsMassTemplateCard(tpl, jenis) {
                var paramHtml = '';
                catalogForJenis(jenis).forEach(function(cat) {
                    var checked = (tpl.catalog_keys || []).indexOf(cat.catalog_key) >= 0 ? ' checked' : '';
                    var simlab = (cat.nama_parameter_klinik_samples || []).join(', ');
                    var inputId = 'tpl_' + tpl.id + '_' + String(cat.catalog_key).replace(/[^a-zA-Z0-9_-]/g, '_');
                    paramHtml += '<div class="custom-control custom-checkbox col-md-6 mb-1">';
                    paramHtml += '<input type="checkbox" class="custom-control-input tms-mass-tpl-param" id="' + inputId + '" data-template-id="' + tpl.id + '" data-catalog-key="' + escapeHtmlTms(cat.catalog_key) + '"' + checked + '>';
                    paramHtml += '<label class="custom-control-label" for="' + inputId + '">';
                    paramHtml += '<strong>' + escapeHtmlTms(cat.name_parameter_tms) + '</strong>';
                    if (simlab) {
                        paramHtml += '<br><small class="text-muted">SIMLAB: ' + escapeHtmlTms(simlab) + '</small>';
                    }
                    paramHtml += '</label></div>';
                });

                return '<div class="card mb-2 tms-mass-template-card" data-template-id="' + tpl.id + '">' +
                    '<div class="card-header py-2 d-flex justify-content-between align-items-center" style="font-size:12px;">' +
                    '<div class="d-flex align-items-center flex-grow-1 mr-2">' +
                    '<span class="mr-2">Kelompok:</span>' +
                    '<input type="text" class="form-control form-control-sm tms-mass-template-name" data-template-id="' + tpl.id + '" value="' + escapeHtmlTms(tpl.name) + '" placeholder="Nama kelompok">' +
                    '</div>' +
                    '<button type="button" class="btn btn-xs btn-outline-danger btn-tms-mass-remove-template" data-template-id="' + tpl.id + '"><i class="fa fa-trash"></i></button>' +
                    '</div>' +
                    '<div class="card-body py-2 px-2"><div class="row no-gutters">' + paramHtml + '</div></div>' +
                    '</div>';
            }

            function renderTmsMassTemplates() {
                var selectedGroups = selectedJenisOrdered();
                if (!selectedGroups.length) {
                    $('#tms-mass-templates-wrap').html('<div class="text-muted small py-2">Pilih jenis sampel terlebih dahulu.</div>');
                    return;
                }
                var html = '';
                selectedGroups.forEach(function(group) {
                    var list = templatesForJenis(group.jenis);
                    html += '<div class="tms-mass-template-jenis mb-3" data-jenis="' + escapeHtmlTms(group.jenis) + '">';
                    html += '<div class="d-flex justify-content-between align-items-center mb-1">';
                    html += '<span class="badge badge-info">' + escapeHtmlTms(group.jenis) + '</span>';
                    html += '<button type="button" class="btn btn-xs btn-outline-primary btn-tms-mass-add-template" data-jenis="' + escapeHtmlTms(group.jenis) + '"><i class="fa fa-plus mr-1"></i> Tambah Kelompok</button>';
                    html += '</div>';
                    if (!list.length) {
                        html += '<div class="text-muted small py-1">Belum ada kelompok untuk ' + escapeHtmlTms(group.jenis) + '.</div>';
                    } else {
                        list.forEach(function(tpl) {
                            html += renderTmsMassTemplateCard(tpl, group.jenis);
                        });
                    }
                    html += '</div>';
                });
                $('#tms-mass-templates-wrap').html(html);
                refreshTmsMassPatientTemplateSelects();
            }

            function addTmsMassTemplate(defaultName, jenis) {
                jenis = (jenis || '').trim();
                if (!jenis) {
                    alert('Pilih jenis sampel terlebih dahulu.');
                    return;
                }
                snapshotTmsMassRowState();
                tmsMassTemplateSeq += 1;
                tmsMassTemplates.push({
                    id: 'tpl_' + tmsMassTemplateSeq,
                    name: defaultName || ('Kelompok ' + tmsMassTemplateSeq),
                    jenis: jenis,
                    catalog_keys: []
                });
                renderTmsMassTemplates();
            }

            function loadTmsMassOrderCandidates() {
                tmsMassSelectedJenisList = [];
                tmsMassTemplates = [];
                tmsMassTemplateSeq = 0;
                tmsMassRowState = {};
                tmsMassTrayByJenis = {};
                $('#tms-mass-step-pasien, #tms-mass-step-kelompok').hide();
                $('#tms-mass-jenis-wrap').html('<span class="text-muted small"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat jenis sampel...</span>');
                $('#tms-mass-info').hide().empty();
                var params = {};
                if (tmsMassJamQuery) {
                    params.jam = tmsMassJamQuery;
                }
                $.getJSON(tmsMassCandidatesUrl, params).done(function(res) {
                    if (!res || !res.status) {
                        $('#tms-mass-jenis-wrap').html('<span class="text-danger small">' + escapeHtmlTms((res && res.pesan) || 'Gagal memuat') + '</span>');
                        return;
                    }
                    tmsMassPatientsCache = res.patients || [];
                    tmsMassCatalogCache = res.parameter_catalog || [];
                    var scopeText = (res.scope && res.scope.is_haji)
                        ? 'Lingkup: rombongan haji yang sama'
                        : 'Lingkup: permohonan tanggal pengujian yang sama';
                    scopeText += ' — ' + (res.total || 0) + ' pasien';
                    $('#tms-mass-scope-info').text(scopeText);
                    renderTmsMassJenisPicker();
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal memuat kandidat order massal';
                    $('#tms-mass-jenis-wrap').html('<span class="text-danger small">' + escapeHtmlTms(msg) + '</span>');
                });
            }

            function buildTmsMassAssignments() {
                var assignments = [];
                var patientMap = {};
                tmsMassPatientsCache.forEach(function(p) {
                    patientMap[p.id_permohonan_uji_klinik] = p;
                });

                $('.tms-mass-patient-check:checked').each(function() {
                    var patientId = $(this).val();
                    var jenis = (($(this).attr('data-jenis') || '') + '').trim();
                    var patient = patientMap[patientId];
                    if (!patient || jenis === '') return;

                    var $row = $(this).closest('tr');
                    var templateId = $row.find('.tms-mass-patient-template').val() || '';
                    if (!templateId) return;

                    var paramLookup = buildPatientParamLookup(patient);
                    var pukSet = {};
                    var tpl = tmsMassTemplates.find(function(t) { return String(t.id) === String(templateId); });
                    if (!tpl) return;
                    (tpl.catalog_keys || []).forEach(function(catalogKey) {
                        var parts = String(catalogKey).split('|');
                        var keyJenis = parts[1] || 'Lainnya';
                        if (keyJenis !== jenis) return;
                        var pukId = paramLookup[catalogKey];
                        if (pukId) pukSet[String(pukId)] = true;
                    });

                    var pukIds = Object.keys(pukSet);
                    if (!pukIds.length) return;

                    var tray = $.trim($row.find('.tms-mass-patient-tray').val() || '');
                    var pos = $.trim($row.find('.tms-mass-patient-pos').val() || '');
                    var trays = {};
                    var positions = {};
                    trays[jenis] = tray;
                    positions[jenis] = pos;

                    assignments.push({
                        id_permohonan_uji_klinik: patientId,
                        parameter_puk_ids: pukIds,
                        kode_barcode: patient.kode_barcode || '',
                        tray: tray,
                        pos: pos,
                        posisi: pos,
                        trays: trays,
                        positions: positions
                    });
                });

                return assignments;
            }

            function submitTmsMassOrder() {
                if (!tmsMassSelectedJenisList.length) {
                    alert('Pilih minimal satu jenis sampel.');
                    return;
                }
                if (!$('#tms-mass-patients-wrap .tms-mass-patient-check:checked').length) {
                    alert('Pilih minimal satu pasien.');
                    return;
                }
                var assignments = buildTmsMassAssignments();
                if (!assignments.length) {
                    alert('Pilih pasien dan tentukan kelompok parameter untuk masing-masing baris.');
                    return;
                }

                var payload = {
                    _token: tmsCsrf,
                    via_mqtt: 1,
                    assignments: assignments
                };

                $('#btn-buat-order-tms-massal').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengirim...');
                $.ajax({
                    url: tmsMassStoreUrl,
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': tmsCsrf }
                }).done(function(res) {
                    if (res && res.status) {
                        var html = '<div class="alert alert-success mb-0 py-2">' + escapeHtmlTms(res.pesan) + '</div>';
                        html += renderMqttPublishInfo(res.mqtt || []);
                        $('#tms-mass-info').html(html).show();
                        loadTmsMassOrderCandidates();
                    } else {
                        $('#tms-mass-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms((res && res.pesan) || 'Gagal order massal') + '</div>').show();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal order massal';
                    $('#tms-mass-info').html('<div class="alert alert-danger mb-0 py-2">' + escapeHtmlTms(msg) + '</div>').show();
                }).always(function() {
                    $('#btn-buat-order-tms-massal').prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Kirim Order Massal');
                });
            }

            $('#tmsMqttTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                updateTmsMqttFooterButtons();
                var href = $(e.target).attr('href');
                if (href === '#tmsMqttPaneOrder') {
                    loadTmsMqttOrderForm(tmsMqttEditingOrder || null);
                } else if (href === '#tmsMqttPaneMassal') {
                    loadTmsMassOrderCandidates();
                } else if (href === '#tmsMqttPaneRiwayat') {
                    loadTmsMqttOrders();
                } else if (href === '#tmsMqttPaneHasil') {
                    fetchTmsMqttResults();
                }
            });

            $('#btn-tms-mqtt-param-all').on('click', function() {
                $('#tms-mqtt-order-param-list .tms-mqtt-param-check:visible').prop('checked', true);
                syncTmsMqttTrayPosBySelection();
            });
            $('#btn-tms-mqtt-param-none').on('click', function() {
                $('#tms-mqtt-order-param-list .tms-mqtt-param-check:visible').prop('checked', false);
                syncTmsMqttTrayPosBySelection();
            });
            $(document).on('change', '.tms-mqtt-param-check', function() {
                syncTmsMqttTrayPosBySelection();
            });
            $(document).on('click', '.tms-mqtt-jenis-toggle', function(e) {
                e.preventDefault();
                var $group = $(this).closest('.tms-mqtt-jenis-group');
                var $checks = $group.find('.tms-mqtt-param-check');
                if (!$checks.length) return;
                var allChecked = $checks.length === $checks.filter(':checked').length;
                $checks.prop('checked', !allChecked);
                syncTmsMqttTrayPosBySelection();
            });
            $('#btn-buat-order-tms-mqtt').on('click', function() {
                submitTmsMqttOrder();
            });
            $('#btn-buat-order-tms-massal').on('click', function() {
                submitTmsMassOrder();
            });
            $('#btn-tms-mass-reload').on('click', function() {
                loadTmsMassOrderCandidates();
            });
            $(document).on('click', '.btn-tms-mass-jenis', function() {
                toggleTmsMassJenis($(this).attr('data-jenis') || '');
            });
            $(document).on('input', '.tms-mass-tray-massal', function() {
                var jenis = ($(this).attr('data-jenis') || '').trim();
                if (jenis) tmsMassTrayByJenis[jenis] = $.trim($(this).val() || '');
            });
            $(document).on('click', '.btn-tms-mass-fill-tray', function() {
                var $block = $(this).closest('.tms-mass-jenis-block');
                var tray = $.trim($block.find('.tms-mass-tray-massal').val() || '');
                if (tray === '') {
                    alert('Isi nomor tray untuk jenis sampel ini terlebih dahulu.');
                    $block.find('.tms-mass-tray-massal').focus();
                    return;
                }
                var $checked = $block.find('.tms-mass-patient-check:checked');
                if (!$checked.length) {
                    alert('Centang pasien pada jenis sampel ini yang akan diisi tray.');
                    return;
                }
                $checked.each(function() {
                    $(this).closest('tr').find('.tms-mass-patient-tray').val(tray);
                });
                tmsMassTrayByJenis[$block.attr('data-jenis') || ''] = tray;
            });
            $('#btn-tms-mass-fill-pos').on('click', function() {
                $('.tms-mass-jenis-block').each(function() {
                    var $block = $(this);
                    var pos = 1;
                    var defaultTray = '';
                    $block.find('.tms-mass-patient-check:checked').each(function() {
                        var trayVal = $.trim($(this).closest('tr').find('.tms-mass-patient-tray').val() || '');
                        if (trayVal !== '') {
                            defaultTray = trayVal;
                            return false;
                        }
                    });
                    $block.find('.tms-mass-patient-check:checked').each(function() {
                        var $row = $(this).closest('tr');
                        if (defaultTray !== '' && $.trim($row.find('.tms-mass-patient-tray').val() || '') === '') {
                            $row.find('.tms-mass-patient-tray').val(defaultTray);
                        }
                        $row.find('.tms-mass-patient-pos').val(String(pos));
                        pos += 1;
                    });
                });
            });
            $(document).on('change', '.tms-mass-patient-check', function() {
                syncTmsMassKelompokStep();
            });
            $(document).on('click', '.btn-tms-mass-add-template', function() {
                addTmsMassTemplate('', $(this).attr('data-jenis') || '');
            });
            $(document).on('click', '.btn-tms-mass-remove-template', function() {
                var tplId = $(this).data('template-id');
                tmsMassTemplates = tmsMassTemplates.filter(function(t) { return String(t.id) !== String(tplId); });
                renderTmsMassTemplates();
            });
            $(document).on('change', '.tms-mass-tpl-param', function() {
                var tplId = $(this).data('template-id');
                var tpl = tmsMassTemplates.find(function(t) { return String(t.id) === String(tplId); });
                if (!tpl) return;
                tpl.catalog_keys = [];
                $('.tms-mass-tpl-param[data-template-id="' + tplId + '"]:checked').each(function() {
                    tpl.catalog_keys.push(String($(this).data('catalog-key')));
                });
            });
            $(document).on('input', '.tms-mass-template-name', function() {
                var tplId = $(this).data('template-id');
                var tpl = tmsMassTemplates.find(function(t) { return String(t.id) === String(tplId); });
                if (tpl) tpl.name = $.trim($(this).val() || '');
                refreshTmsMassPatientTemplateSelects();
            });
            $(document).on('change', '.tms-mass-check-all', function() {
                var jenis = ($(this).attr('data-jenis') || '');
                $(this).closest('.tms-mass-jenis-block').find('.tms-mass-patient-check').prop('checked', $(this).is(':checked'));
                syncTmsMassKelompokStep();
            });
            $('#btn-tms-mqtt-listen').on('click', function() {
                listenTmsMqtt();
            });
            $(document).on('click', '#btn-batal-edit-tms-mqtt-order', function(e) {
                e.preventDefault();
                clearTmsMqttEditMode();
                loadTmsMqttOrderForm();
            });
            $(document).on('click', '.btn-edit-tms-mqtt-order', function() {
                var id = $(this).data('id');
                var order = null;
                for (var i = 0; i < tmsMqttOrdersCache.length; i++) {
                    if (tmsMqttOrdersCache[i].id_order_tms === id) {
                        order = tmsMqttOrdersCache[i];
                        break;
                    }
                }
                if (!order) {
                    alert('Order tidak ditemukan di riwayat. Muat ulang dulu.');
                    return;
                }
                tmsMqttEditingOrderId = order.id_order_tms;
                tmsMqttEditingOrder = order;
                $('#tms-mqtt-tab-order').tab('show');
            });
            $(document).on('click', '.btn-republish-tms-mqtt-order', function() {
                var id = $(this).data('id');
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: tmsMqttRepublishUrlTpl + id,
                    method: 'POST',
                    data: { _token: tmsCsrf },
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': tmsCsrf }
                }).done(function(res) {
                    var text = (res && res.pesan) || 'Selesai';
                    if (typeof swal === 'function') {
                        swal({ icon: (res && res.status) ? 'success' : 'error', title: 'Kirim ke alat', text: text });
                    } else {
                        alert(text);
                    }
                    if (res && res.mqtt) {
                        $('#tms-mqtt-inbox').html(renderMqttPublishInfo([res.mqtt])).show();
                    }
                }).fail(function(xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal mengirim ke alat');
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });
            $(document).on('click', '.btn-resend-tms-mqtt-result', function() {
                var id = $(this).data('id');
                var sampleId = $.trim(String($(this).attr('data-sample') || $(this).data('sample') || ''));
                var $btn = $(this);
                if (!sampleId) {
                    alert('Barcode / sample_id order kosong.');
                    return;
                }
                $btn.prop('disabled', true);
                $.ajax({
                    url: tmsMqttResendResultUrlTpl + id,
                    method: 'POST',
                    data: JSON.stringify({
                        _token: tmsCsrf,
                        sample_id: sampleId
                    }),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': tmsCsrf }
                }).done(function(res) {
                    var text = (res && res.pesan) || 'Selesai';
                    if (typeof swal === 'function') {
                        swal({ icon: (res && res.status) ? 'success' : 'info', title: 'Resend Hasil', text: text });
                    } else {
                        alert(text);
                    }
                    if (res && res.mqtt) {
                        $('#tms-mqtt-inbox').html(renderMqttPublishInfo([res.mqtt])).show();
                    }
                }).fail(function(xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal minta resend hasil');
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });
            $(document).on('click', '.btn-delete-tms-mqtt-order', function() {
                var id = $(this).data('id');
                var $btn = $(this);
                var doDelete = function() {
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: tmsDeleteOrderUrlTpl + id,
                        method: 'POST',
                        data: { _token: tmsCsrf },
                        dataType: 'json',
                        headers: { 'X-CSRF-TOKEN': tmsCsrf }
                    }).done(function(res) {
                        if (res && res.status) {
                            if (tmsMqttEditingOrderId === id) clearTmsMqttEditMode();
                            loadTmsMqttOrders();
                        } else {
                            alert((res && res.pesan) || 'Gagal menghapus order');
                        }
                    }).fail(function(xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal menghapus order');
                    }).always(function() {
                        $btn.prop('disabled', false);
                    });
                };
                if (typeof swal === 'function') {
                    swal({
                        title: 'Hapus order?',
                        text: 'Order akan dihapus dari riwayat TMS.',
                        icon: 'warning',
                        buttons: ['Batal', 'Hapus'],
                        dangerMode: true
                    }).then(function(ok) { if (ok) doDelete(); });
                } else if (confirm('Hapus order ini?')) {
                    doDelete();
                }
            });
            $(document).on('click', '.btn-isi-order-tms-mqtt', function() {
                var rows = rowsFromOrderCard($(this).closest('.card'));
                if (!rows.length) {
                    alert('Belum ada hasil pada order ini. Tarik hasil dari alat dulu.');
                    return;
                }
                var n = applyTmsToForm(rows);
                if (n > 0) {
                    $('#modalAmbilTmsMqtt').modal('hide');
                    if (typeof swal === 'function') {
                        swal({ icon: 'success', title: 'Berhasil', text: n + ' parameter diisi dari TMS. Jangan lupa Simpan Hasil.' });
                    } else {
                        alert(n + ' parameter diisi dari TMS.');
                    }
                } else {
                    alert('Tidak ada nilai yang berhasil diisi ke form.');
                }
            });
            $('#btn-isi-tms-mqtt').on('click', function() {
                var tab = $('#tmsMqttTabs .nav-link.active').attr('href') || '#tmsMqttPaneHasil';
                var rows = tmsMqttMatchedCache || [];
                if (tab === '#tmsMqttPaneRiwayat') {
                    var fromOrders = [];
                    $('#tms-mqtt-riwayat-body .card').each(function() {
                        fromOrders = fromOrders.concat(rowsFromOrderCard($(this)));
                    });
                    if (fromOrders.length) {
                        rows = fromOrders;
                    }
                }
                var n = applyTmsToForm(rows);
                if (n > 0) {
                    $('#modalAmbilTmsMqtt').modal('hide');
                    if (typeof swal === 'function') {
                        swal({ icon: 'success', title: 'Berhasil', text: n + ' parameter diisi dari TMS. Jangan lupa Simpan Hasil.' });
                    } else {
                        alert(n + ' parameter diisi dari TMS. Jangan lupa Simpan Hasil.');
                    }
                } else {
                    alert('Tidak ada nilai yang berhasil diisi ke form. Cari Sample ID dulu atau buka Riwayat Order.');
                }
            });

            $('.btn-simpan').on('click', function() {
                submitForm(false, false); // false = tidak perlu semua parameter terisi, false = simpan sementara
            });

            // Tombol Lanjutkan & Selesai (di dalam modal preview) → submit form
            $('#btn-preview-lanjut-selesai').on('click', function() {
                $('#modalPreviewHasil').modal('hide');
                submitForm(true, true);
            });
        });

        // ============================================================
        // MODAL REVIEW HASIL - Font Size & Kop Handler
        // ============================================================
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings-script')
        (function() {
            var saveFontsizeUrl = '{{ route('elits-permohonan-uji-klinik-2.save-fontsize-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}';
            var previewUrl     = '{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}?mode=preview';
            var csrfToken      = '{{ csrf_token() }}';

            var $slider        = $('#fontsize-slider');
            var $input         = $('#fontsize-input');
            var $preview       = $('#fontsize-preview-sample');
            var $lhSlider      = $('#lineheight-slider');
            var $lhInput       = $('#lineheight-input');
            var $lhPreview     = $('#lineheight-preview-sample');
            var $btnBuka       = $('#btn-buka-review');
            var $loadingIcon   = $('#review-loading-icon');
            var $saveIcon      = $('#review-save-icon');
            var $toggleKop     = $('#toggle-kop');
            var $kopLabel      = $('#kop-label-text');

            var originalFontsize   = parseFloat($slider.val()) || 12;
            var currentFontsize    = originalFontsize;
            var originalLineHeight = parseFloat($lhSlider.val()) || 1;
            var currentLineHeight  = originalLineHeight;
            var originalShowKop    = $toggleKop.is(':checked') ? 1 : 0;
            var currentShowKop     = originalShowKop;

            var marginSettings = initReviewHasilMarginSettings('', function() {
                $btnBuka.prop('disabled', false);
            });

            function updateFontsizeUI(val) {
                val = Math.min(20, Math.max(6, parseFloat(val) || 12));
                val = Math.round(val * 2) / 2; // step 0.5
                $slider.val(val);
                $input.val(val);
                $preview.css('font-size', val + 'pt');
                $btnBuka.prop('disabled', false);
                currentFontsize = val;
            }

            function updateLineHeightUI(val) {
                val = Math.min(3.0, Math.max(0.5, parseFloat(val) || 1));
                val = Math.round(val * 10) / 10; // step 0.1
                $lhSlider.val(val);
                $lhInput.val(val);
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

            // Sync fontsize slider → input
            $slider.on('input change', function() { updateFontsizeUI($(this).val()); });
            $input.on('input change', function()  { updateFontsizeUI($(this).val()); });
            $('#fontsize-minus').on('click', function() { updateFontsizeUI(currentFontsize - 0.5); });
            $('#fontsize-plus').on('click',  function() { updateFontsizeUI(currentFontsize + 0.5); });

            // Sync line-height slider → input
            $lhSlider.on('input change', function() { updateLineHeightUI($(this).val()); });
            $lhInput.on('input change',  function() { updateLineHeightUI($(this).val()); });
            $('#lineheight-minus').on('click', function() { updateLineHeightUI(currentLineHeight - 0.1); });
            $('#lineheight-plus').on('click',  function() { updateLineHeightUI(currentLineHeight + 0.1); });

            // Toggle kop
            $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });

            function openPreview(modeSelesai) {
                var url = previewUrl + '&t=' + Date.now();
                $('#preview-hasil-iframe').attr('src', url);
                $('#modalPreviewHasil').data('mode-selesai', modeSelesai);
                if (modeSelesai) {
                    $('#btn-preview-lanjut-selesai').removeClass('d-none');
                } else {
                    $('#btn-preview-lanjut-selesai').addClass('d-none');
                }
                $('#modalPreviewHasil').modal('show');
            }

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

            function saveSettingsThen(callback) {
                syncEditorsBeforePreviewSave();
                if (currentLineHeight < 0.5) {
                    currentLineHeight = 1;
                }
                if (currentFontsize < 6 || currentFontsize > 20) {
                    currentFontsize = Math.min(20, Math.max(6, parseFloat(originalFontsize) || 12));
                }
                var collected = collectTempHasil();
                var offsetCollected = collectTempOffset();
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
                        temp_offset_param: JSON.stringify(offsetCollected.tempOffsetParam),
                        temp_offset_sub : JSON.stringify(offsetCollected.tempOffsetSub),
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
                    error: function(xhr) {
                        var msg = 'Terjadi kesalahan saat menyimpan pengaturan.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.pesan) {
                                msg = xhr.responseJSON.pesan;
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).map(function(v) {
                                    return Array.isArray(v) ? v.join(', ') : v;
                                }).join('\n');
                            }
                        }
                        swal('Gagal', msg, 'error');
                    }
                });
            }

            function triggerDirectPreview(modeSelesai) {
                var $btnReview = $('.btn-review-hasil');
                var $btnSelesai = $('.btn-selesai');
                $btnReview.prop('disabled', true);
                $btnSelesai.prop('disabled', true);

                saveSettingsThen(function() {
                    openPreview(modeSelesai);
                }).always(function() {
                    $btnReview.prop('disabled', false);
                    $btnSelesai.prop('disabled', false);
                });
            }

            window.triggerDirectPreviewKlinik = triggerDirectPreview;

            // Reset saat modal pengaturan dibuka
            $('#modalReviewHasil').on('show.bs.modal', function() {
                var modeSelesai = $(this).data('mode-selesai') || false;
                $(this).find('.modal-title').html(
                    modeSelesai
                        ? '<i class="fa fa-check-circle mr-2"></i>Pengaturan Hasil — Selesai'
                        : '<i class="fa fa-cog mr-2"></i>Pengaturan Hasil'
                );
                $btnBuka.find('span.btn-label-text').text('Terapkan');
                updateFontsizeUI(originalFontsize);
                updateLineHeightUI(originalLineHeight);
                marginSettings.resetToOriginal();
                $toggleKop.prop('checked', originalShowKop === 1);
                updateKopUI(originalShowKop === 1);
                $btnBuka.prop('disabled', false);
            });

            // Setelah pengaturan ditutup, buka kembali preview jika dipanggil dari tombol Pengaturan Hasil
            $('#modalReviewHasil').on('hidden.bs.modal', function() {
                var reopen = $(this).data('reopen-preview') || false;
                var modeSelesai = $(this).data('mode-selesai') || false;
                $(this).data('mode-selesai', false);
                $(this).data('reopen-preview', false);
                if (reopen) {
                    openPreview(modeSelesai);
                }
            });

            // Helper: kumpulkan semua nilai hasil dari form (termasuk inline inputs yg belum disimpan)
            function collectTempHasil() {
                var tempHasil    = {};
                var tempSubHasil = {};

                // --- Main parameters ---
                $('textarea[name^="hasil_permohonan_uji_parameter_klinik"]').each(function() {
                    var $el      = $(this);
                    var nameAttr = $el.attr('name') || '';
                    var match    = nameAttr.match(/\[([^\]]+)\]$/);
                    if (!match) return;
                    var paramId = match[1];
                    var value   = $el.val() || '';

                    // Jika textarea kosong, coba ambil dari inline input / dropdown / TinyMCE
                    if (!value || value.trim() === '' || value === '-') {
                        var textareaId = $el.attr('id') || '';
                        var $row       = $el.closest('tr');
                        var $dropdown  = $row.find('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                        if ($dropdown.length > 0) {
                            value = $dropdown.val() || '';
                        }
                        if ((!value || value.trim() === '') && typeof tinymce !== 'undefined') {
                            var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                            if ($editor.length > 0) {
                                var edId = $editor.attr('id');
                                if (edId && tinymce.get(edId)) {
                                    try { value = tinymce.get(edId).getContent() || ''; } catch(e) {}
                                }
                            }
                        }
                    }
                    if (value && value.trim() !== '') {
                        tempHasil[paramId] = value;
                    }
                });

                // --- Sub parameters ---
                $('textarea[name^="hasil_permohonan_uji_sub_parameter_klinik"]').each(function() {
                    var $el      = $(this);
                    var nameAttr = $el.attr('name') || '';
                    // name="hasil_permohonan_uji_sub_parameter_klinik[param_id][sub_id]"
                    var match    = nameAttr.match(/\[([^\]]+)\]\[([^\]]+)\]$/);
                    if (!match) return;
                    var subId = match[2];
                    var value = $el.val() || '';

                    if (!value || value.trim() === '' || value === '-') {
                        var textareaId = $el.attr('id') || '';
                        var $row       = $el.closest('tr');
                        var $dropdown  = $row.find('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                        if ($dropdown.length > 0) {
                            value = $dropdown.val() || '';
                        }
                        if ((!value || value.trim() === '') && typeof tinymce !== 'undefined') {
                            var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                            if ($editor.length > 0) {
                                var edId = $editor.attr('id');
                                if (edId && tinymce.get(edId)) {
                                    try { value = tinymce.get(edId).getContent() || ''; } catch(e) {}
                                }
                            }
                        }
                    }
                    if (value && value.trim() !== '') {
                        tempSubHasil[subId] = value;
                    }
                });

                return { tempHasil: tempHasil, tempSubHasil: tempSubHasil };
            }

            function collectTempOffset() {
                var tempOffsetParam = {};
                var tempOffsetSub = {};

                $('input[name^="offset_baku_mutu_param"]').each(function() {
                    var match = ($(this).attr('name') || '').match(/\[([^\]]+)\]$/);
                    if (match) {
                        tempOffsetParam[match[1]] = $(this).val() || 'default';
                    }
                });

                $('input[name^="offset_baku_mutu_sub"]').each(function() {
                    var match = ($(this).attr('name') || '').match(/\[([^\]]+)\]\[([^\]]+)\]$/);
                    if (match) {
                        tempOffsetSub[match[2]] = $(this).val() || 'default';
                    }
                });

                return { tempOffsetParam: tempOffsetParam, tempOffsetSub: tempOffsetSub };
            }

            // Tombol Terapkan di modal pengaturan
            $btnBuka.on('click', function() {
                $btnBuka.prop('disabled', true);
                $loadingIcon.removeClass('d-none');
                $saveIcon.addClass('d-none');

                // Kumpulkan data hasil yang belum disimpan dari form
                if (currentLineHeight < 0.5) {
                    currentLineHeight = 1;
                }
                var collected = collectTempHasil();
                var offsetCollected = collectTempOffset();
                var tempMethod = (typeof window.collectTempMethod === 'function') ? window.collectTempMethod() : {};
                var marginValues = marginSettings.getValues();

                $.ajax({
                    url: saveFontsizeUrl,
                    method: 'POST',
                    data: {
                        _token        : csrfToken,
                        fontsize      : currentFontsize,
                        line_height   : currentLineHeight,
                        padding       : marginValues.padding,
                        padding_top   : marginValues.padding_top,
                        padding_bottom: marginValues.padding_bottom,
                        margin_left   : marginValues.margin_left,
                        margin_right  : marginValues.margin_right,
                        lebar_kolom_pemeriksaan: marginValues.lebar_kolom_pemeriksaan,
                        lebar_kolom_hasil: marginValues.lebar_kolom_hasil,
                        lebar_kolom_satuan: marginValues.lebar_kolom_satuan,
                        lebar_kolom_metode: marginValues.lebar_kolom_metode,
                        lebar_kolom_nilai_normal: marginValues.lebar_kolom_nilai_normal,
                        show_kop      : currentShowKop,
                        temp_hasil    : JSON.stringify(collected.tempHasil),
                        temp_sub_hasil: JSON.stringify(collected.tempSubHasil),
                        temp_offset_param: JSON.stringify(offsetCollected.tempOffsetParam),
                        temp_offset_sub : JSON.stringify(offsetCollected.tempOffsetSub),
                        temp_method   : JSON.stringify(tempMethod),
                        catatan_hasil   : $('#catatan_hasil').val() || '',
                        kesimpulan_hasil: $('#kesimpulan_hasil').val() || ''
                    },
                    success: function(response) {
                        if (response.status) {
                            originalFontsize   = currentFontsize;
                            originalLineHeight = currentLineHeight;
                            marginSettings.commitOriginal();
                            originalShowKop    = currentShowKop;
                            $('#modalReviewHasil').modal('hide');
                            // Buka preview dalam popup iframe fullscreen
                            var url = previewUrl + '&t=' + Date.now();
                            $('#preview-hasil-iframe').attr('src', url);
                            // Tampilkan / sembunyikan tombol "Lanjutkan & Selesai" di preview
                            var modeSelesai = $('#modalReviewHasil').data('mode-selesai') || false;
                            if (modeSelesai) {
                                $('#btn-preview-lanjut-selesai').removeClass('d-none');
                            } else {
                                $('#btn-preview-lanjut-selesai').addClass('d-none');
                            }
                            $('#modalPreviewHasil').modal('show');
                        } else {
                            swal('Gagal', response.pesan || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Terjadi kesalahan saat menyimpan pengaturan.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.pesan) {
                                msg = xhr.responseJSON.pesan;
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).map(function(v) {
                                    return Array.isArray(v) ? v.join(', ') : v;
                                }).join('\n');
                            }
                        }
                        swal('Gagal', msg, 'error');
                    },
                    complete: function() {
                        $btnBuka.prop('disabled', false);
                        $loadingIcon.addClass('d-none');
                        $saveIcon.removeClass('d-none');
                    }
                });
            });

            // Review Hasil & Selesai: tampilkan preview dulu (tanpa modal pengaturan)
            $('.btn-review-hasil').on('click', function() {
                triggerDirectPreview(false);
            });

            $('.btn-selesai').on('click', function() {
                $('#modalPreviewHasil').data('mode-selesai', true);
                triggerDirectPreview(true);
            });

            // Tombol Pengaturan Hasil di dalam preview
            $('#btn-pengaturan-preview').on('click', function() {
                var modeSelesai = $('#modalPreviewHasil').data('mode-selesai') || false;
                $('#modalReviewHasil').data('reopen-preview', true);
                $('#modalReviewHasil').data('mode-selesai', modeSelesai);
                $('#modalPreviewHasil').one('hidden.bs.modal', function() {
                    $('#modalReviewHasil').modal('show');
                });
                $('#modalPreviewHasil').modal('hide');
            });
        })();

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
                    <textarea id="editor_content" name="editor_content"></textarea>
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

    <script>

        // Parameter History Handlers
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
                    // Sync dropdown values (inline-hasil-input)
                    $('select.inline-hasil-input').each(function() {
                        var $select = $(this);
                        var selectedValue = $select.val() || '';
                        var textareaId = $select.data('textarea-id');
                        if (textareaId) {
                            $('#' + textareaId).val(selectedValue);
                        }
                    });
                    
                    // Sync TinyMCE inline editor values (hasil)
                    if (typeof tinymce !== 'undefined') {
                        $('.inline-hasil-editor').each(function() {
                            var $editor = $(this);
                            var textareaId = $editor.data('textarea-id');
                            if (textareaId) {
                                var editorId = $editor.attr('id');
                                try {
                                    if (editorId && tinymce.get(editorId)) {
                                        var content = tinymce.get(editorId).getContent();
                                        $('#' + textareaId).val(content);
                                    } else {
                                        // Fallback to contenteditable content
                                        var content = $editor.html() || '';
                                        $('#' + textareaId).val(content);
                                    }
                                } catch(e) {
                                    // Fallback to contenteditable content
                                    var content = $editor.html() || '';
                                    $('#' + textareaId).val(content);
                                }
                            }
                        });
                        
                        // Sync TinyMCE keterangan editor values
                        $('.inline-keterangan-editor').each(function() {
                            var $editor = $(this);
                            var textareaId = $editor.data('textarea-id');
                            if (textareaId) {
                                var editorId = $editor.attr('id');
                                try {
                                    if (editorId && tinymce.get(editorId)) {
                                        var content = tinymce.get(editorId).getContent();
                                        $('#' + textareaId).val(content);
                                    } else {
                                        // Fallback to contenteditable content
                                        var content = $editor.html() || '';
                                        $('#' + textareaId).val(content);
                                    }
                                } catch(e) {
                                    // Fallback to contenteditable content
                                    var content = $editor.html() || '';
                                    $('#' + textareaId).val(content);
                                }
                            }
                        });
                    }
                    
                    // Get current hasil value from textarea after sync
                    // Only hasil is saved to history, keterangan and method remain unchanged
                    // Find textarea in the same row as the button
                    var $row = $btn.closest('tr');
                    var currentHasil = '';
                    
                    // Find textarea for hasil in the same row (both sub and main parameter)
                    var $hasilTextarea = $row.find('textarea.result_method_klinik');
                    currentHasil = $hasilTextarea.length > 0 ? $hasilTextarea.val() : '';
                    
                    // Small delay to ensure sync is complete
                    setTimeout(function() {
                        $.ajax({
                            url: '{{ url("/elits-permohonan-uji-klinik-2/save-parameter-history") }}/' + parameterId,
                            type: 'POST',
                            // Keterangan dan method tidak dikirim, hanya hasil
                            data: {
                                _token: '{{ csrf_token() }}',
                                is_sub: isSub ? 1 : 0,
                                hasil: currentHasil
                            },
                        success: function(response) {
                            if (response.status) {
                                // Kosongkan field hasil dan beri indikator visual
                                var textareaId = $hasilTextarea.attr('id');
                                
                                // Kosongkan textarea
                                $hasilTextarea.val('');
                                
                                // Kosongkan dan beri indikator pada dropdown jika ada
                                var $dropdown = $row.find('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                                if ($dropdown.length > 0) {
                                    $dropdown.val('').addClass('needs-refill');
                                    // Tambahkan badge indikator
                                    if ($dropdown.next('.needs-refill-badge').length === 0) {
                                        $dropdown.after('<span class="needs-refill-badge" style="display: block; margin-top: 5px; padding: 4px 8px; background-color: #ff6b6b; color: white; border-radius: 4px; font-size: 11px; font-weight: 600;">⚠ Harap isi ulang hasil pemeriksaan</span>');
                                    }
                                    // Fokus ke dropdown
                                    setTimeout(function() {
                                        $dropdown.focus();
                                    }, 300);
                                }
                                
                                // Kosongkan dan beri indikator pada TinyMCE editor jika ada
                                var $editor = $row.find('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                                if ($editor.length > 0) {
                                    var editorId = $editor.attr('id');
                                    if (editorId && typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                                        tinymce.get(editorId).setContent('');
                                    } else {
                                        $editor.html('').addClass('needs-refill');
                                    }
                                    $editor.addClass('needs-refill')
                                        .attr('data-placeholder', '⚠ Harap isi ulang hasil pemeriksaan');
                                    // Tambahkan badge indikator
                                    if ($editor.next('.needs-refill-badge').length === 0) {
                                        $editor.after('<span class="needs-refill-badge" style="display: block; margin-top: 5px; padding: 4px 8px; background-color: #ff6b6b; color: white; border-radius: 4px; font-size: 11px; font-weight: 600;">⚠ Harap isi ulang hasil pemeriksaan</span>');
                                    }
                                    // Fokus ke editor
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
                                    
                                    // Update badge menjadi kosong
                                    var badgeId = 'badge_' + index[0];
                                    $('#' + badgeId).html('');
                                }
                                
                                // Tampilkan pesan sukses tanpa reload - biarkan user langsung mengisi
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
                    }, 100); // Small delay to ensure sync is complete
                }
            });
        });

        // Function to update baku mutu status
        function updateBakuMutuStatus(selectedOffset, index, isSub) {
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
                    
                    var min = $textarea.data('min') || '';
                    var max = $textarea.data('max') || '';
                    var equal = $textarea.data('equal') || '';
                    var numberFormat = $textarea.data('number-format') || 'en';
                    
                    setTimeout(function() {
                        var targetId = $textarea.attr('id');
                        var methodId = isSub ? ('sub_' + textareaIndex) : ('param_' + textareaIndex);
                        var $editorBtn = $row.find('.open-editor-modal').first();
                        if ($editorBtn.length && $editorBtn.data('method-id')) {
                            methodId = $editorBtn.data('method-id');
                        }

                        if (typeof window.updateResultPreview === 'function' && targetId) {
                            window.updateResultPreview(targetId, methodId);
                        } else if (typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.updateResultBadge === 'function') {
                            AnalisInlineEditor.updateResultBadge(textareaIndex, currentValue, min, max, equal, numberFormat);
                        }
                    }, 50);
                }
            }
        }
        
        // Handler for Baku Mutu Override button
        $(document).on('click', '.btn-baku-mutu-override', function() {
            var $btn = $(this);
            var index = $btn.data('index');
            var isSub = $btn.data('is-sub') == '1';
            
            // Find the row and get parameter name
            var $row = $btn.closest('tr');
            var parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
            
            // Get current offset directly from hidden input, not from button data attribute
            // This ensures we always get the latest value
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
            } else {
                // Fallback: try to find by name attribute
                if (isSub) {
                    $offsetInput = $row.find('input[name*="offset_baku_mutu_sub"]');
                } else {
                    $offsetInput = $row.find('input[name*="offset_baku_mutu_param"]');
                }
                if ($offsetInput.length > 0) {
                    currentOffset = String($offsetInput.val() || 'default').trim();
                }
            }
            
            // Also update button's data attribute to keep it in sync
            $btn.attr('data-current-offset', currentOffset);
            
            // Set parameter name in modal
            $('#bakuMutuParamName').text(parameterName);
            
            // Set current selection - ensure we normalize the value
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
                // Use setTimeout to ensure DOM is ready
                setTimeout(function() {
                    updateBakuMutuStatus(selectedOffset, index, isSub);
                }, 10);
            }
        });
        
        // Handler for saving baku mutu override (close modal)
        $('#baku-mutu-save-btn').on('click', function() {
            // Status already updated by radio button change event
            // Just close the modal
            $('#bakuMutuModal').modal('hide');
        });
        
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
                                // Render hasil as HTML if it contains HTML tags
                                var hasilDisplay = history.hasil || '-';
                                if (history.hasil && (history.hasil.includes('<') || history.hasil.includes('&'))) {
                                    hasilDisplay = history.hasil;
                                }
                                html += '<td>' + hasilDisplay + '</td>';
                                html += '<td>' + history.created_at + '</td>';
                                html += '<td>' + history.created_by + '</td>';
                                html += '<td>';
                                if (!history.is_selected) {
                                    // Store hasil in data attribute (use base64 encoding to preserve HTML)
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
                    var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat memuat history';
                    $('#historyModalBody').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                }
            });
        });

        $(document).on('click', '.btn-select-history', function() {
            var $btn = $(this);
            var historyId = $btn.data('history-id');
            var parameterId = $btn.data('parameter-id');
            var isSub = $btn.data('is-sub') == 1 || $btn.data('is-sub') == true;
            // Get hasil from data attribute (base64 encoded, decode it)
            var historyHasil = '';
            var hasilEncoded = $btn.attr('data-hasil-encoded') || '';
            if (hasilEncoded) {
                try {
                    historyHasil = decodeURIComponent(escape(atob(hasilEncoded)));
                } catch(e) {
                    console.error('Error decoding hasil:', e);
                    historyHasil = '';
                }
            }
            
            // Find textarea in the main table by parameter ID
            // Find the row in main table that contains this parameter
            var $hasilTextarea = null;
            var $mainRow = null;
            
            // Try to find row by parameter ID in hidden inputs
            if (isSub) {
                $mainRow = $('input[name*="parameter_sub_satuan_klinik_id"][value="' + parameterId + '"]').closest('tr');
                $hasilTextarea = $mainRow.find('textarea[name*="hasil_permohonan_uji_sub_parameter_klinik"]');
            } else {
                $mainRow = $('input[name*="permohonan_uji_parameter_klinik"][value="' + parameterId + '"]').closest('tr');
                $hasilTextarea = $mainRow.find('textarea[name*="hasil_permohonan_uji_parameter_klinik"]');
            }
            
            // If not found, try alternative methods
            if ($hasilTextarea.length === 0) {
                if (isSub) {
                    $hasilTextarea = $('textarea[name*="hasil_permohonan_uji_sub_parameter_klinik"]').filter(function() {
                        var name = $(this).attr('name') || '';
                        return name.includes(parameterId);
                    });
                } else {
                    $hasilTextarea = $('textarea[name*="hasil_permohonan_uji_parameter_klinik"]').filter(function() {
                        var name = $(this).attr('name') || '';
                        return name.includes(parameterId);
                    });
                }
            }
            
            // Sync all inline editor values to hidden textareas before saving
            // Sync dropdown values (inline-hasil-input)
            $('select.inline-hasil-input').each(function() {
                var $select = $(this);
                var selectedValue = $select.val() || '';
                var textareaId = $select.data('textarea-id');
                if (textareaId) {
                    $('#' + textareaId).val(selectedValue);
                }
            });
            
            // Sync TinyMCE inline editor values (hasil)
            if (typeof tinymce !== 'undefined') {
                $('.inline-hasil-editor').each(function() {
                    var $editor = $(this);
                    var textareaId = $editor.data('textarea-id');
                    if (textareaId) {
                        var editorId = $editor.attr('id');
                        try {
                            if (editorId && tinymce.get(editorId)) {
                                var content = tinymce.get(editorId).getContent();
                                $('#' + textareaId).val(content);
                            } else {
                                // Fallback to contenteditable content
                                var content = $editor.html() || '';
                                $('#' + textareaId).val(content);
                            }
                        } catch(e) {
                            // Fallback to contenteditable content
                            var content = $editor.html() || '';
                            $('#' + textareaId).val(content);
                        }
                    }
                });
            }
            
            // Get current hasil value from textarea (after sync)
            var currentHasil = $hasilTextarea.length > 0 ? $hasilTextarea.val() : '';
            
            // Function to select history after saving current value (if exists)
            var selectHistory = function() {
                $.ajax({
                    url: '{{ url("/elits-permohonan-uji-klinik-2/select-parameter-history") }}/' + parameterId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        history_id: historyId,
                        is_sub: isSub ? 1 : 0
                    },
                    success: function(response) {
                    if (response.status) {
                        // Convert history hasil to HTML format (convert ^(1) to <sup>1</sup>)
                        var convertedHasil = historyHasil;
                        if (historyHasil && typeof window.convertToTinyMCE === 'function') {
                            convertedHasil = window.convertToTinyMCE(historyHasil);
                        } else if (historyHasil && typeof AnalisInlineEditor !== 'undefined' && typeof AnalisInlineEditor.convertSuperscriptToHtml === 'function') {
                            convertedHasil = AnalisInlineEditor.convertSuperscriptToHtml(historyHasil);
                        }
                        
                        // Fill hasil to textarea (save converted HTML format)
                        if ($hasilTextarea.length > 0 && convertedHasil) {
                            $hasilTextarea.val(convertedHasil).trigger('change');
                            
                            // Update inline editor if exists
                            var textareaId = $hasilTextarea.attr('id');
                            if (textareaId) {
                                // Update dropdown if exists
                                var $dropdown = $('select.inline-hasil-input[data-textarea-id="' + textareaId + '"]');
                                if ($dropdown.length > 0) {
                                    // For dropdown, use original value (not converted)
                                    $dropdown.val(historyHasil).trigger('change');
                                }
                                
                                // Update TinyMCE editor if exists (use converted HTML format)
                                var $editor = $('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                                if ($editor.length > 0) {
                                    var editorId = $editor.attr('id');
                                    if (editorId && typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                                        tinymce.get(editorId).setContent(convertedHasil);
                                    } else {
                                        $editor.html(convertedHasil);
                                    }
                                }
                                
                                // Update result display (use converted HTML format for display)
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
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan ? xhr.responseJSON.pesan : 'Terjadi kesalahan saat memilih history';
                        swal('Error!', errorMsg, 'error');
                    }
                });
            };
            
            // If current hasil exists, save it to history first
            if (currentHasil && currentHasil.trim() !== '') {
                // Save current hasil to history
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
                            // After saving current value, select the chosen history
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
                // No current value, directly select history
                selectHistory();
            }
        });
    </script>

    <script>
        // ===== Loading Overlay Control =====
        (function() {
            var $overlay = $('#page-loading-overlay');

            function hideOverlay() {
                $overlay.addClass('fade-out');
                setTimeout(function() {
                    $overlay.remove();
                    // Hapus pemblokir keyboard setelah overlay hilang
                    $(document).off('keydown.loadingBlock');
                }, 450);
            }

            // Blokir semua input keyboard selama overlay tampil
            $(document).on('keydown.loadingBlock', function(e) {
                // Hanya blokir jika overlay masih ada dan belum fade-out
                if ($overlay.length && !$overlay.hasClass('fade-out')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });

            // Jika editor sudah siap sebelum event listener ini terpasang (race condition safeguard)
            if (window.analisEditorReady === true) {
                hideOverlay();
                return;
            }

            // Dengarkan event dari AnalisInlineEditor saat inisialisasi selesai
            $(document).on('analisEditorReady', function() {
                // Beri jeda kecil agar TinyMCE benar-benar siap render
                setTimeout(hideOverlay, 300);
            });

            // Fallback: jika setelah 15 detik editor belum ready, paksa sembunyikan overlay
            setTimeout(function() {
                if ($overlay.length && !$overlay.hasClass('fade-out')) {
                    console.warn('Loading overlay: timeout reached, forcing hide.');
                    hideOverlay();
                }
            }, 15000);
        })();
    </script>

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
@endsection
