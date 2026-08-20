<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Petugas Sampling - LABKES Magelang</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.png') }}" />

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            background: white;
            border-radius: 50%;
            padding: 10px;
        }

        .login-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .login-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .input-group {
            position: relative;
        }

        .input-group-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .input-group .form-control {
            padding-left: 45px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .info-box p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        .qr-info {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .qr-info i {
            font-size: 30px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .qr-info p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('assets/admin/images/logo/logo-magelang.png') }}" alt="Logo"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2240%22 fill=%22%23fff%22/%3E%3C/svg%3E'">
                <h3>PETUGAS SAMPLING</h3>
                <p>Laboratorium Kesehatan Daerah<br>Kabupaten Magelang</p>
            </div>

            <div class="login-body">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('sampling.login.submit') }}" method="POST" autocomplete="off" novalidate
                    id="samplingLoginForm">
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
                        <div class="input-group">
                            <i class="mdi mdi-account input-group-icon"></i>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                id="username" name="username" placeholder="Masukkan username Anda"
                                value="{{ old('username') }}" required autofocus autocomplete="nope" readonly
                                onfocus="this.removeAttribute('readonly');" style="background-color: white;">
                        </div>
                        @error('username')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="mdi mdi-lock input-group-icon"></i>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="Masukkan password Anda" required
                                autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                                style="background-color: white;">
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <div style="margin-top: 8px;">
                            <label
                                style="display: flex; align-items: center; cursor: pointer; font-size: 13px; color: #5a5a5a;">
                                <input type="checkbox" id="showPasswordSampling" onchange="togglePasswordSampling()"
                                    style="margin-right: 6px; cursor: pointer;">
                                <span>Tampilkan Password</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="captcha">Kode Keamanan</label>
                        <div class="input-group">
                            <img src="{{ route('captcha.generate') }}?t={{ microtime(true) }}" alt="CAPTCHA"
                                id="captchaImgSampling"
                                style="height:64px; border-radius:8px; background:#eef2ff; padding:4px; cursor:pointer;"
                                title="Klik untuk refresh">
                            <button type="button"
                                onclick="document.getElementById('captchaImgSampling').src='{{ route('captcha.generate') }}?t='+(Date.now())"
                                class="btn btn-primary" style="margin-left:10px;">Refresh</button>
                        </div>
                        <input type="text" class="form-control" id="captcha" name="captcha"
                            placeholder="Masukkan kode pada gambar" required autocomplete="off"
                            style="margin-top:8px;" onblur="this.value=this.value.trim().toUpperCase();">
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="mdi mdi-login mr-2"></i>LOGIN
                    </button>
                </form>

                <div class="qr-info">
                    <i class="mdi mdi-qrcode-scan"></i>
                    <p><strong>Info:</strong> Halaman ini digunakan untuk login petugas sampling.<br>
                        Scan QR Code pada Surat Perintah Sampling untuk mengakses form input sampel.</p>
                </div>

                <div class="info-box">
                    <p><i class="mdi mdi-information"></i> Gunakan akun petugas yang telah terdaftar di sistem.<br>
                        Jika mengalami kendala, hubungi admin laboratorium.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/admin/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/admin/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/admin/js/misc.js') }}"></script>
    <script>
        // Toggle password visibility
        function togglePasswordSampling() {
            var passwordInput = document.getElementById('password');
            var showPasswordCheckbox = document.getElementById('showPasswordSampling');
            if (passwordInput && showPasswordCheckbox) {
                passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
            }
        }

        // Additional Firefox autofill prevention
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('samplingLoginForm');
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
