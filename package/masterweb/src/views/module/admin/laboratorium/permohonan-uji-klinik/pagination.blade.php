@extends('masterweb::template.admin.layout')

@section('title')
  Permohonan Uji Klinik Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji Klinik Management</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      @if (getAction('create'))
        <div class="row">
          <div class="col-md-12">
            <div class="mb-2 float-right">
              <a href="{{ route('elits-permohonan-uji-klinik.create') }}">

                <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();">
                  Tambah Data
                  <i class="fa fa-plus btn-icon-append"></i>
                </button>
              </a>
            </div>
          </div>
        </div>
      @endif

      <div class="row">

        @if (session('status'))
          <div class="col-12">
            <div class="alert alert-success">
              {{ session('status') }}
            </div>
          </div>
        @endif


        <div class="col-12">
          <table id='empTable' class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>No Register</th>
                <th>No Pasien</th>
                <th>Nama Pasien</th>
                <th>Tanggal Pengambilan</th>
                <th>Status Proses</th>
                <th>Status Pembayaran</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody id="tabel-body">

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL PAYMENT --}}
  <div class="modal fade text-left" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel1">Basic Modal</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <form action="" method="POST" id="form-payment" enctype="multipart/form-data">
          @csrf
          <input type="hidden" class="form-control" name="_token" id="csrf-token" value="{{ Session::token() }}" />
          <input type="hidden" class="form-control" name="id_permohonan_uji_klinik" id="id_permohonan_uji_klinik"
            readonly>
          <input type="hidden" class="form-control" name="nota_petugas_permohonan_uji_payment_klinik"
            id="nota_petugas_permohonan_uji_payment_klinik" readonly>

          <div class="modal-body">
            <div class="form-group">
              <label for="nama_pasien">Nama Pasien</label>
              <input type="text" class="form-control" id="nama_pasien" name="nama_pasien"
                placeholder="Enter nama pasien">
            </div>

            <div class="form-group">
              <label for="alamat_pasien">Alamat Pasien</label>
              <textarea class="form-control" id="alamat_pasien" name="alamat_pasien" cols="30" rows="10"></textarea>
            </div>

            <div class="form-group">
              <label for="total_harga">Total</label>
              <input type="text" class="form-control" id="total_harga_custom" name="total_harga_custom"
                placeholder="Enter total harga">

              <input type="hidden" class="form-control" id="total_harga" name="total_harga" readonly>
            </div>

            <div class="form-group">
              <label for="total_harga">Petugas</label>
              <input type="text" class="form-control" id="nota_namapetugas_permohonan_uji_payment_klinik"
                name="nota_namapetugas_permohonan_uji_payment_klinik" placeholder="Masukkan nama petugas">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">
              <i class="bx bx-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Close</span>
            </button>

            <button type="button" class="btn btn-primary ml-1" id="btnSave">Proses</button>
          </div>
        </form>
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
        stateSave: true,
        responsive: true,
        ajax: {
          url: "{{ route('elits-permohonan-uji-klinik.data-permohonan-uji-klinik') }}",
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
            data: 'noregister_permohonan_uji_klinik',
            name: 'noregister_permohonan_uji_klinik'
          },
          {
            data: 'no_rekammedis',
            name: 'no_rekammedis'
          },
          {
            data: 'nama_pasien',
            name: 'nama_pasien'
          },
          {
            data: 'tgl_pengambilan',
            name: 'tgl_pengambilan'
          },
          {
            data: 'status_permohonan',
            name: 'status_permohonan'
          },
          {
            data: 'status_pembayaran',
            name: 'status_pembayaran'
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

      // datatables responsive
      new $.fn.dataTable.FixedHeader(table);

      var CSRF_TOKEN = $('#csrf-token').val();

      // btn payment
      $('#empTable').on('click', '.btn-payment', function(e) {
        e.preventDefault();
        var permohonan_uji_klinik_id = $(this).data('id');
        $('#form-menu').trigger('reset'); // reset form on modals

        $('#id_permohonan_uji_klinik').val(permohonan_uji_klinik_id);

        $.ajax({
          type: "POST",
          url: "{{ route('permohonan-uji-klinik-get-payment') }}",
          data: {
            permohonan_uji_klinik_id: permohonan_uji_klinik_id,
            _token: CSRF_TOKEN
          },
          dataType: "JSON",
          success: function(data) {
            $('[name="nota_petugas_permohonan_uji_payment_klinik"]').val(data.nota_petugas);
            $('[name="nota_namapetugas_permohonan_uji_payment_klinik"]').val(data.nota_namapetugas).prop(
              'readonly', true);
            $('[name="nama_pasien"]').val(data.nama_pasien).prop('readonly', true);
            $('[name="alamat_pasien"]').val(data.alamat_pasien).prop('readonly', true);
            $('[name="total_harga_custom"]').val(data.total_harga_custom).prop('readonly', true);
            $('[name="total_harga"]').val(data.total_harga);

            $('#modal-payment').modal('show'); // show bootstrap modal when complete loaded
            $('.modal-title').text('Pelunasan Pembayaran'); // Set title to Bootstrap modal title
          },
          error: function(jqXHR, textStatus, errorThrown) {
            swal("Error", "Error get data from ajax!", "error");
          }
        });
      });

      // btn payment proses
      $('#btnSave').click(function(param) {
        $('#btnSave').text('Memproses...'); //change button text
        $('#btnSave').prop('disabled', true); //set button disable

        $.ajax({
          url: "{{ route('permohonan-uji-klinik-store-payment') }}",
          type: "POST",
          data: $('#form-payment').serialize(),
          dataType: "JSON",
          success: function(data) {
            $('#btnSave').text('Proses'); //change button text
            $('#btnSave').prop('disabled', false); //set button enable

            if (data.status == true) //if success close modal and reload ajax table
            {
              swal({
                icon: "success",
                title: "Process Success!",
                text: data.pesan,
              });

              $('#form-payment').trigger('reset'); // reset form on modals
            } else {
              var pesan = "";
              var data_pesan = data.pesan;
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
                  text: data.pesan,
                  icon: "warning"
                });
              }
            }

            $('#modal-payment').modal('hide');
            table.ajax.reload(null, false);
          },
          error: function(jqXHR, textStatus, errorThrown) {
            swal("Error!",
              "Something is wrong when you want to save or change data. Please try again!",
              "error");

            $('#btnSave').text('Proses'); //change button text
            $('#btnSave').prop('disabled', false); //set button enable
          }
        });
      });

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
