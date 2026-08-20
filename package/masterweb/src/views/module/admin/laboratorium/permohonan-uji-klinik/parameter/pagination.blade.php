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
                  <a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                  <span>permohonan uji paket klinik</span>
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
      <h4>Permohonan Uji Klinik Parameter</h4>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="col-md-12">
          <div class="mb-2 float-right">
            <a href="{{ url('/elits-permohonan-uji-klinik') }}">

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
                <td>{{ $item->pasien->nama_pasien }}</td>
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
          <div class="alert alert-fill-warning" role="alert">
            <i class="fa fa-exclamation-triangle"></i>

            <strong>Perhatian!</strong> Data yang Anda tambahkan atau diubah akan mempengaruhi di laporan pastikan
            lakukan koreksi sebelum ke proses Analis.
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <div class="col-md-6">
          <h3>Total: Rp. <span id="count-harga-total"></span></h3>

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
          <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        </div>

        @if ($item->status_permohonan_uji_klinik != 'SELESAI')
          <div class="col-md-6">
            <div class="mb-2 float-right">
              <a href="{{ route('elits-permohonan-uji-klinik.create-permohonan-uji-klinik-parameter', $id) }}">

                <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();">
                  Tambah Data
                  <i class="fa fa-plus btn-icon-append"></i>
                </button>
              </a>
            </div>
          </div>
        @endif
      </div>

      <div class="row">
        <div class="col-12">
          <table id='table-parameter' class="table">
            <thead>
              <tr>
                <th style="width: 10%">No</th>
                <th style="width: 30%">Jenis Parameter</th>
                <th style="width: 20%">Status Paket</th>
                <th style="width: 25%">Harga</th>
                <th style="width: 15%">Aksi</th>
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
      integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

    <script>
      $(document).ready(function() {
        var table = $('#table-parameter').DataTable({
          processing: true,
          serverSide: true,
          stateSave: true,
          responsive: true,
          ajax: {
            url: "/elits-permohonan-uji-klinik/permohonan-uji-klinik-parameter/" + "{{ $id }}",
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
            {
              data: 'parameter_jenis_klinik',
              name: 'parameter_jenis_klinik'
            },
            {
              data: 'type_permohonan_uji_paket_klinik',
              name: 'type_permohonan_uji_paket_klinik'
            },
            {
              data: 'total_harga',
              name: 'total_harga'
            },
            {
              data: 'action',
              name: 'action',
              orderable: false,
              searchable: false
            }
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

        function countHargaTotal(search = null) {
          $.ajax({
            type: "POST",
            url: "{{ route('elits-permohonan-uji-klinik.get-harga-total-permohonan-uji-klinik-parameter') }}",
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
              // $('#count-harga-total').text(response);

              $('#count-harga-total').text(parseFloat(response, 10).toFixed(2).replace(
                  /(\d)(?=(\d{3})+\.)/g, "$1,")
                .toString());
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
                  url: '/elits-permohonan-uji/destroy-permohonan-uji-klinik-parameter/' + kode,
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
                    swal("ERROR", "System tidak dapat menghapus data!", "error");
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
