@extends('masterweb::template.admin.layout')

@section('title')
    Dashboard Dokter - Analisis Hasil Klinik
@endsection

@section('css')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <!-- Leaflet Search CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/stefanocudini/leaflet-search@2.9.8/dist/leaflet-search.min.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
            border-radius: 8px;
            margin-top: 20px;
        }

        .simlab-cluster-icon {
            background: transparent !important;
            border: none !important;
        }

        .simlab-cluster-bubble {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ef6c00;
            border: 4px solid #c62828;
            box-shadow: 0 0 0 3px #fff, 0 2px 8px rgba(0, 0, 0, 0.25);
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .simlab-cluster-bubble--lg {
            width: 44px;
            height: 44px;
            font-size: 13px;
            border-width: 5px;
        }

        .filter-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .chart-container h4 {
            margin-bottom: 15px;
        }

        #scatterChart {
            max-height: 450px !important;
            height: 450px !important;
            width: 100% !important;
        }

        .parameter-list {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .parameter-item {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .parameter-item:last-child {
            border-bottom: none;
        }

        .parameter-item.hidden {
            display: none;
        }

        .param-stats-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(11, 58, 92, 0.08);
            border: 1px solid #e4eeec;
            margin-top: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .param-stats-card__head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #f3f8f7 0%, #eef5f8 100%);
            border-bottom: 1px solid #dfecea;
        }

        .param-stats-card__head h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: #06283f;
        }

        .param-stats-card__head p {
            margin: 4px 0 0;
            font-size: 12.5px;
            color: #5c6d75;
        }

        .param-stats-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .param-stats-search {
            border: 1px solid #c9dbd7;
            border-radius: 8px;
            padding: 8px 12px;
            min-width: 220px;
            font-size: 13px;
            color: #06283f;
        }

        .param-stats-search:focus {
            outline: none;
            border-color: #0d8f7f;
            box-shadow: 0 0 0 3px rgba(13, 143, 127, 0.12);
        }

        .param-stats-hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 700;
            color: #0b3a5c;
            background: #fff;
            border: 1px solid #d5e8e4;
            border-radius: 999px;
            padding: 6px 10px;
        }

        .param-stats-table-wrap {
            max-height: 480px;
            overflow: auto;
        }

        .param-stats-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .param-stats-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #0b3a5c;
            color: #fff;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            padding: 12px 14px;
            white-space: nowrap;
            border: none;
        }

        .param-stats-table tbody td {
            padding: 12px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #eef3f2;
            color: #24353f;
            background: #fff;
        }

        .param-stats-table tbody tr:hover td {
            background: #f5fbfa;
        }

        .param-stats-table tbody tr.is-high td {
            background: #fff7f5;
        }

        .param-stats-table tbody tr.is-high:hover td {
            background: #ffefeb;
        }

        .param-stats-table tbody tr.is-mid td {
            background: #fffaf0;
        }

        .param-name {
            font-weight: 750;
            color: #06283f;
            line-height: 1.35;
        }

        .param-rank {
            display: inline-flex;
            width: 28px;
            height: 28px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e7f4f2;
            color: #0b3a5c;
            font-weight: 800;
            font-size: 12px;
        }

        .param-num {
            font-variant-numeric: tabular-nums;
            font-weight: 650;
            text-align: right;
            white-space: nowrap;
        }

        .param-abnormal-cell {
            min-width: 160px;
        }

        .param-abnormal-meta {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .param-abnormal-meta strong {
            color: #c62828;
            font-size: 13px;
        }

        .param-abnormal-meta span {
            color: #5c6d75;
            font-weight: 600;
        }

        .param-bar {
            height: 8px;
            border-radius: 999px;
            background: #e8efed;
            overflow: hidden;
        }

        .param-bar > i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #16a892, #ef6c00);
        }

        .param-bar > i.is-ok {
            background: #16a892;
        }

        .param-bar > i.is-warn {
            background: linear-gradient(90deg, #f9a825, #ef6c00);
        }

        .param-bar > i.is-danger {
            background: linear-gradient(90deg, #ef6c00, #c62828);
        }

        .param-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 750;
            white-space: nowrap;
        }

        .param-pill--ok {
            background: #e7f4f2;
            color: #0a7a6c;
        }

        .param-pill--warn {
            background: #fff3e0;
            color: #e65100;
        }

        .param-pill--danger {
            background: #ffebee;
            color: #c62828;
        }

        .param-stats-empty {
            padding: 24px;
            text-align: center;
            color: #5c6d75;
            display: none;
        }

        @media (max-width: 767px) {
            .param-stats-search { min-width: 100%; width: 100%; }
            .param-stats-table thead th,
            .param-stats-table tbody td { padding: 10px; }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h3 class="page-title">
                    <i class="mdi mdi-chart-line"></i> Dashboard Analisis Hasil Klinik per Wilayah
                </h3>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="row">
        <div class="col-12">
            <div class="filter-panel">
                <form method="GET" action="{{ route('klinik.analisis-hasil-wilayah') }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tipe_wilayah">Tipe Wilayah:</label>
                            <select name="tipe_wilayah" id="tipe_wilayah" class="form-control smt-select2"
                                onchange="updateWilayahOptions()">
                                <option value="KEC" {{ $tipeWilayah == 'KEC' ? 'selected' : '' }}>Kecamatan</option>
                                <option value="DESA" {{ $tipeWilayah == 'DESA' ? 'selected' : '' }}>Desa/Kelurahan
                                </option>
                                <option value="DUSUN" {{ $tipeWilayah == 'DUSUN' ? 'selected' : '' }}>Dusun</option>
                                <option value="luar_daerah" {{ $tipeWilayah == 'luar_daerah' ? 'selected' : '' }}>Luar
                                    Daerah SIMLAB</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="wilayah_id">Pilih Wilayah:</label>
                            <select name="wilayah_id" id="wilayah_id" class="form-control smt-select2">
                                <option value="">-- Semua Wilayah --</option>
                                @foreach ($wilayahOptions as $wilayah)
                                    <option value="{{ $wilayah->id_wilayah }}"
                                        {{ $wilayahId == $wilayah->id_wilayah ? 'selected' : '' }}>
                                        {{ $wilayah->wilayah }} ({{ $wilayah->wilayah_kode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="bulan">Bulan:</label>
                            <select name="bulan" id="bulan" class="form-control">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                        {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tahun">Tahun:</label>
                            <select name="tahun" id="tahun" class="form-control">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="gender">Jenis Kelamin:</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="" {{ empty($filterGender) ? 'selected' : '' }}>Semua</option>
                                <option value="L" {{ ($filterGender ?? null) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ ($filterGender ?? null) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="umur_min">Usia dari (th):</label>
                            <input type="number" name="umur_min" id="umur_min" class="form-control" min="0" max="150"
                                placeholder="Min" value="{{ $filterUmurMin !== null ? $filterUmurMin : '' }}">
                        </div>
                        <div class="col-md-2">
                            <label for="umur_max">Usia sampai (th):</label>
                            <input type="number" name="umur_max" id="umur_max" class="form-control" min="0" max="150"
                                placeholder="Max" value="{{ $filterUmurMax !== null ? $filterUmurMax : '' }}">
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <small class="text-muted mb-2">
                                Filter diterapkan otomatis. Usia dihitung saat tanggal pengujian; kosongkan untuk semua umur.
                            </small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Pemeriksaan:</label>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                    data-target="#modalPemeriksaan">
                                    <i class="mdi mdi-checkbox-multiple-marked"></i> Pilih Pemeriksaan
                                </button>
                                <span class="ml-2" id="selectedPemeriksaanText">
                                    @if (!empty($parameterIds))
                                        {{ count($parameterIds) }} pemeriksaan dipilih
                                    @else
                                        Semua pemeriksaan
                                    @endif
                                </span>
                            </div>
                            <input type="hidden" name="tipe_parameter" id="tipe_parameter" value="{{ $tipeParameter }}">
                            <input type="hidden" name="parameter_ids" id="parameter_ids"
                                value="{{ !empty($parameterIds) ? implode(',', $parameterIds) : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label>Tampilan:</label>
                            <div class="form-group mb-0">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="view_type" id="view_both"
                                        value="both" {{ $viewType == 'both' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="view_both">
                                        Map & Scatter Plot
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="view_type" id="view_map"
                                        value="map" {{ $viewType == 'map' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="view_map">
                                        Map Saja
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="view_type" id="view_scatter"
                                        value="scatter" {{ $viewType == 'scatter' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="view_scatter">
                                        Scatter Plot Saja
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pemeriksaan -->
    <div class="modal fade" id="modalPemeriksaan" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Pemeriksaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $tipeParameter == 'satuan' ? 'active' : '' }}" data-toggle="tab"
                                href="#tabSatuan" role="tab" onclick="setTipeParameter('satuan')">Parameter
                                Satuan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tipeParameter == 'paket' ? 'active' : '' }}" data-toggle="tab"
                                href="#tabPaket" role="tab" onclick="setTipeParameter('paket')">Parameter Paket</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane {{ $tipeParameter == 'satuan' ? 'active' : '' }}" id="tabSatuan"
                            role="tabpanel">
                            <div class="form-group d-flex">
                                <input type="text" class="form-control" id="searchSatuan"
                                    placeholder="Cari parameter satuan..." onkeyup="filterParameter('satuan')">
                                <button type="button" class="btn btn-sm btn-secondary ml-2"
                                    onclick="selectAllParameter('satuan')">Pilih Semua</button>
                                <button type="button" class="btn btn-sm btn-secondary ml-1"
                                    onclick="deselectAllParameter('satuan')">Hapus Semua</button>
                            </div>
                            <div class="parameter-list" id="listSatuan" style="max-height: 400px; overflow-y: auto;">
                                @foreach ($parameterSatuans as $param)
                                    <div class="form-check parameter-item"
                                        data-name="{{ strtolower($param->name_parameter_satuan_klinik) }}">
                                        <input class="form-check-input parameter-checkbox parameter-satuan"
                                            type="checkbox" value="{{ $param->id_parameter_satuan_klinik }}"
                                            id="satuan_{{ $param->id_parameter_satuan_klinik }}"
                                            {{ in_array($param->id_parameter_satuan_klinik, $parameterIds) && $tipeParameter == 'satuan' ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="satuan_{{ $param->id_parameter_satuan_klinik }}">
                                            {{ $param->name_parameter_satuan_klinik }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane {{ $tipeParameter == 'paket' ? 'active' : '' }}" id="tabPaket"
                            role="tabpanel">
                            <div class="form-group d-flex">
                                <input type="text" class="form-control" id="searchPaket"
                                    placeholder="Cari parameter paket..." onkeyup="filterParameter('paket')">
                                <button type="button" class="btn btn-sm btn-secondary ml-2"
                                    onclick="selectAllParameter('paket')">Pilih Semua</button>
                                <button type="button" class="btn btn-sm btn-secondary ml-1"
                                    onclick="deselectAllParameter('paket')">Hapus Semua</button>
                            </div>
                            <div class="parameter-list" id="listPaket" style="max-height: 400px; overflow-y: auto;">
                                @foreach ($parameterPakets as $param)
                                    <div class="form-check parameter-item"
                                        data-name="{{ strtolower($param->name_parameter_paket_klinik) }}">
                                        <input class="form-check-input parameter-checkbox parameter-paket" type="checkbox"
                                            value="{{ $param->id_parameter_paket_klinik }}"
                                            id="paket_{{ $param->id_parameter_paket_klinik }}"
                                            {{ in_array($param->id_parameter_paket_klinik, $parameterIds) && $tipeParameter == 'paket' ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="paket_{{ $param->id_parameter_paket_klinik }}">
                                            {{ $param->name_parameter_paket_klinik }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="applyPemeriksaanFilter()">Terapkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <h3>Total Pengujian</h3>
                <div class="value">{{ number_format($statistics['total_results'] ?? 0) }}</div>
                <small>parameter</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Total Melewati</h3>
                <div class="value">{{ number_format($statistics['abnormal_count'] ?? 0) }}</div>
                <small>parameter</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Total Tidak Melewati</h3>
                <div class="value">{{ number_format($statistics['normal_count'] ?? 0) }}</div>
                <small>parameter</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <h3>Abnormal</h3>
                <div class="value">{{ number_format($statistics['abnormal_percentage'] ?? 0, 2) }}%</div>
            </div>
        </div>
    </div>

    <!-- Statistics per Parameter -->
    @if (isset($statistics['parameter_stats']) && count($statistics['parameter_stats']) > 0)
        @php
            $paramStatsList = $statistics['parameter_stats'];
            $paramStatsAbnormal = collect($paramStatsList)->where('abnormal_count', '>', 0)->count();
        @endphp
        <div class="row">
            <div class="col-12">
                <div class="param-stats-card">
                    <div class="param-stats-card__head">
                        <div>
                            <h4>Statistik per Parameter</h4>
                            <p>
                                Diurutkan dari jumlah kasus melewati baku mutu terbanyak.
                                {{ number_format(count($paramStatsList)) }} parameter ·
                                {{ number_format($paramStatsAbnormal) }} punya hasil di luar baku mutu.
                            </p>
                        </div>
                        <div class="param-stats-tools">
                            <span class="param-stats-hint"><i class="fas fa-sort-amount-down"></i> Prioritas kasus abnormal</span>
                            <input type="search" id="paramStatsSearch" class="param-stats-search"
                                placeholder="Cari nama parameter..." autocomplete="off">
                        </div>
                    </div>
                    <div class="param-stats-table-wrap">
                        <table class="param-stats-table" id="paramStatsTable">
                            <thead>
                                <tr>
                                    <th style="width:52px;">No</th>
                                    <th>Parameter</th>
                                    <th class="text-right">Di bawah baku mutu</th>
                                    <th class="text-right">Di atas baku mutu</th>
                                    <th class="text-right">Total melewati</th>
                                    <th class="text-right">Tidak melewati</th>
                                    <th class="text-right">Total pengujian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paramStatsList as $index => $param)
                                    @php
                                        $below = (int) ($param['below_count'] ?? 0);
                                        $above = (int) ($param['above_count'] ?? 0);
                                        $abn = (int) ($param['abnormal_count'] ?? 0);
                                        $normal = (int) ($param['normal_count'] ?? 0);
                                        $total = (int) ($param['total_results'] ?? 0);
                                        $pct = (float) ($param['abnormal_percentage'] ?? 0);
                                        $rowClass = $pct >= 30 ? 'is-high' : ($pct > 0 ? 'is-mid' : '');
                                    @endphp
                                    <tr class="{{ $rowClass }}" data-name="{{ strtolower($param['parameter_name']) }}">
                                        <td><span class="param-rank">{{ $index + 1 }}</span></td>
                                        <td>
                                            <div class="param-name">{{ $param['parameter_name'] }}</div>
                                            <div style="font-size:11px;color:#5c6d75;margin-top:2px;">
                                                {{ number_format($pct, 1) }}% melewati baku mutu
                                            </div>
                                        </td>
                                        <td class="param-num">
                                            <span class="param-pill {{ $below > 0 ? 'param-pill--warn' : 'param-pill--ok' }}">
                                                {{ number_format($below) }} kasus
                                            </span>
                                        </td>
                                        <td class="param-num">
                                            <span class="param-pill {{ $above > 0 ? 'param-pill--danger' : 'param-pill--ok' }}">
                                                {{ number_format($above) }} kasus
                                            </span>
                                        </td>
                                        <td class="param-num">
                                            <strong style="color:{{ $abn > 0 ? '#c62828' : '#0a7a6c' }};">
                                                {{ number_format($abn) }} kasus
                                            </strong>
                                        </td>
                                        <td class="param-num">
                                            <span class="param-pill param-pill--ok">
                                                {{ number_format($normal) }} kasus
                                            </span>
                                        </td>
                                        <td class="param-num"><strong>{{ number_format($total) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="param-stats-empty" id="paramStatsEmpty">Tidak ada parameter yang cocok dengan pencarian.</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Map -->
    <div class="row" id="mapSection" style="display: {{ in_array($viewType, ['both', 'map']) ? 'block' : 'none' }};">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Peta Wilayah Melewati Baku Mutu</h4>
                    <p class="text-muted mb-2" style="font-size: 13px;">
                        Titik memakai centroid resmi kecamatan/desa Magelang (bukan koordinat acak).
                        Pemutusan baku mutu sama seperti statistik. Titik berhimpit digabung; zoom out mengelompokkan titik berdekatan.
                    </p>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scatter Plot Chart -->
    <div class="row" id="scatterSection"
        style="display: {{ in_array($viewType, ['both', 'scatter']) ? 'block' : 'none' }};">
        <div class="col-12">
            <div class="chart-container">
                <h4>Grafik Scatter Plot - Jumlah Melewati Baku Mutu per Pemeriksaan</h4>
                <p class="text-muted mb-2" style="font-size: 13px;">
                    Jumlah melewati baku mutu memakai aturan yang sama dengan statistik (baku mutu tersimpan pada hasil, fallback umur/haji).
                </p>
                <canvas id="scatterChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet MarkerCluster -->
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <!-- Leaflet Search JS - Using GitHub CDN -->
    <script src="https://cdn.jsdelivr.net/gh/stefanocudini/leaflet-search@2.9.8/dist/leaflet-search.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        var viewType = '{{ $viewType }}';
        var map = null;
        var scatterChart = null;
        var searchLayer = null;
        var searchControl = null;

        // Function to initialize map
        function initializeMap() {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([-7.4706, 110.2178], 11);

            // CARTO basemaps sekarang wajib API key; tile OSM.org ditolak jika Referer kosong.
            // Esri World Street Map: gratis, tanpa API key, tanpa syarat Referer.
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; <a target="_blank" rel="noopener" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &amp; Esri',
                maxZoom: 19
            }).addTo(map);

            // Cluster layer: titik berdekatan digabung
            searchLayer = L.markerClusterGroup({
                maxClusterRadius: 45,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                disableClusteringAtZoom: 16,
                iconCreateFunction: function(cluster) {
                    var childCount = cluster.getChildCount();
                    var sizeClass = childCount > 8 ? ' simlab-cluster-bubble--lg' : '';
                    return L.divIcon({
                        html: '<div class="simlab-cluster-bubble' + sizeClass + '">' + childCount + '</div>',
                        className: 'simlab-cluster-icon',
                        iconSize: L.point(childCount > 8 ? 44 : 36, childCount > 8 ? 44 : 36)
                    });
                }
            });
            map.addLayer(searchLayer);

            // Map data: hanya wilayah dengan pelanggaran baku mutu
            var mapData = @json($mapData);
            var maxAbnormal = 0;
            mapData.forEach(function(d) {
                if ((d.abnormal_count || 0) > maxAbnormal) maxAbnormal = d.abnormal_count;
            });

            function markerStyleFor(data) {
                var count = data.abnormal_count || 0;
                var ratio = maxAbnormal > 0 ? (count / maxAbnormal) : 0;
                var radius = Math.max(8, Math.min(22, 8 + Math.round(ratio * 14)));
                var color = count >= Math.max(1, maxAbnormal * 0.66)
                    ? '#c62828'
                    : (count >= Math.max(1, maxAbnormal * 0.33) ? '#ef6c00' : '#f9a825');
                return { radius: radius, color: color };
            }

            function buildPopupHtml(data) {
                var rows = '';
                (data.top_parameters || []).forEach(function(item, idx) {
                    rows += '<tr>' +
                        '<td style="padding:2px 8px 2px 0;">' + (idx + 1) + '.</td>' +
                        '<td style="padding:2px 8px 2px 0;"><strong>' + item.parameter + '</strong></td>' +
                        '<td style="padding:2px 0; text-align:right;">' + item.abnormal_count + '</td>' +
                        '</tr>';
                });
                if (!rows) {
                    rows = '<tr><td colspan="3">Tidak ada detail parameter</td></tr>';
                }

                var wilayahBlock = '';
                if (data.is_merged && data.wilayah_list && data.wilayah_list.length) {
                    wilayahBlock = '<div style="margin-top:8px;"><strong>Wilayah dalam titik ini:</strong><ul style="margin:4px 0 0;padding-left:18px;font-size:12px;">';
                    data.wilayah_list.forEach(function(w) {
                        wilayahBlock += '<li>' + w.nama + ' <span style="color:#888;">(' + w.abnormal_count + ')</span></li>';
                    });
                    wilayahBlock += '</ul></div>';
                }

                var subtitle = data.is_merged
                    ? (data.wilayah_list.length + ' wilayah pada titik yang sama')
                    : ('Kode: ' + data.kode + ' · ' + data.tipe);

                return '' +
                    '<div style="min-width:220px;">' +
                    '<strong style="font-size:14px;">' + data.nama + '</strong><br>' +
                    '<span style="color:#666;font-size:12px;">' + subtitle + '</span><br><br>' +
                    '<div><strong>Di bawah baku mutu:</strong> ' + (data.below_count || 0) + '</div>' +
                    '<div><strong>Di atas baku mutu:</strong> ' + (data.above_count || 0) + '</div>' +
                    '<div><strong>Total melewati baku mutu:</strong> ' + (data.abnormal_count || 0) + ' parameter</div>' +
                    '<div><strong>Tidak melewati:</strong> ' + (data.normal_count || 0) + '</div>' +
                    '<div><strong>Total pengujian:</strong> ' + (data.total_results || 0) + ' parameter</div>' +
                    '<div><strong>Persentase parameter abnormal:</strong> ' + (data.abnormal_percentage || 0) + '%</div>' +
                    '<div><strong>Total Pasien:</strong> ' + (data.total_samples || 0) + ' pasien</div>' +
                    wilayahBlock +
                    '<div style="margin-top:8px;"><strong>Parameter paling sering melewati:</strong></div>' +
                    '<table style="width:100%;font-size:12px;margin-top:4px;">' + rows + '</table>' +
                    '</div>';
            }

            // Add markers to cluster layer
            mapData.forEach(function(data) {
                var style = markerStyleFor(data);
                var marker = L.circleMarker([data.lat, data.lng], {
                    radius: style.radius,
                    fillColor: style.color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                });

                marker.options.title = data.nama;
                marker.options.loc = [data.lat, data.lng];
                marker.options.abnormal_count = data.abnormal_count || 0;
                marker.bindPopup(buildPopupHtml(data));
                searchLayer.addLayer(marker);
            });

            if (mapData.length > 0 && searchLayer.getLayers().length > 0) {
                try {
                    map.fitBounds(searchLayer.getBounds().pad(0.12), { maxZoom: 13 });
                } catch (e) {
                    map.setView([-7.4797, 110.2177], 11);
                }
            }

            // Initialize search control after markers are added
            initializeMapSearch();

            // Trigger resize after a short delay to ensure map renders correctly
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }

        // Function to initialize search control
        function initializeMapSearch() {
            // Remove existing search control if any
            if (searchControl && map) {
                map.removeControl(searchControl);
                searchControl = null;
            }

            // Check if Leaflet Search plugin is loaded
            if (typeof L === 'undefined' || typeof L.Control === 'undefined' || typeof L.Control.Search === 'undefined') {
                console.warn('Leaflet Search plugin not loaded. Retrying in 500ms...');
                // Retry after a short delay
                setTimeout(function() {
                    initializeMapSearch();
                }, 500);
                return;
            }

            // Add search control
            try {
                searchControl = new L.Control.Search({
                    layer: searchLayer,
                    propertyName: 'title',
                    propertyLoc: 'loc',
                    initial: false,
                    zoom: 13,
                    textPlaceholder: 'Cari wilayah...',
                    textCancel: 'Batal',
                    textErr: 'Wilayah tidak ditemukan',
                    marker: {
                        icon: false,
                        animate: true,
                        circle: {
                            radius: 15,
                            weight: 3,
                            color: '#ff0000',
                            fillColor: '#ff0000',
                            fillOpacity: 0.2
                        }
                    },
                    moveToLocation: function(latlng, title, map) {
                        map.setView(latlng, 13);
                    }
                });

                searchControl.on('search:locationfound', function(e) {
                    // Highlight the found marker
                    if (e.layer && e.layer.setStyle) {
                        e.layer.setStyle({
                            fillColor: '#ff0000',
                            color: '#fff',
                            weight: 3,
                            radius: 12
                        });

                        // Reset style after 3 seconds
                        setTimeout(function() {
                            var mapData = @json($mapData);
                            var foundData = mapData.find(function(d) {
                                return d.nama === e.layer.options.title;
                            });
                            if (foundData) {
                                var maxAbnormal = 0;
                                mapData.forEach(function(d) {
                                    if ((d.abnormal_count || 0) > maxAbnormal) maxAbnormal = d.abnormal_count;
                                });
                                var count = foundData.abnormal_count || 0;
                                var ratio = maxAbnormal > 0 ? (count / maxAbnormal) : 0;
                                var radius = Math.max(8, Math.min(22, 8 + Math.round(ratio * 14)));
                                var color = count >= Math.max(1, maxAbnormal * 0.66)
                                    ? '#c62828'
                                    : (count >= Math.max(1, maxAbnormal * 0.33) ? '#ef6c00' : '#f9a825');
                                e.layer.setStyle({
                                    fillColor: color,
                                    color: '#fff',
                                    weight: 2,
                                    radius: radius
                                });
                            }
                        }, 3000);
                    }
                });

                map.addControl(searchControl);
            } catch (error) {
                console.error('Error initializing Leaflet Search:', error);
            }
        }

        // Function to initialize scatter chart
        function initializeScatterChart() {
            // Prevent multiple initializations
            if (scatterChart) {
                return;
            }

            // Check if canvas element exists
            var canvas = document.getElementById('scatterChart');
            if (!canvas) {
                return;
            }

            var scatterDataObj = @json($scatterData);
            var scatterData = scatterDataObj.data || [];
            var parameterLabels = scatterDataObj.labels || [];

            // Prepare data for Chart.js: X = parameter index, Y = jumlah abnormal
            var chartData = [];

            scatterData.forEach(function(point) {
                // Add small random jitter to prevent overlapping points
                var jitter = (Math.random() - 0.5) * 0.15;

                chartData.push({
                    x: point.x + jitter,
                    y: point.y,
                    parameter: point.parameter,
                    abnormal_count: point.abnormal_count || point.y,
                    below_count: point.below_count || 0,
                    above_count: point.above_count || 0,
                    normal_count: point.normal_count || 0
                });
            });

            // Create scatter plot
            var ctx = document.getElementById('scatterChart').getContext('2d');
            scatterChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Jumlah Melewati Baku Mutu',
                        data: chartData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 2,
                    animation: {
                        duration: 0
                    },
                    interaction: {
                        intersect: false,
                        mode: 'point'
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Nama Pemeriksaan'
                            },
                            ticks: {
                                callback: function(value, index) {
                                    var intValue = Math.round(value);
                                    return parameterLabels[intValue] || '';
                                },
                                maxRotation: 45,
                                minRotation: 45,
                                autoSkip: false
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Jumlah Melewati Baku Mutu'
                            },
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var point = context.raw;
                                    return [
                                        'Parameter: ' + (point.parameter || 'N/A'),
                                        'Di bawah: ' + (point.below_count || 0),
                                        'Di atas: ' + (point.above_count || 0),
                                        'Total melewati: ' + (point.abnormal_count || point.y),
                                        'Tidak melewati: ' + (point.normal_count || 0)
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize based on view type
        if (viewType === 'both' || viewType === 'map') {
            initializeMap();
        }

        if (viewType === 'both' || viewType === 'scatter') {
            initializeScatterChart();
        }


        // Update wilayah options when tipe changes
        function updateWilayahOptions() {
            // Reset wilayah_id when tipe changes
            document.getElementById('wilayah_id').value = '';
            // Submit form to preserve all filters
            document.getElementById('filterForm').submit();
        }

        function submitFilterForm() {
            document.getElementById('filterForm').submit();
        }

        // Auto-submit filter tanpa klik tombol Filter
        $(document).ready(function() {
            var umurTimer = null;

            function scheduleUmurSubmit() {
                clearTimeout(umurTimer);
                umurTimer = setTimeout(submitFilterForm, 700);
            }

            $('#wilayah_id').on('select2:select select2:clear', function() {
                setTimeout(submitFilterForm, 100);
            });

            $('#bulan, #tahun, #gender').on('change', function() {
                submitFilterForm();
            });

            $('#umur_min, #umur_max').on('change', function() {
                scheduleUmurSubmit();
            }).on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(umurTimer);
                    submitFilterForm();
                }
            });

            var paramSearch = document.getElementById('paramStatsSearch');
            if (paramSearch) {
                paramSearch.addEventListener('input', function() {
                    var term = (paramSearch.value || '').toLowerCase().trim();
                    var rows = document.querySelectorAll('#paramStatsTable tbody tr');
                    var visible = 0;
                    rows.forEach(function(row) {
                        var name = row.getAttribute('data-name') || '';
                        var show = !term || name.indexOf(term) !== -1;
                        row.style.display = show ? '' : 'none';
                        if (show) visible++;
                    });
                    var empty = document.getElementById('paramStatsEmpty');
                    if (empty) empty.style.display = visible ? 'none' : 'block';
                });
            }
        });

        // Set tipe parameter
        var currentTipeParameter = '{{ $tipeParameter }}';

        function setTipeParameter(tipe) {
            currentTipeParameter = tipe;
            // Uncheck all checkboxes when switching tabs
            document.querySelectorAll('.parameter-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            // Re-check based on current selection if any
            var currentIds = document.getElementById('parameter_ids').value;
            if (currentIds && currentTipeParameter === tipe) {
                var ids = currentIds.split(',');
                ids.forEach(function(id) {
                    var checkbox = document.getElementById((tipe === 'paket' ? 'paket_' : 'satuan_') + id);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        // Filter parameter by search
        function filterParameter(tipe) {
            var searchTerm = document.getElementById('search' + (tipe === 'satuan' ? 'Satuan' : 'Paket')).value
                .toLowerCase();
            var items = document.querySelectorAll('#list' + (tipe === 'satuan' ? 'Satuan' : 'Paket') + ' .parameter-item');

            items.forEach(function(item) {
                var name = item.getAttribute('data-name');
                if (name.indexOf(searchTerm) !== -1) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        // Apply pemeriksaan filter
        function applyPemeriksaanFilter() {
            // Get checkboxes based on current tipe parameter
            var selector = currentTipeParameter === 'paket' ? '.parameter-paket:checked' : '.parameter-satuan:checked';
            var checkboxes = document.querySelectorAll(selector);
            var selectedIds = Array.from(checkboxes).map(function(cb) {
                return cb.value;
            });

            document.getElementById('parameter_ids').value = selectedIds.join(',');
            document.getElementById('tipe_parameter').value = currentTipeParameter;

            var count = selectedIds.length;
            var text = count > 0 ? count + ' pemeriksaan dipilih' : 'Semua pemeriksaan';
            document.getElementById('selectedPemeriksaanText').textContent = text;

            $('#modalPemeriksaan').modal('hide');

            // Auto-submit form after applying filter
            document.getElementById('filterForm').submit();
        }

        // Initialize tipe parameter
        document.getElementById('tipe_parameter').value = currentTipeParameter;

        // Select all parameters
        function selectAllParameter(tipe) {
            var checkboxes = document.querySelectorAll('.parameter-' + tipe + ':not(.hidden)');
            checkboxes.forEach(function(cb) {
                cb.checked = true;
            });
        }

        // Deselect all parameters
        function deselectAllParameter(tipe) {
            var checkboxes = document.querySelectorAll('.parameter-' + tipe + ':not(.hidden)');
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });
        }

        // Initialize modal when opened
        $('#modalPemeriksaan').on('show.bs.modal', function() {
            // Set active tab based on current tipe parameter
            if (currentTipeParameter === 'paket') {
                $('.nav-link[href="#tabPaket"]').tab('show');
            } else {
                $('.nav-link[href="#tabSatuan"]').tab('show');
            }

            // Restore checked state based on current selection
            var currentIds = document.getElementById('parameter_ids').value;
            if (currentIds) {
                var ids = currentIds.split(',');
                var selector = currentTipeParameter === 'paket' ? '.parameter-paket' : '.parameter-satuan';
                document.querySelectorAll(selector).forEach(function(cb) {
                    cb.checked = ids.indexOf(cb.value) !== -1;
                });
            }
        });

        // Handle view type change
        $('input[name="view_type"]').on('change', function() {
            var selectedView = $(this).val();

            if (selectedView === 'both') {
                $('#mapSection').show();
                $('#scatterSection').show();

                // Initialize map if not already initialized
                if (!map) {
                    initializeMap();
                } else {
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 100);
                }

                // Initialize scatter chart if not already initialized
                if (!scatterChart && $('#scatterSection').is(':visible')) {
                    setTimeout(function() {
                        initializeScatterChart();
                    }, 100);
                }
            } else if (selectedView === 'map') {
                $('#mapSection').show();
                $('#scatterSection').hide();

                // Initialize map if not already initialized
                if (!map) {
                    initializeMap();
                } else {
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 100);
                }

                // Destroy scatter chart if exists
                if (scatterChart) {
                    scatterChart.destroy();
                    scatterChart = null;
                }
            } else if (selectedView === 'scatter') {
                $('#mapSection').hide();
                $('#scatterSection').show();

                // Destroy map if exists
                if (map) {
                    map.remove();
                    map = null;
                }

                // Initialize scatter chart if not already initialized
                if (!scatterChart && $('#scatterSection').is(':visible')) {
                    setTimeout(function() {
                        initializeScatterChart();
                    }, 100);
                }
            }
        });

        // Prevent window resize from re-initializing chart
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (scatterChart && $('#scatterSection').is(':visible')) {
                    scatterChart.resize();
                }
            }, 250);
        });
    </script>
@endsection
