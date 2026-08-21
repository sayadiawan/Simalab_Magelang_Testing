<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tanda Tangan Surat Persetujuan (Klinik)</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 16px
        }

        .wrap {
            max-width: 680px;
            margin: 0 auto
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            padding: 18px
        }

        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px
        }

        .title {
            font-size: 18px;
            font-weight: 700
        }

        .sig-box {
            background: #fafafa;
            border: 1px dashed #ccc;
            border-radius: 10px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-top: 8px
        }

        canvas {
            width: 100%;
            height: 180px
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 14px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer
        }

        .btn-primary {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: #fff
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #ddd
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px
        }

        @media(min-width:580px) {
            .grid {
                grid-template-columns: 1fr 1fr
            }
        }

        .row {
            margin-top: 12px
        }

        .muted {
            color: #666;
            font-size: 13px
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <div style="font-size:26px">📝</div>
                <div class="title">Tanda Tangan Surat Persetujuan</div>
            </div>
            <div class="muted">Pasien: <b>{{ $permohonan->pasien->nama_pasien ?? '-' }}</b></div>
            <div class="muted">ID: <code>{{ $permohonan->id_permohonan_uji_klinik }}</code></div>

            <div class="row grid">
                <div>
                    <div class="muted">Tanda Tangan Pasien/Wali</div>
                    <div class="sig-box"><canvas id="sigPatient"></canvas></div>
                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button class="btn btn-outline" id="clearPatient">Bersihkan</button>
                    </div>
                </div>
                <div>
                    <div class="muted">Tanda Tangan Petugas</div>
                    <div class="sig-box"><canvas id="sigOfficer"></canvas></div>
                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button class="btn btn-outline" id="clearOfficer">Bersihkan</button>
                    </div>
                </div>
            </div>

            <div class="row" style="display:flex;gap:10px;margin-top:16px">
                <button class="btn btn-primary" id="saveBtn">Simpan Tanda Tangan</button>
                <a class="btn btn-outline" href="{{ route('mobile.signing.home') }}">Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        function setupPad(canvasId) {
            const canvas = document.getElementById(canvasId);
            const pad = new SignaturePad(canvas, {
                minWidth: 0.8,
                maxWidth: 2.4
            });

            function resize() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * ratio;
                canvas.height = 180 * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                pad.clear();
            }
            window.addEventListener('resize', resize);
            setTimeout(resize, 200);
            return pad;
        }
        const padPatient = setupPad('sigPatient');
        const padOfficer = setupPad('sigOfficer');
        document.getElementById('clearPatient').onclick = () => padPatient.clear();
        document.getElementById('clearOfficer').onclick = () => padOfficer.clear();
        document.getElementById('saveBtn').onclick = function() {
            if (padPatient.isEmpty()) {
                alert('Tanda tangan pasien/wali wajib diisi');
                return;
            }
            const payload = {
                sig_patient: padPatient.toDataURL(),
                sig_officer: padOfficer.isEmpty() ? null : padOfficer.toDataURL(),
                _token: '{{ csrf_token() }}'
            };
            fetch('{{ route('mobile.signing.klinik.consent.save', $permohonan->id_permohonan_uji_klinik) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json()).then(j => {
                    if (j.status) {
                        alert('Tanda tangan tersimpan');
                        window.location.href =
                            '{{ route('mobile.signing.klinik.select', $permohonan->id_permohonan_uji_klinik) }}';
                    } else {
                        alert(j.message || 'Gagal menyimpan');
                    }
                })
                .catch(() => alert('Gagal menyimpan'));
        };
    </script>
</body>

</html>
