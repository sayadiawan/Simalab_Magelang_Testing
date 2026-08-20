<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Validasi Hasil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 10px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ececec;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead {
            background: #fff1ed;
        }

        thead th {
            padding: 10px;
            text-align: left;
            color: #ff5e62;
            font-weight: 600;
        }

        tbody td {
            padding: 10px;
            border-top: 1px solid #f1f1f1;
            vertical-align: top;
        }

        .bmu-nilai-table-wrap {
            max-width: 220px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.35;
        }

        .bmu-nilai-table-wrap table {
            width: auto;
            min-width: 100%;
            font-size: 12px;
        }

        .bmu-nilai-table-wrap td,
        .bmu-nilai-table-wrap th {
            padding: 2px 4px;
            border: none;
            white-space: nowrap;
        }

        .pdf-preview {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e3f2fd;
            padding: 15px;
        }

        .pdf-preview iframe {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 10px;
            background: #f5f5f5;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
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
            margin-top: 10px;
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
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-group label span:first-child {
            margin-right: 8px;
            font-size: 18px;
        }

        .petugas-display {
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header" style="position: relative;">
            <form method="POST" action="{{ route('mobile.dokter.logout') }}"
                style="position: absolute; top: 20px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
            <h1>VALIDASI HASIL</h1>
            <p>Laboratorium Kesehatan Daerah<br>Kabupaten Magelang</p>
        </div>

        <div class="card">
            <h3 class="card-title">📋 Informasi Permohonan</h3>
            <div class="info-row">
                <span>No. Register</span>
                <span><strong>{{ $permohonan->noregister_permohonan_uji_klinik }}</strong></span>
            </div>
            @if ($permohonan->pasien)
                <div class="info-row">
                    <span>No. Rekam Medis</span>
                    <span>{{ Carbon\Carbon::createFromFormat('Y-m-d', $permohonan->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $permohonan->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-row">
                    <span>Nama Pasien</span>
                    <span><strong>{{ $permohonan->pasien->nama_pasien }}</strong></span>
                </div>
                <div class="info-row">
                    <span>Usia / Jenis Kelamin</span>
                    <span>{{ $permohonan->umurtahun_pasien_permohonan_uji_klinik }} tahun / {{ $permohonan->pasien->gender_pasien == 'L' || $permohonan->pasien->gender_pasien == 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span>Tgl. Register</span>
                <span>{{ $tgl_register }}</span>
            </div>
            @if ($tgl_pengujian)
                <div class="info-row">
                    <span>Tgl. Pengujian</span>
                    <span>{{ $tgl_pengujian }}</span>
                </div>
            @endif
            @if ($verifikator_data)
                <div class="info-row">
                    <span>Verifikator</span>
                    <span>{{ $verifikator_data['nama'] }}</span>
                </div>
            @endif
        </div>

        <div class="card">
            <h3 class="card-title">🔬 Ringkasan Parameter</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Parameter</th>
                            <th>Hasil</th>
                            <th>Satuan</th>
                            <th>Baku Mutu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($parameters as $parameter)
                            @php
                                $baku_mutu = $parameter->bakumutu;
                                $min = $baku_mutu->min ?? null;
                                $max = $baku_mutu->max ?? null;
                                $equal = $baku_mutu->equal ?? null;
                                $nilai_baku_mutu = $baku_mutu->nilai_baku_mutu ?? null;
                                $hasil = $parameter->hasil_permohonan_uji_parameter_klinik ?? null;
                                $hasil_koreksi = $parameter->hasil_koreksi_permohonan_uji_parameter_klinik ?? null;
                                $current_result = $hasil_koreksi ?: $hasil;
                                
                                // Get number format from parameter
                                $numberFormat = $parameter->parametersatuanklinik->number_format ?? 'en';
                            @endphp
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $parameter->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}</td>
                                <td>
                                    @if ($current_result)
                                        {!! cek_hasil_color(
                                            $current_result,
                                            $min,
                                            $max,
                                            $equal,
                                            'result_display_' . $parameter->id_permohonan_uji_parameter_klinik,
                                            $parameter->offset_baku_mutu ?? 'default',
                                            $numberFormat
                                        ) !!}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $parameter->unit->name_unit ?? '-' }}</td>
                                <td>
                                    @if ($nilai_baku_mutu)
                                        {!! nilaiBakuMutuForDisplay($nilai_baku_mutu) !!}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">🗂️ View Hasil / PDF</h3>
            <div class="pdf-preview">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: 600; color: #555;">Pratinjau PDF Hasil Uji</span>
                    <a href="{{ $previewUrl }}" target="_blank" class="btn btn-primary"
                        style="width: auto; padding: 10px 18px;">
                        <span>📄</span>
                        <span>Buka PDF</span>
                    </a>
                </div>
                <iframe src="{{ $previewUrl }}" loading="lazy"></iframe>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">✅ Validasi Pengesahan</h3>

            @if ($existing_validasi)
                <div class="alert alert-info" style="margin-bottom: 20px; padding: 12px; background-color: #d1ecf1; border-left: 4px solid #0c5460; border-radius: 4px;">
                    <strong>ℹ️ Informasi:</strong> Validasi sudah dilakukan pada
                    <strong>{{ \Carbon\Carbon::parse($existing_validasi->start_date)->isoFormat('D MMMM Y HH:mm') }}</strong>
                    oleh <strong>{{ $existing_validasi->nama_petugas }}</strong>.
                    Anda dapat memperbarui jika dibutuhkan.
                </div>
            @endif

            <form id="validasi-form" action="{{ route('mobile.dokter.storeValidasi', $permohonan->id_permohonan_uji_klinik) }}"
                method="POST">
                @csrf

                <div class="form-group">
                    <label for="waktu">
                        <span>🕐</span>
                        <span>Waktu Validasi</span>
                    </label>
                    <input type="text" class="form-control" id="waktu" name="waktu"
                        value="{{ $default_waktu }}" required 
                        placeholder="--:--">
                </div>

                <div class="form-group">
                    <label for="nama_petugas_display">
                        <span>👤</span>
                        <span>Nama Petugas</span>
                    </label>
                    
                    @if ($user_level === 'DKTR')
                        {{-- Jika level DKTR, hanya tampilkan nama tanpa dropdown --}}
                        <input type="hidden" name="nama_petugas" value="{{ $default_nama_petugas }}">
                        <div class="petugas-display">
                            {{ $default_nama_petugas }}
                        </div>
                    @else
                        {{-- Jika level LAB, ADMIN, atau elits-dev, tampilkan dropdown --}}
                        <select class="form-control" id="nama_petugas" name="nama_petugas" required>
                            <option value="">- Pilih Petugas -</option>
                            @foreach ($petugasValidator as $petugas)
                                <option value="{{ $petugas }}" {{ $default_nama_petugas == $petugas ? 'selected' : '' }}>
                                    {{ $petugas }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="form-group">
                    <label for="kesimpulan_hasil">
                        <span>📝</span>
                        <span>Kesimpulan Hasil</span>
                    </label>
                    <textarea class="form-control" id="kesimpulan_hasil" name="kesimpulan_hasil" rows="6" style="min-height: 150px;">{{ $kesimpulan_hasil }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 6px; margin-top: 10px;">
                    <span>✔</span>
                    <span>Simpan Validasi</span>
                </button>
            </form>
        </div>

        <div class="alert alert-info">
            <strong>ℹ️ Informasi:</strong> Hasil uji telah diverifikasi dan siap untuk divalidasi. Silakan periksa hasil uji di atas sebelum melakukan validasi.
        </div>

        <a href="{{ route('mobile.dokter.home') }}" class="btn btn-secondary">
            <span>←</span>
            <span>Kembali ke Home</span>
        </a>
    </div>

    {{-- jQuery dari CDN dipindah ke file lokal --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
    {{-- Flatpickr & SweetAlert2 dari CDN diganti ke file lokal --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/sweetalert2.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendors/tinymce/tinymce.min.js') }}"></script>
    <script>
        jQuery(document).ready(function($) {
            // Initialize TinyMCE for kesimpulan hasil
            function initKesimpulanHasilTinyMCE() {
                if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                    setTimeout(initKesimpulanHasilTinyMCE, 300);
                    return;
                }

                // Check if editor already exists
                if (tinymce.get('kesimpulan_hasil')) {
                    return;
                }

                var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
                if (tinymce.baseURL === undefined || 
                    tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                    tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                }

                if ($('#kesimpulan_hasil').length > 0) {
                    try {
                        tinymce.init({
                            selector: '#kesimpulan_hasil',
                            height: 200,
                            menubar: false,
                            theme: 'modern',
                            content_css: false,
                            document_base_url: window.location.origin,
                            plugins: [
                                'lists charmap',
                                'searchreplace',
                                'paste'
                            ],
                            toolbar: 'bold italic underline | superscript subscript | charmap | ' +
                                'bullist numlist | alignleft aligncenter alignright alignjustify | ' +
                                'removeformat',
                            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
                            mobile: {
                                plugins: ['autosave', 'lists', 'autolink'],
                                toolbar: ['undo', 'redo', 'bold', 'italic', 'bullist', 'numlist']
                            }
                        });
                    } catch (e) {
                        console.error('Error initializing TinyMCE:', e);
                    }
                }
            }

            // Initialize TinyMCE after a short delay
            setTimeout(initKesimpulanHasilTinyMCE, 500);

            // Initialize Flatpickr for time input
            var defaultTime = "{{ $default_waktu }}";
            var timePicker = flatpickr("#waktu", {
                enableTime: true,
                noCalendar: true,
                allowInput: true,
                dateFormat: "H:i",
                time_24hr: true,
                defaultDate: defaultTime || new Date(),
                minuteIncrement: 1,
                clickOpens: true,
                animate: true,
                placeholder: "--:--"
            });

            // Set default time if exists
            if (defaultTime) {
                try {
                    var timeParts = defaultTime.split(':');
                    if (timeParts.length === 2) {
                        var defaultDate = new Date();
                        defaultDate.setHours(parseInt(timeParts[0]));
                        defaultDate.setMinutes(parseInt(timeParts[1]));
                        timePicker.setDate(defaultDate, false);
                    }
                } catch (e) {
                    console.log('Error setting default time:', e);
                }
            }

            $('#validasi-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var waktuValue = $('#waktu').val();
                
                // Validate waktu format (HH:mm)
                if (!waktuValue || !/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(waktuValue)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Mohon masukkan waktu yang valid (format: HH:mm)'
                    });
                    return false;
                }

                // Save TinyMCE content to textarea before submit
                if (typeof tinymce !== 'undefined' && tinymce.get('kesimpulan_hasil')) {
                    tinymce.get('kesimpulan_hasil').save();
                }

                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.pesan || 'Validasi berhasil disimpan.',
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.pesan || 'Terjadi kesalahan saat menyimpan data.',
                                icon: "warning"
                            });
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON || {};
                        var errorMessage = err.pesan || err.message || 'Terjadi kesalahan saat menyimpan data.';
                        
                        // Handle validation errors
                        if (xhr.status === 422 && err.errors) {
                            var errorList = [];
                            for (var field in err.errors) {
                                if (err.errors.hasOwnProperty(field)) {
                                    errorList.push(err.errors[field][0]);
                                }
                            }
                            errorMessage = errorList.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMessage
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>

