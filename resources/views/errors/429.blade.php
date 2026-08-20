<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terlalu Banyak Permintaan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(1000px 600px at 10% 10%, #eef6ff 0%, #f8fbff 35%, #ffffff 100%);
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
        .header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .brand {
            width: 44px; height: 44px; border-radius: 12px; background: #eff6ff;
            display: grid; place-items: center; font-weight: 700; color: #2563eb;
        }
        .title { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
        .code { font-size: 40px; font-weight: 700; color: #2563eb; }
        .text { font-size: 20px; font-weight: 600; }
        .desc { margin-top: 8px; color: #334155; line-height: 1.7; }
        .actions { margin-top: 28px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px; border: 0; border-radius: 10px;
            cursor: pointer; padding: 12px 24px; font-weight: 600; text-decoration: none; font-size: 15px;
        }
        .btn-primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; }
        .btn-secondary { background: #f1f5f9; color: #0f172a; }
        .tips { margin-top: 22px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <main class="card" role="main">
        <div class="header">
            <div class="brand">LAB</div>
            <div class="title">
                <div class="code">429</div>
                <div class="text">Terlalu Banyak Permintaan</div>
            </div>
        </div>
        <p class="desc">
            Sistem membatasi jumlah permintaan dalam waktu singkat untuk menjaga kestabilan.
            Silakan tunggu sekitar 1 menit, lalu coba lagi.
        </p>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="window.location.reload()">Coba Lagi</button>
            <a href="/" class="btn btn-secondary">Ke Beranda</a>
        </div>
        <p class="tips">Jika sering muncul, hindari menekan tombol berulang kali dan pastikan koneksi stabil.</p>
    </main>
</body>
</html>
