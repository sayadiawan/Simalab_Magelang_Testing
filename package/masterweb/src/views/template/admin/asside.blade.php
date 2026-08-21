@php
    $user = Auth()->user();
    $level = $user->getlevel->level;
    $privilege = \Smt\Masterweb\Models\Privileges::where('id', $user->level)->first();
@endphp
<style>
    /* Default state - semua link putih */
    .sidebar .nav .nav-item .nav-link {
        color: white !important;
        background-color: transparent !important;
        transition: none !important;
    }

    .sidebar .nav .nav-item .nav-link .menu-title {
        color: white !important;
    }

    .sidebar .nav .nav-item .nav-link i {
        color: white !important;
    }

    .sidebar .nav .nav-item .nav-link .menu-arrow {
        color: white !important;
    }

    .sidebar .nav .nav-item .nav-link i.menu-arrow {
        color: white !important;
    }

    .sidebar .menu-arrow::before {
        color: white !important;
    }

    /* Nav profile tidak bisa dihover */
    .sidebar .nav .nav-item.nav-profile .nav-link {
        pointer-events: none;
        cursor: default;
    }

    /* Hover state untuk semua menu utama (kecuali nav-profile) */
    .sidebar .nav>.nav-item:not(.nav-profile)>.nav-link:hover {
        background-color: rgba(22, 168, 146, 0.35) !important;
        color: #ffffff !important;
    }

    .sidebar .nav>.nav-item:not(.nav-profile)>.nav-link:hover .menu-title {
        color: #ffffff !important;
    }

    .sidebar .nav>.nav-item:not(.nav-profile)>.nav-link:hover i {
        color: #ffffff !important;
    }

    .sidebar .nav>.nav-item:not(.nav-profile)>.nav-link:hover .menu-arrow {
        color: #ffffff !important;
    }

    .sidebar .nav>.nav-item:not(.nav-profile)>.nav-link:hover .menu-arrow::before {
        color: #ffffff !important;
        border-color: #ffffff !important;
    }

    /* Default state untuk submenu */
    .sidebar .nav .sub-menu .nav-item .nav-link {
        color: white !important;
        background-color: transparent !important;
        transition: none !important;
    }

    /* Hover state untuk submenu - background biru lebih terang, text putih */
    .sidebar .nav .sub-menu .nav-item .nav-link:hover {
        background-color: rgba(22, 168, 146, 0.35) !important;
        color: #ffffff !important;
        transition: none !important;
    }

    /* Arrow styling */
    .sidebar .menu-arrow,
    .sidebar .menu-arrow::after,
    .sidebar .menu-arrow::before {
        color: white !important;
        border-color: white !important;
    }

    /* Responsive Sidebar Toggle untuk Mobile */
    @media (max-width: 991px) {
        .sidebar-offcanvas {
            position: fixed;
            top: 60px;
            left: -280px;
            width: 280px;
            height: calc(100vh - 60px);
            transition: left 0.3s ease-in-out;
            z-index: 1050;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-offcanvas.active {
            left: 0;
        }

        /* Overlay untuk menutup sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 60px;
            left: 0;
            width: 100vw;
            height: calc(100vh - 60px);
            background: rgba(0, 0, 0, 0.5);
            z-index: 1049;
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Pastikan konten tidak tertutup sidebar */
        .page-body-wrapper {
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar-offcanvas.active ~ .main-panel {
            margin-left: 0;
        }
    }

    /* Fix untuk tombol toggle di mobile */
    @media (max-width: 991px) {
        .navbar-toggler[data-toggle="offcanvas"] {
            display: block !important;
            z-index: 1051;
            position: relative;
            cursor: pointer;
        }

        .navbar-toggler[data-toggle="offcanvas"]:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }
    }

    /* Pastikan sidebar bisa di-scroll di mobile */
    @media (max-width: 991px) {
        .sidebar-offcanvas {
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Desktop: sidebar icon-only dibuka lewat klik menu expand */
    @media (min-width: 992px) {
        body.sidebar-expanded-click .sidebar {
            display: block !important;
            visibility: visible !important;
            width: 255px !important;
            min-width: 255px !important;
            max-width: 255px !important;
            transform: none !important;
            opacity: 1 !important;
        }

        body.sidebar-expanded-click .sidebar .nav .nav-item .collapse.show,
        body.sidebar-expanded-click .sidebar .nav .nav-item .collapsing {
            display: block !important;
        }
    }
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #0b3a5c;">
    <ul class="nav">
        <li class="nav-item nav-profile">
            <div class="nav-link menu">
                <div class="profile-image">
                    {{-- <img src="{{ ($user->photo == NULL) ? asset('assets/admin/images/logo/favicon.png') : asset('/storage/photo/'.$user->photo)}}" alt="image"/> --}}

                    @if (Storage::disk('public')->exists('photo/' . $user->photo) && $user->photo != null)
                        <img src="{{ Storage::url('photo/' . $user->photo) }}" alt="profile" />
                    @else
                        <img src="{{ asset('assets/admin/images/logo/favicon.png') }}" alt="profile" />
                    @endif
                </div>
                <div class="profile-name">
                    <p class="name" style="color: white;">
                        Hai, {{ explode(' ', $user->name)[0] }}
                    </p>
                    <p class="designation">
                        {{-- {{$privilege->name}} --}}
                    </p>
                </div>
            </div>
        </li>

        {{-- LIST MENU --}}
        @php
            $parent = \Smt\Masterweb\Models\AdminMenu::all()->sortBy('order')->where('upmenu', '0');

            // dd($parent);

        @endphp
        @foreach ($parent as $menu)
            @php
                $role = \Smt\Masterweb\Models\Role::where('menu_id', $menu->id)
                    ->where('privilege_id', $privilege->id)
                    ->first();

                if ($role != null) {
                    if ($role->read == 0) {
                        continue;
                    }
                }

                $child = \Smt\Masterweb\Models\AdminMenu::all()->sortBy('order')->where('upmenu', $menu->id);
            @endphp
            @if (SmtHelp::create_link($menu->name) == 'klinik')
                @if (!isset($user->laboratorium))
                    <li class="nav-item menu">
                        @if (count($child) > 0)
                            <a class="nav-link" data-toggle="collapse"
                                href="#menu-{{ SmtHelp::create_link($menu->name) }}" aria-expanded="false"
                                aria-controls="page-layouts">
                                <i class="{{ $menu->icon }} menu-icon"></i><span
                                    class="menu-title">{{ $menu->name }}</span>
                                <i class="menu-arrow"></i>
                            </a>

                            <div class="collapse" id="menu-{{ SmtHelp::create_link($menu->name) }}">
                                <ul class="nav flex-column sub-menu">
                                    @foreach ($child as $submenu)
                                        @php
                                            $role = \Smt\Masterweb\Models\Role::where('menu_id', $submenu->id)
                                                ->where('privilege_id', $privilege->id)
                                                ->first();

                                            if ($role != null) {
                                                if ($role->read == 0) {
                                                    continue;
                                                }
                                            }

                                            // Filter untuk menu Baku Mutu: jika user adalah analis dengan laboratorium, hanya tampilkan submenu sesuai lab-nya
                                            $showSubmenu = true;

                                            if (
                                                stripos($menu->name, 'Baku Mutu') !== false &&
                                                isset($user->laboratorium) &&
                                                ($level == 'ANLS' || $level == 'ALAB')
                                            ) {
                                                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                                                $submenuName = strtolower($submenu->name ?? '');

                                                // Cek apakah submenu sesuai dengan lab user
                                                if (
                                                    $labName == 'mikrobiologi' &&
                                                    stripos($submenuName, 'mikro') === false
                                                ) {
                                                    $showSubmenu = false;
                                                } elseif (
                                                    $labName == 'klinik' &&
                                                    stripos($submenuName, 'klinik') === false
                                                ) {
                                                    $showSubmenu = false;
                                                } elseif (
                                                    $labName == 'kimia' &&
                                                    stripos($submenuName, 'kimia') === false
                                                ) {
                                                    $showSubmenu = false;
                                                }
                                            }
                                        @endphp
                                        @if ($showSubmenu)
                                            <li class="nav-item"> <a class="nav-link"
                                                    href="{{ URL::to($submenu->link) }}">{{ $submenu->name }}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            @if ($menu->link != 'elits-analys')
                                <a class="nav-link" href="{{ $menu->link }}">
                                    <i class="{{ $menu->icon }} menu-icon"></i><span
                                        class="menu-title">{{ $menu->name }}</span>
                                </a>
                            @endif
                            {{-- @if ($user->laboratorium->nama_laboratorium == 'Klinik' && $menu->link != '/elits-analys')
                <a class="nav-link" href="{{ $menu->link }}">
                  <i class="{{ $menu->icon }} menu-icon"></i><span class="menu-title">{{ $menu->name }}</span>
                </a>
              @else
                <a class="nav-link" href="{{ $menu->link }}">
                  <i class="{{ $menu->icon }} menu-icon"></i><span class="menu-title">{{ $menu->name }}</span>
                </a>
              @endif --}}
                        @endif
                    </li>
                @elseif ($user->laboratorium->nama_laboratorium == 'Klinik')
                    <li class="nav-item menu">
                        @if (count($child) > 0)
                            <a class="nav-link" data-toggle="collapse"
                                href="#menu-{{ SmtHelp::create_link($menu->name) }}" aria-expanded="false"
                                aria-controls="page-layouts">
                                <i class="{{ $menu->icon }} menu-icon"></i><span
                                    class="menu-title">{{ $menu->name }}</span>
                                <i class="menu-arrow"></i>
                            </a>

                            <div class="collapse" id="menu-{{ SmtHelp::create_link($menu->name) }}">
                                <ul class="nav flex-column sub-menu">
                                    @foreach ($child as $submenu)
                                        @php
                                            $role = \Smt\Masterweb\Models\Role::where('menu_id', $submenu->id)
                                                ->where('privilege_id', $privilege->id)
                                                ->first();

                                            if ($role != null) {
                                                if ($role->read == 0) {
                                                    continue;
                                                }
                                            }

                                            // Filter untuk menu Baku Mutu: jika user adalah analis dengan laboratorium, hanya tampilkan submenu sesuai lab-nya
                                            $showSubmenu = true;
                                            if (
                                                stripos($menu->name, 'Baku Mutu') !== false &&
                                                isset($user->laboratorium) &&
                                                ($level == 'ANLS' || $level == 'ALAB')
                                            ) {
                                                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                                                $submenuName = strtolower($submenu->name ?? '');

                                                // Cek apakah submenu sesuai dengan lab user
                                                if (
                                                    $labName == 'mikrobiologi' &&
                                                    stripos($submenuName, 'mikro') === false
                                                ) {
                                                    $showSubmenu = false;
                                                } elseif (
                                                    $labName == 'klinik' &&
                                                    stripos($submenuName, 'klinik') === false
                                                ) {
                                                    $showSubmenu = false;
                                                } elseif (
                                                    $labName == 'kimia' &&
                                                    stripos($submenuName, 'kimia') === false
                                                ) {
                                                    $showSubmenu = false;
                                                }
                                            }
                                        @endphp
                                        @if ($showSubmenu)
                                            <li class="nav-item"> <a class="nav-link"
                                                    href="{{ URL::to($submenu->link) }}">{{ $submenu->name }}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            @if ($menu->link != 'elits-analys')
                                <a class="nav-link" href="{{ $menu->link }}">
                                    <i class="{{ $menu->icon }} menu-icon"></i><span
                                        class="menu-title">{{ $menu->name }}</span>
                                </a>
                            @endif
                        @endif
                    </li>
                @endif
            @else
                <li class="nav-item menu">
                    @if (count($child) > 0)
                        <a class="nav-link" data-toggle="collapse" href="#menu-{{ SmtHelp::create_link($menu->name) }}"
                            aria-expanded="false" aria-controls="page-layouts">
                            <i class="{{ $menu->icon }} menu-icon"></i><span
                                class="menu-title">{{ $menu->name }}</span>
                            <i class="menu-arrow"></i>
                        </a>

                        <div class="collapse" id="menu-{{ SmtHelp::create_link($menu->name) }}">
                            <ul class="nav flex-column sub-menu">
                                @foreach ($child as $submenu)
                                    @php
                                        $role = \Smt\Masterweb\Models\Role::where('menu_id', $submenu->id)
                                            ->where('privilege_id', $privilege->id)
                                            ->first();

                                        if ($role != null) {
                                            if ($role->read == 0) {
                                                continue;
                                            }
                                        }

                                        // Filter untuk menu Baku Mutu: jika user adalah analis dengan laboratorium, hanya tampilkan submenu sesuai lab-nya
                                        $showSubmenu = true;
                                        if (
                                            stripos($menu->name, 'Baku Mutu') !== false &&
                                            isset($user->laboratorium) &&
                                            ($level == 'ANLS' || $level == 'ALAB')
                                        ) {
                                            $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                                            $submenuName = strtolower($submenu->name ?? '');

                                            // Cek apakah submenu sesuai dengan lab user
                                            if (
                                                $labName == 'mikrobiologi' &&
                                                stripos($submenuName, 'mikro') === false
                                            ) {
                                                $showSubmenu = false;
                                            } elseif (
                                                $labName == 'klinik' &&
                                                stripos($submenuName, 'klinik') === false
                                            ) {
                                                $showSubmenu = false;
                                            } elseif ($labName == 'kimia' && stripos($submenuName, 'kimia') === false) {
                                                $showSubmenu = false;
                                            }
                                        }
                                    @endphp
                                    @if ($showSubmenu)
                                        <li class="nav-item"> <a class="nav-link"
                                                href="{{ URL::to($submenu->link) }}">{{ $submenu->name }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @else
                        @if (isset($user->laboratorium))
                            @if ($user->laboratorium->nama_laboratorium == 'Klinik' && $menu->link != '/elits-analys')
                                <a class="nav-link" href="{{ $menu->link }}">
                                    <i class="{{ $menu->icon }} menu-icon"></i><span
                                        class="menu-title">{{ $menu->name }}</span>
                                </a>
                            @elseif ($user->laboratorium->nama_laboratorium != 'Klinik' && $menu->link != '/elits-analys/klinik')
                                <a class="nav-link" href="{{ $menu->link }}">
                                    <i class="{{ $menu->icon }} menu-icon"></i><span
                                        class="menu-title">{{ $menu->name }}</span>
                                </a>
                            @endif
                        @else
                            <a class="nav-link" href="{{ $menu->link }}">
                                <i class="{{ $menu->icon }} menu-icon"></i><span
                                    class="menu-title">{{ $menu->name }}</span>
                            </a>
                        @endif
                    @endif
                </li>
            @endif
        @endforeach
        <li class="nav-item menu">
            <a class="nav-link" href="{{ asset('documentation/index.html') }}" target="_blank">
                <i class="fas fa-book-open menu-icon"></i><span class="menu-title">Panduan Sistem</span>
            </a>
        </li>
        <li class="nav-item menu">
            <a class="nav-link" href="{{ route('logout') }}"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-power-off menu-icon"></i><span class="menu-title">Logout</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</nav>
