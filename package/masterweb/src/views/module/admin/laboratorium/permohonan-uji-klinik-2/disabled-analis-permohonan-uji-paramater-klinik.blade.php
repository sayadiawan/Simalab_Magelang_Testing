@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik - View Hasil Analis
@endsection

@section('content')
    <style>
        .info-card {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(11, 58, 92, 0.3);
        }

        .info-card h4 {
            color: white;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h4 i {
            font-size: 24px;
        }

        .data-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .data-card h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0b3a5c;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-card h5 i {
            color: #0b3a5c;
        }

        .info-table th {
            color: #6c757d;
            font-weight: 600;
            padding: 12px 15px;
            background: #f8f9fa;
            border: none;
            width: 200px;
        }

        .info-table td {
            padding: 12px 15px;
            border: none;
            color: #212529;
            font-weight: 500;
        }

        .info-table tr {
            border-bottom: 1px solid #e9ecef;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .result-table {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            position: relative;
        }

        .result-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .result-table .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible; /* Tidak ada scroll vertikal di dalam container */
            -webkit-overflow-scrolling: touch;
            display: block;
            position: relative;
            /* Hapus max-height agar tinggi sesuai konten */
        }

        /* Pastikan parent tidak memiliki overflow yang menghalangi sticky */
        .result-table .table-responsive table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            position: relative;
        }

        .result-table thead {
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .result-table thead th {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%) !important;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: none;
            position: sticky;
            top: 0;
            z-index: 101;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        /* Pastikan tbody tidak menghalangi sticky */
        .result-table tbody {
            position: relative;
        }

        /* Fix untuk browser yang memerlukan explicit positioning */
        .result-table .table-responsive {
            contain: layout style;
        }

        /* Adjust top position when patient data is sticky - will be set by JavaScript */
        .result-table thead th.sticky-below-patient {
            top: 50px; /* Height of compact patient data */
        }

        .result-table thead th.sticky-below-patient-expanded {
            top: 400px; /* Height of expanded patient data */
        }

        .result-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .result-table tbody tr:last-child td {
            border-bottom: none;
        }

        .result-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .parameter-group-header {
            background: #f8f9fa !important;
            font-weight: 700;
            font-size: 16px;
            color: #495057;
            border-left: 4px solid #0b3a5c;
        }

        .parameter-group-header th {
            background: #f8f9fa !important;
            color: #495057 !important;
            padding: 15px !important;
        }

        .result-value {
            font-size: 15px;
            font-weight: 500;
        }

        .result-value.exceeds {
            color: #212529;
            font-weight: bold;
        }

        .keterangan-display {
            max-width: 100%;
            word-wrap: break-word;
            font-size: 13px;
            line-height: 1.6;
        }

        .keterangan-display table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .keterangan-display table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }


        /* Badge hasil — selaraskan dengan halaman verifikasi/analis */
        .result-table .badge-success {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: 500 !important;
            font-size: 12px !important;
            padding: 6px 12px !important;
            border-radius: 6px !important;
            border: none !important;
            display: inline-block !important;
        }

        .result-table .badge-success i {
            display: inline !important;
        }

        .result-table .badge-success small {
            color: #fff !important;
            display: block;
            margin-top: 2px;
        }

        .result-table .badge-danger,
        .result-table .hasil-melewati-baku-mutu {
            background-color: #dc3545 !important;
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            padding: 6px 12px !important;
            border-radius: 6px !important;
            border: none !important;
            display: inline-block !important;
        }

        .result-table .badge-danger .bm-kesimpulan-hasil,
        .result-table .hasil-melewati-baku-mutu .bm-kesimpulan-hasil,
        .result-table .bm-kesimpulan-hasil {
            color: #fff !important;
            display: block;
            margin-top: 2px;
        }

        .result-table .badge-danger .bintang-baku-mutu,
        .result-table .hasil-melewati-baku-mutu .bintang-baku-mutu,
        .result-table .bintang-baku-mutu {
            display: inline !important;
            color: #fff !important;
            font-weight: bold !important;
            font-size: 12px !important;
        }

        .result-table .badge-secondary {
            background-color: transparent !important;
            color: #6c757d !important;
            font-weight: normal !important;
            font-size: 15px !important;
            padding: 0 !important;
            border: none !important;
            display: inline !important;
        }

        /* Styling untuk tombol Simpan Kesimpulan */
        #btn-simpan-kesimpulan {
            transition: all 0.3s ease;
            border: none;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
        }

        #btn-simpan-kesimpulan:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(11, 58, 92, 0.4) !important;
            background: linear-gradient(135deg, #0d8f7f 0%, #0b3a5c 100%);
        }

        #btn-simpan-kesimpulan:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
        }

        #btn-simpan-kesimpulan:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* PDF preview full height */
        .pdf-preview-container {
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            overflow: hidden;
            height: calc(100vh - 260px);
            min-height: 720px;
            width: 100%;
            margin-top: 30px;
        }

        .pdf-preview-container iframe {
            width: 100%;
            height: 100%;
        }

        .btn-back {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Sticky Patient Data */
        .patient-data-sticky-wrapper {
            position: relative;
            z-index: 10;
            margin-bottom: 20px; /* Default margin */
        }

        /* Compact view hidden by default (before scroll) */
        .patient-data-compact {
            display: none;
        }

        .patient-data-sticky-wrapper.sticky {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            border-radius: 0;
        }

        /* Show compact view only when sticky */
        .patient-data-sticky-wrapper.sticky .patient-data-compact {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
        }

        .patient-data-sticky-wrapper.sticky.compact {
            height: 50px; /* Compact height */
            overflow: hidden;
        }

        .patient-data-sticky-wrapper.sticky.expanded {
            height: auto; /* Expanded height */
            max-height: 400px; /* Max height for expanded view */
            overflow-y: auto;
            padding-bottom: 15px;
        }

        .patient-data-sticky-wrapper.sticky .patient-data-full {
            display: none;
        }

        .patient-data-compact-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            flex: 1;
        }

        .patient-data-compact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .patient-data-compact-item i {
            font-size: 14px;
            opacity: 0.9;
        }

        .patient-data-compact-item strong {
            font-weight: 600;
            margin-right: 5px;
        }

        .patient-data-compact-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patient-data-compact-actions .btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s;
        }

        .patient-data-compact-actions .btn i {
            color: white !important;
        }

        .patient-data-compact-actions .btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .patient-data-compact-actions .btn:hover i {
            color: white !important;
        }

        .patient-data-sticky-wrapper.sticky.expanded .patient-data-compact {
            display: none;
        }

        .patient-data-sticky-wrapper.sticky.expanded .patient-data-full {
            display: block;
            width: 100%;
            padding-top: 10px;
        }

        /* Ensure compact is hidden when not sticky */
        .patient-data-sticky-wrapper:not(.sticky) .patient-data-compact {
            display: none !important;
        }

        /* Ensure full is visible when not sticky */
        .patient-data-sticky-wrapper:not(.sticky) .patient-data-full {
            display: block;
        }

        .patient-data-spacer {
            display: none;
            width: 100%;
            height: 50px; /* Default compact height */
            margin-bottom: 20px;
        }

        .patient-data-spacer.expanded {
            height: 400px; /* Height when expanded */
        }

        @media (max-width: 768px) {
            .patient-data-compact-item strong {
                display: none; /* Hide labels on small screens */
            }
            .patient-data-compact-item {
                font-size: 12px;
            }
            .patient-data-compact-actions .btn {
                padding: 3px 8px;
                font-size: 11px;
            }
        }
    </style>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan
                                        Uji Klinik
                                        Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>View Hasil Analis</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Info Card -->
    <div class="info-card">
        <h4>
            <i class="fa fa-flask"></i>
            View Hasil Analis Permohonan Uji Klinik
        </h4>
        <p style="margin: 0; opacity: 0.9;">Tampilan hasil pemeriksaan analitik yang telah disimpan</p>
    </div>

    <div class="patient-data-sticky-wrapper" id="patientDataStickyWrapper">
        <div class="patient-data-compact">
            <div class="patient-data-compact-content">
                <div class="patient-data-compact-item">
                    <i class="fa fa-user"></i>
                    <strong>Nama:</strong> {{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}
                </div>
                <div class="patient-data-compact-item">
                    <i class="fa fa-id-card"></i>
                    @php if(!isset($ks_nr)) { $ks_nr = \Smt\Masterweb\Models\KlinikNumberSettings::getSettings(); } @endphp
                    @if($ks_nr->is_nomor_lab_manual && !empty($item_permohonan_uji_klinik->nomor_lab_manual))
                        <strong>No. Lab:</strong> {{ $item_permohonan_uji_klinik->getLabNumber() }}
                    @elseif($ks_nr->is_nomor_spesimen_manual && !empty($item_permohonan_uji_klinik->nomor_spesimen_manual))
                        <strong>No. Spesimen:</strong> {{ $item_permohonan_uji_klinik->getSpesimenNumber() }}
                    @else
                        <strong>No. Reg:</strong> {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}
                    @endif
                </div>
                <div class="patient-data-compact-item">
                    <i class="fa fa-birthday-cake"></i>
                    <strong>Usia:</strong> {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik }} th
                </div>
                <div class="patient-data-compact-item">
                    <i class="fa fa-venus-mars"></i>
                    <strong>JK:</strong> {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                </div>
                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                    'info_haji' => $info_haji ?? null,
                    'mode' => 'compact',
                ])
            </div>
            <div class="patient-data-compact-actions">
                <button type="button" class="btn btn-sm" id="expandPatientData" title="Perlebar Informasi Pasien">
                    <i class="fa fa-expand-alt"></i>
                </button>
                <button type="button" class="btn btn-sm" id="minimizePatientData" title="Perkecil Informasi Pasien" style="display: none;">
                    <i class="fa fa-compress-alt"></i>
                </button>
            </div>
        </div>
        <div class="patient-data-full">
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                'info_haji' => $info_haji ?? null,
                'mode' => 'alert',
            ])
            <div class="row">
                <!-- Data Pasien - Kiri -->
                <div class="col-md-6">
                    <div class="data-card">
                        <h5>
                            <i class="fa fa-user"></i>
                            Data Pasien
                        </h5>
                        <div class="table-responsive">
                            <table class="table info-table">
                                @if($ks_nr->is_nomor_lab_manual)
                                <tr>
                                    <th width="250px">No. Lab</th>
                                    <td>{{ $item_permohonan_uji_klinik->getLabNumber() }}</td>
                                </tr>
                                @endif
                                @if($ks_nr->is_nomor_spesimen_manual)
                                <tr>
                                    <th width="250px">No. Spesimen</th>
                                    <td>{{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</td>
                                </tr>
                                @endif
                                @if(!$ks_nr->is_nomor_lab_manual && !$ks_nr->is_nomor_spesimen_manual)
                                <tr>
                                    <th width="250px">No. Register</th>
                                    <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <th width="250px">No. Rekam Medis</th>
                                    <td>
                                        {{ $item_permohonan_uji_klinik->getNoRekamMedis() }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Tgl. Register</th>
                                    <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Nama Pasien</th>
                                    <td style="text-transform: uppercase;">{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}</td>
                                </tr>
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                    'info_haji' => $info_haji ?? null,
                                    'mode' => 'table-rows',
                                ])

                                <tr>
                                    <th width="250px">Usia</th>
                                    <td>
                                        {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik .
                                            ' tahun ' .
                                            $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik .
                                            ' bulan ' .
                                            $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik .
                                            ' hari' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Jenis Kelamin</th>
                                    <td>
                                        {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Alamat Pasien</th>
                                    <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item_permohonan_uji_klinik->pasien) }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">No. Telepon</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->phone_pasien }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Pasien - Kanan -->
                <div class="col-md-6">
                    <div class="data-card">
                        <h5>
                            <i class="fa fa-info-circle"></i>
                            Informasi Tambahan
                        </h5>
                        <div class="table-responsive">
                            <table class="table info-table">
                                <tr>
                                    <th>No. Pasien</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nourut_pasien }}</td>
                                </tr>
                                <tr>
                                    <th>No. KTP</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Lahir</th>
                                    <td>
                                        {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                                            ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                                                'D MMMM Y',
                                            )
                                            : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Pengirim</th>
                                    <td>{{ $item_permohonan_uji_klinik->getNamaPengirim() }}</td>
                                </tr>
                                <tr>
                                    <th width="250px">Request Pasien / Keluhan</th>
                                    <td>{!! $item_permohonan_uji_klinik->request_pasien_permohonan_uji_klinik ?? '-' !!}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Diagnosis Dokter</th>
                                    <td>{{ $item_permohonan_uji_klinik->diagnosa_permohonan_uji_klinik ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Kondisi Pasien</th>
                                    <td>{{ $kondisi_pasien ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pengujian</th>
                                    <td>
                                        @if ($tgl_pengujian)
                                            {{ \Carbon\Carbon::parse($tgl_pengujian)->isoFormat('D MMMM Y, HH:mm') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Analis</th>
                                    <td>{{ $item_permohonan_uji_klinik->name_analis_permohonan_uji_klinik ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Verifikator</th>
                                    <td>
                                        @if (isset($verifikator_data) && $verifikator_data['nama'])
                                            {{ $verifikator_data['nama'] }}
                                            @if (isset($verifikator_data['stop_date']))
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock"></i>
                                                    Tanggal Verifikasi:
                                                    {{ \Carbon\Carbon::parse($verifikator_data['stop_date'])->isoFormat('D MMMM Y, HH:mm') }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="patient-data-spacer" id="patientDataSpacer"></div>

    <!-- Hasil Pemeriksaan -->
    <div class="result-table">
        <h5
            style="color: #495057; font-weight: 600; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #0b3a5c; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-file-alt"></i>
            Hasil Pemeriksaan
        </h5>
        <div class="table-responsive">
            @php
                $showCatatanColumn = false;
                $showKeteranganColumn = false;
                $isMeaningfulCatatan = function ($value) {
                    $text = trim(strip_tags((string) ($value ?? '')));
                    return $text !== '' && $text !== '-';
                };
                foreach ($arr_permohonan_parameter as $group) {
                    foreach ($group['item_permohonan_parameter_satuan'] ?? [] as $param) {
                        if (!empty($param['data_permohonan_uji_subsatuan_klinik'])) {
                            foreach ($param['data_permohonan_uji_subsatuan_klinik'] as $sub) {
                                if ($isMeaningfulCatatan($sub['komentar_verifikasi'] ?? null)) {
                                    $showCatatanColumn = true;
                                }
                                if (($sub['history_count'] ?? 0) > 0) {
                                    $showKeteranganColumn = true;
                                }
                            }
                        } else {
                            if ($isMeaningfulCatatan($param['komentar_verifikasi'] ?? null)) {
                                $showCatatanColumn = true;
                            }
                            if (($param['history_count'] ?? 0) > 0) {
                                $showKeteranganColumn = true;
                            }
                        }
                    }
                }
                $tableColspan = 5 + ($showCatatanColumn ? 1 : 0) + ($showKeteranganColumn ? 1 : 0);
            @endphp
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 18%">Nama Test</th>
                        <th style="width: 12%" class="text-center">Hasil</th>
                        <th style="width: 10%" class="text-center">Satuan</th>
                        <th style="width: 12%" class="text-center">Nilai Normal</th>
                        <th style="width: 12%" class="text-center">Metode</th>
                        @if ($showCatatanColumn)
                            <th style="width: 16%">Catatan</th>
                        @endif
                        @if ($showKeteranganColumn)
                            <th style="width: 20%">Keterangan</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @php
                        $no = 0;
                    @endphp

                    @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                        <tr class="parameter-group-header">
                            <th colspan="{{ $tableColspan }}">
                                <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                            </th>
                        </tr>
                        @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                            @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                                <tr>
                                    <td colspan="{{ $tableColspan }}" style="font-weight: 600; color: #495057; padding-left: 20px;">
                                        - {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                    </td>
                                </tr>

                                @php
                                    $no_sub = 0;
                                @endphp

                                @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                    <tr>
                                        <td style="padding-left: 40px;">
                                            {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $hasil_value_sub = $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '';
                                                $min_sub = $item_subsatuan_klinik['min_baku_mutu_detail_parameter_klinik'] ?? null;
                                                $max_sub = $item_subsatuan_klinik['max_baku_mutu_detail_parameter_klinik'] ?? null;
                                                $equal_sub = $item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik'] ?? null;
                                                $offset_sub = $item_subsatuan_klinik['offset_baku_mutu'] ?? 'default';
                                                $multipleBakuMutuSub = isset($item_subsatuan_klinik['multiple_baku_mutu']) && count($item_subsatuan_klinik['multiple_baku_mutu']) > 1
                                                    ? $item_subsatuan_klinik['multiple_baku_mutu']
                                                    : null;
                                                $kesimpulan_sub = $item_subsatuan_klinik['kesimpulan_baku_mutu'] ?? '';
                                                $is_normal_sub = (int) ($item_satuan_klinik['is_normal'] ?? 0);
                                                $pasien_umur_sub = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                                                $pasien_gender_sub = $item_permohonan_uji_klinik->pasien->gender_pasien ?? null;
                                                $result_badge_sub = !empty($hasil_value_sub)
                                                    ? \Smt\Masterweb\Helpers\Smt::checkBakuMutu(
                                                        $hasil_value_sub,
                                                        $min_sub,
                                                        $max_sub,
                                                        $equal_sub,
                                                        $offset_sub,
                                                        $multipleBakuMutuSub,
                                                        $kesimpulan_sub,
                                                        $pasien_umur_sub,
                                                        $pasien_gender_sub,
                                                        $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] ?? null,
                                                        $is_normal_sub
                                                    )
                                                    : '';
                                            @endphp
                                            <div id="result_output_sub_{{ $no_sub }}">
                                                @if (!empty($hasil_value_sub))
                                                    {!! $result_badge_sub ?: rubahNilaikeForm($hasil_value_sub) !!}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            {!! $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] ?? '-' !!}
                                        </td>

                                        <td class="text-center nilai-normal-cell">
                                            {!! rubahNilaikeForm($item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] ?? '-') !!}
                                        </td>

                                        <td class="text-center">
                                            {{ $item_satuan_klinik['method_permohonan_uji_parameter_klinik'] ?? '-' }}
                                        </td>

                                        @if ($showCatatanColumn)
                                            <td>
                                                @if ($isMeaningfulCatatan($item_subsatuan_klinik['komentar_verifikasi'] ?? null))
                                                    <span class="text-muted" style="white-space: pre-wrap;">{{ $item_subsatuan_klinik['komentar_verifikasi'] }}</span>
                                                @endif
                                            </td>
                                        @endif

                                        @if ($showKeteranganColumn)
                                            <td>
                                                @php
                                                    $history_count_sub = $item_subsatuan_klinik['history_count'] ?? 0;
                                                    $histories_sub = $item_subsatuan_klinik['histories'] ?? collect();
                                                    $has_pengulangan = $history_count_sub > 0;
                                                @endphp
                                                @if ($has_pengulangan)
                                                @php
                                                    $selected_history_id_sub = $item_subsatuan_klinik['selected_history_id'] ?? null;
                                                    $current_hasil_sub = $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? null;
                                                    // Cek apakah hasil saat ini ada di history atau tidak
                                                    $current_in_history = false;
                                                    if ($current_hasil_sub && $selected_history_id_sub) {
                                                        $current_in_history = $histories_sub->contains(function($h) use ($selected_history_id_sub) {
                                                            return $h->id_permohonan_uji_sub_parameter_klinik_history == $selected_history_id_sub;
                                                        });
                                                    }
                                                @endphp
                                                <div class="keterangan-display">
                                                    <strong>Pengulangan: {{ $history_count_sub }}x</strong>
                                                    @if ($histories_sub->count() > 0)
                                                        <br>
                                                        <small style="color: #6c757d;">
                                                            @foreach ($histories_sub as $index => $history)
                                                                @php
                                                                    $is_selected = $selected_history_id_sub && $selected_history_id_sub == $history->id_permohonan_uji_sub_parameter_klinik_history;
                                                                    $hasil_text = $history->hasil_permohonan_uji_sub_parameter_klinik ?? '-';
                                                                @endphp
                                                                @if ($is_selected)
                                                                    <strong style="color: #495057;">Percobaan {{ $index + 1 }}: {!! $hasil_text !!} ✓</strong><br>
                                                                @else
                                                                    Percobaan {{ $index + 1 }}: {!! $hasil_text !!}<br>
                                                                @endif
                                                            @endforeach
                                                            @if ($current_hasil_sub && !$current_in_history)
                                                                <strong style="color: #495057;">Percobaan {{ $histories_sub->count() + 1 }}: {!! $current_hasil_sub !!} ✓</strong>
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>

                                    @php
                                        $no_sub++;
                                    @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        - {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $hasil_value_param = $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '';
                                            $min_param = $item_satuan_klinik['min'] ?? null;
                                            $max_param = $item_satuan_klinik['max'] ?? null;
                                            $equal_param = $item_satuan_klinik['equal'] ?? null;
                                            $offset_param = $item_satuan_klinik['offset_baku_mutu'] ?? 'default';
                                            $multipleBakuMutuParam = isset($item_satuan_klinik['multiple_baku_mutu']) && count($item_satuan_klinik['multiple_baku_mutu']) > 1
                                                ? $item_satuan_klinik['multiple_baku_mutu']
                                                : null;
                                            $kesimpulan_param = $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '';
                                            $is_normal_param = (int) ($item_satuan_klinik['is_normal'] ?? 0);
                                            $pasien_umur_param = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                                            $pasien_gender_param = $item_permohonan_uji_klinik->pasien->gender_pasien ?? null;
                                            $result_badge_param = !empty($hasil_value_param)
                                                ? \Smt\Masterweb\Helpers\Smt::checkBakuMutu(
                                                    $hasil_value_param,
                                                    $min_param,
                                                    $max_param,
                                                    $equal_param,
                                                    $offset_param,
                                                    $multipleBakuMutuParam,
                                                    $kesimpulan_param,
                                                    $pasien_umur_param,
                                                    $pasien_gender_param,
                                                    $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? null,
                                                    $is_normal_param
                                                )
                                                : '';
                                        @endphp
                                        <div id="result_output_param_{{ $no }}">
                                            @if (!empty($hasil_value_param))
                                                {!! $result_badge_param ?: rubahNilaikeForm($hasil_value_param) !!}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        {!! $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'] ?? '-' !!}
                                    </td>

                                    <td class="text-center nilai-normal-cell">
                                        {!! rubahNilaikeForm(
                                            $item_satuan_klinik['keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik']
                                                ?? $item_satuan_klinik['nilai_baku_mutu']
                                                ?? '-'
                                        ) !!}
                                    </td>

                                    <td class="text-center">
                                        {{ $item_satuan_klinik['method_permohonan_uji_parameter_klinik'] ?? '-' }}
                                    </td>

                                    @if ($showCatatanColumn)
                                        <td>
                                            @if ($isMeaningfulCatatan($item_satuan_klinik['komentar_verifikasi'] ?? null))
                                                <span class="text-muted" style="white-space: pre-wrap;">{{ $item_satuan_klinik['komentar_verifikasi'] }}</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if ($showKeteranganColumn)
                                        <td>
                                            @php
                                                $history_count = $item_satuan_klinik['history_count'] ?? 0;
                                                $histories = $item_satuan_klinik['histories'] ?? collect();
                                                $has_pengulangan = $history_count > 0;
                                            @endphp
                                            @if ($has_pengulangan)
                                            @php
                                                $selected_history_id = $item_satuan_klinik['selected_history_id'] ?? null;
                                                $current_hasil = $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? null;
                                                // Cek apakah hasil saat ini ada di history atau tidak
                                                $current_in_history = false;
                                                if ($current_hasil && $selected_history_id) {
                                                    $current_in_history = $histories->contains(function($h) use ($selected_history_id) {
                                                        return $h->id_permohonan_uji_parameter_klinik_history == $selected_history_id;
                                                    });
                                                }
                                            @endphp
                                            <div class="keterangan-display">
                                                <strong>Pengulangan: {{ $history_count }}x</strong>
                                                @if ($histories->count() > 0)
                                                    <br>
                                                    <small style="color: #6c757d;">
                                                        @foreach ($histories as $index => $history)
                                                            @php
                                                                $is_selected = $selected_history_id && $selected_history_id == $history->id_permohonan_uji_parameter_klinik_history;
                                                                $hasil_text = $history->hasil_permohonan_uji_parameter_klinik ?? '-';
                                                            @endphp
                                                            @if ($is_selected)
                                                                <strong style="color: #495057;">Percobaan {{ $index + 1 }}: {!! $hasil_text !!} ✓</strong><br>
                                                            @else
                                                                Percobaan {{ $index + 1 }}: {!! $hasil_text !!}<br>
                                                            @endif
                                                        @endforeach
                                                        @if ($current_hasil && !$current_in_history)
                                                            <strong style="color: #495057;">Percobaan {{ $histories->count() + 1 }}: {!! $current_hasil !!} ✓</strong>
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endif

                            @php
                                $no++;
                            @endphp
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Catatan Hasil disembunyikan sesuai permintaan --}}
    {{--
    <div class="result-table" style="margin-top: 30px;">
        <h5
            style="color: #495057; font-weight: 600; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #0b3a5c; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-file-alt"></i>
            Catatan Hasil
        </h5>
        <div class="form-group">
            <textarea 
                name="catatan_hasil_display" 
                id="catatan_hasil" 
                class="form-control" 
                rows="5" 
                placeholder="Masukkan catatan hasil pemeriksaan...">{{ $item_permohonan_uji_klinik->catatan_hasil ?? '' }}</textarea>
        </div>
    </div>
    --}}

    <!-- Data Validasi -->
    <div class="result-table" style="margin-top: 30px;">
        <h5
            style="color: #495057; font-weight: 600; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #0b3a5c; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-check-circle"></i>
            Data Validasi
        </h5>
        <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
        <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
        @if (isset($existingValidasi) && $existingValidasi->is_done == 1)
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Jam Validasi</strong></label>
                        <p class="form-control-plaintext">
                            {{ \Carbon\Carbon::parse($existingValidasi->start_date)->format('H:i') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>Nama Petugas Validator</strong></label>
                        <p class="form-control-plaintext">{{ $existingValidasi->nama_petugas }}</p>
                    </div>
                </div>
            </div>
        @else
            @php
                // Prefill dari query ?jam=&petugas= (dari halaman verification → Input)
                $prefillJamValidasi = '';
                $prefillPetugasValidasi = '';
                if (request()->filled('jam')) {
                    try {
                        $rawJamQuery = trim((string) request('jam'));
                        if (preg_match('/^\d{1,2}:\d{2}$/', $rawJamQuery)) {
                            $prefillJamValidasi = strlen($rawJamQuery) === 4 ? '0' . $rawJamQuery : $rawJamQuery;
                        } else {
                            $prefillJamValidasi = \Carbon\Carbon::parse($rawJamQuery)->format('H:i');
                        }
                    } catch (\Throwable $e) {
                        $prefillJamValidasi = '';
                    }
                }
                if (request()->filled('petugas')) {
                    $prefillPetugasValidasi = trim((string) request('petugas'));
                }
                if ($prefillJamValidasi === '' && isset($existingValidasi) && $existingValidasi->start_date) {
                    $prefillJamValidasi = \Carbon\Carbon::parse($existingValidasi->start_date)->format('H:i');
                }
                if ($prefillPetugasValidasi === '' && isset($existingValidasi)) {
                    $prefillPetugasValidasi = $existingValidasi->nama_petugas ?? '';
                }
            @endphp
            <form id="form-validasi-hasil" method="POST"
                action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}">
                @csrf
                <input type="hidden" name="verification_step" value="5">
                <input type="hidden" name="is_selesai" id="is_selesai_validasi" value="0">
                <input type="hidden" name="stop_date" id="stop_date_validasi_input" value="{{ $prefillJamValidasi }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jam_validasi"><strong>Jam Validasi</strong></label>
                            <input type="text" id="jam_validasi" name="start_date" class="form-control"
                                placeholder="Pilih jam"
                                value="{{ $prefillJamValidasi }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_petugas_validasi"><strong>Nama Petugas Validator</strong></label>
                            <select name="nama_petugas" id="nama_petugas_validasi" class="form-control" required>
                                @if (!empty($petugasValidator))
                                    @foreach ($petugasValidator as $validator)
                                        <option value="{{ $validator }}"
                                            {{ $prefillPetugasValidasi == $validator ? 'selected' : '' }}>
                                            {{ $validator }}</option>
                                    @endforeach
                                    @if ($prefillPetugasValidasi !== '' && !in_array($prefillPetugasValidasi, $petugasValidator, true))
                                        <option value="{{ $prefillPetugasValidasi }}" selected>{{ $prefillPetugasValidasi }}</option>
                                    @endif
                                @else
                                    @php
                                        $day = \Carbon\Carbon::parse($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->dayName;
                                    @endphp
                                    <option value="dr. DINI NURANI KUSUMASTUTI. Sp.PK"
                                        @if ($prefillPetugasValidasi === 'dr. DINI NURANI KUSUMASTUTI. Sp.PK' || (($prefillPetugasValidasi === '') && ($day == 'Selasa' || $day == "Jum'at"))) selected @endif>Penanggung jawab Lab. Klinik</option>
                                    <option value="dr. Muharyati"
                                        @if ($prefillPetugasValidasi === 'dr. Muharyati' || (($prefillPetugasValidasi === '') && ($day != 'Selasa' && $day != "Jum'at"))) selected @endif>Diotorisasi oleh dr. Muharyati</option>
                                    @if ($prefillPetugasValidasi !== '' && !in_array($prefillPetugasValidasi, ['dr. DINI NURANI KUSUMASTUTI. Sp.PK', 'dr. Muharyati'], true))
                                        <option value="{{ $prefillPetugasValidasi }}" selected>{{ $prefillPetugasValidasi }}</option>
                                    @endif
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                    <button type="button" class="btn btn-info btn-review-hasil-validasi" style="min-width: 150px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa fa-eye mr-2"></i>Review Hasil
                    </button>
                    <button type="submit" id="btn-simpan-validasi" class="btn btn-primary" style="min-width: 150px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa fa-save mr-2"></i>Simpan Validasi
                    </button>
                    <button type="button" id="btn-selesai-validasi" class="btn btn-success" style="min-width: 150px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa fa-check-circle mr-2"></i>Selesai
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Kesimpulan Hasil -->
    <div class="result-table" style="margin-top: 30px;">
        <h5
            style="color: #495057; font-weight: 600; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #0b3a5c; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-file-alt"></i>
            Kesimpulan Hasil
        </h5>
        <form id="form-kesimpulan-hasil" method="POST" action="{{ route('elits-permohonan-uji-klinik-2.update-kesimpulan-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="catatan_hasil" id="catatan_hasil_hidden" value="{{ $item_permohonan_uji_klinik->catatan_hasil ?? '' }}">
            <div class="form-group">
                @php
                    $kesimpulanHasilValue = $item_permohonan_uji_klinik->kesimpulan_hasil;
                    if (empty(trim((string) $kesimpulanHasilValue))) {
                        $kesimpulanHasilValue = $item_permohonan_uji_klinik->catatan_hasil ?? '';
                    }
                @endphp
                <textarea 
                    name="kesimpulan_hasil" 
                    id="kesimpulan_hasil" 
                    class="form-control" 
                    rows="5" 
                    placeholder="Masukkan kesimpulan hasil pemeriksaan...">{{ $kesimpulanHasilValue }}</textarea>
            </div>
            <div class="form-group" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" class="btn btn-primary" id="btn-simpan-kesimpulan" style="min-width: 150px; padding: 10px 20px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                    <i class="fa fa-save mr-2"></i>Simpan Kesimpulan
                </button>
            </div>
        </form>
    </div>

    {{-- Preview PDF penuh hanya setelah validasi selesai; sebelum itu gunakan modal review saat klik Selesai --}}
    @if (isset($existingValidasi) && (int) $existingValidasi->is_done === 1)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border-radius: 10px 10px 0 0;">
                    <h5 style="margin: 0; padding: 15px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-file-pdf"></i>
                        Preview Hasil PDF
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Tautan sumber PDF:
                        <a href="{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', [$item_permohonan_uji_klinik->id_permohonan_uji_klinik]) }}?signoption=0"
                            target="_blank">Link</a>
                    </div>
                    <div class="pdf-preview-container">
                        <iframe
                            src="{{ route('elits-permohonan-uji-klinik-2.preview-pdf-hasil', [$item_permohonan_uji_klinik->id_permohonan_uji_klinik]) }}?embed=1"
                            frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL REVIEW / PENGATURAN HASIL (validasi) --}}
    <div class="modal fade" id="modalReviewHasilValidasi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-cog mr-2"></i>Pengaturan Hasil — Validasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Sesuaikan pengaturan tampilan sebelum membuka preview hasil pemeriksaan.
                    </p>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-text-height mr-1"></i>Ukuran Font Hasil
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">6</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="validasi-fontsize-slider"
                                min="6" max="20" step="0.5"
                                value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                            <span class="text-muted small ml-2">20</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="validasi-fontsize-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 90px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="validasi-fontsize-input"
                                    min="6" max="20" step="0.5"
                                    value="{{ $item_permohonan_uji_klinik->fontsize_hasil_permohonan_uji_klinik ?? 12 }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">pt</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="validasi-fontsize-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    @php
                        $lineHeightValidasi = $item_permohonan_uji_klinik->line_height_hasil_permohonan_uji_klinik;
                        $lineHeightValidasi = ($lineHeightValidasi === null || (float) $lineHeightValidasi === 1.5) ? 1 : $lineHeightValidasi;
                    @endphp
                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-align-justify mr-1"></i>Jarak Baris
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0.5</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="validasi-lineheight-slider"
                                min="0.5" max="3.0" step="0.1" value="{{ $lineHeightValidasi }}">
                            <span class="text-muted small ml-2">3.0</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="validasi-lineheight-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="number" class="form-control text-center font-weight-bold" id="validasi-lineheight-input"
                                    min="0.5" max="3.0" step="0.1" value="{{ $lineHeightValidasi }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">×</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="validasi-lineheight-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings', [
                        'idPrefix' => 'validasi-',
                        'item_permohonan_uji_klinik' => $item_permohonan_uji_klinik,
                    ])

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fa fa-file-alt mr-1"></i>Kop Surat
                        </label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-sm text-muted" id="validasi-kop-label-text">
                                {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input" id="validasi-toggle-kop"
                                    {{ ($item_permohonan_uji_klinik->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="validasi-toggle-kop"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info" id="validasi-btn-buka-review">
                        <i class="fa fa-spinner fa-spin mr-1 d-none" id="validasi-review-loading-icon"></i>
                        <i class="fa fa-save mr-1" id="validasi-review-save-icon"></i>
                        Simpan & Buka Review
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW HASIL (validasi) --}}
    <div class="modal fade" id="modalPreviewHasilValidasi" tabindex="-1" role="dialog" aria-hidden="true"
         data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document" style="max-width: 98vw; width: 98vw; margin: 10px auto;">
            <div class="modal-content" style="height: 95vh; display: flex; flex-direction: column;">
                <div class="modal-header py-2 bg-info text-white" style="flex-shrink: 0;">
                    <h5 class="modal-title">
                        <i class="fa fa-file-alt mr-2"></i>Preview Hasil Pemeriksaan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="flex: 1; overflow: hidden;">
                    <iframe id="preview-hasil-validasi-iframe"
                        src="about:blank"
                        style="width: 100%; height: 100%; border: none;"
                        allowfullscreen></iframe>
                </div>
                <div class="modal-footer py-2" style="flex-shrink: 0;">
                    <small class="text-muted mr-auto">
                        <i class="fa fa-info-circle mr-1"></i>
                        Review hasil sebelum menyelesaikan validasi.
                    </small>
                    <button type="button" class="btn btn-outline-warning btn-sm" id="validasi-btn-pengaturan-preview">
                        <i class="fa fa-cog mr-1"></i>Pengaturan Hasil
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="validasi-btn-preview-lanjut-selesai">
                        <i class="fa fa-check-circle mr-1"></i>Lanjutkan & Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4 mb-4">
        <div class="col-12 text-right">
            <button type="button" class="btn btn-light btn-action mr-2"
                onclick="document.location='{{ url('/elits-permohonan-uji-klinik-2/verification/' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}'">
                <i class="fa fa-arrow-left mr-2"></i>Kembali
            </button>
            <button type="button" class="btn btn-info btn-action mr-2 btn-review-hasil-validasi">
                <i class="fa fa-eye mr-2"></i>Review Hasil
            </button>
            @if (!isset($existingValidasi) || (int) $existingValidasi->is_done !== 1)
                <button type="button" class="btn btn-primary btn-action mr-2" id="btn-simpan-validasi-bottom">
                    <i class="fa fa-save mr-2"></i>Simpan Validasi
                </button>
                <button type="button" class="btn btn-success btn-action" id="btn-selesai-validasi-bottom">
                    <i class="fa fa-check-circle mr-2"></i>Selesai
                </button>
            @endif
        </div>
    </div>

    <script>
        $(document).ready(function() {
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-apply-localstorage', [
                'permohonanId' => $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                'stepKey' => 'validasi',
            ])

            // Sticky Patient Data Handler
            (function() {
                var $wrapper = $('#patientDataStickyWrapper');
                var $spacer = $('#patientDataSpacer');
                var $fullContent = $wrapper.find('.patient-data-full');
                var $compactContent = $wrapper.find('.patient-data-compact');
                var stickyOffset = 0;
                var isSticky = false;
                var isExpanded = false; // Track expanded state

                function calculateOffset() {
                    // Reset classes to get original offset
                    $wrapper.removeClass('sticky compact expanded');
                    $spacer.hide();
                    stickyOffset = $wrapper.offset().top;
                    // console.log('Calculated stickyOffset:', stickyOffset);
                }

                function updateSticky() {
                    var scrollTop = $(window).scrollTop();
                    var currentWrapperHeight = $wrapper.outerHeight();

                    if (scrollTop > stickyOffset && !isSticky) {
                        isSticky = true;
                        $wrapper.addClass('sticky compact');
                        $spacer.css({
                            'height': $compactContent.outerHeight() + 'px',
                            'display': 'block'
                        });
                        $('#expandPatientData').show();
                        $('#minimizePatientData').hide();
                    } else if (scrollTop <= stickyOffset && isSticky) {
                        isSticky = false;
                        isExpanded = false;
                        $wrapper.removeClass('sticky compact expanded');
                        $spacer.hide();
                        $('#expandPatientData').show();
                        $('#minimizePatientData').hide();
                    }

                    // Update spacer height if expanded
                    if (isSticky && isExpanded) {
                        $spacer.css('height', $fullContent.outerHeight() + 'px');
                    } else if (isSticky && !isExpanded) {
                        $spacer.css('height', $compactContent.outerHeight() + 'px');
                    }

                    // Update table header position
                    updateTableHeaderPosition();
                }

                // Update table header position when patient data becomes sticky
                function updateTableHeaderPosition() {
                    var $tableHeaders = $('.result-table thead th');
                    var $patientWrapper = $('#patientDataStickyWrapper');
                    
                    if ($patientWrapper.hasClass('sticky')) {
                        if ($patientWrapper.hasClass('expanded')) {
                            $tableHeaders.removeClass('sticky-below-patient').addClass('sticky-below-patient-expanded');
                        } else {
                            $tableHeaders.removeClass('sticky-below-patient-expanded').addClass('sticky-below-patient');
                        }
                    } else {
                        $tableHeaders.removeClass('sticky-below-patient sticky-below-patient-expanded');
                    }
                }

                // Handle expand/minimize buttons
                $('#expandPatientData').on('click', function() {
                    if (isSticky) {
                        isExpanded = true;
                        $wrapper.removeClass('compact').addClass('expanded');
                        $(this).hide();
                        $('#minimizePatientData').show();
                        // Update spacer height immediately
                        $spacer.css('height', $fullContent.outerHeight() + 'px');
                        // Update table header position
                        updateTableHeaderPosition();
                    }
                });

                $('#minimizePatientData').on('click', function() {
                    if (isSticky) {
                        isExpanded = false;
                        $wrapper.removeClass('expanded').addClass('compact');
                        $(this).hide();
                        $('#expandPatientData').show();
                        // Update spacer height immediately
                        $spacer.css('height', $compactContent.outerHeight() + 'px');
                        // Update table header position
                        updateTableHeaderPosition();
                    }
                });

                // Update on scroll
                $(window).on('scroll', function() {
                    updateSticky();
                });

                // Update on resize (offset might change)
                $(window).on('resize', function() {
                    if (!isSticky) {
                        calculateOffset();
                    }
                    updateSticky();
                });

                // Initial calculation and check
                calculateOffset();
                updateSticky();
            })();

            // === PASIENT DATA FOR BAKU MUTU SELECTION ===
            var pasienGender = '{{ $item_permohonan_uji_klinik->pasien->gender_pasien ?? "" }}';
            var pasienUmur = {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? 0 }};
            
            // Format value for display (convert ^() to HTML)
            function toFormatHtml(value) {
                if (!value) return '';
                // Ensure value is a string
                value = String(value);
                
                // Convert Unicode superscript characters to <sup> tags FIRST
                // This handles characters like ³, ², ¹, etc.
                value = value.replace(/¹/g, '<sup>1</sup>');
                value = value.replace(/²/g, '<sup>2</sup>');
                value = value.replace(/³/g, '<sup>3</sup>');
                value = value.replace(/⁴/g, '<sup>4</sup>');
                value = value.replace(/⁵/g, '<sup>5</sup>');
                value = value.replace(/⁶/g, '<sup>6</sup>');
                value = value.replace(/⁷/g, '<sup>7</sup>');
                value = value.replace(/⁸/g, '<sup>8</sup>');
                value = value.replace(/⁹/g, '<sup>9</sup>');
                value = value.replace(/⁰/g, '<sup>0</sup>');
                
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                return value;
            }

            // Create result badge based on status
            function createResultBadge(value, status, kesimpulanBakuMutu) {
                kesimpulanBakuMutu = kesimpulanBakuMutu || '';
                if (status === 'success') {
                    var kesimpulanHtml = kesimpulanBakuMutu
                        ? ' <small style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                        : '';
                    return '<span class="badge badge-success font-weight-bold" style="font-size: 14px; padding: 8px 12px;"><i class="fa fa-check-circle mr-1"></i>' + value + kesimpulanHtml + '</span>';
                }

                var kesimpulanHtml = kesimpulanBakuMutu
                    ? '<br><small class="bm-kesimpulan-hasil" style="font-size: 12px; font-weight: normal; opacity: 0.95;">' + kesimpulanBakuMutu + '</small>'
                    : '';
                return '<span class="badge badge-danger hasil-melewati-baku-mutu" style="font-size: 14px; padding: 8px 12px; font-weight: 700;"><strong>' + value + '<span class="bintang-baku-mutu"> *</span></strong>' + kesimpulanHtml + '</span>';
            }

            // Check if result exceeds baku mutu with multiple baku mutu support
            function checkBakuMutu(value, min, max, equal, offset_baku_mutu, multipleBakuMutu, kesimpulanBakuMutuParam, isNormalParam) {
                if (!value || value === '' || value === '-') return '';

                // Normalize offset_baku_mutu value (handle string comparison)
                offset_baku_mutu = String(offset_baku_mutu || 'default').trim();

                var melewati = false;
                var hasMultipleBakuMutu = multipleBakuMutu && multipleBakuMutu.length > 1;
                var isOutsideNormalRange = false;
                var kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';
                var isNormalFlag = parseInt(isNormalParam, 10);
                if (isNaN(isNormalFlag)) {
                    isNormalFlag = (multipleBakuMutu && multipleBakuMutu.length === 1)
                        ? parseInt(multipleBakuMutu[0].is_normal, 10) || 0
                        : 0;
                }

                // Check manual override FIRST (before automatic check)
                if (offset_baku_mutu === 'false') {
                    // Manual override: Tidak melewati (Memenuhi syarat) - return GREEN/SUCCESS
                    return createResultBadge(toFormatHtml(value), 'success');
                } else if (offset_baku_mutu === 'true') {
                    // Manual override: Melewati (Tidak memenuhi syarat) - return RED/DANGER
                    return createResultBadge(toFormatHtml(value), 'danger');
                } else {
                        
                    // Default: Check automatically based on min/max/equal
                    var numValue = null;
                    if (equal && equal !== '') {
                        melewati = (value !== equal);
                    } else {
                        if (min && min !== '' && max && max !== '') {
                            numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                melewati = (numValue < parseFloat(min) || numValue > parseFloat(max));
                            }
                          
                       
                        } else if (min && min !== '' &&  (max == '' || max == null || max == undefined)) {
                            numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                melewati = isNormalFlag === 1
                                    ? (numValue <= parseFloat(min))
                                    : (numValue < parseFloat(min));
                            }
                        } else if (max && max !== '' && (min == '' || min == null || min == undefined)) {
                            numValue = parseFloat(value);
                            if (!isNaN(numValue)) {
                                melewati = isNormalFlag === 1
                                    ? (numValue >= parseFloat(max))
                                    : (numValue > parseFloat(max));
                            }
                        }
                      
                    }

                    // Jika ada multiple baku mutu, cek apakah hasil di luar range normal
                    if (hasMultipleBakuMutu) {
                        // Cari semua baku mutu yang is_normal = 1
                        var normalBakuMutuList = multipleBakuMutu.filter(function(bm) {
                            return bm.is_normal == 1;
                        });

                        if (normalBakuMutuList.length > 0) {


                    console.log("cek numValue= ");
                            console.log(numValue );
                            console.log(normalBakuMutuList);
                            // Pastikan numValue sudah didefinisikan
                            if (numValue === null || isNaN(numValue)) {
                                numValue = parseFloat(value);
                            }
                            var isWithinAnyNormalRange = false;
                            var selectedBakuMutu = null;

                            // Prioritas 1: Cari yang sesuai dengan gender DAN umur pasien
                            var matchedByGenderAndUmur = normalBakuMutuList.filter(function(bm) {
                                // Gender harus match (tidak null dan sama dengan pasien)
                                var genderMatch = bm.gender_baku_mutu && bm.gender_baku_mutu === pasienGender;
                                // Umur harus match (jika ada range umur, harus sesuai)
                                var umurMatch = (bm.minimal_umur_baku_mutu !== null && bm.minimal_umur_baku_mutu !== undefined &&
                                    bm.maksimal_umur_baku_mutu !== null && bm.maksimal_umur_baku_mutu !== undefined
                                    && pasienUmur >= parseFloat(bm.minimal_umur_baku_mutu) && 
                                    pasienUmur <= parseFloat(bm.maksimal_umur_baku_mutu));

                                // Umur match tapi gender null
                                var umurMatchGenNull = (bm.minimal_umur_baku_mutu !== null && bm.minimal_umur_baku_mutu !== undefined &&
                                    bm.maksimal_umur_baku_mutu !== null && bm.maksimal_umur_baku_mutu !== undefined
                                    && pasienUmur >= parseFloat(bm.minimal_umur_baku_mutu) && 
                                    pasienUmur <= parseFloat(bm.maksimal_umur_baku_mutu))
                                    && (!bm.gender_baku_mutu || bm.gender_baku_mutu === null);

                                // Gender match tapi umur null
                                var genMatchUmurNull = (bm.gender_baku_mutu && bm.gender_baku_mutu === pasienGender) && 
                                    (!bm.minimal_umur_baku_mutu || !bm.maksimal_umur_baku_mutu);

                                return (genderMatch && umurMatch) || umurMatchGenNull || genMatchUmurNull;
                            });

                         
                           
                            

                            // Prioritas 2: Cari yang sesuai gender saja
                            var matchedByGender = normalBakuMutuList.filter(function(bm) {
                                return bm.gender_baku_mutu && bm.gender_baku_mutu === pasienGender;
                            });

                            // Prioritas 3: Cari yang sesuai umur saja
                            var matchedByUmur = normalBakuMutuList.filter(function(bm) {
                                if (bm.minimal_umur_baku_mutu !== null && bm.minimal_umur_baku_mutu !== undefined &&
                                    bm.maksimal_umur_baku_mutu !== null && bm.maksimal_umur_baku_mutu !== undefined) {
                                    return (pasienUmur >= parseFloat(bm.minimal_umur_baku_mutu) && 
                                            pasienUmur <= parseFloat(bm.maksimal_umur_baku_mutu));
                                }
                                return false;
                            });

                            // Tentukan baku mutu yang dipilih berdasarkan prioritas
                            if (matchedByGenderAndUmur.length > 0) {
                                selectedBakuMutu = matchedByGenderAndUmur[0];
                            } else if (matchedByGender.length > 0) {
                                selectedBakuMutu = matchedByGender[0];
                            } else if (matchedByUmur.length > 0) {
                                selectedBakuMutu = matchedByUmur[0];
                            } else {
                                selectedBakuMutu = normalBakuMutuList[0]; // Fallback ke yang pertama
                            }

                            
                            

                            // Gunakan selectedBakuMutu untuk pengecekan, tapi tetap cek semua range normal untuk validasi
                            var rangesToCheck = selectedBakuMutu ? [selectedBakuMutu] : normalBakuMutuList;
                            
                            // Cek apakah hasil masuk dalam range yang dipilih atau salah satu range normal
                            for (var i = 0; i < rangesToCheck.length; i++) {
                                var bakuMutuToCheck = rangesToCheck[i];
                                var isWithinThisRange = false;

                                if (!isNaN(numValue)) {
                                    if (bakuMutuToCheck.min && bakuMutuToCheck.max) {
                                        isWithinThisRange = (numValue >= parseFloat(bakuMutuToCheck.min) &&
                                            numValue <= parseFloat(bakuMutuToCheck.max));
                                    } else if (bakuMutuToCheck.min) {
                                        isWithinThisRange = (numValue >= parseFloat(bakuMutuToCheck.min));
                                    } else if (bakuMutuToCheck.max) {
                                        isWithinThisRange = (numValue <= parseFloat(bakuMutuToCheck.max));
                                    }
                                }

                                if (bakuMutuToCheck.equal) {
                                    isWithinThisRange = (value === bakuMutuToCheck.equal);
                                }

                                if (isWithinThisRange) {
                                    isWithinAnyNormalRange = true;
                                    // Gunakan kesimpulan dari baku mutu yang match
                                    if (bakuMutuToCheck.kesimpulan_baku_mutu) {
                                        kesimpulanBakuMutu = bakuMutuToCheck.kesimpulan_baku_mutu;
                                    }
                                    break;
                                }
                            }
                            // Jika tidak masuk dalam range yang dipilih, cek apakah masuk dalam range normal lainnya
                            // if (!isWithinAnyNormalRange) {
                            //     for (var i = 0; i < normalBakuMutuList.length; i++) {
                            //         var normalBakuMutu = normalBakuMutuList[i];
                            //         var isWithinThisRange = false;

                            //         if (!isNaN(numValue)) {
                            //             if (normalBakuMutu.min && normalBakuMutu.max) {
                            //                 isWithinThisRange = (numValue >= parseFloat(normalBakuMutu.min) &&
                            //                     numValue <= parseFloat(normalBakuMutu.max));
                            //             } else if (normalBakuMutu.min) {
                            //                 isWithinThisRange = (numValue >= parseFloat(normalBakuMutu.min));
                            //             } else if (normalBakuMutu.max) {
                            //                 isWithinThisRange = (numValue <= parseFloat(normalBakuMutu.max));
                            //             }
                            //         }

                            //         if (normalBakuMutu.equal) {
                            //             isWithinThisRange = (value === normalBakuMutu.equal);
                            //         }

                            //         if (isWithinThisRange) {
                            //             isWithinAnyNormalRange = true;
                            //             if (normalBakuMutu.kesimpulan_baku_mutu) {
                            //                 kesimpulanBakuMutu = normalBakuMutu.kesimpulan_baku_mutu;
                            //             }
                            //             break;
                            //         }
                            //     }
                            // }

                            // Jika TIDAK masuk dalam range normal manapun, berarti di luar range
                            isOutsideNormalRange = !isWithinAnyNormalRange;
                            melewati = !isWithinAnyNormalRange;
                        }
                    }


                    // Jika belum di-set kesimpulan dan tidak ada multiple baku mutu, gunakan dari parameter
                    if (!hasMultipleBakuMutu && !kesimpulanBakuMutu) {
                        kesimpulanBakuMutu = kesimpulanBakuMutuParam || '';
                    }

                  
                    
                    
                    var status = melewati ? 'danger' : 'success';
                    return createResultBadge(toFormatHtml(value), status, kesimpulanBakuMutu);
                }
            }



            // Initialize all result displays with multiple baku mutu support
            setTimeout(function() {
                $('[id^="result_output_"]').each(function() {

                    var $resultDiv = $(this);
                    var resultId = $resultDiv.attr('id');

                    // Skip if already processed (not showing "Menghitung...")
                    if (!$resultDiv.text().includes('Menghitung...') && $resultDiv.html().trim() !==
                        '') {
                        return;
                    }

                    // Get data from data attributes if available
                    var multipleBakuMutuData = $resultDiv.attr('data-multiple-baku-mutu');
                    var multipleBakuMutu = null;

                    if (multipleBakuMutuData) {
                        try {
                            multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                            console.log("cek multipleBakuMutu= ");
                            console.log(multipleBakuMutu);
                        } catch (e) {
                            console.error('Error parsing multiple baku mutu data:', e);
                            multipleBakuMutu = null;
                        }
                    }

                    var value = String($resultDiv.data('value') || '').trim();
                    var min = String($resultDiv.data('min') || '');
                    var max = String($resultDiv.data('max') || '');
                    var equal = String($resultDiv.data('equal') || '');
                    var offsetBakuMutu = String($resultDiv.data('offset-baku-mutu') || 'default');
                    var kesimpulanBakuMutu = String($resultDiv.data('kesimpulan-baku-mutu') || '');

                    if (value && value !== '' && value !== '-') {
                        var output = checkBakuMutu(value, min, max, equal, offsetBakuMutu,
                            multipleBakuMutu, kesimpulanBakuMutu);
                        $resultDiv.html(output || '<span class="text-muted">-</span>');
                    } else {
                        $resultDiv.html('<span class="text-muted">-</span>');
                    }
                });
            }, 500);

            // Fallback: Jika masih ada "Menghitung..." setelah 1 detik, ganti dengan fallback
            setTimeout(function() {
                $('[id^="result_output_"]').each(function() {
                    var $resultDiv = $(this);
                    if ($resultDiv.text().includes('Menghitung...')) {
                        var value = String($resultDiv.data('value') || '').trim();
                        var multipleBakuMutuData = $resultDiv.attr('data-multiple-baku-mutu');
                        var multipleBakuMutu = null;

                        if (multipleBakuMutuData) {
                            try {
                                multipleBakuMutu = JSON.parse(multipleBakuMutuData);

                               
                            } catch (e) {
                                console.error('Error parsing multiple baku mutu data:', e);
                            }
                        }

                        var min = String($resultDiv.data('min') || '');
                        var max = String($resultDiv.data('max') || '');
                        var equal = String($resultDiv.data('equal') || '');
                        var offsetBakuMutu = String($resultDiv.data('offset-baku-mutu') ||
                            'default');
                        var kesimpulanBakuMutu = String($resultDiv.data('kesimpulan-baku-mutu') ||
                            '');

                        if (value && value !== '' && value !== '-') {
                            var output = checkBakuMutu(value, min, max, equal, offsetBakuMutu,
                                multipleBakuMutu, kesimpulanBakuMutu);
                            $resultDiv.html(output || '<span class="text-muted">-</span>');
                        } else {
                            $resultDiv.html('<span class="text-muted">-</span>');
                        }
                    }
                });
            }, 1000);
        });

        // Initialize TinyMCE for Catatan Hasil dinonaktifkan karena field disembunyikan
        // function initCatatanHasilTinyMCE() {
        //     // Check if TinyMCE is fully ready
        //     if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function' ||
        //         typeof tinymce.util === 'undefined' || typeof tinymce.EditorManager === 'undefined') {
        //         console.log('TinyMCE not ready yet, retrying...');
        //         setTimeout(initCatatanHasilTinyMCE, 300);
        //         return;
        //     }
        //
        //     // Check if editor already exists
        //     if (tinymce.get('catatan_hasil')) {
        //         console.log('TinyMCE editor for catatan_hasil already exists');
        //         return;
        //     }
        //
        //     var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
        //     if (tinymce.baseURL === undefined || 
        //         tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
        //         tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
        //         tinymce.baseURL = tinymceBasePath;
        //     }
        //
        //     if ($('#catatan_hasil').length > 0) {
        //         try {
        //             tinymce.init({
        //                 selector: '#catatan_hasil',
        //                 height: 300,
        //                 menubar: false,
        //                 theme: 'modern',
        //                 content_css: false,
        //                 document_base_url: window.location.origin,
        //                 plugins: [
        //                     'lists charmap',
        //                     'searchreplace',
        //                     'paste'
        //                 ],
        //                 toolbar: 'bold italic underline | superscript subscript | charmap | ' +
        //                     'bullist numlist | removeformat',
        //                 paste_as_text: true,
        //                 content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; }',
        //                 charmap_append: [
        //                     [0x00B1, 'plus-minus sign'],
        //                     [0x00B2, 'superscript two'],
        //                     [0x00B3, 'superscript three'],
        //                     [0x00B5, 'micro sign'],
        //                     [0x2264, 'less-than or equal to'],
        //                     [0x2265, 'greater-than or equal to'],
        //                     [0x2248, 'almost equal to'],
        //                     [0x2260, 'not equal to'],
        //                     [0x00B0, 'degree sign'],
        //                     [0x2103, 'degree celsius'],
        //                     [0x00D7, 'multiplication sign'],
        //                     [0x00F7, 'division sign'],
        //                     [0x03B1, 'greek small letter alpha'],
        //                     [0x03B2, 'greek small letter beta'],
        //                     [0x03B3, 'greek small letter gamma'],
        //                     [0x03BC, 'greek small letter mu']
        //                 ],
        //                 setup: function(editor) {
        //                     editor.on('init', function() {
        //                         console.log('TinyMCE editor for catatan_hasil initialized');
        //                     });
        //                     
        //                     editor.on('blur', function() {
        //                         // Sync content to textarea and hidden field for form submission
        //                         var content = editor.getContent();
        //                         $('#catatan_hasil').val(content);
        //                         $('#catatan_hasil_hidden').val(content);
        //                     });
        //                 }
        //             });
        //         } catch(e) {
        //             console.error('Error initializing TinyMCE for catatan_hasil:', e);
        //             setTimeout(initCatatanHasilTinyMCE, 500);
        //         }
        //     }
        // }

        // Initialize TinyMCE for Kesimpulan Hasil
        function initKesimpulanHasilTinyMCE() {
            // Check if TinyMCE is fully ready (cukup pastikan objek & init tersedia)
            if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                console.log('TinyMCE not ready yet, retrying...');
                setTimeout(initKesimpulanHasilTinyMCE, 300);
                return;
            }

            // Check if editor already exists
            if (tinymce.get('kesimpulan_hasil')) {
                console.log('TinyMCE editor for kesimpulan_hasil already exists');
                return;
            }

            var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
            if (tinymce.baseURL === undefined || 
                tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                tinymce.baseURL = tinymceBasePath;
            }

            if ($('#kesimpulan_hasil').length > 0) {
                try {
                    tinymce.init({
                        selector: '#kesimpulan_hasil',
                        height: 300,
                        menubar: false,
                        theme: 'modern',
                        content_css: false,
                        document_base_url: window.location.origin,
                        plugins: [
                            'lists charmap',
                            'searchreplace',
                            'paste'
                        ],
                        toolbar: 'bold italic underline | superscript subscript | charmap | ' +
                            'bullist numlist | removeformat',
                        paste_as_text: true,
                        content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; }',
                        charmap_append: [
                            [0x00B1, 'plus-minus sign'],
                            [0x00B2, 'superscript two'],
                            [0x00B3, 'superscript three'],
                            [0x00B5, 'micro sign'],
                            [0x2264, 'less-than or equal to'],
                            [0x2265, 'greater-than or equal to'],
                            [0x2248, 'almost equal to'],
                            [0x2260, 'not equal to'],
                            [0x00B0, 'degree sign'],
                            [0x2103, 'degree celsius'],
                            [0x00D7, 'multiplication sign'],
                            [0x00F7, 'division sign'],
                            [0x03B1, 'greek small letter alpha'],
                            [0x03B2, 'greek small letter beta'],
                            [0x03B3, 'greek small letter gamma'],
                            [0x03BC, 'greek small letter mu']
                        ],
                        setup: function(editor) {
                            editor.on('init', function() {
                                console.log('TinyMCE editor for kesimpulan_hasil initialized');
                            });
                            
                            editor.on('blur', function() {
                                // Sync content to textarea for form submission
                                var content = editor.getContent();
                                $('#kesimpulan_hasil').val(content);
                            });
                        }
                    });
                } catch(e) {
                    console.error('Error initializing TinyMCE for kesimpulan_hasil:', e);
                    setTimeout(initKesimpulanHasilTinyMCE, 500);
                }
            }
        }
        
        // Initialize after a short delay to ensure TinyMCE is loaded
        // setTimeout(initCatatanHasilTinyMCE, 500);
        // Halaman disabled: TinyMCE tetap diaktifkan, dengan retry + fallback aman.
        setTimeout(initKesimpulanHasilTinyMCE, 500);
        setTimeout(function() {
            if (typeof tinymce !== 'undefined' && !tinymce.get('kesimpulan_hasil')) {
                initKesimpulanHasilTinyMCE();
            }
        }, 1500);

        // Fallback: jika TinyMCE gagal init dan textarea terlanjur di-hide,
        // tampilkan kembali textarea Kesimpulan Hasil.
        setTimeout(function() {
            var $kesimpulan = $('#kesimpulan_hasil');
            if ($kesimpulan.length === 0) return;

            var hasTinyEditor = (typeof tinymce !== 'undefined' && tinymce.get('kesimpulan_hasil'));
            if (!hasTinyEditor) {
                $kesimpulan
                    .css('display', 'block')
                    .removeClass('mce-hidden hiddenfocus')
                    .removeAttr('hidden aria-hidden');
            }
        }, 1500);

        // Inisialisasi Flatpickr untuk jam validasi (jangan biarkan error menghentikan handler Selesai)
        if (document.getElementById('jam_validasi')) {
            var existingJamValidasi = document.getElementById('jam_validasi').value;
            try {
                if (typeof flatpickr !== 'undefined') {
                    flatpickr('#jam_validasi', {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: 'H:i',
                        time_24hr: true,
                        defaultDate: existingJamValidasi ? existingJamValidasi : new Date(),
                        onChange: function(selectedDates, dateStr) {
                            document.getElementById('stop_date_validasi_input').value = dateStr;
                        }
                    });
                }
            } catch (e) {
                console.warn('Flatpickr jam validasi gagal diinisialisasi:', e);
            }
            if (!existingJamValidasi) {
                var now = new Date();
                var hh = String(now.getHours()).padStart(2, '0');
                var mm = String(now.getMinutes()).padStart(2, '0');
                document.getElementById('jam_validasi').value = hh + ':' + mm;
                document.getElementById('stop_date_validasi_input').value = hh + ':' + mm;
            } else {
                document.getElementById('stop_date_validasi_input').value = existingJamValidasi;
            }
        }

        // ============================================================
        // Validasi: Simpan / Selesai → review print (bukan PDF penuh langsung)
        // ============================================================
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.review-hasil-margin-settings-script')
        (function() {
            window.permohonanCreatedIso = @json(\Carbon\Carbon::parse($item_permohonan_uji_klinik->created_at ?? $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('Y-m-d'));
            var $form = $('#form-validasi-hasil');

            var saveFontsizeUrl = '{{ route('elits-permohonan-uji-klinik-2.save-fontsize-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}';
            var previewUrl = '{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}?mode=preview';
            var csrfToken = '{{ csrf_token() }}';

            var $slider = $('#validasi-fontsize-slider');
            var $input = $('#validasi-fontsize-input');
            var $lhSlider = $('#validasi-lineheight-slider');
            var $lhInput = $('#validasi-lineheight-input');
            var $btnBuka = $('#validasi-btn-buka-review');
            var $loadingIcon = $('#validasi-review-loading-icon');
            var $saveIcon = $('#validasi-review-save-icon');
            var $toggleKop = $('#validasi-toggle-kop');
            var $kopLabel = $('#validasi-kop-label-text');

            var currentFontsize = parseFloat($slider.val()) || 12;
            var currentLineHeight = parseFloat($lhSlider.val()) || 1;
            var currentShowKop = $toggleKop.is(':checked') ? 1 : 0;

            var marginSettings = typeof initReviewHasilMarginSettings === 'function'
                ? initReviewHasilMarginSettings('validasi-', function() { $btnBuka.prop('disabled', false); })
                : null;

            function syncStopDate() {
                var jam = $('#jam_validasi').val() || '';
                if (!jam) {
                    return;
                }
                if (/^\d{1,2}:\d{2}$/.test(jam.trim()) && window.permohonanCreatedIso) {
                    var parts = window.permohonanCreatedIso.split('-');
                    if (parts.length === 3) {
                        var composed = parts[2] + '/' + parts[1] + '/' + parts[0] + ' ' + jam.trim();
                        $('#jam_validasi').val(composed);
                        $('#stop_date_validasi_input').val(composed);
                        return;
                    }
                }
                $('#stop_date_validasi_input').val(jam);
            }

            function validateValidasiForm() {
                syncStopDate();
                var jam = $('#jam_validasi').val();
                var petugas = $('#nama_petugas_validasi').val();
                if (!jam) {
                    swal({ title: 'Peringatan!', text: 'Jam validasi wajib diisi.', icon: 'warning', button: 'OK' });
                    return false;
                }
                if (!petugas) {
                    swal({ title: 'Peringatan!', text: 'Nama petugas validator wajib dipilih.', icon: 'warning', button: 'OK' });
                    return false;
                }
                return true;
            }

            function updateFontsizeUI(val) {
                val = Math.min(20, Math.max(6, parseFloat(val) || 12));
                val = Math.round(val * 2) / 2;
                $slider.val(val);
                $input.val(val);
                currentFontsize = val;
            }

            function updateLineHeightUI(val) {
                val = Math.min(3.0, Math.max(0.5, parseFloat(val) || 1));
                val = Math.round(val * 10) / 10;
                $lhSlider.val(val);
                $lhInput.val(val);
                currentLineHeight = val;
            }

            function updateKopUI(checked) {
                currentShowKop = checked ? 1 : 0;
                $kopLabel.text(checked ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)');
            }

            updateFontsizeUI(currentFontsize);
            updateLineHeightUI(currentLineHeight);
            updateKopUI($toggleKop.is(':checked'));

            $slider.on('input change', function() { updateFontsizeUI($(this).val()); });
            $input.on('input change', function() { updateFontsizeUI($(this).val()); });
            $('#validasi-fontsize-minus').on('click', function() { updateFontsizeUI(currentFontsize - 0.5); });
            $('#validasi-fontsize-plus').on('click', function() { updateFontsizeUI(currentFontsize + 0.5); });
            $lhSlider.on('input change', function() { updateLineHeightUI($(this).val()); });
            $lhInput.on('input change', function() { updateLineHeightUI($(this).val()); });
            $('#validasi-lineheight-minus').on('click', function() { updateLineHeightUI(currentLineHeight - 0.1); });
            $('#validasi-lineheight-plus').on('click', function() { updateLineHeightUI(currentLineHeight + 0.1); });
            $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });

            function openPreviewValidasi(modeSelesai) {
                var url = previewUrl + '&t=' + Date.now();
                $('#preview-hasil-validasi-iframe').attr('src', url);
                $('#modalPreviewHasilValidasi').data('mode-selesai', modeSelesai);
                if (modeSelesai) {
                    $('#validasi-btn-preview-lanjut-selesai').removeClass('d-none');
                } else {
                    $('#validasi-btn-preview-lanjut-selesai').addClass('d-none');
                }
                $('#modalPreviewHasilValidasi').modal('show');
            }

            function saveSettingsThen(callback) {
                var marginValues = marginSettings ? marginSettings.getValues() : {};
                var kesimpulanVal = $('#kesimpulan_hasil').val() || '';
                var catatanVal = $('#catatan_hasil_hidden').val() || '';

                return $.ajax({
                    url: saveFontsizeUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        fontsize: currentFontsize,
                        line_height: currentLineHeight,
                        padding: marginValues.padding,
                        padding_top: marginValues.padding_top,
                        padding_bottom: marginValues.padding_bottom,
                        margin_left: marginValues.margin_left,
                        margin_right: marginValues.margin_right,
                        lebar_kolom_pemeriksaan: marginValues.lebar_kolom_pemeriksaan,
                        lebar_kolom_hasil: marginValues.lebar_kolom_hasil,
                        lebar_kolom_satuan: marginValues.lebar_kolom_satuan,
                        lebar_kolom_metode: marginValues.lebar_kolom_metode,
                        lebar_kolom_nilai_normal: marginValues.lebar_kolom_nilai_normal,
                        show_kop: currentShowKop,
                        kesimpulan_hasil: kesimpulanVal,
                        catatan_hasil: catatanVal
                    }
                }).done(function(response) {
                    if (response && response.status === false) {
                        swal('Gagal', response.pesan || 'Gagal menyimpan pengaturan.', 'error');
                        return;
                    }
                    if (typeof callback === 'function') {
                        callback();
                    }
                }).fail(function() {
                    swal('Gagal', 'Terjadi kesalahan saat menyimpan pengaturan.', 'error');
                });
            }

            function triggerDirectPreviewValidasi(modeSelesai) {
                var $btnReview = $('.btn-review-hasil-validasi');
                var $btnSelesai = $('#btn-selesai-validasi, #btn-selesai-validasi-bottom');
                $btnReview.prop('disabled', true);
                $btnSelesai.prop('disabled', true);

                saveSettingsThen(function() {
                    openPreviewValidasi(modeSelesai);
                }).always(function() {
                    $btnReview.prop('disabled', false);
                    $btnSelesai.prop('disabled', false);
                });
            }
            window.triggerDirectPreviewValidasi = triggerDirectPreviewValidasi;

            // Simpan Validasi: langsung submit (is_selesai = 0) → VerificationActivitySample step 5
            if ($form.length) {
                $form.on('submit', function() {
                    syncStopDate();
                    if ($('#is_selesai_validasi').val() !== '1') {
                        $('#is_selesai_validasi').val('0');
                    }
                });
            }

            // Review Hasil: tampilkan preview tanpa mode selesai
            $('.btn-review-hasil-validasi').on('click', function() {
                triggerDirectPreviewValidasi(false);
            });

            // Selesai: buka review print dulu (dengan tombol Lanjutkan & Selesai)
            $('#btn-selesai-validasi, #btn-selesai-validasi-bottom').on('click', function() {
                if ($form.length && !validateValidasiForm()) {
                    return;
                }
                triggerDirectPreviewValidasi(true);
            });

            $('#btn-simpan-validasi-bottom').on('click', function() {
                if ($form.length) {
                    $form.submit();
                }
            });

            $('#validasi-btn-buka-review').on('click', function() {
                $btnBuka.prop('disabled', true);
                $loadingIcon.removeClass('d-none');
                $saveIcon.addClass('d-none');
                saveSettingsThen(function() {
                    $('#modalReviewHasilValidasi').modal('hide');
                    openPreviewValidasi($('#modalReviewHasilValidasi').data('mode-selesai') || false);
                }).always(function() {
                    $btnBuka.prop('disabled', false);
                    $loadingIcon.addClass('d-none');
                    $saveIcon.removeClass('d-none');
                });
            });

            $('#validasi-btn-pengaturan-preview').on('click', function() {
                var modeSelesai = $('#modalPreviewHasilValidasi').data('mode-selesai') || false;
                $('#modalReviewHasilValidasi').data('mode-selesai', modeSelesai);
                $('#modalPreviewHasilValidasi').one('hidden.bs.modal', function() {
                    $('#modalReviewHasilValidasi').modal('show');
                });
                $('#modalPreviewHasilValidasi').modal('hide');
            });

            // Setelah review: simpan validasi selesai → redirect ke halaman verification + popup
            $('#validasi-btn-preview-lanjut-selesai').on('click', function() {
                if ($form.length && !validateValidasiForm()) {
                    return;
                }
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');
                syncStopDate();
                $('#is_selesai_validasi').val('1');
                // Native submit agar benar-benar POST (jQuery .trigger('submit') kadang tidak mengirim form)
                if ($form.length && $form[0] && typeof $form[0].submit === 'function') {
                    $form[0].submit();
                } else if ($form.length) {
                    $form.trigger('submit');
                } else {
                    $('#modalPreviewHasilValidasi').modal('hide');
                }
            });
        })();

        // Handler untuk tombol Simpan Kesimpulan
        $('#btn-simpan-kesimpulan').on('click', function() {
            // Sync TinyMCE content to textarea before submit
            // if (typeof tinymce !== 'undefined' && tinymce.get('catatan_hasil')) {
            //     var editor = tinymce.get('catatan_hasil');
            //     if (editor) {
            //         editor.save();
            //         var content = editor.getContent();
            //         $('#catatan_hasil').val(content);
            //         $('#catatan_hasil_hidden').val(content);
            //     }
            // }
            
            if (typeof tinymce !== 'undefined' && tinymce.get('kesimpulan_hasil')) {
                var editor = tinymce.get('kesimpulan_hasil');
                if (editor) {
                    editor.save();
                    var content = editor.getContent();
                    $('#kesimpulan_hasil').val(content);
                }
            }

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Menyimpan...');

            $.ajax({
                url: $('#form-kesimpulan-hasil').attr('action'),
                type: 'POST',
                data: $('#form-kesimpulan-hasil').serialize(),
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.status) {
                        swal({
                            title: "Berhasil!",
                            text: response.pesan || "Kesimpulan hasil berhasil disimpan.",
                            icon: "success",
                            button: "OK"
                        });
                    } else {
                        swal({
                            title: "Error!",
                            text: response.pesan || "Terjadi kesalahan saat menyimpan.",
                            icon: "error",
                            button: "OK"
                        });
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var errorMsg = xhr.responseJSON && xhr.responseJSON.pesan 
                        ? xhr.responseJSON.pesan 
                        : 'Terjadi kesalahan saat menyimpan.';
                    swal({
                        title: "Error!",
                        text: errorMsg,
                        icon: "error",
                        button: "OK"
                    });
                }
            });
        });
    </script>
@endsection
