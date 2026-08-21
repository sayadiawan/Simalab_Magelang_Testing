<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Sampling Kesmas</title>

    <link href="{{ asset('assets/admin/cdn-local/css/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>

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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
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
            color: #0b3a5c;
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
            border-color: #0b3a5c;
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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            border-color: #0b3a5c;
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
            background: #0b3a5c;
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

        .parameter-item.highlight {
            background-color: #fff3cd;
        }

        .parameter-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #0b3a5c;
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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
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
            border-top: 4px solid #0b3a5c;
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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%) !important;
            border-color: #0b3a5c !important;
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
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div style="text-align: center;">
            <div class="spinner"></div>
            <p style="color: white; margin-top: 15px;">Menyimpan data...</p>
        </div>
    </div>

    <div class="top-bar">
        <div style="font-size: 18px; font-weight: 600;">✏️ Edit Sampel Kesmas</div>
        <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">Petugas: {{ $petugas_name }}</div>
    </div>

    <div class="container">
        @if(isset($backUrl))
        <div style="background: rgba(255, 255, 255, 0.95); padding: 12px 20px; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-size: 14px;">
            <a href="{{ $backUrl }}" style="color: #0b3a5c; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                <span>←</span>
                <span>Kembali</span>
            </a>
        </div>
        @endif
        
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

        <form id="samplingForm" method="POST"
            action="{{ route('mobile.sampling.update', [$permohonan_uji->id_permohonan_uji, $sample_id]) }}">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="id_permohonan_uji" value="{{ $permohonan_uji->id_permohonan_uji }}">
            <input type="hidden" name="jenis_sampel" id="jenis_sampel" value="{{ $sample->typesample_samples }}">
            <input type="hidden" name="program_samples" value="{{ $programs->first()->id_program ?? '' }}">

            <!-- Kode Sampel (Read Only) -->
            @if ($sample->codesample_samples)
                <div class="card">
                    <div class="section-title">
                        <i class="fas fa-barcode"></i> Kode Sampel (Tidak dapat diubah)
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                        <div style="font-weight: 700; font-size: 18px; color: #0b3a5c; letter-spacing: 1px;">
                            {!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Informasi Kode Sampel
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); padding: 15px; border-radius: 10px; text-align: center; border-left: 4px solid #ffc107;">
                        <div style="font-weight: 600; font-size: 14px; color: #856404;">
                            ℹ️ Kode sampel akan di-generate secara otomatis setelah proses input selesai
                        </div>
                    </div>
                </div>
            @endif

            <!-- Jenis Sampel -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-vial"></i> Jenis Sampel <span style="color: red;">*</span>
                </div>
                <div class="btn-picker-grid" id="jenisSampelPicker">
                    @foreach ($sample_types as $type)
                        <button type="button" class="btn-picker btn-jenis-sampel"
                            data-id="{{ $type->id_sample_type }}" data-code="{{ $type->code_sample_type }}">
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

            <!-- Tanggal -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-calendar"></i> Tanggal
                </div>

                <div class="form-group">
                    <label>Tanggal Pengambilan</label>
                    <input type="text" class="form-control" id="datesampling_samples_display"
                        placeholder="dd/MM/yyyy HH:mm"
                        value="{{ $sample->datesampling_samples ? date('d/m/Y H:i', strtotime($sample->datesampling_samples)) : '' }}"
                        required readonly>
                    <input type="hidden" name="datesampling_samples" id="datesampling_samples"
                        value="{{ $sample->datesampling_samples }}">
                </div>

                <div class="form-group">
                    <label>Tanggal Pengiriman</label>
                    <input type="text" class="form-control" id="date_sending_display" placeholder="dd/MM/yyyy HH:mm"
                        value="{{ $sample->date_sending ? date('d/m/Y H:i', strtotime($sample->date_sending)) : '' }}"
                        required readonly>
                    <input type="hidden" name="date_sending" id="date_sending" value="{{ $sample->date_sending }}">
                </div>
            </div>

            <!-- Titik Pengambilan -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i> Titik Pengambilan
                </div>



                <div class="form-group">
                    <label>Lokasi/Titik Pengambilan</label>
                    <textarea class="form-control" name="titik_pengambilan" placeholder="Contoh: Sumur Desa A, RT 01/RW 02">{{ $sample->titik_pengambilan }}</textarea>
                </div>

                <div class="form-group">
                    <label>💰 Biaya Pengambilan Sampel (Lab)</label>
                    <input type="number" class="form-control" name="cost_sampling_samples"
                        value="{{ $sample->cost_sampling_samples ?? 20000 }}" placeholder="20000"
                        style="font-weight: 600; font-size: 16px;">
                    <small style="color: #666; font-size: 11px;">
                        <i class="fas fa-info-circle"></i> Biaya default untuk pengambilan sampel oleh petugas lab
                    </small>
                </div>
            </div>

            <!-- Paket (if available) -->
            <div class="card" id="paketSection" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-box"></i> Paket Tersedia
                </div>
                <div class="btn-picker-grid" id="paketPicker">
                    <!-- Paket buttons will be loaded here -->
                </div>
                <input type="hidden" name="packet[]" id="packetSelect">
            </div>

            <!-- Parameter -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-microscope"></i> Parameter Pengujian <span style="color: red;">*</span>
                </div>

                <!-- Search Box -->
                <div id="parameterSearchBox" style="display: none; margin-bottom: 12px;">
                    <input type="text" id="parameterSearch" class="form-control"
                        placeholder="🔍 Cari parameter..." style="padding-left: 12px;">
                </div>

                <div id="parameterContainer">
                    <p style="text-align: center; color: #999; padding: 20px;">Pilih jenis sampel terlebih dahulu</p>
                </div>

                <div id="selectedParamsCount"
                    style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; display: none;">
                    <strong>Terpilih:</strong> <span id="paramCount">0</span> parameter

                    <!-- Breakdown Biaya -->
                    <div
                        style="margin-top: 12px; padding: 10px; background: white; border-radius: 6px; border-left: 3px solid #11998e;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: #666; font-size: 13px;">🔬 Biaya Parameter:</span>
                            <span id="priceParameters" style="font-weight: 600; color: #333;">Rp 0</span>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #ddd;">
                            <span style="color: #666; font-size: 13px;">🚗 Biaya Pengambilan:</span>
                            <span id="priceSampling" style="font-weight: 600; color: #333;">Rp 20.000</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <strong style="font-size: 15px;">Total Keseluruhan:</strong>
                            <span id="totalPrice" style="color: #11998e; font-size: 18px; font-weight: 700;">Rp
                                20.000</span>
                        </div>
                    </div>

                    <small style="display: block; margin-top: 8px; color: #888; font-size: 11px; font-style: italic;">
                        <i class="fas fa-info-circle"></i> Total untuk tampilan saja, database menyimpan terpisah
                    </small>
                </div>
            </div>

            <!-- Petugas Sampling -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-user-md"></i> Petugas Pengambil Sampel
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 12px;">Pilih Nama Petugas:</label>
                    <div style="margin-bottom: 15px;">
                        <div>
                            <label
                                style="display: flex; align-items: center; cursor: pointer; padding: 10px; background: #f8f9fa; border-radius: 6px; border: 2px solid transparent;"
                                id="optionPilihPetugasLabel">
                                <input type="radio" name="petugas_option" value="pilih" id="optionPilihPetugas"
                                    {{ $sample->pengambil_sampel && $sample->pengambil_sampel != $petugas_name ? 'checked' : '' }}
                                    style="margin-right: 10px; width: 18px; height: 18px;"
                                    onchange="togglePetugasOption()">
                                <span style="font-size: 14px; font-weight: 500;">
                                    <i class="fas fa-list"></i> Pilih dari Daftar Petugas
                                </span>
                            </label>
                        </div>
                        <div>
                            <label
                                style="display: flex; align-items: center; cursor: pointer; padding: 10px; background: #f8f9fa; border-radius: 6px; border: 2px solid transparent;"
                                id="optionLoginLabel">
                                <input type="radio" name="petugas_option" value="login" id="optionLogin"
                                    {{ !$sample->pengambil_sampel || $sample->pengambil_sampel == $petugas_name ? 'checked' : '' }}
                                    style="margin-right: 10px; width: 18px; height: 18px;"
                                    onchange="togglePetugasOption()">
                                <span style="font-size: 14px; font-weight: 500;">
                                    <i class="fas fa-user"></i> Gunakan Nama Login ({{ $petugas_name }})
                                </span>
                            </label>
                        </div>
                    </div>
                    <!-- Dropdown Pilih Petugas -->
                    <div id="petugasDropdownSection">
                        <label style="font-size: 13px; color: #666; margin-bottom: 5px; display: block;">
                            Nama Petugas:
                        </label>
                        <select class="form-control" name="petugas_selected" id="petugasSelect">
                            <option value="">-- Pilih Petugas --</option>
                            @foreach ($petugas_list as $petugas)
                                <option value="{{ $petugas['name'] }}"
                                    {{ $sample->pengambil_sampel == $petugas['name'] ? 'selected' : '' }}>
                                    {{ $petugas['name'] }} ({{ $petugas['lab'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Hidden input for login name -->
                    <input type="hidden" name="petugas_login_name" value="{{ $petugas_name }}">
                </div>
            </div>

            <!-- Catatan -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i> Catatan
                </div>

                <div class="form-group">
                    <textarea class="form-control" name="note" placeholder="Catatan tambahan (opsional)">{{ $sample->note_samples }}</textarea>
                </div>
            </div>

            <!-- Hidden cost field -->
            <input type="hidden" name="cost_samples" id="cost_samples" value="0">

        </form>
    </div>

    <div class="floating-submit">
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-submit"
                onclick="window.location.href='{{ route('mobile.sampling.form', $id) }}'"
                style="background: #6c757d; flex: 0.4;">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <button type="button" class="btn-submit" id="submitBtn" onclick="submitForm()" style="flex: 1;">
                <i class="fas fa-save"></i> UPDATE SAMPEL
            </button>
        </div>
    </div>

    <script>
        let selectedJenisSampel = '{{ $sample->typesample_samples }}';
        let selectedParameters = [];
        let selectedPacket = '{{ $sample->packet_id ?? '' }}'; // Pre-selected packet from database

        // Pre-selected method IDs from database
        const preSelectedMethods = @json($selected_methods ?? []);

        // Initialize Flatpickr for date inputs
        $(document).ready(function() {
            // Pre-select jenis sampel
            if (selectedJenisSampel) {
                $('.btn-jenis-sampel[data-id="' + selectedJenisSampel + '"]').addClass('active');
                $('#jenis_sampel').val(selectedJenisSampel);

                const jenisBtn = $('.btn-jenis-sampel[data-id="' + selectedJenisSampel + '"]');
                const jenisName = jenisBtn.text().trim();
                const jenisCode = jenisBtn.data('code') || 'XX';
                $('#selectedJenisName').text(jenisName);
                $('#selectedJenisText').show();

                // Generate sample code
                generateSampleCode(jenisCode);

                // Load paket and parameters
                loadPaket(selectedJenisSampel);
                loadParameters(selectedJenisSampel);
            }

            const now = new Date();

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
        });

        // Jenis Sampel Picker
        $('.btn-jenis-sampel').on('click', function() {
            $('.btn-jenis-sampel').removeClass('active');
            $(this).addClass('active');

            selectedJenisSampel = $(this).data('id');
            $('#jenis_sampel').val(selectedJenisSampel);

            const jenisName = $(this).text().trim();
            const jenisCode = $(this).data('code') || 'XX';
            $('#selectedJenisName').text(jenisName);
            $('#selectedJenisText').show();

            // Generate sample code
            generateSampleCode(jenisCode);

            // Load paket and parameters
            loadPaket(selectedJenisSampel);
            loadParameters(selectedJenisSampel);
        });

        // Load Paket
        function loadPaket(jenisSampelId) {
            $.ajax({
                url: '/api/packet/' + jenisSampelId,
                type: 'POST',
                success: function(response) {
                    const results = response.results;

                    if (results && results.length > 0) {
                        $('#paketSection').show();
                        $('#paketPicker').empty();

                        // Don't reset selectedPacket if it's already set from database
                        const preSelectedPacketId = selectedPacket;

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

                        // Auto-select pre-selected packet from database
                        if (preSelectedPacketId) {
                            setTimeout(function() {
                                const $preSelectedBtn = $(
                                    `.btn-paket[data-id="${preSelectedPacketId}"]`);
                                if ($preSelectedBtn.length > 0) {
                                    $preSelectedBtn.addClass('active');
                                    $('#packetSelect').val(preSelectedPacketId);
                                    loadPaketParameters(preSelectedPacketId);
                                }
                            }, 200);
                        }
                    } else {
                        $('#paketSection').hide();
                    }
                }
            });
        }

        // Load Paket Parameters
        function loadPaketParameters(paketId) {
            const url = "{{ route('mobile.sampling.getdetail_sample_type', ['id' => $id, 'sample_type_id' => '#']) }}"
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
            const url = "{{ route('mobile.sampling.getbaku_mutu', ['id' => $id, 'sample_type_id' => '#']) }}".replace('#',
                jenisSampelId);

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
                                // Check if this method is pre-selected
                                const isChecked = preSelectedMethods.includes(method.id) ?
                                    'checked' : '';

                                html += `
                                    <div class="parameter-item">
                                        <input type="checkbox" class="param-checkbox" name="method[]" 
                                               value="${method.id}_${method.labId}_${method.price}" 
                                               data-price="${method.price}" 
                                               data-id="${method.id}"
                                               data-labid="${method.labId}"
                                               ${isChecked}
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
        function updateParameterCount() {
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

        // Event listener for biaya sampling input
        $(document).on('input change', 'input[name="cost_sampling_samples"]', function() {
            updateParameterCount(); // Update total when sampling cost changes
        });

        // Submit Form
        function togglePetugasOption() {
            const option = $('input[name="petugas_option"]:checked').val();
            const $dropdown = $('#petugasDropdownSection');
            const $select = $('#petugasSelect');

            // Remove active styling from both labels
            $('#optionPilihPetugasLabel, #optionLoginLabel').css('border-color', 'transparent');

            if (option === 'pilih') {
                $dropdown.show();
                $select.prop('required', true);
                $('#optionPilihPetugasLabel').css('border-color', '#11998e');
            } else {
                $dropdown.hide();
                $select.prop('required', false);
                $('#optionLoginLabel').css('border-color', '#11998e');
            }
        }

        function submitForm() {
            // Validation
            if (!selectedJenisSampel) {
                alert('Pilih jenis sampel terlebih dahulu!');
                return;
            }

            const checkedParams = $('.param-checkbox:checked').length;
            if (checkedParams === 0) {
                alert('Pilih minimal 1 parameter pengujian!');
                return;
            }

            // Show loading
            $('#loadingOverlay').addClass('active');
            $('#submitBtn').prop('disabled', true);

            // Submit
            $('#samplingForm').submit();
        }

        // Initialize petugas option visibility on page load
        $(document).ready(function() {
            togglePetugasOption();
        });
    </script>
</body>

</html>
