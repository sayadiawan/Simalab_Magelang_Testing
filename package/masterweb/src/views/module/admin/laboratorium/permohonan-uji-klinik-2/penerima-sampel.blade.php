@extends('masterweb::template.admin.layout')
@section('title')
    Penerima Sampel
@endsection


@section('content')
    <link href="{{ asset('assets/admin/cdn-local/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/admin/cdn-local/js/select2.min.js') }}"></script>

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

        .form-section {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .form-section h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0b3a5c;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section h5 i {
            color: #0b3a5c;
            font-size: 22px;
        }

        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #0b3a5c;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25);
        }

        .quality-checkbox-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 24px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 16px 20px;
            margin-top: 10px;
            pointer-events: auto !important;
        }

        .quality-checkbox-col {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .quality-checkbox-group .form-check {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            min-height: auto;
            pointer-events: auto !important;
        }

        .quality-checkbox-group .form-check-input {
            position: static;
            float: none;
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            margin: 0 8px 0 0;
            cursor: pointer;
            pointer-events: auto !important;
        }

        .quality-checkbox-group .form-check-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
            font-weight: 500;
            color: #495057;
            cursor: pointer;
            white-space: nowrap;
            pointer-events: auto !important;
        }

        .btn-action {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary.btn-action {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        .btn-primary.btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 58, 92, 0.4);
        }

        .btn-light.btn-action {
            border: 1px solid #dee2e6;
        }

        .btn-light.btn-action:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        .badge-custom {
            background: #0b3a5c;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
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
                                <li class="breadcrumb-item active" aria-current="page"><span>Penerima Sampel</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="info-card">
                <h4>
                    <i class="fa fa-flask"></i>
                    Penerima Sampel
                </h4>
                <p style="margin: 0; opacity: 0.9;">Formulir untuk menerima dan mencatat kondisi sampel yang diterima dari
                    pengirim</p>
            </div>

            <form
                action="{{ route('elits-permohonan-uji-klinik-2.store-penerima-sampel', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
                method="POST" enctype="multipart/form-data" id="form">
                @csrf
                @method('PUT')

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                    'info_haji' => $info_haji ?? null,
                    'mode' => 'alert',
                ])

                <!-- Data Pasien Section -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="data-card">
                            <h5>
                                <i class="fa fa-user"></i>
                                Data Pasien
                            </h5>
                            <div class="table-responsive">
                                <table class="table info-table">
                                    @php if(!isset($ks_nr)) { $ks_nr = \Smt\Masterweb\Models\KlinikNumberSettings::getSettings(); } @endphp
                                    @if($ks_nr->is_nomor_lab_manual)
                                    <tr>
                                        <th>No. Lab</th>
                                        <td>{{ $item_permohonan_uji_klinik->getLabNumber() }}</td>
                                    </tr>
                                    @endif
                                    @if($ks_nr->is_nomor_spesimen_manual)
                                    <tr>
                                        <th>No. Spesimen</th>
                                        <td>{{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</td>
                                    </tr>
                                    @endif
                                    @if(!$ks_nr->is_nomor_lab_manual && !$ks_nr->is_nomor_spesimen_manual)
                                    <tr>
                                        <th>No. Register</th>
                                        <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>No. Rekam Medis</th>
                                        <td>
                                            {{ $item_permohonan_uji_klinik->getNoRekamMedis() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tgl. Register</th>
                                        <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <td style="text-transform: uppercase;">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
                                    </tr>
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                        'info_haji' => $info_haji ?? null,
                                        'mode' => 'table-rows',
                                        'thWidth' => null,
                                    ])
                                    <tr>
                                        <th>Usia</th>
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
                                        <th>Jenis Kelamin</th>
                                        <td>
                                            {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Alamat Pasien</th>
                                        <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item_permohonan_uji_klinik->pasien) }}</td>
                                    </tr>
                                    <tr>
                                        <th>No. Telepon</th>
                                        <td>{{ $item_permohonan_uji_klinik->pasien->phone_pasien }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

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
                                        <th>Jenis Sampel</th>
                                        <td>
                                            @php
                                                $jenis_sampel_selected_preview = [];
                                                if (!empty($jenis_sampel)) {
                                                    if (is_string($jenis_sampel)) {
                                                        $jenis_sampel_selected_preview = array_values(array_filter(array_map('trim', explode(',', $jenis_sampel))));
                                                    } elseif (is_array($jenis_sampel)) {
                                                        $jenis_sampel_selected_preview = $jenis_sampel;
                                                    }
                                                }
                                                $jenis_sampel_options_preview = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra($jenis_sampel_selected_preview);
                                            @endphp
                                            <select class="form-control" name="jenis_sampel[]" id="jenis_sampel_penerima" multiple required>
                                                @foreach ($jenis_sampel_options_preview as $option)
                                                    <option value="{{ $option }}"
                                                        {{ in_array($option, $jenis_sampel_selected_preview, true) ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Opsi dari master data Jenis Sampel Klinik. Ubah pilihan untuk menampilkan form penerimaan per jenis.</small>
                                        </td>
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
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pemeriksaan Yang Diujikan Card -->
                <div class="card shadow-sm mb-4" style="border-left: 4px solid #28a745;">
                    <div class="card-header bg-gradient-success text-white"
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <h5 class="mb-0"><i class="fa fa-microscope mr-2"></i>Pemeriksaan Yang Diujikan</h5>
                    </div>
                    <div class="card-body">
                        @if (isset($parameters) && count($parameters) > 0)
                            @php
                                // Group parameters by package name
                                $groupedParams = [];
                                foreach ($parameters as $param) {
                                    $packageName = $param->nama_paket ?? '-';
                                    if (!isset($groupedParams[$packageName])) {
                                        $groupedParams[$packageName] = [];
                                    }
                                    $groupedParams[$packageName][] = $param;
                                }
                                $counter = 1;
                            @endphp

                            <div class="row">
                                @foreach ($groupedParams as $packageName => $params)
                                    @if ($packageName !== '-')
                                        {{-- Jika PAKET: Tampilkan nama paket saja --}}
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-success mr-2"
                                                    style="font-size: 12px;">{{ $counter++ }}</span>
                                                <span style="font-size: 14px;">
                                                    <i class="fa fa-check-circle text-success mr-1"></i>
                                                    <strong>{{ $packageName }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Jika BUKAN PAKET: Tampilkan parameter satuan saja --}}
                                        @foreach ($params as $param)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-success mr-2"
                                                        style="font-size: 12px;">{{ $counter++ }}</span>
                                                    <span style="font-size: 14px;">
                                                        <i class="fa fa-check-circle text-success mr-1"></i>
                                                        {{ $param->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fa fa-info-circle mr-2"></i>Belum ada pemeriksaan yang ditambahkan
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informasi Pengambilan Sample -->
                @if ($latest_sampling)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="data-card">
                                <h5>
                                    <i class="fa fa-vial"></i>
                                    Informasi Pengambilan Sample
                                </h5>
                                <div class="table-responsive">
                                    <table class="table info-table">
                                        <tr>
                                            <th width="200px">Tanggal & Waktu Sampling</th>
                                            <td>
                                                {{ $tgl_waktu_sampling ?? \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::formatTanggalWaktuSamplingKlinikDisplay($item_permohonan_uji_klinik->id_permohonan_uji_klinik ?? null, $latest_sampling ?? null, $item_permohonan_uji_klinik ?? null) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status Sampling</th>
                                            <td>
                                                @if ($latest_sampling->status_sampling == 'Berhasil')
                                                    <span
                                                        class="badge badge-success">{{ $latest_sampling->status_sampling }}</span>
                                                @else
                                                    <span
                                                        class="badge badge-danger">{{ $latest_sampling->status_sampling }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Sample</th>
                                            <td>
                                                @php
                                                    $jenis_sample_display = '';
                                                    if (!empty($latest_sampling->jenis_sample)) {
                                                        if (is_string($latest_sampling->jenis_sample)) {
                                                            $decoded = json_decode(
                                                                $latest_sampling->jenis_sample,
                                                                true,
                                                            );
                                                            $jenis_sample_display = is_array($decoded)
                                                                ? implode(', ', $decoded)
                                                                : $latest_sampling->jenis_sample;
                                                        } elseif (is_array($latest_sampling->jenis_sample)) {
                                                            $jenis_sample_display = implode(
                                                                ', ',
                                                                $latest_sampling->jenis_sample,
                                                            );
                                                        } else {
                                                            $jenis_sample_display = $latest_sampling->jenis_sample;
                                                        }
                                                    }
                                                @endphp
                                                <span class="badge-custom">{{ $jenis_sample_display ?: '-' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tindakan Medis Khusus</th>
                                            <td>
                                                {{ \Smt\Masterweb\Helpers\Smt::formatTindakanMedisKhususDisplay($latest_sampling->tindakan_medis_khusus ?? null) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kondisi Pasien</th>
                                            <td>{{ $latest_sampling->kondisi_pasien ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Petugas Pengambil</th>
                                            <td>{{ $latest_sampling->petugas_name ?? '-' }}</td>
                                        </tr>
                                        @if ($latest_sampling->resampling > 0)
                                            <tr>
                                                <th>Resampling</th>
                                                <td>
                                                    <span class="badge badge-warning">Resampling
                                                        ke-{{ $latest_sampling->resampling + 1 }}</span>
                                                    @if ($latest_sampling->resample_reason)
                                                        <br><small class="text-muted">Alasan:
                                                            {{ $latest_sampling->resample_reason }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif


                <!-- Form Penerimaan Sampel -->
                <div class="form-section">
                    <h5>
                        <i class="fa fa-clipboard-check"></i>
                        Data Penerimaan Sampel
                    </h5>

                    @php
                        // Parse jenis sampel menjadi array
                        $jenis_sampel_array = [];
                        if (!empty($jenis_sampel)) {
                            if (is_string($jenis_sampel)) {
                                // Jika ada koma, split menjadi array
                                if (strpos($jenis_sampel, ',') !== false) {
                                    $jenis_sampel_array = array_map('trim', explode(',', $jenis_sampel));
                                } else {
                                    $jenis_sampel_array = [$jenis_sampel];
                                }
                            } elseif (is_array($jenis_sampel)) {
                                $jenis_sampel_array = $jenis_sampel;
                            }
                        }

                        $jenis_sampel_options = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra($jenis_sampel_array);

                        // Parse existing data penerimaan_sampel (JSON)
                        $penerimaan_sampel_data = [];
                        if (!empty($penerimaan_sampel)) {
                            if (is_string($penerimaan_sampel)) {
                                $decoded = json_decode($penerimaan_sampel, true);
                                $penerimaan_sampel_data = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($penerimaan_sampel)) {
                                $penerimaan_sampel_data = $penerimaan_sampel;
                            }
                        }

                        // Parse existing data volume_sampel (JSON) — robust (handle truncated/double-encoded)
                        $volume_sampel_data = \Smt\Masterweb\Helpers\Smt::decodeJsonMap($volume_sampel ?? '');

                        // Parse existing data kualitas_sampel (JSON)
                        $kualitas_sampel_data = [];
                        if (!empty($kualitas_sampel)) {
                            if (is_string($kualitas_sampel)) {
                                $decoded = json_decode($kualitas_sampel, true);
                                $kualitas_sampel_data = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($kualitas_sampel)) {
                                $kualitas_sampel_data = $kualitas_sampel;
                            }
                        }
                    @endphp

                    @foreach ($jenis_sampel_options as $index => $sampel_type)
                    <div class="row sample-section" data-sample-index="{{ $index }}" data-sample-type="{{ $sampel_type }}"
                        style="{{ in_array($sampel_type, $jenis_sampel_array, true) ? '' : 'display:none;' }}">
                        <div class="col-md-12">
                            <div style="background: #e7f4f2; border-radius: 10px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #0b3a5c;">
                                <h6 style="color: #0b3a5c; font-weight: 600; margin-bottom: 20px;">
                                    <i class="fa fa-vial mr-2"></i>
                                    Sampel: <span class="badge-custom">{{ $sampel_type }}</span>
                                </h6>

                                <div class="form-group">
                                    <label for="penerimaan_sampel_{{ $index }}">
                                        <i class="fa fa-file-alt mr-2"></i>
                                        PENERIMAAN SAMPEL <span style="color: red">*</span>
                                    </label>
                                    <textarea class="form-control sample-field" name="penerimaan_sampel[{{ $sampel_type }}]" id="penerimaan_sampel_{{ $index }}"
                                        {{ in_array($sampel_type, $jenis_sampel_array, true) ? 'required' : '' }} rows="3"
                                        placeholder="Masukkan catatan penerimaan sampel (contoh: kondisi sampel saat diterima, waktu penerimaan, dll)">{{ $penerimaan_sampel_data[$sampel_type] ?? old('penerimaan_sampel.' . $sampel_type) }}</textarea>
                                    <small class="form-text text-muted mt-2">
                                        <i class="fa fa-info-circle"></i> Catat kondisi penerimaan untuk sampel {{ $sampel_type }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="volume_sampel_{{ $index }}">
                                        <i class="fa fa-flask mr-2"></i>
                                        VOLUME SAMPEL <span style="color: red">*</span>
                                    </label>
                                    <input type="text" class="form-control sample-field" name="volume_sampel[{{ $sampel_type }}]" id="volume_sampel_{{ $index }}"
                                        placeholder="Masukkan volume sampel (contoh: 5 ml, 10 cc, dll)"
                                        {{ in_array($sampel_type, $jenis_sampel_array, true) ? 'required' : '' }}
                                        value="{{ $volume_sampel_data[$sampel_type] ?? old('volume_sampel.' . $sampel_type) }}">
                                    <small class="form-text text-muted mt-2">
                                        <i class="fa fa-info-circle"></i> Masukkan volume untuk sampel tipe {{ $sampel_type }}
                                    </small>
                                </div>

                                @if(strtolower(trim($sampel_type)) != 'urine')
                                <div class="form-group">
                                    <label>
                                        <i class="fa fa-check-square mr-2"></i>
                                        KUALITAS SAMPEL <span style="color: red">*</span>
                                    </label>
                                    <div class="quality-checkbox-group">
                                        <div class="quality-checkbox-col">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                                                    id="kualitas_lisis_{{ $index }}" value="Lisis"
                                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Lisis', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kualitas_lisis_{{ $index }}">
                                                    <i class="fa fa-circle" style="color: #ff6b6b;"></i>
                                                    <span>Lisis</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                                                    id="kualitas_ikterik_{{ $index }}" value="Ikterik"
                                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Ikterik', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kualitas_ikterik_{{ $index }}">
                                                    <i class="fa fa-circle" style="color: #ffd93d;"></i>
                                                    <span>Ikterik</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                                                    id="kualitas_lipemik_{{ $index }}" value="Lipemik"
                                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Lipemik', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kualitas_lipemik_{{ $index }}">
                                                    <i class="fa fa-circle" style="color: #ff9ff3;"></i>
                                                    <span>Lipemik</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="quality-checkbox-col">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                                                    id="kualitas_cukup_{{ $index }}" value="Cukup"
                                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Cukup', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kualitas_cukup_{{ $index }}">
                                                    <i class="fa fa-check-circle" style="color: #51cf66;"></i>
                                                    <span>Cukup</span>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="kualitas_sampel[{{ $sampel_type }}][]"
                                                    id="kualitas_beku_{{ $index }}" value="Beku"
                                                    {{ isset($kualitas_sampel_data[$sampel_type]) && in_array('Beku', (array)$kualitas_sampel_data[$sampel_type]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="kualitas_beku_{{ $index }}">
                                                    <i class="fa fa-snowflake" style="color: #74c0fc;"></i>
                                                    <span>Beku</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="fa fa-info-circle"></i> Pilih semua kondisi kualitas sampel yang sesuai untuk {{ $sampel_type }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Catatan Hasil disembunyikan sesuai permintaan --}}
                {{--
                <div class="form-section" style="margin-top: 30px;">
                    <h5>
                        <i class="fa fa-file-alt"></i>
                        Catatan Hasil
                    </h5>
                    <div class="form-group">
                        <textarea 
                            name="catatan_hasil" 
                            id="catatan_hasil" 
                            class="form-control" 
                            rows="5" 
                            placeholder="Masukkan catatan hasil pemeriksaan...">{{ $item_permohonan_uji_klinik->catatan_hasil ?? '' }}</textarea>
                    </div>
                </div>
                --}}

                <!-- Jam & Petugas Penerima Sampel -->
                <div class="form-section" style="margin-top: 25px;">
                    <h5>
                        <i class="fa fa-clock"></i>
                        Data Penerima Sampel
                    </h5>
                    @php
                        $existingJamPenerima     = isset($data_verifikasi_penerima) && $data_verifikasi_penerima->start_date
                            ? \Carbon\Carbon::parse($data_verifikasi_penerima->start_date)->format('H:i')
                            : '';
                        if ($existingJamPenerima === '' && request()->filled('jam')) {
                            try {
                                $rawJamQuery = trim((string) request('jam'));
                                if (preg_match('/^\d{1,2}:\d{2}$/', $rawJamQuery)) {
                                    $existingJamPenerima = strlen($rawJamQuery) === 4 ? '0' . $rawJamQuery : $rawJamQuery;
                                } else {
                                    $existingJamPenerima = \Carbon\Carbon::parse($rawJamQuery)->format('H:i');
                                }
                            } catch (\Throwable $e) {
                                $existingJamPenerima = '';
                            }
                        }
                        $existingPetugasPenerima = isset($data_verifikasi_penerima) ? ($data_verifikasi_penerima->nama_petugas ?? '') : '';
                        if ($existingPetugasPenerima === '' && request()->filled('petugas')) {
                            $existingPetugasPenerima = trim((string) request('petugas'));
                        }
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jam_penerima">JAM PENERIMAAN SAMPEL <span style="color: red">*</span></label>
                                <input type="text" class="form-control" name="jam_penerima" id="jam_penerima"
                                    value="{{ $existingJamPenerima ?: old('jam_penerima') }}" placeholder="HH:mm"
                                    autocomplete="off">
                                <small class="text-muted">Jam otomatis terisi waktu sekarang, dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_petugas_penerima">NAMA PETUGAS PENERIMA <span style="color: red">*</span></label>
                                <select class="form-control" name="nama_petugas_penerima" id="nama_petugas_penerima">
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach ($petugas_penerima_sampel as $nama)
                                        <option value="{{ $nama }}" {{ $existingPetugasPenerima == $nama ? 'selected' : '' }}>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="is_selesai" id="is_selesai_penerima" value="0">
                <!-- Action Buttons -->
                <div class="row mt-4 mb-4">
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-light btn-action mr-2"
                            onclick="document.location='{{ url('/elits-permohonan-uji-klinik-2/verification/' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}'">
                            <i class="fa fa-arrow-left mr-2"></i>Kembali
                        </button>
                        <button type="button" class="btn btn-primary btn-action btn-simpan mr-2">
                            <i class="fa fa-save mr-2"></i>Simpan Data
                        </button>
                        <button type="button" class="btn btn-success btn-action btn-selesai-penerima">
                            <i class="fa fa-check-circle mr-2"></i>Selesai
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <!-- TinyMCE is already loaded in template admin scripts.blade.php from local assets -->
    <!-- Wait for TinyMCE to be ready before loading scripts that use it -->
    <script>
        // Verify TinyMCE is loaded (from template admin scripts.blade.php) and wait for it to be ready
        (function checkTinyMCELoaded() {
            if (typeof tinymce === 'undefined') {
                console.warn('TinyMCE not yet loaded, retrying...');
                setTimeout(checkTinyMCELoaded, 100);
            } else {
                console.log('TinyMCE loaded successfully from template admin');
                // Force TinyMCE to use local assets - prevent CDN loading
                var tinymceBasePath = '{{ asset("assets/admin/vendors/tinymce") }}';
                if (tinymce.baseURL === undefined || tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                    tinymce.baseURL = tinymceBasePath;
                    console.log('TinyMCE baseURL set to:', tinymce.baseURL);
                }
                // Ensure TinyMCE is fully initialized
                if (typeof tinymce.init === 'function') {
                    console.log('TinyMCE ready to use');
                }
            }
        })();
    </script>

    <script>
        $(document).ready(function() {
            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-apply-localstorage', [
                'permohonanId' => $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                'stepKey' => 'penerima',
            ])

            function syncPenerimaJenisSampelSections() {
                var selected = $('#jenis_sampel_penerima').val() || [];
                $('.sample-section').each(function() {
                    var type = String($(this).data('sample-type') || '');
                    var active = selected.indexOf(type) !== -1;
                    $(this).toggle(active);
                    $(this).find('.sample-field').prop('required', active);
                    $(this).find('input, textarea, select').prop('disabled', !active);
                });
            }

            if ($('#jenis_sampel_penerima').length) {
                $('#jenis_sampel_penerima').select2({
                    placeholder: 'Pilih jenis sampel (bisa lebih dari satu)',
                    theme: 'bootstrap4',
                    allowClear: true,
                    width: '100%',
                    multiple: true
                });
                $('#jenis_sampel_penerima').on('change', syncPenerimaJenisSampelSections);
                syncPenerimaJenisSampelSections();
            }

            // Init Flatpickr for jam penerima sampel
            flatpickr('#jam_penerima', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                defaultHour: new Date().getHours(),
                defaultMinute: new Date().getMinutes(),
            });

            // Auto-fill current time if no existing value
            if (!$('#jam_penerima').val()) {
                var now = new Date();
                var hh = String(now.getHours()).padStart(2, '0');
                var mm = String(now.getMinutes()).padStart(2, '0');
                $('#jam_penerima').val(hh + ':' + mm);
            }

            // function initCatatanHasilTinyMCE() {
            //     // Check if TinyMCE is fully ready
            //     if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function' ||
            //         typeof tinymce.util === 'undefined' || typeof tinymce.EditorManager === 'undefined') {
            //         console.log('TinyMCE not ready yet, retrying...');
            //         setTimeout(initCatatanHasilTinyMCE, 300);
            //         return;
            //     }

            //     // Check if editor already exists
            //     if (tinymce.get('catatan_hasil')) {
            //         console.log('TinyMCE editor for catatan_hasil already exists');
            //         return;
            //     }

            //     var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
            //     if (tinymce.baseURL === undefined || 
            //         tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
            //         tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
            //         tinymce.baseURL = tinymceBasePath;
            //     }

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
                                
            //                     editor.on('blur', function() {
            //                         // Sync content to textarea for form submission
            //                         var content = editor.getContent();
            //                         $('#catatan_hasil').val(content);
            //                     });
            //                 }
            //             });
            //         } catch(e) {
            //             console.error('Error initializing TinyMCE for catatan_hasil:', e);
            //             setTimeout(initCatatanHasilTinyMCE, 500);
            //         }
            //     }
            // }

            // Initialize after a short delay to ensure TinyMCE is loaded
            // setTimeout(initCatatanHasilTinyMCE, 500);

            $('.btn-simpan').on('click', function() {
                // Sync TinyMCE content to textarea before form submission
                // if (tinymce.get('catatan_hasil')) {
                //     var content = tinymce.get('catatan_hasil').getContent();
                //     $('#catatan_hasil').val(content);
                // }
                
                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Tersimpan!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    location.reload();
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
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
                                    text: response.pesan,
                                    icon: "warning"
                                });

                            }
                        }
                    },
                    error: function() {

                        swal("Error!", "System gagal menyimpan!", "error");

                    }
                })
            });

            $('.btn-selesai-penerima').on('click', function() {
                $('#is_selesai_penerima').val('1');
                $('#form').ajaxSubmit({
                    success: function(response) {
                        $('#is_selesai_penerima').val('0');
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location =
                                        '/elits-permohonan-uji-klinik-2/verification/{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}';
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
                                swal({ title: "Error!", content: wrapper, icon: "warning" });
                            } else {
                                swal({ title: "Error!", text: response.pesan, icon: "warning" });
                            }
                        }
                    },
                    error: function() {
                        $('#is_selesai_penerima').val('0');
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });
        })
    </script>
@endsection
