@extends('masterweb::template.admin.layout')
@section('title')
  Data Matriks Sample
@endsection


@section('content')
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

                <li class="breadcrumb-item active" aria-current="page">
                  <span>Data Matriks Jenis Sarana</span>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <input type="hidden" name="csrf-token" id="csrf-token" value="{{ Session::token() }}" />

      <div class="row grid-margin">
        <div class="col-12">
          <div class="alert alert-warning" role="alert">
            <i class="fa fa-exclamation-triangle"></i>

            <strong>Perhatian!</strong> Data yang Anda tambahkan atau diubah akan mempengaruhi di laporan pastikan
            lakukan koreksi sebelum lanjut proses.
          </div>
        </div>
      </div>

      @if (getAction('create'))
        <div class="row mt-5">
          <div class="col-md-12">
            <div class="mb-2 float-right">
              <a href="javascript:void(0)">
                <button type="button" class="btn btn-info btn-icon-text btn-create">
                  Tambah Data
                  <i class="fa fa-plus btn-icon-append"></i>
                </button>
              </a>
            </div>
          </div>
        </div>
      @endif

      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id='table-matriks-sample' class="table" width="100%">
              <thead>
                <tr>
                  <th style="width: 10%">No</th>
                  <th style="width: 75%">Nama Matriks Jenis Sarana</th>
                  <th style="width: 15%">Aksi</th>
                </tr>
              </thead>

              <tbody id="tabel-body">

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- begin line modal add --}}
  <div class="modal fade text-left" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel1">Basic Modal</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <form action="" method="POST" id="form-create" enctype="multipart/form-data">
          @csrf

          <div class="modal-body">
            <div class="form-group">
              <label for="name_sample_type_sarana">Nama Matriks Jenis Sarana</label>
              <input type="text" class="form-control" id="name_sample_type_sarana" name="name_sample_type_sarana"
                placeholder="Enter nama matriks jenis sarana">
            </div>
          </div>

          <input type="hidden" class="form-control" id="id_sample_type_sarana" name="id_sample_type_sarana" readonly>

          <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Close</span>
            </button>

            <button type="button" class="btn btn-primary ml-1" id="btn-save">Proses</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  {{-- end line modal add --}}
@endsection

@section('scripts')
  <script>
    $(document).ready(function() {
      var table = $('#table-matriks-sample').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: "{{ route('matriks-jenis-sarana.index') }}",
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
            data: 'name_sample_type_sarana',
            name: 'name_sample_type_sarana'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
          }
        ]
      });

      // datatables responsive
      new $.fn.dataTable.FixedHeader(table);

      table.on('draw', function() {
        $('[data-toggle="tooltip"]').tooltip();
      });

      var CSRF_TOKEN = $('#csrf-token').val();

      $('input[type="search"]').on('keyup', function() {
        table.draw();
      });

      var save_method;

      // trigger btn modal create
      $(".btn-create").click(function(e) {
        e.preventDefault();
        save_method = 'add';

        $('#form-create')[0].reset(); // reset form on modals
        $('#btn-save').text('Proses'); //change button text
        $('#btn-save').prop('disabled', false); //set button enable

        $('#modal-create').modal('show'); // show bootstrap modal
        $('.modal-title').text('Tambah Data'); // Set Title to Bootstrap modal title
      });

      // trigger btn modal update
      $(document).on('click', '.btn-edit', function() {
        var kode = $(this).data('id');
        var nama = $(this).data('nama');
        save_method = 'update';

        $('#form-create')[0].reset();
        $('#btn-save').text('Proses'); //change button text
        $('#btn-save').prop('disabled', false); //set button enable

        $.ajax({
          type: "GET",
          url: "/elits-matriks-sample/edit/" + kode,
          dataType: "JSON",
          success: function(data) {
            console.log(data);
            $('[name="id_sample_type_sarana"]').val(kode);
            $('[name="name_sample_type_sarana"]').val(data.name_sample_type_sarana);

            $('#modal-create').modal('show'); // show bootstrap modal when complete loaded
            $('.modal-title').text('Ubah Data'); // Set title to Bootstrap modal title
          },
          error: function(jqXHR, textStatus, errorThrown) {
            var err = eval("(" + jqXHR.responseText + ")");
            swal("Error!", err.Message, "error");
          }
        });
      });

      // trigger button untuk save form create
      $(document).on("click", "#btn-save", function() {
        $('#btn-save').text('Memproses...'); //change button text
        $('#btn-save').attr('disabled', true); //set button disable
        var url;

        if (save_method == 'add') {
          url = "{{ route('matriks-jenis-sarana-store') }}";
        } else {
          url = "{{ route('matriks-jenis-sarana-update') }}";
        }

        // ajax adding data to database
        $.ajax({
          url: url,
          type: "POST",
          data: $('#form-create').serialize(),
          dataType: "JSON",
          headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
          },
          success: function(response) {
            if (response.status == true) //if success close modal and reload ajax table
            {
              $('#modal-create').modal('hide');
              table.ajax.reload(null, false);

              swal({
                icon: "success",
                title: "Process Success!",
                text: response.pesan,
              });

              $('#form-create')[0].reset();
            } else {
              var pesan = "";
              var data_pesan = response.pesan;
              const wrapper = document.createElement('div');

              if (typeof(data_pesan) == 'object') {
                jQuery.each(data_pesan, function(key, value) {
                  console.log(value);
                  pesan += value + '. <br>';
                  wrapper.innerHTML = pesan;
                });

                swal({
                  title: "Error!",
                  content: wrapper,
                  icon: "warning"
                });
              } else {
                swal({
                  title: "Error!",
                  text: response.pesan,
                  icon: "warning"
                });
              }
            }

            $('#btn-save').text('Proses'); //change button text
            $('#btn-save').attr('disabled', false); //set button enable

          },
          error: function(jqXHR, textStatus, errorThrown) {
            var err = eval("(" + jqXHR.responseText + ")");
            swal("Error!", err.Message, "error");

            $('#btn-save').text('Proses'); //change button text
            $('#btn-save').attr('disabled', false); //set button enable
          }
        });
      });


      $('#table-matriks-sample').on('click', '.btn-hapus', function() {
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
                url: '/matriks-jenis-sarana/delete/' + kode,
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
                error: function(jqXHR, textStatus, errorThrown) {
                  var err = eval("(" + jqXHR.responseText + ")");
                  swal("Error!", err.Message, "error");
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
