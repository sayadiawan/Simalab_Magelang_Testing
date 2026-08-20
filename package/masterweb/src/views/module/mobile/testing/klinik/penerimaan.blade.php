<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Penerimaan Sampel - Klinik</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
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

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
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
            flex: 1;
            margin-left: 10px;
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
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
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

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .step-item {
            flex: 1;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .step-item.active {
            color: #2D6BCF;
            font-weight: 600;
        }

        .step-item.completed {
            color: #28a745;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .sample-section {
            background: #f0f4ff;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #2D6BCF;
        }

        .sample-header {
            color: #2D6BCF;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-sample {
            background: #2D6BCF;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .quality-checkbox-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .quality-checkbox {
            display: flex;
            align-items: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }

        .quality-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }

        .quality-checkbox input[type="checkbox"]:checked + label {
            font-weight: 600;
        }

        .quality-checkbox:has(input[type="checkbox"]:checked) {
            border-color: #2D6BCF;
            background: #f0f4ff;
        }

        .quality-checkbox label {
            display: flex;
            align-items: center;
            cursor: pointer;
            flex: 1;
            font-size: 14px;
            margin: 0;
        }

        .quality-icon {
            margin-right: 6px;
            font-size: 14px;
        }

        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏥 PENERIMAAN SAMPEL</h1>
            <p>Step 1 dari 4</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <span>⚠️</span>
                {{ session('error') }}
            </div>
        @endif

        <div class="step-indicator">
            <div class="step-item active">1. Penerimaan</div>
            <div class="step-item">2. Pengolah</div>
            <div class="step-item">3. Pemeriksa</div>
            <div class="step-item">4. Verifikasi</div>
        </div>

        <div class="card">
            <div class="card-title">
                <span>📋</span>
                <span>Informasi Permohonan</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Register:</span>
                <span class="info-value">{{ $permohonan->getDisplayNoregister() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Pasien:</span>
                <span class="info-value">{{ $permohonan->pasien->nama_pasien ?? '-' }}</span>
            </div>
            @if ($jenis_sampel)
                <div class="info-row">
                    <span class="info-label">Jenis Sampel:</span>
                    <span class="info-value">{{ $jenis_sampel }}</span>
                </div>
            @endif
        </div>

        <form id="formPenerimaan" method="POST" action="{{ route('mobile.testing.klinik.storePenerimaan', $id) }}">
            @csrf
            <div class="card">
                <div class="card-title">
                    <span>⏰</span>
                    <span>Waktu Penerimaan</span>
                </div>
                <div class="form-group">
                    <label for="waktu">Waktu <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="waktu" name="waktu" 
                        value="{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}" 
                        required placeholder="HH:mm">
                </div>
            </div>

            @if (isset($jenis_sampel_array) && count($jenis_sampel_array) > 0)
                @foreach ($jenis_sampel_array as $index => $sampel_type)
                <div class="card sample-section">
                    <div class="sample-header">
                        <span>🧪</span>
                        <span>Sampel: <span class="badge-sample">{{ $sampel_type }}</span></span>
                    </div>

                    <div class="form-group">
                        <label for="penerimaan_sampel_{{ $index }}">
                            PENERIMAAN SAMPEL <span style="color: red">*</span>
                        </label>
                        <textarea class="form-control" name="penerimaan_sampel[{{ $sampel_type }}]" 
                            id="penerimaan_sampel_{{ $index }}" required rows="3"
                            placeholder="Masukkan catatan penerimaan sampel (contoh: kondisi sampel saat diterima, waktu penerimaan, dll)">{{ $penerimaan_sampel_data[$sampel_type] ?? old('penerimaan_sampel.' . $sampel_type) }}</textarea>
                        <small class="form-text">
                            <span>ℹ️</span>
                            Catat kondisi penerimaan untuk sampel {{ $sampel_type }}
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="volume_sampel_{{ $index }}">
                            VOLUME SAMPEL <span style="color: red">*</span>
                        </label>
                        <input type="text" class="form-control" name="volume_sampel[{{ $sampel_type }}]" 
                            id="volume_sampel_{{ $index }}" required
                            placeholder="Masukkan volume sampel (contoh: 5 ml, 10 cc, dll)"
                            value="{{ $volume_sampel_data[$sampel_type] ?? old('volume_sampel.' . $sampel_type) }}">
                        <small class="form-text">
                            <span>ℹ️</span>
                            Masukkan volume untuk sampel tipe {{ $sampel_type }}
                        </small>
                    </div>

                    <div class="form-group">
                        <label>
                            KUALITAS SAMPEL <span style="color: red">*</span>
                        </label>
                        <div class="quality-checkbox-group">
                            <div class="quality-checkbox">
                                <input type="checkbox" name="kualitas_sampel[{{ $sampel_type }}][]"
                                    id="kualitas_lisis_{{ $index }}" value="Lisis"
                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Lisis', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                <label for="kualitas_lisis_{{ $index }}">
                                    <span class="quality-icon" style="color: #ff6b6b;">●</span>
                                    Lisis
                                </label>
                            </div>
                            <div class="quality-checkbox">
                                <input type="checkbox" name="kualitas_sampel[{{ $sampel_type }}][]"
                                    id="kualitas_ikterik_{{ $index }}" value="Ikterik"
                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Ikterik', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                <label for="kualitas_ikterik_{{ $index }}">
                                    <span class="quality-icon" style="color: #ffd93d;">●</span>
                                    Ikterik
                                </label>
                            </div>
                            <div class="quality-checkbox">
                                <input type="checkbox" name="kualitas_sampel[{{ $sampel_type }}][]"
                                    id="kualitas_lipemik_{{ $index }}" value="Lipemik"
                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Lipemik', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                <label for="kualitas_lipemik_{{ $index }}">
                                    <span class="quality-icon" style="color: #ff9ff3;">●</span>
                                    Lipemik
                                </label>
                            </div>
                            <div class="quality-checkbox">
                                <input type="checkbox" name="kualitas_sampel[{{ $sampel_type }}][]"
                                    id="kualitas_cukup_{{ $index }}" value="Cukup"
                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Cukup', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                <label for="kualitas_cukup_{{ $index }}">
                                    <span class="quality-icon" style="color: #51cf66;">✓</span>
                                    Cukup
                                </label>
                            </div>
                            <div class="quality-checkbox">
                                <input type="checkbox" name="kualitas_sampel[{{ $sampel_type }}][]"
                                    id="kualitas_beku_{{ $index }}" value="Beku"
                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Beku', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                <label for="kualitas_beku_{{ $index }}">
                                    <span class="quality-icon" style="color: #74c0fc;">❄</span>
                                    Beku
                                </label>
                            </div>
                        </div>
                        <small class="form-text">
                            <span>ℹ️</span>
                            Pilih semua kondisi kualitas sampel yang sesuai untuk {{ $sampel_type }}
                        </small>
                    </div>
                </div>
                @endforeach
            @else
                <div class="card">
                    <div class="card-title">
                        <span>📝</span>
                        <span>Catatan Penerimaan</span>
                    </div>
                    <div class="alert alert-danger">
                        <span>⚠️</span>
                        Jenis sampel tidak ditemukan. Silakan lengkapi data sampling terlebih dahulu.
                    </div>
                </div>
            @endif

            @if (!$is_analis)
                <div class="card">
                    <div class="card-title">
                        <span>👤</span>
                        <span>Pilih Petugas</span>
                    </div>
                    <div class="form-group">
                        <label for="nama_petugas">Nama Petugas <span style="color: red">*</span></label>
                        @if (isset($selected_petugas) && $selected_petugas)
                            {{-- Jika petugas user ada di list, tampilkan sebagai text dan hidden input --}}
                            <input type="text" class="form-control" value="{{ $selected_petugas }}" readonly style="background-color: #f0f0f0;">
                            <input type="hidden" id="nama_petugas" name="nama_petugas" value="{{ $selected_petugas }}">
                            <small class="form-text text-muted" style="margin-top: 5px; display: block;">
                                <i class="fas fa-info-circle"></i> Petugas terdeteksi dari akun Anda
                            </small>
                        @else
                            {{-- Jika petugas user tidak ada di list, tampilkan dropdown --}}
                            <select class="form-control" id="nama_petugas" name="nama_petugas" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach ($petugas_list as $petugas)
                                    <option value="{{ $petugas['name'] }}" 
                                        {{ ($verification && $verification->nama_petugas == $petugas['name']) ? 'selected' : '' }}>
                                        {{ $petugas['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="info-row">
                        <span class="info-label">Petugas:</span>
                        <span class="info-value">{{ $user_name }}</span>
                    </div>
                </div>
            @endif

            <a href="{{ route('mobile.testing.klinik.status', $id) }}" class="btn btn-secondary" style="margin-bottom: 10px;">
                <span>📊</span>
                <span>Lihat Status</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <span>💾</span>
                <span>Simpan & Lanjutkan</span>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize time picker
            flatpickr("#waktu", {
                enableTime: true,
                noCalendar: true,
                allowInput: true,
                dateFormat: "H:i",
                time_24hr: true,
                defaultDate: "{{ $verification ? \Carbon\Carbon::parse($verification->start_date)->format('H:i') : \Carbon\Carbon::now()->format('H:i') }}"
            });

            // Form submission validation
            document.getElementById('formPenerimaan').addEventListener('submit', function(e) {
                const waktu = document.getElementById('waktu').value;
                if (!waktu || !/^\d{1,2}:\d{2}$/.test(waktu)) {
                    e.preventDefault();
                    alert('Mohon masukkan waktu yang valid (format: HH:mm)');
                    return false;
                }

                // Validate that at least one quality checkbox is checked for each sample type
                const sampleSections = document.querySelectorAll('.sample-section');
                let isValid = true;
                let errorMessage = '';

                sampleSections.forEach(function(section) {
                    const sampleType = section.querySelector('.badge-sample').textContent;
                    const qualityCheckboxes = section.querySelectorAll('input[name*="kualitas_sampel"]');
                    const checkedBoxes = Array.from(qualityCheckboxes).filter(cb => cb.checked);
                    
                    if (checkedBoxes.length === 0) {
                        isValid = false;
                        errorMessage = 'Mohon pilih minimal satu kualitas sampel untuk ' + sampleType;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                    return false;
                }
            });
        });
    </script>
</body>

</html>

