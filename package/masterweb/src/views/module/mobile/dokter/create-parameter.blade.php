<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pilih Parameter</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 600px;
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
            border-left: 4px solid #0b3a5c;
        }

        .patient-info h3 {
            font-size: 14px;
            color: #0b3a5c;
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

        .category-header {
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 0.5px;
            margin: 20px 0 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .parameter-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .parameter-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .parameter-item:hover {
            background: #e9ecef;
            border-color: #0b3a5c;
            transform: translateX(5px);
        }

        .parameter-item input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin-right: 12px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #0b3a5c;
        }

        .parameter-item label {
            margin: 0;
            cursor: pointer;
            flex: 1;
            font-size: 15px;
            color: #333;
            line-height: 1.5;
            font-weight: 500;
        }

        .parameter-item input[type="checkbox"]:checked + label {
            color: #0b3a5c;
            font-weight: 600;
        }

        .parameter-item:has(input[type="checkbox"]:checked) {
            background: #e7f3ff;
            border-color: #0b3a5c;
        }

        .selected-count {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            display: none;
        }

        .selected-count.show {
            display: block;
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
            background: linear-gradient(135deg, #06283f 0%, #0b3a5c 48%, #0d8f7f 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .empty-message {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                📋
            </div>
            <h1>PILIH PARAMETER</h1>
            <p>Laboratorium SIMLAB<br>Lingkungan pengujian</p>
        </div>

        <div class="card">
            <div class="alert alert-info">
                <span>ℹ️</span>
                <span>Pilih parameter pemeriksaan yang diperlukan untuk pasien ini.</span>
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
                @endif
            </div>

            <div class="search-box">
                <input type="text" id="searchParameter" placeholder="🔍 Cari parameter..." autocomplete="off">
            </div>

            <form id="parameterForm" method="POST" action="{{ route('mobile.dokter.storeParameter', $id) }}">
                @csrf
                <input type="hidden" name="permohonan_uji_klinik" value="{{ $id }}">

                @php
                    // Group pakets by category
                    $hematologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                        $nama = strtolower($jenis->name_parameter_jenis_klinik);
                        return str_contains($nama, 'darah') ||
                            str_contains($nama, 'hematologi') ||
                            str_contains($nama, 'hemoglobin');
                    });
                    $hematologi_pakets = collect();
                    foreach ($hematologi_jenis as $jenis) {
                        if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                            $hematologi_pakets = $hematologi_pakets->merge($jenis->pakets);
                        }
                    }
                    $hematologi_pakets = $hematologi_pakets->unique('id_parameter_paket_klinik')->values();

                    $urin_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                        $nama = strtolower($jenis->name_parameter_jenis_klinik);
                        return str_contains($nama, 'urin') ||
                            str_contains($nama, 'urine') ||
                            str_contains($nama, 'narkoba');
                    });
                    $urin_pakets = collect();
                    foreach ($urin_jenis as $jenis) {
                        if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                            $urin_pakets = $urin_pakets->merge($jenis->pakets);
                        }
                    }
                    $urin_pakets = $urin_pakets->unique('id_parameter_paket_klinik')->values();

                    $imunologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                        $nama = strtolower($jenis->name_parameter_jenis_klinik);
                        return str_contains($nama, 'imunologi') ||
                            str_contains($nama, 'serologi') ||
                            str_contains($nama, 'widal') ||
                            str_contains($nama, 'dengue') ||
                            str_contains($nama, 'hepatitis');
                    });
                    $imunologi_pakets = collect();
                    foreach ($imunologi_jenis as $jenis) {
                        if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                            $imunologi_pakets = $imunologi_pakets->merge($jenis->pakets);
                        }
                    }
                    $imunologi_pakets = $imunologi_pakets->unique('id_parameter_paket_klinik')->values();

                    $kimia_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                        $nama = strtolower($jenis->name_parameter_jenis_klinik);
                        return str_contains($nama, 'kimia') || str_contains($nama, 'klinik');
                    });
                    $kimia_pakets = collect();
                    foreach ($kimia_jenis as $jenis) {
                        if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                            $kimia_pakets = $kimia_pakets->merge($jenis->pakets);
                        }
                    }
                    $kimia_pakets = $kimia_pakets->unique('id_parameter_paket_klinik')->values();
                @endphp

                <div class="category-section" data-category="hematologi">
                    <div class="category-header">A. HEMATOLOGI</div>
                    <div class="parameter-list">
                        @if ($hematologi_pakets->isEmpty())
                            <div class="empty-message">Tidak ada parameter</div>
                        @else
                            @foreach ($hematologi_pakets as $paket_item)
                                <div class="parameter-item" data-name="{{ strtolower($paket_item->name_parameter_paket_klinik) }}">
                                    <input type="checkbox" class="form-check-input parameter-checkbox"
                                        name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                        value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                        @if (in_array($paket_item->id_parameter_paket_klinik, $selected_paket_ids ?? [])) checked @endif
                                        id="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                    <label for="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                        {{ $paket_item->name_parameter_paket_klinik }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="category-section" data-category="urin">
                    <div class="category-header">B. URIN</div>
                    <div class="parameter-list">
                        @if ($urin_pakets->isEmpty())
                            <div class="empty-message">Tidak ada parameter</div>
                        @else
                            @foreach ($urin_pakets as $paket_item)
                                <div class="parameter-item" data-name="{{ strtolower($paket_item->name_parameter_paket_klinik) }}">
                                    <input type="checkbox" class="form-check-input parameter-checkbox"
                                        name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                        value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                        @if (in_array($paket_item->id_parameter_paket_klinik, $selected_paket_ids ?? [])) checked @endif
                                        id="urin_{{ $paket_item->id_parameter_paket_klinik }}">
                                    <label for="urin_{{ $paket_item->id_parameter_paket_klinik }}">
                                        {{ $paket_item->name_parameter_paket_klinik }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="category-section" data-category="imunologi">
                    <div class="category-header">C. IMUNOLOGI</div>
                    <div class="parameter-list">
                        @if ($imunologi_pakets->isEmpty())
                            <div class="empty-message">Tidak ada parameter</div>
                        @else
                            @foreach ($imunologi_pakets as $paket_item)
                                <div class="parameter-item" data-name="{{ strtolower($paket_item->name_parameter_paket_klinik) }}">
                                    <input type="checkbox" class="form-check-input parameter-checkbox"
                                        name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                        value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                        @if (in_array($paket_item->id_parameter_paket_klinik, $selected_paket_ids ?? [])) checked @endif
                                        id="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                    <label for="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                        {{ $paket_item->name_parameter_paket_klinik }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="category-section" data-category="kimia">
                    <div class="category-header">D. KIMIA DARAH</div>
                    <div class="parameter-list">
                        @if ($kimia_pakets->isEmpty())
                            <div class="empty-message">Tidak ada parameter</div>
                        @else
                            @foreach ($kimia_pakets as $paket_item)
                                <div class="parameter-item" data-name="{{ strtolower($paket_item->name_parameter_paket_klinik) }}">
                                    <input type="checkbox" class="form-check-input parameter-checkbox"
                                        name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                        value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                        @if (in_array($paket_item->id_parameter_paket_klinik, $selected_paket_ids ?? [])) checked @endif
                                        id="kimia_{{ $paket_item->id_parameter_paket_klinik }}">
                                    <label for="kimia_{{ $paket_item->id_parameter_paket_klinik }}">
                                        {{ $paket_item->name_parameter_paket_klinik }}
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                @if ($parameter_paket_extra->isNotEmpty())
                    <div class="category-section" data-category="extra">
                        <div class="category-header">PAKET EXTRA</div>
                        <div class="parameter-list">
                            @foreach ($parameter_paket_extra as $val)
                                <div class="parameter-item" data-name="{{ strtolower($val->nama_parameter_paket_extra) }}">
                                    <input type="checkbox" class="form-check-input parameter-checkbox"
                                        name="paket_extra[{{ $val->id_parameter_paket_extra }}]"
                                        value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}"
                                        {{ in_array($val->id_parameter_paket_extra, $paket_extra ?? []) ? 'checked' : '' }}
                                        id="extra_{{ $val->id_parameter_paket_extra }}">
                                    <label for="extra_{{ $val->id_parameter_paket_extra }}">
                                        {{ $val->nama_parameter_paket_extra }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" id="btnSave">
                    <span>💾</span>
                    <span>Simpan Parameter</span>
                </button>
                <a href="{{ route('mobile.dokter.home') }}" class="btn btn-secondary">
                    <span>←</span>
                    <span>Kembali</span>
                </a>
            </form>
        </div>
    </div>

    <div class="selected-count" id="selectedCount">
        <span id="selectedCountText">0</span> parameter dipilih
    </div>

    <script>
        // Search functionality
        document.getElementById('searchParameter').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const parameterItems = document.querySelectorAll('.parameter-item');

            parameterItems.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name && name.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide category headers based on visible items
            document.querySelectorAll('.category-section').forEach(section => {
                const visibleItems = section.querySelectorAll('.parameter-item[style="display: flex;"], .parameter-item:not([style*="display: none"])');
                const header = section.querySelector('.category-header');
                if (header) {
                    if (visibleItems.length === 0 && searchTerm !== '') {
                        section.style.display = 'none';
                    } else {
                        section.style.display = 'block';
                    }
                }
            });
        });

        // Update selected count
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.parameter-checkbox:checked').length;
            const countElement = document.getElementById('selectedCount');
            const countText = document.getElementById('selectedCountText');
            
            countText.textContent = checked;
            
            if (checked > 0) {
                countElement.classList.add('show');
            } else {
                countElement.classList.remove('show');
            }
        }

        // Add event listeners to all checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.parameter-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            updateSelectedCount(); // Initial count
        });

        // Form submission
        document.getElementById('parameterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const checked = document.querySelectorAll('.parameter-checkbox:checked').length;
            
            if (checked === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Pilih minimal satu parameter!'
                });
                return;
            }

            // Show loading
            const btnSave = document.getElementById('btnSave');
            btnSave.disabled = true;
            btnSave.innerHTML = '<span>⏳</span><span>Menyimpan...</span>';

            // Submit form using FormData
            var formData = new FormData(this);
            
            // Get CSRF token from meta tag (fallback to form input if meta tag not found)
            let csrfToken = null;
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                csrfToken = metaToken.getAttribute('content');
            } else {
                const formToken = formData.get('_token');
                if (formToken) {
                    csrfToken = formToken;
                }
            }
            
            // Ensure CSRF token is in FormData (Laravel checks both header and form data)
            if (csrfToken && !formData.has('_token')) {
                formData.append('_token', csrfToken);
            }
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => {
                    // Handle CSRF token mismatch (419)
                    if (response.status === 419) {
                        return response.json().then(data => {
                            throw new Error('CSRF_TOKEN_MISMATCH');
                        });
                    }
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If not JSON, might be redirect or error page
                        throw new Error('Unexpected response format');
                    }
                })
                .then(data => {
                    if (data.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.pesan || 'Parameter berhasil disimpan!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            if (data.urlNextStep) {
                                window.location.href = data.urlNextStep;
                            } else {
                                window.location.href = '{{ route('mobile.dokter.home') }}';
                            }
                        });
                    } else {
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<span>💾</span><span>Simpan Parameter</span>';
                        
                        let pesan = '';
                        if (typeof data.pesan === 'object') {
                            Object.values(data.pesan).forEach(value => {
                                pesan += value + '<br>';
                            });
                        } else {
                            pesan = data.pesan || 'Gagal menyimpan parameter!';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: pesan
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<span>💾</span><span>Simpan Parameter</span>';
                    
                    let errorMessage = 'Terjadi kesalahan saat menyimpan parameter.';
                    if (error.message === 'CSRF_TOKEN_MISMATCH') {
                        errorMessage = 'Session expired. Silakan refresh halaman dan coba lagi.';
                        // Optionally reload page after a delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage
                    });
                });
        });
    </script>
</body>

</html>
