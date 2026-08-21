@extends('masterweb::template.admin.layout')
@section('title')
    Edit Data Sampel
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
            color: white;
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
            background: rgba(255, 255, 255, 0.2);
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
            transition: all 0.3s;
        }

        .form-section-card-sample:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .section-title-sample {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #11998e;
            display: flex;
            align-items: center;
        }

        .section-title-sample i {
            margin-right: 12px;
            color: #11998e;
            font-size: 24px;
        }

        .info-alert-custom {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            border-left: 5px solid #00acc1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-alert-custom strong {
            color: #006064;
        }

        .guide-alert-custom {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 5px solid #ff9800;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .guide-alert-custom strong {
            color: #e65100;
            font-size: 16px;
        }

        .guide-alert-custom ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
            margin-top: 10px;
        }

        .guide-alert-custom li {
            margin-bottom: 8px;
            color: #424242;
        }

        .action-buttons-sample {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .gap-3 {
            gap: 15px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .btn-primary-sample {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
            transition: all 0.3s;
            color: white;
        }

        .btn-primary-sample:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-secondary-sample {
            background: #e2e8f0;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            color: #4a5568;
            transition: all 0.3s;
        }

        .btn-secondary-sample:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
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
            border-color: #11998e;
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
        }

        .breadcrumb {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb-item a {
            color: #11998e;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #4a5568;
        }

        /* Parameter Search & Pagination Styles */
        .card-header h5 {
            margin-bottom: 0 !important;
        }

        #search-parameter {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        #search-parameter:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
        }

        #items-per-page {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        /* Radio Button Styling */
        .form-check {
            padding: 12px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .form-check:hover {
            background: #e9ecef;
            border-color: #11998e;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0.15em;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #11998e;
            border-color: #11998e;
        }

        .form-check-label {
            margin-left: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
        }

        .list-group-item {
            border: none;
            padding: 0;
        }

        /* Jenis Sampel Button Styling */
        .btn-pick-jenis {
            border: 2px solid #11998e !important;
            color: #11998e !important;
            background: white !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            transition: all 0.3s !important;
            font-size: 14px !important;
        }

        .btn-pick-jenis:hover {
            background: #11998e !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3) !important;
        }

        .btn-pick-jenis.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
            color: white !important;
            border-color: #11998e !important;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4) !important;
        }

        .parameter-group {
            transition: all 0.2s ease;
        }

        .parameter-group table {
            width: 100%;
        }

        .method-row {
            transition: background-color 0.15s ease;
        }

        .method-row:hover {
            background-color: #f8f9fa;
        }

        #pagination-controls {
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
        }

        .pagination .page-link {
            color: #007bff;
            border-color: #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            cursor: not-allowed;
        }

        #showing-info {
            font-size: 0.875rem;
        }

        /* Make search input and dropdown responsive */
        @media (max-width: 768px) {
            .card-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            #search-parameter,
            #items-per-page {
                width: 100% !important;
                margin-bottom: 10px;
            }
        }

        /* Collapse & Auto-Sort Styles */
        .parameter-group-header {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 15px !important;
            margin-bottom: 15px !important;
            font-weight: 600;
            color: #2d3748;
        }

        .parameter-group-header:hover {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important;
            color: white !important;
            border-color: #0b3a5c !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(11, 58, 92, 0.3);
        }

        .parameter-group-header:hover .collapse-icon {
            color: white !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
            margin-right: 8px;
            color: #0b3a5c;
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
            accent-color: #11998e;
        }

        .method-row label {
            cursor: pointer;
            margin-bottom: 0;
            font-size: 14px;
            color: #4a5568;
        }

        /* Bottom Action Buttons Enhancement */
        .bottom-action-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-top: 3px solid #11998e;
            padding: 20px 30px;
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .bottom-action-container.hidden {
            transform: translateY(100%);
        }

        .btn-simpan:disabled {
            background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .btn-simpan:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 768px) {
            .bottom-action-container {
                flex-direction: column;
                gap: 15px;
            }

            .bottom-action-container .d-flex {
                width: 100%;
                justify-content: center;
            }

            .bottom-action-container button {
                width: 100%;
            }
        }

        .param-count {
            font-size: 13px;
            transition: all 0.2s ease;
        }

        /* Multi-Step Form Wizard */
        .form-wizard-container {
            position: relative;
            overflow: hidden;
        }

        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 15px 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 30px;
            right: -50%;
            width: 100%;
            height: 3px;
            background: #e2e8f0;
            z-index: -1;
        }

        .step-item.completed::after {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #a0aec0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .step-item.active .step-number {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(11, 58, 92, 0.4);
            transform: scale(1.1);
        }

        .step-item.completed .step-number {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .step-item.completed .step-number::before {
            content: '✓';
        }

        .step-title {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 8px;
        }

        .step-item.active .step-title {
            color: #0b3a5c;
        }

        .step-item.completed .step-title {
            color: #11998e;
        }

        /* Navigation Buttons */
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .btn-step {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-prev {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-prev:hover {
            background: #cbd5e0;
            transform: translateX(-3px);
        }

        .btn-next {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(11, 58, 92, 0.3);
        }

        .btn-next:hover {
            transform: translateX(3px);
            box-shadow: 0 6px 20px rgba(11, 58, 92, 0.4);
        }

        .btn-step:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-step:disabled:hover {
            transform: none;
        }

        /* Error Message in Steps */
        .step-error {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .step-error.show {
            display: block;
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .step-error i {
            color: #e53e3e;
            margin-right: 10px;
        }

        #selected-parameters-section {
            background: #f0f8ff;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
        }

        #selected-parameters-section .alert {
            background: linear-gradient(135deg, #2196f3 0%, #21cbf3 100%);
            color: white;
            border: none;
            margin-bottom: 15px;
        }

        #selected-parameters-section .parameter-group {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        #selected-separator {
            border: 0;
            border-top: 3px solid #2196f3;
            margin: 30px 0;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Show More Button Styles */
        .show-more-btn {
            text-align: center;
            padding: 12px 15px;
            margin: 10px 0;
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
            user-select: none;
        }

        .show-more-btn:hover {
            background: linear-gradient(to bottom, #e9ecef, #dee2e6);
            color: #0056b3;
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
            transform: translateY(-1px);
        }

        .show-more-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(0, 123, 255, 0.2);
        }

        .show-more-btn i {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .show-more-btn:hover i {
            transform: translateY(2px);
        }

        /* Cart Widget Styles */
        #parameter-cart {
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #parameter-cart .card-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-bottom: 3px solid #5a67d8;
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

        .cart-packet-badge {
            background: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 6px;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 2px 8px;
        }

        #cart-total-price {
            animation: pulse 0.3s ease;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .badge-lg {
            padding: 6px 12px;
            font-size: 1rem;
        }

        @media (max-width: 991px) {
            #parameter-cart {
                position: static !important;
                margin-top: 20px;
            }
        }

        /* Paket Button Styles - Enhanced Selection Visual */
        .btn-pick-paket {
            position: relative;
            padding: 10px 20px;
            font-weight: 500;
            border: 2px solid #28a745 !important;
            background-color: white;
            color: #28a745;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-pick-paket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
            background-color: #f0fff4;
        }

        .btn-pick-paket.active {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .btn-pick-paket.active::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 8px;
            animation: checkmarkPop 0.3s ease;
        }

        @keyframes checkmarkPop {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .btn-pick-paket:active {
            transform: scale(0.95);
        }

        .btn-pick-paket:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
        }

        .packet-buttons-container {
            gap: 10px !important;
        }
    </style>
@endsection

@section('content')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-ui.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/jquery-ui.min.css') }}">



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
                                <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">Data Sampel</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Sampel</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header-card-sample">
        <h2>
            <i class="fa fa-edit"></i>
            Edit Data Sampel Kesmas
        </h2>
        <div class="subtitle">Perbarui detail sampel untuk permohonan uji</div>
    </div>

    <!-- Permohonan Uji Info -->
    @if (isset($permohonan_uji))
        <div class="info-alert-custom">
            <div>
                <strong><i class="fa fa-file-alt"></i> Permohonan Uji</strong>
                @if (optional($permohonan_uji->customer)->name_customer)
                    — {{ optional($permohonan_uji->customer)->name_customer }}
                @endif
                <div style="font-size: 0.9rem; margin-top: 8px; opacity: 0.8;">
                    <i class="fa fa-info-circle"></i> Langkah 2 dari 2: Tambahkan satu atau lebih sampel untuk permohonan
                    uji di atas.
                </div>
            </div>
            <a href="{{ route('elits-permohonan-uji.edit', [$permohonan_uji->id_permohonan_uji]) }}" class="btn btn-sm"
                style="background: white; color: #00acc1; font-weight: 600; border-radius: 8px; padding: 8px 20px;">
                <i class="fa fa-arrow-left"></i> Kembali ke Permohonan Uji
            </a>
        </div>
    @endif

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; border-left: 5px solid #dc3545;">
            <strong><i class="fa fa-exclamation-triangle"></i> Terdapat kesalahan:</strong>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('elits-samples.update', [$sample->id]) }}" method="POST" id="form-create-sample"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

        {{-- CRITICAL: Hidden fallback inputs for code samples to ensure data is submitted --}}
        <input type="hidden" name="code_sample_kimia_backup" id="code_sample_kimia_backup"
            value="{{ $code_samples['kimia'] ?? '' }}">
        <input type="hidden" name="code_sample_mikro_backup" id="code_sample_mikro_backup"
            value="{{ $code_samples['mikrobiologi'] ?? '' }}">

        {{-- CRITICAL FIX: Controller expects 'code_sample' field (singular) --}}
        {{-- This will be synced from kimia or mikro before submit --}}
        <input type="hidden" name="code_sample" id="code_sample_master" value="{{ $sample->codesample_samples ?? '' }}">

        <!-- Info Box -->
        <div
            style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 5px solid #ff9800; border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <i class="fa fa-lightbulb" style="font-size: 24px; color: #ff9800;"></i>
            <span style="color: #424242; font-size: 14px;">Anda dapat menambahkan lebih dari satu sampel untuk permohonan
                ini</span>
        </div>

        <!-- Kode Sampel Section (Below Alert) -->
        <div class="row mb-4" id="sample-codes-container-top">
            <div class="col-lg-6" id="code_sample_kimia_wrapper_top">
                <div class="card"
                    style="border: 2px solid #11998e; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.15);">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 15px 20px;">
                        <h6 class="mb-0" style="color: white; font-weight: 600;">
                            <i class="fa fa-flask"></i> Kode Sampel Kimia
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-lg" name="code_sample_kimia"
                                id="input_code_sample_kimia" data-type="code_sample" data-idlabs="{{ $lab_keys['kimia'] }}"
                                placeholder="Masukkan kode sampel kimia" value="{{ $code_samples['kimia'] ?? '' }}"
                                style="border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; letter-spacing: 1px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" id="code_sample_mikro_wrapper_top">
                <div class="card"
                    style="border: 2px solid #0b3a5c; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(11, 58, 92, 0.15);">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); padding: 15px 20px;">
                        <h6 class="mb-0" style="color: white; font-weight: 600;">
                            <i class="fa fa-microscope"></i> Kode Sampel Mikrobiologi
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-lg" name="code_sample_mikro"
                                id="input_code_sample_mikro" data-type="code_sample"
                                data-idlabs="{{ $lab_keys['mikrobiologi'] }}" placeholder="Masukkan kode sampel mikro"
                                value="{{ $code_samples['mikrobiologi'] ?? '' }}"
                                style="border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; letter-spacing: 1px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" hidden>
                <div class="form-group">
                    <label for="name_pelanggan"> Nama Pelanggan:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="name_pelanggan" id="name_pelanggan"
                            data-type="name_pelanggan" placeholder="Nama Pelanggan"
                            value="{{ old('name_pelanggan') ?? $permohonan_uji->customer->name_customer }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">Detail Sampel</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">Jenis & Parameter</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">Review & Simpan</div>
            </div>
        </div>

        <!-- Form Wizard Container -->
        <div class="form-wizard-container">

            <!-- STEP 1: Detail Sampel -->
            <div class="form-step active" data-step="1">
                <div class="step-error" id="step1-error">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="step1-error-message"></span>
                </div>

                <!-- Detail Sampel Section -->
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-vial"></i>
                        Detail Sampel
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">

                            <div class="col-lg-12">
                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="datesampling_samples">Tanggal Pengambilan</label>


                                            <input id="datesampling_samples" class="form-control"
                                                name="datesampling_samples" placeholder="--/--/--- --:--"
                                                type="datetime" />


                                            <!-- Sertakan Flatpickr -->

                                            <!-- Input Field -->




                                            <script>
                                                var m = moment(new Date()).format('DD/MM/yyyy HH:mm');

                                                $('#datesampling_samples').val(m);
                                            </script>

                                            <script>
                                                flatpickr("#datesampling_samples", {
                                                    enableTime: true,
                                                    allowInput: true,
                                                    locale: "id",
                                                    dateFormat: "d/m/Y H:i", // 24-hour format
                                                    time_24hr: true
                                                });

                                                $('#datesampling_samples').inputmask("datetime", {
                                                    placeholder: "dd/mm/yyyy hh:mm",

                                                });
                                            </script>

                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="datelab_samples">Tanggal Pengiriman</label>
                                            <input id="date_sending" class="form-control" name="date_sending"
                                                placeholder="--/--/--- --:--" type="datetime" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6" hidden>
                                        <div class="form-group">
                                            <label for="datelab_samples">Tanggal Selesai Pengiriman</label>
                                            <input id="date_sending_stop" class="form-control" name="date_sending_stop"
                                                placeholder="--/--/--- --:--" type="datetime" />
                                        </div>
                                    </div>
                                    <script>
                                        var now = moment();
                                        $('#date_sending').val(now.format('DD/MM/YYYY HH:mm'));
                                        $('#date_sending_stop').val(now.add(10, 'minutes').format('DD/MM/YYYY HH:mm'));

                                        // Update date_sending_stop when date_sending changes
                                        $('#date_sending').on('input', function() {
                                            var dateSendingStr = $(this).val();
                                            var dateSending = moment(dateSendingStr, 'DD/MM/YYYY HH:mm');

                                            if (dateSending.isValid()) {
                                                var dateSendingStop = dateSending.clone().add(10, 'minutes');
                                                $('#date_sending_stop').val(dateSendingStop.format('DD/MM/YYYY HH:mm'));
                                            } else {
                                                // Optional: Handle invalid date input
                                                $('#date_sending_stop').val('');
                                            }
                                        });


                                        $('#date_sending').inputmask("datetime", {

                                            placeholder: "dd/mm/yyyy hh:mm",

                                        });

                                        $('#date_sending_stop').inputmask("datetime", {
                                            placeholder: "dd/mm/yyyy hh:mm",

                                        });
                                    </script>

                                    <script>
                                        flatpickr("#date_sending", {
                                            enableTime: true,
                                            allowInput: true,
                                            locale: "id",
                                            dateFormat: "d/m/Y H:i", // 24-hour format
                                            time_24hr: true
                                        });
                                        flatpickr("#date_sending_stop", {
                                            enableTime: true,
                                            allowInput: true,
                                            locale: "id",
                                            dateFormat: "d/m/Y H:i", // 24-hour format
                                            time_24hr: true
                                        });


                                        // var input_1 = document.querySelectorAll('#datesampling_samples')[0];




                                        // var input_2 = document.querySelectorAll('#date_sending')[0];


                                        // var input_3 = document.querySelectorAll('#date_sending_stop')[0];

                                        // var dateInputMask = function dateInputMask(elm) {
                                        //     elm.addEventListener('keypress', function(e) {
                                        //         if (e.keyCode < 47 || e.keyCode > 57) {
                                        //             e.preventDefault();
                                        //         }

                                        //         var len = elm.value.length;

                                        //         // If we're at a particular place, let the user type the slash
                                        //         // i.e., 12/12/1212
                                        //         if (len !== 1 || len !== 3) {
                                        //             if (e.keyCode == 47) {
                                        //                 e.preventDefault();
                                        //             }
                                        //         }

                                        //         console.log(elm.val );


                                        //         // If they don't add the slash, do it for them...
                                        //         if (len === 2) {
                                        //             elm.value += '/';
                                        //         }

                                        //         // If they don't add the slash, do it for them...
                                        //         if (len === 5) {
                                        //             elm.value += '/';
                                        //         }

                                        //         // If they don't add the slash, do it for them...
                                        //         if (len === 10) {
                                        //             elm.value += ' ';
                                        //         }

                                        //         if (len === 13) {
                                        //             elm.value += ':';
                                        //         }

                                        //         if (len > 15) {
                                        //             e.preventDefault();
                                        //         }
                                        //     });
                                        // };

                                        // dateInputMask(input_1);

                                        // dateInputMask(input_2);

                                        // dateInputMask(input_3);
                                    </script>


                                </div>
                            </div>

                            {{-- <div class="col-lg-12"> --}}
                            {{-- <div class="form-group"> --}}
                            {{-- <label for="lokasi_pengambilan">Objek (Lokasi, Makanan, Minuman, Alat Makan, dll) --}}
                            {{-- Pengambilan:</label> --}}
                            {{-- <div class="input-group date"> --}}
                            {{-- <input type="text" class="form-control" name="lokasi_pengambilan" --}} {{-- id="lokasi_pengambilan"
                  placeholder="Lokasi Pengambilan" --}} {{-- value="{{ old('lokasi_pengambilan') }}"> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}

                            <div class="col-lg-12">
                                <div class="form-group mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="nama_pengambilan" class="mb-0">Titik Pengambilan</label>
                                        <div class="form-check form-check-flat form-check-primary mb-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" id="is_pudam"
                                                    name="is_pudam" type="checkbox" value="true">
                                                Nama dan Detail Titik Pengambilan
                                                <i class="input-helper"></i></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group date" id="titik_pengambilan_text">
                                    <textarea class="form-control" id="titik_pengambilan" name="titik_pengambilan" rows="2">{{ $sample->titik_pengambilan ?? old('titik_pengambilan') }}</textarea>
                                </div>
                                <div class="row" id="pdam" hidden="true">
                                    <div class="col-md-6">
                                        <label for="name_customer_pdam">Nama Titik (Kimia)</label>
                                        <input type="text" class="form-control" name="name_customer_pdam"
                                            id="name_customer_pdam" placeholder="Nama Titik"
                                            value="{{ $sample->name_customer_pdam ?? old('name_customer_pdam') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address_location_pdam">Detail Titik (Mikro)</label>
                                        <textarea class="form-control" id="address_location_pdam" name="address_location_pdam" rows="2">{{ $sample->address_location_pdam ?? old('address_location_pdam') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- End Detail Sampel Section -->

                <!-- Penerimaan Sampel Section -->
                <div class="form-section-card-sample mt-4">
                    <div class="section-title-sample">
                        <i class="fa fa-clipboard-check"></i>
                        Penerimaan Sampel <span style="color: #e53e3e; margin-left: 5px;">*</span>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="tempat_kemasan" style="font-size: 15px; color: #2d3748;">
                                <i class="fa fa-box" style="color: #11998e; margin-right: 8px;"></i>
                                <b>1. Tempat / Kemasan</b>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_tempat_kemasan" type="radio"
                                    value="layak" id="tempat_kemasan_layak" checked>
                                <label class="form-check-label" for="tempat_kemasan_layak">
                                    Layak
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_tempat_kemasan" type="radio"
                                    value="tidak layak" id="tempat_kemasan_tidak_layak">
                                <label class="form-check-label" for="tempat_kemasan_tidak_layak">
                                    Tidak Layak
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="berat_vol" style="font-size: 15px; color: #2d3748;">
                                <i class="fa fa-weight" style="color: #11998e; margin-right: 8px;"></i>
                                <b>2. Berat / Vol</b>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_berat_vol" type="radio" value="layak"
                                    id="berat_vol_layak" checked>
                                <label class="form-check-label" for="berat_vol_layak">
                                    Layak
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="kelayakan_berat_vol" type="radio"
                                    value="tidak layak" id="berat_vol_tidak_layak">
                                <label class="form-check-label" for="berat_vol_tidak_layak">
                                    Tidak Layak
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Penerimaan Sampel Section -->

                <!-- Catatan Sampel Section -->
                <div class="form-section-card-sample mt-4">
                    <div class="section-title-sample">
                        <i class="fa fa-sticky-note"></i>
                        Catatan Sampel
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleFormControlTextarea1" style="font-size: 14px; color: #4a5568;">
                                <i class="fa fa-edit" style="color: #11998e; margin-right: 8px;"></i>
                                Tambahkan catatan atau informasi tambahan mengenai sampel
                            </label>
                            <textarea class="form-control" name="note" id="exampleFormControlTextarea1" rows="6"
                                placeholder="Masukkan catatan sampel di sini...">{{ old('note') ?? '-' }}</textarea>
                        </div>
                    </div>
                </div>
                <!-- End Catatan Sampel Section -->

                <!-- Step Navigation -->
                <div class="step-navigation">
                    <div></div>
                    <button type="button" class="btn-step btn-next" onclick="nextStep(1)">
                        Selanjutnya <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <!-- END STEP 1 -->

            <!-- STEP 2: Jenis & Parameter -->
            <div class="form-step" data-step="2">
                <div class="step-error" id="step2-error">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span id="step2-error-message"></span>
                </div>

                <div class="col-lg-12 mt-2 mb-4">
                    <!-- Jenis Sampel Section -->
                    <div class="form-section-card-sample">
                        <div class="section-title-sample">
                            <i class="fa fa-list-alt"></i>
                            Jenis Sampel <span style="color: #e53e3e; margin-left: 5px;">*</span>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="d-flex flex-wrap" style="gap: 10px">
                                    @foreach ($sampletypes as $sampletype)
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-pick-jenis"
                                            data-id="{{ $sampletype->id_sample_type }}"
                                            data-code="{{ $sampletype->code_sample_type }}">
                                            @empty($sampletype->code_sample_type)
                                                {{ $sampletype->name_sample_type }}
                                            @else
                                                [{{ $sampletype->code_sample_type }}]
                                                {{ $sampletype->name_sample_type }}
                                            @endempty
                                        </button>
                                    @endforeach
                                    <input type="hidden" id="jenis_sampel" name="jenis_sampel" value="">
                                </div>
                                <div class="mt-3" id="form_jenis_makanan" style="display: none">
                                    <label for="jenis_makanan_minuman" class="mb-1">Jenis Makanan</label>
                                    <input type="text" class="form-control" name="jenis_makanan_minuman"
                                        id="jenis_makanan_minuman" placeholder="Jenis makanan">
                                </div>
                            </div>
                        </div>

                        <div class="is_rectal_swab" style="display: none">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>

                                    <div class="row">
                                        <div class="col-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="gender_samples"
                                                        id="gender_samples_1" value="L">
                                                    Laki-Laki
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="gender_samples"
                                                        id="gender_samples_2" value="P">
                                                    Perempuan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="umur_samples">Umur</label>

                                    <input type="number" class="form-control" name="umur_samples" id="umur_samples"
                                        placeholder="Umur..">
                                </div>
                            </div>
                        </div>

                        <div class="is_paket" style="display: none">
                            <input type="hidden" id="is_paket" name="is_paket" value="false">
                        </div>


                        <div class="packet" style="display: none;">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="mb-2">Pilih Paket</label>
                                    <div class="d-flex flex-wrap packet-buttons-container" style="gap: 8px">
                                        <!-- Paket buttons will be dynamically loaded here based on jenis sampel -->
                                    </div>
                                    <select id="packet" name="packet[]" class="d-none" multiple>
                                        <!-- Options will be dynamically loaded -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Jenis sample --}}
                        <div id="jenis_sample_uji_usap" style="display: none">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <select class="form-control" name="jenis_sample_uji_usap">
                                        <option value="Alat Masak">Alat Masak</option>
                                        <option value="Alat Makan">Alat Makan</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="container method">
                                    <div class="row">
                                        <!-- Left Column: Parameter List -->
                                        <div class="col-lg-8">
                                            <div class="form-section-card-sample" style="padding: 0; overflow: hidden;">
                                                <div class="card-header d-flex justify-content-between align-items-center"
                                                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; padding: 20px; margin: 0;">
                                                    <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                        <i class="fa fa-microscope"></i> Parameter Pengujian
                                                    </h5>
                                                    <div class="d-flex align-items-center" style="gap: 10px;">
                                                        <input type="text" id="search-parameter" class="form-control"
                                                            placeholder="🔍 Cari parameter..."
                                                            style="width: 250px; background: white; border: 2px solid #e2e8f0; padding: 8px 15px;">
                                                        <button type="button" class="btn btn-sm" id="expand-all-params"
                                                            style="background: white; color: #0b3a5c; border: 2px solid white; font-weight: 600; padding: 8px 15px;">
                                                            <i class="fas fa-expand-alt"></i> Expand
                                                        </button>
                                                        <button type="button" class="btn btn-sm"
                                                            id="collapse-all-params"
                                                            style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; font-weight: 600; padding: 8px 15px;">
                                                            <i class="fas fa-compress-alt"></i> Collapse
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <!-- Selected Parameters Section (Auto-sorted ke atas) -->
                                                    <div id="selected-parameters-section" class="mb-4"
                                                        style="display: none;">
                                                        <div class="alert alert-info">
                                                            <h5><i class="fas fa-check-circle"></i> Parameter Terpilih</h5>
                                                            <small>Parameter yang sudah dicentang dari paket akan muncul di
                                                                sini</small>
                                                        </div>
                                                        <div class="row" id="selected-parameters-container"></div>
                                                    </div>

                                                    <hr id="selected-separator" style="display: none;">

                                                    <!-- All Parameters Section -->
                                                    <div class="row" id="parameters-container">
                                                        @php
                                                            $char = 'A';
                                                        @endphp

                                                        @for ($i = 0; $i < count($data_methods); $i++)
                                                            @if ($i % 2 == 0 && $i != 0)
                                                                <div class="col-6 parameter-group"
                                                                    data-category="{{ $data_methods[$i]->name }}">
                                                                    <div class="parameter-group-header"
                                                                        data-toggle="collapse"
                                                                        data-target="#param-collapse-{{ $i }}"
                                                                        style="cursor: pointer; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                                                        <h5 class="mb-0">
                                                                            <i
                                                                                class="fas fa-chevron-down collapse-icon"></i>
                                                                            {{ $char }}. Parameter
                                                                            {{ $data_methods[$i]->name }}
                                                                            <span
                                                                                class="badge badge-primary ml-2 param-count">0</span>
                                                                        </h5>
                                                                        @php
                                                                            $char++;
                                                                        @endphp
                                                                    </div>
                                                                    <div class="collapse"
                                                                        id="param-collapse-{{ $i }}">
                                                                        <table>
                                                                            @foreach ($data_methods[$i]->method as $method)
                                                                                <tr class="method-row method-row-{{ $method->id_method }}"
                                                                                    data-method-name="{{ strtolower($method->name_method) }}"
                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}">
                                                                                    <td>
                                                                                        <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input name="method[]"
                                                                                                    class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                                                    data-price="{{ $method->price_method }}"
                                                                                                    data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                                                    data-idmethod="{{ $method->id_method }}"
                                                                                                    data-lab-name="{{ $data_methods[$i]->name_lab ?? '' }}"
                                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}"
                                                                                                    type="checkbox"
                                                                                                    value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                                                    id="defaultCheck3"
                                                                                                    disabled>
                                                                                                <label
                                                                                                    class="form-check-label"
                                                                                                    for="defaultCheck3">
                                                                                                    {{ $method->name_method }}
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td style="display: none;">
                                                                                        <div class="form-group">
                                                                                            <input style="width: 150px"
                                                                                                type="text"
                                                                                                class="form-control"
                                                                                                id="input_price_method_{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                                                value="{{ $method->price_method }}"
                                                                                                placeholder="Harga">
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="col-6 parameter-group"
                                                                    data-category="{{ $data_methods[$i]->name }}">
                                                                    <div class="parameter-group-header"
                                                                        data-toggle="collapse"
                                                                        data-target="#param-collapse-{{ $i }}"
                                                                        style="cursor: pointer; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                                                        <h5 class="mb-0">
                                                                            <i
                                                                                class="fas fa-chevron-down collapse-icon"></i>
                                                                            {{ $char }}. Parameter
                                                                            {{ $data_methods[$i]->name }}
                                                                            <span
                                                                                class="badge badge-primary ml-2 param-count">0</span>
                                                                        </h5>
                                                                        @php
                                                                            $char++;
                                                                        @endphp
                                                                    </div>
                                                                    <div class="collapse"
                                                                        id="param-collapse-{{ $i }}">
                                                                        <table>
                                                                            @foreach ($data_methods[$i]->method as $method)
                                                                                <tr class="method-row method-row-{{ $method->id_method }}"
                                                                                    data-method-name="{{ strtolower($method->name_method) }}"
                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}">
                                                                                    <td>
                                                                                        <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input name="method[]"
                                                                                                    class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                                                    data-price="{{ $method->price_method }}"
                                                                                                    data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                                                    data-idmethod="{{ $method->id_method }}"
                                                                                                    data-lab-name="{{ $data_methods[$i]->name_lab ?? '' }}"
                                                                                                    data-baku-mutu-sampletypes="{{ json_encode($method->baku_mutu_sampletypes) }}"
                                                                                                    type="checkbox"
                                                                                                    value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}_{{ $method->price_method }}"
                                                                                                    id="defaultCheck3"
                                                                                                    disabled>
                                                                                                <label
                                                                                                    class="form-check-label"
                                                                                                    for="defaultCheck3">
                                                                                                    {{ $method->name_method }}
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td style="display: none;">
                                                                                        <div class="form-group">
                                                                                            <input style="width: 150px"
                                                                                                type="text"
                                                                                                class="form-control"
                                                                                                id="input_price_method_{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                                                value="{{ $method->price_method }}"
                                                                                                placeholder="Harga">
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endfor
                                                    </div>

                                                    <!-- Pagination Controls -->
                                                    <div class="d-flex justify-content-between align-items-center mt-4"
                                                        id="pagination-controls">
                                                        <div id="showing-info" class="text-muted"></div>
                                                        <nav>
                                                            <ul class="pagination mb-0" id="pagination">
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: Floating Cart Widget -->
                                        <div class="col-lg-4">
                                            <div class="form-section-card-sample" id="parameter-cart"
                                                style="position: sticky; top: 20px; padding: 0; overflow: hidden;">
                                                <div class="card-header"
                                                    style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px; margin: 0; border: none;">
                                                    <h5 class="mb-0" style="color: white; font-weight: 600;">
                                                        <i class="fas fa-shopping-cart"></i> Parameter Terpilih
                                                    </h5>
                                                </div>
                                                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                                    <!-- Packet Info (if from packet) -->
                                                    <div id="cart-packet-info" style="display: none;"
                                                        class="alert alert-info mb-3">
                                                        <strong><i class="fas fa-box"></i> Paket:</strong>
                                                        <div id="cart-packet-name"></div>
                                                    </div>

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
                                                        <strong style="color: #4a5568;">Total Parameter:</strong>
                                                        <span class="badge badge-lg"
                                                            style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; padding: 8px 15px; font-size: 14px; border-radius: 8px;"
                                                            id="cart-total-items">0</span>
                                                    </div>

                                                    <!-- Price Breakdown -->
                                                    <div id="cart-price-breakdown" style="display: none;" class="mb-3">
                                                        <!-- Breakdown will be inserted here by JS -->
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mb-3"
                                                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);">
                                                        <strong style="font-size: 1.1rem; color: white;">Total
                                                            Harga:</strong>
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
                                        <!-- End Right Column -->


                                    </div>
                                    <!-- End Row -->
                                </div>
                                <!-- End container method -->
                            </div>
                            <!-- End form-group parameter -->
                        </div>

                        <div class="step-navigation">
                            <button type="button" class="btn-step btn-prev" onclick="prevStep(2)">
                                <i class="fa fa-arrow-left"></i> Sebelumnya
                            </button>
                            <button type="button" class="btn-step btn-next" onclick="nextStep(2)">
                                Selanjutnya <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>


            <!-- STEP 3: Review & Simpan -->
            <div class="form-step" data-step="3">
                <div class="form-section-card-sample">
                    <div class="section-title-sample">
                        <i class="fa fa-check-circle"></i>
                        Review Data Sampel
                    </div>
                    <div class="col-lg-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Periksa kembali data yang telah diisi sebelum menyimpan.</strong>
                        </div>
                        <div id="review-content" style="padding: 20px;">
                            <!-- Review content will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Step Navigation -->
                <div class="step-navigation">
                    <button type="button" class="btn-step btn-prev" onclick="prevStep(3)">
                        <i class="fa fa-arrow-left"></i> Sebelumnya
                    </button>
                    <button type="submit" id="submitAll" class="btn-step btn-next btn-simpan">
                        <i class="fa fa-save"></i> Simpan Sampel
                    </button>
                </div>
            </div>
            <!-- END STEP 3 -->

            <div class="col-lg-12" hidden>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="code_sample_customer"> Kode Sampel Pelanggan:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code_sample_customer" value="-"
                                    id="code_sample_customer" placeholder="Kode Sampel Pelanggan"
                                    value="{{ old('code_sample_customer') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12" hidden>
                <div class="form-group">
                    <label for="customer_samples"><span style="color: red">*</span>Program:</label>
                    <select id="program_samples" name="program_samples"
                        class="js-customer-basic-multiple js-states form-control" style="width: 100%" required>
                        <option value="" disabled selected> Pilih Program</option>
                        <option value="{{ $programs[0]->id_program }}" selected>
                            {{ $programs[0]->name_program }}</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id_program }}">{{ $program->name_program }}</option>
                        @endforeach
                    </select>
                </div>
            </div>







            <!-- End col-lg-12 parameter section -->


            <div class="col-lg-12" hidden>
                <div class="form-group">
                    <label for="cost_samples"><strong>Harga Total</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp.</span>
                        </div>
                        <input type="number" class="form-control form-control-lg" id="cost_samples" name="cost_samples"
                            value="0" placeholder="Harga Total" readonly required style="font-weight: 600;">
                    </div>
                </div>
            </div>
            <!-- End col-lg-12 hidden section -->

        </div>
    </form>
