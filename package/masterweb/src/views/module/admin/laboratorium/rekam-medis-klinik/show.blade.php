@extends('masterweb::template.admin.layout')
@section('title')
  Detail Rekam Medis Klinik
@endsection


@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-rekam-medis') }}">Rekam Medis Klinik</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"><span>detail rekam medis klinik</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Detail Rekam Medis Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <div class="table-responsive">
          <table class="table table-borderless">
            <tr>
              <th width="250px">Nama Pasien</th>
              <td>{{ $item->nama_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">Nomor Rekam Medis</th>
              <td>
                {{ $permohonanUji->getNoRekamMedis() }}
              </td>
            </tr>

            <tr>
              <th width="250px">Tanggal Lahir Pasien</th>
              <td>
                {{ isset($item->tgllahir_pasien) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item->tgllahir_pasien)->isoFormat('D MMMM Y') : '' }}
              </td>
            </tr>

            <tr>
              <th width="250px">Jenis Kelamin</th>
              <td>{{ $item->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>

            <tr>
              <th width="250px">Phone Pasien</th>
              <td>{{ $item->phone_pasien }}</td>
            </tr>

            <tr>
              <th width="250px">Alamat Pasien</th>
              <td>{{ $item->alamat_pasien }}</td>
            </tr>
          </table>
        </div>

        <div class="table-responsive">
          <table id="table-detail-rekam-medis" class="table" width="100%">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Register</th>
                <th>Nomor Register</th>
                <th>Umur</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody id="table-body">

            </tbody>
          </table>
        </div>


        <button type="button" class="btn btn-light"
          onclick="document.location='{{ url('/elits-rekam-medis') }}'">Kembali</button>
      </li>
    </ul>
  </div>
@endsection

@section('scripts')
  <script>
    $(document).ready(function() {
      var datatable = $('#table-detail-rekam-medis').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: '{!! url()->current() !!}',
          type: 'GET',
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
            data: 'tgl_register',
            name: 'tgl_register'
          },
          {
            data: 'noregister_permohonan_uji_klinik',
            name: 'noregister_permohonan_uji_klinik'
          },
          {
            data: 'umur_pasien',
            name: 'umur_pasien'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            width: '15%'
          }
        ]
      })

      $('#table-detail-rekam-medis').on('click', '.btn-hapus', function() {
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
                url: '/elits-permohonan-uji-klinik-destroy/' + kode,
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
                        // document.location = '/elits-pasien';

                        datatable.ajax.reload();
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
