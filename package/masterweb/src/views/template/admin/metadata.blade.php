@php
    $opt = DB::table('ms_options')->first();
    $assetVer = function ($relativePath) {
        $fullPath = public_path($relativePath);
        return @filemtime($fullPath) ?: 0;
    };
    $adminCssV = max(
        @filemtime(public_path('assets/admin/css/style.css')) ?: 0,
        @filemtime(public_path('assets/admin/css/custom.css')) ?: 0,
        @filemtime(public_path('assets/admin/css/responsive.css')) ?: 0,
    );
@endphp

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">

    <title>@yield('title') @if (trim($__env->yieldContent('title')))
            -
        @endif {{ $opt->title }}</title>
    <!-- plugins:css -->
    <link rel="shortcut icon"
        href="{{ asset('assets/admin/images/' . \Smt\Masterweb\Models\Option::first()->favicon) }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/iconfonts/font-awesome/css/all.min.css') }}?v={{ $assetVer('assets/admin/vendors/iconfonts/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.base.css') }}?v={{ $assetVer('assets/admin/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.addons.css') }}?v={{ $assetVer('assets/admin/vendors/css/vendor.bundle.addons.css') }}">

    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}?v={{ $adminCssV }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-datetimepicker.min.css') }}?v={{ $assetVer('assets/admin/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}?v={{ $adminCssV }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/responsive.css') }}?v={{ $adminCssV }}">
    <link rel="stylesheet"
        href="{{ asset('assets/admin/vendors/iconfonts/simple-line-icon/css/simple-line-icons.css') }}?v={{ $assetVer('assets/admin/vendors/iconfonts/simple-line-icon/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/iconfonts/ti-icons/css/themify-icons.css') }}?v={{ $assetVer('assets/admin/vendors/iconfonts/ti-icons/css/themify-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/summernote/dist/summernote-bs4.css') }}?v={{ $assetVer('assets/admin/vendors/summernote/dist/summernote-bs4.css') }}">




    <style>
        @font-face {
            font-family: 'gotham-light';
            src: url({{ asset('assets/public/fonts/gotham/Gotham-Light.otf') }});
        }

        @font-face {
            font-family: 'gotham-medium';
            src: url({{ asset('assets/public/fonts/gotham/Gotham-Medium.otf') }});
        }

        @font-face {
            font-family: 'gotham-thin';
            src: url({{ asset('assets/public/fonts/gotham/Gotham-Thin.otf') }});
        }

        @font-face {
            font-family: 'gotham-ultra';
            src: url({{ asset('assets/public/fonts/gotham/Gotham-Ultra.otf') }});
        }

        @font-face {
            font-family: 'gotham-narrow-black';
            src: url({{ asset('assets/public/fonts/gotham/GothamNarrow-Black.otf') }});
        }

        @font-face {
            font-family: 'gothamnarrow-book';
            src: url({{ asset('assets/public/fonts/gotham/GothamNarrow-Book.otf') }});
        }

        @font-face {
            font-family: 'gotham-narrow-thin';
            src: url({{ asset('assets/public/fonts/gotham/GothamNarrow-Thin.otf') }});
        }

        @font-face {
            font-family: 'gotham-narrow-ultra';
            src: url({{ asset('assets/public/fonts/gotham/GothamNarrow-Ultra.otf') }});
        }
    </style>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.5.0.min.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/jquery-3.5.0.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/tempusdominus-bootstrap-4.min.css') }}?v={{ $assetVer('assets/admin/cdn-local/css/tempusdominus-bootstrap-4.min.css') }}" />

    <style>
        .smt-table th {
            background: #2D6BCF !important;
            color: #fff;
        }

        .smt-table tbody td.col-text-truncate {
            overflow: hidden;
            max-width: 0;
        }

        .smt-table .cell-truncate {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        /* Global loading overlay untuk menunggu semua asset/CDN siap */
        #global-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: opacity 0.3s ease;
        }

        #global-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        #global-loader .loader-spinner {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 6px solid #e0e7ff;
            border-top-color: #4f46e5;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }

        #global-loader .loader-text {
            font-size: 14px;
            color: #4b5563;
            text-align: center;
        }

        #global-loader .loader-subtext {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
            text-align: center;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}?v={{ $assetVer('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script defer src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script defer src="{{ asset('assets/admin/cdn-local/js/flatpickr-id.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/flatpickr-id.js') }}"></script>


    <script defer src="{{ asset('assets/admin/js/input-mask.js') }}?v={{ $assetVer('assets/admin/js/input-mask.js') }}"></script>
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendors/datatables/datatables.min.css') }}"> --}}
    {{-- DATATABLES --}}
    {{-- DataTables CSS loaded via localStorage cache-first loader --}}

    {{-- Select2 4.0.3 --}}
    {{-- Select2 CSS loaded via localStorage cache-first loader --}}

    <script>
        // Cache-first loader for non-critical local CSS files.
        // If CSS exists in localStorage, inject immediately without network.
        (function() {
            var STORAGE_PREFIX = 'smt_css_cache_v1:';
            var MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000; // 7 days
            var styles = [
                '{{ asset('vendor/datatables/datatables.min.css') }}?v={{ $assetVer('vendor/datatables/datatables.min.css') }}',
                '{{ asset('vendor/datatables/DataTables-1.12.1/css/dataTables.bootstrap4.min.css') }}?v={{ $assetVer('vendor/datatables/DataTables-1.12.1/css/dataTables.bootstrap4.min.css') }}',
                '{{ asset('vendor/datatables/FixedHeader-3.2.3/css/fixedHeader.bootstrap4.min.css') }}?v={{ $assetVer('vendor/datatables/FixedHeader-3.2.3/css/fixedHeader.bootstrap4.min.css') }}',
                '{{ asset('vendor/datatables/Responsive-2.3.0/css/responsive.bootstrap4.min.css') }}?v={{ $assetVer('vendor/datatables/Responsive-2.3.0/css/responsive.bootstrap4.min.css') }}',
                '{{ asset('vendor/datatables/Responsive-2.3.0/css/responsive.dataTables.min.css') }}?v={{ $assetVer('vendor/datatables/Responsive-2.3.0/css/responsive.dataTables.min.css') }}',
                '{{ asset('assets/admin/cdn-local/css/select2.min.css') }}?v={{ $assetVer('assets/admin/cdn-local/css/select2.min.css') }}',
                '{{ asset('vendor/select2-bootstrap4/dist/select2-bootstrap4.min.css') }}?v={{ $assetVer('vendor/select2-bootstrap4/dist/select2-bootstrap4.min.css') }}'
            ];

            function cacheKey(url) {
                return STORAGE_PREFIX + url;
            }

            function injectCssText(code, url) {
                var s = document.createElement('style');
                s.setAttribute('data-cached-href', url);
                s.textContent = code;
                document.head.appendChild(s);
            }

            function injectCssHref(url) {
                var l = document.createElement('link');
                l.rel = 'stylesheet';
                l.href = url;
                document.head.appendChild(l);
            }

            styles.forEach(function(url) {
                try {
                    var key = cacheKey(url);
                    var raw = localStorage.getItem(key);
                    if (raw) {
                        var parsed = JSON.parse(raw);
                        if (parsed && parsed.ts && parsed.code && (Date.now() - parsed.ts) < MAX_AGE_MS) {
                            injectCssText(parsed.code, url);
                            return;
                        }
                    }

                    fetch(url, { credentials: 'same-origin' })
                        .then(function(resp) { return resp.ok ? resp.text() : Promise.reject(new Error('bad status')); })
                        .then(function(code) {
                            try {
                                localStorage.setItem(key, JSON.stringify({ ts: Date.now(), code: code }));
                            } catch (e) {}
                            injectCssText(code, url);
                        })
                        .catch(function() {
                            injectCssHref(url);
                        });
                } catch (e) {
                    injectCssHref(url);
                }
            });
        })();
    </script>

    <script>
        // Lazy-load reCAPTCHA hanya di halaman yang memerlukannya.
        document.addEventListener('DOMContentLoaded', function() {
            var needRecaptcha = document.querySelector('.g-recaptcha, [data-recaptcha], #recaptcha-container');
            if (!needRecaptcha) return;

            var script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        });
    </script>
</head>
