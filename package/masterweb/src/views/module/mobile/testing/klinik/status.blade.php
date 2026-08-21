<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Status Pengujian - Klinik</title>
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

        .step-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0b3a5c;
        }

        .step-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .step-name {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            flex: 1;
        }

        .step-status {
            font-size: 24px;
        }

        .step-details {
            font-size: 13px;
            color: #666;
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
            text-decoration: none;
            margin-bottom: 15px;
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

        .btn-info {
            background: #17a2b8;
            color: white;
            width: auto;
            padding: 8px 16px;
            font-size: 14px;
            margin-left: 10px;
        }

        .btn-info:hover {
            background: #138496;
            color: white;
        }

        .step-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 STATUS PENGUJIAN</h1>
            <p>Laboratorium Klinik</p>
        </div>

        <div class="card">
            <div class="card-title">
                <span>📋</span>
                <span>Informasi Permohonan</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Register:</span>
                <span class="info-value">{{ $permohonan->getDisplayNoregister() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Pasien:</span>
                <span class="info-value">{{ $permohonan->pasien->nama_pasien ?? '-' }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-title">
                <span>📝</span>
                <span>Status Tahapan</span>
            </div>

            @php
                $step7 = $verification_activities->get(7); // Penerima Sampel
                $step2 = $verification_activities->get(2); // Pengolah Sampel
                $step3 = $verification_activities->get(3); // Pemeriksa Sampel
                $step4 = $verification_activities->get(4); // Verifikasi
            @endphp

            <!-- Step 1: Penerimaan Sampel -->
            <div class="step-card">
                <div class="step-header">
                    <div class="step-name">1. Penerimaan Sampel</div>
                    <div class="step-status">
                        @if ($step7 && $step7->is_done == 1)
                            ✅
                        @else
                            ⏳
                        @endif
                    </div>
                </div>
                @if ($step7 && $step7->is_done == 1)
                    <div class="step-details">
                        <div>Waktu: {{ \Carbon\Carbon::parse($step7->start_date)->format('H:i') }}</div>
                        <div>Petugas: {{ $step7->nama_petugas }}</div>
                    </div>
                    <div class="step-actions">
                        <a href="{{ route('mobile.testing.klinik.penerimaan', $id) }}" class="btn btn-info">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                    </div>
                @else
                    <div class="step-details">Belum selesai</div>
                @endif
            </div>

            <!-- Step 2: Pengolah Sampel -->
            <div class="step-card">
                <div class="step-header">
                    <div class="step-name">2. Pengolah Sampel</div>
                    <div class="step-status">
                        @if ($step2 && $step2->is_done == 1)
                            ✅
                        @else
                            ⏳
                        @endif
                    </div>
                </div>
                @if ($step2 && $step2->is_done == 1)
                    <div class="step-details">
                        <div>Waktu: {{ \Carbon\Carbon::parse($step2->start_date)->format('H:i') }}</div>
                        <div>Petugas: {{ $step2->nama_petugas }}</div>
                    </div>
                    <div class="step-actions">
                        <a href="{{ route('mobile.testing.klinik.pengolah', $id) }}" class="btn btn-info">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                    </div>
                @else
                    <div class="step-details">Belum selesai</div>
                @endif
            </div>

            <!-- Step 3: Pemeriksa Sampel -->
            <div class="step-card">
                <div class="step-header">
                    <div class="step-name">3. Pemeriksa Sampel</div>
                    <div class="step-status">
                        @if ($step3 && $step3->is_done == 1)
                            ✅
                        @else
                            ⏳
                        @endif
                    </div>
                </div>
                @if ($step3 && $step3->is_done == 1)
                    <div class="step-details">
                        <div>Waktu: {{ \Carbon\Carbon::parse($step3->start_date)->format('H:i') }}</div>
                        <div>Petugas: {{ $step3->nama_petugas }}</div>
                    </div>
                    <div class="step-actions">
                        <a href="{{ route('mobile.testing.klinik.pemeriksa', $id) }}" class="btn btn-info">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                    </div>
                @else
                    <div class="step-details">Belum selesai</div>
                @endif
            </div>

            <!-- Step 4: Verifikasi -->
            <div class="step-card">
                <div class="step-header">
                    <div class="step-name">4. Verifikasi</div>
                    <div class="step-status">
                        @if ($step4 && $step4->is_done == 1)
                            ✅
                        @else
                            ⏳
                        @endif
                    </div>
                </div>
                @if ($step4 && $step4->is_done == 1)
                    <div class="step-details">
                        <div>Waktu: {{ \Carbon\Carbon::parse($step4->start_date)->format('H:i') }}</div>
                        <div>Petugas: {{ $step4->nama_petugas }}</div>
                    </div>
                    <div class="step-actions">
                        <a href="{{ route('mobile.testing.klinik.verifikasi', $id) }}" class="btn btn-info">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                    </div>
                @else
                    <div class="step-details">Belum selesai</div>
                @endif
            </div>
        </div>

        <a href="{{ route('mobile.testing.klinik.home') }}" class="btn btn-secondary">
            <span>🏠</span>
            <span>Kembali ke Home</span>
        </a>
    </div>
</body>

</html>

