<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan Server</title>
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
            background: radial-gradient(1000px 600px at 10% 10%, #fff7ed 0%, #fffaf3 35%, #ffffff 100%);
            font-family: "Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, Helvetica, sans-serif;
            color: #111827;
        }

        .card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(2, 12, 27, 0.08);
            padding: 40px 28px;
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
            background: #fff1e6;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #ea580c;
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
            color: #ea580c;
            letter-spacing: 1px;
        }

        .text {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }

        .desc {
            margin-top: 8px;
            color: #374151;
            line-height: 1.7;
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
            padding: 12px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .08s ease, box-shadow .2s ease, background .2s ease;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(234, 88, 12, .25);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #111827;
        }

        .tips {
            margin-top: 22px;
            font-size: 12px;
            color: #6b7280;
        }

        .tips code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 6px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(234, 88, 12, .35);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .icon-refresh {
            display: inline-block;
        }
    </style>
    <script>
        function goBack() {
            if (history.length > 1) history.back();
            else window.location.href = '/';
        }

        function refreshPage() {
            window.location.reload();
        }
    </script>
</head>

<body>
    <main class="card" role="main" aria-labelledby="title-500">
        <div class="header">
            <div class="brand">LAB</div>
            <div class="title">
                <div id="title-500" class="code">500</div>
                <div class="text">Terjadi kesalahan pada server</div>
            </div>
        </div>
        <p class="desc">
            Maaf, terjadi gangguan pada sistem. Tim kami telah mencatat kejadian ini. Coba kembali beberapa saat lagi,
            atau kembali ke beranda.
        </p>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="refreshPage()" aria-label="Refresh halaman">
                <span class="icon-refresh">🔄</span>
                <span>Refresh</span>
            </button>
            <a href="/" class="btn btn-primary" aria-label="Kembali ke beranda">
                <span>⬅</span>
                <span>Ke Beranda</span>
            </a>
            <button type="button" class="btn btn-secondary" onclick="goBack()"
                aria-label="Kembali ke halaman sebelumnya">
                ⤺ Kembali
            </button>
        </div>
        <p class="tips">
            Jika Anda admin: periksa log di <code>storage/logs/laravel.log</code> atau jalankan <code>php artisan
                security:audit</code>.
        </p>
    </main>
</body>

</html>
