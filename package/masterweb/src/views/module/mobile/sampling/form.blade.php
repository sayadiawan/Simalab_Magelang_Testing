<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Sampling Kesmas</title>

    <link href="{{ asset('assets/admin/cdn-local/css/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <!-- jQuery - Load from local asset only (avoid CSP issues) -->
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        /* Button Picker Style */
        .btn-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        .btn-picker {
            padding: 12px 10px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .btn-picker:active {
            transform: scale(0.95);
        }

        .btn-picker.active {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            border-color: #2D6BCF;
        }

        /* Number Input */
        .number-input {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
        }

        .number-input button {
            width: 50px;
            height: 50px;
            border: none;
            background: #2D6BCF;
            color: white;
            border-radius: 12px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .number-input button:active {
            transform: scale(0.9);
        }

        .number-input input {
            width: 80px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
        }

        /* Parameter List */
        .parameter-category {
            margin-bottom: 12px;
        }

        .category-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-header i {
            transition: transform 0.3s;
        }

        .category-header.collapsed i {
            transform: rotate(-90deg);
        }

        .parameter-list {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            transition: max-height 0.3s;
        }

        .parameter-list.collapsed {
            max-height: 0;
            overflow: hidden;
        }

        .parameter-item {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .parameter-item:last-child {
            border-bottom: none;
        }

        .parameter-item.hidden {
            display: none;
        }

        /* Tab Pane Styles - Hide inactive tabs */
        .tab-pane {
            display: none;
        }

        .tab-pane.show.active {
            display: block;
        }

        .parameter-item.highlight {
            background-color: #fff3cd;
        }

        .parameter-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #2D6BCF;
        }

        .parameter-item label {
            flex: 1;
            font-size: 13px;
            color: #333;
            margin: 0;
        }

        /* Floating Submit */
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

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 12px;
            border-radius: 10px;
            border-left: 4px solid #2196f3;
            margin-bottom: 15px;
            font-size: 13px;
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

        /* Loading */
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

        /* Summary Box */
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .summary-label {
            font-weight: 500;
            color: #666;
        }

        .summary-value {
            font-weight: 600;
            color: #333;
        }

        .total-box {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-box .label {
            color: white;
            font-size: 14px;
        }

        .total-box .value {
            color: white;
            font-size: 20px;
            font-weight: 700;
        }

        /* Flatpickr Mobile Styles */
        .flatpickr-calendar {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
            border-radius: 12px !important;
        }

        .flatpickr-day.selected {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%) !important;
            border-color: #2D6BCF !important;
        }

        .flatpickr-day:hover {
            background: #e0e0e0 !important;
        }

        .flatpickr-current-month {
            font-size: 16px !important;
        }

        .flatpickr-time input {
            font-size: 16px !important;
        }
    </style>
    
    <!-- Offline Support & SPA -->
    <script src="{{ asset('js/mobile-sampling-offline.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-sampling-spa.js') }}?v={{ time() }}"></script>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div style="text-align: center;">
            <div class="spinner"></div>
            <p style="color: white; margin-top: 15px;">Menyimpan data...</p>
        </div>
    </div>

    @if(isset($backUrl))
    <div style="background: rgba(255, 255, 255, 0.95); padding: 12px 20px; border-radius: 10px; margin-bottom: 15px; margin-top: 20px; display: flex; align-items: center; gap: 8px; font-size: 14px; max-width: 600px; margin-left: auto; margin-right: auto;">
        <a href="{{ $backUrl }}" style="color: #2D6BCF; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;">
            <span>←</span>
            <span>Kembali</span>
        </a>
    </div>
    @endif

    <div class="top-bar">
        <div style="font-size: 18px; font-weight: 600;">🧪 Form Sampling Kesmas</div>
        <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">Petugas: {{ $petugas_name }}</div>
    </div>

    <div class="container">
        <!-- Info Permohonan -->
        <div class="info-box">
            <strong>👤 Pelanggan:</strong> {{ $permohonan_uji->customer->name_customer ?? '-' }}
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

        <!-- List Sample yang Sudah Diinput -->
        @if ($permohonan_uji->samples && count($permohonan_uji->samples) > 0)
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-list"></i> Sampel Yang Sudah Diinput ({{ count($permohonan_uji->samples) }})
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    @foreach ($permohonan_uji->samples as $index => $sample)
                        <div class="sample-item" style="border-bottom: 1px solid #e0e0e0; padding: 12px 0;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #333; font-size: 14px; margin-bottom: 4px;">
                                        #{{ $index + 1 }} -
                                        {{ $sample->sampletype->name_sample_type ?? 'Jenis Sampel' }}
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-bottom: 2px;">
                                        <i class="fas fa-barcode"></i>
                                        @if ($sample->codesample_samples)
                                            {!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}
                                        @else
                                            <span style="color: #ffc107; font-style: italic;">Kode akan
                                                di-generate</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-bottom: 2px;">
                                        <i class="fas fa-calendar"></i>
                                        {{ $sample->datesampling_samples ? date('d/m/Y H:i', strtotime($sample->datesampling_samples)) : '-' }}
                                    </div>
                                    <div style="font-size: 12px; color: #666;">
                                        <i class="fas fa-map-marker-alt"></i> {{ $sample->titik_pengambilan ?? '-' }}
                                    </div>
                                    @if ($sample->note_samples)
                                        <div style="font-size: 11px; color: #888; margin-top: 4px; font-style: italic;">
                                            💬 {{ $sample->note_samples }}
                                        </div>
                                    @endif
                                </div>
                                <div style="margin-left: 10px;">
                                    <button type="button" class="btn-edit-sample"
                                        data-sample-id="{{ $sample->id_samples }}"
                                        onclick="editSample('{{ $sample->id_samples }}')"
                                        style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
                                                   color: white; border: none; padding: 8px 16px;
                                                   border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @php
                                    $costParam = $sample->cost_samples ?? 0;
                                    $costSampling = $sample->cost_sampling_samples ?? 0;
                                    $totalCost = $costParam + $costSampling;
                                @endphp
                                <span class="badge badge-success" style="font-size: 10px;">
                                    💰 Rp {{ number_format($totalCost, 0, ',', '.') }}
                                </span>
                                @if ($costSampling > 0)
                                    <span class="badge badge-info" style="font-size: 9px; background: #17a2b8;">
                                        🚗 Sampling: Rp {{ number_format($costSampling, 0, ',', '.') }}
                                    </span>
                                @endif
                                @if ($sample->pengambil_sampel)
                                    <span class="badge badge-warning" style="font-size: 10px;">
                                        👤 {{ $sample->pengambil_sampel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form id="samplingForm" method="POST"
            action="{{ route('mobile.sampling.store', $permohonan_uji->id_permohonan_uji) }}">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="id_permohonan_uji" value="{{ $permohonan_uji->id_permohonan_uji }}">
            <input type="hidden" name="jenis_sampel" id="jenis_sampel" value="">
            <input type="hidden" name="program_samples" value="{{ $programs->first()->id_program ?? '' }}">

            <!-- Kode Sampel Section -->
            <div class="card" style="display: none;" id="kodeSampleSection">
                <div class="section-title">
                    <i class="fas fa-barcode"></i> Kode Sampel
                </div>

                <div class="row" style="margin: 0;">
                    <div class="col-6" id="code_sample_kimia_wrapper" style="padding: 5px; display: none;">
                        <div
                            style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 15px; border-radius: 10px;">
                            <label style="color: white; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Kode
                                Sampel Kimia</label>
                            <input type="text" class="form-control" name="code_sample_kimia"
                                id="input_code_sample_kimia"
                                style="text-align: center; font-weight: 700; font-size: 14px; letter-spacing: 1px;"
                                readonly>
                        </div>
                    </div>

                    <div class="col-6" id="code_sample_mikro_wrapper" style="padding: 5px; display: none;">
                        <div
                            style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%); padding: 15px; border-radius: 10px;">
                            <label style="color: white; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Kode
                                Sampel Mikrobiologi</label>
                            <input type="text" class="form-control" name="code_sample_mikro"
                                id="input_code_sample_mikro"
                                style="text-align: center; font-weight: 700; font-size: 14px; letter-spacing: 1px;"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jenis Sampel - Multiple Selection -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-vial"></i> Jenis Sampel <span style="color: red;">*</span>
                </div>
                <small style="display: block; margin-bottom: 12px; color: #666; font-size: 12px;">
                    <i class="fas fa-info-circle"></i> Anda dapat memilih <strong>lebih dari 1 jenis sampel</strong>.
                    Setiap jenis sampel akan memiliki paket & parameter tersendiri.
                </small>
                <div class="btn-picker-grid" id="jenisSampelPicker">
                    @foreach ($sample_types as $type)
                        <button type="button" class="btn-picker btn-jenis-sampel"
                            data-id="{{ $type->id_sample_type }}" data-code="{{ $type->code_sample_type }}"
                            data-name="{{ $type->name_sample_type }}">
                            @if ($type->code_sample_type)
                                [{{ $type->code_sample_type }}]
                            @endif
                            {{ $type->name_sample_type }}
                        </button>
                    @endforeach
                </div>
                <div id="selectedJenisText" style="margin-top: 10px; font-size: 13px; color: #666; display: none;">
                    <strong>Terpilih:</strong> <span id="selectedJenisName"></span>
                </div>
            </div>

            <!-- Tabs untuk setiap jenis sampel -->
            <div class="card" id="sampleTypeTabsSection" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-list-check"></i> Paket & Parameter Pengujian
                </div>

                <!-- Tabs Navigation -->
                <div
                    style="display: flex; overflow-x: auto; gap: 8px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0;">
                    <div id="sampleTypeTabs" style="display: flex; gap: 8px;">
                        <!-- Tabs will be generated dynamically -->
                    </div>
                </div>

                <!-- Tabs Content -->
                <div id="sampleTypeTabsContent">
                    <!-- Tab panels will be generated dynamically -->
                </div>
            </div>

            <!-- Tanggal -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-calendar"></i> Tanggal
                </div>

                <div class="form-group">
                    <label>Tanggal Pengambilan</label>
                    <input type="text" class="form-control" id="datesampling_samples_display"
                        placeholder="dd/MM/yyyy HH:mm" required readonly>
                    <input type="hidden" name="datesampling_samples" id="datesampling_samples">
                </div>

                <div class="form-group">
                    <label>Tanggal Pengiriman</label>
                    <input type="text" class="form-control" id="date_sending_display"
                        placeholder="dd/MM/yyyy HH:mm" required readonly>
                    <input type="hidden" name="date_sending" id="date_sending">
                </div>
            </div>

            <!-- Titik Pengambilan -->
            <div class="card" id="singleTitikPengambilanSection">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i> Titik Pengambilan
                </div>

                <div class="form-group">
                    <label>Lokasi/Titik Pengambilan</label>
                    <textarea class="form-control" name="titik_pengambilan" placeholder="Contoh: Sumur Desa A, RT 01/RW 02"></textarea>
                </div>

                <div class="form-group">
                    <label>💰 Biaya Pengambilan Sampel (Lab)</label>
                    <input type="number" class="form-control" name="cost_sampling_samples" value="20000"
                        placeholder="20000" style="font-weight: 600; font-size: 16px;">
                    <small style="color: #666; font-size: 11px;">
                        <i class="fas fa-info-circle"></i> Biaya default untuk pengambilan sampel oleh petugas lab
                    </small>
                </div>
            </div>

            <!-- Sample Duplicates Detail -->
            <div class="card" id="duplicatesSection" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-clone"></i> Detail Setiap Sample
                </div>
                <small style="display: block; margin-bottom: 15px; color: #666; font-size: 12px;">
                    <i class="fas fa-info-circle"></i> Input titik lokasi dan catatan untuk setiap sample
                </small>
                <div id="duplicatesContainer"></div>
            </div>


            <!-- Petugas Sampling -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-user-md"></i> Petugas Pengambil Sampel
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 12px;">Pilih Petugas Pengambil Sampel:</label>
                    <small style="display: block; margin-bottom: 15px; color: #666; font-size: 12px;">
                        <i class="fas fa-info-circle"></i> Bisa memilih lebih dari satu petugas pengambil sampel
                    </small>

                    <!-- Checkboxes for Petugas (Multiple Selection) -->
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach ($petugas_list as $petugas)
                            <label
                                style="display: flex; align-items: center; cursor: pointer; padding: 12px 15px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s;"
                                class="petugas-checkbox-label"
                                onmouseover="this.style.background='#f0f0f0'; this.style.borderColor='#2D6BCF';"
                                onmouseout="if(!this.querySelector('input:checked')) { this.style.background='#f8f9fa'; this.style.borderColor='#e0e0e0'; }">
                                <input type="checkbox" name="petugas_selected[]" value="{{ $petugas['name'] }}"
                                    style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer;"
                                    onchange="updatePetugasCheckboxStyle()">
                                <span style="font-size: 14px; font-weight: 500; flex: 1;">
                                    <i class="fas fa-user-md" style="color: #2D6BCF; margin-right: 8px;"></i>
                                    {{ $petugas['name'] }}
                                    <small style="color: #666; font-weight: normal; margin-left: 8px;">
                                        ({{ $petugas['lab'] }})
                                    </small>
                                </span>
                            </label>
                        @endforeach

                        <!-- Option: Use Login Name -->
                            <label
                            style="display: flex; align-items: center; cursor: pointer; padding: 12px 15px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e0e0e0; transition: all 0.3s;"
                            class="petugas-checkbox-label"
                            onmouseover="this.style.background='#f0f0f0'; this.style.borderColor='#2D6BCF';"
                            onmouseout="if(!this.querySelector('input:checked')) { this.style.background='#f8f9fa'; this.style.borderColor='#e0e0e0'; }">
                            <input type="checkbox" name="petugas_selected[]" value="{{ $petugas_name }}"
                                style="margin-right: 12px; width: 18px; height: 18px; cursor: pointer;"
                                onchange="updatePetugasCheckboxStyle()">
                            <span style="font-size: 14px; font-weight: 500; flex: 1;">
                                <i class="fas fa-user" style="color: #28a745; margin-right: 8px;"></i>
                                Gunakan Nama Login: <strong>{{ $petugas_name }}</strong>
                                </span>
                            </label>
                    </div>

                        <script>
                        // Wait for jQuery to be ready
                        if (typeof jQuery === 'undefined') {
                            console.error('jQuery is not loaded!');
                        } else {
                            jQuery(document).ready(function($) {
                                // Make function global for SPA compatibility
                                window.updatePetugasCheckboxStyle = function() {
                                    $('.petugas-checkbox-label').each(function() {
                                        const $label = $(this);
                                        const $checkbox = $label.find('input[type="checkbox"]');

                                        if ($checkbox.is(':checked')) {
                                            $label.css({
                                                'background': '#e8f0fe',
                                                'border-color': '#2D6BCF',
                                                'border-width': '2px'
                                            });
                                        } else {
                                            $label.css({
                                                'background': '#f8f9fa',
                                                'border-color': '#e0e0e0',
                                                'border-width': '2px'
                                            });
                                        }
                                    });
                                };

                                // Initialize style on page load
                                window.updatePetugasCheckboxStyle();

                                // Update style on change
                                $('input[name="petugas_selected[]"]').on('change', function() {
                                    window.updatePetugasCheckboxStyle();
                                });
                            });
                        }
                        </script>
                </div>
            </div>

            <!-- Catatan -->
            <div class="card" id="singleCatatanSection">
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i> Catatan
                </div>

                <div class="form-group">
                    <textarea class="form-control" name="note" placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
            </div>

            <!-- Tanda tangan pelanggan -->
            <div class="card" id="pelangganSection" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i> Pelanggan
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="nama_pelanggan" id=""
                        placeholder="Nama Pelanggan">
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="jabatan_pelanggan" id=""
                        placeholder="Jabatan Penanda Tangan">
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="nip_pelanggan" id=""
                        placeholder="NIP Penanda Tangan">
                </div>

                <div style="position: relative; border: 2px solid #ddd; border-radius: 8px; background: white;">
                    <canvas id="signaturePadPelanggan"
                        style="display: block; width: 100%; height: 150px; touch-action: none; -webkit-tap-highlight-color: transparent;">
                    </canvas>
                </div>
                    <button type="button" id="clearPelanggan"
                        style="margin-top: 10px; padding: 8px 15px; background: #f0f0f0; border: none; border-radius: 8px; cursor: pointer; width: 100%;">Hapus
                        Tanda Tangan Pelanggan
                    </button>
            </div>

            <!-- Hidden cost field -->
            <input type="hidden" name="cost_samples" id="cost_samples" value="0">
            <input type="hidden" name="signature_pelanggan" id="signature_pelanggan" value="">

        </form>
    </div>

    <div class="floating-submit">
        <button type="button" class="btn-submit" id="submitBtn" onclick="submitForm()">
            <i class="fas fa-save"></i> SIMPAN SAMPEL
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
        // Multiple sample types selection with configurations
        const selectedSampleTypes = [];
        const sampleTypeConfigs = {}; // Store paket & parameters per sample type
        let signaturePadPelanggan;

        // Initialize Flatpickr for date inputs
        // Wait for jQuery to be ready
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded! Please refresh the page.');
        } else {
            jQuery(document).ready(function($) {
            const now = new Date();

            // Initialize Signature Pad for Pelanggan
            function resizeCanvas(canvas) {
                if (!canvas) return;

                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                // Get actual computed width and height
                const width = rect.width || canvas.offsetWidth || 400;
                const height = rect.height || canvas.offsetHeight || 150;

                // Set internal size
                canvas.width = width * ratio;
                canvas.height = height * ratio;

                // Set CSS size
                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';

                // Scale context
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);

                // Clear canvas
                ctx.clearRect(0, 0, width, height);
            }

            function initSignaturePadPelanggan() {
                // Clear existing pad if any
                if (signaturePadPelanggan) {
                    try {
                        signaturePadPelanggan.clear();
                        signaturePadPelanggan.off();
                    } catch (e) {
                        console.warn('Error clearing pelanggan pad:', e);
                    }
                }

                const canvasPelanggan = document.getElementById('signaturePadPelanggan');

                if (!canvasPelanggan) {
                    console.error('Canvas element not found');
                    return;
                }

                // Use waitForSignaturePad to ensure library is loaded
                window.waitForSignaturePad(function(SignaturePad) {
                    if (!SignaturePad) {
                        console.error('SignaturePad library not available');
                        alert('Library tanda tangan tidak dapat dimuat. Silakan refresh halaman.');
                        return;
                    }

                    // Resize canvas first
                    setTimeout(function() {
                        resizeCanvas(canvasPelanggan);

                        setTimeout(function() {
                            try {
                                // Re-resize to ensure correct dimensions
                                resizeCanvas(canvasPelanggan);

                                // Initialize SignaturePad
                                    signaturePadPelanggan = new SignaturePad(
                                        canvasPelanggan, {
                                    backgroundColor: 'rgba(255, 255, 255, 0)',
                                    penColor: 'rgb(0, 0, 0)',
                                    minWidth: 1,
                                    maxWidth: 3,
                                    velocityFilterWeight: 0.7,
                                    throttle: 16
                                });

                                // CRITICAL for mobile: Ensure canvas can receive touch events
                                canvasPelanggan.style.touchAction = 'none';
                                canvasPelanggan.style.pointerEvents = 'auto';
                                canvasPelanggan.style.webkitTouchCallout = 'none';
                                canvasPelanggan.style.webkitUserSelect = 'none';
                                canvasPelanggan.style.userSelect = 'none';
                                canvasPelanggan.style.msTouchAction = 'none';

                                // CRITICAL for mobile: Add touch event listeners to prevent scrolling
                                    canvasPelanggan.addEventListener('touchstart', function(
                                        e) {
                                    e.preventDefault();
                                    }, {
                                        passive: false
                                    });
                                    canvasPelanggan.addEventListener('touchmove', function(
                                        e) {
                                    e.preventDefault();
                                    }, {
                                        passive: false
                                    });
                                    canvasPelanggan.addEventListener('touchend', function(
                                        e) {
                                    e.preventDefault();
                                    }, {
                                        passive: false
                                    });

                                    console.log(
                                        'SignaturePad Pelanggan initialized successfully'
                                    );
                                    console.log('Canvas Pelanggan size:', canvasPelanggan
                                        .width,
                                        'x',
                                    canvasPelanggan.height);
                            } catch (e) {
                                console.error('Error initializing SignaturePad:', e);
                                    alert('Gagal menginisialisasi tanda tangan: ' + e
                                        .message);
                            }
                        }, 200);
                    }, 150);
                });
            }

            // Initialize signature pad on page load
            setTimeout(function() {
                initSignaturePadPelanggan();
            }, 500);

            // Clear Pelanggan signature
            $('#clearPelanggan').on('click', function() {
                if (signaturePadPelanggan) signaturePadPelanggan.clear();
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (signaturePadPelanggan) {
                    resizeCanvas(document.getElementById('signaturePadPelanggan'));
                    setTimeout(initSignaturePadPelanggan, 100);
                }
            });

            // Initialize Tanggal Pengambilan
            flatpickr("#datesampling_samples_display", {
                enableTime: true,
                time_24hr: true,
                dateFormat: "d/m/Y H:i",
                defaultDate: now,
                locale: "id",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        // Format for server: Y-m-d H:i:s
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');

                        $('#datesampling_samples').val(
                            `${year}-${month}-${day} ${hours}:${minutes}:00`);
                    }
                }
            });

            // Initialize Tanggal Pengiriman
            flatpickr("#date_sending_display", {
                enableTime: true,
                time_24hr: true,
                dateFormat: "d/m/Y H:i",
                defaultDate: now,
                locale: "id",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        // Format for server: Y-m-d H:i:s
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');

                        $('#date_sending').val(`${year}-${month}-${day} ${hours}:${minutes}:00`);
                    }
                }
            });

            // Set initial values
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            $('#datesampling_samples').val(`${year}-${month}-${day} ${hours}:${minutes}:00`);
            $('#date_sending').val(`${year}-${month}-${day} ${hours}:${minutes}:00`);

                // Petugas selection now uses direct radio buttons - no toggle needed
            }); // End of jQuery(document).ready
        } // End of else

        // Jenis Sampel Picker - Multiple Selection
        $(document).on('click', '.btn-jenis-sampel', function() {
            const sampleTypeId = $(this).data('id');
            const sampleTypeCode = $(this).data('code');
            const sampleTypeName = $(this).data('name');
            const $button = $(this);

            // Toggle selection
            const index = selectedSampleTypes.findIndex(function(item) {
                return item.id === sampleTypeId;
            });

            if (index > -1) {
                // Deselect
                selectedSampleTypes.splice(index, 1);
                delete sampleTypeConfigs[sampleTypeId];
                $button.removeClass('active');
                } else {
                // Select
                selectedSampleTypes.push({
                    id: sampleTypeId,
                    code: sampleTypeCode,
                    name: sampleTypeName
                });

                // Initialize config for this sample type
                sampleTypeConfigs[sampleTypeId] = {
                    packets: [],
                    additional_methods: [],
                    cost: 0,
                    titik_pengambilan: '' // Titik pengambilan per sample type
                };

                $button.addClass('active');
            }

            // Update display and regenerate tabs
            updateSelectedSampleTypesDisplay();
            generateSampleTypeTabs();

            // Show/hide tabs section
            if (selectedSampleTypes.length > 0) {
                $('#sampleTypeTabsSection').slideDown();
            } else {
                $('#sampleTypeTabsSection').slideUp();
                $('#selectedJenisText').hide();
            }
        });

        // Update selected sample types display
        function updateSelectedSampleTypesDisplay() {
            if (selectedSampleTypes.length > 0) {
                const names = selectedSampleTypes.map(function(type) {
                    return type.code + ' - ' + type.name;
                }).join(', ');
                $('#selectedJenisName').text(names);
                $('#selectedJenisText').show();
            } else {
                $('#selectedJenisText').hide();
            }
        }

        // Generate tabs for each selected sample type
        function generateSampleTypeTabs() {
            if (selectedSampleTypes.length === 0) {
                $('#sampleTypeTabs').html('');
                $('#sampleTypeTabsContent').html('');
                return;
            }

            let tabsHtml = '';
            let tabContentsHtml = '';

            selectedSampleTypes.forEach(function(type, index) {
                const isActive = index === 0 ? 'active' : '';
                const showClass = index === 0 ? 'show active' : '';

                // Tab header (mobile-friendly, horizontal scroll)
                tabsHtml += `
                    <button type="button" class="btn-picker ${isActive}" 
                            data-tab="${type.id}" 
                            style="white-space: nowrap; min-width: 120px; padding: 10px 15px;">
                        ${type.code} - ${type.name}
                        <span class="badge" style="margin-left: 5px; background: white; color: #2D6BCF;" id="count-${type.id}">0</span>
                    </button>
                `;

                // Tab content
                // Only first tab should be visible initially
                const displayStyle = index === 0 ? 'display: block;' : 'display: none;';
                tabContentsHtml += `
                    <div class="tab-pane ${showClass}" id="content-${type.id}" data-sampletype="${type.id}" style="${displayStyle}">
                        ${generateTabContent(type)}
                    </div>
                `;
            });

            $('#sampleTypeTabs').html(tabsHtml);
            $('#sampleTypeTabsContent').html(tabContentsHtml);

            // Tab click handler
            $(document).off('click', '[data-tab]').on('click', '[data-tab]', function() {
                const tabId = $(this).data('tab');

                // Remove active class from all tabs
                $('[data-tab]').removeClass('active');
            $(this).addClass('active');

                // Hide all tab panes
                $('.tab-pane').removeClass('show active').hide();

                // Show only the active tab pane
                const $activePane = $(`#content-${tabId}`);
                $activePane.addClass('show active').show();

                // Update summary for active tab
                updateTabSummary(tabId);
            });

            // Load data for each tab AFTER DOM is ready
            // Use setTimeout to ensure DOM is fully rendered
            // Also use requestAnimationFrame for better timing
            requestAnimationFrame(function() {
                setTimeout(function() {
                    selectedSampleTypes.forEach(function(type) {
                        const $tabContent = $(`#tab-content-${type.id}`);
                        if ($tabContent.length > 0) {
                            console.log('Loading data for tab:', type.id, 'Element:', $tabContent[
                                0]);
                            loadTabPaketAndParameters(type);
                        } else {
                            console.error('Tab content not found:', `#tab-content-${type.id}`);
                            // Try again after a short delay
                            setTimeout(function() {
                                const $tabContentRetry = $(`#tab-content-${type.id}`);
                                if ($tabContentRetry.length > 0) {
                                    console.log('Retry: Loading data for tab:', type.id);
                                    loadTabPaketAndParameters(type);
                                } else {
                                    console.error(
                                        'Retry failed: Tab content still not found:',
                                        `#tab-content-${type.id}`);
                                }
                            }, 200);
                        }
                    });
                }, 50);
            });

            // Reinitialize event handlers for new elements
            initializeTabEventHandlers();
        }

        // Generate tab content (packet & parameter selection)
        function generateTabContent(type) {
            // Return HTML structure first, then load data after DOM is ready
            return `
                <div id="tab-content-${type.id}">
                    <div style="text-align: center; padding: 20px; color: #999;">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                    </div>
                </div>
            `;
        }

        // Load paket and parameters for tab
        function loadTabPaketAndParameters(type) {
            const $tabContent = $(`#tab-content-${type.id}`);

            // Check if element exists
            if ($tabContent.length === 0) {
                console.error('Tab content element not found:', `#tab-content-${type.id}`);
                return;
            }

            console.log('Loading data for tab:', type.id, 'Element found:', $tabContent.length);

            // Show loading state
            $tabContent.html(`
                <div style="text-align: center; padding: 20px; color: #999;">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                </div>
            `);

            // Use packets data from server (already loaded in page)
            const packets = @json($packets ?? []);

            // Filter packets for this sample type
            const filteredPackets = packets.filter(function(packet) {
                if (!packet.sample_type_ids || packet.sample_type_ids.length === 0) {
                    return true; // Show packets with no restrictions
                }
                return packet.sample_type_ids.includes(type.id);
            });

            // Build Titik Pengambilan HTML first (per jenis sample)
            let titikPengambilanHtml = `
                <div style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 10px; display: block;">
                        <i class="fas fa-map-marker-alt"></i> Titik Pengambilan
                        <small style="color: #666; font-weight: normal;">(Opsional)</small>
                    </label>
                    <textarea class="form-control titik-pengambilan-input-tab" 
                        id="titik_pengambilan_${type.id}"
                        data-sampletype="${type.id}"
                        placeholder="Misal: Jl. Sudirman No. 123, Kota ABC"
                        style="min-height: 60px; font-size: 14px;"></textarea>
                    <small style="display: block; margin-top: 5px; color: #666; font-size: 12px;">
                        <i class="fas fa-info-circle"></i> Lokasi pengambilan sampel untuk jenis sampel <strong>${type.name}</strong>
                    </small>
                </div>
                <hr style="margin: 20px 0; border-top: 2px dashed #e0e0e0;">
            `;

            // Build packets HTML
            let packetsHtml = '';
            if (filteredPackets.length > 0) {
                packetsHtml += `
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; margin-bottom: 10px; display: block;">
                            <i class="fas fa-box"></i> Pilih Paket (Opsional)
                        </label>
                        <small style="display: block; margin-bottom: 10px; color: #666; font-size: 12px;">
                            <i class="fas fa-info-circle"></i> Pilih paket untuk otomatis memilih parameter
                        </small>
                        <div class="btn-picker-grid" id="paketPicker-${type.id}">
                `;

                filteredPackets.forEach(packet => {
                    const packetPrice = packet.price_total_packet || 0;
                    const packetId = packet.id_packet;
                    const packetName = packet.name_packet || 'Paket';

                    packetsHtml += `
                        <button type="button" class="btn-picker btn-paket-tab" 
                                data-sampletype="${type.id}"
                                data-id="${packetId}"
                                data-price="${packetPrice}"
                                data-name="${packetName}">
                            ${packetName}
                            <br><small style="font-size: 11px; color: #28a745;">Rp ${formatRupiah(packetPrice)}</small>
                        </button>
                    `;
                });

                packetsHtml += `
                        </div>
                    </div>
                    <hr style="margin: 20px 0; border-top: 2px dashed #e0e0e0;">
                `;
            }

            // Load parameters via AJAX
            const url =
                "{{ route('mobile.sampling.getbaku_mutu', ['id' => $permohonan_uji->id_permohonan_uji, 'sample_type_id' => '#']) }}"
                .replace('#', type.id);

            console.log('Loading parameters from:', url);

            $.ajax({
                url: url,
                type: 'GET',
                timeout: 10000,
                success: function(paramResponse) {
                    console.log('Parameter response for', type.id, ':', paramResponse);

                    // Check if response has data
                    if (!paramResponse || !paramResponse.data) {
                        console.error('Invalid response structure:', paramResponse);
                        $tabContent.html(`
                            <div style="text-align: center; padding: 20px; color: #dc3545;">
                                <i class="fas fa-exclamation-triangle"></i> Response tidak valid
                            </div>
                        `);
                        return;
                    }

                    console.log('Response data length:', paramResponse.data.length);

                    const groupedParams = {};

                    // Group parameters by laboratory
                    @foreach ($data_methods as $lab)
                        groupedParams['{{ $lab->id_lab }}'] = {
                            name: {!! json_encode($lab->name) !!},
                            methods: []
                        };
                    @endforeach

                    // Populate methods - check which methods have baku mutu for selected sample type
                    @foreach ($data_methods as $lab)
                        @foreach ($lab->method as $method)
                            if (paramResponse.data && Array.isArray(paramResponse.data) && paramResponse.data
                                .some(item => item.id_method ===
                                    '{{ $method->id_method }}')) {
                                (function() {
                                    var pricesBySt = @json($method->prices_by_sample_type ?? []);
                                    var defaultP = {{ (float) ($method->price_method ?? 0) }};
                                    var stId = type.id;
                                    var resolvedP = defaultP;
                                    if (pricesBySt && pricesBySt[stId] != null && pricesBySt[stId] !== '') {
                                        var p = parseFloat(pricesBySt[stId]);
                                        if (!isNaN(p)) {
                                            resolvedP = p;
                                        }
                                    }
                                    groupedParams['{{ $lab->id_lab }}'].methods.push({
                                        id: '{{ $method->id_method }}',
                                        name: {!! json_encode($method->name_method) !!},
                                        price: resolvedP,
                                        labId: '{{ $lab->id_lab }}'
                                    });
                                })();
                            }
                        @endforeach
                    @endforeach

                    console.log('Grouped params:', groupedParams);

                    // Build parameters HTML
                    let parametersHtml = `
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; margin-bottom: 10px; display: block;">
                                <i class="fas fa-microscope"></i> Parameter Pengujian <span style="color: red;">*</span>
                            </label>
                            
                            <!-- Search Box -->
                            <div style="margin-bottom: 12px;">
                                <input type="text" class="form-control tab-parameter-search" 
                                       data-sampletype="${type.id}"
                                       placeholder="🔍 Cari parameter..." 
                                       style="padding-left: 12px;">
                            </div>
                            
                            <div id="parameterContainer-${type.id}">
                    `;

                    let hasMethods = false;
                    Object.keys(groupedParams).forEach(labId => {
                        const lab = groupedParams[labId];
                        if (lab.methods.length > 0) {
                            hasMethods = true;
                            parametersHtml += `
                                <div class="parameter-category">
                                    <div class="category-header" onclick="toggleCategory(this)">
                                        <span>${lab.name}</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="parameter-list">
                            `;

                            lab.methods.forEach(method => {
                                parametersHtml += `
                                    <div class="parameter-item">
                                        <input type="checkbox" class="param-checkbox-tab" 
                                               data-sampletype="${type.id}"
                                               data-method="${method.id}_${method.labId}_${method.price}"
                                               data-lab="${method.labId}"
                                               data-name="${method.name}"
                                               data-labname="${lab.name}"
                                               data-price="${method.price}"
                                               data-id="${method.id}">
                                        <label>${method.name} <small style="color: #666;">(Rp ${formatRupiah(method.price)})</small></label>
                                    </div>
                                `;
                            });

                            parametersHtml += `
                                    </div>
                                </div>
                            `;
                        }
                    });

                    if (!hasMethods) {
                        parametersHtml += `
                            <div style="text-align: center; padding: 20px; color: #999;">
                                <i class="fas fa-info-circle"></i> Tidak ada parameter tersedia untuk jenis sampel ini
                            </div>
                        `;
                    }

                    parametersHtml += `
                            </div>
                            
                            <!-- Summary -->
                            <div id="tabSummary-${type.id}" style="margin-top: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px; display: none;">
                                <div style="margin-bottom: 10px;">
                                    <strong>Total Parameter:</strong> <span id="paramCount-${type.id}">0</span> parameter
                                </div>
                                <div style="padding: 10px; background: white; border-radius: 6px; border-left: 3px solid #11998e;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <span style="color: #666; font-size: 13px;">🔬 Biaya Parameter:</span>
                                        <span id="priceParameters-${type.id}" style="font-weight: 600; color: #333;">Rp 0</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #e0e0e0;">
                                        <span style="color: #666; font-size: 13px;">💰 Biaya Sampling:</span>
                                        <span id="priceSampling-${type.id}" style="font-weight: 600; color: #333;">Rp 0</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 2px solid #2D6BCF;">
                                        <span style="color: #333; font-size: 14px; font-weight: 600;">📊 Total Biaya:</span>
                                        <span id="totalBiaya-${type.id}" style="font-weight: 700; color: #2D6BCF; font-size: 16px;">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Combine titik pengambilan, packets and parameters HTML
                    const finalHtml = titikPengambilanHtml + packetsHtml + parametersHtml;

                    console.log('Updating tab content for', type.id, 'HTML length:', finalHtml.length);
                    console.log('Tab content element before update:', $tabContent.length, $tabContent[0]);

                    // Double check element still exists
                    const $tabContentCheck = $(`#tab-content-${type.id}`);
                    if ($tabContentCheck.length === 0) {
                        console.error('Tab content element disappeared!', `#tab-content-${type.id}`);
                        return;
                    }

                    // Update the tab content
                    try {
                        $tabContentCheck.html(finalHtml);

                        // Verify update immediately
                        const updatedContent = $tabContentCheck.html();
                        if (updatedContent && updatedContent.length > 100) {
                            console.log('Tab content updated successfully for', type.id, 'Content length:',
                                updatedContent.length);
                        } else {
                            console.error('Tab content update failed or too short for', type.id, 'Content:',
                                updatedContent);
                        }
                    } catch (e) {
                        console.error('Error updating tab content:', e);
                    }

                    // Initialize event handlers
                    initializeTabEventHandlers();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading parameters:', error, xhr);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);

                    // Show titik pengambilan and packets even if parameters fail, then show error message
                    let errorHtml = titikPengambilanHtml + packetsHtml;
                    errorHtml += `
                        <div style="text-align: center; padding: 20px; color: #dc3545; margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i> Gagal memuat parameter: ${error || 'Unknown error'}
                            <br><small>Silakan refresh halaman dan coba lagi</small>
                            <br><br>
                            <button type="button" onclick="loadTabPaketAndParameters({id: '${type.id}', code: '${type.code}', name: '${type.name}'})" 
                                    style="padding: 8px 16px; background: #2D6BCF; color: white; border: none; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-redo"></i> Coba Lagi
                            </button>
                        </div>
                    `;

                    $tabContent.html(errorHtml);

                    // Initialize event handlers for packets
                    if (filteredPackets.length > 0) {
                        initializeTabEventHandlers();
                    }
                }
            });
        }

        // Make function globally accessible for retry button
        window.loadTabPaketAndParameters = loadTabPaketAndParameters;

        // Format rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Initialize event handlers for tab elements
        function initializeTabEventHandlers() {
            // Packet button click handler (MULTIPLE SELECTION)
            $(document).off('click', '.btn-paket-tab').on('click', '.btn-paket-tab', function() {
                const sampleTypeId = $(this).data('sampletype');
                const packetId = $(this).data('id');
                const packetName = $(this).data('name');
                const packetPrice = parseFloat($(this).data('price')) || 0;
                const $button = $(this);
                const isActive = $button.hasClass('active');

                // Ensure config exists
                if (!sampleTypeConfigs[sampleTypeId]) {
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [],
                        additional_methods: [],
                        cost: 0
                    };
                }

                if (isActive) {
                    // Deselect this packet only
                    $button.removeClass('active');

                    // Remove packet from config
                    const config = sampleTypeConfigs[sampleTypeId];
                    if (config.packets) {
                        config.packets = config.packets.filter(function(p) {
                            return p.packet_id !== packetId;
                        });
                    }

                    // Uncheck and enable all checkboxes that were from this packet
                    $(`.param-checkbox-tab[data-sampletype="${sampleTypeId}"][data-packet-id="${packetId}"]`).each(
                        function() {
                            const $checkbox = $(this);
                            if ($checkbox.prop('disabled')) {
                                $checkbox.prop('checked', false).prop('disabled', false);
                                $checkbox.closest('.parameter-item').removeClass('disabled-param');
                            }
                        });

                    updateTabSummary(sampleTypeId);
                } else {
                    // Select this packet (MULTIPLE SELECTION - keep others selected)
                    $button.addClass('active');

                    // Add packet to config
                    const config = sampleTypeConfigs[sampleTypeId];
                    if (!config.packets) {
                        config.packets = [];
                    }

                    // Check if packet already exists
                    const existingPacket = config.packets.find(function(p) {
                        return p.packet_id === packetId;
                    });

                    if (!existingPacket) {
                        config.packets.push({
                            packet_id: packetId,
                            packet_name: packetName,
                            packet_price: packetPrice,
                            methods: []
                        });
                    }

                    // Load packet methods and auto-check them
                    loadPacketMethodsForTab(sampleTypeId, packetId);
                }
            });

            // Method checkbox change handler
            $(document).off('change', '.param-checkbox-tab').on('change', '.param-checkbox-tab', function() {
                const sampleTypeId = $(this).data('sampletype');
                const methodValue = $(this).data('method');
                const methodName = $(this).data('name');
                const methodPrice = parseFloat($(this).data('price'));
                const labName = $(this).data('labname');
                const isChecked = $(this).is(':checked');
                const isDisabled = $(this).prop('disabled'); // Check if from packet

                // Ensure config exists
                if (!sampleTypeConfigs[sampleTypeId]) {
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [],
                        additional_methods: [],
                        cost: 0
                    };
                }

                // Update additional_methods array ONLY if NOT from packet (not disabled)
                if (!isDisabled) {
                    if (isChecked) {
                        // Check if not already in additional_methods
                        const exists = sampleTypeConfigs[sampleTypeId].additional_methods.some(function(m) {
                            return m.method_string === methodValue;
                        });

                        if (!exists) {
                            sampleTypeConfigs[sampleTypeId].additional_methods.push({
                                method_string: methodValue,
                                name: methodName,
                                price: methodPrice,
                                lab_name: labName
                            });
                        }
                    } else {
                        // Remove from additional_methods
                        sampleTypeConfigs[sampleTypeId].additional_methods =
                            sampleTypeConfigs[sampleTypeId].additional_methods.filter(function(m) {
                                return m.method_string !== methodValue;
                            });
                    }
                }

                updateTabSummary(sampleTypeId);
            });

            // Handle titik pengambilan input per sample type
            $(document).off('input change', '.titik-pengambilan-input-tab').on('input change',
                '.titik-pengambilan-input-tab',
                function() {
                    const sampleTypeId = $(this).data('sampletype');
                    const titikPengambilan = $(this).val() || '';

                    if (sampleTypeConfigs[sampleTypeId]) {
                        sampleTypeConfigs[sampleTypeId].titik_pengambilan = titikPengambilan;
                    }
                });

            // Search parameter in tab
            $(document).off('keyup', '.tab-parameter-search').on('keyup', '.tab-parameter-search', function() {
                const sampleTypeId = $(this).data('sampletype');
                const searchTerm = $(this).val().toLowerCase().trim();

                $(`#parameterContainer-${sampleTypeId} .parameter-item`).each(function() {
                    const $item = $(this);
                    const paramName = $item.find('label').text().toLowerCase();

                    if (paramName.includes(searchTerm)) {
                        $item.removeClass('hidden');
                        if (searchTerm.length > 0) {
                            $item.addClass('highlight');
                        } else {
                            $item.removeClass('highlight');
                        }
                    } else {
                        $item.addClass('hidden');
                        $item.removeClass('highlight');
                    }
                });
            });
        }

        // Load packet methods from server and auto-check them
        function loadPacketMethodsForTab(sampleTypeId, packetId) {
            const url =
                "{{ route('mobile.sampling.getdetail_sample_type', ['id' => $permohonan_uji->id_permohonan_uji, 'sample_type_id' => '#']) }}"
                .replace('#', packetId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    const methodsData = response.data || [];

                    // Find or create packet entry
                    const config = sampleTypeConfigs[sampleTypeId];
                    if (!config) {
                        sampleTypeConfigs[sampleTypeId] = {
                            packets: [],
                            additional_methods: [],
                            cost: 0
                        };
                    }

                    const packetEntry = config.packets.find(function(p) {
                        return p.packet_id === packetId;
                    });

                    if (packetEntry) {
                        // Clear previous methods from this packet
                        packetEntry.methods = [];

                        if (methodsData.length > 0) {
                            methodsData.forEach(function(methodData) {
                                const methodId = methodData.method_id || methodData.id_method;

                                if (!methodId) return;

                                // Find all checkboxes for this sample type with this method_id
                                const $checkboxes = $(
                                        `.param-checkbox-tab[data-sampletype="${sampleTypeId}"]`)
                                    .filter(function() {
                                        const methodString = $(this).data('method');
                                        if (methodString) {
                                            const parts = methodString.split('_');
                                            return parts[0] === methodId;
                                        }
                                        return false;
                                    });

                                // Process each matching checkbox
                                $checkboxes.each(function() {
                                    const $checkbox = $(this);
                                    const methodString = $checkbox.data('method');

                                    // Store method with packet_id attribute for tracking
                                    $checkbox.attr('data-packet-id', packetId);
                                    packetEntry.methods.push(methodString);

                                    // Check and disable the checkbox
                                    $checkbox.prop('checked', true).prop('disabled', true);
                                    $checkbox.closest('.parameter-item').addClass(
                                        'disabled-param');
                                });
                            });
                        }

                        updateTabSummary(sampleTypeId);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading packet methods:', error);
                }
            });
        }

        // Update summary for specific tab
        window.updateTabSummary = function(sampleTypeId) {
            const config = sampleTypeConfigs[sampleTypeId];
            if (!config) return;

            // Calculate total packet methods
            let totalPacketMethods = 0;
            if (config.packets) {
                config.packets.forEach(function(p) {
                    totalPacketMethods += (p.methods || []).length;
                });
            }

            const totalAdditionalMethods = config.additional_methods ? config.additional_methods.length : 0;
            const totalCount = totalPacketMethods + totalAdditionalMethods;

            // Calculate total cost (parameters only)
            let totalCost = 0;
            if (config.packets) {
                config.packets.forEach(function(packet) {
                    totalCost += parseFloat(packet.packet_price) || 0;
                });
            }
            if (config.additional_methods) {
                config.additional_methods.forEach(function(method) {
                    totalCost += parseFloat(method.price) || 0;
                });
            }
            config.cost = totalCost;

            // Get sampling cost
            let costSampling = parseFloat($('input[name="cost_sampling_samples"]').val()) || 0;

            // Calculate total biaya (parameters + sampling)
            const totalBiaya = totalCost + costSampling;

            // Update display
            $(`#paramCount-${sampleTypeId}`).text(totalCount);
            $(`#priceParameters-${sampleTypeId}`).text('Rp ' + formatRupiah(totalCost));
            $(`#priceSampling-${sampleTypeId}`).text('Rp ' + formatRupiah(costSampling));
            $(`#totalBiaya-${sampleTypeId}`).text('Rp ' + formatRupiah(totalBiaya));
            $(`#count-${sampleTypeId}`).text(totalCount);

            if (totalCount > 0) {
                $(`#tabSummary-${sampleTypeId}`).show();
            } else {
                $(`#tabSummary-${sampleTypeId}`).hide();
            }
        }

        // OLD Load Paket (kept for backward compatibility, but may not be used)
        function loadPaket(jenisSampelId) {
            $.ajax({
                url: '/api/packet/' + jenisSampelId,
                type: 'POST',
                success: function(response) {
                    const results = response.results;

                    if (results && results.length > 0) {
                        $('#paketSection').show();
                        $('#paketPicker').empty();
                        selectedPacket = null;
                        $('#packetSelect').val('');

                        results.forEach(result => {
                            $('#paketPicker').append(`
                                <button type="button" class="btn-picker btn-paket" data-id="${result.id}">
                                    ${result.text}
                                </button>
                            `);
                        });

                        // Paket click handler
                        $('.btn-paket').on('click', function() {
                            $('.btn-paket').removeClass('active');
                            $(this).addClass('active');

                            const paketId = $(this).data('id');
                            selectedPacket = paketId;
                            $('#packetSelect').val(paketId);

                            // Add small delay to ensure parameters are loaded
                            setTimeout(function() {
                                loadPaketParameters(paketId);
                            }, 100);
                        });
                    } else {
                        $('#paketSection').hide();
                    }
                }
            });
        }

        // Load Paket Parameters
        function loadPaketParameters(paketId) {
            const url =
                "{{ route('mobile.sampling.getdetail_sample_type', ['id' => $permohonan_uji->id_permohonan_uji, 'sample_type_id' => '#']) }}"
                .replace('#', paketId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.data && Array.isArray(response.data)) {
                        // First, uncheck all parameters
                        $('.param-checkbox').prop('checked', false);

                        // Then check parameters from the packet
                        response.data.forEach(data => {
                            // Find checkbox by data-id attribute instead of value
                            const checkbox = $(`.param-checkbox[data-id="${data.method_id}"]`);

                            if (checkbox.length > 0) {
                                checkbox.prop('checked', true);
                            }
                        });

                        updateParameterCount();
                    }
                }
            });
        }

        // Load Parameters
        function loadParameters(jenisSampelId) {
            const url =
                "{{ route('mobile.sampling.getbaku_mutu', ['id' => $permohonan_uji->id_permohonan_uji, 'sample_type_id' => '#']) }}"
                .replace('#', jenisSampelId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    const groupedParams = {};

                    // Group parameters by laboratory
                    @foreach ($data_methods as $lab)
                        groupedParams['{{ $lab->id_lab }}'] = {
                            name: {!! json_encode($lab->name) !!},
                            methods: []
                        };
                    @endforeach

                    // Populate methods - check which methods have baku mutu for selected sample type
                    @foreach ($data_methods as $lab)
                        @foreach ($lab->method as $method)
                            if (response.data.some(item => item.id_method === '{{ $method->id_method }}')) {
                                (function() {
                                    var pricesBySt = @json($method->prices_by_sample_type ?? []);
                                    var defaultP = {{ (float) ($method->price_method ?? 0) }};
                                    var stId = jenisSampelId;
                                    var resolvedP = defaultP;
                                    if (pricesBySt && pricesBySt[stId] != null && pricesBySt[stId] !== '') {
                                        var p = parseFloat(pricesBySt[stId]);
                                        if (!isNaN(p)) {
                                            resolvedP = p;
                                        }
                                    }
                                    groupedParams['{{ $lab->id_lab }}'].methods.push({
                                        id: '{{ $method->id_method }}',
                                        name: {!! json_encode($method->name_method) !!},
                                        price: resolvedP,
                                        labId: '{{ $lab->id_lab }}'
                                    });
                                })();
                            }
                        @endforeach
                    @endforeach

                    // Render parameters
                    let html = '';
                    Object.keys(groupedParams).forEach(labId => {
                        const lab = groupedParams[labId];
                        if (lab.methods.length > 0) {
                            html += `
                                <div class="parameter-category">
                                    <div class="category-header" onclick="toggleCategory(this)">
                                        <span>${lab.name}</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="parameter-list">
                            `;

                            lab.methods.forEach(method => {
                                html += `
                                    <div class="parameter-item">
                                        <input type="checkbox" class="param-checkbox" name="method[]"
                                               value="${method.id}_${method.labId}_${method.price}"
                                               data-price="${method.price}"
                                               data-id="${method.id}"
                                               data-labid="${method.labId}"
                                               onchange="updateParameterCount()">
                                        <label>${method.name}</label>
                                    </div>
                                `;
                            });

                            html += `
                                    </div>
                                </div>
                            `;
                        }
                    });

                    if (html) {
                        $('#parameterContainer').html(html);
                        $('#parameterSearchBox').show();
                    } else {
                        $('#parameterContainer').html(
                            '<p style="text-align: center; color: #999; padding: 20px;">Tidak ada parameter tersedia</p>'
                        );
                        $('#parameterSearchBox').hide();
                    }

                    updateParameterCount();
                }
            });
        }

        // Search Parameters
        $('#parameterSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();

            $('.parameter-item').each(function() {
                const $item = $(this);
                const paramName = $item.find('label').text().toLowerCase();

                if (paramName.includes(searchTerm)) {
                    $item.removeClass('hidden');
                    if (searchTerm.length > 0) {
                        $item.addClass('highlight');
                    } else {
                        $item.removeClass('highlight');
                    }
                } else {
                    $item.addClass('hidden');
                    $item.removeClass('highlight');
                }
            });

            // Show/hide categories based on visible items
            $('.parameter-category').each(function() {
                const $category = $(this);
                const visibleItems = $category.find('.parameter-item:not(.hidden)').length;

                if (visibleItems > 0) {
                    $category.show();
                    // Auto-expand category if searching
                    if (searchTerm.length > 0) {
                        $category.find('.category-header').removeClass('collapsed');
                        $category.find('.parameter-list').removeClass('collapsed');
                    }
                } else {
                    $category.hide();
                }
            });
        });

        // Toggle Category
        function toggleCategory(header) {
            const $header = $(header);
            const $list = $header.next('.parameter-list');

            $header.toggleClass('collapsed');
            $list.toggleClass('collapsed');
        }

        // Update Parameter Count
        window.updateParameterCount = function() {
            const checked = $('.param-checkbox:checked');
            const count = checked.length;

            let totalParameters = 0;
            checked.each(function() {
                totalParameters += parseInt($(this).data('price')) || 0;
            });

            // Get biaya sampling
            let costSampling = parseInt($('input[name="cost_sampling_samples"]').val()) || 0;

            // Calculate grand total
            let grandTotal = totalParameters + costSampling;

            // Update displays
            $('#paramCount').text(count);
            $('#priceParameters').text('Rp ' + totalParameters.toLocaleString('id-ID'));
            $('#priceSampling').text('Rp ' + costSampling.toLocaleString('id-ID'));
            $('#totalPrice').text('Rp ' + grandTotal.toLocaleString('id-ID'));
            $('#cost_samples').val(totalParameters); // Database hanya simpan biaya parameter

            if (count > 0) {
                $('#selectedParamsCount').show();
            } else {
                $('#selectedParamsCount').hide();
            }

            // Update sample code visibility
            updateSampleCodeVisibility();
        }

        // Update Sample Code Visibility based on selected parameters
        function updateSampleCodeVisibility() {
            const checkedParams = $('.param-checkbox:checked');
            let hasKimia = false;
            let hasMikro = false;

            // Check each selected parameter's lab
            checkedParams.each(function() {
                const labId = $(this).data('labid');

                // Assuming lab IDs: Kimia might be 1 or contains 'kimia', Mikro might be 2 or contains 'mikro'
                // You need to adjust this based on your actual lab IDs
                @foreach ($data_methods as $lab)
                    if (labId == '{{ $lab->id_lab }}') {
                        @if (stripos($lab->name, 'kimia') !== false || $lab->id_lab == '1')
                            hasKimia = true;
                        @elseif (stripos($lab->name, 'mikro') !== false || $lab->id_lab == '2')
                            hasMikro = true;
                        @endif
                    }
                @endforeach
            });

            const $kimiaWrapper = $('#code_sample_kimia_wrapper');
            const $mikroWrapper = $('#code_sample_mikro_wrapper');
            const $kodeSampleSection = $('#kodeSampleSection');

            // If no parameters selected, hide section
            if (checkedParams.length === 0) {
                $kodeSampleSection.hide();
                $kimiaWrapper.hide();
                $mikroWrapper.hide();
            } else {
                $kodeSampleSection.show();

                if (hasKimia && hasMikro) {
                    // Both: show both side by side
                    $kimiaWrapper.removeClass('col-12').addClass('col-6').show();
                    $mikroWrapper.removeClass('col-12').addClass('col-6').show();
                } else if (hasKimia) {
                    // Only Kimia: show full width
                    $kimiaWrapper.removeClass('col-6').addClass('col-12').show();
                    $mikroWrapper.hide();
                } else if (hasMikro) {
                    // Only Mikro: show full width
                    $kimiaWrapper.hide();
                    $mikroWrapper.removeClass('col-6').addClass('col-12').show();
                } else {
                    $kodeSampleSection.hide();
                }
            }

            // Sample quantity field removed - no duplicates needed
        }

        // Initialize sample codes from backend
        const initialCodeSamples = {
            kimia: '{{ $code_samples['kimia'] ?? '...' }}',
            mikrobiologi: '{{ $code_samples['mikrobiologi'] ?? '...' }}'
        };

        // Generate Sample Code by updating the code type
        function generateSampleCode(jenisSampelCode) {
            // Get initial codes from backend
            let kimiaCode = initialCodeSamples.kimia;
            let mikroCode = initialCodeSamples.mikrobiologi;

            // Replace all dots at the beginning with sample type code + single dot
            // Format: {code_type}.{lab_code}/{sequence}/{year}
            // Example: AB.01/0011/2025
            kimiaCode = kimiaCode.replace(/^\.+/, jenisSampelCode + '.');
            mikroCode = mikroCode.replace(/^\.+/, jenisSampelCode + '.');

            $('#input_code_sample_kimia').val(kimiaCode);
            $('#input_code_sample_mikro').val(mikroCode);
        }

        // Edit Sample
        function editSample(sampleId) {
            const permohonanId = '{{ $permohonan_uji->id_permohonan_uji }}';
            window.location.href = `/mobile/sampling/${permohonanId}/edit/${sampleId}`;
        }

        // Generate Sample Duplicates - DISABLED (field removed)
        function generateSampleDuplicates() {
            // Field removed - always use single sample
            const qty = 1;
            const $container = $('#duplicatesContainer');
            const $section = $('#duplicatesSection');
            const $singleTitikSection = $('#singleTitikPengambilanSection');
            const $singleCatatanSection = $('#singleCatatanSection');

            if (qty > 1) {
                // Save existing values before clearing
                const savedValues = {};
                for (let i = 1; i <= 10; i++) {
                    const titik = $(`textarea[name="titik_pengambilan_${i}"]`).val();
                    const note = $(`textarea[name="note_${i}"]`).val();
                    if (titik || note) {
                        savedValues[i] = {
                            titik: titik || '',
                            note: note || ''
                        };
                    }
                }

                // Hide single input, show duplicates
                $singleTitikSection.hide();
                $singleCatatanSection.hide();
                $section.show();
                $container.empty();

                // Get base sample codes
                const kimiaCode = $('#input_code_sample_kimia').val() || '';
                const mikroCode = $('#input_code_sample_mikro').val() || '';

                for (let i = 1; i <= qty; i++) {
                    // Generate incremented sample codes
                    let kimiaCodeIncrement = incrementSampleCode(kimiaCode, i - 1);
                    let mikroCodeIncrement = incrementSampleCode(mikroCode, i - 1);

                    // Get saved values if exist
                    let savedTitik = savedValues[i] ? savedValues[i].titik : '';
                    let savedNote = savedValues[i] ? savedValues[i].note : '';

                    let html = `
                        <div class="duplicate-item" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #2D6BCF;">
                            <h4 style="color: #2D6BCF; margin-bottom: 12px; font-size: 15px;">
                                <i class="fas fa-flask"></i> Sample #${i}
                            </h4>

                            ${kimiaCode && $('#code_sample_kimia_wrapper').is(':visible') ? `
                                                                <div style="margin-bottom: 10px;">
                                                                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 4px;">
                                                                        🧪 Kode Kimia
                                                                    </label>
                                                                    <input type="text" class="form-control" readonly
                                                                           value="${kimiaCodeIncrement}"
                                                                           style="background: white; font-weight: 600; font-size: 13px;">
                                                                </div>
                                                            ` : ''}

                            ${mikroCode && $('#code_sample_mikro_wrapper').is(':visible') ? `
                                                                <div style="margin-bottom: 10px;">
                                                                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 4px;">
                                                                        🦠 Kode Mikrobiologi
                                                                    </label>
                                                                    <input type="text" class="form-control" readonly
                                                                           value="${mikroCodeIncrement}"
                                                                           style="background: white; font-weight: 600; font-size: 13px;">
                                                                </div>
                                                            ` : ''}

                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 4px;">
                                    📍 Titik Lokasi Pengambilan
                                </label>
                                <textarea class="form-control" name="titik_pengambilan_${i}"
                                          placeholder="Contoh: Sumur Desa A, RT 01/RW 02"
                                          style="font-size: 13px; min-height: 60px;">${savedTitik}</textarea>
                            </div>

                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 4px;">
                                    📝 Catatan
                                </label>
                                <textarea class="form-control" name="note_${i}"
                                          placeholder="Catatan tambahan untuk sample ini"
                                          style="font-size: 13px; min-height: 60px;">${savedNote}</textarea>
                            </div>
                        </div>
                    `;
                    $container.append(html);
                }
            } else {
                // Show single input, hide duplicates
                $singleTitikSection.show();
                $singleCatatanSection.show();
                $section.hide();
                $container.empty();
            }
        }

        // Increment sample code for duplicates
        function incrementSampleCode(code, increment) {
            if (!code) return '';

            // Format: AB.01/0016/2025
            const parts = code.split('/');
            if (parts.length === 3) {
                const prefix = parts[0]; // AB.01
                const number = parseInt(parts[1]) || 0; // 0016
                const year = parts[2]; // 2025

                const newNumber = String(number + increment).padStart(4, '0');
                return `${prefix}/${newNumber}/${year}`;
            }

            return code;
        }

        // Function to toggle Pelanggan section based on sampling cost
        window.togglePelangganSection = function() {
            let costSampling = parseFloat($('input[name="cost_sampling_samples"]').val()) || 0;
            const $pelangganSection = $('#pelangganSection');

            if (costSampling > 0) {
                $pelangganSection.show();
            } else {
                $pelangganSection.hide();
                // Clear pelanggan fields when hidden
                $('input[name="nama_pelanggan"]').val('');
                $('input[name="jabatan_pelanggan"]').val('');
                $('input[name="nip_pelanggan"]').val('');
                $('#signature_pelanggan').val('');
                if (signaturePadPelanggan) {
                    signaturePadPelanggan.clear();
                }
            }
        }

        // Event listener for biaya sampling input
        $(document).on('input change', 'input[name="cost_sampling_samples"]', function() {
            // Update all tab summaries when sampling cost changes
            selectedSampleTypes.forEach(function(type) {
                updateTabSummary(type.id);
            });

            // Toggle Pelanggan section based on sampling cost
            togglePelangganSection();
        });

        // Initialize Pelanggan section visibility on page load
        $(document).ready(function() {
            togglePelangganSection();
        });

        // Submit Form
        window.submitForm = function() {
            // Validation - Multiple sample types
            if (selectedSampleTypes.length === 0) {
                alert('Pilih minimal 1 jenis sampel terlebih dahulu!');
                return;
            }

            // Validation - Petugas Pengambil Sampel (minimal 1 harus dipilih)
            const selectedPetugasCheck = $('input[name="petugas_selected[]"]:checked');
            if (selectedPetugasCheck.length === 0) {
                alert('Pilih minimal 1 petugas pengambil sampel');
                return;
            }

            // Validate each sample type has at least one parameter
            let hasError = false;
            let errorMessages = [];

            selectedSampleTypes.forEach(function(type) {
                const config = sampleTypeConfigs[type.id];
                const hasPackets = (config.packets && config.packets.length > 0);
                const hasAdditionalMethods = (config.additional_methods && config.additional_methods.length > 0);

                if (!config || (!hasPackets && !hasAdditionalMethods)) {
                    hasError = true;
                    errorMessages.push(`${type.code}: Belum ada parameter dipilih`);
                }
            });

            if (hasError) {
                alert('Setiap jenis sampel harus memiliki minimal 1 parameter:\n\n' + errorMessages.join('\n'));
                return;
            }

            // Get cost sampling value (declare once at the beginning)
            let costSampling = parseFloat($('input[name="cost_sampling_samples"]').val()) || 0;

            // Get signature data (only if Pelanggan section is visible)
            let signaturePelanggan = null;
            if (costSampling > 0 && signaturePadPelanggan && !signaturePadPelanggan.isEmpty()) {
                signaturePelanggan = signaturePadPelanggan.toDataURL();
            }

            // Set signature to hidden input (only if sampling is required)
            if (costSampling > 0 && signaturePelanggan) {
                $('#signature_pelanggan').val(signaturePelanggan);
            } else {
                $('#signature_pelanggan').val('');
            }

            // Prepare form data with configurations per sample type
            // Create new FormData (don't use form directly to avoid conflicts)
            const formData = new FormData();

            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            if (!csrfToken) {
                alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
                return;
            }
            formData.append('_token', csrfToken);

            // Add basic form fields
            formData.append('id_permohonan_uji', $('input[name="id_permohonan_uji"]').val());
            formData.append('datesampling_samples', $('input[name="datesampling_samples"]').val());
            formData.append('date_sending', $('input[name="date_sending"]').val());
            formData.append('titik_pengambilan', $('textarea[name="titik_pengambilan"]').val() || '');
            formData.append('cost_sampling_samples', $('input[name="cost_sampling_samples"]').val() || '20000');
            formData.append('note', $('textarea[name="note"]').val() || '');
            formData.append('program_samples', $('input[name="program_samples"]').val() || '');

            // Add petugas (from checkboxes)
            selectedPetugasCheck.each(function() {
                formData.append('petugas_selected[]', $(this).val());
            });

            // Add signature fields
            // Only append pelanggan data if sampling is required (cost_sampling_samples > 0)
            if (costSampling > 0) {
                if ($('#signature_pelanggan').val()) {
                    formData.append('signature_pelanggan', $('#signature_pelanggan').val());
                }
                if ($('input[name="jabatan_pelanggan"]').val()) {
                    formData.append('jabatan_pelanggan', $('input[name="jabatan_pelanggan"]').val());
                }
                if ($('input[name="nama_pelanggan"]').val()) {
                    formData.append('nama_pelanggan', $('input[name="nama_pelanggan"]').val());
                }
                if ($('input[name="nip_pelanggan"]').val()) {
                    formData.append('nip_pelanggan', $('input[name="nip_pelanggan"]').val());
                }
            }

            // Add configurations for each sample type
            // IMPORTANT: Each packet creates a separate sample with same group_id
            let sampleIndex = 0;
            selectedSampleTypes.forEach(function(type) {
                const config = sampleTypeConfigs[type.id];

                // Get titik_pengambilan for this sample type
                const titikPengambilan = (config && config.titik_pengambilan) ? config.titik_pengambilan : '';

                // Process each packet separately (each packet = 1 sample)
                if (config.packets && config.packets.length > 0) {
                    config.packets.forEach(function(packet) {
                        // Validate cost_samples for this packet
                        let costSamples = parseFloat(packet.packet_price) || 0;
                        if (isNaN(costSamples) || costSamples < 0) {
                            costSamples = 0;
                        }

                        formData.append(`samples[${sampleIndex}][sample_type_id]`, type.id);
                        formData.append(`samples[${sampleIndex}][packet_id]`, packet.packet_id || '');
                        formData.append(`samples[${sampleIndex}][packet_name]`, packet.packet_name || '');
                        formData.append(`samples[${sampleIndex}][packet_price]`, packet.packet_price || 0);
                        formData.append(`samples[${sampleIndex}][cost_samples]`, costSamples);
                        formData.append(`samples[${sampleIndex}][titik_pengambilan]`, titikPengambilan);

                        // Add methods from this packet
                        if (packet.methods && packet.methods.length > 0) {
                            packet.methods.forEach(function(method, methodIndex) {
                                formData.append(`samples[${sampleIndex}][methods][${methodIndex}]`,
                                    method);
                            });
                        }

                        sampleIndex++;
                    });
                }

                // If there are additional methods (not from packet), create a sample for them too
                if (config.additional_methods && config.additional_methods.length > 0) {
                    // Calculate cost for additional methods
                    let additionalCost = 0;
                    config.additional_methods.forEach(function(method) {
                        additionalCost += parseFloat(method.price) || 0;
                    });

                    // Validate cost
                    if (isNaN(additionalCost) || additionalCost < 0) {
                        additionalCost = 0;
                    }

                    formData.append(`samples[${sampleIndex}][sample_type_id]`, type.id);
                    formData.append(`samples[${sampleIndex}][packet_id]`, '');
                    formData.append(`samples[${sampleIndex}][packet_name]`, '');
                    formData.append(`samples[${sampleIndex}][packet_price]`, 0);
                    formData.append(`samples[${sampleIndex}][cost_samples]`, additionalCost);
                    formData.append(`samples[${sampleIndex}][titik_pengambilan]`, titikPengambilan);

                    // Add additional methods
                    config.additional_methods.forEach(function(method, methodIndex) {
                        formData.append(`samples[${sampleIndex}][methods][${methodIndex}]`, method
                            .method_string);
                    });

                    sampleIndex++;
                }
            });

            // Debug: Log FormData content
            console.log('Submitting to:', $('#samplingForm').attr('action'));
            console.log('FormData entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Show loading
            $('#loadingOverlay').addClass('active');
            $('#submitBtn').prop('disabled', true);

            // Submit via AJAX
            $.ajax({
                url: $('#samplingForm').attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json', // Expect JSON response
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    $('#loadingOverlay').removeClass('active');
                    $('#submitBtn').prop('disabled', false);

                    console.log('Success response:', response);

                    // Check if response is actually HTML (error page)
                    if (typeof response === 'string' && response.trim().startsWith('<!DOCTYPE')) {
                        console.error(
                            'Received HTML instead of JSON. This indicates a server error or redirect.');
                        alert(
                            'Gagal menyimpan: Server mengembalikan halaman error. Silakan cek console untuk detail.'
                        );
                        return;
                    }

                    if (response && (response.status == true || response.success)) {
                        const redirectUrl = response.redirect ||
                            "{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}";
                        // Force full page reload to ensure fresh data (bypass SPA cache)
                        // Always use window.location.href for draft-list to ensure fresh data
                        window.location.href = redirectUrl;
                    } else {
                        alert('Gagal menyimpan: ' + (response.pesan || response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    $('#loadingOverlay').removeClass('active');
                    $('#submitBtn').prop('disabled', false);

                    console.error('AJAX Error:', xhr);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);

                    let message = 'Gagal menyimpan data!';

                    if (xhr.status === 419) {
                        message = 'CSRF token expired. Silakan refresh halaman dan coba lagi.';
                    } else if (xhr.responseJSON && xhr.responseJSON.pesan) {
                        message = xhr.responseJSON.pesan;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('\n');
                    } else if (xhr.responseText) {
                        message += '\n\nDetail: ' + xhr.responseText.substring(0, 200);
                    }

                    alert('Gagal menyimpan: ' + message);
                }
            });
        }
    </script>
</body>

</html>
