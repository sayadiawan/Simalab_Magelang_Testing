<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Menu - SIMLAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            padding-top: 40px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .menu-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .option-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .option-btn {
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: white;
            color: #666;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .option-btn:hover {
            border-color: #2D6BCF;
            color: #2D6BCF;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 107, 207, 0.2);
        }

        .option-btn.active {
            border-color: #2D6BCF;
            background: #2D6BCF;
            color: white;
        }

        .menu-items {
            display: none;
        }

        .menu-items.active {
            display: block;
        }

        .menu-item {
            display: block;
            padding: 18px 20px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .menu-item:hover {
            background: #e9ecef;
            border-left-color: #2D6BCF;
            transform: translateX(5px);
        }

        .menu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 24px;
            background: white;
            color: #2D6BCF;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 24px;
            }

            .option-buttons {
                grid-template-columns: 1fr;
            }

            .menu-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Logo Magelang" style="max-width: 120px; height: auto; margin-bottom: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            <h1><i class="fas fa-mobile-alt"></i> Mobile Menu</h1>
            <p>Pilih jenis layanan yang ingin Anda akses</p>
        </div>

        <div class="menu-card">
            <h2 class="menu-title">Pilih Layanan</h2>
            <div class="option-buttons">
                <button class="option-btn active" data-type="klinik">
                    <i class="fas fa-hospital"></i><br>
                    Klinik
                </button>
                <button class="option-btn" data-type="kesmas">
                    <i class="fas fa-users"></i><br>
                    Kesmas
                </button>
            </div>

            <!-- Menu Klinik -->
            <div class="menu-items active" id="menu-klinik">
                <a href="{{ route('mobile.signing.home') }}" class="menu-item">
                    <i class="fas fa-signature"></i>
                    Tandatangan Registrasi
                </a>
                <a href="{{ route('mobile.sampling.klinik.home') }}" class="menu-item">
                    <i class="fas fa-vial"></i>
                    Pengambil Sample
                </a>
                <a href="{{ route('mobile.testing.klinik.home') }}" class="menu-item">
                    <i class="fas fa-microscope"></i>
                    Analis
                </a>
                <a href="{{ route('mobile.dokter.home') }}" class="menu-item">
                    <i class="fas fa-user-md"></i>
                    Dokter
                </a>
            </div>

            <!-- Menu Kesmas -->
            <div class="menu-items" id="menu-kesmas">
                <a href="{{ route('mobile.signing.home') }}" class="menu-item">
                    <i class="fas fa-signature"></i>
                    Registrasi
                </a>
                <a href="{{ route('mobile.sampling.home') }}" class="menu-item">
                    <i class="fas fa-vial"></i>
                    Pengambil Sample
                </a>
                <a href="{{ route('mobile.testing.home') }}" class="menu-item">
                    <i class="fas fa-microscope"></i>
                    Analis
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
                    
                    // Update active button
                    optionButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide menu items
                    menuItems.forEach(menu => {
                        menu.classList.remove('active');
                    });
                    document.getElementById('menu-' + type).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>

