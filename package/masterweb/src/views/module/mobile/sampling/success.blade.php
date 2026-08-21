<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengambilan Sampel Berhasil</title>

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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        h1 {
            font-size: 24px;
            color: #28a745;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item label {
            font-size: 13px;
            color: #666;
        }

        .info-item span {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #0b3a5c;
            border: 2px solid #0b3a5c;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .footer-text {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        @if(isset($backUrl))
        <div style="background: rgba(255, 255, 255, 0.95); padding: 12px 20px; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-size: 14px;">
            <a href="{{ $backUrl }}" style="color: #0b3a5c; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                <span>←</span>
                <span>Kembali</span>
            </a>
        </div>
        @endif
        
        <div class="success-card">
            <div class="success-icon">
                ✓
            </div>

            <h1>Pengambilan Sampel Berhasil!</h1>
            <p class="subtitle">Data sampel telah tersimpan di sistem</p>

            <div class="info-box">
                <div class="info-item">
                    <label>Pelanggan:</label>
                    <span>{{ $permohonan_uji->customer->name_customer ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Total Sampel:</label>
                    <span>{{ count($permohonan_uji->samples) }} Sampel</span>
                </div>
                <div class="info-item">
                    <label>Tanggal & Waktu:</label>
                    <span>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <a href="{{ route('mobile.sampling.form', $permohonan_uji->id_permohonan_uji) }}" class="btn btn-primary"
                style="display: block; text-decoration: none; color: white;">
                ➕ Tambah Sampel Lagi
            </a>

            <form action="{{ route('mobile.sampling.logout', $permohonan_uji->id_permohonan_uji) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    ✓ Selesai & Logout
                </button>
            </form>

            <div class="footer-text">
                Terima kasih telah menggunakan sistem<br>
                Laboratorium Kesehatan Daerah SIMLAB
            </div>
        </div>
    </div>
</body>

</html>
