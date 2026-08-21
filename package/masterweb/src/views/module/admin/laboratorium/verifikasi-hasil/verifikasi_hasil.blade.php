@extends('masterweb::template.admin.layout')
@section('title')
    Verifikasi Hasil
@endsection

@section('content')
    <style>
        /* Custom Checkbox Styling */
        .custom-control.custom-checkbox {
            position: relative;
            min-height: 1.5rem;
            padding-left: 0;
        }

        .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #28a745;
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .custom-control.custom-checkbox .custom-control-input:not(:checked)~.custom-control-label::before {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::after {
            animation: checkPop 0.3s ease;
        }

        @keyframes checkPop {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Radio Button Styling */
        .form-check {
            padding-left: 0;
        }

        .form-check-input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
            padding: 0;
        }

        .form-check-label {
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check-input[type="radio"]:checked+.form-check-label {
            transform: scale(1.05);
        }

        /* Table styling */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .thead-light th {
            background-color: #e9ecef;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        /* Badge styling */
        .badge {
            font-weight: 600;
        }

        /* Tooltip */
        .tooltip-inner {
            max-width: 300px;
            text-align: left;
        }

        /* Make TinyMCE/textarea responsive full width */
        textarea#lokasi_pengambilan,
        textarea#lokasi_pengambilan_kimia {
            width: 100% !important;
        }

        .tox.tox-tinymce {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Ensure edit area is comfortably tall */
        .tox .tox-edit-area iframe {
            min-height: 200px !important;
        }

        /* Inline editing styles - same as baca-hasil page */
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
        tr:has(.inline-keterangan-editor:focus) {
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
        
        .hasil-input-container .tox.tox-tinymce,
        .hasil-input-container .tox.tox-tinymce-inline {
            width: 100%;
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
            .sample-data-compact-content { gap: 10px; font-size: 12px; }
            .sample-data-compact-item { font-size: 11px; }
            .sample-data-compact-item strong { display: none; }
            .sample-data-compact-actions .btn { padding: 3px 8px; font-size: 11px; }
        }
        /* ===== End Sticky Sample Info ===== */
    </style>





    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">


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

                                <li class="breadcrumb-item active" aria-current="page"><span>Verifikasi
                                        Hasil</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fa fa-check-double mr-2"></i>
            <h4 class="mb-0">Verifikasi Hasil</h4>
        </div>
        <div class="card-body" style="background-color: #f8f9fa;">
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
                            <div class="card border-0 mb-3" style="background-color: #ffffff;">
                                <div class="card-body">
                                    <h5 class="card-title text-info mb-3"><i class="fa fa-info-circle mr-2"></i>Informasi
                                        Sampel</h5>
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <th style="vertical-align: top"><b>Nama Pelanggan</b></th>
                                            <td style="vertical-align: top">
                                                @php
                                                    $customer = str_replace(
                                                        // Hanya mencari simbol 'Π'
                                                        'π',
                                                        '<span style="font-family: \'DejaVu Sans\', sans-serif;">π</span>', // Ganti dengan <span> yang sesuai
                                                        $sample->name_pelanggan ??
                                                            $sample->namaPelangganDisplay(),
                                                    );
                                                @endphp
                                                {{ $customer }}
                                            </td>
                                            <th class="text-muted"><b><i class="fa fa-calendar mr-2"></i>Tanggal
                                                    Pengambilan</b></th>
                                            <td>
                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}
                                            </td>
                                            <th class="text-muted"><b><i class="fa fa-flask mr-2"></i>Jenis Sampel</b></th>
                                                    <td>{{ $sample->jenisSampelDisplay() }}</td>
                                        </tr>
                                        <tr>
                                                    <th class="text-muted"><b><i class="fa fa-barcode mr-2"></i>Nomor Sampel</b></th>
                                            <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</td>
                                            <th class="text-muted"><b><i class="fa fa-calendar-check mr-2"></i>Tanggal
                                                    Pengiriman</b></th>
                                            <td colspan="3">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><b>Laboratorium</b></th>
                                            <td>{{ $sample->nama_laboratorium }}</td>
                                            <th colspan="4"></th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                                </div><!-- end .sample-data-full -->

                            </div><!-- end .sample-data-sticky-wrapper -->
                            <div class="sample-data-spacer"></div>
                            <br>

                            {{-- Note sample dengan kondisi --}}
                            @if ($sample->note_samples !== null)
                                <div class="alert alert-warning border-left-warning shadow-sm"
                                    style="border-left: 5px solid #ffc107;">
                                    <i class="fa fa-exclamation-triangle mr-2"></i>
                                    <strong>Catatan:</strong> {{ $sample->note_samples }}
                                </div>
                            @endif



                            @if ($sample->is_pudam == 1)
                                <br>
                                <h5>Nama Pengirim : </h5> {{ $sample->name_customer_pdam }}
                                <br>
                                <br>
                                <h5>Alamat Pengirim : </h5> {!! $sample->address_location_pdam !!}
                                <br>
                            @endif

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

                            <br>
                            <br>
                            <br>
                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="col-md-12">


                                            <form
                                                action="{{ route('elits-verifikasi-hasil.store', [Request::segment(2), Request::segment(3)]) }}"
                                                method="POST">
                                                @csrf

                                                <!-- Form Verifikasi Hasil - Dipindahkan ke atas -->
                                                <div class="card border-0 mb-4" style="background-color: #e3f2fd;">
                                                    <div class="card-header bg-primary text-white">
                                                        <h5 class="card-title mb-0">
                                                            <i class="fa fa-check-circle mr-2"></i>Verifikasi Hasil
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="start_date_verifikasi_hasil">
                                                                        <strong>Start Date <span class="text-danger">*</span></strong>
                                                                    </label>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           name="start_date_verifikasi_hasil" 
                                                                           id="start_date_verifikasi_hasil" 
                                                                           placeholder="dd/mm/yyyy" 
                                                                           value="{{ $default_start_date_verifikasi ? $default_start_date_verifikasi->format('d/m/Y') : '' }}"
                                                                           required>
                                                                    <small class="form-text text-muted">
                                                                        Format: dd/mm/yyyy (contoh: 08/01/2026)
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="stop_date_verifikasi_hasil">
                                                                        <strong>Stop Date <span class="text-danger">*</span></strong>
                                                                    </label>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           name="stop_date_verifikasi_hasil" 
                                                                           id="stop_date_verifikasi_hasil" 
                                                                           placeholder="dd/mm/yyyy" 
                                                                           value="{{ $default_stop_date_verifikasi ? $default_stop_date_verifikasi->format('d/m/Y') : '' }}"
                                                                           required>
                                                                    <small class="form-text text-muted">
                                                                        Format: dd/mm/yyyy (contoh: 10/01/2026)
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="nama_petugas_verifikasi_hasil">
                                                                        <strong>Nama Petugas <span class="text-danger">*</span></strong>
                                                                    </label>
                                                                    <select name="nama_petugas_verifikasi_hasil" 
                                                                            id="nama_petugas_verifikasi_hasil" 
                                                                            class="form-control" 
                                                                            required>
                                                                        <option value="">-- Pilih Nama Petugas --</option>
                                                                        @foreach ($analis_list as $analis)
                                                                            <option value="{{ $analis }}" 
                                                                                    {{ $default_analis == $analis ? 'selected' : '' }}>
                                                                                {{ $analis }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Hidden inputs for verification data -->
                                                <input type="hidden" name="verification_step_verifikasi_hasil" id="verification_step_verifikasi_hasil" value="4">
                                                <input type="hidden" name="start_date_verifikasi_hasil_hidden" id="start_date_verifikasi_hasil_hidden">
                                                <input type="hidden" name="stop_date_verifikasi_hasil_hidden" id="stop_date_verifikasi_hasil_hidden">
                                                <input type="hidden" name="nama_petugas_verifikasi_hasil_hidden" id="nama_petugas_verifikasi_hasil_hidden">
                                                <input type="hidden" name="id_laboratorium_verifikasi_hasil_hidden" id="id_laboratorium_verifikasi_hasil_hidden" value="{{ $idlab }}">

                                                <!-- Informasi Lokasi dan Pengujian -->
                                                <div class="card border-0 mb-4" style="background-color: #f1f8e9;">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-success mb-4"><i
                                                                class="fa fa-edit mr-2"></i>Informasi Lokasi dan Pengujian
                                                        </h5>
                                                        <div class="form-group">
                                                            {{-- ### Nama Pengambil ### --}}
                                                            <div class="form-group">
                                                                <label for="nama_pengambil" class="font-weight-bold">
                                                                    <i class="fa fa-user-tie mr-2 text-primary"></i>Nama
                                                                    Pengambil:
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
                                                                            $defaultNamaPengambil =
                                                                                'Petugas ' . $customerName;
                                                                        }
                                                                    }
                                                                @endphp
                                                                <input type="text" class="form-control shadow-sm"
                                                                    id="nama_pengambil" name="nama_pengambil"
                                                                    value="{{ old('nama_pengambil', $defaultNamaPengambil) }}"
                                                                    placeholder="Masukkan nama pengambil..." required>
                                                            </div>

                                                            {{-- ### Titik Sampel (TinyMCE untuk mikro air)
                                                                 Disembunyikan jika sample type = Makanan/Minuman/Lainnya --}}
                                                            @if (!($sample->kode_laboratorium == 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya'))
                                                                <div class="form-group">
                                                                    <label for="titik_pengambilan" class="font-weight-bold">
                                                                        <i class="fa fa-map-pin mr-2 text-info"></i>Titik
                                                                        Sampel:
                                                                    </label>
                                                                    <textarea class="form-control shadow-sm" id="titik_pengambilan" name="titik_pengambilan" rows="2"
                                                                        placeholder="Masukkan titik sampel...">{{ $sample->titik_pengambilan ?? old('titik_pengambilan') }}</textarea>
                                                                </div>
                                                            @endif

                                                            <div class="form-group mt-2">
                                                                {{-- ### Asal Sampel (TinyMCE) ### --}}
                                                                @if ($sample->kode_laboratorium == 'MBI')
                                                                    <label for="lokasi_pengambilan"
                                                                        class="font-weight-bold">
                                                                        <i
                                                                            class="fa fa-map-marker-alt mr-2 text-danger"></i>
                                                                        Asal Sampel:
                                                                    </label>
                                                                    @php
                                                                        // Default Asal Sampel untuk mikro:
                                                                        // name_customer + alamat (detail_alamat_sampling / alamat_lengkap_sampling / address_customer)
                                                                        $asal_sampel_value = old('lokasi_pengambilan');
                                                                        if (empty($asal_sampel_value)) {
                                                                            $perm = $sample->permohonanuji ?? null;
                                                                            $cust = $perm->customer ?? null;
                                                                            $namaCust = $cust->name_customer ?? '';
                                                                            $alamat =
                                                                                $perm->detail_alamat_sampling ??
                                                                                ($perm->alamat_lengkap_sampling ??
                                                                                    ($cust->address_customer ?? ''));
                                                                            $asal_sampel_value = trim($alamat);
                                                                        }
                                                                    @endphp
                                                                    <textarea class="form-control" id="lokasi_pengambilan" name="lokasi_pengambilan" rows="3">{!! $asal_sampel_value !!}</textarea>
                                                                @else
                                                                    <label for="lokasi_pengambilan_kimia"
                                                                        class="font-weight-bold">
                                                                        <b>Asal Contoh Air/ Lokasi Sampel:</b>
                                                                    </label>
                                                                    @if (isset($sample->location_samples) && $sample->location_samples != '')
                                                                        <div class="input-group date">
                                                                            <textarea class="form-control" id="lokasi_pengambilan_kimia" name="lokasi_pengambilan" rows="3">{!! $sample->location_samples !!}</textarea>
                                                                        </div>
                                                                    @else
                                                                        @php
                                                                            if ($sample->is_pudam) {
                                                                                $location =
                                                                                    $sample->name_customer_pdam ??
                                                                                    old('name_customer_pdam');
                                                                            } else {
                                                                                $location =
                                                                                    $sample->titik_pengambilan ??
                                                                                    old('titik_pengambilan');
                                                                            }
                                                                        @endphp
                                                                        <div class="input-group date">
                                                                            <textarea class="form-control" id="lokasi_pengambilan_kimia" name="lokasi_pengambilan" rows="3">{!! $location !!}</textarea>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            {{-- ### Jenis Sarana ### --}}
                                                            @php
                                                                $isKualitasUdaraForJenisSarana = isset($sample->name_sample_type) && 
                                                                    stripos($sample->name_sample_type, 'udara') !== false;
                                                            @endphp
                                                            @if (!$isKualitasUdaraForJenisSarana)
                                                                @if ($sample->kode_laboratorium == 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya')
                                                                    {{-- Untuk sampel makanan MBI: Jenis Sarana disembunyikan dan nilainya otomatis mengikuti jenis makanan --}}
                                                                    <input type="hidden" name="jenis_sarana"
                                                                        id="jenis_sarana"
                                                                        value="{{ old('jenis_sarana', $sample->nama_jenis_makanan ?? ($sample->jenis_sarana_names ?? '')) }}">
                                                                @elseif ($sample->kode_laboratorium == 'MBI')
                                                                    <div class="form-group">
                                                                        <label for="input_jenis_sarana">
                                                                            Jenis Sarana:
                                                                        </label>
                                                                        @isset($jenis_sarana_options)
                                                                            <select id="input_jenis_sarana" name="jenis_sarana"
                                                                                class="js-customer-basic-multiple js-states form-control"
                                                                                style="width: 100%">
                                                                                <option value=""
                                                                                    @selected(empty(old('jenis_sarana')))> Pilih
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
                                                                                class="form-control"
                                                                                style="width: 100%; margin-top: 1em"
                                                                                placeholder="Masukkan jenis sarana..." disabled
                                                                                hidden>
                                                                        @else
                                                                            <input type="text" class="form-control"
                                                                                name="jenis_sarana" id="jenis_sarana"
                                                                                placeholder="Jenis Sarana"
                                                                                value="{{ old('jenis_sarana', $sample->jenis_sarana_names!='' && $sample->jenis_sarana_names!=null ? $sample->jenis_sarana_names : ($sample->name_sample_type ?? '')) }}">
                                                                        @endisset
                                                                    </div>
                                                                @endif
                                                            @endif

                                                            {{-- ### Jenis Makanan (MBI & KIM) + Jenis Sampel (MBI makanan) --}}
                                                            @php
                                                                $showJenisMakananPicker = false;
                                                                $labCode = $sample->kode_laboratorium ?? '';
                                                                
                                                                // Untuk MBI: tampilkan jika ada jenis makanan
                                                                if ($labCode === 'MBI' && 
                                                                    isset($sample->name_sample_type) && 
                                                                    $sample->name_sample_type === 'Makanan/Minuman/Lainnya' && 
                                                                    isset($jenisMakananAll) && 
                                                                    $jenisMakananAll->count() > 0) {
                                                                    $showJenisMakananPicker = true;
                                                                }
                                                                
                                                                // Untuk KIM: tampilkan jika ada lebih dari satu jenis makanan atau ada baku mutu tanpa jenis makanan
                                                                if ($labCode === 'KIM' && 
                                                                    isset($sample->name_sample_type) && 
                                                                    $sample->name_sample_type === 'Makanan/Minuman/Lainnya' && 
                                                                    isset($jenisMakananAll)) {
                                                                    $hasMultipleJenisMakanan = $jenisMakananAll->count() > 1;
                                                                    $hasWithoutJenisMakanan = isset($hasBakuMutuWithoutJenisMakanan) && $hasBakuMutuWithoutJenisMakanan;
                                                                    
                                                                    if (($hasMultipleJenisMakanan || $hasWithoutJenisMakanan) && 
                                                                        ($jenisMakananAll->count() > 0 || $hasWithoutJenisMakanan)) {
                                                                        $showJenisMakananPicker = true;
                                                                    }
                                                                }
                                                                
                                                                $selectedJenisMakananId = $jenis_makanan_id ?? ($sample->jenis_makanan_id ?? null);
                                                                $kimGenericJenisOption = $labCode === 'KIM' && !empty($hasBakuMutuWithoutJenisMakanan) && $hasBakuMutuWithoutJenisMakanan;
                                                            @endphp
                                                            
                                                            @if ($showJenisMakananPicker)
                                                                <div class="form-group">
                                                                    <label for="jenis_makanan_id"
                                                                        class="font-weight-bold">
                                                                        <i class="fa fa-utensils mr-2"></i>Jenis Makanan
                                                                        @if ($labCode === 'KIM')
                                                                            (KIM - Opsional)
                                                                        @else
                                                                            (MBI)
                                                                        @endif
                                                                    </label>
                                                                    <select id="jenis_makanan_id" name="jenis_makanan_id"
                                                                        class="form-control">
                                                                        <option value="" {{ $selectedJenisMakananId === null || $selectedJenisMakananId === '' ? 'selected' : '' }}>
                                                                            @if ($kimGenericJenisOption)
                                                                                Tanpa jenis makanan / semua makanan sama (baku mutu generik, contoh: Borax)
                                                                            @else
                                                                                — Pilih jenis makanan —
                                                                            @endif
                                                                        </option>
                                                                        @foreach ($jenisMakananAll as $jm)
                                                                            <option value="{{ $jm->id_jenis_makanan }}"
                                                                                {{ $selectedJenisMakananId == $jm->id_jenis_makanan ? 'selected' : '' }}>
                                                                                {{ $jm->name_jenis_makanan }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <small class="text-muted">Jenis makanan ini akan
                                                                        digunakan untuk penentuan baku mutu dan tampil di
                                                                        laporan.</small>
                                                                    @if ($kimGenericJenisOption)
                                                                        <small class="text-muted d-block mt-1">Opsi pertama untuk parameter yang berlaku umum di master tanpa jenis makanan tertentu.</small>
                                                                    @endif
                                                                </div>

                                                                {{-- Jenis Sampel (input text) disimpan ke Sample.nama_jenis_makanan --}}
                                                                <div class="form-group">
                                                                    <label class="font-weight-bold"
                                                                        for="nama_jenis_makanan">
                                                                        <i class="fa fa-tag mr-2"></i>Jenis Sampel
                                                                    </label>
                                                                    @php
                                                                        // Default Jenis Sampel:
                                                                        // 1. Jika sudah pernah disimpan, gunakan nama_jenis_makanan
                                                                        // 2. Jika belum, gunakan titik_pengambilan (fallback)
                                                                        if (
                                                                            isset($sample->nama_jenis_makanan) &&
                                                                            $sample->nama_jenis_makanan !== ''
                                                                        ) {
                                                                            $defaultNamaJenis = $sample->namaJenisMakananPlain();
                                                                        } else {
                                                                            $defaultNamaJenis =
                                                                                $sample->titik_pengambilan;
                                                                        }
                                                                    @endphp
                                                                    <input type="text" id="nama_jenis_makanan"
                                                                        name="nama_jenis_makanan" class="form-control"
                                                                        value="{{ old('nama_jenis_makanan', $defaultNamaJenis) }}"
                                                                        placeholder="Contoh: Lemper, Nasi Uduk, dll">
                                                                    <small class="text-muted">
                                                                        Nilai ini akan disimpan ke kolom
                                                                        <code>nama_jenis_makanan</code> pada data sampel
                                                                        dan tampil sebagai Jenis Sampel di laporan.
                                                                    </small>
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
                                                                    <select id="pilih_ruangan" name="pilih_ruangan" class="form-control shadow-sm" style="width: 100%">
                                                                        <option value="">-- Pilih Ruangan/Lokasi --</option>
                                                                        @foreach ($uniqueLokasi as $lokasiNama)
                                                                            <option value="{{ $lokasiNama }}" {{ old('pilih_ruangan', $selectedRuanganFromResult) == $lokasiNama ? 'selected' : '' }}>
                                                                                {{ $lokasiNama }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="hidden" name="selected_ruangan" id="selected_ruangan_hidden" value="{{ old('pilih_ruangan', $selectedRuanganFromResult) }}">
                                                                    <small class="text-muted">
                                                                        <i class="fa fa-info-circle mr-1"></i>
                                                                        Pilih ruangan/lokasi untuk menentukan baku mutu yang akan digunakan untuk setiap parameter.
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>



                                                <div class="form-group">
                                                    <h5 class="mb-3"><i class="fa fa-table mr-2 text-primary"></i>Tabel
                                                        Hasil Pengujian</h5>
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
                                                                $paramIndex = 0; // Counter untuk data-index yang urut
                                                            @endphp
                                                            @foreach ($laboratoriummethods as $laboratoriummethod)
                                                                @if (count($laboratoriummethod['detail']) == 0)
                                                                    @php
                                                                        $paramIndex++; // Increment untuk parameter utama
                                                                    @endphp
                                                                    @if (isset($laboratoriummethod->name_report))
                                                                        <tr>
                                                                            <td class="text-center align-middle">{{ $no }}</td>
                                                                            <td class="align-middle">
                                                                                <div>
                                                                                    <b>{!! rubahNilaikeHtml($laboratoriummethod->name_report) !!}</b>
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
                                                                            </td>
                                                                            <td>{!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}</td>
                                                                            <td>
                                                                                <span
                                                                                    class="not_show_{{ $laboratoriummethod->method_id }}"
                                                                                    style="display: none;">-</span>
                                                                                <div
                                                                                    class="show_{{ $laboratoriummethod->method_id }}">
                                                                                    <div>
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
                                                                                            
                                                                                            // Check if this is an option-based parameter
                                                                                            $isOption = isset($laboratoriummethod->method_is_option) && $laboratoriummethod->method_is_option == 1;
                                                                                            $optionValues = [];
                                                                                            if ($isOption && !empty($laboratoriummethod->method_option)) {
                                                                                                $optionValues = array_map('trim', explode(',', $laboratoriummethod->method_option));
                                                                                            }

                                                                                            $__nilaiBmPlain = '';
                                                                                            if (!empty($laboratoriummethod->nilai_baku_mutu)) {
                                                                                                $__nilaiBmPlain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->nilai_baku_mutu), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                            }
                                                                                            if ($__nilaiBmPlain === '' && !empty($laboratoriummethod->equal) && preg_match('/[<>≤≥]/u', (string) $laboratoriummethod->equal)) {
                                                                                                $__nilaiBmPlain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->equal), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                            }
                                                                                        @endphp
                                                                                        <!-- Hidden input untuk offset baku mutu -->
                                                                                        <input type="hidden"
                                                                                            name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                                                                            id="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                                                                            value="{{ isset($laboratoriummethod->offset_baku_mutu) ? $laboratoriummethod->offset_baku_mutu : 'default' }}">
                                                                                        
                                                                                        <textarea class="form-control result_method result_method_klinik result_method_{{ $laboratoriummethod->method_id }}"
                                                                                            id="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                            name="result_method_{{ $laboratoriummethod->method_id }}" 
                                                                                            data-index="{{ $paramIndex }}"
                                                                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                            data-lokasi-data='{{ $lokasiDataJson }}'
                                                                                            data-min="{{ $laboratoriummethod->min }}"
                                                                                            data-max="{{ $laboratoriummethod->max }}" 
                                                                                            data-equal="{{ $laboratoriummethod->equal }}" 
                                                                                            data-nilai-baku-mutu="{{ e($__nilaiBmPlain) }}"
                                                                                            data-number-format="en"
                                                                                            data-is-option="{{ $isOption ? '1' : '0' }}"
                                                                                            data-option-values="{{ $isOption ? json_encode($optionValues) : '[]' }}"
                                                                                            placeholder="Hasil"
                                                                                            required style="display: none;">{{ isset($laboratoriummethod->hasil) ? rubahNilaikeForm($laboratoriummethod->hasil) : (isset($laboratoriummethod->equal) ? rubahNilaikeForm($laboratoriummethod->equal) : '') }}</textarea>
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
                                                                                                !empty($optionValue)
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
                                                                                                empty($currentResult) &&
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
                                                                                        @if ($isOption && count($options) > 0)
                                                                                            <!-- Mode opsi: hanya gunakan popup editor dengan pilihan -->
                                                                                            <button type="button"
                                                                                                class="btn btn-sm btn-primary open-editor-modal"
                                                                                                data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                                data-method-name="{{ $laboratoriummethod->name_method }}"
                                                                                                data-is-option="1"
                                                                                                data-options='@json($options)'
                                                                                                data-current-value="{{ $currentResult }}">
                                                                                                <i class="fa fa-edit mr-1"></i>
                                                                                                Pilih / Edit Hasil
                                                                                            </button>
                                                                                        @else
                                                                                            <!-- TinyMCE Editor untuk is_option = 0 -->
                                                                                            <button type="button"
                                                                                                class="btn btn-sm btn-primary open-editor-modal"
                                                                                                data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                                                                                data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                                data-method-name="{{ $laboratoriummethod->name_method }}">
                                                                                                <i
                                                                                                    class="fa fa-edit mr-1"></i>
                                                                                                Edit dengan Editor
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <textarea class="form-control metode-editor" id="metode_{{ $laboratoriummethod->method_id }}" name="metode_{{ $laboratoriummethod->method_id }}">{{ isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method }}</textarea>
                                                                                <br><br>
                                                                            </td>
                                                                            <td class="align-middle">
                                                                                @php
                                                                                    $__ktStored = $laboratoriummethod->keterangan ?? '';
                                                                                    $__ktDefault = trim($laboratoriummethod->keterangan_default ?? '');
                                                                                    $__keteranganTampil = $__ktStored !== '' ? $__ktStored : $__ktDefault;
                                                                                @endphp
                                                                                <div style="position: relative;">
                                                                                    <!-- Hidden textarea for form submission -->
                                                                                    <textarea class="form-control" id="keterangan_{{ $laboratoriummethod->method_id }}"
                                                                                        name="keterangan_{{ $laboratoriummethod->method_id }}" placeholder="Masukkan keterangan..."
                                                                                        style="display: none;">{{ $__keteranganTampil }}</textarea>

                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-primary open-editor-modal"
                                                                                        data-target="keterangan_{{ $laboratoriummethod->method_id }}"
                                                                                        data-method-id="{{ $laboratoriummethod->method_id }}"
                                                                                        data-method-name="Keterangan - {{ $laboratoriummethod->name_method }}">
                                                                                        <i class="fa fa-edit mr-1"></i>
                                                                                        Edit Keterangan
                                                                                    </button>
                                                                                </div>
                                                                                <br><br>
                                                                            </td>
                                                                            <td class="text-center align-middle">
                                                                                <!-- Tombol Baku Mutu akan ditambahkan oleh analis-inline-editing.js -->
                                                                            </td>
                                                                        </tr>
                                                                    @else
                                                                        @php
                                                                            $tidak_simpan = true;
                                                                        @endphp
                                                                        <tr
                                                                            style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                            <td>{{ $no }}</td>
                                                                            @php
                                                                                $jenis_makanan = $sample->jenis_makanan;
                                                                                if (isset($jenis_makanan)) {
                                                                                    $jenis_makanan =
                                                                                        $jenis_makanan->name_jenis_makanan;
                                                                                }
                                                                            @endphp
                                                                            <td colspan="7">
                                                                                Baku mutu untuk parameter
                                                                                <b>{{ $laboratoriummethod->params_method }}</b>,
                                                                                untuk
                                                                                jenis sarana
                                                                                <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }}</b></u>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @else
                                                                    @php
                                                                        $paramIndex++; // Increment untuk parameter parent yang punya detail
                                                                    @endphp
                                                                    @if (isset($laboratoriummethod->name_report))
                                                                        <tr>
                                                                            <td style="vertical-align:top"
                                                                                rowspan="{{ count($laboratoriummethod['detail']) + 1 }}"
                                                                                class="text-center align-middle">
                                                                                {{ $no }}</td>
                                                                            <td colspan="6" class="align-middle">
                                                                                <b>{!! rubahNilaikeHtml($laboratoriummethod->name_report) !!}</b>
                                                                            </td>
                                                                        </tr>
                                                                        @foreach ($laboratoriummethod['detail'] as $detail)
                                                                            @php
                                                                                $paramIndex++; // Increment untuk setiap detail parameter
                                                                            @endphp
                                                                            <tr>
                                                                                <td class="align-middle">
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
                                                                                <td>{!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}</td>
                                                                                <td>
                                                                                    <span
                                                                                        class="not_show_{{ $detail->id_sample_result_detail }}"
                                                                                        style="display: none;">-</span>
                                                                                    <div
                                                                                        class="show_{{ $detail->id_sample_result_detail }}">
                                                                                        <div>
                                                                                            <!-- Hidden textarea for form submission -->
                                                                                            @php
                                                                                                // Check if this is an option-based parameter for detail
                                                                                                $isOptionDetailForTextarea = isset($laboratoriummethod->method_is_option) && $laboratoriummethod->method_is_option == 1;
                                                                                                $optionValuesDetail = [];
                                                                                                if ($isOptionDetailForTextarea && !empty($laboratoriummethod->method_option)) {
                                                                                                    $optionValuesDetail = array_map('trim', explode(',', $laboratoriummethod->method_option));
                                                                                                }
                                                                                                $__nilaiBmPlainDetail = '';
                                                                                                if (!empty($laboratoriummethod->nilai_baku_mutu)) {
                                                                                                    $__nilaiBmPlainDetail = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($laboratoriummethod->nilai_baku_mutu), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                }
                                                                                                if ($__nilaiBmPlainDetail === '' && !empty($detail->equal_sample_result_detail) && preg_match('/[<>≤≥]/u', (string) $detail->equal_sample_result_detail)) {
                                                                                                    $__nilaiBmPlainDetail = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($detail->equal_sample_result_detail), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                                                                                                }
                                                                                            @endphp
                                                                                            <!-- Hidden input untuk offset baku mutu -->
                                                                                            <input type="hidden"
                                                                                                name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                                                                id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                                                                value="{{ isset($detail->offset_baku_mutu) ? $detail->offset_baku_mutu : 'default' }}">
                                                                                            
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
                                                                                                placeholder="Hasil" required style="display: none;">{{ isset($detail->hasil) ? rubahNilaikeForm($detail->hasil) : (isset($detail->equal_sample_result_detail) ? rubahNilaikeForm($detail->equal_sample_result_detail) : '') }}</textarea>

                                                                                            <button type="button"
                                                                                                class="btn btn-sm btn-primary open-editor-modal"
                                                                                                data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                                                                data-method-id="{{ $detail->id_sample_result_detail }}"
                                                                                                data-method-name="{{ $detail->name_sample_result_detail }}">
                                                                                                <i
                                                                                                    class="fa fa-edit mr-1"></i>
                                                                                                Edit dengan Editor
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="align-middle">
                                                                                    @php
                                                                                        $__ktDetailStored = $detail->keterangan ?? '';
                                                                                        $__ktDetailDefault = trim($laboratoriummethod->keterangan_default ?? '');
                                                                                        $__keteranganDetailTampil = $__ktDetailStored !== '' ? $__ktDetailStored : $__ktDetailDefault;
                                                                                    @endphp
                                                                                    <div style="position: relative;">
                                                                                        <!-- Hidden textarea for form submission -->
                                                                                        <textarea class="form-control" id="keterangan_detail_{{ $detail->id_sample_result_detail }}"
                                                                                            name="keterangan_{{ $laboratoriummethod->method_id }}" placeholder="Masukkan keterangan..."
                                                                                            style="display: none;">{{ $__keteranganDetailTampil }}</textarea>

                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-primary open-editor-modal"
                                                                                            data-target="keterangan_detail_{{ $detail->id_sample_result_detail }}"
                                                                                            data-method-id="{{ $detail->id_sample_result_detail }}"
                                                                                            data-method-name="Keterangan - {{ $detail->name_sample_result_detail }}">
                                                                                            <i class="fa fa-edit mr-1"></i>
                                                                                            Edit Keterangan
                                                                                        </button>
                                                                                    </div>
                                                                                    <br><br>
                                                                                </td>
                                                                                <td class="text-center align-middle">
                                                                                    <!-- Tombol Baku Mutu akan ditambahkan oleh analis-inline-editing.js -->
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        @php
                                                                            $tidak_simpan = true;
                                                                        @endphp
                                                                        <tr
                                                                            style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                            <td>{{ $no }}</td>
                                                                            @php
                                                                                $jenis_makanan = $sample->jenis_makanan;
                                                                                if (isset($jenis_makanan)) {
                                                                                    $jenis_makanan =
                                                                                        $jenis_makanan->name_jenis_makanan;
                                                                                }
                                                                            @endphp
                                                                            <td colspan="7">
                                                                                Baku mutu untuk parameter
                                                                                <b>{{ $laboratoriummethod->params_method }}</b>,
                                                                                untuk
                                                                                jenis sarana
                                                                                <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }}</b></u>
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
                                                    {{-- <label for="wadah_samples"><b>Apakah baca hasil sudah siap?</b></label> --}}


                                                    {{-- <div class="form-check">
                          <input class="form-check-input" name="persiapan_reagen" type="radio" value="tidak" id="tidak">
                          <label class="form-check-label" for="flexCheckChecked">
                            Tidak
                          </label>
                        </div> --}}

                                                </div>
                                                @if (!$tidak_simpan)
                                                    <div class="form-group">

                                                        <label for="wadah_samples"><b> Verifikasi Hasil
                                                                dilakukan:</b></label>

                                                        @php
                                                            if (isset($verifikasi_hasil->verifikasi_hasil_date)) {
                                                                $verifikasi_hasil = \Carbon\Carbon::createFromFormat(
                                                                    'Y-m-d H:i:s',
                                                                    $verifikasi_hasil->verifikasi_hasil_date,
                                                                )->isoFormat('Y/M/D');
                                                            } else {
                                                                $verifikasi_hasil = '';
                                                            }
                                                        @endphp
                                                        <div class="input-group date" hidden>
                                                            <input type="text" class="form-control verifikasi_hasil"
                                                                name="verifikasi_hasil" id="verifikasi_hasil"
                                                                placeholder="Isikan Tanggal Verifikasi Hasil"
                                                                data-date-format="dd/mm/yyyy" required>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="fas fa-calendar-alt"></i>
                                                                </span>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" name="baca_hasil" type="checkbox"
                                                            value="ya" id="ya" required>
                                                        <label class="form-check-label" for="flexCheckChecked">
                                                            Pengisian Hasil sudah benar dan siap disahkan.
                                                        </label>
                                                    </div>

                                                    <br>


                                                    <button type="submit" id="submitAll"
                                                        class="btn btn-primary mr-2">Simpan</button>


                                                    <button type="button" class="btn btn-light"
                                                        onclick="window.history.back()">Kembali</button>
                                                @endif
                                            </form>





                                        </div>

                                        <br>














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

    <!-- TinyMCE Editor Modal -->
    <div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editorModalLabel">
                        <i class="fa fa-edit mr-2"></i><span id="modal-method-name">Edit Hasil</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fa fa-info-circle mr-2"></i>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Gunakan toolbar untuk format teks (pangkat/subscript)</li>
                            <li>Untuk <strong>pangkat</strong>: ketik angka → pilih → klik tombol <i
                                    class="fa fa-superscript"></i></li>
                            <li>Untuk <strong>subscript</strong>: ketik angka → pilih → klik tombol <i
                                    class="fa fa-subscript"></i></li>
                            <li>Simbol khusus tersedia di menu <strong>Insert → Special Character</strong></li>
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
                        <textarea id="tinyMCEEditor"></textarea>
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
                            Jika tidak dicentang, perubahan metode hanya disimpan untuk sampel ini saat verifikasi disimpan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEditorContent">
                        <i class="fa fa-check mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Status Baku Mutu -->
    <div class="modal fade" id="bakuMutuModal" tabindex="-1" role="dialog" aria-labelledby="bakuMutuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
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
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script>
        // Enable tooltips
        $(function() {
            $('[data-toggle="tooltip"]').tooltip({
                html: true
            });
        });

        // Function to update status label when checkbox changes
        function updateStatusLabel(checkbox, labelId) {
            const label = document.getElementById(labelId);
            if (checkbox.checked) {
                label.className = 'badge badge-success';
                label.innerHTML = '<i class="fa fa-check-circle mr-1"></i>Wajib Diisi';
            } else {
                label.className = 'badge badge-warning';
                label.innerHTML = '<i class="fa fa-times-circle mr-1"></i>Boleh Kosong';
            }
        }

        // Function to create result badge
        function createResultBadge(value, type) {
            if (type === 'success') {
                return '<span class="badge badge-success" style="font-size: 13px; padding: 6px 12px;">' +
                    '<i class="fa fa-check-circle mr-1"></i><strong>' + value + '</strong></span>';
            } else if (type === 'danger') {
                return '<span class="badge badge-danger" style="font-size: 13px; padding: 6px 12px;">' +
                    '<i class="fa fa-times-circle mr-1"></i><strong>' + value + '</strong></span>';
            } else {
                return '<span class="badge badge-secondary" style="font-size: 13px; padding: 6px 12px;">' +
                    '<strong>' + value + '</strong></span>';
            }
        }

        // Normalize string for comparison (remove spaces/nbsp and uppercase)
        function normalizeString(str) {
            if (!str) return '';
            return String(str).replace(/&nbsp;/g, ' ').replace(/\s+/g, '').toUpperCase();
        }

        // === TinyMCE Functions ===
        function convertToTinyMCE(value) {
            if (!value) return '';
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            return value;
        }

        function convertFromTinyMCE(value) {
            if (!value) return '';
            value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
            value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
            value = value.replace(/<[^>]*>/g, ''); // Strip remaining HTML tags
            value = value.replace(/&le;/gi, '≤');
            value = value.replace(/&ge;/gi, '≥');
            value = value.replace(/&lt;/g, '<');
            value = value.replace(/&gt;/g, '>');
            value = value.replace(/&plusmn;/g, '±');
            value = value.replace(/&nbsp;/g, ' ');
            return value;
        }

        $(document).ready(function() {
            var currentTargetTextarea = null;
            var tinyMCEInstance = null;
            var currentIsOption = false;
            var currentOptions = [];
            var currentMethodId = null;
            var currentTargetId = null;
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

            // Open modal when clicking the editor button
            $(document).on('click', '.open-editor-modal', function() {
                var targetId = $(this).data('target');
                var methodName = $(this).data('method-name');
                var isOption = $(this).data('is-option') ? true : false;
                var optionsData = $(this).data('options') || null;
                var currentValueAttr = $(this).data('current-value') || '';

                currentTargetTextarea = document.getElementById(targetId);
                currentTargetId = targetId;
                currentMethodId = $(this).data('method-id') || (targetId && targetId.indexOf('result_method_') === 0 ? targetId.replace('result_method_', '') : null);
                $('#modal-method-name').text(methodName);

                currentIsOption = isOption;
                currentOptions = [];

                if (optionsData) {
                    try {
                        if (Array.isArray(optionsData)) {
                            currentOptions = optionsData;
                        } else if (typeof optionsData === 'string') {
                            currentOptions = JSON.parse(optionsData);
                        }
                    } catch (e) {
                        console.warn('Failed to parse options for verifikasi-hasil editor:', e);
                        currentOptions = [];
                    }
                }

                // Jika ada current_value dari data-attribute, dan textarea kosong, isi dulu
                if (currentTargetTextarea && currentValueAttr && !currentTargetTextarea.value) {
                    currentTargetTextarea.value = currentValueAttr;
                }

                $('#editorModal').modal('show');
            });

            // Initialize editor when modal is shown
            $('#editorModal').on('shown.bs.modal', function() {
                // Reset containers
                $('#editor_option_container').hide();
                $('#editor_text_container').hide();
                toggleEditorMetodeSection(currentTargetId, currentMethodId);

                // MODE DROPDOWN (is_option = 1)
                if (currentIsOption && currentOptions && currentOptions.length > 0 && currentTargetTextarea) {
                    var $select = $('#editor_option_select');
                    $select.empty();

                    var currentVal = currentTargetTextarea.value || '';

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

                    // Pastikan TinyMCE dimatikan di mode dropdown
                    if (tinyMCEInstance) {
                        tinymce.remove('#tinyMCEEditor');
                        tinyMCEInstance = null;
                    }

                    return;
                }

                // MODE TINYMCE (default)
                $('#editor_text_container').show();

                if (currentTargetTextarea) {
                    var currentValue = currentTargetTextarea.value;
                    var htmlValue = convertToTinyMCE(currentValue);

                    // Destroy existing instance if any
                    if (tinyMCEInstance) {
                        tinymce.remove('#tinyMCEEditor');
                    }

                    // Initialize TinyMCE (gunakan lokal)
                    var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                    tinymce.init({
                        selector: '#tinyMCEEditor',
                        height: 300,
                        theme: 'modern',
                        menubar: false,
                        plugins: [
                            'lists link charmap preview anchor',
                            'searchreplace code',
                            'insertdatetime table paste code help wordcount'
                        ],
                        toolbar: 'undo redo | formatselect | bold italic | superscript subscript | charmap | removeformat | help',
                        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                        setup: function(editor) {
                            editor.on('init', function() {
                                editor.setContent(htmlValue);
                                tinyMCEInstance = editor;
                            });
                        }
                    });
                }
            });

            // Save editor content
            $('#saveEditorContent').on('click', function() {
                var closeModal = function() {
                    $('#editorModal').modal('hide');
                };
                var afterHasilSaved = function() {
                    persistMetodeFromModal(currentMethodId, function(ok) {
                        if (ok) closeModal();
                    });
                };

                // MODE DROPDOWN
                if (currentIsOption && currentTargetTextarea) {
                    var selectedValue = $('#editor_option_select').val() || '';

                    currentTargetTextarea.value = selectedValue;
                    $(currentTargetTextarea).trigger('input');

                    afterHasilSaved();
                    return;
                }

                // MODE TINYMCE
                if (tinyMCEInstance && currentTargetTextarea) {
                    var htmlContent = tinyMCEInstance.getContent();
                    var systemFormat = convertFromTinyMCE(htmlContent);

                    currentTargetTextarea.value = systemFormat;

                    // Trigger change event untuk update output simulasi
                    $(currentTargetTextarea).trigger('input');

                    afterHasilSaved();
                }
            });

            // Cleanup on modal close
            $('#editorModal').on('hidden.bs.modal', function() {
                if (tinyMCEInstance) {
                    tinymce.remove('#tinyMCEEditor');
                    tinyMCEInstance = null;
                }
                currentTargetTextarea = null;
                currentTargetId = null;
                currentMethodId = null;
                currentIsOption = false;
                currentOptions = [];
            });
        });

        // saveAll
        // Handler lama untuk radio button offset_baku_mutu sudah diganti dengan modal handler
        // Handler untuk result_method sudah diganti dengan inline editing di analis-inline-editing.js

        // Tidak ada lagi dropdown is_option di luar modal; semua opsi di-handle via popup editor

        var tanggal
        if ("{{ $verifikasi_hasil }}" != undefined && "{{ $verifikasi_hasil }}" != "") {
            tanggal = new Date("{{ $verifikasi_hasil }}")
        } else {

            tanggal = new Date()
        }

        $('.verifikasi_hasil').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('.verifikasi_hasil').datepicker('update', tanggal);

        var laboratoriummethods = @json($laboratoriummethods);

        laboratoriummethods.forEach(laboratoriummethod => {
            $('#status_' + laboratoriummethod.method_id).change(function() {
                // console.log($(this).val())
                if ($(this).is(':checked')) {
                    $(".not_show_" + laboratoriummethod.method_id).hide();
                    $(".show_" + laboratoriummethod.method_id).show();
                    // $("#result_method_"+laboratoriummethod.method_id).val("");

                } else {
                    // $("#result_method_"+laboratoriummethod.method_id).val("-");
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
                        // $("#result_method_"+laboratoriummethod.method_id).val("");

                    } else {
                        // $("#result_method_"+laboratoriummethod.method_id).val("-");
                        $(".show_" + detail.id_sample_result_detail).hide();
                        $(".not_show_" + detail.id_sample_result_detail).show();


                    }
                })
            })
        })



        $(document).ready(function() {
            // TinyMCE untuk Titik Sampel (titik_pengambilan)
            @if (!($sample->kode_laboratorium == 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya'))
                if (tinymce.get('titik_pengambilan')) {
                    tinymce.get('titik_pengambilan').remove();
                }
                // Ensure base URL is set before init (gunakan lokal)
                var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                if (typeof tinymce !== 'undefined') {
                    if (tinymce.baseURL === undefined || 
                        tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                        tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                        tinymce.baseURL = tinymceBasePath;
                    }
                }
                tinymce.init({
                    selector: 'textarea#titik_pengambilan',
                    height: 200,
                    theme: 'modern',
                    menubar: false,
                    plugins: [
                        'lists link charmap preview',
                        'searchreplace code',
                        'insertdatetime table paste wordcount'
                    ],
                    toolbar: 'undo redo | bold italic | bullist numlist | removeformat | help',
                    content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; }',
                    setup: function(editor) {
                        editor.on('change blur', function() {
                            tinymce.triggerSave();
                        });
                    }
                });
            @endif

            // Aktifkan TinyMCE untuk field lokasi agar konsisten dan lebih rapi
            @if ($sample->kode_laboratorium === 'MBI')
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
            @endif

            // === Jenis Makanan (MBI & KIM) - Select2 & on change reload ===
            @if (
                ($sample->kode_laboratorium == 'MBI' || $sample->kode_laboratorium == 'KIM') && 
                $sample->name_sample_type === 'Makanan/Minuman/Lainnya')
                // Cek apakah element jenis_makanan_id ada di DOM
                if ($('#jenis_makanan_id').length > 0) {
                    $('#jenis_makanan_id').select2({
                        placeholder: 'Pilih Jenis Makanan'
                    });

                    // Saat user memilih jenis makanan, reload halaman dengan query ?jenis_makanan_id=...
                    // Untuk KIM, jenisId bisa null/empty (tanpa jenis makanan)
                    $('#jenis_makanan_id').on('change', function() {
                        var jenisId = $(this).val();
                        var url = new URL(window.location.href);
                        if (jenisId && jenisId !== '') {
                            url.searchParams.set('jenis_makanan_id', jenisId);
                        } else {
                            url.searchParams.delete('jenis_makanan_id');
                        }
                        window.location.href = url.toString();
                    });
                }
            @endif

        });

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
            
            value = value.replaceAll('^(', "<sup>");
            value = value.replaceAll(')', "</sup>");
            value = value.replaceAll("<=", '&#8804;');
            value = value.replaceAll(">=", '&#8805;');
            value = value.replaceAll("<", '&#60;');
            value = value.replaceAll(">", '&#62;');
            // console.log(value)
            return value;
            // let result = value.indexOf("^");
            // console.log(value.substring(result+1,value.length))
        }

        // === PENGATURAN BAKU MUTU BERDASARKAN RUANGAN (KUALITAS UDARA) ===
        @php
            $isKualitasUdara = isset($sample->name_sample_type) && 
                stripos($sample->name_sample_type, 'udara') !== false;
        @endphp
        @if ($isKualitasUdara)
            // Function untuk format nilai baku mutu ke HTML (mirip dengan rubahNilaikeForm di PHP)
            function formatNilaiBakuMutu(value) {
                if (!value || value === '' || value === null) return '-';
                
                // Convert format sistem ke HTML untuk display
                var formatted = value.toString();
                
                // Convert Unicode superscript characters to <sup> tags FIRST
                // This handles characters like ³, ², ¹, etc.
                formatted = formatted.replace(/¹/g, '<sup>1</sup>');
                formatted = formatted.replace(/²/g, '<sup>2</sup>');
                formatted = formatted.replace(/³/g, '<sup>3</sup>');
                formatted = formatted.replace(/⁴/g, '<sup>4</sup>');
                formatted = formatted.replace(/⁵/g, '<sup>5</sup>');
                formatted = formatted.replace(/⁶/g, '<sup>6</sup>');
                formatted = formatted.replace(/⁷/g, '<sup>7</sup>');
                formatted = formatted.replace(/⁸/g, '<sup>8</sup>');
                formatted = formatted.replace(/⁹/g, '<sup>9</sup>');
                formatted = formatted.replace(/⁰/g, '<sup>0</sup>');
                
                // Convert ^() to <sup>
                formatted = formatted.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                // Convert _() to <sub>
                formatted = formatted.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                // Convert comparison symbols
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
                    // Baca langsung dari attribute dan unescape HTML entities
                    var lokasiDataRaw = $textarea.attr('data-lokasi-data');
                    
                    if (!lokasiDataRaw || lokasiDataRaw.trim() === '') {
                        return; // Skip jika tidak ada lokasi data
                    }
                    
                    try {
                        // Parse JSON langsung (karena kita sudah tidak menggunakan htmlspecialchars)
                        // Tapi tetap unescape sebagai fallback jika ada HTML entities
                        var lokasiDataUnescaped = lokasiDataRaw;
                        if (lokasiDataRaw.indexOf('&quot;') !== -1 || lokasiDataRaw.indexOf('&amp;') !== -1) {
                            // Jika masih ada HTML entities, unescape dulu
                            lokasiDataUnescaped = $('<div>').html(lokasiDataRaw).text();
                        }
                        
                        // Parse JSON
                        var lokasiArray = JSON.parse(lokasiDataUnescaped);
                        
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
                        console.error('lokasiData value:', lokasiData);
                        console.error('lokasiData type:', typeof lokasiData);
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

        // Flatpickr untuk Verifikasi Hasil
        $(document).ready(function() {
            // Inisialisasi Flatpickr untuk Start Date
            if ($('#start_date_verifikasi_hasil').length) {
                flatpickr('#start_date_verifikasi_hasil', {
                    dateFormat: 'd/m/Y',
                    enableTime: false,
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    defaultHour: 8,
                    defaultMinute: 0,
                    minTime: '08:00',
                    maxTime: '17:00',
                    onChange: function(selectedDates, dateStr, instance) {
                        // Auto-adjust to work hours
                        if (selectedDates.length > 0) {
                            var selectedDate = selectedDates[0];
                            var hours = selectedDate.getHours();
                            if (hours < 8) {
                                instance.setDate(new Date(selectedDate.setHours(8, 0, 0, 0)), false);
                            } else if (hours > 17) {
                                instance.setDate(new Date(selectedDate.setHours(17, 0, 0, 0)), false);
                            }
                        }
                    }
                });
            }

            // Inisialisasi Flatpickr untuk Stop Date
            if ($('#stop_date_verifikasi_hasil').length) {
                flatpickr('#stop_date_verifikasi_hasil', {
                    dateFormat: 'd/m/Y',
                    enableTime: false,
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    defaultHour: 17,
                    defaultMinute: 0,
                    minTime: '08:00',
                    maxTime: '17:00',
                    onChange: function(selectedDates, dateStr, instance) {
                        // Auto-adjust to work hours
                        if (selectedDates.length > 0) {
                            var selectedDate = selectedDates[0];
                            var hours = selectedDate.getHours();
                            if (hours < 8) {
                                instance.setDate(new Date(selectedDate.setHours(8, 0, 0, 0)), false);
                            } else if (hours > 17) {
                                instance.setDate(new Date(selectedDate.setHours(17, 0, 0, 0)), false);
                            }
                        }
                    }
                });
            }

            // Handler untuk form submit
            $('form').on('submit', function(e) {
                // Validasi Verifikasi Hasil fields
                var verifikasiStartDate = $('#start_date_verifikasi_hasil').val();
                var verifikasiStopDate = $('#stop_date_verifikasi_hasil').val();
                var verifikasiNamaPetugas = $('#nama_petugas_verifikasi_hasil').val();

                if (!verifikasiStartDate || !verifikasiStopDate || !verifikasiNamaPetugas) {
                    alert('Form Verifikasi Hasil harus diisi lengkap sebelum menyimpan.');
                    e.preventDefault();
                    return false;
                }

                // Populate hidden inputs for verification
                $('#verification_step_verifikasi_hasil').val('4');
                $('#nama_petugas_verifikasi_hasil_hidden').val(verifikasiNamaPetugas);

                // Convert dates using Flatpickr formatDate
                var startDateFP = $('#start_date_verifikasi_hasil')[0]._flatpickr;
                var stopDateFP = $('#stop_date_verifikasi_hasil')[0]._flatpickr;

                if (startDateFP && startDateFP.selectedDates && startDateFP.selectedDates.length > 0) {
                    var startDateFormatted = startDateFP.formatDate(startDateFP.selectedDates[0], 'd/m/Y');
                    $('#start_date_verifikasi_hasil_hidden').val(startDateFormatted);
                } else {
                    $('#start_date_verifikasi_hasil_hidden').val(verifikasiStartDate);
                }

                if (stopDateFP && stopDateFP.selectedDates && stopDateFP.selectedDates.length > 0) {
                    var stopDateFormatted = stopDateFP.formatDate(stopDateFP.selectedDates[0], 'd/m/Y');
                    $('#stop_date_verifikasi_hasil_hidden').val(stopDateFormatted);
                } else {
                    $('#stop_date_verifikasi_hasil_hidden').val(verifikasiStopDate);
                }
                
                // Ensure all offset_baku_mutu hidden inputs are included in form submission
                // Sync offset values from all hidden inputs to ensure they're submitted
                var offsetInputsFound = 0;
                $('input[id^="offset_baku_mutu_"], input[name^="offset_baku_mutu_"]').each(function() {
                    var $input = $(this);
                    var id = $input.attr('id');
                    var name = $input.attr('name');
                    var value = $input.val();
                    var isInForm = $input.closest('form').length > 0;
                    
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
                        $('form').first().append($clone);
                        console.log('Cloned offset input to form:', id);
                    }
                });
                
                console.log('Total offset inputs found:', offsetInputsFound);
                
                // Also log all form data that will be submitted
                var formData = $('form').first().serializeArray();
                var offsetData = formData.filter(function(item) {
                    return item.name.indexOf('offset_baku_mutu_') !== -1;
                });
                console.log('Offset data in form submission:', offsetData);
            });
        });
    </script>
    
    <!-- Number Format Helper - Required for parseNumberInput function -->
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
    
    <!-- Analis Inline Editing Script -->
    <script src="{{ asset('assets/js/analis-inline-editing.js') }}?v={{ filemtime(public_path('assets/js/analis-inline-editing.js')) }}"></script>
    
    <script>
        // TinyMCE untuk semua textarea Metode (menggunakan konfigurasi yang sama dengan Hasil)
        $(document).ready(function() {
            // Tunggu sampai TinyMCE dan analis-inline-editing.js selesai load
            setTimeout(function() {
                if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                    var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                    if (tinymce.baseURL === undefined || 
                        tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                        tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                        tinymce.baseURL = tinymceBasePath;
                    }
                    
                    // Inisialisasi TinyMCE untuk semua textarea dengan class metode-editor
                    // Menggunakan konfigurasi yang sama dengan inline-hasil-editor
                    $('.metode-editor').each(function() {
                        var $textarea = $(this);
                        var editorId = $textarea.attr('id');
                        if (editorId && !tinymce.get(editorId + '_editor')) {
                            // Convert textarea to inline div (sama seperti hasil)
                            var content = $textarea.val() || $textarea.text();
                            var $editorDiv = $('<div>')
                                .addClass('inline-metode-editor')
                                .attr('id', editorId + '_editor')
                                .attr('data-original-id', editorId)
                                .html(content);
                            $textarea.after($editorDiv).hide();
                            
                            tinymce.init({
                                selector: '#' + editorId + '_editor',
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
                                // Allow sup and sub tags
                                valid_elements: '*[*]',
                                extended_valid_elements: 'sup[*],sub[*]',
                                // Ensure superscript/subscript commands are available
                                formats: {
                                    superscript: {inline: 'sup', styles: {verticalAlign: 'super'}},
                                    subscript: {inline: 'sub', styles: {verticalAlign: 'sub'}}
                                },
                                forced_root_block: false,
                                force_br_newlines: true,
                                force_p_newlines: false,
                                charmap_append: [
                                    // Simbol matematika
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
                                    // Simbol kimia
                                    [0x00B0, 'degree sign'],
                                    [0x2103, 'degree celsius'],
                                    [0x00D7, 'multiplication sign'],
                                    [0x00F7, 'division sign'],
                                    // Greek letters (untuk notasi)
                                    [0x03B1, 'greek small letter alpha'],
                                    [0x03B2, 'greek small letter beta'],
                                    [0x03B3, 'greek small letter gamma'],
                                    [0x03BC, 'greek small letter mu']
                                ],
                                setup: function(editor) {
                                    editor.on('change blur', function() {
                                        // Sync content ke textarea tersembunyi untuk form submission
                                        var content = editor.getContent();
                                        $textarea.val(content);
                                        tinymce.triggerSave();
                                    });
                                }
                            });
                        }
                    });
                }
            }, 500); // Tunggu 500ms untuk memastikan semua script sudah load
        });
        
        // Function to update status label when checkbox changes
        function updateStatusLabel(checkbox, labelId) {
            var label = document.getElementById(labelId);
            var checkboxId = checkbox.id;
            var methodId = checkboxId.replace('status_', '');
            
            if (checkbox.checked) {
                label.className = 'badge badge-success';
                label.innerHTML = '<i class="fa fa-check-circle mr-1"></i>Wajib Diisi';
                showHasilForm(methodId);
            } else {
                label.className = 'badge badge-warning';
                label.innerHTML = '<i class="fa fa-times-circle mr-1"></i>Boleh Kosong';
                hideHasilForm(methodId);
            }
        }
        
        function hideHasilForm(methodId) {
            var $textarea = $('#result_method_' + methodId);
            if ($textarea.length > 0) {
                var index = $textarea.data('index') || methodId;
                var $hasilContainer = $textarea.closest('td').find('.hasil-input-container');
                if ($hasilContainer.length > 0) {
                    $hasilContainer.hide();
                }
                var $badgeButtonsRow = $textarea.closest('td').find('.badge-buttons-row');
                if ($badgeButtonsRow.length > 0) {
                    $badgeButtonsRow.hide();
                }
            }
        }
        
        function showHasilForm(methodId) {
            var $textarea = $('#result_method_' + methodId);
            if ($textarea.length > 0) {
                var index = $textarea.data('index') || methodId;
                var $hasilContainer = $textarea.closest('td').find('.hasil-input-container');
                if ($hasilContainer.length > 0) {
                    $hasilContainer.show();
                }
                var $badgeButtonsRow = $textarea.closest('td').find('.badge-buttons-row');
                if ($badgeButtonsRow.length > 0) {
                    $badgeButtonsRow.show();
                }
            }
        }

        // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
        window.convertToTinyMCE = function(value) {
            if (!value) return '';
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            return value;
        };

        // Convert from HTML <sup> and <sub> to ^() and _() format for our system
        window.convertFromTinyMCE = function(value) {
            if (!value) return '';
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

        // Function to check baku mutu (selaras baca-hasil / cetak mikro)
        window.checkBakuMutu = function(value, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutuParam, numberFormat, nilaiBakuMutuRaw) {
            if (!value || value === '' || value === '-') return '';

            nilaiBakuMutuRaw = (nilaiBakuMutuRaw === undefined || nilaiBakuMutuRaw === null) ? '' : String(nilaiBakuMutuRaw);
            numberFormat = numberFormat || 'en';
            offset_baku_mutu = String(offset_baku_mutu || 'default').trim().toLowerCase();
            if (offset_baku_mutu !== 'true' && offset_baku_mutu !== 'false') {
                offset_baku_mutu = 'default';
            }

            var melewati = false;
            var kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';

            if (offset_baku_mutu === 'false') {
                var formattedValue = toFormatHtml(value || '');
                return createResultBadge(formattedValue, 'success');
            } else if (offset_baku_mutu === 'true') {
                var formattedValue = toFormatHtml(value || '');
                return createResultBadge(formattedValue, 'danger');
            } else {
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
                var equalLooksInequality = equalStr !== '' && /[<>≤≥]/.test(equalStr);
                var equalAsMax = (!equalLooksInequality) ? window.parseLabNumeric(equalStr) : null;

                var nbm = normalizeBmDisplay(nilaiBakuMutuRaw);
                if (!nbm && equalLooksInequality) {
                    nbm = normalizeBmDisplay(equalStr);
                }
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

                var handledFromNilaiTeks = false;
                if (nbm !== '' && isSimpleNumericResult) {
                    var mLe = nbm.match(/(?:<=|≤)\s*([\d.,]+)/);
                    if (mLe) {
                        var thrLe = parseThr(mLe[1]);
                        if (!isNaN(thrLe)) {
                            melewati = (numValue > thrLe);
                            handledFromNilaiTeks = true;
                        }
                    } else if (/<\s*[\d.,]+/.test(nbm) && !/(?:<=|≤)/.test(nbm)) {
                        var mLt = nbm.match(/<\s*([\d.,]+)/);
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
                            melewati = (numValue < numMin);
                        }
                    } else if (hasMax) {
                        if (isSimpleNumericResult) {
                            melewati = (numValue > numMax);
                        }
                    }
                }

                var status = melewati ? 'danger' : 'success';
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

        function toFormatHtml(value) {
            if (!value) return '';
            
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
            
            // If value already contains HTML tags (like <sup> or <sub>), preserve them
            if (value.includes('<sup') || value.includes('<sub')) {
                // Still convert any remaining ^( or _( notation that might be in the text
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\^(\d+)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                value = value.replace(/\_(\d+)/g, '<sub>$1</sub>');
                // Ensure special characters are encoded
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                return value;
            }
            // Convert text format to HTML
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\^(\d+)/g, '<sup>$1</sup>'); // Handle ^2 format
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            value = value.replace(/\_(\d+)/g, '<sub>$1</sub>'); // Handle _2 format
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            return value;
        }

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

        // Global error handler for TinyMCE
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
        
        // Wait for TinyMCE and then initialize AnalisInlineEditor
        var tinyMCERetryCount = 0;
        var maxTinyMCERetries = 50;
        
        function waitForTinyMCE() {
            if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                console.log('TinyMCE loaded successfully, waiting for DOM elements...');
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
                    console.error('TinyMCE failed to load after ' + maxTinyMCERetries + ' attempts.');
                    if (typeof AnalisInlineEditor !== 'undefined') {
                        console.log('Attempting to initialize AnalisInlineEditor without TinyMCE...');
                        AnalisInlineEditor.init();
                    }
                }
            }
        }

        if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
            console.log('TinyMCE already loaded');
            $(document).ready(function() {
                waitForTinyMCE();
            });
        } else {
            $(document).ready(function() {
                waitForTinyMCE();
            });
        }

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
            }, 1500);
        });

        // === BAKU MUTU MODAL HANDLERS ===
        $(document).on('click', '.btn-baku-mutu-override', function() {
            var $btn = $(this);
            var index = $btn.data('index');
            var isSub = $btn.data('is-sub') == '1';
            
            var $row = $btn.closest('tr');
            var $paramTd = $row.find('td').eq(1);
            var parameterName = '';
            
            if ($paramTd.length > 0) {
                parameterName = $paramTd.clone().children().remove().end().text().trim();
                if (!parameterName) {
                    parameterName = $paramTd.find('b, strong').first().text().trim();
                }
                if (!parameterName) {
                    parameterName = $paramTd.text().trim();
                }
            }
            
            if (!parameterName) {
                parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
            }
            
            var offsetInputId = 'offset_baku_mutu_' + index;
            var $offsetInput = $('#' + offsetInputId);
            var currentOffset = 'default';
            
            if ($offsetInput.length === 0) {
                $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
            }
            if ($offsetInput.length === 0 && $row.length > 0) {
                $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"], input[name="offset_baku_mutu_' + index + '"]');
            }
            
            if ($offsetInput.length > 0) {
                currentOffset = String($offsetInput.val() || 'default').trim().toLowerCase();
            }
            
            if (currentOffset !== 'true' && currentOffset !== 'false') {
                currentOffset = 'default';
            }
            
            $('#bakuMutuParamName').text(parameterName);
            $('#bakuMutuModal').data('index', index);
            $('#bakuMutuModal').data('is-sub', isSub);
            $('#bakuMutuModal').data('offset-input-id', offsetInputId);
            $('#bakuMutuModal').data('current-offset', currentOffset);
            
            $('input[name="baku-mutu-offset"]').prop('checked', false);
            var $targetRadio = $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]');
            if ($targetRadio.length > 0) {
                $targetRadio.prop('checked', true);
            } else {
                $('input[name="baku-mutu-offset"][value="default"]').prop('checked', true);
            }
            
            $('#bakuMutuModal').modal('show');
        });
        
        $('#bakuMutuModal').on('shown.bs.modal', function() {
            var index = $('#bakuMutuModal').data('index');
            var offsetInputId = $('#bakuMutuModal').data('offset-input-id');
            var storedOffset = $('#bakuMutuModal').data('current-offset');
            
            if (index !== undefined && index !== null) {
                if (!offsetInputId) {
                    offsetInputId = 'offset_baku_mutu_' + index;
                }
                
                var $offsetInput = $('#' + offsetInputId);
                if ($offsetInput.length === 0) {
                    $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
                }
                
                var currentOffset = 'default';
                if ($offsetInput.length > 0) {
                    currentOffset = String($offsetInput.val() || 'default').trim().toLowerCase();
                } else if (storedOffset) {
                    currentOffset = String(storedOffset).trim().toLowerCase();
                }
                
                if (currentOffset !== 'true' && currentOffset !== 'false') {
                    currentOffset = 'default';
                }
                
                $('input[name="baku-mutu-offset"]').prop('checked', false);
                var $targetRadio = $('input[name="baku-mutu-offset"][value="' + currentOffset + '"]');
                if ($targetRadio.length > 0) {
                    $targetRadio.prop('checked', true);
                } else {
                    $('input[name="baku-mutu-offset"][value="default"]').prop('checked', true);
                }
            }
        });
        
        function updateBakuMutuStatus(selectedOffset, index, isSub) {
            selectedOffset = String(selectedOffset || 'default').trim().toLowerCase();
            if (selectedOffset !== 'true' && selectedOffset !== 'false') {
                selectedOffset = 'default';
            }
            
            var offsetInputId = 'offset_baku_mutu_' + index;
            var $row = null;
            var $btn = $('.btn-baku-mutu-override[data-index="' + index + '"]');
            if ($btn.length > 0) {
                $row = $btn.closest('tr');
            }
            if ($row.length === 0) {
                $row = $('textarea#result_method_' + index).closest('tr');
            }
            
            var $offsetInput = null;
            if ($row.length > 0) {
                $offsetInput = $row.find('#' + offsetInputId);
                if ($offsetInput.length === 0) {
                    $offsetInput = $row.find('input[name="offset_baku_mutu_' + index + '"]');
                }
            }
            
            if ($offsetInput.length === 0) {
                $offsetInput = $('#' + offsetInputId);
            }
            if ($offsetInput.length === 0) {
                $offsetInput = $('input[name="offset_baku_mutu_' + index + '"]');
            }
            
            if (!$offsetInput || $offsetInput.length === 0) {
                var $newInput = $('<input>').attr({
                    'type': 'hidden',
                    'name': 'offset_baku_mutu_' + index,
                    'id': 'offset_baku_mutu_' + index,
                    'value': selectedOffset
                });
                
                if ($row && $row.length > 0) {
                    var $textarea = $row.find('textarea.result_method_klinik');
                    if ($textarea.length > 0) {
                        $newInput.insertAfter($textarea);
                    } else {
                        var $form = $('form').first();
                        if ($form.length > 0) {
                            $form.append($newInput);
                        }
                    }
                } else {
                    var $form = $('form').first();
                    if ($form.length > 0) {
                        $form.append($newInput);
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
                var $form = $('form').first();
                if ($form.length > 0 && !$offsetInput.closest('form').length) {
                    console.warn('Offset input is not inside form, moving it...');
                    $offsetInput.appendTo($form);
                    console.log('Moved offset input to form');
                }
                
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
                }
                
                if ($row.length > 0) {
                    var $textarea = $row.find('textarea.result_method_klinik');
                    if ($textarea.length > 0) {
                        var textareaIndex = $textarea.data('index') || index;
                        var currentValue = $textarea.val() || '';
                        
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
                        var nilaiBmAttr = $textarea.attr('data-nilai-baku-mutu') || '';

                        console.log("min= ",min);
                        console.log("max= ",max);
                        console.log("equal= ",equal);
                        
                        var normalizedOffset = String(selectedOffset || 'default').trim().toLowerCase();
                        if (normalizedOffset !== 'true' && normalizedOffset !== 'false') {
                            normalizedOffset = 'default';
                        }
                        
                        if (typeof window.checkBakuMutu === 'function') {
                            var badgeHtml = window.checkBakuMutu(currentValue, min, max, equal, normalizedOffset, null, '', numberFormat, nilaiBmAttr);
                            if (badgeHtml) {
                                var $badgeContainer = $('#badge_' + textareaIndex);
                                if ($badgeContainer.length === 0) {
                                    $badgeContainer = $row.find('#badge_' + textareaIndex);
                                }
                                if ($badgeContainer.length === 0) {
                                    $badgeContainer = $row.find('.result-badge-inline, [id^="badge_"]');
                                }
                                
                                if ($badgeContainer.length > 0) {
                                    $badgeContainer.html(badgeHtml);
                                }
                            }
                        } else if (typeof AnalisInlineEditor !== 'undefined' && AnalisInlineEditor.updateResultBadge) {
                            AnalisInlineEditor.updateResultBadge(textareaIndex, currentValue, min, max, equal, numberFormat);
                        }
                    }
                }
            }
        }
        
        $(document).on('change', 'input[name="baku-mutu-offset"]', function() {
            var selectedOffset = $(this).val();
            var index = $('#bakuMutuModal').data('index');
            var isSub = $('#bakuMutuModal').data('is-sub') == '1';
            
            if (index !== undefined && index !== null) {
                setTimeout(function() {
                    updateBakuMutuStatus(selectedOffset, index, isSub);
                }, 10);
            }
        });
        
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
                            var $form = $('#form-verifikasi-hasil');
                            if ($form.length > 0 && !$offsetInput.closest('#form-verifikasi-hasil').length) {
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
@endsection
