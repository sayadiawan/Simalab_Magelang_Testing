<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error - Sampling Klinik</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 50px;
        }

        .card h1 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }

        .card p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="error-icon">
                ⚠️
            </div>
            <h1>Terjadi Kesalahan</h1>
            <p>{{ $message ?? 'Terjadi kesalahan yang tidak diketahui. Silakan coba lagi.' }}</p>

            <a href="{{ route('mobile.sampling.klinik.home') }}" class="btn btn-primary">
                <span>🏠</span>
                <span>Kembali ke Halaman Utama</span>
            </a>
        </div>
    </div>
</body>

</html>
