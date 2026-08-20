@extends('masterweb::template.admin.layout')

@section('title')
Laporan Pendaptan Non-Klinik Management
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
              <li class="breadcrumb-item active" aria-current="page"><span>Laporan Pendaptan Non-Klinik</span></li>
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

      <div class="col-3">
        <div class="input-group date datepicker">
          <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Pilih tanggal awal">
          <span class="input-group-addon input-group-append border-left">
            <span class="far fa-calendar input-group-text"></span>
          </span>
        </div>
      </div>

      <div class="col-3">
        <div class="input-group date datepicker">
          <input type="text" class="form-control" id="end_date" name="end_date" placeholder="Pilih tangal akhir">
          <span class="input-group-addon input-group-append border-left">
            <span class="far fa-calendar input-group-text"></span>
          </span>
        </div>
      </div>

      <div class="col-3">
        <div class="row">
          <div class="col-12">
            <div class="form-group" style="margin-bottom:0">
              <select class="select-lab form-control w-100 mt-1 input-select2" name="name_lab" id="name_lab">
                <option value="">-Semua Lab-</option>

                @foreach ($data_lab as $val)
                <option value="{{ $val->id_laboratorium }}">{{ $val->nama_laboratorium }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="form-group" style="margin-bottom:0">
              <select class="select-lab form-control w-100 mt-1 input-select2" name="payment" id="payment">
                <option value="" selected>-Semua Pembayaran-</option>
                <option value="1">-Terbayar-</option>
                <option value="0">-Belum Terbayar-</option>


              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="col-3">
        <a href="javascript:void(0)" class="btn btn-fw btn-primary mt-1 btn-print-periodik-nonklinik"><i
            class="fa fa-print" aria-hidden="true"></i> Print</a>
      </div>

    </div>

    <div class="row">
      <div class="col-12">
        <h4>Total Pendapatan: Rp. <span id="count-pendapatan"></span></h4>

        <h4>Total Sample: <span id="count-sample"></span></h4>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <table id="table-pendapatan-nonklinik" class="table" width="100%">
          <thead>
            <tr>
              <th>No</th>
              <th>No Sample</th>
              <th>Jenis Sample</th>
              <th>Nama Pelanggan</th>
              <th>Laboratorium</th>
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
      var datatable = $('#table-pendapatan-nonklinik').DataTable({
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
            d.name_lab = $('#name_lab').val(),
            d.start_date = $('#start_date').val(),
            d.end_date = $('#end_date').val(),
            d.payment = $('#payment').val()
              
          }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'codesample_samples',
            name: 'codesample_samples'
          },
          {
            data: 'name_sample_type',
            name: 'name_sample_type'
          },
          {
            data: 'name_customer',
            name: 'name_customer'
          },
          {
            data: 'nama_laboratorium',
            name: 'nama_laboratorium'
          },
          {
            data: 'tgl_transaksi',
            name: 'tgl_transaksi'
          },
          {
            data: 'cost_samples',
            name: 'cost_samples'
          }
        ]
      })

      // Refilter the table
      $('#start_date, #end_date, #name_lab, #payment').on('change', function() {
        datatable.draw();

        console.log($('input[type="search"]').val());

        callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('#name_lab').val(), $(
          'input[type="search"]').val(), $('#payment').val());
      });

      $('input[type="search"]').on('keyup', function() {
        datatable.draw();

        callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('#name_lab').val(), $(
          'input[type="search"]').val(), $('#payment').val());
      });

      // logic filter
      $('#start_date, #end_date').on('change', function() {
        // filter print
        var payment = $('#payment').val();
        if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date" +
            start_date +
            "&end_date=" + end_date + "&name_lab=" + name_lab + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&name_lab=" + name_lab+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val() && $('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val() + "&search=" + $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?search=" +
            $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?payment="+payment).attr('target',
            '_blank');
        }
      });

      $('#name_lab').change(function(e) {
        e.preventDefault();
        var payment = $('#payment').val();
        if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date" +
            start_date +
            "&end_date=" + end_date + "&name_lab=" + name_lab + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&name_lab=" + name_lab+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val() && $('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val() + "&search=" + $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?search=" +
            $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?payment="+payment).attr('target',
            '_blank');
        }
      });

      $('#payment').change(function(e) {
        console.log("dsadasads");
        e.preventDefault();
        var payment = $('#payment').val();
        if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date" +
            start_date +
            "&end_date=" + end_date + "&name_lab=" + name_lab + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&name_lab=" + name_lab+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search+"&payment="+payment).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val() && $('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val() + "&search=" + $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('#name_lab').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val()+"&payment="+payment).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?search=" +
            $('input[type="search"]').val()+"&payment="+payment).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?payment="+payment).attr('target',
            '_blank');
        }
      });

      $('input[type="search"]').on('keyup', function() {
        // filter print
        if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date" +
            start_date +
            "&end_date=" + end_date + "&name_lab=" + name_lab + "&search=" + search).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var name_lab = $('#name_lab').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&name_lab=" + name_lab).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val() && $(
            'input[type="search"]').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
          var search = $('input[type="search"]').val();

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date + "&search=" + search).attr('target', '_blank');
        } else if ($('#start_date').val() && $('#start_date').val()) {

          var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
          var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
            start_date + "&end_date=" + end_date).attr('target', '_blank');
        } else if ($('#name_lab').val() && $('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val() + "&search=" + $('input[type="search"]').val()).attr('target', '_blank');
        } else if ($('#name_lab').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
            $('#name_lab').val()).attr('target', '_blank');
        } else if ($('input[type="search"]').val()) {

          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?search=" +
            $('input[type="search"]').val()).attr('target', '_blank');
        } else {
          $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?payment="+payment).attr('target',
            '_blank');
        }
      });

      if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val() && $(
          'input[type="search"]').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
        var name_lab = $('#name_lab').val();
        var search = $('input[type="search"]').val();

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date" +
          start_date +
          "&end_date=" + end_date + "&name_lab=" + name_lab + "&search=" + search).attr('target', '_blank');
      } else if ($('#start_date').val() && $('#start_date').val() && $('#name_lab').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
        var name_lab = $('#name_lab').val();

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
          start_date + "&end_date=" + end_date + "&name_lab=" + name_lab).attr('target', '_blank');
      } else if ($('#start_date').val() && $('#start_date').val() && $(
          'input[type="search"]').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');
        var search = $('input[type="search"]').val();

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
          start_date + "&end_date=" + end_date + "&search=" + search).attr('target', '_blank');
      } else if ($('#start_date').val() && $('#start_date').val()) {

        var start_date = moment($('#start_date').val()).format('YYYY-MM-DD');
        var end_date = moment($('#end_date').val()).format('YYYY-MM-DD');

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?start_date=" +
          start_date + "&end_date=" + end_date).attr('target', '_blank');
      } else if ($('#name_lab').val()) {

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?name_lab=" +
          $('#name_lab').val()).attr('target', '_blank');
      } else if ($('input[type="search"]').val()) {

        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?search=" +
          $('input[type="search"]').val()).attr('target', '_blank');
      } else {
        $("a.btn-print-periodik-nonklinik").attr("href", "/elits-pendapatan-nonklinik-set-print?payment="+$('#payment').val()).attr('target',
          '_blank');
      }

      callCountPendapatan($('#start_date').val(), $('#end_date').val(), $('#name_lab').val(), $(
        'input[type="search"]').val(), $('#payment').val());

      function callCountPendapatan(start_date = null, end_date = null, name_lab = null, search = null, payment = null) {
        $.ajax({
          type: "POST",
          url: "{{ route('elits-pendapatan-nonklinik-count') }}",
          data: {
            search: search,
            start_date: start_date,
            end_date: end_date,
            payment: payment,
            name_lab: name_lab,
            _token: $('#csrf-token').val()
          },
          dataType: "JSON",
          success: function(response) {
            // $('#count-pendapatan').text(response.count_pendapatan);
            $('#count-pendapatan').text(parseFloat(response.count_pendapatan, 10).toFixed(2).replace(
                /(\d)(?=(\d{3})+\.)/g, "$1,")
              .toString());
            $('#count-sample').text(response.count_sample);
          },
          error: function() {
            swal("ERROR", "System tidak dapat mengambil data total harga dan total sample!", "error");
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