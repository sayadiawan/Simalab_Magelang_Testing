<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verifikasi Hasil - Klinik</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 10px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            text-align: right;
            flex: 1;
            margin-left: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .parameter-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0b3a5c;
        }

        .parameter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .parameter-name {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            flex: 1;
        }

        .input-group-mobile {
            margin-bottom: 15px;
        }

        .input-group-mobile label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #0b3a5c;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #fff3cd;
            color: #856404;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .step-item {
            flex: 1;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .step-item.active {
            color: #0b3a5c;
            font-weight: 600;
        }

        .step-item.completed {
            color: #28a745;
        }

        .result-display {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            min-height: 40px;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        .komentar-display {
            padding: 10px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 13px;
        }

        .komentar-display i {
            color: #856404;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 14px;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: none;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-header .close {
            color: white;
            opacity: 0.9;
        }

        .modal-footer {
            border-top: 1px solid #e0e0e0;
        }

        .hidden-field {
            display: none;
        }

        .status-verifikasi-mobile {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .status-verifikasi-mobile.status-pending {
            background-color: #ffc107 !important;
            color: #212529 !important;
            border-color: #ffc107 !important;
        }

        .status-verifikasi-mobile.status-approved {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
        }

        .status-verifikasi-mobile.status-rejected {
            background-color: #dc3545 !important;
            color: white !important;
            border-color: #dc3545 !important;
        }

        .status-verifikasi-mobile.status-corrected {
            background-color: #17a2b8 !important;
            color: white !important;
            border-color: #17a2b8 !important;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
            border: none;
        }

        .btn-info:hover {
            background: #138496;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Inline editing styles for mobile */
        .inline-hasil-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .inline-hasil-input:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }
        
        .inline-hasil-editor {
            width: 100%;
            min-height: 50px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
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
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }
        
        .inline-hasil-editor[data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #999;
        }
        
        .inline-keterangan-editor {
            min-height: 60px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: text;
            transition: all 0.3s;
        }
        
        .inline-keterangan-editor:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }
        
        .result-badge-inline {
            margin-top: 10px;
            display: inline-block;
        }
        
        .result-badge-inline .badge {
            font-size: 13px;
            padding: 8px 12px;
        }
        
        .hasil-input-container {
            position: relative;
            margin-bottom: 10px;
        }
        
        .hasil-navigation-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }
        
        .nav-arrow-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #0b3a5c;
            border-radius: 8px;
            background: white;
            color: #0b3a5c;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            position: relative;
            z-index: 10;
            -webkit-tap-highlight-color: transparent;
        }
        
        .nav-arrow-btn:hover {
            background: #0b3a5c;
            color: white;
        }
        
        .nav-arrow-btn:active {
            transform: scale(0.95);
        }
        
        .nav-arrow-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #e0e0e0;
            border-color: #ccc;
            color: #999;
        }
        
        .hasil-button-with-nav {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }
        
        .hasil-button-with-nav .btn {
            flex: 1;
        }
        
        .hasil-button-with-nav .nav-arrow-btn {
            flex: 0 0 auto;
            width: 50px;
            padding: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>✅ VERIFIKASI HASIL</h1>
            <p>Step 4 dari 4</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <span>⚠️</span>
                {{ session('error') }}
            </div>
        @endif

        <div class="step-indicator">
            <div class="step-item completed">1. Penerimaan</div>
            <div class="step-item completed">2. Pengolah</div>
            <div class="step-item completed">3. Pemeriksa</div>
            <div class="step-item active">4. Verifikasi</div>
        </div>

        <div class="card">
            <div class="card-title" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>📋</span>
                    <span>Informasi Permohonan</span>
                </div>
                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#infoSampleModal" style="padding: 6px 12px; font-size: 12px; width: auto;">
                    <i class="fa fa-info-circle"></i> Info Sample
                </button>
            </div>
            <div class="info-row">
                <span class="info-label">No. Register:</span>
                <span class="info-value">{{ $permohonan->getDisplayNoregister() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Rekam Medis:</span>
                <span class="info-value">
                    @if ($permohonan->pasien && $permohonan->pasien->tgllahir_pasien)
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $permohonan->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) ($permohonan->pasien->no_rekammedis_pasien ?? 0), 4, '0', STR_PAD_LEFT) }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tgl. Register:</span>
                <span class="info-value">
                    @if ($permohonan->tglregister_permohonan_uji_klinik)
                        {{ \Carbon\Carbon::parse($permohonan->tglregister_permohonan_uji_klinik)->locale('id')->isoFormat('D MMMM YYYY') }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Pasien:</span>
                <span class="info-value">{{ $permohonan->pasien->nama_pasien ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Usia:</span>
                <span class="info-value">
                    @if ($permohonan->umurtahun_pasien_permohonan_uji_klinik || $permohonan->umurbulan_pasien_permohonan_uji_klinik || $permohonan->umurhari_pasien_permohonan_uji_klinik)
                        {{ $permohonan->umurtahun_pasien_permohonan_uji_klinik ?? 0 }} tahun 
                        {{ $permohonan->umurbulan_pasien_permohonan_uji_klinik ?? 0 }} bulan 
                        {{ $permohonan->umurhari_pasien_permohonan_uji_klinik ?? 0 }} hari
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Kelamin:</span>
                <span class="info-value">
                    @if ($permohonan->pasien && $permohonan->pasien->gender_pasien)
                        {{ $permohonan->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat Pasien:</span>
                <span class="info-value">{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($permohonan->pasien) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Telepon:</span>
                <span class="info-value">{{ $permohonan->pasien->telepon_pasien ?? '-' }}</span>
            </div>
        </div>

        <form id="formVerifikasi" method="POST" action="{{ route('mobile.testing.klinik.storeVerifikasi', $id) }}">
            @csrf
            <div class="card">
                <div class="card-title">
                    <span>⏰</span>
                    <span>Waktu Verifikasi</span>
                </div>
                <div class="form-group">
                    <label for="waktu">Waktu <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="waktu" name="waktu" 
                        value="{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}" 
                        required placeholder="HH:mm">
                </div>
            </div>

            <div class="card">
                <div class="card-title">
                    <span>📊</span>
                    <span>Verifikasi Hasil Pemeriksaan</span>
                </div>
                @if ($parameters && count($parameters) > 0)
                    @foreach ($parameters as $index => $parameter)
                        @php
                            // Define variables first before using them
                            $baku_mutu_selected = $parameter->baku_mutu_data ?? [];
                            $baku_mutu_multiple = $parameter->baku_mutu_multiple ?? [];
                            $current_result = $parameter->hasil_permohonan_uji_parameter_klinik ?? '';
                            $status_verifikasi = $parameter->status_verifikasi ?? 'approved';
                            if (empty($status_verifikasi) || $status_verifikasi == '') {
                                $status_verifikasi = 'approved';
                            }
                            $komentar_verifikasi = $parameter->komentar_verifikasi ?? '';
                            $is_option = $parameter->parametersatuanklinik->is_option ?? 0;
                            $option_value = $parameter->parametersatuanklinik->option ?? '';
                            $options = [];
                            if ($is_option == 1 && !empty($option_value)) {
                                $options = array_map('trim', explode(',', $option_value));
                            }
                            
                            // Get selected IDs for checking
                            $selected_ids = [];
                            if (is_array($baku_mutu_selected) && count($baku_mutu_selected) > 0) {
                                $selected_ids = array_map(function($bm) {
                                    return is_array($bm) ? ($bm['id_baku_mutu'] ?? null) : ($bm->id_baku_mutu ?? null);
                                }, $baku_mutu_selected);
                                $selected_ids = array_filter($selected_ids);
                            }
                        @endphp
                        <div class="parameter-card">
                            <div class="parameter-header">
                                <div class="parameter-name">
                                    {{ $index + 1 }}. {{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}
                                </div>
                                <div class="status-toggle">
                                    <select class="form-control status-verifikasi-mobile" 
                                        id="status_verifikasi_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        name="parameters[{{ $index }}][status_verifikasi]"
                                        data-parameter-id="{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        style="min-width: 140px; font-size: 13px; padding: 6px 10px;">
                                        <option value="pending" {{ $status_verifikasi == 'pending' ? 'selected' : '' }}>
                                            Belum Diverifikasi
                                        </option>
                                        <option value="approved" {{ $status_verifikasi == 'approved' ? 'selected' : '' }}>
                                            Diterima
                                        </option>
                                        <option value="rejected" {{ $status_verifikasi == 'rejected' ? 'selected' : '' }}>
                                            Ditolak
                                        </option>
                                        <option value="corrected" {{ $status_verifikasi == 'corrected' ? 'selected' : '' }}>
                                            Diperbaiki
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" 
                                name="parameters[{{ $index }}][id]" 
                                value="{{ $parameter->id_permohonan_uji_parameter_klinik }}">

                            @if (count($baku_mutu_selected) > 0 || count($baku_mutu_multiple) > 0)
                                <div class="input-group-mobile">
                                    <label>Kadar Maksimum Yang Diperbolehkan</label>
                                    @if (count($baku_mutu_multiple) > 1)
                                        <!-- Multiple baku mutu - sorted from min to max -->
                                        <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 14px;">
                                            @php
                                                // Sort multiple baku mutu from minimal to maximal (nilai terkecil ke terbesar)
                                                // Menggunakan nilai minimal sebagai prioritas utama
                                                usort($baku_mutu_multiple, function($a, $b) {
                                                    $getNumeric = function($val) {
                                                        if ($val === null || $val === '') return 999999999;
                                                        // Handle special cases like "<100", "≥190", etc.
                                                        if (preg_match('/^[<>≥≤]\s*([\d.,]+)/', $val, $matches)) {
                                                            return (float) str_replace(',', '.', $matches[1]);
                                                        }
                                                        // Handle range like "100-129"
                                                        if (preg_match('/([\d.,]+)\s*-\s*([\d.,]+)/', $val, $matches)) {
                                                            return (float) str_replace(',', '.', $matches[1]);
                                                        }
                                                        $cleaned = preg_replace('/[^0-9\,\.-]/', '', $val);
                                                        $cleaned = str_replace(',', '.', $cleaned);
                                                        return (float) $cleaned;
                                                    };
                                                    
                                                    // Get min and max values for sorting
                                                    // Prioritize 'min' field, then 'max', then 'nilai_baku_mutu'
                                                    $minA = $getNumeric($a['min'] ?? ($a['nilai_baku_mutu'] ?? null));
                                                    $maxA = $getNumeric($a['max'] ?? ($a['nilai_baku_mutu'] ?? null));
                                                    $minB = $getNumeric($b['min'] ?? ($b['nilai_baku_mutu'] ?? null));
                                                    $maxB = $getNumeric($b['max'] ?? ($b['nilai_baku_mutu'] ?? null));
                                                    
                                                    // Sort by min first (ascending: smallest to largest)
                                                    if ($minA != $minB) {
                                                        return $minA <=> $minB;
                                                    }
                                                    // If min equal, sort by max (ascending)
                                                    return $maxA <=> $maxB;
                                                });
                                            @endphp
                                            @foreach ($baku_mutu_multiple as $bm)
                                                @php
                                                    $is_selected = in_array($bm['id_baku_mutu'] ?? null, $selected_ids);
                                                @endphp
                                                <div style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 6px; border-left: 3px solid {{ $is_selected ? '#28a745' : '#ccc' }};">
                                                    <strong>{!! rubahNilaikeForm($bm['nilai_baku_mutu'] ?? '-') !!}</strong>
                                                    @if (isset($bm['gender_baku_mutu']) && $bm['gender_baku_mutu'])
                                                        <small style="color: #666;">({{ $bm['gender_baku_mutu'] == 'L' ? 'Laki-laki' : 'Perempuan' }})</small>
                                                    @endif
                                                    @if (isset($bm['minimal_umur_baku_mutu']) && isset($bm['maksimal_umur_baku_mutu']))
                                                        <small style="color: #666;">Umur: {{ $bm['minimal_umur_baku_mutu'] }}-{{ $bm['maksimal_umur_baku_mutu'] }} tahun</small>
                                                    @endif
                                                    @if ($is_selected)
                                                        <span class="badge badge-success" style="margin-left: 8px;">Dipilih</span>
                                                    @endif
                                                    @if (!empty($bm['kesimpulan_baku_mutu']))
                                                        <br><small style="color: #17a2b8; margin-top: 4px; display: block;">
                                                            <i class="fa fa-info-circle"></i>
                                                            {!! rubahNilaikeForm($bm['kesimpulan_baku_mutu']) !!}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif (count($baku_mutu_selected) > 0)
                                        <!-- Single or multiple selected baku mutu -->
                                        <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 14px;">
                                            @foreach ($baku_mutu_selected as $bm)
                                                @php
                                                    $bm_array = is_array($bm) ? $bm : $bm->toArray();
                                                @endphp
                                                <div style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 6px; border-left: 3px solid #28a745;">
                                                    <strong>{!! rubahNilaikeForm($bm_array['nilai_baku_mutu'] ?? '-') !!}</strong>
                                                    @if (isset($bm_array['gender_baku_mutu']) && $bm_array['gender_baku_mutu'])
                                                        <small style="color: #666;">({{ $bm_array['gender_baku_mutu'] == 'L' ? 'Laki-laki' : 'Perempuan' }})</small>
                                                    @endif
                                                    @if (isset($bm_array['minimal_umur_baku_mutu']) && isset($bm_array['maksimal_umur_baku_mutu']))
                                                        <small style="color: #666;">Umur: {{ $bm_array['minimal_umur_baku_mutu'] }}-{{ $bm_array['maksimal_umur_baku_mutu'] }} tahun</small>
                                                    @endif
                                                    <span class="badge badge-success" style="margin-left: 8px;">Dipilih</span>
                                                    @if (!empty($bm_array['kesimpulan_baku_mutu']))
                                                        <br><small style="color: #17a2b8; margin-top: 4px; display: block;">
                                                            <i class="fa fa-info-circle"></i>
                                                            {!! rubahNilaikeForm($bm_array['kesimpulan_baku_mutu']) !!}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($parameter->unit)
                                <div class="input-group-mobile">
                                    <label>Satuan</label>
                                    <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 14px;">
                                        {{ $parameter->unit->name_unit ?? '-' }}
                                    </div>
                                </div>
                            @endif

                            <div class="input-group-mobile">
                                <label>Hasil Pemeriksaan</label>
                                <div class="result-display" style="padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 14px; margin-bottom: 10px;">
                                    @php
                                        // Get first selected baku mutu for display (fallback)
                                        $first_baku_mutu = null;
                                        if (count($baku_mutu_selected) > 0) {
                                            $first_bm = $baku_mutu_selected[0];
                                            $first_baku_mutu = is_array($first_bm) ? $first_bm : $first_bm->toArray();
                                        } elseif (count($baku_mutu_multiple) > 0) {
                                            $first_baku_mutu = $baku_mutu_multiple[0];
                                        }
                                        
                                        // Use cek_hasil_color with proper baku mutu data
                                        $min = $first_baku_mutu['min'] ?? null;
                                        $max = $first_baku_mutu['max'] ?? null;
                                        $equal = $first_baku_mutu['equal'] ?? null;
                                        $numberFormat = $parameter->parametersatuanklinik->number_format ?? 'en';
                                    @endphp
                                    {!! cek_hasil_color(
                                        $current_result,
                                        $min,
                                        $max,
                                        $equal,
                                        'result_display_' . $parameter->id_permohonan_uji_parameter_klinik,
                                        $parameter->offset_baku_mutu ?? 'default',
                                        $numberFormat
                                    ) !!}
                                </div>
                                
                                <!-- Edit Hasil - Inline Editing (no modal) -->
                                <div class="input-group-mobile" style="margin-top: 10px;">
                                    <label>Koreksi Hasil:</label>
                                    <!-- Hidden textarea for form submission - will be converted to inline input by mobile-inline-editing.js -->
                                    @php
                                        // Convert selected baku mutu to array format for JavaScript
                                        $selected_baku_mutu_array = [];
                                        foreach ($baku_mutu_selected as $bm) {
                                            $selected_baku_mutu_array[] = is_array($bm) ? $bm : $bm->toArray();
                                        }
                                    @endphp
                                    <textarea class="form-control result_method hidden-field"
                                        name="parameters[{{ $index }}][hasil_koreksi]"
                                        id="hasil_koreksi_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-min="{{ $first_baku_mutu['min'] ?? '' }}"
                                        data-max="{{ $first_baku_mutu['max'] ?? '' }}"
                                        data-equal="{{ $first_baku_mutu['equal'] ?? '' }}"
                                        data-is-option="{{ $is_option ? '1' : '0' }}"
                                        data-option-values="{{ $is_option ? json_encode($options) : '[]' }}"
                                        data-number-format="{{ $parameter->parametersatuanklinik->number_format ?? 'en' }}"
                                        data-hasil-analis="{{ $current_result }}"
                                        data-parameter-id="{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-offset-baku-mutu="{{ $parameter->offset_baku_mutu ?? 'default' }}"
                                        data-multiple-baku-mutu="{{ json_encode($baku_mutu_multiple) }}"
                                        data-selected-baku-mutu="{{ json_encode($selected_baku_mutu_array) }}"
                                        placeholder="Kosongkan jika tidak ada koreksi"
                                        style="display: none;">{!! $current_result !!}</textarea>
                                    
                                    <!-- Result Preview - Akan diupdate oleh mobile-inline-editing.js dengan hasil koreksi -->
                                    <div class="result-preview"
                                        id="result_output_method_{{ $parameter->id_permohonan_uji_parameter_klinik }}">
                                        <span class="text-muted">-</span>
                                    </div>
                                    
                                    <!-- History Comparison Button -->
                                    <button type="button" 
                                        class="btn btn-sm btn-info btn-history-comparison" 
                                        style="width: 100%; margin-top: 10px; margin-bottom: 10px; padding: 10px; font-size: 14px;"
                                        data-toggle="modal" 
                                        data-target="#historyComparisonModal_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-parameter-name="{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? 'Parameter' }}"
                                        data-hasil-analis="{{ $current_result }}"
                                        data-hasil-koreksi-id="hasil_koreksi_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-min="{{ $first_baku_mutu['min'] ?? '' }}"
                                        data-max="{{ $first_baku_mutu['max'] ?? '' }}"
                                        data-equal="{{ $first_baku_mutu['equal'] ?? '' }}"
                                        data-number-format="{{ $parameter->parametersatuanklinik->number_format ?? 'en' }}"
                                        data-offset-baku-mutu="{{ $parameter->offset_baku_mutu ?? 'default' }}"
                                        data-multiple-baku-mutu="{{ json_encode($baku_mutu_multiple) }}"
                                        data-selected-baku-mutu="{{ json_encode($selected_baku_mutu_array) }}">
                                        <i class="fa fa-history"></i> Lihat History & Perbandingan
                                    </button>
                                    
                                    <!-- Navigation arrows -->
                                    <div class="hasil-navigation-buttons">
                                        <button type="button" class="nav-arrow-btn nav-arrow-up" 
                                            data-parameter-index="{{ $index }}"
                                            title="Parameter Sebelumnya">
                                            <i class="fa fa-arrow-up"></i>
                                        </button>
                                        <button type="button" class="nav-arrow-btn nav-arrow-down" 
                                            data-parameter-index="{{ $index }}"
                                            title="Parameter Berikutnya">
                                            <i class="fa fa-arrow-down"></i>
                                        </button>
                                    </div>
                                    
                                    <small style="color: #999; font-size: 12px; display: block; margin-top: 5px;">
                                        <i class="fa fa-info-circle"></i> Kosongkan jika tidak ada koreksi
                                    </small>
                                    
                                    <!-- History Comparison Modal -->
                                    <div class="modal fade" id="historyComparisonModal_{{ $parameter->id_permohonan_uji_parameter_klinik }}" tabindex="-1" role="dialog" aria-labelledby="historyComparisonModalLabel_{{ $parameter->id_permohonan_uji_parameter_klinik }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                                                    <h5 class="modal-title" id="historyComparisonModalLabel_{{ $parameter->id_permohonan_uji_parameter_klinik }}">
                                                        <i class="fa fa-history mr-2"></i>History & Perbandingan Hasil
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                                                    <div class="parameter-name-display" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                                        <h6 style="margin: 0; font-weight: 600; color: #333;">
                                                            <i class="fa fa-flask mr-2"></i>{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? 'Parameter' }}
                                                        </h6>
                                                    </div>
                                                    
                                                    <!-- Hasil Analis Section -->
                                                    <div class="comparison-section" style="margin-bottom: 20px;">
                                                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                            <i class="fa fa-user-md" style="color: #0b3a5c; font-size: 18px; margin-right: 8px;"></i>
                                                            <h6 style="margin: 0; font-weight: 600; color: #333;">Hasil Analis (Pemeriksa)</h6>
                                                        </div>
                                                        <div class="result-box" style="padding: 15px; background: #f8f9fa; border-left: 4px solid #0b3a5c; border-radius: 6px;">
                                                            <div id="history_hasil_analis_{{ $parameter->id_permohonan_uji_parameter_klinik }}" style="font-size: 16px; color: #333;">
                                                                {!! cek_hasil_color(
                                                                    $current_result,
                                                                    $baku_mutu['min'] ?? null,
                                                                    $baku_mutu['max'] ?? null,
                                                                    $baku_mutu['equal'] ?? null,
                                                                    'history_hasil_analis_' . $parameter->id_permohonan_uji_parameter_klinik,
                                                                    $parameter->offset_baku_mutu ?? 'default',
                                                                    $parameter->parametersatuanklinik->number_format ?? 'en'
                                                                ) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Hasil Koreksi Section -->
                                                    <div class="comparison-section" style="margin-bottom: 20px;">
                                                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                            <i class="fa fa-check-circle" style="color: #28a745; font-size: 18px; margin-right: 8px;"></i>
                                                            <h6 style="margin: 0; font-weight: 600; color: #333;">Hasil Koreksi (Verifikator)</h6>
                                                        </div>
                                                        <div class="result-box" style="padding: 15px; background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 6px;">
                                                            <div id="history_hasil_koreksi_{{ $parameter->id_permohonan_uji_parameter_klinik }}" style="font-size: 16px; color: #333;">
                                                                <span class="text-muted">Belum ada koreksi</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Perbandingan Section -->
                                                    <div class="comparison-section">
                                                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                            <i class="fa fa-balance-scale" style="color: #ffc107; font-size: 18px; margin-right: 8px;"></i>
                                                            <h6 style="margin: 0; font-weight: 600; color: #333;">Perbandingan</h6>
                                                        </div>
                                                        <div class="comparison-box" id="history_comparison_{{ $parameter->id_permohonan_uji_parameter_klinik }}" style="padding: 15px; background: #fffbf0; border-left: 4px solid #ffc107; border-radius: 6px;">
                                                            <div style="font-size: 14px; color: #856404;">
                                                                <i class="fa fa-info-circle"></i> Belum ada perbandingan
                                                            </div>
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
                                </div>
                            </div>
                            
                            <!-- Komentar Verifikasi -->
                            <div class="input-group-mobile">
                                <label>Komentar Verifikasi</label>
                                @if (!empty($komentar_verifikasi))
                                    <div class="komentar-display" style="margin-bottom: 10px;">
                                        <strong><i class="fa fa-comment-dots"></i> Komentar Sebelumnya:</strong>
                                        <div style="margin-top: 5px; color: #856404;">
                                            {!! nl2br(e($komentar_verifikasi)) !!}
                                        </div>
                                    </div>
                                @endif
                                <textarea class="form-control" 
                                    name="parameters[{{ $index }}][komentar]"
                                    id="komentar_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                    rows="3"
                                    placeholder="Masukkan komentar baru atau edit komentar sebelumnya (opsional)"
                                    style="font-size: 14px; min-height: 80px;"></textarea>
                                <small style="color: #999; font-size: 12px; display: block; margin-top: 5px;">
                                    <i class="fa fa-info-circle"></i> Komentar akan ditampilkan ke pemeriksa jika hasil ditolak atau perlu diperbaiki
                                </small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-danger">
                        <span>⚠️</span>
                        Tidak ada parameter yang perlu diverifikasi.
                    </div>
                @endif
            </div>

            @if (!$is_analis)
                <div class="card">
                    <div class="card-title">
                        <span>👤</span>
                        <span>Pilih Petugas</span>
                    </div>
                    <div class="form-group">
                        <label for="nama_petugas">Nama Petugas <span style="color: red">*</span></label>
                        @if (isset($selected_petugas) && $selected_petugas)
                            {{-- Jika petugas user ada di list, tampilkan sebagai text dan hidden input --}}
                            <input type="text" class="form-control" value="{{ $selected_petugas }}" readonly style="background-color: #f0f0f0;">
                            <input type="hidden" id="nama_petugas" name="nama_petugas" value="{{ $selected_petugas }}">
                            <small class="form-text text-muted" style="margin-top: 5px; display: block;">
                                <i class="fas fa-info-circle"></i> Petugas terdeteksi dari akun Anda
                            </small>
                        @else
                            {{-- Jika petugas user tidak ada di list, tampilkan dropdown --}}
                            <select class="form-control" id="nama_petugas" name="nama_petugas" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach ($petugas_list as $petugas)
                                    <option value="{{ $petugas['name'] }}" 
                                        {{ ($verification && $verification->nama_petugas == $petugas['name']) ? 'selected' : '' }}>
                                        {{ $petugas['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="info-row">
                        <span class="info-label">Petugas:</span>
                        <span class="info-value">{{ $user_name }}</span>
                    </div>
                </div>
            @endif

            <a href="{{ route('mobile.testing.klinik.status', $id) }}" class="btn btn-secondary" style="margin-bottom: 10px;">
                <span>📊</span>
                <span>Lihat Status</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <span>💾</span>
                <span>Simpan Verifikasi</span>
            </button>
        </form>
    </div>

    <!-- Modals removed - using inline editing instead -->

    <script>
        (function() {
            function initApp() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initApp, 100);
                    return;
                }

                jQuery(document).ready(function($) {
                    // Function to update status verifikasi dropdown color
                    function updateStatusVerifikasiColor() {
                        $('.status-verifikasi-mobile').each(function() {
                            var $select = $(this);
                            var value = $select.val();
                            var parameterId = $select.data('parameter-id');
                            var komentarId = 'komentar_' + parameterId;
                            var $komentarField = $('#' + komentarId);
                            
                            // Remove all status classes
                            $select.removeClass('status-pending status-approved status-rejected status-corrected');
                            
                            // Add appropriate class and styling based on value
                            if (value === 'pending') {
                                $select.addClass('status-pending').css({
                                    'background-color': '#ffc107',
                                    'color': '#212529',
                                    'border-color': '#ffc107'
                                });
                                if ($komentarField.length) {
                                    $komentarField.attr('placeholder', 'Masukkan komentar (opsional)');
                                    $komentarField.prop('required', false);
                                }
                            } else if (value === 'approved') {
                                $select.addClass('status-approved').css({
                                    'background-color': '#28a745',
                                    'color': 'white',
                                    'border-color': '#28a745'
                                });
                                if ($komentarField.length) {
                                    $komentarField.attr('placeholder', 'Masukkan komentar (opsional)');
                                    $komentarField.prop('required', false);
                                }
                            } else if (value === 'rejected') {
                                $select.addClass('status-rejected').css({
                                    'background-color': '#dc3545',
                                    'color': 'white',
                                    'border-color': '#dc3545'
                                });
                                if ($komentarField.length) {
                                    $komentarField.attr('placeholder', 'Masukkan komentar mengapa hasil ditolak (disarankan)');
                                    $komentarField.prop('required', false);
                                }
                            } else if (value === 'corrected') {
                                $select.addClass('status-corrected').css({
                                    'background-color': '#17a2b8',
                                    'color': 'white',
                                    'border-color': '#17a2b8'
                                });
                                if ($komentarField.length) {
                                    $komentarField.attr('placeholder', 'Masukkan komentar tentang perbaikan (disarankan)');
                                    $komentarField.prop('required', false);
                                }
                            }
                        });
                    }

                    // Initialize time picker
                    flatpickr("#waktu", {
                        enableTime: true,
                        noCalendar: true,
                        allowInput: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        defaultDate: "{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}"
                    });

                    // Initialize status verifikasi dropdown colors
                    updateStatusVerifikasiColor();

                    // Update color when status changes
                    $(document).on('change', '.status-verifikasi-mobile', function() {
                        updateStatusVerifikasiColor();
                    });

                    // Form submission
                    document.getElementById('formVerifikasi').addEventListener('submit', function(e) {
                        const waktu = document.getElementById('waktu').value;
                        if (!waktu || !/^\d{1,2}:\d{2}$/.test(waktu)) {
                            e.preventDefault();
                            alert('Mohon masukkan waktu yang valid (format: HH:mm)');
                            return false;
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initApp);
            } else {
                initApp();
            }
        })();
    </script>

    <!-- Modal Info Sample -->
    <div class="modal fade" id="infoSampleModal" tabindex="-1" role="dialog" aria-labelledby="infoSampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%); color: white;">
                    <h5 class="modal-title" id="infoSampleModalLabel">
                        <i class="fa fa-info-circle mr-2"></i>Informasi Sample
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <!-- Informasi Pengambilan Sample -->
                    @if ($pengambilan_sample)
                        <div class="card" style="margin-bottom: 15px;">
                            <div class="card-title">
                                <span>✏️</span>
                                <span>Informasi Pengambilan Sample</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanggal & Waktu Sampling:</span>
                                <span class="info-value">
                                    @if ($pengambilan_verification && $pengambilan_verification->start_date)
                                        {{ \Carbon\Carbon::parse($pengambilan_verification->start_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Sampling:</span>
                                <span class="info-value">
                                    @if ($pengambilan_sample->status_sampling == 'berhasil')
                                        <span class="badge badge-success">Berhasil</span>
                                    @elseif ($pengambilan_sample->status_sampling == 'gagal')
                                        <span class="badge badge-danger">Gagal</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $pengambilan_sample->status_sampling ?? '-' }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Jenis Sample:</span>
                                <span class="info-value">
                                    @if ($pengambilan_sample->jenis_sample)
                                        @php
                                            $jenis_samples = is_string($pengambilan_sample->jenis_sample) ? json_decode($pengambilan_sample->jenis_sample, true) : $pengambilan_sample->jenis_sample;
                                            if (is_array($jenis_samples)) {
                                                $jenis_list = array_filter($jenis_samples);
                                                if (count($jenis_list) > 0) {
                                                    echo '<span class="badge badge-info" style="margin-right: 5px;">' . implode('</span><span class="badge badge-info" style="margin-right: 5px;">', $jenis_list) . '</span>';
                                                } else {
                                                    echo '-';
                                                }
                                            } else {
                                                echo $pengambilan_sample->jenis_sample ?? '-';
                                            }
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tindakan Medis Khusus:</span>
                                <span class="info-value">{{ $pengambilan_sample->tindakan_medis_khusus ?? '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Kondisi Pasien:</span>
                                <span class="info-value">{{ $pengambilan_sample->kondisi_pasien ?? '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Petugas Pengambil:</span>
                                <span class="info-value">{{ $pengambilan_sample->petugas_name ?? '-' }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Informasi Penerimaan Sampel -->
                    @if ($penerimaan_verification)
                        <div class="card" style="margin-bottom: 15px;">
                            <div class="card-title">
                                <span>✅</span>
                                <span>Informasi Penerimaan Sampel</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanggal & Waktu Penerimaan:</span>
                                <span class="info-value">
                                    @if ($penerimaan_verification->start_date)
                                        {{ \Carbon\Carbon::parse($penerimaan_verification->start_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Penerimaan Sampel:</span>
                                <span class="info-value">
                                    @php
                                        $penerimaan_value = $permohonan->penerimaan_sampel ?? null;
                                        if ($penerimaan_value) {
                                            // Decode JSON if string
                                            if (is_string($penerimaan_value)) {
                                                $decoded = json_decode($penerimaan_value, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $penerimaan_data = $decoded;
                                                } else {
                                                    $penerimaan_data = [];
                                                }
                                            } elseif (is_array($penerimaan_value)) {
                                                $penerimaan_data = $penerimaan_value;
                                            } else {
                                                $penerimaan_data = [];
                                            }
                                            
                                            // Format as readable list instead of JSON
                                            if (!empty($penerimaan_data)) {
                                                $items = [];
                                                foreach ($penerimaan_data as $key => $value) {
                                                    $display_value = ($value && $value != '-') ? $value : '-';
                                                    $items[] = '<strong>' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($display_value, ENT_QUOTES, 'UTF-8');
                                                }
                                                echo implode('<br>', $items);
                                            } else {
                                                echo '-';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Volume Sampel:</span>
                                <span class="info-value">
                                    @php
                                        $volume_value = $permohonan->volume_sampel ?? null;
                                        if ($volume_value) {
                                            // Decode JSON if string
                                            if (is_string($volume_value)) {
                                                $decoded = json_decode($volume_value, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $volume_data = $decoded;
                                                } else {
                                                    $volume_data = [];
                                                }
                                            } elseif (is_array($volume_value)) {
                                                $volume_data = $volume_value;
                                            } else {
                                                $volume_data = [];
                                            }
                                            
                                            // Format as readable list instead of JSON
                                            if (!empty($volume_data)) {
                                                $items = [];
                                                foreach ($volume_data as $key => $value) {
                                                    $display_value = ($value && $value != '-') ? $value : '-';
                                                    $items[] = '<strong>' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($display_value, ENT_QUOTES, 'UTF-8');
                                                }
                                                echo implode('<br>', $items);
                                            } else {
                                                echo '-';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Kualitas Sampel:</span>
                                <span class="info-value">
                                    @php
                                        $kualitas_value = $permohonan->kualitas_sampel ?? null;

                                        if (!$kualitas_value) {
                                            echo '-';
                                        } else {
                                            // 1. Jika string, coba decode JSON
                                            if (is_string($kualitas_value)) {
                                                $decoded = json_decode($kualitas_value, true);
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    $kualitas = $decoded;
                                                } else {
                                                    $kualitas = [$kualitas_value];
                                                }
                                            }
                                            // 2. Jika array langsung pakai
                                            elseif (is_array($kualitas_value)) {
                                                $kualitas = $kualitas_value;
                                            }
                                            // 3. Jika tipe lain → jadikan array
                                            else {
                                                $kualitas = [(string) $kualitas_value];
                                            }

                                            // --- FLATTEN & FILTER: ambil hanya nilai scalar (string/number) ---
                                            $flat = [];
                                            if (is_array($kualitas)) {
                                                array_walk_recursive($kualitas, function ($v) use (&$flat) {
                                                    if (is_scalar($v) && $v !== '' && $v !== '-') {
                                                        $flat[] = $v;
                                                    }
                                                });
                                            }

                                            if (count($flat) > 0) {
                                                foreach ($flat as $text) {
                                                    echo '<span class="badge badge-warning" style="margin-right: 5px;">'
                                                        . htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8') .
                                                        '</span>';
                                                }
                                            } else {
                                                echo '-';
                                            }
                                        }
                                    @endphp
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Petugas Penerima:</span>
                                <span class="info-value">
                                    @php
                                        $nama_petugas_value = null;
                                        if (isset($penerimaan_verification) && $penerimaan_verification) {
                                            $nama_petugas_value = $penerimaan_verification->nama_petugas ?? null;
                                        }
                                        
                                        if ($nama_petugas_value) {
                                            // Check if it's a JSON string
                                            if (is_string($nama_petugas_value)) {
                                                $first_char = substr(trim($nama_petugas_value), 0, 1);
                                                if ($first_char === '[' || $first_char === '{') {
                                                    $decoded = json_decode($nama_petugas_value, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $nama_petugas_value = $decoded;
                                                    }
                                                }
                                            }
                                            
                                            // Display based on type
                                            if (is_array($nama_petugas_value)) {
                                                $filtered = array_filter($nama_petugas_value);
                                                echo !empty($filtered) ? implode(', ', $filtered) : '-';
                                            } else {
                                                echo (string) $nama_petugas_value;
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Informasi Pengolah Sampel -->
                    @if ($pengolah_verification)
                        <div class="card" style="margin-bottom: 15px;">
                            <div class="card-title">
                                <span>🔬</span>
                                <span>Informasi Pengolah Sampel</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Waktu Pengolahan:</span>
                                <span class="info-value">
                                    @if ($pengolah_verification->start_date)
                                        {{ \Carbon\Carbon::parse($pengolah_verification->start_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Petugas Pengolah:</span>
                                <span class="info-value">
                                    @php
                                        $nama_petugas_value = null;
                                        if (isset($pengolah_verification) && $pengolah_verification) {
                                            $nama_petugas_value = $pengolah_verification->nama_petugas ?? null;
                                        }
                                        
                                        if ($nama_petugas_value) {
                                            // Check if it's a JSON string
                                            if (is_string($nama_petugas_value)) {
                                                $first_char = substr(trim($nama_petugas_value), 0, 1);
                                                if ($first_char === '[' || $first_char === '{') {
                                                    $decoded = json_decode($nama_petugas_value, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $nama_petugas_value = $decoded;
                                                    }
                                                }
                                            }
                                            
                                            // Display based on type
                                            if (is_array($nama_petugas_value)) {
                                                $filtered = array_filter($nama_petugas_value);
                                                echo !empty($filtered) ? implode(', ', $filtered) : '-';
                                            } else {
                                                echo (string) $nama_petugas_value;
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Informasi Pemeriksa Sampel -->
                    @if ($verification)
                        <div class="card">
                            <div class="card-title">
                                <span>📊</span>
                                <span>Informasi Pemeriksa Sampel</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Waktu Pemeriksaan:</span>
                                <span class="info-value">
                                    @if ($verification->start_date)
                                        {{ \Carbon\Carbon::parse($verification->start_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Petugas Pemeriksa:</span>
                                <span class="info-value">
                                    @php
                                        $nama_petugas_value = null;
                                        if (isset($verification) && $verification) {
                                            $nama_petugas_value = $verification->nama_petugas ?? null;
                                        }
                                        
                                        if ($nama_petugas_value) {
                                            // Check if it's a JSON string
                                            if (is_string($nama_petugas_value)) {
                                                $first_char = substr(trim($nama_petugas_value), 0, 1);
                                                if ($first_char === '[' || $first_char === '{') {
                                                    $decoded = json_decode($nama_petugas_value, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $nama_petugas_value = $decoded;
                                                    }
                                                }
                                            }
                                            
                                            // Display based on type
                                            if (is_array($nama_petugas_value)) {
                                                $filtered = array_filter($nama_petugas_value);
                                                echo !empty($filtered) ? implode(', ', $filtered) : '-';
                                            } else {
                                                echo (string) $nama_petugas_value;
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Mobile Inline Editing Script -->
    <script>
        (function() {
            function initNavigation() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initNavigation, 100);
                    return;
                }

                jQuery(document).ready(function($) {
                    // Function to get all parameter cards
                    function getAllParameterCards() {
                        return $('.parameter-card').toArray();
                    }

                    // Function to navigate to parameter
                    function navigateToParameter(direction) {
                        var $currentButton = $(this);
                        var currentIndex = parseInt($currentButton.data('parameter-index')) || 0;
                        var allCards = getAllParameterCards();
                        
                        console.log('Navigating:', direction, 'from index:', currentIndex);
                        
                        var targetIndex;
                        if (direction === 'up') {
                            targetIndex = currentIndex - 1;
                        } else {
                            targetIndex = currentIndex + 1;
                        }
                        
                        // Check if target index is valid
                        if (targetIndex < 0 || targetIndex >= allCards.length) {
                            console.log('Invalid target index:', targetIndex);
                            return;
                        }
                        
                        // Find target card
                        var $targetCard = $(allCards[targetIndex]);
                        if ($targetCard.length === 0) {
                            console.log('Target card not found');
                            return;
                        }
                        
                        // Find inline hasil input in target card (created by mobile-inline-editing.js)
                        // Try inline input first (dropdown or editor), then fallback to button if still exists
                        var $targetInput = $targetCard.find('.inline-hasil-input, .inline-hasil-editor').first();
                        if ($targetInput.length === 0) {
                            // Fallback: try to find button (in case inline editing hasn't initialized yet)
                            $targetInput = $targetCard.find('.open-dropdown-modal-verifikasi, .open-editor-modal-verifikasi').first();
                        }
                        if ($targetInput.length === 0) {
                            console.log('Target input not found in card:', $targetCard);
                            return;
                        }
                        
                        console.log('Target input found, scrolling...', $targetInput);
                        
                        // Highlight target card briefly
                        $targetCard.css({
                            'box-shadow': '0 0 0 3px rgba(11, 58, 92, 0.5)',
                            'transition': 'box-shadow 0.3s',
                            'background-color': 'rgba(11, 58, 92, 0.05)'
                        });
                        setTimeout(function() {
                            $targetCard.css({
                                'box-shadow': '',
                                'transition': '',
                                'background-color': ''
                            });
                        }, 1500);
                        
                        // Use scrollIntoView for reliable mobile scrolling
                        var targetElement = $targetInput.length > 0 ? $targetInput[0] : $targetCard[0];
                        var scrollOffset = 100;
                        
                        // Calculate position
                        var elementTop = targetElement.getBoundingClientRect().top;
                        var elementPosition = elementTop + window.pageYOffset;
                        var offsetPosition = elementPosition - scrollOffset;
                        
                        // Scroll using native method for better mobile support
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        
                        // After scroll, focus on input
                        setTimeout(function() {
                            if ($targetInput.length > 0) {
                                // Focus on target input
                                $targetInput[0].focus();
                                
                                // If it's a select dropdown, open it
                                if ($targetInput.is('select')) {
                                    setTimeout(function() {
                                        $targetInput[0].click();
                                    }, 200);
                                }
                                
                                // If it's a contenteditable div, set cursor at end
                                if ($targetInput.is('[contenteditable="true"]')) {
                                    var range = document.createRange();
                                    var sel = window.getSelection();
                                    range.selectNodeContents($targetInput[0]);
                                    range.collapse(false);
                                    sel.removeAllRanges();
                                    sel.addRange(range);
                                }
                                
                                // Ensure input is in view
                                var inputRect = $targetInput[0].getBoundingClientRect();
                                var inputTop = inputRect.top + window.pageYOffset;
                                var finalInputScroll = inputTop - scrollOffset;
                                
                                window.scrollTo({
                                    top: finalInputScroll,
                                    behavior: 'smooth'
                                });
                            }
                        }, 400);
                    }

                    // Handle arrow up button - use multiple event types for better mobile support
                    $(document).on('click touchstart', '.nav-arrow-up', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Arrow up clicked');
                        navigateToParameter.call(this, 'up');
                    });

                    // Handle arrow down button - use multiple event types for better mobile support
                    $(document).on('click touchstart', '.nav-arrow-down', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Arrow down clicked');
                        navigateToParameter.call(this, 'down');
                    });
                    
                    // Also bind directly to buttons for immediate response
                    setTimeout(function() {
                        $('.nav-arrow-up, .nav-arrow-down').each(function() {
                            var $btn = $(this);
                            if (!$btn.data('handler-attached')) {
                                $btn.on('click touchstart', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    var direction = $btn.hasClass('nav-arrow-up') ? 'up' : 'down';
                                    console.log('Direct handler - Arrow', direction, 'clicked');
                                    navigateToParameter.call(this, direction);
                                });
                                $btn.data('handler-attached', true);
                            }
                        });
                    }, 500);

                    // Update arrow button states on page load
                    function updateArrowButtonStates() {
                        var allCards = getAllParameterCards();
                        var totalCards = allCards.length;
                        
                        $('.nav-arrow-up, .nav-arrow-down').each(function() {
                            var $btn = $(this);
                            var index = parseInt($btn.data('parameter-index')) || 0;
                            
                            if ($btn.hasClass('nav-arrow-up')) {
                                // Disable if first parameter
                                $btn.prop('disabled', index === 0);
                            } else if ($btn.hasClass('nav-arrow-down')) {
                                // Disable if last parameter
                                $btn.prop('disabled', index >= totalCards - 1);
                            }
                        });
                    }

                    // Update button states after page load
                    setTimeout(updateArrowButtonStates, 500);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initNavigation);
            } else {
                initNavigation();
            }
        })();
    </script>
    
    <!-- History Comparison Modal Script -->
    <script>
        (function() {
            function initHistoryComparison() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initHistoryComparison, 100);
                    return;
                }

                jQuery(document).ready(function($) {
                    // Function to update history modal with baku mutu check
                    function updateHistoryModal(buttonElement) {
                        var $btn = $(buttonElement);
                        var hasilAnalis = $btn.data('hasil-analis') || '';
                        var hasilKoreksiId = $btn.data('hasil-koreksi-id');
                        var parameterId = hasilKoreksiId.replace('hasil_koreksi_', '');
                        var min = $btn.data('min') || '';
                        var max = $btn.data('max') || '';
                        var equal = $btn.data('equal') || '';
                        var numberFormat = $btn.data('number-format') || 'en';
                        var offsetBakuMutu = $btn.data('offset-baku-mutu') || 'default';
                        
                        // Get multiple baku mutu data
                        var selectedBakuMutuData = $btn.data('selected-baku-mutu');
                        var selectedBakuMutu = selectedBakuMutuData ? (typeof selectedBakuMutuData === 'string' ? JSON.parse(selectedBakuMutuData) : selectedBakuMutuData) : [];
                        
                        // Get current koreksi value from textarea or inline editor
                        // PRIORITAS: Ambil dari inline editor terlebih dahulu (karena itu yang sedang ditampilkan)
                        var hasilKoreksi = '';
                        var hasilKoreksiPlain = ''; // Plain text untuk validasi
                        var $textarea = $('#' + hasilKoreksiId);
                        
                        // Cari inline editor terlebih dahulu (ini yang sedang aktif)
                        var $inlineEditor = $textarea.closest('.input-group-mobile, .card').find('.inline-hasil-editor, .inline-hasil-input').first();
                        
                        if ($inlineEditor.length > 0) {
                            if ($inlineEditor.is('select')) {
                                // Dropdown
                                hasilKoreksi = $inlineEditor.val() || '';
                                hasilKoreksiPlain = hasilKoreksi;
                                // Update textarea juga untuk konsistensi
                                if ($textarea.length > 0) {
                                    $textarea.val(hasilKoreksi);
                                }
                            } else {
                                // TinyMCE atau contenteditable
                                var editorId = $inlineEditor.attr('id');
                                if (editorId && typeof tinymce !== 'undefined') {
                                    // Coba ambil dari TinyMCE dengan retry
                                    var editor = tinymce.get(editorId);
                                    if (editor && editor.initialized) {
                                        // Ambil dari TinyMCE (value terbaru)
                                        hasilKoreksi = editor.getContent() || ''; // HTML
                                        hasilKoreksiPlain = editor.getContent({format: 'text'}) || ''; // Plain text
                                        // Update textarea juga untuk konsistensi
                                        if ($textarea.length > 0) {
                                            $textarea.val(hasilKoreksi);
                                        }
                                    } else {
                                        // TinyMCE belum ready, coba ambil dari contenteditable atau textarea
                                        var contentFromEditor = $inlineEditor.html() || $inlineEditor.text() || '';
                                        if (contentFromEditor && contentFromEditor.trim() !== '') {
                                            hasilKoreksi = contentFromEditor;
                                            hasilKoreksiPlain = $inlineEditor.text() || '';
                                        } else {
                                            // Fallback ke textarea
                                            hasilKoreksi = $textarea.val() || '';
                                            hasilKoreksiPlain = $('<div>').html(hasilKoreksi).text().trim();
                                        }
                                        // Update textarea juga untuk konsistensi
                                        if ($textarea.length > 0 && hasilKoreksi) {
                                            $textarea.val(hasilKoreksi);
                                        }
                                    }
                                } else {
                                    // Contenteditable biasa atau belum ada TinyMCE
                                    var contentFromEditor = $inlineEditor.html() || $inlineEditor.text() || '';
                                    if (contentFromEditor && contentFromEditor.trim() !== '') {
                                        hasilKoreksi = contentFromEditor;
                                        hasilKoreksiPlain = $inlineEditor.text() || '';
                                    } else {
                                        // Fallback ke textarea
                                        hasilKoreksi = $textarea.val() || '';
                                        hasilKoreksiPlain = $('<div>').html(hasilKoreksi).text().trim();
                                    }
                                    // Update textarea juga untuk konsistensi
                                    if ($textarea.length > 0 && hasilKoreksi) {
                                        $textarea.val(hasilKoreksi);
                                    }
                                }
                            }
                        } else {
                            // Tidak ada inline editor, ambil dari textarea
                            if ($textarea.length > 0) {
                                hasilKoreksi = $textarea.val() || '';
                                // Extract plain text from HTML
                                hasilKoreksiPlain = $('<div>').html(hasilKoreksi).text().trim();
                            }
                        }
                        
                        // Clean HTML untuk perbandingan
                        // Convert hasilAnalis to string first to avoid errors
                        var hasilAnalisStr = hasilAnalis ? String(hasilAnalis) : '';
                        var hasilAnalisClean = $('<div>').html(hasilAnalisStr).text().trim();
                        var hasilKoreksiClean = hasilKoreksiPlain.trim();
                        
                        // Extract plain text dari hasil analis untuk validasi baku mutu
                        var hasilAnalisPlain = hasilAnalisClean;
                        // Jika hasilAnalis masih mengandung HTML, extract text
                        // hasilAnalisStr sudah didefinisikan di atas
                        if (hasilAnalisStr && hasilAnalisStr.indexOf('<') !== -1) {
                            var $tempAnalis = $('<div>').html(hasilAnalisStr);
                            hasilAnalisPlain = $tempAnalis.text().trim() || $tempAnalis.html().trim();
                        }
                        
                        // Debug: Log values untuk memastikan value diambil dengan benar (dapat dihapus setelah testing)
                        // console.log('History Modal - Parameter ID:', parameterId);
                        // console.log('History Modal - Hasil Analis (Plain):', hasilAnalisPlain);
                        // console.log('History Modal - Hasil Koreksi (Plain):', hasilKoreksiPlain);
                        // console.log('History Modal - Hasil Koreksi (HTML):', hasilKoreksi);
                        // console.log('History Modal - Selected Baku Mutu:', selectedBakuMutu);
                        
                        // Update hasil koreksi display with baku mutu check (menggunakan logika yang sama seperti updateResultPreview)
                        var $koreksiDisplay = $('#history_hasil_koreksi_' + parameterId);
                        // Selalu cek baku mutu jika ada hasil koreksi
                        if (hasilKoreksiClean && hasilKoreksiClean !== '' && hasilKoreksiClean !== '-') {
                            // Gunakan logika yang sama seperti updateResultPreview untuk konsistensi
                            var plainValue = hasilKoreksiPlain;
                            var delete_space = plainValue ? String(plainValue).replace(/\s/g, '') : '';
                            
                            // Selalu cek baku mutu jika ada value (tidak hanya jika delete_space tidak kosong)
                            if (delete_space && delete_space !== "" && delete_space !== "-") {
                                var melewati_baku_mutu = false;
                                var matchedBakuMutu = null;
                                var checkMin = min;
                                var checkMax = max;
                                var checkEqual = equal;
                                var kesimpulan = '';
                                
                                // Prioritas: Cek terhadap selected baku mutu terlebih dahulu (jika ada)
                                if (selectedBakuMutu && selectedBakuMutu.length > 0) {
                                    // Gunakan fungsi dari mobile-inline-editing.js jika tersedia
                                    if (typeof window.MobileInlineEditor !== 'undefined' && typeof window.MobileInlineEditor.checkAgainstSelectedBakuMutu === 'function') {
                                        var checkResult = window.MobileInlineEditor.checkAgainstSelectedBakuMutu(plainValue, selectedBakuMutu, numberFormat);
                                        melewati_baku_mutu = checkResult.melewati;
                                        matchedBakuMutu = checkResult.matched;
                                        
                                        if (matchedBakuMutu) {
                                            checkMin = matchedBakuMutu.min || min;
                                            checkMax = matchedBakuMutu.max || max;
                                            checkEqual = matchedBakuMutu.equal || equal;
                                            kesimpulan = matchedBakuMutu.kesimpulan_baku_mutu || '';
                                        }
                                    } else {
                                        // Fallback: gunakan single baku mutu
                                        var hasil_clean = plainValue.toString().replace(/&nbsp;/g, ' ').trim();
                                        var hasil_numeric = parseFloat(hasil_clean.replace(/[^\d.-]/g, '').replace(',', '.'));
                                        
                                        if (equal && equal.trim() !== '') {
                                            var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                            var hasil_compare = hasil_clean.replace(/\s/g, '');
                                            melewati_baku_mutu = (hasil_compare !== equal_clean);
                                        } else if (!isNaN(hasil_numeric)) {
                                            if (min && max) {
                                                var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                                var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                                melewati_baku_mutu = (hasil_numeric < minNum || hasil_numeric > maxNum);
                                            } else if (min) {
                                                var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                                melewati_baku_mutu = (hasil_numeric < minNum);
                                            } else if (max) {
                                                var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                                melewati_baku_mutu = (hasil_numeric > maxNum);
                                            }
                                        }
                                    }
                                } else {
                                    // Fallback ke single baku mutu (min, max, equal)
                                    var hasil_clean = plainValue.toString().replace(/&nbsp;/g, ' ').trim();
                                    var hasil_numeric = parseFloat(hasil_clean.replace(/[^\d.-]/g, '').replace(',', '.'));
                                    
                                    if (equal && equal.trim() !== '') {
                                        var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                        var hasil_compare = hasil_clean.replace(/\s/g, '');
                                        melewati_baku_mutu = (hasil_compare !== equal_clean);
                                    } else if (!isNaN(hasil_numeric)) {
                                        if (min && max) {
                                            var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu = (hasil_numeric < minNum || hasil_numeric > maxNum);
                                        } else if (min) {
                                            var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu = (hasil_numeric < minNum);
                                        } else if (max) {
                                            var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu = (hasil_numeric > maxNum);
                                        }
                                    }
                                }
                                
                                // Gunakan checkBakuMutu untuk mendapatkan badge dengan format yang benar
                                var bakuMutuOutput = null;
                                if (typeof window.checkBakuMutu === 'function') {
                                    bakuMutuOutput = window.checkBakuMutu(plainValue, checkMin, checkMax, checkEqual, offsetBakuMutu, null, '', numberFormat);
                                }
                                
                                if (bakuMutuOutput) {
                                    var output = bakuMutuOutput;
                                    // Tambahkan kesimpulan jika ada
                                    if (kesimpulan && kesimpulan.trim() !== '') {
                                        output = bakuMutuOutput + '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + kesimpulan + '</small>';
                                    }
                                    $koreksiDisplay.html(output);
                                } else {
                                    // Fallback: create badge manually jika checkBakuMutu tidak tersedia atau tidak mengembalikan output
                                    var badgeClass = melewati_baku_mutu ? 'badge-danger' : 'badge-success';
                                    var icon = melewati_baku_mutu ? 'fa-times-circle' : 'fa-check-circle';
                                    var badgeHtml = '<span class="badge ' + badgeClass + '" style="font-size: 14px; padding: 8px 12px;">' +
                                        '<i class="fa ' + icon + '"></i> ' + hasilKoreksi +
                                        (melewati_baku_mutu ? ' <i class="fa fa-exclamation-triangle"></i>' : '') +
                                        '</span>';
                                    if (kesimpulan && kesimpulan.trim() !== '') {
                                        badgeHtml += '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + kesimpulan + '</small>';
                                    }
                                    $koreksiDisplay.html(badgeHtml);
                                }
                            } else {
                                $koreksiDisplay.html('<span class="text-muted">Belum ada koreksi</span>');
                            }
                        } else {
                            $koreksiDisplay.html('<span class="text-muted">Belum ada koreksi</span>');
                        }
                        
                        // Update hasil analis display with baku mutu check (juga dicek dengan baku mutu)
                        var $analisDisplay = $('#history_hasil_analis_' + parameterId);
                        // Gunakan hasilAnalisPlain yang sudah didefinisikan sebelumnya
                        if (typeof hasilAnalisPlain === 'undefined') {
                            hasilAnalisPlain = hasilAnalisClean;
                        }
                        var delete_space_analis = hasilAnalisPlain ? String(hasilAnalisPlain).replace(/\s/g, '') : '';
                        
                        // Selalu cek baku mutu untuk hasil analis jika ada value
                        if (delete_space_analis && delete_space_analis !== "" && delete_space_analis !== "-") {
                            // Gunakan logika yang sama untuk hasil analis
                            var melewati_baku_mutu_analis = false;
                            var matchedBakuMutuAnalis = null;
                            var checkMinAnalis = min;
                            var checkMaxAnalis = max;
                            var checkEqualAnalis = equal;
                            var kesimpulanAnalis = '';
                            
                            // Prioritas: Cek terhadap selected baku mutu terlebih dahulu (jika ada)
                            if (selectedBakuMutu && selectedBakuMutu.length > 0) {
                                // Gunakan fungsi dari mobile-inline-editing.js jika tersedia
                                if (typeof window.MobileInlineEditor !== 'undefined' && typeof window.MobileInlineEditor.checkAgainstSelectedBakuMutu === 'function') {
                                    var checkResultAnalis = window.MobileInlineEditor.checkAgainstSelectedBakuMutu(hasilAnalisPlain, selectedBakuMutu, numberFormat);
                                    melewati_baku_mutu_analis = checkResultAnalis.melewati;
                                    matchedBakuMutuAnalis = checkResultAnalis.matched;
                                    
                                    if (matchedBakuMutuAnalis) {
                                        checkMinAnalis = matchedBakuMutuAnalis.min || min;
                                        checkMaxAnalis = matchedBakuMutuAnalis.max || max;
                                        checkEqualAnalis = matchedBakuMutuAnalis.equal || equal;
                                        kesimpulanAnalis = matchedBakuMutuAnalis.kesimpulan_baku_mutu || '';
                                    }
                                } else {
                                    // Fallback: gunakan single baku mutu
                                    var hasil_clean_analis = hasilAnalisPlain.toString().replace(/&nbsp;/g, ' ').trim();
                                    var hasil_numeric_analis = parseFloat(hasil_clean_analis.replace(/[^\d.-]/g, '').replace(',', '.'));
                                    
                                    if (equal && equal.trim() !== '') {
                                        var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                        var hasil_compare_analis = hasil_clean_analis.replace(/\s/g, '');
                                        melewati_baku_mutu_analis = (hasil_compare_analis !== equal_clean);
                                    } else if (!isNaN(hasil_numeric_analis)) {
                                        if (min && max) {
                                            var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu_analis = (hasil_numeric_analis < minNum || hasil_numeric_analis > maxNum);
                                        } else if (min) {
                                            var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu_analis = (hasil_numeric_analis < minNum);
                                        } else if (max) {
                                            var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                            melewati_baku_mutu_analis = (hasil_numeric_analis > maxNum);
                                        }
                                    }
                                }
                            } else {
                                // Fallback ke single baku mutu (min, max, equal)
                                var hasil_clean_analis = hasilAnalisPlain.toString().replace(/&nbsp;/g, ' ').trim();
                                var hasil_numeric_analis = parseFloat(hasil_clean_analis.replace(/[^\d.-]/g, '').replace(',', '.'));
                                
                                if (equal && equal.trim() !== '') {
                                    var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                    var hasil_compare_analis = hasil_clean_analis.replace(/\s/g, '');
                                    melewati_baku_mutu_analis = (hasil_compare_analis !== equal_clean);
                                } else if (!isNaN(hasil_numeric_analis)) {
                                    if (min && max) {
                                        var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                        var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                        melewati_baku_mutu_analis = (hasil_numeric_analis < minNum || hasil_numeric_analis > maxNum);
                                    } else if (min) {
                                        var minNum = parseFloat(min.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                        melewati_baku_mutu_analis = (hasil_numeric_analis < minNum);
                                    } else if (max) {
                                        var maxNum = parseFloat(max.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
                                        melewati_baku_mutu_analis = (hasil_numeric_analis > maxNum);
                                    }
                                }
                            }
                            
                            // Gunakan checkBakuMutu untuk mendapatkan badge dengan format yang benar
                            var bakuMutuOutputAnalis = null;
                            if (typeof window.checkBakuMutu === 'function') {
                                bakuMutuOutputAnalis = window.checkBakuMutu(hasilAnalisPlain, checkMinAnalis, checkMaxAnalis, checkEqualAnalis, offsetBakuMutu, null, '', numberFormat);
                            }
                            
                            if (bakuMutuOutputAnalis) {
                                var outputAnalis = bakuMutuOutputAnalis;
                                // Tambahkan kesimpulan jika ada
                                if (kesimpulanAnalis && kesimpulanAnalis.trim() !== '') {
                                    outputAnalis = bakuMutuOutputAnalis + '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + kesimpulanAnalis + '</small>';
                                }
                                $analisDisplay.html(outputAnalis);
                            } else {
                                // Fallback: create badge manually jika checkBakuMutu tidak tersedia atau tidak mengembalikan output
                                var badgeClassAnalis = melewati_baku_mutu_analis ? 'badge-danger' : 'badge-success';
                                var iconAnalis = melewati_baku_mutu_analis ? 'fa-times-circle' : 'fa-check-circle';
                                var badgeHtmlAnalis = '<span class="badge ' + badgeClassAnalis + '" style="font-size: 14px; padding: 8px 12px;">' +
                                    '<i class="fa ' + iconAnalis + '"></i> ' + hasilAnalisPlain +
                                    (melewati_baku_mutu_analis ? ' <i class="fa fa-exclamation-triangle"></i>' : '') +
                                    '</span>';
                                if (kesimpulanAnalis && kesimpulanAnalis.trim() !== '') {
                                    badgeHtmlAnalis += '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + kesimpulanAnalis + '</small>';
                                }
                                $analisDisplay.html(badgeHtmlAnalis);
                            }
                        }
                        
                        // Update perbandingan
                        var $comparisonBox = $('#history_comparison_' + parameterId);
                        if (hasilKoreksiClean && hasilKoreksiClean !== '') {
                            if (hasilKoreksiClean !== hasilAnalisClean) {
                                // Berbeda
                                $comparisonBox.html(
                                    '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">' +
                                    '<i class="fa fa-exclamation-triangle" style="color: #dc3545; font-size: 20px;"></i>' +
                                    '<span style="font-weight: 600; color: #dc3545;">Hasil Berbeda</span>' +
                                    '</div>' +
                                    '<div style="padding: 10px; background: white; border-radius: 6px; margin-top: 10px;">' +
                                    '<div style="margin-bottom: 8px;"><strong>Hasil Analis:</strong> <span style="color: #0b3a5c;">' + 
                                    $('<div>').text(hasilAnalis).html() + '</span></div>' +
                                    '<div><strong>Hasil Koreksi:</strong> <span style="color: #28a745;">' + 
                                    $('<div>').text(hasilKoreksi).html() + '</span></div>' +
                                    '</div>'
                                );
                            } else {
                                // Sama
                                $comparisonBox.html(
                                    '<div style="display: flex; align-items: center; gap: 10px;">' +
                                    '<i class="fa fa-check-circle" style="color: #28a745; font-size: 20px;"></i>' +
                                    '<span style="font-weight: 600; color: #28a745;">Hasil Sama dengan Analis</span>' +
                                    '</div>'
                                );
                            }
                        } else {
                            $comparisonBox.html(
                                '<div style="font-size: 14px; color: #856404;">' +
                                '<i class="fa fa-info-circle"></i> Belum ada koreksi' +
                                '</div>'
                            );
                        }
                    }
                    
                    // Function to find and update modal
                    function updateModalForButton($btn) {
                        if ($btn.length === 0) return;
                        var target = $btn.data('target') || $btn.attr('data-target');
                        if (target) {
                            // Extract parameter ID from target
                            var modalId = target.replace('#', '');
                            var parameterId = modalId.replace('historyComparisonModal_', '');
                            
                            // Update the modal content
                            updateHistoryModal($btn[0]);
                        }
                    }
                    
                    // Call updateHistoryModal when button is clicked
                    $(document).on('click', '.btn-history-comparison', function(e) {
                        var $btn = $(this);
                        // Prevent default if needed, but let Bootstrap handle modal
                        // Call immediately and also after delays to ensure DOM is ready
                        updateHistoryModal($btn[0]);
                        setTimeout(function() {
                            updateHistoryModal($btn[0]);
                        }, 200);
                        setTimeout(function() {
                            updateHistoryModal($btn[0]);
                        }, 500);
                        setTimeout(function() {
                            updateHistoryModal($btn[0]);
                        }, 800);
                    });
                    
                    // Also call updateHistoryModal when modal is shown (to ensure it's updated)
                    $(document).on('shown.bs.modal', '[id^="historyComparisonModal_"]', function() {
                        var modalId = $(this).attr('id');
                        var $btn = $('.btn-history-comparison[data-target="#' + modalId + '"]');
                        if ($btn.length > 0) {
                            updateHistoryModal($btn[0]);
                        }
                    });
                    
                    // Also call when modal is about to be shown (before animation)
                    $(document).on('show.bs.modal', '[id^="historyComparisonModal_"]', function() {
                        var modalId = $(this).attr('id');
                        var $btn = $('.btn-history-comparison[data-target="#' + modalId + '"]');
                        if ($btn.length > 0) {
                            // Call immediately to update before modal is shown
                            updateHistoryModal($btn[0]);
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initHistoryComparison);
            } else {
                initHistoryComparison();
            }
        })();
    </script>
    
    <!-- TinyMCE CDN - Required for inline editing with superscript/subscript/symbols -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.7/tinymce.min.js"></script>
    
    <!-- Mobile Inline Editing Script -->
    <script src="{{ asset('assets/js/mobile-inline-editing.js') }}"></script>
</body>

</html>

