@php
    $user = Auth()->user();
    $level = $user->getlevel->level;
    $privilege = \Smt\Masterweb\Models\Privileges::where('id', $user->level)->first();
@endphp
<style>
    /* Sidebar minimize - hide text, show logo only */
    @media (min-width: 992px) {
        body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-text,
        body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-text p,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-text,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-text p {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
        }
        
        body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo {
            display: none !important;
        }
        
        body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini img,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini img {
            width: 40px !important;
            height: auto !important;
            margin: 0 auto !important;
            padding: 0 !important;
            display: block !important;
        }
        
        body.sidebar-icon-only .navbar .navbar-brand-wrapper,
        .sidebar-icon-only .navbar .navbar-brand-wrapper {
            justify-content: center !important;
            align-items: center !important;
            padding: 0 !important;
        }
        
        body.sidebar-icon-only .navbar .navbar-brand-wrapper > div:not(.brand-logo-mini),
        .sidebar-icon-only .navbar .navbar-brand-wrapper > div:not(.brand-logo-mini) {
            display: none !important;
        }
    }

    /* Responsive Header Styles */
    @media (max-width: 1200px) {
        .navbar .navbar-brand-wrapper .brand-text {
            font-size: 0.85rem;
        }
        
        .navbar .nav-link {
            padding: 0.5rem 0.75rem !important;
        }
    }

    @media (max-width: 992px) {
        /* Hide brand text di tablet */
        .navbar .navbar-brand-wrapper .brand-text {
            display: none !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo {
            margin-right: 10px !important;
            margin-left: 10px !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo img {
            width: 35px !important;
        }
        
        /* Responsive navigation dropdown */
        .navbar .nav-search .nav-link {
            width: 300px !important;
            max-width: 100%;
        }
        
        .navbar .nav-search .form-control {
            font-size: 0.875rem;
        }
    }

    @media (max-width: 768px) {
        /* Brand wrapper lebih compact */
        .navbar .navbar-brand-wrapper {
            min-width: 50px !important;
            max-width: 60px !important;
            flex-shrink: 0;
        }
        
        .navbar .navbar-brand-wrapper .brand-text {
            display: none !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo {
            margin-right: 5px !important;
            margin-left: 5px !important;
            padding: 0 !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo img {
            width: 30px !important;
            margin: 0 !important;
        }
        
        /* Navigation dropdown lebih kecil */
        .navbar .nav-search {
            display: none !important;
        }
        
        /* Timer dan user profile lebih compact */
        .navbar-nav-right {
            flex-wrap: nowrap;
            gap: 0.25rem;
        }
        
        .navbar-nav-right .nav-item {
            flex-shrink: 0;
        }
        
        .navbar-nav-right .nav-item #txt {
            font-size: 0.75rem;
            padding: 0;
            white-space: nowrap;
        }
        
        .navbar-nav-right .nav-profile .nav-link span {
            display: none;
        }
        
        .navbar-nav-right .nav-profile .nav-link {
            padding: 0.5rem 0.5rem !important;
        }
        
        .navbar-nav-right .nav-profile .nav-link img {
            width: 30px !important;
            height: 30px !important;
            margin: 0 !important;
        }
        
        /* Navbar menu wrapper */
        .navbar-menu-wrapper {
            flex-wrap: nowrap;
            overflow-x: auto;
        }
        
        /* Navbar toggler */
        .navbar-toggler {
            padding: 0.25rem 0.5rem;
            margin-right: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        /* Brand wrapper minimal */
        .navbar .navbar-brand-wrapper {
            min-width: 45px !important;
            max-width: 55px !important;
            padding: 0 !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .navbar .navbar-brand-wrapper .brand-logo img {
            width: 25px !important;
            margin: 0 !important;
        }
        
        /* Timer lebih kecil atau sembunyikan */
        .navbar-nav-right .nav-item #txt {
            font-size: 0.65rem;
            padding: 0;
        }
        
        /* Profile image lebih kecil */
        .navbar-nav-right .nav-profile .nav-link {
            padding: 0.4rem 0.4rem !important;
        }
        
        .navbar-nav-right .nav-profile .nav-link img {
            width: 25px !important;
            height: 25px !important;
        }
        
        /* Navbar toggler */
        .navbar-toggler {
            padding: 0.2rem 0.4rem;
            font-size: 0.9rem;
            margin-right: 0.25rem;
        }
        
        /* Pastikan navbar tidak overflow */
        .navbar-menu-wrapper {
            min-width: 0;
            overflow-x: auto;
        }
        
        .navbar-nav-right {
            min-width: 0;
        }
    }
    
    /* Fix untuk semua ukuran - pastikan tidak ada overlap */
    .navbar-brand-wrapper {
        flex-shrink: 0;
    }
    
    .navbar-menu-wrapper {
        flex: 1 1 auto;
        min-width: 0;
    }
    
    .navbar-nav {
        flex-wrap: nowrap;
    }
    
    .navbar-nav-right {
        margin-left: auto;
    }

    /* Fix untuk overflow */
    .navbar {
        overflow-x: auto;
        overflow-y: visible;
        min-height: 60px;
    }

    /* Fix untuk dropdown profile */
    .navbar {
        position: relative !important;
        z-index: 1030 !important;
    }

    .navbar-nav-right {
        position: relative !important;
        z-index: 1031 !important;
    }

    .nav-profile {
        position: relative !important;
        z-index: 1032 !important;
    }

    .nav-profile .dropdown-menu {
        display: none !important;
        position: fixed !important;
        top: auto !important;
        right: 15px !important;
        left: auto !important;
        z-index: 99999 !important;
        min-width: 200px !important;
        margin-top: 0 !important;
        background-color: #fff !important;
        border: 1px solid rgba(0,0,0,.15) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Fix untuk dropdown select2 (Dashboard) di navbar - lebih naik */
    .navbar .nav-search .select2-container {
        z-index: 10001 !important;
    }

    .navbar .nav-search .select2-container--open .select2-dropdown {
        margin-top: -8px !important;
        z-index: 10002 !important;
    }

    .navbar .nav-search .select2-dropdown {
        z-index: 10002 !important;
        border: 1px solid rgba(0,0,0,.15) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .nav-profile .dropdown-menu.show {
        display: block !important;
    }

    .nav-profile .dropdown-toggle.show::after {
        transform: rotate(180deg);
    }
    
    .navbar-menu-wrapper {
        overflow-x: auto;
        overflow-y: visible !important;
        flex-wrap: nowrap;
        min-width: 0;
    }

    /* Pastikan dropdown tidak terpotong oleh overflow */
    .navbar-nav-right {
        overflow: visible !important;
    }

    .navbar-menu-wrapper .navbar-nav-right {
        overflow: visible !important;
    }

    /* Pastikan ul.navbar-nav tidak memotong dropdown */
    .navbar-menu-wrapper .navbar-nav {
        overflow: visible !important;
    }

    .navbar-menu-wrapper .navbar-nav .nav-item {
        overflow: visible !important;
    }
    
    /* Pastikan elemen tidak wrap yang tidak perlu */
    .navbar-nav-right {
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }
    
    /* Timer styling */
    .navbar-nav-right .nav-item #txt {
        color: white;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* Profile dropdown responsive */
    .navbar-nav-right .nav-profile .nav-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }
    
    .navbar-nav-right .nav-profile .nav-link img {
        border-radius: 50%;
        object-fit: cover;
    }

    /* Notifikasi navbar */
    .nav-notifications {
        position: relative !important;
        z-index: 1032 !important;
    }

    .nav-notifications .nav-link {
        position: relative;
        padding: 0.5rem 0.75rem !important;
        color: #fff !important;
    }

    #notificationDropdown.dropdown-toggle::after {
        display: none !important;
    }

    .notification-bell-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.35rem;
        height: 1.35rem;
        vertical-align: middle;
    }

    .nav-notifications .nav-link i {
        font-size: 1.15rem;
        line-height: 1;
    }

    .nav-notifications .notification-badge {
        position: absolute;
        top: -7px;
        right: -9px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: #ff4757;
        color: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        line-height: 16px;
        text-align: center;
        display: none;
        border: 2px solid #1a7a6d;
        box-sizing: content-box;
    }

    .nav-notifications .notification-badge.is-visible {
        display: inline-block;
    }

    /* Selector tanpa parent: menu di-append ke body saat dibuka */
    #notificationDropdownMenu,
    .notification-dropdown-menu {
        display: none !important;
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: auto !important;
        float: none !important;
        z-index: 99999 !important;
        width: 320px !important;
        min-width: 0 !important;
        max-width: calc(100vw - 24px) !important;
        max-height: none !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
        border-radius: 0 0 0.25rem 0.25rem !important;
        border-top: none !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        opacity: 1 !important;
        border: 1px solid rgba(0, 0, 0, .12) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12) !important;
        list-style: none !important;
    }

    #notificationDropdownMenu.show,
    .notification-dropdown-menu.show {
        display: block !important;
    }

    .notification-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid #eee;
        background: #fafafa !important;
        margin: 0;
    }

    .notification-panel__header h6 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
        line-height: 1.2;
    }

    .notification-panel__header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-panel__header-actions button,
    .notification-panel__header-actions a {
        border: 0;
        background: transparent;
        color: #0d8f7f;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0;
        cursor: pointer;
        text-decoration: none;
    }

    .notification-panel__body,
    #notificationList {
        margin: 0;
        padding: 0;
        max-height: 360px;
        overflow-y: auto;
        background: #ffffff !important;
        background-color: #ffffff !important;
    }

    .notification-group-label {
        padding: 0.35rem 0.85rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #8899a0;
        background: #f7f8fa;
        border-bottom: 1px solid #f0f0f0;
        margin: 0;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.55rem 0.75rem;
        border-bottom: 1px solid #f1f1f1;
        text-decoration: none !important;
        color: inherit !important;
        transition: background 0.15s ease;
        margin: 0;
        background: #ffffff !important;
        background-color: #ffffff !important;
        opacity: 1 !important;
        min-height: 0;
        line-height: 1.25;
        cursor: pointer;
    }

    .notification-item.is-unread {
        background: #f3fbf9 !important;
        background-color: #f3fbf9 !important;
    }

    .notification-item.is-read {
        opacity: 0.72 !important;
    }

    .notification-item:hover {
        background: #eef8f6 !important;
        background-color: #eef8f6 !important;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item__icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 0.82rem;
        margin-top: 1px;
    }

    .notification-item__icon.bg-warning { background: #ffb822; }
    .notification-item__icon.bg-info { background: #36a3f7; }
    .notification-item__icon.bg-primary { background: #5867dd; }
    .notification-item__icon.bg-success { background: #1dc9b7; }
    .notification-item__icon.bg-danger { background: #fd397a; }
    .notification-item__icon.bg-secondary { background: #6c757d; }

    .notification-item__content {
        flex: 1;
        min-width: 0;
    }

    .notification-item__title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.1rem;
    }

    .notification-item__message {
        font-size: 0.74rem;
        color: #666;
        margin: 0;
        line-height: 1.35;
        white-space: pre-line;
    }

    .notification-item__time {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.68rem;
        color: #99a3a8;
    }

    .notification-item__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #0d8f7f;
        flex-shrink: 0;
        margin-top: 8px;
    }

    .notification-item.is-read .notification-item__dot {
        visibility: hidden;
    }

    .notification-worklist {
        border-top: 1px solid #eee;
        background: #fafafa;
        padding: 0.45rem 0.75rem 0.55rem;
    }

    .notification-worklist__title {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8899a0;
        margin: 0 0 0.35rem;
    }

    .notification-worklist__row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .notification-worklist__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #e5e9ec;
        font-size: 0.7rem;
        color: #334;
        text-decoration: none !important;
    }

    .notification-worklist__chip strong {
        color: #0d8f7f;
    }

    .notification-panel__footer {
        border-top: 1px solid #eee;
        padding: 0.45rem 0.75rem;
        text-align: center;
        background: #fff;
    }

    .notification-panel__footer a {
        font-size: 0.78rem;
        font-weight: 600;
        color: #0d8f7f;
        text-decoration: none;
    }

    .notification-empty {
        padding: 1rem 0.85rem;
        text-align: center;
        color: #999;
        font-size: 0.82rem;
        margin: 0;
    }

    .notification-empty i {
        display: block;
        font-size: 1.25rem;
        margin-bottom: 0.35rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        #notificationDropdownMenu,
        .notification-dropdown-menu {
            width: calc(100vw - 24px) !important;
        }
    }
    
    /* Responsive untuk select2 di header */
    @media (max-width: 1200px) {
        .navbar .nav-search .nav-link {
            width: 400px !important;
        }
    }
    
    @media (max-width: 992px) {
        .navbar .nav-search .nav-link {
            width: 300px !important;
        }
    }

    /* Fix untuk select2 dropdown di navbar - lebih naik */
    .navbar .nav-search .select2-container {
        z-index: 10001 !important;
    }

    .navbar .nav-search .select2-container--open .select2-dropdown {
        margin-top: -5px !important;
        top: 100% !important;
        z-index: 10002 !important;
        position: absolute !important;
    }

    .navbar .nav-search .select2-dropdown {
        z-index: 10002 !important;
        border: 1px solid rgba(0,0,0,.15) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Pastikan navbar tidak collapse di mobile */
    @media (max-width: 768px) {
        .navbar {
            flex-wrap: nowrap;
        }
        
        .navbar-menu-wrapper {
            flex: 1 1 auto;
            min-width: 0;
        }
    }

    /* Fix untuk tombol toggle sidebar di mobile */
    @media (max-width: 991px) {
        .navbar-toggler[data-toggle="offcanvas"] {
            display: block !important;
            z-index: 1051;
            position: relative;
            cursor: pointer;
            padding: 0.5rem 0.75rem !important;
            margin-right: 0.5rem;
            border: none;
            background: transparent;
            color: white;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            min-width: 44px;
            min-height: 44px;
        }

        .navbar-toggler[data-toggle="offcanvas"]:hover {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .navbar-toggler[data-toggle="offcanvas"]:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
            background: rgba(255, 255, 255, 0.1);
        }

        .navbar-toggler[data-toggle="offcanvas"]:active {
            background: rgba(255, 255, 255, 0.2);
        }

        .navbar-toggler[data-toggle="offcanvas"] span {
            display: inline-block;
            pointer-events: none;
        }
    }
</style>
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row navbar-info default-layout-navbar">
    <div class="text-start navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{ url('') }}" target="_blank">
            <img src="{{ asset('/assets/public/images/logo_magelang_mini.png') }}" style="width:42px; height:auto; margin-right: 14px; margin-left: 14px;" alt="Logo SimaLab" />
        </a>
        <div class="brand-text d-none d-lg-block">
            <p>SimaLab</p>
            <small>Lingkungan pengujian</small>
        </div>
        <a class="navbar-brand brand-logo-mini" href="{{ url('') }}" target="_blank" style="display: none;">
            <img src="{{ asset('/assets/public/images/logo_magelang_mini.png') }}" style="width:40px; height:auto;" alt="Logo SimaLab" />
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="fas fa-bars"></span>
        </button>
        <ul class="navbar-nav">
            <li class="nav-item nav-search d-none d-lg-flex">
                <div class="nav-link" style="width: 600px !important; max-width: 100%;">
                    <select class="form-control smt-select2" id="smt_navigation" style="width: 100%">

                        @php
                            $parent = \Smt\Masterweb\Models\AdminMenu::all()->sortBy('order')->where('upmenu', '0');
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

                                $child = \Smt\Masterweb\Models\AdminMenu::all()
                                    ->sortBy('order')
                                    ->where('upmenu', $menu->id);
                            @endphp

                            @if (count($child) > 0)
                                <optgroup label="{{ $menu->name }}">
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
                                        @endphp

                                        <option value="{{ URL::to($submenu->link) }}"
                                            {{ $submenu->link == request()->segment(1) ? 'selected' : '' }}>
                                            {{ $submenu->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option value="{{ $menu->link }}"
                                    {{ $menu->link == request()->segment(1) ? 'selected' : '' }}>
                                    {{ $menu->name }}</option>
                            @endif
                        @endforeach
                    </select>

                </div>
            </li>
        </ul>

        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item d-none d-md-block">
                <div class="nav-link" style="padding: 0.5rem 0.75rem;">
                    <span id="txt"></span>
                </div>
            </li>

            <li class="nav-item nav-notifications dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown"
                    aria-haspopup="true" aria-expanded="false" title="Notifikasi">
                    <span class="notification-bell-wrap">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge">0</span>
                    </span>
                </a>
                <div class="notification-dropdown-menu" id="notificationDropdownMenu"
                    aria-labelledby="notificationDropdown">
                    <div class="notification-panel__header">
                        <h6>Notifikasi</h6>
                        <div class="notification-panel__header-actions">
                            <button type="button" id="notificationMarkAllRead">Tandai terbaca</button>
                        </div>
                    </div>
                    <div class="notification-panel__body" id="notificationList">
                        <div class="notification-empty">
                            <i class="fas fa-spinner fa-spin"></i>
                            Memuat notifikasi...
                        </div>
                    </div>
                    <div class="notification-worklist" id="notificationWorklist" style="display:none;"></div>
                    <div class="notification-panel__footer">
                        <a href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
                    </div>
                </div>
            </li>

            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                    <span class="d-none d-md-inline">{{ $user->name }}</span>
                    {{-- <img
            src="{{ $user->photo == null ? asset('assets/admin/images/logo/favicon.png') : asset('/storage/photo/' . $user->photo) }}"
            alt="profile" /> --}}

                    @if (Storage::disk('public')->exists('photo/' . $user->photo) && $user->photo != null)
                        <img src="{{ Storage::url('photo/' . $user->photo) }}" alt="profile" />
                    @else
                        <img src="{{ asset('assets/admin/images/logo/favicon.png') }}" alt="profile" />
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                    aria-labelledby="profileDropdown">

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item" href="/biodata">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-warning">
                                <i class="fas fa-wrench mx-0"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-medium">Pengaturan</h6>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-info">
                                <i class="fas fa-power-off mx-0"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-medium">Logout</h6>
                        </div>
                    </a>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>

        {{-- <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <span>{{$user->name}}</span>
              <img src="{{ ($user->photo == NULL) ? asset('assets/admin/images/logo/favicon.png') : asset('/storage/photo/'.$user->photo)}}" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="/biodata">
                <i class="fas fa-cog text-primary"></i>
                Pengaturan
              </a>
              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-power-off text-primary"></i> Logout
              </a>


            </div>
          </li>
          {{-- <li class="nav-item nav-settings d-none d-lg-block">
            <a class="nav-link" href="#">
              <i class="fas fa-ellipsis-h"></i>
            </a>
          </li> --}}
        {{-- </ul> --}}
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="fas fa-bars"></span>
        </button>
    </div>
</nav>
<script>
(function ($) {
    var notificationUrl = @json(route('notifications.feed'));
    var markAllUrl = @json(route('notifications.read-all'));
    var markReadUrlTemplate = @json(url('notifications/__ID__/read'));
    var csrfToken = @json(csrf_token());
    var pollIntervalMs = 60000;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderItem(item) {
        var color = item.color || 'secondary';
        var unreadClass = item.unread ? 'is-unread' : 'is-read';
        return '<a class="notification-item ' + unreadClass + '" href="' + escapeHtml(item.url || '#') + '" data-id="' + escapeHtml(item.id || '') + '">' +
            '<div class="notification-item__icon bg-' + color + '">' +
            '<i class="fas ' + escapeHtml(item.icon || 'fa-bell') + '"></i>' +
            '</div>' +
            '<div class="notification-item__content">' +
            '<div class="notification-item__title">' + escapeHtml(item.title || 'Notifikasi') + '</div>' +
            '<p class="notification-item__message">' + escapeHtml(item.message || '') + '</p>' +
            '<span class="notification-item__time">' + escapeHtml(item.created_human || '') + '</span>' +
            '</div>' +
            '<span class="notification-item__dot"></span>' +
            '</a>';
    }

    function renderNotifications(data) {
        var $badge = $('#notificationBadge');
        var $list = $('#notificationList');
        var $worklist = $('#notificationWorklist');
        var unread = parseInt(data && (data.unread != null ? data.unread : data.total), 10) || 0;
        var groups = (data && data.groups) ? data.groups : {};
        var items = (data && data.items) ? data.items : [];
        var worklist = (data && data.worklist) ? data.worklist : [];

        if (unread > 0) {
            $badge.text(unread > 99 ? '99+' : unread).addClass('is-visible');
        } else {
            $badge.removeClass('is-visible');
        }

        var html = '';
        var groupKeys = Object.keys(groups);
        if (groupKeys.length) {
            groupKeys.forEach(function (label) {
                html += '<div class="notification-group-label">' + escapeHtml(label) + '</div>';
                (groups[label] || []).forEach(function (item) {
                    html += renderItem(item);
                });
            });
        } else if (items.length) {
            items.forEach(function (item) {
                html += renderItem(item);
            });
        } else {
            html = '<div class="notification-empty"><i class="far fa-bell-slash"></i>Tidak ada notifikasi belum dibaca</div>';
        }
        $list.html(html);

        if (worklist.length) {
            var chips = worklist.map(function (w) {
                return '<a class="notification-worklist__chip" href="' + escapeHtml(w.url || '#') + '">' +
                    escapeHtml(w.label || '') + ' <strong>' + (w.count || 0) + '</strong></a>';
            }).join('');
            $worklist.html('<div class="notification-worklist__title">Worklist</div><div class="notification-worklist__row">' + chips + '</div>').show();
        } else {
            $worklist.hide().empty();
        }
    }

    function loadNotifications() {
        $.ajax({
            url: notificationUrl,
            type: 'GET',
            cache: false,
            success: function (response) {
                renderNotifications(response);
            },
            error: function () {
                $('#notificationList').html(
                    '<div class="notification-empty">' +
                    '<i class="fas fa-exclamation-circle"></i>' +
                    'Gagal memuat notifikasi' +
                    '</div>'
                );
            }
        });
    }

    function markRead(id, done) {
        if (!id) {
            if (done) done();
            return;
        }
        $.ajax({
            url: markReadUrlTemplate.replace('__ID__', id),
            type: 'POST',
            data: { _token: csrfToken },
            complete: function () {
                if (done) done();
            }
        });
    }

    function positionNotificationDropdown() {
        var $toggle = $('#notificationDropdown');
        var $menu = $('#notificationDropdownMenu');
        var $navbar = $('.navbar.fixed-top, .default-layout-navbar');
        if (!$toggle.length || !$menu.length) {
            return;
        }

        if (!$menu.parent().is('body')) {
            $menu.appendTo('body');
        }

        var toggleRect = $toggle[0].getBoundingClientRect();
        var navbarRect = $navbar.length
            ? $navbar[0].getBoundingClientRect()
            : toggleRect;
        var menuWidth = $menu.outerWidth() || 320;
        var rightPos = window.innerWidth - toggleRect.right;

        if (rightPos + menuWidth > window.innerWidth - 12) {
            rightPos = Math.max(12, window.innerWidth - menuWidth - 12);
        }

        $menu.css({
            position: 'fixed',
            top: Math.round(navbarRect.bottom) + 'px',
            right: Math.round(rightPos) + 'px',
            left: 'auto',
            margin: 0,
            padding: 0,
            transform: 'none'
        });
    }

    function toggleNotificationDropdown(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        var $toggle = $('#notificationDropdown');
        var $menu = $('#notificationDropdownMenu');
        var isVisible = $menu.hasClass('show');

        $('#notificationDropdownMenu').removeClass('show').css('display', 'none');
        $('#notificationDropdown').removeClass('show').attr('aria-expanded', 'false');

        if (!isVisible) {
            loadNotifications();
            positionNotificationDropdown();
            $menu.addClass('show').css('display', 'block');
            $toggle.addClass('show').attr('aria-expanded', 'true');
        }
    }

    $(function () {
        loadNotifications();
        setInterval(loadNotifications, pollIntervalMs);

        $(document).on('click', '#notificationDropdown', toggleNotificationDropdown);

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#notificationDropdown, #notificationDropdownMenu').length) {
                $('#notificationDropdownMenu').removeClass('show').css('display', 'none');
                $('#notificationDropdown').removeClass('show').attr('aria-expanded', 'false');
            }
        });

        $(document).on('click', '#notificationDropdownMenu', function (e) {
            e.stopPropagation();
        });

        $(document).on('click', '#notificationMarkAllRead', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $.ajax({
                url: markAllUrl,
                type: 'POST',
                data: { _token: csrfToken },
                success: function () {
                    loadNotifications();
                }
            });
        });

        $(document).on('click', '#notificationList .notification-item', function (e) {
            var id = $(this).data('id');
            var href = $(this).attr('href');
            e.preventDefault();
            markRead(id, function () {
                if (href && href !== '#') {
                    window.location.href = href;
                } else {
                    loadNotifications();
                }
            });
        });

        var notificationTicking = false;
        $(window).on('resize scroll', function () {
            if (!$('#notificationDropdownMenu').hasClass('show')) {
                return;
            }
            if (notificationTicking) {
                return;
            }
            notificationTicking = true;
            requestAnimationFrame(function () {
                positionNotificationDropdown();
                notificationTicking = false;
            });
        });
    });
})(jQuery);
</script>
