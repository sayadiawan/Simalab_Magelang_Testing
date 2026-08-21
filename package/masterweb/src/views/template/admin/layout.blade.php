<!DOCTYPE html>
<html lang="en">
@include('masterweb::template.admin.metadata')
@yield('css')

<body onload="startTime()">
    {{-- Global loader untuk menunggu semua asset/CDN (TinyMCE, Flatpickr, Select2, dll) siap --}}
    <div id="global-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Memuat komponen halaman...</div>
        <div class="loader-subtext">Mohon tunggu sebentar, sedang memuat editor dan library pendukung.</div>
    </div>
    <script>
        // Early fail-safe: jangan biarkan overlay menutup lama
        // meskipun script berat di bawah belum selesai parse/execute.
        (function() {
            var loader = document.getElementById('global-loader');
            if (!loader) return;

            function fastHide() {
                if (!loader || loader.classList.contains('hidden')) return;
                loader.classList.add('hidden');
                setTimeout(function() {
                    if (loader) loader.style.display = 'none';
                }, 250);
                try { sessionStorage.setItem('smt_loader_seen', '1'); } catch (e) {}
            }

            var seenBefore = false;
            try { seenBefore = sessionStorage.getItem('smt_loader_seen') === '1'; } catch (e) {}
            var fallbackMs = seenBefore ? 350 : 900;

            // Hide cepat saat DOM siap, apapun status library berat.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(fastHide, fallbackMs);
                });
            } else {
                setTimeout(fastHide, fallbackMs);
            }

            // Hard fallback jika event di atas terlambat.
            setTimeout(fastHide, 1500);
        })();
    </script>

    <div class="container-scroller">
        @include('masterweb::template.admin.header')
        <div class="container-fluid page-body-wrapper">
            @include('masterweb::template.admin.asside')
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>

            </div>
        </div>
    </div>
    @include('masterweb::template.admin.scripts')
    <script>
        $(document).ready(function() {
            var SIDEBAR_EXPANDED_CLICK = 'sidebar-expanded-click';
            var SIDEBAR_WAS_ICON_ONLY = 'sidebar-was-icon-only';

            function isDesktopSidebar() {
                return window.matchMedia('(min-width: 992px)').matches;
            }

            function expandSidebarFromIconOnly() {
                var $body = $('body');
                if (!$body.hasClass('sidebar-icon-only')) {
                    return false;
                }

                $body.addClass(SIDEBAR_WAS_ICON_ONLY)
                    .addClass(SIDEBAR_EXPANDED_CLICK)
                    .removeClass('sidebar-icon-only');
                return true;
            }

            function restoreIconOnlyIfNeeded() {
                var $body = $('body');
                if (!$body.hasClass(SIDEBAR_WAS_ICON_ONLY)) {
                    return;
                }

                $body.removeClass(SIDEBAR_EXPANDED_CLICK)
                    .removeClass(SIDEBAR_WAS_ICON_ONLY)
                    .addClass('sidebar-icon-only');
                $('#sidebar .collapse.show').collapse('hide');
                $('.sidebar .nav-item').removeClass('hover-open');
            }

            // Fix untuk toggle sidebar di mobile/mini
            // off-canvas.js juga bind click yang sama → double-toggle (buka lalu langsung tutup)
            $('[data-toggle="offcanvas"]').off('click');

            function toggleSidebar() {
                $('.sidebar-offcanvas').toggleClass('active');
                
                // Tambahkan overlay untuk menutup sidebar saat klik di luar
                if ($('.sidebar-offcanvas').hasClass('active')) {
                    if ($('.sidebar-overlay').length === 0) {
                        $('body').append('<div class="sidebar-overlay"></div>');
                    }
                } else {
                    $('.sidebar-overlay').remove();
                }
            }

            // Event handler untuk tombol toggle sidebar mobile
            $(document).off('click.smtOffcanvas', '[data-toggle="offcanvas"]').on('click.smtOffcanvas', '[data-toggle="offcanvas"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });

            // Fallback minimize sidebar (desktop): pastikan tidak double-bind dengan misc.js
            $('[data-toggle="minimize"]').off('click');
            $(document).off('click.smtMinimize', '[data-toggle="minimize"]').on('click.smtMinimize', '[data-toggle="minimize"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $body = $('body');
                $body.removeClass(SIDEBAR_EXPANDED_CLICK).removeClass(SIDEBAR_WAS_ICON_ONLY);
                $('.sidebar .nav-item').removeClass('hover-open');
                $('#sidebar .collapse.show').collapse('hide');
                if ($body.hasClass('sidebar-toggle-display') || $body.hasClass('sidebar-absolute')) {
                    $body.toggleClass('sidebar-hidden');
                } else {
                    $body.toggleClass('sidebar-icon-only');
                }
            });

            // Buka sidebar penuh sebelum Bootstrap collapse diproses (mousedown < click)
            $(document).on('mousedown', '.sidebar .nav-link[data-toggle="collapse"]', function() {
                if (isDesktopSidebar()) {
                    expandSidebarFromIconOnly();
                }
            });

            $(document).on('click.smtSidebarRestore', function(e) {
                if ($(e.target).closest('#sidebar, [data-toggle="minimize"]').length) {
                    return;
                }
                restoreIconOnlyIfNeeded();
            });

            // Tutup sidebar saat klik overlay
            $(document).on('click', '.sidebar-overlay', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('.sidebar-offcanvas').removeClass('active');
                $('.sidebar-overlay').remove();
            });

            // Tutup sidebar saat klik link navigasi di dalam sidebar (mobile)
            // Jangan tutup saat klik menu expand/collapse (#) atau submenu parent.
            $(document).on('click', '.sidebar-offcanvas .nav-link', function() {
                if ($(window).width() > 991) {
                    return;
                }

                if ($(this).is('[data-toggle="collapse"]')) {
                    return;
                }

                var href = $(this).attr('href') || '';
                if (href.charAt(0) === '#') {
                    return;
                }

                setTimeout(function() {
                    $('.sidebar-offcanvas').removeClass('active');
                    $('.sidebar-overlay').remove();
                }, 300);
            });

            // Handle resize - tutup sidebar jika kembali ke desktop
            $(window).on('resize', function() {
                if ($(window).width() > 991) {
                    $('.sidebar-offcanvas').removeClass('active');
                    $('.sidebar-overlay').remove();
                } else {
                    $('body').removeClass(SIDEBAR_EXPANDED_CLICK).removeClass(SIDEBAR_WAS_ICON_ONLY);
                }
            });

            // Pastikan sidebar tertutup saat page load di mobile
            if ($(window).width() <= 991) {
                $('.sidebar-offcanvas').removeClass('active');
                $('.sidebar-overlay').remove();
            }

            // Fix untuk dropdown profile header
            // Pastikan dropdown bekerja dengan baik
            $(document).on('click', '#profileDropdown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $dropdown = $(this).next('.dropdown-menu');
                var $toggle = $(this);
                
                if ($dropdown.length === 0) {
                    console.error('Dropdown menu not found');
                    return;
                }
                
                var isVisible = $dropdown.hasClass('show');
                
                // Tutup semua dropdown lain
                $('.nav-profile .dropdown-menu').removeClass('show');
                $('.nav-profile .dropdown-toggle').removeClass('show');
                
                // Toggle dropdown ini
                if (!isVisible) {
                    // Hitung posisi untuk fixed positioning
                    var toggleOffset = $toggle.offset();
                    var toggleHeight = $toggle.outerHeight();
                    var navbarHeight = $('.navbar').outerHeight() || 60;
                    
                    // Posisi dropdown tepat di bawah navbar
                    var topPosition = toggleOffset.top + toggleHeight + 8;
                    var rightPosition = $(window).width() - toggleOffset.left - $toggle.outerWidth();
                    
                    $dropdown.addClass('show').css({
                        'display': 'block',
                        'position': 'fixed',
                        'top': topPosition + 'px',
                        'right': rightPosition + 'px',
                        'z-index': '99999'
                    });
                    $toggle.addClass('show');
                } else {
                    $dropdown.removeClass('show').css({
                        'display': 'none'
                    });
                    $toggle.removeClass('show');
                }
            });


            // Fix untuk select2 dropdown (Dashboard) - lebih naik
            $(document).on('select2:open', function(e) {
                if ($(e.target).attr('id') === 'smt_navigation') {
                    setTimeout(function() {
                        var $dropdown = $('.select2-dropdown');
                        $dropdown.css({
                            'margin-top': '-8px'
                        });
                    }, 10);
                }
            });

            // Tutup dropdown saat klik di luar
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.nav-profile').length) {
                    $('.nav-profile .dropdown-menu').removeClass('show').css('display', 'none');
                    $('.nav-profile .dropdown-toggle').removeClass('show');
                }
            });

            // Update posisi dropdown saat scroll atau resize (throttled)
            var profileDropdownTicking = false;
            $(window).on('scroll resize', function() {
                if (profileDropdownTicking) return;
                profileDropdownTicking = true;
                requestAnimationFrame(function() {
                var $dropdown = $('#profileDropdown').next('.dropdown-menu');
                if ($dropdown.hasClass('show')) {
                    var $toggle = $('#profileDropdown');
                    var toggleOffset = $toggle.offset();
                    var toggleHeight = $toggle.outerHeight();
                    var rightPosition = $(window).width() - toggleOffset.left - $toggle.outerWidth();
                    
                    $dropdown.css({
                        'position': 'fixed',
                        'top': (toggleOffset.top + toggleHeight + 8) + 'px',
                        'right': rightPosition + 'px',
                        'z-index': '99999'
                    });
                }
                profileDropdownTicking = false;
                });
            });

            // Pastikan dropdown menu tidak tertutup saat klik di dalamnya
            $(document).on('click', '.nav-profile .dropdown-menu', function(e) {
                e.stopPropagation();
            });
        });
    </script>
    @yield('scripts')

</body>

</html>
