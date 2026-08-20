@extends('masterweb::template.admin.layout')
@section('title')
    Pengesahan Hasil
@endsection

@section('content')
    <style>
        /* Table styling */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .thead-light th {
            background-color: #e9ecef;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .badge {
            font-weight: 600;
        }

        /* PDF preview full height */
        .pdf-preview-container {
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            overflow: hidden;
            height: calc(100vh - 260px);
            min-height: 720px;
            width: 100%;
        }

        .pdf-preview-container iframe {
            width: 100%;
            height: 100%;
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

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-permohonan-uji') }}">
                                        Permohonan Uji</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">
                                        Daftar Pengujian</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ url('/elits-samples/verification-2', [Request::segment(2), Request::segment(3)]) }}">
                                        Analys</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page"><span>Pengesahan Hasil</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fa fa-file-signature mr-2"></i>
            <h4 class="mb-0">Pengesahan Hasil</h4>
        </div>
        <div class="card-body" style="background-color: #f8f9fa;">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- utama -->

                        <div class="col-md-12">
                            <div class="card border-0 mb-3" style="background-color: #ffffff;">
                                <div class="card-body">
                                    <h5 class="card-title text-info mb-3"><i class="fa fa-info-circle mr-2"></i>Informasi
                                        Sampel</h5>
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <th class="text-muted" width="20%"><b><i class="fa fa-user mr-2"></i>Nama
                                                    Pelanggan</b></th>
                                            <td>

                                                @php
                                                    $customer = str_replace(
                                                        // Hanya mencari simbol 'Π'
                                                        'π',
                                                        '<span style="font-family: \'DejaVu Sans\', sans-serif;">π</span>', // Ganti dengan <span> yang sesuai
                                                        $sample->name_pelanggan ??
                                                            optional(optional($sample->permohonanuji)->customer)->name_customer ??
                                                            '-',
                                                    );
                                                @endphp
                                                {!! $customer !!}
                                            </td>
                                            <th class="text-muted" width="20%"><b><i
                                                        class="fa fa-calendar mr-2"></i>Tanggal Pengambilan</b></th>
                                            <td>
                                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}
                                            </td>
                                            <th class="text-muted" width="20%"><b><i class="fa fa-flask mr-2"></i>Jenis
                                                    Sampel</b></th>
                                            <td>{{ $sample->jenisSampelDisplay() }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted"><b><i class="fa fa-barcode mr-2"></i>Nomor Sampel</b>
                                            </th>
                                            <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</td>
                                            <th class="text-muted"><b><i class="fa fa-calendar-check mr-2"></i>Tanggal
                                                    Pengiriman</b></th>
                                            <td>
                                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}
                                            </td>
                                            <th class="text-muted"><b><i class="fa fa-user-tie mr-2"></i>Nama Pengambil</b>
                                            </th>
                                            <td>{{ $sample->name_send_sample ?? optional($sample->permohonanuji)->name_sampling ?? '-' }}</td>
                                        </tr>
                                        @if (!($sample->kode_laboratorium == 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya') && isset($sample->titik_pengambilan) && !empty($sample->titik_pengambilan))
                                            <tr>
                                                <th class="text-muted"><b><i class="fa fa-map-pin mr-2"></i>Titik Sampel</b>
                                                </th>
                                                <td colspan="5">{!! $sample->titik_pengambilan !!}</td>
                                            </tr>
                                        @endif
                                        @php
                                            $selectedRuanganFromResult = null;
                                            if (isset($sample->name_sample_type) && stripos($sample->name_sample_type, 'udara') !== false && isset($laboratoriummethods)) {
                                                foreach ($laboratoriummethods as $lm) {
                                                    if (isset($lm->lokasi_selected) && !empty($lm->lokasi_selected)) {
                                                        $selectedRuanganFromResult = $lm->lokasi_selected;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if ($selectedRuanganFromResult)
                                            <tr>
                                                <th class="text-muted"><b><i class="fa fa-building mr-2"></i>Ruangan / Lokasi</b>
                                                </th>
                                                <td colspan="5"><strong>{{ $selectedRuanganFromResult }}</strong></td>
                                            </tr>
                                        @endif
                                        @if (!is_null($assignedNomerLab ?? null))
                                            @php
                                                $_labKode = strtoupper($sample->kode_laboratorium ?? '');
                                                $_labSegment = $_labKode === 'KIM' ? '01' : ($_labKode === 'MBI' ? '02' : '00');
                                                $_nomerLabFormatted = '449.5/' . $_labSegment . '/' . str_pad($assignedNomerLab, 4, '0', STR_PAD_LEFT) . '/' . ($assignedNomerLabYear ?? date('Y'));
                                            @endphp
                                            <tr>
                                                <th class="text-muted"><b><i class="fa fa-hashtag mr-2"></i>Nomor Lab</b></th>
                                                <td colspan="5">
                                                    <span class="badge badge-success" style="font-size: 14px; padding: 6px 12px; letter-spacing: 0.5px;">
                                                        {{ $_nomerLabFormatted }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="card border-0 mb-3" style="background-color: #fff3e0;">
                                <div class="card-body">
                                    <h5 class="card-title text-warning mb-3"><i class="fa fa-list-ul mr-2"></i>Parameter
                                        {{ $sample->nama_laboratorium }}</h5>
                                    <div class="row">
                                        @foreach ($laboratoriummethods as $index => $laboratoriummethod)
                                            <div class="col-md-3 mb-2">
                                                <span class="badge badge-light border"
                                                    style="font-size: 13px; padding: 8px 12px;">
                                                    <i
                                                        class="fa fa-check-circle text-success mr-1"></i>{{ $laboratoriummethod->params_method }}
                                                </span>
                                            </div>
                                            @if (($index + 1) % 4 == 0)
                                    </div>
                                    <div class="row">
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="col-md-12">
                                @php
                                    // Initialize tidak_simpan before form (will be set in loop below)
                                    $tidak_simpan = false;
                                @endphp
                                <!-- Verifikasi Pengesahan Hasil - Dipindahkan ke atas -->
                                <form
                                    action="{{ route('elits-pengesahan-hasil.store', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
                                    method="POST">
                                    @csrf
                                    @php
                                        if (isset($pengesahan_hasil->pengesahan_hasil_date)) {
                                            $pengesahan_hasil_date = \Carbon\Carbon::createFromFormat(
                                                'Y-m-d H:i:s',
                                                $pengesahan_hasil->pengesahan_hasil_date,
                                            )->isoFormat('Y/M/D');
                                        } else {
                                            $pengesahan_hasil_date = '';
                                        }
                                    @endphp
                                    @if (!$tidak_simpan)
                                        <div class="form-group" style="display: none;">
                                            <label for="wadah_samples"><b>Pengesahan Hasil dilakukan:</b></label>

                                            <div class="input-group date">
                                                <input type="text" class="form-control pengesahan_hasil"
                                                    name="pengesahan_hasil" id="pengesahan_hasil"
                                                    placeholder="Isikan Tanggal Pengesahan Hasil"
                                                    data-date-format="dd/mm/yyyy">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                        @php
                                            $_labKode2    = strtoupper($sample->kode_laboratorium ?? '');
                                            $_labSeg2     = $_labKode2 === 'KIM' ? '01' : ($_labKode2 === 'MBI' ? '02' : '00');
                                            $_nomerLabYear2 = $assignedNomerLabYear ?? date('Y');
                                            $_nextPreview  = $nextNomerLabPreview ?? 1;
                                            $_assignedFull = !is_null($assignedNomerLab ?? null)
                                                ? '449.5/' . $_labSeg2 . '/' . str_pad($assignedNomerLab, 4, '0', STR_PAD_LEFT) . '/' . $_nomerLabYear2
                                                : null;
                                            $_nextFull    = '449.5/' . $_labSeg2 . '/' . str_pad($_nextPreview, 4, '0', STR_PAD_LEFT) . '/' . date('Y');
                                        @endphp

                                        <!-- Nomor Lab Card -->
                                        @if (!is_null($_assignedFull))
                                            <div class="card border-success mb-4">
                                                <div class="card-header bg-success text-white d-flex align-items-center">
                                                    <i class="fa fa-hashtag mr-2"></i>
                                                    <strong>Nomor Lab</strong>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <label><strong>Nomor Lab yang Ditetapkan</strong></label>
                                                        <div class="input-group" style="max-width: 400px;">
                                                            <input type="text" class="form-control font-weight-bold"
                                                                   style="font-size: 18px; letter-spacing: 1px;"
                                                                   value="{{ $_assignedFull }}"
                                                                   readonly>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text bg-success text-white">
                                                                    <i class="fa fa-check"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted mt-1 d-block">
                                                            Nomor lab untuk <em>{{ $sample->name_sample_type }}</em> – Lab {{ $sample->nama_laboratorium }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif (!empty($isLastSampleForNomerLab ?? false))
                                            <div class="card border-warning mb-4">
                                                <div class="card-header bg-warning text-dark d-flex align-items-center">
                                                    <i class="fa fa-star mr-2"></i>
                                                    <strong>Nomor Lab — Sampel Terakhir</strong>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted mb-3">
                                                        Semua sampel lain ({{ ($nomerLabGroupTotal ?? 1) - 1 }} dari {{ $nomerLabGroupTotal ?? 1 }}) dengan jenis sampel
                                                        <em>{{ $sample->name_sample_type }}</em> di lab <em>{{ $sample->nama_laboratorium }}</em>
                                                        sudah selesai pengesahan. Isi nomor urut di bawah ini atau biarkan kosong untuk penomoran otomatis.
                                                    </p>
                                                    <div class="form-group mb-0">
                                                        <label for="nomer_lab_manual">
                                                            <strong>Nomor Urut Lab</strong>
                                                            <span class="text-muted font-weight-normal">(opsional — kosongkan untuk otomatis)</span>
                                                        </label>
                                                        <div class="input-group" style="max-width: 480px;">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text font-weight-bold" style="letter-spacing: 0.5px;">
                                                                    449.5/{{ $_labSeg2 }}/
                                                                </span>
                                                            </div>
                                                            <input type="number" min="1" class="form-control text-center"
                                                                   name="nomer_lab_manual"
                                                                   id="nomer_lab_manual"
                                                                   placeholder="{{ $_nextPreview }}"
                                                                   style="font-size: 16px; max-width: 100px;"
                                                                   oninput="updateNomerLabPreview(this.value)">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text font-weight-bold">
                                                                    /{{ date('Y') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted mt-1 d-block">
                                                            Nomor otomatis berikutnya: <strong id="nomer_lab_preview_text">{{ $_nextFull }}</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                                function updateNomerLabPreview(val) {
                                                    var num = parseInt(val, 10);
                                                    var padded = isNaN(num) || val === ''
                                                        ? '{{ str_pad($_nextPreview, 4, '0', STR_PAD_LEFT) }}'
                                                        : String(num).padStart(4, '0');
                                                    document.getElementById('nomer_lab_preview_text').textContent =
                                                        '449.5/{{ $_labSeg2 }}/' + padded + '/{{ date('Y') }}';
                                                }
                                            </script>
                                        @else
                                            @php
                                                $_pending = ($nomerLabGroupTotal ?? 0) - ($nomerLabGroupDone ?? 0);
                                            @endphp
                                            @if (($_pending ?? 0) > 1)
                                                <div class="card border-info mb-4">
                                                    <div class="card-body d-flex align-items-center text-info">
                                                        <i class="fa fa-info-circle fa-lg mr-3"></i>
                                                        <div>
                                                            Nomor Lab untuk <em>{{ $sample->name_sample_type }}</em> – Lab {{ $sample->nama_laboratorium }}
                                                            akan ditetapkan setelah semua {{ $nomerLabGroupTotal ?? 0 }} sampel selesai pengesahan
                                                            ({{ $nomerLabGroupDone ?? 0 }} dari {{ $nomerLabGroupTotal ?? 0 }} selesai,
                                                            masih ada {{ $_pending }} yang belum).
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Verifikasi Pengesahan Hasil -->
                                        <div class="card border-primary mb-4" style="background-color: #e3f2fd;">
                                            <div class="card-header bg-white">
                                                <h5 class="card-title mb-0 text-dark">
                                                    <i class="fa fa-check-circle mr-2"></i>Verifikasi Pengesahan Hasil
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="start_date_verifikasi_pengesahan">
                                                                <strong>Start Date <span class="text-danger">*</span></strong>
                                                            </label>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="start_date_verifikasi_pengesahan" 
                                                                   id="start_date_verifikasi_pengesahan" 
                                                                   placeholder="dd/mm/yyyy HH:mm" 
                                                                   value="{{ $default_start_date_verifikasi ? $default_start_date_verifikasi->format('d/m/Y H:i') : '' }}"
                                                                   required>
                                                            <small class="form-text text-muted">
                                                                Format: dd/mm/yyyy HH:mm (contoh: 08/01/2026 10:00)
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="stop_date_verifikasi_pengesahan">
                                                                <strong>Stop Date <span class="text-danger">*</span></strong>
                                                            </label>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="stop_date_verifikasi_pengesahan" 
                                                                   id="stop_date_verifikasi_pengesahan" 
                                                                   placeholder="dd/mm/yyyy HH:mm" 
                                                                   value="{{ $default_stop_date_verifikasi ? $default_stop_date_verifikasi->format('d/m/Y H:i') : '' }}"
                                                                   required>
                                                            <small class="form-text text-muted">
                                                                Format: dd/mm/yyyy HH:mm (contoh: 10/01/2026 14:00)
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="nama_petugas_verifikasi_pengesahan">
                                                                <strong>Nama Petugas <span class="text-danger">*</span></strong>
                                                            </label>
                                                            <select name="nama_petugas_verifikasi_pengesahan" 
                                                                    id="nama_petugas_verifikasi_pengesahan" 
                                                                    class="form-control" 
                                                                    required>
                                                                @foreach ($analis_list as $analis)
                                                                    <option value="{{ $analis }}" 
                                                                            {{ $default_analis == $analis ? 'selected' : '' }}>
                                                                        {{ $analis }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden inputs for verification data -->
                                        <input type="hidden" name="verification_step_verifikasi_pengesahan" id="verification_step_verifikasi_pengesahan" value="5">
                                        <input type="hidden" name="start_date_verifikasi_pengesahan_hidden" id="start_date_verifikasi_pengesahan_hidden">
                                        <input type="hidden" name="stop_date_verifikasi_pengesahan_hidden" id="stop_date_verifikasi_pengesahan_hidden">
                                        <input type="hidden" name="nama_petugas_verifikasi_pengesahan_hidden" id="nama_petugas_verifikasi_pengesahan_hidden">
                                        <input type="hidden" name="id_laboratorium_verifikasi_pengesahan_hidden" id="id_laboratorium_verifikasi_pengesahan_hidden" value="{{ $idlab }}">
                                    @endif

                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-hover table-striped result mb-0" width="100%">
                                            <thead class="thead-light">
                                                <tr>
                                                    <td width="5%"><br>No <br><br></td>

                                                    <td width="25%"><br>Parameter Pemeriksaan<br><br></td>
                                                    <td width="20%"><br>Hasil Pemeriksaan<br><br></td>
                                                    <td width="20%"><br>Batas Syarat<br><br></td>
                                                    <td width="30%"><br>Keterangan<br><br></td>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                @php
                                                    $no = 1;
                                                    $tidak_simpan = false;
                                                @endphp
                                                @foreach ($laboratoriummethods as $laboratoriummethod)
                                                    @php
                                                        $hasBakuMutuRow = isset($laboratoriummethod->name_report)
                                                            || !empty($laboratoriummethod->id_baku_mutu)
                                                            || !empty($laboratoriummethod->has_sample_override)
                                                            || !empty($laboratoriummethod->nilai_baku_mutu)
                                                            || (isset($laboratoriummethod->min) && $laboratoriummethod->min !== null && $laboratoriummethod->min !== '')
                                                            || (isset($laboratoriummethod->max) && $laboratoriummethod->max !== null && $laboratoriummethod->max !== '');
                                                        if ($hasBakuMutuRow && empty($laboratoriummethod->name_report)) {
                                                            $laboratoriummethod->name_report = $laboratoriummethod->params_method ?? '-';
                                                        }
                                                    @endphp
                                                    @if (count($laboratoriummethod['detail']) == 0)
                                                        @if ($hasBakuMutuRow)
                                                            <tr>
                                                                <td width="5%"><br>{{ $no }}<br><br></td>
                                                                <td width="25%"><br> <b>{!! $laboratoriummethod->name_report !!}
                                                                    </b><br><br></td>
                                                                @php

                                                                    $unit = $laboratoriummethod->shortname_unit;

                                                                    if (isset($unit)) {
                                                                        $unit = '';
                                                                        if (
                                                                            trim($laboratoriummethod->shortname_unit) !=
                                                                                '-' &&
                                                                            trim(
                                                                                cek_hasil_color(
                                                                                    isset($laboratoriummethod->hasil)
                                                                                        ? $laboratoriummethod->hasil
                                                                                        : (isset(
                                                                                            $laboratoriummethod->equal,
                                                                                        )
                                                                                            ? $laboratoriummethod->equal
                                                                                            : ''),
                                                                                    $laboratoriummethod->min,
                                                                                    $laboratoriummethod->max,
                                                                                    $laboratoriummethod->equal,
                                                                                    'result_output_method_' .
                                                                                        $laboratoriummethod->method_id,
                                                                                    $laboratoriummethod->offset_baku_mutu,
                                                                                    'en',
                                                                                    true,
                                                                                ),
                                                                            ) != '-'
                                                                        ) {
                                                                            // print_r($hasil);
                                                                            $unit = $laboratoriummethod->shortname_unit;
                                                                        }
                                                                    } else {
                                                                        $unit = '';
                                                                    }
                                                                @endphp
                                                                <td width="20%">
                                                                    @if ($laboratoriummethod->nilai_baku_mutu == $unit)
                                                                        <br>{!! cek_hasil_color(
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
                                                                            'en',
                                                                            true,
                                                                        ) !!}
                                                                    @else
                                                                        <br>{!! cek_hasil_color(
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
                                                                            'en',
                                                                            true,
                                                                        ) !!}
                                                                        {!! $unit !!}<br><br>
                                                                    @endif
                                                                </td>
                                                                <td width="20%"><br>
                                                                    @php
                                                                        // Untuk Kualitas Udara dengan lokasi_data, ambil nilai_baku_mutu dari lokasi yang dipilih
                                                                        $bakuMutuDisplay = $laboratoriummethod->nilai_baku_mutu ?? '';
                                                                        
                                                                        // Cek apakah ada lokasi_data (bisa dari lokasi_data atau baku_mutu_lokasi_data alias)
                                                                        $lokasiDataRaw = $laboratoriummethod->lokasi_data ?? $laboratoriummethod->baku_mutu_lokasi_data ?? null;
                                                                        
                                                                        if ($selectedRuanganFromResult && $lokasiDataRaw && !empty($lokasiDataRaw)) {
                                                                            $lokasiData = json_decode($lokasiDataRaw, true);
                                                                            if (is_array($lokasiData)) {
                                                                                foreach ($lokasiData as $lokasi) {
                                                                                    if (!empty($lokasi['nama']) && $lokasi['nama'] === $selectedRuanganFromResult) {
                                                                                        if (!empty($lokasi['nilai_baku_mutu'])) {
                                                                                            $bakuMutuDisplay = $lokasi['nilai_baku_mutu'];
                                                                                        }
                                                                                        break;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    @if ($bakuMutuDisplay == $unit)
                                                                        {!! $bakuMutuDisplay !!}
                                                                    @else
                                                                        {!! $bakuMutuDisplay !!} {!! $unit !!}
                                                                    @endif
                                                                    <br><br>
                                                                </td>
                                                                <td width="30%"><br>
                                                                    @php
                                                                        $keterangan = isset($laboratoriummethod->keterangan) ? $laboratoriummethod->keterangan : '';
                                                                        if (!empty($keterangan)) {
                                                                            // Convert ^() notation to HTML sup/sub tags for display using rubahNilaikeHtml
                                                                            $keterangan = rubahNilaikeHtml($keterangan);
                                                                        }
                                                                    @endphp
                                                                    @if (!empty($keterangan))
                                                                        {!! $keterangan !!}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                    <br><br>
                                                                </td>

                                                            </tr>
                                                        @else
                                                            @php
                                                                $tidak_simpan = true;
                                                            @endphp
                                                            <tr
                                                                style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                <td>{{ $no }}</td>
                                                                @php
                                                                    $jenis_makanan = $sample->jenis_makanan;
                                                                    if (isset($jenis_makanan)) {
                                                                        $jenis_makanan =
                                                                            $jenis_makanan->name_jenis_makanan;
                                                                    }
                                                                @endphp
                                                                <td colspan="5">
                                                                    Baku mutu untuk parameter
                                                                    <b>{{ $laboratoriummethod->params_method }}</b>, untuk
                                                                    jenis sarana
                                                                    <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }}</b></u>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @else
                                                        @if ($hasBakuMutuRow)
                                                            <tr>
                                                                <td style="vertical-align:top"
                                                                    rowspan="{{ count($laboratoriummethod['detail']) + 1 }}">
                                                                    {{ $no }}</td>
                                                                <td colspan="4">
                                                                    <b>{!! $laboratoriummethod->name_report !!}</b>
                                                                </td>
                                                            </tr>
                                                            @foreach ($laboratoriummethod['detail'] as $detail)
                                                                <tr>
                                                                    <td width="25%"><br>{!! $detail->name_sample_result_detail !!}<br><br>
                                                                    </td>
                                                                    @php
                                                                        $hasil = cek_hasil_color(
                                                                            isset($detail->hasil)
                                                                                ? $detail->hasil
                                                                                : (isset(
                                                                                    $detail->equal_sample_result_detail,
                                                                                )
                                                                                    ? $detail->equal_sample_result_detail
                                                                                    : ''),
                                                                            $detail->min_sample_result_detail,
                                                                            $detail->max_sample_result_detail,
                                                                            $detail->equal_sample_result_detail,
                                                                            'result_output_method_' .
                                                                                $detail->id_sample_result_detail,
                                                                            $detail->offset_baku_mutu,
                                                                            'en',
                                                                            true,
                                                                        );
                                                                        if (isset($unit)) {
                                                                            $unit = '';
                                                                            if (
                                                                                trim(
                                                                                    $laboratoriummethod->shortname_unit,
                                                                                ) != '-' &&
                                                                                trim(
                                                                                    cek_hasil_color(
                                                                                        isset($detail->hasil)
                                                                                            ? $detail->hasil
                                                                                            : (isset(
                                                                                                $detail->equal_sample_result_detail,
                                                                                            )
                                                                                                ? $detail->equal_sample_result_detail
                                                                                                : ''),
                                                                                        $detail->min_sample_result_detail,
                                                                                        $detail->max_sample_result_detail,
                                                                                        $detail->equal_sample_result_detail,
                                                                                        'result_output_method_' .
                                                                                            $detail->id_sample_result_detail,
                                                                                        $detail->offset_baku_mutu,
                                                                                        'en',
                                                                                        true,
                                                                                    ),
                                                                                ) != '-'
                                                                            ) {
                                                                                // print_r($hasil);
                                                                                $unit =
                                                                                    $laboratoriummethod->shortname_unit;
                                                                            }
                                                                        } else {
                                                                            $unit = '';
                                                                        }
                                                                    @endphp
                                                                    <td width="20%">
                                                                        <br>{!! cek_hasil_color(
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
                                                                            'en',
                                                                            true,
                                                                        ) !!}{!! $unit !!}<br><br>
                                                                    </td>
                                                                    <td width="20%"><br> {!! $detail->nilai_sample_result_detail !!}
                                                                        {!! $unit !!}<br><br></td>
                                                                    <td width="30%"><br>
                                                                        @php
                                                                            $keterangan = isset($detail->keterangan) ? $detail->keterangan : '';
                                                                            if (!empty($keterangan)) {
                                                                                // Convert ^() notation to HTML sup/sub tags for display using rubahNilaikeHtml
                                                                                $keterangan = rubahNilaikeHtml($keterangan);
                                                                            }
                                                                        @endphp
                                                                        @if (!empty($keterangan))
                                                                            {!! $keterangan !!}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                        <br><br>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            @php
                                                                $tidak_simpan = true;
                                                            @endphp
                                                            <tr
                                                                style="background-color: rgb(240, 19, 19); color: #fff; text-align: center">
                                                                <td>{{ $no }}</td>
                                                                @php
                                                                    $jenis_makanan = $sample->jenis_makanan;
                                                                    if (isset($jenis_makanan)) {
                                                                        $jenis_makanan =
                                                                            $jenis_makanan->name_jenis_makanan;
                                                                    }
                                                                @endphp
                                                                <td colspan="5">
                                                                    Baku mutu untuk parameter
                                                                    <b>{{ $laboratoriummethod->params_method }}</b>, untuk
                                                                    jenis sarana
                                                                    <u><b>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }}</b></u>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                    @php
                                                        $no++;
                                                    @endphp
                                                @endforeach


                                                </thead>
                                        </table>
                                        <br>
                                        <!-- Simulasi PDF sebelum tombol simpan -->
                                        <div class="card border-0 mb-3" style="background-color:#e3f2fd;">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary mb-3">
                                                    <i class="fa fa-file-pdf-o mr-2"></i>Simulasi PDF (Pratinjau)
                                                </h5>
                                                @php
                                                    $isKimia =
                                                        $sample->nama_laboratorium == 'Kimia' ||
                                                        $sample->kode_laboratorium == 'KIM' ||
                                                        $sample->kode_laboratorium == 'KIMIA';
                                                    
                                                    // Cek apakah jenis sample adalah "Makanan/Minuman/Lainnya"
                                                    $isMakananMinumanLainnya = isset($sample->name_sample_type) && 
                                                        $sample->name_sample_type === 'Makanan/Minuman/Lainnya';

                                                    // URL kimia:
                                                    // - Makanan/Minuman/Lainnya => print-kimia (format tabel makmin)
                                                    // - selain itu => printLHU per sampel
                                                    if ($isMakananMinumanLainnya) {
                                                        $kimiaUrl = url(
                                                            'elits-release/print-kimia/' .
                                                                $sample->permohonan_uji_id .
                                                                '/' .
                                                                $sample->typesample_samples .
                                                                '?agenda=&signOption=0',
                                                        );
                                                    } else {
                                                        $kimiaUrl = url(
                                                            'elits-release/printLHU/' .
                                                                $sample->id_samples .
                                                                '/' .
                                                                $sample->id_laboratorium .
                                                                '?agenda=&signOption=0',
                                                        );
                                                    }

                                                    // URL mikro selalu gunakan print-mikro (sesuai lab MBI)
                                                    $mikroBase = url(
                                                        'elits-release/print-mikro/' .
                                                            $sample->permohonan_uji_id .
                                                            '/' .
                                                            $sample->typesample_samples,
                                                    );
                                                    $mikroQuery = '?agenda=&signOption=0&printall=on';

                                                    if (!empty($sample->jenis_makanan_id)) {
                                                        $mikroQuery .= '&jenis_makanan_id=' . $sample->jenis_makanan_id;
                                                    }

                                                    try {
                                                        $mikroSampleIds = \Smt\Masterweb\Models\Sample::query()
                                                            ->where('tb_samples.permohonan_uji_id', $sample->permohonan_uji_id)
                                                            ->join('tb_sample_method', function ($join) {
                                                                $join
                                                                    ->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                                                                    ->whereNull('tb_sample_method.deleted_at');
                                                            })
                                                            ->join('ms_laboratorium', function ($join) {
                                                                $join
                                                                    ->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                                                                    ->whereNull('ms_laboratorium.deleted_at');
                                                            })
                                                            ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                                                            ->pluck('tb_samples.id_samples')
                                                            ->unique()
                                                            ->toArray();
                                                        foreach ($mikroSampleIds as $sid) {
                                                            $mikroQuery .= '&printSamples[]=' . $sid;
                                                        }
                                                    } catch (\Throwable $e) {
                                                        $mikroQuery .= '&printSamples[]=' . $sample->id_samples;
                                                    }

                                                    $mikroUrl = $mikroBase . $mikroQuery;

                                                    // WAJIB: preview mengikuti lab aktif pada halaman ini
                                                    $previewUrl = $isKimia ? $kimiaUrl : $mikroUrl;
                                                @endphp
                                                <div class="alert alert-info mb-3">
                                                    <i class="fa fa-info-circle mr-1"></i>
                                                    Tautan sumber PDF:
                                                    @if ($isKimia)
                                                        <a href="{{ $kimiaUrl }}" target="_blank">Link</a>
                                                    @else
                                                        <a href="{{ $mikroUrl }}" target="_blank">Link</a>
                                                    @endif
                                                </div>
                                                <div class="pdf-preview-container" id="pdfPreviewContainer">
                                                    <iframe id="pdfIframe" src="{{ $previewUrl }}" frameborder="0"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tombol Simpan dan Kembali setelah PDF Preview -->
                                        <div class="mt-4 mb-3">
                                            <button type="submit" id="submitAll" class="btn btn-primary mr-2">
                                                <i class="fa fa-save mr-2"></i>Simpan / Disahkan
                                            </button>
                                            <button type="button" class="btn btn-light" onclick="window.history.back()">
                                                <i class="fa fa-arrow-left mr-2"></i>Kembali
                                            </button>
                                        </div>
                                    </form>




                                    </div>
                                </div>


                            </div>


                        </div>






                    </div>

                    <!-- utama -->
                </div>
                <!-- /.row -->
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/flatpickr.min.css') }}">
    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/cdn-local/js/flatpickr.min.js') }}"></script>
    <script>
        $('.pengesahan_hasil').datepicker({
            format: 'dd/mm/yyyy'
        });
        var tanggal
        if ("{{ $pengesahan_hasil }}" != undefined && "{{ $pengesahan_hasil }}" != "") {
            tanggal = new Date("{{ $pengesahan_hasil }}")
        } else {

            tanggal = new Date()
        }
        $('.pengesahan_hasil').datepicker('update', tanggal);

        // Flatpickr untuk Verifikasi Pengesahan Hasil
        $(document).ready(function() {
            // Inisialisasi Flatpickr untuk Start Date
            if ($('#start_date_verifikasi_pengesahan').length) {
                flatpickr('#start_date_verifikasi_pengesahan', {
                    dateFormat: 'd/m/Y H:i',
                    enableTime: true,
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    defaultHour: 8,
                    defaultMinute: 0,
                    minTime: '08:00',
                    maxTime: '17:00',
                    onChange: function(selectedDates, dateStr, instance) {
                        // Auto-adjust to work hours
                        if (selectedDates.length > 0) {
                            var selectedDate = selectedDates[0];
                            var hours = selectedDate.getHours();
                            if (hours < 8) {
                                instance.setDate(new Date(selectedDate.setHours(8, 0, 0, 0)), false);
                            } else if (hours > 17) {
                                instance.setDate(new Date(selectedDate.setHours(17, 0, 0, 0)), false);
                            }
                        }
                    }
                });
            }

            // Inisialisasi Flatpickr untuk Stop Date
            if ($('#stop_date_verifikasi_pengesahan').length) {
                flatpickr('#stop_date_verifikasi_pengesahan', {
                    dateFormat: 'd/m/Y H:i',
                    enableTime: true,
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    defaultHour: 17,
                    defaultMinute: 0,
                    minTime: '08:00',
                    maxTime: '17:00',
                    onChange: function(selectedDates, dateStr, instance) {
                        // Auto-adjust to work hours
                        if (selectedDates.length > 0) {
                            var selectedDate = selectedDates[0];
                            var hours = selectedDate.getHours();
                            if (hours < 8) {
                                instance.setDate(new Date(selectedDate.setHours(8, 0, 0, 0)), false);
                            } else if (hours > 17) {
                                instance.setDate(new Date(selectedDate.setHours(17, 0, 0, 0)), false);
                            }
                        }
                    }
                });
            }

            // Handler untuk form submit
            $('form').on('submit', function(e) {
                // Validasi Verifikasi Pengesahan Hasil fields - hanya jika field ada dan terlihat
                var $verifikasiStartDate = $('#start_date_verifikasi_pengesahan');
                var $verifikasiCard = $verifikasiStartDate.closest('.card');
                var isVerifikasiVisible = $verifikasiStartDate.length > 0 && 
                                         $verifikasiCard.is(':visible') && 
                                         $verifikasiStartDate.is(':visible');
                
                if (isVerifikasiVisible) {
                    var verifikasiStartDate = $('#start_date_verifikasi_pengesahan').val();
                    var verifikasiStopDate = $('#stop_date_verifikasi_pengesahan').val();
                    var verifikasiNamaPetugas = $('#nama_petugas_verifikasi_pengesahan').val();

                    if (!verifikasiStartDate || !verifikasiStopDate || !verifikasiNamaPetugas) {
                        alert('Form Verifikasi Pengesahan Hasil harus diisi lengkap sebelum menyimpan.');
                        e.preventDefault();
                        return false;
                    }

                    // Populate hidden inputs for verification
                    if ($('#verification_step_verifikasi_pengesahan').length > 0) {
                        $('#verification_step_verifikasi_pengesahan').val('5');
                    }
                    if ($('#nama_petugas_verifikasi_pengesahan_hidden').length > 0) {
                        $('#nama_petugas_verifikasi_pengesahan_hidden').val(verifikasiNamaPetugas);
                    }

                    // Convert dates using Flatpickr formatDate
                    var startDateFP = $('#start_date_verifikasi_pengesahan')[0]._flatpickr;
                    var stopDateFP = $('#stop_date_verifikasi_pengesahan')[0]._flatpickr;

                    if (startDateFP && startDateFP.selectedDates && startDateFP.selectedDates.length > 0) {
                        var startDateFormatted = startDateFP.formatDate(startDateFP.selectedDates[0], 'd/m/Y H:i');
                        if ($('#start_date_verifikasi_pengesahan_hidden').length > 0) {
                            $('#start_date_verifikasi_pengesahan_hidden').val(startDateFormatted);
                        }
                    } else if ($('#start_date_verifikasi_pengesahan_hidden').length > 0) {
                        $('#start_date_verifikasi_pengesahan_hidden').val(verifikasiStartDate);
                    }

                    if (stopDateFP && stopDateFP.selectedDates && stopDateFP.selectedDates.length > 0) {
                        var stopDateFormatted = stopDateFP.formatDate(stopDateFP.selectedDates[0], 'd/m/Y H:i');
                        if ($('#stop_date_verifikasi_pengesahan_hidden').length > 0) {
                            $('#stop_date_verifikasi_pengesahan_hidden').val(stopDateFormatted);
                        }
                    } else if ($('#stop_date_verifikasi_pengesahan_hidden').length > 0) {
                        $('#stop_date_verifikasi_pengesahan_hidden').val(verifikasiStopDate);
                    }
                }
            });
        });

    </script>
@endsection