@endsection

@section('scripts')
    <script>
        // Multi-Step Wizard Functions
        let currentStep = 1;
        const totalSteps = 3;

        function showStep(step) {
            console.log(`=== showStep(${step}) called ===`);

            // Hide all steps
            const allSteps = document.querySelectorAll('.form-step');
            console.log(`Found ${allSteps.length} steps to hide`);
            allSteps.forEach(s => {
                s.classList.remove('active');
                console.log(`Removed active from step ${s.getAttribute('data-step')}`);
            });

            // Show current step
            const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
            console.log(`Step ${step} element found:`, stepElement !== null);
            if (stepElement) {
                stepElement.classList.add('active');
                console.log(`Added active to step ${step}`);
                console.log(`Step ${step} display:`, window.getComputedStyle(stepElement).display);
            } else {
                console.error(`Step ${step} element NOT FOUND!`);
            }

            // Update step indicator
            document.querySelectorAll('.step-item').forEach((item, index) => {
                item.classList.remove('active', 'completed');
                const stepNum = index + 1;

                if (stepNum < step) {
                    item.classList.add('completed');
                } else if (stepNum === step) {
                    item.classList.add('active');
                }
            });

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function validateStep(step) {
            const errorDiv = document.getElementById(`step${step}-error`);
            const errorMessage = document.getElementById(`step${step}-error-message`);

            errorDiv.classList.remove('show');

            if (step === 1) {
                // Validate Step 1: Penerimaan Sampel
                const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked');
                const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked');

                if (!tempatKemasan) {
                    errorMessage.textContent = 'Pilih kelayakan Tempat/Kemasan (Layak/Tidak Layak)';
                    errorDiv.classList.add('show');
                    return false;
                }

                if (!beratVol) {
                    errorMessage.textContent = 'Pilih kelayakan Berat/Vol (Layak/Tidak Layak)';
                    errorDiv.classList.add('show');
                    return false;
                }

                return true;
            }

            if (step === 2) {
                // Validate Step 2: Jenis & Parameter
                const jenisSampel = document.getElementById('jenis_sampel').value;
                const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
                const packetSelect = document.getElementById('packet');

                let hasPacket = false;
                if (packetSelect) {
                    const selectedOptions = Array.from(packetSelect.selectedOptions);
                    hasPacket = selectedOptions.length > 0 && selectedOptions.some(opt => opt.value !== '');
                }

                const hasParameterOrPacket = selectedParams.length > 0 || hasPacket;

                if (!jenisSampel) {
                    errorMessage.textContent = 'Pilih jenis sampel terlebih dahulu';
                    errorDiv.classList.add('show');
                    return false;
                }

                if (!hasParameterOrPacket) {
                    errorMessage.textContent = 'Pilih minimal 1 paket atau parameter pengujian';
                    errorDiv.classList.add('show');
                    return false;
                }

                // Populate review
                populateReview();

                return true;
            }

            return true;
        }

        function nextStep(step) {
            if (validateStep(step)) {
                currentStep = step + 1;
                if (currentStep <= totalSteps) {
                    showStep(currentStep);
                }
            }
        }

        function prevStep(step) {
            currentStep = step - 1;
            if (currentStep >= 1) {
                showStep(currentStep);
            }
        }

        function populateReview() {
            const reviewContent = document.getElementById('review-content');

            // Get form data
            const jenisSampel = document.getElementById('jenis_sampel').value;
            const jenisSampelText = document.querySelector('.btn-pick-jenis.active')?.textContent.trim() || '-';
            const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked')?.value || '-';
            const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked')?.value || '-';
            const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
            const totalHarga = document.getElementById('cart-total-price')?.textContent || 'Rp 0';

            // Get kode sampel
            const kodeSampelKimia = document.getElementById('input_code_sample_kimia')?.value || '-';
            const kodeSampelMikro = document.getElementById('input_code_sample_mikro')?.value || '-';

            // Check if using packet
            const isPaket = document.getElementById('is_paket')?.value === 'true';
            const selectedPackets = [];
            if (isPaket) {
                const packetSelect = document.getElementById('packet');
                if (packetSelect) {
                    Array.from(packetSelect.selectedOptions).forEach(option => {
                        if (option.value) {
                            selectedPackets.push(option.text.trim());
                        }
                    });
                }
            }

            let html = '<div style="display: grid; gap: 20px;">';

            // Kode Sampel Section
            const kimiaVisible = document.getElementById('code_sample_kimia_wrapper_top')?.style.display !== 'none';
            const mikroVisible = document.getElementById('code_sample_mikro_wrapper_top')?.style.display !== 'none';

            if (kimiaVisible || mikroVisible) {
                html +=
                    '<div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 20px; border-radius: 10px; border-left: 5px solid #2196f3;">';
                html += '<h6 style="color: #1976d2; margin-bottom: 15px;"><i class="fa fa-barcode"></i> Kode Sampel</h6>';
                if (kimiaVisible && kodeSampelKimia !== '-') {
                    html +=
                        `<p><strong>Kode Sampel Kimia:</strong> <span style="color: #11998e; font-weight: 700; font-size: 16px; letter-spacing: 1px;">${kodeSampelKimia}</span></p>`;
                }
                if (mikroVisible && kodeSampelMikro !== '-') {
                    html +=
                        `<p><strong>Kode Sampel Mikrobiologi:</strong> <span style="color: #0b3a5c; font-weight: 700; font-size: 16px; letter-spacing: 1px;">${kodeSampelMikro}</span></p>`;
                }
                html += '</div>';
            }

            // Detail Sampel
            html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
            html += '<h6 style="color: #11998e; margin-bottom: 15px;"><i class="fa fa-vial"></i> Detail Sampel</h6>';
            html += `<p><strong>Jenis Sampel:</strong> ${jenisSampelText}</p>`;
            html +=
                `<p><strong>Kelayakan Tempat/Kemasan:</strong> <span style="color: ${tempatKemasan === 'layak' ? '#28a745' : '#dc3545'}">${tempatKemasan}</span></p>`;
            html +=
                `<p><strong>Kelayakan Berat/Vol:</strong> <span style="color: ${beratVol === 'layak' ? '#28a745' : '#dc3545'}">${beratVol}</span></p>`;
            html += '</div>';

            // Separate parameters into packet and additional (satuan)
            let packetParams = [];
            let additionalParams = [];
            let packetPrice = 0;
            let additionalPrice = 0;

            // Get actual packet price from window (not sum of parameters)
            if (window.packetPrice) {
                packetPrice = parseInt(window.packetPrice) || 0;
            }

            if (selectedParams.length > 0) {
                selectedParams.forEach((param) => {
                    const paramId = param.getAttribute('data-idmethod');
                    const paramName = param.closest('.method-row')?.querySelector('label')?.textContent.trim() ||
                        'Unknown';
                    const paramPrice = parseInt(param.getAttribute('data-price')) || 0;

                    // Check if parameter is from packet
                    const isFromPacket = window.packetParameterIds && window.packetParameterIds.includes(paramId);

                    if (isFromPacket) {
                        packetParams.push({
                            name: paramName,
                            price: paramPrice
                        });
                    } else {
                        additionalParams.push({
                            name: paramName,
                            price: paramPrice
                        });
                        additionalPrice += paramPrice;
                    }
                });
            }

            // Paket Section (if applicable)
            if (isPaket && selectedPackets.length > 0) {
                html +=
                    '<div style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); padding: 20px; border-radius: 10px; border-left: 5px solid #ff9800;">';
                html += '<h6 style="color: #f57c00; margin-bottom: 15px;"><i class="fa fa-box"></i> Paket Terpilih</h6>';
                html += '<ul style="margin: 0; padding-left: 20px;">';
                selectedPackets.forEach(packet => {
                    html += `<li style="margin-bottom: 8px;"><strong>${packet}</strong></li>`;
                });
                html += '</ul>';

                // Show packet parameters (without individual prices)
                if (packetParams.length > 0) {
                    html += '<div style="margin-top: 15px;">';
                    html += '<strong style="color: #f57c00;">Parameter dalam Paket:</strong>';
                    html += '<ul style="margin-top: 10px; padding-left: 20px;">';
                    packetParams.forEach((param, index) => {
                        html +=
                            `<li style="margin-bottom: 5px; color: #666;">${index + 1}. ${param.name}</li>`;
                    });
                    html += '</ul>';
                    html += '</div>';
                }

                // Show packet price (actual packet price, not sum of parameters)
                if (packetPrice > 0) {
                    const packetPriceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(packetPrice);
                    html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #ff9800;">`;
                    html +=
                        `<p style="margin: 0;"><strong>Harga Paket:</strong> <span style="color: #f57c00; font-size: 20px; font-weight: 700;">${packetPriceFormatted}</span></p>`;
                    html += `</div>`;
                }

                html += '</div>';
            }

            // Additional Parameters (Satuan) Section
            if (additionalParams.length > 0) {
                html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
                html +=
                    '<h6 style="color: #0b3a5c; margin-bottom: 15px;"><i class="fa fa-plus-circle"></i> Parameter Tambahan (Satuan)</h6>';
                html += `<p><strong>Total Parameter Tambahan:</strong> ${additionalParams.length}</p>`;

                html += '<div style="margin-top: 15px;">';
                html += '<ul style="margin-top: 10px; padding-left: 20px; max-height: 300px; overflow-y: auto;">';

                additionalParams.forEach((param, index) => {
                    const priceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(param.price);
                    html += `<li style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 5px;">`;
                    html += `<span style="color: #2d3748;">${index + 1}. ${param.name}</span>`;
                    html +=
                        ` <span style="color: #0b3a5c; font-weight: 600; float: right;">${priceFormatted}</span>`;
                    html += `</li>`;
                });

                html += '</ul>';
                html += '</div>';

                // Show additional total price
                const additionalPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(additionalPrice);
                html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #0b3a5c;">`;
                html +=
                    `<p style="margin: 0;"><strong>Harga Parameter Tambahan:</strong> <span style="color: #0b3a5c; font-size: 18px; font-weight: 700;">${additionalPriceFormatted}</span></p>`;
                html += `</div>`;

                html += '</div>';
            }

            // All Parameters (if no packet mode)
            if (!isPaket && selectedParams.length > 0) {
                html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">';
                html +=
                    '<h6 style="color: #0b3a5c; margin-bottom: 15px;"><i class="fa fa-microscope"></i> Parameter Terpilih</h6>';
                html += `<p><strong>Total Parameter:</strong> ${selectedParams.length}</p>`;

                html += '<div style="margin-top: 15px;">';
                html += '<strong>Detail Parameter:</strong>';
                html += '<ul style="margin-top: 10px; padding-left: 20px; max-height: 300px; overflow-y: auto;">';

                selectedParams.forEach((param, index) => {
                    const paramName = param.closest('.method-row')?.querySelector('label')?.textContent.trim() ||
                        'Unknown';
                    const paramPrice = param.getAttribute('data-price') || '0';
                    const priceFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(paramPrice);

                    html += `<li style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 5px;">`;
                    html += `<span style="color: #2d3748;">${index + 1}. ${paramName}</span>`;
                    html +=
                        ` <span style="color: #11998e; font-weight: 600; float: right;">${priceFormatted}</span>`;
                    html += `</li>`;
                });

                html += '</ul>';
                html += '</div>';

                // Show total price for all parameters (satuan mode)
                let totalSatuanPrice = 0;
                selectedParams.forEach((param) => {
                    totalSatuanPrice += parseInt(param.getAttribute('data-price')) || 0;
                });

                if (totalSatuanPrice > 0) {
                    const totalSatuanFormatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(totalSatuanPrice);
                    html += `<div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #0b3a5c;">`;
                    html +=
                        `<p style="margin: 0;"><strong>Total Harga Satuan:</strong> <span style="color: #0b3a5c; font-size: 20px; font-weight: 700;">${totalSatuanFormatted}</span></p>`;
                    html += `</div>`;
                }

                html += '</div>';
            }

            // Grand Total Section
            html +=
                '<div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 25px; border-radius: 10px; border-left: 5px solid #4caf50;">';
            html +=
                '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">';

            if (isPaket && packetPrice > 0) {
                const packetPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(packetPrice);
                html += '<div>';
                html += '<p style="margin: 0; color: #666; font-size: 14px;">Harga Paket</p>';
                html +=
                    `<p style="margin: 5px 0 0 0; color: #f57c00; font-size: 18px; font-weight: 600;">${packetPriceFormatted}</p>`;
                html += '</div>';
            }

            if (additionalPrice > 0) {
                const additionalPriceFormatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(additionalPrice);
                html += '<div>';
                html += '<p style="margin: 0; color: #666; font-size: 14px;">Harga Satuan</p>';
                html +=
                    `<p style="margin: 5px 0 0 0; color: #0b3a5c; font-size: 18px; font-weight: 600;">${additionalPriceFormatted}</p>`;
                html += '</div>';
            }

            html += '<div style="flex-grow: 1; text-align: right;">';
            html += '<p style="margin: 0; color: #2d3748; font-size: 16px; font-weight: 600;">TOTAL HARGA</p>';
            html += `<p style="margin: 5px 0 0 0; color: #11998e; font-size: 28px; font-weight: 700;">${totalHarga}</p>`;
            html += '</div>';

            html += '</div>';
            html += '</div>';

            html += '</div>';

            reviewContent.innerHTML = html;
        }

        // Form Validation for Sticky Button
        function validateForm() {
            const submitBtn = document.getElementById('submitAll');
            const validationStatus = document.getElementById('validation-status');
            const validationMessage = document.getElementById('validation-message');

            // For EDIT mode, always enable submit button (data already exists)
            // This is an edit form, not create form
            submitBtn.disabled = false;
            validationStatus.style.display = 'none';
            return; // Skip validation for edit mode

            // Check required fields
            const jenisSampel = document.getElementById('jenis_sampel').value;
            const tempatKemasan = document.querySelector('input[name="kelayakan_tempat_kemasan"]:checked');
            const beratVol = document.querySelector('input[name="kelayakan_berat_vol"]:checked');

            // Check if at least one parameter OR packet is selected
            const selectedParams = document.querySelectorAll('input[name="method[]"]:checked');
            const selectedPackets = document.querySelectorAll('select[name="packet[]"] option:checked');
            const packetSelect = document.getElementById('packet');

            // Check if packet is selected (either via select or hidden input)
            let hasPacket = false;
            if (packetSelect) {
                const selectedOptions = Array.from(packetSelect.selectedOptions);
                hasPacket = selectedOptions.length > 0 && selectedOptions.some(opt => opt.value !== '');
            }

            // Valid if either has parameters OR has packet
            const hasParameterOrPacket = selectedParams.length > 0 || hasPacket;

            let isValid = true;
            let message = '';

            if (!jenisSampel) {
                isValid = false;
                message = 'Pilih jenis sampel terlebih dahulu';
            } else if (!hasParameterOrPacket) {
                isValid = false;
                message = 'Pilih minimal 1 paket atau parameter pengujian';
            } else if (!tempatKemasan) {
                isValid = false;
                message = 'Pilih kelayakan tempat/kemasan';
            } else if (!beratVol) {
                isValid = false;
                message = 'Pilih kelayakan berat/vol';
            }

            if (isValid) {
                submitBtn.disabled = false;
                validationStatus.style.display = 'none';
            } else {
                submitBtn.disabled = true;
                validationStatus.style.display = 'flex';
                validationStatus.style.alignItems = 'center';
                validationMessage.textContent = message;
            }
        }

        // Initialize everything on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== Initializing Form ===');

            // Delay wizard initialization to ensure all jQuery ready handlers complete
            setTimeout(function() {
                console.log('Step 1: Initializing wizard...');

                // Check if elements exist
                const stepIndicator = document.querySelector('.step-indicator');
                const formSteps = document.querySelectorAll('.form-step');

                console.log('Step Indicator found:', stepIndicator !== null);
                console.log('Form Steps found:', formSteps.length);

                // Debug each step
                formSteps.forEach((step, index) => {
                    const stepNum = step.getAttribute('data-step');
                    const display = window.getComputedStyle(step).display;
                    console.log(
                        `Step ${stepNum}: display = ${display}, classList = ${step.classList.toString()}`
                    );
                });

                if (stepIndicator && formSteps.length > 0) {
                    showStep(1);
                    console.log('✓ Wizard initialized at step 1');
                } else {
                    console.error('✗ Wizard elements not found!');
                }

                // Then setup validation
                console.log('Step 2: Setting up validation...');
                validateForm();

                // Add event listeners to all form inputs
                const form = document.getElementById('form-create-sample');
                if (form) {
                    form.addEventListener('change', validateForm);
                    form.addEventListener('input', validateForm);
                    console.log('✓ Form event listeners attached');
                }
            }, 100); // Wait 100ms for jQuery ready to complete

            // Specific listeners for dynamic elements
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-pick-jenis') ||
                    e.target.classList.contains('btn-pick-paket') ||
                    e.target.name === 'method[]' ||
                    e.target.name === 'kelayakan_tempat_kemasan' ||
                    e.target.name === 'kelayakan_berat_vol') {
                    setTimeout(validateForm, 100);
                }
            });

            // Listen to packet select changes
            const packetSelect = document.getElementById('packet');
            if (packetSelect) {
                packetSelect.addEventListener('change', function() {
                    setTimeout(validateForm, 100);
                });
            }

            // Observer for dynamic packet buttons
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        validateForm();
                    }
                });
            });

            const packetContainer = document.querySelector('.packet-buttons-container');
            if (packetContainer) {
                observer.observe(packetContainer, {
                    childList: true,
                    subtree: true
                });
            }
        });

        var methods

        var methods_sample_type = []
        var jenis_sample, jenis_makanan

        var price_sample_type = 0;

        let is_multiple_labs = false;
        let select_multiple_codes = false;

        let lab_id = null;
        let lab_keys = [];
        let lab_keys_sequences = [];

        function integerToRoman(integer) {
            // Convert the integer into an integer (just to make sure)
            integer = parseInt(integer);
            let result = '';

            // Create a lookup array that contains all of the Roman numerals.
            const lookup = {
                'M': 1000,
                'CM': 900,
                'D': 500,
                'CD': 400,
                'C': 100,
                'XC': 90,
                'L': 50,
                'XL': 40,
                'X': 10,
                'IX': 9,
                'V': 5,
                'IV': 4,
                'I': 1
            };

            for (const roman in lookup) {
                // Determine the number of matches
                const value = lookup[roman];
                const matches = Math.floor(integer / value);

                // Add the same number of characters to the string
                result += roman.repeat(matches);

                // Set the integer to be the remainder of the integer and the value
                integer = integer % value;
            }

            // The Roman numeral should be built, return it
            return result;
        }



        $('#ispacket').val("false")

        $(".checkbox").change(function() {
            var total = 0;
            methods = [];
            $(".checkbox:checked").each(function() {
                var idmethod = $(this).data('idmethod');
                var foundMethod = methods.find(function(item) {
                    return item == idmethod;
                });
                if (!foundMethod) {
                    total = total + parseInt($(this).data('price'))
                    methods.push(idmethod)
                }
            });
            let difference = methods.filter(x => !methods_sample_type.includes(x));

            if (arrayContainsArray(methods, methods_sample_type)) {
                $('#ispacket').val("true")
                if (price_sample_type != 0) {
                    let difference = methods.filter(x => !methods_sample_type.includes(x));
                    var total_difference = 0;
                    $(".checkbox:checked").each(function() {

                        var idmethod = $(this).data('idmethod');
                        var foundMethod = difference.find(function(item) {
                            return item == idmethod;
                        });
                        if (foundMethod) {
                            total_difference = total_difference + $(this).data('price')
                            // total=total+parseInt($(this).data('price'))
                            // methods.push(idmethod)
                        }
                    });
                    $('#cost_samples').val(price_sample_type + total_difference)
                } else {
                    $('#cost_samples').val(total)
                }
            } else {
                $('#ispacket').val("false")
                $('#cost_samples').val(total)
            }

            checkMultipleLabs();
        });

        let arrayContainsArray = (a_array, b_array) => {


            for (let i = 0; i < b_array.length; i++) {
                if (a_array.includes(b_array[i])) {
                    let index = a_array.indexOf(b_array[i])
                    a_array.splice(index, 1)
                } else {
                    return false
                }
            }
            return true
        }

        // Paket always visible now - auto-detect mode



        // $code_year       $(".checkbox").prop("disabled", true);

        $("#is_paket").change(function() {

            $(".checkbox").prop("disabled", false);
            $(".checkbox").attr("data-toggle", "tooltip");
            $(".checkbox").attr("data-placement", "right");
            $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
            $("[data-toggle='tooltip']").tooltip();
            $('#cost_samples').val(0);
            $(".checkbox").prop("readonly", false);

            var jenis_sample_text = $("#jenis_sampel").children(":selected").text();


            var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
            url = url.replace('#', jenis_sample);



            $.ajax({
                url: url,
                type: "GET",
                datatype: 'json',
                success: function(response) {
                    var results = response.data;
                    results.forEach(result => {
                        $(".checkbox-" + result.id_method).prop("disabled", false);
                        $(".checkbox-" + result.id_method).removeAttr("title")
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-toggle");
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-placement");
                        $(".checkbox-" + result.id_method).removeAttr(
                            "data-original-title");

                    })
                },
            })

            if (this.checked) {
                // Paket always visible in auto-detect mode

                // Initialize Select2 with proper options for hidden multiple select
                // if ($('#packet').length && !$('#packet').hasClass('select2-hidden-accessible')) {
                //     $('#packet').select2({
                //         theme: 'classic',
                //         width: '100%',
                //         allowClear: true
                //     });
                // }

                methods_sample_type = [];
                price_sample_type = 0;
                $(".checkbox").prop("checked", false);
                $('#cost_samples').val(0);


                if (jenis_sample != null && jenis_sample != undefined) {

                    $(".checkbox").prop("checked", false);



                    var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
                    url = url.replace('#', jenis_sample);


                    $.ajax({
                        url: url,
                        type: "GET",
                        datatype: 'json',
                        success: function(response) {
                            var results = response.data;

                            results.forEach(result => {
                                $(".checkbox-" + result.id_method).prop("disabled", false);
                                $(".checkbox-" + result.id_method).removeAttr("title")
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-toggle");
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-placement");
                                $(".checkbox-" + result.id_method).removeAttr(
                                    "data-original-title");

                            })
                        },
                    })
                    var url = "/api/packet/#"
                    url = url.replace('#', jenis_sample);


                    $.ajax({
                        url: url,
                        type: "POST",
                        datatype: 'json',
                        success: function(response) {
                            $(".is_paket").css('display', 'block');

                            // $(".packet").css('display', 'none');
                            // $("#is_paket").prop('checked', false);
                            // $('#packet').val(null).trigger("change");
                            $('#packet')
                                .find('option')
                                .remove()
                                .end();
                            var results = response.results
                            results.forEach(result => {
                                $('#packet')
                                    .append('<option value="' + result.id +
                                        '" data-extra="' + result.data_extra + '">' +
                                        result.text + '</option>');
                            })

                            $("#packet").change(function() {



                                var packet = $(this).val();

                                var data = $("#packet option:selected").text();

                                if (data === 'ALT/AKK') {
                                    console.log("tampil jenis sample")
                                    $('#jenis_sample_uji_usap').css('display', 'block');
                                } else {
                                    $('#jenis_sample_uji_usap').css('display', 'none');
                                }

                                // $("#test").val(data);

                                // console.log(data);
                                if (data.includes("Fisika")) {
                                    let parsed_sample_code = $('#input_code_sample_kimia')
                                        .val();
                                    let result_fisika = parsed_sample_code.replace("- K",
                                        "- F");
                                    console.log(result_fisika);

                                    $('#input_code_sample_kimia').val(result_fisika);

                                } else {
                                    let parsed_sample_code = $('#input_code_sample_kimia')
                                        .val();
                                    let result_fisika = parsed_sample_code.replace("- F",
                                        "- K");
                                    console.log(result_fisika);

                                    $('#input_code_sample_kimia').val(result_fisika);

                                }
                                var url =
                                    "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                                url = url.replace('#', packet);


                                $('#ispacket').val("true")

                                $.ajax({
                                    url: url,
                                    type: "GET",
                                    datatype: 'json',
                                    success: function(response) {


                                        methods_sample_type = response.methods;


                                        $(".checkbox").prop("checked", false);
                                        var harga = 0;

                                        // Validasi response.data sebelum forEach
                                        if (response.data && Array.isArray(response
                                                .data)) {
                                            response.data.forEach(data => {
                                                harga = harga + parseInt(
                                                    data[
                                                        'price_total_method'
                                                    ]);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "checked", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "readonly", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "disabled", false);

                                                let current_lab_id = $(
                                                        ".checkbox-" + data[
                                                            'method_id'])
                                                    .data(
                                                        'idlabs');
                                                if (!!lab_id && lab_id !=
                                                    current_lab_id) {
                                                    is_multiple_labs = true;
                                                }
                                                lab_id = current_lab_id;
                                            });
                                        }

                                        multipleLabs();

                                        if (response['price'] == 0) {
                                            $('#cost_samples').val(harga)
                                        } else {
                                            $('#cost_samples').val(response[
                                                'price'])

                                            price_sample_type = response[
                                                'price'];
                                        }


                                        //   var url = "{{ route('elits-packet.index') }}";
                                        //   window.location.href = url;

                                    },
                                    error: function(XMLHttpRequest, textStatus,
                                        errorThrown) {
                                        alert(XMLHttpRequest.responseJSON
                                            .message);
                                    }
                                });
                            })
                        },
                    })
                }
            } else {
                // Paket always visible in auto-detect mode
                methods_sample_type = [];
                price_sample_type = 0;
                $(".checkbox").prop("checked", false);
                $('#cost_samples').val(0);
            }

        })

        function ajax_getNewSampleNumberSequence(lab_key, id_permohonan_uji, is_makanan = false) {
            let url = "{{ route('elits-samples.getNewNumberSequence', '#') }}";
            url = url.replace('#', lab_key);
            url = url + "/#";
            url = url.replace('#', id_permohonan_uji);
            url = url + "/#";
            url = url.replace('#', is_makanan);


            $.ajax({
                url: url,
                type: "GET",
                datatype: 'json',
                success: function(response) {
                    // console.log(response)
                    return response;
                    // $('#code_sample').val(response)
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseJSON.message);
                }
            })
        }

        function checkMultipleLabs() {
            let checkbockCheckeds = $(".checkbox:checked");
            lab_keys = [];

            checkbockCheckeds.each((index, element) => {
                let current_lab_id = $(element).data('idlabs');

                if (!!lab_id && lab_id != current_lab_id) {
                    is_multiple_labs = true;
                }

                lab_id = current_lab_id;
                lab_keys = [...lab_keys, lab_id];
            })

            lab_keys = [...new Set(lab_keys)];
            console.log(lab_keys);
        }

        function multipleLabs() {

            console.log("cek");
            return;
            if (lab_keys.length > 1 && $('#code_sample').prop('disabled')) {


                select_multiple_codes = true;

                // Get the lab num sequence each lab keys.
                // todo: make this more efficient
                $code_sample_type = $('#jenis_sample').children(":selected").data('code');

                console.log($code_sample_type);

                for (let lab_key of lab_keys) {

                    let lab_sequence = ajax_getNewSampleNumberSequence(lab_key, '{{ $id }}');
                    lab_keys_sequences[lab_key] = lab_sequence;
                }

                console.log(lab_sequence);

                // Disable and hide the original input element
                $('#code_sample').prop('disabled', true);
                $('#code_sample_form_group').hide();

                // IMPORTANT: Do NOT disable code sample inputs - they need to be submitted
                // Disabled inputs are NOT serialized in form submission
                // $('#input_code_sample_kimia').prop('disabled', true);
                // $('#input_code_sample_mikro').prop('disabled', true);

                // Use readonly instead if needed to prevent editing
                $('#input_code_sample_kimia').prop('readonly', true);
                $('#input_code_sample_mikro').prop('readonly', true);

                // Clone the form group twice
                var clone1 = $('#code_sample_form_group').clone(true, true);
                var clone2 = $('#code_sample_form_group').clone(true, true);

                // Get the sample code
                $code_sample_type = $('#jenis_sample').children(":selected").data('code');
                let parsed_sample_code = $('input#code_sample').val().split('/');

                // Modify the first clone - Kimia (lab code: 01)
                let kimia_parsed_sample_code = [...parsed_sample_code];
                kimia_parsed_sample_code[0] += '.01'; // Use .01 for Kimia
                let kimia_sample_code = kimia_parsed_sample_code.join('/');
                clone1.find('label').text('Kode Sample Kimia:');
                clone1.find('input').prop('disabled', false)
                    .attr('id', 'code_sample_kimia')
                    .attr('name', 'code_sample_kimia')
                    .val(kimia_sample_code)
                clone1.show(); // Ensure it's visible

                // Modify the second clone - Mikrobiologi (lab code: 02)
                let mikrobiologi_parsed_sample_code = [...parsed_sample_code];
                mikrobiologi_parsed_sample_code[0] += '.02'; // Use .02 for Mikro
                let mikrobiologi_sample_code = mikrobiologi_parsed_sample_code.join('/');
                clone2.find('label').text('Kode Sample Mikrobiologi:');
                clone2.find('input').prop('disabled', false)
                    .attr('id', 'code_sample_mikrobiologi')
                    .attr('name', 'code_sample_mikro') // Changed to 'code_sample_mikro' to match controller
                    .val(mikrobiologi_sample_code)
                clone2.show(); // Ensure it's visible


                // Append the clones to the form
                $('#code_sample_form_group').after(clone1);
                clone1.after(clone2);
            } else {
                console.log("cek2");
                select_multiple_codes = false;

                $('#code_sample').prop('disabled', false);
                $('#code_sample_form_group').show();
                $('#code_sample_form_group').nextAll().remove();

                // Re-enable original code sample inputs when not using clones
                $('#input_code_sample_kimia').prop('disabled', false);
                $('#input_code_sample_mikro').prop('disabled', false);
            }
        }

        function pad(n) {
            var s = "000" + n;
            return s.substr(s.length - 4);
        }

        /**
         * Update visibility of code sample inputs based on selected parameters
         * Shows only relevant inputs (Kimia/Mikro) based on lab type
         */
        function updateCodeSampleVisibility() {
            console.log('=== Updating Code Sample Visibility ===');

            // Get all checked parameters
            const checkedParams = $('input[name="method[]"]:checked');

            // Arrays to store lab types
            let hasKimia = false;
            let hasMikro = false;

            // Check each selected parameter for its lab type
            checkedParams.each(function() {
                const labType = $(this).data('lab-type');
                const labName = $(this).data('lab-name');

                console.log('Parameter:', $(this).val(), 'Lab Type:', labType, 'Lab Name:', labName);

                // Detect lab type from data attributes or lab name
                if (labType === 'kimia' || labType === '1' ||
                    (labName && (labName.toLowerCase().includes('kimia') || labName.toLowerCase().includes(
                        'fisika')))) {
                    hasKimia = true;
                } else if (labType === 'mikrobiologi' || labType === '2' ||
                    (labName && labName.toLowerCase().includes('mikro'))) {
                    hasMikro = true;
                }
            });

            // If no parameters selected, check if packet is selected
            if (checkedParams.length === 0) {
                const selectedPacketId = $('#packet').val();
                if (selectedPacketId) {
                    // If packet selected, check packet's parameters
                    const packetOption = $('#packet option:selected');
                    const packetLabs = packetOption.data('labs'); // Assuming packet has labs data

                    console.log('Packet selected:', selectedPacketId, 'Labs:', packetLabs);

                    // You can add logic here to detect labs from packet if needed
                    // For now, show both if packet is selected
                    hasKimia = true;
                    hasMikro = true;
                }
            }

            // If still no selection, check existing values to determine visibility
            if (!hasKimia && !hasMikro) {
                const kimiaVal = $('#input_code_sample_kimia').val();
                const mikroVal = $('#input_code_sample_mikro').val();

                if (kimiaVal && kimiaVal.trim() !== '' && kimiaVal !== '-') {
                    hasKimia = true;
                }
                if (mikroVal && mikroVal.trim() !== '' && mikroVal !== '-') {
                    hasMikro = true;
                }

                console.log('Detected from existing values - Kimia:', hasKimia, 'Mikro:', hasMikro);
            }

            // Show/hide inputs based on detection
            // CRITICAL: NEVER disable inputs - just hide/show the wrapper
            if (hasKimia) {
                $('#code_sample_kimia_wrapper_top').slideDown(300);
                $('#input_code_sample_kimia').prop('disabled', false).prop('readonly', false);
                console.log('✓ Kimia input: SHOWN and EDITABLE');
            } else {
                $('#code_sample_kimia_wrapper_top').slideUp(300);
                // CRITICAL: Keep input enabled even when hidden so it submits
                $('#input_code_sample_kimia').prop('disabled', false);
                console.log('✗ Kimia input: HIDDEN (but ENABLED for submit)');
            }

            if (hasMikro) {
                $('#code_sample_mikro_wrapper_top').slideDown(300);
                $('#input_code_sample_mikro').prop('disabled', false).prop('readonly', false);
                console.log('✓ Mikro input: SHOWN and EDITABLE');
            } else {
                $('#code_sample_mikro_wrapper_top').slideUp(300);
                // CRITICAL: Keep input enabled even when hidden so it submits
                $('#input_code_sample_mikro').prop('disabled', false);
                console.log('✗ Mikro input: HIDDEN (but ENABLED for submit)');
            }

            console.log('Summary - Kimia:', hasKimia ? 'YES' : 'NO', '| Mikro:', hasMikro ? 'YES' : 'NO');
            console.log('======================================');
        }

        tinymce.init({
            selector: 'textarea#titik_pengambilan',
            height: 50,
            menubar: false,
            plugins: [
                'advlist autolink autosave lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
            ],
            toolbar: 'undo redo | bold italic | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('change blur', function() {
                    tinymce.triggerSave();
                });
            }
        });
        tinymce.init({
            selector: 'textarea#address_location_pdam',
            height: 50,
            menubar: false,
            plugins: [
                'advlist autolink autosave lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
            ],
            toolbar: 'undo redo | bold italic | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('change blur', function() {
                    tinymce.triggerSave();
                });
            }
        });



        $("#is_pudam").change(async function() {
            // console.log($(this).val());

            if ($(this).prop("checked")) {

                console.log("yes");
                $('#titik_pengambilan_text').attr('hidden', true);
                $('#pdam').attr('hidden', false);
            } else {
                console.log("no");
                $('#titik_pengambilan_text').attr('hidden', false);
                $('#pdam').attr('hidden', true);
            }

        });

        $("#jenis_sampel").change(async function() {

            jenis_sample = $(this).val();
            var jenis_sample_text = $(this).children(":selected").text();

            let codes = ['input_code_sample_kimia', 'input_code_sample_mikro'];


            // Format: {code_sample_type}.{lab_code}/{number}/{year}
            let codesConfig = [{
                    input: 'input_code_sample_kimia',
                    labCode: '01'
                },
                {
                    input: 'input_code_sample_mikro',
                    labCode: '02'
                }
            ];

            for (let i = 0; i < codes.length; i++) {
                let code = codes[i];
                let inputCodeSampleElement = $('#' + code);
                var code_sample_type = $(this).children(":selected").data('code') /* .toUpperCase() */ || '...';
                let currentValue = $(inputCodeSampleElement).val() || '';

                // Determine lab code
                let labCode = code === 'input_code_sample_mikro' ? '02' : '01';

                // Split by '/' to get parts: ["{code}.{lab}", "number", "year"]
                let parsed_sample_code = currentValue.split('/');

                if (parsed_sample_code.length >= 3) {
                    // Split first part by '.' to get ONLY 2 parts: [code, lab]
                    // Use limit parameter to prevent multiple splits
                    let firstPart = parsed_sample_code[0].split('.', 2);

                    // If firstPart doesn't have 2 elements, initialize them
                    if (firstPart.length === 0) {
                        firstPart = [code_sample_type, labCode];
                    } else if (firstPart.length === 1) {
                        firstPart = [code_sample_type, labCode];
                    } else {
                        // Update both parts
                        firstPart[0] = code_sample_type;
                        firstPart[1] = labCode;
                    }

                    // Reconstruct first part with only 2 elements
                    parsed_sample_code[0] = firstPart[0] + '.' + firstPart[1];
                }

                if (jenis_sample_text.includes("Makanan/Minuman/Lainnya")) {
                    let url = "{{ route('elits-samples.getNewNumberSequence', '#') }}";
                    url = url.replace('#', "d3bff0b4-622e-40b0-b10f-efa97a4e1bd5");
                    url = url + "/#";
                    url = url.replace('#', '{{ $id }}');
                    url = url + "/#";
                    url = url.replace('#', true);
                    $.ajax({
                        url: url,
                        type: "GET",
                        datatype: 'json',
                        success: function(response) {
                            // Update the number part (index 1)
                            parsed_sample_code[1] = pad(parseInt(response) + 1);
                            $(inputCodeSampleElement).val(parsed_sample_code.join('/'));
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseJSON.message);
                        }
                    })
                } else {
                    // Just update the code
                    $(inputCodeSampleElement).val(parsed_sample_code.join('/'));
                }
            }

            if (jenis_sample_text.includes("Rectal Swab (Jasa Boga)") || jenis_sample ==
                "ab516530-aed0-481b-ab9c-86c8ccbcabb3" || jenis_sample_text.includes("Rectal Swab")) {
                $('.is_rectal_swab').show();
            } else {
                $('.is_rectal_swab').hide();
            }

            // if (jenis_sample_text.includes("Uji Usap")) {
            //     $(".jenis_sarana_id").css("display", "block")
            // } else {
            //     $(".jenis_sarana_id").css("display", "none")
            // }

            $(".jenis_makanan_id").css("display", "none")
            methods_sample_type = [];
            price_sample_type = 0;
            $(".checkbox").prop("checked", false);
            $(".checkbox").prop("disabled", false);
            $(".checkbox").attr("data-toggle", "tooltip");
            $(".checkbox").attr("data-placement", "right");
            $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
            $("[data-toggle='tooltip']").tooltip();
            $('#cost_samples').val(0);

            $(".checkbox").prop("readonly", false);

            if (jenis_sample != undefined) {

                var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
                url = url.replace('#', jenis_sample);


                $('#ispacket').val("true")

                $.ajax({
                    url: url,
                    type: "GET",
                    datatype: 'json',
                    success: function(response) {
                        var results = response.data;
                        results.forEach(result => {
                            $(".checkbox-" + result.id_method).prop("disabled", false);
                            $(".checkbox-" + result.id_method).removeAttr("title")
                            $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                            $(".checkbox-" + result.id_method).removeAttr("data-placement");
                            $(".checkbox-" + result.id_method).removeAttr(
                                "data-original-title");

                        })
                    },
                })

                var url = "/api/packet/#"
                url = url.replace('#', jenis_sample);


                $.ajax({
                    url: url,
                    type: "POST",
                    datatype: 'json',
                    success: function(response) {
                        $(".is_paket").css('display', 'block');

                        // Clear existing paket buttons and options
                        $('.packet-buttons-container').empty();
                        $('#packet').find('option').remove().end();

                        var results = response.results;

                        // Show packet section if there are available packets
                        if (results && results.length > 0) {
                            $('.packet').show();

                            // Dynamically create paket buttons
                            results.forEach(result => {
                                // Add button
                                $('.packet-buttons-container').append(
                                    '<button type="button" class="btn btn-outline-success btn-sm btn-pick-paket" data-id="' +
                                    result.id + '">' +
                                    result.text + '</button>'
                                );

                                // Add option to hidden select
                                $('#packet').append('<option value="' + result.id +
                                    '" data-extra="' + result.data_extra + '">' + result
                                    .text + '</option>');
                            });
                        } else {
                            $('.packet').hide();
                        }

                        $("#is_paket").prop('checked', false);

                        $("#packet").change(function() {
                            var packet = $(this).val();
                            var url =
                                "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                            url = url.replace('#', packet);


                            $('#ispacket').val("true")

                            $.ajax({
                                url: url,
                                type: "GET",
                                datatype: 'json',
                                success: function(response) {


                                    methods_sample_type = response.methods;
                                    $(".checkbox").prop("checked", false);
                                    var harga = 0;

                                    // Reset packet parameter tracking
                                    window.packetParameterIds = [];

                                    // Validasi response.data sebelum forEach
                                    if (response.data && Array.isArray(response
                                            .data)) {
                                        response.data.forEach(data => {
                                            harga = harga + parseInt(
                                                data[
                                                    'price_total_method'
                                                ]);
                                            $(".checkbox-" + data[
                                                'method_id']).prop(
                                                "checked", true);
                                            $(".checkbox-" + data[
                                                'method_id']).prop(
                                                "readonly", true);

                                            // Track parameter from packet
                                            window.packetParameterIds
                                                .push(data[
                                                    'method_id']);
                                        });
                                    }

                                    if (response['price'] == 0) {
                                        $('#cost_samples').val(harga)
                                        window.packetPrice = harga;
                                    } else {
                                        $('#cost_samples').val(response[
                                            'price'])

                                        price_sample_type = response['price'];
                                        window.packetPrice = response['price'];
                                    }

                                    // Trigger collapse & auto-sort update after checkboxes loaded
                                    if (typeof window.updateParameterCounts ===
                                        'function') {
                                        setTimeout(function() {
                                            window
                                                .updateParameterCounts();
                                            window
                                                .moveCheckedParametersToTop();
                                        }, 300);
                                    }


                                    //   var url = "{{ route('elits-packet.index') }}";
                                    //   window.location.href = url;

                                },
                                error: function(XMLHttpRequest, textStatus,
                                    errorThrown) {
                                    alert(XMLHttpRequest.responseJSON.message);
                                }
                            });

                        })
                    },
                })


            }
        });

        $(document).ready(function() {
            // Function to filter parameters based on selected jenis sampel
            function filterParametersByJenisSampel(sampletypeId) {
                // Uncheck all checkboxes when jenis sampel changes
                $('.method-row .checkbox').prop('checked', false);

                // Reset harga total
                $('#cost_samples').val('0');

                // Deselect all paket buttons
                $('.btn-pick-paket').removeClass('active');
                $('#packet option').prop('selected', false);

                // Remove old show more buttons
                $('.show-more-btn').remove();

                if (!sampletypeId) {
                    // No jenis sampel selected, disable all
                    $('.method-row .checkbox').prop('disabled', true);
                    // Apply show more to reset view
                    $('.parameter-group').each(function() {
                        applyShowMoreLogic($(this));
                    });
                    return;
                }

                // Enable/disable checkboxes based on baku mutu
                $('.method-row').each(function() {
                    const bakuMutuSampletypes = $(this).data('baku-mutu-sampletypes');
                    const checkbox = $(this).find('.checkbox');

                    // Check if this parameter has baku mutu for the selected jenis sampel
                    if (bakuMutuSampletypes && Array.isArray(bakuMutuSampletypes) &&
                        bakuMutuSampletypes.includes(parseInt(sampletypeId))) {
                        // Has baku mutu - enable checkbox
                        checkbox.prop('disabled', false);
                    } else {
                        // No baku mutu - disable checkbox
                        checkbox.prop('disabled', true);
                    }
                });

                // Apply show more logic to each group IMMEDIATELY
                $('.parameter-group').each(function() {
                    const $group = $(this);
                    const maxVisible = 20;
                    const $rows = $group.find('.method-row');

                    // Remove old state
                    $rows.removeClass('hidden-row');

                    // Apply new visibility
                    $rows.each(function(index) {
                        if (index >= maxVisible) {
                            $(this).addClass('hidden-row').hide();
                        } else {
                            $(this).show();
                        }
                    });

                    // Count hidden and add button
                    const hiddenCount = $rows.filter('.hidden-row').length;
                    if (hiddenCount > 0) {
                        const $showMoreBtn = $(
                            '<div class="show-more-btn" style="text-align: center; padding: 10px; cursor: pointer; color: #007bff; font-weight: bold;">' +
                            '<i class="fas fa-chevron-down"></i> Tampilkan ' + hiddenCount +
                            ' parameter lainnya' +
                            '</div>');

                        $showMoreBtn.on('click', function() {
                            $group.find('.method-row.hidden-row').removeClass('hidden-row').show();
                            $(this).remove();
                        });

                        $group.find('.collapse').append($showMoreBtn);
                    }
                });

                // Update counts
                if (typeof window.updateParameterCounts === 'function') {
                    window.updateParameterCounts();
                }
            }

            // Jenis sampel as cashier-like pick buttons
            $(document).on('click', '.btn-pick-jenis', function() {
                $('.btn-pick-jenis').removeClass('active');
                $(this).addClass('active');
                const id = String($(this).attr('data-id') || '').trim();
                const code = $(this).attr('data-code') || '';

                $('#jenis_sampel').val(id).trigger('change');

                // Filter parameters based on selected jenis sampel
                filterParametersByJenisSampel(id);

                // Update code samples when jenis changes
                // Format: {code_sample_type}.{lab_code}/{number}/{year}
                // Example: AM.01/0013/2025 (Kimia) or AM.02/0014/2025 (Mikro)
                if (code) {
                    // Update Kimia code
                    let kimiaInput = $('#input_code_sample_kimia');
                    let kimiaValue = kimiaInput.val() || '';
                    if (kimiaValue.includes('/')) {
                        let parts = kimiaValue.split('/');
                        if (parts.length >= 3) {
                            kimiaInput.val(code + '.01/' + parts[1] + '/' + parts[2]);
                        }
                    }

                    // Update Mikro code
                    let mikroInput = $('#input_code_sample_mikro');
                    let mikroValue = mikroInput.val() || '';
                    if (mikroValue.includes('/')) {
                        let parts = mikroValue.split('/');
                        if (parts.length >= 3) {
                            mikroInput.val(code + '.02/' + parts[1] + '/' + parts[2]);
                        }
                    }
                }
            });

            // Auto-detect mode: Paket pick buttons toggle selection
            $(document).on('click', '.btn-pick-paket', function() {
                $(this).toggleClass('active');
                const id = String($(this).attr('data-id') || '').trim();
                const select = $('#packet');
                const option = select.find('option[value="' + id + '"]');
                option.prop('selected', $(this).hasClass('active'));

                // Auto set to paket mode when any paket is clicked
                $('#is_paket').val('true');

                // Trigger change event to load paket parameters via AJAX
                select.trigger('change');
            });

            // Track which parameters are from packet (for distinguishing in cart)
            window.packetParameterIds = [];

            // Auto-detect mode: When parameter checkbox is clicked manually (not from paket)
            $(document).on('change', '.checkbox', function() {
                // Don't switch to satuan mode automatically
                // Allow adding extra parameters when packet is selected
                // Update cart to show the difference
            });
            // $.fn.select2.defaults.set("theme", "classic");
            // $('#jenis_sampel').select2();
        })

        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy'
        });

        $('.datepicker').datepicker('update', new Date());

        $('.datelab_samples').datepicker({
            format: 'dd/mm/yyyy'
        });

        $('.datelab_samples').datepicker('update', new Date());
        $('.datesampling_samples').datepicker('update', new Date());

        $(document).ready(function() {
            // CRITICAL: Ensure code sample inputs are enabled and EDITABLE on page load
            console.log('=== Initializing Code Sample Inputs ===');
            $('#input_code_sample_kimia').prop('disabled', false).prop('readonly', false);
            $('#input_code_sample_mikro').prop('disabled', false).prop('readonly', false);

            // Log initial values
            console.log('Initial Kimia Value:', $('#input_code_sample_kimia').val());
            console.log('Initial Mikro Value:', $('#input_code_sample_mikro').val());
            console.log('Kimia Backup:', $('#code_sample_kimia_backup').val());
            console.log('Mikro Backup:', $('#code_sample_mikro_backup').val());

            // Initialize visibility based on existing parameters
            updateCodeSampleVisibility();
            console.log('=======================================');

            // Watch for parameter changes to update visibility
            $(document).on('change', 'input[name="method[]"]', function() {
                console.log('Parameter changed, updating visibility...');
                setTimeout(updateCodeSampleVisibility, 300);
            });

            // Watch for packet selection changes
            $('#packet').on('change', function() {
                console.log('Packet changed, updating visibility...');
                setTimeout(updateCodeSampleVisibility, 300);
            });

            // Watch for packet button clicks
            $(document).on('click', '.btn-pick-paket', function() {
                console.log('Packet button clicked, updating visibility...');
                setTimeout(updateCodeSampleVisibility, 500);
            });

            // CRITICAL: Protect code sample values from being cleared
            // Store original values on page load
            window.originalKimiaCode = $('#input_code_sample_kimia').val();
            window.originalMikroCode = $('#input_code_sample_mikro').val();

            console.log('Original codes stored - Kimia:', window.originalKimiaCode, '| Mikro:', window
                .originalMikroCode);

            // CRITICAL FIX: Initialize code_sample_master with current value
            const initKimia = window.originalKimiaCode;
            const initMikro = window.originalMikroCode;
            let initMaster = '';
            if (initMikro && initMikro.trim() !== '' && initMikro !== '-') {
                initMaster = initMikro;
            } else if (initKimia && initKimia.trim() !== '' && initKimia !== '-') {
                initMaster = initKimia;
            }
            $('#code_sample_master').val(initMaster);
            console.log('Master code initialized:', initMaster);

            // Watch for value changes and restore if cleared unexpectedly
            $('#input_code_sample_kimia').on('change blur', function() {
                const currentVal = $(this).val();
                if ((!currentVal || currentVal.trim() === '') && window.originalKimiaCode && window
                    .originalKimiaCode !== '-') {
                    console.warn('⚠ Kimia code was cleared! Restoring original:', window.originalKimiaCode);
                    $(this).val(window.originalKimiaCode);
                }
            });

            $('#input_code_sample_mikro').on('change blur', function() {
                const currentVal = $(this).val();
                if ((!currentVal || currentVal.trim() === '') && window.originalMikroCode && window
                    .originalMikroCode !== '-') {
                    console.warn('⚠ Mikro code was cleared! Restoring original:', window.originalMikroCode);
                    $(this).val(window.originalMikroCode);
                }
            });

            // $.fn.select2.defaults.set("theme", "classic");

            // $('#unitAttributes').select2({
            //     placeholder: "Pilih Unit",
            //     allowClear: true
            // });

            // $('.js-unit-basic-multiple').select2({
            //     placeholder: "Pilih Unit",
            //     allowClear: true,
            //     ajax: {
            //         url: "{{ url('/api/unit/') }}",
            //         method: "post",
            //         dataType: 'json',
            //         params: { // extra parameters that will be passed to ajax
            //             contentType: "application/json;",
            //         },
            //         data: function(term) {
            //             return {
            //                 term: term.term || '',
            //                 page: term.page || 1
            //             };
            //         },
            //         cache: true
            //     }
            // });

            var element2 = document.getElementById('pengawet_others');


            $('input[type=radio][name=pengawet]').change(function() {

                if (this.value == '0') {
                    element2.style.display = 'block';
                } else {
                    element2.style.display = 'none';
                }

            });

            var CSRF_TOKEN = $('#csrf-token').val();

            $("#form-create-sample").validate({
                // in 'rules' user have to specify all the constraints for respective fields
                rules: {
                    jenis_sampel: "required",
                    cost_samples: "required",
                    program_samples: "required",

                },
                // in 'messages' user have to specify message as per rules
                messages: {
                    jenis_sampel: " Masukan Jenis Sample",
                    cost_samples: " Masukan harga",
                    program_samples: " Masukkan Program",
                },
                submitHandler: function(form) {
                    $('.btn-simpan').prop("disabled", true);

                    // CRITICAL FIX: Re-enable code sample inputs before serialization
                    // Disabled inputs are NOT included in serialize()
                    $('#input_code_sample_kimia').prop('disabled', false);
                    $('#input_code_sample_mikro').prop('disabled', false);

                    // Also ensure they are not readonly (if needed)
                    $('#input_code_sample_kimia').prop('readonly', false);
                    $('#input_code_sample_mikro').prop('readonly', false);

                    // CRITICAL: Sync backup values if main inputs are empty
                    let kimiaVal = $('#input_code_sample_kimia').val();
                    let mikroVal = $('#input_code_sample_mikro').val();
                    const kimiaBackup = $('#code_sample_kimia_backup').val();
                    const mikroBackup = $('#code_sample_mikro_backup').val();

                    console.log('Before restore - Kimia:', kimiaVal, '| Mikro:', mikroVal);
                    console.log('Backup values - Kimia:', kimiaBackup, '| Mikro:', mikroBackup);

                    // CRITICAL FIX: If main input is empty/whitespace but backup has value, restore from backup
                    if ((!kimiaVal || kimiaVal.trim() === '' || kimiaVal === '-') && kimiaBackup &&
                        kimiaBackup !== '-') {
                        $('#input_code_sample_kimia').val(kimiaBackup);
                        kimiaVal = kimiaBackup;
                        console.log('✓ Restored Kimia from backup:', kimiaBackup);
                    }
                    if ((!mikroVal || mikroVal.trim() === '' || mikroVal === '-') && mikroBackup &&
                        mikroBackup !== '-') {
                        $('#input_code_sample_mikro').val(mikroBackup);
                        mikroVal = mikroBackup;
                        console.log('✓ Restored Mikro from backup:', mikroBackup);
                    }

                    // Update backup values with current values (for next submit)
                    if (kimiaVal && kimiaVal.trim() !== '' && kimiaVal !== '-') {
                        $('#code_sample_kimia_backup').val(kimiaVal);
                    }
                    if (mikroVal && mikroVal.trim() !== '' && mikroVal !== '-') {
                        $('#code_sample_mikro_backup').val(mikroVal);
                    }

                    console.log('=== Code Sample Debug ===');
                    console.log('Code Sample Kimia:', $('#input_code_sample_kimia').val());
                    console.log('Code Sample Mikro:', $('#input_code_sample_mikro').val());
                    console.log('Backup Kimia:', $('#code_sample_kimia_backup').val());
                    console.log('Backup Mikro:', $('#code_sample_mikro_backup').val());

                    // FINAL CHECK: Ensure at least one code sample has value
                    const finalKimiaVal = $('#input_code_sample_kimia').val();
                    const finalMikroVal = $('#input_code_sample_mikro').val();

                    if ((!finalKimiaVal || finalKimiaVal === '-') && (!finalMikroVal ||
                            finalMikroVal === '-')) {
                        console.error('❌ ERROR: Both code samples are empty!');

                        // Try to restore from original values
                        if (window.originalKimiaCode && window.originalKimiaCode !== '-') {
                            $('#input_code_sample_kimia').val(window.originalKimiaCode);
                            console.log('✓ Restored Kimia from window.original');
                        }
                        if (window.originalMikroCode && window.originalMikroCode !== '-') {
                            $('#input_code_sample_mikro').val(window.originalMikroCode);
                            console.log('✓ Restored Mikro from window.original');
                        }
                    }

                    console.log('Final values before submit:');
                    console.log('  → Kimia:', $('#input_code_sample_kimia').val());
                    console.log('  → Mikro:', $('#input_code_sample_mikro').val());

                    // CRITICAL FIX: Sync code_sample_master for controller
                    // Controller expects 'code_sample' field (singular)
                    const finalKimia = $('#input_code_sample_kimia').val();
                    const finalMikro = $('#input_code_sample_mikro').val();

                    // Priority: Mikro > Kimia (if both exist, use mikro as primary)
                    // Or use whichever has value
                    let masterCodeSample = '';
                    if (finalMikro && finalMikro.trim() !== '' && finalMikro !== '-') {
                        masterCodeSample = finalMikro;
                    } else if (finalKimia && finalKimia.trim() !== '' && finalKimia !== '-') {
                        masterCodeSample = finalKimia;
                    }

                    $('#code_sample_master').val(masterCodeSample);

                    console.log('  → MASTER (for controller):', masterCodeSample);
                    console.log('========================');

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        success: function(response) {
                            if (response.status == true) {


                                swal({
                                        title: "Success!",
                                        text: response.pesan,
                                        icon: "success"
                                    })
                                    .then(function() {
                                        document.location = response.url_redirect;
                                    });
                            } else {
                                var pesan = "";
                                var data_pesan = response.pesan;
                                const wrapper = document.createElement('div');

                                $('.btn-simpan').prop("disabled", false);

                                if (typeof(data_pesan) == 'object') {
                                    jQuery.each(data_pesan, function(key, value) {
                                        pesan += value + '. <br>';
                                        wrapper.innerHTML = pesan;
                                    });

                                    swal({
                                        title: "Error!",
                                        content: wrapper,
                                        icon: "warning"
                                    });
                                } else {
                                    swal({
                                        title: "Error!",
                                        text: response.pesan,
                                        icon: "warning"
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            $('.btn-simpan').prop("disabled", false);

                            var err = eval("(" + xhr.responseText + ")");
                            swal("Error!", err.Message, "error");
                        }
                    })
                }
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            function toggleJenisMakanan() {
                var selectedValue = $('#jenis_sampel').val();
                if (selectedValue === "d34b4a50-4560-4fce-96c3-046c7080a986") {
                    $('#form_jenis_makanan').show();
                    $('#jenis_makanan_minuman').val('');
                } else {
                    $('#form_jenis_makanan').hide();
                    $('#jenis_makanan_minuman').val('');
                }
            }
            $('#jenis_sampel').on('change', toggleJenisMakanan);
            toggleJenisMakanan();
        });

        // Parameter Search and Pagination
        (function() {
            let currentPage = 1;
            let itemsPerPage = 20;
            let allGroups = [];
            let filteredGroups = [];

            function initializeParameterSearch() {
                // Get all parameter groups
                allGroups = Array.from(document.querySelectorAll('.parameter-group'));
                filteredGroups = [...allGroups];

                // Initialize pagination
                updatePagination();

                // Search functionality
                $('#search-parameter').on('keyup', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    filterParameters(searchTerm);
                });

                // Items per page change
                $('#items-per-page').on('change', function() {
                    const value = $(this).val();
                    itemsPerPage = value === 'all' ? filteredGroups.length : parseInt(value);
                    currentPage = 1;
                    updatePagination();
                });
            }

            function filterParameters(searchTerm) {
                if (!searchTerm) {
                    filteredGroups = [...allGroups];
                } else {
                    filteredGroups = allGroups.filter(group => {
                        const category = group.getAttribute('data-category').toLowerCase();
                        const methods = Array.from(group.querySelectorAll('.method-row'));

                        // Check if category matches
                        if (category.includes(searchTerm)) {
                            return true;
                        }

                        // Check if any method name matches
                        const hasMatchingMethod = methods.some(method => {
                            const methodName = method.getAttribute('data-method-name');
                            return methodName && methodName.includes(searchTerm);
                        });

                        if (hasMatchingMethod) {
                            // Show only matching methods within this group
                            methods.forEach(method => {
                                const methodName = method.getAttribute('data-method-name');
                                if (methodName && methodName.includes(searchTerm)) {
                                    $(method).show();
                                } else {
                                    $(method).hide();
                                }
                            });
                            return true;
                        }

                        return false;
                    });
                }

                // Reset to first page after filter
                currentPage = 1;
                updatePagination();
            }

            function updatePagination() {
                const totalGroups = filteredGroups.length;
                const totalPages = Math.ceil(totalGroups / itemsPerPage);

                // Hide all groups first
                allGroups.forEach(group => $(group).hide());

                // Show current page groups
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalGroups);

                for (let i = startIndex; i < endIndex; i++) {
                    const group = filteredGroups[i];
                    $(group).show();

                    // Show all methods in the group if no search term
                    if (!$('#search-parameter').val()) {
                        $(group).find('.method-row').show();
                    }
                }

                // Update showing info
                if (totalGroups === 0) {
                    $('#showing-info').text('Tidak ada parameter ditemukan');
                } else {
                    $('#showing-info').text(
                        `Menampilkan ${startIndex + 1}-${endIndex} dari ${totalGroups} kategori parameter`);
                }

                // Render pagination buttons
                renderPaginationButtons(totalPages);
            }

            function renderPaginationButtons(totalPages) {
                const $pagination = $('#pagination');
                $pagination.empty();

                if (totalPages <= 1) {
                    return;
                }

                // Previous button
                $pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    $pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `);
                    if (startPage > 2) {
                        $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    $pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                    $pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                        </li>
                    `);
                }

                // Next button
                $pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                `);

                // Add click handlers
                $pagination.find('a.page-link').on('click', function(e) {
                    e.preventDefault();
                    if (!$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                        currentPage = parseInt($(this).data('page'));
                        updatePagination();
                        // Scroll to top of parameters
                        $('html, body').animate({
                            scrollTop: $('#parameters-container').offset().top - 100
                        }, 300);
                    }
                });
            }

            // Initialize on document ready
            $(document).ready(function() {
                initializeParameterSearch();
            });
        })();

        // Parameter Collapse & Auto-Sort Functionality
        $(document).ready(function() {

            // Collapse ALL by default - semua tertutup saat load
            $('.collapse').removeClass('show');

            // Auto-expand first 3 groups untuk menampilkan show more buttons
            $('.parameter-group').slice(0, 3).each(function() {
                $(this).find('.collapse').addClass('show');
                $(this).find('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            });

            // Rotate icon on collapse/expand
            $('.parameter-group-header').on('click', function() {
                const icon = $(this).find('.collapse-icon');
                const $group = $(this).closest('.parameter-group');
                setTimeout(function() {
                    if (icon.closest('.parameter-group-header').attr('aria-expanded') === 'true') {
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        // Apply show more when expanded
                        applyShowMoreLogic($group);
                    } else {
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                }, 100);
            });

            // Expand all button
            $('#expand-all-params').on('click', function() {
                $('.collapse').collapse('show');
                $('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                // Apply show more to all groups
                $('.parameter-group').each(function() {
                    applyShowMoreLogic($(this));
                });
            });

            // Collapse all button  
            $('#collapse-all-params').on('click', function() {
                $('.collapse').collapse('hide');
                $('.collapse-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });

            // Update parameter counts in badges (GLOBAL FUNCTION)
            window.updateParameterCounts = function() {
                $('.parameter-group').each(function() {
                    const checkedCount = $(this).find('input[type="checkbox"]:checked').length;
                    $(this).find('.param-count').text(checkedCount);

                    // Change badge color if has checked items
                    if (checkedCount > 0) {
                        $(this).find('.param-count').removeClass('badge-secondary').addClass(
                            'badge-success');
                    } else {
                        $(this).find('.param-count').removeClass('badge-success').addClass(
                            'badge-secondary');
                    }
                });
            }

            // Auto-expand and sort checked parameters to top (GLOBAL FUNCTION)
            window.moveCheckedParametersToTop = function() {
                // Instead of moving to top section, just auto-expand groups with checked items
                let firstCheckedGroup = null;

                $('.parameter-group').each(function() {
                    const $group = $(this);
                    const checkedCount = $group.find('input[type="checkbox"]:checked').length;

                    if (checkedCount > 0) {
                        // Auto-expand this group
                        $group.find('.collapse').addClass('show');
                        $group.find('.collapse-icon').removeClass('fa-chevron-down').addClass(
                            'fa-chevron-up');

                        // Sort: Move checked parameters to top within group
                        const $rows = $group.find('.method-row');
                        const $checkedRows = $rows.filter(function() {
                            return $(this).find('input[type="checkbox"]').is(':checked');
                        });
                        const $uncheckedRows = $rows.filter(function() {
                            return !$(this).find('input[type="checkbox"]').is(':checked');
                        });

                        // Reorder: checked first, then unchecked
                        if ($checkedRows.length > 0) {
                            const $container = $rows.first().parent();
                            $checkedRows.detach().prependTo($container);
                            $uncheckedRows.detach().appendTo($container);
                        }

                        // Apply show more logic: show checked + first 20 items
                        applyShowMoreLogic($group);

                        // Remember first checked group for scrolling
                        if (!firstCheckedGroup) {
                            firstCheckedGroup = $group;
                        }
                    } else {
                        // For groups without checked items, still apply show more
                        applyShowMoreLogic($group);
                    }
                });

                // Scroll to first checked group
                if (firstCheckedGroup) {
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: firstCheckedGroup.offset().top - 150
                        }, 500);
                    }, 100);
                }
            }

            // Show More Logic
            function applyShowMoreLogic($group) {
                const maxVisible = 20;
                const $rows = $group.find('.method-row');
                const $checkedRows = $rows.filter(function() {
                    return $(this).find('input[type="checkbox"]').is(':checked');
                });

                // Hide rows after maxVisible (but keep checked visible)
                $rows.each(function(index) {
                    const $row = $(this);
                    const isChecked = $row.find('input[type="checkbox"]').is(':checked');

                    if (index >= maxVisible && !isChecked) {
                        $row.addClass('hidden-row').hide();
                    } else {
                        $row.removeClass('hidden-row').show();
                    }
                });

                // Count hidden rows
                const hiddenCount = $group.find('.method-row.hidden-row').length;

                // Remove existing show more button
                $group.find('.show-more-btn').remove();

                // Add show more button if needed
                if (hiddenCount > 0) {
                    const $showMoreBtn = $(
                        '<div class="show-more-btn" style="text-align: center; padding: 10px; cursor: pointer; color: #007bff; font-weight: bold;">' +
                        '<i class="fas fa-chevron-down"></i> Tampilkan ' + hiddenCount + ' parameter lainnya' +
                        '</div>');

                    $showMoreBtn.on('click', function() {
                        $group.find('.method-row.hidden-row').removeClass('hidden-row').show();
                        $(this).remove();
                    });

                    $group.find('.collapse').append($showMoreBtn);
                }
            }

            // Listen to checkbox changes
            $(document).on('change', 'input[type="checkbox"]', function() {
                window.updateParameterCounts();
                window.moveCheckedParametersToTop();

                // Auto-expand group that has checked items
                const $group = $(this).closest('.parameter-group');
                const checkedCount = $group.find('input[type="checkbox"]:checked').length;

                if (checkedCount > 0) {
                    $group.find('.collapse').collapse('show');
                    $group.find('.collapse-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }

                // Update cart widget
                updateCartWidget();
            });

            // Format currency helper
            function formatRupiah(amount) {
                return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
            }

            // Update Cart Widget Function
            function updateCartWidget() {
                const $checkedParams = $('.checkbox:checked');
                const $cartList = $('#cart-items-list');
                const $emptyState = $('#cart-empty-state');

                // Check if from packet - get from select option (more accurate)
                const isPacket = $('#is_paket').val() === 'true';
                const $selectedPacketOption = $('#packet option:selected');
                const packetName = $selectedPacketOption.length > 0 ? $selectedPacketOption.text().trim() : '';

                if (isPacket && packetName) {
                    $('#cart-packet-info').show();
                    $('#cart-packet-name').html(`<strong class="text-info">${packetName}</strong>`);
                } else {
                    $('#cart-packet-info').hide();
                }

                // Clear cart list
                $cartList.empty();

                if ($checkedParams.length === 0) {
                    // Show empty state
                    $cartList.html(`
                        <div class="text-center text-muted py-5" id="cart-empty-state">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada parameter dipilih</p>
                            <small>Centang parameter untuk menambahkan</small>
                        </div>
                    `);
                    $('#cart-total-items').text('0');
                    $('#cart-total-price').text('Rp 0');
                    return;
                }

                let totalPrice = 0;
                let cartHTML = '';

                // Separate parameters from packet and additional (satuan)
                let packetParams = [];
                let additionalParams = [];
                let additionalPrice = 0;

                $checkedParams.each(function() {
                    const $checkbox = $(this);
                    const methodName = $checkbox.closest('.method-row').find('label').text().trim();
                    const price = parseInt($checkbox.data('price')) || 0;
                    const methodId = $checkbox.data('idmethod');
                    const categoryName = $checkbox.closest('.parameter-group').find(
                        '.parameter-group-header h5').text().trim();

                    const paramData = {
                        methodId: methodId,
                        methodName: methodName,
                        price: price,
                        categoryName: categoryName
                    };

                    // Check if from packet
                    if (window.packetParameterIds && window.packetParameterIds.includes(methodId)) {
                        packetParams.push(paramData);
                    } else {
                        additionalParams.push(paramData);
                        additionalPrice += price; // Sum only additional params
                    }
                });

                // Calculate total price
                if (packetParams.length > 0 && window.packetPrice) {
                    // Use packet price (not sum of individual)
                    totalPrice = parseInt(window.packetPrice) + additionalPrice;
                } else {
                    // Pure satuan mode (no packet)
                    totalPrice = additionalPrice;
                }

                // Build HTML for packet parameters
                if (packetParams.length > 0) {
                    packetParams.forEach(param => {
                        cartHTML += `
                            <div class="cart-item" data-method-id="${param.methodId}">
                                <button type="button" class="cart-item-remove" data-method-id="${param.methodId}" title="Hapus">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="cart-item-category">
                                    <i class="fas fa-flask"></i> ${param.categoryName}
                                    <span class="cart-packet-badge">Paket</span>
                                </div>
                                <div class="cart-item-name">${param.methodName}</div>
                                <div class="cart-item-price">${formatRupiah(param.price)}</div>
                            </div>
                        `;
                    });
                }

                // Build HTML for additional (satuan) parameters
                if (additionalParams.length > 0) {
                    additionalParams.forEach(param => {
                        cartHTML += `
                            <div class="cart-item" data-method-id="${param.methodId}">
                                <button type="button" class="cart-item-remove" data-method-id="${param.methodId}" title="Hapus">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="cart-item-category">
                                    <i class="fas fa-flask"></i> ${param.categoryName}
                                    <span class="badge badge-warning badge-sm ml-1">Satuan</span>
                                </div>
                                <div class="cart-item-name">${param.methodName}</div>
                                <div class="cart-item-price">${formatRupiah(param.price)}</div>
                            </div>
                        `;
                    });
                }

                $cartList.html(cartHTML);
                $('#cart-total-items').text($checkedParams.length);

                // Update price breakdown if packet exists
                if (packetParams.length > 0 && window.packetPrice) {
                    let breakdownHTML = `
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-box"></i> Harga Paket: ${formatRupiah(window.packetPrice)}
                        </small>
                    `;
                    if (additionalParams.length > 0) {
                        breakdownHTML += `
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-plus"></i> Satuan Tambahan: ${formatRupiah(additionalPrice)}
                            </small>
                        `;
                    }
                    $('#cart-price-breakdown').html(breakdownHTML).show();
                } else {
                    $('#cart-price-breakdown').hide();
                }

                $('#cart-total-price').text(formatRupiah(totalPrice));

                // Sync with form field
                $('#cost_samples').val(totalPrice);
            }

            // Remove item from cart
            $(document).on('click', '.cart-item-remove', function(e) {
                e.preventDefault();
                const methodId = $(this).data('method-id');

                // Uncheck the corresponding checkbox
                $(`.checkbox[data-idmethod="${methodId}"]`).prop('checked', false).trigger('change');
            });

            // Clear all cart items
            $('#cart-clear-all').on('click', function() {
                if ($('.checkbox:checked').length === 0) return;

                if (confirm('Hapus semua parameter terpilih?')) {
                    $('.checkbox:checked').prop('checked', false);
                    updateCartWidget();
                    window.updateParameterCounts();
                }
            });

            // Function to update sample code visibility based on selected parameters
            function updateSampleCodeVisibility() {
                const checkedParams = $('.checkbox:checked');
                let hasKimia = false;
                let hasMikro = false;

                // Check each selected parameter
                checkedParams.each(function() {
                    const $row = $(this).closest('.method-row');
                    const $group = $row.closest('.parameter-group');
                    const groupTitle = $group.find('.parameter-group-header h5').text().trim()
                        .toLowerCase();

                    // Determine if parameter is Kimia or Mikro based on group title
                    if (groupTitle.includes('kimia')) {
                        hasKimia = true;
                    } else if (groupTitle.includes('mikro')) {
                        hasMikro = true;
                    }
                });

                // Update visibility and layout
                const $kimiaWrapper = $('#code_sample_kimia_wrapper_top');
                const $mikroWrapper = $('#code_sample_mikro_wrapper_top');

                // If no parameters selected, show both (initial state)
                if (checkedParams.length === 0) {
                    $kimiaWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                    $mikroWrapper.removeClass('col-lg-12').addClass('col-lg-6').show();
                } else if (hasKimia && hasMikro) {
                    // Both: show both with col-lg-6
                    $kimiaWrapper.removeClass('col-lg-12').addClass('col-lg-6').fadeIn(300);
                    $mikroWrapper.removeClass('col-lg-12').addClass('col-lg-6').fadeIn(300);
                } else if (hasKimia) {
                    // Only Kimia: show full width
                    $kimiaWrapper.removeClass('col-lg-6').addClass('col-lg-12').fadeIn(300);
                    $mikroWrapper.fadeOut(300);
                } else if (hasMikro) {
                    // Only Mikro: show full width
                    $kimiaWrapper.fadeOut(300);
                    $mikroWrapper.removeClass('col-lg-6').addClass('col-lg-12').fadeIn(300);
                }
            }

            // Listen to checkbox changes
            $(document).on('change', '.checkbox', function() {
                updateSampleCodeVisibility();
            });

            // Initial update
            window.updateParameterCounts();
            window.moveCheckedParametersToTop();
            updateSampleCodeVisibility();

            // Apply show more to all groups on initial load (with delay to ensure DOM ready)
            setTimeout(function() {
                $('.parameter-group').each(function() {
                    const $group = $(this);
                    // Always apply show more logic, even if collapsed
                    applyShowMoreLogic($group);
                });
            }, 500);

            // Listen to packet selection (if exists)
            $('#packet').on('change', function() {
                setTimeout(function() {
                    window.updateParameterCounts();
                    // Update sample code visibility after packet parameters are loaded
                    updateSampleCodeVisibility();
                    window.moveCheckedParametersToTop();
                    updateCartWidget(); // Update cart widget after packet selection
                }, 500); // Give time for checkboxes to update
            });
        });
    </script>
@endsection
