@extends('masterweb::template.admin.layout')

@section('title')
  Data Jenis Sarana
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-sampletypes') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Data Jenis Sarana</span></li>
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
        <div class="col-md-12">
          <div class="mb-2 float-right">
            <a href="{{ route('elits-sampletypes.create') }}">
              <button type="button" class="btn btn-info btn-icon-text">
                Tambah Data
                <i class="fa fa-plus btn-icon-append"></i>
              </button>
            </a>
            <a href="{{ route('elits-methods.reorder-page') }}" class="ml-2">
              <button type="button" class="btn btn-secondary btn-icon-text">
                Urutkan Method
                <i class="fa fa-sort btn-icon-append"></i>
              </button>
            </a>
          </div>
        </div>
      </div>

      <div class="row">
        @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
        @endif

        <div class="col-12">
          <table id="table-sampletypes" class="table" style="width: 100%">
            <thead>
              <tr>
                <th style="width: 10%">No</th>
                {{-- <th>Kode Jenis Sarana</th> --}}
                <th style="width: 50%">Nama Jenis Sarana</th>

                <th style="width: 20%">Actions</th>
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
  <script type="text/javascript">
    $(document).ready(function() {
      // DataTable
      var datatable = $('#table-sampletypes').DataTable({
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
            data: 'name_sample_type',
            name: 'name_sample_type'
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

      $('#table-sampletypes').on('click', '.btn-hapus', function() {
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
                url: '/elits-sampletypes-destroy/' + kode,
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
