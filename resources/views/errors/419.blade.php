<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(1000px 600px at 10% 10%, #fff5e6 0%, #fffbf0 35%, #ffffff 100%);
            font-family: "Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, Helvetica, sans-serif;
            color: #0f172a;
        }

        .card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(2, 12, 27, 0.08);
            padding: 40px 28px;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .brand {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff4e6;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #f59e0b;
        }

        .title {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
        }

        .code {
            font-size: 40px;
            font-weight: 700;
            color: #f59e0b;
            letter-spacing: 1px;
        }

        .text {
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
        }

        .desc {
            margin-top: 8px;
            color: #334155;
            line-height: 1.7;
        }

        .countdown {
            margin-top: 16px;
            padding: 12px 16px;
            background: #fff7ed;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            font-size: 14px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .countdown-icon {
            font-size: 18px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            padding: 12px 24px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .08s ease, box-shadow .2s ease, background .2s ease;
            font-size: 15px;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(245, 158, 11, .25);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(245, 158, 11, .35);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .tips {
            margin-top: 22px;
            font-size: 12px;
            color: #64748b;
        }

        .icon-refresh {
            display: inline-block;
            animation: rotate 2s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .btn-primary .icon-refresh {
            animation: rotate 1s linear infinite;
        }
    </style>
    <script>
        var countdownSeconds = 5;
        var countdownInterval = null;

        function startCountdown() {
            var countdownEl = document.getElementById('countdown');
            countdownEl.textContent = 'Halaman akan di-refresh otomatis dalam ' + countdownSeconds + ' detik...';
            
            countdownInterval = setInterval(function() {
                countdownSeconds--;
                if (countdownSeconds > 0) {
                    countdownEl.textContent = 'Halaman akan di-refresh otomatis dalam ' + countdownSeconds + ' detik...';
                } else {
                    clearInterval(countdownInterval);
                    refreshPage();
                }
            }, 1000);
        }

        function refreshPage() {
            // Clear interval jika masih berjalan
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            // Refresh halaman
            window.location.reload();
        }

        function goBack() {
            // Clear countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            if (history.length > 1) {
                history.back();
            } else {
                window.location.href = '/';
            }
        }

        // Start countdown when page loads
        window.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
    </script>
</head>

<body>
    <main class="card" role="main" aria-labelledby="title-419">
        <div class="header">
            <div class="brand">LAB</div>
            <div class="title">
                <div id="title-419" class="code">419</div>
                <div class="text">Session Expired</div>
            </div>
        </div>
        <p class="desc">
            Session Anda telah berakhir karena tidak ada aktivitas dalam waktu yang lama. Untuk melanjutkan, silakan refresh halaman ini.
        </p>
        <div class="countdown" id="countdown">
            <span class="countdown-icon">⏱</span>
            <span>Halaman akan di-refresh otomatis dalam 5 detik...</span>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="refreshPage()" aria-label="Refresh halaman">
                <span class="icon-refresh">🔄</span>
                <span>Refresh Sekarang</span>
            </button>
            <button type="button" class="btn btn-secondary" onclick="goBack()" aria-label="Kembali ke halaman sebelumnya">
                ⤺ Kembali
            </button>
            <a href="/" class="btn btn-secondary" aria-label="Kembali ke beranda">
                <span>⬅</span>
                <span>Ke Beranda</span>
            </a>
        </div>
        <p class="tips">Setelah refresh, Anda akan kembali ke halaman sebelumnya. Pastikan untuk menyimpan pekerjaan Anda secara berkala.</p>
    </main>
</body>

</html>
