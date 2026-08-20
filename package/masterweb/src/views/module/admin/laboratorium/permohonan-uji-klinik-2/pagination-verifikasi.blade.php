@extends('masterweb::template.admin.layout')

@section('title')
    Verifikasi Permohonan Uji Klinik
@endsection

@section('content')
    <style>
        .pointer {
            cursor: pointer;
        }

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* Full width container */
        .content-wrapper {
            padding: 1.5rem;
        }

        /* Card styling */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table responsive wrapper */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* DataTable styling */
        #empTable {
            width: 100% !important;
            font-size: 0.9rem;
            table-layout: fixed;
        }

        table.dataTable#empTable.table-striped > tbody > tr > *,
        table.dataTable#empTable.table-striped > tbody > tr.odd > *,
        table.dataTable#empTable.table-striped > tbody > tr.even > * {
            box-shadow: none !important;
        }

        table.dataTable#empTable.table-striped > tbody > tr.odd {
            background-color: #fafafa;
        }

        .dataTables_wrapper {
            contain: layout style;
        }

        #empTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th,
        .dataTables_wrapper #empTable.dataTable thead th.sorting,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_asc,
        .dataTables_wrapper #empTable.dataTable thead th.sorting_desc {
            background-color: #2D6BCF !important;
            color: #fff !important;
            font-weight: 600;
            border: none;
            padding: 12px 8px;
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

        #empTable .kesmas-verifikasi-customer-cell {
            line-height: 1.45;
        }

        #empTable .kesmas-verifikasi-customer-cell .kesmas-verifikasi-customer-name {
            font-size: 1.05rem;
            font-weight: 600;
        }

        #empTable .kesmas-verifikasi-customer-cell .kesmas-verifikasi-titik {
            font-size: 0.98rem;
            color: #6c757d;
            margin-top: 4px;
        }

        #empTable tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Styling untuk dropdown action */
        #empTable .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            padding: 8px 0;
            min-width: 220px;
        }

        #empTable .dropdown-header {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 16px 4px;
            margin-bottom: 4px;
        }

        #empTable .dropdown-item {
            padding: 8px 16px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        #empTable .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #2D6BCF;
        }

        #empTable .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        #empTable .dropdown-item.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #empTable .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid #e9ecef;
        }

        #empTable .btn-primary {
            border-radius: 6px;
            padding: 6px 16px;
            font-size: 0.875rem;
        }

        /* Custom scrollbar untuk table */
        .table-responsive-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
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
            color: #2D6BCF;
            background-color: #f8f9fa;
        }

        .tab-filter-item.active {
            color: #2D6BCF;
            border-bottom-color: #2D6BCF;
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
            background-color: #2D6BCF;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            #empTable {
                font-size: 0.8rem;
            }

            #empTable thead th,
            #empTable tbody td {
                padding: 8px 4px;
            }

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
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>

    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/home') }}">
                            <i class="fa fa-home menu-icon mr-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Laboraturium</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Verifikasi Permohonan Uji Klinik
                    </li>
                </ol>
            </nav>

            <!-- Alert Messages -->
            @if (session('error-bsre'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error-bsre') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error-laporan'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: "Gagal Melakukan Tanda Tangan Elektronik",
                            text: "Terjadi kesalahan saat melakukan tanda tangan elektronik. Silakan coba lagi.",
                            icon: "warning",
                            customClass: {
                                popup: 'my-custom-popup-class'
                            }
                        });
                    });
                </script>
            @endif

            <!-- Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="mb-4">
                        @if($isKesmas)
                            @if(in_array($kodeLab, ['KIM', 'KMA', 'FKA']))
                                <h4 class="card-title mb-0">Daftar Verifikasi Sample Kimia</h4>
                                <p class="text-muted mb-0">Kelola verifikasi sample kimia yang sudah terdaftar</p>
                            @elseif($kodeLab == 'MBI')
                                <h4 class="card-title mb-0">Daftar Verifikasi Sample Mikrobiologi</h4>
                                <p class="text-muted mb-0">Kelola verifikasi sample mikrobiologi yang sudah terdaftar</p>
                            @else
                                <h4 class="card-title mb-0">Daftar Verifikasi Sample</h4>
                                <p class="text-muted mb-0">Kelola verifikasi sample yang sudah terdaftar</p>
                            @endif
                        @else
                            <h4 class="card-title mb-0">Daftar Verifikasi Permohonan Uji Klinik</h4>
                            <p class="text-muted mb-0">Kelola verifikasi permohonan uji klinik yang sudah terdaftar</p>
                        @endif
                    </div>

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        @if($isKesmas)
                            <!-- Filter Jenis Sample untuk Kesmas -->
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="form-label">Jenis Sample</label>
                                <select id="filter-jenis-sample" class="form-control">
                                    <option value="all">All</option>
                                    @foreach($sampleTypes as $sampleType)
                                        <option value="{{ $sampleType->id_sample_type }}">{{ $sampleType->name_sample_type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <!-- Filter Tipe Permohonan untuk Klinik -->
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="form-label">Tipe Permohonan</label>
                                <select id="filter-permohonan_uji_klinik" class="form-control">
                                    <option value="all">All</option>
                                    <option value="0">Permohonan Uji Klinik</option>
                                    <option value="4">Permohonan Uji Klinik Prolanis</option>
                                    <option value="1">Permohonan Uji Klinik Prolanis Gula</option>
                                    <option value="2">Permohonan Uji Klinik Prolanis Urine</option>
                                    <option value="3">Permohonan Uji Klinik Haji</option>
                                </select>
                            </div>

                            <!-- Filter Pemeriksaan Haji -->
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="form-label">Pemeriksaan Haji</label>
                                <select id="filter-pemeriksaan-haji" class="form-control">
                                    <option value="all">Semua</option>
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                            </div>
                        @endif

                        <!-- Filter Grup untuk ALAB, PLAB, ANLS dengan mikro atau kimia -->
                        @if(in_array($userLevel, ['ALAB', 'PLAB', 'ANLS']) && (in_array($kodeLab, ['KIM', 'KMA', 'FKA', 'MBI']) || $kodeLab == 'KLI'))
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="form-label">Grup</label>
                                <select id="filter-grup" class="form-control">
                                    <option value="all">Semua Grup</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Dropdown tambahan untuk filter prolanis -->
                        <div class="col-md-3 col-sm-6 mb-2" id="filter-prolanis-wrapper" style="display: none;">
                            <label class="form-label">Prolanis</label>
                            <select id="filter-prolanis" class="form-control">
                                <option value="all">All Prolanis</option>
                                <!-- Data gula akan dimasukkan di sini dari AJAX -->
                            </select>
                        </div>

                        <!-- Dropdown tambahan untuk filter prolanis gula -->
                        <div class="col-md-3 col-sm-6 mb-2" id="filter-prolanis-gula-wrapper" style="display: none;">
                            <label class="form-label">Prolanis Gula</label>
                            <select id="filter-prolanis-gula" class="form-control">
                                <option value="all">All Prolanis Gula</option>
                                <!-- Data gula akan dimasukkan di sini dari AJAX -->
                            </select>
                        </div>

                        <!-- Dropdown tambahan untuk filter prolanis urine -->
                        <div class="col-md-3 col-sm-6 mb-2" id="filter-prolanis-urine-wrapper" style="display: none;">
                            <label class="form-label">Prolanis Urine</label>
                            <select id="filter-prolanis-urine" class="form-control">
                                <option value="all">All Prolanis Urine</option>
                                <!-- Data urine akan dimasukkan di sini dari AJAX -->
                            </select>
                        </div>

                        <!-- Dropdown tambahan untuk filter rombongan haji -->
                        <div class="col-md-3 col-sm-6 mb-2" id="filter-haji-wrapper" style="display: none;">
                            <label class="form-label">Rombongan Haji</label>
                            <select id="filter-haji" class="form-control">
                                <option value="all">Semua Rombongan</option>
                                <!-- Data haji akan dimasukkan di sini dari AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="form-label" for="filter-date-start">Tanggal Mulai</label>
                            <input type="date" id="filter-date-start" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="form-label" for="filter-date-end">Tanggal Akhir</label>
                            <input type="date" id="filter-date-end" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto mb-2 d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-filter-date">
                                <i class="fa fa-filter mr-1"></i> Terapkan
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-date-filter">
                                <i class="fa fa-times mr-1"></i> Reset
                            </button>
                            <small class="text-muted mb-0">
                                @if($isKesmas)
                                    Filter tanggal pengiriman sampel
                                @else
                                    Filter tanggal pendaftaran
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Tab Filter -->
                    @php
                        $user = auth()->user();
                        $userLevel = $user && $user->getlevel ? $user->getlevel->level : null;
                        $kodeLab = $user && $user->laboratorium ? $user->laboratorium->kode_laboratorium : null;
                        $isKesmas = in_array($kodeLab, ['KIM', 'KMA', 'FKA', 'MBI']);
                        $isKlinik = ($kodeLab == 'KLI');
                        
                        // Tentukan tab yang boleh ditampilkan berdasarkan level dan kode lab
                        $allowedTabs = [];
                        if ($isKesmas) {
                            // Kesmas (KIM, KMA, FKA, MBI): semua tab termasuk Semua dan Selesai
                            $allowedTabs = ['all', 'penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi', 'selesai'];
                        } elseif ($userLevel == 'SOLAB') {
                            // SOLAB: hanya pengambilan_sample (tanpa Semua dan Selesai)
                            $allowedTabs = ['pengambilan_sample'];
                        } elseif ($isKlinik || $userLevel == 'ANLS') {
                            // Klinik (KLI) atau ANLS: penerimaan_sample, pemeriksaan, input_hasil, verifikasi, validasi (tanpa Semua dan Selesai)
                            $allowedTabs = ['penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi'];
                        } elseif ($userLevel == 'RGSTR') {
                            // RGSTR: belum_pemeriksaan, selesai
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'selesai'];
                        } elseif ($userLevel == 'DKTR') {
                            // DKTR: belum_pemeriksaan, validasi, selesai
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'validasi', 'selesai'];
                        } else {
                            // Selain itu: semua tab
                            $allowedTabs = ['all', 'belum_pemeriksaan', 'pengambilan_sample', 'penerimaan_sample', 'pemeriksaan', 'input_hasil', 'verifikasi', 'validasi', 'selesai'];
                        }
                    @endphp
                    @php
                        // Tentukan tab pertama yang akan menjadi active jika tab "Semua" tidak ada
                        $firstVisibleTab = null;
                        if (!in_array('all', $allowedTabs)) {
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

                    <!-- Data Table -->
                    <div class="table-responsive-wrapper">
                        <table id='empTable' class="table table-striped table-bordered smt-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>No Register</th>
                                    <th>Tipe Pemeriksaan</th>
                                    <th>{{ $isKesmas ? 'Nama Customer' : 'Nama Pasien' }}</th>
                                    @if($isKesmas)
                                        @if(in_array($kodeLab, ['KIM', 'KMA', 'FKA']))
                                            <th>Paket/Parameter Kimia</th>
                                        @elseif($kodeLab == 'MBI')
                                            <th>Paket/Parameter Mikro</th>
                                        @else
                                            <th>Paket/Parameter</th>
                                        @endif
                                    @else
                                        <th>Status Registrasi</th>
                                    @endif
                                    <th>Status Proses</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-body">
                                <!-- Data akan di-load via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PAYMENT --}}
    <div class="modal fade text-left" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel1">Basic Modal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="" method="POST" id="form-payment" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="form-control" name="_token" id="csrf-token"
                        value="{{ Session::token() }}" />
                    <input type="hidden" class="form-control" name="id_permohonan_uji_klinik" id="id_permohonan_uji_klinik"
                        readonly>
                    <input type="hidden" class="form-control" name="nota_petugas_permohonan_uji_payment_klinik"
                        id="nota_petugas_permohonan_uji_payment_klinik" readonly>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_pasien">Nama Pasien</label>
                            <input type="text" class="form-control" id="nama_pasien" name="nama_pasien"
                                placeholder="Enter nama pasien">
                        </div>

                        <div class="form-group">
                            <label for="alamat_pasien">Alamat Pasien</label>
                            <textarea class="form-control" id="alamat_pasien" name="alamat_pasien" cols="30" rows="10"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="total_harga">Total</label>
                            <input type="text" class="form-control" id="total_harga_custom" name="total_harga_custom"
                                placeholder="Enter total harga">

                            <input type="hidden" class="form-control" id="total_harga" name="total_harga" readonly>
                        </div>

                        <div class="form-group">
                            <label for="total_harga">Petugas</label>
                            <input type="text" class="form-control"
                                id="nota_namapetugas_permohonan_uji_payment_klinik"
                                name="nota_namapetugas_permohonan_uji_payment_klinik" placeholder="Masukkan nama petugas">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" data-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                        </button>

                        <button type="button" class="btn btn-primary ml-1" id="btnSave">Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

