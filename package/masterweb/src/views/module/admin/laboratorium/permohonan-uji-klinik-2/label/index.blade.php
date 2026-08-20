@extends('masterweb::template.admin.layout')

@section('title')
    Print Label Permohonan Uji Klinik
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
                                <li class="breadcrumb-item active" aria-current="page"><span>Label Pasien</span></li>
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

                    <table id="table-pasien" class="table" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 10%" class="text-center">Selection</th>
                                <th>No</th>
                                <th>Nama Pasien</th>
                                <th style="width: 15%" class="text-center">Nomor Rekam Medis</th>
                                <th class="text-center">Tanggal Lahir</th>
                                {{-- <th>Jenis Pemeriksaan</th> --}}
                                <th style="width: 15%" class="text-center">Tgl Pemeriksaan</th>
                            </tr>
                        </thead>

                        <tbody id="table-body"></tbody>
                    </table>

                    <div class="row mt-3 align-items-end">
                        <div class="col-md-4">
                            <label for="label-ukuran" class="font-weight-bold mb-1">Ukuran Label</label>
                            <select id="label-ukuran" class="form-control">
                                <option value="A4">A4 (landscape)</option>
                                <option value="50x30" selected>50x30 mm</option>
                                <option value="57x30">57x30 mm</option>
                                <option value="57x40">57x40 mm</option>
                                <option value="80x80">80x80 mm</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-dark btn-icon-text btn-print mt-4">
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
            var datatable = $('#table-pasien').DataTable({
                processing: true,
                serverSide: true,
                ordering: true,
                stateSave: true,
                responsive: true,
                ajax: {
                    url: "{{ url('elits-label-permohonan-uji-klinik-2') }}",
                    type: 'GET',
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
                        data: 'nama_pasien',
                        name: 'nama_pasien'
                    },
                    {
                        data: 'no_rekammedis_pasien',
                        name: 'no_rekammedis_pasien'
                    },
                    {
                        data: 'tgllahir_pasien',
                        name: 'tgllahir_pasien'
                    },
                    // {
                    //     data: 'set_jenis_pemeriksaan',
                    //     name: 'set_jenis_pemeriksaan'
                    // },
                    {
                        data: 'tgl_pemeriksaan',
                        name: 'tgl_pemeriksaan'
                    }
                ]
            });

            $('.btn-print').on('click', function(event) {
                event.preventDefault();
                var cb_label = $(".checkbox-label:checkbox:checked");
                var id_permohonan_uji_klinik = [];
                $.each(cb_label, function(key, value) {
                    id_permohonan_uji_klinik.push(value.value);
                });
                var id_permohonan_uji_klinik_string = id_permohonan_uji_klinik.toString();

                if (id_permohonan_uji_klinik.length > 0) {
                    swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk mencetak " + id_permohonan_uji_klinik.length + " label",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((result) => {
                        if (result) {
                            var ukuran = $('#label-ukuran').val() || '50x30';
                            window.open(
                                "elits-label-permohonan-uji-klinik-2/print?permohonan_uji_klinik=" +
                                id_permohonan_uji_klinik_string + "&ukuran=" + encodeURIComponent(ukuran),
                                '_blank');
                        } else {
                            swal("Cancelled", "Print Label dibatalkan!", "error");
                        }
                    });
                } else {
                    swal("Warning", "Pilih min. 1 label untuk dicetak!", "warning");
                }
            });
        });
    </script>
@endsection
