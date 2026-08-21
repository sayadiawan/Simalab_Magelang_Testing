<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Daftar Sample Draft</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            min-height: 100vh;
            padding: 20px;
            padding-bottom: 100px;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 18px;
            font-weight: 600;
            color: #0b3a5c;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .offline-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #ffc107;
            color: #333;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2000;
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .offline-indicator.show {
            display: block;
        }
        
        .offline-indicator.online {
            background: #28a745;
            color: white;
        }
        
        .save-session-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .save-session-btn:hover {
            background: #138496;
        }
        
        .save-session-btn:active {
            transform: scale(0.98);
        }
        
        .breadcrumb {
            background: rgba(255, 255, 255, 0.95);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .breadcrumb a {
            color: #0b3a5c;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        
        .breadcrumb a:hover {
            opacity: 0.8;
        }
        
        .breadcrumb-separator {
            color: #999;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .permohonan-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #0b3a5c;
        }

        .permohonan-info h3 {
            font-size: 14px;
            color: #0b3a5c;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-item label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
        }

        .info-item span {
            color: #333;
            flex: 1;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .empty-state p {
            font-size: 14px;
            color: #666;
        }

        .group-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
        }

        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .group-title {
            font-size: 16px;
            font-weight: 600;
            color: #0b3a5c;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .group-count {
            background: #0b3a5c;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .draft-item {
            background: white;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 3px solid #0b3a5c;
        }

        .draft-item:last-child {
            margin-bottom: 0;
        }

        .draft-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }

        .draft-code {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .draft-type {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        .draft-details {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        .draft-details-item {
            margin-bottom: 4px;
        }

        .draft-details-item strong {
            color: #333;
        }

        .draft-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .add-new-btn {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .add-new-btn:active {
            transform: scale(0.98);
        }

        .icon {
            display: inline-block;
            margin-right: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .draft-item.updated {
            border-left-color: #28a745;
            background: #f8fff9;
        }

        .draft-item.not-verified {
            border-left-color: #dc3545;
            background: #fff5f5;
            border-left-width: 4px;
            position: relative;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
        }

        .draft-item.not-verified .draft-code {
            color: #dc3545;
            font-weight: 700;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-warning {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
        }

        .group-actions {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }

        .item-actions {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .item-actions .btn {
            flex: 1;
            min-width: 80px;
            padding: 8px 6px;
            font-size: 11px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 25px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 20px;
            color: #0b3a5c;
            font-size: 18px;
            font-weight: 600;
        }

        .modal-form-group {
            margin-bottom: 15px;
        }

        .modal-form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }

        .modal-form-group textarea,
        .modal-form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }

        .modal-form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .modal-checkbox-list {
            max-height: 200px;
            overflow-y: auto;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            background: #f8f9fa;
        }

        .modal-checkbox-item {
            display: flex;
            align-items: center;
            padding: 8px;
            margin-bottom: 5px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .modal-checkbox-item:hover {
            background: #e9ecef;
        }

        .modal-checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .modal-checkbox-item span {
            font-size: 14px;
            color: #333;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .modal-btn-cancel {
            background: #e0e0e0;
            color: #666;
        }

        .modal-btn-submit {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }
    </style>
    
    <!-- Offline Support & SPA -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/mobile-sampling-offline.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-sampling-spa.js') }}?v={{ time() }}"></script>
    <style>
        .spa-page {
            display: none;
        }
        
        .spa-page.active {
            display: block;
        }
        
        /* Hide loading indicator initially */
        #spa-loader {
            display: none;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @if(isset($backUrl))
        <div class="breadcrumb">
            <a href="{{ $backUrl }}">
                <span>←</span>
                <span>Kembali</span>
            </a>
        </div>
        @endif
        
        <div class="top-bar">
            <h1>📋 Daftar Sample Draft</h1>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="save-session-btn" onclick="saveSessionForOffline()" title="Simpan session untuk mode offline">
                    💾 Simpan Session
                </button>
                <form action="{{ route('mobile.sampling.logout', $permohonan_uji->id_permohonan_uji) }}" method="POST"
                    style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
        
        <!-- Offline Indicator -->
        <div id="offlineIndicator" class="offline-indicator">
            📡 Mode Offline
        </div>

        <div class="card">
            <div class="permohonan-info">
                <h3>📋 Informasi Permohonan</h3>
                <div class="info-item">
                    <label>Pelanggan:</label>
                    <span>{{ $permohonan_uji->customer->name_customer ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal:</label>
                    <span>{{ \Carbon\Carbon::parse($permohonan_uji->date_permohonan_uji)->format('d/m/Y') }}</span>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">
                    <span class="icon">⚠️</span>
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <span class="icon">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($groupedDrafts->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Belum Ada Sample Draft</h3>
                    <p>Admin belum menambahkan sample draft untuk permohonan ini.</p>
                </div>
            @else
                @foreach ($groupedDrafts as $groupId => $drafts)
                    <div class="group-card">
                        <div class="group-header">
                            <div class="group-title">
                                <span>📦</span>
                                <span>Grup {{ $loop->iteration }}</span>
                            </div>
                            <span class="group-count">{{ $drafts->count() }} Sample</span>
                        </div>

                        @foreach ($drafts as $draft)
                            @php
                                // Check if draft has been verified (titik_pengambilan and pengambil_sampel filled)
                                $hasTitikPengambilan = !empty($draft->titik_pengambilan) && trim($draft->titik_pengambilan) !== '-';
                                $hasPengambilSampel = !empty($draft->pengambil_sampel);
                                
                                // Parse pengambil_sampel to check if it's not empty
                                if ($hasPengambilSampel) {
                                    $decoded = json_decode($draft->pengambil_sampel, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $hasPengambilSampel = !empty(array_filter($decoded));
                                    } else {
                                        $hasPengambilSampel = trim($draft->pengambil_sampel) !== '' && trim($draft->pengambil_sampel) !== '-';
                                    }
                                }
                                
                                $isVerified = $hasTitikPengambilan && $hasPengambilSampel;
                                
                                // Check if updated_at is different from created_at (was edited)
                                $wasEdited =
                                    $draft->updated_at &&
                                    $draft->created_at &&
                                    $draft->updated_at->ne($draft->created_at);
                                
                                // If was edited but still missing required fields, consider as not verified
                                if ($wasEdited && !$isVerified) {
                                    $isVerified = false;
                                }
                            @endphp
                            <div class="draft-item {{ $isVerified ? 'updated' : 'not-verified' }}">
                                <div class="draft-header">
                                    <div>
                                        <div class="draft-code">
                                            @if (trim((string) ($draft->codesample_samples ?? '')) === '')
                                                Belum ada kode
                                            @else
                                                {!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($draft) !!}
                                            @endif
                                        </div>
                                        <div class="draft-type">
                                            {{ $draft->sampletype->name_sample_type ?? 'Jenis Sample' }}
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
                                        @if ($isVerified)
                                            <span class="badge badge-success"
                                                title="Sudah terverifikasi: titik lokasi dan pengambil sampel sudah diisi">✓ Sudah
                                                Diisi</span>
                                        @else
                                            <span class="badge badge-danger"
                                                title="Belum terverifikasi: harap isi titik lokasi dan pengambil sampel">⚠ Belum
                                                Terverifikasi</span>
                                        @endif
                                        <span class="badge badge-warning">Draft</span>
                                    </div>
                                </div>

                                <div class="draft-details">
                                    @if ($draft->datesampling_samples)
                                        <div class="draft-details-item">
                                            <strong>Tanggal Sampling:</strong>
                                            {{ \Carbon\Carbon::parse($draft->datesampling_samples)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                    @if ($draft->date_sending)
                                        <div class="draft-details-item">
                                            <strong>Tanggal Kirim:</strong>
                                            {{ \Carbon\Carbon::parse($draft->date_sending)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                    <div class="draft-details-item">
                                        <strong>Titik Pengambilan:</strong>
                                        @if (!empty($draft->titik_pengambilan) && trim($draft->titik_pengambilan) !== '-')
                                            {{ $draft->titik_pengambilan }}
                                        @else
                                            <span style="color: #dc3545; font-style: italic;">⚠ Belum diisi</span>
                                        @endif
                                    </div>
                                    <div class="draft-details-item">
                                        <strong>Pengambil Sampel:</strong>
                                        @if (!empty($draft->pengambil_sampel))
                                            @php
                                                // Try to decode as JSON first
                                                $decoded = json_decode($draft->pengambil_sampel, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $pengambilList = array_filter($decoded);
                                                    if (!empty($pengambilList)) {
                                                        echo implode(', ', $pengambilList);
                                                    } else {
                                                        echo '<span style="color: #dc3545; font-style: italic;">⚠ Belum diisi</span>';
                                                    }
                                                } else {
                                                    $trimmed = trim($draft->pengambil_sampel);
                                                    if ($trimmed !== '' && $trimmed !== '-') {
                                                        echo $draft->pengambil_sampel;
                                                    } else {
                                                        echo '<span style="color: #dc3545; font-style: italic;">⚠ Belum diisi</span>';
                                                    }
                                                }
                                            @endphp
                                        @else
                                            <span style="color: #dc3545; font-style: italic;">⚠ Belum diisi</span>
                                        @endif
                                    </div>
                                    @if ($draft->cost_samples > 0)
                                        <div class="draft-details-item">
                                            <strong>Biaya:</strong>
                                            Rp {{ number_format($draft->cost_samples, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    @if ($draft->note_samples)
                                        <div class="draft-details-item">
                                            <strong>Catatan:</strong>
                                            {{ $draft->note_samples }}
                                        </div>
                                    @endif
                                    @if ($draft->samplemethoddraft && $draft->samplemethoddraft->count() > 0)
                                        <div class="draft-details-item">
                                            <strong>Parameter:</strong>
                                            {{ $draft->samplemethoddraft->count() }} parameter
                                        </div>
                                    @endif
                                </div>

                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="showVerifyModal('{{ $draft->getKey() }}', '{{ $draft->codesample_samples ?? 'Draft' }}', '{{ addslashes($draft->titik_pengambilan ?? '') }}', {{ json_encode($draft->pengambil_sampel ? (is_array(json_decode($draft->pengambil_sampel, true)) ? json_decode($draft->pengambil_sampel, true) : explode(',', $draft->pengambil_sampel)) : []) }})">
                                        ✓ Verif
                                    </button>
                                    <a href="{{ route('mobile.sampling.draft.edit', ['id' => $permohonan_uji->id_permohonan_uji, 'draft_id' => $draft->getKey()]) }}"
                                        class="btn btn-warning btn-sm">
                                        ✏️ Edit
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="deleteDraft('{{ $draft->getKey() }}', '{{ $draft->codesample_samples ?? 'Draft' }}')">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if ($drafts->count() > 1)
                            <div class="group-actions">
                                <button type="button" class="btn btn-danger"
                                    onclick="deleteDraftGroup('{{ $groupId }}', {{ $drafts->count() }})">
                                    🗑️ Hapus Seluruh Grup ({{ $drafts->count() }} sample)
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <div
            style="display: flex; gap: 10px; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: calc(100% - 40px); max-width: 600px; z-index: 1000;">
            <a href="{{ route('mobile.sampling.form', $permohonan_uji->id_permohonan_uji) }}" class="add-new-btn"
                style="flex: 1; text-align: center; text-decoration: none;">
                ➕ Tambah Sample Baru
            </a>
            @if (!$groupedDrafts->isEmpty())
                <button type="button" class="add-new-btn" onclick="showFinishModal()"
                    style="flex: 1; background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    ✅ Selesai
                </button>
            @endif
        </div>
    </div>

    <!-- Modal Verifikasi -->
    <div id="verifyModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">✓ Verifikasi Sample</div>
            <form id="verifyForm">
                <input type="hidden" id="verify_draft_id" name="draft_id">
                
                <div class="modal-form-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i> Titik Lokasi Pengambilan
                    </label>
                    <textarea class="form-control" name="titik_pengambilan" id="verify_titik_pengambilan" rows="3"
                        placeholder="Masukkan titik lokasi pengambilan sampel"></textarea>
                </div>

                <div class="modal-form-group">
                    <label>
                        <i class="fas fa-users"></i> Pengambil Sampel (Bisa lebih dari satu)
                    </label>
                    @if (!empty($pengambil_sampel_list) && count($pengambil_sampel_list) > 0)
                        <div class="modal-checkbox-list" id="verify_pengambil_sampel_list">
                            @foreach ($pengambil_sampel_list as $petugas)
                                <label class="modal-checkbox-item">
                                    <input type="checkbox" name="pengambil_sampel[]" value="{{ $petugas }}">
                                    <span>{{ $petugas }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Pilih satu atau lebih pengambil sampel
                        </small>
                    @else
                        <input type="text" class="form-control" name="pengambil_sampel_text"
                            id="verify_pengambil_sampel_text"
                            placeholder="Masukkan nama pengambil sampel (pisahkan dengan koma jika lebih dari satu)">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Data pengambil sampel belum tersedia di VerificationActivity
                        </small>
                    @endif
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeVerifyModal()">
                        Batal
                    </button>
                    <button type="submit" class="modal-btn modal-btn-submit">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Finish -->
    <div id="finishModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div
            style="background: white; border-radius: 20px; padding: 25px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <h2 style="margin-bottom: 20px; color: #0b3a5c; font-size: 20px;">Selesaikan Sampling</h2>
            <p style="margin-bottom: 20px; color: #666; font-size: 14px;">Masukkan data pelanggan untuk menyelesaikan
                proses sampling. Semua draft akan dikonversi menjadi sample.</p>

            <form id="finishForm">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px;">
                        Nama Pelanggan <span style="color: red;">*</span>
                    </label>
                    <input type="text" name="nama_pelanggan" id="nama_pelanggan" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px;"
                        placeholder="Masukkan nama pelanggan">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px;">
                        Jabatan <span style="color: red;">*</span>
                    </label>
                    <input type="text" name="jabatan_pelanggan" id="jabatan_pelanggan" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px;"
                        placeholder="Masukkan jabatan pelanggan">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px;">
                        NIP (Tidak Wajib)
                    </label>
                    <input type="text" name="nip_pelanggan" id="nip_pelanggan"
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px;"
                        placeholder="Masukkan NIP pelanggan (opsional)">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeFinishModal()"
                        style="flex: 1; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; background: white; color: #666; font-weight: 600; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit"
                        style="flex: 1; padding: 12px; border: none; border-radius: 10px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: 600; cursor: pointer;">
                        Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showVerifyModal(draftId, draftName, titikPengambilan, selectedPengambilSampel) {
            // Set draft ID
            document.getElementById('verify_draft_id').value = draftId;
            
            // Set titik pengambilan
            document.getElementById('verify_titik_pengambilan').value = titikPengambilan || '';
            
            // Set selected pengambil sampel (checkboxes)
            const checkboxes = document.querySelectorAll('#verify_pengambil_sampel_list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectedPengambilSampel && selectedPengambilSampel.includes(checkbox.value);
            });
            
            // Set selected pengambil sampel (text input fallback)
            const textInput = document.getElementById('verify_pengambil_sampel_text');
            if (textInput && selectedPengambilSampel && selectedPengambilSampel.length > 0) {
                textInput.value = Array.isArray(selectedPengambilSampel) ? selectedPengambilSampel.join(', ') : selectedPengambilSampel;
            }
            
            // Show modal
            document.getElementById('verifyModal').style.display = 'flex';
        }

        function closeVerifyModal() {
            document.getElementById('verifyModal').style.display = 'none';
            // Reset form
            document.getElementById('verifyForm').reset();
        }

        // Handle verify form submission with offline support
        document.getElementById('verifyForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const draftId = document.getElementById('verify_draft_id').value;
            const permohonanId = '{{ $permohonan_uji->id_permohonan_uji }}';
            const formData = new FormData(this);

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            // Build request data
            const requestData = {
                titik_pengambilan: formData.get('titik_pengambilan') || '',
                pengambil_sampel: []
            };

            // Get selected checkboxes
            const checkboxes = document.querySelectorAll('#verify_pengambil_sampel_list input[type="checkbox"]:checked');
            checkboxes.forEach(checkbox => {
                requestData.pengambil_sampel.push(checkbox.value);
            });

            // If no checkboxes selected, use text input
            if (requestData.pengambil_sampel.length === 0) {
                const textInput = document.getElementById('verify_pengambil_sampel_text');
                if (textInput && textInput.value) {
                    requestData.pengambil_sampel = textInput.value.split(',').map(n => n.trim()).filter(n => n);
                }
            }

            // Check if online
            const isOnline = navigator.onLine;
            const url = '{{ route('mobile.sampling.draft.verify', ['id' => $permohonan_uji->id_permohonan_uji, 'draft_id' => ':draft_id']) }}'
                .replace(':draft_id', draftId);

            if (isOnline) {
                // Try to submit online
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(requestData)
                    });
                    
                    const data = await response.json();
                    if (data.status) {
                        alert(data.pesan || 'Verifikasi berhasil disimpan!');
                        window.location.reload();
                    } else {
                        throw new Error(data.pesan || 'Gagal menyimpan verifikasi');
                    }
                } catch (error) {
                    // If online request fails, save offline
                    console.error('Online save failed, saving offline:', error);
                    await saveVerifyOffline(permohonanId, draftId, requestData, url, submitBtn, originalText);
                }
            } else {
                // Save offline
                await saveVerifyOffline(permohonanId, draftId, requestData, url, submitBtn, originalText);
            }
        });
        
        // Save verify data offline
        async function saveVerifyOffline(permohonanId, draftId, requestData, url, submitBtn, originalText) {
            if (window.mobileSamplingOffline) {
                // Save to IndexedDB
                await window.mobileSamplingOffline.saveDraft(permohonanId, draftId, {
                    type: 'verify',
                    ...requestData
                });
                
                // Add to sync queue
                await window.mobileSamplingOffline.addToSyncQueue('verify', requestData, url, 'POST');
                
                alert('✅ Data disimpan secara lokal. Akan disinkronkan ketika online kembali.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                closeVerifyModal();
            } else {
                alert('⚠️ Mode offline tidak tersedia. Silakan coba lagi ketika online.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
        
        // Save session for offline
        async function saveSessionForOffline() {
            if (!window.mobileSamplingOffline) {
                alert('⚠️ Fitur offline belum tersedia');
                return;
            }
            
            const sessionData = {
                mobile_sampling_auth: true,
                mobile_sampling_user_id: '{{ session('mobile_sampling_user_id') }}',
                mobile_sampling_user_name: '{{ session('mobile_sampling_user_name') }}',
                mobile_sampling_user_username: '{{ session('mobile_sampling_user_username') }}',
                mobile_sampling_user_level: '{{ session('mobile_sampling_user_level') }}',
                mobile_sampling_id: '{{ $permohonan_uji->id_permohonan_uji }}',
                permohonan_uji: {
                    id: '{{ $permohonan_uji->id_permohonan_uji }}',
                    code: '{{ $permohonan_uji->code_permohonan_uji }}',
                    customer: '{{ $permohonan_uji->customer->name_customer ?? '' }}',
                    date: '{{ $permohonan_uji->date_permohonan_uji }}'
                }
            };
            
            const saved = await window.mobileSamplingOffline.saveSession(sessionData);
            if (saved) {
                alert('✅ Session berhasil disimpan! Anda dapat menggunakan aplikasi tanpa internet.');
            } else {
                alert('❌ Gagal menyimpan session');
            }
        }
        
        // Check online/offline status
        function updateOfflineIndicator() {
            const indicator = document.getElementById('offlineIndicator');
            if (navigator.onLine) {
                indicator.textContent = '✅ Online';
                indicator.className = 'offline-indicator online';
                indicator.style.display = 'none';
            } else {
                indicator.textContent = '📡 Mode Offline';
                indicator.className = 'offline-indicator';
                indicator.classList.add('show');
            }
        }
        
        // Initialize offline indicator
        window.addEventListener('load', () => {
            updateOfflineIndicator();
            window.addEventListener('online', updateOfflineIndicator);
            window.addEventListener('offline', updateOfflineIndicator);
        });
        
        // Listen for sync complete event (non-blocking refresh)
        window.addEventListener('offline-sync-complete', (e) => {
            // Silently refresh draft list if on draft-list page
            if (window.mobileSamplingSPA && window.mobileSamplingSPA.currentPage === 'draft-list') {
                // Refresh without full reload
                window.mobileSamplingSPA.refreshDraftList();
            }
        });

        // Close modal when clicking outside
        document.getElementById('verifyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeVerifyModal();
            }
        });

        // Make functions available globally
        window.showFinishModal = function() {
            const modal = document.getElementById('finishModal');
            if (modal) {
                modal.style.display = 'flex';
        }
        };

        window.closeFinishModal = function() {
            const modal = document.getElementById('finishModal');
            if (modal) {
                modal.style.display = 'none';
        }
        };

        // Handle form submission - use event delegation for SPA compatibility
        function setupFinishFormHandler() {
            const finishForm = document.getElementById('finishForm');
            if (finishForm) {
                // Remove existing listener if any
                const newForm = finishForm.cloneNode(true);
                finishForm.parentNode.replaceChild(newForm, finishForm);
                
                newForm.addEventListener('submit', function(e) {
            e.preventDefault();
                    e.stopPropagation();

                    const namaPelanggan = document.getElementById('nama_pelanggan');
                    const jabatanPelanggan = document.getElementById('jabatan_pelanggan');
                    const nipPelanggan = document.getElementById('nip_pelanggan');

            if (!namaPelanggan || !jabatanPelanggan) {
                alert('Nama pelanggan dan jabatan wajib diisi!');
                return;
            }

                    const namaPelangganValue = namaPelanggan.value.trim();
                    const jabatanPelangganValue = jabatanPelanggan.value.trim();
                    const nipPelangganValue = nipPelanggan ? nipPelanggan.value.trim() : '';

                    if (!namaPelangganValue || !jabatanPelangganValue) {
                        alert('Nama pelanggan dan jabatan wajib diisi!');
                        return;
                    }

            if (!confirm(
                    'Apakah Anda yakin ingin menyelesaikan sampling?\n\nSemua draft akan dikonversi menjadi sample dan draft akan dihapus.'
                    )) {
                return;
            }

            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
                    const originalText = submitBtn ? submitBtn.textContent : '';
                    if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
                    }

            // Submit via AJAX
            fetch('{{ route('mobile.sampling.finishDrafts', $permohonan_uji->id_permohonan_uji) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                                nama_pelanggan: namaPelangganValue,
                                jabatan_pelanggan: jabatanPelangganValue,
                                nip_pelanggan: nipPelangganValue
                    })
                })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                .then(data => {
                    if (data.status || data.success) {
                        alert(data.pesan || 'Berhasil menyelesaikan sampling!');
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    window.location.href = '{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}';
                                }
                    } else {
                        alert('Error: ' + (data.pesan || 'Gagal menyelesaikan sampling'));
                                if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                                }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                            alert('Terjadi kesalahan saat menyelesaikan sampling: ' + error.message);
                            if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                            }
                });
        });
            }
        }

        // Setup on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFinishFormHandler);
        } else {
            setupFinishFormHandler();
        }

        // Also setup when SPA page is shown
        if (window.mobileSamplingSPA) {
            const originalShowPage = window.mobileSamplingSPA.showPage;
            window.mobileSamplingSPA.showPage = function(pageId) {
                originalShowPage.call(this, pageId);
                if (pageId === 'draft-list') {
                    setTimeout(setupFinishFormHandler, 100);
                }
            };
        }

        function deleteDraft(draftId, draftName) {
            if (!confirm('Apakah Anda yakin ingin menghapus draft "' + draftName +
                    '"?\n\nTindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            const url =
                '{{ route('mobile.sampling.draft.delete', ['id' => $permohonan_uji->id_permohonan_uji, 'draft_id' => ':draft_id']) }}'
                .replace(':draft_id', draftId);

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        alert(data.pesan);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.pesan);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus draft');
                });
        }

        function deleteDraftGroup(groupId, count) {
            if (!confirm('Apakah Anda yakin ingin menghapus seluruh grup ini (' + count +
                    ' sample)?\n\nTindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            const url =
                '{{ route('mobile.sampling.draftGroup.delete', ['id' => $permohonan_uji->id_permohonan_uji, 'group_id' => ':group_id']) }}'
                .replace(':group_id', groupId);

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        alert(data.pesan);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.pesan);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus grup draft');
                });
        }
    </script>
</body>

</html>
