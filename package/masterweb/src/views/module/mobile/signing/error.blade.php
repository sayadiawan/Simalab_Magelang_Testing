<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f7fb;
            margin: 0;
            padding: 24px
        }

        .card {
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            padding: 24px;
            text-align: center
        }

        .btn {
            display: inline-block;
            padding: 12px 16px;
            border-radius: 10px;
            background: #6c63ff;
            color: #fff;
            text-decoration: none;
            margin-top: 12px
        }
    </style>
</head>

<body>
    <div class="card">
        <div style="font-size:48px">⚠️</div>
        <h3 style="margin:10px 0 6px">Terjadi kesalahan</h3>
        <p style="color:#666">{{ $message ?? 'Data tidak ditemukan' }}</p>
        <a class="btn" href="{{ route('mobile.signing.home') }}">Kembali</a>
    </div>
</body>

</html>
