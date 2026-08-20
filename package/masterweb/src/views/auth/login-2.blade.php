<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMLAB - Sistem Informasi Laboratorium Kesehatan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .page-subtitle {
            font-size: 14px;
            color: #7f8c8d;
        }

        .login-container {
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            max-width: 600px;
            width: 100%;
            padding: 40px 40px;
        }

        .logo {
            width: 100px;
            height: auto;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            margin-bottom: 35px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            color: #5a5a5a;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #2196f3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }

        .form-control::placeholder {
            color: #bdbdbd;
        }

        .btn-signin {
            display: block;
            width: 80px;
            padding: 10px;
            background-color: #366DC7;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px auto 0 auto;
        }

        .btn-signin:hover {
            background-color: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .invalid-feedback {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 25px;
            }

            .title {
                font-size: 18px;
            }

            .subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <h1 class="page-title">Sistem Informasi Laboratorium Kesehatan</h1>
        <p class="page-subtitle">Sign in to your account to continue</p>
    </div>

    <div class="login-container">
        <div class="header-section">
            <div class="logo-container">
                <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Laboratorium Kesehatan"
                    class="logo">
            </div>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
            @csrf
            <!-- Dummy fields to trick Firefox autofill -->
            <input type="text" name="fakeusernameremembered" style="position:absolute;left:-9999px;" tabindex="-1"
                autocomplete="off">
            <input type="password" name="fakepasswordremembered" style="position:absolute;left:-9999px;" tabindex="-1"
                autocomplete="off">
            <!-- Honeypot -->
            <input type="text" name="hp_field" style="display:none" tabindex="-1" autocomplete="off">

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Enter your username" required
                    autocomplete="nope" readonly onfocus="this.removeAttribute('readonly');"
                    style="background-color: white;">
                @if ($errors->has('username'))
                    <div class="invalid-feedback">
                        {{ $errors->first('username') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" id="password" class="form-control" name="password"
                    placeholder="Enter your password" required autocomplete="new-password" readonly
                    onfocus="this.removeAttribute('readonly');" style="background-color: white;">
                @if ($errors->has('password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
                <div style="margin-top: 8px;">
                    <label
                        style="display: flex; align-items: center; cursor: pointer; font-size: 13px; color: #5a5a5a;">
                        <input type="checkbox" id="showPassword" onchange="togglePassword()"
                            style="margin-right: 6px; cursor: pointer;">
                        <span>Tampilkan Password</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Kode Keamanan</label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="{{ route('captcha.generate') }}?t={{ microtime(true) }}" alt="CAPTCHA" id="captchaImg"
                        style="height:64px; border-radius:8px; background:#eef2ff; padding:4px; cursor:pointer;"
                        title="Klik untuk refresh">
                    <button type="button"
                        onclick="document.getElementById('captchaImg').src='{{ route('captcha.generate') }}?t='+(Date.now())"
                        class="btn-signin" style="width:auto;padding:8px 12px;">Refresh</button>
                </div>
                <input type="text" class="form-control" name="captcha" id="captcha"
                    placeholder="Masukkan kode pada gambar" required autocomplete="off" style="margin-top:8px;"
                    onblur="this.value=this.value.trim().toUpperCase();">
                @if (session('error'))
                    <div class="invalid-feedback" style="display:block;">{{ session('error') }}</div>
                @endif
            </div>

            <button type="submit" class="btn-signin"
                onclick="document.getElementById('captcha').value=document.getElementById('captcha').value.trim().toUpperCase();">Sign
                in</button>

            <img src="{{ asset('assets/admin/images/logo/logo-bsre-2.png') }}" alt="BSrE Logo" style="height: 50px; margin-top: 50px; display: block; margin-left: auto; margin-right: auto;">
        </form>
        <script>
            // Toggle password visibility
            function togglePassword() {
                var passwordInput = document.getElementById('password');
                var showPasswordCheckbox = document.getElementById('showPassword');
                if (passwordInput && showPasswordCheckbox) {
                    passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
                }
            }

            // Additional Firefox autofill prevention
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('loginForm');
                if (form) {
                    // Prevent autofill on form load
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
    </div>
</body>

</html>
