@php
    $assetVer = function ($relativePath) {
        $fullPath = public_path($relativePath);
        return @filemtime($fullPath) ?: 0;
    };
@endphp

<!-- plugins:js -->
<script src="{{ asset('assets/admin/vendors/js/vendor.bundle.base.js') }}?v={{ $assetVer('assets/admin/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/jquery.form.min.js') }}"></script>
<script src="{{ asset('assets/admin/vendors/js/vendor.bundle.addons.js') }}?v={{ $assetVer('assets/admin/vendors/js/vendor.bundle.addons.js') }}"></script>

<!-- endinject -->
<!-- inject:js -->
<script src="{{ asset('assets/admin/js/off-canvas.js') }}?v={{ $assetVer('assets/admin/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/admin/js/hoverable-collapse.js') }}?v={{ $assetVer('assets/admin/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('assets/admin/js/misc.js') }}?v={{ $assetVer('assets/admin/js/misc.js') }}"></script>
<script src="{{ asset('assets/admin/js/settings.js') }}?v={{ $assetVer('assets/admin/js/settings.js') }}"></script>
<script src="{{ asset('assets/admin/js/todolist.js') }}?v={{ $assetVer('assets/admin/js/todolist.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap-datetimepicker.min.js') }}?v={{ $assetVer('assets/admin/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/admin/vendors/summernote/dist/summernote-bs4.min.js') }}?v={{ $assetVer('assets/admin/vendors/summernote/dist/summernote-bs4.min.js') }}"></script>

<script type="text/javascript" src="{{ asset('assets/admin/cdn-local/js/tempusdominus-bootstrap-4.min.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/tempusdominus-bootstrap-4.min.js') }}">
</script>
<!-- endinject -->

<script>
    // Cache-first loader for non-critical local JS files.
    // If script content already exists in localStorage, load without network request.
    (function() {
        var STORAGE_PREFIX = 'smt_script_cache_v1:';
        var MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000; // 7 days
        var scripts = [
            '{{ asset('assets/admin/js/data-table.js') }}?v={{ $assetVer('assets/admin/js/data-table.js') }}',
            '{{ asset('assets/admin/js/formpickers.js') }}?v={{ $assetVer('assets/admin/js/formpickers.js') }}',
            '{{ asset('assets/admin/js/form-repeater.js') }}?v={{ $assetVer('assets/admin/js/form-repeater.js') }}',
            '{{ asset('assets/admin/js/tooltips.js') }}?v={{ $assetVer('assets/admin/js/tooltips.js') }}',
            '{{ asset('assets/admin/js/editorDemo.js') }}?v={{ $assetVer('assets/admin/js/editorDemo.js') }}'
        ];

        function cacheKey(url) {
            return STORAGE_PREFIX + url;
        }

        function injectScriptText(code, url) {
            var s = document.createElement('script');
            s.setAttribute('data-cached-src', url);
            s.text = code;
            document.head.appendChild(s);
        }

        function injectScriptSrc(url) {
            var s = document.createElement('script');
            s.src = url;
            document.head.appendChild(s);
        }

        scripts.forEach(function(url) {
            try {
                var key = cacheKey(url);
                var raw = localStorage.getItem(key);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    if (parsed && parsed.ts && parsed.code && (Date.now() - parsed.ts) < MAX_AGE_MS) {
                        injectScriptText(parsed.code, url);
                        return;
                    }
                }

                fetch(url, { credentials: 'same-origin' })
                    .then(function(resp) { return resp.ok ? resp.text() : Promise.reject(new Error('bad status')); })
                    .then(function(code) {
                        try {
                            localStorage.setItem(key, JSON.stringify({ ts: Date.now(), code: code }));
                        } catch (e) {}
                        injectScriptText(code, url);
                    })
                    .catch(function() {
                        injectScriptSrc(url);
                    });
            } catch (e) {
                injectScriptSrc(url);
            }
        });
    })();
</script>

