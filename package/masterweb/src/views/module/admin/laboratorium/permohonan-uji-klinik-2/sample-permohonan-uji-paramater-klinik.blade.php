@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    <style>
        /* Subtle modern beautification */
        .card {
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
        }

        .card-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: #fff;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .section-title {
            font-weight: 700;
            color: #4a5568;
            margin: 8px 0 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .section-title:before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 10px;
            background: #0b3a5c;
        }

        .table-borderless th {
            color: #6b7280;
            width: 230px;
        }

        .form-group>label {
            font-weight: 600;
            color: #374151;
        }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            z-index: 50;
            background: #ffffffcc;
            backdrop-filter: blur(3px);
            padding: 12px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            border: none;
        }

        .badge-soft {
            background: #eef2ff;
            color: #374151;
            border: 1px solid #e5e7eb;
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        .inline-edit-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
        }

        .inline-edit-display {
            flex: 1;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 0;
            color: #374151;
            cursor: default;
        }

        .inline-edit-display.empty {
            color: #9ca3af;
            font-style: italic;
        }

        .inline-edit-display.hidden {
            display: none;
        }

        .inline-edit-btn {
            cursor: pointer;
            color: #0b3a5c;
            padding: 6px 10px;
            border-radius: 4px;
            transition: all .2s;
            font-size: 14px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            visibility: visible !important;
            opacity: 1 !important;
            min-width: 32px;
            min-height: 32px;
            line-height: 1;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            position: relative;
            z-index: 1;
        }

        .inline-edit-btn:hover {
            background: #eef2ff;
            color: #0d8f7f;
            border-color: #0b3a5c;
        }

        .inline-edit-btn i {
            font-size: 14px !important;
            display: inline-block !important;
            width: auto !important;
            height: auto !important;
            line-height: 1 !important;
        }

        .inline-edit-btn>span {
            display: inline-block;
            line-height: 1;
        }

        /* Sembunyikan unicode fallback jika FontAwesome ter-load (dengan JavaScript) */
        .inline-edit-btn.fa-loaded>span[style*="margin-left"] {
            display: none !important;
        }

        /* Fallback jika FontAwesome tidak ter-load */
        .inline-edit-btn i.fa-pencil:empty:before,
        .inline-edit-btn i.fa-pencil:not(.fa):before {
            content: "✏";
            display: inline-block;
            font-style: normal;
        }

        .inline-edit-input {
            display: none;
            flex: 1;
        }

        .inline-edit-input.active {
            display: block;
        }

        /* Form disabled overlay */
        .form-disabled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            pointer-events: auto;
        }

        .form-disabled-overlay-content {
            text-align: center;
            padding: 24px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            max-width: 360px;
        }

        .form-disabled-overlay-content i {
            font-size: 36px;
            margin-bottom: 12px;
            color: #e53e3e;
        }

        .form-section-disabled {
            position: relative;
        }

        /* Signature Pad Styling */
        .signature-container {
            padding: 15px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .signature-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }

        .signature-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: crosshair;
            touch-action: none;
            background-color: #ffffff !important;
        }

        .signature-wrapper::before {
            content: "Tanda tangan di sini";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }

        .signature-wrapper.active::before {
            display: none;
        }
    </style>
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script> --}}
    <link href="{{ asset('assets/admin/cdn-local/css/gijgo.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('assets/admin/cdn-local/js/gijgo.min.js') }}" type="text/javascript"></script>
    <script src="//cdn.ckeditor.com/4.22.1/basic/ckeditor.js"></script>

    <link href="{{ asset('assets/admin/cdn-local/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/admin/cdn-local/js/select2.min.js') }}"></script>

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
                                <li class="breadcrumb-item active" aria-current="page"><span>Sample Permohonan Uji
                                        Klinik</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form
        action="{{ route('elits-permohonan-uji-klinik-2.store-permohonan-uji-sample', $item_permohonan_uji_klinik->id_permohonan_uji_klinik . '/' . $count) }}"
        method="POST" enctype="multipart/form-data" id="form">
        {{-- <form action=""> --}}

        @csrf
        @method('PUT')

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px">
                    <div class="d-flex align-items-center" style="gap:10px">
                        <h4>Sample Permohonan Uji Paket Klinik</h4>
                        <button type="button" class="btn btn-sm btn-light font-weight-bold" id="btnHeaderSignatureModal" data-toggle="modal" data-target="#signatureSampleModal">
                            <i class="fa fa-pencil-alt mr-1 text-primary"></i> Tanda Tangan
                            @php
                                $hasSigInit = (!empty($item_pengambilan_sample->signature_pengambil_sample_pasien) && !empty($item_pengambilan_sample->signature_pengambil_sample_petugas))
                                    || (!empty($item_permohonan_uji_klinik->signature_pengambil_sample_pasien) && !empty($item_permohonan_uji_klinik->signature_pengambil_sample_petugas));
                            @endphp
                            <span class="badge {{ $hasSigInit ? 'badge-success' : 'badge-danger' }} ml-1" id="headerTtdStatus">
                                {{ $hasSigInit ? 'Sudah TTD' : 'Belum TTD' }}
                            </span>
                        </button>
                    </div>
                    <div class="d-none d-md-flex align-items-center" style="gap:8px">
                        @php if(!isset($ks_nr)) { $ks_nr = \Smt\Masterweb\Models\KlinikNumberSettings::getSettings(); } @endphp
                        @if($ks_nr->is_nomor_lab_manual && !empty($item_permohonan_uji_klinik->nomor_lab_manual))
                            <span class="badge-soft">No. Lab: {{ $item_permohonan_uji_klinik->getLabNumber() }}</span>
                        @elseif($ks_nr->is_nomor_spesimen_manual && !empty($item_permohonan_uji_klinik->nomor_spesimen_manual))
                            <span class="badge-soft">No. Spesimen: {{ $item_permohonan_uji_klinik->getSpesimenNumber() }}</span>
                        @else
                            <span class="badge-soft">No. Register: {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</span>
                        @endif
                        <span class="badge-soft">Tanggal: {{ $tgl_register_permohonan_uji_klinik }}</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                    'info_haji' => $info_haji ?? null,
                    'mode' => 'alert',
                ])
                <div class="row">
                    <div class="col-md-6">
                        <div class="section-title">Data Pasien</div>
                        <div class="table-responsive">
                            <table class="table table-borderless">
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
                                    <td style="text-transform: uppercase;">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
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

                    <div class="col-md-6">
                        <div class="section-title">Informasi Tambahan</div>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="250px">No. Pasien</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nourut_pasien }}</td>
                                </tr>
                                <tr>
                                    <th width="250px">No. KTP</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Tanggal Lahir</th>
                                    <td>
                                        {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                                            ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                                                'D MMMM Y',
                                            )
                                            : '' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Pengirim</th>
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
                            </table>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="tgl_sampling" id="tgl_sampling"
                    value="{{ !empty($prefill_jam_sampling) ? \Carbon\Carbon::parse($prefill_jam_sampling)->format('Y-m-d') : ((isset($data_verifikasi_registrasi) && $data_verifikasi_registrasi->start_date) ? \Carbon\Carbon::parse($data_verifikasi_registrasi->start_date)->format('Y-m-d') : ($tgl_sampling ?? (old('tgl_sampling') ?? \Carbon\Carbon::now()->format('Y-m-d')))) }}">

                @php
                    $existingDateTime = '';
                    $existingPetugas  = '';
                    $queryJam = request()->query('jam_sampling');
                    $queryPetugas = request()->query('nama_petugas_pengambil');
                    if (!empty($prefill_jam_sampling)) {
                        $existingDateTime = $prefill_jam_sampling;
                        $existingPetugas  = $prefill_petugas ?? '';
                    } elseif (!empty($queryJam)) {
                        try {
                            if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', (string) $queryJam)) {
                                $existingDateTime = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $queryJam)->format('Y-m-d H:i');
                            } else {
                                $existingDateTime = \Carbon\Carbon::parse($queryJam)->format('Y-m-d H:i');
                            }
                        } catch (\Exception $e) {
                            $existingDateTime = (string) $queryJam;
                        }
                        $existingPetugas = $queryPetugas ?? '';
                    } elseif (isset($data_verifikasi_registrasi) && !empty($data_verifikasi_registrasi->start_date)) {
                        $existingDateTime = \Carbon\Carbon::parse($data_verifikasi_registrasi->start_date)->format('Y-m-d H:i');
                        $existingPetugas  = $data_verifikasi_registrasi->nama_petugas ?? '';
                    } elseif (!empty($jam_sampling)) {
                        $existingDateTime = preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $jam_sampling)
                            ? $jam_sampling
                            : (\Carbon\Carbon::now()->format('Y-m-d') . ' ' . $jam_sampling);
                    }
                    if (empty($existingPetugas) && !empty($prefill_petugas)) {
                        $existingPetugas = $prefill_petugas;
                    }
                    if (empty($existingPetugas) && !empty($queryPetugas)) {
                        $existingPetugas = $queryPetugas;
                    }
                @endphp

                <div class="row">
                    <div class="col-md-12 p-4" id="dataPengambilanSection">
                        <div class="section-title" style="margin-left: -6px;">Data Pengambilan Sampel</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jam_sampling">TANGGAL & JAM PENGAMBILAN SAMPEL <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" name="jam_sampling" id="jam_sampling"
                                        value="{{ $existingDateTime ?: old('jam_sampling') }}" placeholder="YYYY-MM-DD HH:mm"
                                        autocomplete="off">
                                    <small class="text-muted">Tanggal dan jam otomatis terisi waktu sekarang, dapat diubah</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_petugas_pengambil">NAMA PETUGAS PENGAMBIL <span style="color: red">*</span></label>
                                    <select class="form-control" name="nama_petugas_pengambil" id="nama_petugas_pengambil">
                                        <option value="">-- Pilih Petugas --</option>
                                        <option value="__diisi_pelanggan__"
                                            {{ !empty($existingPetugas) && trim($existingPetugas) === '__diisi_pelanggan__' ? 'selected' : '' }}>
                                            Diisi pelanggan
                                        </option>
                                        @foreach ($petugas_pengambil_sample as $nama)
                                            @php
                                                $isSelectedPetugas = !empty($existingPetugas) && (
                                                    trim($existingPetugas) === trim($nama)
                                                    || strcasecmp(trim($existingPetugas), trim($nama)) === 0
                                                );
                                            @endphp
                                            <option value="{{ $nama }}" {{ $isSelectedPetugas ? 'selected' : '' }}>
                                                {{ $nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 p-4 form-section-disabled" id="dataSamplingSection">
                        <div class="section-title" style="margin-left: -6px;">Data Sampling</div>

                        <!-- Overlay untuk disabled form -->
                        <div class="form-disabled-overlay" id="formDisabledOverlay" style="display: none;">
                            <div class="form-disabled-overlay-content">
                                <i class="fa fa-lock"></i>
                                <div style="font-weight: 700; font-size: 15px; margin-top: 6px; color: #1e293b;">Tanda Tangan Diperlukan</div>
                                <div style="font-size: 13px; margin-top: 6px; color: #64748b; line-height: 1.4;">
                                    Silakan lengkapi tanda tangan Pasien/Wali dan Petugas terlebih dahulu untuk mengisi form pengambilan sample.
                                </div>
                                <button type="button" class="btn btn-primary btn-sm mt-3" data-toggle="modal" data-target="#signatureSampleModal">
                                    <i class="fa fa-pen-nib mr-1"></i> Buka Form Tanda Tangan
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tindakan_medis_khusus">TINDAKAN MEDIS KHUSUS</label>
                            @php
                                // Parse tindakan_medis_khusus - bisa array, JSON string, atau string biasa
                                $tindakan_raw = $tindakan_medis_khusus ?? old('tindakan_medis_khusus') ?? '';
                                
                                // Jika string, coba decode JSON, jika bukan JSON gunakan sebagai string biasa
                                if (is_string($tindakan_raw) && !empty($tindakan_raw)) {
                                    $decoded = json_decode($tindakan_raw, true);
                                    $tindakan_selected = ($decoded !== null && is_array($decoded)) ? $decoded : [$tindakan_raw];
                                } elseif (is_array($tindakan_raw)) {
                                    $tindakan_selected = $tindakan_raw;
                                } else {
                                    $tindakan_selected = [];
                                }
                                
                                // Jika auto_tindakan_medis_khusus ada dan tindakan_selected kosong, gunakan auto
                                if (empty($tindakan_selected) && !empty($auto_tindakan_medis_khusus)) {
                                    $tindakan_selected = is_array($auto_tindakan_medis_khusus) ? $auto_tindakan_medis_khusus : [$auto_tindakan_medis_khusus];
                                }

                                // Selaraskan dengan jenis sampel yang tampil (hindari: jenis ada Darah tapi tindakan hanya Urine)
                                $jenis_for_reconcile =
                                    !empty($jenis_sampel) && is_array($jenis_sampel)
                                        ? $jenis_sampel
                                        : ($auto_jenis_sampel ?? []);
                                if (!empty($jenis_for_reconcile)) {
                                    $tindakan_selected = \Smt\Masterweb\Helpers\Smt::reconcileTindakanMedisWithJenisSampel(
                                        is_array($tindakan_selected) ? $tindakan_selected : [],
                                        $jenis_for_reconcile
                                    );
                                }
                                
                                $tindakan_display_text = !empty($tindakan_selected) && is_array($tindakan_selected)
                                    ? implode(', ', $tindakan_selected)
                                    : '';
                            @endphp
                            <div class="inline-edit-wrapper">
                                <span class="inline-edit-display {{ empty($tindakan_display_text) ? 'empty' : '' }}"
                                    id="tindakan_display">
                                    {{ $tindakan_display_text ?: 'Belum dipilih' }}
                                </span>
                                <span class="inline-edit-btn" id="tindakan_edit_btn" title="Edit">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </span>
                                <div class="inline-edit-input" id="tindakan_input_wrapper">
                                    <select class="form-control" name="tindakan_medis_khusus[]"
                                        id="tindakan_medis_khusus" multiple>
                                        <option value="Pengambilan Darah Vena"
                                            {{ is_array($tindakan_selected) && in_array('Pengambilan Darah Vena', $tindakan_selected) ? 'selected' : '' }}>
                                            Pengambilan Darah Vena</option>
                                        <option value="Pengumpulan Urin Spontan"
                                            {{ is_array($tindakan_selected) && in_array('Pengumpulan Urin Spontan', $tindakan_selected) ? 'selected' : '' }}>
                                            Pengumpulan Urin Spontan</option>
                                        <option value="Pengumpulan Feses Spontan"
                                            {{ is_array($tindakan_selected) && in_array('Pengumpulan Feses Spontan', $tindakan_selected) ? 'selected' : '' }}>
                                            Pengumpulan Feses Spontan</option>
                                        <option value="Pengambilan Swab Rektal"
                                            {{ is_array($tindakan_selected) && in_array('Pengambilan Swab Rektal', $tindakan_selected) ? 'selected' : '' }}>
                                            Pengambilan Swab Rektal</option>
                                        <option value="Lainnya"
                                            {{ is_array($tindakan_selected) && in_array('Lainnya', $tindakan_selected) ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <input type="text" class="form-control mt-2" name="tindakan_medis_khusus_lainnya"
                                id="tindakan_medis_khusus_lainnya" placeholder="Masukkan tindakan medis khusus lainnya"
                                style="display: none;">
                        </div>

                        <div class="form-group">
                            <label for="jenis_sampel">JENIS SAMPEL <span style="color: red">*</span></label>
                            @php
                                // Haji: tampilan/default mengikuti auto dari parameter (jenis_sampel_haji)
                                if (!empty($is_permohonan_haji) && !empty($auto_jenis_sampel) && is_array($auto_jenis_sampel)) {
                                    $jenis_default = $auto_jenis_sampel;
                                } else {
                                    $jenis_default =
                                        !empty($jenis_sampel) && is_array($jenis_sampel)
                                            ? $jenis_sampel
                                            : ($auto_jenis_sampel ?? []);
                                }
                                $jenis_display_text =
                                    !empty($jenis_default) && is_array($jenis_default)
                                        ? implode(', ', $jenis_default)
                                        : '';
                            @endphp
                            <div class="inline-edit-wrapper">
                                <span class="inline-edit-display {{ empty($jenis_display_text) ? 'empty' : '' }}"
                                    id="jenis_sampel_display">
                                    {{ $jenis_display_text ?: 'Belum dipilih' }}
                                </span>
                                <span class="inline-edit-btn" id="jenis_sampel_edit_btn" title="Edit">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </span>
                                <div class="inline-edit-input" id="jenis_sampel_input_wrapper">
                                    <select class="form-control" name="jenis_sampel[]" id="jenis_sampel" multiple
                                        required>
                                        @php
                                            $jenis_sampel_options = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra(
                                                is_array($jenis_default) ? $jenis_default : []
                                            );
                                        @endphp
                                        @foreach ($jenis_sampel_options as $option)
                                            <option value="{{ $option }}"
                                                {{ is_array($jenis_default) && in_array($option, $jenis_default, true) ? 'selected' : '' }}>
                                                {{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    window._jenisSelect2Initialized = false;
                                });
                            </script>
                        </div>



                        <div class="form-group">
                            <label for="kondisi_pasien">KONDISI PASIEN (demam, puasa 12 jam, dll) <span
                                    style="color: red">*</span></label>
                            <textarea class="form-control" name="kondisi_pasien" id="kondisi_pasien" required rows="3"
                                placeholder="Masukkan kondisi pasien (contoh: demam, puasa 12 jam, dll)" style="height: 100px">{{ $kondisi_pasien ?? old('kondisi_pasien') }}</textarea>
                        </div>


                        <div class="form-group">
                            <label for="status_sampling">STATUS SAMPLING <span style="color: red">*</span></label>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input status-sampling-radio"
                                        name="status_sampling" id="status_sampling_berhasil" value="Berhasil" required
                                        {{ ($status_sampling ?? '') == 'Berhasil' ? 'checked' : '' }}>
                                    Berhasil
                                    <i class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input status-sampling-radio"
                                        name="status_sampling" id="status_sampling_gagal" value="Gagal" required
                                        {{ ($status_sampling ?? '') == 'Gagal' ? 'checked' : '' }}>
                                    Gagal
                                    <i class="input-helper"></i>
                                </label>
                            </div>
                        </div>

                        <div id="resampling-section" style="display:none;">
                            <hr>
                            <h6 class="mb-3">Sampling Ulang</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alasan Gagal</label>
                                        <input type="text" class="form-control" name="resample_reason"
                                            value="{{ $resample_reason ?? old('resample_reason') }}" id="resample_reason"
                                            placeholder="Misal: vena sulit, pasien gelisah">
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        // toggle resampling section
                        function toggleResample() {
                            const gagal = $("#status_sampling_gagal").is(':checked');
                            $('#resampling-section').css('display', gagal ? '' : 'none');
                        }
                        $('.status-sampling-radio').on('change', toggleResample);
                        toggleResample();

                        // time-only picker (guard if libs not present)
                        if (document.querySelector('#resample_time')) {
                            if (window.flatpickr) {
                                window.flatpickr('#resample_time', {
                                    enableTime: true,
                                    noCalendar: true,
                                    allowInput: true,
                                    dateFormat: 'H:i',
                                    time_24hr: true
                                });
                            }
                            if ($.fn && $.fn.inputmask) {
                                $('#resample_time').inputmask('99:99', {
                                    placeholder: 'hh:mm'
                                });
                            }
                        }
                        let samplingCount = {{ !empty($sampling_ulang) ? count($sampling_ulang) + 1 : 1 }};


                    });
                </script>

    <input type="hidden" name="is_selesai" id="is_selesai_flag" value="0">
    </form>
    <div class="sticky-actions">
        <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#signatureSampleModal">
            <i class="fa fa-pencil-alt mr-1"></i> Tanda Tangan
        </button>
        <button type="button" class="btn btn-primary mr-2 btn-simpan" id="btnSimpanForm"><i
                class="fa fa-save mr-1"></i> Simpan</button>
        <button type="button" class="btn btn-success mr-2 btn-selesai-sample" id="btnSelesaiForm"><i
                class="fa fa-check-circle mr-1"></i> Selesai</button>
        <button type="button" class="btn btn-light"
            onclick="document.location='{{ request()->get('return_to', url('/elits-permohonan-uji-klinik/verifikasi/lists?status_filter=pengambilan_sample')) }}'">
            <i class="fa fa-arrow-left mr-1"></i> Kembali
        </button>
    </div>
    </div>

    <!-- Modal Signature Pengambil Sample -->
    <div class="modal fade text-left" id="signatureSampleModal" tabindex="-1" role="dialog" aria-labelledby="signatureSampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: #fff; padding: 15px 20px;">
                    <h5 class="modal-title font-weight-bold" id="signatureSampleModalLabel">
                        <i class="fa fa-pencil-alt mr-2"></i> Tanda Tangan Pengambilan Sampel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background: #f8fafc; padding: 20px;">
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="font-size: 13px; border-radius: 8px;">
                        <i class="fa fa-info-circle mr-2" style="font-size: 16px;"></i>
                        <span>Mohon tanda tangani canvas di bawah ini untuk Pasien/Wali dan Petugas Sampling.</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="signature-container">
                                <h6 class="mb-2 d-flex justify-content-between align-items-center font-weight-bold">
                                    <span><i class="fa fa-user-circle mr-2 text-primary"></i>Pasien / Wali</span>
                                    <span class="badge badge-secondary" id="badgeSigPasien">Belum TTD</span>
                                </h6>
                                <div class="signature-wrapper">
                                    <canvas id="sigPadPasien" class="signature-canvas"></canvas>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSigPasien">
                                        <i class="fa fa-eraser mr-1"></i>Clear
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="saveSigPasien">
                                        <i class="fa fa-save mr-1"></i>Simpan Pasien
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="signature-container">
                                <h6 class="mb-2 d-flex justify-content-between align-items-center font-weight-bold">
                                    <span><i class="fa fa-user-md mr-2 text-info"></i>Petugas Sampling</span>
                                    <span class="badge badge-secondary" id="badgeSigPetugas">Belum TTD</span>
                                </h6>
                                <div class="signature-wrapper">
                                    <canvas id="sigPadPetugas" class="signature-canvas"></canvas>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSigPetugas">
                                        <i class="fa fa-eraser mr-1"></i>Clear
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="saveSigPetugas">
                                        <i class="fa fa-save mr-1"></i>Simpan Petugas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #fff; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary font-weight-bold" id="saveAllSignatures">
                        <i class="fa fa-check-circle mr-1"></i> Simpan Semua TTD & Buka Form
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/signature_pad.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        $(document).ready(function() {
            // Variable untuk menyimpan status TTD (akan diupdate dari server)
            let signaturesFilledStatus = false;
            const samplingIndex = {{ max(0, (int) $count - 1) }};
            const ttdLocalStorageKey = 'pengambil_ttd_complete_{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}_' + samplingIndex;

            var existingSignatures = {
                pasien: @if(!empty($item_pengambilan_sample->signature_pengambil_sample_pasien)) '{{ addslashes($item_pengambilan_sample->signature_pengambil_sample_pasien) }}' @elseif(!empty($item_permohonan_uji_klinik->signature_pengambil_sample_pasien) && (int)$count <= 1) '{{ addslashes($item_permohonan_uji_klinik->signature_pengambil_sample_pasien) }}' @else null @endif,
                petugas: @if(!empty($item_pengambilan_sample->signature_pengambil_sample_petugas)) '{{ addslashes($item_pengambilan_sample->signature_pengambil_sample_petugas) }}' @elseif(!empty($item_permohonan_uji_klinik->signature_pengambil_sample_petugas) && (int)$count <= 1) '{{ addslashes($item_permohonan_uji_klinik->signature_pengambil_sample_petugas) }}' @else null @endif
            };

            @if (isset($item_pengambilan_sample) && $item_pengambilan_sample)
                @if (
                    !empty($item_pengambilan_sample->signature_pengambil_sample_pasien) &&
                        !empty($item_pengambilan_sample->signature_pengambil_sample_petugas))
                    signaturesFilledStatus = true;
                @endif
            @endif
            @if (!empty($item_permohonan_uji_klinik->signature_pengambil_sample_pasien) && !empty($item_permohonan_uji_klinik->signature_pengambil_sample_petugas) && (int)$count <= 1)
                signaturesFilledStatus = true;
            @endif

            function updateSignatureBadges() {
                if (existingSignatures.pasien) {
                    $('#badgeSigPasien').removeClass('badge-secondary').addClass('badge-success').text('Sudah TTD');
                } else {
                    $('#badgeSigPasien').removeClass('badge-success').addClass('badge-secondary').text('Belum TTD');
                }
                if (existingSignatures.petugas) {
                    $('#badgeSigPetugas').removeClass('badge-secondary').addClass('badge-success').text('Sudah TTD');
                } else {
                    $('#badgeSigPetugas').removeClass('badge-success').addClass('badge-secondary').text('Belum TTD');
                }

                if (existingSignatures.pasien && existingSignatures.petugas) {
                    $('#headerTtdStatus').removeClass('badge-danger').addClass('badge-success').text('Sudah TTD');
                    updateSignaturesStatus(true);
                    enableFormInputs();
                } else {
                    $('#headerTtdStatus').removeClass('badge-success').addClass('badge-danger').text('Belum TTD');
                }
            }

            function tryUnlockFromLocalStorage() {
                try {
                    if (localStorage.getItem(ttdLocalStorageKey) === '1') {
                        updateSignaturesStatus(true);
                        enableFormInputs();
                        return true;
                    }
                } catch (e) {
                    /* private mode */
                }
                return false;
            }

            // Function to check if signatures are filled
            function checkSignaturesFilled() {
                return signaturesFilledStatus;
            }

            // Function to update signatures status
            function updateSignaturesStatus(status) {
                signaturesFilledStatus = status;
            }

            // Function to disable form inputs
            function disableFormInputs() {
                // Tanggal/jam & petugas tetap bisa diisi tanpa menunggu TTD
                $('#tindakan_medis_khusus').prop('disabled', true);
                $('#tindakan_medis_khusus_lainnya').prop('disabled', true);
                $('#jenis_sampel').prop('disabled', true);
                $('#kondisi_pasien').prop('disabled', true);
                $('#status_sampling_berhasil').prop('disabled', true);
                $('#status_sampling_gagal').prop('disabled', true);
                $('#resample_reason').prop('disabled', true);
                $('#btnSimpanForm').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
                $('#btnSelesaiForm').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');

                $('.inline-edit-btn').css('pointer-events', 'none').css('opacity', '0.5');
                $('#formDisabledOverlay').show();
            }

            function enableFormInputs() {
                $('#tindakan_medis_khusus').prop('disabled', false);
                $('#tindakan_medis_khusus_lainnya').prop('disabled', false);
                $('#jenis_sampel').prop('disabled', false);
                $('#kondisi_pasien').prop('disabled', false);
                $('#status_sampling_berhasil').prop('disabled', false);
                $('#status_sampling_gagal').prop('disabled', false);
                $('#resample_reason').prop('disabled', false);
                $('#btnSimpanForm').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
                $('#btnSelesaiForm').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');

                $('.inline-edit-btn').css('pointer-events', 'auto').css('opacity', '1');
                $('#formDisabledOverlay').hide();
            }

            // Signature Pad setup
            var sigPadPasien = null;
            var sigPadPetugas = null;

            function resizeCanvas(canvas) {
                var wrapper = canvas.parentElement;
                var wrapperWidth = wrapper.offsetWidth;
                var wrapperHeight = wrapper.offsetHeight;
                var ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = wrapperWidth * ratio;
                canvas.height = wrapperHeight * ratio;
                canvas.style.width = wrapperWidth + 'px';
                canvas.style.height = wrapperHeight + 'px';

                var ctx = canvas.getContext('2d', { alpha: true });
                ctx.scale(ratio, ratio);
                ctx.imageSmoothingEnabled = true;
            }

            $('#signatureSampleModal').on('shown.bs.modal', function() {
                var canvasPasien = document.getElementById('sigPadPasien');
                var canvasPetugas = document.getElementById('sigPadPetugas');

                setTimeout(function() {
                    resizeCanvas(canvasPasien);
                    resizeCanvas(canvasPetugas);

                    var ctxPasien = canvasPasien.getContext('2d');
                    var ctxPetugas = canvasPetugas.getContext('2d');

                    ctxPasien.fillStyle = '#ffffff';
                    ctxPasien.fillRect(0, 0, canvasPasien.width, canvasPasien.height);
                    ctxPetugas.fillStyle = '#ffffff';
                    ctxPetugas.fillRect(0, 0, canvasPetugas.width, canvasPetugas.height);

                    if (!sigPadPasien) {
                        sigPadPasien = new SignaturePad(canvasPasien, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(0, 0, 0)',
                            minWidth: 1,
                            maxWidth: 2.5,
                        });
                        sigPadPasien.addEventListener('beginStroke', function() {
                            $(canvasPasien).parent().addClass('active');
                        });
                    }

                    if (!sigPadPetugas) {
                        sigPadPetugas = new SignaturePad(canvasPetugas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(0, 0, 0)',
                            minWidth: 1,
                            maxWidth: 2.5,
                        });
                        sigPadPetugas.addEventListener('beginStroke', function() {
                            $(canvasPetugas).parent().addClass('active');
                        });
                    }

                    sigPadPasien.clear();
                    sigPadPetugas.clear();

                    if (existingSignatures.pasien) {
                        sigPadPasien.fromDataURL(existingSignatures.pasien, {
                            ratio: Math.max(window.devicePixelRatio || 1, 1),
                            width: canvasPasien.offsetWidth,
                            height: canvasPasien.offsetHeight
                        });
                        $(canvasPasien).parent().addClass('active');
                    }
                    if (existingSignatures.petugas) {
                        sigPadPetugas.fromDataURL(existingSignatures.petugas, {
                            ratio: Math.max(window.devicePixelRatio || 1, 1),
                            width: canvasPetugas.offsetWidth,
                            height: canvasPetugas.offsetHeight
                        });
                        $(canvasPetugas).parent().addClass('active');
                    }

                    updateSignatureBadges();
                }, 150);
            });

            function saveSignatures(part) {
                var payload = {
                    sampling: samplingIndex,
                    _token: '{{ csrf_token() }}'
                };

                if ((part === 'pasien' || part === 'all') && sigPadPasien && !sigPadPasien.isEmpty()) {
                    payload.signature_pasien = sigPadPasien.toDataURL('image/png');
                }
                if ((part === 'petugas' || part === 'all') && sigPadPetugas && !sigPadPetugas.isEmpty()) {
                    payload.signature_petugas = sigPadPetugas.toDataURL('image/png');
                }

                if (!payload.signature_pasien && !payload.signature_petugas) {
                    swal({
                        title: 'Perhatian',
                        text: 'Silakan goreskan tanda tangan pada canvas terlebih dahulu.',
                        icon: 'warning'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('elits-permohonan-uji-klinik-2.save-signature-pengambil-sample', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}',
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(resp) {
                        if (resp.status) {
                            if (payload.signature_pasien) {
                                existingSignatures.pasien = payload.signature_pasien;
                            }
                            if (payload.signature_petugas) {
                                existingSignatures.petugas = payload.signature_petugas;
                            }

                            updateSignatureBadges();

                            if (existingSignatures.pasien && existingSignatures.petugas) {
                                updateSignaturesStatus(true);
                                enableFormInputs();
                                try {
                                    localStorage.setItem(ttdLocalStorageKey, '1');
                                    localStorage.setItem('signature_saved_{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}_' + samplingIndex, Date.now().toString());
                                } catch (e) {}

                                $('#signatureSampleModal').modal('hide');

                                swal({
                                    title: 'Berhasil!',
                                    text: 'Tanda tangan berhasil disimpan. Form sampling telah terbuka.',
                                    icon: 'success',
                                    timer: 2000,
                                    buttons: false
                                });
                            } else {
                                swal({
                                    title: 'Tersimpan',
                                    text: resp.pesan + '. Lengkapi tanda tangan lainnya agar form dapat disimpan.',
                                    icon: 'info'
                                });
                            }
                        } else {
                            swal({
                                title: 'Gagal',
                                text: resp.pesan || 'Gagal menyimpan tanda tangan',
                                icon: 'warning'
                            });
                        }
                    },
                    error: function(err) {
                        swal({
                            title: 'Error',
                            text: 'Gagal menyimpan tanda tangan ke server.',
                            icon: 'error'
                        });
                    }
                });
            }

            $('#clearSigPasien').on('click', function() {
                if (sigPadPasien) {
                    sigPadPasien.clear();
                    var ctx = sigPadPasien.canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, sigPadPasien.canvas.width, sigPadPasien.canvas.height);
                    $(sigPadPasien.canvas).parent().removeClass('active');
                }
            });

            $('#clearSigPetugas').on('click', function() {
                if (sigPadPetugas) {
                    sigPadPetugas.clear();
                    var ctx = sigPadPetugas.canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, sigPadPetugas.canvas.width, sigPadPetugas.canvas.height);
                    $(sigPadPetugas.canvas).parent().removeClass('active');
                }
            });

            $('#saveSigPasien').on('click', function() {
                saveSignatures('pasien');
            });

            $('#saveSigPetugas').on('click', function() {
                saveSignatures('petugas');
            });

            $('#saveAllSignatures').on('click', function() {
                saveSignatures('all');
            });

            // Function untuk check TTD dari server (AJAX)
            function checkSignaturesFromServer() {
                $.ajax({
                    url: '{{ route('elits-permohonan-uji-klinik-2.check-signature-status', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        sampling: samplingIndex,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(resp) {
                        if (resp && resp.status && resp.has_signatures) {
                            updateSignaturesStatus(true);
                            enableFormInputs();
                            $('#headerTtdStatus').removeClass('badge-danger').addClass('badge-success').text('Sudah TTD');
                            try {
                                localStorage.setItem(ttdLocalStorageKey, '1');
                            } catch (e) {}
                            if (window.pollingInterval) {
                                clearInterval(window.pollingInterval);
                            }
                        } else if (!signaturesFilledStatus && !tryUnlockFromLocalStorage()) {
                            updateSignaturesStatus(false);
                            disableFormInputs();
                            $('#headerTtdStatus').removeClass('badge-success').addClass('badge-danger').text('Belum TTD');
                            // Otomatis munculkan popup TTD jika belum di-TTD
                            setTimeout(function() {
                                if (!signaturesFilledStatus && !tryUnlockFromLocalStorage()) {
                                    $('#signatureSampleModal').modal('show');
                                }
                            }, 500);
                        }
                    },
                    error: function() {
                        if (!signaturesFilledStatus && !tryUnlockFromLocalStorage()) {
                            disableFormInputs();
                            setTimeout(function() {
                                if (!signaturesFilledStatus && !tryUnlockFromLocalStorage()) {
                                    $('#signatureSampleModal').modal('show');
                                }
                            }, 500);
                        }
                    }
                });
            }

            if (signaturesFilledStatus) {
                enableFormInputs();
            } else {
                if (!tryUnlockFromLocalStorage()) {
                    disableFormInputs();
                }
            }

            // Cek status TTD saat halaman pertama kali dimuat
            checkSignaturesFromServer();

            function applyPengambilFromLocalStorage() {
                var storageKey = 'pengambil_sample_meta_{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}_{{ $count }}';
                var raw;
                try {
                    raw = localStorage.getItem(storageKey);
                } catch (e) {
                    return;
                }
                if (!raw) return;

                var data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    return;
                }
                if (!data || !data.jam_sampling) return;

                var jam = String(data.jam_sampling).trim();
                var petugas = (data.nama_petugas_pengambil || '').trim();
                $('#jam_sampling').val(jam);
                if ($('#tgl_sampling').length && /^\d{4}-\d{2}-\d{2}/.test(jam)) {
                    $('#tgl_sampling').val(jam.substring(0, 10));
                }

                if (petugas) {
                    var $sel = $('#nama_petugas_pengambil');
                    if ($sel.find('option').filter(function() {
                        return String($(this).val()).trim() === petugas;
                    }).length) {
                        $sel.val(petugas);
                    } else {
                        $sel.append($('<option>', { value: petugas, text: petugas, selected: true }));
                    }
                }
            }

            applyPengambilFromLocalStorage();

            // Inisialisasi flatpickr tanggal/jam segera (tidak menunggu TTD)
            if (document.querySelector('#jam_sampling')) {
                var jamSamplingPrefill = ($('#jam_sampling').val() || '').trim();
                if (window.flatpickr) {
                    window.flatpickr('#jam_sampling', {
                        enableTime: true,
                        noCalendar: false,
                        allowInput: true,
                        dateFormat: 'Y-m-d H:i',
                        time_24hr: true,
                        defaultDate: jamSamplingPrefill || null
                    });
                }
                if ($.fn && $.fn.inputmask) {
                    $('#jam_sampling').inputmask('9999-99-99 99:99', {
                        placeholder: 'yyyy-mm-dd hh:mm'
                    });
                }
                if (!jamSamplingPrefill) {
                    $('#jam_sampling').val('{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}');
                }
            }

            setTimeout(function() {
                if (!checkSignaturesFilled() && !tryUnlockFromLocalStorage()) {
                    disableFormInputs();
                }
            }, 1000);

            let metaSaveTimer = null;

            function saveSamplingMeta(showAlert) {
                const jam = ($('#jam_sampling').val() || '').trim();
                const petugas = ($('#nama_petugas_pengambil').val() || '').trim();
                if (!jam || !petugas) {
                    return;
                }

                $.ajax({
                    url: $('#form').attr('action'),
                    type: 'POST',
                    data: $('#form').serialize() + '&save_sampling_meta=1',
                    success: function(response) {
                        if (response.status && showAlert) {
                            swal({
                                title: 'Tersimpan!',
                                text: response.pesan,
                                icon: 'success',
                                timer: 1500,
                                buttons: false
                            });
                        }
                    }
                });
            }

            function queueSaveSamplingMeta() {
                clearTimeout(metaSaveTimer);
                metaSaveTimer = setTimeout(function() {
                    saveSamplingMeta(false);
                }, 700);
            }

            $('#jam_sampling').on('change', queueSaveSamplingMeta);
            $('#nama_petugas_pengambil').on('change', queueSaveSamplingMeta);

            // Polling untuk mengecek apakah TTD sudah diisi (jika user membuka form sebelum TTD)
            // Polling akan berhenti setelah TTD terdeteksi atau setelah 5 menit
            let pollingCount = 0;
            const maxPolling = 60; // 5 menit (60 * 5 detik)
            window.pollingInterval = setInterval(function() {
                checkSignaturesFromServer();
                pollingCount++;
                if (pollingCount >= maxPolling) {
                    clearInterval(window.pollingInterval);
                }
            }, 5000); // Cek setiap 5 detik

            // Listen untuk localStorage event (jika TTD disimpan di tab/window lain)
            window.addEventListener('storage', function(e) {
                if (e.key && (
                    e.key === ttdLocalStorageKey ||
                    e.key.startsWith('signature_saved_{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}_')
                )) {
                    tryUnlockFromLocalStorage();
                    checkSignaturesFromServer();
                }
            });

            // Listen untuk message event (jika dalam iframe atau dari window lain)
            window.addEventListener('message', function(e) {
                if (e.data && e.data.type === 'signature_saved' &&
                    e.data.permohonan_id === '{{ $item_permohonan_uji_klinik->id_permohonan_uji_klinik }}'
                ) {
                    checkSignaturesFromServer();
                }
            });

            // Validasi sebelum submit
            $('.btn-simpan').on('click', function() {
                // Validasi TTD harus sudah diisi
                if (!checkSignaturesFilled()) {
                    swal({
                        title: "Tanda Tangan Belum Lengkap",
                        text: "Tanda tangan pasien dan petugas wajib diisi terlebih dahulu.",
                        icon: "warning",
                        buttons: {
                            cancel: "Batal",
                            confirm: "Buka TTD"
                        }
                    }).then(function(val) {
                        if (val) {
                            $('#signatureSampleModal').modal('show');
                        }
                    });
                    return false;
                }

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

            $('.btn-selesai-sample').on('click', function() {
                if (!checkSignaturesFilled()) {
                    swal({
                        title: "Tanda Tangan Belum Lengkap",
                        text: "Tanda tangan pasien dan petugas wajib diisi terlebih dahulu.",
                        icon: "warning",
                        buttons: {
                            cancel: "Batal",
                            confirm: "Buka TTD"
                        }
                    }).then(function(val) {
                        if (val) {
                            $('#signatureSampleModal').modal('show');
                        }
                    });
                    return false;
                }
                if (!$('#jam_sampling').val() || !$('#nama_petugas_pengambil').val()) {
                    swal({
                        title: "Error!",
                        text: "Tanggal/jam pengambilan sampel dan nama petugas wajib diisi sebelum menyelesaikan.",
                        icon: "warning"
                    });
                    return false;
                }
                $('#is_selesai_flag').val('1');
                $('#form').ajaxSubmit({
                    success: function(response) {
                        $('#is_selesai_flag').val('0');
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location =
                                        '{{ request()->get('return_to', url('/elits-permohonan-uji-klinik/verifikasi/lists?status_filter=pengambilan_sample')) }}';
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
                        $('#is_selesai_flag').val('0');
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });

            // Handle "Lainnya" option untuk tindakan medis khusus (multi select)
            if (document.getElementById('tindakan_medis_khusus')) {
                $(document).on('change', '#tindakan_medis_khusus', function() {
                    var tindakan_medis_khusus_lainnya = document.getElementById(
                        'tindakan_medis_khusus_lainnya');
                    var selectedValues = $(this).val() || [];
                    if (selectedValues.includes('Lainnya')) {
                        tindakan_medis_khusus_lainnya.style.display = 'block';
                    } else {
                        tindakan_medis_khusus_lainnya.style.display = 'none';
                    }
                });
            }

            // Inline edit for Tindakan Medis Khusus (Multi Select)
            function initTindakanEdit() {
                const $display = $('#tindakan_display');
                const $inputWrapper = $('#tindakan_input_wrapper');
                const $select = $('#tindakan_medis_khusus');
                const $btn = $('#tindakan_edit_btn');

                function updateDisplay() {
                    const vals = $select.val() || [];
                    const text = vals.length > 0 ? vals.join(', ') : 'Belum dipilih';
                    $display.text(text).toggleClass('empty', vals.length === 0);
                }

                // Ensure display is visible and input is hidden on init
                $display.removeClass('hidden');
                $inputWrapper.removeClass('active');

                $btn.on('click', function() {
                    $display.addClass('hidden');
                    $inputWrapper.addClass('active');
                    if (!window._tindakanSelect2Initialized) {
                        $select.select2({
                            placeholder: 'Pilih tindakan medis khusus (bisa lebih dari satu)',
                            theme: 'bootstrap4',
                            allowClear: true,
                            multiple: true
                        });
                        window._tindakanSelect2Initialized = true;
                    }
                    setTimeout(() => {
                        $select.select2('open');
                    }, 100);
                });

                $select.on('change', function() {
                    updateDisplay();
                    setTimeout(() => {
                        if (!$inputWrapper.is(':hover')) {
                            $display.removeClass('hidden');
                            $inputWrapper.removeClass('active');
                        }
                    }, 300);
                });

                // Close when select2 closes
                $(document).on('select2:close', '#tindakan_medis_khusus', function() {
                    setTimeout(() => {
                        updateDisplay();
                        $display.removeClass('hidden');
                        $inputWrapper.removeClass('active');
                    }, 200);
                });

                // Initial display update after select2 is ready
                setTimeout(() => {
                    updateDisplay();
                }, 100);
            }

            // Inline edit for Jenis Sampel
            function initJenisSampelEdit() {
                const $display = $('#jenis_sampel_display');
                const $inputWrapper = $('#jenis_sampel_input_wrapper');
                const $select = $('#jenis_sampel');
                const $btn = $('#jenis_sampel_edit_btn');

                function updateDisplay() {
                    const vals = $select.val() || [];
                    const text = vals.length > 0 ? vals.join(', ') : 'Belum dipilih';
                    $display.text(text).toggleClass('empty', vals.length === 0);
                }

                // Ensure display is visible and input is hidden on init
                $display.removeClass('hidden');
                $inputWrapper.removeClass('active');

                $btn.on('click', function() {
                    $display.addClass('hidden');
                    $inputWrapper.addClass('active');
                    if (!window._jenisSelect2Initialized) {
                        $select.select2({
                            placeholder: 'Pilih jenis sampel (bisa lebih dari satu)',
                            theme: 'bootstrap4',
                            allowClear: true,
                            multiple: true
                        });
                        window._jenisSelect2Initialized = true;
                    }
                    setTimeout(() => {
                        $select.select2('open');
                    }, 100);
                });

                $select.on('change', function() {
                    updateDisplay();
                    setTimeout(() => {
                        if (!$inputWrapper.is(':hover')) {
                            $display.removeClass('hidden');
                            $inputWrapper.removeClass('active');
                        }
                    }, 300);
                });

                // Close when select2 closes
                $(document).on('select2:close', '#jenis_sampel', function() {
                    setTimeout(() => {
                        updateDisplay();
                        $display.removeClass('hidden');
                        $inputWrapper.removeClass('active');
                    }, 200);
                });

                // Initial display update after select2 is ready
                setTimeout(() => {
                    updateDisplay();
                }, 100);
            }

            // Check if FontAwesome is loaded and hide unicode fallback
            function checkFontAwesome() {
                const testIcon = document.createElement('i');
                testIcon.className = 'fa fa-pencil';
                testIcon.style.position = 'absolute';
                testIcon.style.visibility = 'hidden';
                document.body.appendChild(testIcon);

                const computedStyle = window.getComputedStyle(testIcon, ':before');
                const content = computedStyle.getPropertyValue('content');
                document.body.removeChild(testIcon);

                // If FontAwesome is loaded, hide unicode fallback
                if (content && content !== 'none' && content !== '""' && content !== "''") {
                    $('.inline-edit-btn > span[style*="margin-left"]').hide();
                    $('.inline-edit-btn').addClass('fa-loaded');
                }
            }

            // Initialize inline edits after select2 is ready
            setTimeout(function() {
                checkFontAwesome();
                initTindakanEdit();
                initJenisSampelEdit();
            }, 500);
        })
    </script>
@endsection
