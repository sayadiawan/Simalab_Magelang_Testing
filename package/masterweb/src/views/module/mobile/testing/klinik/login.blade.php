<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Petugas Pengujian Klinik</title>
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
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 20px;
            padding: 20px;
        }

        .header-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 35px;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .permohonan-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
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
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            -webkit-appearance: none;
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

        .help-text {
            text-align: center;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('/assets/public/images/logo_magelang_mini.png') }}" alt="Logo Magelang" style="max-width: 100px; height: auto; margin-bottom: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            <div class="header-icon">
                🏥
            </div>
            <h1>PENGUJIAN KLINIK</h1>
            <p>Laboratorium SIMLAB<br>Lingkungan pengujian</p>
        </div>

        <div class="card">
            <div class="permohonan-info">
                <h3>📋 Informasi Permohonan</h3>
                <div class="info-item">
                    <label>No. Register:</label>
                    <span><strong>{{ $permohonan->getDisplayNoregister() }}</strong></span>
                </div>
                <div class="info-item">
                    <label>Nama Pasien:</label>
                    <span>{{ $permohonan->pasien->nama_pasien ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Register:</label>
                    <span>{{ \Carbon\Carbon::parse($permohonan->tglregister_permohonan_uji_klinik)->format('d/m/Y') }}</span>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">
                    <span>⚠️</span>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('mobile.testing.klinik.doLogin', $id) }}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Masukkan username" value="{{ old('username') }}" required autofocus
                        autocomplete="nope" readonly onfocus="this.removeAttribute('readonly');">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan password" required autocomplete="new-password" readonly
                        onfocus="this.removeAttribute('readonly');">
                    <div style="margin-top: 8px;">
                        <label
                            style="display: flex; align-items: center; cursor: pointer; font-size: 13px; color: #5a5a5a;">
                            <input type="checkbox" id="showPassword"
                                onchange="togglePassword()" style="margin-right: 6px; cursor: pointer;">
                            <span>Tampilkan Password</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>🔐</span>
                    <span>LOGIN & MULAI PENGUJIAN</span>
                </button>
            </form>

            <div class="help-text">
                <strong>💡 Petunjuk:</strong><br>
                Gunakan username dan password akun petugas Anda.<br>
                Hanya untuk petugas dengan level ANLS, SOLK (pengambil sampel klinik), atau ADMIN.
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var showPasswordCheckbox = document.getElementById('showPassword');
            if (passwordInput && showPasswordCheckbox) {
                passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
            }
        }
    </script>
</body>

</html>