<script src="{{ asset('assets/admin/vendors/tinymce/tinymce.min.js') }}?v={{ $assetVer('assets/admin/vendors/tinymce/tinymce.min.js') }}"></script>
<script>
    // Ensure TinyMCE uses local assets and never tries to load from CDN
    if (typeof tinymce !== 'undefined') {
        // Set baseURL to local assets path (relative to current location)
        var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
        if (tinymce.baseURL === undefined ||
            tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
            tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
            tinymce.baseURL = tinymceBasePath;
        }
        // Ensure theme is set to 'modern' by default (available in local assets)
        if (!tinymce.settings) {
            tinymce.settings = {};
        }
        if (!tinymce.settings.theme) {
            tinymce.settings.theme = 'modern';
        }
    }
    /*Tinymce editor*/
    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML =
            h + ":" + m + ":" + s;
        var t = setTimeout(startTime, 500);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }; // add zero in front of numbers < 10
        return i;
    }


    if ($('.texteditor').length) {
        tinymce.init({
            selector: '.texteditor',
            height: 500,
            theme: 'modern',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen',
                'insertdatetime media nonbreaking save table contextmenu directionality',
                'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help'
            ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
            image_advtab: true,
            templates: [{
                    title: 'Test template 1',
                    content: 'Test 1'
                },
                {
                    title: 'Test template 2',
                    content: 'Test 2'
                }
            ],
            content_css: []
        });
    }
</script>

{{-- DATATABLES --}}
{{-- <script src="{{ asset('assets/admin/vendors/datatables/datatables.min.js') }}"></script> --}}
<script src="{{ asset('vendor/datatables/datatables.min.js') }}?v={{ $assetVer('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.12.1/js/dataTables.bootstrap4.min.js') }}?v={{ $assetVer('vendor/datatables/DataTables-1.12.1/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/FixedHeader-3.2.3/js/fixedHeader.bootstrap4.min.js') }}?v={{ $assetVer('vendor/datatables/FixedHeader-3.2.3/js/fixedHeader.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/Responsive-2.3.0/js/responsive.bootstrap4.min.js') }}?v={{ $assetVer('vendor/datatables/Responsive-2.3.0/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/Responsive-2.3.0/js/responsive.dataTables.min.js') }}?v={{ $assetVer('vendor/datatables/Responsive-2.3.0/js/responsive.dataTables.min.js') }}"></script>

{{-- Select2 4.0.3 --}}
<script src="{{ asset('assets/admin/cdn-local/js/select2.min.js') }}?v={{ $assetVer('assets/admin/cdn-local/js/select2.min.js') }}" onload="window.select2Loaded = true;"></script>

<script>
    function deferNonCriticalInit(callback) {
        if (window.requestIdleCallback) {
            requestIdleCallback(callback, { timeout: 1500 });
        } else {
            setTimeout(callback, 200);
        }
    }

    // Pastikan jQuery dan select2 sudah ter-load sebelum inisialisasi
    (function() {
        var maxRetries = 10;
        var retryCount = 0;

        function initializeSelect2() {
            retryCount++;
            
            // Cek apakah jQuery dan select2 sudah tersedia
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery(document).ready(function($) {
                    // Inisialisasi select2 untuk semua elemen dengan class smt-select2
                    $('.smt-select2').each(function() {
                        // Cek apakah sudah diinisialisasi untuk menghindari duplikasi
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            try {
                                $(this).select2({
                                    theme: 'bootstrap4',
                                });
                            } catch (e) {
                                console.error('Error initializing select2:', e);
                            }
                        }
                    });

                    // bind change event to select navigation (jika ada)
                    if ($('#smt_navigation').length > 0) {
                        $('#smt_navigation').off('change.select2-nav').on('change.select2-nav', function() {
                            var url = $(this).val(); // get selected value
                            if (url) { // require a URL
                                window.location = url; // redirect
                            }
                            return false;
                        });
                    }
                });
            } else if (retryCount < maxRetries) {
                // Jika belum ter-load dan belum mencapai max retries, coba lagi
                setTimeout(initializeSelect2, 200);
            } else {
                console.error('Select2 library failed to load after ' + maxRetries + ' attempts');
            }
        }

        // Mulai inisialisasi setelah DOM ready atau setelah script ter-load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                deferNonCriticalInit(initializeSelect2);
            });
        } else {
            deferNonCriticalInit(initializeSelect2);
        }
    })();
</script>

