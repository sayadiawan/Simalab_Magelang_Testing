@extends('masterweb::template.admin.layout')

@section('title')
    Parameter Jenis Satuan Management
@endsection

@section('content')
    <style>
        /* Style untuk tabel HTML di kolom Ket. Default */
        #order-listing td table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        #order-listing td table td {
            padding: 4px 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }



        #order-listing td {
            max-width: 300px;
            word-wrap: break-word;
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
                                <li class="breadcrumb-item"><a
                                        href="{{ url('/elits-parameter-satuan-klinik') }}">Laboraturium</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Parameter Jenis Satuan
                                        Management</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">


        <div class="card-body">
            <div class="d-flex">
                <div class="mr-auto p-2">
                    {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
                </div>

                <div class="p-2">
                    <a href="{{ route('elits-parameter-satuan-klinik.create') }}">

                        <button type="button" class="btn btn-info btn-icon-text">
                            Tambah Data
                            <i class="fa fa-plus btn-icon-append"></i>
                        </button>
                    </a>
                </div>

                <div class="p-2">
                    <a href="{{ route('parameter-satuan-klinik.reorder-page') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-sort mr-1"></i>Urutkan
                    </a>
                </div>


            </div>

            <div class="row">

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-12">
                    <div class="table-responsive">
                        <table id="order-listing" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Parameter Jenis</th>
                                    <th>Nama Parameter Satuan</th>
                                    <th>Metode</th>
                                    <th>Loinc</th>
                                    <th>Harga</th>
                                    <th>Ket. Default</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="sortable-list">
                                @php
                                    $no = 1;
                                @endphp

                                @foreach ($data as $item)
                                    <tr data-id="{{ $item->id_parameter_satuan_klinik }}">
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</td>
                                        <td>{{ $item->name_parameter_satuan_klinik }}</td>
                                        <td>{{ $item->metode_parameter_satuan_klinik }}</td>
                                        <td>{{ $item->loinc_parameter_satuan_klinik }}</td>
                                        <td>Rp.
                                            {{ number_format($item->harga_satuan_parameter_satuan_klinik, 2, ',', '.') }}
                                        </td>
                                        <td>{!! nilaiBakuMutuForDisplay($item->ket_default_parameter_satuan_klinik ?? '') !!}</td>

                                        <td>
                                            <a
                                                href="{{ route('elits-parameter-satuan-klinik.show', [$item->id_parameter_satuan_klinik]) }}">
                                                <button type="button" class="btn btn-outline-info btn-rounded btn-icon">
                                                    <i class="fas fa-info"></i>
                                                </button>
                                            </a>

                                            <a
                                                href="{{ route('elits-parameter-satuan-klinik.edit', [$item->id_parameter_satuan_klinik]) }}">
                                                <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </a>

                                            <a href="#hapus" class="btn btn-outline-danger btn-rounded btn-icon btn-hapus"
                                                data-id="{{ $item->id_parameter_satuan_klinik }}"
                                                data-name="{{ $item->name_parameter_satuan_klinik }}"><i
                                                    class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script>
        document.write('<scr' + 'ipt src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></scr' + 'ipt>');
        $(function() {
            var reorderEnabled = false;

            function enableSortable() {
                $('#sortable-list').sortable({
                    helper: function(e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            $(this).width($originals.eq(index).width());
                        });
                        return $helper;
                    }
                }).disableSelection();
            }

            $('#btn-reorder').on('click', function() {
                reorderEnabled = !reorderEnabled;
                if (reorderEnabled) {
                    enableSortable();
                    $('#btn-save-order').show();
                    $('#btn-reorder').removeClass('btn-outline-secondary').addClass('btn-secondary').text(
                        'Selesai Urutkan');
                } else {
                    try {
                        $('#sortable-list').sortable('destroy');
                    } catch (e) {}
                    $('#btn-save-order').hide();
                    $('#btn-reorder').removeClass('btn-secondary').addClass('btn-outline-secondary').html(
                        '<i class="fa fa-sort mr-1"></i>Urutkan');
                }
            });

            $('#btn-save-order').on('click', function() {
                var ids = [];
                $('#sortable-list tr').each(function() {
                    ids.push($(this).data('id'));
                });
                $.post("{{ route('parameter-satuan-klinik.reorder') }}", {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                }, function(resp) {
                    if (resp.status) {
                        swal('Success', resp.pesan, 'success');
                    } else {
                        swal('Error', resp.pesan || 'Gagal', 'error');
                    }
                });
            });

            $('#order-listing').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var name = $(this).data('name');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + name,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-parameter-satuan-klinik-destroy/' + kode,
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
                                                    '/elits-parameter-satuan-klinik';
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
        })
    </script>

    <script>
        (function() {
            function persistHandlers(table) {
                if (!table) return;
                table.on('draw.dt length.dt page.dt search.dt', function() {
                    try {
                        var info = table.page.info();
                        localStorage.setItem('psk.list.search', table.search() || '');
                        localStorage.setItem('psk.list.length', String(info.length || 25));
                        localStorage.setItem('psk.list.page', String(info.page || 0));
                    } catch (e) {}
                });
            }

            function applyStateTo(table) {
                var savedSearch = localStorage.getItem('psk.list.search') || '';
                var savedLen = parseInt(localStorage.getItem('psk.list.length') || '25', 10);
                var savedPage = parseInt(localStorage.getItem('psk.list.page') || '0', 10);
                if (!isNaN(savedLen)) table.page.len(savedLen);
                if (savedSearch) table.search(savedSearch);
                table.draw(false);
                if (!isNaN(savedPage)) table.page(savedPage).draw(false);
                persistHandlers(table);
            }

            function initDataTable() {
                if (!$.fn.DataTable) return;
                var $tbl = $('#order-listing');
                if ($.fn.dataTable.isDataTable($tbl)) {
                    var table = $tbl.DataTable();
                    applyStateTo(table);
                } else {
                    // Jangan lakukan init kedua; tunggu inisialisasi dari theme lalu terapkan state
                    $tbl.one('init.dt', function() {
                        var table = $tbl.DataTable();
                        applyStateTo(table);
                    });
                }
            }

            if (typeof $.fn.DataTable === 'undefined') {
                var head = document.getElementsByTagName('head')[0];
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.8/css/jquery.dataTables.min.css';
                head.appendChild(link);

                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js';
                script.onload = initDataTable;
                document.body.appendChild(script);
            } else {
                initDataTable();
            }
        })();
    </script>
@endsection
