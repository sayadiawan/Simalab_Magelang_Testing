<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Sampling Klinik</title>

    <link href="{{ asset('assets/admin/cdn-local/css/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>
    <link href="{{ asset('assets/admin/cdn-local/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/admin/cdn-local/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding-bottom: 80px;
        }

        .top-bar {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #2D6BCF;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: #2D6BCF;
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-item label {
            font-weight: 600;
            min-width: 120px;
            color: #666;
        }

        .info-item span {
            color: #333;
            flex: 1;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .form-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            accent-color: #2D6BCF;
        }

        .floating-submit {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 15px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2D6BCF;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 12px;
            border-radius: 10px;
            border-left: 4px solid #2196f3;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .required {
            color: red;
        }

        .resampling-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
            margin-top: 15px;
        }

        /* Inline Edit Styles */
        .inline-edit-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            margin-top: 8px;
            position: relative;
            z-index: 10;
        }

        .inline-edit-display {
            flex: 1;
            padding: 12px 0;
            background: transparent;
            border: none;
            border-radius: 0;
            color: #333;
            font-size: 15px;
            cursor: default;
            min-height: 48px;
            display: flex;
            align-items: center;
            font-weight: 500;
            position: relative;
            z-index: 10;
        }

        .inline-edit-display.empty {
            color: #9ca3af;
            font-style: italic;
        }

        .inline-edit-display.hidden {
            display: none;
        }

        .inline-edit-btn {
            cursor: pointer;
            color: #2D6BCF;
            padding: 12px 16px;
            border-radius: 10px;
            transition: all .2s;
            font-size: 16px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            visibility: visible !important;
            opacity: 1 !important;
            min-width: 48px;
            min-height: 48px;
            line-height: 1;
            background: #f8f9fa;
            border: 2px solid #e5e7eb;
            position: relative;
            z-index: 100;
            pointer-events: auto !important;
        }

        .inline-edit-btn:active {
            background: #eef2ff;
            color: #1e4a9e;
            border-color: #2D6BCF;
            transform: scale(0.95);
        }

        .inline-edit-btn i {
            font-size: 18px !important;
            display: inline-block !important;
        }

        .inline-edit-btn>span[style*="margin-left"] {
            display: inline-block;
            line-height: 1;
        }

        .inline-edit-btn.fa-loaded>span[style*="margin-left"] {
            display: none !important;
        }

        .inline-edit-btn i.fa-pencil:empty:before,
        .inline-edit-btn i.fa-pencil:not(.fa):before {
            content: "✏";
            display: inline-block;
            font-style: normal;
        }

        .inline-edit-input {
            display: none;
            flex: 1;
            position: relative;
            will-change: contents;
            z-index: 10;
        }

        .inline-edit-input.active {
            display: block;
            z-index: 20;
        }

        .inline-edit-input .form-control {
            width: 100%;
            position: relative;
            z-index: 20;
        }

        .inline-edit-wrapper {
            min-height: 48px;
            position: relative;
        }

        /* Select2 Mobile Styling */
        .select2-container {
            width: 100% !important;
            z-index: 99999 !important;
            position: relative;
        }

        .select2-container--bootstrap4 {
            position: relative !important;
            z-index: 99999 !important;
        }

        .select2-container--open .select2-dropdown {
            z-index: 100000 !important;
            display: block !important;
        }

        .select2-container--bootstrap4 .select2-selection {
            min-height: 48px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 8px;
            font-size: 15px;
        }

        .select2-container--bootstrap4 .select2-selection__choice {
            background-color: #2D6BCF;
            border: none;
            color: white;
            padding: 6px 12px;
            margin: 4px 4px 4px 0;
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.4;
        }

        .select2-container--bootstrap4 .select2-selection__choice__remove {
            color: white;
            margin-right: 6px;
            font-weight: bold;
            font-size: 16px;
        }

        .select2-container--bootstrap4 .select2-selection__choice__remove:hover {
            color: #f0f0f0;
        }

        .select2-dropdown {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-top: 4px;
        }

        .select2-container--bootstrap4 .select2-results__option {
            padding: 12px 16px;
            font-size: 15px;
            line-height: 1.5;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: #2D6BCF;
            color: white;
        }

        .select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
            background-color: #eef2ff;
            color: #2D6BCF;
        }

        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            font-size: 15px;
            margin: 8px;
            width: calc(100% - 16px);
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            padding-left: 0;
            line-height: 32px;
        }

        .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #9ca3af;
            font-size: 15px;
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 46px;
            right: 12px;
        }

        .select2-container--bootstrap4 .select2-selection__arrow b {
            border-color: #2D6BCF transparent transparent transparent;
            border-width: 6px 5px 0 5px;
            margin-top: -3px;
        }

        @media (max-width: 768px) {
            .select2-container--open .select2-dropdown {
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 90vw !important;
                max-width: 400px;
                max-height: 70vh;
                overflow-y: auto;
                z-index: 99999 !important;
                border-radius: 15px !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .select2-container--open .select2-dropdown-mobile {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            body.select2-dropdown-open {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
                touch-action: none;
            }

            .select2-dropdown-mobile {
                position: fixed !important;
                transform: translate(-50%, -50%) !important;
            }

            .select2-container--open .select2-dropdown {
                border-radius: 15px !important;
            }

            .select2-results {
                max-height: 60vh;
                overflow-y: auto;
            }

            .select2-container--bootstrap4 .select2-results__option {
                padding: 14px 18px;
                font-size: 16px;
                min-height: 48px;
                display: flex;
                align-items: center;
            }

            .select2-container--bootstrap4 .select2-selection__choice {
                padding: 8px 14px;
                margin: 3px 3px 3px 0;
                font-size: 14px;
                min-height: 36px;
                display: inline-flex;
                align-items: center;
            }

            .select2-container--bootstrap4 .select2-selection__choice__remove {
                font-size: 18px;
                margin-right: 8px;
                padding: 0;
                width: 20px;
                height: 20px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
                padding: 12px;
                font-size: 16px;
                margin: 10px;
                min-height: 48px;
            }
        }

        .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 48px;
            padding: 4px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            padding: 0;
            margin: 0;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered li {
            margin: 0;
        }

        .select2-dropdown-mobile {
            position: fixed !important;
        }

        .select2-container--bootstrap4 .select2-selection {
            will-change: contents;
        }
    </style>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div style="text-align: center;">
            <div class="spinner"></div>
            <p style="color: white; margin-top: 15px;">Menyimpan data...</p>
        </div>
    </div>

    <div class="top-bar">
        <div style="font-size: 18px; font-weight: 600;">
            🏥 Form Sampling Klinik
            @if (isset($is_resampling) && $is_resampling)
                <span
                    style="background: rgba(255,255,255,0.3); padding: 4px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">
                    Resampling ke-{{ $count }}
                </span>
            @else
                <span
                    style="background: rgba(255,255,255,0.3); padding: 4px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">
                    Sampling ke-{{ $count }}
                </span>
            @endif
        </div>
        <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">Petugas: {{ $petugas_name }}</div>
    </div>

    <div class="container">
        <!-- Info Permohonan -->
        <div class="info-box">
            <strong>📋 No. Register:</strong> {{ $permohonan_uji_klinik->getDisplayNoregister() }}<br>
            <strong>👤 Nama Pasien:</strong> {{ $permohonan_uji_klinik->pasien->nama_pasien ?? '-' }}<br>
            <strong>📅 Tgl. Register:</strong> {{ $tgl_register }}
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if (isset($is_resampling) && $is_resampling)
            <div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">⚠️</span>
                    <div>
                        <strong style="color: #856404;">Resampling ke-{{ $count }}</strong>
                        <p style="margin: 5px 0 0 0; color: #856404; font-size: 13px;">
                            Ini adalah pengambilan sample ulang. Pastikan data yang diisi sesuai dengan kondisi terbaru.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form id="samplingForm" method="POST"
            action="{{ route('mobile.sampling.klinik.store', $permohonan_uji_klinik->id_permohonan_uji_klinik) }}">
            @csrf
            <input type="hidden" name="count" value="{{ $count }}">
            <input type="hidden" name="is_resampling" value="{{ isset($is_resampling) && $is_resampling ? 1 : 0 }}">

            <!-- Data Pasien -->
            <div class="card">
                <div class="section-title">
                    <i class="fa fa-user"></i> Data Pasien
                </div>
                <div class="info-item">
                    <label>No. Rekam Medis:</label>
                    <span>{{ Carbon\Carbon::createFromFormat('Y-m-d', $permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <label>Usia:</label>
                    <span>{{ $permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik }} tahun
                        {{ $permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik }} bulan
                        {{ $permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik }} hari</span>
                </div>
                <div class="info-item">
                    <label>Jenis Kelamin:</label>
                    <span>{{ $permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div class="info-item">
                    <label>No. Telepon:</label>
                    <span>{{ $permohonan_uji_klinik->pasien->phone_pasien ?? '-' }}</span>
                </div>
            </div>

            <!-- List Parameter -->
            @if (!empty($parameters_list) && count($parameters_list) > 0)
            <div class="card">
                <div class="section-title">
                    <i class="fa fa-list"></i> Daftar Parameter Uji
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; padding: 5px;">
                        @foreach ($parameters_list as $param)
                            <div style="padding: 8px 10px; background: #f8f9fa; border-radius: 6px; color: #212529; font-size: 13px; text-align: center; border: 1px solid #e9ecef;">
                                {{ $param->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 6px; font-size: 12px; color: #6c757d; text-align: center;">
                    Total: <strong>{{ count($parameters_list) }}</strong> parameter
                </div>
            </div>
            @endif

            <!-- Step 1: Tanda Tangan -->
            <div class="card">
                <div class="section-title">
                    <i class="fa fa-pencil"></i> Tanda Tangan
                </div>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="button" class="btn btn-warning" id="btnTTD" style="flex: 1; padding: 12px;">
                        <span>✍️</span> Tanda Tangan
                    </button>
                </div>
                <div
                    style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 8px; font-size: 12px; color: #0066cc;">
                    <strong>💡 Info:</strong> Silakan isi tanda tangan terlebih dahulu sebelum melanjutkan ke langkah
                    berikutnya.
                </div>
            </div>

            <!-- Step 2: Data Sampling (Kondisi Pasien & Status Sampling) -->
            <div class="card">
                <div class="section-title">
                    <i class="fa fa-flask"></i> Data Sampling
                </div>

                <input type="hidden" name="tgl_sampling" id="tgl_sampling"
                    value="{{ $tgl_sampling ?? \Carbon\Carbon::now()->format('Y-m-d') }}">

                <div class="form-group">
                    <label for="tindakan_medis_khusus">TINDAKAN MEDIS KHUSUS</label>
                    @php
                        // Handle berbagai format data dari backend
                        $tindakan_raw = $tindakan_medis_khusus ?? null;
                        $tindakan_default = [];
                        
                        if (!empty($tindakan_raw)) {
                            if (is_array($tindakan_raw)) {
                                // Sudah array
                                $tindakan_default = $tindakan_raw;
                            } elseif (is_string($tindakan_raw)) {
                                // Cek apakah JSON
                                $decoded = json_decode($tindakan_raw, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $tindakan_default = $decoded;
                                } else {
                                    // Comma-separated string
                                    $tindakan_default = array_filter(array_map('trim', explode(',', $tindakan_raw)));
                                }
                            }
                        }
                        
                        $tindakan_display_text = !empty($tindakan_default) ? implode(', ', $tindakan_default) : '';
                    @endphp
                    <div class="inline-edit-wrapper">
                        <span class="inline-edit-display {{ empty($tindakan_display_text) ? 'empty' : '' }}"
                            id="tindakan_display">
                            {{ $tindakan_display_text ?: 'Belum dipilih' }}
                        </span>
                        <span class="inline-edit-btn" id="tindakan_edit_btn" title="Edit">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </span>
                        <div class="inline-edit-input" id="tindakan_input_wrapper">
                            <select class="form-control" name="tindakan_medis_khusus[]" id="tindakan_medis_khusus" multiple>
                                @foreach (['Pengambilan Darah Vena', 'Pengumpulan Urin Spontan', 'Pengumpulan Feses Spontan', 'Pengambilan Swab Rektal', 'Lainnya'] as $option)
                                    <option value="{{ $option }}"
                                        {{ in_array($option, $tindakan_default) ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="jenis_sampel">JENIS SAMPEL <span class="required">*</span></label>
                    @php
                        $jenis_default = !empty($jenis_sampel) && is_array($jenis_sampel) ? $jenis_sampel : [];
                        $jenis_display_text =
                            !empty($jenis_default) && is_array($jenis_default) ? implode(', ', $jenis_default) : '';
                    @endphp
                    <div class="inline-edit-wrapper">
                        <span class="inline-edit-display {{ empty($jenis_display_text) ? 'empty' : '' }}"
                            id="jenis_sampel_display">
                            {{ $jenis_display_text ?: 'Belum dipilih' }}
                        </span>
                        <span class="inline-edit-btn" id="jenis_sampel_edit_btn" title="Edit">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </span>
                        <div class="inline-edit-input" id="jenis_sampel_input_wrapper">
                            <select class="form-control" name="jenis_sampel[]" id="jenis_sampel" multiple required>
                                @php
                                    $jenis_sampel_options = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra(
                                        is_array($jenis_sampel ?? null) ? $jenis_sampel : []
                                    );
                                @endphp
                                @foreach ($jenis_sampel_options as $option)
                                    <option value="{{ $option }}"
                                        {{ is_array($jenis_sampel ?? null) && in_array($option, $jenis_sampel ?? [], true) ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kondisi_pasien">KONDISI PASIEN (demam, puasa 12 jam, dll) <span
                            class="required">*</span></label>
                    <textarea class="form-control" name="kondisi_pasien" id="kondisi_pasien" required rows="3"
                        placeholder="Masukkan kondisi pasien (contoh: demam, puasa 12 jam, dll)">{{ $kondisi_pasien ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label for="status_sampling">STATUS SAMPLING <span class="required">*</span></label>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input status-sampling-radio"
                                name="status_sampling" id="status_sampling_berhasil" value="Berhasil" required
                                {{ ($status_sampling ?? '') == 'Berhasil' ? 'checked' : '' }}>
                            Berhasil
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input status-sampling-radio"
                                name="status_sampling" id="status_sampling_gagal" value="Gagal" required
                                {{ ($status_sampling ?? '') == 'Gagal' ? 'checked' : '' }}>
                            Gagal
                        </label>
                    </div>
                </div>

                <div id="resampling-section" class="resampling-section" style="display:none;">
                    <h6 style="margin-bottom: 10px; font-size: 14px;">Sampling Ulang</h6>
                    <div class="form-group">
                        <label>Alasan Gagal</label>
                        <input type="text" class="form-control" name="resample_reason"
                            value="{{ $resample_reason ?? '' }}" id="resample_reason"
                            placeholder="Misal: vena sulit, pasien gelisah">
                    </div>
                </div>
            </div>

            <!-- Step 3: Pengambil Sample (Jam Sampling & Petugas) -->
            <div class="card">
                <div class="section-title">
                    <i class="fa fa-user-md"></i> Pengambil Sample
                </div>

                <div class="form-group">
                    <label for="jam_sampling_display">JAM SAMPLING <span class="required">*</span></label>
                    <input type="text" class="form-control" name="jam_sampling_display" id="jam_sampling_display"
                        value="{{ $jam_sampling ?? \Carbon\Carbon::now()->format('H:i') }}" required
                        placeholder="Pilih jam sampling" readonly>
                    <input type="hidden" name="jam_sampling" id="jam_sampling_hidden"
                        value="{{ $jam_sampling ?? \Carbon\Carbon::now()->format('H:i') }}">
                </div>

                <div class="form-group">
                    <label for="nama_petugas_pengambil">NAMA PENGAMBIL SAMPLE <span class="required">*</span></label>
                    <select class="form-control" name="nama_petugas_pengambil" id="nama_petugas_pengambil" required>
                        <option value="">Pilih Pengambil Sample</option>
                        @foreach ($list_petugas as $petugas)
                            <option value="{{ $petugas }}"
                                {{ ($verification_sample->nama_petugas ?? '') == $petugas ? 'selected' : '' }}>
                                {{ $petugas }}
                            </option>
                        @endforeach
                        @if (!empty($petugas_name) && !in_array($petugas_name, $list_petugas))
                            <option value="{{ $petugas_name }}" selected>{{ $petugas_name }} (User Login)
                            </option>
                        @endif
                    </select>
                </div>
            </div>
        </form>

        <!-- Modal Tanda Tangan -->
        <div id="signatureModal"
            style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 10000; padding: 20px; overflow-y: auto; pointer-events: auto;">
            <div
                style="background: white; border-radius: 15px; padding: 20px; max-width: 500px; margin: 0 auto; margin-top: 20px; pointer-events: auto; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Tanda Tangan</h3>
                    <button type="button" id="closeSignatureModalX" class="close-signature-modal"
                        style="background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <div class="form-group">
                    <label>Tanda Tangan Pasien/Wali</label>
                    <div style="position: relative; border: 2px solid #ddd; border-radius: 8px; background: white;">
                        <canvas id="signaturePadPasien"
                            style="display: block; width: 100%; height: 150px; touch-action: none; -webkit-tap-highlight-color: transparent;"></canvas>
                    </div>
                    <button type="button" id="clearPasien"
                        style="margin-top: 10px; padding: 8px 15px; background: #f0f0f0; border: none; border-radius: 8px; cursor: pointer; width: 100%;">Hapus
                        Tanda Tangan Pasien</button>
                </div>

                <div class="form-group">
                    <label>Tanda Tangan Petugas</label>
                    <div style="position: relative; border: 2px solid #ddd; border-radius: 8px; background: white;">
                        <canvas id="signaturePadPetugas"
                            style="display: block; width: 100%; height: 150px; touch-action: none; -webkit-tap-highlight-color: transparent;"></canvas>
                    </div>
                    <button type="button" id="clearPetugas"
                        style="margin-top: 10px; padding: 8px 15px; background: #f0f0f0; border: none; border-radius: 8px; cursor: pointer; width: 100%;">Hapus
                        Tanda Tangan Petugas</button>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" id="closeSignatureModalBtn" class="btn btn-primary close-signature-modal"
                        style="flex: 1; padding: 12px; cursor: pointer;">
                        <span>✓</span> Tutup
                    </button>
                </div>
                <div
                    style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 8px; font-size: 12px; color: #0066cc;">
                    <strong>💡 Info:</strong> Tanda tangan akan otomatis disimpan saat Anda klik "Simpan Data Sampling"
                    di bawah.
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Submit Button -->
    <div class="floating-submit">
        <button type="button" class="btn-submit" id="btnSubmit">
            💾 Simpan Data Sampling
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"
        onerror="console.error('Failed to load SignaturePad from CDN')"></script>
    <script>
        // Check if SignaturePad is loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                const SignaturePad = window.SignaturePad || (window.signaturePad && (window.signaturePad
                    .SignaturePad || window.signaturePad.default));
                if (SignaturePad) {
                    console.log('SignaturePad library is ready');
                } else {
                    console.warn('SignaturePad library may not be loaded yet');
                }
            }, 1000);
        });

        // Ensure SignaturePad is available globally
        window.waitForSignaturePad = function(callback) {
            if (window.SignaturePad) {
                callback(window.SignaturePad);
            } else if (window.signaturePad) {
                const SignaturePad = window.signaturePad.SignaturePad || window.signaturePad.default;
                if (SignaturePad) {
                    callback(SignaturePad);
                } else {
                    setTimeout(function() {
                        window.waitForSignaturePad(callback);
                    }, 100);
                }
            } else {
                setTimeout(function() {
                    window.waitForSignaturePad(callback);
                }, 100);
            }
        };
    </script>
    <script>
        $(document).ready(function() {
            // Set initial value for jenis_sampel (Select2 will be initialized lazily on edit)
            @if (!empty($jenis_sampel) && is_array($jenis_sampel))
                $("#jenis_sampel").val(@json($jenis_sampel));
            @endif

            // Set initial value for tindakan_medis_khusus (Select2 will be initialized lazily on edit)
            @if (!empty($tindakan_default) && is_array($tindakan_default))
                $("#tindakan_medis_khusus").val(@json($tindakan_default));
            @endif

            // Function to disable form inputs (Step 2 dan Step 3) - harus didefinisikan dulu
            function disableFormInputs() {
                // Disable semua input di Step 2: Data Sampling (kecuali select inline edit)
                // Jangan disable select karena akan mengganggu Select2
                // $('#tindakan_medis_khusus').prop('disabled', true);
                // $('#jenis_sampel').prop('disabled', true);
                $('#tindakan_medis_khusus').addClass('disabled-field');
                $('#jenis_sampel').addClass('disabled-field');
                $('#kondisi_pasien').prop('disabled', true);
                $('#status_sampling_berhasil').prop('disabled', true);
                $('#status_sampling_gagal').prop('disabled', true);
                $('#resample_reason').prop('disabled', true);

                // Disable semua input di Step 3: Pengambil Sample
                $('#jam_sampling_display').prop('disabled', true);
                $('#nama_petugas_pengambil').prop('disabled', true);

                // Disable tombol submit
                $('#btnSubmit').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');

                // JANGAN disable tombol edit inline - biarkan bisa diklik
                // User tetap bisa edit, tapi tidak bisa submit sebelum TTD
                // $('.inline-edit-btn').css('pointer-events', 'none').css('opacity', '0.5');

                // Tambahkan overlay atau pesan info (tapi tidak menutupi tombol edit)
                $('.card').each(function() {
                    const sectionTitle = $(this).find('.section-title').text() || '';
                    if (sectionTitle.includes('Data Sampling') || sectionTitle.includes(
                            'Pengambil Sample')) {
                        $(this).css('position', 'relative');
                        if ($(this).find('.form-disabled-overlay').length === 0) {
                            // Add overlay with pointer-events: none so edit buttons can still be clicked
                            $(this).append(
                                '<div class="form-disabled-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.7); z-index: 5; pointer-events: none; display: flex; align-items: flex-start; justify-content: center; border-radius: 14px; padding-top: 60px;"><div style="text-align: center; padding: 15px 20px; color: #666; background: rgba(255,255,255,0.95); border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"><i class="fa fa-lock" style="font-size: 20px; margin-bottom: 8px;"></i><br><small>Silakan isi tanda tangan terlebih dahulu</small></div></div>'
                            );
                        }
                    }
                });
            }

            // Function to enable form inputs (Step 2 dan Step 3)
            function enableFormInputs() {
                // Enable semua input di Step 2: Data Sampling
                $('#tindakan_medis_khusus').removeClass('disabled-field');
                $('#jenis_sampel').removeClass('disabled-field');
                $('#kondisi_pasien').prop('disabled', false);
                $('#status_sampling_berhasil').prop('disabled', false);
                $('#status_sampling_gagal').prop('disabled', false);
                $('#resample_reason').prop('disabled', false);

                // Enable semua input di Step 3: Pengambil Sample
                $('#jam_sampling_display').prop('disabled', false);
                $('#nama_petugas_pengambil').prop('disabled', false);

                // Enable tombol submit
                $('#btnSubmit').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');

                // Tombol edit inline sudah selalu enabled, tidak perlu di-enable lagi
                // $('.inline-edit-btn').css('pointer-events', 'auto').css('opacity', '1');

                // Hapus overlay
                $('.form-disabled-overlay').remove();
            }

            // Cek apakah TTD sudah ada (untuk mode edit)
            function checkInitialSignatureStatus() {
                @if (isset($pengambilan_sample) && $pengambilan_sample)
                    @if (
                        !empty($pengambilan_sample->signature_pengambil_sample_pasien) &&
                            !empty($pengambilan_sample->signature_pengambil_sample_petugas))
                        enableFormInputs();
                        $('#btnTTD').removeClass('btn-warning').addClass('btn-success');
                        $('#btnTTD').html('<span>✓</span> Tanda Tangan (Sudah Diisi)');
                    @else
                        disableFormInputs();
                    @endif
                @else
                    disableFormInputs();
                @endif
            }

            // Cek status TTD saat halaman pertama kali dimuat
            checkInitialSignatureStatus();

            // Inline Edit for Tindakan Medis Khusus
            let tindakanSelect2Initialized = false;

            function initTindakanEdit() {
                const $display = $('#tindakan_display');
                const $inputWrapper = $('#tindakan_input_wrapper');
                const $select = $('#tindakan_medis_khusus');
                const $btn = $('#tindakan_edit_btn');

                function updateDisplay() {
                    const vals = $select.val() || [];
                    const text = vals.length > 0 ? vals.join(', ') : 'Belum dipilih';
                    $display.text(text).toggleClass('empty', vals.length === 0);
                }

                // Ensure display is visible and input is hidden on init
                $display.removeClass('hidden');
                $inputWrapper.removeClass('active');

                $btn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Prevent layout shift - save current height
                    const currentHeight = $inputWrapper.height() || 48;
                    $inputWrapper.css({
                        'min-height': currentHeight + 'px',
                        'height': 'auto'
                    });

                    $display.addClass('hidden');
                    $inputWrapper.addClass('active');

                    // Force reflow to prevent jump
                    $inputWrapper[0].offsetHeight;

                    if (!tindakanSelect2Initialized) {
                        // Initialize Select2
                        const $body = $('body');
                        $select.select2({
                            placeholder: 'Pilih tindakan medis khusus (bisa lebih dari satu)',
                            allowClear: true,
                            multiple: true,
                            closeOnSelect: false,
                            width: '100%',
                            dropdownParent: $body,
                            dropdownAutoWidth: false
                        });

                        // Set initial value after Select2 is initialized
                        @if (!empty($tindakan_default) && is_array($tindakan_default))
                            $select.val(@json($tindakan_default)).trigger('change');
                        @endif

                        // Prevent scroll when dropdown opens
                        $select.on('select2:open', function() {
                            $('body').addClass('select2-dropdown-open');
                            const scrollY = window.scrollY || window.pageYOffset;
                            $('body').css({
                                'position': 'fixed',
                                'top': `-${scrollY}px`,
                                'width': '100%'
                            });
                        });

                        // Restore scroll when dropdown closes
                        $select.on('select2:close', function() {
                            $('body').removeClass('select2-dropdown-open');
                            const scrollY = $('body').css('top');
                            $('body').css({
                                'position': '',
                                'top': '',
                                'width': ''
                            });
                            if (scrollY) {
                                window.scrollTo(0, parseInt(scrollY || '0') * -1);
                            }
                        });

                        tindakanSelect2Initialized = true;
                    }

                    // Open dropdown after a short delay
                    setTimeout(() => {
                        $select.select2('open');
                        const $dropdown = $('.select2-dropdown');
                        if ($dropdown.length) {
                            $dropdown.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'opacity': '1'
                            });
                        }
                    }, 150);
                });

                $select.on('change', function() {
                    updateDisplay();
                    setTimeout(() => {
                        if (!$inputWrapper.is(':hover')) {
                            $display.removeClass('hidden');
                            $inputWrapper.removeClass('active');
                        }
                    }, 300);
                });

                // Close when select2 closes
                $(document).on('select2:close', '#tindakan_medis_khusus', function() {
                    $('body').removeClass('select2-dropdown-open');
                    const scrollY = $('body').css('top');
                    $('body').css('top', '');
                    if (scrollY) {
                        window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    }

                    setTimeout(() => {
                        updateDisplay();
                        $display.removeClass('hidden');
                        $inputWrapper.removeClass('active');
                        $inputWrapper.css('min-height', '');
                    }, 200);
                });

                // Initial display update
                updateDisplay();
            }

            // Inline Edit for Jenis Sampel
            let jenisSelect2Initialized = false;

            function initJenisSampelEdit() {
                const $display = $('#jenis_sampel_display');
                const $inputWrapper = $('#jenis_sampel_input_wrapper');
                const $select = $('#jenis_sampel');
                const $btn = $('#jenis_sampel_edit_btn');

                function updateDisplay() {
                    const vals = $select.val() || [];
                    const text = vals.length > 0 ? vals.join(', ') : 'Belum dipilih';
                    $display.text(text).toggleClass('empty', vals.length === 0);
                }

                // Ensure display is visible and input is hidden on init
                $display.removeClass('hidden');
                $inputWrapper.removeClass('active');

                $btn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Prevent layout shift - save current height
                    const currentHeight = $inputWrapper.height() || 48;
                    $inputWrapper.css({
                        'min-height': currentHeight + 'px',
                        'height': 'auto'
                    });

                    $display.addClass('hidden');
                    $inputWrapper.addClass('active');

                    // Force reflow to prevent jump
                    $inputWrapper[0].offsetHeight;

                    if (!jenisSelect2Initialized) {
                        // Initialize Select2 - use simple theme without bootstrap4
                        const $body = $('body');
                        $select.select2({
                            placeholder: 'Pilih jenis sampel (bisa lebih dari satu)',
                            allowClear: true,
                            multiple: true,
                            closeOnSelect: false,
                            width: '100%',
                            dropdownParent: $body,
                            dropdownAutoWidth: false
                        });

                        // Set initial value after Select2 is initialized
                        @if (!empty($jenis_sampel) && is_array($jenis_sampel))
                            $select.val(@json($jenis_sampel)).trigger('change');
                        @endif

                        // Prevent scroll when dropdown opens
                        $select.on('select2:open', function() {
                            $('body').addClass('select2-dropdown-open');
                            const scrollY = window.scrollY || window.pageYOffset;
                            $('body').css({
                                'position': 'fixed',
                                'top': `-${scrollY}px`,
                                'width': '100%'
                            });
                        });

                        // Restore scroll when dropdown closes
                        $select.on('select2:close', function() {
                            $('body').removeClass('select2-dropdown-open');
                            const scrollY = $('body').css('top');
                            $('body').css({
                                'position': '',
                                'top': '',
                                'width': ''
                            });
                            if (scrollY) {
                                window.scrollTo(0, parseInt(scrollY || '0') * -1);
                            }
                        });

                        jenisSelect2Initialized = true;
                    }

                    // Open dropdown after a short delay to ensure it's ready
                    setTimeout(() => {
                        try {
                            $select.select2('open');
                            const $dropdown = $('.select2-dropdown');
                            if ($dropdown.length) {
                                $dropdown.css({
                                    'display': 'block',
                                    'visibility': 'visible',
                                    'opacity': '1'
                                });
                            }
                        } catch (e) {
                            console.error('Error opening Select2:', e);
                            setTimeout(() => {
                                $select.select2('open');
                            }, 200);
                        }
                    }, 150);
                });

                $select.on('change', function() {
                    updateDisplay();
                    setTimeout(() => {
                        if (!$inputWrapper.is(':hover')) {
                            $display.removeClass('hidden');
                            $inputWrapper.removeClass('active');
                        }
                    }, 300);
                });

                // Close when select2 closes
                $(document).on('select2:close', '#jenis_sampel', function() {
                    $('body').removeClass('select2-dropdown-open');
                    const scrollY = $('body').css('top');
                    $('body').css('top', '');
                    if (scrollY) {
                        window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    }

                    setTimeout(() => {
                        updateDisplay();
                        $display.removeClass('hidden');
                        $inputWrapper.removeClass('active');
                        $inputWrapper.css('min-height', '');
                    }, 200);
                });

                // Initial display update
                updateDisplay();
            }

            // Check if FontAwesome is loaded and hide unicode fallback
            function checkFontAwesome() {
                const testIcon = document.createElement('i');
                testIcon.className = 'fa fa-pencil';
                testIcon.style.position = 'absolute';
                testIcon.style.visibility = 'hidden';
                document.body.appendChild(testIcon);

                const computedStyle = window.getComputedStyle(testIcon, ':before');
                const content = computedStyle.getPropertyValue('content');
                document.body.removeChild(testIcon);

                if (content && content !== 'none' && content !== '""' && content !== "''") {
                    $('.inline-edit-btn > span[style*="margin-left"]').hide();
                    $('.inline-edit-btn').addClass('fa-loaded');
                }
            }

            // Initialize inline edits
            setTimeout(function() {
                checkFontAwesome();
                initTindakanEdit();
                initJenisSampelEdit();
            }, 300);

            // Toggle resampling section and tombol selesai
            function toggleResample() {
                const gagal = $("#status_sampling_gagal").is(':checked');
                const berhasil = $("#status_sampling_berhasil").is(':checked');

                $('#resampling-section').css('display', gagal ? 'block' : 'none');
                if (gagal) {
                    $('#resample_reason').prop('required', true);
                } else {
                    $('#resample_reason').prop('required', false);
                }

                if (berhasil) {
                    $('#btnSelesai').show();
                } else {
                    $('#btnSelesai').hide();
                }
            }
            $('.status-sampling-radio').on('change', toggleResample);
            toggleResample();

            // Initialize Flatpickr for time picker 24 jam
            @php
                $jam_sampling_default = $jam_sampling ?? \Carbon\Carbon::now()->format('H:i');
                $jam_sampling_flatpickr = \Carbon\Carbon::now()->format('Y-m-d') . ' ' . $jam_sampling_default;
            @endphp
            const jamSamplingPicker = flatpickr('#jam_sampling_display', {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                defaultDate: "{{ $jam_sampling_flatpickr }}",
                locale: "id",
                allowInput: false,
                clickOpens: true,
                minuteIncrement: 1,
                onChange: function(selectedDates, dateStr, instance) {
                    $('#jam_sampling_hidden').val(dateStr);
                },
                onReady: function(selectedDates, dateStr, instance) {
                    $('#jam_sampling_hidden').val(dateStr);
                }
            });

            // Initialize Signature Pads
            let signaturePadPasien, signaturePadPetugas;
            let signatureModal = document.getElementById('signatureModal');
            let isSignatureInitialized = false;

            function resizeCanvas(canvas) {
                if (!canvas) return;

                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                const width = rect.width || canvas.offsetWidth || 400;
                const height = rect.height || canvas.offsetHeight || 150;

                canvas.width = width * ratio;
                canvas.height = height * ratio;

                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';

                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.clearRect(0, 0, width, height);
            }

            function initSignaturePads() {
                if (signaturePadPasien) {
                    try {
                        signaturePadPasien.clear();
                        signaturePadPasien.off();
                    } catch (e) {
                        console.warn('Error clearing pasien pad:', e);
                    }
                }
                if (signaturePadPetugas) {
                    try {
                        signaturePadPetugas.clear();
                        signaturePadPetugas.off();
                    } catch (e) {
                        console.warn('Error clearing petugas pad:', e);
                    }
                }

                const canvasPasien = document.getElementById('signaturePadPasien');
                const canvasPetugas = document.getElementById('signaturePadPetugas');

                if (!canvasPasien || !canvasPetugas) {
                    console.error('Canvas elements not found');
                    return;
                }

                window.waitForSignaturePad(function(SignaturePad) {
                    if (!SignaturePad) {
                        console.error('SignaturePad library not available');
                        swal({
                            title: "Error!",
                            text: "Library tanda tangan tidak dapat dimuat. Silakan refresh halaman.",
                            icon: "error"
                        });
                        return;
                    }

                    setTimeout(function() {
                        resizeCanvas(canvasPasien);
                        resizeCanvas(canvasPetugas);

                        setTimeout(function() {
                            try {
                                resizeCanvas(canvasPasien);
                                resizeCanvas(canvasPetugas);

                                signaturePadPasien = new SignaturePad(canvasPasien, {
                                    backgroundColor: 'rgba(255, 255, 255, 0)',
                                    penColor: 'rgb(0, 0, 0)',
                                    minWidth: 1,
                                    maxWidth: 3,
                                    velocityFilterWeight: 0.7,
                                    throttle: 16
                                });

                                signaturePadPetugas = new SignaturePad(canvasPetugas, {
                                    backgroundColor: 'rgba(255, 255, 255, 0)',
                                    penColor: 'rgb(0, 0, 0)',
                                    minWidth: 1,
                                    maxWidth: 3,
                                    velocityFilterWeight: 0.7,
                                    throttle: 16
                                });

                                canvasPasien.style.touchAction = 'none';
                                canvasPasien.style.pointerEvents = 'auto';
                                canvasPasien.style.webkitTouchCallout = 'none';
                                canvasPasien.style.webkitUserSelect = 'none';
                                canvasPasien.style.userSelect = 'none';
                                canvasPasien.style.msTouchAction = 'none';

                                canvasPetugas.style.touchAction = 'none';
                                canvasPetugas.style.pointerEvents = 'auto';
                                canvasPetugas.style.webkitTouchCallout = 'none';
                                canvasPetugas.style.webkitUserSelect = 'none';
                                canvasPetugas.style.userSelect = 'none';
                                canvasPetugas.style.msTouchAction = 'none';

                                canvasPasien.addEventListener('touchstart', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPasien.addEventListener('touchmove', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPasien.addEventListener('touchend', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });

                                canvasPetugas.addEventListener('touchstart', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPetugas.addEventListener('touchmove', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPetugas.addEventListener('touchend', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });

                                if (signaturePadPasien) {
                                    signaturePadPasien.addEventListener('endStroke',
                                        function() {
                                            updateSignatureStatus();
                                        });
                                }
                                if (signaturePadPetugas) {
                                    signaturePadPetugas.addEventListener('endStroke',
                                        function() {
                                            updateSignatureStatus();
                                        });
                                }

                                isSignatureInitialized = true;
                                console.log('SignaturePad initialized successfully');
                            } catch (e) {
                                console.error('Error initializing SignaturePad:', e);
                                swal({
                                    title: "Error!",
                                    text: "Gagal menginisialisasi tanda tangan: " +
                                        e.message,
                                    icon: "error"
                                });
                            }
                        }, 200);
                    }, 150);
                });
            }

            // Open signature modal
            $('#btnTTD').on('click', function() {
                signatureModal.style.display = 'block';
                isSignatureInitialized = false;
                signatureModal.offsetHeight;

                setTimeout(function() {
                    window.waitForSignaturePad(function(SignaturePad) {
                        if (!SignaturePad) {
                            console.error('SignaturePad still not found');
                            swal({
                                title: "Error!",
                                text: "Library tanda tangan tidak dapat dimuat. Silakan refresh halaman atau pastikan koneksi internet aktif.",
                                icon: "error"
                            });
                            signatureModal.style.display = 'none';
                            return;
                        }

                        console.log('SignaturePad found, initializing...');
                        initSignaturePads();
                    });
                }, 300);
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (signatureModal.style.display === 'block' && isSignatureInitialized) {
                    if (signaturePadPasien) {
                        resizeCanvas(document.getElementById('signaturePadPasien'));
                        signaturePadPasien.clear();
                    }
                    if (signaturePadPetugas) {
                        resizeCanvas(document.getElementById('signaturePadPetugas'));
                        signaturePadPetugas.clear();
                    }
                    setTimeout(initSignaturePads, 100);
                }
            });

            // Close signature modal
            $(document).on('click', '.close-signature-modal, #closeSignatureModalX, #closeSignatureModalBtn',
                function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (signatureModal) {
                        signatureModal.style.display = 'none';
                        updateSignatureStatus();
                    }
                    return false;
                });

            if (signatureModal) {
                $(signatureModal).on('click', function(e) {
                    if (e.target === signatureModal) {
                        signatureModal.style.display = 'none';
                        updateSignatureStatus();
                    }
                });
            }

            // Clear signatures
            $('#clearPasien').on('click', function() {
                if (signaturePadPasien) signaturePadPasien.clear();
                updateSignatureStatus();
            });

            $('#clearPetugas').on('click', function() {
                if (signaturePadPetugas) signaturePadPetugas.clear();
                updateSignatureStatus();
            });

            // Function to save signatures
            function saveSignatures(callback) {
                if (!isSignatureInitialized || !signaturePadPasien || !signaturePadPetugas) {
                    if (typeof callback === 'function') {
                        callback(true, null);
                    }
                    return;
                }

                let signaturePasien = signaturePadPasien && !signaturePadPasien.isEmpty() ?
                    signaturePadPasien.toDataURL() :
                    null;
                let signaturePetugas = signaturePadPetugas && !signaturePadPetugas.isEmpty() ?
                    signaturePadPetugas.toDataURL() :
                    null;

                if (!signaturePasien && !signaturePetugas) {
                    if (typeof callback === 'function') {
                        callback(true, null);
                    }
                    return;
                }

                $.ajax({
                    url: '{{ route('mobile.sampling.klinik.saveSignature', $permohonan_uji_klinik->id_permohonan_uji_klinik) }}',
                    method: 'POST',
                    data: {
                        signature_pasien: signaturePasien,
                        signature_petugas: signaturePetugas,
                        sampling: {{ $count - 1 }},
                        count: {{ $count }},
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            console.log('Signatures saved successfully');
                            updateSignatureStatus();
                            if (typeof callback === 'function') {
                                callback(true, response);
                            }
                        } else {
                            console.error('Failed to save signatures:', response.pesan);
                            if (typeof callback === 'function') {
                                callback(false, response);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Save signature error:', xhr);
                        if (typeof callback === 'function') {
                            callback(true, null);
                        }
                    }
                });
            }

            // Function to check if signatures are filled
            function checkSignaturesFilled() {
                if (!isSignatureInitialized || !signaturePadPasien || !signaturePadPetugas) {
                    return false;
                }
                const pasienFilled = !signaturePadPasien.isEmpty();
                const petugasFilled = !signaturePadPetugas.isEmpty();
                return pasienFilled && petugasFilled;
            }

            // Function to update signature status indicator
            function updateSignatureStatus() {
                const isFilled = checkSignaturesFilled();
                const btnTTD = $('#btnTTD');
                if (isFilled) {
                    btnTTD.removeClass('btn-warning').addClass('btn-success');
                    btnTTD.html('<span>✓</span> Tanda Tangan (Sudah Diisi)');
                    enableFormInputs();
                } else {
                    btnTTD.removeClass('btn-success').addClass('btn-warning');
                    btnTTD.html('<span>✍️</span> Tanda Tangan');
                    disableFormInputs();
                }
            }

            // Function to save sampling data
            function saveSamplingData(callback) {
                if (!checkSignaturesFilled()) {
                    swal({
                        title: "Error!",
                        text: "Tanda tangan pasien dan petugas wajib diisi terlebih dahulu!",
                        icon: "warning"
                    });
                    return false;
                }

                if (!$('#kondisi_pasien').val().trim()) {
                    swal({
                        title: "Error!",
                        text: "Kondisi pasien wajib diisi!",
                        icon: "warning"
                    });
                    return false;
                }

                if (!$('#jenis_sampel').val() || $('#jenis_sampel').val().length === 0) {
                    swal({
                        title: "Error!",
                        text: "Jenis sampel wajib dipilih!",
                        icon: "warning"
                    });
                    return false;
                }

                if (!$('input[name="status_sampling"]:checked').val()) {
                    swal({
                        title: "Error!",
                        text: "Status sampling wajib dipilih!",
                        icon: "warning"
                    });
                    return false;
                }

                if ($('#status_sampling_gagal').is(':checked') && !$('#resample_reason').val().trim()) {
                    swal({
                        title: "Error!",
                        text: "Alasan gagal wajib diisi!",
                        icon: "warning"
                    });
                    return false;
                }

                if (!$('#jam_sampling_display').val()) {
                    swal({
                        title: "Error!",
                        text: "Jam sampling wajib diisi!",
                        icon: "warning"
                    });
                    return false;
                }

                if (!$('#nama_petugas_pengambil').val()) {
                    swal({
                        title: "Error!",
                        text: "Nama pengambil sample wajib dipilih!",
                        icon: "warning"
                    });
                    return false;
                }

                saveSignatures(function(sigSuccess, sigResponse) {
                    if (!sigSuccess) {
                        console.warn('Signature save failed, but continuing with form save');
                    }

                    let formData = $('#samplingForm').serialize();
                    formData += '&jam_sampling_display=' + encodeURIComponent($('#jam_sampling_display')
                        .val());
                    formData += '&nama_petugas_pengambil=' + encodeURIComponent($(
                        '#nama_petugas_pengambil').val());

                    $.ajax({
                        url: $('#samplingForm').attr('action'),
                        method: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status == true) {
                                if (typeof callback === 'function') {
                                    callback(true, response);
                                }
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                                if (typeof callback === 'function') {
                                    callback(false, response);
                                }
                            }
                        },
                        error: function(xhr) {
                            $('#loadingOverlay').removeClass('active');
                            let message = 'Gagal menyimpan data sampling!';
                            if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                message = xhr.responseJSON.pesan;
                            }
                            swal({
                                title: "Error!",
                                text: message,
                                icon: "error"
                            });
                            if (typeof callback === 'function') {
                                callback(false, null);
                            }
                        }
                    });
                });
            }

            // Submit form
            $('#btnSubmit').on('click', function() {
                if (!checkSignaturesFilled()) {
                    swal({
                        title: "Error!",
                        text: "Tanda tangan pasien dan petugas wajib diisi terlebih dahulu!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('#kondisi_pasien').val().trim()) {
                    swal({
                        title: "Error!",
                        text: "Kondisi pasien wajib diisi!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('#jenis_sampel').val() || $('#jenis_sampel').val().length === 0) {
                    swal({
                        title: "Error!",
                        text: "Jenis sampel wajib dipilih!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('input[name="status_sampling"]:checked').val()) {
                    swal({
                        title: "Error!",
                        text: "Status sampling wajib dipilih!",
                        icon: "warning"
                    });
                    return;
                }

                if ($('#status_sampling_gagal').is(':checked') && !$('#resample_reason').val().trim()) {
                    swal({
                        title: "Error!",
                        text: "Alasan gagal wajib diisi!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('#jam_sampling_display').val()) {
                    swal({
                        title: "Error!",
                        text: "Jam sampling wajib diisi!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('#nama_petugas_pengambil').val()) {
                    swal({
                        title: "Error!",
                        text: "Nama pengambil sample wajib dipilih!",
                        icon: "warning"
                    });
                    return;
                }

                $('#loadingOverlay').addClass('active');

                saveSamplingData(function(success, response) {
                    $('#loadingOverlay').removeClass('active');

                    if (success) {
                        let message = response?.pesan || "Data sampling berhasil disimpan!";
                        if (response?.auto_completed) {
                            message += ' Pengambilan sample telah otomatis diselesaikan.';
                        }
                        swal({
                            title: "Success!",
                            text: message,
                            icon: "success"
                        }).then(function() {
                            window.location.href =
                                '{{ route('mobile.sampling.klinik.success', $permohonan_uji_klinik->id_permohonan_uji_klinik) }}';
                        });
                    } else {
                        swal({
                            title: "Error!",
                            text: response?.pesan || "Gagal menyimpan data sampling!",
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>