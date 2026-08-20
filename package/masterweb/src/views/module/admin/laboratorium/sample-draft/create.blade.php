@extends('masterweb::template.admin.layout')
@section('title')
    Input Draft Sample (Sementara)
@endsection

@section('css')
    <style>
        * {
            margin: 0;
            padding: 0
        }

        html {
            height: 100%
        }

        /* Modern Page Styling */
        .page-header-card-sample {
            /* Warna lebih lembut agar tidak terlalu terang */
            background: linear-gradient(135deg, #ffe9a3 0%, #ffd27f 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 6px 18px rgba(206, 173, 80, 0.35);
            color: #4a4a4a;
        }

        .page-header-card-sample h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .page-header-card-sample h2 i {
            margin-right: 15px;
            font-size: 32px;
            background: rgba(255, 255, 255, 0.65);
            padding: 12px;
            border-radius: 12px;
        }

        .page-header-card-sample .subtitle {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-section-card-sample {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
            contain: layout style;
        }

        .section-title-sample {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #ffc107;
            display: flex;
            align-items: center;
        }

        .section-title-sample i {
            margin-right: 12px;
            color: #d89c18;
            font-size: 24px;
        }

        .draft-alert {
            background: linear-gradient(135deg, #fffaf0 0%, #ffeccd 100%);
            border-left: 5px solid #e0b64b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .draft-alert strong {
            color: #7a5c16;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.1);
        }

        /* Collapse & Auto-Sort Styles */
        .parameter-group-header {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f9fafb 0%, #eceff3 100%) !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 15px !important;
            margin-bottom: 15px !important;
            font-weight: 600;
            color: #2d3748;
        }

        .parameter-group-header:hover {
            background: linear-gradient(135deg, #ffe8b3 0%, #ffd499 100%) !important;
            color: white !important;
            border-color: #ffc107 !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        .parameter-group-header:hover .collapse-icon {
            color: white !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
            margin-right: 8px;
            color: #d89c18;
        }

        .param-count {
            font-size: 13px;
            transition: all 0.2s ease;
            background: linear-gradient(135deg, #b1c4ff 0%, #a9a2d6 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            float: right;
        }

        /* Checkbox Styling for Parameters */
        .method-row {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .method-row:hover {
            background: #f8f9fa;
        }

        .method-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ffc107;
        }

        .method-row label {
            cursor: pointer;
            margin-bottom: 0;
            font-size: 14px;
            color: #4a5568;
        }

        /* Tab parameter (selaras sample/create): edit harga & pensil */
        .method-row-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .method-row-tab>label {
            flex: 1;
            margin-bottom: 0;
            min-width: 0;
        }

        .btn-pencil-edit-method {
            display: none !important;
            flex-shrink: 0;
            padding: 2px 8px;
            line-height: 1.2;
        }

        .parameter-group-item.parameter-edit-mode .btn-pencil-edit-method {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .btn-toggle-edit-parameter.active {
            background-color: #495057;
            color: #fff;
            border-color: #495057;
        }

        #mepm-stp-table .mepm-current-st-row {
            background-color: #e8f5e9 !important;
        }

        #mepm-stp-table .mepm-current-st-row td:first-child {
            border-left: 3px solid #11998e;
        }

        #mepm-stp-filter-bar {
            display: flex;
        }

        /* Cart Widget Styles */
        #parameter-cart {
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #parameter-cart .card-header {
            background: linear-gradient(135deg, #ffe4a8 0%, #ffcf80 100%);
            border-bottom: 3px solid #f0b54a;
        }

        #parameter-cart .card-body {
            background: #fafafa;
        }

        #parameter-cart .card-footer {
            background: white;
            border-top: 2px solid #dee2e6;
        }

        .cart-item {
            background: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            position: relative;
        }

        .cart-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .cart-item-name {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 4px;
            padding-right: 25px;
        }

        .cart-item-category {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .cart-item-price {
            font-weight: 600;
            color: #28a745;
            font-size: 0.95rem;
        }

        .cart-item-remove {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            padding: 0;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-remove:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        /* Disabled Parameter */
        .disabled-param {
            opacity: 0.5;
            background-color: #f5f5f5 !important;
            cursor: not-allowed !important;
        }

        .disabled-param input[type="checkbox"] {
            cursor: not-allowed !important;
        }

        .disabled-param label {
            cursor: not-allowed !important;
            color: #999 !important;
        }

        .disabled-param .badge {
            opacity: 0.6;
        }

        /* Search and Pagination */
        #search-parameter {
            border-radius: 0 8px 8px 0 !important;
            font-size: 14px;
        }

        #search-parameter:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }

        .pagination-controls {
            margin-top: 15px;
        }

        .pagination-controls .btn {
            min-width: 80px;
            font-size: 13px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .pagination-controls .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-controls .page-info {
            font-size: 13px;
            font-weight: 500;
        }

        .method-row {
            transition: all 0.2s ease;
        }

        .method-list-container {
            min-height: 50px;
        }

        /* Sample Type Tabs */
        #sampleTypeTabs .nav-link {
            border: 2px solid transparent;
            border-radius: 8px 8px 0 0;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 5px;
            background: #f8f9fa;
        }

        #sampleTypeTabs .nav-link:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        #sampleTypeTabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        #sampleTypeTabs .nav-link.active .badge {
            background: white !important;
            color: #667eea !important;
        }

        .tab-content {
            border: 2px solid #e2e8f0;
            border-radius: 0 8px 8px 8px;
            padding: 25px;
            background: white;
        }

        .btn-pick-paket-tab {
            border: 2px solid #28a745;
            background: white;
            color: #28a745;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-pick-paket-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-pick-paket-tab:hover {
            background: #e8f5e9;
            border-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }

        .btn-pick-paket-tab:hover::before {
            opacity: 1;
        }

        .btn-pick-paket-tab.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            border-color: #28a745 !important;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
            font-weight: 600;
        }

        .btn-pick-paket-tab.active .packet-name-text strong,
        .btn-pick-paket-tab.active .packet-name-text,
        .btn-pick-paket-tab.active .packet-price-text {
            color: white !important;
        }

        .btn-pick-paket-tab.active::after {
            content: '✓ DIPILIH';
            position: absolute;
            top: 8px;
            right: 8px;
            background: white;
            color: #28a745;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        /* Tab Search Box */
        .tab-search-box {
            margin-bottom: 20px;
        }

        .tab-search-box input {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            font-size: 14px;
        }

        .tab-search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        /* Tab Pagination */
        .tab-pagination {
            margin-top: 15px;
        }

        .tab-pagination .btn {
            min-width: 70px;
            border-radius: 6px;
        }

        /* Disabled Parameter (from packet) */
        .disabled-param {
            background-color: #e8f5e9 !important;
            opacity: 0.8;
        }

        .disabled-param label {
            color: #666;
        }

        .packet-badge {
            font-size: 10px;
            padding: 2px 6px;
            vertical-align: middle;
        }

        /* Cart Panel - Kasir Style */
        .cart-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .cart-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 15px;
        }

        .cart-panel-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .cart-empty {
            text-align: center;
            padding: 30px;
            color: #a0aec0;
        }

        .cart-empty i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .cart-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }

        .cart-item-packet {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #28a745;
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .cart-item-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }

        .cart-item-price {
            font-weight: 700;
            color: #667eea;
            font-size: 14px;
        }

        .cart-item-packet .cart-item-name {
            color: #28a745;
        }

        .cart-item-packet .cart-item-price {
            color: #28a745;
        }

        .cart-item-lab {
            color: #718096;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cart-divider {
            border: none;
            border-top: 2px dashed #cbd5e0;
            margin: 15px 0;
        }

        .cart-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-total-label {
            font-size: 16px;
            font-weight: 600;
        }

        .cart-total-price {
            font-size: 22px;
            font-weight: 700;
        }

        .cart-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Multi-Step Form Wizard */
        .form-wizard-container {
            position: relative;
            overflow: hidden;
        }

        .form-step {
            display: none;
            content-visibility: hidden;
            contain-intrinsic-size: 1px 600px;
        }

        .form-step.active {
            display: block;
            content-visibility: visible;
            contain-intrinsic-size: auto;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -50%;
            width: 100%;
            height: 3px;
            background: #e2e8f0;
            z-index: -1;
        }

        .step-item.completed:not(:last-child)::after {
            background: #e0b64b;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #4a5568;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .step-item.active .step-circle {
            background: linear-gradient(135deg, #ffe2a0 0%, #ffcc80 100%);
            color: #4a4a4a;
            box-shadow: 0 4px 12px rgba(214, 176, 72, 0.45);
        }

        .step-item.completed .step-circle {
            background: #5cb85c;
            color: #ffffff;
        }

        .step-label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
        }

        .step-item.active .step-label {
            color: #d89c18;
        }

        /* Step Navigation */
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 15px;
        }

        .btn-step {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-prev {
            background: #e2e8f0;
            color: #2d3748;
        }

        .btn-prev:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        .btn-next {
            background: linear-gradient(135deg, #ffe0a3 0%, #ffca7a 100%);
            color: #3a3a3a;
            box-shadow: 0 3px 10px rgba(214, 176, 72, 0.35);
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(214, 176, 72, 0.45);
        }

        .btn-submit {
            background: linear-gradient(135deg, #34a853 0%, #20c997 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(40, 167, 69, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        /* Jenis Sampel Button Styling */
        .btn-pick-jenis {
            border: 2px solid #e0b64b !important;
            color: #b88714 !important;
            background: #fffaf0 !important;
            padding: 15px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            transition: all 0.3s !important;
            font-size: 14px !important;
        }

        .btn-pick-jenis:hover {
            background: #ffe5ac !important;
            color: #4a4a4a !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(214, 176, 72, 0.35) !important;
        }

        .btn-pick-jenis.active {
            background: linear-gradient(135deg, #ffe0a3 0%, #ffca7a 100%) !important;
            color: #3a3a3a !important;
            border-color: #e0b64b !important;
            box-shadow: 0 4px 15px rgba(214, 176, 72, 0.45) !important;
        }

        /* Paket Button Styles - Enhanced Selection Visual */
        .btn-pick-paket {
            position: relative;
            padding: 15px !important;
            font-weight: 500 !important;
            border: 2px solid #28a745 !important;
            background-color: white !important;
            color: #28a745 !important;
            transition: all 0.3s ease !important;
            cursor: pointer !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-pick-paket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
            background-color: #f0fff4 !important;
        }

        .btn-pick-paket.active {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4) !important;
        }

        .btn-pick-paket.active .text-success {
            color: white !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-item:not(:last-child)::after {
                display: none;
            }

            .step-indicator {
                flex-direction: column;
            }

            #parameter-cart {
                position: static !important;
                margin-top: 20px;
            }
        }

        /* ---- Custom Searchable Dropdown (sdd) ---- */
        .sdd-wrap { position: relative; user-select: none; }
        .sdd-display {
            display: block; width: 100%; padding: .375rem .75rem;
            font-size: 1rem; line-height: 1.5; color: #495057;
            background: #fff; border: 1px solid #ced4da; border-radius: .25rem;
            cursor: pointer; outline: none;
        }
        .sdd-display::after { content: '▾'; float: right; color: #888; }
        .sdd-placeholder { color: #aaa; }
        .sdd-wrap.sdd-open .sdd-display { border-color: #80bdff; box-shadow: 0 0 0 .2rem rgba(0,123,255,.25); }
        .sdd-panel {
            display: none; position: absolute; z-index: 9999;
            width: 100%; background: #fff;
            border: 1px solid #ced4da; border-top: none;
            border-radius: 0 0 .25rem .25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
        .sdd-wrap.sdd-open .sdd-panel { display: block; }
        .sdd-search {
            display: block; width: 100%; padding: 6px 10px;
            border: none; border-bottom: 1px solid #e2e8f0;
            outline: none; font-size: 13px;
        }
        .sdd-list { list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; }
        .sdd-list li { padding: 7px 12px; cursor: pointer; font-size: 13px; }
        .sdd-list li:hover { background: #f0f4ff; }
        .sdd-list li[data-value=""] { color: #aaa; font-style: italic; }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card" style="border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                <div class="card-body" style="padding: 15px 20px;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/home') }}">
                                    <i class="fa fa-home menu-icon mr-1"></i> Beranda
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('elits-permohonan-uji.index') }}">Permohonan Uji</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('elits-sample-draft.index', $permohonan_uji->id_permohonan_uji) }}">Draft
                                    Sample</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Draft Sample</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Draft Alert -->
    <div class="draft-alert">
        <strong><i class="fa fa-info-circle"></i> Mode Draft (Penyimpanan Sementara):</strong>
        <p class="mb-0 mt-2">Data yang Anda input akan disimpan sebagai draft dan dapat dikonfirmasi atau dihapus nanti
            sebelum diproses ke sistem utama.</p>
    </div>

    <!-- Page Header -->
    <div class="page-header-card-sample">
        <h2>
            <i class="fa fa-file-alt"></i>
            Input Draft Sample
        </h2>
        <div class="subtitle">
            Permohonan Uji
            @if (optional($permohonan_uji->customer)->name_customer)
                — <strong>{{ $permohonan_uji->customer->name_customer }}</strong>
            @elseif (!empty($permohonan_uji->name_customer))
                — <strong>{{ $permohonan_uji->name_customer }}</strong>
            @endif
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step-item active" data-step="1">
            <div class="step-circle">1</div>
            <div class="step-label">Input Sample & Parameter</div>
        </div>
        <div class="step-item" data-step="2">
            <div class="step-circle">2</div>
            <div class="step-label">Review & Simpan</div>
        </div>
    </div>

    <!-- Form Container -->
    <form id="draftForm" method="POST"
        action="{{ route('elits-sample-draft.store', $permohonan_uji->id_permohonan_uji) }}">
        @csrf

        <div class="form-wizard-container">
            <!-- STEP 1: Input Sample & Parameter -->
            <div class="form-step active" data-step="1">
                <!-- Informasi Sample -->
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-flask"></i>
                        Informasi Sample
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fa fa-vial"></i> Pilih Jenis Sampel <span class="text-danger">*</span>
                        </label>
                        <small class="form-text text-muted mb-3">
                            <i class="fa fa-info-circle"></i> Anda dapat memilih <strong>lebih dari 1 jenis sampel</strong>.
                            Setiap jenis sampel akan memiliki paket & parameter tersendiri.
                        </small>
                        <div class="row" style="margin-top: 15px;">
                            @foreach ($sampletypes as $type)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <button type="button" class="btn btn-pick-jenis w-100"
                                        data-id="{{ $type->id_sample_type }}" data-code="{{ $type->code_sample_type }}"
                                        data-name="{{ $type->name_sample_type }}"
                                        style="text-align: left; padding: 15px; height: auto; min-height: 60px; position: relative;">
                                        <span class="jenis-check-icon"
                                            style="position: absolute; top: 8px; right: 8px; display: none;">
                                            <i class="fa fa-check-circle" style="font-size: 20px; color: #4caf50;"></i>
                                        </span>
                                        <strong>{{ $type->code_sample_type }}</strong><br>
                                        <small>{{ $type->name_sample_type }}</small>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Display Selected Sample Types with Badge -->
                    <div id="selected-sampletypes-container" style="display: none; margin-top: 20px;">
                        <div class="alert"
                            style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50; border-radius: 10px;">
                            <strong><i class="fa fa-check-circle text-success"></i> Jenis Sampel Terpilih:</strong>
                            <div id="selected-sampletypes-badges"
                                style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="note_samples">
                            <i class="fa fa-sticky-note"></i> Catatan (Opsional)
                        </label>
                        <textarea class="form-control" name="note_samples" id="note_samples" rows="2"
                            placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>

                    <div class="row">
                        <!-- Biaya Sampling -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cost_sampling">
                                    <i class="fa fa-money-bill-wave"></i> Biaya Sampling
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control" name="cost_sampling" id="cost_sampling"
                                        value="20000" min="0" step="1000" placeholder="Masukkan biaya sampling">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Default: Rp 20.000 <strong>per jenis sampel</strong>.
                                    Biaya akan dikalikan dengan jumlah jenis sampel yang dipilih.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Paket & Parameter Selection - Dynamic Tabs per Sample Type -->
                <div class="form-section-card-sample" id="paket-parameter-section" style="display: none;">
                    <div class="section-title-sample">
                        <i class="fa fa-list-check"></i>
                        Paket & Parameter Pengujian
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Informasi:</strong> Setiap jenis sampel dapat memilih paket atau parameter yang berbeda.
                    </div>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="sampleTypeTabs" role="tablist" style="margin-bottom: 20px;">
                        <!-- Tabs will be generated dynamically -->
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="sampleTypeTabsContent">
                        <!-- Tab panels will be generated dynamically -->
                    </div>
                </div>

                <!-- OLD SINGLE PANEL - HIDDEN FOR REFERENCE -->
                <div style="display: none;">
                    <div class="form-group mb-4" id="packet-selection-container-old">
                        <label>
                            <i class="fa fa-cube"></i> Pilih Paket (Opsional)
                        </label>
                        <small class="form-text text-muted mb-2">
                            <i class="fa fa-info-circle"></i> Pilih paket untuk otomatis memilih parameter yang termasuk
                            dalam paket
                        </small>
                        <div class="row packet-buttons-container" style="margin-top: 15px;">
                            @php $displayedPackets = []; @endphp
                            @foreach ($packets as $packet)
                                @if (!in_array($packet->id_packet, $displayedPackets))
                                    @php $displayedPackets[] = $packet->id_packet; @endphp
                                    <div class="col-md-6 col-lg-4 mb-3 packet-button-item"
                                        data-sampletypes="{{ json_encode($packet->sample_type_ids ?? []) }}"
                                        data-packet-id="{{ $packet->id_packet }}" style="display: none;">
                                        <button type="button" class="btn btn-pick-paket w-100"
                                            data-id="{{ $packet->id_packet }}"
                                            data-price="{{ $packet->price_total_packet }}"
                                            data-name="{{ $packet->name_packet }}"
                                            style="text-align: left; padding: 15px; height: auto; min-height: 80px;">
                                            <strong>{{ $packet->name_packet }}</strong><br>
                                            <small class="text-success">
                                                <i class="fa fa-tag"></i> Rp
                                                {{ number_format($packet->price_total_packet, 0, ',', '.') }}
                                            </small>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <input type="hidden" name="packet_id" id="packet_id">
                        <div class="text-center mt-3" id="no-packet-message" style="display: none;">
                            <p class="text-muted"><i class="fa fa-box-open"></i> Tidak ada paket tersedia untuk jenis
                                sampel
                                ini</p>
                        </div>
                    </div>

                    <hr style="margin: 30px 0; border-top: 2px dashed #e2e8f0;">

                    <!-- Parameter Selection -->
                    <div class="row">
                        <!-- Left Column: Parameter List -->
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 style="margin: 0; color: #2d3748; font-weight: 600;">
                                    <i class="fas fa-flask"></i> Pilih Parameter Pengujian
                                </h5>
                            </div>

                            <!-- Search Box -->
                            <div class="form-group mb-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="search-parameter" class="form-control"
                                        placeholder="Cari parameter (misal: BOD, pH, Kadmium...)"
                                        style="border-left: none; padding: 12px; font-size: 14px;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="clear-search"
                                            style="display: none;">
                                            <i class="fa fa-times"></i> Clear
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Ketik untuk mencari parameter berdasarkan nama
                                </small>
                            </div>

                            @foreach ($laboratoriums as $lab)
                                <div class="parameter-group mb-4" data-lab-group="{{ $lab->id_laboratorium }}">
                                    <div class="parameter-group-header" data-toggle="collapse"
                                        data-target="#lab-{{ $lab->id_laboratorium }}">
                                        <i class="fa fa-chevron-down collapse-icon"></i>
                                        <strong>{{ $lab->nama_laboratorium }}</strong>
                                        <span class="param-count" id="count-{{ $lab->id_laboratorium }}">0 dipilih</span>
                                    </div>
                                    <div id="lab-{{ $lab->id_laboratorium }}" class="collapse">
                                        <div class="card-body" style="background: #f8f9fa; border-radius: 0 0 10px 10px;">
                                            @if (isset($methods_by_lab[$lab->id_laboratorium]) && $methods_by_lab[$lab->id_laboratorium]->count() > 0)
                                                <div class="method-list-container"
                                                    id="method-container-{{ $lab->id_laboratorium }}">
                                                    @foreach ($methods_by_lab[$lab->id_laboratorium] as $method)
                                                        <div class="method-row"
                                                            data-method-name="{{ strtolower($method->params_method) }}">
                                                            <label>
                                                                <input type="checkbox" name="method[]"
                                                                    value="{{ $method->id_method }}_{{ $lab->id_laboratorium }}_{{ $method->price_total_method }}"
                                                                    class="method-checkbox"
                                                                    data-lab="{{ $lab->id_laboratorium }}"
                                                                    data-name="{{ $method->params_method }}"
                                                                    data-labname="{{ $lab->nama_laboratorium }}"
                                                                    data-price="{{ $method->price_total_method }}">
                                                                <strong>{{ $method->params_method }}</strong>
                                                                <span class="text-muted">(Rp
                                                                    {{ number_format($method->price_total_method, 0, ',', '.') }})</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Pagination Controls -->
                                                <div class="pagination-controls mt-3"
                                                    id="pagination-{{ $lab->id_laboratorium }}" style="display: none;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        style="padding: 10px; background: white; border-radius: 8px;">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-page-prev"
                                                            data-lab="{{ $lab->id_laboratorium }}">
                                                            <i class="fa fa-chevron-left"></i> Prev
                                                        </button>
                                                        <div class="page-info text-muted">
                                                            <small>
                                                                <span class="current-page">1</span> / <span
                                                                    class="total-pages">1</span>
                                                                (<span class="showing-count">0</span> dari <span
                                                                    class="total-count">0</span>)
                                                            </small>
                                                        </div>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-page-next"
                                                            data-lab="{{ $lab->id_laboratorium }}">
                                                            Next <i class="fa fa-chevron-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-muted">Tidak ada parameter tersedia untuk laboratorium ini.
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Right Column: Cart Widget -->
                        <div class="col-lg-4">
                            <div class="form-section-card-sample" id="parameter-cart"
                                style="position: sticky; top: 20px; padding: 0; overflow: hidden;">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 20px; margin: 0; border: none;">
                                    <h5 class="mb-0" style="color: white; font-weight: 600;">
                                        <i class="fas fa-shopping-cart"></i> Parameter Terpilih
                                    </h5>
                                </div>
                                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                    <!-- Cart Items List -->
                                    <div id="cart-items-list">
                                        <div class="text-center text-muted py-5" id="cart-empty-state">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>Belum ada parameter dipilih</p>
                                            <small>Centang parameter untuk menambahkan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer"
                                    style="background: #f8f9fa; padding: 20px; border-top: 2px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between align-items-center mb-3"
                                        style="background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                        <strong style="color: #4a5568;" id="cart-total-label">Total Parameter:</strong>
                                        <span class="badge badge-lg"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 15px; font-size: 14px; border-radius: 8px;"
                                            id="cart-total-items">0</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3"
                                        style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);">
                                        <strong style="font-size: 1.1rem; color: white;">Total Harga:</strong>
                                        <span style="font-size: 1.4rem; font-weight: bold; color: white;"
                                            id="cart-total-price">Rp 0</span>
                                    </div>
                                    <button type="button" class="btn btn-block"
                                        style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 12px; font-weight: 600; border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3); transition: all 0.3s;"
                                        id="cart-clear-all"
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(229, 62, 62, 0.4)'"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(229, 62, 62, 0.3)'">
                                        <i class="fas fa-trash"></i> Hapus Semua Parameter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-navigation">
                    <a href="{{ route('elits-sample-draft.index', $permohonan_uji->id_permohonan_uji) }}"
                        class="btn-step btn-prev">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn-step btn-next" onclick="nextStep(1)">
                        Selanjutnya <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Review & Simpan -->
            <div class="form-step" data-step="2">
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-check-circle"></i>
                        Review Data Sample Draft
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Periksa kembali data yang telah diisi sebelum menyimpan sebagai draft.</strong>
                    </div>

                    @php
                        $ksDraft = $kesmasSampleSettings ?? \Smt\Masterweb\Models\KesmasSampleNumberSettings::getSettings();
                        $ksDraftTableOk = \Smt\Masterweb\Models\KesmasSampleNumberSettings::tableExists();
                    @endphp
                    @if (!$ksDraftTableOk)
                        <div class="alert alert-warning mb-3">
                            <strong><i class="fa fa-database"></i> Migrasi diperlukan</strong>
                            <p class="mb-0 small mt-2">Jalankan <kbd>php artisan migrate</kbd> agar pengaturan nomor manual Kesmas
                                (<code>ms_kesmas_sample_number_settings</code>) dan kolom draft tersedia.</p>
                        </div>
                    @elseif ($ksDraft->is_nomor_sampel_manual)
                        <div class="alert alert-light border mb-3 small">
                            <i class="fa fa-barcode text-info mr-2"></i>
                            <strong>Nomor sampel manual per jenis sampel:</strong> isi angka urut di setiap kartu jenis sampel pada
                            ringkasan di bawah (urutan bisa berbeda tiap jenis). Format penuh dibentuk otomatis
                            (<code>03/…/tahun</code>).
                        </div>
                    @else
                        <div class="alert alert-light border mb-4">
                            <i class="fa fa-cog text-secondary mr-2"></i>
                            <span class="text-muted small">Nomor sampel <strong>otomatis</strong>. Untuk input manual
                                aktifkan di
                                <a href="{{ route('kesmas-sample-number-settings.index') }}" class="font-weight-bold"
                                    target="_blank">Pengaturan nomor Kesmas</a>.</span>
                        </div>
                    @endif
                    <div class="alert alert-info border mb-3 small">
                        <i class="fa fa-hashtag mr-2"></i>
                        <strong>Nomor laboratorium</strong> ditetapkan otomatis saat semua sampel dengan jenis dan lab yang sama selesai pengesahan hasil.
                    </div>

                    <div id="review-content" style="padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <!-- Review content will be populated by JavaScript -->
                    </div>

                    <!-- Hidden input for cost_samples -->
                    <input type="hidden" name="cost_samples" id="cost_samples" value="0">
                </div>

                <div class="step-navigation">
                    <button type="button" class="btn-step btn-prev" onclick="prevStep(2)">
                        <i class="fa fa-arrow-left"></i> Sebelumnya
                    </button>
                    <button type="submit" class="btn-step btn-submit">
                        <i class="fa fa-save"></i> Simpan sebagai Draft
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('masterweb::module.admin.laboratorium.sample-draft._create_tabs_modals')
@endsection

@section('scripts')
    <script>
    /* TinyMCE sudah dimuat di scripts.blade.php */
    (function () {
        if (typeof tinymce === 'undefined') return;

        /* Agar toolbar / dialog TinyMCE tidak diblokir focus-trap Bootstrap Modal */
        $(document).on('focusin', function (e) {
            if ($(e.target).closest('.tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root').length) {
                e.stopImmediatePropagation();
            }
        });

        var bmTinyBase = {
            height: 170,
            menubar: false,
            statusbar: false,
            plugins: 'advlist autolink lists link charmap searchreplace paste help wordcount',
            toolbar: 'undo redo | bold italic | superscript subscript | charmap | bullist numlist | removeformat | help',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; margin: 8px; }',
            branding: false,
            promotion: false
        };

        window.removeBmTinyInlineEditors = function () {
            ['modal-bm-equal', 'modal-bm-nilai'].forEach(function (id) {
                var ed = tinymce.get(id);
                if (ed) ed.remove();
            });
        };

        window.initBmTinyInlineEditors = function () {
            window.removeBmTinyInlineEditors();
            if (!$('#modal-tambah-param').hasClass('show')) return;
            if (!$('#modal-param-step2').is(':visible')) return;
            if (!$('#modal-bm-no-sub').is(':visible')) return;

            tinymce.init($.extend({}, bmTinyBase, {
                selector: '#modal-bm-equal,#modal-bm-nilai'
            }));
        };

        window.scheduleInitBmTinyInlineEditors = function () {
            setTimeout(function () {
                window.initBmTinyInlineEditors();
            }, 200);
        };
    }());
    </script>

    <script>
        function deferCreatePageInit(callback) {
            if (window.requestIdleCallback) {
                requestIdleCallback(callback, { timeout: 2500 });
            } else {
                setTimeout(callback, 400);
            }
        }

        let currentStep = 1;
        const totalSteps = 2;

        // Pagination settings
        const ITEMS_PER_PAGE = 15;
        const labPagination = {};

        // Multiple sample types selection with configurations
        var selectedSampleTypes = [];
        var sampleTypeConfigs = {};
        window.selectedSampleTypes = selectedSampleTypes;
        window.sampleTypeConfigs = sampleTypeConfigs;

        window.draftKimiaLabId = @json($lab_kimia ? $lab_kimia->id_laboratorium : null);
        window.draftMikroLabId = @json($lab_mikro ? $lab_mikro->id_laboratorium : null);
        window.kesmasDraftYear = @json((string) \Carbon\Carbon::now()->format('Y'));
        @php
            $__ksDraftJs = $kesmasSampleSettings ?? \Smt\Masterweb\Models\KesmasSampleNumberSettings::getSettings();
        @endphp
        window.kesmasDraftNomorSampelManual = @json((bool) $__ksDraftJs->is_nomor_sampel_manual);
        window.kesmasDraftNomorLabManual = false; // Nomer lab ditetapkan otomatis saat pengesahan hasil
        window.kesmasDraftTableOk = @json(\Smt\Masterweb\Models\KesmasSampleNumberSettings::tableExists());

        function collectDraftLabsUsedForType(stId) {
            var kid = window.draftKimiaLabId != null && window.draftKimiaLabId !== '' ?
                String(window.draftKimiaLabId) : '';
            var mid = window.draftMikroLabId != null && window.draftMikroLabId !== '' ?
                String(window.draftMikroLabId) : '';
            var useK = false,
                useM = false;
            if (!kid && !mid) {
                return { useKimia: false, useMikro: false };
            }
            var cfg = sampleTypeConfigs[stId];
            if (!cfg) return { useKimia: false, useMikro: false };
            var list = [];
            (cfg.packets || []).forEach(function(p) {
                (p.methods || []).forEach(function(m) { list.push(String(m)); });
            });
            (cfg.additional_methods || []).forEach(function(m) {
                list.push(String(m.method_string || m.method || ''));
            });
            list.forEach(function(ms) {
                var parts = ms.split('_');
                if (parts.length >= 2) {
                    var labIdStr = String(parts[1]);
                    if (kid && labIdStr === kid) useK = true;
                    if (mid && labIdStr === mid) useM = true;
                }
            });
            return { useKimia: useK, useMikro: useM };
        }

        function syncDraftKesmasDisplaysForType(stId) {
            var clean = String(stId).replace(/-/g, '');
            var y = window.kesmasDraftYear || String(new Date().getFullYear());
            var labs = collectDraftLabsUsedForType(stId);
            
            var typeMeta = resolveSampleTypeMeta(stId);
            var typeCode = typeMeta ? (typeMeta.code || 'AM') : 'AM';

            function pad4(n) {
                var s = String(n || '').replace(/\D/g, '');
                if (!s) return '';
                while (s.length < 4) s = '0' + s;
                return s;
            }
            
            var $spInK = $('#draft_nomor_spesimen_urut_' + clean);
            var $spInM = $('#draft_nomor_spesimen_mikro_urut_' + clean);

            var uSK = String($spInK.val() || '').replace(/\D/g, '');
            if ($spInK.length) {
                $spInK.val(uSK);
                var $fullK = $('#draft_nomor_spesimen_full_kimia_' + clean);
                if ($fullK.length) {
                    $fullK.val(uSK ? (typeCode + '.01/' + pad4(uSK) + '/' + y) : '');
                }
                $('.draft-preview-urut-sp-kimia-' + clean).text(pad4(uSK) || '…');
            }

            var uSM = String($spInM.val() || '').replace(/\D/g, '');
            if ($spInM.length) {
                $spInM.val(uSM);
                var $fullM = $('#draft_nomor_spesimen_full_mikro_' + clean);
                if ($fullM.length) {
                    $fullM.val(uSM ? (typeCode + '.02/' + pad4(uSM) + '/' + y) : '');
                }
                $('.draft-preview-urut-sp-mikro-' + clean).text(pad4(uSM) || '…');
            }

        }

        function syncDraftKesmasDisplaysAll() {
            (selectedSampleTypes || []).forEach(function(t) {
                var cfg = sampleTypeConfigs[t.id];
                if (!cfg) return;
                var hp = cfg.packets && cfg.packets.length > 0;
                var ha = cfg.additional_methods && cfg.additional_methods.length > 0;
                if (hp || ha) {
                    syncDraftKesmasDisplaysForType(t.id);
                }
            });
        }

        function updateKesmasDraftManualPanel() {
            if (!window.kesmasDraftTableOk) return;
            syncDraftKesmasDisplaysAll();
        }

        function getDraftKesmasUrutsForType(typeId) {
            var clean = String(typeId).replace(/-/g, '');
            return {
                sp_k: String($('#draft_nomor_spesimen_urut_' + clean).val() || '').replace(/\D/g, ''),
                sp_m: String($('#draft_nomor_spesimen_mikro_urut_' + clean).val() || '').replace(/\D/g, '')
            };
        }

        $(document).ready(function() {

            $(document).on('input paste', '[data-draft-kesmas-sanitize="1"]', function() {
                var self = this;
                var st = $(self).attr('data-sample-type-id');
                setTimeout(function() {
                    $(self).val(String($(self).val() || '').replace(/\D/g, ''));
                    if (st) {
                        syncDraftKesmasDisplaysForType(st);
                    }
                }, 0);
            });

            // Initialize pagination for each lab
            @foreach ($laboratoriums as $lab)
                labPagination['{{ $lab->id_laboratorium }}'] = {
                    currentPage: 1,
                    totalItems: 0,
                    totalPages: 1,
                    filteredItems: []
                };
            @endforeach

            // Initialize pagination display (deferred — DOM parameter rows cukup besar)
            deferCreatePageInit(function() {
                initializePagination();
            });

            // Search parameter functionality
            $('#search-parameter').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase().trim();

                if (searchTerm.length > 0) {
                    $('#clear-search').show();
                } else {
                    $('#clear-search').hide();
                }

                filterAndPaginate(searchTerm);
            });

            // Clear search button
            $('#clear-search').on('click', function() {
                $('#search-parameter').val('');
                $(this).hide();
                filterAndPaginate('');
            });

            // Pagination buttons
            $('.btn-page-prev').on('click', function() {
                var labId = $(this).data('lab');
                if (labPagination[labId].currentPage > 1) {
                    labPagination[labId].currentPage--;
                    displayPage(labId);
                }
            });

            $('.btn-page-next').on('click', function() {
                var labId = $(this).data('lab');
                if (labPagination[labId].currentPage < labPagination[labId].totalPages) {
                    labPagination[labId].currentPage++;
                    displayPage(labId);
                }
            });

            // Filter and paginate function
            function filterAndPaginate(searchTerm) {
                var hasResults = false;

                @foreach ($laboratoriums as $lab)
                    var labId = '{{ $lab->id_laboratorium }}';
                    var $rows = $('#method-container-' + labId + ' .method-row');
                    var filtered = [];

                    $rows.each(function() {
                        var methodName = $(this).data('method-name') || '';

                        if (searchTerm === '' || methodName.indexOf(searchTerm) !== -1) {
                            filtered.push($(this));
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });

                    labPagination[labId].filteredItems = filtered;
                    labPagination[labId].totalItems = filtered.length;
                    labPagination[labId].totalPages = Math.ceil(filtered.length / ITEMS_PER_PAGE);
                    labPagination[labId].currentPage = 1;

                    // Show/hide lab group if no results
                    if (filtered.length === 0) {
                        $('[data-lab-group="' + labId + '"]').hide();
                        $('#lab-' + labId).collapse('hide');
                    } else {
                        $('[data-lab-group="' + labId + '"]').show();
                        hasResults = true;

                        // Auto-expand accordion when searching
                        if (searchTerm !== '') {
                            $('#lab-' + labId).collapse('show');
                        }

                        displayPage(labId);
                    }
                @endforeach

                // Show "no results" message if no results found
                if (!hasResults && searchTerm !== '') {
                    // Show alert if all labs are hidden
                    if ($('.parameter-group:visible').length === 0) {
                        if (!$('#no-search-results').length) {
                            $('.col-lg-8').prepend(`
                                <div class="alert alert-warning" id="no-search-results" style="margin-top: 10px;">
                                    <i class="fa fa-exclamation-circle"></i>
                                    <strong>Tidak ada hasil ditemukan</strong> untuk pencarian "<strong>${searchTerm}</strong>".
                                    <br>Coba kata kunci lain atau hapus filter pencarian.
                                </div>
                            `);
                        }
                    }
                } else {
                    $('#no-search-results').remove();
                }
            }

            // Display specific page
            function displayPage(labId) {
                var pagination = labPagination[labId];
                var start = (pagination.currentPage - 1) * ITEMS_PER_PAGE;
                var end = start + ITEMS_PER_PAGE;

                // Hide all items first
                pagination.filteredItems.forEach(function($item) {
                    $item.hide();
                });

                // Show only items for current page
                for (var i = start; i < end && i < pagination.filteredItems.length; i++) {
                    pagination.filteredItems[i].show();
                }

                // Update pagination UI
                var $paginationControl = $('#pagination-' + labId);
                if (pagination.totalPages > 1) {
                    $paginationControl.show();
                    $paginationControl.find('.current-page').text(pagination.currentPage);
                    $paginationControl.find('.total-pages').text(pagination.totalPages);
                    $paginationControl.find('.showing-count').text(Math.min(end, pagination.totalItems));
                    $paginationControl.find('.total-count').text(pagination.totalItems);

                    // Enable/disable buttons
                    $paginationControl.find('.btn-page-prev').prop('disabled', pagination.currentPage === 1);
                    $paginationControl.find('.btn-page-next').prop('disabled', pagination.currentPage === pagination
                        .totalPages);
                } else {
                    $paginationControl.hide();
                }
            }

            // Initialize pagination on load
            function initializePagination() {
                @foreach ($laboratoriums as $lab)
                    var labId = '{{ $lab->id_laboratorium }}';
                    var $rows = $('#method-container-' + labId + ' .method-row');

                    labPagination[labId].filteredItems = $rows.toArray().map(function(el) {
                        return $(el);
                    });
                    labPagination[labId].totalItems = $rows.length;
                    labPagination[labId].totalPages = Math.ceil($rows.length / ITEMS_PER_PAGE);

                    if ($rows.length > ITEMS_PER_PAGE) {
                        displayPage(labId);
                    }
                @endforeach
            }

            // Handle jenis sampel button click (Multiple Selection)
            $('.btn-pick-jenis').on('click', function() {
                var sampleTypeId = $(this).data('id');
                var sampleTypeCode = $(this).data('code');
                var sampleTypeName = $(this).data('name');
                var $button = $(this);
                var $checkIcon = $button.find('.jenis-check-icon');

                // Toggle selection
                var index = selectedSampleTypes.findIndex(function(item) {
                    return item.id === sampleTypeId;
                });

                if (index > -1) {
                    // Deselect
                    selectedSampleTypes.splice(index, 1);
                    delete sampleTypeConfigs[sampleTypeId]; // Remove config
                    $button.removeClass('active');
                    $checkIcon.hide();
                } else {
                    // Select
                    selectedSampleTypes.push({
                        id: sampleTypeId,
                        code: sampleTypeCode,
                        name: sampleTypeName
                    });

                    // Initialize config for this sample type
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [], // Array of packets: [{packet_id, packet_name, packet_price, methods}]
                        additional_methods: [], // Methods selected individually (not from packet)
                        cost: 0,
                        titik_pengambilan: '' // Titik lokasi per sample type
                    };

                    $button.addClass('active');
                    $checkIcon.show();
                }

                updateSelectedSampleTypesDisplay();

                if (selectedSampleTypes.length > 0) {
                    $('#paket-parameter-section').show();
                } else {
                    $('#paket-parameter-section').hide();
                    $('#selected-sampletypes-container').hide();
                }

                try {
                    generateSampleTypeTabs();
                } catch (err) {
                    console.error('generateSampleTypeTabs', err);
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: 'Galat',
                            text: 'Gagal memuat paket & parameter: ' + (err && err.message ? err.message :
                                String(err)),
                            icon: 'error'
                        });
                    }
                }
            });

            // Update selected sample types display
            function updateSelectedSampleTypesDisplay() {
                if (selectedSampleTypes.length > 0) {
                    var badgesHtml = '';
                    selectedSampleTypes.forEach(function(type) {
                        badgesHtml += `
                            <span class="badge badge-lg" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                       color: white; padding: 10px 15px; border-radius: 8px; font-size: 14px;">
                                <i class="fa fa-vial"></i> ${type.code} - ${type.name}
                            </span>
                        `;
                    });
                    $('#selected-sampletypes-badges').html(badgesHtml);
                    $('#selected-sampletypes-container').show();
                } else {
                    $('#selected-sampletypes-container').hide();
                }
            }

            var sampleCodeSequenceMap = {};
            var sequenceCounter = 0;
            var sequenceOrder = [];
            window.updateSampleCodeCards = function() {};
            window.updateNextStepButton = function() {};

            // --- Harga per jenis sampel + tab (sama seperti sample/create) ---

        window.resolvePriceFromMap = function(prices, sampleTypeId, defaultPrice) {
            var sid = String(sampleTypeId || '').trim();
            var def = parseFloat(defaultPrice);
            if (isNaN(def)) {
                def = 0;
            }
            if (!sid || !prices || typeof prices !== 'object') {
                return def;
            }
            if (Array.isArray(prices)) {
                return def;
            }
            if (prices[sid] != null && prices[sid] !== '') {
                var p0 = parseFloat(prices[sid]);
                if (!isNaN(p0)) {
                    return p0;
                }
            }
            var keys = Object.keys(prices);
            for (var i = 0; i < keys.length; i++) {
                if (String(keys[i]).trim() === sid) {
                    var p1 = parseFloat(prices[keys[i]]);
                    if (!isNaN(p1)) {
                        return p1;
                    }
                }
            }
            return def;
        };
            function parsePricesBySampleTypeFromEl(el) {
                var raw = el.getAttribute('data-prices-by-sample-type');
                if (!raw) {
                    return {};
                }
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    return {};
                }
            }

            function fmtRpInt(n) {
                n = parseInt(n, 10) || 0;
                return new Intl.NumberFormat('id-ID').format(n);
            }
            window.applyTabMethodPricesForSampleType = function(sampleTypeId) {
                var sid = String(sampleTypeId || '').trim();
                if (!sid) {
                    return;
                }
                $('.method-checkbox-tab').each(function() {
                    var $cb = $(this);
                    if (String($cb.attr('data-sample-type-id') || '').trim() !== sid) {
                        return;
                    }
                    var prices = parsePricesBySampleTypeFromEl(this);
                    var def = parseFloat($cb.attr('data-default-price'));
                    if (isNaN(def)) {
                        def = parseFloat($cb.attr('data-price')) || 0;
                    }
                    var resolved = window.resolvePriceFromMap(prices, sid, def);
                    $cb.attr('data-price', resolved);
                    $cb.data('price', resolved);
                    var parts = String($cb.attr('data-method') || '').split('_');
                    if (parts.length >= 3) {
                        parts[2] = String(Math.round(resolved));
                        $cb.attr('data-method', parts.join('_'));
                    }
                    var $span = $cb.closest('label').find('span.text-muted');
                    if ($span.length) {
                        $span.text('(Rp ' + fmtRpInt(resolved) + ')');
                    }
                });
            };
            window.formatRupiah = function formatRupiah(angka) {
                if (!angka && angka !== 0) return '0';
                var number_string = angka.toString(),
                    sisa = number_string.length % 3,
                    rupiah = number_string.substr(0, sisa),
                    ribuan = number_string.substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return rupiah;
            }

        /** Kode jenis sampel (code_sample_type) yang wajib mengisi titik pengambilan — tambah kode jika perlu */
        const TITIK_WAJIB_SAMPLE_TYPE_CODES = ['MM'];

        function normalizeSampleTypeCode(code) {
            return String(code || '').trim().toUpperCase();
        }

        function sampleTypeRequiresTitikPengambilan(meta) {
            if (!meta) {
                return false;
            }
            return TITIK_WAJIB_SAMPLE_TYPE_CODES.indexOf(normalizeSampleTypeCode(meta.code)) !== -1;
        }

        window.resolveSampleTypeMeta = function resolveSampleTypeMeta(typeId) {
            var idStr = String(typeId || '').trim();
            var sts = window.selectedSampleTypes || [];
            for (var i = 0; i < sts.length; i++) {
                if (String(sts[i].id) === idStr) {
                    return sts[i];
                }
            }
            var esc = idStr.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
            var $b = $('.btn-pick-jenis[data-id="' + esc + '"]');
            if ($b.length) {
                return {
                    id: idStr,
                    code: $b.attr('data-code') || '',
                    name: $b.attr('data-name') || ''
                };
            }
            return {
                id: idStr,
                code: '',
                name: ''
            };
        }

            function updateAllTitikPengambilanState() {
                var sts = window.selectedSampleTypes || selectedSampleTypes || [];
                sts.forEach(function(type) {
                    updateTitikPengambilanState(type.id);
                });
            }

            // Generate tabs for each selected sample type
            function generateSampleTypeTabs() {
                if (selectedSampleTypes.length === 0) {
                    $('#sampleTypeTabs').html('');
                    $('#sampleTypeTabsContent').html('');
                    return;
                }

                var tabsHtml = '';
                var contentHtml = '';

                selectedSampleTypes.forEach(function(type, index) {
                    var isActive = index === 0 ? 'active' : '';
                    var tabId = 'tab-' + type.id.replace(/-/g, '');

                    // Tab navigation
                    var config = sampleTypeConfigs[type.id] || {};
                    var paramCount = (config.methods || []).length + (config.additional_methods || [])
                        .length;
                    var countBadgeHtml = paramCount > 0 ?
                        `<span id="count-${type.id}" class="badge badge-primary ml-2">${paramCount}</span>` :
                        '';

                    var titikWajibJenis = typeof sampleTypeRequiresTitikPengambilan === 'function' ?
                        sampleTypeRequiresTitikPengambilan(type) : false;
                    var titikInputEnabled = (config.packets && config.packets.length > 0) ||
                        (config.additional_methods && config.additional_methods.length > 0);
                    var titikLabelSuffix = titikWajibJenis ?
                        '<small class="text-danger">*</small> <small class="text-muted">Wajib</small>' :
                        '<small class="text-muted">(Opsional)</small>';
                    var titikHelperText = titikInputEnabled ?
                        `<i class="fa fa-info-circle"></i> Lokasi pengambilan sampel untuk jenis sampel <strong>${type.name}</strong>` :
                        '<i class="fa fa-info-circle"></i> Pilih paket atau parameter terlebih dahulu, lalu isi titik pengambilan.';

                    tabsHtml += `
                        <li class="nav-item">
                            <a class="nav-link ${isActive}" id="${tabId}-tab" data-toggle="tab" 
                               href="#${tabId}" role="tab" aria-controls="${tabId}" aria-selected="${index === 0}">
                                ${type.code} - ${type.name}
                                ${countBadgeHtml}
                            </a>
                        </li>
                    `;

                    // Tab content
                    contentHtml += `
                        <div class="tab-pane fade show ${isActive}" id="${tabId}" role="tabpanel" 
                             aria-labelledby="${tabId}-tab" data-sample-type-id="${type.id}">
                            <div class="row">
                                <div class="col-lg-8">

                                    <!-- Banner: muncul saat belum ada paket/parameter, letak di atas label Pilih Paket -->
                                    <div class="titik-locked-banner" id="titik-locked-${type.id}"
                                        style="${titikInputEnabled ? 'display:none;' : ''}
                                            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
                                            border: 2px dashed #f59e0b;
                                            border-radius: 12px;
                                            padding: 16px 18px;
                                            margin-bottom: 18px;
                                            display: flex;
                                            align-items: flex-start;
                                            gap: 12px;">
                                        <div style="flex-shrink: 0; width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                                                    border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-lock" style="color: white; font-size: 16px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: #92400e; font-size: 14px; margin-bottom: 4px;">
                                                <i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Pilih Paket atau Parameter Dahulu
                                            </div>
                                            <div style="color: #78350f; font-size: 13px; line-height: 1.5;">
                                                Titik pengambilan <strong>${type.code} - ${type.name}</strong> baru bisa diisi
                                                setelah Anda memilih minimal <strong>1 paket</strong> atau <strong>1 parameter</strong> di bawah.
                                            </div>
                                            <div style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                                <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; border:1px solid #f59e0b; border-radius:20px; padding:3px 10px; font-size:12px; color:#92400e;">
                                                    <i class="fa fa-cube"></i> Pilih Paket
                                                </span>
                                                <span style="font-size:12px; color:#b45309; line-height:22px;">atau</span>
                                                <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; border:1px solid #f59e0b; border-radius:20px; padding:3px 10px; font-size:12px; color:#92400e;">
                                                    <i class="fa fa-check-square"></i> Centang Parameter
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4 paket-section-tab" id="paket-section-${type.id}">
                                        <div class="d-flex align-items-center justify-content-between mb-2" style="margin-top:4px;">
                                            <label class="paket-section-label-tab mb-0" id="paket-label-${type.id}"><i class="fa fa-cube"></i> Pilih Paket (Opsional)</label>
                                            <button type="button" class="btn btn-sm btn-success btn-tambah-paket"
                                                data-sample-type-id="${type.id}"
                                                data-sample-type-name="${type.name}"
                                                style="font-size:12px; padding:4px 10px;"
                                                title="Tambah paket baru untuk jenis sampel ini">
                                                <i class="fa fa-plus"></i> Tambah Paket
                                            </button>
                                        </div>
                                        <div class="row packet-buttons-container-tab" data-sample-type-id="${type.id}" style="margin-top: 10px;">
                                            @php $displayedPackets = []; @endphp
                                            @foreach ($packets as $packet)
                                                @if (!in_array($packet->id_packet, $displayedPackets))
                                                    @php $displayedPackets[] = $packet->id_packet; @endphp
                                                    <div class="col-md-6 col-lg-4 mb-3 packet-button-item-tab"
                                                        data-sample-type-id="{{ $packet->sample_type_id ?? '' }}"
                                                        data-packet-id="{{ $packet->id_packet }}" 
                                                        style="display: none;">
                                                        <div style="position:relative;">
                                                            <button type="button" class="btn btn-pick-paket-tab w-100"
                                                                data-sample-type-id="${type.id}"
                                                                data-packet-id="{{ $packet->id_packet }}"
                                                                data-price="{{ $packet->price_total_packet }}"
                                                                data-name="{{ $packet->name_packet }}"
                                                                style="text-align: left; padding: 15px; height: auto; min-height: 80px; border: 2px solid #e2e8f0; background: white; color: #2d3748; border-radius: 8px; transition: all 0.3s;">
                                                                <strong class="paket-name-text">{{ $packet->name_packet }}</strong><br>
                                                                <small style="color: #28a745; font-weight: 500;" class="paket-price-text">
                                                                    <i class="fa fa-tag"></i> Rp {{ number_format($packet->price_total_packet, 0, ',', '.') }}
                                                                </small>
                                                            </button>
                                                            <button type="button" class="btn btn-edit-paket"
                                                                data-packet-id="{{ $packet->id_packet }}"
                                                                data-sample-type-id="{{ $packet->sample_type_id ?? '' }}"
                                                                title="Edit paket ini"
                                                                style="position:absolute; top:5px; right:5px; background:rgba(255,255,255,0.9); border:1px solid #ced4da; border-radius:4px; padding:2px 7px; font-size:11px; cursor:pointer; z-index:2;">
                                                                <i class="fa fa-pencil-alt"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <hr class="paket-section-hr-tab" id="paket-hr-${type.id}" style="margin: 20px 0; border-top: 2px dashed #e2e8f0;">

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0"><i class="fa fa-microscope"></i> Pilih Parameter Pengujian</label>
                                        </div>
                                        <input type="text" class="form-control mb-3 search-parameter-tab" 
                                               data-sample-type-id="${type.id}"
                                               placeholder="🔍 Cari parameter...">
                                        
                                        <div class="parameter-list-tab" data-sample-type-id="${type.id}">
                                            @php $char = 'A'; @endphp
                                            @for ($i = 0; $i < count($data_methods); $i++)
                                                <div class="parameter-group-tab mb-3 parameter-group-item" 
                                                     data-lab-group="{{ $data_methods[$i]->id_lab }}"
                                                     data-lab-name="{{ $data_methods[$i]->name }}"
                                                     data-sample-type-id="${type.id}">
                                                    <div class="d-flex align-items-stretch" style="gap:0;">
                                                        <div class="parameter-group-header flex-grow-1"
                                                             data-toggle="collapse"
                                                             data-target="#lab-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}"
                                                             style="flex:1;">
                                                            <i class="fa fa-chevron-down collapse-icon"></i>
                                                            <strong>{{ $data_methods[$i]->name }}</strong>
                                                            <span class="param-count-tab" id="count-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}">0</span>
                                                        </div>
                                                        <div class="d-flex align-items-stretch" style="flex-shrink:0;">
                                                            <button type="button"
                                                                class="btn btn-success btn-tambah-parameter"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-lab-name="{{ $data_methods[$i]->name }}"
                                                                data-sample-type-id="${type.id}"
                                                                data-sample-type-name="${type.name}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0; flex-shrink:0;"
                                                                title="Tambah Parameter Baru">
                                                                <i class="fa fa-plus"></i> Tambah Baru
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-info btn-tambah-baku-mutu-exist"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-lab-name="{{ $data_methods[$i]->name }}"
                                                                data-sample-type-id="${type.id}"
                                                                data-sample-type-name="${type.name}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0; flex-shrink:0; border-left:0;"
                                                                title="Pilih parameter yang sudah ada lalu tambahkan baku mutunya">
                                                                <i class="fa fa-tag"></i> Tambah Baku Mutu
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-secondary btn-toggle-edit-parameter"
                                                                data-lab-id="{{ $data_methods[$i]->id_lab }}"
                                                                data-sample-type-id="${type.id}"
                                                                style="font-size:12px; padding:4px 10px; border-radius:0 6px 0 0; flex-shrink:0; border-left:0;"
                                                                title="Tampilkan ikon edit pada setiap parameter">
                                                                <i class="fa fa-edit"></i> Edit
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="lab-${type.id.replace(/-/g, '')}-{{ $data_methods[$i]->id_lab }}" class="collapse">
                                                        <div class="card-body" style="background: #f8f9fa;">
                                                            @foreach ($data_methods[$i]->method as $method)
                                                                @php
                                                                    $baku_mutu_sampletypes = $method->baku_mutu_sampletypes ?? [];
                                                                @endphp
                                                                <div class="method-row-tab"
                                                                     data-sample-type-id="${type.id}"
                                                                     data-method-id="{{ $method->id_method }}"
                                                                     data-method-name="{{ strtolower($method->name_method) }}"
                                                                     data-baku-mutu-sampletypes="{{ json_encode($baku_mutu_sampletypes) }}">
                                                                    <label>
                                                                        <input type="checkbox" 
                                                                               class="method-checkbox-tab"
                                                                               data-sample-type-id="${type.id}"
                                                                               data-default-price="{{ $method->price_method }}"
                                                                               data-prices-by-sample-type='@json($method->prices_by_sample_type ?? [])'
                                                                               data-method="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                               data-method-id="{{ $method->id_method }}"
                                                                               data-lab="{{ $data_methods[$i]->id_lab }}"
                                                                               data-labname="{{ $data_methods[$i]->name }}"
                                                                               data-name="{{ $method->name_method }}"
                                                                               data-price="{{ $method->price_method }}">
                                                                        <strong>{{ $method->name_method }}</strong>
                                                                        <span class="text-muted">(Rp {{ number_format($method->price_method, 0, ',', '.') }})</span>
                                                                    </label>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary btn-pencil-edit-method"
                                                                        data-method-id="{{ $method->id_method }}"
                                                                        data-method-name="{{ $method->name_method }}"
                                                                        title="Edit parameter dan harga per jenis sampel">
                                                                        <i class="fa fa-pencil-alt"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                        
                                        <!-- Pagination for parameters -->
                                        <div class="parameter-pagination-tab mt-3" data-sample-type-id="${type.id}" style="display: none;">
                                            <nav aria-label="Parameter pagination">
                                                <ul class="pagination justify-content-center mb-0">
                                                    <li class="page-item disabled" id="prev-page-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active" id="page-1-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#" data-page="1">1</a>
                                                    </li>
                                                    <li class="page-item disabled" id="next-page-${type.id.replace(/-/g, '')}">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                            <div class="text-center mt-2">
                                                <small class="text-muted" id="page-info-${type.id.replace(/-/g, '')}">Menampilkan 1-10 dari 0 parameter</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Titik Pengambilan (Per Jenis Sample) — di bawah section parameter -->
                                    <div class="form-group mt-4 titik-pengambilan-wrapper" id="titik-wrapper-${type.id}">
                                        <label for="titik_pengambilan_${type.id}" style="${titikInputEnabled ? '' : 'display:none;'}">
                                            <i class="fa fa-map-marker-alt"></i> Titik Pengambilan
                                            ${titikLabelSuffix}
                                        </label>
                                        <input type="text" class="form-control titik-pengambilan-input-tab" 
                                            id="titik_pengambilan_${type.id}"
                                            data-sample-type-id="${type.id}"
                                            data-titik-wajib="${titikWajibJenis ? '1' : '0'}"
                                            aria-required="${titikWajibJenis ? 'true' : 'false'}"
                                            ${titikInputEnabled ? '' : 'disabled'}
                                            style="${titikInputEnabled ? '' : 'display:none;'}"
                                            placeholder="Misal: Jl. Sudirman No. 123, Kota ABC">
                                        <small class="form-text titik-helper-${type.id}"
                                            style="${titikInputEnabled ? 'color:#6c757d;' : 'display:none;'}">
                                            ${titikHelperText}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-section-card-sample" 
                                         style="position: sticky; top: 20px; padding: 0; overflow: hidden;">
                                        <div class="card-header"
                                             style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px;">
                                            <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                <i class="fas fa-shopping-cart"></i> Parameter Terpilih
                                            </h5>
                                        </div>
                                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                            <div class="cart-items-list-tab" data-sample-type-id="${type.id}">
                                                <div class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>Belum ada parameter dipilih</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer" style="background: #f8f9fa; padding: 20px;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <strong>Total Parameter:</strong>
                                                <span class="badge badge-lg" id="cart-total-items-${type.id}">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Total Harga:</strong>
                                                <span id="cart-total-price-${type.id}" style="font-size: 1.2rem; font-weight: bold; color: #11998e;">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#sampleTypeTabs').html(tabsHtml);
                $('#sampleTypeTabsContent').html(contentHtml);

                // Filter packets and parameters by sample type for each tab
                selectedSampleTypes.forEach(function(type) {
                    filterPacketsBySampleType(type.id);
                    filterParametersBySampleType(type.id);

                    // Initialize pagination for this tab, lalu terapkan ulang harga (pagination tidak mengubah DOM harga)
                    setTimeout(function() {
                        initParameterPagination(type.id);
                        if (typeof window.applyTabMethodPricesForSampleType === 'function') {
                            window.applyTabMethodPricesForSampleType(type.id);
                        }
                    }, 100);
                });

                // Initialize event handler for titik pengambilan input per sample type
                $(document).off('input change', '.titik-pengambilan-input-tab').on('input change',
                    '.titik-pengambilan-input-tab',
                    function() {
                        var sampleTypeId = $(this).data('sample-type-id');
                        var titikPengambilan = $(this).val() || '';

                        if (sampleTypeConfigs[sampleTypeId]) {
                            sampleTypeConfigs[sampleTypeId].titik_pengambilan = titikPengambilan;
                        }

                        // Update next step button visibility after titik pengambilan changes
                        setTimeout(function() {
                            updateNextStepButton();
                        }, 100);
                    });

                updateAllTitikPengambilanState();
            }
            function hasParameterOrPacketForSampleType(sampleTypeId) {
                var cfg = sampleTypeConfigs[sampleTypeId] || {};
                var hasPacket = cfg.packets && cfg.packets.length > 0;
                var hasAdditionalMethods = cfg.additional_methods && cfg.additional_methods.length > 0;
                return !!(hasPacket || hasAdditionalMethods);
            }

            function updateTitikPengambilanState(sampleTypeId, options) {
                var $input = $('#titik_pengambilan_' + sampleTypeId);
                if ($input.length === 0) {
                    return;
                }

                var canInputTitik = hasParameterOrPacketForSampleType(sampleTypeId);
                var $banner = $('#titik-locked-' + sampleTypeId);
                var $wrapper = $('#titik-wrapper-' + sampleTypeId);
                var $label = $wrapper.find('label[for="titik_pengambilan_' + sampleTypeId + '"]');
                var $helper = $wrapper.find('.titik-helper-' + sampleTypeId);

                if (canInputTitik) {
                    $banner.hide();
                    $label.show();
                    $input.prop('disabled', false).removeAttr('title');
                    $input.show();
                    $helper.show();

                    if ($helper.length) {
                        var sampleTypeName = resolveSampleTypeMeta(sampleTypeId).name || '';
                        $helper.html(
                            '<i class="fa fa-map-marker-alt" style="color:#11998e;"></i> Lokasi pengambilan sampel untuk jenis sampel <strong>' +
                            sampleTypeName + '</strong>');
                        $helper.css('color', '#6c757d');
                    }

                    if (options && options.autoFocus && !($input.val() || '').trim()) {
                        setTimeout(function() { $input.trigger('focus'); }, 250);
                    }
                    return;
                }

                $label.hide();
                $input.prop('disabled', true).attr('title', 'Pilih paket atau parameter terlebih dahulu');
                $input.hide();
                $helper.hide();

                if ($banner.css('display') === 'none') {
                    $banner.css({display: 'flex', opacity: 1});
                }
            }
            // Filter packets by sample type
            function filterPacketsBySampleType(sampleTypeId) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var hasPackets = false;

                $tabContent.find('.packet-button-item-tab').each(function() {
                    var $item = $(this);
                    var packetSampleTypeId = $item.attr('data-sample-type-id');

                    if (packetSampleTypeId && packetSampleTypeId === sampleTypeId) {
                        $item.show();
                        hasPackets = true;
                    } else {
                        $item.hide();
                    }
                });

                // Sembunyikan label, section, dan HR jika tidak ada paket untuk jenis ini
                var $paketSection = $('#paket-section-' + sampleTypeId);
                var $paketHr = $('#paket-hr-' + sampleTypeId);
                if (hasPackets) {
                    $paketSection.show();
                    $paketHr.show();
                } else {
                    $paketSection.hide();
                    $paketHr.hide();
                }
            }

            // Filter parameters by sample type (based on baku_mutu)
            function filterParametersBySampleType(sampleTypeId) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');

                // Lacak grup mana saja yang punya parameter valid
                var groupsWithParams = {};

                $tabContent.find('.method-row-tab').each(function() {
                    var $row = $(this);
                    var bakuMutuAttr = $row.attr('data-baku-mutu-sampletypes');
                    var bakuMutuSampleTypes = [];

                    if (bakuMutuAttr) {
                        try {
                            bakuMutuSampleTypes = JSON.parse(bakuMutuAttr);
                        } catch (e) {
                            console.error('Error parsing baku_mutu_sampletypes:', e);
                        }
                    }

                    var isAllowed = bakuMutuSampleTypes.some(function(id) {
                        return String(id) === String(sampleTypeId);
                    });

                    if (isAllowed) {
                        $row.show();
                        $row.find('.method-checkbox-tab').prop('disabled', false);
                        // Catat grup induk row ini
                        var $group = $row.closest('.parameter-group-item');
                        if ($group.length) {
                            var groupId = $group.attr('data-lab-group');
                            if (groupId) groupsWithParams[groupId] = $group;
                        }
                    } else {
                        $row.hide();
                        $row.find('.method-checkbox-tab').prop('disabled', true);
                    }
                });

                // Tampilkan & expand grup yang punya parameter; sembunyikan yang kosong
                $tabContent.find('.parameter-group-item').each(function() {
                    var $group = $(this);
                    var groupId = $group.attr('data-lab-group');
                    if (groupsWithParams[groupId]) {
                        $group.show();
                        $group.find('.collapse').addClass('show');
                        $group.find('.collapse-icon').css('transform', 'rotate(180deg)');
                    } else {
                        $group.hide();
                        $group.find('.collapse').removeClass('show');
                    }
                });

                if (sampleTypeId && typeof window.applyTabMethodPricesForSampleType === 'function') {
                    window.applyTabMethodPricesForSampleType(sampleTypeId);
                }
            }

            $(document).on('shown.bs.tab', '#sampleTypeTabs a[data-toggle="tab"]', function() {
                var href = $(this).attr('href');
                if (!href) {
                    return;
                }
                var $pane = $(href);
                if (!$pane.length) {
                    return;
                }
                var stid = String($pane.attr('data-sample-type-id') || '').trim();
                if (stid && typeof window.applyTabMethodPricesForSampleType === 'function') {
                    window.applyTabMethodPricesForSampleType(stid);
                }
            });

            // Handle packet selection per tab (MULTIPLE SELECTION - no deselect others)
            $(document).on('click', '.btn-pick-paket-tab', function() {
                var sampleTypeId = $(this).data('sample-type-id');
                var packetId = $(this).data('packet-id');
                var packetName = $(this).data('name');
                var packetPrice = $(this).data('price');
                var $button = $(this);

                // Ensure config exists
                if (!sampleTypeConfigs[sampleTypeId]) {
                    sampleTypeConfigs[sampleTypeId] = {
                        packets: [],
                        additional_methods: [],
                        cost: 0,
                        titik_pengambilan: '' // Titik pengambilan per sample type
                    };
                }

                // Toggle selection (MULTIPLE SELECTION - don't deselect others)
                if ($button.hasClass('active')) {
                    // Deselect this packet only
                    $button.removeClass('active').css({
                        'background': 'white',
                        'border-color': '#e2e8f0',
                        'color': '#2d3748'
                    });
                    // Reset price text color to green when inactive
                    $button.find('small').css('color', '#28a745');

                    // Remove packet from config
                    var config = sampleTypeConfigs[sampleTypeId];
                    if (config.packets) {
                        config.packets = config.packets.filter(function(p) {
                            return p.packet_id !== packetId;
                        });
                    }

                    // Uncheck and enable all checkboxes that were from this packet
                    $('.method-checkbox-tab[data-sample-type-id="' + sampleTypeId + '"][data-packet-id="' +
                        packetId + '"]').each(
                        function() {
                            var $checkbox = $(this);
                            if ($checkbox.closest('.method-row-tab').hasClass('from-packet')) {
                                $checkbox.prop('checked', false).prop('disabled', false);
                                $checkbox.closest('.method-row-tab').removeClass('from-packet');
                            }
                        });

                    // Update display and codes
                    updateTabCart(sampleTypeId);
                    updateSelectedSampleTypesDisplay();
                    updateSampleCodeCards();
                    updateTitikPengambilanState(sampleTypeId);
                    updateNextStepButton();
                } else {
                    // Select this packet (MULTIPLE SELECTION - keep others selected)
                    $button.addClass('active').css({
                        'background': 'linear-gradient(135deg, #4caf50 0%, #45a049 100%)',
                        'border-color': '#4caf50',
                        'color': 'white'
                    });
                    // Update price text color to white when active
                    $button.find('small').css('color', 'rgba(255, 255, 255, 0.95)');

                    // Add packet to config (if not already exists)
                    var config = sampleTypeConfigs[sampleTypeId];
                    if (!config.packets) {
                        config.packets = [];
                    }

                    // Check if packet already exists
                    var existingPacket = config.packets.find(function(p) {
                        return p.packet_id === packetId;
                    });

                    if (!existingPacket) {
                        // Add new packet entry
                        config.packets.push({
                            packet_id: packetId,
                            packet_name: packetName,
                            packet_price: parseFloat(packetPrice) || 0,
                            methods: [] // Will be populated by loadPacketMethodsForTab
                        });
                    }

                    // Load packet methods via AJAX (this will assign sequence and update codes)
                    loadPacketMethodsForTab(sampleTypeId, packetId);

                    // Update Next Step button visibility after packet is selected
                    setTimeout(function() {
                        updateTitikPengambilanState(sampleTypeId, {
                            autoFocus: true
                        });
                        updateNextStepButton();
                    }, 300);
                }
            });

            // Load packet methods for a specific tab
            function loadPacketMethodsForTab(sampleTypeId, packetId) {
                var url = "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}";
                url = url.replace('#', packetId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        id: packetId
                    },
                    success: function(response) {
                        // Check response structure (controller returns 'success' not 'status')
                        if (response.success && response.data && Array.isArray(response.data)) {
                            var config = sampleTypeConfigs[sampleTypeId];
                            if (!config) {
                                config = {
                                    packets: [],
                                    additional_methods: [],
                                    cost: 0
                                };
                                sampleTypeConfigs[sampleTypeId] = config;
                            }

                            // Find or create packet entry
                            if (!config.packets) {
                                config.packets = [];
                            }

                            var packetEntry = config.packets.find(function(p) {
                                return p.packet_id === packetId;
                            });

                            if (!packetEntry) {
                                // Create new packet entry
                                packetEntry = {
                                    packet_id: packetId,
                                    packet_name: null,
                                    packet_price: 0,
                                    methods: []
                                };
                                config.packets.push(packetEntry);
                            }

                            // Clear previous methods from this packet
                            packetEntry.methods = [];

                            // Update packet price from response
                            if (response.price) {
                                packetEntry.packet_price = parseFloat(response.price) || 0;
                            }

                            // FIRST: Extract all unique lab IDs from packet methods
                            var labIdsFromPacket = [];
                            response.data.forEach(function(item) {
                                // Get full method string from checkbox data-method attribute
                                // item.id_method is the method ID from PacketDetail
                                var methodId = item.id_method;
                                var $checkbox = $('.method-checkbox-tab[data-sample-type-id="' +
                                    sampleTypeId + '"][data-method-id="' + methodId + '"]');

                                // If not found, try without sample-type-id filter (fallback)
                                if ($checkbox.length === 0) {
                                    $checkbox = $('.method-checkbox-tab[data-method-id="' +
                                            methodId + '"]')
                                        .filter(function() {
                                            return $(this).closest('.tab-pane').attr(
                                                'data-sample-type-id') === sampleTypeId;
                                        });
                                }

                                if ($checkbox.length) {
                                    var methodString = $checkbox.attr('data-method');
                                    if (methodString) {
                                        // Extract lab ID from method string (format: method_id_lab_id_price)
                                        var parts = methodString.split('_');
                                        if (parts.length >= 2 && !labIdsFromPacket.includes(
                                                parts[1])) {
                                            labIdsFromPacket.push(parts[1]);
                                        }
                                    }
                                } else {
                                    console.warn('Checkbox not found for method_id:', methodId,
                                        'sampleTypeId:', sampleTypeId);
                                }
                            });

                            // SECOND: Assign sequence numbers for new lab combinations BEFORE processing methods
                            // This ensures sequence numbers are assigned immediately when packet is selected
                            labIdsFromPacket.forEach(function(labId) {
                                var sequenceKey = sampleTypeId + '_' + labId;
                                if (!sampleCodeSequenceMap[sequenceKey]) {
                                    sequenceCounter++;
                                    sampleCodeSequenceMap[sequenceKey] = sequenceCounter;
                                    sequenceOrder.push({
                                        sampleTypeId: sampleTypeId,
                                        labId: labId,
                                        sequenceNumber: sequenceCounter
                                    });
                                }
                            });

                            // THIRD: Now process methods and check checkboxes
                            response.data.forEach(function(item) {
                                // Get full method string from checkbox data-method attribute
                                var methodId = item.id_method;
                                var $checkbox = $('.method-checkbox-tab[data-sample-type-id="' +
                                    sampleTypeId + '"][data-method-id="' + methodId + '"]');

                                // If not found, try without sample-type-id filter (fallback)
                                if ($checkbox.length === 0) {
                                    $checkbox = $('.method-checkbox-tab[data-method-id="' +
                                            methodId + '"]')
                                        .filter(function() {
                                            return $(this).closest('.tab-pane').attr(
                                                'data-sample-type-id') === sampleTypeId;
                                        });
                                }

                                if ($checkbox.length) {
                                    var methodString = $checkbox.attr('data-method');
                                    if (methodString) {
                                        // Store method with packet_id attribute for tracking
                                        $checkbox.attr('data-packet-id', packetId);
                                        packetEntry.methods.push(methodString);
                                        $checkbox.prop('checked', true).prop('disabled', true);
                                        $checkbox.closest('.method-row-tab').addClass(
                                            'from-packet');
                                    }
                                } else {
                                    console.warn('Checkbox not found for method_id:', methodId,
                                        'sampleTypeId:', sampleTypeId);
                                }
                            });

                            // Update cart and badge count
                            updateTabCart(sampleTypeId);
                            updateSelectedSampleTypesDisplay();
                            updateNextStepButton(); // Update Next Step button visibility

                            // Update tab badge count (include all packet methods + additional methods)
                            var totalPacketMethods = 0;
                            if (config.packets) {
                                config.packets.forEach(function(p) {
                                    totalPacketMethods += (p.methods || []).length;
                                });
                            }
                            var totalParams = totalPacketMethods + (config.additional_methods || [])
                                .length;
                            var $countBadge = $('#count-' + sampleTypeId);
                            if (totalParams > 0) {
                                if ($countBadge.length === 0) {
                                    // Add badge if it doesn't exist
                                    var tabId = sampleTypeId.replace(/-/g, '');
                                    $('#' + tabId + '-tab').append(
                                        `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParams}</span>`
                                    );
                                } else {
                                    $countBadge.text(totalParams).show();
                                }
                            } else {
                                // Hide badge if no parameters
                                $countBadge.hide();
                            }

                            // Update parameter counts per lab group (including packet methods)
                            updateParameterCountsForTab(sampleTypeId);

                            // Update sample code cards AFTER sequence numbers are assigned
                            updateSampleCodeCards();

                            // Update Next Step button visibility (with delay to ensure all updates are done)
                            setTimeout(function() {
                                updateNextStepButton();
                            }, 200);
                        } else {
                            console.error('Invalid response structure:', response);
                            swal({
                                title: "Error!",
                                text: "Format response tidak valid",
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading packet methods:', xhr);
                        swal({
                            title: "Error!",
                            text: "Gagal memuat detail paket",
                            icon: "error"
                        });

                        // Reset packet selection on error
                        if (sampleTypeConfigs[sampleTypeId]) {
                            sampleTypeConfigs[sampleTypeId].packet_id = null;
                            sampleTypeConfigs[sampleTypeId].packet_name = null;
                            sampleTypeConfigs[sampleTypeId].packet_price = 0;
                        }
                        $button.removeClass('active').css({
                            'background': 'white',
                            'border-color': '#e2e8f0',
                            'color': '#2d3748'
                        });
                        // Reset price text color to green when inactive
                        $button.find('small').css('color', '#28a745');
                        updateTabCart(sampleTypeId);
                        updateSelectedSampleTypesDisplay();
                    }
                });
            }
            /* ── URL AJAX PAKET ── */
            var _packetGetDataUrl  = "{{ url('elits-packet') }}";
            var _packetStoreUrl    = "{{ route('elits-packet.ajax-store') }}";
            var _packetUpdateUrl   = "{{ url('elits-packet') }}";
            var _csrfTokenPaket    = "{{ csrf_token() }}";

            /* ── Helper: hitung & tampilkan total harga paket ── */
            function updateModalPaketTotal() {
                var b = parseInt($('#modal-paket-bahan').val())  || 0;
                var s = parseInt($('#modal-paket-sarana').val()) || 0;
                var j = parseInt($('#modal-paket-jasa').val())   || 0;
                var t = b + s + j;
                $('#modal-paket-total').val(t);
                $('#modal-paket-total-display').text('Rp ' + t.toLocaleString('id-ID'));
            }
            $(document).on('input change', '.modal-paket-price-input', updateModalPaketTotal);

            /* ── Helper: update preview parameter yang dipilih ── */
            function updatePaketSelectedPreview() {
                var items = [];
                $('#modal-paket-method-list .paket-method-cb:checked').each(function() {
                    var label = $(this).parent().text().trim();
                    items.push(label);
                });
                if (!items.length) {
                    $('#modal-paket-selected-preview').html(
                        '<span class="text-muted" id="modal-paket-no-param-msg">Belum ada parameter dipilih</span>');
                } else {
                    var html = '';
                    items.forEach(function(n) {
                        html += '<span class="badge badge-primary mr-1 mb-1" style="font-size:11px;padding:4px 7px;">' +
                            $('<span>').text(n).html() + '</span>';
                    });
                    html += ' <small class="text-muted ml-1">(' + items.length + ' terpilih)</small>';
                    $('#modal-paket-selected-preview').html(html);
                }
            }
            $(document).on('change', '.paket-method-cb', updatePaketSelectedPreview);

            /* ── Helper: bangun daftar method untuk modal paket ── */
            function buildPaketMethodList(stId, checkedIds) {
                var $list = $('#modal-paket-method-list').empty();
                var methods = [];
                var seen    = {};
                // Kumpulkan dari semua method-checkbox-tab yang ada di DOM untuk stId
                $('.method-checkbox-tab').each(function() {
                    var mid  = $(this).data('method-id');
                    var stid = String($(this).data('sample-type-id') || '');
                    if (!mid || seen[mid] || stid !== String(stId)) return;
                    seen[mid] = true;
                    var name = $(this).data('name') ||
                               $(this).closest('.method-row-tab').find('strong').first().text().trim();
                    if (name) methods.push({ id: mid, name: name });
                });

                if (!methods.length) {
                    $list.append('<p class="text-muted text-center p-3 small">Tidak ada parameter tersedia untuk jenis sampel ini.</p>');
                    return;
                }

                var html = '';
                methods.forEach(function(m) {
                    var isChecked = checkedIds && checkedIds.indexOf(m.id) !== -1;
                    var safeName  = $('<span>').text(m.name).html();
                    html += '<label class="paket-method-item" style="display:flex;align-items:center;padding:6px 8px;cursor:pointer;border-bottom:1px solid #f0f0f0;margin:0;font-weight:normal;font-size:13px;">' +
                        '<input type="checkbox" class="paket-method-cb mr-2" value="' + m.id + '"' +
                        (isChecked ? ' checked' : '') + '> ' + safeName + '</label>';
                });
                $list[0].innerHTML = html;
                updatePaketSelectedPreview();
            }

            /* ── Helper: reset form modal paket ── */
            function resetModalPaket() {
                $('#modal-paket-id').val('');
                $('#modal-paket-sample-type-id').val('');
                $('#modal-paket-name').val('');
                $('#modal-paket-bahan').val(0);
                $('#modal-paket-sarana').val(0);
                $('#modal-paket-jasa').val(0);
                $('#modal-paket-total').val(0);
                $('#modal-paket-total-display').text('Rp 0');
                $('#modal-paket-method-search').val('');
                $('#modal-paket-method-list').empty();
                $('#modal-paket-selected-preview').html('<span class="text-muted">Belum ada parameter dipilih</span>');
                $('#modal-paket-alert').hide().text('');
                paketMethodFilter('');
            }

            /* ── TOMBOL TAMBAH PAKET ── */
            $(document).on('click', '.btn-tambah-paket', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var stId   = $(this).data('sample-type-id');
                var stName = $(this).data('sample-type-name') || '';

                resetModalPaket();
                $('#modal-paket-title').html('<i class="fa fa-plus-circle mr-2"></i>Tambah Paket Baru');
                $('#modal-paket-sample-type-id').val(stId);
                buildPaketMethodList(stId, []);
                updateModalPaketTotal();
                $('#modal-paket').modal('show');
            });

            /* ── TOMBOL EDIT PAKET ── */
            $(document).on('click', '.btn-edit-paket', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var packetId = $(this).data('packet-id');
                var stId     = $(this).data('sample-type-id') ||
                               $(this).closest('.packet-button-item-tab').data('sample-type-id');

                resetModalPaket();
                $('#modal-paket-title').html('<i class="fa fa-pencil-alt mr-2"></i>Edit Paket');
                $('#modal-paket-id').val(packetId);
                $('#modal-paket-sample-type-id').val(stId);

                // Tampilkan loading
                $('#modal-paket-form').hide();
                $('#modal-paket-loading').show();
                $('#btn-modal-paket-save').prop('disabled', true);
                $('#modal-paket').modal('show');

                $.ajax({
                    url: _packetGetDataUrl + '/' + packetId + '/data',
                    type: 'GET',
                    success: function(resp) {
                        $('#modal-paket-loading').hide();
                        $('#modal-paket-form').show();
                        $('#btn-modal-paket-save').prop('disabled', false);

                        if (!resp.status) {
                            $('#modal-paket-alert').addClass('alert-danger').text('Gagal memuat data paket.').show();
                            return;
                        }
                        $('#modal-paket-name').val(resp.name_packet);
                        $('#modal-paket-bahan').val(resp.price_bahan_packet  || 0);
                        $('#modal-paket-sarana').val(resp.price_sarana_packet || 0);
                        $('#modal-paket-jasa').val(resp.price_jasa_packet    || 0);
                        updateModalPaketTotal();

                        buildPaketMethodList(stId || resp.sample_type_id, resp.method_ids || []);
                    },
                    error: function() {
                        $('#modal-paket-loading').hide();
                        $('#modal-paket-form').show();
                        $('#btn-modal-paket-save').prop('disabled', false);
                        $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                            .text('Gagal memuat data paket.').show();
                    }
                });
            });

            /* ── SIMPAN (Tambah / Edit) ── */
            $(document).on('click', '#btn-modal-paket-save', function() {
                var packetId  = $('#modal-paket-id').val();
                var stId      = $('#modal-paket-sample-type-id').val();
                var name      = $.trim($('#modal-paket-name').val());
                var bahan     = parseInt($('#modal-paket-bahan').val())  || 0;
                var sarana    = parseInt($('#modal-paket-sarana').val()) || 0;
                var jasa      = parseInt($('#modal-paket-jasa').val())   || 0;
                var total     = bahan + sarana + jasa;
                var methodIds = [];

                $('#modal-paket-method-list .paket-method-cb:checked').each(function() {
                    methodIds.push($(this).val());
                });

                $('#modal-paket-alert').hide();

                if (!name) {
                    $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                        .text('Nama paket tidak boleh kosong.').show();
                    return;
                }
                if (!methodIds.length) {
                    $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                        .text('Pilih minimal satu parameter pengujian.').show();
                    return;
                }

                var isEdit = !!packetId;
                var url    = isEdit
                    ? (_packetUpdateUrl + '/' + packetId + '/ajax/update')
                    : _packetStoreUrl;

                var data = {
                    _token:              _csrfTokenPaket,
                    name_packet:         name,
                    sample_type_id:      stId,
                    price_bahan_packet:  bahan,
                    price_sarana_packet: sarana,
                    price_jasa_packet:   jasa,
                    price_total_packet:  total,
                    methodAttributes:    methodIds
                };

                $('#btn-modal-paket-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

                $.ajax({
                    url:  url,
                    type: 'POST',
                    data: data,
                    success: function(resp) {
                        $('#btn-modal-paket-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                        if (!resp.status) {
                            $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger')
                                .text(resp.pesan || 'Gagal menyimpan.').show();
                            return;
                        }

                        if (isEdit) {
                            // Update teks & harga pada kartu paket di halaman
                            var $card = $('.btn-pick-paket-tab[data-packet-id="' + packetId + '"]');
                            $card.find('.paket-name-text').text(resp.name_packet);
                            var priceFormatted = parseInt(resp.price_total_packet).toLocaleString('id-ID');
                            $card.find('.paket-price-text').html(
                                '<i class="fa fa-tag"></i> Rp ' + priceFormatted);
                            $card.attr('data-price', resp.price_total_packet)
                                .attr('data-name', resp.name_packet);
                        } else {
                            // Tambah kartu paket baru ke semua tab dengan sample_type_id yang sama
                            var newId    = resp.id_packet;
                            var newName  = resp.name_packet;
                            var newTotal = parseInt(resp.price_total_packet) || 0;
                            var priceFormatted = newTotal.toLocaleString('id-ID');

                            var safeId   = String(newId).replace(/"/g, '');
                            var safeName = $('<span>').text(newName).html();

                            var $containers = $('.packet-buttons-container-tab[data-sample-type-id="' + stId + '"]');
                            $containers.each(function() {
                                var $colDiv = $('<div class="col-md-6 col-lg-4 mb-3 packet-button-item-tab">')
                                    .attr('data-sample-type-id', stId)
                                    .attr('data-packet-id', safeId);
                                var $wrap = $('<div style="position:relative;">');
                                var $btn = $('<button type="button" class="btn btn-pick-paket-tab w-100">')
                                    .attr('data-sample-type-id', stId)
                                    .attr('data-packet-id', safeId)
                                    .attr('data-price', newTotal)
                                    .attr('data-name', newName)
                                    .css({ textAlign:'left', padding:'15px', minHeight:'80px',
                                           border:'2px solid #e2e8f0', background:'white',
                                           color:'#2d3748', borderRadius:'8px' })
                                    .html('<strong class="paket-name-text">' + safeName + '</strong><br>' +
                                          '<small class="paket-price-text" style="color:#28a745;font-weight:500;">' +
                                          '<i class="fa fa-tag"></i> Rp ' + priceFormatted + '</small>');
                                var $editBtn = $('<button type="button" class="btn btn-edit-paket">')
                                    .attr('data-packet-id', safeId)
                                    .attr('data-sample-type-id', stId)
                                    .attr('title', 'Edit paket ini')
                                    .css({ position:'absolute', top:'5px', right:'5px',
                                           background:'rgba(255,255,255,0.9)', border:'1px solid #ced4da',
                                           borderRadius:'4px', padding:'2px 7px', fontSize:'11px',
                                           cursor:'pointer', zIndex:2 })
                                    .html('<i class="fa fa-pencil-alt"></i>');
                                $wrap.append($btn, $editBtn);
                                $colDiv.append($wrap);
                                $(this).append($colDiv);
                                // Filter visibility sesuai sample type
                                $colDiv.show();
                            });
                        }

                        swal({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, buttons: false });
                        $('#modal-paket').modal('hide');
                    },
                    error: function(xhr) {
                        $('#btn-modal-paket-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                        var msg = 'Gagal menyimpan paket.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.pesan)  msg = xhr.responseJSON.pesan;
                            if (xhr.responseJSON.errors) {
                                var lines = [];
                                $.each(xhr.responseJSON.errors, function(k, v) {
                                    lines.push($.isArray(v) ? v.join(' ') : String(v));
                                });
                                if (lines.length) msg = lines.join('\n');
                            }
                        }
                        $('#modal-paket-alert').removeClass('alert-success').addClass('alert-danger').text(msg).show();
                    }
                });
            });

            /* ── Reset modal saat ditutup ── */
            $(document).on('hidden.bs.modal', '#modal-paket', function() {
                resetModalPaket();
            });
            // Handle search parameter in tab
            $(document).on('keyup', '.search-parameter-tab', function() {
                var sampleTypeId = $(this).data('sample-type-id');
                var searchTerm = $(this).val().toLowerCase();
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $groups = $tabContent.find('.parameter-group-item');

                if (searchTerm === '') {
                    $tabContent.find('.no-results-message').remove();

                    // Kembalikan ke tampilan awal: hanya parameter yang sudah punya baku mutu
                    // untuk jenis sampel yang sedang aktif
                    filterParametersBySampleType(sampleTypeId);

                    return;
                }

                // Filter groups based on search term
                var visibleCount = 0;
                $groups.each(function() {
                    var $group = $(this);
                    var groupName = $group.find('.parameter-group-header strong').text()
                        .toLowerCase();
                    var $methods = $group.find('.method-row-tab');
                    var hasMatch = false;

                    // Check if group name matches
                    if (groupName.includes(searchTerm)) {
                        hasMatch = true;
                    }

                    // Check if any method name matches
                    var methodMatchCount = 0;
                    $methods.each(function() {
                        var $methodRow = $(this);
                        var methodName = $methodRow.find('strong').text().toLowerCase();
                        var methodLabel = $methodRow.find('label').text().toLowerCase();

                        if (methodName.includes(searchTerm) || methodLabel.includes(
                                searchTerm)) {
                            hasMatch = true;
                            methodMatchCount++;
                            // Show matching method rows
                            $methodRow.show();
                        } else {
                            // Hide non-matching method rows
                            $methodRow.hide();
                        }
                    });

                    if (hasMatch) {
                        $group.show();
                        // Expand group if it has matches
                        var $collapse = $group.find('.collapse');
                        if (!$collapse.hasClass('show')) {
                            $collapse.addClass('show');
                        }
                        visibleCount++;
                    } else {
                        $group.hide();
                        // Hide all methods in this group
                        $methods.hide();
                    }
                });

                // Update pagination for filtered results
                if (visibleCount > 0) {
                    // Get visible groups
                    var $allGroups = $tabContent.find('.parameter-group-item');
                    var $visibleGroups = $allGroups.filter(':visible');

                    // Reinitialize pagination with visible groups only
                    initParameterPaginationForFiltered(sampleTypeId, $visibleGroups);
                } else {
                    // No results found
                    $tabContent.find('.parameter-pagination-tab').hide();
                    // Show message if needed
                    var $parameterList = $tabContent.find('.parameter-list-tab');
                    if ($parameterList.find('.no-results-message').length === 0) {
                        $parameterList.prepend(
                            '<div class="no-results-message alert alert-info text-center mt-3">Tidak ada parameter yang ditemukan</div>'
                        );
                    }
                }
            });

            // Handle parameter checkbox change per tab
            $(document).on('change', '.method-checkbox-tab', function() {
                var $t = $(this);
                var sampleTypeId = String($t.attr('data-sample-type-id') || '').trim();
                var methodString = $t.attr('data-method');
                var config = sampleTypeConfigs[sampleTypeId] || {
                    additional_methods: []
                };

                if ($t.is(':checked')) {
                    if (!config.additional_methods) config.additional_methods = [];
                    config.additional_methods.push({
                        method: methodString,
                        name: $t.attr('data-name'),
                        price: parseFloat($t.attr('data-price')) || 0,
                        lab_name: $t.attr('data-labname')
                    });

                    // Extract lab ID and assign sequence number if new combination
                    var parts = methodString.split('_');
                    if (parts.length >= 2) {
                        var labId = parts[1];
                        var sequenceKey = sampleTypeId + '_' + labId;

                        // Assign sequence number for new lab combination
                        if (!sampleCodeSequenceMap[sequenceKey]) {
                            sequenceCounter++;
                            sampleCodeSequenceMap[sequenceKey] = sequenceCounter;
                            sequenceOrder.push({
                                sampleTypeId: sampleTypeId,
                                labId: labId,
                                sequenceNumber: sequenceCounter
                            });
                        }
                    }
                } else {
                    config.additional_methods = (config.additional_methods || []).filter(function(m) {
                        return m.method !== methodString;
                    });
                }

                updateTabCart(sampleTypeId);

                // Update tab badge count
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var totalParamsCount = (config.methods || []).length + (config.additional_methods || [])
                    .length;
                var $countBadge = $('#count-' + sampleTypeId);
                if (totalParamsCount > 0) {
                    if ($countBadge.length === 0) {
                        // Add badge if it doesn't exist
                        var tabId = sampleTypeId.replace(/-/g, '');
                        $('#' + tabId + '-tab').append(
                            `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParamsCount}</span>`
                        );
                    } else {
                        $countBadge.text(totalParamsCount).show();
                    }
                } else {
                    // Hide badge if no parameters
                    $countBadge.hide();
                }

                updateSelectedSampleTypesDisplay();

                // Update parameter counts per lab group (including packet methods)
                updateParameterCountsForTab(sampleTypeId);

                // Update sample code cards when parameters change
                updateSampleCodeCards();

                updateTitikPengambilanState(sampleTypeId);
                updateNextStepButton();
            });

            // Update cart for specific tab
            function updateTabCart(sampleTypeId) {
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var $cartList = $('.cart-items-list-tab[data-sample-type-id="' + sampleTypeId + '"]');
                var $totalItems = $('#cart-total-items-' + sampleTypeId);
                var $totalPrice = $('#cart-total-price-' + sampleTypeId);

                var totalCost = 0;
                var totalItems = 0;
                var cartHtml = '';

                // Multiple packets info
                if (config.packets && config.packets.length > 0) {
                    config.packets.forEach(function(packet) {
                        cartHtml += `
                            <div class="alert alert-info mb-3" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px;">
                                <strong><i class="fas fa-box"></i> Paket:</strong> ${packet.packet_name || 'Paket Terpilih'}
                                <br><small style="color: #1976d2;">${formatRupiah(packet.packet_price || 0)}</small>
                            </div>
                        `;
                        totalCost += parseFloat(packet.packet_price) || 0;
                        // Count packet methods as items
                        if (packet.methods && packet.methods.length > 0) {
                            totalItems += packet.methods.length;
                        }
                    });
                }

                // Additional methods
                if (config.additional_methods && config.additional_methods.length > 0) {
                    config.additional_methods.forEach(function(method) {
                        cartHtml += `
                            <div class="cart-item mb-2 p-2" style="background: white; border-radius: 5px;">
                                <strong>${method.name}</strong>
                                <br><small>${method.lab_name}</small>
                                <div class="text-success">${formatRupiah(method.price)}</div>
                            </div>
                        `;
                        totalCost += parseFloat(method.price) || 0;
                        totalItems++;
                    });
                }

                // Show empty message only if no packets and no additional methods
                var hasPackets = (config.packets && config.packets.length > 0);
                if (cartHtml === '' && !hasPackets && (!config.additional_methods || config.additional_methods
                        .length === 0)) {
                    cartHtml =
                        '<div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p>Belum ada parameter dipilih</p></div>';
                }

                $cartList.html(cartHtml);
                $totalItems.text(totalItems);
                $totalPrice.text(formatRupiah(totalCost));

                config.cost = totalCost;

                // Update badge count in tab and selected types display
                // Include all packet methods in total count
                var totalParamsCount = totalItems;
                if (hasPackets) {
                    var totalPacketMethods = 0;
                    config.packets.forEach(function(p) {
                        totalPacketMethods += (p.methods || []).length;
                    });
                    totalParamsCount = totalPacketMethods + (config.additional_methods ? config.additional_methods
                        .length : 0);
                }

                // Update tab badge count (show/hide based on count)
                var $countBadge = $('#count-' + sampleTypeId);
                if (totalParamsCount > 0) {
                    if ($countBadge.length === 0) {
                        // Add badge if it doesn't exist
                        var tabId = sampleTypeId.replace(/-/g, '');
                        $('#' + tabId + '-tab').append(
                            `<span id="count-${sampleTypeId}" class="badge badge-primary ml-2">${totalParamsCount}</span>`
                        );
                    } else {
                        $countBadge.text(totalParamsCount).show();
                    }
                } else {
                    // Hide badge if no parameters
                    $countBadge.hide();
                }

                updateSelectedSampleTypesDisplay();

                // Update parameter counts per lab group (including packet methods)
                updateParameterCountsForTab(sampleTypeId);

                // Update sample code cards
                updateSampleCodeCards();

                updateTitikPengambilanState(sampleTypeId);

                // Update Next Step button visibility (with small delay to ensure all updates complete)
                setTimeout(function() {
                    updateNextStepButton();
                }, 100);
            }
            // Store pagination state per tab
            var paginationState = {};

            // Update parameter counts for specific tab (including packet methods)
            function updateParameterCountsForTab(sampleTypeId) {
                var config = sampleTypeConfigs[sampleTypeId] || {};
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');

                // Get all lab groups in this tab
                $tabContent.find('.parameter-group-tab').each(function() {
                    var $group = $(this);
                    var labId = $group.data('lab-group');

                    // Count checked checkboxes in this group
                    var checkedCount = $group.find('.method-checkbox-tab:checked').length;

                    // Also count packet methods for this lab
                    if (config.methods && config.methods.length > 0) {
                        config.methods.forEach(function(methodString) {
                            var parts = methodString.split('_');
                            if (parts.length >= 2 && parts[1] === labId) {
                                // Check if this method is already counted (checkbox might be checked)
                                var methodId = parts[0];
                                var $checkbox = $group.find(
                                    '.method-checkbox-tab[data-method-id="' + methodId + '"]');
                                if ($checkbox.length && !$checkbox.is(':checked')) {
                                    checkedCount++;
                                } else if ($checkbox.length === 0) {
                                    // Method from packet but checkbox not found (shouldn't happen, but count anyway)
                                    checkedCount++;
                                }
                            }
                        });
                    }

                    // Update count badge
                    var $countBadge = $group.find('.param-count-tab');
                    $countBadge.text(checkedCount);

                    // Change badge color
                    if (checkedCount > 0) {
                        $countBadge.removeClass('badge-secondary').addClass('badge-success');
                    } else {
                        $countBadge.removeClass('badge-success').addClass('badge-secondary');
                    }
                });
            }

            // Initialize pagination for parameters (with optional filtered groups)
            function initParameterPagination(sampleTypeId, $filteredGroups) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $parameterList = $tabContent.find('.parameter-list-tab');
                var $pagination = $tabContent.find('.parameter-pagination-tab');
                var itemsPerPage = 20; // Changed from 10 to 20

                // Get parameter groups (use filtered if provided, otherwise all)
                var $groups = $filteredGroups;
                if (!$groups || $groups.length === 0) {
                    // Get all groups (but don't show them all - pagination will handle visibility)
                    $groups = $parameterList.find('.parameter-group-item');
                }
                var totalGroups = $groups.length;

                // Hide all groups first - pagination will show only the current page
                $groups.hide();

                // Store pagination state
                if (!paginationState[sampleTypeId]) {
                    paginationState[sampleTypeId] = {};
                }
                paginationState[sampleTypeId].groups = $groups;
                paginationState[sampleTypeId].currentPage = 1;
                paginationState[sampleTypeId].itemsPerPage = itemsPerPage;

                // Always show pagination, even if items fit in one page
                // This ensures consistent UI and better performance for large lists

                // Show pagination (make sure it's visible)
                $pagination.show().css('display', 'block');

                // Calculate total pages
                var totalPages = Math.ceil(totalGroups / itemsPerPage);
                var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');

                // Generate pagination HTML
                var paginationHtml =
                    '<nav aria-label="Parameter pagination"><ul class="pagination justify-content-center mb-0">';

                // Previous button
                paginationHtml += '<li class="page-item disabled" id="prev-page-' + sampleTypeIdClean + '">';
                paginationHtml += '<a class="page-link" href="#" tabindex="-1">Previous</a></li>';

                // Page numbers
                for (var i = 1; i <= totalPages; i++) {
                    var activeClass = i === 1 ? 'active' : '';
                    paginationHtml += '<li class="page-item ' + activeClass + '" id="page-' + i + '-' +
                        sampleTypeIdClean + '">';
                    paginationHtml += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                }

                // Next button (always show, disabled only if on last page)
                paginationHtml += '<li class="page-item disabled" id="next-page-' + sampleTypeIdClean + '">';
                paginationHtml += '<a class="page-link" href="#">Next</a></li>';
                paginationHtml += '</ul></nav>';

                // Page info
                var startItem = 1;
                var endItem = Math.min(itemsPerPage, totalGroups);
                paginationHtml += '<div class="text-center mt-2">';
                paginationHtml += '<small class="text-muted" id="page-info-' + sampleTypeIdClean + '">';
                paginationHtml += 'Menampilkan ' + startItem + '-' + endItem + ' dari ' + totalGroups +
                    ' parameter';
                paginationHtml += '</small></div>';

                $pagination.html(paginationHtml);

                // Ensure Next button exists and is visible
                var $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                if ($nextBtn.length === 0) {
                    // If Next button doesn't exist, add it
                    $pagination.find('ul').append('<li class="page-item disabled" id="next-page-' +
                        sampleTypeIdClean + '"><a class="page-link" href="#">Next</a></li>');
                    $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                }
                $nextBtn.show().css('display', 'list-item');

                // Show first page (this will also update Next button state)
                showParameterPage(sampleTypeId, 1, itemsPerPage, $groups);

                // Ensure Next button is properly initialized
                if (totalPages <= 1) {
                    $nextBtn.addClass('disabled');
                } else {
                    $nextBtn.removeClass('disabled');
                }

                // Remove existing handlers to prevent duplicates
                $pagination.find('.page-link').off('click');

                // Handle pagination clicks
                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    var $link = $(this);
                    var page = parseInt($link.data('page'));
                    var $li = $link.closest('.page-item');
                    var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');

                    if ($li.hasClass('disabled')) {
                        return;
                    }

                    // Handle Previous/Next
                    if ($link.text().trim() === 'Previous') {
                        var $activePage = $pagination.find('.page-item.active');
                        page = parseInt($activePage.find('.page-link').data('page')) - 1;
                    } else if ($link.text().trim() === 'Next') {
                        var $activePage = $pagination.find('.page-item.active');
                        page = parseInt($activePage.find('.page-link').data('page')) + 1;
                    }

                    var state = paginationState[sampleTypeId] || {};
                    var $groupsToUse = state.groups || $groups;
                    var totalPages = Math.ceil($groupsToUse.length / itemsPerPage);

                    if (page >= 1 && page <= totalPages) {
                        showParameterPage(sampleTypeId, page, itemsPerPage, $groupsToUse);
                        if (paginationState[sampleTypeId]) {
                            paginationState[sampleTypeId].currentPage = page;
                        }
                    }
                });
            }

            // Initialize pagination for filtered groups
            function initParameterPaginationForFiltered(sampleTypeId, $filteredGroups) {
                initParameterPagination(sampleTypeId, $filteredGroups);
            }

            // Show specific page of parameters
            function showParameterPage(sampleTypeId, page, itemsPerPage, $groupsToUse) {
                var $tabContent = $('.tab-pane[data-sample-type-id="' + sampleTypeId + '"]');
                var $parameterList = $tabContent.find('.parameter-list-tab');

                // Use provided groups or get from state
                var $groups = $groupsToUse;
                if (!$groups || $groups.length === 0) {
                    var state = paginationState[sampleTypeId];
                    if (state && state.groups) {
                        $groups = state.groups;
                    } else {
                        $groups = $parameterList.find('.parameter-group-item');
                    }
                }

                var totalGroups = $groups.length;
                var totalPages = Math.ceil(totalGroups / itemsPerPage);

                // Hide all groups first
                $parameterList.find('.parameter-group-item').hide();

                // Show groups for current page
                var startIndex = (page - 1) * itemsPerPage;
                var endIndex = Math.min(startIndex + itemsPerPage, totalGroups);
                $groups.slice(startIndex, endIndex).show();

                // Update pagination UI
                var sampleTypeIdClean = sampleTypeId.replace(/-/g, '');
                var $pagination = $tabContent.find('.parameter-pagination-tab');

                // Update active page
                $pagination.find('.page-item').removeClass('active');
                $pagination.find('#page-' + page + '-' + sampleTypeIdClean).addClass('active');

                // Update Previous button
                var $prevBtn = $pagination.find('#prev-page-' + sampleTypeIdClean);
                if (page === 1) {
                    $prevBtn.addClass('disabled');
                } else {
                    $prevBtn.removeClass('disabled');
                }

                // Update Next button (ensure it exists and is visible)
                var $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                if ($nextBtn.length === 0) {
                    // If Next button doesn't exist, add it
                    $pagination.find('ul').append('<li class="page-item" id="next-page-' + sampleTypeIdClean +
                        '"><a class="page-link" href="#">Next</a></li>');
                    $nextBtn = $pagination.find('#next-page-' + sampleTypeIdClean);
                }
                $nextBtn.show().css('display', 'list-item');

                if (page >= totalPages) {
                    $nextBtn.addClass('disabled');
                } else {
                    $nextBtn.removeClass('disabled');
                }

                // Update page info
                var startItem = startIndex + 1;
                var endItem = endIndex;
                $pagination.find('#page-info-' + sampleTypeIdClean).text(
                    'Menampilkan ' + startItem + '-' + endItem + ' dari ' + totalGroups + ' parameter'
                );
            }

            // --- Modal edit parameter / tambah baku mutu ---
        // ============================================================
        // MODAL TAMBAH PARAMETER - 2 Step
        // ============================================================
        var _methodDataBaseUrl   = @json(rtrim(url('/elits-samples/method'), '/'));
        var _methodUpdateBaseUrl = @json(rtrim(url('/elits-samples/method'), '/'));
        var _csrfTokenEdit       = @json(csrf_token());

        $(document).on('click', '.btn-toggle-edit-parameter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $group = $(this).closest('.parameter-group-item');
            var on = !$group.hasClass('parameter-edit-mode');
            $group.toggleClass('parameter-edit-mode', on);
            $(this).toggleClass('active', on);
        });

        /* ── POPUP EDIT METHOD ── */
        $(document).on('click', '.btn-pencil-edit-method', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var methodId = $(this).data('method-id');
            var methodName = $(this).data('method-name') || '—';
            if (!methodId) return;

            // Ambil konteks jenis sampel dari baris parameter yang diklik
            var currentStId = $(this).closest('.method-row-tab').data('sample-type-id') || '';
            var $tabAnchor  = currentStId
                ? $('[href="#tab-' + currentStId.replace(/-/g, '') + '"]')
                : $([]);
            var currentStName = $tabAnchor.length
                ? $.trim($tabAnchor.clone().children('.badge').remove().end().text())
                : '';

            $('#modal-edit-param-method').data('current-st-id',   currentStId);
            $('#modal-edit-param-method').data('current-st-name', currentStName);

            $('#mepm-title').html('<i class="fa fa-pencil-alt mr-2"></i>' + $('<span>').text(methodName).html());
            $('#mepm-body-wrap').hide();
            $('#mepm-loading').show();
            $('#mepm-alert').hide();
            $('#btn-mepm-save').hide();
            $('#mepm-method-id').val(methodId);
            $('#modal-edit-param-method').modal('show');

            $.ajax({
                url: _methodDataBaseUrl + '/' + encodeURIComponent(methodId) + '/data',
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': _csrfTokenEdit, 'X-Requested-With': 'XMLHttpRequest' },
                success: function(r) {
                    if (!r.status) { mepmShowAlert('danger', r.pesan || 'Gagal memuat data'); return; }
                    var m = r.method;
                    // Text fields
                    $('#mepm-params-method').val(m.params_method || '');
                    $('#mepm-name-method').val(m.name_method || '');
                    $('#mepm-price-bahan').val(m.price_bahan || 0);
                    $('#mepm-price-sarana').val(m.price_sarana || 0);
                    $('#mepm-price-jasa').val(m.price_jasa || 0);
                    $('#mepm-price-total').val(m.price_total_method || 0);
                    // Radios
                    $('input[name="mepm_id_pdam_method"]').prop('checked', false);
                    $('input[name="mepm_id_pdam_method"][value="' + (m.id_pdam_method || '0') + '"]').prop('checked', true);
                    $('input[name="mepm_berhubungan_kesehatan"]').prop('checked', false);
                    $('input[name="mepm_berhubungan_kesehatan"][value="' + (m.berhubungan_kesehatan ?? '') + '"]').prop('checked', true);
                    $('input[name="mepm_jenis_parameter_kimia"]').prop('checked', false);
                    $('input[name="mepm_jenis_parameter_kimia"][value="' + (m.jenis_parameter_kimia ?? '') + '"]').prop('checked', true);
                    $('input[name="mepm_is_ready"]').prop('checked', false);
                    $('input[name="mepm_is_ready"][value="' + (m.is_ready || '1') + '"]').prop('checked', true);
                    // Opsi hasil
                    var hasOption = m.is_option == 1;
                    $('#mepm-is-option').prop('checked', hasOption);
                    $('#mepm-option-wrap').toggle(hasOption);
                    $('#mepm-option-rows').empty();
                    if (hasOption && m.option) {
                        var opts = m.option.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                        opts.forEach(function(v, idx) { mepmAddOptionRow(v, idx === 0 && opts.length === 1); });
                    } else if (hasOption) {
                        mepmAddOptionRow('', true);
                    }
                    mepmUpdateOptionHidden();
                    // Harga per jenis sampel — isi nilai lalu filter
                    $('#mepm-stp-table tbody tr').removeClass('mepm-current-st-row').each(function() {
                        var stId = $(this).data('st-id');
                        $(this).find('input').val(r.sample_type_prices[stId] !== undefined ? r.sample_type_prices[stId] : '');
                    });
                    mepmFilterStpTable(currentStId, currentStName);
                    // Laboratorium checkboxes
                    $('#mepm-lab-list input[type="checkbox"]').each(function() {
                        var labId = $(this).val();
                        $(this).prop('checked', r.method_laboratorium_ids.indexOf(labId) !== -1);
                    });
                    $('#mepm-loading').hide();
                    $('#mepm-body-wrap').show();
                    $('#btn-mepm-save').show();
                },
                error: function(xhr) {
                    mepmShowAlert('danger', 'Gagal memuat data parameter.');
                }
            });
        });

        /* Filter tabel harga per jenis sampel sesuai konteks */
        function mepmFilterStpTable(stId, stName) {
            var $bar  = $('#mepm-stp-filter-bar');
            var $rows = $('#mepm-stp-table tbody tr');
            if (!stId) {
                $rows.show();
                $bar.addClass('d-none').css('display', '');
                return;
            }
            $rows.hide();
            $rows.filter('[data-st-id="' + stId + '"]').addClass('mepm-current-st-row').show();
            $('#mepm-stp-filter-label').text(stName || 'Jenis sampel ini');
            $('#mepm-stp-toggle-all').text('Tampilkan semua jenis');
            $bar.removeClass('d-none').css('display', 'flex');
        }

        $(document).on('click', '#mepm-stp-toggle-all', function() {
            var $rows = $('#mepm-stp-table tbody tr');
            if ($rows.filter(':hidden').length === 0) {
                // Sudah tampil semua → kembali ke filter
                var stId   = $('#modal-edit-param-method').data('current-st-id');
                var stName = $('#modal-edit-param-method').data('current-st-name');
                mepmFilterStpTable(stId, stName);
            } else {
                // Tampil semua
                $rows.show();
                $(this).text('Filter jenis ini saja');
                // Scroll ke baris yang di-highlight
                var $cur = $rows.filter('.mepm-current-st-row');
                if ($cur.length) {
                    var $wrap = $cur.closest('.table-responsive');
                    $wrap.scrollTop($wrap.scrollTop() + $cur.position().top - $wrap.position().top);
                }
            }
        });

        function mepmShowAlert(type, msg) {
            $('#mepm-loading').hide();
            $('#mepm-body-wrap').hide();
            $('#mepm-alert').removeClass('alert-danger alert-success alert-warning').addClass('alert-' + type).text(msg).show();
        }

        function mepmAddOptionRow(value, isSingle) {
            var $btn = isSingle
                ? '<button type="button" class="btn btn-success mepm-btn-add-option" title="Tambah"><i class="fa fa-plus"></i></button>'
                : '<button type="button" class="btn btn-danger mepm-btn-remove-option" title="Hapus"><i class="fa fa-times"></i></button>';
            var $row = $('<div class="input-group mb-2 mepm-option-row">' +
                '<input type="text" class="form-control mepm-option-input" placeholder="Masukkan opsi" value="' + $('<span>').text(value).html() + '">' +
                '<div class="input-group-append">' + $btn + '</div></div>');
            $('#mepm-option-rows').append($row);
        }

        function mepmUpdateOptionHidden() {
            var opts = [];
            $('.mepm-option-input').each(function() {
                var v = $(this).val().trim(); if (v) opts.push(v);
            });
            $('#mepm-option-hidden').val(opts.join(', '));
        }

        $(document).on('input', '.mepm-option-input', mepmUpdateOptionHidden);
        $(document).on('click', '.mepm-btn-add-option', function() {
            $(this).closest('.mepm-option-row').find('.mepm-btn-add-option')
                .removeClass('btn-success mepm-btn-add-option')
                .addClass('btn-danger mepm-btn-remove-option')
                .html('<i class="fa fa-times"></i>').attr('title', 'Hapus');
            mepmAddOptionRow('', false);
        });
        $(document).on('click', '.mepm-btn-remove-option', function() {
            $(this).closest('.mepm-option-row').remove();
            mepmUpdateOptionHidden();
            if ($('#mepm-option-rows .mepm-option-row').length === 1) {
                $('#mepm-option-rows .mepm-btn-remove-option')
                    .removeClass('btn-danger mepm-btn-remove-option')
                    .addClass('btn-success mepm-btn-add-option')
                    .html('<i class="fa fa-plus"></i>').attr('title', 'Tambah');
            }
        });
        $('#mepm-is-option').on('change', function() {
            var on = $(this).is(':checked');
            $('#mepm-option-wrap').toggle(on);
            if (on && $('#mepm-option-rows .mepm-option-row').length === 0) mepmAddOptionRow('', true);
            mepmUpdateOptionHidden();
        });
        $(document).on('input', '#mepm-price-bahan, #mepm-price-sarana, #mepm-price-jasa', function() {
            var t = (parseInt($('#mepm-price-bahan').val()) || 0)
                  + (parseInt($('#mepm-price-sarana').val()) || 0)
                  + (parseInt($('#mepm-price-jasa').val()) || 0);
            $('#mepm-price-total').val(t);
        });

        $(document).on('click', '#btn-mepm-save', function() {
            var methodId = $('#mepm-method-id').val();
            if (!methodId) return;
            var labIds = [];
            $('#mepm-lab-list input[type="checkbox"]:checked').each(function() { labIds.push($(this).val()); });

            var stPrices = {};
            $('#mepm-stp-table tbody tr').each(function() {
                var stId = $(this).data('st-id');
                var val  = $(this).find('input').val().trim();
                if (val !== '') stPrices['sample_type_price[' + stId + ']'] = val;
            });

            var payload = {
                _token:                _csrfTokenEdit,
                params_method:         $('#mepm-params-method').val(),
                name_method:           $('#mepm-name-method').val(),
                id_pdam_method:        $('input[name="mepm_id_pdam_method"]:checked').val() || '0',
                berhubungan_kesehatan: $('input[name="mepm_berhubungan_kesehatan"]:checked').val() ?? '',
                jenis_parameter_kimia: $('input[name="mepm_jenis_parameter_kimia"]:checked').val() ?? '',
                is_ready:              $('input[name="mepm_is_ready"]:checked').val() || '1',
                price_bahan:           parseInt($('#mepm-price-bahan').val()) || 0,
                price_sarana:          parseInt($('#mepm-price-sarana').val()) || 0,
                price_jasa:            parseInt($('#mepm-price-jasa').val()) || 0,
                price_total_method:    parseInt($('#mepm-price-total').val()) || 0,
                'laboratoriumAttributes[]': labIds,
                option:                $('#mepm-option-hidden').val() || '',
            };
            if ($('#mepm-is-option').is(':checked')) { payload['is_option'] = '1'; }
            // Merge harga per jenis sampel
            $.extend(payload, stPrices);

            $('#btn-mepm-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');
            $.ajax({
                url: _methodUpdateBaseUrl + '/' + encodeURIComponent(methodId) + '/update',
                type: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': _csrfTokenEdit, 'X-Requested-With': 'XMLHttpRequest' },
                success: function(r) {
                    $('#btn-mepm-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                    if (r.status) {
                        var defaultPrice  = r.price_total_method || 0;
                        var newPricesMap  = r.sample_type_prices  || {};
                        var newPricesJson = JSON.stringify(newPricesMap);

                        // Update SEMUA checkbox untuk method ini (bisa ada di banyak tab)
                        $('.method-checkbox-tab[data-method-id="' + methodId + '"]').each(function() {
                            var $cb   = $(this);
                            var stId  = String($cb.attr('data-sample-type-id') || '').trim();

                            // Perbarui data-prices-by-sample-type (map lengkap)
                            $cb.attr('data-prices-by-sample-type', newPricesJson);

                            // Perbarui default price (fallback)
                            $cb.attr('data-default-price', defaultPrice).data('default-price', defaultPrice);

                            // Hitung harga yang berlaku untuk jenis sampel ini
                            var resolved = defaultPrice;
                            if (stId && newPricesMap[stId] !== undefined) {
                                resolved = newPricesMap[stId];
                            } else if (typeof window.resolvePriceFromMap === 'function') {
                                resolved = window.resolvePriceFromMap(newPricesMap, stId, defaultPrice);
                            }
                            resolved = Math.round(parseFloat(resolved) || defaultPrice);

                            // Perbarui data-price dan atribut data-method (bagian harga di value)
                            $cb.attr('data-price', resolved).data('price', resolved);
                            var parts = String($cb.attr('data-method') || '').split('_');
                            if (parts.length >= 3) {
                                parts[2] = String(resolved);
                                $cb.attr('data-method', parts.join('_'));
                            }

                            // Perbarui teks label harga
                            $cb.closest('label').find('span.text-muted')
                               .text('(Rp ' + resolved.toLocaleString('id-ID') + ')');

                            // Jika sudah tercentang, trigger change agar total terupdate
                            if ($cb.is(':checked')) { $cb.trigger('change'); }
                        });

                        $('#modal-edit-param-method').modal('hide');
                        swal('Berhasil!', r.pesan, 'success');
                    } else {
                        mepmShowAlert('danger', r.pesan || 'Gagal menyimpan');
                    }
                },
                error: function(xhr) {
                    $('#btn-mepm-save').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                    var msg = 'Gagal menyimpan perubahan.';
                    if (xhr.responseJSON && xhr.responseJSON.pesan) msg = xhr.responseJSON.pesan;
                    mepmShowAlert('danger', msg);
                }
            });
        });

        $(document).on('hidden.bs.modal', '#modal-edit-param-method', function() {
            $('#mepm-alert').hide();
            $('#mepm-body-wrap').hide();
            $('#btn-mepm-save').hide();
            $('#mepm-loading').show();
            // Reset filter tabel
            $('#mepm-stp-table tbody tr').show().removeClass('mepm-current-st-row');
            $('#mepm-stp-filter-bar').addClass('d-none').css('display', '');
            $('#modal-edit-param-method').removeData('current-st-id').removeData('current-st-name');
        });

        /* ── TAMBAH BAKU MUTU DARI PARAMETER YANG SUDAH ADA ── */
        $(document).on('click', '.btn-tambah-baku-mutu-exist', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var labId   = $(this).data('lab-id');
            var labName = $(this).data('lab-name');
            var stId    = $(this).data('sample-type-id');
            var stName  = $(this).data('sample-type-name') || '';

            var isKimia      = labId === '3416ca19-6c69-4e5f-a004-ae8275de7644';
            var isMikro      = labId === 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';
            var isMakMinLain = /makanan|minuman|lainnya/i.test(stName);

            // Kumpulkan method yang BELUM punya baku mutu untuk jenis sampel ini
            var $group = $('.parameter-group-item[data-lab-group="' + labId + '"][data-sample-type-id="' + stId + '"]');
            var methods = [];
            $group.find('.method-row-tab').each(function() {
                var $row = $(this);
                var $cb  = $row.find('.method-checkbox-tab').first();
                var mid  = $cb.data('method-id') || $row.data('method-id');
                if (!mid) return;

                // Cek apakah method ini SUDAH punya baku mutu untuk sample type saat ini
                var bakuMutuRaw = $row.attr('data-baku-mutu-sampletypes') || '[]';
                var bakuMutuIds = [];
                try { bakuMutuIds = JSON.parse(bakuMutuRaw); } catch (e) {}
                var hasBakuMutu = bakuMutuIds.some(function(id) {
                    return String(id) === String(stId);
                });
                if (hasBakuMutu) return; // sudah ada baku mutu → skip

                var name  = $cb.data('name') || $row.find('strong').first().text().trim();
                var price = parseInt($cb.attr('data-default-price')) || 0;
                methods.push({ id: mid, name: name, price: price });
            });

            if (!methods.length) {
                swal(
                    'Semua Sudah Punya Baku Mutu',
                    'Semua parameter di grup ini sudah memiliki baku mutu untuk jenis sampel ini.\n\nGunakan "Tambah Baru" untuk menambah parameter baru.',
                    'info'
                );
                return;
            }

            // Set konteks modal
            $('#modal-tambah-param').data('lab-id',          labId);
            $('#modal-tambah-param').data('lab-name',         labName);
            $('#modal-tambah-param').data('sample-type-id',   stId);
            $('#modal-tambah-param').data('sample-type-name', stName);
            $('#modal-tambah-param').data('is-kimia',         isKimia);
            $('#modal-tambah-param').data('is-mikro',         isMikro);
            $('#modal-tambah-param').data('is-mml',           isMakMinLain);

            // Badge lab
            $('#modal-param-lab-badge').text(labName).removeClass('badge-success badge-info badge-secondary');
            if (isKimia) $('#modal-param-lab-badge').addClass('badge-success');
            else if (isMikro) $('#modal-param-lab-badge').addClass('badge-info');
            else $('#modal-param-lab-badge').addClass('badge-secondary');

            // Bangun daftar parameter di Step 0 via innerHTML (paling andal)
            var listHtml = '';
            methods.forEach(function(m) {
                // HTML-escape nama agar aman untuk atribut & teks
                var safeName  = $('<span>').text(m.name).html();
                var safeId    = String(m.id).replace(/"/g, '');
                var safePrice = parseInt(m.price) || 0;
                var priceFormatted = safePrice.toLocaleString('id-ID');
                listHtml +=
                    '<div class="mpicker-row align-items-center p-2"' +
                    ' data-method-id="' + safeId + '"' +
                    ' data-method-price="' + safePrice + '"' +
                    ' style="display:flex;cursor:pointer;border-bottom:1px solid #f0f0f0;border-radius:4px;margin-bottom:2px;">' +
                    '<span class="flex-grow-1" style="font-size:13px;"><strong>' + safeName + '</strong></span>' +
                    '<span class="text-muted ml-2" style="font-size:12px;white-space:nowrap;">Rp ' + priceFormatted + '</span>' +
                    '<span class="btn btn-sm btn-primary ml-2 py-0 px-2" style="font-size:12px;">Pilih</span>' +
                    '</div>';
            });
            document.getElementById('mpicker-list').innerHTML = listHtml;
            $('#mpicker-count').text(methods.length);
            $('#mpicker-search').val('');
            bindMpickerSearch(); // ikat event langsung setelah elemen siap

            // Tampilkan Step 0, sembunyikan step lain
            $('#modal-param-step0').show();
            $('#modal-param-step1').hide();
            $('#modal-param-step2').hide();
            $('#modal-footer-step0').show();
            $('#modal-footer-step1').hide();
            $('#modal-footer-step2').hide();
            $('#modal-param-step-indicator-1').removeClass('active done');
            $('#modal-param-step-indicator-2').removeClass('active done');
            $('#modal-tambah-param-title').text('Tambah Baku Mutu — Pilih Parameter');
            $('#modal-tambah-param').modal('show');
        });

        // Filter list parameter di Step 0
        // Gunakan delegasi ke #modal-tambah-param (bukan document) agar tidak
        // terganggu oleh focus-trapping Bootstrap
        $('#modal-tambah-param').on('input keyup', '#mpicker-search', function() {
            var q = $.trim($(this).val()).toLowerCase();
            $('#mpicker-list .mpicker-row').each(function() {
                var name = $(this).find('strong').first().text().toLowerCase();
                if (q === '' || name.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Setelah modal fully shown: fokus ke search input (jika Step 0 aktif)
        $('#modal-tambah-param').on('shown.bs.modal', function() {
            if ($('#modal-param-step0').is(':visible')) {
                $('#mpicker-search').trigger('focus');
            }
        });

        function bindMpickerSearch() {
            // Rebind langsung sebagai fallback tambahan
            $('#mpicker-search').off('.mpicker').on('input.mpicker keyup.mpicker', function() {
                var q = $.trim($(this).val()).toLowerCase();
                $('#mpicker-list .mpicker-row').each(function() {
                    var name = $(this).find('strong').first().text().toLowerCase();
                    if (q === '' || name.indexOf(q) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }

        // Hover style pada baris picker
        $(document).on('mouseenter', '.mpicker-row', function() {
            $(this).css('background', '#f0f4ff');
        }).on('mouseleave', '.mpicker-row', function() {
            $(this).css('background', '');
        });

        // Pilih parameter → loncat ke Step 2
        // Nama dibaca dari <strong> (bukan data attribute) agar encoding aman
        $(document).on('click', '.mpicker-row', function() {
            var methodId   = $(this).attr('data-method-id');
            var paramName  = $(this).find('strong').first().text().trim();
            var priceTotal = parseInt($(this).attr('data-method-price')) || 0;
            goToStep2Existing(methodId, paramName, priceTotal);
        });

        /** Loncat langsung ke Step 2 (Baku Mutu) dengan method yang sudah ada */
        function goToStep2Existing(methodId, paramName, priceTotal) {
            var labName = $('#modal-tambah-param').data('lab-name');
            var stId    = $('#modal-tambah-param').data('sample-type-id');
            var isKimia = $('#modal-tambah-param').data('is-kimia');
            var isMML   = $('#modal-tambah-param').data('is-mml');

            // Simpan data untuk injectNewParameter jika perlu
            $('#modal-tambah-param').data('new-method-id',   methodId);
            $('#modal-tambah-param').data('new-param-name',  paramName);
            $('#modal-tambah-param').data('new-price-total', priceTotal);
            $('#modal-param-result-method-id').val(methodId);

            // Setup Step 2
            $('#modal-bm-method-id').val(methodId);
            $('#modal-bm-sampletype-id').val(stId);
            $('#modal-step2-param-name').text(paramName);
            $('#modal-step2-lab-name').text(labName);

            if (isKimia) {
                $('#modal-bm-form-title').text('Baku Mutu Kimia');
                $('#modal-bm-store-url').val("{{ route('elits-baku-mutu-kimia.store') }}");
            } else {
                $('#modal-bm-form-title').text('Baku Mutu Mikro');
                $('#modal-bm-store-url').val("{{ route('elits-baku-mutu-mikro.store') }}");
            }

            if (isMML) {
                $('#modal-bm-mml-section').show();
            } else {
                $('#modal-bm-mml-section').hide();
            }

            // Reset form baku mutu
            if (typeof window.removeBmTinyInlineEditors === 'function') {
                window.removeBmTinyInlineEditors();
            }
            $('#modal-bm-min').val('');
            $('#modal-bm-max').val('');
            $('#modal-bm-equal').val('');
            $('#modal-bm-nilai').val('');
            $('input[name="modal_bm_is_sub"][value="false"]').prop('checked', true).trigger('change');
            $('input[name="modal_bm_tipe_nilai"][value="kuantitatif"]').prop('checked', true);
            $('#sdd-jenis-makanan').find('.sdd-display').text('— Pilih Jenis Makanan —').addClass('sdd-placeholder');
            $('#modal-bm-jenis-makanan-id').val('');

            // Tampilkan Step 2
            $('#modal-param-step0').hide();
            $('#modal-param-step1').hide();
            $('#modal-param-step2').show();
            $('#modal-footer-step0').hide();
            $('#modal-footer-step1').hide();
            $('#modal-footer-step2').show();
            $('#modal-param-step-indicator-1').removeClass('active').addClass('done');
            $('#modal-param-step-indicator-2').addClass('active');
            $('#modal-tambah-param-title').text('Tambah Baku Mutu — ' + paramName);

            if (typeof window.scheduleInitBmTinyInlineEditors === 'function') {
                window.scheduleInitBmTinyInlineEditors();
            }
        }

        $(document).on('click', '.btn-tambah-parameter', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var labId     = $(this).data('lab-id');
            var labName   = $(this).data('lab-name');
            var stId      = $(this).data('sample-type-id');
            var stName    = $(this).data('sample-type-name') || '';

            // Tentukan tipe lab
            var isKimia = labId === '3416ca19-6c69-4e5f-a004-ae8275de7644';
            var isMikro = labId === 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';

            // Deteksi apakah jenis sampel Makanan/Minuman/Lainnya
            var isMakMinLain = /makanan|minuman|lainnya/i.test(stName);

            // Set state modal
            $('#modal-tambah-param').data('lab-id',        labId);
            $('#modal-tambah-param').data('lab-name',      labName);
            $('#modal-tambah-param').data('sample-type-id',   stId);
            $('#modal-tambah-param').data('sample-type-name', stName);
            $('#modal-tambah-param').data('is-kimia',      isKimia);
            $('#modal-tambah-param').data('is-mikro',      isMikro);
            $('#modal-tambah-param').data('is-mml',        isMakMinLain);

            // Reset form step 1
            $('#form-step1-param')[0].reset();
            // Pre-set laboratorium hidden field
            $('#modal-param-lab-id').val(labId);

            // Badge lab
            $('#modal-param-lab-badge').text(labName).removeClass('badge-success badge-info badge-secondary');
            if (isKimia) $('#modal-param-lab-badge').addClass('badge-success');
            else if (isMikro) $('#modal-param-lab-badge').addClass('badge-info');
            else $('#modal-param-lab-badge').addClass('badge-secondary');

            // Tampilkan step 1, sembunyikan step 0 & 2
            $('#modal-param-step0').hide();
            $('#modal-param-step1').show();
            $('#modal-param-step2').hide();
            $('#modal-footer-step0').hide();
            $('#modal-footer-step1').show();
            $('#modal-footer-step2').hide();
            $('#modal-param-step-indicator-1').addClass('active').removeClass('done');
            $('#modal-param-step-indicator-2').removeClass('active done');
            $('#modal-tambah-param-title').text('Tambah Parameter Baru — Step 1: Detail Parameter');
            $('#modal-param-result-method-id').val('');

            $('#modal-tambah-param').modal('show');
        });

        // ---- Searchable Dropdown (SDD) — tanpa Select2 ----
        // Tutup semua panel SDD saat klik di luar
        $(document).on('mousedown', function(e) {
            if (!$(e.target).closest('.sdd-wrap').length) {
                $('.sdd-wrap').removeClass('sdd-open');
            }
        });
        // Buka / tutup panel saat klik area display
        $(document).on('mousedown', '.sdd-display', function(e) {
            e.stopPropagation();
            var $wrap = $(this).closest('.sdd-wrap');
            var wasOpen = $wrap.hasClass('sdd-open');
            $('.sdd-wrap').removeClass('sdd-open');
            if (!wasOpen) {
                $wrap.addClass('sdd-open');
                $wrap.find('.sdd-search').val('').trigger('input').focus();
            }
        });
        // Filter list saat mengetik
        $(document).on('input', '.sdd-search', function() {
            var q = $(this).val().toLowerCase();
            $(this).closest('.sdd-panel').find('.sdd-list li').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(q) !== -1);
            });
        });
        // Pilih item
        $(document).on('mousedown', '.sdd-list li', function(e) {
            e.stopPropagation();
            var $wrap  = $(this).closest('.sdd-wrap');
            var val    = $(this).data('value');
            var label  = $(this).text();
            $wrap.find('.sdd-display').text(label).toggleClass('sdd-placeholder', !val);
            // tulis nilai ke hidden input berdasarkan id wrap
            if ($wrap.attr('id') === 'sdd-unit')          { $('#modal-bm-unit-id').val(val); }
            if ($wrap.attr('id') === 'sdd-library')       { $('#modal-bm-library-id').val(val); }
            if ($wrap.attr('id') === 'sdd-jenis-makanan') { $('#modal-bm-jenis-makanan-id').val(val); }
            $wrap.removeClass('sdd-open');
        });
        // Reset SDD dan field MML saat modal ditutup
        $(document).on('hidden.bs.modal', '#modal-tambah-param', function() {
            if (typeof window.removeBmTinyInlineEditors === 'function') {
                window.removeBmTinyInlineEditors();
            }
            $('#sdd-unit').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Satuan —').addClass('sdd-placeholder');
            $('#modal-bm-unit-id').val('');
            $('#sdd-library').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Acuan —').addClass('sdd-placeholder');
            $('#modal-bm-library-id').val('');
            $('#sdd-jenis-makanan').removeClass('sdd-open')
                .find('.sdd-display').text('— Pilih Jenis Makanan —').addClass('sdd-placeholder');
            $('#modal-bm-jenis-makanan-id').val('');
            $('#modal-bm-mml-section').hide();
            $('input[name="modal_bm_tipe_nilai"][value="kuantitatif"]').prop('checked', true);
        });

        // Step 1 → Step 2
        $(document).on('click', '#btn-modal-param-next', function() {
            var labId       = $('#modal-tambah-param').data('lab-id');
            var isKimia     = $('#modal-tambah-param').data('is-kimia');
            var isMikro     = $('#modal-tambah-param').data('is-mikro');
            var stId        = $('#modal-tambah-param').data('sample-type-id');

            var paramsMethod = $.trim($('#modal-param-params-method').val());
            var nameMethod   = $.trim($('#modal-param-name-method').val());
            if (!paramsMethod || !nameMethod) {
                swal('Peringatan', 'Nama Parameter dan Metode wajib diisi!', 'warning');
                return;
            }

            // AJAX store method
            var formData = {
                _token: $('#csrf-token').val() || $('input[name="_token"]').first().val(),
                params_method: paramsMethod,
                name_method: nameMethod,
                berhubungan_kesehatan: $('input[name="modal_berhubungan_kesehatan"]:checked').val() || '0',
                jenis_parameter_kimia: $('input[name="modal_jenis_parameter_kimia"]:checked').val() || '',
                is_ready: $('input[name="modal_is_ready"]:checked').val() || '1',
                price_bahan: parseInt($('#modal-param-price-bahan').val()) || 0,
                price_sarana: parseInt($('#modal-param-price-sarana').val()) || 0,
                price_jasa: parseInt($('#modal-param-price-jasa').val()) || 0,
                price_total_method: parseInt($('#modal-param-price-total').val()) || 0,
                id_pdam_method: '0',
                laboratoriumAttributes: [labId]
            };

            $('#btn-modal-param-next').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            var csrfToken = $('#csrf-token').val() || $('input[name="_token"]').first().val();
            $.ajax({
                url: "{{ route('elits-methods.store') }}",
                type: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    $('#btn-modal-param-next').prop('disabled', false).html('Lanjut ke Baku Mutu <i class="fa fa-arrow-right ml-1"></i>');
                    if (resp.status) {
                        // Simpan method_id untuk step 2
                        $('#modal-param-result-method-id').val(resp.method_id);

                        // Pindah ke step 2
                        $('#modal-param-step1').hide();
                        $('#modal-param-step2').show();
                        $('#modal-footer-step1').hide();
                        $('#modal-footer-step2').show();
                        $('#modal-param-step-indicator-1').removeClass('active').addClass('done');
                        $('#modal-param-step-indicator-2').addClass('active');
                        $('#modal-tambah-param-title').text('Tambah Parameter Baru — Step 2: Baku Mutu');

                        // Set info di step 2
                        var labName = $('#modal-tambah-param').data('lab-name');
                        var stName  = $('[data-sample-type-id="' + stId + '"].tab-pane').first().attr('id') || stId;
                        $('#modal-step2-param-name').text(paramsMethod);
                        $('#modal-step2-lab-name').text(labName);

                        // Set method_id dan sampletype_id di form baku mutu
                        $('#modal-bm-method-id').val(resp.method_id);
                        $('#modal-bm-sampletype-id').val(stId);

                        // Judul form baku mutu sesuai lab
                        if (isKimia) {
                            $('#modal-bm-form-title').text('Baku Mutu Kimia');
                            $('#modal-bm-store-url').val("{{ route('elits-baku-mutu-kimia.store') }}");
                        } else {
                            $('#modal-bm-form-title').text('Baku Mutu Mikro');
                            $('#modal-bm-store-url').val("{{ route('elits-baku-mutu-mikro.store') }}");
                        }

                        // Tampilkan / sembunyikan section Makanan-Minuman-Lainnya
                        var isMML = $('#modal-tambah-param').data('is-mml');
                        if (isMML) {
                            $('#modal-bm-mml-section').show();
                        } else {
                            $('#modal-bm-mml-section').hide();
                        }

                        // Simpan info untuk inject parameter setelah baku mutu berhasil
                        var priceTotal = parseInt($('#modal-param-price-total').val()) || 0;
                        $('#modal-tambah-param').data('new-method-id',   resp.method_id);
                        $('#modal-tambah-param').data('new-param-name',  paramsMethod);
                        $('#modal-tambah-param').data('new-price-total', priceTotal);

                        // Reset form baku mutu
                        if (typeof window.removeBmTinyInlineEditors === 'function') {
                            window.removeBmTinyInlineEditors();
                        }
                        $('#modal-bm-min').val('');
                        $('#modal-bm-max').val('');
                        $('#modal-bm-equal').val('');
                        $('#modal-bm-nilai').val('');
                        $('input[name="modal_bm_is_sub"][value="false"]').prop('checked', true).trigger('change');
                        $('input[name="modal_bm_tipe_nilai"][value="kuantitatif"]').prop('checked', true);
                        $('#sdd-jenis-makanan').find('.sdd-display').text('— Pilih Jenis Makanan —').addClass('sdd-placeholder');
                        $('#modal-bm-jenis-makanan-id').val('');

                        if (typeof window.scheduleInitBmTinyInlineEditors === 'function') {
                            window.scheduleInitBmTinyInlineEditors();
                        }
                    } else {
                        swal('Gagal', resp.pesan || 'Gagal menyimpan parameter!', 'error');
                    }
                },
                error: function(xhr) {
                    $('#btn-modal-param-next').prop('disabled', false).html('Lanjut ke Baku Mutu <i class="fa fa-arrow-right ml-1"></i>');
                    var msg = 'Gagal menyimpan parameter!';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    swal('Error', msg, 'error');
                }
            });
        });

        // Toggle sub baku mutu di modal
        $(document).on('change', 'input[name="modal_bm_is_sub"]', function() {
            if ($(this).val() === 'true') {
                if (typeof window.removeBmTinyInlineEditors === 'function') {
                    window.removeBmTinyInlineEditors();
                }
                $('#modal-bm-no-sub').hide();
                $('#modal-bm-sub-container').show();
            } else {
                $('#modal-bm-no-sub').show();
                $('#modal-bm-sub-container').hide();
                if (typeof window.scheduleInitBmTinyInlineEditors === 'function') {
                    window.scheduleInitBmTinyInlineEditors();
                }
            }
        });

        // Harga total otomatis di modal
        $(document).on('input', '#modal-param-price-bahan, #modal-param-price-sarana, #modal-param-price-jasa', function() {
            var total = (parseInt($('#modal-param-price-bahan').val()) || 0)
                      + (parseInt($('#modal-param-price-sarana').val()) || 0)
                      + (parseInt($('#modal-param-price-jasa').val()) || 0);
            $('#modal-param-price-total').val(total);
        });

        // Simpan baku mutu (step 2)
        $(document).on('click', '#btn-modal-bm-save', function() {
            var storeUrl = $('#modal-bm-store-url').val();
            var isSub    = $('input[name="modal_bm_is_sub"]:checked').val() === 'true';

            var isMML    = $('#modal-tambah-param').data('is-mml');
            var tipeNilai = $('input[name="modal_bm_tipe_nilai"]:checked').val() || '';

            // Validasi client-side untuk Makanan/Minuman/Lainnya
            if (isMML && !tipeNilai) {
                swal('Peringatan', 'Tipe Nilai Baku Mutu wajib dipilih untuk jenis sampel ini!', 'warning');
                return;
            }

            if (typeof tinymce !== 'undefined' && tinymce.triggerSave) {
                tinymce.triggerSave();
            }

            var bmData = {
                _token: $('#csrf-token').val() || $('input[name="_token"]').first().val(),
                sampletype_id: $('#modal-bm-sampletype-id').val(),
                method_id: $('#modal-bm-method-id').val(),
                unit_id: $('#modal-bm-unit-id').val() || null,
                library_id: $('#modal-bm-library-id').val() || null,
                is_sub: isSub ? 'true' : 'false',
                min_no_sub: $('#modal-bm-min').val(),
                max_no_sub: $('#modal-bm-max').val(),
                equal_no_sub: $('#modal-bm-equal').val(),
                nilai_baku_mutu_no_sub: $('#modal-bm-nilai').val(),
                jenis_makanan_id: $('#modal-bm-jenis-makanan-id').val() || null,
                tipe_nilai_baku_mutu: isMML ? tipeNilai : null,
            };

            if (!bmData.sampletype_id || !bmData.method_id) {
                swal('Peringatan', 'Jenis sampel dan parameter wajib ada!', 'warning');
                return;
            }

            $('#btn-modal-bm-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: storeUrl,
                type: 'POST',
                data: bmData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': bmData._token
                },
                success: function(resp) {
                    $('#btn-modal-bm-save').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Simpan Baku Mutu');
                    if (resp.status) {
                        // Update data-baku-mutu-sampletypes pada row DOM agar
                        // tidak muncul lagi di picker "Tambah Baku Mutu" tanpa refresh
                        var savedMethodId = bmData.method_id;
                        var savedStId     = bmData.sampletype_id;
                        if (savedMethodId && savedStId) {
                            var $rows = $('.method-row-tab[data-method-id="' + savedMethodId + '"]');
                            $rows.each(function() {
                                var cur = [];
                                try { cur = JSON.parse($(this).attr('data-baku-mutu-sampletypes') || '[]'); } catch(e) {}
                                if (!cur.includes(savedStId)) { cur.push(savedStId); }
                                $(this).attr('data-baku-mutu-sampletypes', JSON.stringify(cur));
                            });
                        }
                        injectNewParameter();
                        $('#modal-tambah-param').modal('hide');
                        swal('Berhasil!', 'Parameter dan baku mutu berhasil ditambahkan!', 'success');
                    } else {
                        swal('Gagal', resp.pesan || 'Gagal menyimpan baku mutu!', 'warning');
                    }
                },
                error: function(xhr) {
                    $('#btn-modal-bm-save').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Simpan Baku Mutu');
                    swal('Error', 'Gagal menyimpan baku mutu!', 'error');
                }
            });
        });

        // Lewati step 2 (simpan tanpa baku mutu)
        $(document).on('click', '#btn-modal-bm-skip', function() {
            swal({
                title: 'Lewati Baku Mutu?',
                text: 'Parameter akan disimpan tanpa baku mutu. Baku mutu bisa ditambahkan nanti.',
                icon: 'info',
                buttons: { cancel: 'Batal', confirm: 'Ya, Lewati' }
            }).then(function(ok) {
                if (ok) {
                    injectNewParameter();
                    $('#modal-tambah-param').modal('hide');
                }
            });
        });

        // ---- Inject parameter baru ke DOM dan auto-centang ----
        function injectNewParameter() {
            var $modal    = $('#modal-tambah-param');
            var methodId  = $modal.data('new-method-id');
            var paramName = $modal.data('new-param-name');
            var price     = $modal.data('new-price-total') || 0;
            var labId     = $modal.data('lab-id');
            var labName   = $modal.data('lab-name');
            var stId      = $modal.data('sample-type-id');

            if (!methodId || !stId || !labId) return;

            // Cek apakah method row sudah ada (hindari duplikasi)
            if ($('.method-checkbox-tab[data-method-id="' + methodId + '"][data-sample-type-id="' + stId + '"]').length) {
                return;
            }

            var priceFormatted = price.toLocaleString('id-ID');
            var methodKey      = methodId + '_' + labId + '_' + price;

            var $row = $('<div class="method-row-tab">')
                .attr('data-sample-type-id', stId)
                .attr('data-method-id',      methodId)
                .attr('data-method-name',    paramName.toLowerCase())
                .attr('data-baku-mutu-sampletypes', '[]')
                .html(
                    '<label>' +
                    '<input type="checkbox" class="method-checkbox-tab"' +
                    ' data-sample-type-id="' + stId + '"' +
                    ' data-default-price="'  + price + '"' +
                    ' data-prices-by-sample-type="{}"' +
                    ' data-method="'         + methodKey + '"' +
                    ' data-method-id="'      + methodId + '"' +
                    ' data-lab="'            + labId + '"' +
                    ' data-labname="'        + labName + '"' +
                    ' data-name="'           + paramName + '"' +
                    ' data-price="'          + price + '">' +
                    '<strong>' + $('<span>').text(paramName).html() + '</strong> ' +
                    '<span class="text-muted">(Rp ' + priceFormatted + ')</span>' +
                    '</label>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-pencil-edit-method"' +
                    ' data-method-id="' + methodId + '"' +
                    ' data-method-name="' + $('<span>').text(paramName).html() + '"' +
                    ' title="Edit parameter dan harga per jenis sampel">' +
                    '<i class="fa fa-pencil-alt"></i></button>'
                );

            // Cari card-body grup yang sesuai (lab + sample type)
            var $group = $('.parameter-group-item[data-lab-group="' + labId + '"][data-sample-type-id="' + stId + '"]');
            if (!$group.length) return;

            $group.find('.card-body').first().append($row);

            // Pastikan grup terlihat & terbuka
            $group.show();
            $group.find('.collapse').addClass('show');
            $group.find('.collapse-icon').css('transform', 'rotate(180deg)');

            // Auto-centang parameter baru
            var $cb = $row.find('.method-checkbox-tab');
            $cb.prop('checked', true).trigger('change');

            // Scroll ringan ke parameter baru
            $('html, body').animate({ scrollTop: $row.offset().top - 120 }, 300);
        }
            var formatRupiah = window.formatRupiah;

            // Handle paket button click
            $('.btn-pick-paket').on('click', function() {
                var $this = $(this);

                // Check if already active (toggle)
                if ($this.hasClass('active')) {
                    // Deactivate
                    $this.removeClass('active');
                    $('#packet_id').val('');

                    // Uncheck all methods
                    $('.method-checkbox').prop('checked', false).trigger('change');
                } else {
                    // Remove active from all packet buttons
                    $('.btn-pick-paket').removeClass('active');

                    // Add active to this button
                    $this.addClass('active');

                    // Set packet id
                    var packetId = $this.data('id');
                    $('#packet_id').val(packetId);

                    // Load packet methods
                    loadPacketMethods(packetId);
                }
            });

            // Handle packet selection (for backward compatibility)
            $('#packet_id').on('change', function() {
                var packetId = $(this).val();

                if (packetId) {
                    // Load packet methods and auto-check them
                    loadPacketMethods(packetId);
                } else {
                    // If no packet selected, uncheck all methods
                    $('.method-checkbox').prop('checked', false).trigger('change');
                }
            });

            // Load packet methods from server
            function loadPacketMethods(packetId) {
                var url = "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}";
                url = url.replace('#', packetId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Uncheck all first and enable all
                        $('.method-checkbox').prop('checked', false).prop('disabled', false);
                        $('.method-row').removeClass('disabled-param');
                        $('.packet-badge').remove();

                        // Auto-check and disable methods from packet
                        if (response.methods && response.methods.length > 0) {
                            response.methods.forEach(function(methodData) {
                                // Find checkbox with matching method_id
                                var methodId = methodData.id_method;
                                var checkbox = $('.method-checkbox').filter(function() {
                                    var parts = $(this).val().split('_');
                                    return parts[0] === methodId;
                                });

                                if (checkbox.length > 0) {
                                    checkbox.prop('checked', true).prop('disabled', true);
                                    var $row = checkbox.closest('.method-row');
                                    $row.addClass('disabled-param');

                                    // Add badge to show this is from packet
                                    if (!$row.find('.packet-badge').length) {
                                        $row.find('label').append(
                                            ' <span class="badge badge-success packet-badge" style="font-size: 10px; margin-left: 8px;"><i class="fa fa-box"></i> Dari Paket</span>'
                                        );
                                    }
                                }
                            });

                            // Update all lab counters
                            $('.method-checkbox').each(function() {
                                var labId = $(this).data('lab');
                                var count = $('#lab-' + labId + ' .method-checkbox:checked')
                                    .length;
                                $('#count-' + labId).text(count + ' dipilih');
                            });

                            // Update cart
                            updateCart();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading packet methods:', xhr);
                        swal({
                            title: "Error!",
                            text: "Gagal memuat parameter dari paket",
                            icon: "error"
                        });
                    }
                });
            }

            // Update parameter count per lab
            $('.method-checkbox').on('change', function() {
                var labId = $(this).data('lab');
                var count = $('#lab-' + labId + ' .method-checkbox:checked').length;
                $('#count-' + labId).text(count + ' dipilih');

                updateCart();
            });

            // Clear all parameters in cart
            $('#cart-clear-all').on('click', function() {
                $('.method-checkbox').prop('checked', false).prop('disabled', false).trigger('change');
                $('.method-row').removeClass('disabled-param');
                $('.packet-badge').remove();
                $('.btn-pick-paket').removeClass('active');
                $('#packet_id').val('');
                updateCart();
            });

            // Calculate total cost and update cart
            function updateCart() {
                var total = 0;
                var itemsHtml = '';
                var itemCount = 0;
                var isPacket = $('#packet_id').val() !== '' && $('#packet_id').val() !== null;

                // If packet is selected
                if (isPacket) {
                    var $activePacketButton = $('.btn-pick-paket.active');

                    if ($activePacketButton.length > 0) {
                        var packetPrice = $activePacketButton.data('price');
                        var packetName = $activePacketButton.data('name');

                        if (packetPrice) {
                            total = parseFloat(packetPrice) || 0;
                            itemCount = 1; // Count as 1 item (the packet)

                            // Show packet info as cart item
                            itemsHtml = `
                                <div class="cart-item" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50;">
                                    <button type="button" class="cart-item-remove" onclick="removePacket()">×</button>
                                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                        <i class="fa fa-box" style="font-size: 24px; color: #4caf50; margin-right: 10px;"></i>
                                        <div>
                                            <div class="cart-item-name" style="font-size: 1rem; color: #2e7d32;">
                                                <strong>${packetName}</strong>
                                            </div>
                                            <div class="cart-item-category">
                                                <i class="fa fa-check-circle text-success"></i> Paket Lengkap
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cart-item-price" style="font-size: 1.1rem; color: #2e7d32;">
                                        <strong>Rp ${formatRupiah(packetPrice)}</strong>
                                    </div>
                                </div>
                            `;
                        }
                    }

                    // Add additional methods that are NOT disabled (not in packet)
                    var additionalCount = 0;
                    var additionalHtml = '';

                    $('.method-checkbox:checked:not(:disabled)').each(function() {
                        var parts = $(this).val().split('_');
                        if (parts.length >= 3) {
                            var price = parseFloat(parts[2]) || 0;
                            total += price;
                            additionalCount++;

                            var name = $(this).data('name');
                            var labname = $(this).data('labname');

                            additionalHtml += `
                                <div class="cart-item">
                                    <button type="button" class="cart-item-remove" onclick="removeCartItem('${$(this).val()}')">×</button>
                                    <div class="cart-item-name">${name}</div>
                                    <div class="cart-item-category">${labname}</div>
                                    <div class="cart-item-price">Rp ${formatRupiah(price)}</div>
                                </div>
                            `;
                        }
                    });

                    // If there are additional parameters, show separator and add them
                    if (additionalCount > 0) {
                        itemsHtml += `
                            <div style="border-top: 2px dashed #e2e8f0; margin: 15px 0; padding-top: 15px;">
                                <small class="text-muted" style="display: block; margin-bottom: 10px;">
                                    <i class="fa fa-plus-circle"></i> <strong>Parameter Tambahan (${additionalCount})</strong>
                                </small>
                            </div>
                        ` + additionalHtml;
                        itemCount += additionalCount;
                    }

                } else {
                    // Show individual methods if no packet selected
                    $('.method-checkbox:checked').each(function() {
                        var parts = $(this).val().split('_');
                        if (parts.length >= 3) {
                            var price = parseFloat(parts[2]) || 0;
                            total += price;
                            itemCount++;

                            var name = $(this).data('name');
                            var labname = $(this).data('labname');

                            itemsHtml += `
                                <div class="cart-item">
                                    <button type="button" class="cart-item-remove" onclick="removeCartItem('${$(this).val()}')">×</button>
                                    <div class="cart-item-name">${name}</div>
                                    <div class="cart-item-category">${labname}</div>
                                    <div class="cart-item-price">Rp ${formatRupiah(price)}</div>
                                </div>
                            `;
                        }
                    });
                }

                // Update cart display
                if (itemCount > 0) {
                    $('#cart-items-list').html(itemsHtml);
                } else {
                    $('#cart-items-list').html(`
                        <div class="text-center text-muted py-5" id="cart-empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada parameter dipilih</p>
                            <small>Centang parameter untuk menambahkan</small>
                        </div>
                    `);
                }

                // Update cart footer
                if (isPacket) {
                    var additionalEnabled = $('.method-checkbox:checked:not(:disabled)').length;
                    if (additionalEnabled > 0) {
                        $('#cart-total-label').text('Total Item:');
                        $('#cart-total-items').text('1 Paket + ' + additionalEnabled + ' Tambahan');
                    } else {
                        $('#cart-total-label').text('Paket Terpilih:');
                        $('#cart-total-items').text('1 Paket');
                    }
                } else {
                    $('#cart-total-label').text('Total Parameter:');
                    $('#cart-total-items').text(itemCount);
                }

                $('#cart-total-price').text('Rp ' + formatRupiah(total));
                $('#cost_samples').val(total.toFixed(2));
            }

            // Remove item from cart
            window.removeCartItem = function(value) {
                $('.method-checkbox[value="' + value + '"]').prop('checked', false).trigger('change');
            };

            // Remove packet from cart
            window.removePacket = function() {
                $('.btn-pick-paket').removeClass('active');
                $('#packet_id').val('');

                // Uncheck and enable only the disabled checkboxes (from packet)
                $('.method-checkbox:disabled').prop('checked', false).prop('disabled', false);
                $('.method-row').removeClass('disabled-param');
                $('.packet-badge').remove();

                // Update cart to show only remaining manual selections
                updateCart();
            };

            // Step navigation functions
            window.nextStep = function(step) {
                // Validate current step
                if (step === 1) {
                    // Validate jenis sampel (multiple selection)
                    if (selectedSampleTypes.length === 0) {
                        swal({
                            title: "Error!",
                            text: "Pilih minimal 1 jenis sampel terlebih dahulu!",
                            icon: "error"
                        });
                        return;
                    }

                    // Validate each sample type has at least one parameter
                    var hasError = false;
                    var errorMessages = [];

                    selectedSampleTypes.forEach(function(type) {
                        var config = sampleTypeConfigs[type.id];

                        var hasPackets = (config.packets && config.packets.length > 0);
                        var hasAdditionalMethods = (config.additional_methods && config
                            .additional_methods.length > 0);

                        var totalPacketMethods = 0;
                        if (config.packets) {
                            config.packets.forEach(function(p) {
                                totalPacketMethods += (p.methods || []).length;
                            });
                        }

                        if (!config || (!hasPackets && !hasAdditionalMethods)) {
                            hasError = true;
                            errorMessages.push(`${type.code}: Belum ada parameter dipilih`);
                        } else {
                        }
                    });

                    if (hasError) {
                        console.error('Validation failed:', errorMessages);
                        swal({
                            title: "Error!",
                            text: "Setiap jenis sampel harus memiliki minimal 1 parameter:\n\n" +
                                errorMessages.join('\n'),
                            icon: "error"
                        });
                        return;
                    }

                    // Generate review content
                    generateReview();
                }

                // Move to next step
                if (currentStep < totalSteps) {
                    $('.form-step[data-step="' + currentStep + '"]').removeClass('active');
                    $('.step-item[data-step="' + currentStep + '"]').addClass('completed').removeClass(
                        'active');

                    currentStep++;

                    $('.form-step[data-step="' + currentStep + '"]').addClass('active');
                    $('.step-item[data-step="' + currentStep + '"]').addClass('active');

                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                }
            };

            window.prevStep = function(step) {
                if (currentStep > 1) {
                    $('.form-step[data-step="' + currentStep + '"]').removeClass('active');
                    $('.step-item[data-step="' + currentStep + '"]').removeClass('active');

                    currentStep--;

                    $('.form-step[data-step="' + currentStep + '"]').addClass('active');
                    $('.step-item[data-step="' + currentStep + '"]').removeClass('completed');

                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                }
            };

            // Generate review content - Cart Style
            function generateReview() {
                var reviewHtml = '';
                var grandTotal = 0;

                // Calculate total samples that will be created
                var totalSamplesToCreate = 0;
                selectedSampleTypes.forEach(function(type) {
                    var config = sampleTypeConfigs[type.id] || {};
                    if (config.packets && config.packets.length > 0) {
                        totalSamplesToCreate += config.packets.length;
                    }
                    if (config.additional_methods && config.additional_methods.length > 0) {
                        totalSamplesToCreate += 1; // Additional methods create 1 sample
                    }
                });

                // Update info message about multiple samples
                if (totalSamplesToCreate > 1) {
                    reviewHtml += `
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Catatan:</strong> Sistem akan membuat <strong>${totalSamplesToCreate} draft sample</strong> 
                            dengan konfigurasi berbeda. Setiap paket akan menjadi sample terpisah dengan group_id yang sama.
                        </div>
                    `;
                }

                // Loop through each selected sample type
                selectedSampleTypes.forEach(function(type, index) {
                    var config = sampleTypeConfigs[type.id];

                    if (!config) return;

                    // Skip if config is empty (no packets or additional methods)
                    var hasPackets = (config.packets && config.packets.length > 0);
                    var hasAdditionalMethods = (config.additional_methods && config.additional_methods
                        .length > 0);

                    if (!hasPackets && !hasAdditionalMethods) {
                        return; // Skip this sample type
                    }

                    // Calculate cost for this sample type (from all packets + additional methods)
                    var sampleTypeCost = 0;
                    if (hasPackets) {
                        config.packets.forEach(function(packet) {
                            sampleTypeCost += parseFloat(packet.packet_price) || 0;
                        });
                    }
                    if (hasAdditionalMethods) {
                        config.additional_methods.forEach(function(method) {
                            sampleTypeCost += parseFloat(method.price) || 0;
                        });
                    }
                    grandTotal += sampleTypeCost;

                    // Calculate total parameters for this sample type
                    var totalPacketMethods = 0;
                    if (config.packets && config.packets.length > 0) {
                        config.packets.forEach(function(p) {
                            totalPacketMethods += (p.methods || []).length;
                        });
                    }
                    var totalParams = totalPacketMethods + (config.additional_methods ? config
                        .additional_methods.length : 0);

                    reviewHtml += `
                        <div class="cart-panel mb-4" style="border: 2px solid ${index === 0 ? '#667eea' : '#e2e8f0'};">
                            <div class="cart-panel-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0;">
                                <div class="cart-panel-title" style="color: white;">
                                    <i class="fa fa-vial"></i>
                                    ${type.code} - ${type.name}
                                </div>
                                <span class="badge" style="background: white; color: #667eea; font-weight: 700;">
                                    ${totalParams} Parameter
                                </span>
                            </div>
                            <div style="padding: 20px;">
                    `;

                    // Multiple Packets Section
                    if (hasPackets) {
                        reviewHtml += `
                            <div class="cart-section-title">
                                <i class="fa fa-cube"></i> Paket (${config.packets.length} paket)
                            </div>
                        `;

                        config.packets.forEach(function(packet, packetIndex) {
                            reviewHtml += `
                                <div class="cart-item cart-item-packet" style="margin-bottom: 10px;">
                                    <div class="cart-item-header">
                                        <span class="cart-item-name">
                                            <i class="fa fa-box"></i> ${packet.packet_name || 'Paket ' + (packetIndex + 1)}
                                        </span>
                                        <span class="cart-item-price">Rp ${formatRupiah(packet.packet_price || 0)}</span>
                                    </div>
                                    <div class="cart-item-lab" style="color: #666; font-size: 12px;">
                                        <i class="fa fa-list"></i> ${(packet.methods || []).length} parameter
                                    </div>
                                </div>
                            `;
                        });
                    }

                    // Additional Methods Section
                    if (hasAdditionalMethods) {
                        reviewHtml += `
                            <div class="cart-section-title" style="margin-top: ${hasPackets ? '15px' : '0'};">
                                <i class="fa fa-flask"></i> Parameter ${hasPackets ? 'Tambahan' : 'Dipilih'} (${config.additional_methods.length})
                            </div>
                        `;

                        config.additional_methods.forEach(function(method) {
                            reviewHtml += `
                                <div class="cart-item">
                                    <div class="cart-item-header">
                                        <span class="cart-item-name">${method.name}</span>
                                        <span class="cart-item-price">Rp ${formatRupiah(method.price)}</span>
                                    </div>
                                    <div class="cart-item-lab">
                                        <i class="fa fa-building"></i> ${method.lab_name}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    if (window.kesmasDraftTableOk && (window.kesmasDraftNomorSampelManual || window
                            .kesmasDraftNomorLabManual)) {
                        var yDraft = window.kesmasDraftYear || String(new Date().getFullYear());
                        var cid = String(type.id).replace(/-/g, '');
                        var labsT = collectDraftLabsUsedForType(type.id);
                        var escT = function(s) {
                            return $('<span>').text(s == null ? '' : String(s)).html();
                        };
                        var tcEsc = escT(type.code);
                        var ks = '';
                        ks += '<div class="draft-kesmas-per-type card border-info mb-3 mt-2" data-sample-type-id="' +
                            escT(type.id) + '">';
                        ks += '<div class="card-header bg-light py-2"><strong><i class="fa fa-barcode"></i> Nomor sampel (manual) — ' +
                            tcEsc + '</strong></div>';
                        ks += '<div class="card-body p-3">';
                        ks +=
                            '<p class="small text-muted mb-3"><i class="fa fa-info-circle"></i> Isi <strong>angka urut</strong> saja. Format: Kimia → <code>' + tcEsc + '.01/[urut]/tahun</code>; Mikro → <code>' + tcEsc + '.02/[urut]/tahun</code>. Hanya lab yang dipakai parameter yang ditampilkan.</p>';
                        
                        if (window.kesmasDraftNomorSampelManual) {
                            if (labsT.useKimia) {
                                ks += '<div class="mb-3"><label class="font-weight-bold">No. sampel (spesimen) — Kimia (' + tcEsc + ')</label>';
                                ks += '<div class="input-group mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-barcode"></i></span></div>';
                                ks += '<input type="text" class="form-control" readonly id="draft_nomor_spesimen_full_kimia_' + cid + '" placeholder="Pratinjau format penuh"></div>';
                                ks += '<div class="card border-0 shadow-sm mb-0" style="background:linear-gradient(135deg,#e3f2fd 0%,#bbdefb 100%);"><div class="card-body d-flex align-items-center flex-wrap py-2 px-3" style="gap:8px;font-weight:600;">';
                                ks += '<span class="text-primary">' + tcEsc + '.01/</span>';
                                ks += '<input type="text" class="form-control" id="draft_nomor_spesimen_urut_' + cid +
                                    '" data-sample-type-id="' + type.id +
                                    '" data-draft-kesmas-sanitize="1" placeholder="no_urut" inputmode="numeric" autocomplete="off" style="max-width:120px;text-align:center;font-weight:600;height:34px;">';
                                ks += '<span class="text-primary">/' + yDraft + '</span></div></div>';
                                ks += '<small class="text-muted d-block mt-1">Pratinjau: <code>' + tcEsc + '.01/<span class="draft-preview-urut-sp-kimia-' + cid + '">…</span>/' + yDraft + '</code></small></div>';
                            }
                            
                            if (labsT.useMikro) {
                                ks += '<div class="mb-3"><label class="font-weight-bold">No. sampel (spesimen) — Mikrobiologi (' + tcEsc + ')</label>';
                                ks += '<div class="input-group mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-barcode"></i></span></div>';
                                ks += '<input type="text" class="form-control" readonly id="draft_nomor_spesimen_full_mikro_' + cid + '" placeholder="Pratinjau format penuh"></div>';
                                ks += '<div class="card border-0 shadow-sm mb-0" style="background:linear-gradient(135deg,#e3f2fd 0%,#bbdefb 100%);"><div class="card-body d-flex align-items-center flex-wrap py-2 px-3" style="gap:8px;font-weight:600;">';
                                ks += '<span class="text-primary">' + tcEsc + '.02/</span>';
                                ks += '<input type="text" class="form-control" id="draft_nomor_spesimen_mikro_urut_' + cid +
                                    '" data-sample-type-id="' + type.id +
                                    '" data-draft-kesmas-sanitize="1" placeholder="no_urut" inputmode="numeric" autocomplete="off" style="max-width:120px;text-align:center;font-weight:600;height:34px;">';
                                ks += '<span class="text-primary">/' + yDraft + '</span></div></div>';
                                ks += '<small class="text-muted d-block mt-1">Pratinjau: <code>' + tcEsc + '.02/<span class="draft-preview-urut-sp-mikro-' + cid + '">…</span>/' + yDraft + '</code></small></div>';
                            }
                        }
                        
                        // Nomer lab tidak diinput di sini; ditetapkan otomatis saat pengesahan hasil
                        ks += '</div></div>';
                        reviewHtml += ks;
                    }

                    // Titik Lokasi for this sample type
                    var titikPengambilan = (config && config.titik_pengambilan) ? config.titik_pengambilan :
                        '';
                    reviewHtml += `
                                <hr class="cart-divider">
                                <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 10px;">
                                    <strong><i class="fa fa-map-marker-alt"></i> Titik Lokasi:</strong><br>
                                    <span style="color: #555; font-size: 14px;">
                                        ${titikPengambilan || '<em class="text-muted">Belum diisi (bisa dilengkapi saat sampling)</em>'}
                                    </span>
                                </div>
                    `;

                    // Subtotal for this sample type
                    reviewHtml += `
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span style="font-size: 16px; font-weight: 600;">
                                            <i class="fa fa-calculator"></i> Subtotal
                                        </span>
                                        <span style="font-size: 20px; font-weight: 700;">Rp ${formatRupiah(sampleTypeCost || 0)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Calculate biaya sampling FIRST before using in templates
                var biayaSamplingPerSample = parseFloat($('#cost_sampling').val()) || 20000;
                var jumlahJenisSampel = selectedSampleTypes.length;
                var totalBiayaSampling = biayaSamplingPerSample * jumlahJenisSampel;
                var totalPengujian = grandTotal;
                var totalKeseluruhan = totalPengujian + totalBiayaSampling;

                // Informasi Sampling
                reviewHtml += `
                    <div class="card mb-3" style="border: 2px solid #e2e8f0; border-radius: 10px;">
                        <div class="card-header" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; font-weight: 600;">
                            <i class="fa fa-info-circle"></i> Informasi Sampling
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <strong><i class="fa fa-money-bill-wave"></i> Biaya Sampling:</strong><br>
                                    <span style="font-size: 18px; color: #48bb78; font-weight: 600;">
                                        Rp ${formatRupiah(biayaSamplingPerSample)} × ${jumlahJenisSampel} sampel
                                    </span>
                                    <br>
                                    <small class="text-muted">Total: Rp ${formatRupiah(totalBiayaSampling)}</small>
                                    <br><br>
                                    <small class="text-muted">
                                        <i class="fa fa-info-circle"></i> Titik lokasi ditampilkan per jenis sampel di atas
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Catatan
                var catatan = $('#note_samples').val() || '-';
                if (catatan !== '-') {
                    reviewHtml += `
                        <div class="alert alert-secondary">
                            <strong><i class="fa fa-sticky-note"></i> Catatan:</strong><br>
                            ${catatan}
                        </div>
                    `;
                }

                // Grand Total with Breakdown
                reviewHtml += `
                    <div class="cart-total" style="margin-top: 20px;">
                        <!-- Breakdown -->
                        <div style="background: #ffffff; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div class="d-flex justify-content-between mb-3" style="font-size: 16px; color: #2d3748;">
                                <span style="font-weight: 500;">
                                    <i class="fa fa-flask" style="color: #667eea; margin-right: 8px;"></i> 
                                    Total Biaya Pengujian
                                </span>
                                <span style="font-weight: 700; color: #1a202c;">Rp ${formatRupiah(totalPengujian)}</span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 16px; color: #2d3748;">
                                <span style="font-weight: 500;">
                                    <i class="fa fa-map-marker-alt" style="color: #48bb78; margin-right: 8px;"></i> 
                                    Biaya Sampling
                                    <small style="display: block; color: #718096; font-size: 13px; margin-top: 2px; margin-left: 24px;">
                                        ${jumlahJenisSampel} jenis sampel × Rp ${formatRupiah(biayaSamplingPerSample)}
                                    </small>
                                </span>
                                <span style="font-weight: 700; color: #1a202c;">Rp ${formatRupiah(totalBiayaSampling)}</span>
                            </div>
                        </div>
                        
                        <!-- Grand Total -->
                        <div class="cart-total-row" style="font-size: 1.3rem; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <span class="cart-total-label" style="color: white; font-weight: 600;">
                                <i class="fa fa-money-bill-wave"></i> TOTAL KESELURUHAN
                            </span>
                            <span class="cart-total-price" style="color: white; font-weight: 700; font-size: 1.5rem;">
                                Rp ${formatRupiah(totalKeseluruhan)}
                            </span>
                        </div>
                    </div>
                `;

                $('#review-content').html(reviewHtml);
                if (typeof updateKesmasDraftManualPanel === 'function') {
                    updateKesmasDraftManualPanel();
                }
            }

            // Form submission
            $('#draftForm').on('submit', function(e) {
                e.preventDefault();

                // Validate at least one sample type selected
                if (selectedSampleTypes.length === 0) {
                    swal({
                        title: "Error!",
                        text: "Pilih minimal 1 jenis sampel!",
                        icon: "error"
                    });
                    return;
                }

                // Validate each sample type has at least one method
                var hasError = false;
                var errorMessages = [];

                selectedSampleTypes.forEach(function(type) {
                    var config = sampleTypeConfigs[type.id];
                    var hasPackets = (config.packets && config.packets.length > 0);
                    var hasAdditionalMethods = (config.additional_methods && config
                        .additional_methods.length > 0);

                    if (!config || (!hasPackets && !hasAdditionalMethods)) {
                        hasError = true;
                        errorMessages.push(`${type.code}: Belum ada parameter dipilih`);
                    }
                });

                if (hasError) {
                    swal({
                        title: "Error!",
                        text: "Setiap jenis sampel harus memilih minimal 1 parameter:\n\n" +
                            errorMessages.join('\n'),
                        icon: "error"
                    });
                    return;
                }

                if (window.kesmasDraftTableOk && window.kesmasDraftNomorSampelManual) {
                    var nomorErr = [];
                    selectedSampleTypes.forEach(function(type) {
                        var cfg = sampleTypeConfigs[type.id];
                        if (!cfg) return;
                        var hp = cfg.packets && cfg.packets.length > 0;
                        var ha = cfg.additional_methods && cfg.additional_methods.length > 0;
                        if (!hp && !ha) return;
                        var kz = getDraftKesmasUrutsForType(type.id);
                        var labs = collectDraftLabsUsedForType(type.id);
                        if (labs.useKimia && !kz.sp_k) {
                            nomorErr.push(type.code + ': isi angka urut nomor spesimen Kimia');
                        }
                        if (labs.useMikro && !kz.sp_m) {
                            nomorErr.push(type.code + ': isi angka urut nomor spesimen Mikrobiologi');
                        }
                    });
                    if (nomorErr.length) {
                        swal({
                            title: "Nomor spesimen wajib",
                            text: nomorErr.join('\n'),
                            icon: "warning"
                        });
                        return;
                    }
                }

                if (window.kesmasDraftTableOk && window.kesmasDraftNomorLabManual) {
                    var labErr = [];
                    selectedSampleTypes.forEach(function(type) {
                        var cfg = sampleTypeConfigs[type.id];
                        if (!cfg) return;
                        var hp = cfg.packets && cfg.packets.length > 0;
                        var ha = cfg.additional_methods && cfg.additional_methods.length > 0;
                        if (!hp && !ha) return;
                        var labsT = collectDraftLabsUsedForType(type.id);
                        var kz = getDraftKesmasUrutsForType(type.id);
                        if (labsT.useKimia && !kz.k) {
                            labErr.push(type.code + ': isi nomor lab Kimia');
                        }
                        if (labsT.useMikro && !kz.m) {
                            labErr.push(type.code + ': isi nomor lab Mikrobiologi');
                        }
                    });
                    if (labErr.length) {
                        swal({
                            title: "Nomor laboratorium wajib",
                            text: labErr.join('\n'),
                            icon: "warning"
                        });
                        return;
                    }
                }

                // Prepare form data with configurations per sample type
                var formData = new FormData();

                // Add CSRF token from form
                var csrfToken = $('input[name="_token"]').val();
                if (!csrfToken) {
                    csrfToken = $('meta[name="csrf-token"]').attr('content');
                }

                if (!csrfToken) {
                    swal({
                        title: "Error!",
                        text: "CSRF token tidak ditemukan. Silakan refresh halaman.",
                        icon: "error"
                    });
                    return;
                }

                formData.append('_token', csrfToken);

                // Add note
                formData.append('note_samples', $('#note_samples').val() || '');

                // Add biaya sampling - ensure it's a valid number
                var costSampling = parseFloat($('#cost_sampling').val());
                if (isNaN(costSampling) || costSampling < 0) {
                    costSampling = 20000;
                }
                formData.append('cost_sampling', costSampling);

                // Add configurations for each sample type
                // IMPORTANT: Each packet creates a separate sample with same group_id
                var sampleIndex = 0;
                selectedSampleTypes.forEach(function(type) {
                    var config = sampleTypeConfigs[type.id];
                    // Get titik_pengambilan for this sample type
                    var titikPengambilan = (config && config.titik_pengambilan) ? config
                        .titik_pengambilan : '';
                    var kzNomor = getDraftKesmasUrutsForType(type.id);

                    function appendKesmasFieldsForRow(idx) {
                        if (window.kesmasDraftTableOk && window.kesmasDraftNomorSampelManual) {
                            formData.append(`samples[${idx}][nomor_spesimen_manual]`, kzNomor.sp_k);
                            formData.append(`samples[${idx}][nomor_spesimen_mikro_manual]`, kzNomor.sp_m);
                        }
                        // Nomer lab tidak dikirim; ditetapkan otomatis saat pengesahan hasil
                    }

                    // Process each packet separately (each packet = 1 sample)
                    if (config.packets && config.packets.length > 0) {
                        config.packets.forEach(function(packet) {
                            // Validate cost_samples for this packet
                            var costSamples = parseFloat(packet.packet_price) || 0;
                            if (isNaN(costSamples) || costSamples < 0) {
                                costSamples = 0;
                            }

                            formData.append(`samples[${sampleIndex}][sample_type_id]`, type
                                .id);
                            formData.append(`samples[${sampleIndex}][packet_id]`, packet
                                .packet_id || '');
                            formData.append(`samples[${sampleIndex}][packet_name]`, packet
                                .packet_name || '');
                            formData.append(`samples[${sampleIndex}][packet_price]`, packet
                                .packet_price || 0);
                            formData.append(`samples[${sampleIndex}][cost_samples]`,
                                costSamples);
                            formData.append(`samples[${sampleIndex}][titik_pengambilan]`,
                                titikPengambilan);
                            appendKesmasFieldsForRow(sampleIndex);

                            // Add methods from this packet
                            if (packet.methods && packet.methods.length > 0) {
                                packet.methods.forEach(function(method, methodIndex) {
                                    formData.append(
                                        `samples[${sampleIndex}][methods][${methodIndex}]`,
                                        method);
                                });
                            }

                            sampleIndex++;
                        });
                    }

                    // If there are additional methods (not from packet), create a sample for them too
                    if (config.additional_methods && config.additional_methods.length > 0) {
                        // Calculate cost for additional methods
                        var additionalCost = 0;
                        config.additional_methods.forEach(function(method) {
                            additionalCost += parseFloat(method.price) || 0;
                        });

                        formData.append(`samples[${sampleIndex}][sample_type_id]`, type.id);
                        formData.append(`samples[${sampleIndex}][packet_id]`, '');
                        formData.append(`samples[${sampleIndex}][packet_name]`, '');
                        formData.append(`samples[${sampleIndex}][packet_price]`, 0);
                        formData.append(`samples[${sampleIndex}][cost_samples]`, additionalCost);
                        formData.append(`samples[${sampleIndex}][titik_pengambilan]`,
                            titikPengambilan);
                        appendKesmasFieldsForRow(sampleIndex);

                        // Add additional methods
                        config.additional_methods.forEach(function(method, methodIndex) {
                            formData.append(
                                `samples[${sampleIndex}][methods][${methodIndex}]`,
                                method.method_string || method.method);
                        });

                        sampleIndex++;
                    }
                });

                var url = $(this).attr('action');

                // Debug: Log FormData content
                for (var pair of formData.entries()) {
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == true) {
                            var message = response.pesan;
                            if (response.total_created) {
                                message +=
                                    `\n\nTotal draft sample yang dibuat: ${response.total_created}`;
                            }

                            swal({
                                title: "Berhasil!",
                                text: message,
                                icon: "success",
                                buttons: false,
                                timer: 2000
                            }).then(function() {
                                window.location.href =
                                    "{{ route('elits-sample-draft.index', $permohonan_uji->id_permohonan_uji) }}";
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: response.pesan,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr);
                        console.error('Status:', xhr.status);
                        console.error('Response:', xhr.responseText);

                        var message = "Gagal menyimpan draft sample!";

                        if (xhr.status === 419) {
                            message =
                                "CSRF token expired. Silakan refresh halaman dan coba lagi.";
                        } else if (xhr.responseJSON && xhr.responseJSON.pesan) {
                            message = xhr.responseJSON.pesan;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseText) {
                            message += "\n\nDetail: " + xhr.responseText.substring(0, 200);
                        }

                        swal({
                            title: "Error!",
                            text: message,
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>
@endsection