{{--    Modal Option SIGN--}}
    <div class="modal fade" id="signOptionModal" tabindex="-1" aria-labelledby="signOptionTitle" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="signOptionTitle">Pilih metode tanda tangan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="d-flex mx-auto m-2 justify-content-around">
            <a id="linkTTDManual" href=""
               target="_blank">
              <button class="btn text-center m-2 p-2 sign-opt">
                <img src="{{ asset('assets/admin/images/sign-icon.png') }}" width="80" height="80">
                <h5 class="mt-2">Tanda Tangan Manual</h5>
              </button>
            </a>
            <a id="linkTTDElektronik" href=""
               target="_blank">
              <button class="btn text-center m-2 p-2 sign-opt">
                <img src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" width="80" height="80">
                <h5 class="mt-2">Tanda Tangan Elektronik</h5>
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>
@endsection


@section('scripts')
    <script type="text/javascript">
        // Function to load statistics
        function loadStatistics() {
            var dataParams = {};
            @if($isKesmas)
                dataParams.filter_jenis_sample = $('#filter-jenis-sample').val() || 'all';
            @else
                dataParams.is_filter = $('#filter-permohonan_uji_klinik').val() || 'all';
                dataParams.filter_prolanis_gula = $('#filter-prolanis-gula').val() || 'all';
                dataParams.filter_prolanis_urine = $('#filter-prolanis-urine').val() || 'all';
                dataParams.filter_prolanis = $('#filter-prolanis').val() || 'all';
                dataParams.filter_pemeriksaan_haji = $('#filter-pemeriksaan-haji').val() || 'all';
                dataParams.filter_haji = $('#filter-haji').val() || 'all';
            @endif
            dataParams.date_start = $('#filter-date-start').val();
            dataParams.date_end = $('#filter-date-end').val();
            
            // Add group filter if exists
            @if(in_array($userLevel, ['ALAB', 'PLAB', 'ANLS']) && (in_array($kodeLab, ['KIM', 'KMA', 'FKA', 'MBI']) || $kodeLab == 'KLI'))
                dataParams.filter_grup = $('#filter-grup').val() || 'all';
            @endif
            
            $.ajax({
                url: "{{ route('elits-permohonan-uji-klinik.statistics-verifikasi') }}",
                type: "GET",
                data: dataParams,
                cache: false, // pastikan tidak di-cache oleh browser / service worker
                success: function(response) {
                    // Update badge di tab filter (hanya untuk tab yang ditampilkan)
                    if ($('#badge-all').length) {
                        // Calculate total from all badges
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
                    console.error('Response:', xhr.responseText);
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
            
            // Load statistics setelah tabel pertama kali selesai (hindari 2 query berat bersamaan)
            var statisticsScheduled = false;
            function scheduleStatistics() {
                if (statisticsScheduled) {
                    return;
                }
                statisticsScheduled = true;
                var run = function() {
                    loadStatistics();
                };
                if (window.requestIdleCallback) {
                    requestIdleCallback(run, { timeout: 2500 });
                } else {
                    setTimeout(run, 1200);
                }
            }

            // DataTable columns configuration
            var columns = [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'noregister_permohonan_uji_klinik',
                    name: 'noregister_permohonan_uji_klinik'
                },
                {
                    data: 'prolanis',
                    name: 'prolanis'
                },
                @if($isKesmas)
                {
                    data: 'nama_pasien',
                    name: 'nama_pasien',
                    orderable: true,
                    searchable: true
                },
                @else
                {
                    data: 'nama_pasien',
                    name: 'nama_pasien',
                    render: function(data, type, row) {
                        if (!data) {
                            return '';
                        }
                        return data.toString().toUpperCase();
                    }
                },
                @endif
                {
                    data: 'status_registrasi',
                    name: 'status_registrasi'
                },
                {
                    data: 'status_permohonan',
                    name: 'status_permohonan'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ];

            // DataTable
            var table = $('#empTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: false,
                deferRender: true,
                orderClasses: false,
                pageLength: 10, // Default 10 data per halaman
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                ajax: {
                    url: "{{ route('elits-permohonan-uji-klinik.data-permohonan-uji-klinik-verifikasi') }}",
                    type: "GET",
                    data: function(d) {
                        @if($isKesmas)
                            d.filter_jenis_sample = $('#filter-jenis-sample').val();
                        @else
                            d.is_filter = $('#filter-permohonan_uji_klinik').val();
                            d.filter_prolanis_gula = $('#filter-prolanis-gula').val();
                            d.filter_prolanis_urine = $('#filter-prolanis-urine').val();
                            d.filter_prolanis = $('#filter-prolanis').val();
                            d.filter_pemeriksaan_haji = $('#filter-pemeriksaan-haji').val();
                            d.filter_haji = $('#filter-haji').val();
                        @endif
                        d.date_start = $('#filter-date-start').val();
                        d.date_end = $('#filter-date-end').val();
                        @if(in_array($userLevel, ['ALAB', 'PLAB', 'ANLS']) && (in_array($kodeLab, ['KIM', 'KMA', 'FKA', 'MBI']) || $kodeLab == 'KLI'))
                            d.filter_grup = $('#filter-grup').val() || 'all';
                        @endif
                        d.status_filter = $('.tab-filter-item.active').data('status') || '';
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable AJAX Error:', xhr, error, code);
                        var message = 'Gagal memuat data verifikasi.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                        } else if (xhr.status === 500) {
                            message = 'Server error (500). Silakan refresh halaman.';
                        }
                        if (typeof swal !== 'undefined') {
                            swal('Error', message, 'error');
                        }
                    }
                },
                columns: columns,
                language: {
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Tidak ada data yang tersedia",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            table.one('draw.dt', scheduleStatistics);

            // Event handler untuk filter tanggal
            function reloadTableWithDateFilter() {
                var start = $('#filter-date-start').val();
                var end = $('#filter-date-end').val();
                if (start && end && start > end) {
                    swal('Peringatan', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', 'warning');
                    return;
                }
                table.ajax.reload();
                loadStatistics();
            }

            $('#btn-filter-date').on('click', function() {
                reloadTableWithDateFilter();
            });

            $('#btn-reset-date-filter').on('click', function() {
                $('#filter-date-start').val('');
                $('#filter-date-end').val('');
                table.ajax.reload();
                loadStatistics();
            });

            $('#filter-date-start, #filter-date-end').on('keypress', function(e) {
                if (e.which === 13) {
                    reloadTableWithDateFilter();
                }
            });

            @if($isKesmas)
            $('#filter-jenis-sample').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });
            @else
            function loadRombonganHajiOptions() {
                $.ajax({
                    url: "{{ url('elits-permohonan-uji-klinik-2/get-haji') }}",
                    type: "GET",
                    success: function(response) {
                        $('#filter-haji').empty();
                        $('#filter-haji').append('<option value="all">Semua Rombongan</option>');
                        $.each(response, function(index, item) {
                            var label = item.nama_haji || '-';
                            if (item.tgl_haji) {
                                label += ' (' + item.tgl_haji + ')';
                            }
                            $('#filter-haji').append(
                                '<option value="' + item.id_permohonan_uji_klinik_haji + '">' +
                                label + '</option>'
                            );
                        });
                    }
                });
            }

            function syncRombonganHajiVisibility() {
                var showHajiRombongan = $('#filter-permohonan_uji_klinik').val() == '3'
                    || $('#filter-pemeriksaan-haji').val() == '1';
                if (showHajiRombongan) {
                    $('#filter-haji-wrapper').show();
                    if ($('#filter-haji option').length <= 1) {
                        loadRombonganHajiOptions();
                    }
                } else {
                    $('#filter-haji-wrapper').hide();
                    $('#filter-haji').val('all');
                }
            }

            // Ketika filter klinik diubah
            $('#filter-permohonan_uji_klinik').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue == '1') {
                    // Jika "Prolanis Gula" dipilih, tampilkan dropdown gula
                    $('#filter-prolanis-gula-wrapper').show();
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-prolanis-wrapper').hide();

                    // Ambil data gula dari server menggunakan AJAX
                    $.ajax({
                        url: "{{ url('elits-permohonan-uji-klinik-2/get-prolanis-gula') }}",
                        type: "GET",
                        success: function(response) {
                            console.log(response);
                            // Kosongkan dropdown gula terlebih dahulu
                            $('#filter-prolanis-gula').empty();
                            // Tambahkan opsi default
                            $('#filter-prolanis-gula').append(
                                '<option value="all">All Prolanis Gula</option>');

                            // Tambahkan data gula ke dropdown
                            $.each(response, function(index, item) {
                                $('#filter-prolanis-gula').append('<option value="' +
                                    item.id_permohonan_uji_klinik_prolanis +
                                    '">' + item.nama_prolanis + '</option>');
                            });
                        }
                    });

                } else if (selectedValue == '4') {
                    $('#filter-prolanis-gula-wrapper').hide();
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-prolanis-wrapper').show();


                    // Ambil data all prolanis dari server menggunakan AJAX
                    $.ajax({
                        url: "{{ url('elits-permohonan-uji-klinik-2/get-prolanis') }}",
                        type: "GET",
                        success: function(response) {
                            console.log(response);
                            // Kosongkan dropdown urine terlebih dahulu
                            $('#filter-prolanis').empty();
                            // Tambahkan opsi default
                            $('#filter-prolanis').append(
                                '<option value="all">All Prolanis</option>');

                            // Tambahkan data all prolanis ke dropdown
                            $.each(response, function(index, item) {
                                $('#filter-prolanis').append('<option value="' +
                                    item.id_permohonan_uji_klinik_prolanis +
                                    '">' + item.nama_prolanis + '</option>');
                            });
                        }
                    });

                } else if (selectedValue == '2') {
                    // Jika "Prolanis Urine" dipilih, tampilkan dropdown urine
                    $('#filter-prolanis-gula-wrapper').hide(); // Sembunyikan dropdown gula
                    $('#filter-prolanis-urine-wrapper').show();
                    $('#filter-prolanis-wrapper').hide();

                    // Ambil data urine dari server menggunakan AJAX
                    $.ajax({
                        url: "{{ url('elits-permohonan-uji-klinik-2/get-prolanis-urine') }}",
                        type: "GET",
                        success: function(response) {
                            console.log(response);
                            // Kosongkan dropdown urine terlebih dahulu
                            $('#filter-prolanis-urine').empty();
                            // Tambahkan opsi default
                            $('#filter-prolanis-urine').append(
                                '<option value="all">All Prolanis Urine</option>');

                            // Tambahkan data urine ke dropdown
                            $.each(response, function(index, item) {
                                $('#filter-prolanis-urine').append('<option value="' +
                                    item.id_permohonan_uji_klinik_prolanis +
                                    '">' + item.nama_prolanis + '</option>');
                            });
                        }
                    });

                } else if (selectedValue == '3') {
                    $('#filter-prolanis-gula-wrapper').hide();
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-prolanis-wrapper').hide();
                    $('#filter-pemeriksaan-haji').val('1');
                    loadRombonganHajiOptions();

                } else {
                    // Sembunyikan dropdown prolanis jika bukan gula/urine/prolanis
                    $('#filter-prolanis-gula-wrapper').hide();
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-prolanis-wrapper').hide();
                    $('#filter-prolanis-gula').val('all');
                    $('#filter-prolanis-urine').val('all');
                    $('#filter-prolanis').val('all');
                }

                syncRombonganHajiVisibility();

                // Reload DataTable setelah filter diubah
                table.ajax.reload();
                // Reload statistics
                loadStatistics();
            });

            $('#filter-pemeriksaan-haji').on('change', function() {
                var val = $(this).val();
                if ($('#filter-permohonan_uji_klinik').val() == '3' && val != '1') {
                    // jika tipe sudah Haji, paksa pemeriksaan haji = Ya
                    $('#filter-pemeriksaan-haji').val('1');
                    loadRombonganHajiOptions();
                } else if (val == '1') {
                    loadRombonganHajiOptions();
                }
                syncRombonganHajiVisibility();
                table.ajax.reload();
                loadStatistics();
            });
            @endif

            // Reload DataTable saat filter gula diubah
            $('#filter-prolanis-gula').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });

            // Reload DataTable saat filter urine diubah
            $('#filter-prolanis-urine').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });

            // Reload DataTable saat filter urine diubah
            $('#filter-prolanis').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });

            // Reload DataTable saat filter rombongan haji diubah
            $('#filter-haji').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });

            // Event handler untuk filter grup
            @if(in_array($userLevel, ['ALAB', 'PLAB', 'ANLS']) && (in_array($kodeLab, ['KIM', 'KMA', 'FKA', 'MBI']) || $kodeLab == 'KLI'))
            $('#filter-grup').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
                loadStatistics(); // Reload statistics
            });
            @endif


            // Click handler untuk tab filter
            $('.tab-filter-item').on('click', function() {
                // Update active tab
                $('.tab-filter-item').removeClass('active');
                $(this).addClass('active');
                
                // Reload table dengan filter status
                table.ajax.reload();
            });

            var CSRF_TOKEN = $('#csrf-token').val();

            // btn payment
            $('#empTable').on('click', '.btn-payment', function(e) {
                e.preventDefault();
                var permohonan_uji_klinik_id = $(this).data('id');
                $('#form-menu').trigger('reset'); // reset form on modals

                $('#id_permohonan_uji_klinik').val(permohonan_uji_klinik_id);

                $.ajax({
                    type: "POST",
                    url: "{{ route('permohonan-uji-klinik-get-payment2') }}",
                    data: {
                        permohonan_uji_klinik_id: permohonan_uji_klinik_id,
                        _token: CSRF_TOKEN
                    },
                    dataType: "JSON",
                    success: function(data) {
                        $('[name="nota_petugas_permohonan_uji_payment_klinik"]').val(data
                            .nota_petugas);
                        $('[name="nota_namapetugas_permohonan_uji_payment_klinik"]').val(data
                            .nota_namapetugas).prop(
                            'readonly', true);
                        $('[name="nama_pasien"]').val((data.nama_pasien || '').toUpperCase()).prop('readonly', true);
                        $('[name="alamat_pasien"]').val(data.alamat_pasien).prop('readonly',
                            true);
                        $('[name="total_harga_custom"]').val(data.total_harga_custom).prop(
                            'readonly', true);
                        $('[name="total_harga"]').val(data.total_harga);

                        $('#modal-payment').modal(
                            'show'); // show bootstrap modal when complete loaded
                        $('.modal-title').text(
                            'Pelunasan Pembayaran'); // Set title to Bootstrap modal title
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        swal("Error", "Error get data from ajax!", "error");
                    }
                });
            });

            // btn payment proses
            $('#btnSave').click(function(param) {
                $('#btnSave').text('Memproses...'); //change button text
                $('#btnSave').prop('disabled', true); //set button disable

                $.ajax({
                    url: "{{ route('permohonan-uji-klinik-store-payment') }}",
                    type: "POST",
                    data: $('#form-payment').serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        $('#btnSave').text('Proses'); //change button text
                        $('#btnSave').prop('disabled', false); //set button enable

                        if (data.status == true) //if success close modal and reload ajax table
                        {
                            swal({
                                icon: "success",
                                title: "Process Success!",
                                text: data.pesan,
                            });

                            $('#form-payment').trigger('reset'); // reset form on modals
                        } else {
                            var pesan = "";
                            var data_pesan = data.pesan;
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
                                    text: data.pesan,
                                    icon: "warning"
                                });
                            }
                        }

                        $('#modal-payment').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        swal("Error!",
                            "Something is wrong when you want to save or change data. Please try again!",
                            "error");

                        $('#btnSave').text('Proses'); //change button text
                        $('#btnSave').prop('disabled', false); //set button enable
                    }
                });
            });

            $('#empTable').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + nama,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-permohonan-uji-klinik-destroy-2/' + kode,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                // document.location = '/elits-permohonan-uji-klinik';

                                                table.ajax.reload();
                                            });
                                    } else {
                                        swal("Hapus Data Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function() {
                                    swal("ERROR", "System tidak dapat menghapus data!",
                                        "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus data dibatalkan!", "error");
                        }
                    });
            });
        });
    </script>
    <script>
      $(document).ready(function () {
        $('#signOptionModal').on('show.bs.modal', function (event) {
          var link = $(event.relatedTarget).data('href');

          $('#linkTTDManual').attr('href', link + '?signoption=0');
          $('#linkTTDElektronik').attr('href', link + '?signoption=1');
        })
      })
    </script>
@endsection

