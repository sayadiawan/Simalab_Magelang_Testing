@extends('masterweb::template.admin.layout')

@section('title')
  Laporan Pendaptan Klinik Management
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
                <li class="breadcrumb-item active" aria-current="page"><span>Laporan Pendaptan Klinik</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row" style="margin-bottom: 20px">
        <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

        <div class="col">
          <div class="input-group date datepicker">
            <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Pilih tanggal awal">
            <span class="input-group-addon input-group-append border-left">
              <span class="far fa-calendar input-group-text"></span>
            </span>
          </div>
        </div>

        <div class="col">
          <div class="input-group date datepicker">
            <input type="text" class="form-control" id="end_date" name="end_date" placeholder="Pilih tangal akhir">
            <span class="input-group-addon input-group-append border-left">
              <span class="far fa-calendar input-group-text"></span>
            </span>
          </div>
        </div>

        <div class="col">
          <a href="javascript:void(0)" class="btn btn-fw btn-primary mt-1 btn-print-periodik-klinik"><i
              class="fa fa-print" aria-hidden="true"></i> Print</a>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          {{-- <h4>Total Pendapatan: <span id="count-pendapatan">{{ $count != null ? rupiah($count) : 'Rp. 0' }}</span></h4> --}}

          <h4>Total Pendapatan: Rp. <span id="count-pendapatan"></span></h4>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <table id="table-pendapatan-klinik" class="table" width="100%">
            <thead>
              <tr>
                <th>No</th>
                <th>No Register</th>
                <th>Tanggal Register</th>
                <th>Nama Pasien</th>
                <th>Tanggal Transaksi</th>
                <th>Total Harga</th>
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
  <script>
    $(document).ready(function() {
      var datatable = $('#table-pendapatan-klinik').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: '{!! url()->current() !!}',
          type: 'GET',
          data: function(d) {
            d.search = $('input[type="search"]').val(),
              d.start_date = $('#start_date').val(),
              d.end_date = $('#end_date').val()
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
            data: 'tgl_register',
            name: 'tgl_register'
          },
          {
            data: 'nama_pasien',
            name: 'nama_pasien'
          },
          {
            data: 'tgl_transaksi',
            name: 'tgl_transaksi'
          },
          {
            data: 'total_harga',
            name: 'total_harga'
          }
        ]
      })

      var minDate, maxDate;

      /* // Custom filtering function which will search data in column four between two values
      $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
          var min = minDate.val();
          var max = maxDate.val();
          var date = new Date(data[4]);

          if (
            (min === null && max === null) ||
            (min === null && date <= max) ||
            (min <= date && max === null) ||
            (min <= date && date <= max)
          ) {
            return true;
          }
          return false;
        }
      );

      // Create date inputs
      minDate = new DateTime($('#start_date'), {
        format: 'MMMM Do YYYY'
      });
      maxDate = new DateTime($('#end_date'), {
        format: 'MMMM Do YYYY'
      }); */

      // Refilter the table
      $('#start_date, #end_date').on('change', function() {
        datatable.draw();

        console.log($('input[type="search"]').val());

        callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('input[type="search"]').val());
      });

      $('input[type="search"]').on('keyup', function() {
        datatable.draw();

        callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('input[type="search"]').val());
      });

      // logic filter
      $('#start_date, #end_date').on('change', function() {
        if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?search=" +
            $('input[type="search"]').val()).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print").attr('target',
            '_blank');
        }
      });

      $('input[type="search"]').on('keyup', function() {
        if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?search=" +
            $('input[type="search"]').val()).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print").attr('target',
            '_blank');
        }
      });

      if ($('#start_date').val() && $('#start_date').val() && $(
          'input[type="search"]').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
        var search = $('input[type="search"]').val();

        $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
          start_date + "&end_date=" + end_date + "&search=" + search).attr('target', '_blank');
      } else if ($('#start_date').val() && $('#start_date').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

        $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?start_date=" +
          start_date + "&end_date=" + end_date).attr('target', '_blank');
      } else if ($('input[type="search"]').val()) {

        $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print?search=" +
          $('input[type="search"]').val()).attr('target', '_blank');
      } else {
        $("a.btn-print-periodik-klinik").attr("href", "/elits-pendapatan-klinik-set-print").attr('target',
          '_blank');
      }

      callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('input[type="search"]').val());

      function callCountPendapatan(start_date = null, end_date = null, search = null) {
        $.ajax({
          type: "POST",
          url: "{{ route('elits-pendapatan-klinik-count') }}",
          data: {
            search: search,
            start_date: start_date,
            end_date: end_date,
            _token: $('#csrf-token').val()
          },
          dataType: "JSON",
          success: function(response) {
            // $('#count-pendapatan').text(response);

            $('#count-pendapatan').text(parseFloat(response, 10).toFixed(2).replace(
                /(\d)(?=(\d{3})+\.)/g, "$1,")
              .toString());
          },
          error: function() {
            swal("ERROR", "System tidak dapat mengambil data total harga!", "error");
          }
        });
      }

      if ($(".datepicker").length) {
        $('.datepicker').datepicker({
          enableOnReadonly: true,
          todayHighlight: true,
          autoclose: true,
        });
      }
    });
  </script>
@endsection
