<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verifikasi</title>
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
            <h1>VERIFIKASI</h1>
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
            <h3 style="margin-bottom: 15px; color: #333;">Form Verifikasi</h3>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($existing_verification)
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    Data verifikasi sudah tersimpan:
                    <br><strong>Tanggal Mulai:</strong>
                    {{ \Carbon\Carbon::parse($existing_verification->start_date)->format('d/m/Y') }}
                    <br><strong>Tanggal Selesai:</strong>
                    {{ \Carbon\Carbon::parse($existing_verification->stop_date)->format('d/m/Y') }}
                    <br><strong>Nama Petugas:</strong> {{ $existing_verification->nama_petugas }}
                </div>
            @endif

            <form id="verifikasi-form" method="POST"
                action="{{ route('mobile.testing.storeTanggalVerifikasi', $sample->id_samples) }}">
                @csrf
                <input type="hidden" name="verification_step" value="4">
                <input type="hidden" name="lab_id" value="{{ $laboratorium->id_laboratorium }}">

                <div class="form-group">
                    <label for="start_date">Tanggal Mulai / Jam</label>
                    <input type="text" class="form-control" id="start_date" name="start_date"
                        placeholder="Pilih tanggal mulai"
                        value="{{ $existing_verification ? \Carbon\Carbon::parse($existing_verification->start_date)->format('d/m/Y') : $default_start_date ?? '' }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="stop_date">Tanggal Selesai / Jam</label>
                    <input type="text" class="form-control" id="stop_date" name="stop_date"
                        placeholder="Pilih tanggal selesai"
                        value="{{ $existing_verification ? \Carbon\Carbon::parse($existing_verification->stop_date)->format('d/m/Y') : $default_stop_date ?? '' }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="nama_petugas">Nama Petugas</label>
                    <select class="form-control" id="nama_petugas" name="nama_petugas" required>
                        <option value="">Pilih Nama Petugas</option>
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}"
                                {{ (($existing_verification && $existing_verification->nama_petugas == $nama_petugas) || ($default_koordinator_kesmas == $nama_petugas)) ? 'selected' : '' }}>
                                {{ $nama_petugas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <span>✓</span>
                    <span>Verifikasi</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            // Helper function to format date as dd/mm/yyyy
            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            // Helper function to adjust times to working hours (8:00 AM to 3:00 PM)
            function adjustToWorkHours(date) {
                const startHour = 8;
                const endHour = 15;

                if (date.getHours() < startHour) {
                    date.setHours(startHour, 0, 0, 0); // Set time to 8:00 AM
                } else if (date.getHours() >= endHour) {
                    // If time is after 3:00 PM, move to the next day at 8:00 AM
                    date.setDate(date.getDate() + 1);
                    date.setHours(startHour, 0, 0, 0);
                }
            }

            // Determine default dates (same logic as verification-2.blade.php)
            let inputStart, inputStop;

            @if ($existing_verification)
                // Use existing data if available
                inputStart = new Date("{{ $existing_verification->start_date }}");
                inputStop = new Date("{{ $existing_verification->stop_date }}");
            @else
                // Default: Start from Input Hasil Stop (step 3) or current date
                @if (isset($default_start_date) && isset($default_stop_date))
                    // Parse from default dates provided by backend
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
                    var inputHasilStop = new Date();
                    inputStart = new Date(inputHasilStop);
                    adjustToWorkHours(inputStart);
                    inputStop = new Date(inputStart.getTime() + 10 * 60000); // +10 menit dari Input Start
                    adjustToWorkHours(inputStop);
                @endif
            @endif

            // Initialize Flatpickr for date inputs
            const start_date_picker = flatpickr("#start_date", {
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y",
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1
                }
            });

            // Set default date - use existing value if available, otherwise use calculated default
            @if ($existing_verification)
                start_date_picker.setDate(formatDate(inputStart), true);
            @else
                // Use value from input field (which is already set from backend default)
                var startValue = $('#start_date').val();
                if (startValue) {
                    // Parse the value and set it
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

            // Set default date - use existing value if available, otherwise use calculated default
            @if ($existing_verification)
                stop_date_picker.setDate(formatDate(inputStop), true);
            @else
                // Use value from input field (which is already set from backend default)
                var stopValue = $('#stop_date').val();
                if (stopValue) {
                    // Parse the value and set it
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

            // Handle form submission
            $('#verifikasi-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            if (response.redirect_url) {
                                // Show SweetAlert and redirect
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message ||
                                            'Data verifikasi berhasil disimpan. Lanjut ke Verifikasi Hasil.',
                                        icon: 'success',
                                        confirmButtonText: 'Lanjut',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false
                                    }).then(function() {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    if (confirm(response.message ||
                                            'Data verifikasi berhasil disimpan. Klik OK untuk melanjutkan ke Verifikasi Hasil.'
                                        )) {
                                        window.location.href = response.redirect_url;
                                    }
                                }
                            } else {
                                alert('Data verifikasi berhasil disimpan!');
                                location.reload();
                            }
                        } else {
                            alert('Terjadi kesalahan: ' + (response.message ||
                                'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
</body>

</html>
