@extends('masterweb::template.admin.layout')

@section('title')
  Permohonan Uji Klinik Prolanis
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="">Laboratorium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji Klinik Prolanis Gula</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
      <div class="col-12">
          <div class="alert alert-success">
              {{ session('success') }}
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

  <div class="card">
    <div class="card-body">
      <div class="d-flex">
        <div class="mr-auto p-2"></div>
        <div class="p-2">
          <a href="{{ route('elits-permohonan-uji-klinik-2.create-prolanis-gula') }}">
            <button type="button" class="btn btn-info btn-icon-text">
              Tambah Data
              <i class="fa fa-plus btn-icon-append"></i>
            </button>
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
                  <th>Nama Prolanis Gula</th>
                  <th>Tanggal Prolanis Gula</th>
                  <th>Kuota Prolanis Gula</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $no = 1;
                @endphp

                @foreach ($data as $item)
                  <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->nama_prolanis_gula }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tgl_prolanis_gula)->isoFormat('DD MMMM YYYY') }}</td>
                    <td>{{ $item->kuota_prolanis_gula }}</td>
                    <td>
                      <a href="{{ route('elits-permohonan-uji-klinik-2.download-format-prolanis-gula', $item->id_permohonan_uji_klinik_prolanis_gula) }}" class="d-block mb-2">
                        <button type="button" class="btn btn-primary w-50">
                          <i class="fa fa-download"></i> Unduh Format
                        </button>
                      </a>
                      <button type="button" class="btn btn-success w-50" data-toggle="modal" data-target="#importModal-{{ $item->id_permohonan_uji_klinik_prolanis_gula }}">
                        <i class="fa fa-file-excel"></i> Unggah Data
                      </button>
                    </td>
                  </tr>

                  <!-- Modal -->
                  <div class="modal fade" id="importModal-{{ $item->id_permohonan_uji_klinik_prolanis_gula }}" tabindex="-1" aria-labelledby="importModalLabel-{{ $item->id_permohonan_uji_klinik_prolanis_gula }}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="importModalLabel-{{ $item->id_permohonan_uji_klinik_prolanis_gula }}">Import Data Prolanis Gula</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <form action="{{ route('elits-permohonan-uji-klinik-2.import-prolanis-gula', $item->id_permohonan_uji_klinik_prolanis_gula) }}" method="POST" enctype="multipart/form-data">
                          @csrf
                          <div class="modal-body">
                            <div class="mb-3">
                              <label for="file" class="form-label">Pilih File Excel</label>
                              <input type="file" class="form-control" id="file" name="file" required>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Import</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert JS -->
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <!-- Bootstrap JS -->
  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.5.1.slim.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/popper.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/bootstrap4.min.js')}}"></script>

  <script>
    $(function() {
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
                url: '/elits-parameter-paket-extra-destroy/' + kode,
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
                        document.location = '/elits-parameter-paket-extra';
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
    })
  </script>
@endsection
