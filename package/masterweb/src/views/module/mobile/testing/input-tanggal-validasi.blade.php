<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Validasi</title>
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
            background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff7a69;
            box-shadow: 0 0 0 3px rgba(255, 122, 105, 0.15);
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
            background: linear-gradient(135deg, #ff8450 0%, #ff5e62 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            text-align: right;
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

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="header" style="position: relative;">
            <form method="POST" action="{{ route('mobile.testing.logout') }}"
                style="position: absolute; top: 20px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
            <h1>VALIDASI</h1>
            <p>Laboratorium {{ $laboratorium->kode_laboratorium == 'KIM' ? 'Kimia' : 'Mikrobiologi' }}</p>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 15px; color: #333;">Informasi Sample</h3>
            <div class="info-row">
                <span class="info-label">ID Sample:</span>
                <span class="info-value">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Sample:</span>
                <span class="info-value">{{ $sample->sampletype->name_sample_type ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Laboratorium:</span>
                <span class="info-value">{{ $laboratorium->nama_laboratorium }}</span>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 15px; color: #333;">Form Validasi</h3>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($existing_validation)
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    Data validasi sudah tersimpan:
                    <br><strong>Tanggal Mulai:</strong>
                    {{ \Carbon\Carbon::parse($existing_validation->start_date)->format('d/m/Y') }}
                    <br><strong>Tanggal Selesai:</strong>
                    {{ \Carbon\Carbon::parse($existing_validation->stop_date)->format('d/m/Y') }}
                    <br><strong>Nama Petugas:</strong> {{ $existing_validation->nama_petugas }}
                </div>
            @endif

            <form id="validasi-form" method="POST"
                action="{{ route('mobile.testing.storeValidasi', $sample->id_samples) }}">
                @csrf
                <input type="hidden" name="verification_step" value="5">
                <input type="hidden" name="lab_id" value="{{ $laboratorium->id_laboratorium }}">

                <div class="form-group">
                    <label for="start_date">Tanggal Mulai / Jam</label>
                    <input type="text" class="form-control" id="start_date" name="start_date"
                        placeholder="Pilih tanggal mulai"
                        value="{{ $existing_validation ? \Carbon\Carbon::parse($existing_validation->start_date)->format('d/m/Y') : $default_start_date ?? '' }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="stop_date">Tanggal Selesai / Jam</label>
                    <input type="text" class="form-control" id="stop_date" name="stop_date"
                        placeholder="Pilih tanggal selesai"
                        value="{{ $existing_validation ? \Carbon\Carbon::parse($existing_validation->stop_date)->format('d/m/Y') : $default_stop_date ?? '' }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="nama_petugas">Nama Petugas</label>
                    <select class="form-control" id="nama_petugas" name="nama_petugas" required>
                        <option value="">Pilih Nama Petugas</option>
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}"
                                {{ ($existing_validation && $existing_validation->nama_petugas == $nama_petugas) || $default_validator == $nama_petugas ? 'selected' : '' }}>
                                {{ $nama_petugas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>✓</span>
                    <span>Validasi</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            function adjustToWorkHours(date) {
                const startHour = 8;
                const endHour = 15;

                if (date.getHours() < startHour) {
                    date.setHours(startHour, 0, 0, 0);
                } else if (date.getHours() >= endHour) {
                    date.setDate(date.getDate() + 1);
                    date.setHours(startHour, 0, 0, 0);
                }
            }

            let inputStart, inputStop;

            @if ($existing_validation)
                inputStart = new Date("{{ $existing_validation->start_date }}");
                inputStop = new Date("{{ $existing_validation->stop_date }}");
            @else
                @if (isset($default_start_date) && isset($default_stop_date))
                    var partsStart = "{{ $default_start_date }}".split(' ');
                    var datePartsStart = partsStart[0].split('/');
                    var timePartsStart = partsStart[1].split(':');
                    inputStart = new Date(datePartsStart[2], datePartsStart[1] - 1, datePartsStart[0],
                        timePartsStart[0], timePartsStart[1]);

                    var partsStop = "{{ $default_stop_date }}".split(' ');
                    var datePartsStop = partsStop[0].split('/');
                    var timePartsStop = partsStop[1].split(':');
                    inputStop = new Date(datePartsStop[2], datePartsStop[1] - 1, datePartsStop[0], timePartsStop[0],
                        timePartsStop[1]);
                @else
                    inputStart = new Date();
                    adjustToWorkHours(inputStart);
                    inputStop = new Date(inputStart.getTime() + 10 * 60000);
                    adjustToWorkHours(inputStop);
                @endif
            @endif

            const start_date_picker = flatpickr("#start_date", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                }
            });

            @if ($existing_validation)
                start_date_picker.setDate(formatDate(inputStart), true);
            @else
                var startValue = $('#start_date').val();
                if (startValue) {
                    var parts = startValue.split(' ');
                    var dateParts = parts[0].split('/');
                    var timeParts = parts[1].split(':');
                    var startDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0], timeParts[0], timeParts[
                        1]);
                    start_date_picker.setDate(formatDate(startDate), true);
                } else {
                    start_date_picker.setDate(formatDate(inputStart), true);
                }
            @endif

            const stop_date_picker = flatpickr("#stop_date", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                }
            });

            @if ($existing_validation)
                stop_date_picker.setDate(formatDate(inputStop), true);
            @else
                var stopValue = $('#stop_date').val();
                if (stopValue) {
                    var parts = stopValue.split(' ');
                    var dateParts = parts[0].split('/');
                    var timeParts = parts[1].split(':');
                    var stopDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0], timeParts[0], timeParts[
                        1]);
                    stop_date_picker.setDate(formatDate(stopDate), true);
                } else {
                    stop_date_picker.setDate(formatDate(inputStop), true);
                }
            @endif

            $('#validasi-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            if (response.url_redirect) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message ||
                                            'Data validasi berhasil disimpan. Kembali ke status sample.',
                                        icon: 'success',
                                        confirmButtonText: 'Lanjut',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false
                                    }).then(function() {
                                        window.location.href = response.url_redirect;
                                    });
                                } else {
                                    window.location.href = response.url_redirect;
                                }
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message ||
                                    'Terjadi kesalahan saat menyimpan data.',
                                icon: "warning"
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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
