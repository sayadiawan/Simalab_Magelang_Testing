@extends('masterweb::template.admin.layout')

@section('title')
    Permohonan Uji Klinik Registrasi
@endsection

@section('content')
    <style>
        .pointer {
            cursor: pointer;
        }

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* Payment Modal Styles */
        #modal-payment .modal-dialog {
            max-width: 600px;
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
            display: flex;
            flex-direction: column;
        }

        #modal-payment .modal-dialog.modal-dialog-centered {
            align-items: stretch;
            min-height: 0;
        }

        #modal-payment .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #modal-payment .modal-content > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
            margin-bottom: 0;
        }

        #modal-payment .modal-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 25px 30px;
            border: none;
            flex-shrink: 0;
        }

        #modal-payment .modal-header .modal-title {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        #modal-payment .modal-header .modal-title i {
            margin-right: 12px;
            font-size: 28px;
        }

        #modal-payment .modal-header .close {
            color: white;
            opacity: 1;
            text-shadow: none;
            font-size: 32px;
            font-weight: 300;
        }

        #modal-payment .modal-body {
            padding: 30px;
            background-color: #f8f9fa;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
        }

        .payment-info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .payment-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .payment-field-group {
            margin-bottom: 0;
        }

        .payment-field-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .payment-field-label i {
            margin-right: 8px;
            color: #0b3a5c;
            font-size: 16px;
        }

        .payment-field-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            min-height: 48px;
            display: flex;
            align-items: center;
        }

        .payment-field-value.readonly {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            cursor: not-allowed;
        }

        .payment-field-value textarea {
            border: none;
            background: transparent;
            resize: none;
            width: 100%;
            font-size: 16px;
            color: #2c3e50;
            padding: 0;
            outline: none;
        }

        .payment-total-card {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 4px 15px rgba(11, 58, 92, 0.3);
        }

        .payment-total-label {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .payment-total-amount {
            font-size: 36px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .payment-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 20px 0;
        }

        /* Payment Input Field */
        .payment-input-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 2px solid #0b3a5c;
        }

        .payment-input-field {
            position: relative;
        }

        .payment-input-field input {
            width: 100%;
            padding: 15px 20px;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s;
            text-align: right;
        }

        .payment-input-field input:focus {
            border-color: #0b3a5c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .payment-input-prefix {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            font-weight: 600;
            color: #6c757d;
        }

        .payment-change-card {
            background: linear-gradient(135deg, #48c774 0%, #3abb7c 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }

        .payment-change-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .payment-change-amount {
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .payment-error-message {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 10px;
            color: #856404;
            font-size: 14px;
            display: none;
        }

        .payment-error-message.show {
            display: block;
        }

        .payment-error-message i {
            margin-right: 8px;
        }

        /* Quick Amount Buttons */
        .quick-amount-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .quick-amount-btn {
            flex: 1;
            min-width: 80px;
            padding: 10px 15px;
            background: white;
            border: 2px solid #0b3a5c;
            border-radius: 8px;
            color: #0b3a5c;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-amount-btn:hover {
            background: #0b3a5c;
            color: white;
            transform: translateY(-2px);
        }

        .quick-amount-btn.exact {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            border: none;
        }

        #modal-payment .modal-footer {
            flex-shrink: 0;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 20px 30px;
            border-radius: 0 0 15px 15px;
        }

        #modal-payment .modal-footer .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        #modal-payment .modal-footer .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        #modal-payment .modal-footer .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        #modal-payment .modal-footer .btn-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        #modal-payment .modal-footer .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(11, 58, 92, 0.4);
        }

        #modal-payment .modal-footer .btn-primary:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        @media (max-width: 576px) {
            #modal-payment .modal-dialog {
                margin: 10px;
            }

            .payment-total-amount {
                font-size: 28px;
            }
        }

        /* Full width container */
        .content-wrapper {
            padding: 1.5rem;
        }

        /* Card styling */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table responsive wrapper — overflow-x memotong dropdown aksi;
           dropdown di-position fixed via JS agar tidak terpotong */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive-wrapper.dropdown-open,
        .dataTables_wrapper.dropdown-open {
            overflow: visible !important;
        }

        /* DataTable styling */
        #empTable {
            width: 100% !important;
            font-size: 0.9rem;
            table-layout: fixed;
        }

        table.dataTable#empTable.table-striped > tbody > tr > *,
        table.dataTable#empTable.table-striped > tbody > tr.odd > *,
        table.dataTable#empTable.table-striped > tbody > tr.even > * {
            box-shadow: none !important;
        }

        table.dataTable#empTable.table-striped > tbody > tr.odd {
            background-color: #fafafa;
        }

        .dataTables_wrapper {
            /* jangan pakai contain:layout — memotong dropdown aksi */
            width: 100%;
            overflow-x: auto;
            position: relative;
        }

        #empTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th.sorting,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_asc,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_desc {
            background-color: #0b3a5c !important;
            color: #fff !important;
            font-weight: 600;
            border: none;
            padding: 12px 8px;
            text-align: left;
            white-space: nowrap;
        }

        .dataTables_wrapper #empTable.dataTable thead th.sorting:before,
        .dataTables_wrapper #empTable.dataTable thead th.sorting:after,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_asc:before,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_asc:after,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_desc:before,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_desc:after {
            color: rgba(255, 255, 255, 0.85) !important;
            opacity: 1 !important;
        }

        #empTable thead th.text-center {
            text-align: center;
        }

        #empTable tbody td {
            vertical-align: middle;
            padding: 10px 8px;
            word-wrap: break-word;
        }

        #empTable tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Ensure table wrapper is responsive */
        .dataTables_scroll {
            overflow-x: auto !important;
            overflow-y: visible !important;
            width: 100%;
        }

        .dataTables_scrollHead {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        .dataTables_scrollHeadInner {
            width: 100% !important;
        }

        .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        /* Pastikan tabel bisa di-scroll horizontal */
        .dataTables_wrapper .dataTables_scroll .dataTables_scrollHead table,
        .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody table {
            width: 100% !important;
            margin: 0 !important;
        }

        /* Loading indicator */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            z-index: 1000;
        }

        /* Custom scrollbar untuk table */
        .table-responsive-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.justify-content-between > div {
                width: 100%;
            }

            #empTable {
                font-size: 0.8rem;
            }

            #empTable thead th,
            #empTable tbody td {
                padding: 8px 4px;
            }

            /* DataTables wrapper responsive */
            .dataTables_wrapper {
                overflow-x: auto;
            }

            .dataTables_wrapper .row {
                margin: 0;
            }

            .dataTables_wrapper .col-sm-12 {
                padding: 0.5rem;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 0.5rem;
            }

            .dataTables_wrapper .dataTables_length {
                float: none !important;
                text-align: left;
            }

            .dataTables_wrapper .dataTables_filter {
                float: none !important;
                text-align: left;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 0.5rem;
            }

            .dataTables_wrapper .dataTables_length select {
                width: 80px;
                margin: 0 0.5rem;
            }
        }

        /* Extra small devices */
        @media (max-width: 576px) {
            .breadcrumb {
                font-size: 0.75rem;
            }

            .breadcrumb-item {
                max-width: 100px;
            }

            table.dataTable {
                min-width: 800px;
            }

            table.dataTable thead th {
                font-size: 0.65rem;
                padding: 6px 4px !important;
            }

            table.dataTable tbody td {
                font-size: 0.7rem;
                padding: 5px 3px !important;
            }

            .dataTables_wrapper {
                overflow-x: auto;
                width: 100%;
            }

            .dataTables_wrapper .dataTables_scroll {
                overflow-x: auto;
                width: 100%;
            }

            .dataTables_wrapper .dataTables_info {
                font-size: 0.7rem;
                padding: 0.5rem 0;
                white-space: nowrap;
            }

            .dataTables_wrapper .dataTables_paginate {
                font-size: 0.7rem;
                margin-top: 0.5rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.25rem 0.4rem;
                font-size: 0.7rem;
                margin: 0 0.1rem;
            }
        }

        /* Fix untuk semua ukuran layar - pastikan scroll horizontal bekerja */
        @media (max-width: 1200px) {
            .dataTables_wrapper .dataTables_scrollHeadInner,
            .dataTables_wrapper .dataTables_scrollBody {
                overflow-x: auto;
            }

            table.dataTable {
                min-width: 900px;
            }
        }

        @media (max-width: 992px) {
            table.dataTable {
                min-width: 800px;
            }
        }

        @media (max-width: 768px) {
            table.dataTable {
                min-width: 700px;
            }
        }

        /* DataTables responsive behavior */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            background-color: #4CAF50;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th.dtr-control:before {
            background-color: #f44336;
        }

        /* Better spacing for filter dropdowns on mobile */
        @media (max-width: 576px) {
            .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }
        }

        /* DataTables responsive modal styling */
        .dtr-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dtr-modal-content {
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .dtr-modal-content {
                padding: 15px;
                max-width: 95%;
            }
        }

        /* Styling untuk dropdown action */
        #empTable .dropdown-menu,
        .dropdown-menu.emp-table-action-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            padding: 8px 0;
            max-height: min(320px, 50vh);
            overflow-y: auto !important;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            z-index: 2000;
        }

        #empTable .dropdown-header,
        .dropdown-menu.emp-table-action-menu .dropdown-header {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 16px 4px;
            margin-bottom: 4px;
        }

        #empTable .dropdown-item,
        .dropdown-menu.emp-table-action-menu .dropdown-item {
            padding: 8px 16px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        #empTable .dropdown-item:hover,
        .dropdown-menu.emp-table-action-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0b3a5c;
        }

        #empTable .dropdown-item i,
        .dropdown-menu.emp-table-action-menu .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        #empTable .dropdown-item.disabled,
        .dropdown-menu.emp-table-action-menu .dropdown-item.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #empTable .dropdown-divider,
        .dropdown-menu.emp-table-action-menu .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid #e9ecef;
        }

        #empTable .btn-primary {
            border-radius: 6px;
            padding: 6px 16px;
            font-size: 0.875rem;
        }
    </style>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>

    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/home') }}">
                            <i class="fa fa-home menu-icon mr-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/elits-permohonan-uji-klinik/registrasi') }}">Laboraturium</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Permohonan Uji Klinik Registrasi
                    </li>
                </ol>
            </nav>

            <!-- Alert Messages -->
            @if (session('error-bsre'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error-bsre') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error-laporan'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: "Gagal Melakukan Tanda Tangan Elektronik",
                            text: "Terjadi kesalahan saat melakukan tanda tangan elektronik. Silakan coba lagi.",
                            icon: "warning",
                            customClass: {
                                popup: 'my-custom-popup-class'
                            }
                        });
                    });
                </script>
            @endif

            <!-- Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Header dengan tombol tambah -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 8px;">
                        <div>
                            <h4 class="card-title mb-0">Daftar Permohonan Uji Klinik Registrasi</h4>
                            <p class="text-muted mb-0">Kelola data registrasi permohonan uji klinik</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            @if (getAction('delete') && Auth::user()->getlevel->level != 'DKTR')
                                <button type="button" class="btn btn-danger btn-icon-text" id="btn-hapus-massal" href="#hapus" disabled>
                                    <i class="fa fa-trash btn-icon-prepend"></i>
                                    Hapus Massal (<span id="hapus-massal-count">0</span>)
                                </button>
                            @endif
                            @if (getAction('create') && Auth::user()->getlevel->level != 'DKTR')
                                <a href="{{ route('elits-permohonan-uji-klinik-2.create') }}" onclick="localStorage.clear();">
                                    <button type="button" class="btn btn-info btn-icon-text">
                                        <i class="fa fa-plus btn-icon-prepend"></i>
                                        Tambah Data
                                    </button>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filter Tanggal Pendaftaran & Status Pembayaran -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="form-label" for="filter-date-start">Tanggal Mulai</label>
                            <input type="date" id="filter-date-start" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="form-label" for="filter-date-end">Tanggal Akhir</label>
                            <input type="date" id="filter-date-end" class="form-control form-control-sm">
                        </div>
                        @if (Auth::user()->getlevel->level != 'DKTR')
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="form-label" for="filter-pembayaran">Status Pembayaran</label>
                                <select id="filter-pembayaran" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="belum_lunas">Belum Lunas</option>
                                    <option value="lunas">Lunas</option>
                                </select>
                            </div>
                        @endif
                        <div class="col-auto mb-2 d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-filter-date">
                                <i class="fa fa-filter mr-1"></i> Terapkan
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-date-filter">
                                <i class="fa fa-times mr-1"></i> Reset
                            </button>
                            <small class="text-muted mb-0">Filter tanggal &amp; status pembayaran</small>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive-wrapper">
                        <table id='empTable' class="table table-striped table-bordered smt-table" style="width:100%">
                            <thead>
                                <tr>
                                    @if (getAction('delete') && Auth::user()->getlevel->level != 'DKTR')
                                        <th width="40px" class="text-center">
                                            <input type="checkbox" id="check-all-hapus-massal" title="Pilih semua di halaman ini">
                                        </th>
                                    @endif
                                    <th width="5%">No</th>
                                    <th width="12%">No Register</th>
                                    <th>Tipe Pemeriksaan</th>
                                    <th>Nama Pasien</th>
                                    <th width="12%">Status Registrasi</th>
                                    @if (Auth::user()->getlevel->level != 'DKTR')
                                        <th width="12%">Status Pembayaran</th>
                                    @endif
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-body">
                                <!-- Data akan di-load via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PAYMENT --}}
    <div class="modal fade text-left" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">
                        <i class="fa fa-cash-register"></i>
                        Pelunasan Pembayaran
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="" method="POST" id="form-payment" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="form-control" name="_token" id="csrf-token"
                        value="{{ Session::token() }}" />
                    <input type="hidden" class="form-control" name="id_permohonan_uji_klinik" id="id_permohonan_uji_klinik"
                        readonly>
                    <input type="hidden" class="form-control" name="nota_petugas_permohonan_uji_payment_klinik"
                        id="nota_petugas_permohonan_uji_payment_klinik" readonly>
                    <input type="hidden" class="form-control" id="total_harga" name="total_harga" readonly>

                    <div class="modal-body">
                        <!-- Total Amount Card -->
                        <div class="payment-total-card">
                            <div class="payment-total-label">
                                <i class="fa fa-money-bill-wave mr-2"></i> Total Pembayaran
                            </div>
                            <div class="payment-total-amount" id="display_total_harga">
                                Rp. 0
                            </div>
                            <!-- Biaya Pengambilan Sampel (if any) -->
                            <div id="biaya_pengambilan_section" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3);">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <small style="opacity: 0.9;"><i class="fa fa-vial mr-1"></i>Biaya Parameter:</small>
                                    <small id="display_biaya_parameter" class="font-weight-bold" style="opacity: 0.9;">Rp. 0</small>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <small style="opacity: 0.9;"><i class="fa fa-home mr-1"></i>Biaya Pengambilan Sampel:</small>
                                    <small id="display_biaya_pengambilan" class="font-weight-bold" style="opacity: 0.9;">Rp. 0</small>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Information Card -->
                        <div class="payment-info-card">
                            <div class="payment-field-group">
                                <div class="payment-field-label">
                                    <i class="fa fa-user"></i> Nama Pasien
                                </div>
                                <div class="payment-field-value readonly" id="display_nama_pasien">
                                    -
                                </div>
                            </div>
                        </div>

                        <!-- Address Card -->
                        <div class="payment-info-card">
                            <div class="payment-field-group">
                                <div class="payment-field-label">
                                    <i class="fa fa-map-marker-alt"></i> Alamat Pasien
                                </div>
                                <div class="payment-field-value readonly" id="display_alamat_pasien"
                                    style="min-height: 80px; align-items: flex-start;">
                                    -
                                </div>
                            </div>
                        </div>

                        <!-- Officer Card -->
                        <div class="payment-info-card">
                            <div class="payment-field-group">
                                <div class="payment-field-label">
                                    <i class="fa fa-user-shield"></i> Petugas
                                </div>
                                <div class="payment-field-value readonly" id="display_petugas">
                                    -
                                </div>
                            </div>
                        </div>

                        <!-- Detail Pemeriksaan Card -->
                        <div class="payment-info-card" id="detail-pemeriksaan-card" style="display: none;">
                            <div class="payment-field-label mb-3">
                                <i class="fa fa-list-alt"></i> Detail Pemeriksaan
                            </div>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th style="width: 80px;">Tipe</th>
                                            <th>Nama Pemeriksaan</th>
                                            <th style="width: 150px;" class="text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payment-items-body">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                <i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="payment-divider"></div>

                        <!-- Payment Input Card -->
                        <div class="payment-input-card">
                            <div class="payment-field-label mb-3">
                                <i class="fa fa-wallet"></i> Nominal Dibayarkan
                            </div>
                            <div class="payment-input-field">
                                <span class="payment-input-prefix">Rp</span>
                                <input type="text" class="form-control" id="terbayar_permohonan_uji_payment_klinik"
                                    name="terbayar_permohonan_uji_payment_klinik" placeholder="0" autocomplete="off">
                            </div>
                            <div class="payment-error-message" id="payment-error">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span id="payment-error-text"></span>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="quick-amount-buttons">
                                <button type="button" class="quick-amount-btn exact" data-action="exact">
                                    <i class="fa fa-check mr-1"></i> Pas
                                </button>
                                <button type="button" class="quick-amount-btn" data-amount="50000">
                                    + 50rb
                                </button>
                                <button type="button" class="quick-amount-btn" data-amount="100000">
                                    + 100rb
                                </button>
                            </div>
                        </div>

                        <!-- Change Card -->
                        <div class="payment-change-card" id="change-card" style="display: none;">
                            <div class="payment-change-label">
                                <i class="fa fa-hand-holding-usd mr-2"></i> Kembalian
                            </div>
                            <div class="payment-change-amount" id="display_kembalian">
                                Rp. 0
                            </div>
                        </div>

                        <!-- Hidden fields for form submission -->
                        <input type="hidden" id="nama_pasien" name="nama_pasien">
                        <input type="hidden" id="alamat_pasien" name="alamat_pasien">
                        <input type="hidden" id="total_harga_custom" name="total_harga_custom">
                        <input type="hidden" id="nota_namapetugas_permohonan_uji_payment_klinik"
                            name="nota_namapetugas_permohonan_uji_payment_klinik">
                        <input type="hidden" id="total_harga_permohonan_uji_payment_klinik"
                            name="total_harga_permohonan_uji_payment_klinik">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>
                            <span>Batal</span>
                        </button>

                        <button type="button" class="btn btn-primary ml-2" id="btnSave">
                            <i class="fa fa-check-circle mr-2"></i>
                            <span id="btnSaveText">Proses Pembayaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PAYMENT DETAIL --}}
    <div class="modal fade" id="modal-payment-detail" tabindex="-1" role="dialog"
        aria-labelledby="paymentDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #48c774 0%, #3abb7c 100%); color: white;">
                    <h5 class="modal-title" id="paymentDetailLabel">
                        <i class="fa fa-file-invoice-dollar mr-2"></i>
                        Detail Pembayaran
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="background-color: #f8f9fa; padding: 30px;">
                    <!-- Patient Info Card -->
                    <div class="card mb-3" style="border-left: 4px solid #48c774;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong><i class="fa fa-user mr-2 text-primary"></i>Nama
                                            Pasien:</strong></p>
                                    <p id="detail_nama_pasien" class="ml-4">-</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong><i class="fa fa-hashtag mr-2 text-primary"></i>No.
                                            Register:</strong></p>
                                    <p id="detail_no_register" class="ml-4">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary Card -->
                    <div class="card mb-3"
                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-6 border-right border-light">
                                    <h6 class="mb-2"><i class="fa fa-file-invoice mr-2"></i>Total Tagihan</h6>
                                    <h4 id="detail_total_tagihan" class="mb-0 font-weight-bold">Rp. 0</h4>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="fa fa-money-bill-wave mr-2"></i>Total Terbayar</h6>
                                    <h4 id="detail_total_terbayar" class="mb-0 font-weight-bold">Rp. 0</h4>
                                </div>
                            </div>
                            <!-- Biaya Pengambilan Sampel (if any) -->
                            <div id="detail_biaya_pengambilan_section" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3);">
                                <div class="row">
                                    <div class="col-md-6 border-right border-light">
                                        <small style="opacity: 0.9;"><i class="fa fa-vial mr-1"></i>Biaya Parameter:</small>
                                        <div id="detail_biaya_parameter" class="font-weight-bold" style="opacity: 0.9;">Rp. 0</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small style="opacity: 0.9;"><i class="fa fa-home mr-1"></i>Biaya Pengambilan Sampel:</small>
                                        <div id="detail_biaya_pengambilan" class="font-weight-bold" style="opacity: 0.9;">Rp. 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Packages/Parameters Card -->
                    <div class="card mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fa fa-list mr-2 text-info"></i>Detail Paket/Parameter</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="items-detail-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 100px;">Tipe</th>
                                            <th>Nama Paket/Parameter</th>
                                            <th style="width: 150px;" class="text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-detail-body">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fa fa-history mr-2 text-success"></i>Riwayat Pembayaran</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="payment-history-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No. Nota</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Dibayar</th>
                                            <th>Kembalian</th>
                                            <th>Petugas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payment-history-body">
                                        <tr>
                                            <td colspan="6" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{--    Modal Option SIGN --}}
    <div class="modal fade" id="signOptionModal" tabindex="-1" aria-labelledby="signOptionTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signOptionTitle">Pilih metode tanda tangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agendaNumber">Nomor Agenda</label>
                        <input type="text" class="form-control" id="agendaNumber" name="agenda"
                            placeholder="Masukkan nomor agenda">
                    </div>
                </div>
                <div class="d-flex mx-auto m-2 justify-content-around">
                    <a id="linkTTDManual" href="" target="_blank">
                        <button class="btn text-center m-2 p-2 sign-opt">
                            <img src="{{ asset('assets/admin/images/sign-icon.png') }}" width="80" height="80">
                            <h5 class="mt-2">Tanda Tangan Manual</h5>
                        </button>
                    </a>
                    <a id="linkTTDElektronik" href="" target="_blank">
                        <button class="btn text-center m-2 p-2 sign-opt">
                            <img src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" width="80"
                                height="80">
                            <h5 class="mt-2">Tanda Tangan Elektronik</h5>
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // DataTable columns configuration
            var canDeleteMassal = @json(getAction('delete') && Auth::user()->getlevel->level != 'DKTR');
            var noregisterColIndex = canDeleteMassal ? 2 : 1;
            var columns = [];

            if (canDeleteMassal) {
                columns.push({
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                });
            }

            columns.push({
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'noregister_permohonan_uji_klinik',
                    name: 'noregister_permohonan_uji_klinik'
                },
                {
                    data: 'prolanis',
                    name: 'prolanis'
                },
                {
                    data: 'nama_pasien',
                    name: 'nama_pasien',
                    render: function(data, type, row) {
                        if (!data) {
                            return '';
                        }
                        return data.toString().toUpperCase();
                    }
                },
                {
                    data: 'status_registrasi',
                    name: 'status_registrasi'
                }
            );

            // Only add status_pembayaran column if user is not DKTR
            @if (Auth::user()->getlevel->level != 'DKTR')
                columns.push({
                    data: 'status_pembayaran',
                    name: 'status_pembayaran'
                });
            @endif

            // Always add action column at the end
            columns.push({
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            });

            function updateHapusMassalCount() {
                if (!canDeleteMassal) return;
                var count = $('.check-hapus-massal:checked').length;
                $('#hapus-massal-count').text(count);
                $('#btn-hapus-massal').prop('disabled', count === 0);
            }

            // DataTable
            var table = $('#empTable').DataTable({                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: false,
                autoWidth: false,
                deferRender: true,
                orderClasses: false,
                columnDefs: [
                    {
                        targets: canDeleteMassal ? [0, 1] : [0],
                        width: "50px",
                        className: "text-center",
                        orderable: false
                    },
                    {
                        targets: [noregisterColIndex],
                        width: "120px",
                        orderSequence: ['desc']
                    },
                    {
                        targets: [noregisterColIndex + 1],
                        width: "180px"
                    },
                    {
                        targets: [noregisterColIndex + 2],
                        width: "200px"
                    },
                    {
                        targets: [noregisterColIndex + 3],
                        width: "150px"
                    },
                    {
                        targets: '_all',
                        className: "text-left"
                    }
                ],
                pageLength: 10, // Default 10 data per halaman
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                ajax: {
                    url: "{{ request()->is('elits-permohonan-uji-klinik-2*') ? route('elits-permohonan-uji-klinik-2.data-permohonan-uji-klinik-registrasi') : route('elits-permohonan-uji-klinik.data-permohonan-uji-klinik-registrasi') }}",
                    type: "GET",
                    data: function(d) {
                        d.date_start = $('#filter-date-start').val();
                        d.date_end = $('#filter-date-end').val();
                        d.filter_pembayaran = $('#filter-pembayaran').val() || '';
                    },
                    error: function(xhr, error, code) {
                        console.log('DataTable AJAX Error:', xhr, error, code);
                    }
                },
                columns: columns,
                order: [[noregisterColIndex, 'desc']],
                stateLoadParams: function(settings, data) {
                    if (data.columns && data.columns.length !== columns.length) {
                        delete data.columns;
                        data.order = [[noregisterColIndex, 'desc']];
                    }
                    if (data.order && data.order.length) {
                        data.order.forEach(function(item) {
                            if (item[0] === noregisterColIndex) {
                                item[1] = 'desc';
                            }
                        });
                    }
                },
                drawCallback: function() {
                    if (!canDeleteMassal) return;
                    var total = $('.check-hapus-massal').length;
                    var checked = $('.check-hapus-massal:checked').length;
                    $('#check-all-hapus-massal').prop('checked', total > 0 && total === checked);
                    updateHapusMassalCount();
                },
                language: {
                    processing: "Memproses...",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Tidak ada data yang tersedia",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Dropdown aksi: position fixed + max-height agar bisa di-scroll
            // (parent table overflow-x memotong menu biasa)
            function positionEmpTableActionDropdown($dropdown) {
                var $btn = $dropdown.find('[data-toggle="dropdown"]').first();
                var $menu = $dropdown.find('.dropdown-menu').first();
                if (!$btn.length || !$menu.length) {
                    return;
                }

                $menu.addClass('emp-table-action-menu');

                var rect = $btn[0].getBoundingClientRect();
                var gap = 4;
                var spaceBelow = window.innerHeight - rect.bottom - gap;
                var spaceAbove = rect.top - gap;
                var preferUp = spaceBelow < 180 && spaceAbove > spaceBelow;
                var maxH = Math.min(320, preferUp ? spaceAbove : spaceBelow);
                if (maxH < 120) {
                    maxH = Math.min(320, Math.max(spaceBelow, spaceAbove, 120));
                }

                $menu.css({
                    position: 'fixed',
                    left: 'auto',
                    right: Math.max(8, window.innerWidth - rect.right) + 'px',
                    transform: 'none',
                    maxHeight: maxH + 'px',
                    overflowY: 'auto',
                    zIndex: 2000
                });

                if (preferUp) {
                    $menu.css({
                        top: 'auto',
                        bottom: (window.innerHeight - rect.top + gap) + 'px'
                    });
                } else {
                    $menu.css({
                        top: (rect.bottom + gap) + 'px',
                        bottom: 'auto'
                    });
                }
            }

            function resetEmpTableActionDropdown($dropdown) {
                var $menu = $dropdown.find('.dropdown-menu').first();
                $menu.removeClass('emp-table-action-menu').css({
                    position: '',
                    top: '',
                    bottom: '',
                    left: '',
                    right: '',
                    transform: '',
                    maxHeight: '',
                    overflowY: '',
                    zIndex: ''
                });
            }

            $(document).on('show.bs.dropdown', '#empTable .dropdown', function() {
                $('.table-responsive-wrapper, .dataTables_wrapper').addClass('dropdown-open');
            });

            $(document).on('shown.bs.dropdown', '#empTable .dropdown', function() {
                positionEmpTableActionDropdown($(this));
            });

            $(document).on('hide.bs.dropdown', '#empTable .dropdown', function() {
                resetEmpTableActionDropdown($(this));
                $('.table-responsive-wrapper, .dataTables_wrapper').removeClass('dropdown-open');
            });

            $(window).on('scroll.empTableAction resize.empTableAction', function() {
                var $open = $('#empTable .dropdown.show');
                if ($open.length) {
                    positionEmpTableActionDropdown($open);
                }
            });

            // Pastikan scroll di dalam menu tidak tertelan parent
            $(document).on('wheel touchmove', '.dropdown-menu.emp-table-action-menu', function(e) {
                e.stopPropagation();
            });

            function reloadTableWithDateFilter() {
                var start = $('#filter-date-start').val();
                var end = $('#filter-date-end').val();
                if (start && end && start > end) {
                    swal('Peringatan', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', 'warning');
                    return;
                }
                table.ajax.reload();
            }

            $('#btn-filter-date').on('click', function() {
                reloadTableWithDateFilter();
            });

            $('#btn-reset-date-filter').on('click', function() {
                $('#filter-date-start').val('');
                $('#filter-date-end').val('');
                $('#filter-pembayaran').val('');
                table.ajax.reload();
            });

            $('#filter-pembayaran').on('change', function() {
                table.ajax.reload();
            });

            $('#filter-date-start, #filter-date-end').on('keypress', function(e) {
                if (e.which === 13) {
                    reloadTableWithDateFilter();
                }
            });

            var CSRF_TOKEN = $('#csrf-token').val();

            // Format number to rupiah (prefix sekali saja)
            function formatRupiah(angka, prefix = 'Rp. ') {
                var raw = (angka == null ? '0' : String(angka));
                var number_string = raw.replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                if (rupiah === '') {
                    rupiah = '0';
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix + rupiah;
            }

            // Format input to number only
            function formatNumber(input) {
                return String(input == null ? '' : input).replace(/[^0-9]/g, '');
            }

            function parseRupiahToInt(text) {
                var digits = formatNumber(text || '');
                return digits === '' ? 0 : (parseInt(digits, 10) || 0);
            }

            // Total tagihan efektif: hidden total, atau jumlah harga detail item
            function getEffectivePaymentTotal() {
                var fromHidden = parseInt($('#total_harga').val(), 10) || 0;
                var fromItems = 0;
                $('#payment-items-body tr').each(function() {
                    var $priceCell = $(this).find('td').last();
                    if ($priceCell.length) {
                        fromItems += parseRupiahToInt($priceCell.text());
                    }
                });
                var fromDisplay = parseRupiahToInt($('#display_total_harga').text());
                return Math.max(fromHidden, fromItems, fromDisplay);
            }

            function setPaymentTotal(total) {
                total = parseInt(total, 10) || 0;
                $('#total_harga').val(String(total));
                $('[name="total_harga"]').val(String(total));
                $('[name="total_harga_permohonan_uji_payment_klinik"]').val(String(total));
                $('#display_total_harga').text(formatRupiah(total));
            }

            function refreshPaymentButtonState() {
                var totalHarga = getEffectivePaymentTotal();
                if ((parseInt($('#total_harga').val(), 10) || 0) !== totalHarga) {
                    setPaymentTotal(totalHarga);
                }

                var terbayarRaw = formatNumber($('#terbayar_permohonan_uji_payment_klinik').val() || '');
                var terbayar = terbayarRaw === '' ? null : (parseInt(terbayarRaw, 10) || 0);
                var kembalian = (terbayar == null ? 0 : terbayar) - totalHarga;

                $('#payment-error').removeClass('show');
                $('#change-card').hide();

                // Tagihan Rp 0: boleh proses dengan nominal 0 (gratis / BPJS)
                if (totalHarga <= 0) {
                    if (terbayar === null) {
                        $('#terbayar_permohonan_uji_payment_klinik').val('0');
                        terbayar = 0;
                    }
                    $('#btnSave').prop('disabled', false);
                    return;
                }

                if (terbayar === null) {
                    $('#btnSave').prop('disabled', true);
                    return;
                }

                if (terbayar < totalHarga) {
                    $('#payment-error-text').text(
                        'Nominal kurang dari total pembayaran. Status akan menjadi "Belum Lunas".');
                    $('#payment-error').addClass('show');
                    $('#btnSave').prop('disabled', false);
                } else if (kembalian > 0) {
                    $('#display_kembalian').text(formatRupiah(kembalian));
                    $('#change-card').show();
                    $('#btnSave').prop('disabled', false);
                } else {
                    $('#btnSave').prop('disabled', false);
                }
            }

            // Quick amount buttons
            $('.quick-amount-btn').on('click', function() {
                var action = $(this).data('action');
                var amount = $(this).data('amount');
                var totalHarga = getEffectivePaymentTotal();
                setPaymentTotal(totalHarga);

                if (action === 'exact') {
                    // Pas: isi sesuai total efektif (jumlah item), termasuk 0
                    $('#terbayar_permohonan_uji_payment_klinik').val(String(totalHarga));
                    refreshPaymentButtonState();
                } else if (amount) {
                    var currentVal = parseInt(formatNumber($('#terbayar_permohonan_uji_payment_klinik').val() || '0'), 10) || 0;
                    $('#terbayar_permohonan_uji_payment_klinik').val(String(currentVal + amount));
                    refreshPaymentButtonState();
                }
            });

            // Format input field as currency
            $('#terbayar_permohonan_uji_payment_klinik').on('keyup input', function() {
                var number = formatNumber($(this).val());
                $(this).val(number);
                refreshPaymentButtonState();
            });

            // btn payment
            $('#empTable').on('click', '.btn-payment', function(e) {
                e.preventDefault();
                var permohonan_uji_klinik_id = $(this).data('id');
                var $button = $(this);

                // Show loading on button
                $button.html('<i class="fa fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

                $('#form-payment').trigger('reset'); // reset form on modals

                // Clear payment input fields
                $('#terbayar_permohonan_uji_payment_klinik').val('');
                $('#payment-error').removeClass('show');
                $('#change-card').hide();
                $('#btnSave').prop('disabled', true);
                $('#detail-pemeriksaan-card').hide();
                $('#payment-items-body').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');

                $('#id_permohonan_uji_klinik').val(permohonan_uji_klinik_id);

                $.ajax({
                    type: "POST",
                    url: "{{ route('permohonan-uji-klinik-get-payment2') }}",
                    data: {
                        permohonan_uji_klinik_id: permohonan_uji_klinik_id,
                        _token: CSRF_TOKEN
                    },
                    dataType: "JSON",
                    success: function(data) {
                        // Set hidden fields for form submission
                        $('[name="nota_petugas_permohonan_uji_payment_klinik"]').val(data
                            .nota_petugas);
                        $('[name="nota_namapetugas_permohonan_uji_payment_klinik"]').val(data
                            .nota_namapetugas);
                        $('[name="nama_pasien"]').val((data.nama_pasien || '').toUpperCase());
                        $('[name="alamat_pasien"]').val(data.alamat_pasien);
                        // Display data in the beautiful cards
                        $('#display_nama_pasien').text((data.nama_pasien || '-').toUpperCase());
                        $('#display_alamat_pasien').text(data.alamat_pasien || '-');
                        $('#display_petugas').text(data.nota_namapetugas || '-');

                        // Handle biaya pengambilan sampel if exists
                        if (data.biaya_pengambilan_sampel && data.biaya_pengambilan_sampel > 0) {
                            $('#display_biaya_parameter').text(formatRupiah(data.total_harga_parameter || 0));
                            $('#display_biaya_pengambilan').text(formatRupiah(data.biaya_pengambilan_sampel));
                            $('#biaya_pengambilan_section').show();
                        } else {
                            $('#biaya_pengambilan_section').hide();
                        }

                        // Display detail pemeriksaan (items)
                        var itemsHtml = '';
                        var sumItems = 0;
                        if (data.items && data.items.length > 0) {
                            $('#detail-pemeriksaan-card').show();
                            $.each(data.items, function(index, item) {
                                var typeBadge = '';
                                if (item.type === 'Paket Extra') {
                                    typeBadge = '<span class="badge badge-warning badge-pill"><i class="fa fa-star mr-1"></i>Paket Extra</span>';
                                } else if (item.type === 'Paket') {
                                    typeBadge = '<span class="badge badge-primary badge-pill"><i class="fa fa-box mr-1"></i>Paket</span>';
                                } else {
                                    typeBadge = '<span class="badge badge-info badge-pill"><i class="fa fa-flask mr-1"></i>Parameter</span>';
                                }

                                var itemHarga = parseInt(item.harga, 10) || 0;
                                sumItems += itemHarga;
                                
                                itemsHtml += '<tr>';
                                itemsHtml += '<td>' + typeBadge + '</td>';
                                itemsHtml += '<td>' + (item.name || '-') + '</td>';
                                itemsHtml += '<td class="text-right font-weight-bold">' +
                                    formatRupiah(itemHarga) + '</td>';
                                itemsHtml += '</tr>';
                            });
                        } else {
                            $('#detail-pemeriksaan-card').hide();
                            itemsHtml = '<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-info-circle mr-2"></i>Belum ada pemeriksaan yang dipilih</td></tr>';
                        }
                        $('#payment-items-body').html(itemsHtml);

                        // Total efektif: API total ATAU jumlah item (+ biaya pengambilan)
                        var biayaAmbil = parseInt(data.biaya_pengambilan_sampel, 10) || 0;
                        var totalFromApi = parseInt(data.total_harga, 10) || 0;
                        var totalHargaNum = Math.max(totalFromApi, sumItems + biayaAmbil);
                        setPaymentTotal(totalHargaNum);
                        $('[name="total_harga_custom"]').val(formatRupiah(totalHargaNum));

                        // Tagihan 0: isi otomatis nominal 0 agar tombol Pas/Proses aktif
                        if (totalHargaNum <= 0) {
                            $('#terbayar_permohonan_uji_payment_klinik').val('0');
                        } else {
                            $('#terbayar_permohonan_uji_payment_klinik').val('');
                        }
                        refreshPaymentButtonState();

                        // Restore button state
                        $button.html('<i class="fa fa-exclamation-circle mr-1"></i>Belum Bayar')
                            .prop('disabled', false);

                        // Show modal immediately
                        $('#modal-payment').modal('show');

                        // Focus on payment input after modal animation (reduced delay)
                        setTimeout(function() {
                            $('#terbayar_permohonan_uji_payment_klinik').focus();
                        }, 300);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Restore button state
                        $button.html('<i class="fa fa-exclamation-circle mr-1"></i>Belum Bayar')
                            .prop('disabled', false);
                        swal("Error", "Error get data from ajax!", "error");
                    }
                });
            });

            // btn payment detail - when clicking "Lunas" badge
            $('#empTable').on('click', '.btn-payment-detail', function(e) {
                e.preventDefault();
                var permohonan_uji_klinik_id = $(this).data('id');

                // Show modal immediately with loading state
                $('#payment-history-body').html(
                    '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>'
                );
                $('#items-detail-body').html(
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>'
                );
                $('#modal-payment-detail').modal('show');

                $.ajax({
                    type: "POST",
                    url: "{{ route('permohonan-uji-klinik-payment-detail') }}",
                    data: {
                        permohonan_uji_klinik_id: permohonan_uji_klinik_id,
                        _token: CSRF_TOKEN
                    },
                    dataType: "JSON",
                    success: function(response) {
                        console.log('Payment Detail Response:', response);

                        if (response.status && response.data) {
                            var data = response.data;

                            // Populate patient info
                            $('#detail_nama_pasien').text(data.nama_pasien || '-');
                            $('#detail_no_register').text(data.no_register || '-');

                            // Populate payment summary
                            $('#detail_total_tagihan').text(data.total_tagihan_formatted ||
                                'Rp. 0');
                            $('#detail_total_terbayar').text(data.total_terbayar_formatted ||
                                'Rp. 0');

                            // Handle biaya pengambilan sampel if exists
                            if (data.biaya_pengambilan_sampel && data.biaya_pengambilan_sampel > 0) {
                                $('#detail_biaya_parameter').text(formatRupiah(data.total_harga_parameter || 0));
                                $('#detail_biaya_pengambilan').text(formatRupiah(data.biaya_pengambilan_sampel));
                                $('#detail_biaya_pengambilan_section').show();
                            } else {
                                $('#detail_biaya_pengambilan_section').hide();
                            }

                            // Build items (packages/parameters) detail table
                            var itemsHtml = '';
                            if (data.items && data.items.length > 0) {
                                $.each(data.items, function(index, item) {
                                    var typeBadge = '';
                                    if (item.type === 'Paket Extra') {
                                        typeBadge = '<span class="badge badge-warning"><i class="fa fa-star mr-1"></i>Extra</span>';
                                    } else if (item.type === 'Paket') {
                                        typeBadge = '<span class="badge badge-primary"><i class="fa fa-box mr-1"></i>Paket</span>';
                                    } else {
                                        typeBadge = '<span class="badge badge-info"><i class="fa fa-flask mr-1"></i>Parameter</span>';
                                    }
                                    
                                    itemsHtml += '<tr>';
                                    itemsHtml += '<td>' + typeBadge + '</td>';
                                    itemsHtml += '<td>' + (item.name || '-') + '</td>';
                                    itemsHtml += '<td class="text-right font-weight-bold">' +
                                        formatRupiah(item.harga || 0) + '</td>';
                                    itemsHtml += '</tr>';
                                });
                            } else {
                                itemsHtml =
                                    '<tr><td colspan="3" class="text-center text-muted"><i class="fa fa-info-circle mr-2"></i>Belum ada paket/parameter</td></tr>';
                            }
                            $('#items-detail-body').html(itemsHtml);

                            // Build payment history table
                            var historyHtml = '';
                            if (data.payments && data.payments.length > 0) {
                                $.each(data.payments, function(index, payment) {
                                    var kembalianText = payment.kembalian > 0 ?
                                        '<span class="text-success font-weight-bold">' +
                                        formatRupiah(payment.kembalian) + '</span>' :
                                        '<span class="text-muted">-</span>';

                                    historyHtml += '<tr>';
                                    historyHtml += '<td class="font-weight-bold">#' +
                                        (payment.no_nota || '-') + '</td>';
                                    historyHtml += '<td>' + (payment.created_at ||
                                            '-') +
                                        '</td>';
                                    historyHtml += '<td>' + formatRupiah(payment
                                        .total_harga || 0) + '</td>';
                                    historyHtml +=
                                        '<td class="font-weight-bold text-primary">' +
                                        formatRupiah(payment.terbayar || 0) + '</td>';
                                    historyHtml += '<td>' + kembalianText + '</td>';
                                    historyHtml +=
                                        '<td><i class="fa fa-user-circle mr-1"></i>' +
                                        (payment.petugas || 'Unknown') + '</td>';
                                    historyHtml += '</tr>';
                                });
                            } else {
                                historyHtml =
                                    '<tr><td colspan="6" class="text-center text-muted"><i class="fa fa-info-circle mr-2"></i>Belum ada riwayat pembayaran</td></tr>';
                            }

                            $('#payment-history-body').html(historyHtml);
                        } else {
                            var errorMsg = response.pesan || 'Gagal memuat data pembayaran';
                            console.error('Server error:', response);
                            $('#items-detail-body').html(
                                '<tr><td colspan="3" class="text-center text-danger"><i class="fa fa-exclamation-circle mr-2"></i>' +
                                errorMsg + '</td></tr>'
                            );
                            $('#payment-history-body').html(
                                '<tr><td colspan="6" class="text-center text-danger"><i class="fa fa-exclamation-circle mr-2"></i>' +
                                errorMsg + '</td></tr>'
                            );
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', jqXHR, textStatus, errorThrown);
                        console.error('Response:', jqXHR.responseText);

                        var errorMsg = 'Terjadi kesalahan: ' + textStatus;
                        try {
                            var errorResponse = JSON.parse(jqXHR.responseText);
                            if (errorResponse.pesan) {
                                errorMsg = errorResponse.pesan;
                            }
                        } catch (e) {
                            // Could not parse, use default
                        }

                        $('#items-detail-body').html(
                            '<tr><td colspan="3" class="text-center text-danger"><i class="fa fa-exclamation-circle mr-2"></i>' +
                            errorMsg + '</td></tr>'
                        );
                        $('#payment-history-body').html(
                            '<tr><td colspan="6" class="text-center text-danger"><i class="fa fa-exclamation-circle mr-2"></i>' +
                            errorMsg + '</td></tr>'
                        );
                    }
                });
            });

            // btn payment proses
            var isPaymentSubmitting = false;
            $('#btnSave').click(function(param) {
                if (isPaymentSubmitting) {
                    return false;
                }

                // Validate payment amount
                var totalHarga = getEffectivePaymentTotal();
                setPaymentTotal(totalHarga);
                var terbayarRaw = formatNumber($('#terbayar_permohonan_uji_payment_klinik').val() || '');
                var terbayar = terbayarRaw === '' ? NaN : (parseInt(terbayarRaw, 10) || 0);

                // Tagihan 0: nominal 0 tetap boleh diproses (gratis / BPJS)
                if (totalHarga <= 0) {
                    if (isNaN(terbayar) || terbayar < 0) {
                        $('#terbayar_permohonan_uji_payment_klinik').val('0');
                        terbayar = 0;
                    }
                } else if (isNaN(terbayar) || terbayar < 0) {
                    $('#payment-error-text').text('Silakan masukkan nominal yang dibayarkan!');
                    $('#payment-error').addClass('show');
                    $('#terbayar_permohonan_uji_payment_klinik').focus();
                    return false;
                }

                isPaymentSubmitting = true;
                $('#btnSaveText').html(
                    '<i class="fa fa-spinner fa-spin mr-2"></i>Memproses...'); //change button text
                $('#btnSave').prop('disabled', true); //set button disable

                $.ajax({
                    url: "{{ route('permohonan-uji-klinik-store-payment2') }}",
                    type: "POST",
                    data: $('#form-payment').serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        if (data.status == true) //if success close modal and reload ajax table
                        {
                            swal({
                                icon: "success",
                                title: "Pembayaran Berhasil!",
                                text: data.pesan,
                            });

                            $('#form-payment').trigger('reset'); // reset form on modals
                        } else {
                            isPaymentSubmitting = false;
                            $('#btnSaveText').html(
                                '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran'
                            ); //change button text
                            $('#btnSave').prop('disabled', false); //set button enable

                            var pesan = "";
                            var data_pesan = data.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    console.log(value);
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
                                    text: data.pesan,
                                    icon: "warning"
                                });
                            }
                        }

                        $('#modal-payment').modal('hide');
                        table.ajax.reload(null, false);
                        isPaymentSubmitting = false;
                        $('#btnSaveText').html(
                            '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran'
                        );
                        $('#btnSave').prop('disabled', false);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        isPaymentSubmitting = false;
                        swal("Error!",
                            "Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi!",
                            "error");

                        $('#btnSaveText').html(
                            '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran'
                        ); //change button text
                        $('#btnSave').prop('disabled', false); //set button enable
                    }
                });
            });

            $(document).on('change', '#check-all-hapus-massal', function() {
                var checked = $(this).is(':checked');
                $('.check-hapus-massal').prop('checked', checked);
                updateHapusMassalCount();
            });

            $(document).on('change', '.check-hapus-massal', function() {
                var total = $('.check-hapus-massal').length;
                var checked = $('.check-hapus-massal:checked').length;
                $('#check-all-hapus-massal').prop('checked', total > 0 && total === checked);
                updateHapusMassalCount();
            });

            $('#btn-hapus-massal').on('click', function(e) {
                e.preventDefault();
                var $checked = $('.check-hapus-massal:checked');
                if ($checked.length === 0) {
                    swal('Perhatian', 'Pilih minimal satu data untuk dihapus.', 'warning');
                    return;
                }

                var ids = $checked.map(function() { return $(this).val(); }).get();
                swal({
                    title: 'Hapus massal?',
                    text: 'Akan menghapus ' + ids.length + ' data registrasi beserta parameter terkait. Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(function(willDelete) {
                    if (!willDelete) {
                        swal('Cancelled', 'Hapus massal dibatalkan!', 'error');
                        return;
                    }

                    $('#btn-hapus-massal').prop('disabled', true);
                    $.ajax({
                        method: 'POST',
                        url: '{{ route("elits-permohonan-uji-klinik.destroy-massal") }}',
                        dataType: 'json',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: ids
                        },
                        success: function(response) {
                            if (response.status == true) {
                                swal({
                                    title: 'Success!',
                                    text: response.pesan,
                                    icon: 'success'
                                }).then(function() {
                                    $('#check-all-hapus-massal').prop('checked', false);
                                    updateHapusMassalCount();
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                swal('Hapus Massal Gagal!', {
                                    icon: 'warning',
                                    title: 'Failed!',
                                    text: response.pesan
                                });
                                updateHapusMassalCount();
                            }
                        },
                        error: function(xhr) {
                            var pesan = (xhr.responseJSON && xhr.responseJSON.pesan)
                                ? xhr.responseJSON.pesan
                                : 'System tidak dapat menghapus data!';
                            swal('ERROR', pesan, 'error');
                            updateHapusMassalCount();
                        }
                    });
                });
            });

            $('#empTable').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + nama,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-permohonan-uji-klinik-destroy-2/' + kode,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                // document.location = '/elits-permohonan-uji-klinik';

                                                table.ajax.reload();
                                            });
                                    } else {
                                        swal("Hapus Data Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function() {
                                    swal("ERROR", "System tidak dapat menghapus data!",
                                        "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus data dibatalkan!", "error");
                        }
                    });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            function refreshSignLinks(baseLink) {
                var agenda = encodeURIComponent($('#agendaNumber').val() || '');
                var agendaPart = agenda ? ('&agenda=' + agenda) : '';
                $('#linkTTDManual').attr('href', baseLink + '?signoption=0' + agendaPart);
                $('#linkTTDElektronik').attr('href', baseLink + '?signoption=1' + agendaPart);
            }

            var currentBaseLink = '';
            $('#signOptionModal').on('show.bs.modal', function(event) {
                currentBaseLink = $(event.relatedTarget).data('href');
                refreshSignLinks(currentBaseLink);
            });

            $('#agendaNumber').on('input change', function() {
                if (currentBaseLink) {
                    refreshSignLinks(currentBaseLink);
                }
            });
        })
    </script>
@endsection
