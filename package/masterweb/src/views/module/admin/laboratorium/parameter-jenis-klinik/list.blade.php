@extends('masterweb::template.admin.layout')

@section('title')
  Parameter Jenis Klinik Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-jenis-klinik') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Parameter Jenis Klinik Management</span></li>
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
          <button type="button" class="btn btn-warning btn-icon-text mr-2" id="btn-delete-unused">
            <i class="fa fa-trash btn-icon-prepend"></i>
            Hapus Tidak Terpakai
          </button>
          <a href="{{ route('parameter-jenis-klinik.reorder-page') }}">
            <button type="button" class="btn btn-secondary btn-icon-text mr-2">
              Urutkan
              <i class="fa fa-sort btn-icon-append"></i>
            </button>
          </a>
          <a href="{{ route('elits-parameter-jenis-klinik.create') }}">

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
                  <th>Kode Parameter Jenis</th>
                  <th>Nama Parameter Jenis</th>
                  <th>Level</th>
                  <th>Parent</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                @php
                  $no = 1;
                @endphp

                @foreach ($data as $item)
                  <tr class="{{ $item->level > 0 ? 'bg-light' : '' }}">
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->code_parameter_jenis_klinik }}</td>
                    <td>
                      @if($item->level > 0)
                        <span class="ml-4 text-muted">└─</span> 
                      @endif
                      {{ $item->name_parameter_jenis_klinik }}
                    </td>
                    <td>
                      @if($item->level == 0)
                        <span class="badge badge-primary">Parent</span>
                      @else
                        <span class="badge badge-info">Sub</span>
                      @endif
                    </td>
                    <td>
                      @if($item->parent)
                        {{ $item->parent->name_parameter_jenis_klinik }}
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>

                    <td>
                      <a href="{{ route('elits-parameter-jenis-klinik.edit', [$item->id_parameter_jenis_klinik]) }}">
                        <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                          <i class="fas fa-pencil-alt"></i>
                        </button>
                      </a>

                      {{-- <form onsubmit="return confirm('Delete this user permanently?')" class="d-inline"
                        action="{{ route('elits-parameter-jenis-klinik.destroy', [$item->id_parameter_jenis_klinik]) }}"
                        method="POST">

                        @csrf

                        <input type="hidden" name="_method" value="DELETE">

                        <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form> --}}

                      <a href="#hapus" class="btn btn-outline-danger btn-rounded btn-icon btn-hapus"
                        data-id="{{ $item->id_parameter_jenis_klinik }}"
                        data-name="{{ $item->name_parameter_jenis_klinik }}"><i class="fas fa-trash"></i></a>
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

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
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
                url: '/elits-parameter-jenis-klinik-destroy/' + kode,
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
                        document.location = '/elits-parameter-jenis-klinik';
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

      // Hapus parameter jenis klinik yang tidak terpakai
      $('#btn-delete-unused').on('click', function() {
        swal({
            title: "Apakah anda yakin?",
            text: "Akan menghapus semua parameter jenis klinik yang tidak digunakan di parameter satuan klinik dan parameter paket klinik?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              $.ajax({
                type: 'POST',
                url: '{{ route("parameter-jenis-klinik.delete-unused") }}',
                data: {
                  _token: '{{ csrf_token() }}'
                },
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
                        document.location = '/elits-parameter-jenis-klinik';
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
