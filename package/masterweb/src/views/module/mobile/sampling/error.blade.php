<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Error - Sampling</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .error-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 24px;
            color: #dc3545;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            text-decoration: none;
            display: block;
        }

        .btn:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-card">
            <div class="error-icon">
                ⚠️
            </div>

            <h1>Terjadi Kesalahan</h1>
            <p class="message">{{ $message ?? 'Maaf, terjadi kesalahan. Silakan coba lagi atau hubungi administrator.' }}
            </p>

            <a href="javascript:history.back()" class="btn">
                ← Kembali
            </a>
        </div>
    </div>
</body>

</html>
