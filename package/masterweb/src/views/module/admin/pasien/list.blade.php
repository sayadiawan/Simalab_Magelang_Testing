@extends('masterweb::template.admin.layout')

@section('title')
  Pasien Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-pasien') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Pasien Management</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="form-inline flex-wrap">
            <div class="form-group mr-3 mb-2">
              <label for="per_page" class="mr-2 mb-0 text-muted">Tampilkan</label>
              <select id="per_page" class="form-control" style="width: auto;">
                @foreach ($allowedPerPage as $option)
                  <option value="{{ $option }}" {{ (int) $perPage === (int) $option ? 'selected' : '' }}>
                    {{ $option }}
                  </option>
                @endforeach
              </select>
              <span class="ml-2 text-muted">data per halaman</span>
            </div>
          </div>
        </div>
        <div class="col-md-4 text-md-right mb-2">
          <a href="{{ route('elits-pasien.create') }}">
            <button type="button" class="btn btn-info btn-icon-text">
              Tambah Data
              <i class="fa fa-plus btn-icon-append"></i>
            </button>
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="table-pasien" class="table table-striped table-hover" width="100%">
              <thead>
                <tr>
                  <th style="width: 60px;">No</th>
                  <th>NIK Pasien</th>
                  <th>Nomor Rekam Medis</th>
                  <th>Nama Pasien</th>
                  <th>Nomor Telepon</th>
                  <th style="width: 120px;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(document).ready(function() {
      var initialPerPage = parseInt($('#per_page').val(), 10) || 15;

      var datatable = $('#table-pasien').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        stateSave: true,
        responsive: true,
        lengthChange: false,
        pageLength: initialPerPage,
        dom: '<"row"<"col-sm-12 col-md-6"f><"col-sm-12 col-md-6"p>>rtip',
        language: {
          processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Memuat...',
          search: 'Cari:',
          info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
          infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
          infoFiltered: '(disaring dari _MAX_ total data)',
          paginate: {
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Selanjutnya',
            previous: 'Sebelumnya'
          },
          emptyTable: 'Tidak ada data pasien',
          zeroRecords: 'Tidak ada data yang cocok'
        },
        ajax: {
          url: '{{ route('elits-pasien-datatables') }}',
          type: 'GET'
        },
        columns: [{
            data: 'nomer',
            name: 'nomer',
            orderable: false,
            searchable: false,
            className: 'text-center'
          },
          {
            data: 'nik_pasien',
            name: 'nik_pasien'
          },
          {
            data: 'no_rekammedis_pasien',
            name: 'no_rekammedis_pasien'
          },
          {
            data: 'nama_pasien',
            name: 'nama_pasien'
          },
          {
            data: 'phone_pasien',
            name: 'phone_pasien'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            width: '15%'
          }
        ],
        order: [
          [3, 'asc']
        ]
      });

      $('#per_page').on('change', function() {
        var perPage = parseInt($(this).val(), 10) || 15;
        datatable.page.len(perPage).draw();
      });

      $('#table-pasien').on('click', '.btn-hapus', function() {
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
                url: '/elits-pasien-destroy/' + kode,
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
                        datatable.ajax.reload(null, false);
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
