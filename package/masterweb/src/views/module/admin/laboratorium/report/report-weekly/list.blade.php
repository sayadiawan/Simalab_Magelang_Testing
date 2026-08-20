@extends('masterweb::template.admin.layout')

@section('title')
  Laporan Mingguan Pemeriksaan
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
                <li class="breadcrumb-item active"><a href="{{ url('/report-weekly') }}">Laporan Mingguan Pemeriksaan</a>
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
      <div class="d-flex">
        <div class="mr-auto p-2">
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group row mb-4">
            <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

            <div class="col-md-2">
              <label for="month" class="bold">Dari Tanggal</label>

              <input type="date" class="form-control" name="from_tanggal" id="from_tanggal"
                pattern="\d{4}-\d{2}-\d{2}">
            </div>

            <div class="col-md-2">
              <label for="year" class="bold">Ke Tanggal</label>

              <input type="date" class="form-control" name="to_tanggal" id="to_tanggal" pattern="\d{4}-\d{2}-\d{2}"
                readonly>
            </div>

            <div class="col-md-3">
              <label for="Program" class="bold">Program</label>
              <select class="select-program form-control w-100">
                <option value=""></option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-12">
          <div class="pt-4 text-right">
            <a href="#" class="btn btn-light btn-fw btn-print-report-weekly" target="__blank"> <i
                class="fas fa-print"></i>
              Cetak</a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          @if ($message = Session::get('warning'))
            <div class="alert alert-warning alert-block">
              <button type="button" class="close" data-dismiss="alert">×</button>
              <strong>{{ $message }}</strong>
            </div>
          @endif
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id='empTable' class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal Pemeriksaan</th>
                  <th>Nama Sample/Merk</th>
                  <th>Nama Customer/Pasien</th>
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
@endsection


@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      // DataTable
      var table = $('#empTable').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax: {
          url: "{{ route('report-weekly.data-report-weekly') }}",
          type: "GET",
          data: function(d) {
            d.from_tanggal = getFromTanggal(),
              d.to_tanggal = getToTanggal(),
              d.program = getProgram()
          }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'tgl_pemeriksaan',
            name: 'tgl_pemeriksaan'
          },
          {
            data: 'codesample_samples',
            name: 'codesample_samples'
          },
          {
            data: 'customer',
            name: 'customer'
          }
        ]
      });

      table.on('draw', function() {
        $('[data-toggle="tooltip"]').tooltip();
      });

      function getFromTanggal() {
        var from_tanggal = $('#from_tanggal').val();
        return from_tanggal;
      }

      function getToTanggal() {
        var to_tanggal = $('#to_tanggal').val();
        return to_tanggal;
      }

      function getProgram() {
        var program = $('.select-program').val();
        return program;
      }

      $('#from_tanggal').change(function() {
        $('#to_tanggal').val(moment($('#from_tanggal').val()).add(7, 'd').format('YYYY-MM-DD'));

        table.ajax.reload(null, false);

        setUrlPrintReport()
      })

      $('#to_tanggal').change(function() {
        table.ajax.reload(null, false);

        setUrlPrintReport()
      })

      $('.select-program').change(function() {
        table.ajax.reload(null, false);

        setUrlPrintReport()
      })

      $('.btn-print-report-weekly').hide();

      function setUrlPrintReport() {
        var from_tanggal = $('#from_tanggal').val();
        var to_tanggal = $('#to_tanggal').val();
        var program = $('.select-program').val();

        if (from_tanggal !== '' && program !== '') {
          var val_url = '{{ url('report-weekly/print-report-weekly?from_tanggal=') }}' + from_tanggal +
            '&to_tanggal=' + to_tanggal +
            '&program=' +
            program;

          $('.btn-print-report-weekly').show();
          $('.btn-print-report-weekly').attr('href', val_url);
        } else {
          var val_url = '#';

          $('.btn-print-report-weekly').hide();
          $('.btn-print-report-weekly').attr('href', val_url);
        }
      }



      var CSRF_TOKEN = $('#csrf-token').val();

      $(".select-program").select2({
        ajax: {
          url: "{{ route('getProgram') }}",
          type: "post",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              search: params.term // search term
            };
          },
          processResults: function(response) {
            return {
              results: response
            };
          },
          cache: true
        },
        placeholder: 'Pilih Program',
        allowClear: true
      });
    });
  </script>
@endsection
