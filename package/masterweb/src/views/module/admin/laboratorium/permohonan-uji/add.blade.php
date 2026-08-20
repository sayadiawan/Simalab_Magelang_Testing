@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Management
@endsection

@section('content')
    <style>
        /* Modern Form Styling */
        .form-container {
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
            padding-bottom: 30px;
        }

        .page-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .page-header-card h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .page-header-card h2 i {
            margin-right: 15px;
            font-size: 32px;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
        }

        .page-header-card .subtitle {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-section-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
            contain: layout style;
        }

        #wilayah_sampling_section:not(.is-visible) {
            content-visibility: hidden;
            contain-intrinsic-size: 1px 400px;
        }

        #wilayah_sampling_section.is-visible {
            content-visibility: visible;
            contain-intrinsic-size: auto;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 12px;
            color: #667eea;
            font-size: 24px;
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
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control:read-only {
            background-color: #f7fafc;
            cursor: not-allowed;
        }

        .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 0 10px 10px 0;
        }

        .btn-link-custom {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
            display: inline-block;
            margin-top: 10px;
        }

        .btn-link-custom:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #764ba2;
            text-decoration: none;
        }

        .customer-info-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border-left: 5px solid #667eea;
        }

        .customer-info-card .info-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .customer-info-card .info-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 180px;
            font-size: 13px;
        }

        .customer-info-card .info-value {
            color: #2d3748;
            font-size: 15px;
            font-weight: 500;
        }

        .new-customer-card {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 2px solid #fc8181;
            position: relative;
        }

        .new-customer-card .close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 28px;
            color: #e53e3e;
            opacity: 1;
            transition: all 0.3s;
        }

        .new-customer-card .close:hover {
            transform: rotate(90deg);
            color: #c53030;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary-custom {
            background: #e2e8f0;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            color: #4a5568;
            transition: all 0.3s;
        }

        .btn-secondary-custom:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        .select2-container--classic .select2-selection--single {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            height: 48px;
            padding: 8px 15px;
        }

        .select2-container--classic .select2-selection--single:focus {
            border-color: #667eea;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #4a5568;
        }

        /* Icon enhancements */
        .form-group label i {
            margin-right: 8px;
            color: #667eea;
        }
    </style>

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
                                <a href="{{ url('/elits-permohonan-uji') }}">Permohonan Uji Management</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tambah Permohonan Uji</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <h2>
            <i class="fa fa-file-medical"></i>
            Tambah Permohonan Uji
        </h2>
        <div class="subtitle">Lengkapi formulir di bawah ini untuk membuat permohonan uji baru</div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('elits-permohonan-uji.store') }}" id="form" method="POST">
        @csrf

        <!-- Section 1: Informasi Permohonan -->
        <div class="form-section-card">
            <div class="section-title">
                <i class="fa fa-clipboard-list"></i>
                Informasi Permohonan
            </div>

            <input type="hidden" name="code_permohonan_uji" id="code_permohonan_uji" value="{{ $code }}">

            <div class="form-group">
                <label for="date_get_sample">
                    <i class="fa fa-calendar-alt"></i> Tanggal Masuk Pengajuan
                </label>
                <div class="input-group date">
                    <input type="text" class="form-control date_get_sample datepicker" name="date"
                        id="date_get_sample" placeholder="Pilih Tanggal" data-date-format="dd/mm/yyyy" required>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="petugas_penerima">
                    <i class="fa fa-user-tie"></i> Petugas Pendaftar
                </label>
                <select class="form-control" name="petugas_penerima" id="petugas_penerima">
                    <option value="">Pilih Petugas Pendaftar</option>
                    @foreach ($petugasPenerima as $petugas)
                        <option value="{{ $petugas }}">{{ $petugas }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa fa-flask"></i> Pengambilan Sampel (Sampling) Dilakukan Oleh
                </label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_sampling" id="is_sampling_lab" value="1"
                        checked>
                    <label class="form-check-label" for="is_sampling_lab">
                        Laboratorium
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_sampling" id="is_sampling_customer"
                        value="0">
                    <label class="form-check-label" for="is_sampling_customer">
                        Pelanggan
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 2: Informasi Pelanggan -->
        <div class="form-section-card">
            <div class="section-title">
                <i class="fa fa-users"></i>
                Informasi Pelanggan
            </div>

            <div class="form-group">
                <label for="customerAttributes">
                    <i class="fa fa-building"></i> Nama Pelanggan/Perusahaan
                </label>
                <select id="customerAttributes" name="customer" class="js-customer-basic-multiple js-states form-control"
                    style="width: 100%">
                </select>
                <button type="button" class="btn-link-custom not_found">
                    <i class="fa fa-plus-circle"></i> Klik Disini Jika Pelanggan Baru
                </button>
            </div>

            <!-- New Customer Form -->
            <div class="new-customer-card new_customer" style="display: none">
                <button type="button" class="close cancel_customer_new" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h5 style="color: #e53e3e; font-weight: 700; margin-bottom: 20px;">
                    <i class="fa fa-user-plus"></i> Data Pelanggan Baru
                </h5>

                <div class="form-group">
                    <label for="new_customer">
                        <i class="fa fa-user"></i> Nama Pelanggan
                    </label>
                    <input type="text" class="form-control" name="new_customer" id="new_customer"
                        placeholder="Masukkan Nama Pelanggan Baru">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold mb-3" style="color: #667eea; font-size: 15px;">
                        <i class="fa fa-map-marker mr-2"></i>WILAYAH PELANGGAN
                    </label>

                    {{-- Disimpan ke ms_customer.kecamatan_customer (teks kecamatan / jalur wilayah) --}}
                    <input type="hidden" name="new_kecamatan" id="new_kecamatan" value="">

                    <div class="mb-3" style="position: relative; z-index: 110;">
                        <div class="card border-0 shadow-sm"
                            style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #667eea !important; overflow: visible !important;">
                            <div class="card-body py-3" style="overflow: visible !important;">
                                <div class="d-flex align-items-center flex-wrap">
                                    <div class="flex-grow-1 position-relative" style="z-index: 1000; min-width: 220px;">
                                        <label class="small font-weight-bold mb-2" style="color: #1976d2;">
                                            <i class="fa fa-search mr-1"></i> Pencarian Cepat Wilayah
                                        </label>
                                        <div style="position: relative;">
                                            <input type="text" class="form-control form-control-lg"
                                                id="search_wilayah_customer"
                                                placeholder="Ketik nama desa, kecamatan, atau kabupaten... (min 2 karakter)"
                                                autocomplete="off"
                                                style="border: 2px solid #1976d2; border-radius: 10px; padding-left: 40px; font-size: 15px;">
                                            <i class="fa fa-search position-absolute"
                                                style="left: 15px; top: 14px; color: #1976d2; font-size: 16px; pointer-events: none;"></i>

                                            <div id="search_wilayah_customer_results"
                                                style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                                <div class="card border-0 shadow-lg" style="margin-bottom: 0 !important;">
                                                    <div class="list-group list-group-flush"
                                                        id="search_wilayah_customer_results_list"
                                                        style="max-height: 320px; overflow-y: auto;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3 text-center mt-2 mt-md-0">
                                        <button type="button" class="btn btn-sm" id="btn_toggle_manual_customer"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                                            <i class="fa fa-list mr-1"></i> Pilih Manual
                                        </button>
                                        <div class="small text-muted mt-1">atau pilih bertahap</div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    <strong>Tips:</strong> Ketik minimal 2 karakter. Contoh: "Magelang", "Secang", "Pucungrejo"
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3" id="manual_wilayah_customer_selector"
                        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: none; position: relative; z-index: 10;">
                        <div class="card-body" style="overflow: visible !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 font-weight-bold" style="color: #667eea;">
                                    <i class="fa fa-list-ul mr-2"></i>Pilih Wilayah Secara Bertahap
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="btn_hide_manual_customer">
                                    <i class="fa fa-times"></i> Tutup
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provinsi_customer" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-globe mr-1"></i> Provinsi
                                    </label>
                                    <select class="form-control" id="provinsi_customer">
                                        <option value="">-- Pilih Provinsi --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kabupaten_customer" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-building mr-1"></i> Kabupaten/Kota
                                    </label>
                                    <select class="form-control" id="kabupaten_customer" disabled>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kecamatan_customer_wilayah" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-map-signs mr-1"></i> Kecamatan
                                    </label>
                                    <select class="form-control" id="kecamatan_customer_wilayah" disabled>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="desa_customer" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-home mr-1"></i> Desa/Kelurahan
                                    </label>
                                    <select class="form-control" id="desa_customer" disabled>
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_address_customer" class="font-weight-bold" style="color: #667eea;">
                        <i class="fa fa-map-marker-alt mr-1"></i> Detail Alamat
                    </label>
                    <textarea class="form-control" id="new_address_customer" name="new_address_customer"
                        placeholder="Terisi otomatis dari wilayah (desa, kecamatan, kabupaten, provinsi). Bisa ditambah jalan/RT/RW."
                        rows="3"
                        style="border: 2px solid #e2e8f0; border-radius: 8px;"></textarea>
                    <small class="form-text text-muted">
                        <i class="fa fa-info-circle mr-1"></i>Terisi dari wilayah yang dipilih. Silakan tambahkan nama jalan, nomor rumah, RT/RW jika perlu.
                    </small>
                </div>

                <div class="form-group">
                    <label for="new_email_customer">
                        <i class="fa fa-envelope"></i> Email
                    </label>
                    <input type="email" class="form-control" id="new_email_customer" name="new_email_customer"
                        placeholder="Isikan Email">
                </div>

                <div class="form-group">
                    <label for="new_cp_customer">
                        <i class="fa fa-phone"></i> Contact Person
                    </label>
                    <textarea class="form-control" id="new_cp_customer" name="new_cp_customer" placeholder="Isikan Contact Person"></textarea>
                </div>
            </div>

            <!-- Existing Customer Info -->
            <div class="customer-info-card old_customer" style="display: none">
                <h5 style="color: #667eea; font-weight: 700; margin-bottom: 20px;">
                    <i class="fa fa-info-circle"></i> Detail Pelanggan
                </h5>

                <div class="info-row">
                    <div class="info-label">Nama Pelanggan:</div>
                    <div class="info-value" id="name_customer"></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Alamat:</div>
                    <div class="info-value" id="address_customer"></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Kecamatan:</div>
                    <div class="info-value" id="kecamatan_customer"></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value" id="email_customer"></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Contact Person:</div>
                    <div class="info-value" id="cp_customer"></div>
                </div>
            </div>

            <!-- Wilayah Sampling (Only shown when Lab is selected) -->
            <div id="wilayah_sampling_section" class="is-visible">
                <hr class="my-4">
                <div class="form-group">
                    <label class="font-weight-bold mb-3" style="color: #667eea; font-size: 16px;">
                        <i class="fa fa-map-marker mr-2"></i>WILAYAH SAMPLING
                    </label>

                    <!-- Search Wilayah Box -->
                    <div class="mb-3" style="position: relative; z-index: 100;">
                        <div class="card border-0 shadow-sm"
                            style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #667eea !important; overflow: visible !important;">
                            <div class="card-body py-3" style="overflow: visible !important;">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 position-relative" style="z-index: 1000;">
                                        <label class="small font-weight-bold mb-2" style="color: #1976d2;">
                                            <i class="fa fa-search mr-1"></i> Pencarian Cepat Wilayah
                                        </label>
                                        <div style="position: relative;">
                                            <input type="text" class="form-control form-control-lg"
                                                id="search_wilayah_sampling"
                                                placeholder="Ketik nama desa, kecamatan, atau kabupaten... (min 2 karakter)"
                                                autocomplete="off"
                                                style="border: 2px solid #1976d2; border-radius: 10px; padding-left: 40px; font-size: 15px; position: relative; z-index: 1;">
                                            <i class="fa fa-search position-absolute"
                                                style="left: 15px; top: 14px; color: #1976d2; font-size: 16px; pointer-events: none; z-index: 2;"></i>

                                            <!-- Autocomplete Results -->
                                            <div id="search_wilayah_sampling_results"
                                                style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                                <div class="card border-0 shadow-lg" style="margin-bottom: 0 !important;">
                                                    <div class="list-group list-group-flush"
                                                        id="search_wilayah_sampling_results_list"
                                                        style="max-height: 400px; overflow-y: auto;">
                                                        <!-- Results will be populated here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3 text-center">
                                        <button type="button" class="btn btn-sm" id="btn_toggle_manual_sampling"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                                            <i class="fa fa-list mr-1"></i> Pilih Manual
                                        </button>
                                        <div class="small text-muted mt-1">atau pilih bertahap</div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    <strong>Tips:</strong> Ketik minimal 2 karakter untuk melihat rekomendasi.
                                    Contoh: "Magelang", "Secang", "Pucungrejo"
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cascade Dropdown (Can be collapsed) -->
                    <div class="card border-0 shadow-sm mb-3" id="manual_wilayah_sampling_selector"
                        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: none; position: relative; z-index: 10;">
                        <div class="card-body" style="overflow: visible !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 font-weight-bold" style="color: #667eea;">
                                    <i class="fa fa-list-ul mr-2"></i>Pilih Wilayah Secara Bertahap
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="btn_hide_manual_sampling">
                                    <i class="fa fa-times"></i> Tutup
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provinsi_sampling" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-globe mr-1"></i> Provinsi
                                    </label>
                                    <select class="form-control select-wilayah" id="provinsi_sampling"
                                        name="provinsi_sampling">
                                        <option value="">-- Pilih Provinsi --</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="kabupaten_sampling" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-building mr-1"></i> Kabupaten/Kota
                                    </label>
                                    <select class="form-control select-wilayah" id="kabupaten_sampling"
                                        name="kabupaten_sampling" disabled>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="kecamatan_sampling" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-map-signs mr-1"></i> Kecamatan
                                    </label>
                                    <select class="form-control select-wilayah" id="kecamatan_sampling"
                                        name="kecamatan_sampling" disabled>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="desa_sampling" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-home mr-1"></i> Desa/Kelurahan
                                    </label>
                                    <select class="form-control select-wilayah" id="desa_sampling" name="desa_sampling"
                                        disabled>
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Selected Wilayah -->
                    <div id="selected_wilayah_sampling_display" class="alert alert-info" style="display: none;">
                        <strong><i class="fa fa-map-marker-alt mr-2"></i>Wilayah Sampling Terpilih:</strong>
                        <div id="selected_wilayah_sampling_text" class="mt-2 font-weight-bold"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="detail_alamat_sampling" class="font-weight-bold" style="color: #667eea;">
                        <i class="fa fa-road mr-2"></i>DETAIL ALAMAT SAMPLING
                    </label>
                    <textarea class="form-control" name="detail_alamat_sampling" id="detail_alamat_sampling" rows="3"
                        placeholder="Contoh: Jl. Merdeka No. 123, RT 02/RW 05, dekat Masjid Al-Ikhlas"
                        style="border: 2px solid #e2e8f0; border-radius: 8px;"></textarea>
                    <small class="form-text text-muted">
                        <i class="fa fa-info-circle mr-1"></i>Masukkan detail alamat lengkap seperti nama jalan,
                        nomor rumah, RT/RW, dan patokan lokasi sampling
                    </small>
                </div>
            </div>
        </div>

        <!-- Section 3: Informasi Tambahan -->
        <div class="form-section-card">
            <div class="section-title">
                <i class="fa fa-file-alt"></i>
                Informasi Tambahan
            </div>

            <div class="form-group">
                <label for="exampleFormControlTextarea1">
                    <i class="fa fa-sticky-note"></i> Catatan
                </label>
                <textarea class="form-control" name="catatan" id="exampleFormControlTextarea1" rows="4"
                    placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div>

            <div class="form-group">
                <label for="metode_pembayaran">
                    <i class="fa fa-credit-card"></i> Metode Pembayaran
                </label>
                <select class="form-control" name="metode_pembayaran" id="metode_pembayaran">
                    <option value="" selected disabled>Pilih Metode Pembayaran</option>
                    <option value="0">💵 Cash</option>
                    <option value="1">🏦 Transfer</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-section-card">
            <div class="action-buttons">
                <button type="submit" id="submitAll" class="btn btn-primary-custom">
                    <i class="fa fa-save"></i> Simpan Permohonan
                </button>
                <button type="button" class="btn btn-secondary-custom" onclick="window.history.back()">
                    <i class="fa fa-arrow-left"></i> Kembali
                </button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function deferCreatePageInit(callback) {
            if (window.requestIdleCallback) {
                requestIdleCallback(callback, { timeout: 2500 });
            } else {
                setTimeout(callback, 400);
            }
        }

        function setPengirimSample(value) {
            var el = document.getElementById('pengirim_sample');
            if (el) {
                el.value = value || '';
            }
        }

        var wilayahSamplingInitialized = false;
        var wilayahCustomerInitialized = false;
        var syncSamplingFromCustomerTimer = null;
        var lastSelectedExistingCustomer = null;

        /** Auto-isi wilayah/detail sampling hanya jika pengambilan sample oleh laboratorium */
        function isLabSampling() {
            return $('input[name="is_sampling"]:checked').val() === '1';
        }

        function buildWilayahPathParts(desa, kecamatan, kabupaten, provinsi, hasDesa, hasKec, hasKab, hasProv) {
            var candidates = [];
            if (hasDesa && desa && desa.indexOf('--') !== 0) candidates.push(desa);
            if (hasKec && kecamatan && kecamatan.indexOf('--') !== 0) candidates.push(kecamatan);
            if (hasKab && kabupaten && kabupaten.indexOf('--') !== 0) candidates.push(kabupaten);
            if (hasProv && provinsi && provinsi.indexOf('--') !== 0) candidates.push(provinsi);

            var parts = [];
            candidates.forEach(function(name) {
                var normalized = String(name).replace(/\s+/g, ' ').trim().toLowerCase();
                var prev = parts.length ? String(parts[parts.length - 1]).replace(/\s+/g, ' ').trim().toLowerCase() : '';
                if (normalized && normalized !== prev) {
                    parts.push(name.trim());
                }
            });
            return parts;
        }

        function updateSelectedSamplingDisplay() {
            var parts = buildWilayahPathParts(
                $('#desa_sampling option:selected').text(),
                $('#kecamatan_sampling option:selected').text(),
                $('#kabupaten_sampling option:selected').text(),
                $('#provinsi_sampling option:selected').text(),
                !!$('#desa_sampling').val(),
                !!$('#kecamatan_sampling').val(),
                !!$('#kabupaten_sampling').val(),
                !!$('#provinsi_sampling').val()
            );
            if (parts.length) {
                $('#selected_wilayah_sampling_text').text(parts.join(', '));
                $('#selected_wilayah_sampling_display').show();
            } else {
                $('#selected_wilayah_sampling_display').hide();
            }
        }

        function fillSamplingWilayahFromParents(parents, detailText) {
            // Syarat: hanya jika sampling oleh laboratorium
            if (!isLabSampling()) {
                return;
            }

            initWilayahSampling();
            $('#wilayah_sampling_section').addClass('is-visible').show();

            if (detailText !== undefined && detailText !== null && String(detailText).trim() !== '') {
                $('#detail_alamat_sampling').val(detailText);
            }

            if (!parents || !parents.provinsi_id) {
                return;
            }

            $('#manual_wilayah_sampling_selector').show();
            $('#provinsi_sampling').val(parents.provinsi_id).trigger('change');

            setTimeout(function() {
                if (parents.kabupaten_id) {
                    $('#kabupaten_sampling').val(parents.kabupaten_id).trigger('change');

                    setTimeout(function() {
                        if (parents.kecamatan_id) {
                            $('#kecamatan_sampling').val(parents.kecamatan_id).trigger('change');

                            setTimeout(function() {
                                if (parents.desa_id) {
                                    $('#desa_sampling').val(parents.desa_id).trigger('change');
                                }
                                updateSelectedSamplingDisplay();
                            }, 500);
                        } else {
                            updateSelectedSamplingDisplay();
                        }
                    }, 500);
                } else {
                    updateSelectedSamplingDisplay();
                }
            }, 500);
        }

        function syncCustomerWilayahToSampling() {
            // Syarat: hanya jika sampling oleh laboratorium
            if (!isLabSampling()) {
                return;
            }

            var parents = {
                provinsi_id: $('#provinsi_customer').val() || null,
                kabupaten_id: $('#kabupaten_customer').val() || null,
                kecamatan_id: $('#kecamatan_customer_wilayah').val() || null,
                desa_id: $('#desa_customer').val() || null
            };

            if (!parents.provinsi_id && !parents.kabupaten_id && !parents.kecamatan_id && !parents.desa_id) {
                return;
            }

            fillSamplingWilayahFromParents(parents, $('#new_address_customer').val() || '');
        }

        function scheduleSyncCustomerWilayahToSampling() {
            // Jangan antri sync jika sampling bukan oleh laboratorium
            if (!isLabSampling()) {
                clearTimeout(syncSamplingFromCustomerTimer);
                return;
            }
            clearTimeout(syncSamplingFromCustomerTimer);
            syncSamplingFromCustomerTimer = setTimeout(function() {
                syncCustomerWilayahToSampling();
            }, 650);
        }

        function fillSamplingFromExistingCustomer(customer) {
            // Syarat: hanya jika sampling oleh laboratorium
            if (!isLabSampling()) {
                return;
            }

            var address = (customer.address_customer || '').trim();
            var kecamatanName = (customer.kecamatan_customer || '').trim();

            // Default detail sampling = alamat pelanggan (masih bisa diedit)
            if (address) {
                $('#detail_alamat_sampling').val(address);
            }

            var keyword = '';
            if (address) {
                keyword = address.split(',')[0].trim();
            }
            if ((!keyword || keyword.length < 2) && kecamatanName) {
                keyword = kecamatanName;
            }
            if (!keyword || keyword.length < 2) {
                return;
            }

            initWilayahSampling();

            $.ajax({
                url: "{{ route('api.wilayah.search') }}",
                type: "GET",
                data: { keyword: keyword, limit: 15 },
                success: function(results) {
                    // Double-check: user mungkin sudah ganti ke sampling pelanggan
                    if (!isLabSampling() || !results || !results.length) {
                        return;
                    }

                    var best = results[0];
                    var firstNorm = keyword.toLowerCase();
                    var addrNorm = address.toLowerCase();

                    for (var i = 0; i < results.length; i++) {
                        var nama = String(results[i].nama || '').toLowerCase();
                        var fullPath = String(results[i].full_path || '').toLowerCase();
                        if (nama === firstNorm) {
                            best = results[i];
                            break;
                        }
                        if (addrNorm && fullPath && addrNorm.indexOf(nama) !== -1) {
                            best = results[i];
                        }
                    }

                    $.ajax({
                        url: "{{ route('api.wilayah.parents', '') }}/" + best.id,
                        type: "GET",
                        success: function(parents) {
                            if (!isLabSampling()) {
                                return;
                            }
                            fillSamplingWilayahFromParents(parents, address || '');
                        }
                    });
                }
            });
        }

        /** Jika user baru memilih "Laboratorium", isi sampling dari pelanggan yang sudah dipilih */
        function applyPendingCustomerToLabSampling() {
            if (!isLabSampling()) {
                return;
            }

            // Pelanggan baru: pakai cascade wilayah pelanggan
            if ($('.new_customer').is(':visible') && ($('#provinsi_customer').val() || $('#new_address_customer').val())) {
                syncCustomerWilayahToSampling();
                return;
            }

            // Pelanggan lama: pakai data terakhir yang dipilih
            if (lastSelectedExistingCustomer) {
                fillSamplingFromExistingCustomer(lastSelectedExistingCustomer);
            }
        }

        function syncNewCustomerKecamatanFromCascade() {
            var desa = $('#desa_customer option:selected').text();
            var kecamatan = $('#kecamatan_customer_wilayah option:selected').text();
            var kabupaten = $('#kabupaten_customer option:selected').text();
            var provinsi = $('#provinsi_customer option:selected').text();

            var parts = buildWilayahPathParts(
                desa, kecamatan, kabupaten, provinsi,
                !!$('#desa_customer').val(),
                !!$('#kecamatan_customer_wilayah').val(),
                !!$('#kabupaten_customer').val(),
                !!$('#provinsi_customer').val()
            );

            if (parts.length) {
                var fullAddress = parts.join(', ');
                // Isi detail alamat pelanggan dari jalur wilayah yang dipilih
                $('#new_address_customer').val(fullAddress);
                // Backend menyimpan teks kecamatan; fallback ke jalur wilayah jika kecamatan belum dipilih
                var kecamatanText = ($('#kecamatan_customer_wilayah').val() && kecamatan.indexOf('--') !== 0)
                    ? kecamatan
                    : (($('#kabupaten_customer').val() && kabupaten.indexOf('--') !== 0) ? kabupaten : parts[0]);
                $('#new_kecamatan').val(kecamatanText);

                // Auto-isi WILAYAH SAMPLING + detail sampling (sama dengan pelanggan baru)
                scheduleSyncCustomerWilayahToSampling();
            } else {
                $('#new_kecamatan').val('');
            }
        }

        function initWilayahCustomer() {
            if (wilayahCustomerInitialized) {
                return;
            }
            wilayahCustomerInitialized = true;

            $.ajax({
                url: "{{ route('api.wilayah.provinsi') }}",
                type: "GET",
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#provinsi_customer').append(
                            '<option value="' + item.id_wilayah + '">' + item.wilayah + '</option>'
                        );
                    });
                }
            });

            $('#provinsi_customer').on('change', function() {
                var provinsiId = $(this).val();
                $('#kabupaten_customer').empty().append('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', true);
                $('#kecamatan_customer_wilayah').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                $('#desa_customer').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
                syncNewCustomerKecamatanFromCascade();

                if (provinsiId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.kabupaten', '') }}/" + provinsiId,
                        type: "GET",
                        success: function(response) {
                            $('#kabupaten_customer').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#kabupaten_customer').append(
                                    '<option value="' + item.id_wilayah + '">' + item.wilayah + '</option>'
                                );
                            });
                        }
                    });
                }
            });

            $('#kabupaten_customer').on('change', function() {
                var kabupatenId = $(this).val();
                $('#kecamatan_customer_wilayah').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                $('#desa_customer').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
                syncNewCustomerKecamatanFromCascade();

                if (kabupatenId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.kecamatan', '') }}/" + kabupatenId,
                        type: "GET",
                        success: function(response) {
                            $('#kecamatan_customer_wilayah').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#kecamatan_customer_wilayah').append(
                                    '<option value="' + item.id_wilayah + '">' + item.wilayah + '</option>'
                                );
                            });
                        }
                    });
                }
            });

            $('#kecamatan_customer_wilayah').on('change', function() {
                var kecamatanId = $(this).val();
                $('#desa_customer').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
                syncNewCustomerKecamatanFromCascade();

                if (kecamatanId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.desa', '') }}/" + kecamatanId,
                        type: "GET",
                        success: function(response) {
                            $('#desa_customer').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#desa_customer').append(
                                    '<option value="' + item.id_wilayah + '">' + item.wilayah + '</option>'
                                );
                            });
                        }
                    });
                }
            });

            $('#desa_customer').on('change', function() {
                syncNewCustomerKecamatanFromCascade();
            });

            var searchCustomerTimeout;
            $('#search_wilayah_customer').on('keyup', function() {
                var keyword = $(this).val();
                clearTimeout(searchCustomerTimeout);

                if (keyword.length < 2) {
                    $('#search_wilayah_customer_results').hide();
                    return;
                }

                searchCustomerTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('api.wilayah.search') }}",
                        type: "GET",
                        data: { keyword: keyword, limit: 10 },
                        success: function(response) {
                            var resultsHtml = '';
                            if (response.length > 0) {
                                $.each(response, function(index, item) {
                                    resultsHtml +=
                                        '<a href="#" class="list-group-item list-group-item-action wilayah-customer-search-item" ' +
                                        'data-id="' + item.id + '">' +
                                        '<div class="d-flex w-100 justify-content-between">' +
                                        '<h6 class="mb-1">' + item.nama + '</h6>' +
                                        '<small class="badge badge-primary">' + item.tipe + '</small>' +
                                        '</div>' +
                                        '<small class="text-muted">' + (item.full_path || '-') + '</small>' +
                                        '</a>';
                                });
                            } else {
                                resultsHtml = '<div class="list-group-item"><small class="text-muted">Tidak ada hasil ditemukan</small></div>';
                            }
                            $('#search_wilayah_customer_results_list').html(resultsHtml);
                            $('#search_wilayah_customer_results').show();
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.wilayah-customer-search-item', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                $.ajax({
                    url: "{{ route('api.wilayah.parents', '') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        if (response.provinsi_id) {
                            $('#provinsi_customer').val(response.provinsi_id).trigger('change');

                            setTimeout(function() {
                                if (response.kabupaten_id) {
                                    $('#kabupaten_customer').val(response.kabupaten_id).trigger('change');

                                    setTimeout(function() {
                                        if (response.kecamatan_id) {
                                            $('#kecamatan_customer_wilayah').val(response.kecamatan_id).trigger('change');

                                            setTimeout(function() {
                                                if (response.desa_id) {
                                                    $('#desa_customer').val(response.desa_id).trigger('change');
                                                } else {
                                                    syncNewCustomerKecamatanFromCascade();
                                                }
                                            }, 450);
                                        } else {
                                            syncNewCustomerKecamatanFromCascade();
                                        }
                                    }, 450);
                                } else {
                                    syncNewCustomerKecamatanFromCascade();
                                }
                            }, 450);
                        }

                        $('#manual_wilayah_customer_selector').show();
                        $('#search_wilayah_customer').val('');
                        $('#search_wilayah_customer_results').hide();
                    }
                });
            });
        }

        function initWilayahSampling() {
            if (wilayahSamplingInitialized) {
                return;
            }
            wilayahSamplingInitialized = true;

            $.ajax({
                url: "{{ route('api.wilayah.provinsi') }}",
                type: "GET",
                success: function(response) {
                    $('#provinsi_sampling').empty().append('<option value="">-- Pilih Provinsi --</option>');
                    $.each(response, function(index, item) {
                        $('#provinsi_sampling').append('<option value="' + item.id_wilayah + '">' + item
                            .wilayah + '</option>');
                    });
                }
            });

            $('#provinsi_sampling').on('change', function() {
                var provinsiId = $(this).val();
                $('#kabupaten_sampling').empty().append('<option value="">-- Pilih Kabupaten/Kota --</option>').prop(
                    'disabled', true);
                $('#kecamatan_sampling').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop(
                    'disabled', true);
                $('#desa_sampling').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop(
                    'disabled', true);

                if (provinsiId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.kabupaten', '') }}/" + provinsiId,
                        type: "GET",
                        success: function(response) {
                            $('#kabupaten_sampling').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#kabupaten_sampling').append('<option value="' + item
                                    .id_wilayah + '">' + item.wilayah + '</option>');
                            });
                        }
                    });
                }
            });

            $('#kabupaten_sampling').on('change', function() {
                var kabupatenId = $(this).val();
                $('#kecamatan_sampling').empty().append('<option value="">-- Pilih Kecamatan --</option>').prop(
                    'disabled', true);
                $('#desa_sampling').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop(
                    'disabled', true);

                if (kabupatenId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.kecamatan', '') }}/" + kabupatenId,
                        type: "GET",
                        success: function(response) {
                            $('#kecamatan_sampling').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#kecamatan_sampling').append('<option value="' + item
                                    .id_wilayah + '">' + item.wilayah + '</option>');
                            });
                        }
                    });
                }
            });

            $('#kecamatan_sampling').on('change', function() {
                var kecamatanId = $(this).val();
                $('#desa_sampling').empty().append('<option value="">-- Pilih Desa/Kelurahan --</option>').prop(
                    'disabled', true);

                if (kecamatanId) {
                    $.ajax({
                        url: "{{ route('api.wilayah.desa', '') }}/" + kecamatanId,
                        type: "GET",
                        success: function(response) {
                            $('#desa_sampling').prop('disabled', false);
                            $.each(response, function(index, item) {
                                $('#desa_sampling').append('<option value="' + item.id_wilayah +
                                    '">' + item.wilayah + '</option>');
                            });
                        }
                    });
                }
            });

            $('#desa_sampling').on('change', function() {
                updateSelectedSamplingDisplay();
            });

            $('#kecamatan_sampling').on('change', function() {
                // Display juga update jika user berhenti di tingkat kecamatan
                if (!$('#desa_sampling').val()) {
                    updateSelectedSamplingDisplay();
                }
            });

            var searchTimeout;
            $('#search_wilayah_sampling').on('keyup', function() {
                var keyword = $(this).val();

                clearTimeout(searchTimeout);

                if (keyword.length < 2) {
                    $('#search_wilayah_sampling_results').hide();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('api.wilayah.search') }}",
                        type: "GET",
                        data: {
                            keyword: keyword
                        },
                        success: function(response) {
                            var resultsHtml = '';

                            if (response.length > 0) {
                                $.each(response, function(index, item) {
                                    resultsHtml +=
                                        '<a href="#" class="list-group-item list-group-item-action wilayah-search-item" ' +
                                        'data-id="' + item.id + '" ' +
                                        'data-kode="' + item.kode + '" ' +
                                        'data-nama="' + item.nama + '" ' +
                                        'data-tipe="' + item.tipe + '" ' +
                                        'data-provinsi="' + (item.provinsi_id || '') +
                                        '" ' +
                                        'data-kabupaten="' + (item.kabupaten_id || '') +
                                        '" ' +
                                        'data-kecamatan="' + (item.kecamatan_id || '') +
                                        '" ' +
                                        'data-desa="' + (item.desa_id || '') + '">' +
                                        '<div class="d-flex w-100 justify-content-between">' +
                                        '<h6 class="mb-1">' + item.nama + '</h6>' +
                                        '<small class="badge badge-primary">' + item.tipe +
                                        '</small>' +
                                        '</div>' +
                                        '<small class="text-muted">' + item.full_path +
                                        '</small>' +
                                        '</a>';
                                });
                            } else {
                                resultsHtml =
                                    '<div class="list-group-item"><small class="text-muted">Tidak ada hasil ditemukan</small></div>';
                            }

                            $('#search_wilayah_sampling_results_list').html(resultsHtml);
                            $('#search_wilayah_sampling_results').show();
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.wilayah-search-item', function(e) {
                e.preventDefault();

                var id = $(this).data('id');

                $.ajax({
                    url: "{{ route('api.wilayah.parents', '') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        if (response.provinsi_id) {
                            $('#provinsi_sampling').val(response.provinsi_id).trigger('change');

                            setTimeout(function() {
                                if (response.kabupaten_id) {
                                    $('#kabupaten_sampling').val(response.kabupaten_id).trigger(
                                        'change');

                                    setTimeout(function() {
                                        if (response.kecamatan_id) {
                                            $('#kecamatan_sampling').val(response
                                                .kecamatan_id).trigger('change');

                                            setTimeout(function() {
                                                if (response.desa_id) {
                                                    $('#desa_sampling').val(response
                                                        .desa_id).trigger(
                                                        'change');
                                                }
                                            }, 500);
                                        }
                                    }, 500);
                                }
                            }, 500);
                        }

                        $('#manual_wilayah_sampling_selector').show();
                        $('#search_wilayah_sampling').val('');
                        $('#search_wilayah_sampling_results').hide();
                    }
                });
            });
        }

        function toggleWilayahSamplingSection(show) {
            var $section = $('#wilayah_sampling_section');
            if (show) {
                $section.addClass('is-visible').show();
                deferCreatePageInit(initWilayahSampling);
            } else {
                $section.removeClass('is-visible').hide();
            }
        }

        $(document).ready(function() {
            $('.date_get_sample').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            }).datepicker('update', new Date());

            $('#submitAll').click(function(e) {
                e.preventDefault();

                $('#submitAll').prop("disabled", true);

                var originalText = $('#submitAll').html();
                $('#submitAll').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

                $('#form').ajaxSubmit({
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Berhasil!",
                                    text: response.pesan,
                                    icon: "success",
                                    buttons: false,
                                    timer: 1500
                                })
                                .then(function() {
                                    if (response.is_sampling == 1) {
                                        var url =
                                            "{{ route('elits-sample-draft.create', '#') }}"
                                        url = url.replace('#', response.id);
                                        window.location.href = url;
                                    } else {
                                        var url = "{{ route('elits-samples.create', '#') }}"
                                        url = url.replace('#', response.id);
                                        window.location.href = url;
                                    }
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

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
                            $('#submitAll').prop("disabled", false);
                            $('#submitAll').html(originalText);
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                        swal("Error!", "System gagal menyimpan! Silakan coba lagi.", "error");
                        $('#submitAll').prop("disabled", false);
                        $('#submitAll').html(originalText);
                    }
                })
            });

            $('.not_found').click(function() {
                initWilayahCustomer();
                lastSelectedExistingCustomer = null;
                $('.new_customer').show();
                $('.old_customer').hide();
                $("#customerAttributes").val('').trigger('change');
            });

            $('.cancel_customer_new').click(function() {
                $("#customerAttributes").prop("disabled", false);
                $('.new_customer').hide();
                $('.old_customer').hide();
                $('#new_kecamatan').val('');
                $('#new_address_customer').val('');
                $('#search_wilayah_customer').val('');
                $('#search_wilayah_customer_results').hide();
                $('#manual_wilayah_customer_selector').hide();
            });

            $("#new_customer").on('keyup', function() {
                setPengirimSample($("#new_customer").val());
            });

            $('input[name="is_sampling"]').on('change', function() {
                var isLab = $(this).val() == '1';
                toggleWilayahSamplingSection(isLab);
                if (isLab) {
                    // Baru pilih Laboratorium → isi sampling dari pelanggan yang sudah ada
                    applyPendingCustomerToLabSampling();
                }
            });

            $('#btn_toggle_manual_sampling').click(function() {
                $('#manual_wilayah_sampling_selector').toggle();
            });

            $('#btn_hide_manual_sampling').click(function() {
                $('#manual_wilayah_sampling_selector').hide();
            });

            $('#btn_toggle_manual_customer').click(function() {
                initWilayahCustomer();
                $('#manual_wilayah_customer_selector').toggle();
            });

            $('#btn_hide_manual_customer').click(function() {
                $('#manual_wilayah_customer_selector').hide();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#search_wilayah_sampling, #search_wilayah_sampling_results').length) {
                    $('#search_wilayah_sampling_results').hide();
                }
                if (!$(e.target).closest('#search_wilayah_customer, #search_wilayah_customer_results').length) {
                    $('#search_wilayah_customer_results').hide();
                }
            });

            deferCreatePageInit(function() {
                $.fn.select2.defaults.set("theme", "classic");

                $('.js-customer-basic-multiple').select2({
                    placeholder: "Pilih Customer",
                    allowClear: true,
                    ajax: {
                        url: "{{ url('/api/customer/') }}",
                        method: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function(term) {
                            return {
                                term: term.term || '',
                                page: term.page || 1
                            };
                        },
                        cache: true
                    }
                }).on('change', function() {
                    var getID = $(this).select2('data');
                    if (getID[0] != "" && getID[0] != undefined) {
                        var id = getID[0]['id'];
                        var url = "{{ route('customer.detail', '#') }}"
                        url = url.replace("#", id);

                        $.ajax({
                            url: url,
                            type: "GET",
                            success: function(response) {
                                $("#name_customer").text(response.name_customer);
                                $("#address_customer").text(response.address_customer);
                                $("#kecamatan_customer").text(response.kecamatan_customer);
                                $("#email_customer").text(response.email_customer);
                                $("#category_customer").text(response.name_industry);
                                $("#cp_customer").text(response.cp_customer);
                                $('.old_customer').show();
                                $('.new_customer').hide();
                                setPengirimSample(response.name_customer);

                                lastSelectedExistingCustomer = response;
                                // Hanya auto-isi sampling jika pengambilan oleh laboratorium
                                if (isLabSampling()) {
                                    fillSamplingFromExistingCustomer(response);
                                }
                            }
                        })
                    } else {
                        $('.old_customer').hide();
                        lastSelectedExistingCustomer = null;
                    }
                });

                if ($('input[name="is_sampling"]:checked').val() == '1') {
                    initWilayahSampling();
                }
            });
        });
    </script>
@endsection
