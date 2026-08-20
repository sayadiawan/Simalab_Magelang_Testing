<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pemeriksa Sampel - Klinik</title>
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
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
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
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
        }

        .parameter-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #2D6BCF;
        }

        .parameter-card.rejected {
            border-left: 4px solid #dc3545;
            background: #fff5f5;
        }

        .parameter-card.corrected {
            border-left: 4px solid #ffc107;
            background: #fffbf0;
        }

        .verification-comment-box {
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            border-radius: 6px;
            font-size: 13px;
        }

        .verification-comment-box.rejected {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .verification-status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .verification-status-badge.rejected {
            background: #dc3545;
            color: white;
        }

        .verification-status-badge.corrected {
            background: #ffc107;
            color: #212529;
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
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
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
            color: #2D6BCF;
            font-weight: 600;
        }

        .step-item.completed {
            color: #28a745;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
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
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
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
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
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
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
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
            color: #383d41;
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
            border: 2px solid #2D6BCF;
            border-radius: 8px;
            background: white;
            color: #2D6BCF;
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
            background: #2D6BCF;
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

        .result-preview {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
        }

        .hidden-field {
            display: none;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔬 PEMERIKSA SAMPEL</h1>
            <p>Step 3 dari 4</p>
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
            <div class="step-item active">3. Pemeriksa</div>
            <div class="step-item">4. Verifikasi</div>
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

        <form id="formPemeriksa" method="POST" action="{{ route('mobile.testing.klinik.storePemeriksa', $id) }}">
            @csrf
            <div class="card">
                <div class="card-title">
                    <span>⏰</span>
                    <span>Waktu Pemeriksaan</span>
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
                    <span>Hasil Pemeriksaan Parameter</span>
                </div>
                @if ($parameters && count($parameters) > 0)
                    @foreach ($parameters as $index => $parameter)
                        @php
                            $status_verifikasi = $parameter->status_verifikasi ?? null;
                            $komentar_verifikasi = $parameter->komentar_verifikasi ?? null;
                            $is_rejected = ($status_verifikasi == 'rejected');
                            $is_corrected = ($status_verifikasi == 'corrected');
                            $card_class = '';
                            if ($is_rejected) {
                                $card_class = 'rejected';
                            } elseif ($is_corrected) {
                                $card_class = 'corrected';
                            }
                        @endphp
                        <div class="parameter-card {{ $card_class }}">
                            <div class="parameter-header">
                                <div class="parameter-name">
                                    {{ $index + 1 }}. {{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}
                                    @if ($is_rejected)
                                        <span class="verification-status-badge rejected">
                                            <i class="fa fa-times-circle"></i> Ditolak
                                        </span>
                                    @elseif ($is_corrected)
                                        <span class="verification-status-badge corrected">
                                            <i class="fa fa-exclamation-triangle"></i> Perlu Diperbaiki
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" 
                                name="parameters[{{ $index }}][id]" 
                                value="{{ $parameter->id_permohonan_uji_parameter_klinik }}">
                            <input type="hidden" 
                                class="parameter-number-format" 
                                data-param-index="{{ $index }}"
                                value="{{ $parameter->parametersatuanklinik->number_format ?? 'en' }}">

                            @php
                                $baku_mutu_selected = $parameter->baku_mutu_data ?? [];
                                $baku_mutu_multiple = $parameter->baku_mutu_multiple ?? [];
                                $current_result = $parameter->hasil_permohonan_uji_parameter_klinik ?? '';
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
                                <label>Hasil <span style="color: red">*</span></label>
                                
                                <!-- Hidden textarea for form submission -->
                                @php
                                    // Get first selected baku mutu for data attributes (fallback)
                                    $first_baku_mutu = null;
                                    if (count($baku_mutu_selected) > 0) {
                                        $first_bm = $baku_mutu_selected[0];
                                        $first_baku_mutu = is_array($first_bm) ? $first_bm : $first_bm->toArray();
                                    }
                                    
                                    // Convert selected baku mutu to array format for JavaScript
                                    $selected_baku_mutu_array = [];
                                    foreach ($baku_mutu_selected as $bm) {
                                        $selected_baku_mutu_array[] = is_array($bm) ? $bm : $bm->toArray();
                                    }
                                @endphp
                                <textarea class="form-control result_method result_method_{{ $parameter->id_permohonan_uji_parameter_klinik }} hidden-field"
                                    id="result_method_{{ $parameter->id_permohonan_uji_parameter_klinik }}" 
                                    name="parameters[{{ $index }}][hasil]"
                                    data-min="{{ $first_baku_mutu['min'] ?? '' }}" 
                                    data-max="{{ $first_baku_mutu['max'] ?? '' }}"
                                    data-equal="{{ $first_baku_mutu['equal'] ?? '' }}"
                                    data-is-option="{{ $is_option ? '1' : '0' }}"
                                    data-option-values="{{ $is_option ? json_encode($options) : '[]' }}"
                                    data-number-format="{{ $parameter->parametersatuanklinik->number_format ?? 'en' }}"
                                    data-multiple-baku-mutu="{{ json_encode($baku_mutu_multiple) }}"
                                    data-selected-baku-mutu="{{ json_encode($selected_baku_mutu_array) }}"
                                    placeholder="Hasil" 
                                    style="display: none;">{{ $current_result }}</textarea>

                                @if ($is_option == 1 && count($options) > 0)
                                    <!-- Button untuk membuka popup dropdown (is_option = 1) -->
                                    <div class="hasil-button-with-nav" style="margin-bottom: 10px;">
                                        <button type="button" class="btn btn-sm btn-primary open-dropdown-modal"
                                            data-target="result_method_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                            data-method-id="{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                            data-method-name="{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? 'Parameter' }}"
                                            data-is-option="1"
                                            data-options="{{ json_encode($options) }}"
                                            data-current-value="{{ $current_result }}"
                                            data-parameter-index="{{ $index }}">
                                            <i class="fa fa-list mr-1"></i>
                                            {!! $current_result ? $current_result : 'Pilih Hasil' !!}
                                        </button>
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
                                    <!-- Hidden select untuk form submission -->
                                    <select class="form-control result-dropdown hidden-field"
                                        id="result_dropdown_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        name="result_dropdown_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-method-id="{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                        data-is-option="1"
                                        data-parameter-name="{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? 'Parameter' }}"
                                        style="display: none;">
                                        <option value="">Pilih hasil</option>
                                        @foreach ($options as $opt)
                                            <option value="{{ $opt }}"
                                                {{ $current_result == $opt ? 'selected' : '' }}>
                                                {{ $opt }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <!-- TinyMCE Editor button untuk is_option = 0 -->
                                    <div class="hasil-button-with-nav" style="margin-bottom: 10px;">
                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                            data-target="result_method_{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                            data-method-id="{{ $parameter->id_permohonan_uji_parameter_klinik }}"
                                            data-method-name="{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? 'Parameter' }}"
                                            data-is-option="0"
                                            data-parameter-index="{{ $index }}">
                                            <i class="fa fa-edit mr-1"></i>
                                            Edit dengan Editor
                                        </button>
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
                                @endif

                                <!-- Result Preview -->
                                <div class="result-preview"
                                    id="result_output_method_{{ $parameter->id_permohonan_uji_parameter_klinik }}">
                                    {!! cek_hasil_color(
                                        $current_result,
                                        $baku_mutu->min ?? null,
                                        $baku_mutu->max ?? null,
                                        $baku_mutu->equal ?? null,
                                        'result_output_method_' . $parameter->id_permohonan_uji_parameter_klinik,
                                        $parameter->offset_baku_mutu ?? 'default',
                                        $parameter->parametersatuanklinik->number_format ?? 'en'
                                    ) !!}
                                </div>
                            </div>

                            <!-- Komentar Verifikasi jika ditolak atau dikembalikan -->
                            @if (($is_rejected || $is_corrected) && !empty($komentar_verifikasi))
                                <div class="verification-comment-box {{ $is_rejected ? 'rejected' : '' }}" style="margin-top: 15px;">
                                    <strong style="display: block; margin-bottom: 8px;">
                                        <i class="fa fa-comment-dots"></i> 
                                        @if ($is_rejected)
                                            Komentar Penolakan:
                                        @else
                                            Komentar Perbaikan:
                                        @endif
                                    </strong>
                                    <div style="margin-top: 5px; {{ $is_rejected ? 'color: #721c24;' : 'color: #856404;' }} line-height: 1.6;">
                                        {!! nl2br(e($komentar_verifikasi)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-danger">
                        <span>⚠️</span>
                        Tidak ada parameter yang perlu diperiksa.
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
                <span>Simpan & Lanjutkan</span>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize time picker
            flatpickr("#waktu", {
                enableTime: true,
                noCalendar: true,
                allowInput: true,
                dateFormat: "H:i",
                time_24hr: true,
                defaultDate: "{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}"
            });

            // Form submission
            document.getElementById('formPemeriksa').addEventListener('submit', function(e) {
                const waktu = document.getElementById('waktu').value;
                if (!waktu || !/^\d{1,2}:\d{2}$/.test(waktu)) {
                    e.preventDefault();
                    alert('Mohon masukkan waktu yang valid (format: HH:mm)');
                    return false;
                }

                // Sync all dropdowns to textareas
                $('.result-dropdown').each(function() {
                    var methodId = $(this).data('method-id');
                    var selectedValue = $(this).val();
                    var $textarea = $('#result_method_' + methodId);
                    if ($textarea.length) {
                        $textarea.val(selectedValue);
                    }
                });
                
                // Sync all inline editors to textareas (for mobile-inline-editing.js)
                // This ensures all values from inline editors are synced to hidden textareas before validation
                $('.inline-hasil-input, .inline-hasil-editor').each(function() {
                    var $input = $(this);
                    var $textarea = null;
                    
                    // Priority 1: Try to find textarea by data-textarea-id (most reliable)
                    var textareaId = $input.data('textarea-id') || $input.attr('data-textarea-id');
                    if (textareaId) {
                        $textarea = $('#' + textareaId);
                    }
                    
                    // Priority 2: Try to find by data-param-id (name attribute)
                    if (!$textarea || $textarea.length === 0) {
                        var paramId = $input.data('param-id') || $input.attr('data-param-id');
                        if (paramId) {
                            $textarea = $('textarea[name="' + paramId + '"]');
                        }
                    }
                    
                    // Priority 3: Try to find in same card/group
                    if (!$textarea || $textarea.length === 0) {
                        var $group = $input.closest('.input-group-mobile, .card, .parameter-card');
                        if ($group.length > 0) {
                            $textarea = $group.find('textarea.result_method, textarea[name*="[hasil]"]').first();
                        }
                    }
                    
                    if ($textarea && $textarea.length > 0) {
                        var value = '';
                        
                        if ($input.is('select')) {
                            // Dropdown - get value directly
                            value = $input.val() || '';
                        } else {
                            // TinyMCE or contenteditable
                            var editorId = $input.attr('id');
                            
                            // Try to get from TinyMCE first (if loaded and initialized)
                            if (editorId && typeof tinymce !== 'undefined') {
                                try {
                                    var editor = tinymce.get(editorId);
                                    if (editor && editor.initialized) {
                                        // TinyMCE is ready, get HTML content (preserves sup/sub tags)
                                        value = editor.getContent() || '';
                                    } else {
                                        // TinyMCE not ready yet, use contenteditable as fallback
                                        value = $input.html() || $input.text() || '';
                                    }
                                } catch(e) {
                                    // Error getting TinyMCE, use contenteditable as fallback
                                    console.warn('Error getting TinyMCE content for', editorId, ':', e);
                                    value = $input.html() || $input.text() || '';
                                }
                            } else {
                                // TinyMCE not loaded at all, use contenteditable
                                value = $input.html() || $input.text() || '';
                            }
                            
                            // Clean up HTML if needed (remove empty p tags, etc)
                            if (value && typeof value === 'string') {
                                value = value.replace(/<p><\/p>/g, '');
                                value = value.replace(/<p>\s*<\/p>/g, '');
                                value = value.trim();
                            }
                        }
                        
                        // Set value to textarea
                        $textarea.val(value);
                    }
                });

                // Validate all parameter results
                const parameterTextareas = document.querySelectorAll('textarea[name*="[hasil]"]');
                let allFilled = true;
                let emptyParams = [];
                parameterTextareas.forEach(function(textarea) {
                    var value = textarea.value || '';
                    var trimmed = value.trim();
                    if (!trimmed || trimmed === '' || trimmed === '-') {
                        allFilled = false;
                        // Get parameter name for better error message
                        var paramId = textarea.id.replace('result_method_', '');
                        var $paramCard = $(textarea).closest('.parameter-card');
                        var paramName = $paramCard.find('.parameter-name').text().trim() || 'Parameter ' + paramId;
                        emptyParams.push(paramName);
                    }
                });

                if (!allFilled) {
                    e.preventDefault();
                    var errorMsg = 'Mohon lengkapi semua hasil pemeriksaan parameter';
                    if (emptyParams.length > 0 && emptyParams.length <= 3) {
                        errorMsg += ':\n' + emptyParams.join('\n');
                    }
                    alert(errorMsg);
                    return false;
                }
            });
        });
    </script>
    
    <!-- TinyMCE CDN -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.7/tinymce.min.js"></script>
    
    <!-- TinyMCE Editor Modal and Scripts -->
    <script>
        (function() {
            function initApp() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initApp, 100);
                    return;
                }

                jQuery(document).ready(function($) {
                    var currentEditorTarget = null;
                    var editorInstance = null;
                    var allEditorButtons = [];

                    // Collect all editor buttons and dropdowns
                    function collectEditorButtons() {
                        allEditorButtons = [];
                        var allButtons = [];
                        
                        // Collect editor buttons with their DOM position
                        $('.open-editor-modal').each(function() {
                            var $btn = $(this);
                            var position = $btn.offset().top * 10000 + $btn.offset().left; // Use position for sorting
                            allButtons.push({
                                button: $btn,
                                methodId: $btn.data('method-id'),
                                targetId: $btn.data('target'),
                                methodName: $btn.data('method-name'),
                                isOption: $btn.data('is-option') || 0,
                                type: 'editor',
                                position: position
                            });
                        });
                        
                        // Collect dropdown buttons with their DOM position
                        $('.open-dropdown-modal').each(function() {
                            var $btn = $(this);
                            var methodId = $btn.data('method-id');
                            var targetId = $btn.data('target');
                            var position = $btn.offset().top * 10000 + $btn.offset().left; // Use position for sorting
                            allButtons.push({
                                button: $btn,
                                methodId: methodId,
                                targetId: targetId,
                                methodName: $btn.data('method-name') || 'Parameter',
                                isOption: $btn.data('is-option') || 1,
                                type: 'dropdown',
                                options: $btn.data('options') || [],
                                currentValue: $btn.data('current-value') || '',
                                position: position
                            });
                        });
                        
                        // Sort by DOM position to maintain order
                        allButtons.sort(function(a, b) {
                            return a.position - b.position;
                        });
                        
                        // Add index for easier debugging
                        allButtons.forEach(function(btn, idx) {
                            btn.index = idx;
                            allEditorButtons.push(btn);
                        });
                        
                        console.log('collectEditorButtons: Collected', allEditorButtons.length, 'buttons');
                    }

                    collectEditorButtons();

                    // Convert HTML to plain text
                    function stripHtmlTags(html) {
                        if (!html) return '';
                        var tmp = document.createElement('DIV');
                        tmp.innerHTML = html;
                        return tmp.textContent || tmp.innerText || '';
                    }

                    // Convert to TinyMCE format
                    function convertToTinyMCE(value) {
                        if (!value) return '';
                        value = value.replace(/≤/g, '&le;');
                        value = value.replace(/≥/g, '&ge;');
                        value = value.replace(/±/g, '&plusmn;');
                        value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                        value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                        return value;
                    }

                    // Convert from TinyMCE format
                    function convertFromTinyMCE(value) {
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
                    }

                    // Open editor for target
                    function openEditorForTarget(targetId) {
                        var buttonData = allEditorButtons.find(function(item) {
                            return item.targetId == targetId;
                        });

                        if (buttonData) {
                            // Set current target BEFORE getting value
                            currentEditorTarget = targetId;
                            var methodNamePlain = stripHtmlTags(buttonData.methodName);
                            $('#editorModalLabel').text('Editor - ' + methodNamePlain);
                            
                            // Clear editor content first (will be set when modal is shown)
                            $('#editor_content').val('');
                            
                            // Show modal (value will be loaded from target textarea in shown.bs.modal event)
                            $('#editorModal').modal('show');
                        }
                    }

                    $('.open-editor-modal').on('click', function() {
                        var targetId = $(this).data('target');
                        openEditorForTarget(targetId);
                    });

                    // Open dropdown modal
                    var currentDropdownTarget = null;
                    var currentDropdownInfo = null;
                    
                    function openDropdownForTarget(targetId) {
                        var buttonData = allEditorButtons.find(function(item) {
                            return item.targetId == targetId && item.type === 'dropdown';
                        });

                        if (buttonData) {
                            // IMPORTANT: Update currentEditorTarget when opening dropdown
                            // This ensures getNextTargetInfo() works correctly
                            currentEditorTarget = targetId;
                            currentDropdownTarget = targetId;
                            currentDropdownInfo = buttonData;
                            
                            var methodNamePlain = stripHtmlTags(buttonData.methodName);
                            $('#dropdownModalLabel').text('Pilih Hasil - ' + methodNamePlain);
                            
                            // Populate options
                            var $optionsList = $('#dropdown_options_list');
                            $optionsList.empty();
                            
                            if (buttonData.options && buttonData.options.length > 0) {
                                buttonData.options.forEach(function(opt) {
                                    var isSelected = buttonData.currentValue === opt;
                                    var $option = $('<button>')
                                        .addClass('list-group-item list-group-item-action dropdown-option')
                                        .attr('data-value', opt)
                                        .attr('type', 'button')
                                        .text(opt)
                                        .css({
                                            'cursor': 'pointer',
                                            'text-align': 'left',
                                            'padding': '12px 15px',
                                            'border': '1px solid #dee2e6',
                                            'margin-bottom': '5px',
                                            'border-radius': '5px',
                                            'width': '100%',
                                            'background-color': isSelected ? '#e7f3ff' : '',
                                            'border-color': isSelected ? '#2196F3' : '#dee2e6',
                                            'font-weight': isSelected ? 'bold' : 'normal'
                                        });
                                    
                                    $option.on('click', function() {
                                        // Remove previous selection
                                        $('.dropdown-option').css({
                                            'background-color': '',
                                            'border-color': '#dee2e6',
                                            'font-weight': 'normal'
                                        });
                                        
                                        // Highlight selected
                                        $(this).css({
                                            'background-color': '#e7f3ff',
                                            'border-color': '#2196F3',
                                            'font-weight': 'bold'
                                        });
                                        
                                        // Store selected value
                                        $('#dropdownModal').data('selected-value', opt);
                                    });
                                    
                                    $optionsList.append($option);
                                });
                            }
                            
                            // Set current selection
                            var currentVal = buttonData.currentValue || '';
                            $('#dropdownModal').data('selected-value', currentVal);
                            
                            // Show modal first, then highlight after modal is shown
                            $('#dropdownModal').modal('show');
                            
                            // Highlight current selection after modal is shown
                            $('#dropdownModal').on('shown.bs.modal', function() {
                                if (currentVal) {
                                    $('.dropdown-option[data-value="' + currentVal + '"]').css({
                                        'background-color': '#e7f3ff',
                                        'border-color': '#2196F3',
                                        'font-weight': 'bold'
                                    });
                                }
                                $('#dropdownModal').off('shown.bs.modal');
                            });
                        }
                    }

                    $('.open-dropdown-modal').on('click', function() {
                        var targetId = $(this).data('target');
                        // Ensure currentEditorTarget is set when clicking dropdown button directly
                        currentEditorTarget = targetId;
                        openDropdownForTarget(targetId);
                    });

                    // Initialize TinyMCE when modal is shown
                    $('#editorModal').on('shown.bs.modal', function() {
                        if (editorInstance) {
                            try {
                                tinymce.remove('#editor_content');
                            } catch (e) {}
                            editorInstance = null;
                        }

                        var targetValue = '';
                        if (currentEditorTarget) {
                            targetValue = $('#' + currentEditorTarget).val() || '';
                        }
                        var tinymceValue = convertToTinyMCE(targetValue);
                        $('#editor_content').val(tinymceValue);

                        tinymce.init({
                            selector: '#editor_content',
                            height: 300,
                            base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                            suffix: '.min',
                            skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                            content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                            menubar: false,
                            plugins: ['advlist autolink lists charmap', 'searchreplace code', 'insertdatetime paste help wordcount'],
                            toolbar: 'undo redo | bold italic underline | superscript subscript | charmap | removeformat | code | help',
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
                                editor.on('init', function() {
                                    if (tinymceValue) {
                                        editor.setContent(tinymceValue);
                                    }
                                });
                            }
                        });
                    });

                    // Get next target ID and info (based on DOM order)
                    function getNextTargetInfo() {
                        // Re-collect buttons to ensure we have latest data
                        collectEditorButtons();
                        
                        if (!currentEditorTarget || allEditorButtons.length === 0) {
                            console.log('getNextTargetInfo: No currentEditorTarget or no buttons');
                            return null;
                        }

                        var currentIndex = -1;
                        for (var i = 0; i < allEditorButtons.length; i++) {
                            if (allEditorButtons[i].targetId == currentEditorTarget) {
                                currentIndex = i;
                                break;
                            }
                        }

                        console.log('getNextTargetInfo: Current index:', currentIndex, 'of', allEditorButtons.length);

                        // Find next button in DOM order
                        if (currentIndex >= 0 && currentIndex < allEditorButtons.length - 1) {
                            var nextButton = allEditorButtons[currentIndex + 1];
                            console.log('getNextTargetInfo: Found next button:', nextButton);
                            return {
                                targetId: nextButton.targetId,
                                methodId: nextButton.methodId,
                                methodName: nextButton.methodName,
                                isOption: nextButton.isOption || 0,
                                type: nextButton.type || 'editor',
                                options: nextButton.options || [],
                                currentValue: nextButton.currentValue || '',
                                button: nextButton.button
                            };
                        }

                        console.log('getNextTargetInfo: No next button found');
                        return null;
                    }

                    // Save from editor
                    function saveEditorContent(goToNext) {
                        goToNext = goToNext || false;

                        if (editorInstance && currentEditorTarget) {
                            var htmlContent = editorInstance.getContent();
                            var convertedContent = convertFromTinyMCE(htmlContent);
                            $('#' + currentEditorTarget).val(convertedContent);
                            
                            if (currentEditorTarget.startsWith('result_method_')) {
                                var id = currentEditorTarget.replace('result_method_', '');
                                updateResultPreview('result_method_' + id);
                            }
                            
                            if (goToNext) {
                                // Get next target info
                                var nextInfo = getNextTargetInfo();
                                if (nextInfo) {
                                    // Close modal first
                                    $('#editorModal').modal('hide');
                                    
                                    // Wait for modal to close, then handle next
                                    $('#editorModal').on('hidden.bs.modal', function() {
                                        $('#editorModal').off('hidden.bs.modal');
                                        
                                        // Check if next parameter uses dropdown (is_option = 1)
                                        if (nextInfo.isOption == 1 && nextInfo.type === 'dropdown') {
                                            // Open dropdown popup for next parameter
                                            setTimeout(function() {
                                                openDropdownForTarget(nextInfo.targetId);
                                            }, 300);
                                        } else {
                                            // Next parameter uses editor, open it
                                            setTimeout(function() {
                                                openEditorForTarget(nextInfo.targetId);
                                            }, 300);
                                        }
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

                    // Save dropdown selection
                    function saveDropdownSelection(goToNext) {
                        goToNext = goToNext || false;
                        
                        if (currentDropdownTarget && currentDropdownInfo) {
                            var selectedValue = $('#dropdownModal').data('selected-value') || '';
                            
                            if (!selectedValue) {
                                alert('Silakan pilih hasil terlebih dahulu');
                                return;
                            }
                            
                            // Update textarea
                            var $textarea = $('#' + currentDropdownTarget);
                            if ($textarea.length) {
                                $textarea.val(selectedValue);
                                updateResultPreview(currentDropdownTarget);
                            }
                            
                            // Update hidden select
                            var $select = $('#result_dropdown_' + currentDropdownInfo.methodId);
                            if ($select.length) {
                                $select.val(selectedValue);
                            }
                            
                            // Update button text to show selected value
                            var $button = $('.open-dropdown-modal[data-target="' + currentDropdownTarget + '"]');
                            if ($button.length) {
                                $button.html('<i class="fa fa-list mr-1"></i>' + selectedValue);
                                $button.data('current-value', selectedValue);
                                
                                // Update in allEditorButtons array
                                var buttonData = allEditorButtons.find(function(item) {
                                    return item.targetId == currentDropdownTarget;
                                });
                                if (buttonData) {
                                    buttonData.currentValue = selectedValue;
                                    // Also update the button element in the array
                                    if (buttonData.button && buttonData.button.length) {
                                        buttonData.button.data('current-value', selectedValue);
                                    }
                                }
                            }
                            
                            // IMPORTANT: Update currentEditorTarget BEFORE closing modal
                            // This ensures getNextTargetInfo() can find the next parameter correctly
                            currentEditorTarget = currentDropdownTarget;
                            
                            // Close modal
                            $('#dropdownModal').modal('hide');
                            
                            if (goToNext) {
                                // Wait for modal to close, then handle next
                                $('#dropdownModal').on('hidden.bs.modal', function() {
                                    $('#dropdownModal').off('hidden.bs.modal');
                                    
                                    // IMPORTANT: Ensure currentEditorTarget is set correctly
                                    // This must be set before getNextTargetInfo() is called
                                    currentEditorTarget = currentDropdownTarget;
                                    
                                    // Small delay to ensure modal is fully closed and DOM is ready
                                    setTimeout(function() {
                                        // Re-collect buttons to ensure we have latest data
                                        collectEditorButtons();
                                        
                                        // Get next target info (should find the next parameter now)
                                        var nextInfo = getNextTargetInfo();
                                        console.log('After dropdown save - Current editor target:', currentEditorTarget);
                                        console.log('After dropdown save - All buttons count:', allEditorButtons.length);
                                        console.log('After dropdown save - Next target info:', nextInfo);
                                        
                                        if (nextInfo) {
                                            // Check if next parameter uses dropdown (is_option = 1)
                                            if (nextInfo.isOption == 1 && nextInfo.type === 'dropdown') {
                                                // Open dropdown popup for next parameter
                                                setTimeout(function() {
                                                    openDropdownForTarget(nextInfo.targetId);
                                                }, 200);
                                            } else {
                                                // Next parameter uses editor, open it
                                                setTimeout(function() {
                                                    openEditorForTarget(nextInfo.targetId);
                                                }, 200);
                                            }
                                        } else {
                                            // No more parameters
                                            console.log('No more parameters found');
                                        }
                                    }, 200);
                                });
                            }
                        }
                    }

                    $('#saveDropdownContent').on('click', function() {
                        saveDropdownSelection(false);
                    });

                    $('#saveAndNextDropdownContent').on('click', function() {
                        saveDropdownSelection(true);
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
                        // Don't reset currentEditorTarget if we're going to next
                        // It will be reset when opening next editor
                    });

                    // Handle dropdown change
                    $(document).on('change', '.result-dropdown', function() {
                        var methodId = $(this).data('method-id');
                        var selectedValue = $(this).val();
                        var $textarea = $('#result_method_' + methodId);
                        if ($textarea.length) {
                            $textarea.val(selectedValue);
                            updateResultPreview('result_method_' + methodId);
                        }
                    });

                    // Helper function to format value for display
                    function toFormatHtml(value, inputNumberFormat) {
                        // rubah pangkat dan subscript - DIRECT CONVERSION (NO PLACEHOLDER)
                        if (!value) return '';
                        
                        // Convert number format if input is in ID format (BEFORE HTML processing)
                        if (inputNumberFormat === 'id' && value && typeof value === 'string') {
                            // Regex untuk detect angka dengan format ID (ribuan: titik, desimal: koma)
                            // Contoh: 1.234,56 atau 1234,56 atau 4,0 - 6,5 atau dengan whitespace
                            // Note: JavaScript doesn't support negative lookbehind, so we use a workaround
                            value = value.replace(/\b(\d{1,3}(?:\.\d{3})*(?:,\d+)?|\d+,\d+|\d+)(?!\()\b/g, function(match, p1, offset, string) {
                                // Check if previous character is not ^ (negative lookbehind workaround)
                                if (offset > 0 && string[offset - 1] === '^') {
                                    return match; // Don't process if preceded by ^
                                }
                                var number = p1;
                                
                                // Convert ID format to EN format (database format)
                                // Step 1: Remove ALL whitespace
                                var cleanNumber = number.replace(/\s+/g, '');
                                // Step 2: Remove ALL dot (thousands separator in ID)
                                cleanNumber = cleanNumber.replace(/\./g, '');
                                // Step 3: Replace comma with dot (decimal separator)
                                cleanNumber = cleanNumber.replace(/,/g, '.');
                                // Step 4: Remove any remaining non-numeric except dot and minus
                                cleanNumber = cleanNumber.replace(/[^\d.-]/g, '');
                                
                                // Skip jika bukan angka yang valid
                                if (isNaN(cleanNumber) || cleanNumber === '') {
                                    return match;
                                }
                                
                                // Return in EN format (standard database format)
                                // No thousands separator, dot for decimal
                                return cleanNumber;
                            });
                        }
                        
                        // Check if value contains HTML tags (from TinyMCE) or HTML entities
                        // More specific HTML tag detection - must start with letter or slash
                        var hasHtmlTags = /<\/?[a-zA-Z][^>]*>/.test(value);
                        var hasHtmlEntities = /&lt;\/?[a-zA-Z][^&]*&gt;/.test(value);
                        
                        // If this is HTML content from TinyMCE, clean up and return
                        if (hasHtmlTags) {
                            // Remove all <p> and </p> tags but keep the content inside
                            value = value.replace(/<p>/g, '');
                            value = value.replace(/<\/p>/g, '');
                            // Also remove empty paragraph tags (just in case)
                            value = value.replace(/<p><\/p>/g, '');
                            value = value.replace(/<p>\s*<\/p>/g, '');
                            return value;
                        }
                        
                        // If this is escaped HTML content (HTML entities), decode and clean up
                        if (hasHtmlEntities) {
                            // Create a temporary div to decode HTML entities
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = value;
                            var decoded = tempDiv.textContent || tempDiv.innerText || '';
                            // textContent/innerText already removes HTML tags, but just in case:
                            // Remove all <p> and </p> tags but keep the content inside
                            decoded = decoded.replace(/<p>/g, '');
                            decoded = decoded.replace(/<\/p>/g, '');
                            // Also remove empty paragraph tags (just in case)
                            decoded = decoded.replace(/<p><\/p>/g, '');
                            decoded = decoded.replace(/<p>\s*<\/p>/g, '');
                            return decoded;
                        }
                        
                        // Auto-close kurung yang tidak tertutup untuk pangkat
                        var openSupCount = (value.match(/\^\(/g) || []).length;
                        var openSubCount = (value.match(/_\(/g) || []).length;
                        var closeCount = (value.match(/\)/g) || []).length;
                        
                        // Jika ada ^( atau _( yang tidak tertutup, tambahkan ) di akhir
                        var totalOpen = openSupCount + openSubCount;
                        if (totalOpen > closeCount) {
                            value += ')'.repeat(totalOpen - closeCount);
                        }
                        
                        // Step 1: Replace comparison operators FIRST (before < and >)
                        value = value.replace(/<=/g, '&#8804;');
                        value = value.replace(/>=/g, '&#8805;');
                        value = value.replace(/≤/g, '&#8804;');
                        value = value.replace(/≥/g, '&#8805;');
                        
                        // Step 2: Replace remaining < and > symbols (after operators replaced)
                        value = value.replace(/</g, '&#60;');
                        value = value.replace(/>/g, '&#62;');
                        
                        // Step 2.5: Convert Unicode superscript characters to <sup> tags BEFORE processing ^() format
                        // This handles characters like ³, ², ¹, etc. that might be in the data
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
                        
                        // Step 3: Convert ^( and _( to HTML tags DIRECTLY (character by character)
                        // Process with stack to handle nested/sequential sup/sub properly
                        var result = '';
                        var tagStack = [];
                        var i = 0;
                        var len = value.length;
                        
                        while (i < len) {
                            // Check for superscript opening ^(
                            if (i < len - 1 && value[i] === '^' && value[i + 1] === '(') {
                                result += '<sup>';
                                tagStack.push('sup');
                                i += 2; // Skip ^(
                            }
                            // Check for subscript opening _(
                            else if (i < len - 1 && value[i] === '_' && value[i + 1] === '(') {
                                result += '<sub>';
                                tagStack.push('sub');
                                i += 2; // Skip _(
                            }
                            // Check for closing )
                            else if (value[i] === ')' && tagStack.length > 0) {
                                var tag = tagStack.pop();
                                result += (tag === 'sub') ? '</sub>' : '</sup>';
                                i++;
                            }
                            // Regular character
                            else {
                                result += value[i];
                                i++;
                            }
                        }
                        value = result;
                        
                        // Step 4: Handle line breaks and spaces for non-HTML content
                        value = value.replace(/\n/g, '<br>');
                        value = value.replace(/ /g, '&nbsp;');
                        
                        return value;
                    }

                    // Helper function to create result badge with optional kesimpulan
                    function createResultBadge(value, type, kesimpulan) {
                        kesimpulan = kesimpulan || '';
                        var badgeClass, icon, additionalIcon = '';
                        if (type === 'danger') {
                            badgeClass = 'badge badge-danger';
                            icon = '<i class="fa fa-times-circle"></i> ';
                            additionalIcon = ' <i class="fa fa-exclamation-triangle"></i>';
                        } else if (type === 'success') {
                            badgeClass = 'badge badge-success';
                            icon = '<i class="fa fa-check-circle"></i> ';
                        } else {
                            badgeClass = 'badge badge-secondary';
                            icon = '';
                        }
                        var badge = '<span class="' + badgeClass + '" style="font-size: 14px; padding: 8px 12px;">' +
                            icon + toFormatHtml(value) + additionalIcon + '</span>';

                            console.log("value: ", toFormatHtml(value));
                        
                        // Add kesimpulan if provided
                        if (kesimpulan && kesimpulan.trim() !== '') {
                            badge += '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + toFormatHtml(kesimpulan) + '</small>';
                        }
                        
                        return badge;
                    }

                    // Helper function to check if value is valid numeric
                    function isValidNumeric(val, format) {
                        format = format || 'en';
                        var num = parseNumberInput(val, format);
                        return num !== null && !isNaN(num) && isFinite(num);
                    }

                    // Helper function to check if value is valid (for equal check)
                    function isValidEqual(val) {
                        return val !== "" && val !== null && val !== undefined && String(val).trim() !== "";
                    }

                    // Helper function to extract numeric value from string
                    function extractNumericValue(str, format) {
                        format = format || 'en';
                        if (!str) return null;
                        var cleaned = str.toString().replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                        
                        // Try to extract number from patterns like "<100", ">50", etc.
                        var match = cleaned.match(/^[<>≤≥]?\s*([\d.,]+)/);
                        if (match) {
                            return parseNumberInput(match[1], format);
                        }
                        
                        // Try direct parsing
                        var num = parseNumberInput(cleaned, format);
                        return num;
                    }

                    // Check result against selected baku mutu (mengikuti logika dari analis-permohonan-uji-paramater-klinik.blade.php)
                    function checkAgainstSelectedBakuMutu(rawValue, selectedBakuMutu, numberFormat) {
                        numberFormat = numberFormat || 'en';
                        
                        if (!selectedBakuMutu || selectedBakuMutu.length === 0) {
                            return { melewati: true, matched: null };
                        }

                        var hasil_clean = rawValue.toString().replace(/&nbsp;/g, ' ').trim();
                        var hasil_numeric = extractNumericValue(rawValue, numberFormat);
                        var isWithinAnyRange = false;
                        var matchedBakuMutu = null;

                        // Cek apakah hasil masuk dalam salah satu range yang dipilih
                        for (var i = 0; i < selectedBakuMutu.length; i++) {
                            var bm = selectedBakuMutu[i];
                            var isWithinThisRange = false;

                            // Cek dengan equal terlebih dahulu
                            if (isValidEqual(bm.equal)) {
                                var equal_clean = String(bm.equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                var hasil_compare = hasil_clean.replace(/\s/g, '');
                                if (hasil_compare === equal_clean) {
                                    isWithinThisRange = true;
                                    matchedBakuMutu = bm;
                                }
                            }
                            // Jika tidak ada equal, cek dengan min dan max
                            else if (hasil_numeric !== null && !isNaN(hasil_numeric)) {
                                var dbFormat = numberFormat || 'en'; // Use numberFormat or fallback to 'en'
                                var hasMin = isValidNumeric(bm.min, dbFormat);
                                var hasMax = isValidNumeric(bm.max, dbFormat);

                                if (hasMin && hasMax) {
                                    var bmMin = parseNumberInput(bm.min, dbFormat);
                                    var bmMax = parseNumberInput(bm.max, dbFormat);
                                    if (hasil_numeric >= bmMin && hasil_numeric <= bmMax) {
                                        isWithinThisRange = true;
                                        matchedBakuMutu = bm;
                                    }
                                } else if (hasMin) {
                                    var bmMin = parseNumberInput(bm.min, dbFormat);
                                    if (hasil_numeric >= bmMin) {
                                        isWithinThisRange = true;
                                        matchedBakuMutu = bm;
                                    }
                                } else if (hasMax) {
                                    var bmMax = parseNumberInput(bm.max, dbFormat);
                                    // Handle format ">100" - jika hasil <= max, maka dalam range
                                    if (/^>\s*[\d.,]+/.test(hasil_clean)) {
                                        if (hasil_numeric <= bmMax) {
                                            isWithinThisRange = true;
                                            matchedBakuMutu = bm;
                                        }
                                    } else if (hasil_numeric <= bmMax) {
                                        isWithinThisRange = true;
                                        matchedBakuMutu = bm;
                                    }
                                } else {
                                    // Tidak ada min dan max, anggap dalam range
                                    isWithinThisRange = true;
                                    matchedBakuMutu = bm;
                                }
                            }

                            if (isWithinThisRange) {
                                isWithinAnyRange = true;
                                break; // Sudah ketemu yang match, tidak perlu cek yang lain
                            }
                        }

                        return {
                            melewati: !isWithinAnyRange, // Melewati jika TIDAK masuk dalam range manapun
                            matched: matchedBakuMutu
                        };
                    }

                    // Update result preview function with multiple baku mutu support
                    // Mengikuti logika dari analis-permohonan-uji-paramater-klinik.blade.php
                    function updateResultPreview(textareaId) {
                        var $textarea = $('#' + textareaId);
                        if (!$textarea.length) return;

                        var id = textareaId.replace('result_method_', '');
                        var rawValue = $textarea.val();
                        var min = $textarea.data('min');
                        var max = $textarea.data('max');
                        var equal = $textarea.data('equal');
                        var selectedBakuMutuData = $textarea.data('selected-baku-mutu');
                        var selectedBakuMutu = selectedBakuMutuData ? (typeof selectedBakuMutuData === 'string' ? JSON.parse(selectedBakuMutuData) : selectedBakuMutuData) : [];
                        
                        // Get number format from closest parameter card
                        var $paramCard = $textarea.closest('.parameter-card');
                        var numberFormat = $paramCard.find('.parameter-number-format').val() || 'en';

                        // Remove spaces from rawValue for checking
                        var delete_space = rawValue ? String(rawValue).replace(/\s/g, '') : '';

                        if (delete_space && delete_space !== "" && delete_space !== "-") {
                            var melewati_baku_mutu = false;
                            var matchedBakuMutu = null;

                            // Prioritas: Cek terhadap selected baku mutu terlebih dahulu
                            if (selectedBakuMutu && selectedBakuMutu.length > 0) {
                                // Cek terhadap semua baku mutu yang dipilih
                                var checkResult = checkAgainstSelectedBakuMutu(rawValue, selectedBakuMutu, numberFormat);
                                melewati_baku_mutu = checkResult.melewati;
                                matchedBakuMutu = checkResult.matched;
                            } else {
                                // Fallback ke single baku mutu (min, max, equal) jika tidak ada selected
                                var hasil_clean = rawValue.toString().replace(/&nbsp;/g, ' ').trim();
                                var hasil_numeric = extractNumericValue(rawValue, numberFormat);

                                if (isValidEqual(equal)) {
                                    var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                                    var hasil_compare = hasil_clean.replace(/\s/g, '');
                                    if (hasil_compare !== equal_clean) {
                                        melewati_baku_mutu = true;
                                    } else {
                                        melewati_baku_mutu = false;
                                    }
                                } else if (hasil_numeric !== null && !isNaN(hasil_numeric)) {
                                    var dbFormat = numberFormat || 'en'; // Use numberFormat or fallback to 'en'
                                    var hasMin = isValidNumeric(min, dbFormat);
                                    var hasMax = isValidNumeric(max, dbFormat);

                                    if (hasMin && hasMax) {
                                        var minNum = parseNumberInput(min, dbFormat);
                                        var maxNum = parseNumberInput(max, dbFormat);
                                        if (hasil_numeric < minNum || hasil_numeric > maxNum) {
                                            melewati_baku_mutu = true;
                                        } else {
                                            melewati_baku_mutu = false;
                                        }
                                    } else if (hasMin) {
                                        var minNum = parseNumberInput(min, dbFormat);
                                        if (hasil_numeric < minNum) {
                                            melewati_baku_mutu = true;
                                        } else {
                                            melewati_baku_mutu = false;
                                        }
                                    } else if (hasMax) {
                                        var maxNum = parseNumberInput(max, dbFormat);
                                        if (/^>\s*[\d.,]+/.test(hasil_clean)) {
                                            if (hasil_numeric > maxNum) {
                                                melewati_baku_mutu = true;
                                            } else {
                                                melewati_baku_mutu = false;
                                            }
                                        } else if (hasil_numeric > maxNum) {
                                            melewati_baku_mutu = true;
                                        } else {
                                            melewati_baku_mutu = false;
                                        }
                                    } else {
                                        melewati_baku_mutu = false;
                                    }
                                } else {
                                    melewati_baku_mutu = false;
                                }
                            }

                            // Apply styling based on result
                            var value = toFormatHtml(rawValue);
                            var kesimpulan = '';
                            
                            // Get kesimpulan from matched baku mutu
                            if (matchedBakuMutu && matchedBakuMutu.kesimpulan_baku_mutu) {
                                kesimpulan = matchedBakuMutu.kesimpulan_baku_mutu;
                            }
                            
                            if (melewati_baku_mutu) {
                                $('#result_output_method_' + id).html(createResultBadge(value, 'danger', kesimpulan));
                            } else {
                                $('#result_output_method_' + id).html(createResultBadge(value, 'success', kesimpulan));
                            }
                        } else {
                            $('#result_output_method_' + id).html(createResultBadge('-', 'secondary'));
                        }
                    }

                    // Initialize: sync dropdown to textarea and trigger preview update
                    $('.result-dropdown').each(function() {
                        var methodId = $(this).data('method-id');
                        var selectedValue = $(this).val();
                        var $textarea = $('#result_method_' + methodId);
                        if ($textarea.length && selectedValue) {
                            $textarea.val(selectedValue);
                            updateResultPreview('result_method_' + methodId);
                            
                            // Update button text if exists
                            var $button = $('.open-dropdown-modal[data-target="result_method_' + methodId + '"]');
                            if ($button.length) {
                                $button.html('<i class="fa fa-list mr-1"></i>' + selectedValue);
                                $button.data('current-value', selectedValue);
                            }
                        }
                    });

                    // Real-time checking when textarea value changes (for TinyMCE editor)
                    $(document).on('input change', '.result_method', function() {
                        var textareaId = $(this).attr('id');
                        if (textareaId) {
                            updateResultPreview(textareaId);
                        }
                    });

                    // Initialize all result previews on page load
                    $('.result_method').each(function() {
                        var textareaId = $(this).attr('id');
                        if (textareaId) {
                            updateResultPreview(textareaId);
                        }
                    });

                    // Re-collect buttons after all initialization is done
                    // This ensures all buttons (including dropdowns) are in the list
                    setTimeout(function() {
                        collectEditorButtons();
                        console.log('Buttons collected:', allEditorButtons.length);
                    }, 500);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initApp);
            } else {
                initApp();
            }
        })();
    </script>

    <!-- TinyMCE Editor Modal -->
    <div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%); color: white;">
                    <h5 class="modal-title" id="editorModalLabel">
                        <i class="fa fa-edit mr-2"></i>Editor Hasil
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 12px;">
                        <i class="fa fa-info-circle mr-2"></i>
                        <strong>Tips Penggunaan Editor:</strong>
                        <ul class="mb-0 mt-2" style="font-size: 12px;">
                            <li>Ketik angka atau teks hasil pengujian</li>
                            <li>Untuk <strong>pangkat (superscript)</strong>: pilih angka → klik tombol <strong>x<sup>2</sup></strong> di toolbar</li>
                            <li>Untuk <strong>subscript</strong>: pilih angka → klik tombol <strong>x<sub>2</sub></strong> di toolbar</li>
                            <li>Untuk <strong>simbol matematika</strong> (≤, ≥, ±, <,>): klik tombol <strong>Ω (Charmap)</strong> di toolbar</li>
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

    <!-- Dropdown Selection Modal -->
    <div class="modal fade" id="dropdownModal" tabindex="-1" role="dialog" aria-labelledby="dropdownModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document" style="max-width: 90%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="modal-title" id="dropdownModalLabel">
                        <i class="fa fa-list mr-2"></i>Pilih Hasil
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 12px;">
                        <i class="fa fa-info-circle mr-2"></i>
                        <strong>Pilih salah satu hasil dari daftar di bawah ini:</strong>
                    </div>
                    <div id="dropdown_options_list" class="list-group" style="max-height: 400px; overflow-y: auto;">
                        <!-- Options will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" id="saveDropdownContent">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAndNextDropdownContent">
                        <i class="fa fa-save mr-1"></i>Simpan & Lanjut
                        <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Info Sample -->
    <div class="modal fade" id="infoSampleModal" tabindex="-1" role="dialog" aria-labelledby="infoSampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%); color: white;">
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
                            $targetInput = $targetCard.find('.open-dropdown-modal, .open-editor-modal').first();
                        }
                        if ($targetInput.length === 0) {
                            console.log('Target input not found in card:', $targetCard);
                            return;
                        }
                        
                        console.log('Target input found, scrolling...', $targetInput);
                        
                        // Highlight target card briefly
                        $targetCard.css({
                            'box-shadow': '0 0 0 3px rgba(45, 107, 207, 0.5)',
                            'transition': 'box-shadow 0.3s',
                            'background-color': 'rgba(45, 107, 207, 0.05)'
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
    <script src="{{ asset('assets/js/mobile-inline-editing.js') }}"></script>
</body>

</html>

