<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Petugas Sampling Klinik</title>

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

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
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

        .icon {
            display: inline-block;
            margin-right: 5px;
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
            <h1>PENGAMBILAN SAMPEL KLINIK</h1>
            <p>Laboratorium SIMLAB<br>Lingkungan pengujian</p>
        </div>

        <div class="card">
            @if ($permohonan_uji_klinik)
                <div class="permohonan-info">
                    <h3>📋 Informasi Permohonan</h3>
                    <div class="info-item">
                        <label>No. Register:</label>
                        <span><strong>{{ $permohonan_uji_klinik->getDisplayNoregister() }}</strong></span>
                    </div>
                    <div class="info-item">
                        <label>Nama Pasien:</label>
                        <span>{{ $permohonan_uji_klinik->pasien->nama_pasien ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal:</label>
                        <span>{{ \Carbon\Carbon::parse($permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('d/m/Y') }}</span>
                    </div>
                </div>
            @endif

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

            <form action="{{ route('mobile.sampling.klinik.doLogin') }}" method="POST" autocomplete="off" novalidate
                id="mobileKlinikLoginForm">
                @csrf
                <!-- Dummy fields to trick Firefox autofill -->
                <input type="text" name="fakeusernameremembered" style="position:absolute;left:-9999px;"
                    tabindex="-1" autocomplete="off">
                <input type="password" name="fakepasswordremembered" style="position:absolute;left:-9999px;"
                    tabindex="-1" autocomplete="off">
                <!-- Honeypot -->
                <input type="text" name="hp_field" style="display:none" tabindex="-1" autocomplete="off">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Masukkan username" value="{{ old('username') }}" required autofocus
                        autocomplete="nope" readonly onfocus="this.removeAttribute('readonly');"
                        style="background-color: white;">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan password" required autocomplete="new-password" readonly
                        onfocus="this.removeAttribute('readonly');" style="background-color: white;">
                    <div style="margin-top: 8px;">
                        <label
                            style="display: flex; align-items: center; cursor: pointer; font-size: 13px; color: #5a5a5a;">
                            <input type="checkbox" id="showPasswordMobileKlinik" onchange="togglePasswordMobileKlinik()"
                                style="margin-right: 6px; cursor: pointer;">
                            <span>Tampilkan Password</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="captcha">Kode Keamanan</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="{{ route('captcha.generate') }}?t={{ microtime(true) }}" alt="CAPTCHA"
                            id="captchaImgMobileKlinik"
                            style="height:64px; border-radius:8px; background:#eef2ff; padding:4px; cursor:pointer;"
                            title="Klik untuk refresh">
                        <button type="button"
                            onclick="document.getElementById('captchaImgMobileKlinik').src='{{ route('captcha.generate') }}?t='+(Date.now())"
                            class="btn btn-primary" style="padding:8px 12px;">Refresh</button>
                    </div>
                    <input type="text" class="form-control" id="captcha" name="captcha"
                        placeholder="Masukkan kode pada gambar" required autocomplete="off" style="margin-top:8px;"
                        onblur="this.value=this.value.trim().toUpperCase();">
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>🔐</span>
                    <span>LOGIN & MULAI SAMPLING</span>
                </button>
            </form>

            <div class="help-text">
                <strong>💡 Petunjuk:</strong><br>
                Gunakan username dan password akun petugas Anda.<br>
                Jika lupa, hubungi admin laboratorium.
            </div>
        </div>
    </div>
    <script>
        // Toggle password visibility
        function togglePasswordMobileKlinik() {
            var passwordInput = document.getElementById('password');
            var showPasswordCheckbox = document.getElementById('showPasswordMobileKlinik');
            if (passwordInput && showPasswordCheckbox) {
                passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
            }
        }

        // Additional Firefox autofill prevention
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('mobileKlinikLoginForm');
            if (form) {
                setTimeout(function() {
                    var inputs = form.querySelectorAll('input[type="text"], input[type="password"]');
                    inputs.forEach(function(input) {
                        if (input.name !== 'fakeusernameremembered' && input.name !==
                            'fakepasswordremembered') {
                            input.setAttribute('autocomplete', 'nope');
                            input.setAttribute('data-lpignore', 'true');
                            input.setAttribute('data-form-type', 'other');
                        }
                    });
                }, 100);
            }
        });
    </script>
</body>

</html>
