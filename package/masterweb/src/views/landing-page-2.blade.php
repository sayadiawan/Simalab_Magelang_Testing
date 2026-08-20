<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Sistem Informasi Laboratorium Kesehatan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #52c77d 0%, #3fb86f 100%);
            padding: 25px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 5px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 100px;
        }

        .logo {
            width: 70px;
            height: 90px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .logo-text {
            color: white;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .main-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 80px 100px;
            max-width: 1200px;
            margin: 0 auto;
            gap: 80px;
        }

        .left-section {
            flex: 1;
        }

        .badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #2196f3;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 500;
            margin-bottom: 20px;
            margin-left: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.3;
            font-weight: 600;
        }

        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .login-button {
            text-decoration: none;
            background-color: #2196f3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .login-button:hover {
            background-color: #1976d2;
            box-shadow: 0 6px 16px rgba(33, 150, 243, 0.4);
        }

        .mobile-button {
            text-decoration: none;
            background-color: #52c77d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(82, 199, 125, 0.3);
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .mobile-button:hover {
            background-color: #3fb86f;
            box-shadow: 0 6px 16px rgba(82, 199, 125, 0.4);
        }

        .mobile-button i {
            font-size: 18px;
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .mobile-button {
                margin-left: 0;
                margin-top: 10px;
                width: 100%;
                justify-content: center;
            }

            .button-group {
                flex-direction: column;
            }
        }

        .right-section {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .image-container {
            position: relative;
            max-width: 500px;
            width: 100%;
        }

        .lab-image {
            width: 100%;
            height: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transform: perspective(1000px) rotateY(-30deg);
        }


        @media (max-width: 968px) {
            .main-content {
                flex-direction: column;
                padding: 50px 30px;
                gap: 50px;
            }

            .title {
                font-size: 32px;
            }

            .header {
                padding: 20px 30px;
            }
        }

        @media (max-width: 480px) {
            .title {
                font-size: 28px;
            }

            .main-content {
                padding: 40px 20px;
            }

            .header {
                padding: 15px 20px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-container">
            <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Laboratorium Kesehatan" class="logo">
            <span class="logo-text">SIMLAB</span>
        </div>
    </header>

    <main class="main-content">
        <div class="left-section">
            <span class="badge">v1.0.0</span>
            <h6 class="title">Sistem Informasi Laboratorium Kesehatan</h6>
            <p class="subtitle">Sistem Informasi Laboratorium Kesehatan</p>
            <div class="button-group">
                <a class="login-button" href="{{ route('login-form') }}">
                    Login Sistem
                </a>
                <a class="mobile-button" href="{{ route('mobile.menu') }}" id="mobile-menu-btn" style="display: none;">
                    <i class="fas fa-mobile-alt"></i>
                    Versi Mobile
                </a>
            </div>
        </div>

        <div class="right-section">
            <div class="image-container">
                <img src="{{ asset('/assets/public/images/labkes_kab_magelang.jpg') }}" alt="Laboratorium Kesehatan"
                    class="lab-image">
            </div>
        </div>
    </main>
    <footer style="margin-top: 200px;background-color: #48BF76; color: white; padding: 60px 100px 40px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px;">
                <!-- SIMLAB Section -->
                <div>
                    <h4 style="font-weight: bold; margin-bottom: 20px; font-size: 20px;">SIMLAB</h4>
                    <p style="text-align: justify; line-height: 1.8; font-size: 14px; margin-bottom: 20px;">
                        Sistem Informasi Laboratorium Kesehatan adalah aplikasi untuk mengelola data dan layanan laboratorium kesehatan di Kabupaten Magelang secara terkomputerisasi dan terintegrasi.
                    </p>
                    <div style="margin-top: 20px; background-color: white; padding: 10px; display: inline-block; border-radius: 5px;">
                        <img src="{{ asset('assets/admin/images/logo/logo-bsre-2.png') }}" alt="BSrE Logo" style="height: 50px;">
                    </div>
                    <p style="margin-top: 15px; font-size: 13px; line-height: 1.6;">
                        Aplikasi ini didukung oleh Balai Sertifikasi Elektronik (BSrE) dan Badan Siber dan Sandi Negara (BSSN).
                    </p>
                </div>

                <!-- LINK TERKAIT Section -->
                <div>
                    <h4 style="font-weight: bold; margin-bottom: 20px; font-size: 20px;">LINK TERKAIT</h4>
                    <ul style="list-style: none; padding: 0; line-height: 2.2;">
                        <li><a href="https://magelangkab.go.id" target="_blank" style="color: white; text-decoration: none; font-size: 14px;">Home</a></li>
                        <li><a href="https://dinkes.magelangkab.go.id" target="_blank" style="color: white; text-decoration: none; font-size: 14px;">Website Dinkes Kab. Magelang</a></li>
                        <li><a href="https://www.kemkes.go.id" target="_blank" style="color: white; text-decoration: none; font-size: 14px;">Kementerian Kesehatan RI</a></li>
                        <li><a href="https://yankes.kemkes.go.id" target="_blank" style="color: white; text-decoration: none; font-size: 14px;">Sistem Informasi Yankes</a></li>
                    </ul>
                </div>

                <!-- KONTAK KAMI Section -->
                <div>
                    <h4 style="font-weight: bold; margin-bottom: 20px; font-size: 20px;">KONTAK KAMI</h4>
                    <p style="line-height: 1.8; font-size: 14px; margin-bottom: 15px;">
                        Laboratorium Kesehatan<br>
                        Dinas Kesehatan Kabupaten Magelang<br>
                        Jawa Tengah
                    </p>
                    <p style="line-height: 2; font-size: 14px;">
                        <strong>Telepon:</strong> (0293) 788249<br>
                        <strong>Email:</strong> labkes@magelangkab.go.id
                    </p>
                    <div style="margin-top: 20px;">
                        <a href="#" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); border-radius: 50%; color: white; margin-right: 10px; text-decoration: none; font-size: 18px; transition: background-color 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
                            </svg>
                        </a>
                        <a href="#" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); border-radius: 50%; color: white; margin-right: 10px; text-decoration: none; font-size: 18px; transition: background-color 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                            </svg>
                        </a>
                        <a href="#" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); border-radius: 50%; color: white; margin-right: 10px; text-decoration: none; font-size: 18px; transition: background-color 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/>
                            </svg>
                        </a>
                        <a href="#" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); border-radius: 50%; color: white; text-decoration: none; font-size: 18px; transition: background-color 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.2); text-align: center; font-size: 13px;">
                <p>&copy; 2025 Sistem Informasi Laboratorium Kesehatan Kabupaten Magelang. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        footer a:hover {
            opacity: 0.8;
        }

        footer a[style*="background-color"]:hover {
            background-color: rgba(255,255,255,0.2) !important;
        }

        @media (max-width: 968px) {
            footer {
                padding: 40px 30px 30px !important;
            }

            footer > div > div {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
            }
        }

        @media (max-width: 480px) {
            footer {
                padding: 30px 20px 20px !important;
            }
        }
    </style>

    <script>
        // Function to detect mobile/tablet devices
        function isMobileOrTablet() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            
            // Check for mobile devices
            const isMobile = /android|webos|iphone|ipod|blackberry|iemobile|opera mini/i.test(userAgent.toLowerCase());
            
            // Check for tablet devices
            const isTablet = /ipad|android(?!.*mobile)|tablet|playbook|silk/i.test(userAgent.toLowerCase());
            
            // Check screen width (fallback method)
            const isSmallScreen = window.innerWidth <= 1024;
            
            return isMobile || isTablet || isSmallScreen;
        }

        // Show mobile menu button if device is mobile or tablet
        document.addEventListener('DOMContentLoaded', function() {
            if (isMobileOrTablet()) {
                const mobileBtn = document.getElementById('mobile-menu-btn');
                if (mobileBtn) {
                    mobileBtn.style.display = 'inline-flex';
                }
            }
        });
    </script>
</body>

</html>
