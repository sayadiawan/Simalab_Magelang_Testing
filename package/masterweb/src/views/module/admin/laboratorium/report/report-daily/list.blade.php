@extends('masterweb::template.admin.layout')

@section('title')
  Laporan Harian Pemeriksaan
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
                <li class="breadcrumb-item active"><a href="{{ url('/report-daily') }}">Laporan Harian Pemeriksaan</a>
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

            <div class="col-md-3">
              <label for="year" class="bold">Tanggal</label>
              <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}">
            </div>

            <div class="col-md-3">
              <label for="SampleType" class="bold">Labratorium</label>
              <select class="select-laboratorium form-control w-100">
              </select>
            </div>

            <div class="col-md-3">
              <label for="SampleType" class="bold">Jenis Sarana</label>
              <select class="select-sampletype form-control w-100">
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-6">
          <div class="pt-4 text-left">
            <h3>
              <small class="text-muted">
                Total:
              </small>

              <span id="total-harga-sample">-</span>
            </h3>
          </div>
        </div>

        <div class="col-md-6">
          <div class="pt-4 text-right">
            {{-- <a href="#" class="btn btn-light btn-fw btn-print-report-daily mr-1" target="_blank">
              <i class="fas fa-print"></i>
              Cetak</a> --}}
            {{-- Lab dipilih via Select2 (AJAX): saat Blade di-render server belum tahu KLI/non-KLI → pemilihan worksheet di server pakai satu route exportRegister --}}
            <a href="#" class="btn btn-success btn-fw btn-export-register-pendaftaran" target="_blank"
              title="Excel REGISTER PENDAFTARAN">
              <i class="fas fa-file-excel"></i>
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
            <table id='enaena' class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal Pemeriksaan</th>
                  <th>Nama Sample/Merk</th>
                  <th>Nama Customer/Pasien</th>
                  <th>Harga Persample (Rp)</th>
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
  <script type="text/javascript">
    $(document).ready(function() {
      // DataTable
      var table = $('#enaena').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: "{{ route('report-daily.data-report-daily') }}",
          type: "GET",
          data: function(d) {
            d.search = $('input[type="search"]').val()
            d.tanggal = getDate();
            d.sampletype = getSampleType();
            d.laboratorium = getLaboratorium();
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
          },
          {
            data: 'harga_per_sample',
            name: 'harga_per_sample'
          }
        ]
      });
      // datatables responsive
      new $.fn.dataTable.FixedHeader(table);

      table.on('draw', function() {
        $('[data-toggle="tooltip"]').tooltip();
      });

      function getDate() {
        var tanggal = $('#tanggal').val();
        return tanggal;
      }

      function getSampleType() {
        var sampletype = $('.select-sampletype').val();
        return sampletype;
      }

      function getLaboratorium() {
        var laboratorium = $('.select-laboratorium').val();
        return laboratorium;
      }

      $('#tanggal').change(function() {
        table.ajax.reload(null, false);

        hitungSemuaHargaSample()
        setUrlPrintReport()
      })

      $('.select-program').change(function() {
        table.ajax.reload(null, false);

        hitungSemuaHargaSample()
        setUrlPrintReport()
      })

      var skipSampleTypeChangeReload = false;

      $('.select-sampletype').change(function() {
        if (skipSampleTypeChangeReload) {
          return;
        }
        table.ajax.reload(null, false);

        hitungSemuaHargaSample()
        setUrlPrintReport()
        setUrlRegisterPendaftaranExport()
      })

      $('.select-laboratorium').change(function() {
        var labData = $('.select-laboratorium').select2('data');
        var isKlinik = labData && labData.kode_laboratorium === 'KLI';
        var $sampleType = $('.select-sampletype');

        if (isKlinik) {
          skipSampleTypeChangeReload = true;
          $sampleType.val(null).trigger('change');
          skipSampleTypeChangeReload = false;
          $sampleType.prop('disabled', true);
        } else {
          $sampleType.prop('disabled', false);
        }

        table.ajax.reload(null, false);

        hitungSemuaHargaSample()
        setUrlPrintReport()
      })

      hitungSemuaHargaSample()

      function hitungSemuaHargaSample() {
        var tanggal = $('#tanggal').val();
        var sampletype = $('.select-sampletype').val();
        var laboratorium = $('.select-laboratorium').val();

        $.ajax({
          type: "GET",
          url: "{{ route('get-total-harga-sample-daily') }}",
          data: {
            tanggal: tanggal,
            sampletype: sampletype,
            laboratorium: laboratorium
          },
          dataType: "JSON",
          success: function(response) {
            $('#total-harga-sample').html(response);
          },
          error: function(jqXHR, textStatus, errorThrown) {
            var err = eval("(" + jqXHR.responseText + ")");

            alert(err.Message);
          }
        });
      }

      function setUrlRegisterPendaftaranExport() {
        var $btn = $('.btn-export-register-pendaftaran');
        var tanggal = $('#tanggal').val();
        var laboratorium = $('.select-laboratorium').val();

        $btn.removeClass('disabled').css('pointer-events', '');

        if (!tanggal || !laboratorium) {
          $btn.attr('href', '#').addClass('disabled').css('pointer-events', 'none');
          return;
        }

        var year = tanggal.substring(0, 4);
        var url = "{{ route('report-register-pendaftaran.export') }}" +
          '?year=' + encodeURIComponent(year) +
          '&date=' + encodeURIComponent(tanggal) +
          '&laboratorium_id=' + encodeURIComponent(laboratorium);
        $btn.attr('href', url);
      }

      $('.btn-export-register-pendaftaran').on('click', function(e) {
        var tanggal = $('#tanggal').val();
        var laboratorium = $('.select-laboratorium').val();

        if (!tanggal) {
          e.preventDefault();
          alert('Pilih tanggal terlebih dahulu (tahun diambil dari tanggal tersebut).');
          return;
        }
        if (!laboratorium) {
          e.preventDefault();
          alert('Pilih laboratorium terlebih dahulu.');
          return;
        }
      });

      function setUrlPrintReport() {
        var tanggal = $('#tanggal').val();
        var sampletype = $('.select-sampletype').val();
        var laboratorium = $('.select-laboratorium').val();

        if (tanggal != "" && tanggal != null && tanggal != undefined) {
          var val_url = "{{ url('report-daily/print-report-daily-maatweb?tanggal=') }}" + tanggal;

          if (sampletype != "" && sampletype != null && sampletype != undefined) {
            val_url = val_url + "&sampletype=" + sampletype;
          }

          if (laboratorium != "" && laboratorium != null && laboratorium != undefined) {
            val_url = val_url + "&laboratorium=" + laboratorium;
          }

          $('.btn-print-report-daily').show();
          $('.btn-print-report-daily').attr('href', val_url);
        } else {
          var val_url = '#';

          $('.btn-print-report-daily').hide();
          $('.btn-print-report-daily').attr('href', val_url);
        }

        setUrlRegisterPendaftaranExport();
      }

      var CSRF_TOKEN = $('#csrf-token').val();

      $(".select-sampletype").select2({
        ajax: {
          url: "{{ route('getSampleType') }}",
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
        placeholder: 'Pilih Jenis Sarana',
        allowClear: true,
        theme: 'bootstrap4'
      });

      $(".select-laboratorium").select2({
        ajax: {
          url: "{{ route('get-laboratorium') }}",
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
        placeholder: 'Pilih Laboratorium',
        allowClear: true,
        theme: 'bootstrap4'
      });

      setUrlPrintReport();
    });
  </script>
@endsection
