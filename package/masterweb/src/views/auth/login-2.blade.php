@php
    $videoDir = 'assets/admin/video';
    $heroVideoPath = null;
    $heroPosterPath = null;
    $heroVideoCandidates = [
        'hero-lab.mp4',
        'mixkit-scientist-mixing-liquids-in-a-laboratory-4719-hd-ready.mp4',
        'mixkit-laboratory-worker-looking-at-a-test-tube-21454-hd-ready.mp4',
        'mixkit-woman-working-with-samples-in-laboratory-21457-hd-ready.mp4',
        'mixkit-drops-filling-a-lab-tube-17456-hd-ready.mp4',
    ];
    foreach ($heroVideoCandidates as $candidate) {
        $relative = $videoDir . '/' . $candidate;
        if (is_file(public_path($relative))) {
            $heroVideoPath = $relative;
            break;
        }
    }
    $hasHeroVideo = !empty($heroVideoPath);
    foreach (['hero-poster.jpg', 'poster-pengujian.jpg'] as $posterCandidate) {
        $relative = $videoDir . '/' . $posterCandidate;
        if (is_file(public_path($relative))) {
            $heroPosterPath = $relative;
            break;
        }
    }
    $hasHeroPoster = !empty($heroPosterPath);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke SimaLab — lingkungan pengujian Sistem Informasi Laboratorium.">
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/logo/logo_magelang_mini.png') }}">
    <script>document.documentElement.className += ' js';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Masuk — SimaLab</title>
    <style>
        :root {
            --navy: #0b3a5c;
            --navy-deep: #06283f;
            --teal: #0d8f7f;
            --teal-bright: #16a892;
            --teal-soft: #e7f4f2;
            --sand: #f5f8f7;
            --ink: #1c2c33;
            --muted: #5c6d75;
            --line: #dbe5e3;
            --white: #ffffff;
            --danger: #c0392b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
        }

        body {
            font-family: "Manrope", sans-serif;
            color: var(--ink);
            background: var(--sand);
            -webkit-font-smoothing: antialiased;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        }

        /* —— Panel merek (kiri) —— */
        .brand-panel {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: linear-gradient(155deg, var(--navy-deep) 0%, var(--navy) 48%, #0a5a58 100%);
            color: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(28px, 4vw, 44px);
            min-height: 100vh;
        }

        .brand-media {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .brand-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1) scale(1.08);
            filter: grayscale(0.88) contrast(1.05) brightness(0.55);
            animation: brandKenBurns 22s ease-in-out infinite alternate;
            will-change: transform;
        }

        .brand-video.is-playing {
            animation: brandKenBurns 28s ease-in-out infinite alternate;
        }

        .brand-orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(48px);
            opacity: 0.45;
            animation: brandDrift 16s ease-in-out infinite;
        }

        .brand-orb--a {
            width: min(42vw, 360px);
            height: min(42vw, 360px);
            top: 12%;
            left: -8%;
            background: rgba(22, 168, 146, 0.55);
        }

        .brand-orb--b {
            width: min(36vw, 300px);
            height: min(36vw, 300px);
            bottom: 8%;
            right: -6%;
            background: rgba(11, 58, 92, 0.65);
            animation-duration: 20s;
            animation-direction: reverse;
        }

        @keyframes brandKenBurns {
            0% { transform: scaleX(-1) scale(1.06) translate3d(0, 0, 0); }
            100% { transform: scaleX(-1) scale(1.14) translate3d(-1.5%, -1.2%, 0); }
        }

        @keyframes brandDrift {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(18px, -14px, 0); }
        }

        .brand-tint {
            position: absolute;
            inset: 0;
            background: linear-gradient(150deg, #0b3a5c 0%, #0e7a76 60%, #16a892 100%);
            mix-blend-mode: color;
            opacity: 0.9;
        }

        .brand-veil {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, rgba(6, 40, 63, 0.88) 0%, rgba(6, 40, 63, 0.55) 48%, rgba(10, 90, 88, 0.72) 100%),
                radial-gradient(ellipse 70% 60% at 20% 80%, rgba(22, 168, 146, 0.28), transparent 70%);
        }

        .brand-grid {
            position: absolute;
            inset: 0;
            z-index: 1;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: radial-gradient(ellipse 80% 70% at 40% 40%, #000 20%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 40% 40%, #000 20%, transparent 100%);
            pointer-events: none;
        }

        .brand-top,
        .brand-copy,
        .brand-foot {
            position: relative;
            z-index: 2;
        }

        .brand-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            transition: color 0.2s, transform 0.2s;
        }

        .brand-home img {
            width: 30px;
            height: auto;
            display: block;
            filter: brightness(1.05);
        }

        .brand-home:hover {
            color: #fff;
            transform: translateX(-2px);
        }

        .brand-copy {
            max-width: 26ch;
            padding: 40px 0 28px;
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #7fd4c8;
            margin-bottom: 18px;
        }

        .brand-eyebrow::before {
            content: "";
            width: 22px;
            height: 2px;
            background: #7fd4c8;
        }

        .brand-copy h1 {
            font-size: clamp(2.4rem, 5vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 0.95;
            margin-bottom: 16px;
        }

        .brand-copy h1 span {
            color: #2ad3b6;
        }

        .brand-copy p {
            font-size: 15px;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.78);
            max-width: 34ch;
        }

        .brand-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 28px;
        }

        .brand-pills span {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.08);
            padding: 7px 12px;
            backdrop-filter: blur(6px);
        }

        .brand-foot {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.55);
        }

        .brand-foot img {
            height: 34px;
            width: auto;
            opacity: 0.9;
        }

        /* —— Panel form (kanan) —— */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(28px, 5vw, 48px) clamp(22px, 4vw, 48px);
            background:
                radial-gradient(ellipse 50% 40% at 90% 10%, rgba(22, 168, 146, 0.12), transparent 60%),
                radial-gradient(ellipse 40% 35% at 10% 90%, rgba(11, 58, 92, 0.08), transparent 55%),
                var(--sand);
        }

        .form-card {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border: 1px solid var(--line);
            box-shadow: 0 22px 50px rgba(6, 40, 63, 0.08);
            padding: clamp(28px, 4vw, 40px) clamp(24px, 3.5vw, 36px);
            position: relative;
        }

        .form-card::before {
            content: "";
            position: absolute;
            top: -1px;
            left: -1px;
            width: 42px;
            height: 42px;
            border-top: 2px solid var(--teal);
            border-left: 2px solid var(--teal);
        }

        .form-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 22px;
        }

        .form-logo img {
            width: 220px;
            max-width: 100%;
            height: auto;
            display: block;
        }

        .form-head {
            text-align: center;
            margin-bottom: 28px;
        }

        .form-head h2 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--navy-deep);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .form-head p {
            font-size: 13.5px;
            color: var(--muted);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 7px;
            letter-spacing: 0.02em;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 13px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1px solid var(--line);
            background: var(--white);
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .form-control.no-icon {
            padding-left: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(13, 143, 127, 0.14);
        }

        .form-control::placeholder {
            color: #9aadb4;
        }

        .show-pass {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 12.5px;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }

        .show-pass input {
            accent-color: var(--teal);
            cursor: pointer;
        }

        .captcha-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .captcha-row img {
            height: 56px;
            border-radius: 6px;
            background: var(--teal-soft);
            padding: 4px;
            cursor: pointer;
            border: 1px solid var(--line);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .captcha-row img:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(11, 58, 92, 0.1);
        }

        .btn-refresh {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: var(--white);
            color: var(--navy);
            font-size: 12.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s, background 0.2s, transform 0.2s;
        }

        .btn-refresh:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: var(--teal-soft);
            transform: translateY(-1px);
        }

        .btn-signin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 8px;
            padding: 13px 20px;
            border: none;
            background: var(--navy);
            color: var(--white);
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(11, 58, 92, 0.22);
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s, background 0.2s;
        }

        .btn-signin:hover {
            background: var(--navy-deep);
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 14px 28px rgba(11, 58, 92, 0.28);
        }

        .btn-signin:active {
            transform: translateY(0) scale(0.99);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .mobile-login-link {
            display: none;
            margin-top: 14px;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border: 1px solid var(--line);
            background: var(--white);
            color: var(--navy);
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }

        .mobile-login-link:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: var(--teal-soft);
        }

        @media (max-width: 1024px), (hover: none) and (pointer: coarse) {
            .mobile-login-link {
                display: inline-flex;
            }
        }

        .form-meta {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .form-meta .bsre {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 11.5px;
            color: var(--muted);
            max-width: 18ch;
            line-height: 1.35;
        }

        .form-meta .bsre img {
            height: 36px;
            width: auto;
            display: block;
        }

        .back-link {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--teal);
            text-decoration: none;
            transition: color 0.2s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-link:hover {
            color: var(--navy);
            transform: translateX(-2px);
        }

        /* Reveal ringan */
        .js .reveal {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .js .reveal.is-visible {
            opacity: 1;
            transform: none;
        }

        .js .reveal[data-delay="1"] { transition-delay: 0.08s; }
        .js .reveal[data-delay="2"] { transition-delay: 0.16s; }
        .js .reveal[data-delay="3"] { transition-delay: 0.24s; }

        @media (prefers-reduced-motion: reduce) {
            .js .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .brand-video,
            .brand-orb {
                animation: none !important;
            }

            .btn-signin:hover,
            .btn-refresh:hover,
            .captcha-row img:hover {
                transform: none;
            }
        }

        @media (max-width: 960px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                min-height: auto;
                padding: 22px 22px 28px;
            }

            .brand-media {
                display: none;
            }

            .brand-copy {
                padding: 22px 0 8px;
                max-width: none;
            }

            .brand-copy h1 {
                font-size: 2rem;
            }

            .brand-copy p {
                font-size: 14px;
            }

            .brand-foot {
                display: none;
            }

            .form-panel {
                padding: 0 0 36px;
                align-items: flex-start;
            }

            .form-card {
                max-width: none;
                border: none;
                border-top: 1px solid var(--line);
                box-shadow: none;
                border-radius: 0;
            }

            .form-card::before {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .brand-pills {
                display: none;
            }

            .form-card {
                padding: 26px 18px 32px;
            }
        }
    </style>
</head>

<body>
    <div class="login-shell">
        <aside class="brand-panel" aria-hidden="false">
            @if ($hasHeroVideo)
                <div class="brand-media" aria-hidden="true">
                    <span class="brand-orb brand-orb--a"></span>
                    <span class="brand-orb brand-orb--b"></span>
                    <video class="brand-video" id="brandVideo" muted loop playsinline autoplay
                        @if ($hasHeroPoster) poster="{{ asset($heroPosterPath) }}" @endif>
                        <source src="{{ asset($heroVideoPath) }}" type="video/mp4">
                    </video>
                    <span class="brand-tint"></span>
                    <span class="brand-veil"></span>
                </div>
            @else
                <div class="brand-media" aria-hidden="true">
                    <span class="brand-orb brand-orb--a"></span>
                    <span class="brand-orb brand-orb--b"></span>
                    <span class="brand-tint"></span>
                    <span class="brand-veil"></span>
                </div>
            @endif
            <span class="brand-grid" aria-hidden="true"></span>

            <div class="brand-top reveal">
                <a class="brand-home" href="{{ url('/') }}">
                    <img src="{{ asset('/assets/public/images/logo_magelang_mini.png') }}" alt="">
                    <span>SimaLab</span>
                </a>
            </div>

            <div class="brand-copy reveal" data-delay="1">
                <p class="brand-eyebrow">Lingkungan pengujian</p>
                <h1>Sima<span>Lab</span></h1>
                <p>Kelola permohonan uji, sampel, verifikasi hasil, dan administrasi dalam satu alur kerja — untuk uji fitur dan demo.</p>
                <div class="brand-pills">
                    <span>Testing</span>
                    <span>Demo</span>
                    <span>TTE BSrE</span>
                </div>
            </div>

            <div class="brand-foot reveal" data-delay="2">
                <img src="{{ asset('assets/admin/images/logo/logo-bsre-2.png') }}" alt="BSrE">
                <span>Didukung tanda tangan elektronik tersertifikasi BSrE</span>
            </div>
        </aside>

        <main class="form-panel">
            <div class="form-card reveal" data-delay="1">
                <div class="form-logo">
                    <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Logo SimaLab">
                </div>
                <div class="form-head">
                    <h2>Masuk ke sistem</h2>
                    <p>Gunakan akun pengujian Anda untuk melanjutkan</p>
                </div>

                <form id="loginForm" method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
                    @csrf
                    <input type="text" name="fakeusernameremembered" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
                    <input type="password" name="fakepasswordremembered" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
                    <input type="text" name="hp_field" style="display:none" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <div class="input-wrap">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <input type="text" id="username" class="form-control" name="username"
                                placeholder="Masukkan username" required autocomplete="nope" readonly
                                onfocus="this.removeAttribute('readonly');">
                        </div>
                        @if ($errors->has('username'))
                            <div class="invalid-feedback">{{ $errors->first('username') }}</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="Masukkan password" required autocomplete="new-password" readonly
                                onfocus="this.removeAttribute('readonly');">
                        </div>
                        @if ($errors->has('password'))
                            <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                        @endif
                        <label class="show-pass">
                            <input type="checkbox" id="showPassword" onchange="togglePassword()">
                            <span>Tampilkan password</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="captcha">Kode keamanan</label>
                        <div class="captcha-row">
                            <img src="{{ route('captcha.generate') }}?t={{ microtime(true) }}" alt="CAPTCHA"
                                id="captchaImg" title="Klik untuk memperbarui kode"
                                onclick="refreshCaptcha()">
                            <button type="button" class="btn-refresh" onclick="refreshCaptcha()">
                                <i class="fas fa-rotate-right" aria-hidden="true"></i>
                                Refresh
                            </button>
                        </div>
                        <input type="text" class="form-control no-icon" name="captcha" id="captcha"
                            placeholder="Masukkan kode pada gambar" required autocomplete="off"
                            onblur="this.value=this.value.trim().toUpperCase();">
                        @if (session('error'))
                            <div class="invalid-feedback">{{ session('error') }}</div>
                        @endif
                    </div>

                    <button type="submit" class="btn-signin"
                        onclick="document.getElementById('captcha').value=document.getElementById('captcha').value.trim().toUpperCase();">
                        Masuk
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>

                    <a class="mobile-login-link" href="{{ route('mobile.menu') }}">
                        <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                        Versi Mobile
                    </a>
                </form>

                <div class="form-meta">
                    <div class="bsre">
                        <img src="{{ asset('assets/admin/images/logo/logo-bsre-2.png') }}" alt="BSrE">
                        <span>Tanda tangan elektronik tersertifikasi</span>
                    </div>
                    <a class="back-link" href="{{ url('/') }}">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Beranda
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var showPasswordCheckbox = document.getElementById('showPassword');
            if (passwordInput && showPasswordCheckbox) {
                passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
            }
        }

        function refreshCaptcha() {
            document.getElementById('captchaImg').src = '{{ route('captcha.generate') }}?t=' + Date.now();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('loginForm');
            if (form) {
                setTimeout(function () {
                    var inputs = form.querySelectorAll('input[type="text"], input[type="password"]');
                    inputs.forEach(function (input) {
                        if (input.name !== 'fakeusernameremembered' && input.name !== 'fakepasswordremembered') {
                            input.setAttribute('autocomplete', 'nope');
                            input.setAttribute('data-lpignore', 'true');
                            input.setAttribute('data-form-type', 'other');
                        }
                    });
                }, 100);
            }

            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            document.querySelectorAll('.reveal').forEach(function (el) {
                el.classList.add('is-visible');
            });

            var video = document.getElementById('brandVideo');
            if (video && !reducedMotion && window.innerWidth > 960) {
                video.preload = 'auto';

                function markPlaying() {
                    video.classList.add('is-playing');
                }

                function tryPlay() {
                    var playPromise = video.play();
                    if (playPromise && typeof playPromise.then === 'function') {
                        playPromise.then(markPlaying).catch(function () {
                            document.addEventListener('click', function retryOnce() {
                                var retry = video.play();
                                if (retry && typeof retry.then === 'function') {
                                    retry.then(markPlaying).catch(function () {});
                                }
                            }, { once: true });
                        });
                    }
                }

                if (video.readyState >= 2) {
                    tryPlay();
                } else {
                    video.addEventListener('loadeddata', tryPlay, { once: true });
                    video.addEventListener('canplay', tryPlay, { once: true });
                }

                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                var resumed = video.play();
                                if (resumed && typeof resumed.then === 'function') {
                                    resumed.then(markPlaying).catch(function () {});
                                }
                            } else {
                                video.pause();
                            }
                        });
                    }, { threshold: 0.12 });
                    observer.observe(video);
                }
            }
        });
    </script>
</body>

</html>
