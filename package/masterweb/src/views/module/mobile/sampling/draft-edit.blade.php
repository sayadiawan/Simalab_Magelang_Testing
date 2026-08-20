<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-id" content="{{ session()->getId() }}">
    <meta name="has-auth" content="{{ session('mobile_sampling_auth') ? 'true' : 'false' }}">
    <title>Edit Draft Sample</title>

    <link href="{{ asset('assets/admin/cdn-local/css/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    
    <!-- jQuery - Must load first before any other scripts that depend on it -->
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script>
        // Ensure jQuery is loaded and available globally
        if (typeof jQuery === 'undefined' && typeof $ === 'undefined') {
            console.error('jQuery failed to load!');
        } else {
            // Make sure jQuery is available as both $ and jQuery
            window.$ = window.jQuery = jQuery;
        }
    </script>
    
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>
    
    <!-- Offline Support & SPA -->
    <script src="{{ asset('js/mobile-sampling-offline.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-sampling-spa.js') }}?v={{ time() }}"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding-bottom: 100px;
        }

        .top-bar {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 18px;
            font-weight: 600;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #2D6BCF;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #2D6BCF;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 13px;
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
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .readonly-field {
            background: #f8f9fa;
            color: #666;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .floating-submit {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .parameter-badge {
            background: #2D6BCF;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin: 3px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <a href="{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}" class="back-btn">
            ← Kembali
        </a>
        <h1>✏️ Edit Draft Sample</h1>
        <div style="width: 80px;"></div>
    </div>

    <div class="container">
        <!-- Info Permohonan -->
        <div class="info-box">
            <strong>👤 Pelanggan:</strong> {{ $permohonan_uji->customer->name_customer ?? '-' }}
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form method="POST"
            action="{{ route('mobile.sampling.draft.update', [$permohonan_uji->id_permohonan_uji, $draft->id_sample_draft]) }}"
            id="draftEditForm"
            data-spa-ignore="true">
            @csrf

            <!-- Informasi Sample (Read Only) -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Informasi Sample (Tidak dapat diubah)
                </div>

                <div class="form-group">
                    <label>Jenis Sampel:</label>
                    <input type="text" class="form-control readonly-field" readonly
                        value="{{ $draft->sampletype ? $draft->sampletype->code_sample_type . ' - ' . $draft->sampletype->name_sample_type : '-' }}">
                </div>

                @if ($draft->packet)
                    <div class="form-group">
                        <label>Paket:</label>
                        <input type="text" class="form-control readonly-field" readonly
                            value="{{ $draft->packet->name_packet }}">
                    </div>
                @endif

                <div class="form-group">
                    <label>Parameter Pengujian:</label>
                    <div>
                        @if ($draft->samplemethoddraft && $draft->samplemethoddraft->count() > 0)
                            @foreach ($draft->samplemethoddraft as $method_draft)
                                @if ($method_draft->method)
                                    <span class="parameter-badge">
                                        {{ $method_draft->method->params_method }}
                                        @if ($method_draft->laboratorium)
                                            ({{ $method_draft->laboratorium->nama_laboratorium }})
                                        @endif
                                    </span>
                                @endif
                            @endforeach
                        @else
                            <span style="color: #999;">Tidak ada parameter</span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label>Biaya Pengujian:</label>
                    <input type="text" class="form-control readonly-field" readonly
                        value="Rp {{ number_format($draft->cost_samples, 0, ',', '.') }}">
                </div>
            </div>

            <!-- Data yang Dapat Diubah -->
            <div class="card">
                <div class="section-title">
                    <i class="fas fa-edit"></i> Data yang Dapat Diubah
                </div>

                <div class="form-group">
                    <label for="datesampling_samples">
                        <i class="fas fa-calendar"></i> Tanggal Sampling
                    </label>
                    <input type="text" class="form-control" name="datesampling_samples" id="datesampling_samples"
                        value="{{ old('datesampling_samples', $draft->datesampling_samples ? \Carbon\Carbon::parse($draft->datesampling_samples)->format('d/m/Y H:i') : '') }}"
                        placeholder="Pilih tanggal sampling" readonly>
                </div>

                <div class="form-group">
                    <label for="date_sending">
                        <i class="fas fa-calendar-check"></i> Tanggal Kirim
                    </label>
                    <input type="text" class="form-control" name="date_sending" id="date_sending"
                        value="{{ old('date_sending', $draft->date_sending ? \Carbon\Carbon::parse($draft->date_sending)->format('d/m/Y H:i') : '') }}"
                        placeholder="Pilih tanggal kirim" readonly>
                </div>

                <div class="form-group">
                    <label for="titik_pengambilan">
                        <i class="fas fa-map-marker-alt"></i> Titik Lokasi Pengambilan
                    </label>
                    <textarea class="form-control" name="titik_pengambilan" id="titik_pengambilan" rows="3"
                        placeholder="Masukkan titik lokasi pengambilan sampel">{{ old('titik_pengambilan', $draft->titik_pengambilan) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="pengambil_sampel">
                        <i class="fas fa-users"></i> Pengambil Sampel (Bisa lebih dari satu)
                    </label>
                    @if (!empty($pengambil_sampel_list))
                        <div
                            style="max-height: 200px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 10px; padding: 10px; background: #f8f9fa;">
                            @foreach ($pengambil_sampel_list as $petugas)
                                <label
                                    style="display: flex; align-items: center; padding: 8px; margin-bottom: 5px; cursor: pointer; border-radius: 8px; transition: background 0.2s;"
                                    onmouseover="this.style.background='#e9ecef'"
                                    onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="pengambil_sampel[]" value="{{ $petugas }}"
                                        {{ in_array($petugas, $selected_pengambil_sampel) ? 'checked' : '' }}
                                        style="margin-right: 10px; width: 20px; height: 20px; cursor: pointer;">
                                    <span style="font-size: 14px; color: #333;">{{ $petugas }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Pilih satu atau lebih pengambil sampel
                        </small>
                    @else
                        <input type="text" class="form-control" name="pengambil_sampel_text"
                            id="pengambil_sampel_text"
                            value="{{ old('pengambil_sampel_text', is_array($selected_pengambil_sampel) ? implode(', ', $selected_pengambil_sampel) : $draft->pengambil_sampel) }}"
                            placeholder="Masukkan nama pengambil sampel (pisahkan dengan koma jika lebih dari satu)">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Data pengambil sampel belum tersedia di
                            VerificationActivity
                        </small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="cost_sampling_samples">
                        <i class="fas fa-money-bill-wave"></i> Biaya Sampling
                    </label>
                    <input type="number" class="form-control" name="cost_sampling_samples" id="cost_sampling_samples"
                        min="0" step="1000"
                        value="{{ old('cost_sampling_samples', $draft->cost_sampling_samples) }}"
                        placeholder="Masukkan biaya sampling">
                </div>

                <div class="form-group">
                    <label for="note_samples">
                        <i class="fas fa-sticky-note"></i> Catatan
                    </label>
                    <textarea class="form-control" name="note_samples" id="note_samples" rows="3"
                        placeholder="Tambahkan catatan jika diperlukan">{{ old('note_samples', $draft->note_samples) }}</textarea>
                </div>
            </div>
        </form>
    </div>

    <div class="floating-submit">
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-submit btn-secondary"
                onclick="window.location.href='{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}'"
                style="flex: 0.4;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn-submit" onclick="submitForm(); return false;" style="flex: 1;">
                <i class="fas fa-save"></i> SIMPAN PERUBAHAN
            </button>
        </div>
    </div>

    <script>
        // Make functions available globally
        window.submitForm = async function(e) {
            // Prevent any default behavior
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            const form = document.querySelector('form#draftEditForm') || document.querySelector('form');
            if (!form) {
                console.error('Form not found');
                alert('Form tidak ditemukan');
                return false;
            }
            
            // Prevent form from submitting normally and prevent SPA from intercepting
            if (!form.hasAttribute('data-spa-ignore')) {
                form.setAttribute('data-spa-ignore', 'true');
            }
            
            // Prevent default form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                return false;
            }, { once: true });
            
            const formData = new FormData(form);

            // Convert date format from d/m/Y H:i to Y-m-d H:i:s
            const datesamplingEl = document.getElementById('datesampling_samples');
            const dateSendingEl = document.getElementById('date_sending');

            // Convert date format from d/m/Y H:i to Y-m-d H:i:s
            if (datesamplingEl && datesamplingEl.value) {
                try {
                    const datesampling = datesamplingEl.value.trim();
            if (datesampling) {
                const parts = datesampling.split(' ');
                const datePart = parts[0].split('/');
                        if (datePart.length === 3) {
                const timePart = parts[1] || '00:00';
                            const timeParts = timePart.split(':');
                            const hours = timeParts[0] || '00';
                            const minutes = timeParts[1] || '00';
                            // Format: Y-m-d H:i:s
                            const formattedDate = `${datePart[2]}-${datePart[1].padStart(2, '0')}-${datePart[0].padStart(2, '0')} ${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}:00`;
                            formData.set('datesampling_samples', formattedDate);
                            console.log('Formatted datesampling_samples:', formattedDate);
            }
                    }
                } catch (e) {
                    console.error('Error formatting datesampling_samples:', e);
                }
            }

            if (dateSendingEl && dateSendingEl.value) {
                try {
                    const dateSending = dateSendingEl.value.trim();
            if (dateSending) {
                const parts = dateSending.split(' ');
                const datePart = parts[0].split('/');
                        if (datePart.length === 3) {
                const timePart = parts[1] || '00:00';
                            const timeParts = timePart.split(':');
                            const hours = timeParts[0] || '00';
                            const minutes = timeParts[1] || '00';
                            // Format: Y-m-d H:i:s
                            const formattedDate = `${datePart[2]}-${datePart[1].padStart(2, '0')}-${datePart[0].padStart(2, '0')} ${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}:00`;
                            formData.set('date_sending', formattedDate);
                            console.log('Formatted date_sending:', formattedDate);
                        }
                    }
                } catch (e) {
                    console.error('Error formatting date_sending:', e);
                }
            }

            // Handle pengambil_sampel checkboxes
            // Remove all existing pengambil_sampel entries from formData first
            const allKeys = Array.from(formData.keys());
            allKeys.forEach(key => {
                if (key.startsWith('pengambil_sampel')) {
                    formData.delete(key);
                }
            });
            
            // Add checked checkboxes as array
            const pengambilSampelCheckboxes = document.querySelectorAll('input[name="pengambil_sampel[]"]:checked');
            if (pengambilSampelCheckboxes.length > 0) {
                pengambilSampelCheckboxes.forEach(checkbox => {
                    formData.append('pengambil_sampel[]', checkbox.value);
                });
                console.log('Added pengambil_sampel[]:', Array.from(pengambilSampelCheckboxes).map(cb => cb.value));
            }

            // Handle pengambil_sampel_text if exists (fallback) - only if no checkboxes selected
            if (pengambilSampelCheckboxes.length === 0) {
            const pengambilSampelText = document.getElementById('pengambil_sampel_text');
            if (pengambilSampelText && pengambilSampelText.value) {
                // If using text input, convert to array
                const names = pengambilSampelText.value.split(',').map(n => n.trim()).filter(n => n);
                names.forEach((name, index) => {
                    formData.append(`pengambil_sampel[${index}]`, name);
                });
                    console.log('Added pengambil_sampel from text:', names);
                }
            }

            // Show loading
            const submitBtn = document.querySelector('button[onclick*="submitForm"]');
            if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;

            // Check if online
            const isOnline = navigator.onLine;
            const permohonanId = '{{ $id }}';
            const draftId = '{{ $draft_id }}';
            
                try {
            if (isOnline) {
                // Try to submit online
                        // Get CSRF token from meta tag or form
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                         '{{ csrf_token() }}';
                        
                        // Check session state before submit
                        const hasAuth = document.querySelector('meta[name="has-auth"]')?.getAttribute('content') === 'true';
                        const sessionId = document.querySelector('meta[name="session-id"]')?.getAttribute('content');
                        const hasSessionCookie = document.cookie.includes('laravel_session') || document.cookie.includes('XSRF-TOKEN');
                        
                        // Log for debugging
                        console.log('Submitting form:', {
                            action: form.action,
                            hasCsrfToken: !!csrfToken,
                            hasAuth: hasAuth,
                            sessionId: sessionId,
                            hasSessionCookie: hasSessionCookie,
                            cookies: document.cookie
                        });
                        
                        // Warn if session seems missing
                        if (!hasAuth || !hasSessionCookie) {
                            console.warn('⚠️ Warning: Session may be missing before submit!', {
                                hasAuth: hasAuth,
                                hasSessionCookie: hasSessionCookie,
                                sessionId: sessionId
                            });
                        }
                        
                        const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Cache-Control': 'no-cache',
                                    'Pragma': 'no-cache'
                                },
                                credentials: 'include', // Important: include cookies/session
                                redirect: 'manual', // Don't follow redirects automatically
                                cache: 'no-store' // Don't cache the request
                            });
                        
                        console.log('Response received:', {
                            status: response.status,
                            statusText: response.statusText,
                            ok: response.ok,
                            redirected: response.redirected,
                            type: response.type,
                            url: response.url
                        });
                        
                        // Check response status
                        if (response.status === 401 || response.status === 403) {
                            // Unauthorized - session expired
                            const data = await response.json().catch(() => null);
                            if (data && data.redirect) {
                                alert('Session telah berakhir. Silakan login kembali.');
                                window.location.href = data.redirect;
                            } else {
                                alert('Session telah berakhir. Silakan login kembali.');
                                window.location.href = '{{ route('mobile.sampling.index', $permohonan_uji->id_permohonan_uji) }}';
                            }
                            return;
                        }
                        
                        // Check response status first
                        if (response.status === 422) {
                            // Validation error
                            try {
                                const data = await response.json();
                                let errorMessage = 'Data tidak valid';
                                if (data.errors) {
                                    const errorList = Object.values(data.errors).flat().join(', ');
                                    errorMessage = errorMessage + ': ' + errorList;
                                } else if (data.message) {
                                    errorMessage = data.message;
                                }
                                alert(errorMessage);
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                }
                                return;
                            } catch (e) {
                                console.error('Error parsing validation error:', e);
                                alert('Data tidak valid. Silakan periksa kembali input Anda.');
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                }
                                return;
                            }
                        }
                        
                        // Check content type
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            // JSON response
                            try {
                                const data = await response.json();
                                if (data.status === true || data.success === true) {
                                    // Success, redirect to draft list
                                    alert(data.message || 'Draft sample berhasil diupdate!');
                                    window.location.href = data.redirect || '{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}';
                                    return;
                                } else if (data.redirect) {
                                    // Has redirect URL
                                    alert(data.message || data.pesan || 'Terjadi kesalahan');
                                    window.location.href = data.redirect;
                                    return;
                        } else {
                                    alert(data.message || data.pesan || 'Terjadi kesalahan');
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalText;
                                    }
                                    return;
                                }
                            } catch (e) {
                                console.error('Error parsing JSON:', e);
                                alert('Terjadi kesalahan saat memproses response');
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                }
                                return;
                            }
                        }
                        
                        // HTML response - check if it's a redirect page
                        const html = await response.text();
                        
                        // Check if response contains redirect to login (session expired)
                        if (html.includes('mobile/sampling') && html.includes('login') && !html.includes('draft')) {
                            alert('Session telah berakhir. Silakan login kembali.');
                            window.location.href = '{{ route('mobile.sampling.index', $permohonan_uji->id_permohonan_uji) }}';
                            return;
                        }
                        
                        // Check for redirect in HTML
                        if (html.includes('window.location') || html.includes('redirect')) {
                            const match = html.match(/window\.location\.href\s*=\s*['"]([^'"]+)['"]/);
                            if (match) {
                                window.location.href = match[1];
                                return;
                            }
                        }
                        
                        // Check for Laravel redirect in HTML
                        const redirectMatch = html.match(/<meta[^>]*http-equiv=["']refresh["'][^>]*content=["'][^>]*url=([^"']+)["']/i);
                        if (redirectMatch) {
                            window.location.href = redirectMatch[1];
                            return;
                        }
                        
                        // If response is OK and contains success message, redirect to draft list
                        if (response.ok && (html.includes('success') || html.includes('berhasil'))) {
                            window.location.href = '{{ route('mobile.sampling.draftList', $permohonan_uji->id_permohonan_uji) }}';
                            return;
                        }
                        
                        // Default: reload page
                        window.location.reload();
                    } else {
                        // Save offline
                        await saveEditOffline(permohonanId, draftId, formData, form.action, originalText, submitBtn);
                    }
                } catch (error) {
                        console.error('Error:', error);
                        // If online request fails, save offline
                    await saveEditOffline(permohonanId, draftId, formData, form.action, originalText, submitBtn);
                }
            } else {
                // Fallback: submit form normally (but prevent SPA from intercepting)
                form.setAttribute('data-spa-ignore', 'true');
                form.submit();
            }
            return false;
        };

        // Initialize Flatpickr for date inputs
        function initializeFlatpickr() {
            const datesamplingEl = document.getElementById('datesampling_samples');
            const dateSendingEl = document.getElementById('date_sending');
            
            // Check if flatpickr locale is available
            const flatpickrConfig = {
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            };
            
            // Only add locale if it's available
            if (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) {
                flatpickrConfig.locale = "id";
            }
            
            if (datesamplingEl && !datesamplingEl.hasAttribute('data-flatpickr-initialized')) {
                try {
                    flatpickr(datesamplingEl, flatpickrConfig);
                    datesamplingEl.setAttribute('data-flatpickr-initialized', 'true');
                } catch (e) {
                    console.warn('Failed to initialize flatpickr for datesampling:', e);
                    // Fallback: initialize without locale
                    try {
                        flatpickr(datesamplingEl, {
                            enableTime: true,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true
                        });
                        datesamplingEl.setAttribute('data-flatpickr-initialized', 'true');
                    } catch (e2) {
                        console.error('Failed to initialize flatpickr (fallback):', e2);
                    }
                }
            }

            if (dateSendingEl && !dateSendingEl.hasAttribute('data-flatpickr-initialized')) {
                try {
                    flatpickr(dateSendingEl, flatpickrConfig);
                    dateSendingEl.setAttribute('data-flatpickr-initialized', 'true');
                } catch (e) {
                    console.warn('Failed to initialize flatpickr for date_sending:', e);
                    // Fallback: initialize without locale
                    try {
                        flatpickr(dateSendingEl, {
                            enableTime: true,
                            dateFormat: "d/m/Y H:i",
                            time_24hr: true
                        });
                        dateSendingEl.setAttribute('data-flatpickr-initialized', 'true');
                    } catch (e2) {
                        console.error('Failed to initialize flatpickr (fallback):', e2);
                    }
                }
            }
        }

        // Wait for flatpickr to be fully loaded before initializing
        function waitForFlatpickr(callback, maxAttempts = 10) {
            if (typeof flatpickr !== 'undefined') {
                callback();
            } else if (maxAttempts > 0) {
                setTimeout(() => waitForFlatpickr(callback, maxAttempts - 1), 100);
            } else {
                console.warn('Flatpickr not loaded, initializing without locale');
                callback();
            }
        }

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                waitForFlatpickr(initializeFlatpickr);
            });
        } else {
            waitForFlatpickr(initializeFlatpickr);
        }

        // Also initialize when SPA page is shown
        if (window.mobileSamplingSPA) {
            const originalShowPage = window.mobileSamplingSPA.showPage;
            window.mobileSamplingSPA.showPage = function(pageId) {
                originalShowPage.call(this, pageId);
                if (pageId && pageId.includes('draft-edit')) {
                    setTimeout(() => waitForFlatpickr(initializeFlatpickr), 200);
            }
            };
        }
        
        // Save edit data offline
        window.saveEditOffline = async function(permohonanId, draftId, formData, url, originalText, submitBtn) {
            if (window.mobileSamplingOffline) {
                // Convert FormData to object
                const data = {};
                for (let [key, value] of formData.entries()) {
                    if (data[key]) {
                        // If key already exists, make it an array
                        if (Array.isArray(data[key])) {
                            data[key].push(value);
                        } else {
                            data[key] = [data[key], value];
                        }
                    } else {
                        data[key] = value;
                    }
                }
                
                // Save to IndexedDB
                await window.mobileSamplingOffline.saveDraft(permohonanId, draftId, {
                    type: 'update',
                    ...data
                });
                
                // Add to sync queue
                await window.mobileSamplingOffline.addToSyncQueue('update', data, url, 'POST');
                
                alert('✅ Data disimpan secara lokal. Akan disinkronkan ketika online kembali.');
                if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Tersimpan (Offline)';
                submitBtn.disabled = false;
                }
                
                // Show offline indicator
                if (window.mobileSamplingOffline) {
                    window.mobileSamplingOffline.showNotification('Data tersimpan secara lokal', 'warning');
                }
            } else {
                alert('⚠️ Mode offline tidak tersedia. Silakan coba lagi ketika online.');
                if (submitBtn && originalText) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
        };
    </script>
</body>

</html>