<script>
    // Buat semua table di halaman admin otomatis responsive
    // - Membungkus <table> dengan <div class="table-responsive">
    // - Kecuali table yang:
    //   * sudah berada di dalam .table-responsive
    //   * berada di dalam wrapper DataTables (.dataTables_wrapper)
    //   * punya class .table-no-responsive (opsi untuk menonaktifkan)
    jQuery(function($) {
        function makeTablesResponsive(context) {
            var $root = context ? $(context) : $(document);

            $root.find('table').each(function() {
                var $table = $(this);

                // Skip jika:
                if (
                    $table.closest('.table-responsive').length || // sudah responsive
                    $table.closest('.dataTables_wrapper').length || // datatables wrapper
                    $table.hasClass('table-no-responsive') || // diminta tidak responsive
                    $table.closest('.table-no-responsive').length // parent diminta tidak responsive
                ) {
                    return;
                }

                // Bungkus dengan .table-responsive
                $table.wrap('<div class="table-responsive auto-table-responsive"></div>');
            });
        }

        // Inisialisasi saat halaman pertama kali load (idle agar first paint lebih cepat)
        deferNonCriticalInit(function() {
            makeTablesResponsive();
        });

        // Jika ada konten yang dimuat via AJAX dan butuh table responsive,
        // panggil window.makeTablesResponsive(containerElement)
        window.makeTablesResponsive = makeTablesResponsive;
    });

    // Global AJAX error handler untuk handle 419 (CSRF Token Expired)
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        // Handle 419 CSRF Token Expired
        if (xhr.status === 419) {
            // Cek apakah ini AJAX request atau form submit
            var isAjaxRequest = settings && settings.type && settings.type.toUpperCase() === 'POST';
            
            // Tampilkan notifikasi sebelum redirect ke halaman 419
            if (typeof swal !== 'undefined') {
                swal({
                    title: "Session Expired",
                    text: "Session Anda telah berakhir. Anda akan diarahkan ke halaman refresh.",
                    icon: "warning",
                    timer: 2000,
                    buttons: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false
                }).then(function() {
                    // Redirect ke halaman 419 (akan auto-refresh)
                    window.location.reload();
                });
            } else {
                // Jika SweetAlert tidak tersedia, langsung reload
                alert('Session Anda telah berakhir. Halaman akan di-refresh.');
                window.location.reload();
            }
            return false; // Prevent other error handlers
        }
    });

    // Setup AJAX untuk handle 419 secara global
    $.ajaxSetup({
        statusCode: {
            419: function() {
                // Handle 419 CSRF Token Expired
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Session Expired",
                        text: "Session Anda telah berakhir. Halaman akan di-refresh otomatis.",
                        icon: "warning",
                        timer: 2000,
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    alert('Session Anda telah berakhir. Halaman akan di-refresh.');
                    window.location.reload();
                }
            }
        }
    });

    /**
     * Front-end localStorage cache for GET AJAX
     * - Default aktif untuk GET same-origin
     * - TTL pendek (4G optimization) agar data tetap cukup fresh
     * - Skip endpoint sensitif / side-effect
     */
    (function($) {
        if (!$ || !$.ajax) return;

        var CACHE_PREFIX = 'smt_ajax_cache_v1:';
        var DEFAULT_TTL_MS = 90 * 1000; // 90 detik
        var SKIP_URL_RE = /(\/print|\/download|\/export|\/signature|\/sign|\/blob|\/pdf)/i;

        function isSameOrigin(url) {
            try {
                var target = new URL(url, window.location.origin);
                return target.origin === window.location.origin;
            } catch (e) {
                return false;
            }
        }

        function normalizeUrl(url) {
            try {
                var u = new URL(url, window.location.origin);
                // Sort query params to avoid cache miss by order.
                var params = Array.from(u.searchParams.entries()).sort(function(a, b) {
                    return a[0] < b[0] ? -1 : (a[0] > b[0] ? 1 : 0);
                });
                u.search = '';
                params.forEach(function(pair) {
                    u.searchParams.append(pair[0], pair[1]);
                });
                return u.toString();
            } catch (e) {
                return url;
            }
        }

        function buildCacheKey(opts) {
            var url = normalizeUrl(opts.url || window.location.href);
            var dataPart = '';
            if (opts.data && typeof opts.data !== 'function') {
                if (typeof opts.data === 'string') {
                    dataPart = opts.data;
                } else {
                    try {
                        dataPart = JSON.stringify(opts.data);
                    } catch (e) {
                        dataPart = String(opts.data);
                    }
                }
            }
            return CACHE_PREFIX + btoa(unescape(encodeURIComponent(url + '::' + dataPart)));
        }

        function readCache(key, ttl) {
            try {
                var raw = localStorage.getItem(key);
                if (!raw) return null;
                var parsed = JSON.parse(raw);
                if (!parsed || typeof parsed.ts !== 'number') return null;
                if ((Date.now() - parsed.ts) > ttl) return null;
                return parsed.payload;
            } catch (e) {
                return null;
            }
        }

        function writeCache(key, payload) {
            try {
                localStorage.setItem(key, JSON.stringify({
                    ts: Date.now(),
                    payload: payload
                }));
            } catch (e) {
                // localStorage bisa penuh / dibatasi; gagal cache tidak boleh mematahkan flow.
            }
        }

        var _ajax = $.ajax;
        $.ajax = function(url, options) {
            var opts = (typeof url === 'object')
                ? $.extend(true, {}, url)
                : $.extend(true, {}, options || {}, { url: url });

            var method = String(opts.type || opts.method || 'GET').toUpperCase();
            var ttl = Number(opts.localCacheTTL || DEFAULT_TTL_MS);
            var cacheEnabled = opts.localCache !== false; // default true
            var canUseCache = (
                method === 'GET' &&
                cacheEnabled &&
                ttl > 0 &&
                opts.async !== false
            );

            if (!canUseCache || !opts.url || !isSameOrigin(opts.url) || SKIP_URL_RE.test(opts.url)) {
                return _ajax.apply($, arguments);
            }

            var key = buildCacheKey(opts);
            var cached = readCache(key, ttl);

            // Serve cache first (fast path), then refresh in background.
            if (cached !== null && opts.cacheFirst !== false) {
                var dfd = $.Deferred();
                var fakeXhr = dfd.promise();

                setTimeout(function() {
                    try {
                        if (typeof opts.success === 'function') {
                            opts.success(cached, 'success', fakeXhr);
                        }
                        if (typeof opts.complete === 'function') {
                            opts.complete(fakeXhr, 'success');
                        }
                        dfd.resolve(cached, 'success', fakeXhr);
                    } catch (e) {
                        dfd.reject(fakeXhr, 'error', e);
                    }
                }, 0);

                // Background refresh to keep cache fresh.
                if (opts.backgroundRefresh !== false) {
                    var refreshOpts = $.extend(true, {}, opts, {
                        localCache: false
                    });
                    _ajax.call($, refreshOpts).done(function(data) {
                        writeCache(key, data);
                    });
                }

                return fakeXhr;
            }

            // Network path, then cache success payload.
            var jqXHR = _ajax.call($, opts);
            jqXHR.done(function(data) {
                writeCache(key, data);
            });
            return jqXHR;
        };
    })(window.jQuery);
