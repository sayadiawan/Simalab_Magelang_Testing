<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Success - Sampling Klinik</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: left;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item label {
            font-weight: 600;
            min-width: 120px;
            color: #666;
        }

        .info-item span {
            color: #333;
            flex: 1;
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
            margin-bottom: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e0e0e0;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: #2D6BCF;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="success-icon">
                ✓
            </div>
            <h1>Data Sampling Berhasil Disimpan!</h1>
            <p>Data sampling untuk permohonan uji klinik telah berhasil disimpan ke sistem.</p>

            @if ($permohonan_uji_klinik)
                <div class="info-box">
                    <div class="info-item">
                        <label>No. Register:</label>
                        <span><strong>{{ $permohonan_uji_klinik->getDisplayNoregister() }}</strong></span>
                    </div>
                    <div class="info-item">
                        <label>Nama Pasien:</label>
                        <span>{{ $permohonan_uji_klinik->pasien->nama_pasien ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal:</label>
                        <span>{{ \Carbon\Carbon::parse($permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('d/m/Y') }}</span>
                    </div>
                    @if (isset($status_sampling))
                        <div class="info-item">
                            <label>Status Sampling:</label>
                            <span><strong>{{ $status_sampling }}</strong></span>
                        </div>
                    @endif
                </div>
            @endif

            @if (isset($status_sampling) && $status_sampling === 'Berhasil' && !$is_done)
                <!-- Tombol Selesai hanya muncul jika status Berhasil dan belum selesai -->
                <div
                    style="background: #fff3cd; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">JAM
                            SAMPLING</label>
                        <input type="time" class="form-control" id="jam_sampling_success"
                            value="{{ $jam_sampling ?? \Carbon\Carbon::now()->format('H:i') }}"
                            style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 15px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">NAMA
                            PENGAMBIL SAMPLE</label>
                        <select class="form-control" id="nama_petugas_success"
                            style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 15px;">
                            <option value="">Pilih Pengambil Sample</option>
                            @foreach ($list_petugas as $petugas)
                                <option value="{{ $petugas }}"
                                    {{ ($verification_sample->nama_petugas ?? '') == $petugas ? 'selected' : '' }}>
                                    {{ $petugas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" id="btnSelesaiSuccess"
                        style="width: 100%; padding: 12px;">
                        <span>✓</span> Selesai
                    </button>
                </div>
            @endif

            @if ($is_done)
                <div
                    style="background: #d4edda; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                    <p style="margin: 0; color: #155724; font-weight: 600;">✓ Pengambilan sample telah diselesaikan</p>
                </div>
            @endif

            @if (!$is_done || $status_sampling !== 'Berhasil')
                <a href="{{ route('mobile.sampling.klinik.form.withCount', [$permohonan_uji_klinik->id_permohonan_uji_klinik, $next_count ?? 2]) }}"
                    class="btn btn-secondary">
                    <span>🔄</span>
                    <span>Sampling Baru (Resampling ke-{{ $next_count ?? 2 }})</span>
                </a>
            @else
                <div
                    style="background: #f8d7da; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                    <p style="margin: 0; color: #721c24; font-weight: 600;">⚠️ Sampling terakhir sudah berhasil dan
                        selesai. Tidak dapat melakukan sampling ulang.</p>
                </div>
            @endif

            <a href="{{ route('mobile.sampling.klinik.home') }}" class="btn btn-primary" style="margin-top: 10px;">
                <span>🏠</span>
                <span>Kembali ke Home</span>
            </a>

            <form action="{{ route('mobile.sampling.klinik.logout') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Tombol Selesai di halaman success
            $('#btnSelesaiSuccess').on('click', function() {
                // Validate
                if (!$('#jam_sampling_success').val()) {
                    swal({
                        title: "Error!",
                        text: "Jam sampling wajib diisi!",
                        icon: "warning"
                    });
                    return;
                }

                if (!$('#nama_petugas_success').val()) {
                    swal({
                        title: "Error!",
                        text: "Nama pengambil sample wajib dipilih!",
                        icon: "warning"
                    });
                    return;
                }

                // Show loading
                $(this).prop('disabled', true).text('Memproses...');

                // Submit to mark-done
                $.ajax({
                    url: '{{ route('mobile.sampling.klinik.markDone', $permohonan_uji_klinik->id_permohonan_uji_klinik) }}',
                    method: 'POST',
                    data: {
                        jam_sampling: $('#jam_sampling_success').val(),
                        nama_petugas: $('#nama_petugas_success').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            $('#btnSelesaiSuccess').prop('disabled', false).html(
                                '<span>✓</span> Selesai');
                            swal({
                                title: "Error!",
                                text: response.pesan,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#btnSelesaiSuccess').prop('disabled', false).html(
                            '<span>✓</span> Selesai');
                        let message = 'Gagal menyelesaikan pengambilan sample!';
                        if (xhr.responseJSON && xhr.responseJSON.pesan) {
                            message = xhr.responseJSON.pesan;
                        }
                        swal({
                            title: "Error!",
                            text: message,
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
