<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mobile Testing - Scan Sample</title>
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

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .scan-icon {
            text-align: center;
            font-size: 60px;
            margin-bottom: 15px;
        }

        h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            margin-bottom: 15px;
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
            margin-bottom: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: #17a2b8;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
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

        .help-text ul {
            margin-left: 20px;
            margin-top: 8px;
            text-align: left;
        }

        .help-text li {
            margin-bottom: 5px;
        }

        #scanner-section {
            display: none;
            margin-top: 15px;
        }

        #qr-video {
            width: 100%;
            height: auto;
            border-radius: 12px;
            background: #000;
        }

        .qr-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid #0b3a5c;
            border-radius: 12px;
            pointer-events: none;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
            font-size: 14px;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #ddd;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .logout-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>

<body>
    <div class="container">
        @if (isset($is_authenticated) && $is_authenticated)
            <form method="POST" action="{{ route('mobile.testing.logout') }}"
                style="position: absolute; top: 20px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
        @endif

        <div class="header">
            <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Logo Magelang" style="max-width: 100px; height: auto; margin-bottom: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            <div class="header-icon">
                🔬
            </div>
            <h1>PENGUJIAN SAMPLE</h1>
            <p>Laboratorium SIMLAB<br>Lingkungan pengujian</p>
        </div>

        <div class="card">
            @if (session('error'))
                <div class="alert alert-danger">
                    <span>⚠️</span>
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <span>✓</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="scan-icon">📱</div>
            <h2>Scan QR Code atau Input ID Sample</h2>
            @if (isset($is_authenticated) && $is_authenticated)
                <div class="alert alert-success">
                    <span>✓</span>
                    <span>Anda sudah login. Silakan scan QR code atau input ID sample untuk langsung ke form
                        pengujian.</span>
                </div>
            @else
                <p>Silakan scan QR code atau masukkan ID sample untuk memulai proses pengujian.</p>
            @endif

            <form method="POST" action="{{ route('mobile.testing.inputId') }}" style="margin-top: 16px;">
                @csrf
                <label for="id_sample" style="display:block;margin-bottom:8px;font-weight:600;text-align:left;">ID
                    Sample</label>
                <input id="id_sample" name="id_sample" class="form-control" placeholder="Masukkan ID sample"
                    value="{{ old('id_sample') }}" required autocomplete="off">
                <button type="submit" class="btn btn-primary"><span>🔍</span><span>Cari & Lanjutkan</span></button>
            </form>

            <div class="divider">ATAU</div>

            <div id="scanner-section" style="display:none;">
                <div style="position: relative;">
                    <video id="qr-video" autoplay playsinline></video>
                    <canvas id="qr-canvas" style="display: none;"></canvas>
                    <div class="qr-overlay"></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" id="switch-camera" class="btn btn-secondary">🔄 Ganti Kamera</button>
                    <button type="button" id="stop-scanner" class="btn btn-danger">🛑 Stop Scanner</button>
                </div>
            </div>
            <button type="button" id="start-scanner" class="btn btn-primary">📷 Buka QR Scanner</button>

            <div class="help-text">
                <strong>💡 Petunjuk:</strong>
                <ul>
                    <li>Klik "Buka QR Scanner" untuk mengaktifkan kamera</li>
                    <li>Arahkan kamera ke QR code pada label sample</li>
                    <li>Atau gunakan aplikasi QR scanner di ponsel Anda</li>
                    <li>QR code akan otomatis terdeteksi dan Anda akan diarahkan ke form pengujian (jika sudah login)
                        atau halaman login</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- QR Code Scanner Library - Using jsQR with manual getUserMedia -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        let stream = null;
        let scanning = false;
        let facingMode = 'environment'; // 'user' for front camera, 'environment' for back camera
        const video = document.getElementById('qr-video');
        const canvas = document.getElementById('qr-canvas');
        const ctx = canvas.getContext('2d');

        document.getElementById("start-scanner").onclick = () => {
            startScanner();
        };

        document.getElementById("stop-scanner").onclick = () => {
            stopScanner();
        };

        document.getElementById("switch-camera").onclick = () => {
            switchCamera();
        };

        async function startScanner() {
            try {
                const constraints = {
                    video: {
                        facingMode: facingMode
                    }
                };

                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                video.setAttribute('playsinline', true);
                video.play();

                document.getElementById('scanner-section').style.display = 'block';
                document.getElementById('start-scanner').style.display = 'none';
                scanning = true;
                scanLoop();
            } catch (err) {
                console.error("Error accessing camera:", err);
                alert("Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.");
            }
        }

        function switchCamera() {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            stopScanner();
            setTimeout(() => {
                startScanner();
            }, 100);
        }

        function scanLoop() {
            if (!scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(img.data, img.width, img.height, {
                    inversionAttempts: "dontInvert"
                });

                if (code) {
                    scanning = false;
                    stopScanner();
                    handleScanResult(code.data);
                    return;
                }
            }

            requestAnimationFrame(scanLoop);
        }

        function stopScanner() {
            scanning = false;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.pause();
            document.getElementById('scanner-section').style.display = 'none';
            document.getElementById('start-scanner').style.display = 'block';
        }

        function handleScanResult(decodedText) {
            // Extract ID from URL or use as-is
            let id_sample = null;
            try {
                const url = new URL(decodedText);
                const parts = url.pathname.split('/').filter(Boolean);
                const idx = parts.indexOf('scan');
                if (idx !== -1 && parts[idx + 1]) {
                    id_sample = parts[idx + 1];
                }
            } catch (e) {
                // Fallback: treat whole text as ID
                id_sample = decodedText;
            }

            if (id_sample) {
                window.location.href = '{{ url('/mobile/testing/scan') }}/' + id_sample;
            }
        }
    </script>
</body>

</html>
