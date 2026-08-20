@extends('masterweb::template.admin.layout')

@section('title')
  Laporan Bulanan Pemeriksaan
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
                <li class="breadcrumb-item active"><a href="{{ url('/report-monthly') }}">Laporan Bulanan Pemeriksaan</a>
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
              <label for="month" class="bold">Bulan</label>
              <select class="smt-select2 form-control w-100" id="month">
                @for ($i = 1; $i <= 12; $i++)
                  <option value="{{ sprintf('%02d', $i) }}" {{ isSelected($i, date('m')) }}>{{ fbulan($i) }}
                  </option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label for="year" class="bold">Tahun</label>
              <select class="smt-select2 form-control w-100" id="year">
                @for ($i = 1990; $i <= date('Y') + 1; $i++)
                  <option value="{{ $i }}" {{ isSelected($i, date('Y')) }}>{{ $i }}</option>
                @endfor
              </select>
            </div>

            <div class="col-md-3">
              <label for="SampleType" class="bold">Laboratorium</label>
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
            {{-- Sama laporan harian: REGISTER PENDAFTARAN (klinik / kimia / mikro) via laboratorium_id --}}
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
            <table id='empTable' class="table">
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
      var table = $('#empTable').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        responsive: true,
        ajax: {
          url: "{{ route('report-monthly.data-report-monthly') }}",
          type: "GET",
          data: function(d) {
            d.search = $('input[type="search"]').val();
            d.month = getMonth();
            d.year = getYear();
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

      new $.fn.dataTable.FixedHeader(table);

      table.on('draw', function() {
        $('[data-toggle="tooltip"]').tooltip();
      });

      function getMonth() {
        return $('#month').val();
      }

      function getYear() {
        return $('#year').val();
      }

      function getSampleType() {
        return $('.select-sampletype').val();
      }

      function getLaboratorium() {
        return $('.select-laboratorium').val();
      }

      $('#month').change(function() {
        table.ajax.reload(null, false);
        hitungSemuaHargaSample();
        setUrlRegisterPendaftaranExport();
      });

      $('#year').change(function() {
        table.ajax.reload(null, false);
        hitungSemuaHargaSample();
        setUrlRegisterPendaftaranExport();
      });

      var skipSampleTypeChangeReload = false;

      $('.select-sampletype').change(function() {
        if (skipSampleTypeChangeReload) {
          return;
        }
        table.ajax.reload(null, false);
        hitungSemuaHargaSample();
        setUrlRegisterPendaftaranExport();
      });

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
        hitungSemuaHargaSample();
        setUrlRegisterPendaftaranExport();
      });

      hitungSemuaHargaSample();

      function hitungSemuaHargaSample() {
        $.ajax({
          type: "GET",
          url: "{{ route('get-total-harga-sample-monthly') }}",
          data: {
            month: getMonth(),
            year: getYear(),
            sampletype: getSampleType(),
            laboratorium: getLaboratorium()
          },
          dataType: "JSON",
          success: function(response) {
            $('#total-harga-sample').html(response);
          },
          error: function(jqXHR) {
            var err = eval("(" + jqXHR.responseText + ")");
            alert(err.Message);
          }
        });
      }

      function setUrlRegisterPendaftaranExport() {
        var $btn = $('.btn-export-register-pendaftaran');
        var month = getMonth();
        var year = $('#year').val();
        var laboratorium = $('.select-laboratorium').val();

        $btn.removeClass('disabled').css('pointer-events', '');

        if (!month || !year || !laboratorium) {
          $btn.attr('href', '#').addClass('disabled').css('pointer-events', 'none');
          return;
        }

        var url = "{{ route('report-register-pendaftaran.export') }}" +
          '?year=' + encodeURIComponent(year) +
          '&month=' + encodeURIComponent(parseInt(month, 10)) +
          '&laboratorium_id=' + encodeURIComponent(laboratorium);
        $btn.attr('href', url);
      }

      $('.btn-export-register-pendaftaran').on('click', function(e) {
        var month = getMonth();
        var year = $('#year').val();
        var laboratorium = $('.select-laboratorium').val();

        if (!month) {
          e.preventDefault();
          alert('Pilih bulan terlebih dahulu.');
          return;
        }
        if (!year) {
          e.preventDefault();
          alert('Pilih tahun terlebih dahulu.');
          return;
        }
        if (!laboratorium) {
          e.preventDefault();
          alert('Pilih laboratorium terlebih dahulu.');
          return;
        }
      });

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
              search: params.term
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
              search: params.term
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

      setUrlRegisterPendaftaranExport();
    });
  </script>
@endsection
