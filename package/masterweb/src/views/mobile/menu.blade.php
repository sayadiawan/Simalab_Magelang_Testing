<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Menu - SIMLAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Manrope", sans-serif;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container { max-width: 560px; margin: 0 auto; }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 28px;
            padding-top: 28px;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .header img {
            max-width: 108px;
            height: auto;
            margin-bottom: 14px;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.22));
        }

        .header h1 {
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 0.95rem;
            opacity: 0.9;
            max-width: 320px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 18px;
            box-shadow: 0 14px 32px rgba(6, 40, 63, 0.18);
        }

        .menu-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #06283f;
            margin-bottom: 16px;
            text-align: center;
        }

        .option-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .option-btn {
            padding: 16px 14px;
            border: 2px solid #d7e4e1;
            border-radius: 14px;
            background: #fff;
            color: #5c6d75;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            font-family: inherit;
        }

        .option-btn i {
            display: block;
            font-size: 1.35rem;
            margin-bottom: 8px;
            color: #0d8f7f;
        }

        .option-btn:hover {
            border-color: #0d8f7f;
            color: #0b3a5c;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(11, 58, 92, 0.12);
        }

        .option-btn.active {
            border-color: transparent;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            box-shadow: 0 8px 18px rgba(11, 58, 92, 0.25);
        }

        .option-btn.active i { color: #fff; }

        .menu-items { display: none; }
        .menu-items.active { display: block; }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 16px;
            margin-bottom: 10px;
            background: #f5f8f7;
            border-radius: 14px;
            text-decoration: none;
            color: #1a2b33;
            font-weight: 700;
            transition: all 0.25s ease;
            border: 1px solid #e7f4f2;
            border-left: 4px solid transparent;
        }

        .menu-item i {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e7f4f2;
            color: #0d8f7f;
            margin-right: 0;
        }

        .menu-item:hover {
            background: #e7f4f2;
            border-left-color: #0d8f7f;
            transform: translateX(4px);
            color: #0b3a5c;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            background: rgba(255,255,255,0.95);
            color: #0b3a5c;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-top: 8px;
            box-shadow: 0 6px 16px rgba(6, 40, 63, 0.16);
            transition: all 0.25s ease;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            color: #06283f;
        }

        @media (max-width: 480px) {
            .header h1 { font-size: 1.4rem; }
            .option-buttons { grid-template-columns: 1fr; }
            .menu-card { padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-badge"><i class="fas fa-mobile-alt"></i> Akses Mobile</div>
            <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="SIMLAB">
            <h1>SIMLAB Mobile</h1>
            <p>Pilih layanan laboratorium untuk melanjutkan pekerjaan di lapangan.</p>
        </div>

        <div class="menu-card">
            <h2 class="menu-title">Pilih Layanan</h2>
            <div class="option-buttons">
                <button class="option-btn active" data-type="klinik" type="button">
                    <i class="fas fa-hospital"></i>
                    Klinik
                </button>
                <button class="option-btn" data-type="kesmas" type="button">
                    <i class="fas fa-users"></i>
                    Kesmas
                </button>
            </div>

            <div class="menu-items active" id="menu-klinik">
                <a href="{{ route('mobile.signing.home') }}" class="menu-item">
                    <i class="fas fa-signature"></i>
                    <span>Tandatangan Registrasi</span>
                </a>
                <a href="{{ route('mobile.sampling.klinik.home') }}" class="menu-item">
                    <i class="fas fa-vial"></i>
                    <span>Pengambil Sample</span>
                </a>
                <a href="{{ route('mobile.testing.klinik.home') }}" class="menu-item">
                    <i class="fas fa-microscope"></i>
                    <span>Analis</span>
                </a>
                <a href="{{ route('mobile.dokter.home') }}" class="menu-item">
                    <i class="fas fa-user-md"></i>
                    <span>Dokter</span>
                </a>
            </div>

            <div class="menu-items" id="menu-kesmas">
                <a href="{{ route('mobile.signing.home') }}" class="menu-item">
                    <i class="fas fa-signature"></i>
                    <span>Registrasi</span>
                </a>
                <a href="{{ route('mobile.sampling.home') }}" class="menu-item">
                    <i class="fas fa-vial"></i>
                    <span>Pengambil Sample</span>
                </a>
                <a href="{{ route('mobile.testing.home') }}" class="menu-item">
                    <i class="fas fa-microscope"></i>
                    <span>Analis</span>
                </a>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionButtons = document.querySelectorAll('.option-btn');
            const menuItems = document.querySelectorAll('.menu-items');

            optionButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    optionButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    menuItems.forEach(menu => menu.classList.remove('active'));
                    document.getElementById('menu-' + type).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
