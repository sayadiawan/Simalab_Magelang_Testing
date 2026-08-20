<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Baca Hasil Pengujian</title>
    {{-- Select2 for searchable dropdown (Jenis Makanan) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 10px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            text-align: right;
            flex: 1;
            margin-left: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #2D6BCF;
            box-shadow: 0 0 0 3px rgba(45, 107, 207, 0.1);
        }

        .parameter-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #2D6BCF;
        }

        .parameter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .parameter-name {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            flex: 1;
        }

        .status-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #fff3cd;
            color: #856404;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #28a745;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .input-group-mobile {
            margin-bottom: 15px;
        }

        .input-group-mobile label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
            display: block;
        }

        .input-group-mobile input,
        .input-group-mobile textarea,
        .input-group-mobile select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .input-group-mobile input:focus,
        .input-group-mobile textarea:focus,
        .input-group-mobile select:focus {
            outline: none;
            border-color: #2D6BCF;
        }

        .result-preview {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
        }

        .baku-mutu-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .radio-option:hover {
            border-color: #2D6BCF;
            background: #f8f9ff;
        }

        .radio-option input[type="radio"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }

        .radio-option label {
            margin: 0;
            cursor: pointer;
            flex: 1;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .hidden-field {
            display: none;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/number-format-helper.js') }}"></script>
</head>

<body>
    <div class="container">
        <div class="header" style="position: relative;">
            <form method="POST" action="{{ route('mobile.testing.logout') }}"
                style="position: absolute; top: 20px; right: 20px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
            <h1>BACA HASIL PENGUJIAN</h1>
            <p>Laboratorium {{ $lab->kode_laboratorium == 'KIM' ? 'Kimia' : 'Mikrobiologi' }}</p>
        </div>

        <!-- Info Sample Card -->
        <div class="card">
            <h3 class="card-title">📋 Informasi Sampel</h3>
            <div class="info-row">
                <span class="info-label">ID Sample:</span>
                <span class="info-value">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Sample:</span>
                <span
                    class="info-value">{{ $sample->jenisSampelDisplay() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Laboratorium:</span>
                <span class="info-value">{{ $lab->nama_laboratorium }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pengambilan:</span>
                <span
                    class="info-value">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}</span>
            </div>
        </div>

        @if ($sample->note_samples !== null)
            <div class="alert alert-warning">
                <strong>Catatan:</strong> {{ $sample->note_samples }}
            </div>
        @endif

        <!-- Form Baca Hasil -->
        <form class="form"
            action="{{ route('mobile.testing.storeBacaHasil', [$sample->id_samples, $lab_id, $method_id]) }}"
            method="POST" id="form-baca-hasil">
            @csrf
            <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

            <!-- Informasi Lokasi dan Pengujian -->
            <div class="card">
                <h3 class="card-title">📍 Informasi Lokasi dan Pengujian</h3>

                <!-- Nama Pengambil -->
                <div class="form-group">
                    <label for="nama_pengambil">Nama Pengambil</label>
                    @php
                        $defaultNamaPengambil = '';
                        if (!empty($sample->namaPengambilDisplay())) {
                            $defaultNamaPengambil = $sample->namaPengambilDisplay();
                        } else {
                            if ($sample->is_sampling == 1) {
                                $defaultNamaPengambil = 'Petugas Laboratorium Kesehatan';
                            } else {
                                $customerName =
                                    $sample->name_pelanggan ?? ($sample->namaPelangganDisplay() ?? '');
                                $defaultNamaPengambil = 'Petugas ' . $customerName;
                            }
                        }
                    @endphp
                    <input type="text" class="form-control" id="nama_pengambil" name="nama_pengambil"
                        value="{{ old('nama_pengambil', $defaultNamaPengambil) }}"
                        placeholder="Masukkan nama pengambil..." required>
                </div>

                <!-- Asal Sampel / Lokasi Pengambilan -->
                <div class="form-group">
                    <label for="lokasi_pengambilan">
                        @if ($lab->kode_laboratorium === 'MBI')
                            Asal Sampel
                        @else
                            Asal Contoh Air / Lokasi Sampel
                        @endif
                    </label>
                    @php
                        if ($lab->kode_laboratorium === 'MBI') {
                            // Default: gunakan detail_alamat_sampling jika sudah diisi di web.
                            // Jika belum ada, fallback ke name_customer + address_customer.
                            $asal_sampel_value = old('lokasi_pengambilan');
                            if (empty($asal_sampel_value)) {
                                $perm = $sample->permohonanuji ?? null;
                                $cust = $perm->customer ?? null;

                                if ($perm && !empty($perm->detail_alamat_sampling)) {
                                    // Sudah ada detail lengkap (biasanya "nama<br>alamat")
                                    $asal_sampel_value = $perm->detail_alamat_sampling;
                                } else {
                                    $namaCust = $cust->name_customer ?? '';
                                    $alamat = $cust->address_customer ?? '';
                                    $asal_sampel_value = trim($namaCust . "\n" . $alamat);
                                }
                            }
                        } else {
                            // Non MBI: gunakan location_samples atau fallback lama
                            if (isset($sample->location_samples) && $sample->location_samples != '') {
                                $asal_sampel_value = $sample->location_samples;
                            } else {
                                if ($sample->is_pudam) {
                                    $asal_sampel_value = $sample->name_customer_pdam ?? old('name_customer_pdam');
                                } else {
                                    $asal_sampel_value = $sample->titik_pengambilan ?? old('titik_pengambilan');
                                }
                            }
                        }
                    @endphp
                    <textarea class="form-control" id="lokasi_pengambilan" name="lokasi_pengambilan" rows="3"
                        placeholder="Masukkan asal sampel...">{{ $asal_sampel_value }}</textarea>
                </div>

                <!-- Titik Pengambilan (disembunyikan bila jenis sampel makanan/minuman/lainnya MBI) -->
                @if (!($lab->kode_laboratorium === 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya'))
                    <div class="form-group">
                        <label for="titik_pengambilan">Titik Pengambilan</label>
                        @php
                            $titik_pengambilan_value = $sample->titik_pengambilan ?? old('titik_pengambilan');
                        @endphp
                        <textarea class="form-control" id="titik_pengambilan" name="titik_pengambilan" rows="2"
                            placeholder="Masukkan titik pengambilan...">{{ $titik_pengambilan_value }}</textarea>
                    </div>
                @endif

                {{-- Jenis Sarana (disembunyikan di tampilan mobile testing, tetapi tetap bisa dipakai di backend jika diperlukan) --}}

                {{-- Jenis Makanan (MBI) + Jenis Sampel untuk Makanan/Minuman/Lainnya --}}
                @if (
                    $lab->kode_laboratorium === 'MBI' &&
                        $sample->name_sample_type === 'Makanan/Minuman/Lainnya' &&
                        isset($jenisMakananAll) &&
                        $jenisMakananAll->count() > 0)
                    @php
                        $selectedJenisMakananId = $jenis_makanan_id ?? ($sample->jenis_makanan_id ?? null);
                        // Default: kalau kosong, pilih jenis makanan paling atas (sesuai versi web)
                        if (empty($selectedJenisMakananId) && $jenisMakananAll->count() > 0) {
                            $selectedJenisMakananId = $jenisMakananAll->first()->id_jenis_makanan;
                        }
                    @endphp
                    <div class="form-group">
                        <label for="jenis_makanan_id">
                            Jenis Makanan (MBI)
                        </label>
                        <select id="jenis_makanan_id" name="jenis_makanan_id" class="form-control">
                            <option value="" disabled>- Pilih Jenis Makanan -</option>
                            @foreach ($jenisMakananAll as $jm)
                                <option value="{{ $jm->id_jenis_makanan }}"
                                    {{ $selectedJenisMakananId == $jm->id_jenis_makanan ? 'selected' : '' }}>
                                    {{ $jm->name_jenis_makanan }}
                                </option> @endforeach
                        </select>
                        <small class="text-muted">
    Memilih jenis makanan akan menyesuaikan baku mutu yang digunakan.
    </small>
    </div>

    <div class="form-group">
        <label for="nama_jenis_makanan">
            Jenis Sampel
        </label>
        @php
            if (isset($sample->nama_jenis_makanan) && $sample->nama_jenis_makanan !== '') {
                $defaultNamaJenis = $sample->namaJenisMakananPlain();
            } else {
                $defaultNamaJenis = $sample->titik_pengambilan;
            }
        @endphp
        <input type="text" id="nama_jenis_makanan" name="nama_jenis_makanan" class="form-control"
            value="{{ old('nama_jenis_makanan', $defaultNamaJenis) }}" placeholder="Contoh: Lemper, Nasi Uduk, dll">
        <small class="text-muted">
            Disimpan ke kolom <code>nama_jenis_makanan</code> pada sampel dan tampil sebagai Jenis
            Sampel di laporan.
        </small>
    </div>
    @endif
    </div>

    <!-- Data Hasil Pengujian -->
    <div class="card">
        <h3 class="card-title">🔬 Data Hasil Pengujian</h3>

        @php
            $no = 1;
            $tidak_simpan = false;
        @endphp

        @foreach ($laboratoriummethods as $laboratoriummethod)
            @if (count($laboratoriummethod['detail']) == 0)
                @if (isset($laboratoriummethod->name_report))
                    <!-- Parameter Card -->
                    <div class="parameter-card" id="param_{{ $laboratoriummethod->method_id }}">
                        <div class="parameter-header">
                            <div class="parameter-name">
                                {{ $no }}. {!! $laboratoriummethod->name_report !!}
                            </div>
                            <div class="status-toggle">
                                <label class="switch">
                                    <input type="checkbox" id="status_{{ $laboratoriummethod->method_id }}"
                                        value="true" name="status_{{ $laboratoriummethod->method_id }}"
                                        class="status-relay" checked
                                        onchange="updateStatusLabel(this, 'label_{{ $laboratoriummethod->method_id }}')">
                                    <span class="slider"></span>
                                </label>
                                <small id="label_{{ $laboratoriummethod->method_id }}" class="status-badge active">
                                    <i class="fa fa-check-circle"></i> Wajib Diisi
                                </small>
                            </div>
                        </div>

                        <div class="show_{{ $laboratoriummethod->method_id }}">
                            <!-- Baku Mutu -->
                            <div class="input-group-mobile">
                                <label>Kadar Maksimum Yang Diperbolehkan</label>
                                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                    {!! rubahNilaikeForm($laboratoriummethod->nilai_baku_mutu) ?? '-' !!}
                                </div>
                            </div>

                            <!-- Satuan -->
                            <div class="input-group-mobile">
                                <label>Satuan</label>
                                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                    {!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}
                                </div>
                            </div>

                            <!-- Hasil -->
                            <div class="input-group-mobile">
                                <label>Hasil</label>
                                @php
                                    $isOption = false;
                                    $optionValue = '';
                                    if (
                                        isset($laboratoriummethod->method_is_option) &&
                                        $laboratoriummethod->method_is_option == 1
                                    ) {
                                        $isOption = true;
                                        $optionValue = $laboratoriummethod->method_option ?? '';
                                    }
                                    $options = [];
                                    if ($isOption && !empty($optionValue)) {
                                        $options = array_map('trim', explode(',', $optionValue));
                                    }
                                    $currentResult = isset($laboratoriummethod->hasil)
                                        ? rubahNilaikeForm($laboratoriummethod->hasil)
                                        : '';
                                    if (
                                        empty($currentResult) &&
                                        isset($laboratoriummethod->equal) &&
                                        !empty($laboratoriummethod->equal)
                                    ) {
                                        $currentResult = rubahNilaikeForm($laboratoriummethod->equal);
                                    }
                                @endphp

                                <!-- Hidden textarea for form submission -->
                                <textarea class="form-control result_method result_method_{{ $laboratoriummethod->method_id }} hidden-field"
                                    id="result_method_{{ $laboratoriummethod->method_id }}" name="result_method_{{ $laboratoriummethod->method_id }}"
                                    data-min="{{ $laboratoriummethod->min }}" data-max="{{ $laboratoriummethod->max }}"
                                    data-equal="{{ $laboratoriummethod->equal }}" placeholder="Hasil" required style="display: none;">
@if ($laboratoriummethod->is_ready == 1)
{!! isset($laboratoriummethod->hasil)
    ? rubahNilaikeForm($laboratoriummethod->hasil)
    : (isset($laboratoriummethod->equal)
        ? rubahNilaikeForm($laboratoriummethod->equal)
        : '') !!}
@else
Alat Dan Reagen tidak tersedia
@endif
</textarea>

                                @if ($laboratoriummethod->is_ready == 1)
                                    @if ($isOption && count($options) > 0)
                                        <!-- is_option = 1: gunakan popup (seperti versi web) -->
                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                            data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                            data-method-name="{{ $laboratoriummethod->name_report }}"
                                            data-is-option="1"
                                            data-options='@json($options)'
                                            data-current-value="{{ $currentResult }}"
                                            style="width: 100%; margin-bottom: 10px;">
                                            <i class="fa fa-edit mr-1"></i>
                                            Pilih / Edit Hasil
                                        </button>
                                    @else
                                        <!-- is_option = 0: TinyMCE Editor -->
                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                            data-target="result_method_{{ $laboratoriummethod->method_id }}"
                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                            data-method-name="{{ $laboratoriummethod->name_report }}"
                                            style="width: 100%; margin-bottom: 10px;">
                                            <i class="fa fa-edit mr-1"></i>
                                            Edit dengan Editor
                                        </button>
                                    @endif
                                @endif

                                <!-- Result Preview -->
                                <div class="result-preview"
                                    id="result_output_method_{{ $laboratoriummethod->method_id }}">
                                    {!! cek_hasil_color(
                                        isset($laboratoriummethod->hasil)
                                            ? $laboratoriummethod->hasil
                                            : (isset($laboratoriummethod->equal)
                                                ? $laboratoriummethod->equal
                                                : ''),
                                        $laboratoriummethod->min,
                                        $laboratoriummethod->max,
                                        $laboratoriummethod->equal,
                                        'result_output_method_' . $laboratoriummethod->method_id,
                                        $laboratoriummethod->offset_baku_mutu,
                                        $laboratoriummethod->number_format ?? 'en'
                                    ) !!}
                                </div>
                            </div>

                            <!-- Metode -->
                            <div class="input-group-mobile">
                                <label>Metode</label>
                                @if ($laboratoriummethod->name_report == 'Kesadahan')
                                    @php
                                        $metode_kesadahan = explode('/', $laboratoriummethod->name_method);
                                    @endphp
                                    <select class="form-control" name="metode_{{ $laboratoriummethod->method_id }}">
                                        <option value="{{ $metode_kesadahan[0] }}">{{ $metode_kesadahan[0] }}
                                        </option>
                                        <option value="{{ $metode_kesadahan[1] }}">{{ $metode_kesadahan[1] }}
                                        </option>
                                    </select>
                                @else
                                    <textarea class="form-control" name="metode_{{ $laboratoriummethod->method_id }}" rows="2">{{ isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method }}</textarea>
                                @endif
                            </div>

                            <!-- Keterangan -->
                            <div class="input-group-mobile">
                                <label>Keterangan</label>
                                <!-- Hidden textarea for form submission -->
                                <textarea class="form-control" id="keterangan_{{ $laboratoriummethod->method_id }}"
                                    name="keterangan_{{ $laboratoriummethod->method_id }}" placeholder="Masukkan keterangan..." style="display: none;">{{ !empty($laboratoriummethod->keterangan) ? $laboratoriummethod->keterangan : '' }}</textarea>

                                <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                    data-target="keterangan_{{ $laboratoriummethod->method_id }}"
                                    data-method-id="{{ $laboratoriummethod->method_id }}"
                                    data-method-name="Keterangan - {{ $laboratoriummethod->name_report }}"
                                    style="width: 100%;">
                                    <i class="fa fa-edit mr-1"></i>
                                    Edit Keterangan
                                </button>
                            </div>

                            <!-- Offset Baku Mutu -->
                            <div class="input-group-mobile">
                                <label>Dianggap Melewati Baku Mutu</label>
                                <div class="baku-mutu-options">
                                    <div class="radio-option">
                                        <input type="radio"
                                            id="offset_baku_mutu_default_{{ $laboratoriummethod->method_id }}"
                                            value="default"
                                            name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                            class="offset_baku_mutu"
                                            {{ $laboratoriummethod->offset_baku_mutu == 'default' || !isset($laboratoriummethod->offset_baku_mutu) ? 'checked' : '' }}>
                                        <label for="offset_baku_mutu_default_{{ $laboratoriummethod->method_id }}">
                                            <span class="badge badge-secondary">Default</span>
                                            <small style="display: block; color: #666; font-size: 12px;">Otomatis
                                                sistem</small>
                                        </label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio"
                                            id="offset_baku_mutu_ya_{{ $laboratoriummethod->method_id }}"
                                            value="true"
                                            name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                            class="offset_baku_mutu"
                                            {{ $laboratoriummethod->offset_baku_mutu == 'true' ? 'checked' : '' }}>
                                        <label for="offset_baku_mutu_ya_{{ $laboratoriummethod->method_id }}">
                                            <span class="badge badge-danger">Ya, Melewati</span>
                                            <small style="display: block; color: #666; font-size: 12px;">Tidak
                                                memenuhi syarat</small>
                                        </label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio"
                                            id="offset_baku_mutu_tidak_{{ $laboratoriummethod->method_id }}"
                                            value="false"
                                            name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                            class="offset_baku_mutu"
                                            {{ $laboratoriummethod->offset_baku_mutu == 'false' ? 'checked' : '' }}>
                                        <label for="offset_baku_mutu_tidak_{{ $laboratoriummethod->method_id }}">
                                            <span class="badge badge-success">Tidak Melewati</span>
                                            <small style="display: block; color: #666; font-size: 12px;">Memenuhi
                                                syarat</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="not_show_{{ $laboratoriummethod->method_id }}" style="display: none;">
                            <div style="text-align: center; color: #999; padding: 20px;">
                                Parameter ini tidak diisi
                            </div>
                        </div>
                    </div>
                @else
                    @php $tidak_simpan = true; @endphp
                    <!-- Parameter tanpa baku mutu -->
                    <div class="parameter-card" style="border-left-color: #dc3545;">
                        <div class="alert alert-warning" style="margin-bottom: 15px;">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            <strong>Baku mutu belum tersedia</strong><br>
                            Baku mutu untuk parameter <strong>{{ $laboratoriummethod->params_method }}</strong>,
                            untuk jenis sarana
                            <strong>{{ $sample->name_sample_type }}{{ !isset($sample->jenis_makanan) ? '' : ' - ' . ($sample->jenis_makanan->name_jenis_makanan ?? '') }}</strong>
                            belum tersedia.
                        </div>
                        <button type="button" class="btn btn-primary btn-tambah-baku-mutu"
                            data-method-id="{{ $laboratoriummethod->method_id }}"
                            data-method-name="{{ $laboratoriummethod->params_method }}"
                            data-sample-type-id="{{ $sample->id_sample_type }}"
                            data-sample-type-name="{{ $sample->name_sample_type }}"
                            data-jenis-makanan-id="{{ $sample->jenis_makanan_id ?? '' }}"
                            data-jenis-makanan-name="{{ isset($sample->jenis_makanan) ? $sample->jenis_makanan->name_jenis_makanan : '' }}"
                            data-lab-code="{{ $lab->kode_laboratorium }}"
                            style="width: 100%;">
                            <i class="fa fa-plus mr-1"></i>
                            Tambah Baku Mutu
                        </button>
                    </div>
                @endif
            @else
                <!-- Parameter dengan detail -->
                @if (isset($laboratoriummethod->name_report))
                    <div class="parameter-card">
                        <div class="parameter-header">
                            <div class="parameter-name">
                                {{ $no }}. {!! $laboratoriummethod->name_report !!}
                            </div>
                        </div>

                        @foreach ($laboratoriummethod['detail'] as $detail)
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                                <div style="font-weight: 600; margin-bottom: 10px;">
                                    {!! $detail->name_sample_result_detail !!}
                                </div>

                                <div class="status-toggle" style="margin-bottom: 10px;">
                                    <label class="switch">
                                        <input type="checkbox" id="status_{{ $detail->id_sample_result_detail }}"
                                            value="true" name="status_{{ $detail->id_sample_result_detail }}"
                                            class="status-relay" checked
                                            onchange="updateStatusLabel(this, 'label_{{ $detail->id_sample_result_detail }}')">
                                        <span class="slider"></span>
                                    </label>
                                    <small id="label_{{ $detail->id_sample_result_detail }}"
                                        class="status-badge active">
                                        <i class="fa fa-check-circle"></i> Wajib Diisi
                                    </small>
                                </div>

                                <div class="show_{{ $detail->id_sample_result_detail }}">
                                    <!-- Baku Mutu Detail -->
                                    <div class="input-group-mobile">
                                        <label>Kadar Maksimum</label>
                                        <div style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                            {!! $detail->nilai_sample_result_detail !!}
                                        </div>
                                    </div>

                                    <!-- Satuan Detail -->
                                    <div class="input-group-mobile">
                                        <label>Satuan</label>
                                        <div style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                            {!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}
                                        </div>
                                    </div>

                                    <!-- Hasil Detail -->
                                    <div class="input-group-mobile">
                                        <label>Hasil</label>
                                        @php
                                            $isOptionDetail = false;
                                            $optionValueDetail = '';
                                            if (
                                                isset($laboratoriummethod->method_is_option) &&
                                                $laboratoriummethod->method_is_option == 1
                                            ) {
                                                $isOptionDetail = true;
                                                $optionValueDetail = $laboratoriummethod->method_option ?? '';
                                            }
                                            $optionsDetail = [];
                                            if ($isOptionDetail && !empty($optionValueDetail)) {
                                                $optionsDetail = array_map('trim', explode(',', $optionValueDetail));
                                            }
                                            $currentResultDetail = isset($detail->hasil)
                                                ? rubahNilaikeForm($detail->hasil)
                                                : '';
                                            if (
                                                empty($currentResultDetail) &&
                                                isset($detail->equal_sample_result_detail) &&
                                                !empty($detail->equal_sample_result_detail)
                                            ) {
                                                $currentResultDetail = rubahNilaikeForm(
                                                    $detail->equal_sample_result_detail,
                                                );
                                            }
                                        @endphp

                                        <textarea class="form-control result_method hidden-field" id="result_method_{{ $detail->id_sample_result_detail }}"
                                            name="result_method_{{ $detail->id_sample_result_detail }}" data-min="{{ $detail->min_sample_result_detail }}"
                                            data-max="{{ $detail->max_sample_result_detail }}" data-equal="{{ $detail->equal_sample_result_detail }}"
                                            placeholder="Hasil" required style="display: none;">{!! isset($detail->hasil)
                                                ? rubahNilaikeForm($detail->hasil)
                                                : (isset($detail->equal_sample_result_detail)
                                                    ? rubahNilaikeForm($detail->equal_sample_result_detail)
                                                    : '') !!}</textarea>

                                        @if ($laboratoriummethod->is_ready == 1)
                                            @if ($isOptionDetail && count($optionsDetail) > 0)
                                                <!-- detail is_option = 1: gunakan popup (seperti versi web) -->
                                                <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                                    data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                    data-method-id="{{ $detail->id_sample_result_detail }}"
                                                    data-method-name="{{ $detail->nama_sample_result_detail }}"
                                                    data-is-option="1"
                                                    data-options='@json($optionsDetail)'
                                                    data-current-value="{{ $currentResultDetail }}"
                                                    style="width: 100%; margin-bottom: 10px;">
                                                    <i class="fa fa-edit mr-1"></i>
                                                    Pilih / Edit Hasil
                                                </button>
                                            @else
                                                <!-- detail is_option = 0: TinyMCE Editor -->
                                                <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                                    data-target="result_method_{{ $detail->id_sample_result_detail }}"
                                                    data-method-id="{{ $detail->id_sample_result_detail }}"
                                                    data-method-name="{{ $detail->nama_sample_result_detail }}"
                                                    style="width: 100%; margin-bottom: 10px;">
                                                    <i class="fa fa-edit mr-1"></i>
                                                    Edit dengan Editor
                                                </button>
                                            @endif
                                        @endif

                                        <!-- Result Preview Detail -->
                                        <div class="result-preview"
                                            id="result_output_method_{{ $detail->id_sample_result_detail }}">
                                            {!! cek_hasil_color(
                                                isset($detail->hasil)
                                                    ? $detail->hasil
                                                    : (isset($detail->equal_sample_result_detail)
                                                        ? $detail->equal_sample_result_detail
                                                        : ''),
                                                $detail->min_sample_result_detail,
                                                $detail->max_sample_result_detail,
                                                $detail->equal_sample_result_detail,
                                                'result_output_method_' . $detail->id_sample_result_detail,
                                                $detail->offset_baku_mutu,
                                                $detail->number_format ?? 'en'
                                            ) !!}
                                        </div>
                                    </div>

                                    <!-- Metode Detail -->
                                    <div class="input-group-mobile">
                                        <label>Metode</label>
                                        @if ($laboratoriummethod->name_report == 'Kesadahan')
                                            @php
                                                $metode_kesadahan = explode('/', $laboratoriummethod->name_method);
                                            @endphp
                                            <select class="form-control"
                                                name="metode_{{ $laboratoriummethod->method_id }}">
                                                <option value="{{ $metode_kesadahan[0] }}">
                                                    {{ $metode_kesadahan[0] }}</option>
                                                <option value="{{ $metode_kesadahan[1] }}">
                                                    {{ $metode_kesadahan[1] }}</option>
                                            </select>
                                        @else
                                            <textarea class="form-control" name="metode_{{ $laboratoriummethod->method_id }}" rows="2">{{ isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method }}</textarea>
                                        @endif
                                    </div>

                                    <!-- Keterangan Detail -->
                                    <div class="input-group-mobile">
                                        <label>Keterangan</label>
                                        <!-- Hidden textarea for form submission -->
                                        <textarea class="form-control" id="keterangan_detail_{{ $laboratoriummethod->method_id }}"
                                            name="keterangan_{{ $laboratoriummethod->method_id }}" style="display: none;">{{ !empty($laboratoriummethod->keterangan) ? $laboratoriummethod->keterangan : '' }}</textarea>

                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal"
                                            data-target="keterangan_detail_{{ $laboratoriummethod->method_id }}"
                                            data-method-id="{{ $laboratoriummethod->method_id }}"
                                            data-method-name="Keterangan - {{ $laboratoriummethod->name_report }}"
                                            style="width: 100%;">
                                            <i class="fa fa-edit mr-1"></i>
                                            Edit Keterangan
                                        </button>
                                    </div>

                                    <!-- Offset Baku Mutu Detail -->
                                    <div class="input-group-mobile">
                                        <label>Dianggap Melewati Baku Mutu</label>
                                        <div class="baku-mutu-options">
                                            <div class="radio-option">
                                                <input type="radio"
                                                    id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_default"
                                                    value="default"
                                                    name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                    class="offset_baku_mutu"
                                                    {{ $detail->offset_baku_mutu == 'default' || !isset($detail->offset_baku_mutu) ? 'checked' : '' }}>
                                                <label
                                                    for="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_default">
                                                    Default
                                                </label>
                                            </div>
                                            <div class="radio-option">
                                                <input type="radio"
                                                    id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_ya"
                                                    value="true"
                                                    name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                    class="offset_baku_mutu"
                                                    {{ $detail->offset_baku_mutu == 'true' ? 'checked' : '' }}>
                                                <label
                                                    for="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_ya">
                                                    Ya
                                                </label>
                                            </div>
                                            <div class="radio-option">
                                                <input type="radio"
                                                    id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_tidak"
                                                    value="false"
                                                    name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                                    class="offset_baku_mutu"
                                                    {{ $detail->offset_baku_mutu == 'false' ? 'checked' : '' }}>
                                                <label
                                                    for="offset_baku_mutu_{{ $detail->id_sample_result_detail }}_tidak">
                                                    Tidak
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="not_show_{{ $detail->id_sample_result_detail }}" style="display: none;">
                                    <div style="text-align: center; color: #999; padding: 10px;">
                                        Parameter ini tidak diisi
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php $tidak_simpan = true; @endphp
                    <!-- Parameter detail tanpa baku mutu -->
                    <div class="parameter-card" style="border-left-color: #dc3545;">
                        <div class="alert alert-warning" style="margin-bottom: 15px;">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            <strong>Baku mutu belum tersedia</strong><br>
                            Baku mutu untuk parameter <strong>{{ $laboratoriummethod->params_method }}</strong>,
                            untuk jenis sarana
                            <strong>{{ $sample->name_sample_type }}{{ !isset($sample->jenis_makanan) ? '' : ' - ' . ($sample->jenis_makanan->name_jenis_makanan ?? '') }}</strong>
                            belum tersedia.
                        </div>
                        <button type="button" class="btn btn-primary btn-tambah-baku-mutu"
                            data-method-id="{{ $laboratoriummethod->method_id }}"
                            data-method-name="{{ $laboratoriummethod->params_method }}"
                            data-sample-type-id="{{ $sample->id_sample_type }}"
                            data-sample-type-name="{{ $sample->name_sample_type }}"
                            data-jenis-makanan-id="{{ $sample->jenis_makanan_id ?? '' }}"
                            data-jenis-makanan-name="{{ isset($sample->jenis_makanan) ? $sample->jenis_makanan->name_jenis_makanan : '' }}"
                            data-lab-code="{{ $lab->kode_laboratorium }}"
                            style="width: 100%;">
                            <i class="fa fa-plus mr-1"></i>
                            Tambah Baku Mutu
                        </button>
                    </div>
                @endif
            @endif

            @php $no++; @endphp
        @endforeach
    </div>

    @if (!$tidak_simpan)
        <!-- Confirmation Checkbox -->
        <div class="card">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <input type="checkbox" name="baca_hasil" value="ya"
                    id="input_checkbox_confirm_submit_baca_hasil" required
                    style="width: 20px; height: 20px; margin-top: 3px;">
                <label for="input_checkbox_confirm_submit_baca_hasil" style="flex: 1; cursor: pointer;">
                    <strong>Pengisian Hasil sudah sesuai dengan hasil uji lapangan.</strong>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <button type="button" id="submitAll" class="btn btn-success">
                <span>✓</span>
                <span>Selesai</span>
            </button>
            <button type="button" id="saveAll" class="btn btn-primary">
                <span>💾</span>
                <span>Simpan</span>
            </button>
            <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-secondary"
                style="text-decoration: none; display: block;">
                <span>←</span>
                <span>Kembali</span>
            </a>
        </div>
    @endif
    </form>
    </div>

    <script>
        // Wait for all scripts to load before initializing
        (function() {
            function initApp() {
                // Ensure jQuery and Bootstrap are loaded
                if (typeof jQuery === 'undefined') {
                    console.error('jQuery is not loaded. Please check CDN links.');
                    setTimeout(initApp, 100);
                    return;
                }

                if (typeof jQuery.fn.modal === 'undefined') {
                    console.error('Bootstrap modal is not loaded. Retrying...');
                    setTimeout(initApp, 100);
                    return;
                }

                // All libraries loaded, initialize app
                jQuery(document).ready(function($) {
                    var CSRF_TOKEN = $('#csrf-token').val();

                    // Function to update status label
                    function updateStatusLabel(checkbox, labelId) {
                        var label = document.getElementById(labelId);
                        var methodId = checkbox.id.replace('status_', '');
                        if (checkbox.checked) {
                            label.className = 'status-badge active';
                            label.innerHTML = '<i class="fa fa-check-circle"></i> Wajib Diisi';
                            $(".not_show_" + methodId).hide();
                            $(".show_" + methodId).show();
                        } else {
                            label.className = 'status-badge inactive';
                            label.innerHTML = '<i class="fa fa-times-circle"></i> Boleh Kosong';
                            $(".show_" + methodId).hide();
                            $(".not_show_" + methodId).show();
                        }
                    }

                    // Make updateStatusLabel global
                    window.updateStatusLabel = updateStatusLabel;

                    // Helper function to format value for display
                    function toFormatHtml(value) {
                        if (!value) return '';
                        // Auto-close kurung yang tidak tertutup untuk pangkat
                        var openSupCount = (value.match(/\^\(/g) || []).length;
                        var closeCount = (value.match(/\)/g) || []).length;
                        if (openSupCount > closeCount) {
                            for (var i = 0; i < (openSupCount - closeCount); i++) {
                                value += ')';
                            }
                        }
                        
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
                        
                        // Convert to HTML
                        value = value.replace(/≤/g, '&#8804;');
                        value = value.replace(/≥/g, '&#8805;');
                        value = value.replace(/</g, '&#60;');
                        value = value.replace(/>/g, '&#62;');
                        value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                        value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                        return value;
                    }

                    // Helper function to normalize string
                    function normalizeString(str) {
                        if (!str) return '';
                        str = str.toString();
                        str = str.replace(/&nbsp;/g, ' ');
                        str = str.replace(/\s/g, '');
                        return str;
                    }

                    // Helper function to create result badge
                    function createResultBadge(value, type) {
                        var badgeClass, icon, additionalIcon = '';
                        if (type === 'danger') {
                            badgeClass = 'badge badge-danger';
                            icon = '<i class="fa fa-times-circle"></i> ';
                            additionalIcon = ' <i class="fa fa-exclamation-triangle"></i>';
                        } else if (type === 'success') {
                            badgeClass = 'badge badge-success';
                            icon = '<i class="fa fa-check-circle"></i> ';
                        } else {
                            badgeClass = 'badge badge-secondary';
                            icon = '';
                        }
                        return '<span class="' + badgeClass + '" style="font-size: 14px; padding: 8px 12px;">' +
                            icon + value + additionalIcon + '</span>';
                    }

                    // Note: result-input is no longer used, replaced with TinyMCE editor
                    // Input changes are handled through the editor modal

                    // Handle dropdown change
                    $(document).on('change', '.result-dropdown', function() {
                        var methodId = $(this).data('method-id');
                        var selectedValue = $(this).val();
                        var $textarea = $('#result_method_' + methodId);
                        if ($textarea.length) {
                            $textarea.val(selectedValue);
                            updateResultPreview('result_method_' + methodId);
                        }
                    });

                    // Helper function to check if value is valid numeric
                    function isValidNumeric(val, format) {
                        format = format || 'en';
                        var num = parseNumberInput(val, format);
                        return num !== null && !isNaN(num) && isFinite(num);
                    }

                    // Helper function to check if value is valid (for equal check)
                    function isValidEqual(val) {
                        return val !== "" && val !== null && val !== undefined && String(val).trim() !== "";
                    }

                    // Helper function to extract numeric value from string (matching PHP logic)
                    function extractNumericValue(str, format) {
                        format = format || 'en';
                        if (!str) return null;
                        // Clean string: remove HTML tags, &nbsp;, and trim
                        var cleaned = str.toString().replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                        
                        // Extract numeric value (handle format like ">100", "<5", etc.)
                        // Match: optional >, <, ≤, ≥ followed by optional whitespace and number
                        var match = cleaned.match(/^[<>≤≥]?\s*([\d.,]+)/);
                        if (match) {
                            return parseNumberInput(match[1], format);
                        }
                        
                        // Try direct parsing
                        var num = parseNumberInput(cleaned, format);
                        return num;
                    }

                    // Update result preview (matching PHP cek_hasil_color logic)
                    function updateResultPreview(textareaId) {
                        var $textarea = $('#' + textareaId);
                        if (!$textarea.length) return;

                        var id = textareaId.replace('result_method_', '');
                        var rawValue = $textarea.val();
                        var min = $textarea.data('min');
                        var max = $textarea.data('max');
                        var equal = $textarea.data('equal');
                        var offset_baku_mutu = $('input[name="offset_baku_mutu_' + id + '"]:checked').val();
                        
                        // Get number format (default to 'en' for backward compatibility)
                        var numberFormat = 'en';

                        // Normalize offset_baku_mutu
                        offset_baku_mutu = offset_baku_mutu ? String(offset_baku_mutu).toLowerCase().trim() :
                            "default";

                        // Remove spaces from rawValue for checking
                        var delete_space = rawValue ? String(rawValue).replace(/\s/g, '') : '';

                        if (delete_space && delete_space !== "" && delete_space !== "-") {
                            // Manual override: false = tidak melewati, true = melewati
                            if (offset_baku_mutu === "false") {
                                var value = toFormatHtml(rawValue);
                                $('#result_output_method_' + id).html(createResultBadge(value, 'success'));
                            } else if (offset_baku_mutu === "true") {
                                var value = toFormatHtml(rawValue);
                                $('#result_output_method_' + id).html(createResultBadge(value, 'danger'));
                            } else {
                                // Automatic calculation (matching PHP logic)
                                if (rawValue !== "-" && rawValue !== "") {
                                    var melewati_baku_mutu = false;

                                    // Clean hasil untuk perhitungan numerik
                                    var hasil_clean = rawValue.toString().replace(/&nbsp;/g, ' ').trim();

                                    // Extract numeric value
                                    var hasil_numeric = extractNumericValue(rawValue, numberFormat);

                                    // Cek dengan equal terlebih dahulu (jika ada equal, itu prioritas)
                                    if (isValidEqual(equal)) {
                                        var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(
                                            /\s/g, '');
                                        var hasil_compare = hasil_clean.replace(/\s/g, '');

                                        if (hasil_compare !== equal_clean) {
                                            melewati_baku_mutu = true;
                                        } else {
                                            melewati_baku_mutu = false;
                                        }
                                    }
                                    // Jika tidak ada equal, cek dengan min dan max
                                    else if (hasil_numeric !== null) {
                                        var dbFormat = numberFormat || 'en'; // Use numberFormat or fallback to 'en'
                                        var hasMin = isValidNumeric(min, dbFormat);
                                        var hasMax = isValidNumeric(max, dbFormat);

                                        // Jika ada min DAN max, pastikan hasil dalam range
                                        if (hasMin && hasMax) {
                                            var minNum = parseNumberInput(min, dbFormat);
                                            var maxNum = parseNumberInput(max, dbFormat);
                                            if (hasil_numeric < minNum || hasil_numeric > maxNum) {
                                                melewati_baku_mutu = true;
                                            } else {
                                                melewati_baku_mutu = false;
                                            }
                                        }
                                        // Jika hanya ada min
                                        else if (hasMin) {
                                            var minNum = parseNumberInput(min, dbFormat);
                                            if (hasil_numeric < minNum) {
                                                melewati_baku_mutu = true;
                                            } else {
                                                melewati_baku_mutu = false;
                                            }
                                        }
                                        // Jika hanya ada max
                                        else if (hasMax) {
                                            var maxNum = parseNumberInput(max, dbFormat);
                                            // Handle format ">100" - jika hasil > max, maka melewati
                                            if (/^>\s*[\d.,]+/.test(hasil_clean)) {
                                                if (hasil_numeric > maxNum) {
                                                    melewati_baku_mutu = true;
                                                } else {
                                                    melewati_baku_mutu = false;
                                                }
                                            } else if (hasil_numeric > maxNum) {
                                                melewati_baku_mutu = true;
                                            } else {
                                                melewati_baku_mutu = false;
                                            }
                                        }
                                        // Jika tidak ada min dan max, anggap tidak melewati
                                        else {
                                            melewati_baku_mutu = false;
                                        }
                                    }
                                    // Jika hasil tidak numeric dan tidak ada equal, anggap tidak melewati
                                    else {
                                        melewati_baku_mutu = false;
                                    }

                                    // Apply styling based on result
                                    var value = toFormatHtml(rawValue);
                                    if (melewati_baku_mutu) {
                                        $('#result_output_method_' + id).html(createResultBadge(value,
                                            'danger'));
                                    } else {
                                        $('#result_output_method_' + id).html(createResultBadge(value,
                                            'success'));
                                    }
                                } else {
                                    $('#result_output_method_' + id).html(createResultBadge('-', 'secondary'));
                                }
                            }
                        } else {
                            $('#result_output_method_' + id).html(createResultBadge('-', 'secondary'));
                        }
                    }

                    // Handle offset_baku_mutu change
                    $(document).on('change', '.offset_baku_mutu', function() {
                        var id = $(this).attr('id');
                        if (id.indexOf('offset_baku_mutu_ya_') !== -1) {
                            id = id.substring(20);
                        } else if (id.indexOf('offset_baku_mutu_tidak_') !== -1) {
                            id = id.substring(23);
                        } else if (id.indexOf('offset_baku_mutu_default_') !== -1) {
                            id = id.substring(25);
                        } else {
                            // For detail
                            id = id.replace('offset_baku_mutu_', '').replace('_default', '').replace(
                                    '_ya', '')
                                .replace('_tidak', '');
                        }
                        updateResultPreview('result_method_' + id);
                    });

                    // Initialize: sync dropdown to textarea
                    $('.result-dropdown').each(function() {
                        var methodId = $(this).data('method-id');
                        var selectedValue = $(this).val();
                        var $textarea = $('#result_method_' + methodId);
                        if ($textarea.length && selectedValue) {
                            $textarea.val(selectedValue);
                        }
                    });

                    // === TINYMCE EDITOR MODAL ===
                    var currentEditorTarget = null;
                    var editorInstance = null;
                    var currentMethodId = null;
                    var currentIsOption = false;
                    var currentOptions = [];
                    var currentDefaultValue = '';
                    var allEditorButtons = [];

                    // Convert HTML to plain text (remove HTML tags)
                    function stripHtmlTags(html) {
                        if (!html) return '';
                        var tmp = document.createElement('DIV');
                        tmp.innerHTML = html;
                        return tmp.textContent || tmp.innerText || '';
                    }

                    // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
                    function convertToTinyMCE(value) {
                        if (!value) return '';

                        // Simple direct replacement - no complex placeholder system
                        // Step 1: Handle comparison symbols first
                        value = value.replace(/≤/g, '&le;');
                        value = value.replace(/≥/g, '&ge;');
                        value = value.replace(/±/g, '&plusmn;');

                        // Step 2: Convert ^() to <sup> and _() to <sub>
                        // Use regex with capturing group for content between markers
                        value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                        value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');

                        return value;
                    }

                    // Convert from HTML <sup> and <sub> to ^() and _() format for our system
                    function convertFromTinyMCE(value) {
                        if (!value) return '';

                        // Simple direct replacement
                        // Step 1: Convert HTML tags to ^() and _() format
                        value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                        value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');

                        // Step 2: Strip any remaining HTML tags
                        value = value.replace(/<[^>]*>/g, '');

                        // Step 3: Decode HTML entities
                        value = value.replace(/&le;/gi, '≤');
                        value = value.replace(/&ge;/gi, '≥');
                        value = value.replace(/&lt;/g, '<');
                        value = value.replace(/&gt;/g, '>');
                        value = value.replace(/&plusmn;/g, '±');
                        value = value.replace(/&nbsp;/g, ' ');

                        return value;
                    }

                    // Collect all editor buttons on page load (in DOM order)
                    function collectEditorButtons() {
                        allEditorButtons = [];
                        $('.open-editor-modal').each(function(index) {
                            allEditorButtons.push({
                                button: $(this),
                                methodId: $(this).data('method-id'),
                                targetId: $(this).data('target'),
                                methodName: $(this).data('method-name'),
                                index: index,
                                isOption: $(this).data('is-option') ? true : false,
                                options: $(this).data('options') || null,
                                currentValue: $(this).data('current-value') || ''
                            });
                        });
                    }

                    // Initialize on page load
                    collectEditorButtons();

                    // Function to open editor for a specific target (by targetId)
                    // NOTE: dibuat robust: jika buttonData tidak ditemukan (DOM berubah/baru render),
                    // gunakan data dari tombol yang diklik.
                    function openEditorForTarget(targetId, $clickedButton) {
                        var buttonData = allEditorButtons.find(function(item) {
                            return item.targetId == targetId;
                        });

                        if (!buttonData && $clickedButton && $clickedButton.length) {
                            buttonData = {
                                button: $clickedButton,
                                methodId: $clickedButton.data('method-id'),
                                targetId: targetId,
                                methodName: $clickedButton.data('method-name'),
                                isOption: $clickedButton.data('is-option') ? true : false,
                                options: $clickedButton.data('options') || null,
                                currentValue: $clickedButton.data('current-value') || ''
                            };
                        }

                        if (buttonData) {
                            var methodName = buttonData.methodName;
                            var methodId = buttonData.methodId;

                            // Set current target BEFORE getting value (important for next navigation)
                            currentEditorTarget = targetId;
                            currentMethodId = methodId;
                            currentIsOption = buttonData.isOption || false;
                            currentDefaultValue = buttonData.currentValue || '';

                            // Parse options (if any) menjadi array
                            currentOptions = [];
                            if (buttonData.options) {
                                try {
                                    if (Array.isArray(buttonData.options)) {
                                        currentOptions = buttonData.options;
                                    } else if (typeof buttonData.options === 'string') {
                                        currentOptions = JSON.parse(buttonData.options);
                                    }
                                } catch (e) {
                                    console.warn('Failed to parse options for editor:', e);
                                    currentOptions = [];
                                }
                            }

                            // Strip HTML tags from methodName for modal title
                            var methodNamePlain = stripHtmlTags(methodName);

                            // Set modal title
                            $('#editorModalLabel').text('Editor - ' + methodNamePlain);

                            // Clear editor content first (will be set when modal is shown)
                            $('#editor_content').val('');

                            // Show modal (value will be loaded from target textarea in shown.bs.modal event)
                            $('#editorModal').modal('show');
                        }
                    }

                    // Open editor modal - delegated handler (lebih aman untuk DOM yang berubah)
                    $(document).on('click', '.open-editor-modal', function() {
                        // refresh cache tombol (untuk kasus render dinamis)
                        collectEditorButtons();

                        var $btn = $(this);
                        var targetId = $btn.data('target');
                        openEditorForTarget(targetId, $btn);
                    });

                    // Function to get next target ID (based on DOM order, same type only)
                    function getNextTargetId() {
                        if (!currentEditorTarget || allEditorButtons.length === 0) {
                            return null;
                        }

                        // Determine current input type (hasil or keterangan)
                        var currentType = '';
                        if (currentEditorTarget.startsWith('result_method_')) {
                            currentType = 'hasil';
                        } else if (currentEditorTarget.startsWith('keterangan') || currentEditorTarget
                            .startsWith(
                                'keterangan_detail')) {
                            currentType = 'keterangan';
                        }

                        if (!currentType) {
                            return null;
                        }

                        var currentIndex = -1;
                        for (var i = 0; i < allEditorButtons.length; i++) {
                            if (allEditorButtons[i].targetId == currentEditorTarget) {
                                currentIndex = i;
                                break;
                            }
                        }

                        // Find next button of the same type in DOM order
                        if (currentIndex >= 0) {
                            for (var i = currentIndex + 1; i < allEditorButtons.length; i++) {
                                var nextTargetId = allEditorButtons[i].targetId;
                                var nextType = '';

                                if (nextTargetId.startsWith('result_method_')) {
                                    nextType = 'hasil';
                                } else if (nextTargetId.startsWith('keterangan') || nextTargetId.startsWith(
                                        'keterangan_detail')) {
                                    nextType = 'keterangan';
                                }

                                // Return if same type
                                if (nextType == currentType) {
                                    return nextTargetId;
                                }
                            }
                        }

                        return null;
                    }

                    // Initialize editor modal when shown (dropdown mode for is_option=1, TinyMCE for others)
                    $('#editorModal').on('shown.bs.modal', function() {
                        // Reset mode containers
                        $('#editor_option_container').hide();
                        $('#editor_text_container').hide();

                        // MODE OPTION (is_option = 1): dropdown di dalam modal
                        if (currentIsOption && currentOptions && currentOptions.length > 0 && currentEditorTarget) {
                            var $select = $('#editor_option_select');
                            $select.empty();

                            var $target = $('#' + currentEditorTarget);
                            var currentVal = ($target.val() || '').toString();

                            // Jika belum ada nilai, gunakan default dari server (hasil/equal) bila tersedia
                            if (!currentVal && currentDefaultValue) {
                                currentVal = currentDefaultValue.toString();
                            }

                            // Fallback: gunakan data-equal (baku mutu) sebagai default jika masih kosong
                            var equalVal = $target.data('equal');
                            if (!currentVal && equalVal !== null && equalVal !== undefined && String(equalVal).trim() !== '') {
                                currentVal = String(equalVal);
                            }

                            // Opsi placeholder
                            $select.append($('<option>', {
                                value: '',
                                text: 'Pilih hasil'
                            }));

                            var currentNorm = normalizeString(currentVal).toLowerCase();
                            currentOptions.forEach(function(opt) {
                                var optStr = (opt !== null && opt !== undefined) ? String(opt) : '';
                                var optNorm = normalizeString(optStr).toLowerCase();
                                $select.append($('<option>', {
                                    value: optStr,
                                    text: optStr,
                                    selected: currentNorm && currentNorm === optNorm
                                }));
                            });

                            $('#editor_option_container').show();

                            // Pastikan TinyMCE tidak aktif di mode option
                            if (editorInstance) {
                                try {
                                    tinymce.remove('#editor_content');
                                } catch (e) {}
                                editorInstance = null;
                            }
                            return;
                        }

                        // MODE TINYMCE (default)
                        $('#editor_text_container').show();

                        // Remove existing editor instance if any
                        if (editorInstance) {
                            try {
                                tinymce.remove('#editor_content');
                            } catch (e) {}
                            editorInstance = null;
                        }

                        // Get fresh value from target textarea (not from editor content)
                        var targetValue = '';
                        if (currentEditorTarget) {
                            targetValue = $('#' + currentEditorTarget).val() || '';
                        }
                        var tinymceValue = convertToTinyMCE(targetValue);

                        // Set value to textarea before initializing TinyMCE
                        $('#editor_content').val(tinymceValue);

                        // Initialize TinyMCE
                        tinymce.init({
                            selector: '#editor_content',
                            height: 300,
                            base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                            suffix: '.min',
                            // Force all assets to load from CDN to avoid 404 to local paths
                            skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                            content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                            menubar: false,
                            plugins: [
                                'advlist autolink lists charmap',
                                'searchreplace code',
                                'insertdatetime paste help wordcount'
                            ],
                            toolbar: 'undo redo | bold italic underline | ' +
                                'superscript subscript | ' +
                                'charmap | ' +
                                'removeformat | code | help',
                            charmap_append: [
                                [60, 'less than'],
                                [62, 'greater than'],
                                [8804, 'less than or equal to'],
                                [8805, 'greater than or equal to'],
                                [177, 'plus-minus sign']
                            ],
                            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; padding: 10px; }',
                            setup: function(editor) {
                                editorInstance = editor;
                                editor.on('init', function() {
                                    // Set content after editor is fully initialized
                                    if (tinymceValue) {
                                        editor.setContent(tinymceValue);
                                    }
                                });
                            }
                        });
                    });

                    // Save from editor to textarea
                    function saveEditorContent(goToNext) {
                        goToNext = goToNext || false;

                        // MODE OPTION (is_option = 1): simpan dari dropdown modal
                        if (currentIsOption && currentEditorTarget) {
                            var selectedValue = $('#editor_option_select').val() || '';

                            // Jika user tidak memilih, tetap coba pakai default value (equal/baku mutu)
                            if (!selectedValue) {
                                var $target = $('#' + currentEditorTarget);
                                var fallback = currentDefaultValue || $target.data('equal') || $target.val() || '';
                                selectedValue = fallback ? String(fallback) : '';
                            }

                            // Set ke textarea target
                            $('#' + currentEditorTarget).val(selectedValue);

                            // Update preview jika hasil
                            if (currentEditorTarget.startsWith('result_method_')) {
                                $('#' + currentEditorTarget).trigger('input');
                                var id = currentEditorTarget.replace('result_method_', '');
                                updateResultPreview('result_method_' + id);
                            }

                            if (goToNext) {
                                var nextTargetId = getNextTargetId();
                                if (nextTargetId) {
                                    $('#editorModal').modal('hide');
                                    $('#editorModal').on('hidden.bs.modal', function() {
                                        $('#editorModal').off('hidden.bs.modal');
                                        setTimeout(function() {
                                            openEditorForTarget(nextTargetId);
                                        }, 300);
                                    });
                                } else {
                                    $('#editorModal').modal('hide');
                                }
                            } else {
                                $('#editorModal').modal('hide');
                            }
                            return;
                        }

                        if (editorInstance && currentEditorTarget) {
                            // Get content from TinyMCE (HTML format)
                            var htmlContent = editorInstance.getContent();

                            // Convert from TinyMCE HTML format to our ^() format
                            var convertedContent = convertFromTinyMCE(htmlContent);

                            // Set to original textarea
                            $('#' + currentEditorTarget).val(convertedContent);

                            // Trigger input event to update preview (if it's a result_method)
                            if (currentEditorTarget.startsWith('result_method_')) {
                                $('#' + currentEditorTarget).trigger('input');
                                // Also manually update preview
                                var id = currentEditorTarget.replace('result_method_', '');
                                updateResultPreview('result_method_' + id);
                            }

                            if (goToNext) {
                                // Get next target ID
                                var nextTargetId = getNextTargetId();
                                if (nextTargetId) {
                                    // Close modal first, then open next
                                    $('#editorModal').modal('hide');

                                    // Wait for modal to close, then open next
                                    $('#editorModal').on('hidden.bs.modal', function() {
                                        $('#editorModal').off('hidden.bs.modal');
                                        setTimeout(function() {
                                            openEditorForTarget(nextTargetId);
                                        }, 300);
                                    });
                                } else {
                                    // No next target, just close modal
                                    $('#editorModal').modal('hide');
                                }
                            } else {
                                // Close modal
                                $('#editorModal').modal('hide');
                            }
                        }
                    }

                    $('#saveEditorContent').on('click', function() {
                        saveEditorContent(false);
                    });

                    // Save and Next button
                    $('#saveAndNextEditorContent').on('click', function() {
                        saveEditorContent(true);
                    });

                    // Clean up on modal close
                    $('#editorModal').on('hidden.bs.modal', function() {
                        // Remove TinyMCE instance
                        if (editorInstance) {
                            try {
                                tinymce.remove('#editor_content');
                            } catch (e) {}
                            editorInstance = null;
                        }
                        // Don't reset currentEditorTarget and currentMethodId if we're going to next
                        // They will be reset when opening next editor
                    });

                    // Handle jenis sarana (MBI)
                    @if ($lab->kode_laboratorium == 'MBI')
                        $('#input_jenis_sarana').on('change', function() {
                            var value = $(this).val();
                            if (value == "Lainnya") {
                                $('#input_jenis_sarana_lainnya').prop('disabled', false).prop('hidden',
                                    false);
                            } else {
                                $('#input_jenis_sarana_lainnya').prop('disabled', true).prop('hidden',
                                    true);
                            }
                        });
                    @endif

                    // Submit All
                    $('#submitAll').on('click', function(e) {
                        e.preventDefault();

                        // Check if confirmation checkbox is checked
                        if (!$('#input_checkbox_confirm_submit_baca_hasil').is(':checked')) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Harap centang konfirmasi bahwa pengisian hasil sudah sesuai dengan hasil uji lapangan.'
                            });
                            return false;
                        }

                        $('#submitAll').text('Proses....').addClass('disabled');

                        // Sync all dropdowns and inputs to textareas
                        $('.result-dropdown').each(function() {
                            var methodId = $(this).data('method-id');
                            var selectedValue = $(this).val();
                            var $textarea = $('#result_method_' + methodId);
                            if ($textarea.length) {
                                $textarea.val(selectedValue);
                            }
                        });

                        // Note: result-input is no longer used, replaced with TinyMCE editor
                        // All values are already in hidden textareas

                        $('#form-baca-hasil').ajaxSubmit({
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: response.pesan,
                                        icon: "success"
                                    }).then(function() {
                                        if (response.url_redirect) {
                                            window.location.href = response
                                                .url_redirect;
                                        } else {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    var pesan = "";
                                    var data_pesan = response.pesan;
                                    if (typeof(data_pesan) == 'object') {
                                        jQuery.each(data_pesan, function(key, value) {
                                            pesan += value + '. <br>';
                                        });
                                        Swal.fire({
                                            title: "Error!",
                                            html: pesan,
                                            icon: "warning"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.pesan,
                                            icon: "warning"
                                        });
                                    }
                                }
                                $('#submitAll').text('Selesai').removeClass('disabled');
                            },
                            error: function(xhr) {
                                var err = xhr.responseJSON || {};
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: err.message ||
                                        'Terjadi kesalahan saat menyimpan data.'
                                });
                                $('#submitAll').text('Selesai').removeClass('disabled');
                            }
                        });
                    });

                    // Save All
                    $("#saveAll").on('click', function() {
                        $('#saveAll').text('Proses menyimpan....').addClass('disabled');

                        // Sync all dropdowns and inputs to textareas
                        $('.result-dropdown').each(function() {
                            var methodId = $(this).data('method-id');
                            var selectedValue = $(this).val();
                            var $textarea = $('#result_method_' + methodId);
                            if ($textarea.length) {
                                $textarea.val(selectedValue);
                            }
                        });

                        // Note: result-input is no longer used, replaced with TinyMCE editor
                        // All values are already in hidden textareas

                        var data = $('.form').serializeArray().reduce(function(obj, item) {
                            obj[item.name] = item.value;
                            return obj;
                        }, {});

                        var url =
                            "{{ route('mobile.testing.storeBacaHasil', ['id' => $sample->id_samples, 'lab_id' => $lab_id, 'method_id' => $method_id]) }}";
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: data,
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            success: function(response) {

                                if (response.success == true) {
                                    var pesan = "";
                                    var data_pesan = response.pesan;
                                    if (typeof(data_pesan) == 'object') {
                                        jQuery.each(data_pesan, function(key, value) {
                                            pesan += value + '. <br>';
                                        });
                                        Swal.fire({
                                            title: "Error!",
                                            html: pesan,
                                            icon: "warning"
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: response.pesan,
                                            icon: "warning"
                                        });
                                    }
                                }
                                $('#saveAll').text('Simpan').removeClass('disabled');
                            },
                            error: function(xhr) {
                                var err = xhr.responseJSON || {};
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: err.message ||
                                        'Terjadi kesalahan saat menyimpan data.'
                                });
                                $('#saveAll').text('Simpan').removeClass('disabled');
                            }
                        });
                    });

                    // === Jenis Makanan (MBI makanan) - on change reload, seperti web ===
                    @if ($lab->kode_laboratorium === 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya')
                        $('#jenis_makanan_id').on('change', function() {
                            var jenisId = $(this).val();
                            if (!jenisId) return;
                            var url = new URL(window.location.href);
                            url.searchParams.set('jenis_makanan_id', jenisId);
                            window.location.href = url.toString();
                        });
                    @endif

                    // ============================================
                    // JEMBATAN OTOMATIS BAKU MUTU
                    // ============================================

                    // Function untuk konversi dari format sistem ke TinyMCE HTML
                    function convertFromSystemToTinyMCE(systemContent) {
                        if (!systemContent) return '';
                        var content = systemContent;
                        content = content.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                        content = content.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                        content = content.replace(/≤/g, '&le;');
                        content = content.replace(/≥/g, '&ge;');
                        content = content.replace(/</g, '&lt;');
                        content = content.replace(/>/g, '&gt;');
                        content = content.replace(/±/g, '&plusmn;');
                        return content;
                    }

                    // Function untuk konversi dari TinyMCE HTML ke format sistem
                    function convertFromTinyMCEToSystem(htmlContent) {
                        if (!htmlContent) return '';
                        var content = htmlContent.replace(/<p>/gi, '').replace(/<\/p>/gi, '');
                        content = content.replace(/<br\s*\/?>/gi, '');
                        content = content.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                        content = content.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
                        content = content.replace(/&le;/gi, '≤');
                        content = content.replace(/&ge;/gi, '≥');
                        content = content.replace(/&lt;/gi, '<');
                        content = content.replace(/&gt;/gi, '>');
                        content = content.replace(/&plusmn;/gi, '±');
                        content = content.replace(/&nbsp;/gi, ' ');
                        content = content.replace(/<[^>]*>/gi, '');
                        return content.trim();
                    }

                    // Function untuk handle tambah baku mutu
                    function handleTambahBakuMutu(button) {
                        var $button = $(button);
                        var methodId = $button.data('method-id');
                        var methodName = $button.data('method-name');
                        var sampleTypeId = $button.data('sample-type-id');
                        var sampleTypeName = $button.data('sample-type-name');
                        var jenisMakananId = $button.data('jenis-makanan-id');
                        var jenisMakananName = $button.data('jenis-makanan-name');
                        var labCode = $button.data('lab-code');

                        // Reset form
                        $('#formTambahBakuMutu')[0].reset();

                        // Set data ke form modal
                        $('#modal-method-display').val(methodName);
                        $('#modal-method-id').val(methodId);
                        $('#modal-sample-type-display').val(sampleTypeName);
                        $('#modal-sampletype-id').val(sampleTypeId);

                        // Auto-fill nama parameter di laporan
                        $('#modal-name-report').val(methodName);

                        // Handle jenis makanan (khusus untuk sampel makanan/minuman/lainnya)
                        var currentJenisMakananId = $('#jenis_makanan_id').val();
                        var currentJenisMakananName = $('#jenis_makanan_id option:selected').text();

                        if (currentJenisMakananId && currentJenisMakananName && currentJenisMakananName !== '- Pilih Jenis Makanan -') {
                            $('#modal-jenis-makanan-display').val(currentJenisMakananName);
                            $('#modal-jenis-makanan-id').val(currentJenisMakananId);
                            $('#modal-jenis-makanan-group').show();
                        } else if (jenisMakananId && jenisMakananName) {
                            $('#modal-jenis-makanan-display').val(jenisMakananName);
                            $('#modal-jenis-makanan-id').val(jenisMakananId);
                            $('#modal-jenis-makanan-group').show();
                        } else {
                            $('#modal-jenis-makanan-group').hide();
                        }

                        // Simpan lab code untuk submit
                        $('#formTambahBakuMutu').data('lab-code', labCode);

                        // Tampilkan modal
                        $('#modalTambahBakuMutu').modal('show');
                    }

                    // Handle click tombol tambah baku mutu dengan event delegation
                    $(document).on('click', '.btn-tambah-baku-mutu', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        handleTambahBakuMutu(this);
                    });

                    // Initialize Select2 dan TinyMCE ketika modal baku mutu ditampilkan
                    $('#modalTambahBakuMutu').on('shown.bs.modal', function() {
                        var $modal = $(this);
                        
                        // Destroy existing Select2 instances if any
                        if ($('#modal-library-id').hasClass('select2-hidden-accessible')) {
                            $('#modal-library-id').select2('destroy');
                        }
                        if ($('#modal-unit-id').hasClass('select2-hidden-accessible')) {
                            $('#modal-unit-id').select2('destroy');
                        }

                        // Delay initialization untuk memastikan modal sudah fully rendered
                        setTimeout(function() {
                            // Initialize Select2 untuk Acuan Baku Mutu
                            var $librarySelect = $('#modal-library-id');
                            $librarySelect.select2({
                                placeholder: 'Pilih Acuan Baku Mutu',
                                allowClear: true,
                                dropdownParent: $modal,
                                width: '100%'
                            });

                            // Initialize Select2 untuk Satuan
                            var $unitSelect = $('#modal-unit-id');
                            $unitSelect.select2({
                                placeholder: 'Pilih Satuan',
                                allowClear: true,
                                dropdownParent: $modal,
                                width: '100%'
                            });

                            // Prevent modal from closing when clicking on Select2 dropdown
                            $(document).on('click.select2-modal', '.select2-dropdown', function(e) {
                                e.stopPropagation();
                            });
                        }, 100);

                        // Initialize TinyMCE untuk Nilai Sama Dengan
                        if (tinymce.get('modal-equal')) {
                            tinymce.get('modal-equal').remove();
                        }
                        tinymce.init({
                            selector: '#modal-equal',
                            height: 100,
                            menubar: false,
                            base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                            suffix: '.min',
                            skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                            content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                            plugins: ['charmap'],
                            toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                            charmap_append: [
                                [60, 'less than'],
                                [62, 'greater than'],
                                [8804, 'less than or equal to'],
                                [8805, 'greater than or equal to'],
                                [177, 'plus-minus sign']
                            ],
                            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; }',
                            setup: function(editor) {
                                editor.on('change', function() {
                                    tinymce.triggerSave();
                                });
                                editor.on('init', function() {
                                    var existingContent = $('#modal-equal').val();
                                    if (existingContent) {
                                        var convertedContent = convertFromSystemToTinyMCE(existingContent);
                                        editor.setContent(convertedContent);
                                    }
                                });
                            }
                        });

                        // Initialize TinyMCE untuk Nilai Baku Mutu di Laporan
                        if (tinymce.get('modal-nilai-baku-mutu')) {
                            tinymce.get('modal-nilai-baku-mutu').remove();
                        }
                        tinymce.init({
                            selector: '#modal-nilai-baku-mutu',
                            height: 150,
                            menubar: false,
                            base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                            suffix: '.min',
                            skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                            content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                            plugins: ['charmap'],
                            toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                            charmap_append: [
                                [60, 'less than'],
                                [62, 'greater than'],
                                [8804, 'less than or equal to'],
                                [8805, 'greater than or equal to'],
                                [177, 'plus-minus sign']
                            ],
                            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; }',
                            setup: function(editor) {
                                editor.on('change', function() {
                                    tinymce.triggerSave();
                                });
                                editor.on('init', function() {
                                    var existingContent = $('#modal-nilai-baku-mutu').val();
                                    if (existingContent) {
                                        var convertedContent = convertFromSystemToTinyMCE(existingContent);
                                        editor.setContent(convertedContent);
                                    }
                                });
                            }
                        });
                    });

                    // Cleanup TinyMCE ketika modal ditutup
                    $('#modalTambahBakuMutu').on('hidden.bs.modal', function() {
                        // Destroy Select2
                        if ($('#modal-library-id').hasClass('select2-hidden-accessible')) {
                            $('#modal-library-id').select2('destroy');
                        }
                        if ($('#modal-unit-id').hasClass('select2-hidden-accessible')) {
                            $('#modal-unit-id').select2('destroy');
                        }

                        // Remove Select2 event handlers
                        $(document).off('click.select2-modal', '.select2-dropdown');

                        // Destroy TinyMCE
                        if (tinymce.get('modal-equal')) {
                            tinymce.get('modal-equal').remove();
                        }
                        if (tinymce.get('modal-nilai-baku-mutu')) {
                            tinymce.get('modal-nilai-baku-mutu').remove();
                        }
                    });

                    // Handle submit form baku mutu
                    $('#formTambahBakuMutu').on('submit', function(e) {
                        e.preventDefault();

                        // Trigger TinyMCE save untuk memastikan content tersimpan ke textarea
                        tinymce.triggerSave();

                        // Konversi TinyMCE HTML ke format sistem sebelum submit
                        var equalContent = '';
                        var nilaiContent = '';

                        if (tinymce.get('modal-equal')) {
                            equalContent = convertFromTinyMCEToSystem(tinymce.get('modal-equal').getContent());
                            $('#modal-equal').val(equalContent);
                        }

                        if (tinymce.get('modal-nilai-baku-mutu')) {
                            nilaiContent = convertFromTinyMCEToSystem(tinymce.get('modal-nilai-baku-mutu').getContent());
                            $('#modal-nilai-baku-mutu').val(nilaiContent);
                        }

                        var labCode = $(this).data('lab-code');
                        var formData = $(this).serialize();

                        // Tentukan route berdasarkan lab
                        var route = '';
                        if (labCode === 'MBI') {
                            route = '{{ route('elits-baku-mutu-mikro.store') }}';
                        } else if (labCode === 'KIM') {
                            route = '{{ route('elits-baku-mutu-kimia.store') }}';
                        } else {
                            route = '{{ route('elits-baku-mutu-mikro.store') }}'; // Default ke mikro
                        }

                        // Disable tombol submit
                        $('#btnSimpanBakuMutu').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

                        $.ajax({
                            url: route,
                            type: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            success: function(response) {
                                if (response.status == true) {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Baku mutu berhasil ditambahkan. Halaman akan dimuat ulang untuk menampilkan baku mutu baru.",
                                        icon: "success"
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                } else {
                                    var pesan = "";
                                    var data_pesan = response.pesan;

                                    if (typeof(data_pesan) == 'object') {
                                        jQuery.each(data_pesan, function(key, value) {
                                            pesan += value + '. <br>';
                                        });
                                    } else {
                                        pesan = response.pesan;
                                    }

                                    Swal.fire({
                                        title: "Error!",
                                        html: pesan,
                                        icon: "warning"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                var errorMsg = 'Terjadi kesalahan saat menyimpan baku mutu.';
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMsg = response.message;
                                    }
                                } catch (e) {
                                    // Use default error message
                                }

                                Swal.fire({
                                    title: "Error!",
                                    text: errorMsg,
                                    icon: "error"
                                });
                            },
                            complete: function() {
                                // Re-enable tombol submit
                                $('#btnSimpanBakuMutu').prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan Baku Mutu');
                            }
                        });
                    });
                }); // End of jQuery(document).ready
            }

            // Start initialization when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initApp);
            } else {
                // DOM already loaded
                initApp();
            }
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-form@4.3.0/dist/jquery.form.min.js"></script>
    <!-- TinyMCE CDN from jsDelivr (Free, no API key required) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.7/tinymce.min.js"></script>
    <!-- Select2 for searchable Jenis Makanan -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- TinyMCE inline untuk field Asal Sampel / Lokasi Pengambilan + Select2 init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // TinyMCE untuk asal sampel
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#lokasi_pengambilan',
                    height: 200,
                    menubar: false,
                    base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                    suffix: '.min',
                    skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                    content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                    plugins: [
                        'advlist autolink lists charmap',
                        'searchreplace code',
                        'insertdatetime paste help wordcount'
                    ],
                    toolbar: 'undo redo | bold italic underline | ' +
                        'bullist numlist | alignleft aligncenter alignright | ' +
                        'removeformat | code | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; padding: 10px; }'
                });
            }

            // Select2 untuk Jenis Makanan (MBI)
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined' && $('#jenis_makanan_id').length) {
                $('#jenis_makanan_id').select2({
                    width: '100%',
                    placeholder: '- Pilih Jenis Makanan -',
                    theme: 'bootstrap4'
                });
            }
        });
    </script>

    <!-- TinyMCE Editor Modal -->
    <div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%); color: white;">
                    <h5 class="modal-title" id="editorModalLabel">
                        <i class="fa fa-edit mr-2"></i>Editor Hasil
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Mode Option (is_option = 1): dropdown di dalam modal -->
                    <div id="editor_option_container" style="display: none;">
                        <div class="form-group mb-0">
                            <label style="font-weight: 600; font-size: 13px;">Hasil</label>
                            <select id="editor_option_select" class="form-control">
                                <option value="">Pilih hasil</option>
                            </select>
                            <small class="text-muted d-block mt-2" style="font-size: 12px;">
                                Pilih salah satu opsi yang tersedia sesuai hasil uji.
                            </small>
                        </div>
                    </div>

                    <!-- Mode Text (default): TinyMCE -->
                    <div id="editor_text_container">
                        <div class="alert alert-info" style="font-size: 12px;">
                            <i class="fa fa-info-circle mr-2"></i>
                            <strong>Tips Penggunaan Editor:</strong>
                            <ul class="mb-0 mt-2" style="font-size: 12px;">
                                <li>Ketik angka atau teks hasil pengujian</li>
                                <li>Untuk <strong>pangkat (superscript)</strong>: pilih angka → klik tombol
                                    <strong>x<sup>2</sup></strong> di toolbar
                                </li>
                                <li>Untuk <strong>subscript</strong>: pilih angka → klik tombol
                                    <strong>x<sub>2</sub></strong> di toolbar
                                </li>
                                <li>Untuk <strong>simbol matematika</strong> (≤, ≥, ±, <,>): klik tombol <strong>Ω
                                            (Charmap)</strong> di toolbar</li>
                            </ul>
                        </div>
                        <textarea id="editor_content" name="editor_content"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" id="saveEditorContent">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAndNextEditorContent">
                        <i class="fa fa-save mr-1"></i>Simpan & Lanjut
                        <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Baku Mutu -->
    <div class="modal fade" id="modalTambahBakuMutu" tabindex="-1" role="dialog"
        aria-labelledby="modalTambahBakuMutuLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; margin: 10px auto;">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #2D6BCF 0%, #1e4a9e 100%); color: white;">
                    <h5 class="modal-title" id="modalTambahBakuMutuLabel">
                        <i class="fa fa-plus mr-2"></i>Tambah Baku Mutu
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formTambahBakuMutu">
                    @csrf
                    <input type="hidden" name="is_sub" value="false">
                    <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                        <!-- Informasi Dasar Parameter -->
                        <div class="card mb-3" style="border: 1px solid #e0e0e0;">
                            <div class="card-header" style="background: #f8f9fa; padding: 10px;">
                                <h6 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Dasar Parameter</h6>
                            </div>
                            <div class="card-body" style="padding: 15px;">
                                <div class="form-group">
                                    <label style="font-weight: 600;"><i class="fa fa-flask mr-1"></i>Jenis Sampel</label>
                                    <input type="text" id="modal-sample-type-display" class="form-control" readonly>
                                    <input type="hidden" id="modal-sampletype-id" name="sampletype_id">
                                </div>
                                <div class="form-group">
                                    <label style="font-weight: 600;"><i class="fa fa-list mr-1"></i>Parameter</label>
                                    <input type="text" id="modal-method-display" class="form-control" readonly>
                                    <input type="hidden" id="modal-method-id" name="method_id">
                                </div>

                                <div class="form-group" id="modal-jenis-makanan-group" style="display: none;">
                                    <label style="font-weight: 600;"><i class="fa fa-utensils mr-1"></i>Jenis Makanan
                                        <span class="badge badge-danger ml-1">Wajib</span></label>
                                    <input type="text" id="modal-jenis-makanan-display" class="form-control" readonly>
                                    <input type="hidden" id="modal-jenis-makanan-id" name="jenis_makanan_id">
                                </div>

                                <div class="form-group">
                                    <label style="font-weight: 600;"><i class="fa fa-file-alt mr-1"></i>Nama Parameter di
                                        Laporan</label>
                                    <input type="text" id="modal-name-report" name="name_report" class="form-control"
                                        placeholder="Nama Parameter di Laporan">
                                </div>

                                <div class="form-group">
                                    <label style="font-weight: 600;"><i class="fa fa-book mr-1"></i>Acuan Baku Mutu</label>
                                    <select name="library_id" id="modal-library-id" class="form-control">
                                        <option value="">Pilih Acuan Baku Mutu</option>
                                        @if (isset($libraries))
                                            @foreach ($libraries as $library)
                                                <option value="{{ $library->id_library }}">
                                                    {{ $library->title_library }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label style="font-weight: 600;"><i class="fa fa-ruler mr-1"></i>Satuan</label>
                                    <select id="modal-unit-id" name="unit_id" class="form-control">
                                        <option value="">Pilih Satuan</option>
                                        @if (isset($units))
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id_unit }}">{!! $unit->shortname_unit !!}</option>
                                            @endforeach
                                        @endif
                                        <option value="-">-</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Konfigurasi Baku Mutu -->
                        <div class="card mb-3" style="border: 1px solid #e0e0e0;">
                            <div class="card-header" style="background: #f8f9fa; padding: 10px;">
                                <h6 class="mb-0"><i class="fa fa-sliders mr-2"></i>Konfigurasi Baku Mutu</h6>
                            </div>
                            <div class="card-body" style="padding: 15px;">
                                <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                                    <h6 class="mb-3"><i class="fa fa-chart-line mr-2"></i>Nilai Baku Mutu</h6>

                                    <div class="form-group">
                                        <label style="font-weight: 600;"><i class="fa fa-arrow-down mr-1"></i>Min
                                            (Minimum)</label>
                                        <input type="text" class="form-control" id="modal-min" name="min_no_sub"
                                            placeholder="Contoh: 4.0">
                                    </div>
                                    <div class="form-group">
                                        <label style="font-weight: 600;"><i class="fa fa-arrow-up mr-1"></i>Max
                                            (Maksimum)</label>
                                        <input type="text" class="form-control" id="modal-max" name="max_no_sub"
                                            placeholder="Contoh: 6.5">
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 600;"><i class="fa fa-equals mr-1"></i>Nilai Sama
                                            Dengan</label>
                                        <textarea class="form-control" id="modal-equal" name="equal_no_sub" rows="3"
                                            placeholder="Contoh: Negatif"></textarea>
                                        <small class="form-text text-muted" style="font-size: 12px;">Untuk nilai non-range
                                            seperti Positif/Negatif</small>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 600;"><i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu
                                            di Laporan</label>
                                        <textarea class="form-control" id="modal-nilai-baku-mutu"
                                            name="nilai_baku_mutu_no_sub" rows="3" placeholder="Nilai Baku Mutu"></textarea>
                                        <small class="form-text text-muted" style="font-size: 12px;">Teks yang akan muncul di
                                            laporan hasil</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e0e0e0;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            style="width: auto; padding: 10px 20px;">
                            <i class="fa fa-times mr-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success" id="btnSimpanBakuMutu"
                            style="width: auto; padding: 10px 20px;">
                            <i class="fa fa-save mr-1"></i>Simpan Baku Mutu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </body>

</html>
