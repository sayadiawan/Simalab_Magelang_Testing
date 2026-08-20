@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik Parameter
@endsection


@section('content')
    <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
    <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />


    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan Uji Klinik
                                        Management</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page">
                                    <span>Permohonan Uji Paket Klinik</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Permohonan Uji Parameter Klinik</h4>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-2 float-right">
                        @php
                            $backUrl = !empty($item->id_permohonan_uji_klinik_haji)
                                ? route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $item->id_permohonan_uji_klinik_haji)
                                : url('/elits-permohonan-uji-klinik-2');
                        @endphp
                        <a href="{{ $backUrl }}">

                            <button type="button" class="btn btn-default btn-icon-text">
                                <i class="fa fa-arrow-left btn-icon-append"></i>
                                Kembali
                            </button>
                        </a>
                    </div>
                </div>
            </div>

            <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="250px">No. Register</th>
                                <td>{{ $item->noregister_permohonan_uji_klinik }}</td>

                                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik"
                                    value="{{ $item->id_permohonan_uji_klinik }}" readonly>
                            </tr>

                            <tr>
                                <th width="250px">No. Rekam Medis</th>
                                <td>
                                    {{ $item->getNoRekamMedis() }}
                                </td>
                            </tr>

                            <tr>
                                <th width="250px">Tgl. Register</th>
                                <td>{{ $tgl_register }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Nama Pasien</th>
                                <td style="text-transform: uppercase;">{{ $item->pasien->nama_pasien }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Umur/Jenis Kelamin</th>
                                <td>
                                    {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                                    / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-fill-warning mb-0" role="alert">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong> Data yang Anda tambahkan atau diubah akan mempengaruhi di laporan
                        pastikan
                        lakukan koreksi sebelum ke proses Analis.
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card" style="border-left: 4px solid #667eea;">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fa fa-calculator mr-2"></i>Rincian Biaya</h5>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Biaya Parameter:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <strong id="display_biaya_parameter_total">Rp. 0</strong>
                                </div>
                            </div>
                            <div class="row mb-2" id="biaya_pengambilan_row" style="display: none;">
                                <div class="col-6">
                                    <strong><i class="fa fa-home mr-1"></i>Biaya Pengambilan Sampel:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <strong id="display_biaya_pengambilan_total">Rp. 0</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <h4><strong>Total:</strong></h4>
                                </div>
                                <div class="col-6 text-right">
                                    <h4><strong>Rp. <span id="count-harga-total"></span></strong></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                </div>

                <div class="col-md-6">
                    <div class="mb-2 float-right text-right">
                        <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-klinik-parameter', $id) }}">

                            <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();"
                                {{-- {{ isset($payment) && $payment ? 'disabled' : '' }} --}}>
                                Edit Data
                                <i class="fa fa-plus btn-icon-append"></i>
                            </button>
                        </a>

                        @if ($item->status_permohonan_uji_klinik == 'SELESAI')
                            <small class="d-block text-muted mt-2">
                                <i class="fa fa-info-circle mr-1"></i>
                                Pemeriksaan sudah selesai. Menambah parameter baru akan mengembalikan status
                                ke Analis agar hasilnya bisa diisi.
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <table id='table-parameter' class="table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No</th>
                                {{-- <th style="width: 30%">Jenis Parameter</th> --}}
                                <th style="width: 20%">Parameter Paket</th>
                                <th style="width: 20%">Harga</th>
                                {{-- <th style="width: 15%">Aksi</th> --}}
                            </tr>
                        </thead>

                        <tbody id="tabel-body">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
        <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

        <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
            integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
        </script>

        <script>
            $(document).ready(function() {
                var table = $('#table-parameter').DataTable({
                    processing: true,
                    serverSide: true,
                    // Jangan simpan state pagination: default 10 baris menyembunyikan paket ke-11+ (mis. Kreatinin).
                    stateSave: false,
                    responsive: true,
                    ordering: false,
                    order: [],
                    pageLength: -1,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
                    ajax: {
                        url: "/elits-permohonan-uji-klinik-2/permohonan-uji-klinik-parameter/" +
                            "{{ $id }}",
                        type: "GET",
                        data: function(d) {
                            d.search = $('input[type="search"]').val()
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        // {
                        //   data: 'parameter_jenis_klinik',
                        //   name: 'parameter_jenis_klinik'
                        // },
                        {
                            data: 'paket',
                            name: 'paket'
                        },
                        {
                            data: 'harga_permohonan_uji_paket_klinik',
                            name: 'harga_permohonan_uji_paket_klinik'
                        },
                        // {
                        //     data: 'action',
                        //     name: 'action',
                        //     orderable: false,
                        //     searchable: false
                        // }
                    ]
                });

                table.on('draw', function() {
                    $('[data-toggle="tooltip"]').tooltip();
                });

                var CSRF_TOKEN = $('#csrf-token').val();

                $('input[type="search"]').on('keyup', function() {
                    table.draw();

                    countHargaTotal($('input[type="search"]').val());
                });

                countHargaTotal($('input[type="search"]').val());

                function formatRupiah(angka) {
                    var number_string = angka.toString(),
                        split = number_string.split('.'),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                    return rupiah;
                }

                function updateDisplay(total_biaya_parameter, biaya_pengambilan_sampel, total) {
                    // Update biaya parameter
                    $('#display_biaya_parameter_total').text('Rp. ' + formatRupiah(total_biaya_parameter));
                    
                    // Update biaya pengambilan sampel
                    if (biaya_pengambilan_sampel > 0) {
                        $('#display_biaya_pengambilan_total').text('Rp. ' + formatRupiah(biaya_pengambilan_sampel));
                        $('#biaya_pengambilan_row').show();
                    } else {
                        $('#biaya_pengambilan_row').hide();
                    }
                    
                    // Update total
                    $('#count-harga-total').text(formatRupiah(total));
                }

                function countHargaTotal(search = null) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('elits-permohonan-uji-klinik-2.get-harga-total-permohonan-uji-klinik-parameter') }}",
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        data: {
                            _token: $('#csrf-token').val(),
                            search: search,
                            id_permohonan_uji_klinik: "{{ $id }}"
                        },
                        dataType: "JSON",
                        success: function(response) {
                            // Handle response (bisa berupa object atau number untuk backward compatibility)
                            var total_biaya_parameter = 0;
                            var biaya_pengambilan_sampel = 0;
                            var total = 0;

                            if (typeof response === 'object' && response.total !== undefined) {
                                // New format with details
                                total_biaya_parameter = response.total_biaya_parameter || 0;
                                biaya_pengambilan_sampel = response.biaya_pengambilan_sampel || 0;
                                total = response.total || 0;
                            } else {
                                // Old format (number only) - backward compatibility
                                // Assume the total already includes biaya_pengambilan_sampel
                                // We'll need to get biaya_pengambilan_sampel from the permohonan data
                                total = parseFloat(response) || 0;
                                // For now, assume no biaya_pengambilan_sampel if response is just a number
                                // This will be handled by the new response format
                                total_biaya_parameter = total;
                                biaya_pengambilan_sampel = 0;
                            }

                            updateDisplay(total_biaya_parameter, biaya_pengambilan_sampel, total);
                        },
                        error: function() {
                            swal("ERROR", "System tidak dapat mengambil data total harga!", "error");
                        }
                    });
                }

                $('#table-parameter').on('click', '.btn-hapus', function() {
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
                                    url: '/elits-permohonan-uji-2/destroy-permohonan-uji-klinik-parameter/' +
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
    @endsection
