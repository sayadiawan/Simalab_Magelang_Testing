@extends('masterweb::template.admin.layout')

@section('title')
Permohonan Uji Management
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
              <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Laboraturium</a></li>
              <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji Management</span></li>
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

      @if (getAction('create'))
      <div class="p-2">
        <a href="{{ route('elits-permohonan-uji.create') }}">

          <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();">
            Tambah Data
            <i class="fa fa-plus btn-icon-append"></i>
          </button>
        </a>
      </div>
      @endif
    </div>

    <div class="row">

      @if (session('status'))
      <div class="alert alert-success">
        {{ session('status') }}

      </div>
      @endif
      <div class="col-12">
        <div class="table-responsive">
          <table id='empTable' class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>No Sampel</th>
                <th>Pelanggan</th>
                <th>TGL</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
                <th>Cetak</th>

              </tr>
            </thead>

          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {

      // DataTable
      var table = $('#empTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('elits-permohonan-uji.getSamplePagination') }}",
        columns: [{
            data: 'number'
          },
          {
            data: 'code_permohonan_uji'
          },
          {
            data: 'customer_permohonan_uji'
          },

          {
            data: 'date_permohonan_uji'
          },
          {
            data: 'status_pembayaran'
          },
          {
            data: 'action'
          },
          {
            data: 'print'
          },
        ],
        columnDefs: [
          { targets: 1, visible: false, searchable: true }
        ]
      });

      // datatables responsive
      new $.fn.dataTable.FixedHeader(table);

      $('#empTable').on('click', '.btn-hapus', function() {
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
                url: '/elits-permohonan-uji/elits-permohonan-uji-destroy/' + kode,
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
                        document.location = '/elits-permohonan-uji';
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