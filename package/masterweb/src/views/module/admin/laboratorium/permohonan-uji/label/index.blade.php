@extends('masterweb::template.admin.layout')

@section('title')
    Print Label Permohonan Uji
@endsection

@section('content')
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
                                <li class="breadcrumb-item active" aria-current="page"><span>Label Sampel</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">

                    <table id="table-permohonan-uji" class="table" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 10%" class="text-center">Selection</th>
                                <th>No</th>
                                <th>Kode Permohonan</th>
                                <th>Nama Customer</th>
                                <th class="text-center">Tanggal Permohonan</th>
                            </tr>
                        </thead>

                        <tbody id="table-body"></tbody>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-dark btn-icon-text btn-print">
                                Print Selected Label
                                <i class="fa fa-print btn-icon-append"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var datatable = $('#table-permohonan-uji').DataTable({
                processing: true,
                serverSide: true,
                ordering: true,
                stateSave: true,
                responsive: true,
                ajax: {
                    url: "{{ url('elits-label-permohonan-uji') }}",
                    type: 'GET',
                    data: function(d) {
                        d.search = $('input[type="search"]').val()
                    },
                    error: function(xhr, error, code) {
                        console.log("error");
                    }
                },
                columns: [{
                        data: 'set_checkbox',
                        name: 'set_checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code_permohonan_uji',
                        name: 'code_permohonan_uji'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'date_permohonan_uji',
                        name: 'date_permohonan_uji'
                    }
                ],
                columnDefs: [
                    { targets: 2, visible: false }
                ]
            });

            $('.btn-print').on('click', function(event) {
                event.preventDefault();
                var cb_label = $(".permohonan-uji-checkbox:checkbox:checked");
                var id_permohonan_uji = [];
                $.each(cb_label, function(key, value) {
                    id_permohonan_uji.push(value.value);
                });
                var id_permohonan_uji_string = id_permohonan_uji.toString();

                if (id_permohonan_uji.length > 0) {
                    swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk mencetak label sampel dari " + id_permohonan_uji.length +
                            " permohonan uji",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((result) => {
                        if (result) {
                            window.open(
                                "elits-label-permohonan-uji/print?permohonan_uji=" +
                                id_permohonan_uji_string, '_blank');
                        } else {
                            swal("Cancelled", "Print Label dibatalkan!", "error");
                        }
                    });
                } else {
                    swal("Warning", "Pilih min. 1 permohonan uji untuk dicetak!", "warning");
                }
            });
        });
    </script>
@endsection