</script>

{{-- Global loader controller: tunggu sampai library utama (jQuery, TinyMCE, Select2, Flatpickr) siap --}}
<script>
    (function() {
        var loader = document.getElementById('global-loader');
        if (!loader) return;

        var maxWaitMs = 2500; // jangan blokir render terlalu lama
        var startTime = Date.now();
        var hasCheckedOnce = false;

        function hideLoader() {
            if (!loader || loader.classList.contains('hidden')) return;
            loader.classList.add('hidden');
            setTimeout(function() {
                if (loader) loader.style.display = 'none';
            }, 350);
        }

        function isLibraryReady() {
            // Loader global cukup menunggu jQuery + DOM,
            // komponen berat (TinyMCE/Select2/Flatpickr) boleh menyusul.
            return typeof window.jQuery !== 'undefined';
        }

        function checkReady() {
            hasCheckedOnce = true;
            
            if (isLibraryReady()) {
                hideLoader();
                return;
            }

            var elapsed = Date.now() - startTime;
            if (elapsed > maxWaitMs) {
                // Timeout: jangan biarkan loader menggantung selamanya
                console.warn('Loader timeout setelah ' + (elapsed / 1000).toFixed(1) + ' detik, hiding loader...');
                hideLoader();
                return;
            }

            setTimeout(checkReady, 100); // Check lebih sering (100ms instead of 150ms)
        }

        // Mulai pengecekan segera setelah DOM ready (tidak perlu tunggu window load)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(checkReady, 300);
            });
        } else {
            // DOM sudah ready, langsung check
            setTimeout(checkReady, 300);
        }

        // Safety net: jika window load event terpanggil, check sekali lagi
        window.addEventListener('load', function() {
            if (!hasCheckedOnce) {
                setTimeout(checkReady, 200);
            }
        });

        // Safety net tambahan: force hide setelah 8 detik maksimum
        setTimeout(function() {
            if (loader && !loader.classList.contains('hidden')) {
                console.warn('Force hiding loader after maximum wait time');
                hideLoader();
            }
        }, maxWaitMs);
    })();
</script>
