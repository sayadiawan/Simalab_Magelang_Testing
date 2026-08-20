@extends('masterweb::template.admin.layout')

@section('title')
  Rekam Medis Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-rekam-medis') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Rekam Medis Management</span></li>
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
          <table id="table-rekam-medis" class="table" width="100%">
            <thead>
              <tr>
                <th>No</th>
                <th>NIK Pasien</th>
                <th>Nomor Rekam Medis</th>
                <th>Nama Pasien</th>
                <th>Nomor Telepon</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody id="table-body">

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(document).ready(function() {
      var datatable = $('#table-rekam-medis').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        stateSave: true,
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100]
        ],
        order: [
          [3, 'asc']
        ],
        ajax: {
          url: '{!! url()->current() !!}',
          type: 'GET'
        },
        language: {
          lengthMenu: 'Tampilkan _MENU_ data per halaman',
          zeroRecords: 'Data tidak ditemukan',
          info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
          infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
          infoFiltered: '(difilter dari _MAX_ total data)',
          search: 'Cari:',
          paginate: {
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Selanjutnya',
            previous: 'Sebelumnya'
          }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'pasien.nik_pasien',
            name: 'pasien.nik_pasien'
          },
          {
            data: 'no_rekam_medis',
            name: 'no_rekam_medis'
          },
          {
            data: 'pasien.nama_pasien',
            name: 'pasien.nama_pasien'
          },
          {
            data: 'pasien.phone_pasien',
            name: 'pasien.phone_pasien'
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

      $('#table-rekam-medis').on('click', '.btn-hapus', function() {
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
                url: '/elits-rekam-medis-destroy/' + kode,
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
