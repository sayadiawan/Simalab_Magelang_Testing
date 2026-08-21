<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pengolah Sampel - Klinik</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔬 PENGOLAH SAMPEL</h1>
            <p>Step 2 dari 4</p>
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
            <div class="step-item active">2. Pengolah</div>
            <div class="step-item">3. Pemeriksa</div>
            <div class="step-item">4. Verifikasi</div>
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

        <form id="formPengolah" method="POST" action="{{ route('mobile.testing.klinik.storePengolah', $id) }}">
            @csrf
            <div class="card">
                <div class="card-title">
                    <span>⏰</span>
                    <span>Waktu Pengolahan</span>
                </div>
                <div class="form-group">
                    <label for="waktu">Waktu <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="waktu" name="waktu" 
                        value="{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}" 
                        required placeholder="HH:mm">
                </div>
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
            document.getElementById('formPengolah').addEventListener('submit', function(e) {
                const waktu = document.getElementById('waktu').value;
                if (!waktu || !/^\d{1,2}:\d{2}$/.test(waktu)) {
                    e.preventDefault();
                    alert('Mohon masukkan waktu yang valid (format: HH:mm)');
                    return false;
                }
            });
        });
    </script>
</body>

</html>

