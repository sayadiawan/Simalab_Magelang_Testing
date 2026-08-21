<!DOCTYPE html>
<html lang="id">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/mobile/css/simlab-mobile-theme.css') }}?v={{ @filemtime(public_path('assets/mobile/css/simlab-mobile-theme.css')) ?: time() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tanda Tangan Sampling</title>

    <link href="{{ asset('assets/admin/cdn-local/css/font-awesome.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>

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
            padding-bottom: 100px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .top-bar h1 {
            font-size: 18px;
            font-weight: 600;
            color: #0b3a5c;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #0b3a5c;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #0b3a5c;
        }

        .signature-canvas-container {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            margin-bottom: 10px;
        }

        .signature-canvas {
            display: block;
            width: 100%;
            height: 150px;
            touch-action: none;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-clear {
            width: 100%;
            padding: 10px;
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .btn-clear:active {
            background: #e0e0e0;
        }

        .btn-submit {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 600px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .btn-submit:active {
            transform: translateX(-50%) scale(0.98);
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .info-box {
            background: #e7f3ff;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            color: #0066cc;
            margin-bottom: 15px;
        }

        .signature-section {
            margin-top: 15px;
        }

        .btn-method.active {
            background: #0b3a5c !important;
            color: white !important;
            border-color: #0b3a5c !important;
        }

        .btn-method:not(.active) {
            background: white !important;
            color: #666 !important;
            border-color: #e0e0e0 !important;
        }
    </style>
</head>

<body>
    <div class="container">
        @if(isset($backUrl))
        <div style="background: rgba(255, 255, 255, 0.95); padding: 12px 20px; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-size: 14px;">
            <a href="{{ $backUrl }}" style="color: #0b3a5c; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                <span>←</span>
                <span>Kembali</span>
            </a>
        </div>
        @endif
        
        <div class="top-bar">
            <h1>✍️ Tanda Tangan Sampling</h1>
        </div>

        <div class="card">
            <div class="section-title">
                <i class="fas fa-user"></i> Tanda Tangan Pelanggan
            </div>

            <div class="info-box">
                <strong>Pelanggan:</strong> {{ $permohonan_uji->customer->name_customer ?? '-' }}
            </div>

            @php
                // Ambil data dari session jika sudah diinput di draft-list, atau dari old() jika ada error
                $nama_pelanggan_value = old('nama_pelanggan', isset($customer_data) && is_array($customer_data) ? ($customer_data['nama_pelanggan'] ?? '') : '');
                $jabatan_pelanggan_value = old('jabatan_pelanggan', isset($customer_data) && is_array($customer_data) ? ($customer_data['jabatan_pelanggan'] ?? '') : '');
                $nip_pelanggan_value = old('nip_pelanggan', isset($customer_data) && is_array($customer_data) ? ($customer_data['nip_pelanggan'] ?? '') : '');
                $is_customer_data_from_session = isset($customer_data) && is_array($customer_data) && !empty($customer_data) && isset($customer_data['nama_pelanggan']) && !empty($customer_data['nama_pelanggan']);
            @endphp

            @if ($is_customer_data_from_session)
                {{-- Jika data sudah diinput di draft-list, tampilkan sebagai readonly dengan info --}}
                <div class="alert alert-info" style="margin-bottom: 15px; padding: 12px; border-radius: 8px; background-color: #e7f3ff; border-left: 4px solid #0b3a5c;">
                    <i class="fas fa-info-circle"></i> <strong>Data pelanggan sudah diinput sebelumnya</strong>
                </div>
            @endif

            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" class="form-control" id="nama_pelanggan" 
                    value="{{ $nama_pelanggan_value }}"
                    placeholder="Nama Pelanggan" 
                    {{ $is_customer_data_from_session ? 'readonly' : '' }}
                    required>
            </div>

            <div class="form-group">
                <label>Jabatan</label>
                <input type="text" class="form-control" id="jabatan_pelanggan" 
                    value="{{ $jabatan_pelanggan_value }}"
                    placeholder="Jabatan Penanda Tangan" 
                    {{ $is_customer_data_from_session ? 'readonly' : '' }}
                    required>
            </div>

            <div class="form-group">
                <label>NIP (Opsional)</label>
                <input type="text" class="form-control" id="nip_pelanggan" 
                    value="{{ $nip_pelanggan_value }}"
                    placeholder="NIP Penanda Tangan"
                    {{ $is_customer_data_from_session ? 'readonly' : '' }}>
            </div>

            <div class="form-group">
                <label>Pilih Metode Tanda Tangan</label>
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <button type="button" id="btnCanvasPelanggan" class="btn-method active"
                        onclick="switchMethodPelanggan('canvas')"
                        style="flex: 1; padding: 10px; border: 2px solid #0b3a5c; border-radius: 8px; background: #0b3a5c; color: white; font-weight: 600; cursor: pointer;">
                        ✍️ Tulis Manual
                    </button>
                    <button type="button" id="btnUploadPelanggan" class="btn-method"
                        onclick="switchMethodPelanggan('upload')"
                        style="flex: 1; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #666; font-weight: 600; cursor: pointer;">
                        📤 Upload File
                    </button>
                </div>
            </div>

            <!-- Canvas Signature -->
            <div id="canvasSectionPelanggan" class="signature-section">
                <div class="signature-canvas-container">
                    <canvas id="signaturePadPelanggan" class="signature-canvas"></canvas>
                </div>
                <button type="button" id="clearPelanggan" class="btn-clear">
                    🗑️ Hapus Tanda Tangan Pelanggan
                </button>
            </div>

            <!-- Upload File -->
            <div id="uploadSectionPelanggan" class="signature-section" style="display: none;">
                <div class="form-group">
                    <input type="file" id="fileUploadPelanggan" accept="image/*" style="display: none;"
                        onchange="handleFileUploadPelanggan(event)">
                    <button type="button" onclick="document.getElementById('fileUploadPelanggan').click()"
                        style="width: 100%; padding: 12px; border: 2px dashed #0b3a5c; border-radius: 8px; background: #f8f9ff; color: #0b3a5c; font-weight: 600; cursor: pointer;">
                        📤 Pilih File Gambar
                    </button>
                </div>
                <div id="previewPelanggan" style="display: none; margin-top: 15px;">
                    <img id="previewImagePelanggan" src="" alt="Preview"
                        style="max-width: 100%; max-height: 200px; border: 2px solid #ddd; border-radius: 8px; padding: 5px;">
                    <button type="button" onclick="clearUploadPelanggan()"
                        style="width: 100%; margin-top: 10px; padding: 10px; border: none; border-radius: 8px; background: #dc3545; color: white; font-weight: 600; cursor: pointer;">
                        🗑️ Hapus Gambar
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="section-title">
                <i class="fas fa-user-md"></i> Tanda Tangan Petugas Pengambil Sampel
            </div>

            <div class="info-box">
                <strong>Petugas Terbanyak:</strong> {{ $petugas_terbanyak }}
            </div>

            <div class="signature-canvas-container">
                <canvas id="signaturePadPetugas" class="signature-canvas"></canvas>
            </div>
            <button type="button" id="clearPetugas" class="btn-clear">
                🗑️ Hapus Tanda Tangan Petugas
            </button>
        </div>

        <input type="hidden" id="signature_pelanggan" value="">
        <input type="hidden" id="signature_petugas" value="">
    </div>

    <button type="button" class="btn-submit" id="submitBtn" onclick="submitSignature()">
        ✅ Simpan Tanda Tangan
    </button>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        let signaturePadPelanggan;
        let signaturePadPetugas;

        // Wait for SignaturePad to load
        window.waitForSignaturePad = function(callback) {
            if (window.SignaturePad) {
                callback(window.SignaturePad);
            } else if (window.signaturePad) {
                const SignaturePad = window.signaturePad.SignaturePad || window.signaturePad.default;
                if (SignaturePad) {
                    callback(SignaturePad);
                } else {
                    setTimeout(function() {
                        window.waitForSignaturePad(callback);
                    }, 100);
                }
            } else {
                setTimeout(function() {
                    window.waitForSignaturePad(callback);
                }, 100);
            }
        };

        function resizeCanvas(canvas) {
            if (!canvas) return;

            const rect = canvas.getBoundingClientRect();
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            const width = rect.width || canvas.offsetWidth || 400;
            const height = rect.height || canvas.offsetHeight || 150;

            canvas.width = width * ratio;
            canvas.height = height * ratio;

            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';

            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);

            ctx.clearRect(0, 0, width, height);
        }

        function initSignaturePads() {
            window.waitForSignaturePad(function(SignaturePad) {
                if (!SignaturePad) {
                    console.error('SignaturePad library not available');
                    alert('Library tanda tangan tidak dapat dimuat. Silakan refresh halaman.');
                    return;
                }

                // Initialize Pelanggan signature pad
                const canvasPelanggan = document.getElementById('signaturePadPelanggan');
                if (canvasPelanggan) {
                    setTimeout(function() {
                        resizeCanvas(canvasPelanggan);
                        setTimeout(function() {
                            resizeCanvas(canvasPelanggan);
                            try {
                                signaturePadPelanggan = new SignaturePad(canvasPelanggan, {
                                    backgroundColor: 'rgba(255, 255, 255, 0)',
                                    penColor: 'rgb(0, 0, 0)',
                                    minWidth: 1,
                                    maxWidth: 3,
                                    velocityFilterWeight: 0.7,
                                    throttle: 16
                                });

                                canvasPelanggan.style.touchAction = 'none';
                                canvasPelanggan.style.pointerEvents = 'auto';
                                canvasPelanggan.style.webkitTouchCallout = 'none';
                                canvasPelanggan.style.webkitUserSelect = 'none';
                                canvasPelanggan.style.userSelect = 'none';

                                canvasPelanggan.addEventListener('touchstart', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPelanggan.addEventListener('touchmove', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPelanggan.addEventListener('touchend', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });

                                console.log('SignaturePad Pelanggan initialized');
                            } catch (e) {
                                console.error('Error initializing SignaturePad Pelanggan:', e);
                            }
                        }, 200);
                    }, 150);
                }

                // Initialize Petugas signature pad
                const canvasPetugas = document.getElementById('signaturePadPetugas');
                if (canvasPetugas) {
                    setTimeout(function() {
                        resizeCanvas(canvasPetugas);
                        setTimeout(function() {
                            resizeCanvas(canvasPetugas);
                            try {
                                signaturePadPetugas = new SignaturePad(canvasPetugas, {
                                    backgroundColor: 'rgba(255, 255, 255, 0)',
                                    penColor: 'rgb(0, 0, 0)',
                                    minWidth: 1,
                                    maxWidth: 3,
                                    velocityFilterWeight: 0.7,
                                    throttle: 16
                                });

                                canvasPetugas.style.touchAction = 'none';
                                canvasPetugas.style.pointerEvents = 'auto';
                                canvasPetugas.style.webkitTouchCallout = 'none';
                                canvasPetugas.style.webkitUserSelect = 'none';
                                canvasPetugas.style.userSelect = 'none';

                                canvasPetugas.addEventListener('touchstart', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPetugas.addEventListener('touchmove', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });
                                canvasPetugas.addEventListener('touchend', function(e) {
                                    e.preventDefault();
                                }, {
                                    passive: false
                                });

                                console.log('SignaturePad Petugas initialized');
                            } catch (e) {
                                console.error('Error initializing SignaturePad Petugas:', e);
                            }
                        }, 200);
                    }, 150);
                }
            });
        }

        // Switch method for pelanggan
        function switchMethodPelanggan(method) {
            const canvasSection = document.getElementById('canvasSectionPelanggan');
            const uploadSection = document.getElementById('uploadSectionPelanggan');
            const btnCanvas = document.getElementById('btnCanvasPelanggan');
            const btnUpload = document.getElementById('btnUploadPelanggan');

            if (method === 'canvas') {
                canvasSection.style.display = 'block';
                uploadSection.style.display = 'none';
                btnCanvas.classList.add('active');
                btnUpload.classList.remove('active');
            } else {
                canvasSection.style.display = 'none';
                uploadSection.style.display = 'block';
                btnCanvas.classList.remove('active');
                btnUpload.classList.add('active');
            }
        }

        // Handle file upload for pelanggan
        function handleFileUploadPelanggan(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.match('image.*')) {
                alert('File harus berupa gambar!');
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB!');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewPelanggan');
                const previewImage = document.getElementById('previewImagePelanggan');
                previewImage.src = e.target.result;
                preview.style.display = 'block';

                // Store base64 data
                document.getElementById('signature_pelanggan').value = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function clearUploadPelanggan() {
            document.getElementById('fileUploadPelanggan').value = '';
            document.getElementById('previewPelanggan').style.display = 'none';
            document.getElementById('signature_pelanggan').value = '';
        }

        // Clear buttons
        document.getElementById('clearPelanggan').addEventListener('click', function() {
            if (signaturePadPelanggan) {
                signaturePadPelanggan.clear();
                document.getElementById('signature_pelanggan').value = '';
            }
        });

        document.getElementById('clearPetugas').addEventListener('click', function() {
            if (signaturePadPetugas) signaturePadPetugas.clear();
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (signaturePadPelanggan) {
                resizeCanvas(document.getElementById('signaturePadPelanggan'));
                setTimeout(initSignaturePads, 100);
            }
            if (signaturePadPetugas) {
                resizeCanvas(document.getElementById('signaturePadPetugas'));
                setTimeout(initSignaturePads, 100);
            }
        });

        // Initialize on page load
        window.addEventListener('load', function() {
            setTimeout(initSignaturePads, 500);
        });

        function submitSignature() {
            // Validation
            const namaPelanggan = document.getElementById('nama_pelanggan').value.trim();
            const jabatanPelanggan = document.getElementById('jabatan_pelanggan').value.trim();

            if (!namaPelanggan || !jabatanPelanggan) {
                alert('Nama pelanggan dan jabatan wajib diisi!');
                return;
            }

            // Get signature data
            let signaturePelanggan = null;

            // Check if using canvas or upload
            const canvasSection = document.getElementById('canvasSectionPelanggan');
            if (canvasSection.style.display !== 'none') {
                // Using canvas
                if (signaturePadPelanggan && !signaturePadPelanggan.isEmpty()) {
                    signaturePelanggan = signaturePadPelanggan.toDataURL();
                }
            } else {
                // Using upload
                signaturePelanggan = document.getElementById('signature_pelanggan').value;
            }

            // Validate signature pelanggan
            if (!signaturePelanggan) {
                alert('Tanda tangan pelanggan wajib diisi!');
                return;
            }

            let signaturePetugas = null;
            if (signaturePadPetugas && !signaturePadPetugas.isEmpty()) {
                signaturePetugas = signaturePadPetugas.toDataURL();
            }

            // Set signature to hidden inputs
            if (signaturePelanggan) {
                document.getElementById('signature_pelanggan').value = signaturePelanggan;
            }
            if (signaturePetugas) {
                document.getElementById('signature_petugas').value = signaturePetugas;
            }

            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            // Submit via AJAX
            fetch('{{ route('mobile.sampling.saveSignature', $permohonan_uji->id_permohonan_uji) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nama_pelanggan: namaPelanggan,
                        jabatan_pelanggan: jabatanPelanggan,
                        nip_pelanggan: document.getElementById('nip_pelanggan').value.trim(),
                        signature_pelanggan: signaturePelanggan,
                        signature_petugas: signaturePetugas
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status || data.success) {
                        alert(data.pesan || 'Tanda tangan berhasil disimpan!');
                        window.location.href = data.redirect ||
                            '{{ route('mobile.sampling.success', $permohonan_uji->id_permohonan_uji) }}';
                    } else {
                        alert('Error: ' + (data.pesan || 'Gagal menyimpan tanda tangan'));
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan tanda tangan');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
        }
    </script>
</body>

</html>
