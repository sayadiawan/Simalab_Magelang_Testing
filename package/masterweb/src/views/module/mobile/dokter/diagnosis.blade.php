<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Input Diagnosis</title>
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
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 20px;
            padding: 20px;
        }

        .header-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 35px;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .patient-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #2D6BCF;
        }

        .patient-info h3 {
            font-size: 14px;
            color: #2D6BCF;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-control:focus {
            outline: none;
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
            margin-bottom: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: #e7f3ff;
            color: #0066cc;
            border: 1px solid #b3d9ff;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                👨‍⚕️
            </div>
            <h1>INPUT DIAGNOSIS</h1>
            <p>Laboratorium Kesehatan Daerah<br>Kabupaten Magelang</p>
        </div>

        <div class="card">
            <div class="alert alert-info">
                <span>ℹ️</span>
                <span>Silakan lengkapi informasi diagnosis untuk pasien ini sebelum melanjutkan ke pemilihan parameter
                    pemeriksaan.</span>
            </div>

            <div class="patient-info">
                <h3>👤 Informasi Pasien</h3>
                <div class="info-item">
                    <label>No. Registrasi:</label>
                    <span><strong>{{ $code }}</strong></span>
                </div>
                @if ($pasien)
                    <div class="info-item">
                        <label>No. Rekam Medis:</label>
                        <span>{{ Carbon\Carbon::createFromFormat('Y-m-d', $pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="info-item">
                        <label>Nama Pasien:</label>
                        <span><strong>{{ $pasien->nama_pasien }}</strong></span>
                    </div>
                    <div class="info-item">
                        <label>Umur/Jenis Kelamin:</label>
                        <span>{{ $umur_string }} /
                            {{ $pasien->gender_pasien == 'L' || $pasien->gender_pasien == 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Alamat:</label>
                        <span>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($pasien) }}</span>
                    </div>
                    <div class="info-item">
                        <label>No. Telepon:</label>
                        <span>{{ $pasien->phone_pasien ?? '-' }}</span>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <span>⚠️</span>
                        <span>Data pasien tidak ditemukan</span>
                    </div>
                @endif
            </div>

            @if($item->request_pasien_permohonan_uji_klinik)
            <div class="card" style="margin-bottom: 20px;">
                <div class="patient-info">
                    <h3>💬 Request / Keluhan Pasien</h3>
                    <div style="background: white; padding: 12px; border-radius: 8px; margin-top: 10px; color: #333; font-size: 14px; line-height: 1.6;">
                        {!! rubahNilaikeHtml($item->request_pasien_permohonan_uji_klinik) !!}
                    </div>
                    <small class="form-text" style="margin-top: 8px; display: block;">Request atau keluhan pasien yang telah diinput sebelumnya</small>
                </div>
            </div>
            @endif

            <form id="diagnosisForm" method="POST" action="{{ route('mobile.dokter.storeDiagnosis', $id) }}">
                @csrf
                <input type="hidden" name="permohonan_uji_klinik" value="{{ $id }}">

                <div class="form-group">
                    <label for="diagnosa_permohonan_uji_klinik">
                        DIAGNOSA <span class="required">*</span>
                    </label>
                    <textarea class="form-control" name="diagnosa_permohonan_uji_klinik"
                        id="diagnosa_permohonan_uji_klinik" placeholder="Masukkan diagnosa pasien" rows="5"
                        required>{{ $item->diagnosa_permohonan_uji_klinik ?? '' }}</textarea>
                    <small class="form-text">Jelaskan diagnosis pasien dengan lengkap untuk menentukan pemeriksaan
                        laboratorium yang sesuai</small>
                </div>

                <button type="submit" class="btn btn-primary" id="btnSave">
                    <span>💾</span>
                    <span>Simpan & Lanjut ke Parameter</span>
                </button>
                <a href="{{ route('mobile.dokter.home') }}" class="btn btn-secondary">
                    <span>←</span>
                    <span>Kembali</span>
                </a>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('diagnosisForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var diagnosa = document.getElementById('diagnosa_permohonan_uji_klinik').value.trim();

            if (!diagnosa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Mohon isi field diagnosa!'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form using FormData
            var formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.pesan || 'Diagnosis berhasil disimpan!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.urlNextStep;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.pesan || 'Gagal menyimpan diagnosis!'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat menyimpan diagnosis.'
                    });
                });
        });
    </script>
</body>

</html>

