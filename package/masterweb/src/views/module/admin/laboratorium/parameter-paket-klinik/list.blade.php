@extends('masterweb::template.admin.layout')

@section('title')
  Parameter Paket Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-paket-klinik') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Parameter Paket Management</span></li>
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
          <a href="{{ route('elits-parameter-paket-klinik.categoryLayout') }}">
            <button type="button" class="btn btn-gradient-success btn-icon-text">
              <i class="fa fa-th-large btn-icon-prepend"></i>
              Penataan Layout Halaman
            </button>
          </a>
        </div>

        <div class="p-2">
          <a href="{{ route('elits-parameter-paket-klinik.create') }}">
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
                  <th width="50">Urut</th>
                  <th>No</th>
                  <th>Nama Parameter Paket</th>
                  <th>Singkatan Laporan</th>
                  <th>Nama Parameter Jenis</th>
                  <th>Harga Parameter Paket</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody id="sortable-table">
                @php
                  $no = 1;
                @endphp

                @foreach ($data as $item)
                  <tr data-id="{{ $item->id_parameter_paket_klinik }}">
                    <td class="drag-handle" style="cursor: move;">
                      <i class="fas fa-grip-vertical"></i>
                    </td>
                    <td class="row-number">{{ $no++ }}</td>
                    <td>{{ $item->name_parameter_paket_klinik}}</td>
                    <td>
                      @if (!empty($item->singkatan_laporan))
                        <span class="badge badge-info">{{ $item->singkatan_laporan }}</span>
                        @if (!empty($item->is_agregat_laporan))
                          <span class="badge badge-warning">agregat</span>
                        @endif
                      @else
                        <span class="text-muted">-</span>
                      @endif
                      @if (isset($item->tampil_di_laporan) && !(int) $item->tampil_di_laporan)
                        <span class="badge badge-secondary">disembunyikan</span>
                      @endif
                    </td>

                    <td>
                      @foreach ($item->parameterpaketjenisklinik as $dps)


                        @if ($dps->parameterjenisklinik)
                          <label class="badge badge-primary badge-pill">{{ $dps->parameterjenisklinik->name_parameter_jenis_klinik }}</label>
                        @endif
                      @endforeach
                    </td>

{{--
                    <td>{{ $item->name_parameter_paket_klinik }}</td> --}}
                    <td>Rp. {{ number_format($item->harga_parameter_paket_klinik, 2, ',', '.') }}</td>

                    <td>
                      <a href="{{ route('elits-parameter-paket-klinik.edit', [$item->id_parameter_paket_klinik]) }}">
                        <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                          <i class="fas fa-pencil-alt"></i>
                        </button>
                      </a>

                      <a href="#hapus" class="btn btn-outline-danger btn-rounded btn-icon btn-hapus"
                        data-id="{{ $item->id_parameter_paket_klinik }}"
                        data-name="{{ $item->name_parameter_paket_klinik }}"><i class="fas fa-trash"></i>
                      </a>
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
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
  <script>
    // Override DataTable initialization before data-table.js loads
    $(document).ready(function() {
      // Wait for data-table.js to initialize, then modify settings
      setTimeout(function() {
        if ($.fn.DataTable.isDataTable('#order-listing')) {
          var table = $('#order-listing').DataTable();
          table.page.len(-1).draw(); // Show all entries
          
          // Hide pagination elements
          $('#order-listing_wrapper .dataTables_paginate').hide();
          $('#order-listing_wrapper .dataTables_length').hide();
          $('#order-listing_wrapper .dataTables_info').hide();
        }
      }, 100);
    });

    $(function() {
      // Initialize Sortable
      var el = document.getElementById('sortable-table');
      var sortable = Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
          // Update row numbers
          updateRowNumbers();

          var items = [];
          $('#sortable-table tr').each(function(index) {
            items.push({
              id: $(this).data('id'),
              sort: index + 1
            });
          });

          // Update sort order via AJAX
          $.ajax({
            type: 'POST',
            url: '/elits-parameter-paket-klinik-update-sort',
            data: {
              _token: '{{ csrf_token() }}',
              items: items
            },
            success: function(response) {
              if (response.status) {
                //swal("Success!", response.pesan, "success");
              } else {
                swal("Error!", response.pesan, "error");
              }
            },
            error: function() {
              swal("ERROR", "Gagal mengupdate urutan data!", "error");
            }
          });
        }
      });

      // Function to update row numbers
      function updateRowNumbers() {
        $('#sortable-table tr').each(function(index) {
          $(this).find('.row-number').text(index + 1);
        });
      }

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
                url: '/elits-parameter-paket-klinik-destroy/' + kode,
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
                        document.location = '/elits-parameter-paket-klinik';
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
