<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Scan QR Code - Sampling Klinik</title>

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
            justify-content: center;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            padding: 20px;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
        }

        .header h1 {
            font-size: 24px;
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
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .scan-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
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
            text-decoration: none;
            margin-top: 15px;
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
            gap: 8px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .help-text {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
            text-align: left;
        }

        .help-text strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            text-align: center;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0b3a5c;
        }

        #reader {
            width: 100%;
            margin-bottom: 15px;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
        }

        #reader video {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        #reader__camera_selection {
            margin-bottom: 10px;
        }

        #reader__scan_region {
            border: 2px solid #0b3a5c !important;
            border-radius: 10px;
        }

        #reader__dashboard_section {
            margin-top: 10px;
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
            <h1>SAMPLING KLINIK</h1>
            <p>Laboratorium SIMLAB<br>Lingkungan pengujian</p>
        </div>

        <div class="card">
            @if (session('error'))
                <div class="alert alert-danger">
                    <span>⚠️</span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="scan-icon">📱</div>
            <h2>Scan QR Code atau Input ID</h2>
            @if (isset($is_authenticated) && $is_authenticated)
                <div class="alert"
                    style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 20px;">
                    <span>✓</span>
                    <span>Anda sudah login. Silakan scan QR code atau input ID untuk langsung ke form sampling.</span>
                </div>
            @else
                <p>Silakan scan QR code atau masukkan ID permohonan uji klinik untuk memulai proses sampling.</p>
            @endif

            <!-- Form Input ID Permohonan -->
            <form method="POST" action="{{ route('mobile.sampling.klinik.inputId') }}" style="margin-top: 20px;">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label for="id_permohonan"
                        style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; text-align: left;">
                        ID Permohonan Uji Klinik
                    </label>
                    <input type="text" id="id_permohonan" name="id_permohonan" class="form-control"
                        placeholder="Masukkan ID permohonan uji klinik" value="{{ old('id_permohonan') }}" required
                        autocomplete="off"
                        style="width: 100%; padding: 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 15px; text-align: center; transition: border-color 0.3s;">
                </div>
                <button type="submit" class="btn btn-primary">
                    <span>🔍</span>
                    <span>Cari & Lanjutkan</span>
                </button>
            </form>

            <div style="margin: 20px 0; text-align: center; color: #999; font-size: 14px;">
                <span style="display: inline-block; padding: 0 15px;">ATAU</span>
            </div>

            <!-- QR Code Scanner -->
            <div id="scanner-section" style="display: none;">
                <div id="reader"
                    style="width: 100%; margin-bottom: 15px; border-radius: 10px; overflow: hidden; position: relative;">
                    <video id="qr-video" autoplay playsinline
                        style="width: 100%; height: auto; border-radius: 10px;"></video>
                    <canvas id="qr-canvas" style="display: none;"></canvas>
                    <div class="qr-overlay"
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 250px; height: 250px; border: 2px solid #00ff00; border-radius: 10px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" id="switch-camera" class="btn"
                        style="background:#17a2b8;color:#fff;flex:1;">
                        <span>🔄</span>
                        <span>Ganti Kamera</span>
                    </button>
                    <button type="button" id="stop-scanner" class="btn"
                        style="background: #dc3545; color: white; flex:1;">
                        <span>🛑</span>
                        <span>Stop Scanner</span>
                    </button>
                </div>
            </div>

            <button type="button" id="start-scanner" class="btn btn-primary" style="margin-top: 10px;">
                <span>📷</span>
                <span>Buka QR Scanner</span>
            </button>

            <div class="help-text" style="margin-top: 20px;">
                <strong>💡 Petunjuk:</strong>
                <ul style="margin-left: 20px; margin-top: 8px;">
                    <li>Klik "Buka QR Scanner" untuk mengaktifkan kamera</li>
                    <li>Arahkan kamera ke QR code pada formulir</li>
                    <li>Atau gunakan aplikasi QR scanner di ponsel Anda</li>
                    <li>QR code akan otomatis terdeteksi dan Anda akan diarahkan ke form sampling (jika sudah login)
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
        let currentFacingMode = 'environment'; // 'environment' = back, 'user' = front

        const video = document.getElementById("qr-video");
        const canvas = document.getElementById("qr-canvas");
        const ctx = canvas.getContext("2d");

        // Function to handle scanned QR code
        function handleScanResult(decodedText) {
            // Extract ID from URL (format: /mobile/sampling-klinik/scan/{id})
            let id_permohonan = null;
            try {
                const url = new URL(decodedText);
                const parts = url.pathname.split('/').filter(Boolean);
                const idx = parts.indexOf('scan');
                if (idx !== -1 && parts[idx + 1]) {
                    id_permohonan = parts[idx + 1];
                }
            } catch (e) {
                // Fallback: treat whole text as ID
                id_permohonan = decodedText;
            }

            if (id_permohonan) {
                window.location.href = '{{ url('/mobile/sampling-klinik/scan') }}/' + id_permohonan;
            }
        }

        // Function to start camera with specific facing mode
        async function startCamera(facingMode) {
            // Stop existing stream first
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            currentFacingMode = facingMode;
            scanning = false; // Pause scanning while switching

            let constraints = {
                video: {
                    facingMode: facingMode
                }
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (e1) {
                console.log('Camera failed, trying any camera:', e1);
                // Fallback to any camera
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                } catch (e2) {
                    console.log(e2);
                    alert("Tidak bisa mengakses kamera.");
                    throw e2;
                }
            }

            video.srcObject = stream;
            video.setAttribute("playsinline", true);
            await video.play();

            scanning = true;
            requestAnimationFrame(scanLoop);
        }

        // Function to switch camera
        async function switchCamera() {
            if (!scanning && !stream) {
                alert("Kamera belum aktif. Silakan mulai scanner terlebih dahulu.");
                return;
            }

            // Toggle between back and front camera
            const newFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

            try {
                await startCamera(newFacingMode);
            } catch (err) {
                console.error('Error switching camera:', err);
                alert('Gagal mengganti kamera. Silakan coba lagi.');
            }
        }

        // Loop scan
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

        // Function to stop scanner and cleanup
        function stopScanner() {
            scanning = false;
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.pause();
        }

        // Detect mobile device
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Handle camera error
        function handleCameraError(err) {
            console.error('Error accessing camera:', err);

            // Handle different error types
            let errorMessage = 'Gagal mengakses kamera.';
            let showRetry = true;

            const errMsg = err && err.message ? err.message.toLowerCase() : '';
            const errName = err && err.name ? err.name.toLowerCase() : '';

            if (errMsg.includes('permission') || errMsg.includes('notallowed') ||
                errName.includes('notallowed') || errName.includes('permissiondenied')
            ) {
                errorMessage =
                    '⚠️ Akses kamera ditolak.\n\n' +
                    'Silakan izinkan akses kamera melalui pengaturan browser.';
            } else if (errMsg.includes('notfound') || errMsg.includes('no camera')) {
                errorMessage = 'Kamera tidak ditemukan di perangkat ini.';
                showRetry = false;
            } else if (errMsg.includes('notreadable') || errMsg.includes('in use')) {
                errorMessage =
                    'Kamera sedang digunakan oleh aplikasi lain.\n' +
                    'Silakan tutup aplikasi lain yang menggunakan kamera dan coba lagi.';
            } else {
                errorMessage =
                    'Error: ' + (err.message || err.toString()) + '\n\n' +
                    'Silakan coba lagi atau gunakan aplikasi QR scanner.';
            }

            // Show error and offer retry
            const userChoice = confirm(
                errorMessage +
                (showRetry ?
                    '\n\nKlik OK untuk mencoba lagi, atau Cancel untuk membatalkan.' :
                    '\n\nKlik OK untuk menutup.')
            );

            if (showRetry && userChoice) {
                // Reset and retry
                document.getElementById('scanner-section').style.display = 'none';
                document.getElementById('start-scanner').style.display = 'block';
                scanning = false;

                setTimeout(() => {
                    document.getElementById('start-scanner').click();
                }, 500);
            } else {
                document.getElementById('scanner-section').style.display = 'none';
                document.getElementById('start-scanner').style.display = 'block';
                scanning = false;
            }
        }

        // Start scanner
        document.getElementById('start-scanner').addEventListener('click', async function() {
            if (scanning) return;

            // Check if camera API is available
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert(
                    'Kamera tidak didukung di browser ini. Silakan gunakan aplikasi QR scanner atau input ID manual.'
                );
                return;
            }

            // Check if HTTPS (required for camera access on mobile)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !==
                '127.0.0.1') {
                alert(
                    'Akses kamera memerlukan koneksi HTTPS. Silakan gunakan aplikasi QR scanner atau input ID manual.'
                );
                return;
            }

            // Show scanner section
            document.getElementById('scanner-section').style.display = 'block';
            document.getElementById('start-scanner').style.display = 'none';

            try {
                // Start with back camera
                await startCamera('environment');
            } catch (err) {
                handleCameraError(err);
            }
        });

        const errMsg = err3 && err3.message ? err3.message.toLowerCase() : '';
        const errName = err3 && err3.name ? err3.name.toLowerCase() : '';

        if (errMsg.includes('permission') || errMsg.includes('notallowed') ||
            errName.includes('notallowed') || errName.includes('permissiondenied')
        ) {
            errorMessage =
                '⚠️ Izin akses kamera diperlukan.\n\n' +
                'Browser akan menampilkan prompt untuk mengizinkan akses kamera saat Anda mencoba lagi.\n\n' +
                'Jika prompt tidak muncul, izinkan melalui:\n' +
                '1. Klik ikon 🔒 atau ⓘ di address bar\n' +
                '2. Pilih "Izinkan" untuk Kamera\n' +
                '3. Klik OK di bawah untuk mencoba lagi';
        } else if (errMsg.includes('notfound') || errMsg.includes('no camera')) {
            errorMessage = 'Kamera tidak ditemukan di perangkat ini.';
            showRetry = false;
        } else if (errMsg.includes('notreadable') || errMsg.includes('in use')) {
            errorMessage =
                'Kamera sedang digunakan oleh aplikasi lain.\n' +
                'Silakan tutup aplikasi lain yang menggunakan kamera dan coba lagi.';
        } else {
            errorMessage =
                'Error: ' + (err3.message || err3.toString()) + '\n\n' +
                'Silakan coba lagi atau gunakan aplikasi QR scanner.';
        }

        // Show error and offer retry
        const userChoice = confirm(
            errorMessage +
            (showRetry ?
                '\n\nKlik OK untuk mencoba lagi, atau Cancel untuk membatalkan.' :
                '\n\nKlik OK untuk menutup.')
        );

        if (showRetry && userChoice) {
            // Reset and retry
            document.getElementById('scanner-section').style.display = 'none';
            document.getElementById('start-scanner').style.display = 'block';
            isScanning = false;

            setTimeout(() => {
                document.getElementById('start-scanner').click();
            }, 500);
        } else {
            document.getElementById('scanner-section').style.display = 'none';
            document.getElementById('start-scanner').style.display = 'block';
            isScanning = false;
        }

        // Stop scanner
        document.getElementById('switch-camera').addEventListener('click', function() {
            switchCamera();
        });

        document.getElementById('stop-scanner').addEventListener('click', function() {
            stopScanner();
            document.getElementById('scanner-section').style.display = 'none';
            document.getElementById('start-scanner').style.display = 'block';
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopScanner();
        });
    </script>
</body>

</html>
