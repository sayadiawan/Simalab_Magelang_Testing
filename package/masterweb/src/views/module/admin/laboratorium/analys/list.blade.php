@extends('masterweb::template.admin.layout')

@section('title')
    Beranda
@endsection

@php
    $user = Auth()->user();
@endphp

@section('content')
    <style>
        /* DataTable styling */
        #empTable {
            width: 100% !important;
            font-size: 0.9rem;
        }

        table.dataTable#empTable.table-striped > tbody > tr > *,
        table.dataTable#empTable.table-striped > tbody > tr.odd > *,
        table.dataTable#empTable.table-striped > tbody > tr.even > * {
            box-shadow: none !important;
        }

        table.dataTable#empTable.table-striped > tbody > tr.odd {
            background-color: #fafafa;
        }

        #empTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th.sorting,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_asc,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_desc {
            background-color: #0b3a5c !important;
            color: #ffffff !important;
            font-weight: 600;
            border: none;
            padding: 12px 10px;
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

        /* Tab Filter Styling */
        .tab-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
        }

        .tab-filter-item {
            padding: 12px 20px;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            bottom: -2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-filter-item:hover {
            color: #3ca8a8;
            background-color: #f8f9fa;
        }

        .tab-filter-item.active {
            color: #3ca8a8;
            border-bottom-color: #3ca8a8;
            background-color: #f8f9fa;
        }

        .tab-filter-item i {
            margin-right: 0;
        }

        .tab-filter-item .badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            background-color: #6c757d;
            color: #fff;
            min-width: 24px;
            text-align: center;
        }

        .tab-filter-item.active .badge {
            background-color: #3ca8a8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .tab-filter {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .tab-filter-item {
                white-space: nowrap;
                padding: 10px 15px;
                font-size: 0.85rem;
            }
        }
    </style>
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Data Sampel
                </div>

                <div class="p-2">
                    {{-- <button type="button" class="btn btn-primary mr-1" data-toggle="modal" data-target="#importExcel">
                    IMPORT EXCEL
                </button> --}}

                    <!-- Import Excel -->
                    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form method="post" action="/elits-excel/formImports" enctype="multipart/form-data">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                                    </div>
                                    <div class="modal-body">

                                        {{ csrf_field() }}

                                        <div class="form-group">

                                            <div class="row">

                                                <div class="col-md-12">
                                                    <label for="method">Pilih Method</label>
                                                    <select class="form-select form-control" id="method" name="method"
                                                        aria-label="Pilih Method">
                                                        @foreach ($methods as $method)
                                                            <option value="{{ $method->id_method }}">
                                                                {{ $method->params_method }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div>
                                        </div>

                                        <label>Pilih file excel</label>
                                        <div class="form-group">

                                            <input type="file" name="file" required="required">
                                        </div>

                                    </div>
                                    <div id="test">
                                    </div>
                                    <div class="modal-footer ">
                                        <button type="button" id="download" class="btn btn-primary mr-auto">Download
                                            Format</button>


                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Import</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Tab Filter -->
                    @php
                        $userLevel = $userLevel ?? (Auth::user()->getlevel->level ?? null);
                        // Tentukan tab yang boleh ditampilkan berdasarkan level
                        $allowedTabs = [];
                        if (in_array($userLevel ?? null, ['ANLS', 'ALAB'])) {
                            // ANLS/LAB/ALAB: penerimaan_sample, pemeriksaan, input_hasil, verifikasi, validasi (tanpa Semua dan Selesai)
                            $allowedTabs = ['penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi'];
                        } elseif (($userLevel ?? null) == 'RGSTR') {
                            // RGSTR: belum_pemeriksaan, selesai
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'selesai'];
                        } elseif (($userLevel ?? null) == 'DKTR') {
                            // DKTR: belum_pemeriksaan, validasi, selesai
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'validasi', 'selesai'];
                        } elseif (\Smt\Masterweb\Helpers\SampleCollectorAccess::isKesmas($userLevel ?? null)) {
                            // Pengambil sampel kesmas (SOLM): pengambilan_sample
                            $allowedTabs = ['pengambilan_sample'];
                        } else {
                            // Selain itu: semua tab
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'pengambilan_sample', 'penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi', 'selesai'];
                        }
                        
                        $urlStatusFilter = request()->get('status_filter', '');
                        if ($urlStatusFilter && !in_array($urlStatusFilter, $allowedTabs, true)) {
                            $urlStatusFilter = '';
                        }

                        // Tentukan tab pertama yang akan menjadi active jika tab "Semua" tidak ada
                        $firstVisibleTab = null;
                        if ($urlStatusFilter) {
                            $firstVisibleTab = $urlStatusFilter;
                        } elseif (!in_array('all', $allowedTabs)) {
                            // Urutan tab yang mungkin muncul pertama
                            $tabOrder = ['belum_pemeriksaan', 'pengambilan_sample', 'penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi', 'selesai'];
                            foreach ($tabOrder as $tab) {
                                if (in_array($tab, $allowedTabs)) {
                                    $firstVisibleTab = $tab;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="tab-filter" id="tabFilter">
                        @if (in_array('all', $allowedTabs))
                        <button class="tab-filter-item active" data-filter="all" data-status="">
                            <i class="fa fa-list"></i> Semua
                            <span class="badge" id="badge-all">0</span>
                        </button>
                        @endif
                        @if (in_array('belum_pemeriksaan', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'belum_pemeriksaan' ? 'active' : '' }}" data-filter="belum_pemeriksaan" data-status="belum_pemeriksaan">
                            <i class="fa fa-clock"></i> Belum Pemeriksaan
                            <span class="badge" id="badge-belum-pemeriksaan">0</span>
                        </button>
                        @endif
                        @if (in_array('pengambilan_sample', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'pengambilan_sample' ? 'active' : '' }}" data-filter="pengambilan_sample" data-status="pengambilan_sample">
                            <i class="fa fa-vial"></i> Pengambilan Sample
                            <span class="badge" id="badge-pengambilan-sample">0</span>
                        </button>
                        @endif
                        @if (in_array('penerimaan_sample', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'penerimaan_sample' ? 'active' : '' }}" data-filter="penerimaan_sample" data-status="penerimaan_sample">
                            <i class="fa fa-inbox"></i> Penerimaan Sample
                            <span class="badge" id="badge-penerimaan-sample">0</span>
                        </button>
                        @endif
                        @if (in_array('pemeriksaan', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'pemeriksaan' ? 'active' : '' }}" data-filter="pemeriksaan" data-status="pemeriksaan">
                            <i class="fa fa-flask"></i> Pemeriksaan
                            <span class="badge" id="badge-pemeriksaan">0</span>
                        </button>
                        @endif
                        @if (in_array('input_hasil', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'input_hasil' ? 'active' : '' }}" data-filter="input_hasil" data-status="input_hasil">
                            <i class="fa fa-file-medical"></i> Input Hasil
                            <span class="badge" id="badge-input-hasil">0</span>
                        </button>
                        @endif
                        @if (in_array('verifikasi', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'verifikasi' ? 'active' : '' }}" data-filter="verifikasi" data-status="verifikasi">
                            <i class="fa fa-check-circle"></i> Verifikasi
                            <span class="badge" id="badge-verifikasi">0</span>
                        </button>
                        @endif
                        @if (in_array('validasi', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'validasi' ? 'active' : '' }}" data-filter="validasi" data-status="validasi">
                            <i class="fa fa-shield-alt"></i> Validasi
                            <span class="badge" id="badge-validasi">0</span>
                        </button>
                        @endif
                        @if (in_array('selesai', $allowedTabs))
                        <button class="tab-filter-item {{ $firstVisibleTab == 'selesai' ? 'active' : '' }}" data-filter="selesai" data-status="selesai">
                            <i class="fa fa-check-double"></i> Selesai
                            <span class="badge" id="badge-selesai">0</span>
                        </button>
                        @endif
                    </div>

                    <div class="table-responsive-wrapper">
                        <table id="empTable" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Pelanggan</th>
                                    <th>Nomor Sampel</th>
                                    <th>Jenis Sampel</th>
                                    <th>Status Pengajuan</th>
                                    <th>Tanggal Diterima</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan di-load via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        // Function to load statistics
        function loadStatistics() {
            $.ajax({
                url: "{{ route('elits-analys.statistics') }}",
                type: "GET",
                success: function(response) {
                    // Update badge counts
                    if ($('#badge-all').length) {
                        var total = (response.belum_pemeriksaan || 0) + 
                                   (response.pengambilan_sample || 0) + 
                                   (response.penerimaan_sample || 0) + 
                                   (response.pemeriksaan || 0) + 
                                   (response.input_hasil || 0) + 
                                   (response.verifikasi || 0) + 
                                   (response.validasi || 0) + 
                                   (response.selesai || 0);
                        $('#badge-all').text(total);
                    }
                    if ($('#badge-belum-pemeriksaan').length) {
                        $('#badge-belum-pemeriksaan').text(response.belum_pemeriksaan || 0);
                    }
                    if ($('#badge-pengambilan-sample').length) {
                        $('#badge-pengambilan-sample').text(response.pengambilan_sample || 0);
                    }
                    if ($('#badge-penerimaan-sample').length) {
                        $('#badge-penerimaan-sample').text(response.penerimaan_sample || 0);
                    }
                    if ($('#badge-pemeriksaan').length) {
                        $('#badge-pemeriksaan').text(response.pemeriksaan || 0);
                    }
                    if ($('#badge-input-hasil').length) {
                        $('#badge-input-hasil').text(response.input_hasil || 0);
                    }
                    if ($('#badge-verifikasi').length) {
                        $('#badge-verifikasi').text(response.verifikasi || 0);
                    }
                    if ($('#badge-validasi').length) {
                        $('#badge-validasi').text(response.validasi || 0);
                    }
                    if ($('#badge-selesai').length) {
                        $('#badge-selesai').text(response.selesai || 0);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading statistics:', error);
                }
            });
        }

        $(document).ready(function() {
            // Set tab pertama sebagai active jika tidak ada tab "Semua"
            if ($('.tab-filter-item[data-filter="all"]').length === 0) {
                var firstTab = $('.tab-filter-item').first();
                if (firstTab.length > 0) {
                    $('.tab-filter-item').removeClass('active');
                    firstTab.addClass('active');
                }
            }

            // Load statistics on page load
            setTimeout(function() {
                loadStatistics();
            }, 100);

            // Tab filter click handler
            $('.tab-filter-item').on('click', function() {
                $('.tab-filter-item').removeClass('active');
                $(this).addClass('active');
                
                // Reload DataTable dengan filter baru
                if (table) {
                    table.ajax.reload();
                }
            });

            $("#download").click(function() {
                var id_method = $('#method').find(":selected").val()
                console.log(id_method)
                var url = "{{ route('elits-excel.downloadFormImports', ':id') }}";
                url = url.replace(":id", id_method);
                console.log(url);
                window.location = url;
            })

            var table = $('#empTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: true,
                ajax: {
                    url: "{{ route('elits-analys.data-analys') }}",
                    type: "GET",
                    data: function(d) {
                        d.search = $('input[type="search"]').val();
                        d.status_filter = $('.tab-filter-item.active').data('status') || '';
                    }
                },
                columns: [{
                        data: 'nomer',
                    },
                    {
                        data: 'pelanggan',
                        name: 'pelanggan'
                    },
                    {
                        data: 'codesample_samples',
                        name: 'codesample_samples'
                    },
                    {
                        data: 'name_sample_type',
                        name: 'name_sample_type'
                    },
                    {
                        data: 'last_status',
                        name: 'last_status'
                    },
                    {
                        data: 'date_sending',
                        name: 'date_sending'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // datatables responsive
            new $.fn.dataTable.FixedHeader(table);
        })
    </script>
@endsection
