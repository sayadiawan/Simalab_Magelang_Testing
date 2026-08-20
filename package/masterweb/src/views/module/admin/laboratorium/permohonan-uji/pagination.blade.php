@extends('masterweb::template.admin.layout')

@section('title')
    Permohonan Uji Management
@endsection

@section('content')
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
                        <a href="{{ url('/elits-permohonan-uji') }}">Laboraturium</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Permohonan Uji Management
                    </li>
                </ol>
            </nav>

            <!-- Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Header dengan tombol tambah -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-0">Daftar Permohonan Uji</h4>
                            <p class="text-muted mb-0">Kelola data permohonan uji laboratorium</p>
                        </div>
                        @if (getAction('create'))
                            <a href="{{ route('elits-permohonan-uji.create') }}" onclick="localStorage.clear();">
                                <button type="button" class="btn btn-info btn-icon-text">
                                    <i class="fa fa-plus btn-icon-prepend"></i>
                                    Tambah Data
                                </button>
                            </a>
                        @endif
                    </div>

                    <!-- Alert Messages -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Filter tanggal pengiriman sampel (tb_samples.date_sending) --}}
                    <div class="card border mb-3" style="background: #f8fafc;">
                        <div class="card-body py-3">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="mb-1 small font-weight-bold text-muted" for="filter-date-start">
                                        <i class="fa fa-calendar mr-1"></i> Tanggal Mulai
                                    </label>
                                    <input type="date" id="filter-date-start" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="mb-1 small font-weight-bold text-muted" for="filter-date-end">
                                        <i class="fa fa-calendar-check mr-1"></i> Tanggal Akhir
                                    </label>
                                    <input type="date" id="filter-date-end" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="d-flex flex-wrap" style="gap: 8px;">
                                        <button type="button" class="btn btn-primary btn-sm" id="btn-filter-date">
                                            <i class="fa fa-filter mr-1"></i> Terapkan Filter
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-reset-date-filter">
                                            <i class="fa fa-times mr-1"></i> Reset
                                        </button>
                                        <small class="text-muted align-self-center ml-md-2">
                                            Rentang tanggal pengiriman sampel
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="mb-2 small text-muted">
                        <span class="mr-2"><span class="badge badge-danger">Belum diisi</span> belum ada nomor lab</span>
                        <span class="mr-2"><span class="badge badge-warning text-dark">Kurang</span> sebagian belum lengkap</span>
                        <span><span class="badge badge-success">Lengkap</span> semua kombinasi jenis sampel × lab sudah punya nomor</span>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table id='empTable' class="table table-striped table-bordered smt-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Pelanggan</th>
                                    <th>Pemeriksaan</th>
                                    <th width="15%">No Sampel</th>
                                    <th width="12%">Jenis Sample</th>
                                    <th width="12%" class="text-center">Nomor Lab</th>
                                    <th width="12%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
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

    <div class="modal fade" id="modalPermohonanUjiPayment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nota Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formPermohonanUjiPayment" method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="biaya_tindakan_rectal_swab" id="pay-biaya-rectal" value="0">
                        <p id="pay-total-text" class="mb-3"></p>
                        <div class="form-group">
                            <label for="pay-recipient">Telah Diterima Dari</label>
                            <input type="text" class="form-control" name="recipient-name" id="pay-recipient" required>
                        </div>
                        <div class="form-group">
                            <label for="pay-address">Alamat</label>
                            <textarea class="form-control" name="address" id="pay-address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="pay-date">Tanggal Bayar</label>
                            <input type="date" class="form-control" name="tanggal_bayar" id="pay-date" required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="pay-amount">Jumlah Terbayar</label>
                            <input type="number" class="form-control" name="amount" id="pay-amount"
                                placeholder="Kosongi jika terbayar lunas">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="payment_submit" value="partial" class="btn btn-primary">Belum Lunas</button>
                        <button type="submit" name="payment_submit" value="lunas" class="btn btn-success">LUNAS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditNota" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Nota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditNota" method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-nota-diterima-dari">Telah Diterima Dari</label>
                            <input type="text" class="form-control" name="nota_diterima_dari" id="edit-nota-diterima-dari">
                        </div>
                        <div class="form-group mb-0">
                            <label for="edit-nota-alamat">Alamat</label>
                            <textarea class="form-control" name="nota_address_from" id="edit-nota-alamat" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <style>
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

        /*
         * Satu overflow horizontal di wrapper (tanpa DataTables scrollX) agar tidak
         * menduplikasi tabel dan mengurangi Paint/Layout.
         */
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

        /* DataTables Bootstrap4: inset box-shadow per cell = Paint mahal */
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
        }

        #empTable tbody td.col-text-truncate {
            overflow: hidden;
            max-width: 0;
        }

        #empTable .cell-truncate {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
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

        /* Styling khusus untuk Surat Perintah Sampling */
        #empTable .dropdown-item[style*="background: #fff3cd"] {
            background-color: #fff3cd !important;
            border-left: 3px solid #ffc107;
            margin: 4px 8px;
            border-radius: 4px;
        }

        #empTable .dropdown-item[style*="background: #fff3cd"]:hover {
            background-color: #ffe69c !important;
        }

        /* DataTables scrollbar styling */
        .dataTables_scrollHead {
            overflow-x: auto;
        }

        /*
         * Satu baris data membuat tinggi scroll body sangat pendek sehingga menu
         * dropdown terpotong dan tidak bisa discroll. Min-height memberi ruang di
         * dalam area scroll agar menu terlihat utuh.
         */
        .dataTables_scrollBody {
            overflow-x: auto !important;
            min-height: 320px;
        }

        .dataTables_scrollHead table,
        .dataTables_scrollBody table {
            width: 100% !important;
        }

        /* Loading indicator */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            z-index: 1000;
        }

        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.justify-content-between > div {
                width: 100%;
            }

            #empTable {
                font-size: 0.8rem;
            }

            #empTable thead th,
            #empTable tbody td {
                padding: 8px 4px;
            }
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
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            var paymentUrlBase = @json(url('elits-permohonan-uji/payment'));
            var editNotaUrlBase = @json(url('elits-permohonan-uji/edit-nota'));
            var table = $('#empTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: false,
                autoWidth: false,
                deferRender: true,
                orderClasses: false,
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Memuat...</span>',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data yang tersedia",
                    zeroRecords: "Tidak ada data yang cocok dengan pencarian"
                },
                ajax: {
                    url: "{{ route('elits-permohonan-uji.pagination') }}",
                    type: "GET",
                    data: function(d) {
                        d.date_start = $('#filter-date-start').val();
                        d.date_end = $('#filter-date-end').val();
                    }
                },
                columns: [{
                        data: 'nomer',
                        name: 'nomer',
                        width: '5%',
                        className: 'text-center'
                    },
                    {
                        data: 'customer_permohonan_uji',
                        name: 'customer_permohonan_uji',
                        width: '14%',
                        className: 'col-text-truncate'
                    },
                    {
                        data: 'pemeriksaan',
                        name: 'pemeriksaan',
                        width: '22%',
                        className: 'col-text-truncate'
                    },
                    {
                        data: 'num_samples',
                        name: 'num_samples',
                        width: '18%',
                        className: 'col-text-truncate text-center'
                    },
                    {
                        data: 'count_sample_type',
                        name: 'count_sample_type',
                        width: '12%',
                        className: 'col-text-truncate text-center'
                    },
                    {
                        data: 'nomer_lab_status',
                        name: 'nomer_lab_status',
                        orderable: false,
                        searchable: false,
                        width: '12%',
                        className: 'text-center'
                    },
                    {
                        data: 'status_pembayaran',
                        name: 'status_pembayaran',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
            });

            $('#empTable').on('click', '.btn-open-payment', function() {
                var btn = $(this);
                var permohonanId = btn.data('id');

                $('#formPermohonanUjiPayment').attr('action', paymentUrlBase + '/' + permohonanId);
                $('#pay-biaya-rectal').val(btn.data('biaya-rectal') || 0);
                $('#pay-total-text').html('Total yang harus dibayar: <b>' + btn.data('total-label') + '</b>');
                $('#pay-recipient').val(btn.data('customer-name') || '');
                $('#pay-address').val(btn.data('customer-address') || '');
                $('#pay-date').val(btn.data('tanggal-bayar') || '');
                $('#pay-amount').val('');
                $('#modalPermohonanUjiPayment').modal('show');
            });

            $('#empTable').on('click', '.btn-edit-nota', function(e) {
                e.preventDefault();
                var btn = $(this);
                var permohonanId = btn.data('id');

                $('#formEditNota').attr('action', editNotaUrlBase + '/' + permohonanId);
                $('#edit-nota-diterima-dari').val(btn.data('diterima-dari') || '');
                $('#edit-nota-alamat').val(btn.data('alamat') || '');
                $('#modalEditNota').modal('show');
            });

            function reloadTableWithDateFilter() {
                var start = $('#filter-date-start').val();
                var end = $('#filter-date-end').val();
                if (start && end && start > end) {
                    swal('Peringatan', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.', 'warning');
                    return;
                }
                table.ajax.reload();
            }

            $('#btn-filter-date').on('click', function() {
                reloadTableWithDateFilter();
            });

            $('#btn-reset-date-filter').on('click', function() {
                $('#filter-date-start').val('');
                $('#filter-date-end').val('');
                table.ajax.reload();
            });

            $('#filter-date-start, #filter-date-end').on('keypress', function(e) {
                if (e.which === 13) {
                    reloadTableWithDateFilter();
                }
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
                                url: '/elits-permohonan-uji/elits-permohonan-uji-destroy/' +
                                    kode,
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
                                                document.location =
                                                    '/elits-permohonan-uji';
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
@endsection
