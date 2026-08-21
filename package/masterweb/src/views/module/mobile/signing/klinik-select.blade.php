<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pilih Dokumen Tanda Tangan (Klinik)</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column
        }

        .wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 520px;
            margin: 0 auto;
            width: 100%;
            justify-content: center
        }

        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .2);
            padding: 22px;
            text-align: center
        }

        .header {
            color: #fff;
            text-align: center;
            margin-bottom: 18px
        }

        .header .icon {
            width: 76px;
            height: 76px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 38px
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: #fff
        }

        .btn-disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-top: 6px
        }

        @media(min-width:560px) {
            .grid {
                grid-template-columns: 1fr 1fr
            }
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 8px
        }

        .done {
            background: #d4edda;
            color: #155724
        }

        .todo {
            background: #fff3cd;
            color: #856404
        }

        .muted {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="header">
            <div class="icon">🖊️</div>
            <h2>Pilih Dokumen Tanda Tangan</h2>
            <div style="opacity:.95">Pasien: <b>{{ $permohonan->pasien->nama_pasien ?? '-' }}</b></div>
            <div style="opacity:.95">ID: <code>{{ $permohonan->id_permohonan_uji_klinik }}</code></div>
        </div>
        <div class="card">
            <div class="muted">Silakan pilih salah satu dokumen untuk ditandatangani. Jika sudah ditandatangani, tombol
                akan nonaktif.</div>
            <div class="grid">
                <a class="btn {{ $nota_done ? 'btn-disabled' : 'btn-primary' }}"
                    href="{{ $nota_done ? 'javascript:void(0)' : route('mobile.signing.klinik.nota', $permohonan->id_permohonan_uji_klinik) }}"
                    {{ $nota_done ? 'aria-disabled=true' : '' }}>
                    🧾 TTD Nota
                    <span class="badge {{ $nota_done ? 'done' : 'todo' }}">{{ $nota_done ? 'Sudah' : 'Belum' }}</span>
                </a>
                <a class="btn {{ $consent_done ? 'btn-disabled' : 'btn-primary' }}"
                    href="{{ $consent_done ? 'javascript:void(0)' : route('mobile.signing.klinik.consent', $permohonan->id_permohonan_uji_klinik) }}"
                    {{ $consent_done ? 'aria-disabled=true' : '' }}>
                    📝 TTD Persetujuan
                    <span
                        class="badge {{ $consent_done ? 'done' : 'todo' }}">{{ $consent_done ? 'Sudah' : 'Belum' }}</span>
                </a>
            </div>
            <div style="margin-top:14px">
                <a class="btn" style="background:#e9ecef;color:#333"
                    href="{{ route('mobile.signing.home') }}">Kembali</a>
            </div>
        </div>
    </div>
</body>

</html>
