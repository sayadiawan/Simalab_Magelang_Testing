<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan</title>
    <!-- Google Fonts diaktifkan kembali (butuh koneksi internet) -->
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
            background: radial-gradient(1000px 600px at 10% 10%, #eef5ff 0%, #f7fbff 35%, #ffffff 100%);
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
            background: #e8f1ff;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #1d4ed8;
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
            color: #1d4ed8;
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
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(37, 99, 235, .25);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #0f172a;
        }

        .tips {
            margin-top: 22px;
            font-size: 12px;
            color: #64748b;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(37, 99, 235, .35);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
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
    <!-- Optional CSP-safe inline SVG icons -->
</head>

<body>
    <main class="card" role="main" aria-labelledby="title-404">
        <div class="header">
            <div class="brand">LAB</div>
            <div class="title">
                <div id="title-404" class="code">404</div>
                <div class="text">Halaman tidak ditemukan</div>
            </div>
        </div>
        <p class="desc">
            Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan. Periksa kembali URL atau kembali ke
            beranda.
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
        <p class="tips">Jika masalah berlanjut, hubungi admin laboratorium.</p>
    </main>
</body>

</html>
