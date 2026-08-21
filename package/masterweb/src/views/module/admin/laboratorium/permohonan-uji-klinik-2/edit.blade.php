@extends('masterweb::template.admin.layout')
@section('title')
    Edit Permohonan Uji Klinik
@endsection

@section('content')
    <script src="{{ asset('assets/admin/cdn-local/js/gijgo.min.js') }}" type="text/javascript"></script>
    <link href="{{ asset('assets/admin/cdn-local/css/gijgo.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    
    <!-- TinyMCE is already loaded in scripts.blade.php, no need to load again -->

    <script src="{{ asset('assets/admin/js/bootstrap-birthday.js') }}"></script>

    <style>
        .form-check {
            display: flex;
            align-items: center;
        }

        .single-date-field {
            width: 120px;
        }

        .form-check-input {
            position: relative;
            width: 30px;
            height: 15px;
            -webkit-appearance: none;
            appearance: none;
            background-color: #ccc;
            outline: none;
            cursor: pointer;
            border-radius: 15px;
            transition: background-color 0.3s;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
        }

        .form-check-input:before {
            content: "";
            position: absolute;
            width: 13px;
            height: 13px;
            background-color: white;
            border-radius: 50%;
            top: 1px;
            left: 1px;
            transition: transform 0.3s;
        }

        .form-check-input:checked:before {
            transform: translateX(15px);
        }

        .form-check-label {
            margin-left: 2px;
            font-size: 16px;
        }

        /* Enhanced Professional UI Styles */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding: 0 20px;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 35px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: linear-gradient(to right, #e0e0e0 0%, #e0e0e0 100%);
            z-index: 0;
            border-radius: 2px;
        }

        .wizard-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .wizard-step-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white;
            border: 4px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: 700;
            color: #999;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .wizard-step.active .wizard-step-circle {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-color: #0b3a5c;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(11, 58, 92, 0.4);
        }

        .wizard-step.completed .wizard-step-circle {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-color: #11998e;
            color: white;
        }

        .wizard-step.completed .wizard-step-circle::after {
            content: '✓';
            position: absolute;
            font-size: 28px;
        }

        .wizard-step-title {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }

        .wizard-step.active .wizard-step-title {
            color: #0b3a5c;
            font-size: 15px;
        }

        .wizard-step.completed .wizard-step-title {
            color: #11998e;
        }

        /* Doctor Type Selector - Enhanced */
        .doctor-type-selector {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 40px 0;
        }

        .doctor-type-card {
            flex: 1;
            max-width: 350px;
            padding: 40px 30px;
            border: 3px solid #e8e8e8;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
        }

        .doctor-type-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #0b3a5c 0%, #0d8f7f 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .doctor-type-card:hover {
            border-color: #0b3a5c;
            box-shadow: 0 10px 30px rgba(11, 58, 92, 0.25);
            transform: translateY(-8px);
        }

        .doctor-type-card:hover::before {
            transform: scaleX(1);
        }

        .doctor-type-card.selected {
            border-color: #0b3a5c;
            background: linear-gradient(135deg, #e7f4f2 0%, #dcefeb 100%);
            box-shadow: 0 10px 35px rgba(11, 58, 92, 0.3);
            transform: translateY(-5px) scale(1.02);
        }

        .doctor-type-card.selected::before {
            transform: scaleX(1);
        }

        .doctor-type-card.selected::after {
            content: '✓';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(17, 153, 142, 0.3);
        }

        .doctor-type-icon {
            font-size: 70px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }

        .doctor-type-card:hover .doctor-type-icon {
            transform: scale(1.1) rotateY(10deg);
        }

        .doctor-type-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #2d3748;
        }

        .doctor-type-description {
            font-size: 14px;
            color: #718096;
            line-height: 1.6;
        }

        .form-section {
            background: white;
            padding: 0;
            border-radius: 0;
            margin-bottom: 0;
            border: none;
            box-shadow: none;
        }

        .form-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #0b3a5c 0%, #0d8f7f 100%);
            border-image-slice: 1;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 12px;
            font-size: 24px;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-step {
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-step:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-step.btn-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        .btn-step.btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
        }

        .btn-step.btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
        }

        .btn-step.btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
        }

        .btn-step.btn-warning {
            background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
            border: none;
            color: white;
        }

        .btn-step.btn-warning:hover {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        }

        .btn-step:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Form Controls */
        .form-control,
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 3px rgba(11, 58, 92, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        /* Patient Detail Display Enhancement */
        #patient-detail-display {
            animation: slideIn 0.4s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #patient-detail-display .card {
            border: 3px solid #11998e;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.2);
            overflow: hidden;
        }

        #patient-detail-display .card-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            font-weight: 700;
            padding: 15px 20px;
            border: none;
        }

        #patient-detail-display .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 700;
            color: #2d3748;
            padding: 15px;
            border: none;
        }

        #patient-detail-display .table td {
            padding: 15px;
            color: #4a5568;
            border-color: #e2e8f0;
        }

        #patient-detail-display .table {
            margin-bottom: 0;
        }
    </style>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                        class="fa fa-home menu-icon mr-1"></i>
                                    Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan Uji
                                    Klinik
                                    Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>Edit</span></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Page Header -->
    <div class="page-header-card"
        style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(246, 173, 85, 0.3); color: white;">
        <h2 style="margin: 0; font-size: 28px; font-weight: 700; display: flex; align-items: center;">
            <i class="fa fa-edit"
                style="margin-right: 15px; font-size: 32px; background: rgba(255, 255, 255, 0.2); padding: 12px; border-radius: 12px;"></i>
            Edit Permohonan Uji Klinik
        </h2>
        <div style="margin-top: 10px; opacity: 0.9; font-size: 14px;">
            No. Sample: <strong>{{ $item->noregister_permohonan_uji_klinik }}</strong>
        </div>
    </div>

    <div class="card" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none;">
        <div class="card-body" style="padding: 40px;">
            <!-- Wizard Steps Indicator -->
            <div class="wizard-steps">
                <div class="wizard-step completed" data-step="1">
                    <div class="wizard-step-circle">1</div>
                    <div class="wizard-step-title">Tipe Dokter</div>
                </div>
                <div class="wizard-step completed" data-step="2">
                    <div class="wizard-step-circle">2</div>
                    <div class="wizard-step-title">Data Pasien</div>
                </div>
                <div class="wizard-step active" data-step="3">
                    <div class="wizard-step-circle">3</div>
                    <div class="wizard-step-title">Informasi Permohonan</div>
                </div>
            </div>

            <!-- Step 1: Review Tipe Dokter (Read-only) -->
            <div class="step-content" id="step-1">
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-user-md"></i>
                        Tipe Dokter Terpilih
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Tipe dokter sudah dipilih dan tidak dapat diubah saat edit.
                    </div>

                    <div class="doctor-type-selector">
                        <div class="doctor-type-card selected" data-type="{{ $item->doctor_type }}">
                            <div class="doctor-type-icon">
                                <i class="fa fa-{{ $item->doctor_type == 'lab' ? 'flask' : 'hospital' }}"></i>
                            </div>
                            <div class="doctor-type-title">
                                {{ $item->doctor_type == 'lab' ? 'Dokter Lab' : 'Dokter Rujukan' }}</div>
                            <div class="doctor-type-description">
                                {{ $item->doctor_type == 'lab' ? 'Untuk pemeriksaan laboratorium internal tanpa rujukan dari dokter luar' : 'Untuk pemeriksaan berdasarkan rujukan dari dokter pengirim dengan diagnosa' }}
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step"
                            onclick="window.location='{{ url('/elits-permohonan-uji-klinik-2') }}'">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-step" id="btn-next-step-1">
                            Lanjut <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Review Data Pasien (Read-only) -->
            <div class="step-content" id="step-2">
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-users"></i>
                        Data Pasien
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Data pasien yang sudah terpilih. Data ini tidak dapat diubah saat
                        edit.
                    </div>

                    <!-- Patient Detail Display -->
                    <div id="patient-detail-display">
                        <div class="card border-success" style="margin-top: 20px;">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-check-circle"></i> Data Pasien Terpilih</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 25%">ID Satu Sehat</th>
                                            <td>{{ $pasien->id_pasien_satu_sehat ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>NIK Pasien</th>
                                            <td><strong>{{ $pasien->nik_pasien ?? '-' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>No Rekam Medis</th>
                                            <td><strong>{{ $pasien->no_rekammedis_pasien ?? '-' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <td><strong style="font-size: 16px;">{{ mb_strtoupper($pasien->nama_pasien ?? '-', 'UTF-8') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td>{{ $pasien->gender_pasien ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Lahir</th>
                                            <td>{{ $pasien->tgllahir_pasien ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor Telepon</th>
                                            <td>{{ $pasien->phone_pasien ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($pasien) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step" id="btn-prev-step-2">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-step" id="btn-next-step-2">
                            Lanjut <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Edit Informasi Permohonan -->
            <div class="step-content active" id="step-3">
                <form action="{{ route('elits-permohonan-uji-klinik-2.update', $item->id_permohonan_uji_klinik) }}"
                    method="POST" enctype="multipart/form-data" id="form">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="doctor_type" id="doctor_type" value="{{ $item->doctor_type }}">
                    <input type="hidden" name="pasien_permohonan_uji_klinik"
                        value="{{ $item->pasien_permohonan_uji_klinik }}">

                    <!-- Informasi Dasar -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa fa-file-text"></i>
                            Informasi Dasar Permohonan
                        </div>

                        <div class="form-group">
                            <label for="code_register">No. SAMPLE <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly
                                    name="noregister_permohonan_uji_klinik" id="noregister_permohonan_uji_klinik"
                                    value="{{ $item->noregister_permohonan_uji_klinik }}">
                            </div>
                            @if(isset($numberSettings) && $numberSettings->is_nomor_spesimen_manual)
                            <div class="mt-2">
                                <div id="nomor_spesimen_manual_container" style="margin-top: 10px;">
                                    <label for="nomor_spesimen_manual" class="font-weight-bold">Input Manual Nomor Spesimen</label>
                                    <input type="text" class="form-control" 
                                        name="nomor_spesimen_manual" id="nomor_spesimen_manual"
                                        placeholder="Masukkan nomor spesimen manual"
                                        value="{{ $item->nomor_spesimen_manual ?? '' }}">
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Setting global: Input manual aktif. Kosongkan untuk menggunakan nomor otomatis.
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="nomor_lab">No. LABORATORIUM</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-flask"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly
                                    name="nomor_lab_display" id="nomor_lab_display"
                                    placeholder="No. Lab (Otomatis)" 
                                    value="{{ $item->nomer_lab ? str_pad($item->nomer_lab, 3, '0', STR_PAD_LEFT) : '' }}">
                            </div>
                            @if(isset($numberSettings) && $numberSettings->is_nomor_lab_manual)
                            <div class="mt-2">
                                <div id="nomor_lab_manual_container" style="margin-top: 10px;">
                                    <label for="nomor_lab_manual" class="font-weight-bold">Input Manual Nomor Laboratorium</label>
                                    <input type="text" class="form-control" 
                                        name="nomor_lab_manual" id="nomor_lab_manual"
                                        placeholder="Masukkan nomor laboratorium manual"
                                        value="{{ $item->nomor_lab_manual ?? '' }}">
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Setting global: Input manual aktif. Kosongkan untuk menggunakan nomor otomatis.
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="tglregister_permohonan_uji_klinik">TGL. REGISTER <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="datetime-local" class="form-control" autocomplete="on"
                                    name="tglregister_permohonan_uji_klinik" id="tglregister_permohonan_uji_klinik"
                                    value="{{ \Carbon\Carbon::parse($item->tglregister_permohonan_uji_klinik)->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="petugas_penerima">PETUGAS Registrasi</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                </div>
                                <select class="form-control" name="petugas_penerima" id="petugas_penerima">
                                    <option value="" {{ empty($verifikasi->nama_petugas) ? 'selected' : '' }}>Pilih
                                        petugas penerima</option>
                                    @foreach ($petugasPenerima as $petugas)
                                        <option value="{{ $petugas }}"
                                            {{ isset($verifikasi->nama_petugas) && $verifikasi->nama_petugas == $petugas ? 'selected' : '' }}>
                                            {{ $petugas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="umur_pasien">UMUR PASIEN</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurtahun_pasien_permohonan_uji_klinik"
                                            id="umurtahun_pasien_permohonan_uji_klinik"
                                            value="{{ $item->umurtahun_pasien_permohonan_uji_klinik }}" placeholder="0"
                                            readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Tahun</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurbulan_pasien_permohonan_uji_klinik"
                                            id="umurbulan_pasien_permohonan_uji_klinik"
                                            value="{{ $item->umurbulan_pasien_permohonan_uji_klinik }}" placeholder="0"
                                            readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            name="umurhari_pasien_permohonan_uji_klinik"
                                            id="umurhari_pasien_permohonan_uji_klinik"
                                            value="{{ $item->umurhari_pasien_permohonan_uji_klinik }}" placeholder="0"
                                            readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Metode Pembayaran</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                                </div>
                                <select class="form-control" name="metode_pembayaran">
                                    <option value=""
                                        {{ $item->metode_pembayaran === '' || $item->metode_pembayaran === null ? 'selected' : '' }}>
                                        Pilih metode pembayaran</option>
                                    <option value="0" {{ $item->metode_pembayaran == 0 ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="1" {{ $item->metode_pembayaran == 1 ? 'selected' : '' }}>Transfer
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="request_pasien_permohonan_uji_klinik">REQUEST PASIEN / KELUHAN <span class="text-muted">(Sebelum pemilihan layanan pemeriksaan)</span></label>
                            <textarea class="form-control" name="request_pasien_permohonan_uji_klinik" id="request_pasien_permohonan_uji_klinik"
                                placeholder="Masukkan request pasien atau keluhan sebelum memilih layanan pemeriksaan yang dipilihkan dokter" rows="4">{{ old('request_pasien_permohonan_uji_klinik', $item->request_pasien_permohonan_uji_klinik ?? '') }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Field ini digunakan untuk mencatat request pasien atau keluhan sebelum dokter memilih layanan pemeriksaan.
                            </small>
                        </div>
                    </div>

                    <!-- Informasi Dokter Rujukan (Only for Dokter Rujukan) -->
                    <div class="form-section" id="rujukan-fields"
                        style="{{ $item->doctor_type == 'rujukan' ? '' : 'display: none;' }}">
                        <div class="form-section-title">
                            <i class="fa fa-hospital"></i>
                            Informasi Dokter Pengirim
                        </div>

                        <div class="form-group">
                            <label for="nama_dokter_pengirim_permohonan_uji_klinik">NAMA DOKTER PENGIRIM</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user-md"></i></span>
                                </div>
                                <input type="text" class="form-control"
                                    name="nama_dokter_pengirim_permohonan_uji_klinik"
                                    id="nama_dokter_pengirim_permohonan_uji_klinik"
                                    value="{{ $item->nama_dokter_pengirim_permohonan_uji_klinik }}"
                                    placeholder="Masukkan nama dokter pengirim">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="hp_dokter_pengirim_permohonan_uji_klinik">No. HP DOKTER PENGIRIM</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control"
                                    name="hp_dokter_pengirim_permohonan_uji_klinik"
                                    id="hp_dokter_pengirim_permohonan_uji_klinik"
                                    value="{{ $item->hp_dokter_pengirim_permohonan_uji_klinik }}"
                                    placeholder="Masukkan no. hp dokter pengirim">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tipe_pemeriksaan_prolanis">TIPE PEMERIKSAAN</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-stethoscope"></i></span>
                                </div>
                                <select class="form-control" name="tipe_pemeriksaan_prolanis"
                                    id="tipe_pemeriksaan_prolanis">
                                    <option value=""
                                        {{ is_null($item->tipe_pemeriksaan_prolanis) ? 'selected' : '' }}>Pilih Tipe
                                        Pemeriksaan</option>
                                    <option value="PROLANIS DM"
                                        {{ $item->tipe_pemeriksaan_prolanis === 'PROLANIS DM' ? 'selected' : '' }}>PROLANIS
                                        DM</option>
                                    <option value="PROLANIS HT"
                                        {{ $item->tipe_pemeriksaan_prolanis === 'PROLANIS HT' ? 'selected' : '' }}>PROLANIS
                                        HT</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diagnosa_permohonan_uji_klinik">DIAGNOSA</label>
                            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
                                placeholder="Masukkan diagnosa" rows="4">{{ $item->diagnosa_permohonan_uji_klinik }}</textarea>
                        </div>
                    </div>

                    <!-- Informasi Perwakilan Dokter (Only for Dokter Lab) -->
                    <div class="form-section" id="perwakilan_dokter_form_group"
                        style="{{ $item->doctor_type == 'lab' ? '' : 'display: none;' }}">
                        <div class="form-section-title">
                            <i class="fa fa-users"></i>
                            Perwakilan Dokter
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefaultDokter"
                                name="isPerwakilanDokter" {{ $item->diagnosa_permohonan_uji_klinik ? 'checked' : '' }}>
                            <label class="form-check-label" for="flexSwitchCheckDefaultDokter"
                                style="margin-left: 12px;">
                                Menggunakan Perwakilan
                            </label>
                        </div>

                        <div id="form_perwakilan_dokter"
                            style="{{ $item->diagnosa_permohonan_uji_klinik ? 'display: block;' : 'display: none;' }}">
                            <div class="form-group">
                                <label for="diagnosa_permohonan_uji_klinik_lab">DIAGNOSA</label>
                                <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik_lab"
                                    placeholder="Masukkan diagnosa" rows="4">{{ $item->diagnosa_permohonan_uji_klinik }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Wali -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa fa-users"></i>
                            Wali (Opsional)
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                name="isPerwakilan" {{ $item->nama_perwakilan_permohonan_uji_klinik ? 'checked' : '' }}>
                            <label class="form-check-label" for="flexSwitchCheckDefault" style="margin-left: 12px;">
                                Menggunakan Wali
                            </label>
                        </div>

                        <div id="form_perwakilan"
                            style="{{ $item->nama_perwakilan_permohonan_uji_klinik ? 'display: block;' : 'display: none;' }}">
                            <div class="form-group">
                                <label for="nama_perwakian_permohonan_uji_klinik">NAMA WALI</label>
                                <input type="text" class="form-control" name="nama_perwakian_permohonan_uji_klinik"
                                    id="nama_perwakian_permohonan_uji_klinik"
                                    value="{{ $item->nama_perwakilan_permohonan_uji_klinik }}"
                                    placeholder="Masukkan nama perwakilan">
                            </div>

                            <div class="form-group">
                                <label for="gender_perwakilan_permohonan_uji_klinik">JENIS KELAMIN</label>
                                <select class="form-control" id="gender_perwakilan_permohonan_uji_klinik"
                                    name="gender_perwakilan_permohonan_uji_klinik">
                                    <option value=""
                                        {{ empty($item->gender_perwakilan_permohonan_uji_klinik) ? 'selected' : '' }}>Pilih
                                        jenis kelamin</option>
                                    <option value="L"
                                        {{ $item->gender_perwakilan_permohonan_uji_klinik == 'L' ? 'selected' : '' }}>
                                        Laki-Laki</option>
                                    <option value="P"
                                        {{ $item->gender_perwakilan_permohonan_uji_klinik == 'P' ? 'selected' : '' }}>
                                        Perempuan</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_lahir_perwakilan">TANGGAL LAHIR WALI</label>
                                <input type="text" name="tanggal_lahir_perwakilan" id="basic2"
                                    value="{{ $item->tanggal_lahir_perwakilan_permohonan_uji_klinik }}"
                                    class="form-control" />
                            </div>

                            <div class="form-group">
                                <label for="alamat_perwakilan">ALAMAT WALI</label>
                                <textarea class="form-control" name="alamat_perwakilan" id="alamat_perwakilan" rows="3">{{ $item->alamat_perwakilan_permohonan_uji_klinik }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="status_hubungan_perwakilan_permohonan_uji_klinik">STATUS HUBUNGAN DENGAN PASIEN</label>
                                <select class="form-control" id="status_hubungan_perwakilan_permohonan_uji_klinik"
                                    name="status_hubungan_perwakilan_permohonan_uji_klinik">
                                    <option value="">-- Pilih Status Hubungan --</option>
                                    <option value="Orang Tua" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                                    <option value="Suami" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Suami' ? 'selected' : '' }}>Suami</option>
                                    <option value="Istri" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Istri' ? 'selected' : '' }}>Istri</option>
                                    <option value="Anak" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Anak' ? 'selected' : '' }}>Anak</option>
                                    <option value="Wali" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Wali' ? 'selected' : '' }}>Wali</option>
                                    <option value="Lainnya" {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            <div class="form-group" id="status_hubungan_lainnya_group" style="display: {{ $item->status_hubungan_perwakilan_permohonan_uji_klinik == 'Lainnya' ? 'block' : 'none' }};">
                                <label for="status_hubungan_lainnya_permohonan_uji_klinik">KETERANGAN LAINNYA</label>
                                <input type="text" class="form-control" name="status_hubungan_lainnya_permohonan_uji_klinik"
                                    id="status_hubungan_lainnya_permohonan_uji_klinik" 
                                    value="{{ $item->status_hubungan_lainnya_permohonan_uji_klinik ?? '' }}"
                                    placeholder="Masukkan status hubungan lainnya">
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary btn-step" id="btn-prev-step-3">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-warning btn-step btn-simpan">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // ============================================
            // WIZARD NAVIGATION SCRIPT
            // ============================================
            let currentStep = 3; // Start at step 3 for edit mode

            // Step Navigation Buttons
            $('#btn-next-step-1').on('click', function() {
                goToStep(2);
            });

            $('#btn-prev-step-2').on('click', function() {
                goToStep(1);
            });

            $('#btn-next-step-2').on('click', function() {
                goToStep(3);
            });

            $('#btn-prev-step-3').on('click', function() {
                goToStep(2);
            });

            // Function to change steps
            function goToStep(step) {
                // Hide all steps
                $('.step-content').removeClass('active');
                $('.wizard-step').removeClass('active completed');

                // Show current step
                $('#step-' + step).addClass('active');

                // Update wizard indicators
                for (let i = 1; i <= step; i++) {
                    if (i < step) {
                        $('.wizard-step[data-step="' + i + '"]').addClass('completed');
                    } else if (i === step) {
                        $('.wizard-step[data-step="' + i + '"]').addClass('active');
                    }
                }

                currentStep = step;

                // Scroll to top
                $('html, body').animate({
                    scrollTop: $('.card').offset().top - 20
                }, 500);
            }

            // Form Perwakilan Toggle
            $('#flexSwitchCheckDefault').on('change', function() {
                var formPerwakilan = $('#form_perwakilan');
                if (this.checked) {
                    formPerwakilan.find('input, textarea, select').prop('disabled', false);
                    formPerwakilan.show();
                } else {
                    formPerwakilan.find('input, textarea, select').prop('disabled', true);
                    formPerwakilan.hide();
                }
            });

            // Form Perwakilan Dokter Toggle
            $('#flexSwitchCheckDefaultDokter').on('change', function() {
                var formPerwakilanDokter = $('#form_perwakilan_dokter');
                if (this.checked) {
                    formPerwakilanDokter.find('input, textarea, select').prop('disabled', false);
                    formPerwakilanDokter.show();
                } else {
                    formPerwakilanDokter.find('input, textarea, select').prop('disabled', true);
                    formPerwakilanDokter.hide();
                }
            });

            // Toggle input "Lainnya" untuk status hubungan perwakilan
            $('#status_hubungan_perwakilan_permohonan_uji_klinik').on('change', function() {
                if ($(this).val() === 'Lainnya') {
                    $('#status_hubungan_lainnya_group').show();
                    $('#status_hubungan_lainnya_permohonan_uji_klinik').prop('required', true);
                } else {
                    $('#status_hubungan_lainnya_group').hide();
                    $('#status_hubungan_lainnya_permohonan_uji_klinik').prop('required', false);
                    $('#status_hubungan_lainnya_permohonan_uji_klinik').val('');
                }
            });

            // Initialize: show input jika status sudah "Lainnya" saat page load
            if ($('#status_hubungan_perwakilan_permohonan_uji_klinik').val() === 'Lainnya') {
                $('#status_hubungan_lainnya_group').show();
            }

            // Initialize birthday picker for perwakilan
            if (typeof $.fn.bootstrapBirthday !== 'undefined') {
                setTimeout(function() {
                    $('#basic2').bootstrapBirthday({
                        dateFormat: "littleEndian"
                    });
                }, 500);
            }

            // Phone number filter for hp dokter pengirim
            $('#hp_dokter_pengirim_permohonan_uji_klinik').on('input', function() {
                this.value = this.value.replace(/[^\d]+/g, '');
            });

            // Form submission
            $('.btn-simpan').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);

                // Sync TinyMCE content to textarea before form submission
                if (typeof tinymce !== 'undefined') {
                    var editor = tinymce.get('request_pasien_permohonan_uji_klinik');
                    if (editor) {
                        editor.save(); // This syncs the content to the textarea
                    }
                }

                $button.prop('disabled', true);
                $button.html('<i class="fa fa-spinner fa-spin"></i> Updating...');

                $('#form').ajaxForm({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan || "Data berhasil diupdate!",
                                    icon: "success"
                                })
                                .then(function() {
                                    // Jika ada redirect_url, gunakan itu, jika tidak redirect ke list
                                    if (response.redirect_url) {
                                        window.location.href = response.redirect_url;
                                    } else {
                                        window.location.href = '/elits-permohonan-uji-klinik-2';
                                    }
                                });
                        } else {
                            $button.prop('disabled', false);
                            $button.html('<i class="fa fa-save"></i> Update');

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
                                    text: response.pesan || "Terjadi kesalahan!",
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html('<i class="fa fa-save"></i> Update');

                        console.error('Error:', error);
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });

                // Trigger form submission
                $('#form').submit();
            });

            // Function to initialize TinyMCE for request pasien/keluhan
            function initializeTinyMCE() {
                // Check if TinyMCE is loaded
                if (typeof tinymce === 'undefined') {
                    console.warn('TinyMCE is not loaded yet. Retrying in 500ms...');
                    setTimeout(function() {
                        initializeTinyMCE();
                    }, 500);
                    return;
                }

                // Check if element exists
                var $textarea = $('#request_pasien_permohonan_uji_klinik');
                if ($textarea.length === 0) {
                    console.warn('Textarea #request_pasien_permohonan_uji_klinik not found. Retrying in 300ms...');
                    setTimeout(function() {
                        initializeTinyMCE();
                    }, 300);
                    return;
                }

                // Remove existing TinyMCE instance if any
                var existingEditor = tinymce.get('request_pasien_permohonan_uji_klinik');
                if (existingEditor) {
                    tinymce.remove('#request_pasien_permohonan_uji_klinik');
                }

                // Initialize TinyMCE using local version
                try {
                    tinymce.init({
                        selector: '#request_pasien_permohonan_uji_klinik',
                        height: 300,
                        menubar: false,
                        theme: 'modern',
                        plugins: [
                            'advlist autolink lists link charmap',
                            'searchreplace code',
                            'insertdatetime table paste help wordcount'
                        ],
                        toolbar: 'undo redo | formatselect | bold italic underline | ' +
                            'alignleft aligncenter alignright | ' +
                            'bullist numlist | removeformat | help',
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; padding: 10px; }',
                        branding: false,
                        setup: function(editor) {
                            editor.on('init', function() {
                                console.log('✅ TinyMCE initialized for request_pasien_permohonan_uji_klinik');
                            });
                        }
                    });
                } catch (error) {
                    console.error('Error initializing TinyMCE:', error);
                }
            }

            // Wait for TinyMCE script to load
            function waitForTinyMCE(callback, maxAttempts) {
                maxAttempts = maxAttempts || 20; // 20 attempts = 10 seconds max
                var attempts = 0;
                
                function checkTinyMCE() {
                    attempts++;
                    if (typeof tinymce !== 'undefined') {
                        console.log('TinyMCE is loaded, initializing...');
                        callback();
                    } else if (attempts < maxAttempts) {
                        setTimeout(checkTinyMCE, 500);
                    } else {
                        console.error('TinyMCE failed to load after ' + maxAttempts + ' attempts');
                    }
                }
                
                checkTinyMCE();
            }

            // Initialize TinyMCE when page loads (step 3 is active by default in edit mode)
            waitForTinyMCE(function() {
                setTimeout(function() {
                    initializeTinyMCE();
                }, 500);
            });

            // Jika setting global manual aktif, field readonly bisa diubah
            @if(isset($numberSettings) && $numberSettings->is_nomor_spesimen_manual)
            $('#noregister_permohonan_uji_klinik').prop('readonly', false);
            @endif
        });
    </script>
@endsection
