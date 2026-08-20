@extends('masterweb::template.admin.layout')

@section('title')
  Print Label Permohonan Uji Klinik
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
                <li class="breadcrumb-item active" aria-current="page"><span>Label Pasien</span></li>
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

            <table id="table-pasien" class="table" width="100%">
              <thead>
                <tr>
                  <th style="width: 10%" class="text-center">Selection</th>
                  <th>No</th>
                  <th>Nama Pasien</th>
                  <th style="width: 15%" class="text-center">Nomor Rekam Medis</th>
                  <th class="text-center">Tanggal Lahir</th>
                  <th>Jenis Pemeriksaan</th>
                  <th style="width: 15%" class="text-center">Tgl Pemeriksaan</th>
                </tr>
              </thead>

              <tbody id="table-body"></tbody>
            </table>

            <div class="row mt-3">
              <div class="col-md-12">
                <button type="button" class="btn btn-dark btn-icon-text btn-print">
                  Print Selected Label
                  <i class="fa fa-print btn-icon-append"></i>
                </button>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(document).ready(function() {
      var datatable = $('#table-pasien').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: "{{ url('elits-label-permohonan-uji-klinik') }}",
          type: 'GET',
          data: function(d) {
            d.search = $('input[type="search"]').val()
          }
        },
        columns: [
          {
            data: 'set_checkbox',
            name: 'set_checkbox',
            orderable: false,
            searchable: false
          },
          {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'nama_pasien',
            name: 'nama_pasien'
          },
          {
            data: 'no_rekammedis_pasien',
            name: 'no_rekammedis_pasien'
          },
          {
            data: 'tgllahir_pasien',
            name: 'tgllahir_pasien'
          },
          {
            data: 'set_jenis_pemeriksaan',
            name: 'set_jenis_pemeriksaan'
          },
          {
            data: 'tgl_pemeriksaan',
            name: 'tgl_pemeriksaan'
          }
        ]
      });

      $('.btn-print').on('click', function(event) {
        event.preventDefault();
        var cb_label = $(".checkbox-label:checkbox:checked");
        var id_pengujian_uji_klinik = [];
        $.each(cb_label, function(key, value) {
          id_pengujian_uji_klinik.push(value.value);
        });
        var id_pengujian_uji_klinik_string = id_pengujian_uji_klinik.toString();

        if (id_pengujian_uji_klinik.length > 0) {
          swal({
            title: "Apakah anda yakin?",
            text: "Untuk mencetak " + id_pengujian_uji_klinik.length + " label",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          }).then((result) => {
            if (result) {
              window.open("elits-label-permohonan-uji-klinik/print?pengujian_uji_klinik=" + id_pengujian_uji_klinik_string, '_blank');
            } else {
              swal("Cancelled", "Print Label dibatalkan!", "error");
            }
          });
        } else {
          swal("Warning", "Pilih min. 1 label untuk dicetak!", "warning");
        }
      });
    });
  </script>
@endsection
