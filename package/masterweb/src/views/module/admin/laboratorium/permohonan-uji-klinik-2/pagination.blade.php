@extends('masterweb::template.admin.layout')

@section('title')
    Permohonan Uji Klinik Management
@endsection

@section('content')
    <style>
        .pointer {
            cursor: pointer;
        }

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* Responsive Table Styling */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Styling untuk mobile devices */
        @media (max-width: 768px) {
            .card-body {
                padding: 0.5rem !important;
            }

            table.dataTable thead th,
            table.dataTable tbody td {
                font-size: 12px;
                padding: 8px 4px !important;
            }

            .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
            }

            .dataTables_wrapper .dataTables_length select {
                width: 60px;
            }

            /* Make action buttons stack vertically on mobile */
            .btn-group-vertical {
                display: flex;
                flex-direction: column;
            }

            .btn-group-vertical .btn {
                width: 100%;
                margin-bottom: 2px;
            }
        }

        /* DataTables responsive behavior */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            background-color: #4CAF50;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th.dtr-control:before {
            background-color: #f44336;
        }

        /* Better spacing for filter dropdowns on mobile */
        @media (max-width: 576px) {
            .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>

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
                                <li class="breadcrumb-item"><a
                                        href="{{ url('/elits-permohonan-uji-klinik-2') }}">Laboraturium</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji Klinik
                                        Management</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (session('error-bsre'))
        <div class="col-12">
            <div class="alert alert-danger">
                {{ session('error-bsre') }}
            </div>
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
    <div class="card">
        <div class="card-body">
            @if (getAction('create'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-2 float-right">
                            <a href="{{ route('elits-permohonan-uji-klinik-2.create') }}">

                                <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();">
                                    Tambah Data
                                    <i class="fa fa-plus btn-icon-append"></i>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-3 mb-2">
                    <select id="filter-permohonan_uji_klinik" class="form-control">
                        <option value="all">All</option>
                        <option value="0">Permohonan Uji Klinik</option>
                        <option value="4">Permohonan Uji Klinik Prolanis</option>
                        <option value="1">Permohonan Uji Klinik Prolanis Gula</option>
                        <option value="2">Permohonan Uji Klinik Prolanis Urine</option>
                        <option value="3">Permohonan Uji Klinik Haji</option>
                    </select>
                </div>

                <!-- Dropdown tambahan untuk filter prolanis -->
                <div class="col-3 mb-2" id="filter-prolanis-wrapper" style="display: none;">
                    <select id="filter-prolanis" class="form-control">
                        <option value="all">All Prolanis</option>
                        <!-- Data gula akan dimasukkan di sini dari AJAX -->
                    </select>
                </div>

                <!-- Dropdown tambahan untuk filter prolanis gula -->
                <div class="col-3 mb-2" id="filter-prolanis-gula-wrapper" style="display: none;">
                    <select id="filter-prolanis-gula" class="form-control">
                        <option value="all">All Prolanis Gula</option>
                        <!-- Data gula akan dimasukkan di sini dari AJAX -->
                    </select>
                </div>

                <!-- Dropdown tambahan untuk filter prolanis urine -->
                <div class="col-3 mb-2" id="filter-prolanis-urine-wrapper" style="display: none;">
                    <select id="filter-prolanis-urine" class="form-control">
                        <option value="all">All Prolanis Urine</option>
                        <!-- Data urine akan dimasukkan di sini dari AJAX -->
                    </select>
                </div>

                <!-- Dropdown tambahan untuk filter haji -->
                <div class="col-3 mb-2" id="filter-haji-wrapper" style="display: none;">
                    <select id="filter-haji" class="form-control">
                        <option value="all">All Haji</option>
                        <!-- Data haji akan dimasukkan di sini dari AJAX -->
                    </select>
                </div>
                @if (session('status'))
                    <div class="col-12">
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="col-12">
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="col-12">
                    <div class="table-responsive">
                        <table id='empTable' class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Register</th>
                                    <th>Tipe Pemeriksaan</th>
                                    <th>Nama Pasien</th>
                                    {{-- <th>Tanggal Pengambilan</th> --}}
                                    <th>Status Proses</th>
                                    <th>Status Pembayaran</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="tabel-body">

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

    {{--    Modal Option SIGN --}}
    <div class="modal fade" id="signOptionModal" tabindex="-1" aria-labelledby="signOptionTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signOptionTitle">Pilih metode tanda tangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agendaNumber">Nomor Agenda</label>
                        <input type="text" class="form-control" id="agendaNumber" name="agenda"
                            placeholder="Masukkan nomor agenda">
                    </div>
                </div>
                <div class="d-flex mx-auto m-2 justify-content-around">
                    <a id="linkTTDManual" href="" target="_blank">
                        <button class="btn text-center m-2 p-2 sign-opt">
                            <img src="{{ asset('assets/admin/images/sign-icon.png') }}" width="80" height="80">
                            <h5 class="mt-2">Tanda Tangan Manual</h5>
                        </button>
                    </a>
                    <a id="linkTTDElektronik" href="" target="_blank">
                        <button class="btn text-center m-2 p-2 sign-opt">
                            <img src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" width="80"
                                height="80">
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
        $(document).ready(function() {
            // DataTable
            var table = $('#empTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                scrollX: true,
                autoWidth: false,
                language: {
                    processing: "Memproses...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                ajax: {
                    url: "{{ route('elits-permohonan-uji-klinik-2.data-permohonan-uji-klinik') }}",
                    type: "GET",
                    data: function(d) {
                        d.search = $('input[type="search"]').val()
                        d.is_filter = $('#filter-permohonan_uji_klinik').val();
                        d.filter_prolanis_gula = $('#filter-prolanis-gula').val();
                        d.filter_prolanis_urine = $('#filter-prolanis-urine').val();
                        d.filter_prolanis = $('#filter-prolanis').val();
                        d.filter_haji = $('#filter-haji').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'noregister_permohonan_uji_klinik',
                        name: 'noregister_permohonan_uji_klinik',
                        responsivePriority: 2
                    },
                    {
                        data: 'prolanis',
                        name: 'prolanis',
                        responsivePriority: 4
                    },
                    {
                        data: 'nama_pasien',
                        name: 'nama_pasien',
                        responsivePriority: 3
                    },
                    {
                        data: 'status_permohonan',
                        name: 'status_permohonan',
                        responsivePriority: 5
                    },
                    {
                        data: 'status_pembayaran',
                        name: 'status_pembayaran',
                        responsivePriority: 6
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
                    }
                ]
            });

            table.on('draw', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            // //filter prolanis gula
            // $('#filter-permohonan_uji_klinik').on('change', function() {
            //     table.ajax.reload(); // Refresh DataTable
            // });

            // Ketika filter klinik diubah
            $('#filter-permohonan_uji_klinik').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue == '1') {
                    // Jika "Prolanis Gula" dipilih, tampilkan dropdown gula
                    $('#filter-prolanis-gula-wrapper').show();
                    $('#filter-prolanis-urine-wrapper').hide(); // sembunyikan urine
                    $('#filter-haji-wrapper').hide(); // sembunyikan urine
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
                    $('#filter-haji-wrapper').hide();
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
                    $('#filter-haji-wrapper').hide();
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
                    // Jika "Prolanis Urine" dipilih, tampilkan dropdown urine
                    $('#filter-prolanis-gula-wrapper').hide(); // Sembunyikan dropdown gula
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-haji-wrapper').show();
                    $('#filter-prolanis-wrapper').hide();


                    // Ambil data urine dari server menggunakan AJAX
                    $.ajax({
                        url: "{{ url('elits-permohonan-uji-klinik-2/get-haji') }}",
                        type: "GET",
                        success: function(response) {
                            // Kosongkan dropdown urine terlebih dahulu
                            $('#filter-haji').empty();
                            // Tambahkan opsi default
                            $('#filter-haji').append('<option value="all">All Haji</option>');

                            // Tambahkan data urine ke dropdown
                            $.each(response, function(index, item) {
                                $('#filter-haji').append('<option value="' + item
                                    .id_permohonan_uji_klinik_haji + '">' + item
                                    .nama_haji + '</option>');
                            });
                        }
                    });

                } else {
                    // Sembunyikan kedua dropdown jika bukan gula atau urine yang dipilih
                    $('#filter-prolanis-gula-wrapper').hide();
                    $('#filter-prolanis-urine-wrapper').hide();
                    $('#filter-prolanis-wrapper').hide();
                    $('#filter-haji-wrapper').hide();
                    $('#filter-prolanis-gula').val('all'); // Reset nilai filter gula
                    $('#filter-prolanis-urine').val('all'); // Reset nilai filter urine
                    $('#filter-prolanis').val('all');
                    $('#filter-haji').val('all'); // Reset nilai filter urine
                }

                // Reload DataTable setelah filter diubah
                table.ajax.reload();
            });

            // Reload DataTable saat filter gula diubah
            $('#filter-prolanis-gula').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
            });

            // Reload DataTable saat filter urine diubah
            $('#filter-prolanis-urine').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
            });

            // Reload DataTable saat filter urine diubah
            $('#filter-prolanis').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
            });

            // Reload DataTable saat filter urine diubah
            $('#filter-haji').on('change', function() {
                table.ajax.reload(); // Refresh DataTable
            });

            // datatables responsive
            new $.fn.dataTable.FixedHeader(table);

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
        $(document).ready(function() {
            function refreshSignLinks(baseLink) {
                var agenda = encodeURIComponent($('#agendaNumber').val() || '');
                var agendaPart = agenda ? ('&agenda=' + agenda) : '';
                $('#linkTTDManual').attr('href', baseLink + '?signoption=0' + agendaPart);
                $('#linkTTDElektronik').attr('href', baseLink + '?signoption=1' + agendaPart);
            }

            var currentBaseLink = '';
            $('#signOptionModal').on('show.bs.modal', function(event) {
                currentBaseLink = $(event.relatedTarget).data('href');
                refreshSignLinks(currentBaseLink);
            });

            $('#agendaNumber').on('input change', function() {
                if (currentBaseLink) {
                    refreshSignLinks(currentBaseLink);
                }
            });
        })
    </script>
@endsection
