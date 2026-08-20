@extends('masterweb::template.admin.layout')

@section('title')
    Dashboard Dokter - Analisis Hasil Klinik
@endsection

@section('css')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                <form method="GET" action="{{ route('dokter.dashboard') }}" id="filterForm">
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
                                    Daerah Kab. Magelang</option>
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
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="mdi mdi-filter"></i> Filter
                            </button>
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
                <h3>Total Sampel</h3>
                <div class="value">{{ number_format($statistics['total_samples']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Rata-rata Hasil</h3>
                <div class="value">{{ number_format($statistics['average'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Maksimal</h3>
                <div class="value">{{ number_format($statistics['max'], 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <h3>Abnormal (%)</h3>
                <div class="value">{{ number_format($statistics['abnormal_percentage'], 2) }}%</div>
                <small>({{ number_format($statistics['abnormal_count']) }} kasus)</small>
            </div>
        </div>
    </div>

    <!-- Statistics per Parameter -->
    @if (isset($statistics['parameter_stats']) && count($statistics['parameter_stats']) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Statistik per Parameter</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Parameter</th>
                                        <th>Rata-rata</th>
                                        <th>Maksimal</th>
                                        <th>Minimal</th>
                                        <th>Jumlah Abnormal</th>
                                        <th>% Abnormal</th>
                                        <th>Total Hasil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($statistics['parameter_stats'] as $index => $param)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $param['parameter_name'] }}</strong></td>
                                            <td>{{ number_format($param['average'], 2) }}</td>
                                            <td>{{ number_format($param['max'], 2) }}</td>
                                            <td>{{ number_format($param['min'], 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $param['abnormal_count'] > 0 ? 'danger' : 'success' }}">
                                                    {{ number_format($param['abnormal_count']) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $param['abnormal_percentage'] > 0 ? 'warning' : 'success' }}">
                                                    {{ number_format($param['abnormal_percentage'], 2) }}%
                                                </span>
                                            </td>
                                            <td>{{ number_format($param['total_results']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                    <h4 class="card-title">Peta Persebaran Hasil Klinik</h4>
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
                <canvas id="scatterChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Create search layer group
            searchLayer = L.layerGroup().addTo(map);

            // Map data
            var mapData = @json($mapData);

            // Add markers to map and search layer
            mapData.forEach(function(data) {
                var color = data.avg_hasil > 0 ? '#ff0000' : '#00ff00';
                var marker = L.circleMarker([data.lat, data.lng], {
                    radius: 10,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                });

                // Add to search layer
                marker.addTo(searchLayer);

                // Set title for search (propertyName) - required by leaflet-search
                marker.options.title = data.nama;
                marker.options.loc = [data.lat, data.lng];

                marker.bindPopup(`
                     <strong>${data.nama}</strong><br>
                     Kode: ${data.kode}<br>
                     Tipe: ${data.tipe}<br>
                     Rata-rata: ${data.avg_hasil}<br>
                     Maksimal: ${data.max_hasil}<br>
                     Minimal: ${data.min_hasil}<br>
                     Total Sampel: ${data.total_samples}
                 `);
            });

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
                                var color = foundData.avg_hasil > 0 ? '#ff0000' : '#00ff00';
                                e.layer.setStyle({
                                    fillColor: color,
                                    color: '#fff',
                                    weight: 2,
                                    radius: 10
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
                    abnormal_count: point.abnormal_count || point.y
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
                                        'Jumlah Melewati Baku Mutu: ' + (point.abnormal_count || point.y)
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

        // Auto-submit form when select2 changes (for wilayah_id)
        $(document).ready(function() {
            $('#wilayah_id').on('select2:select', function() {
                // Small delay to ensure value is set
                setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 100);
            });
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
